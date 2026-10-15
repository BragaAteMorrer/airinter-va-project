<?php
/*******************************************************************************
 *   Social Networks menu
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

if (stristr($_SERVER['SCRIPT_NAME'], 'snmenu.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[50] == '') {
        $nomzone = $admin[336];
        include 'access.inc';
        exit();
    }
}

$regit  = import('regit');
$act    = import('act');
$coders = import('rscode', 'POST', false);

if ($regit == 1) {
$mettre = "<?php
\$coders = stripslashes('".addslashes($coders)."');
?>";
	WriteFullDB(CONFIGREP.'sncode.inc', $mettre);
}
include CONFIGREP.'sncode.inc';

################################################################################
// code pour les services
if($act == 1) {
	$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[348]);
	include CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($admin[348], '100%');
    echo '
  <p style="text-align:center;">'.ToHelp($lng, '&pg=1046&mnuid=937&tconfig=0#z2').$admin[352].'</p>
  <hr />
  <div class="w-100 m-auto" style="max-width:480px;">
    <form name="members" action="admin.php?lng='.$lng.'&amp;pg=snmenu&amp;act=1" method="post">
      <input type="hidden" name="regit" value="1" />
      <br />
      <div class="table-responsive-md">
	  <table class="table m-auto border-0">
		<tr><td style="text-align:center;"><b>'.$admin[348].'</td></tr>
        <tr>
          <td style="text-align:center;">
            <textarea name="rscode" class="w-100" style="max-width:470px;" rows="6">'.stripslashes($coders).'</textarea>
          </td>
        </tr>
	  </table>
      </div>
	  <br />
	  <div class="quest" style="padding:2px 20px;">
		<div style="float:right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div>
		<div style="clear:both"></div>
	  </div>
	</form>
  </div>';
}
btable();
include CHEMIN.'admin/bpage.inc';
?>