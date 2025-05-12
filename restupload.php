<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require_once('common/commonfunctions.php');
require_once('config.php');
$newFileArray = array();
$skipConvert = getSetting('SkipConversion');
$sendWordToConvert = getSetting('SendWordFilesToConvert');
function cleanFileName($filename)
{
	return preg_replace('/[^A-Za-z0-9.~:,\+\'!@#=@\$\^ \-_[\]()]/', '', $filename);
}
if (!isset($XVARS['SID'])) {
	echo json_encode(array('message' => 'No session provided', 'status' => false));
	exit;
}
$userhost = getUserHost();
$json = doRIPActionRequest('', 'GET', 'SESSIONS/' . $SID . '?controlid=' . $CID . '&host=' . $userhost, 'REST/');
if (!$json['result']) {
	echo json_encode(array('message' => $json['error'], 'status' => false));
	exit;
}
if (isset($_FILES['fileupload'])) {
	$data = json_decode(file_get_contents('php://input'), true);
	$msg = '';
	$target_path = '';
	$filename = '';
	$uniqnm = '';
	$mxsz = 1000000;
	$thetype = "";
	if (isset($cfg['db']['MaxUploadSize'])) {
		$mxsz = $cfg['db']['MaxUploadSize'];
	}
	foreach ($_FILES as $key => $value) {
		if ($value['error'][$i] != 0) {
			if ($value['error'][$i] == 4) {
				continue;
			} //the file was optional
			if (($value['error'][$i] == 2) || ($value['error'][$i] == 1)) {
				$msg = 'The file you selected to upload is too large.  Please scan the image at a lower resolution.';
			} else {
				$msg = 'There was an error uploading the image. ERROR CODE: ' . $value['error'][$i];
			}
		} else if ($value["size"][$i] < $mxsz) {
			$path_parts = pathinfo($value['name']);
			if ((strtolower($path_parts['extension']) == "doc") || (strtolower($path_parts['extension']) == "docx")) {
				$movefileto = (isset($cfg['db']['worddir']) ? $cfg['db']['worddir'] : "temp/");
			} else {
				$movefileto = (isset($cfg['db']['tempfilepath']) ? $cfg['db']['tempfilepath'] : "temp/");
			}
			$filename = cleanFileName($path_parts['filename']) . "." . $path_parts['extension'];
			$file_extn = $path_parts['extension'];
			$uniqnm = uniqid() . '.' . strtolower($file_extn);
			$target_path = $movefileto . $uniqnm;
			if (file_exists($movefileto)) {
				try {
					if (!move_uploaded_file($value['tmp_name'], $target_path)) {
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
		$newFileArray[] = array('error' => false, 'filename' => $filename, 'file_extn' => $file_extn, 'uniqnm' => $uniqnm, 'msg' => 'file uploaded');
	}
	if ($msg == '') {
		echo json_encode($newFileArray);
	} else {
		echo json_encode(array('message' => $msg, 'error' => true));
	}
	exit;
} else {
	echo json_encode(array('message' => 'No files were uploaded', 'status' => false));
}
