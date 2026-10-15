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
 * v6.00.19 (August 26, 2024) : Initial Release and Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "sec_ban_def.inc")) {
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

?>
<style type="text/css">
	<!--
	.fa-trash{ /* Couleur pour action supprimer */
		color: red;
		width: 16px;
		height: 16px;
	
	}
	.fa-trash:hover { /* Couleur pour action supprimer */
		color: green;
		width: 16px;
		height: 16px;
	
	}
	.fa-edit	{ /* Couleur pour modif */
		color: #BDD3AC;
		width: 16px;
		height: 16px;
	
	}
	.fa-edit:hover	{ /* Couleur pour modif */
		color: blue;
		width: 16px;
		height: 16px;
	
	}
	.fa-eye-slash { /* Couleur pour action désactiver */
		color: red;
		width: 16px;
		height: 16px;
	
	}
	.fa-eye-slash:hover { /* Couleur pour action désactiver */
		color: blue;
		width: 16px;
		height: 16px;
	
	}
	.fa-eye {           /* Couleur pour action activer */
		color: green;
		width: 16px;
		height: 16px;
	
	}
	.fa-eye:hover {     /* Couleur pour action activer */
		color: blue;
		width: 16px;
		height: 16px;
	
	}
	.fa-exclamation-triangle {           /* Couleur pour action déactiver/activer */
		color: red;
		width: 16px;
		height: 16px;
	
	}
	.fa-exclamation-triangle:hover {     /* Couleur pour action déactiver/activer */
		color: green;
		width: 16px;
		height: 16px;
	
	}
-->
</style>
<?php
include_once CHEMIN.INCREP.'security.inc';
include_once CHEMIN.INCREP.'functions.php';
$cfg = ReadSecurityConfig(BAN_CFG);
$def = ReadSecurityDatas(BAN_DEF);
$actiondemande = import('actiondemande');

$act  = isset($_POST['act'])  ? $_POST['act']  : (isset($_GET['act']) ? $_GET['act'] : 'list');
$id   = isset($_GET['id'])    ? $_GET['id']    : null;
$xdef = isset($_POST['xdef']) ? $_POST['xdef'] : null;
$urlpardefaut = 'error403.php';
$errpardefaut = '403';
switch ($act) {
case 'add' :
    $xdef = array('id'     => $cfg['nextid'], 
	              'status' => 'on', 
				  'ip'     => '',
				  'host'   => '',
				  'pseudo' => '',
				  'email'  => '',
				  'path'   => '',				  
				  'agent'  => '',
				  'mots'   => '',
				  'msg'    => $errpardefaut,
				  'url'    => $urlpardefaut);
    $act  = 'edit';
	$actiondemande = 'add';
    break;
case 'edit' :
    $xdef = $def[$id];
    $act  = 'edit';
    break;
case 'change' :
	$rechdefsel = false;
	if ($actiondemande == 'add')
	{
		$rechdefsel = SelectBANReadSecurityDatas(trim($xdef['ip']), trim($xdef['host']));
	}
	if (!$rechdefsel)
	{
		$tmp1 = trim($xdef['ip']).trim($xdef['pseudo']).trim($xdef['email']).trim($xdef['host']).trim($xdef['path']).trim($xdef['agent']).trim($xdef['mots']);
		$tmp2 = trim($xdef['msg']).trim($xdef['url']);
		if (empty($tmp1)) {
			$msg = $admin[1029];
			$act = 'edit';
		}
		elseif (empty($tmp2)) {
			$msg = $admin[1030];
			$act = 'edit';
		}
		else {
			$msg = '';
			if ($xdef['url'] == '')  $xdef['url'] = $urlpardefaut;
			if ($xdef['msg'] == '')  $xdef['msg'] = $errpardefaut;
			$def[$xdef['id']] = $xdef;
			if ($cfg['nextid'] == $xdef['id']) {
				$cfg['nextid']++;
			}
			WriteSecurityConfig(BAN_CFG, $cfg);
			WriteSecurityDatas(BAN_DEF, $def);
			$act = 'list';
		}
	}
	else
	{
		echo '
		<p style="text-align:center;"><i class="far fa-meh-rolling-eyes"></i><b>'.$admin[1870].'</b></p>';
		 $act = 'list';
	}			
    break;
case 'status' :
    $def[$id]['status'] = $def[$id]['status'] == 'on' ? '' : 'on';
    WriteSecurityDatas(BAN_DEF, $def);
    $act = 'list';
    break;
case 'delete' :
    unset($def[$id]);
    WriteSecurityDatas(BAN_DEF, $def);
    $act = 'list';
    break;
case 'list' :
default :
}

if ('list' == $act) {
    $widepage = 'on';
}
$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[996].' '.$admin[1721]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[1430], '100%');

echo '
<div class="w-100 m-auto text-align-center" >
<p>'.ToHelp($lng, '&pg=780&mnuid=1157&tconfig=0#z2').'</p>
<p style="text-align:center;">'.$admin[1000].'</p>
<hr />';

if ('list' == $act) {
    echo '
<p style="text-align:center;">
  <a href="admin.php?lng='.$lng.'&amp;pg=sec_ban_def&amp;act=add">'.$admin[1006].'</a>
</p>

  <fieldset>'.legend($admin[1430]);
    if (0 == count($def)) {
        echo '
    <p>'.$admin[1008].'</p>';
    } else {
        echo '
    <br />
    <table style="border:1px;width:100%;">
      <tr class="forum" style="text-align:center;">
        <td rowspan="2"> ID </td>
        <td colspan="7">'.$admin[1009].'</td>
        <td colspan="2">'.$admin[1010].'</td>
        <td rowspan="2">'.$admin[1011].'</td>
      </tr>
      <tr class="forum" style="text-align:center;">
        <td style="width:18%;">'.$admin[1012].'</td>
        <td style="width:10%;">'.$admin[517].'</td>
        <td style="width:10%;">'.$admin[1014].'</td>
        <td style="width:18%;">'.$admin[1015].'</td>
        <td style="width:18%;">'.$admin[1040].'</td>
        <td style="width:18%;">'.$admin[1016].'</td>
		<td style="width:18%;">'.$admin[1118].'</td>
        <td style="width:18%;">'.$admin[1017].'</td>
        <td style="width:18%;">'.$admin[1018].'</td>
      </tr>
';
        $i = 0;
		$flagediter = '<i class="far fa-file-alt" alt="'.$admin[1025].'" title="'.$admin[1025].'"></i>'; // Icone devant le champs de modification

		$flagdelete = '<i class="fas fa-trash align-middle" alt="'.$admin[1028].'" titre="'.$admin[1028].'" style="vertical-align:middle; font-size: 1.2rem;"></i>'; // suppression
        foreach ($def as $id=>$def1) {
			if (!isset($def[$id]['status'])) $def[$id]['status'] = '';
			$txt = $def[$id]['status'] == 'on' ? $admin[1027] : $admin[1026];
			$flagactiver = '<i class="far fa-eye-slash align-middle " alt="'.$txt.'" titre="'.$txt.'" style="vertical-align:middle; font-size: 1.2rem;"></i>'; // desactiver		
			$flagdesactiver = '<i class="far fa-eye" alt="'.$txt.'" titre="'.$txt.'" style="vertical-align:middle; font-size: 1.2rem;"></i>'; // desactiver         
			$class = ++$i % 2 == 0 ? ' class="quest"' : ' class="rep"';
            $img = $def[$id]['status'] == 'on' ? $flagdesactiver : $flagactiver;
			if (!isset($xdef['path'])) $xdef['path'] = '';
			if (!isset($xdef['pseudo'])) $xdef['pseudo'] = '';
			if (!isset($xdef['email'])) $xdef['email'] = '';
            echo '
      <tr'.$class.'>
        <td style="text-align:center;">'.$id.'</td>
        <td style="text-align:center;">'.(empty($def1['ip'])? '': CutField($def1['ip'])).'</td>
        <td style="text-align:center;">'.(empty($def1['pseudo'])? '': CutField($def1['pseudo'])).'</td>
        <td style="text-align:center;">'.(empty($def1['email'])? '': CutField($def1['email'])).'</td>
        <td style="text-align:center;">'.(empty($def1['host'])? '': CutField($def1['host'])).'</td>
		<td style="text-align:center;">'.(empty($def1['path'])? '': CutField($def1['path'])).'</td>
        <td style="text-align:center;">'.(empty($def1['agent'])? '': CutField($def1['agent'])).'</td>
        <td style="text-align:center;">'.(empty($def1['mots'])? '': CutField($def1['mots'])).'</td>
        <td style="text-align:center;">'.CutField($def1['msg']).'</td>
        <td style="text-align:center;">'.CutField($def1['url']).'</td>

        <td style="white-space:nowrap; text-align:center;">
          <a href="admin.php?lng='.$lng.'&amp;pg=sec_ban_def&amp;act=edit&amp;id='.$id.'" title="'.$admin[1025].'">
   		  '.$flagediter.'
          </a>
          <a href="admin.php?lng='.$lng.'&amp;pg=sec_ban_def&amp;act=status&amp;id='.$id.'" title="'.$txt.'">
            '.$img.'
          </a>
          <a href="admin.php?lng='.$lng.'&amp;pg=sec_ban_def&amp;act=delete&amp;id='.$id.'" title="'.$admin[1028].'">
            '.$flagdelete.'
          </a>
        </td>
      </tr>

';
        }
    }
    echo '
    </table>
    <br />
  </fieldset>
';
}
elseif ('edit' == $act) {
    echo '<div style="margin-left:40%;">
  <div class="d-inline-block" style="text-align:center;">
    <a href="admin.php?lng='.$lng.'&amp;pg=sec_ban_def&amp;act=list">'.$admin[1023].'</a> ||
    <a href="admin.php?lng='.$lng.'&amp;pg=sec_ban_def&amp;act=add">'.$admin[1006].'</a>
  </div></div>
  <form name="security" action="admin.php?lng='.$lng.'&amp;pg=sec_ban_def" method="post">
    <input type="hidden" name="act" value="change" />
	<input type="hidden" name="actiondemande" value="'.$actiondemande.'" />
    <input type="hidden" name="xdef[id]" value="'.$xdef['id'].'" />
    <fieldset>'.legend($admin[1431].' <em>(ID='.$xdef['id'].')</em>');
    if (!empty($msg)) {
        echo '
   <div class="alert-error" role="alert"  style="font-weight:bold;text-align:center;">
      <i class="fadm-off" style="font-size:1.3rem;"></i>&nbsp;
      '.$msg.'
    </div>';
    }
	if (!isset($xdef['path'])) $xdef['path'] = '';
	if (!isset($xdef['status'])) $xdef['status'] = '';
	if (!isset($xdef['pseudo'])) $xdef['pseudo'] = '';
	if (!isset($xdef['email'])) $xdef['email'] = '';
	
    echo '
		<div style="margin-bottom:12px;">
			<div class="d-inline-block" style="margin-left:10%;"><label for="xdef[status]">'.$admin[1024].'</label>
				<input class="form-check-input" type="checkbox" id="xdef[status]" name="xdef[status]"'.Checked($xdef['status']).' />
    </div></div>
    <fieldset>'.legend($admin[1009]).'
	<div style="margin-bottom:12px;">
		<div class="form-group d-inline-block" style="margin-left:10%;"><label for="xdef[ip]">'.$admin[1012].'</label>
			<input type="text" class="form-control" id="xdef[ip]" name="xdef[ip]" value="'.trim($xdef['ip']).'" size="50" />
    </div></div>

	<div style="margin-bottom:12px;">
		<div class="form-group d-inline-block" style="margin-left:10%;"><label for="xdef[pseudo]">'.$admin[517].'</label>
			<input type="text" class="form-control" id="xdef[pseudo]" name="xdef[pseudo]" value="'.trim($xdef['pseudo']).'" size="30" />
    </div></div>
	<div style="margin-bottom:12px;">
		<div class="form-group d-inline-block" style="margin-left:10%;"><label for="xdef[email]">'.$admin[1014].'</label>
			<input type="text" class="form-control" id="xdef[email]" name="xdef[email]" value="'.trim($xdef['email']).'" size="30" />
    </div></div>
	
	<div style="margin-bottom:12px;">
		<div class="form-group d-inline-block" style="margin-left:10%;"><label for="xdef[host]">'.$admin[1015].'</label>
			<input type="text" class="form-control" id="xdef[host]" name="xdef[host]" value="'.trim($xdef['host']).'" size="50" />
    </div></div>
	<div style="margin-bottom:12px;">
		<div class="form-group" style="margin-left:10%;"><label for="xdef[path]">'.$admin[1040].'</label>
			<textarea class="form-control" id="xdef[path]" name="xdef[path]" rows="3" max-rows="6"/>'.trim($xdef["path"]).'</textarea>
    </div></div>
	<div style="margin-bottom:12px;">
		<div class="form-group d-inline-block" style="margin-left:10%;"><label for="xdef[agent]">'.$admin[1016].'</label>
			<input type="text" class="form-control" id="xdef[agent]" name="xdef[agent]" value="'.trim($xdef['agent']).'" size="50" />
    </div></div>

	<div style="margin-bottom:12px;">
		<div class="form-group" style="margin-left:10%;"><label for="xdef[mots]">'.$admin[1118].'</label>
				<textarea class="form-control" id="xdef[mots]" name="xdef[mots]" rows="3" max-rows="6"/>'.trim($xdef["mots"]).'</textarea>
				 <br />
			<em>'.$admin[1119].'</em>
		  <br /><br />
    </div></div>
    </fieldset>
    <fieldset>'.legend($admin[1010]).'
	<div style="margin-bottom:12px;">
		<div class="form-group d-inline-block" style="margin-left:10%;"><label for="xdef[msg]">'.$admin[1017].'</label>
			<input type="text" class="form-control" id="xdef[msg]" name="xdef[msg]" value="'.trim($xdef['msg']).'" size="50" />
    </div></div>
	
	<div style="margin-bottom:12px;">
		<div class="form-group d-inline-block" style="margin-left:10%;"><label for="xdef[url]">'.$admin[1018].'</label>
			<input type="text" class="form-control" id="xdef[url]" name="xdef[url]" value="'.trim($xdef['url']).'" size="50" />
    </div></div>';	
    echo   $admin[1039].'
    </fieldset>
    <div class="form-group quest" style="padding:2px 20px;">
      <div style="float:right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div>
      <div style="clear:both"></div>
    </div>
  </form>';
}

echo '
</div>';

btable();
include CHEMIN.'admin/bpage.inc';
?>