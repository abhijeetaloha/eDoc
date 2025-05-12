<?php
header("Access-Control-Allow-Origin: *");
require_once('common/commonfunctions.php');
require_once('config.php');
require_once('posttemplate.php');
require_once('common/idprovider.php');

getOriginNav(__DIR__);

//Process posted files (and set session data from it)
if (isset($XVARS['JSON'])) {
	$error = '';
	if (isset($_FILES) && count($_FILES) > 0) {
		$retJSON = json_decode(handlePostedFile(), true);
		logToFile('$retJSON: ' . print_r($retJSON, true));
		if (!$retJSON['result']) {
			$error = $retJSON['error'];
		} else {
			$XVARS = json_decode(urldecode($XVARS['JSON']), true);
			$XVARS['file'] = $retJSON['name'];
		}
	} else {
		$XVARS = json_decode(urldecode($XVARS['JSON']), true);
		$SID = $XVARS['session'];
		$CID = $XVARS['controlid'];
	}

	if ($error != "") {
		returnError($error);
	}
}

checkLogin();

$tempDir = isset($cfg['db']['tempdir']) ? $cfg['db']['tempdir'] : "temp/";
$localTempDir = isset($cfg['db']['localtempdir']) ? $cfg['db']['localtempdir'] : $tempDir;
$microservicesURL = isset($cfg['db']['eServices']) ? $cfg['db']['eServices'] : $cfg['db']['MicroservicesURL'];


?>
<html>

<head>
	<?php require_once("common/headers.php"); ?>
	<style>
		.PkgSlctCell {
			width: 790px;
			height: 420px;
			overflow-y: scroll;
		}

		.IndxScroll {
			width: 570px;
			height: 250px;
			overflow-y: scroll;
			border: 1px solid #AAA;
		}

		.scrollWindow {
			width: 416px;
			height: 385px;
			overflow-y: scroll;
			border: 1px solid #AAA;
		}

		.CkdBox {
			border: solid 1px #05a8ff;
			border-radius: 2px;
			height: 55px;
			width: auto;
			margin: 0px 11px 5px 11px;
		}
	</style>
	<script src="workflows/SearchWorkflows.js?id=<?= $vrs ?>"></script>
	<script src="workflows/AddWorkflow.js?id=<?= $vrs ?>"></script>
	<script src="common/formsandinputs.js?id=<?= $vrs ?>"></script>
	<script src="common/dragdoc.js?id=<?= $vrs ?>"></script>
	<script src="common/setup.js?id=<?= $vrs ?>"></script>
	<script src="pending/pendingdocs.js?id=<?= $vrs ?>"></script>
	<script src="common/editIndices.js?id=<?= $vrs ?>"></script>
	<link href="common/formsandinputs.css?id=<?= $vrs ?>" rel="stylesheet" type="text/css">
	<?php require_once("common/session.php"); ?>
	<?php require_once("user/configvars.php"); ?>
	<script>
		var tempDir = "<?php echo $tempDir; ?>";
		var localTempDir = "<?php echo $localTempDir; ?>";
		var pkgID = "<?= isset($XVARS['PkgID']) ? $XVARS['PkgID'] : (isset($XVARS['PKGID']) ? $XVARS['PKGID'] : '') ?>";
		var pkgName = "<?= isset($XVARS['PkgName']) ? $XVARS['PkgName'] : (isset($XVARS['PKGNAME']) ? $XVARS['PKGNAME'] : '')  ?>";
		var entityID = "<?= isset($XVARS['EntityID']) ? $XVARS['EntityID'] :  ''; ?>";
		var entityType = "<?= isset($XVARS['EntityType']) ? $XVARS['EntityType'] :  ''; ?>";
		var goTo = "";
		var microservicesUrlSet = <?php echo !empty($microservicesURL) ? "true" : "false"; ?>;
		var showDataEntry = <?php echo (getSetting('ShowDataEntry')) ? "true" : "false"; ?>;
		var showNewElements = <?php echo (getSetting('ShowNewElements')) ? "true" : "false"; ?>;
		var showeSigners = <?php echo (getSetting('ShoweSigners')) ? "true" : "false"; ?>;
		var showSignerGroups = <?php echo (getSetting('ShowSignerGroups')) ? "true" : "false"; ?>;
		var showSharing = <?php echo (getSetting('ShowSharing')) ? "true" : "false"; ?>;
		var showIndices = <?php echo (getSetting('ShowIndices')) ? "true" : "false"; ?>;
		var showAuthCode = <?php echo (getSetting('HideAuthCodes')) ? "false" : "true"; ?>;
		var supportFillableFields = <?php echo (getSetting('SupportFillableFields')) ? "true" : "false"; ?>;
		var requireAuthCode = <?php echo (getSetting('RequireAuthCode')) ? "true" : "false"; ?>;
		var blankAuthCodes = <?php echo (getSetting('BlankAuthCodes')) ? "true" : "false"; ?>;
		var enableHelcimPayments = <?php echo (getSetting('EnableHelcimPayments')) ? "true" : "false"; ?>;
		var enablePersonalMessaging = <?php echo (getSetting('EnablePersonalMessaging')) ? "true" : "false"; ?>;
		var showDelegate = <?php echo (userhasperm("eDOCSig Allow Delegation")) ? "true" : "false"; ?>;
		const shouldValidateEmail = <?php echo (getSetting('EmailValidation')) ? "true" : "false"; ?>;
		let targetTables = [];

		<?php $tables = getSetting('TargetTables');
		if (!isset($tables)) {
			$tables = [];
		}
		for ($i = 0; $i < count($tables); $i++) {
			echo 'targetTables.push("' . $tables[$i] . '");';
		} ?>
		var smpData = [];
		var loadFiles = [];
		var authCodeHTMLClass = '';
		var templatesArray = [];
		var curTemplate = 0;
		var ssIndexData = [];
		var sourceApp = "<?= isset($XVARS['SOURCEAPP']) ? $XVARS['SOURCEAPP'] : ''; ?>";
		var addRefOnly = <?= $addrefonly ?>;
		MAX_FILE_SIZE = <?php if (isset($cfg['db']['MaxUploadSize'])) {
							echo $cfg['db']['MaxUploadSize'] ?? '';
						} else {
							echo '1000000';
						} ?>;

		function enableButtons() {

		}

		function disableButtons() {

		}

		function hideAllSendDocTables() {
			get("RoleMappingTable").className = "hidden";
			get("UploadDocTable").className = "hidden";
			get("SelectSignersTable").className = "hidden";
			get("IndexFieldsTable").className = "hidden";
			get("SetupDocTable").className = "hidden";
			get("SharingTable").className = "hidden";
			get("ReviewTable").className = "hidden";
			get("RefSigningTable").className = "hidden";
			get("ReqSigningTable").className = "hidden";
			get('WorkflowsTable').className = "hidden";
		}

		function showPage(page) {
			hideAllSendDocTables();
			if (showeSigners) {
				fillListOfeSigners();
			}

			if (goBackToReview) {
				page = "Review";
				goBackToReview = false;
			}

			// Show or hide footer based on the page
			if (page === "SetupDoc") {
				$('.footer').hide();
			} else {
				$('.footer').show();
			}

			switch (page) {
				case "RoleMapping":
					setMenuTitle("Assign Signers to Roles");
					get("RoleMappingTable", "table").className = "";
					helpPage = helpPageURL?.pages?.sendDoc?.roleMapping ?? '';
					setupRoleDialog();
					break;
				case "SelTemplate":
					setMenuTitle("Select Template");
					get("WorkflowsTable", "table").className = "";
					drawTemplatesForSelection();
					break;
				case "Upload":
					setMenuTitle(allowMultipleDocs ? "Upload Documents" : "Upload Document");
					get("UploadDocTable", "table").className = "";
					helpPage = helpPageURL?.pages?.sendDoc?.upload ?? '';
					if (addRefOnly) {
						get("UploadDocRow").className = "hidden";
						$("#UploadBackBtnCell").hide();
					}
					break;
				case "Indices":
					setMenuTitle(`${truncate(`Index Data for "${uploadedDocs[currentDoc].formname}`, 35)}"`);
					get("IndexFieldsTable", "table").className = "";
					get('DataEntryCell').className = showDataEntry ? '' : 'hidden';
					if (showDataEntry) {
						get('EnableDataEntryCB').checked = uploadedDocs[currentDoc].enabledataentry;
					}
					includeNone = true;
					selectedDefinition = selectedDefinition || "";
					getIndexForms();
					checkIndicesBackButton();
					helpPage = helpPageURL?.pages?.sendDoc?.indices ?? '';
					break;
				case "SetupDoc":
					setMenuTitle(`${truncate(`Set Up for "${uploadedDocs[currentDoc].formname}`, 35)}"`);
					get("SetupDocTable", "table").className = "";
					setupTheSetupScreen();
					helpPage = helpPageURL?.pages?.sendDoc?.setupDoc ?? '';
					break;
				case "Sharing":
					setMenuTitle(`${truncate(`Sharing for "${uploadedDocs[currentDoc].formname}`, 35)}"`);
					get("SharingTable", "table").className = "";
					setupSharing();
					helpPage = helpPageURL?.pages?.sendDoc?.sharing ?? '';
					break;
				case "Signers":
					setMenuTitle("Select Signers for Send");
					get("SelectSignersTable", "table").className = "";
					getSigners();
					helpPage = helpPageURL?.pages?.sendDoc?.signers ?? '';
					break;
				case "ReqSigners":
					get("ReqSigningTable", "table").className = "";
					helpPage = helpPageURL?.pages?.sendDoc?.reqSigners ?? '';
					setupReqSigning();
					break;
				case "SelectWorkflow":
					setMenuTitle("Select Package Type");
					get("WorkflowsTable", "table").className = "";
					helpPage = helpPageURL?.pages?.sendDoc?.selectWorkflow ?? '';
					loadWorkflowSelect();
					break;
				case "RefSigners":
					get("RefSigningTable", "table").className = "";
					helpPage = helpPageURL?.pages?.sendDoc?.refSigners ?? '';
					setupRefSigning();
					break;
				case "Review":
					setMenuTitle("Review Document Information");
					get("ReviewTable", "table").className = "";
					setupReview();
					helpPage = helpPageURL?.pages?.sendDoc?.review ?? '';
					break;
				default:
					console.error("Unknown page: " + page);
					break;
			}
		}


		function loadNextTemplate() {
			if (curTemplate < templatesArray.length) {
				loadTemplateFromID(templatesArray[curTemplate]);
				curTemplate++;
			}
		}

		function processLoadSSPackageJSON(theJSON) {
			pleaseWait("");
			const theData = JSON.parse(theJSON);
			if (!theData.result) {
				logout(theData.error);
			} else {
				ssIndexData = JSON.parse(decodeURIComponent(theData.package.indexdata));
				smpData.url = hexToString(theData.package.ssurl);
				const conInfoArray = hexToString(theData.package.ssconinfo).split(":");
				smpData.user = conInfoArray[0];
				smpData.pass = conInfoArray[1];
				smpData.port = conInfoArray[2];
				smpData.filename = '';
				if (!ssIndexData[0].name) {
					let tmpInd = [];
					for (var key in ssIndexData[0]) {
						tmpInd.push({
							name: key,
							value: ssIndexData[0][key]
						});
					}
					ssIndexData = deepCopy(tmpInd);
				}
				for (let i = 0; i < ssIndexData.length; i++) {
					if (ssIndexData[i].name == "EntityType") {
						entityType = ssIndexData[i].value;
					}
					if (ssIndexData[i].name == "AttachmentID") {
						smpData.attachmentid = ssIndexData[i].value;
					}
				}
				if (smpData.attachmentid != "") {
					//make post request to SSGetAttachment.php with smpData
					axios({
						method: 'post',
						url: 'SSGetAttachment.php',
						data: {
							user: smpData.user,
							pass: smpData.pass,
							port: smpData.port,
							url: smpData.url,
							attachmentid: smpData.attachmentid,
							session: SID
						}
					}).then(function(response) {
						if (response.data.error) {
							showError(response.data.error);
						} else {
							smpData.filename = response.data.filename;
							const fNameData = smpData.filename.split('.');
							showPage('Upload');
							selectTemplateFlag = true;
							uploadType = 1;
							addUploadedDoc(smpData.filename, fNameData[0]);
						}
					}).catch(function(error) {
						logout(error);
					});
					return false;
				}
				getCategoriesForSSTemplate();
			}
		}

		function getCategoriesForSSTemplate() {
			templates = [];
			let tag = '';
			const temp = {
				session: SID,
				controlid: CID,
				resource: "CATEGORIES",
				action: "GETLIST"
			};
			doFetchCall("common/rest.php", temp).then(
				data => {
					for (let i = 0; i < data.categories.length; i++) {
						if (data.categories[i].name == entityType) {
							tag = data.categories[i].id;
							break;
						}
					}
					const categories = [tag];
					const temp = {
						session: SID,
						controlid: CID,
						categories: categories,
						resource: "TEMPLATES",
						type: 'Signing',
						action: "GETLIST"
					};
					pleaseWait("Getting list of templates");
					doRestCall("common/rest.php", temp, processgetSSTemplateListJSON);
				}
			);
		}

		function processgetSSTemplateListJSON(theJSON) {
			pleaseWait("");
			const theData = JSON.parse(theJSON);
			if (!theData.result) {
				logout(theData.error);
			} else {
				templates = theData.templates;
				if (templates.length == 0) {
					setMenuTitle("No Templates Found");
					get("WorkflowsTable", "table").className = "";
					drawNoTemplatesForSelection();
				} else {
					showPage('SelTemplate');
				}
			}
		}

		function processLoadPkgTemplatesJSON(theJSON) {
			pleaseWait("");
			const theData = JSON.parse(theJSON);
			if (!theData.result) {
				logout(theData.error);
			} else {
				curTemplate = 0;
				templatesArray = theData.templates;
				loadNextTemplate();
				showPage("Upload");
			}
		}

		function processeDOCItLoadTemplateJSON(theJSON) {
			pleaseWait("");
			const theData = JSON.parse(theJSON);
			if (!theData.result) {
				logout(theData.error);
			} else {
				uploadedDocs = [];
				currentDoc = 0;
				let tempIndexData = [];
				for (let theName in theData.indexfields) {
					tempIndexData.push({
						name: theName,
						value: theData.indexfields[theName]
					});
				}
				uploadedDocs.push({
					pdf: tempDir + theData.graphic,
					formname: theData.form,
					notificationname: theData.notificationname,
					notificationemail: theData.notificationemail,
					redirecturl: theData.redirecturl,
					targettable: theData.targettable,
					pagecount: 0,
					sigboxes: [],
					pkgid: pkgID,
					docid: templateID,
					sendcopy: 0,
					signers: [],
					triggers: [],
					indexdata: tempIndexData,
					images: [],
					status: theData.status
				});
				sharePagesVisited.push(false);
				if (theData.redirecturl != "") {
					defaultRURL = theData.redirecturl;
				}
				if (theData.notificationname != "") {
					defaultNotificationName = theData.notificationname;
					defaultNotificationEmail = theData.notificationemail;
				}
				const pkgDate = new Date;
				if (packageName == "") {
					packageName = `${uploadedDocs[0].formname} ${pkgDate.getMonth()}${pkgDate.getDay()}${pkgDate.getFullYear()}`;
				}
				createRoleList(theData.signsets);
				uploadedRoles = roles;
				for (let s = 0; s < theData.sigboxes.length; s++) {
					const len = uploadedDocs[0].sigboxes.push({
						x: 0,
						y: 0,
						w: 0,
						h: 0,
						xp: parseFloat(theData.sigboxes[s].left),
						yp: parseFloat(theData.sigboxes[s].top),
						wp: parseFloat(theData.sigboxes[s].width),
						hp: parseFloat(theData.sigboxes[s].height),
						maxt: 0,
						maxl: 0,
						maxx: 0,
						maxy: 0,
						id: theData.sigboxes[s].boxid,
						type: parseInt(theData.sigboxes[s].esigntype),
						page: parseInt(theData.sigboxes[s].pagenumber),
						docnum: 0,
						font: theData.sigboxes[s].font,
						fontcolor: theData.sigboxes[s].fontcolor,
						fontsize: parseInt(theData.sigboxes[s].fontsize),
						lhp: theData.sigboxes[s].lhp,
						lineheight: parseInt(theData.sigboxes[s].lineheight),
						fieldname: theData.sigboxes[s].fieldname,
						fieldlabel: theData.sigboxes[s].fieldlabel,
						defaultvalue: theData.sigboxes[s].defaultvalue,
						required: theData.sigboxes[s].fieldrequired,
						autofillfield: theData.sigboxes[s].autofillfield || '',
						checkedvalue: theData.sigboxes[s].checkedvalue,
						uncheckedvalue: theData.sigboxes[s].uncheckedvalue,
						depfield: theData.sigboxes[s].depfield,
						depfieldvalue: theData.sigboxes[s].depfieldvalue,
						depoperator: theData.sigboxes[s].depoperator,
						signsetid: theData.sigboxes[s].signsetid,
						signer: new Signer
					});
					if (
						uploadedDocs[0].sigboxes[len - 1].type == 7 ||
						uploadedDocs[0].sigboxes[len - 1].type == 6
					) {
						uploadedDocs[0].sigboxes[len - 1].signer = deepCopy(userSigner);
					} else {
						uploadedDocs[0].sigboxes[len - 1].signer.signsetid = theData.sigboxes[s].signsetid;
					}
				}
				for (let i = 0; i < uploadedDocs[0].sigboxes.length; i++) {
					//first set the role on the sig box
					for (let s = 0; s < theData.signsets.length; s++) {
						if (theData.signsets[s].signsetid == uploadedDocs[0].sigboxes[i].signsetid) {
							uploadedDocs[0].sigboxes[i].signer.role = theData.signsets[s].role;
							break;
						}
					}
					//now we can match the role and update to the correct signer
					for (let j = 0; j < roles.length; j++) {
						if (uploadedDocs[0].sigboxes[i].signer.role == roles[j].role) {
							if (roles[j].signsetid !== 'USERFIELD') {
								uploadedDocs[0].sigboxes[i].signer = roles[j];
								uploadedDocs[0].sigboxes[i].signsetid = roles.signsetid;
							}
							break;
						}
					}
				}
				if (theData.triggerdefs) {
					for (let t = 0; t < theData.triggerdefs.length; t++) {
						uploadedDocs[0].triggers.push(getDocTriggerFromDef(theData.triggerdefs[t]));
					}
				}
				uploadedDocs[0].sharing = [];
				allowMultipleDocs = false;
				const args = "<?php echo 'Go=Upl&CnvrtFile=' . urlencode((isset($XVARS["NewFile"]) ? $XVARS["NewFile"] : '')) . '&theFile=' . urlencode((isset($XVARS["NewFileName"]) ? $XVARS["NewFileName"] : '')); ?>";
				useNewDoc = true;
				goTo = "Upload";
				pleaseWait("Converting file");
				ajax_do_post("upload.php", args);
			}
		}
		var passedRoles = [];

		function applyPassedRoles() {
			for (let r = 0; r < roles.length; r++) {
				for (let p = 0; p < passedRoles.length; p++) {
					if (roles[r].role == passedRoles[p].role) {
						roles[r].name = passedRoles[p].name;
						roles[r].email = passedRoles[p].email;
						roles[r].authcode = passedRoles[p].authcode;
						roles[r].tier = passedRoles[p].tier;
						roles[r].verifyrequired = passedRoles[p].verifyrequired;
						roles[r].idprovider = passedRoles[p].idprovider;
						roles[r].phone = passedRoles[p].phone;
						roles[r].notificationtype = passedRoles[p].notificationtype;
						roles[r].notificationaccount = passedRoles[p].notificationaccount;
						const sngrid = generateGUID();
						let signerFound = false;
						for (let s = 0; s < signers.length; s++) {
							if ((signers[s].name == roles[r].name) && (signers[s].email == roles[r].email)) {
								signerFound = true;
								break;
							}
						}
						if (!signerFound) {
							signers.push({
								id: sngrid,
								signsetid: sngrid,
								color: "#FFFFFF",
								name: roles[r].name,
								email: roles[r].email,
								authcode: roles[r].authcode,
								selected: true,
								tier: roles[r].tier,
								role: roles[r].role,
								verifyrequired: roles[r].verifyrequired,
								idprovider: roles[r].idprovider,
								phone: roles[r].phone,
								notificationtype: roles[r].notificationtype,
								notificationaccount: roles[r].notificationaccount
							});
						}
					}
				}
			}
			setSignersIDs();
			for (let u = 0; u < uploadedDocs.length; u++) {
				uploadedDocs[u].signers = [];
				for (let s = 0; s < signers.length; s++) {
					uploadedDocs[u].signers.push(signers[s]);
					for (let i = 0; i < uploadedDocs[u].sigboxes.length; i++) {
						for (let r = 0; r < roles.length; r++) {
							if (uploadedDocs[u].sigboxes[i].signer.role == roles[r].role) {
								if ((signers[s].name == roles[r].name) && (signers[s].email == roles[r].email)) {
									uploadedDocs[u].sigboxes[i].signer = signers[s];
								}
							}
						}
					}
				}
				for (let i = uploadedDocs[u].sigboxes.length - 1; i >= 0; i--) {
					if (uploadedDocs[u].sigboxes[i].signer.name == "") {
						if (uploadedDocs[u].sigboxes[i].type != 6) {
							uploadedDocs[u].sigboxes.splice(i, 1);
						}
					}
				}
			}
			fillInUserFieldsWithMatchingIndexData();
			goTo = "SetupDoc";
			currentPage = 1;
			PDFJS.getDocument(uploadedDocs[docInd].pdf).promise.then(function(pdfDoc) {
				setupPDF(pdfDoc);
			})
		}

		function hideAddGroupBtn() {
			get('AddBlankGroupBtn').style = "display:none";
			get('AddBlankRoleGroupBtn').style = "display:none";
		}

		function loadGroupSubTable() {
			if (signerGroups.length == 0) {
				hideAddGroupBtn();
			}
		}

		function loadUploadedFiles() {
			if (loadFiles.length == 0) {
				return true;
			}
			let newTempArray = [];
			for (var i = 0; i < loadFiles.length; i++) {
				//get file extension
				const ext = loadFiles[i].file.split('.').pop();
				newTempArray.push({
					msg: '',
					filename: loadFiles[i].name,
					file_extn: ext,
					uniqnm: loadFiles[i].file
				});
			}
			uploadmsg(newTempArray);
		}
		$(window).on("load", function() {
			if (!showAuthCode) {
				authCodeHTMLClass = ' class="hidden"';
			}
			loadGoDSeal();
			if (isInternetExplorer()) {
				PDFJS.workerSrc = "<?= $pdfjsURL ?>/pdf.worker.mjs";
			} else {
				PDFJS = this.pdfjsLib;
				PDFJS.GlobalWorkerOptions.workerSrc = "<?= $pdfjsURL ?>/build/pdf.worker.mjs";
			}
			setupSession();
			if (showSignerGroups) {
				getSignerGroups();
			} else {
				// TODO: Figure out why this is breaking the page
				// hideAddGroupBtn();
			}
			saveSendingDoc = true;
			if (pkgID == "") {
				pkgID = generateGUID();
			} else {
				//Set up the senddoc process for adding to a package
				getSignersForPackage(pkgID, doProcessGotSigners);
				setPkgNmFrmPost();
				//Make a call to the backend to get a package's existing elements
				//TODO: Allow this to use async. We can't here because this is in a script tag.
				getOtherPackageFieldnames(pkgID);
				targetPage = "managedoc.php";
			}
			defaultNotificationName = userFullName;
			defaultNotificationEmail = userEmail;
			<?php
			if (isset($XVARS['DU'])) {
				echo 'goTo="Upload";
				ajax_do_post("upload.php","SID="+SID+"&StripFile=' . $XVARS["NewFile"] . '&theFile=' . urlencode($XVARS["NewFileName"]) . '");
				pleaseWait("Converting file");';
			} else if (isset($XVARS['DRefU'])) {
				echo 'goTo="Upload";
				ajax_do_post("uploadref.php","CnvrtFile=' . $XVARS["NewFile"] . '&theFile=' . urlencode($XVARS["NewFileName"]) . '");
				pleaseWait("Converting file");';
			} else if (isset($XVARS['SendTemp'])) {
				echo 'var temp = {session:SID,controlid:CID,resource:"TEMPLATES",action:"EDIT",docid:"' . $XVARS['SendTemp'] . '"};
				pleaseWait("Loading template");
				doRestCall("common/rest.php",temp,processeDOCItLoadTemplateJSON);';
			} else if (isset($XVARS['LoadPkgTemp'])) {
				echo 'pkgID="' . $XVARS['LoadPkgTemp'] . '";var temp = {session:SID,controlid:CID,resource:"PACKAGES",action:"GETTEMPLATEIDS",pkgid:"' . $XVARS['LoadPkgTemp'] . '"};
				pleaseWait("Loading packages");
				doRestCall("common/rest.php",temp,processLoadPkgTemplatesJSON);';
			} else if (isset($XVARS['ShowWorkflowSelect'])) {
				echo 'showPage("SelectWorkflow");';
				if ($XVARS['ShowWorkflowSelect'] != 'Y') {
					echo 'selWFID="' . $XVARS['ShowWorkflowSelect'] . '";';
				}
			} else if (isset($XVARS['action'])) {
				if ($XVARS['action'] == 'LOADFROMTEMPLATE') {
					echo 'templateID="' . $XVARS['templateid'] . '";';
					echo 'pkgID="' . $XVARS['pkgid'] . '";';
					echo 'packageName="' . $XVARS['pkgname'] . '";';
					echo 'pkgName="' . $XVARS['pkgname'] . '";';
					echo 'passedRoles=' . json_encode($XVARS['roles']) . ';';
					echo 'uploadedDocs.push({pdf:"' . $tempDir . $XVARS['file'] . '",formname:"' . $XVARS['formname'] . '",indexdata:' . json_encode($XVARS['indexdata']) . ',notificationname:defaultNotificationName,notificationemail:defaultNotificationEmail,sigboxes:[],pkgid:pkgID,docid:generateGUID(),images:[],sendcopy:0,signers:[]});';
					echo 'docInd=uploadedDocs.length-1;';
					echo 'LoadMatchingDocCBFunction="applyPassedRoles";';
					echo 'loadMatchingTemplate();';
				}
			} else if (isset($XVARS['OrgFile'])) {
				$FileArray = explode(",", $XVARS['TheFile']);
				$OrgArray = explode(",", $XVARS['OrgFile']);
				if (count($FileArray) != count($OrgArray)) {
					echo 'showError("Error: File and Original File count do not match");';
				} else {
					for ($i = 0; $i < count($FileArray); $i++) {
						echo 'loadFiles.push({file:"' . $FileArray[$i] . '",name:"' . $OrgArray[$i] . '"});';
					}
					echo 'loadUploadedFiles();';
				}
				echo 'showPage("Upload");';
			} else if (isset($XVARS['PendDocId'])) {
				echo 'pullPndDocForLoad("' . $XVARS['PendDocId'] . '");';
				echo 'showPage("Upload");';
			} else if (isset($XVARS['SSPKGID'])) {
				echo 'pkgID="' . $XVARS['SSPKGID'] . '";var temp = {session:SID,controlid:CID,resource:"PACKAGES",action:"GETPACKAGEDATA",pkgid:"' . $XVARS['SSPKGID'] . '"};
				pleaseWait("Loading data");
				doRestCall("common/rest.php",temp,processLoadSSPackageJSON);';
			} else {
				echo 'showPage("Upload");';
			}
			$NT = getSetting('NotificationTypes');

			$NTMap = getSetting('NotificationTypesMap');

			$NT2 = getSetting('NotificationTypes2');
			if (isset($NT) && !isset($NT2)) {
				for ($i = 0; $i < count($NT); $i++) {
					$NT2[$NT[$i]] = $NT[$i];
				}
			}

			echo 'notificationTypes=[];';
			foreach ($NT2 as $ind => $val) {
				if (isset($NTMap[$val])) {
					$val = $NTMap[$val];
				}
				echo 'notificationTypes.push({"name":"' . $ind . '","value":"' . $val . '"});';
			}
			getAndEchoIDProdiverSettings();
			if ($enableTemplateMatching) {
				echo 'fillCompareTemplateList();';
			}
			?>

		});
		window.location.hash = "no-back-button";
		window.location.hash = "Again-No-back-button"; //again because google chrome don't insert first hash into history
		window.onhashchange = function() {
			window.location.hash = "no-back-button";
		}
	</script>
</head>

<body id="body" class="d-flex flex-column">
	<header>
		<?php require_once("common/menu.php"); ?>
	</header>
	<main class="flex-shrink-0">
		<?php
		require_once("senddoc/rolemapping/rolemapping.php");
		require_once("senddoc/uploaddoc/uploaddoc.php");
		require_once("senddoc/selectsigners/selectsigners.php");
		require_once("senddoc/indices/indices.php");
		require_once("senddoc/setup/setup.php");
		require_once("senddoc/sharing/sharing.php");
		require_once("senddoc/review/review.php");
		require_once("senddoc/referencesigners/referencesigners.php");
		require_once("senddoc/requestedsigners/requestedsigners.php");
		?>
		<script src="senddoc/seltemplate/seltemplate.js?r=<?= $vrs ?>"></script>
		<table width="100%" cellspacing="0" cellpadding="0" id="WorkflowsTable" class="hidden">
			<tr>
				<td align="center" height="100%" valign="top" id="workflowscontent">&nbsp;</td>
			</tr>
		</table>
	</main>
	<footer class="footer mt-auto pt-3">
		<?php require_once("common/footer.php"); ?>
	</footer>
	<?php require_once("common/snackbar.php"); ?>
</body>
<?php require_once("common/messages.php"); ?>

</html>