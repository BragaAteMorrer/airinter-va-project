<?php
/*******************************************************************************
 *   Blog update routine
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

if (stristr($_SERVER['SCRIPT_NAME'], 'mod_blog.inc')) {
    header('location:../index.php');
    die();
}

if (!empty($pg)) {
    ReadDoc($pg);
    $fileid = $pg;
    if ($mod == 'maj') {
        $note = '<hr /><u>'.$web[417].'</u> '.$web[6].' '.$userprefs[1].' '.$web[7].' '.FormatDate(date('YmdHi'));
        if ($lang[0] == $lng) {
            $fielda1 = addslashes(stripslashes($rub));
            $fieldb1 = addslashes(stripslashes($ptit));
            $fieldc1 = addslashes(stripslashes(replaceimg(ReplaceDolar(PutBR($ptxt))))).$note;
        }
        else {
            $fielda2 = addslashes(stripslashes($rub));
            $fieldb2 = addslashes(stripslashes($ptit));
            $fieldc2 = addslashes(stripslashes(replaceimg(ReplaceDolar(PutBR($ptxt))))).$note;
        }
		$fieldd1 = ($pehide == 'on') ? 'on' : '';
        WriteDoc();

		if ($supervision[8] == "on") {
			$eSub  = stripslashes($site[($lang[0] == $lng ? 0 : 11)])." ".$web[688];
			$eMsg  = stripslashes($site[($lang[0] == $lng ? 0 : 11)])." - ".$web[688].", ".$web[104]." ".$datefrmt.".<br />";
			$eMsg .= $web[107].stripslashes($pseudo)." (".stripslashes($pemail).").<br />";
			$eMsg .= $web[108].stripslashes($ptit)."<br />";
			$eMsg .= '<hr />'.$web[109].'<br />'.ForceToAbsolute(stripslashes($ptxt)).$note;
			$eMsg .= "<hr />".$web[349]." : ".$web[354].$id;
			$eMsg .= "<br />".$web[360].'<a href="'.$site[3].$site['BL'].".php?lng=".$lng."&pg=".$id.'">'.$site[3].$site['BL'].".php?lng=".$lng."&pg=".$id.'</a>';
			$eMsg .= "<br />".$web[350].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=blog&form=2&id=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=blog&form=2&id=".$id.'</a>';
			if ($site[30] == "1") {
				$eMsg .= "<br />".$web[351].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=blog&act=a&id=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=blog&act=a&id=".$id.'</a>';
			} else {
				$eMsg .= "<br />".$web[352].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=blog&act=i&id=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=blog&act=i&id=".$id.'</a>';
			}
			$eMsg .= "<br />".$web[353].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=blog&del=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=blog&del=".$id.'</a>';
			$eMsg .= "<hr />".$infos;
			$maillist = GetListCollaboratorEmail(TYP_BLOG);
			eMailHtmlTo($eSub, $eMsg, trim(implode(' ', $maillist).' '.trim($supervision[10]).' '.$user[1]), $user[1], '', $pemail);
		}
	}
}
?>