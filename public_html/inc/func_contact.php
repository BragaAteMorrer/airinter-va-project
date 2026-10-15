<?php
/*******************************************************************************
 *   Functions Advanced contact
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

if (stristr($_SERVER["SCRIPT_NAME"], "func_contact.php")) {
  header("location:../index.php");
  die();
}

function ItemName($description, $name0, $value0, $name1, $value1) {
    global $flag1, $flag2, $lang, $charset;
	echo '
<div style="text-align:center;width:520px;padding-bottom:8px;">
  <div style="width:140px;float:left;position:relative;top:12px;padding-left:12px;text-align:left;">&bull; '.$description.'</div>
  <div style="width:240px;float:left;position:relative;top:7px;left:260px;text-align:right;">
	<div>';
    if (!empty($lang[1])) {
		echo '
    <div class="d-inline-block"><i class="flg-'.$lang[0].' align-middle" title="'.$lang[0].'"></i></div>';
	}
    echo '
    <div class="d-inline-block">
      <input class="form-control texte" name="'.$name0.'" type="text" size="24" value="'.htmlentities($value0, ENT_QUOTES, $charset).'" />
    </div>
    </div>';
	if (!empty($lang[1])) {
		echo '
    <div>
	  <div class="d-inline-block"><i class="flg-'.$lang[1].' align-middle" title="'.$lang[1].'"></i></div>
      <div class="d-inline-block">
        <input class="form-control texte" name="'.$name1.'" type="text" size="24" value="'.htmlentities($value1, ENT_QUOTES, $charset).'" />
      </div>
    </div>';
	}
    echo '
  </div>';
	echo '
  <div class="clearfix"></div>';
	echo '
</div>';
}

function ItemCheck($area, $chk0) {
    global $admin;
	echo '
<div class="form-check" style="z-index:100;float:right;position:relative;top:14px;right:320px;width:216px;text-align:left;">';
    if ($area == 'chmail1' || $area == 'chmail2' || $area == 'chmes1') {
		echo '
  <input class="form-check-input" type="radio" name="'.$area.'" value="fix" checked="checked" />&nbsp;<span class="pl-3">'.$admin[1188].' (*)</span>';
    } else {
        echo '
  <input class="form-check-input" type="radio" name="'.$area.'" value=""'; 
        if ($chk0 == '') {
            echo ' checked="checked"';
        } 
        echo ' /><span class="px-3"> '.$admin[1604].'</span>';
        echo '
  <input class="form-check-input" type="radio" name="'.$area.'" value="on"'; 
        if ($chk0 == 'on') {
            echo ' checked="checked"';
        } 
        echo ' /><span class="px-3"> '.$admin[1605].'</span>';
        if ($area != 'chfic0'){
            echo '
  <input class="form-check-input" type="radio" name="'.$area.'" value="fix"'; 
            if ($chk0 == 'fix') {
                echo ' checked="checked"';
            } 
            echo ' />&nbsp;<span class="pl-3">'.$admin[1188].'</span>';
        }
    }
    echo '
</div>';
}
?>