<?
  
$error_index = 0;
$error_json = '';
function myErrorHandler($errno, $errstr, $errfile, $errline) {
    GLOBAL $error_index, $error_json;
    $message = $errno.': '.$errstr;
    $error_json .= ($error_json?',':'').'"'.str_replace('"', "'", $message).'"';
    $error_index++;
    return true;
}

function errorJSON() {
    GLOBAL $error_json;
    return $error_json?',"errors":['.$error_json.']':'';
}

set_error_handler('myErrorHandler');

$homePath = '/home';
$configFile =  $homePath.'/config_trade.php';

include_once($configFile);         
include_once(INCLUDE_PATH.'/_dbu.php');
include_once(INCLUDE_PATH.'/_edbu2.php');
include_once(INCLUDE_PATH.'/request.php');
include_once(INCLUDE_PATH.'/fdbg.php');
?>