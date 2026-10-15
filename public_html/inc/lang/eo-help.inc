<?php
/*******************************************************************************
 *   Help Language Strings
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

if (stristr($_SERVER['SCRIPT_NAME'], 'eo-help.inc')) {
    header('location:../index.php');
    die();
}

$help[1] = 'Rezultoj de pli malalta graveco estas kaŝitaj. La valoro 0% kutime montras ĉiujn rezultojn.';
$help[2] = 'Defaŭlte: 10 minimume.';
$help[3] = '<b>GRAVA!</b> Lasu malplena se vi ne volas ŝanĝi vian pasvorton. Alie vi devos enigi ankaŭ la kontrolpasvorton.';
$help[4] = 'Enigu la valoron "0" por malaktivigi la kontrolon.';
$help[5] = 'Enigu valoron inter \"1\" kaj \"30\", \"0\" por daŭra prizorgado.';
$help[6] = 'Por inkluzivi bildon en la foruma subskribo, vi nur devas enigi la kodon por tiu bildo ĉe la dezirata loko en la subskriba teksto.<br />Akceptitaj formatoj: .gif .jpg. png 468x60 maks.<br /><br />La kodo por la ekstera bildo estas via kaŝnomo prefiksita per <b>fr_</b>. Ekzemplo: <b>fr_MiaKaŝnomo</b>.';
$help[7] = 'AVERTO! Ĉiu ago rezultigas la sendadon de sciiga retpoŝto al la interesato.';
$help[8] = 'Por uzi vian avataron de alia retejo, tajpu ĝian adreson.<br />Akceptitaj formatoj: .gif .jpg .png 60x60 maks.';
$help[9] = 'Por sinkronigi la retpoŝtadresojn de via konto kaj de la informletero.';
$help[10] = 'ID estas unika identigilo konsistanta el de 2 ĝis 8 signoj sen supersignoj, sen interspacoj.';
$help[11] = 'Indiku la opcio-numerojn dekomence, sekvitajn de la nombro de tiu opcio en la submenuo, apartigitaj per streketoj.<br />(Ekz .: 2-4-1 signifas venas de la 2a opcio de la ĉefa menuo, de la 4a de la malfermita submenuo kaj ĝi estas opcio n° 1 de la dua submenuo.)';
$help[12] = 'Por ne atribui iun ligilon al la menua opcio, enigu <b>?</b>.';
$help[13] = 'Vi povas krei subkategoriojn disigante ilin per la simbolo |. <br /> Ekz.: Kategorio | Nivelo 1 | Nivelo 2';
$help[14] = 'La datoj devas esti en la formato JJJJMMTThhmn (jaro monato tago horo minutoj).<br />Lasu malplena se vi ne volas uzi la planitan publikigon.<br />Vi povas nur indiki daton de komenco aŭ nur finan daton.<br />Se la komenca dato estas preterlasita, la eventotempo estos prenita por la registrado.';
$help[15] = '(malplenigu la kampon por forigi)';
$help[16] = '<br /><br /><strong>La kategorio utilas nur por la konstruo de la menua opcio se necese.</strong>';
$help[17] = 'ATENTU! Krucmarku nur se vi uzas la membran areon. <br /> Tiu opcio influas la serĉon, do uzu se necese. ';
$help[18] = '<b>ATENTU!</b> La grando de la retejo eble tre gravas. Tiukaze savkopiu en plurajn pecojn.';
$help[19] = 'La klaso <b>ZipArchive</b> necesa por plenumi tiun skripton ne estas ebligita.<br />Bonvolu mane efektivigi la agojn per FTP.';
$help[20] = 'GuppY estas protektita per la libera permesilo CeCILL.<br />Forigi aŭ modifi la kopirajtan noticon, aŭ kaŝi ĝin per ia ajn rimedo, estas strikte malpermesita.';
$help[21] = 'La pasvorto devas enhavi 8 signojn.';
$help[22] = 'Bona pasvorto!';
$help[23] = 'Faru vian pasvorton pli forta per pli da ĉefliteroj,pli da ciferoj kaj specialaj signoj!';
$help[24] = 'Malforta pasvorto, provu uzi nombrojn, majusklojn kaj specialajn signojn.';
$help[25] = 'Por ke rotacio ripetiĝu ĉiujare, JJJJ povas esti anstataŭigita per ---- (4 streketoj), tio estos anstataŭigita dum prilaborado de la kuranta jaro.<br /><br />La horo kaj la minutoj po 2 ciferoj estas nedevigaj, ĝi estos anstataŭigita per 0000.';
$help[26] = 'Markante tiun keston, kaŝkopio kun la retpoŝta adreso de la retejestro aldoniĝas al la ligo al la retpoŝta adreso de la ricevonto por la respondo.<br /><br />Utilas kontroli kiam ekzistas elekto inter pluraj ricevontoj kaj vi volas, ke la retejestro ĉiam ricevu la respondojn.';
$help[27] = 'Registrado de membroj ĝis 20 samtempe. La nepraj kampoj estas salutnomo kaj retpoŝtadreso. Se la pasvorto ne estas entajpita, ĝi estos generita.<br />La ensalutiloj estas sendotaj al la membro kaj administranto.';

$errormsg['400'] = array(
    'Malbona peto',
    'La HTTP peto ne povis esti komprenita de la servilo pro erara sintakso.',
    'La problemo povas deveni de tro freŝa retumilo aŭ tro malnova HTTP servilo.',
    'HTTP/1.0 400 Malbona Peto');
$errormsg['401'] = array(
    'Nerajtigita',
    'La peto postulas aŭtentokontrolon de uzanto.',
    '',
    'HTTP/1.0 401 Nerajtigita');
$errormsg['403'] = array(
    'Malpermesita',
    '- La HTTP servilo komprenis la peton, sed rifuzis plenumi ĝin.',
    'Tiu kodo estas ĝenerale uzata kiam servilo ne volas indiki kial la peto estis malakceptita, aŭ kiam neniu alia respondo kongruas (ekzemple la servilo estas Intrareto kaj nur maŝinoj en la loka reto rajtas konektiĝi al la servilo).',
    'HTTP/1.0 403 Malpermesita');
$errormsg['404'] = array(
    'Ne trovita',
    'La servilo trovis nenion kongruan kun la petita adreso (URI).',
    'Tio signifas, ke la tajpita aŭ alklakita URL estas malĝusta aŭ malaktuala kaj ne kongruas kun iu ajn ekzistanta dokumento en la servilo (vi povas provi iom post iom forigi erojn de la URL ekde la fino por fine trovi alirvojon ekzistantan).',
    'HTTP/1.0 404 Ne Trovita');
$errormsg['500'] = array(
    'Interna Eraro de Servilo',
    'La HTTP-servilo renkontis neatenditan kondiĉon, kiu malebligis plenumi la peton.',
    'Tiu eraro povas ekzemple esti la rezulto de malbona agordo de la servilo, aŭ risurco elĉerpita aŭ rifuzita al la servilo sur la gastmaŝino.',
    'HTTP/1.0 500 Interna Eraro de Servilo');

?>
