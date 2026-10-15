<?php
/*******************************************************************************
 *   Admin modification personalized lang file
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
 * v6.01.00 (September 22, 2022) : Initial  release
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'configlang.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[43] == "") {
        $nomzone = $admin[957];
        include 'access.inc';
        exit();
    }
}

$regit      = import('regit');
$corit      = import('corit');
$myfichierlang                 = import('myfichierlang');
$modcontenuadmin               = import('modcontenuadmin');
$modcontenuadminplus           = import('modcontenuadminplus');
$modcontenuweb                 = import('modcontenuweb');
$modcontenuwebplus             = import('modcontenuwebplus');
$modcontenuadmin1              = import('modcontenuadmin1');
$modcontenuadminplus1          = import('modcontenuadminplus1');
$modcontenuweb1                = import('modcontenuweb1');
$modcontenuwebplus1            = import('modcontenuwebplus1');

$active     = 1;
$mesfichierslangs = CHEMIN.'inc/lang/';

if ($corit == 1) {
    $modcontenuadminplus = trim(stripslashes($modcontenuadminplus));
	$complmtmodcontenuadminplus = str_replace('***infophpnepassupprimer','<?php', $modcontenuadminplus);
    WriteFullDB($mesfichierslangs.$lang[0].'-admplus.inc', $complmtmodcontenuadminplus);
	$active      = 2;
}
if ($corit == 2) {
    $modcontenuwebplus = trim(stripslashes($modcontenuwebplus));
	$complmtmodcontenuwebplus = str_replace('***infophpnepassupprimer','<?php', $modcontenuwebplus);
    WriteFullDB($mesfichierslangs.$lang[0].'-plus.inc', $complmtmodcontenuwebplus);
	$active      = 4;
}
if ($corit == 3) {
    $modcontenuadminplus1 = trim(stripslashes($modcontenuadminplus1));
	$complmtmodcontenuadminplus1 = str_replace('***infophpnepassupprimer','<?php', $modcontenuadminplus1);
    WriteFullDB($mesfichierslangs.$lang[1].'-adminplus.inc', $complmtmodcontenuadminplus1);
	$active      = 6;
}
if ($corit == 4) {
    $modcontenuwebplus1 = trim(stripslashes($modcontenuwebplus1));
	$complmtmodcontenuwebplus1 = str_replace('***infophpnepassupprimer','<?php', $modcontenuwebplus1);
    WriteFullDB($mesfichierslangs.$lang[1].'-plus.inc', $complmtmodcontenuwebplus1);
	$active      = 8;
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

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[957]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($topmess, '100%');

$avertissement = $admin[1869].$admin[957].' => '.$lang[0];
if ($lang[1] != '')
	$avertissement .= $admin[961].$lang[1];
echo '
  <p class="notice" align="center">'.$avertissement.'</p>';

$mesfichierslangs = CHEMIN."inc/lang/";

echo '
  <ul id="gyaccordion" style="max-width:1240px; margin:auto;">';

if (FileDBExist($mesfichierslangs.$lang[0].'-admin.inc')) {
		$titre = $admin[959]." ".$admin[958].' '.$lang[0].'-admin.inc';
		$titre .= $admin[831];
	echo '
    <a id="topmobstyle"></a>
    <li>
      <a href="#">'.$titre.'</a>
      <ul>
	    <li>
		  <form name="modmobcss" action="admin.php?lng='.$lng.'&amp;pg=configlang#topmobstyle" method="post">';
	echo '    
		    <input type="hidden" name="myfichierlang" value="'.$lang[0].'-admin" />
		    <div style="margin:auto;text-align:left;">
			  <textarea name="modcontenuadmin" id="modcontenuadmin" rows="32" style="width:100%;">';
	$fichierlangfile = fopen($mesfichierslangs.$lang[0].'-admin.inc', 'r');
	while (!feof($fichierlangfile)) {
		$fichierlangline = fgets($fichierlangfile, 255);
		echo $fichierlangline;
	}
	fclose($fichierlangfile);
	echo '
			  </textarea>
		    </div>';
	echo '
		  </form>
          <script>
				var editor = CodeMirror.fromTextArea(document.getElementById("modcontenuadmin"), {        
			  lineNumbers: true,
			  readOnly: true,
			  scrollbarStyle: "native"
            });
          </script>
        </li>
	  </ul>
    </li>';
}
if (FileDBExist($mesfichierslangs.$lang[0].'-admplus.inc')) {
		$titre = $admin[144].' '.$admin[958].' '.$lang[0].'-admplus.inc';
	echo '
    <a id="topmobstyle"></a>
    <li>
      <a href="#">'.$titre.'</a>
      <ul>
	    <li>
		  <form name="modmobcss" action="admin.php?lng='.$lng.'&amp;pg=configlang#topmobstyle" method="post">';
	echo '
			<div class="quest" style="margin:0 8px;padding:2px 20px;">
			  <div style="float:right">'.SubmitButton($admin[144].' '.$admin[958], '', '', $imgdroite).'</div>
			  <div style="clear:both"></div>
			</div>';
	echo '
		    <input type="hidden" name="corit" value="1" />		    
		    <input type="hidden" name="myfichierlang" value="'.$lang[0].'-admplus" />
			<div style="margin:auto;text-align:left;">
					<textarea name="modcontenuadminplus" id="modcontenuadminplus" rows="32" style="width:100%;">';
	$fichierlangfile = fopen($mesfichierslangs.$lang[0].'-admplus.inc', 'r');
	while (!feof($fichierlangfile)) {
		$fichierlangline = fgets($fichierlangfile, 255);
		if (str_contains($fichierlangline,'<?php'))
		{
			$fichierlangline = str_replace('<?php','***infophpnepassupprimer', $fichierlangline);
		}
		echo $fichierlangline;
	}
	fclose($fichierlangfile);
	echo '
			  </textarea>
		    </div>';
	echo '
			<div class="quest" style="margin:0 8px;padding:2px 20px;">
			  <div style="float:right">'.SubmitButton($admin[144].' '.$admin[958], '', '', $imgdroite).'</div>
			  <div style="clear:both"></div>
			</div>';
	echo '
		  </form>
          <script>
				var editor = CodeMirror.fromTextArea(document.getElementById("modcontenuadminplus"), {        
			  lineNumbers: true,
			  mode: "javascript",
			  scrollbarStyle: "native"
            });
          </script>
        </li>
	  </ul>
    </li>';
}
if (FileDBExist($mesfichierslangs.$lang[0].'-web.inc')) {
		$titre = $admin[959]." ".$admin[958].' '.$lang[0].'-web.inc';
		$titre .= $admin[831];
	echo '
    <a id="topmobstyle"></a>
    <li>
      <a href="#">'.$titre.'</a>
      <ul>
	    <li>
		  <form name="modmobcss" action="admin.php?lng='.$lng.'&amp;pg=configlang#topmobstyle" method="post">';
	echo '    
		    <input type="hidden" name="myfichierlang" value="'.$lang[0].'-web" />
		    <div style="margin:auto;text-align:left;">
			  <textarea name="modcontenuweb" id="modcontenuweb" rows="32" style="width:100%;">';
	$fichierlangfile = fopen($mesfichierslangs.$lang[0].'-web.inc', 'r');
	while (!feof($fichierlangfile)) {
		$fichierlangline = fgets($fichierlangfile, 255);
		echo $fichierlangline;
	}
	fclose($fichierlangfile);
	echo '
			  </textarea>
		    </div>';
	echo '
		  </form>
          <script>
				var editor = CodeMirror.fromTextArea(document.getElementById("modcontenuweb"), {        
			  lineNumbers: true,
			  readOnly: true,
			  scrollbarStyle: "native"
            });
          </script>
        </li>
	  </ul>
    </li>';
}
if (FileDBExist($mesfichierslangs.$lang[0].'-plus.inc')) {
		$titre = $admin[144].' '.$admin[958].' '.$lang[0].'-plus.inc';
	echo '
    <a id="topmobstyle"></a>
    <li>
      <a href="#">'.$titre.'</a>
      <ul>
	    <li>
		  <form name="modmobcss" action="admin.php?lng='.$lng.'&amp;pg=configlang#topmobstyle" method="post">';
	echo '
			<div class="quest" style="margin:0 8px;padding:2px 20px;">
			  <div style="float:right">'.SubmitButton($admin[144].' '.$admin[958], '', '', $imgdroite).'</div>
			  <div style="clear:both"></div>
			</div>';
	echo '	    
		    <input type="hidden" name="corit" value="2" />
			<input type="hidden" name="myfichierlang" value="'.$lang[0].'-plus" />
			<div style="margin:auto;text-align:left;">
					<textarea name="modcontenuwebplus" id="modcontenuwebplus" rows="32" style="width:100%;">';
	$fichierlangfile = fopen($mesfichierslangs.$lang[0].'-plus.inc', 'r');
	while (!feof($fichierlangfile)) {
		$fichierlangline = fgets($fichierlangfile, 255);
		if (str_contains($fichierlangline,'<?php'))
		{
			$fichierlangline = str_replace('<?php','***infophpnepassupprimer', $fichierlangline);
		}
		echo $fichierlangline;
	}
	fclose($fichierlangfile);
	echo '
			  </textarea>
		    </div>';
	echo '
			<div class="quest" style="margin:0 8px;padding:2px 20px;">
			  <div style="float:right">'.SubmitButton($admin[144].' '.$admin[958], '', '', $imgdroite).'</div>
			  <div style="clear:both"></div>
			</div>';
	echo '
		  </form>
          <script>
				var editor = CodeMirror.fromTextArea(document.getElementById("modcontenuwebplus"), {        
			  lineNumbers: true,
			  scrollbarStyle: "native"
            });
          </script>
        </li>
	  </ul>
    </li>';
}

if ($lang[1] != '') // si lang[1] est définie dans la config Générale
{
	if (FileDBExist($mesfichierslangs.$lang[1].'-admin.inc')) {
			$titre = $admin[959]." ".$admin[958].' '.$lang[1].'-admin.inc';
			$titre .= $admin[831];
		echo '
		<a id="topmobstyle"></a>
		<li>
		  <a href="#">'.$titre.'</a>
		  <ul>
			<li>
			  <form name="modmobcss" action="admin.php?lng='.$lng.'&amp;pg=configlang#topmobstyle" method="post">';
		echo '    
				<input type="hidden" name="myfichierlang" value="'.$lang[1].'-admin" />
				<div style="margin:auto;text-align:left;">
				  <textarea name="modcontenuadmin1" id="modcontenuadmin1" rows="32" style="width:100%;">';
		$fichierlangfile = fopen($mesfichierslangs.$lang[1].'-admin.inc', 'r');
		while (!feof($fichierlangfile)) {
			$fichierlangline = fgets($fichierlangfile, 255);
			echo $fichierlangline;
		}
		fclose($fichierlangfile);
		echo '
				  </textarea>
				</div>';
		echo '
			  </form>
			  <script>
					var editor = CodeMirror.fromTextArea(document.getElementById("modcontenuadmin1"), {        
				  lineNumbers: true,
				  readOnly: true,
				  scrollbarStyle: "native"
				});
			  </script>
			</li>
		  </ul>
		</li>';
	}
	if (FileDBExist($mesfichierslangs.$lang[1].'-admplus.inc')) {
			$titre = $admin[144].' '.$admin[958].' '.$lang[1].'-admplus.inc';
		echo '
		<a id="topmobstyle"></a>
		<li>
		  <a href="#">'.$titre.'</a>
		  <ul>
			<li>
			  <form name="modmobcss" action="admin.php?lng='.$lng.'&amp;pg=configlang#topmobstyle" method="post">';
		echo '
				<div class="quest" style="margin:0 8px;padding:2px 20px;">
				  <div style="float:right">'.SubmitButton($admin[144].' '.$admin[958], '', '', $imgdroite).'</div>
				  <div style="clear:both"></div>
				</div>';
		echo '
				<input type="hidden" name="corit" value="3" />		    
				<input type="hidden" name="myfichierlang" value="'.$lang[1].'-admplus" />
				<div style="margin:auto;text-align:left;">
						<textarea name="modcontenuadminplus1" id="modcontenuadminplus1" rows="32" style="width:100%;">';
		$fichierlangfile = fopen($mesfichierslangs.$lang[1].'-admplus.inc', 'r');
		while (!feof($fichierlangfile)) {
			$fichierlangline = fgets($fichierlangfile, 255);
			if (str_contains($fichierlangline,'<?php'))
			{
				$fichierlangline = str_replace('<?php','***infophpnepassupprimer', $fichierlangline);
			}
			echo $fichierlangline;
		}
		fclose($fichierlangfile);
		echo '
				  </textarea>
				</div>';
		echo '
				<div class="quest" style="margin:0 8px;padding:2px 20px;">
				  <div style="float:right">'.SubmitButton($admin[144].' '.$admin[958], '', '', $imgdroite).'</div>
				  <div style="clear:both"></div>
				</div>';
		echo '
			  </form>
			  <script>
					var editor = CodeMirror.fromTextArea(document.getElementById("modcontenuadminplus1"), {        
				  lineNumbers: true,
				  scrollbarStyle: "native"
				});
			  </script>
			</li>
		  </ul>
		</li>';
	}
	if (FileDBExist($mesfichierslangs.$lang[1].'-web.inc')) {
			$titre = $admin[959]." ".$admin[958].' '.$lang[1].'-web.inc';
			$titre .= $admin[831];
		echo '
		<a id="topmobstyle"></a>
		<li>
		  <a href="#">'.$titre.'</a>
		  <ul>
			<li>
			  <form name="modmobcss" action="admin.php?lng='.$lng.'&amp;pg=configlang#topmobstyle" method="post">';
		echo '    
				<input type="hidden" name="myfichierlang" value="'.$lang[1].'-web" />
				<div style="margin:auto;text-align:left;">
				  <textarea name="modcontenuweb1" id="modcontenuweb1" rows="32" style="width:100%;">';
		$fichierlangfile = fopen($mesfichierslangs.$lang[1].'-web.inc', 'r');
		while (!feof($fichierlangfile)) {
			$fichierlangline = fgets($fichierlangfile, 255);
			echo $fichierlangline;
		}
		fclose($fichierlangfile);
		echo '
				  </textarea>
				</div>';
		echo '
			  </form>
			  <script>
					var editor = CodeMirror.fromTextArea(document.getElementById("modcontenuweb1"), {        
				  lineNumbers: true,
				  readOnly: true,
				  scrollbarStyle: "native"
				});
			  </script>
			</li>
		  </ul>
		</li>';
	}
	if (FileDBExist($mesfichierslangs.$lang[1].'-plus.inc')) {
			$titre = $admin[144].' '.$admin[958].' '.$lang[1].'-plus.inc';
		echo '
		<a id="topmobstyle"></a>
		<li>
		  <a href="#">'.$titre.'</a>
		  <ul>
			<li>
			  <form name="modmobcss" action="admin.php?lng='.$lng.'&amp;pg=configlang#topmobstyle" method="post">';
		echo '
				<div class="quest" style="margin:0 8px;padding:2px 20px;">
				  <div style="float:right">'.SubmitButton($admin[144].' '.$admin[958], '', '', $imgdroite).'</div>
				  <div style="clear:both"></div>
				</div>';
		echo '
				<input type="hidden" name="corit" value="4" />		    
				<input type="hidden" name="myfichierlang" value="'.$lang[1].'-plus" />
				<div style="margin:auto;text-align:left;">
					<textarea name="modcontenuwebplus1" id="modcontenuwebplus1" rows="32" style="width:100%;">';
		$fichierlangfile = fopen($mesfichierslangs.$lang[1].'-plus.inc', 'r');
		while (!feof($fichierlangfile)) {
			$fichierlangline = fgets($fichierlangfile, 255);
			if (str_contains($fichierlangline,'<?php'))
			{
				$fichierlangline = str_replace('<?php','***infophpnepassupprimer', $fichierlangline);
			}
			echo $fichierlangline;
		}
		fclose($fichierlangfile);
		echo '
				  </textarea>
				</div>';
		echo '
				<div class="quest" style="margin:0 8px;padding:2px 20px;">
				  <div style="float:right">'.SubmitButton($admin[144].' '.$admin[958], '', '', $imgdroite).'</div>
				  <div style="clear:both"></div>
				</div>';
		echo '
			  </form>
			  <script>
					var editor = CodeMirror.fromTextArea(document.getElementById("modcontenuwebplus1"), {        
				  lineNumbers: true,
				  scrollbarStyle: "native"
				});
			  </script>
			</li>
		  </ul>
		</li>';
	}
} // fin des lang[1]
echo '
  </ul>';

echo '
</div>';
btable();
include CHEMIN.'admin/bpage.inc';
?>
