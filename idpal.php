<?php
require_once('common/commonfunctions.php');
require_once('config.php');
$error = checkLogin();
if ($error != "") {
	echo '<html><head><META HTTP-EQUIV="Pragma" CONTENT="no-cache"><META HTTP-EQUIV="Expires" CONTENT="-1"><script src="common/post.js"></script><script>function load(){post("index.php",{ERROR:"' . $error . '"});}</script></head><body onload="load();"></body></html>';
	exit;
}
$XVARS = (isset($_POST) && count($_POST) > 0) ? $_POST : $_GET;
$tempDir = isset($cfg['db']['tempdir']) ? $cfg['db']['tempdir'] : "temp/";
// # TODO: Remove exposing eServices URL to javascript
$eServicesURL = $cfg['db']['eServices'];

?>
<html>

<head>
	<?php require_once("common/headers.php"); ?>
	<link href="common/formsandinputs.css?id=<?= $vrs ?>" rel="stylesheet" type="text/css">
	<script src="common/formsandinputs.js?id=<?= $vrs ?>"></script>
	<script src="common/setup.js?id=<?= $vrs ?>"></script>
	<script src="idpal/IdPal.js?id=<?= $vrs ?>"></script>
	<?php require_once("common/session.php"); ?>
	<script>
		var tempDir = "<?php echo $tempDir; ?>";
		var fonta = "<?php echo $fontAwesomeURL; ?>";
		var eServicesURL = "<?php echo $eServicesURL; ?>";
		var showDelegate = <?php echo (userhasperm("eDOCSig Allow Delegation")) ? "true" : "false"; ?>;
		let IDPalSigners = [];
		let canManageIDPal = <?php echo (userhasperm("eDOCSig Manage Documents")) ? "true" : "false"; ?>;

		function enableButtons() {
			$('.TrashCanClass').css("pointer-events", "auto");
		}

		function disableButtons() {
			$('.TrashCanClass').css("pointer-events", "none");
		}

		function hideIDPalVerificationElements() {
			$("#BlueHeaderRow").hide();
			$(".LoginFooter").hide();
			$("#IdPalVerification").hide();
		}

		function hideIDPalSubmissionElements() {
			$("#IdPalSubmissionHeader").hide();
			$("#IdPalSubmissionFooter").hide();
			$("#IdPalSubmission").hide();
		}

		function showPage(page, index) {
			if (page == "IDPalSumbmission") {
				hideIDPalVerificationElements();
				$("#IdPalSubmissionHeader").show();
				$("#IdPalSubmissionFooter").show();
				$("#IDPalReviewAccept").hide();
				$("#IDPalReviewReject").hide();
				get("IdPalSubmission", "div").innerHTML = "";
				IdPalUserVerificationDetailInfo(index);
				$("#IdPalSubmission").show();
				// helpPage = helpPageURL?.pages?.idPal?.idPalSubmission ?? '';
			} else if (page == "IDPalVerification") {
				hideIDPalSubmissionElements();
				$("#BlueHeaderRow").show();
				$(".LoginFooter").show();
				setMenuTitle("ID-Pal Verification");
				get("IdVerificationResult", "div").innerHTML = "";
				IdPalUserStatusDetails();
				$("#IdPalVerification").show();
				// helpPage = helpPageURL?.pages?.idPal?.idPalVerification ?? '';;
			}
		}

		$(window).on("load", function() {
			if (isInternetExplorer()) {
				PDFJS.workerSrc = "<?= $pdfjsURL ?>/pdf.worker.mjs";
			} else {
				PDFJS = this.pdfjsLib;
				PDFJS.GlobalWorkerOptions.workerSrc = "<?= $pdfjsURL ?>/build/pdf.worker.mjs";
			}
			setupSession();
			showPage("IDPalVerification");
			<?php
			$url = getSetting('SignNowReturnURL');
			if (isset($url)) {
				echo 'redirectURL="' . $url . '";';
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
		<div id="IdPalSubmissionHeader" class="header">
			<div class="headerlogo" onclick="goToLanding();"></div>
			<div class="headertitle"></div>
		</div>
	</header>
	<main class="flex-shrink-0">
		<?php
		require_once("idpal/idpalverification.php");
		require_once("idpal/idpalreview.php");
		?>
	</main>
	<footer class="footer mt-auto pt-3">
		<?php require_once("common/footer.php"); ?>
		<div class='vtc_footer' id='IdPalSubmissionFooter'>
			<div onclick='showPage("IDPalVerification");'>Back</div>
			<div id='IDPalReviewAccept' onclick=IDPalReviewSubmit(true)>Accept</div>
			<div id='IDPalReviewReject' onclick=IDPalReviewSubmit(false)>Reject</div>
		</div>
	</footer>
</body>
<?php require_once('common/messages.php'); ?>

</html>