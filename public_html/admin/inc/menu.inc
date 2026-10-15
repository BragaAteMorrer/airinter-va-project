<?php
/*******************************************************************************
 *   Admin Menu boxes
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
 * v6.00.19 (December 15, 2024) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'menu.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[17] == '') {
        $nomzone = $admin[1216];
        include 'access.inc';
        exit();
    }
}

$form      = import('form');

$qtype     = TYP_MENUBOX;
$bname     = 1;
$bemail    = 1;
$bcat      = 1;
$btitre    = 1;
$bcontenu  = 1;
$bnouveau  = 1;
$qformonly = 0;
$qtitle    = $admin[1210];
$qintro    = '<p style="text-align:center;">'.ToHelp($lng, '&pg=1030&mnuid=937&tconfig=0#z2').'</p>'.$admin[1211].(empty($form) ? $admin[223] : '');
$qseeall   = $admin[1212];
$qwrite    = $admin[1213];
$qcat      = $admin[1221];
$qtitre    = $admin[1214];
$qcontenu  = $admin[1215];
$qnouveau  = $admin[1230];

function ShowValidMenus($accept = '') {
    global $admin;
    $valid = array(
        BOX_TOP => false, BOX_BOTTOM => false,
        BOX_LEFT => false, BOX_RIGHT => false,
        BOX_ABOVE => false, BOX_UNDER => false,
        BOX_EDITO => false);
    for ($i = 0; $i < strlen($accept); $i++) {
        $letter = substr($accept, $i, 1);
        if (isset($valid[$letter])) $valid[$letter] = true;
    }
    return '
<div class="text-left p-1 m-auto" style="width:420px;">
  <div class="pt-1" style="width:402px;border:1px solid;">
    <label for="valid['.BOX_TOP.']" style="margin-left:152px;">'.$admin[1085].'</label>
    <input type="checkbox" id="valid['.BOX_TOP.']" name="valid['.BOX_TOP.']"'.Checked($valid[BOX_TOP]).' />
  </div>
  <div>
    <div class="float-left pt-3" style="width:100px;height:6em;border:1px solid;">
      <label for="valid['.BOX_LEFT.']" class="pt-1" style="margin-left:10px;">'.$admin[1086].'</label>
      <input type="checkbox" id="valid['.BOX_LEFT.']" name="valid['.BOX_LEFT.']"'.Checked($valid[BOX_LEFT]).' />
    </div>
    <div class="float-left" style="width:200px;">
      <div style="width:200px;border:1px solid;height:2em;">
        <label for="valid['.BOX_ABOVE.']" class="pt-1" style="margin-left:50px;margin-top:2px;">'.$admin[1087].'</label>
        <input type="checkbox" id="valid['.BOX_ABOVE.']" name="valid['.BOX_ABOVE.']"'.Checked($valid[BOX_ABOVE]).' />
      </div>
      <div style="width:200px;border:1px solid;height:2em;">
        <label for="valid['.BOX_EDITO.']" class="pt-1" style="margin-left:50px;margin-top:2px;">'.$admin[1088].'</label>
        <input type="checkbox" id="valid['.BOX_EDITO.']" name="valid['.BOX_EDITO.']"'.Checked($valid[BOX_EDITO]).' />
      </div>
      <div style="width:200px;border:1px solid;height:2em;">
        <label for="valid['.BOX_UNDER.']" class="pt-1" style="margin-left:50px;">'.$admin[1089].'</label>
        <input type="checkbox" id="valid['.BOX_UNDER.']" name="valid['.BOX_UNDER.']"'.Checked($valid[BOX_UNDER]).' />
      </div>
    </div>
    <div class="float-left pt-3" style="width:100px;height:6em;border:1px solid;">
      <label for="valid['.BOX_RIGHT.']" class="pt-1" style="margin-left:10px;">'.$admin[1090].'</label>
      <input type="checkbox" id="valid['.BOX_RIGHT.']" name="valid['.BOX_RIGHT.']"'.Checked($valid[BOX_RIGHT]).' />
    </div>
  </div>
  <div style="width:402px;border:1px solid;">
    <label for="valid['.BOX_BOTTOM.']" class="pt-1" style="margin-left:152px;">'.$admin[1091].'</label>
    <input type="checkbox" id="valid['.BOX_BOTTOM.']" name="valid['.BOX_BOTTOM.']"'.Checked($valid[BOX_BOTTOM]).' />
  </div>
</div><div class="clearfix"></div>
';
}

include 'inc/admin.inc';
?>