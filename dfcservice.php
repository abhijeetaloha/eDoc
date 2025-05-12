<?php
require_once('common/commonfunctions.php');
function doDFCRequest($json)
{
    // print_r("In Dfc service:");
    // print_r("\n". $json);
    global $cfg;

    // Add the session id to the json object.
    $json = json_decode($json, true);
    $json['session'] = $_SESSION['session'];
    // TODO: Add support for removing session/CID from request.

    $json = json_encode($json);

    $userhost = getUserHost();

    list($json, $_) = processURIForToken($json, null);
    $decJ = json_decode($json, true);
    // print_r("DFC SErvice--------------");
    // print_r($decJ);
    $res = '';
    if (isset($decJ['resource'])) {
        $res = $decJ['resource'];
    };
    $decJ = removeFromArray($decJ, 'resource');

    $METHOD = 'POST';
    if (isset($decJ['method'])) {
        $METHOD = $decJ['method'];
    };

    $decJ = removeFromArray($decJ, 'method'); //apparently some requests need this kept in the body
    if (isset($decJ['password'])) {
        $decJ['passwordhash'] = strtoupper(sha1($decJ['password']));
        $decJ = removeFromArray($decJ, 'password');
    }
    if (isset($decJ['newpassword'])) {
        $SID = $decJ['session'];
        //hex password
        $pass = bin2hex($decJ['newpassword']);
        //add date mm to the beginning and dd to the end
        $pass = date('m') . $pass . date('d');
        //flip the string
        $pass = strrev($pass);
        //flip every 2 characters
        $pass = str_split($pass, 2);
        $pass = implode('', array_reverse($pass));
        $decJ['newpassword'] = $pass;
    }
    if (isset($decJ['files'])) {
        // print_r("decJ \n");
        // print_r($decJ);
        // logToFile($decJ);
		$temppath = $cfg['db']['tempfilepath'];
		$fileToSend = $temppath . $decJ['files'][0];
		logToFile('File to send: ' . $fileToSend);
		if (!file_exists($fileToSend)) {
			$fileToSend = ''; //file should be up on the server so it can already see it
		} else {
			$filenames = array($fileToSend);
			foreach ($filenames as $f) {
				$files[$f] = file_get_contents($fileToSend);
			}
		}
	}
    $decJ['host'] = isset($decJ['host']) ? $decJ['host'] : $userhost;
    // $url = (isset($cfg['db']['eServices']) ? $cfg['db']['eServices'] : $cfg['db']['MicroservicesURL']) . '/' . $res;
    $url = (isset($cfg['db']['dfcService']) ? $cfg['db']['dfcService'] : 'http://localhost:3025') . '/' . $res;
    $json = json_encode($decJ);
    // print_r("\n Modified json:");
    // print_r("\n". $json);
    if ($fileToSend != '') {
        logToFile('Making multi-part request to ' . $url);
		logToFile('JSON: ' . $json);
        // print_r("File to send: " . $fileToSend);
        // print_r($url);
        // print_r($json);
		// $result = doMultiPartRequest($url, $json, $files);
        $result = ($res === "DFC/CREATEDFC") ? doMultiPartRequest($url, $json, $files, $res) : doMultiPartRequest($url, $json, $files);
    } else {
        // print_r("\n ELSE:");
        // print_r($url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $METHOD);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json)
            )
        );
    
        logToFile('Making (Microservice) ' . $METHOD . ' request to ' . $url);
        logToFile('JSON string: ' . $json);
        logToFile('RESOURCE: ' . $res);
    
        $result = curl_exec($ch);
        curl_close($ch);
    }
    logToFile('Result: ' . $result);
    // print_r("\n Result:");
    // print_r("\n". $result);
    if ($result !== false) {
        return mergeResponseTokens($result);
    } else {
        return null;
    }
}
