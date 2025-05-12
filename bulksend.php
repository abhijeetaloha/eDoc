<?php
require_once('common/commonfunctions.php');
require_once('config.php');

getOriginNav(__DIR__);
checkLogin();

$XVARS = (isset($_POST) && count($_POST) > 0) ? $_POST : $_GET;
$tempDir = isset($cfg['db']['tempdir']) ? $cfg['db']['tempdir'] : "temp/";
?>
<html>

<head>
	<?php require_once("common/headers.php"); ?>
	<style>
		.subbtn {
			background-color: #05A8FF;
			border: 1px solid grey;
			border-radius: 4px 4px 0px 0px;
			height: 30px;
			width: 110px;
			cursor: pointer;
			color: white;
			text-align: center;
			vertical-align: middle;
			line-height: 30px;
		}

		.subbtnactive {
			color: #0082cb;
			background-color: #FFFFFF;
			border: 1px solid grey;
			border-bottom: none;
			border-radius: 4px 4px 0px 0px;
			height: 30px;
			width: 110px;
			cursor: pointer;
			text-align: center;
			vertical-align: middle;
			line-height: 30px;
		}

		.pageBtn {
			height: 30px;
			line-height: 30px;
		}

		#LinkExpire {
			width: 100px;
			display: inline-block;
		}

		.ColTextTen {
			padding-left: 10px;
		}

		.ColTextTwenty {
			padding-left: 25px;
		}

		.Overflow {
			text-overflow: ellipsis;
		}
	</style>
	<link href="common/formsandinputs.css?id=<?= $vrs ?>" rel="stylesheet" type="text/css">
	<script src="common/dragdoc.js?id=<?= $vrs ?>"></script>
	<script src="common/formsandinputs.js?id=<?= $vrs ?>"></script>
	<?php require_once("common/session.php"); ?>
	<script>
		var tempDir = "<?php echo $tempDir; ?>";
		var addRefOnly = <?= $addrefonly ?>;
		var showResendAuthCode = <?php echo (getSetting('ShowResendAuthCode')) ? "true" : "false"; ?>;
		var showAuthCode = <?php echo (getSetting('HideAuthCodes')) ? "false" : "true"; ?>;
		var requireAuthCode = <?php echo (getSetting('RequireAuthCode')) ? "true" : "false"; ?>;
		var authCodeHTMLClass = '';
		var showSendDoc = <?php echo (getSetting('ShowSendDoc')) ? "true" : "false"; ?>;
		var enableHelcimPayments = <?php echo (getSetting('EnableHelcimPayments')) ? "true" : "false"; ?>;
		var enablePersonalMessaging = <?php echo (getSetting('EnablePersonalMessaging')) ? "true" : "false"; ?>;
		var targetTables = [];
		<?php
		$tables = getSetting('TargetTables');
		if (!isset($tables)) {
			$tables = [];
		}
		for ($i = 0; $i < count($tables); $i++) {
			echo 'targetTables.push("' . $tables[$i] . '");';
		}
		?>
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
			get("SearchDocTable").className = "hidden";
			get("ResultsBulkTable").className = "hidden";
			get("BulkAddTable").className = "hidden";
			get("SetupBulkTable").className = "hidden";
		}


		function showPage(page) {
			hideAllManageDocTables();
			if (page == "SetupDoc") {
				$('footer').hide();
			} else {
				$('footer').show();
			}
			helpPage = helpPageURL?.pages?.bulksend ?? '';
			if (page == "Search") {
				get("BulkSearchResultsCell").innerHTML = "";
				if (handoffFromProDOC == "MNG_DOCS") {
					//get("DocSrchBackBtnCell").className="hidden";
				}
				setMenuTitle("Search for Bulk Send");
				get("SearchDocTable", "table").className = "";
				setupSearchPage();
			} else if (page == "DocSearchResults") {
				setMenuTitle("Bulk Send Results");
				get("ResultsBulkTable", "table").className = "";
				subPage("History");
			} else if (page == "AddSigners") {
				setMenuTitle("Add Bulk Send Data");
				get("BulkAddTable", "table").className = "";
				drawAddSignersPage();
			} else if (page == "BulkSendSetup") {
				setMenuTitle("Set Up New Bulk Send");
				get("SetupBulkTable").className = "";
				setupBulkSend();
			} else if (page == "BulkSendSetupBack") {
				setMenuTitle("Set Up New Bulk Send");
				get("SetupBulkTable").className = "";
				reloadSetupBulk();
			}
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
			getTemplateList("");
			$retentiondate = "-" + getretentiondperiod() + "D";
			showPage("DocSearchResults");
			<?php
			if (isset($XVARS['PKGID']) && $XVARS['PKGID'] <> "") {
				echo 'showPage("Search");';
			} else {
				echo 'setupSearchPage();';
			}
			?>
			//$( ".date" ).attr("min", $retentiondate);
			<?php
			$url = getSetting('SignNowReturnURL');
			if (isset($url)) {
				echo 'redirectURL="' . $url . '";';
			}
			$NT = getSetting('NotificationTypes');
			$NT2 = getSetting('NotificationTypes2');
			if (isset($NT) && !isset($NT2)) {
				for ($i = 0; $i < count($NT); $i++) {
					$NT2[$NT[$i]] = $NT[$i];
				}
			}
			if (isset($NT2)) {
				echo 'notificationTypes=[];';
				foreach ($NT2 as $ind => $val) {
					//while(list($ind,$val)=each($NT2)){
					echo 'notificationTypes.push({"name":"' . $ind . '","value":"' . $val . '"});';
				}
			}
			$idCheckOptions = getSetting('IDCheckOptions');
			if (!isset($idCheckOptions)) {
				$idCheckOptions = [];
			}
			if (count($idCheckOptions) > 0) {
				echo 'idCheckProviders=[];';
				for ($i = 0; $i < count($idCheckOptions); $i++) {
					echo 'idCheckProviders.push("' . $idCheckOptions[$i] . '");';
				}
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

<body class="d-flex flex-column">
	<header>
		<?php require_once("common/menu.php"); ?>
	</header>
	<main class="flex-shrink-0">
		<?php
		require_once("bulksend/searchbulk.php");
		require_once("bulksend/bulkresults.php");
		require_once("bulksend/newbulksend.php");
		require_once("bulksend/setupbulksend.php");
		?>
	</main>
	<footer class="footer mt-auto pt-3">
		<?php require_once("common/footer.php"); ?>
	</footer>
	<?php require_once("common/snackbar.php"); ?>
</body>
<?php require_once("common/messages.php"); ?>

</html>