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
 * v6.00.10 (September 22, 2022) : update font-awesome, bootstrap
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '../');
define('NO_CRYPT', true);
include CHEMIN.'inc/includes.inc';
include CHEMIN.'inc/lang/'.$lng.'-special.inc';

$annee   = htmlentities(import('annee'), ENT_QUOTES, $charset);
$mois    = htmlentities(import('mois'), ENT_QUOTES, $charset);
$tconfig = $serviz[171] == 'ALL' ? import('tconfig', '', true, 0) : $serviz[171];
$pos     = htmlentities(import('pos', '', true, 'L'), ENT_QUOTES, $charset);
$nbox    = import('nbox');

echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Calendar</title>
<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'" />
<meta name="Robots" content="None" />';

if (file_exists($meskin."style.css")) echo '
<link rel="stylesheet" href="'.$meskin.'style.css">';
else echo '
<link rel="stylesheet" href="no_skin/style.css">';
if (file_exists($meskin."styleplus.css")) echo '
<link rel="stylesheet" href="'.$meskin.'styleplus.css">';

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

$l = $lng == $lang[0] ? 0 : 1;
$asite19 = explode('|', $site[19]);

if (empty($annee)) $annee = date('Y');
if (empty($mois))  $mois = date('m');
$jour           = date('d');
$joursmois      = date('t', mktime(0, 0, 0, (int)$mois, 1, (int)$annee));
$premierjour    = date('w', mktime(0, 0, 0, (int)$mois, 1, (int)$annee));
$mois           = date('m', mktime(0, 0, 0, (int)$mois, 1, (int)$annee));
$annee          = date('Y', mktime(0, 0, 0, (int)$mois, 1, (int)$annee));
$jourj          = date('d', mktime(0, 0, 0, (int)$mois, (int)$jour, (int)$annee));
if ($premierjour == 0) {
    $premierjour = 7;
}
$semainepays = $web[153];
if ($semainepays == 0) {
    $joursemaine = array(1 => $web[147], $web[148], $web[149], $web[150], $web[151], $web[152], $web[146]);
}
else {
    $joursemaine = array(1 => $web[146], $web[147], $web[148], $web[149], $web[150], $web[151], $web[152]);
}
$moispays = array(1 => $web[268], $web[269], $web[270], $web[271], $web[272], $web[273], $web[274], $web[275], $web[276], $web[277], $web[278], $web[279]);
$nommois = $moispays[$mois*$mois/$mois];
$date = getdate(time());
$mois_ci = $date['mon'];
if ($mois_ci < 10) {
    $mois_ci = '0'.$mois_ci;
}
$ann_ci = $date['year'];
if ($mois == 1) {
    $mois_p = 12;
    $ann_p = $annee - 1;
}
else {
    $mois_p = $mois - 1;
    $ann_p = $annee;
}
if ($mois_p <10) {
    $mois_p = '0'.$mois_p;
}
if ($mois == 12) {
    $mois_s = 1; $ann_s = $annee + 1;
}
else {
    $mois_s = $mois + 1;
    $ann_s = $annee;
}
if ($mois_s <10) {
    $mois_s = '0'.$mois_s;
}
$dbwork = ReadDBFields(DBAGENDA);
$dbannee = array();
$dbworkag = array();
foreach($dbwork as $dbw) {
	$an = substr($dbw[2], -4);
	$mt = substr($dbw[2], 0, 2);
	if ($an == $annee && $mt == $mois) $dbworkag[] = $dbw;
	if (!in_array($an, $dbannee)) $dbannee[] = $an;
}
$an = date('Y');
if (!in_array($an, $dbannee)) $dbannee[] = $an;
sort($dbannee);
$nbjours = date('d',mktime(0, 0, 0,(int)$mois_s, 0, (int)$annee));
$idpg = '';
echo '
<div class="table-responsive-md">
<table class="table table-borderless cal TCAL'.$pos.$tconfig.' text-center m-auto w-100">';

if (($asite19[$l] == 'E1') || ($asite19[$l] == 'E2')) {
	echo '
  <tr class="cal TCAL'.$pos.$tconfig.'">
    <td class="leftNavCalW">';
	if ($dbannee[0] <= $ann_p) { echo '
      <a class="btn btn-md" href="'.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[17].'-2-'.$mois_p.'-'.$ann_p 
          : 'calendar.php?lng='.$lng.'&amp;mois='.$mois_p.'&amp;annee='.$ann_p).'" title="'.$mois_p.'-'.$ann_p.'">
        <i class="fas fa-caret-left"></i>
      </a>';
	}
	echo '
    </td>
    <td colspan="5" class="midNavCalW">
      <a class="calagd" 
         href="'.CHEMIN.($site['URLR'] == 'on'         
          ? $lng.'-'.$urlrw[17].'-21-'.$mois.'-'.$annee.'-2-'.$tconfig 
          : 'agenda.php?lng='.$lng.'&amp;mois='.$mois.'&amp;an='.$annee.'&amp;agv=2&amp;tconfig='.$tconfig).$z2.'" target="_parent" title="'.$web[287].' '.$mois.'-'.$annee.'">
        <b>'.$nommois.' '.$annee.'</b>
      </a>
	</td>
    <td class="rightNavCalW">';
	if ($dbannee[count($dbannee)-1] >= $ann_s) {
		echo '
      <a class="btn btn-md" 
         href="'.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[17].'-2-'.$mois_s.'-'.$ann_s 
          : 'calendar.php?lng='.$lng.'&amp;mois='.$mois_s.'&amp;annee='.$ann_s).'" title="'.$mois_s.'-'.$ann_s.'">
        <i class="fas fa-caret-right"></i>
      </a>';
	}
	echo '
    </td>
  </tr>
  <tr class="cal calsel TCAL'.$pos.$tconfig.'">
    <td colspan="7" class="cal TCAL'.$pos.$tconfig.' mnthyearCalW">
	  <form name="selmois" method="POST" 
        action="'.($site['URLR'] == 'on' ? 
          $lng.'-'.$urlrw[17].'-1-'.$mois.'-'.$annee.'-'.$pos.'-'.$tconfig 
          : 'calendar.php?lng='.$lng.'&amp;mois='.$mois.'&amp;annee='.$annee.'&amp;pos='.$pos.'&amp;tconfig='.$tconfig).'"
      >
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
	foreach($dbannee as $an)
		echo '
	        <option value="'.$an.'"'.Selected($an == $annee).'>'.$an.'</option>';
	echo '
		  </select>
		</div>
		<div class="clearfix"></div>
      </form>
	</td>
  </tr>
';
}
else {
    echo '
  <tr class="cal TCAL'.$pos.$tconfig.'">
    <td class="leftNavCalW">
      <a class="btn btn-md" href="'.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[17].'-2-'.$mois_p.'-'.$ann_p 
          : 'calendar.php?lng='.$lng.'&amp;mois='.$mois_p.'&amp;annee='.$ann_p).'" title="'.$ann_p.'-'.$mois_p.'">
        <i class="fas fa-caret-left"></i>
      </a>
    </td>
    <td colspan="5" class="midNavCalW">
      <a href="'.CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[17].'-21-'.$mois.'-'.$annee.'-2-'.$tconfig 
          : 'agenda.php?lng='.$lng.'&amp;mois='.$mois.'&amp;an='.$annee.'&amp;agv=2&amp;tconfig='.$tconfig).$z2.'" target="_parent" title="'.$web[287].' '.$annee.'-'.$mois.'">
        '.$annee.' '.$nommois.'
      </a>
    </td>
    <td class="rightNavCalW">;
      <a class="btn btn-md" href="'.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[17].'-2-'.$mois_s.'-'.$ann_s 
          : 'calendar.php?lng='.$lng.'&amp;mois='.$mois_s.'&amp;annee='.$ann_s).'" title="'.$ann_s.'-'.$mois_s.'">
        <i class="fas fa-caret-right"></i>
      </a>
    </td>
  </tr>
  <tr class="cal calsel TCAL'.$pos.$tconfig.'">
    <td colspan="7" class="cal TCAL'.$pos.$tconfig.' mnthyearCalW">
	  <form name="selan" method="POST" 
        action="'.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[17].'-1-'.$mois.'-'.$annee.'-'.$pos.'-'.$tconfig 
          : 'calendar.php?lng='.$lng.'&amp;mois='.$mois.'&amp;annee='.$annee.'&amp;pos='.$pos.'&amp;tconfig='.$tconfig).'">
	    <div class="float-left">
	      <select class="form-control cal selYearCal" name="annee" onchange="this.form.submit();">';
	foreach($dbannee as $an)
		echo '
	        <option value="'.$an.'"'.Selected($an == $annee).'>'.$an.'</option>';
	echo '
		  </select>
		</div>
	    <div class="float-right">
	      <select class="form-control cal selMonthCal" name="mois" onchange="this.form.submit();">';
	foreach($moispays as $key=>$mnth)
		echo '
	        <option value="'.$key.'"'.Selected($key == $mois).'>'.$mnth.'</option>';
	echo '
		  </select>
		</div>
		<div class="clearfix"></div>
      </form>
    </td>
  </tr>';
}
echo '
  <tr class="cal TCAL'.$pos.$tconfig.'">';
for ($nbrejours = 1; $nbrejours < 8; $nbrejours++) {
    echo '
    <td class="cals"><strong>'.$joursemaine[$nbrejours].'</strong></td>';
}
echo '
  </tr>
  <tr class="cal TCAL'.$pos.$tconfig.'">';
if ((($joursmois + $premierjour + $semainepays - 1) % 7) == 0 )
    $max = 7*floor(($joursmois + $premierjour + $semainepays)/7);
else
    $max = 7*ceil(($joursmois + $premierjour + $semainepays)/7);
for ($i = 1; $i <= $max; $i++) {
    $a = $i - $premierjour + 1 - $semainepays;
    $jour = $i - $premierjour + 1 - $semainepays;
    if (strlen($a) == 0) {
        $a = $a;
    }
    if ($i < $premierjour || $jour > $joursmois || $a == '') {
        if  (!($premierjour == 7 && $semainepays == 1)) {
            $textecal = '&nbsp;';
            echo '
    <td class="cal0 cellCalW cell'.$annee.$mois.$jour.'">'.$textecal.'</td>';   // jours vides
        }
    }
    elseif ($jour > $nbjours) {
        $textecal = '&nbsp;';
        echo '
    <td class="cal0 cellCalW cell'.$annee.$mois.$jour.'">'.$textecal.'</td>';   // jours vides
    }
    else {
        if ($a <10) {
            $a = '0'.$a;
        }
        if ($jour <10) {
            $jour = '0'.$jour;
        }
        if (cal_ferie($jour, $mois, $annee)) {
            $textecal = $a;
            if (($mois == $mois_ci) && ($jourj == $a))
                $stylecal = 'cal4';
            else
                $stylecal = 'cal3';
        }
        else {
            $textecal = $a;
            if (($jourj == $a)) {
                if (($i%7) == $semainepays) {
                    if ($mois == $mois_ci) {
                        $stylecal = 'cal4';
                    }
                    else {
                        $stylecal = 'cal3';
                    }
                }
                else {
                if ($mois == $mois_ci && $annee == $ann_ci) {
                    $stylecal = 'cal2';
                }
                else {
                    $stylecal = 'cal1';
                }
                }
            }
            else {
                if (($i%7) == $semainepays) {
                    $stylecal = 'cal3';
                }
                else {
                    $stylecal = 'cal1';
                }
            }
        }
        $jagok = FALSE;
        $pgtab = array($jour => '');
        $breve = array($jour => '');
        foreach ($dbworkag as $dbw) {
			$pgag = $dbw[4];
			ReadDoc($pgag);
            if (!isAccessGranted($fieldmod) || !isAuthorizedPublication($creadate)) continue;
            if (substr($dbw[0], 0, 8) == $annee.$mois.$a) {
                $jagok = TRUE;
                $pgtab[$jour] .= $dbw[4].'-';
                $breve[$jour] .= trim(CutLongWord(strip_tags($lng == $lang[0] ? $fieldc1 : $fieldc2), 36)).'... ';
            }
        }
        if ($jagok) {
            if ($pgtab[$jour] == '') $pgtab[$jour] = $pgag.'-';
            echo '
    <td class="calevt cellCalW cell'.$annee.$mois.$jour.'">
      <span class="calevt">
        <a href="'.CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[17].'-22-'.$pgtab[$jour].'-'.$pgag.'-1-'.$tconfig 
          : 'agenda.php?lng='.$lng.'&amp;idpg='.$pgtab[$jour].'&amp;pg='.$pgag.'&amp;agv=1&amp;tconfig='.$tconfig).$z2.'" 
          target="_parent" title="'.$breve[$jour].'">
          '.$textecal.'
        </a>
      </span>
    </td>';
        } else {
            echo '
    <td class="'.$stylecal.' cellCalW cell'.$annee.$mois.$jour.'">'.$textecal.'</td>';
        }
    }
    if (($i%7) == 0 and $i != $max) {
        echo '
  </tr>
  <tr>';
    }
}
echo '
  </tr>
</table>
</div>
</body>
</html>';
?>
