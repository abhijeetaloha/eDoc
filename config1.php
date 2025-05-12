<?php
$cfg['db']['eDOCSignatureURL'] = 'http://127.0.0.1/eDOCSig/';
$cfg['db']['InternalRESTAddress'] = 'https://sandbox.edoclogic.com/ISAPISB/RIP3.dll';
$cfg['db']['localtempdir'] = "http://127.0.0.1/temp/";
$cfg['db']['tempdir'] = "http://127.0.0.1/temp/";
$cfg['db']['WebCommon'] = "http://127.0.0.1/webcommon/";
$cfg['db']['eServices'] = 'http://localhost:3000';
$cfg['db']['RemoteeSigners'] = 'https://sandbox.edoclogic.com/eDOCSigAdminsb/eservices.php';

$cfg['Default']['SigextractService'] = 'http://127.0.0.1:5000';

$cfg['db']['tempfilepath'] = "C:\\Web\\temp\\";
$cfg['db']['worddir'] = "C:\\Web\\temp\\";

$cfg['LogFile'] = 'C:\\Web\\logs\\signatureadminlog.txt';

//$cfg['db']['marketingfilepath'] = "C:\\Web\\eDOCSignatureAdmin\\marketing_images\\";
$cfg['db']['marketingfilepath'] = ".\\marketing_images\\";

$cfg['Default']['EULAHW'] = array("width" => "850", "height" => "1000");

$cfg['db']['MaxUploadSize'] = 30000000;

$cfg['Diagnostics'] = true;

//Enable sigextract service to detect blank fields with ai
// $cfg['Default']['EnableSigextract'] = false;

$cfg['Default']['ShowDataEntry'] = true;

$cfg['Default']['SkipConversion'] = false;

$cfg['Default']['SendWordFilesToConvert'] = true;

$cfg['Default']['AllowLoginAgain'] = true;

$cfg['ImagesFolders'] = array(
	"Tablet_Images" => 'C:\\Web\\eDSTablet\\Marketing\\',
	"Admin_Images" => 'C:\\Web\\eDOCSignatureAdmin\\marketing_images\\'
);

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

$cfg['Reed']['eDOCItPrinter'] = 'eDOCItInstalls/edocitprinter.msi';
$cfg['Reed']['eDOCItTrayApp'] = 'eDOCItInstalls/eDOCItTray_Install.msi';

$cfg['Reed']['Disabled'] = false;
$cfg['Reed']['DisabledMessage'] = '<div class="container p-3"><div class="text-center">Thank you for using eDOCSignature. Your free trial period has expired as of 10/01/20. If you would like to continue using eDOCSignature, please contact our Sales team at 800-425-7766 option 3 to discuss available features and pricing.</div></div>';

$cfg['Default']['ShowNewElements'] = true;

$cfg['Default']['ShowPackages'] = true;

$cfg['Default']['ShoweSigners'] = true;

$cfg['Default']['ShowSignerGroups'] = true;

$cfg['Default']['ShowMarketing'] = true;

$cfg['Default']['ShowSharing'] = true;

$cfg['Default']['ShowIndices'] = true;

$cfg['Default']['ShowReports'] = true;

$cfg['Default']['ShowTemplateSetup'] = true;

$cfg['Default']['ShowTemplateLinks'] = false;

$cfg['Default']['EnableTemplateMatching'] = false;

$cfg['Default']['ShowPending'] = true;

$cfg['Default']['ShowTicketLink'] = false;

$cfg['Default']['ShowSignNow'] = true;

$cfg['Default']['ShowEvents'] = true;

$cfg['Default']['ShoweDOCIt'] = true; //hide or show edoc it link
$cfg['Default']['ShoweDOCItTray'] = true; //enable the new printer and tray
$cfg['Default']['ShoweDOCItPrinter'] = true;

$cfg['Default']['ShowEmailCustomization'] = true;

$cfg['Default']['IDCheckDisplay'] = array(
	'None' => 'None',
	'IDology' => 'Questions Only',
	'eDS_DEV' => 'Questions or ID Scan',
	'eDS_IDAB' => 'Questions plus ID Scan',
	'IDPal' => 'ID Scan App'
);

$cfg['Default']['EnableReferenceDocs'] = true;

$cfg['Default']['EnableRequestedDocs'] = true;

$cfg['Default']['AllowAddRefDoc'] = true;

$cfg['Default']['EnableBulkSend'] = true;

$cfg['Default']['ShowResendAuthCode'] = true;

$cfg['Default']['ShowOnDemandReports'] = true;

$cfg['Default']['ShowSendDoc'] = true;

$cfg['Default']['ShowRedirectURL'] = true;

$cfg['Default']['HideAuthCodes'] = false;

$cfg['Reed']['RequireAuthCode'] = false;

$cfg['Default']['UseTabletSigning'] = true;

$cfg['Default']['BlankAuthCodes'] = false;

$cfg['Default']['SupportFillableFields'] = true;

$cfg['Reed']['SignNowReturnURL'] = "http://localhost:8080/eDOCSignatureAdmin/index.php?source=1234";

$cfg['Reed']['ReportTypeFilter'] = 'eSIG';

$cfg['Default']['NotificationTypes'] = array("Public", "Text", "Private", "No Email");

$cfg['Default']['NotificationTypes2'] = array(
	"Public" => "Email",
	"Text" => "Text",
	"Private" => "Home Banking",
	"No Email" => "None"
);

$cfg['Default']['NotificationTypesMap'] = array(
	"Email" => "Email",
	"Text" => "Text",
	"None" => "Do not send"
);

$cfg['Default']['TargetTables'] = array("InBox", "Forms", "eDOCSig_Reports");

$cfg['Default']['EnableHelcimPayments'] = false;

$cfg['Default']['EnableDelegation'] = true;

$cfg['Default']['EnablePersonalMessaging'] = false;

$cfg['Default']['HelpPageURL'] = 'https://sandbox.edoclogic.com/eDOCHelp/eDOCSignature/';

$cfg['eDOCItVersion'] = '8.10.0.1';
$cfg['eDOCItMinVersion'] = '8.10.0.1';

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
	
	//customize help message
	//$cfg['Reed']['IntroHelpMsg'].='<table><tr><td></td>Welcome to XYZ Credit Union. Would you like help?</tr><tr></tr></table>';
