<?php
$cfg['db']['InternalRESTAddress'] =  'http://localhost/ISAPI/RIP.dll';//'https://sandbox.edoclogic.com/ISAPISB/RIP3.dll';
$cfg['db']['RegistrationServer'] =  'http://172.23.1.212/ISAPI/RIP.dll';//'https://sandbox.edoclogic.com/ISAPISB/RIP3.dll';
$cfg['db']['eDOCSignatureURL'] = 'http://localhost/eDOCSig/';
$cfg['db']['eServices'] = 'http://127.0.0.1:4000';
$cfg['db']['tempdir'] = "http://localhost/temp/";
$cfg['db']['SigextractService'] = 'http://127.0.0.1:3000';
//The webcommon library used by eDOC Innovations products
$cfg['db']['WebCommon'] = "http://localhost/webcommon/";

//Directory to store converted temporary general files
$cfg['db']['tempfilepath'] = "C:\\Web\\temp\\";
//Directory to store converted temporary .doc and .docx files
// $cfg['db']['worddir'] = "C:\\Web\\temp\\";

//Defines the file used to log errors. File will be saved with the current date appended before .txt
$cfg['LogFile'] = 'C:\\Web\\logs\\signaturelog.txt';

//The path used for the login page marketing images.
//If the folder specified doesn't contain any images, defaults to images/Fotolia_64319332_L.jpg
//Please use relative paths for marketing files so PHP can retrieve the images.
$cfg['db']['marketingfilepath'] = ".\\marketing_images\\";

//Restricts users from uploading files larger than a defined file size.
$cfg['db']['MaxUploadSize'] = 1000000;

//Enables/Disables logging to the file as defined by $cfg['LogFile']
$cfg['Diagnostics'] = true;

//Directory to load and store marketing images set on the Settings -> Admin -> Marketing page.
$cfg['ImagesFolders'] = array(
	"Tablet_Images" => 'C:\\Web\\eDSTablet\\Marketing\\',
	"Admin_Images" => 'C:\\Web\\eDOCSignatureAdmin\\marketing_images\\'
);

//Disable eDSAdmin access to a specified CID. Upon logging in the user is restricted access and is only shown a screen with a message as defined in $cfg['Reed']['DisabledMessage'].
// $cfg['Reed']['Disabled'] = true;

//Enable sigextract service to detect blank fields with ai
$cfg['Default']['EnableSigextract'] = false;
$cfg['Default']['EnableDescription'] = false;

//Enable DataEntry across eDSAdmin.
//Requires $cfg['Default']['ShowDataEntry'] to be set to true in order to allow editing in signing element editing pages.
$cfg['Default']['EnableDataEntry'] = true;

//Enable data entry, visible as a checkbox on the Index Data screen in package creation.
$cfg['Default']['ShowDataEntry'] = true;

//The message to display to users for $cfg['Reed']['Disabled']. Accepts HTML and plain text.
$cfg['Reed']['DisabledMessage'] = '<div class="container p-3"><div class="text-center">Thank you for using eDOCSignature. Your free trial period has expired as of 10/01/20. If you would like to continue using eDOCSignature, please contact our Sales team at 800-425-7766 option 3 to discuss available features and pricing.</div></div>';

//Use a separate method for converting to PDF. Used in upload.php when the form parameter StripFile is set.
$cfg['Default']['SendWordFilesToConvert'] = true;

//Enables/Disables the use of eSigners
$cfg['Default']['ShoweSigners'] = true;

//Enables/Disables the use of Signer Groups
$cfg['Default']['ShowSignerGroups'] = true;

//Show marketing options in the Settings
$cfg['Default']['ShowMarketing'] = true;

//Enable access to certain elements on signing element editing screens. Includes DateSigned and Memobox elements.
$cfg['Default']['ShowNewElements'] = true;

//Enable SendPackages and Set Up Package Type buttons on the landing page.
$cfg['Default']['ShowPackages'] = false;

//Enable sharing options on package review screens
$cfg['Default']['ShowSharing'] = true;

//Enable indicies across eDSAdmin
$cfg['Default']['ShowIndices'] = true;

//If a session times out while saving a package or template, give them the chance to log in again.
$cfg['Default']['AllowLoginAgain'] = false;

//Show reports button on the landing page
$cfg['Default']['ShowReports'] = true;

//Enable template functionality across eDSAdmin. Shows template buttons on the landing page
$cfg['Default']['ShowTemplateSetup'] = true;

//Show template links across template management pages.
$cfg['Default']['ShowTemplateLinks'] = false;

//Enable template matching. When enabled and uploading a document in the package creation process
//and it closely matches one used in an existing template, a prompt will come up to use that template.
$cfg['Default']['EnableTemplateMatching'] = false;

//if there is a difference in uploaded doc and template doc length, will move sigbox elements relative to the difference in length
$cfg['Default']['EnableLastPageSigboxOffset'] = false;

//Show pending docs options across eDSAdmin
$cfg['Default']['ShowPending'] = true;

//Show ticket links for packages on senddoc and managedoc pages
$cfg['Default']['ShowTicketLink'] = false;

//Show SignNow across eDSAdmin
$cfg['Default']['ShowSignNow'] = true;

//If not set or set to false, uses ShowTriggers.
$cfg['Default']['ShowEvents'] = true;

//hide or show eDOCIt link
$cfg['Default']['ShoweDOCIt'] = true;
//enable eDOCIt tray
$cfg['Default']['ShoweDOCItTray'] = true;
//enable eDOCIt printer
$cfg['Default']['ShoweDOCItPrinter'] = true;

//Show customizing emails in the settings page
$cfg['Default']['ShowEmailCustomization'] = true;

//Defines the default IDCheck options.
$cfg['Default']['IDCheckOptions'] = array('None', 'IDology', 'IDPal');
//Allows you to customize the name an IDCheckOption has across eDSAdmin.
//References the ['IDCheckOptions'] setting.
$cfg['Default']['IDCheckDisplay'] = array(
	'None' => 'None',
	'IDology' => 'Questions Only',
	'eDS_DEV' => 'Questions and ID Scan',
	'IDPal' => 'ID Scann App'
);

//Enable sending as reference document across eDSAdmin
$cfg['Default']['EnableReferenceDocs'] = true;

//Enable sending as requested document across eDSAdmin
$cfg['Default']['EnableRequestedDocs'] = true;

//Enable bulk send across eDSAdmin
$cfg['Default']['EnableBulkSend'] = false;

//Won't work if config option ['HideAuthCodes'] is set to true.
$cfg['Default']['ShowResendAuthCode'] = true;

//Enable "Run Report" on the Reports page
$cfg['Default']['ShowOnDemandReports'] = true;

//Enables SendDoc and SendPackages on landing page
$cfg['Default']['ShowSendDoc'] = true;

//Enables Redirect URL Fields on package and template review pages.
$cfg['Default']['ShowRedirectURL'] = true;

//Hide AuthCode fields on Signer managing sections across eDSAdmin.
$cfg['Default']['HideAuthCodes'] = false;

//Require filling out the authentication code field for signers 
$cfg['Reed']['RequireAuthCode'] = true;

//Automatically checks the force name checkbox in pages
$cfg['Default']['ForcedNameChecked'] = true;

//Overrides ForcedNameChecked, disables the force name checkbox completly
$cfg['Default']['DisableForcedName'] = true;

//If not set or set to false, prompts for tablet signing when attempting to sign a document from eDSAdmin.
$cfg['Default']['UseTabletSigning'] = true;

//The URL to redirect to when using Sign Now from eDSAdmin
$cfg['Reed']['SignNowReturnURL'] = "http://127.0.0.1/eDOCSignatureAdmin/index.php?source=1234";

//Set the report type that the Run Report tab obtains on the Reports page. Accepts strings.
$cfg['Reed']['ReportTypeFilter'] = 'eSIG';

//Support using fillable fields on the signing element editing sections of SendDoc and SetupTemplate
$cfg['Default']['SupportFillableFields'] = false;

//Defines the supported notification types
$cfg['Default']['NotificationTypes'] = array("Public", "Text", "Private", "No Email");

//Allows you to customize the internal name a notification type is across eDSAdmin.
//References the ['NotificationTypes'] setting.
$cfg['Default']['NotificationTypes2'] = array(
	"Public" => "Email",
	"Text" => "Text",
	"Private" => "Home Banking",
	"No Email" => "None"
);

//Allows you to customize the name a notification type will show up as across eDSAdmin.
//References the value of the ['NotificationTypes2'] settings.
$cfg['Default']['NotificationTypesMap'] = array(
	"Email" => "Gmail",
	"Text" => "SMS",
	"None" => "Do not send"
);

//Define what target tables are available across the index data in the package creation and editing process.
$cfg['Default']['TargetTables'] = array("InBox", "Forms", "eDOCSig_Reports");

//Enable requested payment fields on signer assigning sections of the package creation process..
$cfg['Default']['EnableHelcimPayments'] = false;

//Enables a personal message field on signer assigning sections of the package creation process.
//A message will be added to the beginning or end of the ticket notification text depending on your selection.
$cfg['Default']['EnablePersonalMessaging'] = true;

//Defines what url help buttons refer to
$cfg['Default']['HelpPageURL'] = 'https://sandbox.edoclogic.com/eDOCHelp/eDOCSignature/';

//The version of eDOCIt to use. Defaults to 0 if not set.
$cfg['eDOCItVersion'] = '8.1.1.12';
//The minimum version of eDOCIt to use. Defaults to 0 if not set.
$cfg['eDOCItMinVersion'] = '8.1.1.12';

//Specifies the default report filter options on the Report History page. If nothing is set, defaults to the following options:
/*
Packages out for signing by user
Package days out for signing
Unsent packages by user
Total packages by user
Packages by status
Packages expiring within 30 days
 */
$cfg['Default']['ReportTypes'] = array(
	"Packages out for signing by user" => "Packages out for signing by user",
	"Package days out for signing" => "Package days out for signing",
	"Unsent packages by user" => "Unsent packages by user",
	"Total packages by user" => "Total packages by user",
	"Packages by status" => "Packages by status",
	"Packages expiring within 30 days" => "Packages expiring within 30 days"
);