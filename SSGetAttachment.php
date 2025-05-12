<?php
require_once('common/commonfunctions.php');
require_once('config.php');
$downloadfolder = (isset($cfg['db']['tempfilepath']) ? $cfg['db']['tempfilepath'] : "temp/");
//read content of request as JSON
$json_data = file_get_contents('php://input');
logToFile($json_data);
//decode JSON into PHP object
$data = json_decode($json_data);
logToFile(print_r($data, true));
//data contains attachmentid, url, port, user, and pass
//if port is not 443 then modify the url to include the port number
//url may contain a forward slash at the end, so check for that
$url = $data->url;
//remove any trailing slash
$url = rtrim($url, "/");
$port = $data->port;
$attachmentid = $data->attachmentid;
$session = $data->session;
$url = $url . ":" . $port . "/sdata/slx/system/-/attachments('" . $attachmentid . "')/file";
$user = $data->user;
$pass = $data->pass;
$usernamePassword = $user . ':' . $pass;
$headers = array('Authorization: Basic ' . base64_encode($usernamePassword));
$filename = $session . $attachmentid . '.pdf';
$path = $downloadfolder . $filename;
//FOLDER PATH
$fp = fopen($path, 'w+');
logToFile($url);
logToFile(print_r($headers, true));
//SETTING UP CURL REQUEST
$ch = curl_init();
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FILE, $fp);
$result = curl_exec($ch);
if ($result === false) {
    logToFile(curl_error($ch));
}
//CONNECTION CLOSE
curl_close($ch);
fclose($fp);
//if request failed then delete the file and return an error
if ($result == false) {
    unlink($path);
    echo '{"error":"Error downloading file"}';
} else {
    //respond with JSON containing the file name
    echo '{"filename":"' . $filename . '"}';
}
