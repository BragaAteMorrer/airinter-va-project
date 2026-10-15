<?php
/*******************************************************************************
 *   AdminWebmster Main page
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
 * v6.00.24 (August 12, 2026) : Rights install and rights scripts
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'administrateur.php')) {
    header('location:../index.php');
    die();
}
$skintheme = ExploreDir('skins/');
$items1    = array();
                            $items1[] = array('txt'=>$admin[276],  'href'=>'about');
                            $items1[] = array('txt'=>$admin[904],  'href'=>'update');
                            $items1[] = array('txt'=>$admin[1265], 'href'=>'help');
                            $items1[] = array('txt'=>$admin[1554], 'href'=>'protect');
                            $items1[] = array('txt'=>$admin[817],  'href'=>'maintenance');
							
$items2a   = array();
$items2b   = array();
                            $items2a[] = array('txt'=>$admin[7],    'href'=>'config1');
                            $items2a[] = array('txt'=>$admin[169],  'href'=>'config5');
                            $items2a[] = array('txt'=>$admin[166],  'href'=>'config4');
if ($serviz[99] == 'on')    $items2a[] = array('txt'=>$admin[1183], 'href'=>'contact&amp;act=1', 'src'=>'contact');
                            $items2a[] = array('txt'=>$admin[1264], 'href'=>'search');
							
                            $items2b[] = array('txt'=>$admin[888],  'href'=>'config9', 'src'=>'config2');
                            $items2b[] = array('txt'=>$admin[890],  'href'=>'config2', 'src'=>'config9');
                            $items2b[] = array('txt'=>$admin[346],  'href'=>'config6');
                            $items2b[] = array('txt'=>$admin[469].' / '.strtolower($admin[770]),  'href'=>'config3');
                            $items2b[] = array('txt'=>$admin[719],  'href'=>'config8', 'src'=>'css');							

$items3a   = array();
$items3b   = array();
$items3c   = array();
$items3d   = array();
                            $items3a[] = array('txt'=>$admin[925],  'href'=>'members&amp;act=5', 'src'=>'members2');
                            $items3a[] = array('txt'=>$admin[696],  'href'=>'members&amp;act=2', 'src'=>'members3');
                            $items3a[] = array('txt'=>$admin[697],  'href'=>'members&amp;act=3', 'src'=>'members4');
// if ($serviz[42] == 'on')    $items3a[] = array('txt'=>$admin[820],  'href'=>'attribdroits&amp;etape=1', 'src'=>'droits1');
if ($serviz[42] == 'on' && isAdmin($userprefs[1]))    $items3a[] = array('txt'=>$admin[820],  'href'=>'attribdroits&amp;etape=1', 'src'=>'droits1');
if ($serviz[42] == 'on')    $items3a[] = array('txt'=>$admin[821],  'href'=>'gestredac', 'src'=>'droits2');
if ($serviz[42] == 'on')    $items3a[] = array('txt'=>$admin[1476], 'href'=>'gestredac&amp;act=2', 'src'=>'droits3');

                            $items3b[] = array('txt'=>$admin[278],  'href'=>'dbcheck');
                            $items3b[] = array('txt'=>$admin[170],  'href'=>'maintain');
							$items3b[] = array('txt'=>$admin[577],  'href'=>'archive');
							$items3b[] = array('txt'=>$admin[1844], 'href'=>'archiveepuration', 'src'=>'archive');
                            $items3b[] = array('txt'=>$admin[1126], 'href'=>'install');
                            $items3b[] = array('txt'=>$admin[1245], 'href'=>'save');
                            $items3b[] = array('txt'=>$admin[1093], 'href'=>'import');
                            $items3b[] = array('txt'=>$admin[171],  'href'=>'upload');
							
                            $items3c[] = array('txt'=>$admin[653],  'href'=>'counter', 'src'=>'counter1');
                            $items3c[] = array('txt'=>$admin[654],  'href'=>'countart', 'src'=>'counter2');
                            $items3c[] = array('txt'=>$admin[655],  'href'=>'countforum', 'src'=>'counter3');
                            $items3c[] = array('txt'=>$admin[656],  'href'=>'countdwnl', 'src'=>'counter4');
                            $items3c[] = array('txt'=>$admin[972],  'href'=>'statsvw', 'src'=>'stats1');
                            $items3c[] = array('txt'=>$admin[973],  'href'=>'statsprg', 'src'=>'stats2');
                            $items3c[] = array('txt'=>$admin[974],  'href'=>'statscfg', 'src'=>'stats3');
							
                            $items3d[] = array('txt'=>$admin[7].' Log',    'href'=>'logconfig', 'src'=>'eye1');
                            $items3d[] = array('txt'=>$admin[554],  'href'=>'logread', 'src'=>'eye2');
							$items3d[] = array('txt'=>$admin[1807],  'href'=>'config_log_smtp', 'src'=>'eye2');	
							$items3d[] = array('txt'=>$admin[1911],  'href'=>'config_log_list', 'src'=>'eye2');	
							$items3d[] = array('txt'=>$admin[830],  'href'=>'configlang', 'src'=>'article');

$items4a   = array();
$items4b   = array();
$items4c   = array();
$items4d   = array();
                            $items4a[] = array('txt'=>$admin[270],  'href'=>'freebox&amp;tri=ch', 'src'=>'freebox');
if ($serviz[27] == 'on')    $items4a[] = array('txt'=>$admin[602],  'href'=>'rss', 'src'=>'newsrss');
if ($serviz[19] == 'on')    $items4a[] = array('txt'=>$admin[263],  'href'=>'banner'); /// $serviz[19] est toujours √† 'on'
if ($serviz[0] == 'on')     $items4a[] = array('txt'=>$admin[6],    'href'=>'think'); /// $serviz[0] est toujours √† 'on'

                            $items4b[] = array('txt'=>$admin[876],  'href'=>'menuico&amp;tri=ch', 'src'=>'menuico');
                            $items4b[] = array('txt'=>$admin[1216], 'href'=>'menu&amp;tri=ch', 'src'=>'menu1');
                            $items4b[] = array('txt'=>$admin[1217], 'href'=>'menubox&amp;act=1', 'src'=>'menu2');
                            $items4b[] = array('txt'=>$admin[1349], 'href'=>'ddmenu&amp;tri=ch', 'src'=>'menu3');
                            $items4b[] = array('txt'=>$admin[1350], 'href'=>'menubox&amp;act=2', 'src'=>'menu4');

if ($serviz[9] == 'on')     $items4c[] = array('txt'=>$admin[632],  'href'=>'photo', 'src'=>'photo2');
if ($serviz[9] == 'on')     $items4c[] = array('txt'=>$admin[633],  'href'=>'photorama&amp;act=2&amp;sel=3', 'src'=>'photo3');
if ($serviz[9] == 'on')     $items4c[] = array('txt'=>$admin[631],  'href'=>'photorama&amp;act=2&amp;sel=1', 'src'=>'photo1');
if ($serviz[9] == 'on')     $items4c[] = array('txt'=>$admin[1741], 'href'=>'liquidslider&amp;act=1', 'src'=>'photo4');
							
                            $items4d[] = array('txt'=>$admin[348],  'href'=>'snmenu&amp;act=1', 'src'=>'socnet1');
                            $items4d[] = array('txt'=>$admin[349],  'href'=>'socnet&amp;tri=ch', 'src'=>'socnet2');
							$items4d[] = array('txt'=>$admin[1776], 'href'=>'rgpd&amp;act=1', 'src'=>'socnet1');

$items5a   = array();
$items5b   = array();
$items5c   = array();
if ($serviz[8] == 'on')     $items5a[] = array('txt'=>$admin[13],   'href'=>'news');
                            $items5a[] = array('txt'=>$admin[5],    'href'=>'art&amp;tri=-id', 'src'=>'article');
if ($serviz[29] != '')      $items5a[] = array('txt'=>$admin[476],  'href'=>'react');
if ($serviz[10] == 'on')    $items5a[] = array('txt'=>$admin[18],   'href'=>'dnload', 'src'=>'download');
if ($serviz[11] == 'on')    $items5a[] = array('txt'=>$admin[10],   'href'=>'links');
if ($serviz[14] == 'on')    $items5a[] = array('txt'=>$admin[206],  'href'=>'faq');
if ($serviz[12] == 'on')    $items5a[] = array('txt'=>$admin[11],   'href'=>'guestbk', 'src'=>'guestbook');
if ($serviz[47] == 'on')    $items5a[] = array('txt'=>$admin[624],  'href'=>'agenda&amp;tri=ch', 'src'=>'agenda');
if ($serviz[13] == 'on')    $items5a[] = array('txt'=>$admin[942],  'href'=>'frconfig', 'src'=>'config71');
if ($serviz[13] == 'on')    $items5a[] = array('txt'=>$admin[943],  'href'=>'frcateg', 'src'=>'config72');
if ($serviz[13] == 'on')    $items5a[] = array('txt'=>$admin[945],  'href'=>'rules', 'src'=>'config74');
if ($serviz[13] == 'on')    $items5a[] = array('txt'=>$admin[9],    'href'=>'forum&amp;tri=-ch', 'src'=>'forum1');
if ($serviz[13] == 'on')    $items5a[] = array('txt'=>$admin[1908],    'href'=>'clos_postforum&amp;action=list', 'src'=>'forum1');

if ($serviz[53] == 'on')    $items5b[] = array('txt'=>$admin[15],   'href'=>'brub&amp;tri=ch', 'src'=>'blogrub');
if ($serviz[53] == 'on')    $items5b[] = array('txt'=>$admin[770],  'href'=>'blog&amp;tri=ch', 'src'=>'blog');
if ($serviz[53] == 'on')    $items5b[] = array('txt'=>$admin[774],  'href'=>'reblog');
if ($serviz[53] == 'on')    $items5b[] = array('txt'=>$admin[791],  'href'=>'bss', 'src'=>'blogrss');

if ($serviz[36] != '')      $items5c[] = array('txt'=>$admin[540],  'href'=>'nwllist', 'src'=>'newsletter1');
if ($serviz[36] != '')      $items5c[] = array('txt'=>$admin[925],  'href'=>'nwlgroup&amp;act=5', 'src'=>'newsletter2');
if ($serviz[36] != '')      $items5c[] = array('txt'=>$admin[538],  'href'=>'nwl', 'src'=>'newsletter3');
if ($serviz[36] != '')      $items5c[] = array('txt'=>$admin[534],  'href'=>'nwlsend', 'src'=>'newsletter4');
if ($serviz[36] != '')      $items5c[] = array('txt'=>$admin[1080], 'href'=>'newssend', 'src'=>'newsletter5');
$items5d   = array();
							$items5d[] = array('txt'=>$admin[1430], 'href'=>'sec_ban_def', 'src'=>'security1');
                            $items5d[] = array('txt'=>$admin[1431], 'href'=>'sec_ban_cfg', 'src'=>'security2');
                            $items5d[] = array('txt'=>$admin[1432], 'href'=>'sec_ban_log', 'src'=>'security3');
							$items5d[] = array('txt'=>$admin[1895], 'href'=>'sec_ban_counters_ip', 'src'=>'security3');								
							$items5d[] = array('txt'=>$admin[1810],  'href'=>'sec_ban_logguppy', 'src'=>'security5');	

if (is_dir(CHEMIN.'plugins/')) {
	$pluginlist = ExploreDir("plugins/");
	$items6 = array();
	if (!empty($pluginlist)) {
		foreach ($pluginlist as $item) {
			if(is_file("plugins/".$item."/plugin.inc")) {
				$plugin_admin_name = '';
				include "plugins/".$item."/plugin.inc";
				if ($plugin_admin_name != '') {
					$items6[] = array('txt'=>$plugin_admin_name, 'href'=>'plugin&amp;plug='.$plugin_admin_url, 'src'=>'plugins/'.$plugin_admin_icon);
				}
			}
		}
	}
}

echo '
    <div class="table-responsive-md">
    <table class="bord w-98 m-auto rounded-pill fs-14">
      <tr>
        <td class="col0 w-85">'.@ExecFunctionBox(BOX_BOTTOM, '||fbox_ariane||W12').'</td>
        <td class="col3 w-15 text-center pr-2">
          <strong><a href="admin.php?lng='.$lng.'&amp;logout=1">'.$admin[19].'</a></strong>
        </td>
      </tr>
    </table>
    </div>
    <div class="tbl w-100">
      <div class="bg-white">
      <nav class="navbar ddl navbar-expand-md">
        <a class="navbar-brand" href="#" title="'.$admin[23].'" style="color:#FF0000;">&nbsp;<i class="fas fa-tools">&nbsp;</i></a>&nbsp;';
if (file_exists(CHEMIN.'install/install.php') && ($userprefs[1] == $serviz[31] || $userprefs[1] == $serviz[176])) {
	echo '
        <a class="btn btn-md bg-yellow navbar-brand" href="'.CHEMIN.'install/install.php?lng='.$lng.'" title="Install" style="color:#FF0000;">Install</a>';
}
echo '
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSC-DDLADMIN" aria-controls="navbarSC-DDLADMIN" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSC-DDLADMIN">
          <style>
            @media screen and (max-width: 1000px) {
              .navbar.ddl ul.navbar-nav {
                -ms-flex-direction: column;
                -webkit-flex-direction: column;
                flex-direction: column;
              }
            }
          </style>
          <ul class="navbar-nav m-0">
            <li class="nav-item dropdown w-100">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDDL" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                '.$admin[1355].'
              </a>
              <div class="dropdown-menu DDLADMIN w-100 bg-transparent" aria-labelledby="navbarDDL">
                <div class="container-fluid ml-0 pl-0">
                  <div class="row w-auto">
                    <div class="col-md-12 ml-3 pt-2 showblock">';
ShowBlock('', $items1, 6);
echo '
                    </div>
                  </div>
                </div>
              </div>
            </li>      
            <li class="nav-item dropdown w-100">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDDL" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                '.$admin[1356].'
              </a>
              <div class="dropdown-menu DDLADMIN bg-transparent w-100" aria-labelledby="navbarDDL">
                <div class="container-fluid ml-0 pl-0">
                  <div class="row w-auto">
                    <div class="col-md-12 ml-3 pt-2 showblock">';
ShowBlock('', $items2a, 5);
ShowBlock('', $items2b, 5, $skintheme);
echo '
                    </div>
                  </div>
                </div>
              </div>
            </li>
            <li class="nav-item dropdown w-100">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDDL" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                '.$admin[881].'
              </a>
              <div class="dropdown-menu DDL461 bg-transparent w-100" aria-labelledby="navbarDDL">
                <div class="container-fluid ml-0 pl-0">
                  <div class="row w-auto">
                    <div class="col-md-12 ml-3 pt-2 showblock">';
ShowBlock('', $items3a, 7);
ShowBlock('', $items3b, 7);
ShowBlock('', $items3c, 7);
ShowBlock('', $items3d, 7);
echo '
                    </div>
                  </div>
                </div>
              </div>
            </li>
            <li class="nav-item dropdown w-100">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDDL" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                '.$admin[1357].'
              </a>
              <div class="dropdown-menu DDL461 bg-transparent w-100" aria-labelledby="navbarDDL">
                <div class="container-fluid ml-0 pl-0">
                  <div class="row w-auto">
                    <div class="col-md-12 ml-3 pt-2 showblock">';
ShowBlock('', $items4a, 5);
ShowBlock('', $items4b, 5);
ShowBlock('', $items4c, 5);
if (!empty($items4d))
ShowBlock('', $items4d, 5);
echo '
                    </div>
                  </div>
                </div>
              </div>
            </li>
            <li class="nav-item dropdown w-100">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDDL" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                '.$admin[882].'
              </a>
              <div class="dropdown-menu DDL461 bg-transparent w-100" aria-labelledby="navbarDDL">
                <div class="container-fluid ml-0 pl-0">
                  <div class="row w-auto">
                    <div class="col-md-12 ml-3 pt-2 showblock">';
ShowBlock('', $items5a, 6);
if (!empty($items5b))
ShowBlock('', $items5b, 6);
if (!empty($items5c))
ShowBlock('', $items5c, 6);
echo '
                </div>
              </div>
            </div>
          </div>
        </li>';
if (!empty($items5d)) {
	echo '
            <li class="nav-item dropdown w-100">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDDL" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                '.$admin[1819].'
              </a>
              <div class="dropdown-menu DDL461 bg-transparent w-100" aria-labelledby="navbarDDL">
                <div class="container-fluid ms-0 ps-0">
                  <div class="row w-auto">
                    <div class="col-md-12 ms-3 pt-2 showblock">';
ShowBlock('', $items5d, 7);
	echo '
                    </div>
                  </div>
                </div>
              </div>
            </li>';
}

if (!empty($items6)) {
	echo '
            <li class="nav-item dropdown w-100">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDDL" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                '.$admin[883].'
              </a>
              <div class="dropdown-menu DDL461 bg-transparent w-100" aria-labelledby="navbarDDL">
                <div class="container-fluid ml-0 pl-0">
                  <div class="row w-auto">
                    <div class="col-md-12 ml-3 pt-2 showblock">';
ShowBlock('', $items6, 7, NULL, true);
	echo '
                    </div>
                  </div>
                </div>
              </div>
            </li>';
}
echo '
          </ul>
        </div>
      </nav>
      </div>';
echo '
    <!-- End of ADMIN TOPBOXES -->
	';
unset($items1, $items2a, $items2b, $items3a, $items3b, $items3c, $items3d, $items4a, $items4b, $items4c, $items5a, $items5b, $items5c, $items5d, $items6);
?>