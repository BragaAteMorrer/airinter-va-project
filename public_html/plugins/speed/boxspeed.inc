<?php
/*******************************************************************************
 *   Plugin Speed
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2013 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.00 (December 15, 2019) : initial release
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "boxspeed.inc")) {
    header("location:../index.php");
    die();
}

function fbox_speed($pos, $args) {
    global $lng, $lang, $drtuser, $serviz, $userprefs, $web, $page, $tconfig;

    if (is_file(CHEMIN.'plugins/speed/'.$lng.'-speed.inc')) {
        include CHEMIN.'plugins/speed/'.$lng.'-speed.inc';
    } else {
        include CHEMIN.'plugins/speed/en-speed.inc';
    }

    $speedtitle = "<img src=\"".CHEMIN."plugins/speed/speed.gif\" align=\"right\" alt=\"speed.gif\" />".$speed[7];

    $out = BeginJavascript().'
var tjs_img;
var tjs_src="'.CHEMIN.'img/robot.gif";
var tjs_size=19925;
var tjs_delai=100;
var tjs_nb=-1;
var tjs_delai_max=20000;
var timer1=0; var timer2=0;
var tjs_fin="";

function Checkkos() {
    tjs_img=new Image();
    timer1=new Date();
    timer1=timer1.getTime();
    tjs_img.src=tjs_src+"?dummy="+timer1;
    tjs_nb=0;
    document.countkos.info.value="'.$speed[9].'";
    setTimeout("Timerkos()",tjs_delai);
}

function Timerkos() {
    var anim="-"
    tjs_nb++;
    document.countkos.info.value="'.$speed[9].'";
    if (tjs_nb*tjs_delai>=tjs_delai_max) {
        tjs_fin=EvalConnexion(0);
        document.countkos.info.value=tjs_fin;
    }
    else {
        if (tjs_img.complete) {
            timer2=new Date(); timer2=timer2.getTime();
            tjs_fin=EvalConnexion(tjs_size/(timer2-timer1));
            document.countkos.info.value=tjs_fin;
        }
        else {
            setTimeout("Timerkos()",tjs_delai);
        }
    }
}

function EvalConnexion(kos) {
    tjs_nb=-1;
    res="";
    if (kos==0) { res="'.$speed[10].'"; }
    if ((kos>0)&&(kos<3)) { res="'.$speed[11].'"; }
    if ((kos>3)&&(kos<6)) { res="'.$speed[12].'"; }
    if ((kos>6)&&(kos<100)) { res="'.$speed[13].'"; }
    if (kos>100) { res="'.$speed[14].'"; }
    kos=Math.round(kos*10)/10;
    return res+" (" + kos +" '.$speed[15].')";
}'.EndJavascript();

    $out .= '
<div class="box">
<h2 class="text-center">'.$speed[16].'</h2>
<p>'.$speed[17].'</p>
<form name="countkos" action="'.CHEMIN.'plugins/speed/'.($site['URLR'] == 'on' ? $lng.'-speedcnt' : 'speed.php?lng='.$lng).'">
  <p class="text-center">'.SubmitButton($speed[18], 'Checkkos(); return false;').'</p>
  <p class="text-center">'.$speed[19].'
    <input type="text" class="form-control texte w-100 m-auto" name="info" style="max-width:340px;" />
  </p>
</form>
</div>';


    if (($serviz[32] == "on" && !empty($serviz[31]) && $serviz[31]==$userprefs[1]) || ($serviz[32] == "on" && $drtuser[17] == "on")) {
        $out .= '
<p class="text-center">
  <a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg=plugin&amp;plug=speed/speed">
    <i class="fas fa-edit align-middle" title="'.$web[308].'"></i>
  </a>
</p>';
    }

    return boxwide($pos, $speedtitle, $out, ' SPEED'.$tconfig);
}
?>