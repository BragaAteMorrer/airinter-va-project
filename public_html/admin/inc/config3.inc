<?php
/*******************************************************************************
 *   Admin Configuration
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

if (stristr($_SERVER['SCRIPT_NAME'], 'config3.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[2] == '') {
        $nomzone = $admin[469];
        include 'access.inc';
        exit();
    }
}

$regit   = import('regit');
$xpb     = import('xpb', 'POST', FALSE);
$wpb     = import('wpb', 'POST', FALSE);
$skns    = import('skns', 'POST', FALSE);
$conf    = import('conf', '', FALSE, 0);
$messreg = '';

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
if (is_file ($meskin.$fileconf.'.inc')) {
    include $meskin.$fileconf.'.inc';
} else {
    include CHEMIN.'skins/no_skin/'.$fileconf.'.inc';
}

if ($regit == 1) {
	$lst = '';
    foreach (array(BOX_LSIDE, BOX_LEFT, BOX_CENTER, BOX_RIGHT, BOX_RSIDE) as $key) {
		foreach ($xpb[BOX_EDITO][$key] as $i => $value) {
			if (!empty($value) && empty($wpb[BOX_EDITO][$key][$i])) { 
				$lst .= ' - [ '.BOX_EDITO.' ] [ '.$key.' ] [ '.$i.' ] => '.getLabelBox($boxes, substr($value, 2)).'\n';
                $wpb[BOX_EDITO][$key][$i] = '';
            }
		}
	}
	if ($lst == '') {
		$messreg = ' : &nbsp;OK';
	} else {
		echo BeginJavascript().'alert("'.addslashes($admin[706]).'\n\n'.$lst.'");'.EndJavascript();
	}
	
	$meskin = CHEMIN.'skins/'.$selskin.'/';
	if (is_file ($meskin.$fileconf.'.inc')) {
		include $meskin.$fileconf.'.inc';
	} else {
		include CHEMIN.'skins/no_skin/'.$fileconf.'.inc';
	}
	
	$site[4]     = import('site4'); // Nouvelles sur la page d'accueil
	$site[20]    = import('site20'); // Messages Forum sur la page d'accueil
	$site[29]    = import('site29'); // Hauteur en pixels des nouvelles en page d'accueil (0 = tous)
	$site[33]    = import('site33'); // Hauteur en pixels des billets du blog en page d'accueil (0 = tous)
	$site[34]    = import('site34'); // Nombre de caractères des posts forum en page d'accueil (0 = tous)
	$site[31]    = import('site31'); // Blogs sur la page d'accueil
    $serviz[74]  = import('serviz74'); // Cacher les boîtes en page d'accueil ON/OFF
	
	$xposbox[BOX_BLOG][BOX_LEFT]  = $xpb[BOX_BLOG][BOX_LEFT];
	$xposbox[BOX_BLOG][BOX_RIGHT] = $xpb[BOX_BLOG][BOX_RIGHT];
    foreach (array(BOX_LSIDE, BOX_LEFT, BOX_CENTER, BOX_RIGHT, BOX_RSIDE) as $key) {
		foreach ($xpb[BOX_EDITO][$key] as $i => $value) {
			$args  = '';
			if (!empty($value)) {
				$wb   = intval($wpb[BOX_EDITO][$key][$i]);
				$pos  = strrpos($value, CONNECTOR, 2);
				$args = $wb == 0 ? '' : ($pos == 0 ? CONNECTOR : '').'W'.$wb;
			}
			$xpb[BOX_EDITO][$key][$i] = $value.$args;
		}
		$xposbox[BOX_EDITO][$key] = $xpb[BOX_EDITO][$key];
	}
	unset($xpb, $wpb);
	
	include 'inc/'.$fileconf.'.inc';
    if ($site[7] == 'on' || PHP700) {
        @ob_flush();
        sleep(PHP700 ? 3 : $site[8]);
        echo BeginJavascript().'window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=config3";'.EndJavascript();
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

include CONFIG;
$conf = !isset($templates[$conf]) ? 0 : $conf;

TestPosBoxes($xposbox[BOX_EDITO][BOX_LSIDE], 5, 2);
TestPosBoxes($xposbox[BOX_EDITO][BOX_LEFT], 5, 2);
TestPosBoxes($xposbox[BOX_EDITO][BOX_CENTER], 5, 2);
TestPosBoxes($xposbox[BOX_EDITO][BOX_RIGHT], 5, 2);
TestPosBoxes($xposbox[BOX_EDITO][BOX_RSIDE], 5, 2);
TestSizeBoxes($xposbox[BOX_EDITO][BOX_LSIDE], $xposbox[BOX_EDITO][BOX_LEFT], $xposbox[BOX_EDITO][BOX_CENTER], $xposbox[BOX_EDITO][BOX_RIGHT], $xposbox[BOX_EDITO][BOX_RSIDE]);

TestPosBoxes($xposbox[BOX_BLOG][BOX_LEFT], 5, 2);
TestPosBoxes($xposbox[BOX_BLOG][BOX_RIGHT], 5, 2);
TestSizeBoxes($xposbox[BOX_BLOG][BOX_LEFT], $xposbox['B'][BOX_RIGHT]);

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[470]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[470], '100%');

include 'plugins/plugbox.inc';

$skn   = isset($selskin) ? '&amp;selskin='.$selskin : 'no_skin';
$skins = ExploreDir('skins/');

echo '
<div style="text-align:center">
  <p>'.ToHelp($lng, '&pg=952&mnuid=972&tconfig=0#z2').$admin[471].'</p>
</div>

<div  style="text-align:center">';
$confstruct = 'web';

echo '
  <hr />
  <div class="table-responsive-md">
  <table class="table text-left w-100">
    <tr>
      <td class="forum" style="vertical-align:top; text-align:left; width:50%; padding:5px 20px;">
	    '.$admin[725].' : <strong>'.$userprefs[10].$messreg.'</strong>
	  </td>
      <td class="forum text-center align-top"">
	    '.$admin[707].'
        <form name="selskns" action="admin.php?lng='.$lng.'&amp;pg=config3" method="post">
          <input type="hidden" name="regit" value="2" />
          <input type="hidden" name="selskin" value="'.$selskin.'" /><br />    
	      <select class="form-control mx-auto w-100" name="skns[]" style="max-width:200px;" size="10" multiple="multiple">';

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
</div>
<div class="mx-auto w-100">
  <form name="conf3" action="admin.php?lng='.$lng.'&amp;pg=config3" method="post">
    <input type="hidden" name="regit" value="1" />
    <div class="row quest p-0 mb-2 w-100 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
    </div>
    <fieldset>'.legend($admin[1077]).'
      <div class="table-responsive-md">
      <table class="table text-center w-100 m-auto">
        <tr>
          <td style="padding: 0px;">';
WriteColumnSelectBoxes('xpb['.BOX_EDITO.']['.BOX_LSIDE.']', $xposbox[BOX_EDITO][BOX_LSIDE], SelectBoxes($boxes, BOX_EDITO), true, true, 'wpb['.BOX_EDITO.']['.BOX_LSIDE.']', $wposbox[BOX_EDITO][BOX_LSIDE]);
echo '
          </td>
          <td style="padding: 0px;">';
WriteColumnSelectBoxes('xpb['.BOX_EDITO.']['.BOX_LEFT.']', $xposbox[BOX_EDITO][BOX_LEFT], SelectBoxes($boxes, BOX_EDITO), true, true, 'wpb['.BOX_EDITO.']['.BOX_LEFT.']', $wposbox[BOX_EDITO][BOX_LEFT]);
echo '
          </td>
          <td style="padding: 0px;">';
WriteColumnSelectBoxes('xpb['.BOX_EDITO.']['.BOX_CENTER.']', $xposbox[BOX_EDITO][BOX_CENTER], SelectBoxes($boxes, BOX_EDITO), true, true, 'wpb['.BOX_EDITO.']['.BOX_CENTER.']', $wposbox[BOX_EDITO][BOX_CENTER]);
echo '
          </td>
          <td style="padding: 0px;">';
WriteColumnSelectBoxes('xpb['.BOX_EDITO.']['.BOX_RIGHT.']', $xposbox[BOX_EDITO][BOX_RIGHT], SelectBoxes($boxes, BOX_EDITO), true, true, 'wpb['.BOX_EDITO.']['.BOX_RIGHT.']', $wposbox[BOX_EDITO][BOX_RIGHT]);
echo '
          </td>
          <td style="padding: 0px;">';
WriteColumnSelectBoxes('xpb['.BOX_EDITO.']['.BOX_RSIDE.']', $xposbox[BOX_EDITO][BOX_RSIDE], SelectBoxes($boxes, BOX_EDITO), true, true, 'wpb['.BOX_EDITO.']['.BOX_RSIDE.']', $wposbox[BOX_EDITO][BOX_RSIDE]);
echo '
          </td>
        </tr>
      </table>
      </div>
    </fieldset>
	
    <fieldset>'.legend($admin[770]).'
      <div class="table-responsive-md">
      <table class="table text-center w-100 border-0">
        <tr>
          <td style="vertical-align:top;width:30%;">
            <fieldset>'.legend($admin[337]);
WriteColumnSelectBoxes('xpb['.BOX_BLOG.']['.BOX_LEFT.']', $xposbox[BOX_BLOG][BOX_LEFT], SelectBoxes($boxes, BOX_LEFT), true, true);
echo '
            </fieldset>
          </td>
          <td style="vertical-align:top;width:40%;">
            <p class="quest" style="text-align:center;line-height:75px;margin:92px 0 0;font-weight:bold;font-size:1.5em;">'.$admin[878].'</p>
          </td>
          <td style="vertical-align:top;width:30%;">
            <fieldset>'.legend($admin[338]);
WriteColumnSelectBoxes('xpb['.BOX_BLOG.']['.BOX_RIGHT.']', $xposbox[BOX_BLOG][BOX_RIGHT], SelectBoxes($boxes, BOX_RIGHT), true, true);
echo '
            </fieldset>
          </td>
        </tr>
      </table>
      </div>
    </fieldset>
	
    <fieldset>
      <div class="table-responsive-md">
      <table class="table w-100">';
echo '
        <tr style="text-align:left;">
          <td class="w-20">&nbsp;</td>
          <td class="w-40"><label for="serviz74">&bull; '.$admin[1078].'</label></td>
          <td><input class="checkbox" type="checkbox" id="serviz74" name="serviz74"'.Checked($serviz[74]).' /></td></tr>
        <tr style="text-align:left;">
          <td class="w-20">&nbsp;</td>
          <td>&bull; '.$admin[58].'</td>
          <td><input class="form-control w-40 texte" type="text" name="site4" size="3" value="'.$site[4].'" /></td></tr>
        <tr style="text-align:left;">
          <td class="w-20">&nbsp;</td>
          <td>&bull; '.$admin[778].'</td>
          <td><input class="form-control w-40 texte" type="text" name="site31" size="3" value="'.$site[31].'" /></td></tr>
        <tr style="text-align:left;">
          <td class="w-20">&nbsp;</td>
          <td>&bull; '.$admin[684].'</td>
          <td><input class="form-control w-40 texte" type="text" name="site29" size="7" value="'.$site[29].'" /></td></tr>
        <tr style="text-align:left;">
          <td class="w-20">&nbsp;</td>
          <td>&bull; '.$admin[1469].'</td>
          <td><input class="form-control w-40 texte" type="text" name="site33" size="7" value="'.$site[33].'" /></td></tr>
        <tr style="text-align:left;">
          <td class="w-20">&nbsp;</td>
          <td>&bull; '.$admin[1470].'</td>
          <td><input class="form-control w-40 texte" type="text" name="site34" size="7" value="'.$site[34].'" /></td></tr>
        <tr style="text-align:left;">
          <td class="w-20">&nbsp;</td>
          <td>&bull; '.$admin[463].'</td>
          <td><input class="form-control w-40 texte" type="text" name="site20" size="3" value="'.$site[20].'" /></td></tr>
      </table>
      </div>
    </fieldset>
    <div class="row quest p-0 mt-2 w-100 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
    </div>
  </form>
</div>';
btable();
include(CHEMIN.'admin/bpage.inc');
?>