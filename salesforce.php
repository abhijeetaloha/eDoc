<?php

require_once('common/commonfunctions.php');
require_once('config.php');

// Set origin nav for redirecting methods
getOriginNav(__DIR__);
checkLogin();

$XVARS = (isset($_POST) && count($_POST) > 0) ? $_POST : $_GET;
$tempDir = isset($cfg['db']['tempdir']) ? $cfg['db']['tempdir'] : "temp/";

// Obtain salesforce handoff code with a request
$userhost = getUserHost();
$data = array("session" => $SID, "controlid" => $CID, "action" => "GETEDOCLINK", "host" => $userhost);
$data_string = json_encode($data);
$json = doRIPRequest2($data_string, 'POST', 'USERS/');
if (!isset($json)) {
    logtofile("Salesforce session request response json is null");
    returnError('Server Down');
}
if (!$json["result"]) {
    logtofile("Salesforce session request response result is false");
    $GENERIC_SALESFORCE_ERROR = "An error occurred obtaining the Salesforce handoff code";
    returnError($json['systemerror'] && $json['error'] ? "$GENERIC_SALESFORCE_ERROR: " . $json['error'] : $GENERIC_SALESFORCE_ERROR);
}
$handoff = $json["handoff"];

//end the session but keep the user on the page so they can't navigate to any other pages.
$data = array("session" => $SID, "action" => "DELETE", "host" => $userhost);
$data_string = json_encode($data);
$json = doRIPRequest2($data_string, 'POST', 'SESSIONS/');
// TODO: Log out if an unanticipated error occurs. We're expecting the session to have been closed.

?>
<style>
    <?php include 'common/bootstrap-eDOCSig.css'; ?>
</style>

<head>
    <?php require_once("common/headers.php"); ?>
    <?php require_once("common/session.php"); ?>
</head>

<body class="d-flex flex-column">
    <header>
        <div class="row w-100 g-0 LoginHeader" id="BlueHeaderRow">
            <div class="col-12">
                <div class="row w-100 justify-content-center">
                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-6 text-white p-2">
                        <img src="images/feather.svg" class="FASecondarySVG cursor ms-3" onload="SVGInject(this)" />
                        <strong class="align-middle">eDOCSignature Admin</strong>
                    </div> <!--window.open('https://edoclogic.com', '_blank');-->
                    <div class="col-xl-4 col-lg-3 d-sm-none d-xs-none d-md-none d-lg-block text-center text-white align-self-center" id="MenuTitleCell" style="font-size:24px;font-weight:bold;"></div>
                    <div class="col-xl-3 col-lg-2 col-md-4 col-sm-6 align-self-center" id="ControlIDSelect"></div>
                    <div class="col-xl-1 col-lg-3 col-md-4 col-sm-12">
                        <div class="row justify-content-end flex-nowrap">
                            <div class="col text-end" id="HelpCell" onclick="window.open(getHelpLink(),'help');"><button type="button" class="helpbutton cursor mt-2 mx-auto">?</button></div>
                            <div id="LogoutBtn" class="cursor col" onclick="logout('');">
                                <img src="images/logout.svg" class="cursor FASecondarySVG mt-2" title="Logout" onload="SVGInject(this)" /></img>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <main class="flex-shrink-0">
        <div>&nbsp;</div>
        <div class="grid">
            <img src="images/salfesforcelock.jpg" alt="Salesforce lock">
        </div>
        <div class="grid">
            <table>
                <tr>
                    <div>Copy the Salesforce code to your clipboard:</div>
                </tr>
                <tr>
                    <input disabled="disabled" style="width: 300px; height:fit-content" class="form-control form-control-lg mt-1" type="text" id="salesforcecode">
                </tr>
                <tr>
                    <button class="btn btn-primary btn-block m-lg-2" onclick="copyToClipboard()">Copy to Clipboard</button>
                </tr>
            </table>
        </div>
    </main>
    <script>
        setSalesforceCode(`<?php echo $handoff; ?>`);

        function showPage(page) {}

        function setSalesforceCode(code) {
            document.getElementById("salesforcecode").value = code;
        }

        function copyToClipboard() {
            let copyText = document.getElementById("salesforcecode");

            copyText.select();
            copyText.setSelectionRange(0, 99999); // For mobile devices

            navigator.clipboard.writeText(copyText.value);
            let msg = copyText.value ? "Copied code to clipboard!" : "Issue copying code to clipboard.";

            alert(msg);
        }

        function enableButtons() {}

        function disableButtons() {}

        $(window).on("load", function() {
            loadGoDSeal();
            setupSession();
            showPage();
        });
        window.location.hash = "no-back-button";
        window.location.hash = "Again-No-back-button"; //again because google chrome don't insert first hash into history
        window.onhashchange = function() {
            window.location.hash = "no-back-button";
        }
    </script>
</body>

<footer class="footer mt-auto pt-3">
    <?php require_once("common/footer.php"); ?>
</footer>
<?php require_once("common/snackbar.php"); ?>
<?php require_once("common/messages.php"); ?>

</html>