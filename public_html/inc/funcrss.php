<?php
/*******************************************************************************
 *   RSS News Publication Functions
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

if (stristr($_SERVER['SCRIPT_NAME'], 'funcrss.inc')) {
    header('location:../index.php');
    die();
}

function PubDate($creadate) {
    return date('r',
        mktime(
            intval(substr($creadate, 8, 2)),
            intval(substr($creadate, 10, 2)),
            0,
            intval(substr($creadate, 4, 2)),
            intval(substr($creadate, 6, 2)),
            intval(substr($creadate, 0, 4))
        ));
}

function UpdateRSSall() {
    global $site, $lang, $lng, $serviz,
        $type, $fileid, $status, $creadate, $moddate, $author, $email,
        $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldmod;
	list($rsshead1, $rsshead2, $rssfoot) = MakeRSSheadfoot();
    if ($serviz[103] == 'on' && ($serviz[16] == 'on' || $serviz[54] == 'on' || $serviz[60] == 'on')) {
		$rssall1 = $rssall2 = '';
		
		if ($serviz[16] == 'on') {
			$db1 = ReadDBFields(DBNEWS);
			$db2 = array();
            foreach ($db1 as $db) {
                if (empty($db[6])) {
                    ReadDoc($db[4]);
                    if (TYP_NEWS != $type) continue;
                    $db2[] = array(substr($moddate, 0, 12), $fileid);
                }
            }

			list($rsstxtl1, $rsstxtl2) = MakeRSSitems($db2, $site['NE'].'.php?lng=%s&amp;pg=%s', $serviz[37]);
			$rssall1 .= $rsstxtl1;
			$rssall2 .= $rsstxtl2;
			unset($db1, $db2);
		}
		
		if ($serviz[54] == 'on') {
			$db1 = ReadDBFields(DBBLOG);
			$db2 = array();
			foreach ($db1 as $db) {
				if (empty($db[6])) {
                    ReadDoc($db[4]);
                    if (TYP_BLOG != $type) continue;
                    $db2[] = array(substr($moddate, 0, 12), $fileid);
				}
			}

			list($rsstxtl1, $rsstxtl2) = MakeRSSitems($db2, $site['BL'].'.php?lng=%s&amp;sel=pg&amp;pg=%s', $serviz[56]);
			$rssall1 .= $rsstxtl1;
			$rssall2 .= $rsstxtl2;
			unset($db1, $db2);
		}
		
		if ($serviz[60] == 'on') {
			$db1 = ReadDBFields(DBART);
			$db2 = array();
			foreach ($db1 as $db) {
				if (empty($db[6])) {
                    ReadDoc($db[4]);
                    if (TYP_ART != $type) continue;
                    $db2[] = array(substr($moddate, 0, 12), $fileid);
				}
			}

			list($rsstxtl1, $rsstxtl2) = MakeRSSitems($db2, 'articles.php?lng=%s&amp;pg=%s', $serviz[82]);
			$rssall1 .= $rsstxtl1;
			$rssall2 .= $rsstxtl2;
			unset($db1, $db2);
		}

		WriteFullDB(CHEMIN.sprintf(RSSALL, $lang[0]), $rsshead1.$rssall1.$rssfoot);
		if ($lang[1] != '') {
			WriteFullDB(CHEMIN.sprintf(RSSALL, $lang[1]), $rsshead2.$rssall2.$rssfoot);
		}
    }
    else {
        WriteFullDB(CHEMIN.sprintf(RSSALL, $lang[0]), $rsshead1.$rssfoot);
        if ($lang[1] != '') {
            WriteFullDB(CHEMIN.sprintf(RSSALL, $lang[1]), $rsshead2.$rssfoot);
        }
    }
}

function UpdateRSSnews() {
    global $site, $lang, $lng, $serviz,
        $type, $fileid, $status, $creadate, $moddate, $author, $email,
        $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldmod;
	list($rsshead1, $rsshead2, $rssfoot) = MakeRSSheadfoot();
    if ($serviz[16] == 'on') {
        $db1 = ReadDBFields(DBNEWS);
        $db2 = array();
        foreach ($db1 as $db) {
            if (empty($db[6])) {
                ReadDoc($db[4]);
                if (TYP_NEWS != $type) continue;
                $db2[] = array(substr($moddate, 0, 12), $fileid);
            }
        }

        list($rsstxtl1, $rsstxtl2) = MakeRSSitems($db2, $site['NE'].'.php?lng=%s&amp;pg=%s', $serviz[37]);
        unset($db1, $db2);

        WriteFullDB(CHEMIN.sprintf(RSSNEWS, $lang[0]), $rsshead1.$rsstxtl1.$rssfoot);
        if ($lang[1] != '') {
            WriteFullDB(CHEMIN.sprintf(RSSNEWS, $lang[1]), $rsshead2.$rsstxtl2.$rssfoot);
        }
    }
    else {
        WriteFullDB(CHEMIN.sprintf(RSSNEWS, $lang[0]), $rsshead1.$rssfoot);
        if ($lang[1] != '') {
            WriteFullDB(CHEMIN.sprintf(RSSNEWS, $lang[1]), $rsshead2.$rssfoot);
        }
    }
}

function UpdateRSSblog() {
    global $site, $lang, $lng, $serviz,
        $type, $fileid, $status, $creadate, $moddate, $author, $email,
        $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldmod;
    list($rsshead1, $rsshead2, $rssfoot) = MakeRSSheadfoot();
    if ($serviz[54] == 'on') {
        $db1 = ReadDBFields(DBBLOG);
        $db2 = array();
        foreach ($db1 as $db) {
            if (empty($db[6])) {
                ReadDoc($db[4]);
                if (TYP_BLOG != $type) continue;
                $db2[] = array(substr($moddate, 0, 12), $fileid);
            }
        }

        list($rsstxtl1, $rsstxtl2) = MakeRSSitems($db2, $site['BL'].'.php?lng=%s&amp;sel=pg&amp;pg=%s', $serviz[56]);
        unset($db1, $db2);

        WriteFullDB(CHEMIN.sprintf(RSSBLOG, $lang[0]), $rsshead1.$rsstxtl1.$rssfoot);
        if ($lang[1] != '') {
            WriteFullDB(CHEMIN.sprintf(RSSBLOG, $lang[1]), $rsshead2.$rsstxtl2.$rssfoot);
        }
    }
    else {
        WriteFullDB(CHEMIN.sprintf(RSSBLOG, $lang[0]), $rsshead1.$rssfoot);
        if ($lang[1] != '') {
            WriteFullDB(CHEMIN.sprintf(RSSBLOG, $lang[1]), $rsshead2.$rssfoot);
        }
    }
}

function UpdateRSSart() {
    global $lang, $lng, $serviz,
        $type, $fileid, $status, $creadate, $moddate, $author, $email,
        $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldmod;
	list($rsshead1, $rsshead2, $rssfoot) = MakeRSSheadfoot();
    if ($serviz[60] == 'on') {
        $db1 = ReadDBFields(DBART);
        $db2 = array();
        foreach ($db1 as $db) {
            if (empty($db[6])) {
                ReadDoc($db[4]);
                if (TYP_ART != $type) continue;
                $db2[] = array(substr($moddate, 0, 12), $fileid);
            }
        }

        list($rsstxtl1, $rsstxtl2) = MakeRSSitems($db2, 'articles.php?lng=%s&amp;pg=%s', $serviz[82]);
        unset($db1, $db2);

        WriteFullDB(CHEMIN.sprintf(RSSART, $lang[0]), $rsshead1.$rsstxtl1.$rssfoot);
        if ($lang[1] != '') {
            WriteFullDB(CHEMIN.sprintf(RSSART, $lang[1]), $rsshead2.$rsstxtl2.$rssfoot);
        }
    }
    else {
        WriteFullDB(CHEMIN.sprintf(RSSART, $lang[0]), $rsshead1.$rssfoot);
        if ($lang[1] != '') {
            WriteFullDB(CHEMIN.sprintf(RSSART, $lang[1]), $rsshead2.$rssfoot);
        }
    }
}

function MakeRSSheadfoot() {
	global $site, $user, $lang, $lng, $charset, $skn_logo;
	
    $rsstxt1 = '<?xml version="1.0" encoding="'.$charset.'"?'.'>
<?xml-stylesheet type="text/css" media="screen" href="'.$site[3].'data/stylexml.css"?>
<!-- RSS generated by GuppY v'.MP_VERSION.' on '.FormatDate(GetCurrentDateTime()).' -->
<rss version="2.0">
  <channel>
    <title>'.stripslashes($site[($lang[0] == $lng ? 0 : 11)]).'</title>
    <link>'.$site[3].'</link>
    <description>'.$site[1].'</description>
    <lastBuild>'.date(DATE_RSS).'</lastBuild>';

    $rsstxt2 = '
    <docs>http://blogs.law.harvard.edu/tech/rss</docs>
    <generator>GuppY v'.MP_VERSION.'</generator>
    <managingEditor>no-response@no-spam.spam ('.$user[0].')</managingEditor>
    <webMaster>no-response@no-spam.spam ('.$user[0].')</webMaster>';
	
	if ($skn_logo != '' && is_file($skn_logo)) {
		list($width, $height, $type, $attr) = getimagesize($skn_logo);
		$rsstxt2 .= '
    <image>
      <title>'.stripslashes($site[($lang[0] == $lng ? 0 : 11)]).'</title>
      <url>'.$site[3].substr($skn_logo, 3).'</url>
      <link>'.$site[3].'</link>
      <width>'.$width.'</width>
      <height>'.$height.'</height>
    </image>';
	}

    $rsshead1 = $rsstxt1.'
    <language>'.$lang[0].'</language>'.$rsstxt2;
    if ($lang[1] != '') {
        $rsshead2 = $rsstxt1.'
    <language>'.$lang[1].'</language>'.$rsstxt2;
    } else {
        $rsshead2 = '';
    }
	$rssfoot = '
  </channel>
</rss>';
    return array($rsshead1, $rsshead2, $rssfoot);
}

function MakeRSSitems($db, $rssurl, $nbitem) {
    global $site, $lang, $lng, $serviz, $meskin, $urlrw, $z2,
        $type, $fileid, $status, $creadate, $moddate, $author, $email,
        $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldmod;
    @rsort($db);
    $lng  = empty($lng) ? $lang[0] : (!in_array($lng, $lang) ? $lang[0] : $lng);
    $aml  = '';
    $wbm  = '';
    if ($lang[1] != '') {
        include CHEMIN.'inc/lang/'.$lang[1].'-web'.INCEXT;
        $par2 = $web[6];
        $le2 = $web[7];
    }
    include CHEMIN.'inc/lang/'.$lang[0].'-web'.INCEXT;
    $par1 = $web[6];
    $le1 = $web[7];
    include CHEMIN.'inc/lang/'.$lng.'-web'.INCEXT;
    $sito = $site[3];
	
	$rsstxt1 = $rsstxt2 = '';
    for ($i = 0; $i < Min(count($db),$nbitem); $i++) {
        ReadDoc($db[$i][1]);
        if (!isAccessGranted($fieldmod) || !isAuthorizedPublication($creadate)) continue;
        $fieldc1 = ForceToAbsolute($fieldc1);
        $fieldc2 = ForceToAbsolute($fieldc2);
        if (($type == TYP_BLOG && $serviz[114] == 'on') || ($type == TYP_NEWS && $serviz[73] == 'on')) $author = $web[11];
        $rsstxt1 .= '
    <item>
      <title>'.strip_tags($fieldb1).' - '.$par1.' '.$author.' '.$le1.' '.FormatDate($moddate).'</title>
      <link>'.$sito.sprintf($rssurl, $lang[0], $fileid).'</link>
      <guid>'.$sito.sprintf($rssurl, $lang[0], $fileid).'</guid>
      <description><![CDATA['.stripslashes($fieldc1).']]></description>
      <pubDate>'.PubDate($moddate).'</pubDate>
    </item>';
        if ($lang[1] != '') {
            $rsstxt2 .= '
    <item>
      <title>'.strip_tags($fieldb2).' - '.$par2.' '.$author.' '.$le2.' '.FormatDate($moddate).'</title>
      <link>'.$sito.sprintf($rssurl, $lang[1], $fileid).'</link>
      <guid>'.$sito.sprintf($rssurl, $lang[1], $fileid).'</guid>
      <description>'.stripslashes($fieldc2).']]></description>
      <pubDate><![CDATA['.PubDate($moddate).'</pubDate>
    </item>';
        }
    }
    return array($rsstxt1, $rsstxt2);
}
?>
