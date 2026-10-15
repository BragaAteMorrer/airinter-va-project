<?php
/*******************************************************************************
 *   Admin Technicals Information
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2024 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.24 (August 12, 2026) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'update.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[48] == '') {
        $nomzone = $admin[904];
        include 'access.inc';
        exit();
    }
}

$version           = array();
$version['actual'] = trim(MP_VERSION);
$version['url']    = 'https://www.guppyland.org/update.txt';
$version['ok']     = '';
$load_ok           = FALSE;
$lines             = array();

if (ini_get('allow_url_fopen')) {
	$lines = @file($version['url']);
	$load_ok = TRUE;
}
elseif ($curl = curl_init($version['url'])) {
	$timeout = 5; // set to zero for no timeout
	curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
	$version_content = curl_exec($curl);
	curl_close($curl);
	if ($version_content != FALSE) {
		$lines   = explode("\n", $version_content);
		$load_ok = TRUE;
	}
}
else {
	$version['errors'] = $admin[898];
}

if ($load_ok) {
	$version['latest']   = @trim($lines[0]);
	$version['previous'] = @trim($lines[2]);
    $version['note']     = $lng == $lang[0] ? @trim($lines[6]) : @trim($lines[7]);
    $version['cur'] = str_replace('-', '.0', $version['actual']);
    $version['lat'] = str_replace('-', '.0', $version['latest']);
    $version['pre'] = str_replace('-', '.0', $version['previous']);
    $acur = explode('.', $version['cur']);
    $alat = explode('.', $version['lat']);
    $apre = explode('.', $version['pre']);
    $an   = max(count($acur), count($alat), count($apre));
    if ($an > count($alat)) $version['lat'] .= '.00';
    if ($an > count($acur)) $version['cur'] .= '.00';
    if ($an > count($apre)) $version['pre'] .= '.00';
    $ncur = intval(str_replace('.', '', $version['cur']));
    $npre = intval(str_replace('.', '', $version['pre']));
    $nlat = intval(str_replace('.', '', $version['lat']));
    $rpre = intval(substr(str_replace('.', '', $version['pre']), 0, 3));
    $rlat = intval(substr(str_replace('.', '', $version['lat']), 0, 3));
    $cmp  = version_compare($version['cur'], $version['lat']);
    $cmp  = $ncur == $nlat ? 0 : $cmp;
	switch ($version['latest']){
	case '':
		$version['errors'] = $admin[899];
		break;
	default:
		switch ($cmp) {
		case -1 :
			$reco1 = '';
			$reco2 = '';
			$reco3 = '';
			if ($acur[0] < $alat[0]) {
				$reco3 = $admin[1273];
			} else {
                if ((($ncur - $npre) == 0 || ($rlat - $rpre) == 1) && ($npre - $ncur) < 100) $reco1 = $admin[1273]; else $reco2 = $admin[1273];
			}
			//	$admin[1269].'">'.$admin[1269]      Patch non-cumulatif
			//	$admin[1270].'">'.$admin[1270]      Patch cumulatif
			//	$admin[1272].$version['latest']   	Pack complet
			//	$admin[900] 						Une nouvelle version est disponible
			// $admin[1271]							Vous pouvez télécharger l'une de ces trois mises à jour :';
			//	$admin[901].' '.$version['latest'].' '.$admin[902]  version du
			$version['ok'] = '
			  <span style="color:green;">'.
			    $admin[900].'<br />'.
			    $admin[901].' '.$version['latest'].' '.$admin[902].' '.trim($lines[1]).'
			  </span><br /><br />'.
			  $admin[1271].'
			  <ul>
				<li><a href="'.trim($lines[4]).'" target="_blank" title="'.$admin[1269].'">'.$admin[1269].'</a> '.$reco1.'</li>
				<li><a href="'.trim($lines[5]).'" target="_blank" title="'.$admin[1270].'">'.$admin[1270].'</a> '.$reco2.'</li>
				<li><a href="'.trim($lines[3]).'" target="_blank" title="'.$admin[1272].$version['latest'].'">'.$admin[1272].$version['latest'].'</a> '.$reco3.'</li>
			  </ul>';
			break;
		case 0 :
			$version['ok'] = $admin[903]; // Vous utilisez déjà la dernière version
			break;
		case 1 :
			$version['ok'] = $admin[1274]; // Version en développement (non certifiée)
			break;
		}
	}
}
elseif (empty($version['errors'])) {
	$version['errors'] = $admin[898];
}

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[1266]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[1266], '100%');
echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=942&mnuid=937&tconfig=0#z2').'</p>
<div style="padding:8px; width:480px; margin:auto; text-align:left;">
  <fieldset>'.legend($admin[904]).'
	<ul>
	  <li>GuppY '.$version['actual'].'</li>
	  <li>';
	if ($version['ok'] != '') {
		echo $version['ok'];
	}
	else {
		echo '<span style="color:red;">'.$version['errors'].'</span>';
	}
    echo '</li>
	</ul>';
	if ($cmp == -1 && $version['note'] != '') {
		echo '
    <ul style="margin: 0 6px;">
      <li>'.$version['note'].'</li>
    </ul>';
	}
    echo '
  </fieldset>
</div>';

btable();
include CHEMIN.'admin/bpage.inc' ;
?>