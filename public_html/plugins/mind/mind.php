<?php
/*******************************************************************************
 *   Mind Plugin
 *******************************************************************************
 *   GuppY PHP Script - version 5.0
 *   CeCILL Copyright (C) 2004-2014 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = http://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V5 developed by Lud Bienaimé
 *      with the participation of Jean-Michel Misrachi and the GuppY Team
 *******************************************************************************
 *   Version History :
 * v5.0.0 (February 15, 2014) : initial release.
 ******************************************************************************/

@session_set_cookie_params(0, '/', $_SERVER['HTTP_HOST'], false, ($_SERVER["HTTPS"] == 'on' ? true : false));
@session_name('GuppYSite');
session_start();
header('Pragma: no-cache');
define('CHEMIN', '../../');
include CHEMIN.'inc/includes.inc';

if (is_file(CHEMIN.'plugins/mind/'.$lng.'-mind.inc')) {
    include CHEMIN.'plugins/mind/'.$lng.'-mind.inc';
} else {
    include CHEMIN.'plugins/mind/en-mind.inc';
}


$alphaArray = array('a', 'n', 'b', 'd', 'f', 'h', '{', 'i', 'l', 'v', 'x', 'z', 'I', 'J', 'M', 'N', 'o', 'O', 'R', 'S', 'T', 'U', 'm', '6', '^', 'u', '_', '[', ']');

function init_seed() {
  list($usec, $sec) = explode(' ', microtime());
  return (float) $sec + ((float) $usec * 100000);
}

function ChooseMind() {
  srand(init_seed());
  $ax = rand(0,26);
  return $ax;
}

function WriteMindTable($ax) {
    global $alphaArray;
    $tablo = '
<table border="0" cellspacing="1" cellpadding="1" width="100%" summary="">';
    $j = 1;
    for ($i = 99 ; $i >= 0 ; $i-- ) {
        $a = rand(0,26);
        if ($i % 9 == 0 &&  $i < 89) {
            $a = $ax;
        }
        if ($j % 10 == 1) {
            $tablo .= '
  <tr>';
        }
        $tablo .= '
    <td style="color:red;font-family:Verdana">'.$i.'</td>
    <td style="color:blue;font-family:Windings">'.$alphaArray[$a].'</td>';
        if ($j % 10 == 0) {
            $tablo .= '
  </tr>';
        }
        $j++;
    }
    $tablo .= '
</table>';
    return $tablo;
}

$topmess = '<img src="mind.gif" style="width:32px;height:32px;float:right;" alt="" />'.$mind[10];
include CHEMIN.'inc/hpage.inc';
htable($topmess, "100%");

$guess = import('guess');

if ($guess == 0) {
    $rndax = ChooseMind();
    echo '
<p>'.$mind[3].'</p>
<p>
  '.$mind[11].'<br />
  '.$mind[12].'<br />
  '.$mind[13].'<br />
  '.$mind[14].'<br /><br />
</p>
'.WriteMindTable($rndax).'
<form name="guessmind1" action="mind.php?lng='.$lng.'&amp;guess='.($rndax+1).$z2.'" method="post">
  <div style="text-align:center;">
  '.SubmitButton($mind[15]).'<br />
  </div>
</form>';
}
else {
    echo '
<p>
  '.$mind[16].'
  <span style="font-size:2em;color:blue;background-color:yellow;font-policy:wingding;">&nbsp;'.$alphaArray[$guess-1].'&nbsp;</span>
</p>
<form name="guessmind2" action="mind.php?lng='.$lng.'&amp;guess=0'.$z2.'" method="post">
  <div style="text-align:center;">
    '.SubmitButton($mind[17]).'<br />
  </div>
</form>';
}

btable();
include CHEMIN.'inc/bpage.inc';
?>
