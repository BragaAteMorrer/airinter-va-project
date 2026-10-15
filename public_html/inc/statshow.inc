<?php
/*******************************************************************************
 *   Statistics Reporting
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
 * v6.00.19 (August 26, 2024) : modification to best views of statistics
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'statshow.inc')) {
  header('location:../index.php');
  die();
}

$per = import('per');
$anal = import('anal');

if (empty($per)) {
    $per = 1;
}
echo '
<form name="chgestat" action="'.(CHEMIN == '' ? 'stats.php?lng='.$lng.'&amp;tconfig='.$tconfig.$z2 : 'admin.php?lng='.$lng.'&amp;pg=statsvw').'" method="post">
<div class="table-responsive-md">
<table class="table m-auto text-center">
  <tr>
    <td class="tdstats"><b>'.$web[71].'</b></td>
    <td class="tdstats">
      <select class="form-control" style="max-width:200px;" name="per">
        <optgroup label="'.$web[447].'">
        <option value="1"'.Selected($per == 1).'>'.$web[72].'</option>
        <option value="2"'.Selected($per == 2).'>'.$web[73].'</option>
        <option value="3"'.Selected($per == 3).'>'.$web[74].'</option>
        </optgroup>';
$foptions = ListStatsFiles();
if (!empty($foptions)) {
    echo '
        <optgroup label="'.$web[448].'">';
    foreach ($foptions as $name => $value) {
    echo '
        <option value="'.$value.'"'.Selected($per === $value).'>'.$name.'</option>';
    }
    echo '
        </optgroup>';
}
echo '
      </select>
    </td>
  </tr>
  <tr>
    <td class="tdstats"><b>'.$web[76].'</b></td>
    <td class="tdstats">
      <select class="form-control" style="max-width:260px;" name="anal">
        <option value="1"'.Selected($anal == 1).'>'.$web[77].'</option>
        <option value="2"'.Selected($anal == 2).'>'.$web[78].'</option>
        <option value="3"'.Selected($anal == 3).'>'.$web[79].'</option>
      </select>
    </td>
  </tr>
  <tr class="text-center">
    <td class="tdstats" colspan="2">'.SubmitButton($web[80]).'</td>
  </tr>
</table>
</div>
</form>';

if (!empty($anal)) {
    $tmp = explode(',', $per);
    $per = $tmp[0];
    $file = isset($tmp[1]) ? $tmp[1] : '';
    echo '
<hr />';
}

if ($anal == 1) {
    switch($per) {
    case 1 : $result = AnalyzeDay(CalcDay($file));     $txt = $web[81]; break;
    case 2 : $result = AnalyzeMonth(CalcMonth($file)); $txt = $web[82]; break;
    case 3 : $result = AnalyzeYear(CalcYear($file));   $txt = $web[83]; break;
    }
    echo '
<p class="quest text-center"><b>'.$txt.'</b></p>
<div class="table-responsive w-90">
<table class="table m-auto text-center">
  <tr>';

    $bar = 200/$result['max1'];
    for ($k = 0; $k < count($result['tab1']); $k++) {
        echo '
<style>
.vertbarre1'.$k.' {
    width: 10px;
    margin: 0 2px;
    height: '.round($result['tab1'][$k][0]*$bar).'px;
}
</style>
    <td class="align-bottom text-center vertbarStat">
      '.$result['tab1'][$k][0].'<br />
        <img src="'.CHEMIN.'inc/img/bars/vertbleu.gif" class="vertbarre1'.$k.'" alt="'.$result['tab1'][$k][0].'"/>
    </td>';
    }
    echo '
  </tr>
  <tr>';
    for ($k = 0; $k < count($result['tab1']); $k++) {
        echo '
    <td class="align-bottom text-center vertbarStat">
      <b>'.$result['tab1'][$k][1].'</b>
    </td>';
    }
    switch($per) {
    case 1 : $txt = $web[84]; break;
    case 2 : $txt = $web[85]; break;
    case 3 : $txt = $web[86]; break;
    }
    echo '
  </tr>
</table>
</div>
<br />
<p class="quest text-center"><b>'.$txt.'</b></p>
<div class="table-responsive w-90">
<table class="table m-auto text-center">
  <tr>';

    $bar = 200/$result['max2'];
    for ($k = 0; $k < count($result['tab2']); $k++) {
        echo '
<style>
.vertbarre2'.$k.' {
    width: 10px;
    margin: 0 2px;
    height: '.round($result['tab2'][$k][0]*$bar).'px;
}
</style>
    <td class="align-bottom text-center vertbarStat">
      '.$result['tab2'][$k][0].'<br />
        <img src="'.CHEMIN.'inc/img/bars/vertbleu.gif" class="vertbarre2'.$k.'"/>
    </td>';
    }
    echo '
  </tr>
  <tr>';
    for ($k = 0; $k < count($result['tab2']); $k++) {
        echo '
    <td class="align-bottom text-center vertbarStat">
      <b>'.$result['tab2'][$k][1].'</b>
    </td>';
    }
    echo '
  </tr>
</table>
</div>';
}

elseif ($anal == "2") {
    switch($per) {
    case 1 : $result = AnalyzeLang(CalcDay($file));  break;
    case 2 : $result = AnalyzeLang(CalcMonth($file));    break;
    case 3 : $result = AnalyzeLang(CalcYear($file)); break;
    }
    echo '
<p class="quest text-center"><b>'.$web[87].'</b></p>
<div class="table-responsive w-90">
<table class="table m-auto text-left">';
    $bar = 250/$result['max1'];
    for ($k = 0; $k < count($result['tab1']); $k++) {
        if ($result['tab1'][$k][0] > 0) {
            echo '
<style>
.horizbarre1'.$k.' {
    width: '.round($result['tab1'][$k][0]*$bar).'px;
    height: 10px;
    margin: 0 5px;
}
</style>
  <tr>
    <td class="horiztxtStat"><b>'.$result['tab1'][$k][1].' </b></td>
    <td class="horizbarStat">
      <img src="'.CHEMIN.'inc/img/bars/vert.gif" class="horizbarre1'.$k.'" alt="'.$result['tab1'][$k][1].'"/> '.$result['tab1'][$k][0].'
    </td>
  </tr>';
        }
    }
    echo '
</table>
</div>
<br />
<p class="quest text-center"><b>'.$web[88].'</b></p>
<div class="table-responsive w-90">
<table class="table m-auto text-left">';
    $bar = 250/$result['max2'];
    for ($k = 0; $k < count($result['tab2']); $k++) {
        if ($result['tab2'][$k][0] > 0) {
            echo '
<style>
.horizbarre2'.$k.' {
    width: '.round($result['tab2'][$k][0]*$bar).'px;
    height: 10px;
    margin: 0 5px;
}
</style>
  <tr>
    <td class="horiztxtStat"><b>'.$result['tab2'][$k][1].' </b></td>
    <td class="horizbarStat">
      <img src="'.CHEMIN.'inc/img/bars/orange.gif" class="horizbarre2'.$k.'" alt="'.$result['tab2'][$k][1].'" /> '.$result['tab2'][$k][0].'
    </td>
  </tr>';
        }
    }
    echo '
</table>
</div>
<br />';
}

elseif ($anal == "3") {
    switch($per) {
    case 1 : $result = AnalyzeNavOs(CalcDay($file));  break;
    case 2 : $result = AnalyzeNavOs(CalcMonth($file));    break;
    case 3 : $result = AnalyzeNavOs(CalcYear($file)); break;
    }
    echo '
<p class="quest text-center"><b>'.$web[89].'</b></p>
<div class="table-responsive w-90">
<table class="table m-auto text-left">';
    $bar = 250/$result['max1'];
    for ($k = 0; $k < count($result['tab1']); $k++) {
        if ($result['tab1'][$k][0] > 0) {
            echo '
<style>
.horizbarre1'.$k.' {
    width:'.round($result['tab1'][$k][0]*$bar).'px;
    height: 10px;
    margin: 0 5px;
}
</style>
  <tr>
    <td class="horiztxtStat"><b>'.$result['tab1'][$k][1].' </b></td>
    <td class="horizbarStat">
      <img src="'.CHEMIN.'inc/img/bars/rouge.gif" class="horizbarre1'.$k.'" alt="'.$result['tab1'][$k][1].'" /> '.$result['tab1'][$k][0].'
    </td>
  </tr>';
        }
    }
    echo '
</table>
</div>
<br />
<p class="quest text-center"><b>'.$web[90].'</b></p>
<div class="table-responsive w-90">
<table class="table m-auto text-left">';
    $bar = 250/$result['max2'];
    for ($k = 0; $k < count($result['tab2']); $k++) {
        if ($result['tab2'][$k][0] > 0) {
            echo '
<style>
.horizbarre2'.$k.' {
    width: '.round($result['tab2'][$k][0]*$bar).'px;
    height: 10px;
    margin: 0 5px;
}
</style>
  <tr>
    <td class="horiztxtStat"><b>'.$result['tab2'][$k][1].' </b></td>
    <td class="horizbarStat">
      <img src="'.CHEMIN.'inc/img/bars/bleu.gif" class="horizbarre2'.$k.'" alt="'.$result['tab2'][$k][1].'" /> '.$result['tab2'][$k][0].'
    </td>
  </tr>';
        }
    }
    echo '
</table>
</div>';
}

switch($anal) {
    case 1 :
        echo '
<br />
<p>'.$web[449].'</p>';
        break;
    case 2 :
    case 3 :
        echo '
<br />
<p>'.$web[510].'</p>';
        break;
}
?>