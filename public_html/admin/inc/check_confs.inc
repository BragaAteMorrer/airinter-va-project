<?php
/*******************************************************************************
 *   Checking config is done
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

if (stristr($_SERVER['SCRIPT_NAME'], 'check_confs.inc')) {
    header('location:../index.php');
    die();
}

function CreateStruct() {
    global $site, $serviz, $xposbox, $templates;
    $mettre = '
$site[4]  = stripslashes("'.$site[4].'");  // Nouvelles sur la page d\'accueil
$site[20] = stripslashes("'.$site[20].'"); // Messages Forum sur la page d\'accueil
// site[21] = stripslashes(""); // Inutilisé
$site[29] = stripslashes("'.$site[29].'"); // Hauteur en pixels des nouvelles en page d\'accueil
$site[33] = stripslashes("'.$site[33].'"); // Hauteur en pixels des billets en page d\'accueil
$site[34] = stripslashes("'.$site[34].'"); // Nombre de caractères des posts forum en page d\'accueil
$site[31] = stripslashes("'.$site[31].'"); // Blog sur la page d\'accueil

$serviz[28]  = stripslashes("'.$serviz[28].'");  // Préférences visiteurs
$serviz[38]  = stripslashes("'.$serviz[38].'");  // RSS News ON / OFF
$serviz[74]  = stripslashes("'.$serviz[74].'"); // Cacher les boîtes en page d\'accueil

// Configurations accueil et pages secondaires';

    foreach(array(BOX_LEFT, BOX_CENTER, BOX_RIGHT) as $subarray) {
        $mettre .= "\n// \$xposbox[\"E\"][$subarray]";
        foreach ($xposbox[BOX_EDITO][$subarray] as $key=>$value) {
            $mettre .= '
$xposbox["'.BOX_EDITO.'"]["'.$subarray.'"]['.$key.'] = stripslashes("'.$value.'");';
        }
        $mettre .= "\n";
    }

    $mettre .= "\n".'
// NE PAS SUPPRIMER $templates[0]';
    foreach ($templates as $keyconf=>$valueconf) {
        $mettre .= '
$templates['.$keyconf.'] = stripslashes("'.$valueconf.'");';
    }

    foreach ($templates as $keyconf=>$valueconf) {
        $mettre .= "\n".str_repeat('/', 20).' '.$valueconf.' '.str_repeat('/', 20);
        foreach (array(BOX_LEFT, BOX_RIGHT, BOX_ABOVE, BOX_UNDER) as $subarray) {
            $mettre .= "\n// \$xposbox[$valueconf][$subarray]";
            foreach ($xposbox[$keyconf][$subarray] as $key=>$value) {
                $mettre .= '
$xposbox['.$keyconf.']["'.$subarray.'"]['.$key.'] = stripslashes("'.$value.'");';
            }
            $mettre .= "\n";
        }
        
        foreach (array(BOX_TOP, BOX_BOTTOM) as $array) {
            foreach(array(BOX_LEFT, BOX_CENTER, BOX_RIGHT) as $subarray) {
                $mettre .= "\n// \$xposbox[$valueconf][$array][$subarray]";
                foreach ($xposbox[$keyconf][$array][$subarray] as $key=>$value) {
                    $mettre .= '
$xposbox['.$keyconf.']["'.$array.'"]["'.$subarray.'"]['.$key.'] = stripslashes("'.$value.'");';
                }
                $mettre .= "\n";
            }
        }
    }
    return $mettre."\n?>";
}

if (!is_file(CHEMIN.'skins/'.$pathconf.'/confweb'.INCEXT)) {
    $mettre = '<?php
if (stristr($_SERVER["SCRIPT_NAME"], "confweb.inc")) {
    header("location:../index.php");
    die();
}'.CreateStruct();

    WriteFullDB(CHEMIN.'skins/'.$pathconf.'/confweb'.INCEXT, $mettre);
}
    
?>