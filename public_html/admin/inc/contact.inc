<?php
/*******************************************************************************
 *   Config Advanced contact
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
 * v6.00.22 (December 19, 2025) : Modification select zone
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'contact.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[13] == '') {
        $nomzone = $admin[1183];
        include 'access.inc';
        exit();
    }
}

$lng      = import('lng');
$act      = import('act');
$regit    = import('regit');
$ctconfig = import('ctconfig');
$ctname   = import('ctname');
$ctlbl0   = import('ctlbl0');
$ctlbl1   = import('ctlbl1');

require CHEMIN.'admin/editors/editors_functions.php';
include CHEMIN.INCREP.'func_contact.php';

if ($regit == 1) { // enregistrer la config
    
	$nbdest  = !isset($_POST['nbdest']) ? count($fdest) : import('nbdest');
    $fdest1  = import('fdest1'); // email destinataire par défaut
    $fname1  = addslashes(import('fname1'));
    $f2name1 = addslashes(import('f2name1'));
    $ftel1   = import('ftel1',  'POST', false);
    $ftel11  = import('ftel11', 'POST', false);
    $flet1   = import('flet1',  'POST', false);
    $flet11  = import('flet11', 'POST', false);
    
    // mail destinataire
    $fdest[0]  = import('fdest0'); // email en copie Cc
    $fdest[1]  = ($fdest1 == '') ? $user[1] : $fdest1; // email destinataire par défaut
	for ($d = 2; $d < $nbdest; $d++) {
		$fdest[$d] = import('fdest'.$d); // email destinataire 1+d
	}
    // Noms langue principale
    $fname[0]  = addslashes(import('fname0')); // Choix de destinataire
    $fname[1]  = ($fname1 == '') ? $admin[718] : $fname1; // Nom destinataire 1
	for ($d = 2; $d < $nbdest; $d++) {
		$fname[$d] = addslashes(import('fname'.$d)); // Nom destinataire d
	}
    $fmail[1]  = import('fmail1'); // Pseudo expéditeur
    $fmail[2]  = import('fmail2'); // e-mail expéditeur
    $ftxt[1]   = addslashes(import('ftxt1')); // 1er champ texte
    $ftxt[2]   = addslashes(import('ftxt2')); // 2ème champ texte
    $ftxt[3]   = addslashes(import('ftxt3')); // 3ème champ texte
    $ftxt[4]   = addslashes(import('ftxt4')); // 4ème champ texte
    $ftxt[5]   = addslashes(import('ftxt5')); // 5ème champ texte
    $ftxt[6]   = addslashes(import('ftxt6')); // 6ème champ texte
    $fsel[0]   = addslashes(import('fsel0')); // Champ select
    $fsel[1]   = addslashes(import('fsel1')); // Option 1
    $fsel[2]   = addslashes(import('fsel2')); // Option 2
    $fsel[3]   = addslashes(import('fsel3')); // Option 3
    $fsel[4]   = addslashes(import('fsel4')); // Option 4
    $frad[0]   = addslashes(import('frad0')); // Champ bouton radio
    $frad[1]   = addslashes(import('frad1')); // Choix 1
    $frad[2]   = addslashes(import('frad2')); // Choix 2
    $frad[3]   = addslashes(import('frad3')); // Choix 3
    $frad[4]   = addslashes(import('frad4')); // Choix 4
    $fchk[0]   = addslashes(import('fchk0')); // Champ checkbox
    $fchk[1]   = addslashes(import('fchk1')); // Choix 1
    $fchk[2]   = addslashes(import('fchk2')); // Choix 2
    $fchk[3]   = addslashes(import('fchk3')); // Choix 3
    $fchk[4]   = addslashes(import('fchk4')); // Choix 4
    $fmes[1]   = import('fmes1'); // Champ message
    // Noms langue secondaire
    $f2name[0] = addslashes(import('f2name0')); // Choice of receiver
    $f2name[1] = ($f2name1 == '') ? $admin[718] : $f2name1; // Name default receiver
	for ($d = 2; $d < $nbdest; $d++) {
		$f2name[$d] = addslashes(import('f2name'.$d)); // Name receiver d
	}
    $fmail[11] = import('fmail11'); // Nickname sender
    $fmail[12] = import('fmail12'); // e-mail sender
    $ftxt[11]  = addslashes(import('ftxt11')); // 1st text area
    $ftxt[12]  = addslashes(import('ftxt12')); // 2nd text area
    $ftxt[13]  = addslashes(import('ftxt13')); // 3rd text area
    $ftxt[14]  = addslashes(import('ftxt14')); // 4th text area
    $ftxt[15]  = addslashes(import('ftxt15')); // 5th text area
    $ftxt[16]  = addslashes(import('ftxt16')); // 6th text area
    $fsel[10]  = addslashes(import('fsel10')); // Select area
    $fsel[11]  = addslashes(import('fsel11')); // Option 1
    $fsel[12]  = addslashes(import('fsel12')); // Option 2
    $fsel[13]  = addslashes(import('fsel13')); // Option 3
    $fsel[14]  = addslashes(import('fsel14')); // Option 4
    $frad[10]  = addslashes(import('frad10')); // Button radio
    $frad[11]  = addslashes(import('frad11')); // Choice 1
    $frad[12]  = addslashes(import('frad12')); // Choice 2
    $frad[13]  = addslashes(import('frad13')); // Choice 3
    $frad[14]  = addslashes(import('frad14')); // Choice 4
    $fchk[10]  = addslashes(import('fchk10')); // Check box
    $fchk[11]  = addslashes(import('fchk11')); // Choice 1
    $fchk[12]  = addslashes(import('fchk12')); // Choice 2
    $fchk[13]  = addslashes(import('fchk13')); // Choice 3
    $fchk[14]  = addslashes(import('fchk14')); // Choice 4
    $fmes[11]  = import('fmes11'); // Message area
    // check activation on/off/fix
    $chmail[1] = 'fix'; //Nom
    $chmail[2] = 'fix'; //email
    $chtxt[1]  = import('chtxt1'); // 1er champ
    $chtxt[2]  = import('chtxt2'); // 2ème champ
    $chtxt[3]  = import('chtxt3'); // 3ème champ
    $chtxt[4]  = import('chtxt4'); // 4ème champ
    $chtxt[4]  = import('chtxt4'); // 4ème champ
    $chtxt[4]  = import('chtxt4'); // 4ème champ
    $chtxt[5]  = import('chtxt5'); // 5ème champ
    $chtxt[6]  = import('chtxt6'); // 6ème champ
    $chsel[0]  = import('chsel0'); // Select
    $chrad[0]  = import('chrad0'); // Bouton radio
    $chchk[0]  = import('chchk0'); // Checkbox
    $chmes[1]  = 'fix'; // Message
    $chfic[0]  = import('chfic0', 'POST', true, '0'); // Fichier joint
	// Contact Tel - Contact Postal au dessous de Contact e-Mail(below)
	$ftelbelow[0] = import('ftelbelow0'); // on/off
	$fletbelow[0] = import('fletbelow0'); // on/off
    // Contact par téléphone
    $ftel[0]   = import('ftel0'); // on/off
    $ftel[1]   = addslashes(import('ftel1', 'POST', false));  // libellé langue 1
    $ftel[11]  = addslashes(import('ftel11', 'POST', false)); // libellé langue 2
    // Contact par courrier
    $flet[0]   = import('flet0'); // on/off
    $flet[1]   = addslashes(import('flet1', 'POST', false));  // libellé langue 1
    $flet[11]  = addslashes(import('flet11', 'POST', false)); // libellé langue 2
    $far       = import('ar'); // avis de remise on/off
    $chkcc     = import('chkcc'); // copie au webmestre on/off

    $mettre = "<?php
if (stristr(\$_SERVER[\"SCRIPT_NAME\"], \"".$ctconfig.INCEXT."\")) {
    header(\"location:../index.php\");
    die();
}

// libellé dans boîte latérale
\$ctlbl0 = stripslashes(\"$ctlbl0\"); // 1ère langue
\$ctlbl1 = stripslashes(\"$ctlbl1\"); // 2ème langue
// adresses mail
\$fdest[0] = stripslashes(\"$fdest[0]\"); // email en copie
\$fdest[1] = stripslashes(\"$fdest[1]\"); // email destinataire par défaut";
	for ($d = 2; $d < count($fdest); $d++)
		if ($fdest[$d] != '' && (($lang[0] == $lng && $fname[$d] != '') || ($lang[1] == $lng && $f2name[$d] != ''))) $mettre .= "
\$fdest[$d] = stripslashes(\"$fdest[$d]\"); // email destinataire $d";
  $mettre .= "
//  Noms des rubriques en langue principale
\$fname[0] = stripslashes(\"$fname[0]\"); // Choix destinataire
\$fname[1] = stripslashes(\"$fname[1]\"); // Nom destinataire par défaut";
	for ($d = 2; $d < $nbdest; $d++)
		if ($fdest[$d] != '' && $fname[$d] != '') $mettre .= "
\$fname[$d] = stripslashes(\"$fname[$d]\"); // Nom destinataire $d";
	$mettre .= "
\$fmail[1] = stripslashes(\"$fmail[1]\"); // Pseudo expéditeur
\$fmail[2] = stripslashes(\"$fmail[2]\"); // e-mail expéditeur
\$ftxt[1] = stripslashes(\"$ftxt[1]\"); // 1er champ texte
\$ftxt[2] = stripslashes(\"$ftxt[2]\"); // 2ème champ texte
\$ftxt[3] = stripslashes(\"$ftxt[3]\"); // 3ème champ texte
\$ftxt[4] = stripslashes(\"$ftxt[4]\"); // 4ème champ texte
\$ftxt[5] = stripslashes(\"$ftxt[5]\"); // 5ème champ texte
\$ftxt[6] = stripslashes(\"$ftxt[6]\"); // 6ème champ texte
\$fsel[0] = stripslashes(\"$fsel[0]\"); // Champ select
\$fsel[1] = stripslashes(\"$fsel[1]\"); // Option 1
\$fsel[2] = stripslashes(\"$fsel[2]\"); // Option 2
\$fsel[3] = stripslashes(\"$fsel[3]\"); // Option 3
\$fsel[4] = stripslashes(\"$fsel[4]\"); // Option 4
\$frad[0] = stripslashes(\"$frad[0]\"); // Champ bouton radio
\$frad[1] = stripslashes(\"$frad[1]\"); // Choix 1
\$frad[2] = stripslashes(\"$frad[2]\"); // Choix 2
\$frad[3] = stripslashes(\"$frad[3]\"); // Choix 3
\$frad[4] = stripslashes(\"$frad[4]\"); // Choix 4
\$fchk[0] = stripslashes(\"$fchk[0]\"); // Champ checkbox
\$fchk[1] = stripslashes(\"$fchk[1]\"); // Choix 1
\$fchk[2] = stripslashes(\"$fchk[2]\"); // Choix 2
\$fchk[3] = stripslashes(\"$fchk[3]\"); // Choix 3
\$fchk[4] = stripslashes(\"$fchk[4]\"); // Choix 4
\$fmes[1] = stripslashes(\"$fmes[1]\"); // Champ message
//  Rubric names in secondary language
\$f2name[0] = stripslashes(\"$f2name[0]\"); // Choice of receiver
\$f2name[1] = stripslashes(\"$f2name[1]\"); // Name of default receiver";
	for ($d = 2; $d < $nbdest; $d++)
		if ($fdest[$d] != '' && $fname[$d] != '' && $f2name[$d] != '') $mettre .= "
\$f2name[$d] = stripslashes(\"$f2name[$d]\"); // Nom destinataire $d";
	$mettre .= "
\$fmail[11] = stripslashes(\"$fmail[11]\"); // Nickname sender
\$fmail[12] = stripslashes(\"$fmail[12]\"); // e-mail sender
\$ftxt[11] = stripslashes(\"$ftxt[11]\"); // 1st text area
\$ftxt[12] = stripslashes(\"$ftxt[12]\"); // 2nd text area
\$ftxt[13] = stripslashes(\"$ftxt[13]\"); // 3rd text area
\$ftxt[14] = stripslashes(\"$ftxt[14]\"); // 4th text area
\$ftxt[15] = stripslashes(\"$ftxt[15]\"); // 5th text area
\$ftxt[16] = stripslashes(\"$ftxt[16]\"); // 6th text area
\$fsel[10] = stripslashes(\"$fsel[10]\"); // Select area
\$fsel[11] = stripslashes(\"$fsel[11]\"); // Option 1
\$fsel[12] = stripslashes(\"$fsel[12]\"); // Option 2
\$fsel[13] = stripslashes(\"$fsel[13]\"); // Option 3
\$fsel[14] = stripslashes(\"$fsel[14]\"); // Option 4
\$frad[10] = stripslashes(\"$frad[10]\"); // radio button area
\$frad[11] = stripslashes(\"$frad[11]\"); // Choice 1
\$frad[12] = stripslashes(\"$frad[12]\"); // Choice 2
\$frad[13] = stripslashes(\"$frad[13]\"); // Choice 3
\$frad[14] = stripslashes(\"$frad[14]\"); // Choice 4
\$fchk[10] = stripslashes(\"$fchk[10]\"); // Checkbox area
\$fchk[11] = stripslashes(\"$fchk[11]\"); // Choice 1
\$fchk[12] = stripslashes(\"$fchk[12]\"); // Choice 2
\$fchk[13] = stripslashes(\"$fchk[13]\"); // Choice 3
\$fchk[14] = stripslashes(\"$fchk[14]\"); // Choice 4
\$fmes[11] = stripslashes(\"$fmes[11]\"); // Message area
// activation des champs on / off /fix
\$chmail[1] = stripslashes(\"$chmail[1]\"); // Champ nom 
\$chmail[2] = stripslashes(\"$chmail[2]\"); // Champ email
\$chtxt[1] = stripslashes(\"$chtxt[1]\"); // 1er champ
\$chtxt[2] = stripslashes(\"$chtxt[2]\"); // 2ème champ
\$chtxt[3] = stripslashes(\"$chtxt[3]\"); // 3ème champ
\$chtxt[4] = stripslashes(\"$chtxt[4]\"); // 4ème champ
\$chtxt[5] = stripslashes(\"$chtxt[5]\"); // 5ème champ
\$chtxt[6] = stripslashes(\"$chtxt[6]\"); // 6ème champ
\$chsel[0] = stripslashes(\"$chsel[0]\"); // Select
\$chrad[0] = stripslashes(\"$chrad[0]\"); // Boutons radio
\$chchk[0] = stripslashes(\"$chchk[0]\"); // Checkbox
\$chmes[1] = stripslashes(\"$chmes[1]\"); // message
\$chfic[0] = stripslashes(\"$chfic[0]\"); // fichier joint
// Contact Tel - Contact Postal au dessous de Contact e-Mail(below)
\$ftelbelow[0] = stripslashes(\"$ftelbelow[0]\"); // on/off
\$fletbelow[0] = stripslashes(\"$fletbelow[0]\"); // on/off
// contact par téléphone
\$ftel[0] = stripslashes(\"$ftel[0]\"); // on/off
\$ftel[1] = stripslashes(\"$ftel[1]\"); // texte en langue 1
\$ftel[11] = stripslashes(\"$ftel[11]\"); // text in language 2
// contact par courrier
\$flet[0] = stripslashes(\"$flet[0]\"); // on/off
\$flet[1] = stripslashes(\"$flet[1]\"); // texte en langue 1
\$flet[11] = stripslashes(\"$flet[11]\"); // text in language 2

\$far   = stripslashes(\"$far\"); // receipt on/off
\$chkcc = stripslashes(\"$chkcc\"); // copy to webmaster on/off
?>
";
    WriteFullDB(CONFIGREP.$ctconfig.INCEXT, $mettre);
	unset($fdest, $fname, $f2name);
	$ctfiles = ReadDBFields(CONFIGREP.'ctconfigs.dtb');
	for($i = 0; $i < count($ctfiles); $i++) {
		if ($ctfiles[$i][1] == $ctconfig) {
			$ctfiles[$i][3] = $ctlbl0;
			$ctfiles[$i][4] = $ctlbl1;
		}
	}
	WriteDBFields(CONFIGREP.'ctconfigs.dtb', $ctfiles);
    if ($site[7] == 'on' || PHP700) {
        @ob_flush();
        sleep(PHP700 ? 3 : $site[8]);
        echo BeginJavascript().'window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=contact&act='.$act.'&ctconfig='.$ctconfig.'";'.EndJavascript();
    }
}

if ($regit == 2) {
	if (is_file(CONFIGREP.$ctname.INCEXT)) {
		echo BeginJavascript().'
alert("'.addslashes($admin[1585].$admin[1590].' contact'.$admin[1586]).'");
history.back();
'.EndJavascript();
	} else {
		$ctfiles = ReadDBFields(CONFIGREP.'ctconfigs.dtb');
		rsort($ctfiles);
		$num = $ctfiles[0][0] + 1;
		$i   = count($ctfiles);
		$ctfiles[$i] = array($num, $ctname, 'a', $ctlbl0, $ctlbl1);
		sort($ctfiles);
		WriteDBFields(CONFIGREP.'ctconfigs.dtb', $ctfiles);

		$mettre = "<?php
if (stristr(\$_SERVER[\"SCRIPT_NAME\"], \"".$ctname.INCEXT."\")) {
    header(\"location:../index.php\");
    die();
}

// libellé dans boîte latérale
\$ctlbl0 = stripslashes(\"$ctlbl0\"); // 1ère langue
\$ctlbl1 = stripslashes(\"$ctlbl1\"); // 2ème langue
// adresses mail
\$fdest[0] = stripslashes(\"\"); // email en copie
\$fdest[1] = stripslashes(\"\"); // email destinataire par défaut";
		for ($d = 2; $d < 4; $d++)
			$mettre .= "
\$fdest[$d] = stripslashes(\"\"); // email destinataire $d";
		$mettre .= "
//  Noms des rubriques en langue principale
\$fname[0] = stripslashes(\"\"); // Choix destinataire
\$fname[1] = stripslashes(\"\"); // Nom destinataire par défaut";
		for ($d = 2; $d < 4; $d++)
			$mettre .= "
\$fname[$d] = stripslashes(\"\"); // Nom destinataire $d";
		$mettre .= "
\$fmail[1] = stripslashes(\"\"); // Pseudo expéditeur
\$fmail[2] = stripslashes(\"\"); // e-mail expéditeur
\$ftxt[1] = stripslashes(\"\"); // 1er champ texte
\$ftxt[2] = stripslashes(\"\"); // 2ème champ texte
\$ftxt[3] = stripslashes(\"\"); // 3ème champ texte
\$ftxt[4] = stripslashes(\"\"); // 4ème champ texte
\$ftxt[5] = stripslashes(\"\"); // 5ème champ texte
\$ftxt[6] = stripslashes(\"\"); // 6ème champ texte
\$fsel[0] = stripslashes(\"\"); // Champ select
\$fsel[1] = stripslashes(\"\"); // Option 1
\$fsel[2] = stripslashes(\"\"); // Option 2
\$fsel[3] = stripslashes(\"\"); // Option 3
\$fsel[4] = stripslashes(\"\"); // Option 4
\$frad[0] = stripslashes(\"\"); // Champ bouton radio
\$frad[1] = stripslashes(\"\"); // Choix 1
\$frad[2] = stripslashes(\"\"); // Choix 2
\$frad[3] = stripslashes(\"\"); // Choix 3
\$frad[4] = stripslashes(\"\"); // Choix 4
\$fchk[0] = stripslashes(\"\"); // Champ checkbox
\$fchk[1] = stripslashes(\"\"); // Choix 1
\$fchk[2] = stripslashes(\"\"); // Choix 2
\$fchk[3] = stripslashes(\"\"); // Choix 3
\$fchk[4] = stripslashes(\"\"); // Choix 4
\$fmes[1] = stripslashes(\"\"); // Champ message
//  Rubric names in secondary language
\$f2name[0] = stripslashes(\"\"); // Choice of receiver
\$f2name[1] = stripslashes(\"\"); // Name of default receiver";
		for ($d = 2; $d < 4; $d++)
			$mettre .= "
\$f2name[$d] = stripslashes(\"\"); // Nom destinataire $d";
		$mettre .= "
\$fmail[11] = stripslashes(\"\"); // Nickname sender
\$fmail[12] = stripslashes(\"\"); // e-mail sender
\$ftxt[11] = stripslashes(\"\"); // 1st text area
\$ftxt[12] = stripslashes(\"\"); // 2nd text area
\$ftxt[13] = stripslashes(\"\"); // 3rd text area
\$ftxt[14] = stripslashes(\"\"); // 4th text area
\$ftxt[15] = stripslashes(\"\"); // 5th text area
\$ftxt[16] = stripslashes(\"\"); // 6th text area
\$fsel[10] = stripslashes(\"\"); // Select area
\$fsel[11] = stripslashes(\"\"); // Option 1
\$fsel[12] = stripslashes(\"\"); // Option 2
\$fsel[13] = stripslashes(\"\"); // Option 3
\$fsel[14] = stripslashes(\"\"); // Option 4
\$frad[10] = stripslashes(\"\"); // radio button area
\$frad[11] = stripslashes(\"\"); // Choice 1
\$frad[12] = stripslashes(\"\"); // Choice 2
\$frad[13] = stripslashes(\"\"); // Choice 3
\$frad[14] = stripslashes(\"\"); // Choice 4
\$fchk[10] = stripslashes(\"\"); // Checkbox area
\$fchk[11] = stripslashes(\"\"); // Choice 1
\$fchk[12] = stripslashes(\"\"); // Choice 2
\$fchk[13] = stripslashes(\"\"); // Choice 3
\$fchk[14] = stripslashes(\"\"); // Choice 4
\$fmes[11] = stripslashes(\"\"); // Message area
// activation des champs on / off /fix
\$chmail[1] = stripslashes(\"\"); // Champ nom 
\$chmail[2] = stripslashes(\"\"); // Champ email
\$chtxt[1] = stripslashes(\"\"); // 1er champ
\$chtxt[2] = stripslashes(\"\"); // 2ème champ
\$chtxt[3] = stripslashes(\"\"); // 3ème champ
\$chtxt[4] = stripslashes(\"\"); // 4ème champ
\$chtxt[5] = stripslashes(\"\"); // 5ème champ
\$chtxt[6] = stripslashes(\"\"); // 6ème champ
\$chsel[0] = stripslashes(\"\"); // Select
\$chrad[0] = stripslashes(\"\"); // Boutons radio
\$chchk[0] = stripslashes(\"\"); // Checkbox
\$chmes[1] = stripslashes(\"\"); // message
\$chfic[0] = stripslashes(\"\"); // fichier joint
// Contact Tel - Contact Postal au dessous de Contact e-Mail(below)
\$ftelbelow[0] = stripslashes(\"\"); // on/off
\$fletbelow[0] = stripslashes(\"\"); // on/off
// contact par téléphone
\$ftel[0] = stripslashes(\"\"); // on/off
\$ftel[1] = stripslashes(\"\"); // texte en langue 1
\$ftel[11] = stripslashes(\"\"); // text in language 2
// contact par courrier
\$flet[0] = stripslashes(\"\"); // on/off
\$flet[1] = stripslashes(\"\"); // texte en langue 1
\$flet[11] = stripslashes(\"\"); // text in language 2

\$far   = stripslashes(\"\"); // receipt on/off
\$chkcc = stripslashes(\"\"); // copy to webmaster on/off
?>
";
		WriteFullDB(CONFIGREP.$ctname.INCEXT, $mettre);
	}
    if ($site[7] == 'on' || PHP700) {
        @ob_flush();
        sleep(PHP700 ? 3 : $site[8]);
        echo BeginJavascript().'window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=contact&act='.$act.'";'.EndJavascript();
    }
}

if ($regit == 3) {
	$ctfiles = ReadDBFields(CONFIGREP.'ctconfigs.dtb');
    for($i = 0; $i < count($ctfiles); $i++) {
		if ($ctfiles[$i][1] == $ctconfig) $ctfiles[$i][2] = $ctfiles[$i][2] == 'a' ? 'i' : 'a';
	}
	WriteDBFields(CONFIGREP.'ctconfigs.dtb', $ctfiles);
    if ($site[7] == 'on') {
        flush();
        sleep($site[8]);
        header('location:'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=contact&act='.$act);
    }
}

if ($regit == 4) {
	$cttemp  = array();
	$ctfiles = ReadDBFields(CONFIGREP.'ctconfigs.dtb');
    for($i = 0; $i < count($ctfiles); $i++) {
		if ($ctfiles[$i][1] != $ctconfig) $cttemp[] = $ctfiles[$i];
	}
	WriteDBFields(CONFIGREP.'ctconfigs.dtb', $cttemp);
	unlink(CONFIGREP.$ctconfig.INCEXT);
    if ($site[7] == 'on') {
        flush();
        sleep($site[8]);
        header('location:'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=contact&act='.$act);
    }
}
    
if ($regit == 5) {
    include CONFIG;
    $serviz[155] = import('serviz155'); // type de courriel Contact
    include 'inc/confupdt.inc';
    if ($site[7] == 'on' || PHP700) {
        @ob_flush();
        sleep(PHP700 ? 3 : $site[8]);
        echo BeginJavascript().'window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=contact&act='.$act.'";'.EndJavascript();
    }
}

if (is_file ($meskin.'confskin.inc')) {
    include $meskin.'confskin.inc';
} else {
    include CHEMIN.'skins/no_skin/confskin.inc';
}

$headinc .= BeginJavascript().'
function VerifyForm0() {
    var sto    = "";
    var erreur = false;
    var lng2   = "'.$lang[1].'";
    regexp     = /^[^\.\s]+(\.[^\.\s]+)*@[^\.\s]+(\.[^\.\s]+)+$/;
    if (document.fcontact.fdest1.value > "") {
        if (!regexp.test(document.fcontact.fdest1.value)) {
            sto += "  - '.addslashes($admin[544]).' #1\n";
			document.fcontact.fdest1.className = "errorInputText";
            erreur = true;
        }
    }
    if (document.fcontact.fdest2.value > "") {
        if (!regexp.test(document.fcontact.fdest2.value)) {
            sto += "  - '.addslashes($admin[544]).' #2\n";
			document.fcontact.fdest2.className = "errorInputText";
            erreur = true;
        }
    }
    if (document.fcontact.fdest3.value > "") {
        if (!regexp.test(document.fcontact.fdest3.value)) {
            sto += "  - '.addslashes($admin[544]).' #3\n";
			document.fcontact.fdest3.className = "errorInputText";
            erreur = true;
        }
    }
    if (document.fcontact.fdest4.value > "") {
        if (!regexp.test(document.fcontact.fdest4.value)) {
            sto += "  - '.addslashes($admin[544]).' #4\n";
			document.fcontact.fdest4.className = "errorInputText";
            erreur = true;
        }
    }
    if (document.fcontact.fdest5.value > "") {
        if (!regexp.test(document.fcontact.fdest5.value)) {
            sto += "  - '.addslashes($admin[544]).' #5\n";
			document.fcontact.fdest5.className = "errorInputText";
            erreur = true;
        }
    }
    var mail1 = document.fcontact.fmail1.value;
    if (mail1.length < 5) {
        sto += "  - '.addslashes($admin[1194].' '.$admin[1195].$admin[1177]).'\n";
		document.fcontact.fmail1.className = "errorInputText";
        erreur = true;
    }
    if (lng2 > "") {
        var mail2 = document.fcontact.fmail2.value;
        if (mail2.length < 5) {
            sto += "  - '.addslashes($admin[1194].' '.$admin[1195].$admin[1178]).'\n";
			document.fcontact.fmail2.className = "errorInputText";
            erreur = true;
        }
    }    
    var mail11 = document.fcontact.fmail11.value;
    if (mail11.length < 5) {
        sto += "  - '.addslashes($admin[1194].' '.$admin[1196].$admin[1177]).'\n";
		document.fcontact.fmail11.className = "errorInputText";
        erreur = true;
    }
    if (lng2 > "") {
        var mail12 = document.fcontact.fmail12.value;
        if (mail12.length < 5) {
            sto += "  - '.addslashes($admin[1194].' '.$admin[1196].$admin[1178]).'\n";
			document.fcontact.fmail12.className = "errorInputText";
            erreur = true;
        }
    }    
    var mes1 = document.fcontact.fmes1.value;
    if (mes1.length < 5) {
        sto += "  - '.addslashes($admin[1194].' '.$admin[1017].$admin[1177]).'\n";
		document.fcontact.fmes1.className = "errorInputText";
        erreur = true;
    }
    if (lng2 > "") {
        var mes11 = document.fcontact.fmes11.value;
        if (mes11.length < 5) {
            sto += "  - '.addslashes($admin[1194].' '.$admin[1017].$admin[1178]).'\n";
			document.fcontact.fmes11.className = "errorInputText";
            erreur = true;
        }
    }    
	if (document.fcontact.ctlbl0.value == "") {
		sto += "  - '.addslashes($admin[949].' '.$admin[1177]).'\n";
		document.fcontact.ctlbl0.className = "errorInputText";
		erreur = true;
	}
	if (lng2 > "") {
		if (document.fcontact.ctlbl1.value == "") {
			sto += "  - '.addslashes($admin[949].' '.$admin[1178]).'\n";
			document.fcontact.ctlbl1.className = "errorInputText";
			erreur = true;
		}
	}
    if (erreur == true) {
        sto = "'.addslashes($admin[1589]).'\n\n" + sto;
        alert(sto);
        return false;
    }
}
function VerifyForm1() {
    var sto    = "";
    var erreur = false;
    var lng2   = "'.$lang[1].'";
	if (document.scontact.ctname.value == "") {
		sto += "  - '.addslashes($admin[1585].$admin[1590].' contact'.$admin[1586]).'\n";
		document.scontact.ctname.className = "errorInputText";
		erreur = true;
	} else {
		regexp = /^[-a-z0-9_]{2,20}$/;
		if (!regexp.test(document.scontact.ctname.value)) {
			sto += "  - '.addslashes($admin[1585].$admin[1590].' contact'.$admin[1586]).'\n";
			document.scontact.ctname.className = "errorInputText";
			erreur = true;
		}
	}
	if (document.scontact.ctlbl0.value == "") {
		sto += "  - '.addslashes($admin[949].' '.$admin[1177]).'\n";
		document.scontact.ctlbl0.className = "errorInputText";
		erreur = true;
	}
	if (lng2 > "") {
		if (document.scontact.ctlbl1.value == "") {
			sto += "  - '.addslashes($admin[949].' '.$admin[1178]).'\n";
			document.scontact.ctlbl1.className = "errorInputText";
			erreur = true;
		}
	}
	if (erreur == true) {
        sto = "'.addslashes($admin[1589]).'\n\n" + sto;
        alert(sto);
        return false;
    }
}'.EndJavascript();

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[1181]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[1181], "100%");
$n = ($lng == $lang[0] ? 3 : 4);

################################################################################
// choix et création d'un contact
if (empty($act) || $act == 1) {
    include CONFIG;
	$ctfiles = ReadDBFields(CONFIGREP.'ctconfigs.dtb');
    echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=958&mnuid=937&tconfig=0#z2').$admin[1445].'</p>
<hr />
<div class="w-100 m-auto" style="max-width:550px;">
  <fieldset style="padding-top:12px;">
    <form name="scontact" action="admin.php?lng='.$lng.'&amp;pg=contact"  method="post" onsubmit="return VerifyForm1(); return false;">
	  <input type="hidden" name="act" value="1" />
	  <input type="hidden" name="regit" value="2" />
      <div class="table-responsive-md">
	  <table class="table bord w-100 m-auto text-center" style="max-width:480px;">
	    <tr class="forum">
		  <td colspan="2" style="width:360px;text-align:center;"><b>'.$admin[1183].'</b></td>
		  <td colspan="3" style="width:120px;text-align:center;"><b>'.$admin[680].'</b></td>
		</tr>';
    foreach ($ctfiles as $ct) {
		$alt = $ct[2] == 'a' ? $admin[32] : $admin[34];
		$img = $ct[2] == 'a' ? 'on' : 'off';
		echo '
		<tr class="rep">
		  <td style="width:120px;text-align:left;">'.$ct[1].'</td>
		  <td style="width:240px;text-align:left;">'.$ct[$lng == $lang[0] ? 3 : 4].'</td>
		  <td style="width:40px;text-align:center;">
		    <a href="admin.php?lng='.$lng.'&amp;pg=contact&amp;act=2&amp;ctconfig='.$ct[1].'" title="'.$admin[144].' '.$ct[1].'">
			  <i class="fas fa-edit" title="'.$admin[144].' '.$ct[1].'" style="font-size: 1.3rem;"></i>
            </a><br />
		  </td>
		  <td style="width:40px;text-align:center;">';
		if ($ct[1] != 'ctconfig') {
			echo '
	        <a href="admin.php?lng='.$lng.'&amp;pg=contact&amp;act=1&amp;regit=3&amp;ctconfig='.$ct[1].'" title="'.$alt.' '.$ct[1].'">
		      <i class="fas fa-toggle-'.$img.'" title="'.$alt.' '.$ct[1].'" style="font-size: 1.3rem;"></i>
			</a>';
		}
		echo '
		  </td>
		  <td style="width:40px;text-align:center;">';
		if ($ct[1] != 'ctconfig') {
			echo '
		    <a href="admin.php?lng='.$lng.'&amp;pg=contact&amp;act=1&amp;regit=4&amp;ctconfig='.$ct[1].'" title="'.$admin[186].' '.$ct[1].'">
			  <i class="fas fa-trash-alt text-danger" title="'.$admin[186].' '.$ct[1].'" style="font-size: 1.3rem;"></i>
			</a><br />';
		}
		echo '
		  </td>
		</tr>';
    }
    unset($ctfiles);
    echo '
	  </table>
      </div><br />';
    echo '
	  <br />
      <div class="table-responsive-md">
	  <table class="table w-98 m-auto">
	    <tr>
		  <td class="text-right align-middle">
            '.$admin[1590].' contact :
          </td>
		  <td>
            <input class="form-control texte" type="text" name="ctname" value="" />
          </td>
		</tr>
	    <tr>
		  <td class="text-right align-middle">
            '.$admin[949].' '.$admin[1177].' '.$flag1.' :
          </td>
		  <td>
            <input class="form-control texte" type="text" name="ctlbl0" value="" />
          </td>
		</tr>';
	if ($lang[1] != '')
		echo '
	    <tr>
		  <td class="text-right align-middle">
            '.$admin[949].' '.$admin[1178].' '.$flag2.' :
          </td>
		  <td>
            <input class="form-control texte" type="text" name="ctlbl1" value="" />
          </td>
		</tr>';
	echo '
	  </table>
      </div>
      <div class="row quest p-0 mt-2 w-100 m-auto">
	    <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
        <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div></div>
      </div>
    </form>
  </fieldset>
  <fieldset style="padding-top:12px;">
    <form name="mcontact" action="admin.php?lng='.$lng.'&amp;pg=contact"  method="post">
	  <input type="hidden" name="act" value="1" />
	  <input type="hidden" name="regit" value="5" />
      <div style="text-align:center;margin-bottom:12px;">
        '.$admin[230].'<input type="texte" name="serviz155" value="phpmailer" readonly size=8 maxsize=8>
      </div>
      <div class="row quest p-0 mt-2 w-100 m-auto">
	    <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
        <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div></div>
      </div>
    </form>
  </fieldset>
</div>';
}
################################################################################
// modification d'un contact
elseif ($act == 2) {
	if (is_file(CONFIGREP.$ctconfig.INCEXT)) include CONFIGREP.$ctconfig.INCEXT;
	echo '
<p class="text-center w-100 m-auto"><br />'.ToHelp($lng, 'contact').$admin[1189].'</p>
<hr />
<form name="fcontact" action="admin.php?lng='.$lng.'&amp;pg=contact" method="post" onsubmit="return VerifyForm0(); return false;">
  <input type="hidden" name="act" value="2" />
  <input type="hidden" name="regit" value="1" />
  <input type="hidden" name="ctconfig" value="'.$ctconfig.'" />
  <div class="text-center">'.$admin[949].' '.$admin[1446].'</div>
  <div class="table-responsive-md">
  <table class="table w-100 m-auto">
    <tr>
	  <td class="text-center">
        <div class="d-inline-block text-right">'.$flag1.'&nbsp;</div>
	    <div class="d-inline-block text-left">
		  <input class="form-control texte" type="text" name="ctlbl0" value="'.$ctlbl0.'" style="width:240px;" />
        </div>
      </td>
    </tr>';
	if ($lang[1] != '')
		echo '
    <tr>
	  <td class="text-center">
        <div class="d-inline-block text-right">'.$flag2.'&nbsp;</div>
	    <div class="d-inline-block text-left">
		  <input class="form-control texte" type="text" name="ctlbl1" value="'.$ctlbl1.'" style="width:240px;" />
        </div>
      </td>
    </tr>';
	echo '
  </table>
  </div>
  <hr />
  <div class="w-100 m-auto" style="max-width:700px;">
    <div class="row quest p-0 mb-2 w-100 m-auto">
	  <div class="col-md-6">
        '.SubmitButton($admin[857].' '.$admin[1183], 'self.location.href=\'admin.php?lng='.$lng.'&pg=contact&act=1\'', $imggauche, '', 'button').'
      </div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
    </div>
	<fieldset class="m-0"><legend style="text-align:center;">'.mb_strtoupper($admin[1682], $charset).'</legend>';

	// Select email destinataire ------------------------------
	if (!empty($lang[1])) {
	  $tpos = '<span style="padding-left:16px;">&nbsp; </span>';
	  $pos  = '20px';
	} else {
	  $tpos = '<span style="padding-left:32px;">&nbsp; </span>';
	  $pos  = '10px';
	}
	echo '
	  <p style="text-align:center"><strong>'.$admin[1683].' '.mb_strtolower($admin[759], $charset).'</strong></p>
	  <div class="text-left my-0 mx-auto w-100" style="max-width:520px;">
		<div class="w-100 text-left">&bull; ';
	if (!empty($lang[1])) {
		echo '
		  <div class="d-inline-block">'.$flag1.'&nbsp; </div>';
	}
	echo '
		  <div class="d-inline-block">
            <input class="form-control texte" name="fname0" type="text" style="width:208px;" value="'.htmlentities($fname[0], ENT_QUOTES, $charset).'" />
          </div>
		  <div class="d-inline-block"><i class="fas fa-caret-square-down text-secondary" style="font-size: 1.4rem;"></i></div>
        </div>';
	$fname[1] = ($fname[1] == '') ? $admin[718] : $fname[1];
    $f2name[1] = ($f2name[1] == '') ? $admin[718] : $f2name[1];
	$fdest[1] = ($fdest[1] == '') ? $user[1] : $fdest[1];
	if (!empty($lang[1])) {
		echo '
		<div class="w-100 text-left">
		  <div class="d-inline-block">&bull; '.$flag2.'&nbsp; </div>
		  <div class="d-inline-block">
            <input class="form-control texte" name="f2name0" type="text" style="width:208px;" value="'.htmlentities($f2name[0], ENT_QUOTES, $charset).'" />
          </div>
		  <div class="d-inline-block"><i class="fas fa-caret-square-down text-secondary" style="font-size: 1.4rem;"></i></div>
        </div>';
	}
	echo '
      </div>'; 
	$nb     = count($fdest) < 5 ? 5 : count($fdest) + 2;
	$fdest  = array_pad($fdest, $nb, '');
	$fname  = array_pad($fname, $nb, '');
	$f2name = array_pad($f2name, $nb, '');
	echo '
      <input type="hidden" name="nbdest" value='.$nb.' />
      <div class="table-responsive-md">
        <table class="table w-100 p-2">';
	for ($d = 1; $d < $nb; $d++) {
		$note = $d != 1 ? '' : ' (*)';
		echo '
          <tr>
            <td class="align-middle'.(empty($lang[1]) ? ' pb-2' : '').'">';
		if (!empty($lang[1])) {
			echo '
              <div class="d-inline-block align-middle">'.$flag1.'</div>';
		}
		echo '
              <div class="d-inline-block">
                <input class="form-control texte" name="fname'.$d.'" type="text" style="width:160px;" 
                  value="'.htmlentities($fname[$d], ENT_QUOTES, $charset).'" />
              </div>';
		if (!empty($lang[1])) {
			echo '
              <div>
                <div class="d-inline-block align-middle pb-2">'.$flag2.'</div>
                <div class="d-inline-block align-middle pb-2">
                  <input class="form-control texte" name="f2name'.$d.'" type="text" style="width:160px;" 
                    value="'.htmlentities($f2name[$d], ENT_QUOTES, $charset).'" />
                </div>
              </div>';
		}
        echo '
            </td>
            <td class="align-middle'.(empty($lang[1]) ? ' pb-2' : '').'">
              <div class="d-inline-block">
                <input class="form-control texte" name="fdest'.$d.'" type="text" style="width:200px;" value="'.$fdest[$d].'" />
              </div>
              <div class="d-inline-block text-left"> '.$note.'</div>
            </td>
          </tr>';
	}
    echo '
        </table>
      </div>';

	// Copie Cc et Bcc -----------------------------------------
	echo '
      <div class="table-responsive-md">
        <table class="table w-100 p-2">
          <tr>
            <td class="w-60 align-middle">&bull; <label for="fdest0">'.$admin[1190].'</label> :</td>
            <td class="w-40">
              <input class="form-control texte" id="fdest0" name="fdest0" type="text" value="'.$fdest[0].'" />
            </td>
          </tr>
          <tr>
            <td class="pt-4 text-right">&bull; <label for="chkcc">'.$admin[459].'</label></td>
            <td class="text-left pr-2">
              <input class="mt-3" type="checkbox" id="chkcc" name="chkcc"'.Checked($chkcc).' />
              <script>
                $(document).ready(function(){
                  $(\'.repwebm\').popover({
                    title:"'.mb_substr($admin[459], 0, -2).'",
                    content:"'.$help[26].'",
                    trigger:"hover",
                    placement:"auto",
                    html:true
                  });
                });
              </script>
              <div class="overpop pl-3 mt-n4">&nbsp;
                <a class="repwebm" title="'.$admin[1634].'">
                  <i class="far fa-comment-dots"></i>
                </a>
              </div>
            </td>
          </tr>
        </table>
      </div>
      <hr />';

	// mail expéditeur ----------------------------------------
	echo '
  <p style="text-align:center;"><strong>'.$admin[1191].' :</strong></p>
  <div class="table-responsive-md">
    <table class="table w-100 p-2">
      <tr>
        <td class="w-20 p-0 text-left align-middle">&bull; '.$admin[1194].' '.$admin[1195].'</td>
        <td class="w-40 p-0 text-left align-middle">
          <input type="radio" name="chmail1" value="fix" checked="checked" />&nbsp;'.$admin[1188].' (*)
        </td>
        <td class="w-40 p-0 pr-2 text-right">';
    if (!empty($lang[1])) {
		echo '
          <div class="d-inline-block">'.$flag1.'</div>';
	}
    echo '
          <div class="d-inline-block">
            <input class="form-control texte" name="fmail1" type="text" size="24" value="'.htmlentities($fmail[1], ENT_QUOTES, $charset).'" />
          </div>';
	if (!empty($lang[1])) {
		echo '
          <div>
            <div class="d-inline-block">'.$flag2.'</div>
            <div class="d-inline-block">
              <input class="form-control texte" name="fmail11" type="text" size="24" value="'.htmlentities($fmail[11], ENT_QUOTES, $charset).'" />
            </div>
          </div>';
	}
    echo '
        </td>
      </tr>
      <tr>
        <td class="w-20 text-left p-0 text-left align-middle">&bull; '.$admin[1194].' '.$admin[1196].'</td>
        <td class="w-40 text-left p-0 text-left align-middle">
          <input type="radio" name="chmail2" value="fix" checked="checked" />&nbsp;'.$admin[1188].' (*)
        </td>
        <td class="w-40 text-right p-0 pr-2 text-right">';
    if (!empty($lang[1])) {
		echo '
          <div class="d-inline-block">'.$flag1.'</div>';
	}
    echo '
          <div class="d-inline-block">
            <input class="form-control texte" name="fmail2" type="text" size="24" value="'.htmlentities($fmail[2], ENT_QUOTES, $charset).'" />
          </div>';
	if (!empty($lang[1])) {
		echo '
          <div>
            <div class="d-inline-block">'.$flag2.'</div>
            <div class="d-inline-block">
              <input class="form-control texte" name="fmail12" type="text" size="24" value="'.htmlentities($fmail[12], ENT_QUOTES, $charset).'" />
            </div>
          </div>';
	}
    echo '
        </td>
      </tr>
    </table>
  </div>';

	// zones texte 1 à 3 --------------------------------------
	echo '
  <hr />
  <p style="text-align:center"><strong>'.$admin[1192].mb_strtolower($admin[1017]).'</strong></p>
  <div class="table-responsive-md">
    <table class="table w-100 p-2">
      <tr>
        <td class="w-25 p-0 text-left align-middle">&bull; '.$admin[1197].'</td>
        <td class="w-35 p-0 pt-1 text-left align-middle">
          <input type="radio" id="cht11" name="chtxt1" value=""'.Checked($chtxt[1] == '').' />
            <span class="pr-3"><label for="cht11">'.$admin[1604].'</label></span>
          <input type="radio" id="cht12" name="chtxt1" value="on"'.Checked($chtxt[1] == 'on').' />
            <span class="pr-3"><label for="cht12">'.$admin[1605].'</label></span>
          <input type="radio" id="cht13" name="chtxt1" value="fix"'.Checked($chtxt[1] == 'fix').' />
            <span class="pr-3"><label for="cht13">'.$admin[1188].'</label></span>
        </td>
        <td class="w-40 p-0 pr-2 text-right">';
    if (!empty($lang[1])) {
		echo '
          <div class="d-inline-block">'.$flag1.'</div>';
	}
    echo '
          <div class="d-inline-block">
            <input class="form-control texte" name="ftxt1" type="text" size="24" value="'.htmlentities($ftxt[1], ENT_QUOTES, $charset).'" />
          </div>';
	if (!empty($lang[1])) {
		echo '
          <div>
            <div class="d-inline-block">'.$flag2.'</div>
            <div class="d-inline-block">
              <input class="form-control texte" name="ftxt11" type="text" size="24" value="'.htmlentities($ftxt[11], ENT_QUOTES, $charset).'" />
            </div>
          </div>';
	}
    echo '
        </td>
      </tr>
      <tr>
        <td class="w-25 p-0 text-left align-middle">&bull; '.$admin[1198].'</td>
        <td class="w-35 p-0 pt-1 text-left align-middle">
          <input type="radio" id="cht21" name="chtxt2" value=""'.Checked($chtxt[2] == '').' />
            <span class="pr-3"><label for="cht21">'.$admin[1604].'</label></span>
          <input type="radio" id="cht22" name="chtxt2" value="on"'.Checked($chtxt[2] == 'on').' />
            <span class="pr-3"><label for="cht22">'.$admin[1605].'</label></span>
          <input type="radio" id="cht23" name="chtxt2" value="fix"'.Checked($chtxt[2] == 'fix').' />
            <span class="pr-3"><label for="cht23">'.$admin[1188].'</label></span>
        </td>
        <td class="w-40 p-0 pr-2 text-right">';
    if (!empty($lang[1])) {
		echo '
          <div class="d-inline-block">'.$flag1.'</div>';
	}
    echo '
          <div class="d-inline-block">
            <input class="form-control texte" name="ftxt2" type="text" size="24" value="'.htmlentities($ftxt[2], ENT_QUOTES, $charset).'" />
          </div>';
	if (!empty($lang[1])) {
		echo '
          <div>
            <div class="d-inline-block">'.$flag2.'</div>
            <div class="d-inline-block">
              <input class="form-control texte" name="ftxt12" type="text" size="24" value="'.htmlentities($ftxt[12], ENT_QUOTES, $charset).'" />
            </div>
          </div>';
	}
    echo '
        </td>
      </tr>
      <tr>
        <td class="w-25 p-0 text-left align-middle">&bull; '.$admin[1199].'</td>
        <td class="w-35 p-0 pt-1 text-left align-middle">
          <input type="radio" id="cht31" name="chtxt3" value=""'.Checked($chtxt[3] == '').' />
            <span class="pr-3"><label for="cht31">'.$admin[1604].'</label></span>
          <input type="radio" id="cht32" name="chtxt3" value="on"'.Checked($chtxt[3] == 'on').' />
            <span class="pr-3"><label for="cht32">'.$admin[1605].'</label></span>
          <input type="radio" id="cht33" name="chtxt3" value="fix"'.Checked($chtxt[3] == 'fix').' />
            <span class="pr-3"><label for="cht33">'.$admin[1188].'</label></span>
        </td>
        <td class="w-40 p-0 pr-2 text-right">';
    if (!empty($lang[1])) {
		echo '
          <div class="d-inline-block">'.$flag1.'</div>';
	}
    echo '
          <div class="d-inline-block">
            <input class="form-control texte" name="ftxt3" type="text" size="24" value="'.htmlentities($ftxt[3], ENT_QUOTES, $charset).'" />
          </div>';
	if (!empty($lang[1])) {
		echo '
          <div>
            <div class="d-inline-block">'.$flag2.'</div>
            <div class="d-inline-block">
              <input class="form-control texte" name="ftxt13" type="text" size="24" value="'.htmlentities($ftxt[13], ENT_QUOTES, $charset).'" />
            </div>
          </div>';
	}
    echo '
        </td>
      </tr>
    </table>
  </div>';

	// zone select --------------------------------------------
    echo '
  <div class="table-responsive-md">
    <table class="table w-100 p-2">
      <tr>
        <td class="w-25 p-0 text-left align-middle">&bull; '.$admin[1193].'</td>
        <td class="w-35 p-0 pt-1 text-left align-middle">
          <input type="radio" id="chs01" name="chsel0" value=""'.Checked($chsel[0] == '').' />
            <span class="pr-3"><label for="chs01">'.$admin[1604].'</label></span>
          <input type="radio" id="chs02" name="chsel0" value="on"'.Checked($chsel[0] == 'on').' />
            <span class="pr-3"><label for="chs02">'.$admin[1605].'</label></span>
          <input type="radio" id="chs013" name="chsel0" value="fix"'.Checked($chsel[0] == 'fix').' />
            <span class="pr-3"><label for="chs03">'.$admin[1188].'</label></span>
        </td>
        <td class="w-40 p-0 pr-2 text-right">';
    if (!empty($lang[1])) {
		echo '
          <div class="d-inline-block">'.$flag1.'</div>';
	}
    echo '
          <div class="d-inline-block">
            <input class="form-control texte" name="fsel0" type="text" size="22" value="'.htmlentities($fsel[0], ENT_QUOTES, $charset).'" />
          </div>
          <div class="d-inline-block"><i class="fas fa-caret-square-down text-secondary ml-1" style="font-size: 1.4rem;"></i></div>
        </td>
      </tr>';
	for ($s = 1; $s <= 4; $s++) {
		echo '
      <tr>
        <td class="w-25">&nbsp;</td>
        <td class="w-35">&nbsp;</td>
        <td class="w-40 p-0 pr-2">
          <input class="form-control texte float-right" name="fsel'.$s.'" type="text"  style="width:180px;" 
            value="'.htmlentities($fsel[$s], ENT_QUOTES, $charset).'" />
        </td>
      </tr>';
	}
	if (!empty($lang[1])) {
		echo '
      <tr>
        <td class="w-25">&nbsp;</td>
        <td class="w-35">&nbsp;</td>
        <td class="w-40 p-0 pr-2 text-right">
          <div class="d-inline-block">'.$flag2.'</div>
          <div class="d-inline-block">
            <input class="form-control texte" name="fsel10" type="text" size="22" value="'.htmlentities($fsel[10], ENT_QUOTES, $charset).'" />
          </div>
          <div class="d-inline-block"><i class="fas fa-caret-square-down text-secondary ml-1" style="font-size: 1.4rem;"></i></div>
        </td>
      </tr>';
		for ($s = 11; $s <= 14; $s++) {
			echo '
      <tr>
        <td class="w-25">&nbsp;</td>
        <td class="w-35">&nbsp;</td>
        <td class="w-40 p-0 pr-2 text-right">
          <input class="form-control texte float-right" name="fsel'.$s.'" type="text" style="width:180px;" 
            value="'.htmlentities($fsel[$s], ENT_QUOTES, $charset).'" />
        </td
      </tr>';
		}
	}
    echo '
    </table>
  </div>';

	// zone boutons radio -------------------------------------
    echo '
  <div class="table-responsive-md">
    <table class="table w-100 p-2">
      <tr>
        <td class="w-25 p-0 text-left align-middle">&bull; '.$admin[1203].'</td>
        <td class="w-35 p-0 pt-1 text-left align-middle">
          <input type="radio" id="chr01" name="chrad0" value=""'.Checked($chrad[0] == '').' />
            <span class="pr-3"><label for="chr01">'.$admin[1604].'</label></span>
          <input type="radio" id="chr02" name="chrad0" value="on"'.Checked($chrad[0] == 'on').' />
            <span class="pr-3"><label for="chr02">'.$admin[1605].'</label></span>
          <input type="radio" id="chr013" name="chrad0" value="fix"'.Checked($chrad[0] == 'fix').' />
            <span class="pr-3"><label for="chr03">'.$admin[1188].'</label></span>
        </td>
        <td class="w-40 p-0 pr-2 text-right">';
    if (!empty($lang[1])) {
		echo '
          <div class="d-inline-block">'.$flag1.'</div>';
	}
    echo '
          <div class="d-inline-block">
            <input class="form-control texte" name="frad0" type="text" size="24" value="'.htmlentities($frad[0], ENT_QUOTES, $charset).'" />
          </div>
          <div class="d-inline-block"><i class="fas fa-caret-square-down text-transparent ml-1" style="font-size: 1.4rem;"></i></div>
        </td>
      </tr>';
	for ($b = 1; $b <= 4; $b++) {
		echo '
      <tr>
        <td class="w-25">&nbsp;</td>
        <td class="w-35">&nbsp;</td>
        <td class="w-40 p-0 pr-2 text-right">
          <div class="d-inline-block"><i class="far fa-dot-circle mr-1" style="font-weight: 900;"></i></div>
          <div class="d-inline-block">
            <input class="form-control texte" name="frad'.$b.'" type="text" size="24" value="'.htmlentities($frad[$b], ENT_QUOTES, $charset).'" />
          </div>
        </td>
      </tr>';
	}
	if (!empty($lang[1])) {
		echo '
      <tr>
        <td class="w-25">&nbsp;</td>
        <td class="w-35">&nbsp;</td>
        <td class="w-40 p-0 pr-2 text-right">
          <div class="d-inline-block">'.$flag2.'</div>
          <div class="d-inline-block">
            <input class="form-control texte" name="frad10" type="text" size="24" value="'.htmlentities($frad[10], ENT_QUOTES, $charset).'" />
          </div>
          <div class="d-inline-block"><i class="fas fa-caret-square-down text-transparent ml-1" style="font-size: 1.4rem;"></i></div>
        </td>
      </tr>';
		for ($b = 11; $b <= 14; $b++) {
			echo '
      <tr>
        <td class="w-25">&nbsp;</td>
        <td class="w-35">&nbsp;</td>
        <td class="w-40 p-0 pr-2 text-right">
          <div class="d-inline-block"><i class="far fa-dot-circle mr-1" style="font-weight: 900;"></i></div>
          <div class="d-inline-block">
            <input class="form-control texte" name="frad'.$b.'" type="text" size="24" value="'.htmlentities($frad[$b], ENT_QUOTES, $charset).'" />
          </div>
        </td
      </tr>';
		}
	}
    echo '
    </table>
  </div>';

	// zone cases à cocher ------------------------------------
    echo '
  <div class="table-responsive-md">
    <table class="table w-100 p-2">
      <tr>
        <td class="w-25 p-0 text-left align-middle">&bull; '.$admin[1204].'</td>
        <td class="w-35 p-0 pt-1 text-left align-middle">
          <input type="radio" id="chchk1" name="chchk0" value=""'.Checked($chchk[0] == '').' />
            <span class="pr-3"><label for="chchk1">'.$admin[1604].'</label></span>
          <input type="radio" id="chchk2" name="chchk0" value="on"'.Checked($chchk[0] == 'on').' />
            <span class="pr-3"><label for="chchk2">'.$admin[1605].'</label></span>
          <input type="radio" id="chchk3" name="chchk0" value="fix"'.Checked($chchk[0] == 'fix').' />
            <span class="pr-3"><label for="chchk3">'.$admin[1188].'</label></span>
        </td>
        <td class="w-40 p-0 pr-2 text-right">';
    if (!empty($lang[1])) {
		echo '
          <div class="d-inline-block">'.$flag1.'</div>';
	}
    echo '
          <div class="d-inline-block">
            <input class="form-control texte" name="fchk0" type="text" size="24" value="'.htmlentities($fchk[0], ENT_QUOTES, $charset).'" />
          </div>
          <div class="d-inline-block"><i class="fas fa-caret-square-down text-transparent ml-1" style="font-size: 1.4rem;"></i></div>
        </td>
      </tr>';
	for ($c = 1; $c <= 4; $c++) {
		echo '
      <tr>
        <td class="w-25">&nbsp;</td>
        <td class="w-35">&nbsp;</td>
        <td class="w-40 p-0 pr-2 text-right">
          <div class="d-inline-block"><i class="fas fa-check-square mr-1" style="font-weight: 900;"></i></div>
          <div class="d-inline-block">
            <input class="form-control texte" name="fchk'.$c.'" type="text" size="24" value="'.htmlentities($fchk[$c], ENT_QUOTES, $charset).'" />
          </div>
        </td>
      </tr>';
	}
	if (!empty($lang[1])) {
		echo '
      <tr>
        <td class="w-25">&nbsp;</td>
        <td class="w-35">&nbsp;</td>
        <td class="w-40 p-0 pr-2 text-right">
          <div class="d-inline-block">'.$flag2.'</div>
          <div class="d-inline-block">
            <input class="form-control texte" name="fchk10" type="text" size="24" value="'.htmlentities($fchk[10], ENT_QUOTES, $charset).'" />
          </div>
          <div class="d-inline-block"><i class="fas fa-check-square text-transparent ml-1" style="font-size: 1.4rem;"></i></div>
        </td>
      </tr>';
		for ($c = 11; $c <= 14; $c++) {
			echo '
      <tr>
        <td class="w-25">&nbsp;</td>
        <td class="w-35">&nbsp;</td>
        <td class="w-40 p-0 pr-2 text-right">
          <div class="d-inline-block"><i class="fas fa-check-square mr-1" style="font-weight: 900;"></i></div>
          <div class="d-inline-block">
            <input class="form-control texte" name="fchk'.$c.'" type="text" size="24" value="'.htmlentities($fchk[$c], ENT_QUOTES, $charset).'" />
          </div>
        </td
      </tr>';
		}
	}
    echo '
    </table>
  </div>';

	// zones texte 4 à 6 --------------------------------------
    echo '
  <div class="table-responsive-md">
    <table class="table w-100 p-2">
      <tr>
        <td class="w-25 p-0 text-left align-middle">&bull; '.$admin[1200].'</td>
        <td class="w-35 p-0 pt-1 text-left align-middle">
          <input type="radio" id="cht41" name="chtxt4" value=""'.Checked($chtxt[4] == '').' />
            <span class="pr-3"><label for="cht41">'.$admin[1604].'</label></span>
          <input type="radio" id="cht42" name="chtxt4" value="on"'.Checked($chtxt[4] == 'on').' />
            <span class="pr-3"><label for="cht42">'.$admin[1605].'</label></span>
          <input type="radio" id="cht43" name="chtxt4" value="fix"'.Checked($chtxt[4] == 'fix').' />
            <span class="pr-3"><label for="cht43">'.$admin[1188].'</label></span>
        </td>
        <td class="w-40 p-0 pr-2 text-right">';
    if (!empty($lang[1])) {
		echo '
          <div class="d-inline-block">'.$flag1.'</div>';
	}
    echo '
          <div class="d-inline-block">
            <input class="form-control texte" name="ftxt4" type="text" size="24" value="'.htmlentities($ftxt[4], ENT_QUOTES, $charset).'" />
          </div>';
	if (!empty($lang[1])) {
		echo '
          <div>
            <div class="d-inline-block">'.$flag2.'</div>
            <div class="d-inline-block">
              <input class="form-control texte" name="ftxt14" type="text" size="24" value="'.htmlentities($ftxt[14], ENT_QUOTES, $charset).'" />
            </div>
          </div>';
	}
    echo '
        </td>
      </tr>
      <tr>
        <td class="w-25 p-0 text-left align-middle">&bull; '.$admin[1201].'</td>
        <td class="w-35 p-0 pt-1 text-left align-middle">
          <input type="radio" id="cht51" name="chtxt5" value=""'.Checked($chtxt[5] == '').' />
            <span class="pr-3"><label for="cht51">'.$admin[1604].'</label></span>
          <input type="radio" id="cht52" name="chtxt5" value="on"'.Checked($chtxt[5] == 'on').' />
            <span class="pr-3"><label for="cht52">'.$admin[1605].'</label></span>
          <input type="radio" id="cht53" name="chtxt5" value="fix"'.Checked($chtxt[5] == 'fix').' />
            <span class="pr-3"><label for="cht53">'.$admin[1188].'</label></span>
        </td>
        <td class="w-40 p-0 pr-2 text-right">';
    if (!empty($lang[1])) {
		echo '
          <div class="d-inline-block">'.$flag1.'</div>';
	}
    echo '
          <div class="d-inline-block">
            <input class="form-control texte" name="ftxt5" type="text" size="24" value="'.htmlentities($ftxt[5], ENT_QUOTES, $charset).'" />
          </div>';
	if (!empty($lang[1])) {
		echo '
          <div>
            <div class="d-inline-block">'.$flag2.'</div>
            <div class="d-inline-block">
              <input class="form-control texte" name="ftxt15" type="text" size="24" value="'.htmlentities($ftxt[15], ENT_QUOTES, $charset).'" />
            </div>
          </div>';
	}
    echo '
        </td>
      </tr>
      <tr>
        <td class="w-25 p-0 text-left align-middle">&bull; '.$admin[1202].'</td>
        <td class="w-35 p-0 pt-1 text-left align-middle">
          <input type="radio" id="cht61" name="chtxt6" value=""'.Checked($chtxt[6] == '').' />
            <span class="pr-3"><label for="cht61">'.$admin[1604].'</label></span>
          <input type="radio" id="cht62" name="chtxt6" value="on"'.Checked($chtxt[6] == 'on').' />
            <span class="pr-3"><label for="cht62">'.$admin[1605].'</label></span>
          <input type="radio" id="cht63" name="chtxt6" value="fix"'.Checked($chtxt[6] == 'fix').' />
            <span class="pr-3"><label for="cht63">'.$admin[1188].'</label></span>
        </td>
        <td class="w-40 p-0 pr-2 text-right">';
    if (!empty($lang[1])) {
		echo '
          <div class="d-inline-block">'.$flag1.'</div>';
	}
    echo '
          <div class="d-inline-block">
            <input class="form-control texte" name="ftxt6" type="text" size="24" value="'.htmlentities($ftxt[6], ENT_QUOTES, $charset).'" />
          </div>';
	if (!empty($lang[1])) {
		echo '
          <div>
            <div class="d-inline-block">'.$flag2.'</div>
            <div class="d-inline-block">
              <input class="form-control texte" name="ftxt16" type="text" size="24" value="'.htmlentities($ftxt[16], ENT_QUOTES, $charset).'" />
            </div>
          </div>';
	}
    echo '
        </td>
      </tr>
      <tr>
        <td class="w-25 p-0 text-left align-middle">&bull; '.$admin[1194].' '.$admin[1017].'</td>
        <td class="w-35 p-0 pt-1 text-left align-middle">
          <input type="radio" name="chmes1" value="fix" checked="checked" />&nbsp;'.$admin[1188].' (*)
        </td>
        <td class="w-40 text-right p-0 pr-2 text-right">';
    if (!empty($lang[1])) {
		echo '
          <div class="d-inline-block">'.$flag1.'</div>';
	}
    echo '
          <div class="d-inline-block">
            <input class="form-control texte" name="fmes1" type="text" size="24" value="'.htmlentities($fmes[1], ENT_QUOTES, $charset).'" />
          </div>';
	if (!empty($lang[1])) {
		echo '
          <div>
            <div class="d-inline-block">'.$flag2.'</div>
            <div class="d-inline-block">
              <input class="form-control texte" name="fmes11" type="text" size="24" value="'.htmlentities($fmes[11], ENT_QUOTES, $charset).'" />
            </div>
          </div>';
	}
    echo '
        </td>
      </tr>
    </table>
  </div>';

	// zone fichiers joints -----------------------
	echo '
  <div style="text-align:left;width:520px;">
    <div style="width:120px;float:left;position:relative;top:14px;padding-left:12px;">&bull; '.$admin[1205].'</div>
    <div style="width:240px;float:left;position:relative;top:4px;left:28px;padding:0 8px 0 0;text-align:left;">
      <select class="form-control" name="chfic0" style="width:60px;">';
	for ($s = 0; $s < 6; $s++) { // max fichiers
		echo '
        <option value="'.$s.'"'; 
		if ($chfic[0] == $s) { 
			echo ' selected="selected"'; 
		} 
		echo '>'.$s.'</option>';
	}
	echo '
      </select></div>
    <div class="clearfix"></div>
  </div>';

	// Notice -----------------------------
	echo '
    <p style="text-align:left;padding:12px 0;">&nbsp;(*) <span class="notice">'.$admin[1206].'</span></p><hr />';

	// receipt
	echo '
    <div class="row">
      <div class="col-md-6 text-right"><strong>&bull; <label for="ar">'.$admin[1207].'</label> </strong></div>
      <div class="col-md-6 form-check text-left pl-4">
        <input class="form-check-input" type="checkbox" id="ar" name="ar"'.Checked($far).' />
      </div>
    </div>
  </fieldset>';
	
	// ----------- Phone contact - OPTION au dessous de Contact e-Mail - Phone contact below e-Mail contact --------------
	echo '
    <div class="row quest p-0 my-2 w-100 m-auto">
	  <div class="col-md-6">
        '.SubmitButton($admin[857].' '.$admin[1183], 'self.location.href=\'admin.php?lng='.$lng.'&pg=contact&act=1\'', $imggauche, '', 'button').'
      </div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
    </div>
  <fieldset class="m-0"><legend style="text-align:center;"><label for="ftel0">'.mb_strtoupper($admin[1684], $charset).'&nbsp;
    <input id="ftel0" name="ftel0" type="checkbox"';
	if ($ftel[0]) echo ' checked="checked"';
	echo ' /></label></legend>';
	echo '<div style="text-align:center;"><strong><label for="ftelbelow0">'.$admin[1553].'</label></strong>&nbsp;
      <input id="ftelbelow0" name="ftelbelow0" type="checkbox"';
	if ($ftelbelow[0]) echo ' checked="checked"';
	echo ' /></div>';
	echo '
  <div style="text-align:left;width:100%;">
    <p style="text-align:left;">&bull; '.$admin[1208].'</p>
    <div class="text-center">';
	if (!empty($lang[1])) {
		echo '
      <div class="my-2">'.$flag1.'</div>';
	}
	$ftel1 = stripslashes($ftel[1]);
	display_admin_editor('ftel1', '100%', '140px', $ftel1);
	if (!empty($lang[1])) {
		echo '
      <div class="my-2">'.$flag2.'</div>';
		$ftel11 = stripslashes($ftel[11]);
		display_admin_editor('ftel11', '100%', '140px', $ftel11);
	}
	echo '
    </div>
  </div>
  </fieldset>';

	// ----------- Postal contact - OPTION au dessous de Contact e-Mail - Postal contact below e-Mail contact ------
	echo '
    <div class="row quest p-0 my-2 w-100 m-auto">
	  <div class="col-md-6">
        '.SubmitButton($admin[857].' '.$admin[1183], 'self.location.href=\'admin.php?lng='.$lng.'&pg=contact&act=1\'', $imggauche, '', 'button').'
      </div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
    </div>
  <fieldset><legend style="text-align:center;"><label for="flet0">'.mb_strtoupper($admin[1685], $charset).'</label>&nbsp;
    <input class="texte" id="flet0" name="flet0" type="checkbox"';
	if ($flet[0]) echo ' checked="checked"';
	echo ' /></legend>';
	echo '<div style="text-align:center;"><strong><label for="fletbelow0">'.$admin[1553].'</label></strong>&nbsp;
      <input class="texte" id="fletbelow0" name="fletbelow0" type="checkbox"';
	if ($fletbelow[0]) echo ' checked="checked"';
	echo ' /></div>';
	echo '
  <div style="text-align:left;width:100%;">
    <p style="text-align:left;">&bull; '.$admin[1208].'</p>
    <div class="text-center">';
	if (!empty($lang[1])) {
		echo '
      <div class="my-2">'.$flag1.'</div>';
	}
	$flet1 = stripslashes($flet[1]);
	display_admin_editor('flet1', '100%', '140px', $flet1);
	if (!empty($lang[1])) {
		echo '
      <div class="my-2">'.$flag2.'</div>';
	$flet11 = stripslashes($flet[11]);
	display_admin_editor('flet11', '100%', '140px', $flet11);
	}
	echo '
    </div>
  </div>
  </fieldset>
    <div class="row quest p-0 mt-2 w-100 m-auto">
	  <div class="col-md-6">
        '.SubmitButton($admin[857].' '.$admin[1183], 'self.location.href=\'admin.php?lng='.$lng.'&pg=contact&act=1\'', $imggauche, '', 'button').'
      </div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
    </div>
  </div>
</form>';
}

btable();
include CHEMIN.'admin/bpage.inc';
?>