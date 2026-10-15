<?php
/*******************************************************************************
 *   Admin Logbook Configuration
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
 * v6.00.19 (December 15, 2024) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'logconfig.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[35] == '') {
        $nomzone = $admin[553];
        include 'access.inc';
        exit();
    }
}

function GetBeginFieldset($name, $title, $droits = TRUE) {
    global $admin;
    return '
    <fieldset>'.
    legend('<input type="checkbox" id="'.$name.'" name="'.$name.'" title="'.$admin[893].'" style="vertical-align:middle"'.
    Checked($droits).' onclick="selectAll(this)" /> <label for="'.$name.'">'.$title.'</label>');
}

function GetDiv($index, $label) {
    global $configlog;
    return '
      <div style="float:left; width:170px; text-align:right; padding:4px 0;">
        <label for="cfglog'.$index.'">'.$label.'</label>
        <input id="cfglog'.$index.'" type="checkbox" name="cfglog['.$index.']"'.Checked($configlog[$index]).' style="vertical-align:middle" />
      </div>';
}

function GetEndFieldset() {
    return '
      <div class="clearfix"></div>
    </fieldset>
';
}

$headinc .= BeginJavascript().'
function selectAll(opt)
{
	var allSelected = opt.checked;
	var xId = new Array(0,1,2,3,4,5,6,7,8,10,11,12,13,14,15,16,17,18,19);
	if (xId.length != undefined) {
		for (i = 0; i < xId.length; i++) {
			document.forms["logconfig"].elements["cfglog[" + xId[i] + "]"].checked = (allSelected ? true : false);
		}
	}
	return false;
}'.EndJavascript();


$topmess  = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.$admin[1311];
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[1311], '100%');

if (isset($_POST['cfglogNB'])) {
    $configlogNB = $_POST['cfglogNB'];
    $configlog = isset($_POST['cfglog']) ? $_POST['cfglog'] : array();
    for ($i = 0; $i < 19; $i++) {
        if (!isset($configlog[$i])) {
            $configlog[$i] = '';
        }
    }
    include('inc/confupdt.inc');
}

echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=1016&mnuid=937&tconfig=0#z2').$admin[566].'></p>
<hr />';

$nblignes = array(10,25,50,75,100,150,200,250,300,400,500,750,1000);

echo '
<div class="w-100 m-auto" style="max-width:640px;">
  <form name="logconfig" action="admin.php?lng='.$lng.'&amp;pg=logconfig" method="post">
    <fieldset>
    <div class="table-responsive-md">
    <table class="table w-100 m-auto" style="max-width:250px;">
      <tr>
        <td style="padding:7px;">'.$admin[565].'</td>
        <td style="padding:7px;">
          <select class="form-control" name="cfglogNB">';
    foreach ($nblignes as $nb) {
        echo '
            <option value="'.$nb.'"'.Selected($configlogNB == $nb).'>'.$nb.'</option>';
    }
echo '
          </select>
        </td>
      </tr>
    </table>
    </div>'.
GetBeginFieldset('cfglogall', $admin[1047],
    $configlog[0] == 'on' && $configlog[1] == 'on' && $configlog[2] == 'on' &&
    $configlog[3] == 'on' && $configlog[4] == 'on' && $configlog[5] == 'on' &&
    $configlog[6] == 'on' && $configlog[7] == 'on' && $configlog[8] == 'on' &&
    $configlog[9] == 'on' && $configlog[10] == 'on' && $configlog[11] == 'on' &&
    $configlog[12] == 'on' && $configlog[13] == 'on' && $configlog[14] == 'on' &&
    $configlog[15] == 'on' && $configlog[16] == 'on' && $configlog[17] == 'on' &&
    $configlog[18] == 'on' && $configlog[19]
).
GetDiv( 0, $admin[1049]). // 0 = articles
GetDiv( 1, $admin[570]).  // 1 = dwnld
GetDiv( 2, $admin[572]).  // 2 = faq
GetDiv( 3, $admin[574]).  // 3 = forum
GetDiv( 4, $admin[573]).  // 4 = guestbk
GetDiv( 5, $admin[567]).  // 5 = accueil
GetDiv( 6, $admin[571]).  // 6 = liens
GetDiv( 7, $admin[568]).  // 7 = news
GetDiv( 8, $admin[569]).  // 8 = photo
GetDiv(10, $admin[1051]). // 10 = search
GetDiv(11, $admin[552]).  // 11 = stats
GetDiv(12, $admin[551]).  // 12 = admin
GetDiv(13, $admin[1048]). // 13 = blog
GetDiv(14, $admin[1052]). // 14 = newsletter
GetDiv(15, $admin[1053]). // 15 = news RSS
GetDiv(16, $admin[1054]). // 16 = blog RSS
GetDiv(17, $admin[1055]). // 17 = agenda
GetDiv(18, $admin[1056]). // 18 = plugins
GetDiv(19, $admin[1061]). // 19 = pages
GetEndFieldset().'

  </fieldset>
  <div class="row quest p-0 mt-2 w-100 m-auto">
    <div class="col-md-6">'.SubmitButton($admin[554], 'self.location.href=\'admin.php?lng='.$lng.'&pg=logread\'', $imggauche, '', 'button').'</div>
    <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
  </div>
  </form>
</div>';

btable();
include CHEMIN.'admin/bpage.inc';
?>