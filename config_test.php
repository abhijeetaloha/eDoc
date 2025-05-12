<?php
$cfg['db']['InternalRESTAddress'] = 'https://sandbox.edoclogic.com/ISAPISB/RIP3.dll';

$cfg['db']['tempfilepath'] = "/var/www/html/temp/";

//$cfg['db']['MicroservicesURL'] = 'http://127.0.0.1:4000/';

$cfg['db']['localtempdir'] = "http://127.0.0.1/temp/";

$cfg['db']['tempdir'] = "https://sandbox.edoclogic.com/Temp/";

$cfg['LogFile'] = '/var/log/web/signatureadminlog.txt';

$cfg['db']['marketingfilepath'] = "/var/www/html/marketing_images/";

$cfg['Default']['EULAHW'] = array("width" => "850", "height" => "1000");

$cfg['db']['MaxUploadSize'] = 30000000;

$cfg['db']['WebCommon'] = "https://sandbox.edoclogic.com/webcommon/";

$cfg['Diagnostics'] = true;

$cfg['Default']['SkipConversion'] = true;

//Sigextract service url
$cfg['db']['SigextractService'] = 'http://127.0.0.1:5000';
//Enable sigextract service to detect blank fields with ai
$cfg['Default']['EnableSigextract'] = false;
$cfg['Default']['EnableTemplateDescription'] = false;

// Enable Field Alignment fields
$cfg['Default']['EnableFieldAlignment'] = false;

//Option to use Imagick to render PDF images
//Ghostscript and imagick need to be installed on web server for it to work
//$cfg['Reed']['UseImagick']=true;

//Help Images
$cfg['Default']['HelpImages'] = array(
	"Intro" => "fritz_intro.png",
	"ReturnIntro" => "fritz_intro.png",
	"ReadyToBegin" => "fritz_only.png",
	"CreateSignature" => "fritz_only.png",
	"CreateInitials" => "fritz_only.png",
	"ReadyToSign" => "fritz_only.png",
	"ExitWarning" => "exclamation_pt.png",
	"EmailCopy" => "fritz_only.png",
	"Finish" => "fritz_only.png"
);

//EULA
$cfg['Default']['eDOCSigEULA'] = 'eDOCSignature EULA.htm';

//Consumer Disclosure
$cfg['Default']['eDOCSigDisclosure'] = 'eDOCSignature Customer Disclosure.htm';

$cfg['Default']['eDOCSigClosingMsg'] = '<table cellpadding="3" cellspading="0" class="XChangeCloseTable" width="800">';
$cfg['Default']['eDOCSigClosingMsg'] .= '<tr><th align="center">Thank you!  Your document was successfully signed.</th></tr>';
$cfg['Default']['eDOCSigClosingMsg'] .= '<tr><td>You can obtain a copy of the document by clicking the Back To Documents button below.  If you would like a paper copy of these document(s), please contact a member service representative to assist you.</td></tr>';
$cfg['Default']['eDOCSigClosingMsg'] .= '</table>';

//For adding text to adopt signature canvas
$cfg['MaxTextFontSize'] = 60;
$cfg['Fonts'] = array(
	"AlluraRegular" => "fonts/Allura-Regular.ttf",
	"AnkeCallig" => "fonts/ankecallig-fg.ttf",
	"BlackJack" => "fonts/black_jack.ttf",
	"DancingScript" => "fonts/Dancing Script.ttf",
	"GradeCursive" => "fonts/gradecursive.ttf",
	"HoneyScriptLight" => "fonts/HoneyScript-Light.ttf"
);

$cfg['Reed']['Disabled'] = false;
$cfg['Reed']['DisabledMessage'] = '<div class="container p-3"><div class="text-center">Thank you for using eDOCSignature. Your free trial period has expired as of 10/01/20. If you would like to continue using eDOCSignature, please contact our Sales team at 800-425-7766 option 3 to discuss available features and pricing.</div></div>';

$cfg['Default']['ShowNewElements'] = true;

//$cfg['Default']['ShoweSigners'] = true;

$cfg['Default']['ShowPackages'] = true;

$cfg['Default']['ShowSharing'] = true;

$cfg['Default']['ShowIndices'] = true;

$cfg['Default']['ShowReports'] = true;

$cfg['Default']['ShowTemplateSetup'] = true;

$cfg['Default']['ShowTemplateLinks'] = true;

$cfg['Default']['EnableTemplateMatching'] = true;

$cfg['Default']['ShowPending'] = true;

$cfg['Default']['ShowTicketLink'] = true;

$cfg['Default']['ShowSignNow'] = true;

$cfg['Default']['ShowTriggers'] = true;

$cfg['Default']['ShoweDOCIt'] = true;

$cfg['Default']['ShowEmailCustomization'] = true;

$cfg['Default']['IDCheckOptions'] = array('None', 'IDology', 'IDPal');

$cfg['Default']['IDCheckDisplay'] = array(
	'None' => 'None',
	'IDology' => 'Questions Only',
	'eDS_IDTEST' => 'Questions and ID Scan',
	'IDPal' => 'ID Scann App'
);

$cfg['Default']['EnableReferenceDocs'] = true;

$cfg['Default']['EnableRequestedDocs'] = true;

$cfg['Default']['AllowAddRefDoc'] = true;

$cfg['Default']['EnableBulkSend'] = true;

$cfg['Default']['ShowResendAuthCode'] = true;

$cfg['Default']['ShowOnDemandReports'] = true;

$cfg['Default']['ShowSendDoc'] = true;

$cfg['Default']['ShowSendNotary'] = true;

$cfg['Default']['ShowRedirectURL'] = true;

$cfg['Default']['HideAuthCodes'] = false;

$cfg['Reed']['RequireAuthCode'] = false;

$cfg['Default']['UseTabletSigning'] = true;

$cfg['Default']['BlankAuthCodes'] = false;

$cfg['Default']['SupportFillableFields'] = true;

$cfg['Reed']['SignNowReturnURL'] = "http://127.0.0.1:8000/eDOCSignatureAdmin/index.php?source=1234";

$cfg['Reed']['ReportTypeFilter'] = 'eSIG';

$cfg['Default']['NotificationTypes'] = array("Public", "Text", "Private", "No Email");

$cfg['Default']['NotificationTypes2'] = array(
	"Public" => "Email",
	"Text" => "Text",
	"Private" => "Home Banking",
	"No Email" => "None"
);

$cfg['Default']['NotificationTypesMap'] = array(
	"Email" => "Gmail",
	"Text" => "SMS",
	"None" => "Do not send"
);

$cfg['Default']['TargetTables'] = array("InBox", "Forms", "eDOCSig_Reports");

$cfg['Default']['EnableHelcimPayments'] = false;

$cfg['Default']['EnablePersonalMessaging'] = true;

$cfg['Default']['HelpPageURL'] = 'https://edochelp.com/';

$cfg['eDOCItVersion'] = '8.10.0.1';
$cfg['eDOCItMinVersion'] = '8.10.0.1';

$cfg['Default']['ShowEvents'] = true;

$cfg['Default']['ReportTypes'] = array(
	"Packages out for signing by user" => "Packages out for signing by user",
	"Package days out for signing" => "Package days out for signing",
	"Unsent packages by user" => "Unsent packages by user",
	"Total packages by user" => "Total packages by user",
	"Packages by status" => "Packages by status",
	"Packages expiring within 30 days" => "Packages expiring within 30 days"
);

//sets the minimum size for a valid signature
$cfg['Default']['MinSigSize'] = 20;
$cfg['Default']['ExitMessage'] = '<tr><td>Thank you for using eDOCSignature</tr></td>';
//$cfg['Reed']['ExitURL']='http://edoclogic.com/';

//Determines whether or not to allow user to decide whether or not to send document
$cfg['Default']['ShowSendCopy'] = 'False';

//turn off help pages for all
$cfg['Default']['TurnOffHelp'] = 'false';
//$cfg['Reed']['TurnOffHelp'].='true';

//if name is forced, should we hide the font selection pages?
$cfg['Default']['HideIfForced'] = 'True';

$cfg['Default']['EmailValidation'] = false;

//customize help message
//$cfg['Reed']['IntroHelpMsg'].='<table><tr><td></td>Welcome to XYZ Credit Union. Would you like help?</tr><tr></tr></table>';
