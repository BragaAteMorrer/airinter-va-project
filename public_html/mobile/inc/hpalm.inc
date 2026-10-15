<?php
/*******************************************************************************
 *   Site Top of Page for Mobile
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
 * v6.00.11 (March 09, 2023) : correction test forum $dbcat, update bootstrap and font awesome
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'hpalm.inc')) {
    header('location:../index.php');
    die();
}

$toppage = stripslashes($site[($lang[0] == $lng ? 0 : 11)]);
if (($page[22]) && (!empty($topmess))) {
	$toppage .= ' - '.strip_tags($topmess);
}
echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">';

	///////////////////////////////////////////////////////////////////////////////////
	//                                                                               //
	//                GuppY is protected by the CeCILL FREE LICENSE.                 //
	//  REMOVING OR MODIFYING THE FOLLOWING COPYRIGHT NOTICE IS STRICTLY FORBIDDEN.  //
	//                                                                               //
	///////////////////////////////////////////////////////////////////////////////////

echo '

     <!--[  GuppY v'.MP_VERSION.' CeCILL Copyright (C) '.MP_COPYRIGHT.' by Laurent Duveau - https://www.freeguppy.org/  ]-->

<head>
<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'" />
<meta charset="'.$charset.'" />

<title>'.$toppage.'</title>

<meta name="description" content="'.$site[1].'" />
<meta name="generator" content="GuppY" />
<meta name="author" content="'.$site[9].'" />
<meta name="keywords" content="'.$site[2].'" />';

if (preg_match('!admin/|'.$site['PG'].'|newsrss|error!', $endurl) === false) {
    echo '
<meta name="Robots" content="'.$site[7].'" />
<meta name="Revisit-After" content="'.$site[8].'" />';
}
else {
    echo '
<meta name="Robots" content="NONE" />';
}
echo '
<meta name="reply-to" content="guppy@guppy.cms" />';
echo '
<link rel="shortcut icon" href="'.$site[3].'favicon.ico" />

<link rel="stylesheet" href="'.CHEMIN.'inc/unitegallery/css/unite-gallery.css" />
<link rel="stylesheet" href="'.CHEMIN.'inc/lang/flags.css" />
<link rel="stylesheet" href="'.CHEMIN.'inc/img/avatars/'.$page[23].'.css" />
<link rel="stylesheet" href="'.CHEMIN.'inc/img/general/forum.css" />
<link rel="stylesheet" href="'.CHEMIN.'inc/img/general/general.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.18.4/styles/default.min.css" 
  integrity="sha512-h0/Zh3Xv/rDANm6S9yRPeNMHQVIy3f0VcvLGfJFeEOibLcHDadiHyqlb2+FjiwDLIGwluKRhxh1cqUYpGoj/yw==" crossorigin="anonymous" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/SocialIcons/1.0.1/soc.min.css" 
  integrity="sha512-PTz/4lAo890ortUEd041dNdebPVxpjxZiTTgW8DXUTIiPZQGXFua9U7izCygP7NqHUDmaDF4F1CswmblvYq4Vw==" crossorigin="anonymous" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" 
  integrity="sha512-c42qTSw/wPZ3/5LBzD+Bw5f7bSF2oxou6wEb+I/lqeaKV5FDIfMvvRp772y4jcJLKuGUOpbJMdg/BTl50fJYAw==" crossorigin="anonymous" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css" 
  integrity="sha512-rt/SrQ4UNIaGfDyEXZtNcyWvQeOq0QLygHluFQcSjaGB04IxWhal71tKuzP6K8eYXYB6vJV4pHkXcmFGGQ1/0w==" crossorigin="anonymous" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" 
  integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" 
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
';

// php or html scripts located in directory inc/inchead are automatically include at the top of the page
if (is_dir(CHEMIN.'mobile/inc/inchead')) { 
	$hd = opendir(CHEMIN.'mobile/inc/inchead');
	while ($file = readdir($hd)) { 
        if (1 == preg_match('!^[^_].*(\.inc|\.inc\.php)$!', $file)) {
			include CHEMIN.'mobile/inc/inchead/'.$file;
		}
	}
}

echo '
</head>
<body>';
if ($page[4] != "") {
    echo '
<div style="text-align:center;">
  <a href="index.php?lng='.$lng.'">
    <img src="'.CHEMIN.'img/'.mb_substr($page[4], mb_strpos($page[4], '|') + 1).'" style=" vertical-align:middle;" alt="'.stripslashes($site[($lang[0] == $lng ? 0 : 11)]).'" />
  </a>
</div>
<br>';
}
if ($lng == $lang[0]) {
  $i = 0;
  $j = 0;
}
else {
  $i = 10;
  $j = 1;
}

$endurl = str_replace('&', '&amp;', $endurl);

if ($lang[1] != '') {
    if (substr_count($endurl,'?lng=') == 0) {
        $endurl0 = '?lng='.$lang[0];
        $endurl1 = '?lng='.$lang[1];
    }
    else {
        $endurl0 = str_replace('?lng='.$lang[1],'?lng='.$lang[0],$endurl);
        $endurl1 = str_replace('?lng='.$lang[0],'?lng='.$lang[1],$endurl);
    }
    if (substr_count($endurl,'articles.php') != 0 && substr_count($endurl,'&amp;pg=') == 0) {
        $endurl0 = $endurl0.'&amp;pg='.$pg;
        $endurl1 = $endurl1.'&amp;pg='.$pg;
    }
    if ($lng == $lang[0]) {
        echo '
<a href="'.$endurl1.'" title="['.$lang[1].']">'.$flag2.'</a>&nbsp;';
    } else {
        echo '
<a href="'.$endurl0.'" title="['.$lang[0].']">'.$flag1.'</a>&nbsp;';
    }
}
$nom[$i] = strip_tags($nom[$i]);
echo '
<a class="menu" href="'.CHEMIN.'mobile/index.php?lng='.$lng.'" title="'.getLabel($nom[$i], 1).'">';
if ($page[9] != "") {
    echo '
  <img src="'.CHEMIN.'inc/img/icons/'.$page[9].'/home.gif" alt="'.getLabel($nom[$i]).'" style="width:32px;height:32px;vertical-align:middle;" /></a>&nbsp;';
}
else {
  echo getLabel($nom[$i]).'</a>&nbsp;';
}
if ($serviz[53] == 'on') {
    $nom[$j+42] = strip_tags($nom[$j+42]);
    echo '
  <a href="blogs.php?lng='.$lng.'" title="'.getLabel($nom[$j+42], 1).'">';
    if ($page[9] != "") {
        echo '
    <img src="'.CHEMIN.'inc/img/icons/'.$page[9].'/blog.gif" alt="'.getLabel($nom[$j+42]).'" style="width:32px;height:32px;vertical-align:middle;" /></a>&nbsp;';
    }
    else {
        echo getLabel($nom[$j+42]).'</a>&nbsp;';
    }
}
if ($serviz[8] == 'on') {
    $nom[$i+7] = strip_tags($nom[$i+7]);
    echo '
  <a href="news.php?lng='.$lng.'" title="'.getLabel($nom[$i+7], 1).'">';
    if ($page[9] != '') {
        echo '
    <img src="'.CHEMIN.'inc/img/icons/'.$page[9].'/news.gif" alt="'.getLabel($nom[$i+7]).'" style="width:32px;height:32px;vertical-align:middle;" /></a>&nbsp;';
    }
    else {
        echo getLabel($nom[$i+7]).'</a>&nbsp;';
    }
}
if ($serviz[10] == 'on') {
    $nom[$i+1] = strip_tags($nom[$i+1]);
    echo '
  <a href="download.php?lng='.$lng.'" title="'.getLabel($nom[$i+1], 1).'">';
    if ($page[9] != '') {
        echo '
    <img src="'.CHEMIN.'inc/img/icons/'.$page[9].'/download.gif" alt="'.getLabel($nom[$i+1]).'" style="width:32px;height:32px;vertical-align:middle;" /></a>&nbsp;';
    }
    else {
        echo getLabel($nom[$i+1]).'</a>&nbsp;';
    }
}
if ($serviz[11] == 'on') {
    $nom[$i+3] = strip_tags($nom[$i+3]);
    echo '
  <a href="links.php?lng='.$lng.'" title="'.getLabel($nom[$i+3], 1).'">';
    if ($page[9] != '') {
        echo '
    <img src="'.CHEMIN.'inc/img/icons/'.$page[9].'/links.gif" alt="'.getLabel($nom[$i+3]).'" style="width:32px;height:32px;vertical-align:middle;" /></a>&nbsp;';
    }
    else {
        echo getLabel($nom[$i+3]).'</a>&nbsp;';
    }
}
if ($serviz[14] == 'on') {
    $nom[$j+24] = strip_tags($nom[$j+24]);
    echo '
  <a href="faq.php?lng='.$lng.'" title="'.getLabel($nom[$j+24], 1).'">
  ';
    if ($page[9] != '') {
        echo '
    <img src="'.CHEMIN.'inc/img/icons/'.$page[9].'/faq.gif" alt="'.getLabel($nom[$j+24]).'" style="width:32px;height:32px;vertical-align:middle;" /></a>&nbsp;
    ';
    }
    else {
        echo getLabel($nom[$j+24]).'</a>&nbsp;';
    }
}
if ($serviz[12] == 'on') {
    $nom[$i+9] = strip_tags($nom[$i+9]);
    echo '
  <a href="guestbk.php?lng='.$lng.'" title="'.getLabel($nom[$i+9], 1).'">
  ';
    if ($page[9] != '') {
        echo '
    <img src="'.CHEMIN.'inc/img/icons/'.$page[9].'/guestbook.gif" alt="'.getLabel($nom[$i+9]).'" style="width:32px;height:32px;vertical-align:middle;" /></a>&nbsp;
    ';
    }
    else {
        echo getLabel($nom[$i+9]).'</a>&nbsp;';
    }
}
$dbcat = array();
$okfr  = false;
if ($serviz[13] == 'on')
{
	$dbcat = ReadDBFields(DBFORUMCAT);

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
}
if ($serviz[13] == 'on' && $okfr) {
    $nom[$j+22] = strip_tags($nom[$j+22]);
    echo '
  <a href="fortopic.php?lng='.$lng.'" title="'.getLabel($nom[$j+22], 1).'">
  ';
    if ($page[9] != '') {
        echo '
      <img src="'.CHEMIN.'inc/img/icons/'.$page[9].'/forum.gif" alt="'.getLabel($nom[$j+22]).'" style="width:32px;height:32px;vertical-align:middle;" /></a>
    ';
    }
    else {
        echo getLabel($nom[$j+22]).'</a>';
    }
}
echo '
<hr />';
?>
