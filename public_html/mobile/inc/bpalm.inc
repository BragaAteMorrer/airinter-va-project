<?php
/*******************************************************************************
 *   Site Bottom of Page for Mobile
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
 * v6.00.07 (February 24, 2022) : correction script
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "bpalm.inc")) {
    header("location:../index.php");
    die();
}

// php or html scripts located in directory inc/incfoot are automatically include at the bottom of the page
if (is_dir(CHEMIN.'mobile/inc/incfoot')) { 
	$hd = opendir(CHEMIN.'mobile/inc/incfoot');
	while ($file = readdir($hd)) { 
		if (1 == preg_match('!^[^_].*(\.inc|\.inc\.php)$!', $file)) {
			include(CHEMIN.'mobile/inc/incfoot/'.$file);
		}
	}
}
// echo 'Michel mobile test';
// Haut
echo '
    <div style="text-align:center;">
      <a class="top" href="#top">
        <b> '.$web[136].' </b>
      </a>
    </div>

    <div style="text-align:center;">';

$txtxhtml = $site[32] != 'on' ? '' : '
      <a href="http://validator.w3.org/check?uri=referer" target="_blank" title="Valid HTML 5.0"><i class="gen-html5 align-middle"></i></a> ';

$txtcss = $site[32] != 'on' ? '' : ' <a href="http://jigsaw.w3.org/css-validator/validator?uri='.$site[3].'" target="_blank" title="Valid CSS 3"><i class="gen-css3 align-middle"></i></a>';

$imgspe = $site[24] == 'N' ? '' : '<a href="http://www.freeguppy.org/" target="_blank" title="GuppY site"><i class="gen-gyslogo align-middle"></i></a>';


    ///////////////////////////////////////////////////////////////////////////////////
    //                                                                               //
    //                GuppY is protected by the CeCILL FREE LICENSE.                 //
    //  REMOVING OR MODIFYING THE FOLLOWING COPYRIGHT NOTICE IS STRICTLY FORBIDDEN.  //
    //                                                                               //
    ///////////////////////////////////////////////////////////////////////////////////


if ($lng == 'fr') {
    $txtspe = ' <a class="copyright" href="http://www.freeguppy.org/" title="freeguppy.org" target="_blank"><i class="gen-gypower align-middle"></i></a> '.html_entity_decode('© '.MP_COPYRIGHT, ENT_COMPAT, $charset).' <a class="copyright" href="http://www.cecill.info/index.fr.html" title="En savoir plus ..." target="_blank"><i class="gen-gycecill-f align-middle"></i></a> ';
}
else {
    $txtspe = ' <a class="copyright" href="http://www.freeguppy.org/" title="freeguppy.org" target="_blank"><i class="gen-gypower align-middle"></i></a> '.html_entity_decode('© '.MP_COPYRIGHT, ENT_COMPAT, $charset).' <a class="copyright" href="http://www.cecill.info/index.en.html" title="More info ..." target="_blank"><i class="gen-gycecill-e align-middle"></i></a> ';
}

if ($site[25] == 'T') {
    echo $imgspe.'
      <br />
'.$txtxhtml.$txtspe.$txtcss;
}
elseif ($site[25] == 'L') {
    echo $txtxhtml.$imgspe.$txtspe.$txtcss;
}
elseif ($site[25] == 'R') {
    echo $txtxhtml.$txtspe.$imgspe.$txtcss;
}
else {
    echo $txtxhtml.$imgspe.$txtspe.$imgspe.$txtcss;
}
echo '
  </div>

';

if (!empty($skinCopyright)) {
    echo '
  <div style="text-align:center;" id="skinCopyright">'.$skinCopyright.'</div>

';
}


    ///////////////////////////    END of COPYRIGHT NOTICE    /////////////////////////


if ($page[8] == 'on') {
    $timer = StopTimer();
    echo '
    <p class="timer">'.$web[116].' '.$timer.' '.($timer <= 1 ? $web[117] : $web[118]).'</p>';
}
echo '
</body>
</html>';
?>