<?php
/*
    Sample External Page - GuppY PHP Script - version 4.6
    CeCILL Copyright (C) 2004-2007 by Laurent Duveau
    Initiated by Laurent Duveau and Nicolas Alves
      Web site = http://www.freeguppy.org/
      e-mail   = info@freeguppy.org

    This is a sample external PHP script that integrates with GuppY

    Version History :
      v2.3 (27 July 2003)        : initial release (by Isabelle)
      v3.0 (25 February 2004)    : moved to pages/ directory
      v4.0 (06 December 2004)    : Added text in many languages (by Jean-Mi)
	    v4.5 (27 April 2005)       : Added page title (thanks Ricsen)
	    v4.6.0 (20 november 2006)  : corrected $topmess in htable() (by Ghazette)
	    v4.6.6 (14 April 2008)     : corrected bad position of include hpage.inc (by Icare)
*/

header("Pragma: no-cache");
define("CHEMIN", "../");
include(CHEMIN."inc/includes.inc");
if ($lng == $lang[0]) {
  $topmess = "Inscription à la soirée du 19 septembre 2010";
}
else {
  $topmess = "Window title in secondary language";
}
include(CHEMIN."inc/hpage.inc");

// Si vous voulez mettre une icône du répertoire img en tête de page:
// If you want to put an icon from img directory on top of the page:
$topmess = '<img src="'.CHEMIN.'img/logo022.gif" align="right" width="32" height="32" border="0" alt="My icon">'.$topmess;
if ($lng == $lang[0]) {
htable($topmess, "100%"); // Titre en langue principale
?>

<!-- Début du texte HTML dans la langue principale -->


<?php
// Couleur du texte des champs si erreur saisie utilisateur
$color_font_warn="#FF0000";
// Couleur de fond des champs si erreur saisie utilisateur
$color_form_warn="#FFCC66";
// Ne rien modifier ci-dessous si vous n’êtes pas certain de ce que vous faites !
if(isset($_POST['submit'])){
	$erreur="";
	// Nettoyage des entrées
	while(list($var,$val)=each($_POST)){
	if(!is_array($val)){
		$$var=strip_tags($val);
	}else{
		while(list($arvar,$arval)=each($val)){
				$$var[$arvar]=strip_tags($arval);
			}
		}
	}
	// Formatage des entrées
	$f_1=trim(ucwords(eregi_replace("[^a-zA-Z0-9éèàäö\ -]", "", $f_1)));
	$f_2=trim(ucwords(eregi_replace("[^a-zA-Z0-9éèàäö\ -]", "", $f_2)));
	$f_3=trim(ucwords(eregi_replace("[^a-zA-Z0-9éèàäö\ -]", "", $f_3)));
	$f_4=trim(ucwords(eregi_replace("[^a-zA-Z0-9éèàäö\ -]", "", $f_4)));
	$f_5=trim(ucwords(eregi_replace("[^a-zA-Z0-9éèàäö\ -]", "", $f_5)));
	$f_6=trim(ucwords(eregi_replace("[^a-zA-Z0-9éèàäö\ -]", "", $f_6)));
	// Verification des champs
	if(strlen($f_1)<2){
		$erreur.="<li><span class='txterror'>Le champ &laquo; Nom &raquo; est vide ou incomplet.</span>";
		$errf_1=1;
	}
	if(strlen($f_2)<2){
		$erreur.="<li><span class='txterror'>Le champ &laquo; Prénom &raquo; est vide ou incomplet.</span>";
		$errf_2=1;
	}
	if(strlen($f_3)<2){
		$erreur.="<li><span class='txterror'>Le champ &laquo; Numéro de vol &raquo; est vide ou incomplet.</span>";
		$errf_3=1;
	}
	if(strlen($f_4)<2){
		$erreur.="<li><span class='txterror'>Le champ &laquo; Départ(Heure locale) &raquo; est vide ou incomplet.</span>";
		$errf_4=1;
	}
	if(strlen($f_5)<2){
		$erreur.="<li><span class='txterror'>Le champ &laquo; Durée estimée du vol &raquo; est vide ou incomplet.</span>";
		$errf_5=1;
	}
	if(strlen($f_6)<2){
		$erreur.="<li><span class='txterror'>Le champ &laquo; Appareil &raquo; est vide ou incomplet.</span>";
		$errf_6=1;
	}
	if($erreur==""){
		// Création du message
		$titre="ITVA Soirée du 19 spet 2010";
		$tete="From:Site@Airinter-va.org\n";
		$corps.="Nom : ".$f_1."\n";
		$corps.="Prénom : ".$f_2."\n";
		$corps.="Numéro de vol : ".$f_3."\n";
		$corps.="Départ(Heure locale) : ".$f_4."\n";
		$corps.="Durée estimée du vol : ".$f_5."\n";
		$corps.="Appareil : ".$f_6."\n";
		$corps.="Observations : ".$f_7."\n";
		if(mail("ercheron52@modulonet.fr", $titre, stripslashes($corps), $tete)){
			$ok_mail="true";
		}else{
			$erreur.="<li><span class='txterror'>Une erreur est survenue lors de l'envoi du message, veuillez refaire une tentative.</span>";
		}
	}
}
?>

<? if($ok_mail=="true"){ ?>
	<table width='100%' border='0' cellspacing='1' cellpadding='1'>
		<tr><td><span class='txtform'>Les données ci-dessous nous ont bien été transmises, le planning sera mis à jour dans les prochaines 24h00.<br>N'oubliez pas de déposer votre plan de vol sur le "Flight Sheduling System" IVAO.</span></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td><tt><?echo nl2br(stripslashes($corps));?></tt></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td><span class='txtform'>Nous vous remercions de votre inscription et vous souhaitons un bon vol lors de cette soirée.<br>A bientôt.<br>Eric IT055</span></td></tr>
	</table>
<? }else{ ?>
<form action='<? echo $PHP_SELF ?>' method='post' name='Form'>
<table width='100%' border='0' cellspacing='1' cellpadding='1'>
<? if($erreur){ ?><tr><td colspan='2' bgcolor='red'><span class='txterror'><font color='white'><b>&nbsp;ERREUR, votre message n'a pas été transmis</b></font></span></td></tr><tr><td colspan='2'><ul><?echo$erreur?></ul></td></tr><?}?>
<tr><td colspan='2'><span class='txterror'>Les champs marqués d'un * sont obligatoires</span></td></tr>
<tr><td align='right' width='30%'><span class='txtform'>Nom* :</span></td><td><input type='text' style='width:200 <?if($errf_1==1){print("; background-color: ".$color_form_warn."; color: ".$color_font_warn);}?>;' name='f_1' value='<?echo stripslashes($f_1);?>' size='24' border='0'></td></tr>
<tr><td align='right' width='30%'><span class='txtform'>Prénom* :</span></td><td><input type='text' style='width:200 <?if($errf_2==1){print("; background-color: ".$color_form_warn."; color: ".$color_font_warn);}?>;' name='f_2' value='<?echo stripslashes($f_2);?>' size='24' border='0'></td></tr>
<tr><td align='right' width='30%'><span class='txtform'>Numéro de vol* :</span></td><td><input type='text' style='width:200 <?if($errf_3==1){print("; background-color: ".$color_form_warn."; color: ".$color_font_warn);}?>;' name='f_3' value='<?echo stripslashes($f_3);?>' size='24' border='0'></td></tr>
<tr><td align='right' width='30%'><span class='txtform'>Départ (Heure locale)* :</span></td><td><input type='text' style='width:200 <?if($errf_4==1){print("; background-color: ".$color_form_warn."; color: ".$color_font_warn);}?>;' name='f_4' value='<?echo stripslashes($f_4);?>' size='24' border='0'></td></tr>
<tr><td align='right' width='30%'><span class='txtform'>Durée estimée du vol* :</span></td><td><input type='text' style='width:200 <?if($errf_5==1){print("; background-color: ".$color_form_warn."; color: ".$color_font_warn);}?>;' name='f_5' value='<?echo stripslashes($f_5);?>' size='24' border='0'></td></tr>
<tr><td align='right' width='30%'><span class='txtform'>Appareil* :</span></td><td><input type='text' style='width:200 <?if($errf_6==1){print("; background-color: ".$color_form_warn."; color: ".$color_font_warn);}?>;' name='f_6' value='<?echo stripslashes($f_6);?>' size='24' border='0'></td></tr>
<tr><td align='right' width='30%'><span class='txtform'>Observations :</span></td><td><textarea style='width:360 <?if($errf_7==1){print("; background-color: ".$color_form_warn."; color: ".$color_font_warn);}?>;' name='f_7' rows='6' cols='40'><?echo$f_7?></textarea></td></tr>
<tr><td align='right' width='30%'></td><td><input type='submit' name='submit' value='Envoyer' border='0'></td></tr>
</table>
</form>
<? } ?>

<!-- Fin du texte HTML en langue principale -->

<?php
}
else {
htable($topmess, "100%"); // Titre en langue secondaire
?>

<!-- Début du texte HTML dans la langue secondaire -->

<br />
Texte HTML dans la langue secondaire <br />
Text HTML in the secondary language <br />
HTML-Text in der sekundären Sprache <br />
Texto HTML en la lengua secundaria <br />
Testo HTML nella lingua secondaria <br />
Tekst HTML in de ondergeschikte taal <br />
Texto HTML na língua secundária <br />
<br />

<!-- Fin du texte HTML en langue secondaire -->

<?php
}
btable();
include(CHEMIN."inc/bpage.inc");
?>
