<?php
/*******************************************************************************
 *   Construction du captcha sélection texte
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
 * v6.00.12 (MAy 28, 2023) : Modify srand
 ******************************************************************************/

error_reporting(E_ALL ^ E_NOTICE);
//srand((int)microtime()*1000000);
srand((float)microtime(true)*1000000);
include 'cryptoseltexte.cfg.php';

$word   = '';
$pair   = rand(0,1);
$charnb = rand($charnbmin, $charnbmax);
for ($i = 1; $i <= $charnb; $i++) {
    if ($crypteasy) $tword = (!$pair ? substr($charelc, rand(0, strlen($charelc)-1), 1) : substr($charelv, rand(0, strlen($charelv)-1), 1));
    else $tword = substr($charel, rand(0, strlen($charel)-1), 1);

    $pair = !$pair;
    $word .= $tword;
}

// Conversion du cryptogramme en Majuscule si insensibilité à la casse
$word = ($difuplow ? $word : strtoupper($word));

// Retourne 4 informations dans la session:
// - Le code du cryptogramme à afficher
// - Le nombre de digits à saisir
// - Les positions des digits à saisir
// - Le code du cryptogramme (crypté ou pas)
$cryptnb = (int)rand(4,5);
$pos     = '';
for ($i = 0; $i < $cryptnb; $i++) $pos .= rand(1, strlen($word)).'-';
$cryptpos = substr($pos, 0, -1);
$tpos = explode('-', $cryptpos);
$tword = '';
foreach ($tpos as $p) $tword .= substr($word, $p - 1, 1);
switch (strtoupper($cryptsecure)) {
    case "MD5"  : $w0 = md5($tword); break;
    case "SHA1" : $w0 = sha1($tword); break;
    default     : $w0 = $tword; break;
}
setcookie('GuppYCrypt', gy_crypt(CRYPT_KEY, $w0).'||'.gy_crypt(CRYPT_KEY, $tword).'||'.gy_crypt(CRYPT_KEY, $word).'||'.$cryptnb.'||'.gy_crypt(CRYPT_KEY, $cryptpos));

unset($word, $tword, $w0);
?>
