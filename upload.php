<?php
require_once('common/commonfunctions.php');
require_once('config.php');
$newFileArray = array();
$skipConvert = getSetting('SkipConversion');
$sendWordToConvert = getSetting('SendWordFilesToConvert');

if (isset($XVARS['DeleteUploadedDoc'])) {
	logToFile("Deleting file: " . $XVARS['DeleteUploadedDoc']);

	$temppath = $cfg['db']['tempfilepath'];
	if (!isset($temppath)) {
		logToFile("Unable to delete file, \$temppath not set");
		exit;
	}

	$basename = basename($XVARS['DeleteUploadedDoc']);
	$fn = $temppath . $basename;
	if (!file_exists($fn)) {
		logToFile(`Unable to delete file, does not exist: $fn`);
	} else {
		unlink($fn);
	}
	exit;
}

if (isset($_FILES['clonefileupload'])) {
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
	foreach ($_FILES as $ind => $val) {
		$thetype = $ind;
		if (!validFileType($val['tmp_name'], $skipConvert)) {
			logToFile('Invalid clonefileupload file type, returning error');
			echo '<html><head><script src="common/jfunctions.js"></script><script>';
			echo 'function startup(){window.top.window.uploadmsg([{"msg":"Invalid file type", "filename": "", "uniqnm":""}]);}';
			echo '</script></head><body onload="startup();"></body></html>';
			exit;
		} else if ($val['error'] != 0) {
			if ($val['error'] == 4) {
				continue;
			} //the file was optional
			if (($val['error'] == 2) || ($val['error'] == 1)) {
				$msg = 'The file you selected to upload is too large.  Please scan the image at a lower resolution.';
			} else {
				$msg = 'There was an error uploading the image. ERROR CODE: ' . $val['error'];
			}
		} else if ($val["size"] < $mxsz) {
			$path_parts = pathinfo($val['name']);
			if ((strtolower($path_parts['extension']) == "doc") || (strtolower($path_parts['extension']) == "docx")) {
				$movefileto = (isset($cfg['db']['worddir']) ? $cfg['db']['worddir'] : "temp/");
			} else {
				$movefileto = (isset($cfg['db']['tempfilepath']) ? $cfg['db']['tempfilepath'] : "temp/");
			}
			$filename = cleanFileName($path_parts['filename']) . "." . $path_parts['extension'];
			logToFile('File Name: ' . $filename);
			$file_extn = $path_parts['extension'];
			$uniqnm = uniqid() . '.' . strtolower($file_extn);
			$target_path = $movefileto . $uniqnm;
			if (file_exists($movefileto)) {
				try {
					if (!move_uploaded_file($val['tmp_name'], $target_path)) {
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
	}
	if ($skipConvert) {
		echo '<html><head><script src="common/jfunctions.js"></script><script>';
		echo 'function startup(){window.top.window.addUploadedDoc("' . $uniqnm . '","' . $filename . '");}';
		echo '</script></head><body onload="startup();"></body></html>';
	} else if (isset($XVARS['dropupload'])) {
		echo 'uploadmsg([{"msg":"' . $msg . '", "filename":"' . $filename . '", "uniqnm":"' . $uniqnm . '"}]);';
	} else if (isset($XVARS['droprefupload'])) {
		echo 'uploadrefmsg("' . $msg . '","' . $filename . '","' . $uniqnm . '");';
	} else {
		echo '<html><head><script src="common/jfunctions.js"></script><script>';
		if ($thetype == "filerefupload") {
			echo 'function startup(){window.top.window.uploadrefmsg("' . $msg . '","' . $filename . '","' . $uniqnm . '");}</script></head><body onload="startup();"></body></html>';
		} else {
			echo 'function startup(){window.top.window.uploadmsg([{"msg":"' . $msg . '", "filename":"' . $filename . '", "uniqnm":"' . $uniqnm . '"}]);}</script></head><body onload="startup();"></body></html>';
		}
	}
	exit;
} else if (
	isset($_FILES['fileupload']) ||
	isset($_FILES['CameraUpload']) ||
	isset($_FILES['addnotupload'])
) {
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
	if (isset($_FILES['fileupload'])) {
		$upFiles = $_FILES['fileupload'];
		logToFile('Files received in \$_FILES[\'fileupload\']');
	} else if (isset($_FILES['CameraUpload'])) {
		logToFile('Files received in \$_FILES[\'CameraUpload\']');
		$upFiles = $_FILES['CameraUpload'];
	} else if (isset($_FILES['addnotupload'])) {
		logToFile('Files received in \$_FILES[\'addnotupload\']');
		$upFiles = $_FILES['addnotupload'];
	}
	// Check that the file is a valid size
	$filecount = count($upFiles['name']);
	for ($i = 0; $i < $filecount; $i++) {
		logToFile('Checking validity of file: ' . $upFiles['tmp_name'][$i]);
		if (isset($XVARS['SelType']) && $XVARS['SelType'] == 'Ref') {
			if (!validFileType($upFiles['tmp_name'][$i], false)) {
				logToFile('Invalid Ref fileupload file type, returning error');
				echo '<html><head><script src="common/jfunctions.js"></script><script>';
				echo 'function startup(){window.top.window.uploadmsg([{"msg":"Invalid file type", "filename": "", "uniqnm":""}]);}';
				echo '</script></head><body onload="startup();"></body></html>';
				exit;
			}
		} else if (!validFileType($upFiles['tmp_name'][$i], $skipConvert)) {
			logToFile('Invalid fileupload file type, returning error');
			echo '<html><head><script src="common/jfunctions.js"></script><script>';
			echo 'function startup(){window.top.window.uploadmsg([{"msg":"Invalid file type", "filename": "", "uniqnm":""}]);}';
			echo '</script></head><body onload="startup();"></body></html>';
			exit;
		}
		if ($upFiles['error'][$i] != 0) {
			if ($upFiles['error'][$i] == 4) {
				continue;
			} //the file was optional
			if (($upFiles['error'][$i] == 2) || ($upFiles['error'][$i] == 1)) {
				$msg = 'The file you selected to upload is too large.  Please scan the image at a lower resolution.';
			} else {
				$msg = 'There was an error uploading the image. ERROR CODE: ' . $upFiles['error'][$i];
			}
		} else if ($upFiles["size"][$i] < $mxsz) {
			// logtofile('Tmp path: '.$upFiles['tmp_name'][$i]);
			$path_parts = pathinfo($upFiles['name'][$i]);
			if ((strtolower($path_parts['extension']) == "doc") || (strtolower($path_parts['extension']) == "docx")) {
				$movefileto = (isset($cfg['db']['worddir']) ? $cfg['db']['worddir'] : "temp/");
			} else {
				$movefileto = (isset($cfg['db']['tempfilepath']) ? $cfg['db']['tempfilepath'] : "temp/");
			}
			$filename = cleanFileName($path_parts['filename']) . "." . $path_parts['extension'];
			logToFile('Cleaned file name: ' . $filename);
			$file_extn = $path_parts['extension'];
			$uniqnm = uniqid() . '.' . strtolower($file_extn);
			$target_path = $movefileto . $uniqnm;
			if (file_exists($movefileto)) {
				try {
					if (!move_uploaded_file($upFiles['tmp_name'][$i], $target_path)) {
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
	if (isset($XVARS['dropupload'])) {
		echo 'var newFileArray = ' . json_encode($newFileArray) . ';';
		echo 'uploadmsg(newFileArray);';
	} else if (isset($XVARS['droprefupload'])) {
		echo 'var newFileArray = ' . json_encode($newFileArray) . ';';
		echo 'uploadrefmsg(newFilearray);';
	} else {
		if ($skipConvert || (isset($XVARS['SelType']) && $XVARS['SelType'] == 'Ref')) {
			$filename = cleanFileName($path_parts['filename']);
			echo '<html><head><script src="common/jfunctions.js"></script><script>';
			echo 'function startup(){window.top.window.addUploadedDoc("' . $uniqnm . '","' . $filename . '");}';
			echo '</script></head><body onload="startup();"></body></html>';
		} else {
			$targetMethod = ($thetype == "filerefupload") ? 'uploadrefmsg' : 'uploadmsg';
			echo '<html><head><script src="common/jfunctions.js"></script><script>var newFileArray = ' . json_encode($newFileArray) . ';';
			echo 'function startup(){window.top.window.' . $targetMethod . '(newFileArray);}</script></head><body onload="startup();"></body></html>';
		}
	}
	exit;
} else if (isset($XVARS['CnvrtFile'])) {
	$temppath = $cfg['db']['tempfilepath'];
	$wordpath = $cfg['db']['worddir'];
	$isSigextractEnabled = getsetting('SigextractService');
	$filename = pathinfo($XVARS['theFile'])['filename'];
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
		if (file_exists($err)) {
			unlink($err);
			logToFile('{"result":false,"error":"Invalid PDF"}');
			echo 'pleaseWait("");showError("Invalid PDF");';
		} else {
			echo 'addUploadedDoc("' . $newname . '","' . $filename . '");';
		}
		exit;
	}

	$json = doRIPActionRequest('', 'GET', '?SID=' . $XVARS['SID'] . '&NAME=' . $XVARS['CnvrtFile'], 'Con2PDF');
	if (!$json['result']) {
		if (isset($isSigextractEnabled) && empty($json['sigboxes'])) {
			$json = doRequestToSigextract($temppath . $XVARS['CnvrtFile']);
			if (!$json['result']) {
				echo 'pleaseWait("");showError("' . $json['error'] . '");';
				exit;
			}
			echo 'addUploadedDoc("' . $json['pdf'] . '","' . $filename . '",{"sigboxes":' . json_encode($json['sigboxes']) . '});';
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
		echo 'addUploadedDoc("' . $json['pdf'] . '","' . $filename . '",{"sigboxes":' . json_encode($json['sigboxes']) . '});';
	}
	exit;
} else if (isset($XVARS['StripFile'])) {
	// logtofile("STRIPPING FILE");
	$temppath = $cfg['db']['tempfilepath'];
	$wordpath = $cfg['db']['worddir'];
	$isSigextractEnabled = getsetting('SigextractService');
	$filename = pathinfo($XVARS['theFile'])['filename'];
	$path_parts = pathinfo($XVARS['StripFile']);
	if (($path_parts['extension'] == "doc") || ($path_parts['extension'] == "docx")) {
		if (!isset($cfg['db']['worddir'])) {
			logtofile('No worddir specified for converting doc files');
			echo 'pleaseWait("");showError("Error converting document");';
			exit;
		}
		$fn = $wordpath . $XVARS['StripFile'];
		// logtofile("StripFile doc filename to convert: " . $fn);
		$i = 0;
		//jpg and .docx files don't have permissions to view, why??
		//Maybe they're supposed to be deleted

		//sleep(1) or $i++ seems to be causing a "Maximum execution time" error.
		//Why are we sleeping here?
		//Looks like: "Error out if the file still exists after 60 ticks."
		//This makes me assume that we expect the file to be removed by some external process.

		//Have 2 services "WordtoPDF service"
		//install it

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
		if (file_exists($err)) {
			unlink($err);
			logToFile('{"result":false,"error":"Invalid PDF"}');
			echo 'pleaseWait("");showError("Invalid PDF");';
			exit;
		} else {
			if ($sendWordToConvert) {
				$XVARS['StripFile'] = $newname;
			} else {
				echo 'addUploadedDoc("' . $newname . '","' . $filename . '");';
				exit;
			}
		}
	}
	$json = doRIPActionRequest('', 'GET', '?SID=' . $XVARS['SID'] . '&STRIPBOXES=Y&NAME=' . $XVARS['StripFile'], 'Con2PDF');
	if (!$json['result']) {
		if (isset($isSigextractEnabled) && empty($json['sigboxes'])) {
			$json = doRequestToSigextract($temppath . $XVARS['StripFile']);
			if (!$json['result']) {
				echo 'pleaseWait("");showError("' . $json['error'] . '");';
				exit;
			}
			echo 'addUploadedDoc("' . $json['pdf'] . '","' . $filename . '",{"sigboxes":' . json_encode($json['sigboxes']) . '});';
		} else {
			echo 'pleaseWait("");showError("' . $json['error'] . '");';
			exit;
		}
	} else {
		if (isset($temppath)) {
			$fn = $temppath . $XVARS['StripFile'];
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
		echo 'addUploadedDoc("' . $json['pdf'] . '","' . $filename . '",{"sigboxes":' . json_encode($json['sigboxes']) . '});';
	}
	exit;
}


function cleanFileName($filename)
{
	return preg_replace('/[^A-Za-z0-9.~:,\+\'!@#=@\$\^ \-_[\]()]/', '', $filename);
}
