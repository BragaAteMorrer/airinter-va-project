<?php
/*******************************************************************************
 *   Checking config is done
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

if (stristr($_SERVER['SCRIPT_NAME'], 'check_config.inc')) {
    header('location:../index.php');
    die();
}

echo '
<div id="note" class="bord notice" style="position:absolute; top:100px; left:50%; width:560px; margin-left:-280px; padding:0; z-index:100;">
  <div style="float:left; padding:20px;">
    <i class="gen-attention" title="'.$admin[993].' !!!"></i>
  </div>
  <div style="float:left; width:400px; padding:20px 0;">
    <p style="text-align:center; text-decoration:blink; font-weight:bold; font-size:2em;"> *** '.$admin[993].' *** </p>
    <hr />
    <p style="text-align:center; font-weight:bold; font-size:1.2em;"><br />'.$admin[1317].'</p>
  </div>
  <div style="clear:both;">
    <hr />
    <p style="text-align:right; padding:0 10px 5px ;">';
if ($site[3] != $realurl) {
	echo '
      <a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=config1" title="'.$admin[7].'">
        <i class="gen-config align-middle" title="'.$admin[7].'"></i>
      </a> &nbsp;';
}
echo '
    </p>
  </div>
</div>';
?>