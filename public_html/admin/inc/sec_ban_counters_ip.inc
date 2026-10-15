<?php
/*******************************************************************************
 *   Security Bannishment
 *******************************************************************************
 *   GuppY PHP Script - version 5.0
 *   CeCILL Copyright (C) 2004-2014 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = http://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V5 developed by Lud Bienaimé
 *      with the participation of Jean-Michel Misrachi and the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 *  v6.00.20 (October 24, 2024) : Integration counter IP Initial Release
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "sec_ban_counters_ip.inc")) {
    header("location:../index.php");
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[45] == '') {
        $nomzone = $admin[940];
        include 'access.inc';
        exit();
    }
}

include_once CHEMIN.INCREP.'security.inc';
$cfg = ReadSecurityConfig(BAN_CFG);
$logsemaine = array_unique(ExploreFileSemaine(BAN_REPCOUNTERS));
$logs = $logsemaine;
$logs = array_reverse($logs);
$log       = import('log');
$tri       = import('tri');
$idpg      = import('idpg');
if (!empty($logs)) {
    $log = in_array($log, $logs) ? $log : $logs[0];
}

$widepage = 'on';
$topmess  = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.$admin[1896];
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[1896], '100%');

echo '
<div class="w-100 m-auto text-align-center" >
<p>'.ToHelp($lng, '&pg=1160&mnuid=937&tconfig=0#z2').'</p>
<p style="text-align:center;">'.$admin[557].' '.getIPadress($_SERVER[$site[6] == 'on' ? 'HTTP_X_FORWARDED_FOR' : 'REMOTE_ADDR']).'</p>
<hr />';
if (empty($logs)) {
    echo '
<p style="text-align:center;"><b>'.$admin[1043].'</b></p>';
}
else {
    if (count($logs) > 1) {
        echo '
<div class="w-100 m-auto text-align-center" >
  <form name="security" action="admin.php?lng='.$lng.'&amp;pg=sec_ban_counters_ip" method="post">
	<fieldset style="margin:10px auto;">'.legend($admin[1897]).'
      <div class="d-inline-block" style="text-align:center;">
		  <div style="text-align:center;margin:auto;">
			<select name="log">';
			foreach ($logs as $value) {
				echo '
			  <option value="'.$value.'"'.Selected($log === $value).'>'.$value.'</option>';
			}
			echo '
			</select>
		  </div>
	  </div>
	</fieldset>
    <div class="quest" style="padding:2px 20px;">
      <div style="float:right">'.SubmitButton($admin[1896], '', '', $imgdroite).'</div>
      <div style="clear:both"></div>
    </div>
  </form>
</div>';
    }
    if (!empty($log)) 
	{
		$semainechoisi = substr($log, -2);
		$datafilesems = ExploreFileSemainechoisi(BAN_REPCOUNTERS,$semainechoisi);
		$tmpi   = 0;
		$tmp[0] = array();
		$tmp[1] = array();
		$tmp[2] = array();
		for ($i = 0; $i < count($datafilesems); $i++) 
		{
			$tmp[0][$tmpi] = $datafilesems[$i][0];
			$tmp[1][$tmpi] = $datafilesems[$i][1];
			$tmp[2][$tmpi] = $datafilesems[$i][2];
			$tmpi++;
		}
		unset($datafilesems);
		if ($tmpi > 0) {
			$href = CHEMIN.'admin/admin.php?lng='.$lng.'&amp;log='.$log.'&amp;pg=sec_ban_counters_ip';
			switch ($tri) {
			case 'datespam' :
			case '-datespam' :
				$cr = array_multisort($tmp[0], $tri == 'datespam' ? SORT_ASC : SORT_DESC, $tmp[1], $tmp[2]);
				break;
			case 'ipsem' :
			case '-ipsem' :
				$cr = array_multisort($tmp[1], $tri == 'ipsem' ? SORT_ASC : SORT_DESC, $tmp[0], $tmp[2]);
				break;
			case 'compteur' :
			case '-compteur' :
				$cr = array_multisort($tmp[2], $tri == 'compteur' ? SORT_ASC : SORT_DESC, $tmp[0], $tmp[1]);
				break;
			}
		}
		if (empty($idpg)) {
			$idpg = 1;
		}
		else {
			while ($tmpi <= ($idpg - 1)* $serviz[75]) $idpg--;
		}
		if ($tmpi > 0) 
		{
			$navbar = GetNavBar('admin.php?lng='.$lng.'&amp;log='.$log.'&amp;pg=sec_ban_counters_ip&amp;tri='.$tri.'&amp;idpg=', $tmpi, $idpg, $serviz[75]);
			echo $navbar.'
				<form name="fast_actions" id="fast_actions" action="admin.php?lng='.$lng.'&amp;log='.$log.'&amp;idpg='.$idpg.'&amp;pg=sec_ban_counters_ip&amptri='.$tri.'" method="post">
				  <fieldset class="w-100 m-0 p-2">'.legend($admin[1045].substr($log, -2));
			echo '
			<div class="table-responsive-md">
			<table class="table w-100 m-auto table-bordered">';
				$idup     = $tri == 'datespam' 
				  ? '<i class="fas fa-caret-up text-danger align-middle" style="font-size: 1.8rem;"></i>' 
				  : '<i class="fas fa-caret-up align-middle" style="font-size: 1.8rem;"></i>';
				$iddown   = $tri == '-datespam' 
				  ? '<i class="fas fa-caret-down text-danger align-middle" style="font-size: 1.8rem;"></i>' 
				  : '<i class="fas fa-caret-down align-middle" style="font-size: 1.8rem;"></i>';
				$descup   = $tri == 'ipsem' 
				  ? '<i class="fas fa-caret-up text-danger align-middle" style="font-size: 1.8rem;"></i>' 
				  : '<i class="fas fa-caret-up align-middle" style="font-size: 1.8rem;"></i>';
				$descdown = $tri == '-ipsem' 
				  ? '<i class="fas fa-caret-down text-danger align-middle" style="font-size: 1.8rem;"></i>' 
				  : '<i class="fas fa-caret-down align-middle" style="font-size: 1.8rem;"></i>';
				$moddup   = $tri == 'compteur' 
				  ? '<i class="fas fa-caret-up text-danger align-middle align-middle" style="font-size: 1.8rem;"></i>' 
				  : '<i class="fas fa-caret-up align-middle" style="font-size: 1.8rem;"></i>';
				$modddown = $tri == '-compteur' 
				  ? '<i class="fas fa-caret-down text-danger align-middle" style="font-size: 1.8rem;"></i>' 
				  : '<i class="fas fa-caret-down align-middle" style="font-size: 1.8rem;"></i>';
			echo '
				 <tr class="forum">';
			echo '
			<td style="vertical-align:middle;padding:5px;white-space:nowrap;text-align:center;width:15%;">
			  <a href="'.$href.'&amp;tri=datespam" title="'.$admin[1898].'" style="top:-1px;left:1px;"> '.$moddup.' </a>
			  '.$admin[521].'
			  <a href="'.$href.'&amp;tri=-datespam" title="'.$admin[1899].'" style="top:-1px;left:1px;"> '.$modddown.' </a>
			</td>
			<td style="vertical-align:middle;padding:5px;width: 70%;">
			  <a href="'.$href.'&amp;tri=ipsem" title="'.$admin[1900].'" style="top:-1px;left:1px;"> '.$descup.' </a>
			  '.$admin[518].'
			  <a href="'.$href.'&amp;tri=-ipsem" title="'.$admin[1901].'" style="top:-1px;left:1px;"> '.$descdown.' </a>
			</td>
			<td style="vertical-align:middle;padding:5px;white-space:nowrap;text-align:center;width:15%;">
			  <a href="'.$href.'&amp;tri=compteur" title="'.$admin[1902].'" style="top:-1px;left:1px;"> '.$moddup.' </a>
			  '.$admin[1895].'
			  <a href="'.$href.'&amp;tri=-compteur" title="'.$admin[1903].'" style="top:-1px;left:1px;"> '.$modddown.' </a>
			</td>
		  </tr>';
			for ($i = $serviz[75]*($idpg-1); $i < Min($serviz[75]*$idpg, $tmpi); $i++)
			{
				echo '
					<tr '.($i % 2 ? 'class="rep"' : 'class="quest"').'>
						<td style="font-size:0.9em;text-align:center;">'.$tmp[0][$i].'</td>
						<td style="font-size:0.9em;text-align:center;">'.$tmp[1][$i].'</td>
						<td style="font-size:0.9em;text-align:center;">'.$tmp[2][$i].'</td>	
					</tr>';
			}
			echo  $navbar;
		}
        echo '
			</table>
			</fieldset>
			</form>
			<hr />';


    }
}
echo '</div>';
btable();
include CHEMIN.'admin/bpage.inc';
?>