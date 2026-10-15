<?php
/*******************************************************************************
 *   Admin : To rename a category in a menu box
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
 * v6.00.03 (August 10, 2021) : adaptation to php 8
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'rencat.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[17] == '') {
        $nomzone = $admin[1364];
        include 'access.inc';
        exit();
    }
}

$id      = import('id');
$rencat  = import('rencat', 'POST', NULL);

################################################################################
function _getPrefixCateg($prefix, $categ) {
    $x = explode('|', $prefix);
    if ('' == trim($x[0])) unset($x[0]);
    if ('' != $categ) $x[] = $categ;
    return count($x) > 0 ? implode('|', $x) : '';
}

function _changeCateg($prefix, $old, $new, $fielda) {
    $prefix_old = _getPrefixCateg($prefix, $old);
    $prefix_new = _getPrefixCateg($prefix, $new);
    if ('' == $prefix_old) {
        if ('' == $fielda) {
            return $prefix_new;
        } else {
            echo "## DEBUG 1 ## |$prefix|$old|$new|$fielda|<br>";
            return FALSE;
        }
    } else {
        $p = mb_strpos($fielda, $prefix_old);
        if (0 === $p) {
            return $prefix_new.mb_substr($fielda, $p + mb_strlen($prefix_old));
        } else {
            echo "## DEBUG 4 ## |$prefix|$old|$new|$fielda|<br>";
            return FALSE;
        }
    }
}

################################################################################
$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[1365]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[1365], '100%');

$select = isset($rencat['select']) ? $rencat['select'] : -1;
$ok = FALSE;
/// Y-a-t-il un traitement valide à  effectuer ?
if (isset($rencat)) {
    $ok = isset($rencat['prefix']) && isset($rencat['old']) && isset($rencat['new']);
    $ok = $ok && isset($rencat['old'][$lang[0]]);
    $ok = $ok && isset($rencat['new'][$lang[0]]);
    if ('' == $lang[1]) {
        $ok = $ok && ('' == $rencat['prefix'][$lang[0]] || '' != $rencat['new'][$lang[0]]);
        $ok = $ok && ($rencat['old'][$lang[0]] != $rencat['new'][$lang[0]]);
    } else {
        $ok = $ok && isset($rencat['old'][$lang[1]]);
        $ok = $ok && isset($rencat['new'][$lang[1]]);
        $ok = $ok && (
            ('' == $rencat['prefix'][$lang[0]] || '' != $rencat['new'][$lang[0]]) &&
            ('' == $rencat['prefix'][$lang[1]] || '' != $rencat['new'][$lang[1]])
        );
        $ok = $ok && (
            ($rencat['old'][$lang[0]] != $rencat['new'][$lang[0]]) ||
            ($rencat['old'][$lang[1]] != $rencat['new'][$lang[1]])
        );
    }
}
/// Traitement du formulaire
if ($ok) {
    $select = -1;
    $path = empty($rencat['prefix'][$lng]) ? '' : $rencat['prefix'][$lng].'|';
    echo '
  <p style="text-align:center;">'.sprintf($admin[1370], htmlentities("$path"), htmlentities($rencat['old'][$lng]), htmlentities($rencat['new'][$lng])).'</p>';
    $oms = ReadDbFields(DBOPTMENU);

    foreach ($oms as $om) {
        if ($om[5] != $id) continue;
        $prefix_categ = _getPrefixCateg($rencat['prefix'][$lang[0]], $rencat['old'][$lang[0]]);
        if (('' == $om[0] && '' == $prefix_categ) || ('' != $prefix_categ && 0 === strpos($om[0] , $prefix_categ))) {
            ReadDoc($om[4]);
            $new = _changeCateg($rencat['prefix'][$lang[0]], $rencat['old'][$lang[0]], $rencat['new'][$lang[0]], $fielda1);
            $fielda1 = FALSE === $new ? $fielda1 : $new;

            if ('' != $lang[1]) {
                $new = _changeCateg($rencat['prefix'][$lang[1]], $rencat['old'][$lang[1]], $rencat['new'][$lang[1]], $fielda2);
                $fielda2 = FALSE === $new ? $fielda2 : $new;
            }

            $fielda1 = addslashes($fielda1);
            $fielda2 = addslashes($fielda2);
            $fieldb1 = addslashes($fieldb1);
            $fieldb2 = addslashes($fieldb2);
            WriteDoc();
        }
    }
    UpdateDBdtb(TYP_OPTMENU);
}
/// Préparation du formulaire
$oms = ReadDbFields(DBOPTMENU);
sort($oms);
$cats = array();
$currcat = '---';
$icat = -1;
foreach ($oms as $om) {
    if ($om[5] != $id) continue;
    if ($om[0] != $currcat) {
        $cats[++$icat] = array($om[0], $om[1], $om[4], 1);
        $currcat = $om[0];
    } else {
        $cats[$icat][3]++;
    }
}
sort($cats);
unset($oms, $icat, $currcat);

$curcats = array();
$liste = array();
foreach ($cats as $item) {
    if (empty($item[0])) {
        $liste[] = array(
            0, '', '', '', '', $item[2], $item[3]
            );
    } else {
        $cats0 = explode('|', $item[0]);
        $cats1 = explode('|', $item[1]);
        $xcat0 = $xcat1 = '';
        for ($i = 0; $i < count($cats0); $i++) {
            if (@$curcats[$i] != @$cats0[$i]) {
                $liste[] = array(
                    $i, $xcat0, $cats0[$i], $xcat1, @$cats1[$i], $item[2], ($item[0] ==  $xcat0 ? $item[3] : '*')
                    );
                $curcats[$i] = $cats0[$i];
                array_splice($curcats, $i+1);
            }
            $xcat0 .= (empty($xcat0) ? '' : '|').$cats0[$i];
            $xcat1 .= (empty($xcat1) ? '' : '|').@$cats1[$i];
        }
    }
}
unset($cats0, $cats1, $curcats, $xcat0, $xcat1);
$select = $select < count($liste) ? $select : -1;
if (1 == count($liste)) $select = 0;
// Affichage du formulaire
if (0 == count($liste)) {
    echo '
  <p>'.$admin[1369].'</p>';

} else {

    echo '
<div class="w-100 mx-auto" style="max-width:520px;">
  <p class="forum" style="text-align:left; padding:5px 20px;">'.$admin[1364].'</p>
  <form name="frencat1" action="admin.php?lng='.$lng.'&amp;pg=rencat&amp;id='.$id.'" method="post">
    <fieldset class="w-100 mx-0 my-3 text-center">
      <input type="hidden" id="prefix_'.$lang[0].'" name="rencat[prefix]['.$lang[0].']" value="'.(-1 != $select ? htmlentities($liste[$select][1]) : '').'" />
      <input type="hidden" id="prefix_'.$lang[1].'" name="rencat[prefix]['.$lang[1].']" value="'.(-1 != $select ? htmlentities($liste[$select][3]) : '').'" />
      <input type="hidden" id="old_'.$lang[0].'" name="rencat[old]['.$lang[0].']" value="'.(-1 != $select ? htmlentities($liste[$select][2]) : '').'" />
      <input type="hidden" id="old_'.$lang[1].'" name="rencat[old]['.$lang[1].']" value="'.(-1 != $select ? htmlentities($liste[$select][4]) : '').'" />
      <div>'.$admin[1366].'</div>
      <div>
        <select name="rencat[select]" size="'.count($liste).'" class="form-control w-100 mx-auto" 
          style="overflow:none;max-width:320px;" onchange="change_rencat(this.value);">';
    foreach ($liste as $i=>$item) {
        $value = htmlentities($item[$lng == $lang[0] ? 2 : 4]);
        $value = empty($value) ? ' -- '.$admin[1125].' -- ' : $value;
        echo '
          <option value="'.$i.'"'.Selected($select == $i).'>'.str_pad('', 15*$item[0], ' &nbsp; &nbsp; ').$value.'</option>';
    }
    echo '
        </select>
		<noscript><br />'.SubmitButton($admin[1367]).'</noscript>
      </div>
      <p>'.$admin[1368].'</p>';
    if ('' == $lang[1]) {
        echo '
      <div class="w-100 mx-auto" style="max-width:320px;">
        <input class="form-control texte" type="text" id="new_'.$lang[0].'" name="rencat[new]['.$lang[0].']" 
          value="'.(-1 != $select ? htmlentities($liste[$select][2]) : '').'" />
      </div>';
    } else {
        echo '
      <div>
        <div class="d-inline-block w-100 mx-auto" style="max-width:24px;">'.$flag1.'</div>
        <div class="d-inline-block w-100 mx-auto" style="max-width:320px;">
          <input class="form-control texte" type="text" id="new_'.$lang[0].'" name="rencat[new]['.$lang[0].']" 
            value="'.(-1 != $select ? htmlentities($liste[$select][2]) : '').'" />
        </div>
      </div>
      <div>
        <div class="d-inline-block w-100 mx-auto" style="max-width:24px;">'.$flag2.'</div>
        <div class="d-inline-block w-100 mx-auto" style="max-width:320px;">
	      <input class="form-control texte" type="text" id="new_'.$lang[1].'" name="rencat[new]['.$lang[1].']" 
            value="'.(-1 != $select ? htmlentities($liste[$select][4]) : '').'" />
        </div>
      </div>';
    }

    echo '
    </fieldset>';
    echo '
    <div class="row quest p-0 mt-2 w-100 m-auto">
      <div class="col-md-6">'.SubmitButton($admin[1220], 'self.location.href=\'admin.php?lng='.$lng.'&pg=menu&tri=ch\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[1318], '', '', $imgdroite).'</div></div>
    </div>
  </form>
</div>
';
    echo BeginJavascript().'
var rencat = [';
    foreach ($liste as $i=>$item) {
        echo '
    '.(0 == $i ? '' : ',').'[ "'.addslashes($item[1]).'", "'.addslashes($item[2]).'", "'.addslashes($item[3]).'", "'.addslashes($item[4]).'" ]';
    }
    echo '
];
function change_rencat(value) {
    $("#prefix_'.$lang[0].'").val( rencat[value][0] );
    $("#prefix_'.$lang[1].'").val( rencat[value][2] );
    $("#old_'.$lang[0].'").val( rencat[value][1] );
    $("#old_'.$lang[1].'").val( rencat[value][3] );
    $("#new_'.$lang[0].'").val( rencat[value][1] );
    $("#new_'.$lang[1].'").val( rencat[value][3] );
}
$("#select").hide();
'.EndJavascript();

}

################################################################################

btable();
include CHEMIN.'admin/bpage.inc';
?>