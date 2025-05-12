<?php
require_once('common/commonfunctions.php');
require_once('config.php');
$tempDir = isset($cfg['db']['tempdir']) ? $cfg['db']['tempdir'] : "temp/";
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
		echo 'function startup(){window.top.window.uploadPPMsg("Invalid Session ID","","");}</script></head><body onload="startup();"></body></html>';
		exit;
	}
	$userhost = getUserHost();
	$json = doRIPActionRequest('', 'GET', 'SESSIONS/' . $SID . '?controlid=' . $CID . '&host=' . $userhost, 'REST/');
	if (!isset($json['result'])) {
		echo '<html><head><script src="common/jfunctions.js"></script><script>';
		echo 'function startup(){window.top.window.uploadPPMsg("' . $json['error'] . '","","");}</script></head><body onload="startup();"></body></html>';
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
		//while(list($ind,$val)=each($_FILES)){
		$thetype = $ind;
		if (false) {
			$msg = "Invalid file type";
		} else if ($val['error'] != 0) {
			if ($val['error'] == 4) {
				continue;
			} //the file was optional
			if (($val['error'] == 2) || ($val['error'] == 1)) {
				$msg = 'The file you selected to upload is too large.  Please reduce file size.';
			} else {
				$msg = 'There was an error uploading the csv. ERROR CODE: ' . $val['error'];
			}
		} else if ($val["size"] < $mxsz) {
			$path_parts = pathinfo($val['name']);
			logToFile(print_r($path_parts, true));
			$movefileto = (isset($cfg['db']['tempfilepath']) ? $cfg['db']['tempfilepath'] : "temp/");
			$filename = basename($val['name']);
			$filename = preg_replace('/[^A-Za-z0-9\. -]/', '', $filename);
			$file_extn = substr($filename, strrpos($filename, '.') + 1);
			$uniqnm = uniqid() . '.' . strtolower($file_extn);
			$target_path = $movefileto . $uniqnm;
			logToFile("Moving file to " . $target_path);
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
	echo '<html><head><script>';
	echo 'function startup(){window.parent.window.uploadPPMsg("' . $msg . '","' . $filename . '","' . $uniqnm . '");}</script></head><body onload="startup();"></body></html>';
	exit;
} else {
	$str_json = file_get_contents('php://input');
	$decJ = json_decode($str_json, true);
	if (!isset($decJ['name'])) {
		echo '{"result":false,"error":"file name blank"}';
		exit;
	}
	$file = (isset($cfg['db']['tempfilepath']) ? $cfg['db']['tempfilepath'] : "temp/");
	$file = $file . $decJ['name'];
	if (!File_Exists($file)) {
		logToFile('Unable to find: ' . $file);
		echo '{"result":false,"error":"Unable to find file"}';
		exit;
	}
	$csv = file_get_contents($file);
	$rows = str_getcsv(trim($csv), "\n");
	$numColumns = count(str_getcsv($rows[0]));
	$keys = array();
	for ($i = 1; $i <= $numColumns; $i++) {
		$header = 'col' . $i;
		$keys[] = $header;
	}
	$array = array_map(function ($row) {
		global $keys;
		$returnArray = array_combine($keys, str_getcsv($row));
		return $returnArray;
	}, $rows);
	$json = json_encode($array);
	$tmpln = '"csv":' . $json;
	echo '{"result":true,' . $tmpln . '}';
}
