<?php
$cfg['db']['InternalRESTAddress'] = 'http://127.0.0.1/ISAPI/RIP.dll';
$cfg['db']['RegistrationServer'] = 'http://172.23.1.212/ISAPI/RIP.dll';
$cfg['db']['eDOCSignatureURL'] = 'http://127.0.0.1/eDOCSig/';
$cfg['db']['tempdir'] = "http://127.0.0.1/temp/";
$cfg['db']['WebCommon'] = "http://127.0.0.1/webcommon/";

// URL of the Microservices. Also can be defined using the deprecated $cfg['db']['eServices'] setting.
$cfg['db']['MicroservicesURL'] = "http://127.0.0.1:4000";

//Sigextract service url
$cfg['Default']['SigextractService'] = 'http://127.0.0.1:5000';

//Enable auto share signing document to group
$cfg['Default']['EnableAutoShareToGroup'] = false;

//IDology URLS
$cfg['Default']['IDologyURL'] = 'https://web.idologylive.com/api/idiq.svc';
$cfg['Default']['IDologyDIFURL'] = 'https://web.idologylive.com/api/differentiator-answer.svc';
$cfg['Default']['IDologyANSURL'] = 'https://web.idologylive.com/api/idliveq-answers.svc';

$cfg['db']['tempfilepath'] = "C:\\Web\\iDocVAULT\\temp\\";

// word directory for converting word files to PDF. Needs to be different from 'tempfilepath'. Needs to match the same RDI setting in your wordToPDF service.
$cfg['db']['worddir'] = "C:\\Web\\iDocVAULT\\temp\\";

$cfg['LogFile'] = 'C:\\Web\\iDocVAULT\\logs\\signaturelog.txt';

//Please use relative paths for marketing files so PHP can retrieve the images.
$cfg['db']['marketingfilepath'] = ".\\marketing_images\\";

// Marketing Image Hyperlink 
$cfg['Default']['MarketingImageHyperlink'] = "https://edochelp.com/index.php?page=Current_Release/Updates.md";

$cfg['Default']['EULAHW'] = array("width" => "850", "height" => "1000");

$cfg['db']['MaxUploadSize'] = 1000000;

$cfg['Diagnostics'] = true;

$cfg['ImagesFolders'] = array(
	"Tablet_Images" => 'C:\\Web\\eDSTablet\\Marketing\\',
	"Admin_Images" => 'C:\\Web\\eDOCSignatureAdmin\\marketing_images\\'
);

//Enable description field for templates
$cfg['Default']['EnableTemplateDescription'] = false;

// Enable Field Alignment fields
$cfg['Default']['EnableFieldAlignment'] = false;

//Name to use for the PHP session cookie of the application.
//Set if hosting multiple of the same application on a server to avoid PHP session conflicts. IE: "eDSAdmin1" and "eDSAdmin2".
// $cfg['PHPCookie'] = "eDSAdmin2";

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

$cfg['Reed']['Disabled'] = true;

//Same effect as 'EnableDataEntry'.
$cfg['Default']['ShowDataEntry'] = true;

$cfg['Reed']['DisabledMessage'] = '<div class="container p-3"><div class="text-center">Thank you for using eDOCSignature. Your free trial period has expired as of 10/01/20. If you would like to continue using eDOCSignature, please contact our Sales team at 800-425-7766 option 3 to discuss available features and pricing.</div></div>';

$cfg['Default']['SendWordFilesToConvert'] = true;

$cfg['Default']['ShoweSigners'] = true;

$cfg['Default']['ShowSignerGroups'] = true;

$cfg['Default']['ShowMarketing'] = true;

$cfg['Default']['ShowNewElements'] = true;

$cfg['Default']['ShowPackages'] = false;

$cfg['Default']['ShowSharing'] = true;

$cfg['Default']['ShowIndices'] = true;

$cfg['Default']['AllowLoginAgain'] = false;

$cfg['Default']['ShowReports'] = true;

$cfg['Default']['ShowTemplateSetup'] = true;

$cfg['Default']['ShowTemplateLinks'] = false;

$cfg['Default']['EnableTemplateMatching'] = false;

$cfg['Default']['EnableLastPageSigboxOffset'] = false;

$cfg['Default']['EnableDelegation'] = true;

//Same effect as 'ShowDataEntry'.
$cfg['Default']['EnableDataEntry'] = false;

$cfg['Default']['ShowPending'] = true;

$cfg['Default']['ShowTicketLink'] = false;

$cfg['Default']['ShowSignNow'] = true;

$cfg['Default']['ShowEvents'] = true;


$cfg['Default']['ShoweDOCIt'] = true; //hide or show edoc it link
$cfg['Default']['ShoweDOCItTray'] = true; //enable the new printer and tray
$cfg['Default']['ShoweDOCItPrinter'] = true;

$cfg['Default']['ShowEmailCustomization'] = true;

$cfg['Default']['IDCheckOptions'] = array('None', 'IDology', 'IDPal');

$cfg['Default']['IDCheckDisplay'] = array(
	'None' => 'None',
	'IDology' => 'Questions Only',
	'eDS_DEV' => 'Questions and ID Scan',
	'IDPal' => 'ID Scann App'
);

$cfg['Default']['EnableReferenceDocs'] = true;

$cfg['Default']['EnableRequestedDocs'] = true;

$cfg['Default']['AllowAddRefDoc'] = true;

$cfg['Default']['EnableBulkSend'] = false;

$cfg['Default']['ShowResendAuthCode'] = true;

$cfg['Default']['ShowOnDemandReports'] = true;

$cfg['Default']['ShowSendDoc'] = true;

$cfg['Default']['ShowSendNotary'] = true;

$cfg['Default']['ShowRedirectURL'] = true;

$cfg['Default']['HideAuthCodes'] = false;

$cfg['Reed']['RequireAuthCode'] = true;

$cfg['Default']['ForcedNameChecked'] = true; //Automatically checks the force name checkbox in pages

$cfg['Default']['DisableForcedName'] = true; //Overrides ForcedNameChecked, disables the force name checkbox completly

$cfg['Default']['UseTabletSigning'] = true;

$cfg['Reed']['SignNowReturnURL'] = "http://127.0.0.1/eDOCSignatureAdmin/index.php?source=1234";

$cfg['Reed']['ReportTypeFilter'] = 'eSIG';

$cfg['Default']['SupportFillableFields'] = false;

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

$cfg['Default']['EnablePersonalMessaging'] = false;

//define helpsite links
$cfg['Default']['HelpLinks'] = array(
	//base url for helpsite. Used to be defined as $cfg['Default']['HelpPageURL'].
	"base" => "https://edochelp.com/",
	//the route to access pages from. Is appended to "base".
	"route" => "index.php?page=eDOCSignature",
	//The pages to access from base and route. These are used all over the site.
	"pages" => array(
		"default" => "Welcome_to_eDOCSignature.md",
		"landing" => "Welcome_to_eDOCSignature.md",
		"bulksend" => "User_Tutorials/Set_Up_Bulk_Send.md",
		"edocit" => "eDOC-It_Virtual_Printer/Using_the_Virtual_Printer.md",
		"manageDoc" => array(
			"search" => "User_Tutorials/Manage_Packages.md",
			"docSearchResults" => "User_Tutorials/Manage_Packages.md",
			"editDoc" => "User_Tutorials/Manage_Packages.md",
			"docHistory" => "User_Tutorials/Manage_Packages.md",
			"docResults" => "User_Tutorials/Manage_Packages.md",
		),
		"manageTemplate" => array(
			"search" => "User_Tutorials/Set_Up_Templates/Manage_Templates.md",
			"docSearchResults" => "User_Tutorials/Set_Up_Templates/Search_for_Templates.md",
			"editDoc" => "User_Tutorials/Set_Up_Templates/Manage_Templates.md",
		),
		"reports" => array(
			"history" => "User_Tutorials/Reports/Report_History.md",
			"details" => "User_Tutorials/Reports/Run_a_Report.md",
		),
		"sendDoc" => array(
			"roleMapping" => "User_Tutorials/Send_Document/Select_or_Assign_Signers.md",
			"upload" => "User_Tutorials/Send_Document/Upload_Documents.md",
			"indices" => "User_Tutorials/Send_Document/Index_Data.md",
			"setupDoc" => "User_Tutorials/Send_Document/Set_up_Document.md",
			"sharing" => "User_Tutorials/Send_Document/Share_Documents.md",
			"signers" => "User_Tutorials/Send_Document/Select_or_Assign_Signers.md",
			"reqSigners" => "User_Tutorials/Send_Document/Select_Reference_and_or_Requested_Document_Viewers.md",
			"selectWorkflow" => "User_Tutorials/Send_Package.md",
			"refSigners" => "User_Tutorials/Send_Document/Select_Reference_and_or_Requested_Document_Viewers.md",
			"review" => "User_Tutorials/Send_Document/Review_Documents.md",
		),
		"settings" => array(
			"signers" => "User_Tutorials/Settings/Signers_Tab.md",
			"profile" => "User_Tutorials/Settings/Profile_Tab.md",
			"users" => "User_Tutorials/Settings/Users_Tab.md",
			"groups" => "User_Tutorials/Settings/Groups_Tab.md",
			"disclosure" => "User_Tutorials/Settings/Disclosures_Tab.md",
			"EULA" => "User_Tutorials/Settings/Disclosures_Tab.md",
			"indices" => "User_Tutorials/Settings/Indices_Tab.md",
			"marketing" => "User_Tutorials/Settings/Marketing_tab.md",
			"tags" => "User_Tutorials/Settings/Tags_Tab.md",
			"roles" => "User_Tutorials/Settings/Roles_Tab.md",
			"emails" => "User_Tutorials/Settings/Emails_Tab.md",
			"editEmail" => "User_Tutorials/Settings/Emails_Tab.md",
			"events" => "User_Tutorials/Settings/Events_Tab.md",
		),
		"setupTemplate" => array(
			"upload" => "User_Tutorials/Set_Up_Templates/Create_Templates/Create_Templates.md",
			"selectType" => "User_Tutorials/Set_Up_Templates/Create_Templates/Create_Templates.md",
			"indices" => "User_Tutorials/Set_Up_Templates/Create_Templates/Set_Up_Template_Index_Data.md",
			"setupDoc" => "User_Tutorials/Set_Up_Templates/Set_Up_Templates.md",
			"sharing" => "User_Tutorials/Set_Up_Templates/Manage_Templates.md",
			"signers" => "User_Tutorials/Set_Up_Templates/Select_Signer_Roles.md",
			"review" => "User_Tutorials/Set_Up_Templates/Review_and_Create_Templates.md",
		),
		"pending" => "Pending_Docs/Pending_Docs.md",
		"workflows" => array(
			"addWorkflow" => "User_Tutorials/Set_Up_Package_Types.md",
			"editWorkflow" => "User_Tutorials/Set_Up_Package_Types.md",
			"selectDoc" => "User_Tutorials/Send_Package.md",
			"search" => "User_Tutorials/Set_Up_Package_Types.md",
			"results" => "User_Tutorials/Set_Up_Package_Types.md",
		),
	),
	//Where to send the user to submit a help ticket.
	"ticket" => "https://edocinn.zohodesk.com/portal/en/signin",
);

$cfg['eDOCItVersion'] = '8.1.1.12';
$cfg['eDOCItMinVersion'] = '8.1.1.12';

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

// Enable/Disable the Email Me feature on the 'Review Document Information' page
$cfg['Default']['EnableEmailMe'] = true;

// Hides the field label input in the sig element edit modals
$cfg['Default']['HideFieldLabel'] = true;

// Settings related to the beta and production sites.
$cfg['Default']['AltSiteSettings'] = array(
	/** The button in the header menu.
	 * 	* Both "Link" and "Text" need to be specified to enable the button and enable redirect.
	 *  * The site from the link needs to be on the same backend as this one and also feature this config setting linking to this site.
	 *  * Always posts the user to the landing page.
	 *  * Will remember via cookie which site you clicked on last, so visiting the
	 *  landing page of the option you left from will redirect you to the option you last chose.
	 */
	"Button" => array(
		//The link the button uses.
		"Link" => "http://172.23.1.212/sigadminqc", //"http://172.23.1.212/SigAdmin8",
		//Text to display on the button
		"Text" => "Production",
	),
	"Modal" => array(
		//Link to use for the "Beta" button in the header menu. If set, enables button.
		//Settings for displaying a modal of beta changes on visiting the landing page.
		//If either Version or Content is not set, no modal will not show up at all.
		//Modal displays based on cookie named via Version.
		"Version" => "8.23.0.0",
		//Supports HTML
		"Content" => "<h2>Example content to explain what stuff was changed</h2>
		<ul>
			<li><strong>Feature:</strong> Added new login functionality.</li>
			<li><strong>Enhancement:</strong> Improved user interface for better accessibility.</li>
			<li><strong>Fix:</strong> Resolved issue with form validation on the registration page.</li>
			<li><strong>Performance:</strong> Optimized database queries for faster page loading.</li>
			<li><strong>Security:</strong> Implemented CSRF protection for all forms.</li>
		</ul>",
	),
);

// Determines if the Audit Reports page is visible or not when viewing a document
$cfg['Default']['EnableAuditReports'] = true;
