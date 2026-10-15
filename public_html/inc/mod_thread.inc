<?php
/*******************************************************************************
 *   Thread update routine
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
 * v6.00.11 (March 09, 2023) : correction variable $bodycorps
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'mod_thread.inc')) {
    header('location:../index.php');
    die();
}
if (!empty($pg)) {
    $th_id = '';
    ReadDoc($pg);
    $fileid = $pg;
    if ($mod == 'up' ) {
        $moddate   = date('YmdHi', time() + (3600 * $forum[5]));
        $fieldmod  =  'u#'.$userprefs[1].'#'.date('YmdHi');
        $web[111] .= ' : '.$web[364];
    }
    elseif ($mod == 'top' ) {
        $moddate   = $forum[6] == 0 ? '301201010101' : date('YmdHi', time() + (86400 * $forum[6]));
        $fieldmod  =  't#'.$userprefs[1].'#'.$moddate;
        $web[111] .= '  : '.$web[365];
    }
    elseif ($mod == 'lock' ) {
        $fieldmod =  'c#'.$userprefs[1].'#'.date('YmdHi');
        $web[111] .= ' : '.$web[363];
    }
    elseif ($mod == 'stop' ) {
        $fieldmod =  's#'.$userprefs[1].'#'.date('YmdHi');
        $web[111] .= ' : '.$web[363];
    }
    elseif ($mod == 'raz' ) {
        $fieldmod = '';
        $web[111] .= ' : '.$web[621];
    }
    elseif ($mod == 'maj' ) {
        $fieldmod = '';
        $web[111] .= ' : '.$web[369];
    }
    elseif ($mod == 'move' ) {
        $fieldmod = '';
        $web[111] .= ' : '.$web[632];
    }
    $fieldb1 = addslashes($fieldb1);
    if ($mod == 'maj') {
		include TEMPREP.$filesign;
        $note      = '<hr /><u>'.$web[417].'</u> '.$web[6].' '.$userprefs[1].' '.$web[7].' '.FormatDate(date('YmdHi'));
        $fieldc1   = addslashes(stripslashes(replaceimg(ReplaceDolar(PutBR($ptxt)))).$signforum).$note;
		DestroyDBfile(TEMPREP.$filesign);
		$fieldd1   = ($pehide == 'on') ? 'on' : '';
		$fieldmail = $replymail;
    } elseif ($mod == 'move') {
		include TEMPREP.$filesign;
		$fieldb2   = $tocat;
        $note      = '<hr /><u>'.$web[635].'</u> '.$web[6].' '.$userprefs[1].' '.$web[7].' '.FormatDate(date('YmdHi'));
        $fieldc1   = addslashes(stripslashes(replaceimg(ReplaceDolar(PutBR($ptxt)))).$signforum).$note;
		DestroyDBfile(TEMPREP.$filesign);
		$fieldd1   = ($pehide == 'on') ? 'on' : '';
		$fieldmail = $replymail;
    } elseif ($mod == 'stop') {
		include TEMPREP.$filesign;
        $fieldc1 = addslashes(stripslashes(replaceimg(ReplaceDolar(PutBR($ptxt)))).$signforum);
		DestroyDBfile(TEMPREP.$filesign);
    } else {
        $fieldc1 = addslashes(stripslashes($fieldc1));
    }
    WriteDoc();
    $data[7]  = $fielda1; // n° thread
    $data[8]  = $fielda2; // n° response
    $data[10] = $fieldb2; // n° catégorie
    if ($typ == TYP_FORUM) {
        $catthread = getForumCatFull($fieldb2); // n° id category
    } else {
        $dbf = ReadDBFields(DBFORUM);
        for ($i = 0; $i < count($dbf); $i++) {
            if ($dbf[$i][1] == $num) {
                $catthread = getForumCatFull($dbf[$i][12]); // n° id category
                break;
            }
        }
        unset($dbf);
    }

    if ($mod == 'up' || $mod == 'top') { // maj date de modif forum-thread
        $newdate = $moddate;
        $dbf     = ReadDBFields(DBFORUM);
        for ($i = 0; $i < count($dbf); $i++) {
            if ($dbf[$i][2] == $pg) {
				$dbf[$i][0] = $moddate;
				$n_th       = $dbf[$i][1];
				$n_re       = $dbf[$i][7];
				break;
            }
        }
        @rsort($dbf, SORT_REGULAR);
        WriteDBFields(DBFORUM, $dbf);
        $dbth = ReadDBFields(DBTHREAD);
        for ($i = 0; $i < count($dbth); $i++) {
            if ($dbth[$i][1] == $n_th && $dbth[$i][2] == $n_re) {
                $dbth[$i][0] = $moddate;
                $th_id = $dbth[$i][3];
            }
        }
        WriteDBFields(DBTHREAD, $dbth);
        ReadDoc($th_id);
        $fileid  = $th_id;
        $moddate = $newdate;
        $fieldb1 = addslashes(stripslashes($fieldb1));
        $fieldb2 = addslashes(stripslashes($fieldb2));
        $fieldc1 = addslashes(stripslashes($fieldc1));
        $fieldc2 = addslashes(stripslashes($fieldc2));
        if ($mod == 'up' ) {
            $fieldmod =  'u#'.$userprefs[1].'#'.date('YmdHi');
        } else {
            $fieldmod =  't#'.$userprefs[1].'#'.$newdate;
        }
		if (($forum[6] != 0 && $mod == 'top') || $mod == 'up') WriteDoc();
    }
	if ($mod == 'maj') {
		$dbworkmail = ReadDBFields(DBFORUM);
		for ($i = 0; $i < count($dbworkmail); $i++) {
			if ($num == $dbworkmail[$i][1]){
				$sujetthread = $dbworkmail[$i][5];
				$catthread   = $dbworkmail[$i][12];
				break;
			}
		}
		$dbmailuniq = Array();
		$dbworkuniq = ReadDBFields(DBTHREAD);
		for ($i = 0; $i < count($dbworkuniq); $i++) {
		if ($num == $dbworkuniq[$i][1] && $dbworkuniq[$i][2] == 0) {
			$pgview = $dbworkuniq[$i][3];
		}
		if ($num == $dbworkuniq[$i][1] && $dbworkuniq[$i][9] == 'on') {
			$dbmailuniq[] = $dbworkuniq[$i][5];
		}
		}
		$mailuniq = ValUnique($dbmailuniq);
		for ($i = 0; $i < count($mailuniq); $i++) {
			$to    = $mailuniq[$i];
			$sujet = $web[345].$sujetthread;
			$bodycorps  = $web[346].$sujetthread.'<br /><br />';
			$bodycorps .= $web[109].'<hr />'.ForceToAbsolute(stripslashes($ptxt)).'<hr /><br />';
			$bodycorps .= $web[347].'<br />';
			$bodycorps .= '<a href="'.$site[3].($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[5].'-4-'.$pgview.'-'.getForumCatId($catthread).$z2 
              : $site['TH'].'.php?lng='.$lng.'&pg='.$pgview.'&cat='.getForumCatId($catthread).$z2).'">';
			$bodycorps .= $site[3].($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[5].'-4-'.$pgview.'-'.getForumCatId($catthread).$z2 
              : $site['TH'].'.php?lng='.$lng.'&pg='.$pgview.'&cat='.getForumCatId($catthread).$z2);
			$bodycorps .= '</a><br /><br />';
			$bodycorps .= $web[348].'<br />'.$user[0];
			eMailHtmlTo($sujet, $bodycorps, $to);
		}
	}
	if ($mod == 'move') {
        $dbf = ReadDBFields(DBFORUM);
        for ($i = 0; $i < count($dbf); $i++) {
            if ($dbf[$i][2] == $pg) {
				$dbf[$i][12] = $tocat;
				break;
            }
        }
        @rsort($dbf, SORT_REGULAR);
        WriteDBFields(DBFORUM, $dbf);
        $dbth = ReadDBFields(DBTHREAD);
        for ($i = 0; $i < count($dbth); $i++) {
            if ($dbth[$i][1] == $fielda1) {
                $dbth[$i][8] = $tocat;
            }
        }
        WriteDBFields(DBTHREAD, $dbth);
		$to    = $email;
		$sujet = $web[632].$fieldb1;
		$bodycorps  = sprintf($web[636], $fieldb1).'<br /><br />';
		$bodycorps .= $web[109].'<hr />'.ForceToAbsolute(stripslashes($ptxt)).'<hr /><br />';
		$bodycorps .= $web[347].'<br />';
		$bodycorps .= '<a href="'.$site[3].($site['URLR'] == 'on' ? $lng.'-'.$urlrw[5].'-4-'.$pg.'-'.$tocat.$z2 : $site['TH'].'.php?lng='.$lng.'&pg='.$pg.'&cat='.$tocat.$z2).'">';
		$bodycorps .= $site[3].($site['URLR'] == 'on' ? $lng.'-'.$urlrw[5].'-4-'.$pg.'-'.$tocat.$z2 : $site['TH'].'.php?lng='.$lng.'&pg='.$pg.'&cat='.$tocat.$z2);
		$bodycorps .= '</a><br /><br />';
		$bodycorps .= $web[348].'<br />'.$user[0];
		eMailHtmlTo($sujet, $bodycorps, $to);
	}
    unset($dbf, $dbth);
}
?>
