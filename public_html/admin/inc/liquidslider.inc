<?php
/*******************************************************************************
 *   Text slider
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
 * v6.00.19 (August 26, 2024) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "liquidslider.inc")) {
	header("location:../index.php");
	die();
}

$act   = import('act');
$tri   = import('tri');
$idls  = import('idls');
$idpg  = import('idpg');
$regit = import('regit');
$def   = import('def');

$baseurl  = CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg=liquidslider';
$nbresult = 25;
$easefunc = array(
    ''=>'-- '.$admin[77].' --',
    'animate.css'=>'Animate',
    'linear'=>'Linear',
    'swing'=>'Swing',
    'easeInOutQuad'=>'Quad',
    'easeInOutCubic'=>'Cubic',
    'easeInOutQuart'=>'Quart',
    'easeInOutQuint'=>'Quint',
    'easeInOutExpo'=>'Expo',
    'easeInOutSine'=>'Sine',
    'easeInOutCirc'=>'Circ',
    'easeInOutElastic'=>'Elastic',
    'easeInOutBack'=>'Back',
    'easeInOutBounce'=>'Bounce'
);
$animeIn = array(
    'No Animate'=>'bounceInRight',
    'Seekers'=>'flash,bounce,shake,tada,swing,woobe,wiggle,pulse',
    'Fading'=>'fadeIn,fadeInUp,fadeInDown,fadeInLeft,fadeInRight,fadeInUpBig,fadeInDownBig,fadeInLeftBig,fadeInRightBig',
    'Bouncing'=>'bounceIn,bounceInDown,bouneInUp,bounceInLeft,bouceInRight',
    'Rotating'=>'rotateIn,rotateInDownLeft,rotateInDownRight,rotateInUpLeft,rotateInUpRight',
    'Lightspeed'=>'lightSpeedIn',
    'Specials'=>'hinge,rollIn'
);
$animeOut = array(
    'No Animate'=>'bounceOutRight',
    'Seekers'=>'flash,bounce,shake,tada,swing,woobe,wiggle,pulse',
    'Fading'=>'fadeOut,fadeOutUp,fadeOutDown,fadeOutLeft,fadeOutRight,fadeOutUpBig,fadeOutDownBig,fadeOutLeftBig,fadeOutRightBig',
    'Bouncing'=>'bounceOut,bounceOutDown,bouneOutUp,bounceOutLeft,bouceOutRight',
    'Rotating'=>'rotateOut,rotateOutDownLeft,rotateOutDownRight,rotateOutUpLeft,rotateOutUpRight',
    'Lightspeed'=>'lightSpeedOut',
    'Specials'=>'hinge,rollOut'
);
$direction      = array(''=>'-- '.$admin[77].' --', 'right'=>$admin[495], 'left'=>$admin[494]);
$tabshorizalign = array(''=>'-- '.$admin[77].' --', 'left'=>$admin[494], 'right'=>$admin[495], 'middle'=>$admin[397]);
$tabsvertalign  = array(''=>'-- '.$admin[77].' --', 'top'=>$admin[1583], 'bottom'=>$admin[1667]);

$strvalues = array('left','center','right','top','bottom','animate.css','linear','swing','easeInOutQuad','easeInOutCubic','easeInOutQuart',
                   'easeInOutQuint','easeInOutExpo','easeInOutSine','easeInOutCirc','easeInOutElastic','easeInOutBack','easeInOutBounce',
                   'flash','bounce','shake','tada','swing','woobe','wiggle','pulse','fadeIn','fadeInUp','fadeInDown','fadeInLeft','fadeInRight',
                   'fadeInUpBig','fadeInDownBig','fadeInLeftBig','fadeInRightBig','bounceIn','bounceInDown','bouneInUp','bounceInLeft',
                   'bouceInRight','rotateIn','rotateInDownLeft','rotateInDownRight','rotateInUpLeft','rotateInUpRight','lightSpeedIn','hinge',
                   'rollIn','fadeOut','fadeOutUp','fadeOutDown','fadeOutLeft','fadeOutRight','fadeOutUpBig','fadeOutDownBig','fadeOutLeftBig',
                   'fadeOutRightBig','bounceOut','bounceOutDown','bouneOutUp','bounceOutLeft','bouceOutRight','rotateOut','rotateOutDownLeft',
                   'rotateOutDownRight','rotateOutUpLeft','rotateOutUpRight','lightSpeedOut','rollOut','bounceInRight','bounceOutRight');

define('LSDATAREP', CHEMIN.DATAREP.'other/liquidslider/');
if (is_file(CHEMIN.'inc/lang/'.$lng.'-lsadmin.inc'))
    include CHEMIN.'inc/lang/'.$lng.'-lsadmin.inc';
else
    include CHEMIN.'inc/lang/en-lsadmin.inc';

// actions rapides
if ($act == "fast") { 
	$ids   = import('ids');
	$dbids = explode(" ", trim($ids));
	$data  = array();
	for ($ij = 0; $ij < count($dbids); $ij++) {
		$ii = $dbids[$ij];
		echo $ii;
        if (isset($_POST['a_'.$ii]) && $_POST['a_'.$ii] == 'on') {
			$data[] = array($ii, "a");
		}
        elseif (isset($_POST['i_'.$ii]) && $_POST['i_'.$ii] == 'on') {
			$data[] = array($ii, "i");
		}
        elseif (isset($_POST['d_'.$ii]) && $_POST['d_'.$ii] == 'on') {
			$data[] = array($ii, "d");
		}
	}
	unset($dbids);
	
	if (!empty($data)) {
		$lsidx = ReadDBFields(LSDATAREP.'lsindex.dtb');
		for ($i=0; $i<count($lsidx); $i++) {
			for ($j=0; $j<count($data); $j++) {
				if ($data[$j][0] == $lsidx[$i][0]) {
					$lsidx[$i][1] = $data[$j][1];
					break;
				}
			}
		}
		WriteDBFields(LSDATAREP.'lsindex.dtb', $lsidx);
		unset($lsidx);
	}
	unset($data);
	$act = 1;
}

function constructLS() {
    global $idls, $lsadmin;
    
    $def_ls = array(1=>'true','0','1500','easeInOutExpo','bounceInRight','bounceOutRight','500','500','false','right','6000',
                       'false','false','true','true','false','true','left','top','true','1');
    $lbl_ls = array(1=>'autoHeight','minHeight','slideEaseDuration','slideEaseFunction','animateIn','animateOut','fadeInDuration',
                       'fadeOutDuration','autoSlide','autoSlideDirection','autoSlideInterval','forceAutoSlide','pauseOnHover',
                       'dynamicArrows','dynamicArrowsGraphical','hideSideArrows','dynamicTabs','dynamicTabsAlign',
                       'dynamicTabsPosition','includeTitle','firstPanelToLoad');
        
    $construct = '
<link href="inc/liquidslider/css/liquid-slider.css" rel="stylesheet" />
<link href="inc/liquidslider/css/animate.css" rel="stylesheet" />
<script src="inc/liquidslider/js/jquery.liquid-slider.min.js"></script>
<script src="inc/liquidslider/js/jquery.touchSwipe.min.js"></script>
<script type="text/javascript">
$(function(){
  $(\'#lstext-'.$idls.'\').liquidSlider({';
    if (file_exists(LSDATAREP.'/lsvalues_'.$idls.INCEXT)) include LSDATAREP.'/lsvalues_'.$idls.INCEXT;
    foreach ($def_ls as $key => $value) {
        if (!empty($ls_val[$key]) && $ls_val[$key] != $value) 
            $construct .= chr(10).'    '.$lbl_ls[$key].': '.(in_array($ls_val[$key], array('true', 'false')) || 1 == preg_match('!^[0-9]!', $ls_val[$key]) ? $ls_val[$key] : '"'.$ls_val[$key].'"').',';
        if ($ls_val[15] == 'false' && $lbl_ls[$key] == 'dynamicArrowsGraphical')
            $construct .= chr(10).'    dynamicArrowLeftText: "&#171; '.$lsadmin[37].'",'.chr(10).'    dynamicArrowRightText: "'.$lsadmin[38].' &#187;",';
    }
    if ($ls_val[4] == 'animate.css') {
        $construct .= '
    callback: function() {
      var self = this;
      $(\'.lstext-'.$idls.'-panel\').each(function() {
        $(this).removeClass(\'animated \' + self.options.animateIn);
      });
    }';
    }
    $construct .= '
  });';
    if ($btnStartStop == 'true') $construct .= '
  $(\'.lstext-'.$idls.'-btn\').on(\'click\', function() {
    var api = $.data( $(\'#lstext-'.$idls.'\')[0], \'liquidSlider\'),
      text = $(this).text() === \''.$lsadmin[32].'\' ? \''.$lsadmin[33].'\' : \''.$lsadmin[32].'\';
      if (text === \''.$lsadmin[33].'\')
          api.startAutoSlide();
      else
          api.stopAutoSlide();
      $(this).text(text);
  });';
    $construct .= '
});
</script>
<style>
.ls-main, #lstext-'.$idls.'-wrapper {
  max-width: '.(empty($diaporamaWidth) ? '600px' : $diaporamaWidth.(substr($diaporamaWidth, -1) != '%' ? 'px' : '')).' !important;
  margin: auto;
}
#lstext-'.$idls.' .ls-item {
  width: 100%; '.($ls_val[1] == 'true' ? '' : (empty($ls_val[2]) ? '' : 'min-height: '.$ls_val[2].'px;')).'
  background: transparent !important;
}
.liquid-slider {
  background: '.(!empty($backgroundDiaporama) ? $backgroundDiaporama : 'transparent').' !important;
  color: '.(!empty($colorDiaporama) ? $colorDiaporama : '#000000').' !important;
}
.ls-nav { background: '.(!empty($backgroundDiaporama) ? $backgroundDiaporama : 'transparent').' !important; }
ul#lstext-'.$idls.'-nav-ul li a {
  font-weight: bold;
  background: '.(!empty($backgroundTabs) ? $backgroundTabs : 'transparent').' !important;
  color: '.(!empty($colorTabs) ? $colorTabs : '#000000').' !important;
}
ul#lstext-'.$idls.'-nav-ul li a:hover, ul#lstext-'.$idls.'-nav-ul li.current a {
  background: '.(!empty($backgroundCurrentTab) ? $backgroundCurrentTab : 'transparent').' !important;
  color: '.(!empty($colorCurrentTab) ? $colorCurrentTab : '#000000').' !important;
}
#lstext-'.$idls.' .ls-select-box select { width: 100%; font-size: 18px; font-weight: bold; }
</style>
<div class="ls-main">
  <div class="liquid-slider" id="lstext-'.$idls.'">';
    for ($n = 0; $n < $minPanels; $n++) $construct .= '
    <div class="ls-item">
      <h2 class="title">Text '.($n + 1).'</h2>
      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas metus nulla, commodo a sodales sed, dignissim pretium nunc. Nam et lacus neque. Sed volutpat ante id mauris laoreet vestibulum. Nam blandit felis non neque cursus aliquet. Morbi vel enim dignissim massa dignissim commodo vitae quis tellus. Nunc non mollis nulla. Sed consectetur elit id mi consectetur bibendum. Ut enim massa, sodales tempor convallis et, iaculis ac massa. Etiam suscipit nisl eget lorem pellentesque quis iaculis mi mattis. Aliquam sit amet purus lectus. Maecenas tempor ornare sollicitudin.</p>
    </div>';
    $construct .= '
  </div>';
    if ($btnStartStop == 'true') $construct .= '
  <p class="text-center"><button class="lstext-'.$idls.'-btn">'.($ls_val[9] == 'true' ? $lsadmin[33] : $lsadmin[32]).'</button></p>';
    $construct .= '
</div>';

    return $construct;
}

if ($regit == 1) {
    $diaporamaTitle0 = import('diaporamaTitle0');
    $diaporamaTitle1 = import('diaporamaTitle1');
    
    if (empty($idls)) {
        $nextid = ReadCounter(LSDATAREP.'nextls.dtb');
        $nextid = empty($nextid) ? 1 : $nextid + 1;
        $lsid = array($nextid, 'a', $diaporamaTitle0, $diaporamaTitle1);
        AppendDBFields(LSDATAREP.'lsindex.dtb', $lsid);
        WriteCounter(LSDATAREP.'nextls.dtb', $nextid);
        $idls = $nextid;
    } else {
		$dbdocs = ReadDBFields(LSDATAREP.'lsindex.dtb');
		for ($i=0; $i<count($dbdocs); $i++) {
			if ($dbdocs[$i][0] == $idls) {
				$dbdocs[$i][2] = $diaporamaTitle0;
				$dbdocs[$i][3] = $diaporamaTitle1;
				break;
			}
		}
		WriteDBFields(LSDATAREP.'lsindex.dtb', $dbdocs);
    }
    
	$mettre = "<?php
if (stristr(\$_SERVER[\"SCRIPT_NAME\"], \"lsvalues_".$idls.".inc\")) {
    header(\"location:../index.php\");
    die();
}

\$diaporamaTitle0 = stripslashes(\"".$diaporamaTitle0."\");
\$diaporamaTitle1 = stripslashes(\"".$diaporamaTitle1."\");

\$diaporamaWidth = stripslashes(\"".($def == 'on' ? '600' : import('diaporamaWidth'))."\");
\$backgroundDiaporama = stripslashes(\"".($def == 'on' ? '#F2F2F2' : import('backgroundDiaporama'))."\");
\$colorDiaporama = stripslashes(\"".($def == 'on' ? '#000000' : import('colorDiaporama'))."\");
\$backgroundTabs = stripslashes(\"".($def == 'on' ? '#F2F2F2' : import('backgroundTabs'))."\");
\$colorTabs = stripslashes(\"".($def == 'on' ? '#000000' : import('colorTabs'))."\");
\$backgroundCurrentTab = stripslashes(\"".($def == 'on' ? '#F2F2F2' : import('backgroundCurrentTab'))."\");
\$colorCurrentTab = stripslashes(\"".($def == 'on' ? '#000000' : import('colorCurrentTab'))."\");
\$btnStartStop = stripslashes(\"".($def == 'on' ? 'false' : (import('btnStartStop') == 'on' ? 'true' : 'false'))."\");
\$minPanels = stripslashes(\"".($def == 'on' ? '2' : import('minPanels'))."\");

\$ls_val[1] = stripslashes(\"".($def == 'on' ? 'true' : (import('autoHeight') == 'on' ? 'true' : 'false'))."\");
\$ls_val[2] = stripslashes(\"".($def == 'on' ? '0' : import('minHeight'))."\");
\$ls_val[3] = stripslashes(\"".($def == 'on' ? '1500' : import('slideEaseDuration'))."\");
\$ls_val[4] = stripslashes(\"".($def == 'on' ? 'easeInOutExpo' : import('slideEaseFunction'))."\");
\$ls_val[5] = stripslashes(\"".($def == 'on' ? 'bounceInRight' : import('animateIn'))."\");
\$ls_val[6] = stripslashes(\"".($def == 'on' ? 'bounceOutRight' : import('animateOut'))."\");
\$ls_val[7] = stripslashes(\"".($def == 'on' ? '500' : import('fadeInDuration'))."\");
\$ls_val[8] = stripslashes(\"".($def == 'on' ? '500' : import('fadeOutDuration'))."\");
\$ls_val[9] = stripslashes(\"".($def == 'on' ? 'false' : (import('autoSlide') == 'on' ? 'true' : 'false'))."\");
\$ls_val[10] = stripslashes(\"".($def == 'on' ? 'right' : import('autoSlideDirection'))."\");
\$ls_val[11] = stripslashes(\"".($def == 'on' ? '6000' : import('autoSlideInterval'))."\");
\$ls_val[12] = stripslashes(\"".($def == 'on' ? 'false' : (import('forceAutoSlide') == 'on' ? 'true' : 'false'))."\");
\$ls_val[13] = stripslashes(\"".($def == 'on' ? 'false' : (import('pauseOnHover') == 'on' ? 'true' : 'false'))."\");
\$ls_val[14] = stripslashes(\"".($def == 'on' ? 'true' : (import('dynamicArrows') == 'on' ? 'true' : 'false'))."\");
\$ls_val[15] = stripslashes(\"".($def == 'on' ? 'true' : (import('dynamicArrowsGraphical') == 'on' ? 'true' : 'false'))."\");
\$ls_val[16] = stripslashes(\"".($def == 'on' ? 'false' : (import('hideSideArrows') == 'on' ? 'true' : 'false'))."\");
\$ls_val[17] = stripslashes(\"".($def == 'on' ? 'true' : (import('dynamicTabs') == 'on' ? 'true' : 'false'))."\");
\$ls_val[18] = stripslashes(\"".($def == 'on' ? 'left' : import('dynamicTabsAlign'))."\");
\$ls_val[19] = stripslashes(\"".($def == 'on' ? 'top' : import('dynamicTabsPosition'))."\");
\$ls_val[20] = stripslashes(\"".($def == 'on' ? 'true' : (import('includeTitle') == 'on' ? 'true' : 'false'))."\");
\$ls_val[21] = stripslashes(\"".($def == 'on' ? '1' : import('firstPanelToLoad'))."\");
?>";
    WriteFullDB(LSDATAREP.'/lsvalues_'.$idls.INCEXT, $mettre);
    WriteFullDB(LSDATAREP.'/lsconstr_'.$idls.INCEXT, constructLS());
}

// chargement des données
$dbdocs = ReadDBFields(LSDATAREP.'lsindex.dtb');
$tmpi   = 0;
$tmp[0] = array();
$tmp[1] = array();
$tmp[2] = array();
$tmp[3] = array();
for ($i = 0; $i < count($dbdocs); $i++) {
	$tmp[0][$tmpi] = $dbdocs[$i][0];
	$tmp[1][$tmpi] = $dbdocs[$i][1];
	$tmp[2][$tmpi] = $dbdocs[$i][2];
	$tmp[3][$tmpi] = $dbdocs[$i][3];
	$tmpi++;
}
if (empty($idpg)) {
	$idpg = 1;
}
else {
	while ($tmpi <= ($idpg - 1)* $nbresult) $idpg--;
}
if (empty($tri)) $tri = "id";

if ($act == 1) {
    $topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $lsadmin[1]);
    include CHEMIN.'admin/hpage.inc'; 
    if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
    if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($lsadmin[1], "100%");
	echo '
<p style="text-align:center">'.ToHelp($lng, '&pg=1044&mnuid=937&tconfig=0#z2').$lsadmin[2].'<hr />'.$lsadmin[6].'</p><hr />
<div style="text-align:center">
  <a href="'.$baseurl.'&amp;tri='.$tri.'&amp;act=1">'.$lsadmin[3].'</a> || <a href="'.$baseurl.'&amp;tri='.$tri.'&amp;act=2">'.$lsadmin[4].'</a>
</div><hr />';
	if ($tmpi > 0) {
        switch ($tri) {
        case 'id' :
			$cr = array_multisort($tmp[0], SORT_NUMERIC, SORT_ASC, $tmp[1], $tmp[2], $tmp[3]);
            break;
        case '-id' :
			$cr = array_multisort($tmp[0], SORT_NUMERIC, SORT_DESC, $tmp[1], $tmp[2], $tmp[3]);
            break;
        case 'ch' :
            if ($lng == $lang[0])
                $cr = array_multisort($tmp[2], SORT_ASC, $tmp[0], $tmp[1], $tmp[3]);
            else
                $cr = array_multisort($tmp[3], SORT_ASC, $tmp[0], $tmp[1], $tmp[2]);
            break;
        case '-ch' :
            if ($lng == $lang[0])
                $cr = array_multisort($tmp[2], SORT_DESC, $tmp[0], $tmp[1], $tmp[3]);
            else
                $cr = array_multisort($tmp[3], SORT_DESC, $tmp[0], $tmp[1], $tmp[2]);
            break;
        }
		$navbar = GetNavBar($baseurl."&amp;tri=".$tri."&amp;idpg=", $tmpi, $idpg, $nbresult);
		echo '
<div class="w-100 text-center m-auto" style="max-width:840px;">'.$navbar.'
  <form name="fast_actions" action="'.$baseurl.'&amp;idpg='.$idpg.'&amp;idls='.$idls.'&amp;act=fast&amp;tri='.$tri.'" method="post">
    <div class="table-responsive-md">
	<table class="table table-bordered w-100">';
		$idup     = $tri == 'id' ? '<i class="fas fa-caret-up text-danger" style="font-size: 1.8rem;"></i>': '<i class="fas fa-caret-up" style="font-size: 1.8rem;"></i>';
		$iddown   = $tri == '-id' ? '<i class="fas fa-caret-down text-danger" style="font-size: 1.8rem;"></i>': '<i class="fas fa-caret-down" style="font-size: 1.8rem;"></i>';
		$descup   = $tri == 'ch' ? '<i class="fas fa-caret-up text-danger" style="font-size: 1.8rem;"></i>': '<i class="fas fa-caret-up" style="font-size: 1.8rem;"></i>';
		$descdown = $tri == '-ch' ? '<i class="fas fa-caret-down text-danger" style="font-size: 1.8rem;"></i>': '<i class="fas fa-caret-down" style="font-size: 1.8rem;"></i>';
		echo '
      <tr>
        <td colspan="3">
          <div style="text-align:right; padding-right:24px;">'.SubmitButton($admin[1602]).'
            <i class="fas fa-level-down-alt"></i>
          </div>
        </td>
      </tr>
	  <tr class="forum">
        <td class="text-center text-nowrap p-4" style="max-width:100px;">
	      <a href="'.$baseurl.'&amp;act=1&amp;tri=id" title="'.$admin[674].'" style="top:-1px;left:-1px;">'.$idup.'</a>
		    '.$admin[678].'
	      <a href="'.$baseurl.'&amp;act=1&amp;tri=-id" title="'.$admin[675].'" style="top:-1px;left:1px;">'.$iddown.'</a>
		</td>
        <td class="text-nowrap p-4">
	      <a href="'.$baseurl.'&amp;act=1&amp;tri=ch" title="'.$admin[676].'" style="top:-1px;left:1px;">'.$descup.'</a>
		    '.$admin[679].'
	      <a href="'.$baseurl.'&amp;act=1&amp;tri=-ch" title="'.$admin[677].'" style="top:-1px;left:1px;">'.$descdown.'</a>
		</td>
		<td>'.$admin[680].'</td>
	  </tr>';
			$ids = "";
			for ($i = $nbresult*($idpg-1); $i < Min($nbresult*$idpg, $tmpi); $i++) {
				if ($wri == $userprefs[1] && $drtuser[42] == 'redac1' && $wri != $tmp[4][$i]) continue;
				$fileid = $tmp[0][$i];
				$statut = $tmp[1][$i];
				if ($statut != "d") {
					echo '
	  <tr class="'.($i%2 == 0 ? 'rep' : 'quest').'">
	    <td class="text-center w-15" style="max-width:80px;">'.$fileid.'</td>
		<td class="text-center">
		  <span class="float-left">
			<a href="'.$baseurl.'&amp;act=2&amp;idls='.$fileid.'" title="'.$admin[144].'">
			  <i class="far fa-file-alt" title="'.$admin[33].'"></i> 
			  <span style="font-size:11pt;">'.$tmp[$lng == $lang[0] ? 2 : 3][$i].'</span>
			</a>
		  </span>
		  <span style="clear:both"></span>
		</td>
	    <td class="text-center w-15" style="max-width:80px;">';
					if ($statut == "a") {
						MakeRadioGroup(array( array("a", "i", $fileid), array("a", "d", $fileid) ));
					}
					elseif ($statut == "i") {
						MakeRadioGroup(array( array("i", "a", $fileid), array("i", "d", $fileid) ));
					}
					elseif ($status == "d") {
						MakeRadioGroup(array( array("d", "s", $fileid), array("d", "i", $fileid) ));
					}
					echo '
		</td>
	  </tr>';
					$ids .= $fileid." ";
				}
			}
			echo '
      <tr>
        <td colspan="3">
          <div style="text-align:right; padding-right:24px;">'.SubmitButton($admin[1602]).'
            <i class="fas fa-level-up-alt"></i>
          </div>
        </td>
      </tr>
	</table>
    </div>
	<input type="hidden" name="ids" value="'.$ids.'" />
  </form>
  '.$navbar.'
</div>';
	}
}

function GetSelector($padding, $label, $inputname, $color) { // Palette de couleurs
    return '
        <tr style="text-align:left;">
          <td class="align-middle" style="padding:4px 0 2px '.$padding.'px;width:55%;">&bull; '.$label.'</td>
		  <td style="padding:4px 0 2px 4px;">
			<div>
			  <input class="form-control" id="'.$inputname.'" name="'.$inputname.'" type="text" size="6" value="'.$color.'" 
                style="font:14px Arial;text-align:center;width:92px;height:29px;border:1px solid #000;border-radius:4px 0 0 4px;float:left;" />
			  <div style="float:left;width:40px;height:29px;vertical-align:top;border:1px solid #000;border-left:none;background-color:'.$color.';padding:-4px 0 4px 0;"></div>
			  <div id="'.$inputname.'div" style="float:left;width:40px;height:29px;border:1px solid #000;border-left:none;background-color:'.$color.';border-radius:0 4px 4px 0;">&nbsp;</div>
			</div>'.
			BeginJavascript()."
			$('#".$inputname."').ColorPicker({
				onSubmit: function(hsb, hex, rgb, el) {
					$(el).val('#' + hex.toUpperCase());
					$(el).ColorPickerHide();
				},
				onChange: function (hsb, hex, rgb) {
					$('#".$inputname."div').css('backgroundColor', '#' + hex);
				},
				onBeforeShow: function () {
					$(this).ColorPickerSetColor(this.value);
				}
			})
			.bind('keyup', function(){
				$(this).ColorPickerSetColor(this.value);
			});".EndJavascript().'
          </td>
        </tr>';
}

function SelectText($padding, $label, $name, $value, &$textarray) {
    $out = '
        <tr style="text-align:left;">
          <td class="align-middle w-65" style="padding-left:'.$padding.'px;">&bull; '.$label.'</td>
          <td style="padding:0 0 0 4px;">
            <select class="form-control" name="'.$name.'">';
    foreach($textarray as $key => $text) {
        $out .= '
              <option value="'.$key.'"'.Selected($value == $key).'>'.$text.'</option>';
    }
    $out .= '
            </select>
          </td>
        </tr>';
    return $out;
}

function SelectTextGroup($padding, $label, $name, $value, &$textarray) {
    $out = '
        <tr style="text-align:left;">
          <td class="align-middle w-65" style="padding-left:'.$padding.'px;">&bull; '.$label.'</td>
          <td style="padding:0 0 0 4px;">
            <select class="form-control" name="'.$name.'">';
    foreach($textarray as $key => $text) {
        $out .= '
              <optgroup label="'.$key.'">';
        $opts = explode(',', $text);
        foreach($opts as $opt) $out .= '
                <option value="'.$opt.'"'.Selected($value == $opt).'>'.$opt.'</option>';
        $out .= '
              </optgroup>';
    }
    $out .= '
            </select>
          </td>
        </tr>';
    return $out;
}

function InputTexte($padding, $label, $name, $value, $size) {
    return '
        <tr style="text-align:left;">
          <td class="align-middle w-65" style="padding-left:'.$padding.'px;">&bull; '.$label.'</td>
          <td style="padding:0 0 0 4px;"><input name="'.$name.'" class="form-control texte" type="text" size="'.$size.'" value="'.$value.'" /></td>
        </tr>';
}

function InputCheckbox($padding, $label, $name, $value) {
    return '
        <tr style="text-align:left;">
          <td class="align-middle w-65" style="padding:6px 0 3px '.$padding.'px;">&bull; <label for="'.$name.'">'.$label.'</label></td>
          <td style="padding:6px 0 3px 4px;"><input type="checkbox" id="'.$name.'" name="'.$name.'"'.Checked($value == 'on').' /></td>
        </tr>';
}

if ($act == 2) {
    $headinc .= '
<link rel="stylesheet" media="screen" type="text/css" href="../inc/colorpicker/css/colorpicker.css" /> 
<script type="text/javascript" src="../inc/colorpicker/js/colorpicker.js"></script>
';
    $topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > <a href="'.$baseurl.'&amp;act=1">'.str_replace('Admin - ', '', $lsadmin[1]).'</a> > '.$lsadmin[4];
    include CHEMIN.'admin/hpage.inc'; 
    if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
    if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($lsadmin[4], "100%");
    if (file_exists(LSDATAREP.'/lsvalues_'.$idls.INCEXT)) include LSDATAREP.'/lsvalues_'.$idls.INCEXT;
    echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=1044&mnuid=937&tconfig=0#z2').$lsadmin[5].'</p>
<hr />
<div class="w-100 text-center m-auto" style="max-width:680px;">
  <fieldset class="m-0">
    <form name="retdiapo" action="'.$baseurl.'&amp;act=1" method="post">
      <div class="quest" style="padding:2px 20px;">
        <div style="float:right">'.SubmitButton(str_replace('Admin - ', '', $lsadmin[1]), '', '', $imgdroite).'</div>
        <div style="clear:both"></div>
      </div>
    </form>
  </fieldset>
  <fieldset class="m-0">'.legend(!empty($idls) ? $idls.' => '.($lng == $lang[0] ? $diaporamaTitle0 : $diaporamaTitle1) : $lsadmin[4]).'
    <form name="diapotext" action="'.$baseurl.'&amp;idls='.$idls.'#constructpanel" method="post">
      <input type="hidden" name="act" value="2" />
      <input type="hidden" name="regit" value="1" />
      <div class="quest" style="margin-bottom: 5px; padding:2px 20px;">
        <div style="float:right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div>
        <div style="float:right;margin-right:24px;padding-top:8px;"><label for="def">'.$lsadmin[7].'</label>
          <input type="checkbox" id="def" name="def" style="vertical-align:middle;" />
        </div>
        <div style="clear:both"></div>
      </div>
      <div class="table-responsive-md">
      <table class="table table-bordered w-90">
        '.InputTexte(32, $lsadmin[8].' '.$flag1, 'diaporamaTitle0', @$diaporamaTitle0, 30);
    if (!empty($lang[1])) echo 
        InputTexte(32, $lsadmin[8].' '.$flag2, 'diaporamaTitle1', @$diaporamaTitle1, 30);
    echo '
        '.InputTexte(32, $lsadmin[30].' (600)', 'diaporamaWidth', @$diaporamaWidth, 4).'
        '.GetSelector(32, $lsadmin[31].' (#F2F2F2)', 'backgroundDiaporama', @$backgroundDiaporama).'
        '.GetSelector(32, $lsadmin[40].' (#000000)', 'colorDiaporama', @$colorDiaporama).'
        '.GetSelector(32, $lsadmin[41].' (#F2F2F2)', 'backgroundTabs', @$backgroundTabs).'
        '.GetSelector(32, $lsadmin[42].' (#000000)', 'colorTabs', @$colorTabs).'
        '.GetSelector(32, $lsadmin[43].' (#F2F2F2)', 'backgroundCurrentTab', @$backgroundCurrentTab).'
        '.GetSelector(32, $lsadmin[44].' (#000000)', 'colorCurrentTab', @$colorCurrentTab).'
        '.InputCheckbox(32, $lsadmin[34].' ('.$admin[1604].')', 'btnStartStop', (@$btnStartStop == 'true' ? true : false)).'
        '.InputTexte(32, $lsadmin[35].' (2)', 'minPanels', @$minPanels, 4).'
      </table>
      </div>
      <hr />
      <div class="table-responsive-md">
      <table class="table table-bordered w-90">
        '.InputCheckbox(32, $lsadmin[9].' ('.$admin[1605].')', 'autoHeight', (@$ls_val[1] == 'true' ? true : false)).'
        '.InputTexte(32, $lsadmin[10].' (0)', 'minHeight', @$ls_val[2], 4).'
        '.InputTexte(32, $lsadmin[11].' (1500)', 'slideEaseDuration', @$ls_val[3], 4).'
        '.SelectText(32, $lsadmin[12], 'slideEaseFunction', @$ls_val[4], $easefunc).'
        '.SelectTextGroup(32, $lsadmin[13], 'animateIn', @$ls_val[5], $animeIn).'
        '.SelectTextGroup(32, $lsadmin[14], 'animateOut', @$ls_val[6], $animeOut).'
        '.InputTexte(32, $lsadmin[15].' (500)', 'fadeInDuration', @$ls_val[7], 4).'
        '.InputTexte(32, $lsadmin[16].' (500)', 'fadeOutDuration', @$ls_val[8], 4).'
        '.InputCheckbox(32, $lsadmin[17].' ('.$admin[1604].')', 'autoSlide', (@$ls_val[9] == 'true' ? true : false)).'
        '.SelectText(32, $lsadmin[18], 'autoSlideDirection', @$ls_val[10], $direction).'
        '.InputTexte(32, $lsadmin[19].' (6000)', 'autoSlideInterval', @$ls_val[11], 4).'
        '.InputCheckbox(32, $lsadmin[20].' ('.$admin[1604].')', 'forceAutoSlide', (@$ls_val[12] == 'true' ? true : false)).'
        '.InputCheckbox(32, $lsadmin[21].' ('.$admin[1604].')', 'pauseOnHover', (@$ls_val[13] == 'true' ? true : false)).'
        '.InputCheckbox(32, $lsadmin[22].' ('.$admin[1605].')', 'dynamicArrows', (@$ls_val[14] == 'true' ? true : false)).'
        '.InputCheckbox(32, $lsadmin[23].' ('.$admin[1605].')', 'dynamicArrowsGraphical', (@$ls_val[15] == 'true' ? true : false)).'
        '.InputCheckbox(32, $lsadmin[24].' ('.$admin[1604].')', 'hideSideArrows', (@$ls_val[16] == 'true' ? true : false)).'
        '.InputCheckbox(32, $lsadmin[25].' ('.$admin[1605].')', 'dynamicTabs', (@$ls_val[17] == 'true' ? true : false)).'
        '.SelectText(32, $lsadmin[26], 'dynamicTabsAlign', @$ls_val[18], $tabshorizalign).'
        '.SelectText(32, $lsadmin[27], 'dynamicTabsPosition', @$ls_val[19], $tabsvertalign).'
        '.InputCheckbox(32, $lsadmin[28].' ('.$admin[1605].')', 'includeTitle', (@$ls_val[20] == 'true' ? true : false)).'
        '.InputTexte(32, $lsadmin[29].' (1)', 'firstPanelToLoad', @$ls_val[21], 4).'
      </table>
      </div>
      <div class="quest" style="margin-top: 5px; padding:2px 20px;">
        <div style="float:right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div>
        <div style="float:right;margin-right:24px;padding-top:8px;"><label for="def">'.$lsadmin[7].'</label>
          <input type="checkbox" id="def" name="def" style="vertical-align:middle;" />
        </div>
        <div style="clear:both"></div>
      </div>
    </form>';
    if (!empty($idls)) echo '
    <a id="constructpanel"></a>
    <div id="lsconstruct"><hr />
      <fieldset style="margin:-6px 0 0;padding:8px 10px 0 6px;">'.legend($lsadmin[36]).'
        <textarea style="width:100%;height:320px;margin:-12px 0 6px;" onfocus="window.CopyToClipboard(this);">'.constructLS().'</textarea>
      </fieldset>
    </div>'.BeginJavascript().'
window.CopyToClipboard = function (source) {
  source.select();
  if(window.clipboardData){
    var r=clipboardData.setData("Text",source.value);
    source.blur();
    return 1;
  } else
    return 0;
}'.EndJavascript();
    echo '
    <form name="viewls" action="'.CHEMIN.'lstest.php?lng='.$lng.'&amp;tconfig=0&amp;idls='.$idls.'#z2" target="_blank" method="post">
      <div class="quest" style="padding:2px 20px;">
        <div style="float:right">'.SubmitButton(str_replace('Admin - ', '', $lsadmin[39]), '', '', $imgdroite).'</div>
        <div style="clear:both"></div>
      </div>
    </form>
  </fieldset>
</div>
';
}

btable();
include CHEMIN.'admin/bpage.inc';
?>