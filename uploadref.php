<?php
require_once('common/commonfunctions.php');
require_once('config.php');
$tempDir = isset($cfg['db']['tempdir']) ? $cfg['db']['tempdir'] : "temp/";
$newFileArray = array();
function cleanFileName($filename)
{
	return preg_replace('/[^A-Za-z0-9.~:,\+\'!@#=@\$\^ \-_[\]()]/', '', $filename);
}
if (isset($XVARS['DeleteUploadedDoc'])) {
	$temppath = $cfg['db']['tempfilepath'];
	if (!isset($temppath)) {
		exit;
	}
	$fn = $temppath . $XVARS['DeleteUploadedDoc'];
	logToFile('Trying to delete: ' . $fn);
	if (file_exists($fn)) {
		unlink($fn);
	}
	exit;
}

if (isset($_FILES) && count($_FILES) > 0) {
	if (!isset($XVARS['SID'])) {
		echo '<html><head><script src="common/jfunctions.js"></script><script>';
		echo 'function startup(){window.top.window.uploadmsg([{"msg":"Invalid Session ID", "filename": "", "uniqnm":""}]);}</script></head><body onload="startup();"></body></html>';
		exit;
	}
	$userhost = getUserHost();
	$json = doRIPActionRequest('', 'GET', 'SESSIONS/' . $SID . '?controlid=' . $CID . '&host=' . $userhost, 'REST/');
	if (!$json['result']) {
		echo '<html><head><script src="common/jfunctions.js"></script><script>';
		echo 'function startup(){window.top.window.uploadmsg([{"msg":"' . $json['error'] . '", "filename": "", "uniqnm":""}]);}</script></head><body onload="startup();"></body></html>';
		exit;
	}
	$msg = '';
	$target_path = '';
	$filename = '';
	$uniqnm = '';
	$mxsz = 1000000;
	$thetype = "";
	if (isset($cfg['db']['MaxUploadSize'])) {
		$mxsz = $cfg['db']['MaxUploadSize'];
	}
	$upfiles = $_FILES['filerefupload'];
	logToFile(print_r($upfiles, true));
	$filecount = count($upfiles['name']);
	for ($i = 0; $i < $filecount; $i++) {
		$thetype = $ind;
		if (!validFileType($upfiles['tmp_name'][$i], false)) {
			$msg = "Invalid file type";
		} else if ($upfiles['error'][$i] != 0) {
			if ($upfiles['error'][$i] == 4) {
				continue;
			} //the file was optional
			if (($upfiles['error'][$i] == 2) || ($upfiles['error'][$i] == 1)) {
				$msg = 'The file you selected to upload is too large.  Please scan the image at a lower resolution.';
			} else {
				$msg = 'There was an error uploading the image. ERROR CODE: ' . $upfiles['error'][$i];
			}
		} else if ($upfiles["size"][$i] < $mxsz) {
			$path_parts = pathinfo($upfiles['name'][$i]);
			logToFile(print_r($path_parts, true));
			$movefileto = (isset($cfg['db']['tempfilepath']) ? $cfg['db']['tempfilepath'] : "temp/");

			$filename = cleanFileName($path_parts['filename']);
			logToFile('File Name: ' . $filename);
			$file_extn = $path_parts['extension'];
			$uniqnm = uniqid() . '.' . strtolower($file_extn);
			$target_path = $movefileto . $uniqnm;

			// $filename=basename($upfiles['name'][$i]);
			// $filename = preg_replace('/[^A-Za-z0-9\. -]/', '', $filename);
			// $file_extn = substr($filename, strrpos($filename, '.')+1);
			// $uniqnm=uniqid().'.'.strtolower($file_extn);
			// $target_path = $movefileto.$uniqnm; 

			logToFile("Moving file to " . $target_path);
			if (file_exists($movefileto)) {
				try {
					if (!move_uploaded_file($upfiles['tmp_name'][$i], $target_path)) {
						$msg = 'Error moving uploaded file to destination';
					} else if (file_exists($target_path)) {
						$msg = '';
					} else {
						$msg = "An error occured during the file upload.";
					}
				} catch (Exception $e) {
					$msg = "Error uploading the file (" . $e->getMessage() . ")";
				}
			} else {
				$msg = "There was an error uploading the file, please try again.";
			}
		} else {
			$msg = "Error uploading file, file size too large";
		}
		$newFileArray[] = array('filename' => $filename, 'file_extn' => $file_extn, 'uniqnm' => $uniqnm, 'target_path' => $target_path, 'msg' => $msg);
	}
	if (isset($XVARS['droprefupload'])) {
		echo 'var newFileArray = ' . json_encode($newFileArray) . ';';
		echo 'uploadrefmsg(newFileArray);';
	} else {
		echo '<html><head><script src="common/jfunctions.js"></script><script>var newFileArray = ' . json_encode($newFileArray) . ';';
		echo 'function startup(){window.top.window.uploadrefmsg(newFileArray);}</script></head><body onload="startup();"></body></html>';
	}
	exit;
} else if (isset($XVARS['CnvrtFile'])) {
	$temppath = $cfg['db']['tempfilepath'];
	$wordpath = $cfg['db']['worddir'];
	$isSigextractEnabled = getSetting('SigextractService');
	logToFile('Converting image to pdf');
	$path_parts = pathinfo($XVARS['CnvrtFile']);
	if (($path_parts['extension'] == "doc") || ($path_parts['extension'] == "docx")) {
		$fn = $wordpath . $XVARS['CnvrtFile'];
		$i = 0;
		while (file_exists($fn)) {
			sleep(1);
			$i++;
			if ($i == 60) {
				echo 'pleaseWait("");showError("Error converting document");';
				exit;
			}
		}
		$newname = $path_parts['filename'] . '.pdf';
		$err = $temppath . $path_parts['filename'] . '.err';
		logToFile($err);
		if (file_exists($err)) {
			unlink($err);
			logToFile('{"result":false,"error":"Invalid PDF"}');
			echo 'pleaseWait("");showError("Invalid PDF");';
		} else {
			echo 'addUploadedDoc("' . $newname . '","' . explode('.', $XVARS['theFile'])[0] . '");';
		}
		exit;
	}
	$json = doRIPActionRequest('', 'GET', '?SID=' . $SID . '&NAME=' . $XVARS['CnvrtFile'], 'Con2PDF');
	if (!$json['result']) {
		if (isset($isSigextractEnabled) && empty($json['sigboxes'])) {
			$json = doRequestToSigextract($temppath . $XVARS['CnvrtFile']);
			if (!$json['result']) {
				echo 'pleaseWait("");showError("' . $json['error'] . '");';
				exit;
			}
			echo 'addRefUploadedDoc("' . $json['pdf'] . '","' . explode('.', $XVARS['theFile'])[0] . '");';
		} else {
			echo 'pleaseWait("");showError("' . $json['error'] . '");';
			exit;
		}
	} else {
		if (isset($temppath)) {
			$fn = $temppath . $XVARS['CnvrtFile'];
			logToFile('Checking to delete ' . $fn);
			if (file_exists($fn)) {
				unlink($fn);
			}
			if (isset($isSigextractEnabled) && empty($json['sigboxes'])) {
				$json = doRequestToSigextract($temppath . $json['pdf']);
				if (!$json['result']) {
					echo 'pleaseWait("");showError("' . $json['error'] . '");';
					exit;
				}
			}
		}
		echo 'addRefUploadedDoc("' . $json['pdf'] . '","' . explode('.', $XVARS['theFile'])[0] . '");';
	}
	exit;
}
