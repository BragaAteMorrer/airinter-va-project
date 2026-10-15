<?php
/*******************************************************************************
 *   Construction du captcha texte
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2023 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.12 (MAy 28, 2023) : correction script
 ******************************************************************************/

error_reporting(E_ALL ^ E_NOTICE);
//srand((int)microtime()*1000000);
srand((float)microtime(true)*1000000);
include 'cryptotexte.cfg.php';

$word   = '';
$pair   = rand(0,1);
$charnb = rand($charnbmin, $charnbmax);
for ($i = 1; $i <= $charnb; $i++) {
    if ($crypteasy) $tword = (!$pair ? substr($charelc, rand(0, strlen($charelc)-1), 1) : substr($charelv, rand(0, strlen($charelv)-1), 1));
    else $tword = substr($charel, rand(0, strlen($charel)-1), 1);

    $pair = !$pair;
    $word .= $tword;
}

// Conversion du cryptogramme en Majuscule si insensibilit√© √† la casse
$word = ($difuplow ? $word : strtoupper($word));

switch (strtoupper($cryptsecure)) {
    case "MD5"  : $w0 = md5($word); break;
    case "SHA1" : $w0 = sha1($word); break;
    default     : $w0 = $word; break;
}
setcookie('GuppYCrypt', gy_crypt(CRYPT_KEY, $w0).'||'.gy_crypt(CRYPT_KEY, $word));

unset($word, $tword, $w0);
?>
