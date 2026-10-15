<?php
/*******************************************************************************
 *   Forum functions
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
 * v6.00.11 (March 09, 2023) : correction $dbcat, addittion test
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'func_forum.php')) {
  header('location:../index.php');
  die();
}

function isForumPage() {
    global $site;
    $sname = substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '/') + 1, -4);
    return 1 == preg_match('!'.$site['FR'].'|'.$site['FRT'].'|'.$site['TH'].'|forum|forumarch|thread|treadarch|fortopic|fortopicarch|formyposts!', $sname);
}

################################################################################

function fbox_forum($pos = NULL, $args = NULL) {
    global $lng, $lang, $forum, $members, $nom, $page, $site, $serviz, $userprefs, $web, $urlrw, $tconfig, $z2;
    global $type, $fileid, $status, $creadate, $moddate, $author, $email,
         $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
         $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
    $dbcat = array();
	if ($serviz[13] == 'on')
	   $dbcat = ReadDBFields(DBFORUMCAT);
    $okfr  = false;
    if (!empty($dbcat)) 
	{
		foreach ($dbcat as $cat) 
		{
			if (!isset($cat[5])) $cat[5] = '';
			if ($cat[5] == 'on') 
			{ 
				$okfr = true; break;
			}
		}
	}
    if (!isAccessGranted($members[5])) return FALSE;
    if ($serviz[13] == 'on' && $okfr && $site[20] > 0) {
		$dbworkcat = array();
        $l      = $lng == $lang[0] ? 0 : 1;
        $dbwork = ReadDBFields(DBFORUMCAT);
        foreach ($dbcat as $dbc)
            if ($dbc[5] != '') $dbworkcat[] = $dbc;
        $dbwork  = array();
        $dbwork1 = ReadDBFields(DBTHREAD);
        foreach ($dbwork1 as $db) {
            if ($db[8] != '') $dbcat = SelectDBFieldsByField($dbworkcat, 0, $db[8]);
            if (isForumAccessGranted($db[8]) && !empty($dbcat)) $dbwork[] = $db;
        }
        unset($dbwork1);
        @sort($dbwork);
        $dbfor  = ReadDBFields(DBFORUM);
        $dbmess = array();
        $j = 0;
        if (!empty($dbwork)) {
            $newdate = date('YmdHi',time()-(86400*$forum[8]));
            $messtop = '<i class="frm-msg_t" title="'.$web[370].'"></i>';
            $messnew = '<i class="frm-msg_n" title="'.$web[371].'"></i>';
            for ($i = count($dbwork)-1; $i >= max(0,count($dbwork)-$site[20]); $i--) {
                ReadDoc($dbwork[$i][3]);
                $img = getNewUpdateDoc($creadate, $moddate, $serviz[139]);
                $dbmess[$j] = array_pad(array(), 10, '');
                if ($fielda2 == '0') {
                    $dbmess[$j][0] = $fileid;
                    $sujet = strip_tags($fieldb1);
                } else {
                    for ($k = 0; $k < count($dbfor); $k++) {
                        if ($fielda1 == $dbfor[$k][1]) {
                            $fieldb2       = $dbfor[$k][12];
                            $dbmess[$j][0] = $dbfor[$k][2];
                            $sujet         = strip_tags($dbfor[$k][5]);
                            $nbrep         = $dbfor[$k][7];
                            $dbmess[$j][7] = $dbwork[$i][2];
                            $dbmess[$j][8] = $dbfor[$k][3];
                        }
                    }
                }
                $dbmess[$j][1] = $fieldb2;
                for ($k = 0; $k < count($dbworkcat); $k++) {
                    if ($dbworkcat[$k][0] == $fieldb2) {
                        $dbmess[$j][2] = $dbworkcat[$k][$l+1];
                    }
                }
                $dbmess[$j][3] = strip_tags(str_replace('<br />', ' &#172; ',$fieldc1));
                $dbmess[$j][4] = FormatDate($creadate).'<br />';
                if ($newdate < $creadate || $newdate < $moddate) {
                    $dbmess[$j][4] = FormatDate($creadate).'<br />'.$messnew.' ';
                }
                if ($moddate != $creadate) {
                    if ($moddate > date('YmdHi')) {
                        $dbmess[$j][4] = FormatDate(date('YmdHi')).'<br />'.$messtop.' ';
                    } elseif ($fieldmod != '') {
                        $datemod       = explode('#',$fieldmod);
                        $dbmess[$j][4] = FormatDate($datemod[2]).'<br />';
                    } else {
                        $dbmess[$j][4] = FormatDate($moddate).'<br />';
                    }
                }
                $dbmess[$j][5] = $img.$sujet;
                $dbmess[$j][6] = (ceil((int)$dbmess[$j][7] / $serviz[20]));
                $dbmess[$j][9] = $author;
                $j++;
            }
        }
        $topmess = getLabel($nom[$l+22]).' '.$web[179];
        if ($page[9] != '') {
            $topmess = '<i class="fab fa-forumbee float-right"></i>'.$topmess;
        }
        if (!isAccessGranted($members[5])) {
            $out = '
<div class="text-center web342">'.$web[342];
            if ($members[19] != '0' && $members[19] != '3') {
                $href1 = CHEMIN.($site['URLR'] == 'on' 
                  ? $lng.'-'.$urlrw[3].'-0-'.$tconfig.$z2 
                  : 'connect.php?lng='.$lng.'&tconfig='.$tconfig.$z2);
                $href2 = CHEMIN.($site['URLR'] == 'on' 
                  ? $lng.'-'.$urlrw[15].'-5-new-'.$tconfig.$z2 
                  : $site['USR'].'.php?lng='.$lng.'&uuser=new&tconfig='.$tconfig.$z2);
                echo '
  <p class="text-center">
    '.SubmitButton($web[630], 'self.location.href=\''.$href1.'\'').
      (empty($userprefs[1]) ? '&nbsp;&nbsp;'.SubmitButton($web[160], 'self.location.href=\''.$href2.'\'') : '').'
  </p>';
    }
			$out .= '
</div>';
        } else {
            $out = '';
            if (!empty($dbmess)) {
                $out .= '
<style>
.frcellheight {
  height: '.(empty($site[34]) ? 'auto' : $site[34].'px').';
  min-height: 40px;
  overflow: hidden;
}
</style>
<div class="text-center m-auto">
  <div class="table-responsive-md">
  <table class="table table-borderless text-center">';
                for ($i = 0; $i < count($dbmess); $i++) {
					$dbmess[$i][7] = empty($dbmess[$i][7]) ? 0 : $dbmess[$i][7];
                    $bgcol = $i %2 == 0 ? 'rep' : 'quest';
                    $tmpmess = '
    <tr class="'.$bgcol.' align-top text-left p-0">';
                    $urw = empty($dbmess[$i][1]) ? '-31' : '-2-'.getForumcatId($dbmess[$i][1]);
                    $tmpmess .= '
      <td class="w-22 px-0 py-2">
        <div class="px-1">
        <a href="'.($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[5].$urw.$z2 
            : $site['FR'].'.php?lng='.$lng.'&amp;cat='.getForumcatId($dbmess[$i][1]).$z2).'" title ="'.$web[297].'">
          <b>'.$dbmess[$i][2].'</b>
        </a>
        </div>
      </td>';
					$urw0 = empty($dbmess[$i][1]) ? '-41-'.$dbmess[$i][0] : '-4-'.$dbmess[$i][0].'-'.getForumcatId($dbmess[$i][1]);
					$urw1 = empty($dbmess[$i][1]) ? '-411-'.$dbmess[$i][0] : '-5-'.$dbmess[$i][0].'-'.getForumcatId($dbmess[$i][1]);
                    $tmpmess .= '
      <td class="w-55 px-0 py-2">
        <div class="text-left px-1">
          <b>'.$web[384].' 
          <a href="'.($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[5].$urw0.$z2 
              : $site['TH'].'.php?lng='.$lng.'&amp;pg='.$dbmess[$i][0].'&amp;cat='.getForumcatId($dbmess[$i][1]).$z2).'" 
                title ="'.$web[6].' '.$dbmess[$i][8].' ... '.$web[297].' ">
            '.$dbmess[$i][5].'
          </a></b>
        </div>
        <div class="text-left">
          <div class="frcellheight px-1">
            <a href="'.($site['URLR'] == 'on' 
                ? $lng.'-'.$urlrw[5].$urw1.'-'.$dbmess[$i][6].'#R'.$dbmess[$i][7] 
                : $site['TH'].'.php?lng='.$lng.'&amp;pg='.$dbmess[$i][0].'&amp;cat='.getForumcatId($dbmess[$i][1]).
                  '&amp;id='.$dbmess[$i][6].'#R'.$dbmess[$i][7]).'" title ="'.$web[6].' '.$dbmess[$i][9].' ... '.$web[297].' ">
              &nbsp;'.$dbmess[$i][3].' ...
            </a>
          </div>
        </div>  
      </td>
      <td class="w-23 text-center px-0 py-2"> '.$dbmess[$i][4].' ('.$web[6].' '.$dbmess[$i][9].')</td>
    </tr>';
                    $out .= $tmpmess;
                }
                $out .= '
  </table>
  </div>
</div>';
            }
        }
        return boxwide($pos, $topmess, $out, ' FRA'.$pos.$tconfig);
    } else {
        return FALSE;
    }
}

?>
