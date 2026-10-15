<?php
/*******************************************************************************
 *   Clock Sample Plugin
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

header('Pragma: no-cache');
define("CHEMIN", "../../");
include CHEMIN.'inc/includes.inc';

if (is_file(CHEMIN.'plugins/clock/'.$lng.'-clock.inc')) {
    include CHEMIN.'plugins/clock/'.$lng.'-clock.inc';
} else {
    include CHEMIN.'plugins/clock/en-clock.inc';
}

$topmess = '<img src="clock.gif" class="img32x32 f-right" alt="clock.gif" />'.$clock[7];

$userprefs[3] = 'L';
include CHEMIN.'inc/hpage.inc';
htable($topmess, '100%');

echo '
<br />
<div id="clock" class="text-center">';

if (!empty($page[13])) {
    echo BeginJavascript().'hideLoadingPage()'.EndJavascript();
}
echo BeginJavascript();
?>
<!-- DEBUT DE CONFIGURATION -->
// Largeur des cellules en pixels (points qui marquent l'heure)
var cellwidth=8;
// Hauteur des cellules en pixels (points qui marquent l'heure)
var cellheight=8;
// Taille de la fonte qui affiche le texte. Attention de ne pas la mettre trop grande
var fontsize=12;
// Couleur de la fonte qui affiche le texte
var fontcolor="black";
// Style de la fonte gras = bold - italique = italic - aucun style = none
var fontstyle="none";
// Couleur des cellules actives
var oncolor="#d7d71e";
// Couleur des cellules inactives
var offcolor="#d3766b";
<!-- FIN DE CONFIGURATION -->
//************** Ne rien éditer ci-dessous *************//
var NS4 = (document.layers)? true : false;
var IE4 = (document.all && !document.getElementById)? true : false;
var NS6 = (document.getElementById && navigator.appName.indexOf("Netscape")>=0 )? true: false;
var binclk;
var now;
var t='<div class="table-responsive-md"><table style="border:none;"><tr><td style="text-align:center;"> </td>';
for(i=0;i<=58;i+=2)t+='<td style="text-align:left;" colspan="2"><font style="font-size:'+fontsize+'px; font-weight:'+fontstyle+'; color: '+fontcolor+'">'+i+'<br /> |</font></td>';
t+='<td> </td></tr><tr><td style="text-align:center;"><font style="font-size:'+fontsize+'px; font-weight:'+fontstyle+'; color: '+fontcolor+'">H : </font></td>';
for(i=0;i<=23;i++)t+=(NS4)? '<td><ilayer name="hrs'+i+'" height="'+cellheight+'" width="'+cellwidth+'" bgcolor="'+offcolor+'"></ilayer></td>' : '<td><div id="hrs'+i+'" style="position:relative; width:'+cellwidth+'px; font-size:1px; height:'+cellheight+'px; background-color:'+offcolor+'"></div></td>';
t+='<td colspan="36"><td> </td></tr><tr><td style="text-align:center;"><font style="font-size:'+fontsize+'px; font-weight:'+fontstyle+'; color: '+fontcolor+'">M : </font></td>';
for(i=0;i<=59;i++)t+=(NS4)? '<td><ilayer name="min'+i+'" width="'+cellwidth+'" height="'+cellheight+'" bgcolor="'+offcolor+'"></ilayer></td>' : '<td><div id="min'+i+'" style="position:relative; width:'+cellwidth+'px; font-size:1px; height:'+cellheight+'px; background-color:'+offcolor+'"></div></td>';
t+='<td> </td></tr><tr><td style="text-align:center;"><font style="font-size:'+fontsize+'px; font-weight:'+fontstyle+'; color: '+fontcolor+'">S : </font></td>';
for(i=0;i<=59;i++)t+=(NS4)? '<td><ilayer name="sec'+i+'" width="'+cellwidth+'" height="'+cellheight+'" bgcolor="'+offcolor+'"></ilayer></td>' : '<td><div id="sec'+i+'" style="position:relative; width:'+cellwidth+'px; font-size:1px; height:'+cellheight+'px; background-color:'+offcolor+'"></div></td>';
t+='<td> </td></tr><tr><td> </td><td> </td>';
for(i=1;i<=59;i+=2)t+='<td style="text-align:left;" colspan="2"><font style="font-size:'+fontsize+'px; font-weight:'+fontstyle+'; color: '+fontcolor+'"> |<br />'+i+'</font></td>';
t+='</tr></table></div>';
document.write(t);

function getvals() {
now=new Date();
now.s=now.getSeconds();
now.h=now.getHours();
now.m=now.getMinutes();
}

function setclock() {
getvals();
if((now.h==0)&&(now.m==0)) for(i=1;i<=23;i++)setbgcolor('hrs'+i, offcolor);
if((now.s==0)&&(now.m==0)) for (i=1;i<=59;i++)setbgcolor('min'+i, offcolor);
if(now.s==0) for(i=1;i<=59;i++)setbgcolor('sec'+i, offcolor);
setbgcolor('hrs'+now.h, oncolor);
setbgcolor('min'+now.m, oncolor);
setbgcolor('sec'+now.s, oncolor);
}

function setbgcolor(idstr, color) {
if(IE4)document.all[idstr].style.backgroundColor=color;
else if(NS4)document.layers[idstr].bgColor=color;
else document.getElementById(idstr).style.backgroundColor=color;
}

window.onload=function() {
  getvals();
  for(i=0;i<=now.h;i++)setbgcolor('hrs'+i, oncolor);
  for(i=0;i<=now.m;i++)setbgcolor('min'+i, oncolor);
  for(i=0;i<=now.s;i++)setbgcolor('sec'+i, oncolor);
  setInterval('setclock()', 100);
}

window.onresize=function() {
  if(NS4)setTimeout('history.go(0)',400);
}
<?php
echo EndJavascript().'
</div>
<br />';

btable();
include CHEMIN.'inc/bpage.inc';
?>