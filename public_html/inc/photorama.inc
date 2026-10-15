<?php
/*******************************************************************************
 *   Photos
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
 * v6.00.22 (December 19, 2025) : Validation Php 8.4
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'photorama.inc')) {
    header('location:../index.php');
    die();
}

if (is_dir(CHEMIN.'inc/photo/lightbox')) {
    $headinc .= '
<link href="'.CHEMIN.'inc/photo/lightbox/css/lightbox.min.css" rel="stylesheet" />'.
JavascriptFile(CHEMIN.'inc/photo/lightbox/js/lightbox.min.js').
JavascriptFile(CHEMIN.'inc/photo/lightbox/js/modernizr.custom.js');
}
if (is_dir(CHEMIN.'inc/photo/lytebox')) {
    $headinc .= '
<link rel="stylesheet" href="'.CHEMIN.'inc/photo/lytebox/lytebox.css" type="text/css" media="screen" />'.
JavascriptFile(CHEMIN.'inc/photo/lytebox/lytebox.js');
}
#[AllowDynamicProperties]
class GY_photo {
    public $tconfig;
    public $topmess;
    public $widepage;
    public $userprefs3;
    public $phtyp;
    protected $pg;
    protected $id;
    protected $largpage;
    protected $idpg;
    
    public function __construct() {
        global $nom, $menuico, $page, $lang, $lng, $charset, $serviz;
        $topmess        = getLabel($nom[$lng == $lang[0] ? 2 : 12]);
        $this->topmess  = $page[9] == '' ? $topmess : '<i class="far fa-images float-right"></i>'.$topmess;
        $this->tconfig  = $menuico[14] == 'ALL' ? ($serviz[157] != 0 ? $serviz[157] : import('tconfig', '', true, 0)) : $menuico[14];
        $this->widepage = $serviz[156];
        $this->pg       = import('pg');
        $this->id       = import('id', '', true, 1);
        $this->largpage = import('largpage', '', true, 'N');
        $this->idpg     = import('idpg', '', true, 1);
    }
    
    public function PH_ok($pseudo, $lng) {
        global $site, $urlrw, $serviz, $web, $z2, $members;
        $href1 = CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[3].'-0-'.$this->tconfig.$z2 
          : 'connect.php?lng='.$lng.'&tconfig='.$this->tconfig.$z2);
        $href2 = CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[15].'-5-new-'.$this->tconfig.$z2 
          : $site['USR'].'.php?lng='.$lng.'&uuser=new&tconfig='.$this->tconfig.$z2);
        $out = '';
        if ($serviz[9] != 'on') {
            $out = '
    <div class="text-center web342">'.$web[143];
            $href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng);
            $out .= '
      <p class="text-center">
        '.SubmitButton($web[135], 'self.location.href=\''.$href1.'\'').'
      </p>
    </div>';
            return array($this->phtyp, $out);
        }
        elseif (!isAccessGranted($members[2])) {
            $out = '
	<div class="web342 text-center">'.$web[342];
            if ($members[19] != '0' && $members[19] != '3') {
                $out .= '
      <p class="text-center">
        '.SubmitButton($web[630], 'self.location.href=\''.$href1.'\'').
          (empty($pseudo) ? '&nbsp;&nbsp;'.SubmitButton($web[160], 'self.location.href=\''.$href2.'\'') : '').'
      </p>';
            }
            $out .= '
	</div>';
            return array($this->phtyp, $out);
        }
        elseif (!empty($this->pg)) {
            $dbwork = SelectDbFieldsByField(ReadDBFields(DBPHOTO), 4, $this->pg);
			if (!empty($dbwork))
			{
				if (!isAccessGranted($dbwork[0][6]) || !isAuthorizedPublication($dbwork[0][5])) {
					$out = '
		<div class="web342 text-center">'.$web[342];
					if ($members[19] != '0' && $members[19] != '3') {
						$out .= '
		  <p class="text-center">
			'.SubmitButton($web[630], 'self.location.href=\''.$href1.'\'').
			  (empty($pseudo) ? '&nbsp;&nbsp;'.SubmitButton($web[160], 'self.location.href=\''.$href2.'\'') : '').'
		  </p>';
					}
					$out .= '
		</div>';
					return array($this->phtyp, $out);
				}
			}
            if (empty($dbwork) || !is_file(PHOTOREP.'gal_'.$this->pg.'/params_'.$this->pg.INCEXT)) {
                unset($dbwork);
                if ($site[10] != '') return array('h1', '');
                else return array('h2', '');
            }
        }
        return array('');
    }
    
    public function PH_content() {
        global $lang, $lng, $web, $serviz, $urlrw, $site, $prt, $z2;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, 
               $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
               $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        $out = '';
// LOg 
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d');
$nomficlog = CHEMIN.'data/log/photorama'.'_'.$dateheureminiutesecl.'.log';
// fin de log
        if (!empty($this->pg)) {
            $dbwork = SelectDbFieldsByField(ReadDBFields(DBPHOTO), 4, $this->pg);
			if (file_exists(PHOTOREP.'gal_'.$this->pg.'/params_'.$this->pg.INCEXT))
            include PHOTOREP.'gal_'.$this->pg.'/params_'.$this->pg.INCEXT;
        } else {
            $dbwork  = array();
            $dbwork2 = ReadDBFields(DBPHOTO);
            $dbwork1 = array_filter($dbwork2, 'IsNotEmptyTitle');
            foreach ($dbwork1 as $db) {
                $param10 = '';
				if (file_exists(PHOTOREP.'gal_'.$db[4].'/params_'.$db[4].INCEXT))
				{
					include PHOTOREP.'gal_'.$db[4].'/params_'.$db[4].INCEXT;
					if (isAccessGranted($db[6]) && isAuthorizedPublication($db[5]) && empty($param10)) {
						if (!isset($db[9])) $db[9] = '0099';
						$dbwork[] = $db;
					}
				}
            }
            unset($dbwork1, $dbwork2);
        }
		if (!isset($serviz[182])) $serviz[182] = 0;
        if ($serviz[182] == 0) sort($dbwork);
		if ($serviz[182] == 1)
			array_multisort(array_column($dbwork, 0), SORT_ASC, array_column($dbwork, 4), SORT_DESC, $dbwork);
		if ($serviz[182] == 2)
			array_multisort(array_column($dbwork, 4), SORT_DESC, array_column($dbwork, 0), SORT_ASC, $dbwork);			
        if (empty($this->id)) $this->id = 1;
        if (!empty($dbwork)) {
            $typeRubrique = TYP_PHOTO;
            $imgOpen      = "far fa-plus-square";
            $imgClose     = "far fa-minus-square";
            $subImgOpen   = "far fa-caret-square-down";
            $subImgClose  = "far fa-caret-square-up";
            $mawmenu      = (count($dbwork)-($serviz[87]*($this->id-1))>$serviz[87]) ? $serviz[87] : count($dbwork)-($serviz[87]*($this->id-1)) ;
            $out .= BeginJavascript().'
var maxsub = '.$mawmenu.';'.EndJavascript();
            $rubr  = '';
            $l     = 0;
            $isNew = false;
            for ($i = $serviz[87]*($this->id-1); $i < Min($serviz[87]*$this->id, count($dbwork)); $i++) {
                ReadDoc($dbwork[$i][4]);

                $dirgal = 'gal_'.$fileid;
                $dirmin = $dirgal.'/vgnt';
                $img = getNewUpdateDoc($creadate, $moddate, $serviz[142]);
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
                if (!is_file(PHOTOREP.$dirgal.'/params_'.substr($dirgal, 4).INCEXT)) {
                    $txt2 = '
  <p class="text-center"><br>'.$web[680].'<br /><br /></p>';
                } else {
                    $tmpi = count(SelectDBFieldsByField(ReadDBFields(PHOTOREP.$dirgal.'/'.$dirgal.DBEXT), 1, 'a'));
					if (file_exists(include PHOTOREP.$dirgal.'/params_'.substr($dirgal, 4).INCEXT))
                    include PHOTOREP.$dirgal.'/params_'.substr($dirgal, 4).INCEXT;
                }
                if ($rubr != $txt3) {
                    $rubr = $txt3;
                    if ($l > 0) $out .= '
</div>';
                    if (trim($rubr) != "") {
                        $l++;
                        $out .= '
<div id="titreRubr'.$typeRubrique.$i.'" class="phrubr font-weight-bold" 
    onclick="MontreCacheItems(\'imgOpen'.$typeRubrique.$i.'\',\'imgClose'.$typeRubrique.$i.'\',
      \'itemsRubr'.$typeRubrique.$i.'\',\'itemsRubrSelect\');">
  <i id="imgOpen'.$typeRubrique.$i.'" class="'.$imgOpen.'" title="'.$web[429].'" style="display: none;"></i>
  <i id="imgClose'.$typeRubrique.$i.'" class="'.$imgClose.'" title="'.$web[57].'" style="display: inline;"></i>
  &nbsp;'.$rubr.'
</div>';
                        if (count($dbwork) == 1) $out .= '
<div id="itemsRubrSelect" style="display: block;">';
                        else $out .= '
<div id="itemsRubr'.$typeRubrique.$i.'" style="display: block;">';
                    }
                }
                $out .= '
  <div id="titreSubRubr'.$typeRubrique.$i.'" class="phrubr2 font-weight-bold" 
      onclick="MontreCacheItems(\'subImgOpen'.$typeRubrique.$i.'\',\'subImgClose'.$typeRubrique.$i.'\',
        \'itemSubRubr'.$typeRubrique.$i.'\',\'itemSubRubrSelect\');">'."\n".'
	<div class="phitem2">
      <i id="subImgOpen'.$typeRubrique.$i.'" class="'.$subImgOpen.'" title="'.$web[429].'" style="display: none;"></i>
      <i id="subImgClose'.$typeRubrique.$i.'" class="'.$subImgClose.'" title="'.$web[57].'" style="display: inline;"></i>
	</div>
	<div class="phsubrubr">
      <a class="pl-3" id="subRubr'.$typeRubrique.$i.'">'.$txt1.'</a>'.(@$param11 != 'on' ? '<span class="phcpt">('.$tmpi.' photos)</span>' : '').'
    </div>
  </div>';
                $out .= '
  <div class="clearfix"></div>';
                if (count($dbwork) == 1) $out .= '
  <div class="bord2" id="itemSubRubrSelect" style="display: block;">';
                else $out .= '
  <div class="bord2" id="itemSubRubr'.$typeRubrique.$i.'" style="display: block;">';
                if (!empty($txt2)) $out .= '
  <div class="phcomment">'.$txt2.'</div>';
                $out .= '
  <div class="phimgs">';
                if (file_exists(PHOTOREP.$dirgal.'/params_'.substr($dirgal, 4).INCEXT)) {
                    if ($tmpi > 0) {
                        $dirgal  = 'gal_'.$fileid;
                        $dirmin  = $dirgal.'/vgnt';
						$dbphoto = ReadDBFields(PHOTOREP.$dirgal.'/'.$dirgal.DBEXT);
                        $tmpi    = 0;
                        $tmp[0]  = array();
                        $tmp[1]  = array();
                        $tmp[2]  = array();
                        $tmp[3]  = array();
                        $tmp[4]  = array();
                        $tmp[5]  = array();
                        $tmp[6]  = array();
						$tmp[9]  = array();
                        for ($ii = 0; $ii < count($dbphoto); $ii++) {
                            if ($dbphoto[$ii][1] == 'a') {
                                $tmp[0][$tmpi] = $dbphoto[$ii][0];
                                $tmp[1][$tmpi] = $dbphoto[$ii][1];
                                $tmp[2][$tmpi] = $dbphoto[$ii][2];
                                $tmp[3][$tmpi] = $dbphoto[$ii][3];
                                $tmp[4][$tmpi] = $dbphoto[$ii][4];
                                $tmp[5][$tmpi] = $dbphoto[$ii][5];
                                $tmp[6][$tmpi] = $dbphoto[$ii][6];
								if (!isset($dbphoto[$ii][9])) 
									$tmp[9][$tmpi] = '';
								else
									$tmp[9][$tmpi] = $dbphoto[$ii][9];
                                $tmpi++;
                            }
                        }
                        unset($dbphoto);
//						file_put_contents($nomficlog, date('Y-m-d H:i:s').'tri 2 174: '.$serviz[174]."\n", FILE_APPEND | LOCK_EX);
						if ($serviz[174] == 0) // nom ascendant
							array_multisort($tmp[2], SORT_ASC, $tmp[0], $tmp[1], $tmp[3], $tmp[4], $tmp[5], $tmp[6]);	
						if ($serviz[174] == 1) //tri no ordre asc et nom ascendant
							array_multisort($tmp[9], SORT_ASC, $tmp[2] , SORT_ASC, $tmp[0], $tmp[1], $tmp[3], $tmp[4], $tmp[5], $tmp[6]);							
                        $countgal = UpdateDocCounter($dbwork[$i][4]);
                        $txtcount = $web[685].'<b>'.$countgal.' '.($countgal < 2 ? $web[188] : $web[189]).'</b>';
                        $out .= '
    <div class="phimgs2 '.($param8 == 'L' ? 'text-left' : ($param8 == 'C' ? 'text-center' : 'text-right')).'">';
                        $nbmin = min($param1, $tmpi);
                        $nbpl  = min($param6, $nbmin);
                        $nb    = 0;
                        for ($ii = 0; $ii < $nbmin; $ii++) {
                            $filemin = PHOTOREP.$dirmin.'/'.$tmp[2][$ii];
                            $fileimg = PHOTOREP.$dirgal.'/'.$tmp[2][$ii];
                            $comment = stripslashes($tmp[$lng == $lang[0] ? 5 : 6][$ii]);
                            switch ($param4) {
                            case 'lightbox' :
                                $out .= '
	  <a href="'.$fileimg.'" class="lightshow" title="'.$comment.'" data-lightbox="'.$fileid.'" rel="lightbox[plants]">';
                                break;
                            case 'lytebox' :
                                $out .= '
	  <a href="'.$fileimg.'" class="lyteshow" data-lyte-options="slide:true group:'.$fileid.' slideInterval:'.((int)$param5*1000).'" 
        data-title="'.$comment.'" rel="lyteshow[diapo'.$fileid.']">';
                                break;
                            }
							if (empty($param7)) $param7 = 1;
                            $out .= '
<style>
.phimg'.$fileid.$ii.' {
    width: '.$tmp[3][$ii].'px;
    height: '.$tmp[4][$ii].'px;
    margin: '.intval($param7/2).'px;
}
</style>
        <img class="phimg phimg'.$fileid.$ii.'" src="'.$filemin.'" alt="" title="" />
	  </a>';
                            $nb++;
                            if ($nb == $nbpl) {
                                $out .= '<br />';
                                $nb = 0;
                            }
                        }
                        if ($nbmin < $tmpi) $out .= '
      <strong>...</strong>';
                        for ($ii = $nbmin; $ii < $tmpi; $ii++) {
                            $filemin = PHOTOREP.$dirmin.'/'.$tmp[2][$ii];
                            $fileimg = PHOTOREP.$dirgal.'/'.$tmp[2][$ii];
                            $comment = stripslashes($tmp[$lng == $lang[0] ? 5 : 6][$ii]);
                            $out .= '
      <span class="d-none">';							
                            switch ($param4) {
                            case 'lightbox' :
                                $out .= '
	    <a href="'.$fileimg.'" title="'.$comment.'" data-lightbox="'.$fileid.'" rel="lightbox[plants]">';
                                break;
                            case 'lytebox' :
                                $out .= '
	    <a href="'.$fileimg.'" class="lyteshow" data-lyte-options="slide:true group:'.$fileid.' slideInterval:'.((int)$param5*1000).'" 
          data-title="'.$comment.'" rel="lyteshow[diapo'.$fileid.']">';
                                break;
                            }
                            $out .= '
<style>
.phimg2'.$fileid.$ii.' {
    width: '.$tmp[3][$ii].'px;
    height: '.$tmp[4][$ii].'px;
}
</style>
          <img src="'.$filemin.'"  class="phimg2'.$fileid.$ii.'" alt="" title="" />
	    </a>
	  </span>';
                        }
                        $out .= '
    </div>';
                        if (@$param12 != 'on') $out .= '
    <div class="galcounter text-right">('.$txtcount.')</div>';
                        $out .= '
    <div class="clearfix"></div>';
                    }
                    $out .= displayQuickConfig(NULL, $dbwork[$i][4], 'photo&amp;form=2&amp;id='.$dbwork[$i][4], 
                      'photo&amp;act=i&amp;id='.$dbwork[$i][4], 'photo&amp;del='.$dbwork[$i][4], 18);
                    $out .= '
  </div>
</div>';
                }

		} // fin for
            if ($l > 0) $out .= '
</div>';
            if ('on' != $serviz[81] && !$isNew && empty($prt)) {
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
            if ('on' != $serviz[81] && empty($prt)) {
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
        }
        elseif (!empty($this->pg)) $out .= '
<p>'.$web[36].'</p>';
        $out .= '
<div class="phrubr2 font-weight-bold">
  '.GetNavBar(($site['URLR'] == 'on' 
    ? $lng.'-'.$urlrw[9].'-5-'.$this->tconfig.'-' 
    : 'photorama.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.'&amp;id='), 
    count($dbwork), $this->id, $serviz[87], $z2).'
</div>';
        return array($this->phtyp, $out);
    }
    
    public function PH2_content() {
        global $lang, $lng, $web, $serviz, $urlrw, $site, $prt, $z2, $members, $nom, $page;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, 
               $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
               $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        $out = '';
// LOg 
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d');
$nomficlog = CHEMIN.'data/log/photorama ph2_content'.'_'.$dateheureminiutesecl.'.log';
// file_put_contents($nomficlog, date('Y-m-d H:i:s').' debut: '."\n", FILE_APPEND | LOCK_EX);
// fin de log

        if (!empty($this->pg)) {
            $dbwork = SelectDbFieldsByField(ReadDBFields(DBPHOTO), 4, $this->pg);
            if (!empty($dbwork)) {
                ReadDoc($dbwork[0][4]);
                $img = getNewUpdateDoc($creadate, $moddate, $serviz[142]);
                if ($lng == $lang[0]) {
                    $txt1 = $img.$fieldb1;
                    $txt2 = $fieldc1;
                    $txt3 = $fielda1;
                } 
                else {
                    $txt1 = $fieldb2;
                    $txt2 = $fieldc2;
                    $txt3 = $fielda2;
                }
                $dirgal  = 'gal_'.$fileid;
                $dirmin  = $dirgal.'/vgnt';
                $dbphoto = ReadDBFields(PHOTOREP.$dirgal.'/'.$dirgal.DBEXT);
                $tmpi    = 0;
                $tmp[0]  = array();
                $tmp[1]  = array();
                $tmp[2]  = array();
                $tmp[3]  = array();
                $tmp[4]  = array();
                $tmp[5]  = array();
				$tmp[9]  = array();
				for ($ii = 0; $ii < count($dbphoto); $ii++) {
					if ($dbphoto[$ii][1] == 'a') {
						$tmp[0][$tmpi] = $dbphoto[$ii][0];
						$tmp[1][$tmpi] = $dbphoto[$ii][1];
						$tmp[2][$tmpi] = $dbphoto[$ii][2];
						$tmp[3][$tmpi] = $dbphoto[$ii][3];
						$tmp[4][$tmpi] = $dbphoto[$ii][4];
						$tmp[5][$tmpi] = $dbphoto[$ii][5];
						$tmp[6][$tmpi] = $dbphoto[$ii][6];
						if (!isset($dbphoto[$ii][9])) 
							$tmp[9][$tmpi] = '';
						else
							$tmp[9][$tmpi] = $dbphoto[$ii][9];
						$tmpi++;
					}
				}
				unset($dbphoto);
//				file_put_contents($nomficlog, date('Y-m-d H:i:s').'tri 1er 174: '.$serviz[174]."\n", FILE_APPEND | LOCK_EX);
				if ($serviz[174] == 0) // nom ascendant
					array_multisort($tmp[2], SORT_ASC, $tmp[0], $tmp[1], $tmp[3], $tmp[4], $tmp[5], $tmp[6]);	
				if ($serviz[174] == 1) //tri no ordre asc et nom ascendant
					array_multisort($tmp[9], SORT_ASC, $tmp[2] , SORT_ASC, $tmp[0], $tmp[1], $tmp[3], $tmp[4], $tmp[5], $tmp[6]);		
                if (file_exists(PHOTOREP.$dirgal.'/params_'.substr($dirgal, 4).INCEXT))
                    include PHOTOREP.$dirgal.'/params_'.substr($dirgal, 4).INCEXT;
            }
            $this->largpage = empty($this->largpage) ? 'N' : $this->largpage;
            switch($this->largpage) {
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
                break;
            }
            $topmess       = getLabel($nom[$lng == $lang[0] ? 2 : 12]).(empty($txt3) ? '' : ' - '.$txt3.' : ').$txt1;
            $this->topmess = $page[9] == '' ? $topmess : '<i class="far fa-images float-right"></i>'.$topmess;
            $errpg         = false;
            if (!is_file(PHOTOREP.$dirgal.'/params_'.substr($dirgal, 4).INCEXT)) $txt2 = '
<p class="text-center"><br>'.$web[680].'<br /><br /></p>';
            if ($param4 == 'unitegallery') {
                $ugfile = PHOTOREP.$dirgal.'/'.$lng.'-th'.$param13.'_'.$this->pg.INCEXT;
                if (is_file($ugfile)) {
                    if (!empty($txt2)) $out .= '
<div class="phcomment">'.$txt2.'</div>';
                    $out .= file_get_contents($ugfile);
                    $countgal = UpdateDocCounter($dbwork[0][4]);
                    $txtcount = $web[685].'<b>'.$countgal.' '.($countgal < 2 ? $web[188] : $web[189]).'</b>';
                    if ($param12 != 'on') $out .= '
<div class="galcounter text-right">('.$txtcount.')</div>';
                } 
                else
				{
                    $errpg = true;				
				}
            } 
            else {
                if (!empty($dbwork)) {
					if (!isset($param11)) $param11 = '';
                    $out .= '
<div class="phrubr2 font-weight-bold phsubrubr">'.$txt1.($param11 != 'on' ? '<span class="phcpt">('.$tmpi.' photos)</span>' : '').'</div>';
                    if (!empty($txt2)) $out .= '
<div class="phcomment">'.$txt2.'</div>';
                    $fileid = substr($dirgal, 4);
                    $out .= '
<div class="phimgs">';
                    if ($tmpi > 0 && is_file(PHOTOREP.$dirgal.'/params_'.$fileid.INCEXT)) {
                        $out .= '
  <div class="phimgs2 w-100 m-auto '.($param8 == 'L' ? 'text-left' : ($param8 == 'C' ? 'text-center' : 'text-right')).'">';
                        $nbmin = min($param1, $tmpi);
                        $nbpl  = min($param6, $nbmin);
                        $nb    = 0;
                        if (empty($this->idpg)) $this->idpg = 1;
                        else while ($tmpi <= ($this->idpg - 1)* $nbmin) $this->idpg--;
                        $navbar = GetNavBar(($site['URLR'] == 'on' 
                          ? $lng.'-'.$urlrw[9].'-3-'.$this->pg.'-'.$this->largpage.'-'.$this->tconfig.'-' 
                          : 'photorama2.php?lng='.$lng.'&amp;pg='.$this->pg.'&amp;largpage='.$this->largpage.'&amp;tconfig='.$this->tconfig.'&amp;idpg='), 
                          $tmpi, $this->idpg, $nbmin, $z2);
                        for ($ii = 0; $ii < max($nbmin*($this->idpg-1), 0); $ii++) {
                            $filemin = PHOTOREP.$dirmin.'/'.$tmp[2][$ii];
                            $fileimg = PHOTOREP.$dirgal.'/'.$tmp[2][$ii];
                            $comment = stripslashes($tmp[$lng == $lang[0] ? 5 : 6][$ii]);
                            $out .= '
    <span class="d-none">';							
                            switch ($param4) {
                            case 'lightbox' :
                                $out .= '
      <a href="'.$fileimg.'" class="lightshow" title="'.$comment.'" data-lightbox="'.$dbwork[0][4].'" rel="lightbox[plants]">';
                                break;
                            case 'lytebox' :
                                $out .= '
	  <a href="'.$fileimg.'" class="lyteshow" data-lyte-options="slide:true group:'.$dbwork[0][4].' slideInterval:'.($param5*1000).'" 
        data-title="'.$comment.'" rel="lyteshow[diapo'.$dbwork[0][4].']">';
                                break;
                            }
                            $out .= '
<style>
.phimg2'.$fileid.$ii.' {
    width: '.$tmp[3][$ii].'px;
    height: '.$tmp[4][$ii].'px;
}
</style>
        <img src="'.$filemin.'"  class="phimg2'.$fileid.$ii.'" alt="" title="" />
      </a>
	</span>';
                        }
                        $countgal = UpdateDocCounter($dbwork[0][4]);
                        $txtcount = $web[685].'<b>'.$countgal.' '.($countgal < 2 ? $web[188] : $web[189]).'</b>';;
                        for ($ii = $nbmin*($this->idpg-1); $ii < Min($nbmin*$this->idpg, $tmpi); $ii++) {
                            $filemin = PHOTOREP.$dirmin.'/'.$tmp[2][$ii];
                            $fileimg = PHOTOREP.$dirgal.'/'.$tmp[2][$ii];
                            $comment = stripslashes($tmp[$lng == $lang[0] ? 5 : 6][$ii]);
                            switch ($param4) {
                            case 'lightbox' :
                                $out .= '
	  <a href="'.$fileimg.'" class="lightshow" title="'.$comment.'" data-lightbox="'.$dbwork[0][4].'" rel="lightbox[plants]">';
                                break;
                            case 'lytebox' :
                                $out .= '
	  <a href="'.$fileimg.'" class="lyteshow" data-lyte-options="slide:true group:'.$dbwork[0][4].' slideInterval:'.((int)$param5*1000).'" data-title="'.$comment.'" rel="lyteshow[diapo'.$dbwork[0][4].']">';
                                break;
                            }
                            $out .= '
<style>
.phimg'.$fileid.$ii.' {
    width: '.$tmp[3][$ii].'px;
    height: '.$tmp[4][$ii].'px;
    margin: '.intval($param7/2).'px;
}
</style>
        <img src="'.$filemin.'" class="phimg phimg'.$fileid.$ii.'" alt="" title="" />
	  </a>';
                            $nb++;
                            if ($nb == $nbpl) {
                                $out .= '<br />';
                                $nb = 0;
                            }
                        }
                        for ($ii = Min($nbmin*$this->idpg, $tmpi); $ii < $tmpi; $ii++) {
                            $filemin = PHOTOREP.$dirmin.'/'.$tmp[2][$ii];
                            $fileimg = PHOTOREP.$dirgal.'/'.$tmp[2][$ii];
                            $comment = stripslashes($tmp[$lng == $lang[0] ? 5 : 6][$ii]);
                            $out .= '
    <span class="d-none">';							
                            switch ($param4) {
                            case 'lightbox' :
                                $out .= '
	  <a href="'.$fileimg.'" class="lightshow" title="'.$comment.'" data-lightbox="'.$dbwork[0][4].'" rel="lightbox[plants]">';
                                break;
                            case 'lytebox' :
                                $out .= '
	  <a href="'.$fileimg.'" class="lyteshow" data-lyte-options="slide:true group:'.$dbwork[0][4].' slideInterval:'.((int)$param5*1000).'" 
        data-title="'.$comment.'" rel="lyteshow[diapo'.$dbwork[0][4].']">';
                                break;
                            }
                            $out .= '
<style>
.phimg3'.$fileid.$ii.' {
    width: '.$tmp[3][$ii].'px;
    height: '.$tmp[4][$ii].'px;
}
</style>
          <img src="'.$filemin.'"  class="phimg3'.$fileid.$ii.'" alt="" title="" />
	    </a>
	  </span>';
                        }
                        $out .= $navbar;
                        $out .= '
    </div>';
						if (!isset($param12)) $param12 = 'on';
                        if ($param12 != 'on') $out .= '
    <div class="galcounter text-right">('.$txtcount.')</div>';
                        $out .= '
    <div class="clearfix"></div>';
                        if (empty($prt)) $out .= 
    displayQuickConfig(NULL, false, 'photo&amp;form=2&amp;id='.$dbwork[0][4], 
      'photo&amp;act=i&amp;id='.$dbwork[0][4], 'photo&amp;del='.$dbwork[0][4], 18);
                    }
                    unset($tmp[0],$tmp[1],$tmp[2],$tmp[3],$tmp[4],$tmp[5],$tmp[6]);
                    $out .= '
  </div>';
                }
                else
                    $errpg = true;
            }
            if (!empty($this->pg) && $errpg) $out .= '
<p>'.$web[36].'</p>';
        }
        return array($this->phtyp, $out);
    }
}
?>
