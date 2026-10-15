<?php
/*******************************************************************************
 *   Admin Blog topics
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
 * v6.00.19 (August 26, 2024) : initial release
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'blogrub.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[39] == '') {
        $nomzone = $admin[13];
        include 'access.inc';
        exit();
    }
}

$regit = import('regit');
$idpg  = import('idpg');

if ($regit == 'fast') {
    
}

if ($regit == 'fast') {
    
}

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.$admin[15];
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable(ucfirst($admin[15]), '100%');
echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=1074&mnuid=937&tconfig=0#z2').$admin[1772].'</p>
<hr />';
echo '
<div class="w-100 m-auto" style="max-width:560px;">
  <form name="fast_actions" action="admin.php?lng='.$lng.'&amp;pg=members" method="post">
  <fieldset style="padding-top:12px;">
    <input type="hidden" name="regit" value="fast" />
	<div class="table-responsive-md">
    <table class="table table-bordered text-center m-auto" style="max-width:520px;">
	  <tr>
        <td colspan="2">
          <div class="float-right mb-2" style="padding-right:60px;">
            '.SubmitButton($admin[1602]).'
            <i class="fas fa-level-down-alt align-middle" style="font-size: 1.4rem;"></i>
          </div>
        </td>
      </tr>
      <tr class="forum">
		<td class="text-left pl-1 py-1"><b>'.ucfirst($admin[169]).'</b></td>
		<td class="text-center py-1" style="width:76px;"><b>'.$admin[680].'</b></td>
	  </tr>';
if (empty($idpg)) {
    $idpg = 1;
} else {
    while ($count <= ($idpg - 1)* $nbpg) $idpg--;
}
$navbar = GetNavBar('admin.php?lng='.$lng.'&amp;pg=blogrub&amp;idpg=', $count, $idpg, $nbpg);
$ids    = '';
for ($i = ($idpg - 1)*$nbpg; $i < Min($nbpg*$idpg, $count); $i++) {
    if (!empty($lst[$i])) {
        echo '
	  <tr class="rep">
		<td class="text-left pl-1 py-1">'.$lst[$i].'</td>
		<td class="text-center py-1">';
                MakeRadioGroup(array( array("d", "s", $i)));
                echo '
		</td>
	  </tr>';
            }
            $ids .= $i." ";
        }
        unset($grps);
        echo '
	  <tr>
        <td colspan="2">
          <div class="float-right mb-2" style="padding-right:60px;">
            '.SubmitButton($admin[1602]).'
            <i class="fas fa-level-up-alt align-middle" style="font-size: 1.4rem;"></i>
          </div>
        </td>
      </tr>
	</table>
    </div>
    <br />
    <input type="hidden" name="ids" value="'.$ids.'" />
  </form>
  <div class="text-center">'.$navbar.'</div>';
        echo '
  <form name="members" action="admin.php?lng='.$lng.'&amp;pg=blogrub"  method="post">
    <input type="hidden" name="regit" value="add" />
	<div class="text-center">'.$admin[1773].' :</div>
	<div class="text-center'.($lang[1] == '' ? ' mb-4' : '').'">
      '.($lang[1] != '' ? '<div class="d-inline-block">'.$flag1.'</div>' : '').'
      <div class="d-inline-block">
        <input type="text" class="form-control mx-auto texte" name="newtopic0" style="width:200px;" />
      </div>';
        if ($lang[1] != '') {
            echo '
    </div>
	<div class="text-center mb-4">
      <div class="d-inline-block">'.$flag2.'</div>
      <div class="d-inline-block">
        <input type="text" class="form-control mx-auto texte" name="newtopic1" style="width:200px;" />
      </div>';
        }
        echo '
	</div>
	<div class="quest" style="padding:2px 20px;">
      <div class="float-right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div>
      <div class="clearfix"></div>
    </div>
  </fieldset>
  </form>
</div>';

btable();
include CHEMIN.'admin/bpage.inc';
?>