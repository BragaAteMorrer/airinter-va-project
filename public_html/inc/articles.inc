<?php
/*******************************************************************************
 *   Articles
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
* v6.00.21 (December 17, 2024) : Validation Php 8.4
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'articles.inc')) {
    header('location:../index.php');
    die();
}
#[AllowDynamicProperties]
class GY_articles {
    public $tconfig;
    public $topmess;
    public $userprefs3;
    public $widepage;
    public $countit;
    public $txtart2;
    public $img;
    public $urltitre;
    protected $mnuid;
    protected $pg;
    protected $id;
    protected $txtart102;
    
    public function __construct() {
        global $templates, $widepage;
        $tconfig        = import('tconfig', '', true, 0);
        $this->tconfig  = !isset($templates[$tconfig]) ? 0 : $tconfig;
        $this->widepage = $widepage;
        $this->mnuid    = import('mnuid', '', true, 0);
        $this->pg       = import('pg');
        $this->id       = import('id');
    }

    protected function GetOptionCateg($mnu, $id) {
        global $lang, $fielda1, $fielda2, $fieldd1, $fieldd2, $lng;
        if (substr($mnu, 0, 3) == 'MNU') {
            $mnu_id = substr($mnu, 4);
            $dbw    = SelectDbFieldsByField(ReadDBFields(DBOPTMENU), 5, $mnu_id);
            foreach ($dbw as $item) {
                ReadDoc($item[4]);
                $urls = explode('||', $lng == $lang[0] ? $fieldd1 : $fieldd2);
                $pg   = substr($urls[0], strpos($urls[0], 'pg=') + 3);
                $pos  = strpos($pg, '&');
                $pg   = $pos === false ? $pg : substr($pg, 0, $pos);
                if ($pg == $id) return ($lng == $lang[0] ? $fielda1 : $fielda2);
            }
        }
        return '';
    }

    protected function GetOptionBoxes($mnu, $id) {
        global $lang, $fieldc2, $fieldd1, $fieldd2, $lng;
        if (substr($mnu, 0, 3) == 'MNU') {
            $mnu_id = substr($mnu, 4);
            $dbw    = SelectDbFieldsByField(ReadDBFields(DBOPTMENU), 5, $mnu_id);
            foreach ($dbw as $item) {
                ReadDoc($item[4]);
                $urls = explode('||', $lng == $lang[0] ? $fieldd1 : $fieldd2);
                $pg   = substr($urls[0], strpos($urls[0], 'pg=') + 3);
                $pos  = strpos($pg, '&');
                $pg   = $pos === false ? $pg : substr($pg, 0, $pos);
                if ($pg == $id) return $fieldc2;
            }
        }
        return '';
    }

    protected function ArtNavbar($mnu, $cat, $titre) {
        global $lang, $web, $site, $urlrw, $xposbox, $z2, $lng, 
               $fieldb1, $fieldb2, $fieldc1, $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod, $creadate;
        $imgPrev = '<i class="fas fa-caret-left align-middle mr-2" title="'.$web[32].'"></i>';
        $imgNext = '<i class="fas fa-caret-right align-middle ml-2" title="'.$web[34].'"></i>';
        $nav     = $navp = $navn = $navs = $categ = '';
        $cat     = stripslashes($cat);
        $titre   = stripslashes($titre);
        if (substr($mnu, 0, 3) == 'MNU') {
            $mnu_id = substr($mnu, 4);
            $dbw    = SelectDbFieldsByField(ReadDBFields(DBOPTMENU), 5, $mnu_id);
            $dbart  = array();
            $cmd    = $site['URLR'] == 'on' ? $lng.'-'.$urlrw[20].'-103' : 'articles.php';
            foreach ($dbw as $item) {
                ReadDoc($item[4]);
                if (!isAuthorizedPublication($creadate) || !isAccessGranted($fieldmod)) continue;
                if (strtolower($fieldc1) != '_self' || $fieldweb != $mnu_id) continue;
                $urls1 = explode('||', $fieldd1);
                $urls2 = explode('||', $fieldd2);
                $url1  = $site['URLR'] == 'on' ? (empty($urls1[1]) ? '' : substr($urls1[1], 0, 7 + strlen($urlrw[20]))) : substr($urls1[0], 0, 12);
                $url2  = $site['URLR'] == 'on' ? (empty($urls2[1]) ? '' : substr($urls2[1], 0, 7 + strlen($urlrw[20]))) : substr($urls2[0], 0, 12);
                $url   = $lng == $lang[0] ? ($url1 == $cmd) : ($url2 == $cmd);
                $pg    = substr($urls1[0], strpos($urls1[0], 'pg=') + 3);
                $pos   = strpos($pg, '&');
                $pg    = $pos === false ? $pg : substr($pg, 0, $pos);
                // par défaut le titre de l'article est celui de l'option de menu
                $item[6] = $item[2];
                $item[7] = $item[3];
                if ($pg) { // récupère le titre de l'article pour la comparaison
                    ReadDoc($pg);
                    $item[6] = $fieldb1;
                    $item[7] = $fieldb2;
                }
                if ($url) $dbart[] = $item;
                unset($urls1, $urls2);
            }
            sort($dbart);
            if (count($dbart) > 1) {
                $navs = '
                <form name="selnavart" action="#" method="post" class="d-inline align-middle">
                  <select onchange="document.selnavart.action=this.value; document.selnavart.submit();" class="form-control selArtNavbar" title="">';
            }
            for ($i = 0; $i < count($dbart); $i++) {
                $sel      = '';
                $opttitre = stripslashes($dbart[$i][$lng == $lang[0] ? 2 : 3]);
                $arttitre = stripslashes($dbart[$i][$lng == $lang[0] ? 6 : 7]);
                if ($navs != '') {
                    ReadDoc($dbart[$i][4]);
                    $urls  = explode('||', $lng == $lang[0] ? $fieldd1 : $fieldd2);
                    $pg    = substr($urls[0], strpos($urls[0], 'pg=') + 3);
                    $pos   = strpos($pg, '&');
                    $pg    = $pos === false ? $pg : substr($pg, 0, $pos);
                    $ancre = '';
                    $pos1  = strpos($urls[0], '#');
                    $ancre = empty($ancre) ? $z2 : $ancre;
                    if ($pos1 !== false) $ancre = substr($urls[0], $pos1);
                    $atconf  = explode('|', $fieldmail);
                    $sconfig = ($atconf[0] == 'ALL' ? '' : '&amp;tconfig='.$atconf[0]);
                    $iconf   = ($atconf[0] == 'ALL' ? '1031' : '103');
                    $sconf   = ($atconf[0] == 'ALL' ? '' : '-'.$atconf[0]);
                    $urls[0] = 'articles.php?lng='.$lng.'&amp;pg='.$pg.'&amp;mnuid='.$item[5].$sconfig.$ancre;
                    $urls[1] = $lng.'-'.$urlrw[20].'-'.$iconf.'-'.$pg.'-'.$item[5].$sconf.$ancre;
                    $url = empty($urls[1]) ? $urls[0] : ($site['URLR'] != 'on' ? $urls[0] : $urls[1]);
                    unset($urls);
                    // indication des catégories et sous-catégories
                    $curcat = str_replace('|', '-', strip_tags($dbart[$i][$lng == $lang[0] ? 0 : 1]));
                    if ($categ != $curcat) {
                        if ($categ != '' && $i > 0) $navs .= '
                    </optgroup>';
                        $categ = $curcat;
                        $navs .= '
                    <optgroup label="'.$categ.'">';
                    }
                    $navs .= '
                    <option value="'.$url.'"'.Selected($titre == $arttitre).'>'.strip_tags($opttitre).'</option>';
                }
                if ($titre == $arttitre) {
                    if ($i > 0) { // précédent
                        ReadDoc($dbart[$i - 1][4]);
                        $urls  = explode('||', $lng == $lang[0] ? $fieldd1 : $fieldd2);
                        $pg    = substr($urls[0], strpos($urls[0], 'pg=') + 3);
                        $pos   = strpos($pg, '&');
                        $pg    = $pos === false ? $pg : substr($pg, 0, $pos);
                        $ancre = '';
                        $pos1  = strpos($urls[0], '#');
                        if ($pos1 !== false) $ancre = substr($urls[0], $pos1);
                        $ancre   = empty($ancre) ? $z2 : $ancre;
                        $atconf  = explode('|', $fieldmail);
                        $sconfig = ($atconf[0] == 'ALL' ? '' : '&amp;tconfig='.$atconf[0]);
                        $iconf   = ($atconf[0] == 'ALL' ? '1031' : '103');
                        $sconf   = ($atconf[0] == 'ALL' ? '' : '-'.$atconf[0]);
                        $urls[0] = 'articles.php?lng='.$lng.'&amp;pg='.$pg.'&amp;mnuid='.$item[5].$sconfig.$ancre;
                        $urls[1] = $lng.'-'.$urlrw[20].'-'.$iconf.'-'.$pg.'-'.$item[5].$sconf.$ancre;
                        $url = empty($urls[1]) ? $urls[0] : ($site['URLR'] != 'on' ? $urls[0] : $urls[1]);
                        unset($urls);
                        $navp = '<a href="'.$url.'" class="artNavP">'.$imgPrev.'</a>&nbsp;&nbsp;';
                    }
                    if ($i < count($dbart) - 1) { // suivant
                        ReadDoc($dbart[$i + 1][4]);
                        $urls  = explode('||', $lng == $lang[0] ? $fieldd1 : $fieldd2);
                        $pg    = substr($urls[0], strpos($urls[0], 'pg=') + 3);
                        $pos   = strpos($pg, '&');
                        $pg    = $pos === false ? $pg : substr($pg, 0, $pos);
                        $ancre = '';
                        $pos1  = strpos($urls[0], '#');
                        if ($pos1 !== false) $ancre = substr($urls[0], $pos1);
                        $ancre   = empty($ancre) ? $z2 : $ancre;
                        $atconf  = explode('|', $fieldmail);
                        $sconfig = ($atconf[0] == 'ALL' ? '' : '&amp;tconfig='.$atconf[0]);
                        $iconf   = ($atconf[0] == 'ALL' ? '1031' : '103');
                        $sconf   = ($atconf[0] == 'ALL' ? '' : '-'.$atconf[0]);
                        $urls[0] = 'articles.php?lng='.$lng.'&amp;pg='.$pg.'&amp;mnuid='.$item[5].$sconfig.$ancre;
                        $urls[1] = $lng.'-'.$urlrw[20].'-'.$iconf.'-'.$pg.'-'.$item[5].$sconf.$ancre;
                        $url = empty($urls[1]) ? $urls[0] : ($site['URLR'] != 'on' ? $urls[0] : $urls[1]);
                        unset($urls);
                        $navn = '&nbsp;&nbsp;<a href="'.$url.'" class="artNavS">'.$imgNext.'</a>';
                    }
                }
            }
            if ($navs != '') {
                if ($categ != '') $navs .= '
                    </optgroup>';
                $navs .= '
                  </select>
                </form>';
            }
            $nav = '
              <div class="m-auto text-center">
                <div class="d-inline-block">'.$navp.'</div>
                <div class="d-inline-block">'.$navs.'</div>
                <div class="d-inline-block">'.$navn.'</div>
              </div>';
        }
        return $nav;
    }
    
    public function ART_content() {
        global $members, $z2, $site, $urlrw, $serviz, $web, $lang, $lng, $prt, $userprefs, 
               $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, 
               $fieldweb, $fieldmail, $fieldmod, $creadate, $moddate;
        $isdoc = count(SelectDBFields(TYP_ART, 'a', $this->pg)) == 1;
		$txtart101 = 0;
		$txtart102 = 'off';
        if ($isdoc) {
            ReadDoc($this->pg);
            $isdoc = isAuthorizedPublication($creadate);
            if ($isdoc) {
                $groupeprive     = $fieldmod;
                $txtart10        = explode('|', $fieldmail);
                $txtart10        = explode('|', $fieldmail);
                $txtart101       = isset($txtart10[1]) ? $txtart10[1] : 0;
                $txtart102       = isset($txtart10[2]) ? $txtart10[2] : 'off';
                $txtart1         = $lng == $lang[0] ? $fieldb1 : $fieldb2;
                $this->txtart2   = $lng == $lang[0] ? $fieldc1 : $fieldc2;
                $txtart3         = str_replace('|', '-', $lng == $lang[0] ? $fielda1 : $fielda2);
                $txtart4         = FormatDate($moddate);
                $txtart5         = FormatDate($creadate);
                $screadate       = $creadate;
                $smoddate        = $moddate;
                $txtart6         = $fieldd2;
                $txtart8         = $this->mnuid > 0 ? 'MNU '.$this->mnuid : $fieldd1;
                $categ           = $this->GetOptionCateg($txtart8, $this->pg);
                $boxes           = $this->GetOptionBoxes($txtart8, $this->pg);
                $txtart3         = $categ == '' ? $txtart3 : str_replace('|', '-', $categ);
                $txtart9         = $boxes == '' ? $fieldweb : $boxes;
                $this->img       = getNewUpdateDoc($screadate, $smoddate, $serviz[136]);
                $menus           = SelectDBFieldsByField(ReadDBFields(DBMENUBOX), 4, $this->mnuid);
                $txtart11        = $lng == $lang[0] ? (!isset($menus[0][2]) ? '' : $menus[0][2]) : (!isset($menus[0][3]) ? '' : $menus[0][3]);
                $txtart11        = mb_substr($txtart11, (mb_substr($txtart11, 0, 1) == '§' ? 1 : 0));
                $this->txtart102 = $txtart102;
                $this->countit   = true;
            }
        }
        if (!$isdoc) {
            $txtart1         = $web[35];
            $this->txtart2   = $web[36];
            $txtart3         = '';
            $txtart4         = $web[37];
            $txtart5         = $web[37];
            $screadate       = $web[37];
            $smoddate        = $web[37];
            $txtart6         = '';
            $txtart8         = '';
            $txtart9         = '';
            $txtart10        = array(0, 0, 'off');
            $txtart11        = '';
            $groupeprive     = '';
            $this->img       = '';
            $this->txtart102 = 'off';
            $this->countit   = false;
        }
        $this->topmess  = (!empty($txtart6) && is_file(CHEMIN.'img/'.$txtart6) 
          ? '<img src="'.CHEMIN.'img/'.$txtart6.'" class="float-right" alt="" />' 
          : '<i class="fas fa-newspaper float-right"></i>').$txtart1;
        $this->urltitre = $site['URLR'] == 'on'
            ? $lng.'-'.$urlrw[20].'-102-'.$this->pg.'-'.$this->tconfig 
            : 'articles.php?lng='.$lng.'&amp;pg='.$this->pg.'&amp;tconfig='.$this->tconfig;
        switch ($txtart9) {
        case 'W' : 
            $this->userprefs3 = '';
            $this->widepage   = 'on';
            break;
        case 'L' : 
            $this->userprefs3 = 'L';
            break;
        case 'R' : 
            $this->userprefs3 = 'R';
            break;
        default : 
            $this->userprefs3 = '';
        }
        $href1 = CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[3].'-0-'.$this->tconfig.$z2 
          : 'connect.php?lng='.$lng.'&tconfig='.$this->tconfig.$z2);
        $href2 = CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[15].'-5-new-'.$this->tconfig.$z2 
          : $site['USR'].'.php?lng='.$lng.'&uuser=new&tconfig='.$this->tconfig.$z2);
        if (!isAccessGranted($members[1])) {
            $out = '
    <div class="text-center web342">'.$web[342];
            if ($members[19] != '0' && $members[19] != '3') {
                $out .= '
      <p class="text-center">
        '.SubmitButton($web[630], 'self.location.href=\''.$href1.'\'').
          (empty($userprefs[1]) ? '&nbsp;&nbsp;'.SubmitButton($web[160], 'self.location.href=\''.$href2.'\'') : '').'
      </p>';
            }
            $out .= '
    </div>
';
            $this->txtart102 = 'off';
            $this->txtart2   = $out;
            return true;
        } else {
            if (!isAccessGranted($groupeprive)) {
                $out = '
    <div class="text-center web342">'.$web[444];
            if ($members[19] != '0' && $members[19] != '3') {
                $out .= '
      <p class="text-center">
        '.SubmitButton($web[630], 'self.location.href=\''.$href1.'\'').
          (empty($userprefs[1]) ? '&nbsp;&nbsp;'.SubmitButton($web[160], 'self.location.href=\''.$href2.'\'') : '').'
      </p>';
            }
            $out .= '
    </div>
';
                $this->txtart102 = 'off';
                $this->txtart2   = $out;
                return true;
            } else {
                $out = '';        
                if ($prt != -1 && $prt != 1) {
                    if ($serviz[60] == 'on') {
                        $rssartsurl = $site[3].($site['URLR'] == 'on'? DATAREP.$lng.'-'.$urlrw[19].'-28': sprintf(RSSART, $lng));
                        if (empty($prt)) $out .= '
    <style>
      .overpop { padding-top: 20px; }
    </style>
    <script>
      $(document).ready(function(){
        $(\'.rssArt\').popover({
          title:"RSS",
          content:"'.$web[406].' : <br /><b> '.$rssartsurl.'</b>",
          trigger:"hover",
          placement:"auto",
          offset:"0 10 10 10",
          html:true
        });
      });
    </script>
    <div class="overpop pt-1 pl-2 pb-5">
      <a href="'.CHEMIN.'newsrss.php?lng='.$lng.'&url=arts&tconfig='.$this->tconfig.$z2.'" class="rssArt float-left">
        <i class="fas fa-rss"></i>
      </a>
    </div>';                            
                    }
                }
                if (!empty($this->txtart2)) {
                    $max_height = $txtart101 > 0 && empty($prt) ? $txtart101 : 0;
                    $txt3 = '
    <a id="art'.$this->pg.'"></a>
    <style>
    .contentArt2 {
      height: '.($max_height > 0 ? $max_height.'px;' : 'auto;').'
      overflow: '.($max_height > 0 ? 'hidden;' : 'visible;').'
    }
    </style>
    <div id="texte30" class="pt-4 contentArt">
      <div class="contentArt2">
        '.$this->txtart2.'
      </div>
      <div class="clearfix"></div>';
                    if ($max_height > 0) $txt3 .= '
      <p class="text-right">
        <a href="javascript:ActiveMenu(\'texte3\',\'texte2\',0,1,0)" class="box" title="'.$web[296].'">
          '.$web[296].' <i class="far fa-plus-square align-middle"></i>
        </a>
      </p>';
                    $txt3 .= '
    </div>' ;
                    if ($max_height > 0) $txt3 .= '
    <div id="texte20" style="display:none;">'.$this->txtart2.'
      <p class="text-right c-pointer">
        <a href="#art'.$this->pg.'" title="'.$web[57].'" onclick="cache(\'texte20\');montre(\'texte30\')">
          '.$web[57].' <i class="far fa-minus-square align-middle"></i>
        </a>
      </p>
    </div><div class="clearfix"></div>';
                    $out .= $txt3;            
                    if ($serviz[113] != 'on') {
                        $keywords = GetKeywords($this->pg);
                        if (trim($keywords) != '') {
                            $out .= '
    <fieldset>'.legend($web[641]).$keywords.'</fieldset>';
                        }
                    }
                    $out .= '
    <hr />';
                    if ($prt == 0 && $serviz[105] == 'on') {
                        include CONFIGREP.'sncode.inc';
                        if (!empty($coders))
                            $out .= '
    <div class="row w-100">
      <div class="col-md-12 codersArt">'.$coders.'</div>
    </div>';
                    }
                    $artcounter = $serviz[33] == 'on' ? UpdateDocCounter($this->pg) : 0;
                    $svz = 0;
                    if ($this->countit) {
                            $out .= '
    <div class="row w-100">';
                        if ($serviz[64] == '' || $serviz[65] == '' || $serviz[66] == '' || $serviz[67] == '') {
                            $svz++;
                            $out .= '
      <div class="col-md-6 pl-5 propArtW">';
                            if ($serviz[64] == '') {
                                $out .= '
        <div>'.$web[95].' <b>'.$txtart5.'</b></div>';
                            }
                            $datespub = explode('-', $screadate);
                            if ($serviz[65] == '' && $smoddate > $datespub[0]) {
                                $out .= '
        <div>'.$web[20].' <b>'.$txtart4.'</b></div>';
                            }
                            if ($serviz[66] == '') {
                                $out .= '
        <div>'.$web[21].' <b>'.$txtart11.' - '.$txtart3.'</b></div>';
                            }
                            if ($serviz[67] == '' && $serviz[33] == 'on') {
                                $txtcount = $artcounter < 2 ? $web[188] : $web[189];
                                $out .= '
        <div>'.$web[190].' <b>'.$artcounter.' '.$txtcount.'</b></div>';
                            }
                            $out .= '
      </div>';
                        }
                        else $out .= '
      <div class="col-md-6">&nbsp;</div>';
                        if ($prt == 0 && ($serviz[102] == '' || $serviz[68] == '')) {
                            $svz++;
                            $out .= '
      <div class="col-md-6">';
                            if ($serviz[102] == '') {
                                $navbar = $this->ArtNavbar($txtart8, $txtart3, $txtart1);
                                if ($navbar != '') {
                                    $out .= '
        <div class="row">
          <div class="col-md-12 text-center navbarArt">'.$navbar.'</div>
        </div>';
                                }
                            }
                            if ($serviz[68] == '') {
                                $out .= '
        <div class="row">
	      <div class="col-md-12 text-center prtArt">
            <a href="'.($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[20].'-1001-'.$this->pg.'--1' 
              : 'articles.php?lng='.$lng.'&amp;pg='.$this->pg.'&amp;prt=-1').'" target="_blank">
              <i class="fas fa-eye" title="'.$web[264].'"></i>&nbsp;'.$web[264].'
            </a>&nbsp;
            <a href="'.($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[20].'-1001-'.$this->pg.'-1' 
              : 'articles.php?lng='.$lng.'&amp;pg='.$this->pg.'&amp;prt=1').'" target="_blank">
              <i class="fas fa-print" title="'.$web[22].'"></i>&nbsp;'.$web[22].'
            </a>
          </div>
        </div>';				
                            }
                            $out .= '
      </div>';
                        }
                        else $out .= '
      <div class="col-md-6">&nbsp;</div>';
                    }
                    $out .= '
    </div>
    <div class="clearfix"></div>';
                    if ($this->countit && $svz > 0)
                        $out .='
    <hr />';
                    if (empty($prt)) $out .= displayQuickConfig(NULL, $this->pg, 'art&amp;form=2&amp;id='.$this->pg, 
                                                                'art&amp;act=i&amp;id='.$this->pg, 'art&amp;del='.$this->pg, 15);
                } else {
                    $this->countit = false;
                }
                if ($serviz[29] != '2' && $this->txtart102 == 'on' && $this->countit && $prt == 0) {
                    // $out .= '
    // <div class="divnewline"></div>';
                    $this->txtart102 = 'on';
                    $this->txtart2   = $out;
                }
                else {
                    $this->txtart102 = 'off';
                    $this->txtart2   = $out;
                }
                return true;
            }
        }
    }
    
    public function ART_react() {
        global $serviz, $site, $page, $urlrw, $web, $prt, $z2, $lng, 
               $author, $email, $status, $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2, 
               $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod, $creadate, $moddate;
        if ($serviz[29] == '2' || $this->countit === false || $this->txtart102 != 'on') return NULL;
        $out = '
    <a id="reactart"></a>';
        $boxtopmess = $page[9] == '' ? $web[184] : '<i class="far fa-comments float-right"></i>'.$web[184];
        $react      = import('react');
        $dbwork     = array();
        if (!empty($react))
            $dbwork[] = $react;
        elseif (!empty($this->pg)) {
            $dbreaction = ReadDBFields(DBREACT);
            for ($i = 0; $i < count($dbreaction); $i++) {
                if ($dbreaction[$i][1] == $this->pg && isAccessGranted($dbreaction[$i][6])) {
                    $dbwork[] = $dbreaction[$i][0];
                }
            }
        }
        @rsort($dbwork);
        $out .= '
    <form 
      name="react" 
      action="'.($site['URLR'] == 'on' 
        ? $lng.'-'.$urlrw[14].'-4-'.TYP_REACT.'-'.$this->pg.$z2 
        : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_REACT.'&amp;pg='.$this->pg.$z2).'" 
      method="post">
      <input type="hidden" name="tconfig" value="'.$this->tconfig.'">
      <div class="text-center pb-3">'.SubmitButton($web[181]).'</div>
    </form>';
        if (empty($this->id)) $this->id = 1;
        if (!empty($dbwork)) {
            if (abs($prt) == 3) {
                $this->id = 1;
                $serviz[30] = 99999;
            }
            $nbreact = count($dbwork);
            for ($i = $serviz[30]*($this->id-1); $i < Min($serviz[30]*$this->id, $nbreact); $i++) {
                ReadDoc($dbwork[$i]);
                if ($status == 'a') {
                    $out .= '
    <div class="table-responsive-md">
    <table class="w-100 headreact">
      <tr>
        <td class="forum w-15 align-top headreactleft"><p>'.$web[185].$fielda1.'&nbsp;</p></div>
        <td class="quest align-middle headreactright">'.$web[6].' ';
                    if ($fieldd1 == 'on' && empty($prt)) $out .= displayWriteEmail('react'.$i, $author, $email);
                    else $out .= '<b>'.$author.'</b> ';
                    $out .= '
          '.$web[7].' '.FormatDate($creadate).'
        </td>
      </tr>
    </table>
    </div>
    <div class="table-responsive-md">
    <table class="w-100 bodyreact">
      <tr><td class="rep">'.$fieldc1.'</td></tr>
    </table>
    </div>
    <div class="table-responsive-md">
    <table class="w-100">
      <tr>
        <td>';
                    if (empty($prt)) $out .= '
          '.displayQuickConfig(NULL, $dbwork[$i], 'react&amp;form=2&amp;id='.$dbwork[$i], 'react&amp;act=i&amp;id='.$dbwork[$i], 'react&amp;del='.$dbwork[$i], 16);
                        $out .= '
        </td>
      </tr>
    </table>
    </div>
    <br />';
                }
            }
        } else {
            $out .= '
    <div class="table-responsive-md">
    <table class="text-center border-0 m-auto">
      <tr>
        <td><p class="text-center">'.$web[186].'</p></div>
      </td>
    </table>
    </div>';
        }
        $out .= 
    GetNavBar(($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[20].'-1003-'.$this->pg.'-'.$this->tconfig.'-' 
      : 'articles.php?lng='.$lng.'&amp;pg='.$this->pg.'&amp;tconfig='.$this->tconfig.'&amp;id='), count($dbwork), $this->id, $serviz[30], '#reactart');
        return array($boxtopmess, $out);
    }
}
?>
