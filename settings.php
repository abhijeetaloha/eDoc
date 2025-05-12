<?php
require_once('common/commonfunctions.php');
require_once('config.php');

getOriginNav(__DIR__);
checkLogin();

$eDocSigSetup = 'eDOCSig Setup';
$XVARS = (!empty($_POST)) ? $_POST : $_GET;
$tempDir = isset($cfg['db']['tempdir']) ? $cfg['db']['tempdir'] : "temp/";
$showeDocIt = getSetting('ShoweDOCIt');
$showeDocItTray = getSetting('ShoweDOCItTray');
$showeDocItPrinter = getSetting('ShoweDOCItPrinter');
$showEmails = getSetting('ShowEmailCustomization');
$showTriggers = (getSetting('ShowEvents') ? getSetting('ShowEvents') : getSetting('ShowTriggers'));
$showIndices = getSetting('ShowIndices');
$showMarketing = getSetting('ShowMarketing');
$eDocItPrinter = getSetting('eDOCItPrinter');
$eDocItTrayApp = getSetting('eDOCItTrayApp');
$page = isset($XVARS['page']) ? $XVARS['page'] : 'Profile';
$enableBulkSend = getSetting('EnableBulkSend');
$showeSigners = getSetting('ShoweSigners');
$showSignerGroups = getSetting('ShowSignerGroups');
$onlySignerTab = !$enableBulkSend && !$showeSigners && !$showSignerGroups;
$microservicesURL = isset($cfg['db']['eServices']) ? $cfg['db']['eServices'] : $cfg['db']['MicroservicesURL'];
$EnableAuditReports = getSetting('EnableAuditReports');
?>
<!DOCTYPE HTML>
<html lang="eng">

<head>
	<title>eDOCSignature</title>
	<?php require_once("common/headers.php"); ?>
	<style>
		.scrollIt {
			width: 300px;
			height: 100%;
			overflow-y: scroll;
		}

		.nav-pills .nav-link {
			border-radius: 0px;
		}

		.nav-pills.nav-secondary .nav-link.active {
			background-color: var(--bs-secondary) !important;
			font-weight: bold;
		}
	</style>
	<script src="common/formsandinputs.js?id=<?= $vrs ?>"></script>
	<script src="common/roles.js?id=<?= $vrs ?>"></script>
	<script src="common/jscolor.js?id=<?= $vrs ?>"></script>
	<script src="settings/addnoticeday.js?id=<?= $vrs ?>"></script>
	<link href="common/formsandinputs.css?id=<?= $vrs ?>" rel="stylesheet" type="text/css">
	<?php require_once("common/session.php"); ?>
	<script>
		var tempDir = "<?php echo $tempDir; ?>";
		var microservicesUrlSet = <?php echo !empty($microservicesURL) ? "true" : "false"; ?>;
		var showeSigners = <?php echo ($showeSigners) ? "true" : "false"; ?>;
		var showSignerGroups = <?php echo ($showSignerGroups) ? "true" : "false"; ?>;
		var enableBulkSend = <?php echo ($enableBulkSend) ? "true" : "false"; ?>;
		var onlySignerTab = <?php echo ($onlySignerTab) ? "true" : "false"; ?>;
		var addRefOnly = <?= $addrefonly ?>;
		let targetTables = [];
		var eDOCItPrinterURL;
		var eDOCItTrayAppURL;

		<?php if ($eDocItPrinter != "") {
			echo 'eDOCItPrinterURL = "' . $eDocItPrinter . '";';
		}
		if ($eDocItTrayApp != "") {
			echo 'eDOCItTrayAppURL = "' . $eDocItTrayApp . '";';
		} ?>

		<?php
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

		$tables = getSetting('TargetTables');
		if (!isset($tables)) {
			$tables = [];
		}
		for ($i = 0; $i < count($tables); $i++) {
			echo 'targetTables.push("' . $tables[$i] . '");';
		}

		?>

		edtGroup = <?php if (userhasperm('Edit Groups')) {
						echo 'true;';
					} else {
						echo 'false;';
					} ?>;
		MAX_FILE_SIZE = <?php if (isset($cfg['db']['MaxUploadSize'])) {
							echo $cfg['db']['MaxUploadSize'];
						} else {
							echo '1000000';
						} ?>;

		function hideAdmin() {
			$('#AdminBtn').removeClass('active');
			$('#AdminSideNav').hide();
			$('#AdminToggler').removeClass('fa-chevron-down').addClass('fa-chevron-right');

			$('#RolesTable').removeClass('active');
			$('#DisclosureTable').removeClass('active');
			$('#EULATable').removeClass('active');
			$('#TagsTable').removeClass('active');
			$('#EmailsTable').removeClass('active');
			$('#MarketingTable').removeClass('active');
			$('#EventsTable').removeClass('active');
			$('#IndicesTable').removeClass('active');
			document.getElementById("AuditReportsPage").classList.remove("active")
		}

		function hideAdminPanel() {
			$('#AdminTable').hide();
		}

		function showAdminPanel() {
			$('#AdminTable').show();
		}

		function showAdmin() {
			$('#AdminSideNav').show();
			$('#AdminToggler').removeClass('fa-chevron-right').addClass('fa-chevron-down');
		}

		function toggleAdminPanel() {
			if ($('#AdminSideNav').is(":hidden")) {
				showAdmin();
				showAdminPanel();
				get("RolesTabBtn").click();
			} else {
				hideAdmin();
				hideAdminPanel();
			}
		}

		function hideSigners() {
			$('#SignerSideNav').hide();
			$('#SignerToggler').removeClass('fa-chevron-down').addClass('fa-chevron-right');

			$('#SignerTable').removeClass('active');
			$('#SignerGroupTable').removeClass('active');
			$('#eSignerEdit').removeClass('active');
			$('#eSignersTable').removeClass('active');
		}

		function showSigners() {
			$('#SignerSideNav').show();
			$('#SignerToggler').removeClass('fa-chevron-right').addClass('fa-chevron-down');
		}

		function toggleSignersPanel() {
			if (onlySignerTab || $('#SignerSideNav').is(":hidden")) {
				if (onlySignerTab) {
					subPage("Signer");
				} else {
					showSigners();
					get("SignersSubBtn").click();
				}
				helpPage = helpPageURL?.pages?.settings?.signers ?? '';
			} else {
				$('#SignersBtn').removeClass('active');
				if (!onlySignerTab) hideSigners();
			}
		}

		const showPage = async (page) => {
			if (showeSigners) {
				fillListOfeSigners();
			}
			switch (page) {
				case 'Profile':
					hideAdmin();
					hideSigners();
					setMenuTitle("Edit Profile");
					await loadCurrentUserData();
					<?php

					logToFile($showeDocIt . "    - Test");
					if ($showeDocIt) {
						if ($showeDocItTray) {
							echo '$("#eDOCItTable").show();';
							if ($showeDocItPrinter && (userhasPerm("DOC Configure tools") || userhasPerm("eDOCSig Setup"))) {
								echo '$("#eDOCItPrinterTable").show();';
							}
						} else {
							echo 'getNewLink("true");';
							echo 'if(eDOCItPrinterURL!=undefined ){';
							echo '$("#legacyeDOCItTable").show();';
							echo '$("#edocItPrinterLink").attr("href",eDOCItPrinterURL);';
							echo '$("#edocItTrayLink").attr("href", eDOCItTrayAppURL);';
							echo '}else{';
							echo '$("#legacyeDOCItTable").show();';
							echo '}';
						}
					}
					?>

					helpPage = helpPageURL?.pages?.settings?.profile ?? '';
					break;
				case 'Users':
					hideAdmin();
					hideSigners();
					setMenuTitle("Edit Users");
					setupEditUserDialog();
					helpPage = helpPageURL?.pages?.settings?.users ?? '';
					break;
				case 'Groups':
					hideAdmin();
					hideSigners();
					setMenuTitle("Edit Groups");
					setupGroupsDialog();
					helpPage = helpPageURL?.pages?.settings?.groups ?? '';
					break;
				case 'Signers':
					hideAdmin();
					toggleSignersPanel();
					break;
				case 'Disclosure':
					showAdmin();
					hideSigners();
					showAdminPanel();
					setMenuTitle("Edit Disclosure");
					loadCurrentDisclosure();
					loadCurrentEULA();
					helpPage = helpPageURL?.pages?.settings?.disclosure ?? '';
					break;
				case 'EULA':
					showAdmin();
					hideSigners();
					setMenuTitle("Edit EULA");
					loadCurrentEULA();
					helpPage = helpPageURL?.pages?.settings?.EULA ?? '';
					break;
				case 'Indices':
					showAdmin();
					hideSigners();
					showAdminPanel();
					selectedFormInd = -1;
					setMenuTitle("Edit Index Definitions");
					helpPage = helpPageURL?.pages?.settings?.indices ?? '';
					getIndices();
					break;
				case 'AuditReports':
					showAdmin();
					hideSigners();
					showAdminPanel();
					setMenuTitle("Audit Reports");
					hideAdminPanel();
					document.getElementById("AdminTable").classList.remove("active")
					// helpPage = helpPageURL?.pages?.settings?.indices ?? '';
					loadAuditReportsPage();
					break;
				case 'Marketing':
					showAdmin();
					hideSigners();
					showAdminPanel();
					setMenuTitle("Marketing");
					showMarketingPage()
					helpPage = helpPageURL?.pages?.settings?.marketing ?? '';
					break;
				case 'Tags':
					showAdmin();
					hideSigners();
					showAdminPanel();
					setMenuTitle("Manage Tags");
					helpPage = helpPageURL?.pages?.settings?.tags ?? '';
					doTagsSearch();
					// Needed to 'click' the Tags Tab button to ensure that the tags section displays correctly
					let tagsTriggerEl = document.querySelector('#TagsTabBtn')
					let tab = new bootstrap.Tab(tagsTriggerEl)
					tab.show()
					break;
				case 'Roles':
					showAdmin();
					showAdminPanel();
					hideSigners();
					$("#RolesTable").addClass("active");
					setMenuTitle("Manage Roles");
					helpPage = helpPageURL?.pages?.settings?.roles ?? '';
					showRoleManagement();
					break;
				case 'Emails':
					showAdmin();
					hideSigners();
					showAdminPanel();
					setMenuTitle("Customize Email Templates");
					getTemplatesAndColors();
					helpPage = helpPageURL?.pages?.settings?.emails ?? '';
					break;
				case 'EditEmail':
					showAdmin();
					showAdminPanel();
					hideSigners();
					setMenuTitle(`${truncate(`Editing "${templateToEdit}`, 35)}"`);
					getEmailTemplateData();
					$('#EmailEditTable').show();
					helpPage = helpPageURL?.pages?.settings?.editEmail ?? '';
					break;
				case 'Events':
					showAdmin();
					hideSigners();
					showAdminPanel();
					setMenuTitle("Default Package Events");
					helpPage = helpPageURL?.pages?.settings?.events ?? '';
					showGlobalEvents();
					break;
				case 'Admin':
					toggleAdminPanel();
					break;
			}
		}

		// Ensures that the correct tab is highlighted
		function selectTab() {
			// Determine which tab is the first and apply the 'active' class
			let adminTabsArray = [];
			let disclosuresBtn = document.querySelector("#DisclosureBtn");
			let tagsBtn = document.querySelector("#TagsTabBtn");
			let rolesBtn = document.querySelector("#RolesTabBtn");
			let emailsBtn = document.querySelector("#EmailsBtn");
			let eventsBtn = document.querySelector("#EventsTabBtn");
			let indicesBtn = document.querySelector("#IndicesBtn");
			let marketingBtn = document.querySelector("#MarketingBtn");
			let auditReportsBtn = document.querySelector("#AuditReportsBtn");

			adminTabsArray.push(rolesBtn, disclosuresBtn, tagsBtn, emailsBtn, eventsBtn, indicesBtn, marketingBtn, auditReportsBtn);
			// Removes the active class to prevent duplicate highlights
			for (let button of adminTabsArray) {
				if (button.classList.contains("active")) {
					button.classList.remove('active');
					break;
				}
			}
			for (let button of adminTabsArray) {
				if (!button.classList.contains("hidden")) {
					button.classList.add('active');
					break;
				}
			}
		}

		function enableButtons() {
			let buttons = document.querySelectorAll('[id^="SaveSigner"]');
			// Directly enable or disable the buttons
			buttons.forEach(button => {
				button.disabled = false;
			});
		}

		function disableButtons() {
			let buttons = document.querySelectorAll('[id^="SaveSigner"]');
			// Directly enable or disable the buttons
			buttons.forEach(button => {
				button.disabled = true;
			});
		}
		$(window).on("load", () => {
			let esignNotHtml = '';
			for (var n = 0; n < notificationTypes.length; n++) {
				if (notificationTypes[n].name == 'Private') continue; //not an option for eSigners
				esignNotHtml += '<option value="' + notificationTypes[n].name + '"';
				esignNotHtml += '>' + notificationTypes[n].value;
			}
			if (get('eSignerNotificationType')) {
				get('eSignerNotificationType').innerHTML = esignNotHtml;
			}
			loadGoDSeal();
			setupSession();
			showPage("<?php echo $page; ?>");
		});
		window.location.hash = "no-back-button";
		window.location.hash = "Again-No-back-button"; //again because google chrome don't insert first hash into history
		window.onhashchange = function() {
			window.location.hash = "no-back-button";
		}
	</script>
</head>

<body class="d-flex flex-column">
	<header>
		<?php require_once("common/menu.php"); ?>
	</header>
	<main class="flex-shrink-0" style="min-height:85vh;">
		<div class="row w-100 g-0" style="height:100%">
			<div class="col-xl-1 col-md-2 ps-2 h-100 bg-light">
				<ul class="nav nav-pills nav-fill flex-column" id="SettingsTabs" role="tablist">
					<li class="nav-item" role="presentation">
						<button type="button" class="nav-link" id="goToLandingButton" onclick="goToLanding();">Menu</button>
					</li>
					<li class="nav-item" role="presentation">
						<button id="ProfileBtn" type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#ProfileTable" onclick="showPage('Profile')">Profile</button>
					</li>
					<li <?php hideIfNotHave('Edit Users', null); ?> class="nav-item" role="presentation">
						<button id="UsersBtn" type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#UsersTable" onclick='showPage("Users")'>Users</button>
					</li>
					<li <?php hideIfNotHave('Edit Groups', null); ?> class="nav-item" role="presentation">
						<button id="GroupsBtn" type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#GroupsTable" onclick='showPage("Groups")'>Groups</button>
					</li>
					<li <?php hideIfNotHave('eDOCSig Edit eSigners', null); ?> class="nav-item" role="presentation">
						<?php
						if ($onlySignerTab) {
							echo '<button id="SignersBtn" type="button" class="nav-link pe-2" data-bs-toggle="tab" data-bs-target="#SignersTable" onclick=\'showPage("Signers")\'>Signers</button>';
						} else {
							echo '	<button id="SignersBtn" type="button" class="nav-link pe-2" data-bs-toggle="tab" data-bs-target="#SignersTable" onclick=\'showPage("Signers")\'><i id="SignerToggler" class="fas fa-chevron-right pe-2"></i>&nbsp;Signers</button>
									<small><ul class="nav nav-pills nav-fill flex-column ps-4 nav-secondary" id="SignerSideNav" role="tablist" style="display: none;">
										<li class="nav-item" role="presentation">
											<button id="SignersSubBtn" type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#SignerTable" onclick=\'subPage("Signer")\'>Signers</button>
										</li>';
							if ($showeSigners) {
								echo '	<li class="nav-item" role="presentation">
											<button id="eSignersBtn" type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#eSignersTable" onclick=\'subPage("eSigners")\'>eSigners</button>
										</li>';
							}
							if ($showSignerGroups) {
								echo '	<li class="nav-item" role="presentation">
											<button id="GroupSubBtn" type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#SignerGroupTable" onclick=\'subPage("Group")\'>Signer Groups</button>
										</li>';
							}
							echo '	</ul></small>';
						}
						?>
					</li>
					<li role="presentation" <?php hideIfNotHave($eDocSigSetup, 'nav-item'); ?>>
						<button id="AdminBtn" type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#AdminTable" onclick='showPage("Admin")'>
							<span id="AdminToggler" class="fas fa-chevron-right pe-2"></span>&nbsp;Admin
						</button>
						<small>
							<ul class="nav nav-pills nav-fill flex-column ps-2 nav-secondary" id="AdminSideNav" role="tablist" style="display: none;">
								<li class="nav-item" role="presentation">
									<button class="nav-link" id="RolesTabBtn" type="button" data-bs-toggle="tab" data-bs-target="#RolesTable" onclick='showPage("Roles")'>Roles</button>
								</li>
								<li class="nav-item" role="presentation">
									<button <?php hideIfNotHave('Edit User Agreements', 'nav-link'); ?> id="DisclosureBtn" type="button" data-bs-toggle="tab" data-bs-target="#DisclosureTable" onclick='showPage("Disclosure")'>Disclosures</button>
								</li>
								<li class="nav-item" role="presentation">
									<button <?php hideIfNotHave('Edit EULA', 'nav-link'); ?> id="EULABtn" type="button" data-bs-toggle="tab" data-bs-target="#EULATable" onclick='showPage("EULA")'>EULA</button>
								</li>
								<li class="nav-item" role="presentation">
									<button class="nav-link" id="TagsTabBtn" type="button" data-bs-toggle="tab" data-bs-target="#TagsTable" onclick='showPage("Tags")'>Tags</button>
								</li>
								<?php
								if ($showEmails) {
									echo '<li class="nav-item" role="presentation"><button class="nav-link" id="EmailsBtn" type="button" data-bs-toggle="tab" data-bs-target="#EmailsTable" onclick=\'showPage("Emails")\'>Emails</button></li>';
								}
								if ($showTriggers) {
									echo '<li class="nav-item" role="presentation"><button class="nav-link" id="EventsTabBtn" type="button" data-bs-toggle="tab" data-bs-target="#EventsTable" onclick=\'showPage("Events")\'>Events</button></li>';
								}
								if ($showMarketing) {
									echo '<li class="nav-item" role="presentation"><button class="nav-link" id="MarketingBtn" type="button" data-bs-toggle="tab" data-bs-target="#MarketingTable" onclick=\'showPage("Marketing")\'>Marketing</button></li>';
								}
								if ($showIndices) {
									echo '<li class="nav-item" role="presentation"><button class="nav-link" id="IndicesBtn" type="button" data-bs-toggle="tab" data-bs-target="#IndicesTable" onclick=\'showPage("Indices")\'>Indices</button></li>';
								}
								if ($EnableAuditReports && userhasperm('Edit Users')) {
									echo '<li class="nav-item" role="presentation"><button class="nav-link" id="AuditReportsTabBtn" type="button" data-bs-toggle="tab" data-bs-target="#AuditReportsPage" onclick=\'showPage("AuditReports")\'>Audit Reports</button></li>';
								}
								?>

							</ul>
						</small>
					</li>
				</ul>
			</div>
			<div class="col-xl-11 col-md-10 tab-content justify-content-center">
				<?php
				require_once("settings/profile.php");
				require_once("settings/users.php");
				require_once("settings/groups.php");
				require_once("settings/signers.php");
				require_once("settings/auditReports.php");
				require_once("settings/admin.php");

				if ($showeDocIt) {
					require_once("settings/eDOCIt.php");
				}
				?>
			</div>
		</div>
	</main>
	<footer class="footer mt-auto pt-3">
		<?php require_once("common/footer.php"); ?>
	</footer>
	<?php require_once("common/snackbar.php"); ?>
</body>

<?php require_once('common/messages.php'); ?>

</html>