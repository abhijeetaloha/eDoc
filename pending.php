<?php
require_once('common/commonfunctions.php');
require_once('config.php');

getOriginNav(__DIR__);
checkLogin();

$tempDir = isset($cfg['db']['tempdir']) ? $cfg['db']['tempdir'] : "temp/";
?>
<html>

<head>
	<?php require_once("common/headers.php"); ?>
	<script src="pending/pendingdocs.js?r=<?= $vrs ?>"></script>
	<?php require_once("common/session.php"); ?>
	<script>
		document.addEventListener("DOMContentLoaded", () => {
			helpPage = helpPageURL?.pages?.pending ?? "";
		});
		MAX_FILE_SIZE = <?php if (isset($cfg['db']['MaxUploadSize'])) {
							echo $cfg['db']['MaxUploadSize'];
						} else {
							echo '1000000';
						} ?>;
		var tempDir = "<?php echo $tempDir; ?>";
		var addRefOnly = <?= $addrefonly ?>;

		function enableButtons() {

		}

		function disableButtons() {

		}

		function addUploadedDoc(thePDF, formName) {
			let floneext = getFileExt(thePDF);
			let fltwoext = getFileExt(formName);
			if (floneext != fltwoext) {
				formName += '.' + floneext;
			}
			pleaseWait("Saving Document... ");
			pendingDocUpload(thePDF, formName);
		}

		/**
		 * Processes an array of file objects, converting documents and handling errors.
		 *
		 * @param {UploadFileArrayObject[]} fileArrayJSON - An array of file objects to be processed.
		 */
		window.uploadmsg = handleUploadMsg;

		$(window).on("load", function() {
			loadGoDSeal();
			setupSession();
			getUserPendingDocs();
			setMenuTitle("My Pending Docs");
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
		<iframe class="hidden" id="upload_target" name="upload_target" style="width:0;height:0;border:0px solid #fff;"></iframe>
		<form id="UploadForm" action="upload.php" method="post" enctype="multipart/form-data" target="upload_target" class="hidden">
			<input id="SID" name="SID" value="<?php echo $SID; ?>" type="hidden"><input class="hidden" name="fileupload[]" id="fileupload" onchange='uploadDocuments("fileupload", "UploadForm");' accept=".jpg,.jpeg,.bmp,.tif,.png,.tiff,.gif,.pdf,.docx,.doc" type="file" multiple>
		</form>
		<table cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td class="navrow" height="40px">
					<table width="100%">
						<tr>
							<td width="20px">&nbsp;</td>
							<td width="80px">
								<button type="button" class="ediBtn navBack" id="goToLandingButton" onclick=goToLanding();>Menu</button>
							</td>
							<td>&nbsp;</td>
							<td width="80px">
								<button type="button" class="ediBtn navBack" id="uploadPendingButton" onclick="get('fileupload').click();">Upload</button>
							</td>
							<td width="20px">&nbsp;</td>
							<td width="80px">
								<button type="button" class="ediBtn navNext" id="uploadPendingButton" onclick="savePendingDocNames()">Save</button>
							</td>
							<td width="20px">&nbsp;</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td align="center" class="p-3">
					<div class="row pendingDocsDrop">
						<div class="col-12 overflow-auto" style="height: calc(100% - 200px);" id="PendingDocList" ondrop="dropUpload(event)" ondragover="allowDrop(event)">
							<ul class="list-group">
								<li class="list-group-item">No Documents Found</li>
								<li class="list-group-item">drag and drop any .pdf, .doc, .png, .jpg or .tiff file.</li>
							</ul>
						</div>
					</div>
				</td>
			</tr>
		</table>
	</main>
	<footer class="footer mt-auto pt-3">
		<?php require_once("common/footer.php"); ?>
	</footer>
	<?php require_once("common/snackbar.php"); ?>
	<?php require_once("common/messages.php"); ?>
</body>

</html>