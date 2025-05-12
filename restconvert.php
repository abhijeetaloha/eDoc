<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require_once('common/commonfunctions.php');
require_once('config.php');
$str_json = file_get_contents('php://input');
$data = json_decode($str_json, true);
$SID = $data['session'];
$CID = $data['controlid'];
$file = $data['thefile'];
if (!isset($SID)) {
	echo json_encode(array('message' => 'No session provided', 'status' => false));
	exit;
}
if (!isset($CID)) {
	echo json_encode(array('message' => 'No controlid provided', 'status' => false));
	exit;
}
if (!isset($file)) {
	echo json_encode(array('message' => 'No file provided', 'status' => false));
	exit;
}
$userhost = getUserHost();
$json = doRIPActionRequest('', 'GET', 'SESSIONS/' . $SID . '?controlid=' . $CID . '&host=' . $userhost, 'REST/');
if (!$json['result']) {
	echo json_encode(array('message' => $json['error'], 'status' => false));
	exit;
}
$temppath = $cfg['db']['tempfilepath'];
$wordpath = $cfg['db']['worddir'];
$isSigextractEnabled = getsetting('SigextractService');
$path_parts = pathinfo($file);
if (($path_parts['extension'] == "doc") || ($path_parts['extension'] == "docx")) {
	$fn = $wordpath . $file;
	$i = 0;
	while (file_exists($fn)) {
		sleep(1);
		$i++;
		if ($i == 60) {
			echo json_encode(array('message' => 'Error converting document', 'status' => false));
			exit;
		}
	}
	$newname = $path_parts['filename'] . '.pdf';
	$err = $temppath . $path_parts['filename'] . '.err';
	if (file_exists($err)) {
		unlink($err);
		logToFile('{"result":false,"error":"Invalid PDF"}');
		echo json_encode(array('message' => 'Invalid PDF', 'status' => false));
		exit;
	} else {
		$file = $newname;
	}
}
$json = doRIPActionRequest('', 'GET', '?SID=' . $SID . '&STRIPBOXES=Y&NAME=' . $file, 'Con2PDF');
if (!$json['result']) {
	if (isset($isSigextractEnabled) && empty($json['sigboxes'])) {
		$json = doRequestToSigextract($temppath . $file);
		if (!$json['result']) {
			echo 'pleaseWait("");showError("' . $json['error'] . '");';
			exit;
		}
		echo 'addRefUploadedDoc("' . $json['pdf'] . '","' . explode('.', $XVARS['theFile'])[0] . '");';
	} else {
		echo json_encode(array('message' => $json['error'], 'status' => false));
		exit;
	}
} else {
	if (isset($temppath)) {
		$fn = $temppath . $file;
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
	echo json_encode(array('message' => 'file converted', 'status' => true, 'file' => $json['pdf'], 'sigboxes' => $json['sigboxes']));
}
exit;
