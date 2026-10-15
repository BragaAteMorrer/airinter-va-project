<?php
/*******************************************************************************
 *   Admin Configuration Sections
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
 * v6.00.24 (August 12, 2026) : Modify max-width 220px for div
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'config4.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[3] == '') {
        $nomzone = $admin[166];
        include 'access.inc';
        exit();
    }
}

function SectionNameCB($description, $name0, $value0, $name1, $value1, $confpage=false, $confvalue='', $menuico=false, $icovalue='', $menugrp=false, $grpvalue='', $readonly=false) {
    global $flag1, $flag2, $lang, $charset, $admin, $templates;
    echo '
      <tr class="px-3">
        <td class="text-left align-middle" style="max-width:180px;">&bull; <label for="'.$menuico.'">'.$description.'</label></td>
        <td class="text-left align-middle">';
    if (empty($lang[1])) {
        echo '
          <div>
            <input class="form-control texte" style="max-width:200px;" name="'.$name0.'" type="text" value="'.htmlentities($value0, ENT_QUOTES, $charset).'" />
		  </div>';
    } else {
        echo '
          <div style="max-width:220px;">
            <div class="d-inline-block">'.$flag1.'</div>
            <div class="d-inline-block">
		      <input class="form-control texte" style="max-width:200px;" name="'.$name0.'" type="text" value="'.htmlentities($value0, ENT_QUOTES, $charset).'" />
            </div>
		  </div>
          <div style="max-width:220px;">
            <div class="d-inline-block">'.$flag2.'</div>
		    <div class="d-inline-block">
              <input class="form-control texte" style="max-width:200px;" name="'.$name1.'" type="text" value="'.htmlentities($value1, ENT_QUOTES, $charset).'" />
            </div>
		  </div>';
    }
    echo '
        </td>
        <td class="text-left align-middle" style="max-width:170px;">';
    if ($confpage) {
        echo '
          <div style="max-width:150px;">
            <select class="form-control w-100" style="" name="'.$confpage.'">
              <option value="ALL"'.Selected($confvalue == 'ALL').'>'.$admin[1363].'</option>';
        foreach ($templates as $key=>$value)
            echo '
              <option value="'.$key.'"'.Selected($confvalue != 'ALL' && $confvalue == $key).'>'.$key.' - '.$value.'</option>';
        echo '
            </select>
		  </div>';
    }
    echo '
        </td>
        <td class="text-left align-middle">';
    if ($menugrp) {
        $dbgroup = ReadDBFields(GROUPS);
        echo '
          <div style="max-width:150px;">
            <select class="form-control w-100" name="'.$menugrp.'">
              <option value=""'.Selected($grpvalue == '').'> -- '.$admin[1604].' -- </option>
              <option value="all_members"'.Selected($grpvalue != '' && $grpvalue == 'all_members').'>'.$admin[690].'</option>';
        foreach ($dbgroup as $groupe)
            echo '
              <option value="'.$groupe[0].'"'.Selected($grpvalue != '' && $grpvalue == $groupe[0]).'>'.$groupe[0].'</option>';
        echo '
            </select>
          </div>';
    } else echo '-';
    echo '
        </td>';
    if ($menuico) {
        echo '
        <td class="text-left align-middle">
          <input type="checkbox" id="'.$menuico.'" name="'.$menuico.'" 
            value="on"'.Checked($icovalue).($readonly ? ' disabled="disabled"' : '').' />
        </td>';
    }
    echo '
      </tr>';
}

function SectionNameSB($description, $name0, $value0, $name1, $value1, $dynabox=false, $dynavalue='') {
    global $flag1, $flag2, $lang, $charset, $admin;
    echo '
      <tr>
        <td class="text-left align-middle" style="max-width:180px;">&bull; '.$description.'</div>
        <td class="text-left align-middle">';
    if (empty($lang[1])) {
        echo '
          <input class="form-control texte" style="max-width:170px;" name="'.$name0.'" type="text" size="30" value="'.htmlentities($value0, ENT_QUOTES, $charset).'" />';
    } else {
        echo '
          <div>
            <div class="d-inline-block">'.$flag1.'</div>
		    <div class="d-inline-block">
              <input class="form-control texte" style="max-width:170px;" name="'.$name0.'" type="text" size="30" value="'.htmlentities($value0, ENT_QUOTES, $charset).'" />
            </div>
		  </div>
          <div>
            <div class="d-inline-block">'.$flag2.'</div>
		    <div class="d-inline-block">
              <input class="form-control texte" style="max-width:170px;" name="'.$name1.'" type="text" size="30" value="'.htmlentities($value1, ENT_QUOTES, $charset).'" />
            </div>
		  </div>';
    }
    echo '
        </td>';
    if ($dynabox) {
        echo '
        <td class="text-left align-middle">
		  <select class="form-control" style="max-width:150px;" name="'.$dynabox.'">
			<option value="off"'.Selected($dynavalue == "off").'>'.$admin[358].'</option>
			<option value="open"'.Selected($dynavalue == "open").'>'.$admin[359].'</option>';
		if ($dynabox != 'dynabox4' && $dynabox != 'dynabox24')
			echo '
			<option value="close"'.Selected($dynavalue == "close").'>'.$admin[360].'</option>';
		echo '
		  </select>
        </td>';
    }
    echo '
      </tr>';
}

function SectionSelect($description, $dynabox=false, $dynavalue='') {
    global $admin;

    echo '
      <tr">
        <td class="text-left align-middle">&bull; '.$description.'</td>';
    if ($dynabox) {
        echo '
        <td class="text-right align-middle">
		  <select class="form-control" style="max-width:150px;" style="max-width:190px;" name="'.$dynabox.'">
			<option value="off"'.Selected($dynavalue == "off").'>'.$admin[358].'</option>
			<option value="open"'.Selected($dynavalue == "open").'>'.$admin[359].'</option>
			<option value="close"'.Selected($dynavalue == "close").'>'.$admin[360].'</option>
		  </select>
        </td>';
    }
    echo '
      </tr>';
}

$regit = import('regit');
if (!isset($nom[58])) $nom[58] = '';
if (!isset($nom[59])) $nom[59] = '';
if (!isset($nom[60])) $nom[60] = '';
if (!isset($nom[61])) $nom[61] = '';
if (!isset($dynabox[19])) $dynabox[19] = '';
if (!isset($dynabox[20])) $dynabox[20] = '';


if ($regit == 1) {
    include CONFIG;
    $nom[0]  = str_replace('"', '', import('nom0', 'POST', false));  // Page Accueil langue principale
    $nom[10] = str_replace('"', '', import('nom10', 'POST', false)); // Page Accueil deuxième langue
    $nom[7]  = str_replace('"', '', import('nom7', 'POST', false));  // Nouvelles langue principale
    $nom[17] = str_replace('"', '', import('nom17', 'POST', false)); // Nouvelles deuxième langue
    $nom[42] = str_replace('"', '', import('nom42', 'POST', false)); // Blog langue principale
    $nom[43] = str_replace('"', '', import('nom43', 'POST', false)); // Blog deuxième langue
    $nom[2]  = str_replace('"', '', import('nom2', 'POST', false));  // Page des photos langue principale
    $nom[12] = str_replace('"', '', import('nom12', 'POST', false)); // Page des photos deuxième langue
    $nom[1]  = str_replace('"', '', import('nom1', 'POST', false));  // Page des téléchargements langue principale
    $nom[11] = str_replace('"', '', import('nom11', 'POST', false)); // Page des téléchargements deuxième langue
    $nom[3]  = str_replace('"', '', import('nom3', 'POST', false));  // Page des liens langue principale
    $nom[13] = str_replace('"', '', import('nom13', 'POST', false)); // Page des liens deuxième langue
    $nom[24] = str_replace('"', '', import('nom24', 'POST', false)); // FAQ langue principale
    $nom[25] = str_replace('"', '', import('nom25', 'POST', false)); // FAQ deuxième langue
    $nom[9]  = str_replace('"', '', import('nom9', 'POST', false));  // Livre d'or langue principale
    $nom[19] = str_replace('"', '', import('nom19', 'POST', false)); // Livre d'or deuxième langue
    $nom[22] = str_replace('"', '', import('nom22', 'POST', false)); // Forum langue principale
    $nom[23] = str_replace('"', '', import('nom23', 'POST', false)); // Forum deuxième langue
    $nom[26] = str_replace('"', '', import('nom26', 'POST', false)); // Statistiques langue principale
    $nom[27] = str_replace('"', '', import('nom27', 'POST', false)); // Statistiques deuxième langue
    $nom[46] = str_replace('"', '', import('nom46', 'POST', false)); // Contact langue principale
    $nom[47] = str_replace('"', '', import('nom47', 'POST', false)); // Contact deuxième langue
    
    $nom[44] = str_replace('"', '', import('nom44')); // Boite Menu langue principale WEB
    $nom[45] = str_replace('"', '', import('nom45')); // Boite Menu deuxième langue WEB
    $nom[8]  = str_replace('"', '', import('nom8'));  // Compteur langue principale WEB
    $nom[18] = str_replace('"', '', import('nom18')); // Compteur deuxième langue WEB
    $nom[20] = str_replace('"', '', import('nom20')); // Recherche langue principale WEB
    $nom[21] = str_replace('"', '', import('nom21')); // Recherche deuxième langue WEB
    $nom[30] = str_replace('"', '', import('nom30')); // Boite Photorama langue principale WEB
    $nom[31] = str_replace('"', '', import('nom31')); // Boite Photorama deuxième langue WEB
    $nom[32] = str_replace('"', '', import('nom32')); // Boite Calendrier langue principale WEB
    $nom[33] = str_replace('"', '', import('nom33')); // Boite Calendrier deuxième langue WEB
    $nom[34] = str_replace('"', '', import('nom34')); // Boite préférences utilisateur langue principale WEB
    $nom[35] = str_replace('"', '', import('nom35')); // Boite préférences utilisateur deuxième langue WEB
    $nom[38] = str_replace('"', '', import('nom38')); // Boite Newsletter langue principale WEB
    $nom[39] = str_replace('"', '', import('nom39')); // Boite Newsletter deuxième langue WEB
    $nom[36] = str_replace('"', '', import('nom36')); // Boite RSS langue principale WEB
    $nom[37] = str_replace('"', '', import('nom37')); // Boite RSS deuxième langue WEB
    $nom[40] = str_replace('"', '', import('nom40')); // Boite Webmaster - Infos langue principale WEB
    $nom[41] = str_replace('"', '', import('nom41')); // Boite Webmaster - Infos deuxième langue WEB
	$nom[48] = str_replace('"', '', import('nom48')); // Boîte réseaux sociaux langue principale WEB
	$nom[49] = str_replace('"', '', import('nom49')); // Boîte réseaux sociaux deuxième langue WEB
	$nom[54] = str_replace('"', '', import('nom54')); // Boîte nouveautés langue principale WEB
	$nom[55] = str_replace('"', '', import('nom55')); // Boîte nouveautés deuxième langue WEB
	$nom[56] = str_replace('"', '', import('nom56')); // Boîte Contact langue principale WEB
	$nom[57] = str_replace('"', '', import('nom57')); // Boîte Contact deuxième langue WEB
	$nom[58] = str_replace('"', '', import('nom58')); // Boite Tous les documents langue principale WEB
    $nom[59] = str_replace('"', '', import('nom59')); // Boite Tous les documents deuxième langue WEB
	$nom[60] = str_replace('"', '', import('nom60')); // Boite Tous les posts forum pour cloture automatique langue principale WEB
    $nom[61] = str_replace('"', '', import('nom61')); // Boite Tous les posts forum pour cloture automatique deuxième langue WEB


    
    $menuico[0] = import('menuico0'); // Icône Accueil
    $menuico[1] = import('menuico1'); // Icône Nouvelles
    $menuico[2] = import('menuico2'); // Icône Blog
    $menuico[3] = import('menuico3'); // Icône Photorama
    $menuico[4] = import('menuico4'); // Icône Téléchargements
    $menuico[5] = import('menuico5'); // Icône Liens
    $menuico[6] = import('menuico6'); // Icône FAQ
    $menuico[7] = import('menuico7'); // Icône Livre d'or
    $menuico[8] = import('menuico8'); // Icône Forum
    $menuico[9] = import('menuico9'); // Icône Stats
    $menuico[10] = import('menuico10'); // Icône Contact avancé
    $menuico[22] = import('menuico22', 'POST', true, '0'); // Page affichage Nouvelles
    $menuico[23] = import('menuico23', 'POST', true, '0'); // Page affichage Blog
    $menuico[24] = import('menuico24', 'POST', true, '0'); // Page affichage Photorama
    $menuico[25] = import('menuico25', 'POST', true, '0'); // Page affichage Téléchargements
    $menuico[26] = import('menuico26', 'POST', true, '0'); // Page affichage Liens
    $menuico[27] = import('menuico27', 'POST', true, '0'); // Page affichage FAQ
    $menuico[28] = import('menuico28', 'POST', true, '0'); // Page affichage Livre d'or
    $menuico[29] = import('menuico29', 'POST', true, '0'); // Page affichage Forum
    $menuico[30] = import('menuico30', 'POST', true, '0'); // Page affichage Stats
    $menuico[31] = import('menuico31', 'POST', true, '0'); // Page affichage Contact avancé
    $menuico[32] = import('menuico32', 'POST', true, '');  // Groupe Nouvelles
    $menuico[33] = import('menuico33', 'POST', true, '');  // Groupe Blog
    $menuico[34] = import('menuico34', 'POST', true, '');  // Groupe Photorama
    $menuico[35] = import('menuico35', 'POST', true, '');  // Groupe Téléchargements
    $menuico[36] = import('menuico36', 'POST', true, '');  // Groupe Liens
    $menuico[37] = import('menuico37', 'POST', true, '');  // Groupe FAQ
    $menuico[38] = import('menuico38', 'POST', true, '');  // Groupe Livre d'or
    $menuico[39] = import('menuico39', 'POST', true, '');  // Groupe Forum
    $menuico[40] = import('menuico40', 'POST', true, '');  // Groupe Stats
	
    // boîtes dynamiques partie WEB
	$dynabox[0] = import('dynabox0'); // Boite Menu
	$dynabox[2] = import('dynabox2'); // Boite Compteur
	$dynabox[3] = import('dynabox3'); // Boite Recherche
	$dynabox[4] = import('dynabox4'); // Boite Calendrier
	$dynabox[5] = import('dynabox5'); // Boite préférences
	$dynabox[6] = import('dynabox6'); // Boite Newsletter
	$dynabox[7] = import('dynabox7'); // Boite RSS
	$dynabox[8] = import('dynabox8'); // Boite Webmaster - Infos
	$dynabox[9] = import('dynabox9'); // Boîte réseaux sociaux
	$dynabox[10] = import('dynabox10'); // Boîte Rubriques blog
	$dynabox[11] = import('dynabox11'); // Boîte Derniers billets blog
	$dynabox[12] = import('dynabox12'); // Boîte Derniers commentaires blog
	$dynabox[13] = import('dynabox13'); // Boîte Calendrier du blog
	$dynabox[14] = import('dynabox14'); // Boîte Billets des amis blog
	$dynabox[15] = import('dynabox15'); // Boîte Nouveautés du site
	$dynabox[16] = import('dynabox16'); // Boîte Contacts avancés
	$dynabox[17] = import('dynabox17'); // Boîte Photorama
	$dynabox[18] = import('dynabox18'); // Boîte Archives blog
	$dynabox[19] = import('dynabox19'); // PAge affichage tous les documents
	$dynabox[20] = import('dynabox20'); // PAge affichage tous les posts à cloturer
	
	
    include 'inc/confupdt.inc';
    if ($site[7] == 'on' || PHP700) {
        @ob_flush();
        sleep(PHP700 ? 3 : $site[8]);
        echo BeginJavascript().'window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=config4";'.EndJavascript();
    }
}
include CONFIG;

if (is_file ($meskin.'confskin.inc')) {
    include $meskin.'confskin.inc';
} else {
    die('Skin not found : '.$meskin);
}

$headinc .= '
    <script type="text/javascript">
        $(document).ready( function() {
            $("#gyaccordion").hoverAccordion({
                keepHeight: false,
                activateItem: 1,
				onClickOnly: true,
                speed: 400
            });
            $("#gyaccordion").children("li:first").addClass("firstitem");
            $("#gyaccordion").children("li:last").addClass("lastitem");
        });
    </script>
';

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[167]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[167], '100%');

echo '
<div style="text-align:center">
  <p>'.ToHelp($lng, '&pg=956&mnuid=937&tconfig=0#z2').$admin[168].'</p>
</div>
<hr />
<form name="conf4" action="admin.php?lng='.$lng.'&amp;pg=config4" method="post">
  <input type="hidden" name="regit" value="1" />
  <fieldset class="m-auto" style="max-width:840px;">'.legend($admin[70]).'
    <div class="m-auto" id="accordion">
      <div class="row quest p-0 mb-2 w-100 m-auto">
	    <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
        <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
      </div>';
echo '
      <div class="card">
        <a class="card-header card-link" data-toggle="collapse" href="#collapse1">'.$admin[806].'</a>
        <div class="collapse show" data-parent="#accordion" id="collapse1">
          <div class="card-body" style="max-width:840px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div style="margin:4px auto; padding:0 0 8px 0;">
              <div style="float:right; margin-right:6px; text-align:center; line-height:16px;"><b>'.$admin[1244].'</b></div>
              <div style="float:right; margin-right:20px; width:120px; text-align:center; line-height:16px;"><b>'.$admin[932].'</b></div>
              <div style="float:right; margin-right:20px; width:140px; text-align:center; line-height:16px;"><b>'.$admin[1335].'</b></div>
              <div class="table-responsive-md">
                <table class="table">
              ';
SectionNameCB($admin[71],  "nom0",  $nom[0],  "nom10", $nom[10], false, '', "menuico0", $menuico[0], '', false, $serviz[1] != '0');
SectionNameCB($admin[72],  "nom7",  $nom[7],  "nom17", $nom[17], "menuico22", $menuico[22], "menuico1", $menuico[1], "menuico32", $menuico[32], $serviz[8] == '');
SectionNameCB($admin[780], "nom42", $nom[42], "nom43", $nom[43], "menuico23", $menuico[23], "menuico2", $menuico[2], "menuico33", $menuico[33], $serviz[53] == '');
SectionNameCB($admin[74],  "nom2",  $nom[2],  "nom12", $nom[12], "menuico24", $menuico[24], "menuico3", $menuico[3], "menuico34", $menuico[34], $serviz[9] == '');
SectionNameCB($admin[73],  "nom1",  $nom[1],  "nom11", $nom[11], "menuico25", $menuico[25], "menuico4", $menuico[4], "menuico35", $menuico[35], $serviz[10] == '');
SectionNameCB($admin[75],  "nom3",  $nom[3],  "nom13", $nom[13], "menuico26", $menuico[26], "menuico5", $menuico[5], "menuico36", $menuico[36], $serviz[11] == '');
SectionNameCB($admin[207], "nom24", $nom[24], "nom25", $nom[25], "menuico27", $menuico[27], "menuico6", $menuico[6], "menuico37", $menuico[37], $serviz[14] == '');
SectionNameCB($admin[76],  "nom9",  $nom[9],  "nom19", $nom[19], "menuico28", $menuico[28], "menuico7", $menuico[7], "menuico38", $menuico[38], $serviz[12] == '');
SectionNameCB($admin[164], "nom22", $nom[22], "nom23", $nom[23], "menuico29", $menuico[29], "menuico8", $menuico[8], "menuico39", $menuico[39], $serviz[13] == '');
SectionNameCB($admin[1182], "nom46", $nom[46], "nom47", $nom[47], "menuico31", $menuico[31], "menuico10", $menuico[10], '', false, $serviz[99] == '');
echo '
                </table>
              </div>
			</div>
          </div>
        </div>
      </div>
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse2">'.strtoupper($admin[346]).'</a>
        <div class="collapse" data-parent="#accordion" id="collapse2">
          <div class="card-body" style="max-width:840px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div style="float:right; margin-right:40px; text-align:center;"><b>'.$admin[357].'</b></div>
            <div class="table-responsive-md">
              <table class="table">';
SectionNameSB($admin[880]." :", "nom44", $nom[44], "nom45", $nom[45], "dynabox0", $dynabox[0]);
SectionNameSB($admin[80], "nom8",  $nom[8], "nom18", $nom[18], "dynabox2", $dynabox[2]);
SectionNameSB($admin[163], "nom20", $nom[20], "nom21", $nom[21], "dynabox3", $dynabox[3]);
SectionNameSB($admin[448], "nom32", $nom[32], "nom33", $nom[33], "dynabox4", $dynabox[4]);
SectionNameSB($admin[450].' :', "nom34", $nom[34], "nom35", $nom[35], "dynabox5", $dynabox[5]);
SectionNameSB($admin[548], "nom38", $nom[38], "nom39", $nom[39], "dynabox6", $dynabox[6]);
SectionNameSB($admin[610], "nom36", $nom[36], "nom37", $nom[37], "dynabox7", $dynabox[7]);
SectionNameSB($admin[853], "nom40", $nom[40], "nom41", $nom[41], "dynabox8", $dynabox[8]);
SectionNameSB($admin[336], "nom48", $nom[48], "nom49", $nom[49], "dynabox9", $dynabox[9]);
SectionNameSB($admin[1374], "nom54", $nom[54], "nom55", $nom[55], "dynabox15", $dynabox[15]);
SectionNameSB($admin[1892], "nom58", $nom[58], "nom59", $nom[59], "dynabox19", $dynabox[19]);
SectionNameSB($admin[1910], "nom60", $nom[60], "nom61", $nom[61], "dynabox20", $dynabox[20]);
SectionNameSB($admin[1446], "nom56", $nom[56], "nom57", $nom[57], "dynabox16", $dynabox[16]);
SectionNameSB($admin[1456], "nom30", $nom[30], "nom31", $nom[31], "dynabox17", $dynabox[17]);
echo '
              </table>
            </div>
            <div style="height:12px;"></div>
            <div class="table-responsive-md">
              <table class="table">';
SectionSelect($admin[769].' '.$admin[1672], "dynabox10", $dynabox[10]);
SectionSelect($admin[769].' '.$admin[1673], "dynabox11", $dynabox[11]);
SectionSelect($admin[769].' '.$admin[1674], "dynabox12", $dynabox[12]);
SectionSelect($admin[769].' '.$admin[1765], "dynabox13", $dynabox[13]);
SectionSelect($admin[769].' '.$admin[1676], "dynabox14", $dynabox[14]);
SectionSelect($admin[769].' '.$admin[1675].' '.$admin[770], "dynabox18", $dynabox[18]);
echo '
              </table>
            </div>
        </div>
      </div>
      <div class="row quest p-0 mt-2 w-100 m-auto">
	    <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
        <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
      </div>
    </div>
  </fieldset>
</form>
';

btable();
include CHEMIN.'admin/bpage.inc';
?>
