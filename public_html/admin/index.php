<?php 
header('Pragma: no-cache');
define('CHEMIN', '../');
include CHEMIN.'inc/includes.inc';
$location = ($site['URLR'] == 'on')? $lng.'-'.$urlrw[0].'-19': 'admin.php?lng='.$lng;
header("location:".$location); 
?>
