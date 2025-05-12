<?php
require_once('common/commonfunctions.php');
require_once('config.php');
$tempDir = isset($cfg['db']['tempdir']) ? $cfg['db']['tempdir'] : "temp/";
function handlePostedFile()
{
	global $_FILES;
	global $XVARS;
	global $CID;
	global $SID;
	global $cfg;
	logToFile('Handling Posted File');
	logToFile(print_r($_FILES, true));
	logToFile(print_r($XVARS, true));
	$userhost = getUserHost();
	if (isset($_FILES) && count($_FILES) > 0) {
		$response = '';
		$jsonRes = json_decode(urldecode($XVARS['JSON']), true);
		$CID = $jsonRes['controlid'];
		$data = array("edocsig" => $jsonRes['handoff'], "action" => "EDOCSIG", "host" => $userhost, "controlid" => $jsonRes['controlid']);
		$data_string = json_encode($data);
		$json = doRIPRequest2($data_string, 'POST', 'SESSIONS/');
		if (!isset($json)) {
			return '{"result":false,"error":"Error during handoff"}';
			exit;
		} else {
			if ($json["result"]) {
				$SID = $json['session'];
				$XVARS['SID'] = $json['session'];
				$response = ',"session":"' . $SID . '"';
			} else {
				return '{"result":false,"error":"Error during handoff"}';
				exit;
			}
		}
		$msg = '';
		$target_path = '';
		$filename = '';
		$uniqnm = '';
		foreach ($_FILES as $ind => $val) {
			//while(list($ind,$val)=each($_FILES)){
			if ($val['error'] != 0) {
				if ($val['error'] == 4) {
					continue;
				} //the file was optional
				if (($val['error'] == 2) || ($val['error'] == 1)) {
					$msg = 'The file you selected to upload is too large.  Please scan the image at a lower resolution.';
				} else {
					$msg = 'There was an error uploading the image. ERROR CODE: ' . $val['error'];
				}
			} else if ($val["size"] < 10000000000) {
				$path_parts = pathinfo($val['name']);
				$isSigextractEnabled = getSetting('SigextractService');
				$temppath = $cfg['db']['tempfilepath'];
				logToFile(print_r($path_parts, true));
				if (($path_parts['extension'] == "doc") || ($path_parts['extension'] == "docx")) {
					$movefileto = (isset($cfg['db']['worddir']) ? $cfg['db']['worddir'] : "temp/");
				} else {
					$movefileto = (isset($cfg['db']['tempfilepath']) ? $cfg['db']['tempfilepath'] : "temp/");
				}
				$filename = basename($val['name']);
				$filename = preg_replace('/[^A-Za-z0-9\. -]/', '', $filename);
				$file_extn = substr($filename, strrpos($filename, '.') + 1);
				$uniqnm = uniqid() . '.' . $file_extn;
				$target_path = $movefileto . $uniqnm;
				logToFile("Moving file to " . $target_path);
				if (file_exists($movefileto)) {
					try {
						while (move_uploaded_file($val['tmp_name'], $target_path)) {; // Wait for the script to finish its upload    
						}
						if (file_exists($target_path)) {
							$msg = '';
							if (isset($XVARS['JSON'])) { //doc conversion as well
								$json = doRIPActionRequest('', 'GET', '?SID=' . $SID . '&NAME=' . $uniqnm, 'Con2PDF');
								if (!$json['result']) {
									if (isset($isSigextractEnabled) && empty($json['sigboxes'])) {
										$json = doRequestToSigextract($temppath . $uniqnm);
										if (!$json['result']) {
											$msg = $json['error'];
										} else {
											$uniqnm = $json['pdf'];
										}
									} else {
										$msg = $json['error'];
									}
								} else {
									if (isset($temppath)) {
										$fn = $temppath . $uniqnm;
										logToFile('Checking to delete ' . $fn);
										if (file_exists($fn)) {
											unlink($fn);
										}
										if (isset($isSigextractEnabled) && empty($json['sigboxes'])) {
											$json = doRequestToSigextract($temppath . $json['pdf']);
											if (!$json['result']) {
												$msg = $json['error'];
											}
										}
									}
									$uniqnm = $json['pdf'];
								}
							}
						} else {
							$msg = "An error occured during the file upload.";
						}
					} catch (Exception $e) {
						$msg = "Error uploading the file (" . $e->getMessage() . ")";
					}
				} else {
					$msg = "There was an error uploading the file, please try again!";
				}
			} else {
				$msg = "Error uploading file (File to Large).";
			}
		}
		if ($msg == '') {
			return '{"result":true,"name":"' . $uniqnm . '"' . $response . '}';
		} else {
			return '{"result":false,"error":"' . $msg . '"}';
		}
		exit;
	}
}
