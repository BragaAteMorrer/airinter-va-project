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
  $topmess = "Titre de la fenêtre en langue principale";
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
$list['f_7']=array("oui","non");
$list['f_8']=array("FS2004","FSX","Autre");
$list['f_9']=array("oui","non");
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
	$f_3=strip_tags(trim($f_3));
	$f_4=trim(eregi_replace("[^0-9\ +]", "", $f_4));
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
		$erreur.="<li><span class='txterror'>Le champ &laquo; E-mail &raquo; est vide ou incomplet.</span>";
		$errf_3=1;
	}else{
		if(!ereg('^[-!#$%&\'*+\./0-9=?A-Z^_`a-z{|}~]+'.
		'@'.
		'[-!#$%&\'*+\/0-9=?A-Z^_`a-z{|}~]+\.'.
		'[-!#$%&\'*+\./0-9=?A-Z^_`a-z{|}~]+$',
		$f_3)){
			$erreur.="<li><span class='txterror'>La syntaxe de votre adresse e-mail n'est pas correcte.</span>";
			$errf_3=1;
		}
	}
	if(strlen($f_4)<2){
		$erreur.="<li><span class='txterror'>Le champ &laquo; ID Ivao &raquo; est vide ou incomplet.</span>";
		$errf_4=1;
	}
	if(!$f_7){
		$erreur.="<li><span class='txterror'>Le champ &laquo; Avez-vous une expérience en pilotage réel ? &raquo; requiert au moins une sélection.</span>";
		$errf_7=1;
	}
	if(!$f_8){
		$erreur.="<li><span class='txterror'>Le champ &laquo; Quel simulateur de vol utilisez vous ? &raquo; requiert au moins une sélection.</span>";
		$errf_8=1;
	}
	if(!$f_9){
		$erreur.="<li><span class='txterror'>Le champ &laquo; Avez-vous lu les manuels MANEX et MANOPS ITVA ? &raquo; requiert au moins une sélection.</span>";
		$errf_9=1;
	}
	if(strlen($f_10)<2){
		$erreur.="<li><span class='txterror'>Le champ &laquo; Etes-vous (ou avez vous été) inscrit dans une autre VA ? &raquo; est vide ou incomplet.</span>";
		$errf_10=1;
	}
	if(strlen($f_11)<2){
		$erreur.="<li><span class='txterror'>Le champ &laquo; Pourquoi voulez-vous intégrer ITVA (motivations) ? &raquo; est vide ou incomplet.</span>";
		$errf_11=1;
	}
	if($erreur==""){
		// Création du message
		$titre="Message de votre site";
		$tete="From:Site@Airinter-va.org\n";
		$corps.="Nom : ".$f_1."\n";
		$corps.="Prénom : ".$f_2."\n";
		$corps.="E-mail : ".$f_3."\n";
		$corps.="ID Ivao : ".$f_4."\n";
		$corps.="Heures de vol IVAO : ".$f_5."\n";
		$corps.="Ville de résidence : ".$f_6."\n";
		for($id=0; $id<count($list['f_7']); $id++){
			if($f_7[$id]==1){
				$corps.="Avez-vous une expérience en pilotage réel ? : ".$list['f_7'][$id]."\n";
			}
		}
		for($id=0; $id<count($list['f_8']); $id++){
			if($f_8[$id]==1){
				$corps.="Quel simulateur de vol utilisez vous ? : ".$list['f_8'][$id]."\n";
			}
		}
		for($id=0; $id<count($list['f_9']); $id++){
			if($f_9[$id]==1){
				$corps.="Avez-vous lu le règlement ITVA ? : ".$list['f_9'][$id]."\n";
			}
		}
		$corps.="Etes-vous (ou avez vous été) inscrit dans une autre VA ? : ".$f_10."\n";
		$corps.="Pourquoi voulez-vous intégrer ITVA (motivations) ? : ".$f_11."\n";
		if(mail("it014@hotmail.fr", $titre, stripslashes($corps), $tete)){
			$ok_mail="true";
		}else{
			$erreur.="<li><span class='txterror'>Une erreur est survenue lors de l'envoi du message, veuillez refaire une tentative.</span>";
		}
	}
}
?>

<? if($ok_mail=="true"){ ?>
	<table width='100%' border='0' cellspacing='1' cellpadding='1'>
		<tr><td><span class='txtform'>Le message ci-dessous nous a bien été transmis, et nous vous en remercions.</span></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td><tt><?echo nl2br(stripslashes($corps));?></tt></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td><span class='txtform'>Nous allons y donner suite dans les meilleurs délais.<br>A bientôt.</span></td></tr>
	</table>
<? }else{ ?>
<form action='<? echo $PHP_SELF ?>' method='post' name='Form'>
<table width='100%' border='0' cellspacing='1' cellpadding='1'>
<? if($erreur){ ?><tr><td colspan='2' bgcolor='red'><span class='txterror'><font color='white'><b>&nbsp;ERREUR, votre message n'a pas été transmis</b></font></span></td></tr><tr><td colspan='2'><ul><?echo$erreur?></ul></td></tr><?}?>
<tr><td colspan='2'><span class='txterror'>Les champs marqué d'un * sont obligatoires</span></td></tr>
<tr><td align='right' width='30%'><span class='txtform'>Nom* :</span></td><td><input type='text' style='width:200 <?if($errf_1==1){print("; background-color: ".$color_form_warn."; color: ".$color_font_warn);}?>;' name='f_1' value='<?echo stripslashes($f_1);?>' size='24' border='0'></td></tr>
<tr><td align='right' width='30%'><span class='txtform'>Prénom* :</span></td><td><input type='text' style='width:200 <?if($errf_2==1){print("; background-color: ".$color_form_warn."; color: ".$color_font_warn);}?>;' name='f_2' value='<?echo stripslashes($f_2);?>' size='24' border='0'></td></tr>
<tr><td align='right' width='30%'><span class='txtform'>E-mail* :</span></td><td><input type='text' style='width:200 <?if($errf_3==1){print("; background-color: ".$color_form_warn."; color: ".$color_font_warn);}?>;' name='f_3' value='<?echo stripslashes($f_3);?>' size='24' border='0'></td></tr>
<tr><td align='right' width='30%'><span class='txtform'>ID Ivao* :</span></td><td><input type='text' style='width:200 <?if($errf_4==1){print("; background-color: ".$color_form_warn."; color: ".$color_font_warn);}?>;' name='f_4' value='<?echo stripslashes($f_4);?>' size='24' border='0'></td></tr>
<tr><td align='right' width='30%'><span class='txtform'>Heures de vol IVAO :</span></td><td><input type='text' style='width:200 <?if($errf_5==1){print("; background-color: ".$color_form_warn."; color: ".$color_font_warn);}?>;' name='f_5' value='<?echo stripslashes($f_5);?>' size='24' border='0'></td></tr>
<tr><td align='right' width='30%'><span class='txtform'>Ville de résidence :</span></td><td><input type='text' style='width:200 <?if($errf_6==1){print("; background-color: ".$color_form_warn."; color: ".$color_font_warn);}?>;' name='f_6' value='<?echo stripslashes($f_6);?>' size='24' border='0'></td></tr>
<tr><td align='right' width='30%'><span class='txtform'>Avez-vous une expérience en pilotage réel ?* :</span></td><td>
<table border='0' cellspacing='0' cellpadding='0'>
<? for($id=0;$id<count($list['f_7']);$id++){
if($f_7[$id]==1){$ct="checked";}
print("<tr><td><input ".$ct." type='checkbox' name='f_7[$id]' value='1' border='0'></td><td><span class='txtform'>".$list[f_7][$id]."</span></td></tr>");
unset($ct);
}?>
</table>
</td></tr>
<tr><td align='right' width='30%'><span class='txtform'>Quel simulateur de vol utilisez vous ?* :</span></td><td>
<table border='0' cellspacing='0' cellpadding='0'>
<? for($id=0;$id<count($list['f_8']);$id++){
if($f_8[$id]==1){$ct="checked";}
print("<tr><td><input ".$ct." type='checkbox' name='f_8[$id]' value='1' border='0'></td><td><span class='txtform'>".$list[f_8][$id]."</span></td></tr>");
unset($ct);
}?>
</table>
</td></tr>
<tr><td align='right' width='30%'><span class='txtform'>Avez-vous lu les manuels MANEX et MANOPS ITVA ?* :</span></td><td>
<table border='0' cellspacing='0' cellpadding='0'>
<? for($id=0;$id<count($list['f_9']);$id++){
if($f_9[$id]==1){$ct="checked";}
print("<tr><td><input ".$ct." type='checkbox' name='f_9[$id]' value='1' border='0'></td><td><span class='txtform'>".$list[f_9][$id]."</span></td></tr>");
unset($ct);
}?>
</table>
</td></tr>
<tr><td align='right' width='30%'><span class='txtform'>Etes-vous (ou avez vous été) inscrit dans une autre VA ?* :</span></td><td><textarea style='width:360 <?if($errf_10==1){print("; background-color: ".$color_form_warn."; color: ".$color_font_warn);}?>;' name='f_10' rows='6' cols='40'><?echo$f_10?></textarea></td></tr>
<tr><td align='right' width='30%'><span class='txtform'>Pourquoi voulez-vous intégrer ITVA (motivations) ?* :</span></td><td><textarea style='width:360 <?if($errf_11==1){print("; background-color: ".$color_form_warn."; color: ".$color_font_warn);}?>;' name='f_11' rows='6' cols='40'><?echo$f_11?></textarea></td></tr>
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
