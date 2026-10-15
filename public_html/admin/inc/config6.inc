<?php
/*******************************************************************************
 *   Admin Boxes disposition
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2020 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************************************
 *   Latest Changes :
 * v6.00.24 (August 12, 2026) : Delete secondary templates of 0 at 5 not allowed, only modifiy the name 
 *******************************************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'config6.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[5] == '') {
        $nomzone = $admin[346];
        include 'access.inc';
        exit();
    }
}

$widepage = 'on';
$regit    = import('regit');
$xpb      = import('xpb', 'POST', FALSE);
$wpb      = import('wpb', 'POST', FALSE);
$skns     = import('skns', 'POST', FALSE);
$conf     = import('conf');
$nameconf = import('nameconf', 'POST');
$messreg  = '';
$fromconf = import('fromconf', 'POST');

AddFreeBoxes();
AddMenuBoxes();
AddDDMenuBoxes();

$admcookie = isset($_COOKIE[ADMP_COOKIE]) ? $_COOKIE[ADMP_COOKIE] : '00';
$positem   = 0;
for ($i = 0; $i < 9; $i++) {
    $posdata        = $positem + 2;
    $lendata        = intval(substr($admcookie, $positem, 2));
    $adminprefs[$i] = substr($admcookie, $posdata, $lendata);
    $positem        = $posdata + $lendata;
}

$adminprefs[8] = 'web';
$admdata       = '';
for ($i = 0; $i < count($adminprefs); $i++) {
    $strlen   = sprintf("%02d", strlen($adminprefs[$i]));
    $admdata .= $strlen.$adminprefs[$i];
}
setcookie(ADMP_COOKIE, $admdata);

$fileconf = 'confweb';
$meskin   = CHEMIN.'skins/'.$selskin.'/';
unset($xposbox);
if (is_file ($meskin.$fileconf.'.inc')) {
    include $meskin.$fileconf.'.inc';
} else {
    include CHEMIN.'skins/no_skin/'.$fileconf.'.inc';
}

if ($regit == 1) {
	$lst = '';
    foreach (array(BOX_LSIDE, BOX_LEFT, BOX_CENTER, BOX_RIGHT, BOX_RSIDE) as $key) {
		foreach ($xpb[$conf][BOX_TOP][$key] as $i => $value) {
			if (!empty($value) && empty($wpb[BOX_TOP][$key][$i])) { 
				$lst .= ' - [ '.BOX_TOP.' ] [ '.$key.' ] [ '.$i.' ] => '.getLabelBox($boxes, substr($value, 2)).'\n';
                $xpb[$conf][BOX_TOP][$key][$i] = '';
            }
		}
		foreach ($xpb[$conf][BOX_BOTTOM][$key] as $i => $value) {
			if (!empty($value) && empty($wpb[BOX_BOTTOM][$key][$i])) {
				$lst .= ' - [ '.BOX_BOTTOM.' ] [ '.$key.' ] [ '.$i.' ] => '.getLabelBox($boxes, substr($value, 2)).'\n';
                $xpb[$conf][BOX_BOTTOM][$key][$i] = '';
            }
		}
	}
	if ($lst == '') {
		$messreg = ' : &nbsp;OK';
	} else {
		echo BeginJavascript().'alert("'.addslashes($admin[706]).'\n\n'.$lst.'");'.EndJavascript();
	}

	$xposbox[$conf][BOX_LEFT]  = $xpb[$conf][BOX_LEFT];
	$xposbox[$conf][BOX_RIGHT] = $xpb[$conf][BOX_RIGHT];
	$xposbox[$conf][BOX_ABOVE] = $xpb[$conf][BOX_ABOVE];
	$xposbox[$conf][BOX_UNDER] = $xpb[$conf][BOX_UNDER];
    foreach (array(BOX_LSIDE, BOX_LEFT, BOX_CENTER, BOX_RIGHT, BOX_RSIDE) as $key) {
		foreach ($xpb[$conf][BOX_TOP][$key] as $i => $value) {
			$args  = '';
			if (!empty($value)) {
				$wb   = intval($wpb[BOX_TOP][$key][$i]);
				$pos  = strrpos($value, CONNECTOR, 2);
				$args = $wb == 0 ? '' : ($pos == 0 ? CONNECTOR : '').'W'.$wb;
			}
			$xpb[$conf][BOX_TOP][$key][$i] = $value.$args;
		}
		foreach ($xpb[$conf][BOX_BOTTOM][$key] as $i => $value) {
			$args  = '';
			if (!empty($value)) {
				$wb   = intval($wpb[BOX_BOTTOM][$key][$i]);
				$pos  = strrpos($value, CONNECTOR, 2);
				$args = $wb == 0 ? '' : ($pos == 0 ? CONNECTOR : '').'W'.$wb;
			}
			$xpb[$conf][BOX_BOTTOM][$key][$i] = $value.$args;
		}
		$xposbox[$conf][BOX_TOP][$key]    = $xpb[$conf][BOX_TOP][$key];
		$xposbox[$conf][BOX_BOTTOM][$key] = $xpb[$conf][BOX_BOTTOM][$key];
	}
	unset($xpb, $wpb);

	include 'inc/'.$fileconf.'.inc';
    if ($site[7] == 'on' || PHP700) {
        @ob_flush();
        sleep(PHP700 ? 3 : $site[8]);
        echo BeginJavascript().'window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=config6&conf='.$conf.'";'.EndJavascript();
    }
}
elseif ($regit == 2) { // copie config dans les autres skins
	if (!empty($skns)) {
		foreach ($skns as $skin) {
            $src  = CHEMIN.'skins/'.$selskin.'/'.$fileconf.'.inc';
            $dest = CHEMIN.'skins/'.$skin.'/'.$fileconf.'.inc';
            copy($src, $dest);
		}
		unset($skns);
	}
} 
elseif ($regit == 3) { // ajout config secondaire
	if (!empty($nameconf) && preg_match('`^\w{4,24}$`', $nameconf)) {	
		$meskin = CHEMIN.'skins/'.$selskin.'/';
        $templates[] = $nameconf;
        foreach (array('confweb') as $conffile) {
            if (is_file ($meskin.$conffile.'.inc')) {
                include $meskin.$conffile.'.inc';
            } else {
                $fromconf = '';
                include CHEMIN.'skins/no_skin/'.$conffile.'.inc';
            }
			$nbrtemplateabsent = 0;
			for ($ij=0; $ij < count($templates); $ij++)
			{
				if (!isset($templates[$ij])) 
					$nbrtemplateabsent++;
			}
            foreach ($templates as $key=>$tmpl)
			{
				//   file_put_contents(($nomficlog, ' template key: '.$key.' '."\n", FILE_APPEND | LOCK_EX);
				$conf = $key;
				//   file_put_contents(($nomficlog, ' template conf: '.$conf.' '."\n", FILE_APPEND | LOCK_EX);
			}
            foreach (array(BOX_LEFT, BOX_RIGHT, BOX_ABOVE, BOX_UNDER) as $subarray) {
                $xposbox[$conf][$subarray][0] = '';
            }
            if ($fromconf != '') {
                $n = count($xposbox[$fromconf][BOX_TOP][BOX_LEFT]) - 1;
                for ($i = 0; $i < count($xposbox[$fromconf][BOX_TOP][BOX_LEFT]) - 1; $i++) {
                    foreach (array(BOX_TOP, BOX_BOTTOM) as $array) {
                        foreach (array(BOX_LSIDE, BOX_LEFT, BOX_CENTER, BOX_RIGHT, BOX_RSIDE) as $key) {
                            $xposbox[$conf][$array][$key][$i] = @$xposbox[$fromconf][$array][$key][$i];
                        }
                    }
                }
            }
            else {
                foreach (array(BOX_TOP, BOX_BOTTOM) as $array) {
                    foreach (array(BOX_LSIDE, BOX_LEFT, BOX_CENTER, BOX_RIGHT, BOX_RSIDE) as $subarray) {
                        $xposbox[$conf][$array][$subarray][0] = '';
                    }
                }
            }
            include 'inc/'.$conffile.'.inc';
            if ($site[7] == 'on' || PHP700) {
                @ob_flush();
                sleep(PHP700 ? 3 : $site[8]);
                echo BeginJavascript().'window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=config6&conf='.$conf.'";'.EndJavascript();
            }
        }																							 
        for ($i = 1; $i < count($templates)+$nbrtemplateabsent; $i++) {
            if (!is_file($meskin.'stylepage'.$i.'.css')) {
                $csstitle = '/* ----  CSS to customize '.$page[14].' for secondary page '.$templates[$i].' ---- */';
                $cssbody  = chr(10).chr(10).chr(10).'
/* ---- set the mobile customizations for the page '.$templates[$i].', between the { } ---- */';
                $cssbody .= '
@media screen and (max-width: 760px), screen and (max-device-width: 760px) {
    
}';
                WriteFullDB($meskin.'stylepage'.$i.'.css', $csstitle.$cssbody);
            }
        }
	} else {
		echo BeginJavascript().'alert("'.addslashes($admin[1337]).'");'.EndJavascript();
	}
}
elseif ($regit == 4) { // modification/suppression config secondaire
    $meskin = CHEMIN.'skins/'.$selskin.'/';
    foreach (array('confweb') as $conffile) {
        if (!empty($nameconf)) {
            if (is_file ($meskin.$conffile.'.inc')) {
                include $meskin.$conffile.'.inc';
            } else {
                include CHEMIN.'skins/no_skin/'.$conffile.'.inc';
            }
            if (preg_match('`^\w{4,24}$`', $nameconf)) {
                foreach($templates as $key=>$value)
                    if ($conf == $key) {
                        $templates[$conf] = $nameconf;
                        break;
                    }
                include 'inc/'.$conffile.'.inc';
            }
        } else {
            if (is_file ($meskin.$conffile.'.inc')) {
                include $meskin.$conffile.'.inc';
            } else {
                include CHEMIN.'skins/no_skin/'.$conffile.'.inc';
            }
            foreach($templates as $key=>$value)
			{
                if ($key > 5)
				{
					if ($conf == $key && $key != 0) {
						unset($templates[$conf]);
						unset($xposbox[$conf]);
						@unlink($meskin.'stylepage'.$conf.'.css');
						break;
					}
				}
			}
            include 'inc/'.$conffile.'.inc';
        }
    }
}

include CONFIG;
$conf = !isset($templates[$conf]) ? 0 : $conf;

TestPosBoxes($xposbox[$conf][BOX_LEFT], 10, 2);
TestPosBoxes($xposbox[$conf][BOX_RIGHT], 10, 2);
TestSizeBoxes($xposbox[$conf][BOX_LEFT], $xposbox[$conf][BOX_RIGHT]);

TestPosBoxes($xposbox[$conf][BOX_TOP][BOX_LSIDE], 2, 1);
TestPosBoxes($xposbox[$conf][BOX_TOP][BOX_LEFT], 2, 1);
TestPosBoxes($xposbox[$conf][BOX_TOP][BOX_CENTER], 2, 1);
TestPosBoxes($xposbox[$conf][BOX_TOP][BOX_RIGHT], 2, 1);
TestPosBoxes($xposbox[$conf][BOX_TOP][BOX_RSIDE], 2, 1);
TestSizeBoxes($xposbox[$conf][BOX_TOP][BOX_LSIDE], $xposbox[$conf][BOX_TOP][BOX_LEFT], $xposbox[$conf][BOX_TOP][BOX_CENTER], $xposbox[$conf][BOX_TOP][BOX_RIGHT], $xposbox[$conf][BOX_TOP][BOX_RSIDE]);

TestPosBoxes($xposbox[$conf][BOX_ABOVE], 2, 1);
TestPosBoxes($xposbox[$conf][BOX_UNDER], 2, 1);

TestPosBoxes($xposbox[$conf][BOX_BOTTOM][BOX_LSIDE], 2, 1);
TestPosBoxes($xposbox[$conf][BOX_BOTTOM][BOX_LEFT], 2, 1);
TestPosBoxes($xposbox[$conf][BOX_BOTTOM][BOX_CENTER], 2, 1);
TestPosBoxes($xposbox[$conf][BOX_BOTTOM][BOX_RIGHT], 2, 1);
TestPosBoxes($xposbox[$conf][BOX_BOTTOM][BOX_RSIDE], 2, 1);
TestSizeBoxes($xposbox[$conf][BOX_BOTTOM][BOX_LSIDE], $xposbox[$conf][BOX_BOTTOM][BOX_LEFT], $xposbox[$conf][BOX_BOTTOM][BOX_CENTER], $xposbox[$conf][BOX_BOTTOM][BOX_RIGHT], $xposbox[$conf][BOX_BOTTOM][BOX_RSIDE]);

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[350]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[350], '100%');

include 'plugins/plugbox.inc';

$skn   = isset($selskin) ? '&amp;selskin='.$selskin : 'no_skin';
$skins = ExploreDir('skins/');

echo '
<div class="text-center">'.ToHelp($lng, '&pg=968&mnuid=937&tconfig=0#z2').'<p>'.$admin[351].'</p>';

echo '
  <hr />
  <div class="table-responsive-md">
  <table class="table text-left w-100">
    <tr>
      <td class="forum w-50 m-auto align-top text-left" style="padding:5px 20px;">
	    '.$admin[725].' : <strong>'.$userprefs[10].$messreg.'</strong>
		<div style="text-align:center;margin-top:12px;">
          <form name="selconf" action="admin.php?lng='.$lng.'&amp;pg=config6" method="post">
            <input type="hidden" name="selskin" value="'.$selskin.'" />
		    <div class="d-inline-block">'.$admin[1335].'</div>
	        <div class="d-inline-block">
              <select class="form-control" name="conf" style="width:140px;" onchange="javascript:selconf.submit();">';

foreach ($templates as $key=>$tmpl) {
	echo '
		        <option value="'.$key.'"'.Selected($conf == $key).'>'.$key.' - '.$tmpl.'</option>>';
}

echo '
		      </select>
            </div>
          </form>
		</div>
		<div id="modbutton" class="text-center w-100 m-auto">
		  '.SubmitButton($admin[144].'/'.$admin[186], 'cache(\'modbutton\'); montre(\'modtname\',\'block\');').'
	    </div>
		<div id="modtname" class="quest text-center w-100 m-auto" 
          style="display:none; max-width:250px;height:auto;z-index:15;border:1px solid;border-radius:8px;">
		  <form name="modconf" action="admin.php?lng='.$lng.'&amp;pg=config6" method="post">
			<input type="hidden" name="regit" value="4" />
			<input type="text" name="conf" value="'.$conf.'" />
			<div>'.$admin[144].' : <input class="form-control" type="text" name="nameconf" value="'.$templates[$conf].'" /><br />'.$help[15].'</div>
			<div style="text-align:center;margin-top:4px;">'.SubmitButton($admin[38]).'</div>
			<div style="float:right;margin:-12px 4px 0 0;">
			  <i class="far fa-caret-square-up" title="'.$admin[458].'" onmouseup="cache(\'modtname\'); montre(\'modbutton\');"></i>
			</div>
			<div class="clearfix"></div>
		  </form>
		</div>
		<hr />
		<div style="text-align:center;margin-top:12px;">
          <form name="addconf" action="admin.php?lng='.$lng.'&amp;pg=config6" method="post">
            <input type="hidden" name="regit" value="3" />
            <input type="hidden" name="selskin" value="'.$selskin.'" />
			<div>
              <div class="d-inline-block">'.$admin[1336].'</div>
              <div class="d-inline-block"><input name="nameconf" class="form-control texte" type="text" /></div>
            </div>
            <div>
              <div class="d-inline-block">'.$admin[1540].'</div>
              <div class="d-inline-block">
                <select class="form-control" name="fromconf" style="width:140px;">
		          <option value="">'.$admin[875].'</option>>';
foreach ($templates as $key=>$tmpl) {
	echo '
		          <option value="'.$key.'">'.$key.' - '.$tmpl.'</option>>';
}

echo '
		        </select>
              </div>
            </div>
		    '.SubmitButton($admin[38]).'
          </form>
		</div>
	  </td>
      <td class="forum" style="text-align:center;vertical-align:top;">
	    '.$admin[707].'
        <form name="selskns" action="admin.php?lng='.$lng.'&amp;pg=config6" method="post">
          <input type="hidden" name="regit" value="2" />
          <input type="hidden" name="selskin" value="'.$selskin.'" />
	      <select class="form-control mx-auto mt-3 w-100" style="max-width:200px;" name="skns[]" size="10" multiple="multiple">';

foreach ($skins as $skn)
	if ($skn != $selskin) echo '
			<option value="'.$skn.'">'.$skn.'</option>>';

echo '
		  </select>
		  '.SubmitButton($admin[708]).'
        </form>
	  </td>
	</tr>
  </table>
  </div>
  <div style="width:100%;margin-left:auto;margin-right:auto;text-align:center">
    <form name="conf6" action="admin.php?lng='.$lng.'&amp;pg=config6" method="post">
      <div class="row quest p-0 mb-2 w-98 m-auto">
	    <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
        <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
      </div>
      <input type="hidden" name="regit" value="1" />
      <input type="hidden" name="conf" value="'.$conf.'" />
      <input type="hidden" name="selskin" value="'.$selskin.'" />';

echo '
      <fieldset style="margin: 4px 0;">'.legend($admin[1073]).'
        <div class="table-responsive-md">
        <table class="table text-center w-100 m-0">
          <tr>
            <td style="padding: 0px;">';
if (empty($wposbox[$conf]))
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_TOP.']['.BOX_LSIDE.']', $xposbox[$conf][BOX_TOP][BOX_LSIDE], SelectBoxes($boxes, BOX_TOP), true, true, 'wpb['.BOX_TOP.']['.BOX_LSIDE.']');
else
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_TOP.']['.BOX_LSIDE.']', $xposbox[$conf][BOX_TOP][BOX_LSIDE], SelectBoxes($boxes, BOX_TOP), true, true, 'wpb['.BOX_TOP.']['.BOX_LSIDE.']', $wposbox[$conf][BOX_TOP][BOX_LSIDE]);
echo '
            </td>
            <td style="padding: 0px;">';
if (empty($wposbox[$conf]))
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_TOP.']['.BOX_LEFT.']', $xposbox[$conf][BOX_TOP][BOX_LEFT], SelectBoxes($boxes, BOX_TOP), true, true, 'wpb['.BOX_TOP.']['.BOX_LEFT.']');
else
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_TOP.']['.BOX_LEFT.']', $xposbox[$conf][BOX_TOP][BOX_LEFT], SelectBoxes($boxes, BOX_TOP), true, true, 'wpb['.BOX_TOP.']['.BOX_LEFT.']', $wposbox[$conf][BOX_TOP][BOX_LEFT]);
echo '
            </td>
            <td style="padding: 0px;">';
if (empty($wposbox[$conf]))
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_TOP.']['.BOX_CENTER.']', $xposbox[$conf][BOX_TOP][BOX_CENTER], SelectBoxes($boxes, BOX_TOP), true, true, 'wpb['.BOX_TOP.']['.BOX_CENTER.']');
else
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_TOP.']['.BOX_CENTER.']', $xposbox[$conf][BOX_TOP][BOX_CENTER], SelectBoxes($boxes, BOX_TOP), true, true, 'wpb['.BOX_TOP.']['.BOX_CENTER.']', $wposbox[$conf][BOX_TOP][BOX_CENTER]);
echo '
            </td>
            <td style="padding: 0px;">';
if (empty($wposbox[$conf]))
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_TOP.']['.BOX_RIGHT.']', $xposbox[$conf][BOX_TOP][BOX_RIGHT], SelectBoxes($boxes, BOX_TOP), true, true, 'wpb['.BOX_TOP.']['.BOX_RIGHT.']');
else
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_TOP.']['.BOX_RIGHT.']', $xposbox[$conf][BOX_TOP][BOX_RIGHT], SelectBoxes($boxes, BOX_TOP), true, true, 'wpb['.BOX_TOP.']['.BOX_RIGHT.']', $wposbox[$conf][BOX_TOP][BOX_RIGHT]);
echo '
            </td>
            <td style="padding: 0px;">';
if (empty($wposbox[$conf]))
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_TOP.']['.BOX_RSIDE.']', $xposbox[$conf][BOX_TOP][BOX_RSIDE], SelectBoxes($boxes, BOX_TOP), true, true, 'wpb['.BOX_TOP.']['.BOX_RSIDE.']');
else
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_TOP.']['.BOX_RSIDE.']', $xposbox[$conf][BOX_TOP][BOX_RSIDE], SelectBoxes($boxes, BOX_TOP), true, true, 'wpb['.BOX_TOP.']['.BOX_RSIDE.']', $wposbox[$conf][BOX_TOP][BOX_RSIDE]);
echo '
            </td>
          </tr>
        </table>
        </div>
      </fieldset>

      <div class="table-responsive-md">
      <table class="table text-center w-100 border-0">
        <tr>
          <td style="vertical-align:top;width:30%;">
            <fieldset>'.legend($admin[337]);
WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_LEFT.']', $xposbox[$conf][BOX_LEFT], SelectBoxes($boxes, BOX_LEFT), true, true);
echo '
            </fieldset>
          </td>
          <td style="vertical-align:top;width:40%;">
            <fieldset>'.legend($admin[1075], '10px');
WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_ABOVE.']', $xposbox[$conf][BOX_ABOVE], SelectBoxes($boxes, BOX_ABOVE), true, true);

echo '
            </fieldset>
            <p class="quest" style="text-align:center;line-height:75px;margin:50px 0;font-weight:bold;font-size:1.5em;">'.$admin[878].'</p>
            <fieldset>'.legend($admin[1076], '10px');
WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_UNDER.']', $xposbox[$conf][BOX_UNDER], SelectBoxes($boxes, BOX_UNDER), true, true);
echo '
            </fieldset>
          </td>
          <td style="vertical-align:top;width:30%;">
            <fieldset>'.legend($admin[338]);
WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_RIGHT.']', $xposbox[$conf][BOX_RIGHT], SelectBoxes($boxes, BOX_RIGHT), true, true);
echo '
            </fieldset>
          </td>
        </tr>
      </table>
      </div>
      <fieldset style="margin: 4px 0;">'.legend($admin[1074]).'
        <div class="table-responsive-md">
        <table class="table text-center w-100 m-0">
          <tr>
            <td style="padding: 0px;">';
if (empty($wposbox[$conf]))
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_BOTTOM.']['.BOX_LSIDE.']', $xposbox[$conf][BOX_BOTTOM][BOX_LSIDE], SelectBoxes($boxes, BOX_BOTTOM), true, true, 'wpb['.BOX_BOTTOM.']['.BOX_LSIDE.']');
else
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_BOTTOM.']['.BOX_LSIDE.']', $xposbox[$conf][BOX_BOTTOM][BOX_LSIDE], SelectBoxes($boxes, BOX_BOTTOM), true, true, 'wpb['.BOX_BOTTOM.']['.BOX_LSIDE.']', $wposbox[$conf][BOX_BOTTOM][BOX_LSIDE]);
echo '
            </td>
            <td style="padding: 0px;">';
if (empty($wposbox[$conf]))
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_BOTTOM.']['.BOX_LEFT.']', $xposbox[$conf][BOX_BOTTOM][BOX_LEFT], SelectBoxes($boxes, BOX_BOTTOM), true, true, 'wpb['.BOX_BOTTOM.']['.BOX_LEFT.']');
else
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_BOTTOM.']['.BOX_LEFT.']', $xposbox[$conf][BOX_BOTTOM][BOX_LEFT], SelectBoxes($boxes, BOX_BOTTOM), true, true, 'wpb['.BOX_BOTTOM.']['.BOX_LEFT.']', $wposbox[$conf][BOX_BOTTOM][BOX_LEFT]);
echo '
            </td>
            <td style="padding: 0px;">';
if (empty($wposbox[$conf]))
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_BOTTOM.']['.BOX_CENTER.']', $xposbox[$conf][BOX_BOTTOM][BOX_CENTER], SelectBoxes($boxes, BOX_BOTTOM), true, true, 'wpb['.BOX_BOTTOM.']['.BOX_CENTER.']');
else
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_BOTTOM.']['.BOX_CENTER.']', $xposbox[$conf][BOX_BOTTOM][BOX_CENTER], SelectBoxes($boxes, BOX_BOTTOM), true, true, 'wpb['.BOX_BOTTOM.']['.BOX_CENTER.']', $wposbox[$conf][BOX_BOTTOM][BOX_CENTER]);
echo '
            </td>
            <td style="padding: 0px;">';
if (empty($wposbox[$conf]))
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_BOTTOM.']['.BOX_RIGHT.']', $xposbox[$conf][BOX_BOTTOM][BOX_RIGHT], SelectBoxes($boxes, BOX_BOTTOM), true, true, 'wpb['.BOX_BOTTOM.']['.BOX_RIGHT.']');
else
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_BOTTOM.']['.BOX_RIGHT.']', $xposbox[$conf][BOX_BOTTOM][BOX_RIGHT], SelectBoxes($boxes, BOX_BOTTOM), true, true, 'wpb['.BOX_BOTTOM.']['.BOX_RIGHT.']', $wposbox[$conf][BOX_BOTTOM][BOX_RIGHT]);
echo '
            </td>
            <td style="padding: 0px;">';
if (empty($wposbox[$conf]))
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_BOTTOM.']['.BOX_RSIDE.']', $xposbox[$conf][BOX_BOTTOM][BOX_RSIDE], SelectBoxes($boxes, BOX_BOTTOM), true, true, 'wpb['.BOX_BOTTOM.']['.BOX_RSIDE.']');
else
	WriteColumnSelectBoxes('xpb['.$conf.']['.BOX_BOTTOM.']['.BOX_RSIDE.']', $xposbox[$conf][BOX_BOTTOM][BOX_RSIDE], SelectBoxes($boxes, BOX_BOTTOM), true, true, 'wpb['.BOX_BOTTOM.']['.BOX_RSIDE.']', $wposbox[$conf][BOX_BOTTOM][BOX_RSIDE]);
echo '
            </td>
          </tr>
        </table>
        </div>
      </fieldset>
';

echo '
      <div class="row quest p-0 mt-2 w-98 m-auto">
	    <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
        <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
      </div>
    </form>
  </div>
</div>';

btable();
include CHEMIN."admin/bpage.inc";
?>