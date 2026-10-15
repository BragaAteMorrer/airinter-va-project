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
 * v6.00.22 (December 19, 2025) : modify script in viwe table
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "sec_ban_log.inc")) {
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
$logs = ExploreFile(BAN_REP);
$logs = array_reverse($logs);

$log = isset($_POST['log']) ? $_POST['log'] : '';
if (!empty($logs)) {
    $log = in_array($log, $logs) ? $log : $logs[0];
}

$widepage = 'on';
$topmess  = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.$admin[1432];
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[1432], '100%');

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
  <form name="security" action="admin.php?lng='.$lng.'&amp;pg=sec_ban_log" method="post">
	<fieldset style="margin:10px auto;">'.legend($admin[1044]).'
      <div class="d-inline-block" style="text-align:center;">
		  <div style="text-align:center;margin:auto;">
			<select name="log">';
			foreach ($logs as $value) {
				echo '
			  <option value="'.$value.'"'.Selected($log === $value).'>'.substr($value, 0, -4).'</option>';
			}
			echo '
			</select>
		  </div>
	  </div>
	</fieldset>
    <div class="quest" style="padding:2px 20px;">
      <div style="float:right">'.SubmitButton($admin[1046], '', '', $imgdroite).'</div>
      <div style="clear:both"></div>
    </div>
  </form>
</div>';
    }
    if (!empty($log)) {
        $datas = ReadDbFields(BAN_REP.$log);
        $datas = array_reverse($datas);
        echo '
<fieldset>'.legend($admin[1045].substr($log, 0, -4)).'
<table style="width:100%;">
  <tr class="forum2" style="text-align:center;white-space:nowrap;">
    <td style="text-align:center;font-weight:bold;padding:2px 4px;"> '.$admin[521].' </td>
    <td style="text-align:center;font-weight:bold;padding:2px 4px;">No '.$admin[435].' </td>
    <td style="text-align:center;font-weight:bold;padding:2px 4px;"> '.$admin[1012].' </td>
    <td style="text-align:center;font-weight:bold;padding:2px 4px;"> '.$admin[1015].' </td>
    <td style="text-align:center;font-weight:bold;padding:2px 4px;"> '.$admin[1016].' </td> 
  </tr>';
    $i = 0;
    foreach ($datas as $data)
	{
		if (!isset($data[1])) continue;
        echo '
		<tr '.( $i++ % 2 ? 'class="rep"' : 'class="quest"').'>
			<td style="text-align:center;font-size:0.9em;padding:2px 4px;text-align:center;">'.FormatDateSec(str_replace(' ', '', $data[0])).'</td>
			<td style="text-align:center;font-size:0.9em;padding:2px 4px;">'.CutField($data[1]).'</td>
			<td style="text-align:center;font-size:0.9em;padding:2px 4px;">'.CutField($data[2]).'</td>
			<td style="text-align:center;font-size:0.9em;padding:2px 4px;">'.CutField($data[4]).'</td>
			<td style="text-align:center;font-size:0.9em;padding:2px 4px;">'.CutField($data[5]).'</td>																			  
		</tr>';
        }
    echo '
		</table>
		</fieldset>
		<hr />';
			}
		}
echo '</div>';
btable();
include CHEMIN.'admin/bpage.inc';
?>