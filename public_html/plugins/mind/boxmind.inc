<?php
/*******************************************************************************
 *   Mind Plugin
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2022 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = http://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V5 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 * v6.00.08 (March 10, 2022)    : adaptation PHP 8 et 8.1
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'boxmind.inc')) {
    header('location:../index.php');
    die();
}

function fbox_mind($pos, $args) {
    global $lng, $lang, $site, $tconfig, $z2;

    if (is_file(CHEMIN.'plugins/mind/'.$lng.'-mind.inc')) {
        include CHEMIN.'plugins/mind/'.$lng.'-mind.inc';
    } else {
        include CHEMIN.'plugins/mind/en-mind.inc';
    }

    $out = '
<form action="'.CHEMIN.'plugins/mind/mind.php?lng='.$lng.'&amp;guess=0'.$z2.'" method="post" name="formMind">
  <p class="box" style="text-align:center">
    '.$mind[3].'
    <br />
    '.SubmitButton($mind[9]).'
  </p>
</form>';

    if ($pos == BOX_LEFT || $pos == BOX_RIGHT) {
        return '
<div class="'.$pos.$tconfig.'MIND">'.boxthin($pos, $mind[7], $out, ' MIND'.$tconfig).'
</div>';
    } else {
        return boxwide($pos, $mind[7], $out, ' MIND'.$tconfig);
    }
}
?>
