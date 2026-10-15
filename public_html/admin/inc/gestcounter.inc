<?php
/*******************************************************************************
 *   Admin Counters
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
 * v6.00.19 (August 26, 2024) : Tohelp cancel
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'gestcounter.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[31] == '') {
        $nomzone = $admin[17];
        include 'access.inc';
        exit();
    }
}

$mod   = import('mod');
$del   = import('del');
$cnum  = import('cnum');
$id    = import('id');
$regit = import('regit');

$docdtb = DBCOUNT.TestFileId($id).DBEXT;
$docinc = DBBASE.TestFileId($id).INCEXT;

if ($cnum == 1) {
    $countername = 'countdwnl';
    $namecount = $admin[18];
}

if ($cnum == 2) {
    $countername = 'countart';
    $namecount = $admin[5];
}

if ($cnum == 3) {
    $countername = 'countforum';
    $namecount = $admin[253];
}

if ($regit == 1) {
	$newcount = import('newcount');
    WriteCounter($docdtb, $newcount);
}

if ($mod == 1) {
	$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > <a href="admin.php?lng='.$lng.'&amp;pg='.$countername.'">'.$admin[17].' '.$namecount.'</a> > '.$admin[660];
	include CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($admin[660].' '.$namecount, '100%');
    $dbcounter = ReadCounter($docdtb);
    ReadDoc($docinc);
    if ($cnum == 1) {
        if ($lng == $lang[0]) {
            $txt = $admin[670].' '.$fieldb1;
        }
        else {
            $txt = $admin[670].' '.$fieldb2;
        }
    }
    if ($cnum == 2) {
        if ($lng == $lang[0]) {
            $txt = $admin[671].' '.$fieldb1;
        }
        else {
            $txt = $admin[671].' '.$fieldb2;
        }
    }
    if ($cnum == 3) {
        $txt = $admin[672].' '.$fieldb1;
    }
    echo '
<hr />
<div style="width:300px; margin:0 auto 4px;">
<p class="forum" style="text-align:left; padding:5px 20px;">doc'.TestFileId($id).INCEXT.'</p>
<form name="gestcounter" action="admin.php?lng='.$lng.'&amp;pg=gestcounter" method="post">
  <input type="hidden" name="id" value="'.$id.'" />
  <input type="hidden" name="mod" value="1" />
  <input type="hidden" name="cnum" value="'.$cnum.'" />
  <input type="hidden" name="regit" value="1" />
  <fieldset>
  <div class="table-responsive-md">
  <table class="table w-100">
    <tr>
      <td style="white-space:nowrap;">
          <input class="form-control w-80 mx-auto" type="text" name="newcount" value="'.$dbcounter.'" style="max-width:80px;" />
      </td>
    </tr>
  </table>
  </div>
  </fieldset>
  <div class="quest" style="padding:2px 20px;">
    <div style="float:right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div>
    <div style="clear:both"></div>
  </div>
</form>
</div>
'.BeginJavascript().'document.gestcounter.newcount.focus()'.Endjavascript();
}

if ($del == 1) {
	$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > <a href="admin.php?lng='.$lng.'&amp;pg=count">'.str_replace('Admin - ', '', $admin[652]).'</a> > 
	  <a href="admin.php?lng='.$lng.'&amp;pg='.$countername.'">'.$admin[17].' '.$namecount.'</a> > '.$admin[666];
	include CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($admin[666].' '.$namecount, '100%');
    SetChmod($docdtb);
    @unlink($docdtb);
    echo '
<p style="text-align:center;">'.$admin[667].'<b> doc'.TestFileId($id).DBEXT.' </b>'.$admin[668].'</p>';
}

echo'
<br />
<hr />
<p style="text-align:center;">
  <a href="admin.php?lng='.$lng.'&amp;pg='.$countername.'">'.$admin[669].' '.$namecount.'</a>
</p>';

btable();
include CHEMIN.'admin/bpage.inc';
?>