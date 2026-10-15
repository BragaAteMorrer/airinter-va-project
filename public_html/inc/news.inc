<?php
/*******************************************************************************
 *   News
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2022 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.21 (December 17, 2024) : Validation Php 8.4
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'news.inc')) {
    header('location:../index.php');
    die();
}
#[AllowDynamicProperties]
class GY_news {
    public $tconfig;
    public $topmess;
    protected $pg;
    protected $id;
    protected $nbox;
    protected $pos;

    public function __construct() {
        global $menuico, $nom, $lang, $lng, $page, $pg, $id, $pos;
        static $nbox = 0;
        $topmess       = getLabel($nom[$lng == $lang[0] ? 7 : 17]);
        $this->topmess = $page[9] == '' ? $topmess : '<i class="far fa-newspaper float-right align-middle""></i>'.$topmess;
        $this->tconfig = $menuico[12] == 'ALL' ? import('tconfig', '', true, 0) : $menuico[12];
        $this->pg      = $pg;
        $this->id      = $id;
        $this->nbox    = $nbox++;
        $this->pos     = $pos;
    }
    
    public function NEWS_ok($pseudo, $lng) {
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
            return array('NEW', $out);
        }
        elseif (!isAccessGranted($members[13])) {
            $out .= '
<div class="text-center web342">'.$web[342];
            if ($members[19] != '0' && $members[19] != '3') {
                $href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[3].'-0-'.$tconfig.$z2 : 'connect.php?lng='.$lng.'&tconfig='.$tconfig.$z2);
                $href2 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[15].'-5-new-'.$tconfig.$z2 : $site['USR'].'.php?lng='.$lng.'&uuser=new&tconfig='.$tconfig.$z2);
                $out .= '
  <p class="text-center">
    '.SubmitButton($web[630], 'self.location.href=\''.$href1.'\'').
      (empty($pseudo) ? '&nbsp;&nbsp;'.SubmitButton($web[160], 'self.location.href=\''.$href2.'\'') : '').'
  </p>';
            }
            $out .= '
</div>';
            return array('NEW', $out);
        }
        if (!empty($this->pg)) {
            $db1 = SelectDbFieldsByField(ReadDbFields(DBNEWS), 4, $this->pg);
            $db2 = array_filter($db1, 'IsNotEmptyTitle');
            if (empty($db2) || 1 != count($db2)) {
                if ($site[10] != '') return array('h1', '');
                else return array('h2', '');
            }
        }
        return array('');
    }
    
    public function NEWS_content() {
        global $lng, $lang, $site, $serviz, $web, $prt, $urlrw, $z2;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, $fielda1, $fielda2, $fieldb1, 
               $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        //$this->id    = strip_tags($this->id);
        $max_height  = isset($site[29]) && $site[29] > 0 && empty($prt) ? $site[29] : 0;
        $max_height2 = isset($site[13]) && $site[13] > 0 && empty($prt) ? $site[13] : 0;
        $out = $outrs = '';
        if ($prt == 0 && $serviz[106] == 'on') {
            include CONFIGREP.'sncode.inc';
            if (!empty($coders))
                $outrs .= '
    <div class="codersNews">'.$coders.'</div>';
        }
        $archdate = ReadCounter(DBNEWSARCHDATE);
        if ($archdate > 0) {
            $out .= '
<div class="text-center archNews">'.$web[580].' '.FormatDate($archdate).'
  <br />'.$web[579].'&nbsp;
  <a href="'.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[8].'-26'.$z2 : 'newsarch.php?lng='.$lng.$z2).'" title="'.$web[578].'">'.$web[578].'
    <i class="far fa-file-alt"></i>
  </a>
</div>';
        }
        $out .= '
<div class="headNews text-center">';
        if ($serviz[16] == 'on' && empty($prt)) {
            $rssnewsurl = $site[3].sprintf(RSSNEWS, $lng);
            $out .= '
  <style>
    .headNews .overpop { padding-top: 8px; }
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
    <a href="'.CHEMIN.'newsrss.php?lng='.$lng.'&url=news&tconfig='.$this->tconfig.$z2.'" class="rssNews float-left">
      <i class="fas fa-rss"></i>
    </a>
  </div>';                            
        }
        if ($site[5] != '2' && empty($prt)) {
            $out .= '
  <form 
    name="postnews'.$this->nbox.'" 
    action="'.CHEMIN.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[14].'-'.TYP_NEWS.$z2 
      : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_NEWS.$z2).'" 
    method="post">
    <input type="hidden" name="tconfig" value="'.$this->tconfig.'" />
    <div class="text-center'.($serviz[16] == 'on' ? ' postNews' : '').'">'.SubmitButton($web[4]).'</div>
  </form>';
        } else
            $out .= '<div class="divnewline"></div>';
        $out .= '
</div>';
        if (!empty($this->pg)) {
            $db1 = SelectDbFieldsByField(ReadDbFields(DBNEWS), 4, $this->pg);
            $db2 = array_filter($db1, 'IsNotEmptyTitle');
            if (isAccessGranted($db2[0][6]) && isAuthorizedPublication($db2[0][5]))
                $dbwnews = array(array(TYP_NEWS, $db2[0][5], $this->pg, 'a'));
            else
                $dbwnews = array();
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
        $this->id = empty($this->id) ? 1 : $this->id;
        $minnews = $serviz[2]*($this->id-1);
        $maxnews = Min($serviz[2]*$this->id, count($dbwnews));
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
                    $txt1 = '<img src="'.CHEMIN.'img/'.$fieldd2.'" class="'.($serviz[152] == 'on' 
                      ? 'float-left' : 'float-right').'" alt="'.$fieldd2.'" />'.$txt1;
                }
                $out .= '
  <div class="bord">
    <div class="quest '.($serviz[152] != 'on' ? 'text-left' : 'text-right').'">
	  <a href="'.$site[3].($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[8].'-22-'.$fileid.'-'.$this->tconfig.$z2 
          : $site['NE'].'.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;tconfig='.$this->tconfig.$z2).'" title="'.$web[511].'">
        '.$img.'<b>'.$txt1.'</b>
      </a>'.$txt72.$txt73.'
    </div>';
                $txt3 = '
    <a id="news'.$this->pos.$i.'"></a>
<style>
.repNews2 {
  max-height: '.($max_height2 > 0 ? $max_height2.'px;' : 'none;').'
  overflow: '.($max_height2 > 0 ? 'hidden;' : 'visible;').'
  background: transparent;
}
</style>';
                if (!empty($txt2)) {
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
        <a href="#news'.$this->pos.$i.'" title="'.$web[57].'" onclick="cache(\'texte2'.$i.'\');montre(\'texte3'.$i.'\')">
          '.$web[57].' <i class="far fa-minus-square"></i>
        </a>
      </p>
    </div><div class="clearfix"></div>';
                }
                $out .= $txt3.
                    displayQuickConfig($this->pos, $dbwnews[$i][2], 'news&amp;form=2&amp;id='.$dbwnews[$i][2], 
                      'news&amp;act=i&amp;id='.$dbwnews[$i][2], 'news&amp;del='.$dbwnews[$i][2], 14).'
  </div>
  <br />';
            }
        } elseif (!empty($this->pg)) {
            $out .= '
  <p>'.$web[36].'</p>';
        }
        $this->pg = empty($this->pg) ? '0' : $this->pg;
        $out .= GetNavBar(($site['URLR'] == 'on'? 
            $lng.'-'.$urlrw[8].'-3-'.$this->pg.'-': 
            $site['NE'].'.php?lng='.$lng.'&amp;pg='.$this->pg.'&amp;id='), count($dbwnews), $this->id, $serviz[2], $z2);
        return array('NEW', $out);
    }
}
?>