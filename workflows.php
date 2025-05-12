<?php
require_once('common/commonfunctions.php');
require_once('config.php');

getOriginNav(__DIR__);
checkLogin();

$XVARS = (isset($_POST) && count($_POST) > 0) ? $_POST : $_GET;
$tempDir = isset($cfg['db']['tempdir']) ? $cfg['db']['tempdir'] : "temp/";
?>
<!DOCTYPE html>
<html>

<head>
	<?php require_once("common/headers.php"); ?>
	<script src="common/formsandinputs.js?id=<?= $vrs ?>"></script>
	<script src="common/owners.js?id=<?= $vrs ?>"></script>
	<script src="common/dragdoc.js?id=<?= $vrs ?>"></script>
	<script src="workflows/AddWorkflow.js?id=<?= $vrs ?>"></script>
	<script src="workflows/EditWorkflow.js?id=<?= $vrs ?>"></script>
	<script src="workflows/SearchWorkflows.js?id=<?= $vrs ?>"></script>
	<link href="common/formsandinputs.css?id=<?= $vrs ?>" rel="stylesheet" type="text/css">
	<?php require_once("common/session.php"); ?>
	<script>
		var tempDir = "<?php echo $tempDir; ?>";
		var addRefOnly = <?= $addrefonly ?>;
		MAX_FILE_SIZE = <?php if (isset($cfg['db']['MaxUploadSize'])) {
							echo $cfg['db']['MaxUploadSize'];
						} else {
							echo '1000000';
						} ?>;

		function enableButtons() {

		}

		function disableButtons() {

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
			<?php
			if ($XVARS['ACTION'] == 'Manage') {
				echo 'SearchWorkflows();';
			} else {
				echo 'setMenuTitle("Create Package Type");showAddWorkflow();';
			}
			?>
		});
		window.location.hash = "no-back-button";
		window.location.hash = "Again-No-back-button"; //again because google chrome doesn't insert first hash into history
		window.onhashchange = function() {
			window.location.hash = "no-back-button";
		}
	</script>
</head>

<body id="content" class="d-flex flex-column">
	<header>
		<?php require_once("common/menu.php"); ?>
	</header>
	<main class="flex-shrink-0">
		<div align="center" valign="top" class="maincontent" id="workflowscell"></div>
	</main>
	<footer class="footer mt-auto pt-3">
		<?php require_once("common/footer.php"); ?>
	</footer>
	<?php require_once("common/snackbar.php"); ?>
</body>
<?php require_once('common/messages.php'); ?>

</html>