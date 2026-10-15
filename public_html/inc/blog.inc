<?php
/*******************************************************************************
 *   Blog
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

if (stristr($_SERVER['SCRIPT_NAME'], 'blog.inc')) {
    header('location:../index.php');
    die();
}
#[AllowDynamicProperties]
class GY_blog {
    public $tconfig;
    public $topmess;
    public $userprefs3;
    public $typ;
    public $txtart2;
    protected $pg;
    protected $id;
    protected $sel;
    protected $cat;
    protected $datej;
    protected $date;
    protected $smois;
    protected $sannee;
    protected $react;

    public function __construct() {
        global $nom, $menuico, $page, $lang, $lng, $web, $charset, $userprefs;
        static $nbox = 0;
        $this->tconfig    = $menuico[13] == 'ALL' ? import('tconfig', '', true, 0) : $menuico[13];
        $this->pg         = import('pg', '', true, '0');
        $this->id         = import('id');
        $this->react      = import('react');        
        $this->userprefs3 = $userprefs[3] == '' || $userprefs[3] == BOX_LEFT.BOX_RIGHT ? BOX_LEFT : $userprefs3;
        $this->sel        = import('sel', '', true, 'none');
        $this->cat        = import('cat', '', true, '0');
        $this->datej      = import('datej', '', true, '0');
        $this->smois      = import('smois');
        $this->sannee     = import('sannee');
        $date             = import('date', '', true, '0');
        $this->date       = $date == '0' && !empty($this->sannee) && !empty($this->smois) ? $this->sannee.$this->smois : $date;
        $topmess          = getLabel($nom[$lng == $lang[0] ? 42 : 43]);
        $topmess          = $page[9] == '' ? $topmess : '<i class="fas fa-blog float-right pr-2"></i>'.$topmess;
        $this->topmess    = !empty($this->datej) || !empty($this->date) ? $topmess.' - '.$web[399] : $topmess;
        $this->nbox       = $nbox++;
    }
    
    public function BLS_ok($pseudo, $lng) {
        global $site, $urlrw, $serviz, $web, $z2, $members;
        if ($serviz[53] != 'on') {
            $out = '
    <div class="text-center web342">'.$web[143];
            $href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng);
            $out .= '
      <p class="text-center">
        '.SubmitButton($web[135], 'self.location.href=\''.$href1.'\'').'
      </p>
    </div>';
            return array($this->typ, $out);
        }
        elseif (!isAccessGranted($members[15])) {
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
            return array($this->typ, $out);
        }
        elseif (!empty($this->pg)) {
            $dbwork = SelectDBFields(TYP_BLOG, 'a', $this->pg);
            $isdoc  = (count($dbwork) != 0);
            if ($isdoc) {
                $isdoc = isAuthorizedPublication(@$dbwork[0][5]);
                if (!isAccessGranted(@$dbwork[0][6])) return array('h0', '');
            }
            if (!$isdoc) {
                $dbwork = array();
                if ($site[10] != '') return array('h1', '');
                else return array('h2', '');
            }
        }
        return array('');
    }

    public function BLS_content() {
        global $lng, $lang, $drtuser, $page, $site, $serviz, $userprefs, $web, $urlrw, 
               $charset, $prt, $headinc, $titre, $xposbox, $z2;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, $fielda1, $fielda2, 
               $fieldb1, $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        $out         = '';
        $max_height  = isset($site[33]) && $site[33] > 0 && empty($prt) ? $site[33] : 0;
        $max_height2 = isset($site[36]) && $site[36] > 0 && empty($prt) ? $site[36] : 0;
        $xdbw        = array();
        $dbwork      = array();
        switch ($this->sel) {
        case 'pg': // cas billet unique ---------------------
            if (!empty($this->pg)) {
                $stitre = '';
                $xdbw   = SelectDBFields(TYP_BLOG, 'a', $this->pg);
                ReadDoc($this->pg);
            }
            break;
        case 'cat': // cas liste catégorie -----------------
            if (!empty($this->cat)) {
                $stitre = $this->cat;
                $dbwork = ReadDBFields(DBBLOG);
                if (!empty($dbwork)) {
                    $j = $lng == $lang[0]? 0 : 1;
                    for ($i = 0; $i < count($dbwork); $i++) {
                        if ($cat == strip_tags($dbwork[$i][0+$j]) && isAccessGranted($dbwork[$i][6]) && isAuthorizedPublication($dbwork[$i][5])) {
                            $xdbw[$i][0] = TYP_BLOG;
                            $xdbw[$i][2] = $dbwork[$i][4];
                        }
                    }
                }
            }
            break;
        case 'jour': // cas liste jour ---------------------
            if (!empty($this->datej)) {
                $stitre     = $web[403].substr(FormatDate($this->datej), 0, 10);
                $this->date = $this->datej;
                $dbwork     = ReadDBFields(DBBLOG);
                for ($i = 0; $i < count($dbwork); $i++) {
                    if (!isAccessGranted($dbwork[$i][6])) continue;
                    ReadDoc($dbwork[$i][4]);
                    $datebi = substr($creadate, 0, 8);
                    if ($datebi == $this->datej && isAuthorizedPublication($creadate)) {
                        $xdbw[$i][0] = TYP_BLOG;
                        $xdbw[$i][2] = $fileid;
                    }
                }
            }
            break;
        case 'mois': // cas liste mois ---------------------
            if (!empty($this->date)) {
                $stitre = $web[403].' '.substr(FormatDate($this->date), 1, 7);
                $dbwork = ReadDBFields(DBBLOG);
                for ($i = 0; $i < count($dbwork); $i++) {
                    if (!isAccessGranted($dbwork[$i][6])) continue;
                    ReadDoc($dbwork[$i][4]);
                    $datebi = substr($creadate, 0, 6);
                    if ($datebi == $this->date && isAuthorizedPublication($creadate)) {
                        $xdbw[$i][0] = TYP_BLOG;
                        $xdbw[$i][2] = $fileid;
                    }
                }
            }
            break;
        default: // cas liste globlale ---------------------
            $stitre = ' - '.$web[383];
            $dbwork = ReadDBFields(DBBLOG);
            foreach ($dbwork as $db) {
                if (isAccessGranted($db[6]) && isAuthorizedPublication($db[5])) {
                    $xdbw[] = array(TYP_BLOG, $db[5], $db[4], 'a');
                }
            }
        }
        unset($dbwork);
        @rsort($xdbw);
        if (empty($this->id)) $this->id = 1;
        $minblog = $serviz[55];
        $maxblog = Min(intval($serviz[55])*$this->id,count($xdbw));
        if (isset($prt) && 3 == abs($prt)) {
            $this->id = 1;
            $minblog  = 99999;
            $maxblog  = min($minblog, count($xdbw));
        }
        if (abs($prt) != 1) $out .= $this->topblog();
        $out .= '
    <div class="w-100">
          <div class="text-center headCenterBlog">';
        if ($serviz[54] == 'on' && empty($prt)) {
            $rssblogurl = $site[3].sprintf(RSSBLOG, $lng);
            $out .= '
            <style>
              .headCenterBlog .overpop { padding-top: 20px; }
              .overpop a i { margin-top: -20px; }
            </style>
            <script>
              $(document).ready(function(){
                $(\'.rssBlog\').popover({
                  title:"RSS",
                  content:"'.$web[398].' : <br /><b> '.$rssblogurl.'</b>",
                  trigger:"hover",
                  placement:"auto",
                  offset:"0 10 10 10",
                  html:true
                });
              });
            </script>
            <div class="overpop pt-0 pl-2">
              <a href="'.CHEMIN.'newsrss.php?lng='.$lng.'&url=blog&tconfig='.$this->tconfig.$z2.'" class="rssBlog float-left">
                <i class="fas fa-rss"></i>
              </a>
            </div>';                            
        }
        if ($site[30] != '2' && empty($prt)) $out .= '
            <form 
              name="postblog'.$this->nbox.'" 
              action="'.CHEMIN.($site['URLR'] == 'on' 
                ? $lng.'-'.$urlrw[14].'-'.TYP_BLOG.$z2 
                : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_BLOG.$z2).'" 
              method="post">
              <div class="text-center'.($serviz[54] == 'on' ? ' postBlog' : '').'">'.SubmitButton($web[386]).'</div>
            </form>';
        else $out .= '
            <div class="divnewline"></div>';
        $out .= '
          </div>';
        $out .= GetNavBar(($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[1].'-31-'.$this->sel.'-'.$this->pg.'-'.$this->cat.'-'.$this->datej.'-'.$this->date.'-' 
          : $site['BLS'].'.php?lng='.$lng.'&amp;sel='.$this->sel.'&amp;pg='.$this->pg.'&amp;cat='.$this->cat.
            '&amp;datej='.$this->datej.'&amp;date='.$this->date.'&amp;id='), 
          count($xdbw), $this->id, $minblog, $z2);
        $nbrnvisi = count($xdbw) > $site[31] ? $site[31] : count($xdbw);
        if (count($xdbw) == 0) { // Cas particuliers
            if ($this->sel == 'mois') $out .= '
            <p class="text-center font-weight-bold">'.$web[438].'</p>';
            else $out .= '
            <p class="text-center font-weight-bold">'.$web[439].($site[30] == '2'? '' : '<br />'.$web[440]).'</p>';
        }
        else {
            for ($i = $minblog*($this->id-1); $i < $maxblog; $i++) {
                ReadDoc($xdbw[$i][2]);
                $txt0 = $fileid;
                $img  = getNewUpdateDoc($creadate, $moddate, $serviz[138]);
                if ($lng == $lang[0]) {
                    $txt1    = $fieldb1;
                    $txtart2 = $fieldc1;
                }
                else {
                    $txt1    = $fieldb2;
                    $txtart2 = $fieldc2;
                }
                if (!empty($txtart2)) {
                    if (!empty($fieldd2)) 
                        $txt1 = '<img src="'.CHEMIN.'img/'.$fieldd2.'" 
                                      class="'.($serviz[153] == 'on' ? 'float-left' : 'float-right').'" 
                                      alt="'.$fieldd2.'"/>'.$txt1;
                    $out .= '
            <div class="bord">
              <div class="quest '.($serviz[153] != 'on' ? 'text-left' : 'text-right').'">
                <a href="'.$site[3].($site['URLR'] == 'on' 
                    ? $lng.'-'.$urlrw[1].'-4-'.$fileid.$z2 
                    : $site['BL'].'.php?lng='.$lng.'&amp;pg='.$fileid.$z2).'" title="'.$web[511].'">
                  '.$img.'<b>'.$txt1.'</b>
                </a> &nbsp;';
                    if ($serviz[114] != 'on') {
                        if ($fieldd1 == 'on' && !empty($email) && !empty($author)) 
                            $out .= '- &nbsp;'.$web[6].displayWriteEmail('blog'.$i, $author, $email);
                        elseif (!empty($author)) 
                            $out .= '- &nbsp;'.$web[6].'
                <b>'.$author.'</b>';
                    }
                    $out .= '
              </div>';
                    $txt3 = '
              <a id="billet'.$i.'"></a>
              <div id="blog3'.$i.'" class="rep blog3Blog">';
                    if (!empty($txtart2)) {
                        $txt3 .= '
<style>
.text2Blog {
  max-height: '.($max_height2 > 0 ? $max_height2.'px' : 'auto').';
  '.($max_height2 > 0 ? ' overflow: hidden;' : '').'
  padding: 4px;
}
</style>
                <div class="rep text2Blog">
                  '.$txtart2.'
                </div><div class="clearfix"></div>';
                    if ($max_height2 > 0) $txt3 .= '
                <p class="text-right"> 
                  <a href="javascript:ActiveMenu(\'blog3\',\'blog2\',0,'.$nbrnvisi.','.$i.')" class="box" title="'.$web[296].'">'.$web[296].' 
                    <i class="far fa-plus-square"></i>
                  </a>
                </p>';
                        $txt3 .= '
              </div>';
                    if ($max_height2 > 0) $txt3 .= '
              <div id="blog2'.$i.'" class="rep blog2Blog" style="display:none;">'.$txtart2.'<div class="clearfix"></div>
                <p class="text-right c-pointer">
                  <a href="#billet'.$i.'" title="'.$web[57].'" onclick="cache(\'blog2'.$i.'\');montre(\'blog3'.$i.'\')">
                    '.$web[57].' <i class="far fa-minus-square"></i>
                  </a>
                </p>
              </div>';
                    }
                    $out .= $txt3;
                    if ($prt == 0 && $serviz[104] == 'on') {
                        include CONFIGREP.'sncode.inc';
                        if (!empty($coders))
                            $out .= '
              <div class="text-left codersBlog">'.$coders.'</div>';
                    }
                    $out .= '
              <div class="text-right align-middle footerItemBlog">
                <form name="formblog'.$i.'" 
                      action="'.CHEMIN.($site['URLR'] == 'on' 
                        ? $lng.'-'.$urlrw[1].'-4-'.$fileid.$z2 
                        : $site['BL'].'.php?lng='.$lng.'&amp;pg='.$fileid.$z2).'" 
                      method="post">
                  <b>'.$web[394].'</b>'.FormatDate($creadate).'&nbsp;';
                    if ($serviz[57] != "") {
                        $dbcom = ReadDBFields(DBREBLOG);
                        $nbcom = 0;
                        for ($k = 0; $k < count($dbcom); $k++) {
                            if ($dbcom[$k][1] == $txt0) {
                                ReadDoc($dbcom[$k][0]);
                                if ($status == 'a') {
                                    $nbcom++;
                                }
                            }
                        }
                        $fileid = $txt0;
                        if ($nbcom == 0) $nbcom = $web[430].' '.$web[379];
                        elseif ($nbcom == 1) $nbcom = $nbcom.' '.$web[379];
                        else $nbcom = $nbcom.' '.$web[379].'s';
                        if ($serviz[57] != '2')
                            $out .= '
                  <a href="'.CHEMIN.($site['URLR'] == 'on' 
                      ? $lng.'-'.$urlrw[1].'-4-'.$fileid.$z2 
                      : $site['BL'].'.php?lng='.$lng.'&amp;pg='.$fileid.$z2).'" title="'.$nbcom.' - '.$web[297].' '.$web[385].'">
                    <span class="commentBlog"> - '.$nbcom.' -  </span>
 			        <i class="far fa-comment align-middle commentBlogW" title="'.$nbcom.' - '.$web[297].' '.$web[385].'"></i>
                  </a>';
                    }
                    $out .= '
                  <input type="hidden" name="cat" value="'.$this->cat.'"/>
                  <input type="hidden" name="sel" value="'.$this->sel.'"/>';
                    $rank     = ReadDBFields(ADMINS);
                    $modadmin = '';
                    for ($y = 0; $y < count($rank); $y++) {
                        if ($rank[$y][0] == $userprefs[1] && $rank[$y][2] == 'on') {
                            $modadmin = 'yes';
                            break;
                        }
                    }
                    if (($author == $userprefs[1] || $modadmin == 'yes' || $serviz[31] == $userprefs[1] || $serviz[176] == $userprefs[1]) && $site[30] != '2') {
                        $urlb = $site['URLR'] == 'on' 
                          ? $lng.'-'.$urlrw[14].'-11-'.TYP_BLOG.'-maj-'.$fileid.$z2 
                          : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_BLOG.'&amp;mod=maj&amp;pg='.$fileid.$z2;
                        $out .= '
                  &nbsp;| <a href="'.$urlb.'" title="'.$web[369].'"><i class="far fa-edit"></i></a>&nbsp;';
                    }
                    if (empty($serviz[76]) && empty($prt)) {
                        $out .= '| 
                  <a href="'.CHEMIN.($site['URLR'] == 'on' 
                      ? $lng.'-'.$urlrw[1].'-41-'.$fileid.'--1'
                      : $site['BL'].'.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;prt=-1').'" target="_blank">
                    <i class="far fa-eye align-middle" title="'.$web[264].'"></i>
                  </a>&nbsp;
                  <a href="'.CHEMIN.($site['URLR'] == 'on' 
                      ? $lng.'-'.$urlrw[1].'-41-'.$fileid.'-1' 
                      : $site['BL'].'.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;prt=1').'" target="_blank">
                    <i class="fas fa-print align-middle" title="'.$web[22].'"></i>
                  </a>&nbsp;';
                    }
                    if (empty($prt)) $out .= '
                  <a href="#top">| <i class="fas fa-arrow-up align-middle"></i></a>';
                        $out .= '
                </form>
              </div>
            </div>';
                    if (empty($prt)) $out .= '
            <div class="qcItemBlog">
              '.displayQuickConfig(null, $xdbw[$i][2], 'blog&amp;form=2&amp;id='.$xdbw[$i][2], 
                  'blog&amp;act=i&amp;id='.$xdbw[$i][2], 'blog&amp;del='.$xdbw[$i][2], 39).'
            </div>';
                }
            }
            $out .= GetNavBar(($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[1].'-31-'.$this->sel.'-'.$this->pg.'-'.$this->cat.'-'.$this->datej.'-'.$this->date.'-' 
              : $site['BLS'].'.php?lng='.$lng.'&amp;sel='.$this->sel.'&amp;pg='.$this->pg.'&amp;cat='.$this->cat.
                '&amp;datej='.$this->datej.'&amp;date='.$this->date.'&amp;id='), 
              count($xdbw), $this->id, $minblog, $z2);
        }
        if ($site[30] != "2") {
                $out .= '
            <form 
              action="'.CHEMIN.($site['URLR'] == 'on' 
                ? $lng.'-'.$urlrw[14].'-'.TYP_BLOG.$z2 
                : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_BLOG.$z2).'" 
              method="post">
              <div class="text-center">'.SubmitButton($web[386]).'</div>
            </form>';
        }
        $out .= '
    </div>
    ';
        $this->topmess .= $stitre;
        $this->txtart2  = $out;
        return true;
    }
    
    private function topblog() {
        global $lng, $lang, $nom, $site, $urlrw, $serviz, $userprefs, $members, $web, $z2, $tconfig;
        $out = '';
        if (($serviz[28] == 'on' || $serviz[24] == 'on') && $serviz[181] != 'on') {
            $out .= '
<div class="row p-0 m-0 mt-3">';
            $dossier_users = MSGREP;
            $file_users    = opendir($dossier_users);
            $nbr_membres   = 0;
            while ($nomfichier = readdir($file_users)) 
                if (substr($nomfichier,-3) == "dtb") $nbr_membres++;
            closedir($file_users);
            if ($serviz[28] == 'on' && !empty($nbr_membres)) {
                $out .= '
  <div class="col-md-4 p-0">';
                if (!empty($userprefs[1])) {
                    if ($serviz[18] != 'on') $out .= '
    <div class="d-inline-block pb-3">
      <form 
        name="usermsg" 
        action="'.CHEMIN.($site['URLR'] =='on' 
          ? $lng.'-'.$urlrw[13].'-'.$this->tconfig.$z2 
          : 'tinymsg.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'" 
        method="post" >
        <input type="hidden" name="action" value="3" />
        '.SubmitButton($web[318]).'
      </form>
    </div>';
                    $nbrmsg = array();
                    if (FileDBExist(MSGREP.$userprefs[1].DBEXT)) {
                        $dbmsg = ReadDBFields(MSGREP.$userprefs[1].DBEXT);
                        if ($dbmsg[0][0] == $userprefs[7]) 
                            for ($i = 2; $i < count($dbmsg); $i++) 
                                if ($dbmsg[$i][3] == "new") $nbrmsg[] = $dbmsg[$i][3];
                        if (count($nbrmsg)!= 0) {
                            $out .= '
    <div class="d-inline-block userWaveBlog">
      <a class="box" href="'.CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[13].'-3-3-'.$this->tconfig.$z2 
          : 'tinymsg.php?lng='.$lng.'&amp;action=3&amp;tconfig='.$this->tconfig.$z2).'">
        <i class="far fa-envelope blink display-4 text-success align-middle" title="'.$web[319].'"></i>
      </a>
    </div>';
                        }
                    }
                } elseif ($members[19] != '0' && $members[19] != '3') 
                    $out .= '
    <div class="d-inline-block pb-3">
      <form 
        name="userpref" 
        action="'.CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[15].'-5-new-'.$this->tconfig.$z2 
          : $site['USR'].'.php?lng='.$lng.'&amp;uuser=new&amp;tconfig='.$this->tconfig.$z2).'" 
          method="post">
        '.SubmitButton($web[160]).'
      </form>
    </div>';
                    if ($nbr_membres > 0 && empty($userprefs[1]))
                        $out .= '
    <div class="d-inline-block">
	  <form 
        name="userconnect" 
        action="'.CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[3].'-0-'.$this->tconfig.$z2 
          : 'connect.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'" 
        method="post">
	    '.SubmitButton($web[373]).'
	  </form>
    </div>';
                $out .= '
  </div>';
            }
            $out .= '
  <div class="col-md-2 p-0">';
            if ($nbr_membres > 0) $out .= '
	<div class="d-inline-block text-center membersTopBlog">';
            if ($serviz[158] == '') {
                $text_nbr_membres = $web[$nbr_membres > 1 ? 321: 322];
                $out .= '
	  <i class="fas fa-users" title="'.$web[320].'"></i>&nbsp;<b>'.$nbr_membres.' '.$text_nbr_membres.'</b>'."\n";
            }
            $out .= '
	</div>
  </div>';
            $out .= '
  <div class="col-md-4 p-0">';
            if ($serviz[159] == '') {
                $regusers    = ReadDBFields(DBIPSTATS);
                $usersonline = array();
                for ($i = 0; $i < count($regusers); $i++) 
                    if (!empty($regusers[$i][2])) $usersonline[] = $regusers[$i][2];
                @sort($usersonline);
                $uniqueusers = array();
                if (!empty($usersonline)) $uniqueusers[] = $usersonline[0];
                for ($i = 1; $i < count($usersonline); $i++) 
                    if ($usersonline[$i] != $usersonline[$i-1]) $uniqueusers[] = $usersonline[$i];
                if (empty($usersonline)) $out .= '
	<div class="d-inline-block align-top text-center membersTopBlog">
      <b>'.$web[175].'</b> '.$web[176].'
    </div>';
                else {
                    $out .= '
	<div class="d-inline-block text-center membersTopBlog">
      <b>'.$web[175].'</b> '.count($uniqueusers).'&nbsp;';
                    if ($userprefs[1] != "") {
                        $from = $userprefs[1];
                        $out .= '
		&nbsp;<i class="fas fa-caret-right connectedImg" title="'.$web[321].'"></i>'.$userprefs[1].' &nbsp;';
                    }
                    if (count($uniqueusers) > '1' && $serviz[18] != 'on') {
                        $out .= '
      <form name="tinysend" 
            method="post" 
            action="'.CHEMIN.($site['URLR'] =='on' 
              ? $lng.'-'.$urlrw[13].'-'.$this->tconfig.$z2 
              : 'tinymsg.php?lng='.$lng.'&amp;tconfig='.$tconfig.$z2).'">
		<input type="hidden" name="from" value="'.@$from.'" />
        <input type="hidden" name="action" value="1" />
        <div class="d-inline-block">&nbsp;+&nbsp;</div>
		<div class="d-inline-block">
          <select name="to" onchange="this.form.submit" class="form-control align-middle" style="max-width: 128px;">';
                        for ($i = 0; $i < count($uniqueusers); $i++) 
                            if ($uniqueusers[$i] != $userprefs[1]) $out .= '
		    <option value="'.$uniqueusers[$i].'">&nbsp;'.$uniqueusers[$i].'</option>';
                        $out .= '
		  </select>
        </div>';
                        if ($userprefs[1] != '' && count($uniqueusers) > '1') $out .= '
	    <div class="d-inline-block">
          <button type="submit" class="mailsubmitTopForum" style="width: 56px;" title="'.$web[214].'"><i class="far fa-envelope"></i></button>
        </div>';
                        $out .= '
	  </form>';
                    }
                    $out .= '
	</div>';
                }
            }
            $out .= '
  </div>
  <div class="col-md-2 p-0 text-right searchTopBlog">';
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
	  <input name="searchin" type="hidden" value="'.TYP_BLOG.'" />
      <div class="input-group mb-3 m-auto">
        <input name="search" class="texte form-control" type="text" placeholder="'.$web[58].'" value="" />
        <div class="input-group-append c-pointer" onclick="javascript:what.submit();">
          <span class="input-group-text">
            <i class="fas fa-search" title="'.$web[58].' '.getLabel($nom[$lng == $lang[0] ? 42 : 43]).'"></i>
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
    
    public function BL_content() {
        global $lng, $lang, $drtuser, $site, $serviz, $userprefs, $web, $urlrw, $charset, $prt, $z2;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, $fielda1, $fielda2, 
               $fieldb1, $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        $out   = '';
        $dbw   = SelectDBFields(TYP_BLOG, 'a', $this->pg);
        $isdoc = count($dbw) == 1;
        if ($isdoc) {
            ReadDoc($this->pg);
            $isdoc = isAuthorizedPublication($creadate);
            if ($isdoc) {
                $countit = 1;
                if ($lng == $lang[0]) {
                    $txtart1 = $fieldb1;
                    $txtprt  = strip_tags($fieldb1);
                    $txtart2 = $fieldc1;
                    $txtart3 = $fielda1;
                }
                else {
                    $txtart1 = $fieldb2;
                    $txtprt  = strip_tags($fieldb2);
                    $txtart2 = $fieldc2;
                    $txtart3 = $fielda2;
                }
                $txtart4 = FormatDate($moddate);
                $txtart5 = FormatDate($creadate);
                $txtart6 = $author;
                $txtart7 = $fieldd1 != 'on' ? $email : '';
                $txtart8 = $fieldd2;
            }
        }
        if (!$isdoc) {
            $countit = 0;
            $txtart1 = $web[35];
            $txtart2 = $web[36];
            $txtart3 = $web[37];
            $txtart4 = $web[37];
            $txtart5 = $web[37];
            $txtart6 = '';
            $txtart7 = '';
            $txtart8 = '';
        }
        $max_height2 = isset($site[36]) && $site[36] > 0 && empty($prt) ? $site[36] : 0;
        if (!empty($txtprt)) $this->topmess .= ' - '.$txtart3;
        if (abs($prt) != 1) $out .= $this->topblog();
        $out .= '
    <div class="text-center headCenterBlog">';
        if ($serviz[54] == 'on') {
            $rssblogurl = $site[3].sprintf(RSSBLOG, $lng);
            $out .= '
      <style>
        .headCenterBlog .overpop { padding-top: 20px; }
        .overpop a i { margin-top: -20px; }
      </style>
      <script>
        $(document).ready(function(){
          $(\'.rssBlog\').popover({
            title:"RSS",
            content:"'.$web[398].' : <br /><b> '.$rssblogurl.'</b>",
            trigger:"hover",
            placement:"auto",
            offset:"0 10 10 10",
            html:true
          });
        });
      </script>
      <div class="overpop pt-0 pl-2">
        <a href="'.CHEMIN.'newsrss.php?lng='.$lng.'&url=blog&tconfig='.$this->tconfig.$z2.'" class="rssBlog float-left">
          <i class="fas fa-rss"></i>
        </a>
      </div>';                            
        }
        if ($site[30] != '2' && abs($prt) != 1) $out .= '
      <form 
        name="comment" 
        action="'.($site['URLR'] == 'on' 
          ? $site[3].$lng.'-'.$urlrw[14].'-'.TYP_BLOG.$z2 
          : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_BLOG.$z2).'" 
        method="post">
        <p class="text-center">'.SubmitButton($web[386]).'</p>
      </form>';
        else $out .= '
      <div class="divnewline"></div>';
        $out .= '
    </div>';
        if (!empty($txtart1)) {
            $out .= '
    <a id="hautbillet"></a>';
            if (!empty($txtart8)) 
                $txtart1 = '<img src="'.CHEMIN.'img/'.$txtart8.'" class="'.($serviz[153] == 'on' ? 'float-left' : 'float-right').'" alt=""/>'.$txtart1;
            $out .= '
    <div class="quest '.($serviz[153] != 'on' ? 'text-left' : 'text-right').'"><b>'.$txtart1.'</b> &nbsp;';
            if ($serviz[114] != 'on') {
                if (!empty($txtart6) && !empty($txtart7)) 
                    $out .= '- &nbsp;'.$web[6].displayWriteEmail('blog', $txtart6, $txtart7);
                elseif (!empty($txtart6)) $out .= '- &nbsp;'.$web[6].'
      <b>'.$txtart6.'</b>';
            }
            $out .= '
    </div>
    <div class="bord bordBillet">
      <div class="rep text-left billetBlog">';
            $txt3 = '
        <div id="blog31" class="rep blog3Blog">
<style>
.text2Blog {
  max-height: '.($max_height2 > 0 ? $max_height2.'px' : 'auto').';
  '.($max_height2 > 0 ? ' overflow: hidden;' : '').'
  padding: 4px;
}
</style>';
            if (mb_strlen($txtart2, $charset) >= 0) {
                $txt3 .= '
          <div class="rep text2Blog">'.$txtart2.'</div><div class="clearfix"></div>';
                if ($max_height2 > 0) $txt3 .= '
          <p class="text-right"> 
            <a href="javascript:ActiveMenu(\'blog3\',\'blog2\',0,1,1)" class="box" title="'.$web[296].'">'.$web[296].' 
			  <i class="far fa-plus-square"></i>
		    </a>
          </p>';
            }
            else $txt3 .= '
          <div class="rep txt2Blog">'.$txtart2.'</div><div class="clearfix"></div>';
            $out .= $txt3.'
		</div>
	    <div id="blog21" class="rep blog2Blog" style="display:none;">'.$txtart2.'
		  <p class="text-right c-pointer">
            <a href="#hautbillet" title="'.$web[57].'" onclick="cache(\'blog21\');montre(\'blog31\')">
	          '.$web[57].' <i class="far fa-minus-square"></i>
            </a>
	      </p>
		</div><div class="clearfix"></div>
      </div>';
            if ($prt == 0 && $serviz[104] = 'on') {
                include CONFIGREP.'sncode.inc';
                if (!empty($coders))
                    $out .= '
	  <div class="text-left codersBlog">'.$coders.'</div>';
            }
            $out .= '
      <div id="action" class="float-right text-right align-middle footerBillet">
        <b>'.$web[394].'</b> '.$txtart5.'&nbsp;&nbsp;';
            if ($serviz[33] == 'on' && $countit == 1) {
                $artcounter = UpdateDocCounter($this->pg);
                $txtcount   = $artcounter <= 1 ? $web[188] : $web[189];
            }
            if ($countit == 1) {
                $dbw = array();
                $dbw = SelectDBFields(TYP_BLOG, 'a', '');
                @rsort($dbw);
                for ($i = 0; $i < count($dbw) ; $i++) {
                    if ($dbw[$i][1] == $this->pg){
                        $idb = floor(1+($i/$serviz[55]));
                        break;
                    }
                }
                $out .= '
        <a href="'.($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[1].'-'.$this->tconfig.$z2 
            : $site['BLS'].'.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'">
          | <i class="far fa-file-alt align-middle commentBlogW" title="'.$web[383].'"></i>
        </a>';
                $rank     = ReadDBFields(ADMINS);
                $modadmin = '';
                for ($y = 0; $y < count($rank); $y++) {
                    if ($rank[$y][0] == $userprefs[1] && $rank[$y][2] == 'on') {
                        $modadmin = 'yes';
                        break;
                    }
                }
                if (($txtart6 == $userprefs[1] || $modadmin == 'yes' || $serviz[31] == $userprefs[1] || $serviz[176] == $userprefs[1]) && $site[30] != 2) {
                    $urlb = $site['URLR'] == 'on' 
                      ? $lng.'-'.$urlrw[14].'-11-'.TYP_BLOG.'-maj-'.$this->pg.$z2 
                      : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_BLOG.'&amp;mod=maj&amp;pg='.$this->pg.$z2;
                    $out .= '
        &nbsp;| <a href="'.$urlb.'" title="'.$web[369].'"><i class="far fa-edit"></i></a>&nbsp;';
                }
                if ($prt == 0) {
                    if (empty($serviz[76])) {
                        $out .= '
        <a href="'.($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[1].'-41-'.$this->pg.'--1' 
            : $site['BL'].'.php?lng='.$lng.'&amp;pg='.$this->pg.'&amp;prt=-1').'" target="_blank">
          | <i class="far fa-eye align-middle" title="'.$web[264].'"></i>
        </a>&nbsp;
        <a href="'.($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[1].'-41-'.$this->pg.'-1' 
            : $site['BL'].'.php?lng='.$lng.'&amp;pg='.$this->pg.'&amp;prt=1').'" target="_blank">
          <i class="fas fa-print align-middle" title="'.$web[22].'"></i>
        </a>&nbsp;';
                    }
                    $out .= '| <a href="#top" title="'.$web[136].'"><i class="fas fa-arrow-up align-middle"></i></a>';
                }
            }
            $out .= '
      </div>
      <div class="clearfix"></div>
    </div>';
            if (empty($prt) && $countit == 1) 
                $out .= displayQuickConfig(NULL, $this->pg, 'blog&amp;form=2&amp;id='.$this->pg, 
                  'blog&amp;act=i&amp;id='.$this->pg, 'blog&amp;del='.$this->pg, 39);
        } 
        else $countit = 0;
        if ($serviz[57] != '2' && $countit == 1 && 1 != abs($prt)) {
            $out .= '
            <input type="hidden" name="cat" value="'.$txtart3.'"/><br /><br />';
            $dbwork = array();
            if (!empty($this->react)) $dbwork[0] = $this->react;
            elseif (!empty($this->pg)) {
                $dbreaction = ReadDBFields(DBREBLOG);
                for ($i = 0; $i < count($dbreaction); $i++) {
                    if ($dbreaction[$i][1] == $this->pg && isAccessGranted($dbreaction[$i][6])) {
                        $dbwork[] = $dbreaction[$i][0];
                    }
                }
            }
            @sort($dbwork);
            $out .= '
    <div class="titrebox">'.$web[407].'</div>
    <div>
      <form 
        name="formreblog1" 
        action="'.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[14].'-4-'.TYP_REBLOG.'-'.$this->pg.$z2 
          : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_REBLOG.'&amp;pg='.$this->pg.$z2).'" 
        method="post">
        <p class="text-center">'.SubmitButton($web[380]).'</p>
      </form>';
            if (empty($this->id)) $this->id = 1;
            if (!empty($dbwork)) {
                if (3 == abs($prt)) {
                    $this->id   = 1;
                    $serviz[30] = 99999;
                }
                $out .= GetNavBar(($site['URLR'] == 'on' 
                  ? $lng.'-'.$urlrw[1].'-45-'.$this->pg.'-' 
                  : $site['BL'].'.php?lng='.$lng.'&amp;pg='.$this->pg.'&amp;id='), count($dbwork), $this->id, $serviz[30], $z2);
                for ($i = $serviz[30]*($this->id-1); $i < Min($serviz[30]*$this->id, count($dbwork)); $i++) {
                    ReadDoc($dbwork[$i]);
                    if ($status == 'a') {
                        $out .= '
      <div class="table-responsive-md">
      <div class="d-table w-100 boxreBlog">
        <div class="d-table-row text-left">
          <div class="forum d-table-cell w-15 align-top boxreBlog2"><p>'.$web[185].$fielda1.'&nbsp;</p></div>
          <div class="quest d-table-cell align-middle boxreBlog3">&nbsp;'.$web[6].' ';
                        if ($fieldd1 == 'on') $out .= displayWriteEmail('reblog'.$i, $author, $email);
                        else $out .= '
            <b>'.$author.'</b> ';
                    $out .= '
            '.$web[7].' '.FormatDate($creadate).'
          </div>
        </div>
      </div>
      </div>
      <div class="table-responsive-md">
      <div class="d-table w-100 bodyreBlog">
        <div class="d-table-row">
          <div class="rep d-table-cell">';
                $out .= '
            '.$fieldc1.'
          </div>
        </div>
      </div>
      </div>
      <div class="table-responsive-md">
      <div class="d-table w-100">
        <div class="d-table-row">
          <div class="d-table-cell">';
                        if (empty($prt)) $out .= '
          '.displayQuickConfig(NULL, $dbwork[$i], 'reblog&amp;form=2&amp;id='.$dbwork[$i], 
            'reblog&amp;act=i&amp;id='.$dbwork[$i], 'reblog&amp;del='.$dbwork[$i], 40);
                        $out .= '
          </div>
        </div>
      </div>
      </div>';
                    }
                }
                $out .= GetNavBar(($site['URLR'] == 'on'  
                  ? $lng.'-'.$urlrw[1].'-45-'.$this->pg.'-' 
                  : $site['BL'].'.php?lng='.$lng.'&amp;pg='.$this->pg.'&amp;id='), 
                  count($dbwork), $this->id, $serviz[30], $z2);
                if (count($dbwork) > 1) $out .= '
      <form 
        name="formreblog2" 
        action="'.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[14].'-4'.TYP_REBLOG.'-'.$this->pg.$z2 
          : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_REBLOG.'&amp;pg='.$this->pg.$z2).'" 
        method="post">
        <p class="text-center">'.SubmitButton($web[380]).'</p>
      </form>';
            }
            else $out .= '
      <p class="text-center">'.$web[186].'</p>';
            $out .= '
    </div>';
        }
        $this->txtart2 = $out;
        return true;
    }
    
    public function BLSA_content() {
        global $lng, $lang, $drtuser, $page, $site, $serviz, $userprefs, $web, $urlrw, $z2, $prt;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email,
             $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
             $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        $max_height = isset($site[36]) && $site[36] > 0 && empty($prt) ? $site[36] : 0;
        $out        = '';        
        $xdbw       = array();
        switch ($this->sel) {
        case 'pg': // cas billet unique ---------------------
            if (!empty($this->pg)) {
                $titre = '';
                $xdbw  = SelectDBFields(TYP_BLOG, 'a', $this->pg, DOCIDARCH);
                ReadDoc($pg, ARCHDBBASE);
            }
            break;
        case 'cat': // cas liste catégorie -----------------
            if (!empty($this->cat)) {
                $titre  = $cat;
                $dbwork = ReadDBFields(DBBLOGARCH);
                if (!empty($dbwork)) {
                    $j = $lng == $lang[0]? 0 : 1;
                    for ($i = 0; $i < count($dbwork); $i++) {
                        if ($this->cat == strip_tags($dbwork[$i][0+$j]) && isAcccessGranted($dbwork[$i][6])) {
                            $xdbw[$i][0] = TYP_BLOG;
                            $xdbw[$i][1] = $dbwork[$i][4];
                        }
                    }
                }
            }
            break;
        case 'jour': // cas liste jour ---------------------
            if (!empty($this->datej)) {
                $titre      = $web[403].substr(formatDate($this->datej), 0, 10);
                $this->date = $this->datej;
                $dbwork     = ReadDBFields(DBBLOGARCH);
                for ($i = 0; $i < count($dbwork); $i++) {
                    if (!isAccessGranted($dbwork[$i][6])) continue;
                    ReadDoc($dbwork[$i][4]);
                    $datebi = CutLongWord($creadate, 8);
                    if ($datebi == $this->datej) {
                        $xdbw[$i][0] = TYP_BLOG;
                        $xdbw[$i][1] = $fileid;
                    }
                }
            }
            break;
        case 'mois': // cas liste mois ---------------------
            if (!empty($this->date)) {
                $titre  = $web[399].' '.substr(FormatDate($this->date), 1, 7);
                $dbwork = ReadDBFields(DBBLOGARCH);
                for ($i = 0; $i < count($dbwork); $i++) {
                    if (!isAccessGranted($dbwork[$i][6])) continue;
                    ReadDoc($dbwork[$i][4]);
                    $datebi = CutLongWord($creadate, 6);
                    if ($datebi == $this->date) {
                        $xdbw[$i][0] = TYP_BLOG;
                        $xdbw[$i][1] = $fileid;
                    }
                }
            }
            break;
        default: // cas liste globlale ---------------------
            $titre  = $web[383];
            $dbwork = ReadDBFields(DBBLOGARCH);
            foreach ($dbwork as $db) 
                if (isAccessGranted($db[6])) 
                    $xdbw[] = array(TYP_BLOG, $db[4], 'a');
        }
        unset($dbwork);
        if (!empty($xdbw) || $this->sel == 'mois') {
            @rsort($xdbw);
            if (empty($this->id)) $this->id = 1;
            $minblog = $serviz[55];
            $maxblog = Min($serviz[55]*$this->id, count($xdbw));
            if (isset($prt) && 3 == abs($prt)) {
                $this->id = 1;
                $minblog  = 99999;
                $maxblog  = min($minblog, count($xdbw));
            }
            $out .= '
    <br />
    <div class="w-100">';
            $out .= GetNavBar(($site['URLR'] == 'on' 
                ? $lng.'-'.$urlrw[1].'-44-'.$this->sel.'-'.$this->pg.'-'.$this->cat.'-'.$this->datej.'-'.$this->date.'-' 
                : 'blogsarch.php?lng='.$lng.'&amp;sel='.$this->sel.'&amp;pg='.$this->pg.'&amp;cat='.$this->cat.
                  '&amp;datej='.$this->datej.'&amp;date='.$this->date.'&amp;id='), 
                count($xdbw), $this->id, $minblog, $z2);
            $nbrnvisi = count($xdbw) > $site[31] ? $site[31] : count($xdbw);
            if (count($xdbw) == 0) {
                if ($this->sel == 'mois') $out .= '
      <p class="text-center font-weight-bold">'.$web[438].'</p>';
                else $out .= '
      <p class="text-center font-weight-bold">'.$web[439].($site[30] == '2'? '' : '<br />'.$web[440]).'</p>';
            } 
            else {
                for ($i = $minblog*($this->id-1); $i < $maxblog; $i++) {
                    ReadDoc($xdbw[$i][1], ARCHDBBASE);
                    $txt0 = $fileid;
                    if ($lng == $lang[0]) {
                        $txt1 = $fieldb1;
                        $txt2 = $fieldc1;
                    }
                    else {
                        $txt1 = $fieldb2;
                        $txt2 = $fieldc2;
                    }
                    if (!empty($txt2)) {
                        if (!empty($fieldd2)) $txt1 = '<img src="'.CHEMIN.'img/'.$fieldd2.'" class="float-right" alt="'.$fieldd2.'"/>'.$txt1;
                        $out .= '
      <div class="quest text-left">
        <a href="'.$site[3].($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[1].'-42-'.$fileid.$z2 
            : 'blogarch.php?lng='.$lng.'&amp;pg='.$fileid.$z2).'" title="'.$web[511].'">
          <b>'.$txt1.'</b>
        </a> &nbsp;';
                        if ($serviz[114] != 'on') {
                            if ($fieldd1 == 'on' && !empty($email) && !empty($author)) 
                                $out .= '- &nbsp;'.$web[6].displayWriteEmail('blog'.$i, $author, $email);
                            elseif (!empty($author)) $out .= '- &nbsp;'.$web[6].'
                <b>'.$author.'</b>';
                        }
                        $out .= '
      </div>
          ';
                        $txt3 = '
      <a id="billet'.$i.'"></a>
      <div id="blog3'.$i.'" class="rep blog3Blog">';
                        if (!empty($txt2)) {
                            $txt3 .= '
<style>
.txt2arch {
  height: '.($max_height > 0 ? $max_height.'px' : 'auto').';
  '.($max_height > 0 ? ' overflow: hidden;' : '').'
  padding: 4px;
}
</style>
        <div class="rep txt2arch">'.$txt2.'</div>';
                        if ($max_height > 0) $txt3 .= '
        <p class="text-right"> 
          <a href="javascript:ActiveMenu(\'blog3\',\'blog2\',0,'.$nbrnvisi.','.$i.')" class="box" title="'.$web[296].'">'.$web[296].' 
            <i class="far fa-plus-square"></i>
          </a>
        </p>';
                        $txt3 .= '
      </div>';
                        if ($max_height > 0) $txt3 .= '
      <div id="blog2'.$i.'" class="rep blog2Blog" style="display:none;">'.$txt2.'
        <p class="text-right c-pointer">
          <a href="#billet'.$i.'" title="'.$web[57].'" onclick="cache(\'blog2'.$i.'\');montre(\'blog3'.$i.'\')">
            '.$web[57].' <i class="far fa-minus-square"></i>
          </a>
        </p>
      </div>';
                        }
                        $out .= $txt3;
                        $out .= '
      <div class="text-right align-middle headblogarch">
        <form 
          name="formblog'.$i.'" 
          action="'.CHEMIN.($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[1].'-70'.$z2 
            : 'blogarch.php?lng='.$lng.$z2).'" 
          method="post">
          <b>'.$web[394].'</b>'.FormatDate($creadate).'&nbsp;';
                        $out .= '
          <input type="hidden" name="pg" value="'.$fileid.'"/>
          <input type="hidden" name="cat" value="'.$this->cat.'"/>
          <input type="hidden" name="sel" value="'.$this->sel.'"/>';
                        if (empty($serviz[76])) $out .= '
          <a href="'.CHEMIN.($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[1].'-43-'.$fileid.'--1' 
              : 'blogarch.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;prt=-1').'" target="_blank">
            <i class="far fa-eye" title="'.$web[264].'"></i>
          </a>&nbsp;
          <a href="'.CHEMIN.($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[1].'-43-'.$fileid.'-1' 
              : 'blogarch.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;prt=1').'" target="_blank">
            <i class="fas fa-print" title="'.$web[22].'"></i>
          </a>&nbsp;';
                        $out .= '
        </form>
      </div>';
                    }
                }
                $out .= GetNavBar(($site['URLR'] == 'on' 
                  ? $lng.'-'.$urlrw[1].'-44-'.$this->sel.'-'.$this->pg.'-'.$this->cat.'-'.$this->datej.'-'.$this->date.'-' 
                  : 'blogsarch.php?lng='.$lng.'&amp;sel='.$this->sel.'&amp;pg='.$this->pg.'&amp;cat='.$this->cat.
                    '&amp;datej='.$this->datej.'&amp;date='.$this->date.'&amp;id='), 
                  count($xdbw), $this->id, $minblog, $z2);
            }
                $out .= '
    </div>';
        }
        $this->topmess .= ' - '.$web[399].' - '.$titre;
        $this->txtart2  = $out;
        return true;
    }

    public function BLA_content() {
        global $lng, $lang, $drtuser, $page, $site, $serviz, $userprefs, $web, $urlrw, $z2, $prt;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, $fielda1, $fielda2, $fieldb1, 
               $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        $out = '';
        if (count(SelectDBFields(TYP_BLOG, 'a', $this->pg, DOCIDARCH)) == 1) {
            ReadDoc($this->pg, ARCHDBBASE);
            $countit = 1;
            if ($lng == $lang[0]) {
                $txtart1 = $fieldb1;
                $txtprt  = strip_tags($fieldb1);
                $txtart2 = $fieldc1;
                $txtart3 = $fielda1;
            }
            else {
                $txtart1 = $fieldb2;
                $txtprt  = strip_tags($fieldb2);
                $txtart2 = $fieldc2;
                $txtart3 = $fielda2;
            }
            $txtart4 = FormatDate($moddate);
            $txtart5 = FormatDate($creadate);
            $txtart6 = $author;
            $txtart7 = $fieldd1 != 'on' ? $email : '';
            $txtart8 = $fieldd2;
        }
        else {
            $countit = 0;
            $txtart1 = $web[35];
            $txtart2 = $web[36];
            $txtart3 = $web[37];
            $txtart4 = $web[37];
            $txtart5 = $web[37];
            $txtart6 = '';
            $txtart7 = '';
            $txtart8 = '';
        }
        $out .= '
  <br />';
        if (!empty($txtart1)) {
            if (!empty($txtart8)) $txtart1 = '<img src="'.CHEMIN.'img/'.$txtart8.'" class="float-right" alt=""/>'.$txtart1;
            $out .= '
  <div class="text-left quest"><b>'.$txtart1.'</b> &nbsp;';
            if (!empty($txtart6)) $out .= '- &nbsp;'.$web[6].' <b>'.$txtart6.'</b>';
            $out .= '
  </div>
  <div class="bord bordTextArch">
    <div class="rep text-left textArchBlog">
          '.$txtart2.'
    </div><div class="clearfix"></div>
    <div class="float-left w-50 text-left align-middle pubArchBlog">
      <b>'.$web[394].'</b> '.$txtart5.'&nbsp;&nbsp;';
            if ($serviz[33] == 'on' && $countit == 1) {
                $artcounter = UpdateDocCounter($this->pg);
                $txtcount   = $artcounter <= 1 ? $web[188] : $web[189];
            }
            $out .= '
    </div>
    <div id="action" class="w-40 float-right text-right align-middle actionArchBlog">';
            if ($countit == 1) {
                $dbw = array();
                $dbw = SelectDBFields(TYP_BLOG, 'a', '', DOCIDARCH);
                @rsort($dbw);
                for ($i = 0; $i < count($dbw) ; $i++) {
                    if ($dbw[$i][1] == $this->pg){
                        $idb = floor(1+($i/$serviz[55]));
                        break;
                    }
                }
				if ($prt == 0) {
					$out .= '
      <a href="'.($site['URLR'] =='on' ? $lng.'-'.$urlrw[1].'-7'.$z2 : 'blogsarch.php?lng='.$lng.$z2).'">
        <i class="far fa-file-alt align-middle" title="'.$web[383].'"></i>
      </a>&nbsp;';
                    $out .= '
      <a href="'.($site['URLR'] =='on' 
          ? $lng.'-'.$urlrw[1].'-43-'.$this->pg.'--1' 
          : 'blogarch.php?lng='.$lng.'&amp;pg='.$this->pg.'&amp;prt=-1').'" target="_blank">
        <i class="far fa-eye align-middle" title="'.$web[264].'"></i>
      </a>&nbsp;
      <a href="'.($site['URLR'] =='on' 
          ? $lng.'-'.$urlrw[1].'-43-'.$this->pg.'-1' 
          : 'blogarch.php?lng='.$lng.'&amp;pg='.$this->pg.'&amp;prt=1').'" target="_blank">
        <i class="fas fa-print align-middle" title="'.$web[22].'"></i>
      </a>&nbsp;';
				}
			}
            $out .= '
    </div><div class="clearfix"></div>
  </div>';
        } 
        else $countit = 0;
        if ($serviz[57] != "" && $countit == 1 && 1 != abs($prt)) {
            $out .= '
    <input type="hidden" name="cat" value="'.$txtart3.'"/><br /><br />';
            $dbwork = array();
            if (!empty($this->react)) $dbwork[0] = $this->react;
            elseif (!empty($this->pg)) {
                $dbreaction = ReadDBFields(DBREBLOGARCH);
                for ($i = 0; $i < count($dbreaction); $i++) {
                    if ($dbreaction[$i][1] == $this->pg && isAccessGranted($dbreaction[$i][6])) 
                        $dbwork[] = $dbreaction[$i][0];
                }
            }
            @sort($dbwork);
            if (empty($this->id)) $this->id = 1;
            if (!empty($dbwork)) {
                $out .= '
  <div class="titrebox">'.$web[407].'</div>';
                if (3 == abs($prt)) {
                    $this->id   = 1;
                    $serviz[30] = 99999;
                }
                $out .= GetNavBar(($site['URLR'] == 'on' ? 
                    $lng.'-'.$urlrw[1].'-46-'.$this->pg.'-' : 
                    'blogarch.php?lng='.$lng.'&amp;pg='.$this->pg.'&amp;id='), 
                    count($dbwork), $this->id, $serviz[30], $z2);
                for ($i = $serviz[30]*($this->id-1); $i < Min($serviz[30]*$this->id, count($dbwork)); $i++) {
                    ReadDoc($dbwork[$i], ARCHDBBASE);
                    if ($status == 'a') {
                        $out .= '
  <div class="bord text-left boxreBlog">
    <p class="boxreBlog2"><b>'.$web[185].$fielda1.'</b>&nbsp; - &nbsp;'.$web[6].' ';
                        if ($fieldd1 == 'on') $out .= '<b>'.$author.'</b> ';
                        $out .= $web[7].' '.FormatDate($creadate).'
    </p>
    <div class="rep">';
                        $out .= '
                  '.$fieldc1.'
    </div>
  </div>';
                    }
                }
                $out .= GetNavBar(($site['URLR'] == 'on' ? 
                    $lng.'-'.$urlrw[1].'-46-'.$this->pg.'-' : 
                    'blogarch.php?lng='.$lng.'&amp;pg='.$this->pg.'&amp;id='), 
                    count($dbwork), $this->id, $serviz[30], $z2);
            }
        }
        $this->topmess .= ' - '.$web[399].(!empty($txtprt) ? ' - '.$txtart3 : '');
        $this->txtart2  = $out;
        return true;
    }
    
    public function BSS_content() {
        global $lng, $lang, $serviz, $nom, $web, $page, $prt, $charset;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, $fielda1, $fielda2, $fieldb1, 
               $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
		$out = '';
        $max_height    = isset($serviz[133]) && $serviz[133] > 0 ? $serviz[133] : 0;
        ReadDoc($this->id);
        if ($lng == $lang[0]) {
            $rssurl = $fieldb1;
            $rsstxt = $fielda1;
        }
        else {
            $rssurl = $fieldb2;
            $rsstxt = $fielda2;
        }
        $topmess       = getLabel($nom[$lng == $lang[0] ? 42 : 43]).' - '.$web[402].' - '.$rsstxt;
        $this->topmess = $page[9] == '' ? $topmess : '<i class="fas fa-blog float-right pr-2"></i>'.$topmess;
        if (abs($prt) != 1) $out .= $this->topblog();
        $out .= '
<div class="table-responsive-md">
<div class="d-table w-100">
  <div class="d-table-row">
    <div class="d-table-cell w-100 align-top itemRssBlog">';
        include_once 'inc/lastRSS.inc';
        $rss             = new lastRSS;
        $rss->cache_dir  = substr(CACHEREP, 0, strlen(CACHEREP)-1);
        $rss->cache_time = $serviz[39];
        $rs              = $rss->Get($rssurl);
        if (FALSE !== $rs) {
            $cpin  = strtoupper($rs['encoding']);
            $cpout = strtoupper($charset);
            if ($cpin == $cpout) {
                function MyIConv($txt) { return $txt; }
            } else {
                function MyIConv($txt) {
                    global $cpin, $cpout;
                    return iconv($cpin, $cpout, $txt);
                }
            }
            if (isset($rs['image_url']) && $rs['image_url'] != '') $out .= '
      <div class="text-center"><img src="'.$rs['image_url'].'" alt="" title="" /></div>';
            $out .= '
      <p class="forum">
        <a href="'.$rs['link'].'" target="_blank"><b>'.MyIConv($rs['title']).'</b></a>
      </p>
      <br />
      <p><b>'.html_entity_decode(MyIConv($rs['description']), ENT_QUOTES, $charset).'</b></p>
      <hr class="width12" />';
            foreach($rs['items'] as $item) {
                if ($item['title'] == '' && $item['link'] == '') $txt = '';
                elseif ($item['title'] == '') $txt = '
      <a href="'.$item['link'].'" target="_blank" title="">'.$item['link'].'</a>
      <br /><br />';
                elseif ($item['link'] == "") $txt = '
      <u>'.MyIConv($item['title']).'</u>
      <br /><br />';
                else $txt = '
      <a href="'.$item['link'].'" target="_blank" title="'.$web[296].'">'.MyIConv($item['title']).'"</a>
      <br /><br />';
                $desc = html_entity_decode(MyIConv($item['description']), ENT_QUOTES, $charset);
                $txt1 = '
      <b>
'.$txt.'
      </b>'.$desc;
                if (!empty($desc)) {
                    $out .= '
<style>
.text1RssBlog {
  height: '.($max_height > 0 ? $max_height.'px;' : 'auto;').'
  overflow: '.($max_height > 0 ? 'hidden;' : 'visible;').'
}
</style>
      <div id="texte2'.$i.'" class="rep text2RssBlog">
        <div class="rep text1RssBlog">
          '.$txt1.'
        </div>';
                    if ($max_height > 0) $out .= '
        <p class="text-right">
          <a href="javascript:cache(\'texte2'.$i.'\');montre(\'texte1'.$i.'\');" class="box" title="'.$web[296].'">
            '.$web[296].' <i class="far fa-plus-square"></i>
          </a>
        </p>';
                $out .= '
      </div>';
                    if ($max_height > 0) $out .= '
      <div id="texte1'.$i.'" class="bord textRssBlog text-left" style="display: none;">'.$txt1.'
        <p class="text-right c-pointer">
          <a href="#billet'.$pos.$i.'" title="'.$web[57].'" onclick="cache(\'texte1'.$i.'\');montre(\'texte2'.$i.'\')">
	        '.$web[57].' <i class="far fa-minus-square"></i>
          </a>
        </p>
      </div>';
                }
                if ($item['pubDate'] != '') $out .= '
      <span class="pubdateRss">('.FormatDate($item['pubDate']).')</span>';
                $out .= '
      <hr />';
                $i++;
            }
            $modfl = @filemtime(CACHEREP.'rsscache_'.md5($rssurl));
            if ($modfl == false) $txt = $web[261];
            else $txt = $web[263].' <b>'.FormatDateStamp($modfl).'</b>';
            $out .= '
      <p>'.$txt.'</p>';
        }
        else $out .= '
      <p>'.$web[262].' '.$rssurl.'</p>';
        $out .= '
    </div>
  </div>
</div>
</div>';
        return array($this->typ, $out);
    }
}
?>