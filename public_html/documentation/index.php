<?php 
header('Pragma: no-cache');
define('CHEMIN', '../');
include CHEMIN.'inc/includes.inc';
$location = ($site['URLR'] == 'on')? '../'.$lng.'-'.$urlrw[0]: '../index.php?lng='.$lng;
header("location:".$location); 
?>
