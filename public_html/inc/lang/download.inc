<?php
/*******************************************************************************
 *   Download
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

if (stristr($_SERVER['SCRIPT_NAME'], 'download.inc')) {
    header('location:../index.php');
    die();
}

class GY_download {
    public $tconfig;
    public $topmess;
    protected $pg;
    protected $id;
    protected $dirdnl;
    protected $delay;
    protected $li;
    protected $dn;
    
    public function __construct() {
        global $nom, $menuico, $page, $lang, $lng, $charset;
        $topmess = getLabel($nom[$lng == $lang[0] ? 1 : 11]);
        $this->topmess = $page[9] == '' ? $topmess : '<i class="fas fa-download float-right align-middle"></i>'.$topmess;
        $this->tconfig = $menuico[15] == 'ALL' ? import('tconfig', '', true, 0) : $menuico[15];
        $this->pg      = import('pg');
        $this->id      = import('id');
        $this->dirdnl  = import('dirdnl');
        $this->delay   = import('delay');
        $li            = import('li');
        $this->li      = htmlentities($li, ENT_QUOTES, $charset);
        $this->dn      = import('dn', 'GET');
    }
    
    public function DWNL_ok($pseudo, $lg) {
        global $site, $urlrw, $serviz, $web, $z2, $members;
        $out = '';
        if ($serviz[10] != 'on') {
            $out .= '
    <div class="text-center web342">'.$web[143];
            $href1 = CHEMIN.($site['URLR'] == 'on' ? $lg.'-'.$urlrw[0] : 'index.php?lng='.$lg);
            $out .= '
      <p class="text-center">
        '.SubmitButton($web[135], 'self.location.href=\''.$href1.'\'').'
      </p>
    </div>';
            return array('DWNL', $out);
        }
        elseif (!isAccessGranted($members[7])) {
            $out .= '
	<div class="web342 text-center">'.$web[342];
            if ($members[19] != '0' && $members[19] != '3') {
                $href1 = CHEMIN.($site['URLR'] == 'on' 
                  ? $lng.'-'.$urlrw[3].'-0-'.$this->tconfig.$z2 
                  : 'connect.php?lng='.$lng.'&tconfig='.$this->tconfig.$z2);
                $href2 = CHEMIN.($site['URLR'] == 'on' 
                  ? $lng.'-'.$urlrw[15].'-5-new-'.$this->tconfig.$z2 
                  : $site['USR'].'.php?lng='.$lng.'&uuser=new&tconfig='.$this->tconfig.$z2);
                $out .= '
      <p class="text-center">
        '.SubmitButton($web[630], 'self.location.href=\''.$href1.'\'').
          (empty($pseudo) ? '&nbsp;&nbsp;'.SubmitButton($web[160], 'self.location.href=\''.$href2.'\'') : '').'
      </p>';
            }
            $out .= '
	</div>';
            return array('DWNL', $out);
        }
        elseif (!empty($this->pg)) {
            $dbwork = SelectDbFieldsByField(ReadDBFields(DBDNLOAD), 4, $this->pg);
            $isdoc  = (count($dbwork) != 0);
            if ($isdoc) {
                $isdoc = isAuthorizedPublication($dbwork[0][5]);
                if (!isAccessGranted($dbwork[0][6])) return array('h0', '');
            }
            if (!$isdoc) {
                $dbwork = array();
                if ($site[10] != '') return array('h1', '');
                else return array('h2', '');
            }
        }
        return array('');
    }
    
    public function DWNL_content() {
        global $lang, $lng, $web, $serviz, $urlrw, $site, $reptheme, $prt, $z2;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, 
               $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
               $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        $k      = ($lng == $lang[0])? 0 : 1;
        $categ  = '';
        $dbwork = array();
        $dbwan  = array();
        $dbw50  = array();
        if (!empty($this->pg)) {
            $dbwork = SelectDbFieldsByField(ReadDBFields(DBDNLOAD), 4, $this->pg);
        } else {
            $dbwork2 = ReadDBFields(DBDNLOAD);
            $dbwork1 = array_filter($dbwork2, 'IsNotEmptyTitle');
            unset($dbwork2);
            $dbworkR = array_slice($dbwork1, -intval($serviz[150]));
            $categR  = sprintf($web[651], min(count($dbworkR), $serviz[150]));
            sort($dbworkR);
            $dbwork2 = array();
            $dbwork3 = array();
            $nbdwnld = 0;
            foreach ($dbwork1 as $dbw) {
                $dbwork3[] = array(ReadCounter(DBCOUNT.TestFileID($dbw[4]).DBEXT), $dbw);
            }
            sort($dbwork3);
            $dbwork2 = array_slice($dbwork3, -intval($serviz[151]));
            rsort($dbwork2);
            $dbworkT = array();
            foreach ($dbwork2 as $db) $dbworkT[] = $db[1];
            unset($dbwork2, $dbwork3);
            $categT = sprintf($web[652], min(count($dbworkT), $serviz[151]));
            if (empty($this->dirdnl) || $this->dirdnl == 'no') {
                foreach ($dbwork1 as $db) {
                    if (isAccessGranted($db[6]) && isAuthorizedPublication($db[5])) {
                        $cats  = explode('|', $db[0 + $k]);
                        $catan = count($cats) > 1 ? $cats[0] : $web[649];
                        if (array_search($catan, $dbwan) === false) {
                            $dbwan[] = $catan;
                        }
                    }
                }
                if (count($dbwan) == 1 && $dbwan[0] == $web[649]) {
                    $this->dirdnl = 'no';
                    foreach ($dbwork1 as $db) {
                        if (isAccessGranted($db[6]) && isAuthorizedPublication($db[5])) {
                            $dbwork[] = $db;
                        }
                    }
                }
                sort($dbwan);
                $dbcnt = array();
                foreach ($dbwan as $db) {
                    $nb = 0;
                    foreach ($dbwork1 as $dbw) {
                        if (isAccessGranted($dbw[6]) && isAuthorizedPublication($dbw[5])) {
                            $cats  = explode('|', $dbw[0 + $k]);
                            $catan =  count($cats) > 1 ? $cats[0] : $web[649];
                            if ($db == $catan) $nb++;
                        }
                    }
                    $pos = mb_strpos($db, '-->');
                    $cat = $pos !== false ? mb_substr($db, $pos + 3) : $db;
                    $dbcnt[KeepGoodChars($cat)] = $nb;
                }
                sort($dbwork);
            }
            else {
                $dbwan = array();
                if ($this->dirdnl == 'R') {
                    $dbwork = $dbworkR;
                    $categ  = $categR;
                }
                elseif ($this->dirdnl == 'T') {
                    $dbwork = $dbworkT;
                    $categ  = $categT;
                }
                else {
                    foreach ($dbwork1 as $db) {
                        if (isAccessGranted($db[6]) && isAuthorizedPublication($db[5])) {
                            $cats  = explode('|', $db[0 + $k]);
                            $cat0  = count($cats) > 1 ? $cats[0] : $web[649];
                            $pos   = mb_strpos($cat0, '-->');
                            $cat0  = $pos !== false ? mb_substr($cat0, $pos + 3) : $cat0;
                            $catan = KeepGoodChars(count($cats) > 1 ? $cat0 : $web[649]);
                            if ($this->dirdnl == $catan) {
                                $dbwork[] = $db;
                                $categ    = $cat0;
                            }
                        }
                    }
                    $categ = $dirlnk == KeepGoodChars($web[649]) ? $web[649] : $categ;
                    sort($dbwork);
                }
            }
            unset($dbwork1);
        }
        $out .= '
<div><p class="text-center">'.$web[38].'</p></div>';
        if (empty($this->dirdnl) && empty($this->pg)) {
            if (!empty($dbwan)) {
                if (!empty($serviz[150])) $out .= '
<a href="'.($site['URLR'] == 'on' 
    ? $lng.'-'.$urlrw[11].'-8-R-'.$this->tconfig.$z2 
    : $site['DN'].'.php?lng='.$lng.'&amp;dirdnl=R&amp;tconfig='.$this->tconfig.$z2).'">
  <div class="dncatdwnl">
	<i class="fas fa-download" title="'.$an.'"></i>'.$categR.'
  </div>
</a>';
                if (!empty($serviz[151])) $out .= '
<a href="'.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[11].'-8-T-'.$this->tconfig.$z2 : $site['DN'].'.php?lng='.$lng.'&amp;dirdnl=T&amp;tconfig='.$this->tconfig.$z2).'">
  <div class="dncatdwnl">
	<i class="fas fa-download" title="'.$an.'"></i>'.$categT.'
  </div>
</a>';
                if (!empty($serviz[150]) || !empty($serviz[151])) $out .= '<br />';
                foreach ($dbwan as $an) {
                    $pos = mb_strpos($an, '-->');
                    $an  = $pos !== false ? mb_substr($an, $pos + 3) : $an;
                    $out .= '
<a href="'.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[11].'-8-'.KeepGoodChars($an).'-'.$this->tconfig.$z2 : $site['DN'].'.php?lng='.$lng.'&amp;dirdnl='.KeepGoodChars($an).'&amp;tconfig='.$this->tconfig.$z2).'">
  <div class="dncatdwnl">
	<i class="fas fa-download" title="'.$an.'"></i>'.$an.' ('.$dbcnt[KeepGoodChars($an)].')
  </div>
</a>';
                }
            }
        } else {
            if (empty($this->id)) {
                $this->id = 1;
            }
            if (empty($this->pg) && $this->dirdnl != 'no') {
                if (!empty($categ)) $out .= '
<div class="dntitlecat">'.$categ.'</div>';
                $out .= '
<div class="text-center">
  <form 
    name="retandnl" 
    action="'.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[11].'-'.$this->tconfig.$z2 
      : $site['DN'].'.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'" 
    method="post">
    '.SubmitButton($web[650]).'
  </form>
</div>';
            }
            if (!empty($dbwork)) {
                $typeRubrique = TYP_DNLOAD;
                $imgOpen = 'dl.png';
                $imgClose = 'dl1.png';
                $subImgOpen = 'puce1.png';
                $subImgClose = 'puce2.png';
                $mawmenu = (count($dbwork)-($serviz[4]*($this->id-1))>$serviz[4])? $serviz[4] : count($dbwork)-($serviz[4]*($this->id-1)) ;
                $out .= BeginJavascript().'
var maxsub = '.$mawmenu.';'.EndJavascript();
                $rubr = '';
                $l    = 0;
                if (3 == abs($prt)) {
                    $this->id = 1;
                    $serviz[4] = 99999;
                }
                $isNew = false;
                for ($i = $serviz[4]*($this->id-1); $i < Min($serviz[4]*$this->id, count($dbwork)); $i++) {
                    ReadDoc($dbwork[$i][4]);
                    $img = getNewUpdateDoc($creadate, $moddate, $serviz[143]);
                    if ($img != '') $isNew = true;
                    if ($lng == $lang[0]) {
                        $txt1    = $img.$fieldb1;
                        $txt2    = $fieldc1;
                        $txt3    = $fielda1;
                        $fc      = explode(CONNECTOR, $fieldd1);
                        $file    = $fc[0];
                        $fieldd1 = $file;
                    } else {
                        $txt1    = $img.$fieldb2;
                        $txt2    = $fieldc2;
                        $txt3    = $fielda2;
                        $fc      = explode(CONNECTOR, $fieldd2);
                        $file    = $fc[0];
                        $fieldd2 = $file;
                    }
                    $txt5 = '';
                    $accessDenied = !isAccessGranted($fieldmod) || !isAuthorizedPublication($creadate);
                    $licence = '';
                    if ($fieldweb != '') {
                        $licence = $fieldweb;
                    }
                    elseif (!preg_match('![/.]!', $file)) {
                        $licence = $fieldd2;
                    }
                    if ($licence != '') {
                        $txt5 = $licence;
                    }
                    $txtcount = '';
                    if ($serviz[35] == 'on') {
                        $downcounter  = max(0, ReadDocCounter($dbwork[$i][4]));
                        $txtcount = $web[191].'&nbsp;'.$downcounter.'&nbsp;'.($downcounter <= 1 ? $web[188] : $web[189]);
                    }
                    if (preg_match('!^(http|ftp)!i',$file)) {
                        $size = @filesize($file);
                        if ($size === false) {
                            $txt4 = '???';
                        } else {
                            $txt4 = (($lng == 'fr')? number_format($size/1000, 2, ',', ' ') : number_format($size/1000, 2, '.', ' '));
                        }
                    } else {
                        $txt4 = (($lng == 'fr') 
                          ? number_format(@filesize(CHEMIN.$file)/1000, 2, ',', ' ') 
                          : number_format(@filesize(CHEMIN.$file)/1000, 2, '.', ' '));
                    }
                    if ($accessDenied){
                        $txt4 = '';
                        $txt2 = '
<p class="text-center"><br><br>'.$web[445].'<br /><br /></p>';
                    }
                    if ($rubr != $txt3) {
                        $rubr  = $txt3;
                        $rubrs = explode('|', $rubr);
                        $srubr = count($rubrs) > 1 ? $rubrs[1] : $rubrs[0];
                        if ($l > 0) {
                            $out .= '
</div>';
                        }
                        if (trim($rubr) != "" && $this->dirdnl != 'T') {
                            $l++;
                            $out .= '
<div id="titreRubr'.$typeRubrique.$i.'" class="dnrubr font-weight-bold" 
    onclick="MontreCacheItems(\'imgOpen'.$typeRubrique.$i.'\',\'imgClose'.$typeRubrique.$i.'\',\'itemsRubr'.$typeRubrique.$i.'\',\'itemsRubrSelect\');">
  <i id="imgOpen'.$typeRubrique.$i.'" class="'.$imgOpen.'" title="'.$web[429].'" style="display: none;"></i>
  <i id="imgClose'.$typeRubrique.$i.'" class="'.$imgClose.'" title="'.$web[57].'" style="display: inline;"></i>
  &nbsp;'.$srubr.'
</div>';
                            if (count($dbwork) == 1) {
                                $out .= '
<div id="itemsRubrSelect" class="d-block">';
                            } else {
                                $out .= '
<div id="itemsRubr'.$typeRubrique.$i.'" class="d-block">';
                            }
                        }
                    }
                    $n = (count($dbwork)>$serviz[4])? $i-$serviz[4]*$this->id+$serviz[4] : $i ;
                    if (!$accessDenied) {
                        $out .= '
  <div id="titreSubRubr'.$typeRubrique.$i.'" class="dnrubr2 font-weight-bold" 
      onclick="MontreCacheItems(\'subImgOpen'.$typeRubrique.$i.'\',\'subImgClose'.$typeRubrique.$i.'\',
        \'itemSubRubr'.$typeRubrique.$i.'\',\'itemSubRubrSelect\');">'."\n".'
	<div class="float-left text-right dnrubr3">
      <i id="subImgOpen'.$typeRubrique.$i.'" class="'.$subImgOpen.'" title="'.$web[429].'" style="display: none;"></i>
      <i id="subImgClose'.$typeRubrique.$i.'" class="'.$subImgClose.'" title="'.$web[57].'" style="display: inline;"></i>
    </div>'."\n".'
	<div class="dnsubrubr"><a id="subRubr'.$typeRubrique.$i.'">'.$txt1.'</a></div>'."\n".'
  	</div>'."\n";
                        $out .= '<div class="clearfix"></div>';
                        if (count($dbwork) == 1) {
                            $out .= '
  <div id="itemSubRubrSelect" class="bord2" style="display: block;">';
                        } else {
                           $out .= '
  <div id="itemSubRubr'.$typeRubrique.$i.'" class="bord2" style="display: block;">';
                        }
                        $out .= '
  <div class="row rep dncontent">'.$txt2.'<div class="clearfix"></div><hr />';
                        $out .= '
    <div class="col-md-7 dninfos pb-3">'.basename($file).' ('.$txt4.' '.$web[28].') '.$txtcount.'</div>
    <div class="col-md-5 pb-3">';
                        if ($prt == 0) {
                            if (in_array(strrchr($file, '.'), array('.php', '.inc', '.css', '.htm', '.html', '', '.pdf', '.txt'))) {
                                $out .= '
      <a href="'.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[11].($txt5 == ''? '-2' : '-3').'-0-'.$dbwork[$i][4].($txt5 != '' ? '-'.$txt5 : '') 
          : 'dwnld.php?lng='.$lng.'&amp;delay=0&amp;pg='.$dbwork[$i][4].($txt5 != ''? '&amp;li='.$txt5: '')).'"
        onclick="PopupWindow(\''.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[11].($txt5 == '' ? '-2': '-3').'-0-'.$dbwork[$i][4].($txt5 != '' ? '-'.$txt5: '') 
          : 'dwnld.php?lng='.$lng.'&amp;delay=0&amp;pg='.$dbwork[$i][4].($txt5 != '' ? '&amp;li='.$txt5 : '')).'\',
          \'dnload\',800,600,\'yes\',\'yes\');return false;"
        title="'.$web[362].'" class="dnlink" target="'.TYP_BLANK.'">';
                            } else {
                                $out .= '
      <a href="'.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[11].($txt5 == '' ? '-2' : '-3').'-8-'.$dbwork[$i][4].($txt5 != '' ? '-'.$txt5 : '') 
          : 'dwnld.php?lng='.$lng.'&amp;delay=8&amp;pg='.$dbwork[$i][4].($txt5 != ''? '&amp;li='.$txt5: '')).'"
        onclick="PopupWindow(\''.($site['URLR'] == 'on'? $lng.'-'.$urlrw[11].($txt5 == '' 
          ? '-2': '-3').'-8-'.$dbwork[$i][4].($txt5 != '' ? '-'.$txt5 : '') 
          : 'dwnld.php?lng='.$lng.'&amp;delay=8&amp;pg='.$dbwork[$i][4].($txt5 != '' ? '&amp;li='.$txt5 : '')).'\',
          \'dnload\',540,340,\'no\',\'no\');return false;"
        title="'.$web[362].'" class="dnlink" target="'.TYP_BLANK.'">';
                                }
                            $out .= '
        <i class="fas fa-download" title="'.$web[362].'"></i>
      </a>';
                            $out .= '
      <span class="f-right">';
                            $out .= '
        <a href="'.CHEMIN.($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[11].'-4-'.$dbwork[$i][4].'-'.$this->tconfig.$z2 
            : $site['DN'].'.php?lng='.$lng.'&amp;pg='.$dbwork[$i][4].'&amp;tconfig='.$this->tconfig.$z2).'">
          <i class="fas fa-link" title="'.$web[527].'"></i>
        </a>&nbsp;';
                            if (empty($serviz[77])) {
                                $out .= '
        <a href="'.($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[11].'-5-'.$dbwork[$i][4].'--1' 
            : $site['DN'].'.php?lng='.$lng.'&amp;pg='.$dbwork[$i][4].'&amp;prt=-1').'" target="'.TYP_BLANK.'">
          <i class="far fa-eye" title="'.$web[264].'"></i>
        </a>&nbsp;
        <a href="'.($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[11].'-5-'.$dbwork[$i][4].'-1' 
            : $site['DN'].'.php?lng='.$lng.'&amp;pg='.$dbwork[$i][4].'&amp;prt=1').'" target="'.TYP_BLANK.'">
          <i class="fas fa-print" title="'.$web[22].'"></i>
        </a>&nbsp;';
                            }
                            $out .= '
      </span>';
                            if (empty($prt)) $out .= 
      displayQuickConfig(NULL, $dbwork[$i][4], 'dnload&amp;form=2&amp;id='.$dbwork[$i][4], 
        'dnload&amp;act=i&amp;id='.$dbwork[$i][4], 'dnload&amp;del='.$dbwork[$i][4], 19);
                        }
                        $out .= '
    </div>
  </div><div class="clearfix"></div>';
                        $out .= '
</div>';
                    }
                }
                if ($l > 0) {
                    $out .= '
</div>';
                }
                if (0 == $prt && 'on' != $serviz[78] && !$isNew) {
                    $out .= BeginJavascript().'
var nbRubr = '.$i.';
var typeRubr = "'.$typeRubrique.'";
for(i = 0; i < nbRubr; i++) {
	if((document.getElementById && document.getElementById("itemsRubr"+ typeRubr + i) != null) 
      || (document.all && document.all["itemsRubr"+ typeRubr + i] != undefined ) 
      || (document.layers && document.layers["itemsRubr"+ typeRubr + i] != undefined) ) {
		cache("itemsRubr"+ typeRubr + i);
		montre("imgOpen"+ typeRubr + i,"inline");
		cache("imgClose"+ typeRubr + i);
	}
}'.EndJavascript();
                }
                if (0 == $prt && 'on' != $serviz[78]) {
                    $out .= BeginJavascript().'
var nbRubr = '.$i.';
var typeRubr = "'.$typeRubrique.'";
for(i = 0; i < nbRubr; i++) {
	if((document.getElementById && document.getElementById("itemSubRubr"+ typeRubr + i) != null) 
      || (document.all && document.all["itemSubRubr"+ typeRubr + i] != undefined ) 
      || (document.layers && document.layers["itemSubRubr"+ typeRubr + i] != undefined) ) {
		cache("itemSubRubr"+ typeRubr + i);
		montre("subImgOpen"+ typeRubr + i,"inline");
		cache("subImgClose"+ typeRubr + i);
	}
}'.EndJavascript();
                }
            } elseif (!empty($this->pg)) {
                $out .= '
<p>'.$web[36].'</p>';
            }
            $out .= GetNavBar(($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[11].'-7-'.$this->dirdnl.'-'.$this->tconfig.'-' 
              : $site['DN'].'.php?lng='.$lng.'&amp;dirdnl='.$this->dirdnl.'&amp;tconfig='.$this->tconfig.'&amp;id='), 
              count($dbwork), $this->id, $serviz[4], $z2);
            if (empty($this->pg) && $this->dirdnl != 'no')
                $out .= '
<div class="text-center">
  <form 
    name="retanlnk" 
    action="'.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[11].'-'.$this->tconfig.$z2 
      : $site['DN'].'.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'"
    method="post">
    '.SubmitButton($web[650]).'
  </form>
</div>';
        }
        return array('DWNL', $out);
    }
               
    protected function CalcDownloadTime($file_size, $modem_speed) {
        global $web;
        if ($file_size == $web[37]) {
            $calctime = $web[37];
        }
        else {
            $secondes = round($file_size/$modem_speed/10);
            $minutes  = intval($secondes/60);
            $secondes = $secondes - 60*$minutes;
            $calctime = $minutes.' '.$web[202].' '.$secondes.' '.$web[203];
        }
        return $calctime;
    }
    
    public function DWNL2_ok() {
        global $site, $urlrw, $serviz, $web, $z2, $members;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, 
               $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
               $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;        
        $out = '';
        if ($serviz[10] != 'on') { $out = '
    <div class="text-center web342">'.$web[143].'</div>';
            return array('', 'DWNL2', $out);
        }
        elseif ($this->dn != NULL) { $out = '
    <div class="text-center web342">STOP ! Variable $dn: illegal origine</div>';
            return array('', 'DWNL2', $out);
        }
        elseif (empty($this->pg)) { $out = '
    <div class="text-center web342">'.$web[35].'</div>';
            return array('', 'DWNL2', $out);
        }
        else {
            ReadDoc($this->pg);
            if ($type != "dn") { $out = '
    <div class="text-center web342">STOP ! Variable $pg : illegal value ('.$this->pg.')</div>';
                return array('', 'DWNL2', $out);
            }
            elseif (!isAccessGranted($fieldmod)) { $out = '
    <div class="text-center web342">STOP ! '.$web[445].'</div>';
                return array('', 'DWNL2', $out);
            }
        }
        return array('');
    }
    
    public function DWNL2_content() {
        global $lang, $lng, $web, $serviz, $urlrw, $site, $reptheme, $prt, $z2, $meskin;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, 
               $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
               $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        if (isset($this->dn) && $this->dn != 'yes') unset($this->dn);
        ReadDoc($this->pg);
        if ($lng == $lang[0]) {
            $nomdw = $fieldb1;
            $urls  = explode(CONNECTOR, $fieldd1);
            $urldw = $urls[0];
        }
        else {
            $nomdw = $fieldb2;
            $urls  = explode(CONNECTOR, $fieldd2);
            $urldw = $urls[0];
        }
        $licence     = $this->li;
        $nomfl       = basename($urldw);
        $sizefl      = FileSizeInKb($urldw);
        $sizefl      = $sizefl === FALSE ? $web[37] : $sizefl.' '.$web[28];
        $modfl       = @filemtime($urldw);
        $modfl       = $modfl === FALSE ? $web[37] : FormatDateStamp($modfl);
        $dnldfl      = @filemtime(DBCOUNT.TestFileId($this->pg).DBEXT);
        $dnldfl      = $dnldfl === FALSE ? $web[37] : FormatDateStamp($dnldfl);
        $dnldcounter = UpdateDocCounter($this->pg);
        $txtcount    = $dnldcounter <= 1 ? $dnldcounter.' '.$web[188] : $dnldcounter.' '.$web[189];

        $out = '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>'.$web[192].'</title>
<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'" />';
        if (isset($this->dn) || $licence == "") {
            if ($licence == '') $this->delay = 0;
            $out .= '
<meta http-equiv="refresh" content="'.$this->delay.'; URL='.$urldw.'" />';
        }
        if (file_exists($meskin."style.css")) {
            $out .= '
<link type="text/css" rel="stylesheet" href="'.$meskin.'style.css">
<link type="text/css" rel="stylesheet" href="'.$meskin.'styleplus.css">';
        }
        else {
            $out .= '
<link type="text/css" rel="stylesheet" href="no_skin/style.css">
<link type="text/css" rel="stylesheet" href="no_skin/styleplus.css">';
        }
        if (file_exists($meskin."bs4style.css")) $out .= '
<link type="text/css" rel="stylesheet" href="'.$meskin.'bs4style.css">';
        $out .=  '
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" 
  integrity="sha256-h20CPZ0QyXlBuAw7A+KluUYx/3pK+c7lYEpqLTlxjYQ=" crossorigin="anonymous" />
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" 
  integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"
  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous">
</script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" 
  integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous">
</script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" 
  integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous">
</script>
</head>';
        if (!isset($this->dn) && $licence != '') {
            $url  = IndexDbFieldsByField(ReadDbFields(LICENCES), 0);
            $out .= '
<body class="tbl dwnld1Body">
<div class="bord2 rep maindwnld">
  <fieldset class="dwnld"><legend>'.$web[480].'</legend>
    <div class="divdwnld">'.sprintf($web[481], $licence).'</div>
    <div class="divdwnld">'.$web[482].'</div>';
        if (!empty($url[$licence][2]))
            $out .= '
    <div class="divdwnld font-weight-bold text-center">
      &bull; <a title="'.$web[483].'" target="_blank" href="'.sprintf($url[$licence][2], $lng).'"><u>'.$web[483].'</u></a> &bull;
    </div>';
        $out .= '
    <div class="divdwnld text-left"><br />'.$web[484].'</div>
    <form name="choice" 
      action="'.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[11].'-6-'.$this->pg : 'dwnld.php?lng='.$lng.'&amp;pg='.$this->pg).'" 
      method="post">
      <input type="hidden" name="dn" value="yes" />
      <div class="div1dwnld text-right">
'.SubmitButton($web[414], 'javascript:window.close();').' &nbsp; '.SubmitButton($web[415]).'
      </div>
    </form>
  </fieldset>
</div>';
        }
        else {
            $out .= '
<body class="tbl dwnld1Body" onload="setTimeout(\'self.close();\',10000)">
  <fieldset class="rep dwnld">
    <div class="text-center m-auto;"><b>'.$web[193].'</b></div><hr />
    <div class="d-table text-center divdwnld">
      <div class="d-table-row">
        <div class="d-table-cell text-right">'.$web[218].'&nbsp;</div>
        <div class="d-table-cell text-left"><b>'.$nomdw.'</b></div>
      </div>
      <div class="d-table-row">
        <div class="d-table-cell text-right align-top">'.$web[194].'</div>
        <div class="d-table-cell text-left align-top"><b><a href="'.$urldw.'" target="mainFrame">'.$nomfl.'</a></b></div>
      </div>
      <div class="d-table-row">
        <div class="d-table-cell text-right">'.$web[263].'&nbsp;</div>
        <div class="d-table-cell text-left">'.$modfl.'</div>
      </div>
      <div class="d-table-row">
        <div class="d-table-cell text-right">'.$web[199].'&nbsp;</div>
        <div class="d-table-cell text-left">'.$txtcount.'</div>
      </div>
      <div class="d-table-row">
        <div class="d-table-cell text-right">'.$web[197].'&nbsp;</div>
        <div class="d-table-cell text-left">'.$dnldfl.'</div>
      </div>
      <div class="d-table-row">
        <div class="d-table-cell text-right">'.$web[195].'&nbsp;</div>
        <div class="d-table-cell text-left">'.$sizefl.'</div>
      </div>
      <div class="d-table-row">
        <div class="d-table-cell text-right">'.$web[198].'&nbsp;</div>
        <div class="d-table-cell text-left">'.$this->CalcDownloadTime($sizefl, 512).'</div>
      </div>
      <div class="d-table-row">
        <div class="d-table-cell text-right">'.$web[200].'&nbsp;</div>
        <div class="d-table-cell text-left">'.$this->CalcDownloadTime($sizefl, 56).'</div>
      </div>
      <div class="d-table-row">
        <div class="d-table-cell text-right">'.$web[201].'&nbsp;</div>
        <div class="d-table-cell text-left">'.$this->CalcDownloadTime($sizefl, 33.6).'</div>
      </div>
    </div>
  </fieldset>';
        }
        $out .= '
</body>
</html>';
        return array($out);
    }
}
?>