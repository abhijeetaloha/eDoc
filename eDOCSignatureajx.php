<?php
require_once('common/commonfunctions.php');
require_once('config.php');
$XVARS = (isset($_POST) && count($_POST) > 0) ? $_POST : $_GET;
$SID = (isset($XVARS['SID'])) ? $XVARS['SID'] : '';
$CID = isset($XVARS["CID"]) ? $XVARS["CID"] : '';
$userhost = getUserHost();
$tmp = isset($cfg['db']['tempfilepath']) ? $cfg['db']['tempfilepath'] : "temp/";
if (isset($XVARS['CIDTEST'])) {
	$res = 'SESSIONS/';
	$act = 'SQLTEST';
	if (isset($XVARS['RAP'])) {
		if ($XVARS['RAP'] == 'EDS') {
			$res = 'SIGNSETS/';
		}
		if ($XVARS['RAP'] == 'PKG') {
			$res = 'PACKAGES/';
		}
		if ($XVARS['RAP'] == 'RDC') {
			$res = 'DEPOSITS/';
		}
		if ($XVARS['RAP'] == 'TMP') {
			$res = 'TEMPLATES/';
		}
		if ($XVARS['RAP'] == 'MITEK') {
			$res = 'DEPOSITS/';
			$act = 'HEARTBEAT';
		}
		if ($XVARS['RAP'] == 'WF') {
			$res = 'WORKFLOWS/';
		}
		if ($XVARS['RAP'] == 'DOC') {
			$res = 'DOCS/';
		}
	}
	$data = array("action" => $act, "host" => $userhost, "controlid" => $CID);
	$data_string = json_encode($data);
	$json = doRIPActionRequest($data_string, 'POST', $res, 'REST/');
	if (!isset($json)) {
		echo 'SERVER DOWN';
		exit;
	}
	logToFile(print_r($json, true));
	if ($json["result"]) {
		echo 'OK';
	} else {
		echo $json["error"];
	}
	exit;
}
if (isset($XVARS['DeleteUploadedDoc'])) {
	$temppath = $cfg['db']['tempfilepath'];
	if (!isset($temppath)) {
		exit;
	}
	$fn = $temppath . $XVARS['DeleteUploadedDoc'];
	if (file_exists($fn)) {
		unlink($fn);
	}
	$tmp = $XVARS;
	foreach ($tmp as $ind => $val) {
		//while(list($ind,$val)=each($tmp)){
		if (($ind != 'CID') && ($ind != 'DeleteUploadedDoc') && ($ind != 'LogOff') && ($ind != 'SID')) {
			$fn = $temppath . $val;
			if (file_exists($fn)) {
				unlink($fn);
			}
		}
	}
	if (isset($XVARS['LogOff'])) {
		echo 'window.open("eDOCSignature.php?CID=' . $XVARS['CID'] . '&SID=' . $XVARS['SID'] . '","_self")';
	}
	echo 'OK';
	exit;
}
if (isset($XVARS['DeleteTempFiles'])) {
	$data = array("action" => "DELETE", "session" => $SID, "deletesession" => true, "host" => $userhost, "controlid" => $CID);
	$data_string = json_encode($data);
	$json = doRIPActionRequest($data_string, 'POST', 'SESSIONS/', 'REST/', 'REST/');
	if ($SID == "") {
		exit;
	}
	deleteFilesForSession($SID);
	if (!isset($json)) {
		echo 'ERROR';
		exit;
	} else {
		echo 'OK';
		exit;
	}
}
if (isset($XVARS['UploadSignSet'])) {
	$docId = $XVARS['DocID'];
	$signSetId = $XVARS['SignSetID'];
	$msg = '';
	$target_path = '';
	$filename = '';
	if (isset($_FILES)) {
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
			} else if ($val["size"] < 8000000) {
				$path_parts = pathinfo($val['name']);
				logToFile(print_r($path_parts, true));
				$movefileto = (isset($cfg['db']['tempfilepath']) ? $cfg['db']['tempfilepath'] : "temp/");
				$filename = basename($val['name']);
				$filename = preg_replace('/[^A-Za-z0-9\. -]/', '', $filename);
				$target_path = $movefileto . $SID . '.sie';
				logToFile("Moving file to " . $target_path);
				if (file_exists($movefileto)) {
					try {
						while (move_uploaded_file($val['tmp_name'], $target_path)) {; // Wait for the script to finish its upload
						}
						if (file_exists($target_path)) {
							$file_contents = file_get_contents($target_path);
							$file_contents = str_replace(array("!DOCID!", "!SIGNSETID!", "!IP!"), array($docId, $signSetId, $userhost), $file_contents);
							file_put_contents($target_path, $file_contents);
							$msg = '';
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
	}
	if ($msg != "") {
		echo $msg;
	} else {
		echo '<html><body>FileName="' . $SID . '.sie"</body></html>';
	}
	exit;
}
if (isset($XVARS['UploadIt'])) {
	$msg = '';
	$target_path = '';
	$filename = '';
	if (isset($_FILES)) {
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
			} else if ($val["size"] < 8000000) {
				$path_parts = pathinfo($val['name']);
				logToFile(print_r($path_parts, true));
				if (($path_parts['extension'] == "doc") || ($path_parts['extension'] == "docx")) {
					$movefileto = (isset($cfg['db']['worddir']) ? $cfg['db']['worddir'] : "temp/");
				} else {
					$movefileto = (isset($cfg['db']['tempfilepath']) ? $cfg['db']['tempfilepath'] : "temp/");
				}
				$filename = basename($val['name']);
				$filename = preg_replace('/[^A-Za-z0-9\._ -]/', '', $filename);
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
	}
	logToFile($filename . ' uploaded');
	if (isset($XVARS['TestUp'])) {
		echo '<html><body>FileName="' . $uniqnm . '"</body></html>';
		exit;
	}
	echo '<html><head><script src="jfunctions.js"></script><script>';
	echo 'function startup(){window.top.window.uploadmsg([{"msg":"' . $msg . '", "filename":"' . $filename . '", "uniqnm":"' . $uniqnm . '"}]);}</script></head><body onload="startup();"></body></html>';
	exit;
}
/*
TODO: Many functions/variables we call from this point onward are undefined and have been that way as far back as
1021c1c56dac274d28d4d8a0a678d563d34907e0 when the code was copied to GitHub.
Replace the following with their proper functions:
RefreshSessionTimeOut
DrawPackages
DrawUploadedDocs
GUID
ShowLogIn
DoSgnStSearch
ShowDocSent
ShowLoginError
ReloadPage
ShowTemplateLink
ShowTemplateSaved
DrawingViewDoc
LoadDocumentForView
MergeSavedSignersWithCurrentList
ItemCount
DrawingEditDoc
LoadDocumentForEdit
UpdatePackageName
ShowEditDocSignerDlg
PckgSigners
ShowEditSignerDlg
DrawTemplates
*/
if (isset($XVARS['CnvrtFile'])) {
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
				echo 'alert("Error converting document");hideIt("PlsWt");';
				logToFile('Conversion took to long');
				exit;
			}
		}
		$newname = $path_parts['filename'] . '.pdf';
		echo 'var theDoc=new UploadedDoc();';
		echo 'theDoc.pdf="' . $newname . '";';
		echo 'theDoc.formname="' . $XVARS['theFile'] . '";';
		echo 'theDoc.pkgid=GUID;';
		echo 'uploadedDocs.push(theDoc);DrawUploadedDocs();';
		exit;
	}
	$json = doRIPRequest('', 'GET', '?SID=' . $SID . '&NAME=' . $XVARS['CnvrtFile'], 'Con2PDF');
	if (!$json['result']) {
		if (isset($isSigextractEnabled) && empty($json['sigboxes'])) {
			$json = doRequestToSigextract($temppath . $XVARS['CnvrtFile']);
			if (!$json['result']) {
				echo 'alert("' . $json['error'] . '");hideIt("PlsWt");';
				exit;
			}
			echo 'var theDoc=new UploadedDoc();';
			echo 'theDoc.pdf="' . $json['pdf'] . '";';
			$explodedata = explode('.', $XVARS['CnvrtFile']);
			echo 'theDoc.formname="' . $XVARS['theFile'] . '";';
			echo 'theDoc.pkgid=GUID;';
			if (!empty($json['sigboxes'])) {
				echo 'theDoc.sigboxes=' . $json['sigboxes'] . ';';
			}
			echo 'uploadedDocs.push(theDoc);DrawUploadedDocs();';
		} else {
			echo 'alert("' . $json['error'] . '");hideIt("PlsWt");';
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
					echo 'alert("' . $json['error'] . '");hideIt("PlsWt");';
					exit;
				}
			}
		}
		echo 'var theDoc=new UploadedDoc();';
		echo 'theDoc.pdf="' . $json['pdf'] . '";';
		$explodedata = explode('.', $XVARS['CnvrtFile']);
		echo 'theDoc.formname="' . $XVARS['theFile'] . '";';
		echo 'theDoc.pkgid=GUID;';
		if (!empty($json['sigboxes'])) {
			echo 'theDoc.sigboxes=' . $json['sigboxes'] . ';';
		}
		echo 'uploadedDocs.push(theDoc);DrawUploadedDocs();';
	}
	exit;
}
if (isset($XVARS['Logout'])) {
	$json = doRIPRequest('', 'DELETE', 'SESSIONS/' . $XVARS['SID'] . '?controlid=' . $CID . '&host=' . $userhost);
	if ($XVARS['SID'] == "") {
		exit;
	}
	deleteFilesForSession($XVARS['SID']);
	echo 'ShowLogOff();';
	exit;
}
if (isset($XVARS['RefreshSession'])) {
	$json = doRIPRequest('', 'GET', 'SESSIONS/' . $SID . '?controlid=' . $CID . '&host=' . $userhost);
	if (!$json['result']) {
		echo 'ShowLogIn("' . $json['error'] . '");';
		exit;
	} else {
		echo 'RefreshSessionTimeOut();';
	}
	exit;
}
if (isset($XVARS['SendTicket'])) {
	$data = array("session" => $SID, "action" => "SEND", "signsetid" => "none", "pkgid" => $XVARS['PkgID'], "host" => $userhost, "controlid" => $CID);
	//$data = array("session" => $SID, "action"=>"TICKET", "signsetid" => "none", "pkgid" => $XVARS['PkgID'], "host" => $userhost, "controlid" => $CID);
	$data_string = json_encode($data);
	logToFile($data_string);
	$json = doRIPRequest($data_string, 'POST', 'PACKAGES/');
	//$json=doRIPRequest($data_string,'POST','SIGNSETS/');
	if (!isset($json)) {
		echo 'showError("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'alert("' . $json['error'] . '");';
		exit;
	}
	if (isset($XVARS['OnPkgDialog'])) {
		echo 'DoSgnStSearch("",0);';
	} else {
		echo 'ShowDocSent();';
	}
	echo 'hideIt("EditSignerDlg");hideIt("PlsWt");RefreshSessionTimeOut();';
	exit;
}
if (isset($XVARS['DeleteTemplate'])) {
	$data = array("docid" => $XVARS['DocID'], "session" => $SID, "host" => $userhost, "controlid" => $CID);
	$data_string = json_encode($data);
	logToFile($data_string);
	$json = doRIPRequest($data_string, 'DELETE', 'TEMPLATES/');
	echo 'hideIt("PlsWt");';
	if (!isset($json)) {
		echo 'alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'ShowLoginError("Server Down Try Again Later");';
		exit;
	} else {
		echo 'window.open("Templates.php?CID=' . $CID . '&SID=' . $SID . '","_self");';
	}
	exit;
}
if (isset($XVARS['GetUsers'])) {
	$data = array("action" => "GETUSERS", "requiredpermission" => "ProDOC Edit All Packages", "session" => $SID, "host" => $userhost, "controlid" => $CID);
	$data_string = json_encode($data);
	logToFile($data_string);
	$json = doRIPRequest($data_string, 'GET', 'USERS/');
	echo 'hideIt("PlsWt");';
	if (!isset($json)) {
		echo 'alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'ShowLoginError("Server Down Try Again Later");';
		exit;
	} else {
		if (isset($json['users']) && count($json['users']) == 0) {
			echo 'get("UserListCell").innerHTML=\'<input type="hidden" id="UserName" name="UserName" value="' . $XVARS['Name'] . '">\';';
		} else {
			$page = '<select id="UserName" name="UserName" class="SearchTextField"><option value="">All</option>';
			for ($i = 0; $i < count($json['users']); $i++) {
				$checked = '';
				if ($json['users'][$i]['username'] == $XVARS['Name']) {
					$checked = ' selected';
				}
				$nm = $json['users'][$i]['fullname'];
				if ($nm == '') {
					$nm = $json['users'][$i]['username'];
				}
				$page .= '<option value="' . $json['users'][$i]['username'] . '"' . $checked . '>' . $nm . '</option>';
			}
			$page .= '</select>';
			$page = str_replace('"', '\"', $page);
			echo 'get("UserListCell").innerHTML="' . $page . '";';
			echo 'showIt2("UserListCell","table-cell");';
		}
		echo 'DoSgnStSearch("",searchStart);';
	}
	exit;
}
if (isset($XVARS['DeleteDFC'])) {
	$data = array("docid" => $XVARS['DocID'], "session" => $SID, "pkgid" => $XVARS['PkgID'], "host" => $userhost, "controlid" => $CID);
	$data_string = json_encode($data);
	logToFile($data_string);
	$json = doRIPRequest($data_string, 'DELETE', 'DFC/');
	echo 'hideIt("PlsWt");';
	if (!isset($json)) {
		echo 'alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'ShowLoginError("Server Down Try Again Later");';
		exit;
	} else {
		echo 'ReloadPage();';
	}
	exit;
}
if (isset($XVARS['ResetDFC'])) {
	$data = array("docid" => $XVARS['DocID'], "session" => $SID, "pkgid" => $XVARS['PkgID'], "host" => $userhost, "controlid" => $CID);
	$data_string = json_encode($data);
	logToFile($data_string);
	$json = doRIPRequest($data_string, 'RESET', 'DFC/');
	echo 'hideIt("PlsWt");';
	if (!isset($json)) {
		echo 'alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'ShowLoginError("Server Down Try Again Later");';
		exit;
	} else {
		echo 'ReloadPage();';
	}
	exit;
}
if (isset($XVARS['Link'])) {
	$data = array("docid" => $XVARS['DocID'], "session" => $SID, "host" => $userhost, "controlid" => $CID);
	$data_string = json_encode($data);
	logToFile($data_string);
	$json = doRIPRequest($data_string, 'LINK', 'TEMPLATES/');
	if (!isset($json)) {
		echo 'hideIt("PlsWt");alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'hideIt("PlsWt");ShowLoginError("Server Down Try Again Later");';
		exit;
	} else {
		echo 'hideIt("PlsWt");ShowTemplateLink("' . $json['link'] . '");RefreshSessionTimeOut();';
	}
	exit;
}
if (isset($XVARS['UpdateTemplate'])) {
	$tmparr = $XVARS;
	foreach ($tmparr as $ind => $val) {
		//while(list($ind,$val)=each($tmparr)){
		if (($ind != 'SID') && ($ind != 'CID') && ($ind != 'FormName') && ($ind != 'UpdateTemplate') && ($ind != 'PkgID') && ($ind != 'DocID')) {
			$sigboxes[] = $val;
		}
	}
	$data = array("docid" => $XVARS['DocID'], "session" => $SID, "formname" => $XVARS['FormName'], "sigboxes" => $sigboxes, "host" => $userhost, "controlid" => $CID);
	$data_string = json_encode($data);
	logToFile($data_string);
	$json = doRIPRequest($data_string, 'UPDATE', 'TEMPLATES/');
	if (!isset($json)) {
		echo 'hideIt("PlsWt");alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'hideIt("PlsWt");ShowLoginError("Server Down Try Again Later");';
		exit;
	} else {
		echo 'hideIt("PlsWt");RefreshSessionTimeOut();';
	}
	exit;
}
if (isset($XVARS['UpdateDFC'])) {
	$tmparr = $XVARS;
	foreach ($tmparr as $ind => $val) {
		//while(list($ind,$val)=each($tmparr)){
		if (($ind != 'SID') && ($ind != 'CID') && ($ind != 'FormName') && ($ind != 'UpdateDFC') && ($ind != 'PkgID') && ($ind != 'DocID')) {
			$sigboxes[] = $val;
		}
	}
	$data = array("docid" => $XVARS['DocID'], "session" => $SID, "formname" => $XVARS['FormName'], "pkgid" => $XVARS['PkgID'], "sigboxes" => $sigboxes, "host" => $userhost, "controlid" => $CID);
	$data_string = json_encode($data);
	logToFile($data_string);
	$json = doRIPRequest($data_string, 'UPDATEDFC', 'DFC/');
	if (!isset($json)) {
		echo 'hideIt("PlsWt");alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'hideIt("PlsWt");ShowLoginError("Server Down Try Again Later");';
		exit;
	} else {
		echo 'hideIt("PlsWt");RefreshSessionTimeOut();';
	}
	exit;
}
if (isset($XVARS['CreateTemplate'])) {
	$tmparr = $XVARS;
	foreach ($tmparr as $ind => $val) {
		//while(list($ind,$val)=each($tmparr)){
		if (($ind != 'SID') && ($ind != 'Send') && ($ind != 'PkgName') && ($ind != 'CID') && ($ind != 'FormName') && ($ind != 'CreateTemplate') && ($ind != 'PkgID') && ($ind != 'Image')) {
			$sigboxes[] = $val;
		}
	}
	$files[] = $XVARS['Image'];
	$data = array("formname" => $XVARS['FormName'], "session" => $SID, "sigboxes" => $sigboxes, "host" => $userhost, "controlid" => $CID, "files" => $files);
	$data_string = json_encode($data);
	logToFile($data_string);
	$json = doRIPRequest($data_string, 'POST', 'TEMPLATES/');
	if (!isset($json)) {
		echo 'hideIt("PlsWt");alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'hideIt("PlsWt");ShowLoginError("Server Down Try Again Later");';
		exit;
	} else {
		echo 'hideIt("PlsWt");ShowTemplateSaved("' . $json['handoff'] . '");RefreshSessionTimeOut();';
	}
	exit;
}
if (isset($XVARS['CreateDFC'])) {
	$tmparr = $XVARS;
	foreach ($tmparr as $ind => $val) {
		//while(list($ind,$val)=each($tmparr)){
		if (($ind != 'SID') && ($ind != 'Send') && ($ind != 'SndCode') && ($ind != 'SendCopy') && ($ind != 'PkgName') && ($ind != 'CID') && ($ind != 'FormName') && ($ind != 'CreateDFC') && ($ind != 'PkgID') && ($ind != 'Image')) {
			$sigboxes[] = $val;
		}
	}
	$files[] = $XVARS['Image'];
	$data = array("formname" => $XVARS['FormName'], "session" => $SID, "sendcode" => $XVARS['SndCode'], "sendcopy" => $XVARS['SendCopy'], "pkgname" => $XVARS['PkgName'], "pkgid" => $XVARS['PkgID'], "sigboxes" => $sigboxes, "host" => $userhost, "controlid" => $CID, "files" => $files);
	$data_string = json_encode($data);
	logToFile($data_string);
	$json = doRIPRequest($data_string, 'POST', 'DFC/');
	if (!isset($json)) {
		echo 'hideIt("PlsWt");alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'hideIt("PlsWt");ShowLoginError("Server Down Try Again Later");';
		exit;
	} else {
		echo 'hideIt("PlsWt");saveSendNextDocument(' . $XVARS['Send'] . ');RefreshSessionTimeOut();';
	}
	exit;
}
if (isset($XVARS['VieworEditDoc'])) {
	$data = array("session" => $SID, "action" => "EDITDOC", "host" => $userhost, "controlid" => $CID, "docid" => $XVARS['DocID']);
	$data_string = json_encode($data);
	$json = doRIPRequest($data_string, 'GET', 'DFC/');
	echo 'hideIt("PlsWt");';
	if (!isset($json)) {
		echo 'alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'alert("' . $json['error'] . '");';
		exit;
	} else {
		if (($json["status"] == "Complete") || ($json["status"] == "Part Signed") || ($json["status"] == "Declined")) {
			echo 'uploadedDocs=[];var theDoc=new UploadedDoc;theDoc.pdf="' . $json['graphic'] . '";theDoc.formname="' . $json['form'] . '";theDoc.docid="' . $XVARS['DocID'] . '";theDoc.pkgid="' . $json['pkgid'] . '";';
			echo 'uploadedDocs.push(theDoc);';
			echo 'DrawingViewDoc=true;LoadDocumentForView();RefreshSessionTimeOut();';
		} else {
			echo 'uploadedDocs=[];var theDoc=new UploadedDoc;theDoc.pdf="' . $json['graphic'] . '";theDoc.formname="' . $json['form'] . '";theDoc.docid="' . $XVARS['DocID'] . '";theDoc.pkgid="' . $json['pkgid'] . '";';
			echo 'uploadedDocs.push(theDoc);';
			echo 'signers=[];';
			echo 'var newSigner=new Signer;';
			if (!isset($json['signsets'])) {
				$json['signsets'] = [];
			}
			for ($i = 0; $i < count($json['signsets']); $i++) {
				if ($i != 0) {
					echo 'newSigner=new Signer;';
				}
				echo 'newSigner.signsetid="' . $json['signsets'][$i]['signsetid'] . '";';
				echo 'newSigner.name="' . $json['signsets'][$i]['name'] . '";';
				echo 'newSigner.email="' . $json['signsets'][$i]['email'] . '";';
				echo 'newSigner.selected=true;';
				echo 'newSigner.authcode="' . $json['signsets'][$i]['authcode'] . '";';
				echo 'newSigner.verifyrequired="' . $json['signsets'][$i]['verifyrequired'] . '";';
				echo 'signers.push(newSigner);';
			}
			echo 'MergeSavedSignersWithCurrentList();setSignersIDs();';
			echo 'sigItems=[];var theBox=new SigBox;';
			if (!is_array($json['sigboxes'])) {
				$json['sigboxes'] = [];
			}
			for ($i = 0; $i < count($json['sigboxes']); $i++) {
				if ($i != 0) {
					echo 'theBox=new SigBox;';
				}

				echo 'theBox.xp=' . $json['sigboxes'][$i]['left'] . ';';
				echo 'theBox.yp=' . $json['sigboxes'][$i]['top'] . ';';
				echo 'theBox.wp=' . $json['sigboxes'][$i]['width'] . ';';
				echo 'theBox.hp=' . $json['sigboxes'][$i]['height'] . ';';
				echo 'theBox.type=' . $json['sigboxes'][$i]['esigntype'] . ';';
				echo 'theBox.page=' . $json['sigboxes'][$i]['pagenumber'] . ';';
				echo 'theBox.signerid="' . $json['sigboxes'][$i]['signsetid'] . '";';
				echo 'theBox.signsetid="' . $json['sigboxes'][$i]['signsetid'] . '";';
				echo 'theBox.fieldname="' . $json['sigboxes'][$i]['fieldname'] . '";';
				echo 'theBox.fieldlabel="' . $json['sigboxes'][$i]['fieldlabel'] . '";';
				echo 'theBox.defaultvalue="' . $json['sigboxes'][$i]['defaultvalue'] . '";';
				echo 'theBox.required="' . $json['sigboxes'][$i]['fieldrequired'] . '";';
				echo 'theBox.autofillfield="' . ($json['sigboxes'][$i]['autofillfield'] || '') . '";';
				echo 'theBox.checkedvalue="' . $json['sigboxes'][$i]['checkedvalue'] . '";';
				echo 'theBox.uncheckedvalue="' . $json['sigboxes'][$i]['uncheckedvalue'] . '";';
				echo 'theBox.depfield="' . $json['sigboxes'][$i]['depfield'] . '";';
				echo 'theBox.depfieldvalue="' . $json['sigboxes'][$i]['depfieldvalue'] . '";';
				echo 'theBox.depoperator="' . $json['sigboxes'][$i]['depoperator'] . '";';
				echo 'theBox.id="' . $i . '";';
				echo 'sigItems.push(theBox);';
			}
			echo 'ItemCount=sigItems.length;';
			echo 'DrawingEditDoc=true;LoadDocumentForEdit();RefreshSessionTimeOut();';
		}
	}
	exit;
}
if (isset($XVARS['EditTemplate'])) {
	$data = array("session" => $SID, "action" => "EDIT", "host" => $userhost, "controlid" => $CID, "docid" => $XVARS['DocID']);
	$data_string = json_encode($data);
	$json = doRIPRequest($data_string, 'GET', 'TEMPLATES/');
	echo 'hideIt("PlsWt");';
	if (!isset($json)) {
		echo 'alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'alert("' . $json['error'] . '");';
		exit;
	} else {
		echo 'uploadedDocs=[];var theDoc=new UploadedDoc;theDoc.pdf="' . $json['graphic'] . '";theDoc.formname="' . $json['form'] . '";theDoc.docid="' . $XVARS['DocID'] . '";theDoc.pkgid="' . $json['pkgid'] . '";';
		echo 'uploadedDocs.push(theDoc);';
		echo 'signers=[];';
		echo 'var newSigner=new Signer;';
		if (!is_array($json['signsets'])) {
			$json['signsets'] = [];
		}
		for ($i = 0; $i < count($json['signsets']); $i++) {
			if ($i != 0) {
				echo 'newSigner=new Signer;';
			}
			echo 'newSigner.signsetid="' . $json['signsets'][$i]['signsetid'] . '";';
			echo 'newSigner.name="' . $json['signsets'][$i]['name'] . '";';
			echo 'newSigner.email="' . $json['signsets'][$i]['email'] . '";';
			echo 'newSigner.selected=true;';
			echo 'newSigner.authcode="' . $json['signsets'][$i]['authcode'] . '";';
			echo 'newSigner.verifyrequired="' . $json['signsets'][$i]['verifyrequired'] . '";';
			echo 'signers.push(newSigner);';
		}
		echo 'MergeSavedSignersWithCurrentList();setSignersIDs();';
		echo 'sigItems=[];var theBox=new SigBox;';
		if (!is_array($json['sigboxes'])) {
			$json['sigboxes'] = [];
		}
		for ($i = 0; $i < count($json['sigboxes']); $i++) {
			if ($i != 0) {
				echo 'theBox=new SigBox;';
			}
			echo 'theBox.xp=' . $json['sigboxes'][$i]['left'] . ';';
			echo 'theBox.yp=' . $json['sigboxes'][$i]['top'] . ';';
			echo 'theBox.wp=' . $json['sigboxes'][$i]['width'] . ';';
			echo 'theBox.hp=' . $json['sigboxes'][$i]['height'] . ';';
			echo 'theBox.type=' . $json['sigboxes'][$i]['esigntype'] . ';';
			echo 'theBox.page=' . $json['sigboxes'][$i]['pagenumber'] . ';';
			echo 'theBox.signerid="' . $json['sigboxes'][$i]['signsetid'] . '";';
			echo 'theBox.signsetid="' . $json['sigboxes'][$i]['signsetid'] . '";';
			echo 'theBox.fieldname="' . $json['sigboxes'][$i]['fieldname'] . '";';
			echo 'theBox.fieldlabel="' . $json['sigboxes'][$i]['fieldlabel'] . '";';
			echo 'theBox.defaultvalue="' . $json['sigboxes'][$i]['defaultvalue'] . '";';
			echo 'theBox.required="' . $json['sigboxes'][$i]['fieldrequired'] . '";';
			echo 'theBox.autofillfield="' . ($json['sigboxes'][$i]['autofillfield'] || '') . '";';
			echo 'theBox.checkedvalue="' . $json['sigboxes'][$i]['checkedvalue'] . '";';
			echo 'theBox.uncheckedvalue="' . $json['sigboxes'][$i]['uncheckedvalue'] . '";';
			echo 'theBox.depfield="' . $json['sigboxes'][$i]['depfield'] . '";';
			echo 'theBox.depfieldvalue="' . $json['sigboxes'][$i]['depfieldvalue'] . '";';
			echo 'theBox.depoperator="' . $json['sigboxes'][$i]['depoperator'] . '";';
			echo 'theBox.id="' . $i . '";';
			echo 'sigItems.push(theBox);';
		}
		echo 'ItemCount=sigItems.length;';
		echo 'DrawingEditDoc=true;LoadDocumentForEdit();RefreshSessionTimeOut();';
	}
	exit;
}
if (isset($XVARS['EditDoc'])) {
	$data = array("session" => $SID, "action" => "EDITDOC", "host" => $userhost, "controlid" => $CID, "docid" => $XVARS['DocID']);
	$data_string = json_encode($data);
	$json = doRIPRequest($data_string, 'GET', 'DFC/');
	echo 'hideIt("PlsWt");';
	if (!isset($json)) {
		echo 'alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'alert("' . $json['error'] . '");';
		exit;
	} else {
		echo 'uploadedDocs=[];var theDoc=new UploadedDoc;theDoc.pdf="' . $json['graphic'] . '";theDoc.formname="' . $json['form'] . '";theDoc.docid="' . $XVARS['DocID'] . '";theDoc.pkgid="' . $json['pkgid'] . '";';
		echo 'uploadedDocs.push(theDoc);';
		echo 'signers=[];';
		echo 'var newSigner=new Signer;';
		if (!is_array($json['signsets'])) {
			$json['signsets'] = [];
		}
		for ($i = 0; $i < count($json['signsets']); $i++) {
			if ($i != 0) {
				echo 'newSigner=new Signer;';
			}
			echo 'newSigner.signsetid="' . $json['signsets'][$i]['signsetid'] . '";';
			echo 'newSigner.name="' . $json['signsets'][$i]['name'] . '";';
			echo 'newSigner.email="' . $json['signsets'][$i]['email'] . '";';
			echo 'newSigner.selected=true;';
			echo 'newSigner.authcode="' . $json['signsets'][$i]['authcode'] . '";';
			echo 'signers.push(newSigner);';
		}
		echo 'MergeSavedSignersWithCurrentList();setSignersIDs();';
		echo 'sigItems=[];var theBox=new SigBox;';
		if (!is_array($json['sigboxes'])) {
			$json['sigboxes'] = [];
		}
		for ($i = 0; $i < count($json['sigboxes']); $i++) {
			if ($i != 0) {
				echo 'theBox=new SigBox;';
			}
			echo 'theBox.xp=' . $json['sigboxes'][$i]['left'] . ';';
			echo 'theBox.yp=' . $json['sigboxes'][$i]['top'] . ';';
			echo 'theBox.wp=' . $json['sigboxes'][$i]['width'] . ';';
			echo 'theBox.hp=' . $json['sigboxes'][$i]['height'] . ';';
			echo 'theBox.type=' . $json['sigboxes'][$i]['esigntype'] . ';';
			echo 'theBox.page=' . $json['sigboxes'][$i]['pagenumber'] . ';';
			echo 'theBox.signerid="' . $json['sigboxes'][$i]['signsetid'] . '";';
			echo 'theBox.signsetid="' . $json['sigboxes'][$i]['signsetid'] . '";';
			echo 'theBox.fieldname="' . $json['sigboxes'][$i]['fieldname'] . '";';
			echo 'theBox.fieldlabel="' . $json['sigboxes'][$i]['fieldlabel'] . '";';
			echo 'theBox.defaultvalue="' . $json['sigboxes'][$i]['defaultvalue'] . '";';
			echo 'theBox.required="' . $json['sigboxes'][$i]['fieldrequired'] . '";';
			echo 'theBox.autofillfield="' . ($json['sigboxes'][$i]['autofillfield'] || '') . '";';
			echo 'theBox.checkedvalue="' . $json['sigboxes'][$i]['checkedvalue'] . '";';
			echo 'theBox.uncheckedvalue="' . $json['sigboxes'][$i]['uncheckedvalue'] . '";';
			echo 'theBox.depfield="' . $json['sigboxes'][$i]['depfield'] . '";';
			echo 'theBox.depfieldvalue="' . $json['sigboxes'][$i]['depfieldvalue'] . '";';
			echo 'theBox.depoperator="' . $json['sigboxes'][$i]['depoperator'] . '";';
			echo 'theBox.id="' . $i . '";';
			echo 'sigItems.push(theBox);';
		}
		echo 'ItemCount=sigItems.length;';
		echo 'DrawingEditDoc=true;LoadDocumentForEdit();RefreshSessionTimeOut();';
	}
	exit;
}
if (isset($XVARS['DeletePackage'])) {
	$data = array("session" => $SID, "pkgid" => $XVARS['PkgID'], "host" => $userhost, "controlid" => $CID);
	$data_string = json_encode($data);
	$json = doRIPRequest($data_string, 'DELETE', 'PACKAGES/');
	echo 'hideIt("PlsWt");';
	if (!isset($json)) {
		echo 'alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'alert("' . $json['error'] . '");';
		exit;
	} else {
		echo 'DoSgnStSearch("",0);RefreshSessionTimeOut();';
	}
	exit;
}
if (isset($XVARS['ClosePackage'])) {
	$data = array("session" => $SID, "pkgid" => $XVARS['PkgID'], "host" => $userhost, "controlid" => $CID);
	$data_string = json_encode($data);
	$json = doRIPRequest($data_string, 'CLOSE', 'PACKAGES/');
	echo 'hideIt("PlsWt");';
	if (!isset($json)) {
		echo 'alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'alert("' . $json['error'] . '");';
		exit;
	} else {
		echo 'DoSgnStSearch("",0);RefreshSessionTimeOut();';
	}
	exit;
}
if (isset($XVARS['ResetPackage'])) {
	$data = array("session" => $SID, "pkgid" => $XVARS['PkgID'], "host" => $userhost, "controlid" => $CID);
	$data_string = json_encode($data);
	$json = doRIPRequest($data_string, 'RESET', 'PACKAGES/');
	echo 'hideIt("PlsWt");';
	if (!isset($json)) {
		echo 'alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'alert("' . $json['error'] . '");';
		exit;
	} else {
		echo 'DoSgnStSearch("",0);RefreshSessionTimeOut();';
	}
	exit;
}
if (isset($XVARS['SendTicketForSignSet'])) {
	$data = array("session" => $SID, "signsetid" => $XVARS['SignSetID'], "host" => $userhost, "controlid" => $CID);
	$data_string = json_encode($data);
	$json = doRIPRequest($data_string, 'TICKET', 'SIGNSETS/');
	echo 'hideIt("PlsWt");';
	if (!isset($json)) {
		echo 'alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'alert("' . $json['error'] . '");';
		exit;
	} else {
		echo 'hideIt("EditSignerDlg");RefreshSessionTimeOut();';
	}
	exit;
}
if (isset($XVARS['SendAuthCodeForSignSet'])) {
	$data = array("session" => $SID, "signsetid" => $XVARS['SignSetID'], "host" => $userhost, "controlid" => $CID);
	$data_string = json_encode($data);
	$json = doRIPRequest($data_string, 'SENDAUTHCODE', 'SIGNSETS/');
	echo 'hideIt("PlsWt");';
	if (!isset($json)) {
		echo 'alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'alert("' . $json['error'] . '");';
		exit;
	} else {
		echo 'hideIt("EditSignerDlg");RefreshSessionTimeOut();';
	}
	exit;
}
if (isset($XVARS['EmailDoc'])) {
	$data = array("session" => $SID, "email" => $XVARS['Email'], "docid" => $XVARS['DocID'], "host" => $userhost, "controlid" => $CID);
	$data_string = json_encode($data);
	$json = doRIPRequest($data_string, 'SENDEMAIL', 'DFC/');
	echo 'hideIt("PlsWt");';
	if (!isset($json)) {
		echo 'alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'alert("' . $json['error'] . '");';
		exit;
	} else {
		echo 'RefreshSessionTimeOut();';
	}
	exit;
}
if (isset($XVARS['SavePckSigners'])) {
	$tmparr = $XVARS;
	foreach ($tmparr as $ind => $val) {
		//while(list($ind,$val)=each($tmparr)){
		if (($ind != 'SID') && ($ind != 'SavePckSigners') && ($ind != 'DocID') && ($ind != 'PkgID') && ($ind != 'NewDocName') && ($ind != 'NewName') && ($ind != 'CID')) {
			$signerlist[] = $ind . ';' . $val;
		}
	}
	$NewName = (isset($XVARS['NewName'])) ? $XVARS['NewName'] : "";
	$NewDocName = (isset($XVARS['NewDocName'])) ? $XVARS['NewDocName'] : "";
	$data = array("session" => $SID, "docid" => $XVARS['DocID'], "pkgid" => $XVARS['PkgID'], "newdocname" => $NewDocName, "newname" => $NewName, "action" => "EDITSIGNERS", "host" => $userhost, "controlid" => $CID, "signerlist" => $signerlist);
	$data_string = json_encode($data);
	$json = doRIPRequest($data_string, 'GET', 'SIGNSETS/');
	echo 'hideIt("PlsWt");';
	if (!isset($json)) {
		echo 'alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'alert("' . $json['error'] . '");';
		exit;
	} else {
		if ($NewName != "") {
			echo 'UpdatePackageName("' . $XVARS['PkgID'] . '","' . $NewName . '");';
		}
		if ($NewDocName != "") {
			echo 'uploadedDocs[0].formname="' . $NewDocName . '";';
		}
		echo 'RefreshSessionTimeOut();';
	}
	exit;
}
if (isset($XVARS['SaveTemplateSigners'])) {
	$tmparr = $XVARS;
	foreach ($tmparr as $ind => $val) {
		//while(list($ind,$val)=each($tmparr)){
		if (($ind != 'SID') && ($ind != 'SaveTemplateSigners') && ($ind != 'DocID') && ($ind != 'PkgID') && ($ind != 'NewDocName') && ($ind != 'NewName') && ($ind != 'CID')) {
			$signerlist[] = $ind . ';' . $val;
		}
	}
	$NewName = (isset($XVARS['NewName'])) ? $XVARS['NewName'] : "";
	$NewDocName = (isset($XVARS['NewDocName'])) ? $XVARS['NewDocName'] : "";
	$data = array("session" => $SID, "docid" => $XVARS['DocID'], "pkgid" => $XVARS['PkgID'], "newdocname" => $NewDocName, "newname" => $NewName, "action" => "EDITSIGNERS", "host" => $userhost, "controlid" => $CID, "signerlist" => $signerlist);
	$data_string = json_encode($data);
	$json = doRIPRequest($data_string, 'GET', 'TEMPLATES/');
	echo 'hideIt("PlsWt");';
	if (!isset($json)) {
		echo 'alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'alert("' . $json['error'] . '");';
		exit;
	} else {
		if ($NewName != "") {
			echo 'UpdatePackageName("' . $XVARS['PkgID'] . '","' . $NewName . '");';
		}
		if ($NewDocName != "") {
			echo 'uploadedDocs[0].formname="' . $NewDocName . '";';
		}
		echo 'RefreshSessionTimeOut();';
	}
	exit;
}
if (isset($XVARS['GetDocSigners'])) {
	$data = array("session" => $SID, "action" => "SIGNERS", "pkgid" => $XVARS['PkgID'], "host" => $userhost, "controlid" => $CID);
	$data_string = json_encode($data);
	$json = doRIPRequest($data_string, 'GET', 'PACKAGES/');
	echo 'hideIt("PlsWt");';
	if (!isset($json)) {
		echo 'alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'alert("' . $json['error'] . '");';
		exit;
	}
	if (!is_array($json['signers'])) {
		$json['signers'] = [];
	}
	if (count($json['signers']) > 0) {
		for ($i = 0; $i < count($json['signers']); $i++) {
			echo 'SetSignerSignSetID("' . $json['signers'][$i]['name'] . '","' . $json['signers'][$i]['email'] . '","' . $json['signers'][$i]['id'] . '");';
		}
	}
	echo 'ShowEditDocSignerDlg();RefreshSessionTimeOut();';
	exit;
}
if (isset($XVARS['GetSigners'])) {
	$data = array("session" => $SID, "action" => "SIGNERS", "pkgid" => $XVARS['PkgID'], "host" => $userhost, "controlid" => $CID);
	$data_string = json_encode($data);
	$json = doRIPRequest($data_string, 'GET', 'PACKAGES/');
	echo 'hideIt("PlsWt");';
	if (!isset($json)) {
		echo 'alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'alert("' . $json['error'] . '");';
		exit;
	}
	if (!is_array($json['signers'])) {
		$json['signers'] = [];
	}
	if (count($json['signers']) == 0) {
		echo 'alert("No Signers Found");';
		exit;
	} else {
		echo 'var sgnr=new Signer;PckgSigners=[];';
		for ($i = 0; $i < count($json['signers']); $i++) {
			if ($i != 0) {
				echo 'sgnr=new Signer;';
			}
			echo 'sgnr.name="' . $json['signers'][$i]['name'] . '";';
			echo 'sgnr.email="' . $json['signers'][$i]['email'] . '";';
			echo 'sgnr.authcode="' . $json['signers'][$i]['authcode'] . '";';
			echo 'sgnr.authcode="' . $json['signers'][$i]['authcode'] . '";';
			echo 'sgnr.verifyrequired="' . $json['signers'][$i]['verifyrequired'] . '";';
			echo 'PckgSigners.push(sgnr);';
		}
		echo 'ShowEditSignerDlg();RefreshSessionTimeOut();';
	}
	exit;
}
if (isset($XVARS['TemplateSearch'])) {
	$data = array("session" => $SID, "orderby" => $XVARS['ORDERBY'], "action" => "SEARCH", "search" => $XVARS['TemplateSearch'], "host" => $userhost, "controlid" => $CID, "fromdate" => $XVARS['StDt'], "todate" => $XVARS['EnDt']);
	$data_string = json_encode($data);
	$json = doRIPRequest($data_string, 'GET', 'TEMPLATES/');
	echo 'packages=[];hideIt("PlsWt");';
	if (!isset($json)) {
		echo 'alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'DrawTemplates();alert("' . $json['error'] . '");';
		exit;
	}
	if (!is_array($json['templates'])) {
		$json['templates'] = [];
	}
	if (count($json['templates']) == 0) {
		echo 'DrawTemplates();';
		exit;
	} else {
		for ($i = 0; $i < count($json['templates']); $i++) {
			echo 'var thePackage=new Package;';
			echo 'thePackage.id="' . $json['templates'][$i]['id'] . '";';
			echo 'thePackage.name="' . $json['templates'][$i]['name'] . '";';
			echo 'thePackage.created="' . $json['templates'][$i]['created'] . '";';
			echo 'thePackage.triggers=[];packages.push(thePackage);';
		}
		echo 'DrawTemplates();RefreshSessionTimeOut();';
	}
	exit;
}
if (isset($XVARS['Search'])) {
	$data = array("session" => $SID, "createdby" => $XVARS['PkgUsr'], "orderby" => $XVARS['ORDERBY'], "action" => "SEARCH", "formname" => $XVARS['DocName'], "search" => $XVARS['Search'], "host" => $userhost, "controlid" => $CID, "fromdate" => $XVARS['StDt'], "todate" => $XVARS['EnDt']);
	$data_string = json_encode($data);
	$json = doRIPRequest($data_string, 'GET', 'PACKAGES/');
	echo 'packages=[];hideIt("PlsWt");';
	if (!isset($json)) {
		echo 'alert("Server Down");';
		exit;
	}
	logToFile(print_r($json, true));
	if (!$json['result']) {
		echo 'DrawPackages();alert("' . $json['error'] . '");';
		exit;
	}
	if (!isset($json['packages'])) {
		$json['packages'] = [];
	}
	if (count($json['packages']) == 0) {
		echo 'DrawPackages();';
		exit;
	} else {
		for ($i = 0; $i < count($json['packages']); $i++) {
			echo 'var thePackage=new Package;';
			echo 'thePackage.triggers=[];thePackage.id="' . $json['packages'][$i]['id'] . '";';
			echo 'thePackage.name="' . $json['packages'][$i]['name'] . '";';
			echo 'thePackage.created="' . $json['packages'][$i]['created'] . '";';
			echo 'thePackage.createdby="' . $json['packages'][$i]['createdby'] . '";';
			echo 'thePackage.fullname="' . $json['packages'][$i]['fullname'] . '";';
			echo 'thePackage.status="' . $json['packages'][$i]['status'] . '";';
			for ($j = 0; $j < count($json['packages'][$i]['documents']); $j++) {
				echo 'var theDoc=new PkgDoc;';
				echo 'theDoc.docid="' . $json['packages'][$i]['documents'][$j]['docid'] . '";';
				echo 'theDoc.form="' . $json['packages'][$i]['documents'][$j]['form'] . '";';
				echo 'theDoc.status="' . $json['packages'][$i]['documents'][$j]['status'] . '";';
				echo 'theDoc.packageid="' . $json['packages'][$i]['documents'][$j]['packageid'] . '";';
				echo 'theDoc.created="' . $json['packages'][$i]['documents'][$j]['created'] . '";';
				echo 'theDoc.createdby="' . $json['packages'][$i]['documents'][$j]['createdby'] . '";';
				echo 'theDoc.fullname="' . $json['packages'][$i]['documents'][$j]['fullname'] . '";';
				echo 'thePackage.docs.push(theDoc);';
			}
			echo 'packages.push(thePackage);';
		}
		echo 'DrawPackages();RefreshSessionTimeOut();';
	}
	exit;
}
