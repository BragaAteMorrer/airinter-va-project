<?php
if (stristr($_SERVER["SCRIPT_NAME"], "lsvalues_1.inc")) {
    header("location:../index.php");
    die();
}

$diaporamaTitle0 = stripslashes("Screenshots");
$diaporamaTitle1 = stripslashes("");

$diaporamaWidth = stripslashes("900");
$backgroundDiaporama = stripslashes("#F2F2F2");
$colorDiaporama = stripslashes("#000000");
$backgroundTabs = stripslashes("#F2F2F2");
$colorTabs = stripslashes("#000000");
$backgroundCurrentTab = stripslashes("#F2F2F2");
$colorCurrentTab = stripslashes("#000000");
$btnStartStop = stripslashes("false");
$minPanels = stripslashes("2");

$ls_val[1] = stripslashes("true");
$ls_val[2] = stripslashes("0");
$ls_val[3] = stripslashes("1500");
$ls_val[4] = stripslashes("easeInOutExpo");
$ls_val[5] = stripslashes("bounceInRight");
$ls_val[6] = stripslashes("bounceOutRight");
$ls_val[7] = stripslashes("500");
$ls_val[8] = stripslashes("500");
$ls_val[9] = stripslashes("true");
$ls_val[10] = stripslashes("right");
$ls_val[11] = stripslashes("6000");
$ls_val[12] = stripslashes("false");
$ls_val[13] = stripslashes("true");
$ls_val[14] = stripslashes("true");
$ls_val[15] = stripslashes("true");
$ls_val[16] = stripslashes("false");
$ls_val[17] = stripslashes("true");
$ls_val[18] = stripslashes("left");
$ls_val[19] = stripslashes("top");
$ls_val[20] = stripslashes("true");
$ls_val[21] = stripslashes("1");
?>