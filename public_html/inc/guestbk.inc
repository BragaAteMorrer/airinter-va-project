<?php
/*******************************************************************************
 *   Guestbook
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

if (stristr($_SERVER['SCRIPT_NAME'], 'guestbk.inc')) {
    header('location:../index.php');
    die();
}
#[AllowDynamicProperties]
class GY_guestbk {
    public $tconfig;
    public $topmess;
    protected $pg;
    protected $id;

    public function __construct() {
        global $menuico, $nom, $lang, $lng, $page, $pg, $id;
        $topmess       = getLabel($nom[$lng == $lang[0] ? 9 : 19]);
        $this->topmess = $page[9] == '' ? $topmess : '<i class="fas fa-book-reader float-right align-middle"></i>'.$topmess;
        $this->tconfig = $menuico[18] == 'ALL' ? import('tconfig', '', true, 0) : $menuico[18];
        $this->pg      = $pg;
        $this->id      = $id;
    }
    
    public function GBK_ok($pseudo, $lng) {
        global $site, $urlrw, $serviz, $web, $z2, $members;
        $out = '';
        if ($serviz[12] != 'on') {
            $href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng);
            $out .= '
<div class="text-center web342">'.$web[143].'
  <p class="text-center">
    '.SubmitButton($web[135], 'self.location.href=\''.$href1.'\'').'
  </p>
</div>';
            return array('GBK', $out);
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
            return array('GBK', $out);
        }
        elseif (!empty($this->pg)) {
            $dbwork = SelectDBFields(TYP_GUESTBK, 'a', $this->pg);
            $isdoc  = (count($dbwork) > 0);
            if (!$isdoc) {
                if ($site[10] != '') return array('h1', '');
                else return array('h2', '');
            }
        }
        return array('');
    }
    
    public function GBK_content() {
        global $lng, $site, $serviz, $web, $prt, $urlrw, $z2;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, $fielda1, $fielda2, $fieldb1, 
               $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        if (!empty($this->pg)) {
            if (count(SelectDBFields(TYP_GUESTBK, 'a', $this->pg)) != 0) {
                $dbwork = SelectDBFields(TYP_GUESTBK, 'a', $this->pg);
            } else {
                $dbwork = array();
            }
        } else {
            $dbwork = SelectDBFields(TYP_GUESTBK, 'a', '');
        }
        @rsort($dbwork);
        $out = '';
        if (empty($prt)) $out .= '
<form 
    action="'.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[14].'-'.TYP_GUESTBK.$z2 
      : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_GUESTBK.$z2).'" 
    method="post">
  <p class="text-center">'.SubmitButton($web[41]).'</p>
</form>
<hr />';
        if (empty($this->id)) $this->id = 1;
        if (!empty($dbwork)) {
            if (3 == abs($prt)) {
                $this->id  = 1;
                $serviz[7] = 99999;
            }
            for ($i = $serviz[7]*($this->id-1); $i < Min($serviz[7]*$this->id, count($dbwork)); $i++) {
                ReadDoc($dbwork[$i][1]);
                $img = getNewUpdateDoc($creadate, $moddate, $serviz[141]);
                $out .= '
<div class="bord text-center w-98">
  <div class="table-responsive-md">
  <div class="d-table text-center w-100">
    <div class="d-table-row">
      <div class="forum d-table-cell w-18">'.$web[39].$fielda1.'</div>
      <div class="quest d-table-cell">'.$web[6].' ';
                if ($fieldd1 == 'on') $out .= displayWriteEmail('guest'.$i, $author, $email);
                else $out .= '
        <b>'.$author.'</b>';
                $out .= '
        '.$web[7].' '.FormatDate($creadate).'
        <div class="gbkb1W">';
                if ($fieldb1 != '' && $fieldb1 != 'http://' && $fieldb1 != 'https://') $out .= '
          <a href="'.$fieldb1.'" target="_blank">
            <i class="fas fa-globe"></i>&nbsp;&nbsp;'.$fieldb1.'
          </a>&nbsp;';
                $out .= $img.'
        </div>
      </div>
    </div>
  </div>
  </div>
  <div class="rep w-98 m-auto gbkc1">'.$fieldc1;
            if (empty($prt)) $out .= 
  displayQuickConfig(NULL, $dbwork[$i][1], 'guestbk&amp;form=2&amp;id='.$dbwork[$i][1], 
    'guestbk&amp;act=i&amp;id='.$dbwork[$i][1], 'guestbk&amp;del='.$dbwork[$i][1], 22);
            $out .= '
  </div>
</div>';
                if ($i < min($serviz[7]*$this->id, count($dbwork))-1) $out .= '
<hr />';
            }
        } else if (!empty($this->pg)) {
            $out .= '
<p>'.$web[36].'</p>';
        }
        $out .= GetNavBar(($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[7].'-2'.'-' 
          : $site['GB'].'.php?lng='.$lng.'&amp;id='), 
          count($dbwork), $this->id, $serviz[7], $z2);
        return array('GBK', $out);
   }
}
?>