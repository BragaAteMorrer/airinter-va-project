<?php
/*******************************************************************************
 *   Admin newsletter list
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
 * v6.00.19 (August 26, 2024) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'nwllist.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[33] == '') {
        $nomzone = $admin[546];
        include 'access.inc';
        exit();
    }
}

$idpg    = import('idpg', '', true, 1);
$tri     = import('tri', '', true, -1);
$action  = import('action');
$search  = import('search');
$spseudo = import('spseudo');
$semail  = import('semail');

/// Nombre d'abonnés par page
$nbpg = 25;

$state0 = '<br /><span style="color:red">'.$admin[732].'</span>';
$state1 = '<br /><span style="color:red">'.$admin[733].'</span>';
$state2 = '<br /><span style="color:green">'.$admin[734].'</span>';
$state3 = '<br /><span style="color:black">'.$admin[735].'</span>';
$state4 = '<br /><span style="color:black">'.$admin[736].'</span>';
$state5 = '<br /><span style="color:green">'.$admin[737].'</span>';

function pseudo_sort($a, $b) {
	global $charset;
	$a = mb_strtolower($a[0], $charset);
	$b = mb_strtolower($b[0], $charset);
	if ($a == $b) {
		return 0;
	} else {
		return $a < $b ? -1 : +1;
	}
}

function pseudo_rsort($a, $b) {
	return - pseudo_sort($a, $b);
}

function address_sort($a, $b) {
	global $charset;
	$a[1] = mb_strtolower($a[1], $charset);
	$b[1] = mb_strtolower($b[1], $charset);
	list($an, $ad) = explode('@', $a[1]);
	list($bn, $bd) = explode('@', $b[1]);
	if ($ad == $bd) {
		if ($an == $bn) {
			return 0;
		} else {
			return $an < $bn ? -1 : +1;
		}
	} else {
		return $ad < $bd ? -1 : +1;
	}
}

function address_rsort($a, $b) {
  return - address_sort($a, $b);
}

function TestEmail($email) {
	global $state0, $state1, $state2, $state3, $state4, $state5;
	$result = $state5;
	if (!preg_match("`^[a-zA-Z0-9][a-zA-Z0-9_-]*(?:\.[a-zA-Z0-9_-]+)*@[a-zA-Z0-9][a-zA-Z0-9-]*(?:\.[a-zA-Z0-9-]+)*\.[a-z]{2,6}$`", $email)) {
		$result = $state0;
	}
	return $result;
}

include_once CHEMIN.'inc/func_groupsnwl.php';
$topmess  = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[542]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
$list = ReadDBFields(NEWSLET_LIST);
if ($search == 'ok') {
	$lst = array();
	foreach ($list as $abon) 
		if (!empty($abon[0]) && !empty($abon[1]) && ($abon[0] == $spseudo || $abon[1] == $semail)) {
			$lst[] = $abon;
			break;
		}
	if (!empty($lst)) $list = $lst;
}
for ($i = 0; $i < count($list); $i++) {
	$list[$i][3] = $i;
}

$tri = isset($tri) ? $tri : -1;
switch ($tri) {
case  1 : break;
case -1 : $list = array_reverse($list); break;
case  2 : usort($list, 'pseudo_sort'); break;
case -2 : usort($list, 'pseudo_rsort'); break;
case  3 : usort($list, 'address_sort'); break;
case -3 : usort($list, 'address_rsort'); break;
default : $tri = 1;
}

if ($action == 'fastact') {
    $list0 = ReadDBFields(NEWSLET_LIST);
    if ($search == 'ok' && 1 == count($list)) {
        $lst = array();
        foreach ($list0 as $abon) 
            if (!empty($abon[0]) && !empty($abon[1]) && $abon[0] != $spseudo && $abon[1] != $semail) {
                $lst[] = $abon;
            }
        if (!empty($lst)) {
            $list0 = $lst;
            $list  = $lst;
        }
        unset($idpg, $search);
    } else {
        $count = count($list);
        for ($i = ($idpg - 1)*$nbpg; $i < Min($nbpg*$idpg, $count); $i++) {
            if (isset($_POST['s_'.$i]) && $_POST['s_'.$i] == 'on') {
                DestroyAbonne($list[$i][0]); // suppression dans les groupes
                unset($list0[$list[$i][3]], $list[$i]);
            }
            if (isset($_POST['selgrp_'.$i]) && $_POST['selgrp_'.$i] != '') {
                AddAbonne($_POST['selgrp_'.$i], $list[$i][0]); // ajout dans un groupe
            }
        }
        $list  = array_values($list);
        $list0 = array_values($list0);
    }
    WriteDBFields(NEWSLET_LIST, $list0);
    unset($list0);
}

$count = count($list);
if (!isset($idpg)) {
    $idpg = 1;
} else {
    while ($count <= ($idpg - 1)* $nbpg) $idpg--;
}

htable($admin[542], '100%');
echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=1084&mnuid=937&tconfig=0#z2').$admin[541].'</p>
<hr />';


// if (empty($list)) {
if (strlen($list[0][0]) == 0) {
    echo '
<p style="text-align:center;">
  <i class="far fa-meh-rolling-eyes"></i>
  <strong>'.$admin[543].'</strong>
</p>';
} else {
    $tris = array (1=>$admin[738], -1=>$admin[739], 2=>$admin[740], -2=>$admin[741], 3=>$admin[742], -3=>$admin[743]);
    $navbar = GetNavBar('admin.php?lng='.$lng.'&amp;pg=nwllist&amp;tri='.$tri.'&amp;idpg=', $count, $idpg, $nbpg);
    echo '
<div class="w-98 m-auto">
  <div class="d-inline-block w-48 m-auto" style="height:150px; padding-bottom:20px;">
    <fieldset class="m-0">'.legend($admin[748]).'
      <form name="nwlistsort" method="post" action="admin.php?lng='.$lng.'&amp;pg=nwllist">
		<input type="hidden" name="idpg" value="'.$idpg.'" />
        <div class="text-center">
          <div class="d-inline-block">'.$admin[744].'</div>
          <div class="d-inline-block">
            <select name="tri" class="form-control align-middle">';
    foreach ($tris as $k=>$v) {
      echo '
              <option value="'.$k.'"'.Selected($k == $tri).'>'.($k == $tri ? '&bull; ' : '- ').$v.'</option>';
    }
    echo '
            </select>
          </div>
		</div>
		<div class="text-center mt-2 mx-auto">'.SubmitButton($admin[747]).'</div>
	  </form>
    </fieldset>
  </div>
  <div class="d-inline-block w-48" style="height:186px;">
    <fieldset>'.legend($admin[1392]).'
      <form name="nwlistsearch" method="post" action="admin.php?lng='.$lng.'&amp;pg=nwllist">
	    <input type="hidden" name="search" value="ok" />
        <div class="pl-2 w-100">
          <div class="d-inline-block">'.$admin[517].' :</div>
          <div class="d-inline-block w-90"><input type="text" class="form-control texte" name="spseudo" /></div>
        </div>
        <div class="mt-3 pl-2 w-100">
          <div class="d-inline-block">'.$admin[1014].' :</div>
          <div class="d-inline-block w-90"><input type="text" class="form-control texte" name="semail" /></div>
        </div>
		<div class="w-50 mt-2 mx-auto">'.SubmitButton($admin[1700]).'</div>
      </form>
    </fieldset>
  </div>
</div>
<div class="w-100 m-auto">
  <fieldset class="m-0">'.legend($admin[540].' ('.$count.')').'
    <div class="text-center">'.$navbar.'</div>
    <div class="text-center w-100 m-auto">
      <form name="nwlistdel" method="post" action="admin.php?lng='.$lng.'&amp;pg=nwllist">
      <div class="table-responsive-md">
      <table class="table table-bordered w-100">
        <td colspan="4">
          <div class="float-right mb-2" style="padding-right:30px;">
            '.SubmitButton($admin[1602]).'
            <i class="fas fa-level-down-alt align-middle" style="font-size: 1.4rem;"></i>
          </div>
        </td>
        <tr class="forum">
          <td style="padding:4px;width:25%;"><strong>'.$admin[517].'</strong></td>
          <td style="padding:4px;width:42%;"><strong>'.$admin[1014].'</strong></td>
          <td style="padding:4px;width:28%;"><strong>'.ucfirst($admin[928]).'</strong></td>
          <td style="padding:4px;width:5%;"><strong>'.$admin[186].'</strong></td>
        </tr>';
    $abonnes = ReadAbonnes();
    for ($i = ($idpg - 1)*$nbpg; $i < Min($nbpg*$idpg, $count); $i++) {
        echo '
        <tr class="'.($i % 2 == 0 ? 'rep' : 'quest').'">
          <td style="padding:4px;vertical-align:top;"><strong>'.$list[$i][0].'</strong></td>
          <td style="padding:4px;"><strong>'.$list[$i][1].'</strong>'.TestEmail($list[$i][1]).'</td>';
        $grps    = !empty($abonnes[$list[$i][0]]) ? $abonnes[$list[$i][0]] : array();
        $strgrps = '';
        foreach ($grps as $grp) $strgrps .= $grp.'<br />';
        $groups = ReadGroupsNewsletter();
        $lstgrp = '';
        foreach ($groups as $group=>$abon) {
            if (!in_array($group, $grps)) $lstgrp .= '<option value="'.$group.'">'.$group.'</option>';
        }
        $select = empty($lstgrp) ? '' : '
          <div class="float-right">
            <select class="form-control" name="selgrp_'.$i.'" style="width:120px;">
              <option value="">-- '.$admin[1485].' --</option>
              '.$lstgrp.'
            </select>
          </div>';
        echo '
          <td>'.$strgrps.$select.'<div class="c-right"></div></td>
          <td class="align-middle text-center">';
        echo MakeRadioGroup(array(array('d', 's', $i)));
        echo '
          </td>
        </tr>';
    }
    echo '
        <td colspan="4">
          <div class="float-right mb-2" style="padding-right:30px;">
            '.SubmitButton($admin[1602]).'
            <i class="fas fa-level-up-alt align-middle" style="font-size: 1.4rem;"></i>
          </div>
        </td>
      </table>
      </div>
      <input type="hidden" name="search" value="'.$search.'" />';
    if ($search == 'ok' && 1 == count($list)) {
        echo '
      <input type="hidden" name="spseudo" value="'.$list[0][0].'" />
      <input type="hidden" name="semail" value="'.$list[0][1].'" />';
    }
    echo '
      <input type="hidden" name="action" value="fastact" />
      <input type="hidden" name="idpg" value="'.$idpg.'" />
      <input type="hidden" name="tri" value="'.$tri.'" />
    </form>
  </div>';
	echo '
  <div style="text-align:center">'.$navbar.'</div>
</fieldset>
</div>';
}
btable();
include CHEMIN.'admin/bpage.inc';
?>