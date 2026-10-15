<?php
/*******************************************************************************
 *   Admin CSS Generator
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
 * v6.00.21 (December 17, 2024) : List files begin with smtp_
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'config_log_smtp.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
	if (!isset($smtp[6])) $smtp[6] == "";
    if ($drtuser[45] == "" || $smtp[6] == "") {
        $nomzone = $admin[1807];
        include 'access.inc';
        exit();
    }
}

$active     = 1;

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

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[1807]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[1807], '100%');
$mycss = '';
echo '
<div style="width:98%; margin:auto; text-align:center;">';

echo '
  <br /><span class="quest">'.$admin[1807].' <b>&nbsp;'.$mycss.'</b></span><br />';

$meslogsmtp = CHEMIN."data/log/"; // Dossier contenant les logs SMTP

echo '
  <ul id="gyaccordion" style="max-width:1240px; margin:auto;">';
// lecture dans le dossier data/log et prendre les details des fichiers
    $file_names = array();
    $handler = opendir($meslogsmtp);
    while ($file = readdir($handler)) {  
	    $debutnamefile = substr($file,0,5);
        if ($file != '.' && $file != '..' && $file != "robots.txt" && $file != ".htaccess" && $debutnamefile == 'smtp_'){
            $currentModified = filectime($meslogsmtp."/".$file);
            $file_names[] = $file;
        }    
    }
       closedir($handler);
if (count($file_names) > 0)
{  
		for ($i =0; $i < count($file_names); $i++)
		{
			$file = $file_names[$i];
			echo '
			<a name="topbs4style"></a>
			<li>
			  <a href="#">'.$admin[1807].$file.'</a>
			  <ul>
				<li>
				  <div style="margin:auto;text-align:left;">
					<textarea name="contenulog'.$i.'" id="contenulog'.$i.'" rows="32" style="width:100%;">';
			$contenulog = fopen($meslogsmtp."/".$file, "r");
			while (!feof($contenulog)) {
				$contenulogline = fgets($contenulog, 255);
				echo $contenulogline;
			}
			fclose($contenulog);
			echo '
					</textarea>
				  </div>
				  <script>
					var editor = CodeMirror.fromTextArea(document.getElementById("contenulog'.$i.'"), {        
					  lineNumbers: true,
					  readOnly: true,
					  scrollbarStyle: "native"
					});
				  </script>
				</li>
			  </ul>
			</li>';
		}
}
echo '
  </ul>';

if (isset($selskin)) $skn = "&amp;selskin=".$selskin;
echo '
</div>';
btable();
include CHEMIN.'admin/bpage.inc';
?>
