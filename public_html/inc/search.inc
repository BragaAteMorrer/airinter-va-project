<?php
/*******************************************************************************
 *   Search
 *****************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2022 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 *  v6.00.22 (December 19, 2025) : Correction for react article
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'search.inc')) {
    header('location:../index.php');
    die();
}
#[AllowDynamicProperties]
class GY_search {
    public $tconfig;
    public $topmess;
    protected $search;
    protected $searchin;
    protected $searchlng;
    protected $tokens;

    public function __construct() {
        global $web;
        include CONFIGREP.'searchconfig.inc';
        $this->search    = import('search');
        $this->tconfig   = import('tconfig', '', true, 0);
        $this->tconfig   = $confpage == 'ALL' ? $this->tconfig : $confpage;
        $this->topmess   = $web[59].' - '.stripslashes($this->search);
        $this->searchin  = import('searchin');
        $this->searchlng = import('searchlng');
        $this->cat       = import('cat', '', false, 0);
    }
    
    public function SCH_ok($pseudo, $lng) {
        global $site, $urlrw, $serviz, $web;
        $out   = '';
        $href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng);
        if ($serviz[24] != 'on') {
            $out = '
    <div class="text-center web342">'.$web[143];
            $out .= '
      <p class="text-center">
        '.SubmitButton($web[135], 'self.location.href=\''.$href1.'\'').'
      </p>
    </div>';
            return array('SCH', $out);
        }
        if (empty(trim($this->search))) {
            $out = '
    <div class="text-center web342">'.$web[505];
            $out .= '
      <p class="text-center">
        '.SubmitButton($web[135], 'self.location.href=\''.$href1.'\'').'
      </p>
    </div>';
            return array('SCH', $out);
        }
        return array('');
    }

    protected function Rechercher($expr, $string) {
        $res        = 0;
        $total      = 0;
        $occurrence = array();
        $i          = 0;
        $k          = 0;
        foreach ($expr as $exp) {
            $r = preg_match_all('"'.$exp.'"', $string, $x);
            if (($r !== false) && ($r > 0)) {
                $res += (10 + max(100-20*$i, 1))*(9 + min($r, 5));
            }
            if (in_array($exp, $this->tokens)) {
                $occurrence['mot'][$k] = $exp;
                $occurrence['nb'][$k]  = $r;
                $total += $r;
                $k++;
            }
            $i++;
        }
        if ($total == 0) $res = 0;
        $occurrence['stat'][0] = $res;
        $occurrence['stat'][1] = $total;
        return $occurrence;
    }

    protected function GetTitleMenuBox($id) {
        global $lng, $lang;
        $title = '';
        $id    = TestFileID($id);
        if (FileDBExist(DBBASE.$id.INCEXT)) {
            include DBBASE.$id.INCEXT;
            $title = ($lng == $lang[0]) ? $fieldb1 : $fieldb2;
        }
        return $title;
    }

    public function SCH_content() {
        global $nom, $site, $page, $lang, $lng, $members, $charset, $serviz, $nom, $page, $prt, 
               $userprefs, $web, $charset, $urlrw, $z2;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, $fielda1, $fielda2, 
               $fieldb1, $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        $out = '';
        $id  = import('id');
        include CONFIGREP.'searchconfig.inc';
        $this->tconfig     = $confpage == 'ALL' ? $this->tconfig : $confpage;
        $savesearch        = $this->search;
        $restricted        = false;
        $savepertinencemin = $pertinencemin;
		//log 
		$datjourl = new DateTime('now');
		$dateheureminiutesecl = $datjourl->format('Y_m_d');
//		$nomficlog = CHEMIN.'data/log/searchinc'.'_'.$dateheureminiutesecl.'.log';
		
//        $pertinencemin  = import('pert');
		if (!empty(import('pert'))) $pertinencemin  = import('pert');
        if ($userprefs[1] == '') {
            if (!empty($this->searchin)) {
                $searchfor = array($this->searchin);
                $illegal   = false;
                switch ($this->searchin) {
                case TYP_ART :
                case TYP_REACT :
				    if (!isset($rech_article)) $rech_article = TYP_ART;
					if ($rech_article != '')
					{
						if (!isAccessGranted($members[1])) $illegal = true;
					}
					else
						$illegal = true;						
                    break;
                case TYP_BLOG :
                case TYP_REBLOG :
				    if (!isset($rech_blog)) $rech_blog = TYP_BLOG;
					if ($rech_blog != '')
						if (!isAccessGranted($members[15])) $illegal = true;
                    break;
                case TYP_DNLOAD :
                    if (!isset($rech_download)) $rech_download = TYP_DNLOAD;
					if ($rech_download != '')
						if (!isAccessGranted($members[7])) $illegal = true;
                    break;
                case TYP_FAQ :
                    if (!isset($rech_faq)) $rech_faq = TYP_FAQ;
					if ($rech_faq != '')
						if (!isAccessGranted($members[4])) $illegal = true;
                    break;
                case TYP_FORUM :
                    if (!isset($rech_forum)) $rech_forum = TYP_FORUM;
					if ($rech_forum != '')
						if (!isAccessGranted($members[5])) $illegal = true;
                    break;
                case TYP_GUESTBK :
                    if (!isset($rech_livreor)) $rech_livreor = TYP_GUESTBK;
					if ($rech_livreor != '')
						if (!isAccessGranted($members[12])) $illegal = true;
                    break;
                case TYP_LINKS :
                     if (!isset($rech_liens)) $rech_liens = TYP_LINKS;
					if ($rech_liens != '')
						if (!isAccessGranted($members[3])) $illegal = true;
                    break;
                case TYP_NEWS :
                     if (!isset($rech_news)) $rech_news = TYP_NEWS;
					if ($rech_news != '') 
						if (!isAccessGranted($members[13])) $illegal = true;
                    break;
                case TYP_PHOTO :
                    if (!isset($rech_photo)) $rech_photo = TYP_PHOTO;
					if ($rech_photo != '') 
					    if (!isAccessGranted($members[2])) $illegal = true;
                    break;
                case TYP_AGENDA :
                    if (!isset($rech_rech_agenda)) $rech_rech_agenda = TYP_AGENDA;
					if ($rech_rech_agenda != '')
						if (!isAccessGranted($members[14])) $illegal = true;
                    break;
                }
                if ($illegal) {
                    die('STOP ! Illegal search');
                }
            } 
            else {
                $searchall = array (TYP_ART, TYP_DNLOAD, TYP_FAQ, TYP_FORUM, TYP_FREEBOX, TYP_GUESTBK, TYP_LINKS, 
                                    TYP_NEWS, TYP_PHOTO, TYP_REACT, TYP_AGENDA, TYP_BLOG, TYP_REBLOG, TYP_ARCH);
                $searchfor = array();
                foreach ($searchall as $typ) {
                    switch ($typ) {
                    case TYP_ART :
                    case TYP_REACT :
						if (!isset($rech_article)) $rech_article = TYP_ART;
						if ($rech_article != '')
						{
							if (isAccessGranted($members[1])) $searchfor[] = $typ; else $restricted = true;
						}
                        break;
                    case TYP_BLOG :
                    case TYP_REBLOG :
						if (!isset($rech_blog)) $rech_blog = TYP_BLOG;
						if ($rech_blog != '') $searchfor[] = $typ; else $restricted = true;
                        if (isAccessGranted($members[15])) $searchfor[] = $typ; else $restricted = true;
                        break;
                    case TYP_DNLOAD :
						if (!isset($rech_download)) $rech_download = TYP_DNLOAD ;
						if ($rech_download != '') $searchfor[] = $typ; else $restricted = true;
                        if (isAccessGranted($members[7])) $searchfor[] = $typ; else $restricted = true;
                        break;
                    case TYP_FAQ :
 						if (!isset($rech_faq)) $rech_faq = TYP_FAQ;
						if ($rech_faq != '') $searchfor[] = $typ; else $restricted = true;
						if (isAccessGranted($members[4])) $searchfor[] = $typ; else $restricted = true;
                        break;
                    case TYP_FORUM :
						if (!isset($rech_forum)) $rech_forum = TYP_FORUM ;
						if ($rech_forum != '') $searchfor[] = $typ; else $restricted = true;
                        if (isAccessGranted($members[5])) $searchfor[] = $typ; else $restricted = true;
                        break;
                    case TYP_GUESTBK :
						if (!isset($rech_livreor)) $rech_livreor = TYP_GUESTBK;
						if ($rech_livreor != '') $searchfor[] = $typ; else $restricted = true;
                        if (isAccessGranted($members[12])) $searchfor[] = $typ; else $restricted = true;
                        break;
                    case TYP_LINKS :
						if (!isset($rech_liens)) $rech_liens = TYP_LINKS;
						if ($rech_liens != '') $searchfor[] = $typ; else $restricted = true;
                        if (isAccessGranted($members[3])) $searchfor[] = $typ; else $restricted = true;
                        break;
                    case TYP_NEWS :
						if (!isset($rech_news)) $rech_news = TYP_NEWS;
						if ($rech_news != '') $searchfor[] = $typ; else $restricted = true;
                        if (isAccessGranted($members[13])) $searchfor[] = $typ; else $restricted = true;
                        break;
                    case TYP_PHOTO :
						if (!isset($rech_photo)) $rech_photo = TYP_PHOTO;
						if ($rech_photo != '') $searchfor[] = $typ; else $restricted = true;
                        if (isAccessGranted($members[2])) $searchfor[] = $typ; else $restricted = true;
                        break;
                    case TYP_AGENDA :
						if (!isset($rech_agenda)) $rech_agenda = TYP_AGENDA ;
						if ($rech_agenda != '') $searchfor[] = $typ; else $restricted = true;
                       if (isAccessGranted($members[14])) $searchfor[] = $typ; else $restricted = true;
                        break;
                    case TYP_ARCH :
						if (!isset($rech_archive)) $rech_archive = TYP_ARCH ;
						if ($rech_archive != '') $searchfor[] = $typ; else $restricted = true;
                        break;
                    default :
                        $searchfor[] = $typ;
                    }
                    unset($searchall);
                }
            }
        } else 
            $searchfor = array (TYP_ART, TYP_DNLOAD, TYP_FAQ, TYP_FORUM, TYP_FREEBOX, TYP_GUESTBK, TYP_LINKS, 
                                TYP_NEWS, TYP_PHOTO, TYP_REACT, TYP_AGENDA, TYP_BLOG, TYP_REBLOG, TYP_ARCH);
        if (in_array($this->searchin, array(TYP_FORUM, TYP_ARCH))) {
            $searchfor      = array(TYP_FORUM, TYP_ARCH);
            $this->searchin = '';
        }
        $longword     = 100;
        $longbarre    = 75;
        $nbresult     = $resultbypage;
        $stylespan    = '<span style="';
        $stylespan   .= ($styleocc[0] == '') ? '' : 'color:'.$styleocc[0].'; ';
        $stylespan   .= ($styleocc[1] == '') ? '' : 'font-style:'.$styleocc[1].'; ';
        $stylespan   .= ($styleocc[2] == '') ? '' : 'font-weight:'.$styleocc[2].'; ';
        $stylespan   .= ($styleocc[3] == '') ? '' : 'text-decoration:'.$styleocc[3].'; ';
        $stylespan   .= ($styleocc[4] == '') ? '' : 'background-color:';
        $endstyle     = '</span>';
        $found        = array();
        $this->search = stripslashes(trim($this->search));
        $this->search = str_replace(array('^', '[', ']', '.', '+', '?', '*', '|'), '', $this->search);
        $this->search = str_replace(array('(', ')'), ' ', $this->search);
        $this->tokens = array();
        if (!empty($this->search)) {
            $searchlower = strtolower($this->search);
            $nbres = preg_match_all('/[ \t]*"([^ \t"]+[^"]*[^ \t"]+)"[ \t]*|[ \t]*([^ \t]+)[ \t]*/', $searchlower, $res);
            if (($nbres !== false) && ($nbres > 0)) {
                $this->search = '';
                for ($i=0; $i < count($res[1]); $i++) {
                    if (empty($res[1][$i])) {
                        $this->tokens[] = str_replace(array('/', '"'), array('\/', ''), trim($res[0][$i]));
                        $this->search  .= $this->tokens[$i].' ' ;
                    }
                    else {
                        $this->tokens[] = str_replace('/', '\/', $res[1][$i]);
                        $this->search  .= "'".$this->tokens[$i]."' ";
                    }
                }
                //------ Elargir la recherche sur des expressions similaires à la requête ------//
                $expression = array();
                $k          = 0;
                for ($i = 0; $i < count($this->tokens); $i++) {
                    $expression[$k] = '';
                    for ($j = 0; $j < count($this->tokens) - $i; $j++) {
                        $expression[$k] .= ' '.$this->tokens[$j];
                    }
                    $expression[$k] = trim($expression[$k]);
                    $k++;
                }
                for ($i = 1; $i < count($this->tokens); $i++) {
                    $expression[$k] = $this->tokens[$i];
                    $k++;
                }
                for ($i = count($this->tokens) - 2; $i > 0; $i--) {
                    $expression[$k] = '';
                    for ($j = count($this->tokens) - 1; $j >= $i; $j--) {
                        $expression[$k] = $this->tokens[$j].' '.$expression[$k];
                    }
                    $expression[$k] = trim($expression[$k]);
                    $k++;
                }
                unset($k);
                $this->tokens = array_unique($this->tokens);
                $expression   = array_unique($expression);
                //---------------- Vire tous les termes inutiles (non exhaustif) ---------------//
                $exclus = $lng == $lang[0] ? $exclure0 : $exclure1;
                if (!empty($exclus)) {
                    $exclure = explode(',', $exclus);
                    for ($i = 0; $i < count($exclure); $i++) {
                        $exclure[$i] = trim($exclure[$i]);
                        if ($exclure[$i] == '') {
                            array_splice($exclure, $i);
                            $i--;
                        }
                    }
                    array_unique($exclure);
                }
                $ignore = array();
                $j      = 0;
                for ($i = 0; $i < count($this->tokens); $i++) {
                    if (in_array($this->tokens[$i], $exclure)) {
                        if (!in_array($this->tokens[$i], $ignore)) {
                            $ignore[$j] = $this->tokens[$i];
                            $j++;
                        }
                        array_splice($this->tokens, $i, 1);
                        $i--;
                    }
                    if (strpos($this->tokens[$i], "'") == 1) $this->tokens[$i] = substr(strrchr($this->tokens[$i], "'"), 1);
                }
                unset($j);
                if (!empty($this->tokens)) {
                    for ($i = 0; $i < count($expression); $i++) {
                        if (in_array($expression[$i], $exclure)) {
                            array_splice($expression, $i, 1);
                            $i--;
                        }
                        if (strpos($expression[$i], "'") == 1) $expression[$i] = substr(strrchr($expression[$i], "'"), 1);
                    }
                    if ($this->tokens[0] != $expression[0]) {
                        for ($i=count($this->tokens); $i>0; $i--) {
                            $this->tokens[$i] = $this->tokens[$i-1];
                        }
                        $this->tokens[0] = $expression[0];
                    }
                } 
                else $expression = $this->tokens;
                $this->search = trim($this->search);	
				$dbwork = array();
				$dbw1 = array();								
                if (!empty($this->searchin)) {
					if (!isset($rech_archive)) $rech_archive = TYP_ARCH;
                    if ($this->searchin == TYP_ARCH && $rech_archive == TYP_ARCH) {
                        $dbw1 = SelectDBFieldsByStatus(ReadDBFields(DOCIDARCH), 'a');
                        for ($i = 0; $i < count($dbw1); $i++)
						{
							$typnonrech = false;
							if ($rech_article == $dbw1[$i][0]) 	$typnonrech = true;
							if ($serviz[29] != "2" && TYP_REACT == $dbw1[$i][0]) $typnonrech = true;
							if ($rech_download == $dbw1[$i][0])	$typnonrech = true;
							if ($rech_faq == $dbw1[$i][0]) 		$typnonrech = true;
							if ($rech_livreor == $dbww1[$i][0])   $typnonrech = true;
							if ($rech_liens == $dbw1[$i][0])     $typnonrech = true;
							if ($rech_news == $dbw1[$i][0])      $typnonrech = true;
							if ($rech_blog == $dbw1[$i][0])      $typnonrech = true;
							if ($rech_photo == $dbw1[$i][0])     $typnonrech = true;
							if ($rech_agenda == $dbw1[$i][0])    $typnonrech = true;
							if ($rech_forum == $dbw1[$i][0])      $typnonrech = true;
							if ($typnonrech)
							{
								$dbw1[$i][4] = 'i';
								$dbwork[] = $dbw1[$i];
							}
						}

                    } else {
						if (!isset($rech_article)) $rech_article = TYP_ART;
						if (!isset($rech_download)) $rech_download = TYP_DNLOAD;
						if (!isset($rech_faq)) $rech_faq = TYP_FAQ;
						if (!isset($rech_livreor)) $rech_livreor = TYP_GUESTBK;
						if (!isset($rech_liens)) $rech_liens = TYP_LINKS;
						if (!isset($rech_news)) $rech_news = TYP_NEWS;
						if (!isset($rech_blog)) $rech_blog = TYP_BLOG;
						if (!isset($rech_photo)) $rech_photo = TYP_PHOTO;
						if (!isset($rech_agenda)) $rech_agenda = TYP_AGENDA;
						if (!isset($rech_forum)) $rech_forum = TYP_FORUM;						
							$dbwork = SelectDBFields($this->searchin, 'a', '');
							for ($i = 0; $i < count($dbwork); $i++)
							{
								$typnonrech = false;
								if (isset($dbwork[$i][0]))
								{
									if ($rech_article == $dbwork[$i][0]) 	$typnonrech = true;
									if ($serviz[29] != "2" && TYP_REACT == $dbwork[$i][0]) $typnonrech = true;
									if ($rech_download == $dbwork[$i][0])	$typnonrech = true;
									if ($rech_faq == $dbwork[$i][0]) 		$typnonrech = true;
									if ($rech_livreor == $dbwork[$i][0])   $typnonrech = true;
									if ($rech_liens == $dbwork[$i][0])     $typnonrech = true;
									if ($rech_news == $dbwork[$i][0])      $typnonrech = true;
									if ($rech_blog == $dbwork[$i][0])      $typnonrech = true;
									if ($rech_photo == $dbwork[$i][0])     $typnonrech = true;
									if ($rech_agenda == $dbwork[$i][0])    $typnonrech = true;
									if ($rech_forum == $dbwork[$i][0])      $typnonrech = true;
									if ($typnonrech)
									{
										$dbwork[$i][4] = 'a';
										$dbw1[] = $dbwork[$i];
									}
								}
						}
                    }
                } 
				else 
				{
						if (!isset($rech_article)) $rech_article = TYP_ART;
						if (!isset($rech_download)) $rech_download = TYP_DNLOAD;
						if (!isset($rech_faq)) $rech_faq = TYP_FAQ;
						if (!isset($rech_livreor)) $rech_livreor = TYP_GUESTBK;
						if (!isset($rech_liens)) $rech_liens = TYP_LINKS;
						if (!isset($rech_news)) $rech_news = TYP_NEWS;
						if (!isset($rech_blog)) $rech_blog = TYP_BLOG;
						if (!isset($rech_photo)) $rech_photo = TYP_PHOTO;
						if (!isset($rech_agenda)) $rech_agenda = TYP_AGENDA;
						if (!isset($rech_forum)) $rech_forum = TYP_FORUM;							
						$dbw = SelectDBFieldsByStatus(ReadDBFields(DOCID), 'a');
						$dbw1 = $dbw2 = array();
						for ($i = 0; $i < count($dbw); $i++) 
						{
							$typnonrech = false;
							if ($rech_article == $dbw[$i][0]) 	$typnonrech = true;
							if ($serviz[29] != "2" && TYP_REACT == $dbw[$i][0]) $typnonrech = true;
							if ($rech_download == $dbw[$i][0] )	$typnonrech = true;
							if ($rech_faq == $dbw[$i][0]) 		$typnonrech = true;
							if ($rech_livreor == $dbw[$i][0])   $typnonrech = true;
							if ($rech_liens == $dbw[$i][0])     $typnonrech = true;
							if ($rech_news == $dbw[$i][0])      $typnonrech = true;
							if ($rech_blog == $dbw[$i][0])      $typnonrech = true;
							if ($rech_photo == $dbw[$i][0])     $typnonrech = true;
							if ($rech_agenda == $dbw[$i][0])    $typnonrech = true;
							if ($rech_forum == $dbw[$i][0])     $typnonrech = true;
							if ($typnonrech)
							{
								$dbw[$i][4] = 'a';
								$dbw1[] = $dbw[$i];
							}
						}	
						if (!isset($rech_archive)) $rech_archive = TYP_ARCH;
						$dbw = array();
						if ($rech_archive != '')
						{
							$dbw = SelectDBFieldsByStatus(ReadDBFields(DOCIDARCH), 'a');
							for ($i = 0; $i < count($dbw); $i++) 
							{
								$typnonrech = false;
								if ($rech_article == $dbw[$i][0]) 	$typnonrech = true;
								if ($serviz[29] != "2" && TYP_REACT == $dbw[$i][0]) $typnonrech = true;
								if ($rech_download == $dbw[$i][0])	$typnonrech = true;
								if ($rech_faq == $dbw[$i][0]) 		$typnonrech = true;
								if ($rech_livreor == $dbw[$i][0])   $typnonrech = true;
								if ($rech_liens == $dbw[$i][0])     $typnonrech = true;
								if ($rech_news == $dbw[$i][0])      $typnonrech = true;
								if ($rech_blog == $dbw[$i][0])      $typnonrech = true;
								if ($rech_photo == $dbw[$i][0])     $typnonrech = true;
								if ($rech_agenda == $dbw[$i][0])    $typnonrech = true;
								if ($rech_forum == $dbw[$i][0])      $typnonrech = true;
								if ($typnonrech)
								{
									$dbw[$i][4] = 'i';
									$dbw2[] = $dbw[$i];									
								}
							}	
						}
                    $dbwork = array_merge($dbw1, $dbw2);
                }
                @rsort($dbwork);

                $type_unilingue = array(TYP_GUESTBK, TYP_REACT, TYP_REBLOG, TYP_FORUM);
                $searchlng0     = (empty($this->searchlng) || $this->searchlng == $lang[0]);
                $searchlng1     = (empty($this->searchlng) || $this->searchlng == $lang[1]);

                $j = 0;
                foreach ($dbwork as $dbw) {
					if (isset($dbw[4]))
					{
						if ($dbw[4] == 'a')
							ReadDoc($dbw[1]);
						else
							ReadDoc($dbw[1], ARCHDBBASE);	
						if ($type == TYP_FORUM) {
							$frcat = explode(',', $fieldb2);
							if (!empty($frcat[1]) && !isAccessGranted($frcat[1])) {
								continue;
							}
						} else {
							if (!isAccessGranted($fieldmod) || !isAuthorizedPublication($creadate)) {
								continue;
							}
						}
						if ((empty($type)) || (!in_array($type, $searchfor))) continue;
						$unilingue = in_array($type, $type_unilingue);
						if ($searchlng0 || $unilingue) {
							//----- Variable $occurrence qui stocke les résultats attribuée à $found[5] ----//
							$txt        = strip_tags(strtolower($author.' '.$fielda1.' '.$fieldb1.' '.$fieldc1));
							$occurrence = $this->Rechercher($expression, $txt);
							$note       = $occurrence['stat'][0];
							if ($note > 0) {
								$found[0][$j] = $note;
								$found[1][$j] = (empty($moddate) ? $creadate : $moddate);
								$found[2][$j] = ($unilingue ? 0 : 1);
								$found[3][$j] = $type;
								$found[4][$j] = $dbw[1];
								$found[5][$j] = $occurrence;
								$found[6][$j] = $dbw[4];
								$j++;
							}
						}
						if ($searchlng1 && $type != TYP_FORUM) {
							$txt        = strip_tags(strtolower($author.' '.$fielda2.' '.$fieldb2.' '.$fieldc2));
							$occurrence = $this->Rechercher($expression, $txt);
							$note       = $occurrence['stat'][0];
							if ($note > 0) {
								$found[0][$j] = $note;
								$found[1][$j] = (empty($moddate) ? $creadate : $moddate);
								$found[2][$j] = 2;
								$found[3][$j] = $type;
								$found[4][$j] = $dbw[1];
								$found[5][$j] = $occurrence;
								$found[6][$j] = $dbw[4];
								$j++;
							}
						}
					}
					
                }
                unset($dbwork);
                if (count($found) > 0) {
                    array_multisort($found[0], SORT_DESC, SORT_NUMERIC, $found[1], SORT_DESC, $found[2], SORT_ASC, $found[3], $found[4], $found[5], $found[6]);
						if (empty($this->searchin) && $rech_forum != '') {
							$cattitle = array();
							$catwork  = ReadDBFields(DBFORUMCAT);
							for ($i = 0; $i < count($catwork); $i++){
								$cattitle[$catwork[$i][0]][1] = $catwork[$i][1];
								$cattitle[$catwork[$i][0]][2] = $catwork[$i][2];
							}
							unset($catwork);
							$fridthd = array();
							$frtitle = array();
							$fridcat = array();
							$frwork = ReadDBFields(DBFORUM);
							for ($i = 0; $i < count($frwork); $i++) {
								$fridthd[$frwork[$i][1]] = $frwork[$i][2];
								$frtitle[$frwork[$i][1]] = $frwork[$i][5];
								$fridcat[$frwork[$i][1]] = $frwork[$i][12];
							}
							unset($frwork);
						}
                }
            }
        }
        //------------ Caractères à remplacer dans str_word_count ----------------------//
        $accent     = array("à", "â", "é", "è", "ê", "ë", "î", "ï", "ô", "ù");
        $sansaccent = array("a", "a", "e", "e", "e", "e", "i", "i", "o", "u");
        //------------------------------------------------------------------------------//
        if (empty($found)) {
            $out .= '
        <p class="text-center"><strong>'.$web[62].'</strong></p>';
            return array('SCH', $out);
        }
		if (!isset($id)) $id = 1;
		if (!is_numeric($id)) $id = 1;								
        if (empty($id)) $id = 1;
        if (count($found[0]) != 0) {
            if (count($found[0]) == 1) $out .= '
        <p class="text-center"><strong>1</strong> '.$web[60].' <strong>'.stripslashes($this->search).'</strong></p>';
            else $out .= '
        <p class="text-center"><strong>'.count($found[0]).'</strong> '.$web[61].' <strong>'.stripslashes($this->search).'</strong></p><hr /> ';
            //--------------------------- Affiche les mots ignorés -------------------------//
            if (!empty($ignore)) {
                $out .= '<div class="text-center m-auto">( ';
                if (count($ignore) == 1) $out .= ($web[569].'<b>'.$ignore[0].'</b>'.$web[570].$web[573]);
                else {
                    $out .= ($web[571]);
                    for ($i = 0; $i < count($ignore); $i++) {
                        $out .= ('<b>'.$ignore[$i].'</b>, ');
                    }
                    $out .=($web[572].$web[573]);    
                }
                $out .= ')</div>';
            }
            //------------------------------------------------------------------------------//
            if ($pertinencemin > 0) $out .= '<div class="text-center m-auto fontXsmall">'.$web[574].'</div>';
            $out .= '<br /><br />';
            $maxresult   = count($found[0]) - ($nbresult * ($id - 1)) > $nbresult ? $nbresult : count($found[0]) - ($nbresult*($id-1));
            $imgprtsearc = '<i class="fas fa-print c-pointer" title="'.$web[22].'"></i> '.$web[22].' ';
            $imgdnlsearc = '<i class="fas fa-database c-pointer" title="'.$web[362].'"></i> '.$web[362].' ';
            $ilng        = ($lng != $lang[1] ? 0 : 1);
            //---- Modification de l'affichage des barres de résultat qui tient compte: ----//
            //---- de l'absence de résultat sur un terme de la requête ---------------------//
            //---- de la présence de l'expression exacte dans les résultats ----------------//
            $notemax = $found[0][0];
            $first   = $found[5][0];
            for ($i = 1; $i < count($first['mot']); $i++) {
                if ($first['nb'][$i] == 0) 
                    $notemax += $found[0][0] * (count($first['mot']) - $i) / count($first['mot']);
            }
            if ($first['nb'][0] > 0) {
                $coef    = 1 + 2 / 10;
                $notemax = $notemax * $coef;
            }
            //------------------------------------------------------------------------------//
            $stop          = false;
            $nbresultshown = 0;
            $prive         = 0; /// modif accès privé
            for ($i = $nbresult*($id-1); $i < Min($nbresult*$id, count($found[0])); $i++) {
                if (!$stop) {
                    if ($found[6][$i] == 'a') ReadDoc($found[4][$i]);
                    else ReadDoc($found[4][$i], ARCHDBBASE);
                    $acces  = isAccessGranted($fieldmod) && isAuthorizedPublication($creadate);
                    $doctit = '';
                    $doclnk = '';
                    $doctxt = '';
                    $docinv = '';
                    switch ($found[2][$i]) {
                    case 0 :
                        $doclang = 'lang';
                        switch ($found[3][$i]) {
                        case TYP_GUESTBK :
                            $doctit = '<strong>'.getLabel($nom[9 + 10 * $ilng]).' :</strong> '.$web[39].' <strong>
                              '.$fielda1.'</strong> - '.$web[6].' '.$author.' '.$web[7].' '.FormatDate($creadate);
                            $doclnk = $site['URLR'] == 'on' ? $lng.'-'.$urlrw[7].'-3-'.$fileid.$z2 : $site['GB'].'.php?lng='.$lng.'&amp;pg='.$fileid.$z2;
                            break;
                        case TYP_REACT :
                            $doctit0 = '<strong>'.$web[187].' :</strong> ';
                            $doctit2 = $web[185].$fielda1.' - '.$web[6].' '.$author.' '.$web[7].' '.FormatDate($creadate);
                            $doclnk  = $site['URLR'] == 'on' 
                              ? $lng.'-'.$urlrw[20].'-101-'.$fielda2.'-'.$fileid.$z2 
                              : 'articles.php?lng='.$lng.'&amp;pg='.$fielda2.'&amp;react='.$fileid.$z2;
                            ReadDoc($fielda2);
                            $acces   = isAccessGranted($fieldmod) && isAuthorizedPublication($creadate);
                            $doctit1 = ($lng == $lang[0] ? $fieldb1 : $fieldb2);
                            $doctit  = $doctit0.' <strong>'.$doctit1.' :</strong> '.$doctit2;
                            break;
                        case TYP_REBLOG :
                            $doctit0 = '<strong>'.$web[381].' - '.$web[379].' :</strong> ';
                            $doctit2 = $web[185].$fielda1.' - '.$web[6].' '.$author.' '.$web[7].' '.FormatDate($creadate);
                            if ($found[6][$i] == 'a') {
                                $doclnk = $site['URLR'] == 'on' 
                                  ? $lng.'-'.$urlrw[1].'-47-'.$fielda2.'-'.$fileid.$z2 
                                  : $site['BL'].'.php?lng='.$lng.'&amp;pg='.$fielda2.'&amp;react='.$fileid.$z2;
                                ReadDoc($fielda2);
                            } 
                            else {
                                $doclnk = $site['URLR'] == 'on' 
                                  ? $lng.'-'.$urlrw[1].'-48-'.$fielda2.'-'.$fileid.$z2 
                                  : 'blogarch.php?lng='.$lng.'&amp;pg='.$fielda2.'&amp;react='.$fileid.$z2;
                                ReadDoc($fielda2, ARCHDBBASE);
                            }
                            $acces   = isAccessGranted($fieldmod) && isAuthorizedPublication($creadate);
                            $doctit1 = ($lng == $lang[0] ? $fieldb1 : $fieldb2);
                            $doctit  = $doctit0.' <strong>'.$doctit1.' :</strong> '.$doctit2;
                            break;
                        case TYP_FORUM :
                            $frcat  = explode(',', $fieldb2);
                            $acces  = isForumAccessGranted($frcat[0]);
                            $doctit = '<strong>'.getLabel($nom[22 + $ilng]).'</strong>';
                            if (!empty($fridcat[$fielda1]) && !empty($cattitle[$fridcat[$fielda1]][1 + $ilng])) {
                                $doctit .= ' : <strong>'.$cattitle[$fridcat[$fielda1]][1 + $ilng].'</strong>';
                            }
                            if (!empty($fieldb1)) {
                                $doctit .= ' : '.$web[63].' '.$fielda1.' <strong>'.$fieldb1.'</strong>';
                                if ($found[6][$i] == 'a') {
                                    $thrdid = GetThreadId($fielda1);
                                    $doclnk = $site['URLR'] == 'on' 
                                      ? $lng.'-'.$urlrw[5].'-4-'.$thrdid.'-'.(empty($fridcat[$fielda1]) ? 0 : $fridcat[$fielda1]).$z2 
                                      : $site['TH'].'.php?lng='.$lng.'&amp;pg='.$thrdid.
                                      GetOptionnalArg('cat', (empty($fridcat[$fielda1]) ? 0 : $fridcat[$fielda1])).$z2;
                                } 
                                else {
                                    $thrdid = GetThreadId($fielda1, DBTHREADARCH);
                                    $doclnk = $site['URLR'] == 'on' 
                                      ? $lng.'-'.$urlrw[5].'-42-'.$thrdid.'-'.(empty($fridcat[$fielda1]) ? 0 
                                      : $fridcat[$fielda1]).$z2 : 'threadarch.php?lng='.$lng.'&amp;pg='.$thrdid.
                                      GetOptionnalArg('cat', (empty($fridcat[$fielda1])? 0: $fridcat[$fielda1])).$z2;
                                }
                            } 
                            else {
                                $doctit .= ' : '.$web[63].' '.$fielda1.' <strong>
                                  '.(empty($frtitle[$fielda1]) ? '' : $frtitle[$fielda1]).'</strong> - '.$web[68].$fielda2;
                                if (empty($fridthd[$fielda1]) || empty($fridcat[$fielda1]))
                                    $doclnk = '';
                                else {
                                    if ($found[6][$i] == 'a')
                                        $doclnk = $site['URLR'] == 'on' 
                                          ? $lng.'-'.$urlrw[5].'-5-'.$fridthd[$fielda1].'-'.$fridcat[$fielda1].'-'.ceil($fielda2/$serviz[20]).'#R'.$fielda2 
                                          : $site['TH'].'.php?lng='.$lng.'&amp;pg='.$fridthd[$fielda1].
                                          GetOptionnalArg('cat', $fridcat[$fielda1]).GetOptionnalArg('id', ceil($fielda2/$serviz[20])).'#R'.$fielda2;
                                    else
                                        $doclnk = $site['URLR'] == 'on' 
                                          ? $lng.'-'.$urlrw[5].'-52-'.$fridthd[$fielda1].'-'.$fridcat[$fielda1].'-'.ceil($fielda2/$serviz[20]).'#R'.$fielda2 
                                          : 'threadarch.php?lng='.$lng.'&amp;pg='.$fridthd[$fielda1].
                                          GetOptionnalArg('cat', $fridcat[$fielda1]).GetOptionnalArg('id', ceil($fielda2/$serviz[20])).'#R'.$fielda2;
                                }
                            }
                            $doctit .= ' - '.$web[6].' '.$author.' '.$web[7].' '.FormatDate($creadate);
                            break;
                        }
                        $doctxt = WrapLongWords(CutLongWord(strip_tags($fieldc1), $longword));
                        $doctxtlong = $fieldc1;
                        break;
                    case 1 :
                        $doclang = $lang[0];
                        switch ($found[3][$i]) {
                        case TYP_ART :
                            if (substr($fieldd1, 0, 3) == 'MNU') {
                                $mnuid    = intval(substr($fieldd1, 4));
                                $mnutitle = $this->GetTitleMenuBox($mnuid);
                                $doctit   = '<strong>'.$mnutitle.' :</strong> ';
                            } 
                            else $doctit = '<strong>'.$fileid.' :</strong> ';
                            if (!empty($doctit)) {
                                $doctit .= ' <strong>'.$fielda1.'</strong> - ';
                                $doctit .= '<strong>'.$fieldb1.'</strong>';
                                $doclnk  = $site['URLR'] == 'on' 
                                  ? $lang[0].'-'.$urlrw[20].'-100-'.$fileid.$z2 
                                  : 'articles.php?lng='.$lang[0].'&amp;pg='.$fileid.$z2;
                                $docinv  = '<a href="'.($site['URLR'] == 'on' 
                                  ? $lang[0].'-'.$urlrw[20].'-1001-'.$fileid.'-1' 
                                  : 'articles.php?lng='.$lang[0].'&amp;pg='.$fileid.'&amp;prt=1').'" target="_blank">'.$imgprtsearc.'</a>';
                            }
                            break;
                        case TYP_DNLOAD :
                            $doctit = '<strong>'.getLabel($nom[1]).' :</strong> '.$fieldb1;
                            $doctit .= ' - '.$web[6].' '.$author.' '.$web[7].' '.FormatDate($moddate);
                            $doclnk = $site['URLR'] == 'on' 
                              ? $lang[0].'-'.$urlrw[11].'-4-'.$fileid.$z2 
                              : $site['DN'].'.php?lng='.$lang[0].'&amp;pg='.$fileid.$z2;
                            $docinv = '<a href="javascript:PopupWindow(\''.($site['URLR'] == 'on' ? 
                              $lang[0].'-'.$urlrw[11].'-6-'.$fileid : 'dwnld.php?lng='.$lang[0].'&amp;pg='.$fileid)
                              .'\',\'dnload\',400,265,\'no\',\'no\')">'.$imgdnlsearc.'</a>';
                            break;
                        case TYP_LINKS :
                            $doctit = '<strong>'.getLabel($nom[3]).' :</strong> '.$fieldb1;
                            $doclnk = $site['URLR'] == 'on' ? $lang[0].'-'.$urlrw[6].'-1-'.$fileid.$z2 : 'links.php?lng='.$lang[0].'&amp;pg='.$fileid.$z2;
                            break;
                        case TYP_NEWS :
                            $doctit  = '<strong>'.getLabel($nom[7]).' :</strong> <strong>'.$fieldb1.'</strong>';
                            $doctit .= ' - '.$web[6].' '.$author.' '.$web[7].' '.FormatDate($moddate);
                            if ($found[6][$i] == 'a')
                                $doclnk = $site['URLR'] == 'on' 
                                  ? $lang[0].'-'.$urlrw[8].'-2-'.$fileid.$z2 
                                  : $site['NE'].'.php?lng='.$lang[0].'&amp;pg='.$fileid.$z2;
                            else
                                $doclnk = $site['URLR'] == 'on' 
                                  ? $lang[0].'-'.$urlrw[8].'-262-'.$fileid.$z2 
                                  : 'newsarch.php?lng='.$lang[0].'&amp;pg='.$fileid.$z2;
                            break;
                        case TYP_BLOG :
                            $doctit  = '<strong>'.getLabel($nom[42]).' :</strong> <strong>'.$fieldb1.'</strong>';
                            $doctit .= ' - '.$web[6].' '.$author.' '.$web[7].' '.FormatDate($moddate);
                            if ($found[6][$i] == 'a')
                                $doclnk = $site['URLR'] == 'on' 
                                  ? $lang[0].'-'.$urlrw[1].'-4-'.$fileid.$z2 
                                  : $site['BL'].'.php?lng='.$lang[0].'&amp;pg='.$fileid.$z2;
                            else
                                $doclnk = $site['URLR'] == 'on' 
                                  ? $lang[0].'-'.$urlrw[1].'-42-'.$fileid.$z2 
                                  : 'blogarch.php?lng='.$lang[0].'&amp;pg='.$fileid.$z2;
                            break;
                        case TYP_FREEBOX :
                            $doctit = '<strong>'.$fieldb1.'</strong>';
                            $doctit .= ' - '.$web[6].' '.$author.' '.$web[7].' '.FormatDate($moddate);
                            $doclnk = $site['URLR'] == 'on' ? $lang[0].'-'.$urlrw[0].$z2 : 'index.php?lng='.$lang[0].$z2;
                            break;
                        case TYP_FAQ :
                            $doctit = '<strong>'.getLabel($nom[24]).' :</strong> '.$fieldb1;
                            $doclnk = $site['URLR'] == 'on' 
                              ? $lang[0].'-'.$urlrw[4].'-3-'.$fileid.$z2 
                              : 'faq.php?lng='.$lang[0].'&amp;pg='.$fileid.$z2;
                            $docinv = '<a href="'.($site['URLR'] == 'on' 
                              ? $lang[0].'-'.$urlrw[4].'-4-'.$fileid.'-1' 
                              : 'faq.php?lng='.$lang[0].'&amp;pg='.$fileid.'&amp;prt=1').'" target="_blank">'.$imgprtsearc.'</a>';
                            break;
                        case TYP_PHOTO :
                            $doctit = '<strong>'.getLabel($nom[2]).' :</strong> '.$fieldb1;
                            $doclnk = $site['URLR'] == 'on' 
                              ? $lang[0].'-'.$urlrw[9].'-4-'.$fileid.$z2 
                              : 'photorama.php?lng='.$lang[0].'&amp;pg='.$fileid.$z2;
                            break;
                        case TYP_AGENDA :
                            $doctit = $web[287].' '.$fieldb1;
                            if ($found[6][$i] == 'a')
                                $doclnk = $site['URLR'] == 'on' 
                                  ? $lang[0].'-'.$urlrw[17].'-2-'.$fileid.'-'.$fileid.'-1'.$z2 
                                  : 'agenda.php?lng='.$lang[0].'&amp;idpg='.$fileid.'-&amp;pg='.$fileid.'&agv=1'.$z2;
                            else
                                $doclnk = "";
                            break;
                        }
                        $doctxt = WrapLongWords(CutLongWord(strip_tags($fieldc1), $longword));
                        $doctxtlong = $fieldc1;
                        break;
                    case 2 :
                        $doclang = $lang[1];
                        switch ($found[3][$i]) {
                        case TYP_ART :
                            if (substr($fieldd1, 0, 3) == 'MNU') {
                                $mnuid    = intval(substr($fieldd1, 4));
                                $mnutitle = $this->GetTitleMenuBox($mnuid);
                                $doctit   = '<strong>'.$mnutitle.' :</strong> ';
                            } 
                            else $doctit = '<strong>'.$fileid.' :</strong> ';

                            if (!empty($doctit)) {
                                $doctit .= ' <strong>'.$fielda2.'</strong> - ';
                                $doctit .= '<strong>'.$fieldb2.'</strong>';
                                $doclnk  =  $site['URLR'] == 'on' 
                                  ? $lang[1].'-'.$urlrw[20].'-100-'.$fileid.$z2 
                                  : 'articles.php?lng='.$lang[1].'&amp;pg='.$fileid.$z2;
                                $docinv  = '<a href="'.($site['URLR'] == 'on' 
                                  ? $lang[1].'-'.$urlrw[20].'-1001-'.$fileid.'-1' 
                                  : 'articles.php?lng='.$lang[1].'&amp;pg='.$fileid.'&amp;prt=1').'" target="_blank">'.$imgprtsearc.'</a>';
                            }
                            break;
                        case TYP_DNLOAD :
                            $doctit = '<strong>'.getLabel($nom[11]).' :</strong> '.$fieldb2;
                            $doctit .= ' - '.$web[6].' '.$author.' '.$web[7].' '.FormatDate($moddate);
                            $doclnk = $site['URLR'] == 'on' 
                              ? $lang[1].'-'.$urlrw[11].'-4-'.$fileid.$z2 
                              : $site['DN'].'.php?lng='.$lang[1].'&amp;pg='.$fileid.$z2;
                            $docinv = '<a href="javascript:PopupWindow(\''.($site['URLR'] == 'on' 
                              ? $lang[1].'-'.$urlrw[11].'-6-'.$fileid : 'dwnld.php?lng='.$lang[1].'&amp;pg='.$fileid)
                              .'\', \'dnload\', 400, 265, \'no\', \'no\')">'.$imgdnlsearc.'</a>';
                            break;
                        case TYP_LINKS :
                            $doctit = '<strong>'.getLabel($nom[13]).' :</strong> '.$fieldb2;
                            $doclnk = $site['URLR'] == 'on' 
                              ? $lang[1].'-'.$urlrw[6].'-1-'.$fileid.$z2 
                              : 'links.php?lng='.$lang[1].'&amp;pg='.$fileid.$z2;
                            break;
                        case TYP_BLOG :
                            $doctit = '<strong>'.getLabel($nom[42]).' :</strong> <strong>'.$fieldb2.'</strong>';
                            if ($found[6][$i] == 'a')
                                $doclnk = $site['URLR'] == 'on' 
                                  ? $lang[1].'-'.$urlrw[1].'-4-'.$fileid.$z2 
                                  : $site['BL'].'.php?lng='.$lang[1].'&amp;pg='.$fileid.$z2;
                            else
                                $doclnk = $site['URLR'] == 'on' 
                                  ? $lang[1].'-'.$urlrw[1].'-42-'.$fileid.$z2 
                                  : 'blogarch.php?lng='.$lang[1].'&amp;pg='.$fileid.$z2;
                            break;
                        case TYP_NEWS :
                            $doctit = '<strong>'.getLabel($nom[17]).': </strong> <strong>'.$fieldb2.
                              '</strong> - '.$web[6].' '.$author.' '.$web[7].' '.FormatDate($moddate);
                            if ($found[6][$i] == 'a')
                                $doclnk = $site['URLR'] == 'on' 
                                  ? $lang[1].'-'.$urlrw[8].'-2-'.$fileid.$z2 
                                  : $site['NE'].'.php?lng='.$lang[1].'&amp;pg='.$fileid.$z2;
                            else
                                $doclnk = $site['URLR'] == 'on' 
                                  ? $lang[1].'-'.$urlrw[8].'-262-'.$fileid.$z2 
                                  : 'newsarch.php?lng='.$lang[1].'&amp;pg='.$fileid.$z2;
                            break;
                        case TYP_FREEBOX :
                            $doctit = '<strong>'.$fieldb2.'</strong>';
                            $doclnk = $site['URLR'] == 'on' ? $lang[1].'-'.$urlrw[0].$z2 : 'index.php?lng='.$lang[1].$z2;
                            break;
                        case TYP_FAQ :
                            $doctit = '<strong>'.getLabel($nom[25]).' :</strong> '.$fieldb2;
                            $doclnk = $site['URLR'] == 'on' 
                              ? $lang[1].'-'.$urlrw[4].'-3-'.$fileid.$z2 
                              : 'faq.php?lng='.$lang[1].'&amp;pg='.$fileid.$z2;
                            $docinv = '<a href="'.($site['URLR'] == 'on' 
                              ? $lang[1].'-'.$urlrw[4].'-4-'.$fileid.'-1' 
                              : 'faq.php?lng='.$lang[1].'&amp;pg='.$fileid.'&amp;prt=1').'" target="_blank">'.$imgprtsearc.'</a>';
                            break;
                        case TYP_PHOTO :
                            $doctit = '<strong>'.getLabel($nom[12]).' :</strong> '.$fieldb2;
                            $doclnk = $site['URLR'] == 'on' 
                              ? $lang[1].'-'.$urlrw[9].'-4-'.$fileid.$z2 
                              : 'photorama.php?lng='.$lang[1].'&amp;pg='.$fileid.$z2;
                            break;
                        case TYP_AGENDA :
                            $doctit = $web[287].' '.$fieldb2;
                            if ($found[6][$i] == 'a')
                                $doclnk = $site['URLR'] == 'on' 
                                  ? $lang[1].'-'.$urlrw[17].'-22-'.$fileid.'-'.$fileid.'-1-'.$this->tconfig.$z2 
                                  : 'agenda.php?lng='.$lang[1].'&amp;idpg='.$fileid.'-&amp;pg='.$fileid.'&agv=1&amp;tconfig='.$this->tconfig.$z2;
                            else
                                $doclnk = "";
                            break;
                        }
                        $doctxt     = WrapLongWords(CutLongWord(strip_tags($fieldc2), $longword));
                        $doctxtlong = $fieldc2;
                        break;
                    }
                    for ($j=0; $j<count($this->tokens); $j++) {
                        $doctxt = preg_replace('/('.$this->tokens[$j].')/i', '<strong>\\1</strong>', $doctxt);
                    }
                    //--- Applique le style aux occurrences en excluant celles dans les balises ----//
                    for ($j = count($this->tokens) - 1; $j >= 0; $j--) {
                        if ($styleocc[4] == 'on') $background = $bgcolor[fmod($j, count($bgcolor))].';';
                        $document = '';
                        $text = preg_split('/('.$this->tokens[$j].')/i', $doctxtlong, -1, PREG_SPLIT_OFFSET_CAPTURE);
                        if ($text[0][1] != 0) $document .= substr($doctxtlong, 0, $text[0][1] - 1);
                        for ($k = 0; $k < count($text) - 1; $k++) {
                            if (strrpos($text[$k][0], '<') > strrpos($text[$k][0], '>') || strpos($text[$k+1][0], '>') < strpos($text[$k+1][0], '<')) 
                                $document .= $text[$k][0].substr($doctxtlong, $text[$k+1][1]-strlen($this->tokens[$j]), strlen($this->tokens[$j]));
                            else 
                                $document .= $text[$k][0].$stylespan.$background.'">'.substr($doctxtlong, 
                                  $text[$k+1][1]-strlen($this->tokens[$j]), strlen($this->tokens[$j])).$endstyle;
                        }
                        $document  .= $text[count($text) - 1][0];
                        $doctxtlong = $document;
                    }
                    //------------------------------------------------------------------------------//
                    $doctit = strip_tags($doctit, '<strong>');
                    //-------- Récupération des occurrences pour chaque résultat et pondération ----//
                    //-------- Calcul de la pertinence du résultat et modification de l'affichage --//
                    $occurrence = $found[5][$i];
                    if ($occurrence['nb'][0] > 0) $found[0][$i] = $found[0][$i]*$coef;
                    if ($found[0][$i] > $notemax) $found[0][$i] = $notemax;
                    $pourcent   = ceil($found[0][$i] / $notemax * $longbarre);
                    $pertinence = ceil($pourcent / $longbarre * 100);
                    $stop       = ($pertinence < $pertinencemin) ? true : false;
                    if ($stop) break;
                    $width = $longbarre + 36;
                    if ($acces) {
                        $out .= '
<style>
.divbars { width: '.$width.'px; }
.imggreen { width: '.$pourcent.'px !important; height: 8px !important; }
.imgyellow { width: '.($longbarre - $pourcent).'px !important; height: 8px !important; margin-left: -3px; }
.imglang { padding: 2px 0 0 4px; width: 18px !important; height: 12px !important; }
</style>
  <div class="bord">
	<div class="table-responsive-md">
    <div class="d-table w-100">
	  <div class="d-table-row trheadsearch">
		<div class="d-table-cell">
		  <div class="divbars">
		    <img src="'.CHEMIN.'inc/img/bars/vert.gif" alt="" class="align-middle imggreen" />';
                        if ($pourcent < $longbarre) $out .= '
			<img src="'.CHEMIN.'inc/img/bars/jaune.gif" alt="" class="align-middle imgyellow" />';
                        $out .= '
			<i class="flg-'.$doclang.' align-middle imglang" title="'.$doclang.'"></i>
		  </div>
		</div>
		<div class="d-table-cell w-100 text-left">
          &nbsp;'.($doclnk == '' ? $doctit : '<a href="'.$doclnk.'" title="'.$web[326].'">'.$doctit.'</a>').'
        </div>
	  </div>
    </div>
    </div>
    <div class="table-responsive-md">
	<div class="d-table w-100">
	  <div class="d-table-row fontXsmall trheadsearch">
		<div class="d-table-cell w-100 text-center">&nbsp;'.$web[566].$pertinence.'% - ';
                        if (str_word_count(str_replace($accent, $sansaccent, $occurrence['mot'][0])) > 1 && $occurrence['nb'][0] > 0) {
                            $out .= $web[567].'<u><b>'.$occurrence['mot'][0].':</b></u> '.$occurrence['nb'][0].' - ';
                            $firstocc = 1;
                        } 
                        else $firstocc = 0;
                        $out  .= $web[568].' : ';
                        $tiret = false;
                        for ($j = $firstocc; $j < count($occurrence['mot']); $j++) {
                            if ($styleocc[4] == 'on') $background = $bgcolor[fmod($j, count($bgcolor))].';';
                            if ($occurrence['nb'][$j] > 0) {
                                if ($tiret) $out .= ' - ';
                                $out .= $stylespan.$background.'">'.$occurrence['mot'][$j].$endstyle.' : '.$occurrence['nb'][$j];
                                $tiret = true;
                            }
                        }
                        $out .= '
		</div>
	  </div>
    </div>
    </div>
    <div class="table-responsive-md">
    <div class="d-table w-100">
	  <div class="d-table-row text-left">
		<div class="d-table-cell tr2headsearch">
		  <div id="court'.$i.'">
			<i class="far fa-plus-square c-pointer" title="'.$web[429].'" 
              onclick="ActiveMenu(\'court\',\'long\','.$nbresult*($id-1).','.$maxresult.','.$i.')">
            </i>
			&nbsp; '.$doctxt.' ...
		  </div>
		  <div id="long'.$i.'" style="display:none;">
			<div>
			  <i class="far fa-minus-square c-pointer" title="'.$web[57].'" 
                onclick="DesactiveItem(\'court\',\'long\','.$i.')">
              </i>
			  &nbsp; '.($hideprint == 'on' ? '' : $docinv).'
			</div>';
						if (strlen($doctxtlong) > 750) $out .= '
			<div class="txthiddensearch">'.$doctxtlong.'</div>
		  </div>';
						else $out .= '
			<div>'.$doctxtlong.'</div>
		  </div>';
						$out .= '
	    </div>
      </div>
      </div>
	</div>
  </div>
	<br />';
                    } 
                    else $prive++;
                } //------------------------- fin d'affichage pour $pertinence > $pertinence min
                $nbresultshown++;
            }
        } 
        else {
            $out .= '<div class="text-center m-auto">'.$web[62].'</div><br />';
            if (!empty($this->search) && count($this->tokens) == 0) {
                $words = explode(' ', $this->search);
                if (count($words) == 1) $out .= 
                    '<div class="text-center m-auto">'.$web[569].'<b>'.$words[0].'</b>'.$web[575].'</div><br />';
                elseif (count($words) > 1) {
                    $out .= '<div class="text-center m-auto">'.$web[571];
                    for ($j = 0; $j < count($words); $j++) 
                        $out .= '<b>'.$words[$j].'</b>'.(($j == count($words) - 2) ? ' & ' : ', ');
                    $out .= $web[576].'</div><br />';
                }
            }
        }
        //------------ On récupère la requête initiale pour la page suivante... --------//
        $this->search = stripslashes($savesearch);
        //------------------------------------------------------------------------------//
        $max = 1;
        if ($pertinencemin > 0) {
            $nbresulttotshown = $nbresult * ($id - 1) + $nbresultshown;
            $nextpage         = ($nbresultshown == $nbresult && $pertinence > $pertinencemin) ? 1 : 0;
            $max              = max($max, $nbresulttotshown + $nextpage);
        }
        if ($pertinencemin > 0 && $max < count($found[0])) {
            if ($nbresulttotshown == 1) $out .= '
<div class="text-center m-auto">'.$web[559].'<div><br />';
            elseif ($nbresulttotshown > 1) $out .= '
<div class="text-center m-auto">'.$web[560].'<b>'.$nbresulttotshown.'</b>'.$web[561].'<div><br />';
            $out .= '
<div class="text-center m-auto">
  <a href="'.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[12].'-3-'.$this->cat.'-'.$this->search.'-'.$this->searchin.'-'.$this->searchlng.'-0-'.$max.'-'.$id.$z2 
      : 'search.php?lng='.$lng.GetOptionnalArg('cat', $this->cat).'&amp;search='.$this->search.
      '&amp;searchin='.$this->searchin.'&amp;searchlng='.$this->searchlng.'&amp;pert=0&amp;max='.$max.'&amp;id='.$id.$z2).'">
    '.(($id > 1) ? $web[563] : $web[562]).'
  </a>
</div>
<br /><br />';
        }
        if ($pertinencemin == 0) {
            $out .= 
        GetNavBar((false 
          ? $lng.'-'.$urlrw[12].'-3-'.(empty($this->cat) ? '0' : $this->cat).'-'.$this->search.'-'.(empty($this->searchin) ? '0' : $this->searchin).'-'.$this->searchlng.'-0-'.$max.'-'
          : 'search.php?lng='.$lng.GetOptionnalArg('cat', $this->cat).'&amp;search='.$this->search.
            '&amp;searchin='.$this->searchin.'&amp;searchlng='.$this->searchlng.'&amp;pert='.$pertinencemin.'&amp;max='.$max.'&amp;id='), 
          count($found[0]), $id, $nbresult, $z2).'
<br />
<div class="text-center m-auto">'.$web[564].'<br />
  <a href="'.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[12].'-3-'.$this->cat.'-'.$this->search.'-'.$this->searchin.'-'.$this->searchlng.'-'.$savepertinencemin.'-'.$max.'-1'.$z2 
      : 'search.php?lng='.$lng.GetOptionnalArg('cat', $this->cat).'&amp;search='.$this->search.
      '&amp;searchin='.$this->searchin.'&amp;searchlng='.$this->searchlng.'&amp;pert='.$savepertinencemin.'&amp;max='.$max.'&amp;id=1'.$z2).'">
    '.$web[565].'
  </a>
</div>
<br /><br />';          
        } 
        if ($prive > 0) $out .= '<hr />
<p class="text-center"><strong>... '.$web[446].'</strong></p>';
        if ($restricted) {
            $out .= '
<hr />
<p class="text-center">'.$web[441].'</p>';
            if ($members[19] != '0' && $members[19] != '3') $out .= '
<p class="text-center">
  <a href="'.CHEMIN.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[15].'-5-new-'.$this->tconfig.$z2 
      : $site['USR'].'.php?lng='.$lng.'&amp;uuser=new&amp;tconfig='.$this->tconfig.$z2).'" title="'.$web[160].'">
    ['.$web[160].']
  </a>
</p>';
        }
        return array('SCH', $out);
    }
}
?>