<?php
/*******************************************************************************
 *   Admin Routine
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2023 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.24 (August 12, 2026) : Check for CONFIG existence
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'admin.inc')) {
    header('location:../index.php');
    die();
}
// LOg 
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d');
$nomficlog = CHEMIN.'data/log/admin'.'_'.$dateheureminiutesecl.'.log';

// fin de log
if (!isset($serviz[31]) || !isset($serviz[176]))
{
    die("!!! Erreur - Veuillez contacter le webmaster du site. !!! Error - Please contact the site's webmaster");
}
if (import('action') == "news") {
    $titre1 = $titre2 = $contenu1 = $contenu2 = '';
    $slng    = $lng;
    $lng     = $lang[0];
    $datedeb = date('Ymd');
    include CHEMIN.'inc/lang/'.$lang[0].'-admin'.INCEXT;
	include CHEMIN.'inc/lang/'.$lang[0].'-web'.INCEXT;
    $titre1   = sprintf($admin[1518], FormatDate($datedeb), $serviz[119]);
    $contenu1 = fbox_lastdoc('NWL');
    if (!empty($lang[1])) {
        $lng     = $lang[1];
        $datedeb = date('Ymd');
        include CHEMIN.'inc/lang/'.$lang[1].'-admin'.INCEXT;
        $titre2   = sprintf($admin[1518], FormatDate($datedeb), $serviz[119]);
        $contenu2 = fbox_lastdoc('NWL');
    }
    $lng = $slng;
	include CHEMIN.'inc/lang/'.$lng.'-admin'.INCEXT;
    
    $data     = array_fill(0, 17, '');
    $data[0]  = 'nl';
    $data[2]  = $droitaccegroupredac == 'off' ? ($status == 'on' ? 'a' : 'i'): 'i';
    $data[3]  = $datedeb.'-';
    $data[5]  = $userprefs[1];
    $data[6]  = $userprefs[2];
    $data[9]  = $lng == $lang[0] ? $titre1 : $titre2;
    $data[10] = $lng == $lang[0] ? $titre2 : $titre1;
    $data[11] = $lng == $lang[0] ? $contenu1 : $contenu2;
    $data[12] = $lng == $lang[0] ? $contenu2 : $contenu1;
    $id       = ActionOnFields('add', $data);
    unset($data, $action);
}

include_once CHEMIN.'inc/func_groupscol.php';

$idpg      = import('idpg');
$id        = isset($id) ? $id : import('id');
$typ       = import('typ');
$form      = import('form');
$act       = import('act');
$add       = import('add');
$del       = import('del');
$mod       = import('mod');
$dup       = import('dup');
$tri       = import('tri');
$cat1      = import('cat1',     'POST', FALSE);
$cat2      = import('cat2',     'POST', FALSE);
$titre1    = import('titre1',   'POST', FALSE);
$titre2    = import('titre2',   'POST', FALSE);
$contenu1  = import('contenu1', 'POST', FALSE);
$contenu2  = import('contenu2', 'POST', FALSE);
$nouveau1  = import('nouveau1', 'POST', FALSE);
$nouveau2  = import('nouveau2', 'POST', FALSE);
$ancre     = import('ancre');
$fieldweb  = import('fieldweb');
$fieldmail = import('fieldmail', 'POST', true, '0');
$fieldmod  = import('fieldmod');
$pseudo1   = import('pseudo1');
$grpcol    = import('grpcol');
$email1    = import('email1');
$annee     = import('annee');
$mois      = import('mois');
$day       = import('day');
$status    = import('status');
$hh1       = import('hh1');
$mn1       = import('mn1');
$sth1      = import('sth1');
$hh2       = import('hh2');
$mn2       = import('mn2');
$sth2      = import('sth2');
$group     = import('group');
$cbrpt     = import('cbrpt');
$typrpt    = import('typrpt', 'POST', true, '0');
$drpt      = import('drpt');
$drpt      = $drpt == '' ? '1' : $drpt;
$wrpt      = import('wrpt');
$wrpt      = $wrpt == '' ? '1' : $wrpt;
$mrpt      = import('mrpt');
$mrpt      = $mrpt == '' ? '1' : $mrpt;
$dayrpt    = import('dayrpt', 'POST', true, '0');
$nodayrpt  = import('nodayrpt');
$torpt     = import('torpt', 'POST', FALSE);
$menubox   = import('menubox');
$ddmenubox = import('ddmenubox');
$majfile   = import('majfile');
$majfile2  = import('majfile2');
$datecrea  = import('datecrea');
$datemod   = import('datemod');
$datedeb   = import('datedeb');
$datefin   = import('datefin');
$svart     = import('svart');
$svart2    = import('svart2');
$tconfart  = import('tconfart');
$highart   = import('highart');
$okreact   = import('okreact');

$grpcols = ReadMembersCol();
$grpcol  = empty($grpcol) ? (isset($grpcols[$myname]) ? (isset($grpcols[$myname][0]) ? $grpcols[$myname][0] : '') : '') : $grpcol;

$erreur       = '';
$err_datedeb  = false;
$err_datefin  = false;
$err_pseudo1  = false;
$err_email1   = false;
$err_hhmn     = false;
$err_cat1     = false;
$err_cat2     = false;
$err_titre1   = false;
$err_titre2   = false;
$err_nouveau1 = false;
$err_nouveau2 = false;
$err_contenu1 = false;
$err_contenu2 = false;					  
if (!in_array($qtype, array(TYP_GUESTBK, TYP_FORUM, TYP_NWL,TYP_REACT, TYP_REBLOG, TYP_RULES, TYP_RSS, TYP_BSS, TYP_SOCNET))) {
	if ($datedeb != '') {
		$err_datedeb = 1 != preg_match('/^[0-9]{12,12}$/', $datedeb) && ($add == 1 || $mod == 1) ? true : false;
		$erreur     .= $err_datedeb ? '<li>'.$admin[1585].$admin[1407].$admin[1587].' => '.$admin[1409].'</li>': '';
	}
	if ($datefin != '') {
		$err_datefin = 1 != preg_match('/^[0-9]{12,12}$/', $datefin) && ($add == 1 || $mod == 1) ? true : false;
		$erreur     .= $err_datefin ? '<li>'.$admin[1585].$admin[1408].$admin[1587].' => '.$admin[1409].'</li>': '';
		$debdate     = $datedeb != '' ? $datedeb : date('Ymdhi');
		$err_datefin = $debdate >= $datefin && ($add == 1 || $mod == 1) ? true : false;
		$erreur     .= $err_datefin ? '<li>'.$admin[1585].$admin[1408].$admin[1587].' => '.$admin[1423].'</li>': '';
	}
}
if ($bname == 1) {
	$err_pseudo1 = 1 != preg_match('/^[-a-zA-Z0-9_]{2,'.$serviz[148].'}$/', $pseudo1) && ($add == 1 || $mod == 1) ? true : false;
	$erreur     .= $err_pseudo1 ? '<li>'.$admin[1588].'</li>': '';
}
if ($bemail == 1) {
	$err_email1 = 1 != preg_match('/^[^\.\s]+(\.[^\.\s]+)*@[^\.\s]+(\.[^\.\s]+)+$/', $email1) && ($add == 1 || $mod == 1) ? true : false;
	$erreur    .= $err_email1 ? '<li>'.$admin[544].'</li>': '';
}
if ($bcat == 1) {
	if ($qtype == TYP_AGENDA) {
        if ($site[22] == 'H3') {
			$sdh1 = $sdh2 = $shh1 = $shh2 = $smn1 = $smn2=0;
			if ($sth1 == 'PM') $sdh1 = 12;
			if ($sth2 == 'PM') $sdh2 = 12;
			if ($hh1 > '') $shh1 = (intval($hh1) + $sdh1) * 60;
			if ($mn1 > '') $shh1 = $shh1 + intval($mn1);
			$txt = $hh2.$mn2;
			if (txt == '') {
				$shh2 = $shh1;
			}
			else {
				if ($hh2 > '') $shh2 = (intval($hh2) + $sdh2) * 60; 
				if ($mn2 > '') $shh2 = $shh2 + intval($mn2);
			}
        } else {
			$sdh1 = $sdh2 = $shh1 = $shh2 = $smn1 = $smn2=0;
			$txt = '';
			if ($hh1 > '') $shh1 = intval($hh1) * 60;
			if ($mn1 > '') $shh1 = $shh1 + intval($mn1);
			$txt = $hh2.$mn2;
			if ($txt == '') {
				$shh2 = $shh1;
			}
			else {
				if ($hh2 > '') $shh2 = intval($hh2) * 60; 
				if ($mn2 > '') $shh2 = $shh2 + intval($mn2);
			}
        }
		$err_hhmn = $shh1 > $shh2 && ($add == 1 || $mod == 1) ? true : false;
		$erreur     .= $err_hhmn ? '<li>'.$admin[1592].'</li>': '';
	}
    if ($qtype == TYP_MENUICO) {
		$err_cat1 = 1 != preg_match('/^[a-zA-Z0-9_]{2,8}$/', $cat1) && ($add == 1 || $mod == 1) ? true : false;
		$erreur  .= $err_cat1 ? '<li>'.$admin[1585].$qcat.$admin[1586].'</li>': '';
	}
    if (($qtype == TYP_OPTDDMENU && $typmenu != 'DDL')) {
		$err_cat1 = 1 != preg_match('/^[0-9-]+$/', $cat1) && ($add == 1 || $mod == 1) ? true : false;
		$erreur  .= $err_cat1 ? '<li>'.$admin[1585].$qcat.$admin[1586].'</li>': '';
	}
    if ($qtype == TYP_BANNER || $qtype == TYP_RSS || $qtype == TYP_BSS) {
		$err_cat1 = 1 != preg_match('/[A-Za-z0-9.-_]/', $cat1) && ($add == 1 || $mod == 1) ? true : false;
		if ($lang[1] != '') {
			$err_cat2 = 1 != preg_match('/[A-Za-z0-9.-_]/', $cat2) && ($add == 1 || $mod == 1) ? true : false;
			$erreur  .= $err_cat2 ? '<li>'.$admin[1585].$qcat.$admin[1587].$admin[1178].'</li>': '';
		}
	}
}
if (($btitre == 1 && !in_array($qtype, array(TYP_GUESTBK, TYP_THINK, TYP_RECO))) || ($btitre == 2 && $qtype == TYP_FAQ)) { 
	if ($qtype == TYP_RSS || $qtype == TYP_BSS) {
		$err_titre1 = 1 != preg_match('/(ftp|http|https):\/\/[a-z0-9à-ö-]+(.[a-z0-9à-ö-]+)*(:[0-9]+)?(\/.*)?$/i', $titre1) && ($add == 1 || $mod == 1) ? true : false;
		$erreur    .= $err_titre1 ? '<li>'.$admin[1585].$qtitre.$admin[1587].($lang[1] != '' ? $admin[1177] : '').'</li>': '';
		if ($lang[1] != '') {
			$err_titre2 = 1 != preg_match('/(ftp|http|https):\/\/[a-z0-9à-ö-]+(.[a-z0-9à-ö-]+)*(:[0-9]+)?(\/.*)?$/i', $titre2) && ($add == 1 || $mod == 1) ? true : false;
			$erreur    .= $err_titre2 ? '<li>'.$admin[1585].$qtitre.$admin[1587].$admin[1178].'</li>': '';
		}
	} else {
        $err_titre1 = empty($titre1) && ($add == 1 || $mod == 1) ? true : false;
        $erreur    .= $err_titre1 ? '<li>'.$admin[1585].$qtitre.$admin[1586].($lang[1] != '' ? $admin[1177] : '').'</li>': '';
        if (!in_array($qtype, array(TYP_FORUM, TYP_SOCNET)) && $lang[1] != '') {
			$err_titre2 = empty($titre2) && ($add == 1 || $mod == 1) ? true : false;
			$erreur    .= $err_titre2 ? '<li>'.$admin[1585].$qtitre.$admin[1586].$admin[1178].'</li>': '';
		}
	}
}
	if (in_array($qtype, array(TYP_THINK, TYP_ART, TYP_NEWS, TYP_REACT, TYP_DNLOAD, TYP_LINKS, TYP_FAQ, TYP_GUESTBK,
			TYP_AGENDA, TYP_FORUM, TYP_BLOG, TYP_REBLOG)))
	{
		$err_contenu1 = empty($contenu1) && ($add == 1 || $mod == 1) ? true : false;
		$erreur      .= $err_contenu1 ? '<li>'.$admin[1585].$qcontenu.$admin[1586].'</li>': '';
		if ($lang[1] != '') {
			$err_contenu2 = empty($contenu2) && ($add == 1 || $mod == 1) ? true : false;
			$erreur      .= $err_contenu2 ? '<li>'.$admin[1585].$qcontenu.$admin[1586].$admin[1178].'</li>': '';
		}
	}																					
if ($bnouveau == 1 && $qtype != TYP_MENUBOX && $qtype != TYP_DDMENUBOX && $qtype != TYP_FREEBOX) {
	if ($qtype == TYP_DNLOAD) $pattern = '/[A-Za-z0-9.-_]/';
	elseif ($qtype == TYP_LINKS) $pattern = '/(ftp|http|https):\/\/[a-z0-9à-ö-]+(.[a-z0-9à-ö-]+)*(:[0-9]+)?(\/.*)?$/i';
	elseif ($qtype == TYP_OPTMENU || ($qtype == TYP_OPTDDMENU && $typmenu != 'DDL')) $pattern = '/[a-z0-9à-ö.-_\|?]/';
	elseif ($qtype != TYP_ART) $pattern = '/[A-Za-z0-9.-_]/';
    if ($qtype != TYP_ART && $qtype != TYP_BANNER) {
		$err_nouveau1 = 1 != preg_match($pattern, $nouveau1) && ($add == 1 || $mod == 1) ? true : false;
		$erreur      .= $err_nouveau1 ? '<li>'.$admin[1585].$qnouveau.$admin[1587].($lang[1] != '' ? $admin[1177] : '').'</li>': '';
		if ($lang[1] != '' && $qtype != TYP_SOCNET) {
			$err_nouveau2 = 1 != preg_match($pattern, $nouveau2) && ($add == 1 || $mod == 1) ? true : false;
			$erreur      .= $err_nouveau2 ? '<li>'.$admin[1585].$qnouveau.$admin[1587].$admin[1178].'</li>': '';
		}
	}
    if ($qtype != TYP_ART && $qtype != TYP_SOCNET) {
		if ($lang[1] != '') {
			$err_nouveau2 = 1 != preg_match($pattern, $nouveau2) && ($add == 1 || $mod == 1) ? true : false;
			$erreur      .= $err_nouveau1 ? '<li>'.$admin[1585].$qnouveau.$admin[1587].($lang[1] != '' ? $admin[1178] : '').'</li>': '';
		}
	}
    if ($qtype == TYP_BANNER) {
		$err_nouveau1 = empty($nouveau1) && ($add == 1 || $mod == 1) ? true : false;
		$erreur      .= $err_nouveau1 ? '<li>'.$admin[1585].$qnouveau.$admin[1586].($lang[1] != '' ? $admin[1177] : '').'</li>': '';
		if ($lang[1] != '') {
			$err_nouveau2 = empty($nouveau2) && ($add == 1 || $mod == 1) ? true : false;
			$erreur      .= $err_nouveau2 ? '<li>'.$admin[1585].$qnouveau.$admin[1586].$admin[1178].'</li>': '';
		}
	}
}
	if (in_array($qtype, array(TYP_THINK, TYP_ART, TYP_NEWS, TYP_REACT, TYP_DNLOAD, TYP_LINKS, TYP_FAQ, TYP_GUESTBK,
			TYP_AGENDA, TYP_FORUM, TYP_BLOG, TYP_REBLOG)))
	{
		$err_contenu1 = empty($contenu1) && ($add == 1 || $mod == 1) ? true : false;
		$erreur      .= $err_contenu1 ? '<li>'.$admin[1585].$qcontenu.$admin[1586].'</li>': '';
		if ($lang[1] != '') {
			$err_contenu2 = empty($contenu2) && ($add == 1 || $mod == 1) ? true : false;
			$erreur      .= $err_contenu2 ? '<li>'.$admin[1585].$qcontenu.$admin[1586].$admin[1178].'</li>': '';
		}
	}
if ($bnouveau == 1 && $qtype == TYP_FREEBOX) {
    $pattern      = '/[0-9]/';
    $fieldweb     = empty($fieldweb) ? 0 : $fieldweb;
	$err_fieldweb = 1 != preg_match($pattern, $fieldweb) && ($add == 1 || $mod == 1) ? true : false;
	$erreur      .= $err_fieldweb ? '<li>'.$admin[1585].$admin[1537].$admin[1587].'</li>': '';
}
if ($bnouveau == 1 && $qtype == TYP_ART) {
    $pattern      = '/[0-9]/';
    $highart      = empty($highart) ? '0' : $highart;
	$err_highart  = 1 != preg_match($pattern, $highart) && ($add == 1 || $mod == 1) ? true : false;
	$erreur      .= $err_highart ? '<li>'.$admin[1585].$admin[1537].$admin[1587].'</li>': '';
}
if ($add == 1 && !empty($erreur)) $form = 1;
if ($mod == 1 && !empty($erreur)) $form = 2;

function TimeInterval($h1, $m1, $s1, $h2, $m2, $s2) {
    global $site;
    if ($m1 < 0 || $m1 > 60) $m1 = 0;
    if ($m2 < 0 || $m2 > 60) $m2 = 0;
    if ($site[22] == 'H3') {
        if ($h1 < 0 || $h1 > 12) {
            $h1 = 0;
        }
        if ($h2 < 0 || $h2 > 12) {
            $h2 = 0;
        }
    } else {
        if ($h1 < 0 || $h1 > 24) {
            $h1 = 0;
        }
        if ($h2 < 0 || $h2 > 24) {
            $h2 = 0;
        }
        $s1 = $s2 = '';
    }
    if ($h1 == 0 && $m1 == 0) {
        $d1 = '';
    } else {
        $d1 = sprintf('%02d:%02d', $h1, $m1).$s1;
    }
    if ($h2 == 0 && $m2 == 0) {
        $d2 = '';
    } else {
        $d2 = sprintf('%02d:%02d', $h2, $m2).$s2;
    }
    return $d1.'-'.$d2;
}

function UpdateIndexMenuOption($typ, $art_id, $opt_id, $mnu_id, $nom1, $nom2) {
	$pos  = strpos($nom1, '-->');
	$nom1 = substr($nom1, ($pos !== false ? $pos + 3 : 0));
	$pos  = strpos($nom2, '-->');
	$nom2 = substr($nom2, ($pos !== false ? $pos + 3 : 0));
	
	$aroms  = ReadDBFields(DBARTMENU);
	$trouve = false;
	$nb     = count($aroms);
	if ($typ == TYP_MENUBOX) {
		for ($n = 0; $n < $nb; $n++) {
			if ($aroms[$n][2] == $mnu_id) {
				$aroms[$n][3] = $nom1;
				$aroms[$n][4] = $nom2;
			}
		}
		$trouve = true;
	} 
	else {
		for ($n = 0; $n < $nb; $n++) {
			if ($aroms[$n][1] == $opt_id) {
				$mnu = SelectDBFieldsByField(ReadDBFields(DBMENUBOX), 4, $mnu_id);
				$pos = strpos($mnu[0][2], '-->');
				$aroms[$n][3] = substr($mnu[0][2], ($pos !== false ? $pos + 3 : 0));
				$pos = strpos($mnu[0][3], '-->');
				$aroms[$n][4] = substr($mnu[0][3], ($pos !== false ? $pos + 3 : 0));
				$aroms[$n][0] = $art_id;
				$aroms[$n][2] = $mnu_id;
				$aroms[$n][5] = $nom1;
				$aroms[$n][6] = $nom2;
				$trouve = true;
				break;
			}
		}
	}
	if (!$trouve) {
		$mnu  = SelectDBFieldsByField(ReadDBFields(DBMENUBOX), 4, $mnu_id);
		$mnus2 = $mnus3 = '';
		if (strlen($mnu[0][2]) > 0)
		{
			$pos  = strpos($mnu[0][2], '-->');
			$mnu2 = substr($mnu[0][2], ($pos !== false ? $pos + 3 : 0));
			$pos  = strpos($mnu[0][3], '-->');
			$mnu3 = substr($mnu[0][3], ($pos !== false ? $pos + 3 : 0));
			$aroms[] = array($art_id, $opt_id, $mnu_id, $mnu2, $mnu3, $nom1, $nom2);
		}		
	}
	WriteDBFields(DBARTMENU, $aroms);
}

$keywords1 = $keywords2 = '';
if ($qtype == TYP_ART) {
	if ($form == 2) {
		$dbks = ReadDBFields(DBKEYWORDS);
		foreach ($dbks as $dbk) {
			if ($id == $dbk[0]) {
				$keywords1 = $dbk[3];
				$keywords2 = $dbk[4];
				break;
			}
		}
	}
}

if ($qtype == TYP_FORUM) {
    $dbcat = ReadDBFields(DBFORUMCAT);
    $ilng = ($lng == $lang[0] ? 1 : 2);
    for ($i = 0; $i < count($dbcat); $i++) {
        $dbfcat[$i][0] = $dbcat[$i][0];
        $dbfcat[$i][1] = $dbcat[$i][$ilng];
    }
    unset($dbcat);
}
require CHEMIN.'admin/editors/editors_functions.php';
$contenu1 = PathAbsoluteRelative($contenu1);
$contenu2 = PathAbsoluteRelative($contenu2);

$canduplicate = array('art', 'news', 'blog', 'dnload', 'links', 'faq', 'nwl', 'agenda', 'freebox', 'optmenu', 'optddmenu');
$jours        = array(0 => $admin[1162], $admin[1163], $admin[1164], $admin[1165], $admin[1166], $admin[1167], $admin[1168]);

if ($act == 'fast') {
    $dbids = explode(' ', trim(import('ids')));
    $data  = array();
    for ($ij = 0; $ij < count($dbids); $ij++) {
        $ii = $dbids[$ij];
        if (isset($_POST['a_'.$ii]) && $_POST['a_'.$ii] == 'on') {
            ActionOnFields('act', array($qtype, $ii, 'a'), FALSE);
        }
        elseif (isset($_POST['i_'.$ii]) && $_POST['i_'.$ii] == 'on') {
            ActionOnFields('act', array($qtype, $ii, 'i'), FALSE);
            if ($qtype == TYP_ART | $qtype == TYP_BLOG) DeactivateInCascade($qtype, $ii);
        }
        elseif (isset($_POST['d_'.$ii]) && $_POST['d_'.$ii] == 'on') {
            ActionOnFields('del', array($qtype, $ii), FALSE);
			if (in_array($qtype, array(TYP_ART, TYP_MENUBOX, TYP_OPTMENU))) {
				$idx   = $qtype == TYP_ART || $qtype == TYP_PHOTO ? 0 : ($qtype == TYP_MENUBOX ? 2 : 1);
				$aroms = ReadDBFields(DBARTMENU);
				$dbres = array();
				foreach ($aroms as $arom) 
					if ($arom[$idx] != $ii) $dbres[] = $arom;
				WriteDBFields(DBARTMENU, $dbres);
				unset($aroms, $dbres);
			}
        }
        UpdateSitemaps($qtype);
    }
    unset($dbids);
    ActionOnFields('reindex', array($qtype, 0));
    if ($site[7] == 'on' || PHP700) {
        @ob_flush();
        sleep(PHP700 ? 3 : $site[8]);
		echo BeginJavascript().'window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg='.$pg.'&tri='.$tri.'&idpg='.$idpg.'&form=0'.($qtype == TYP_OPTMENU ? '&menubox='.$menubox :'').($qtype == TYP_OPTDDMENU ? '&ddmenubox='.$ddmenubox :'').'"'.EndJavascript();
    }
}
elseif ($act == 'a' || $act == 'i') {
    ActionOnFields('act', array($qtype, $id, $act));
}
elseif (!empty($del)) {
    ActionOnFields('del', array($qtype, $del, 'd'));
}
elseif ($add == 1 && empty($erreur)) {
    $data[0] = $qtype;
    $data[1] = '';
    $data[2] = $droitaccegroupredac == 'off' ? ($status == 'on' ? 'a' : 'i'): 'i';
    $data[3] = ($datedeb.$datefin == '') ? '' : $datedeb.'-'.$datefin;
    $data[4] = '';
    $data[5] = ($bname == 0 ? $myname : $pseudo1).(!empty($grpcol) ? '|'.$grpcol : '');
    $data[6] = $bemail == 0 ? $myemail : $email1;
    if ($qtype == TYP_FREEBOX || $qtype == TYP_MENUBOX || $qtype == TYP_DDMENUBOX) {
        $cat1 = isset($_POST['valid']) ? implode('', array_keys($_POST['valid'])) : '';
        if (empty($cat1)) {
            $cat1 = 'TLAEURB';
        }
    }
    $data[7] = ($qtype == TYP_BRUB ? $titre1 : $cat1);
    if ($qtype == TYP_AGENDA) {
        $cat2 = TimeInterval($hh1, $mn1, $sth1, $hh2, $mn2, $sth2);
    }
    $data[8]  = ($qtype == TYP_BRUB ? $titre2 : $cat2);
    $data[9]  = $titre1;
    $data[10] = $titre2;
    $data[11] = $qtype == TYP_OPTMENU || ($qtype == TYP_OPTDDMENU && $typmenu != 'DDL') || 
                $qtype == TYP_MENUICO || $qtype == TYP_SOCNET || $qtype == TYP_DDMENUBOX ? $contenu1 : putBR($contenu1);
    $data[12] = $qtype == TYP_OPTMENU || $qtype == TYP_MENUICO || $qtype == TYP_SOCNET || ($qtype == TYP_OPTDDMENU && $typmenu != 'DDL') ? $contenu2 : putBR($contenu2);
    $ancre    = $ancre == '' ? '' : '#'.$ancre;
    $slng     = (strpos($nouveau1, '?lng=') > 0 || strpos($nouveau1, '&lng=') > 0 || $site['URLR'] == 'on') ? '' : 'lng='.$lang[0];
    if ($slng != '') $slng .= (strpos($nouveau1, '?') > 0) ? '&'.$slng : '?'.$slng;
    $data[13] = $nouveau1.($qtype == TYP_OPTMENU ? (1 != preg_match('!http:|https:|&tconfig=!', $nouveau1) ? $slng.'&tconfig='.$fieldmail : '').$ancre : '');
    $slng     = (strpos($nouveau2, '?lng=') > 0 || strpos($nouveau2, '&lng=') > 0 || $site['URLR'] == 'on') ? '' : 'lng='.$lang[1];
    if ($slng != '') $slng .= (strpos($nouveau2, '?') > 0) ? '&'.$slng : '?'.$slng;
    $data[14] = $nouveau2.($qtype == TYP_OPTMENU ? (1 != preg_match('!http:|https:|&tconfig=!', $nouveau1) ? $slng.'&tconfig='.$fieldmail : '').$ancre : '');
	if ($qtype == TYP_OPTMENU) $fieldweb = $menubox;
	if ($qtype == TYP_OPTDDMENU) $fieldweb = $ddmenubox;
    $data[15] = $fieldweb;
	$tconf    = $qtype == TYP_ART ? $tconfart : $fieldmail;
    $data[16] = $qtype == TYP_ART ? $tconfart.'|'.$highart.'|'.$okreact : $fieldmail;
    $data[17] = $group;
    if ($qtype == TYP_AGENDA && $cbrpt == 'on' && $typrpt != '' && $torpt != '') {
        $d     = intval($day);
        $m     = intval($mois);
        $a     = intval($annee);
        $today = date('Ymd', mktime(0, 0, 0, $m, $d, $a));
        $dbto  = explode('-', $torpt);
        if (count($dbto) != 3) {
            $torpt = $today;
        } else {
            $at    = intval($dbto[0]);
            $mt    = intval($dbto[1]);
            $dt    = intval($dbto[2]);
            $torpt = substr('0000'.strval($at), -4).substr('00'.strval($mt), -2).substr('00'.strval($dt), -2);
        }
        if ($torpt > $today) {
            $id = ActionOnFields('add', $data);
            if ($typrpt == '0' && ($drpt % 7) == 0) { $typrpt = '1'; $wrpt = strval(intval($drpt / 7)); }
			@set_time_limit(0);
            do {
                $nbj = intval(date('t', mktime(0, 0, 0, $m, $d, $a)));
                switch ($typrpt) {
                case "0" :
                case "1" :
                    $t        = $typrpt == "0" ? intval($drpt) : intval($wrpt * 7); 
                    $d        = $d + $t;
                    $datenext = date('Ymd', mktime(0, 0, 0, $m, $d, $a));
                    break;
                case "2" :
                    $m        = $m + intval($mrpt);
                    $t        = intval(date('t', mktime(0, 0, 0, $m, $d, $a)));
                    $datenext = intval(date('Ymd', mktime(0, 0, 0, $m, $d, $a)));
                    break;
                case "3" :
                    $m++;
                    if ($m > 12) { $m = 1; $a++; }
                    $t  = intval(date('t', mktime(0, 0, 0, $m, $d, $a)));
                    $nd = 0;
                    for ($i = 1; $i <= $t; $i++) {
                        $dj       = date('w', mktime(0, 0, 0, $m, $i, $a));
                        $datenext = date('Ymd', mktime(0, 0, 0, $m, $i, $a));
                        if ($dj == $dayrpt) {
                            $nd++;
                            if ($nd == $nodayrpt) break;
                        }
                    }
                    break;
                }
                if (intval($datenext) > intval($torpt)) break;
                $data[7] = $datenext."0000";
                $data[9] = substr($datenext, 4, 2).substr($datenext, 0, 4);
                $id = ActionOnFields('add', $data);
            } while (intval($datenext) <= intval($torpt) - $t);
			@set_time_limit(120);
        }
    } else {
        $id   = ActionOnFields('add', $data);
		$oid  = $id;
		$ocol = $fieldweb;
		if ($qtype == TYP_OPTMENU && (strpos($nouveau1, 'articles.php') !== false || strpos($nouveau1, 'photorama2.php') !== false)) {
			$pos = strpos($nouveau1, 'pg=');
			$npg = $pos !== false ? substr($nouveau1, $pos + 3) : 0;
			$pos = strpos($npg, '&');
			$npg = $pos === false ? $npg : substr($npg, 0, $pos);
			UpdateIndexMenuOption($qtype, $npg, $oid, $fieldweb, $cat1, $cat2);
		}
        if ($qtype == TYP_ART && substr($nouveau1, 0, 3) == 'MNU') {
            $mnu_id   = substr($nouveau1, 4);
            $sconfig  = ($tconf == 'ALL' ? '' : '&tconfig='.$tconf);
            $iconf    = ($tconf == 'ALL' ? '1031' : '103');
            $sconf    = ($tconf == 'ALL' ? '' : '-'.$tconf);
			$url1     = 'articles.php?lng='.$lang[0].'&pg='.$oid.'&mnuid='.$mnu_id.$sconfig.$ancre.'||'.$lang[0].'-'.$urlrw[20].'-'.$iconf.'-'.$id.'-'.$mnu_id.$sconf.$ancre;
			$url2     = 'articles.php?lng='.$lang[1].'&pg='.$oid.'&mnuid='.$mnu_id.$sconfig.$ancre.'||'.$lang[1].'-'.$urlrw[20].'-'.$iconf.'-'.$id.'-'.$mnu_id.$sconf.$ancre;
            $data[0]  = TYP_OPTMENU;
			$data[3]  = ($datedeb.$datefin == '') ? '' : $datedeb.'-'.$datefin;
            $data[11] = '_SELF';
            $data[12] = $ocol;
            $data[13] = $url1;
            $data[14] = $url2;
            $data[15] = $mnu_id;
            $data[16] = $tconf;
            $id = ActionOnFields('add', $data);
			UpdateIndexMenuOption($qtype, $oid, $id, $mnu_id, $cat1, $cat2);
        }
		$id = $oid;
		if ($qtype == TYP_ART) {
			$keywords1 = import('keywords1');
			$keywords2 = import('keywords2');
			UpdateKeywords($id, $titre1, $titre2, $keywords1, $keywords2, $fieldmail);
		}
    }
    UpdateSitemaps($qtype);
    if ($dup == 2 || $cbrpt == 'on')
        $form = 2;
    else
		$form = import('retform') == 'on' || import('retform2') == 'on' ? 0 : 2;
	$dup = 0;
    if ($site[7] == 'on' || PHP700) {
        @ob_flush();
        sleep(PHP700 ? 3 : $site[8]);
		echo BeginJavascript().'window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg='.$pg.'&tri='.$tri.'&idpg='.$idpg.'&form='.$form.($qtype == TYP_OPTMENU ? '&menubox='.$menubox :'').($qtype == TYP_OPTDDMENU ? '&ddmenubox='.$ddmenubox :'').'&dup='.$dup.'&id='.$id.'"'.EndJavascript();
    }
}
elseif ($mod == 1 && empty($erreur)) {
    $data[0] = $qtype;
    $data[1] = $id;
    $data[2] = '';
    $data[3] = (($datecrea == $datedeb.'-'.$datefin) ? $datecrea : ($datedeb.$datefin == '' ? '' : $datedeb.'-'.$datefin));
    $data[4] = $majfile == 'on' || $majfile2 == 'on' ? ($datemod > $datedeb ? $datemod : $datedeb) : GetCurrentDateTime();
    $data[5] = ($bname == 0 ? $myname : $pseudo1).(!empty($grpcol) ? '|'.$grpcol : '');
    $data[6] = $bemail == 0 ? $myemail : $email1;
    if ($qtype == TYP_FREEBOX || $qtype == TYP_MENUBOX || $qtype == TYP_DDMENUBOX) {
        $xcat1 = empty($_POST['valid']) ? array() : $_POST['valid'];
        $cat1 = implode('', array_keys($xcat1));
        if (empty($cat1)) $cat1 = 'TLAEURB';
    }
    $data[7] = ($qtype == TYP_BRUB ? $titre1 : $cat1);
    if ($qtype == TYP_AGENDA) {
        $cat2 = TimeInterval($hh1, $mn1, $sth1, $hh2, $mn2, $sth2);
    }
    $data[8]  = ($qtype == TYP_BRUB ? $titre2 : $cat2);
    $data[9]  = $titre1;
    $data[10] = $titre2;
    $data[11] = $qtype == TYP_OPTMENU || ($qtype == TYP_OPTDDMENU && $typmenu != 'DDL') || 
                $qtype == TYP_MENUICO || $qtype == TYP_SOCNET || $qtype == TYP_DDMENUBOX ? $contenu1 : putBR($contenu1);
    $data[12] = $qtype == TYP_MENUICO || $qtype == TYP_SOCNET || ($qtype == TYP_OPTDDMENU && $typmenu != 'DDL') ? $contenu2 : putBR($contenu2);
    $ancre    = empty($ancre) ? '' : '#'.$ancre;
    $is_art   = strpos($nouveau1, 'articles.php') !== false;
    $is_photo = strpos($nouveau1, 'photorama2.php') !== false;
	if ($qtype == TYP_OPTMENU && ($is_art || $is_photo)) {
		$pos = strpos($nouveau1, 'pg=');
		$npg = $pos !== false ? substr($nouveau1, $pos + 3) : 0;
		$pos = strpos($npg, '&');
		$npg = $pos === false ? $npg : substr($npg, 0, $pos);
		if ($is_art) $nouveau1 = 'articles.php?lng='.$lang[0].'&pg='.$npg.'&mnuid='.$menubox.'&tconfig='.$fieldmail.$ancre.'||'.$lang[0].'-'.$urlrw[20].'-103-'.$npg.'-'.$menubox.'-'.$fieldmail;
		if ($is_photo) $nouveau1 = 'photorama2.php?lng='.$lang[0].'&pg='.$npg.'&mnuid='.$menubox.'&tconfig='.$fieldmail.$ancre.'||'.$lang[0].'-'.$urlrw[9].'-30-'.$npg.'-'.$menubox.'-'.$fieldmail;
		$art_id = $npg;
	}
    $mnu_id   = substr($nouveau1, 4);
    $data[13] = $nouveau1.($qtype == TYP_OPTMENU && 1 != preg_match('!http:|https:|&tconfig=!', $nouveau1) ? '&tconfig='.$fieldmail : '');
    $is_art   = strpos($nouveau2, 'articles.php') !== false;
    $is_photo = strpos($nouveau2, 'photorama2.php') !== false;
	if ($qtype == TYP_OPTMENU && ($is_art || $is_photo)) {
		$pos = strpos($nouveau2, 'pg=');
		$npg = $pos !== false ? substr($nouveau2, $pos + 3) : 0;
		$pos = strpos($npg, '&');
		$npg = $pos === false ? $npg : substr($npg, 0, $pos);
		if ($is_art) $nouveau2 = 'articles.php?lng='.$lang[1].'&pg='.$npg.'&mnuid='.$menubox.'&tconfig='.$fieldmail.$ancre.'||'.$lang[1].'-'.$urlrw[20].'-103-'.$npg.'-'.$menubox.'-'.$fieldmail;
		if ($is_photo) $nouveau2 = 'photorama2.php?lng='.$lang[1].'&pg='.$npg.'&mnuid='.$menubox.'&tconfig='.$fieldmail.$ancre.'||'.$lang[1].'-'.$urlrw[9].'-30-'.$npg.'-'.$menubox.'-'.$fieldmail;
	}
    $data[14] = $nouveau2.($qtype == TYP_OPTMENU && 1 != preg_match('!http:|https:|&tconfig=!', $nouveau2) ? '&tconfig='.$fieldmail : '');
	if ($qtype == TYP_OPTMENU) $fieldweb = $menubox;
	if ($qtype == TYP_OPTDDMENU) $fieldweb = $ddmenubox;
    $data[15] = $fieldweb;
	$tconf    = $qtype == TYP_ART ? $tconfart : $fieldmail;
    $data[16] = $qtype == TYP_ART ? $tconfart.'|'.$highart.'|'.$okreact : $fieldmail;
    $data[17] = $fieldmod; 
    if ($qtype == TYP_ART) {
		$idart = $id;
		$grp   = $fieldmod;
        ReadDoc($id);
        $old_mnu = $fieldd1;				
    }
    ActionOnFields('mod', $data);
	if ($qtype == TYP_OPTMENU && (strpos($nouveau1, 'articles.php') !== false || strpos($nouveau1, 'photorama2.php') !== false)) {
		UpdateIndexMenuOption($qtype, $art_id, $id, $menubox, $cat1, $cat2);
	}
	if ($qtype == TYP_MENUBOX) {
		UpdateIndexMenuOption($qtype, 0, 0, $id, $titre1, $titre2);
	}
    if ($qtype == TYP_ART) {
		$keywords1 = import('keywords1');
		$keywords2 = import('keywords2');
		UpdateKeywords($id, $titre1, $titre2, $keywords1, $keywords2, $fieldmail);
		$oid     = $id;
		$ocol    = $fieldweb;
        if ($dup == 1 && $qtype == TYP_ART && substr($nouveau1, 0, 3) == 'MNU') {
            $mnu_id   = substr($nouveau1, 4);
            $sconfig  = ($tconf == 'ALL' ? '' : '&tconfig='.$tconf);
            $iconf    = ($tconf == 'ALL' ? '1031' : '103');
            $sconf    = ($tconf == 'ALL' ? '' : '-'.$tconf);
			$url1     = 'articles.php?lng='.$lang[0].'&pg='.$oid.'&mnuid='.$mnu_id.$sconfig.$ancre.'||'.$lang[0].'-'.$urlrw[20].'-'.$iconf.'-'.$id.'-'.$mnu_id.$sconf.$ancre;
			$url2     = 'articles.php?lng='.$lang[1].'&pg='.$oid.'&mnuid='.$mnu_id.$sconfig.$ancre.'||'.$lang[1].'-'.$urlrw[20].'-'.$iconf.'-'.$id.'-'.$mnu_id.$sconf.$ancre;
            $data[0]  = TYP_OPTMENU;
			$data[2]  = 'a';
            $data[11] = '_SELF';
            $data[12] = $ocol;
            $data[13] = $url1;
            $data[14] = $url2;
            $data[15] = $mnu_id;
            $data[16] = $tconf;
            $id       = ActionOnFields('add', $data);
			UpdateIndexMenuOption($qtype, $oid, $id, $mnu_id, $cat1, $cat2);
			$dup = 0;
        }
		if ($mod == 1 && $qtype == TYP_ART && substr($nouveau1, 0, 3) == 'MNU')
		{
			$dbw = SelectDBFieldsByField(ReadDBFields(DBARTMENU), 0, $idart);
			if (count($dbw) < 1) 
			{
				$mnu_id   = substr($nouveau1, 4);
				$sconfig  = ($tconf == 'ALL' ? '' : '&tconfig='.$tconf);
				$iconf    = ($tconf == 'ALL' ? '1031' : '103');
				$sconf    = ($tconf == 'ALL' ? '' : '-'.$tconf);
				$url1     = 'articles.php?lng='.$lang[0].'&pg='.$oid.'&mnuid='.$mnu_id.$sconfig.$ancre.'||'.$lang[0].'-'.$urlrw[20].'-'.$iconf.'-'.$id.'-'.$mnu_id.$sconf.$ancre;
				$url2     = 'articles.php?lng='.$lang[1].'&pg='.$oid.'&mnuid='.$mnu_id.$sconfig.$ancre.'||'.$lang[1].'-'.$urlrw[20].'-'.$iconf.'-'.$id.'-'.$mnu_id.$sconf.$ancre;
				$data[0]  = TYP_OPTMENU;
				$data[2]  = 'a';
				$data[3]  = ($datedeb.$datefin == '') ? '' : $datedeb.'-'.$datefin;
				$data[11] = '_SELF';
				$data[12] = $ocol;
				$data[13] = $url1;
				$data[14] = $url2;
				$data[15] = $mnu_id;
				$data[16] = $tconf;
				$id = ActionOnFields('add', $data);
				UpdateIndexMenuOption($qtype, $oid, $id, $mnu_id, $cat1, $cat2);
	
			}
		}
		$id = $oid;
    }
    if ($qtype == TYP_RULES) {
        ReadDoc($id);
        $fieldc1 = addslashes(stripslashes($fieldc1));
        $fieldc2 = addslashes(stripslashes($fieldc2));
        $txt = "<?php
\$rule1 = stripslashes(\"$fieldc1\");
\$rule2 = stripslashes(\"$fieldc2\");
";
        WriteFullDB(DBRULES,$txt);
    }
    $form = import('retform') == 'on' || import('retform2') == 'on' ? 0 : 2;
    if ($site[7] == 'on' || PHP700) {
        @ob_flush();
        sleep(PHP700 ? 3 : $site[8]);
		echo BeginJavascript().'window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg='.$pg.'&tri='.$tri.'&idpg='.$idpg.'&form='.$form.($qtype == TYP_OPTMENU ? '&menubox='.$menubox :'').($qtype == TYP_OPTDDMENU ? '&ddmenubox='.$ddmenubox :'').'&id='.$id.'"'.EndJavascript();
    }
}

// notification de création ou modification à la supervision
if (($add == 1 || $mod == 1) && empty($erreur)) {
	switch ($qtype) {
	case TYP_ART :
		if ($svart == 'on' || $svart2 == 'on') {
			$eSub  = stripslashes($site[($lang[0] == $lng ? 0 : 11)]).' '.($add == 1 ? $admin[1448] : $admin[1449]);
			$eMsg  = $admin[1593].$userprefs[1].' ('.$userprefs[2].')<br />';
			$eMsg .= $admin[1594].$id.' - '.$admin[1598].($lng == $lang[0] ? $titre1 : $titre2).'<br />';
			$eMsg .= "<br />".$admin[1595].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=art&form=2&id=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=art&form=2&id=".$id.'</a>';
			$maillist = GetListCollaboratorEmail(TYP_ART);
			eMailHtmlTo($eSub, $eMsg, trim(implode(' ', $maillist).' '.trim($supervision[10]).' '.$user[1]), $user[1]);
		}
		break;
	}
}

$pgreturn[TYP_AGENDA]    = 'agenda';
$pgreturn[TYP_ART]       = 'art';
$pgreturn[TYP_BANNER]    = 'banner';
$pgreturn[TYP_DNLOAD]    = 'dnload';
$pgreturn[TYP_FAQ]       = 'faq';
$pgreturn[TYP_FORUM]     = 'forum';
$pgreturn[TYP_FREEBOX]   = 'freebox';
$pgreturn[TYP_GUESTBK]   = 'guestbk';
$pgreturn[TYP_LINKS]     = 'links';
$pgreturn[TYP_NEWS]      = 'news';
$pgreturn[TYP_BLOG]      = 'blog';
$pgreturn[TYP_BRUB]      = 'brub';
$pgreturn[TYP_NWL]       = 'nwl';
$pgreturn[TYP_PHOTO]     = 'photo';
$pgreturn[TYP_REACT]     = 'react';
$pgreturn[TYP_REBLOG]    = 'reblog';
$pgreturn[TYP_RECO]      = 'reco';
$pgreturn[TYP_RULES]     = 'rules';
$pgreturn[TYP_RSS]       = 'rss';
$pgreturn[TYP_BSS]       = 'bss';
$pgreturn[TYP_THINK]     = 'think';
$pgreturn[TYP_MENUBOX]   = 'menu';
$pgreturn[TYP_OPTMENU]   = 'optmenu&amp;menubox='.$menubox;
$pgreturn[TYP_DDMENUBOX] = 'ddmenu';
$pgreturn[TYP_OPTDDMENU] = 'optddmenu&amp;ddmenubox='.$ddmenubox;
$pgreturn[TYP_MENUICO]   = 'menuico';
$pgreturn[TYP_SOCNET]    = 'socnet';

$headinc .= BeginJavascript().'
function setret2(form) {
  if (form.retform.checked) form.retform2.checked = "checked";
  if (!form.retform.checked) form.retform2.checked = "";
}
function setret(form) {
  if (form.retform2.checked) form.retform.checked = "checked";
  if (!form.retform2.checked) form.retform.checked = "";
}
function setmaj2(form) {
  if (form.majfile.checked) form.majfile2.checked = "checked";
  if (!form.majfile.checked) form.majfile2.checked = "";
}
function setmaj(form) {
  if (form.majfile2.checked) form.majfile.checked = "checked";
  if (!form.majfile2.checked) form.majfile.checked = "";
}
function setart2(form) {
  if (form.svart.checked) form.svart2.checked = "checked";
  if (!form.svart.checked) form.svart2.checked = "";
}
function setart(form) {
  if (form.svart2.checked) form.svart.checked = "checked";
  if (!form.svart2.checked) form.svart.checked = "";
}

function VerifyForm() {
    var sto    = "";
    var erreur = false;
    var lng2   = "'.$lang[1].'";
';
if ($bname == 1) {
    $headinc .= '
    if (document.adminsend.pseudo1.value == "") {
        sto += "  - '.addslashes($admin[1596]).'\n";
		document.adminsend.pseudo1.className = "errorInputText";
        erreur = true;
    } else {
        regexp = /^[-a-zA-Z0-9_]{2,'.$serviz[148].'}$/;
        if (!regexp.test(document.adminsend.pseudo1.value)) {
            sto += "  - '.addslashes($admin[1588]).'\n";
			document.adminsend.pseudo1.className = "errorInputText";
            erreur = true;
        }
    }';
}
if (!in_array($qtype, array(TYP_GUESTBK, TYP_FORUM, TYP_NWL,TYP_REACT, TYP_REBLOG, TYP_RULES, TYP_RSS, TYP_BSS, TYP_SOCNET))) {
    $headinc .= '
    if (document.adminsend.datedeb.value != "") {
		regexp = /^[0-9]{12,12}$/;
        if (!regexp.test(document.adminsend.datedeb.value)) {
            sto += "  - '.addslashes($admin[1585].$admin[1407].$admin[1587].' => '.$admin[1409]).'\n";
			document.adminsend.datedeb.className = "errorInputText";
            erreur = true;
        }
	}
    if (document.adminsend.datefin.value != "") {
		regexp = /^[0-9]{12,12}$/;
        if (!regexp.test(document.adminsend.datefin.value)) {
            sto += "  - '.addslashes($admin[1585].$admin[1408].$admin[1587].' => '.$admin[1409]).'\n";
			document.adminsend.datefin.className = "errorInputText";
            erreur = true;
        } else {
			var deb = "'.date('YmdHi').'";
			if (document.adminsend.datedeb.value != "") deb = document.adminsend.datedeb.value;
			if (document.adminsend.datefin.value <= deb) {
				sto += "  - '.addslashes($admin[1585].$admin[1408].$admin[1587].' => '.$admin[1423]).'\n";
				document.adminsend.datefin.className = "errorInputText";
				erreur = true;
			}
		}
	}';
}
if ($bemail == 1) {
    $headinc .= '
	regexp = /^[-a-z0-9!#$%&\'*+\\/=?^_`{|}~]+(\.[-a-z0-9!#$%&\'*+\\/=?^_`{|}~]+)*@(([a-z0-9à-ö]([-a-z0-9à-ö]*[a-z0-9à-ö]+)?){1,63}\.)+([a-z0-9à-ö]([-a-z0-9à-ö]*[a-z0-9à-ö]+)?){2,63}$/i;
    if (!regexp.test(document.adminsend.email1.value)) {
        sto += "  - '.addslashes($admin[544]).'\n";
		document.adminsend.email1.className = "errorInputText";
        erreur = true;
    }';
}
if ($bcat == 1) {
    if ($qtype == TYP_AGENDA) {
        if ($site[22] == 'H3') {
            $headinc .= '
    var dh1=0, dh2=0, hh1=0, hh2=0, mn1=0, mn2=0;
    if (document.adminsend.sth1.value == "PM") dh1 = 12;
    if (document.adminsend.sth2.value == "PM") dh2 = 12;
    if (document.adminsend.hh1.value > "") hh1 = (parseInt(document.adminsend.hh1.value) + dh1) * 60;
    if (document.adminsend.mn1.value > "") hh1 = hh1 + parseInt(document.adminsend.mn1.value);
    txt = document.adminsend.hh2.value + document.adminsend.mn2.value;
    if (txt == "") {
        hh2 = hh1;
    }
    else {
        if (document.adminsend.hh2.value > "") hh2 = (parseInt(document.adminsend.hh2.value) + dh2) * 60; 
        if (document.adminsend.mn2.value > "") hh2 = hh2 + parseInt(document.adminsend.mn2.value);
    }';
        } else {
            $headinc .= '
    var hh1=0, hh2=0, mn1=0, mn2=0, txt="";
    if (document.adminsend.hh1.value > "") hh1 = parseInt(document.adminsend.hh1.value) * 60;
    if (document.adminsend.mn1.value > "") hh1 = hh1 + parseInt(document.adminsend.mn1.value);
    txt = document.adminsend.hh2.value + document.adminsend.mn2.value;
    if (txt == "") {
        hh2 = hh1;
    }
    else {
        if (document.adminsend.hh2.value > "") hh2 = parseInt(document.adminsend.hh2.value) * 60; 
        if (document.adminsend.mn2.value > "") hh2 = hh2 + parseInt(document.adminsend.mn2.value);
    }';
        }
        $headinc .= '
    if (hh1 > hh2) {
        sto += "  - '.addslashes($admin[1592]).'\n";
		document.adminsend.hh1.className = "errorInputText";
		document.adminsend.mn1.className = "errorInputText";
		document.adminsend.hh2.className = "errorInputText";
		document.adminsend.mn2.className = "errorInputText";
        erreur = true;
    }';
    }
    if ($qtype == TYP_BLOG) {
        $headinc .= '
    if (document.adminsend.cat1.value == "") {
        sto += "  - '.addslashes($admin[1597].$admin[1177]).'\n";
        document.adminsend.cat1.className = "errorInputText";
        erreur = true;
    }
    if (document.adminsend.cat2.value == "") {
        sto += "  - '.addslashes($admin[1597].$admin[1178]).'\n";
        document.adminsend.cat2.className = "errorInputText";
        erreur = true;
    }';
    }
    if ($qtype == TYP_MENUICO) {
        $headinc .= '
    regexp = /^[a-zA-Z0-9_]{2,8}$/;
    if (!regexp.test(document.adminsend.cat1.value)) {
        sto += "  - '.addslashes($admin[1585].$qcat.$admin[1586]).'\n";
		document.adminsend.cat1.className = "errorInputText";
        erreur = true;
    }';
    }
    if ($qtype == TYP_OPTDDMENU && $typmenu != 'DDL') {
        $headinc .= '
    regexp = /^[-0-9]+$/;
    if (!regexp.test(document.adminsend.cat1.value)) {
        sto += "  - '.addslashes($admin[1585].$qcat.$admin[1587]).'\n";
		document.adminsend.cat1.className = "errorInputText";
        erreur = true;
    }';
    }
    if ($qtype == TYP_BANNER) {
        $headinc .= '
    regexp = /[-A-Za-z0-9._]/;
    if (!regexp.test(document.adminsend.cat1.value)) {
        sto += "  - '.addslashes($admin[1585].$qcat.$admin[1587].($lang[1] != '' ? $admin[1177] : '')).'\n";
		document.adminsend.cat1.className = "errorInputText";
        erreur = true;
    }
    if (lng2 > "") {
        if (!regexp.test(document.adminsend.cat2.value)) {
            sto += "  - '.addslashes($admin[1585].$qcat.$admin[1587].$admin[1178]).'\n";
			document.adminsend.cat2.className = "errorInputText";
            erreur = true;
        }
    }';
    }
    if ($qtype == TYP_RSS || $qtype == TYP_BSS) {
        $headinc .= '
    var ct1 = document.adminsend.cat1.value;
    if (ct1.length < 3) {
        sto += "  - '.addslashes($admin[1585].$qcat.$admin[1587].($lang[1] != '' ? $admin[1177] : '')).'\n";
		document.adminsend.cat1.className = "errorInputText";
        erreur = true;
    }
    if (lng2 > "") {
        var ct2 = document.adminsend.cat2.value;
        if (ct2.length < 3) {
            sto += "  - '.addslashes($admin[1585].$qcat.$admin[1586].$admin[1178]).'\n";
			document.adminsend.cat2.className = "errorInputText";
            erreur = true;
        }
    }';
    }
}
if ($qtype == TYP_ART || $qtype == TYP_OPTMENU) {
    $headinc .= '
    var acr = document.adminsend.ancre.value;
    if (ancr > "") {
        regexp = /[-A-Za-z0-9._]/;
        if (!regexp.test(document.adminsend.ancre.value)) {
            sto += "  - '.addslashes($admin[1585].$admin[1534].$admin[1587]).'\n";
            document.adminsend.ancre.className = "errorInputText";
            erreur = true;
        }
    }';
}
if (($btitre == 1 && !in_array($qtype, array(TYP_GUESTBK, TYP_THINK, TYP_RECO))) || ($btitre == 2 && $qtype == TYP_FAQ)) { 
    if ($qtype == TYP_RSS || $qtype == TYP_BSS) {
        $headinc .= '
	regexp = /(ftp|http|https):\/\/[a-z0-9à-ö-]+(.[a-z0-9à-ö-]+)*(:[0-9]+)?(\/.*)?$/i;
    if (!regexp.test(document.adminsend.titre1.value)) {
        sto += "  - '.addslashes($admin[1585].$qtitre.$admin[1587].($lang[1] != '' ? $admin[1177] : '')).'\n";
		document.adminsend.titre1.className = "errorInputText";
        erreur = true;
    }
    if (lng2 > "" && (document.adminsend.titre2.value != "") && (document.adminsend.titre2.value != "http://")) { 
        if (!regexp.test(document.adminsend.titre2.value)) {
            sto += "  - '.addslashes($admin[1585].$qtitre.$admin[1587].$admin[1178]).'\n";
			document.adminsend.titre2.className = "errorInputText";
            erreur = true;
        }
    }';
    } else {
        $headinc .= '
    var tit1 = document.adminsend.titre1.value;
    if (tit1.length < 1) {
        sto += "  - '.addslashes($admin[1585].$qtitre.$admin[1586].($lang[1] != '' ? $admin[1177] : '')).'\n";
		document.adminsend.titre1.className = "errorInputText";
        erreur = true;
    }';
        if (!in_array($qtype, array(TYP_FORUM, TYP_SOCNET))) {
            $headinc .= '
    if (lng2 > "") {
        var tit2 = document.adminsend.titre2.value;
        if (tit2.length < 1) {
            sto += "  - '.addslashes($admin[1585].$qtitre.$admin[1586].$admin[1178]).'\n";
			document.adminsend.titre2.className = "errorInputText";
            erreur = true;
        }
    }';
        }
    }
}
if ($bnouveau == 1 && $qtype != TYP_MENUBOX && $qtype != TYP_DDMENUBOX && $qtype != TYP_FREEBOX) {
    if ($qtype == TYP_DNLOAD)
        $headinc .= '
    regexp = /[A-Za-z0-9.-_]/;';
    elseif ($qtype == TYP_LINKS)
        $headinc .= '
	regexp = /(ftp|http|https):\/\/[a-z0-9à-ö-]+(.[a-z0-9à-ö-]+)*(:[0-9]+)?(\/.*)?$/i;';
    elseif ($qtype == TYP_OPTMENU || $qtype == TYP_OPTDDMENU)
        $headinc .= '
    regexp = /[a-z0-9à-ö.-_\|?]/;';
    elseif ($qtype != TYP_ART)
        $headinc .= '
    regexp = /[A-Za-z0-9.-_]/;';
    if ($qtype != TYP_ART && $qtype != TYP_BANNER)
        $headinc .= '
    if (!regexp.test(document.adminsend.nouveau1.value)) {
        sto += "  - '.addslashes($admin[1585].$qnouveau.$admin[1587].($lang[1] != '' ? $admin[1177] : '')).'\n";
		document.adminsend.nouveau1.className = "errorInputText";
        erreur = true;
    }';
    if ($qtype != TYP_ART && $qtype != TYP_SOCNET)
		$headinc .= '
    if (lng2 > "") {
        if (!regexp.test(document.adminsend.nouveau2.value)) {
            sto += "  - '.addslashes($admin[1585].$qnouveau.$admin[1587].$admin[1178]).'\n";
			document.adminsend.nouveau2.className = "errorInputText";
            erreur = true;
        }
    }';
    if ($qtype == TYP_BANNER) {
        $headinc .= '
    var nouv1 = document.adminsend.nouveau1.value;
    if (nouv1.length < 3) {
        sto += "  - '.addslashes($admin[1585].$qnouveau.$admin[1586].($lang[1] != '' ? $admin[1177] : '')).'\n";
		document.adminsend.nouveau1.className = "errorInputText";
        erreur = true;
    }
    if (lng2 > "") {
        var nouv2 = document.adminsend.nouveau2.value;
        if (nouv2.length < 3) {
            sto += "  - '.addslashes($admin[1585].$qnouveau.$admin[1586].$admin[1178]).'\n";
			document.adminsend.nouveau2.className = "errorInputText";
            erreur = true;
        }
    }';
	}
}
if ($bnouveau == 1 && $qtype == TYP_FREEBOX) {
    $headinc .= '
    regexp = /[0-9]/;
    fieldweb = document.adminsend.fieldweb.value;
    if (fieldweb > "") {
        if (!regexp.test(fieldweb)) {
            sto += "  - '.addslashes($admin[1585].$admin[1537].$admin[1587]).'\n";
            document.adminsend.fieldweb.className = "errorInputText";
            erreur = true;
        }
    }';
}
if ($bnouveau == 1 && $qtype == TYP_ART) {
    $headinc .= '
    regexp = /[0-9]/;
    highart = document.adminsend.highart.value;
    if (highart > "") {
        if (!regexp.test(highart)) {
            sto += "  - '.addslashes($admin[1585].$admin[1537].$admin[1587]).'\n";
            document.adminsend.highart.className = "errorInputText";
            erreur = true;
        }
    }';
}
$headinc .= '
    if (erreur == true) {
        sto = "'.addslashes($admin[1589]).'\n\n" + sto;
        alert(sto);
        return false;
    }
}'.EndJavascript();

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $qtitle);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
$langue0 = $lang[0];
$langue1 = $lang[1];
if (in_array($qtype, array(TYP_ART, TYP_BLOG, TYP_FAQ, TYP_DNLOAD, TYP_LINKS, TYP_OPTMENU, TYP_OPTDDMENU, TYP_PHOTO))) {
    echo BeginJavascript().'
function set(form) {
  form.cat1.value = form.list1.value;
}
function set2(form) {
  selindex = form.list1.selectedIndex;
  form.cat1.value = form.list1.value;
  form.list2.selectedIndex = selindex;
  form.cat2.value = form.list2.value;
}
function set3(form) {
  selindex = form.list2.selectedIndex;
  form.cat2.value = form.list2.value;
  form.list1.selectedIndex = selindex;
  form.cat1.value = form.list1.value;
}
function reset(form) {
    form.list1.selectedIndex = 0;
}
function reset2(form) {
    form.list1.selectedIndex = 0;
    form.list2.selectedIndex = 0;
}
    '.EndJavascript();
}

$mem_lang = $lang;
if (in_array($qtype, array(TYP_GUESTBK, TYP_FORUM, TYP_REACT, TYP_REBLOG))) {
    $flag1 = '<i class="flg-lang align-middle" style="width:30px;height:20px;"></i>';
    $lang[0] = 'lang';
    $lang[1] = '';
}
else $lang = $mem_lang;
    
htable($qtitle, '100%');
if ($qformonly == 2) {
    $form = 2;
}
echo '
<div style="text-align:center;">';
if ($qintro != '') {
    echo $qintro.'
  <br />
  <hr />';
}
if ($qseeall != '') {
    if ($pgreturn[$qtype] == 'nwl' && empty($form))
        echo '
<form name="sellist" method="POST" action="admin.php?lng='.$lng.'&amp;pg=nwl&amp;action=news">
  <div style="text-align:center;">'.SubmitButton($admin[1517]).'</div>
</form>
<hr />';
    if ($qtype == TYP_OPTMENU) {
       echo '
  <a href="admin.php?lng='.$lng.'&amp;pg=menubox&amp;act=1">'.$admin[1235].'</a><br />';
    }
    if ($qtype == TYP_OPTDDMENU) {
       echo '
  <a href="admin.php?lng='.$lng.'&amp;pg=menubox&amp;act=2">'.$admin[1235].'</a><br />';
    }
    echo '
  <a href="admin.php?lng='.$lng.'&amp;pg='.$pgreturn[$qtype].'&amp;tri='.$tri.'">'.$qseeall.'</a> ';
    if (in_array($pg, $canduplicate) && $form == 2) {
        if ($qtype == TYP_AGENDA) 
            echo '||
  <a href="admin.php?lng='.$lng.'&amp;pg=agenda&amp;tri='.$tri.'&amp;form=3&amp;idpg='.$idpg.'&amp;id='.$id.'&amp;dup=2">'.$admin[1522].' </a>';
        else
            echo '||
  <a href="admin.php?lng='.$lng.'&amp;pg='.$pgreturn[$qtype].'&amp;form=2&amp;id='.$id.'&amp;dup=1">'.$admin[1522].' </a>';
    }
    if ($qwrite != '') {
        echo '||
  <a href="admin.php?lng='.$lng.'&amp;pg='. $pgreturn[$qtype].'&amp;tri='.$tri.'&amp;form='.(isset($qwriteform) ? $qwriteform : '1').'">'.$qwrite.'</a>';
    }
    echo '
  <br />
  <hr />
  ';
}

################################################################################
if (empty($form)) {
	@set_time_limit(0);
    if (empty($tri)) {
        $tri = '-id';
    }
    if ($qtype == TYP_OPTMENU) {
        $dbw    = SelectDBFieldsByNotStatus(SelectDBFieldsByType(ReadDBFields(DOCID),$qtype),'d');
        $dbwork = array();
        for ($i = 0; $i < count($dbw); $i++) {
            ReadDoc($dbw[$i][1]);
            if ($fieldweb == $menubox) {
                $dbwork[] = $dbw[$i];
            }
        }
	}
    elseif ($qtype == TYP_OPTDDMENU) {
        $dbw    = SelectDBFieldsByNotStatus(SelectDBFieldsByType(ReadDBFields(DOCID),$qtype),'d');
        $dbwork = array();
        for ($i = 0; $i < count($dbw); $i++) {
            ReadDoc($dbw[$i][1]);
            if ($fieldweb == $ddmenubox) {
                $dbwork[] = $dbw[$i];
            }
        }
    } else {
        $dbwork = SelectDBFieldsByNotStatus(SelectDBFieldsByType(ReadDBFields(DOCID),$qtype),'d');
    }	
    @rsort($dbwork);
    $tmpi   = 0;
    $tmp[0] = array();
    $tmp[1] = array();
    $tmp[2] = array();
    $tmp[3] = array();
    $tmp[4] = array();
	$tmp[5] = array();
	$tmp[6] = array();
	$tmp[7] = array();
	$tmp[8] = array();
    $mbs    = ReadDbFields(DBMENUBOX);
    $flng   = ($lng == $mem_lang[0]);
    $format_nextid = '%'.ceil(log10(ReadCounter(NEXTID))).'u';
    $format_fr     = '%'.ceil(log10(ReadCounter(DBFORUMCOUNTER))).'u';
    for ($i = 0; $i < count($dbwork); $i++) {
        ReadDoc($dbwork[$i][1]);
        $temp   = explode('|', $author);
        $author = $temp[0];
        $grpc   = isset($temp[1]) ? $temp[1] : '';
        $grps   = ReadGroupsCol();
        $access = false;
        foreach($grps as $grp=>$membrs) {
            if ($fctwri == 'redac2' && $grp == $grpc && in_array($userprefs[1], $membrs)) {
                $access = true;
                break;
            }
        }
        $access = $access || in_array($fctwri, array('admin', 'modo', 'webm')) || $author == $myname || isAccessGrantedCol($grpc) ? true : false;
        if (!$access) continue;
        $champs0 = '';
        $champs1 = '';
        $champs2 = '';
        $champs3 = '';
        $champs4 = '';
        if ($fileid != $dbwork[$i][1] || $type != $dbwork[$i][0]) {
            $fid = '*'.$dbwork[$i][1];
            $champs0 = $admin[796].': ';
            $champs1 = $admin[797];
        }
        switch ($type) {
        case TYP_GUESTBK :
            $champs1 = $admin[175].strip_tags(str_replace('<!--', '', str_replace('-->', ' ', $fielda1))).' - ';
            $champs2 = $author;
            break;
        case TYP_BANNER :
            $champs0 = $fielda1.' - ';
            $champs1 = $fieldb1.' - ';
            $champs2 = $fieldd1;
            break;
        case TYP_REACT :
            $champs0 = $admin[1599].Formatage($format_nextid, $fielda2).' : '.(empty($tmp0)? '' : ($flng ? $tmp0[0][2] : $tmp0[0][3])).' - ';
            $champs1 = $admin[1600].Formatage('%2u', $fielda1).' - ';
            $champs2 = $admin[1601].' '.$author;
            break;
        case TYP_REBLOG :
            $champs0 = $admin[1117].Formatage($format_nextid, $fielda2).' : '.(empty($tmp0)? '' : ($flng ? $tmp0[0][2] : $tmp0[0][3])).' - ';
            $champs1 = $admin[1600].Formatage('%2u', $fielda1).' - ';
            $champs2 = $admin[1601].' '.$author;
            break;
        case TYP_NEWS :
        case TYP_BLOG :
            $champs0 = ($flng ? $fielda1 : $fielda2).' - ';
            $champs1 = ($flng ? $fieldb1 : $fieldb2).' - ';
            $champs2 = $author;
            $champs4 = $fieldmod;
            break;
        case TYP_BRUB :
            $champs0 = ($flng ? $fielda1 : $fielda2);
            break;
        case TYP_MENUBOX :
            $champs1 = ($flng ? $fieldb1 : $fieldb2);
            $champs4 = $fieldmod != '' ? true : false;
            break;
        case TYP_MENUICO :
            $champs1 = ($flng ? $fieldb1 : $fieldb2);
            $champs4 = $fieldmod;
            break;
        case TYP_FREEBOX :
            $champs1 = ($flng ? $fieldb1 : $fieldb2);
            $champs4 = $fieldmod;
            break;
        case TYP_NWL     :
            $champs1 = ($flng ? $fieldb1 : $fieldb2).' ('.FormatDate(substr($moddate, 0, 8)).')';
            break;
        case TYP_SOCNET  :
            $champs1 = $fieldb1;
            $champs4 = $fieldmod;
            break;
        case TYP_DDMENUBOX :
            $champs1 = $fielda2.' - '.($flng ? $fieldb1 : $fieldb2);
            $champs4 = $fieldmod;
            break;
        case TYP_FORUM :
            if ($fielda2 == '0') {
                $champs1 = $admin[208].Formatage($format_fr, $fielda1).' + ';
                $champs2 = '<strong>'.$fieldb1.'</strong>';
            }
            else {
                $champs1 = $admin[208].Formatage($format_fr, $fielda1).' - ';
                $champs2 = $admin[209].Formatage('%2u', $fielda2);
            }
            break;
        case TYP_ART :
            $champs0 = GetArtOptMenu($fileid, $type);
            $champs2 = strip_tags($flng ? $fieldb1 : $fieldb2);
            $champs4 = $fieldmod;
            break;
        case TYP_PHOTO     :
            $champs0 = GetArtOptMenu($fileid, $type);
            $champs1 = ($flng ? $fielda1 : $fielda2).' - ';
            $champs2 = ($flng ? $fieldb1 : $fieldb2);
            $champs4 = $fieldmod;
            break;
        case TYP_OPTMENU   :
        case TYP_OPTDDMENU :
        case TYP_DNLOAD    :
        case TYP_FAQ       :
        case TYP_LINKS     :
            $champs1 = ($flng ? $fielda1 : $fielda2).' - ';
            $champs2 = ($flng ? $fieldb1 : $fieldb2);
            $champs4 = $fieldmod;
            break;
        case TYP_BSS       :
        case TYP_RSS       :
            $champs1 = ($flng ? $fielda1 : $fielda2).' - ';
            $champs2 = ($flng ? $fieldb1 : $fieldb2);
            break;
        case TYP_THINK :
            $champs1 = ($flng ? $fieldb1 : $fieldb2).' - ';
            $champs2 = strip_tags(CutLongWord(($flng ? $fieldc1 : $fieldc2), 142));
            break;
        case TYP_RECO :
            $champs1 = $fieldb1.' - ';
            $champs2 = ($flng ? $fieldc1 : $fieldc2);
            break;
        case TYP_AGENDA :
			$day     = substr($fielda1, 6, 2);
			$mois    = substr($fielda1, 4, 2);
			$annee   = substr($fielda1, 0, 4);
			$chps4   = substr(FormatDate($annee.$mois.$day."00000"), 0, 10);
            $champs1 = $fielda1.' - ';
            if ($fielda2 == '' || $fielda2 == '-') {
                $champs1 = $chps4.' - ';
            } else {
                $champs1 = $chps4.' - '.$fielda2.' - ';
            }
            $champs2 = ($flng ? $fieldc1 : $fieldc2);
			$champs2 = strip_tags(str_replace('<br />', '', str_replace('</p>', '', str_replace('<p>', '', $champs2))));
            if (strlen($champs2) > 142) {
                $champs2 = CutLongWord($champs2,142).' .../...';
            }
			$champs3 = $annee.$mois.$day;
            $champs4 = $fieldmod;
            break;
        }
		
        $tmp[0][$tmpi] = (TYP_ART == $type || TYP_PHOTO == $type ? '' : $champs0).'<strong>'.$champs1.'</strong>'.$champs2;
        $tmp[1][$tmpi] = $fileid;
        $tmp[2][$tmpi] = $status;
        $tmp[3][$tmpi] = $champs3;
		$tmp[4][$tmpi] = $fieldd1;
        $tmp[5][$tmpi] = $champs0;
		$tmp[6][$tmpi] = $moddate;
        $tmp[7][$tmpi] = $fielda2;
        $tmp[8][$tmpi] = $champs4;
        $tmpi++;
    }
    unset($dbwork);
    if ($tmpi > 0) {
        $href = CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg='.$pg.($qtype == TYP_OPTMENU ? '&amp;menubox='.$menubox : '').($qtype == TYP_OPTDDMENU ? '&amp;ddmenubox='.$ddmenubox : '');
        switch ($tri) {
        case 'id' :
        case '-id' :
            $cr = array_multisort($tmp[1], SORT_NUMERIC, $tri == 'id' ? SORT_ASC : SORT_DESC, $tmp[0], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8]);
            break;
        case 'ch' :
        case '-ch' :
			$cr = array_multisort($tmp[0], $tri == 'ch' ? SORT_ASC : SORT_DESC, $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8]);
            break;
        case 'dt' :
        case '-dt' :
            $cr = array_multisort($tmp[3], SORT_NUMERIC, $tri == 'dt' ? SORT_ASC : SORT_DESC, $tmp[0], $tmp[1], $tmp[2], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8]);
            break;
        case 'md' :
        case '-md' :
            $cr = array_multisort($tmp[6], SORT_NUMERIC, $tri == 'md' ? SORT_ASC : SORT_DESC, $tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[7], $tmp[8]);
            break;
        }
    }
    if (empty($idpg)) {
        $idpg = 1;
    }
    else {
        while ($tmpi <= ($idpg - 1)* $serviz[75]) $idpg--;
    }
	if ($tmpi > 0) {
        $navbar = GetNavBar('admin.php?lng='.$lng.'&amp;pg='.$pgreturn[$qtype].'&amp;tri='.$tri.'&amp;idpg=', $tmpi, $idpg, $serviz[75]);
        echo $navbar.'
<form name="fast_actions" id="fast_actions" action="admin.php?lng='.$lng.'&amp;idpg='.$idpg.'&amp;pg='.$pgreturn[$qtype].'&amp;tri='.$tri.'&amp;act=fast" method="post">
  <fieldset class="w-100 m-0 p-2">';
		echo '
    <div class="table-responsive-md">
    <table class="table w-100 m-auto table-bordered">
      <tr>
        <td colspan="'.($qtype == TYP_AGENDA ? '5' : '4').'">
          <div style="text-align:right; padding-right:25px; margin-bottom:4px;">'.SubmitButton($admin[1602]).'
            <i class="fas fa-level-down-alt align-middle" style="font-size: 1.4rem;"></i>
          </div>
        </td>
      </tr>';
		$idup     = $tri == 'id' 
          ? '<i class="fas fa-caret-up text-danger align-middle" style="font-size: 1.8rem;"></i>' 
          : '<i class="fas fa-caret-up align-middle" style="font-size: 1.8rem;"></i>';
		$iddown   = $tri == '-id' 
          ? '<i class="fas fa-caret-down text-danger align-middle" style="font-size: 1.8rem;"></i>' 
          : '<i class="fas fa-caret-down align-middle" style="font-size: 1.8rem;"></i>';
		$descup   = $tri == 'ch' 
          ? '<i class="fas fa-caret-up text-danger align-middle" style="font-size: 1.8rem;"></i>' 
          : '<i class="fas fa-caret-up align-middle" style="font-size: 1.8rem;"></i>';
		$descdown = $tri == '-ch' 
          ? '<i class="fas fa-caret-down text-danger align-middle" style="font-size: 1.8rem;"></i>' 
          : '<i class="fas fa-caret-down align-middle" style="font-size: 1.8rem;"></i>';
		$moddup   = $tri == 'md' 
          ? '<i class="fas fa-caret-up text-danger align-middle align-middle" style="font-size: 1.8rem;"></i>' 
          : '<i class="fas fa-caret-up align-middle" style="font-size: 1.8rem;"></i>';
		$modddown = $tri == '-md' 
          ? '<i class="fas fa-caret-down text-danger align-middle" style="font-size: 1.8rem;"></i>' 
          : '<i class="fas fa-caret-down align-middle" style="font-size: 1.8rem;"></i>';
		echo '
      <tr class="forum">
        <td style="vertical-align:middle;padding:5px;white-space:nowrap;text-align:center;width:8%;">
		  <a href="'.$href.'&amp;tri=id" title="'.$admin[674].'" style="top:-1px;left:1px;"> '.$idup.' </a>
		  '.$admin[678].'
	      <a href="'.$href.'&amp;tri=-id" title="'.$admin[675].'" style="top:-1px;left:1px;"> '.$iddown.' </a>
		</td>';
		if ($type == TYP_AGENDA) {
			$datup   = $tri == 'dt' ? '<i class="fas fa-caret-up text-danger" style="font-size: 1.8rem;"></i>': '<i class="fas fa-caret-up" style="font-size: 1.8rem;"></i>';
			$datdown = $tri == '-dt' ? '<i class="fas fa-caret-down text-danger" style="font-size: 1.8rem;"></i>': '<i class="fas fa-caret-down" style="font-size: 1.8rem;"></i>';
			echo '
        <td style="vertical-align:middle;padding:5px;white-space:nowrap;text-align:center;width:10%;">
		  <a href="'.$href.'&amp;tri=dt" title="'.$admin[1339].'" style="top:-1px;left:1px;"> '.$datup.' </a>
		  '.trim(str_replace(':', '', $admin[1603])).'
	      <a href="'.$href.'&amp;tri=-dt" title="'.$admin[1340].'" style="top:-1px;left:1px;"> '.$datdown.' </a>
		</td>';
		}
		echo '
		<td style="vertical-align:middle;padding:5px;width:'.($type == TYP_AGENDA ? 60 : 70).'%;">
		  <a href="'.$href.'&amp;tri=ch" title="'.$admin[676].'" style="top:-1px;left:1px;"> '.$descup.' </a>
		  '.$admin[679].'
	      <a href="'.$href.'&amp;tri=-ch" title="'.$admin[677].'" style="top:-1px;left:1px;"> '.$descdown.' </a>
		</td>
        <td style="vertical-align:middle;padding:5px;white-space:nowrap;text-align:left;width:15%;">
	      <a href="'.$href.'&amp;tri=md" title="'.$admin[1442].'" style="top:-1px;left:1px;"> '.$moddup.' </a>
		  '.$admin[1441].'
	      <a href="'.$href.'&amp;tri=-md" title="'.$admin[1443].'" style="top:-1px;left:1px;"> '.$modddown.' </a>
		</td>
		<td style="vertical-align:middle;padding:5px;width:7%;">'.$admin[680].'</td>
      </tr>';
        $ids = '';
        for ($i = $serviz[75]*($idpg-1); $i < Min($serviz[75]*$idpg, $tmpi); $i++) {
            $fileid  = $tmp[1][$i];
            $status  = $tmp[2][$i];
			$fieldd1 = $tmp[4][$i];
            echo '
      <tr class="'.($i %2 == 0 ? 'rep' : 'quest').'">';
            echo '
        <td style="padding:7px 5px;white-space:nowrap;text-align:center;">'.$tmp[1][$i].' </td>
        <td colspan="'.($type == TYP_AGENDA ? 3 : 2).'" style="padding:7px 5px;width:100%;text-align:left;">'.(TYP_ART == $type || TYP_PHOTO == $type ? $tmp[5][$i] : '').'
          <span style="float:left;">
            <i class="far fa-dot-circle text-'.($tmp[8][$i] == '' ? 'transparent' : 'dark').'" style="font-weight: 900;" title="'.$tmp[8][$i].'"></i>
            <a href="admin.php?lng='.$lng.'&amp;pg='.$pgreturn[$qtype].'&amp;tri='.$tri .'&amp;form=2&amp;idpg='.$idpg.'&amp;id='.$fileid.'" title="'.$admin[33].'">
              <i class="far fa-file-alt" title="'.$admin[33].'"></i> '.$tmp[0][$i].'
			</a>
          </span>';
            if (in_array($pg, $canduplicate)) {
                if ($pg == "agenda") {
                    echo '
          <span style="float:right;padding-left:4px;"> 
		    <a href="admin.php?lng='.$lng.'&amp;pg='.$pgreturn[$qtype].'&amp;tri='.$tri.'&amp;form=3&amp;idpg='.$idpg.'&amp;id='.$fileid.'&amp;dup=2" 
              title="'.$admin[915].'">
			  <i class="fas fa-paste text-info" style="font-size: 1.4rem;" title="'.$admin[915].'"></i>
			</a>
		  </span>';
                } else {
					echo '
          <span style="float:right;padding-left:4px;"> 
		    <a href="admin.php?lng='.$lng.'&amp;pg='.$pgreturn[$qtype].'&amp;tri='.$tri.'&amp;form=2&amp;idpg='.$idpg.'&amp;id='.$fileid.'&amp;dup=1" 
              title="'.$admin[915].'">
			  <i class="fas fa-paste text-info" style="font-size: 1.4rem;" title="'.$admin[915].'"></i>
			</a>
		  </span>';
                }
            }
            if (TYP_MENUBOX == $type && $fctwri != 'redac1') {
                echo '
          <span style="float:right;padding-right:4px;">
		    <a href="admin.php?lng='.$lng.'&amp;pg=mnutoddm&amp;id='.$fileid.'" title="'.$admin[1541].'">
			  <i class="fas fa-exchange-alt text-warning" style="font-size: 1.4rem;" title="'.sprintf($admin[1541], strip_tags($tmp[0][$i])).'"></i>
			</a>
		    <a href="admin.php?lng='.$lng.'&amp;pg=rencat&amp;tri='.$tri.'&amp;id='.$fileid.'" title="'.$admin[1365].'">
			  <i class="fas fa-file-alt" style="font-size: 1.4rem;" title="'.$admin[1365].'"></i>
			</a>
          </span>';
            }
            if (TYP_DDMENUBOX == $type && $fctwri != 'redac1') {
                echo '
          <span style="float:right;padding-right:4px;">';
                if (in_array($tmp[7][$i], array('DDH', 'DDV')))
                    echo '
		    <a href="admin.php?lng='.$lng.'&amp;pg=ddmtomnu&amp;id='.$fileid.'" title="'.$admin[1542].'">
			  <i class="fas fa-exchange-alt text-warning" style="font-size: 1.4rem;" title="'.sprintf($admin[1542], strip_tags($tmp[0][$i])).'"></i>
			</a>';
            }
            if (TYP_OPTMENU == $type) {
				if (strpos($fieldd1, 'articles') !== false) {
					$pgart = substr($fieldd1, strpos($fieldd1, 'pg=') + 3);
					$pgart = strpos($pgart, CONNECTOR) > 0 ? substr($pgart, 0, strpos($pgart, CONNECTOR)) : $pgart;
					$pgart = strpos($pgart, '&') > 0 ? substr($pgart, 0, strpos($pgart, '&')) : $pgart;				
					echo '
            <span style="float:right;padding-right:4px;">
		      <a href="admin.php?lng='.$lng.'&amp;pg=art&amp;tri='.$tri.'&amp;form=2&amp;idpg='.$idpg.'&amp;id='.$pgart.'" title="'.$admin[1371].'">
			    <i class="far fa-file" title="'.$admin[1371].'"></i>
			  </a>
            </span>';
				}
            }
            echo '
          <span style="clear:both"></span>
        </td>
        <td  style="padding:7px 5px;white-space:nowrap;text-align:center;">';
            if ($status == "a") {
                MakeRadioGroup(array( array("a", "i", $fileid), array("a", "d", $fileid) ));
            }
            elseif ($status == "i") {
                MakeRadioGroup(array( array("i", "a", $fileid), array("i", "d", $fileid) ));
            }
            elseif ($status == "d") {
                MakeRadioGroup(array( array("d", "s", $fileid), array("d", "i", $fileid) ));
            }
            else {
                echo '
          <a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg=dbcheck" title="'.$admin[278].'">
            <i class="adm-dbcheck"></i>
          </a>';
            }
            echo '
        </td>
      </tr>';
            $ids .= $tmp[1][$i]." ";
        }
        echo '
      <tr>
        <td colspan="4">
          <div style="text-align:right; padding-right:25px; margin-bottom:4px;">'.SubmitButton($admin[1602]).'
            <i class="fas fa-level-up-alt align-middle" style="font-size: 1.4rem;"></i>
          </div>
        </td>
      </tr>
    </table>
    </div>
    <input type="hidden" name="ids" value="'.$ids.'" />';
		if (in_array($qtype, array(TYP_FREEBOX, TYP_MENUBOX, TYP_DDMENUBOX, TYP_SOCNET, TYP_BANNER, TYP_THINK, TYP_RSS)) 
            && !in_array($fctwri, array('redac1', 'modo')))
			echo '
    <div class="float-left mt-5">'
      .(@$drtuser[5] == 'on' || in_array($fctwri, array('admin', 'webm', 'redac2')) ? '
	  <a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg=config6&amp;selskin='.$page[14].'">'.$admin[346].'
	    <i class="gen-cfgboites"></i>
	  </a>&nbsp;||&nbsp;': '')
      .(@$drtuser[2] == 'on' || in_array($fctwri, array('admin', 'webm')) ? '
	  <a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg=config3&amp;selskin='.$page[14].'&amp;conf='.$tconfig.'">'.$admin[469].'
	    <i class="gen-cfgaccueil"></i>
	  </a>' : '').'
    </div>';
		echo '
  </fieldset>
</form>';
        echo $navbar;
	}
	else {
        echo '
<p class="text-center">'.$admin[854].'</p>';
	}
}
################################################################################
elseif ($form == 1) {
	if (!empty($erreur))
		echo displayErrorMsg($admin[1589], $erreur);
	if (!empty($id)) {
        if ($qtype == TYP_OPTMENU && $typ == TYP_ART) {
            $nouveau1 = 'articles.php?lng='.$lang[0].'&pg='.$id;
            $nouveau2 = 'articles.php?lng='.$lang[1].'&pg='.$id;
        }
        if ($qtype == TYP_OPTMENU && $typ == TYP_PHOTO) {
            $nouveau1 = 'photorama2.php?lng='.$lang[0].'&pg='.$id;
            $nouveau2 = 'photorama2.php?lng='.$lang[1].'&pg='.$id;
        }
	}
    if ($qtype ==  TYP_ART) {
        $okreact = !isset($okreact) ? 'on' : $okreact;
    }
    echo '
  <style> .w-admeditor { max-width: '.$serviz[101].'; } </style>
  <form name="adminsend" id="adminsend" 
      action="admin.php?lng='.$lng.'&amp;pg='.$pgreturn[$qtype].'" 
      method="post" onsubmit="return VerifyForm(); return false;">
    <input type="hidden" name="add" value="1" />
    <input type="hidden" name="tri" value="'.$tri.'" />
    <input type="hidden" name="idpg" value="'.$idpg.'" />
    <fieldset class="w-100 m-0 p-2">';
	echo '
      <br />'.$admin[764].'
      <select class="form-control w-100 mb-3 mx-auto" style="max-width:80px;" name="status">
        <option value="on" selected="selected">'.$admin[765].'</option>
        <option value="">'.$admin[766].'</option>
      </select>';
    /// début modif accès privé
    if (in_array($qtype, array(TYP_ART, TYP_AGENDA, TYP_BLOG, TYP_DNLOAD, TYP_FAQ, TYP_LINKS, TYP_NEWS, TYP_PHOTO, 
		TYP_FREEBOX, TYP_MENUBOX, TYP_OPTMENU, TYP_DDMENUBOX, TYP_OPTDDMENU, TYP_MENUICO, TYP_SOCNET, TYP_NWL))) {
        $dbgroup = ReadDBFields($qtype == TYP_NWL ? GROUPSNWL : GROUPS);
        echo '
      '.$admin[$qtype == TYP_NWL ? 1484 : 932].' :&nbsp;
      <select class="form-control w-100 mb-3 mx-auto" name="group" style="max-width:136px;">';
        if ($qtype != TYP_NWL)
            echo '
        <option value=""> -- '.$admin[1604].' -- </option>';
        echo '
        <option value="'.($qtype == TYP_NWL ? 'all_subscribers' : 'all_members').'">'.$admin[$qtype == TYP_NWL ? 1150 : 690].'</option>';
        foreach ($dbgroup as $groupe)
            echo '
        <option value="'.$groupe[0].'">'.$groupe[0].'</option>';
        echo '
      </select>';
    }
    /// fin modif accès privé
    echo '
    <div class="table-responsive-md">
    <table class="table w-100 m-auto">';
	if (!in_array($qtype, array(TYP_GUESTBK, TYP_FORUM, TYP_NWL,TYP_REACT, TYP_BRUB, TYP_REBLOG, TYP_RULES, TYP_RSS, TYP_BSS, TYP_SOCNET))) {
		if ($fctwri != 'redac1')
			echo '
      <tr>
	    <td class="text-center">
		  <div class="mx-auto" style="width:184px;">
		    <div class="float-left pr-1">'.$admin[1406].'</div>
            <script>
              $(document).ready(function(){
                $(\'.popadmin1\').popover({
                  title:"'.$admin[1406].'",
                  content:"'.$help[14].'",
                  trigger:"hover",
                  placement:"auto",
                  html:true
                });
              });
            </script>
            <span class="overpop pl-1">
              <a class="popadmin1 float-left" title="'.$admin[1406].'">
                <i class="far fa-comment-dots"></i>
              </a>
            </span>
		  </div>
	    </td>
	  </tr>
      <tr>
	    <td class="text-center">
		  <div class="d-inline-block">'.$admin[1407].' :</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_datedeb ? 'errorInputText' : 'texte').'" type="text" name="datedeb" size="10" value="'.$datedeb.'" />
          </div>
          <div class="d-inline-block">&nbsp;&nbsp;'.$admin[1408].' :</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_datefin ? 'errorInputText' : 'texte').'" type="text" name="datefin" size="10" value="'.$datefin.'" />
          </div>
		</td>
	  </tr>';
		else
			echo '
	  <input type="hidden" name="datedeb" value="'.$datedeb.'" />
	  <input type="hidden" name="datefin" value="'.$datefin.'" />';
	}
    if ($bname == 1) {
		if ($fctwri != 'redac1') {
			echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[114].'</div>
          <div class="d-inline-block">
            <input class="form-control w-80 mx-auto '.($err_pseudo1 ? 'errorInputText' : 'texte').'" 
              type="text" name="pseudo1" size="32" value="'.$myname.'" />
          </div>
        </td>
      </tr>';
            if ((@$drtuser[12] != '' || in_array($fctwri, array('admin', 'webm', 'redac2'))) && $serviz[42] == 'on') {
                $dbgroup = ReadDBFields(GROUPSCOL);
                echo '
      <tr>
        <td class="text-center">'.$admin[1480].' 
          <select class="form-control w-100 mx-auto" style="max-width:180px;" name="grpcol">
            <option value=""'.Selected($grpcol == '').'>'.$admin[1604].'</option>
            <option value="ALL"'.Selected($grpcol == 'ALL').'>'.$admin[1481].'</option>';
                foreach ($dbgroup as $groupe)
                    echo '
            <option value="'.$groupe[0].'"'.Selected($grpcol == $groupe[0]).'>'.$groupe[0].'</option>';
                echo '            
          </select>
        </td>
      </tr>';
            } else {
                echo '
	  <input type="hidden" name="grpcol" value="'.$grpcol.'" />';
            }
		} else {
			echo '
	  <input type="hidden" name="pseudo1" value="'.$myname.'" />
	  <input type="hidden" name="grpcol" value="'.$grpcol.'" />';
        }
    }
    if ($bemail == 1) {
		if ($fctwri != 'redac1')
			echo '
      <tr>
        <td class="text-center">'.$admin[115].'&nbsp;
          <input class="form-control w-100 mx-auto '.($err_email1 ? 'errorInputText' : 'texte').'" type="text" name="email1" style="max-width:280px;" value="'.$myemail.'" />
        </td>
      </tr>';
		else
			echo '
	  <input type="hidden" name="email1" value="'.$myemail.'" />';
    }
    if ($bnouveau == 2 && $qtype == TYP_NEWS) {
		if ($fctwri != 'redac1')
			echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block"><label for="nouveau1">'.$qnouveau.'</label></div>
          <div class="d-inline-block form-check"><input class="form-check-input" type="checkbox" id="nouveau1" name="nouveau1" /></div>
        </td>
      </tr>';
		else
			echo '
	  <input type="hidden" name="nouveau1" value="" />';
    }
    if ($bcat == 1 && !in_array($qtype, array(TYP_AGENDA, TYP_FREEBOX, TYP_MENUBOX, TYP_DDMENUBOX, TYP_MENUICO, TYP_SOCNET))) {
        if ($qtype == TYP_ART || $qtype == TYP_OPTMENU || $qtype == TYP_PHOTO) {
			echo '
	  <tr>
	    <td class="text-center">
		  <div class="text-center mt-2 mx-auto" style="width:200px;">
		    <div class="float-left pr-1">'.$qcat.'</div>
            <script>
              $(document).ready(function(){
                $(\'.popadmin2\').popover({
                  title:"'.$qcat.'",
                  content:"'.$help[13].($qtype == TYP_ART ? $help[16] : '').'",
                  trigger:"hover",
                  placement:"auto",
                  html:true
                });
              });
            </script>
            <span class="overpop">
              <a class="popadmin2 float-left ml-2" title="'.$qcat.'">
                <i class="far fa-comment-dots"></i>
              </a>
            </span>
		  </div>
        </td>
	  </tr>';
		} else
			echo '
      <tr><td class="text-center">'.$qcat.'</td></tr>';
        if ($qtype == TYP_BANNER) {
            echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag1.'</div>
          <div class="d-inline-block">
            <input id="cat1" class="form-control '.($err_cat1 ? 'errorInputText' : 'texte').'" 
              name="cat1" style="vertical-align:middle;width:400px;" type="text" value="" />
          </div>
          <div class="d-inline-block">
            <input type="button" class="btn btn-info bouton" name="browserver1" id="browserver1" 
              onclick="PopupWindow(\''.CHEMIN.'admin/editors/upload/upload.php?lng='.$lng.
                '&uptype=Image&namerepconfig=guppy_config&pathconfig=admin/editors/\',\'cat1\',\'700\',\'515\', \'yes\');" 
              value="'.$admin[793].'" />
          </div>';
            if ($lang[1] != "") {
                echo '
        </td>
      </tr>
      <tr>
        <td style="text-align:center">
          <div class="d-inline-block">'.$flag2.'</div>
          <div class="d-inline-block">
            <input id="cat2" class="form-control '.($err_cat2 ? 'errorInputText' : 'texte').'" 
              name="cat2" style="vertical-align:middle;width:400px;" type="text" value="" />
          </div>
          <div class="d-inline-block">
            <input type="button" class="btn btn-info bouton" name="browserver2" id="browserver2" 
              onclick="PopupWindow(\''.CHEMIN.'admin/editors/upload/upload.php?lng='.$lng.
                '&uptype=Image&namerepconfig=guppy_config&pathconfig=admin/editors/\',\'cat2\',\'700\',\'515\', \'yes\');" 
              value="'.$admin[793].'" />
          </div>';
            }
		  echo '
        </td>
      </tr>';
        }
        elseif ($qtype == TYP_BSS || $qtype == TYP_RSS) {
            echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag1.'</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_cat1 ? 'errorInputText' : 'texte').'" type="text" name="cat1" value="" size="38" />
          </div>';
            if ($lang[1] != "") {
                echo '
        </td>
      </tr>
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag2.'</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_cat2 ? 'errorInputText' : 'texte').'" type="text" name="cat2" value="" size="38" />
          </div>';
            }
            echo '
        </td>
      </tr>';
        }
        elseif ($qtype == TYP_OPTDDMENU) {
            echo '
      <tr>
        <td class="text-center">
		  <div class="text-center m-auto" style="width:240px;">
		    <div class="float-left" style="padding-left:10px;">
              <input id="cat1" class="form-control '.($err_cat1 ? 'errorInputText' : 'texte').'" name="cat1" style="width:200px;" type="text" value="" />
		    </div>
            <script>
              $(document).ready(function(){
                $(\'.popadmin3\').popover({
                  title:"'.$qcat.'",
                  content:"'.$help[11].'",
                  trigger:"hover",
                  placement:"auto",
                  html:true
                });
              });
            </script>
            <span class="overpop">
              <a class="popadmin3 float-left" title="'.$qcat.'">
                <i class="far fa-comment-dots"></i>
              </a>
            </span>
		  </div>
        </td>
      </tr>';
		}
        else {
            $cat1 = $cat2 = '';
			$nlg = $lang[1] != '' ? '2' : '';
            echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag1.'</div>
          <div class="d-inline-block">
            <select class="form-control" name="list1" id="list1" onchange="set'.$nlg.'(this.form);" style="width:220px;">';
            if ($qtype != TYP_BLOG) echo '
              <option value="" selected="selected"> -- '.$admin[917].' -- </option>';
            if ($qtype == TYP_BLOG) echo '
              <option value=""> -- '.$admin[1773].' -- </option>';
            if ($qtype != TYP_BLOG) echo '
              <option value=""> -- '.$admin[1125].' -- </option>';
            if ($qtype == TYP_ART) {
                $dbom = ReadDBFields(DBINDEX.'om'.DBEXT);
                $dbw  = array();
                foreach ($dbom as $ix) {
                    if ($ix[0].$ix[1] != '') $dbw[] = $ix;
                }
            }
            elseif ($qtype == TYP_BLOG) {
                $dbw = ReadDBFields(DBINDEX.TYP_BRUB.DBEXT);
            }
            else
                $dbw = ReadDBFields(DBINDEX.$qtype.DBEXT);
            sort($dbw);
            if (!empty($dbw)) {
                $curr_cat = "";
                for ($i = 0; $i < count($dbw); $i++) {
                    if ($dbw[$i][0].$dbw[$i][1] != $curr_cat) {
                        $cat = str_replace('<!--', '&#60;!--', str_replace('-->', '--&#62;', $dbw[$i][0]));
                        $curr_cat = $dbw[$i][0].$dbw[$i][1];
                        echo '
              <option value="'.htmlspecialchars($dbw[$i][0], ENT_QUOTES, $charset).'">'.$cat.'</option>';
                    }
                }
            }
            echo '
            </select>
          </div>
          <div class="d-inline-block"><i class="fas fa-angle-double-right"></i></div>
          <div class="d-inline-block">
            <input class="form-control texte" type="text" name="cat1" 
              value="'.htmlspecialchars($cat1, ENT_QUOTES, $charset).'" size="38" onfocus="reset'.$nlg.'(this.form);" />
          </div>
        </td>
      </tr>';
            if ($lang[1] != "") {
                echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag2.'</div>
          <div class="d-inline-block">
            <select class="form-control" name="list2" id="list2" onchange="set3(this.form);" style="width:220px;">';
            if ($qtype != TYP_BLOG) echo '
              <option value="" selected="selected"> -- '.$admin[917].' -- </option>';
            if ($qtype == TYP_BLOG) echo '
              <option value=""> -- '.$admin[1773].' -- </option>';
            if ($qtype != TYP_BLOG) echo '
              <option value=""> -- '.$admin[1125].' -- </option>';
                if (!empty($dbw)) {
                    reset($dbw);
                    $curr_cat = "";
                    for ($i = 0; $i < count($dbw); $i++) {
                        if ($dbw[$i][0].$dbw[$i][1] != $curr_cat) {
                            $cat = str_replace('<!--', '&#60;!--', str_replace('-->', '--&#62;', $dbw[$i][1]));
                            $curr_cat = $dbw[$i][0].$dbw[$i][1];
                            echo '
              <option value="'.htmlspecialchars($dbw[$i][1], ENT_QUOTES, $charset).'">'.$cat.'</option>';
                        }
                    }
                }
                echo '
            </select>
          </div>
          <div class="d-inline-block"><i class="fas fa-angle-double-right"></i></div>
          <div class="d-inline-block">
            <input class="form-control texte" type="text" name="cat2" value="'.htmlspecialchars($cat2, ENT_QUOTES, $charset).'" size="38" 
              onfocus="this.value=\'\';this.form.list2.selectedIndex=0;" />
          </div>
        </td>
      </tr>';
            }
        }
    }
    elseif ($bcat == 1 && $qtype == TYP_AGENDA) {
        $dd = date('w', mktime(0, 0, 0, intval($mois), intval($day), intval($annee)));
        $jour = $jours[$dd];
        $datevent = substr(FormatDate($annee.$mois.$day."00000"), 0, 10);
        if ($admin[1606]=='0') $datevent = $jour.' '.$datevent; else $datevent = $datevent.', '.$jour;
        echo '
      <tr>
        <td>
          <p class="text-center">'.$qtitre.$admin[630].'
            <input class="form-control w-20 mx-auto texte" type="text" name="cat0" size="45" value="'.$datevent.'" readonly="readonly" />
            <input type="hidden" name="cat1" size="45" value="'.date('YmdHi', mktime(0, 0, 0, intval($mois), intval($day), intval($annee))).'" />
            <input type="hidden" name="titre1" size="45" value="'.$mois.$annee.'" />
          </p>
        </td>
      </tr>
      <tr>
        <td>'.$admin[923].'
          <div class="d-inline-block">
            <input class="form-control '.($err_hhmn ? 'errorInputText' : 'texte').'" type="text" name="hh1" value="" maxlength="2" style="width:30px;" />
          </div>
          <div class="d-inline-block">&nbsp;:&nbsp;</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_hhmn ? 'errorInputText' : 'texte').'" type="text" name="mn1" value="" maxlength="2" style="width:30px;" />
          </div>';
        if ($site[22] == "H3") {
            echo '
          <div class="d-inline-block">
            <select name="sth1" class="form-control select">
              <option value=" AM" selected="selected">AM</option>
              <option value=" PM">PM</option>
            </select>
          </div>';
        }
        echo '
        </td>
      </tr>
      <tr>
        <td>
          <div class="d-inline-block">'.$admin[924].'</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_hhmn ? 'errorInputText' : 'texte').'" type="text" name="hh2" value="" maxlength="2" style="width:30px;" />
          </div>
          <div class="d-inline-block">&nbsp;:&nbsp;</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_hhmn ? 'errorInputText' : 'texte').'" type="text" name="mn2" value="" maxlength="2" style="width:30px;" />
          </div>';
        if ($site[22] == "H3") {
            echo '
          <div class="d-inline-block">
            <select name="sth2" class="form-control select">
              <option value=" AM" selected="selected">AM</option>
              <option value=" PM">PM</option>
            </select>
          </div>';
        }
        echo '
        </td>
      </tr>';
        echo '
      <tr>
        <td class="text-center">
          <fieldset style="width:260px;margin:auto;text-align:left;">
            '.legend('<label>'.$admin[1154].'<input type="checkbox" id="cbrpt" name="cbrpt" value="on" /></label> ').'
            <input type="hidden" name="day" value="'.$day.'" />
            <input type="hidden" name="mois" value="'.$mois.'" />
            <input type="hidden" name="annee" value="'.$annee.'" />
            <div>
              <div class="d-inline-block">
                <label><input type="radio" name="typrpt" value="0" onclick="document.adminsend.cbrpt.checked=true;" /> '.$admin[1155].'</label>
              </div>
              <div class="d-inline-block">
                <input class="form-control texte" type="text" name="drpt" value="'.$drpt.'" maxlength="2" style="width:30px;" />
              </div>
              <div class="d-inline-block">'.$admin[1173].'</div>
            </div>
            <div>
              <div class="d-inline-block">
                <label><input type="radio" name="typrpt" value="1" onclick="document.adminsend.cbrpt.checked=true;" /> '.$admin[1156].'</label>
              </div>
              <div class="d-inline-block">
                <input class="form-control texte" type="text" name="wrpt" value="'.$wrpt.'" maxlength="2" style="width:30px;" />
              </div>
              <div class="d-inline-block">'.$admin[1157].'</div>
            </div>
            <div>
              <div class="d-inline-block">
                <label><input type="radio" name="typrpt" value="2" onclick="document.adminsend.cbrpt.checked=true;" /> '.$admin[1155].'</label>
              </div>
              <div class="d-inline-block">
                <input class="form-control texte" type="text" name="mrpt" value="'.$mrpt.'" maxlength="2" style="width:30px;" />
              </div>
              <div class="d-inline-block">'.$admin[1158].'</div>
            </div>
            <div>
              <div class="d-inline-block">
                <label><input type="radio" name="typrpt" value="3" onclick="document.adminsend.cbrpt.checked=true;" /> '.$admin[1159].'</label>
              </div>
              <div class="d-inline-block">
                <select name="nodayrpt" class="form-control select" style="width:54px;">
                  <option value="1">'.$admin[1169].'</option>
                  <option value="2">'.$admin[1170].'</option>
                  <option value="3">'.$admin[1171].'</option>
                  <option value="4">'.$admin[1172].'</option>
                </select>
              </div>
              <div class="d-inline-block">
                <select name="dayrpt" class="form-control select" style="width:76px;">';
        for ($jj = 1; $jj < count($jours); $jj++)
            echo '
                  <option value="'.$jj.'">'.$jours[$jj].'</option>';
        echo '
                  <option value="0">'.$jours[0].'</option>
                </select>
              </div>
              <div class="d-inline-block"> '.$admin[1160].'</div>
            </div>
            <div>
              <div class="d-inline-block">'.$admin[1161].'</div>
              <div class="d-inline-block">
                <input class="form-control texte" type="text" name="torpt" value="" maxlength="10" style="width:80px;" />
              </div>
            </div>
          </fieldset>
        </td>
      </tr>';
    }
    elseif ($bcat == 1 && $qtype == TYP_MENUICO) {
        echo '
      <tr>
        <td class="text-center">
		  <div class="d-inline-block">'.$qcat.'&nbsp;</div>
          <div class="d-inline-block">
            <input id="cat1" class="form-control '.($err_cat1 ? 'errorInputText' : 'texte').'" name="cat1" style="width:80px;" type="text" value="" />
		  </div>
          <div class="d-inline-block">
            <script>
              $(document).ready(function(){
                $(\'.popadmin4\').popover({
                  title:"'.$qcat.'",
                  content:"'.$help[10].'",
                  trigger:"hover",
                  placement:"auto",
                  html:true
                });
              });
            </script>
            <span class="overpop">
              <a class="popadmin4 float-left" title="'.$qcat.'">
                <i class="far fa-comment-dots"></i>
              </a>
            </span>
		  </div>
        </td>
      </tr>';
    }
    elseif ($bcat == 1 && ($qtype == TYP_FREEBOX || $qtype == TYP_MENUBOX)) {
        if ($qtype == TYP_MENUBOX) {
			$types_menu = array($admin[397], $admin[398], $admin[504], $admin[621], $admin[1354]);
			echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$qcat.' : </div>
          <div class="d-inline-block">
            <select class="form-control" name="fieldweb">';
			for ($jj = 0; $jj < count($types_menu); $jj++) {
				echo '
              <option value="'.$jj.'"'.Selected($fieldweb == $jj).'>'.$types_menu[$jj].'</option>';
			}
				echo '
            </select>
          </div>
        </td>
      </tr>';
			echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[1475].'&nbsp;:&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="nouveau1">
              <option value="L">'.$admin[494].'</option>
              <option value="R">'.$admin[495].'</option>
            </select>
          </div>
        </td>
      </tr>';
		}
        $cat2s = explode('|', $cat2);
		if ($fctwri != 'redac1')
			echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block pt-2">'.$admin[357].'&nbsp;:&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="cat2">
              <option value="off"'.Selected($cat2s[0] == 'off').'>'.$admin[358].'</option>
              <option value="open"'.Selected($cat2s[0] == 'open').'>'.$admin[359].'</option>
              <option value="close"'.Selected($cat2s[0] == 'close').'>'.$admin[360].'</option>
            </select>
          </div>
        </td>
      </tr>';
		else
			echo '
	  <input type="hidden" name="cat2" value="off" />';
	}
    elseif ($bcat == 1 && $qtype == TYP_DDMENUBOX) {
		$types_menu = array('DDV'=>$admin[1351], 'DDH'=>$admin[1352], 'DDL'=>$admin[1387]);
        $cat2s = explode('|', $cat2);
		echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$qcat.'&nbsp;:&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="cat2">';
			foreach ($types_menu as $key=>$value) {
				echo '
              <option value="'.$key.'"'.Selected($cat2 == $key).'>'.$value.'</option>';
			}
				echo '
            </select>
          </div>
        </td>
      </tr>
      <tr>
        <td class="text-center">
          <div class="d-inline-block pt-2">'.$admin[357].'&nbsp;:&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="contenu1">
              <option value="off"'.Selected($cat2s[0] == 'off').'>'.$admin[358].'</option>
              <option value="open"'.Selected($cat2s[0] == 'open').'>'.$admin[359].'</option>
              <option value="close"'.Selected($cat2s[0] == 'close').'>'.$admin[360].'</option>
            </select>
          </div>
        </td>
      </tr>';
    }
    if ($qtype == TYP_FREEBOX) {
			echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[1537].'</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_fieldweb ? 'errorInputText' : 'texte').'" type="text" name="fieldweb" size="6" />
          </div>
        </td>
      </tr>';
    }
    if ($qtype == TYP_ART) {
			echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[1537].'</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_highart ? 'errorInputText' : 'texte').'" type="text" name="highart" size="6" value="'.$highart.'" />
          </div>
        </td>
      </tr>
      <tr>
        <td class="text-center">
          <div class="d-inline-block"><label for="okreact">'.$admin[846].'</label></div>
          <div class="d-inline-block p-0 form-check">
            <input type="checkbox"'.Checked($okreact == 'on').' id="okreact" name="okreact" />
          </div>
        </td>
      </tr>';
    }
    if ($btitre >= 1) {
        echo '
      <tr><td class="text-center pt-2">'.$qtitre.'</td></tr>
      <tr>
        <td class="text-center">';
		if ($qtype != TYP_SOCNET)
			echo '
          <div class="d-inline-block">'.$flag1.'</div>';
        echo '
          <div class="d-inline-block w-100" style="max-width:320px;">
            <input class="form-control '.($err_titre1 ? 'errorInputText' : 'texte').'" type="text" name="titre1" />
          </div>
        </td>
      </tr>';
        if ($lang[1] != '' && $qtype != TYP_SOCNET) {
            echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag2.'</div>
          <div class="d-inline-block w-100" style="max-width:320px;">
            <input class="form-control '.($err_titre2 ? 'errorInputText' : 'texte').'" type="text" name="titre2" />
          </div>
        </td>
      </tr>';
        }
		if ($qtype == TYP_SOCNET) {
            $socs = ReadDBFields(CONFIGREP.'socnet.dtb');
            echo '
      <tr><td class="text-center pt-2">'.$admin[499].'</td></tr>
      <tr>
        <td class="text-center">
          <div class="d-inline-block">
            <select class="form-control" name="contenu2">
              <option value=""'.Selected(@$fieldc2 == '').'>'.$admin[1367].'</option>';
            foreach ($socs as $soc) {
                echo '
              <option value="'.strtolower($soc[0]).'"'.Selected($soc[0] == strtolower($contenu2) && $fieldc2 != '').'>'.strtoupper($soc[0]).'</option>'; 
            }
            echo '
            </select>
          </div>
        </td>
      </tr>
      <tr>
        <td class="text-center">
          <div class="d-inline-block"><label for="cat1">'.$admin[467].'</label></div>
          <div class="d-inline-block form-check">
            <input class="form-check-input" style="margin-top: -0.8rem;" type="checkbox"'.Checked($cat1 == 'on').' name="cat1" id="cat1" />
          </div>
          <div class="d-inline-block ml-5"><label for="cat2">'.$admin[1386].'</label></div>
          <div class="d-inline-block form-check">
            <input class="form-check-input" style="margin-top: -0.8rem;" type="checkbox"'.Checked($cat2 == 'on').' name="cat2" id="cat2" />
          </div>
        </td>
      </tr>
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[86].' (px)</div>
          <div class="d-inline-block">
            <input style="max-width: 64px;" class="form-control texte" name="titre2" type="text" value="'.$titre2.'" />
          </div>
        </td>
      </tr>';
        }
        if (in_array($qtype, array(TYP_RSS, TYP_BSS))) {
            echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[1552].'</div>
          <div class="d-inline-block">
            <select class="form-control" name="fieldweb" style="width:140px;">
              <option value="ALL"'.Selected($fieldweb == 'ALL').'>'.$admin[1363].'</option>';
        foreach ($templates as $key=>$value) {
            echo '
              <option value="'.$key.'"'.Selected($fieldweb == $key && $fieldweb != 'ALL').'>'.$key.' - '.$value.'</option>';
        }
        echo '
            </select>
          </div>
        </td>
      </tr>';
        }
    }
    if ($bnouveau == 1) {
        if ($qtype == TYP_FREEBOX) {
			if ($fctwri != 'redac1')
				echo '
      <tr><td class="text-center pt-3">'.$qnouveau.'</td></tr>
      <tr><td class="text-center">'.ShowValidBoxes().'</td></tr>';
			else
				echo '
	  <input type="hidden" name="cat1" value="" />';
        } 
        elseif ($qtype == TYP_MENUBOX || $qtype == TYP_DDMENUBOX) {
            echo '
      <tr><td class="text-center pt-3"><br />'.$qnouveau.'</td></tr>
      <tr><td class="text-center">'.ShowValidMenus().'</td></tr>';
        }
        elseif ($qtype == TYP_ART) {
			$mbs = ReadAuthorizedIndex(DBMENUBOX, $myname, $grpcol, $fctwri);
			echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$qnouveau.'</div>
          <div class="d-inline-block">
            <select class="form-control" name="nouveau1">
              <option value="none"'.Selected($nouveau1 == "none" || $nouveau1 == "").'>'.$admin[681].'</option>';
            foreach ($mbs as $mb) {
                $boxname = $lng == $lang[0] ? $mb[2] : $mb[3];
                $mnu_id  = sprintf('MNU %d', $mb[4]);
                echo '
              <option value="'.$mnu_id.'"'.Selected($nouveau1 == $mnu_id).'>'.$mnu_id.' : '.$boxname.'</option>';
            }
            echo '
            </select>
          </div>
        </td>
      </tr>';
        }
        elseif ($qtype == TYP_DNLOAD) {
            echo '
      <tr><td class="text-center"><br />'.$qnouveau.'</td></tr>
      <tr>
        <td class="text-center">';
            if ($lang[1] != "") {
                echo '
          <div class="d-inline-block">'.$flag1.'</div>';
            }
            echo '
          <div class="d-inline-block">
            <input class="form-control '.($err_nouveau1 ? 'errorInputText' : 'texte').'" id="nouveau1" name="nouveau1" 
              style="width:540px;" type="text" value="" />
          </div>
          <div class="d-inline-block">
            <input type="button" class="btn btn-info bouton" name="browserver1" id="browserver1" 
              onclick="PopupWindow(\''.CHEMIN.'admin/editors/upload/upload.php?lng='.$lng.
                '&uptype=Link&namerepconfig=guppy_config&pathconfig=admin/editors/\',\'nouveau1\',\'700\',\'515\', \'yes\');" 
              value="'.$admin[793].'" />
          </div>
        </td>
      </tr>';
            if ($lang[1] != "") {
                echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag2.'</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_nouveau2 ? 'errorInputText' : 'texte').'" id="nouveau2" name="nouveau2" 
              style="width:540px;" type="text" value="" />
          </div>
          <div class="d-inline-block">
          <input type="button" class="btn btn-info bouton" name="browserver2" id="browserver2" 
            onclick="PopupWindow(\''.CHEMIN.'admin/editors/upload/upload.php?lng='.$lng.
              '&uptype=Link&namerepconfig=guppy_config&pathconfig=admin/editors/\',\'nouveau2\',\'700\',\'515\', \'yes\');" 
            value="'.$admin[793].'" />
          </div>
  	    </td>
      </tr>';
            }
        }
        elseif ($qtype == TYP_SOCNET) {
            echo '
      <tr><td class="text-center"><br />'.$qnouveau.'</td></tr>
      <tr>
        <td class="text-center">
          <input class="form-control w-50 mx-auto '.($err_nouveau1 ? 'errorInputText' : 'texte').'" type="text" name="nouveau1" size="45" />
        </td>
      </tr>';
		}
        else {
			if ($qtype == TYP_OPTDDMENU) {
				echo '
	  <tr>
	    <td class="text-center">
		  <div class="text-center m-auto" style="width:164px;">
		    <div style="float:left;padding-left:36px;">'.$qnouveau.'</div>
            <script>
              $(document).ready(function(){
                $(\'.popadmin5\').popover({
                  title:"'.$qnouveau.'",
                  content:"'.$help[12].'",
                  trigger:"hover",
                  placement:"auto",
                  html:true
                });
              });
            </script>
            <span class="overpop">
              <a class="popadmin5 float-left" title="'.$qnouveau.'">
                <i class="far fa-comment-dots"></i>
              </a>
            </span>
		  </div>
        </td>
      </tr>';
			} else {
				echo '
      <tr><td class="text-center"><br />'.$qnouveau.'</td></tr>';
			}
			echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag1.'</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_nouveau1 ? 'errorInputText' : 'texte').'" type="text" name="nouveau1" size="45" value="'.$nouveau1.'" />
          </div>
        </td>
      </tr>';
            if ($lang[1] != "") {
                echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag2.'</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_nouveau2 ? 'errorInputText' : 'texte').'" type="text" name="nouveau2" size="45" value="'.$nouveau2.'" />
          </div>
        </td>
      </tr>';
            }
        }
        if ($qtype == TYP_ART || $qtype == TYP_OPTMENU)
            echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[1534].'</div>
          <div class="d-inline-block">
            <input class="form-control texte" type="text" name="ancre" size="15" value="'.$ancre.'" />
          </div>
        </td>
      </tr>';
        if ($qtype == TYP_DNLOAD) {
            $dblicences = ReadDbFields(LICENCES);
            echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[717].'</div>
          <div class="d-inline-block">
            <select class="form-control" name="fieldweb">
              <option value=""'.Selected($fieldweb == "").'> -- '.$admin[1604].' -- </option>';
            foreach ($dblicences as $lic) {
                echo '
              <option value="'.$lic[0].'"'.Selected($fieldweb == $lic[0]).'>'.$lic[1].'</option>';
            }
            echo '
            </select>
          </div>
        </td>
      </tr>';
        }
    }
    if ($qtype == TYP_ART || $qtype == TYP_OPTMENU) {
		switch ($qtype) {        
        case TYP_ART     : $fname = 'fieldweb'; break;
		case TYP_OPTMENU : $fname = 'contenu2'; break;
        }
		if ($fctwri != 'redac1') {
			echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[78].'</div>
          <div class="d-inline-block">
            <select class="form-control" name="'.$fname.'">';
			foreach (array('S' => $admin[1315], 'W' => $admin[1316], 'L' => $admin[1319], 'R' => $admin[1320]) as $key=>$opt) {
				echo '
			  <option value="'.$key.'">'.$opt.'</option>';
			}
			echo '
            </select>
          </div>
        </td>
      </tr>';
		} else
			echo '
	  <input type="hidden" name='.$fname.'" value="R" />';
	}
    if ($qtype == TYP_OPTMENU || ($qtype == TYP_OPTDDMENU && $typmenu != 'DDL') || $qtype == TYP_ART) {
        $fname = $qtype == TYP_ART ? 'tconfart' : 'fieldmail';
		if (count($templates) > 1 && $fctwri != 'redac1') {
			echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[1552].'</div>
          <div class="d-inline-block">
            <select class="form-control" name="'.$fname.'" style="width:140px;">
              <option value="ALL">'.$admin[1363].'</option>';
            foreach ($templates as $key=>$value) {
                echo '
              <option value="'.$key.'">'.$key.' - '.$value.'</option>';
            }
            echo '
            </select>
          </div>
        </td>
      </tr>';
		} else
			echo '
	  <input type="hidden" name="'.$fname.'" value="0">';
	}
    if (in_array($qtype, array(TYP_ART, TYP_NEWS, TYP_BLOG, TYP_MENUICO, TYP_OPTDDMENU, TYP_SOCNET)) && @$typmenu != 'DDL') {
		switch ($qtype) {        
        case TYP_ART       : 
        case TYP_BLOG      : $label = $admin[461];  $fname = 'nouveau2'; break;
        case TYP_NEWS      : $label = $admin[462];  $fname = 'nouveau2'; break;
        case TYP_MENUICO   : 
		case TYP_OPTDDMENU : $label = $admin[1240]; $fname = 'contenu2'; break;
        case TYP_SOCNET    : $label = $admin[829];  $fname = 'nouveau2'; break;
        }
        echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$label.'</div>
          <div class="d-inline-block">
            <input id="'.$fname.'" class="form-control texte" name="'.$fname.'" style="width:240px;" type="text" value="" />
          </div>
          <div class="d-inline-block">
            <input type="button" class="btn btn-info bouton" name="browserver3" id="browserver3" 
              onclick="PopupWindow(\''.CHEMIN.'admin/editors/upload/upload.php?lng='.$lng.
                '&uptype=Image&namerepconfig=guppy_config&pathconfig=admin/editors/\',\''.$fname.'\',\'700\',\'515\', \'yes\');" 
              value="'.$admin[793].'" />
		  </div>
  	    </td>
      </tr>';
    }
    if ($qtype == TYP_MENUICO) {
		echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[1335].'</div>
          <div class="d-inline-block">
            <select class="form-control" name="fieldmail" style="width:140px;">
              <option value="ALL"'.Selected($fieldmail == 'ALL').'>'.$admin[1363].'</option>';
        foreach ($templates as $key=>$value) {
            echo '
              <option value="'.$key.'"'.Selected($fieldmail == $key && $fieldmail != 'ALL').'>'.$key.' - '.$value.'</option>';
        }
        echo '
            </select>
          </div>
        </td>
      </tr>
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[1552].'</div>
          <div class="d-inline-block">
            <select class="form-control" name="fieldweb" style="width:140px;">
              <option value="ALL"'.Selected($fieldweb == 'ALL').'>'.$admin[1363].'</option>';
        foreach ($templates as $key=>$value) {
            echo '
              <option value="'.$key.'"'.Selected($fieldweb == $key && $fieldweb != 'ALL').'>'.$key.' - '.$value.'</option>';
        }
        echo '
            </select>
          </div>
        </td>
      </tr>';
	}
    if ($bcontenu == 1) {
        if (in_array($qtype, array(TYP_OPTMENU, TYP_MENUICO, TYP_SOCNET)) || ($qtype == TYP_OPTDDMENU && $typmenu != 'DDL')) {
            echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$qcontenu.'</div>
          <div class="d-inline-block">
            <select class="form-control" name="contenu1">
              <option value="_self"'.Selected($contenu1 == '_self').'>'.$admin[1232].'</option>
              <option value="_blank"'.Selected($contenu1 == '_blank').'>'.$admin[1233].'</option>';
			if ($qtype != TYP_SOCNET) {
				echo '
              <option value="_POPUP"'.Selected($contenu1 == '_popup').'>'.$admin[1243].'</option>';
			}
            echo '
            </select>
          </div>
        </td>
      </tr>';
        }
        else {
            if ($qtype == TYP_ART && $serviz[113] != 'on') {
				echo '
      <tr><td class="text-center">'.$admin[1347].'</td></tr>
	  <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag1.'</div>
          <div class="d-inline-block">
		    <input class="form-control texte" type="text" name="keywords1" size="70" value="'.$keywords1.'" />
          </div>
        </td>
	  </tr>';
				if ($lang[1] != "") {
					echo '
	  <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag2.'</div>
          <div class="d-inline-block">
		    <input class="form-control texte" type="text" name="keywords2" size="70" value="'.$keywords2.'" />
          </div>
        </td>
	  </tr>';
				}
			}
            echo '
      <tr>
        <td class="text-center"><div class="mt-2">'.$qcontenu.'</div></td>
      </tr>
      <tr>
        <td class="text-center">'.$flag1.'</td>
      </tr>
      <tr>
	    <td>';
            echo '
		  <div class="text-center w-100 w-admeditor mx-auto">';
            $hedit = in_array($qtype, array(TYP_MENUBOX, TYP_PHOTO, TYP_THINK)) ? '120px' : '320px';
            display_admin_editor('contenu1', '100%', $hedit, $contenu1);
			echo '
		  </div>
	  	</td>
	  </tr>';
            if ($lang[1] != '') {
                echo '
      <tr>
        <td class="text-center">'.$flag2.'</td>
      </tr>
      <tr>
        <td>
		  <div class="text-center w-100 w-admeditor mx-auto">';
                display_admin_editor('contenu2', '100%', $hedit, $contenu2);
			echo '
		  </div>
        </td>
      </tr>';
            }
        }
    }
    echo '
    </table>
    </div>';
    echo '
	</fieldset>
    <div class="quest" style="padding:2px 20px;">';
	if (in_array($qtype, array(TYP_FREEBOX, TYP_MENUBOX, TYP_DDMENUBOX, TYP_SOCNET, TYP_BANNER, TYP_THINK, TYP_RSS)))
		echo '
      <div class="float-left mt-3">
	    <a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg=config6&amp;selskin='.$page[14].'&amp;conf='.$tconfig.'">
          '.$admin[346].'&nbsp;<i class="gen-cfgboites align-middle" title="'.$admin[346].'"></i>
	    </a>&nbsp;||&nbsp;
	    <a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg=config3&amp;selskin='.$page[14].'&amp;conf='.$tconfig.'">
          '.$admin[469].'&nbsp;<i class="gen-cfgaccueil align-middle" title="'.$admin[469].'"></i>
	    </a>
      </div>';
	echo '
      <div class="float-right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div>'.
	  ($qseeall == '' ? '' : '
      <div class="float-right pt-3" style="margin-right:24px;">
        <label for="retform">'.$admin[857].$qseeall.'</label>
        <input type="checkbox" id="retform" name="retform" />
      </div>').
	  ($qtype == TYP_ART ? '
      <div class="float-right pt-3" style="margin-right:24px;">
        <label for="svart">'.$admin[1014].' admins&nbsp;</label>
        <input type="checkbox" id="svart" name="svart" />
      </div>' : '').'
      <div class="clearfix"></div>
    </div>
  </form>';
}
################################################################################
elseif ($form == 2) {
	if (!empty($erreur))
		echo displayErrorMsg($admin[1589], $erreur);
    ReadDoc($id);
    $datespub = explode('-', $creadate);
    $datedeb  = $datespub[0];
    $datefin  = empty($datespub[1]) ? '' : $datespub[1];
	$cdate    = $creadate;
	$mdate    = $moddate;
    $tmp      = explode('|', $author);
    $author   = $dup == 1 || $dup == 2 ? $myname : $tmp[0];
    $grpcol   = isset($tmp[1]) ? $tmp[1] : '';
    $contenu1 = $fieldc1;
    if ($qtype == TYP_ART || $qtype == TYP_OPTMENU) {
        $urls = explode('||', $fieldd1);
        $pos  = strpos($urls[0], '#');
        if ($pos !== false) $ancre = substr($urls[0], $pos + 1);
        $urls = explode('||', $fieldd2);
		if (isset($urls[1]))
		{
			$pos  = strpos(@$urls[1], '#');
			if ($pos !== false && empty($ancre)) $ancre = substr(@$urls[1], $pos + 1);
		}
    }
    if ($qtype == TYP_ART) {
        $afmail   = explode('|', $fieldmail);
        $tconfart = $afmail[0];
        $highart  = !empty($afmail[1]) ? $afmail[1] : '0';
        $okreact  = isset($afmail[2]) ? $afmail[2] : 'on';
    }
    echo '
  <style> .w-admeditor { max-width: '.$serviz[101].'; } </style>
  <form name="adminsend" id="adminsend" action="admin.php?lng='.$lng.'&amp;pg='.$pgreturn[$qtype].'" method="post" onsubmit="return VerifyForm(); return false;">
    <input type="hidden" name="tri" value="'.$tri.'" />
    <div class="quest mb-3" style="padding:2px 20px;">';
	if (in_array($qtype, array(TYP_GUESTBK, TYP_FREEBOX, TYP_MENUBOX, TYP_DDMENUBOX, TYP_SOCNET, TYP_BANNER, TYP_THINK, TYP_RSS)) && $fctwri != 'redac1')
		echo '
      <div class="float-left mt-3">
	    <a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg=config6&amp;selskin='.$page[14].'&amp;conf='.$tconfig.'">
          '.$admin[346].'&nbsp;<i class="gen-cfgboites align-top" title="'.$admin[346].'"></i>
	    </a>&nbsp;||&nbsp;
	    <a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg=config3&amp;selskin='.$page[14].'&amp;conf='.$tconfig.'">
          '.$admin[469].'&nbsp;<i class="gen-cfgaccueil align-top" title="'.$admin[469].'"></i>
	    </a>
      </div>';
	echo '
      <div class="float-right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div>'.
	  ($qseeall == '' ? '' : '
      <div class="float-right pt-3" style="margin-right:24px;">
        <label for="retform">'.$admin[857].$qseeall.'</label>
        <input type="checkbox" id="retform" name="retform" onchange="setret2(this.form);" />
      </div>').'
      <div class="float-right pt-3" style="margin-right:24px;">
        <label for="majfile">'.$admin[1381].'</label>
        <input type="checkbox" checked="checked" id="majfile" name="majfile" onchange="setmaj2(this.form);" />
      </div>'.
	  ($qtype == TYP_ART ? '
      <div class="float-right pt-3" style="margin-right:24px;">
        <label for="svart">'.$admin[1014].' admins&nbsp;</label>
        <input type="checkbox" id="svart" name="svart" onchange="setart2(this.form);" />
      </div>' : '').'
      <div class="clearfix"></div>
    </div>
    <fieldset class="w-100 m-0 p-2">';
    if ($dup == 1 || $dup == 2) {
        echo '
      <input type="hidden" name="dup" value="'.$dup.'" />';
		echo '
      <br />'.$admin[921].'
      <select name="status" class="form-control w-100 mx-auto align-middle" style="max-width:80px;">
        <option value="on" selected="selected">'.$admin[765].'</option>
        <option value="">'.$admin[766].'</option>
      </select><br />';
		echo '
      <input type="hidden" name="add" value="1" />';
	  $datedeb = date('YmdHi');
	  $datefin = '';
    } else {
        echo '
      <input type="hidden" name="mod" value="1" />';
    }
    echo '
      <input type="hidden" name="form" value="2" />
      <input type="hidden" name="idpg" value="'.$idpg.'" />
      <input type="hidden" name="id" value="'.$id.'" />
	  <input type="hidden" name="datecrea" value="'.$cdate.'" />
	  <input type="hidden" name="datemod" value="'.$mdate.'" />';
    if ($bcat == 1 && $qtype == TYP_AGENDA) {
        if ($dup == 2) {
            $fieldb1 = $mois.$annee;
        }
        echo '
      <input type="hidden" name="titre1" value="'.$fieldb1.'" />';
    }
    if (in_array($qtype, array(TYP_GUESTBK, TYP_FORUM, TYP_REACT, TYP_REBLOG))) {
        echo '
      <input type="hidden" name="cat1" value="'.$fielda1.'" />';
    }
    if (in_array($qtype, array(TYP_FORUM, TYP_REACT, TYP_REBLOG))) {
        echo '
      <input type="hidden" name="cat2" value="'.$fielda2.'" />';
    }
    if ($btitre = 1 && $qtype == TYP_FORUM && $fielda2 != '0') {
        echo '
      <input type="hidden" name="titre1" value="'.$fielda2.'" />';
	}
    echo '
    <div class="table-responsive-md">
    <table class="table w-100 m-auto">';
    /// début modif accès privé
    if (in_array($qtype, array(TYP_ART, TYP_AGENDA, TYP_BLOG, TYP_DNLOAD, TYP_FAQ, TYP_LINKS, TYP_NEWS, TYP_PHOTO, 
		TYP_FREEBOX, TYP_MENUBOX, TYP_OPTMENU, TYP_DDMENUBOX, TYP_OPTDDMENU, TYP_MENUICO, TYP_SOCNET, TYP_NWL))) {
        $dbgroup = ReadDBFields($qtype == TYP_NWL ? GROUPSNWL : GROUPS);
        echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[$qtype == TYP_NWL ? 1484 : 932].'&nbsp;:&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="fieldmod" style="width:164px;">';
        if ($qtype != TYP_NWL)
            echo '
              <option value=""> -- '.$admin[1604].' -- </option>';
        echo '
			  <option value="'.($qtype == TYP_NWL ? 'all_subscribers' : 'all_members').'"'.
                Selected($fieldmod == ($qtype == TYP_NWL ? 'all_subscribers' : 'all_members')).'>'.$admin[$qtype == TYP_NWL ? 1150 : 690].'
              </option>';
        foreach ($dbgroup as $groupe)
            echo '
              <option value="'.$groupe[0].'"'.Selected($fieldmod == $groupe[0]).'>'.$groupe[0].'</option>';
        echo '
            </select>
          </div>
        </td>
      </tr>';
    }
    /// fin modif accès privé
	if (!in_array($qtype, array(TYP_GUESTBK, TYP_FORUM, TYP_NWL,TYP_REACT, TYP_REBLOG, TYP_RULES, TYP_RSS, TYP_BSS, TYP_SOCNET))) {
		if ($fctwri != 'redac1')
			echo '
      <tr>
	    <td class="text-center">
		  <div style="width:184px;margin:auto;">
		    <div class="float-left">'.$admin[1406].'</div>
            <script>
              $(document).ready(function(){
                $(\'.popadmin6\').popover({
                  title:"'.$admin[1406].'",
                  content:"'.$help[14].'",
                  trigger:"hover",
                  placement:"auto",
                  html:true
                });
              });
            </script>
            <span class="overpop">
              <a class="popadmin6 float-left" title="'.$admin[1406].'">
                <i class="far fa-comment-dots pl-3"></i>
              </a>
            </span>
		  </div>
	    </td>
	  </tr>
      <tr>
	    <td class="text-center">
		  <div class="d-inline-block">'.$admin[1407].'&nbsp;:&nbsp;</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_datedeb ? 'errorInputText' : 'texte').'" type="text" name="datedeb" size="10" value="'.$datedeb.'" />
          </div>
		  <div class="d-inline-block">&nbsp;&nbsp;'.$admin[1408].'&nbsp;:&nbsp;</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_datefin ? 'errorInputText' : 'texte').'" type="text" name="datefin" size="10" value="'.$datefin.'" />
          </div>
		</td>
	  </tr>';
		else
			echo '
      <input type="hidden" name="datedeb" value="'.$datedeb.'" />
      <input type="hidden" name="datefin" value="'.$datefin.'" />';
	}
	else
		echo '
      <input type="hidden" name="datedeb" value="'.$datedeb.'" />
      <input type="hidden" name="datefin" value="'.$datefin.'" />';
    if ($bname == 1) {
		if ($fctwri != 'redac1') {
			echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[114].'&nbsp;</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_pseudo1 ? 'errorInputText' : 'texte').'" type="text" name="pseudo1" size="32" value="'.$author.'" />
          </div>
        </td>
      </tr>';
            if ((@$drtuser[12] != '' || in_array($fctwri, array('admin', 'webm', 'redac2'))) && $serviz[42] == 'on') {
                $dbgroup = ReadDBFields(GROUPSCOL);
                echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[1480].'&nbsp;</div>
          <div class="d-inline-block w-100" style="max-width:180px;">
            <select class="form-control" name="grpcol">
              <option value=""'.Selected($grpcol == '').'>'.$admin[1604].'</option>
              <option value="ALL"'.Selected($grpcol == 'ALL').'>'.$admin[1481].'</option>';
                foreach ($dbgroup as $group)
                    echo '
              <option value="'.$group[0].'"'.Selected($grpcol == $group[0] && $grpcol != 'ALL').'>'.$group[0].'</option>';
                echo '            
            </select>
          </div>
        </td>
      </tr>';
            } else {
                echo '
	  <input type="hidden" name="grpcol" value="'.$grpcol.'" />';
            }
		} else
			echo '
	  <input type="hidden" name="pseudo1" value="'.$author.'" />
	  <input type="hidden" name="grpcol" value="'.$grpcol.'" />';
    }
    if ($bemail == 1) {
		if ($fctwri != 'redac1')
			echo '
      <tr>
        <td class="text-center pt-2">
          <div class="d-inline-block">'.$admin[115].'&nbsp;</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_email1 ? 'errorInputText' : 'texte').'" type="text" name="email1" size="40" value="'.$email.'" />
          </div>
        </td>
      </tr>';
		else
			echo '
      <input type="hidden" name="email1" value="'.$email.'" />';
    }
    if ($bnouveau == 2) {
		if ($fctwri != 'redac1')
			echo '
      <tr>
        <td class="text-center pt-2">
          <div class="d-inline-block"><label for="nouveau1">'.$qnouveau.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label></div>
          <div class="d-inline-block">
            <input type="checkbox" id="nouveau1" name="nouveau1"'.Checked($fieldd1 == 'on').' />
          </div>
        </td>
      </tr>';
		else
			echo '
      <input type="hidden" name="nouveau1" value="'.$fieldd1.'" />';
    }
    if ($bcat == 1) {
        if ($qtype == TYP_AGENDA) {
            if ($dup == 2) {
                $fielda1 = substr(FormatDate($annee.$mois.$day."00000"), 0, 10);
            } else {
				$day   = substr($fielda1, 6, 2);
				$mois  = substr($fielda1, 4, 2);
				$annee = substr($fielda1, 0, 4);
            }
            $dd = date('w', mktime(0, 0, 0, intval($mois), intval($day), intval($annee)));
            $jour = $jours[$dd];
            $datevent = substr(FormatDate($annee.$mois.$day."00000"), 0, 10);
            if ($admin[1606] == '0') $datevent = $jour.' '.$datevent; else $datevent = $datevent.', '.$jour;
            echo '
      <tr><td class="text-center pt-2">'.$qcat.'</td></tr>
      <tr>
        <td class="text-center">
          <input style="width: 200px;" class="form-control mx-auto texte" type="text" name="cat0" size="45" 
            value="'.$datevent.'" readonly="readonly" />
        </td>
      </tr>
      <tr>
        <td class="text-center">
          <input type="hidden" name="cat1" size="45" 
            value="'.date('YmdHi', mktime(0, 0, 0, intval($mois), intval($day), intval($annee))).'" />
        </td>
      </tr>';
            $timed = $timef = array('', '');
            $sthh1 = $sthh2 = '';
            if ('-' != $fielda2) {
                $timeag = explode('-', $fielda2);
                if ($site[22] == 'H3') {
                    $sthh1 = explode(' ', $timeag[0]);
                    $timed = explode(':', $sthh1[0]);
                    $sthh2 = explode(' ', (empty($timeag[1])? '': $timeag[1]));
                    $timef = explode(':', $sthh2[0]);
                } else {
                    $timed = explode(':', $timeag[0]);
                    $timef = explode(':', (empty($timeag[1])? '': $timeag[1]));
                }
            }
            echo '
      <tr>
        <td>
          <div class="d-inline-block">'.$admin[923].'</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_hhmn ? 'errorInputText' : 'texte').'" type="text" name="hh1" size="2" 
              value="'.$timed[0].'" maxlength="2" style="width:30px;" />
          </div>
          <div class="d-inline-block">&nbsp;:&nbsp;</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_hhmn ? 'errorInputText' : 'texte').'" type="text" name="mn1" size="2" 
              value="'.(empty($timed[1])? '': $timed[1]).'" maxlength="2" style="width:30px;" />
          </div>';
            if ($site[22] == 'H3') {
                echo '
          <div class="d-inline-block">
            <select class="form-control" name="sth1" class="select">
              <option value=" AM"'.Selected($sthh1[1] == " AM").'> AM </option>
              <option value=" PM"'.Selected($sthh1[1] == " PM").'> PM </option>
            </select>
          </div>';
            }
            echo '
        </td>
      </tr>
      <tr>
        <td>
          <div class="d-inline-block">'.$admin[924].'</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_hhmn ? 'errorInputText' : 'texte').'" type="text" name="hh2" size="2" 
              value="'.$timef[0].'" maxlength="2" style="width:30px;" />
          </div>
          <div class="d-inline-block">&nbsp;:&nbsp;</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_hhmn ? 'errorInputText' : 'texte').'" type="text" name="mn2" size="2" 
              value="'.(empty($timef[1])? '': $timef[1]).'" maxlength="2" style="width:30px;" />
          </div>';
            if ($site[22] == 'H3') {
                echo '
          <div class="d-inline-block">
            <select class="form-control" name="sth2" class="select">
              <option value=" AM"'.Selected($sthh2[1] == " AM").'> AM </option>
              <option value=" PM"'.Selected($sthh2[1] == " PM").'> PM </option>
            </select>
          </div>';
            }
			echo '
		</td>
	  </tr>';
        }
        elseif ($qtype == TYP_MENUICO) {
            echo '
      <tr>
        <td class="text-center pt-2">
		  <div class="text-center m-auto" style="max-width:158px;">
		    <div class="float-left">
              <div class="d-inline-block">'.$qcat.'</div>
              <div class="d-inline-block">
                <input id="cat1" class="form-control '.($err_cat1 ? 'errorInputText' : 'texte').'" name="cat1" 
                  style="width:80px;" type="text" value="'.$fielda1.'" />
              </div>
		    </div>
            <script>
              $(document).ready(function(){
                $(\'.popadmin7\').popover({
                  title:"'.$qcat.'",
                  content:"'.$help[10].'",
                  trigger:"hover",
                  placement:"auto",
                  html:true
                });
              });
            </script>
            <span class="overpop">
              <a class="popadmin7 float-left" title="'.$qcat.'">
                <i class="far fa-comment-dots"></i>
              </a>
            </span>
		  </div>
        </td>
      </tr>';
        }
		elseif ($qtype == TYP_FREEBOX || $qtype == TYP_MENUBOX) {
			if ($qtype == TYP_MENUBOX) {
				$types_menu = array($admin[397], $admin[398], $admin[504], $admin[621], $admin[1354]);
				echo '
      <tr>
        <td class="text-center pt-2">
          <div class="d-inline-block">'.$qcat.'&nbsp;:&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="fieldweb">';
				for ($jj = 0; $jj < count($types_menu); $jj++) {
					echo '
              <option value="'.$jj.'"'.Selected($fieldweb == $jj).'>'.$types_menu[$jj].'</option>';
				}
				echo '
            </select>
          </div>
        </td>
      </tr>';
                echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[1475].'&nbsp;:&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="nouveau1">
              <option value="L"'.Selected($fieldd1 == 'L').'>'.$admin[494].'</option>
              <option value="R"'.Selected($fieldd1 == 'R').'>'.$admin[495].'</option>
            </select>
          </div>
        </td>
      </tr>';
			}
            $cat2s = explode('|', $fielda2);
			if ($fctwri != 'redac1')
				echo '
      <tr>
        <td class="text-center pt-2">
          <div class="d-inline-block">'.$admin[357].'&nbsp;:&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="cat2">
              <option value="off"'.Selected($cat2s[0] == 'off').'>'.$admin[358].'</option>
              <option value="open"'.Selected($cat2s[0] == 'open').'>'.$admin[359].'</option>
              <option value="close"'.Selected($cat2s[0] == 'close').'>'.$admin[360].'</option>
            </select>
          </div>
        </td>
      </tr>';
			else
				echo '
	  <input type="hidden" name="cat2" value="'.$cat2s[0].'|'.$cat2s[1].'" />';
        }
		elseif ($qtype == TYP_DDMENUBOX) {
			$types_menu = array('DDV'=>$admin[1351], 'DDH'=>$admin[1352], 'DDL'=>$admin[1387]);
			echo '
      <tr>
        <td class="text-center pt-2">
          <div class="d-inline-block">'.$qcat.'&nbsp;:&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="cat2">';
			foreach ($types_menu as $key=>$value) {
				echo '
              <option value="'.$key.'"'.Selected($fielda2 == $key).'>'.$value.'</option>';
			}
				echo '
            </select>
          </div>
        </td>
      </tr>
      <tr>
        <td class="text-center">
          <div class="d-inline-block pt-2">'.$admin[357].'&nbsp;:&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="contenu1">
              <option value="off"'.Selected($contenu1 == 'off').'>'.$admin[358].'</option>
              <option value="open"'.Selected($contenu1 == 'open').'>'.$admin[359].'</option>
              <option value="close"'.Selected($contenu1 == 'close').'>'.$admin[360].'</option>
            </select>
          </div>
        </td>
      </tr>';
		}
        elseif ($qtype != TYP_FORUM ) {
			if (($dup == 1 && $qtype == TYP_ART) || $qtype == TYP_OPTMENU) {
				echo '
	  <tr>
	    <td class="text-align:center">
		  <div style="text-align:center;width:270px;margin:auto;">
		    <div style="float:left;padding-left:56px;">'.$qcat.'</div>
            <script>
              $(document).ready(function(){
                $(\'.popadmin8\').popover({
                  title:"'.$qcat.'",
                  content:"'.$help[13].'",
                  trigger:"hover",
                  placement:"auto",
                  html:true
                });
              });
            </script>
            <span class="overpop">
              <a class="popadmin8 float-left ml-2" title="'.$qcat.'">
                <i class="far fa-comment-dots"></i>
              </a>
            </span>
		  </div>
        </td>
	  </tr>';
			} 
			elseif ($qtype != TYP_ART)
				echo '
      <tr><td class="text-center pt-2">'.$qcat.'</td></tr>';
            if ($qtype == TYP_BANNER) {
	           echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag1.'</div>
          <div class="d-inline-block">
            <input id="cat1" class="form-control '.($err_cat1 ? 'errorInputText' : 'texte').'" name="cat1" style="vertical-align:middle;width:400px;" type="text" value="'.$fielda1.'" />
          </div>
          <div class="d-inline-block">
            <input type="button" class="btn btn-info bouton" name="browserver1" id="browserver1" 
              onclick="PopupWindow(\''.CHEMIN.'admin/editors/upload/upload.php?lng='.$lng.
                '&uptype=Image&namerepconfig=guppy_config&pathconfig=admin/editors/\',\'cat1\',\'700\',\'515\', \'yes\');" 
              value="'.$admin[793].'" />
          </div>';
                if ($lang[1] != "") {
                    echo '
        </td>
      </tr>
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag2.'</div>
          <div class="d-inline-block">
            <input id="cat2" class="form-control align-middle '.($err_cat2 ? 'errorInputText' : 'texte').'" name="cat2" 
              style="width:400px;" type="text" value="'.$fielda2.'" />
          </div>
          <div class="d-inline-block">
            <input type="button" class="btn btn-info bouton" name="browserver2" id="browserver2" 
              onclick="PopupWindow(\''.CHEMIN.'admin/editors/upload/upload.php?lng='.$lng.
                '&uptype=Image&namerepconfig=guppy_config&pathconfig=admin/editors/\',\'cat2\',\'700\',\'515\', \'yes\');" 
              value="'.$admin[793].'" />
          </div>';
                }
                echo '
        </td>
      </tr>';
            }
            elseif ($qtype == TYP_BSS || $qtype == TYP_RSS) {
                echo '
      <tr>
        <td class="text-center pt-2">
          <div class="d-inline-block">'.$flag1.'</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_cat1 ? 'errorInputText' : 'texte').'" type="text" name="cat1" value="'.$fielda1.'" size="38" />
          </div>';
                if ($lang[1] != "") {
                    echo '
        </td>
      </tr>
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag2.'</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_cat2 ? 'errorInputText' : 'texte').'" type="text" name="cat2" value="'.$fielda2.'" size="38" />
          </div>';
                }
                echo '
        </td>
      </tr>';
            }
			elseif ($qtype == TYP_OPTDDMENU) {
				echo '
      <tr>
        <td class="text-center">
		  <div style="text-align:center;width:240px;margin:auto;">
		    <div class="float-left">
              <input id="cat1" class="form-control '.($err_cat1 ? 'errorInputText' : 'texte').'" name="cat1" 
                style="width:200px;" type="text" value="'.$fielda1.'" />
		    </div>
            <script>
              $(document).ready(function(){
                $(\'.popadmin9\').popover({
                  title:"'.$qcat.'",
                  content:"'.$help[11].'",
                  trigger:"hover",
                  placement:"auto",
                  html:true
                });
              });
            </script>
            <span class="overpop">
              <a class="popadmin9 float-left" title="'.$qcat.'">
                <i class="far fa-comment-dots"></i>
              </a>
            </span>
		  </div>
        </td>
      </tr>';
			}
            elseif ($dup != 1 && $qtype == TYP_ART) {
				echo '
	  <input type="hidden" name="cat1" value="'.$fielda1.'" />
	  <input type="hidden" name="cat2" value="'.$fielda2.'" />';
			} else {
                echo '
      <tr>
        <td class="text-center pt-2">
          <div class="d-inline-block">'.$flag1.'</div>';
                $nlg = $lang[1] != '' ? '2' : '';
                echo '
          <div class="d-inline-block">
            <select class="form-control" name="list1" id="list1" style="width:220px;" onchange="set'.$nlg.'(this.form);">
              <option value=""> -- '.$admin[917].' -- </option>';
                if ($qtype != TYP_BLOG) echo '
              <option value=""'.Selected('' == $fielda1.$fielda2).'> -- '.$admin[1125].' -- </option>';
                if ($qtype == TYP_ART) {
                    $dbix = ReadDBFields(DBINDEX.'arom'.DBEXT);
                    $dbw  = array();
                    foreach ($dbix as $ix) {
                        $dbw[] = array($ix[5], $ix[6]);
                    }
                } 
                elseif ($qtype == TYP_BLOG) {
                    $dbw = ReadDBFields(DBINDEX.TYP_BRUB.DBEXT);
                }
                else
                    $dbw = ReadDBFields(DBINDEX.$qtype.DBEXT);
				sort($dbw);
				if (!empty($dbw)) {
					$curr_cat = "";
					for ($i = 0; $i < count($dbw); $i++) {
						if ($dbw[$i][0].$dbw[$i][1] != $curr_cat) {
							$sel= "";
							$cat = str_replace('<!--', '&#60;!--', str_replace('-->', '--&#62;', $dbw[$i][0]));
							$curr_cat = $dbw[$i][0].$dbw[$i][1];
							echo '
                <option value="'.htmlspecialchars($dbw[$i][0], ENT_QUOTES, $charset).'"'.Selected($curr_cat == $fielda1.$fielda2).'>'.$cat.'</option>';
						}
					}
				}
				echo '
            </select>
          </div>
          <div class="d-inline-block">&nbsp;>>&nbsp;</div>
          <div class="d-inline-block">
            <input class="form-control texte" type="text" name="cat1" 
              value="'.htmlspecialchars($fielda1, ENT_QUOTES, $charset).'" size="36" onfocus="reset'.$nlg.'(this.form);" />
          </div>
        </td>
      </tr>';
				if ($lang[1] != "") {
					echo '
      <tr>
        <td style="text-align:center;white-space:nowrap;">
          <div class="d-inline-block">'.$flag2.'</div>
          <div class="d-inline-block">
            <select class="form-control" name="list2" id="list2" style="width:220px;" onchange="set3(this.form);">
              <option value=""> -- '.$admin[917].' -- </option>';
                    if ($qtype != TYP_BLOG) echo '
              <option value=""'.Selected('' == $fielda1.$fielda2).'> -- '.$admin[1125].' -- </option>';
					if (!empty($dbw)) {
						reset($dbw);
						$curr_cat = "";
						for ($i = 0; $i < count($dbw); $i++) {
							if ($dbw[$i][0].$dbw[$i][1] != $curr_cat) {
								$sel= "";
								$cat = str_replace('<!--', '&#60;!--', str_replace('-->', '--&#62;', $dbw[$i][1]));
								$curr_cat = $dbw[$i][0].$dbw[$i][1];
								echo '
              <option value="'.htmlspecialchars($dbw[$i][1], ENT_QUOTES, $charset).'"'.Selected($dbw[$i][0].$dbw[$i][1] == $fielda1.$fielda2).'>'.$cat.'</option>';
							}
						}
					}
					echo '
            </select>
          </div>
          <div class="d-inline-block">&nbsp;>>&nbsp;</div>
          <div class="d-inline-block">
            <input class="form-control texte" type="text" name="cat2" value="'.htmlspecialchars($fielda2, ENT_QUOTES, $charset).'" size="36" 
              onfocus="reset'.$nlg.'(this.form);" />
          </div>
        </td>
      </tr>';
                }
            }
        }
        elseif ($fielda2 == "0") {
            echo '
      <tr><td class="text-center">'.$admin[260].'</td></tr>
      <tr>
        <td class="text-center">
          <div class="d-inline-block">
            <select class="form-control" name="titre2">
              <option value="">'.$admin[259].'</option>';
            for ($i = 0; $i < count($dbfcat); $i++) {
                echo '
              <option value="'.$dbfcat[$i][0].'"'.Selected($dbfcat[$i][0] == $fieldb2).'>'.$dbfcat[$i][1].'</option>';
            }
            echo '
            </select>
          </div>
        </td>
      </tr>';
        }
    }
    if ($qtype == TYP_FREEBOX) {
		echo '
      <tr>
        <td class="text-center pt-2">
          <div class="d-inline-block">'.$admin[1537].'&nbsp;</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_fieldweb ? 'errorInputText' : 'texte').'" type="text" name="fieldweb" size="6" value="'.$fieldweb.'" />
          </div>
        </td>
      </tr>';
    }
    if ($qtype == TYP_ART) {
		echo '
      <tr>
        <td class="text-center pt-2">
          <div class="d-inline-block">'.$admin[1537].'&nbsp;</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_highart ? 'errorInputText' : 'texte').'" type="text" name="highart" size="6" value="'.$highart.'" />
          </div>
        </td>
      </tr>
      <tr>
        <td class="text-center">
          <div class="d-inline-block"><label for="okreact">'.$admin[846].'&nbsp;</label</div>
          <div class="d-inline-block">
            <input type="checkbox"'.Checked($okreact == 'on').' id="okreact" name="okreact" />
          </div>
        </td>
      </tr>';
    }
    if (($qtype != TYP_FORUM && $qtype != TYP_AGENDA) || ($qtype == TYP_FORUM && $fielda2 == "0")) {
        $fieldb1 = str_replace('"','&#34;',$fieldb1);
        echo '
      <tr><td class="text-center pt-2">'.$qtitre.'</td></tr>
      <tr>
        <td class="text-center">';
		if ($qtype != TYP_SOCNET)
            echo '
          <div class="d-inline-block">'.$flag1.'</div>';
            echo '
          <div class="d-inline-block w-100" style="max-width:320px;">
            <input class="form-control '.($err_titre1 ? 'errorInputText' : 'texte').'" type="text" name="titre1" value="'.$fieldb1.'" />
          </div>
        </td>
      </tr>';
        if ($lang[1] != "" && $qtype != TYP_SOCNET) {
            $fieldb2 = str_replace('"','&#34;',$fieldb2);
            echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag2.'</div>
          <div class="d-inline-block w-100" style="max-width:320px;">
            <input class="form-control '.($err_titre2 ? 'errorInputText' : 'texte').'" type="text" name="titre2" value="'.$fieldb2.'" />
          </div>
        </td>
      </tr>';
        }
		if ($qtype == TYP_SOCNET) {
            $socs = ReadDBFields(CONFIGREP.'socnet.dtb');
            echo '
      <tr><td class="text-center pt-2">'.$admin[499].'</td></tr>
      <tr>
        <td class="text-center">
          <div class="d-inline-block">
            <select class="form-control" name="contenu2">
              <option value="">'.$admin[1367].'</option>';
            foreach ($socs as $soc) {
                echo '
              <option value="'.$soc[0].'"'.Selected($soc[0] == strtolower($fieldc2) && $fieldc2 != '').'>'.strtoupper($soc[0]).'</option>'; 
            }
            echo '
            </select>
          </div>
        </td>
      </tr>
      <tr>
        <td class="text-center">
          <div class="d-inline-block"><label for="cat1">'.$admin[467].'</label></div>
          <div class="d-inline-block form-check">
            <input class="form-check-input" style="margin-top: -0.8rem;" type="checkbox"'.Checked($fielda1 == 'on').' name="cat1" id="cat1" />
          </div>
          <div class="d-inline-block ml-5"><label for="cat2">'.$admin[1386].'</label></div>
          <div class="d-inline-block form-check">
            <input class="form-check-input" style="margin-top: -0.8rem;" type="checkbox"'.Checked($fielda2 == 'on').' name="cat2" id="cat2" />
          </div>
        </td>
      </tr>
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[86].' (px)</div>
          <div class="d-inline-block">
            <input style="max-width: 64px;" class="form-control texte" name="titre2" type="text" value="'.$fieldb2.'" />
          </div>
        </td>
      </tr>';
        }
        if (in_array($qtype, array(TYP_RSS, TYP_BSS))) {
            echo '
      <tr>
        <td class="text-center pt-2">
          <div class="d-inline-block">'.$admin[1552].'&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="fieldweb" style="width:140px;">
              <option value="ALL"'.Selected($fieldweb == 'ALL').'>'.$admin[1363].'</option>';
        foreach ($templates as $key=>$value) {
            echo '
              <option value="'.$key.'"'.Selected($fieldweb == $key && $fieldweb != 'ALL').'>'.$key.' - '.$value.'</option>';
        }
        echo '
            </select>
          </div>
        </td>
      </tr>';
        }
    }
    if ($bnouveau == 1) {
        if ($qtype == TYP_FREEBOX) {
			if ($fctwri != 'redac1')
				echo '
      <tr><td class="text-center pt-2">'.$qnouveau.'</td></tr>
      <tr><td class="text-center">'.ShowValidBoxes($fielda1).'</td></tr>';
			else
				echo '
	  <input type="hidden" name="cat1" value="'.$fielda1.'" />';
        } 
        elseif ($qtype == TYP_MENUBOX || $qtype == TYP_DDMENUBOX) {
            echo '
      <tr><td class="text-center pt-2">'.$qnouveau.'</td></tr>
      <tr><td class="text-center">'.ShowValidMenus($fielda1).'</td></tr>';
        }
        elseif ($qtype == TYP_ART) {
			if ($dup == 1) {
                $mbs = ReadAuthorizedIndex(DBMENUBOX, $myname, $grpcol, $fctwri);
                echo '
      <tr>
        <td class="text-center pt-2">
          <div class="d-inline-block">'.$qnouveau.'&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="nouveau1">
              <option value="none"'.Selected($nouveau1 == "none" || $nouveau1 == "").'>'.$admin[681].'</option>';
            foreach ($mbs as $mb) {
                $boxname = $lng == $lang[0] ? $mb[2] : $mb[3];
                $mnu_id  = sprintf('MNU %d', $mb[4]);
                echo '
              <option value="'.$mnu_id.'"'.Selected($nouveau1 == $mnu_id).'>'.$mnu_id.' : '.$boxname.'</option>';
            }
            echo '
            </select>
          </div>
        </td>
      </tr>';
			}
			else echo '
	  <input type="hidden" name="fieldweb" value="'.$fieldweb.'" />';
        } elseif ($qtype == TYP_DNLOAD) {
            echo '
      <tr><td class="text-center pt-2">'.$qnouveau.'</td></tr>
      <tr>
        <td class="text-center">';
            if ($lang[1] != "") {
                echo '
          <div class="d-inline-block">'.$flag1.'&nbsp;</div>';
            }
			$p  = mb_strpos($fieldd1, CONNECTOR);
			$d1 = ($p > 0) ? mb_substr($fieldd1, 0, $p): $fieldd1;
            echo '
          <div class="d-inline-block">
           <input id="nouveau1" class="form-control '.($err_nouveau1 ? 'errorInputText' : 'texte').'" name="nouveau1" 
             style="width:540px;" type="text" value="'.$d1.'" />
          </div>
          <div class="d-inline-block">
            <input type="button" class="btn btn-info bouton" name="browserver1" id="browserver1" 
              onclick="PopupWindow(\''.CHEMIN.'admin/editors/upload/upload.php?lng='.$lng.
                '&uptype=Link&namerepconfig=guppy_config&pathconfig=admin/editors/\',\'nouveau1\',\'700\',\'515\', \'yes\');" 
              value="'.$admin[793].'" />
          </div>
        </td>
      </tr>';
            if ($lang[1] != "") {
				$p  = mb_strpos($fieldd2, CONNECTOR);
				$d2 = ($p > 0) ? mb_substr($fieldd2, 0, $p): $fieldd2;
                echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag2.'&nbsp;</div>
          <div class="d-inline-block">
            <input id="nouveau2" class="form-control '.($err_nouveau2 ? 'errorInputText' : 'texte').'" name="nouveau2" 
              style="width:540px;" type="text" value="'.$d2.'" />
          </div>
          <div class="d-inline-block">
            <input type="button" class="btn btn-info bouton" name="browserver2" id="browserver2" 
              onclick="PopupWindow(\''.CHEMIN.'admin/editors/upload/upload.php?lng='.$lng.
                '&uptype=Link&namerepconfig=guppy_config&pathconfig=admin/editors/\',\'nouveau2\',\'700\',\'515\', \'yes\');" 
              value="'.$admin[793].'" />
          </div>
        </td>
      </tr>';
            }
        }
        elseif ($qtype == TYP_SOCNET) {
            echo '
      <tr><td class="text-center pt-2">'.$qnouveau.'</td></tr>
      <tr>
        <td class="text-center">
          <input class="form-control w-50 mx-auto '.($err_nouveau1 ? 'errorInputText' : 'texte').'" type="text" name="nouveau1" size="45" value="'.$fieldd1.'" />
        </td>
      </tr>';
		}
        else {
			if ($qtype == TYP_OPTDDMENU) {
				echo '
	  <tr>
	    <td class="text-center pt-2">
		  <div style="text-align:center;width:164px;margin:auto;">
		    <div style="float:left;padding-left:36px;">'.$qnouveau.'</div>
            <script>
              $(document).ready(function(){
                $(\'.popadmin10\').popover({
                  title:"'.$qnouveau.'",
                  content:"'.$help[12].'",
                  trigger:"hover",
                  placement:"auto",
                  html:true
                });
              });
            </script>
            <span class="overpop">
              <a class="popadmin10 float-left" title="'.$qnouveau.'">
                <i class="far fa-comment-dots"></i>
              </a>
            </span>
		  </div>
        </td>
      </tr>';
			} else {
				echo '
      <tr><td class="text-center pt-2">'.$qnouveau.'</td></tr>';
			}
			echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag1.'&nbsp;</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_nouveau1 ? 'errorInputText' : 'texte').'" type="text" name="nouveau1" size="55" value="'.$fieldd1.'" />
          </div>
        </td>
      </tr>';
            if ($lang[1] != "") {
                echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag2.'&nbsp;</div>
          <div class="d-inline-block">
            <input class="form-control '.($err_nouveau2 ? 'errorInputText' : 'texte').'" type="text" name="nouveau2" size="55" value="'.$fieldd2.'" />
          </div>
        </td>
      </tr>';
            }
        }
        if ($qtype == TYP_DNLOAD) {
            $dblicences = ReadDbFields(LICENCES);
            echo '
      <tr>
        <td class="text-center pt-2">
          <div class="d-inline-block">'.$admin[717].'&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="fieldweb">
              <option value=""'.Selected($fieldweb == "").'> -- '.$admin[1604].' -- </option>';
            foreach ($dblicences as $lic) {
                echo '
              <option value="'.$lic[0].'"'.Selected($fieldweb == $lic[0]).'>'.$lic[1].'</option>';
            }
            echo '
            </select>
          </div>
        </td>
      </tr>';
        }
        if ($qtype == TYP_OPTMENU || ($qtype == TYP_ART && $dup == 1)) {
            if (strpos($ancre, '#') !== false) $ancre = substr($ancre, 1);
            echo '
      <tr>
        <td class="text-center pt-2">
          <div class="d-inline-block">'.$admin[1534].'&nbsp;</div>
          <div class="d-inline-block">
            <input class="form-control texte" type="text" name="ancre" size="15" value="'.$ancre.'" />
          </div>
        </td>
      </tr>';
        }
    }
    if ($qtype == TYP_FORUM) {
        echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">
            <input class="form-control texte" type="hidden" name="contenu2" size="55" value="'.$fieldc2.'" />
          </div>
          <div class="d-inline-block">
            <input class="form-control texte" type="hidden" name="nouveau2" size="55" value="'.$fieldd2.'" />
          </div>
          <div class="d-inline-block">
            <input class="form-control texte" type="hidden" name="fieldweb" size="55" value="'.$fieldweb.'" />
          </div>
          <div class="d-inline-block">
            <input class="form-control texte" type="hidden" name="fieldmod" size="55" value="'.$fieldmod.'" />
          </div>
        </td>
      </tr>';
    }
    if ($qtype == TYP_OPTMENU) {
        $mbs = ReadDbFields(DBMENUBOX);
        echo '
      <tr>
        <td class="text-center pt-2">
          <div class="d-inline-block">'.$admin[880].'&nbsp;:&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="menubox">';
            foreach ($mbs as $mb) {
                echo '
              <option value="'.$mb[4].'"'.Selected($fieldweb == $mb[4]).'>'.sprintf('MNU %d', $mb[4]).' : '.$mb[$lng == $lang[0] ? 2 : 3].'</option>';
            }
            echo '
            </select>
          </div>
        </td>
      </tr>';
    }
    if ($qtype == TYP_ART && $dup != 1) {
			$mbs = ReadAuthorizedIndex(DBMENUBOX, $myname, $grpcol, $fctwri);
			echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$qnouveau.'</div>
          <div class="d-inline-block">
            <select class="form-control" name="nouveau1">
              <option value="none"'.Selected($fieldd1 == "none" || $fieldd1 == "").'>'.$admin[681].'</option>';
            foreach ($mbs as $mb) {
                $boxname = $lng == $lang[0] ? $mb[2] : $mb[3];
                $mnu_id  = sprintf('MNU %d', $mb[4]);
                echo '
              <option value="'.$mnu_id.'"'.Selected($fieldd1 == $mnu_id).'>'.$mnu_id.' : '.$boxname.'</option>';
            }
            echo '
            </select>
          </div>
        </td>
      </tr>';
    }
    if ($qtype == TYP_ART || $qtype == TYP_OPTMENU) {
		switch ($qtype) {        
        case TYP_ART     : $fname = 'fieldweb'; $value = $fieldweb; break;
		case TYP_OPTMENU : $fname = 'contenu2'; $value = $fieldc2; break;
        }
		if ($fctwri != 'redac1') {
			echo '
      <tr>
        <td class="text-center pt-2">
          <div class="d-inline-block">'.$admin[78].'&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="'.$fname.'">';
			foreach (array(array('S', $admin[1315]), array('W', $admin[1316]), array('L', $admin[1319]), array('R', $admin[1320])) as $opt) {
				echo '
			  <option value="'.$opt[0].'"'.Selected($value == $opt[0]).'>'.$opt[1].'</option>';
			}
			echo '
            </select>
          </div>
        </td>
      </tr>';
		}
		else
			echo '
	  <input type="hidden" name="'.$fname.'" value="'.$value.'">';
	}
    if ($qtype == TYP_OPTMENU || ($qtype == TYP_OPTDDMENU && $typmenu != 'DDL')) {
		if (count($templates) > 1) {
			if ($fctwri != 'redac1') {
				echo '
      <tr>
        <td class="text-center'.($dup == 1 ? ' pt-5' : '').'">
          <div class="d-inline-block">'.$admin[1552].'&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="fieldmail" style="width:140px;">
              <option value="ALL"'.Selected($fieldmail == 'ALL').'>'.$admin[1363].'</option>';
				foreach ($templates as $key=>$value) {
					echo '
              <option value="'.$key.'"'.Selected($fieldmail == $key && $fieldmail != 'ALL').'>'.$key.' - '.$value.'</option>';
				}
				echo '
            </select>
          </div>
        </td>
      </tr>';
			}
			else
				echo '
	  <input type="hidden" name="fieldmail" value="'.$fieldmail.'">';
		} else {
			echo '
	  <input type="hidden" name="fieldmail" value="0">';
		}
	}
    if ($qtype == TYP_ART) {
		if (count($templates) > 1) {
			if ($dup != 1)
				echo '
	  <input type="hidden" name="tconfart" value="'.$tconfart.'">';
			elseif ($fctwri != 'redac1') {
				echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[1552].'&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="tconfart" style="width:140px;">
              <option value="ALL"'.Selected($tconfart == 'ALL').'>'.$admin[1363].'</option>';
				foreach ($templates as $key=>$value) {
					echo '
              <option value="'.$key.'"'.Selected($tconfart == $key && $tconfart != 'ALL').'>'.$key.' - '.$value.'</option>';
				}
				echo '
            </select>
          </div>
        </td>
      </tr>';
			} else
				echo '
	  <input type="hidden" name="tconfart" value="'.$tconfart.'">';
		} else {
			echo '
	  <input type="hidden" name="tconfart" value="0">';
		}
	}
	if (in_array($qtype, array(TYP_ART, TYP_NEWS, TYP_BLOG, TYP_MENUICO, TYP_SOCNET)) || ($qtype == TYP_OPTDDMENU && $typmenu != 'DDL')) {
        switch ($qtype) {        
        case TYP_ART       : 
        case TYP_BLOG      : $label = $admin[461];  $fname = 'nouveau2'; $value = $fieldd2; break;
        case TYP_NEWS      : $label = $admin[462];  $fname = 'nouveau2'; $value = $fieldd2; break;
        case TYP_MENUICO   :
		case TYP_OPTDDMENU : $label = $admin[1240]; $fname = 'contenu2'; $value = $fieldc2; break;
        case TYP_SOCNET    : $label = $admin[829];  $fname = 'nouveau2'; $value = $fieldd2; break;
        }
        echo '
      <tr>
        <td class="text-center pt-2">
          <div class="d-inline-block">'.$label.'&nbsp;</div>
          <div class="d-inline-block">
            <input id="'.$fname.'" class="form-control texte" name="'.$fname.'" style="width:240px;" type="text" value="'.$value.'" />
          </div>
          <div class="d-inline-block">
            <input type="button" class="btn btn-info bouton" name="browserver3" id="browserver3" 
              onclick="PopupWindow(\''.CHEMIN.'admin/editors/upload/upload.php?lng='.$lng.
                '&uptype=Image&namerepconfig=guppy_config&pathconfig=admin/editors/\',\''.$fname.'\',\'700\',\'515\', \'yes\');" 
              value="'.$admin[793].'" />
          </div>
  	    </td>
      </tr>';
    }
    if ($qtype == TYP_MENUICO) {
		echo '
      <tr>
        <td class="text-center pt-2">
          <div class="d-inline-block">'.$admin[1335].'&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="fieldmail" style="width:140px;">
              <option value="ALL"'.Selected($fieldmail == 'ALL').'>'.$admin[1363].'</option>';
        foreach ($templates as $key=>$value) {
            echo '
              <option value="'.$key.'"'.Selected($fieldmail != 'ALL' && $fieldmail == $key).'>'.$key.' - '.$value.'</option>';
        }
        echo '
            </select>
          </div>
        </td>
      </tr>';
    }
    if ($qtype == TYP_MENUICO || ($qtype == TYP_ART && $dup != 1)) {
        switch ($qtype) {        
        case TYP_ART     : $fname = 'tconfart'; $vconf = explode('|', $fieldmail)[0]; break;
        case TYP_MENUICO : $fname = 'fieldweb'; $vconf = $fieldc2; break;
        }
		echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$admin[1552].'&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="'.$fname.'" style="width:140px;">
              <option value="ALL"'.Selected($vconf == 'ALL').'>'.$admin[1363].'</option>';
        foreach ($templates as $key=>$value) {
            echo '
              <option value="'.$key.'"'.Selected($vconf != 'ALL' && $vconf == $key).'>'.$key.' - '.$value.'</option>';
        }
        echo '
            </select>
          </div>
        </td>
      </tr>';
	}
    if ($bcontenu == 1) {
        if (in_array($qtype, array(TYP_OPTMENU, TYP_MENUICO, TYP_SOCNET)) || ($qtype == TYP_OPTDDMENU && $typmenu != 'DDL')) {
			function GetTypMenu($mnu) {
				global $fieldweb;
				ReadDoc($mnu);
				return $fieldweb;
			}
            function GetTargetMenu($mnu) {
				global $fieldc1;
                ReadDoc($mnu);
                return $fieldc1;
            }
			if ($qtype == TYP_OPTMENU && GetTypMenu($menubox) == '1') {
				echo '
      <tr><td class="text-center">'.$admin[1234].'</td></tr>';
				$contenu1 = '_self';
            } else {
				$target = GetTargetMenu($id);
                echo '
      <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$qcontenu.'&nbsp;</div>
          <div class="d-inline-block">
            <select class="form-control" name="contenu1">
              <option value="_self"'.Selected($target == '_self').'>'.$admin[1232].'</option>
              <option value="_blank"'.Selected($target == '_blank').'>'.$admin[1233].'</option>';
				if ($qtype != TYP_SOCNET) {
					echo '
              <option value="_popup"'.Selected($target == '_popup').'>'.$admin[1243].'</option>';
				}
                echo '
            </select>
          </div>
        </td>
      </tr>';
            }
        }
        else {
            if ($qtype == TYP_ART && $serviz[113] != 'on') {
				echo '
      <tr><td class="text-center pt-2">'.$admin[1347].'</td></tr>
	  <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag1.'&nbsp;</div>
          <div class="d-inline-block">
		    <input class="form-control texte" type="text" name="keywords1" size="70" value="'.$keywords1.'" />
          </div>
        </td>
	  </tr>';
				if ($lang[1] != "") {
					echo '
	  <tr>
        <td class="text-center">
          <div class="d-inline-block">'.$flag2.'&nbsp;</div>
          <div class="d-inline-block">
		    <input class="form-control texte" type="text" name="keywords2" size="70" value="'.$keywords2.'" />
          </div>
        </td>
	  </tr>';
				}
			}
            echo '
      <tr><td class="text-center pt-2">'.$qcontenu.'</td></tr>
      <tr><td class="text-center">'.$flag1.'</td></tr>
	  <tr>
	    <td class="text-center">
		  <div class="text-center w-100 w-admeditor mx-auto">';
            $hedit = in_array($qtype, array(TYP_MENUBOX, TYP_PHOTO, TYP_THINK)) ? '120px' : '320px';
            display_admin_editor('contenu1', '100%', $hedit, empty($erreur) ? $fieldc1 : $contenu1);
            echo '
          </div>';
            if ($qtype == TYP_FORUM && $fieldb1 != "") {
                echo '
		  <div class="text-center">
            <div class="d-inline-block">'.$admin[1607].'&nbsp;:&nbsp;</div>
            <div class="d-inline-block">
		      <select class="form-control align-middle" name="fieldmod">
			    <option value=""'.Selected($fieldmod == '').'>'.$admin[1608].'</option>
			    <option value="c"'.Selected($fieldmod != '').'>'.$admin[1609].'</option>
			  </select>
            </div>
		  </div>';
            }
            if ($qtype == TYP_FORUM) {
                echo '
		  <div class="text-center">
            <div class="d-inline-block">'.$admin[173].'&nbsp;</div>
            <div class="d-inline-block">
              <input type="text" class="form-control texte" name="fieldweb" value="'.$fieldweb.'" size="60" />
            </div>
          </div>';
            }
			echo '
		</td>
	  </tr>';
            if ($lang[1] != "") {
                echo '
      <tr><td class="text-center pt-2">'.$flag2.'</td></tr>
      <tr>
        <td class="text-center">
		  <div class="text-center w-100 w-admeditor mx-auto">';
                display_admin_editor('contenu2', '100%', $hedit, empty($erreur) ? $fieldc2 : $contenu2);
				echo '
          </div>
		</td>
	  </tr>';
            }
        }
    }
    echo '
    </table>
    </div>';
    echo '
    </fieldset>
    <div class="quest mt-3" style="padding:2px 20px;">';
	if (in_array($qtype, array(TYP_FREEBOX, TYP_MENUBOX, TYP_DDMENUBOX, TYP_SOCNET, TYP_BANNER, TYP_THINK, TYP_RSS)))
		echo '
      <div class="float-left mt-3">
	    <a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg=config6&amp;selskin='.$page[14].'&amp;conf='.$tconfig.'">
          '.$admin[346].'&nbsp;<i class="gen-cfgboites align-top" title="'.$admin[346].'"></i>
	    </a>&nbsp;||&nbsp;
	    <a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg=config3&amp;selskin='.$page[14].'&amp;conf='.$tconfig.'">
          '.$admin[469].'&nbsp;<i class="gen-cfgaccueil align-top" title="'.$admin[469].'"></i>
	    </a>
      </div>';
	echo '
      <div class="float-right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div>'.
	  ($qseeall == '' ? '' : '
      <div class="float-right pt-3" style="margin-right:24px;">
        <label for="retform2">'.$admin[857].$qseeall.'</label>
        <input type="checkbox" id="retform2" name="retform2" onchange="setret(this.form);" />
      </div>').'
      <div class="float-right pt-3" style="margin-right:24px;">
        <label for="majfile2">'.$admin[1381].'</label>
        <input type="checkbox" checked="checked" id="majfile2" name="majfile2" onchange="setmaj(this.form);" />
      </div>'.
	  ($qtype == TYP_ART ? '
      <div class="float-right pt-3" style="margin-right:24px;">
        <label for="svart2">'.$admin[1014].' admins&nbsp;</label>
        <input type="checkbox" id="svart2" name="svart2" onchange="setart(this.form);" />
      </div>' : '').'
      <div class="clearfix"></div>
    </div>
  </form>';
}
################################################################################
elseif ($form == 3) {
    include('inc/cal.inc');
}
################################################################################
echo '
</div>
<hr />
<p class="text-center">';

btable();
$lang[0] = $langue0;
$lang[1] = $langue1;
include CHEMIN.'admin/bpage.inc';
?>