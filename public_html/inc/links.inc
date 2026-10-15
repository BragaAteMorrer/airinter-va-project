<?php
/*******************************************************************************
 *   Links
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
 * v6.00.21 (December 17, 2024) : Validation Php 8.4
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'links.inc')) {
    header('location:../index.php');
    die();
}
#[AllowDynamicProperties]
class GY_links {
    public $tconfig;
    public $topmess;
    protected $pg;
    protected $id;
    protected $dirlnk;
    
    public function __construct() {
        global $nom, $menuico, $page, $lang, $lng, $charset;
        $topmess = getLabel($nom[$lng == $lang[0] ? 3 : 13]);
        $this->topmess = $page[9] == '' ? $topmess : '<i class="fas fa-external-link-alt float-right align-middle"></i></i>'.$topmess;
        $this->tconfig = $menuico[16] == 'ALL' ? import('tconfig', '', true, 0) : $menuico[16];
        $this->pg      = import('pg');
        $this->id      = import('id');
        $this->dirlnk  = import('dirlnk');
    }
    
    public function LNK_ok($pseudo, $lng) {
        global $site, $urlrw, $serviz, $web, $z2, $members;
        $out = '';
        if ($serviz[11] != 'on') {
            $out = '
    <div class="text-center web342">'.$web[143];
            $href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng);
            $out .= '
      <p class="text-center">
        '.SubmitButton($web[135], 'self.location.href=\''.$href1.'\'').'
      </p>
    </div>';
            return array('LNK', $out);
        }
        elseif (!isAccessGranted($members[14])) {
            $out = '
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
            return array('LNK', $out);
        }
        elseif (!empty($this->pg)) {
            $dbwork = SelectDbFieldsByField(ReadDBFields(DBLINKS), 4, $this->pg);
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
    
    public function LNK_content() {
        global $lang, $lng, $web, $serviz, $urlrw, $site, $prt, $z2;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, 
               $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
               $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        $k      = ($lng == $lang[0]) ? 0 : 1;
        $categ  = '';
        $dbwork = array();
        if (!empty($this->pg)) {
            $dbwork = SelectDbFieldsByField(ReadDBFields(DBLINKS), 4, $this->pg);
            $dbwan  = array();
        }
        else {
            $dbwan   = array();
            $dbwork2 = ReadDBFields(DBLINKS);
            $dbwork1 = array_filter($dbwork2, 'IsNotEmptyTitle');
            unset($dbwork2);
            if (empty($this->dirlnk) || $this->dirlnk == 'no') {
                foreach ($dbwork1 as $db) {
                    if (isAccessGranted($db[6]) && isAuthorizedPublication($db[5])) {
                        $cats  = explode('|', $db[0 + $k]);
                        $catan =  count($cats) > 1 ? $cats[0] : $web[649];
                        if (array_search($catan, $dbwan) === false) $dbwan[] = $catan;
                    }
                }
                if (count($dbwan) == 1 && $dbwan[0] == $web[649]) {
                    $this->dirlnk = 'no';
                    foreach ($dbwork1 as $db) 
                        if (isAccessGranted($db[6]) && isAuthorizedPublication($db[5])) $dbwork[] = $db;
                    $dbwan = array();
                }
                sort($dbwan);
            }
            else {
                $dbwan = array();
                foreach ($dbwork1 as $db) {
                    if (isAccessGranted($db[6]) && isAuthorizedPublication($db[5])) {
                        $cats  = explode('|', $db[0 + $k]);
                        $cat0  = count($cats) > 1 ? $cats[0] : $web[649];
                        $pos   = mb_strpos($cat0, '-->');
                        $cat0  = $pos !== false ? mb_substr($cat0, $pos + 3) : $cat0;
                        $catan = KeepGoodChars(count($cats) > 1 ? $cat0 : $web[649]);
                        if ($this->dirlnk == $catan) {
                            $dbwork[] = $db;
                            $categ    = $cat0;
                        }
                    }
                }
                $categ = $this->dirlnk == KeepGoodChars($web[649]) ? $web[649] : $categ;
            }
            unset($dbwork1);
        }
        sort($dbwork);
        $out = '
<div><p class="text-center">'.$web[38].'</p></div>';
        if (empty($this->dirlnk) && empty($this->pg) && !empty($dbwan)) {
            foreach ($dbwan as $an) {
                $pos  = mb_strpos($an, '-->');
                $an   = $pos !== false ? mb_substr($an, $pos + 3) : $an;
                $out .= '
<a href="'.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[6].'-6-'.KeepGoodChars($an).'-'.$this->tconfig.$z2 : 'links.php?lng='.$lng.'&amp;dirlnk='.KeepGoodChars($an).'&amp;tconfig='.$this->tconfig.$z2).'">
  <div class="licatlink">
    <i class="fas fa-external-link-square-alt" title="'.$an.'"></i>'.$an.'
  </div>
</a>';
            }
        } 
        else {
            if (empty($this->id)) $this->id = 1;
            if (empty($this->pg) && $this->dirlnk != 'no') {
                if (!empty($categ)) $out .= '
<div class="lititlecat">'.$categ.'</div>';
                $out .= '
<div class="text-center">
  <form name="retanlnk" method="post" action="'.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[6].'-'.$this->tconfig.$z2 
      : 'links.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'">
    '.SubmitButton($web[650]).'
  </form>
</div>';
            }
            if (!empty($dbwork)) {
                $typeRubrique = TYP_LINKS;
                $imgOpen      = "far fa-plus-square";
                $imgClose     = "far fa-minus-square";
                $subImgOpen   = "far fa-caret-square-down align-middle pt-2";
                $subImgClose  = "far fa-caret-square-up align-middle pt-2";
                $nlpg         = (count($dbwork)-($serviz[5]*($this->id-1))>$serviz[5])? $serviz[5] : count($dbwork)-($serviz[5]*($this->id-1)) ;
                $out         .= BeginJavascript().'var maxsub = '.$nlpg.';'.EndJavascript();
                $rubr         = '';
                $l            = 0;
                if (3 == abs($prt)) {
                    $this->id  = 1;
                    $serviz[5] = 99999;
                }
                $isNew = false;
                for ($i = $serviz[5]*($this->id-1); $i < Min($serviz[5]*$this->id, count($dbwork)); $i++) {
                    ReadDoc($dbwork[$i][4]);
                    $img = getNewUpdateDoc($creadate, $moddate, $serviz[144]);
                    if ($img != '') $isNew = true;
                    if ($lng == $lang[0]) {
                        $txt1 = $img.$fieldb1;
                        $txt2 = $fieldc1;
                        $txt3 = $fielda1;
                        $txt4 = $fieldd1;
                    } else {
                        $txt1 = $img.$fieldb2;
                        $txt2 = $fieldc2;
                        $txt3 = $fielda2;
                        $txt4 = $fieldd2;
                    }
                    if ($rubr <> $txt3 || $txt3 == '') {
                        $rubr  = $txt3;
                        $rubrs = explode('|', $rubr);
                        $srubr = count($rubrs) > 1 ? $rubrs[1] : $rubrs[0];
                        if ($l > 0) $out .= '
</div>';
                        if (trim($rubr) != '') {
                            $l++;
                            $out .= '
<div id="titreRubr'.$typeRubrique.$i.'" class="lirubr font-weight-bold" 
    onclick="MontreCacheItems(\'imgOpen'.$typeRubrique.$i.'\',\'imgClose'.$typeRubrique.$i.'\',\'itemsRubr'.$typeRubrique.$i.'\',\'itemsRubrSelect\');">
  <i id="imgOpen'.$typeRubrique.$i.'" class="'.$imgOpen.'" title="'.$web[429].'" style="display: none;"></i>
  <i id="imgClose'.$typeRubrique.$i.'" class="'.$imgClose.'" title="'.$web[57].'" style="display: inline;"></i>
  &nbsp;'.$srubr.'
</div>';
                            if (count($dbwork) == 1) $out .= '
<div id="itemsRubrSelect" style="display: block;">';
                            else $out .= '
<div id="itemsRubr'.$typeRubrique.$i.'" style="display: block;">';
                        }
                    }
                    $n = (count($dbwork) > $serviz[5])? $i-$serviz[5]*$this->id+$serviz[5] : $i;
                    $out .= '
  <div id="titreSubRubr'.$typeRubrique.$i.'" class="lirubr2 font-weight-bold" 
      onclick="MontreCacheItems(\'subImgOpen'.$typeRubrique.$i.'\',\'subImgClose'.$typeRubrique.$i.'\',
        \'itemSubRubr'.$typeRubrique.$i.'\',\'itemSubRubrSelect\');">'."\n".'
	<div class="float-left text-right lirubr3">
      <i id="subImgOpen'.$typeRubrique.$i.'" class="'.$subImgOpen.'" title="'.$web[429].'" style="display: none;"></i>
      <i id="subImgClose'.$typeRubrique.$i.'" class="'.$subImgClose.'" title="'.$web[57].'" style="display: inline;"></i>
    </div>'."\n".'
    <div class="lisubrubr"><a id="subRubr'.$typeRubrique.$i.'">'.$txt1.'</a></div>'."\n".'
  </div>'."\n";
                    if (count($dbwork) == 1) $out .= '
<div class="bord2" id="itemSubRubrSelect" style="display: block;">';
                    else $out .= '
<div class="bord2" id="itemSubRubr'.$typeRubrique.$i.'" style="display: block;">';
                    $printurl = $prt != 0 ? ' <strong> '.$txt4.' </strong>' : '';
                    $out .= '
  <div class="rep licontent">'.$txt2.'<div class="clearfix"></div>
    <p class="text-right c-pointer">';
                    $out .= '
      <a href="'.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[6].'-1-'.$dbwork[$i][4].$z2 : 'links.php?lng='.$lng.'&amp;pg='.$dbwork[$i][4].$z2).'">
        <i class="fas fa-link align-middle" title="'.$web[527].'"></i>
      </a>&nbsp;&nbsp;';
                    $out .= '
      <a href="'.$txt4.'" target="_blank">
        <i class="fas fa-globe align-middle" title="'.$web[304].'"></i>'.$printurl.'
      </a>
    </p>
  </div><div class="clearfix"></div>';
                    if (empty($prt)) $out .= 
  displayQuickConfig(NULL, $dbwork[$i][4], 'links&amp;form=2&amp;id='.$dbwork[$i][4], 
    'links&amp;act=i&amp;id='.$dbwork[$i][4], 'links&amp;del='.$dbwork[$i][4], 20);
                    $out .= '
</div>';
                }
                if ($l > 0) $out .= '
</div>';
                if (0 == $prt && 'on' != $serviz[80] && !$isNew) $out .= 
BeginJavascript().'
var nbRubr = '.$i.';
var typeRubr = "'.$typeRubrique.'";
for(i = 0; i < nbRubr; i++) {
	if((document.getElementById && document.getElementById("itemsRubr"+ typeRubr + i) != null) 
      || (document.all && document.all["itemsRubr"+ typeRubr + i] != undefined ) 
      || (document.layers && document.layers["itemsRubr"+ typeRubr + i] != undefined) ) {
		cache("itemsRubr"+ typeRubr + i);
		montre("imgOpen"+ typeRubr + i, "inline");
		cache("imgClose"+ typeRubr + i);
	}
}'.EndJavascript();
                if (0 == $prt && 'on' != $serviz[80]) $out .= 
BeginJavascript().'
var nbRubr = '.$i.';
var typeRubr = "'.$typeRubrique.'";
for(i = 0; i < nbRubr; i++) {
	if((document.getElementById && document.getElementById("itemSubRubr"+ typeRubr + i) != null) 
      || (document.all && document.all["itemSubRubr"+ typeRubr + i] != undefined ) 
      || (document.layers && document.layers["itemSubRubr"+ typeRubr + i] != undefined) ) {
		cache("itemSubRubr"+ typeRubr + i);
		montre("subImgOpen"+ typeRubr + i, "inline");
		cache("subImgClose"+ typeRubr + i);
	}
}'.EndJavascript();
            }
            elseif (!empty($this->pg)) $out .= '
<p>'.$web[36].'</p>';
            $out .= GetNavBar(($site['URLR'] == 'on' ? $lng.'-'.$urlrw[6].'-4-'.$this->dirlnk.'-' 
              : 'links.php?lng='.$lng.'&amp;dirlnk='.$this->dirlnk.'&amp;id='), count($dbwork), $this->id, $serviz[5], $z2);
            if (empty($this->pg) && $this->dirlnk != 'no') $out .= '
<div class="text-center">
  <form 
      name="retanlnk" 
      action="'.($site['URLR'] == 'on' 
        ? $lng.'-'.$urlrw[6].'-'.$this->tconfig.$z2 
        : 'links.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'" 
      method="post">
    '.SubmitButton($web[650]).'
  </form>
</div>';
        }
        return array('LNK', $out);
    }
}
?>