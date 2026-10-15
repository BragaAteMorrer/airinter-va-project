<?php
/*******************************************************************************
 *   Fonctions captcha sélection texte
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
 * v6.00.12 (MAy 28, 2023) : : correction crypt
 ******************************************************************************/

function dsp_crypt($cfg = 0, $reload = 1, $crypt_nb = 1, $crypt_num = 0, $lbl = '', $dul = '') {
    // Affiche le cryptogramme
    include 'cryptoseltexte.cfg.php';
	if (isset($_COOKIE['GuppYCrypt']))
	{
		$acookie = explode('||', $_COOKIE['GuppYCrypt']);
		$style  = 'width:'.(strlen(gy_decrypt(CRYPT_KEY, @$acookie[2])) * 16).'px; height:'.$cryptheight.'px; border:0;';
		$out = '
		<div id="cryptogram'.$crypt_num.'" class="m-auto cryptsel" style="'.$style.'">';
		$style = 'width:14px; padding-left:1px; color:inherit; background-color:inherit;line-height:14px;';
		for ($i = 0; $i < strlen(gy_decrypt(CRYPT_KEY, @$acookie[2])); $i++) {
			$out .= '
		  <div class="text-center float-left font-weight-bold" style="'.($i > 0 ? 'border-left:1px solid '.$bdS.'; ' : '').$style.'">
			'.substr(gy_decrypt(CRYPT_KEY, @$acookie[2]), $i, 1).'
			<span style="font-size:8px;">'.($i + 1).'</span>
		  </div>';
		}
		$out .= '
		</div>
		<div class="text-center m-auto cryptpos">'.sprintf($lbl, @$acookie[3], $dul).'
		  <br /><span style="font-size:14px;"><b>'.gy_decrypt(CRYPT_KEY, @$acookie[4]).'</b></span>
		</div>'."\n";
	} 
	else $out = '';
    return $out;
}

function chk_crypt($code) {
    // Vérifie si le code est correct
    include 'cryptoseltexte.cfg.php';
    $code = addslashes($code);
    $code = str_replace(' ', '', $code);  // supprime les espaces saisis par erreur.
    $code = ($difuplow ? $code : strtoupper($code));
    switch (strtoupper($cryptsecure)) {
        case "MD5"  : $code = md5($code); break;
        case "SHA1" : $code = sha1($code); break;
    }
    $acookie = explode('||', $_COOKIE['GuppYCrypt']);
    return gy_decrypt(CRYPT_KEY, @$acookie[0]) == $code ? true : false;
}
?>