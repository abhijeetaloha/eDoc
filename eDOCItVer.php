<?php
require_once('common/commonfunctions.php');
require_once('config.php');
$str_json = file_get_contents('php://input');
logToFile($str_json);
$jsonArgs = json_decode($str_json, true);
$shPnd = ',"showpending":false';
if ($jsonArgs['controlid']) {
	$CID = $jsonArgs['controlid'];
	$showPending = getSetting('ShowPending');
	if ($showPending) {
		$shPnd = ',"showpending":true';
	}
}
if (!isset($cfg['eDOCItVersion'])) {
	$cfg['eDOCItVersion'] = 0;
}
if (!isset($cfg['eDOCItMinVersion'])) {
	$cfg['eDOCItMinVersion'] = 0;
}
$rt = '{"result":true,"version":"' . $cfg['eDOCItVersion'] . '","minversion":"' . $cfg['eDOCItMinVersion'] . '"' . $shPnd . '}';
logToFile($rt);
echo $rt;
