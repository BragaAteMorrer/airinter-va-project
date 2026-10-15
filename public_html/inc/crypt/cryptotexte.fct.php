<?php
/*******************************************************************************
 *   Fonctions captcha texte
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
 * v6.00.12 (MAy 28, 2023) : correction crypt
 ******************************************************************************/

function dsp_crypt($cfg = 0, $reload = 1, $crypt_nb = 1, $crypt_num = 0) {
    // Affiche le cryptogramme
    include 'cryptotexte.cfg.php';
    $style  = 'width:'.$cryptwidth.'px; height:'.$cryptheight.';';
	if (isset($_COOKIE['GuppYCrypt']))
	{
		$acookie = explode('||', $_COOKIE['GuppYCrypt']);
		$out = '<div id="cryptogram'.$crypt_num.'" class="cryptcode" style="'.$style.'">'.gy_decrypt(CRYPT_KEY, @$acookie[1]).'</div>'."\n";
	}
	else $out = '';
    return $out;
}

function chk_crypt($code) {
    // Vérifie si le code est correct
    include 'cryptotexte.cfg.php';
    $code = addslashes($code);
    $code = str_replace(' ', '', $code);  // supprime les espaces saisis par erreur.
    $code = ($difuplow ? $code : strtoupper($code));
    switch (strtoupper($cryptsecure)) {
        case "MD5"  : $code = md5($code); break;
        case "SHA1" : $code = sha1($code); break;
    }
    $acookie = explode('||', $_COOKIE['GuppYCrypt']);
    return gy_decrypt(CRYPT_KEY, $acookie[0]) == $code ? true : false;
}
?>