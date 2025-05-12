<?php
require_once('common/commonfunctions.php');
require_once('config.php');
require_once('common/idprovider.php');

getOriginNav(__DIR__);
checkLogin();

$tempDir = isset($cfg['db']['tempdir']) ? $cfg['db']['tempdir'] : "temp/";
$localTempDir = isset($cfg['db']['localtempdir']) ? $cfg['db']['localtempdir'] : $tempDir;
$microservicesURL = isset($cfg['db']['eServices']) ? $cfg['db']['eServices'] : $cfg['db']['MicroservicesURL'];
?>
<html>

<head>
	<?php require_once("common/headers.php"); ?>
	<link href="common/formsandinputs.css?id=<?= $vrs ?>" rel="stylesheet" type="text/css">
	<script src="common/setup.js?id=<?= $vrs ?>"></script>
	<?php require_once("common/session.php"); ?>
	<script>
		var tempDir = "<?php echo $tempDir; ?>";
		var localTempDir = "<?php echo $localTempDir; ?>";
		MAX_FILE_SIZE = <?php if (isset($cfg['db']['MaxUploadSize'])) {
							echo $cfg['db']['MaxUploadSize'];
						} else {
							echo '1000000';
						} ?>;
		var pkgID = "<?php echo $XVARS['PkgID'] ?? '' ?>";
		var showDataEntry = <?php echo (getSetting('ShowDataEntry')) ? "true" : "false"; ?>;
		var showIndices = <?php echo (getSetting('ShowIndices')) ? "true" : "false"; ?>;
		var showSharing = <?php echo (getSetting('ShowSharing')) ? "true" : "false"; ?>;
		var supportFillableFields = <?php echo (getSetting('SupportFillableFields')) ? "true" : "false"; ?>;
		var showDelegate = <?php echo (userhasperm("eDOCSig Allow Delegation")) ? "true" : "false"; ?>;
		var showeSigners = 
		var showSignerGroups = <?php echo (getSetting('ShowSignerGroups')) ? "true" : "false"; ?>;
		let targetTables = [];
		var microservicesUrlSet = <?php echo !empty($microservicesURL) ? "true" : "false"; ?>;
		<?php $tables = getSetting('TargetTables');
		if (!isset($tables)) {
			$tables = [];
		}
		for ($i = 0; $i < count($tables); $i++) {
			echo 'targetTables.push("' . $tables[$i] . '");';
		} ?>
		var addRefOnly = <?= $addrefonly ?>;
		var goTo = "";
		var callSigners = true;

		function enableButtons() {

		}

		function disableButtons() {

		}

		function hideAllSendDocTables() {
			get("SelectTypeTable").className = "hidden";
			get("UploadDocTable").className = "hidden";
			get("SelectSignersTable").className = "hidden";
			get("IndexFieldsTable").className = "hidden";
			get("SetupDocTable").className = "hidden";
			get("ReviewTable").className = "hidden";
			get("SharingTable").className = "hidden";
		}

		function showPage(page) {
			hideAllSendDocTables();
			if (goBackToReview) {
				page = "Review";
				goBackToReview = false;
			}
			if (page == "SetupDoc") {
				$('.footer').hide();
			} else {
				$('.footer').show();
			}
			if (page == "Upload") {
				setMenuTitle("Upload Document");
				get("UploadDocTable", "table").className = "";
				helpPage = helpPageURL?.pages?.setupTemplate?.upload ?? '';
				if (templateType == "Ref") {
					$('.DocListHeader').each(function() {
						$(this).removeClass('DocListHeader').addClass("DocReferenceHeader");
					});
					$('.ReviewSendCell').removeClass('ReviewSendCell').addClass("ReviewReferenceSendCell");
				} else {
					$('.DocReferenceHeader').each(function() {
						$(this).removeClass('DocReferencenHeader').addClass("DocListHeader");
					});
					$('.ReviewReferenceSendCell').removeClass('ReviewReferenceSendCell').addClass("ReviewSendCell");
				}
			} else if (page == "SelectType") {
				setMenuTitle("Select Template Type");
				get("SelectTypeTable", "table").className = "";
				helpPage = helpPageURL?.pages?.setupTemplate?.selectType ?? '';
			} else if (page == "Indices") {
				setMenuTitle(`${truncate(`Index Data for "${uploadedDocs[currentDoc].formname}`, 35)}"`);
				get("IndexFieldsTable", "table").className = "";
				includeNone = true;
				if (!selectedDefinition) {
					selectedDefinition = "";
				}
				getIndexForms();
				checkIndicesBackButton();
				helpPage = helpPageURL?.pages?.setupTemplate?.indices ?? '';
			} else if (page == "SetupDoc") {
				setMenuTitle(`${truncate(`Set Up Template for "${uploadedDocs[currentDoc].formname}`, 35)}"`);
				get("SetupDocTable", "table").className = "";
				setupTheSetupScreen();
				helpPage = helpPageURL?.pages?.setupTemplate?.setupDoc ?? '';
			} else if (page == "Sharing") {
				setMenuTitle(`${truncate(`Sharing for "${uploadedDocs[currentDoc].formname}`, 35)}"`);
				get("SharingTable", "table").className = "";
				setupSharing();
				helpPage = helpPageURL?.pages?.setupTemplate?.sharing ?? '';
			} else if (page == "Signers") {
				setMenuTitle("Select Signer Roles");
				get("SelectSignersTable", "table").className = "";
				searchRolesCBFunction = 'getSigners';
				if (callSigners) {
					searchRoles();
				}
				callSigners = false;
				helpPage = helpPageURL?.pages?.setupTemplate?.signers ?? '';
				drawSigners();
			} else if (page == "Review") {
				setMenuTitle("Review and Create Template");
				get("ReviewTable", "table").className = "";
				setupReview();
				helpPage = helpPageURL?.pages?.setupTemplate?.review ?? '';
			}
		}

		function loadGroupSubTable() {
			//dont do anything
		}
		$(window).on("load", function() {
			loadGoDSeal();
			if (isInternetExplorer()) {
				PDFJS.workerSrc = "<?= $pdfjsURL ?>/pdf.worker.mjs";
			} else {
				PDFJS = this.pdfjsLib;
				PDFJS.GlobalWorkerOptions.workerSrc = "<?= $pdfjsURL ?>/build/pdf.worker.mjs";
			}
			setupSession();
			getSignerGroups();
			saveSetUpTemplate = true;
			if (pkgID == "") {
				pkgID = generateGUID();
			}
			<?php
			echo 'defaultNotificationName="' . $fullName . '";';
			echo 'defaultNotificationEmail="' . $userEmail . '";';
			getAndEchoIDProdiverSettings();
			if (isset($XVARS['DU'])) {
				echo 'goTo="Upload";pleaseWait("Converting file");ajax_do_post("upload.php","SID="+SID+"&CnvrtFile=' . $XVARS["NewFile"] . '&theFile=' . $XVARS["NewFileName"] . '");';
			} else {
				echo 'showPage("SelectType");';
			}
			?>
			document.getElementById('TagsModalID').onclick = (params) => {
				saveModifiedTags();
				// if (uploadedDocs[currentDoc]) {
				// 	saveDocChanges();
				// }
			}
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
		require_once("setuptemplate/selecttype/selecttype.php");
		require_once("setuptemplate/uploaddoc/uploaddoc.php");
		require_once("setuptemplate/selectsigners/selectsigners.php");
		require_once("setuptemplate/indices/indices.php");
		require_once("setuptemplate/sharing/sharing.php");
		require_once("setuptemplate/setup/setup.php");
		require_once("setuptemplate/review/review.php");
		?>
	</main>
	<footer class="footer mt-auto pt-3">
		<?php require_once("common/footer.php"); ?>
	</footer>
	<?php require_once("common/snackbar.php"); ?>
</body>
<?php require_once('common/messages.php'); ?>

</html>