<?php
/*******************************************************************************
 *   Admin Menu to DDM
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
 * v6.00.19 (August 26, 2024) : Correct TOhelp
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'ddmtomnu.inc')) {
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

$act     = import('act');
$id      = import('id');
$titre1  = import('titre1');
$titre2  = import('titre2');
$typmenu = import('typmenu');
$posimg  = import('posimg');

$erreur     = '';
$err_titre1 = false;
$err_titre2 = false;
$err_titre1 = empty($titre1) && ($act == 1) ? true : false;
$erreur    .= $err_titre1 ? '<li>'.$admin[1585].$qtitre.$admin[1586].($lang[1] != '' ? $admin[1177] : '').'</li>': '';
if ($lang[1] != '') {
    $err_titre2 = empty($titre2) && ($act == 1) ? true : false;
    $erreur    .= $err_titre2 ? '<li>'.$admin[1585].$qtitre.$admin[1586].$admin[1178].'</li>': '';
}
$act = empty($erreur) ? $act : 0;

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
<div class="text-left p-1 mx-auto mb-2" style="width:420px;">
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
</div>
<div class="clearfix"></div>';
}

$headinc .= BeginJavascript().'
function VerifyForm() {
    var sto    = "";
    var erreur = false;
    var lng2   = "'.$lang[1].'";
    var tit1 = document.inputmenu.titre1.value;
    if (tit1.length < 1) {
        sto += "  - '.addslashes($admin[1585].$admin[1214].$admin[1586].($lang[1] != '' ? $admin[1177] : '')).'\n";
		document.inputmenu.titre1.className = "errorInputText";
        erreur = true;
    }
    if (lng2 > "") {
        var tit2 = document.inputmenu.titre2.value;
        if (tit2.length < 3) {
            sto += "  - '.addslashes($admin[1585].$admin[1214].$admin[1586].$admin[1178]).'\n";
			document.inputmenu.titre2.className = "errorInputText";
            erreur = true;
        }
    }
    if (erreur == true) {
        sto = "'.addslashes($admin[1589]).'\n\n" + sto;
        alert(sto);
        return false;
    }
}'.EndJavascript();

ReadDoc($id);
$mnu = $lng == $lang[0] ? $fieldb1 : $fieldb2;
$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.sprintf($admin[1542], $mnu);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable(sprintf($admin[1542], $mnu), '100%');
echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=1036&mnuid=937&tconfig=0#z2').'</p>';

if (empty($act)) {
    $types_menu = array($admin[397], $admin[398], $admin[504], $admin[621], $admin[1354]);
    echo '
<div class="text-center mt-2">'.$admin[1213].'</div>';
    if (!empty($erreur))
        echo displayErrorMsg($admin[1589], $erreur);
    echo '
<form name="inputmenu" id="inputmenu" action="admin.php?lng='.$lng.'&amp;pg=ddmtomnu" method="post" onsubmit="return VerifyForm(); return false;">
  <input type="hidden" name="act" value="1" />
  <input type="hidden" name="id" value="'.$id.'" />
  <fieldset class="w-100 mx-auto mt-3" style="max-width:560px;">
    <div class="table-responsive-md">
    <table class="w-100 m-auto">
      <tr><td class="text-center pt-2">'.$admin[1214].'</td></tr>
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag1.'</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_titre1 ? 'errorInputText' : 'texte').'" type="text" name="titre1" size="45" value="'.$fieldb1.'" />
          </div>
        </td>
      </tr>';
    if ($lang[1] != '') {
        echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag2.'</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_titre2 ? 'errorInputText' : 'texte').'" type="text" name="titre2" size="45" value="'.$fieldb2.'" />
          </div>
        </td>
      </tr>';
    } else {
        echo '
  <input type="hidden" name="titre2" value="" />';
    }
    echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[1221].'&nbsp;:&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="typmenu">';
			for ($jj = 0; $jj < count($types_menu); $jj++) {
				echo '
              <option value="'.$jj.'"'.Selected($typmenu == $jj).'>'.$types_menu[$jj].'</option>';
			}
				echo '
            </select>
          </div>
        </td>
      </tr>
      <tr>
        <td style="text-align:center">
          <div class="d-inline-block">'.$admin[1475].'&nbsp;:&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="posimg">
              <option value="L"'.Selected($fieldd1 == 'L').'>'.$admin[494].'</option>
              <option value="R"'.Selected($fieldd1 == 'R').'>'.$admin[495].'</option>
            </select>
          </div>
        </td>
      </tr>
      <tr><td class="text-center pt-2">'.$admin[1230].'</td></tr>
      <tr><td class="text-center">'.ShowValidMenus().'</td></tr>
    </table>
    </div>
    <div class="row quest p-0 mt-2 w-100 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[1349], 'self.location.href=\'admin.php?lng='.$lng.'&pg=ddmenu&tri=ch\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div></div>
    </div>
  </fieldset>
</form>';
}

if ($act == 1 && empty($erreur)) {
    ReadDoc($id);
    $data     = array_fill(0, 17, '');
    $data[0]  = TYP_MENUBOX;
    $data[2]  = 'a';
    $data[5]  = empty($userprefs[1]) ? $author : $userprefs[1];
    $data[6]  = empty($userprefs[2]) ? $email : $userprefs[2];
    $data[7]  = isset($_POST['valid']) ? implode('', array_keys($_POST['valid'])) : 'TLAEURB';
    $data[8]  = 'off|off';
    $data[9]  = $titre1;
    $data[10] = $titre2;
    $data[13] = $posimg;
    $data[15] = $typmenu;
    $data[17] = $fieldmod;
    $idmnu    = ActionOnFields('add', $data);
    
    $dbw      = SelectDbFieldsByField(ReadDBFields(DBOPTDDMENU), 5, $id);
    $optmenu  = array();
    $curr_cat = '';
    foreach ($dbw as $item) {
        ReadDoc($item[4]);
        if ($status != 'a') continue;
        $optmenu[] = array(trim($item[0]), $item[4]);
    }
    unset($dbw);
    sort($optmenu);
    if (!empty($optmenu)) {
        $i = $cnivs = $cpnivs = 0;
        $cat1 = $cat2 = $pniv = '';
        foreach ($optmenu as $item) {
            ReadDoc($item[1]);
            $nivs  = explode('-', $item[0]);
            $cnivs = count($nivs);
            $niv   = '';
            for ($i = 0; $i < count($nivs) - 1; $i++) $niv .= $nivs[$i].'-';
            if ($fieldd1 != '?' && count($nivs) == 1) {
                $cat1 = '';
                $cat2 = '';
            }
            if ($fieldd1 == '?') {
                if (count($nivs) == 1) {
                    $cat1 = '';
                    $cat2 = '';
                }
                $acats = explode('|', $cat1);
                // $cat1  = $acats[0];
                if ($niv != $pniv && $cpnivs > $cnivs) array_splice($acats, $cnivs-1, $cpnivs-$cnivs);
                $cat1 = '';
                for ($i = 0; $i < $cnivs-2; $i++) $cat1 .= (empty($acats[$i]) ? '' : '|').$acats[$i];
                for ($i = $cnivs-2; $i < $cnivs; $i++) $cat1 .= (empty($acats[$i]) ? '' : '|').$acats[$i];
                $cat1 .= '|'.$fieldb1;
                $cat1  = substr($cat1, 0, 1) == '|' ? substr($cat1, 1) : $cat1;
                $acats = explode('|', $cat2);
                // $cat2  = $acats[0];
                if ($niv != $pniv && $cpnivs > $cnivs) array_splice($acats, $cnivs-1, $cpnivs-$cnivs);
                $cat2 = '';
                for ($i = 0; $i < $cnivs-2; $i++) $cat2 .= (empty($acats[$i]) ? '' : '|').$acats[$i];
                for ($i = $cnivs-2; $i < $cnivs; $i++) $cat2 .= (empty($acats[$i]) ? '' : '|').$acats[$i];
                $cat2 .= '|'.$fieldb2;
                $cat2  = substr($cat2, 0, 1) == '|' ? substr($cat2, 1) : $cat2;
            }
            if ($fieldd1 != '?') {
                $data     = array_fill(0, 17, '');
                $data[0]  = TYP_OPTMENU;
                $data[2]  = 'a';
                $data[5]  = empty($userprefs[1]) ? $author : $userprefs[1];
                $data[6]  = empty($userprefs[2]) ? $email : $userprefs[2];
                $data[7]  = $cat1;
                $data[8]  = $cat2;
                $data[9]  = $fieldb1;
                $data[10] = $fieldb2;
                $data[11] = $fieldc1;
                $data[12] = 'S';
                $data[13] = $fieldd1;
                $data[14] = $fieldd2;                
                $data[15] = $idmnu;
                $data[16] = $fieldmail;
                $data[17] = $fieldmod;
                $idopt    = ActionOnFields('add', $data);
            }
            $pniv   = $niv;
            $cpnivs = count($nivs);
        }
        $titre = $lng == $lng[0] ? $titre1 : $titre2;
        echo '
<p class="text-center">'.sprintf($admin[1546], $titre).'</p>';
    }
}

btable();
include CHEMIN.'admin/bpage.inc';
?>