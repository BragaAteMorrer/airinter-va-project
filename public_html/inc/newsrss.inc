<?php
/*******************************************************************************
 *   News RSS
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

if (stristr($_SERVER['SCRIPT_NAME'], 'newsrss.inc')) {
    header('location:../index.php');
    die();
}
#[AllowDynamicProperties]
class GY_newsrss {
    public $tconfig;
    public $topmess;
    protected $url;
    protected $id;

    public function __construct() {
        $this->tconfig = import('tconfig', '', true, 0);
        $this->id      = import('id');
    }
    
    public function RSS_ok($pseudo, $lng) {
        global $site, $urlrw, $serviz, $web;
        $this->url = import('url', 'GET', true, '');
        $out = '';
        if ($serviz[38] != 'on') {
            $out = '
    <div class="text-center web342">'.$web[143];
            $href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng);
            $out .= '
      <p class="text-center">
        '.SubmitButton($web[135], 'self.location.href=\''.$href1.'\'').'
      </p>
    </div>';
            return array('RSS', $out);
        }
        return array('');
    }

    public function RSS_content() {
        global $nom, $page, $lang, $lng, $charset, $web, $serviz, $site, $page, $prt, $charset, $style;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, $fielda1, $fielda2, $site,
               $fieldb1, $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        if (!empty($this->url)) {
            switch ($this->url) {
            case 'news' : $this->url = $site[3].sprintf(RSSNEWS, $lng); break;
            case 'arts' : $this->url = $site[3].sprintf(RSSART, $lng); break;
            case 'blog' : $this->url = $site[3].sprintf(RSSBLOG, $lng); break;
            case 'all'  : $this->url = $site[3].sprintf(RSSALL, $lng); break;
            default: $this->url = '';
            }
        }
        ReadDoc($this->id);
        $this->tconfig = $fieldweb == 'ALL' ? $this->tconfig : $fieldweb;
        $rssurl        = empty($this->url) ? ($lng == $lang[0] ? $fieldb1 : $fieldb2) : $this->url;
        $rsstxt        = getLabel($nom[$lng == $lang[0] ? 36 : 37]).' - '.($lng == $lang[0] ? $fielda1 : $fielda2);
        $rsstxt        = $page[9] == '' ? $rsstxt : '<i class="fas fa-rss float-right align-middle""></i>'.$rsstxt;
        $this->topmess = mb_substr($rsstxt, 0, 1, $charset) == 'Â§' ? mb_substr($rsstxt, 1, mb_strlen($rsstxt ,$charset) ,$charset) : $rsstxt;
        $max_width     = $style != '1024' ? ($style == '800' ? '500px' : 'auto') : '720px';
        $max_height    = isset($serviz[133]) && $serviz[133] > 0 && empty($prt) ? $serviz[133] : 0;
        include 'inc/lastRSS.inc';
        $rss             = new lastRSS;
        $rss->default_cp = strtoupper($charset);
        $rss->cache_dir  = substr(CACHEREP, 0, strlen(CACHEREP)-1);
        $rss->cache_time = $serviz[39];
        $rs              = $rss->Get($rssurl);
        if (FALSE !== $rs) {
            $cpin  = strtoupper($rs['encoding']);
            $cpout = strtoupper($charset);
            if ($cpin == $cpout) {
                function MyIConv($txt) { return $txt; }
            } 
            else {
                function MyIConv($txt) {
                    global $cpin, $cpout;
                    return iconv($cpin, $cpout, $txt);
                }
            }
            $out = '';
            if (@isset($rs['image_url']) && $rs['image_url'] != '') {
                if (@isset($rs['image_width']))
                    $classimg = @$rs['image_width'] > 540 || @$rs['image_width'] == '' ? ' class="imgrss540"' : ' class="imgrss"';
                else
                    $classimg = ' class="imgrss540"';
                $out .= '
<style>
.imgrss540 { width: 540px; }
.imgrss { width: '.@$rs['image_width'].'px; }
</style>
<div class="text-center m-auto"><img src="'.$rs['image_url'].'" alt="" title="'.$rs['image_title'].'"'.$classimg.' /></div>';
            }
            if (isset($rs['link']) && $rs['title'] != '') {
                if (preg_match('!^https?://([-a-z0-9.]+)+(/[-a-z0-9.]*)*(\?[-a-z0-9=&/]+)?$!', $rs['link']))
                    $out .= '
<p class="forum"><b><a href="'.$rs['link'].'" target="_blank">'.MyIConv($rs['title']).'</a></b></p>
<br />';
                else
                    $out .= '
<p class="forum"><b>'.MyIConv($rs['title']).'</b></p>
<p class="text-center m-auto">'.$rs['link'].'</p><br />';
            }
            if (isset($rs['description'])) {
                $out .= '
<p><b>'.html_entity_decode(MyIConv($rs['description']), ENT_QUOTES, $charset).'</b></p>
<hr class="w-60" />';
            }            
            $i = 0;
            foreach($rs['items'] as $item) {
                $i++;
                if ($item['title'] == '' && $item['link'] == '') {
                    $txt = '';
                }
                elseif ($item['title'] == '') {
                    $txt = '<a href="'.$item['link'].'" target="_blank">'.$item['link'].'</a><br /><br />';
                }
                elseif ($item['link'] == '') {
                    $txt = '<u>'.MyIConv($item['title']).'</u><br /><br />';
                }
                else {
                    $txt = '<a href="'.$item['link'].'" target="_blank" title="'.$web[297].'">
                              '.MyIConv($item['title']).'&nbsp; <i class="fas fa-eye" title="'.$web[297].'"></i>
                            </a><br /><br />';
                }
                $txt1 = '';
				if (isset($item['description'])) {
					$desc = html_entity_decode(MyIConv($item['description']), ENT_QUOTES, $charset);			  
					$txt1 = '<b>'.$txt.'</b>'.$desc;
				}
                if (!empty($txt1)) {
                    $out .= '
<style>
.maxwidthitem { max-width: '.$max_width.'; }
.txt1rss {
  height: '.($max_height > 0 ? $max_height.'px;' : 'auto;').'
  overflow: '.($max_height > 0 ? 'hidden;' : 'visible;').'
}
</style>
<div id="texte2'.$i.'" class="rep text2newsrss maxwidthitem">
  <div class="rep text-justify txt1rss">
    '.$txt1.'
  </div>';
                    if ($max_height > 0) $out .= '
  <p class="text-right">
    <a href="javascript:cache(\'texte2'.$i.'\');montre(\'texte1'.$i.'\');" class="box" title="'.$web[296].'">
    '.$web[296].' <i class="far fa-plus-square" title="'.$web[296].'"></i></a>
  </p>
</div>
<div id="texte1'.$i.'" class="bord text-justify text1newsrss maxwidthitem" style="display:none;">
          '.$txt1.'
  <p class="text-right c-pointer">
    <a href="javascript:cache(\'texte1'.$i.'\');montre(\'texte2'.$i.'\');" class="box" title="'.$web[57].'">
      '.$web[57].' <i class="far fa-minus-square" title="'.$web[57].'"></i>
    </a>
  </p>
</div>';
                }
                if (isset($item['pubDate']) && $item['pubDate'] != '') $out .= '
<div class="m-auto datitemrss maxwidthitem">('.FormatDate($item['pubDate']).')</div>';
                else $out .= '
<div class="m-auto datitemrss maxwidthitem">(???)</div>';
                $out .= '
<hr class="maxwidthitem" />';
            }
            $modfl = @filemtime(CACHEREP.'rsscache_'.md5($rssurl));
            $txt   = $modfl == false ? $web[261] : $web[263].' <b>'.FormatDateStamp($modfl).'</b>';
            $out  .= '
<p>'.$txt.'</p>';
        }
        else 
            $out = '
<p>'.$web[262].' '.$rssurl.'</p>';
        return array('RSS', $out);
    }
}    
?>
