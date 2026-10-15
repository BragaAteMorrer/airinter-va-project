<?php
/*******************************************************************************
 *   Forum Topics
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
if (stristr($_SERVER['SCRIPT_NAME'], 'faq.inc')) {
    header('location:../index.php');
    die();
}
#[AllowDynamicProperties]
class GY_forum {
    public $tconfig;
    public $topmess;
    public $frtyp;
    public $nom_frth;
    protected $pg;
    protected $id;
    protected $cat;
    protected $thrd;
    protected $fid;

    public function __construct() {
        global $menuico, $nom, $lang, $lng, $page, $pg, $id;
        $frcat          = explode(',', import('cat'));
        $topmess        = getLabel($nom[$lng == $lang[0] ? 22 : 23]);
        $this->nom_frth = strip_tags($topmess);
        $this->topmess  = $page[9] == '' ? $topmess : '<i class="fab fa-forumbee float-right"></i>'.$topmess;
        $this->tconfig  = $menuico[19] == 'ALL' ? import('tconfig', '', true, 0) : $menuico[19];
        $this->pg       = $pg;
        $this->id       = import('id');
        $this->cat      = $frcat[0];
        $this->thrd     = import('thrd');
        $this->fid      = import('fid');
    }
    
   public function FR_ok($pseudo, $lng) {
        global $site, $urlrw, $serviz, $web, $z2, $members, $tconfig;
        $out = '';
        if ($serviz[13] != 'on') {
            $href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng);
            $out .= '
<div class="text-center web342">'.$web[143].'
  <p class="text-center">
    '.SubmitButton($web[135], 'self.location.href=\''.$href1.'\'').'
  </p>
</div>';
            return array($this->frtyp, $out);
        }
        elseif (!isAccessGranted($members[5])) {
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
            return array($this->frtyp, $out);
        }
        $dbcat = ReadDBFields(DBFORUMCAT);
        $okfr  = false;
		if (!empty($dbcat)) 
		{
			foreach ($dbcat as $cat) 
			{
				if (!isset($cat[5])) $cat[5] = '';
				if ($cat[5] == 'on') 
				{ 
					$okfr = true; break;
				}
			}
		}
		if (!$okfr) {
			$href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng);
			$out .= '
		<div class="text-center web342">'.$web[143].'
		<p class="text-center">
		'.SubmitButton($web[135], 'self.location.href=\''.$href1.'\'').'
		</p>
		</div>';
			return array($this->frtyp, $out);
		}
        return array('');
    }
    
    public function FR_rules() {
        global $userprefs, $web, $page, $meskin, $site, $charset, $lang, $lng;
        if (is_file(DBRULES)) @include DBRULES;
        $out = '<!DOCTYPE html">
<html>
<head>
<title>'.$web[409].'</title>
<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'">';
        if(file_exists($meskin."style.css")) $out .= '
<link rel="stylesheet" href="'.$meskin.'style.css" />';
        else $out .= '
<link rel="stylesheet" href="no_skin/style.css" />';
        if(file_exists($meskin."styleplus.css")) $out .= '
<link rel="stylesheet" href="'.$meskin.'styleplus.css" />';
        else $out .= '
<link rel="stylesheet" href="no_skin/styleplus.css" />';
        if (file_exists($meskin."bs4style.css")) $out .= '
<link rel="stylesheet" href="'.$meskin.'bs4style.css" />';
        $out .=  '
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" 
  integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css" 
  integrity="sha512-rt/SrQ4UNIaGfDyEXZtNcyWvQeOq0QLygHluFQcSjaGB04IxWhal71tKuzP6K8eYXYB6vJV4pHkXcmFGGQ1/0w==" crossorigin="anonymous" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" 
  integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.bundle.min.js" 
	integrity="sha512-igl8WEUuas9k5dtnhKqyyld6TzzRjvMqLC79jkgT3z02FvJyHAuUtyemm/P/jYSne1xwFI06ezQxEwweaiV7VA==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-migrate/3.5.2/jquery-migrate.min.js" 
  integrity="sha512-BzvgYEoHXuphX+g7B/laemJGYFdrq4fTKEo+B3PurSxstMZtwu28FHkPKXu6dSBCzbUWqz/rMv755nUwhjQypw==" crossorigin="anonymous">
</script>
';
        $out .= '
</head>
<body class="nobackground border-0">
<br />
<div class="bord text-left mainrules">';
        if ($lng == $lang[0]) $out .= $rule1; else $out .= $rule2;
        $out .= '
</div>
<p class="text-right m-5 p-5">'.SubmitButton($web[57], 'javascript:window.close();').'</p>
</body>
</html>';
        return array($this->frtyp, $out);
    }
    
    public function FR_myposts() {
        global $userprefs, $web, $page, $meskin, $serviz, $forum, $urlrw, $site, $charset, $lng;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, $fielda1, $fielda2, $fieldb1, 
               $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        $dbw    = ReadDBFields(DBTHREAD);
        $dbwork = array();
        for ($i = 0; $i < count($dbw); $i++) {
            if ($dbw[$i][4] == $userprefs[1]) {
                $cat = strtoupper(substr($dbw[$i][8], 0, 2)) == 'PR' ? substr($dbw[$i][8], 2) : $dbw[$i][8];
                $dbw[$i][6] = getThreadName($dbw[$i][1]);
                $dbw[$i][7] = getForumCatName($cat);
                $dbwork[] = $dbw[$i];
            }
        }
        sort($dbwork);
        $topmess       = $web[616];
        $this->topmess = $page[9] == '' ? $topmess : '<i class="fab fa-forumbee float-right"></i>'.$topmess;
        $out           = '<!DOCTYPE html>
<html>
<head>
<title>'.$web[616].'</title>
<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'">';
        if (file_exists($meskin.'style.css')) $out .= '
<link rel="stylesheet" href="'.$meskin.'style.css" />';
        else $out .= '
<link rel="stylesheet" href="no_skin/style.css" />';
        if (file_exists($meskin.'styleplus.css')) $out .= '
<link rel="stylesheet" href="'.$meskin.'styleplus.css" />';
        else $out .= '
<link rel="stylesheet" href="no_skin/styleplus.css" />';
        if (file_exists($meskin."bs4style.css")) $out .= '
<link rel="stylesheet" href="'.$meskin.'bs4style.css" />';
        $out .=  '
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" 
  integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/css/bootstrap.min.css" 
  integrity="sha512-T584yQ/tdRR5QwOpfvDfVQUidzfgc2339Lc8uBDtcp/wYu80d7jwBgAxbyMh0a9YM9F8N3tdErpFI8iaGx6x5g==" crossorigin="anonymous" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" 
  integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.5.4/umd/popper.min.js" 
  integrity="sha512-7yA/d79yIhHPvcrSiB8S/7TyX0OxlccU8F/kuB8mHYjLlF1MInPbEohpoqfz0AILoq5hoD7lELZAYYHbyeEjag==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.min.js" 
  integrity="sha512-7rusk8kGPFynZWu26OKbTeI+QPoYchtxsmPeBqkHIEXJxeun4yJ4ISYe7C6sz9wdxeE1Gk3VxsIWgCZTc+vX3g==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-migrate/3.5.2/jquery-migrate.min.js" 
  integrity="sha512-BzvgYEoHXuphX+g7B/laemJGYFdrq4fTKEo+B3PurSxstMZtwu28FHkPKXu6dSBCzbUWqz/rMv755nUwhjQypw==" crossorigin="anonymous">
</script>
';
        $out .= '
</head>
<body class="bodyfrposts tblbox">
<br />
<div class="bord text-left mainfrposts">';
        if (empty($this->id)) $this->id = 1;
        if (!empty($dbwork)) {
            for ($i = $serviz[20] * ($this->id - 1); $i < Min($serviz[20] * $this->id, count($dbwork)); $i++) {
                ReadDoc($dbwork[$i][3]);
				$mrposline = stripos($fieldc1, '<p class="text-right thsign">');
				if (is_numeric($mrposline) && $mrposline > 0)
					$fieldc1 = str_replace('<p class="text-right thsign">', '<p class="text-end thsign">', $fieldc1);
                $out .= '
<div class="table-responsive-md">
<div class="d-table w-100">
  <div class="d-table-row">
    <div class="quest d-table-cell">
      <b>'.$dbwork[$i][7].'</b><br />'.$dbwork[$i][6].($forum[3] == 'on' ? ' - ' : '<br />').$web[68].$fielda2;
                $out .= '
    '.$web[7].' '.FormatDate($creadate).'
    </div>
  </div>
  <div class="d-table-row">
    <div class="rep d-table-cell">'.$fieldc1.'</div>
  </div>
</div>
</div>';
            }
        }
        if (count($dbwork) > $serviz[20]) $out .= '
<hr />
<div class="text-center">
  '.GetNavBar(($site['URLR'] == 'on' 
    ? $lng.'-'.$urlrw[5].'-8-' 
    : 'formyposts.php?lng='.$lng.'&amp;id='), 
    count($dbwork), $this->id, $serviz[20]);
        $out .= '
</div>
<div class="text-right m-5 p-5">'.SubmitButton($web[57], 'javascript:window.close();').'</div>';
        $out .= '
</div>
</body>
</html>';
        return array($this->frtyp, $out);
    }
    
    protected function topforum($forumname) {
        global $lng, $lang, $site, $serviz, $web, $prt, $urlrw, $z2, $members, $forum, $userprefs, $tconfig;
        $out = '';
        if (($serviz[28] == 'on' || $serviz[24] == 'on') && $forum[15] != 'on') {
            $dossier_users = MSGREP;
            $file_users    = opendir($dossier_users);
            $nbr_membres   = 0;
            while ($nomfichier = readdir($file_users)) {
                if (substr($nomfichier,-3) == "dtb") {
                    $nbr_membres++;
                }
            }
            closedir($file_users);
            $nbrmsg = array();
            if (!empty($userprefs[1]) && FileDBExist(MSGREP.$userprefs[1].DBEXT)) {
                $dbmsg = ReadDBFields(MSGREP.$userprefs[1].DBEXT);
                if ($dbmsg[0][0] == $userprefs[7]) {
                    for ($i = 2; $i < count($dbmsg); $i++) {
                        if ($dbmsg[$i][3] == "new") {
                            $nbrmsg[] = $dbmsg[$i][3];
                        }
                    }
                }
            }
            $out .= '
<div class="row p-0 m-0">
  <div class="col-md-4 p-0 m-0">';
            if ($serviz[28] == 'on' && !empty($nbr_membres)) {
                if (!empty($userprefs[1])) {
                    $out .= '
    <div class="d-inline-block pb-3 postsBtnForum">
	  <form 
        name="myposts" 
        action="" onsubmit="PopupWindow(\''.CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[5].'-7' 
          : 'formyposts.php?lng='.$lng).'\', \''.($site['URLR'] == 'on'? $lng.'067': 'myposts').'\', 1024, 768, \'yes\', \'yes\');" 
        method="post">
	  '.SubmitButton($web[616]).'
	  </form>
    </div>';
                    if ($serviz[18] != 'on') $out .= '
    <div class="d-inline-block mailBtnForum'.($forum[3] == 'on' ? 'W' : 'T').'">
	  <form 
        name="usermsg" 
        action="'.CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[13].'-3-3-'.$this->tconfig.$z2 
          : 'tinymsg.php?lng='.$lng.'&amp;action=3&amp;tconfig='.$this->tconfig.$z2).'" 
        method="post">
	    '.SubmitButton($web[318]).'
	  </form>
    </div>';
                    if (count($nbrmsg)!= 0) {
                        $act  = ($serviz[18] == 'on' ? '0' : '3');
                        $out .= '
    <div class="d-inline-block userWaveForum">
      <a class="box" href="'.CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[13].'-3-'.$act.'-'.$this->tconfig.$z2 
          : 'tinymsg.php?lng='.$lng.'&amp;action='.$act.'&amp;tconfig='.$this->tconfig.$z2).'">
        <i class="far fa-envelope blink display-4 text-success align-middle" title="'.$web[319].'"></i>
	  </a>
	</div>';
                    }
                }
                else {
                    if (empty($userprefs[1]) && $members[19] != '0' && $members[19] != '3') {
                        $out .= '
    <div class="d-inline-block newmembBtnForumW">
	  <form 
        name="newuser" 
        action="'.CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[15].'-5-new-'.$this->tconfig.$z2 
          : $site['USR'].'.php?lng='.$lng.'&amp;uuser=new&amp;tconfig='.$this->tconfig.$z2).'" 
        method="post">
	    '.SubmitButton($web[160]).'
	  </form>
    </div>';
                    }
                    if ($nbr_membres > 0 && empty($userprefs[1]))
                        $out .= '
    <div class="d-inline-block connectBtnForumW">
	  <form 
        name="userconnect" 
        action="'.CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[3].'-0-'.$this->tconfig.$z2 
          : 'connect.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'" 
        method="post">
	    '.SubmitButton($web[373]).'
	  </form>
    </div>';
                }
            }
            else $out .= '&nbsp;';
            $out .= '
  </div>
  <div class="col-md-2 p-0 m-0">';
            if ($serviz[28] == 'on' && !empty($nbr_membres)) {
                    $out .= '
    <div class="text-center align-middle membTopForumWW">';
                if ($serviz[158] == '') {
                    $text_nbr_membres = $web[$nbr_membres > 1 ? 321: 322];
                    $out .= '
	    <i class="fas fa-users" title="'.$web[320].'"></i>&nbsp;<b>'.$nbr_membres.' '.$text_nbr_membres.'</b>';
                }
                $out .= '
    </div>';
            }
            else $out .= '&nbsp;';
            $out .= '
  </div>
  <div class="col-md-4 p-0 m-0">';
            if ($serviz[28] == 'on' && !empty($nbr_membres)) {
                if ($serviz[159] == '') {
                    $regusers    = ReadDBFields(DBIPSTATS);
                    $usersonline = array();
                    for ($i = 0; $i < count($regusers); $i++) {
                        if (!empty($regusers[$i][2])) {
                            $usersonline[] = $regusers[$i][2];
                        }
                    }
                    @sort($usersonline);
                    if (empty($usersonline)) {
                        $out .= '
    <div class="d-inline-block text-center membTopForumWW">
	  <b>'.$web[175].'</b> '.$web[176].'
    </div>';
                    }
                    else {
                        $uniqueusers   = array();
                        $uniqueusers[] = $usersonline[0];
                        for ($i = 1; $i < count($usersonline); $i++) {
                            if ($usersonline[$i] != $usersonline[$i - 1]) {
                                $uniqueusers[] = $usersonline[$i];
                            }
                        }
                        if ($forum[3] == 'on' && $serviz[18] != 'on') {
                            $out .= '
    <div class="d-inline-block text-center membTopForumWW">
      <b>'.$web[175].'</b> '.count($uniqueusers).'&nbsp;';
                            $from = $userprefs[1];
                            if ($userprefs[1] != "") {
                                $out .= '
	  &nbsp;<i class="fas fa-caret-right" title="'.$web[321].'"></i> '.$userprefs[1].' &nbsp;';
                            }
                            $out .= '
      <form 
        name="tinysend"
        action="'.CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[13].'-3-1-'.$this->tconfig.$z2 
          : 'tinymsg.php?lng='.$lng.'&amp;action=1&amp;tconfig='.$tconfig.$z2).'" 
        method="post">
	    <input type="hidden" name="from" value="'.$from.'" />';
                            if (count($uniqueusers) > 1) {
                                $out .= '
        <div class="d-inline-block"> + </div>
	    <div class="d-inline-block">
          <select name="to" onchange="this.form.submit" class="form-control seluserTopForum align-middle">';
                                for ($i = 0; $i < count($uniqueusers); $i++) {
                                    if ($uniqueusers[$i] != $userprefs[1]) {
                                        $out .= '
		    <option value="'.$uniqueusers[$i].'">&nbsp;'.$uniqueusers[$i].'</option>';
                                    }
                                }
                                $out .= '
	      </select>
          </div>';
                            }
                            if ($userprefs[1] != '' && count($uniqueusers) > 1) {
                                $out .= '
	    <div class="d-inline-block">
          <button type="submit" class="mailsubmitTopForum" style="width: 56px;" title="'.$web[214].'"><i class="far fa-envelope"></i></button>
        </div>';
                            }
                            $out .= '
	  </form>
    </div>';
                        }
                        else {
                            $out .= '
    <div class="d-inline-block text-center membTopForumWW">
      <b>'.$web[175].'</b> '.count($uniqueusers).'&nbsp;
    </div>';
                        }
                    }
                }
                else $out .= '&nbsp;';
            }
            else $out .= '&nbsp;';
                $out .= '
  </div>
  <div class="col-md-2 p-0 m-0 text-right searchTopForum">';
            if ($serviz[24] == 'on') {
                include CONFIGREP.'searchconfig.inc';
                $tconfig = $confpage == 'ALL' ? $this->tconfig : $confpage;
                $out .= '
	<form 
        name="what"
        action="'.CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[12].'-'.$tconfig.$z2 
          : 'search.php?lng='.$lng.'&amp;tconfig='.$tconfig.$z2).'" 
        method="post">
	  <input name="searchin" type="hidden" value="'.TYP_FORUM.'" />
      <div class="input-group mb-3 w-100 m-auto">
        <input name="search" class="texte form-control" type="text" placeholder="'.$web[58].'" value="" />
        <div class="input-group-append c-pointer" onclick="javascript:what.submit();">
          <span class="input-group-text">
            <i class="fas fa-search" title="'.$web[58]." ".$forumname.'"></i>
          </span>
        </div>
      </div>
	</form>';
            }
            else $out .= '&nbsp;';
        $out .= '
  </div>
</div>
<div class="clearfix"></div>';
        }
        return $out;
    }
    
    public function FRT_content() {
        global $lng, $lang, $page, $site, $nom, $serviz, $web, $prt, $urlrw, $z2, $forum, $userprefs;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, $fielda1, $fielda2, $fieldb1, 
               $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        $k             = $lng == $lang[0] ? 0 : 1;
        $nom_fr        = getLabel($nom[$k+22]);
        $topmess       = strip_tags($nom_fr);
        $this->topmess = $page[9] == '' ? $topmess : '<i class="fab fa-forumbee float-right"></i>'.$topmess;
        $out           = '';
        $dbw           = ReadDBFields(DBFORUMCAT);
        $dbwork        = array();
        foreach ($dbw as $db) if (isForumAccessGranted($db[0]) && 'on' == $db[5]) $dbwork[] = $db;
        $dbworkcat = ReadDBFields(DBFORUM);
        for ($i = 0; $i < count($dbwork); $i++) {
            $prcat = explode(',', $dbwork[$i][0]);
            $dbwork[$i][0] = $prcat[0];
            $dbcat0 = $dbwork[$i][3];
            $dbcat1 = $dbwork[$i][4];
            $dbwork[$i][3] = 0;
            $dbwork[$i][4] = 0;
            $dbwork[$i] = array_pad($dbwork[$i] , 15, '');
            $dbwork[$i][14] = $dbcat0;
            $dbwork[$i][15] = $dbcat1;
            for ($j = 0; $j < count($dbworkcat); $j++) {
                $frcat = explode(',', $dbworkcat[$j][12]);
                $cat   = $frcat[0];
                $grp   = isset($frcat[1]) ? $frcat[1] : '';
                if ($cat == $dbwork[$i][0] && isForumAccessGranted($cat)) {
                    $dbwork[$i][3]++;
                    $dbwork[$i][4] = $dbwork[$i][4] + $dbworkcat[$j][7] + 1;
                    if (empty($dbwork[$i][6])) { 
                        if (!empty($dbworkcat[$j][8])) {
                            $dbwork[$i][6] = $dbworkcat[$j][8]; 
                            if ($dbworkcat[$j][11] != 'on') 
                                $dbwork[$i][7] = $dbworkcat[$j][9]; 
                            else 
                                $dbwork[$i][7] = ''; 
                            $dbwork[$i][8] = $dbworkcat[$j][0]; 
                        } 
                        else {
                            $dbwork[$i][6] = $dbworkcat[$j][3]; 
                            if ($dbworkcat[$j][10] != 'on') 
                                $dbwork[$i][7] = $dbworkcat[$j][4]; 
                            else 
                                $dbwork[$i][7] = ''; 
                            $dbwork[$i][8] = $dbworkcat[$j][0]; 
                        }
                        $dbwork[$i][9]  = $dbworkcat[$j][1]; 
                        $dbwork[$i][10] = $dbworkcat[$j][7]; 
                        $dbwork[$i][11] = ceil($dbworkcat[$j][7] / $serviz[20]); 
                        $dbwork[$i][12] = $dbworkcat[$j][2]; 
                        $dbwork[$i][13] = $dbworkcat[$j][12];
                    }
                }
            }
        }
        unset($dbworkcat);
        $dbwork    = array_values($dbwork);
        $forumname = $nom_fr;
        $archdate  = ReadCounter(DBFORUMARCHDATE);
        if ($archdate > 0) {
            $out .= '
<p class="text-center frtlnkarch">'.$web[228].' '.FormatDate($archdate).'
  <br />'.$web[227].'&nbsp;
  <a href="'.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[5].'-6-'.$this->tconfig.$z2 
      : 'fortopicarch.php?lng='.$lng.'&tconfig='.$this->tconfig.$z2).'">
    <b>'.$web[226].'&nbsp;<i class="far fa-file-alt"></i></b>
  </a>
  <br />
</p>';
        }
        if (empty($prt)) $out .= '<p>'.$this->topforum($forumname).'</p>';
        if ($forum[9] == 'on' && $forum[10] == 'on') {
            $out .= '
<div class="font-weight-bold text-left frtlnkrules">
  <a href="javascript:PopupWindow(\'rules.php?lng='.$lng.'\',\'rules\',800,500,\'yes\',\'yes\');" title="'.$web[409].'">
    <i class="fas fa-file-alt"></i> - '.$web[409].'
  </a>
</div>';
        }
        if (!empty($dbwork)) {
            $out .= '
<div class="text-center m-auto w-100 titrefr">
  <div class="table-responsive-md">
  <div class="bord d-table d-tbl w-100">
    <div class="d-table-row d-trhfr">
      <div class="d-table-cell d-tblth"></div>
      <div class="d-table-cell d-tblth w-40">'.$web[130].'</div>
      <div class="d-table-cell d-tblth w-13">'.$web[131].'</div>
      <div class="d-table-cell d-tblth w-13">'.$web[132].'</div>
      <div class="d-table-cell d-tblth w-30">'.$web[133].'</div>
    </div>';
            for ($i = 0; $i < count($dbwork); $i++) {
                $out .= '
    <div class="'.($i %2 == 0 ? 'rep' : 'quest').' d-table-row">
      <div class="d-table-cell text-center align-middle">
        <i class="frm-msg_a" title="'.$web[367].'"></i>
      </div>
      <div class="d-table-cell w-40">
        <a href="'.($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[5].'-2-'.getForumCatId($dbwork[$i][0]).$z2 
            : $site['FR'].'.php?lng='.$lng.GetOptionnalArg('cat', getForumCatId($dbwork[$i][0])).$z2).'" title="'.$dbwork[$i][1].'">
          <b>'.$dbwork[$i][1+$k].'</b>
        </a>
        <br /><i>'.($lng == $lang[0] ? $dbwork[$i][14] : $dbwork[$i][15]).'</i>
      </div>';
                $out .= '
      <div class="d-table-cell text-center w-13">'.$dbwork[$i][3].'</div>
      <div class="d-table-cell text-center w-13">'.$dbwork[$i][4].'</div>';
                $out .= '
      <div class="d-table-cell w-30">';
                $url1 = $site['URLR'] == 'on'? 
                    $lng.'-'.$urlrw[5].'-5-'.$dbwork[$i][12].'-'.getForumCatId($dbwork[$i][13]).'-'.$dbwork[$i][10].'#R'.$dbwork[$i][10] : 
                    $site['TH'].'.php?lng='.$lng.'&amp;pg='.$dbwork[$i][12].GetOptionnalArg('cat', getForumCatId($dbwork[$i][13])).
                      GetOptionnalArg('id'.$dbwork[$i][11]).'#R'.$dbwork[$i][10];
                if ($dbwork[$i][7] == "on") {
                    $em = BreakEMail($dbwork[$i][7]);
                    $out .= '
        '.$web[6].' '.displayWriteEmail('fortopic'.$i, $dbwork[$i][6], $dbwork[$i][7]).'
        <a href="'.$url1.'" title ="'.$web[297].'">&nbsp;<i class="far fa-caret-square-right"></i></a>';
                } elseif ($dbwork[$i][6] != "") {
                    $out .= '
        '.$web[6].' <b>'.$dbwork[$i][6].'</b>
        <a href="'.$url1.'" title ="'.$web[297].'">&nbsp;<i class="far fa-caret-square-right"></i></a>';
                }
				$idth = 0;
                if ($dbwork[$i][8] != "") {
                    $top  = '';
                    $dbth = array();
                    $dbth = ReadDBFields(DBTHREAD);
                    for ($l = 0; $l < count($dbth); $l++) {
                        if ($dbth[$l][1] == $dbwork[$i][9] && $dbth[$l][2] == $dbwork[$i][10]) {
                            $idth = $dbth[$l][3];
                            break;
                        }
                    }
					if ($idth != 0)
					{
						ReadDoc($idth);
						$lastdate = $creadate;
						$out .= '
			<br />'.$web[7].' '.FormatDate($lastdate);
					}
                }
                $out .= '
      </div>
    </div>';
            }
            $out .='
  </div>
  </div>
  <div class="bord bottomfrt w-100">';
            if ($forum[9] == 'on' && $forum[10] == '') {
                $out .= '
    <br />
    <p class="titre text-center text-uppercase"><u>'.$web[409].'</u></p>
    <br />
    <div class="frtrules">';
                include DBRULES;
                $out .= '
      '.($lng == $lang[0] ? $rule1 : $rule2).'
      <hr />
    </div>';
                if (empty($prt)) $out .= displayQuickConfig(NULL, false, 'rules', '', '', 24);
            }
            $out .= '
    <div class="infosfrt">
      <p class="titre text-center text-underline">'.$web[410].'</p>
      <p><b>'.$web[411].'</b>
        <br />
        <i class="frm-msg_a valign-4" title="'.$web[367].'"></i> '.$web[366].$web[367].' &nbsp;
        <i class="frm-msg_c valign-4" title="'.$web[368].'"></i> '.$web[366].$web[368].' &nbsp;
        <i class="frm-msg_t valign-4" title="'.$web[370].'"></i> '.$web[370].' &nbsp;
        <i class="frm-msg_n valign-4" title="'.$web[371].'"></i> '.$web[371].' &nbsp;
        <br />
        <br />
        '.$web[412].'
        <br />
        <i class="far fa-edit" title="'.$web[334].'"></i> '.$web[369].' '.$web[334].' &nbsp;
        <i class="frm-lock valign-1" title="'.$web[363].'"></i> '.$web[363].$web[366].' &nbsp;
        <i class="frm-lockup valign-1" title="'.$web[364].'"></i> '.$web[364].$web[366].' &nbsp;'
        .($userprefs[1] == $serviz[31] || $userprefs[1] == $serviz[176] ? '
        <i class="frm-unlock valign-1" title="'.$web[621].'"></i> '.$web[621].$web[366].' &nbsp;
        <i class="frm-moveto valign-1" title="'.$web[632].'"></i> '.$web[632].$web[366]
		: '').'
        <br />
      </p>
    </div>
  </div>
</div>';
        }
        return array($this->frtyp, $out);
    }
    
    public function FRTA_content() {
        global $lng, $lang, $site, $page, $web, $prt, $urlrw, $z2, $serviz;
        $k      = $lng == $lang[0] ? 0 : 1;
        $out    = '';
        $dbw    = ReadDBFields(DBFORUMCAT);
        $dbwork = array();
        foreach ($dbw as $db) {
            $acat = explode(',', $db[0]);
            if (isForumAccessGranted($acat[0])) $dbwork[] = $db;
        }
        $dbworkcat = ReadDBFields(DBFORUMARCH);
        for ($i = 0; $i < count($dbwork); $i++) {
            $prcat = explode(',', $dbwork[$i][0]);
            $dbwork[$i][0] = $prcat[0];
            $dbwork[$i][3] = 0;
            $dbwork[$i][4] = 0;
            for ($j = 0; $j < count($dbworkcat); $j++) {
                if ($dbworkcat[$j][12] == $dbwork[$i][0]) {
                    $dbwork[$i][3]++;
                    $dbwork[$i][4] = $dbwork[$i][4] + $dbworkcat[$j][7] + 1;
                    if (empty($dbwork[$i][6])) {
                        if (!empty($dbworkcat[$j][8])) {
                            $dbwork[$i][6] = $dbworkcat[$j][8];
                            if ($dbworkcat[$j][11] != 'on') 
                                $dbwork[$i][7] = $dbworkcat[$j][9];
                            else 
                                $dbwork[$i][7] = '';
                            $dbwork[$i][8] = $dbworkcat[$j][0];
                        }
                        else {
                            $dbwork[$i][6] = $dbworkcat[$j][3];
                            if ($dbworkcat[$j][10] != 'on') 
                                $dbwork[$i][7] = $dbworkcat[$j][4];
                            else 
                                $dbwork[$i][7] = '';
                            $dbwork[$i][8] = $dbworkcat[$j][0];
                        }
                    }
                }
            }
        }
        unset($dbworkcat);
        $dbwork        = array_values($dbwork);
        $topmess       = $web[226];
        $this->topmess = $page[9] == '' ? $topmess : '<i class="fab fa-forumbee float-right"></i>'.$topmess;
        $archdate      = ReadCounter(DBFORUMARCHDATE);
        if ($archdate > 0 && empty($prt)) {
            $out .= '
<p class="text-center">
  '.$web[225].' '.FormatDate($archdate).'<br />'.$web[224].'&nbsp;
  <a href="'.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[5].'-'.$this->tconfig.$z2 
      : $site['FRT'].'.php?lng='.$lng.'&tconfig='.$this->tconfig.$z2).'">
    <b>'.$web[223].'</b>&nbsp;
    <i class="far fa-file-alt"></i>
  </a>.<br /><br />
</p>';
        }
        if (!empty($dbwork)) {
            $out .= '
<div class="table-responsive-md">';
		if ($serviz[34] == 'on') 
				$out .= '<div class="bord d-table d-tbl m-auto w-98">';
		else
			$out .= '<div class="bord d-table d-tbl m-auto w-50">';
        $out .= '
  <div class="d-table-row text-center;">
    <div class="d-table-cell">'.$web[130].'</div>
    <div class="d-table-cell">'.$web[131].'</div>
    <div class="d-table-cell">'.$web[132].'</div>
    <div class="d-table-cell">'.$web[133].'</div>
  </div>';
            for ($i = 0; $i < count($dbwork); $i++) {
                if ($dbwork[$i][3] > 0) {
                    $acces = 'ok';
                    $prcat = explode(',', $dbwork[$i][0]);
                    if (isset($prcat[1])) {
                        $acces = 'no';
                        include_once CHEMIN.'inc/func_groups.php';
                        if (CheckGroup($prcat[1], $userprefs[1])) $acces = 'ok';
                    }
                    if ($acces == 'ok') {
                        $out .= '
  <div class="d-table-row text-center">
    <div class="quest d-table-cell">
      <a href="'.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[5].'-33-'.getForumCatId($dbwork[$i][0]).$z2 
          : 'forumarch.php?lng='.$lng.GetOptionnalArg('cat', getForumCatId($dbwork[$i][0])).$z2).'">
        <b>'.$dbwork[$i][1+$k].'</b>
      </a>
    </div>
    <div class="quest d-table-cell">'.$dbwork[$i][3].'</div>
    <div class="quest d-table-cell">'.$dbwork[$i][4].'</div>
    <div class="quest d-table-cell">';
                        if (isset($dbwork[$i][7]) && $dbwork[$i][7] != "") 
                            $out .= $web[6].' '.displayWriteEmail('fortopicarch'.$i, $dbwork[$i][6], $dbwork[$i][7]);
                        elseif (isset($dbwork[$i][6]) && $dbwork[$i][6] != '') 
                            $out .= $web[6].' <b>'.$dbwork[$i][6].'</b>';
                        if (isset($dbwork[$i][8]) && $dbwork[$i][8] != '') 
                            $out .= '<br />'.$web[7].' '.FormatDate($dbwork[$i][8]);
                        $out .= '</div></div>';
                    }
                }
            }
        }
        return array($this->frtyp, $out);
    }
    
    public function FR_content() {
        global $lng, $lang, $site, $nom, $serviz, $web, $prt, $urlrw, $z2, $forum, $page;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, $fielda1, $fielda2, $fieldb1, 
               $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        $l             = $lng == $lang[0] ? 0 : 1;
        $nom_fr        = getLabel($nom[$l + 22]);
        $topmess       = $nom_fr;
        $this->topmess = $page[9] == '' ? $topmess : '<i class="fab fa-forumbee float-right"></i>'.$topmess;
        $cats          = explode(',', $this->cat);
        $this->cat     = $cats[0];
        $dbwork1       = array();
        if (!empty($this->pg)) { 
            $dbwork1 = SelectDBForumByID(ReadDBFields(DBFORUM), $this->pg);
        }
        elseif (!empty($this->cat)) {
            $dbwork1 = SelectDBForumByCat(ReadDBFields(DBFORUM), $this->cat);
        }
        else {
            $dbwork1 = ReadDBFields(DBFORUM);
        }
        $out       = '';
        $dbworkcat = array();
        $dbwork    = ReadDBFields(DBFORUMCAT);
        foreach ($dbwork as $dbcat) if ($dbcat[5] != '') $dbworkcat[] = $dbcat;
        $dbwork = array();
        foreach ($dbwork1 as $db) if (isForumAccessGranted(getForumCatFull($db[12]))) $dbwork[] = $db;
        unset($dbwork1);
        if (!isForumAccessGranted(getForumCatFull($this->cat))) return array($this->frtyp, 1);
        $catxt   = '';
        $topmess = '<a href="'.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[5].$z2 : $site['FRT'].'.php?lng='.$lng.$z2).'">'.$nom_fr.'</a>';
        if (!empty($this->cat)) {
            $catxt    = getForumCatName($this->cat);
            $topmess .= ' - '.$catxt;
        }
        $this->topmess = $page[9] == '' ? $topmess : '<i class="fab fa-forumbee float-right"></i>'.$topmess;
        $forumname = strip_tags($nom_fr);
        $archdate  = ReadCounter(DBFORUMARCHDATE);
        if ($archdate > 0) $out .= '
<p class="text-center frtlnkarch">'.$web[228].' '.FormatDate($archdate).'
  <br />'.$web[227].'&nbsp;
  <a href="'.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[5].'-6-'.$this->tconfig.$z2 
      : 'fortopicarch.php?lng='.$lng.'&tconfig='.$this->tconfig.$z2).'">
    <b>'.$web[226].'&nbsp;<i class="far fa-file-alt"></i></b>
  </a><br />
</p>';
        if (empty($prt)) $out .= $this->topforum($forumname);
        $out .= '
<div class="table-responsive-md">
<div class="bord d-table d-tbl text-center w-100">
  <div class="d-table-row">
    <div class="d-table-cell text-left w-50">';
        if ($forum[9] == 'on') $out .= '
      <div class="font-weight-bold frtlnkrules">
		<a href="javascript:PopupWindow(\'rules.php?lng='.$lng.'\',\'rules\',800,500,\'yes\',\'yes\');" title="'.$web[409].'">
          <i class="fas fa-file-alt"></i> - '.$web[409].'
        </a>
      </div>
      <br />';
        if ($catxt != '') $out .= '
      <div class="font-weight-bold frlnkforumW">
        <a href="'.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[5].'-'.$this->tconfig.$z2 : $site['FRT'].'.php?lng='.$lng.'&tconfig='.$this->tconfig.$z2).'" title="'.$forumname.'">
          <i class="fas fa-folder-open"></i> - '.$forumname.'
        </a>
      </div>
      <div class="font-weight-bold frlnkcategW">
        <i class="far fa-folder-open" title="'.$catxt.'"></i> -
        <a href="'.$site[3].($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[5].'-2-'.$this->cat.$z2 
            : $site['FR'].'.php?lng='.$lng.'&amp;cat='.$this->cat.$z2).'" title="'.$catxt.'">
          '.$catxt.'
        </a>
      </div>
    </div>
    <div class="d-table-cell text-right width50">
      <form 
          action="'.CHEMIN.($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[14].'-3-'.TYP_FORUM.'-'.$this->cat.$z2 
            : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_FORUM.GetOptionnalArg('cat', $this->cat).$z2).'" 
          method="post">
        <p class="text-right">
          '.SubmitButton($web[19]).'
        </p>
      </form>
    </div>';
        else $out .= '
    </div>
    <div class="d-table-cell width50">
      <form 
          action="'.CHEMIN.($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[14].'-'.TYP_FORUM.$z2 
            : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_FORUM.$z2).'" 
          method="post">
        <p class="text-right">
          '.SubmitButton($web[19]).'
        </p>
      </form>
    </div>';
        $out .= '
  </div>
  </div>
</div>';
        if (empty($this->id)) $this->id = 1;
        if (3 == abs($prt)) {
            $this->id   = 1;
            $serviz[17] = 99999;
        }
        $navbar = GetNavBar(($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[5].'-22-'.$this->cat.'-' 
          : $site['FR'].'.php?lng='.$lng.GetOptionnalArg('cat', $this->cat).'&amp;id='), 
          count($dbwork), $this->id, $serviz[17], $z2);
        $out   .= $navbar;
        $out   .= '
<div class="text-center m-auto">
  <div class="table-responsive-md">
  <div class="bord d-table d-tbl width98">
    <div class="d-table-row d-trhfr">
      <div class="d-table-cell d-tblth w-30">'.$web[298].'</div>
      <div class="d-table-cell d-tblth w-10">'.$web[63].'</div>
      <div class="d-table-cell d-tblth w-20">'.$web[299].'</div>';
        if ($serviz[34] == 'on') $out .= '
      <div class="d-table-cell d-tblth w-10">'.$web[217].'</div>
      <div class="d-table-cell d-tblth w-10">'.$web[67].'</div>
      <div class="d-table-cell d-tblth w-20">'.$web[66].'</div>
    </div>';
	    else $out .= '</div>';
	 
        if (count($dbwork) > 0) {
            $newdate = date('YmdHi', time()-(86400*$forum[8]));
            for ($i = $serviz[17]*($this->id-1); $i < Min($serviz[17]*$this->id, count($dbwork)); $i++) {
                $color = $i %2 == 0 ? 'rep' : 'quest';
                if ($serviz[34] == 'on') $threadcounter = ReadDocCounter($dbwork[$i][2]);
                ReadDoc($dbwork[$i][2]);
                $img = getNewUpdateDoc($creadate, $moddate, $serviz[139]);
                $st  = explode('#',$fieldmod);
                if ($st[0] == 'c') {
                    $etat    = $web[368].' '.$web[6].' '.@$st[1].((isset($st[2]))? ' '.$web[7].' '.FormatDate($st[2]) : '');
                    $img_msg = 'msg_'.$st[0];
                } 
                elseif ($st[0] == 's') {
                    $st[0]   = 'c';
                    $etat    = $web[368].' '.$web[6].' '.@$st[1].((isset($st[2]))? ' '.$web[7].' '.FormatDate($st[2]) : '');
                    $img_msg = 'msg_'.$st[0];
                } 
                elseif ($st[0] == 't' && $moddate > date('YmdHi')) {
                    $etat    = 'Top '.$web[6].' '.$st[1];
                    $img_msg = 'msg_'.$st[0];
                } 
                elseif ($st[0] == 'u' && $moddate > date('YmdHi')) {
                    $st[0]   = 't';
                    $etat    = 'Up '.$web[6].' '.$st[1].((isset($st[2]))? ' '.$web[7].' '.FormatDate($st[2]) : '');
                    $img_msg = 'msg_'.$st[0];
                } 
                elseif ($newdate < $creadate) {
                    $st[0]   = 'n';
                    $etat    = $web[371];
                    $img_msg = 'msg_'.$st[0];
                } 
                else {
                    $st[0]   = 'a';
                    $etat    = $web[367];
                    $img_msg = 'msg_'.$st[0];
                }
                $urw  = empty($this->cat) ? '-411-'.$dbwork[$i][2] : '-5-'.$dbwork[$i][2].'-'.$this->cat;
                $out .= '
    <div class="'.$color.' d-table-row">
      <div class="d-table-cell w-30">
        <i class="frm-'.$img_msg.' align-middle" title="'.$etat.'"></i>
        <a href="'.($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[5].$urw.'-'.$this->id.$z2 
            : $site['TH'].'.php?lng='.$lng.'&amp;pg='.$dbwork[$i][2].GetOptionnalArg('cat', $this->cat).GetOptionnalArg('fid', $this->id).$z2).'"  
            title="'.$dbwork[$i][5].'">
          '.$img.'<b>'.$dbwork[$i][5].'</b>
        </a>
      </div>
      <div class="d-table-cell text-center w-10">
        <a href="'.($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[5].$urw.'-'.$this->id.$z2 
            : $site['TH'].'.php?lng='.$lng.'&amp;pg='.$dbwork[$i][2].GetOptionnalArg('cat', $this->cat).GetOptionnalArg('fid', $this->id).$z2).'">
          <b>'.$dbwork[$i][1].'</b>
        </a>
      </div>
      <div class="d-table-cell w-20">';
                if ($dbwork[$i][10] == "on") $out .= displayWriteEmail('forum1'.$i, $dbwork[$i][3], $dbwork[$i][4]);
                else $out .= '
        <b>'.$dbwork[$i][3].'</b>';
            ReadDoc($dbwork[$i][2]);
            $out .= '
        <br />'.FormatDate($creadate).'
      </div>';
                if ($serviz[34] == 'on')
				{
						$txtcount = $threadcounter <= 1 ? $web[188] : $web[189];
						$out .= '
		  <div class="d-table-cell text-center w-10">'.$threadcounter.' '.$txtcount.'</div>';
					 $out .= '
		  <div class="d-table-cell text-center w-10">'.$dbwork[$i][7].'</div>';
					$out .= '
		  <div class="d-table-cell w-20">';
					if ($dbwork[$i][7] != 0) {
						$idder = (ceil($dbwork[$i][7]/ $serviz[20]));
						$urw   = empty($this->cat) ? '-411-'.$fileid : '-5-'.$fileid.'-'.$this->cat;
						if ($dbwork[$i][11] == 'on') 
							$out .= displayWriteEmail('forum2'.$i, $dbwork[$i][8], $dbwork[$i][9]).' 
			<a href="'.($site['URLR'] == 'on' 
				? $lng.'-'.$urlrw[5].$urw.'-'.$idder.'#R'.$dbwork[$i][7] 
				: $site['TH'].'.php?lng='.$lng.'&amp;pg='.$fileid.GetOptionnalArg('cat', $this->cat).GetOptionnalArg('id', $idder).'#R'.$dbwork[$i][7]).'" 
				title ="'.$web[297].'"> &nbsp;
			  <i class="far fa-caret-square-right"></i>
			</a>
			<br />';
						else $out .= '
			<b>'.$dbwork[$i][8].'</b>
			<a href="'.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[5].$urw.'-'.$idder.'#R'.$dbwork[$i][7] : $site['TH'].'.php?lng='.$lng.'&amp;pg='.$fileid.GetOptionnalArg('cat', $this->cat).GetOptionnalArg('id', $idder).'#R'.$dbwork[$i][7]).'" title ="'.$web[297].'"> &nbsp;
			  <i class="far fa-caret-square-right"></i>
			</a>
			<br />';
						$dbth = array();
						$dbth = ReadDBFields(DBTHREAD);
						for ($j = 0; $j < count($dbth); $j++) {
							if ($dbth[$j][1] == $dbwork[$i][1] && $dbth[$j][2] == $dbwork[$i][7]) {
								$th_id = $dbth[$j][3];
								break;
							}
						}
						if (isset($th_id)) {
							ReadDoc($th_id);
							$out .= FormatDate($creadate);
						}
					}
				
				$out .= '</div>';
				}
                $out .= '</div>';
            }
        }
        $out .= '
  </div>
  </div>';
        $out .= $navbar;
        $out .= '
  <div class="w-100 frbottom">
    <div class="text-left align-middle float-left frimgsbottom">
      <i class="frm-msg_a valign-4" title="'.$web[367].'"></i> '.$web[366].$web[367].' &nbsp;
      <i class="frm-msg_c valign-4" title="'.$web[368].'"></i> '.$web[366].$web[368].' &nbsp;
      <i class="frm-msg_t valign-4" title="'.$web[370].'"></i> '.$web[370].' &nbsp;
      <i class="frm-msg_n valign-4" title="'.$web[371].'"></i> '.$web[371].$web[334].' &nbsp;
    </div>
    <div class="text-right float-right frretfrt">
      <form 
          action="'.CHEMIN.($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[5].'-'.$this->tconfig.$z2 
            : $site['FRT'].'.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'" 
          method="post">
        '.SubmitButton($web[134]).'
      </form>
    </div>
  </div>
</div>
<br />';
        return array($this->frtyp, $out);
    }
    
    public function FRA_content() {
        global $lng, $lang, $site, $page, $nom, $serviz, $web, $prt, $urlrw, $z2, $forum, $userprefs;
        $l       = $lng == $lang[0] ? 0 : 1;
        $out     = '';
        $dbwork1 = array();
		$dbwork = array();
		$dbthrd = array();
        if (!empty($this->pg)) $dbwork1 = SelectDBForumByID(ReadDBFields(DBFORUMARCH), $this->pg);
        elseif (!empty($this->cat)) $dbwork1 = SelectDBForumByCat(ReadDBFields(DBFORUMARCH), $this->cat);
        else $dbwork1 = ReadDBFields(DBFORUMARCH);
        $dbworkcat = array();
        $dbwork    = ReadDBFields(DBFORUMCAT);
        foreach ($dbwork as $dbcat) {
            $acat = explode(',', $dbcat[0]);
            if ($dbcat[5] == 'on' && isForumAccessGranted($acat[0])) $dbworkcat[] = $dbcat;
        }
        $dbwork = array();
        foreach ($dbwork1 as $db) {
            $dbcat = array();
            if ($db[12] != '') $dbcat = SelectDBFieldsByField($dbworkcat, 0, $db[12]);
            if (isForumAccessGranted($db[12]) && !empty($dbcat)) $dbwork[] = $db;
        }
        unset($dbwork1);
        if (empty($dbwork) || !isForumAccessGranted(getForumCatFull($this->cat))) {
            return array($this->frtyp, 1);
        }
        $topmess = '<a href="'.($site['URLR'] == 'on' ? 
          $lng.'-'.$urlrw[5].'-6-'.$z2 : 'fortopicarch.php?lng='.$lng.'&tconfig='.$this->tconfig.$z2).'">'.$web[226].'</a>';
        if (!empty($this->cat)) {
            $catxt = '';
            for ($k = 0; $k < count($dbworkcat); $k++) {
                if ($dbworkcat[$k][0] == $this->cat) {
                    $catxt = $dbworkcat[$k][1 + $l];
                    break;
                }
            }
            $topmess .= ' - '.$catxt;
        }
        $this->topmess = $page[9] == '' ? $topmess : '<i class="fab fa-forumbee float-right"></i>'.$topmess;
        include_once CHEMIN.'inc/func_groups.php';
        $acces = 'ok';
        if (!IsValidForumCat($this->cat)) $acces = 'no';
        if (IsPrivateForumCat($this->cat)) {
            $acces = 'no';
            if ($userprefs[1] != '') if (CheckGroup($this->cat, $userprefs[1])) $acces = 'ok';
        }
        if ($acces != 'ok') $out .= '
<p class="text-center font-weight-bold frnoaccess"><br />'.$web[443].'</strong><br /></p>';
        else {
            if (empty($prt)) {
                $archdate = ReadCounter(DBFORUMARCHDATE);
                if ($archdate > 0) $out .= '
<p class="text-center">
  '.$web[225].' '.FormatDate($archdate).'.<br />'.$web[224].'&nbsp;
  <a href="'.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[5].'-20'.$this->cat.$z2 
      : $site['FR'].'.php?lng='.$lng.'&cat='.$this->cat.$z2).'">
    <b>'.$web[223].'&nbsp;<i class="far fa-file-alt"></i></b>
  </a>.<br />
  <hr />
</p>';
            }
            if (empty($this->id)) $this->id = 1;
            if (!empty($dbwork)) {
                for ($i = $serviz[17]*($this->id-1); $i < Min($serviz[17]*$this->id, count($dbwork)); $i++) {
                    if ($serviz[34] == 'on') $threadcounter = ReadDocCounter($dbwork[$i][2], ARCHDBBASE);
                    $out .= '
<div class="table-responsive-md">
<div class="d-table">
  <div class="d-table-row">
	<div class="d-table-cell frarchsubject";>
	  <a href="'.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[5].'-51-'.$dbwork[$i][2].'-'.$this->cat.'-'.$this->id.$z2 
          : 'threadarch.php?lng='.$lng.'&pg='.$dbwork[$i][2].GetOptionnalArg('cat', $this->cat).GetOptionnalArg('fid', $this->id).$z2).'">
        '.$web[63].$dbwork[$i][1].'
      </a>
	</div>
	<div class="quest d-table-cell w-100 frarchbody">
	  <a href="'.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[5].'-51-'.$dbwork[$i][2].'-'.$this->cat.'-'.$this->id.$z2 
          : 'threadarch.php?lng='.$lng.'&pg='.$dbwork[$i][2].GetOptionnalArg('cat', $this->cat).GetOptionnalArg('fid', $this->id).$z2).'">
        <b>'.$dbwork[$i][5].'</b>
      </a>
	  <br />&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;'.$web[6].' ';
                    if ($dbwork[$i][10] != 'on') $out .= displayWriteEmail('forumarch1'.$i, $dbwork[$i][3], $dbwork[$i][4]);
                    else $out .= '
	  <b>'.$dbwork[$i][3].'</b>';
                    $out .= ' '.$web[7].' '.FormatDate($dbwork[$i][6]);
                    if ($dbwork[$i][7] != 0) {
                        if ($dbwork[$i][11] != 'on') 
                            $formail = displayWriteEmail('forumarch2'.$i, $dbwork[$i][8], $dbwork[$i][9]);
                        else $formail = '
	  <b>'.$dbwork[$i][8].'</b>';
                        if ($dbwork[$i][7] == 1) $out .= '
	  <br />&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;'.$web[93].' '.$formail.' '.$web[94].' '.FormatDate($dbwork[$i][0]).')';
                        else $out .= '
	  <br />&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;'.$dbwork[$i][7].' '.$web[66]." ".$formail.' '.$web[94].' '.FormatDate($dbwork[$i][0]).')';
                    }
                    else $out .= '
	  <br />&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;'.$web[67];
                    if ($serviz[34] == 'on') {
                        $txtcount = $threadcounter <= 1 ? $web[188] : $web[189];
                        $out .= '
	  <br />&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;'.$web[217].' '.$threadcounter.' '.$txtcount; 
                    }
                    $out .= '
	  <br />
	</div>
  </div>
</div>
</div>';
                    if ($i < Min($serviz[17]*$this->id, count($dbwork))-1) $out .= '
<hr />';
                }
            }
            if (count($dbwork)>$serviz[17]) $out .= '
<hr />
'.GetNavBar(($site['URLR'] == 'on' 
    ? $lng.'-'.$urlrw[5].'-35-'.$this->cat.'-' 
    : 'forumarch.php?lng='.$lng.GetOptionnalArg('cat', $this->cat).'&id='), 
    count($dbwork), $this->id, $serviz[17], $z2);
            if (count($dbwork) <= $serviz[17]) $out .= '
<hr />';
            $out .= '
<p class="text-center">
  <a href="'.($site['URLR'] == 'on' ? 
    $lng.'-'.$urlrw[5].'-6-'.$this->tconfig.$z2 : 'fortopicarch.php?lng='.$lng.'&tconfig='.$this->tconfig.$z2).'">'.$web[134].'
  </a>
</p>';
        }
        return array($this->frtyp, $out);
    }
    
    public function TH_content() {
        global $userprefs, $web, $page, $serviz, $forum, $urlrw, $site, $lng, $prt, $drtuser, $z2;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, $fielda1, $fielda2, $fieldb1, 
               $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        $out   = '';
		$dbwork = array(); 
        $acats = GetForumCat();
        $iscat = false;
        foreach($acats as $acat) {
            if (getForumCatId($acat[0]) == $this->cat) {
                $iscat = true;
                break;
            }
        }
        if (!isForumAccessGranted(getForumCatFull($this->cat)) || !$iscat) {
            $href1 = $site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng;
            return array($this->frtyp, 1, $href1);
        }
        $thread = 0;
        if (!empty($this->pg)) {
            $dbwork = SelectDBForumByID(ReadDBFields(DBFORUM), $this->pg);
            if (count($dbwork) != 0) $thread = $dbwork[0][1];
            ReadDoc($this->pg);
            $frcat     = explode(',', $fieldb2);
            $this->cat = $frcat[0];
        } 
        elseif (!empty($this->thrd)) {
            $dbwork = ReadDBFields(DBTHREAD);
            foreach ($dbwork as $dbw) {
                if ($dbw[1] == $this->thrd && $dbw[2] == '0') {
                    $thread   = $this->thrd;
                    $this->pg = $dbw[3];
                    break;
                }
            }
        }
        $activeCat = true;
        if (!empty($this->cat)) {
            $dbcat = getForumCat();
            foreach ($dbcat as $db)
                if ($db[0] == $this->cat) {
                    $activeCat = $db[5] == 'on';
                    break;
                }
        }
        if ($thread === 0 || !$activeCat) {
            $threadarch = 0;
            $dbworkarch = SelectDBForumByID(ReadDBFields(DBFORUMARCH), $this->pg);
			$threadarch = 0;
			if (is_array($dbworkarch))
			{
				if (count($dbworkarch) > 0)        	
						$threadarch = $dbwork[0][1];
				if ($threadarch !== 0) {
					return array($this->frtyp, 1, ($site['URLR'] == 'on' ? 
						$lng.'-'.$urlrw[5].'-51-'.$this->pg.'-'.$this->cat.'-'.$this->fid.$z2 : 
						'threadarch.php?lng='.$lng.'&pg='.$this->pg.'&cat='.$this->cat.'&fid='.$this->fid.$z2));
				}
			}
            if ($site[10] != '') 
                return array($this->frtyp, 1, $site[3].'error.php?lng='.$lng.'&err=404');
            else 
                return array($this->frtyp, 1, '', $web[36]);
        }
        if ($serviz[34] == 'on') $threadcounter = UpdateDocCounter($this->pg);
        $dbthrd = SelectDBThreadByThread(ReadDBFields(DBTHREAD), $thread);
        if (!isForumAccessGranted($fielda1)) {
            return array($this->frtyp, 1, 'HTTP/1.0 403 Forbidden');
        }
        $topmess = '<a href="'.($site['URLR'] == 'on' ? 
            $lng.'-'.$urlrw[5].'-21-'.$this->fid.$z2 : 
            $site['FR'].'.php?lng='.$lng.'&id='.$this->fid.$z2).'">'.$this->nom_frth.'</a>';
        if (!empty($this->cat)) {
            $dbworkcat = array();
            $dbwcat    = ReadDBFields(DBFORUMCAT);
            foreach($dbwcat as $wcat) if ($wcat[5] == 'on') $dbworkcat[] = $wcat;
            $catxt   = getForumCatName($this->cat);
            $topmess = '<a href="'.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[5].$z2 : $site['FRT'].'.php?lng='.$lng.$z2).'">'.$this->nom_frth.'</a>
            - <a href="'.($site['URLR'] == 'on' ? 
                $lng.'-'.$urlrw[5].'-22-'.$this->fid.'-'.getForumcatId($this->cat).$z2 : 
                $site['FR'].'.php?lng='.$lng.'&id='.$this->fid.'&cat='.getForumcatId($this->cat).$z2).'">'.$catxt.'</a> - ';
        }
        else {
            $dbworkcat     = array();
            $catxt         = 'unknown';
            $topmess = '<a href="'.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[5].$z2 : $site['FRT'].'.php?lng='.$lng.$z2).'">'.$this->nom_frth.'</a> - ';
        }
        $topmess      .= $fieldb1;
        $this->topmess = $page[9] == '' ? $topmess : '<i class="fab fa-forumbee float-right"></i>'.$topmess;
        ReadDoc($this->pg);
        $out .= '<br />';
        if (empty($prt)) $out .= $this->topforum($this->nom_frth);
        if ($forum[9] == 'on' && empty($prt)) {
            $out .= '
<div class="font-weight-bold frtlnkrules">
  <a href="javascript:PopupWindow(\'rules.php?lng='.$lng.'\',\'rules\',800,500,\'yes\',\'yes\');" title="'.$web[409].'">
    <i class="fas fa-file-alt"></i> - '.$web[409].'
  </a>
</div>';
        }
        $title = '
<div class="font-weight-bold thlnkforumW">
  <a href="'.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[5].'-'.$this->tconfig.$z2 
      : $site['FRT'].'.php?lng='.$lng.'&tconfig='.$this->tconfig.$z2).'" title="'.$this->nom_frth.'">
    <i class="fas fa-folder-open"></i> - '.$this->nom_frth.'
  </a>
</div>
<div class="font-weight-bold thlnkcateg">
  <a href="'.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[5].'-2-'.getForumcatId($this->cat).$z2 
      : $site['FR'].'.php?lng='.$lng.'&cat='.getForumcatId($this->cat).$z2).'" title="'.$catxt.'">
    <i class="far fa-folder-open"></i> - '.$catxt.'
  </a>
</div>';
        $out .= '
<p class="text-left">'.$title.'</p>';
        $newdate = date('YmdHi', time() - (86400 * $forum[8]));
        $st = explode('#',$fieldmod);
		if (!isset($st[1])) $st[1] = '';
		if (!isset($st[2])) $st[2] = '';
        if ($st[0] == 'c') {
            $etat = $web[368].' '.$web[6].' '.$st[1].' '.$web[7].' '.FormatDate($st[2]);
            $img_msg = 'msg_'.$st[0];
        } elseif ($st[0] == 's') {
            $st[0] = 'c';
            $etat = $web[368].' '.$web[6].' '.$st[1].' '.$web[7].' '.FormatDate($st[2]);
            $img_msg = 'msg_'.$st[0];
        } elseif ($st[0] == 't' && $moddate > date('YmdHi')) {
            $etat = $web[370].' '.$web[6].' '.$st[1].' '.$web[7].' '.FormatDate($st[2]);
            $img_msg = 'msg_'.$st[0];
        } elseif ($st[0] == 'u' && $moddate > date('YmdHi')) {
            $st[0] = 't';
            $etat = 'Up '.$web[6].' '.$st[1].' '.$web[7].' '.FormatDate($st[2]);
            $img_msg = 'msg_'.$st[0];
        } elseif ($newdate < $creadate) {
            $st[0] = 'n';
            $etat = $web[371];
            $img_msg = 'msg_'.$st[0];
        } else {
            $st[0] = 'a';
            $etat = $web[367];
            $img_msg = 'msg_'.$st[0];
        }
        $out .= '
<div class="w-100 thmainW">
  <div class="bord row w-100 thhead">
    <div class="col-md-3 p-1">
      <div class=" text-center px-2 pt-2 thheadsubjectW">
	    <i class="frm-'.$img_msg.'" title="'.$etat.'"></i>&nbsp;&nbsp;<b>'.$web[63].' '.$thread.'</b>
      </div>
	</div>
    <div class="col-md-9 p-1">
      <div class="text-left px-2 pt-2 thheadbodyW">&nbsp;
        <a href="'.$site[3].($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[5].'-4-'.$this->pg.'-'.$this->cat.$z2 
            : $site['TH'].'.php?lng='.$lng.'&pg='.$this->pg.GetOptionnalArg('cat', $this->cat).$z2).'" title="'.$web[511].'">
          <b>'.$fieldb1.'</b>
        </a>
	  </div>
    </div>
  </div>
  <div class="row w-100 thbody">
    <div class="col-md-3 rep text-center">
      <div class="row">
        <div class="col-md-12">';
        if ($fieldd2 != 'v' && $fieldd2 != '') 
            $nbrpostmsg = $fieldd2.' '.($fieldd2 > 1 ? $web[335] : $web[334]);
        else 
            $nbrpostmsg = '';
        $max = explode('-', $forum[7]);
        if ($fieldd2 == 'v') {
            $inscrit = $web[15];
        } elseif($forum[7] == '') {
            $inscrit = '';
        } elseif($fieldd2 >= $max[3]) {
            $inscrit = '<i class="frm-rank5" title="'.$nbrpostmsg.'"></i>';
        } elseif($fieldd2 >= $max[2]) {
            $inscrit = '<i class="frm-rank4" title="'.$nbrpostmsg.'"></i>';
        } elseif($fieldd2 >= $max[1]) {
            $inscrit = '<i class="frm-rank3" title="'.$nbrpostmsg.'"></i>';
        } elseif($fieldd2 >= $max[0]) {
            $inscrit = '<i class="frm-rank2" title="'.$nbrpostmsg.'"></i>';
        } else {
            $inscrit = '<i class="frm-rank1" title="'.$nbrpostmsg.'"></i>';
        }
        if ($page[23] != 'none') {
            if($fieldc2 == '') {
                $fieldc2 = '<i class="ava-00 ava-th" title="'.$web[305].'"></i>';
            } else {
                if (is_file(MSGREP.$author.DBEXT)) {
                    $userdb  = ReadDBFields(MSGREP.$author.DBEXT);
                    if (!empty($userdb[1][16]) || $userdb[1][9] != 'ava-00') {
                        if ($userdb[1][9] == 'ava-00' && !empty($userdb[1][16])) 
                            $fieldc2 = '<img class="ava-th" src="'.$userdb[1][16].'" alt="'.$author.'" title="'.$author.'" />';
                        else 
                            $fieldc2 = '<i class="'.$userdb[1][9].' ava-th" title="'.$author.'"></i>';
                    }
                    elseif ($userdb[1][9] == 'ava-00') {
                        $fieldc2 = '<i class="ava-00 ava-th" title="'.$author.'"></i>';
                    }
                } 
                else 
                    $fieldc2 = '<i class="ava-00 ava-th" title="'.$author.'"></i>';
            }
        }
        $out .= '
          <div class="">'.$web[7].' '.FormatDate($creadate).'</div>
          <div class="">'.$web[6].' <b>'.addslashes($author).'</b></div>
          <div class="my-2">'.($page[23] != 'none' ? $fieldc2 : '&nbsp;').'</div>
        </div>
        <div class="col-md-12 mt-2">';
        $rank     = ReadDBFields(ADMINS);
        $modadmin = '';
        for ($y = 0; $y < count($rank); $y++) 
            if ($rank[$y][0] == $userprefs[1] && $rank[$y][2] == 'on') {
                $modadmin = 'yes';
                break;
            }
        if ($userprefs[1] == $serviz[31] || $userprefs[1] == $serviz[176]) $modadmin = 'yes';
        if ($author == $serviz[31] || $author == $serviz[176]) $out .= '
          <div class="py-2">'.$web[418].'</div>';
        elseif (is_file(CHEMIN.'admin/redac/'.$author.'.inc')) {
            include CHEMIN.'admin/redac/'.$author.'.inc';
            if (!empty($drtuser[30])) $out .= '
          <div class="py-2">'.$drtuser[30].'</div>';
            else $out .= '
          <div class="py-2">&nbsp;</div>';
        } 
        else {
            for ($y = 0; $y < count($rank); $y++) {
                if ($rank[$y][0] == $author) {
                    switch ($rank[$y][1]) {
                    case 'admin' :
                        $out .= (empty($rank[$y][3]) ? $web[418] : $rank[$y][3]).'<br />';
                        break;
                    case 'modo' :
                        $out .= (empty($rank[$y][3]) ? $web[420] : $rank[$y][3]).'<br />';
                        break;
                    case 'redac' :
                        $out .= (empty($rank[$y][3]) ? $web[419] : $rank[$y][3]).'<br />';
                        break;
                    case 'webm' :
                        $out .= (empty($rank[$y][3]) ? $web[11] : $rank[$y][3]).'<br />';
                        break;
                    }
                }
            }
        }
        $out .= $inscrit;
        $url1 = $site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[14].'-1-'.TYP_THREAD.'-'.'maj'.'-'.$thread.'-'.$fileid 
            : $site['PG'].'.php?lng='.$lng.'&typ='.TYP_THREAD.'&amp;mod=maj&amp;num='.$thread.'&amp;pg='.$fileid;
        $url2 = $site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[14].'-1-'.TYP_THREAD.'-'.'stop'.'-'.$thread.'-'.$fileid 
            : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_THREAD.'&amp;mod=stop&amp;num='.$thread.'&amp;pg='.$fileid;
        $url3 = $site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[14].'-1-'.TYP_THREAD.'-'.'raz'.'-'.$thread.'-'.$fileid 
            : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_THREAD.'&amp;mod=raz&amp;num='.$thread.'&amp;pg='.$fileid;
        $url4 = $site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[14].'-1-'.TYP_THREAD.'-'.'lock'.'-'.$thread.'-'.$fileid 
            : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_THREAD.'&amp;mod=lock&amp;num='.$thread.'&amp;pg='.$fileid;
        $url5 = $site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[14].'-1-'.TYP_THREAD.'-'.'top'.'-'.$thread.'-'.$fileid 
            : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_THREAD.'&amp;mod=top&amp;num='.$thread.'&amp;pg='.$fileid;
        $url6 = $site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[14].'-1-'.TYP_THREAD.'-'.'up'.'-'.$thread.'-'.$fileid 
            : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_THREAD.'&amp;mod=up&amp;num='.$thread.'&amp;pg='.$fileid;
        $url7 = $site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[14].'-1-'.TYP_THREAD.'-'.'move'.'-'.$thread.'-'.$fileid 
            : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_THREAD.'&amp;mod=move&amp;num='.$thread.'&amp;pg='.$fileid;
        switch ($st[0]) {
        case 'c':
        case 's':
            if ($modadmin == 'yes') {
                $out .= '&nbsp;
          <a href="'.$url1.$z2.'" title="'.$web[369].'">
            <i class="far fa-edit"></i>
          </a>&nbsp;
          <a href="'.$url3.$z2.'" title="'.$web[621].'">
            <i class="frm-unlock"></i>
          </a>';
            }
            break;
        case 't':
        case 'u':
            if ($modadmin == 'yes') {
                $out .= '
          &nbsp;<a href="'.$url1.$z2.'" title="'.$web[369].'"><i class="far fa-edit"></i></a>
          &nbsp;<a href="'.$url2.$z2.'" title="'.$web[390].'"><i class="frm-lock"></i></a>
          &nbsp;<a href="'.$url3.$z2.'" title="'.$web[621].'"><i class="frm-unlock"></i></a>';
            }
            elseif ($userprefs[1] == $author) {
                $out .= '
          &nbsp;<a href="'.$url1.$z2.'" title="'.$web[369].'"><i class="far fa-edit"></i></a>';
                $out .= '
          &nbsp;<a href="'.$url4.$z2.'" title="'.$web[363].'"><i class="frm-lock"></i></a>';
            }
            break;
        default:
            if ($modadmin == 'yes') {
                $out .= '
          &nbsp;<a href="'.$url1.$z2.'" title="'.$web[369].'"><i class="far fa-edit"></i></a>
          &nbsp;<a href="'.$url2.$z2.'" title="'.$web[390].'"><i class="frm-lock"></i></a>';
                if ($forum[6] != '') {
                    $out .= '
	      &nbsp;<i class="frm-lockup c-pointer" title="'.$web[364].'" onmouseup="cache(\'moveto\'); montre(\'timetop\');"></i>
          <div id="timetop" class="quest position-absolute text-center thtimetop" style="display:none;">
            <form name="topval" action="'.$url5.$z2.'" method="post">
              '.$web[622].'
               <input class="form-control texte m-auto w-20" type="text" name="forum6" value="'.$forum[6].'" />
              <div class="text-center">'.SubmitButton($web[52]).'</div>
              <div class="float-right mt-n4">
                <i class="far fa-caret-square-up" title="'.$web[57].'" onmouseup="cache(\'timetop\')"></i>
              </div>
              <div class="clearfix"></div>
            </form>
          </div>';
                }
                $out .= '
          &nbsp;<i class="frm-moveto c-pointer" title="'.$web[632].'" onmouseup="cache(\'timetop\'); montre(\'moveto\');"></i>
          <div id="moveto" class="quest position-absolute text-center thmoveto" style="display:none;">
            <form name="movetoform" action="'.$url7.$z2.'" method="post">
	    '.$web[633].' 
              <select name="tocat" onchange="this.form.submit();" class="form-control m-auto thtocat">';
                for ($k = 0; $k < count($dbworkcat); $k++) {
                    $out .= '
                <option value="'.$dbworkcat[$k][0].'"'.Selected($dbworkcat[$k][0] == $this->cat).'>'.$dbworkcat[$k][1].'</option>';
                }
                $out .= '
              </select>
              <div class="float-right mt-n3">
                <i class="far fa-caret-square-up" title="'.$web[57].'" onmouseup="cache(\'moveto\')"></i>
              </div>
              <div class="clearfix"></div>
            </form>
          </div>';
            }
            elseif ($userprefs[1] == $author) {
                $out .= '
          &nbsp;<a href="'.$url1.$z2.'" title="'.$web[369].'"><i class="far fa-edit"></i>';
                $out .= '
          &nbsp;<a href="'.$url4.$z2.'" title="'.$web[363].'"><i class="frm-lock" title="'.$web[363].'"></i></a>';
                if ($forum[5] != '') {
                    $out .= '
          &nbsp;<a href="'.$url6.$z2.'" title="'.$web[364].'"><i class="frm-lockup" title="'.$web[364].'"></i></a>';
                }
            }
        }
		$mrposline = stripos($fieldc1, '<p class="text-right thsign">');
		if (is_numeric($mrposline) && $mrposline > 0)
			$fieldc1 = str_replace('<p class="text-right thsign">', '<p class="text-end thsign">', $fieldc1);
        $out .= '
        </div>
      </div>
    </div>
    <div class="col-md-9 px-0 pt-2">
      <div class="rep text-left mx-1 thbodycontentW"><br />'.$fieldc1.'</div>
      <div class="text-left thbodybottom">';
        if ($prt == 0 && $serviz[107] == 'on' && empty($prt)) {
            include CONFIGREP.'sncode.inc';
            if (!empty($coders))
                $out .= '
	    <div class="thcoders">'.$coders.'</div>';
        }
        $out .= '
	    <div class="w-100">';
        if ($fieldd1 == 'on') $out .= 
		    displayWriteEmail(TYP_THREAD, $author, $email, ' <i class="far fa-envelope m-2 mt-3 align-middle themail" title="'.$web[173].' '.$author.'"></i>');
        if ($fieldweb != '') $out .= '
          <a href="'.$fieldweb.'" target="_blank" title="">
            <i class="fas fa-globe m-2 mt-3 align-middle" title="'.$web[304].' '.$author.'"></i>
          </a>';
        if (empty($prt)) $out .= 
            displayQuickConfig(NULL, $fileid, 'forum&amp;form=2&amp;id='.$fileid, 'forum&amp;act=i&amp;id='.$fileid, 'forum&amp;del='.$fileid, 23);
        $out .= '
	      <div class="float-right m-2">';
        if ($st[0] != 'c') $out .= '
            <a class="mr-3" title="" href="'.($site['URLR'] == 'on' ? 
                $lng.'-'.$urlrw[14].'-2-'.TYP_THREAD.'-'.$thread.'-'.$fileid.$z2 : 
                $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_THREAD.'&amp;num='.$thread.'&amp;pg='.$fileid.$z2).'">
              <i class="frm-reponse align-middle m-2 thimgrepW" title="'.$web[65].'"></i>
		    </a>';
        else $out .= '
            <i class="frm-rep-lock align-middle m-2 thimgrepW" title="'.$web[366].$web[368].'"></i>';
        $out .= '
            <a href="#top" title=""><i class="far fa-caret-square-up m-2 align-middle" title="'.$web[136].'"></i></a>&nbsp;&nbsp;
	      </div>
          <div class="clearfix"> </div>
	    </div>';
        $out .= '
      </div>
    </div>
  </div>';
        $dbder = ReadDBFields(DBFORUM);
        for ($i = 0; $i < count($dbder); $i++) {
            if($fielda1 == $dbder[$i][1] && $dbder[$i][7] > 3 && empty($prt)) {
                $idder = ceil(count($dbthrd) / intval($serviz[20]));
                $out  .= '
  <form 
    action="'.($site['URLR'] == 'on' ? 
      $lng.'-'.$urlrw[5].'-5-'.$this->pg.'-'.$this->cat.'-'.$idder.'#R'.$dbder[$i][7] : 
      $site['TH'].'.php?lng='.$lng.'&amp;pg='.$this->pg.GetOptionnalArg('cat', $this->cat).GetOptionnalArg('id', $idder).'#R'.$dbder[$i][7]).'" 
    method="post">
    <p class="text-right">
      '.SubmitButton($web[283]).'
    </p>
  </form>';
                break;
            }
        }
        if (empty($this->id)) $this->id = 1;
       if (is_array($dbthrd)) {
			if (count($dbthrd) > 0)
			{
				if (3 == abs($prt)) {
					$this->id = 1;
					$serviz[20] = 99999;
				}
				$out .= GetNavBar(($site['URLR'] == 'on' 
						? $lng.'-'.$urlrw[5].'-5-'.$this->pg.'-'.$this->cat.'-' 
						: $site['TH'].'.php?lng='.$lng.'&amp;pg='.$this->pg.GetOptionnalArg('cat', $this->cat).'&amp;id='), 
						count($dbthrd), $this->id, $serviz[20], $z2);
				for ($i = $serviz[20]*($this->id-1); $i < Min($serviz[20]*$this->id, count($dbthrd)); $i++) {
					ReadDoc($dbthrd[$i][3]);
					if ($fieldd2 != 'v' && $fieldd2 != '') {
						$nbrpostmsg = $fieldd2.' '.($fieldd2 > 1 ? $web[335] : $web[334]);
					} else {
						$nbrpostmsg='';
					}
					if ($fieldd2=='v' || !FileDBExist(MSGREP.$author.DBEXT)) {
						$inscrit = $web[15];
					} elseif($forum[7] == '') {
						$inscrit = '';
					} elseif($fieldd2 >= $max[3]) {
						$inscrit = '<i class="frm-rank5" title="'.$nbrpostmsg.'"></i>';
					} elseif($fieldd2 >= $max[2]) {
						$inscrit = '<i class="frm-rank4" title="'.$nbrpostmsg.'"></i>';
					} elseif($fieldd2 >= $max[1]) {
						$inscrit = '<i class="frm-rank3" title="'.$nbrpostmsg.'"></i>';
					} elseif($fieldd2 >= $max[0]) {
						$inscrit = '<i class="frm-rank2" title="'.$nbrpostmsg.'"></i>';
					} else {
						$inscrit = '<i class="frm-rank1" title="'.$nbrpostmsg.'"></i>';
					}
					if ($page[23] != 'none') {
						if($fieldc2 == '') {
							$fieldc2 = '<i class="ava-00 ava-th" title="'.$web[305].'"></i>';
						} else {
							if (is_file(MSGREP.$author.DBEXT)) {
								$userdb  = ReadDBFields(MSGREP.$author.DBEXT);
								if (!empty($userdb[1][16]) || $userdb[1][9] != 'ava-00') {
									if ($userdb[1][9] == 'ava-00' && !empty($userdb[1][16])) 
										$fieldc2 = '<img class="ava-th" src="'.$userdb[1][16].'" alt="'.$author.'" title="'.$author.'" />';
									else 
										$fieldc2 = '<i class="'.$userdb[1][9].' ava-th" title="'.$author.'"></i>';
								}
								elseif ($userdb[1][9] == 'ava-00') {
									$fieldc2 = '<i class="ava-00 ava-th" title="'.$author.'"></i>';
								}
							} 
							else 
								$fieldc2 = '<i class="ava-00 ava-th" title="'.$author.'"></i>';
						}
					}
					$out .= '
	  <a id="R'.$fielda2.'"></a>
	  <div class="row w-100 thbody">
		<div class="col-md-3 my-2 rep text-center">
		  <div class="row">
			<div class="col-md-12">';
					$newdate = date('YmdHi', time() - (86400 * $forum[8]));
					if ($newdate < $creadate) $out .= '
			  <i class="frm-msg_n" title="'.$web[171].'"></i>';
					$out .= '
			  <div><b>'.$web[68].' '.$fielda2.'</b></div>
			  <div>--------</div>
			  <div>'.$web[7].' '.FormatDate($creadate).'</div>
			  <div>'.$web[6].' <b>'.addslashes($author).'</b></div>
			  <div class="my-2">'.($page[23] != 'none' ? $fieldc2 : '&nbsp;').'</div>
			</div>
			<div class="col-md-12">';
					if ($author == $serviz[31] || $author == $serviz[176]) 
						$out .= '
			  <div class="my-2">'.$web[418].'</div>';
					elseif (is_file(CHEMIN.'admin/redac/'.$author.'.inc')) {
						include CHEMIN.'admin/redac/'.$author.'.inc';
						if (!empty($drtuser[30])) $out .= '
			  <div class="my-2">'.$drtuser[30].'</div>';
					} 
					else {
						for ($y = 0; $y < count($rank); $y++) {
							if ($rank[$y][0] == $author) {
								switch ($rank[$y][1]) {
								case 'admin' :
									$out .= $web[418].'<br />';
									break;
								case 'modo' :
									$out .= $web[420].'<br />';
									break;
								case 'redac' :
									$out .= $web[419].'<br />';
									break;
								case 'webm' :
									$out .= $web[11].'<br />';
									break;
								}
							}
						}
					}
					$out .= $inscrit;
					if ($st[0] != 'c' && $st[0] != 's') {
						if ($author == $userprefs[1] || $modadmin == 'yes' || $serviz[31] == $userprefs[1] || $serviz[176] == $userprefs[1]) {
							if ($st[0] != 'c' && $st[0] != 's') {
								$url = $site['URLR'] == 'on' 
								  ? $lng.'-'.$urlrw[14].'-1-'.TYP_THREAD.'-maj-'.$thread.'-'.$fileid.$z2 
								  : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_THREAD.'&amp;mod=maj&amp;num='.$thread.'&amp;pg='.$fileid.$z2;
								$out .= '
			  &nbsp;<a href="'.$url.'" title="'.$web[369].'"><i class="far fa-edit"></i></a>';
							}
						}
					}
					$out .= '
			</div>
		  </div>';
			$mrposline = stripos($fieldc1, '<p class="text-right thsign">');
			if (is_numeric($mrposline) && $mrposline > 0)
				$fieldc1 = str_replace('<p class="text-right thsign">', '<p class="text-end thsign">', $fieldc1);              
			$fieldc1 = stripslashes($fieldc1);

					$out .= '
		  </div>
		  <div class="col-md-9 px-0 pt-2">
			<div class="rep text-left mx-1 thbodycontentW"><br />'.$fieldc1.'</div>
			<div class="text-left thbodybottom">';
					if ($fieldd1 == 'on') $out .= 
						displayWriteEmail('thread'.$i, $author, $email, ' <i class="far fa-envelope m-2 mt-3 align-middle themail" title="'.$web[173].' '.$author.'"></i>');
					if ($fieldweb != '') $out .= '
			  <a href="'.$fieldweb.'" target="_blank" title="">
				<i class="fas fa-globe ml-2 mt-3 align-middle thimgsiteW" title="'.$web[304].' '.$author.'"></i>
			  </a>';
					if (empty($prt)) $out .= 
						displayQuickConfig(NULL, $fileid, 'forum&amp;form=2'.GetOptionnalArg('id', $fileid), 
						  'forum&amp;act=i'.GetOptionnalArg('id', $fileid), 'forum'.GetOptionnalArg('del', $fileid), 23);
					$out .= '
			  <div class="float-right m-2">';
					if ($st[0] != 'c') $out .= '
				<a class="mr-3" title="" href="'.($site['URLR'] == 'on' 
					? $lng.'-'.$urlrw[14].'-2-'.TYP_THREAD.'-'.$thread.'-'.$fileid.$z2 
					: $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_THREAD.'&amp;num='.$thread.'&amp;pg='.$fileid.$z2).'">
				  <i class="frm-reponse m-2 align-middle thimgrepW" title="'.$web[65].'"></i> 
				</a>';
			else $out .= '
				<i class="frm-rep-lock m-2 align-middle thimgrepW" title="'.$web[366].$web[368].'"></i>';
			$out .= '
				<a href="#top" title=""><i class="far fa-caret-square-up align-middle" title="'.$web[136].'"></i></a>&nbsp;&nbsp;
			  </div>
			  <div class="clearfix"></div>
			</div>
		  </div>
		</div>';
				}
			}
		} // pas vide
		$nbrthcount = '';
       if (is_array($dbthrd))  $nbrthcount = count($dbthrd) > 0;
		$out .= GetNavBar(($site['URLR'] == 'on' 
				  ? $lng.'-'.$urlrw[5].'-5-'.$this->pg.'-'.$this->cat.'-' 
				  : $site['TH'].'.php?lng='.$lng.'&amp;pg='.$this->pg.GetOptionnalArg('cat', $this->cat).'&amp;id='), 
				  $nbrthcount, $this->id, $serviz[20], $z2);
		if (empty($this->fid)) $this->fid = 1;
				$out .= '
		  <div class="w-100 text-left thmsgicons">
			<div>
			  <i class="frm-msg_a valign-4" title="'.$web[367].'"></i> '.$web[366].$web[367].' &nbsp;
			  <i class="frm-msg_c valign-4" title="'.$web[368].'"></i> '.$web[366].$web[368].' &nbsp;
			  <i class="frm-msg_t valign-4" title="'.$web[370].'"></i> '.$web[370].' &nbsp;
			  <i class="frm-msg_n valign-4" title="'.$web[371].'"></i> '.$web[371].' &nbsp;
			</div>
			<div>
			  <i class="far fa-edit" title="'.$web[334].'"></i> '.$web[369].' '.$web[334].' &nbsp;
			  <i class="frm-lock valign-1" title="'.$web[363].'"></i> '.$web[363].$web[366].' &nbsp;
			  <i class="frm-lockup valign-1" title="'.$web[364].'"></i> '.$web[364].$web[366].' &nbsp;'
			  .($userprefs[1] == $serviz[31] || $userprefs[1] == $serviz[176] ? '
			  <i class="frm-unlock valign-1" title="'.$web[621].'"></i> '.$web[621].$web[366].' &nbsp;
			  <i class="frm-moveto valign-1" title="'.$web[632].'"></i> '.$web[632].$web[366] : '').'
			</div>
		  </div>';
				if (empty($prt)) {
				$out .= '
		  <div class="w-100 text-right">';
					if (!empty($dbworkcat)) {
						$out .= '
			<div class="d-inline-block text-right mt-2 mr-3 thselcat">
			  <div class="d-inline-block">
				<a href="'.($site['URLR'] == 'on' 
					? $lng.'-'.$urlrw[5].'-31-'.$this->tconfig.$z2 
					: $site['FR'].'.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'" title="'.$web[130].'">
				  <i class="fas fa-folder-open"></i> &nbsp;<b>'.$this->nom_frth.'</b>
				</a>&nbsp;
			  </div>
			  <div class="d-inline-block">
				<form method="post" action="'.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[5].'-31'.$z2 : $site['FR'].'.php?lng='.$lng.$z2).'">
				  <select name="cat" onchange="this.form.submit();" class="form-control align-middle c-pointer">';
						foreach ($dbworkcat as $dbw) {
							$out .= '
					<option value="'.$dbw[0].'" '.Selected($dbw[0] == $this->cat).'>'.$dbw[1].'</option>';
						}
						$out .= '
				  </select>
				</form>
			  </div>
			</div>';
					}
					$out .= '
			<div class="d-inline-block thretbutton">
			  <form 
				action="'.($site['URLR'] == 'on' 
				  ? $lng.'-'.$urlrw[5].'-22-'.$this->cat.'-'.$this->fid.$z2 
				  : $site['FR'].'.php?lng='.$lng.GetOptionnalArg('cat', $this->cat).GetOptionnalArg('id', $this->fid).$z2).'" 
				method="post">
			  '.SubmitButton($web[70]).'
			  </form>
			</div>';
					$out .= '
		  </div>';
				}
				$out .= '
		</div>
		';
				return array($this->frtyp, $out);
    }
    
    public function THA_content() {
        global $userprefs, $web, $page, $serviz, $urlrw, $site, $lng, $prt, $z2;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, $fielda1, $fielda2, $fieldb1, 
               $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        $out    = '';
        $thread = 0;
        if (!empty($this->pg)) {
            $dbwork = SelectDBForumByID(ReadDBFields(DBFORUMARCH), $this->pg);
            if (count($dbwork) != 0) {
                $thread    = $dbwork[0][1];
                $this->cat = $dbwork[0][12];
            }
        }
        elseif (!empty($this->thrd)) {
            $dbwork = ReadDBFields(DBTHREADARCH);
            foreach ($dbwork as $dbw) {
                if ($dbw[1] == $thrd && $dbw[2] == '0') {
                    $thread    = $this->thrd;
                    $this->pg  = $dbw[3];
                    $this->cat = $dbw[8];
                }
            }
        }
		$dbthrd = array();
        if ($serviz[34] == 'on') $threadcounter = UpdateDocCounterArch($this->pg);
        $dbthrd = SelectDBThreadByThread(ReadDBFields(DBTHREADARCH),$thread);
        @sort($dbthrd);
        ReadDoc($this->pg, ARCHDBBASE);
        if ((CheckGroup($this->cat, $userprefs[1]) != 1 && !is_numeric($this->cat)) ||
            (preg_match('/^pr/i', $fieldb2) && (CheckGroup($fieldb2, $userprefs[1]) != 1))) {
            $href1 = $site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng;
            return array($this->frtyp, 1, $href1);
        }
        if (!empty($this->cat)) {
            $catxt   = getForumCatName($this->cat);
            $topmess = '<a href="'.($site['URLR'] == 'on' ? 
                $lng.'-'.$urlrw[5].'-6-'.$this->tconfig.$z2 : 
                'fortopicarch.php?lng='.$lng.'&tconfig='.$this->tconfig.$z2).'">'.$web[226].'</a> - <a href="'.($site['URLR'] == 'on' ? 
                    $lng.'-'.$urlrw[5].'-35-'.$this->cat.$this->fid.$z2 : 
                    'forumarch.php?lng='.$lng.GetOptionnalArg('cat', $this->cat).GetOptionnalArg('id', $this->fid).$z2).'">'.$catxt.'</a> - '.$web[63].$fielda1;
        }
        else 
            $topmess = '<a href="'.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[5].'-6-'.$this->tconfig.$z2 : 
                'fortopicarch.php?lng='.$lng.'&tconfig='.$this->tconfig.$z2).'">'.$web[226].'</a> - '.$web[63].$fielda1;
        $this->topmess = $page[9] == '' ? $topmess : '<i class="fab fa-forumbee float-right"></i>'.$topmess;
        $out .= '
<div class="bord tharchmain">
  <div class="table-responsive-md">
  <div class="d-table w-100">
    <div class="d-table-row">
      <div class="forum d-table-cell width25 tharchsubject">'.$web[63].$fielda1.'</div>
      <div class="forum2 d-table-cell width75">
        <b>'.$fieldb1.'</b>
        <br />
        &nbsp;&nbsp;&nbsp;'.$web[6].' ';
        if ($fieldd1 != 'on') $out .= 
            displayWriteEmail('threadarch1', $author, $email);
        else $out .= '
        <b>'.$author.'</b>';
		$mrposline = stripos($fieldc1, '<p class="text-right thsign">');
		if (is_numeric($mrposline) && $mrposline > 0)
			$fieldc1 = str_replace('<p class="text-right thsign">', '<p class="text-end thsign">', $fieldc1);
        $out .= '
      '.$web[7].' '.FormatDate($creadate).'
      </div>
    </div>
  </div>
  </div>
  <div class="table-responsive-md">
  <div class="d-table w-100">
    <div class="d-table-row">
      <div class="quest d-table-cell w-100">'.$fieldc1.'</div>
    </div>
  </div>
  </div>
</div>';
        if (empty($this->id)) $this->id = 1;
        if (!empty($dbthrd)) {
            if (3 == abs($prt)) {
                $this->id = 1;
                $serviz[20] = 99999;
            }
            for ($i = $serviz[20]*($this->id-1); $i < Min($serviz[20]*$this->id, count($dbthrd)); $i++) {
                ReadDoc($dbthrd[$i][3], ARCHDBBASE);
                $out .= '
<div class="bord trarchmain">
  <div class="table-responsive-md">
  <div class="d-table w-100">
    <div class="d-table-row">
      <div class="quest d-table-cell text-center">'.$web[68].$fielda2.'</div>
      <div class="quest d-table-cell width85">'.$web[6].' ';
                if ($fieldd1 != "on") {
                    $out .= displayWriteEmail('threadarch2'.$i, $author, $email);
                }
                else {
                    $out .= '<b>'.$author.'</b>';
                }
		$mrposline = stripos($fieldc1, '<p class="text-right thsign">');
		if (is_numeric($mrposline) && $mrposline > 0)
			$fieldc1 = str_replace('<p class="text-right thsign">', '<p class="text-end thsign">', $fieldc1);
                $out .= '
      '.$web[7].' '.FormatDate($creadate).'
      </div>
    </div>
  </div>
  </div>
  <div class="table-responsive-md">
  <div class="d-table w-100">
    <div class="d-table-row">
      <div class="rep d-table-cell" colspan="2">'.$fieldc1.'</div>
    </div>
  </div>
  </div>
</div>';
            }
        }
        if (count($dbthrd)>$serviz[20]) $out .= '
<hr />'.GetNavBar(($site['URLR'] == 'on' ? 
    $lng.'-'.$urlrw[5].'-52-'.$this->pg.'-'.$this->cat.'-' : 
    'threadarch.php?lng='.$lng.'&amp;pg='.$this->pg.GetOptionnalArg('cat', $this->cat).'&amp;id='), count($dbthrd), $this->id, $serviz[20], $z2);
        $out .= '
<p class="text-center">
  <a href="'.($site['URLR'] == 'on' ? 
    $lng.'-'.$urlrw[5].'-35-'.$this->cat.'-'.$this->fid.$z2 : 
    'forumarch.php?lng='.$lng.GetOptionnalArg('cat', $this->cat).GetOptionnalArg('id', $this->fid).$z2).'">'.$web[70].'</a>
</p>';
        return array($this->frtyp, $out);
    }
}
?>
