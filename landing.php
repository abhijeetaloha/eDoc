<?php
require_once('common/commonfunctions.php');

getOriginNav(__DIR__);
checkLogin();

$ipRest = getSetting('AllowedIP');
if (isset($ipRest)) {
	if (count($ipRest) > 0) {
		$allowed = false;
		$userhost = getUserHost();
		$clparts = explode(".", $userhost);
		for ($i = 0; $i < count($ipRest); $i++) {
			$ipparts = explode(".", $ipRest[$i]);
			$good = true;
			for ($p = 0; $p < count($ipparts); $p++) {
				if ($ipparts[$p] == '*') {
					continue;
				}
				if ($ipparts[$p] != $clparts[$p]) {
					$good = false;
				}
			}
			if ($good) {
				$allowed = true;
				break;
			}
		}
		if (!$good) {
			logToFile("Invalid userhost tripped, logging user out: " . $userhost);
			returnError('You are unable to login at this time.');
		}
	}
}

$WMSG = isset($_POST['WM']) ? $_POST['WM'] : "";

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<?php require_once("common/headers.php"); ?>
	<script src="landing/redirect.js?r=<?= $vrs ?>"></script>
	<style>
		.LandingCell {
			width: 320px;
		}

		.LandingButton {
			background-repeat: no-repeat;
			background-position: 10px 17px;
			background-size: 38px 45px;
			font-size: 22px;
			color: #4A4A4A;
			border: 1px solid #9B9B9B;
			width: 300px;
			height: 80px;
			cursor: pointer;
			text-align: left;
		}

		.svg-inline--fa {
			color: #0D72B9;
		}

		.FALandingIcon {
			padding-left: 12px;
			font-size: 40px;
		}

		.FALandingStackSpan {
			font-size: 1.7em;
		}

		.FALandingStack1 {
			margin-top: 7px;
			margin-left: -20px;
			opacity: 1;
		}

		.FALandingStack2 {
			margin-left: -10px;
			opacity: 0.5;
		}

		.FALandingStack3 {
			margin-top: -7px;
			opacity: 0.25;
		}

		.FAClipboard {
			font-size: 1.6em;
			margin-top: 6px;
			margin-left: -10px;
		}

		.FAArrowUP {
			font-size: 0.8em;
			margin-left: -10px;
			margin-top: 6px;
		}

		.LandingText {
			font-size: 22px;
		}
	</style>
	<?php require_once("common/session.php"); ?>
	<script>
		var eDocSigUrl = "<?php echo $cfg['db']['eDOCSignatureURL']; ?>";
		var showeSigners = <?php echo (getSetting('ShoweSigners')) ? "true" : "false"; ?>;
		var showSignerGroups = <?php echo (getSetting('ShowSignerGroups')) ? "true" : "false"; ?>;
		<?php
		$altSiteSettings = getSetting('AltSiteSettings') ?? array();
		//Set the name of the cookie used to show the modal
		if (isset($altSiteSettings['Modal'], $altSiteSettings['Modal']['Version'])) {
			$altSiteSettings['Modal']['ModalCookie'] = "beta" . $altSiteSettings['Modal']['Version'];
		}
		//Add the setting to remove the redirect cookie if we posted here with the FromAltSite query param
		if (isset($XVARS['FromAltSite']))
			$altSiteSettings['WasRedirected'] = true;
		?>
		const altSite = <?php echo json_encode($altSiteSettings); ?>;

		function enableButtons() {

		}

		function disableButtons() {

		}

		function hasPerm(thePerm, prms) {
			for (var i = 0; i < prms.length; i++) {
				if (prms[i] == thePerm) {
					return true;
				}
			}
			return false;
		}

		async function processSwitchCIDJSON(theJSON) {
			pleaseWait("");
			var theData = JSON.parse(theJSON);
			if (!theData.result) {
				logout(theData.error);
			} else {
				CID = theData.controlid;
				await handleCookieCID(theData.username);

				// setCookie(removeNonNumAlpha(sessionUser) + "CID", CID, 365);

				//update landing page buttons based on permissions for the new control id we switched into
				var permissions = theData.permissions;
				var clsNm = (hasPerm('eDOCSig Send Document', permissions)) ? "" : "hidden";
				get("SendDocSp").className = clsNm;
				get("SendDocRow").className = clsNm;
				get("SendWrkSp").className = clsNm;
				get("SendWrkRow").className = clsNm;
				clsNm = (hasPerm('eDOCSig Manage Documents', permissions)) ? "" : "hidden";
				get("MngDocSp").className = clsNm;
				get("MngDocRow").className = clsNm;
				clsNm = (theData.showreports) ? "" : "hidden";
				get("RptsSp").className = clsNm;
				get("RptsRow").className = clsNm;
				clsNm = (hasPerm('eDOCSig Setup', permissions)) ? "" : "hidden";
				get("LandingCell2").className = clsNm;
				get("MngTempSp").className = clsNm;
				get("MngTempRow").className = clsNm;
				get("MngWrkSp").className = clsNm;
				get("MngWrkRow").className = clsNm;
				clsNm = (hasPerm('Change Own Password', permissions)) ? "" : "hidden";
				get("SettingsRow").className = clsNm;
				get("SettingsSP").className = clsNm;
				await displayInboxButton();
			}
		}

		/**
		 * @param {Event} event
		 */
		async function switchControlIDs(event) {
			/** @type {HTMLInputElement} */
			const target = event.target;
			const swCID = target.value;
			pleaseWait("Switching Control ID please wait ");
			doRestCall("common/rest.php", {
				session: SID,
				controlid: CID,
				resource: "SESSIONS",
				newcontrolid: swCID,
				action: "CHANGE_CONTROL_ID",
			}, processSwitchCIDJSON);
		}

		const handleCookieCID = async (username) => {
			const formData = new FormData();
			formData.append("controlID", CID);
			formData.append("username", username);

			await fetch("api/setcookie.php", {
				method: "POST",
				body: formData,
			});
		};

		function drawControlIDSelector() {
			if (sessionExpire > 0) {
				if (controlIDs.length > 1) {
					page = '<select style="width:120px;color:#000" onchange="switchControlIDs(event)" class="form-select ms-auto" id="CIDSelector">';
					for (var i = 0; i < controlIDs.length; i++) {
						page += '<option value="' + controlIDs[i] + '">' + controlIDs[i];
					}
					page += '</select>';
					get("ControlIDSelect").innerHTML = page;
					get("CIDSelector").value = CID;
				}
			} else {
				setTimeout(function() {
					drawControlIDSelector();
				}, 100);
			}
		}

		const displayInboxButton = async () => {
			//hide when ShoweSigners setting is false
			if (!showeSigners) return;

			let drawInbox = true;
			try {
				//get esigner inbox list
				const res = await doeServiceCall({
					url: 'api/eSignersAPI.php',
					function: 'geteSignerREST',
					esigners: [{
						id: UID
					}],
				});
				const no_esigner = (res.error !== null && res.error === 'eSigner not found');
				if (!res.result && !no_esigner) {
					throw new Error(res.error);
				}
				delete res.result;

				// don't show inbox button if no eSigner was found
				// don't show inbox button for a global user's eSigner who isn't associated with the current control ID
				if (no_esigner || res.esigners[0].controlid !== CID) {
					drawInbox = false;
					return;
				}
			} catch (error) {
				drawInbox = false;
				console.error(error);
			} finally {
				if (drawInbox) {
					get("InboxRow").classList.remove("hidden");
					get("InboxSp").classList.remove("hidden");
					get("LandingCell2").classList.remove("hidden");
					setCookie("eSFlag", true, 'Mon, 01 Jan 1970 00: 00: 00 UTC');
					//get total number of active packages to sign for the eSigner and draw it as a counter
					drawInboxCounter();
				} else {
					get("InboxRow").classList.add("hidden");
					get("InboxSp").classList.add("hidden");
				}
			}
		};

		/**
		 * Display a counter of the total number of active packages to sign for the eSigner
		 */
		async function drawInboxCounter() {
			/**
			 * @type {Number} - the total number of active packages to sign for the eSigner. -1 for not drawing the counter.
			 */
			let pckgCount = -1;
			try {
				//Specify CID the eSigner is associated with.
				const data = await doeServiceCall({
					url: 'api/eSignersAPI.php',
					function: 'getTotalNumOfeSignerPackages',
					// controlid: 'Reed',
					id: UID,
				});
				if (!data.result) {
					throw new Error(data.error);
				}

				//if no active packages, don't draw the counter
				if (data.activepackages.length <= 0) {
					pckgCount = -1;
					return;
				}

				//go through packages.active and exclude any that are has a status of Declined
				pckgCount = 0;
				for (let package of data.activepackages) {
					if (package.status !== 'Declined') pckgCount++;
				}
			} catch (error) {
				pckgCount = -1;
				console.error(error);
			} finally {
				if (pckgCount == -1) {
					get('InboxEnvCell').innerHTML = `<i class="fas fa-envelope FALandingIcon"></i>`;
				} else {
					get('InboxEnvCell').innerHTML = `<span class="fa-layers fa-fw fa-2x ms-2">
														<i class="fa-solid fa-envelope"></i>
														<span class="fa-layers-counter" style="background:Tomato">${pckgCount}</span>
													</span>`;
				}
			}
		}

		const postToSigningPages = async () => {
			pleaseWait("Please wait while we transfer you to Signing Pages...");
			const {
				data: {
					session
				}
			} = await axios({
				method: 'post',
				url: 'common/rest.php',
				data: {
					GetToken: true,
					session: SID
				},
			});
			pleaseWait("");
			if (session) {
				post(eDocSigUrl + "index.php?redir=true", {
					SID: SID,
					CID: CID,
					SIGNER: UID,
					RURL: "login.php",
					token: session
				}, "_self");
			} else {
				post(eDocSigUrl + "index.php?redir=true", {
					SID: SID,
					CID: CID,
					SIGNER: UID,
					RURL: "login.php"
				}, "_self");
			}
		}

		$(window).on("load", async function() {
			setMenuTitle("eDOCSignature");
			loadGoDSeal();
			setupSession();
			setTimeout(function() {
				drawControlIDSelector();
			}, 100);
			helpPage = helpPageURL?.pages?.landing ?? '';
			<?php
			if ($WMSG != "") {
				echo 'drawInfoModal("' . $WMSG . '");';
			}
			$DisableCID = getSetting('Disabled');
			$DisabledMessage = getSetting('DisabledMessage');
			if ($DisableCID) {
				echo 'get("LandingBody").innerHTML=\'' . $DisabledMessage . '\';';
			}
			?>
			displayInboxButton();
			handleAltSite();
			// Set the locale globally
			moment.locale('en', {
				week: {
					dow: 1 // Monday is the first day of the week
				}
			});
		});
	</script>
</head>

<body class="d-flex flex-column">
	<header>
		<?php require_once("common/menu.php"); ?>
	</header>
	<main class="flex-shrink-0">
		<div class="row w-100">
			<div class="col" align="center" valign="top" id="LandingBody">
				<table cellspacing="0" cellpadding="0">
					<tr id="LandingRow" style="display: table-row;">
						<td class="LandingCell" id="LandingCell" valign="top">
							<table align="center">
								<tr <?php
									if (!$showSendDoc) {
										echo ' class="hidden"';
									} else {
										hideIfNotHave('eDOCSig Send Document', null);
									} ?> id="SendDocSp">
									<td align="center" height="10"></td>
								</tr>
								<tr <?php if (!$showSendDoc) {
										echo ' class="hidden"';
									} else {
										hideIfNotHave('eDOCSig Send Document', null);
									} ?> id="SendDocRow">
									<td id="SendDocLandBtn" class="CreateSigningDocLink LandingButton" onclick='post("senddoc.php",{SID:SID,CID:CID});' valign="middle" align="Center">
										<table width="100%" cellpadding="0" cellspacing="0">
											<tr>
												<td width="77px"><i class="fas fa-file-arrow-up FALandingIcon"></i></td>
												<td class="LandingText">Send Document</td>
										</table>
								</tr>
								<tr <?php if ($showPackages && $showSendDoc) {
										hideIfNotHave('eDOCSig Send Document', null);
									} else {
										echo ' class="hidden"';
									} ?> id="SendWrkSp">
									<td align="center" height="10"></td>
								</tr>
								<tr <?php if ($showPackages && $showSendDoc) {
										hideIfNotHave('eDOCSig Send Document', null);
									} else {
										echo ' class="hidden"';
									} ?> id="SendWrkRow">
									<td id="SendWrkLandBtn" class="SendWorkFlow LandingButton" onclick='post("senddoc.php",{ShowWorkflowSelect:"Y",SID:SID,CID:CID});' valign="middle">
										<table width="100%" cellpadding="0" cellspacing="0">
											<tr>
												<td class="fa-4x FALandingIcon" width="70px">
													<i class="fa fa-arrow-up" data-fa-transform="shrink-9 down-3" data-fa-mask="fas fa-clipboard"></i>
												</td>
												<td class="LandingText">Send Package</td>
											</tr>
										</table>
								</tr>
								<tr <?php hideIfNotHave('eDOCSig Manage Documents', null); ?> id="MngDocSp">
									<td align="center" height="10"></td>
								</tr>
								<tr <?php hideIfNotHave('eDOCSig Manage Documents', null); ?> id="MngDocRow">
									<td id="ManageDocLandBtn" class="SearchSigningsLink LandingButton" onclick='post("managedoc.php",{SID:SID,CID:CID});' valign="middle">
										<table width="100%" cellpadding="0" cellspacing="0">
											<tr>
												<td width="70px">
													<span class="icon fa-layers fa-fw FALandingStackSpan">
														<i class="fas fa-file FALandingStack1" data-fa-transform="down-2 right-2"></i>
														<i class="fas fa-file FALandingStack2" data-fa-transform="up-2 right-6"></i>
														<i class="fas fa-file FALandingStack3" data-fa-transform="up-6 right-10"></i>
													</span>
												</td>
												<td class="LandingText">Manage Packages</td>
											</tr>
										</table>
								</tr>
								<tr id="RptsSp" <?php if (!$showReports) {
													echo ' class="hidden"';
												} ?>>
									<td align="center" height="10"></td>
								</tr>
								<tr id="RptsRow" <?php if (!$showReports) {
														echo ' class="hidden"';
													} ?>>
									<td id="ReportsLandBtn" class="ReportsLink LandingButton" onclick='post("reports.php",{SID:SID,CID:CID});' valign="middle">
										<table width="100%" cellpadding="0" cellspacing="0">
											<tr>
												<td width="77px"><i class="fas fa-chart-bar FALandingIcon"></i></td>
												<td class="LandingText">Reports</td>
										</table>
								</tr>
								<tr <?php if ($showPending) {
										hideIfNotHave('eDOCSig Send Document', null);
									} else {
										echo ' class="hidden"';
									} ?> id="MngPndSp">
									<td align="center" height="10"></td>
								</tr>
								<tr <?php if ($showPending) {
										hideIfNotHave('eDOCSig Send Document', null);
									} else {
										echo ' class="hidden"';
									} ?> id="MngPndRow">
									<td id="ManagePendingLandBtn" class="LandingButton" onclick='post("pending.php",{SID:SID,CID:CID,ACTION:"Manage"});' valign="middle">
										<table width="100%" cellpadding="0" cellspacing="0">
											<tr>
												<td width="77px"><i class="fas fa-file-powerpoint FALandingIcon"></i>
												</td>
												<td class="LandingText">Pending Docs</td>
										</table>
								</tr>
								<tr <?php hideIfNotHave('Change Own Password', null); ?> id="SettingsSP">
									<td align="center" height="10"></td>
								</tr>
								<tr <?php hideIfNotHave('Change Own Password', null); ?> id="SettingsRow">
									<td id="SettingsLandBtn" class="SettingsLink LandingButton" onclick='post("settings.php",{SID:SID,CID:CID});' valign="middle">
										<table width="100%" cellpadding="0" cellspacing="0">
											<tr>
												<td width="77px"><i class="fas fa-gear FALandingIcon"></i></td>
												<td class="LandingText">Settings</td>
										</table>
								</tr>
							</table>
						</td>
						<td id="LandingCell2" valign="top" <?php hideIfNotHave('eDOCSig Setup', 'LandingCell'); ?>>
							<table align="center">
								<tr <?php if (!$showTemplateSetup) {
										echo ' class="hidden"';
									} else {
										hideIfNotHave('eDOCSig Setup', null);
									} ?> id="MngTempSp">
									<td align="center" height="10"></td>
								</tr>
								<tr <?php if (!$showTemplateSetup) {
										echo ' class="hidden"';
									} else {
										hideIfNotHave('eDOCSig Setup', null);
									} ?> id="MngTempRow">
									<td id="ManageTemplateLandBtn" class="ManageTemplates LandingButton" onclick='post("managetemplate.php",{SID:SID,CID:CID});' valign="middle">
										<table width="100%" cellpadding="0" cellspacing="0">
											<tr>
												<td width="70px">
													<span class="icon fa-layers fa-fw FALandingStackSpan">
														<i class="fas fa-file-invoice FALandingStack1" data-fa-transform="down-2 right-2"></i>
														<i class="fas fa-file-invoice FALandingStack2" data-fa-transform="up-2 right-6"></i>
														<i class="fas fa-file-invoice FALandingStack3" data-fa-transform="up-6 right-10"></i>
													</span>
												</td>
												<td class="LandingText">Set Up Templates</td>
											</tr>
										</table>
								</tr>
								<tr <?php if ($showPackages && $showTemplateSetup) {
										hideIfNotHave('eDOCSig Setup', null);
									} else {
										echo ' class="hidden"';
									} ?> id="MngWrkSp">
									<td align="center" height="10"></td>
								</tr>
								<tr <?php if ($showPackages && $showTemplateSetup) {
										hideIfNotHave('eDOCSig Setup', null);
									} else {
										echo ' class="hidden"';
									} ?> id="MngWrkRow">
									<td id="ManageWorkflowsLandBtn" class="ManageWorkflows LandingButton" onclick='post("workflows.php",{SID:SID,CID:CID,ACTION:"Manage"});' valign="middle">
										<table width="100%" cellpadding="0" cellspacing="0">
											<tr>
												<td width="70px">
													<span class="icon fa-layers fa-fw FALandingStackSpan">
														<i class="fas fa-clipboard-list FALandingStack1" data-fa-transform="down-2 right-2"></i>
														<i class="fas fa-clipboard-list FALandingStack2" data-fa-transform="up-2 right-6"></i>
														<i class="fas fa-clipboard-list FALandingStack3" data-fa-transform="up-6 right-10"></i>
													</span>
												</td>
												<td class="LandingText">Set Up Package Types</td>
											</tr>
										</table>
								</tr>
								<tr <?php if ($enableBulkSend) {
										hideIfNotHave('eDOCSig Setup', null);
									} else {
										echo ' class="hidden"';
									} ?> id="BulkSendSp">
									<td align="center" height="10"></td>
								</tr>
								<tr <?php if ($enableBulkSend) {
										hideIfNotHave('eDOCSig Setup', null);
									} else {
										echo ' class="hidden"';
									} ?> id="BulkSendRow">
									<td id="BulkSendLandBtn" class="LandingButton" onclick='post("bulksend.php",{SID:SID,CID:CID});' valign="middle">
										<table width="100%" cellpadding="0" cellspacing="0">
											<tr>
												<td width="77px"><i class="fas fa-rectangle-list FALandingIcon"></i>
												</td>
												<td class="LandingText">Set Up Bulk Send</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr id="InboxSp" class="hidden">
									<td align="center" height="10"></td>
								</tr>
								<tr id="InboxRow" class="hidden">
									<td id="InboxLandBtn" class="LandingButton" onclick='postToSigningPages();' valign="middle">
										<table width="100%" cellpadding="0" cellspacing="0">
											<tr>
												<td width="77px" id="InboxEnvCell"></td>
												<td class="LandingText">Inbox</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</main>
	<footer class="footer mt-auto pt-3">
		<?php require_once("common/footer.php"); ?>
	</footer>
	<?php require_once("common/snackbar.php"); ?>
	<?php require_once('common/messages.php'); ?>
</body>

</html>