<?php
if (stristr($_SERVER["SCRIPT_NAME"], "ctconfig.inc")) {
    header("location:../index.php");
    die();
}

// libellé dans boîte latérale
$ctlbl0 = stripslashes("Nous contacter"); // 1ère langue
$ctlbl1 = stripslashes("Contact us"); // 2ème langue
// adresses mail
$fdest[0] = stripslashes("it001alex@airinter-va.org"); // email en copie
$fdest[1] = stripslashes("webmaster@airinter-va.org"); // email destinataire par défaut
//  Noms des rubriques en langue principale
$fname[0] = stripslashes("Choisissez un destinataire"); // Choix destinataire
$fname[1] = stripslashes("Webmestre"); // Nom destinataire par défaut
$fmail[1] = stripslashes("Vos nom et prénom"); // Pseudo expéditeur
$fmail[2] = stripslashes("Votre courriel"); // e-mail expéditeur
$ftxt[1] = stripslashes("Votre ville et votre pays"); // 1er champ texte
$ftxt[2] = stripslashes("Membre d\'une autre VA ? Si oui, laquelle"); // 2ème champ texte
$ftxt[3] = stripslashes("Avez-vous lu les MANEX et MANOPS ?"); // 3ème champ texte
$ftxt[4] = stripslashes("ID IVAO"); // 4ème champ texte
$ftxt[5] = stripslashes("ID VATSIM"); // 5ème champ texte
$ftxt[6] = stripslashes("Heures de vol totales en réseau"); // 6ème champ texte
$fsel[0] = stripslashes("Expérience vol réel ?"); // Champ select
$fsel[1] = stripslashes("Non"); // Option 1
$fsel[2] = stripslashes("Elève pilote / ULM"); // Option 2
$fsel[3] = stripslashes("CPL/IR - FI/FE"); // Option 3
$fsel[4] = stripslashes("ATPL / ATC"); // Option 4
$frad[0] = stripslashes("Simulateur de vol principal"); // Champ bouton radio
$frad[1] = stripslashes("FS2004/FSX"); // Choix 1
$frad[2] = stripslashes("X-Plane"); // Choix 2
$frad[3] = stripslashes("MSFS 2020"); // Choix 3
$frad[4] = stripslashes("MSFS 2024"); // Choix 4
$fchk[0] = stripslashes("Vol en réseau"); // Champ checkbox
$fchk[1] = stripslashes("Non"); // Choix 1
$fchk[2] = stripslashes("Autres"); // Choix 2
$fchk[3] = stripslashes("IVAO"); // Choix 3
$fchk[4] = stripslashes("VATSIM"); // Choix 4
$fmes[1] = stripslashes("Votre message"); // Champ message
//  Rubric names in secondary language
$f2name[0] = stripslashes(""); // Choice of receiver
$f2name[1] = stripslashes("Webmestre"); // Name of default receiver
$fmail[11] = stripslashes("Your first and last name"); // Nickname sender
$fmail[12] = stripslashes("Your email"); // e-mail sender
$ftxt[11] = stripslashes("Your town and country"); // 1st text area
$ftxt[12] = stripslashes("Another VA member? If yes, which one"); // 2nd text area
$ftxt[13] = stripslashes("Did you read the FOM and AOM?"); // 3rd text area
$ftxt[14] = stripslashes("IVAO ID"); // 4th text area
$ftxt[15] = stripslashes("VATSIM ID"); // 5th text area
$ftxt[16] = stripslashes("Total time online"); // 6th text area
$fsel[10] = stripslashes("Real flight experience?"); // Select area
$fsel[11] = stripslashes("No"); // Option 1
$fsel[12] = stripslashes("Student / UL"); // Option 2
$fsel[13] = stripslashes("CPL/IR - CFI/CFII/MEI"); // Option 3
$fsel[14] = stripslashes("ATPL / ATC"); // Option 4
$frad[10] = stripslashes("Main Flight Simulator"); // radio button area
$frad[11] = stripslashes("FS2004/FSX"); // Choice 1
$frad[12] = stripslashes("X-Plane"); // Choice 2
$frad[13] = stripslashes("MSFS 2020"); // Choice 3
$frad[14] = stripslashes("MSFS 2024"); // Choice 4
$fchk[10] = stripslashes("Online flying"); // Checkbox area
$fchk[11] = stripslashes("No"); // Choice 1
$fchk[12] = stripslashes("Others"); // Choice 2
$fchk[13] = stripslashes("IVAO"); // Choice 3
$fchk[14] = stripslashes("VATSIM"); // Choice 4
$fmes[11] = stripslashes("Your message"); // Message area
// activation des champs on / off /fix
$chmail[1] = stripslashes("fix"); // Champ nom 
$chmail[2] = stripslashes("fix"); // Champ email
$chtxt[1] = stripslashes("fix"); // 1er champ
$chtxt[2] = stripslashes(""); // 2ème champ
$chtxt[3] = stripslashes(""); // 3ème champ
$chtxt[4] = stripslashes(""); // 4ème champ
$chtxt[5] = stripslashes(""); // 5ème champ
$chtxt[6] = stripslashes(""); // 6ème champ
$chsel[0] = stripslashes(""); // Select
$chrad[0] = stripslashes(""); // Boutons radio
$chchk[0] = stripslashes(""); // Checkbox
$chmes[1] = stripslashes("fix"); // message
$chfic[0] = stripslashes("2"); // fichier joint
// Contact Tel - Contact Postal au dessous de Contact e-Mail(below)
$ftelbelow[0] = stripslashes(""); // on/off
$fletbelow[0] = stripslashes("on"); // on/off
// contact par téléphone
$ftel[0] = stripslashes(""); // on/off
$ftel[1] = stripslashes("<fieldset class=\"border rounded\"><legend><span style=\"font-size: medium;\">•</span><span style=\"font-size: medium;\"> Nous contacter par téléphone</span></legend><br />
Vous pouvez nous joindre du lundi au vendredi de 9h à 12h et de 14 à 19h au :<br />
&nbsp;
<div style=\"text-align: center;\"><span style=\"font-size:16px;\"><b>01.00.88.99.99</b></span></div>
</fieldset>
"); // texte en langue 1
$ftel[11] = stripslashes(""); // text in language 2
// contact par courrier
$flet[0] = stripslashes(""); // on/off
$flet[1] = stripslashes("<fieldset class=\"border rounded\"><legend><span style=\"font-size: medium;\">•</span><span style=\"font-size: medium;\"> Pour venir nous voir ou nous écrire</span></legend><br />
&nbsp; N\'hésitez pas, une seule adresse :
<p style=\"text-align: center;\"><span style=\"font-size: medium;\">Ets Jean <b>Peuplus</b>,<br />
2 rue de l\'abbaye<br />
97899 </span><b><span style=\"font-size: medium;\">CHAIMOIS</span></b><br />
<span style=\"font-size: medium;\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (France)</span></p>
</fieldset>
"); // texte en langue 1
$flet[11] = stripslashes(""); // text in language 2

$far   = stripslashes("on"); // receipt on/off
$chkcc = stripslashes("on"); // copy to webmaster on/off
?>
