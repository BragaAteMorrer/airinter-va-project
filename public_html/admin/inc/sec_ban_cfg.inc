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
 * v6.00.20 (October 24, 2024) : Modify détection secuity
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "sec_ban_cfg.inc")) {
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
	
$xcfg = isset($_POST['xcfg']) ? $_POST['xcfg'] : null;
if (isset($xcfg)) {
    $cfg = $xcfg;
    WriteSecurityConfig(BAN_CFG, $cfg);
	$msg = $admin[1820];
}

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[997].' '.$admin[1721]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[1431], '100%');

echo '
<div class="w-100 m-auto text-align-center" >
<p>'.ToHelp($lng, '&pg=1156&mnuid=937&tconfig=0#z2').'</p>
<p style="text-align:center;">'.$admin[1001].'</p>
<hr />';
echo '
<form name="security" action="admin.php?lng='.$lng.'&amp;pg=sec_ban_cfg" method="post">
  <input type="hidden" name="xcfg[nextid]" value="'.$cfg['nextid'].'" />';
      if (!empty($msg)) {
        echo '
    <div class="alert-success" role="alert"  style="font-weight:bold;text-align:center;">
      <i class="fas fa-file" style="font-size:1.3rem;color:#ba55d3;"></i>&nbsp;
      '.$msg.'
    </div>';
    }
 echo '
	<fieldset>'.legend($admin[1873]).'
		<div style="margin-bottom:12px;">
			<div class="d-inline-block" style="margin-left:10%;"><label for="xcfg[inforparmail]">'.$admin[1874].'</label>
				'.SelectSecurity("xcfg[inforparmail]", $cfg['inforparmail'], $sec_active).'
			</div>
		</div>
	</fieldset>';
echo '
  <fieldset>'.legend($admin[1437]);

    echo '
	<div style="margin-bottom:12px;">
	<div class="d-inline-block" style="margin-left:10%;">'.$admin[1031].'</div>
		<div class="d-inline-block">
			'.SelectSecurity("xcfg[status]", $cfg['status'], $sec_active).'
    </div></div>	
	
	<div style="margin-bottom:12px;">
	<div class="d-inline-block" style="margin-left:10%;">'.$admin[1032].'</div>
		<div class="d-inline-block">
			'.SelectSecurity("xcfg[record]", $cfg['record'], $sec_active).'
    </div></div>

	<div style="margin-bottom:12px;">
	<div class="d-inline-block" style="margin-left:10%;">'.$admin[1033].'</div>
		<div class="d-inline-block">
			'.SelectSecurity("xcfg[retention]", $cfg['retention'], $sec_retention).'
    </div></div>
  </fieldset>';
  if (!isset($xcfg['seuilmaxip'])) $xcfg['seuilmaxip'] = 100;
  if (!isset($cfg['seuilmaxip'])) $cfg['seuilmaxip'] = 100;
echo '
  <fieldset>'.legend($admin[1893]).'	
	<div style="margin-bottom:12px;">
	<div class="d-inline-block" style="margin-left:10%;">'.$admin[1041].'</div>
		<div class="d-inline-block">
			'.SelectSecurity("xcfg[pathlgmessage]", $cfg['pathlgmessage'], $sec_pathlgmessage).'
    </div></div>
	<div style="margin-bottom:12px;">
	<div class="d-inline-block" style="margin-left:10%;">'.$admin[1072].'</div>
		<div class="d-inline-block">
			'.SelectSecurity("xcfg[querylgmessage]", $cfg['querylgmessage'], $sec_querylgmessage).'
    </div></div>
	<div style="margin-bottom:12px;">
	<div class="d-inline-block" style="margin-left:10%;">'.$admin[1894].'</div>
		<div class="d-inline-block">
			'.SelectSecurity("xcfg[seuilmaxip]", $cfg['seuilmaxip'], $sec_seuilmaxip).'
    </div></div>	
  </fieldset>
  <div class="quest" style="padding:2px 20px;">
    <div style="float:right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div>
    <div style="clear:both"></div>
  </div>
</form>';

echo '
</div>';

btable();
include CHEMIN.'admin/bpage.inc';
?>
