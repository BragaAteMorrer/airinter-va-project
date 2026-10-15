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
 *  v6.00.19 (August 26, 2024) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'mnutoddm.inc')) {
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
$typmenu = import('typmenu');
$titre1  = import('titre1');
$titre2  = import('titre2');

$erreur     = '';
$err_titre1 = false;
$err_titre2 = false;
$err_titre1 = empty($titre1) && ($act == 2) ? true : false;
$erreur    .= $err_titre1 ? '<li>'.$admin[1585].$qtitre.$admin[1586].($lang[1] != '' ? $admin[1177] : '').'</li>': '';
if ($lang[1] != '') {
    $err_titre2 = empty($titre2) && ($act == 2) ? true : false;
    $erreur    .= $err_titre2 ? '<li>'.$admin[1585].$qtitre.$admin[1586].$admin[1178].'</li>': '';
}
$act = empty($erreur) ? $act : 1;

function ShowValidMenus($accept = '') {
    global $admin;
    $valid = array(
        BOX_TOP => false, BOX_BOTTOM => false,
        BOX_LEFT => false, BOX_RIGHT => false,
        BOX_ABOVE => false, BOX_UNDER => false,
        BOX_EDITO => false );
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
</div><div class="clearfix"></div>
';
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
$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.sprintf($admin[1541], $mnu);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable(sprintf($admin[1541], $mnu), '100%');
echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=1148&mnuid=1030&tconfig=0#z2').'</p>';
$types_menu = array('DDV'=>$admin[1351], 'DDH'=>$admin[1352]);

if (empty($act)) {
    echo '
<form name="seltypmenu" action="admin.php?lng='.$lng.'&amp;pg=mnutoddm" method="post">
  <input type="hidden" name="act" value="1" />
  <input type="hidden" name="id" value="'.$id.'" />
  <fieldset class="w-100 mx-auto mt-5" style="max-width:280px;"><legend>'.$admin[1222].'</legend>
  <div class="table-responsive-md">
  <table style="margin:auto;">
    <tr>
      <td style="text-align:center;">
        <p>
          <select class="form-control" name="typmenu" onchange="submit();">
            <option value="" select="selected">'.$admin[1058].'</option>';
	foreach ($types_menu as $key=>$value) {
		echo '
            <option value="'.$key.'"'.Selected($typmenu == $key).'>'.$value.'</option>';
	}
	echo '
          </select>
        </p>
      </td>
    </tr>
  </table>
  </div>
  <div class="row quest p-0 mt-2 w-100 m-auto">
    <div class="col-md-12">'.SubmitButton($admin[1220], 'self.location.href=\'admin.php?lng='.$lng.'&pg=menu&tri=ch\'', $imggauche, '', 'button').'</div>
  </div>
  </fieldset>
</form>';
}

if ($act == 1) {
    echo '
<div class="text-center">'.$admin[1213].' => '.$types_menu[$typmenu].'</div>';
    if (!empty($erreur))
        echo displayErrorMsg($admin[1589], $erreur);
    echo '
<form name="inputmenu" id="inputmenu" action="admin.php?lng='.$lng.'&amp;pg=mnutoddm" method="post" onsubmit="return VerifyForm(); return false;">
  <input type="hidden" name="act" value="2" />
  <input type="hidden" name="id" value="'.$id.'" />
  <input type="hidden" name="typmenu" value="'.$typmenu.'" />
  <fieldset class="w-100 mx-auto" style="max-width:520px;">
    <div class="table-responsive-md">
    <table class="w-100 m-auto">
      <tr><td class="text-center"><br />'.$admin[1214].'</td></tr>
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
      <tr><td class="text-center"><br />'.$admin[1230].'</td></tr>
      <tr><td class="text-center">'.ShowValidMenus().'</td></tr>
    </table>
    </div>
    <div class="row quest p-0 mt-2 w-100 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[1216], 'self.location.href=\'admin.php?lng='.$lng.'&pg=menu&tri=ch\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div></div>
    </div>
  </fieldset>
</form>';
}

if ($act == 2 && empty($erreur)) {
    ReadDoc($id);
    $data     = array_fill(0, 17, '');
    $data[0]  = TYP_DDMENUBOX;
    $data[2]  = 'a';
    $data[5]  = empty($userprefs[1]) ? $author : $userprefs[1];
    $data[6]  = empty($userprefs[2]) ? $email : $userprefs[2];
    $data[7]  = isset($_POST['valid']) ? implode('', array_keys($_POST['valid'])) : 'TLAEURB';
    $data[8]  = $typmenu;
    $data[9]  = $titre1;
    $data[10] = $titre2;
    $data[17] = $fieldmod;
    $idmnu    = ActionOnFields('add', $data);
    
    $dbw      = SelectDbFieldsByField(ReadDBFields(DBOPTMENU), 5, $id);
    $optmenu  = array();
    $curr_cat = '';
    foreach ($dbw as $item) {
        ReadDoc($item[4]);
        if ($status != 'a') continue;
        $optmenu[] = array(trim($item[0]), $item[4]);
    }
    unset($dbw);
    sort($optmenu);
    $curcats = array();
    $liste   = array();
    $n       = 0;
    $n2      = -1;
    $p       = 1;
    foreach ($optmenu as $item) {
        if (empty($item[0])) {
            $i = 0;
        } else {
            $cats = explode('|', $item[0]);
            $xcat = '';
            for ($i = 0; $i < count($cats); $i++) {
                $xcat .= (empty($xcat) ? '' : '|').$cats[$i];
                if (@$curcats[$i] != @$cats[$i]) {
                    if ($i == 0) $n++;
                    $ast = array();
                    for ($j = 1; $j < $i+1; $j++) $ast[] = '-01';
                    if ($i > 0) $ast[$i-1] = '-'.sprintf("%'.02d", $n2+1);
                    if ($i > 1) for ($k = 0; $k < count($past); $k++) $ast[$k] = $past[$k];
                    $liste[] = array('cat' => TRUE, 'niv' => $i+1, 'attn' => sprintf("%'.02d", $n).implode($ast), 'opt' => $item[1]);
                    $curcats[$i] = $cats[$i];
                    array_splice($curcats, $i+1);
                    $p    = $i;
                    $past = $ast;
                    $n2   = 0;
                }
            }
        }
        if (empty($item[0]) || $i == 0) { $n++; $n2 = 0; }
        if ($i > $p) $n2++;
        $ast = array();
        for ($j = 1; $j < $i+1; $j++) $ast[] = '-01';
        if ($n2 > 1) $ast[$i-1] = '-'.sprintf("%'.02d", $n2);
        if ($i > 1) for ($k = 0; $k < count($past); $k++) $ast[$k] = $past[$k];
        $liste[] = array('cat' => FALSE, 'niv' => $i+1, 'attn' => sprintf("%'.02d", $n).implode($ast), 'opt' => $item[1]);
    }
    unset($cats, $curcats);
    
    if (!empty($liste)) {
        if (in_array($typmenu, array('DDH', 'DDV'))) {
            foreach ($liste as $item) {
                $opt = $item['opt'];
                ReadDoc($opt);
                $data     = array_fill(0, 17, '');
                $data[0]  = TYP_OPTDDMENU;
                $data[2]  = 'a';
                $data[5]  = empty($userprefs[1]) ? $author : $userprefs[1];
                $data[6]  = empty($userprefs[2]) ? $email : $userprefs[2];
                $data[7]  = $item['attn'];
                $data[11] = $fieldc1;
                if ($item['cat']) {
                    $cats1 = explode('|', $fielda1);
                    $cats2 = explode('|', $fielda2);
                    $data[9]  = $cats1[$item['niv'] - 1];
                    $data[10] = $cats2[$item['niv'] - 1];
                    $data[13] = '?';
                    $data[14] = '?';
                } else {
                    $data[9]  = $fieldb1;
                    $data[10] = $fieldb2;
                    $data[13] = $fieldd1;
                    $data[14] = $fieldd2;
                }
                $data[15] = $idmnu;
                $data[16] = $fieldmail;
                $data[17] = $fieldmod;
                $idopt    = ActionOnFields('add', $data);
            }
            $titre = $lng == $lng[0] ? $titre1 : $titre2;
            echo '
<p style="text-align:center;">'.sprintf($admin[1544], $titre).'</p>';
        }
        elseif ($typmenu == 'DDM') {
            foreach ($liste as $item) {
                if ($item['cat'] && $item['niv'] > 1) continue;
                $opt = $item['opt'];
                ReadDoc($opt);
                $data     = array_fill(0, 17, '');
                $data[0]  = TYP_OPTDDMENU;
                $data[2]  = 'a';
                $data[5]  = empty($userprefs[1]) ? $author : $userprefs[1];
                $data[6]  = empty($userprefs[2]) ? $email : $userprefs[2];
                $data[7]  = $item['attn'];
                $data[11] = $fieldc1;
                if ($item['cat']) {
                    $cats = explode('|', $fielda1);
                    $data[9]  = $cats[0];
                    $data[10] = $fielda2;
                    $data[13] = '?';
                    $data[14] = '?';
                }
                if (!$item['cat']) {
                    $data[9]  = $fieldb1;
                    $data[10] = $fieldb2;
                    $data[13] = $fieldd1;
                    $data[14] = $fieldd2;
                }
                $data[15] = $idmnu;
                $data[16] = $fieldmail;
                $data[17] = $fieldmod;
                $idopt    = ActionOnFields('add', $data);
            }
            $titre = $lng == $lng[0] ? $titre1 : $titre2;
            echo '
<p class="text-center">'.sprintf($admin[1544], $titre).'</p>';
        }
    }
}

btable();
include CHEMIN.'admin/bpage.inc';
?>