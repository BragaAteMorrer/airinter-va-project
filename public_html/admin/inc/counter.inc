<?php
/*******************************************************************************
 *   Admin Counter
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
 * v6.00.19 (December 15, 2024) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'counter.inc')) {
      header('location:../index.php');
      die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[31] == '') {
        $nomzone = $admin[17];
        include 'access.inc';
        exit();
    }
}

$actlog  = import('actlog', 'GET');
$visites = import('visites');
$numeric = import('numeric');
$action  = import('action', 'POST');

if ($action == 'go') {
	$nblog   = import('nblog');
    $tmplog1 = file(DBLOGSTATS);
    $tmplog2 = array();
	$tmplog0 = array();
    for($i = max(0, count($tmplog1) - $nblog); $i < count($tmplog1); $i++) {
  //      $tmplog2[][0] = trim($tmplog1[$i]);
		$dblog2 = explode(';', $tmplog1[$i]);
		$tmplog0[$i][0] = $dblog2[0].';';
		$tmplog0[$i][1] = $dblog2[1];
		$tmplog2[][0] = trim($tmplog0[$i][0].$tmplog0[$i][1]);
    }
    WriteDBFields(DBLOGSTATS, $tmplog2);
}

if ($visites == 1) {
    if ($numeric < 1) {
        $numeric = 1;
    }
    WriteCounter(DBSTATS,$numeric);
    WriteCounter(DBSTATSBK,$numeric);
    $tmplog1 = file(DBLOGSTATS);
    $tmplog2 = array();
    for($i = 0; $i < count($tmplog1)-1; $i++) {
  //      $tmplog2[][0] = trim($tmplog1[$i]);
		$dblog2 = explode(';', $tmplog1[$i]);
		$tmplog0[$i][0] = $dblog2[0].';';
		$tmplog0[$i][1] = $dblog2[1];
		$tmplog2[][0] = trim($tmplog0[$i][0].$tmplog0[$i][1]);
    }
    WriteDBFields(DBLOGSTATS, $tmplog2);
    WriteCounter(DBLOGDATE, '');
}
$pgvues = ReadCounter(DBSTATS);

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[141]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[141], '100%');
echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=1002&mnuid=937&tconfig=0#z2').'</p>';

if (!empty($actlog)) {
	switch($actlog) {
	case 'vider' :
		$mess = $admin[754];
		$nblog = 2;
		break;
	case 'reduire' :
		$mess = $admin[755];
		$nblog = 9;
		break;
	}
	echo '
<div class="text-center mt-5">
  <form name="confactlog" action="admin.php?lng='.$lng.'&amp;pg=counter" method="POST">
    <input type="hidden" name="action" value="go">
    <input type="hidden" name="nblog" value="'.$nblog.'">
    <fieldset class="text-center w-100 m-auto" style="max-width:300px;">';
    echo '<p>'.$mess.'</p><p><strong>'.strtoupper($admin[993]).' : '.$admin[48].'</strong></p>
      <span style="margin-right:6px;">
        '.SubmitButton($admin[1605]).'
      </span>
      <span style="margin-left:6px;">
        '.SubmitButton($admin[1604], 'self.location.href=\'admin.php?lng='.$lng.'&pg=counter\'', '', '', 'button').'
      </span>
    </fieldset>
  </form>
</div>';
}

echo '
<div class="text-center mt-5">
  <br />
  <form action="admin.php?lng='.$lng.'&amp;pg=counter" method="post">
    <input type="hidden" name="visites" value="1"/>
    <div class="table-responsive-md">
    <table class="table m-auto">
    <tr><td style="text-align:center;">'.$admin[142].'</td></tr>
    <tr>
      <td style="text-align:center;">
        <div class="d-inline-block"><input class="form-control texte" type="text" name="numeric" size="6" value="'.$pgvues.'"/></div>
        <div class="d-inline-block ps-2">'.SubmitButton($admin[144], '', $imggauche, '').'</div>
      </td>
    </tr>
    </table>
    </div>
  </form>';
if (is_file(DBLOGSTATS)) {
	function DiffDate($d1, $d2) {
		list($j1, $m1, $a1) = explode('/', $d1);
		list($j2, $m2, $a2) = explode('/', $d2);
		$result = (mktime(0, 0, 0, (int)$m1, (int)$j1, (int)$a1) - mktime(0, 0, 0, (int)$m2, (int)$j2, (int)$a2))/86400;
		return max(1, $result);
	}
	$dblog1 = file(DBLOGSTATS);
	if (count($dblog1) > 2) {
		for($i=0; $i<count($dblog1); $i++) {
    		$dblog2 = explode(';', $dblog1[$i]);
    		$dblog3[$i][0] = $dblog2[0];
    		$dblog3[$i][1] = $dblog2[1];
    		$dblog3[$i][2] = ($i == 0 ? 0 : ($dblog2[1] - $dblog3[$i - 1][1])/DiffDate($dblog2[0], $dblog3[$i - 1][0]));
		}
		for($i=1; $i<count($dblog3); $i++) {
			$dblog3[$i][2] = ($dblog3[$i][1] - $dblog3[$i - 1][1])/DiffDate($dblog3[$i][0],$dblog3[$i - 1][0]);
			if ($dblog3[$i][2] < 0) {
				$dblog3[$i][1] = $dblog3[$i-1][1] + $dblog3[$i-1][2] * (DiffDate($dblog3[$i][0],$dblog3[$i - 1][0]));
				$dblog3[$i][2] = $dblog3[$i-1][2];
				$dblog3[$i][3] = ' style="color:red"';
				$dblog3[$i][4] = CHEMIN.'inc/img/bars/rouge.gif';
			}
			else {
				$dblog3[$i][3] = '';
				$dblog3[$i][4] = CHEMIN.'inc/img/bars/vert.gif';
			}
		}
		$countmin = 999999999;
		for ($i = count($dblog3)-1; $i > max(count($dblog3)-16, 0); $i--) {
			$countmin = min($countmin, $dblog3[$i][1]);
		}
		$countmax = $dblog3[count($dblog3)-1][1];
		echo '
  <p>&nbsp;</p><hr />
  <div class="table-responsive-md">
  <table class="table w-98 m-auto" style="margin-right:120px;">';
		echo '
	<tr><td colspan="3" style="text-align:center;">'.$admin[750].'</td></tr>';
		echo '
	<tr style="text-align:center;">
	  <td style="width:15%;">'.$admin[751].'</td>
	  <td style="width:15%;">'.$admin[752].'</td>
	  <td style="width:70%;">'.$admin[753].'</td>
	</tr>';
		$diff = 0;
		for ($i = count($dblog3)-1; $i > max(count($dblog3)-16, 0); $i--) {
			$pourcent = 4 + floor(100 * ($dblog3[$i][1] - $countmin) / ($countmax - $countmin));
			$diff += $dblog3[$i][2];
			echo '
	<tr>
	  <td style="text-align:center;">'.$dblog3[$i][0].'</td>
	  <td style="text-align:center;"'.$dblog3[$i][3].'>'.$dblog3[$i][1].'</td>
	  <td style="text-align:left;">
	    <img src="'.$dblog3[$i][4].'" style="height:10px;width:'.$pourcent.'%;" alt="'.$pourcent.'%" />
	  </td>
	</tr>';
		}
		echo '
  </table>
  </div>';
		echo '
  <p style="text-align:center;">
    '.(empty($actlog) || $actlog == 'reduire' 
      ? SubmitButton($admin[754], 'self.location.href=\'admin.php?lng='.$lng.'&pg=counter&actlog=vider\'', '', '', 'button') : '').'      
    '.(empty($actlog) || $actlog == 'vider' 
      ? SubmitButton($admin[755], 'self.location.href=\'admin.php?lng='.$lng.'&pg=counter&actlog=reduire\'', '', '', 'button') : '').'
  </p>
  <hr />';
	}
}
echo '
</div>';

btable();
include CHEMIN."admin/bpage.inc";
?>
