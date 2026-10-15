<?php
/*******************************************************************************
 *   Checking install dir
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
 * v6.00.00 (December 15, 2020) : initial release
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'check_save.inc')) {
    header('location:../index.php');
    die();
}

if (1 != preg_match('!^[localhost]!', $site[3]) && 1 != preg_match('^[!127.0.0.1]!', $site[3])) {
    echo '
<div id="note" class="bord notice" style="position:absolute; top:100px; left:50%; width:600px; margin-left:-280px; padding:0; z-index:100;">
  <div style="float:left; padding:20px;">
    <i class="gen-attention" title="'.$admin[993].' !!!"></i>
  </div>
  <div style="float:left; width:440px; padding:20px 0;">
    <p style="text-align:center; text-decoration:blink; font-weight:bold; font-size:2em;"> *** '.$admin[993].' *** </p>
    <hr />
    <p style="text-align:center; font-weight:bold; font-size:1.2em;">'.$admin[1473].'</p>
  </div>
  <div style="clear:both;">
    <hr />
    <p style="text-align:right; padding:0 10px 5px ;">
      <a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg=upload&amp;rep=save" title="'.$admin[186].$admin[1252].'">
        <i class="far fa-trash-alt align-middle"></i>
      </a> &nbsp;
      <a href="#" onclick="document.getElementById(\'note\').style.visibility = \'hidden\'" title="'.$admin[458].'">
        <i class="fadm-sup align-bottom"></i>
      </a>
    </p>
  </div>
</div>';
}
?>