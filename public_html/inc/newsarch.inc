<?php
/*******************************************************************************
 *   Archived News
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

if (stristr($_SERVER['SCRIPT_NAME'], 'newsarch.inc')) {
    header('location:../index.php');
    die();
}
#[AllowDynamicProperties]
class GY_newsarch {
    public $tconfig;
    public $topmess;
    protected $pg;
    protected $id;

    public function __construct() {
        global $menuico, $nom, $lang, $lng, $page, $web;
        $topmess       = getLabel($nom[$lng == $lang[0] ? 7 : 17]).' - '.$web[399];
        $this->topmess = $page[9] == '' ? $topmess : '<i class="far fa-newspaper float-right align-middle""></i>'.$topmess;
        $this->tconfig = $menuico[12] == 'ALL' ? import('tconfig', '', true, 0) : $menuico[12];
        $this->pg      = import('pg');
        $this->id      = import('id');
    }
    
    public function NWA_ok($pseudo, $lng) {
        global $site, $urlrw, $serviz, $web, $z2, $members;
        $out = '';
        if ($serviz[8] != 'on') {
            $out .= '
<div class="text-center web342">'.$web[143];
            $href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng);
            $out .= '
  <p class="text-center">
    '.SubmitButton($web[135], 'self.location.href=\''.$href1.'\'').'
  </p>';
            $out .= '
</div>';
            return array('NWA', $out);
        }
        elseif (!isAccessGranted($members[13])) {
            $out .= '
<div class="text-center web342">'.$web[342];
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
            return array('NWA', $out);
        }
        if (!empty($this->pg)) {
            $db1 = SelectDbFieldsByField(ReadDbFields(DBNEWSARCH), 4, $this->pg);
            $db2 = array_filter($db1, 'IsNotEmptyTitle');
            if (1 != count($db2) || !isAccessGranted($db2[0][6]) || !isAuthorizedPublication($db2[0][5])) {
                if ($site[10] != '') return array('h1', '');
                else return array('h2', '');
            }
        }
        return array('');
    }
    
    public function NWA_content() {
        global $lng, $lang, $site, $serviz, $web, $urlrw, $z2, $prt, $pos;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, $fielda1, $fielda2, $fieldb1, 
               $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        $this->id = strip_tags($this->id);
        $out      = '';
        $archdate = ReadCounter(DBNEWSARCHDATE);
        if ($archdate > 0) {
            $out .= '
  <div class="text-center archNews">'.$web[583].' '.FormatDate($archdate).'
    <br />'.$web[582].'&nbsp;
    <a href="'.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[8].$z2 : $site['NE'].'.php?lng='.$lng.$z2).'" title="'.$web[581].'">'.$web[581].'
      <i class="far fa-file-alt"></i>
    </a>
  </div>';
        }
        if (!empty($this->pg)) {
            $db1 = SelectDbFieldsByField(ReadDbFields(DBNEWSARCH), 4, $this->pg);
            $db2 = array_filter($db1, 'IsNotEmptyTitle');
            if (1 != count($db2) || !isAccessGranted($db2[0][6]) || !isAuthorizedPublication($db2[0][5])) {
                $dbwnews = array();
            } else {
                $dbwnews = array(array(TYP_NEWS, $this->pg, 'a'));
            }
        } else {
            $db1 = ReadDbFields(DBNEWSARCH);
            $db2 = array_filter($db1, 'IsNotEmptyTitle');
            $dbwnews = array();
            foreach ($db2 as $db) {
                if (isAccessGranted($db[6])) {
                    $dbwnews[] = array(TYP_NEWS, $db[4], 'a');
                }
            }
        }
        @rsort($dbwnews);
        unset($db1, $db2);
        $this->id    = empty($this->id) ? 1 : $this->id;
        $minnews     = $serviz[2]*($this->id-1);
        $maxnews     = Min($serviz[2]*$this->id, count($dbwnews));
        $max_height2 = isset($site[13]) && $site[13] > 0 && empty($prt) ? $site[13] : 0;
        if (!empty($dbwnews)) {
            for ($i = $minnews; $i < $maxnews; $i++) {
                ReadDoc($dbwnews[$i][1], ARCHDBBASE);
                if ($lng == $lang[0]) {
                    $txt1 = $fieldb1;
                    $txt2 = $fieldc1;
                }
                else {
                    $txt1 = $fieldb2;
                    $txt2 = $fieldc2;
                }
                // date création
                if ($serviz[72] == '') {
                    $txt72 = ' - '.$web[7].' ';
                    $txt72 .= '
    <b>'.FormatDate($creadate).'</b> ';
                } else {
                    $txt72 = '';
                }
                // auteur
                $txt73 = '';
                if ($serviz[73] == '') {
                    $txt73 = (empty($txt72) ? ' - ' : ' ').$web[6].' <b>'.$author.'</b>';
                }
                // icone
                if (!empty($fieldd2)) {
                    $txt1 = '<img src="'.CHEMIN.'img/'.$fieldd2.'" class="'.($serviz[152] == 'on' 
                      ? 'float-left' : 'float-right').'" alt="'.$fieldd2.'" />'.$txt1;
                }
                $txt3 = '
<div class="forum2"><b>'.$txt1.'</b>'.$txt72.$txt73.'</div>
<style>
.repNews2 {
  max-height: '.($max_height2 > 0 ? $max_height2.'px;' : 'none;').'
  overflow: '.($max_height2 > 0 ? 'hidden;' : 'visible;').'
  background: transparent;
}
</style>';
                if (!empty($txt1)) {
                    $txt3 .= '
<div id="texte3'.$i.'" class="rep fullNews">
  <div class="rep repNews2">
    '.$txt2.'
  </div><div class="clearfix"></div>';
                if ($max_height2 > 0) $txt3 .= '
  <p class="text-right">
    <a href="javascript:ActiveMenu(\'texte3\',\'texte2\',0,'.$maxnews.','.$i.')" class="box" title="'.$web[296].'">
      '.$web[296].' <i class="far fa-plus-square"></i>
    </a>
  </p>';
                $txt3 .= '
</div>' ;
                if ($max_height2 > 0) $txt3 .= '
<div id="texte2'.$i.'" class="rep txt2News" style="display:none;">'.$txt2.'
  <p class="text-right c-pointer">
    <a href="#news'.$pos.$i.'" title="'.$web[57].'" onclick="cache(\'texte2'.$i.'\');montre(\'texte3'.$i.'\')">
      '.$web[57].' <i class="far fa-minus-square"></i>
    </a>
  </p>
</div><div class="clearfix"></div>';
                }
                $out .= $txt3.'
<br />';
            }
        } elseif (!empty($this->pg)) {
            $out .= '
<p>'.$web[36].'</p>';
        }
        $this->pg = empty($this->pg)? '0' : $this->pg;
        $out .= GetNavBar(($site['URLR'] == 'on'? 
            $lng.'-'.$urlrw[8].'-4-'.$this->pg.'-': 
            'newsarch.php?lng='.$lng.'&amp;pg='.$this->pg.'&amp;id='), count($dbwnews), $this->id, $serviz[2], $z2);
        return array('NWA', $out);
    }
}
?>