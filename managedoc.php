<?php
require_once('common/commonfunctions.php');
require_once('config.php');
require_once('common/idprovider.php');

getOriginNav(__DIR__);
checkLogin();

$XVARS = (isset($_POST) && count($_POST) > 0) ? $_POST : $_GET;
$tempDir = isset($cfg['db']['tempdir']) ? $cfg['db']['tempdir'] : "temp/";
$localTempDir = isset($cfg['db']['localtempdir']) ? $cfg['db']['localtempdir'] : $tempDir;
$microservicesURL = isset($cfg['db']['eServices']) ? $cfg['db']['eServices'] : $cfg['db']['MicroservicesURL'];

?>
<html>

<head>
	<?php require_once("common/headers.php"); ?>
	<link href="common/formsandinputs.css?id=<?= $vrs ?>" rel="stylesheet" type="text/css">
	<script src="common/dragdoc.js?id=<?= $vrs ?>"></script>
	<script src="common/formsandinputs.js?id=<?= $vrs ?>"></script>
	<script src="common/setup.js?id=<?= $vrs ?>"></script>
	<script src="common/editIndices.js?id=<?= $vrs ?>"></script>
	<?php require_once("common/session.php"); ?>
	<script>
		var tempDir = "<?php echo $tempDir; ?>";
		var localTempDir = "<?php echo $localTempDir; ?>";
		var microservicesUrlSet = <?php echo !empty($microservicesURL) ? "true" : "false"; ?>;
		var showResendAuthCode = <?php echo (getSetting('ShowResendAuthCode')) ? "true" : "false"; ?>;
		var showIndices = <?php echo (getSetting('ShowIndices')) ? "true" : "false"; ?>;
		var showDataEntry = <?php echo (getSetting('ShowDataEntry')) ? "true" : "false"; ?>;
		var showAuthCode = <?php echo (getSetting('HideAuthCodes')) ? "false" : "true"; ?>;
		var requireAuthCode = <?php echo (getSetting('RequireAuthCode')) ? "true" : "false"; ?>;
		var showeSigners = <?php echo (getSetting('ShoweSigners')) ? "true" : "false"; ?>;
		var showSignerGroups = <?php echo (getSetting('ShowSignerGroups')) ? "true" : "false"; ?>;
		var authCodeHTMLClass = '';
		var showSendDoc = <?php echo (getSetting('ShowSendDoc')) ? "true" : "false"; ?>;
		var enableHelcimPayments = <?php echo (getSetting('EnableHelcimPayments')) ? "true" : "false"; ?>;
		var enablePersonalMessaging = <?php echo (getSetting('EnablePersonalMessaging')) ? "true" : "false"; ?>;
		const shouldValidateEmail = <?php echo (getSetting('EmailValidation')) ? "true" : "false"; ?>;
		let targetTables = [];
		hasUnsavedChanges = false;

		<?php
		$tables = getSetting('TargetTables');
		if (!isset($tables)) {
			$tables = [];
		}
		for ($i = 0; $i < count($tables); $i++) {
			echo 'targetTables.push("' . $tables[$i] . '");';
		}
		?>
		var addRefOnly = <?= $addrefonly ?>;
		var showDelegate = <?php echo (userhasperm("eDOCSig Allow Delegation")) ? "true" : "false"; ?>;
		MAX_FILE_SIZE = <?php if (isset($cfg['db']['MaxUploadSize'])) {
							echo $cfg['db']['MaxUploadSize'];
						} else {
							echo '1000000';
						} ?>;

		function enableButtons() {
			$('.TrashCanClass').css("pointer-events", "auto");
		}

		function disableButtons() {
			$('.TrashCanClass').css("pointer-events", "none");
		}

		function hideAllManageDocTables() {
			// get("SearchDocTable").className = "hidden";
			get("ResultsDocTable").className = "hidden";
			get("EditDocTable").className = "hidden";
			get("DocHistoryTable").className = "hidden";
		}

		function showPage(page) {
			hideAllManageDocTables();
			if (showeSigners) {
				fillListOfeSigners();
			}
			if (page == "EditDoc") {
				$('.footer').hide();
			} else {
				$('.footer').show();
			}
			if (page == "Search") {
				document.getElementById("SignSearchRow").style.display = "";

				get("DocSearchResultsCell").innerHTML = "";
				if (handoffFromProDOC == "MNG_DOCS") {
					//get("DocSrchBackBtnCell").className="hidden";
				}
				setMenuTitle("Search for Packages");
				get("SearchDocTable", "table").className = "";
				setupSearchPage();
				showPackageSearch();
				helpPage = helpPageURL?.pages?.manageDoc?.search ?? '';
			} else if (page == "DefaultPackageSearch") {
				setupSearchPage();
				doDefaultSearch();
			} else if (page == "DocSearchResults") {
				setMenuTitle("Package Results");
				get("ResultsDocTable", "table").className = "";
				drawPackageSearchResults();
				helpPage = helpPageURL?.pages?.manageDoc?.docSearchResults ?? '';;
			} else if (page == "EditDoc") {
				setMenuTitle(`${truncate(`Edit Document "${uploadedDocs[currentDoc].formname}`, 35)}"`);
				get("EditDocTable", "table").className = "";
				drawDocForEdit();
				helpPage = helpPageURL?.pages?.manageDoc?.editDoc ?? '';;
			} else if (page == "DocHistory") {
				setMenuTitle("Document History");
				get("DocHistoryTable", "table").className = "";
				drawHistoryEventsTable();
				helpPage = helpPageURL?.pages?.manageDoc?.docHistory ?? '';;
			}
		}

		function loadGroupSubTable() {
			//dont do anything
			console.log('signer groups loaded');
		}
		$(window).on("load", function() {
			if (!showAuthCode) {
				authCodeHTMLClass = ' class="hidden"';
			}
			loadGoDSeal();
			if (showSignerGroups) {
				getSignerGroups();
			} else {
				loadGroupSubTable();
			}
			if (isInternetExplorer()) {
				PDFJS.workerSrc = "<?= $pdfjsURL ?>/pdf.worker.mjs";
			} else {
				PDFJS = this.pdfjsLib;
				PDFJS.GlobalWorkerOptions.workerSrc = "<?= $pdfjsURL ?>/build/pdf.worker.mjs";
			}
			setupSession();
			$retentiondate = "-" + getretentiondperiod() + "D";
			<?php
			if ((isset($XVARS['PKGID']) && $XVARS['PKGID'] <> "")) {
				echo 'setupSearchPage();showPackage("' . $XVARS['PKGID'] . '");';
			} else {
				echo 'showPage("DefaultPackageSearch");';
			}
			?>
			//$( ".date" ).attr("min", $retentiondate);
			<?php
			$url = getSetting('SignNowReturnURL');
			if (isset($url)) {
				echo 'redirectURL="' . $url . '";';
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
		require_once("managedoc/searchdoc/searchdoc.php");
		require_once("managedoc/docresults/docresults.php");
		require_once("managedoc/editdoc/editdoc.php");
		require_once("managedoc/dochistory/dochistory.php");
		?>
	</main>
	<footer class="footer mt-auto pt-3">
		<?php require_once("common/footer.php"); ?>
	</footer>
	<?php require_once("common/snackbar.php"); ?>
</body>
<?php require_once('common/messages.php'); ?>

</html>