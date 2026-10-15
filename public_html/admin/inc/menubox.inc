<?php
/*******************************************************************************
 *   Admin Menus
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

if (stristr($_SERVER['SCRIPT_NAME'], 'menubox.inc')) {
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
$idpg    = import('idpg');
$selmenu = import('selmenu');
$newopt  = import('newopt');
$typ     = import('typ');

include_once CHEMIN.'inc/func_groupscol.php';
$grpcols = ReadMembersCol();
if (!empty($userprefs[1])) {
	$myname  = $userprefs[1];
	$myemail = $userprefs[2];
}
else {
	$myname  = $user[0];
	$myemail = $user[1];
}
$grpcol  = empty($grpcol) ? (isset($grpcols[$myname]) ? (isset($grpcols[$myname][0]) ? $grpcols[$myname][0] : '') : '') : $grpcol;
################################################################################
// choix de la boîte menu
if ($act == 1) {
	$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[1219]);
    include CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($admin[1219], '100%');
    $dbmenu = ReadAuthorizedIndex(DBMENUBOX, $myname, $grpcol, $fctwri);
    $dbwork = array();
    for ($i = 0; $i < count($dbmenu); $i++) {
        $dbwork[$i][0] = $dbmenu[$i][$lang[0] == $lng ? 2 : 3];
        $dbwork[$i][1] = $dbmenu[$i][4];
    }
    sort($dbwork);
	echo '
<p class="text-center;">'.ToHelp($lng, '&pg=1032&mnuid=937&tconfig=0#z2');
    if (!empty($dbwork)) {
		echo '
<form name="selmenubox" action="admin.php?lng='.$lng.'&amp;pg=optmenu" method="post">';
		if ($newopt == 'ok') echo '
  <input type="hidden" name="id" value="'.$id.'" />
  <input type="hidden" name="typ" value="'.$typ.'" />
  <input type="hidden" name="form" value="1" />';		
		echo '
  <fieldset class="w-100 mx-auto mt-5" style="max-width:360px;"><legend>'.$admin[1218].'</legend>
  <div class="table-responsive-md">
  <table class="m-auto">
    <tr>
      <td class="text-center">
        <p>
          <select class="form-control" name="menubox" onchange="submit();">
            <option value="" select="selected">'.$admin[1222].'</option>';
    foreach ($dbwork as $menu) {
        echo '
            <option value="'.$menu[1].'">'.$menu[0].'</option>';
    }
    echo '
          </select>
        </p>
      </td>
    </tr>
  </table>
  </div>
  </fieldset>
</form>';
    } else {
        echo '
  <p class="text-center">'.$admin[854].'</p>';
    }
}
################################################################################
// choix du menu déroulant
elseif ($act == 2) {
	$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[1219]);
    include CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($admin[1219], '100%');
    $dbmenu = ReadAuthorizedIndex(DBDDMENUBOX, $myname, $grpcol, $fctwri);
    $dbwork = array();
    for ($i = 0; $i < count($dbmenu); $i++) {
        $dbwork[$i][0] = $dbmenu[$i][$lang[0] == $lng ? 2 : 3];
        $dbwork[$i][1] = $dbmenu[$i][4];
    }
    sort($dbwork);
	echo '
<p class="text-center">'.ToHelp($lng, '&pg=1036&mnuid=937&tconfig=0#z2');
    if (!empty($dbwork)) {
		echo '
<form name="selddmenubox" action="admin.php?lng='.$lng.'&amp;pg=optddmenu" method="post">
  <fieldset class="w-100 mx-auto mt-5" style="max-width:300px;"><legend>'.$admin[1218].'</legend>
  <div class="table-responsive-md">
  <table class="m-auto">
    <tr>
      <td class="text-center">
        <p><select class="form-control" name="ddmenubox" onchange="submit();">
          <option value="" select="selected">'.$admin[1222].'</option>';
    foreach ($dbwork as $menu) {
        echo '
          <option value="'.$menu[1].'">'.$menu[0].'</option>';
    }
    echo '
        </select></p>
      </td>
    </tr>
  </table>
  </div>
  </fieldset>
</form>';
    } else {
        echo '
  <p class="text-center">'.$admin[854].'</p>';
    }
}

btable();
include CHEMIN.'admin/bpage.inc';
?>