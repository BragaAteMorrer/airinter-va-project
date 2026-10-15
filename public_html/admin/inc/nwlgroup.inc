<?php
/*******************************************************************************
 *   Admin newsletter groups
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

if (stristr($_SERVER['SCRIPT_NAME'], 'nwlgroup.inc')) {
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

$act    = import('act');
$group  = import('group');
$abonne = import('abonne');
$regit  = import('regit');
$idpg   = import('idpg', '', true, 1);

function abonne_sort($a, $b) {
	$a = strtolower($a[0]);
	$b = strtolower($b[0]);
	if ($a == $b) {
		return 0;
	} else {
		return $a < $b ? -1 : +1;
	}
}

include_once CHEMIN.'inc/func_groupsnwl.php';

if ($act == 'fast') {
    $dbids = explode(' ', trim(import('ids')));
    $grps  = ReadGroupsNewsletter();
    $lst   = $grps[$group];
    @sort($lst);
    $mess = '';
    foreach ($dbids as $ii) {
        $abonne = $lst[$ii];
        if (isset($_POST['s_'.$ii]) && $_POST['s_'.$ii] == 'on') {
            $mess .= (DelAbonne($group, $abonne) 
              ? ucfirst($admin[928]).' '.$group.' : '.$admin[1711].' '.$abonne.$admin[930].' !<br />' 
              : $admin[1711].' '.$abonne.' '.mb_strtolower($admin[1604], $charset).$admin[930].' !<br />');
        }
    }
    $act = 6;
}

// gestion groupes
if ($act == 5) { 
	$mess = '';
	if ($regit == 5 && $group != '') { //ajouter groupe
		$group = preg_replace('! !', '_', $group);
		$mess  = AddGroupNEWSLETTER($group) ? ucfirst($admin[928]).' '.$group.$admin[929].' !<br />' : ucfirst($admin[928]).' '.$group.$admin[931].' !<br />';
	} 
	elseif ($regit == 6) { // supprimer groupe
		$mess = (DelGroupNEWSLETTER($group) 
          ? ucfirst($admin[928]).' '.$group.$admin[930].' !<br />' 
          : ucfirst($admin[928]).' '.$group.' '.mb_strtolower($admin[1604], $charset).$admin[930].' !<br />');
	}
	$grps = ReadGroupsNewsletter();

	$topmess  = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[1209]);
	include CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
	htable($admin[1209], '100%');
	echo '			
	<p style="text-align:center;">'.ToHelp($lng, '&pg=1086&mnuid=937&tconfig=0#z2').$admin[1146].'</p>
	<hr />
	';
		
	echo '
<p style="text-align:center;">'.$admin[1144].'</p>
<hr />'.$mess.'
<div class="w-100 m-auto" style="max-width:420px;">
  <form name="abonnegrp" action="admin.php?lng='.$lng.'&amp;pg=nwlgroup" method="post">
    <fieldset class="w-100 m-0" style="padding:12px;">
    <div class="table-responsive-md">
    <table class="table table-bordered w-100 m-auto" style="max-width:340px;">
	  <tr class="forum">
	    <td colspan="2" style="width:68%;text-align:center;"><b>'.ucfirst($admin[928]).'</b></td>
	    <td colspan="2" style="text-align:center;"><b>'.$admin[680].'</b></td>
	  </tr>
';

	foreach ($grps as $grp=>$abonnes) {
		echo '
	  <tr class="rep">
		<td style="width:51%;text-align:left;padding:4px 0;">'.$grp.'</td>
		<td style="width:17%;text-align:center;">'.count($abonnes).'</td>
		<td style="text-align:center;">
          <a href="admin.php?lng='.$lng.'&amp;pg=nwlgroup&amp;act=6&amp;group='.$grp.'" title="'.$admin[144].' '.$grp.'">
            <i class="far fa-edit align-middle" style="font-size: 1.3rem;"></i>
          </a><br />
        </td>
		<td style="text-align:center;">
          <a href="admin.php?lng='.$lng.'&amp;pg=nwlgroup&amp;act=5&amp;regit=6&amp;group='.$grp.'" title="'.$admin[186].' '.$grp.'">
            <i class="fas fa-times text-danger align-middle" style="font-size: 1.5rem;"></i>
          </a><br />
        </td>
	  </tr>';
	}
	unset($grps);
	echo '
	</table>
    </div>
	<br />
    <input type="hidden" name="regit" value="5" />
    <input type="hidden" name="act" value="5" />
    <div class="text-center mb-3">
      <div class="d-inline-block">'.$admin[1590].' '.$admin[928].' :</div>
      <div class="d-inline-block"><input class="form-control texte" type="text" name="group" value="" style="width:196px;" /></div>
	</div>
    <div class="row quest p-0 mt-2 w-100 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[540], 'self.location.href=\'admin.php?lng='.$lng.'&pg=nwllist\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div></div>
    </div>
  </fieldset>
</form>
</div>
';
}
//ajout et suppression des abonnés 
elseif ($act == 6) { 
	$aAbonnes = (array)import('aAbonnes', 'POST', FALSE);
    $mess     = '';
    if ($regit == 'add' && $group != '' && count($aAbonnes) != 0) { 
		for ($ijk = 0; $ijk < count($aAbonnes); $ijk++) {
			$abonne = $aAbonnes[$ijk];
            if (!empty($abonne))
                $mess  .= AddAbonne($group, $abonne) 
                    ? ucfirst($admin[928]).' '.$group.' 
                    : '.$admin[1711].' '.$abonne.$admin[929].' !<br />' : $admin[1711].' '.$abonne.$admin[931].' !<br />';
		}
	}
	$topmess  = '<a href="admin.php?lng='.$lng.'">Admin</a> > <a href="admin.php?lng='.$lng.
                '&amp;pg=nwlgroup&amp;act=5">'.$admin[925].'</a> > '.str_replace('Admin - ', '', $admin[1209].' : '.$group);
	include CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($admin[1209].' : '.$group, '100%');
    echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=1086&mnuid=937&tconfig=0#z2').$admin[1145].'</p>
<hr />'.$mess;
    if (FileDBExist(GROUPSNWL)) {
        $grps      = ReadGroupsNewsletter();
        $lst       = $grps[$group];
        $lstabon   = array();
        $dbabonnes = ReadDBFields(NEWSLET_LIST);
        $abonnes   = array();
        $maxlen    = 0;
        foreach ($dbabonnes as $abonne) {
			if (!in_array($abonne[0], $lst)) {
                $maxlen = max($maxlen, strlen($abonne[0].$abonne[1]));
                $abonnes[] = array($abonne[0], $abonne[1]);
            } else {
                $lstabon[] = array($abonne[0], $abonne[1]);
            }
        }
        @usort($abonnes, 'abonne_sort');
        @sort($lstabon);
        $count = count($lstabon);
		echo '
<div class="w-100 m-auto" style="max-width:580px;">
  <p class="forum" style="text-align:left; padding:5px 20px;">'.$group.' ('.$count.')</p>
  <fieldset class="w-100 m-0 pt-4">
  <form name="fast_actions" action="admin.php?lng='.$lng.'&amp;idpg='.$idpg.'&amp;pg=nwlgroup&amp;group='.$group.'&amp;act=fast" method="post">
    <div class="table-responsive-md w-100 m-auto px-1">
    <table class="table table-bordered">
	  <tr>
        <td colspan="3">
          <div class="float-right mb-2" style="padding-right:32px;">
            '.SubmitButton($admin[1602]).'
            <i class="fas fa-level-down-alt align-middle" style="font-size: 1.4rem;"></i>
          </div>
        </td>
      </tr>
      <tr class="forum">
        <td colspan="2" style="text-align:center;"><b>'.$admin[1712].'</b></td>
        <td style="width:76px;text-align:center;"><b>'.$admin[680].'</b></td>
      </tr>';
        $nbpg = 15;
        if (!isset($idpg)) {
            $idpg = 1;
        } else {
            while ($count <= ($idpg - 1)* $nbpg) $idpg--;
        }
        $navbar = GetNavBar('admin.php?lng='.$lng.'&amp;pg=nwlgroup&amp;act=6&amp;group='.$group.'&amp;idpg=', $count, $idpg, $nbpg);
        $ids    = '';
        for ($i = ($idpg - 1)*$nbpg; $i < Min($nbpg*$idpg, $count); $i++) {
			if (!empty($lstabon[$i][0])) {
				echo '
      <tr>
	    <td class="text-left pl-1">'.$lstabon[$i][0].'</td>
	    <td class="text-left">'.$lstabon[$i][1].'</td>
	    <td class="text-center">';
                MakeRadioGroup(array( array("d", "s", $i)));
                echo '
        </td>
      </tr>';
            }
            $ids .= $i." ";
        }
        unset($grps);
        echo '
    </table>
    </div>
    <br />
    <input type="hidden" name="ids" value="'.$ids.'" />
  </form>
  <div style="text-align:center">'.$navbar.'</div><br />';
        echo '
  <form name="frmabonnes" action="admin.php?lng='.$lng.'&amp;pg=nwlgroup" method="post">
    <input type="hidden" name="regit" value="add" />
    <input type="hidden" name="act" value="6" />
    <input type="hidden" name="group" value="'.$group.'" />
    <p style="text-align:center">'.$admin[1713].' '.mb_strtolower($admin[1711], $charset).' :</p>
    <p style="text-align:center">';
        
		$size = min(20, count($abonnes) + 2);
        echo '
	  <select class="form-control w-85 m-auto" name="aAbonnes[]" multiple="multiple" size="'.$size.'" style="font-size:14px;">';
        foreach ($abonnes as $abonne) {
            $len = $maxlen + 10 - strlen($abonne[0].$abonne[1]);
            echo '
	    <option value="'.$abonne[0].'">'.$abonne[0].str_repeat('.', $len).$abonne[1].'</option>';
		}
        echo '
      </select>
    </p>
    <div class="row quest p-0 mt-2 w-100 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[857].' '.$admin[925], 'self.location.href=\'admin.php?lng='.$lng.'&pg=nwlgroup&act=5\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div></div>
    </div>
  </form>
  </fieldset>
</div>';
    } 
}
btable();
include CHEMIN.'admin/bpage.inc';
?>