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
		.tabbtn {
			background-color: #0082cb;
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

		.tabHbtn {
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

		.scrollIt {
			width: 300px;
			height: 200px;
			overflow-y: scroll;
		}
	</style>
	<?php require_once("common/session.php"); ?>
	<script>
		var tempDir = "<?php echo $tempDir; ?>";
		var gotOnDemandReportList = false;
		var addRefOnly = <?= $addrefonly ?>;
		MAX_FILE_SIZE = <?php if (isset($cfg['db']['MaxUploadSize'])) {
							echo $cfg['db']['MaxUploadSize'];
						} else {
							echo '1000000';
						} ?>;
		var showOnDemandReports = <?php echo (getSetting('ShowOnDemandReports')) ? "true" : "false"; ?>;

		function hideAllReportTables() {
			get("HistoryBtn").className = "tabbtn";
			get("HistoryTable").className = "hidden";
			get("DetailsBtn").className = "tabbtn";
			get("DetailsTable").className = "hidden";
			//get("ScheduleBtn").className="tabbtn";
			//get("ScheduleTable").className="hidden";
		}

		function showPage(page) {
			hideAllReportTables();
			if (page == "History") {
				setMenuTitle("Report History");
				get("HistoryTable", "table").className = "";
				get("HistoryBtn").className = "tabHbtn";
				helpPage = helpPageURL?.pages?.reports?.history ?? '';
			} else if (page == "Details") {
				setMenuTitle("Run Report");
				get("DetailsTable", "table").className = "";
				get("DetailsBtn").className = "tabHbtn";
				if (!gotOnDemandReportList) {
					getOnDemandReportList();
				}
				helpPage = helpPageURL?.pages?.reports?.details ?? '';
			}
			//else if(page == "Schedule"){
			//	setMenuTitle("Schedule Report");
			//	get("ScheduleTable","table").className="";
			//	get("ScheduleBtn").className="tabHbtn";
			//	helpPage="#t=Reports.htm";			
			//}
		}

		function enableButtons() {

		}

		function disableButtons() {

		}
		$(window).on("load", function() {
			if (!showOnDemandReports) {
				get("RptDetailsBtn").className = "hidden";
			}
			loadGoDSeal();
			setupSession();
			showPage("History");
			//$( ".date" ).attr("min", $retentiondate);
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
		<table cellpadding="0" cellspacing="0" height="100%" width="100%">
			<tr>
				<td class="navrow" height="60px">
					<table width="100%">
						<tr>
							<td width="20px">&nbsp;</td>
							<td width="80px"><button type="button" id="goToLandingBtn" class="ediBtn navBack" onclick=goToLanding();>Menu</button></td>
							<td>&nbsp;</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class="navrow" height="60px" valign="bottom">
					<table width="100%">
						<tr>
							<td align="center" valign="bottom">
								<table cellpadding="0" cellspacing="0" height="100%">
									<tr>
										<td width="115" id="RptHistoryBtn" align="center" valign="bottom"><button id="HistoryBtn" type="button" class="tabbtn" onclick="showPage('History')">History</button></td>
										<td width="115" id="RptDetailsBtn" align="center" valign="bottom"><button id="DetailsBtn" type="button" class="tabHbtn" onclick="showPage('Details')">Run Report</button></td>
										<td width="115" id="RptScheduleBtn" align="center" valign="bottom" class="hidden"><button id="ScheduleBtn" type="button" class="tabbtn" onclick="showPage('Schedule')">Schedule</button></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td align="center" valign="top">
					<?php
					require_once("reports/history.php");
					require_once("reports/schedule.php");
					require_once("reports/details.php");
					?>
				</td>
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