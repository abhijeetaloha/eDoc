<?php
$username = $_SESSION['username'] ?? "";
// Check if the necessary POST variables are set and assign them
$error = $_POST['error'] ?? "";
$multifactorType = $_POST['multifactorType'] ?? "";
$isSetup = $_POST['setup'] ?? "";
$resetpassword = $_POST['resetpassword'] ?? "";
$promptpassword = $_POST['promptpassword'] ?? "";
$successMessage = $_POST['success'] ?? "";
$forceChange = $_POST['forcechange'] ?? false;

?>
<style>
	.form {
		width: 90%;
	}

	.greenbtnwide {
		background-color: #7ed321;
		border: 1px solid grey;
		border-radius: 4px;
		height: 30px;
		width: 100%;
		cursor: pointer;
		color: white;
		text-align: center;
		vertical-align: middle;
		line-height: 30px;
	}

	.greenbtnwide[disabled] {
		background-color: #888;
		border: 1px solid grey;
		opacity: .65;
		cursor: not-allowed;
	}

	.labelfield {
		text-align: left;
		float: left;
	}

	.label {
		display: inline-block;
		max-width: 100%;
		margin-bottom: 5px;
		font-weight: 700;
	}

	.link {
		display: inline-block;
		color: blue;
	}

	.SignInMsg {
		color: Red;
	}

	.SignInTitle {
		font-style: normal;
		height: 30px;
	}

	.ErrorTitle {
		font-style: normal;
		height: 30px;
		color: red;
	}

	.SignInText {
		padding-left: 10px;
		padding-right: 12px;
		padding-bottom: 2px
	}

	.SelectSendText {
		padding-left: 10px;
		padding-right: 12px;
		padding-bottom: 2px
	}

	.SignInPText {
		padding-left: 10px;
		padding-right: 12px;
	}

	.SignInBox {
		border-left: 1px solid grey;
		border-right: 1px solid grey;
		background-color: #FBFBFB;
	}
</style>
<script>
	var currUsername = "";
	var CID = "";
	let wrningMsg = "";
	var newKey = false;
	var chosenOpt = "";
	loginError = "<?php echo $error; ?>";
	multifactorType = "<?php echo $multifactorType; ?>";
	resetPassword = "<?php echo $resetpassword; ?>";
	promptPassword = "<?php echo $promptpassword; ?>";
	username = "<?php echo $username; ?>";
	successMessage = "<?php echo $successMessage; ?>";
	forceChange = <?php echo json_encode($forceChange); ?>;
	var passwordRulesObject = {
		minlength: 0,
		minuppercase: 0,
		minlowercase: 0,
		minnumeric: 0,
		expiration: 0,
		waringdays: 0,
		lockoutminutes: 0,
		lastnpasswords: 0,
		logingraceperiod: 0,
		resetgraceperiod: 0,
	};
	let isSetup = "<?php echo $isSetup ?>";
	// Get the available MF options from the session
	let availableMFOptions = "<?= isset($_SESSION['availableMFOptions']) ? $_SESSION['availableMFOptions'] : ''; ?>";

	let phoneNumber = "<?= isset($_SESSION['phoneNumber']) ? $_SESSION['phoneNumber'] : ''; ?>";
	let email = "<?= isset($_SESSION['email']) ? $_SESSION['email'] : ''; ?>";

	function clearError() {
		pleaseWait("");
		get("errormsg").innerHTML = "";
	}

	function setError(msg) {

		const el = get("errormsg");

		if (el) {
			el.innerHTML = msg;
		}
	}

	function checkForChngPass(theEvent) {
		var keyCode = theEvent.keyCode || theEvent.which;
		if ((keyCode == 13)) {
			setNewPassword();
		}
		return true;
	}

	function showUserLogin(showLoginMsg) {
		let page = `
            <form method="POST" action="api/login.php">
              <div class="form-group">
                ${showLoginMsg ? `<div id="completeMFSetupMsg" class="messages has-change">${showLoginMsg}</div>`: ""}
                  <div class="form-floating">
                    <input autocomplete="username" type="text" name="UserName" id="UserName" class="form-control form-control-lg mt-2" placeholder="Username or Email" required>
                    <label for="UserName" id="UserNameLbl">Username or Email</label>
                  </div>
                  <button type="submit" class="btn btn-success btn-block mt-3" id="NxtLoginBtn">Next</button>
                </div>
              </form>
            `;
		document.getElementById("loginForm").innerHTML = page;
		$("#UserName").val("");
		$("#UserName").focus();
	}

	function showPasswordLogin() {
		document.getElementById("loginForm").innerHTML = `
			<form method="POST" action="api/login.php">
					<div class="text-left text-danger" id="ErrorHolder"></div>
					<div class="form-floating" id="UPass">
						<input autocomplete="current-password" type="password" name="UserPass" id="UserPass" class="form-control form-control-lg mt-2" placeholder="Password" required>
						<label for="UserPass" class="text-start w-100" id="UserPassLbl">Password</label>
						<small id="forgotpasshelp" style="cursor:pointer" class="" onclick="forgotPass()">Forgot password? Click here.</small>
					</div>
					<button type="submit" class="btn btn-success btn-block mt-3" id="NxtLoginBtn">Next</button>
					<button type="button" class="btn btn-warning btn-block mt-3 ms-1" id="BckLoginBtn" onclick="loginBackDialog()">Cancel</button>
					<input autocomplete="none" type="submit" class="d-none">
			</form>`;
		$("#BckLoginBtn").removeClass("d-none");
		$("#UserPass").val("");
		$("#UserPass").focus();
	}

	// Shows the User all MF Options from the institution
	function showMultifactorOptions({
		availableMFOptions,
		phoneNumber,
		email
	}) {
		pleaseWait("");

		if (availableMFOptions === "") return confirmMF();

		let page = `<div class="form-group pt-4" id="MultifactorSetup">
                    <h4 class="text-start w-100 fw-bold">
                        Multi-Factor Authentication
                    </h4>
                    <p class="text-start w-100 pt-2" id="">
                    Please choose from the options below:
                    </p>
                    <div class="container align-items-center w-100" id="MFOptionsHolder">
                      ${getMFOptions({ availableMFOptions, phoneNumber, email })}
                    <div>
                 </div>
    <button type="button" class="btn btn-warning btn-block mt-3 ms-1" id="BckLoginBtn" onclick="loginBackDialog()">Cancel</button>
    <input autocomplete="none" type="submit" class="d-none">`;
		document.getElementById("loginForm").innerHTML = page;
	}
	// Checks if there is only one possible MF option. If true, sets it automtically without prompting user
	const checkSingleOption = (mfoptions) => {
		if (mfoptions.length == 1) {
			setFirstTimeMF(mfoptions, true);
			return true;
		}
		return false;
	};

	// Ends saved session
	const endSession = async () => {
		try {
			const {
				data: {
					result,
					error
				},
			} = await axios({
				method: 'post',
				url: 'common/rest.php',
				data: {
					deletesession: true,
					resource: "SESSIONS",
					action: "DELETE",
				},
			});
			if (!result) throw new Error(error);
		} catch (error) {
			showError(error);
		}
	};

	const loginBackDialog = async () => {
		await endSession();
		// Redirect the user back to the index page
		window.location = "index.php";
	}

	// Compiles the MF Options list
	function getMFOptions({
		availableMFOptions,
		phoneNumber,
		email
	}) {
		const mfoptions = buildMFOptions(availableMFOptions, phoneNumber, email);
		let optionsDiv = "";
		for (let option of mfoptions) {
			optionsDiv += `<button type="button" class="btn btn-outline-primary w-100 mb-3" onClick="setFirstTimeMF('${option.optionCode}')">
                     <div class="row py-2 d-flex align-items-center">
                        <div class="col offset-2 mf-icon">
                          <span class="${option.icon} fa-2x"></span>
                        </div>
                        <div class="col-8 d-flex justify-content-start">
                          ${option.title}
                        </div>
                      </div>
                    </button>`;
		}
		return optionsDiv;
	}

	// Builds MF Options
	const buildMFOptions = (options, phone, email) => {
		let builtOptions = [];
		// possible options: ETGN

		if (options.includes("E") && email) {
			builtOptions.push({
				icon: "fa-solid fa-envelope",
				title: "Email Authentication",
				optionCode: "E"
			});
		}
		if (options.includes("T") && phone) {
			builtOptions.push({
				icon: "fa-solid fa-comment-sms",
				title: "SMS Text Message",
				optionCode: "T"
			});
		}
		if (options.includes("G")) {
			builtOptions.push({
				icon: "fa-brands fa-google",
				title: "Google Authenticator",
				optionCode: "G"
			});
		}
		if (options.includes("N")) {
			builtOptions.push({
				icon: "fa-solid fa-empty-set",
				title: "None",
				optionCode: "N"
			});
		}

		return builtOptions;
	};

	function switchDeviceOS(element, iOSClicked) {
		const MFQRCode = document.getElementById("MFQRCode");
		const iosTab = document.getElementById("iosTab");
		const androidTab = document.getElementById("androidTab");

		iosTab.ariaCurrent = false;
		iosTab.classList.remove("active");
		androidTab.ariaCurrent = false;
		androidTab.classList.remove("active");

		element.ariaCurrent = true;
		element.classList.add("active");
		if (iOSClicked) {
			// Show iOS QR Code
			MFQRCode.src = "./images/ios.png";
			MFQRCode.alt = "ios qr code";
		} else {
			// Show Android QR Code
			MFQRCode.src = "./images/android.png";
			MFQRCode.alt = "android qr code";
		}
	}
	// Gets Google App Codes
	function getAppCodes() {
		let page = `<div class="form-group pt-4" id="UPass">
                    <h4 class="text-start w-100 fw-bold" id="MAMsg">
                        Google Authenticator App
                    </h4>
                    <div class="card text-center mt-4">
                      <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs pointer">
                          <li class="nav-item ">
                            <a class="nav-link active" id="iosTab" aria-current="true" onclick="switchDeviceOS(this, true)">iOS</a>
                          </li>
                          <li class="nav-item">
                            <a class="nav-link" id="androidTab" onclick="switchDeviceOS(this)">Android</a>
                          </li>
                        </ul>
                      </div>
                      <div class="card-body">
                          <img id="MFQRCode" src="./images/ios.png" alt="ios qr code" width="200" height="200"></img>
                      </div>
                    </div>
                 </div>
    <button class="btn btn-secondary btn-block mt-3" onclick="processGetGoogleCodeJSON('', true)">Next</button>
    <button class="btn btn-warning btn-block mt-3 ms-1" id="BckLoginBtn" onclick="loginBackDialog()">Cancel</button>
    <input autocomplete="none" type="submit" class="d-none">
    `;
		document.getElementById("loginForm").innerHTML = page;
		$("#AndroidQR").hide();
		$("#BckLoginBtn").removeClass("d-none");
	}

	async function processGetGoogleCodeJSON() {
		pleaseWait("");

		// Make call to get info
		const {
			data: {
				googleSecret,
				googleURL
			},
		} = await axios({
			method: "post",
			url: "api/googlesecret.php",
		});

		let page = `<div class="form-group pt-4" id="UPass">
            <h4 class="text-start w-100 fw-bold" id="MAMsg">
                Google Multi-Factor Authentication required
            </h4>
            <p class="text-start w-100 pt-2">
              Please scan the QR Code below
            </p>
            <div class="text-center w-100">
              <canvas id="qrcode"></canvas>
            <div>
          </div>
          <div class="mt-4">
            <button type="button" class="btn btn-secondary btn-block mt-3" onclick="showAuthCodeLogin('GOOGLE', '')">Next</button>
            <button type="button" class="btn btn-warning btn-block mt-3 ms-1" id="BckLoginBtn" onclick="loginBackDialog()">Cancel</button>
            <input autocomplete="none" type="submit" class="d-none">
            <p class="form-text pt-3">
              Don't have the app?
              <small id="needGoogleApp" style="cursor:pointer" class="form-text text-primary" onclick="getAppCodes()">Click here.</small>
            </p>
          </div>`;
		document.getElementById("loginForm").innerHTML = page;
		const qr = new QRious({
			element: document.getElementById("qrcode"),
			value: googleURL,
			size: 200,
		});
	}
	// Sets a user's MF for first time login
	const setFirstTimeMF = async (optionCode, autoSet = false) => {
		let mfOption = "";
		// possible options: ETGN
		switch (optionCode) {
			case "E":
				mfOption = "EMAIL";
				break;
			case "T":
				mfOption = "TEXT";
				break;
			case "G":
				mfOption = "GOOGLE";
				break;
			case "N":
				mfOption = "NONE";
				break;
			default:
				showError("Unknown Multi-Factor Authentication Option");
				return;
		}

		post("api/multifactor.php", {
			mfOption,
			setMF: true,
		});
	};

	//Dialog to get multifactor code
	function showAuthCodeLogin(currentOption, endingIn) {
		let authmsg = "";
		if (currentOption === "GOOGLE") authmsg = "Enter your Google security code";
		else if (currentOption === "TEXT") authmsg = `Enter the security code sent to your phone ending in ${endingIn}`;
		else if (currentOption === "EMAIL") authmsg = `Enter the security code sent to your email ending in ${endingIn}`;
		let page = `<form method="POST" action="api/multifactor.php">
              <h4 class="card-title my-3">Multifactor Login</h4>
                  <div class="form-group" id="UCode">
                    <label for="UserCode" class="text-start w-100 text-secondary" id="UserCodeMsg">
                      ${authmsg}
                    </label>

                    <input autocomplete="one-time-code" type="text" name="UserCode" id="UserCode" class="form-control form-control-lg mt-2" placeholder="Security Code">
                  </div>
                  <input type="hidden" name="MFType" value="${currentOption}">
                  <button type="submit" class="btn btn-secondary btn-block mt-3">
                    Next
                  </button>
                  <button type="button" class="btn btn-warning btn-block mt-3 ms-1" id="BckLoginBtn" onclick="loginBackDialog()">
                    Cancel
                  </button>
                  <input autocomplete="none" type="submit" class="d-none">
                </form>`;
		document.getElementById("loginForm").innerHTML = page;
		const userCode = document.getElementById("UserCode");
		userCode.value = "";
		userCode.focus();
	}

	const confirmMF = () => {
		modalFactory("MFSetupNotification").hide();
		if (chosenOpt === "G") {
			showUserLogin();
			setError("Please login again to complete Google authentication setup");
		} else {
			//forceChange ? expiredPass() : setControlId(CID);
			forceChange ? expiredPass() : loginMFPass();
		}
	};

	const loginMFPass = () => {
		post("api/login.php", {});
	};

	async function forgotPass() {
		clearError();

		pleaseWait("Processing request   ");
		const response = await axios({
			url: "api/forgotpassword.php",
			method: "POST",
			data: {
				username: currUsername,
			}
		});

		processForgotPassJSON(response.data);
	}

	function processForgotPassJSON(data) {
		pleaseWait("");
		if (!data.result) {
			setError(data.error);
		} else {
			drawInfoModal("An email to reset your password has been sent");
		}
		showUserLogin();
	}

	function checkForEnter(theEvent, runFunction) {
		var keyCode = theEvent.keyCode || theEvent.which;
		if (keyCode == 13) {
			theEvent.preventDefault();
			eval(runFunction + "()");
		}
		return true;
	}

	const manageForgotPasswordHandoff = (parms) => {


		pleaseWait("Please wait while link is verified ");
		post(
			"api/passwordhandoff.php", {
				handoff: parms["handoff"],
				controlid: parms["controlid"],
			}
		);
	}

	const showPasswordReset = () => {
		let page = `
			<form method="POST" action="api/changepassword.php">
				<h4 class="card-title my-3">New Password Required</h4>
				<div class="form-group">
					<label for="newPassword" class="text-start w-100" id="NewUserPassLbl">Enter new password:</label>
					<input autocomplete="new-password" type="password" name="newPassword" id="newPassword" class="form-control form-control-lg mt-1" placeholder="New Password">
				</div>
				<div class="form-group">
					<label for="confirmNewPassword" class="text-start w-100" id="ConUserPassLbl">Confirm password:</label>
					<input autocomplete="new-password" type="password" name="confirmNewPassword" id="confirmNewPassword" class="form-control form-control-lg mt-1" placeholder="Confirm Password">
				</div>
				<button type="submit" class="btn btn-success btn-block mt-3" id="NxtLoginBtn">Next</button>
				<button type="button" class="btn btn-warning btn-block mt-3 ms-1" id="BckLoginBtn" onclick="loginBackDialog()">Cancel</button>
				<input autocomplete="none" type="submit" class="d-none">
			</form>`;
		document.getElementById("loginForm").innerHTML = page;
		let newUserPass = document.getElementById("newPassword");
		// newUserPass.value = "";
		newUserPass.focus();
	}
	window.addEventListener("load", () => {
		var uri = window.location.toString();
		var ind = uri.indexOf("?") + 1;
		urisub = uri.substr(ind);
		var parms = transformToAssocArray(urisub);

		if (loginError) {
			setError(decodeURIComponent(loginError).split("#")[0]);
		}
		if (parms["handoff"]) {
			manageForgotPasswordHandoff(parms);
			return;
		} else {
			window.location.hash = "no-back-button";
			window.location.hash = "Again-No-back-button";
			window.onhashchange = function() {
				window.location.hash = "no-back-button";
			}
		}
		if (promptPassword) {
			showPasswordLogin();
			return;
		} else if (resetPassword) {
			showPasswordReset();
			return;
		} else if (isSetup === "google") {
			getAppCodes();
			return;
		}
		if (multifactorType) {
			if (multifactorType === "NEW_MF") {
				showMultifactorOptions({
					availableMFOptions,
					phoneNumber,
					email
				});
				return;
			}
			let endingIn = "<?= isset($_SESSION['endingIn']) ? $_SESSION['endingIn'] : ''; ?>";
			showAuthCodeLogin(multifactorType, endingIn);
			return;
		}
		showUserLogin(successMessage);
	});
</script>
<div class="row mt-2 text-center">
	<div class="col text-center">
		<img src="images/feather.svg" class="FAStandardSVG" onload="SVGInject(this)" />
	</div>
</div>
<div class="row mt-2 mb-2 text-center">
	<div class="SignInTitle">
		<h4>
			Login to eDOCSignature
		</h4>
	</div>
	<?php require_once("login/userlogin/handoffmessage.php"); ?>
	<p id="errormsg" class="text-danger"></p>
</div>

<div id="loginForm"></div>