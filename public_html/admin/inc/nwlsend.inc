<?php
/*******************************************************************************
 *   Send Newsletter
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
 * v6.00.19 (August 26, 2024) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'nwlsend.inc')) {
  header('location:../index.php');
  die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[33] == '') {
        $nomzone = $admin[546];
        include 'access.inc';
        exit();
    }
}

include_once CHEMIN.'inc/func_groupsnwl.php';
$actioncomplete = '';
$action   = import('action');
$idpg     = import('idpg', '', true, 1);
$lsn      = import('lsn', '', true, 0);
$id       = import('id');
$selgroup = import('selgroup', 'POST');
$from     = import('from', 'POST');

/// Nombre de lettres par page
$nbpg = 8;

$topmess  = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[530]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[530], '100%');

$dbwork = SelectDBFieldsByStatus(SelectDBFieldsByType(ReadDBFields(DOCID),TYP_NWL),'a');
@rsort($dbwork);

$count = count($dbwork);
if (!isset($idpg)) {
    $idpg = 1;
} else {
    while ($count <= ($idpg - 1)* $nbpg) $idpg--;
}

if ($action != 'send') {
    echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=1090&mnuid=937&tconfig=0#z2').$admin[529].'</p>
<hr />
';
	if (empty($dbwork)) {
		echo '
<p style="text-align:center;">
  <strong>'.$admin[1149].'</strong>
</p>
		';
	} else {
		echo '
<br />
<p class="forum w-100 my-0 mx-auto" style="max-width:900px;">
  '.$admin[1081].'
</p>
		';
		$navbar = GetNavBar('admin.php?lng='.$lng.'&amp;pg=nwlsend&amp;idpg=', $count, $idpg, $nbpg);
		if (!empty($navbar)) {
			echo '
<hr />
<div style="text-align:center">'.$navbar.'</div>
<hr />
			';
		}
		for ($i = ($idpg - 1)*$nbpg; $i < Min($nbpg*$idpg, $count); $i++) {
			ReadDoc($dbwork[$i][1]);
			$class = $i % 2 == 0 ? 'rep' : 'quest';
			echo '
<p class="'.$class.' w-100 my-0 mx-auto" style="max-width:900px;">
  <a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg=nwlsend&amp;id='.$dbwork[$i][1].'&amp;action=edit" title="">'.$admin[756].'<b>'.$fieldb1.'</b></a>
</p>
			';
		}
	}
	echo '
<br />
	';
}

if ($action == "edit") {
    ReadDoc($id);
    $bodycorps1 = ForceToAbsolute($fieldc1);
    include CHEMIN.'inc/lang/'.$lang[0].'-admin'.INCEXT;
    echo '
<hr />
<p style="text-align:center;"><b>'.$admin[528].'</b> '.$flag1.'</i></p>
<br />
<p>'.$admin[527].' '.$user[1].'</p>
<p>'.$admin[526].'</p>
<p>'.$admin[525].' '.$fieldb1.'</p>
<p style="text-align:center;">'.$admin[1709].$admin[1710].'</p>
<fieldset style="padding:15px;"><legend> '.getLabel($nom[38]).' </legend>'.$bodycorps1.'</fieldset>
	';
    echo '
<p style="text-align:center;font-size:0.75em;">'.$admin[533].'</p>
	';
    if ($lang[1] != "") {
        include CHEMIN.'inc/lang/'.$lang[1].'-admin'.INCEXT;
        $bodycorps2 = ForceToAbsolute($fieldc2);
        echo '
<p><br /></p>
<p style="text-align:center;"><b>'.$admin[528].'</b> '.$flag2.'</p>
<br />
<p>'.$admin[527].' '.$user[1].'</p>
<p>'.$admin[526].'</p>
<p>'.$admin[525].' '.$fieldb2.'</p>
<p style="text-align:center;">'.$admin[1709].$admin[1710].'</p>
<fieldset style="padding:15px;"><legend> '.getLabel($nom[39]).' </legend>'.$bodycorps2.'</fieldset>
		';
        echo '
<p style="text-align:center;font-size:0.75em;">'.$admin[533].'</p>
		';
    }
    include CHEMIN.'inc/lang/'.$lng.'-admin'.INCEXT;
    echo '
<p style="text-align:center;">'
  .$boutonleft.'
  <button type="button" title="'.$admin[757].'" class="btn btn-info btn-md" 
    onClick="self.location.href=\'admin.php?lng='.$lng.'&amp;pg=nwlsend&amp;action=send&amp;id='.$id.'&amp;lsn=-1\'">'.$admin[757].'</button>'
  .$boutonright.'
</p>
	';
    $listabon = ReadDBFields(NEWSLET_LIST);
    if (!empty($listabon)) {
        echo '
<br />
<fieldset>
<legend>'.$admin[1151].'</legend>
<div style="text-align:center;">
<form name="sellist" method="POST" action="admin.php?lng='.$lng.'&amp;pg=nwlsend&amp;action=send&amp;id='.$id.'">';
    $domserv = $_SERVER['HTTP_HOST'];
    $domcol  = explode('@', $userprefs[2])[1];
/*
	if (strpos($domserv, $domcol) !== false) {
        echo '
    <p style="text-align:center;">
      <b>'.$admin[1191].'</b> :&nbsp;&nbsp;<label for="webmestre">'.$admin[718].'</label>&nbsp;
      <input type="radio" id="webmestre" name="from" value="'.$user[1].'"'.checked($from == $admin[718]).' /> &nbsp;
      <label for="user">'.$userprefs[1].'</label> <input type="radio" id="user" name="from" value="'.$userprefs[2].'"'.checked($from == $userprefs[1]).' /> 
    </p>';
    }
    else {
*/
        $from = $user[1];
        echo '
    <p style="text-align:center;">
      <b>'.$admin[1191].'</b> :&nbsp;&nbsp;'.$from.'
    </p>';
//    }
    echo '
	<select class="form-control w-100 mx-auto" name="selgroup" style="max-width:160px;">
		<option value="all_subscribers">'.$admin[1150].'</option>
		';
		$grps = ReadGroupsNewsletter();
		foreach ($grps as $grp=>$group) {
			echo '
		<option value="'.$grp.'"'.Selected($fieldmod == $grp).'>'.$grp.' ('.count($grps[$grp]).')</option>
			';
		}
		echo '
	</select>
	<br />
	<p style="text-align:center;">'.SubmitButton($admin[523]).'</p>
</form>
</div>
</fieldset>
		';
    }
}
elseif ($action == 'send') {
    CreateDir(TEMPREP);
	echo '
<fieldset>
	';
	if ($lsn != -1) {
    echo BeginJavascript().'alert("'.$serviz[58].' - '.$serviz[168].' - '.$lsn.'";'.EndJavascript();
		$listabon = ReadDBFields(NEWSLET_LIST);
		if ($selgroup == 'all_subscribers') {
			$lst = $listabon;
		}
		else {
			$lst  = array();
			$grps = ReadGroupsNewsletter();
			if (count($grps[$selgroup]) > 0)
				foreach ($grps[$selgroup] as $abongrp) {
					foreach ($listabon as $abonlst) {
						if ($abongrp == $abonlst[0]) {
							$lst[] = $abonlst;
							break;
						}
					}
				}
		}
		WriteDBFields(TEMPREP.'nwltemp.dtb', $lst);
        echo '
<p style="text-align:center;">'.$admin[524].'</p>
<p style="text-align:center;">'.count($lst).' '.$admin[516].'</p>
<hr />
<p style="text-align:center;"><b>'.$admin[522].'</b></p>
<br />';
		$firststep = "PopupWindow(\"inc/nwlmail.php?lng=".$lng."&id=".$id."&selgroup=".$selgroup."&from=".$from."&lsn=".$lsn."&npp=".$serviz[58]."&dep=".$serviz[168]."\",\"nwlmail\",400,225,\"no\",\"no\")";
		echo BeginJavascript().$firststep.EndJavascript();
	}
	else {
		$nom38 = getLabel($nom[38]);
		$nom38 = mb_substr($nom38, 0, 1, $charset) == '§' ? mb_substr($nom38, 1, mb_strlen($nom38 ,$charset) ,$charset) : $nom38;
		$nom39 = getLabel($nom[39]);
		$nom39 = mb_substr($nom39, 0, 1, $charset) == '§' ? mb_substr($nom39, 1, mb_strlen($nom39 ,$charset) ,$charset) : $nom39;
		ReadDoc($id);
		include CHEMIN.'inc/lang/'.$lang[0].'-admin'.INCEXT;
		$to = trim(trim($supervision[10]).' '.$user[1]);
		$sujet = strip_tags($fieldb1);
		$bodycorps  = '<p style="text-align:center;">'.$admin[1709].'<br />';
		$bodycorps .= '<a href="'.$site[3].$site['NL'].'.php?lng='.$lang[0].'&pg='.$id.'&nlpseudo='.$user[0].'&nlgrp=all_subscribers">';
		$bodycorps .= $site[3].$site['NL'].'.php?lng='.$lang[0].'&pg='.$id.'</a></p>';
		$bodycorps .= '<fieldset style="padding:15px;"><legend> '.$nom38.' </legend>'.ForceToAbsolute($fieldc1).'</fieldset>';
		$bodycorps .= '<p style="text-align:center;font-size:0.75em;">'.$admin[549].'<br />';
		$bodycorps .= '<a href="'.$site[3].$site['NL'].".php?lng=".$lang[0]."&action=unsub&nlpseudo=".$user[0]."&nlmail=".$user[1].'">';
		$bodycorps .= $site[3].$site['NL'].".php?lng=".$lang[0]."&action=unsub&nlpseudo=".$user[0]."&nlmail=".$user[1]."</a></p>";
		$actioncomplete = 'L';
		eMailHtmlTo($sujet, $bodycorps, $to, $from);
		if (!empty($lang[1])) {
			include CHEMIN.'inc/lang/'.$lang[1].'-admin'.INCEXT;
			$sujet = strip_tags($fieldb2);
			$bodycorps .= '<br /><br /><p style="text-align:center;">'.$admin[1709].'<br />';
			$bodycorps .= '<a href="'.$site[3].$site['NL'].'.php?lng='.$lang[1].'&pg='.$id.'&nlpseudo='.$user[0].'&nlgrp=all_subscribers">';
			$bodycorps .= $site[3].$site['NL'].'.php?lng='.$lang[1].'&pg='.$id.'</a></p>';
			$bodycorps .= '<fieldset style="padding:15px;"><legend> '.$nom39.' </legend>'.ForceToAbsolute($fieldc2).'</fieldset>';
			$bodycorps .= '<p style="text-align:center;font-size:0.75em;">'.$admin[549].'<br />';
			$bodycorps .= '<a href="'.$site[3].$site['NL'].".php?lng=".$lang[1]."&action=unsub&nlpseudo=".$user[0]."&nlmail=".$user[1].'">';
			$bodycorps .= $site[3].$site['NL'].".php?lng=".$lang[1]."&action=unsub&nlpseudo=".$user[0]."&nlmail=".$user[1]."</a></p>";
			eMailHtmlTo($sujet, $bodycorps, $to, $from);
		}
		include CHEMIN.'inc/lang/'.$lng.'-admin'.INCEXT;
		echo '
<p style="text-align:center;">'.$admin[758].'</p>
<p style="text-align:center;">'.$admin[759].$to.'</p>
<hr />
<p style="text-align:center;"><b>'.$admin[760].'</b></p>
<br />
		';
		echo '
<p style="text-align:center;">'.SubmitButton($admin[1153], 'self.location.href=\'admin.php?lng='.$lng.'&amp;pg=nwlsend&amp;action=edit&amp;id='.$id.'\'').'</p>';
	}
	echo '
</fieldset>
<div class="row quest p-0 mt-2 w-100 m-auto">
  <div class="col-md-6">
    '.SubmitButton($admin[1153], 'self.location.href=\'admin.php?lng='.$lng.'&pg=nwlsend&action=edit\'', $imggauche, '', 'button').'
  </div>
</div>
	';
}

btable();
include CHEMIN.'admin/bpage.inc';
?>
