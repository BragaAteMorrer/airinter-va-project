<?php
/*******************************************************************************
 *   Stats calculation
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

if (stristr($_SERVER['SCRIPT_NAME'], 'statcalc.inc')) {
    header('location:../index.php');
    die();
}

if (!isset($npage)) {
    include(CHEMIN.INCREP."lang/".$lng."-web".INCEXT);
    if ($lng == $lang[0]) {
        $npage = array($web[169], getLabel($nom[1]), getLabel($nom[24]), getLabel($nom[22]), getLabel($nom[9]), getLabel($nom[0]), getLabel($nom[3]),
            getLabel($nom[7]), getLabel($nom[2]), '', getLabel($nom[20]), getLabel($nom[26]), $web[144], getLabel($nom[42]),
            $web[485], $web[486], $web[487], $web[488], $web[489], $web[490]
            );
    } else {
        $npage = array($web[169], getLabel($nom[11]), getLabel($nom[25]), getLabel($nom[23]), getLabel($nom[19]), getLabel($nom[10]), getLabel($nom[13]),
            getLabel($nom[17]), getLabel($nom[12]), '', getLabel($nom[21]), getLabel($nom[27]), $web[144], getLabel($nom[43]),
            $web[485], $web[486], $web[487], $web[488], $web[489], $web[490]
            );
    }
}

function CalcDay($file = '') {
    if (empty($file)) {
        $statslog = ReadDBFields(DBLOGD);
        $dbt1 = ReadDBFields(DBLOGH);
        for ($i = 0; $i < STATS_NBPAGE;     $i++)   { $statslog[1][$i] = $statslog[1][$i]+$dbt1[1][$i]; }
        for ($i = 0; $i < STATS_NBLANGUE;   $i++)   { $statslog[2][$i] = $statslog[2][$i]+$dbt1[2][$i]; }
        for ($i = 0; $i < STATS_NBBROWSER;  $i++)   { $statslog[3][$i] = $statslog[3][$i]+$dbt1[3][$i]; }
        for ($i = 0; $i < STATS_NBOS;       $i++)   { $statslog[4][$i] = $statslog[4][$i]+$dbt1[4][$i]; }
        $statslog[5][$dbt1[0][0]] = $statslog[5][$dbt1[0][0]]+$dbt1[5][0];
        $statslog[6][$dbt1[0][0]] = $statslog[6][$dbt1[0][0]]+$dbt1[6][0];
        return $statslog;
    } else {
        return ReadDBFields(OLDSSTATSREP.$file);
    }
}

function CalcMonth($file = '') {
    if (empty($file)) {
        $statslog = ReadDBFields(DBLOGM);
        $dbt1 = ReadDBFields(DBLOGH);
        $dbt2 = ReadDBFields(DBLOGD);
        for ($i = 0; $i < STATS_NBPAGE;     $i++)   { $dbt2[1][$i] = $dbt2[1][$i]+$dbt1[1][$i]; }
        for ($i = 0; $i < STATS_NBLANGUE;   $i++)   { $dbt2[2][$i] = $dbt2[2][$i]+$dbt1[2][$i]; }
        for ($i = 0; $i < STATS_NBBROWSER;  $i++)   { $dbt2[3][$i] = $dbt2[3][$i]+$dbt1[3][$i]; }
        for ($i = 0; $i < STATS_NBOS;       $i++)   { $dbt2[4][$i] = $dbt2[4][$i]+$dbt1[4][$i]; }
        $dbt2[5][$dbt1[0][0]] = $dbt2[5][$dbt1[0][0]]+$dbt1[5][0];
        $dbt2[6][$dbt1[0][0]] = $dbt2[6][$dbt1[0][0]]+$dbt1[6][0];
        for ($i = 0; $i < STATS_NBPAGE;     $i++)   { $statslog[1][$i] = (isset($statslog[1][$i]) ? $statslog[1][$i] : 0) + (isset($dbt2[1][$i]) ? $dbt2[1][$i] : 0); }
        for ($i = 0; $i < STATS_NBLANGUE;   $i++)   { $statslog[2][$i] = $statslog[2][$i]+$dbt2[2][$i]; }
        for ($i = 0; $i < STATS_NBBROWSER;  $i++)   { $statslog[1][$i] = (isset($statslog[3][$i]) ? $statslog[3][$i] : 0) + (isset($dbt2[3][$i]) ? $dbt2[3][$i] : 0); }
        for ($i = 0; $i < STATS_NBOS;       $i++)   { $statslog[4][$i] = $statslog[4][$i]+$dbt2[4][$i]; }
        for ($i = 0; $i < STATS_NBHEURE;    $i++)   { $statslog[5][$dbt2[0][0]-1] = $statslog[5][$dbt2[0][0]-1]+$dbt2[5][$i]; }
        for ($i = 0; $i < STATS_NBHEURE;    $i++)   { $statslog[6][$dbt2[0][0]-1] = $statslog[6][$dbt2[0][0]-1]+$dbt2[6][$i]; }
        return $statslog;
    } else {
        return ReadDBFields(OLDSSTATSREP.$file);
    }
}

function CalcYear($file = '') {
    if (empty($file)) {
        $statslog = ReadDBFields(DBLOGY);
        $dbt1 = ReadDBFields(DBLOGH);
        $dbt2 = ReadDBFields(DBLOGD);
        $dbt3 = ReadDBFields(DBLOGM);
        for ($i = 0; $i < STATS_NBPAGE;     $i++)   { $dbt2[1][$i] = $dbt2[1][$i]+$dbt1[1][$i]; }
        for ($i = 0; $i < STATS_NBLANGUE;   $i++)   { $dbt2[2][$i] = $dbt2[2][$i]+$dbt1[2][$i]; }
        for ($i = 0; $i < STATS_NBBROWSER;  $i++)   { $dbt2[3][$i] = $dbt2[3][$i]+$dbt1[3][$i]; }
        for ($i = 0; $i < STATS_NBOS;       $i++)   { $dbt2[4][$i] = $dbt2[4][$i]+$dbt1[4][$i]; }
        $dbt2[5][$dbt1[0][0]] = $dbt2[5][$dbt1[0][0]]+$dbt1[5][0];
        $dbt2[6][$dbt1[0][0]] = $dbt2[6][$dbt1[0][0]]+$dbt1[6][0];
        for ($i = 0; $i < STATS_NBPAGE;     $i++)   { $dbt3[1][$i] = $dbt3[1][$i]+$dbt2[1][$i]; }
        for ($i = 0; $i < STATS_NBLANGUE;   $i++)   { $dbt3[2][$i] = $dbt3[2][$i]+$dbt2[2][$i]; }
        for ($i = 0; $i < STATS_NBBROWSER;  $i++)   { $dbt3[3][$i] = $dbt3[3][$i]+$dbt2[3][$i]; }
        for ($i = 0; $i < STATS_NBOS;       $i++)   { $dbt3[4][$i] = $dbt3[4][$i]+$dbt2[4][$i];}
        for ($i = 0; $i < STATS_NBHEURE;    $i++)   { $dbt3[5][$dbt2[0][0]-1] = $dbt3[5][$dbt2[0][0]-1]+$dbt2[5][$i]; }
        for ($i = 0; $i < STATS_NBHEURE;    $i++)   { $dbt3[6][$dbt2[0][0]-1] = $dbt3[6][$dbt2[0][0]-1]+$dbt2[6][$i]; }
        for ($i = 0; $i < STATS_NBPAGE;     $i++)   { $statslog[1][$i] = $statslog[1][$i]+$dbt3[1][$i]; }
        for ($i = 0; $i < STATS_NBLANGUE;   $i++)   { $statslog[2][$i] = $statslog[2][$i]+$dbt3[2][$i]; }
        for ($i = 0; $i < STATS_NBBROWSER;  $i++)   { $statslog[3][$i] = $statslog[3][$i]+$dbt3[3][$i]; }
        for ($i = 0; $i < STATS_NBOS;       $i++)   { $statslog[4][$i] = $statslog[4][$i]+$dbt3[4][$i]; }
        for ($i = 0; $i < STATS_NBJOUR;     $i++)   { $statslog[5][$dbt3[0][0]-1] = $statslog[5][$dbt3[0][0]-1]+$dbt3[5][$i]; }
        for ($i = 0; $i < STATS_NBJOUR;     $i++)   { $statslog[6][$dbt3[0][0]-1] = $statslog[6][$dbt3[0][0]-1]+$dbt3[6][$i]; }
        return $statslog;
    } else {
        return ReadDBFields(OLDSSTATSREP.$file);
    }
}

function AnalyzeDay($statslog) {
    $nhour = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23);
    $shit = array();
    $snbpage = array();
    $maxshit = 1;
    $maxsnbpage = 1;
    for ($j = 0; $j < count($nhour); $j++) {
        $shit[$j][0] = $statslog[6][$j];
        $shit[$j][1] = $nhour[$j];
        $maxshit = Max($maxshit,$shit[$j][0]);
        $snbpage[$j][0] = $statslog[5][$j];
        $snbpage[$j][1] = $nhour[$j];
        $maxsnbpage = Max($maxsnbpage,$snbpage[$j][0]);
    }
    return array(
        'max1' => $maxshit,
        'tab1' => $shit,
        'max2' => $maxsnbpage,
        'tab2' => $snbpage
        );
}

function AnalyzeMonth($statslog) {
    $nmonth = array();
    $nbday = empty($statslog[0][2]) ? 0 : date('t', mktime(0, 0, 0, intval(substr($statslog[0][2], 5, 2)), 2, intval(substr($statslog[0][2], 0, 4))));
    for ($n = 0; $n < $nbday; $n++) {
        $nmonth[$n] = $n + 1;
    }
    $shit = array();
    $snbpage = array();
    $maxshit = 1;
    $maxsnbpage = 1;
    for ($j = 0; $j < count($nmonth); $j++) {
        $shit[$j][0] = $statslog[6][$j];
        $shit[$j][1] = $nmonth[$j];
        $maxshit = Max($maxshit,$shit[$j][0]);
        $snbpage[$j][0] = $statslog[5][$j];
        $snbpage[$j][1] = $nmonth[$j];
        $maxsnbpage = Max($maxsnbpage,$snbpage[$j][0]);
    }
    return array(
        'max1' => $maxshit,
        'tab1' => $shit,
        'max2' => $maxsnbpage,
        'tab2' => $snbpage
        );
}

function AnalyzeYear($statslog) {
    $nyear = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
    $shit = array();
    $snbpage = array();
    $maxshit = 1;
    $maxsnbpage = 1;
    for ($j = 0; $j < count($nyear); $j++) {
        $shit[$j][0] = $statslog[6][$j];
        $shit[$j][1] = $nyear[$j];
        $maxshit = Max($maxshit,$shit[$j][0]);
        $snbpage[$j][0] = $statslog[5][$j];
        $snbpage[$j][1] = $nyear[$j];
        $maxsnbpage = Max($maxsnbpage,$snbpage[$j][0]);
    }
    return array(
        'max1' => $maxshit,
        'tab1' => $shit,
        'max2' => $maxsnbpage,
        'tab2' => $snbpage
        );
}

function compare($a, $b) {
	if ($a[0] == $b[0]) {
		return strtolower($a[1]) > strtolower($b[1]) ? +1 : -1;

	} else {
		return $a[0] < $b[0] ? +1 : -1;
	}
}

function AnalyzeLang($statslog) {
    global $npage, $show_stat_admin, $lang;
    $slang = array();
    $maxslang = 1;
    for ($j = 0; $j < count($lang); $j++) {
        $slang[$j][0] = $statslog[2][$j];
        $slang[$j][1] = $lang[$j];
        $maxslang = Max($maxslang,$slang[$j][0]);
    }
    @rsort($slang);
    $spage = array();
    $maxspage = 1;
    for ($j = 0; $j < count($npage); $j++) {
        if (12 == $j && isset($show_stat_admin) && !$show_stat_admin ) continue;
        $spage[$j][0] = $statslog[1][$j];
        $spage[$j][1] = $npage[$j];
        $maxspage = Max($maxspage,$spage[$j][0]);
    }
    if (isset($show_stat_admin) && !$show_stat_admin) {
        unset($spage[12]);
        $spage = array_values($spage);
    }
    usort($spage, 'compare');
    return array(
        'max1' => $maxslang,
        'tab1' => $slang,
        'max2' => $maxspage,
        'tab2' => $spage
        );
}

function AnalyzeNavOs($statslog) {
    $nnav = array('Netscape', 'MSIE', 'Mozilla', 'DA', 'Opera', 'iCab', 'Konqueror', 'Robot', 'Other',
        'Wget', 'Avant Browser', 'Crazy Browser', 'Maxthon', 'Lynx', 'Safari', 'Seamonkey',
        'Playstation portable', 'Playstation 3', 'Nintendo Wii', 'Firefox', 'Flock',
        'Iceweasel', 'Chrome', 'Camino', 'Dillo', 'Element Browser', 'Epiphany',
        'Galeon', 'K-Meleon', 'Kazehakase', 'Minifield');
    $nos = array('Windows', 'Mac', 'Linux', 'FreeBSD', 'SunOS', 'IRIX', 'BeOS', 'OS/2', 'AIX', 'Other');
    $snav = array();
    $maxsnav = 1;
    $sos = array();
    $maxsos = 1;
    for ($j = 0; $j < count($nnav); $j++) {
        $snav[$j][0] = $statslog[3][$j];
        $snav[$j][1] = $nnav[$j];
        $maxsnav = Max($maxsnav,$snav[$j][0]);
    }
    usort($snav, 'compare');
    for ($j = 0; $j < count($nos); $j++) {
        $sos[$j][0] = $statslog[4][$j];
        $sos[$j][1] = $nos[$j];
        $maxsos = Max($maxsos,$sos[$j][0]);
    }
    @rsort($sos);
    return array(
        'max1' => $maxsnav,
        'tab1' => $snav,
        'max2' => $maxsos,
        'tab2' => $sos
        );
}

function ListStatsFiles() {
    $files = ExploreFile(STATSREP.'olds/');
    rsort($files);
    $foptions = array();
    foreach ($files as $file) {
        if ($file == 'index.php') continue;
        $name = substr($file, 0, -4);
        switch (strlen($name)) {
        case 4  : $foptions[$name] = '3,'.$file; break;
        case 7  : $foptions[$name] = '2,'.$file; break;
        case 10 : $foptions[$name] = '1,'.$file; break;
        default : $foptions[$name] = '0,'.$file;
        }
    }
    return $foptions;
}
?>