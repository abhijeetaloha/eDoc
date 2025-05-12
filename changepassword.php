<?php
require_once('common/commonfunctions.php');
require_once('config.php');
if ($error != "") {
	echo '<html><head><META HTTP-EQUIV="Pragma" CONTENT="no-cache"><META HTTP-EQUIV="Expires" CONTENT="-1"><script src="common/post.js"></script><script>function load(){post("index.php",{ERROR:"' . $error . '"});}</script></head><body onload="load();"></body></html>';
	exit;
}
$exp = date("m/d/Y", mktime(0, 0, 0, date("m"), (date("d") + 90),   date("Y")));

?>
<html>

<head>
	<?php require_once("common/headers.php"); ?>
	<style>
		.SignInTitle {
			font-size: 18px;
			font-weight: bold;
			font-style: normal;
			height: 30px;
		}

		.ErrorTitle {
			font-size: 18px;
			font-weight: bold;
			font-style: normal;
			height: 30px;
			color: red;
		}
	</style>
	<?php require_once("common/session.php"); ?>
	<script>
		$(window).on("load", function() {
			setupSession();
		});

		function enableButtons() {
			get("ChgPassBtn").disabled = false;
		}

		function disableButtons() {
			get("ChgPassBtn").disabled = true;
		}

		function clearError() {
			get("MsgCell").innerHTML = "Login to eDOCSignature";
			get("MsgCell").className = "SignInTitle";
		}

		function setError(msg) {
			get("MsgCell").innerHTML = msg;
			get("MsgCell").className = "ErrorTitle";
		}

		function doChangePassword() {
			setError('');
			var pass = get("NewPass").value;
			var cpass = get("NewPassC").value;

			if (pass !== cpass) {
				setError('Passwords do not match');
				return false;
			}

			var temp = {
				session: SID,
				controlid: CID,
				resource: "USERS",
				action: "UPDATEUSERDATA",
				expiration: "<?= $exp ?>",
				lockedout: false,
				username: sessionUser,
				password: pass
			};
			pleaseWait("Processing request   ");
			doRestCall("common/rest.php", temp, processChangePasswordJSON);
		}

		function processChangePasswordJSON(theJSON) {
			pleaseWait("");
			var theData = JSON.parse(theJSON);
			if (!theData.result) {
				setError(theData.error);
			} else {
				post("landing.php", {
					SID: SID,
					CID: CID
				});
			}
		}
		window.location.hash = "no-back-button";
		window.location.hash = "Again-No-back-button"; //again because google chrome don't insert first hash into history
		window.onhashchange = function() {
			window.location.hash = "no-back-button";
		}
	</script>
</head>

<body onload="get('NewPass').focus" class="d-flex flex-column">
	<header>
		<?php require_once("common/menu.php"); ?>
	</header>
	<main class="container d-flex justify-content-center mt-3">
		<table cellpadding="0" cellspacing="0" width="400">
			<tr>
				<td align="center" height="45" id="MsgCell" style="font-size:20px" colspan="2">Choose a new password</td>
			</tr>
			<tr>
				<td align="left"><input type="password" class="hidden" autocomplete="new-password">New Password:</td>
				<td align="center"><input autocomplete="new-password" id="NewPass" style="width: 250px" ngControl="NewPass" class="form-control" placeholder="Password" type="password"></td>
			</tr>
			<tr>
				<td colspan="2" height="10px">&nbsp;</td>
			</tr>
			<tr>
				<td align="left">Confirm Password:</td>
				<td align="center"><input autocomplete="new-password" id="NewPassC" style="width: 250px" ngControl="NewPassC" class="form-control" placeholder="Confirm Password" type="password"></td>
			</tr>
			<tr>
				<td colspan="2" height="10px">&nbsp;</td>
			</tr>
			<tr>
				<td height="30" align="center" colspan="2"><button type="button" id="ChgPassBtn" class="ediBtn navNext" onclick="doChangePassword();">Submit</button></td>
			</tr>
		</table>
	</main>
	<footer class="footer mt-auto pt-3">
		<?php require_once("common/footer.php"); ?>
	</footer>
	<?php require_once("common/snackbar.php"); ?>
</body>

</html>