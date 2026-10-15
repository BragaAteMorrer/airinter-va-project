<?php
/*******************************************************************************
 *   Admin CSS Generator
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2023 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaim√©
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.19 (August 15, 2023) : Correction secondary page, Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'config8.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[43] == "") {
        $nomzone = $admin[719];
        include 'access.inc';
        exit();
    }
}

$regit      = import('regit');
$corit      = import('corit');
$mycss      = import('mycss');
$cssval     = import('cssval');
$bs4cssval  = import('bs4cssval');
$mobcssval  = import('mobcssval');
$pluscssval = import('pluscssval');
$fontcssval = import('fontcssval');
$selskin    = import('selskin');
$active     = 1;

if ($corit == 1) {
    $mobcssval = str_replace('\'', '"', $mobcssval);
    $mobcssval = trim(stripslashes($mobcssval));
    WriteFullDB(CHEMIN.'skins/'.$mycss.'/mobstyle.css', $mobcssval);
    $cor_mobcss = 'ok';
	$active     = 1;
}

if ($corit == 2) {
    $pluscssval = str_replace('\'', '"', $pluscssval);
    $pluscssval = trim(stripslashes($pluscssval));
    WriteFullDB(CHEMIN.'skins/'.$mycss.'/styleplus.css', $pluscssval);
    $cor_cssplus = 'ok';
	$active      = 2;
}
$nbrekeypastrouve = 0;
$nbrekeytrouve = 0;
for ($i = 1; $i <= count($templates); $i++) {

	if (!isset($templates[$i]))
	{
		$nbrekeypastrouve ++;
	}
}
$nbrekeylast = array_key_last($templates);
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d_H');
$nomficlog = CHEMIN.'data/log/debugconfig6'.'_'.$dateheureminiutesecl.'.log';
for ($i = 1; $i <= $nbrekeylast+$nbrekeypastrouve; $i++) {
    if ($corit == (2 + $i)) {
        $cssvalpage = import('cssvalpage'.$i);
        $cssvalpage = str_replace('\'', '"', $cssvalpage);
        $cssvalpage = trim(stripslashes($cssvalpage));
//   file_put_contents(($nomficlog, ' Config8 write css: '.$cssvalpage."\n", FILE_APPEND | LOCK_EX);
        WriteFullDB(CHEMIN.'skins/'.$mycss.'/stylepage'.$i.'.css', $cssvalpage);
        $active = 2 + $i;
    }
}

if ($corit == count($templates) + 2) {
    $fontcssval = str_replace('\'', '"', $fontcssval);
    $fontcssval = trim(stripslashes($fontcssval));
    WriteFullDB(CHEMIN.'skins/'.$mycss.'/font/fontstyle.css', $fontcssval);
    $cor_cssfont = 'ok';
	$active      = count($templates) + 2;
}

if (!FileDBExist($meskin."styleplus.css")) {
    $pluscssval = '/*  ---------  CSS to customize '.$selskin.'  ----------- */';
    WriteFullDB($meskin.'styleplus.css', $pluscssval);
}
for ($i = 1; $i <= $nbrekeylast+$nbrekeypastrouve; $i++) {
	if (isset($templates[$i]))
	{
		if (!is_file($meskin.'stylepage'.$i.'.css')) {
			$csstitle = '/*  ---------  CSS to customize secondary page '.$templates[$i].'  ----------- */';
			WriteFullDB($meskin.'stylepage'.$i.'.css', $csstitle);
		}
	}
}

$headinc .= '
<script>
$(document).ready( function() {
    $("#gyaccordion").hoverAccordion({
        keepHeight: false,
        activateItem: '.$active.',
        onClickOnly: true,
        speed: 400
    });
    $("#gyaccordion").children("li:first").addClass("firstitem");
    $("#gyaccordion").children("li:last").addClass("lastitem");
});
</script>
<link rel="stylesheet" href="inc/codemirror/codemirror.css">
<link rel="stylesheet" href="inc/codemirror/simplescrollbars.css">
<script src="inc/codemirror/codemirror.js"></script>
<script src="inc/codemirror/css.js"></script>
<script src="inc/codemirror/simplescrollbars.js"></script>
';
$headinc .= "
<style>
#gyaccordion {
    width: auto;
    height: auto;
    list-style-type: none;
    background-color: inherit;
    color: inherit;
}
#gyaccordion a {
    display: block;
    font-weight: bold;
    text-decoration: none;
}
#gyaccordion ul {
    overflow: hidden;
    margin: 0;
    padding: 0;
}
#gyaccordion li {
    margin: auto;
    list-style-type: none;
    height:auto;
}
#gyaccordion li a {
    width: auto;
    height: auto;
    padding: 8px;
    display: block;
    color: #FFFFFF;
}
#gyaccordion li a.closed {
    color: #000000 !important;
    background-color: #DCD9E0;
    border: 1px solid #67890B;
	border-radius: 4px;
    margin: 4px 0;
}
#gyaccordion li.firstitem a.closed {
    color: #000000 !important;
    background-color: #DCD9E0;
    border: 1px solid #67890B;
	border-radius: 4px;
}
#gyaccordion li.lastitem a.closed {
    color: #000000 !important;
    background-color: #DCD9E0;
    border: 1px solid #67890B;
	border-radius: 4px;
}
#gyaccordion li li {
    width: auto;
    height: auto;
    margin: auto;
    line-height: 28px;
    border: 1px solid #67890B;
}
#gyaccordion li a.opened {
    color: #FFFFFF;
    font-weight: bold;
    background-color: #7F7F7F;
    border: 1px solid #67890B;
	border-radius: 4px;
    margin: 4px 0;
}
#gyaccordion li.firstitem a.opened {
    color: #FFFFFF;
    font-weight: bold;
    background-color: #7F7F7F;
    border: 1px solid #67890B;
	border-radius: 4px;
    margin: 4px 0;
}
#gyaccordion li.lastitem li {
    margin: 4px 0 12px 0;
}
#gyaccordion li.lastitem ul {
    padding-bottom: 4px;
    margin: 0;
}
</style>
<script>
(function($) {
    $.fn.hoverAccordion = function(options) {
        options = jQuery.extend({
            speed : 'fast',
            activateItem : true,
            keepHeight : false,
            onClickOnly : false,
            classActive : 'active',
            classHeader : 'header',
            classHover : 'hover',
            classOpen : 'opened',
            classClosed : 'closed'
        }, options);
        var container = this;
        var pageHref = window.location.href;
        var i = 0;
        function doHover(itemList, itemHeader, listHeight) {
            var oldList = $(container).find('.' + options.classOpen).closest(
            'li').find('ul:first');
            if (false === oldList.is(':animated')) {
                if (options.keepHeight == true) {
                    listHeight = maxHeight;
                }
                if (itemHeader.hasClass(options.classOpen) == false) {
                    itemList.children().show();
                    itemList.animate({
                        height : listHeight
                    }, {
                        step : function(n, fx) {
                            itemList.height(listHeight - n);
                        },
                        duration : options.speed
                    });
                    oldList.animate({
                        height : 0
                    }, {
                        step : function(n, fx) {
                            itemList.height(listHeight - n);
                        },
                        duration : options.speed
                    }).children().hide();
                    itemHeader.addClass(options.classOpen).removeClass(
                    options.classClosed);
                    oldList.closest('li').removeClass(options.classActive)
                    .find('a:first').addClass(options.classClosed).removeClass(
                    options.classOpen);
                }
            }
        }
        var itemNo = 0;
        var maxHeight = 0;
        $(this)
        .children('li')
        .each(
        function() {
            var item = $(this);
            var isActive = false;
            itemNo++;
            var itemHeader = item.find('a:first').addClass(options.classHeader);
            if (itemHeader.length > 0) {
                itemHeader.hover(function() {
                    itemHeader.addClass(options.classHover);
                }, function() {
                    itemHeader.removeClass(options.classHover);
                });
                var itemHref = itemHeader.attr('href');
                if (itemHref == '#') {
                    itemHeader.click(function() {
                        this.blur();
                        return false;
                    });
                } else if (options.activateItem == true
                && pageHref.indexOf(itemHref) > 0
                && pageHref.length - pageHref.lastIndexOf(itemHref) == itemHref.length) {
                    isActive = true;
                    item.addClass(options.classActive);
                    itemHeader.removeClass(options.classClosed).addClass(
                    options.classOpen);
                }
            }
            var itemList = item.find('ul:first');
            if (itemList.length > 0) {
                var listHeight = itemList.height();
                if (maxHeight < listHeight)
                    maxHeight = listHeight;
                if (options.onClickOnly == true) {
                    itemHeader.click(function() {
                        doHover(itemList, itemHeader, listHeight);
                    });
                } else {
                    itemHeader.hover(function() {
                        i = setInterval(function() {
                            doHover(itemList, itemHeader, listHeight);
                            clearInterval(i);
                        }, 400);
                    }, function() {
                        clearInterval(i);
                    });
                }
                if (options.activateItem === true) {
                    itemList
                    .children('li')
                    .each(
                    function() {
                        var m = $(this).find('a').attr('href');
                        if (m) {
                            if (pageHref.indexOf(m) > 0
                            && pageHref.length - pageHref.lastIndexOf(m) == m.length) {
                                isActive = true;
                                item.addClass(options.classActive);
                                itemHeader.removeClass(options.classClosed)
                                .addClass(options.classOpen);
                            }
                        }
                    });
                } else if (parseInt(options.activateItem, 10) == itemNo) {
                    isActive = true;
                    item.addClass(options.classActive);
                    itemHeader.removeClass(options.classClosed).addClass(
                    options.classOpen);
                }
            }
            if (!isActive) {
                itemHeader.removeClass(options.classOpen);
                if (itemList.length > 0) {
                    itemList.children().hide();
                    itemHeader.addClass(options.classClosed);
                }
            }
        });
        return this;
    };
})(jQuery);
</script>
";

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[720]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[720].' => '.$selskin, '100%');

echo '
<div style="width:98%; margin:auto; text-align:center;">'.ToHelp($lng, '&pg=974&mnuid=937&tconfig=0#z2');

if (isset($selskin)) $skn = '&amp;selskin='.$selskin;
echo '
  <p>&nbsp;</p>
  <p class="notice">'.$admin[887].'</p>';
if (isset($put_css)) {
	echo '
  <br /><span class="quest">'.$admin[721].' <b>&nbsp;'.$mycss.'</b></span><br />';
}
$meskin = CHEMIN."skins/".$selskin."/";

echo '
  <ul id="gyaccordion" style="max-width:1240px; margin:auto;">';

if (FileDBExist($meskin."mobstyle.css")) {
	if (isset($cor_mobcss)) {
		$titre = $admin[728].'(RD mobile)';
	}
	else {
		$titre = $admin[144]." ".$admin[729].'(RD mobile)';
	}
	echo '
    <a id="topmobstyle"></a>
    <li>
      <a href="#">'.$titre.'</a>
      <ul>
	    <li>
		  <form name="modmobcss" action="admin.php?lng='.$lng.'&amp;pg=config8#topmobstyle" method="post">';
	if ($selskin != 'no_skin')
		echo '
			<div class="quest" style="margin:0 8px;padding:2px 20px;">
			  <div style="float:right">'.SubmitButton($admin[144].' '.$admin[422], '', '', $imgdroite).'</div>
			  <div style="clear:both"></div>
			</div>';
	echo '
		    <input type="hidden" name="corit" value="1" />
		    <input type="hidden" name="selskin" value="'.$selskin.'" />
		    <input type="hidden" name="mycss" value="'.$selskin.'" />
		    <div style="margin:auto;text-align:left;">
			  <textarea name="mobcssval" id="mobcssval" rows="32" style="width:100%;">';
	$cssfile = fopen($meskin."mobstyle.css", "r");
	while (!feof($cssfile)) {
		$cssline = fgets($cssfile, 255);
		echo $cssline;
	}
	fclose($cssfile);
	echo '
			  </textarea>
		    </div>';
	if ($selskin != 'no_skin')
		echo '
			<div class="quest" style="margin:0 8px;padding:2px 20px;">
			  <div style="float:right">'.SubmitButton($admin[144].' '.$admin[422], '', '', $imgdroite).'</div>
			  <div style="clear:both"></div>
			</div>';
	echo '
		  </form>
          <script>
            var editor = CodeMirror.fromTextArea(document.getElementById("mobcssval"), {        
              lineNumbers: true,
              scrollbarStyle: "native"
            });
          </script>
        </li>
	  </ul>
    </li>';
}

if (FileDBExist($meskin."styleplus.css")) {
	if (isset($cor_cssplus)) {
		$titre = $admin[728].'(styleplus)';
	}
	else {
		$titre = $admin[144]." ".$admin[1500].'(styleplus)';
	}
	echo '
    <a id="topstyleplus"></a>
    <li>
      <a href="#">'.$titre.'</a>
      <ul>
	    <li>
		  <form name="modcssplus" action="admin.php?lng='.$lng.'&amp;pg=config8#topstyleplus" method="post">';
	if ($selskin != 'no_skin')
		echo '
			<div class="quest" style="margin:0 8px;padding:2px 20px;">
			  <div style="float:right">'.SubmitButton($admin[144].' '.$admin[422], '', '', $imgdroite).'</div>
			  <div style="clear:both"></div>
			</div>';
	echo '
			<input type="hidden" name="corit" value="2" />
			<input type="hidden" name="selskin" value="'.$selskin.'" />
			<input type="hidden" name="mycss" value="'.$selskin.'" />
			<div style="margin:auto;text-align:left;">
			  <textarea name="pluscssval" id="pluscssval" rows="32" style="width:100%;">';
	$cssfile = fopen($meskin."styleplus.css", "r");
	while (!feof($cssfile)) {
		$cssline = fgets($cssfile, 255);
		echo $cssline;
	}
	fclose($cssfile);
	echo '
			  </textarea>
			</div>';
	if ($selskin != 'no_skin')
		echo '
			<div class="quest" style="margin:0 8px;padding:2px 20px;">
			  <div style="float:right">'.SubmitButton($admin[144].' '.$admin[422], '', '', $imgdroite).'</div>
			  <div style="clear:both"></div>
			</div>';
	echo '
		  </form>
          <script>
            var editor = CodeMirror.fromTextArea(document.getElementById("pluscssval"), {        
              lineNumbers: true,
              scrollbarStyle: "native"
            });
          </script>
        </li>
	  </ul>
    </li>';
}
$nbrekeypastrouve = 0;
$nbrekeytrouve = 0;
for ($i = 1; $i <= count($templates); $i++) {

    if (!isset($templates[$i]))
	{
		$nbrekeypastrouve ++;					  
	}
	else
		$nbrekeytrouve++;				   

}
$nbrekeylast = array_key_last($templates);
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d_H');
$nomficlog = CHEMIN.'data/log/debugconfig6'.'_'.$dateheureminiutesecl.'.log';
//   file_put_contents(($nomficlog, ' Config2 nbrekeypastrouve: '.$nbrekeypastrouve."\n", FILE_APPEND | LOCK_EX);
//   file_put_contents(($nomficlog, ' Config2 nbrekeytrouve: '.$nbrekeytrouve."\n", FILE_APPEND | LOCK_EX);
//   file_put_contents(($nomficlog, ' Config2 keylast: '.$nbrekeylast."\n", FILE_APPEND | LOCK_EX);
//   file_put_contents(($nomficlog, ' Config2 count: '.count($templates)."\n", FILE_APPEND | LOCK_EX);
for ($i = 1; $i <= $nbrekeylast+$nbrekeypastrouve; $i++) {
	if (isset($templates[$i]))
	{
		if (FileDBExist($meskin.'stylepage'.$i.'.css')) {
			$titre = $admin[144]." ".$admin[1500].'(stylepage'.$i.')';
			echo '
		<a name="toppage'.$i.'"></a>
		<li>
		  <a href="#">'.$titre.'</a>
		  <ul>
			<li>
			  <form name="modcsspage'.$i.'" action="admin.php?lng='.$lng.'&amp;pg=config8#toppage'.$i.'" method="post">';
			if ($selskin != 'no_skin')
				echo '
				<div class="quest" style="margin:0 8px;padding:2px 20px;">
				  <div style="float:right">'.SubmitButton($admin[144].' '.$admin[422], '', '', $imgdroite).'</div>
				  <div style="clear:both"></div>
				</div>';
			echo '
				<input type="hidden" name="corit" value="'.(2 + $i).'" />
				<input type="hidden" name="selskin" value="'.$selskin.'" />
				<input type="hidden" name="mycss" value="'.$selskin.'" />
				<div style="margin:auto;text-align:left;">
				  <textarea name="cssvalpage'.$i.'" id="cssvalpage'.$i.'" rows="32" style="width:100%;">';
			$cssfile = fopen($meskin.'stylepage'.$i.'.css', 'r');
			while (!feof($cssfile)) {
				$cssline = fgets($cssfile, 255);
				echo $cssline;
			}
			fclose($cssfile);
			echo '
				  </textarea>
				</div>';
			if ($selskin != 'no_skin')
				echo '
				<div class="quest" style="margin:0 8px;padding:2px 20px;">
				  <div style="float:right">'.SubmitButton($admin[144].' '.$admin[422], '', '', $imgdroite).'</div>
				  <div style="clear:both"></div>
				</div>';
			echo '
			  </form>
			  <script>
				var editor = CodeMirror.fromTextArea(document.getElementById("cssvalpage'.$i.'"), {        
				  lineNumbers: true,
				  scrollbarStyle: "native"
				});
			  </script>
			</li>
		  </ul>
		</li>';
		}
	}
}

if (FileDBExist($meskin."font/fontstyle.css")) {
	if (isset($cor_cssfont)) {
		$titre = $admin[728].'(fontstyle)';
	}
	else {
		$titre = $admin[144].' '.$admin[1535].'(fontstyle)';
	}
	echo '
    <a id="topfontstyle"></a>
    <li>
      <a href="#">'.$titre.'</a>
      <ul>
	    <li>
		  <form name="modcssfont" action="admin.php?lng='.$lng.'&amp;pg=config8#topfontstyle" method="post">';
	if ($selskin != 'no_skin')
		echo '
			<div class="quest" style="margin:0 8px;padding:2px 20px;">
			  <div style="float:right">'.SubmitButton($admin[144].' '.$admin[422], '', '', $imgdroite).'</div>
			  <div style="clear:both"></div>
			</div>';
	echo '
			<input type="hidden" name="corit" value="'.(count($templates) + 2).'" />
			<input type="hidden" name="selskin" value="'.$selskin.'" />
			<input type="hidden" name="mycss" value="'.$selskin.'" />
			<div style="margin:auto;text-align:left;">
			  <textarea name="fontcssval" id="fontcssval" rows="32" style="width:100%;">';
	$cssfile = fopen($meskin."font/fontstyle.css", "r");
	while (!feof($cssfile)) {
		$cssline = fgets($cssfile, 255);
		echo $cssline;
	}
	fclose($cssfile);
	echo '
			  </textarea>
			</div>';
	if ($selskin != 'no_skin')
		echo '
			<div class="quest" style="margin:0 8px;padding:2px 20px;">
			  <div style="float:right">'.SubmitButton($admin[144].' '.$admin[422], '', '', $imgdroite).'</div>
			  <div style="clear:both"></div>
			</div>';
	echo '
		  </form>
          <script>
            var editor = CodeMirror.fromTextArea(document.getElementById("fontcssval"), {        
              lineNumbers: true,
              scrollbarStyle: "native"
            });
          </script>
        </li>
	  </ul>
    </li>';
}

if (FileDBExist($meskin."style.css")) {
	echo '
    <li>
      <a href="#">'.$admin[729].'</a>
      <ul>
	    <li>
		  <div style="margin:auto;text-align:left;">
			<textarea name="cssval" id="cssval" rows="32" style="width:100%;text-align:left;">';/*720px*/
	$cssfile = fopen($meskin."style.css", "r");
	while (!feof($cssfile)) {
		$cssline = fgets($cssfile, 255);
		echo $cssline;
	}
	fclose($cssfile);
	echo '
			</textarea>
		  </div>
          <script>
            var editor = CodeMirror.fromTextArea(document.getElementById("cssval"), {        
              lineNumbers: true,
              scrollbarStyle: "native"
            });
          </script>
        </li>
	  </ul>
    </li>';
}

if (FileDBExist($meskin."bs4style.css")) {
	echo '
    <a name="topbs4style"></a>
    <li>
      <a href="#">'.$admin[729].'(Bootstrap 4)</a>
      <ul>
	    <li>
		  <div style="margin:auto;text-align:left;">
			<textarea name="bs4cssval" id="bs4cssval" rows="32" style="width:100%;">';
	$cssfile = fopen($meskin."bs4style.css", "r");
	while (!feof($cssfile)) {
		$cssline = fgets($cssfile, 255);
		echo $cssline;
	}
	fclose($cssfile);
	echo '
			</textarea>
		  </div>
          <script>
            var editor = CodeMirror.fromTextArea(document.getElementById("bs4cssval"), {        
              lineNumbers: true,
              scrollbarStyle: "native"
            });
          </script>
        </li>
	  </ul>
    </li>';
}

echo '
  </ul>';

if (isset($selskin)) $skn = "&amp;selskin=".$selskin;
echo '
</div>';
btable();
include CHEMIN.'admin/bpage.inc';
?>
