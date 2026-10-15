<?php
/*******************************************************************************
 *   Admin Calendar
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
 * v6.00.00 (December 15, 2020) : initial release
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'cal.inc')) {
	header('location:../index.php');
	die();
}

if ($dup == 2) {
    function DupTextId ($id) {
        global $lng, $lang;
        include DBBASE.TestFileID($id).INCEXT;
        $day   = substr($fielda1, 6, 2);
        $mois  = substr($fielda1, 4, 2);
        $annee = substr($fielda1, 0, 4);
        $txt   = $lng == $lang[0] ? $fieldc1 : $fieldc2;
        $txt   = strip_tags(str_replace('<br />', '', str_replace('</p>', '', str_replace('<p>', '', $txt))));
        $txt   = strlen($txt) > 142 ? CutLongWord($txt, 40).' .../...' : $txt;
        return '<b>'.substr(FormatDate($annee.$mois.$day."00000"), 0, 10).'</b> - '.$txt;
    }
    echo '
 -------------- <br />
<b>'.ucfirst($admin[915]).' '.$admin[922].'</b> '.DupTextId($id).'
<br /><br />';
}
    
include CHEMIN.'inc/lang/'.$lng.'-special.inc';
$l = ($lng == $lang[0]) ? 0 : 1;
$asite19 = explode('|', $site[19]);
if (empty($annee)) $annee = date('Y');
if (empty($mois))  $mois  = date('m');
$jour        = date('d');
$joursmois   = date('t', mktime(0, 0, 0, (int)$mois, 1, (int)$annee));
$premierjour = date('w', mktime(0, 0, 0, (int)$mois, 1, (int)$annee));
$mois        = date('m', mktime(0, 0, 0, (int)$mois, 1, (int)$annee));
$annee       = date('Y', mktime(0, 0, 0, (int)$mois, 1, (int)$annee));
$jourj       = date('d', mktime(0, 0, 0, (int)$mois, (int)$jour, (int)$annee));
if ($premierjour == 0) $premierjour = 7;
$semainepays = $admin[1613];
if ($semainepays == 0) {
    $joursemaine = array(1 => $admin[1615], $admin[1616], $admin[1617], $admin[1618], $admin[1619], $admin[1620], $admin[1614]);
}
else {
    $joursemaine = array(1 => $admin[1614], $admin[1615], $admin[1616], $admin[1617], $admin[1618], $admin[1619], $admin[1620]);
}
$moispays = array(1 => $admin[639], $admin[640], $admin[641], $admin[642], $admin[643], $admin[644], $admin[645], $admin[646], $admin[647], $admin[648], $admin[649], $admin[650]);
$nommois  = $moispays[$mois*$mois/$mois];
$dbannee  = array();
for ($i = -1; $i < 8; $i++) $dbannee[] = date('Y') + $i;
$date     = getdate(time());
$mois_ci  = $date['mon'];
if ($mois_ci < 10) {
  $mois_ci = '0'.$mois_ci;
}
$ann_ci = $date["year"];
if ($mois == 1) {
  $mois_p = 12;
  $ann_p  = $annee - 1;
}
else {
  $mois_p = $mois - 1;
  $ann_p  = $annee;
}
if ($mois_p < 10) $mois_p = '0'.$mois_p;
if ($mois == 12) {
  $mois_s = 1;
  $ann_s  = $annee + 1;
}
else {
  $mois_s = $mois + 1;
  $ann_s  = $annee;
}
if ($mois_s < 10) $mois_s = '0'.$mois_s;
$nbjours = date('d', mktime(0,0,0,(int)$mois_s,0,(int)$annee));
$idn     = '';

$dupid = $dup == 2 ? '&amp;dup=2&amp;id='.$id : '';

$adm_agenda1 = 'admin.php?lng='.$lng.'&amp;pg=agenda&amp;form=1&amp;tri='.$tri;
$adm_agenda2 = 'admin.php?lng='.$lng.'&amp;pg=agenda&amp;form=2&amp;tri='.$tri;
$adm_agenda3 = 'admin.php?lng='.$lng.'&amp;pg=agenda&amp;form=3&amp;tri='.$tri;
echo '
<div style="margin:auto;text-align:center;">
  <div class="table-responsive-md">
  <table class="cal" style="width:280px;margin:auto;">';
if (($asite19[$l] == 'E1') || ($asite19[$l] == 'E2')) {
	echo '
  <tr class="cal">
    <td>
      <a href="'.$adm_agenda3.$dupid.'&amp;mois='.$mois_p.'&amp;annee='.$ann_p.'" title="'.$mois_p.'-'.$ann_p.'">
	    <i class="fas fa-caret-left align-middle"></i>
	  </a>
    </td>
    <td colspan="5">
	  <form name="selmois" method="POST" action="'.$adm_agenda3.$dupid.'">
        <input type="hidden" name="annee" value="'.$annee.'" />
	    <div style="float:left;">
	      <select class="form-control cal" style="width:112px;" name="mois" onchange="this.form.submit();">';
	foreach($moispays as $key=>$mnth)
		echo '
	        <option value="'.$key.'"'.Selected($key == $mois).'>'.$mnth.'</option>';
	echo '
		  </select>
		</div>
      </form>
	  <form name="selannee" method="POST" action="'.$adm_agenda3.$dupid.'">
        <input type="hidden" name="mois" value="'.$mois.'" />
	    <div style="float:right;">
	      <select class="form-control cal" style="width:76px;" name="annee" onchange="this.form.submit();">';
	foreach($dbannee as $an)
		echo '
	        <option value="'.$an.'"'.Selected($an == $annee).'>'.$an.'</option>';
	echo '
		  </select>
		</div>
		<div style="clear:both;"></div>
		<noscript>
		  <div style="text-align:center;max-height:22px;margin-top:4px;">'.SubmitButton($admin[519]).'</div>
		</noscript>
      </form>
	</td>
    <td>
      <a href="'.$adm_agenda3.'&amp;mois='.$mois_s.'&amp;annee='.$ann_s.$dupid.'" title="'.$mois_s.'-'.$ann_s.'">
	    <i class="fas fa-caret-right align-middle"></i>
	  </a>
    </td>
  </tr>';
}
else {
	echo '
    <td>
      <a href="'.$adm_agenda3.'&amp;mois='.$mois_p.'&amp;annee='.$ann_p.'" title="'.$ann_p.'-'.$mois_p.$dupid.'">
	    <i class="fas fa-caret-left align-middle"></i>
	  </a>
    </td>
    <td colspan="5">
	  <form name="selannee" method="POST" action="'.$adm_agenda3.$dupid.'">
        <input type="hidden" name="mois" value="'.$mois_p.'" />
	    <div style="float:left;">
	      <select class="form-control cal" style="width:76px;" name="annee" onchange="this.form.submit();">';
	foreach($dbannee as $an)
		echo '
	        <option value="'.$an.'"'.Selected($an == $annee).'>'.$an.'</option>';
	echo '
		  </select>
		</div>
	  <form name="selmois" method="POST" action="'.$adm_agenda3.$dupid.'">
        <input type="hidden" name="annee" value="'.$ann_p.'" />
	    <div style="float:right;">
	      <select class="form-control cal" style="width:112px;" name="mois" onchange="this.form.submit();">';
	foreach($moispays as $key=>$mnth)
		echo '
	        <option value="'.$key.'"'.Selected($key == $mois).'>'.$mnth.'</option>';
	echo '
		  </select>
		</div>
		<div style="clear:both;"></div>
		<noscript>
		  <div style="text-align:center;max-height:22px;margin-top:4px;">'.SubmitButton($admin[519]).'</div>
		</noscript>
      </form>
    </td>
    <td>
      <a href="'.$adm_agenda3.'&amp;mois='.$mois_s.'&amp;annee='.$ann_s.$dupid.'" title="'.$ann_s.'-'.$mois_s.'">
	    <i class="fas fa-caret-right align-middle"></i>
	  </a>
    </td>';
}
echo '
  <tr>';
for ($nbrejours = 1; $nbrejours < 8; $nbrejours++) {
	$couleurcal = $calendar[0];
	echo'
    <td class="cal0" style="text-align:center;"><b>'.$joursemaine[$nbrejours].'</b></td>';
}
echo '
  </tr>
  <tr>';
for ($i = 1; $i < $joursmois + $premierjour + $semainepays; $i++) {
	$a = $i - $premierjour + 1 - $semainepays;
	$jour = $i - $premierjour + 1 - $semainepays;
	if (strlen($a) == 0) {
		$a = "$a";
	}
	if ($i < $premierjour || $a == '') {
		if  (!($premierjour == 7 && $semainepays == 1)) {
			$textecal = '&nbsp;';
			echo '
    <td class="cal0" style="background-image:none">'.$textecal.'</td>';
		}
	}
	elseif ($jour > $nbjours) {
		$textecal = '&nbsp;';
		echo '
	<td class="cal0">'.$textecal.'</td>';   // jours vides
	} else {
		if ($a <10) {
			$a = '0'.$a;
		}
		if ($jour <10) {
			$jour = '0'.$jour;
		}
		$textecal = "$a";
		if (cal_ferie($jour, $mois, $annee)) {
			if (($mois == $mois_ci) && ($jourj == $a)) $stylecal = 'cal4';
			else $stylecal = 'cal3';
		} else {
			if (($jourj == $a)) {
				if (($i%7) == $semainepays) {
					if ($mois == $mois_ci) {
						$stylecal = 'cal4';
					} else {
						$stylecal = 'cal3';
					}
				} else {
					if ($mois == $mois_ci) {
						$stylecal = 'cal2';
					} else {
						$stylecal = 'cal1';
					}
				}
			} else {
				if (($i%7) == $semainepays) {
					$stylecal = 'cal3';
				} else {
					$stylecal = 'cal1';
				}
			}
		}
		if (($asite19[$l] == 'E1') || ($asite19[$l] == 'E2')) {
			$agdt = ('day='.$a.'&amp;mois='.$mois.'&amp;annee='.$annee);
		}
		if (($asite19[$l] == 'U1') || ($asite19[$l] == 'U2')) {
			$agdt = ('mois='.$mois.'&amp;day='.$a.'&amp;annee='.$annee);
		}
		if (($asite19[$l] == 'C1') || ($asite19[$l] == 'C2')) {
			$agdt = ('annee='.$annee.'&amp;mois='.$mois.'&amp;day='.$a);
		}
		$jagok    = '0';
		$dbworkag = ReadDBFields(DBAGENDA);
		for ($k = 0; $k < count($dbworkag); $k++) {
			switch ($asite19[$l]) {
			case 'U1' :
			case 'U2' :
				if ($dbworkag[$k][0] == $mois.'/'.$a.'/'.$annee || $dbworkag[$k][0] == $mois.'.'.$a.'.'.$annee) {
					$jagok = '1';
					$pgag  = $dbworkag[$k][4];
				}
				break;
			case 'C1' :
			case 'C2' :
				if ($dbworkag[$k][0] == $annee.'/'.$mois.'/'.$a || $dbworkag[$k][0] == $annee.'.'.$mois.'.'.$a) {
					$jagok = '1';
					$pgag  = $dbworkag[$k][4];
				}
				break;
			case 'E1' :
			case 'E2' :
				if ($dbworkag[$k][0] == $a.'/'.$mois.'/'.$annee || $dbworkag[$k][0] == $a.'.'.$mois.'.'.$annee) {
					$jagok = '1';
					$pgag  = $dbworkag[$k][4];
				}
			}
		}
		if ($jagok == '1') {
			if ($dup == 2) {
				echo'
      <td class="calevt"><span class="calevt"><a href="'.$adm_agenda2.'&amp;idn='.$pgag.'&amp;'.$agdt.'&amp;dup=2&amp;id='.$id.'">'.$textecal.'</a></span></td>';
			} else {
				echo '
      <td class="calevt"><span class="calevt"><a href="'.$adm_agenda1.'&amp;'.$agdt.'">'.$textecal.'</a></span></td>';
			}
		} elseif ($jagok != '1') {
			if ($dup == 2) {
				echo '
      <td class="'.$stylecal.'"><a href="'.$adm_agenda2.'&amp;'.$agdt.$dupid.'">'.$textecal.'</a></td>';
			} else {
				echo '
      <td class="'.$stylecal.'"><a href="'.$adm_agenda1.'&amp;'.$agdt.'">'.$textecal.'</a></td>';
			}
		}
	}
	if (($i%7) == 0) {
		echo '</tr><tr>';
	}
}
for ($i = $joursmois + $premierjour + $semainepays; $i < 43; $i++) {
	echo '
  <td class="cal0">'.(isset($joursemaine[$nbrejours]) ? $joursemaine[$nbrejours] : '').'</td>';
	if (($i%7) == 0 && $i != 42) {
		echo '
	</tr>
	<tr>';
	}
}
echo '
    </tr>
  </table>
  </div>
</div>';
?>