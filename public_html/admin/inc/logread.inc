<?php
/*******************************************************************************
 *   Read Admin Logbook
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
 * v6.00.20 (October 24, 2024) : modify script 
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'logread.inc')) {
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

function CutField($field, $max = 15, $more = '...') {
	global $charset;
    $len = strlen($field);
    if ($len > $max) {
        return '<acronym title="'.htmlentities($field, ENT_COMPAT, $charset).'" class="cursorHelp">'.substr($field, 0, $max).$more.'</acronym>';
    } else {
        return $field;
    }
}

$headinc .= BeginJavascript().'
function ConfirmDeleteAll(url) {
    if (confirm("'.$admin[559].'")) location.href=url;
}'.EndJavascript();

$action = import('action');

if ($action == "delall") {
    DestroyDBFile(DBLOGBOOK);
	DestroyDBFile(DBCOUNTERLOGBK);
    echo BeginJavascript().'
window.location="admin.php?lng='.$lng.'&pg=logread";'.EndJavascript();
}

$amsglog = ReadDBFields(DBLOGBOOK);

$widepage = 'on';
$topmess  = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.$admin[1310];
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[1310], '100%');
echo '
<p style="text-align:center;">
  '.ToHelp($lng, '&pg=1018&mnuid=937&tconfig=0#z2').'<div class="mt-5 pt-2">'.$admin[557].' '.getIPadress($site[6] == 'on' ? (isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']): $_SERVER['REMOTE_ADDR']).'</div>
</p>';

if (empty($amsglog)) {
    echo '
<p style="text-align:center;"><i class="far fa-meh-rolling-eyes"></i><b>'.$admin[558].'</b></p>';
}
else {
    echo '
<div class="row quest p-0 mb-2 w-100 m-auto">
  <div class="col-md-6">'.SubmitButton($admin[7].' Log', 'self.location.href=\'admin.php?lng='.$lng.'&pg=logconfig\'', $imggauche, '', 'button').'</div>
  <div class="col-md-6">
    <div style="float:right">
      '.SubmitButton($admin[218], 'ConfirmDeleteAll(\'admin.php?lng='.$lng.'&amp;pg=logread&amp;action=delall\')', '', $imgdroite).'
    </div>
  </div>
</div>
<fieldset class="mx-0">'.legend(sprintf($admin[1380], min($configlogNB, count($amsglog)))).'
  <div class="table-responsive-md">
  <table class="table table-bordered w-100">
    <tr class="forum2" style="text-align:center;white-space:nowrap;height:2.5em;">
      <td class="align-middle font-weight-bold py-1 px-1 4px w-10"> '.$admin[521].' </td>
      <td class="align-middle font-weight-bold py-1 px-1 4px w-20"> '.$admin[518].' </td>
      <td class="align-middle font-weight-bold py-1 px-1 4px w-25"> '.$admin[560].' </td>
      <td class="align-middle font-weight-bold py-1 px-1 4px w-25"> '.$admin[986].' </td>
      <td class="align-middle font-weight-bold py-1 px-1 4px w-20"> '.$admin[561].' </td>
      <td class="align-middle font-weight-bold py-1 px-1 4px"> '.substr($admin[562], 0, 4).'...</td>
      <td class="align-middle font-weight-bold py-1 px-1 4px"> '.substr($admin[563], 0, 4).'...</td>
    </tr>';
    $nnav = array('netscape', 'msie', 'mozilla', 'da', 'opera', 'icab',
        'konqueror', 'robot', 'unknown', 'wget', 'avant browser', 'crazy',
        'maxthon', 'lynx', 'safari', 'seamonkey', 'psp', 'ps3', 'wii',
        'firefox', 'flock', 'iceweasel', 'chrome', 'camino', 'dillo',
        'element', 'epiphany', 'galeon', 'k-meleon', 'kazehakase', 'minifield'
        );
    $serviz[85] .= @$serviz[85][@strlen($serviz[85])] != '/' ? '/' : '';
    for ($i = count($amsglog)-1; $i >= 0; $i--) {
		if (isset($amsglog[$i][3])) {
			$imgflag = substr($amsglog[$i][3], 1 + strrpos($amsglog[$i][3], '.'));
			if (!CheckDB1Field(CHEMIN.'inc/lang/flags.inc', $imgflag, 0)) {
				$imgflag = 'unknown';
			}
		}
        else
            $imgflag = 'unknown';
 //       $xurl = str_replace('&', '&amp;', $amsglog[$i][2]);
		$xurl = rawurldecode(htmlspecialchars($amsglog[$i][2]));
        $xurl = empty($xurl) ? '&nbsp;' : '&nbsp;<a style="font-weight:normal;" href="'.$xurl.'" target="_blank" title="'.$xurl.'">'.CutField($xurl, 80).'</a>';
        $IP   = substr($amsglog[$i][1], 0, 18).(mb_strlen($amsglog[$i][1]) > 15 ? ' ...' : '');
        echo '
    <tr '.( $i % 2 ? 'class="rep"' : 'class="quest"').'">
      <td class="align-middle p-2 text-center" style="font-size:0.9em;">'.FormatDateSec($amsglog[$i][0]).'</td>
      <td class="align-middle p-2 text-center" style="font-size:0.9em;">
	    <a href="'.$serviz[85].$amsglog[$i][1].'" title="'.$amsglog[$i][1].'" target="_blank">'.$IP.'</a>
	  </td>';
	  $amsglog[$i][5] = rawurldecode(htmlspecialchars($amsglog[$i][5]));
	  $amsglog[$i][3] = rawurldecode(htmlspecialchars($amsglog[$i][3]));
	  echo '
      <td class="align-middle p-2" style="font-size:0.9em;">'.$xurl.'</td>
      <td class="align-middle p-2" style="font-size:0.9em;">'.(isset($amsglog[$i][5]) ? CutField($amsglog[$i][5], 80) : '').'</td>
      <td class="align-middle p-2" style="font-size:0.9em;">'.(isset($amsglog[$i][3]) ? CutField($amsglog[$i][3], 25) : '').'</td>
      <td class="align-middle p-2 text-center" style="font-size:0.9em;">
	    <i class="flg-'.$imgflag.'" title="'.$imgflag.'"></i>
	  </td>
      <td class="align-middle" style="font-size:0.9em;padding:0 2px;text-align:center;">';
		if (isset($amsglog[$i][4]))
			echo '
	    <i class="brw-'.$nnav[$amsglog[$i][4]].'" title="'.ucfirst($nnav[$amsglog[$i][4]]).'"></i>';
		else
			echo '
	    <i class="brw-unknown" title="Unknown"></i>';
		echo '
	  </td>
    </tr>';
    }
    echo '
  </table>
  </div>
</fieldset>
<div class="row quest p-0 mt-2 w-100 m-auto">
  <div class="col-md-6">'.SubmitButton($admin[7].' Log', 'self.location.href=\'admin.php?lng='.$lng.'&pg=logconfig\'', $imggauche, '', 'button').'</div>
  <div class="col-md-6">
    <div style="float:right">
      '.SubmitButton($admin[218], 'ConfirmDeleteAll(\'admin.php?lng='.$lng.'&amp;pg=logread&amp;action=delall\')', '', $imgdroite).'
    </div>
  </div>
</div>
<hr />';
}

btable();
include CHEMIN.'admin/bpage.inc';
?>
