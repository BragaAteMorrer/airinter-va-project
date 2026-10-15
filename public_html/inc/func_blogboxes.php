<?php
/*******************************************************************************
 *   Functions for blog boxes
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
 * v6.00.07 (February 24, 2022) : adaptation to php 8.1
 ******************************************************************************/
 
if (stristr($_SERVER['SCRIPT_NAME'], 'func_blogboxes.php')) {
  header('location:../index.php');
  die();
}

################################################################################

function isBlogPage() {
    global $site;
    $sname = substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '/') + 1, -4);
    return 1 == preg_match('!'.$site['BLS'].'|'.$site['BL'].'|blogsarch|blogarch|blogrss!', $sname);
}

################################################################################

function fbox_blogcat($pos = NULL, $args = NULL) {
    global $pg, $lng, $lang, $drtuser, $serviz, $texte, $userprefs, $web, $dynabox, $site, $urlrw, 
           $page, $tconfig, $creadate, $moddate, $members, $z2;
    static $nbox = 0;
    $nbox++;
    if (!isAccessGranted($members[15])) return FALSE;
    if ($serviz[53] != 'on') return FALSE;
    $dbr = ReadDBFields(DBBRUB);
    sort($dbr);
    $j    = ($lng == $lang[0]) ? 0 : 1 ;
    $dbw  = array();
    $dbw0 = ReadDBFields(DBBLOG);
    $dbw1 = array();
    foreach ($dbr as $rub) {
        $dbw2 = SelectDBFieldsByField($dbw0, $j, $rub[$j]);
        @rsort($dbw2);
        $dbw1 = array_merge($dbw1, $dbw2);
    }
    unset($dbw0);
    foreach ($dbw1 as $db) {
        if (isAccessGranted($db[6]) && isAuthorizedPublication($db[5])) {
            $dbw[] = $db;
        }
    }
    unset($dbw1);
    if (!empty($dbw) && NULL != $pos) {
        $out       = '';
     	$k         = 0;
     	$txtbi     = array();
     	$curr_rubr = '';
     	$curr_bi   = '';
     	for ($i = 0; $i < count($dbw); $i++) {
            if (trim($dbw[$i][0+$j]) != '') {
                $dates = explode('-', $dbw[$i][5]);
                $txtbi[$k][0] = trim($dbw[$i][0+$j]).$dates[0];
                $txtbi[$k][1] = trim($dbw[$i][0+$j]);
                $txtbi[$k][2] = trim($dbw[$i][2+$j]);
                $txtbi[$k][3] = $dbw[$i][4];
                if ($pg == $dbw[$i][4]) {
                    $curr_rubr = $txtbi[$k][1];
                    $curr_bi = $txtbi[$k][3];
                }
                $k++;
            }
     	}
     	@rsort($txtbi);
    	$typeRubrique = 'blog'.$nbox;
     	$rubr         = '';
     	$marqueur     = $texte[3];
     	$curr_marq    = '<i class="fas fa-caret-right puce"></i>';
     	$url          = CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[1].'-2-pg-'.$tconfig.'-' 
          : $site['BL'].'.php?lng='.$lng.'&amp;sel=pg&amp;tconfig='.$tconfig.'&amp;pg=');
     	$out .= '';
        switch ($serviz[59]) {
        case "0" : // liste de menu
            for ($i = 0; $i < count($txtbi); $i++) {
				ReadDoc($txtbi[$i][3]);
				$img = getNewUpdateDoc($creadate, $moddate, $serviz[138]);
				if ($rubr <> $txtbi[$i][1]) {
					if (!empty($rubr)) {
						$out .= '
  </ul>';
					}
					$rubr = $txtbi[$i][1];
					$out .= '
  <p class="item">'.$rubr.'</p>
  <ul class="item">';
				}
				if ($curr_bi == $txtbi[$i][3]) {
					$out .= '
    <li class="curr_item">'.$curr_marq.' <a href="'.$url.$txtbi[$i][3].$z2.'">'.$img.$txtbi[$i][2].'</a>';
				}
				else {
                $out .= '
    <li class="item">'.$marqueur.' <a href="'.$url.$txtbi[$i][3].$z2.'">'.$img.$txtbi[$i][2].'</a>';
				}
				$out .= '
    </li>';
			}
        $out .= '
  </ul>';
            break;

     	case "1" : // menu dynamique
       	    $out .= '
  <form name="blog'.$nbox.'" action="" method="post">
    <p class="text-center">';
            for ($i = 0; $i < count($txtbi); $i++) {
				ReadDoc($txtbi[$i][3]);
				$img = getNewUpdateDoc($creadate, $moddate, $serviz[138]);
            	if ($rubr <> strip_tags($txtbi[$i][1])) {
            		if (!empty($rubr)) {
                        $out .= '
      </select>';
                    }
                    $rubr   = strip_tags($txtbi[$i][1]);
                    $maxlen = max(strlen($rubr), 25);
                    $out   .= '
      <select size="1" onchange="document.blog'.$nbox.'.action=this.value; document.blog'.$nbox.'.submit();" class="form-control c-pointer selBlogCat">
        <option>'.$rubr.'</option>';
                }
                $out .= '
        <option value="'.$url.$txtbi[$i][3].$z2.'">&nbsp;&nbsp;'.$marqueur.'&nbsp;'.$img.substr(strip_tags($txtbi[$i][2]), 0, $maxlen).'</option>';
            }
            if (!empty($rubr)) {
                $out .= '
      </select>';
            }
            $out .= '
    </p>
  </form>';
            break;
            
        case "2" : // menu liste dynamique n°1
    		$openimg = 'far fa-plus-square';
    		$closeimg = 'far fa-minus-square';
        case "3" : // menu liste dynamique n°2
          	if ($serviz[59] == "3") {
                $openimg = 'fas fa-chevron-right';
                $closeimg = 'fas fa-chevron-up';
          	}
            $l = 0;
			$isNew = false;
          	for ($i = 0; $i < count($txtbi); $i++) {
				ReadDoc($txtbi[$i][2]);
				$img = getNewUpdateDoc($creadate, $moddate, $serviz[138]);
				if ($img != '') $isNew = true;
           		if ($rubr <> $txtbi[$i][1]) {
           			if (!empty($rubr)) {
                        $out .= '
  </ul>';
                    }
             		$rubr   = $txtbi[$i][1];
             		$rubric = '
    <a href="'.CHEMIN.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[1].'-3-cat-'.$txtbi[$i][1].$z2 
      : $site['BLS'].'.php?lng='.$lng.'&amp;sel=cat&amp;cat='.$txtbi[$i][1].$z2).'">'.$txtbi[$i][1].'
    </a>';
                    $l++;
                    $out .= '
      <p id="titreRubr'.$typeRubrique.$l.'" class="rubr c-pointer" 
        onclick="MontreCacheItems(\'imgOpen'.$typeRubrique.$l.'\',\'imgClose'.$typeRubrique.$l.'\',\'itemsRubr'.$typeRubrique.$l.'\',\'itemsRubrSelect'.$nbox.'\');">
        <i id="imgOpen'.$typeRubrique.$l.'" class="'.$openimg.'" title="'.$web[429].'" style="display: none;"></i>
        <i id="imgClose'.$typeRubrique.$l.'" class="'.$closeimg.'" title="'.$web[57].'"></i>&nbsp;'.@$item['txt'].' '.$rubr.'
      </p>';
    				if ($curr_rubr == $txtbi[$i][1]) {
         			    $out .= '
  <ul id="itemsRubrSelect'.$nbox.'" class="item"> ';
    				}
                    else {
                        $out .= '
  <ul id="itemsRubr'.$typeRubrique.$l.'" class="item">';
    				}
                }
                if ($curr_bi == $txtbi[$i][3]) {
                    $out .= '
    <li class="curr_item">'.$curr_marq.' <a href="'.$url.$txtbi[$i][3].$z2.'">'.$img.$txtbi[$i][2].'</a></li>';
                }
                else {
                    $out .= '
    <li class="item">'.$marqueur.' <a href="'.$url.$txtbi[$i][3].$z2.'">'.$img.$txtbi[$i][2].'</a></li>';
                }
            }
            if (!empty($rubr)) {
                $out .= '
  </ul>';
            }
            if ($userprefs[1] == '' || !$isNew) $out .= '
<script>
var nbRubr = '.($l+1).';
var typeRubr = "'.$typeRubrique.'";
for(var i = 0; i < nbRubr; i++) {
 if((document.getElementById && document.getElementById(\'itemsRubr\'+ typeRubr + i) != null) || (document.all && document.all[\'itemsRubr\'+ typeRubr + i] != undefined ) || (document.layers && document.layers[\'itemsRubr\'+ typeRubr + i] != undefined) ) {
  cache(\'itemsRubr\'+ typeRubr + i);
  montre(\'imgOpen\'+ typeRubr + i,\'inline\');
  cache(\'imgClose\'+ typeRubr + i);
 }
}
</script>';
            break;
        }
        $out  .= '<div class="dispqcBlogCat">'.displayQuickConfig(NULL, false, 'blog', '', '', 39).'</div>';
        $titre = DisplayDownArrow($dynabox[10], 'CATBG'.$nbox.$pos.$tconfig).$web[400];
        return boxthin('BG', $titre, $out, ' CATBG'.$nbox.$pos.$tconfig, $dynabox[10]);
    } else {
        return FALSE;
    }
}

################################################################################

function fbox_blogcom($pos = NULL, $args = NULL) {
    global $lng, $lang, $serviz, $web, $dynabox, $site, $urlrw, $tconfig, $members, $z2;
    global $type, $fileid, $status, $creadate, $moddate, $author, $email,
         $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
         $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
    static $nbox = 0;
    $nbox++;
    if (!isAccessGranted($members[15])) return FALSE;
    if ($serviz[53] != 'on') return FALSE;
    $dbwr   = array();
    $dbreac = ReadDBFields(DBREBLOG);
    foreach ($dbreac as $reac) {
        $dates  = explode('-', $reac[5]);
        $dbwr[] = array($dates[0], $reac[0]); 
    }
    unset($dbreac);
    @rsort($dbwr);
    if (!empty($dbwr) && $serviz[57] != '' && NULL != $pos) {
        $out = '';
        for ($i = 0; $i < min($serviz[61], count($dbwr)); $i++) {
            ReadDoc($dbwr[$i][1]);
			$img = getNewUpdateDoc($creadate, $moddate, $serviz[138]);
            if (isAccessGranted($fieldmod) && isAuthorizedPublication($creadate)) {
                $reac0 = $fieldc1; //($lng == $lang[0])? $fieldc1 : $fieldc2 ;
                $reac1 = formatDate($creadate);
                $reac2 = $fielda2;
                if (!empty($reac0) && ($status == "a")) {
                    $out .=  '
  <div class="text-left itemBlogBoxW">
    '.$reac1.'
    <br />
    <a href="'.CHEMIN.($site['URLR'] == 'on' 
        ? $lng.'-'.$urlrw[1].'-2-pg-'.$tconfig.'-'.$reac2.$z2 
        : $site['BL'].'.php?lng='.$lng.'&amp;sel=pg&amp;tconfig='.$tconfig.'&amp;pg='.$reac2.$z2).'" title="'.$web[297].'">
      '.$img.CutLongWord(strip_tags($reac0), 80).' ...
    </a>
  </div>';
                }
            }
        }
        $titre = DisplayDownArrow($dynabox[12], 'COMBG'.$nbox.$pos.$tconfig).$web[401];
        return boxthin('BG', $titre, $out, ' COMBG'.$nbox.$pos.$tconfig, $dynabox[12]);
    } else {
        return FALSE;
    }
}

################################################################################

function fbox_bloglast($pos = NULL, $args = NULL) {
    global $lng, $lang, $serviz, $web, $dynabox, $site, $urlrw, $tconfig, $members, $z2;
    global $type, $fileid, $status, $creadate, $moddate, $author, $email,
         $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
         $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
    static $nbox = 0;
    $nbox++;
    if (!isAccessGranted($members[15])) return FALSE;
    if ($serviz[53] != 'on') return FALSE;
    $dbw0 = ReadDBFields(DBBLOG);
    $dbw1 = array();
    $j    = ($lng == $lang[0])? 0 : 1 ;
    foreach ($dbw0 as $db) {
        if (isAccessGranted($db[6]) && isAuthorizedPublication($db[5])) {
			  ReadDoc($db[4]);
			if ($fileid != "") 
			{
				$dates  = explode('-', $db[5]);
				$dbw1[] = array($dates[0], $db[4], trim($db[0+$j])); 
			}
        }
    }
    @rsort($dbw1);
    if (!empty($dbw1) && NULL != $pos) {
        unset($dbw0);
        $out = '';
        for ($i = 0; $i < min($serviz[61], count($dbw1)); $i++) {
            $out .=  ' 
  <div class="text-left itemBlogBoxW">';
            if ($dbw1[$i][2] != "") {
                ReadDoc($dbw1[$i][1]);
				$img   = getNewUpdateDoc($creadate, $moddate, $serviz[138]);
				$sujet = ($lng == $lang[0])? $fieldb1 : $fieldb2 ;
				$sujet = $img.CutLongWord(strip_tags($sujet), 60).' ...';
				$out .=  '
				'.formatDate($creadate).'
				<br />
				<a href="'.CHEMIN.($site['URLR'] == 'on' 
					? $lng.'-'.$urlrw[1].'-2-pg-'.$tconfig.'-'.$fileid.$z2 
					: $site['BL'].'.php?lng='.$lng.'&amp;sel=pg&amp;tconfig='.$tconfig.'&amp;pg='.$fileid.$z2).'" title="'.$web[297].'">
				  '.$sujet.'
				</a>';
			}
            $out .=  '
  </div>';
        }
        $titre = DisplayDownArrow($dynabox[11], 'LSTBG'.$nbox.$pos.$tconfig).$web[382];
        return boxthin('BG', $titre, $out, ' LSTBG'.$nbox.$pos.$tconfig, $dynabox[11]);
    } else {
        unset($dbw0);
        return FALSE;
    }
}

################################################################################

function fbox_blogcal($pos = NULL, $args = NULL) {
    global $lng, $lang, $nom, $drtuser, $page, $serviz, $userprefs, $web, $dynabox, $site, $urlrw, 
        $tconfig, $charset, $members;
    static $nbox = 0;
    $nbox++;
    if (!isAccessGranted($members[15])) return FALSE;
    if ($serviz[53] == 'on') {
		$hcal = '190';
        $out = '
<script>
function getElement(aID) {
    return (document.getElementById) ? document.getElementById(aID) : document.all[aID];
}
function getIFrameDocument(aID){ 
    var rv = null; 
    var frame=getElement(aID);
    if (frame.contentDocument)
      rv = frame.contentDocument;
    else // bad Internet Explorer  ;)
      rv = document.frames[aID].document;
    return rv;
  }
    
function adjustMyFrameHeight'.$nbox.$pos.$tconfig.'() {
	var frame = getElement("bcalbox'.$nbox.$pos.$tconfig.'");
	var frameDoc = getIFrameDocument("bcalbox'.$nbox.$pos.$tconfig.'");
	frame.height = frameDoc.body.offsetHeight + 2;
}
</script>
<noscript>';
		$hcal = '340';
		$out .= '
</noscript>
<div class="cal text-center m-auto bg-inherit">
  <iframe 
    class="cal" 
    id="bcalbox'.$nbox.$pos.$tconfig.'" 
    src="'.CHEMIN.'inc/'.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[1].'-500-'.$pos.'-'.$tconfig.'-'.$nbox 
      : 'blogcal.php?lng='.$lng.'&amp;pos='.$pos.'&amp;tconfig='.$tconfig.'&amp;nbox='.$nbox).'" 
    height="'.$hcal.'">
  </iframe>
</div>
';
        $titre  = DisplayDownArrow($dynabox[13], 'CALBG'.$nbox.$pos.$tconfig).$web[395];
        $out   .= displayQuickConfig($pos, false, 'agenda', '', '', 37);
        return '
<div class="'.$pos.$tconfig.'BG'.$nbox.'">'.boxthin($pos, $titre, $out, ' CALBG'.$nbox.$pos.$tconfig, $dynabox[13]).'</div>';
    } else {
        return FALSE;
    }
}

################################################################################

function fbox_blogarch($pos = NULL, $args = NULL) {
    global $lng, $lang, $web, $site, $urlrw, $tconfig, $dynabox, $serviz, $members, $z2;
    static $nbox = 0;
    $nbox++;
    if (!isAccessGranted($members[15])) return FALSE;
    if ($serviz[53] != 'on') return FALSE;
	$archdate = ReadCounter(DBBLOGARCHDATE);
	if ($archdate > 0) {
        $sname = substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '/') + 1, -4);
        $mode  = 1 == preg_match('!blogsarch|blogarch!', $sname) ? 'actif' : '';
		if ($mode == '') {
			$out = '
<div class="text-center">
  '.$web[586].' '.FormatDate($archdate).'<br />'.$web[585].'<br />
  <a href="'.($site['URLR'] =='on' ? $lng.'-'.$urlrw[1].'-7'.$z2 : 'blogsarch.php?lng='.$lng.$z2).'"><b>'.$web[584].'</b></a>
</div>';
		} else {
			$out = '
<div class="text-center">
  '.$web[589].' '.FormatDate($archdate).'<br />'.$web[588].'<br />
  <a href="'.CHEMIN.($site['URLR'] =='on' ? $lng.'-'.$urlrw[1].$z2 : $site['BLS'].'.php?lng='.$lng.$z2).'"><b>'.$web[587].'</b></a>
</div>';
		}
        $titre = DisplayDownArrow($dynabox[18], 'ARCBG'.$nbox.$pos.$tconfig).$web[584];
        return boxthin($pos, $titre, $out, ' ARCBG'.$nbox.$pos.$tconfig, $dynabox[18]);
    } else {
        return FALSE;
    }
}

################################################################################

function fbox_blogrss($pos = NULL, $args = NULL) {
    global $lng, $lang, $drtuser, $serviz, $userprefs, $web, $dynabox, $site, $urlrw, $page, $tconfig, $z2;
    global $type, $fileid, $status, $creadate, $moddate, $author, $email,
         $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
         $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
    static $nbox = 0;
    $nbox++;
    $dbbss = ReadDBFields(DBBSS);
    sort($dbbss);
    if (!empty($dbbss) && $serviz[27] == 'on' && NULL != $pos) {
        $i   = $lng == $lang[0] ? 0 : 1;
        $out = '';
        for ($j = 0; $j < count($dbbss); $j++) {
            Readdoc($dbbss[$j][4]);
            $tconf = empty($fieldweb) ? 0 : ($fieldweb == 'ALL' ? $tconfig : $fieldweb);
            $out  .= '
  <div class="item itemRssBlog text-left">
    <a href="'.CHEMIN.($site['URLR'] =='on' 
      ? $lng.'-'.$urlrw[19].'-3-'.$dbbss[$j][4].'-'.$tconf.$z2 
      : 'blogrss.php?lng='.$lng.'&amp;id='.$dbbss[$j][4].'&amp;tconfig='.$tconf.$z2).'">'.$dbbss[$j][$i].'
    </a>
  </div>';
        }
        $titre = DisplayDownArrow($dynabox[14], 'RSSBG'.$nbox.$pos.$tconfig).$web[402];
        $out  .= '<div class="rssBlog">'.displayQuickConfig(NULL, false, 'bss', '', '', 41).'</div>';
        return boxthin($pos, $titre , $out, ' RSSBG'.$nbox.$pos.$tconfig, $dynabox[14]);
    } else {
        return FALSE;
    }
}

################################################################################

function fbox_blog($pos = NULL, $args = NULL) {
    global $lng, $lang, $drtuser, $page, $site, $serviz, $userprefs, $web, $tconfig, $z2, 
        $id, $prt, $headinc, $pg, $topmess, $widepage, $urlrw, $members;
    global $type, $fileid, $status, $creadate, $moddate, $author, $email,
         $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
         $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
    if (!isAccessGranted($members[15])) return FALSE;
    if ($serviz[53] != 'on') return FALSE;    
    $pg          = !empty($pg) ? $pg : '0';
    $out         = '';
    $max_height  = isset($site[33]) && $site[33] > 0 && empty($prt) ? $site[33] : 0;
    $max_height2 = isset($site[36]) && $site[36] > 0 && empty($prt) ? $site[36] : 0;
    $xdbw        = array();
    $dbwork = ReadDBFields(DBBLOG);
    foreach ($dbwork as $db) {
        if (isAccessGranted($db[6]) && isAuthorizedPublication($db[5])) 
            $xdbw[] = array(TYP_BLOG, $db[5], $db[4], 'a');
    }
    unset($dbwork);
    if (!empty($xdbw)) {
        @rsort($xdbw);
        $minblog = intval($site[31]);
        $maxblog = Min(intval($site[31]), count($xdbw));
        $topmess = $maxblog == 1 ? $web[389] : sprintf($web[3], $maxblog);
        $id      = 1;
		$out    .= '
  <div class="text-center headCenterBlog">';
		if ($serviz[54] == 'on' && empty($prt)) {
			$rssblogurl = $site[3].sprintf(RSSBLOG, $lng);
			$out .= '
    <style>
      .headCenterBlog .overpop { padding-top: 20px; }
      .overpop a i { margin-top: -20px; }
    </style>
    <script>
      $(document).ready(function(){
        $(\'.rssBlog\').popover({
          title:"RSS",
          content:"'.$web[398].' : <br /><b> '.$rssblogurl.'</b>",
          trigger:"hover",
          placement:"auto",
          offset:"0 10 10 10",
          html:true
        });
      });
    </script>
    <div class="overpop pt-0 pl-2">
        <a href="'.CHEMIN.'newsrss.php?lng='.$lng.'&url=blog&tconfig='.$tconfig.$z2.'" class="rssBlog float-left">
          <i class="fas fa-rss"></i>
        </a>
    </div>';                            
        }
		$out .= '
    <div class="divnewline"></div>
  </div>';
        $nbrnvisi = count($xdbw) > $site[31] ? $site[31] : count($xdbw);
        for ($i = $minblog*($id-1); $i < $maxblog; $i++) {
            ReadDoc($xdbw[$i][2]);
            $txt0 = $fileid;
            $img  = getNewUpdateDoc($creadate, $moddate, $serviz[138]);
            if ($lng == $lang[0]) {
                $txt1 = $fieldb1;
                $txt2 = $fieldc1;
            }
            else {
                $txt1 = $fieldb2;
                $txt2 = $fieldc2;
            }
            if (!empty($txt2)) {
                if (!empty($fieldd2)) {
                    $txt1 = '<img src="'.CHEMIN.'img/'.$fieldd2.'" class="'.($serviz[153] == 'on' ? 'float-left' : 'float-right').'" alt="'.$fieldd2.'"/>'.$txt1;
                }
                $out .= '
	    <div class="bord">
		  <div class="quest '.($serviz[153] != 'on' ? 'text-left' : 'text-right').'">
		    <a href="'.$site[3].($site['URLR'] == 'on' 
                ? $lng.'-'.$urlrw[1].'-4-'.$fileid.$z2 
                : $site['BL'].'.php?lng='.$lng.'&amp;pg='.$fileid.$z2).'" title="'.$web[511].'">
              '.$img.'<b>'.$txt1.'</b>
            </a> &nbsp;';
                if ($serviz[114] != 'on') {
                    if ($fieldd1 == 'on' && !empty($email) && !empty($author)) {
                        $out .= '- &nbsp;'.$web[6].displayWriteEmail('blog'.$i, $author, $email);
                    }
                    elseif (!empty($author)) {
                        $out .= '- &nbsp;'.$web[6].'
            <b>'.$author.'</b>';
                    }
                }
                $out .= '
		  </div>';
                $txt3 = '';
                if (!empty($txt2)) {
                    $txt3 .= '
<style>
.text2Blog {
  max-height: '.($max_height > 0 ? $max_height.'px;' : 'auto;').'
  overflow: '.($max_height > 0 ? 'hidden;' : 'visible;').'
  padding: 4px;
  background: transparent;
}
</style>
		  <div id="blog3'.$i.'" class="w-100 rep blog3Blog">
            <div class="rep text2Blog">
              '.$txt2.'
            </div><div class="clearfix"></div>';
                    if ($max_height > 0) $txt3 .= '
            <p class="text-right c-pointer pr-5"> 
		      <a href="javascript:ActiveMenu(\'blog3\',\'blog2\',0,'.$nbrnvisi.','.$i.')" class="box" title="'.$web[296].'">
                '.$web[296].' <i class="far fa-plus-square"></i>
		      </a>
            </p>';
                    $txt3 .= '
		  </div>';
                    if ($max_height > 0) $txt3 .= '
	      <div id="blog2'.$i.'" class="w-100 rep blog2Blog" style="display:none;">'.$txt2.'
		    <p class="text-right c-pointer pr-5">
              <a href="#billet'.$pos.$i.'" title="'.$web[57].'" onclick="cache(\'blog2'.$i.'\');montre(\'blog3'.$i.'\')">
	            '.$web[57].' <i class="far fa-minus-square"></i>
              </a>
	        </p>
		  </div><div class="clearfix"></div>';
                }
                $out .= $txt3;
                if ($prt == 0 && $serviz[104] == 'on') {
                    include CONFIGREP.'sncode.inc';
                    if (!empty($coders))
                        $out .= '
		  <div class="text-left codersBlog">'.$coders.'</div>';
                }
                $out .= '
		  <div class="text-right align-middle footerItemBlog">
		    <form name="formblog'.$i.'" 
                action="'.CHEMIN.($site['URLR'] == 'on' 
                  ? $lng.'-'.$urlrw[1].'-4-'.$fileid.$z2 
                  : $site['BL'].'.php?lng='.$lng.'&amp;pg='.$fileid.$z2).'" 
                method="post">
              <b>'.$web[394].'</b>'.FormatDate($creadate).'&nbsp;';
                if ($serviz[57] != "") {
                    $dbcom = ReadDBFields(DBREBLOG);
                    $nbcom = 0;
                    for ($k = 0; $k < count($dbcom); $k++) {
                        if ($dbcom[$k][1] == $txt0) {
                            ReadDoc($dbcom[$k][0]);
                            if ($status == 'a') {
                                $nbcom++;
                            }
                        }
                    }
                    $fileid = $txt0;
                    if ($nbcom == 0) {
                        $nbcom = $web[430].' '.$web[379];
                    } elseif ($nbcom == 1) {
                        $nbcom = $nbcom.' '.$web[379];
                    } else {
                        $nbcom = $nbcom.' '.$web[379].'s';
                    }
                    if ($serviz[57] != '2')
                        $out .= '
			  <a href="'.CHEMIN.($site['URLR'] == 'on' 
                  ? $lng.'-'.$urlrw[1].'-4-'.$fileid.$z2 
                  : $site['BL'].'.php?lng='.$lng.'&amp;pg='.$fileid.$z2).'" title="'.$nbcom.' - '.$web[297].' '.$web[385].'">
                <span class="commentBlog"> - '.$nbcom.' -  </span>
			    <i class="far fa-comment align-middle commentBlogW" title="'.$nbcom.' - '.$web[297].' '.$web[385].'"></i>
			  </a> &nbsp;';
                }
                $out .= '
			  <input type="hidden" name="cat" value="'.$cat.'"/>
			  <input type="hidden" name="sel" value="'.$sel.'"/>';
                $rank     = ReadDBFields(ADMINS);
                $modadmin = '';
                for ($y = 0; $y < count($rank); $y++) {
                    if ($rank[$y][0] == $userprefs[1] && $rank[$y][2] == 'on') {
                        $modadmin = 'yes';
                        break;
                    }
                }
                if (($author == $userprefs[1] || $modadmin == 'yes' || $serviz[31] == $userprefs[1] || $serviz[176] == $userprefs[1]) && $site[30] != '2') {
                    $urlb = $site['URLR'] == 'on' 
                      ? $lng.'-'.$urlrw[14].'-11-'.TYP_BLOG.'-maj-'.$fileid.$z2 
                      : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_BLOG.'&amp;mod=maj&amp;pg='.$fileid.$z2;
                    $out .= '
              &nbsp;| <a href="'.$urlb.'" title="'.$web[369].'"><i class="far fa-edit"></i></a>&nbsp;';
                }
                if (empty($serviz[76]) && empty($prt)) {
                    $out .= '| 
			  <a href="'.CHEMIN.($site['URLR'] == 'on' 
                  ? $lng.'-'.$urlrw[1].'-41-'.$fileid.'--1' 
                  : $site['BL'].'.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;prt=-1').'" target="_blank">
			    <i class="far fa-eye align-middle" title="'.$web[264].'"></i>
			  </a>&nbsp;
			  <a href="'.CHEMIN.($site['URLR'] == 'on' 
                  ? $lng.'-'.$urlrw[1].'-41-'.$fileid.'-1' 
                  : $site['BL'].'.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;prt=1').'" target="_blank">
		        <i class="fas fa-print align-middle" title="'.$web[22].'"></i>
			  </a>&nbsp;';
                }
                if (empty($prt)) $out .= '
              <a href="#top" title="'.$web[136].'">| <i class="fas fa-arrow-up align-middle"></i></a>';
                $out .= '
		    </form>
          </div>
		</div>';
                if (empty($prt)) $out .= '
        <div class="qcItemBlog">
          '.displayQuickConfig($pos, $xdbw[$i][2], 'blog&amp;form=2&amp;id='.$xdbw[$i][2], 'blog&amp;act=i&amp;id='.$xdbw[$i][2], 'blog&amp;del='.$xdbw[$i][2], 39).'
        </div>';
            }
        }
        if ($page[9] != '') {
            $topmess = '<i class="fas fa-blog float-right pr-2"></i>'.$topmess;
        }
        return boxwide('BG'.$pos, $topmess, $out, ' BG'.$pos.$tconfig);
    }
    else {
        return false;
    }
}
?>