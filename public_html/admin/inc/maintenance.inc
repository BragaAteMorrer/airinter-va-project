<?php
/*******************************************************************************
 *   Admin maintenance
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
 * v6.00.19 August 15, 2024) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'maintenance.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[11] == '') {
        $nomzone = $admin[817];
        include 'access.inc';
        exit();
    }
}

$regit = import('regit');

if ($regit == 1) {
    $user1 = import('user');
    switch ($user1) {
    case 'serviz31' : $serviz[31] = import('serviz31'); break;
    case 'serviz176' : $serviz[176] = import('serviz176'); break;
    }
    $serviz[44] = import('serviz44');
    include 'inc/confupdt.inc';

    if ($site[7] == 'on' || PHP700) {
        @ob_flush();
        sleep(PHP700 ? 3 : $site[8]);
        echo BeginJavascript().'window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=maintenance";'.EndJavascript();
    }
}
include CONFIG;

if (is_file(CHEMIN.'skins/'.$page[14].'/confskin'.INCEXT)){
    include CHEMIN.'skins/'.$page[14].'/confskin'.INCEXT;
} else {
    include CHEMIN.'skins/no_skin/confskin'.INCEXT;
}

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', substr($admin[815], 0, -1));
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[811], '100%');

$user = ($userprefs[1] == $serviz[31] ? 'serviz31' : ($userprefs[1] == $serviz[176] ? 'serviz176' : ''));
if (!empty($user)) {
    echo '
<p class="text-center">'.ToHelp($lng, '&pg=950&mnuid=937&tconfig=0#z2').$admin[812].'</p>
<hr />
<div class="w-100 m-auto" style="max-width:560px;">
  <form name="conf4" action="admin.php?lng='.$lng.'&amp;pg=maintenance" method="post">
    <input type="hidden" name="regit" value="1" />
    <input type="hidden" name="user" value="'.$user.'" />
    <fieldset>
      <div class="row">
        <div class="col-md-12 p-4 text-center" style="font-weight:bold;">'.$admin[813].'</div>
      </div>
      <div class="w-80 m-auto">';
    if (!empty($user)) echo '
        <div class="row">
          <div class="col-md-6 pt-2">'.$admin[814].'</div>
          <div class="col-md-6">
            <input class="form-control texte" type="text" name="'.$user.'" size="20" value="'.$userprefs[1].'" readonly="readonly" />
          </div>
        </div>';
    echo '      
        <div class="row py-2">
          <div class="col-md-6"><label for="serviz44">'.$admin[815].'</label></div>
          <div class="col-md-6">
            <input type="checkbox" name="serviz44" id="serviz44" '.Checked($serviz[44]).' />
          </div>
        </div>
      </div>
    </fieldset>
    <div class="row quest p-0 w-98 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
      <div class="clearfix"></div>
    </div>
  </form>
</div>';
}
else echo '
<p class="text-center">'.$admin[847].'</p>';

btable();
include CHEMIN.'admin/bpage.inc';
?>