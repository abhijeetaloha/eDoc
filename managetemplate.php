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
	<script src="common/setup.js?id=<?= $vrs ?>"></script>
	<script src="common/editIndices.js?id=<?= $vrs ?>"></script>
	<?php require_once("common/session.php"); ?>
	<script>
		var tempDir = "<?php echo $tempDir; ?>";
		var localTempDir = "<?php echo $localTempDir; ?>";
		var microservicesUrlSet = <?php echo !empty($microservicesURL) ? "true" : "false"; ?>;
		var showIndices = "<?= getSetting('ShowIndices') ?>";
		var showDataEntry = <?php echo (getSetting('ShowDataEntry')) ? "true" : "false"; ?>;
		var showeSigners = <?php echo (getSetting('ShoweSigners')) ? "true" : "false"; ?>;
		var showSignerGroups = <?php echo (getSetting('ShowSignerGroups')) ? "true" : "false"; ?>;
		var addRefOnly = <?= $addrefonly ?>;
		var showDelegate = <?php echo (userhasperm("eDOCSig Allow Delegation")) ? "true" : "false"; ?>;
		let targetTables = [];
		<?php $tables = getSetting('TargetTables');
		if (!isset($tables)) {
			$tables = [];
		}
		for ($i = 0; $i < count($tables); $i++) {
			echo 'targetTables.push("' . $tables[$i] . '");';
		} ?>
		MAX_FILE_SIZE = <?php if (isset($cfg['db']['MaxUploadSize'])) {
							echo $cfg['db']['MaxUploadSize'];
						} else {
							echo '1000000';
						} ?>;

		function enableButtons() {

		}

		function disableButtons() {

		}

		function hideAllManageDocTables() {
			// get("SearchDocTable")?.classList.add("hidden");
			get("ResultsDocTable")?.classList.add("hidden");
			get("EditDocTable")?.classList.add("hidden");
		}

		function showPage(page) {
			hideAllManageDocTables();
			if (page == "EditDoc") {
				$('.footer').hide();
			} else {
				$('.footer').show();
			}
			helpPage = helpPageURL?.pages?.manageTemplate?.search ?? '';
			if (page == "Search") {
				setMenuTitle("Search for Templates");
				get("SearchDocTable").className = "";
				setupSearchPage();
				helpPage = helpPageURL?.pages?.manageTemplate?.docSearchResults ?? '';
			} else if (page == "DocSearchResults") {
				setMenuTitle("Manage Templates");
				get("ResultsDocTable").className = "";
				getTemplateSearchResults();
			} else if (page == "EditDoc") {
				setMenuTitle(`${truncate(`Edit Template "${uploadedDocs[currentDoc].formname}`, 35)}"`);
				get("EditDocTable").classList.remove('hidden');
				drawDocForEdit();
				helpPage = helpPageURL?.pages?.manageTemplate?.editDoc ?? '';
			}
		}

		function loadGroupSubTable() {
			//dont do anything
			console.log('signer groups loaded');
		}
		$(window).on("load", function() {
			loadGoDSeal();
			getSignerGroups();
			if (isInternetExplorer()) {
				PDFJS.workerSrc = "<?= $pdfjsURL ?>/pdf.worker.mjs";
			} else {
				PDFJS = this.pdfjsLib;
				PDFJS.GlobalWorkerOptions.workerSrc = "<?= $pdfjsURL ?>/build/pdf.worker.mjs";
			}
			<?php
			getAndEchoIDProdiverSettings();
			?>
			setupSession();
			<?php
			if ((isset($XVARS['TID']) && $XVARS['TID'] <> "")) {
				echo 'getDocForEdit("' . $XVARS['TID'] . '");';
			} else {
				echo 'searchStart = 0;doSearch({orderBy: "", page: 0});';
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
		require_once("managetemplate/searchdoc/searchdoc.php");
		require_once("managetemplate/docresults/docresults.php");
		require_once("managetemplate/editdoc/editdoc.php");
		?>
	</main>
	<footer class="footer mt-auto pt-3">
		<?php require_once("common/footer.php"); ?>
	</footer>
	<?php require_once("common/snackbar.php"); ?>
</body>
<?php require_once('common/messages.php'); ?>

</html>