<?php
/*******************************************************************************
 *   Functions for boxes
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
 * v6.00.24 (August 12, 2026) : Delete lines log Modify isAdmin function
 ******************************************************************************/
 
if (stristr($_SERVER['SCRIPT_NAME'], 'func_boxes.php')) {
    header('location:../index.php');
    die();
}

################################################################################

function fbox_ariane($pos = NULL, $args = NULL) {
    global $lng, $lang, $nom, $page, $site, $web, $urlrw, $endurl, $npage, $pgtype, $topmess, $tconfig, $z2;
	$root   = $site[3].($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng);
    $stroot = strip_tags(getLabel($nom[($lng == $lang[0] ? 0 : 10)]));
	$sep    = '&raquo;';
    switch ($pgtype) {
    case 5 :
        $ariane = '<span class="arianeOff">'.$npage[5].'</span>';
        break;
    case 18 : // plugins
        if (isset($topmess) && !empty($topmess)) {
            $plugin_name = strip_tags($topmess);
        } else {
			$tmp = explode('/', $endurl);
			foreach ($tmp as $key=>$val) {
				if ($val == 'plugins') break;
			}
			$plugin_dirname = 'plugins/'.$tmp[$key + 1];
			$plugin_file    = CHEMIN.$plugin_dirname.'/plugin.inc';
            $plugin_name    = '';
            if (is_file($plugin_file)) {
                $plugin_bar_name = '';
                include $plugin_file;
                if (!empty($plugin_bar_name)) {
                    $plugin_name = $plugin_bar_name;
                }
            }
            if (empty($plugin_name)) {
                $plugin_name = '';
                if (is_file(CHEMIN.'admin/'.$plugin_dirname.'/plugin.inc')) {
                    $plugin_admin_name = '';
                    include_once CHEMIN.'admin/'.$plugin_dirname.'/plugin.inc';
                    if (!empty($plugin_admin_name)) {
                        $plugin_name = $plugin_admin_name;
                    }
                }
            }
            if (empty($plugin_name)) {
                $plugin_name = $web[465];
            }
        }
        $ariane = '<span class="arianeOn"><a href="'.$root.'" title="'.$stroot.'"> '.$stroot.' </a></span> '.$sep.' <span class="arianeOff">'.$plugin_name.'</span>';
        break;
    case 13 : // blog
        $topmess2 = explode(' - ', empty($topmess) ? $npage[$pgtype] : strip_tags($topmess));
        $last     = ' '.$sep.' <span class="arianeOff">'.array_pop($topmess2).'</span>';
        $middle   = (0 == count($topmess2)) ? '' : ' '.$sep.' <span class="arianeOn">
          <a href="'.$site[3].($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[1].$z2 : $site['BLS'].'.php?lng='.$lng.$z2).'" title="'.$topmess2[0].'">'.$topmess2[0].'</a></span>';
        $ariane   = '<span class="arianeOn"><a href="'.$root.'" title="'.$stroot.'"> '.$stroot.' </a></span>'.$middle.$last;
        break;
    case 0 :
    case 3 : // forum
        $topmess2 = explode(' - ', empty($topmess) ? $npage[$pgtype] : strip_tags($topmess, '<a>'));
        $last     = ' '.$sep.' <span class="arianeOff">'.array_pop($topmess2).'</span>';
        $middle   = '';
        foreach ($topmess2 as $item) {
            $middle .= ' '.$sep.' <span class="arianeOn">'.$item.'</span>';
        }
        $ariane = '<span class="arianeOn"><a href="'.$root.'" title="'.$stroot.'"> '.$stroot.' </a></span>'.$middle.$last;
        break;
    case FALSE :
        $ariane = '<span class="arianeOn">
          <a href="'.$root.'" title="'.$stroot.'"> '.$stroot.' </a>
          </span> '.$sep.' <span class="arianeOff">'.(empty($topmess) ? '???' : strip_tags($topmess, '<a>')).'</span>';
        break;
    default :
        $topmess2 = explode(' - ', empty($topmess) ? $npage[$pgtype] : strip_tags($topmess, '<a>'));
        $last = ' '.$sep.' <span class="arianeOff">'.array_pop($topmess2).'</span>';
        $middle = '';
        foreach ($topmess2 as $item) {
            $middle .= ' '.$sep.' <span class="arianeOn">'.$item.'</span>';
        }
        $ariane = '<span class="arianeOn"><a href="'.$root.'" title="'.$stroot.'"> '.$stroot.' </a></span>'.$middle.$last;
        break;
    }
	$out = '
<div class="ariane'.(in_array($pos, array(BOX_TOP, BOX_BOTTOM)) ? ' ARN'.$pos.$tconfig : '').' text-start p-2 m-0">'
	.@$web[637].$ariane.'
</div>';
	if (in_array($pos, array(BOX_TOP, BOX_BOTTOM))) {
		return $out;
	} else {
		if (in_array($pos, array(BOX_LEFT, BOX_RIGHT))) {
			return '
<div class="'.$pos.$tconfig.'ARN">'.boxthin($pos, '', $out, ' ARN'.$pos.$tconfig).'
</div>';
		} else {
			return boxwide($pos, '', $out, ' ARN'.$pos.$tconfig, 'nav');
		}
	}
}

################################################################################

function fbox_print($pos = NULL, $args = NULL) {
    global $serviz, $web, $endurl, $pgtype, $site, $lng, $prt, $urlrw, $page, $tconfig;
	$url1 = str_replace('&', '&amp;', $endurl).'&amp;prt=-2';
	$url2 = str_replace('&', '&amp;', $endurl).'&amp;prt=2';
	$url3 = str_replace('&', '&amp;', $endurl).'&amp;prt=-3';
	$url4 = str_replace('&', '&amp;', $endurl).'&amp;prt=3';
	if ($site['URLR'] == 'on' && strpos($endurl, '/admin/') === false) {
		$infos = explode('/', $_SERVER['SCRIPT_NAME']);
		$qstrs = explode('&', $_SERVER['QUERY_STRING']);
		$fname = explode('.', $infos[count($infos) - 1]);
		$urls  = array(
			'index'=>$urlrw[0].'-1', $site['BLS']=>$urlrw[1].'-1', 'blogs'=>$urlrw[1].'-1' ,'selpg'=>$urlrw[1].'-21', $site['BL'].'pg'=>$urlrw[1].'-41', 'blogsarch'=>$urlrw[1].'-71', $site['BL'].'selpg'=>$urlrw[1].'-21', 
		    'blogsarchselpgcatdatejdateid'=>$urlrw[1].'-72', 'blogarchpg'=>$urlrw[1].'-43', $site['BL'].'pgreact'=>$urlrw[1].'-470', 'blogarchpgreact'=>$urlrw[1].'-480', 
		    'contact'=>$urlrw[2].'-1', 'connect'=>$urlrw[3].'-2', 'connectconnectuuser'=>$urlrw[3].'-31', 'compte'=>$urlrw[3].'-141', 'lostpass'=>$urlrw[3].'-160', 
			'faq'=>$urlrw[4].'-1', 'faqtconfig'=>$urlrw[4].'-11','faqpg'=>$urlrw[4].'-4', 'faqid'=>$urlrw[4].'-5', 'faqdirfaqtconfig'=>$urlrw[4].'-10',$site['FRT']=>$urlrw[5].'-1', $site['FR'].'cat'=>$urlrw[5].'-3', 
			$site['TH'].'pgcat'=>$urlrw[5].'-40', $site['TH'].'pgcatid'=>$urlrw[5].'-50', 'threadarchpgcat'=>$urlrw[5].'-420', 'threadarchpgcatid'=>$urlrw[5].'-520', 
			'fortopicarch'=>'frm-9',
			'links'=>$urlrw[6].'-2', 'linkspg'=>$urlrw[6].'-3', $site['GB']=>$urlrw[7].'-1', $site['GB'].'id'=>$urlrw[7].'-20', $site['GB'].'pg'=>$urlrw[7].'-30', 
			$site['NE']=>$urlrw[8].'-1', $site['NE'].'pgid'=>$urlrw[8].'-31', $site['NE'].'pg'=>$urlrw[8].'-20', 'newsarch'=>$urlrw[8].'-261', 'newsarchpg'=>$urlrw[8].'-263', 
			'newsarchpgid'=>$urlrw[8].'-41', 'photorama'=>$urlrw[9].'-1', 'photorama2pgtconfig'=>$urlrw[9].'-20', 'photoramapg'=>$urlrw[9].'-40', 'photoramatconfig'=>$urlrw[9].'-41',  
			'download'=>$urlrw[11].'-1', 'downloaddirdnl'=>$urlrw[11].'-9','downloadpg'=>$urlrw[11].'-5', 'downloadid'=>$urlrw[11].'-70', 'search'=>$urlrw[12].'-1', 
			'tinymsg'=>$urlrw[13].'-1', 'tinymsgaction'=>$urlrw[13].'-31', 'tinymsgactionid'=>$urlrw[13].'-51', 'tinymsgactionidtofrom'=>$urlrw[13].'-41', 
			'tinymsgactiontofrom'=>$urlrw[13].'-21', $site['PG'].'typ'=>$urlrw[14].'-0', $site['PG'].'typmodnumpg'=>$urlrw[14].'-10', $site['PG'].'typnumpg'=>$urlrw[14].'-20', 
			$site['PG'].'typcat'=>$urlrw[14].'-30', $site['PG'].'typpg'=>$urlrw[14].'-40', $site['USR']=>$urlrw[15].'-0', $site['USR'].'uuser'=>$urlrw[15].'-50', 
			$site['USR'].'modifuuser'=>$urlrw[15].'-60', 'agendamoisanagv'=>$urlrw[17].'-211', 'agendamoisanagvid'=>$urlrw[17].'-231', 
			'agendaidpgpgagv'=>$urlrw[17].'-221', $site['NL']=>$urlrw[18].'-0', 'rss'=>$urlrw[19].'-1', 'articlespg'=>$urlrw[20].'-1001', 'articlespg'=>$urlrw[20].'-102', 
			'articlespgtconfig'=>$urlrw[20].'-102', 'articlespgreact'=>$urlrw[20].'-1010', 'articlespg'=>$urlrw[20].'-1020', 
			'articlespgmnuid'=>$urlrw[20].'-1030', 'articlespgtconfig'=>$urlrw[20].'-1020', 'articlespgmnuidtconfig'=>$urlrw[20].'-1030'
			);
		$vars = '';
		$vals = '-';
/*
		echo 'query string: '.$_SERVER['QUERY_STRING'].'<br />';
    	echo 'SCRIPT_NAME: '.$_SERVER['SCRIPT_NAME'].'<br />';
		echo 'Infos count : '.count($infos).'<br />';
		echo 'Infos 0: '.$infos[0].'<br />';
		echo 'Infos 1: '.$infos[1].'<br />';
		echo 'Infos 2: '.$infos[2].'<br />';
*/		
/*
		foreach($_SERVER as $key => $value)
			echo '$_SERVER['.$key.']='.$value .'<br />';
*/
		$info[1] = trim($info[1]);
$mystring = 'abc';
$findme   = 'a';
$pos = strpos($_SERVER['SCRIPT_NAME'], 'plugins');
        if ($pos === false)
		{
//		    echo 'Apres if Infos 1: '.$infos[1].'<br />';			
			foreach($qstrs as $key=>$val) {
//					echo 'Avant et dans qstrs key: '.$key.'<br />';
//					echo 'Avant et dans qstrs val: '.$val.'<br />';
					
				if ($key != 'lng') {
					$param = explode('=', $val);
					if ($param[0] != 'lng' )
					{	
						if ($fname[0] != 'fortopic' && $fname[0] != 'blog'  && $fname[0] != 'blogs' && $fname[0] != 'news' && $fname[0] != 'agenda' 
							&& $fname[0] != 'contact' && $fname[0] != 'compte' && $fname[0] != 'tinymsg' && $fname[0] != 'download'
							&& $fname[0] != 'links' && $fname[0] != 'guestbk' && $fname[0] != 'photorama')
						{
							if ($param[0] == 'tconfig' && $param[1] >= 0)
							{
	//							echo 'Dans Foreach qstrs param0: '.$param[0].'<br />';	
	//							echo 'Dans Foreach qstrs param1: '.$param[1].'<br />';				
								$vars .= $param[0];
								$vals .= $param[1].'-';
	//							echo 'Dans Foreach qstrs vars: '.$vars.'<br />';	
	//							echo 'Dans Foreach qstrs vals: '.$vals.'<br />';
							}
						}
						if ($param[0] != 'tconfig')
						{
	//						echo 'Dans Foreach qstrs param0: '.$param[0].'<br />';	
	//						echo 'Dans Foreach qstrs param1: '.$param[1].'<br />';				
							$vars .= $param[0];
							$vals .= $param[1].'-';
	//						echo 'Dans Foreach qstrs vars: '.$vars.'<br />';	
	//						echo 'Dans Foreach qstrs vals: '.$vals.'<br />';
						}
					}
				}
			}
			$urlr = '';
/*			
			echo 'fname[0]: '.$fname[0].'<br />';
			echo 'vars: '.$vars.'<br />';	
			echo 'vals: '.$vals.'<br />';	
*/
			foreach($urls as $key=>$url) {
/*
					echo 'AVnt foreach Urls => key: '.$key.'<br />';
					echo 'AVnt foreach Urls =>  vars: '.$vars.'<br />';
					echo 'AVnt foreach Urls =>  fname[0].vars: '.$fname[0].$vars.'<br />';
					echo 'AVnt foreach Urls =>  fname[0]: '.$fname[0].'<br />';
*/					
					if ($fname[0].$vars == $key)
					{		
//							echo 'Dans fname.var if key: '.$key.'<br />';
//							echo 'Dans fname.var if vars: '.$vars.'<br />';	
							$urlr = $url;
//							echo 'Dans fname.var url: '.$url.'<br />';
//							echo 'Dans fname.var urlr: '.$urlr.'<br />';
							break;
					}
					elseif($fname[0] == $key) 
					{
//						echo 'Dans fname = key : '.$key.'<br />';
						$urlr = $url;
//						echo 'Dans fnamE url: '.$url.'<br />';
//						echo 'Dans fname urlr: '.$urlr.'<br />';					
					}
				}
			if ($fname[0] == 'allarticles')
			{
				$url1 = $site[3].$urlr.$lng.'-'.'allart-2'.$vals.'-2';
				$url2 = $site[3].$urlr.$lng.'-'.'allart-2'.$vals.'2';
				$url3 = $site[3].$urlr.$lng.'-'.'allart-2'.$vals.'-3';
				$url4 = $site[3].$urlr.$lng.'-'.'allart-2'.$vals.'3';
			}
			elseif ($fname[0] == 'annoncesplus')
			{
				$url1 = $site[3].$urlr.$lng.'-'.'ap-8'.$vals.'-2';
				$url2 = $site[3].$urlr.$lng.'-'.'ap-8'.$vals.'2';
				$url3 = $site[3].$urlr.$lng.'-'.'ap-8'.$vals.'-3';
				$url4 = $site[3].$urlr.$lng.'-'.'ap-8'.$vals.'3';
			}
			else
			{
				$url1 = $lng.'-'.$urlr.$vals.'-2';
				$url2 = $lng.'-'.$urlr.$vals.'2';
				$url3 = $lng.'-'.$urlr.$vals.'-3';
				$url4 = $lng.'-'.$urlr.$vals.'3';
			}	
		}
		else
			{
				$url1 = $site[3].$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'].'&prt=-2';
				$url2 = $site[3].$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'].'&prt=2';

			}				
/*
		echo 'url1: '.$url1.'<br />';	
		echo 'url2: '.$url2.'<br />';		
		echo 'url3: '.$url3.'<br />';	
		echo 'url4: '.$url4.'<br />';	
*/
	}
    if ($pgtype != 11) {
        $out = '
<div class="boxprint'.(in_array($pos, array(BOX_TOP, BOX_BOTTOM)) ? ' PRT'.$pos.$tconfig : '').' mx-3 p-2">
  <span class="boxprint1 float-right">
    <a href="'.$url1.'" target="_blank"><i class="fas fa-eye mx-2" title="'.$web[264].'"></i></a>&nbsp;
    <a href="'.$url2.'" target="_blank"><i class="fas fa-print" title="'.$web[22].'"></i></a>&nbsp;
  </span>
  <span class="boxprint2 float-right">'.$web[470].'</span>
  ';
        $out .= '
</div>';
		if (in_array($pos, array(BOX_TOP, BOX_BOTTOM))) {
			return $out;
		} else {
			if (in_array($pos, array(BOX_LEFT, BOX_RIGHT))) {
				return '
<div class="'.$pos.$tconfig.'PRT">
  '.boxthin($pos, '', $out, ' PRT'.$pos.$tconfig).'
</div>';
			} else {
				return boxwide($pos, '', $out, ' PRT'.$pos.$tconfig);
			}
		}
    } else {
        return FALSE;
    }
}

################################################################################

function fbox_logo($pos = NULL, $args = NULL) {
    global $lang, $lng, $page, $site, $skn_logo, $urlrw, $tconfig, $meskin;
    $img  = explode('|', $page[4]);
    $urli = substr($img[0], 0, 1) == 'I' ? CHEMIN.'img/' : $meskin.'img/';
    $logo = $skn_logo != '' && empty($page[4]) ? $skn_logo : $urli.$img[1];
    if ($logo != $urli) {
		$url = ($site['URLR'] == 'on')? $lng.'-'.$urlrw[0]: 'index.php?lng='.$lng;
        return '
  <div class="logo m-0 p-0'.(in_array($pos, array(BOX_TOP, BOX_BOTTOM)) ? ' LO'.$pos.$tconfig : '').'">
    <a href="'.$site[3].$url.'" title="'.stripslashes($site[($lang[0] == $lng ? 0 : 11)]).'">
      <img src="'.$logo.'" alt="'.stripslashes($site[($lang[0] == $lng ? 0 : 11)]).'" />
    </a>
  </div>
';
    } else {
        return FALSE;
    }
}

################################################################################
 
function getMenuFlags($pos = NULL) {
    global $lng, $lang, $page, $site, $endurl, $urlrw, $web, $z2, $flag1, $flag2;
	$flags = '';
    if ($lang[1] != '') { // boxlang
		if ($site['URLR'] == 'on') {
			$len     = strlen(parse_url($site[3], PHP_URL_PATH));
            $flagurl = substr($endurl, $len);
            $pds     = (strstr($flagurl, '/')) ? strripos( $flagurl, '/') : 0;
            if ($pds > 1) {
				$len           = strlen(substr($flagurl, $pds + 1));
                $chemin_plugin = substr($flagurl, 0, $pds + 1);
                $flagurl       = substr($flagurl, $pds + 1);
			} else $chemin_plugin = '';
			$flagurl0 = $site[3].$chemin_plugin.$lang[$lng == $lang[0] ? 1 : 0].'-'.substr($flagurl, 3);
			$flagurl1 = $site[3].($lng == $lang[0] ? $lang[1] : $lang[0]).'-'.$urlrw[0];
            $flagurl = strlen($flagurl) > 3 ? $flagurl0 : $flagurl1;
		} else {
			$slng = strpos($endurl, '?lng=') > 0 || strpos($endurl, '&lng=') > 0 ? '' : 'lng='.$lng;
			if ($slng != '') $endurl .= (strpos($endurl, '?') > 0) ? '&'.$slng : '?'.$slng;
			$flagurl = str_replace('&', '&amp;', $endurl);
			if ($lng == $lang[0]) $flagurl = str_replace('lng='.$lang[0], 'lng='.$lang[1], $flagurl); 
			if ($lng == $lang[1]) $flagurl = str_replace('lng='.$lang[1], 'lng='.$lang[0], $flagurl); 
		}
		$flags = '
      <script>
        $(document).ready(function(){
          $(\'.menuiconflag\').popover({
            title:"'.$web[87].'",
            content:"'.($lng == $lang[0] ? $web[555].': <b>'.$lang[1].'</b>' : $web[556].': <b>'.$lang[0].'</b>').'",
            trigger:"hover",
            placement:"auto",
            html:true
          });
        });
      </script>
      <a href="'.$flagurl.(strpos($flagurl, 'index.php') > 0 || strpos($flagurl, $urlrw[0]) > 0 ? '' : $z2).'" class="menuiconflag" title="'.$web[87].'">
        <i class="flg-'.($lng == $lang[0] ? $lang[1] : $lang[0]).'"></i>
      </a>';
        if ($pos == BOX_LEFT || $pos == BOX_RIGHT && $pos != NULL) {
            // Drapeau menu boite latérale
			$flags = '
      <script>
        $(document).ready(function(){
          $(\'.menuiconflag\').popover({
            title:"'.$web[87].'",
            content:"'.($lng == $lang[0] ? $web[555].': <b>'.$lang[1].'</b>' : $web[556].': <b>'.$lang[0].'</b>').'",
            trigger:"hover",
            placement:"auto",
            html:true
          });
        });
      </script>
      <span class="menuIcons_flag">
        <a href="'.$flagurl.(strpos($flagurl, 'index.php') > 0 || strpos($flagurl, $urlrw[0]) > 0 ? '' 
            : $z2).'" class="menuiconflag float-left" title="'.$web[87].'">
          <i class="flg-'.($lng == $lang[0] ? $lang[1] : $lang[0]).'"></i>
        </a>
      </span>';
        }
    } // boxlang
    return $flags;
}
 
################################################################################

function getOptionsMenuIcons() {
    global $lng, $lang, $serviz, $web, $site, $menuico, $nom, $page, $tconfig, $urlrw, $userprefs, $z2,
		   $fielda1, $fieldb1, $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
    $tconfig = empty($tconfig) ? '0' : $tconfig;
    $menu    = array();
    $i       = ($lng == $lang[0])? 0 : 10;
    $j       = ($lng == $lang[0])? 0 : 1;
    // Admin
    if (isAdmin($userprefs[1])) {
    	$menu[strip_tags($web[144]).'_admin'] = array(
    		'id'      => 'admin',
            'z2'      => false,
    		'name'    => strip_tags($web[144]),
    		'title'   => $web[144],
            'pagedisp' => $tconfig,
            'granted' => true,
    		'icon'    => '<i class="fas fa-user-cog"></i>',
    		'url'     => ($site['URLR'] == 'on')? 'admin/'.$lng.'-'.$urlrw[0].'-19' : 'admin/admin.php?lng='.$lng,
			'base'    => 'admin.php',
			'target'  => '_self'
    	);
    }
    //Home
    if ($serviz[1] == 0 && $menuico[0] == 'on') {
        $menu[getLabel($nom[$i+0]).'_home'] = array(
            'id'       => 'home',
            'z2'       => false,
            'name'     => strip_tags(getLabel($nom[$i+0])),
            'title'    => getLabel($nom[$i+0], 1),
            'icon'     => '<i class="fas fa-home"></i>',
            'pageexec' => 0,
            'pagedisp' => $tconfig,
            'granted'  => true,
            'url'      => ($site['URLR'] == 'on') ? $lng.'-'.$urlrw[0] : 'index.php',
			'base'     => 'index.php',
			'target'   => '_self'
        );
    }
    //Blog
    if ($serviz[53] == 'on' && $menuico[2] == 'on') {
        $menutconf = $menuico[13] == 'ALL' ? $tconfig : $menuico[13];
    	$menu[getLabel($nom[$j+42]).'_blog'] = array(
    		'id'       => 'blog',
            'z2'       => $z2,
            'name'     => strip_tags(getLabel($nom[$j+42])),
            'title'    => getLabel($nom[$j+42], 1),
    		'icon'     => '<i class="fas fa-blog"></i>',
            'pageexec' => $menutconf,
            'pagedisp' => $menuico[23] == 'ALL' ? $tconfig : $menuico[23],
            'granted'  => empty($menuico[33]) ? true : isAccessGranted($menuico[33]),
    		'url'      => ($site['URLR'] == 'on') ? $lng.'-'.$urlrw[1].'-'.$menutconf : $site['BLS'].'.php',
    		'alturls'  => array($site['BL'].'.php', 'blogsarch.php', 'blogarch.php'),
			'base'     => $site['BLS'].'.php',
			'target'   => '_self'
    	);
    }
    //Contact
    if ($serviz[21] == 'on' && $serviz[99] == 'on' && $menuico[10] == 'on') {
        $menutconf = $menuico[21] == 'ALL' ? $tconfig : $menuico[21];
    	$menu[getLabel($nom[$j+46]).'_contact'] = array(
    		'id'       => 'contact',
            'z2'       => $z2,
            'name'     => strip_tags(getLabel($nom[$j+46])),
            'title'    => getLabel($nom[$j+46], 1),
    		'icon'     => '<i class="fas fa-envelope-open-text"></i>',
            'pageexec' => $menutconf,
            'pagedisp' => $menuico[31] == 'ALL' ? $tconfig : $menuico[31],
            'granted'  => true,
    		'url'      => ($site['URLR'] == 'on') ? $lng.'-'.$urlrw[2].'-'.$menutconf : $site['CT'].'.php',
			'base'     => $site['CT'].'.php',
			'target'   => '_self'
    	);
    }
    //Connexion
    if ($menuico[11] == 'on') {
		$jj              = $userprefs[1] == '' ? $j+14 : $j+28;
        $menutconf       = $serviz[172] == 'ALL' ? $tconfig : $serviz[172];
		$connectName     = $userprefs[1] == '' ? 'connect.php' : 'connect.php?lng='.$lng.'&amp;connect=no';
		$connectNameURLR = $userprefs[1] == '' ? $urlrw[3].'-0' : $urlrw[3].'-1-no';
    	$menu[getLabel($nom[$jj]).'_connect'] = array(
    		'id'       => 'connect',
            'z2'       => $z2,
            'name'     => strip_tags(getLabel($nom[$jj])),
            'title'    => getLabel($nom[$jj], 1),
    		'icon'     => '<i class="'.($userprefs[1] == '' ? 'fas fa-sign-in-alt' : 'fas fa-sign-out-alt').'"></i>',
            'pageexec' => $menutconf,
            'pagedisp' => $serviz[173] == 'ALL' ? $tconfig : $serviz[173],
            'granted'  => true,
    		'url'      => ($site['URLR'] == 'on') ? $lng.'-'.$connectNameURLR.'-'.$menutconf : $connectName,
			'base'     => 'connect.php',
			'target'   => '_self'
    	);
    }
    //FAQ
    if ($serviz[14] == 'on' && $menuico[6] == 'on') {
        $menutconf = $menuico[17] == 'ALL' ? $tconfig : $menuico[17];
    	$menu[getLabel($nom[$j+24]).'_faq'] = array(
    		'id'       => 'faq',
            'z2'       => $z2,
            'name'     => strip_tags(getLabel($nom[$j+24])),
            'title'    => getLabel($nom[$j+24], 1),
    		'icon'     => '<i class="far fa-question-circle"></i>',
            'pageexec' => $menutconf,
            'pagedisp' => $menuico[27] == 'ALL' ? $tconfig : $menuico[27],
            'granted'  => empty($menuico[37]) ? true : isAccessGranted($menuico[37]),
    		'url'      => ($site['URLR'] == 'on') ? $lng.'-'.$urlrw[4].'-9-'.$menutconf : 'faq.php',
			'base'     => 'faq.php',
			'target'   => '_self'
    	);
    }
    //Forum
	$dbcat = array();
	if ($serviz[13] == 'on')
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
    if ($serviz[13] == 'on' && $okfr && $menuico[8] == 'on') {
        $menutconf = $menuico[19] == 'ALL' ? $tconfig : $menuico[19];
    	$forumName = $site['FRT'];
    	$forumNameURLR = $urlrw[5].'-'.$menutconf;
    	$menu[getLabel($nom[$j+22]).'_forum'] = array(
    		'id'       => 'forum',
            'z2'       => $z2,
            'name'     => strip_tags(getLabel($nom[$j+22])),
            'title'    => getLabel($nom[$j+22], 1),
    		'icon'     => '<i class="fab fa-forumbee"></i>',
            'pageexec' => $menutconf,
            'pagedisp' => $menuico[29] == 'ALL' ? $tconfig : $menuico[29],
            'granted'  => empty($menuico[39]) ? true : isAccessGranted($menuico[39]),
    		'url'      => ($site['URLR'] == 'on') ? $lng.'-'.$forumNameURLR : $forumName.'.php',
    		'alturls'  => array($site['TH'].'.php', 'forumarch.php', 'fortopicarch.php', 'treadarch.php'),
			'base'     => $forumName.'.php',
			'target'   => '_self'
    	);
    }
    //Link
    if ($serviz[11] == 'on' && $menuico[5] == 'on') {
        $menutconf = $menuico[16] == 'ALL' ? $tconfig : $menuico[16];
    	$menu[getLabel($nom[$i+3]).'_links'] = array(
    		'id'       => 'links',
            'z2'       => $z2,
            'name'     => strip_tags(getLabel($nom[$i+3])),
            'title'    => getLabel($nom[$i+3], 1),
    		'icon'     => '<i class="fas fa-external-link-alt"></i>',
            'pageexec' => $menutconf,
            'pagedisp' => $menuico[26] == 'ALL' ? $tconfig : $menuico[26],
            'granted'  => empty($menuico[36]) ? true : isAccessGranted($menuico[36]),
    		'url'      => ($site['URLR'] == 'on') ? $lng.'-'.$urlrw[6].'-'.$menutconf : 'links.php',
			'base'     => 'links.php',
			'target'   => '_self'
    	);
    }
    //Guestbook
    if ($serviz[12] == 'on' && $menuico[7] == 'on') {
        $menutconf = $menuico[18] == 'ALL' ? $tconfig : $menuico[18];
    	$menu[getLabel($nom[$i+9]).'_guestbk'] = array(
    		'id'       => 'guestbook',
            'z2'       => $z2,
            'name'     => strip_tags(getLabel($nom[$i+9])),
            'title'    => getLabel($nom[$i+9], 1),
    		'icon'     => '<i class="fas fa-book"></i>',
            'pageexec' => $menutconf,
            'pagedisp' => $menuico[28] == 'ALL' ? $tconfig : $menuico[28],
            'granted'  => empty($menuico[38]) ? true : isAccessGranted($menuico[38]),
    		'url'      => ($site['URLR'] == 'on') ? $lng.'-'.$urlrw[7].'-'.$menutconf : $site['GB'].'.php',
			'base'     => $site['GB'].'.php',
			'target'   => '_self'
    	);
    }
    //News
    if ($serviz[8] == 'on' && $menuico[1] == 'on') {
        $menutconf = $menuico[12] == 'ALL' ? $tconfig : $menuico[12];
    	$menu[getLabel($nom[$i+7]).'_news'] = array(
    		'id'       => 'news',
            'z2'       => $z2,
            'name'     => strip_tags(getLabel($nom[$i+7])),
            'title'    => getLabel($nom[$i+7], 1),
    		'icon'     => '<i class="far fa-newspaper"></i>',
            'pageexec' => $menutconf,
            'pagedisp' => $menuico[22] == 'ALL' ? $tconfig : $menuico[22],
            'granted'  => empty($menuico[32]) ? true : isAccessGranted($menuico[32]),
    		'url'      => ($site['URLR'] == 'on') ? $lng.'-'.$urlrw[8].'-5-'.$menutconf : $site['NE'].'.php',
    		'alturls'  => array('newsarch.php'),
			'base'     => $site['NE'].'.php',
			'target'   => '_self'
    	);
    }
    //Photorama
    if ($serviz[9] == 'on' && $menuico[3] == 'on') {
    	$menutconf = $menuico[14] == 'ALL' ? $tconfig : $menuico[14];
        $photoName = 'photorama'; 
    	$menu[getLabel($nom[$i+2]).'_photo'] = array(
    		'id'       => 'photo',
            'z2'       => $z2,
            'name'     => strip_tags(getLabel($nom[$i+2])),
            'title'    => getLabel($nom[$i+2], 1),
    		'icon'     => '<i class="far fa-images"></i>',
            'pageexec' => $menutconf,
            'pagedisp' => $menuico[24] == 'ALL' ? $tconfig : $menuico[24],
            'granted'  => empty($menuico[34]) ? true : isAccessGranted($menuico[34]),
    		'url'      => ($site['URLR'] == 'on') ? $lng.'-'.$urlrw[9].'-'.$menutconf : 'photorama.php',
    		'alturls'  => array('photorama2.php'),
			'base'     => 'photorama.php',
			'target'   => '_self'
    	);
    }
    //Download
    if ($serviz[10] == 'on' && $menuico[4] == 'on') {
    	$menutconf = $menuico[15] == 'ALL' ? $tconfig : $menuico[15];
    	$menu[getLabel($nom[$i+1]).'_download'] = array(
    		'id'       => 'download',
            'z2'       => $z2,
            'name'     => strip_tags(getLabel($nom[$i+1])),
            'title'    => getLabel($nom[$i+1], 1),
    		'icon'     => '<i class="fas fa-download"></i>',
            'pageexec' => $menutconf,
            'pagedisp' => $menuico[25] == 'ALL' ? $tconfig : $menuico[25],
            'granted'  => empty($menuico[35]) ? true : isAccessGranted($menuico[35]),
    		'url'      => ($site['URLR'] == 'on') ? $lng.'-'.$urlrw[11].'-'.$menutconf : $site['DN'].'.php',
			'base'     => $site['DN'].'.php',
			'target'   => '_self'
    	);
    }
    //Plugins
    if (is_file(CHEMIN."inc/hplugins.inc")) {
    	include CHEMIN."inc/hplugins.inc";
    }
    //Icônes supplémentaires
    $dbicons = SelectDBFieldsByType(ReadDBFields(DOCID), TYP_MENUICO);
    foreach ($dbicons as $item) {
        if ($item[2] == 'a') {
            ReadDoc($item[1]);
			if ($fieldmail == $tconfig || $fieldmail == 'ALL') {
				$urls = explode('||', $lng == $lang[0] ? $fieldd1 : $fieldd2);
				if (preg_match('!https?://|ftps?://|www.!', $urls[0]))
					$url = $urls[0];
				else {
					$slng = (strpos($urls[0], '?lng=') > 0 || strpos($urls[0], '&lng=') > 0 || $site['URLR'] == 'on') ? '' : 'lng='.$lng;
					if ($slng != '') $urls[0] .= (strpos($urls[0], '?') > 0) ? '&'.$slng : '?'.$slng;
					$urls[0] = strpos($urls[0], 'tconfig') > 0 ? $urls[0] : $urls[0].'&tconfig='.($fieldweb == 'ALL' ? $tconfig : $fieldweb);
					if ($site['URLR'] == 'on' && empty($urls[1]) && strpos($urls[0], 'articles.php') !== false) {
						$pg      = substr($urls[0], strpos($urls[0], 'pg=') + 3);
						$pg      = strpos($pg, '&') > 0 ? substr($pg, 0, strpos($pg, '&')) : $pg;
						$urls[1] = $lng.'-'.$urlrw[20].($fieldweb == 'ALL' ? '-100-'.$pg : '-102-'.$pg.'-'.$fieldweb);
					}
					$url = (count($urls) == 1 || $site['URLR'] != 'on' ? $urls[0] : $urls[1]);
				}
				$icon_name = $lang[0] == $lng ? getLabel($fieldb1) : getLabel($fieldb2);
				$basename  = $site['URLR'] == 'on' ? basename($_SERVER['SCRIPT_NAME']) : parse_url($url, PHP_URL_PATH);
				$menu[$icon_name.'menuicon_'.$fielda1] = array(
					'id'       => strip_tags($fielda1),
                    'z2'       => preg_match('!https?://|ftps?://|www.!', $url) ? '' : $z2,
					'name'     => $lang[0] == $lng ? strip_tags(getLabel($fieldb1)) : strip_tags(getLabel($fieldb2)),
					'title'    => $icon_name,
					'icon'     => ($fieldc2 != '' && is_file(CHEMIN.'img/'.$fieldc2) 
                                     ? '<img src="'.CHEMIN.'img/'.$fieldc2.'" alt="" class="mt-n1" style="width:2rem;" />' 
                                     : '<i class="fas fa-external-link-alt"></i>'),
                    'pageexec' => $fieldweb == 'ALL' ? $tconfig : $fieldweb,
                    'pagedisp' => $fieldmail == 'ALL' ? $tconfig : $fieldmail,
                    'granted'  => empty($fieldmod) ? true : isAccessGranted($fieldmod),
					'url'      => $url,
					'base'     => basename(parse_url($url, PHP_URL_PATH)).'?'.parse_url($url, PHP_URL_QUERY),
					'target'   => $fieldc1,
					'docid'    => $item[1]
				);
				unset($urls);
			}
        }
    }
    ksort($menu);
    return $menu;
}

################################################################################
    
function fbox_menu($pos = NULL, $args = NULL) {
    global $lng, $lang, $drtuser, $nom, $page, $site, $serviz, $userprefs, $web, $menuico, 
	    $urlrw, $tconfig, $flag1, $flag2, $dynabox, $endurl, $charset, $z2;
    static $nbox;
    $nbox++;
	$title_menu = getLabel($nom[$lng == $lang[0] ? 44 : 45]);
	$title_menu = mb_substr(trim($title_menu), 0, 1, $charset) == '§' ? '' : DisplayDownArrow($dynabox[0], 'MI'.$nbox.$pos.$tconfig).$title_menu;
	$tconfig    = empty($tconfig) ? '0' : $tconfig;
	$flags      = getMenuFlags($pos); // boxlang
    if ($lang[1] != '' && ($pos == BOX_LEFT || $pos == BOX_RIGHT)) 
        $title_menu = $flags.'<div'.(empty($lang[1]) ? '' : ' class="titleboxmenu"').'>'.$title_menu.'</div>';
    $base_current = basename(substr($_SERVER["SCRIPT_NAME"], strlen(parse_url($site[3], PHP_URL_PATH))));
	$full_current = $base_current.'?'.$_SERVER["QUERY_STRING"];
    unset($dbw);
    $menu = getOptionsMenuIcons();
    $out = '';
    $currentMenuClass = '';
    if ($pos != BOX_LEFT && $pos != BOX_RIGHT) { // Menu horizontal
    	// Sections du menu
    	$found_current = false;
    	foreach ($menu as $section) {
			if ($section['granted'] === false) continue;
            if ($currentMenuClass != '') {
                $found_current = true;
                $currentMenuClass = '';
            }
            if (!$found_current) {
        		if (!isset($section['alturls'])) {
					if (isset($section['docid']))
						$currentMenuClass = ($full_current == $section['base']) ? ' active' : '';
					else
						$currentMenuClass = ($base_current == $section['base']) ? ' active' : '';
        		} else {
        		    $currentMenuClass = ($base_current == $section['base']) ? ' active' : '';
                    foreach($section['alturls'] as $url) {
                        if ($base_current == $url) {
                            $currentMenuClass = ' active';
                            break;
                        }
                    }
        		}
    		}
    		if (($page[9] != '' || $section['name'] != '') && $section['pagedisp'] == $tconfig) {
				if (strpos($section['url'], $site[3]) !== false) {
					$url  = CHEMIN.substr($section['url'], strlen($site[3]));
					$slng = (strpos($url, '?lng=') > 0 || strpos($url, '&lng=') > 0 || $site['URLR'] == 'on') ? '' : 'lng='.$lng.'&amp;tconfig='.$section['pageexec'];
					if ($slng != '') $url .= (strpos($url, '?') > 0) ? '&'.$slng : '?'.$slng;
				} elseif (preg_match('!https?://|ftps?://|www.!', $section['url'])) {
					$url = $section['url'];
				} else {
					$url  = CHEMIN.$section['url'];
					$slng = (strpos($url, '?lng=') > 0 || strpos($url, '&lng=') > 0 || $site['URLR'] == 'on') ? '' : 'lng='.$lng.'&amp;tconfig='.$section['pageexec'];
					if ($slng != '') $url .= (strpos($url, '?') > 0) ? '&'.$slng : '?'.$slng;
				}
				if ($section['target'] == '_POPUP')
					$target = '	onclick="window.open(this.href, \'popup'.(isset($section['docid']) ? $section['docid'] : '0').'\', 
						\'resizable=yes,status=yes,location=no,toolbar=yes,menubar=no,fullscreen=no,scrollbars=yes,dependent=no,width=1024,left=0,height=768,top=0\'); return false;"';
				else
					$target = strtolower($section['target']) == '_self' ? '' : ' target="'.strtolower($section['target']).'"';
				$contents = '
			<a href="'.$url.($section['z2'] ? $z2 : '').'" title="'.strip_tags($section['title']).'"'.$target.'>';
    			if ($section['name'] != '') {
    				$contents .= '
			  <span class="name_'.$section['id'].$currentMenuClass.'">'.($page[9] != '' ? $section['icon'].'&nbsp;' : '').$section['name'].'</span>';
    			}
    			$contents .= '
			</a>';

    			$out .= '
          <li id="menu_'.$section['id'].'" class="nav-item'.$currentMenuClass.'">
		    '.$contents.'
		  </li>';
    		}
    	}
      return '
<nav class="menuIcons navbar navbar-expand-md navbar-light '.$pos.$tconfig.'MI">
  '.$flags.(empty($flags) ? '<span class="mi-title ms-2">&nbsp;</span>' : '').'
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarMI" aria-controls="navbarMI" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarMI">
    <ul class="container-fluid navbar-nav mr-auto">
      '.$out.'
    </ul>
  </div>
</nav>
';
    } else { // Menu boite latérale
    	$out .= '
      <ul class="boxmenu">
    	';
    	// Sections du menu
    	$found_current = false;
    	$currentMenuClass = '';
    	foreach ($menu as $section) {
			if ($section['granted'] === false) continue;
            if ($currentMenuClass != '') {
                $found_current = true;
                $currentMenuClass = '';
            }
            if (!$found_current) {
        		if (!isset($section['alturls'])) {
					if (isset($section['docid']))
						$currentMenuClass = ($full_current == $section['url']) ? ' curr_item' : '';
					else
						$currentMenuClass = ($base_current == $section['base']) ? ' curr_item' : '';
        		} else {
        		    $currentMenuClass = ($base_current == $section['base'])? ' curr_item' : '';
                    foreach($section['alturls'] as $url) {
                        if ($base_current == $url) {
                            $currentMenuClass = ' curr_item';
                            break;
                        }
                    }
        		}
    		}
    		if ($page[9] != '' || $section['name'] != '') {
				if (strpos($section['url'], $site[3]) !== false) {
					$url  = CHEMIN.substr($section['url'], strlen($site[3]));
					$slng = (strpos($url, '?lng=') > 0 || strpos($url, '&lng=') > 0 || $site['URLR'] == 'on') ? '' : 'lng='.$lng.'&amp;tconfig='.$section['pageexec'];
					if ($slng != '') $url .= (strpos($url, '?') > 0) ? '&'.$slng : '?'.$slng;
				} elseif (preg_match('!https?://|ftps?://|www.!', $section['url'])) {
					$url = $section['url'];
				} else {
					$url  = CHEMIN.$section['url'];
					$slng = (strpos($url, '?lng=') > 0 || strpos($url, '&lng=') > 0 || $site['URLR'] == 'on') ? '' : 'lng='.$lng.'&amp;tconfig='.$section['pageexec'];
					if ($slng != '') $url .= (strpos($url, '?') > 0) ? '&'.$slng : '?'.$slng;
				}
				if (strtoupper($section['target']) == '_POPUP')
					$target = '	onclick="window.open(this.href, \'popup'.(isset($section['docid']) ? $section['docid'] : '0').'\', 
						\'resizable=yes,status=yes,location=no,toolbar=yes,menubar=no,fullscreen=no,scrollbars=yes,dependent=no,width=1024,left=0,height=768,top=0\'); return false;"';
				else
					$target = strtolower($section['target']) == '_self' ? '' : ' target="'.strtolower($section['target']).'"';
				$contents = '<a href="'.$url.(isset($section['z2']) && $section['z2'] ? $z2 : '').'" title="'.strip_tags($section['title']).'"'.$target.'>';
    			if($page[9] != '') {
    				$contents .= $section['icon'].'&nbsp;';
    			}
    			if ($section['name'] != '') {
    				$contents .= '<span class="name_'.$section['id'].$currentMenuClass.'">'.$section['name'].'</span>';
    			}
    			$contents .= '</a>';

    			$out .= '
          <li class="menu_'.$section['id'].$currentMenuClass.'">'.$contents.'</li>';
    		}
    	}
    	$out .= '
      </ul>';
        $out .= displayQuickConfig($pos, false, '', '', '', 29);
        return '
<div class="'.$pos.$tconfig.'MI">
  '.boxthin($pos, $title_menu, $out, ' MI'.$nbox.$pos.$tconfig, $dynabox[0], 'nav').'
</div>';
    }
}

################################################################################

function fbox_sidemenu($pos = NULL, $args = NULL) {
    global $lng, $lang, $drtuser, $nom, $page, $site, $serviz, $userprefs, $web, $menuico, 
	    $urlrw, $tconfig, $flag1, $flag2, $dynabox, $endurl, $charset, $z2;
    static $nbox;
    $nbox++;
	$tconfig    = empty($tconfig) ? '0' : $tconfig;
	$title_menu = getLabel($nom[$lng == $lang[0] ? 44 : 45]);
	$flags      = getMenuFlags(); // boxlang
    $menu       = getOptionsMenuIcons();
    $out        = '
<script>
function openNav() {
  document.getElementById("GY_sidenav").style.width = "300px";
  document.getElementById("overlay").style.display = "block";
}
function openConnect() {
  document.getElementById("GY_connect").style.width = "400px";
  document.getElementById("overlay").style.display = "block";
}
function closeNav() {
  document.getElementById("GY_sidenav").style.width = "0";
  document.getElementById("overlay").style.display = "none";
}
function closeConnect() {
  document.getElementById("GY_connect").style.width = "0";
  document.getElementById("overlay").style.display = "none";
}
function gotop() { window.location="#top"; }
function goz2() { window.location="#z2"; }
</script>
';
   	$out2 = '
    <div class="smopt">
      <ul class="boxmenu">';
    // Sections du menu
    $nameConnect = $nameAdmin = '';
    foreach ($menu as $section) {
        if ($section['granted'] === false) continue;
        if ($section['id'] == 'connect') $nameConnect = trim($section['name']);
        $pos         = strpos($nameConnect, '-->');
        $nameConnect = ($pos > 0 ? substr($nameConnect, $pos + 3) : $nameConnect);
        if ($section['id'] == 'admin') $nameAdmin = trim($section['name']);
        if (($page[9] != '' || $section['name'] != '') && $section['id'] != 'connect' && $section['id'] != 'admin') {
            if (strpos($section['url'], $site[3]) !== false) {
                $url  = CHEMIN.substr($section['url'], strlen($site[3]));
                $slng = (strpos($url, '?lng=') > 0 || strpos($url, '&lng=') > 0 || $site['URLR'] == 'on') ? '' : 'lng='.$lng.'&amp;tconfig='.$section['pageexec'];
                if ($slng != '') $url .= (strpos($url, '?') > 0) ? '&'.$slng : '?'.$slng;
            } elseif (preg_match('!https?://|ftps?://|www.!', $section['url'])) {
                $url = $section['url'];
            } else {
                $url  = CHEMIN.$section['url'];
                $slng = (strpos($url, '?lng=') > 0 || strpos($url, '&lng=') > 0 || $site['URLR'] == 'on') ? '' : 'lng='.$lng.'&amp;tconfig='.$section['pageexec'];
                if ($slng != '') $url .= (strpos($url, '?') > 0) ? '&'.$slng : '?'.$slng;
            }
            if (strtoupper($section['target']) == '_POPUP')
                $target = '	onclick="window.open(this.href, \'popup'.(isset($section['docid']) ? $section['docid'] : '0').'\', 
                    \'resizable=yes,status=yes,location=no,toolbar=yes,menubar=no,fullscreen=no,scrollbars=yes,dependent=no,width=1024,left=0,height=768,top=0\'); return false;"';
            else
                $target = strtolower($section['target']) == '_self' ? '' : ' target="'.strtolower($section['target']).'"';
            $contents = '<a href="'.$url.(isset($section['z2']) && $section['z2'] ? $z2 : '').'" title="'.strip_tags($section['title']).'"'.$target.'>';
            if ($section['name'] != '') {
                $contents .= '<div class="d-inline-block name_'.$section['id'].'">'.($page[9] != '' ? $section['icon'].'&nbsp;' : '').$section['name'].'</div>';
            }
            $contents .= '</a>';
            $out2     .= '
      <li>'.$contents.'</li>';
        }
    }
    $sym = ($nameConnect == $nom[14] || $nameConnect == $nom[15] || empty($userprefs[1]) 
      ? '<i class="fas fa-user" title="'.strip_tags($nom[$lng == $lang[0] ? 14 : 15]).'"></i>' 
      : '<i class="fas fa-user-slash" title="'.strip_tags($nom[$lng == $lang[0] ? 28 : 29]).'"></i>');
    $out2 .= '
      </ul>
    </div>';
    $out3 = '
    <div class="smlogo text-center ml-1 mr-4 mb-2 py-2 font-weight-bold">
      '.stripslashes($site[($lang[0] == $lng ? 0 : 11)]).'
    </div>';
    $out .= '
<div id="overlay" onclick="closeNav();closeConnect();"></div>
<div class="sidemenu">
  <div class="sideicon rounded px-3 pb-2" onclick="openNav(); '.($z2 != '' ? 'goz2();' : 'gotop();').'" title="'.$title_menu.'">
    <i class="fas fa-bars align-middle"></i>
  </div>
  '.(isAdmin($userprefs[1]) ? '
  <div class="admicon sidetexte">
    <a href="'.CHEMIN.($site['URLR'] == 'on' ? 'admin/'.$lng.'-'.$urlrw[0].'-19' : 'admin/admin.php?lng='.$lng).'" title="'.$nameAdmin.'">
      <i class="fas fa-user-cog"></i>
    </a>
  </div>' : '').'
  <div class="sidetexte" onclick="openConnect();'.($z2 != '' ? 'goz2();' : 'gotop();').'">'.$sym.'</div>
  <div class="sideflag">'.$flags.'</div>
</div>
<div class="sidenav" id="GY_sidenav">
  '.$out3.'<div><a class="closebtn" href="javascript:void(0)" onclick="closeNav();"><i class="fas fa-chevron-right"></i></a></div>'.$out2.'
</div>
<div class="sideconnect" id="GY_connect">
  <a class="closebtn" href="javascript:void(0)" onclick="closeConnect();"><i class="fas fa-chevron-right"></i></a>
  <div class="tblbox">'.fbox_user().'</div>
</div>';
    $out .= displayQuickConfig($pos, false, '', '', '', 29);
    return boxthin($pos, '', $out, ' SMI SMI'.$nbox.$pos.$tconfig, 'off', 'nav');
}

################################################################################

function fbox_cita($pos = NULL, $args = NULL) {
    global $lng, $lang, $drtuser, $serviz, $userprefs, $web, $page, $tconfig;
    global $type, $fileid, $status, $creadate, $moddate, $author, $email,
         $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
         $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
	$bc  = $args[0];
	$out = '';
    $dbw = SelectDBFields(TYP_THINK, 'a', '');
    if (count($dbw) > 0) {
		if ($serviz[108] == '') {
			srand(MakeSeed());
			$i = rand(0, count($dbw)-1);
			ReadDoc($dbw[$i][1]);
			if (isAuthorizedPublication($creadate)) {
				if ($lng == $lang[0]) {
					$txt1 = PathToImage($fieldc1);
					$txt2 = $fieldb1;
				} else {
					$txt1 = PathToImage($fieldc2);
					$txt2 = $fieldb2;
				}
				$txt1 = str_ireplace('<p>', ' ', $txt1);
				$txt1 = str_ireplace('</p>', ' ', $txt1);
				$out  = '
<div class="quote QOT'.$pos.$tconfig.'">
  <span class="labelcita">'.$web[5].'&nbsp;&nbsp;</span><span class="cita">'.$txt1.'&nbsp;&nbsp;</span><span class="author">'.$txt2.'</span>
</div>';
			}
		} else {
			$citatime = $serviz[109] * 1000;
			$out = '
<style>
  #lgcita'.$bc.' { position: relative; }
  #lgcita'.$bc.' div { display: none; position: absolute; }
  #lgcita'.$bc.' #citafirst'.$bc.' { position: relative; display: block; }
</style>
<div class="quote">
  <div id="lgcita'.$bc.'">';
			$n = min((int)$bc, count($dbw)) - 1;
			for ($i = 0; $i < count($dbw); $i++) {
				ReadDoc($dbw[$i][1]);
				if (!isAuthorizedPublication($creadate)) continue;
				if ($lng == $lang[0]) {
					$txt1 = PathToImage($fieldc1);
					$txt2 = $fieldb1;
				} else {
					$txt1 = PathToImage($fieldc2);
					$txt2 = $fieldb2;
				}
				$txt1 = str_ireplace('<p>', ' ', $txt1);
				$txt1 = str_ireplace('</p>', ' ', $txt1);
				$out .= '
    <div'.($i == $n ? ' id="citafirst'.$bc.'" ' : '').'>
      <span class="labelcita">'.$web[5].'&nbsp;&nbsp;</span><span class="cita">'.$txt1.'&nbsp;&nbsp;</span><span class="author">'.$txt2.'</span>
    </div>';
			}
			$out .= '
  </div>
  <script>
    function nextcita'.$bc.'(speed) {
      $("#citafirst'.$bc.'").hide();
      if ($("#citafirst'.$bc.'").next().fadeOut(speed).length)
        $("#citafirst'.$bc.'").next().fadeIn(speed,function(){
          $("#citafirst'.$bc.'").removeAttr("id").next().attr("id","citafirst'.$bc.'");
        });
      else
        $("#lgcita'.$bc.' div:first").fadeIn(speed,function(){
		  $("#citafirst'.$bc.'").removeAttr("id");
          $("#lgcita'.$bc.' div:first").attr("id","citafirst'.$bc.'");
		});
    }
    setInterval("nextcita'.$bc.'(0)",'.$citatime.');
  </script>
</div>';
		}
        $out .= displayQuickConfig($pos, false, 'think', '', '', 28);
		return $out;
    } else {
        return FALSE;
    }
}

################################################################################

function fbox_count($pos = NULL, $args = NULL) {
    global $lng, $lang, $drtuser, $nom, $page, $site, $serviz, $userprefs, $web, $tabcounter, 
           $dynabox, $tconfig, $charset;
    static $nbox;
    $nbox++;
    $i = $lng == $lang[0] ? 0 : 10;
    $out1 = $out2 = '
<p class="box text-start">&nbsp;';
    $out1 .= $tabcounter[0].'&nbsp;';
    $out2 .= $tabcounter[1].'&nbsp;';
    $out1 .= $tabcounter[0] == 1 ? $web[15] : $web[16];
    $out1 .= '
</p>';
    $out2 .= $tabcounter[1] == 1 ? $web[17] : $web[18];
    $out2 .= '
</p>';
    $out = $out1.$out2;
    $out .= displayQuickConfig($pos, false, 'counter', '', '', 31);
	$titre = getLabel($nom[$lng == $lang[0] ? 8 : 18]);
	$titre = mb_substr(trim($titre), 0, 1, $charset) == '§' ? '' : DisplayDownArrow($dynabox[2], 'CNT'.$nbox.$pos.$tconfig).$titre;
    return '
<div class="'.$pos.$tconfig.'CNT">
  '.boxthin($pos, $titre, $out, ' CNT'.$nbox.$pos.$tconfig, $dynabox[2]).'
</div>';
}

################################################################################

function fbox_ban($pos = NULL, $args = NULL) {
    global $lng, $lang, $drtuser, $serviz, $userprefs, $web, $page, $tconfig;
    global $type, $fileid, $status, $creadate, $moddate, $author, $email,
         $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
         $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
    static $nbox;
    $nbox++;
    $bc = $nbox;
    $dbw = SelectDBFields(TYP_BANNER, 'a', '');
    if (count($dbw) > 0) {
		if ($serviz[25] == '') {
			$out = '
<div class="banner">';
			srand(MakeSeed());
			$i = rand(0,count($dbw)-1);
			ReadDoc($dbw[$i][1]);
			if (isAuthorizedPublication($creadate)) {
				if ($lng == $lang[0]) {
					$img = $fielda1;
					$url = $fieldb1;
					$alt = empty($fieldd1) ? $url : $fieldd1;
				} else {
					$img = empty($fielda2) ? $fielda1 : $fielda2;
					$url = empty($fieldb2) ? $fieldb1 : $fieldb2;
					$alt = empty($fieldd2) ? $url : $fieldd2;
				}
				$img = CHEMIN.'img/'.$img;
				$extn = substr($img,-3);

				if (isImage($extn)) {
					if (!empty($url)) {
						$out .= '
  <a href="'.$url.'" target="_blank" title="'.$alt.'">';
					}
					$out .= '
    <img src="'.$img.'" alt="'.$alt.'" title="'.$alt.'" />';
					if (!empty($url)) {
						$out .= '
  </a>';
					}
				}
			}
		} else {
			$bantime = $serviz[26] * 1000;
			$out = '
<style>
  #ban'.$bc.' { position: relative; }
  #ban'.$bc.' a { display: none; position: absolute; }
  #ban'.$bc.' #banfirst'.$bc.' { position: relative; display: block; }
</style>
<div class="banner BAN'.$pos.$tconfig.'">
  <div id="ban'.$bc.'">';
			$n = min($bc, count($dbw)) - 1;
			for ($i = 0; $i < count($dbw); $i++) {
				ReadDoc($dbw[$i][1]);
				if (!isAuthorizedPublication($creadate)) continue;
				if ($lng == $lang[0]) {
					$img = $fielda1;
					$url = $fieldb1;
					$alt = empty($fieldd1) ? $url : $fieldd1;
				} else {
					$img = empty($fielda2) ? $fielda1 : $fielda2;
					$url = empty($fieldb2) ? $fieldb1 : $fieldb2;
					$alt = empty($fieldd2) ? $url : $fieldd2;
				}
				$img = CHEMIN.'img/'.$img;
				$extn = substr($img,-3);

				if (isImage($extn)) {
					if (!empty($url)) {
						$out .= '
    <a '.($i == $n ? 'id="banfirst'.$bc.'" ' : '').'href="'.$url.'" target="_blank" title="'.$alt.'">';
					}
					$out .= '
	  <img src="'.$img.'" alt="'.$alt.'" title="'.$alt.'" />';
					if (!empty($url)) {
						$out .= '
	</a>';
					}
				}
			}
			$out .= '
  </div>
  <script>
    function nextban'.$bc.'(speed) {
      if ($("#banfirst'.$bc.'").fadeOut(speed).next().length)
        $("#banfirst'.$bc.'").next().fadeIn(speed,function(){
          $("#banfirst'.$bc.'").removeAttr("id").next().attr("id","banfirst'.$bc.'");
        });
      else
        $("#ban'.$bc.' a:first").fadeIn(speed,function(){
		  $("#banfirst'.$bc.'").removeAttr("id");
          $("#ban'.$bc.' a:first").attr("id","banfirst'.$bc.'");
		});
    }
    setInterval("nextban'.$bc.'(0)",'.$bantime.');
  </script>';
		}
        $out .= displayQuickConfig($pos, false, 'banner', '', '', 27);
        return $out.'
</div>';
    } else {
        return FALSE;
    }
}

################################################################################

function fbox_search($pos = NULL, $args = NULL) {
    global $lng, $lang, $members, $nom, $page, $site, $serviz, $userprefs, $drtuser, $web, 
           $dynabox, $urlrw, $tconfig, $charset, $z2;
    static $nbox = 0;
    $nbox++;
    if (!isAccessGranted($members[23])) return FALSE;
    if ($serviz[24] != 'on') return FALSE;
    include CONFIGREP.'searchconfig.inc';
    $tconf = ($confpage == 'ALL' ? $tconfig : $confpage);
    if (in_array($pos, array(BOX_LEFT, BOX_RIGHT))) {
        $out = '
<div class="thinboxsearch TS'.$pos.$tconfig.'">
  <form class="text-center" name="thinsearch'.$nbox.'" 
      action="'.CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[12].'-'.$tconf.$z2 : 'search.php?lng='.$lng.'&amp;tconfig='.$tconf.$z2).'" 
      method="post">
    <input type="hidden" name="searchin" value="" />
    <div class="input-group mb-3 w-100 m-auto">
      <input name="search" class="texte form-control" type="text" placeholder="'.$web[58].'" value="" />
      <div class="input-group-append c-pointer" onclick="javascript:document.thinsearch'.$nbox.'.submit();">
        <span class="input-group-text">
          <i class="fas fa-search" title="'.getLabel($nom[$lng == $lang[0] ? 20 : 21]).'"></i>
        </span>
      </div>
    </div>';
        if ($lang[1] != '') {
            $out .= '
    <div class="thinboxsearchW">
      <select name="searchlng" class="form-control form-control-lg w-98 m-auto">
        <option value="">['.$lang[0].'] '.$web[177].' ['.$lang[1].']</option>
        <option value="'.$lang[0].'"'.Selected($lng == $lang[0]).'>['.$lang[0].'] '.$web[178].'</option>
        <option value="'.$lang[1].'"'.Selected($lng == $lang[1]).'>['.$lang[1].'] '.$web[178].'</option>
      </select>
	</div>';
        } else {
            $out .= '
    <input type="hidden" name="searchlng" value="'.$lang[0].'" />';
        }
        $out .= '
  </form>
</div>';
		$out .= displayQuickConfig($pos, false, '', '', '', 49);
		$titre = getLabel($nom[$lng == $lang[0] ? 20 : 21]);
		$titre = mb_substr(trim($titre), 0, 1, $charset) == '§' ? '' : DisplayDownArrow($dynabox[3], 'TS'.$nbox.$pos.$tconfig).$titre;
        return '
<div class="'.$pos.$tconfig.'TS">'.boxthin($pos, $titre, $out, ' TS'.$nbox.$pos.$tconfig, $dynabox[3]).'
</div>';
    } else {
       $out = '
<div class="wideboxsearch WS'.$pos.$tconfig.'">
  <form name="widesearch'.$nbox.'" 
      action="'.CHEMIN.($site['URLR'] == 'on' ? 
        $lng.'-'.$urlrw[12].'-2-'.$lng.'-'.$tconf.$z2 : 
        'search.php?lng='.$lng.'&amp;searchlng='.$lng.'&amp;tconfig='.$tconf.$z2).'" 
      method="post">
    <input type="hidden" name="searchin" value="" />
    <div class="input-group w-85 mx-auto mb-2">
      <input name="search" class="texte form-control" type="text" placeholder="'.$web[58].'" value="" />
      <div class="input-group-append c-pointer" onclick="javascript:document.widesearch'.$nbox.'.submit();">
        <span class="input-group-text">
          <i class="fas fa-search" title="'.getLabel($nom[$lng == $lang[0] ? 20 : 21]).'"></i>
        </span>
      </div>
    </div>
  </form>
</div>';
		$out .= displayQuickConfig($pos, false, '', '', '', 49);
		if (in_array($pos, array(BOX_TOP, BOX_BOTTOM))) {
			return $out;
		} else {
			return boxwide($pos, '', $out, ' WS'.$pos.$tconfig);
		}
    }
}

################################################################################

function fbox_search2($pos = NULL, $args = NULL) {
    global $lng, $lang, $members, $nom, $page, $site, $serviz, $userprefs, $drtuser, $web, $urlrw, $tconfig, $z2;
    static $nbox = 0;
    $nbox++;
    if (!isAccessGranted($members[23])) return FALSE;
    if ($serviz[24] != 'on') return FALSE;
    include CONFIGREP.'searchconfig.inc';
    $tconf = $confpage == 'ALL' ? $tconfig : $confpage;
    $okfr  = false;
	$dbcat = array();
 	if (!isset($rech_forum)) $rech_forum = 'rech_forum';
	if ($serviz[13] == 'on' && $rech_forum != '')
	{
		$dbcat = ReadDBFields(DBFORUMCAT);
	}
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
    $i = $lng == $lang[0] ? 0 : 1;
    $j = 10 * $i;
    $options = '';
	if (!isset($rech_article)) $rech_article = TYP_ART;
	if ($rech_article != '')
	{
		$options .= SearchOption(true,                isAccessGranted($members[1]),  TYP_ART,     $web[169]);
		$options .= SearchOption($serviz[29] == 'on', isAccessGranted($members[1]) && isAccessGranted($members[11]), TYP_REACT,   $web[187]);
	}
	if (!isset($rech_download)) $rech_download = TYP_DNLOAD;
	if ($rech_download != '')
		$options .= SearchOption($serviz[10] == 'on', isAccessGranted($members[7]),  TYP_DNLOAD,  getLabel($nom[1+$j]));
	if (!isset($rech_faq)) $rech_faq = TYP_FAQ;
	if ($rech_faq != '') 
		$options .= SearchOption($serviz[14] == 'on', isAccessGranted($members[4]),  TYP_FAQ,     getLabel($nom[24+$i]));
 	if (!isset($rech_forum)) $rech_forum = TYP_FORUM;
	if ($rech_forum != '') 
		$options .= SearchOption($serviz[13] == 'on' && $okfr, isAccessGranted($members[5]), TYP_FORUM, getLabel($nom[22+$i]));
 	if (!isset($rech_livreor)) $rech_livreor = TYP_GUESTBK;
	if ($rech_livreor != '') 
		$options .= SearchOption($serviz[12] == 'on', isAccessGranted($members[12]), TYP_GUESTBK, getLabel($nom[9+$j]));
 	if (!isset($rech_liens)) $rech_liens = TYP_LINKS;
	if ($rech_liens != '') 
		$options .= SearchOption($serviz[11] == 'on', isAccessGranted($members[3]),  TYP_LINKS,   getLabel($nom[3+$j]));
  	if (!isset($rech_news)) $rech_news = TYP_NEWS;
	if ($rech_news != '') 
		$options .= SearchOption($serviz[8]  == 'on', isAccessGranted($members[13]), TYP_NEWS,    getLabel($nom[7+$j]));
   	if (!isset($rech_blog)) $rech_blog = TYP_BLOG;
	if ($rech_blog != '') 
		$options .= SearchOption($serviz[53] == 'on', isAccessGranted($members[15]), TYP_BLOG,    getLabel($nom[42+$i]));
   	if (!isset($rech_photo)) $rech_photo = TYP_PHOTO;
	if ($rech_photo != '') 
		$options .= SearchOption($serviz[9]  == 'on', isAccessGranted($members[2]),  TYP_PHOTO,   getLabel($nom[2+$j]));
   	if (!isset($rech_agenda)) $rech_agenda = TYP_AGENDA;
	if ($rech_agenda != '') 
		$options .= SearchOption($serviz[47] == 'on', isAccessGranted($members[14]), TYP_AGENDA,  $web[488]);
    if (!isset($rech_archive)) $rech_archive = TYP_ARCH;
	if ($rech_archive != '')
		$options .= SearchOption(true,                true                         , TYP_ARCH,    $web[399]);

    $out = '
<form name="widesearch2'.$nbox.'" 
    action="'.CHEMIN.($site['URLR'] == 'on' ? 
      $lng.'-'.$urlrw[12].'-2-'.$lng.'-'.$tconf.$z2 : 
      'search.php?lng='.$lng.'&amp;searchlng='.$lng.'&amp;tconfig='.$tconf.$z2).'" 
    method="post">
  <div id="search2'.$nbox.'" class="'.(in_array($pos, array(BOX_LEFT, BOX_RIGHT)) ? 'thinboxsearch1 TS1' : 'wideboxsearch1 WS1').$pos.$tconfig.'">
    <input type="hidden" name="searchin" value="" />
    <div class="input-group mb-3 w-85 m-auto">
      <input name="search" class="texte form-control" type="text" size="18" placeholder="'.$web[58].'" value="" />
      <div class="input-group-append c-pointer" onclick="javascript:document.widesearch2'.$nbox.'.submit();">
        <span class="input-group-text">
          <i class="fas fa-search" title="'.getLabel($nom[$lng == $lang[0] ? 20 : 21]).'"></i>
        </span>
      </div>
    </div>
    <div id="adv1'.$nbox.'" class="adv1web">
      <a href="javascript:cache(\'adv1'.$nbox.'\'); montre(\'adv2'.$nbox.'\'); montre(\'search1'.$nbox.'\');">'.$web[590].'</a>
    </div>
    <div id="adv2'.$nbox.'" class="adv2'.(in_array($pos, array(BOX_LEFT, BOX_RIGHT)) ? '' : ' adv2W').'">&nbsp;</div>
    <div id="search1'.$nbox.'" class="'.(in_array($pos, array(BOX_LEFT, BOX_RIGHT)) 
      ? 'w-100 mt-4 thinboxsearch2'.(in_array($pos, array(BOX_LEFT, BOX_RIGHT)) 
        ? '' 
        : ' thinboxsearch2W') 
      : 'pt-2 wideboxsearch2'.(in_array($pos, array(BOX_LEFT, BOX_RIGHT)) 
        ? '' 
        : ' wideboxsearch2W')).'">
      <div class="'.(in_array($pos, array(BOX_LEFT, BOX_RIGHT)) ? 'mt-n4 thinboxsearch3W' : 'wideboxsearch3W').' pt-1">
	    <select name="searchin" class="form-control-lg w-60 m-auto">';
		if ($rech_article == TYP_ART && $rech_download == TYP_DNLOAD && $rech_faq == TYP_FAQ && $rech_forum == TYP_FORUM 
			&& $rech_livreor == TYP_GUESTBK && $rech_liens == TYP_LINKS && $rech_news == TYP_NEWS && $rech_blog == TYP_BLOG 
			&& $rech_photo == TYP_PHOTO && $rech_agenda == TYP_AGENDA && $rech_archive == TYP_ARCH)
				$out .= ' <option value="" selected="selected">'.$web[168].'</option>';
		$out .= $options.'
				</select>
			</div>';
    if ($lang[1] != '') {
        $out .= '
      <div class="searchlngW">
        <select name="searchlng" class="form-control-lg w-70 m-auto">
          <option value="">['.$lang[0].'] '.$web[177].' ['.$lang[1].']</option>
          <option value="'.$lang[0].'"'.Selected($lng == $lang[0]).'>['.$lang[0].'] '.$web[178].'</option>
          <option value="'.$lang[1].'"'.Selected($lng == $lang[1]).'>['.$lang[1].'] '.$web[178].'</option>
        </select>
	  </div>';
    } else {
        $out .= '
      <input type="hidden" name="searchlng" value="'.$lang[0].'" />';
    }
    $out.= '
      <i class="fas fa-chevron-up searchclose" title="'.$web[57].'" 
        onclick="cache(\'adv2'.$nbox.'\'); cache(\'search1'.$nbox.'\'); montre(\'adv1'.$nbox.'\');">
      </i>
    </div>
  </div>
</form>
';
	$out .= displayQuickConfig($pos, false, '', '', '', 49);
	if (in_array($pos, array(BOX_TOP, BOX_BOTTOM))) {
		return $out;
	} else {
		if (in_array($pos, array(BOX_LEFT, BOX_RIGHT))) {
			return '
<div class="'.$pos.$tconfig.'SCH">'.boxthin($pos, '', $out, ' SCH'.$pos.$tconfig).'</div>';
		} else {
			return boxwide($pos, '', $out, ' SCH'.$pos.$tconfig);
		}
	}
}

################################################################################

function fbox_empty($pos = NULL, $args = NULL) {
    static $nbox = 0;
    $nbox++;
    $sidebox = in_array($pos, array(BOX_LEFT, BOX_RIGHT, BOX_TOP, BOX_BOTTOM));
	$class   = 'FB'.$nbox.$pos;
	$out = '';
    $content = '
<style>
  .'.$class.' {
    background-color: transparent;
    border: 0;
    box-shadow: none;
    height: 0;
    margin: 0;
    padding: 0;
  } 
</style>
<div class="d-none">&nbsp;</div>';
	if ($sidebox) {
		$out .= boxthin($pos, '', $content, ' '.$class, 'off');
	} else {
		$out .= boxwide($pos, '', $content, ' '.$class);
	}
    return $out;
}

################################################################################

function fbox_freebox($pos = NULL, $args = NULL) {
    global $lng, $lang, $drtuser, $serviz, $site, $userprefs, $web, $page, $tconfig, $charset;
    global $type, $fileid, $status, $creadate, $moddate, $author, $email,
         $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
         $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
    static $nbox = 0;
    $nbox++;
    $sidebox = in_array($pos, array(BOX_LEFT, BOX_RIGHT, BOX_TOP, BOX_BOTTOM));
    $id = $args[0];
    ReadDoc($id);
    if (!isAccessGranted($fieldmod) || !isAuthorizedPublication($creadate)) return FALSE;
    if ($type != TYP_FREEBOX) return FALSE;
    if ($status != 'a') return FALSE;
    $fieldweb   = intval($fieldweb);
    $max_height = $fieldweb;
	$dynbox     = explode('|', $fielda2);
	$class      = ' FB'.$nbox.$pos.$fileid;
    if ($lng == $lang[0]) {
        $titre   = $fieldb1;
        $contenu = PathToImage($fieldc1);
    }
    else {
        $titre = $fieldb2;
        $contenu = PathToImage($fieldc2);
    }
	if ($pos == BOX_EDITO && mb_substr(trim($titre), 0, 2, $charset) == '§§') 
		$titre = sprintf(empty($userprefs[1]) ? $web[1] : $web[167], stripslashes($site[($lang[0] == $lng ? 0 : 11)]), $userprefs[1]);
    $titre = mb_substr(trim($titre), 0, 1, $charset) == '§' ? '' : ($pos == BOX_EDITO ? '' : DisplayDownArrow($dynbox[0], trim($class))).$titre;
    $out = '
<div class="'.$pos.$tconfig.'fb'.$id.'">';
    $content = '';
    if (!empty($contenu)) {
        $content .= '
<style>
.freebox'.$id.' {
  max-height: '.($max_height > 0 ? $max_height.'px;' : 'none;').'
  overflow: '.($max_height > 0 ? 'hidden;' : 'visible;').'
  background: transparent;
}
</style>';
        $content .= '
  <div id="box'.$id.'30" class="w-100 box freebox'.$id.'">'.$contenu.'</div>
  <div class="clearfix"></div>';
        if ($max_height > 0) $content .= '
    <p id="box'.$id.'10" class="float-right c-pointer pe-5">
      <a href="javascript:cache(\'box'.$id.'30\');montre(\'box'.$id.'20\');cache(\'box'.$id.'10\');" title="'.$web[296].'">
        '.$web[296].' <i class="far fa-plus-square"></i>
      </a>
    </p>';
        if ($max_height > 0) {
            $content .= '
  <div id="box'.$id.'20" class="w-100 box" style="display: none;">'.$contenu.'
    <p class="float-right c-pointer pe-5">
      <a href="#freebox'.$pos.$id.'" title="'.$web[57].'" onclick="cache(\'box'.$id.'20\');montre(\'box'.$id.'30\');montre(\'box'.$id.'10\');">
	    '.$web[57].' <i class="far fa-minus-square"></i>
      </a>
    </p>
  </div><div class="clearfix"></div>';
        }
    }
  $content .= displayQuickConfig($pos, $id, 'freebox&amp;form=2&amp;tri=ch&amp;id='.$id, '', '', 26);
	if ($sidebox) {
		$out .= boxthin($pos, $titre, $content, $class, $dynbox[0]);
	} else {
		$out .= boxwide($pos, $titre, $content, $class);
	}
	$out .= '
</div>';
    return $out;
}

################################################################################

function fbox_user($pos = NULL, $args = NULL) {
    global $lng, $lang, $members, $nom, $userprefs, $web, $endurl, $serviz, $cryptinstall, 
	       $dynabox, $site, $urlrw, $page, $tconfig, $charset, $tabcounter, $drtuser, $z2, $presform;
    static $nbox = 0;
    $nbox++;
    $out = '
    <div class="box text-end m-auto">';

    if (!empty($userprefs[1])) {
        $out .='
	  <form name="useraccount'.$nbox.'" action="'.CHEMIN.($site['URLR'] == 'on'? $lng.'-'.$urlrw[3].'-14-'.$tconfig.$z2 : 'compte.php?lng='.$lng.'&amp;tconfig='.$tconfig.$z2).'" method="post">
	    <div class="text-center userButtonW cmptButton">'.SubmitButton($web[212]).'</div>
	  </form>';
        if ($serviz[18] != 'on') $out .= '
	  <form name="usermsg'.$nbox.'" action="'.CHEMIN.($site['URLR'] == 'on'? $lng.'-'.$urlrw[13].'-3-3-'.$tconfig.$z2 : 'tinymsg.php?lng='.$lng.'&amp;action=3&amp;tconfig='.$tconfig.$z2).'" method="post">
	    <div class="text-center userButtonW msgButton">'.SubmitButton($web[318]).'</div>
	  </form>';
        $out .= '
	  <form name="useroff'.$nbox.'" action="'.CHEMIN.($site['URLR'] == 'on'? $lng.'-'.$urlrw[3].'-1-off-'.$tconfig.$z2 : 'connect.php?lng='.$lng.'&amp;connect=off&amp;tconfig='.$tconfig.$z2).'" method="post">
	    <div class="text-center userButtonW cntButton">'.SubmitButton($web[372]).'</div>
	  </form>';
    }
    else {
        if ($members[19] != '0' && $members[19] != '3') {
            $out .='
	  <form 
          name="usermemb'.$nbox.'" 
          action="'.CHEMIN.($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[3].'-3-on-new-'.$tconfig.$z2 
            : 'connect.php?lng='.$lng.'&amp;connect=on&amp;uuser=new&amp;tconfig='.$tconfig.$z2).'" 
          method="post">
	    <div class="text-center userButtonW">'.SubmitButton($web[160]).'</div>
	  </form>
	  <hr />';
        }
        $out.= '
<style>
.lblusr i { font-size: 1.7rem; width: 1.85rem; }
.lblusr input, .passwd input { border-radius: '.$presform[9].'; }
.passwd input.texte {
  height: 3.3rem;
  border: thin solid #ced4da;
}
.passwd button i {
  width: 1.85rem;
  height: 2.36rem;
  font-size: 1.6rem;
  padding-top: 4px;
}
.passwd .btn-outline-secondary, #uid-eye .input-group-text {
  background-color: #e9ecef;
  border-color: #ced4da;
  border-radius: '.$presform[9].';
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
}
</style>
	  <div class="text-center m-auto">
        <b>'.$web[373].' :</b>
        <form 
            name="userin'.$nbox.'" 
            action="'.CHEMIN.($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[3].'-0-'.$tconfig.$z2 
              : 'connect.php?lng='.$lng.'&amp;tconfig='.$tconfig.$z2).'" 
            method="post">
          <input type="hidden" name="connect" value="on"/>
          <input type="hidden" name="uuser" value="old"/>
          <div class="text-center w-100 m-auto" style="max-width: 300px;">
            <div class="lblusr input-group mb-3">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="far fa-user"></i></span>
              </div>
              <input class="form-control" type="text" name="pseudo" placeholder="'.$web[49].'" value=""/>
            </div>
            <div class="passwd input-group mb-3">
              <input 
                data-toggle="password"
                id="uuid'.$pos.$tconfig.'" 
                class="texte userIdentW form-control" 
                type="password" 
                name="uid" 
                placeholder="'.$web[300].'" 
                value=""
                data-message="" 
              />
            </div>
            ';
        $out .= '      
              '.SubmitButton($web[52]).'
          </div>
        </form>
	  </div>
	  <div class="text-center userLostW">
	    <a class="userLostA" href="'.CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[3].'-16-'.$tconfig.$z2 
          : 'lostpass.php?lng='.$lng.'&amp;tconfig='.$tconfig.$z2).'">'.$web[592].'</a>
	  </div><br />';
    }
    $out .= '
    </div>';

    $nbrmsg = array();
    if (FileDBExist(MSGREP.$userprefs[1].DBEXT)) {
        $dbmsg = ReadDBFields(MSGREP.$userprefs[1].DBEXT);
        if ($dbmsg[0][0] == $userprefs[7]) {
            for ($i = 2; $i < count($dbmsg); $i++) {
                if (isset($dbmsg[$i][3]) && $dbmsg[$i][3] == 'new') {
                    $nbrmsg[] = $dbmsg[$i][3];
                }
            }
        }
    }
    if (count($nbrmsg) != 0) {
        $act  = ($serviz[18] == 'on' ? '0' : '3');
        $out .= '
	<div class="userWave text-center">
      <a class="box" href="'.CHEMIN.($site['URLR'] == 'on'
        ? $lng.'-'.$urlrw[13].'-3-'.$act.'-'.$tconfig.$z2 
        : 'tinymsg.php?lng='.$lng.'&amp;action='.$act.'&amp;tconfig='.$tconfig.$z2).'">
        <i class="far fa-envelope blink display-4 text-success" title="'.$web[319].'"></i>
      </a>';
        if (FALSE === stristr($endurl, 'tinymsg.php')) {
            $out .= '
      <object type="audio/wave" data="'.CHEMIN.'inc/mail/mail.wav">
        <param name="src" value="'.CHEMIN.'inc/mail/mail.wav" />
        <param name="autoplay" value="true" />
        <param name="autoStart" value="1">
        <param name="controller" value="false">
        <param name="filename" value="'.CHEMIN.'inc/mail/mail.wav" />
        <param name="showcontrols" value="false" />
      </object>';
        }
        $out .= '
    </div>';
    }
    if ($serviz[158].$serviz[159] != 'onon') $out .= '
    <hr />';
    $file_users  = opendir(MSGREP);
    $nbr_membres = 0;
    while ($nomfichier = readdir($file_users)) {
        if ('.dtb' == $nomfichier) {
            @unlink(MSGREP.$nomfichier);
            continue;
        }
        if( substr($nomfichier,-3) == "dtb" ) {
            $nbr_membres++;
        }
    }
    closedir($file_users);
    if ($nbr_membres > 1) {
        $text_nbr_membres = $web[321];
    }
    else {
        $text_nbr_membres = $web[322];
    }
	if ($serviz[149] == 'on') {
		$out1 = $tabcounter[0].'&nbsp;';
		$out2 = $tabcounter[1].'&nbsp;';
		$out .= '
    <div class="userCount text-start">&nbsp;
	  '.$out1.($tabcounter[0] == 1 ? $web[15] : $web[16]).'<br />&nbsp;
      '.$out2.($tabcounter[1] == 1 ? $web[17] : $web[18]).'<br /><br />
	</div>';
	}
    $regusers    = ReadDBFields(DBIPSTATS);
    $usersonline = array();
	$myIP        = '';
    foreach ($regusers as $reg) {
        if (!empty($reg[2])) {
			if ($userprefs[1] == $reg[2]) $myIP = $reg[0];
            $usersonline[] = array($reg[2], $reg[0]);
        }
    }
    @usort($usersonline,'AsciiCompare');
	$uniqueusers   = array();
//	$uniqueusers[] = $usersonline[0];
	for ($i = 0; $i < count($usersonline); $i++) {
			$uniqueusers[] = $usersonline[$i];
	}

    if ($serviz[158] != 'on') {
        $accessok = isAccessGranted($serviz[179], $userprefs[1]);
        $out .= '
    <div class="userList box text-start">&nbsp;';
        if ($accessok && $userprefs[1] != '' && $userprefs[20] != 'on' && $userprefs[5] == 'on' && $serviz[86] == 'on') $out .= '
	  <a href="'.CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[15].'-9-'.$tconfig.$z2 
          : 'memberlist.php?lng='.$lng.'&amp;tconfig='.$tconfig.$z2).'" title="'.$web[664].'"><strong>';
        $out .= '<i class="fas fa-users"></i>&nbsp;'.$nbr_membres." ".$text_nbr_membres;
        if ($accessok && $userprefs[1] != '' && $userprefs[20] != 'on' && $serviz[86] == 'on') $out .= '</strong>
      </a>';
        $out .= '
	</div>';
    }
    $visits  = ReadDBFields(USERREP.'visitsofday.dtb');
    $today   = date('Ymd');
    $visits1 = array();
    foreach ($visits as $visit) if ($visit[1] == $today) $visits1[] = $visit;
    $found = false;
    foreach ($uniqueusers as $unuser) {
        foreach ($visits1 as $visit) if ($visit[0] == $unuser[0]) $found = true;
        if (!$found && !empty($unuser[0])) $visits1[] = array($unuser[0], $today);
        $found = false;
    }
    WriteDBFields(USERREP.'visitsofday.dtb', $visits1);
	$visits    = array();
	$nomvisits    = array();
	if ($userprefs[1] != '' && $userprefs[20] != 'on' && $serviz[111] == 'on') {
		$visitsday = SelectDBFieldsByField(ReadDBFields(USERREP.'visitsofday.dtb'), 1, $today);
		foreach ($visitsday as $visit) {
            if (is_file(MSGREP.$visit[0].'.dtb')) {
                $dbmsg = ReadDBFields(MSGREP.$visit[0].DBEXT);
              if (isset($dbmsg[1][6]))
				{
					if ($dbmsg[1][6] == 'on')
					{					
						$visits[] = $visit;
						$nomvisits[] = $visit[0];
					}
				}
                unset($dbmsg);
            }
		}
		$nomvisits = array_unique($nomvisits);
		$out .= '
	<div class="userVisits box text-start">&nbsp;';
		$nbvisits = count($nomvisits);
		if ($nbvisits > 0) $out .= '
	  <a class="c-pointer" title="'.$web[677].'" onmouseup="montre(\'visits'.$nbox.'\');">';
		$out .= '<i class="fas fa-user-check"></i>&nbsp;'.$web[677].$nbvisits;
		if ($nbvisits > 0) $out .= '
	  </a>';
		$out .= '
	</div>
<style>
.visits {
    margin-top: -'.min(200, ($nbvisits*18)).'px;
}
</style>
	<div id="visits'.$nbox.'" class="visits">
	  <div class="visits2">';
		foreach ($nomvisits as $nomvisit) {
			$db = ReadDBFields(MSGREP.$nomvisit.'.dtb');
            if (!empty($db) && $db[1][6] == 'on') {
                $out .= '<div class="connectedofday">';
                if ($page[23] != 'none') {
                    if (!empty($db[1][16]) || $db[1][9] != 'ava-00') {
                        if ($db[1][9] == 'ava-00' && !empty($db[1][16])) $out .= '
            <img src="'.$db[1][16].'" class="ava-img my-1" alt="" />';
                        else $out .= '
            <i class="'.$db[1][9].' ava-img my-1"></i>';
                    }
                    elseif ($db[1][9] == 'ava-00') {
                        $out .= '
            <i class="ava-00 ava-img my-1"></i>';
                    }
                }
                $out .= $nomvisit.'
                </div>';
            }
		}
		$out .= '
	  </div>
	  <div class="visitsClose float-right c-pointer" onmouseup="cache(\'visits'.$nbox.'\')">&#xe808;</div>
	  <div class="clearfix"></div>
	</div>';
	}
    if ($serviz[159] != 'on') {
        $out .= '
    <div class="box text-start connectedList"><b>'.$web[175].'</b></div>';
        if (empty($usersonline)) {
            $out .= '
    <div class="box text-center">'.$web[176].'</div>';
        }
        else {
            $out .= '
    <div class="box text-start connectedList">';
            for ($i = 0; $i < count($uniqueusers); $i++) {
				if (isset($uniqueusers[$i][1]) && $uniqueusers[$i][1] != '')
				{
					if (substr($serviz[85], -1) != '/') $serviz[85] = $serviz[85].'/';
					if (!isset($drtuser[42])) $drtuser[42] = '';
					$whois = (($serviz[31] == $userprefs[1] || $serviz[176] == $userprefs[1] || $drtuser[42] == 'admin') && $serviz[84] == 'on' 
					  ? ' (<a href="'.$serviz[85].$uniqueusers[$i][1].'" title="'.$uniqueusers[$i][1].'" target="_blank">'.substr($uniqueusers[$i][1], 0, 8).'...</a>)' : '');
					$db = ReadDBFields(MSGREP.$uniqueusers[$i][0].'.dtb');
					if ($db[1][6] == 'on') {
						if (!empty($db[1][16]) || $db[1][9] != 'ava-00') {
							if ($db[1][9] == 'ava-00' && !empty($db[1][16])) 
								$avatar = '<img class="img-ava mr-2" src="'.$db[1][16].'" alt="" />';
							else 
								$avatar = '<i class="'.$db[1][9].' ava-img mr-2"></i>';
						}
						elseif ($db[1][9] == 'ava-00') {
							$avatar = '<i class="ava-00 ava-img mr-2"></i>';
						}
						$avatar = $page[23] == 'none' ? ' ' : $avatar;
						if ($userprefs[1] != '' && $userprefs[1] != $uniqueusers[$i][0] && $db[1][24] == 'on') {
							$from = $userprefs[1];
							$out .= '
		  <div class="connectedUserW">
			<i class="fas fa-caret-right connectedImg" title="'.$web[321].'"></i>'.$avatar;
							if ($serviz[18] != 'on') $out .= '
			<a href="'.CHEMIN.($site['URLR'] == 'on' 
				? $lng.'-'.$urlrw[13].'-2-1-'.$uniqueusers[$i][0].'-'.$from.'-'.$tconfig.$z2 
				: 'tinymsg.php?lng='.$lng.'&amp;action=1&amp;to='.$uniqueusers[$i][0].'&amp;from='.$from.'&amp;tconfig='.$tconfig.$z2).'" 
				  title="'.$web[214]." ".$uniqueusers[$i][0].'">
			  <i class="far fa-envelope" title="'.$web[214]." ".$uniqueusers[$i][0].'"></i>
			</a>';
							$out .= '&nbsp;&nbsp;'.$uniqueusers[$i][0].$whois.'
		  </div>';
						}
						else {
							$out .= '
		  <div class="connectedUserW">
			<i class="fas fa-caret-right connectedImg" title="'.$web[321].'"></i>'.$avatar.$uniqueusers[$i][0].'
		  </div>';
						}
					}
				}
            }
            if ($userprefs[1] != '' && $serviz[84] == 'on') {
                $smyIP = substr($myIP, 0, 15).(mb_strlen($myIP) > 15 ? ' ...' : '');
                $out .= '
	  <div class="text-center userprefIP">IP: <a href="'.$serviz[85].$myIP.'" title="'.$myIP.'" target="_blank">'.$smyIP.'</a></div>';
            }
            $out .= '
    </div>';
        }
    }
    if (is_null($pos)) return $out;
	$out .= displayQuickConfig($pos);
	$titre = getLabel($nom[$lng == $lang[0] ? 34 : 35]);
	$titre = mb_substr(trim($titre), 0, 1, $charset) == '§' ? '' : DisplayDownArrow($dynabox[5], 'USR'.$nbox.$pos.$tconfig).$titre;
    return '
<div class="'.$pos.$tconfig.'USR">'.boxthin($pos, $titre, $out, ' USR'.$nbox.$pos.$tconfig, $dynabox[5]).'
</div>';
}

################################################################################

function fbox_webm($pos = NULL, $args = NULL) {
    global $lng, $lang, $drtuser, $nom, $serviz, $user, $userprefs, $web, $page, $site, $dynabox, $urlrw, 
           $tconfig, $charset, $meskin, $z2;
    static $nbox = 0;
    $nbox++;
    if (isset($user[1])) $em = BreakEMail($user[1]);
    $out = '
<div class="box webm">
  <div class="webmtblW">
    <div class="table-responsive-md">
    <table class="table-responsive-md table-borderless">';
    if ($serviz[21] == 'on') {
		$out .= '
      <tr class="webmlh">';
        if ($serviz[99] == 'on') {
            $out .= '
        <td class="p-3 text-center">
          <i class="fas fa-user-edit" title="'.$web[173].' '.stripslashes($site[($lang[0] == $lng ? 0 : 11)]).'"></i>
        </td>
        <td class="p-3 text-start">
          <a class="box" href="'.CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[2].'-'.$tconfig.$z2 : $site['CT'].'.php?lng='.$lng.'&amp;tconfig='.$tconfig.$z2).'" title="'.$web[464].'">&nbsp;'.$web[464].'</a>
        </td>';
        } else {
            $out .= '
        <td class="p-3 text-center">
          <i class="fas fa-user-edit" title="'.$web[173].' '.$user[0].'"></i>
        </td>';
            if ($user[3] == 'on') {
                $out .= '
        <td class="p-3 text-start">
          <a class="box" href="'.CHEMIN.($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[14].'-'.TYP_MAIL.$z2 
              : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_MAIL.$z2).'" title="'.$web[173].' '.$user[0].'">
            &nbsp;'.$web[11].'
          </a>
        </td>';
            } else {
				$org  = 'webm';
                $out .= '
        <td class="p-3 text-start">
          <a data-target="#contactmodal" data-toggle="modal" class="box" style="cursor: pointer;" title="'.$web[173].' '.$user[0].'">&nbsp;'.$web[11].'</a>
        </td>
        <style> .modal-header { padding: 15px; } </style>
        <div class="modal fade" id="contactmodal" role="dialog">
          <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header">
                <h2>'.$web[173].' '.$user[0].'</h2>
                <button class="close" data-dismiss="modal" type="button"><i class="fas fa-times"></i></button>
              </div>
              <div class="modal-body">
                <p class="text-center popupMail">'.$web[644].'</p>
                <p class="text-center popupMail">
                  <a href="javascript:showEmails'.$org.'(\''.$em[0].'\',\''.$em[1].'\',\''.$em[2].'\');" 
                      title="'.addslashes($user[0]).'" class="text-center popupMail">
                    '.addslashes($user[0]).'
                  </a>
                </p>
                <p class="text-center popupMail"><b><span id="lblemail'.$org.'">(--------------)</span></b></p>
              </div>
            </div>
          </div>
        </div>
        <script>
          function showEmails'.$org.'(em1, em2, em3) {
            var emailFinal = "<a href=\'mailto:" + em1 + "@" + em2 + "." + em3 + "\'>" + em1 + "@" + em2 + "." + em3 + "</a>";
            document.getElementById("lblemail'.$org.'").innerHTML = emailFinal;
          }
        </script>';
            }
        }
		$out .= '
	  </tr>';
    }
    if ($serviz[62] == 'on') {
        $out .= '
      <tr class="webmlh">
        <td class="p-3 text-center">
          <i class="far fa-grin-hearts" title="'.$web[124].'"></i>
        </td>
        <td class="p-3 text-start">
          <a class="box" href="'.CHEMIN.($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[14].'-'.TYP_RECO.$z2 
              : $site['PG'].'.php?lng='.$lng.'&amp;typ='.TYP_RECO.$z2).'" title="'.$web[124].'">
            &nbsp;'.$web[120].'
          </a>
        </td>
	  </tr>';
    }
    if (is_dir(CHEMIN.'mobile')) {
        $out .= '
      <tr class="webmlh">
        <td class="p-3 text-center">
          <i class="fas fa-mobile-alt" title="'.$web[295].'"></i>
        </td>
        <td class="p-3 text-start">
          <a class="box" href="'.CHEMIN.'mobile/?lng='.$lng.'"" target="_blank">&nbsp;'.$web[295].'</a>
        </td>
	  </tr>';
    }
    if (!empty($serviz[177])) {
        $out .= '
      <tr class="webmlh">
        <td class="p-3 text-center">
          <i class="fas fa-file-contract" title="'.$web[301].'"></i>
        </td>
        <td class="p-3 text-start">
          <a class="box" href="'.CHEMIN.($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[20].'-102-'.$serviz[177].'-'.$serviz[178] 
              : 'articles.php?lng='.$lng.'&pg='.$serviz[177].'&tconfig='.$serviz[178]).$z2.'" title="'.$web[301].'">
            &nbsp;'.$web[301].'
          </a>
        </td>
      </tr>';
    }
    $out .= '
    </table>
    </div>
  </div>
</div>';
    $out .= displayQuickConfig($pos);
	$titre = getLabel($nom[$lng == $lang[0] ? 40 : 41]);
	$titre = mb_substr(trim($titre), 0, 1, $charset) == '§' ? '' : DisplayDownArrow($dynabox[8], 'WEB'.$nbox.$pos.$tconfig).$titre;
    return '
<div class="'.$pos.$tconfig.'WEB">'.boxthin($pos, $titre, $out, ' WEB'.$nbox.$pos.$tconfig, $dynabox[8]).'</div>';
}

################################################################################

function fbox_cal($pos = NULL, $args = NULL) {
    global $lng, $lang, $nom, $drtuser, $page, $serviz, $userprefs, $web, $dynabox, $site, $urlrw, 
        $tconfig, $charset, $members;
    static $nbox = 0;
    $nbox++;
    if (!isAccessGranted($members[14])) return FALSE;
    if ($serviz[47] == 'on') {
		$titre = getLabel($nom[$lng == $lang[0] ? 32 : 33]);
		$titre = mb_substr(trim($titre), 0, 1, $charset) == '§' ? '' : DisplayDownArrow($dynabox[4], 'CAL'.$nbox.$pos.$tconfig).$titre;
        $out = '
<script>
function getElement(aID) {
    return (document.getElementById) ? document.getElementById(aID) : document.all[aID];
}
function getIFrameDocument(aID){ 
    var rv = null; 
    var frame=getElement(aID);
    if (frame.contentDocument)
      rv = frame.contentDocument;
    else
      rv = document.frames[aID].document;
    return rv;
  }
    
function adjustMyFrameHeight'.$nbox.$pos.$tconfig.'() {
	var frame = getElement("calbox'.$nbox.$pos.$tconfig.'");
	var frameDoc = getIFrameDocument("calbox'.$nbox.$pos.$tconfig.'");
	frame.height = frameDoc.body.offsetHeight + 2;
}
</script>
<noscript>';
		$hcal = '340';
		$out .= '
</noscript>
<div class="text-center bg-inherit">
  <iframe 
    class="cal" 
    id="calbox'.$nbox.$pos.$tconfig.'" 
    src="'.CHEMIN.'inc/'.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[17].'-'.$pos.'-'.$tconfig.'-'.$nbox 
      : 'calendar.php?lng='.$lng.'&amp;pos='.$pos.'&amp;tconfig='.$tconfig.'&amp;nbox='.$nbox).'" 
    height="'.$hcal.'">
  </iframe>
</div>
';
        $out   .= displayQuickConfig($pos, false, 'agenda', '', '', 37);
        return '
<div class="'.$pos.$tconfig.'CAL'.$nbox.'">'.boxthin($pos, $titre, $out, ' CAL'.$nbox.$pos.$tconfig, $dynabox[4]).'</div>';
    } else {
        return FALSE;
    }
}

################################################################################

function fbox_newslet($pos = NULL, $args = NULL) {
    global $lng, $lang, $drtuser, $members, $nom, $serviz, $userprefs, $web, $page, $cryptinstall, $dynabox, $site, $urlrw,
           $tconfig, $charset, $z2, $rgpd;
    static $nbox = 0;
    $nbox++;
    if (!isAccessGranted($members[18], $userprefs[1])) {
        return FALSE;
    } else {
		$titre = getLabel($nom[$lng == $lang[0] ? 38 : 39]);
		$titre = mb_substr(trim($titre), 0, 1, $charset) == '§' ? '' : DisplayDownArrow($dynabox[6], 'NWL'.$nbox.$pos.$tconfig).$titre;
		// suppression des inscriptions en attente hors délai
		$tmp = ReadDBFields(NEWSLET_WAIT);
		for ($i = 0; $i < count($tmp); $i++) {
			if ((date('Ymd') - $serviz[110]) > substr($tmp[$i][3], 0, 8)) {
				unset($tmp[$i]);
			}
		}
		WriteDBFields(NEWSLET_WAIT, $tmp);
        $nabon = CountDBFields(NEWSLET_LIST);
        $mot   = $nabon < 2 ? $web[257] : $web[258];
        $txt1  = !empty($userprefs[1]) ? $userprefs[1] : $web[259];
        $txt2  = !empty($userprefs[2]) ? $userprefs[2] : $web[260];
        $txt3  = ($nabon == 0) ? $mot.'<br />'.$web[176] : $nabon.' '.$mot;
        $txt3 = $serviz[63] == 'on' ? '' : '
<div class="nwl_nabon text-center">'.$txt3.'</div>';
        if (empty($userprefs[1])) { // non membre
            $out = '
<form 
  class="nwlSubscribe" 
  name="subscribe'.$nbox.'" 
  action="'.CHEMIN.($site['URLR'] == 'on' 
    ? $lng.'-'.$urlrw[18].$z2 
    : $site['NL'].'.php?lng='.$lng.$z2).'" 
  method="post">
  <input type="hidden" name="nlsecure" value="on" />
  <div class="box">
    <div class="text-center">'.$web[247].'<span class="d-none"><input type="text" name="code_pseudo" value="" /></span></div>
    <div class="input-group mb-3 w-100 mx-auto" style="max-width: 300px;">
      <div class="input-group-prepend">
        <span class="input-group-text"><i class="far fa-user"></i></span>
      </div>
      <input class="texte form-control" type="text" name="nlpseudo" value="'.$txt1.'" onfocus="this.value=\'\'" />
    </div>
    <div class="input-group mb-3 w-100 mx-auto" style="max-width: 300px;">
      <div class="input-group-prepend">
        <span class="input-group-text"><i class="fas fa-at"></i></i></span>
      </div>
      <input class="texte form-control" type="text" name="nlmail" value="'.$txt2.'" onfocus="this.value=\'\'" />
    </div>
    <div class="nwlActionW">
      <label for="action1">'.$web[237].'</label> <input type="radio" id="action1" name="action" value="sub" checked="checked" />
    </div>
    <div class="nwlActionW">
      <label for="action2">'.$web[236].'</label> <input type="radio" id="action2" name="action" value="unsub" />
    </div>';
            if (!empty($serviz[177]) && !OK_RGPD_date($userprefs[1])) $out .= '
    <br/>
    <fieldset class="w-100 mx-auto text-start" style="max-width: 300px;">
      <p>'.$web[693].'</p>
      <div><label for="rgpd">'.$web[415].':&nbsp;&nbsp;&nbsp;</label><input type="checkbox" id="rgpd" name="rgpd"'.Checked($rgpd).' /></div>
    </fieldset>';
            if ($serviz[100] != '0' && $serviz[93] == 'on' && ($userprefs[1] == '' || $serviz[98] == '')) {
				$lbl = $serviz[100] == '3' ? $web[642] : '';
                $out .= '
	<div class="text-center boxCryptW">
      '.dsp_crypt(0, 1, 3, 1, $lbl, $serviz[115] == 'on' ? $web[643] : '').'
	  <div class="text-center m-auto">
	    '.($serviz[100] == '3' ? '' : $web[530]).'<input class="texte inputcrypt m-auto form-control" type="text" name="crypt" />
	  </div>
	</div>';
            }
            $out .= '
    <div class="text-center">'.SubmitButton($web[52]).'</div>'.$txt3.'
  </div>
</form>';
        } else {
            $abon = CheckDB2Fields(NEWSLET_LIST, $userprefs[1], 0, $userprefs[2], 1);
            if ($abon) { // abonné
                $out = '
<form 
  class="nwlUnsubscribe" 
  name="unsubscribe'.$nbox.'" 
  action="'.CHEMIN.($site['URLR'] == 'on' 
    ? $lng.'-'.$urlrw[18].$z2 
    : $site['NL'].'.php?lng='.$lng.$z2).'" 
  method="post">
  <input type="hidden" name="nlsecure" value="on" />
  <input type="hidden" name="action" value="unsub" />
  <input type="hidden" name="nlpseudo" value="'.$userprefs[1].'" />
  <input type="hidden" name="nlmail" value="'.$userprefs[2].'" />
  <div class="box">
    <div class="text-center">'.$web[500].'<span class="d-none"><input type="text" name="code_pseudo" value="" /></span></div>';
                if ($serviz[100] != '0' && $serviz[93] == 'on' && $serviz[98] == '') {
					$lbl = $serviz[100] == '3' ? $web[642] : '';
                    $out .= '
	<div class="text-center boxCryptW">
	  '.dsp_crypt(0, 1, 3, 1, $lbl, $serviz[115] == 'on' ? $web[643] : '').'
	  <div class="text-center m-auto">
	    '.($serviz[100] == '3' ? '' : $web[530]).'<input class="texte inputcrypt m-auto form-control" type="text" name="crypt" />
	  </div>
	</div>';
                }
                $out .= '
    <div class="text-center">'.SubmitButton($web[236]).'</div>'.$txt3.'
  </div>
</form>';
            } else {
                $tmp = CheckDB2Fields(NEWSLET_WAIT, $userprefs[1], 0, $userprefs[2], 1);
                if ($tmp) { // abonnement en attente de confirmation
                    $out = '
<div class="text-center">'.$web[501].'</div>'.$txt3;
                } else { // non abonné à la newsletter
                    $out = '
<form class="nwlSubscribe" name="subscribe'.$nbox.'" 
    action="'.CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[18] : $site['NL'].'.php?lng='.$lng).$z2.'" method="post">
  <input type="hidden" name="nlsecure" value="on" />
  <input type="hidden" name="action" value="sub" />
  <div class="box">
    <div class="text-center">'.$web[247].'<span class="d-none"><input type="text" name="code_pseudo" value="" /></div>
    <div class="input-group mb-3 w-100 mx-auto" style="max-width: 300px;">
      <div class="input-group-prepend">
        <span class="input-group-text"><i class="far fa-user"></i></span>
      </div>
      <input class="texte nwlIdentW form-control" type="text" name="nlpseudo" value="'.$txt1.'" onfocus="this.value=\'\'" />
    </div>
    <div class="input-group mb-3 w-100 mx-auto" style="max-width: 300px;">
      <div class="input-group-prepend">
        <span class="input-group-text"><i class="fas fa-at"></i></i></span>
      </div>
      <input class="texte nwlIdentW form-control" type="text" name="nlmail" value="'.$txt2.'" onfocus="this.value=\'\'" />
    </div>';
                if (!empty($serviz[177]) && !OK_RGPD_date($userprefs[1])) $out .= '
    <br/>
    <fieldset class="w-100 mx-auto text-start" style="max-width: 300px;">
      <p>'.$web[693].'</p>
      <div><label for="rgpd">'.$web[415].':&nbsp;&nbsp;&nbsp;</label><input type="checkbox" id="rgpd" name="rgpd"'.Checked($rgpd).' /></div>
    </fieldset>';
                    if ($serviz[100] != '0' && $serviz[93] == 'on' && $serviz[98] == '') {
						$lbl = $serviz[100] == '3' ? $web[642] : '';
                        $out .= '
	<div class="text-center boxCryptW">
	  '.dsp_crypt(0, 1, 3, 1, $lbl, $serviz[115] == 'on' ? $web[643] : '').'
	  <div class="text-center m-auto">
	    '.($serviz[100] == '3' ? '' : $web[530]).'<input class="texte inputcrypt m-auto form-control" type="text" name="crypt" />
	  </div>
	</div>';
                    }
                    $out .= '
    <div class="text-center">'.SubmitButton($web[237]).'</div>'.$txt3.'
  </div>
</form>';
                }
            }
        }
        $out   .= displayQuickConfig($pos, false, '', '', '', 33);
        return '
<div class="'.$pos.$tconfig.'NWL">'.boxthin($pos, $titre, $out, ' NWL'.$nbox.$pos.$tconfig, $dynabox[6]).'</div>';
    }
}

################################################################################

function fbox_rss($pos = NULL, $args = NULL) {
    global $lng, $lang, $drtuser, $nom, $serviz, $userprefs, $web, $page, $dynabox, $site, 
         $urlrw, $tconfig, $charset, $z2;
    global $type, $fileid, $status, $creadate, $moddate, $author, $email,
         $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
         $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
    static $nbox = 0;
    $nbox++;
    $dbrss = ReadDBFields(DBRSS);
    sort($dbrss);
    $out = '';
    $i = $lng == $lang[0] ? 0 : 1;
	$titre = getLabel($nom[36 + $i]);
	$titre = mb_substr(trim($titre), 0, 1, $charset) == '§' ? '' : DisplayDownArrow($dynabox[7], 'RSS'.$nbox.$pos.$tconfig).$titre;
    for ($j = 0; $j < count($dbrss); $j++) {
        Readdoc($dbrss[$j][4]);
        $tconf = empty($fieldweb) ? 0 : ($fieldweb == 'ALL' ? $tconfig : $fieldweb);
        $out .= '
<div class="text-center itemRssBoxW">
  <a class="box" href="'.CHEMIN.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[19].'-2-'.$dbrss[$j][4].'-'.$tconf.$z2 
      : 'newsrss.php?lng='.$lng.'&amp;id='.$dbrss[$j][4].'&amp;tconfig='.$tconf.$z2).'">
    '.$dbrss[$j][$i].'
  </a>
</div>';
    }
    $out   .= displayQuickConfig($pos, false, 'rss', '', '', 34);
    return '
<div class="'.$pos.$tconfig.'RSS">'.boxthin($pos, $titre, $out, ' RSS'.$nbox.$pos.$tconfig, $dynabox[7]).'</div>';
}

################################################################################

function fbox_socnet($pos = NULL, $args = NULL) {
    global $lng, $lang, $drtuser, $serviz, $userprefs, $web, $page, $nom, $site, $urlrw, $tconfig, $charset, 
		$status, $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $dynabox, $z2;
    static $nbox = 0;
    $nbox++;

    $dbnet = ReadDBFields(DBSOCNET);
    $nbnet = count($dbnet);
    for ($j = 0; $j < $nbnet; $j++) {
        $dbnet[$j][0] = $dbnet[$j][2];
        if (substr($dbnet[$j][0], 0, 4) == '<!--') 
            $dbnet[$j][0] = str_replace('<!--', '', $dbnet[$j][0]);
        else
            $dbnet[$j][0] = $nbnet;
    }
	@sort($dbnet);
	$soc = array();
	if (file_exists(SOCIALDATA))
		$soc = ReadDBFields(SOCIALDATA);

    $out = '
<div class="text-start iconSocnetW">';
    for ($j = 0; $j < count($dbnet); $j++) {
		ReadDoc($dbnet[$j][4]);
		$verifinarray = 0;
		if (strpos($fieldc2, ':') > 0 )
		{
			$socexplode = explode(':', $fieldc2);
			$nomsocrech0 = $socexplode[0];
		}
		else
		{
			$nomsocrech0 = $fieldc2;

		}	
		for ($isoc = 0; $isoc < count($soc); $isoc++)
		{
			if ($nomsocrech0 == $soc[$isoc][0])
			{
				$verifinarray = 1;
				break;
			}
		}

		if ($status == 'a' && trim($fieldd1) != '') {
            $cr = '#000000';
            $bg = '#E9ECEF';
            $rd = ($fielda2 == 'on' ? 'circle' : '6px');
            $sz = $fieldb2;
            $fielda2 = $fieldb2 = '';
            $out .= '
  <style> .soc a:hover { color: '.$cr.'; background-color: '.$bg.'; } </style>
';
            if ($fieldc2 == 'rss' && ($serviz[16] == 'on' || $serviz[54] == 'on' || $serviz[60] == 'on' || $serviz[103] == 'on')) $out .= '
  <div class="soc d-inline-block" data-size="'.$sz.'" data-iconcolor="'.$cr.'" data-buttoncolor="'.$bg.'" data-spacing="10px" data-radius="'.$rd.'">
    <a class="soc-rss" href="'.CHEMIN.($site['URLR'] == 'on'? $lng.'-'.$urlrw[19].$z2 : 'rss.php?lng='.$lng.$z2).'" title="'.$web[617].'" target="'.$fieldc1.'"></a>
  </div>';
          if ($fieldd2 != '' && strpos($fieldd2, '.') > 0) $out .= '
  <div class="d-inline-block align-top">
    <a href="'.$fieldd1.'" title="'.$fieldb1.'" target="'.$fieldc1.'">
      <img src="'.CHEMIN.'img/'.$fieldd2.'" alt="'.(strip_tags($fieldb1)).'" style="width:'.$sz.';" />
    </a>
  </div>';
         if ($fieldc2 != '' && $fieldc2 != 'rss' && $verifinarray == 1)
			{
				file_put_contents($nomficlog, 'ligne 2261 verifinarray: '.$verifinarray.' nomsocrech0: '.$nomsocrech0."\n", FILE_APPEND | LOCK_EX);
				$out .= '
		<div class="soc d-inline-block" data-size="'.$sz.'" data-iconcolor="'.$cr.'" data-buttoncolor="'.$bg.'" data-spacing="10px" data-radius="'.$rd.'">
			<a href="'.$fieldd1.'" title="'.(strip_tags($fieldb1)).'" target="'.$fieldc1.'">
			  <i class="'.$soc[0][2].'" /></i>
			</a>
		</div>';
			}
		}
    }
    $out .= '
</div>    
<div class="clearfix"></div>';
    $out  .= displayQuickConfig($pos, false, 'socnet', '', '', 50);
	$titre = getLabel($nom[$lng == $lang[0] ? 48 : 49]);
	if (in_array($pos, array(BOX_TOP, BOX_BOTTOM))) {
		return '<div class="boxnet NET'.$pos.$tconfig.'">'.$out.'</div>';
	} else {
		if (in_array($pos, array(BOX_LEFT, BOX_RIGHT))) {
            $titre = mb_substr(trim($titre), 0, 1, $charset) == '§' ? '' : DisplayDownArrow($dynabox[9], 'NET'.$nbox.$pos.$tconfig).$titre;
			return '
<div class="'.$pos.$tconfig.'NET">'.boxthin($pos, $titre, $out, ' NET'.$nbox.$pos.$tconfig, $dynabox[9]).'</div>';
		} else {
            $titre = mb_substr(trim($titre), 0, 1, $charset) == '§' ? '' : $titre;
			return boxwide($pos, $titre, $out, ' NET'.$pos.$tconfig);
		}
	}
}

################################################################################

function fbox_lastdoc($pos = NULL, $args = NULL) {
    global $lng, $lang, $nom, $page, $serviz, $site, $userprefs, $web, $urlrw, $tconfig, 
	       $charset, $drtuser, $dynabox, $members, $z2;
    global $type, $fileid, $status, $creadate, $moddate, $author, $email,
         $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
         $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
    if (!isAccessGranted($members[22])) return FALSE;
	if ($serviz[116] != 'on') return false;
	if ($serviz[117].$serviz[118] == '') return false;
    static $nbox = 0;
    $nbox++;
    $out   = '';
    $ndays = 0;
    if ($serviz[118] == 'on') $ndays = !empty($serviz[119]) && is_numeric($serviz[119]) ? $serviz[119] : '15';
	if ($serviz[118] == 'on' && $ndays > 0) $dat = date('YmdHi', strtotime($ndays.' days ago'));
    if ($serviz[117] == 'on') {
        if ($userprefs[1] != '') $dat = !empty($userprefs[14]) ? $userprefs[14] : date('YmdHi');
        else $dat = date('YmdHi');
    }
	$db    = $dba = $dbb = array();
	$docid = SelectDBFieldsByStatus(ReadDBFields(DOCID), 'a');
	$dbdat = ReadDBFields(DOCDATES);
	foreach ($dbdat as $item) if ($item[2] >= $dat) $db[] = array($item[2], $item[0]);
	rsort($db);
	$dbag = $dbag2 = $dbar = $dbra = $dbom = $dbbl = $dbrb = $dbdn = $dbfa = $dbfr = $dbli = $dbgb = $dbne = $dbph = array();
	foreach ($db as $item) {
		$typ    = SelectDBFieldsByField($docid, 1, $item[1]);
		$idat   = $serviz[169] == 'on' ? '' : substr(FormatDate($item[0]), 0, 10).' :';
		$typdoc = empty($typ[0][0]) ? '' : $typ[0][0];
		switch ($typdoc) {
		case 'ag' : // agenda
            $zmbr = $userprefs[1] == '' && !isAccessGranted($members[14]) ? true : false;
			if ($serviz[121] == 'on' && !$zmbr) {
				ReadDoc($item[1]);
                $valid_date = (int)substr($fielda1, 0, 8) >= (int)date('Ymd');
				if (isAuthorizedPublication($creadate) && isAccessGranted($fieldmod) && $status == 'a' && $valid_date) {
					$url0 = 'agenda.php?lng='.$lng.'&amp;idpg='.$fileid.'-'.'&amp;pg='.$item[0].'&amp;agv=1&amp;tconfig='.$tconfig.$z2;
					$url1 = $lng.'-'.$urlrw[17].'-22-'.$fileid.'--'.$fileid.'-1-'.$tconfig.$z2;
					$link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.FormatDate(substr($fielda1, 0, 8)).' - '.CutLongWord(strip_tags($lng == $lang[0] ? $fieldc1 : $fieldc2), 36).'...</a>';
					$dbag[]  = array($nom[$lng == $lang[0] ? 32 : 33], $link);
                    $dbag2[] = $fielda1;
				}
			}
			break;
		case 'ar' : // articles
   		case 'ra' : // réactions articles
            $zmbr = $userprefs[1] == '' && !isAccessGranted($members[1]) ? true : false;
            $id = false;
            if ($typdoc == 'ra' && $serviz[29] != '2' && $serviz[166] == 'on' && !$zmbr) {
                $dbw = SelectDBFieldsByField(ReadDBFields(DBREACT), 0, $item[1]);
                $id  = $dbw[0][1];
            }
            if ($typdoc == 'ar' && $serviz[122] == 'on' && !$zmbr) {
                $id = $item[1];
            }
            if ($id) {
                ReadDoc($id);
                $afm   = explode('|', $fieldmail);
                $tconf = $typdoc == 'ar' ? ($afm[0] == 'ALL' ? $tconfig : $afm[0]) : $fieldmail;
                $tconf = empty($tconf) ? $tconfig : $tconf;
                if (isAuthorizedPublication($creadate) && isAccessGranted($fieldmod) && $status == 'a') {
                    $fa1  = $fielda1;
                    $fa2  = $fielda2;
                    $fb1  = $fieldb1;
                    $fb2  = $fieldb2;
                    $dbwo = ReadDBFields(DBOPTMENU);
                    $url0 = 'articles.php?lng='.$lng.'&pg='.$id.'&tconfig='.$tconf.$z2;
                    $url1 = $lng.'-'.$urlrw[20].'-102-'.$id.'-'.$tconf.$z2;
                    foreach ($dbwo as $item1) {
                        ReadDoc($item1[4]);
                        if (!isAuthorizedPublication($creadate) || !isAccessGranted($fieldmod) || $status != 'a') continue;
                        $urls = explode('||', $lng == $lang[0] ? $fieldd1 : $fieldd2);
                        $pg   = substr($urls[0], strpos($urls[0], 'pg=') + 3);
                        $pos1 = strpos($pg, '&');
                        $pg   = $pos1 === false ? $pg : substr($pg, 0, $pos1);
                        if ($pg == $id) {
                            $fa1  = $fielda1;
                            $fa2  = $fielda2;
                            $fb1  = $fieldb1;
                            $fb2  = $fieldb2;
                            $tcnf = empty($fieldmail) ? $tconf : $fieldmail;
                            $url0 = 'articles.php?lng='.$lng.'&pg='.$pg.'&mnuid='.$item1[5].'&tconfig='.$tcnf.$z2;
                            $url1 = $lng.'-'.$urlrw[20].'-103-'.$pg.'-'.$item1[5].'-'.$tcnf.$z2;
                            break;
                        }
                    }
                    $link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.strip_tags($lng == $lang[0] ? $fa1 : $fa2).' - '.strip_tags($lng == $lang[0] ? $fb1 : $fb2).'</a>';
                    if ($typdoc == 'ar') {
                        $dbar[] = array($web[169], $link); 
                        $dba[]  = $id;
                    }
                    if ($typdoc == 'ra') {
                        $dbra[] = array($web[187], $link); 
                        $dba[]  = $id;
                    }
                }
            }
            break;
		case 'om' : // options de menu indépendantes des services
			if ($serviz[154] == 'on') {
				ReadDoc($item[1]);
                $targets = array('agenda', 'articles', 'blog', 'download', 'faq', 'links', 'guestbk', 'thread', 'news', 'photorama2');
                $isValid = true;
                foreach($targets as $target) if (strpos($fieldd1, $target.'.php') === true) $isValid = false;
				if (isAuthorizedPublication($creadate) && isAccessGranted($fieldmod) && $status == 'a' && $isValid) {
                    $fa1  = $fielda1;
                    $fa2  = $fielda2;
                    $fb1  = $fieldb1;
                    $fb2  = $fieldb2;
                    $urls = explode('||', $lng == $lang[0] ? $fieldd1 : $fieldd2);
                    if (empty($urls[1])) $urls[1] = $urls[0];
					$link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $urls[1].$z2 : $urls[0].$z2).'" target="_self" title="">'.strip_tags($lng == $lang[0] ? $fa1 : $fa2).' - '.strip_tags($lng == $lang[0] ? $fb1 : $fb2).'</a>';
					$dbom[] = array($web[448], $link);
				}
			}
			break;
		case 'bl' : // blog et commentaires
		case 'rb' : 
            $zmbr = $userprefs[1] == '' && !isAccessGranted($members[15]) ? true : false;
            $id = false;
            if ($typdoc == 'rb' && $serviz[57] != '2' && $serviz[167] == 'on' && !$zmbr) {
                $dbw = SelectDBFieldsByField(ReadDBFields(DBREBLOG), 0, $item[1]);
                $id  = $dbw[0][1];
            }
            if ($typdoc == 'bl' && $serviz[123] == 'on' && !$zmbr) {
                $id = $item[1];
            }
            if ($id) {
                ReadDoc($id);
				if (isAuthorizedPublication($creadate) && isAccessGranted($fieldmod) && $status == 'a') {
                    $url0 = $site['BL'].'.php?lng='.$lng.'&amp;sel=pg&amp;tconfig='.$tconfig.'&amp;pg='.$fileid.$z2;
                    $url1 = $lng.'-'.$urlrw[1].'-2-pg-'.$tconfig.'-'.$fileid.$z2;
                    $link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.strip_tags($lng == $lang[0] ? $fielda1 : $fielda2).' - '.strip_tags($lng == $lang[0] ? $fieldb1 : $fieldb2).'</a>';
                    if ($typdoc == 'bl') {
                        $dbbl[] = array($nom[$lng == $lang[0] ? 42 : 43], $link); 
                        $dbb[]  = $id;
                    }
                    if ($typdoc == 'rb') {
                        $dbrb[] = array($web[407].' '.mb_strtolower($nom[$lng == $lang[0] ? 42 : 43]), $link);
                        $dbb[]  = $id;
                    }
				}
			}
			break;
		case 'dn' : // téléchargements
            $zmbr = $userprefs[1] == '' && !isAccessGranted($members[7]) ? true : false;
			if ($serviz[124] == 'on' && !$zmbr) {
				ReadDoc($item[1]);
				if (isAuthorizedPublication($creadate) && isAccessGranted($fieldmod) && $status == 'a') {
					$url0 = $site['DN'].'.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;tconfig='.$tconfig.$z2;
					$url1 = $lng.'-'.$urlrw[11].'-4-'.$fileid.'-'.$tconfig.$z2;
					$link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.strip_tags($lng == $lang[0] ? $fielda1 : $fielda2).' - '.strip_tags($lng == $lang[0] ? $fieldb1 : $fieldb2).'</a>';
					$dbdn[] = array($nom[$lng == $lang[0] ? 1 : 11], $link);
				}
			}
			break;
		case 'fa' : // faq
            $zmbr = $userprefs[1] == '' && !isAccessGranted($members[4]) ? true : false;
			if ($serviz[125] == 'on' && !$zmbr) {
				ReadDoc($item[1]);
				if (isAuthorizedPublication($creadate) && isAccessGranted($fieldmod) && $status == 'a') {
					$url0 = 'faq.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;tconfig='.$tconfig.$z2;
					$url1 = $lng.'-'.$urlrw[4].'-6-'.$fileid.'-'.$tconfig.$z2;
					$link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.strip_tags($lng == $lang[0] ? $fielda1 : $fielda2).' - '.strip_tags($lng == $lang[0] ? $fieldb1 : $fieldb2).'</a>';
					$dbfa[] = array($nom[$lng == $lang[0] ? 24 : 25], $link);
				}
			}
			break;
		case 'fr' : // forum
            $zmbr = $userprefs[1] == '' && !isAccessGranted($members[5]) ? true : false;
			if ($serviz[126] == 'on' && !$zmbr) {
				ReadDoc($item[1]);
                if (isForumAccessGranted($fieldb2)) {
                    $dbw  = SelectDBFieldsByField(ReadDBFields(DBFORUM), 1, $fielda1);
                    $fid  = @$dbw[0][2];
                    $urw0 = empty($fieldb2) ? '-41-'.$fid : '-4-'.$fid.'-'.getForumcatId($fieldb2);
                    $urw1 = empty($fieldb2) ? '-411-'.$fid : '-5-'.$fid.'-'.getForumcatId($fieldb2);
                    $url0 = $site['TH'].'.php?lng='.$lng.'&amp;pg='.$fid.'&amp;cat='.getForumcatId($fieldb2).($fielda2 == '0' ? '' : '&amp;id=1#R'.$fielda2);
                    $url1 = $lng.'-'.$urlrw[5].($fielda2 == '0' ? $urw0.$z2 : $urw1.'-1#R'.$fielda2);
                    $lbl0 = ($fielda2 == '0' ? 'S' : 'R').$fielda1;
                    $lbl1 = $fielda2 == '0' ? $fieldb1 : CutLongWord(strip_tags($fieldc1), 36).'...';
                    $link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.$lbl0.' - '.strip_tags($lbl1).'</a>';
                    $dbfr[] = array($nom[$lng == $lang[0] ? 22 : 23], $link);
                }
			}
			break;
		case 'li' : // liens
            $zmbr = $userprefs[1] == '' && !isAccessGranted($members[3]) ? true : false;
			if ($serviz[127] == 'on' && !$zmbr) {
				ReadDoc($item[1]);
				if (isAuthorizedPublication($creadate) && isAccessGranted($fieldmod) && $status == 'a') {
					$url0 = 'links.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;tconfig='.$tconfig.$z2;
					$url1 = $lng.'-'.$urlrw[6].'-5-'.$fileid.'-'.$tconfig.$z2;
					$link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.strip_tags($lng == $lang[0] ? $fielda1 : $fielda2).' - '.strip_tags($lng == $lang[0] ? $fieldb1 : $fieldb2).'</a>';
					$dbli[] = array($nom[$lng == $lang[0] ? 3 : 13], $link);
				}
			}
			break;
		case 'gb' : // livre d'or
            $zmbr = $userprefs[1] == '' && !isAccessGranted($members[12]) ? true : false;
			if ($serviz[128] == 'on' && !$zmbr) {
				ReadDoc($item[1]);
				$url0 = $site['GB'].'.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;tconfig='.$tconfig.$z2;
				$url1 = $lng.'-'.$urlrw[7].'-4-'.$fileid.'-'.$tconfig.$z2;
				$link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.$author.' - '.CutLongWord(strip_tags($fieldc1), 36).'...</a>';
				$dbgb[] = array($nom[$lng == $lang[0] ? 9 : 19], $link);
			}
			break;
		case 'ne' : // nouvelles
            $zmbr = $userprefs[1] == '' && !isAccessGranted($members[13]) ? true : false;
			if ($serviz[129] == 'on' && !$zmbr) {
				ReadDoc($item[1]);
				if (isAuthorizedPublication($creadate) && isAccessGranted($fieldmod)) {
					$url0 = $site['NE'].'.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;tconfig='.$tconfig.$z2;
					$url1 = $lng.'-'.$urlrw[8].'-22-'.$fileid.'-'.$tconfig.$z2;
					$link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.strip_tags($lng == $lang[0] ? $fielda1 : $fielda2).' - '.strip_tags($lng == $lang[0] ? $fieldb1 : $fieldb2).'</a>';
					$dbne[] = array($nom[$lng == $lang[0] ? 7 : 17], $link);
				}
			}
			break;
		case 'ph' : // photos
            $zmbr = $userprefs[1] == '' && !isAccessGranted($members[2]) ? true : false;
			if ($serviz[130] == 'on' && $zmbr === false) {
                ReadDoc($item[1]);
                if (isAuthorizedPublication($creadate) && isAccessGranted($fieldmod) && $status == 'a') {
                    $url0 = 'photorama2.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;tconfig='.$tconfig.$z2;
                    $url1 = $lng.'-'.$urlrw[9].'-2-'.$fileid.'-'.$tconfig.$z2;
                    $link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.strip_tags($lng == $lang[0] ? $fielda1 : $fielda2).' - '.strip_tags($lng == $lang[0] ? $fieldb1 : $fieldb2).'</a>';
                    $dbph[] = array($nom[$lng == $lang[0] ? 2 : 12], $link);
                }
			}
			break;
		}
	}
	unset($db);
    if (!empty($dbag)) $cr = array_multisort($dbag2, SORT_ASC, $dbag);
	$dbw = array($dbag, $dbar, $dbra, $dbom, $dbbl, $dbrb, $dbdn, $dbfa, $dbfr, $dbli, $dbgb, $dbne, $dbph);
	foreach ($dbw as $db) {
		if (!empty($db)) {
			$out .= '
	<fieldset><legend>'.$db[0][0].'</legend>';
			foreach ($db as $item) {
				$out .= '
	  <div'.($pos == 'NWL' ? ' class="py-1"' : '').'>'.$item[1].'</div>';
			}
			$out .= '
	</fieldset>';
		}
	}
	unset($dbag, $dbar, $dbra, $dbom, $dbbl, $dbrb, $dbdn, $dbfa, $dbfr, $dbli, $dbgb, $dbne, $dbphn, $dbw);
	if ($out != '') {
		$sidebox    = $pos == BOX_LEFT || $pos == BOX_RIGHT;
		$titre      = getLabel($nom[$lng == $lang[0] ? 54 : 55]);
		$lenout     = mb_strlen($out, $charset);
		$max_height = $pos == 'NWL' ? 0 : $serviz[120];
		$txt        = '
	'.($pos == 'NWL' ? '' : '<a id="hautlastdoc'.$nbox.'"></a>').'
	<div class="text-center">
      '.($serviz[118] == 'on'  ? sprintf($web[645], $ndays) : sprintf($web[683], FormatDate(substr($userprefs[14], 0, 8)))).'
    </div>';
		if ($lenout > 0) {
			$txt .= '
<style>
.outLastdoc {
  max-height: '.($max_height > 0 ? $max_height.'px;' : 'none;').'
  overflow: '.($max_height > 0 ? 'hidden;' : 'visible;').'
}
</style>';
            if ($pos != 'NWL') $txt .= '
    <div id="minlastdoc'.$nbox.'" style="display: block;" class="lastdoc">';
            $txt .= '
      <div class="outLastdoc">
        '.$out.'
      </div>';
            if ($max_height > 0) $txt .= '
      <div class="text-end plusLastdocW">
        <a href="#lastdoc'.$nbox.'" title="'.$web[296].'" onclick="cache(\'minlastdoc'.$nbox.'\'); montre(\'maxlastdoc'.$nbox.'\');">
          '.$web[296].' <i class="far fa-plus-square"></i>
        </a>
      </div>';
            if ($pos != 'NWL') $txt .= '
    </div>';
            if ($max_height > 0) $txt .= '
    <div id="maxlastdoc'.$nbox.'" style="display: none;" class="lastdoc2">'.$out.'
      <div class="text-end plusLastdocW">
        <a href="#lastdoc'.$nbox.'" title="'.$web[57].'" onclick="cache(\'maxlastdoc'.$nbox.'\'); montre(\'minlastdoc'.$nbox.'\');">
	      '.$web[57].' <i class="far fa-minus-square"></i>
        </a>
      </div>
    </div>';
        }
        if ($pos == 'NWL') return $txt;
        $txt .= displayQuickConfig($pos);
		if ($sidebox  || $pos == BOX_BOTTOM) {
            $titre = mb_substr(trim($titre), 0, 1, $charset) == '§' ? '' : DisplayDownArrow($dynabox[15], 'NOV'.$nbox.$pos.$tconfig).$titre;
			return '
<div class="'.$pos.$tconfig.'NOV">'.boxthin($pos, $titre, $txt, ' NOV'.$nbox.$pos.$tconfig, $dynabox[15]).'</div>';
		} else {
            $titre = mb_substr(trim($titre), 0, 1, $charset) == '§' ? '' : $titre;
			return boxwide($pos, $titre, $txt, ' NOV'.$nbox.$pos.$tconfig);
        }
	} else
		return false;
}

################################################################################

function fbox_menubox($pos = NULL, $args = NULL) {
    global $lng, $lang, $drtuser, $site, $serviz, $userprefs, $web, $texte, $page, $urlrw, $tconfig, $charset,
        $type, $fileid, $status, $creadate, $moddate, $author, $email,$fielda1, $fielda2, $z2, 
		$fieldb1, $fieldb2, $fieldc1, $fieldc2,$fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
    static $nbox = 0;
    $nbox++;
	$tconfig = empty($tconfig) ? 0 : $tconfig;
    $sidebox = in_array($pos, array(BOX_LEFT, BOX_RIGHT, BOX_TOP, BOX_BOTTOM));
	$ilng    = $lng == $lang[0] ? 0 : 1 ;
    $menu    = $args[0];
    ReadDoc($menu);
    if (!isAccessGranted($fieldmod) || !isAuthorizedPublication($creadate)) return FALSE;
    if ($type != TYP_MENUBOX) return FALSE;
    if ($status != 'a') return FALSE;
    $type_menu = $fieldweb;
    $posimg    = $fieldd1;
	$class     = ' MNU'.$nbox.$pos.$fileid;
	$dynbox    = explode('|', $fielda2);
    $titre     = $lng == $lang[0] ? $fieldb1 : $fieldb2;
    $contenu   = PathToImage($lng == $lang[0] ? $fieldc1 : $fieldc2);
    $len       = strlen(parse_url($site[3], PHP_URL_PATH));
    $url_self  = $site['URLR'] == 'on' ? substr($_SERVER['REQUEST_URI'], $len): substr($_SERVER['PHP_SELF'], $len).($_SERVER['QUERY_STRING'] == '' ? '' : '?'.$_SERVER['QUERY_STRING']);
	$url_self .= $site['URLR'] == 'on' ? '': (strpos($url_self, 'tconfig') > 0 ? '' : '&amp;tconfig='.$tconfig);
    $dbw       = SelectDbFieldsByField(ReadDBFields(DBOPTMENU), 5, $menu);
    /**
     * $optmenu : tableau des options de menu
     *
     * $optmenu[*][0] = catégorie
     * $optmenu[*][1] = titre de l'option
     * $optmenu[*][2] = ID de l'option de menu
     * $optmenu[*][3] = URL de l'option de menu
     * $optmenu[*][4] = target du lien
     * $optmenu[*][5] = ID de la boîte menu
     * $optmenu[*][6] = indique si c'est la page courante (true/false]
	 * $optmenu[*][7] = indique si nouveauté
	 * $optmenu[*][8] = image new ou maj
     **/
    $optmenu  = array();
    $curr_cat = '';
    foreach ($dbw as $item) {
        if (!isAccessGranted($item[6])) continue;
        ReadDoc($item[4]);
		if (!isAuthorizedPublication($creadate)) continue;
        if ((mb_substr(trim($item[2]), 0, 1, $charset) == '§' && $ilng == 0) || (mb_substr(trim($item[3]), 0, 1, $charset) == '§' && $ilng == 1)) continue;
		$fieldmail = empty($fieldmail) || $fieldmail == 'ALL' ? $tconfig : $fieldmail;
        if ($status != 'a') continue;
        if ($lng == $lang[1] && empty($fieldb2)) continue;
		$img   = '';
		$urls  = explode('||', $lng == $lang[0] ? $fieldd1 : $fieldd2);
        $ancre = '';
        $posa  = strpos($urls[0], '#');
        if ($posa !== false) $ancre = substr($urls[0], $posa);
		$pg   = '';
		if (preg_match('!https?://|ftps?://|www.!', $urls[0]))
			$url = $urls[0];
		else {
            $is_art   = strpos($urls[0], 'articles.php') !== false;
            $is_photo = strpos($urls[0], 'photorama2.php') !== false;
			if ($is_art || $is_photo) {
				$pg   = substr($urls[0], strpos($urls[0], 'pg=') + 3);
				$pos1 = strpos($pg, '&');
				$pg   = $pos1 === false ? $pg : substr($pg, 0, $pos1);
                if ($is_art) {
                    $urls[0] = 'articles.php?lng='.$lng.'&pg='.$pg.'&mnuid='.$item[5].'&tconfig='.$fieldmail.$ancre;
                    $urls[1] = $lng.'-'.$urlrw[20].'-103-'.$pg.'-'.$item[5].'-'.$fieldmail;
                }
                if ($is_photo) {
                    $urls[0] = 'photorama2.php?lng='.$lng.'&pg='.$pg.'&mnuid='.$item[5].'&tconfig='.$fieldmail;
                    $urls[1] = $lng.'-'.$urlrw[9].'-30-'.$pg.'-'.$item[5].'-'.$fieldmail;
                }
			}
			$url = empty($urls[1]) ? $urls[0] : ($site['URLR'] != 'on' ? $urls[0] : $urls[1]);
			if (strpos($url, $site[3]) !== false) {
				$url  = CHEMIN.substr($url, strlen($site[3]));
				$slng = (strpos($url, '?lng=') > 0 || strpos($url, '&lng=') > 0 || $site['URLR'] == 'on') ? '' : 'lng='.$lng;
				if ($slng != '') $url .= (strpos($url, '?') > 0) ? '&'.$slng : '?'.$slng;
			} else {
				$url  = CHEMIN.$url;
				$slng = (strpos($url, '?lng=') > 0 || strpos($url, '&lng=') > 0 || $site['URLR'] == 'on') ? '' : 'lng='.$lng;
				if ($slng != '') $url .= (strpos($url, '?') > 0) ? '&'.$slng : '?'.$slng;
			}
		}
		if ($fieldc1 == '_POPUP')
			$target = '	onclick="window.open(this.href, \'popup'.$item[4].'\', '
				.'\'resizable=yes,status=yes,location=no,toolbar=yes,menubar=no,fullscreen=no,scrollbars=yes,dependent=no,width=1024,left=0,height=768,top=0\'); return false;"';
		else
			$target = ' target="'.strtolower($fieldc1).'"';
		$target  = strtolower($fieldc1) == '_self' ? '' : $target;
        $is_curr = basename($url_self) == basename($url);
		$isNew   = $img != '';
        if ($is_curr) {
            $curr_cat  = trim($item[0 + $ilng]);
			$dynbox[0] = $dynbox[0] != 'off' ? 'open' : $dynbox[0];
        }
		if (!empty($pg)) {
			ReadDoc($pg);
            if (($lng == $lang[0] && empty($fieldb1)) || ($lng == $lang[1] && empty($fieldb2))) continue;
            $accept = $type == TYP_ART ? ($serviz[154] == 'on' ? $serviz[136] : 'off') : ($type == TYP_PHOTO ? $serviz[142] : 'off');
			$imgpg  = getNewUpdateDoc($creadate, $moddate, $accept);
			$isNew  = $isNew || ($imgpg != '');
			$img    = $imgpg != '' ?  $imgpg : $img;
		} else {
			$imgpg = getNewUpdateDoc($creadate, $moddate, $serviz[154]);
			$isNew = $isNew || ($imgpg != '');
			$img   = $imgpg != '' ?  $imgpg : $img;
        }
        if ($isNew) {
            $dynbox[0] = 'open';
        }
		$optmenu[] = array(trim($item[0 + $ilng]), trim($item[2 + $ilng]), $item[4], $url, $target, $item[5], $is_curr, $isNew, $img);
    }
    unset($dbw);
    sort($optmenu);

    $curcats = array();
    /**
     * $liste = liste des catégories et items dans l'ordre d'affichage
     *
     * $liste[*]['opt'] = item du tableau $optmenu d'où provient l'élément
     * $liste[*]['cat'] = est-ce une catégorie (true/false)
     * $liste[*]['txt'] = texte de l'élément, cad soit la [sous-]catégorie, soit le titre du lien
     * $liste[*]['niv'] = niveau d'indentation (0=racine)
     * $liste[*]['vis'] = est-ce un élément visible (true/false)
     * $liste[*]['new'] = est-ce un élément nouveau ou modifié
	 *
     **/
    $liste = array();
    foreach ($optmenu as $item) {
        if (empty($item[0])) {
            $i = 0;
        } else {
            $cats = explode('|', $item[0]);
            $xcat = '';
            for ($i = 0; $i < count($cats); $i++) {
                $xcat .= (empty($xcat) ? '' : '|').$cats[$i];
                // @ pour supprimer les NOTICE dûes à l'initialisation aléatoire des tableaux
                if (@$curcats[$i] != @$cats[$i]) {
                    $liste[] = array(
                        'cat' => TRUE, 'txt' => trim($cats[$i]), 'niv' => $i+1,
                        'vis' => (0 === strpos($curr_cat, $xcat)), 'opt' => $item, 'new' => $item[7]);
                    $curcats[$i] = $cats[$i];
                    array_splice($curcats, $i+1);
                }
            }
        }
        $liste[] = array('cat' => FALSE, 'txt' => ($posimg == 'L' ? $item[8].$item[1] : $item[1].'&nbsp;'.$item[8]), 'niv' => $i+1, 'vis' => $item[6], 'opt' => $item, 'new' => $item[7]);
    }
    unset($cats, $curcats);
    $content = '';
    if (!empty($optmenu)) {
        $out = '
<div class="'.$pos.$tconfig.'mb'.$menu.'">';
        $content = empty($contenu) ? '' : '
 <div class="box">
'.$contenu.'
 </div>';
        $typeRubrique = 'mnu'.$nbox;
      	$marqueur = $texte[3].' ';
      	$curr_marq = '<i class="fas fa-caret-right puce"></i>&nbsp;';
      	$rubr = '---';
      	switch ($type_menu) {
        /**********************************************************************/
  		case "0" : // liste de menu
            $niveau = 0;
            $content .= '
 <div class="mbtype0">
  <ul class="item">';
            foreach ($liste as $item) {
                $opt = $item['opt'];
                $opt[3] = 1 != preg_match('/(ftp|http|https)$/i', $opt[3]) ? $opt[3].$z2 : $opt[3];
                while ($niveau > $item['niv']) {
                    $niveau--;
                    $padding = str_pad('', 3*$niveau);
                    $content .=  '
'.$padding.'  </ul>
'.$padding.' </div>
'.$padding.'</li>';
                }
                $niveau = $item['niv'];
                $padding = str_pad('', 3*$niveau);
                if ($item['cat']) {
                    $content .=  '
'.$padding.'<li>
'.$padding.' <div>
'.$padding.'  <p class="rubr font-weight-bold categ niv'.$item['niv'].'">'.$item['txt'].'</p>
'.$padding.'  <ul class="item">';
                }
                else {
                    $content .= '
'.$padding.'<li class="link '.($opt[6] ? 'curr_item' : 'item').' niv'.$item['niv'].'">'.($opt[6] ? $curr_marq : $marqueur).'<a href="'.htmlentities($opt[3]).'" title="'.strip_tags($opt[1]).'"'.$opt[4].'>'.$item['txt'].'</a>';
        			$content .= displayQuickConfig(NULL, $opt[2], 'optmenu&amp;menubox='.$opt[5].'&amp;form=2&amp;tri=-id&amp;id='.$opt[2], '', '', 17);
                    $content .= '
'.$padding.'</li>';
                }
            }
            while ($niveau > 1) {
                $niveau--;
                $padding = str_pad('', 3*$niveau);
                $content .= '
'.$padding.'  </ul>
'.$padding.' </div>
'.$padding.'</li>';
            }
            $content .= '
  </ul>
 </div>';
    	break;
        /**********************************************************************/
    	case "1" : // menu dynamique
            $niveau = 0;
            $content .= '
 <div class="mbtype1">
  <form name="'.$typeRubrique.'" method="post">
   <p class="ta-center">';
            foreach ($liste as $item) {
                $opt = $item['opt'];
                $opt[3] = 1 != preg_match('/(ftp|http|https)$/i', $opt[3]) ? $opt[3].$z2 : $opt[3];
                if ($item['cat'] && 1 == $item['niv']) {
                    if (0 != $niveau) {
            $content .= '
    </select>';
                    }
                    $content .= '
    <select class="form-control" size="1" onchange="if (\'\' != this.value) { document.'.$typeRubrique.'.action=this.value; document.'.$typeRubrique.'.submit(); }">';
                }
                $selected = $opt[6] ? ' selected="selected"' : '';
                $padding = '';
                for ($i = 1; $i < $item['niv']; $i++) $padding .= '&nbsp; ';
                if ($item['cat']) {
                    $content .= '
     <option class="categ niv'.$item['niv'].'" value=""'.$selected.'>'.$padding.strip_tags($item['txt']).'</option>';
                } else {
                    if (0 == $niveau) {
                    $content .= '
    <select size="1" onchange="if (\'\' != this.value) { document.'.$typeRubrique.'.action=this.value; document.'.$typeRubrique.'.submit(); }" title="">';
                    }
            		$content .= '
     <option class="link niv'.$item['niv'].($opt[6] ? ' current' : '').'" value="'.$opt[3].'"'.$selected.'>'.$padding.$marqueur.strip_tags($item['txt']).'</option>';
                }
                $niveau = $item['niv'];
            }
            $content .= '
    </select>
   </p>
  </form>
 </div>';
            break;
        /**********************************************************************/
    	case "2" : // menu liste dynamique n°1
    		$openimg = 'far fa-plus-square';
    		$closeimg = 'far fa-minus-square';
        /**********************************************************************/
    	case "3" : // menu liste dynamique n°2
    		if ($type_menu == "3") {
                $openimg = 'fas fa-chevron-right';
                $closeimg = 'fas fa-chevron-up';
    		}

            $niveau = 0;
			$cpt    = 0;
			$nliste = $liste;
            $nums   = array_pad(array(), 12, 1);
            $bids   = array();
            $cache  = '';
			$padding = '';
            $content .= '
 <div class="mbtype'.$type_menu.'">
  <ul class="item">';
            foreach ($liste as $item) {
                $opt = $item['opt'];
                $opt[3] = 1 != preg_match('/(ftp|http|https)$/i', $opt[3]) ? $opt[3].$z2 : $opt[3];
                while ($niveau > $item['niv']) {
                    $niveau--;
                    $padding = str_pad('', 3*$niveau);
                    $content .=  '
'.$padding.'  </ul>
'.$padding.' </div>
'.$padding.'</li>';
                }
                $niveau = $item['niv'];
                $padding = str_pad('', 3*$niveau);
                if ($item['cat']) {
					$n = $cpt + 1;
					$isNew = false;
					while ($n < count($nliste) && $nliste[$n]['niv'] > $niveau) {
						if ($nliste[$n]['new']) {
							$isNew = true;
							break;
						}
						$n++;
					} 
                    $baseid = $pos.'mb'.$opt[5].'_'.$item['niv'].'_'.$nums[$item['niv']]++;
                    if (!$item['vis'] && !$isNew) {
                        $cache .= '
  $(\'#'.$baseid.'\').toggle(); $(\'#'.$baseid.'p\').toggle(); $(\'#'.$baseid.'m\').toggle();';
                    }
                    $content .=  '
'.$padding.'<li>
'.$padding.' <div>
'.$padding.'  <p class="rubr font-weight-bold categ niv'.$item['niv'].'" data-rubrid="'.$baseid.'">'
    .'<i id="'.$baseid.'p" class="'.$openimg.'" title="'.$web[429].'" style="display: none;"></i>'
    .'<i id="'.$baseid.'m" class="'.$closeimg.'" title="'.$web[57].'"></i>&nbsp;'.$item['txt'].'</p>
'.$padding.'  <ul id="'.$baseid.'" class="item">';
                }
                else {
                    $content .= '
'.$padding.'<li class="link '.($opt[6] ? 
    'curr_item' : 'item').' niv'.$item['niv'].'">'.($opt[6] ? $curr_marq 
    : $marqueur).'<a href="'.htmlentities($opt[3]).'" title="'.strip_tags($opt[1]).'"'.$opt[4].'>'.$item['txt'].'</a>';
        			$content .= displayQuickConfig(NULL, $opt[2], 'optmenu&amp;menubox='.$opt[5].'&amp;form=2&amp;tri=-id&amp;id='.$opt[2], '', '', 17);
                    $content .= '
'.$padding.'</li>';
                }
				$cpt++;
            }
            while ($niveau > 1) {
                $niveau--;
                $padding = str_pad('', 3*$niveau);
                $content .= '
'.$padding.'  </ul>
'.$padding.' </div>
'.$padding.'</li>';
            }
            $content .= '
  </ul>
 </div>';
            if ($cache != '') {
                $content .= BeginJavascript().$cache.EndJavascript();
            }
            break;
        /**********************************************************************/
    	case "4" : // menu dynamique compact
            $content .= '
 <div class="mbtype4">
  <form name="'.$typeRubrique.'" method="post">
   <p class="ta-center">
    <select size="1" onchange="if (\'\' != this.value) { document.'.$typeRubrique.'.action=this.value; document.'.$typeRubrique.'.submit(); }" title="">';
            foreach ($liste as $item) {
                $opt = $item['opt'];
                $selected = $opt[6] ? ' selected="selected"' : '';
                $padding = '';
                for ($i = 1; $i < $item['niv']; $i++) $padding .= '&nbsp; ';
                if ($item['cat']) {
            		$content .= '
     <option class="categ niv'.$item['niv'].'" value=""'.$selected.'>'.$padding.strip_tags($item['txt']).'</option>';
                } else {
            		$content .= '
     <option class="link niv'.$item['niv'].($opt[6] ? ' current' : '').'" value="'.$opt[3].'"'.$selected.'>'.$padding.$marqueur.strip_tags($item['txt']).'</option>';
                }
            }
            $content .= '
    </select>
   </p>
  </form>
 </div>';
            break;
        }
        $content .= displayQuickConfig($pos, $menu, 'menu&amp;form=2&amp;tri=ch&amp;id='.$menu, '', '', 17);
        $titre    = mb_substr(trim($titre), 0, 1, $charset) == '§' ? '' : $titre;
        if ($sidebox) {
            $titre = DisplayDownArrow($dynbox[0], trim($class)).$titre;
            $out  .= boxthin($pos, $titre, $content, $class, $dynbox[0], 'nav');
        } else {
            $out .= boxwide($pos, $titre, $content, $class, 'nav');
        }
        $out .= '
</div>';
        return $out;
    } else {
        return false;
    }
}

################################################################################

function fbox_ddmenubox($pos = NULL, $args = NULL) {
    global $lng, $lang, $drtuser, $site, $serviz, $userprefs, $web, $urlrw, $tconfig, $charset, $meskin, $z2;
    global $type, $fileid, $status, $creadate, $moddate, $author, $email,
         $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
         $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
    static $nbox = 0;
    $nbox++;
    $sidebox = $pos == BOX_LEFT || $pos == BOX_RIGHT;
	$ilng    = $lng == $lang[0] ? 0 : 1 ;
    $menu    = $args[0];
    ReadDoc($menu);
    if (!isAccessGranted($fieldmod) || !isAuthorizedPublication($creadate)) return FALSE;
    if ($type != TYP_DDMENUBOX) return FALSE;
    if ($status != 'a') return FALSE;
    $len       = strlen(parse_url($site[3], PHP_URL_PATH));
    $url_self  = $site['URLR'] == 'on' ? substr($_SERVER['REQUEST_URI'], $len) : (substr($_SERVER['PHP_SELF'], $len).($_SERVER['QUERY_STRING'] == '' ? '' : '?'.$_SERVER['QUERY_STRING']));
	$url_self .= $site['URLR'] == 'on' ? '' : (strpos($url_self, 'tconfig') > 0 ? '' : '&amp;tconfig='.$tconfig);
    $type_menu = $fielda2;
	$class     = $type_menu.$fileid;
    $titre     = ($lng == $lang[0] ? $fieldb1 : $fieldb2);
	$titre     = (mb_substr(trim($titre), 0, 1, $charset) == '§' ? '' : '<span class="pe-3">'.$titre.'</span>');
    $dynabox   = $fieldc1;
    $dbw       = SelectDbFieldsByField(ReadDBFields(DBOPTDDMENU), 5, $menu);
	$optmenu   = array();
    $is_curr   = false;
    sort($dbw);
    $num = 0;
    foreach ($dbw as $item) {
        if (!isAccessGranted($item[6])) continue;
        ReadDoc($item[4]);
		if (!isAuthorizedPublication($creadate)) continue;
        if ($status != 'a') continue;
		if ($type_menu == 'DDL') {
			$panel     = PathToImage($ilng == 0 ? $fieldc1 : $fieldc2);
			$optmenu[] = array(trim($item[2 + $ilng]), $panel);
		} else {
			$urls     = explode('||', $lng == $lang[0] ? $fieldd1 : $fieldd2);
            $is_art   = strpos($urls[0], 'articles.php') !== false;
            $is_photo = strpos($urls[0], 'photorama2.php') !== false;
			$icon     = $fieldc2;
			$url      = '';
			$target   = '';
			if (trim($urls[0]) != '?') {
                $flda1   = $fielda1;
                $fldc1   = $fieldc1;
                $fldmail = $fieldmail;
				if (preg_match('!https?://|ftps?://|www.!', $urls[0]))
					$url = $urls[0];
				else {
					$urls[0] = strpos($urls[0], 'tconfig') > 0 ? $urls[0] : ($urls[0].'&tconfig='.(empty($fldmail) ? $tconfig : $fldmail));
					if ($is_art || $is_photo) {
                        $pg   = substr($urls[0], strpos($urls[0], 'pg=') + 3);
                        $pos1 = strpos($pg, '&');
                        $pg   = $pos1 === false ? $pg : substr($pg, 0, $pos1);
                        ReadDoc($pg);
                        if (!isAccessGranted($fieldmod)) continue;
					}
					if (!empty($urls[1]) && $fieldmail > 0 && ($is_art || $is_photo)) {
                        if ($is_art) $urls[1] = $lng.'-'.$urlrw[20].'-102-'.$pg.'-'.$fldmail;
                        if ($is_photo) $urls[1] = $lng.'-'.$urlrw[9].'-2-'.$pg.'-'.$fldmail;
					}
					$url = empty($urls[1]) ? $urls[0] : ($site['URLR'] != 'on' ? $urls[0] : $urls[1]);
                    $is_curr = $url_self == $url ? true : false;
					if (strpos($url, $site[3]) !== false) {
						$url  = CHEMIN.substr($url, strlen($site[3]));
						$slng = (strpos($url, '?lng=') > 0 || strpos($url, '&amp;lng=') > 0 || $site['URLR'] == 'on') ? '' : 'lng='.$lng;
						if ($slng != '') $url .= (strpos($url, '?') > 0) ? '&amp;'.$slng : '?'.$slng;
					} elseif ($url != '#') {
						$url  = CHEMIN.$url;
						$slng = (strpos($url, '?lng=') > 0 || strpos($url, '&lng=') > 0 || $site['URLR'] == 'on') ? '' : 'lng='.$lng;
						if ($slng != '') $url .= (strpos($url, '?') > 0) ? '&'.$slng : '?'.$slng;
					}
                    $url = 1 != preg_match('/(ftp|http|https)$/i', $url) ? $url.(strstr($url, 'index.php') ? '' : $z2) : $url;
				}
				if (strtoupper($fldc1) == '_POPUP')
					$target = '	onclick="window.open(this.href, \'popup'.$item[4].'\', 
						\'resizable=yes,status=yes,location=no,toolbar=yes,menubar=no,fullscreen=no,scrollbars=yes,dependent=no,width=1024,left=0,height=768,top=0\'); return false;"';
				else
					$target = ' target="'.strtolower($fldc1).'"';
				$target = strtolower($fldc1) == '_self' ? '': $target;
			}
			$optmenu[] = @array($item[0], trim($item[2 + $ilng]), str_replace('&', '&amp;', $url), $target, $icon, $flda1, $is_curr);
            if ($is_curr) {
                $pos = strrpos($item[0], '-');
                $pos = $pos !== false ? $pos : 10;
                $niv = substr($item[0], 0, $pos);
                for ($j = $num; $j > -1; $j--) {
                    if ($niv == $optmenu[$j][0] && 1 !== preg_match('!https?://|ftps?://|www.!', $optmenu[$j][2])) {
                        $pos            = strrpos($niv, '-');
                        $pos            = $pos !== false ? $pos : 10;
                        $niv            = substr($niv, 0, $pos);
                        $optmenu[$j][6] = $j > ($type_menu == 'DDH' ? 0 : -1) ? true : false;
                        $is_curr        = false;
                        if ($pos == 1) break;
                    }
                }
            }
		}
        $num++;
    }
    unset($dbw);
    if (!empty($optmenu)) {
		sort($optmenu);
		$out = '';		
		$content = '';			  
		if ($type_menu == 'DDL') {
			$out = '';
            $content = '
<nav class="navbar ddl '.$class.' navbar-expand-md">
  <a class="navbar-brand" href="'.$url_self.'">'.$titre.'</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSC-'.$class.'" aria-controls="navbarSC-'.$class.'" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarSC-'.$class.'">
    <ul class="navbar-nav m-0">';
			$n = 0;
			foreach ($optmenu as $opt) {
				$content .= '      
      <li class="nav-item dropdown w-100 T'.$n.'">
        <a class="nav-link dropdown-toggle T'.$n.'" href="#" id="navbarDDL" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          '.$opt[0].'  
        </a>
        <div class="dropdown-menu '.$class.' w-100" aria-labelledby="navbarDDL">
          <div class="container-fluid ms-0 ps-0">
            <div class="row w-100">
              <div class="col-md-12 m-0 ms-4 p-0 ps-4">
                '.$opt[1].'
              </div>
            </div>
          </div>
        </div>
      </li>';
				$n++;
			}
			$content .= '
    </ul>
  </div>
</nav>
';
            $class = ' ddl '.$class;
            $out .= boxwide($pos, '', $content, ' '.$class, 'div');
			$out .= displayQuickConfig($pos, false, '', '', '', 17);
			return $out;
		} 
        else {
			$content = '';	 
			switch ($type_menu) {
			case 'DDH' :
                $content = '
<nav class="navbar ddh '.$class.' navbar-expand-md navbar-light" id="navbar'.$class.'">
  <a class="navbar-brand" href="'.$url_self.'">'.$titre.'</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSC-'.$class.'" aria-controls="navbarSC-'.$class.'" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarSC-'.$class.'">
    <ul class="navbar-nav mr-auto">
                ';
                $scl = 'ddh';
				break;
			case 'DDV' :
				$content = '
<nav class="navbar ddv '.$class.'">
  <ul class="navbar-nav mr-auto">
';
                $scl = 'ddv';
				break;
			}
            $cnt = count($optmenu);
            $i   = 0;
            while ($i < $cnt) {
                $img = empty($optmenu[$i][4]) ? '' : '<img src="'.CHEMIN.'img/'.$optmenu[$i][4].'" class="ddmImg" />';
                $cur = explode('-', $optmenu[$i][0]);
                $c   = count($cur);
                $p   = $s = $c;
                if ($i > 0) {
                    $pred = explode('-', $optmenu[$i - 1][0]);
                    $p    = count($pred);
                }
                if ($i + 1 < $cnt) {
                    $succ = explode('-', $optmenu[$i + 1][0]);
                    $s    = count($succ);
                }
                if ($c == 1 && $s == 1) { 
                    $content .= '
      <li class="nav-item '.$class.' btn-'.$scl.' mt-4 L'.$optmenu[$i][0].'">
        <a class="nav-link" href="'.$optmenu[$i][2].'">'.$img.$optmenu[$i][1].'</a>
      </li>';
                }
                elseif ($c == 1 && $s > $c) { $content .= '
      <li class="nav-item dropdown '.$class.' btn-'.$scl.' L'.$optmenu[$i][0].'">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown'.$optmenu[$i][0].'" role="button" data-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false">
            '.$img.$optmenu[$i][1].'
        </a>
        <ul class="dropdown-menu" aria-labelledby="navbarDropdown'.$optmenu[$i][0].'">';
                }
                elseif ($c == $s) { $content .= '
          <li class="dropdown-item '.$class.($optmenu[$i][6] ? ' dd_current' : '').' L'.$optmenu[$i][0].'">
            <a href="'.$optmenu[$i][2].'"'.$optmenu[$i][3].'>'.$img.$optmenu[$i][1].'</a>
          </li>';
                }
                elseif ($s > $c) { $content .= '
          <li class="nav-item dropdown L'.$optmenu[$i][0].'">
            <a class="dropdown-item dropdown-toggle" href="#" id="navbarDropdown'.$optmenu[$i][0].'" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                '.$img.$optmenu[$i][1].'
            </a>
            <ul class="'.$scl.' dropdown-menu" aria-labelledby="navbarDropdown'.$optmenu[$i][0].'">';
                }
                elseif ($s < $c) { 
                    $content .= '
            <li class="dropdown-item '.$class.($optmenu[$i][6] ? ' dd_current' : '').' L'.$optmenu[$i][0].'">
              <a href="'.$optmenu[$i][2].'"'.$optmenu[$i][3].'>'.$img.$optmenu[$i][1].'</a>
            </li>
          </ul>
        </li>';
                    for ($k = 1; $k < $c - $s; $k++)
                        $content .= '
      </ul>
    </li>';
                }
                $i++;
            }
			switch ($type_menu) {
            case 'DDH' :
                $content .= '
      </ul>
    </li>
  </div>
</nav>';
                $class .= ' ddh';
                $title  = '';
                break;
            case 'DDV' :
                $content .= '
    </ul>
</nav>';
                $class .= ' ddv';
                $title  = $titre;
                break;
            }
            $content .= '
<script>
  $(function () {
    $("#navbar'.$class.'").bootnavbar();
  })
</script>';
			if ($sidebox) {
                $title = DisplayDownArrow($dynabox, trim($class)).$title;
				$out   = boxthin($pos, $title, $content, ' '.$class, $dynabox, 'div');
			} else {
				$out = boxwide($pos, $title, $content, ' '.$class, 'div');
			}
			$out .= displayQuickConfig($pos, false, '', '', '', 17);
			return $out;
		}
    } else {
        return false;
    }
}

################################################################################

function fbox_contact($pos = NULL, $args = NULL) {
    global $lng, $lang, $drtuser, $nom, $urlrw, $site, $serviz, $userprefs, $web, $dynabox, 
           $tconfig, $charset, $menuico, $members, $z2;
    static $nbox = 0;
    $nbox++;
    if ($serviz[99] == '') return false;
    if (!isAccessGranted($members[20])) return FALSE;
    $tconf = $menuico[21] == 'ALL' ? $tconfig : $menuico[21];
	$out = '
<div class="box text-start">';
	$ctfiles = ReadDBFields(CONFIGREP.'ctconfigs.dtb');
	foreach($ctfiles as $ct) {
		if ($ct[2] == 'a') $out .= '
  <div class="itemContactW">
    <a href="'.CHEMIN.($site['URLR'] == 'on' 
        ? $lng.'-'.$urlrw[2].'-2-'.$ct[1].'-'.$tconf.$z2 
        : $site['CT'].'.php?lng='.$lng.'&amp;ctconfig='.$ct[1].'&amp;tconfig='.$tconf.$z2).'" title="'.$ct[$lng == $lang[0] ? 3 : 4].'">
	  <i class="far fa-envelope imgContact"></i>'.($ct[$lng == $lang[0] ? 3 : 4]).'
	</a>
  </div>';
	}
    $out .= '
</div>';
    $out .= displayQuickConfig($pos, false, 'contact', '', '', 13);
	$titre = getLabel($nom[$lng == $lang[0] ? 56 : 57]);
	$titre = mb_substr(trim($titre), 0, 1, $charset) == '§' ? '' : DisplayDownArrow($dynabox[16], 'CT'.$nbox.$pos.$tconf).$titre;
    return '
<div class="'.$pos.$tconfig.'CT">'.boxthin($pos, $titre, $out, ' CT'.$nbox.$pos.$tconfig, $dynabox[16]).'
</div>';
}

################################################################################

function fbox_photobox($pos = NULL, $args = NULL) {
    global $lng, $lang, $drtuser, $nom, $urlrw, $site, $serviz, $userprefs, $web, 
           $dynabox, $charset, $z2, $titre, $members, $menuico;
	if (!in_array($pos, array(BOX_EDITO, BOX_LEFT, BOX_RIGHT, BOX_ABOVE, BOX_UNDER, BOX_TOP, BOX_BOTTOM))) return false;
    static $nbox = 0;
    $nbox++;
    if (!isAccessGranted($members[2])) return FALSE;
    if (!isAccessGranted($menuico[34])) return false;
	$phs = ReadDBFields(DBPHOTO);
	$dps = array();
	$n   = 0;
	foreach ($phs as $ph) {
        if (isAccessGranted($ph[6]) && is_file(CHEMIN.'photo/gal_'.$ph[4].'/params_'.$ph[4].INCEXT)) {
            include CHEMIN.'photo/gal_'.$ph[4].'/params_'.$ph[4].INCEXT;
            if ($param9 == 'on' && empty($param10)) $dps[] = array($ph[4], $n);
            $param9 = '';
        }
        $n++;
	}
    $tconf = $menuico[14] == 'ALL' ? ($serviz[157] != 0 ? $serviz[157] : import('tconfig', '', true, 0)) : $menuico[14];
	if (empty($dps)) return false;
	$nb   = rand(0, count($dps) - 1);
	$dir  = 'gal_'.$dps[$nb][0];
	$gals = ReadDBFields(CHEMIN.'photo/'.$dir.'/'.$dir.DBEXT);
    $imgs = array();
	foreach ($gals as $img) if ($img[1] == 'a') $imgs[] = $img;
	if (empty($imgs)) return false;
	if (!isset($serviz[182])) $serviz[182] = 0;
	if ($serviz[182] == 0) sort($imgs);
	if ($serviz[182] == 1)
		array_multisort(array_column($imgs, 0), SORT_ASC, array_column($imgs, 4), SORT_DESC, $imgs);
	if ($serviz[182] == 2)
		array_multisort(array_column($imgs, 4), SORT_DESC, array_column($imgs, 0), SORT_ASC, $imgs);
    $nbimg = count($imgs);
	$title = $phs[$dps[$nb][1]][$lng == $lang[0] ? 2 : 3];
	$comment = $img[$lng == $lang[0] ? 5 : 6];
	$tpos  = mb_strpos($title, '-->');
	$title = $tpos === false ? $title : mb_substr($title, $tpos + 3);
    $class = $nbox.$pos.$tconf;
	$out = '
<div class="text-center m-auto">
  <div class="phtitlebox">
    <a href="'.CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[9].'-2-'.$phs[$dps[$nb][1]][4].'-'.$tconf.$z2 : 'photorama2.php?lng='.$lng.'&amp;pg='.$phs[$dps[$nb][1]][4].'&amp;tconfig='.$tconf.$z2).'" title="'.$title.'">'.$title.'</a>
  </div>';
    $ugw  = in_array($pos, array(BOX_LEFT, BOX_RIGHT)) ? 540 : (in_array($pos, array(BOX_ABOVE, BOX_UNDER, BOX_EDITO)) ? 800 : 600);
    $ugh  = intval($ugw * 0.75);
    $long = floor($ugw / 17);
    $nblg = intval(floor($nbimg / ($long > 0 ? $long : 1))) + ($long * 17 > $ugw ? 1 : 0);
	switch ($serviz[83]) {
	case '0' :
        $out .= '
  <script src="'.CHEMIN.'inc/unitegallery/themes/tilesgrid/ug-theme-tilesgrid.js"></script>
  <script>
    jQuery(document).ready(function(){
	  jQuery("#'.$class.$dir.'").unitegallery({
		gallery_theme: "tilesgrid",
        grid_num_rows: 1,
        theme_navigation_type: "arrows",
        theme_arrows_margin_top: 8,
        tile_border_width: 3,
        tile_border_radius: 8,
        tile_enable_shadow: false,
        lightbox_type: "compact",
        lightbox_arrows_position: "inside",
      });
    });
  </script>';
		break;
	case '1' :
        $out .= '
  <script src="'.CHEMIN.'inc/unitegallery/themes/carousel/ug-theme-carousel.js"></script>
  <script>
    jQuery(document).ready(function(){
	  jQuery("#'.$class.$dir.'").unitegallery({
		gallery_theme: "carousel",
        theme_navigation_margin: 0,
        tile_border_width: 3,
        tile_width: '.$ugw.',
        tile_height: '.$ugh.',
        tile_border_radius: 0,
        tile_enable_shadow: false,
        lightbox_type: "compact",
        lightbox_arrows_position: "inside",
      });
    });
  </script>';
		break;
	case '2' :
        $out .= '
  <script src="'.CHEMIN.'inc/unitegallery/themes/slider/ug-theme-slider.js"></script>
  <script>
    jQuery(document).ready(function(){
	  jQuery("#'.$pos.$nbox.$dir.'").unitegallery({
		gallery_theme: "slider",
        gallery_width: '.$ugw.',
        gallery_height: '.($ugh + 32 + ($nblg * 15)).',
        slider_transition: "fade",
        slider_transition_speed: 1500,
        slider_enable_zoom_panel: false,
        slider_enable_arrows: false,
        slider_enable_progress_indicator: false,
        slider_item_padding_bottom: '.(32 + ($nblg * 15)).',
      });
    });
  </script>';
		break;
	}
	$out .= '
  <div id="'.$class.$dir.'" style="display:none;margin:auto;">';
	foreach ($imgs as $img) {
		$out .= '
    <img alt="" src="'.CHEMIN.'photo/'.$dir.'/'.$img[2].'" style="display:none;" />';
	}
	$out .= '
  </div>
</div>';
    $out .= displayQuickConfig($pos, false, '', '', '', 18);
	$title = getLabel($nom[$lng == $lang[0] ? 30 : 31]);
	$title = mb_substr(trim($title), 0, 1, $charset) == '§' ? '' : DisplayDownArrow($dynabox[17], 'photobox SWPH'.$class).$title;
	return '
<div class="'.$class.'SWPH">
  '.boxthin($pos, $title, $out, ' photobox SWPH'.$class, $dynabox[17]).'
</div>';
}

################################################################################

function isAdmin($pseudo) {
    global $serviz;
	if (!isset($serviz[31]) || !isset($serviz[176]))
	{
		die("!!! Erreur - Veuillez contacter le webmaster du site. !!! Error - Please contact the site's webmaster");
	}
    if (empty($pseudo) && trim($serviz[31]) == '' && trim($serviz[176]) == '' && is_file(CHEMIN.'install/install.php')) return TRUE;
    if ($pseudo == $serviz[31] || $pseudo == $serviz[176]) return TRUE;
    if ($serviz[42] == 'on') {
		static $admins = null;
		if (!isset($admins)) {
			$admins = ReadDbFields(ADMINS);
		}
		foreach ($admins as $admin) {
			if ((!empty($pseudo) && !empty($admin[0])) && ($pseudo == $admin[0])) return TRUE;
		}
	}
    return FALSE;
}
################################################################################

function fbox_alldocuments($pos = NULL, $args = NULL) {
    global $lng, $lang, $nom, $page, $serviz, $site, $userprefs, $web, $urlrw, $tconfig, 
	       $charset, $drtuser, $dynabox, $members, $z2;
    global $type, $fileid, $status, $creadate, $moddate, $author, $email,
         $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
         $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
	
	$datjourl = new DateTime('now');
	$dateheureminiutesecl = $datjourl->format('Y_m_d_H');
	$nomficlog = CHEMIN.'data/log/func_boxes'.'_'.$dateheureminiutesecl.'.log';
	
    if (!isAccessGranted($members[22])) return FALSE;
	if ($serviz[132] != 'on') return false;
    static $nbox = 0;
    $nbox++;
    $out   = '';
			
	$db    = $dba = $dbb = array();
	$docid = SelectDBFieldsByStatus(ReadDBFields(DOCID), 'a');
	$dbdat = ReadDBFields(DOCDATES);
	foreach ($dbdat as $item)
	{
		if ($item[2] != '')
			$db[] = array($item[2], $item[0]);
		else
			$db[] = array($item[1], $item[0]);
	}
	rsort($db);
	$dbag = $dbag2 = $dbar = $dbra = $dbom = $dbbl = $dbrb = $dbdn = $dbfa = $dbfr = $dbli = $dbgb = $dbne = $dbph = array();
	foreach ($db as $item) {
		$typ    = SelectDBFieldsByField($docid, 1, $item[1]);
		$idat   = $serviz[169] == 'on' ? '' : substr(FormatDate($item[0]), 0, 10).' :';
		$typdoc = empty($typ[0][0]) ? '' : $typ[0][0];
		switch ($typdoc) {
		case 'ar' : // articles
   		case 'ra' : // réactions articles
            $zmbr = $userprefs[1] == '' && !isAccessGranted($members[1]) ? true : false;
            $id = false;
            if ($typdoc == 'ra' && $serviz[29] != '2' && $serviz[166] == 'on' && !$zmbr) {
                $dbw = SelectDBFieldsByField(ReadDBFields(DBREACT), 0, $item[1]);
                $id  = $dbw[0][1];
            }
            if ($typdoc == 'ar' && $serviz[122] == 'on' && !$zmbr) {
                $id = $item[1];
            }
            if ($id) {
                ReadDoc($id);
                $afm   = explode('|', $fieldmail);
                $tconf = $typdoc == 'ar' ? ($afm[0] == 'ALL' ? $tconfig : $afm[0]) : $fieldmail;
                $tconf = empty($tconf) ? $tconfig : $tconf;
                if (isAuthorizedPublication($creadate) && isAccessGranted($fieldmod) && $status == 'a') {
                    $fa1  = $fielda1;
                    $fa2  = $fielda2;
                    $fb1  = $fieldb1;
                    $fb2  = $fieldb2;
                    $dbwo = ReadDBFields(DBOPTMENU);
                    $url0 = 'articles.php?lng='.$lng.'&pg='.$id.'&tconfig='.$tconf.$z2;
                    $url1 = $lng.'-'.$urlrw[20].'-102-'.$id.'-'.$tconf.$z2;
                    foreach ($dbwo as $item1) {
                        ReadDoc($item1[4]);
                        if (!isAuthorizedPublication($creadate) || !isAccessGranted($fieldmod) || $status != 'a') continue;
                        $urls = explode('||', $lng == $lang[0] ? $fieldd1 : $fieldd2);
                        $pg   = substr($urls[0], strpos($urls[0], 'pg=') + 3);
                        $pos1 = strpos($pg, '&');
                        $pg   = $pos1 === false ? $pg : substr($pg, 0, $pos1);
                        if ($pg == $id) {
                            $fa1  = $fielda1;
                            $fa2  = $fielda2;
                            $fb1  = $fieldb1;
                            $fb2  = $fieldb2;
                            $tcnf = empty($fieldmail) ? $tconf : $fieldmail;
                            $url0 = 'articles.php?lng='.$lng.'&pg='.$pg.'&mnuid='.$item1[5].'&tconfig='.$tcnf.$z2;
                            $url1 = $lng.'-'.$urlrw[20].'-103-'.$pg.'-'.$item1[5].'-'.$tcnf.$z2;
                            break;
                        }
                    }
                    $link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.strip_tags($lng == $lang[0] ? $fa1 : $fa2).' - '.strip_tags($lng == $lang[0] ? $fb1 : $fb2).'</a>';
                    if ($typdoc == 'ar') {
                        $dbar[] = array($web[169], $link); 
                        $dba[]  = $id;
                    }
                    if ($typdoc == 'ra') {
                        $dbra[] = array($web[187], $link); 
                        $dba[]  = $id;
                    }
                }
            }
            break;
		case 'om' : // options de menu indépendantes des services
			if ($serviz[154] == 'on') {
				ReadDoc($item[1]);
                $targets = array('agenda', 'articles', 'blog', 'download', 'faq', 'links', 'guestbk', 'thread', 'news', 'photorama2');
                $isValid = true;
                foreach($targets as $target) if (strpos($fieldd1, $target.'.php') === true) $isValid = false;
				if (isAuthorizedPublication($creadate) && isAccessGranted($fieldmod) && $status == 'a' && $isValid) {
                    $fa1  = $fielda1;
                    $fa2  = $fielda2;
                    $fb1  = $fieldb1;
                    $fb2  = $fieldb2;
                    $urls = explode('||', $lng == $lang[0] ? $fieldd1 : $fieldd2);
                    if (empty($urls[1])) $urls[1] = $urls[0];
					$link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $urls[1].$z2 : $urls[0].$z2).'" target="_self" title="">'.strip_tags($lng == $lang[0] ? $fa1 : $fa2).' - '.strip_tags($lng == $lang[0] ? $fb1 : $fb2).'</a>';
					$dbom[] = array($web[448].' '.$idat, $link);
				}
			}
			break;
		case 'bl' : // blog et commentaires
		case 'rb' : 
            $zmbr = $userprefs[1] == '' && !isAccessGranted($members[15]) ? true : false;
            $id = false;
            if ($typdoc == 'rb' && $serviz[57] != '2' && $serviz[167] == 'on' && !$zmbr) {
                $dbw = SelectDBFieldsByField(ReadDBFields(DBREBLOG), 0, $item[1]);
                $id  = $dbw[0][1];
            }
            if ($typdoc == 'bl' && $serviz[123] == 'on' && !$zmbr) {
                $id = $item[1];
            }
            if ($id) {
                ReadDoc($id);
				if (isAuthorizedPublication($creadate) && isAccessGranted($fieldmod) && $status == 'a') {
                    $url0 = $site['BL'].'.php?lng='.$lng.'&amp;sel=pg&amp;tconfig='.$tconfig.'&amp;pg='.$fileid.$z2;
                    $url1 = $lng.'-'.$urlrw[1].'-2-pg-'.$tconfig.'-'.$fileid.$z2;
                    $link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.strip_tags($lng == $lang[0] ? $fielda1 : $fielda2).' - '.strip_tags($lng == $lang[0] ? $fieldb1 : $fieldb2).'</a>';
                    if ($typdoc == 'bl') {
                        $dbbl[] = array($nom[$lng == $lang[0] ? 42 : 43], $link); 
                        $dbb[]  = $id;
                    }
                    if ($typdoc == 'rb') {
                        $dbrb[] = array($web[407].' '.mb_strtolower($nom[$lng == $lang[0] ? 42 : 43]), $link);
                        $dbb[]  = $id;
                    }
				}
			}
			break;
		case 'dn' : // téléchargements
            $zmbr = $userprefs[1] == '' && !isAccessGranted($members[7]) ? true : false;
			if ($serviz[124] == 'on' && !$zmbr) {
				ReadDoc($item[1]);
				if (isAuthorizedPublication($creadate) && isAccessGranted($fieldmod) && $status == 'a') {
					$url0 = $site['DN'].'.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;tconfig='.$tconfig.$z2;
					$url1 = $lng.'-'.$urlrw[11].'-4-'.$fileid.'-'.$tconfig.$z2;
					$link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.strip_tags($lng == $lang[0] ? $fielda1 : $fielda2).' - '.strip_tags($lng == $lang[0] ? $fieldb1 : $fieldb2).'</a>';
					$dbdn[] = array($nom[$lng == $lang[0] ? 1 : 11], $link);
				}
			}
			break;
		case 'fa' : // faq
            $zmbr = $userprefs[1] == '' && !isAccessGranted($members[4]) ? true : false;
			if ($serviz[125] == 'on' && !$zmbr) {
				ReadDoc($item[1]);
				if (isAuthorizedPublication($creadate) && isAccessGranted($fieldmod) && $status == 'a') {
					$url0 = 'faq.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;tconfig='.$tconfig.$z2;
					$url1 = $lng.'-'.$urlrw[4].'-6-'.$fileid.'-'.$tconfig.$z2;
					$link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.strip_tags($lng == $lang[0] ? $fielda1 : $fielda2).' - '.strip_tags($lng == $lang[0] ? $fieldb1 : $fieldb2).'</a>';
					$dbfa[] = array($nom[$lng == $lang[0] ? 24 : 25], $link);
				}
			}
			break;
		case 'fr' : // forum
            $zmbr = $userprefs[1] == '' && !isAccessGranted($members[5]) ? true : false;
			if ($serviz[126] == 'on' && !$zmbr) {
				ReadDoc($item[1]);
                if (isForumAccessGranted($fieldb2)) {
                    $dbw  = SelectDBFieldsByField(ReadDBFields(DBFORUM), 1, $fielda1);
                    $fid  = @$dbw[0][2];
                    $urw0 = empty($fieldb2) ? '-41-'.$fid : '-4-'.$fid.'-'.getForumcatId($fieldb2);
                    $urw1 = empty($fieldb2) ? '-411-'.$fid : '-5-'.$fid.'-'.getForumcatId($fieldb2);
                    $url0 = $site['TH'].'.php?lng='.$lng.'&amp;pg='.$fid.'&amp;cat='.getForumcatId($fieldb2).($fielda2 == '0' ? '' : '&amp;id=1#R'.$fielda2);
                    $url1 = $lng.'-'.$urlrw[5].($fielda2 == '0' ? $urw0.$z2 : $urw1.'-1#R'.$fielda2);
                    $lbl0 = ($fielda2 == '0' ? 'S' : 'R').$fielda1;
                    $lbl1 = $fielda2 == '0' ? $fieldb1 : CutLongWord(strip_tags($fieldc1), 36).'...';
                    $link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.$lbl0.' - '.strip_tags($lbl1).'</a>';
                    $dbfr[] = array($nom[$lng == $lang[0] ? 22 : 23], $link);
                }
			}
			break;
		case 'li' : // liens
            $zmbr = $userprefs[1] == '' && !isAccessGranted($members[3]) ? true : false;
			if ($serviz[127] == 'on' && !$zmbr) {
				ReadDoc($item[1]);
				if (isAuthorizedPublication($creadate) && isAccessGranted($fieldmod) && $status == 'a') {
					$url0 = 'links.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;tconfig='.$tconfig.$z2;
					$url1 = $lng.'-'.$urlrw[6].'-5-'.$fileid.'-'.$tconfig.$z2;
					$link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.strip_tags($lng == $lang[0] ? $fielda1 : $fielda2).' - '.strip_tags($lng == $lang[0] ? $fieldb1 : $fieldb2).'</a>';
					$dbli[] = array($nom[$lng == $lang[0] ? 3 : 13], $link);
				}
			}
			break;
		case 'gb' : // livre d'or
            $zmbr = $userprefs[1] == '' && !isAccessGranted($members[12]) ? true : false;
			if ($serviz[128] == 'on' && !$zmbr) {
				ReadDoc($item[1]);
				$url0 = $site['GB'].'.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;tconfig='.$tconfig.$z2;
				$url1 = $lng.'-'.$urlrw[7].'-4-'.$fileid.'-'.$tconfig.$z2;
				$link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.$author.' - '.CutLongWord(strip_tags($fieldc1), 36).'...</a>';
				$dbgb[] = array($nom[$lng == $lang[0] ? 9 : 19], $link);
			}
			break;
		case 'ne' : // nouvelles
            $zmbr = $userprefs[1] == '' && !isAccessGranted($members[13]) ? true : false;
			if ($serviz[129] == 'on' && !$zmbr) {
				ReadDoc($item[1]);
				if (isAuthorizedPublication($creadate) && isAccessGranted($fieldmod)) {
					$url0 = $site['NE'].'.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;tconfig='.$tconfig.$z2;
					$url1 = $lng.'-'.$urlrw[8].'-22-'.$fileid.'-'.$tconfig.$z2;
					$link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.strip_tags($lng == $lang[0] ? $fielda1 : $fielda2).' - '.strip_tags($lng == $lang[0] ? $fieldb1 : $fieldb2).'</a>';
					$dbne[] = array($nom[$lng == $lang[0] ? 7 : 17], $link);
				}
			}
			break;
		case 'ph' : // photos
            $zmbr = $userprefs[1] == '' && !isAccessGranted($members[2]) ? true : false;
			if ($serviz[130] == 'on' && $zmbr === false) {
                ReadDoc($item[1]);
                if (isAuthorizedPublication($creadate) && isAccessGranted($fieldmod) && $status == 'a') {
                    $url0 = 'photorama2.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;tconfig='.$tconfig.$z2;
                    $url1 = $lng.'-'.$urlrw[9].'-2-'.$fileid.'-'.$tconfig.$z2;
                    $link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.strip_tags($lng == $lang[0] ? $fielda1 : $fielda2).' - '.strip_tags($lng == $lang[0] ? $fieldb1 : $fieldb2).'</a>';
                    $dbph[] = array($nom[$lng == $lang[0] ? 2 : 12], $link);
                }
			}
			break;
		}
	}
	unset($db);
	$dbw = array($dbag, $dbar, $dbra, $dbom, $dbbl, $dbrb, $dbdn, $dbfa, $dbfr, $dbli, $dbgb, $dbne, $dbph);
	foreach ($dbw as $db) {
		if (!empty($db)) {
			$out .= '
	<fieldset><legend>'.$db[0][0].'</legend>';
			foreach ($db as $item) {
				$out .= '
					<div'.($pos == 'NWL' ? ' class="py-1"' : '').'>'.$item[0].' '.$item[1].'</div>';
			}
			$out .= '
	</fieldset>';
		}
	}
	unset($dbag, $dbar, $dbra, $dbom, $dbbl, $dbrb, $dbdn, $dbfa, $dbfr, $dbli, $dbgb, $dbne, $dbphn, $dbw);
	if ($out != '') {
		$sidebox    = $pos == BOX_LEFT || $pos == BOX_RIGHT;
		$titre      = getLabel($nom[$lng == $lang[0] ? 58 : 59]);
		$lenout     = mb_strlen($out, $charset);
		$max_height = $pos == 'NWL' ? 0 : $serviz[120];
		$txt        = '
	'.($pos == 'NWL' ? '' : '<a id="hautlastdoc'.$nbox.'"></a>');
		if ($lenout > 0) {
			$txt .= '
<style>
.outLastdoc {
  max-height: '.($max_height > 0 ? $max_height.'px;' : 'none;').'
  overflow: '.($max_height > 0 ? 'hidden;' : 'visible;').'
}
</style>';
            if ($pos != 'NWL') $txt .= '
    <div id="minlastdoc'.$nbox.'" style="display: block;" class="lastdoc">';
            $txt .= '
      <div class="outLastdoc">
        '.$out.'
      </div>';
            if ($max_height > 0) $txt .= '
      <div class="text-end plusLastdocW">
        <a href="#lastdoc'.$nbox.'" title="'.$web[296].'" onclick="cache(\'minlastdoc'.$nbox.'\'); montre(\'maxlastdoc'.$nbox.'\');">
          '.$web[296].' <i class="far fa-plus-square"></i>
        </a>
      </div>';
            if ($pos != 'NWL') $txt .= '
    </div>';
            if ($max_height > 0) $txt .= '
    <div id="maxlastdoc'.$nbox.'" style="display: none;" class="lastdoc2">'.$out.'
      <div class="text-end plusLastdocW">
        <a href="#lastdoc'.$nbox.'" title="'.$web[57].'" onclick="cache(\'maxlastdoc'.$nbox.'\'); montre(\'minlastdoc'.$nbox.'\');">
	      '.$web[57].' <i class="far fa-minus-square"></i>
        </a>
      </div>
    </div>';
        }
        if ($pos == 'NWL') return $txt;
        $txt .= displayQuickConfig($pos);
		if ($sidebox  || $pos == BOX_BOTTOM) {
            $titre = mb_substr(trim($titre), 0, 1, $charset) == '§' ? '' : DisplayDownArrow($dynabox[15], 'NOV'.$nbox.$pos.$tconfig).$titre;
			return '
<div class="'.$pos.$tconfig.'NOV">'.boxthin($pos, $titre, $txt, ' NOV'.$nbox.$pos.$tconfig, $dynabox[15]).'</div>';
		} else {
            $titre = mb_substr(trim($titre), 0, 1, $charset) == '§' ? '' : $titre;
			return boxwide($pos, $titre, $txt, ' NOV'.$nbox.$pos.$tconfig);
        }
	} else
		return false;
}

?>
