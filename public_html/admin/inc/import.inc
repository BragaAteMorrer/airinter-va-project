<?php
/*******************************************************************************
 *   Import
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
 * v6.00.19 (August 15, 2029) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'import.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[44] == '') {
        $nomzone = $admin[1093];
        include 'access.inc';
        exit();
    }
}

function InputFileId($oid, $bnew) {
    global $admin;
    if ($bnew) {
        return $admin[1111].'<input type="hidden" name="new[fileid]" value="-1" />';
    } else {
        return '
    <select class="form-control" name="new[fileid]">
      <option value="-1" selected="selected">'.$admin[1111].'</option>
      <option value="'.$oid.'">'.$admin[1112].'</option>
    </select>';
    }
}

function SelectStatus($ostatus){
    global $admin;
    return '
    <select class="form-control" name="new[status]">
      <option value="a"'.Selected($ostatus == 'a').'>'.$admin[765].'</option>
      <option value="i"'.Selected($ostatus == 'i').'>'.$admin[766].'</option>
    </select>';
}

function InputType($otype) {
    global $admin;
    if (in_array($otype, array('f1', 'f2', 'f3', 'f4', 'ed', 'ft', 'sp'))) {
        return '
    <input class="form-control" type="text" name="new[type]" value="fb" size="2" readonly="readonly" />';
    } else {
        return $admin[1108].'
	<input type="hidden" name="new[type]" value="'.$otype.'" />';
    }
}

function InputAuthor($oauthor) {
    global $user, $serviz, $userprefs, $admin;
    if (!empty($user[0]) && $oauthor == $user[0]) {
        return $admin[1108].'
	<input type="hidden" name="new[author]" value="'.$oauthor.'" />';
    } elseif (!empty($serviz[31]) && $oauthor == $serviz[31]) {
        return $admin[1108].'
	<input type="hidden" name="new[author]" value="'.$oauthor.'" />';
    } elseif (!empty($serviz[176]) && $oauthor == $serviz[176]) {
        return $admin[1108].'
	<input type="hidden" name="new[author]" value="'.$oauthor.'" />';
    } elseif (!empty($userprefs[1]) && $oauthor == $userprefs[1]) {
        return $admin[1108].'
	<input type="hidden" name="new[author]" value="'.$oauthor.'" />';
    } else {
        $out = '
    <select class="form-control" name="new[author]" style="width:170px;">';
        if (!empty($oauthor)) {
            $out.= '
      <option value="'.$oauthor.'" selected="selected">'.$oauthor.'</option>';
        } else {
            $out.= '
      <option value="">'.$admin[1108].'</option>';
        }
        if (!empty($user[0])) {
            $out.= '
      <option value="'.$user[0].'">'.$user[0].'</option>';
        }
        if (!empty($serviz[31]) && $serviz[31] != $user[0]) {
            $out.= '
      <option value="'.$serviz[31].'">'.$serviz[31].'</option>';
        }
        if (!empty($serviz[176]) && $serviz[176] != $user[0]) {
            $out.= '
      <option value="'.$serviz[176].'">'.$serviz[176].'</option>';
        }
        if (!empty($userprefs[1]) && $userprefs[1] != $serviz[31] && $userprefs[1] != $serviz[176] && $userprefs[1] != $user[0]) {
            $out.= '
      <option value="'.$userprefs[1].'">'.$userprefs[1].'</option>';
        }
        $out .= '
    </select>';
        return $out;
    }
}

function InputEmail($oemail) {
    global $user, $userprefs, $admin, $oauthor;
    if (!empty($user[1]) && $oemail == $user[1]) {
        return $admin[1108].'
	<input type="hidden" name="new[email]" value="'.$oemail.'" />';
    } elseif (!empty($userprefs[2]) && $oauthor == $userprefs[1]) {
        return $admin[1108].'
	<input type="hidden" name="new[email]" value="'.$oemail.'" />';
    } else {
        $out = '
    <select class="form-control" name="new[email]" style="width:170px;">';
        if (!empty($oemail)) {
            $out.= '
      <option value="'.$oemail.'" selected="selected">'.$oemail.'</option>';
        } else {
            $out.= '
      <option value="">'.$admin[1108].'</option>';
        }
        if (!empty($user[1])) {
            $out.= '
      <option value="'.$user[1].'">'.$user[1].'</option>';
        }
        if (!empty($userprefs[2]) && $userprefs[2] != $user[1]) {
            $out.= '
      <option value="'.$userprefs[2].'">'.$userprefs[2].'</option>';
        }
        $out .= '
    </select>';
        return $out;
    }

}

function InputCreaDate($odate, $bnew) {
    global $admin;
    $now = GetCurrentDateTime();
    if ($bnew) {
        return '
    <select class="form-control" name="new[creadate]">
      <option value="'.$odate.'">'.$admin[1108].'</option>
      <option value="'.$now.'" selected="selected">'.FormatDate($now).'</option>
    </select>';
    } else {
        return $admin[1108].'
	<input type="hidden" name="new[creadate]" value="'.$odate.'" />';
    }
}

function InputModDate($odate, $bnew) {
    global $admin;
    $now = GetCurrentDateTime();
    if ($bnew) {
        return '
    <select class="form-control" name="new[moddate]">
      <option value="'.$odate.'">'.$admin[1108].'</option>
      <option value="'.$now.'" selected="selected">'.FormatDate($now).'</option>
    </select>';
    } else {
        return FormatDate($now).' <input type="hidden" name="new[moddate]" value="'.$now.'" />';
    }
}

function InputFielda1($otype, $ofielda1) {
    global $admin;
    if ($otype == 'ed') {
        return '
    <input class="form-control" type="text" name="new[fielda1]" value="E" size="25" readonly="readonly" />';
    } elseif ($otype == 'ft') {
        return '
    <input class="form-control" type="text" name="new[fielda1]" value="B" size="25" readonly="readonly" />';
    } elseif (in_array($otype, array('f1', 'f2', 'f3', 'f4', 'sp'))) {
        return '
    <input class="form-control" type="text" name="new[fielda1]" value="TLAEURB" size="25" readonly="readonly" />';
    } elseif ($otype == 'sp') {
        return '
    <input class="form-control" type="text" name="new[fielda1]" value="LAEUR" size="25" readonly="readonly" />';
    } elseif ($otype == 'ar') {
        return '
    <input class="form-control" type="text" class="texte" name="new[fielda1]" value="'.$ofielda1.'" size="25" />';
    } else {
        return $admin[1108].'
	<input type="hidden" name="new[fielda1]" value="'.$ofielda1.'" />';
    }
}

function InputFielda2($otype, $ofielda2) {
    global $admin;
    if ($otype == 'ar') {
        return '
    <input class="form-control" type="text" class="texte" name="new[fielda2]" value="'.$ofielda2.'" size="25" />';
    } else {
        return $admin[1108].'
	<input type="hidden" name="new[fielda2]" value="'.$ofielda2.'" />';
    }
}

function InputFieldb1($otype, $ofieldb1, $doc) {
    global $admin;
	$titre = '§ImportDoc ed '.substr($doc, 0, -4);
    if ($otype == 'ed') {
        return '
    <input class="form-control" type="text" name="new[fieldb1]" value="'.$titre.'" size="25" readonly="readonly" />';
    } elseif ($otype == 'ft') {
        return '
    <input class="form-control" type="text" name="new[fieldb1]" value="'.$titre.'" size="25" readonly="readonly" />';
    } else {
        return $admin[1108].'
	<input type="hidden" name="new[fieldb1]" value="'.strip_tags($ofieldb1).'" />';
    }
}

function InputFieldb2($otype, $ofieldb2, $doc) {
    global $admin;
	$titre = '§ImportDoc ed '.substr($doc, 0, -4);
    if ($otype == 'ed') {
        return '
    <input class="form-control" type="text" name="new[fieldb2]" value="'.$titre.'" size="25" readonly="readonly" />';
    } elseif ($otype == 'ft') {
        return '
    <input class="form-control" type="text" name="new[fieldb2]" value="'.$titre.'" size="25" readonly="readonly" />';
    } else {
        return $admin[1108].'
	<input type="hidden" name="new[fieldb2]" value="'.strip_tags($ofieldb2).'" />';
    }
}

function InputFieldd1($otype, $ofieldd1, $doc) {
    global $admin, $lang, $lng;
	$mnus = ReadDBFields(DBMENUBOX);
    if ($otype == 'ar') {
        $ret = '
    <select class="form-control" name="new[fieldd1]" style="width:170px;">
	  <option value="none">'.$admin[681].'</option>';
		foreach($mnus as $mnu) $ret .= '
	  <option value="MNU '.$mnu[4].'">MNU '.$mnu[4].'-'.$mnu[$lng == $lang[0] ? 2 : 3].'</option>';
		$ret .= '
	</select>';
		return $ret;
    } else {
        return $admin[1108].'<input type="hidden" name="new[fieldd1]" value="'.$ofieldd1.'" />';
    }
}

function InputFieldweb($otype, $ofieldweb, $doc) {
    global $admin;
    if ($otype == 'od') {
		$dbmenu = ReadDBFields(DBDDMENUBOX);
		$dbwork = array();
		for ($i = 0; $i < count($dbmenu); $i++) {
			$dbwork[$i][0] = $dbmenu[$i][$lang[0] == $lng ? 2 : 3];
			$dbwork[$i][1] = $dbmenu[$i][4];
		}
		sort($dbwork);
		if (!empty($dbwork)) {
			$ret = '
     <select class="form-control" name="new[fieldweb]">
        <option value="" select="selected">'.$admin[1222].'</option>';
			foreach ($dbwork as $menu) {
				$ret .= '
        <option value="'.$menu[1].'">'.$menu[0].'</option>';
			}
			$ret .= '
    </select>';
		} else 
			$ret = $ofieldweb;
		return $ret;
    } else {
        return $admin[1108].'
	<input type="hidden" name="new[fieldweb]" value="'.$ofieldweb.'" />';
    }
}

function IsNew($oid, $otype) {
    $id = TestFileID($oid);
    if (is_file(DBBASE.$id.INCEXT)) {
        include DBBASE.$id.INCEXT;
        return $type != $otype;
    } else {
        return TRUE;
    }
}


$headinc = '
<style type="text/css">
	#doc { border:1px solid; border-collapse:collapse;  margin:0 auto 10px; }
	#doc td { border:1px solid; padding:2px 5px; }
	#doc input { background-color : #CCC; }
	#doc .short { max-height:75px; max-width:500px; overflow:hidden; }
	.error { text-align:center; color:red; background-color:yellow; font-weight:bold; }
</style>';


$pats = array(chr(36), chr(146), chr(128), chr(140), chr(156), chr(138), chr(154), '&#8230;', '&#x2026;', '&#133;'); 
$patr = array('&dollar;', '&rsquo;', '&euro;', '&OElig;', '&oelig;', '&Scaron;', '&scaron;', '&hellip;', '&hellip;', '&hellip;');
$step = isset($_POST['step']) ? $_POST['step'] : 1;
if (!in_array($step, array(1, 2, 3))) $step = 1;

$intro = $admin[1095];
switch ($step) {
case 1 :
    $introStep = $admin[1096];
    $content = '
<form name="import" method="post" action="admin.php?lng='.$lng.'&amp;pg=import" enctype="multipart/form-data">
  <input type="hidden" name="step" value="2" />
  <fieldset class="w-100 m-0 mb-2 py-0 px-2">
	<br />
	<div style="text-align:center">'.$admin[1099].' 
      <input class="form-control w-100 mx-auto" style="max-width:610px;" type="file" name="file" />
    </div>
	<br />
  </fieldset>
  <div class="row quest p-0 mt-2 w-100 m-auto">
    <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
    <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[1100], '', '', $imgdroite).'</div></div>
  </div>
</form>
';
    break;
    
case 2 :
    $introStep = $admin[1097];
    $file = $_FILES['file']['name'];
    if (!empty($file)) {
        $extension = mb_strtolower(substr($file, -4), $charset);
        if ('.inc' != $extension) {
            // Erreur : Mauvaise extension
            $content = '
<form name="import" method="post" action="admin.php?lng='.$lng.'&amp;pg=import">
<input type="hidden" name="step" value="1" />
<fieldset class="w-100 m-0 p-0" style="padding-top:12px;">
  <p class="error">'.$admin[1102].'</p>
</fieldset>
<div class="row quest p-0 mt-2 w-100 m-auto">
  <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
  <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[1101], '', '', $imgdroite).'</div></div>
</div>
</form>';
        } else {
            if (is_uploaded_file($_FILES['file']['tmp_name'])) {
                move_uploaded_file($_FILES['file']['tmp_name'], TEMPREP.$file);
                $datas = file(TEMPREP.$file);
                $cr = preg_match('!type = \'([^\']*)\';!', trim($datas[1]), $res);
                if ($cr != 1) $cr = preg_match('!type = "([^"]*)";!', trim($datas[1]), $res);
                if ($cr != 1) {
                    // Format inconnu (type non trouvé)
                    unlink(TEMPREP.$file);
                    $content = '
<form name="import" method="post" action="admin.php?lng='.$lng.'&amp;pg=import">
<input type="hidden" name="step" value="1" />
<fieldset class="w-100 m-0 p-0" style="padding-top:12px;">
  <p class="error">'.$cr.'<br>'.$datas[1].'<br>'.$admin[1105].'</p>
</fieldset>
<div class="row quest p-0 mt-2 w-100 m-auto">
  <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
  <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[1101], '', '', $imgdroite).'</div></div>
</div>
</form>';
                } else {
                    // Corriger les short_open_tag <? -> <?php si besoin
                    $file_contents = file_get_contents(TEMPREP.$file);
                    if (preg_match("!\<\?\s!", $file_contents)) {
                        $file_contents = preg_replace('!\<\?!', '<?php', $file_contents);
                        $file_contents = preg_replace('!\<\?phpphp!', '<?php', $file_contents);
                        file_put_contents(TEMPREP.$file, $file_contents);
                    }
                    unset($file_contents);
                    // Type trouvé
                    $type = $res[1];
                    $typesImportables = array(
                        'ag', 'ar', 'ba', 'bl', 'bs', 'ci', 'dn', 
						'fa', 'gb', 'fb', 'f1', 'f2', 'f3', 'f4', 
						'sp', 'ed', 'fr', 'ft', 'li', 'ne', 'nl', 
						'ph', 'rs',	'mb', 'om', 'dm', 'od', 'sn', 
						'mi', 'rb', 'ru');
                    if (!in_array($type, $typesImportables)) {
                        // Type non importable
                        unlink(TEMPREP.$file);
                        $content ='
<form name="import" method="post" action="admin.php?lng='.$lng.'&amp;pg=import">
<fieldset class="w-100 m-0 p-0">
  <input type="hidden" name="step" value="1" />
  <p class="error">'.$admin[1104].'</p>
</fieldset>
<div class="row quest p-0 mt-2 w-100 m-auto">
  <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
  <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[1101], '', '', $imgdroite).'</div></div>
</div>
</form>';
                    } else {
                        include TEMPREP.$file;
						$in_charset = preg_match('!doc!', $file) ? 'ISO-8859-1' : $charset;
                        $new        = IsNew($fileid, $type);
						$fieldweb   = empty($fieldweb) ? '' : $fieldweb;
						$fieldmail  = empty($fieldmail) ? '' : $fieldmail;
						$fieldmod   = empty($fieldmod) ? '' : $fieldmod;
						$fielda1    = $in_charset == $charset ? $fielda1 : str_replace($pats, $patr, $fielda1);
						$fielda2    = $in_charset == $charset ? $fielda2 : str_replace($pats, $patr, $fielda2);
						$fieldb1    = $in_charset == $charset ? $fieldb1 : str_replace($pats, $patr, $fieldb1);
						$fieldb2    = $in_charset == $charset ? $fieldb2 : str_replace($pats, $patr, $fieldb2);
						$fieldc1    = $in_charset == $charset ? $fieldc1 : str_replace($pats, $patr, $fieldc1);
						$fieldc2    = $in_charset == $charset ? $fieldc2 : str_replace($pats, $patr, $fieldc2);
						$content    = '
<form name="import" method="post" action="admin.php?lng='.$lng.'&amp;pg=import">
<input type="hidden" name="step" value="3" />
<input type="hidden" name="file" value="'.$file.'" />
<fieldset class="w-100 m-0 p-0" style="padding-top:12px;">
<p style="text-align:center;">'.$admin[1106].'<br />'.$file.' - '.$in_charset.'</p>
<div class="table-responsive-md">
<table id="doc" class="table table-bordered">
<tr style="text-align:center;">
  <td class="align-middle" style="width:70px;"></td>
  <td class="align-middle" style="width:560px;"><b>'.$admin[1109].'</b></td>
  <td class="align-middle" style="width:170px;"><b>'.$admin[1110].'</b></td>
</tr>
<tr>
  <td class="align-middle">$type</td>
  <td class="align-middle">'.$type.'</td>
  <td class="align-middle">'.InputType($type).'</td>
</tr>
<tr>
  <td class="align-middle">$fileid</td>
  <td class="align-middle">'.$fileid.'</td>
  <td class="align-middle">'.InputFileId($fileid, $new).'</td>
</tr>
<tr>
  <td class="align-middle">$status</td>
  <td class="align-middle">'.$status.'</td>
  <td class="align-middle">'.SelectStatus($status).'</td>
</tr>
<tr>
  <td class="align-middle">$author</td>
  <td class="align-middle">'.iconv($in_charset, $charset, $author).'</td>
  <td class="align-middle">'.InputAuthor(iconv($in_charset, $charset, $author)).'</td>
</tr>
<tr>
  <td class="align-middle">$creadate</td>
  <td class="align-middle">'.FormatDate($creadate).'</td>
  <td class="align-middle">'.InputCreaDate($creadate, $new).'</td> 
</tr>
<tr>
  <td class="align-middle">$moddate</td>
  <td class="align-middle">'.FormatDate($moddate).' </td>
  <td class="align-middle">'.InputModDate($moddate, $new).'</td>
</tr>
<tr><td>$email</td>  <td>'.$email.'</td> <td>'.InputEmail($email).'</td> </tr>
<tr>
  <td class="align-middle">$fielda1</td>
  <td class="align-middle">'.iconv($in_charset, $charset, $fielda1).'</td>
  <td class="align-middle">'.InputFielda1($type, iconv($in_charset, $charset, $fielda1)).'</td>
</tr>
<tr>
  <td class="align-middle">$fielda2</td>
  <td class="align-middle">'.iconv($in_charset, $charset, $fielda2).'</td>
  <td class="align-middle">'.InputFielda2($type, iconv($in_charset, $charset, $fielda2)).'</td>
</tr>
<tr>
  <td class="align-middle">$fieldb1</td>
  <td class="align-middle">'.iconv($in_charset, $charset, $fieldb1).'</td>
  <td class="align-middle">'.InputFieldb1($type, iconv($in_charset, $charset, $fieldb1), $file).'</td>
</tr>
<tr>
  <td class="align-middle">$fieldb2</td>
  <td class="align-middle">'.iconv($in_charset, $charset, $fieldb2).'</td>
  <td class="align-middle">'.InputFieldb2($type, iconv($in_charset, $charset, $fieldb2), $file).'</td>
</tr>
<tr>
  <td class="align-middle">$fieldc1</td>
  <td class="align-middle"><div class="short">'.iconv($in_charset, $charset, $fieldc1).'</div></td>
  <td class="align-middle">'.$admin[1108].'</td>
</tr>
<tr>
  <td class="align-middle">$fieldc2</td>
  <td class="align-middle"><div class="short">'.iconv($in_charset, $charset, $fieldc2).'</div></td>
  <td class="align-middle">'.$admin[1108].'</td>
</tr>
<tr>
  <td class="align-middle">$fieldd1</td>
  <td class="align-middle">'.$fieldd1.'</td>
  <td class="align-middle">'.InputFieldd1($type, $fieldd1, $file).'</td>
</tr>
<tr>
  <td class="align-middle">$fieldd2</td>
  <td class="align-middle">'.$fieldd2.'</td>
  <td class="align-middle">'.$admin[1108].'</td>
</tr>
<tr>
  <td class="align-middle">$fieldweb </td>
  <td class="align-middle">'.$fieldweb .'</td>
  <td class="align-middle">'.InputFieldweb($type, iconv($in_charset, $charset, $fieldweb), $file).'</td>
</tr>
<tr>
  <td class="align-middle">$fieldmail</td>
  <td class="align-middle">'.$fieldmail.'</td>
  <td class="align-middle">'.$admin[1108].'</td>
</tr>
<tr>
  <td class="align-middle">$fieldmod </td>
  <td class="align-middle">'.$fieldmod .'</td>
  <td class="align-middle">'.$admin[1108].'</td>
</tr>
</table>
</div>
</fieldset>
<div class="quest" style="padding:2px 20px;">
  <div style="float:left">'.SubmitButton($admin[1101], 'step.value=1;', $imggauche, '').'</div>
  <div style="float:right">'.SubmitButton($admin[1107], '', '', $imgdroite).'</div>
  <div style="clear:both"></div>
</div>
</form>';
                    }
                }
            } else {
                // Le fichier n'est pas chargé
                $content = '
<form name="import" method="post" action="admin.php?lng='.$lng.'&amp;pg=import">
<input type="hidden" name="step" value="1" />
<fieldset class="w-100 m-0 p-0" style="padding-top:12px;">
  <p class="error">'.$admin[1103].'</p>
</fieldset>
<div class="row quest p-0 mt-2 w-100 m-auto">
  <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
  <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[1101], '', '', $imgdroite).'</div></div>
</div>
</form>';
            }
        }
    } else {
        // Pas de nom de fichier
        header('location:admin.php?lng='.$lng.'&pg=import');
        exit;
    }
	break;

case 3 :
    $new        = import('new', 'POST', FALSE);
    $file       = import('file');
    $introStep  = $admin[1098];
	$in_charset = preg_match('!doc!', $file) ? 'ISO-8859-1' : $charset;
    include TEMPREP.$file;
	$fieldweb  = empty($fieldweb) ? '' : $fieldweb;
	$fieldmail = empty($fieldmail) ? '' : $fieldmail;
	$fieldmod  = empty($fieldmod) ? '' : $fieldmod;
	$fieldc1   = $in_charset == $charset ? $fieldc1 : str_replace($pats, $patr, $fieldc1);
	$fieldc2   = $in_charset == $charset ? $fieldc2 : str_replace($pats, $patr, $fieldc2);
    $data[0]   = $new['type'];
    $data[1]   = -1 == $new['fileid'] ? '' : $new['fileid'];
    $data[2]   = $new['status'];
    $data[3]   = $new['creadate'];
    $data[4]   = $new['moddate'];
    $data[5]   = $new['author'];
    $data[6]   = $new['email'];
    $data[7]   = $new['fielda1'];
    $data[8]   = $new['fielda2'];
    $data[9]   = $type == TYP_ART ? iconv($in_charset, $charset, $fieldb1) : $new['fieldb1'];
    $data[10]  = $type == TYP_ART ? iconv($in_charset, $charset, $fieldb2) : $new['fieldb2'];
    $data[11]  = $in_charset == $charset ? $fieldc1 : iconv($in_charset, $charset, $fieldc1);
    $data[12]  = $in_charset == $charset ? $fieldc2 : iconv($in_charset, $charset, $fieldc2);
    $data[13]  = $new['fieldd1'];
    $data[14]  = $fieldd2;
    $data[15]  = $new['fieldweb'];
    $data[16]  = $fieldmail;
    $data[17]  = $fieldmod;
    $id  = ActionOnFields(-1 == $new['fileid'] ? 'add' : 'mod', $data);
    $txt = sprintf((-1 == $new['fileid'] ? $admin[1113] : $admin[1114]), $id);
    unlink(TEMPREP.$file);
	if ($new['type'] == TYP_ART && substr($new['fieldd1'], 0, 3) == 'MNU') {
		$mnu_id   = substr($new['fieldd1'], 4);
		$dbw      = SelectDbFieldsByField(ReadDBFields(DBOPTMENU), 5, $mnu_id);
		if (!isset($tconf)) $tconf=0;
		$url1     = 'articles.php?lng='.$lang[0].'&pg='.$id.'&mnuid='.$mnu_id.'&tconfig='.$tconf.'||'.$lang[0].'-'.$urlrw[20].'-103-'.$id.'-'.$mnu_id.'-'.$tconf;
		$url2     = 'articles.php?lng='.$lang[1].'&pg='.$id.'&mnuid='.$mnu_id.'&tconfig='.$tconf.'||'.$lang[1].'-'.$urlrw[20].'-103-'.$id.'-'.$mnu_id.'-'.$tconf;
		$data[0]  = TYP_OPTMENU;
		$data[1]  = '';
		$data[11] = '_SELF';
		$data[12] = $new['fieldweb'];
		$data[13] = $url1;
		$data[14] = $url2;
		$data[15] = $mnu_id;
		$data[16] = 0;
		$id = ActionOnFields('add', $data);
	}
	$typeToEdit = array(
		'ag' => 'agenda',  'ar' => 'art',     'ba' => 'banner', 'bl' => 'blog', 
		'bs' => 'bss',     'ci' => 'think',   'dn' => 'dnload', 'fa' => 'faq', 
		'gb' => 'guestbk', 'fb' => 'freebox', 'fr' => 'forum',  'li' => 'links', 
		'ne' => 'news',    'nl' => 'nwl',     'ph' => 'photo',  'rs' => 'rss',
		'mb' => 'menu',    'om' => 'optmenu', 'dm' => 'ddmenu', 'od' => 'optddmenu', 
		'sn' => 'socnet',  'mi' => 'menuico', 'rb' => 'reblog', 'ru' => 'rules');
    $content = '
<fieldset class="w-100 m-0 p-0" style="padding-top:12px;">
<p style="text-align:center; font-weight:bold;">'.$txt.'</p>
</fieldset>
<div class="quest" style="padding:2px 20px;">
  <form name="import" method="post" action="admin.php?lng='.$lng.'&amp;pg=import">
  <input type="hidden" name="step" value="1" />
  <div style="float:left">'.SubmitButton($admin[1115], '', $imggauche, '').'</div>
  </form>
  <form name="import" method="post" action="admin.php?lng='.$lng.'&amp;pg='.$typeToEdit[$data[0]].'&amp;form=2&amp;id='.$id.'">
  <div style="float:right">'.SubmitButton($admin[1383], '', '', $imgdroite).'</div>
  </form>
  <div style="clear:both"></div>
</div>';
	break;
}

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[1094]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[1094], '100%');
CreateDir(TEMPREP);

echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=998&mnuid=937&tconfig=0#z2').$intro.'</p>
<hr />
<div class="w-100 m-auto" style="max-width:800px;">
<p class="forum" style="text-align:left; padding:5px 20px;">'.$introStep.'</p>
'.$content.'
</div>';

btable();
include CHEMIN.'admin/bpage.inc';
?>