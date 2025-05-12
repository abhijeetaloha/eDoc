<?php
require_once('common/commonfunctions.php');
function doeServiceRequest($json)
{

    // Add the session id to the json object.
    $json = json_decode($json, true);
    $json['session'] = $_SESSION['session'];

    $json = json_encode($json);

    $userhost = getUserHost();
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
    $decJ['host'] = isset($decJ['host']) ? $decJ['host'] : $userhost;
    $url = (isset($cfg['db']['eServices']) ? $cfg['db']['eServices'] : $cfg['db']['MicroservicesURL']) . '/' . $res;
    $json = json_encode($decJ);
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
    logToFile('Result: ' . $result);
    if ($result !== false) {
        return mergeResponseTokens($result);
    } else {
        return null;
    }
}
