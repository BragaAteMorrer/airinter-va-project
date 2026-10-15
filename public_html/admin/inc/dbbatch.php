<?php
/*******************************************************************************
 *   BATCH DataBase Integrity Check
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2020 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.00 (December 15, 2020) : initial release
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '../../');
include_once CHEMIN.'admin/includes.inc';
include_once 'funcdbchk.inc';

$delta      = intval(import('delta'));
$range1     = intval(import('range1'));
$range2     = intval(import('range2'));
$integr     = import('integr');
$checkid    = intval(import('checkid'));
$checkerr   = intval(import('checkerr'));
$checkquiet = intval(import('checkquiet'));
$typ        = import('typ');

if (empty($checkid)) {
    if (empty($range1)) {
        $range1 = 1;
    }
    else {
        $range1 = Min($range1,ReadCounter(NEXTID));
    }
    if (empty($range2)) {
        $range2 = ReadCounter(NEXTID)+10;
    }
    if ($range2 < $range1) {
        $range2 = $range1;
    }
    $checkid = $range1;
    if (empty($delta)) {
        $delta = DBCHK_DEFAULT;
    }
    else {
        $delta = min(DBCHK_MAX, max($delta, 1));
    }
}
echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'" />
<title>'.strip_tags($admin[278]).'</title>
'.JavascriptFile(CHEMIN.'inc/hpage.js');
echo '
</head>
<body style="background-color: #EFEFEF; color: #000; overflow: hidden;">
<fieldset>
<p style="text-align:center;">'.$admin[295].'</p>
<p style="text-align:center;">[ '.$range1.' / <b>'.Min($checkid,$range2).'</b> / '.$range2.' ]</p>';

if ($checkid <= $range2) {
    $checkerr = $checkerr + CheckDBmP($checkid, $checkid + $delta, $checkquiet);
    $checkid += $delta;
    $nextstep = "setTimeout('PopupWindow(\"dbbatch.php?lng=".$lng."&typ=".$typ."&range1=".$range1."&range2=".$range2."&checkquiet=".$checkquiet."&checkid=".$checkid."&checkerr=".$checkerr."&delta=".$delta."\",\"dbbatch\",480,360,\"no\",\"no\")', 1)";
}
else {
    echo '
<hr />
<p style="text-align:center;"><b>'.$admin[298].'</b></p>';
    if ($checkquiet != 1) {
        if ($checkerr == 0) {
            $dbresult = $admin[297];
        }
        else {
            $dbresult = $checkerr." ".$admin[576];
        }
        echo '
<p style="text-align:center;">'.$dbresult.'</p>
<p style="text-align:right;"><a href="#" onclick="window.close();"><b>'.$admin[458].'&nbsp;</b></a></p>';
    }
    $nextstep = "";
	if (TYP_FORUM == $typ) {
        $dbfr = ReadDBFields(DBFORUM);
        foreach ($dbfr as $doc) {
            if (!FileDBExist(DBBASE.TestFileId($doc[2]).INCEXT)) continue;
            ReadDoc(DBBASE.TestFileId($doc[2]).INCEXT);
            $data[0]  = $type;
            $data[1]  = $fileid;
            $data[2]  = $status;
            $data[3]  = $creadate;
            $data[4]  = $moddate;
            $data[5]  = $author;
            $data[6]  = $email;
            $data[7]  = $fielda1;
            $data[8]  = $fielda2;
            $data[9]  = $fieldb1;
            $data[10] = $fieldb2;
            $data[11] = $fieldc1;
            $data[12] = $fieldc2;
            $data[13] = $fieldd1;
            $data[14] = $fieldd2;
            $data[15] = $fieldweb;
            $data[16] = $fieldmail;
            $data[17] = $fieldmod;
            ActionOnFields('mod', $data);
            break;
        }
	}
}
echo '
</fieldset>';
if ($nextstep != "") {
    echo BeginJavascript().$nextstep.EndJavascript();
}
echo '
</body>
</html>';
?>