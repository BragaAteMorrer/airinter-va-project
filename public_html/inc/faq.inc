<?php
/*******************************************************************************
 *   FAQ
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

if (stristr($_SERVER['SCRIPT_NAME'], 'faq.inc')) {
    header('location:../index.php');
    die();
}
#[AllowDynamicProperties]
class GY_faq {
    public $tconfig;
    public $topmess;
    protected $pg;
    protected $id;
    protected $dirfaq;

    public function __construct() {
        global $menuico, $nom, $lang, $lng, $page, $pg, $id;
        $topmess       = getLabel($nom[$lng == $lang[0] ? 24 : 25]);
        $this->topmess = $page[9] == '' ? $topmess : '<i class="far fa-question-circle float-right align-middle"></i>'.$topmess;
        $this->tconfig = $menuico[17] == 'ALL' ? import('tconfig', '', true, 0) : $menuico[17];
        $this->pg      = $pg;
        $this->id      = $id;
        $this->dirfaq  = import('dirfaq');
    }
    
    public function FAQ_ok($pseudo, $lng) {
        global $site, $urlrw, $serviz, $web, $z2, $members;
        $out = '';
        if ($serviz[14] != 'on') {
            $href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng);
            $out .= '
<div class="text-center web342">'.$web[143].'
  <p class="text-center">
    '.SubmitButton($web[135], 'self.location.href=\''.$href1.'\'').'
  </p>
</div>';
            return array('FAQ', $out);
        }
        elseif (!isAccessGranted($members[4])) {
            $out .= '
<div class="text-center web342">'.$web[342];
            $href1 = CHEMIN.($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[3].'-0-'.$tconfig.$z2 
              : 'connect.php?lng='.$lng.'&tconfig='.$tconfig.$z2);
            $href2 = CHEMIN.($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[15].'-5-new-'.$tconfig.$z2 
              : $site['USR'].'.php?lng='.$lng.'&uuser=new&tconfig='.$tconfig.$z2);
            if ($members[19] != '0' && $members[19] != '3') $out .= '
  <p class="text-center">
    '.SubmitButton($web[630], 'self.location.href=\''.$href1.'\'').
      (empty($pseudo) ? '&nbsp;&nbsp;'.SubmitButton($web[160], 'self.location.href=\''.$href2.'\'') : '').'
  </p>';
            $out .= '
</div>';
            return array('FAQ', $out);
        }
        elseif (!empty($this->pg)) {
            $dbwork = SelectDBFieldsByField(ReadDBFields(DBFAQ), 4, $this->pg);
            $isdoc  = (count($dbwork) > 0);
            if ($isdoc) {
                $isdoc = isAuthorizedPublication($dbwork[0][5]);
                if (!isAccessGranted($dbwork[0][6])) return array('h1', '');
            }
            if (!$isdoc) {
                if ($site[10] != '') return array('h2', '');
                else return array('h3', '');
            }
        }
        return array('');
    }
    
    public function FAQ_content() {
        global $lng, $lang, $site, $serviz, $web, $prt, $urlrw, $z2, $tconfig;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, $fielda1, $fielda2, $fieldb1, 
               $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        $out    = '';
        $dbwork = array();
        if (!empty($this->pg)) {
            $dbwork = SelectDBFieldsByField(ReadDBFields(DBFAQ), 4, $this->pg);
            $isdoc  = (count($dbwork) != 0);
            $dbwan  = array();
        } else {
            $dbwork2 = ReadDBFields(DBFAQ);
            $dbwork1 = array_filter($dbwork2, 'IsNotEmptyTitle');
            unset($dbwork2);
            $dbwan = array();
            $k     = $lng == $lang[0] ? 0 : 1;
            if (empty($this->dirfaq) || $this->dirfaq == 'no') {
                foreach ($dbwork1 as $db) {
                    if (isAccessGranted($db[6]) && isAuthorizedPublication($db[5])) {
                        $cats  = explode('|', $db[0 + $k]);
                        $catan =  count($cats) > 1 ? $cats[0] : $web[649];
                        if (array_search($catan, $dbwan) === false) {
                            $dbwan[] = $catan;
                        }
                    }
                }
                if (count($dbwan) == 1 && $dbwan[0] == $web[649]) {
                    $this->dirfaq = 'no';
                    foreach ($dbwork1 as $db) {
                        if (isAccessGranted($db[6]) && isAuthorizedPublication($db[5])) {
                            $dbwork[] = $db;
                        }
                    }
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
                        if ($this->dirfaq == $catan) {
                            $dbwork[] = $db;
                            $categ    = $cat0;
                        }
                    }
                }
                $categ = $this->dirfaq == KeepGoodChars($web[649]) ? $web[649] : $categ;
            }
            unset($dbwork1);
        }
        sort($dbwork);
        if (empty($this->dirfaq) && empty($this->pg)) {
            if (!empty($dbwan)) {
                foreach ($dbwan as $an) {
                    $pos = mb_strpos($an, '-->');
                    $an  = $pos !== false ? mb_substr($an, $pos + 3) : $an;
                    $out .= '
<a href="'.($site['URLR'] == 'on' 
    ? $lng.'-'.$urlrw[4].'-8-'.KeepGoodChars($an).'-'.$tconfig.$z2 
    : 'faq.php?lng='.$lng.'&amp;dirfaq='.KeepGoodChars($an).'&amp;tconfig='.$tconfig.$z2).'">
  <div class="facatlink">
    <i class="fas fa-question-circle" title="'.$an.'"></i>'.$an.'
  </div>
</a>';
                }
            }
        } else {
            if (empty($this->id)) $this->id = 1;
            if (empty($this->pg) && $this->dirfaq != 'no') {
                if (!empty($categ)) $out .= '
<div class="fatitlecat">'.$categ.'</div>';
                $out .= '
<div class="text-center">
  <form name="retanfaq" method="post" action="'.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[4].'-9-'.$this->tconfig.$z2 
      : 'faq.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'">
    '.SubmitButton($web[650]).'
  </form>
</div>';
            }
            if (!empty($dbwork)) {
                $typeRubrique = TYP_FAQ;
                $imgOpen      = "far fa-plus-square";
                $imgClose     = "far fa-minus-square";
                $subImgOpen   = "far fa-caret-square-down";
                $subImgClose  = "far fa-caret-square-up";
                $nfaqpg       = (count($dbwork)-($serviz[6]*($this->id-1))>$serviz[6])? $serviz[6] : count($dbwork)-($serviz[6]*($this->id-1)) ;
                $out .= BeginJavascript().'var maxsub = '.$nfaqpg.';'.EndJavascript();
                $rubr = '';
                $l    = 0;
                if (3 == abs($prt)) {
                    $this->id  = 1;
                    $serviz[6] = 999999;
                }
                $isNew = false;
                for ($i = $serviz[6]*($this->id-1); $i < Min($serviz[6]*$this->id, count($dbwork)); $i++) {
                    ReadDoc($dbwork[$i][4]);
                    $img = getNewUpdateDoc($creadate, $moddate, $serviz[140]);
                    if ($img != '') $isNew = true;
                    if ($lng == $lang[0]) {
                        $txt1 = $img.$fieldb1;
                        $txt2 = $fieldc1;
                        $txt3 = $fielda1;
                    } else {
                        $txt1 = $img.$fieldb2;
                        $txt2 = $fieldc2;
                        $txt3 = $fielda2;
                    }
                    $txt4 = FormatDate($moddate);
                    $txt5 = FormatDate($creadate);
                    if ($rubr <> $txt3 || $txt3 == '') {
                        $rubr  = $txt3;
                        $rubrs = explode('|', $rubr);
                        $srubr = count($rubrs) > 1 ? $rubrs[1] : $rubrs[0];
                        if ($l > 0) $out .= '
</div>';
                        if (trim($rubr) != '') {
                            $l++;
                            $out .= '
<div id="titreRubr'.$typeRubrique.$i.'" class="farubr font-weight-bold" 
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
                    $n = (count($dbwork) > $serviz[6])? $i-$serviz[6]*$id+$serviz[6] : $i ;
                    $out .= '
  <div id="titreSubRubr'.$typeRubrique.$i.'" class="farubr2 font-weight-bold" 
      onclick="MontreCacheItems(\'subImgOpen'.$typeRubrique.$i.'\',\'subImgClose'.$typeRubrique.$i.'\',
        \'itemSubRubr'.$typeRubrique.$i.'\',\'itemSubRubrSelect\');">'."\n".'
	<div class="float-left text-right farubr3">
      <i id="subImgOpen'.$typeRubrique.$i.'" class="'.$subImgOpen.'" title="'.$web[429].'" style="display: none;"></i>
      <i id="subImgClose'.$typeRubrique.$i.'" class="'.$subImgClose.'" title="'.$web[57].'" style="display: inline;"></i>
	</div>'."\n".'
	<div class="fasubrubr"><a id="subRubr'.$typeRubrique.$i.'">'.$txt1.'</a></div>'."\n".'
  </div>'."\n";
                    if (count($dbwork) == 1) $out .= '
  <div class="bord2" id="itemSubRubrSelect" style="display: block;">';
                    else $out .= '
  <div class="bord2" id="itemSubRubr'.$typeRubrique.$i.'" style="display: block;">';
                    $out .= '
    <div class="rep facontent">'.$txt2.'<div class="clearfix"></div>';
                    if ($serviz[69] == '' || $serviz[70] == '' || $serviz[71] == '') {
                        $out .= '
    <hr />
    <div class="row">
      <div class="col-md-6 pb-3 fainfos">';
                        if ($serviz[69] == '') $out .= '
        '.$web[95].'<strong>'.$txt5.'</strong>';
                        if ($serviz[70] == '' && $moddate > substr($creadate,0,12)) $out .= '
        '.$web[20].'<strong>'.$txt4.'</strong>';
                        $out .= '
      </div>
      <div class="col-md-6 pb-3">';
                        if ($prt == 0) {
                            $out .= '
        <span class="float-right">
		  <a href="'.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[4].'-3-'.$dbwork[$i][4] : 'faq.php?lng='.$lng.'&amp;pg='.$dbwork[$i][4]).'">
            <i class="fas fa-link align-middle" title="'.$web[527].'"></i>
          </a>&nbsp;';
                            if ($serviz[71] == '') $out .= '
		  <a href="'.($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[4].'-4-'.$dbwork[$i][4].'--1' 
              : 'faq.php?lng='.$lng.'&amp;pg='.$dbwork[$i][4].'&amp;prt=-1').'" target="_blank">
            <i class="far fa-eye align-middle" title="'.$web[264].'"></i>
          </a>&nbsp;
		  <a href="'.($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[4].'-4-'.$dbwork[$i][4].'-1' 
              : 'faq.php?lng='.$lng.'&amp;pg='.$dbwork[$i][4].'&amp;prt=1').'" target="_blank">
            <i class="fas fa-print align-middle" title="'.$web[22].'"></i>
          </a>&nbsp;';
                            $out .= '
        </span>';
                        }
                        if (empty($prt)) $out .= 
        displayQuickConfig(NULL, $dbwork[$i][4], 'faq&amp;form=2&amp;id='.$dbwork[$i][4], 
          'faq&amp;act=i&amp;id='.$dbwork[$i][4], 'faq&amp;del='.$dbwork[$i][4], 21).'
      </div>
    </div>';
                    }
                    $out .= '
    </div>
  </div>';
                }
                if ($l > 0) {
                    $out .= '
</div>';
                }
                if (0 == $prt && 'on' != $serviz[79] && !$isNew) {
                    $out .= '
<script>
var nbRubr = '.$i.';
var typeRubr = "'.$typeRubrique.'";
for(i = 0; i < nbRubr; i++) {
	if((document.getElementById && document.getElementById("itemsRubr"+ typeRubr + i) != null) 
      || (document.all && document.all["itemsRubr"+ typeRubr + i] != undefined ) 
      || (document.layers && document.layers["itemsRubr"+ typeRubr + i] != undefined)) {
		cache("itemsRubr"+ typeRubr + i);
		montre("imgOpen"+ typeRubr + i,"inline");
		cache("imgClose"+ typeRubr + i);
	}
}
</script>';
                }
                if (0 == $prt && 'on' != $serviz[79]) {
                    $out .= '
<script>
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
}
</script>';
                }
            } elseif (!empty($this->pg)) $out .= '
<p>'.$web[36].'</p>';
            $out .= GetNavBar(($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[4].'-7-'.$this->dirfaq.'-' 
              : 'faq.php?lng='.$lng.'&amp;dirfaq='.$this->dirfaq.'&amp;id='), 
              count($dbwork), $this->id, $serviz[6], $z2);
            if (empty($this->pg) && $this->dirfaq != 'no') $out .= '
<div class="text-center">
  <form 
      name="retanfaq" 
      action="'.($site['URLR'] == 'on' 
        ? $lng.'-'.$urlrw[4].'-9-'.$this->tconfig.$z2 
        : 'faq.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'"
      method="post">
    '.SubmitButton($web[650]).'
  </form>
</div>';
        }
        return array('FAQ', $out);
    }
}
?>