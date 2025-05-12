<?php
header('Content-type: text/html; charset=utf-8');
require_once realpath(__DIR__) . '/common/commonfunctions.php';
require_once realpath(__DIR__) . '/config.php';
require_once realpath(__DIR__) . '/login/login_common.php';
if (isset($_FILES) && count($_FILES) > 0) {
	$response = '';
	logToFile(print_r($XVARS['JSON'], true));
	if (isset($XVARS['JSON'])) {
		$jsonRes = json_decode($XVARS['JSON'], true);
		if (isset($jsonRes['handoff'])) {
			$CID = $jsonRes['controlid'];
			$data = array("edocsig" => $jsonRes['handoff'], "action" => "EDOCSIG", "host" => $userhost, "controlid" => $jsonRes['controlid']);
			$data_string = json_encode($data);
			$json = doRIPRequest2($data_string, 'POST', 'SESSIONS/');
			if (!isset($json)) {
				echo '{"result":false,"error":"Error during handoff"}';
				exit;
			} else {
				if ($json["result"]) {
					$SID = $json['session'];
					$XVARS['SID'] = $json['session'];
					$response = ',"session":"' . $SID . '"';
				} else {
					echo '{"result":false,"error":"Error during handoff"}';
					exit;
				}
			}
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
		echo '{"result":true,"name":"' . $uniqnm . '"' . $response . '}';
	} else {
		echo '{"result":false,"error":"' . $msg . '"}';
	}
	logToFile('Exiting');
	exit;
}
require_once realpath(__DIR__) . "/common/headers.php";
$yesterday  = date("m/d/Y", mktime(0, 0, 0, date("m"), (date("d") - 1),   date("Y")));
$threedaysago  = date("m/d/Y", mktime(0, 0, 0, date("m"), (date("d") - 3),   date("Y")));
$lastweek = date("m/d/Y", mktime(0, 0, 0, date("m"), (date("d") - 7),   date("Y")));
$today  = date("m/d/Y");
$EMSG = isset($XVARS['EMSG']) ? $XVARS['EMSG'] : '';
$ADStartUp = '';
$LoginClass = '';
if ($EMSG != '') {
	$ADStartUp = 'showError("' . $EMSG . '");';
}
$host = $_SERVER['HTTP_HOST'];
$userhost = getUserHost();
$tempDir = isset($cfg['db']['tempdir']) ? $cfg['db']['tempdir'] : "temp/";

// # TODO: None of these php variables are used here, verify if it's okay to remove
$logoffurl = getSetting('eDOCSignatureLogoffURL');
$Limit = getSetting('ResultLimit');
$ShowBackBtn = getSetting('ShowBackBtnOnHandoff');
if (!isset($ShowBackBtn)) {
	$ShowBackBtn = true;
}
if ($Limit == "") {
	$Limit = 10;
}
if (!isset($logoffurl)) {
	$logoffurl = 'index.php';
}

// Set origin nav for redirecting methods
getOriginNav(__DIR__);
global $origin_nav;

if (isset($XVARS['EDOCSIG'])) {
	// Validate EDOCSIG queried session
	$data = array("action" => "EDOCSIG", "edocsig" => urlencode($XVARS['EDOCSIG']), "host" => $userhost, "controlid" => $CID);
	$data_string = json_encode($data);
	$json = doRIPActionRequest($data_string, 'POST', 'SESSIONS/', 'REST/');
	if (!isset($json)) {
		logToFile("eDOCSignature session request response json is null");
		returnError('Login Server Down');
	}
	if (!$json["result"]) {
		logToFile("eDOCSignature session request response result is false");
		$EDOCSIG_SESSION_VALIDATION_ERROR = "Error during edocsig session validation";
		returnError($json['systemerror'] && $json['error'] ? "$EDOCSIG_SESSION_VALIDATION_ERROR: " . $json['error'] : $EDOCSIG_SESSION_VALIDATION_ERROR);
	}
} else if ($SID != '') {
	// Validate the session
	$json = doRIPActionRequest('', 'GET', 'SESSIONS/' . $SID . '?controlid=' . $CID . '&host=' . $userhost, 'REST/');
	if (!isset($json)) {
		logToFile("eDOCSignature session request response json is null");
		returnError('Login Server Down');
	}
	if (!$json["result"]) {
		logToFile("eDOCSignature session request response result is false");
		global $GENERIC_SESSION_VALIDATION_ERROR;
		returnError($json['systemerror'] && $json['error'] ? "$GENERIC_SESSION_VALIDATION_ERROR: " . $json['error'] : $GENERIC_SESSION_VALIDATION_ERROR);
	}
} else {
	// Redirect by posting query params to login page
	redirectWithMethodPost($origin_nav . 'index.php', $XVARS);
}

// Save post-login handoff related parameters to the session
savePostLoginHandoffParams();

// Handle redirect-to-login scenarios
if ($json["forcechange"]) {
	// # TODO: Refactor processLoginJSON to extract a method we can use here
	returnError("You need to change your password.  Please click the forgot password link to recieve and email with instructions for changing your password.");
}
$fullName = $json['fullname'];
$permissions = $json['permissions'];
$user = $json['username'];
$controlIds = $json['controlids'];
$SID = $json['session'];
$CID = $json['controlid'];
if (!userhasperm("eDOCSig Manage Documents") && !userhasperm("eDOCSig Send Document")) {
	returnError('You do not have permission to use eDOCSignature');
}
// Handle redirecting for package completed handoff and salesforce
handlePostLoginHandoffRedirect();

// Account for eDOCSignature.php-exclusive query params (Are these exclusive?)
$url = "landing.php";
if (isset($XVARS['NAV'])) {
	if ($XVARS['NAV'] == "SEND_DOC") {
		$url = "senddoc.php";
	}
	if ($XVARS['NAV'] == "MNG_DOCS") {
		$url = "managedoc.php";
	}
	if ($XVARS['NAV'] == "MNG_SETTINGS") {
		$url = "settings.php";
	}
	if ($XVARS['NAV'] == "eDOC_It") {
		$XVARS['SID'] = $json['session'];
		ShoweDOCItUploadLandingPage();
		exit;
	}
	logToFile("Redirecting to NAV: $url");
}
redirectWithMethodPost($origin_nav . $url, ['SID' => $SID, 'CID' => $CID, 'NAV' => $XVARS['NAV']]);

function ShoweDOCItUploadLandingPage()
{
	global $XVARS;
	global $permissions;
	$refDocsEnabled = getSetting('EnableReferenceDocs');
	$refDocEnblText = ($refDocsEnabled) ? '' : ' class="hidden"';
	$templateEnabled = getSetting('ShowTemplateSetup');
	$remplateEnblText = '';
	if (!$templateEnabled) {
		$remplateEnblText = ' class="hidden"';
	}

	require_once("common/session.php");

	require_once("senddoc/review.php");
	echo '<html><head>';
	require_once("senddoc/uploaddoc.php");
	echo '</head><style>';
	echo '	.ManageTemplates{
				font-size:24px;
				border:solid 1px grey;
				width:315px;
				height:80px;
				cursor:pointer;
				text-align:center;
			}
			.PkgSlctCell{
				width:790px;
				height:420px;
				overflow-y:scroll;
			}
			.TemplatesLink{
				font-size:24px;
				border:solid 1px grey;
				width:315px;
				height:80px;
				cursor:pointer;
				text-align:center;
			}
			.SearchSigningsLink{
				font-size:24px;
				border:solid 1px grey;
				width:315px;
				height:80px;
				cursor:pointer;
				text-align:center;
			}
			.CreateSigningDocLink{
				font-size:24px;
				border:solid 1px grey;
				width:315px;
				height:80px;
				cursor:pointer;
				text-align:center;
			}
			.CreateReferenceDocLink{
				font-size:24px;
				border:solid 1px grey;
				width:315px;
				height:80px;
				cursor:pointer;
				text-align:center;
			}
		</style>
		<script>';
	echo '
		var newFile = "' . $XVARS['TheFile'] . '";
		var newFileName = "' . $XVARS['OrgFile'] . '";
		function enableButtons(){

		}
		function disableButtons(){

		}
		async function getTemplateTypeFilterListEDOCIT(newDoc, type, filter, reload = true) {
			if (typeof newDoc == "undefined") {
				newDoc = useNewDoc;
			}
			if (typeof type == "undefined") {
				type = lastTempType;
			}
			if (typeof filter == "undefined") {
				filter = "";
			}
			useNewDoc = newDoc;
			if ((templates.length > 0) && (type == lastTempType)) {
				drawChooseTemplate2Modal();
			} else {
				lastTempType = type;
				if (!tagsLoaded) {
					tagsCBFunction = "getTemplateTypeFilterListEDOCIT";
					searchTags("");
					return;
				}
				templates = [];
				var categories = [];
				if (filter != "") {
					categories.push(filter);
				}
				var temp = {
					session: SID,
					controlid: CID,
					categories: categories,
					resource: "TEMPLATES",
					type: type,
					action: "GETLIST"
				};
				pleaseWait("Getting list of templates");
				let response = await axios({
					method: "post",
					url: "common/rest.php",
					data: temp,

				})
				processGetTemplateTypeFilterListEDOCITJSON(response.data, reload)
			}
		}

		var lastFilterTag="";

		function drawTags() {
			let filterTagContainer = document.getElementById("filterMenuDropdownEDOCIT");
			filterTagContainer.innerHTML = "";

			let page = `<div class="text-center">
							<div class="FilterSearch">
								<h6>Filter Search</h6>
								<hr class="dropdown-divider" />
							</div>`;
			for (let tag of Tags) {
				page += `<li>
							<a class="dropdown-item">
								<div class="row" id=${tag.id}>
									<div class="col-2">
										<input type="checkbox" onclick="filterTags(\'${tag.id}\')" name="${tag.name}" class="form-check-input" value="${tag.id}"/>
									</div>
									<div class="col-10">
										${tag.name}
									</div>
								</div>
							</a>
						</li>
						<hr class="dropdown-divider"/>`;
			}
			page += "</div>";

			filterTagContainer.innerHTML = page;
		}

		function processGetTemplateTypeFilterListEDOCITJSON(theJSON, reload) {
			pleaseWait("");
			if (!theJSON.result) {
				logout(theJSON.error);
			} else {
				if (theJSON.templates.length > 0) {
					for (let template of theJSON.templates) {
						templates.push({
							id: template.id,
							name: template.name,
							type: template.type,
							redirecturl: template.redirecturl,
							message: template.message
						});
					}
					if (reload) {
						drawChooseTemplate2Modal();
					}
				} else {
					lastFilterTag = "";
					modalFactory("NoTemplatesModal").show();
				}
			}
		}

		function drawChooseTemplate2Modal() {
			let page = "";
			let tempsdlg = document.getElementById("templateseldlgEDOCIT");
			tempsdlg.html = "";

			let container = document.getElementById("selectedFiltersContainerEDOCIT");
			container.html = "";

			// Draws the templates in the dropdown
			for (let template of templates) {
				page += `<option value="${template.id}">${template.name}</option>`;
			}
			tempsdlg.innerHTML = page;
			modalFactory("ChooseTemplateUploadModalEDOCIT").show();
			drawTags();
		}


		// Called when a checkbox is clicked
		function filterTags(tagid) {
			selectedTags = [];

			// Get checked boxes
			let checkedOptions = $("#filterMenuDropdownEDOCIT").find("input:checked");
			for (let option of checkedOptions) {
				let tag = {
					name: "",
					id: ""
				}
				tag.name = option.name;
				tag.id = option.parentElement.parentElement.id;
				selectedTags.push(tag);

			}
			drawFilterTags();

		}

		// Draws the filter icons in the selected filters section
		function drawFilterTags() {
			let filterHolder = document.getElementById("selectedFiltersContainerEDOCIT");
			filterHolder.innerHTML = "";

			for (let tag of selectedTags) {
				let drawnTag = document.createElement("div");
				drawnTag.classList.add("mb-2");
				drawnTag.innerHTML = `<div class="tags" name="${tag.name}" id="${tag.id}">${tag.name}</div>`
				filterHolder.appendChild(drawnTag);
			}
			let type = "Signing"
			let filterTags = selectedTags.map(tag => tag.id);
			filterTagsCall(type, filterTags);

		}

		function filterTagsCall(type, tags) {
			templates = [];
			let temp = {
				session: SID,
				controlid: CID,
				categories: tags,
				resource: "TEMPLATES",
				type: type,
				action: "GETLIST"
			};
			pleaseWait("Getting list of templates");
			axios({
				method: "post",
				url: "common/rest.php",
				data: temp
			}).then(function(response) {
				pleaseWait("");
				let theData = response.data;
				if (!theData.result) {
					logout(theData.error);
				} else {
					if (theData.templates.length > 0) {
						for (let template of theData.templates) {
							templates.push({
								id: template.id,
								name: template.name,
								type: template.type,
								redirecturl: template.redirecturl,
								message: template.message
							});
						}
						let templatesDropDown = document.getElementById("templateseldlgEDOCIT");
						templatesDropDown.html = "";
						let page = "";
						for (let template of templates) {
							page += `<option value="${template.id}">${template.name}</option>`;
						}
						$("#templateseldlgEDOCIT").html(page);
					} else {
						modalFactory("NoTemplatesModal").show();
					}
				}
			});
		}

		function loadTemplateeDocIt(){
			var eltm = get("templateseldlgEDOCIT").value;
			post("senddoc.php",{SendTemp:eltm,SID:SID,CID:CID,PkgID:pkgID,PkgName:pkgName,NewFile:newFile,NewFileName:newFileName});
		}

		function drawChooseTemplateModal() {
			let page = "";
			// Draws the templates in the dropdown
			for (let template of templates) {
				page += `<option value="${template.id}">${template.name}</option>`;
			}
			let tempdlg = document.getElementById("templateseldlgEDOCIT");
			tempdlg.html = page;
			modalFactory("ChooseTemplateUploadModalEDOCIT").show();
			drawTags();
		}
		function checkNewUpDocName(){
			pkgName=get("PackNameEdt").value;
			if(pkgName==""){
				showError("Please provide a package name",get("PackNameEdt"));
				return false;
			}
			var msg=validPkgName(pkgName);
			if(msg!=""){showError(msg, get("PackNameEdt"));return false;}
			msg = validDocName(newFileName);
			if(msg!=""){showError(msg);return false;}
			return true;
		}

		function checkDocumentName(){
			var msg = validDocName(newFileName);
			if(msg!=""){showError(msg);return false;}
			return true;
		}

		function updateFileName(){
			newFileName=get("NewUpDocName").value;
		}
		function updatePkgName(){
			pkgName=get("PackNameEdt").value;
		}
		function selectPkgID(num) {
			pkgID = packages[num].pkgid;
			modalFactory("AddToExistingPackageModal").hide();
			get("UseDef").className = "";
			get("UseExi").className = "hidden";
			get("PackNameEdt").disabled = true;
			get("PackNameEdt").value = packages[num].name;
			pkgName = packages[num].name;
			getSignersForPackage(pkgID, doProcessGotSigners);
		}
		$(window).on("load",function() {
			setMenuTitle("Welcome to eDOCSignature");
			loadGoDSeal();
			setupSession();
			SID="' . $XVARS['SID'] . '";
			CID="' . $XVARS['CID'] . '";
			helpPage = helpPageURL?.pages?.edocit ?? "";
			get("NewUpDocName").value=newFileName;
			pkgID=generateGUID();
		});
		</script>
	</head>
	<body id="body">
		<table cellpadding="0" cellspacing="0" width="100%" height="100%">
			<tr>
				<td height="50px">';
	require_once("common/menu.php");
	echo '		</td>
			</tr>
			<tr>
				<td align="center" valign="top">
					<table width="100%" cellspacing="0" cellpadding="0">
						<tr id="LandingRow" style="display: table-row;">
							<td class="LandingCell" id="LandingCell" valign="top">
								<table align="center" style="min-width:793px;">
									<tr>
										<td align="center" height="10"></td>
									</tr>
									<tr>
										<td align="center" style="width:400;font-size:16px;font-weight:300">Document uploaded, what would you like to do with it?</td>
									</tr>
									<tr>
										<td align="center" height="10"></td>
									</tr>
									<tr>
										<td align="center" height="50px">
											<table cellpadding="0" cellspacing="0" width="100%">
												<tr>
													<td align="left" width="165px" class="instructionsheader">Document Name:</td>
													<td width="350px" align="left"><input size="50" class="form-control" type="text" name="NewUpDocName" id="NewUpDocName" onchange="updateFileName();"></td>
													<td>&nbsp;</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td align="center" height="10"></td>
									</tr>
									<tr>
										<td height="50px" align="center">
											<table cellpadding="0" cellspacing="0" width="100%">
												<tbody>
													<tr>
														<td align="left" width="165px" class="instructionsheader">Package Name: </td>
														<td align="center" valign="middle" height="50px" width="350px"><input autocomplete="off" onchange="updatePkgName()" name="PackNameEdt" class="form-control" id="PackNameEdt" size="50" value="" type="text" height="50px"></td>
														<td align="center" id="UseDef" width="150" class="hidden">
															<button type="button" style="width:120" id="UseDefaultBtn" class="ediBtn navBack" onclick="setPkgDefault()">Reset</button>
														</td>
														<td id="UseExi" onclick="getPackageList();"><table cellpadding="0" cellspacing="0"><tr>
															<td width = "20px"> &nbsp; </td>
															<td width="50px" class="cursor"><i class="fal fa-folder-plus FAFolderIcon"></i></td>
															<td width = "10px"> &nbsp; </td>
															<td class="instructions"> Add to existing Package </td>
														</tr></table></td>
														<td>&nbsp;</td>
													</tr>
												</tbody>
											</table>
										</td>
									</tr>
									<tr>
										<td class="CreateSigningDocLink" id="SndForSgnBtn" onclick=\'if(checkNewUpDocName()){post("senddoc.php",{DU:"Y",SID:SID,CID:CID,PkgID:pkgID,PkgName:pkgName,NewFile:newFile,NewFileName:newFileName});}\' valign="middle"><i class="fas fa-file-arrow-up FAStandardIcon" style="float:left;padding-left:20px;"></i><span width="20px">&nbsp;</span>Send for Signing</td>
									</tr>
									<tr ' . $refDocEnblText . '>
										<td align="center" height="10"></td>
									</tr>
									<tr' . $refDocEnblText . '>
										<td class="CreateReferenceDocLink" id="SndForRefBtn" onclick=\'if(checkNewUpDocName()){post("senddoc.php",{DRefU:"Y",SID:SID,CID:CID,PkgID:pkgID,PkgName:pkgName,NewFile:newFile,NewFileName:newFileName});}\' valign="middle"><i class="fas fa-file FAStandardIcon" style="float:left;padding-left:20px;color:#40B445;"></i><span width="20px">&nbsp;</span>Send as Reference Document</td>
									</tr>
									<tr' . $remplateEnblText . '>
										<td align="center" height="10"></td>
									</tr>
									<tr' . $remplateEnblText . '>
										<td class="ManageTemplates" id="CrtTmplateBtn" onclick=\'if(checkDocumentName()){post("setuptemplate.php",{DU:"Y",SID:SID,CID:CID,NewFile:newFile,NewFileName:newFileName});}\' valign="middle"><i class="fas fa-file-invoice FAStandardIcon" style="float:left;padding-left:20px;"></i><span width="20px">&nbsp;</span>Create Template</td>
									</tr>
									<tr' . $remplateEnblText . '>
										<td align="center" height="10"></td>
									</tr>
									<tr' . $remplateEnblText . '>
										<td class="TemplatesLink" id="SendUsgTmpBtn" onclick=\'if(checkNewUpDocName()){getTemplateTypeFilterListEDOCIT();}\' valign="middle"><i class="fas fa-file-invoice FAStandardIcon" style="float:left;padding-left:20px;"></i><span width="20px">&nbsp;</span>Send Using a Template</td>
									</tr>
									<tr>
										<td align="center">&nbsp;</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td height="60px">';
	require_once('common/footer.php');
	require_once("common/snackbar.php");
	require_once('common/messages.php');
	echo '		</td>
			</tr>
		</table>
	</body>
	</html>';
}
