<?php
/*******************************************************************************
 *   Load Includes
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

if (stristr($_SERVER['SCRIPT_NAME'], 'func_news.php')) {
  header('location:../index.php');
  die();
}

################################################################################

function fbox_news($pos = NULL, $args = NULL) {
    global $id, $pg, $lng, $lang, $drtuser, $members, $nom, $page, $site, $serviz, $user, $userprefs, $web, 
	     $topmess, $prt, $urlrw, $tconfig, $charset, $z2;
    global $type, $fileid, $status, $creadate, $moddate, $author, $email,
         $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
         $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
    if (!isAccessGranted($members[13]) || $serviz[8] != 'on') return FALSE;
    static $nbox = 0;
    $nbox++;
    $out = '';
    $id  = strip_tags($id);
    $max_height  = isset($site[29]) && $site[29] > 0 && empty($prt) ? $site[29] : 0;
    $max_height2 = isset($site[13]) && $site[13] > 0 && empty($prt) ? $site[13] : 0;
    $outrs = '';
    if ($prt == 0 && $serviz[106] == 'on') {
        include CONFIGREP.'sncode.inc';
        if (!empty($coders))
            $outrs .= '
<div class="codersNews">'.$coders.'</div>';
    }
    $out .= '
<div class="headNews text-center">';
    if ($serviz[16] == 'on' && empty($prt)) {
        $rssnewsurl = $site[3].sprintf(RSSNEWS, $lng);
        $out .= '
  <style>
    .headNews .overpop { padding-top: 20px; }
    .overpop a i { margin-top: -20px; }
  </style>
  <script>
    $(document).ready(function(){
      $(\'.rssNews\').popover({
        title:"RSS",
        content:"'.$web[221].' : <br /><b> '.$rssnewsurl.'</b>",
        trigger:"hover",
        placement:"auto",
        offset:"0 10 10 10",
        html:true
      });
    });
  </script>
  <div class="overpop pt-0 pl-2">
    <a href="'.CHEMIN.'newsrss.php?lng='.$lng.'&url=news&tconfig='.$tconfig.$z2.'" class="rssNews float-left">
      <i class="fas fa-rss"></i>
    </a>
  </div>';                            
    }
    $out .= '
  <div class="divnewline"></div>
</div>';
    if (!empty($pg)) {
        $db1 = SelectDbFieldsByField(ReadDbFields(DBNEWS), 4, $pg);
        $db2 = array_filter($db1, 'IsNotEmptyTitle');
        if (empty($db2) || 1 != count($db2)) {
            if ($site[10] != '')
                header('location:'.$site[3].'error.php?lng='.$lng.'&err=404');
            else
                header('HTTP/1.0 404 Not Found');
            exit;
        } else {
            if (isAccessGranted($db2[0][6]) && isAuthorizedPublication($db2[0][5]))
                $dbwnews = array(array(TYP_NEWS, $db2[0][5], $pg, 'a'));
            else
                $dbwnews = array();
        }
    } else {
        $db1 = ReadDbFields(DBNEWS);
        $db2 = array_filter($db1, 'IsNotEmptyTitle');
        $dbwnews = array();
        foreach ($db2 as $db) {
            if (isAccessGranted($db[6]) && isAuthorizedPublication($db[5])) {
                $dbwnews[] = array(TYP_NEWS, $db[5], $db[4], 'a');
            }
        }
        @rsort($dbwnews);
    }
    unset($db1, $db2);
    if ($pos == BOX_EDITO) { // edito
        $minnews = 0;
        $maxnews = Min($site[4], count($dbwnews));
        $id = 1;
    }
    if (!empty($dbwnews)) {
        for ($i = $minnews; $i < $maxnews; $i++) {
            ReadDoc($dbwnews[$i][2]);
            $img = getNewUpdateDoc($creadate, $moddate, $serviz[137]);
            if ($lng == $lang[0]) {
                $txt1 = $fieldb1;
                $txt2 = $fieldc1;
            }
            else {
                $txt1 = $fieldb2;
                $txt2 = $fieldc2;
            }
            $txt2 .= $outrs;
            // date création
            $txt72 = $serviz[72] == '' ? ' - '.$web[7].' <b>'.FormatDate($creadate).'</b> ' : '';
            // auteur
            $txt73 = $serviz[73] == '' 
              ? (empty($txt72) ? ' - ' : ' ').$web[6].' '.($fieldd1 == 'on' ? displayWriteEmail('boxnews'.$i, $author, $email) : '<b>'.$author.'</b>')
              : '';
            // icone
            if (!empty($fieldd2)) {
                $txt1 = '<img src="'.CHEMIN.'img/'.$fieldd2.'" class="'.($serviz[152] == 'on' ? 'float-left' : 'float-right').'" alt="'.$fieldd2.'" />'.$txt1;
            }
            $out .= '
  <div class="bord">
    <div class="quest '.($serviz[152] != 'on' ? 'text-left' : 'text-right').'">
	  <a href="'.$site[3].($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[8].'-22-'.$fileid.'-'.$tconfig.$z2 
          : $site['NE'].'.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;tconfig='.$tconfig.$z2).'" title="'.$web[511].'">
        '.$img.'<b>'.$txt1.'</b>
      </a>
      '.$txt72.$txt73.'
    </div>';
            if ($pos == BOX_EDITO) { // edito : nouvelle réduite
                $txt3 = '
<style>
.repNews {
  max-height: '.($max_height > 0 ? $max_height.'px;' : 'none;').'
  overflow: '.($max_height > 0 ? 'hidden;' : 'visible;').'
  background: transparent;
}
</style>';
                if (!empty($txt2)) {
                    $txt3 .= '
    <div id="texte3'.$i.'" class="w-100 rep editoNews">
      <div class="rep repNews">
        '.$txt2.'
      </div><div class="clearfix"></div>';
                        if ($max_height > 0) $txt3 .= '
      <p class="text-right c-pointer pr-5">
        <a href="javascript:ActiveMenu(\'texte3\',\'texte2\',0,'.$maxnews.','.$i.')" class="box" title="'.$web[296].'">
          '.$web[296].' <i class="far fa-plus-square"></i>
        </a>
      </p>';
                        $txt3 .= '
    </div>' ;
                        if ($max_height > 0) $txt3 .= '
    <div id="texte2'.$i.'" class="w-100 rep txt2News" style="display:none;">'.$txt2.'
      <p class="text-right c-pointer pr-5">
        <a href="#news'.$pos.$i.'" title="'.$web[57].'" onclick="cache(\'texte2'.$i.'\');montre(\'texte3'.$i.'\')">
          '.$web[57].' <i class="far fa-minus-square"></i>
        </a>
      </p>
    </div><div class="clearfix"></div>';
                }
                $out .= $txt3;
            } 
            $out .= displayQuickConfig($pos, $dbwnews[$i][2], 'news&amp;form=2&amp;id='.$dbwnews[$i][2], 'news&amp;act=i&amp;id='.$dbwnews[$i][2], 'news&amp;del='.$dbwnews[$i][2], 14);
            $out .= '
  </div>
  <br />';
        }
    } elseif (!empty($pg)) {
        $out .= '
  <p>'.$web[36].'</p>';
    }
    $pg = empty($pg) ? '0' : $pg;
    if (count($dbwnews) > 1 && $site[4] > 1) {
        $topmess = sprintf($web[2], min($site[4], count($dbwnews)));
    } else {
        $topmess = $web[145];
    }
    $class = ' NEW'.$pos.$tconfig;
    if ($page[9] != '') {
        $topmess = '<i class="far fa-newspaper float-right pr-2"></i>'.$topmess;
    }
    return $maxnews == 0 ? false : boxwide($pos, $topmess, $out, $class);
}

?>