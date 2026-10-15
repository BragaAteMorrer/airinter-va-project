<?php
/*******************************************************************************
 *   Admin Configuration look
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
 *  v6.00.24 (August 12, 2026) : Correction Stylepage, Tohelp modify 
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'config2.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[1] == '') {
        $nomzone = $admin[45];
        include 'access.inc';
        exit();
    }
}

include CONFIG;
$selskin = is_file(CHEMIN.'skins/'.$selskin.'/confskin.inc') ? $selskin : 'no_skin';
$meskin = CHEMIN.'skins/'.$selskin.'/';
if (is_file ($meskin.'confskin.inc')) {
    include $meskin.'confskin.inc';
} else {
    die('Skin not found : '.$meskin);
}

$tfonts = '';
$afonts = array();

if (is_file($meskin.'font/fontstyle.css')) {
    $tfonts = file_get_contents($meskin.'font/fontstyle.css');
    $sf = file($meskin.'font/fontstyle.css');
    foreach($sf as $line) {
        $n = strpos($line, 'font-family');
        if ($n !== false) {
            $line1 = substr($line, $n + 11);
            $n = strpos($line1, "'");
            $n = $n !== false ? $n : strpos($line1, '"');
            $p = strrpos($line1, "'");
            $p = $p !== false ? $p : strrpos($line1, '"');
            $r = strlen($line1);
            if ($n !== false && $p !== false) {
                $ft = trim(substr($line1, $n + 1, $r - ($n + ($r - $p)) - 1));
                if ($ft) $afonts[] = $ft.', sans-serif';
            }
        }
    }
}

$mycss = import('mycss');
$regit = import('regit');
$incontrolskin = import('incontrolskin');
if(is_file(CONTROLSKIN) && $incontrolskin == 1)
      		@unlink(CONTROLSKIN);
function UrlBKimg($nameimg) {
    $imgcss = explode('|', $nameimg);
    $url    = substr($imgcss[0], 0, 1) == 'I' ? '../../' : '';
    return 'url('.$url.'img/'.$imgcss[1].')';
}

function hex2rgba($color, $opac='0.0') {
    $opac = preg_replace("/[^0-9.]/", '', $opac);
    if (substr($color, 0, 1) != '#' || empty($opac)) return $color;
    $color = preg_replace("/[^0-9A-Fa-f]/", '', $color);
    $argb  = array();
    if (strlen($color) == 6) {
        $colorVal  = hexdec($color);
        $argb['R'] = 0xFF & ($colorVal >> 0x10);
        $argb['G'] = 0xFF & ($colorVal >> 0x8);
        $argb['B'] = 0xFF & $colorVal;
    } elseif (strlen($color) == 3) {
        $argb['R'] = hexdec(str_repeat(substr($color, 0, 1), 2));
        $argb['G'] = hexdec(str_repeat(substr($color, 1, 1), 2));
        $argb['B'] = hexdec(str_repeat(substr($color, 2, 1), 2));
    } else {
        return false;
    }
    return 'rgba('.implode(',', $argb).', '.$opac.')';
}

function getBorder ($width='0', $style='solid', $color='#000000', $force=false) {
    $awidth = explode(' ', $width);
    $astyle = explode(' ', $style);
    $acolor = explode(' ', $color);
    $sforce = $force ? ' !important' : '';
    $count  = count($awidth) + count($astyle) + count($acolor);
    if ($count == 3) {
        $width = in_array($width, array('0', '0px')) || in_array($style, array('hidden', 'none')) ? '0px' : $width;
        $color = in_array($color, array('inherit', 'transparent')) ? '#000000' : $color;
        return $width == '0px' || $width == '0' ? 'border: 0'.$sforce.';' : 'border: '.$width.' '.$style.' '.$color.$sforce.';';
    }
    return 'border-width: '.$width.$sforce.';'."\n".'    border-style: '.$style.$sforce.';'."\n".'    border-color: '.$color.$sforce.';';
}
$messreg  = '';
$messreg2 = '';
if ($regit == 1) {
    $regpart = import('regpart');
	$messreg = " OK";

	if ($regpart == '1') {
        $body[5] = import('body5', 'POST', true, '1%'); // Position en bas de page du bouton flottant
        $body[6] = import('body6', 'POST', true, '0'); // Décalage avant affichage du bouton flottant
        $body[7] = import('body7', '', true); // Bouton flottant ON/OFF
        $opacity[20] = import('opacity20', 'POST', true, '0.7'); // Opacité du bouton flottant
        
        $page[8]  = import('page8', '', true); // Affichage Temps de chargement de page ON / OFF
        $page[22] = import('page22', 'POST', true); // utilisation titres longs
        $page[54] = '';
 /*
        foreach ($templates as $key=>$tmpl) 
		{
			$page[54] .= ','.import('page54'.$key, 'POST', true, ''); // N° ligne TOP à fixer en haut de page
		}
*/
		$nbrtemplateabsent = 0;
		for ($ij=0; $ij < count($templates); $ij++)
		{
			if (!isset($templates[$ij])) 
				$nbrtemplateabsent++;
		}	
		for ($itemplates = 0; $itemplates < count($templates)+$nbrtemplateabsent; $itemplates++)
		{
            if (isset($templates[$itemplates]))
			{
			    $key = $itemplates;
				$page[54] .= ','.import('page54'.$key, 'POST', true, ''); // N° ligne TOP à fixer en haut de page
			}
			else
				$page[54] .= ',';
		}
        $page[54] = substr($page[54], 1);
        $page[62] = '';
/*
        foreach ($templates as $key=>$tmpl) 
            $page[62] .= ','.import('page62'.$key, 'POST', true, '0'); // Décalage du centre sous la ligne TOP
*/
		for ($itemplates = 0; $itemplates < count($templates)+$nbrtemplateabsent; $itemplates++)
		{
            if (isset($templates[$itemplates]))
			{
			    $key = $itemplates;
				$page[62] .= ','.import('page62'.$key, 'POST', true, '0'); // Décalage du centre sous la ligne TOP
			}
			else
				$page[62] .= ',';
		} 
        $page[62] = substr($page[62], 1);
        $body[16] = import('body16', '', true); // Ancrage automatique zone 2
    }
    
	if ($regpart == '2') {
		$body[0] = import('body0', 'POST', true, 'transparent'); // Arrière-plan du site
		$body[1] = import('body1', '', true); // Image de fond du site
		$body[2] = import('body2', '', true); // Image de fond fixe / mouvante
        $body[3] = import('body3', 'POST', true, 'Non'); // Répétition image de fond
        $body[4] = import('body4', 'POST', true, 'TOP'); // Position horizontale
		$body[8] = import('body8', '', true); // Image de fond bas de page
        $body[9] = import('body9', 'POST', true, 'Non'); // Répétition image bas de page
        $body[10] = import('body10', 'POST', true, '1'); // N° de la colonne gauche
        $body[11] = import('body11', 'POST', true, '2'); // N° de la colonne centrale
        $body[12] = import('body12', 'POST', true, '3'); // N° de la colonne droite
        $body[13] = import('body13', 'POST', true, '1'); // N° de la colonne gauche
        $body[15] = import('body15', 'POST', true, '3'); // N° de la colonne droite

        $citation[0] = import('citation0', 'POST', true, 'Arial, Helvetica, sans-serif'); // Police des citations
		$citation[1] = import('citation1', 'POST', true, '16px'); // Taille de la police des citations
		$citation[2] = import('citation2', 'POST', true, 'inherit'); // Couleur de la police des citations
		$citation[3] = import('citation3', 'POST', true, ''); // Style de la police des citations
		$citation[4] = import('citation4', 'POST', true, 'inherit'); // Couleur des adresses actives
		$citation[5] = import('citation5', 'POST', true, 'inherit'); // Couleur de la page courante
		$citation[6] = import('citation6', 'POST', true, 'inherit'); // Police de la boîte impression
		$citation[7] = import('citation7', 'POST', true, ''); // Style de la police boîte impression
		$citation[8] = import('citation8', 'POST', true, '16px'); // Taille de la police boîte impression
		$citation[9] = import('citation9', 'POST', true, 'inherit'); // Couleur de la police boîte impression
        $citation[10] = import('citation10', 'POST', true, 'transparent'); // Arrière-plan des citations
        $citation[11] = import('citation11', '', true); // Image de fond des citations
        $citation[12] = import('citation12', 'POST', true, 'inherit'); // Couleur des bordures des citations
        $citation[13] = import('citation13', 'POST', true, '0px'); // Taille des bordures des citations
		$citation[14] = import('citation14', 'POST', true, '0px'); // Taille coins arrondis des citations
		$citation[15] = import('citation15', 'POST', true, 'solid'); // Style des bordures des citations
		$citation[16] = import('citation16', 'POST', true, '0'); // Marges externes des citations
		$citation[17] = import('citation17', 'POST', true, '0'); // Marges internes des citations

		$lien[0] = import('lien0', 'POST', true, 'inherit'); // Lien OFF des boites centrales
		$lien[1] = import('lien1', 'POST', true, 'inherit'); // Lien ON des boites centrales
		$lien[2] = import('lien2', 'POST', true, 'inherit'); // Lien OFF des boites latérales
		$lien[3] = import('lien3', 'POST', true, 'inherit'); // Lien ON des boites latérales
		$lien[4] = import('lien4', 'POST', true, 'inherit'); // Liens OFF de la barre de menu haute
		$lien[5] = import('lien5', 'POST', true, 'inherit'); // Liens ON de la barre de menu haute
		$lien[6] = import('lien6', 'POST', true, 'transparent'); // Fond liens ON de la barre de menu haute
		$lien[7] = import('lien7', 'POST', true, 'transparent'); // Fond liens OFF de la barre de menu haute
		$lien[8] = import('lien8', 'POST', true, 'transparent'); // Fond lien actif de la barre de menu haute
		$lien[9] = import('lien9', 'POST', true, 'inherit'); // Liens actif de la barre de menu haute
		$lien[10] = import('lien10', 'POST', true, 'inherit'); // Couleur de la bordure des onglets de la barre de menu
		$lien[11] = import('lien11', 'POST', true, '0px'); // Taille de la bordure des onglets de la barre de menu
		$lien[12] = import('lien12', 'POST', true, 'solid'); // Style de la bordure des onglets de la barre de menu
		$lien[13] = import('lien13', 'POST', true, '0px'); // Taille des  coins arrondis des onglets de la barre de menu
		$lien[14] = import('lien14', 'POST', true, '0'); // Marges internes des onglets de la barre de menu
		$lien[15] = import('lien15', 'POST', true, '0'); // Marges externes des onglets de la barre de menu
		$lien[16] = import('lien16', 'POST', true, 'transparent'); // Liens OFF du menu navigation latérale
		$lien[17] = import('lien17', 'POST', true, 'transparent'); // Liens ON du menu navigation latérale
		$lien[18] = import('lien18', 'POST', true, 'transparent'); // Fond liens ON du menu navigation latérale
		$lien[19] = import('lien19', 'POST', true, 'transparent'); // Fond des panneaux de navigation latérale
		$lien[20] = import('lien20', 'POST', true, 'transparent'); // Couleur de la bordure gauche des panneaux
		$lien[21] = import('lien21', 'POST', true, ''); // Taille de la bordure gauche des panneaux
		$lien[22] = import('lien22', 'POST', true, 'transparent'); // Liens OFF du panneau menu
		$lien[23] = import('lien23', 'POST', true, 'transparent'); // Liens ON du panneau menu
		$lien[24] = import('lien24', 'POST', true, 'transparent'); // Fond liens ON du panneau menu
		$lien[25] = import('lien25', 'POST', true, 'transparent'); // Fond du menu navigation latérale
        $lien[26] = import('lien26', 'POST', true, 'inherit'); // Couleur des bordures du menu latéral
        $lien[27] = import('lien27', 'POST', true, '0px'); // Taille des bordures du menu latéral
		$lien[28] = import('lien28', 'POST', true, '0px'); // Taille coins arrondis du menu latéral
		$lien[29] = import('lien29', 'POST', true, 'solid'); // Style des bordures du menu latéral
		$lien[30] = import('lien30', 'POST', true, 'transparent'); // Fond liens OFF menu navigation latérale
		$lien[31] = import('lien31', 'POST', true, 'transparent'); // Fond liens ON options panneau menu
        $lien[32] = import('lien32', 'POST', true, 'inherit'); // Couleur des bordures options panneau menu
        $lien[33] = import('lien33', 'POST', true, '0px'); // Taille des bordures options panneau menu
		$lien[34] = import('lien34', 'POST', true, '0px'); // Taille coins arrondis options panneau menu
		$lien[35] = import('lien35', 'POST', true, 'solid'); // Style des bordures options panneau menu
        $lien[36] = import('lien36', 'POST', true, 'inherit'); // Couleur lien fermeture panneau menu

		$page[1] = import('page1', 'POST', true, 'Arial, Helvetica, sans-serif'); // Police des boites centrales
		$page[2] = import('page2', 'POST', true, '16px'); // Taille des textes des boites centrales
        $page[4] = import('page4', 'POST', true, 'Pas d\'image'); // Logo du site
		$page[5] = import('page5', 'POST', true, 'transparent'); // Arrière-plan du bandeau TOP
		$page[6] = import('page6', 'POST', true, 'normal'); // Hauteur des lignes boites centrales
		$page[7] = import('page7', '', true); // Flèches pour la navigation parallaxe ON / OFF
		$page[11] = import('page11', 'POST', true, 'normal'); // Hauteur des lignes boites latérales
		$page[16] = import('page16', 'POST', true, 'Arial, Helvetica, sans-serif'); // Police des boites latérales
		$page[17] = import('page17', 'POST', true, '16px'); // Taille des textes des boites latérales
		$page[18] = import('page18', 'POST', true, 'Arial, Helvetica, sans-serif'); // Police du menu de navigation
		$page[19] = import('page19', 'POST', true, '16px'); // Taille de police du menu de navigation
		$page[21] = import('page21', 'POST', true, 'auto'); // Hauteur du footer
		$page[24] = import('page24', '', true); // Image de fond parallaxe 1
		$page[25] = import('page25', '', true); // Image de fond parallaxe 2
		$page[26] = import('page26', '', true); // Image de fond parallaxe 3
        $page[29] = import('page29', 'POST', true, 'transparent'); // Arrière-plan du bandeau TOP
        $page[31] = import('page31', 'POST', true, 'transparent'); // Arrière-plan du bandeau BOTTOM
        $page[33] = import('page33', '', true); // Image de fond de la barre de menu
        $page[34] = import('page34', 'POST', true, 'transparent'); // Arrière-plan de la barre de menu
        $page[35] = import('page35', 'POST', true, ''); // Style de la police des boîtes latérales
        $page[36] = import('page36', 'POST', true, ''); // Style de la police des boîtes centrales
        $page[37] = import('page37', 'POST', true, ''); // Style de la police du menu icônes
		$page[39] = import('page39', 'POST', true, 'Arial, Helvetica, sans-serif'); // Police corps du document
		$page[40] = import('page40', 'POST', true, '16px'); // Taille des textes corps du document
        $page[41] = import('page41', 'POST', true, ''); // Style de la police corps du document
        $page[43] = import('page43', 'POST', true, 'transparent'); // Arrière-plan du header
		$page[45] = import('page45', 'POST', true, 'Arial, Helvetica, sans-serif'); // Police footer
		$page[46] = import('page46', 'POST', true, '16px'); // Taille des textes footer
        $page[47] = import('page47', 'POST', true, ''); // Style de la police footer
        $page[50] = import('page50', 'POST', true, 'no'); // Menu effet de parallaxe
		$page[51] = import('page51', 'POST', true, 'transparent'); // Arrière-plan boutons parallaxe
        $page[52] = import('page52', 'POST', true, '40%'); // Position navigateur parallaxe
		$page[56] = import('page56', 'POST', true, 'transparent'); // Arrière-plan parallaxe 1
		$page[57] = import('page57', 'POST', true, 'transparent'); // Arrière-plan parallaxe 2
		$page[58] = import('page58', 'POST', true, 'transparent'); // Arrière-plan parallaxe 3
		$page[63] = import('page63', '', true); // Largeur des flèches de navigation parallaxe
		$page[64] = import('page64', '', true); // Hauteur des flèches de navigation parallaxe
		$page[65] = import('page65', 'POST', true, 'Arial, Helvetica, sans-serif'); // Police du menu navigation latérale
		$page[66] = import('page66', 'POST', true, ''); // Style de police du menu navigation latérale
		$page[67] = import('page67', 'POST', true, ''); // Taille des liens du menu navigation latérale
		$page[68] = import('page68', 'POST', true, 'Arial, Helvetica, sans-serif'); // Police du panneau menu navigation latérale
		$page[69] = import('page69', 'POST', true, ''); // Style de police du panneau menu navigation latérale
		$page[70] = import('page70', 'POST', true, ''); // Taille des liens du panneau menu navigation latérale

		$bordure[3] = import('bordure3', 'POST', true, 'inherit'); // Couleur des bordures bandeau et pied de page
		$bordure[4] = import('bordure4', 'POST', true, '0px'); // Taille des bordures bandeau et pied de page
		$bordure[5] = import('bordure5', 'POST', true, '0px'); // Taille coins arrondis bandeau et pied de page
		$bordure[7] = import('bordure7', 'POST', true, 'inherit'); // Couleur des bordures titre boîtes centrales
		$bordure[8] = import('bordure8', 'POST', true, '0px'); // Taille des bordures titre boîtes centrales
		$bordure[9] = import('bordure9', 'POST', true, '0px'); // Taille coins arrondis titre boîtes centrales
		$bordure[10] = import('bordure10', 'POST', true, 'inherit'); // Couleur des bordures corps boîtes centrales
		$bordure[11] = import('bordure11', 'POST', true, '0px'); // Taille des bordures corps boîtes centrales
		$bordure[12] = import('bordure12', 'POST', true, '0px'); // Taille coins arrondis corps boîtes centrales
		$bordure[13] = import('bordure13', 'POST', true, 'inherit'); // Couleur des bordures titre boîtes latérales
		$bordure[14] = import('bordure14', 'POST', true, '0px'); // Taille des bordures titre boîtes latérales
		$bordure[15] = import('bordure15', 'POST', true, '0px'); // Taille coins arrondis titre boîtes latérales
		$bordure[16] = import('bordure16', 'POST', true, 'inherit'); // Couleur des bordures corps boîtes latérales
		$bordure[17] = import('bordure17', 'POST', true, '0px'); // Taille des bordures corps boîtes latérales
		$bordure[18] = import('bordure18', 'POST', true, '0px'); // Taille coins arrondis corps boîtes latérales
		$bordure[19] = import('bordure19', 'POST', true, 'inherit'); // Couleur des bordures du bandeau TOP
		$bordure[20] = import('bordure20', 'POST', true, '0px'); // Taille des bordures du bandeau TOP
		$bordure[21] = import('bordure21', 'POST', true, '0px'); // Taille coins arrondis du bandeau TOP
		$bordure[22] = import('bordure22', 'POST', true, 'inherit'); // Couleur des bordures du bandeau BOTTOM
		$bordure[23] = import('bordure23', 'POST', true, '0px'); // Taille des bordures du bandeau BOTTOM
		$bordure[24] = import('bordure24', 'POST', true, '0px'); // Taille coins arrondis du bandeau BOTTOM
        $bordure[26] = import('bordure26', 'POST', true, 'inherit'); // Couleur des bordures de la barre de menu
        $bordure[27] = import('bordure27', 'POST', true, '0px'); // Taille des bordures de la barre de menu
		$bordure[28] = import('bordure28', 'POST', true, '0px'); // Taille coins arrondis de la barre de menu
		$bordure[29] = import('bordure29', 'POST', true, 'solid'); // Style des bordures du bandeau TOP
		$bordure[30] = import('bordure30', 'POST', true, 'solid'); // Style des bordures du bandeau BOTTOM
		$bordure[31] = import('bordure31', 'POST', true, 'solid'); // Style des bordures du pied de page
		$bordure[32] = import('bordure32', 'POST', true, 'solid'); // Style des bordures de la barre de menu
		$bordure[34] = import('bordure34', 'POST', true, 'solid'); // Style des bordures titre boîtes centrales
		$bordure[35] = import('bordure35', 'POST', true, 'solid'); // Style des bordures corps boîtes centrales
		$bordure[36] = import('bordure36', 'POST', true, 'solid'); // Style des bordures titre boîtes latérales
		$bordure[37] = import('bordure37', 'POST', true, 'solid'); // Style des bordures corps boîtes latérales
		$bordure[38] = import('bordure38', 'POST', true, 'solid'); // Style des bordures formulaire
		$bordure[39] = import('bordure39', 'POST', true, 'solid'); // Style des bordures annuaires
		$bordure[40] = import('bordure40', 'POST', true, 'inherit'); // Couleur des bordures du header
		$bordure[41] = import('bordure41', 'POST', true, '0px'); // Taille des bordures du header
		$bordure[42] = import('bordure42', 'POST', true, '0px'); // Taille coins arrondis du header
		$bordure[43] = import('bordure43', 'POST', true, 'solid'); // Style des bordures du header
		$bordure[44] = import('bordure44', 'POST', true, 'inherit'); // Couleur bordures boutons parallaxe

		$barre[0] = import('barre0', 'POST', true, 'inherit'); // Couleur des HR
		$barre[1] = import('barre1', 'POST', true, '1px'); // Taille des HR
		$barre[2] = import('barre2', 'POST', true, 'solid'); // Style des HR
		$barre[3] = import('barre3', 'POST', true, 'inherit'); // Couleur des bordures
		$barre[4] = import('barre4', 'POST', true, '0px'); // Taille des bordures
		$barre[5] = import('barre5', 'POST', true, 'solid'); // Style des bordures

		$titre[0] = import('titre0', 'POST', true, 'inherit'); // Couleur du titre des boites centrales
		$titre[1] = import('titre1', 'POST', true, 'transparent'); // Arrière-plan du titre des boites centrales
		$titre[2] = import('titre2', 'POST', true, 'Arial, Helvetica, sans-serif'); // Police des titres des boites centrales
		$titre[3] = import('titre3', 'POST', true, '18px'); // Taille des titres des boites centrales
		$titre[4] = import('titre4', 'POST', true, 'inherit'); // Couleur du titre des boites latérales
		$titre[5] = import('titre5', 'POST', true, 'transparent'); // Arrière-plan du titre des boites latérales
		$titre[6] = import('titre6', 'POST', true, 'Arial, Helvetica, sans-serif'); // Police des titres des boites latérales
		$titre[7] = import('titre7', 'POST', true, '18px'); // Taille des titres des boites latérales
		$titre[8] = import('titre8', 'POST', true); // Image de fond des titres des boites centrales
		$titre[9] = import('titre9', '', true); // Image de fond des titres des boites latérales
		$titre[10] = import('titre10', 'POST', true, 'inherit'); // Couleur du titre des boites BOTTOM
		$titre[11] = import('titre11', 'POST', true, ''); // Style de police des titres des boites latérales
		$titre[12] = import('titre12', 'POST', true, ''); // Style de police des titres des boites centrales
		$titre[13] = import('titre13', 'POST', true, 'inherit'); // Couleur des textes des boites BOTTOM
		$titre[14] = import('titre14', 'POST', true, 'transparent'); // Arrière-plan de la colonne latérale gauche
		$titre[15] = import('titre15', 'POST', true, 'transparent'); // Arrière-plan du titre des boites BOTTOM
		$titre[16] = import('titre16', 'POST', true, 'inherit'); // Couleur des bordures de la colonne latérale gauche
		$titre[17] = import('titre17', 'POST', true, '0px'); // Taille des bordures de la colonne latérale gauche
		$titre[18] = import('titre18', 'POST', true, 'solid'); // Style des bordures de la colonne latérale gauche
		$titre[19] = import('titre19', 'POST', true, '0px'); // Rayon des coins de la colonne latérale gauche
		$titre[20] = import('titre20', 'POST', true, 'transparent'); // Arrière-plan OFF des boites BOTTOM
		$titre[21] = import('titre21', 'POST', true, 'transparent'); // Arrière-plan de la colonne latérale droite
		$titre[23] = import('titre23', 'POST', true, 'inherit'); // Couleur des bordures de la colonne latérale droite
		$titre[24] = import('titre24', 'POST', true, '0px'); // Taille des bordures de la colonne latérale droite
		$titre[25] = import('titre25', 'POST', true, 'solid'); // Style des bordures de la colonne latérale droite
		$titre[26] = import('titre26', 'POST', true, '0px'); // Rayon des coins de la colonne latérale droite
		$titre[27] = import('titre27', 'POST', true, 'transparent'); // Arrière-plan des colonnes du blog
		$titre[29] = import('titre29', 'POST', true, 'inherit'); // Couleur des bordures des colonnes du blog
		$titre[30] = import('titre30', 'POST', true, '0px'); // Taille des bordures des colonnes du blog
		$titre[31] = import('titre31', 'POST', true, 'solid'); // Style des bordures des colonnes du blog
		$titre[32] = import('titre32', 'POST', true, '0px'); // Rayon des coins des colonnes du blog
		$titre[34] = import('titre34', 'POST', true, 'transparent'); // Arrière-plan de la zone principale
		$titre[36] = import('titre36', 'POST', true, 'inherit'); // Couleur des bordures de la zone principale
		$titre[37] = import('titre37', 'POST', true, '0px'); // Taille des bordures de la zone principale
		$titre[38] = import('titre38', 'POST', true, 'solid'); // Style des bordures de la zone principale
		$titre[39] = import('titre39', 'POST', true, '0px'); // Rayon des coins de la zone principale
		$titre[41] = import('titre41', 'POST', true, 'transparent'); // Arrière-plan de la colonne centrale
		$titre[43] = import('titre43', 'POST', true, 'inherit'); // Couleur des bordures de la colonne centrale
		$titre[44] = import('titre44', 'POST', true, '0px'); // Taille des bordures de la colonne centrale
		$titre[45] = import('titre45', 'POST', true, 'solid'); // Style des bordures de la colonne centrale
		$titre[46] = import('titre46', 'POST', true, '0px'); // Rayon des coins de la colonne centrale
		$titre[47] = import('titre47', 'POST', true, 'Arial, Helvetica, sans-serif'); // Police du titre du panneau menu
		$titre[48] = import('titre48', 'POST', true, ''); // Style de police du titre du panneau menu
		$titre[49] = import('titre49', 'POST', true, ''); // Taille du texte du titre du panneau menu
		$titre[50] = import('titre50', 'POST', true, 'transparent'); // Couleur du titre du panneau menu
		$titre[51] = import('titre51', 'POST', true, 'transparent'); // Couleur de fond du titre du panneau menu
        $titre[52] = import('titre52', 'POST', true, 'inherit'); // Couleur des bordures du titre du panneau menu
        $titre[53] = import('titre53', 'POST', true, '0px'); // Taille des bordures du titre du panneau menu
		$titre[54] = import('titre54', 'POST', true, 'solid'); // Style des bordures du titre du panneau menu
		$titre[55] = import('titre55', 'POST', true, '0px'); // Taille coins arrondis du titre du panneau menu


		$titre[60] = import('titre60', 'POST', true, 'transparent'); // Arrière-plan de la zone au-dessus
		$titre[62] = import('titre62', 'POST', true, 'inherit'); // Couleur des bordures de la zone au-dessus
		$titre[63] = import('titre63', 'POST', true, '0px'); // Taille des bordures de la zone au-dessus
		$titre[64] = import('titre64', 'POST', true, 'solid'); // Style des bordures de la zone au-dessus
		$titre[65] = import('titre65', 'POST', true, '0px'); // Rayon des coins de la zone au-dessus
		$titre[66] = import('titre66', 'POST', true, 'transparent'); // Arrière-plan de la zone édito
		$titre[68] = import('titre68', 'POST', true, 'inherit'); // Couleur des bordures de la zone édito
		$titre[69] = import('titre69', 'POST', true, '0px'); // Taille des bordures de la zone édito
		$titre[70] = import('titre70', 'POST', true, 'solid'); // Style des bordures de la zone édito
		$titre[71] = import('titre71', 'POST', true, '0px'); // Rayon des coins de la zone édito
		$titre[72] = import('titre72', 'POST', true, 'transparent'); // Arrière-plan de la zone au-dessous
		$titre[74] = import('titre74', 'POST', true, 'inherit'); // Couleur des bordures de la zone au-dessous
		$titre[75] = import('titre75', 'POST', true, '0px'); // Taille des bordures de la zone au-dessous
		$titre[76] = import('titre76', 'POST', true, 'solid'); // Style des bordures de la zone au-dessous
		$titre[77] = import('titre77', 'POST', true, '0px'); // Rayon des coins de la zone au-dessous
		$titre[78] = import('titre78', 'POST', true, '100%'); // Largeur TOPBOXES
		$titre[79] = import('titre79', 'POST', true, '100%'); // Largeur MAINCONTENT
		$titre[80] = import('titre80', 'POST', true, '100%'); // Largeur BOTTOMBOXES
		$titre[81] = import('titre81', 'POST', true, '100%'); // Largeur FOOTER
        
		$texte[0] = import('texte0', 'POST', true, 'inherit'); // Couleur des textes des boites centrales
		$texte[1] = import('texte1', 'POST', true, 'transparent'); // Arrière-plan OFF des boites centrales
		$texte[2] = import('texte2', 'POST', true, 'transparent'); // Arrière-plan ON des boites centrales
		$texte[5] = import('texte5', 'POST', true, 'inherit'); // Couleur des textes des boites latérales
		$texte[6] = import('texte6', 'POST', true, 'transparent'); // Arrière-plan OFF des boites latérales
		$texte[7] = import('texte7', 'POST', true, 'transparent'); // Arrière-plan ON des boites latérales
		$texte[8] = import('texte8', 'POST', true, 'inherit'); // Couleur des textes éléments footer
		$texte[9] = import('texte9', 'POST', true, 'inherit'); // Couleur des textes corps du document
		$texte[10] = import('texte10', 'POST', true, 'left'); // Alignement des titres boîtes centrales
		$texte[11] = import('texte11', 'POST', true, 'left'); // Alignement des titres boîtes latérales
		$texte[12] = import('texte12', 'POST', true, 'left'); // Alignement des options de menu déroulant
		$texte[13] = import('texte13', 'POST', true, 'center'); // Alignement des lignes du bas de page

		$forum[0] = import('forum0', 'POST', true, 'transparent'); // Arrière-plan entêtes forum
		$forum[1] = import('forum1', 'POST', true, 'transparent'); // Arrière-plan du sujet
		$forum[2] = import('forum2', 'POST', true, 'transparent'); // Arrière-plan de la réponse
		$forum[11] = import('forum11', 'POST', true, 'inherit'); // Couleur texte dans entêtes
		$forum[12] = import('forum12', 'POST', true, 'Arial, Helvetica, sans-serif'); // Police du texte dans entêtes
		$forum[13] = import('forum13', 'POST', true, ''); // Style du texte dans entêtes
		$forum[14] = import('forum14', 'POST', true, '16px'); // Taille du texte dans entêtes

		$presform[0] = import('presform0', 'POST', true, 'inherit'); // Couleur des textes dans les boites de choix et boutons de commande
		$presform[1] = import('presform1', 'POST', true, 'inherit'); // Couleur des boites de choix
		$presform[2] = import('presform2', 'POST', true, 'inherit'); // Couleur des boutons de commande
		$presform[3] = import('presform3', 'POST', true, 'inherit'); // Couleur des zone de texte
		$presform[4] = import('presform4', 'POST', true, 'Arial, Helvetica, sans-serif'); // Police dans la présentation des formulaires
		$presform[5] = import('presform5', 'POST', true); // Taille de la police dans la présentation des formulaires
		$presform[6] = import('presform6', 'POST', true, 'transparent'); // Arrière-plan des textarea
		$presform[7] = import('presform7', 'POST', true, '1px'); // Taille de la bordure
		$presform[8] = import('presform8', 'POST', true, 'inherit'); // Couleur de la bordure
		$presform[9] = import('presform9', 'POST', true, '0px'); // Taille coins arrondis
		$presform[10] = import('presform10', 'POST', true, ''); // Style de la police de présentation
		$presform[11] = import('presform11', 'POST', true, 'inherit'); // Couleur du texte des boutons de commande
		$presform[12] = import('presform12', 'POST', true, '0'); // Marges externes des boutons de commande
		$presform[13] = import('presform13', 'POST', true, 'inherit'); // Couleur des boutons de commande survolés
		$presform[14] = import('presform14', 'POST', true, 'inherit'); // Couleur du texte des boutons de commande survolés

		$calendar[0]  = import('calendar0', 'POST', true, 'transparent'); // Couleur de fond des jours libre du mois
		$calendar[1]  = import('calendar1', 'POST', true, 'transparent'); // Couleur de fond des jours du mois
		$calendar[2]  = import('calendar2', 'POST', true, 'transparent'); // Couleur de fond de la journée
		$calendar[3]  = import('calendar3', 'POST', true, 'transparent'); // Couleur de fond des dimanches
		$calendar[4]  = import('calendar4', 'POST', true, 'transparent'); // Couleur de fond de la journée si dimanche
		$calendar[5]  = import('calendar5', 'POST', true, 'Arial, Helvetica, sans-serif'); // Police du calendrier
		$calendar[6]  = import('calendar6', 'POST', true, '15px'); // Taille de la police du calendrier
		$calendar[7]  = import('calendar7', 'POST', true, 'inherit'); // Couleurs de la police des chiffres
		$calendar[8]  = import('calendar8', 'POST', true, 'inherit'); // Couleurs de la police des jours de la semaine
		$calendar[9]  = import('calendar9', 'POST', true, '0px'); // Espacement des bordures du calendrier
		$calendar[10] = import('calendar10', 'POST', true, 'inherit'); // Couleur évènement
		$calendar[11] = import('calendar11', 'POST', true, 'inherit'); // Couleur de fond du calendrier/bordure
		$calendar[12] = import('calendar12', 'POST', true, '0px'); // Taille des bordures du calendrier
		$calendar[13] = import('calendar13', 'POST', true, 'inherit'); // Couleur bordure calendrier
		$calendar[14] = import('calendar14', 'POST', true, '0px'); // Taille coins arrondis calendrier
		$calendar[15] = import('calendar15', 'POST', true, 'normal'); // Style de police du calendrier
		$calendar[16] = import('calendar16', 'POST', true, 'inherit'); // Couleur de la police évènement
		$calendar[17] = import('calendar17', 'POST', true, 'transparent'); // Couleur de fond des jours de la semaine
		$calendar[18] = import('calendar18', 'POST', true, 'inherit'); // Couleur de la police de la journée
		$calendar[19] = import('calendar19', 'POST', true, 'inherit'); // Couleur de la police des dimanches
		$calendar[20] = import('calendar20', 'POST', true, 'inherit'); // Couleur de la police de la journée si dimanche
        
        $annuaire[0] = import('annuaire0', 'POST', true, 'transparent'); // Arrière-plan de la catégorie 
        $annuaire[1] = import('annuaire1', 'POST', true, 'Arial, Helvetica, sans-serif'); // Police de la catégorie
        $annuaire[2] = import('annuaire2', 'POST', true, '18px'); // Taille de la police de la catégorie
        $annuaire[3] = import('annuaire3', 'POST', true, 'inherit'); // Couleur de la police de la catégorie
        $annuaire[4] = import('annuaire4', 'POST', true, 'inherit'); // Couleur de bordure de la catégorie
        $annuaire[5] = import('annuaire5', 'POST', true, '0px'); // Taille de bordure de la catégorie
        $annuaire[6] = import('annuaire6', 'POST', true, '0px'); // Taille des coins arrondis de la catégorie
        $annuaire[7] = import('annuaire7', 'POST', true, ''); // Style de police de la catégorie
        $annuaire[8] = import('annuaire8', 'POST', true, 'transparent'); // Arrière-plan sous-catégorie 
        $annuaire[9] = import('annuaire9', 'POST', true, 'transparent'); // Arrière-plan élément
        $annuaire[10] = import('annuaire10', 'POST', true, 'Arial, Helvetica, sans-serif'); // Police de la sous-catégorie
        $annuaire[11] = import('annuaire11', 'POST', true, 'normal'); // Style de police de la sous-catégorie
        $annuaire[12] = import('annuaire12', 'POST', true, '16px'); // Taille de la police de la sous-catégorie
        $annuaire[13] = import('annuaire13', 'POST', true, 'inherit'); // Couleur de la police de la sous-catégorie
        $annuaire[14] = import('annuaire14', 'POST', true, 'Arial, Helvetica, sans-serif'); // Police des éléments
        $annuaire[15] = import('annuaire15', 'POST', true, ''); // Style de police des éléments
        $annuaire[16] = import('annuaire16', 'POST', true, '16px'); // Taille de la police des éléments
        $annuaire[17] = import('annuaire17', 'POST', true, 'inherit'); // Couleur de la police des éléments
        
//		$dropdown[0] = import('dropdown0', 'POST', true, 'auto'); // Largeur option menu déroulant
		$dropdown[1] = import('dropdown1', 'POST', true, 'Arial, Helvetica, sans-serif'); // Police option menu déroulant
		$dropdown[2] = import('dropdown2', 'POST', true, 'normal'); // Style de police option menu déroulant
		$dropdown[3] = import('dropdown3', 'POST', true, '16px'); // Taille de police option menu déroulant
		$dropdown[4] = import('dropdown4', 'POST', true, 'inherit'); // Couleur de police option menu déroulant
		$dropdown[5] = import('dropdown5', 'POST', true, 'transparent'); // Couleur arrière-plan option menu déroulant
		$dropdown[7] = import('dropdown7', 'POST', true, 'inherit'); // Couleur de bordure option menu déroulant
		$dropdown[8] = import('dropdown8', 'POST', true, '0px'); // Taille de bordure option menu déroulant
		$dropdown[9] = import('dropdown9', 'POST', true, 'solid'); // Style de bordure option menu déroulant
		$dropdown[10] = import('dropdown10', 'POST', true, '0px'); // Taille des coins arrondis option menu déroulant
//		$dropdown[11] = import('dropdown11', 'POST', true, 'auto'); // Largeur sous-option menu déroulant
// modif Linuxmr
        $dropdown[0] = $dropdown[11] = 'auto';										  
		$dropdown[12] = import('dropdown12', 'POST', true, 'transparent'); // Couleur arrière-plan sous-option menu déroulant
		$dropdown[14] = import('dropdown14', 'POST', true, 'transparent'); // Couleur arrière-plan option visitée menu déroulant
		$dropdown[16] = import('dropdown16', 'POST', true, 'inherit'); // Couleur texte option visitée menu déroulant
		$dropdown[17] = import('dropdown17', 'POST', true, 'auto'); // Largeur panneau menu déroulant linéaire
		$dropdown[18] = import('dropdown18', 'POST', true, 'auto'); // Hauteur panneau option menu déroulant linaire
		$dropdown[19] = import('dropdown19', 'POST', true, 'transparent'); // Couleur arrière-plan option menu déroulant linaire
		$dropdown[21] = import('dropdown21', 'POST', true, 'inherit'); // Couleur de bordure option menu déroulant linéaire
		$dropdown[22] = import('dropdown22', 'POST', true, '0px'); // Taille de bordure option menu déroulant linéaire
		$dropdown[23] = import('dropdown23', 'POST', true, 'solid'); // Style de bordure option menu déroulant linéaire
		$dropdown[24] = import('dropdown24', 'POST', true, '0px'); // Taille des coins arrondis option menu déroulant linéaire
		$dropdown[25] = import('dropdown25', 'POST', true, 'inherit'); // Couleur texte sous-option menu déroulant
		$dropdown[26] = import('dropdown26', 'POST', true, 'inherit'); // Couleur texte panneau menu déroulant linéaire
		$dropdown[27] = import('dropdown27', 'POST', true, 'transparent'); // Couleur arrière-plan option survolée menu déroulant
		$dropdown[29] = import('dropdown29', 'POST', true, 'inherit'); // Couleur texte option survolée menu déroulant
        
        $tabs[0] = import('tabs0', 'POST', true, 'inherit'); // Couleur des textes des onglets inactifs
        $tabs[1] = import('tabs1', 'POST', true, 'transparent'); // Couleur arrière-plan des onglets inactifs
        $tabs[2] = import('tabs2', 'POST', true, 'inherit'); // Couleur des bordures des onglets inactifs
        $tabs[3] = import('tabs3', 'POST', true, '0px'); // Taille des bordures des onglets inactifs
        $tabs[4] = import('tabs4', 'POST', true, '0px'); // Taille des coins arrondis des onglets inactifs
        $tabs[5] = import('tabs5', 'POST', true, 'transparent'); // Couleur arrière-plan des onglets actifs
        $tabs[6] = import('tabs6', 'POST', true, 'inherit'); // Couleur des textes des onglets actifs
        $tabs[7] = import('tabs7', 'POST', true, 'transparent'); // Couleur arrière-plan des panneaux des onglets
        $tabs[8] = import('tabs8', 'POST', true, 'inherit'); // Couleur des bordures des panneaux des onglets
        $tabs[9] = import('tabs9', 'POST', true, '0px'); // Taille des bordures des panneaux des onglets
        $tabs[10] = import('tabs10', 'POST', true, '0px'); // Taille des coins arrondis des panneaux des onglets
        $tabs[11] = import('tabs11', 'POST', true, 'inherit'); // Couleur des textes des accordéons inactifs
        $tabs[12] = import('tabs12', 'POST', true, 'transparent'); // Couleur arrière-plan des accordéons inactifs
        $tabs[13] = import('tabs13', 'POST', true, 'inherit'); // Couleur des bordures des accordéons inactifs
        $tabs[14] = import('tabs14', 'POST', true, '0px'); // Taille des bordures des accordéons inactifs
        $tabs[15] = import('tabs15', 'POST', true, '0px'); // Taille des coins arrondis des accordéons inactifs
        $tabs[16] = import('tabs16', 'POST', true, 'transparent'); // Couleur arrière-plan des accordéons actifs
        $tabs[17] = import('tabs17', 'POST', true, ''); // Couleur des textes des accordéons actifs
        $tabs[18] = import('tabs18', 'POST', true, 'transparent'); // Couleur arrière-plan des panneaux des accordéons
        $tabs[19] = import('tabs19', 'POST', true, 'inherit'); // Couleur des bordures des panneaux des accordéons
        $tabs[20] = import('tabs20', 'POST', true, '0px'); // Taille des bordures des panneaux des accordéons
        
		$tabs[21] = import('tabs21', 'POST', true, '0px'); // Taille des coins arrondis des panneaux des accordéons
        
		$tabs[22] = import('tabs22', 'POST', true, 'transparent'); //
		
        $tabs[23] = import('tabs23', 'POST', true, ''); // Modal content Couleur des textes modales
        $tabs[24] = import('tabs24', 'POST', true, 'transparent'); // Modal content Couleur arrière-plan modales
        $tabs[25] = import('tabs25', 'POST', true, 'inherit'); // Modal content Couleur des bordures modales
        $tabs[26] = import('tabs26', 'POST', true, '0px'); // Modal content Taille des bordures modales
	    $tabs[27] = import('tabs27', 'POST', true, '0px'); // Modal content Taille des bordures modales
		
        $tabs[28] = import('tabs28', 'POST', true, 'transparent'); // Couleur arrière-plan des onglets survolés
        $tabs[29] = import('tabs29', 'POST', true, 'inherit'); // Couleur des textes des onglets survolés
	    $tabs[30] = import('tabs30', 'POST', true, 'transparent'); // Couleur arrière-plan des accordéons survolés
        $tabs[31] = import('tabs31', 'POST', true, 'inherit'); // Couleur des textes des accordéons survolés
		
	    $tabs[32] = import('tabs32', 'POST', true, 'transparent'); // Entete Couleur arrière-plan des modales
        $tabs[33] = import('tabs33', 'POST', true, 'inherit');     // Entete Couleur des textes des modales
		
        $tabs[34] = import('tabs34', 'POST', true, 'transparent'); // Body Couleur arrière-plan des modales
        $tabs[35] = import('tabs35', 'POST', true, 'inherit'); // Body Couleur des textes des modales
		
	    $tabs[36] = import('tabs36', 'POST', true, 'inherit'); // Footer Couleur arrière-plan des modales		
	    $tabs[37] = import('tabs37', 'POST', true, 'inherit'); // Footer Couleur des textes des modales

        $other[0] = import('other0', 'POST', true, 'inherit'); // Couleur des textes visites du jour
        $other[1] = import('other1', 'POST', true, 'transparent'); // Couleur arrière-plan visites du jour
        $other[2] = import('other2', 'POST', true, 'inherit'); // Couleur des bordures visites du jour
        $other[3] = import('other3', 'POST', true, '0px'); // Taille des bordures visites du jour
        $other[4] = import('other4', 'POST', true, '0px'); // Taille des coins arrondis visites du jour
        $other[5] = import('other5', 'POST', true, '0'); // Marges externes logo
        $other[6] = import('other6', 'POST', true, '0'); // Marges internes logo
        $other[7] = import('other7', 'POST', true, '0'); // Marges externes bannières
        $other[8] = import('other8', 'POST', true, '0'); // Marges internes bannières
        $other[9] = import('other9', 'POST', true, 'inherit'); // Couleur des bordures citations de texte
        $other[10] = import('other10', 'POST', true, '0px'); // Taille des bordures citations de texte
		$other[11] = import('other11', 'POST', true, 'dashed'); // Style de bordure citations de texte
        $other[12] = import('other12', 'POST', true, 'transparent'); // Couleur arrière-plan auteur de la citation
        $other[13] = import('other13', 'POST', true, 'inherit'); // Couleur des textes auteur de la citation
        $other[14] = import('other14', 'POST', true, 'transparent'); // Couleur arrière-plan info bulle
        $other[15] = import('other15', 'POST', true, 'inherit'); // Couleur des bordures info bulle
        $other[16] = import('other16', 'POST', true, '0px'); // Taille des bordures info bulle
        $other[17] = import('other17', 'POST', true, '0px'); // Taille des coins arrondis info bulle
        $other[18] = import('other18', 'POST', true, 'transparent'); // Couleur arrière-plan cryptogramme
        $other[19] = import('other19', 'POST', true, 'inherit'); // Couleur des textes cryptogramme
        $other[20] = import('other20', 'POST', true, 'inherit'); // Couleur des bordures cryptogramme
        $other[21] = import('other21', 'POST', true, '0px'); // Taille des bordures cryptogramme
        $other[22] = import('other22', 'POST', true, '0px'); // Taille des coins arrondis cryptogramme
        $other[23] = import('other23', 'POST', true, 'transparent'); // Couleur arrière-plan citation de code
        $other[24] = import('other24', 'POST', true, '#FFFFFF'); // Couleur arrière-plan CKEditor
        $other[25] = import('other25', 'POST', true, '#000000'); // Couleur des textes CKEditor
        $other[26] = import('other26', 'POST', true, '1.6rem'); // Taille des textes CKEditor
        $other[27] = import('other27', 'POST', true, 'inherit'); // Taille des textes blocs infos
        $other[28] = import('other28', 'POST', true, '#000000'); // Couleur arrière-plan bandeau acceptation cookies
        $other[29] = import('other29', 'POST', true, '#FFFFFF'); // Couleur des textes bandeau acceptation cookies
        $other[30] = import('other30', 'POST', true, '1.6rem'); // Taille des textes bandeau acceptation cookies
        
        $opacity[0] = import('opacity0', 'POST', true, '1'); // Opacité arrière-plan entête
        $opacity[1] = import('opacity1', 'POST', true, '1'); // Opacité arrière-plan bas de page
        $opacity[2] = import('opacity2', 'POST', true, '1'); // Opacité arrière-plan pied de page
        $opacity[3] = import('opacity3', 'POST', true, '1'); // Opacité arrière-plan menu icônes
        $opacity[4] = import('opacity4', 'POST', true, '1'); // Opacité arrière-plan colonne gauche
        $opacity[5] = import('opacity5', 'POST', true, '1'); // Opacité arrière-plan colonne droite
        $opacity[6] = import('opacity6', 'POST', true, '1'); // Opacité arrière-plan colonnes blog
        $opacity[7] = import('opacity7', 'POST', true, '1'); // Opacité arrière-plan zone principale
        $opacity[8] = import('opacity8', 'POST', true, '1'); // Opacité arrière-plan colonne centrale
        $opacity[9] = import('opacity9', 'POST', true, '1'); // Opacité arrière-plan titres boîtes centrales
        $opacity[10] = import('opacity10', 'POST', true, '1'); // Opacité arrière-plan tables boîtes centrales
        $opacity[11] = import('opacity11', 'POST', true, '1'); // Opacité arrière-plan titres boîtes latérales
        $opacity[12] = import('opacity12', 'POST', true, '1'); // Opacité arrière-plan tables boîtes latérales
        $opacity[15] = import('opacity15', 'POST', true, '1'); // Opacité arrière-plan zone au-dessus
        $opacity[16] = import('opacity16', 'POST', true, '1'); // Opacité arrière-plan zone édito
        $opacity[17] = import('opacity17', 'POST', true, '1'); // Opacité arrière-plan zone au-dessous
        $opacity[18] = import('opacity18', 'POST', true, '1'); // Opacité arrière-plan header
        $opacity[19] = import('opacity19', 'POST', true, '1'); // Opacité arrière-plan citations
        $opacity[22] = import('opacity22', 'POST', true, '1'); // Opacité arrière-plan auteur de la citation
        $opacity[23] = import('opacity23', 'POST', true, '1'); // Opacité arrière-plan citation de code
        
        $marge[9] = import('marge9', 'POST', true, '0'); // Marges externes titre boîtes centrales
        $marge[10] = import('marge10', 'POST', true, '0'); // Marges internes titre boîtes centrales
        $marge[11] = import('marge11', 'POST', true, '0'); // Marges externes boîtes centrales
        $marge[12] = import('marge12', 'POST', true, '0'); // Marges internes boîtes centrales
        $marge[13] = import('marge13', 'POST', true, '0'); // Marges externes titre boîtes latérales
        $marge[14] = import('marge14', 'POST', true, '0'); // Marges internes titre boîtes latérales
        $marge[15] = import('marge15', 'POST', true, '0'); // Marges externes boîtes latérales
        $marge[16] = import('marge16', 'POST', true, '0'); // Marges internes boîtes latérales
        $marge[24] = import('marge24', 'POST', true, '0'); // Marges internes du bandeau TOP
        $marge[25] = import('marge25', 'POST', true, '0'); // Marges internes du bandeau MAIN
        $marge[26] = import('marge26', 'POST', true, '0'); // Marges internes du bandeau BOTTOM
        $marge[27] = import('marge27', 'POST', true, '0'); // Marges internes du bandeau FOOTER
        $marge[28] = import('marge28', 'POST', true, '0'); // Marges internes zone au-dessus
        $marge[29] = import('marge29', 'POST', true, '0'); // Marges internes zone éditorial
        $marge[30] = import('marge30', 'POST', true, '0'); // Marges internes zone au-dessous
        $marge[33] = import('marge33', 'POST', true, '4px 0 0 0'); // Marges externes menu latéral
        $marge[34] = import('marge34', 'POST', true, '0 4px 2px'); // Marges internes menu latéral
        $marge[35] = import('marge35', 'POST', true, '2px 0 0 10px'); // Marges externes options menu latéral
        $marge[36] = import('marge36', 'POST', true, '3px 5px'); // Marges internes options menu latéral
        $marge[37] = import('marge37', 'POST', true, '-2px 5px 0 10px'); // Marges externes icône menu latéral
        $marge[38] = import('marge38', 'POST', true, '3px 4px 0 0'); // Marges externes icône drapeau
	}
    
	include 'inc/confskin.inc';
    if ($site[7] == 'on' || PHP700) {
        @ob_flush();
        sleep(PHP700 ? 3 : $site[8]);
    }

    if($regpart == 2) {
	    $boutoncenter = '';
        $messreg2 = " OK";
        $cssbody2 = $body[3].' '.$body[4].(empty($body[2]) ? '' : ' fixed');
        $cssbody8 = empty($body[8]) ? '' : UrlBKimg($body[8]).$body[9].' bottom';
        $cssbody3 = empty($body[1]) 
          ? (empty($body[8]) ? '' : "\n".'    background: '.$cssbody8.';') 
          : "\n".'    background: '.UrlBKimg($body[1]).$cssbody2.(!empty($body[8]) ? ', '.$cssbody8 : '').';';
        $css5  = empty($page[5]) ? "\n".'    background-color: transparent;' : "\n".'    background-color: '.hex2rgba($page[5], $opacity[2]).';';
        $css8  = empty($titre[8]) ? '' : "\n".'    background: '.UrlBKimg($titre[8]).';';
        $css9  = empty($titre[9]) ? '' : "\n".'    background: '.UrlBKimg($titre[9]).';';
        $css10 = empty($calendar[0]) ? '    background-color: inherit;' : '    background-color: '.$calendar[0].';';
        $css11 = $boutoncenter != '' ? '    border: 0px;'."\n".'    height: 20px;'."\n".'    margin: 2px 0px;'."\n".'    padding-bottom: 2px;'."\n".'    vertical-align: top;'."\n".'    text-decoration: none;'."\n".'    background-color: transparent;'."\n".'    background-image: url('.$boutoncenter.');'."\n".'    background-repeat: repeat-x;'."\n".'    font-size: 9px;' : '';
        $css14 = "\n".'    '.getBorder($bordure[4], $bordure[31], $bordure[3])."\n".'    border-radius: '.$bordure[5].';';
        $css15 = "\n".'    '.getBorder($bordure[8], $bordure[34], $bordure[7])."\n".'    border-radius: '.$bordure[9].';';
        $css16 = "\n".'    '.getBorder($bordure[11], $bordure[35], $bordure[10])."\n".'    border-radius: '.$bordure[12].';';
        $css17 = "\n".'    '.getBorder($bordure[14], $bordure[36], $bordure[13])."\n".'    border-radius: '.$bordure[15].';';
        $css18 = "\n".'    '.getBorder($bordure[17], $bordure[37], $bordure[16])."\n".'    border-radius: '.$bordure[18].';';
        $css20 = empty($page[29]) ? "\n".'    background-color: transparent;' : (empty($page[30]) ? "\n".'    background-color: '.hex2rgba($page[29], $opacity[0]).';' : '');
        $css21 = "\n".'    '.getBorder($bordure[20], $bordure[29], $bordure[19])."\n".'    border-radius: '.$bordure[21].';';
        $css22 = "\n".'    background: '.(empty($page[29]) ? '' : hex2rgba($page[29], $opacity[0]).' ').';';
        $css23 = empty($page[31]) ? "\n".'    background-color: transparent;' : (empty($page[32]) ? "\n".'    background-color: '.hex2rgba($page[31], $opacity[1]).';' : '');
        $css24 = "\n".'    '.getBorder($bordure[23], $bordure[30], $bordure[22])."\n".'    border-radius: '.$bordure[24].';';
        $css25 = "\n".'    background: '.(empty($page[31]) ? '' : hex2rgba($page[31], $opacity[1]).' ').';';
        $css26 = "\n".'    '.getBorder($bordure[27], $bordure[32], $bordure[26])."\n".'    border-radius: '.$bordure[28].';';
        $css27 = empty($page[33]) ? (empty($page[34]) ? '' : "\n".'    background-color: '.hex2rgba($page[34], $opacity[3]).';') : "\n".'    background: '.UrlBKimg($page[33]).';';
        $css29 = empty($annuaire[0]) ? "\n".'    background-color: transparent;' : "\n".'    background-color: '.$annuaire[0].';';
        $css30 = "\n".'    '.getBorder($annuaire[5], $bordure[39], $annuaire[4])."\n".'    border-radius: '.$annuaire[6].';';    
        $css31 = empty($annuaire[8]) ? "\n".'    background-color: transparent;' : "\n".'    background-color: '.$annuaire[8].';';
        $css32 = empty($annuaire[9]) ? "\n".'    background-color: transparent;' : "\n".'    background-color: '.$annuaire[9].';';
        $css34 = "\n".'    '.getBorder($dropdown[8], $dropdown[9], $dropdown[7])."\n".'    border-radius: '.$dropdown[10].';';
        $css35 = "\n".'    '.getBorder($dropdown[22], $dropdown[23], $dropdown[21])."\n".'    border-radius: '.$dropdown[24].';';
        $css36 = empty($dropdown[6]) ? (empty($dropdown[5]) ? '' : "\n".'    background: '.$dropdown[5].';') : '    background: '.UrlBKimg($dropdown[6]).';';
        $css37 = empty($dropdown[13]) ? (empty($dropdown[12]) ? '' : "\n".'    background: '.$dropdown[12].';') : '    background: '.UrlBKimg($dropdown[13]).';';
        $css38 = empty($dropdown[15]) ? (empty($dropdown[14]) ? '' : "\n".'    background: '.$dropdown[14].';') : '    background: '.UrlBKimg($dropdown[15]).';';
        $css40 = empty($titre[14]) ? '' : "\n".'    background-color: '.hex2rgba($titre[14], $opacity[4]).';';
        $css41 = "\n".'    '.getBorder($titre[17], $titre[18], $titre[16])."\n".'    border-radius: '.$titre[19].';';
        $css43 = "\n".'    background-color: '.hex2rgba($titre[21], $opacity[5]).';';
        $css44 = "\n".'    '.getBorder($titre[24], $titre[25], $titre[23])."\n".'    border-radius: '.$titre[26].';';
        $css45 = empty($titre[28]) ? (empty($titre[27]) ? '' : "\n".'    background-color: '.hex2rgba($titre[27], $opacity[6]).';') : '    background: '.UrlBKimg($titre[28]).';';
        $css46 = "\n".'    '.getBorder($titre[30], $titre[31], $titre[29])."\n".'    border-radius: '.$titre[32].';';
        $css60 = "\n".'    '.getBorder($titre[37], $titre[38], $titre[36])."\n".'    border-radius: '.$titre[39].';';
        $css61 = "\n".'    '.getBorder($titre[44], $titre[45], $titre[43])."\n".'    border-radius: '.$titre[46].';';
        $css63 = "\n".'    '.getBorder($titre[57], $titre[58], $titre[56])."\n".'    border-radius: '.$titre[59].';';
        $css64 = "\n".'    '.getBorder($titre[63], $titre[64], $titre[62])."\n".'    border-radius: '.$titre[65].';';
        $css65 = "\n".'    '.getBorder($titre[69], $titre[70], $titre[68])."\n".'    border-radius: '.$titre[71].';';
        $css66 = "\n".'    '.getBorder($titre[75], $titre[76], $titre[74])."\n".'    border-radius: '.$titre[77].';';
        $css67a = empty($titre[34]) ? "\n".'    background-color: transparent;' : (empty($titre[35]) ? "\n".'    background-color: '.hex2rgba($titre[34], $opacity[7]).';' : '');
        $css67b = "\n".'    background: '.(empty($titre[34]) ? '' : hex2rgba($titre[34], $opacity[7]).' ').';';
        $css68a = empty($titre[41]) ? "\n".'    background-color: transparent;' : (empty($titre[42]) ? "\n".'    background-color: '.hex2rgba($titre[41], $opacity[8]).';' : '');
        $css68b = "\n".'    background: '.(empty($titre[41]) ? '' : hex2rgba($titre[41], $opacity[8]).' ').';';
        $css71a = empty($titre[60]) ? "\n".'    background-color: transparent;' : (empty($titre[61]) ? "\n".'    background-color: '.hex2rgba($titre[60], $opacity[15]).';' : '');
        $css71b = "\n".'    background: '.(empty($titre[60]) ? '' : hex2rgba($titre[60], $opacity[15]).' ').';';
        $css72a = empty($titre[66]) ? "\n".'    background-color: transparent;' : (empty($titre[67]) ? "\n".'    background-color: '.hex2rgba($titre[66], $opacity[16]).';' : '');
        $css72b = empty($titre[67]) ? '' : "\n".'    background: '.(empty($titre[66]) ? '' : hex2rgba($titre[66], $opacity[16]).' ').UrlBKimg($titre[67]).';';
        $css73a = empty($titre[72]) ? "\n".'    background-color: transparent;' : (empty($titre[73]) ? "\n".'    background-color: '.hex2rgba($titre[72], $opacity[17]).';' : '');
        $css73b = "\n".'    background: '.(empty($titre[72]) ? '' : hex2rgba($titre[72], $opacity[17]).' ').';';
        $css74 = empty($dropdown[28]) ? (empty($dropdown[27]) ? '' : "\n".'    background: '.$dropdown[27].';') : '    background: '.UrlBKimg($dropdown[28]).';';
        $css75 = empty($page[43]) ? "\n".'    background-color: transparent;' : (empty($page[44]) ? "\n".'    background-color: '.hex2rgba($page[43], $opacity[18]).';' : '');
        $css76 = "\n".'    '.getBorder($bordure[41], $bordure[43], $bordure[40])."\n".'    border-radius: '.$bordure[42].';';
        $css77 = "\n".'    background: '.(empty($page[43]) ? '' : hex2rgba($page[43], $opacity[18]).' ').';';
        $css78 = empty($citation[11]) ? (empty($citation[10]) ? '' : "\n".'    background-color: '.hex2rgba($citation[10], $opacity[19]).';') : "\n".'    background: '.UrlBKimg($citation[11]).';';
        $css79 = "\n".'    '.getBorder($citation[13], $citation[15], $citation[12])."\n".'    border-radius: '.$citation[14].';';
        $css80 = $page[56] != 'transparent' ? '#slide1 {'."\n".'    background: '.$page[56].';'."\n".'}'."\n" :
          (empty($page[24]) ? '' : "\n".'#slide1 {'."\n".'    background: '.UrlBKimg($page[24]).' center 0 no-repeat fixed;'."\n".'    background-size: cover;'."\n".'}'."\n");
        $css81 = $page[57] != 'transparent' ? '#slide2 {'."\n".'    background: '.$page[57].';'."\n".'}'."\n" :
          (empty($page[25]) ? '' : '#slide2 {'."\n".'    background: '.UrlBKimg($page[25]).' center 0 no-repeat fixed;'."\n".'    background-size: cover;'."\n".'}'."\n");
        $css82 = $page[58] != 'transparent' ? '#slide3 {'."\n".'    background: '.$page[58].';'."\n".'}'."\n" :
          (empty($page[26]) ? '' : '#slide3 {'."\n".'    background: '.UrlBKimg($page[26]).' center 0 no-repeat fixed;'."\n".'    background-size: cover;'."\n".'}');

        /**********************************
         * Construction de la feuille CSS *
         **********************************/
        $csslist = "/* ---------  CSS generated for ".$mycss."  ----------- */

".$tfonts."
@font-face {
  font-family: \"fontello\";
  src: url(\"font/fontello.eot?33978178\");
  src: url(\"font/fontello.eot?33978178#iefix\") format(\"embedded-opentype\"),
       url(\"font/fontello.woff?33978178\") format(\"woff\"),
       url(\"font/fontello.ttf?33978178\") format(\"truetype\"),
       url(\"font/fontello.svg?33978178#fontello\") format(\"svg\");
  font-weight: normal;
  font-style: normal;
}

html { /*  document */
    font-size: 62.5%;
    color: ".$texte[9].";
    background-color: ".$body[0].";
}
body { /*  corps du document */
    font: ".(empty($page[40]) ? 'inherit' : trim($page[41]." ".$page[40]." ".$page[39])).";
    background-color: ".$body[0].";
    color: ".$texte[9].";
}".(empty($cssbody3) ? '' : "\n".'.body { '.$cssbody3.' }')."
/************
 * Général */
a { /* lien */
    color: ".$lien[0].";
    background: inherit;
    text-decoration: none;
}
a.titre { /* lien titres */
    color: ".$texte[0].";
    background-color: inherit;
    text-decoration: none;
}
a:hover { /* lien survolé */
    color: ".$lien[1].";
    text-decoration: none;
    font-weight: normal;
    background-color: inherit;
}
h1 {
    font-size: 24px;
}
h2 {
    font-size: 22px;
}
h3 {
    font-size: 20px;
}
h4 {
    font-size: 18px;
}
p { /* paragraphe */
    margin: 4px 0;
    color: inherit;
    background: inherit;
}
hr { /* ligne horizontale */
    height: ".$barre[1].";
    ".getBorder($barre[1], $barre[2], $barre[0])."
    width: 98%;
}
ul, ol, dl, dd, dt { /* listes */
    padding: 0;
    margin: 0;
}
img, object, embed, canvas, video, audio, picture {
    border-style: none;
    max-width: 100%;
    height: auto !important;
}
.blink { animation: blink 1.2s infinite; }
@keyframes blink {
    0%   { opacity: 1; }
    50%  { opacity: 0; }
    100% { opacity: 1; }
}
/******************************************
* Mise en page des blocks div principaux */
#overlay {
    position: fixed;
    display: none;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999;
    cursor: pointer;
}
#header { /* header */
    color: inherit;".$css75.$css76.$css77."
    z-index: 0;
}
.fixed-top-line {
    margin-top: 0;
    padding: 6px 0;
    ".getBorder($bordure[41], $bordure[43], $bordure[40])."
    background-color: ".hex2rgba($page[43], $opacity[18]).";
}
#TopBoxes { /* area between header and main area */
    padding: ".(empty($marge[24]) ? 0 : $marge[24]).";
    color: inherit;
    max-width: ".$titre[78]." !important;".$css20.$css21.$css22."
}
#MainContent { /* main area */
    margin: 4px 0;
    padding: ".(empty($marge[25]) ? 0 : $marge[25])." !important;
    max-width: ".$titre[79]." !important;".$css60.$css67a.$css67b."
}
#LeftBoxes { /* left boxes area */"
    .$css40.$css41."
    order: ".$body[10].";
}
#CenterBoxes { /* center area */"
    .$css61.$css68a.$css68b."
    order: ".$body[11].";
}
#RightBoxes { /* right boxes area */"
    .$css43.$css44."
    order: ".$body[12].";
}
#AboveBoxes { /* boxes above in center area */
    padding: ".(empty($marge[28]) ? 0 : $marge[28]).";".$css71a.$css71b.$css64."
}
#EditoBoxes { /* boxes that appear in the home page - HOMEPAGE CONFIG */
    padding: ".(empty($marge[29]) ? 0 : $marge[29]).";".$css72a.$css65."
}
#UnderBoxes { /* boxes under center area */
    padding: ".(empty($marge[30]) ? 0 : $marge[30]).";".$css73a.$css73b.$css66."
}
#BottomBoxes { /* area between main and footer */
    padding: ".(empty($marge[26]) ? 0 : $marge[26])." !important;
    color: inherit;
    max-width: ".$titre[80]." !important;".$css23.$css24.$css25."
}
#footer { /* footer Copyright */
    font: ".$page[47]." ".(empty($page[46]) ? 'inherit' : $page[46])." ".$page[45].";
    text-align: ".$texte[13].";
    color: ".$texte[8].";
    padding: ".(empty($marge[27]) ? 0 : $marge[27]).";
    margin: 0 auto;
    height: ".$page[21].";".$css5.$css14."
    max-width: ".(empty($titre[81]) ? '100%' : $titre[81])." !important;
}
.copyright {
    background-color: transparent;
    color: ".$texte[8].";
    text-align: ".$texte[13].";
}
.top {
    background: transparent;
    color: ".$texte[8].";
    text-align: ".$texte[13].";
    font: 1.2em ".$page[39].";
    font-weight: bold;
}
a.top span { font-family: fontello; }
.txtspeC { margin: 12px 0 4px; }
.txtspeLR { margin: 8px 0 4px; }
.txtspeG { padding: 10px 0 0; }
.timer {
    background-color: transparent;
    color: ".$texte[8].";
    font: italic 0.9em ".$page[39].";
    text-align: ".$texte[13].";
    padding: 8px;
}
#skinCopyright {
    width: 100%;
    margin-top: 2px;
    color: ".$texte[8].";
    text-align: ".$texte[13].";
}
#BlogLeftBoxes { /* blog left column */
    height: 100%;".$css45.$css46."
    order: ".$body[13].";
}
#BlogRightBoxes { /* blog right boxes */
    height: 100%;".$css45.$css46."
    order: ".$body[15].";
}
";
$csslist .= "
/***********************
 * Effet de parallaxe */"
 .$css80.$css81.$css82."
.slide_inside {
    width: 100%;
    max-width: 100%;
    margin: 0 auto;
    display: flex;
}
#slide1 .slide_inside { min-height: 0px; }
#slide2 .slide_inside { min-height: 0px; }
#slide3 .slide_inside { min-height: 0px; }
#paranav {
  position: fixed;
  top: ".$page[52].";
  ".($page[50] == 'left' ? 'left: 6px' : 'right: 20px').";
  list-style: none;
  z-index: 999;
}
#paranav li {
  margin: 0 0 15px;
}
#paranav li a {
  display: block;
  width: ".($page[7] == 'on' ? $page[63] : '10px').";
  height: ".($page[7] == 'on' ? $page[64] : '10px').";
  border-radius: ".($page[7] == 'on' ? '0px' : '10px').";
  background-color: ".($page[7] == 'on' ? 'transparent' : $page[51]).";
  border: ".($page[7] == 'on' ? 'none' : 'solid 2px '.$bordure[44]).";
}".($page[7] == 'on' ? '
#paranav li:nth-child(1) a {
  background: url(img/para1.png);
}
#paranav li:nth-child(2) a {
  background: url(img/para2.png);
}
#paranav li:nth-child(3) a {
  background: url(img/para3.png);
}' : '')."
/****************************
 * Mise en page des blocks */
div.logo { /* block logo */
    margin: ".(empty($other[5]) ? 0 : $other[5]).";
    padding: ".(empty($other[6]) ? 0 : $other[6])." !important;
    text-align: center;
}
div.banner { /* block bannière */
    background-repeat: no-repeat;
    margin: ".(empty($other[7]) ? 0 : $other[7]).";
    padding: ".(empty($other[8]) ? 0 : $other[8]).";
    text-align: center;
    position: inherit;
    z-index: 10;
}
/*************************
 * block boite citation */
div.quote { /* block citation */
    margin: ".(empty($citation[16]) ? 0 : $citation[16]).";
    padding: ".(empty($citation[17]) ? 0 : $citation[17]).";
    text-align: center;".$css78.$css79."
}
div.quote span.labelcita { /* label */
    font: bold ".(empty($citation[1]) ? 'inherit' : $citation[1])." ".$citation[0].";
    color: ".$citation[2].";
    background: transparent;
    margin: 0;
    padding: 0 8px;
}
div.quote span.cita { /* texte */
    font: ".$citation[3]." ".(empty($citation[1]) ? 'inherit' : $citation[1])." ".$citation[0].";
    color: ".$citation[2].";
    background: transparent;
    font-style: normal;
    text-align: center;
    border: 0px;
    margin: 0;
    padding: 0 8px;
}
div.quote span.author { /* auteur */
    font: italic ".(empty($citation[1]) ? 'inherit' : $citation[1])." ".$citation[0].";
    color: ".$citation[2].";
    background: transparent;
}
#BottomBoxes .quote span { color: ".$titre[13]." !important; }
";
$csslist .= "
/*********************************
 * bloc horizontal Menu sections */
nav.menuIcons {".$css26.$css27."
}
.menuIcons.navbar .navbar-toggler {  /* bordure icône de la barre en mobile */
    border-color: ".hex2rgba($lien[5], 0.9).";
    border-radius: 0.35rem;
    margin-right: .5rem;
}
nav.menuIcons.navbar.navbar-light .navbar-toggler-icon { /* couleur texte icône de la barre en mobile (stroke) */
	width: 2em !important;
	height: 2em !important;
	background-image: url(\"data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'30\' height=\'30\' viewBox=\'0 0 448 512\' %3e%3cpath stroke=\'".hex2rgba($lien[4], 0.9)."\' stroke-linecap=\'round\' stroke-miterlimit=\'10\' stroke-width=\'4\' d=\'M16 132h416c8.8 0 16-7.2 16-16V76c0-8.8-7.2-16-16-16H16C7.2 60 0 67.2 0 76v40c0 8.8 7.2 16 16 16zm0 160h416c8.8 0 16-7.2 16-16v-40c0-8.8-7.2-16-16-16H16c-8.8 0-16 7.2-16 16v40c0 8.8 7.2 16 16 16zm0 160h416c8.8 0 16-7.2 16-16v-40c0-8.8-7.2-16-16-16H16c-8.8 0-16 7.2-16 16v40c0 8.8 7.2 16 16 16z\'/%3e%3c/svg%3e\") !important;
}
nav.menuIcons a.menuiconflag i { margin: 5px 8px 0 0; }
nav.menuIcons ul li { text-align: left !important;line-height: 3.6 !important;display: inline-block !important;}
ul.container-fluid { text-align: left !important;line-height: 3.6 !important;display: inline-block !important;}
nav.menuIcons ul li a span { /* lien */
    background-color: ".$lien[7].";
    color: ".$lien[4].";
    margin: ".(empty($lien[15]) ? 0 : $lien[15]).";
    padding: ".(empty($lien[14]) ? 0 : $lien[14]).";
    ".getBorder($lien[11], $lien[12], $lien[10])."
    border-radius: ".$lien[13].";
    font: ".$page[37]." ".(empty($page[19]) ? 'inherit' : $page[19])." ".$page[18].";
    line-height: 3.2rem;
}
nav.menuIcons ul li a span.active { /* lien actif */
    background-color: ".$lien[8].";
    color:".$lien[9].";
    padding: ".(empty($lien[14]) ? 0 : $lien[14]).";
    ".getBorder($lien[11], $lien[12], $lien[10])."
    border-radius: ".$lien[13].";
}
nav.menuIcons ul li a:hover span { /* lien survolé*/
    background-color: ".$lien[6].";
    color: ".$lien[5].";
    margin: ".(empty($lien[15]) ? 0 : $lien[15]).";
    padding: ".(empty($lien[14]) ? 0 : $lien[14]).";
    ".getBorder($lien[11], $lien[12], $lien[10])."
    border-radius: ".$lien[13].";
}
.mi-title {
    font-size: 1.8rem;
    color: ".$lien[4].";
}
/*************************************
 * bloc boîte latérale Menu Sections */
span.menuIcons_flag a i { margin-top: 4px; }
.titleboxmenu { padding-left: 28px; }
ul.boxmenu { /* block liste */
    margin: 0;
    padding: 5px 0;
    list-style: none;
}
ul.boxmenu li { /* items liste */
    margin-bottom: 10px;
}
ul.boxmenu li .cur_item { /* items actifs liste */
    margin-bottom: 10px;
}
ul.boxmenu li img { /* icons */
    vertical-align: middle;
    margin-right: 10px;
    height: auto;
    max-height: 56px;
}
/****************************
 * Menu navigation latérale */
.sidenav, .sideconnect {
    height: 100%;
    width: 0;
    position: fixed;
    z-index: 999;
    top: 0;
    right: -12px;
    background-color: ".hex2rgba($lien[19], 1).";
    border-left: ".$lien[21]." solid ".$lien[20].";
    padding-top: 42px;
    overflow-x: hidden;
    transition: 0.8s;
}
.sideconnect .tblbox, .sidenav ul { padding-bottom: 32px; }
.sidenav li a div {
    padding: 2px 8px;
    width: 92%;
    font: ".$page[69]." ".(empty($page[70]) ? 'inherit' : $page[70])." ".$page[68].";
    ".getBorder($lien[33], $lien[35], $lien[32])."
    border-radius: ".$lien[34].";
    background-color: ".hex2rgba($lien[31], 1).";
    color: ".$lien[22].";
}
.sidenav li a:hover div {
    ".getBorder($lien[33], $lien[35], $lien[32])."
    border-radius: ".$lien[34].";
    background-color: ".hex2rgba($lien[24], 1).";
    color: ".$lien[23].";
    transition: 0.8s;
}
.sidenav .closebtn, .sideconnect .closebtn {
    position: absolute;
    top: 12px;
    right: 40px;
    font-size: 24px;
    color: ".$lien[36].";
}
.smlogo {
    font: ".$titre[48]." ".$titre[49]." ".$titre[47]." !important;
    color: ".$titre[50].";
    background-color: ".hex2rgba($titre[51], 1).";
    ".getBorder($titre[53], $titre[54], $titre[52])."
    border-radius: ".$titre[55].";
}
.sideconnect .tblbox { border: 0; margin: 4px 18px 0 0; }
#GY_connect { padding-left: 10px; max-width: 100%; }
.sidemenu {
    background-color: ".hex2rgba($lien[25], 1).";
    margin: ".$marge[33].";
    padding: ".$marge[34].";
    float: right;
    ".getBorder($lien[27], $lien[29], $lien[26])."
    border-radius: ".$lien[28].";
    position: relative;
    z-index: 99;
}
.sidemenu div, .sidemenu div a {
    cursor: pointer;
    font: ".$page[66]." ".(empty($page[67]) ? 'inherit' : $page[67])." ".$page[65].";
    background-color: ".hex2rgba($lien[30], 1).";
    color: ".$lien[16].";
}
.sidemenu div.sidetexte:hover, .sidemenu div.sidetexte a:hover {
    background-color: ".hex2rgba($lien[18], 1).";
    color: ".$lien[17].";
}
.sidemenu > div { float: right; }
.sidemenu div.sidetexte { margin: ".$marge[35]."; padding: ".$marge[36]."; }
.sidemenu div.sideicon {
    font-size: 24px;
    margin: ".$marge[37].";
    border: thin solid ".$lien[16].";
}
.sideflag a.menuiconflag i { margin: ".$marge[38]."; }
.tblbox.SMI { background: transparent; border: 0px; }
/****************
 * Fil d'ariane */
.ariane { /* bloc fil d'ariane */
    text-align: left;
    padding: 4px;
    margin: 0px;
    color: ".$citation[5].";
}
div.ariane span.arianeOn a { /* adresses actives */
    font-style: italic;
    color: ".$citation[4].";
}
div.ariane span.arianeOff { /* page courante */
    font-style: italic;
    color: ".$citation[5].";
}
#BottomBoxes .ariane, #BottomBoxes .ariane span, 
#BottomBoxes span.arianeOn a { color: ".$titre[13]." !important; }
";
$csslist .= "
/****************************************
 * Mise en page des blocs dans le corps */
/* bloc boîtes latérales */
.htable {
    margin-bottom: 8px;
}
.htable1 {
    margin-bottom: 8px;
}
.titrebox { /* Titre */
    background-color: ".hex2rgba($titre[5], $opacity[11]).";".$css9.$css17."
    margin: ".(empty($marge[13]) ? 0 : $marge[13]).";
    padding: ".(empty($marge[14]) ? 0 : $marge[14]).";
    white-space: normal;
    word-wrap: break-word;
    font: ".$titre[11]." ".(empty($titre[7]) ? 'inherit' : $titre[7])." ".$titre[6].";
    color: ".$titre[4].";
    text-align: ".(empty($texte[11]) ? 'left' : $texte[11]).";
    cursor: pointer;
}
.tblbox {  /* Corps */
    background-color: ".hex2rgba($texte[6], $opacity[12]).";".$css18."
    margin: ".(empty($marge[15]) ? 0 : $marge[15]).";
    padding: ".(empty($marge[16]) ? 0 : $marge[16]).";
    color: inherit;
    text-align:left;
    line-height: ".$page[11].";
}
.tblboxover { /* Corps survolé */
    background-color: ".hex2rgba($texte[7], $opacity[12]).";
}
#BottomBoxes .titrebox {
    background-color: ".$titre[15].";
    color: ".$titre[10].";
}
#BottomBoxes .tblbox {
    background-color: ".$titre[20].";
    color: ".$titre[13]." !important;
}
/* bloc boîtes centrales */
.titre {
    background-color: ".hex2rgba($titre[1], $opacity[9]).";".$css8.$css15."
    margin: ".(empty($marge[9]) ? 0 : $marge[9]).";
    padding: ".(empty($marge[10]) ? 0 : $marge[10]).";
    white-space: normal;
    word-wrap: break-word;
    font: ".$titre[12]." ".(empty($titre[3]) ? 'inherit' : $titre[3])." ".$titre[2].";
    color: ".$titre[0].";
    text-align: ".(empty($texte[10]) ? 'left' : $texte[10]).";
}
.titre a {
    background-color: ".hex2rgba($titre[1], $opacity[9]).";
    padding: ".(empty($marge[10]) ? 0 : $marge[10]).";
    white-space: normal;
    word-wrap: break-word;
    font: ".$titre[12]." ".(empty($titre[3]) ? 'inherit' : $titre[3])." ".$titre[2].";
    color: ".$titre[0].";
    text-align: ".(empty($texte[10]) ? 'left' : $texte[10]).";
}
.titre a span {
    background-color: inherit;
}
.tbl {
    font: ".$page[36]." ".$page[2]." ".$page[1].";
    color: ".$texte[0].";".$css16."
    margin: ".(empty($marge[11]) ? 0 : $marge[11]).";
    padding: ".(empty($marge[12]) ? 0 : $marge[12]).";
    text-align:left;
    line-height: ".$page[6].";
    background-color: ".hex2rgba($texte[1], $opacity[10]).";
}
.tbl>p, .tbl>div { line-height: 2rem }
.tblover {
    background-color: ".hex2rgba($texte[2], $opacity[10]).";
}
div ul, ol { /* listes */
    padding: 8px;
    margin-left: 16px;
}
div li{
    padding: 0;
    margin-left: 12px;
}
.box {
    font: ".$page[35]." ".(empty($page[17]) ? 'inherit' : $page[17])." ".$page[16].";
    color: ".$texte[5].";
    background-color: transparent;
    text-align: left;
    line-height: inherit;
}
p.box {
    margin: 0;
    font: ".$page[35]." ".(empty($page[17]) ? 'inherit' : $page[17])." ".$page[16].";
    color: ".$texte[5].";
    background-color: transparent;
    line-height: inherit;
}
.box a, .item a {
    color: ".$lien[2].";
    background-color: transparent;
    text-decoration: none;
    font: ".$page[35]." ".(empty($page[17]) ? 'inherit' : $page[17])." ".$page[16].";
}
.box a:hover, .item a:hover {
    color: ".$lien[3].";
    text-decoration: none;
    background-color: transparent;
}
#BottomBoxes .box, #BottomBoxes .box a, #BottomBoxes .item a,
#BottomBoxes p.rubr, #BottomBoxes ul.item li.item { color: ".$titre[13]." !important; }
/* boîtes diverses */
.boxnet {
    width: auto;
    margin: auto;
    text-align: center;
    background-color: transparent;
}
.boxprint, .boxprint a { 
    margin: 0;
    height: 40px;
    font: ".$citation[7].' '.(empty($citation[8]) ? 'inherit' : $citation[8]).' '.$citation[6].";
    color: ".$citation[9].";
    background-color: transparent;
}
.boxprint i { 
    color: ".$citation[9].";
    background-color: transparent;
}
.boxprint1 {
    float: right;
}
.boxprint2 {
    float: right;
    padding-right: 10px;
}
#BottomBoxes .boxprint span, #BottomBoxes .boxprint span a i {
    color: ".$titre[13]." !important;
}
/* search boxes */
.thinboxsearch {
    background-color: transparent;
    text-align: center;
}
.thinboxsearchW { margin: 4px 0 0; }
.wideboxsearch {
    background-color: transparent;
    text-align: center;
}
.wideboxsearch .input-group-text,
.thinboxsearch .input-group-text,
.wideboxsearch1 .input-group-text,
.thinboxsearch1 .input-group-text,
.searchTopForum .input-group-text {
	font-size: 1.6rem;
	height: auto;
    margin-top: 4px !important;
    margin-bottom: 4px !important;
}
.thinboxsearch1 {
    background-color: transparent;
    text-align: center;
}
.wideboxsearch1 {
    background-color: transparent;
    padding-bottom: 8px;
    margin: auto;
    text-align: center;
    max-width: 100%;
}
.imgsearchW {
    vertical-align: bottom;
    cursor: pointer;
    padding-left: 0;
}
.thinboxsearch2 {
    display: none;
    background-color: transparent;
    text-align: center;
}
.wideboxsearch2 {
    display: none;
    background-color: transparent;
    padding-bottom: 8px;
    margin: -4px auto auto;
    text-align: center;
    width: 100%;
}
.thinboxsearch2W, .wideboxsearch2W {
    position: relative;
    z-index: 999;
}
.thinboxsearch3W, .wideboxsearch3W {
    text-align: center;
    width: 100%;
    margin: 0 auto;
}
.searchlngW {
    text-align: center;
    width: 80%;
    margin: 2px auto 0;
}
.adv1web {
    text-align: center;
    font-size: 0.8em;
    margin-top: 3px;
}
.adv2 {
    display: none;
    text-align: center;
    font-size: 0.8em;
    margin-top: -2px;
    height: 2px;
}
.adv2W {
    height: 15px;
}
.searchclose {
    cursor: pointer;
    float: right;
    margin: -15px 22px 0 0;
}
#BottomBoxes .wideboxsearch1 .adv1web a, #BottomBoxes .wideboxsearch1 select,
#BottomBoxes .wideboxsearch1 i { color: ".$titre[13]." !important; }
";
$csslist .= "
/*********************
 * bloc boîtes menus */
.rubr { /* catégories des articles */
    font: ".$page[35]." ".(empty($page[17]) ? 'inherit' : $page[17])." ".$page[16].";
    color: ".$lien[2].";
    background-color: transparent;
    text-align: left;
    margin: 1rem auto;
    padding: 4px 2px;
}
p.rubr {
    padding: 0 2px;
    margin: 1rem auto;
    font: ".$page[35]." ".(empty($page[17]) ? 'inherit' : $page[17])." ".$page[16].";
    color: inherit;
    background-color: transparent;
    text-align: left;
    line-height: 2rem;
    font-weight: bold;
}
p.rubr:hover { color: ".$lien[3]."; }
.item {
    font: ".$page[35]." ".(empty($page[17]) ? 'inherit' : $page[17])." ".$page[16].";
    color: ".$lien[2].";
    background-color: transparent;
    text-align: left;
    list-style: none;
    margin: 0;
    padding: 2px 0;
}
ul.item {
    margin: 0;
    padding: 0;
}
ul.item li.item { /* titres des articles */
    font: ".$page[35]." ".(empty($page[17]) ? 'inherit' : $page[17])." ".$page[16].";
    color: ".$lien[2].";
    background-color: transparent;
    text-align: left;
    list-style: none;
    padding: 2px 0 2px 10px;
}
.curr_item {
    font: ".$page[35]." ".(empty($page[17]) ? 'inherit' : $page[17])." ".$page[16].";
    color: ".$forum[11].";
    background-color: ".$forum[0].";
    text-align: left;
    list-style: none;
    padding: 2px 0;
}
ul.item li.curr_item a { /* titre article est affiché */
    font: ".$page[35]." ".(empty($page[17]) ? 'inherit' : $page[17])." ".$page[16].";
    color: ".$forum[11].";
    background-color: ".$forum[0].";
    text-align: left;
    list-style: none;
    padding: 2px 0 2px 10px;
}
";
$csslist .= "
/**********
 * Photos */
.phrubr {
    font: ".$annuaire[11].' '.(empty($annuaire[12]) ? 'inherit' : $annuaire[12]).' '.$annuaire[10].";
    color: ".$annuaire[13].";
    text-align: left;
    margin: 1rem auto;
    padding: 2px 6px;
    border: 0px;
    cursor: pointer;".$css31."
}
.phrubr2 {
    font: ".$annuaire[15].' '.(empty($annuaire[16]) ? 'inherit' : $annuaire[16]).' '.$annuaire[14].";
    color: ".$annuaire[17].";
    text-align: left;
    margin: 1rem auto;
    padding: 2px 0px;
    border: 0px;
    cursor: pointer;".$css32."
}
.phsubrubr { 
    text-align: justify;
    padding-left: 35px;
}
.phitem2 {
    float: left;
    text-align: right;
    margin-left: 20px !important;
    margin-left: 12px;
    height: 15px;
}
.phcomment {
    font: ".$page[36]." ".$page[2]." ".$page[1].";
    padding: 0 10px;
}
.phimgs {
    font: ".$page[36]." ".$page[2]." ".$page[1].";
    margin: 5px;
    background-color: transparent;
    padding: 10px 5px 5px 5px;
}
.phimgs2 {
    padding-left: 4px; 
}
.phimg { /* miniatures des galeries photos */
    background: transparent;
    margin: 4px 4px 10px;
    border: 1px solid #C0C0C0 !important;
    border-radius: 4px;
    box-shadow: 1px 2px 2px #555; 
}
.phcpt {
    font: ".$page[36]." ".$page[2]." ".$page[1].";
    float: right;
    font-weight: normal;
    padding: 0px 6px;
}
.photobox { /* contenu boite photorama */
    padding: 10px 5px 15px;
}
.phtitlebox {
    text-align:center;
    margin-bottom:6px;
    font-size: 16px;
    font-weight: bold;
}
.galcounter {
    font: ".$page[36]." ".$page[2]." ".$page[1].";
    font-weight: normal;
    margin: 20px 6px 10px;
}
#BottomBoxes .phtitlebox a { color: ".$titre[13]." !important; }
/*********
 * Liens */
.lititlecat {
    text-align: center;
    width: 80%;
    margin: 10px auto;
    padding: 4px 0;
    font: ".$annuaire[7].' '.$annuaire[2].' '.$annuaire[1].";".$css29.$css30."
    color: ".$annuaire[3].";
}
.licatlink {
    width: 80%;
    margin: 5px auto;
    padding: 4px 0;
    font: ".$annuaire[7].' '.$annuaire[2].' '.$annuaire[1].";".$css29.$css30."
    color: ".$annuaire[3].";
}
.licatlink i { padding: 0 8px; }
.lirubr {
    text-align: left;
    font: ".$annuaire[11].' '.$annuaire[12].' '.$annuaire[10].";
    color: ".$annuaire[13].";
    background: transparent;
    margin: 1rem auto;
    padding: 2px 6px;
    border: 0px;
    cursor: pointer;".$css31."
}
.lirubr2 {
    text-align: left;
    font: ".$annuaire[15].' '.$annuaire[16].' '.$annuaire[14].";
    color: ".$annuaire[17].";
    margin: 1rem auto;
    padding: 2px 6px;
    border: 0px;
    cursor: pointer;".$css32."
}
.lirubr3 {
    margin-left: 20px !important;
    margin-right: 12px;
    height: 15px;
}
.lisubrubr {
    text-align: justify;
    padding-left: 35px;
}
.licontent { margin: 5px; }
/*******
 * FAQ */
.fatitlecat {
    text-align: center;
    width: 80%;
    margin: 10px auto;
    padding: 4px 0;
    font: ".$annuaire[7].' '.$annuaire[2].' '.$annuaire[1].";".$css29.$css30."
}
.facatlink {
    width: 80%;
    margin: 5px auto;
    padding: 4px 0;
    font: ".$annuaire[7].' '.$annuaire[2].' '.$annuaire[1].";".$css29.$css30."
}
.facatlink i { padding: 0 8px; }
.farubr {
    text-align: left;
    font: ".$annuaire[11].' '.$annuaire[12].' '.$annuaire[10].";
    color: ".$annuaire[13].";
    background: transparent;
    margin: 1rem auto;
    padding: 2px 6px;
    border: 0px;
    cursor: pointer;".$css31."
}
.farubr2 {
    text-align: left;
    font: ".$annuaire[15].' '.$annuaire[16].' '.$annuaire[14].";
    color: ".$annuaire[17].";
    margin: 1rem auto;
    padding: 2px 6px;
    border: 0px;
    cursor: pointer;".$css32."
}
.farubr3 {
    margin-left: 20px !important;
    margin-right: 12px;
    height: 15px;
}
.fasubrubr {
    text-align: justify;
    padding-left: 35px;
}
.facontent { margin: 5px; }
.fainfos { font-size: ".$other[27]."; }
.fainfos strong { margin: 0 20px; }
/*******************
 * Téléchargements */
.dwnld1Body { padding: 4px; height: auto; }
.maindwnld { padding: 5px; overflow: auto; }
.divdwnld { padding: 5px; }
.div1dwnld { padding: 5px 20px; }
.dntitlecat {
    text-align: center;
    width: 80%;
    margin: 10px auto;
    padding: 4px 0;
    font: ".$annuaire[7].' '.$annuaire[2].' '.$annuaire[1].";".$css29.$css30."
}
.dncatdwnl {
    width: 80%;
    margin: 5px auto;
    padding: 4px 0;
    font: ".$annuaire[7].' '.$annuaire[2].' '.$annuaire[1].";".$css29.$css30."
}
.dncatdwnl i { padding: 0 8px; }
.dnrubr {
    text-align: left;
    font: ".$annuaire[11].' '.$annuaire[12].' '.$annuaire[10].";
    color: ".$annuaire[13].";
    background: transparent;
    margin: 1rem auto;
    padding: 2px 6px;
    border: 0px;
    cursor: pointer;".$css31."
}
.dnrubr2 {
    text-align: left;
    font: ".$annuaire[15].' '.$annuaire[16].' '.$annuaire[14].";
    color: ".$annuaire[17].";
    margin: 1rem auto;
    padding: 2px 6px;
    border: 0px;
    cursor: pointer;".$css32."
}
.dnrubr3 {
    margin-left: 20px !important;
    margin-right: 12px;
    height: 15px;
}
.dnsubrubr {
    text-align: justify;
    padding-left: 35px;
}
.dncontent { margin: 5px; }
.dnlink { margin: 0 15px; }
.dninfos { font-weight: bold; font-size: ".$other[27]."; }
.dninfos span { margin: 0 20px; }
/**************
 * info bulle */
.overpop { cursor: help; }
.overpop a i { color: inherit; }
";
$csslist .= "
/**************
 * Calendrier */
.cal {
    width: 100%;
    margin: auto;
    text-align: center;
    font: ".$calendar[15]." ".(empty($calendar[6]) ? 'inherit' : $calendar[6])." ".$calendar[5].";
    border: 0px;
    border-collapse: separate;
    border-radius: ".$calendar[14].";
    border-spacing: ".$calendar[9].";
    color: inherit;
    background-color: ".$calendar[11].";
}
tr.cal { /* calendar nav bar */
    font: ".$calendar[15]." ".(empty($calendar[6]) ? 'inherit' : $calendar[6])." ".$calendar[5].";
    color: ".$calendar[8].";
    background-color: ".$calendar[11].";
    text-align: center;
}
select.cal { /* mont and year selection */
    font: ".$calendar[15]." ".(empty($calendar[6]) ? 'inherit' : $calendar[6])." ".$calendar[5].";
    color: ".$calendar[8].";
    background-color: ".$calendar[11].";
    ".getBorder('1px', 'solid', $calendar[13])."
}
td.cals { /* week */
    font: ".$calendar[15]." ".(empty($calendar[6]) ? 'inherit' : $calendar[6])." ".$calendar[5].";
    color: ".$calendar[8].";
    background-color: ".$calendar[17].";".$css9."
    width: 14%;
    text-align: center;
}
td.cal0 { /* empty cell*/
    font: ".$calendar[15]." ".(empty($calendar[6]) ? 'inherit' : $calendar[6])." ".$calendar[5].";
    color: ".$calendar[7].";".$css10."
    width: 14%;
    text-align: center;
}
td.cal1 { /* days */
    font: ".$calendar[15]." ".(empty($calendar[6]) ? 'inherit' : $calendar[6])." ".$calendar[5].";
    color: ".$calendar[7].";
    background-color: ".$calendar[1].";
    width: 14%;
    text-align: center;
}
td.cal2 { /* today */
    font: ".$calendar[15]." ".(empty($calendar[6]) ? 'inherit' : $calendar[6])." ".$calendar[5].";
    color: ".$calendar[18].";
    background-color: ".$calendar[2].";
    ".getBorder($calendar[12], 'solid', $calendar[13])."
    border-radius: ".$calendar[14].";
    width: 14%;
    text-align: center;
}
td.cal3 { /* sunday or special */
    font: ".$calendar[15]." ".(empty($calendar[6]) ? 'inherit' : $calendar[6])." ".$calendar[5].";
    color: ".$calendar[19].";
    background-color: ".$calendar[3].";
    ".getBorder($calendar[12], 'solid', $calendar[13])."
    border-radius: ".$calendar[14].";
    width: 14%;
    text-align: center;
}
td.cal4 { /* today is sunday or special */
    font: ".$calendar[15]." ".(empty($calendar[6]) ? 'inherit' : $calendar[6])." ".$calendar[5].";
    color: ".$calendar[20]." !important;
    background-color: ".$calendar[4]." !important;
    ".getBorder($calendar[12], 'solid', $calendar[13])."
    border-radius: ".$calendar[14].";
    width: 14%;
    text-align: center;
}
td.calevt { /* planner event */
    font: ".$calendar[15]." ".(empty($calendar[6]) ? 'inherit' : $calendar[6])." ".$calendar[5].";
    color: ".$calendar[8].";
    background-color: ".$calendar[10].";
    text-align: center;
    width: 14%;
}
td.calevt a { /* link of planner event */
    font: ".$calendar[15]." ".(empty($calendar[6]) ? 'inherit' : $calendar[6])." ".$calendar[5].";
    color: ".$calendar[16].";
}
.calsel select { margin: -8px 0 -8px !important; }
.leftNavCalW a.btn, .rightNavCalW a.btn, .cal3BlogW a.btn {
    ".getBorder($calendar[12], 'solid', $calendar[13])."
    border-radius: ".$calendar[14].";
}
.leftNavCalW i, .rightNavCalW i, .cal3BlogW i {
    vertical-align: middle;
    font-size: 2.6rem;
    color: ".$calendar[7].";
}
.midNavCalW { vertical-align: middle !important; }
.mnthyearCalW { max-height: 22px; margin: -3px 0; }
.selYearCal { width: 72px; }
.selMonthCal { width: 98px; }
.mnthyearBtnCal { max-height: 22px; margin-top: 4px; }
.calagd { color: ".$calendar[7]."; }
/***********************
 * Formulaires (Forms) */
form { /* formulaire */
    margin: 3px 0;
}
fieldset { /* Encadrement des champs */
    ".getBorder($presform[7], $bordure[38], $presform[8])."
    border-radius: ".$presform[9].";
    margin: 8px;
    padding: 4px;
}
legend { /* légende des encadrements */
    font: ".$page[36]." ".$page[2]." ".$page[1]." !important;
    color: ".$texte[0]." !important;
    background-color: transparent !important;
    font-weight: bold !important;
    width: auto !important;
    padding: 0 4px !important;
    margin-left: 5% !important;
}
input.bouton, button { /* bouton */
    cursor: pointer;
    margin: 4px 0;
    padding: ".(empty($presform[12]) ? 0 : $presform[12]).";
    ".getBorder('1px', 'inset', $presform[8])."
    border-radius: ".$presform[9].";
    background-color: ".$presform[2].";
    color: ".$presform[11].";
    font: ".$presform[10]." ".(empty($presform[5]) ? 'inherit' : $presform[5])." ".$presform[4].";".$css11."
}
input.bouton:hover, button:hover { /* bouton survolé */
    background-color: ".$presform[13].";
    color: ".$presform[14].";
    ".getBorder('1px', 'inset', $presform[14])."
}
input.texte { /* champ texte */
    background-color: ".$presform[3].";
    font: ".$presform[10]." ".(empty($presform[5]) ? 'inherit' : $presform[5])." ".$presform[4].";
    color: ".$presform[0].";
    ".getBorder($presform[7], $bordure[38], $presform[8], true)."
    border-radius: ".$presform[9].";
    margin-top: 4px !important;
    margin-bottom: 4px !important;
}
select { /* champ select */
    color: ".$presform[0].";
    background-color: ".$presform[1].";
    font: ".$presform[10]." ".(empty($presform[5]) ? 'inherit' : $presform[5])." ".$presform[4].";
    ".getBorder($presform[7], $bordure[38], $presform[8])."
    border-radius: ".$presform[9]." !important;
    margin: 4px 0;
}
textarea { /* champ textarea */
    color: ".$presform[0].";
    background-color: ".$presform[6].";
    ".getBorder($presform[7], $bordure[38], $presform[8])."
    border-radius: ".$presform[9].";
    overflow: auto;
    margin: 4px 0;
}
/***********************************
 * boîtes centrales forum - tables */
.d-tbl {
    border-collapse: separate;
    border-spacing: 2px;
    width: 100%;
    max-width: 100%;
}
.forum, .forum a {
    background-color: ".$forum[0].";
    padding: 5px 5px;
    font: bold ".$page[2]." ".$page[1].";
    color: ".$forum[11].";
    text-align: center;
    vertical-align: middle;
}
.forum2 {
    background-color: ".$forum[0].";
    padding: 5px 5px;
    font: ".$page[36]." ".$page[2]." ".$page[1].";
    color: ".$forum[11].";
    text-align: left;
}
.d-tblth {
    vertical-align: middle;
    padding: 0px 4px;
    text-align: center;
    font-weight: bold;
}
.d-trhfr {
    height: 34px;
    background-color: ".$forum[0].";
    padding: 5px 5px;
    font: ".$forum[14]." ".(empty($forum[12]) ? 'inherit' : $forum[12])." ".$forum[13].";
    color: ".$forum[11].";
    text-align: left;
}
.bottomfrt {
    padding: 6px 4px 0px;
    margin: 8px auto;
    max-width: 100%;
    text-align: left;
    vertical-align: middle;
}
.frtrules {
    margin: 5px 40px;
    height: 300px;
    overflow: auto;
}
.quest {
    background-color: ".$forum[1].";
    padding: 5px 5px 5px 5px;
    font: ".$page[36]." ".$page[2]." ".$page[1].";
    color: ".$texte[0].";
    text-align: left;
}
.rep {
    background-color: ".$forum[2].";
    padding: 10px 5px 5px 5px;
    font: ".$page[36]." ".$page[2]." ".$page[1].";
    color: ".$texte[0].";
    text-align: left;
}
.rep > p { background: transparent; }
.bord {
    padding: 1px;
    ".getBorder($barre[4], $barre[5], $barre[3])."
}
.bord2 { ".getBorder('2px', 'double', $forum[2])." }
.Code {
    ".getBorder('1px', 'solid', $forum[0])."
    padding-right: 5px;
    padding-left: 5px;
    color: ".$texte[5].";
    font: 12px ".(empty($titre[2]) ? 'inherit' : $titre[2]).", Monospace;
    background-color: ".$forum[2].";
}
";
$csslist .= "
/************************
 * Divers, inclassables */
.divnewline { min-height: 24px; }
.notice { 
    color: #FF0000;
    background-color: #FF9;
    padding: 2px;
}
.accookie {
    background-color: ".$other[28].";
    color: ".$other[29].";
    font-size: ".$other[30].";
    z-index: 1000;
    top: 0;
    width: 100%;
}
.accookie a {
    color: ".$other[29].";
    font-style: italic;
}
.accookie button { font-size: ".$other[30]."; }
.errorInputText {
    background-color: #FFE5E5;
    font: ".$presform[10]." ".(empty($presform[5]) ? 'inherit' : $presform[5])." ".$presform[4].";
    color: #000099;
    ".getBorder($presform[7], $bordure[38], $presform[8])."
    border-radius: ".$presform[9].";
    margin: 4px 0;
    padding: 0px 2px;
}
.c-pointer { cursor: pointer; }
.c-help { cursor: help; }
.textUnderline { text-decoration: underline; }
.popupMail { margin: 8px 0; }
.dispError {
    max-width: 480px;
    margin: auto;
    background-color: #FFF;
}
.dispError2 {
    color: #FFF;
    background-color: #FF0000;
    text-align: center;
    font-size: 10pt;
    font-weight: bold;
    padding: 2px 0;
}
.dispArrow {
    float: right;
    font-family: fontello;
    font-size: 13px;
    padding: 3px 6px 0 0;
}
.w-98 { width: 98% !important; }
.w-95 { width: 95% !important; }
.w-90 { width: 90% !important; }
.w-88 { width: 88% !important; }
.w-85 { width: 85% !important; }
.w-80 { width: 80% !important; }
.w-78 { width: 78% !important; }
.w-75 { width: 75% !important; }
.w-70 { width: 70% !important; }
.w-65 { width: 65% !important; }
.w-60 { width: 60% !important; }
.w-55 { width: 55% !important; }
.w-48 { width: 48% !important; }
.w-45 { width: 45% !important; }
.w-40 { width: 40% !important; }
.w-35 { width: 35% !important; }
.w-30 { width: 30% !important; }
.w-22 { width: 22% !important; }
.w-20 { width: 20% !important; }
.w-18 { width: 18% !important; }
.w-15 { width: 15% !important; }
.w-13 { width: 13% !important; }
.w-12 { width: 12% !important; }
.w-10 { width: 10% !important; }
.web342 { margin: 15px 0 35px; }
.bg-inherit { background: inherit !important; }
.boxCryptW { margin: 8px 0 0; }
.fontXsmall { font-size: x-small; }
.showprogbar {
    z-index: 50;
    position: absolute;
    left: 33%; top: 360px;
    width: 300px;
    padding: 20px;
    border-width: 4px;
    border-style: inset;
}
.workprogress {
    padding: 10px;
    border: 3px solid #FF0000;
    font-size: larger;
}
.loadingpage {
    position: absolute;
    left: 20%; top: 28px;
    width: 240px;
    padding: 5px;
    border-width: 1px;
    border-style: solid;
    background-color: #EEEEFF;
}
.trheadsearch { background-color: ".$forum[1]."; }
.tr2headsearch { padding: 4px; background-color: ".$forum[2]."; }
.txthiddensearch { overflow: auto; height: 300px; }
.inputcrypt { width: 84px;}
/***************************
 * Evaluation mot de passe */
.alert {
    background-color: #FCF8E3;
    ".getBorder($presform[7], $bordure[38], $presform[8])."
    border-radius: ".$presform[9].";
    margin: 4px 0;
    padding: 2px;
    color: ".$presform[0].";
    text-align: center;
    font-weight: bold;
}
.alert-error {
    color: #FFFFFF;
    background-color: #FF0000;
    border-color: #EED3D7;
}
.alert-success {
    color: #000000;
    background-color: #00FF00;
    border-color: #D6E9C6;
}
.alert-info {
    color: #FFFFFF;
    background-color: #009DFF;
    border-color: #BCE8F1;
}
/*******
 * Rss */
.rssitem { padding: 6px; }
.text1newsrss, .text2newsrss {
    margin: 2px auto;
    padding: 10px 5px 5px;
}
.datitemrss { color: #999; }
.pubdateRss { color: #999; }
/***********************
 * Postguest - Editeur */
.pg280 { max-width: 280px; }
.pg360 { max-width: 360px; }
.pgrbra {
    height: 160px;
    overflow: auto;
    border-style: groove;
}
.pgc1 {
    height: 160px;
    max-width: 560px;
    overflow: auto;
    border-style: groove;
}
.pgptxt { padding: 5px }
.pgtbl .input-group-text {
	font-size: 1.6rem;
	height: auto;
	margin-top: 4px;
}
blockquote.citation {
    border: ".$other[10]." ".$other[11]." ".$other[9]." !important;
    border-left-width: 4px !important;
}
blockquote.citation > :first-child {
    background-color: ".hex2rgba($other[12], $opacity[22])." !important;
    color: ".$other[13]." !important;
}
blockquote.citation div { font-style: italic !important; }
.hljs {
    background-color: ".hex2rgba($other[23], $opacity[23])." !important;
    margin: 0 8px;
    padding: 6px !important;
    border: ".$other[10]." ".$other[11]." ".$other[9]." !important;
    border-left-width: 4px !important;
}
/*********************
 * Boîte Préférences */
.userIdentW {
    margin: -4px auto 0;
    width: 240px;
    max-width: 90%;
    height: 25px;
}
.user-eye i { font-size: 1.4rem; }
.userLostA { font-size:0.9em; }
.userWave object { width: 0; height: 0; }
.visits {
    position: absolute;
    margin: -210px 0 0 24px;
    padding: 4px;
    width: 190px;
    background: ".$other[1].";
    color: ".$other[0].";
    ".getBorder($other[3], 'solid', $other[2])."
    border-radius: ".$other[4].";
    display: none;
    z-index: 15;
}
.visits2 {
    max-height: 200px;
    overflow: auto;
    background: transparent;
    text-align: left;
}
.visits2 img, .visits2 i {
    vertical-align: middle;
    height: 32px !important;
    padding: 1px 2px;
}
.visitsClose {
    margin: 8px 6px 0;
    font-family: fontello;
    font-size: 1.6rem;
}
.ava-img, .img-ava {
    margin-bottom: -6px;
    width: 32px !important;
    height: 32px !important;
}
.img-ava { margin-bottom: 0; }
.ava-th {
    width: 64px !important;
    height: 64px !important;
}
.connectedList { margin-top: 6px; }
.connectedImg { margin: 0 2px; }
.connectedUserW { margin-bottom: 2px; }
.userprefIP { margin: 10px; }
.itemRssBoxW { margin: 0; }
.userList, .userVisits { line-height: 2em; }
.userList i, .userVisits i { font-size: 2rem; }
#BottomBoxes .userList a, #BottomBoxes .userVisits a, #BottomBoxes .userLostA,
#BottomBoxes .connectedList, #BottomBoxes .userprefIP a {
    color: ".$titre[13]." !important;
}
/*************************
 * Boîte Infos Webmestre */
.webm { padding-left: 4px; }
.webm td { color: inherit; }
.webmlh { line-height: 1rem; }
.webmlh i {	font-size: 2rem; }
#BottomBoxes .webm td { color: ".$titre[13]." !important; }
/***********************
 * Barre de navigation */
.navbarLeftW i.cur, .navbarRightW i.cur { font-size: 2rem; margin-top: 5px; }
.navbarLeftW i.curN, .navbarRightW i.curN { font-size: 2rem; margin-top: 5px; opacity: 0.5; }
.navbarLeftW { padding: 5px; }
.imgPlus { padding-left: 5px; }
.navbarRightW { padding: 5px; }
/*************
 * Guestbook */
.gbkb1W { padding-top: 6px; }
.gbkc1 { padding-bottom: 24px; }
/**********
 * Agenda */
.calagvL {
    margin: 1px;
    min-width: 60px;
    padding-top: 10px;
}
.calagvR { margin: 1px; }
.calendarg, .calendard {
    margin: 1px;
    padding: 4px;
}
.calimggW, .calimgdW {
    width: 14px;
    height: 11px;
}
";
$csslist .= "
/*********
 * Forum */
.membTopForumWW { padding-top: 12px; }
.seluserTopForum { width: 128px; }
.mailsubmitTopForum { padding-left: 5px; }
.frtlnkarch img { vertical-align: -4px; }
.frtlnkrules { margin-top: -8px; }
.frlnkcategW { margin-left: 18px; margin-bottom: 18px; }
.frbottom { padding: 16px 0 4px; }
.frimgsbottom { padding: 4px; }
.frretfrt {
    width: 250px;
    margin-top: -8px;
    padding-top: 2px;
}
.thlnkforumW { margin-left: 8px; }
.thlnkcateg { margin-left: 18px; }
.thhead { margin: 0 0 4px; }
.thheadsubjectW {
    height: 32px;
    background-color: ".$forum[0].";
}
.thheadbodyW {
    word-break: break-all;
    height: 32px;
    background-color: ".$forum[0].";
}
.thbody { margin: 0 0 6px; }
.thtimetop {
    z-index: 15;
    border-width: 1px;
    border-style: solid;
    border-radius: 8px;
    width: 260px;
}
.thmoveto {
    z-index: 15;
    border-width: 1px;
    border-style: solid;
    border-radius: 8px;
    width: 220px;
}
.thtocat { width: 140px; }
.thbodybottom { height: 28px; }
.thcoders { padding-top: 4px; }
.thmsgicons { padding: 4px; margin: 4px auto 0 0; }
.frnoaccess { padding-bottom: 48px; }
.frarchsubject {
    min-width: 148px;
    margin: 1px;
    white-space: nowrap;
}
.frarchbody { margin: 1px; }
.tharchmain { margin-bottom: 8px; }
.tharchsubject { max-width: 160px; }
.thsign { padding-top: 8px; }
.bodyfrposts {
    margin: 0px;
    padding: 0px;
    background-image: none;
    border: 0px;
}
.mainfrposts, .mainrules {
    padding: 5px;
    margin: 2px;
}
/*****************
 * Lettre d'info */
.nwlSubscribe .input-group-text {
	font-size: 1.75rem;
	height: auto;
	margin-top: 4px !important;
	margin-bottom: 4px !important;
}
.nwlMailW { margin-top: 0px; }
.nwlActionW { padding-left: 35px; }
.nwlunsub { margin: 12px 0; }
.nwlcrypt { margin-top: 8px; }
.nwlyesbtn { margin-right: 6px; }
.nwlnobtn { margin-left: 6px; }
/*******************
 * Réseaux sociaux */
.rssSocnet { padding: 0 2px; }
.iconSocnetW { margin: auto; padding: 6px 2px; }
/*************
 * Nouvelles */
.codersNews { padding: 12px 0 4px; }
.archNews { padding: 0 0 12px; }
.archNews img { vertical-align: -4px; }
.postNews { margin: 0 64px 2px 0; }
.editoNews { margin-top: 4px; padding: 2px; }
.txt2News { margin-top: 4px; padding: 10px 5px 5px; }
.headNews { margin: 2px 0 0; }
.rssNews { width: auto; }
.overpop .rssNews i, .itemrss a i { font-size: 2.5rem; color: #FF6701; }
/**********************
 * Nouveautés du site */
.itemLastdoc { margin: 8px 0 16px; }
.lastdoc { margin: 0px; padding: 0 5px 5px }
.lastdoc2 { margin: 0px; padding: 0 5px 5px; }
.lastdoc a, .lastdoc2 a { font-weight: bold; }
#BottomBoxes .lastdoc legend, #BottomBoxes .lastdoc2 legend,
#BottomBoxes .lastdoc a, #BottomBoxes .lastdoc2 a {
    color: ".$titre[13]." !important;
}
/***********
 * Contact */
.itemContactW { margin: 4px 0 0; }
.imgContact { margin: 0 8px -4px 12px; }
.dispMail{ padding: 8px; }
.dispMailBody {
    width: 90%;
    padding: 8px;
    margin: 0 auto;
}
.headContactW {
    text-align: left;
    font-size: 1.6rem;
}
.nameContactR { padding-left: 22px; }
.mailContactL { padding: 0 0 16px; }
.mailContactR { padding: 0 0 16px 22px; }
.mainct .input-group-text {
	font-size: 1.6rem;
	height: auto;
	margin-top: 4px !important;
	margin-bottom: 4px !important;
}
.maxWidthContact { max-width: 560px; }
.userCnct { max-width: 368px; }
";
$csslist .= "
/********
 * Blog */
.selBlogCat { width: 166px; }
.dispqcBlogCat { margin-bottom: 8px; }
.itemBlogBoxW, .itemBlogBoxW a { padding: 3px 6px; color: ".$lien[2]."; }
.cal3Blog { white-space: nowrap; }
.calButton { min-width: 0px; }
.itemRssBlog { padding: 3px; }
.rssBlog { margin-bottom: 8px; }
.overpop .rssBlog i { font-size: 2.5rem; color: #FF6701; }
.boxreBlog2 { padding: 4px; }
.headCenterBlog { margin: 2px 0 0; }
.postBlog { margin: 0 20px; }
.txt2Blog { padding: 4px }
.blogBlog { padding: 5px; }
.blog2Blog { margin-top: 4px; padding: 10px 5px 5px; }
.blog3Blog { margin-top: 4px; padding: 10px 5px 5px; }
.codersBlog { padding: 6px 0 0 10px; }
.footerItemBlog { padding: 6px 4px 2px; font-size: 0.9em; }
.commentBlog { font-size: smaller; }
.qcItemBlog { margin: -4px 0 20px; }
.messErrorBlog { padding: 6px; }
.blogarch { padding: 4px; }
.titleArchBlog { margin: 8px 0; }
.bordTextArch { margin-top: 4px; }
.textArchBlog { padding: 4px; }
.actionArchBlog { padding: 2px 4px; font-size: smaller; }
.pubArchBlog { padding: 2px 4px; font-size: smaller; }
.titreBlog { margin: 8px 0px; }
.bordBillet { margin-top: 4px; }
.billetBlog { padding: 4px; }
.footerBillet { padding: 4px 4px 0 0; font-size: smaller; }
.leftRssBlog, .rightRssBlog { padding: 4px; }
.text2RssBlog { margin: 0; padding: 2px; }
.textRssBlog { margin: 2px; padding: 10px 5px 5px; }
.buttonTopBlog { padding: 6px 4px 2px; }
.membersTopBlog { padding-top: 10px; }
.searchTopBlog .input-group-text {
	font-size: 1.6rem;
	height: auto;
	margin-top: 4px !important;
    margin-bottom: 4px !important;
}
.lnkblarch { padding: 8px 4px 0; }
.blog3arch { padding: 2px; }
.txt3arch { margin: 2px; padding: 5px; }
.txt2blogarch { padding: 5px; }
.headblogarch { padding: 6px 4px 2px; font-size: 0.9em; }
/************
 * Articles */
.selArtNavbar { width: 200px; }
.overpop .rssArt i { font-size: 2.5rem; color: #FF6701; }
.contentArt { padding: 6px; }
.codersArt { padding: 4px 0 10px 30px; }
.propArtW { font-size: smaller; }
.navbarArt, .prtArt { margin: 4px; }
.headreactleft { padding: 4px; }
.artNavP i, .artNavS i { font-size: 2.4rem; }
/***********
 * Membres */
.user-form { max-width: 920px; margin: auto; }
.afterlost { margin: 32px; }
.mainConnect { width: 320px; }
.lostconnect { font-size: 0.9em; }
.prfAdmButton { max-width: 500px; }
.prftblborder { ".getBorder('1px', 'solid', '#000')." }
.prfheadtbl { padding: 16px; }
.prfava { max-height: 80px; }
.prfwebsite { padding: 24px 0; }
.prfimgmail { height: 24px; }
.mbrlistmain { margin-top: 8px; }
.mbrlistimgava { height: 32px !important; }
.mbrlistimg { height: 24px; }
.cmptlbl { margin: 2px; padding: 10px 4px; }
.popsyncmail { width: 32px; }
.popsyncmail img { margin: -6px 0 0 0; }
.pop.popsyncmail a span { left: -270px; top: 20px; }
.pop.popsyncmail a span em { left:auto; right: 12px; }
.cmptsyncmail { margin: -6px 2px 0 16px; }
.tbltinysend { width: 100%; max-width: 540px; }
.tdrepmsg {
    height: 80px;
    max-width: 480px;
    overflow: auto;
    border-width: 1px;
    border-style: solid;
}
.msgtinysend { max-width: 500px; }
.tbltinymain { max-width: 460px; }
.trmsgtiny { padding: 8px 0; }
.tdmsglnk { padding: 8px 4px; }
.btntinymain { width: 460px; padding: 16px 0 0; }
.divcmdtiny { margin: 0 auto; width:100%; max-width: 340px; }
.frmcmdtiny { margin: 0; padding: 0; }
.divreptiny { padding: 4px; margin-top: 8px; }
.divuseritem { margin: 2px; }
.divuserpwd { margin: 8px 2px 2px; }
.popuserpwd { width: auto; margin-left: 6px; }
.popusersign { width: auto; margin-left: 6px; }
.usermainava { margin: 16px auto auto; }
.divuserava { max-height: 380px; overflow: auto; }
.divuseravaextW { max-width: 430px; margin: 12px auto 0px; }
.userextavaW { width: 100%; }
.popavaext { width: 32px; }
.tbluseravaW { width: 420px; max-width: 60%; }
#returnOnTop {
    background: url(img/return_on_top.png);
    cursor: pointer;
    display: none;
    filter: alpha(opacity = 50);
    height: 48px;
    width: 48px;
    position: fixed;
    right: 1%;
}
#returnOnTop:hover {
    filter: alpha(opacity = 100);
    opacity: 1;
}
.cryptpos {
	max-width: 480px;
	width: 90%;
	line-height: 18px;
}
.inputuid {
    width: 220px;
    max-width: 100%;
    margin-right: -2px;
}
";
$csslist .= "
/********************
 * Menus déroulants */
/* mega menu */
@media screen and (min-width: 760px) {
  .navbar.ddl {
    display: flex;
    padding-top: 0px;
    padding-bottom: 0px;
  }
  .navbar.ddl .nav-item {
    padding: .5rem .5rem;
    margin: 0 .25rem;
  }
  .navbar.ddl .dropdown { position: static; }
  .navbar.ddl .dropdown-menu {
    width: 100%;
    left: 0;
    right: 0;
    top: 45px;
    display: block;
    visibility: hidden;
    opacity: 0;
    transition: visibility 0s, opacity 0.3s linear;
  }
  .navbar.ddl .dropdown:hover .dropdown-menu, .navbar.ddl .dropdown .dropdown-menu:hover {
    display: block;
    visibility: visible;
    opacity: 1;
    transition: visibility 0s, opacity 0.3s linear;
  }
}
.navbar.ddl .dropdown-menu div[class*=\"col\"] {
    margin-bottom: 1rem;
}
.navbar.ddl a.navbar-brand, .navbar.ddh a.navbar-brand, 
.navbar ul.navbar-nav a.nav-link, .nav-link {
    font: ".$dropdown[2]." ".(empty($dropdown[3]) ? 'inherit' : $dropdown[3])." ".$dropdown[1].";
    background-color: ".$dropdown[5].";
    color: ".hex2rgba($dropdown[4], 1).";
}
.navbar.ddl a.navbar-brand:hover, .navbar ul.navbar-nav a.nav-link:hover,
.navbar.ddl a.navbar-brand:focus, .navbar.ddh a.navbar-brand:hover, 
.navbar ul.navbar-nav a.nav-link:focus {
    font: ".$dropdown[2]." ".(empty($dropdown[3]) ? 'inherit' : $dropdown[3])." ".$dropdown[1].";
    background-color: ".hex2rgba($dropdown[27], 1).";
    color: ".hex2rgba($dropdown[29], 1).";
}
.navbar.ddl {
	background-color: transparent;
    color: ".hex2rgba($dropdown[4], 1).";
}
.navbar.ddl .dropdown-menu {
    margin-top: -.45rem !important;
    width: ".$dropdown[17].";
    height: ".$dropdown[18].";
    background-color: ".$dropdown[19]." !important;".$css35."
}
.navbar.ddl .dropdown-menu > div, .navbar.ddl .dropdown-menu > div p {
    font-size: 1.6rem;
    color: ".$dropdown[26].";
}
.navbar .navbar-toggler, .navbar.ddl .navbar-toggler, .navbar.ddh .navbar-toggler {  /* bordure icône de la barre en mobile */
    border-color: ".hex2rgba($dropdown[4], 0.9).";
    border-radius: 0.35rem;
}
.navbar.ddl .navbar-toggler-icon { /* couleur texte icône de la barre en mobile (stroke) */
	width: 2em !important;
	height: 2em !important;
	background-image: url(\"data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'30\' height=\'30\' viewBox=\'0 0 448 512\' %3e%3cpath stroke=\'".hex2rgba($lien[4], 0.9)."\' stroke-linecap=\'round\' stroke-miterlimit=\'10\' stroke-width=\'4\' d=\'M16 132h416c8.8 0 16-7.2 16-16V76c0-8.8-7.2-16-16-16H16C7.2 60 0 67.2 0 76v40c0 8.8 7.2 16 16 16zm0 160h416c8.8 0 16-7.2 16-16v-40c0-8.8-7.2-16-16-16H16c-8.8 0-16 7.2-16 16v40c0 8.8 7.2 16 16 16zm0 160h416c8.8 0 16-7.2 16-16v-40c0-8.8-7.2-16-16-16H16c-8.8 0-16 7.2-16 16v40c0 8.8 7.2 16 16 16z\'/%3e%3c/svg%3e\") !important;
}
.navbar.ddh .btn-ddh, .navbar.ddh .nav-item a, 
.navbar.ddh .dropdown-menu > .dropdown-item a,
.navbar.ddv .btn-ddv, .navbar.ddv .nav-item a, 
.navbar.ddv .dropdown-menu > .dropdown-item a {
    font-size: ".$dropdown[3].";
    color: ".$dropdown[25].";
}
.navbar.ddh .nav-item a:hover, .navbar.ddh .dropdown-menu > dropdown-item a:hover,
.navbar.ddv .nav-item a:hover, .navbar.ddv .dropdown-menu > dropdown-item a:hover {
    color: ".$dropdown[29].";
}
.navbar.ddh .navbar-toggler-icon { /* couleur texte icône de la barre en mobile (stroke) */
	width: 2em !important;
	height: 2em !important;
	background-image: url(\"data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'30\' height=\'30\' viewBox=\'0 0 448 512\' %3e%3cpath stroke=\'".hex2rgba($lien[4], 0.9)."\' stroke-linecap=\'round\' stroke-miterlimit=\'10\' stroke-width=\'4\' d=\'M16 132h416c8.8 0 16-7.2 16-16V76c0-8.8-7.2-16-16-16H16C7.2 60 0 67.2 0 76v40c0 8.8 7.2 16 16 16zm0 160h416c8.8 0 16-7.2 16-16v-40c0-8.8-7.2-16-16-16H16c-8.8 0-16 7.2-16 16v40c0 8.8 7.2 16 16 16zm0 160h416c8.8 0 16-7.2 16-16v-40c0-8.8-7.2-16-16-16H16c-8.8 0-16 7.2-16 16v40c0 8.8 7.2 16 16 16z\'/%3e%3c/svg%3e\") !important;
}
/*****************
 * Dropdown Menu */
.dropdown > .dropdown-menu {
  top: 102%;
  transition: 0.3s all ease-in-out;
}
.dropdown:hover > .dropdown-menu {
  display: block;
  top: 92%;
}
.ddh.dropdown-menu, .ddv.dropdown-menu { left: 1rem; }
.ddv.dropdown-menu { position: absolute; }
.navbar.ddv ul.navbar-nav { margin-top: -20px; }
.navbar.ddh .dropdown-item, .navbar.ddh .nav-item,
.navbar.ddv .dropdown-item, .navbar.ddv .nav-item {
    margin-left: 0;
    font: ".$dropdown[2]." ".(empty($dropdown[3]) ? 'inherit' : $dropdown[3])." ".$dropdown[1].";
    background-color: ".$dropdown[5].";
    color: ".$dropdown[4].";
}
.navbar.ddh .dropdown-item:hover, .navbar.ddh .nav-item:hover,
.navbar.ddv .dropdown-item:hover, .navbar.ddv .nav-item:hover,
.navbar.ddv .dropdown-item a:hover, .navbar.ddv .nav-item a:hover,
li .dropdown-item a:hover, li .nav-item a:hover {
    font-weight: ".$dropdown[2].";
    background-color: ".$dropdown[27].";
    color: ".$dropdown[29].";
}
.ddm {
    padding: 0;
    background-color: transparent;
}
.btn-ddh, .btn-ddv {
    display: inline-block;
    margin-top: 0 !important;
    border-radius: .25rem;
}
@media screen and (max-width: 760px) {
  .btn-ddh, .btn-ddv {
      display: block;
      margin-top: 4px !important;
  }
}
.ddmImg {
    padding-right: 6px;
    vertical-align: top;
    max-height: 16px;
}
";
$csslist .= "
/*************
 * Mots-clés */
ul.keyword {
    list-style: none;
    margin: -12px 0 4px; 
    padding: 0;
    position: relative;
    float: left;
}
ul.keyword li {
    margin: 6px 0 0;
    padding: 0px 6px;
    background-color: ".hex2rgba($texte[1], $opacity[10]).";
    color: inherit;
    list-style: none;
    float: left;
    line-height: 2.0em;
    vertical-align: bottom;
}
ul.keyword li:hover {
    color: inherit;
    position: relative;
    z-index: 599;
    cursor: pointer;
}
ul.keyword ul {
    width: 180px;
    list-style: none;
    margin: 0;
    padding: 0;
    visibility: hidden;
    position: absolute;
    top: 75%;
    left: 0;
    z-index: 598;
}
ul.keyword-horizontal ul li *.dir {
    padding-right: 20px;
}
ul.keyword li:hover > ul {
    transition-property : visibility;
    transition-delay : 300ms;
    visibility: visible;
}
ul.keyword div {
    ".getBorder('1px', 'solid', $dropdown[7])."
    border-radius: 4px;
    background-color: ".$dropdown[5].";
    padding: 4px 8px;
    line-height: 1.3em;
    color: ".$dropdown[4].";
}
.keyword0 { /* nombre d'occurrences nulle */
    font-size : 0.8em;
}
.keyword1 { /* entre 1 et 3 */ 
    font-size : 1.2em;
}
.keyword2 { /* entre 4 et 5 */
    font-size : 1.6em;
}
.keyword3 { /* plus de 5 */
    font-size : 2.0em;
}
/************************
 * Cryptogrammes texte */
#cryptogram0, #cryptogram1, #cryptogram2 {
    margin: 6px auto;
    padding: 2px;
    font-family: Arial;
    font-weight: bold; 
    font-size: 16px;
    background-color: ".$other[18].";
    color: ".$other[19].";
    ".getBorder($other[21], 'solid', $other[20], true)."
    border-radius: ".$other[22].";
    text-align: center;
    box-sizing: content-box;
}

/*********************************** 
 * déclarations générales mobiles *
 ***********************************/
 
@media screen and (max-width: 1079.98px) {
  body { padding: 0 !important; }
  #LeftBoxes, #BlogLeftBoxes { order: 2 !important; max-width: 100% !important; }
  #CenterBoxes { order: 1 !important; max-width: 100% !important; }
  #RightBoxes, #BlogRightBoxes { order: 3 !important; max-width: 100% !important; }  
  #returnOnTop { display: none !important; }
  .iconSocnetW { display:none; }
  div.quote { display:none }
  .tbl > p, .tbl > div { line-height: 3.2rem; }
  .a-col { padding-left: 0 !important; padding-right: 0 !important; }
  .tbluseravaW i { width: 48px !important; height: 48px !important; }
  fieldset { margin: 0 !important; }
  .nav-link .dropdown-toggle { padding:.5rem 1rem; }
  #MainContent { margin:10px 0 0;padding:10px 0!important;border:0!important; }
  .B0entrow1 { padding:10px 2px!important; }
  #BottomBoxes .tbl,#BottomBoxes .tblbox,#BottomBoxes .titre,#BottomBoxes .titrebox { padding:10px 2px!important; }
}
@media screen and (min-width: 768px) and (max-width: 1079.98px) {
  #MainContent .col-md-9, #MainContent .col-md-6 {
    padding: 0 30px !important;
    -ms-flex: 0 0 100% !important; flex: 0 0 100% !important; max-width: 100% !important;
  } 
  #MainContent .col-md-3 {
	padding: 0 30px !important;
	-ms-flex: 0 0 100% !important; flex: 0 0 100% !important; max-width: 100% !important;
	margin: 5px auto !important;
  }
}
";
	
    SetChmod(CHEMIN.'skins/'.$mycss.'/style.css');
    $csslist = str_replace('\'', '"', $csslist);
    WriteFullDB(CHEMIN.'skins/'.$mycss.'/style.css', $csslist);
/*
    Ajout fond de couleur 	pour tarteaucitron attention aux lignes $other
*/
if (is_file ('inc/conftarteaucitron.inc')) {
    include 'inc/conftarteaucitron.inc';
}
/* Fin pour tarteaucitron */
    /***************************************************************
     * Construction de la feuille CSS de personnalisation Bootstrap*
     ***************************************************************/
    $csslist = "/*  ---------  CSS for Bootstrap 4 generated for ".$mycss."  ----------- */

/***********
* Onglets */
.tab-content div.container {
    padding-top: 0px;
    max-width: 100%;
    text-align: left;
    ".getBorder($tabs[9], 'solid', $tabs[8])."
    border-radius: ".$tabs[10].";
    background-color: ".$tabs[7].";
}
ul.nav-tabs {
    padding-bottom: 0px;
    width: 100%;
    border-bottom: 0;
}
.nav-tabs .nav-link.active {
    background-color: ".$tabs[5].";
    font-weight: bold;
    color: ".$tabs[6].";
    ".getBorder($tabs[3], 'solid', $tabs[2])."    
    border-bottom-width: 0;
    border-radius: ".$tabs[4].";
}
.nav-tabs .nav-link:hover {
    background-color: ".$tabs[28]." !important;
    color: ".$tabs[29]." !important;
    ".getBorder($tabs[3], 'solid', $tabs[2])."
    border-bottom-width: 0;
    border-radius: ".$tabs[4].";
}
.nav-tabs .nav-link {
    font: ".(empty($page[40]) ? 'inherit' : trim($page[41]." ".$page[40]." ".$page[39])).";
    margin-bottom: 1px;
    background-color: ".$tabs[1].";
    color: ".$tabs[0]." !important;
}
/**********************
* Accordéon vertical */
.card .card-header.collapsed.card-link {
    color: ".$tabs[11]." !important;
    background-color: ".$tabs[12].";
    ".getBorder($tabs[14], 'solid', $tabs[13])."
    border-radius: ".$tabs[15].";
}
.card .card-header.card-link {
    color: ".$tabs[17].";
    font-weight: bold;
    background-color: ".$tabs[16].";
    ".getBorder($tabs[14], 'solid', $tabs[13])."
    border-radius: ".$tabs[15].";
}
.card .collapse.show {
    background-color: ".$tabs[18].";
    color: inherit;
    ".getBorder($tabs[20], 'solid', $tabs[19])."
    border-radius: ".$tabs[21].";
}
.card .card-body {
    font-size: 13px;
}
/*******************
 * Fenêtre modale  *
 *******************/
.close {
	  float: right !important;
	  font-size: 4rem !important;
	  line-height: 1 !important;
	  color: ".$presform[11]." !important;
	  text-shadow: 0 1px 0 #fff !important;
	  opacity: .5 !important;
}
.modal-content {
    background-color: ".$tabs[24]." !important;
    color: ".$tabs[23]." !important;
	border: ".$tabs[26]." ".$tabs[25]." !important;
    border-radius: ".$tabs[27]." !important;
}
.modal-header {
    background-color: ".$tabs[32]." !important;
    color: ".$tabs[33]." !important;
}
.modal-body {
    background-color: ".$tabs[34]." !important;
    color: ".$tabs[35]." !important;
}
.modal-footer {
    background-color: ".$tabs[36]." !important;
    color: ".$tabs[37]." !important;
}
/**************
* Info bulle */
.popover {
    color: ".$texte[0].";
    background-color: ".$other[14].";
    border-radius: 6px;
}
.popover-header {
    color: ".$titre[0].";
    background-color: ".$titre[1].";
    font: normal ".$page[2]." ".$page[1].";
}
.popover-body {
    color: ".$texte[0].";
    background-color: ".$other[14].";
    font: normal ".$page[2]." ".$page[1].";
    border-radius: 0 0 6px 6px;
}
/***********
* Boutons */
.btn-lg, .btn-md {
    font-size: 1.5rem;
    border-radius: ".$presform[9].";
}
.btn-info {
	color: ".$presform[11].";
	background-color: ".$presform[2].";
	border-color: ".$presform[2].";
    border-style: solid;
    font-size: 1.6rem;
}
.btn-info:hover {
    color: ".$presform[14].";
    background-color: ".$presform[13].";
	border-color: ".$presform[13].";
    border-style: solid;
}
.btn.btn-outline-secondary {
    background-color: ".$presform[3].";
}
/***************
* Formulaires */
.form-control, .form-control-lg {
    font-size: inherit;
    border-radius: 4px !important;
}
.input-group-prepend .input-group-text {
	border-radius: ".$presform[9].";
}
.input-group-append .input-group-text {
	border-radius: ".$presform[9].";
}
span.input-group-text {
    ".getBorder($presform[7], $bordure[38], $presform[8])."
    border-radius: 4px !important;
}
";
	
        SetChmod(CHEMIN.'skins/'.$mycss.'/jqstyle.css');
        $csslist = str_replace('\'', '"', $csslist);
        WriteFullDB(CHEMIN.'skins/'.$mycss.'/bs4style.css', $csslist);
        
        /**************************************************
         * Construction de la feuille CSS CKEditor public *
         **************************************************/
        $csslist = "/*  ---------  Paramètrage de CKEditor public  ----------- */

body {
	background: ".$other[24].";
	color: ".$other[25].";
	font-size: ".$other[26].";
}
";
	
        SetChmod(CHEMIN.'inc/ckeditor_config/guppy_contents_plus.css');
        $csslist = str_replace('\'', '"', $csslist);
        WriteFullDB(CHEMIN.'inc/ckeditor_config/guppy_contents_plus.css', $csslist);
    }
}

if ($regit == 3) {
	$nameskin = import('nameskin');
	if (preg_match('/^[a-z0-9_]{4,20}$/', $nameskin) && !is_dir(CHEMIN.'skins/'.$nameskin)) {
		recursiveCopy(CHEMIN.'skins/no_skin', CHEMIN.'skins/'.$nameskin);
		$selskin = $userprefs[10] = $page[14] = $nameskin;
	}
}

$skn  = isset($selskin) ? '&amp;selskin='.$selskin : '';
$img1 = $img2 = array();
$img0 = ExploreImg($meskin.'img/');
$n    = 0;
foreach($img0 as $img) { $img1['S'.$n.'|'] = $img; $n++; }
$img0 = ExploreImg('img/');
$n    = 0;
foreach($img0 as $img) { $img2['I'.$n.'|'] = $img; $n++; }
$imgs = array_merge($img1, $img2);

$rpts         = array('no-repeat'=>$admin[1604], 'repeat'=>'X + Y', 'repeat-x'=>'X', 'repeat-y'=>'Y');
$poss         = array('top', 'bottom', 'center');
$textalign    = array('left'=>$admin[390], 'center'=>$admin[391], 'right'=>$admin[392]);
$textstyles   = array(''=>$admin[423], 'bold'=>$admin[387], 'italic'=>$admin[388]);
$borderstyles = array('none'=>$admin[875], 'hidden'=>$admin[1556], 'solid'=>$admin[1501], 'dotted'=>$admin[1502], 'dashed'=>$admin[1503], 'double'=>$admin[1504], 'groove'=>$admin[1505], 'ridge'=>$admin[1506], 'inset'=>$admin[1507], 'outset'=>$admin[1508]);
$parallaxnav  = array('no'=>$admin[1604], 'left'=>$admin[494], 'right'=>$admin[495]);

$fontNames = array(
    "Arial, Helvetica, sans-serif",
    "Verdana, 'Bitstream Vera Sans', sans-serif",
    "'Trebuchet MS', Helvetica, sans-serif",
    "'Comic sans MS', Arial, sans-serif",
    "'Times New Roman', Times, serif",
    "Georgia, 'Times New Roman', serif",
    "'Courier New', Courier, monospace"
);
$fontNames = array_merge($fontNames, $afonts);

$headinc .= '
    <script type="text/javascript">
        $(document).ready( function() {
            $("#gyaccordion").hoverAccordion({
                keepHeight: false,
                activateItem: 1,
				onClickOnly: true,
                speed: 400
            });
            $("#gyaccordion").children("li:first").addClass("firstitem");
            $("#gyaccordion").children("li:last").addClass("lastitem");
        });
    </script>
';

$headinc .= '
<link rel="stylesheet" media="screen" type="text/css" href="../inc/colorpicker/css/colorpicker.css" /> 
<script type="text/javascript" src="../inc/colorpicker/js/colorpicker.js"></script>
';

function GetSelector($padding, $label, $inputname, $color) { // Palette de couleurs
	return '
        <tr style="text-align:left;">
          <td class="w-55 align-middle" style="padding:4px 0 2px '.$padding.'px;">&bull; '.$label.'</td>
		  <td style="padding:4px 0 2px 4px;">
			<div>
			  <input class="form-control" id="'.$inputname.'" name="'.$inputname.'" type="text" size="6" value="'.$color.'" 
                style="font:14px Arial;text-align:center;width:92px;height:29px;border:1px solid #000;border-radius:4px 0 0 4px;float:left;" />
			  <div style="float:left;width:40px;height:29px;vertical-align:top;border:1px solid #000;border-left:none;background-color:'.$color.';padding:-4px 0 4px 0;"></div>
			  <div id="'.$inputname.'div" style="float:left;width:40px;height:29px;border:1px solid #000;border-left:none;background-color:'.$color.';border-radius:0 4px 4px 0;">&nbsp;</div>
			</div>'.
			BeginJavascript()."
			$('#".$inputname."').ColorPicker({
				onSubmit: function(hsb, hex, rgb, el) {
					$(el).val('#' + hex.toUpperCase());
					$(el).ColorPickerHide();
				},
				onChange: function (hsb, hex, rgb) {
					$('#".$inputname."div').css('backgroundColor', '#' + hex);
				},
				onBeforeShow: function () {
					$(this).ColorPickerSetColor(this.value);
				}
			})
			.bind('keyup', function(){
				$(this).ColorPickerSetColor(this.value);
			});".EndJavascript().'
          </td>
        </tr>';
}

function SelectImgs($padding, $label, $name, $value, &$imgs) {
    global $admin;
    $out = '
        <tr style="text-align:left;">
          <td class="w-55 align-middle py-0 pr-0" style="padding-left:'.$padding.'px;">&bull; '.$label.'</td>
          <td style="padding:0 0 0 4px;">
            <select class="form-control" name="'.$name.'">
              <option value="">'.$admin[90].'</option>
              <optgroup label="SKIN">';
    $ok = true;
	foreach($imgs as $key => $img) {
        if (substr($key, 0, 1) != 'S' && $ok) {
            $out .= '
              </optgroup>
              <optgroup label="IMG">';
            $ok = false;
        }
        $out .= '
              <option value="'.$key.$img.'"'.Selected($value == $key.$img).'>'.$img.'</option>';
	}
	$out .= '
              </optgroup>
            </select>
          </td>
        </tr>';
    return $out;
}

function SelectRepeats($padding, $label, $name, $srpt, &$rpts) {
    $out = '
        <tr style="text-align:left;">
          <td class="w-55 align-middle py-0 pr-0" style="padding-left:'.$padding.'px;">&bull; '.$label.'</td>
          <td style="padding:0 0 0 4px;">
            <select class="form-control" name="'.$name.'">';
	foreach($rpts as $key => $rpt) {
		$out .= '
              <option value="'.$key.'"'.Selected($srpt == $key).'>'.$rpt.'</option>';
	}
	$out .= '
            </select>
          </td>
        </tr>';
    return $out;
}

function SelectBorderStyles($padding, $label, $name, $value, &$borderstyles) {
    $out = '
        <tr style="text-align:left;">
          <td class="align-middle py-0 pr-0" style="padding-left:'.$padding.'px;">&bull; '.$label.'</td>
          <td style="padding:0 0 0 4px;">
            <select class="form-control" name="'.$name.'">';
    foreach($borderstyles as $key => $style) {
        $out .= '
              <option value="'.$key.'"'.Selected($value == $key).'>'.$style.'</option>';
	}
	$out .= '
            </select>
          </td>
        </tr>';
    return $out;
}

function SelectPoss($padding, $label, $name, $value, &$poss) {
    $out = '
        <tr style="text-align:left;">
          <td class="align-middle py-0 pr-0" style="padding-left:'.$padding.'px;">&bull; '.$label.'</td>
          <td style="padding:0 0 0 4px;">
            <select class="form-control" name="'.$name.'">';
	foreach($poss as $pos) {
		$out .= '
              <option value="'.$pos.'"'.Selected($value == $pos).'>'.strtoupper($pos).'</option>';
	}
	$out .= '
            </select>
          </td>
        </tr>';
    return $out;
}

function SelectFontOptions($padding, $name, $value, &$fontNames) {
    global $admin;
    $out = '
        <tr style="text-align:left;">
          <td class="w-55 align-middle py-0 pr-0" style="padding-left:'.$padding.'px;">&bull; '.$admin[85].'</td>
		  <td style="padding:0 0 0 4px;">
            <select class="form-control" name="'.$name.'">';
    foreach ($fontNames as $font) {
        $out .= '
              <option value="'.$font.'"'.Selected($value == $font).'>'.$font.'</option>';
    }
    return $out.'
            </select>
          </td>
        </tr>';
}

function SelectTextStyles($padding, $name, $value, &$textstyles) {
    global $admin;
    $out = '
        <tr style="text-align:left;">
          <td class="w-55 align-middle py-0 pr-0" style="padding-left:'.$padding.'px;">&bull; '.$admin[1499].'</td>
          <td style="padding:0 0 0 4px;">
            <select class="form-control" name="'.$name.'">';
    foreach($textstyles as $key => $style) {
        $out .= '
              <option value="'.$key.'"'.Selected($value == $key).'>'.$style.'</option>';
	}
	$out .= '
            </select>
          </td>
        </tr>';
    return $out;
}

function InputTexte($padding, $label, $name, $value, $size) {
    return '
        <tr style="text-align:left;">
          <td class="w-55 align-middle py-0 pr-0" style="padding-left:'.$padding.'px;">&bull; '.$label.'</td>
		  <td style="padding:0 0 0 4px;">
            <input name="'.$name.'" class="form-control texte" style="width: '.($size * 12).'px;" type="text" size="'.$size.'" value="'.$value.'" />
          </td>
        </tr>';
}

function InputCheckbox($padding, $label, $name, $value) {
    return '
        <tr style="text-align:left;">
          <td style="padding:6px 0 2px '.$padding.'px;width:55%;">&bull; <label for="'.$name.'">'.$label.'</label></td>
          <td class="form-check" style="padding:8px 0 2px 4px;">
            <input class="form-check-input ml-0" type="checkbox" id="'.$name.'" name="'.$name.'"'.Checked($value == 'on').' />
          </td>
        </tr>';
}

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[67]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[67], '100%');

echo '
<div style="padding:16px 0;">'.ToHelp($lng, '&pg=966&mnuid=937&tconfig=0#z2').'</div>';

echo '
<div style="text-align:center;">
'.$admin[68].'<br />'.$admin[69].'
</div>';

echo '
<p class="forum" style="text-align:left; padding:5px 20px;">'.$admin[725].' : '.$userprefs[10].$messreg.'</p>
<div class="w-100 m-auto" style="max-width:720px;">';
if ($selskin == 'no_skin') {
	echo '
  <p style="text-align:center;">'.$admin[1438].'</p>
  <fieldset>
	<form name="genskin" action="admin.php?lng='.$lng.'&amp;pg=config2" method="post">
	  <input type="hidden" name="regit" value="3" />
	  <p class="forum2" style="text-align:center;">'.$admin[1439].'
	    <input type="text" class="texte" name="nameskin" size=20 />
	  </p>
	  <p style="text-align:center;">'.SubmitButton($admin[38]).'</p>
    </form>
  </fieldset>';
} else {
	echo '
  <form name="conf1" action="admin.php?lng='.$lng.'&amp;pg=config2" method="post">
    <input type="hidden" name="regit" value="1" />
    <input type="hidden" name="regpart" value="1" />
    <input type="hidden" name="selskin" value="'.$selskin.'" />';
	echo '
    <p style="width:700px; background-color:#7f7f7f; border:1px solid #67890b; border-radius:4px; color:#ffffff; font-weight:bold; margin:4px auto; padding:4px;">'.$admin[1559].'</p>
    <fieldset class="w-100 m-auto mb-3" style="max-width:700px;padding:0 16px 6px;">'.legend($admin[81]).'
      <div class="table-responsive-md">
      <table class="table w-100">
        <tr style="text-align:left;">
          <td style="padding:4px;">&bull; '.$admin[1577].'</td>
        </tr>';
    $pages54 = explode(',', $page[54]);
    $pages62 = explode(',', $page[62]);
    foreach ($templates as $key=>$tmpl) {
        if (!isset($pages54[$key])) $pages54[$key] = '-';
        if ($pages54[$key] == '-') $pages54[$key] = '';
        echo '
        '.InputTexte(32, $admin[1665].$key.' - '.$tmpl, 'page54'.$key, $pages54[$key], 4);
        if (!isset($pages62[$key])) $pages62[$key] = '-';
        if ($pages62[$key] == '-') $pages62[$key] = '';
        echo '
        '.InputTexte(64, $admin[1761], 'page62'.$key, $pages62[$key], 4);
    }
    echo '
      </table>
      </div>
    </fieldset>
    <fieldset style="max-width:700px;padding:0 16px 6px;">'.legend($admin[83]).'
      <div class="table-responsive-md">
      <table class="table w-100">';
	echo '
        '.InputCheckbox(4, $admin[623], 'page22', $page[22]).'
        '.InputCheckbox(4, $admin[244], 'page8', $page[8]).'
        '.InputCheckbox(4, $admin[1562], 'body7', $body[7]).'
        '.InputTexte(32, $admin[1560], 'body5', $body[5], 6).'
        '.InputTexte(32, $admin[1561], 'body6', $body[6], 6).'
        '.InputTexte(32, $admin[1529], 'opacity20', $opacity[20], 5).'
        '.InputCheckbox(4, $admin[1580], 'body16', $body[16]).'
      </table>
      </div>
    </fieldset>
    <div class="row quest p-0 mt-2 w-98 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
    </div>
  </form>';
    if (FileDBExist($meskin.'style.css')) {
		echo '
  <hr /><br />
  <a id="generate"></a>
  <fieldset class="w-100 text-center m-auto" style="max-width:700px;">'.legend($admin[724].$messreg2);
		echo '
    <b>'.$admin[725].'&nbsp;:&nbsp;'.$selskin.'</b>
    <p style="text-align:center;">'.$admin[722].'</p>';
	if (is_file(CONTROLSKIN))
{
	$handle = fopen(CONTROLSKIN, "r");
	$size = filesize(CONTROLSKIN);
	$controleskin = array();
	if ($size !== false)    
	{
		$controleskinlect  = fread($handle, filesize(CONTROLSKIN));
		$controleskind = explode("\n", $controleskinlect);
	}
	if ($controleskind[0] == 'oui')
	{
		echo '
	  <div class="alert alert-danger">
		<strong>'.$admin[2034].'</strong>
	  </div>';
	}
}
echo '
    <br />
    <form name="conf2" action="admin.php?lng='.$lng.'&amp;pg=config2#generate" method="post">
      <input type="hidden" name="regit" value="1" />
      <input type="hidden" name="regpart" value="2" />
	  <input type="hidden" name="incontrolskin" value="1" />												 
	  <input type="hidden" name="mycss" value="'.$selskin.'" />
      <input type="hidden" name="selskin" value="'.$selskin.'" />
      <div class="row quest p-0 mt-2 w-100 m-auto">
	    <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
        <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
      </div>';
        // Corps du document
		echo '
      <div class="m-auto" id="accordion" style="max-width:680px;">
      <div class="card">
        <a class="card-header card-link" data-toggle="collapse" href="#collapse1">'.strtoupper(trim(mb_substr($admin[91], 0, -1))).' / '.$admin[1489].'</a>
        <div class="collapse show" data-parent="#accordion" id="collapse1">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 4px;">&bull; '.strtoupper($admin[91]).'</td></tr>
              '.SelectImgs(32, $admin[91], 'page4', $page[4], $imgs).'
              '.InputTexte(32, $admin[1550], 'other5', $other[5], 16).'
              '.InputTexte(32, $admin[1551], 'other6', $other[6], 16).'
              <tr><td colspan="2" style="text-align:left;padding:4px 0;">&bull; '.$admin[1489].'</td></tr>
              '.SelectFontOptions(32, 'page39', $page[39], $fontNames).'
              '.SelectTextStyles(64, 'page41', $page[41], $textstyles).'
              '.InputTexte(64, $admin[86], 'page40', $page[40], 6).'
              '.GetSelector(64, $admin[95], 'texte9', $texte[9]).'
              '.GetSelector(32, $admin[82], 'body0', $body[0]).'
              '.SelectImgs(32, $admin[88], 'body1', $body[1], $imgs).'
              '.InputCheckbox(64, $admin[321], 'body2', $body[2]).'
              '.SelectRepeats(64, $admin[1490], 'body3', $body[3], $rpts).'
              '.SelectPoss(64, $admin[1491], 'body4', $body[4], $poss).'
              '.SelectImgs(32, $admin[1493], 'body8', $body[8], $imgs).'
              '.SelectRepeats(64, $admin[1490], 'body9', $body[9], $rpts).'
            </table>
            </div>
          </div>
        </div>
      </div>';
        // Parallaxe
        echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse2">'.$admin[1572].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse2">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 4px;">&bull; '.$admin[1572].'</td></tr>';
        echo '
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 32px;">&bull; '.$admin[1574].'1 ('.$admin[1583].')</td></tr>
              '.GetSelector(64, $admin[82], 'page56', $page[56]).'
              '.SelectImgs(64, $admin[88], 'page24', $page[24], $imgs).'
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 32px;">&bull; '.$admin[1574].'2 ('.$admin[1666].')</td></tr>
              '.GetSelector(64, $admin[82], 'page57', $page[57]).'
              '.SelectImgs(64, $admin[88], 'page25', $page[25], $imgs).'
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 32px;">&bull; '.$admin[1574].'3 ('.$admin[1667].')</td></tr>
              '.GetSelector(64, $admin[82], 'page58', $page[58]).'
              '.SelectImgs(64, $admin[88], 'page26', $page[26], $imgs).'
              '.SelectRepeats(32, $admin[880], 'page50', $page[50], $parallaxnav).'
              '.InputCheckbox(64, $admin[241], 'page7', $page[7]).'
              '.InputTexte(96, $admin[436], 'page63', $page[63], 6).'
              '.InputTexte(96, $admin[1516], 'page64', $page[64], 6).'
              '.GetSelector(64, $admin[82], 'page51', $page[51]).'
              '.GetSelector(64, $admin[87], 'bordure44', $bordure[44]).'
              '.InputTexte(64, $admin[1575], 'page52', $page[52], 6).'
            </table>
            </div>
          </div>
        </div>
      </div>';
        // Bandeaux
        echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse3">'.$admin[81].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse3">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 4px;">&bull; HEADER</td></tr>
              '.GetSelector(32, $admin[82], 'page43', $page[43]).'
              '.InputTexte(64, $admin[1529], 'opacity18', $opacity[18], 6).'
              '.GetSelector(32, $admin[87], 'bordure40', $bordure[40]).'
              '.InputTexte(64, $admin[211], 'bordure41', $bordure[41], 16).'
              '.SelectBorderStyles(64, $admin[422], 'bordure43', $bordure[43], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'bordure42', $bordure[42], 16).'
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 4px;">&bull; TOP</td></tr>
              '.InputTexte(32, $admin[436], 'titre78', (empty($titre[78]) ? '100%' : $titre[78]), 6).'
              '.InputTexte(32, $admin[1551], 'marge24', $marge[24], 16).'
              '.GetSelector(32, $admin[82], 'page29', $page[29]).'
              '.InputTexte(64, $admin[1529], 'opacity0', $opacity[0], 6).'
              '.GetSelector(32, $admin[87], 'bordure19', $bordure[19]).'
              '.InputTexte(64, $admin[211], 'bordure20', $bordure[20], 16).'
              '.SelectBorderStyles(64, $admin[422], 'bordure29', $bordure[29], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'bordure21', $bordure[21], 16).'
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">&bull; MAIN</td></tr>
              '.InputTexte(32, $admin[436], 'titre79', (empty($titre[79]) ? '100%' : $titre[79]), 6).'
              '.InputTexte(32, $admin[1551], 'marge25', $marge[25], 16).'
              '.GetSelector(32, $admin[82], 'titre34', $titre[34]).'
              '.InputTexte(64, $admin[1529], 'opacity7', $opacity[7], 6).'
              '.GetSelector(32, $admin[87], 'titre36', $titre[36]).'
              '.InputTexte(64, $admin[211], 'titre37', $titre[37], 16).'
              '.SelectBorderStyles(64, $admin[422], 'titre38', $titre[38], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'titre39', $titre[39], 16).'
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 4px;">&bull; BOTTOM</td></tr>
              '.InputTexte(32, $admin[436], 'titre80', (empty($titre[80]) ? '100%' : $titre[80]), 6).'
              '.InputTexte(32, $admin[1551], 'marge26', $marge[26], 16).'
              '.GetSelector(32, $admin[82], 'page31', $page[31]).'
              '.InputTexte(64, $admin[1529], 'opacity1', $opacity[1], 6).'
              '.GetSelector(32, $admin[87], 'bordure22', $bordure[22]).'
              '.InputTexte(64, $admin[211], 'bordure23', $bordure[23], 16).'
              '.SelectBorderStyles(64, $admin[422], 'bordure30', $bordure[30], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'bordure24', $bordure[24], 16).'
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">&bull; FOOTER</td></tr>
              '.InputTexte(32, $admin[436], 'titre81', (empty($titre[81]) ? '100%' : $titre[81]), 6).'
              '.InputTexte(32, $admin[1564], 'page21', $page[21], 6).'
              '.InputTexte(32, $admin[1551], 'marge27', $marge[27], 16).'
              '.GetSelector(32, $admin[82], 'page5', $page[5]).'
              '.InputTexte(64, $admin[1529], 'opacity2', $opacity[2], 6).'
              '.GetSelector(32, $admin[87], 'bordure3', $bordure[3]).'
              '.InputTexte(64, $admin[211], 'bordure4', $bordure[4], 16).'
              '.SelectBorderStyles(64, $admin[422], 'bordure31', $bordure[31], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'bordure5', $bordure[5], 16).'
              '.SelectFontOptions(32, 'page45', $page[45], $fontNames).'
              '.SelectTextStyles(64, 'page47', $page[47], $textstyles).'
              '.InputTexte(64, $admin[86], 'page46', $page[46], 6).'
              '.GetSelector(64, $admin[95], 'texte8', $texte[8]).'
              '.SelectBorderStyles(32, $admin[443], 'texte13', $texte[13], $textalign).'
            </table>
            </div>
          </div>
        </div>
      </div>';
        // Colonnes latérales
        echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse4">'.$admin[1519].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse4">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">&bull; '.strtoupper($admin[494]).'</td></tr>
              '.InputTexte(32, $admin[1565], 'body10', $body[10], 2).'
              '.GetSelector(32, $admin[82], 'titre14', $titre[14]).'
              '.InputTexte(64, $admin[1529], 'opacity4', $opacity[4], 6).'
              '.GetSelector(32, $admin[87], 'titre16', $titre[16]).'
              '.InputTexte(64, $admin[211], 'titre17', $titre[17], 16).'
              '.SelectBorderStyles(64, $admin[422], 'titre18', $titre[18], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'titre19', $titre[19], 16).'
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">&bull; '.strtoupper($admin[495]).'</td></tr>
              '.InputTexte(32, $admin[1565], 'body12', $body[12], 2).'
              '.GetSelector(32, $admin[82], 'titre21', $titre[21]).'
              '.InputTexte(64, $admin[1529], 'opacity5', $opacity[5], 6).'
              '.GetSelector(32, $admin[87], 'titre23', $titre[23]).'
              '.InputTexte(64, $admin[211], 'titre24', $titre[24], 16).'
              '.SelectBorderStyles(64, $admin[422], 'titre25', $titre[25], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'titre26', $titre[26], 16).'
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">&bull; '.strtoupper($admin[770]).'</td></tr>
              '.InputTexte(32, $admin[1565].' '.$admin[494], 'body13', $body[13], 2).'
              '.InputTexte(32, $admin[1565].' '.$admin[495], 'body15', $body[15], 2).'
              '.GetSelector(32, $admin[82], 'titre27', $titre[27]).'
              '.InputTexte(64, $admin[1529], 'opacity6', $opacity[6], 6).'
              '.GetSelector(32, $admin[87], 'titre29', $titre[29]).'
              '.InputTexte(64, $admin[211], 'titre30', $titre[30], 16).'
              '.SelectBorderStyles(64, $admin[422], 'titre31', $titre[31], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'titre32', $titre[32], 16).'
            </table>
            </div>
          </div>
        </div>
      </div>';
        // Zones centrales
        echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse5">'.$admin[1548].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse5">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">&bull; CENTER</td></tr>
              '.InputTexte(32, $admin[1565], 'body11', $body[11], 2).'
              '.GetSelector(32, $admin[82], 'titre41', $titre[41]).'
              '.InputTexte(64, $admin[1529], 'opacity8', $opacity[8], 6).'
              '.GetSelector(32, $admin[87], 'titre43', $titre[43]).'
              '.InputTexte(64, $admin[211], 'titre44', $titre[44], 16).'
              '.SelectBorderStyles(64, $admin[422], 'titre45', $titre[45], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'titre46', $titre[46], 16).'
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">&bull; '.strtoupper($admin[1087]).'</td></tr>
              '.InputTexte(32, $admin[1551], 'marge28', $marge[28], 16).'
              '.GetSelector(32, $admin[82], 'titre60', $titre[60]).'
              '.InputTexte(64, $admin[1529], 'opacity15', $opacity[15], 6).'
              '.GetSelector(32, $admin[87], 'titre62', $titre[62]).'
              '.InputTexte(64, $admin[211], 'titre63', $titre[63], 16).'
              '.SelectBorderStyles(64, $admin[422], 'titre64', $titre[64], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'titre65', $titre[65], 16).'
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">&bull; '.strtoupper($admin[1088]).'</td></tr>
              '.InputTexte(32, $admin[1551], 'marge29', $marge[29], 16).'
              '.GetSelector(32, $admin[82], 'titre66', $titre[66]).'
              '.InputTexte(64, $admin[1529], 'opacity16', $opacity[16], 6).'
              '.GetSelector(32, $admin[87], 'titre68', $titre[68]).'
              '.InputTexte(64, $admin[211], 'titre69', $titre[69], 16).'
              '.SelectBorderStyles(64, $admin[422], 'titre70', $titre[70], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'titre71', $titre[71], 16).'
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">&bull; '.strtoupper($admin[1089]).'</td></tr>
              '.InputTexte(32, $admin[1551], 'marge30', $marge[30], 16).'
              '.GetSelector(32, $admin[82], 'titre72', $titre[72]).'
              '.InputTexte(64, $admin[1529], 'opacity17', $opacity[17], 6).'
              '.GetSelector(32, $admin[87], 'titre74', $titre[74]).'
              '.InputTexte(64, $admin[211], 'titre75', $titre[75], 16).'
              '.SelectBorderStyles(64, $admin[422], 'titre76', $titre[76], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'titre77', $titre[77], 16).'
            </table>
            </div>
          </div>
        </div>
      </div>';
        // Citations - Fil d'Ariane - Boîte impression
        echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse6">
          '.$admin[405].' - '.strtoupper($admin[1668]).' - '.strtoupper($admin[1084]).'
        </a>
        <div class="collapse" data-parent="#accordion" id="collapse6">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">&bull; '.$admin[405].'</td></tr>
              '.SelectFontOptions(32, 'citation0', $citation[0], $fontNames).'
              '.SelectTextStyles(64, 'citation3', $citation[3], $textstyles).'
              '.InputTexte(64, $admin[86], 'citation1', $citation[1], 6).'
              '.GetSelector(64, $admin[93], 'citation2', $citation[2]).'
              '.GetSelector(32, $admin[82], 'citation10', $citation[10]).'
              '.InputTexte(64, $admin[1529], 'opacity19', $opacity[19], 6).'
              '.SelectImgs(64, $admin[88], 'citation11', $citation[11], $imgs).'
              '.GetSelector(64, $admin[87], 'citation12', $citation[12]).'
              '.InputTexte(96, $admin[211], 'citation13', $citation[13], 16).'
              '.SelectBorderStyles(96, $admin[422], 'citation15', $citation[15], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'citation14', $citation[14], 16).'
              '.InputTexte(32, $admin[1550], 'citation16', $citation[16], 16).'
              '.InputTexte(32, $admin[1551], 'citation17', $citation[17], 16).'
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">&bull; '.strtoupper($admin[1668]).'</td></tr>
              '.GetSelector(32, $admin[150].$admin[1520], 'citation4', $citation[4]).'
              '.GetSelector(32, $admin[150].$admin[1521], 'citation5', $citation[5]).'
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">&bull; '.strtoupper($admin[1084]).'</td></tr>
              '.SelectFontOptions(32, 'citation6', $citation[6], $fontNames).'
              '.SelectTextStyles(64, 'citation7', $citation[7], $textstyles).'
              '.InputTexte(64, $admin[86], 'citation8', $citation[8], 6).'
              '.GetSelector(64, $admin[93], 'citation9', $citation[9]).'
            </table>
            </div>
          </div>
        </div>
      </div>';
        // Menu de navigation
        echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse7">'.$admin[806].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse7">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              '.SelectFontOptions(4, 'page18', $page[18], $fontNames).'
              '.SelectTextStyles(32, 'page37', $page[37], $textstyles).'
              '.InputTexte(32, $admin[86], 'page19', $page[19], 6).'
              '.GetSelector(4, $admin[1510], 'lien9', $lien[9]).'
              '.GetSelector(32, $admin[865], 'lien8', $lien[8]).'
              '.GetSelector(4, $admin[101], 'lien4', $lien[4]).'
              '.GetSelector(32, $admin[865], 'lien7', $lien[7]).'
              '.GetSelector(4, $admin[102], 'lien5', $lien[5]).'
              '.GetSelector(32, $admin[865], 'lien6', $lien[6]).'
              '.InputTexte(4, $admin[1550].' ('.mb_strtolower($admin[1525]).')', 'lien15', $lien[15], 16).'
              '.InputTexte(4, $admin[1551].' ('.mb_strtolower($admin[1525]).')', 'lien14', $lien[14], 16).'
              '.GetSelector(4, $admin[87].' ('.mb_strtolower($admin[1525]).')', 'lien10', $lien[10]).'
              '.InputTexte(32, $admin[211], 'lien11', $lien[11], 6).'
              '.SelectBorderStyles(32, $admin[422], 'lien12', $lien[12], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'lien13', $lien[13], 16).'
              '.GetSelector(4, $admin[82], 'page34', $page[34]).'
              '.InputTexte(32, $admin[1529], 'opacity3', $opacity[3], 6).'
              '.SelectImgs(32, $admin[88], 'page33', $page[33], $imgs).'
              '.GetSelector(32, $admin[87], 'bordure26', $bordure[26]).'
              '.InputTexte(64, $admin[211], 'bordure27', $bordure[27], 16).'
              '.SelectBorderStyles(64, $admin[422], 'bordure32', $bordure[32], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'bordure28', $bordure[28], 16).'
            </table>
            </div>
          </div>
        </div>
      </div>';
        // Navigation latérale
        echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse8">'.mb_strtoupper($admin[1766]).'</a>
        <div class="collapse" data-parent="#accordion" id="collapse8">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">&bull; '.mb_strtoupper($admin[880]).'</td></tr>
              '.InputTexte(32, $admin[1550].' ('.$admin[880].')', 'marge33', $marge[33], 16).'
              '.InputTexte(32, $admin[1551].' ('.$admin[880].')', 'marge34', $marge[34], 16).'
              '.InputTexte(32, $admin[1550].' ('.$admin[1770].')', 'marge37', $marge[37], 16).'
              '.InputTexte(32, $admin[1550].' ('.$admin[1771].')', 'marge38', $marge[38], 16).'
              '.GetSelector(32, $admin[865], 'lien25', $lien[25]).'
              '.GetSelector(32, $admin[87], 'lien26', $lien[26]).'
              '.InputTexte(64, $admin[211], 'lien27', $lien[27], 16).'
              '.SelectBorderStyles(64, $admin[422], 'lien29', $lien[29], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'lien28', $lien[28], 16).'
              '.SelectFontOptions(32, 'page65', $page[65], $fontNames).'
              '.SelectTextStyles(64, 'page66', $page[66], $textstyles).'
              '.InputTexte(64, $admin[86], 'page67', $page[67], 6).'
              '.InputTexte(32, $admin[1550].' ('.$admin[394].')', 'marge35', $marge[35], 16).'
              '.InputTexte(32, $admin[1551].' ('.$admin[394].')', 'marge36', $marge[36], 16).'
              '.GetSelector(32, $admin[101], 'lien16', $lien[16]).'
              '.GetSelector(64, $admin[865], 'lien30', $lien[30]).'
              '.GetSelector(32, $admin[102], 'lien17', $lien[17]).'
              '.GetSelector(64, $admin[865], 'lien18', $lien[18]).'
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">&bull; '.mb_strtoupper($admin[1767]).'</td></tr>
              '.GetSelector(32, $admin[865], 'lien19', $lien[19]).'
              '.GetSelector(32, $admin[87], 'lien20', $lien[20]).'
              '.InputTexte(64, $admin[211], 'lien21', $lien[21], 6).'
              '.GetSelector(32, $admin[1768], 'lien36', $lien[36]).'
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">&bull; '.mb_strtoupper($admin[956]).'</td></tr>
              '.SelectFontOptions(32, 'titre47', $titre[47], $fontNames).'
              '.SelectTextStyles(64, 'titre48', $titre[48], $textstyles).'
              '.InputTexte(64, $admin[86], 'titre49', $titre[49], 6).'
              '.GetSelector(64, $admin[150], 'titre50', $titre[50]).'
              '.GetSelector(32, $admin[865], 'titre51', $titre[51]).'
              '.GetSelector(32, $admin[87], 'titre52', $titre[52]).'
              '.InputTexte(64, $admin[211], 'titre53', $titre[53], 16).'
              '.SelectBorderStyles(64, $admin[422], 'titre54', $titre[54], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'titre55', $titre[55], 16).'
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">&bull; '.mb_strtoupper($admin[1217]).'</td></tr>
              '.SelectFontOptions(32, 'page68', $page[68], $fontNames).'
              '.SelectTextStyles(64, 'page69', $page[69], $textstyles).'
              '.InputTexte(64, $admin[86], 'page70', $page[70], 6).'
              '.GetSelector(32, $admin[101], 'lien22', $lien[22]).'
              '.GetSelector(64, $admin[865], 'lien31', $lien[31]).'
              '.GetSelector(32, $admin[102], 'lien23', $lien[23]).'
              '.GetSelector(64, $admin[865], 'lien24', $lien[24]).'
              '.GetSelector(32, $admin[87], 'lien32', $lien[32]).'
              '.InputTexte(64, $admin[211], 'lien33', $lien[33], 16).'
              '.SelectBorderStyles(64, $admin[422], 'lien35', $lien[35], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'lien34', $lien[34], 16).'
            </table>
            </div>
          </div>
        </div>
      </div>';
        // Titre des boîtes centrales
        echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse9">'.$admin[92].$admin[96].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse9">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              '.GetSelector(4, $admin[82], 'titre1', $titre[1]).'
              '.InputTexte(32, $admin[1529], 'opacity9', $opacity[9], 6).'
              '.SelectImgs(4, $admin[94], 'titre8', $titre[8], $imgs).'
              '.SelectFontOptions(4, 'titre2', $titre[2], $fontNames).'
              '.SelectTextStyles(32, 'titre12', $titre[12], $textstyles).'
              '.InputTexte(32, $admin[86], 'titre3', $titre[3], 6).'
              '.GetSelector(32, $admin[93], 'titre0', $titre[0]).'
              '.SelectBorderStyles(32, $admin[443], 'texte10', $texte[10], $textalign).'
              '.InputTexte(4, $admin[1550], 'marge9', $marge[9], 16).'
              '.InputTexte(4, $admin[1551], 'marge10', $marge[10], 16).'
              '.GetSelector(4, $admin[87], 'bordure7', $bordure[7]).'
              '.InputTexte(32, $admin[211], 'bordure8', $bordure[8], 16).'
              '.SelectBorderStyles(32, $admin[422], 'bordure34', $bordure[34], $borderstyles).'
              '.InputTexte(4, $admin[1343], 'bordure9', $bordure[9], 16).'
            </table>
            </div>
          </div>
        </div>
      </div>';
        // Boîtes centrales
        echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse10">'.$admin[96].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse10">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              '.SelectFontOptions(4, 'page1', $page[1], $fontNames).'
              '.SelectTextStyles(32, 'page36', $page[36], $textstyles).'
              '.InputTexte(32, $admin[86], 'page2', $page[2], 6).'
              '.GetSelector(32, $admin[95], 'texte0', $texte[0]).'
              '.InputTexte(32, $admin[695], 'page6', $page[6], 6).'
              '.GetSelector(4, $admin[99], 'texte1', $texte[1]).'
              '.GetSelector(4, $admin[100], 'texte2', $texte[2]).'
              '.InputTexte(32, $admin[1529], 'opacity10', $opacity[10], 6).'
              '.GetSelector(4, $admin[101], 'lien0', $lien[0]).'
              '.GetSelector(4, $admin[102], 'lien1', $lien[1]).'
              '.InputTexte(4, $admin[1550], 'marge11', $marge[11], 16).'
              '.InputTexte(4, $admin[1551], 'marge12', $marge[12], 16).'
              '.GetSelector(4, $admin[87], 'bordure10', $bordure[10]).'
              '.InputTexte(32, $admin[211], 'bordure11', $bordure[11], 16).'
              '.SelectBorderStyles(32, $admin[422], 'bordure35', $bordure[35], $borderstyles).'
              '.InputTexte(4, $admin[1343], 'bordure12', $bordure[12], 16).'
            </table>
            </div>
          </div>
        </div>
      </div>';
        // Titre des boîtes latérales
        echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse11">'.$admin[92].$admin[804].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse11">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              '.GetSelector(4, $admin[82], 'titre5', $titre[5]).'
              '.GetSelector(4, $admin[82].' BOTTOM', 'titre15', $titre[15]).'
              '.InputTexte(32, $admin[1529], 'opacity11', $opacity[11], 6).'
              '.SelectImgs(4, $admin[94], 'titre9', $titre[9], $imgs).'
              '.SelectFontOptions(4, 'titre6', $titre[6], $fontNames).'
              '.SelectTextStyles(32, 'titre11', $titre[11], $textstyles).'
              '.InputTexte(32, $admin[86], 'titre7', $titre[7], 6).'
              '.GetSelector(32, $admin[93], 'titre4', $titre[4]).'
              '.GetSelector(32, $admin[93].' BOTTOM', 'titre10', $titre[10]).'
              '.SelectBorderStyles(32, $admin[443], 'texte11', $texte[11], $textalign).'
              '.InputTexte(4, $admin[1550], 'marge13', $marge[13], 16).'
              '.InputTexte(4, $admin[1551], 'marge14', $marge[14], 16).'
              '.GetSelector(4, $admin[87], 'bordure13', $bordure[13]).'
              '.InputTexte(32, $admin[211], 'bordure14', $bordure[14], 16).'
              '.SelectBorderStyles(32, $admin[422], 'bordure36', $bordure[36], $borderstyles).'
              '.InputTexte(4, $admin[1343], 'bordure15', $bordure[15], 16).'
            </table>
            </div>
          </div>
        </div>
      </div>';
        // Boîtes latérales
        echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse12">'.$admin[804].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse12">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              '.SelectFontOptions(4, 'page16', $page[16], $fontNames).'
              '.SelectTextStyles(32, 'page35', $page[35], $textstyles).'
              '.InputTexte(32, $admin[86], 'page17', $page[17], 6).'
              '.GetSelector(32, $admin[95], 'texte5', $texte[5]).'
              '.GetSelector(32, $admin[95].' BOTTOM', 'titre13', $titre[13]).'
              '.InputTexte(32, $admin[695], 'page11', $page[11], 6).'
              '.GetSelector(4, $admin[99], 'texte6', $texte[6]).'
              '.GetSelector(4, $admin[99].' BOTTOM', 'titre20', $titre[20]).'
              '.GetSelector(4, $admin[100], 'texte7', $texte[7]).'
              '.InputTexte(32, $admin[1529], 'opacity12', $opacity[12], 6).'
              '.GetSelector(4, $admin[101], 'lien2', $lien[2]).'
              '.GetSelector(4, $admin[102], 'lien3', $lien[3]).'
              '.InputTexte(4, $admin[1550], 'marge15', $marge[15], 16).'
              '.InputTexte(4, $admin[1551], 'marge16', $marge[16], 16).'
              '.GetSelector(4, $admin[87], 'bordure16', $bordure[16]).'
              '.InputTexte(32, $admin[211], 'bordure17', $bordure[17], 16).'
              '.SelectBorderStyles(32, $admin[422], 'bordure37', $bordure[37], $borderstyles).'
              '.InputTexte(4, $admin[1343], 'bordure18', $bordure[18], 16).'
            </table>
            </div>
          </div>
        </div>
      </div>';
        // Forum - Blog - Tables
        echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse13">'.$admin[800].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse13">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              '.GetSelector(4, $admin[801], 'forum0', $forum[0]).'
              '.SelectFontOptions(32, 'forum12', $forum[12], $fontNames).'
              '.SelectTextStyles(64, 'forum13', $forum[13], $textstyles).'
              '.InputTexte(64, $admin[86], 'forum14', $forum[14], 6).'
              '.GetSelector(32, $admin[95], 'forum11', $forum[11]).'
              '.GetSelector(4, $admin[802], 'forum1', $forum[1]).'
              '.GetSelector(4, $admin[803], 'forum2', $forum[2]).'
            </table>
            </div>
          </div>
        </div>
      </div>';
        // Formulaires
        echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse14">'.$admin[399].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse14">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              '.GetSelector(4, $admin[400], 'presform1', $presform[1]).'
              '.GetSelector(4, $admin[401], 'presform2', $presform[2]).'
              '.GetSelector(32, ucfirst($admin[1669]), 'presform11', $presform[11]).'
              '.InputTexte(32, $admin[1551], 'presform12', $presform[12], 16).'
              '.GetSelector(4, $admin[401].' ('.$admin[1566].')', 'presform13', $presform[13]).'
              '.GetSelector(32, ucfirst($admin[1669]).' ('.$admin[1566].')', 'presform14', $presform[14]).'
              '.GetSelector(4, $admin[402], 'presform3', $presform[3]).'
              '.GetSelector(4, $admin[403], 'presform6', $presform[6]).'
              '.SelectFontOptions(4, 'presform4', $presform[4], $fontNames).'
              '.SelectTextStyles(32, 'presform10', $presform[10], $textstyles).'
              '.InputTexte(32, $admin[86], 'presform5', $presform[5], 6).'
              '.GetSelector(4, $admin[95], 'presform0', $presform[0]).'
              '.GetSelector(4, $admin[404], 'presform8', $presform[8]).'
              '.InputTexte(32, $admin[211], 'presform7', $presform[7], 16).'
              '.SelectBorderStyles(32, $admin[422], 'bordure38', $bordure[38], $borderstyles).'
              '.InputTexte(4, $admin[1343], 'presform9', $presform[9], 16).'
            </table>
            </div>
          </div>
        </div>
      </div>';
        // Boîte calendrier
        echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse15">'.$admin[409].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse15">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              '.GetSelector(4, $admin[865], 'calendar11', $calendar[11]).'
              '.GetSelector(4, $admin[411], 'calendar0', $calendar[0]).'
              '.GetSelector(4, $admin[412], 'calendar1', $calendar[1]).'
              '.GetSelector(4, $admin[413], 'calendar2', $calendar[2]).'
              '.GetSelector(32, ucfirst($admin[1669]), 'calendar18', $calendar[18]).'
              '.GetSelector(4, $admin[414], 'calendar3', $calendar[3]).'
              '.GetSelector(32, ucfirst($admin[1669]), 'calendar19', $calendar[19]).'
              '.GetSelector(4, $admin[415], 'calendar4', $calendar[4]).'
              '.GetSelector(32, ucfirst($admin[1669]), 'calendar20', $calendar[20]).'
              '.SelectFontOptions(4, 'calendar5', $calendar[5], $fontNames).'
              '.SelectTextStyles(32, 'calendar15', $calendar[15], $textstyles).'
              '.InputTexte(32, $admin[86], 'calendar6', $calendar[6], 6).'
              '.GetSelector(4, $admin[417], 'calendar7', $calendar[7]).'
              '.GetSelector(4, $admin[418], 'calendar8', $calendar[8]).'
              '.GetSelector(32, $admin[865], 'calendar17', $calendar[17]).'
              '.GetSelector(4, $admin[1670], 'calendar10', $calendar[10]).'
              '.GetSelector(32, ucfirst($admin[1669]), 'calendar16', $calendar[16]).'
              <tr style="text-align:left;">
                <td style="padding:4px;">&bull; '.$admin[1344].'</td>
                <td style="padding:4px;">
                  <select name="calendar9">
                    <option value="0px"'.Selected($calendar[9] == "0px").'>0px</option>
                    <option value="1px"'.Selected($calendar[9] == "1px").'>1px</option>
                    <option value="2px"'.Selected($calendar[9] == "2px").'>2px</option>
                    <option value="3px"'.Selected($calendar[9] == "3px").'>3px</option>
                  </select>
                </td>
              </tr>
              '.InputTexte(4, $admin[211], 'calendar12', $calendar[12], 6).'
              '.GetSelector(4, $admin[404], 'calendar13', $calendar[13]).'
              '.InputTexte(4, $admin[1343], 'calendar14', $calendar[14], 16).'
            </table>
            </div>
          </div>
        </div>
      </div>';
        // Annuaires - Photos
        echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse16">'.$admin[1495].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse16">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              '.GetSelector(4, $admin[1496], 'annuaire0', $annuaire[0]).'
              '.SelectFontOptions(32, 'annuaire1', $annuaire[1], $fontNames).'
              '.SelectTextStyles(64, 'annuaire7', $annuaire[7], $textstyles).'
              '.InputTexte(64, $admin[86], 'annuaire2', $annuaire[2], 6).'
              '.GetSelector(64, $admin[93], 'annuaire3', $annuaire[3]).'
              '.GetSelector(32, $admin[87], 'annuaire4', $annuaire[4]).'
              '.InputTexte(64, $admin[211], 'annuaire5', $annuaire[5], 16).'
              '.SelectBorderStyles(64, $admin[422], 'bordure39', $bordure[39], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'annuaire6', $annuaire[6], 16).'
              '.GetSelector(4, $admin[1497], 'annuaire8', $annuaire[8]).'
              '.SelectFontOptions(32, 'annuaire10', $annuaire[10], $fontNames).'
              '.SelectTextStyles(64, 'annuaire11', $annuaire[11], $textstyles).'
              '.InputTexte(64, $admin[86], 'annuaire12', $annuaire[12], 6).'
              '.GetSelector(64, $admin[93], 'annuaire13', $annuaire[13]).'
              '.GetSelector(4, $admin[1498], 'annuaire9', $annuaire[9]).'
              '.SelectFontOptions(32, 'annuaire14', $annuaire[14], $fontNames).'
              '.SelectTextStyles(64, 'annuaire15', $annuaire[15], $textstyles).'
              '.InputTexte(64, $admin[86], 'annuaire16', $annuaire[16], 6).'
              '.GetSelector(64, $admin[93], 'annuaire17', $annuaire[17]).'
            </table>
            </div>
          </div>
        </div>
      </div>';
        // Onglets - Accordéons - Fenêtre modale
		if (!isset($tabs[28])) $tabs[28] = '';
		if (!isset($tabs[29])) $tabs[29] = '';
		if (!isset($tabs[30])) $tabs[30] = '';
		if (!isset($tabs[31])) $tabs[31] = '';
		
        echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse17">
          '.$admin[1525].' - '.$admin[1526].' - '.$admin[1527].'
        </a>
        <div class="collapse" data-parent="#accordion" id="collapse17">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">'.$admin[1525].' :</td></tr>
              '.GetSelector(32, $admin[82].' ('.mb_strtolower($admin[1525]).')', 'tabs1', $tabs[1]).'
              '.GetSelector(64, $admin[95], 'tabs0', $tabs[0]).'
              '.GetSelector(32, $admin[87].' ('.mb_strtolower($admin[1525]).')', 'tabs2', $tabs[2]).'
              '.InputTexte(64, $admin[211], 'tabs3', $tabs[3], 6).'
              '.InputTexte(64, $admin[1343], 'tabs4', $tabs[4], 16).'
              '.GetSelector(32, $admin[82].' ('.mb_strtolower($admin[765]).')', 'tabs5', $tabs[5]).'
              '.GetSelector(64, $admin[95], 'tabs6', $tabs[6]).'
              '.GetSelector(32, $admin[82].' ('.mb_strtolower($admin[1566]).')', 'tabs28', $tabs[28]).'
              '.GetSelector(64, $admin[95], 'tabs29', $tabs[29]).'
              '.GetSelector(32, $admin[82].' ('.mb_strtolower($admin[1389]).')', 'tabs7', $tabs[7]).'
              '.GetSelector(32, $admin[87].' ('.mb_strtolower($admin[1389]).')', 'tabs8', $tabs[8]).'
              '.InputTexte(64, $admin[211], 'tabs9', $tabs[9], 6).'
              '.InputTexte(64, $admin[1343], 'tabs10', $tabs[10], 16).'
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">'.$admin[1526].' :</td></tr>
              '.GetSelector(32, $admin[82].' ('.mb_strtolower($admin[1525]).')', 'tabs12', $tabs[12]).'
              '.GetSelector(64, $admin[95], 'tabs11', $tabs[11]).'
              '.GetSelector(32, $admin[87].' ('.mb_strtolower($admin[1525]).')', 'tabs13', $tabs[13]).'
              '.InputTexte(64, $admin[211], 'tabs14', $tabs[14], 6).'
              '.InputTexte(64, $admin[1343], 'tabs15', $tabs[15], 16).'
              '.GetSelector(32, $admin[82].' ('.mb_strtolower($admin[765]).')', 'tabs16', $tabs[16]).'
              '.GetSelector(64, $admin[95], 'tabs17', $tabs[17]).'
              '.GetSelector(32, $admin[82].' ('.mb_strtolower($admin[1566]).')', 'tabs30', $tabs[30]).'
              '.GetSelector(64, $admin[95], 'tabs31', $tabs[31]).'
              '.GetSelector(32, $admin[82].' ('.mb_strtolower($admin[1389]).')', 'tabs18', $tabs[18]).'
              '.GetSelector(32, $admin[87].' ('.mb_strtolower($admin[1389]).')', 'tabs19', $tabs[19]).'
              '.InputTexte(64, $admin[211], 'tabs20', $tabs[20], 6).'
              '.InputTexte(64, $admin[1343], 'tabs21', $tabs[21], 16).'
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">'.$admin[1527].' :</td></tr>
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 32px;">'.$admin[1904].'</td></tr>
              '.GetSelector(64, $admin[82], 'tabs24', $tabs[24]).'
              '.GetSelector(64, $admin[95], 'tabs23', $tabs[23]).'
              '.GetSelector(64, $admin[87], 'tabs25', $tabs[25]).'
              '.InputTexte(64, $admin[211], 'tabs26', $tabs[26], 16).'
              '.InputTexte(64, $admin[1343], 'tabs27', $tabs[27], 16).'
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 64px;">'.$admin[1905].'</td></tr>
              '.GetSelector(96, $admin[82], 'tabs32', $tabs[32]).'
              '.GetSelector(96, $admin[95], 'tabs33', $tabs[33]).'
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 64px;">'.$admin[1906].'</td></tr>
              '.GetSelector(96, $admin[82], 'tabs34', $tabs[34]).'
			  '.GetSelector(96, $admin[95], 'tabs35', $tabs[35]).'
             <tr><td colspan="2" style="text-align:left;padding:4px 0 0 64px;">'.$admin[1907].'</td></tr>
              '.GetSelector(96, $admin[82], 'tabs36', $tabs[36]).'
			  '.GetSelector(96, $admin[95], 'tabs37', $tabs[37]).'
            </table>
            </div>
          </div>
        </div>
      </div>';
        // Menus déroulants
        echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse18">'.$admin[1511].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse18">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">'.$admin[1512].'</td></tr>
              '.InputTexte(32, $admin[436], 'dropdown0', $dropdown[0], 6).'
              '.SelectFontOptions(32, 'dropdown1', $dropdown[1], $fontNames).'
              '.SelectTextStyles(64, 'dropdown2', $dropdown[2], $textstyles).'
              '.InputTexte(64, $admin[86], 'dropdown3', $dropdown[3], 6).'
              '.GetSelector(64, $admin[93], 'dropdown4', $dropdown[4]).'
              '.SelectBorderStyles(32, $admin[443], 'texte12', $texte[12], $textalign).'
              '.GetSelector(32, $admin[82], 'dropdown5', $dropdown[5]).'
              '.GetSelector(32, $admin[87], 'dropdown7', $dropdown[7]).'
              '.InputTexte(64, $admin[211], 'dropdown8', $dropdown[8], 16).'
              '.SelectBorderStyles(64, $admin[422], 'dropdown9', $dropdown[9], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'dropdown10', $dropdown[10], 16).'
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">'.$admin[1513].'</td></tr>
              '.InputTexte(32, $admin[436], 'dropdown11', $dropdown[11], 6).'
              '.GetSelector(32, $admin[82], 'dropdown12', $dropdown[12]).'
              '.GetSelector(32, $admin[95], 'dropdown25', $dropdown[25]).'
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">'.$admin[1514].'</td></tr>
              '.GetSelector(32, $admin[82], 'dropdown14', $dropdown[14]).'
              '.GetSelector(32, $admin[95], 'dropdown16', $dropdown[16]).'
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">'.$admin[1563].'</td></tr>
              '.GetSelector(32, $admin[82], 'dropdown27', $dropdown[27]).'
              '.GetSelector(32, $admin[95], 'dropdown29', $dropdown[29]).'
              <tr style="text-align:left;"><td colspan="2" style="padding:4px 0 0 4px;">'.$admin[1515].'</td></tr>
              '.InputTexte(32, $admin[436], 'dropdown17', $dropdown[17], 6).'
              '.InputTexte(32, $admin[1516], 'dropdown18', $dropdown[18], 6).'
              '.GetSelector(32, $admin[95], 'dropdown26', $dropdown[26]).'
              '.GetSelector(32, $admin[82], 'dropdown19', $dropdown[19]).'
              '.GetSelector(32, $admin[87], 'dropdown21', $dropdown[21]).'
              '.InputTexte(64, $admin[211], 'dropdown22', $dropdown[22], 16).'
              '.SelectBorderStyles(64, $admin[422], 'dropdown23', $dropdown[23], $borderstyles).'
              '.InputTexte(32, $admin[1343], 'dropdown24', $dropdown[24], 16).'
            </table>
            </div>
          </div>
        </div>
      </div>';
        // Lignes de séparation - Bordures
        echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse19">
          '.$admin[805].' - '.strtoupper(substr($admin[87], 0, -1)).'
        </a>
        <div class="collapse" data-parent="#accordion" id="collapse19">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 4px;">&bull; '.$admin[805].'</td></tr>
              '.GetSelector(32, $admin[150], 'barre0', $barre[0]).'
              '.InputTexte(32, $admin[86], 'barre1', $barre[1], 6).'
              '.SelectBorderStyles(32, $admin[422], 'barre2', $barre[2], $borderstyles).'
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 4px;">&bull; '.strtoupper(substr($admin[87], 0, -1)).' (.bord)'.'</td></tr>
              '.GetSelector(32, $admin[150], 'barre3', $barre[3]).'
              '.InputTexte(32, $admin[86], 'barre4', $barre[4], 6).'
              '.SelectBorderStyles(32, $admin[422], 'barre5', $barre[5], $borderstyles).'
            </table>
            </div>
          </div>
        </div>
      </div>';
        // Autres...
        echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse20">'.strtoupper($admin[1116]).'</a>
        <div class="collapse" data-parent="#accordion" id="collapse20">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="table-responsive-md">
            <table class="table w-100">
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 4px;">&bull; '.strtoupper($admin[1730]).'</td></tr>
              '.InputTexte(32, $admin[386], 'other27', $other[27], 6).'
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 4px;">&bull; '.strtoupper($admin[1671]).'</td></tr>
              '.GetSelector(32, $admin[82], 'other1', $other[1]).'
              '.GetSelector(32, $admin[95], 'other0', $other[0]).'
              '.GetSelector(32, $admin[87], 'other2', $other[2]).'
              '.InputTexte(64, $admin[211], 'other3', $other[3], 6).'
              '.InputTexte(64, $admin[1343], 'other4', $other[4], 16).'
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 4px;">&bull; '.mb_strtoupper($admin[262], $charset).'</td></tr>
              '.InputTexte(32, $admin[1550], 'other7', $other[7], 16).'
              '.InputTexte(32, $admin[1551], 'other8', $other[8], 16).'
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 4px;">&bull; '.strtoupper($admin[1582]).'</td></tr>
              '.GetSelector(32, $admin[82], 'other24', $other[24]).'
              '.GetSelector(32, $admin[95], 'other25', $other[25]).'
              '.InputTexte(32, $admin[386], 'other26', $other[26], 6).'
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 4px;">&bull; '.strtoupper($admin[179]).'</td></tr>
              '.GetSelector(32, $admin[87], 'other9', $other[9]).'
              '.InputTexte(64, $admin[211], 'other10', $other[10], 16).'
              '.SelectBorderStyles(64, $admin[422], 'other11', $other[11], $borderstyles).'
              <tr><td colspan="2" style="text-align:left;padding:0 0 0 32px;">&bull; '.$admin[43].'</td></tr>
              '.GetSelector(64, $admin[82], 'other12', $other[12]).'
              '.InputTexte(92, $admin[1529], 'opacity22', $opacity[22], 6).'
              '.GetSelector(64, $admin[95], 'other13', $other[13]).'
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 4px;">&bull; '.strtoupper($admin[1567]).'</td></tr>
              '.GetSelector(32, $admin[82], 'other23', $other[23]).'
              '.InputTexte(64, $admin[1529], 'opacity23', $opacity[23], 6).'
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 4px;">&bull; '.strtoupper($admin[1569]).'</td></tr>
              '.GetSelector(32, $admin[82], 'other14', $other[14]).'
              '.GetSelector(32, $admin[87], 'other15', $other[15]).'
              '.InputTexte(64, $admin[211], 'other16', $other[16], 6).'
              '.InputTexte(32, $admin[1343], 'other17', $other[17], 16).'
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 4px;">&bull; '.strtoupper($admin[1570]).'</td></tr>
              '.GetSelector(32, $admin[82], 'other18', $other[18]).'
              '.GetSelector(32, $admin[95], 'other19', $other[19]).'
              '.GetSelector(32, $admin[87], 'other20', $other[20]).'
              '.InputTexte(64, $admin[211], 'other21', $other[21], 6).'
              '.InputTexte(64, $admin[1343], 'other22', $other[22], 16).'
              <tr><td colspan="2" style="text-align:left;padding:4px 0 0 4px;">&bull; '.strtoupper($admin[1758]).'</td></tr>
              '.GetSelector(32, $admin[82], 'other28', $other[28]).'
              '.GetSelector(32, $admin[95], 'other29', $other[29]).'
              '.InputTexte(32, $admin[386], 'other30', $other[30], 6).'
            </table>
            </div>
          </div>
        </div>
      </div>
      </div>
      <div class="row quest p-0 mt-2 w-100 m-auto">
	    <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
        <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
      </div>
    </form>
  </fieldset>';
	}
}
echo '
</div>';

btable();
include CHEMIN.'admin/bpage.inc';
?>
