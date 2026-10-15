<?php
/*******************************************************************************
 *   Newsletter
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

if (stristr($_SERVER['SCRIPT_NAME'], 'download.inc')) {
    header('location:../index.php');
    die();
}
#[AllowDynamicProperties]
class GY_newsletter {
    public $tconfig;
    public $topmess;
    protected $pg;
    protected $rgpd;
    protected $nlgrp;
    protected $nlpseudo;
    protected $nlmail;
    protected $action;
    
    public function __construct() {
        global $lng, $web;
        $this->topmess  = $web[222];
        $this->tconfig  = import('tconfig', '', true, 0);
        $this->pg       = import('pg');
        $this->rgpd     = import('rgpd');
        $this->nlgrp    = import('nlgrp', 'GET', true, null);
        $this->nlpseudo = import('nlpseudo', 'GET', true, null);
        $this->action   = import('action');
        $this->nlmail   = import('nlmail');
    }

    public function NWL_ok($pseudo, $lng) {
        global $site, $urlrw, $serviz, $web, $z2, $members;
        $out = '';
        if ($serviz[36] != 'on') {
            $out = '
    <div class="text-center web342">'.$web[143];
            $href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng);
            $out .= '
      <p class="text-center">
        '.SubmitButton($web[135], 'self.location.href=\''.$href1.'\'').'
      </p>
    </div>';
            return array('DWNL', $out);
        }
        return array('');
    }
    
    private function isValidEmail($email, $verify, $return_errors=true) {
        global $web, $charset;
        $error = '';
		$out = '';
        if (preg_match('/^([a-zA-Z0-9\._\+-]+)\@((\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,7}|[0-9]{1,3})(\]?))$/', $email, $matches)) {
            @$user   = $matches[1];
            $domain = $matches[2];
            if ($verify == "on") {
                $out  .= sprintf($web[502], $domain);
                $amxrr = array();
                $mxrr  = getmxrr($domain, $amxrr);
                if (count($amxrr) <= 0) {
                    $error = $web[503];
                }
            }
        } else {
            $error = $web[504];
        }
        if ($return_errors) {
            if(isset($error)) {
                return htmlentities($error, NULL, $charset);
            } else {
                return TRUE;
            }
        }
    }

    private function CheckEqualityDB1Field($fic, $submit, $FieldNB) {
        if (FileDBExist($fic)) {
            $DataDB = ReadDBFields($fic);
            for ($i = 0; $i < count($DataDB); $i++) {
                if (isset($DataDB[$i][$FieldNB]) && strtolower($DataDB[$i][$FieldNB]) == strtolower($submit)) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    
    public function NWL_content() {
        global $lang, $lng, $web, $nom, $serviz, $urlrw, $site, $userprefs, $prt, $z2, $charset, $supervision;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, 
               $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
               $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
		$out = '';
        $crypt     = isset($_POST['crypt']) ? strip_tags($_POST['crypt']) : NULL;
        $cryptok   = (!$crypt) ? false : chk_crypt($crypt);
        $nlsecure  = import('nlsecure');
        $nlconfirm = import('nlconfirm');
        $ctrl_spam = import('code_pseudo', 'POST', true, '');
        if (empty($this->action) && !empty($this->pg) && !is_null($this->nlgrp) && !is_null($this->nlpseudo)) {
            // Affichage d'une lettre d'information
            $abonnes = ReadDBFields(MEMBERSNWL);
            if ($this->nlgrp != 'all_subscribers') {
                $ok = false;
                foreach ($abonnes as $abonne)
                    if ($abonne[0] == $this->nlpseudo) {
                        $ok = $this->nlgrp == $abonne[1];
                        break;
                    }
            } 
            else
                $ok = true;
            if ($ok && (1 == count(SelectDBFields(TYP_NWL, 'a', $this->pg)) || 1 == count(SelectDBFields(TYP_NEWS, 'a', $this->pg)))) {
                ReadDoc($this->pg);
                $this->topmess = $lng == $lang[0] ? getLabel($nom[38]).' - '.$fieldb1 : getLabel($nom[39]).' - '.$fieldb2;
                $txtnwl        = $lng == $lang[0] ? $fieldc1 : $fieldc2;
            } else {
                $this->topmess = getLabel($nom[$lng == $lang[0] ? 38 : 39]);
                if ($site[10] != '') {
                    $txtnwl = BeginJavascript().'window.location="'.$site[3].'error.php?lng='.$lng.'&err=404"'.EndJavascript();
                }
                else 
                    $txtnwl = $web[36];
            }
            $this->topmess = mb_substr($this->topmess, 0, 1, $charset) == '§' 
              ? mb_substr($this->topmess, 1, mb_strlen($this->topmess ,$charset) ,$charset) 
              : $this->topmess;
            $out .= '
        <p>
        '.$txtnwl.'
        </p>';
        }
        elseif (empty($ctrl_spam)) {
			$error = '';
            // Abonnement / confirmation / désabonnement
            $this->nlpseudo = import('nlpseudo');
			if (strlen($this->nlpseudo) == 0)
				$error .= '<p class="text-justify"> - erreur '.$web[252].'</p>';
            $this->nlpseudo = KeepGoodChars(CutLongWord(strip_tags(stripslashes($this->nlpseudo)),20));
            $this->topmess  = $web[222];
            if (!empty($userprefs[1]) && $this->rgpd == 'on') setRGPDdate();
			if (strlen($this->action) == 0) $this->action = "noconfsub"; 
            switch ($this->action) {
			case "noconfsub" :
					 $out .= '<p class="text-justify"><b>'.$web[256].'</b></p><br />';
				break;
            case 'sub' :
                $out .= '<p class="text-left">'.sprintf($web[502], $this->nlmail).'<br /></p>';
                $error = '';
				if (strlen($this->nlpseudo) == 0 ){
                    $error     .= '<p class="text-justify"> - '.$web[252].'</p>';
                    $nlconfirm = 'on';
                }
                if(($this->nlpseudo == "") || ($this->nlpseudo == KeepGoodChars($web[259]))) {
                    $error .= '<p class="text-justify"> - '.$web[252].'</p>';
                }
				if (strlen($this->nlmail == 0)) {
                    $error .= '<p class="text-justify"> - '.$web[251].'</p>';
				}
                if ((!preg_match("!^(.+)@(.+)\\.(.+)$!",$this->nlmail)) || ($this->nlmail == "") || ($this->nlmail == $web[260])) {
                    $error .= '<p class="text-justify"> - '.$web[251].'</p>';
                }
                if (($serviz[100] != '0' && $serviz[93] == 'on' && ($userprefs[1] == '' || $serviz[98] == '')) && !$cryptok) {
                    $error .= '<p class="text-justify"> - '.$web[529].'</p>';
                }
                if ($error == "") {
                    $controle = $this->CheckEqualityDB1Field(NEWSLET_LIST, $this->nlpseudo,0);
                    if ($controle) {
                        $error = $web[392];
                    }
                    else {
                        $controle = $this->CheckEqualityDB1Field(NEWSLET_LIST, $this->nlmail,1);
                        if ($controle) $error = $web[250];
                    }
                    if (FileDBExist(NEWSLET_WAIT)) {
                        $tmplist = ReadDBFields(NEWSLET_WAIT);
                        for ($k = count($tmplist)-1; $k >= 0; $k--) {
                            if ($tmplist[$k][0] !="" && $tmplist[$k][0] != $this->nlpseudo && $tmplist[$k][3] < date("YmdHi")) {
                                DeleteDBFieldById(NEWSLET_WAIT, $id=$k);
                            }
                        }
                        unset($tmplist);
                        $tmplist = ReadDBFields(NEWSLET_WAIT);
                        for ($k = 0; $k < count($tmplist); $k++) {
                            if ($tmplist[$k][0] == $this->nlpseudo) {
                                unset($tmplist);
                                $out = '
<script>
alert("'.addslashes($web[433]).'");
document.location.href="'.($site['URLR'] == 'on'? $lng.'-'.$urlrw[0]: 'index.php?lng='.$lng).'";
</script>';
                                return array('NWL', $out);
                            }
                        }
                        unset($tmplist);
                    }
                    if (!$controle) {
                        $valemail = $this->IsValidEmail($this->nlmail, $serviz[36]);
                        if ($valemail) {
                            $controle = false;
                            $error    = $valemail;
                        }
                    }
                    if (!$controle && !$valemail) {
                        $nllist    = array();
                        $nllist[0] = $this->nlpseudo;
                        $nllist[1] = $this->nlmail;
                        $nllist[2] = $lng;
                        $nllist[3] = date("YmdHi",time()+900); //délai 15 mn
                        AppendDBFields(NEWSLET_WAIT, $nllist);
                        $out  .= '<p>'.$web[245].'<b> '.$this->nlpseudo.'</b> '.$web[431].
                          stripslashes($site[($lang[0] == $lng ? 0 : 11)]).'<br />'.$web[433].'</p>';
                        $to    = $this->nlmail;
                        $sujet = stripslashes($site[($lang[0] == $lng ? 0 : 11)])." - ".$web[246];
                        $bodycorps  = $web[245]." ".$this->nlpseudo." ".$web[431].stripslashes($site[($lang[0] == $lng ? 0 : 11)])."<br /><br />";
                        $bodycorps .= $web[432]."<br />";
                        $url   = $site['URLR'] == 'on' 
                          ? $lng.'-'.$urlrw[18].'-1-confsub-'.$this->nlpseudo.'-'.$this->nlmail 
                          : $site['NL'].'.php?lng='.$lng.'&action=confsub&nlpseudo='.$this->nlpseudo.'&nlmail='.$this->nlmail;
                        $bodycorps .= '<a href="'.$site[3].$url.'">'.$site[3].$url.'</a>';
                        $bodycorps .= "<hr />".$web[235]."<br />".@$user[0]." <br />".$site[3];
                        eMailHtmlTo($sujet, $bodycorps, $to);
                    } 
                    else {
                        $out .= '<br /><p>'.$error.'</p><br />';
                    }
                } 
                else {
                    $out .= '<p class="text-justify"><b>'.$web[256].'</b></p><br />'.$error;
                }
                break;
            case "confsub" :
                $error        = "";
                $this->nlmail = strip_tags(stripslashes($this->nlmail));
                $out         .= '<p class="text-left">'.sprintf($web[502], $this->nlmail).'<br /></p>';
				if (strlen($this->nlpseudo) == 0 ){
                    $error     = '<p class="text-justify"> - '.$web[252].'</p>';
                    $nlconfirm = 'on';
                }
                if (($this->nlpseudo == "") || ($this->nlpseudo == KeepGoodChars($web[259]))) {
                    $error = '<p class="text-justify""> - '.$web[252].'</p>';
                }
				if (strlen($this->nlmail == 0)) {
                    $error .= '<p class="text-justify"> - '.$web[251].'</p>';
				}
                if ((!preg_match("!^(.+)@(.+)\\.(.+)$!", $this->nlmail)) || ($this->nlmail == "") || ($this->nlmail == $web[260])) {
                    $error .= '<p class="text-justify"> - '.$web[251].'</p>';
                }
                if ($error == "") {
                    $controle = $this->CheckEqualityDB1Field(NEWSLET_LIST, $this->nlpseudo,0);
                    if ($controle) {
                        $error = $web[392];
                    } 
                    else{
                        $controle = $this->CheckEqualityDB1Field(NEWSLET_LIST, $this->nlmail,1);
                        if ($controle) {
                            $error = $web[250];
                        }
                    }
                    // update tmp list
                    if (FileDBExist(NEWSLET_WAIT)) {
                        $tmplist = ReadDBFields(NEWSLET_WAIT);
                        for ($k = 0; $k < count($tmplist); $k++) {
                            if ($tmplist[$k][0] == $this->nlpseudo && $tmplist[$k][3] > date("YmdHi")) {
                                unset($tmplist);
                                DeleteDBFieldById(NEWSLET_WAIT, $id=$k);
                                break;
                            }
                        }
                    }
                    if (!$controle) {
                        $valemail = $this->IsValidEmail($this->nlmail, @$user[1], $serviz[36]);
                        if ($valemail) {
                            $controle = false;
                            $error    = $valemail;
                        }
                    }
                    if (!$controle && !$valemail) {
                        $out .= '<p>'.$web[249].'<b> '.$this->nlpseudo.'</b><br /><br />'.$web[50].'<b> '.$this->nlmail.'</b> '.$web[248].'</p><br />';
                        $nllist    = array();
                        $nllist[0] = $this->nlpseudo;
                        $nllist[1] = $this->nlmail;
                        $nllist[2] = $lng;
                        AppendDBFields(NEWSLET_LIST, $nllist);
                        $to    = $this->nlmail;
                        $sujet = stripslashes($site[($lang[0] == $lng ? 0 : 11)])." - ".$web[246];
                        $bodycorps  = $web[245]." ".$this->nlpseudo.$web[244]." ".stripslashes($site[($lang[0] == $lng ? 0 : 11)])."<br /><br />";
                        $bodycorps .= $web[235]."<br />".@$user[0]."<br />".$site[3]."<hr />";
                        $bodycorps .= $web[243]."<br />";
                        $url   = $site['URLR'] == 'on' 
                          ? $lng.'-'.$urlrw[18].'-1-unsub-'.$this->nlpseudo.'-'.$this->nlmail 
                          : $site['NL'].'.php?lng='.$lng.'&action=unsub&nlpseudo='.$this->nlpseudo.'&nlmail='.$this->nlmail;
                        $bodycorps .= '<a href="'.$site[3].$url.'">'.$site[3].$url.'</a>';
                        eMailHtmlTo($sujet, $bodycorps, $to);
                        if ($supervision[7] == "on") {
                            $bodycorps  = $this->nlpseudo.' ('.$this->nlmail.') '.$web[242].' '.stripslashes($site[($lang[0] == $lng ? 0 : 11)])."<hr />";
                            $bodycorps .= $web[241].":<br />";
                            $bodycorps .= '<a href="'.$site[3].$url.'">'.$site[3].$url.'</a>';
                            eMailHtmlTo($sujet, $bodycorps);
                        }
                    } 
                    else {
                        $out .= '<br /><p>'.$error.'</p><br />';
                    }
                } 
                else {
                    $out .= '<p class="text-justify"><b>'.$web[256].'</b></p><br />'.$error;
                }
                break;
            case "unsub" :
                $error = "";
				if (strlen($this->nlpseudo) == 0 ){
                    $error     = '<p class="text-justify"> - '.$web[252].'</p>';
                    $nlconfirm = 'on';
                }
                if ($nlsecure != 'on' && $nlconfirm != 'on') $nlsecure = 'on';
                if (($this->nlpseudo == '' ||  $this->nlpseudo == KeepGoodChars($web[259])) && $nlsecure != 'on') {
                    $error     = '<p class="text-justify"> - '.$web[252].'</p>';
                    $nlconfirm = 'on';
                }
				if (strlen($this->nlmail == 0)) {
                    $error .= '<p class="text-justify"> - '.$web[251].'</p>';
				}
                if ((!preg_match("!^(.+)@(.+)\\.(.+)$!",$this->nlmail) || $this->nlmail == "" || $this->nlmail == $web[260]) && $nlsecure != 'on') {
                    $error    .= '<p class="text-justify"> - '.$web[251].'</p>';
                    $nlconfirm = 'on';
                }
                if (($serviz[100] != '0' && $serviz[93] == 'on' && ($userprefs[1] == '' || $serviz[98] == '')) && !$cryptok && $nlsecure != 'on') {
                    $error    .= '<p class="text-justify"> - '.$web[529].'</p>';
                    $nlconfirm = 'on';
                } 
                else {
                    if ($nlsecure == 'on' && $nlconfirm != 'on') {
                        $out .= '
<div class="container box">
  <div class="text-center nwlunsub"><b>'.$this->nlpseudo.' => '.$this->nlmail.'</b><br /><br />'.$web[553].'</div>
  <form name="confunsub" action="'.CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[18].$z2 : $site['NL'].'.php?lng='.$lng.$z2).'" method="POST">
    <input type="hidden" name="nlsecure" value="on">
    <input type="hidden" name="nlconfirm" value="on">
    <input type="hidden" name="action" value="unsub">
    <input type="hidden" name="nlpseudo" value="'.$this->nlpseudo.'" />
    <input type="hidden" name="nlmail" value="'.$this->nlmail.'" />
    <div class="text-center">';
                if ($serviz[100] != '0' && $serviz[93] == 'on' && $serviz[98] == '') {
					$lbl  = $serviz[100] == '3' ? $web[642] : '';
                    $out .= '
      <div class="text-center nwlcrypt">
        <div class="text-center">'.dsp_crypt(0, 1, 3, 1, $lbl, $serviz[115] == 'on' ? $web[643] : '');      
                    if ($serviz[100] == '1') $out .= '<br />';
                    $out .= ($serviz[100] == '3' ? '' : $web[530]).'<br />
          <input type="text" class="inputcrypt" name="crypt" onpaste="return false;" />
        </div>
      </div><br />';
                }
                $out .= '
      <span class="nwlyesbtn">
        '.SubmitButton($web[313]).'
      </span>
      <span class="nwlnobtn">
        <button 
          type="button" 
          class="btn btn-info btn-lg" 
          title="'.$web[314].'" 
          onclick="self.location.href=\''.($site['URLR'] == 'on'? $lng.'-'.$urlrw[0]: 'index.php?lng='.$lng).'\'">'.$web[314].'
        </button>
      </span>
    </div>
  </form>
</div>';
                    }
                }
                if ($nlconfirm == 'on') {
                    if ($error == '') {
                        $controle = CheckDB2Fields(NEWSLET_LIST, $this->nlpseudo, 0, $this->nlmail,1);
                        if ($controle) {
                            $this->nlpseudo = stripslashes($this->nlpseudo);
                            $newdb          = array();
                            $dbwork         = ReadDBFields(NEWSLET_LIST);
                            for ($i = 0; $i < count($dbwork); $i++) {
                                if (isset($dbwork[$i][1]) && $dbwork[$i][1] == $this->nlmail) {
                                    if ($dbwork[$i][0] != $this->nlpseudo) {
                                        $newdb[] = $dbwork[$i];
                                    }
                                } 
                                else {
                                    $newdb[] = $dbwork[$i];
                                }
                            }
                            WriteDBFields(NEWSLET_LIST,$newdb);
                            $out .= '<p>'.$web[253].'<b> '.$this->nlpseudo.'</b><br /><br />'.$web[255].'<b> '.$this->nlmail.'</b> '.$web[254].'</p><br />';
                            if ($supervision[7] == "on") {
                                $to    = $this->nlmail;
                                $sujet = stripslashes($site[($lang[0] == $lng ? 0 : 11)])." - ".$web[241];
                                $bodycorps  = $web[240]." ".$this->nlpseudo.$web[239]." ".stripslashes($site[($lang[0] == $lng ? 0 : 11)])."<hr />";
                                $bodycorps .= $web[235]."<br />".@$user[0]."<br />".$site[3]."<hr />";
                                $bodycorps .= $web[234]."<br />";
                                $url   = $site['URLR'] == 'on'? $lng.'-'.$urlrw[18].'-1-sub-'.$this->nlpseudo.'-'.$this->nlmail: $site['NL'].'.php?lng='.$lng.'&action=sub&nlpseudo='.$this->nlpseudo.'&nlmail='.$this->nlmail;
                                $bodycorps .= '<a href="'.$site[3].$url.'">'.$site[3].$url.'</a>';
                                eMailHtmlTo($sujet, $bodycorps,$to);
                                $bodycorps = $this->nlpseudo." (".$this->nlmail.") ".$web[238]." ".stripslashes($site[($lang[0] == $lng ? 0 : 11)])."\n";
                                eMailHtmlTo($sujet, $bodycorps, '');
                            }
                        } else {
                            $out .= '<br /><p>'.$web[255].' <b>'.$this->nlmail.'</b> '.$web[233].'</p><br />';
                        }
                    } else {
                        $out .= '<p class="text-justify"><b>'.$web[256].'</b></p><br />'.$error;
                    }
                }
                break;
            }
        }
        return array('NWL', $out);
    }
}
?>