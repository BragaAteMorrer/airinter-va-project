<?php
/*******************************************************************************
 *   Integrated calendar
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2022 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.10 (September 22, 2022) : update bootstrap, font-awesome
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '../');
define('NO_CRYPT', true);
include CHEMIN.'inc/includes.inc';
$annee   = htmlentities(import('annee'), ENT_QUOTES, $charset);
$annee   = empty($annee) ? date('Y') : $annee;
$mois    = htmlentities(import('mois'), ENT_QUOTES, $charset);
$mois    = empty($mois) ? date('m') : $mois;
$tconfig = import('tconfig', '', true, 0);
$pos     = htmlentities(import('pos', '', true, 'L'), ENT_QUOTES, $charset);
$nbox    = import('nbox');

echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Blog Calendar</title>
<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'" />
<meta name="Robots" content="None" />';

if (file_exists($meskin."style.css")) echo '
<link type="text/css" rel="stylesheet" href="'.$meskin.'style.css">';
else echo '
<link type="text/css" rel="stylesheet" href="no_skin/style.css">';
if (file_exists($meskin."styleplus.css")) echo '
<link type="text/css" rel="stylesheet" href="'.$meskin.'styleplus.css">';

echo '
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" 
  integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css" 
  integrity="sha512-rt/SrQ4UNIaGfDyEXZtNcyWvQeOq0QLygHluFQcSjaGB04IxWhal71tKuzP6K8eYXYB6vJV4pHkXcmFGGQ1/0w==" crossorigin="anonymous" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" 
  integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.bundle.min.js" 
	integrity="sha512-igl8WEUuas9k5dtnhKqyyld6TzzRjvMqLC79jkgT3z02FvJyHAuUtyemm/P/jYSne1xwFI06ezQxEwweaiV7VA==" crossorigin="anonymous">
</script>
';

echo '
</head>
<body class="bg-inherit" onload="parent.adjustMyFrameHeight'.$nbox.$pos.$tconfig.'();">';

include_once CHEMIN.'inc/lang/'.$lng.'-special.inc';
$sel    = import('sel');
$datej  = import('datej');
$jour        = date('d');
$joursmois   = date('t',mktime(0,0,0,intval($mois),1,intval($annee)));
$premierjour = date('w',mktime(0,0,0,intval($mois),1,intval($annee)));
$mois        = date('m',mktime(0,0,0,intval($mois),1,intval($annee)));
$annee       = date('Y',mktime(0,0,0,intval($mois),1,intval($annee)));
$jourj       = date('d',mktime(0,0,0,intval($mois),intval($jour),intval($annee)));
$moism       = date('m');
$anneea      = date('Y');
if ($premierjour == 0) $premierjour = 7;
$semainepays = $web[153];
$joursemaine = $semainepays == 0 ?
    array(1 => $web[147], $web[148], $web[149], $web[150], $web[151], $web[152], $web[146]) :
    array(1 => $web[146], $web[147], $web[148], $web[149], $web[150], $web[151], $web[152]);
$moispays = array(1 => $web[268], $web[269], $web[270], $web[271], $web[272], $web[273], $web[274], $web[275], $web[276], $web[277], $web[278], $web[279]);
$nommois  = $moispays[$mois*$mois/$mois];
$adate    = getdate(time());
$mois_ci  = $adate['mon'];
$ann_ci   = $adate['year'];
if ($mois == 1) {
    $mois_p = 12;
    $ann_p  = $annee - 1;
    $mois_ci = $mois_p;
    $ann_ci  = $ann_p;
}
else {
    $mois_p = $mois - 1;
    $ann_p  = $annee;
    $mois_ci = $mois_p;
    $ann_ci  = $ann_p;
}
if ($mois == 12) {
    $mois_s = 1;
    $ann_s  = $annee + 1;
    $mois_ci = $mois_s;
    $ann_ci  = $ann_s;
}
else {
    $mois_s = $mois + 1;
    $ann_s  = $annee;
    $mois_ci = $mois_s;
    $ann_ci  = $ann_s;
}
$mois_ci = sprintf('%02d', $mois_ci);
$mois_p  = sprintf('%02d', $mois_p);
$mois_s  = sprintf('%02d', $mois_s);
$nbjours = date('d',mktime(0,0,0,intval($mois_s),0,intval($annee)));
$l       = ($lng == $lang[0]) ? 0 : 1;
$asite19 = explode('|', $site[19]);
if (!function_exists('MyMonth')) {
    function MyMonth($month, $year, $separator = '-') {
        global $site;
        if ((@$asite19[$l] == 'E1') || (@$asite19[$l] == 'E2')) {
            return $month . $separator . $year;
        } else {
            return $year . $separator . $month;
        }
    }
}
$dat_max = date('Y').date('m');
$dbwork  = ReadDBFields(DBBLOG);
$dat_min = count($dbwork) == 0 ? $dat_max : substr($dbwork[0][5], 0, 6);
$dat_p   = $ann_p.$mois_p;
$dat_s   = $ann_s.$mois_s;
$out     = '';
$tdleft  = '';
if ($dat_p >= $dat_min) $tdleft = '
    <a class="btn btn-md" href="'.($site['URLR'] =='on' 
        ? $lng.'-'.$urlrw[1].'-510-'.$ann_p.'-'.$mois_p 
        : 'blogcal.php?lng='.$lng.'&amp;annee='.$ann_p.'&amp;mois='.$mois_p).'" title="'.MyMonth($mois_p, $ann_p).'">
      <i class="fas fa-caret-left"></i>
    </a>';
$tdright = '';
if ($dat_s <= $dat_max) $tdright = '
    <a class="btn btn-md" href="'.($site['URLR'] =='on' 
        ? $lng.'-'.$urlrw[1].'-510-'.$ann_s.'-'.$mois_s 
        : 'blogcal.php?lng='.$lng.'&amp;annee='.$ann_s.'&amp;mois='.$mois_s).'" title="'.MyMonth($mois_s, $ann_s).'">
      <i class="fas fa-caret-right"></i>
    </a>';
echo '
  <div class="table-responsive-md">
  <table class="cal text-center m-auto">
    <tr class="cal">
      <td class="cal3BlogW pt-2">'.$tdleft.'</td>
      <td colspan="5" class="text-center cal3BlogW pt-2">
        <strong>
          <a class="calagd" href="'.CHEMIN.($site['URLR'] =='on' 
              ? $lng.'-'.$urlrw[1].'-5-mois-'.$ann_ci.$mois 
              : $site['BLS'].'.php?lng='.$lng.'&amp;sel=mois&amp;date='.$annee.$mois).$z2.'" title="'.MyMonth($mois, $ann_ci).'">
            '.$nommois.' '.$annee.'
          </a>
        </strong>
      </td>
      <td class="cal3BlogW pt-2">'.$tdright.'</td>
    </tr>
    <tr class="cal">
      <td colspan="7" class="cal calBlogW">
	    <form name="selmois" method="POST" 
            action="'.($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[1].'-510-'.$annee.'-'.$mois 
              : 'blogcal.php?lng='.$lng.'&amp;date='.$annee.$mois).'">
	      <div class="float-left">
	        <select class="form-control cal selMonthCal" name="mois" onchange="this.form.submit();">';
foreach($moispays as $key=>$mnth)
    echo '
	          <option value="'.$key.'"'.Selected($key == $mois).'>'.$mnth.'</option>';
echo '
		    </select>
		  </div>
	      <div class="float-right">
	        <select class="form-control cal selYearCal" name="annee" onchange="this.form.submit();">';
$dbannee = array();
$datesin = array();
foreach ($dbwork as $work) {
    $dbannee[] = substr($work[5], 0, 4);
    $datesin[] = substr($work[5], 0, 6);
}
$dbannee[] = date('Y');
$dbannee   = array_unique($dbannee);
$datesin[] = date('Y').date('m');
$datesin   = array_unique($datesin);
foreach($dbannee as $an)
    echo '
	          <option value="'.$an.'"'.Selected($an == $annee).'>'.$an.'</option>';
    echo '
		    </select>
		  </div>
        </form>
	  </td>
    </tr>
    <tr>';
for ($nbrejours = 1; $nbrejours < 8; $nbrejours++) {
    echo '
      <td class="cals text-center pb-2"><strong>'.$joursemaine[$nbrejours].'</strong></td>';
}
echo '
    </tr>
    <tr>';
$max = (($joursmois + $premierjour + $semainepays - 1) % 7) == 0 ?
    7 * floor(($joursmois + $premierjour + $semainepays) / 7) :
    7 * ceil(($joursmois + $premierjour + $semainepays) / 7);
for ($i = 1; $i <= $max; $i++) {
    $a = $jour = $i - $premierjour + 1 - $semainepays;
    if ($i < $premierjour || $jour > $joursmois || $a == '') {
        if  (!($premierjour == 7 && $semainepays == 1)) echo '
      <td class="cal0 cal0BlogW">&nbsp;</td>';
    }
    elseif ($jour > $nbjours) {
        echo '
      <td class="cal0 cal0BlogW">&nbsp;</td>';
    }
    else {
        $a    = sprintf('%02d', $a);
        $jour = sprintf('%02d', $jour);
        if (cal_ferie($jour, $mois, $annee)) {
            $textecal = $a;
            $stylecal = ($a == $jourj && $mois == $moism && $annee == $anneea) ? 'cal2' : 'cal3';
        }
        else {
            $textecal = $a;
			if ($jourj == $a) {
                if (($i%7) == $semainepays) $stylecal = ($mois == $moism && $annee == $anneea ? 'cal4' : 'cal3');
                else $stylecal = ($mois == $moism && $annee == $anneea ? 'cal2' : 'cal1');
            }
            else $stylecal = (($i%7) == $semainepays ? 'cal3' : 'cal1');
        }
        $jok = FALSE;
        for ($k = 0; $k < count($dbwork); $k++) {
            $datebi = substr($dbwork[$k][5], 0, 8);
            if ($datebi == $annee.$mois.$a) {
                if (isAccessGranted($dbwork[$k][6]) && isAuthorizedPublication($dbwork[$k][5])){
                    $jok = TRUE;
                    $datej = $datebi;
                }
                break;
            }
        }
        if ($jok) echo '
      <td class="calevt cal0BlogW">
        <a href="'.CHEMIN.($site['URLR'] =='on' 
            ? $lng.'-'.$urlrw[1].'-6-jour-'.$datej 
            : $site['BLS'].'.php?lng='.$lng.'&amp;sel=jour&amp;datej='.$datej).$z2.'" title="'.substr(formatDate($datej), 0, 10).'">
          '.$textecal.'
        </a>
      </td>';
        else echo '
      <td class="'.$stylecal.' cal0BlogW">'.$textecal.'</td>';
    }
    if (($i%7) == 0 and $i != $max) echo '
    </tr>
    <tr>';
}
echo '
    </tr>
  </table>
  </div>
</body>
</html>';
?>
