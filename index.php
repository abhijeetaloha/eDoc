<?php
header("Access-Control-Allow-Origin: *");
require_once('common/commonfunctions.php');
require_once('config.php');
require_once('login/login.php');
?>
<!DOCTYPE html>
<html lang="eng">

<head>
    <title>eDOCSignature</title>
    <?php require_once("common/headers.php"); ?>
    <script>
        function enableButtons() {
            get("NxtLoginBtn").disabled = false;
        }

        function disableButtons() {
            get("NxtLoginBtn").disabled = true;
        }
        $(window).on("load", function() {
            loadGoDSeal();
        });
    </script>
</head>

<body>
    <div class="row g-0 w-100 h-100">
        <div class="col my-auto">
            <div class="cursor w-100">
                <a <?= getSetting('MarketingImageHyperlink') ? 'href="' . getSetting('MarketingImageHyperlink') . '"' : ''; ?> target="_blank">
                    <img id="loginImg" class="w-100" alt="eDOCSignature" src='<?= getMarketingImage(); ?>'>
                </a>
            </div>
        </div>
        <div class="col card" style="border:none; min-width:450px; max-width:450px">
            <div class="row g-0 card-body">
                <div class="col-12 my-auto" style="min-width: 400px;" id="LoginMain">
                    <?php require_once("userlogin.php"); ?>
                </div>
            </div>
            <div class="row g-0 card-footer" style="border:none;background:none;">
                <div class="col-12 mt-auto">
                    <div class="row g-0 LoginFooter align-items-center p-2" style="min-width: 400px;">
                        <div class="col-1">
                            <i class="fas fa-lock-keyhole text-dark fa-lg ps-2"></i>
                        </div>
                        <div class="col-4 text-start">
                            <div class="FooterText">Secured by eDOC Innovations, Inc.&nbsp;&nbsp;</div>
                        </div>
                        <div class="col-3 text-center">
                            <div class="FooterText cursor justify-content-center" id="TOSBtn" onclick="window.open('eDOCSignature EULA.htm','_tos');">Terms and Conditions</div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="justify-content-end" width="135">
                                <div id="siteseal"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<?php require_once('common/messages.php'); ?>

</html>