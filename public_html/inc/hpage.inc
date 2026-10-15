<?php
/*******************************************************************************
 *   Site Index Top of Page
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
 * v6.00.24 (August 12, 2026) : Delete lines log: Conrol file user exist
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'hpage.inc')) {
    header('location:../index.php');
    die();
}
if (function_exists('opcache_get_configuration'))
{
	// Si opcache est active mettre en disable
	if ($site[7] == 'on')
	{
		if (OPCACHEDISABLE == 1)
		{
			if (OPCACHEINISET == 1) // Gestion des options caches enable/disable
			{	
				{	
					if (ini_get('opcache') == 1) 
					{
						ini_set('opcache.enable', '0');
//						echo ' OPcache désactivé fait<br />';
					}
				}
			}
		}
	}
} // fin de existance opcache_get_configuration()
$obgo = FALSE;
if (($site[26] == 'on')) {
    if (!strstr($endurl, 'admin/') && !strstr($endurl, $site['PG'].'.php')) {
        if (extension_loaded('zlib') > 0) {
            if (strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
                $obgo = ob_start();
            }
        }
    }
}

$toppage = stripslashes($site[($lang[0] == $lng ? 0 : 11)]);
if (($page[22]) && (!empty($topmess))) {
    $toppage .= ' - '.strip_tags($topmess);
}
if ($serviz[32] == 'on' && $serviz[42] == 'on' && $userprefs[1] != '' && FileDBExist(CHEMIN.'admin/redac/'.$userprefs[1].INCEXT)) {
    include CHEMIN.'admin/redac/'.$userprefs[1].INCEXT;
}
$tconfig = import('tconfig');

if (!empty($userprefs[1]) && @substr($_COOKIE[COOKIE_COOKIE], 0, 1) == '1' && !OK_RGPD_date($userprefs[1]) && (file_exists(MSGREP.$userprefs[1].DBEXT))) {
	if (!OK_RGPD_date($userprefs[1])) 
	{
		setRGPDdate();
	}
}
elseif (!empty($userprefs[1] && !file_exists(MSGREP.$userprefs[1].DBEXT)))
{
	$userprefs = array();
	$userprefs = array_fill(0, 31, '');
}

echo '<!DOCTYPE html>
<html lang="'.$lng.'">';

	///////////////////////////////////////////////////////////////////////////////////
	//                                                                               //
	//                GuppY is protected by the CeCILL FREE LICENSE.                 //
	//  REMOVING OR MODIFYING THE FOLLOWING COPYRIGHT NOTICE IS STRICTLY FORBIDDEN.  //
	//                                                                               //
	///////////////////////////////////////////////////////////////////////////////////

echo '

  <!--[  GuppY v'.MP_VERSION.' CeCILL Copyright (C) '.MP_COPYRIGHT.' by Laurent Duveau - https://www.freeguppy.org/  ]-->
  
<head>
<meta http-equiv="content-type" content="text/html; charset='.$charset.'" />
<meta name="application-name" content="'.$toppage.'" />

<title>'.$toppage.'</title>

<meta name="description" content="'.$site[1].'" />
<meta name="generator" content="GuppY CMS" />
<meta name="author" content="'.$site[9].'" />
<meta name="keywords" content="'.$site[2].'" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">';

echo '
<script>
  var charset = "'.$charset.'";
  var site0 = "'.htmlspecialchars($site[($lang[0] == $lng ? 0 : 11)]).'";
  var site3 = "'.SITE3.'";
</script>';

include CONFIGREP.'lancrgpd.inc'; // RGPD (ne pas supprimer)

/*
 Fin de rgpd.js
 */
 if (is_dir(CHEMIN.'inc/incpage')) {
    $hd = opendir(CHEMIN.'inc/incpage');
    while ($file = readdir($hd)) {
        if (1 == preg_match('!^[^_].*(\.inc|\.inc\.php)$!', $file)) {
			include CHEMIN.'inc/incpage/'.$file;
        }
    }
	closedir($hd);
}

if (!defined('NO_CRYPT') && $serviz[100] > 0 && empty($_COOKIE['GuppYCrypt'])) echo '
<script> window.location.reload(); </script>';

echo '
<link rel="shortcut icon" href="'.$site[3].'favicon.ico">';

echo '
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css" 
  integrity="sha512-rt/SrQ4UNIaGfDyEXZtNcyWvQeOq0QLygHluFQcSjaGB04IxWhal71tKuzP6K8eYXYB6vJV4pHkXcmFGGQ1/0w==" crossorigin="anonymous" />
<link rel="stylesheet" href="'.CHEMIN.'inc/lang/flags.css" />
<link rel="stylesheet" href="'.CHEMIN.'inc/img/avatars/'.$page[23].'.css" />
<link rel="stylesheet" href="'.CHEMIN.'inc/img/general/forum.css" />
<link rel="stylesheet" href="'.CHEMIN.'inc/img/general/general.css" />
';
if (is_file ($meskin.'confskin.inc')) {
    include $meskin.'confskin.inc';
} else {
    include CHEMIN.'skins/no_skin/confskin.inc';
}
if (file_exists($meskin."style.css")) echo '
<link rel="stylesheet" href="'.$meskin.'style.css" />';
else echo '
<link rel="stylesheet" href="no_skin/style.css" />';
if (file_exists($meskin."bs4style.css")) echo '
<link rel="stylesheet" href="'.$meskin.'bs4style.css" />';
if (file_exists($meskin."styleplus.css")) echo '
<link rel="stylesheet" href="'.$meskin.'styleplus.css" />';
if ($tconfig > 0 && file_exists($meskin.'stylepage'.$tconfig.'.css')) echo '
<link rel="stylesheet" href="'.$meskin.'stylepage'.$tconfig.'.css" />';
if (file_exists($meskin."mobstyle.css")) echo '
<link rel="stylesheet" href="'.$meskin.'mobstyle.css" />';

echo '
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.3.1/styles/default.min.css" 
  integrity="sha512-3xLMEigMNYLDJLAgaGlDSxpGykyb+nQnJBzbkQy2a0gyVKL2ZpNOPIj1rD8IPFaJbwAgId/atho1+LBpWu5DhA==" crossorigin="anonymous" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" 
  integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/SocialIcons/1.0.1/soc.min.css" 
  integrity="sha512-PTz/4lAo890ortUEd041dNdebPVxpjxZiTTgW8DXUTIiPZQGXFua9U7izCygP7NqHUDmaDF4F1CswmblvYq4Vw==" crossorigin="anonymous" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" 
  integrity="sha512-c42qTSw/wPZ3/5LBzD+Bw5f7bSF2oxou6wEb+I/lqeaKV5FDIfMvvRp772y4jcJLKuGUOpbJMdg/BTl50fJYAw==" crossorigin="anonymous" />
<link rel="stylesheet" href="'.CHEMIN.'inc/unitegallery/css/unite-gallery.css" />
';

echo '
<style media="print"> @import url('.CHEMIN.'inc/print.css); </style>';

$dir = CHEMIN.'inc/csshead';
if (is_dir($dir)) {
    $allcss = scandir($dir);
    if (FALSE !== $allcss) {
        sort($allcss);
        foreach ($allcss as $onecss) {
            $info = pathinfo($onecss);
            if (isset($info['extension']) && $info['extension'] == 'css') echo '
<link rel="stylesheet" href="'.$dir.'/'.$onecss.'" />';
        }
        unset($allcss, $onecss);
    }
}

// ordre de chargement : jquery, popper, bootstrap (ou jquery, bundle)
echo 
  JavascriptFile(CHEMIN.'inc/hpage.js').'
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" 
  integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.bundle.min.js" 
	integrity="sha512-igl8WEUuas9k5dtnhKqyyld6TzzRjvMqLC79jkgT3z02FvJyHAuUtyemm/P/jYSne1xwFI06ezQxEwweaiV7VA==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-migrate/3.5.2/jquery-migrate.min.js" 
  integrity="sha512-BzvgYEoHXuphX+g7B/laemJGYFdrq4fTKEo+B3PurSxstMZtwu28FHkPKXu6dSBCzbUWqz/rMv755nUwhjQypw==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js" 
  integrity="sha512-0QbL0ph8Tc8g5bLhfVzSqxe9GERORsKhIn1IrpxDAgUsbBGz/V7iSav2zzW325XGd1OMLdL4UiqRJj702IeqnQ==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.3.1/highlight.min.js" 
  integrity="sha512-Pbb8o120v5/hN/a6LjF4N4Lxou+xYZ0QcVF8J6TWhBbHmctQWd8O6xTDmHpE/91OjPzCk4JRoiJsexHYg4SotQ==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/SocialIcons/1.0.1/soc.min.js" 
  integrity="sha512-eUJ3eP9+avp5kHKhfx5gB0vzLEgMkZiOmZcpVqJmlg9hMMse2SChMOTSDWl6oYGfmYW2N7oO/W6CqpgYBneqKw==" crossorigin="anonymous">
</script>
<script src="https://unpkg.com/bootstrap-show-password@1.2.1/dist/bootstrap-show-password.min.js"></script>
'.JavascriptFile(CHEMIN.'inc/unitegallery/js/unitegallery.min.js');

$dir = CHEMIN.'inc/jshead';
if (is_dir($dir)) {
    $alljs = scandir($dir);
    if (FALSE !== $alljs) { 
        sort($alljs);
        foreach ($alljs as $onejs) {
            $info = pathinfo($onejs);
            if (isset($info['extension']) && $info['extension'] == 'js')
				echo '
<script src="'.$dir.'/'.$onejs.'"></script>
';
        }
        unset($alljs, $onejs);
    }
}

if (is_dir(CHEMIN.'inc/jqhead')) {
    $hd = opendir(CHEMIN.'inc/jqhead');
    while ($file = readdir($hd)) {
        if (1 == preg_match('!^[^_].*(\.js|\.js\.php)$!', $file)) {
			include CHEMIN.'inc/jqhead/'.$file;
        }
    }
	closedir($hd);
}

echo '
<script>
  $(document).ready(function() {
    $("pre").each(function(i, block) {
      hljs.highlightBlock(block);
    });
    $("pre code").each(function(i, block) {
      hljs.highlightBlock(block);
    });
  });
</script>';

$tconfig = isset($tconfig) ? $tconfig : import('tconfig', '', true, 0);
$tconfig = !isset($templates[$tconfig]) ? 0 : $tconfig;

if (empty($prt)) echo '
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-parallax/1.1.3/jquery-parallax-min.js" 
  integrity="sha512-ES/eSqVi/9sgeZfvunOto+gwgFGrD/hzi5UOJFDR1Me8acKSBJIb2Gk0IyKje2ZaX+OovAG2/bRzj/uBcNeesg==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-scrollTo/2.1.3/jquery.scrollTo.min.js" 
  integrity="sha512-PsJ1f4lw0Jrga4wbDOvdWs9DFl88C1vlcH2VQYqgljHBmzmqtGivUkzRHWx2ZxFlnysKUcROqLeuOpYh9q4YNg==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-localScroll/2.0.0/jquery.localScroll.min.js" 
  integrity="sha512-x/Viuh5YndnrDISWqrZ6rerGnHccdLv/TW2B+xEGqubrLGCT6LdBGhnQxXo1Q4sXFgO12YeRWDYJkIV41OtOTA==" crossorigin="anonymous">
</script>
';

if (empty($prt)) echo '
<script>
  $(document).ready(function(){		
    $("#paranav").localScroll(800);
    $("#slide1").parallax("50%", 0.2, true);
    $("#slide2").parallax("50%", 0.2, true);
    $("#slide3").parallax("50%", 0.2, true);
  })
</script>
';
if (!isset($page[54])) $page[54] = '0,0,0,0,0,0';
$numlines = explode(',', $page[54]);
if ($numlines[$tconfig] != '') {
    echo '
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.sticky/1.0.4/jquery.sticky.min.js" 
  integrity="sha512-QABeEm/oYtKZVyaO8mQQjePTPplrV8qoT7PrwHDJCBLqZl5UmuPi3APEcWwtTNOiH24psax69XPQtEo5dAkGcA==" crossorigin="anonymous">
</script>
';
    echo "
<script>
  $(document).ready(function() {
    $('#T".$tconfig."entrow".$numlines[$tconfig]."').sticky({topSpacing:0,zIndex:1040});
  })
  $(window).scroll(function() {
    if ($('#T".$tconfig."entrow".$numlines[$tconfig]."').css('position') == 'fixed') {
      $('#T".$tconfig."entrow".$numlines[$tconfig]."').addClass('fixed-top-line');
    }
    else {
      $('#T".$tconfig."entrow".$numlines[$tconfig]."').removeClass('fixed-top-line');
    }
  });
</script>
";
}

echo $headinc;

if (isset($prt)) {
    switch ($prt) {
    case -1 :
    case -2 :
    case -3 :
		echo '
<style media="screen">
  @import url('.CHEMIN.'inc/preview.css);
</style>';
        break;
    case 1 :
    case 2 :
    case 3 :
		echo '
<style media="screen">
  @import url('.CHEMIN.'inc/preview.css);
</style>';
        echo '
<script>
  window.print();
</script>
';
        break;
    }
}

if ($body[7] == 'on' && empty($prt))
    echo '
<style>
#returnOnTop {
  bottom: '.$body[5].';
  opacity: '.$opacity[20].';
}
</style>
';
    echo "
<script>
  $(document).ready( function () {
    $('body').append('<div id=\"returnOnTop\" title=\"".$web[136]."\">&nbsp;</div>');
    $('#returnOnTop').click( function() {
    $('html,body').animate({scrollTop: 0}, 'slow');
    });
  });
  $(window).scroll(function() {
    if ($(window).scrollTop() < ".$body[6].")
      $('#returnOnTop').fadeOut();
    else
      $('#returnOnTop').fadeIn();
  });
</script>
";

echo '
<!--[if lte IE 9]>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js" 
  integrity="sha256-3Jy/GbSLrg0o9y5Z5n1uw0qxZECH7C6OQpVBgNFYa0g=" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js" 
  integrity="sha256-g6iAfvZp+nDQ2TdTR/VVKJf3bGro4ub5fvWSWVRi2NE=" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectivizr/1.0.2/selectivizr-min.js" 
  integrity="sha256-8fF/6nzuSprnSYGykwsQo6r9cT9FZAgN7yTl+4NCozg=" crossorigin="anonymous">
</script>
<![endif]-->

</head>

<body class="m-0 text-center'.($prt == 0 ? ' body' : '').'">';

$cookies = intval(@substr($_COOKIE[COOKIE_COOKIE], 1));
if (!empty($cookies) && time() > $cookies) {
    echo '
<script>
var sValue = "";
document.cookie = "'.COOKIE_COOKIE.'" + "=" + encodeURIComponent(sValue) + ";expires=0";
</script>
';
    $cookies = 0;
}
/*
if (empty($cookies) && !OK_RGPD_date($userprefs[1])) {
    echo '
<script>
function setCookiesAccept() {
  var sValue = "'.(empty($userprefs[1]) ? '0'.strval(time() + 259200) : '1'.strval(time() + 31536000)).'";
  var today = new Date(), expires = new Date();
  expires.setTime(today.getTime() + '.(empty($userprefs[1]) ? 259200000 : 31536000000).');
  document.cookie = "'.COOKIE_COOKIE.'" + "=" + encodeURIComponent(sValue) + ";expires=" + expires.toGMTString();
  window.location.reload();
}
</script>
';
}*/
if (@substr($_COOKIE[COOKIE_COOKIE], 0, 1) == '0' && !empty($userprefs[1])) {
    echo '
<script>
  var sValue = "'.('1'.strval(time() + 31536000)).'";
  var today = new Date(), expires = new Date();
  expires.setTime(today.getTime() + 31536000000);
  document.cookie = "'.COOKIE_COOKIE.'" + "=" + encodeURIComponent(sValue) + ";expires=" + expires.toGMTString();
</script>
';
}

// extraction des éléments des boîtes utilisées WEB et MOB
$bccount = 0;
$wposbox = array();
foreach ($templates as $keyconf=>$valueconf) {
	foreach (array(BOX_LSIDE, BOX_LEFT, BOX_CENTER, BOX_RIGHT, BOX_RSIDE) as $key) {
		foreach ($xposbox[$keyconf][BOX_TOP][$key] as $i=>$value) {
			$args  = '';
			if (!empty($value)) {
				$pos   = strrpos($value, 'W');
				$args  = $pos === false ? '' : substr($value, $pos + 1);
				$value = $pos === false ? $value : substr($value, 0, $pos);
				$value = substr($value, -1) == '|' ? substr($value, 0, -2) : $value;			
			}
			$xposbox[$keyconf][BOX_TOP][$key][$i] = $value;
			$wposbox[$keyconf][BOX_TOP][$key][$i] = $args;
		}
		foreach ($xposbox[$keyconf][BOX_BOTTOM][$key] as $i=>$value) {
			$args  = '';
			if (!empty($value)) {
				$pos   = strrpos($value, 'W');
				$args  = $pos === false ? '' : substr($value, $pos + 1);
				$value = $pos === false ? $value : substr($value, 0, $pos);
				$value = substr($value, -1) == '|' ? substr($value, 0, -2) : $value;
			}
			$xposbox[$keyconf][BOX_BOTTOM][$key][$i] = $value;
			$wposbox[$keyconf][BOX_BOTTOM][$key][$i] = $args;
		}
	}
}
foreach (array(BOX_LSIDE, BOX_LEFT, BOX_CENTER, BOX_RIGHT, BOX_RSIDE) as $key) {
	foreach ($xposbox[BOX_EDITO][$key] as $i=>$value) {
		$args  = '';
		if (!empty($value)) {
			$pos   = strrpos($value, 'W');
			$args  = $pos === false ? '' : substr($value, $pos + 1);
			$value = $pos === false ? $value : substr($value, 0, $pos);
			$value = substr($value, -1) == '|' ? substr($value, 0, -2) : $value;
		}
		$xposbox[BOX_EDITO][$key][$i] = $value;
		$wposbox[BOX_EDITO][$key][$i] = $args;
	}
}

if (!isset($userprefs3)) $userprefs3 = $userprefs[3];

echo '
  <a id="z1"></a>
';
if (!isset($page[50])) $page[50] = '';
if (empty($prt) && $page[50] != 'no') {
    echo '
  <script>
    $(document).ready(function(){		
      $("#paranav").localScroll(800);
    })
  </script>
';
    echo '
  <ul id="paranav"> <!-- parallax navigator -->
    <li><a href="#z1" title="'.$web[136].'"></a></li>
    <li><a href="#z2" title="'.$web[695].'"></a></li>
    <li><a href="#z3" title="'.$web[696].'"></a></li>
  </ul>
';
}

// Site en travaux
if (empty($prt) && $serviz[44] == "on") {
    echo '
    <section id="work">
      <div class="row mx-0 px-0">
        <div class="col-md-12 mx-0 px-0 notice text-center font-weight-bold workprogress">
          ATTENTION : site en travaux - CAUTION: work in progress
        </div>
      </div>
    </section>
';
}

/*
// message RGPD
$cookies = (is_file(CHEMIN.'install/install.php') ? 'off' : $cookies);
if (empty($prt) && empty($cookies) && !OK_RGPD_date($userprefs[1])) {
    echo '
  <div id="overlay"></div>
  <script>
    document.getElementById("overlay").style.display = "block";
  </script>
  <section id="rgpd" class="sticky-top m-0 p-0 accookie">
    <div class="row">
      <div class="col-md-3"></div>
      <div class="col-md-6">
        <div class="pt-5 display-3"><i class="fas fa-file-contract"></i></div>
        <div>'.$web[91].'</div>
      </div>
      <div class="col-md-3"></div>
      <div class="col-md-12 pt-3 pb-5 text-center">'.SubmitButton($web[629], 'setCookiesAccept();', '', '', 'button').'</div>
    </div>
  </section>
';
}
*/
if (empty($prt)) echo '
  <header id="slide1"> <!-- Begin of Total HEADER -->
    <div class="slide_inside">
';

// php or html scripts located in directory inc/inchead are automatically include at the top of the page
if (is_dir(CHEMIN.'inc/inchead')) { 
        $hd = opendir(CHEMIN.'inc/inchead');
        while ($file = readdir($hd)) { 
            if (1 == preg_match('!^[^_].*(\.inc|\.inc\.php)$!', $file)) {
                echo '
    <section id="header" class="row mx-0 px-0">
      <div class="col-md-12 mx-0 px-0">';
                include CHEMIN.'inc/inchead/'.$file;
                echo '
      </div>
    </section>
';
            }
        }
}

if (empty($prt)) {
    echo '
      <!-- Begin of TOP BOXES -->
      <a id="top"></a>
      <section id="TopBoxes" class="container-fluid mx-auto px-0">';
    foreach ($xposbox[$tconfig][BOX_TOP][BOX_LEFT] as $i => $aa) {
        $item4 = @ExecFunctionBox(BOX_TOP, $xposbox[$tconfig][BOX_TOP][BOX_LSIDE][$i]);
        $item1 = @ExecFunctionBox(BOX_TOP, $xposbox[$tconfig][BOX_TOP][BOX_LEFT][$i]);
        $item2 = @ExecFunctionBox(BOX_TOP, $xposbox[$tconfig][BOX_TOP][BOX_CENTER][$i]);
        $item3 = @ExecFunctionBox(BOX_TOP, $xposbox[$tconfig][BOX_TOP][BOX_RIGHT][$i]);
        $item5 = @ExecFunctionBox(BOX_TOP, $xposbox[$tconfig][BOX_TOP][BOX_RSIDE][$i]);
        if ($item4 || $item1 || $item2 || $item3 || $item5) {
            $w3    = (int)$wposbox[$tconfig][BOX_TOP][BOX_LSIDE][$i];
            $w0    = (int)$wposbox[$tconfig][BOX_TOP][BOX_LEFT][$i];
            $w1    = (int)$wposbox[$tconfig][BOX_TOP][BOX_CENTER][$i];
            $w2    = (int)$wposbox[$tconfig][BOX_TOP][BOX_RIGHT][$i];
            $w4    = (int)$wposbox[$tconfig][BOX_TOP][BOX_RSIDE][$i];
            $sfix  = BOX_TOP.$tconfig.$i;
            $wcol3 = 'col-md-'.($item4 === false ?  '0' : $w3).' mx-0 px-'.($item4 === false ?  '0' : '2').' wcol3'.$sfix;
            $wcol0 = 'col-md-'.($item1 === false ?  '0' : $w0).' mx-0 px-'.($item1 === false ?  '0' : '2').' wcol0'.$sfix;
            $wcol1 = 'col-md-'.($item2 === false ?  '0' : $w1).' mx-0 px-'.($item2 === false ?  '0' : '2').' wcol1'.$sfix;
            $wcol2 = 'col-md-'.($item3 === false ?  '0' : $w2).' mx-0 px-'.($item3 === false ?  '0' : '2').' wcol2'.$sfix;
            $wcol4 = 'col-md-'.($item5 === false ?  '0' : $w4).' mx-0 px-'.($item5 === false ?  '0' : '2').' wcol4'.$sfix;
			if (!isset($titre[78])) $titre[78] = '';
            echo '
        <style> #T'.$tconfig.'row'.$i.' { max-width: '.$titre[78].'; } </style>
        <div id="T'.$tconfig.'entrow'.$i.'" class="T'.$tconfig.'entrow'.$i.'">
          <div id="T'.$tconfig.'row'.$i.'" class="row mx-auto px-0 T'.$tconfig.'row'.$i.'">
            <div class="'.$wcol3.'">'.(FALSE !== $item4 ? $item4 : '').'</div>
            <div class="'.$wcol0.'">'.(FALSE !== $item1 ? $item1 : '').'</div>
            <div class="'.$wcol1.'">'.(FALSE !== $item2 ? $item2 : '').'</div>
            <div class="'.$wcol2.'">'.(FALSE !== $item3 ? $item3 : '').'</div>
            <div class="'.$wcol4.'">'.(FALSE !== $item5 ? $item5 : '').'</div>
          </div>
        </div>';
        }
    }

    echo '
      </section> <!-- End of TOP BOXES -->
';
    echo '
    </div> <!--.slide_inside-->	 
  </header> <!--#slide1--> <!-- end of Total HEADER -->
';
}
if (!isset($page[62])) $page[62] = '70,70,70,70,70,70';
if ($serviz[74] == '' || strip_tags($_SERVER['SCRIPT_NAME']) != '/index.php') {
    $pages62 = explode(',', $page[62]);
    $pages62[$tconfig] = empty($pages62[$tconfig]) ? 0 : $pages62[$tconfig];
    echo '
<style>.mainoffset'.$tconfig.' { padding-top: '.$pages62[$tconfig].'px !important; } </style>
';
}

if (empty($prt)) echo '
  <!-- Begin of MAIN CONTENT -->
  <a id="z2"></a>
  <main id="slide2" class="mainoffset'.$tconfig.'">
    <div class="slide_inside">
      <div id="MainContent" class="container-fluid mx-auto px-0">
        <div class="row mx-auto px-0">
';
    $colconf = isBlogPage() ? 'B'  : $tconfig;

    for ($ii=0; $ii < count($xposbox[$colconf][BOX_LEFT]); $ii++) {
        $box  = $xposbox[$colconf][BOX_LEFT][$ii];
        $temp = explode(CONNECTOR, $box);
        $fnct = isset($temp[1]) ? $temp[1] : '';
        $args = isset($temp[2]) ? $temp[2] : '';
        if (in_array($fnct, array('fbox_freebox', 'fbox_menubox', 'fbox_ddmenubox'))) {
            ReadDoc($args);
            $xposbox[$colconf][BOX_LEFT][$ii] = isAccessGranted($fieldmod) ? $box : '';
        }
    }
    for ($ii=0; $ii < count($xposbox[$colconf][BOX_RIGHT]); $ii++) {
        $box  = $xposbox[$colconf][BOX_RIGHT][$ii];
        $temp = explode(CONNECTOR, $box);
        $fnct = isset($temp[1]) ? $temp[1] : '';
        $args = isset($temp[2]) ? $temp[2] : '';
        if (in_array($fnct, array('fbox_freebox', 'fbox_menubox', 'fbox_ddmenubox'))) {
            ReadDoc($args);
            $xposbox[$colconf][BOX_RIGHT][$ii] = isAccessGranted($fieldmod) ? $box : '';
        }
    }
    
    $left_empty  = array_reduce($xposbox[$colconf][BOX_LEFT], 'EstNonVide', true) || $userprefs3 == BOX_RIGHT;
    $right_empty = array_reduce($xposbox[$colconf][BOX_RIGHT], 'EstNonVide', true) || $userprefs3 == BOX_LEFT;
    $userprefs3  = (isBlogPage() ? BOX_LEFT.BOX_RIGHT : $userprefs3);
    $userprefs3  = (isForumPage() ? ($forum[3] == '' ? $forum[4] : BOX_LEFT.BOX_RIGHT) : $userprefs3);

    if ($serviz[74] == '' || strip_tags($_SERVER['SCRIPT_NAME']) != '/index.php') {
        if (is_file($meskin.'leftside.inc')) {
            include($meskin.'leftside.inc');
        } else {
            if ($widepage == '' && $userprefs3 != BOX_RIGHT && !$left_empty) {
                echo '

        <!-- Begin of LEFT BOXES -->
        <aside id="'.(isBlogPage() ? 'Blog' : '').'LeftBoxes" class="col-md-3 mx-0 pl-0 pr-4 a-col">
          <div id="'.(isBlogPage() ? 'Blog' : '').'BoxesLeft"> <!-- hauteur des boîtes seules -->';

                foreach ($xposbox[$colconf][BOX_LEFT] as $ii => $aa) {
                    if ($userprefs3 != BOX_RIGHT && $xposbox[$colconf][BOX_LEFT][$ii] != '') {
                        $item = ExecFunctionBox(BOX_LEFT, $xposbox[$colconf][BOX_LEFT][$ii]);
                        if (FALSE !== $item) echo $item;
                    }
                    if ($userprefs3 == BOX_LEFT && $xposbox[$colconf][BOX_RIGHT][$ii] != '') {
                        $item = ExecFunctionBox(BOX_LEFT, $xposbox[$colconf][BOX_RIGHT][$ii]);
                        if (FALSE !== $item) echo $item;
                    }
                }

                echo '
          </div>
        </aside>  <!-- End of LEFT_BOXES -->
        ';
            }
        }
    }
    $lcb = 'col-md-'.(($left_empty && $right_empty) || ($serviz[74] == 'on' && strip_tags($_SERVER['SCRIPT_NAME']) == '/index.php') || !empty($widepage) 
      ? '12' : ($left_empty || $right_empty || (isForumPage() && $forum[3] == '') ? '9' : '6'));
    echo '
        <!-- Begin CENTER BOXES -->
        <section id="CenterBoxes" class="'.$lcb.' mx-auto px-1">
          <div id="BoxesCenter" class="row mx-0 px-0"> <!-- hauteur des boîtes seules -->
            <div class="col-12 mx-0 px-0">';
    
    $above_empty = array_reduce($xposbox[$tconfig][BOX_ABOVE], 'EstNonVide', true);
    if (!$above_empty) {
        echo '
            <!-- Begin of ABOVE BOXES -->
            <div id="AboveBoxes" class="row mx-0 px-0 '.BOX_ABOVE.$tconfig.'">
              <div id="BoxesAbove" class="col-12 mx-0 px-0">';
        foreach ($xposbox[$tconfig][BOX_ABOVE] as $item) {
            $bc = '';
            if ($item != '') {
                if (strpos($item, 'fbox_cita') > 0) { 
                    $bccount++; $bc = '||'.$bccount; 
                }
                $item = ExecFunctionBox(BOX_ABOVE, $item.$bc);
                if (FALSE !== $item) echo '
                <div class="row mx-0 px-0 '.BOX_ABOVE.'row'.$tconfig.'">
                  <div class="col-12 mx-0 px-0">
                    '.$item.'
                  </div>
                </div>';
            }
        }
            echo '
              </div>
            </div> <!-- End of ABOVE BOXES -->
      ';
    }
    	if (!OK_RGPD_date($userprefs[1])) 
		{
			setRGPDdate();
		}
?>
