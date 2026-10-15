<?php
/*******************************************************************************
 *   Admin Droits
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
 * v6.00.24 (August 12, 2026) : Correction Rights scripts and plugins
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'attribdroits.inc')) {
    header('location:../index.php');
    die();
}

if (!isAdmin($userprefs[1])) {
        $nomzone = $admin[2028];
        include 'access.inc';
        exit();
}

$etape      = import('etape');
$namedroits = import('namedroits');
$maxdiv     = 6;

function GetBeginFieldset($name, $title, $droits = TRUE) {
    global $admin;
    return '
    <fieldset class="w-100 mx-0 my-1 p-1">'.
    legend('<label><input type="checkbox" name="'.$name.'" title="'.$admin[893].'" style="vertical-align:middle"'.
    Checked('true').' onclick="selectAllDroits(this)" /> '.$title.'</label>');
}

function GetDiv($cntdiv, $index, $label, $image, $actif=true) {
    global $drtuser, $maxdiv;
	if (!isset($drtuser[$index])) $drtuser[$index] ='';
    return '
      <div style="vertical-align:top;width:'.intval(100 / 6).'%; text-align:center; margin:2px 0; padding:4px 0; display:'.($actif ? 'inline-block' : 'none').'">
        <i class="adm-'.$image.'" title="'.$label.'"></i><br />
        <label for="image'.$cntdiv.'">'.$label.'</label><br />
        <input id="image'.$cntdiv.'" type="'.($actif ? 'checkbox' : 'hidden').'" name="drtuser'.$index.'"'.Checked($drtuser[$index] == 'on').' />
      </div>';
}

function GetDivPlugin($index, $label, $image, $droit) {
	global $cntdiv, $maxdiv;
    return '
      <div style="vertical-align:top; min-width:'.intval(100 / $maxdiv).'%; text-align:center; margin:2px 0; padding:4px 0; display:inline-block;">
        <img src="plugins/'.$image.'" alt="'.$label.'" title="'.$label.'" /><br />
	    <label for="image'.$cntdiv.'">'.$label.'</label><br />
        <input id="image'.$cntdiv.'" type="checkbox" name="drtuserplgform['.$index.']"'.Checked($droit).' />
      </div>';
}

function GetEndFieldset() {
    return '
      <div class="clearfix"></div>
    </fieldset>
';
}

function LineCB($label, $name, $value) {
    return '
	  <tr>
        <td class="w-40 text-center">
		  <input type="checkbox" id="'.$name.'" name="'.$name.'"'.Checked($value).' />
        </td>
        <td class="w-60 text-left">
          <label for="'.$name.'"><b>&bull;</b>&nbsp;&nbsp;'.$label.'</label>
        </td>
	  </tr>';
}

$pluginlist = ExploreDir('plugins/');
for ($i = 0; $i < count($pluginlist); $i++) {
    if (!is_file('plugins/'.$pluginlist[$i].'/plugin.inc')) {
        unset($pluginlist[$i]);
    }
}
$headinc .= BeginJavascript().'
function selectAllDroits(opt) {
	var option = opt.name;
	var allSelected = opt.checked;
	switch (option) {
		case "droitsGeneral" :
			var dtrName = "drtuser";
			var dtrId = new Array(9, 11, 48);
			break;
		case "droitsConfig" :
			var dtrName = "drtuser";
			var dtrId = new Array(0, 1, 2, 3, 4, 5, 13, 43, 49);
			break;
		case "droitsAdmGen" :
			var dtrName = "drtuser";
			var dtrId = new Array(6, 8, 52, 53, 10, 12, 25, 31, 32, 35, 36, 44, 45, 46, 47);
			break;
		case "droitsStructure" :
			var dtrName = "drtuser";
			var dtrId = new Array(17, 18, 26, 27, 28, 29, 34, 45, 17, 50, 51);
			break;
		case "droitsDatas" :
			var dtrName = "drtuser";
			var dtrId = new Array(14, 15, 16, 19, 20, 21, 22, 23, 24, 33, 37, 39, 40, 41);
			var dtrName = "drtuser";
			break;
		case "droitsPlugin" :
			var dtrName = "";
			var dtrId = new Array();
			var nbPlug = '.count($pluginlist).';
			for(i = 0; i < nbPlug+1; i++) {
				dtrId[i] = "drtuserplgform["+ i +"]";
			}
			break;
		default :
			return false;
	}

	if (dtrId.length != undefined) {
		for (i = 0; i < dtrId.length; i++) {
			document.forms["droitsUser"].elements[dtrName + dtrId[i]].checked = allSelected ? true : false;
		}
	}
	return false;
}'.EndJavascript();

if ($namedroits == '') {
    $etape = 1;
}

$adroits1 = array('modo', 'admin');
$adroits2 = array('webm', 'admin');
$adroits3 = array('admin');
$adroits4 = array('webm', 'admin', 'redac1', 'redac2');
$adroits5 = array('admin', 'redac1', 'redac2');
$adroits6 = array('modo', 'admin', 'redac1', 'redac2');

if ($etape == 3) {
	$drtuser9  = '';
    $drtuser30 = import('drtuser30');
    $drtuser38 = import('drtuser38');
    $drtuser42 = import('drtuser42');
			
	$modifdroits42 = 0;
	$anciendrtuser42 = '';
if (FileDBExist(REDACREP.'redacaccessdossier/accessdossier'.$namedroits.INCEXT))
	{
		$anciendrtuser42 = "redac1";
		$modifdroits42 = 1;
	}
	if (!isset($drtuser[25])) $drtuser[25] = '';	
    $drtuser0  = in_array($drtuser42, $adroits2) ? import('drtuser0')  : '';
    $drtuser1  = in_array($drtuser42, $adroits2) ? import('drtuser1')  : '';
    $drtuser2  = in_array($drtuser42, $adroits2) ? import('drtuser2')  : '';
    $drtuser3  = in_array($drtuser42, $adroits2) ? import('drtuser3')  : '';
    $drtuser4  = in_array($drtuser42, $adroits2) ? import('drtuser4')  : '';
    $drtuser5  = in_array($drtuser42, $adroits4) ? import('drtuser5')  : '';
    $drtuser6  = in_array($drtuser42, $adroits2) ? import('drtuser6')  : '';
    $drtuser7  = $serviz[13] == 'on' && in_array($drtuser42, $adroits3) ? import('drtuser7')  : '';
    $drtuser8  = in_array($drtuser42, $adroits2) ? import('drtuser8')  : '';
    $drtuser9  = $serviz[42] == 'on' && $serviz[176] == $namedroits && in_array($drtuser42, $adroits3) ? import('drtuser9')  : '';
    $drtuser10 = in_array($drtuser42, $adroits2) ? import('drtuser10') : '';
    $drtuser11 = in_array($drtuser42, $adroits2) ? import('drtuser11') : '';
    $drtuser12 = $serviz[42] == 'on' && in_array($drtuser42, $adroits3) ? import('drtuser12') : '';
    $drtuser13 = $serviz[99] == 'on' && in_array($drtuser42, $adroits2) ? import('drtuser13') : '';
    $drtuser14 = $serviz[8] == 'on' && in_array($drtuser42, $adroits6) ? import('drtuser14') : '';
    $drtuser15 = in_array($drtuser42, $adroits5) ? import('drtuser15') : '';
    $drtuser16 = $serviz[29] != '2' && in_array($drtuser42, $adroits1) ? import('drtuser16') : '';
    $drtuser17 = in_array($drtuser42, $adroits4) ? import('drtuser17') : '';
    $drtuser18 = $serviz[9] == 'on' && in_array($drtuser42, $adroits4) ? import('drtuser18') : '';
    $drtuser19 = $serviz[10] == 'on' && in_array($drtuser42, $adroits2) ? import('drtuser19') : '';
    $drtuser20 = $serviz[11] == 'on' && in_array($drtuser42, $adroits2) ? import('drtuser20') : '';
    $drtuser21 = $serviz[14] == 'on' && in_array($drtuser42, $adroits4) ? import('drtuser21') : '';
    $drtuser22 = $serviz[12] == 'on' && in_array($drtuser42, $adroits1) ? import('drtuser22') : '';
    $drtuser23 = $serviz[13] == 'on' && in_array($drtuser42, $adroits1) ? import('drtuser23') : '';
    $drtuser24 = $serviz[13] == 'on' && in_array($drtuser42, $adroits3) ? import('drtuser24') : '';
	$drtuser25 = in_array($drtuser42, $adroits2) ? 'on' : '';
    $drtuser26 = in_array($drtuser42, $adroits4) ? import('drtuser26') : '';
    $drtuser27 = $serviz[19] == 'on' && in_array($drtuser42, $adroits2) ? import('drtuser27') : '';
    $drtuser28 = $serviz[0] == 'on' && in_array($drtuser42, $adroits4) ? import('drtuser28') : '';
    $drtuser29 = in_array($drtuser42, $adroits2) ? import('drtuser29') : '';
    $drtuser31 = in_array($drtuser42, $adroits2) ? import('drtuser31') : '';
    $drtuser32 = in_array($drtuser42, $adroits2) ? import('drtuser32') : '';
    $drtuser33 = $serviz[36] != '' && in_array($drtuser42, $adroits2) ? import('drtuser33') : '';
    $drtuser34 = $serviz[27] == 'on' && in_array($drtuser42, $adroits2) ? import('drtuser34') : '';
    $drtuser35 = in_array($drtuser42, $adroits2) ? import('drtuser35') : '';
    $drtuser36 = in_array($drtuser42, $adroits2) ? import('drtuser36') : '';
    $drtuser37 = $serviz[47] == 'on' && in_array($drtuser42, $adroits4) ? import('drtuser37') : '';
    $drtuser39 = $serviz[53] == 'on' && in_array($drtuser42, $adroits6) ? import('drtuser39') : '';
    $drtuser40 = $serviz[53] == 'on' && in_array($drtuser42, $adroits1) ? import('drtuser40') : '';
    $drtuser41 = $serviz[53] == 'on' && in_array($drtuser42, $adroits2) ? import('drtuser41') : '';
    $drtuser43 = in_array($drtuser42, $adroits2) ? 'on' : '';
    $drtuser44 = in_array($drtuser42, $adroits5) ? 'on' : '';
    $drtuser45 = in_array($drtuser42, $adroits2) ? 'on' : '';
    $drtuser46 = in_array($drtuser42, $adroits2) ? import('drtuser46') : '';
    $drtuser47 = in_array($drtuser42, $adroits2) ? import('drtuser47') : '';
    $drtuser48 = in_array($drtuser42, $adroits2) ? import('drtuser48') : '';
    $drtuser49 = in_array($drtuser42, $adroits2) ? import('drtuser49') : '';
    $drtuser50 = in_array($drtuser42, $adroits2) ? import('drtuser50') : '';
	$drtuser51 = in_array($drtuser42, $adroits2) ? import('drtuser51') : '';
	$drtuser52 = in_array($drtuser42, $adroits2) ? import('drtuser52') : '';
	$drtuser53 = in_array($drtuser42, $adroits2) ? import('drtuser53') : '';
	$drtusersv0 = import('drtusersv0'); // e-mail publication article
	$drtusersv1 = import('drtusersv1'); // e-mail publication réaction article
	$drtusersv2 = import('drtusersv2'); // e-mail publication nouvelle
	$drtusersv3 = import('drtusersv3'); // e-mail publication blog
	$drtusersv4 = import('drtusersv4'); // e-mail publication commentaire blog
	$drtusersv5 = import('drtusersv5'); // e-mail publication livre d'or
	$drtusersv6 = import('drtusersv6'); // e-mail publication forum
    $filedroit = REDACREP.$namedroits.INCEXT;
    $mettre    = "<?php
if (stristr(\$_SERVER['SCRIPT_NAME'], '".$namedroits.".inc')) {
    header('location:../index.php');
    die();
}

\$drtuser[0]  = \"".$drtuser0."\";  // Accès à config générale
\$drtuser[1]  = \"".$drtuser1."\";  // Accès à config thèmes
\$drtuser[2]  = \"".$drtuser2."\";  // Accès à config accueil
\$drtuser[3]  = \"".$drtuser3."\";  // Accès à config section
\$drtuser[4]  = \"".$drtuser4."\";  // Accès à Config services
\$drtuser[5]  = \"".$drtuser5."\";  // Accès à Config boites
\$drtuser[6]  = \"".$drtuser6."\";  // Accès à Maintenance
\$drtuser[7]  = \"".$drtuser7."\";  // Accès à Archivage Forum
\$drtuser[8]  = \"".$drtuser8."\";  // Accès à Contrôle d'intégrité DB
\$drtuser[9]  = \"".$drtuser9."\";  // ".($drtuser9 == 'on' ? 'Accès à Mot de passe Collaborateur' : 'Inutilisé')."
\$drtuser[10] = \"".$drtuser10."\"; // Accès à Config zone membres
\$drtuser[11] = \"".$drtuser11."\"; // Accès à Travaux
\$drtuser[12] = \"".$drtuser12."\"; // Accès à Droits
\$drtuser[13] = \"".$drtuser13."\"; // Accès à Contact avancé
\$drtuser[14] = \"".$drtuser14."\"; // Accès à Nouvelles
\$drtuser[15] = \"".$drtuser15."\"; // Accès à Articles
\$drtuser[16] = \"".$drtuser16."\"; // Accès à Réactions Articles
\$drtuser[17] = \"".$drtuser17."\"; // Accès à Boîtes menus
\$drtuser[18] = \"".$drtuser18."\"; // Accès à Photos
\$drtuser[19] = \"".$drtuser19."\"; // Accès à Téléchargements
\$drtuser[20] = \"".$drtuser20."\"; // Accès à Liens
\$drtuser[21] = \"".$drtuser21."\"; // Accès à FAQ
\$drtuser[22] = \"".$drtuser22."\"; // Accès à Livre d'or
\$drtuser[23] = \"".$drtuser23."\"; // Accès à Forum
\$drtuser[24] = \"".$drtuser24."\"; // Accès à Config Forum
\$drtuser[25] = \"".$drtuser25."\"; // Accès aux fichiers langues personnalisés
\$drtuser[26] = \"".$drtuser26."\"; // Accès à Boites libres
\$drtuser[27] = \"".$drtuser27."\"; // Accès à Bannières
\$drtuser[28] = \"".$drtuser28."\"; // Accès à Citations
\$drtuser[29] = \"".$drtuser29."\"; // Accès à Menu icônes
\$drtuser[30] = \"".$drtuser30."\"; // Statut à afficher
\$drtuser[31] = \"".$drtuser31."\"; // Accès à Compteur
\$drtuser[32] = \"".$drtuser32."\"; // Accès à Statistiques
\$drtuser[33] = \"".$drtuser33."\"; // Accès à Newsletter
\$drtuser[34] = \"".$drtuser34."\"; // Accès à Nouvelles RSS
\$drtuser[35] = \"".$drtuser35."\"; // Accès à Logs
\$drtuser[36] = \"".$drtuser36."\"; // Accès à Fichiers
\$drtuser[37] = \"".$drtuser37."\"; // Accès à Agenda
\$drtuser[38] = \"".$drtuser38."\"; // Password
\$drtuser[39] = \"".$drtuser39."\"; // Accès à Blogs
\$drtuser[40] = \"".$drtuser40."\"; // Accès à Réactions Blogs
\$drtuser[41] = \"".$drtuser41."\"; // Accès à Blog RSS
\$drtuser[42] = \"".$drtuser42."\"; // Fonction
\$drtuser[43] = \"".$drtuser43."\"; // Accès à Config Style
\$drtuser[44] = \"".$drtuser44."\"; // Accès à Import
\$drtuser[45] = \"".$drtuser45."\"; // Accès à Sécurité
\$drtuser[46] = \"".$drtuser46."\"; // Accès à Mise à jour / Extension
\$drtuser[47] = \"".$drtuser47."\"; // Accès à Sauvegarde du site
\$drtuser[48] = \"".$drtuser48."\"; // Accès à Version de GuppY
\$drtuser[49] = \"".$drtuser49."\"; // Accès à Config Recherche
\$drtuser[50] = \"".$drtuser50."\"; // Accès à Réseaux sociaux
\$drtuser[51] = \"".$drtuser51."\"; // Accès à Déclaration RGPD
\$drtuser[52] = \"".$drtuser52."\"; // Accès à Archivage
\$drtuser[53] = \"".$drtuser53."\"; // Accès à épuration archivage
\$drtusersv[0] = \"".$drtusersv0."\"; // e-mail publication article
\$drtusersv[1] = \"".$drtusersv1."\"; // e-mail publication réaction article
\$drtusersv[2] = \"".$drtusersv2."\"; // e-mail publication nouvelle
\$drtusersv[3] = \"".$drtusersv3."\"; // e-mail publication blog
\$drtusersv[4] = \"".$drtusersv4."\"; // e-mail publication commentaire blog
\$drtusersv[5] = \"".$drtusersv5."\"; // e-mail publication livre d\'or
\$drtusersv[6] = \"".$drtusersv6."\"; // e-mail publication forum";

    $drtuserplgform = import('drtuserplgform', 'POST', FALSE);
    $slistplug      = import('listplug');
    $listplug       = explode("::", $slistplug);
    foreach ($listplug as $i => $value) {
		if (!empty($drtuserplgform[$i]))
        $mettre .= "
\$drtuserplg['$value'] = \"".(in_array($drtuser42, $adroits4) ? $drtuserplgform[$i] : '')."\"; // Accès plugin $value";
    }
    $mettre .='
?>';
    WriteFullDB($filedroit, $mettre);
	if (!isset($droitaccegroupredac)) $droitaccegroupredac = '';
	if ($modifdroits42 == 1 && ($drtuser42 != "redac1" && $drtuser42 != "redac2"))
	{
			if ($droitaccegroupredac == 'on')
			{					
				if ($anciendrtuser42 == "redac1" || $anciendrtuser42 == "redac2")
				{					
					if (FileDBExist(REDACREP.'redacaccessdossier/accessdossier'.$namedroits.INCEXT)) 
					{
						include REDACREP.'redacaccessdossier/accessdossier'.$namedroits.INCEXT;
						$accessRepUpload = explode(',',$drtaccessRepUpload);       //Nom des répertoires à la racine de Guppy accessibles pour le fichier Upload
						$accessRepUploadImage = explode(',',$drtaccessRepUploadImage);  //Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion d'Images.(accès avec variable uptype='Image')
						$accessRepUploadLink = explode(',',$drtaccessRepUploadLink);  //Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion de Liens (accès avec variable uptype='Link')
						$accessRepUploadMedia = explode(',',$drtaccessRepUploadMedia);
						$drtaccessRepUpload = '';
						for ($i =0; $i < count($accessRepUpload); $i++)
						{
							if (strpos($accessRepUpload[$i], $namedroits))
							{
								$drtaccessRepUpload = $accessRepUpload[$i];
							}
								
						}
						$drtaccessRepUploadImage = '';
						for ($i =0; $i < count($accessRepUploadImage)-1; $i++)
						{
							if (strpos($accessRepUploadImage[$i], $namedroits))
								$drtaccessRepUploadImage .= $accessRepUploadImage[$i];
						}
						$drtaccessRepUploadLink = '';
						for ($i =0; $i < count($accessRepUploadLink)-1; $i++)
						{
							if (strpos($accessRepUploadLink[$i], $namedroits))
								$drtaccessRepUploadLink .= $accessRepUploadLink[$i];
						}
						$drtaccessRepUploadMedia = '';
						for ($i =0; $i < count($accessRepUploadMedia)-1; $i++)
						{
							if (strpos($accessRepUploadMedia[$i], $namedroits))
								$drtaccessRepUploadMedia .= $accessRepUploadMedia[$i];
						}
// Copie des dossier contenant  les Images, les videos etc ...
						@recursiveCopy(CHEMIN.$drtaccessRepUploadMedia, CHEMIN.'redacgroupdocs/'.$drtaccessRepUploadMedia);
// Suppression du dossier du Redacteur
						@RecursiveDeleteDirComplet(CHEMIN.$drtaccessRepUploadMedia);					
// Suppression des dossiers					
						DestroyDBFile(REDACREP.'redacaccessdossier/accessdossier'.$namedroits.INCEXT);
/*						echo 'nom dossier drtaccessRepUpload: '.$drtaccessRepUpload.'<br />';	
						echo 'nom dossier drtaccessRepUploadImage: '.$drtaccessRepUploadImage.'<br />';	
						echo 'nom dossier drtaccessRepUploadLink: '.$drtaccessRepUploadLink.'<br />';
						echo 'nom dossier drtaccessRepUploadMedia: '.$drtaccessRepUploadMedia.'<br />';
*/						
						$dbw    = SelectDBFieldsByNotStatus(ReadDBFields(DOCID),'d');
						for ($i = 0; $i < count($dbw); $i++) 
						{					
							$modif = 0;
							ReadDoc($dbw[$i][1]);	
							$temp   = explode('|', $author);
							$authorcontrol = $temp[0];
							$id = $dbw[$i][1];
							if ($authorcontrol == $namedroits) 
							{					
								if (strpos($fieldc1, $namedroits)) {
									$contenu = stripslashes($fieldc1);
									$contenu = str_replace('redac_'.$namedroits.'img', 'redacgroupdocs/redac_'.$namedroits.'img', $contenu);
									$fieldc1 = $contenu;
									$modif = 1;	
								}	
								if (strpos($fieldc2, $namedroits)) {
									$contenu = stripslashes($fieldc2);
									$contenu = str_replace('redac_'.$namedroits.'img', 'redacgroupdocs/redac_'.$namedroits.'img', $contenu);
									$fieldc2 = $contenu;
									$modif = 1;	
								}	
								if ($modif != 0)
								{
										$data[0] = $type;
										$data[1] = $id;
										$data[2] = $status;
										$data[3] = $creadate;
										$data[4] = $moddate;
										$data[5] = $author;
										$data[6] = $email;
										$data[7] = $fielda1;
										$data[8]  = $fielda2;
										$data[9]  = $fieldb1;
										$data[10] = $fieldb2;
										$data[11] = $fieldc1;
										$data[12] = $fieldc2;
										$data[13] = $fieldd1;
										$data[14] = $fieldd2;
										$data[15] = $fieldweb;
										$data[16] = $fieldmail;
										$data[17] = $fieldmod; 
										ActionOnFields('mod', $data);			
								}
							}
						}

					} // fin acces dossiers pour redac			
				}
			} // fin d'existence du dossier droits acces membre   		
	}
	if (!isset($droitaccegroupredac)) $droitaccegroupredac = '';
	if ($droitaccegroupredac == 'on')
	{
		if ($drtuser42 == "redac1" || $drtuser42 == "redac2")
		{
			$accessRepUpload = 'redac_'.$namedroits.'img,redacgroupdocs';
			$accessRepUploadImage = 'redac_'.$namedroits.'img,redacgroupdocs';
			$accessRepUploadLink = 'redac_'.$namedroits.'img,redacgroupdocs';
			$accessRepUploadMedia = 'redac_'.$namedroits.'img,redacgroupdocs';
		
		
		
$mettre    = "<?php
if (stristr(\$_SERVER['SCRIPT_NAME'], 'accessdossier".$namedroits.".inc')) {
    header('location:../index.php');
    die();
}

\$drtaccessRepUpload = \"".$accessRepUpload ."\"; //Nom des répertoires à la racine de Guppy accessibles pour le fichier Upload
\$drtaccessRepUploadImage = \"".$accessRepUploadImage ."\"; //Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion d'Images.(accès avec variable uptype='Image')
\$drtaccessRepUploadLink = \"".$accessRepUploadLink ."\"; //Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion de Liens (accès avec variable uptype='Link')
\$drtaccessRepUploadMedia = \"".$accessRepUploadMedia."\"; //Accès aux répertoires à la racine de Guppy pour les fichiers Audio et Video (accès avec variable uptype='Media')";                                                        //Attention: vous devez choisir les répertoires uniquement parmi la liste des répertoires contenus dans la variable $accessRepUpload ci-dessus
$mettre .='
?>';
			if (!is_dir(CHEMIN.'admin/redac/redacaccessdossier')) // Generation des access utilisés en upload.php
			{
				@mkdir(CHEMIN.'admin/redac/redacaccessdossier');
				SetChmod(CHEMIN.'admin/redac/redacaccessdossier');
				@copy(CHEMIN.'admin/redac/index.php', CHEMIN.'admin/redac/redacaccessdossier/index.php');
				SetChmod(CHEMIN.'admin/redac/redacaccessdossier/index.php');
			}
			if (!is_dir(CHEMIN.'redac_'.$namedroits.'img')) // Creation dossier acces en Upload Image, AUdio,Link et média
			{
				@mkdir(CHEMIN.'redac_'.$namedroits.'img');
				SetChmod(CHEMIN.'redac_'.$namedroits.'img');
				@copy(CHEMIN.'img/index.php', CHEMIN.'redac_'.$namedroits.'img/index.php');	
				SetChmod(CHEMIN.'redac_'.$namedroits.'img/index.php');
			}
			if (!is_dir(CHEMIN.'redacgroupdocs')) // Creation dossier Redacgrupdocs pour tous les rédacteurs
			{
				@mkdir(CHEMIN.'redacgroupdocs');
				SetChmod(CHEMIN.'redacgroupdocs');
				@copy(CHEMIN.'img/index.php', CHEMIN.'redacgroupdocs/index.php');	
				SetChmod(CHEMIN.'redacgroupdocs/index.php');
			}
			$filedroitdossierredac = REDACREP.'redacaccessdossier/accessdossier'.$namedroits.INCEXT;
			WriteFullDB($filedroitdossierredac, $mettre);
		} // fin acces dossiers pour redac
		else
		{
			if (file_exists(REDACREP.'redacaccessdossier/accessdossier'.$namedroits.INCEXT))
				DestroyDBFile(REDACREP.'redacaccessdossier/accessdossier'.$namedroits.INCEXT);	
			
		}
	}
	else
	{
		if (file_exists(REDACREP.'redacaccessdossier/accessdossier'.$namedroits.INCEXT))
			DestroyDBFile(REDACREP.'redacaccessdossier/accessdossier'.$namedroits.INCEXT);	
		
	}

    $adm   = array($namedroits, $drtuser42, $drtuser23, $drtuser30);
    $dbadm = ReadDBFields(ADMINS);
    for ($k = 0; $k < count($dbadm); $k++) {
        if ($dbadm[$k][0] == $namedroits) {
            unset($dbadm);
            DeleteDBFieldById(ADMINS, $id = $k);
            break;
        }
    }
    AppendDBFields(ADMINS, $adm);
}

################################################################################
if ($etape == 1) {

	$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[819]);
	include CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
	htable($admin[819], '100%');
    echo '
<p class="text-center">'.ToHelp($lng, '&pg=982&mnuid=937&tconfig=0#z2').$admin[822].'</p>
<hr />
<form action="admin.php?lng='.$lng.'&amp;pg=attribdroits" method="post">
  <input type="hidden" name="etape" value="2" />
  <div class="m-auto text-center" style="max-width: 200px;">
    <select class="form-control" name="namedroits" onchange="submit();">';
	$dbadmins = ReadDBFields(ADMINS);
	$dbadms   = array();
	foreach ($dbadmins as $row) 
	{
		$dbadms[] = $row[0];
	}
    $dbusersmsg = array();
    $file_users = opendir(MSGREP);
    while ($nomfichier = readdir($file_users)) {
        if (substr($nomfichier, -3) == 'dtb') {
            $dbusersmsg[] = str_replace(substr($nomfichier, -4), '', $nomfichier);
        }
    }
    closedir($file_users);
    @usort($dbusersmsg, 'AsciiCompare');
    echo '
      <option value="" select="selected">'.$admin[823].'</option>';
    foreach ($dbusersmsg as $user) {
        if ($user != $serviz[31] && $user != $userprefs[1] && $user != $user[0] && !in_array($user, $dbadms)) {	
            echo '
      <option value="'.$user.'">'.$user.'</option>';
        }
    }
    echo '
        </select>
	<noscript>
	  <br />
	  '.SubmitButton($admin[519]).'
	</noscript>
  </div>
  <br />&nbsp;
</form>';
}
################################################################################
elseif ($etape == 2 || $etape == 3) {
	$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > <a href="admin.php?lng='.$lng.'&amp;pg=attribdroits">'.str_replace('Admin - ', '', $admin[820]).'</a> > <a href="admin.php?lng='.$lng.'&amp;pg=gestredac">'.str_replace('Admin - ', '', $admin[819]).'</a> > '.$admin[820].' '.$namedroits;
	include CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
	htable($admin[820], '100%');
    if (FileDBExist(REDACREP.$namedroits.INCEXT)) {
        include REDACREP.$namedroits.INCEXT;
    }
    else {
        if ($etape == 3) {
            echo BeginJavascript().'
alert("'.addslashes('ERROR : the file '.REDACREP.$namedroits.INCEXT.' does not exists : STOP !').'");
            '.EndJavascript();
            exit();
        } else {
            $drtuser = array();
            for ($i = 0; $i <= 50; $i++) $drtuser[] = '';
            $drtuserplg = array();
            foreach ($pluginlist as $plugin) $drtuserplg[$plugin] = '';
        }
    }

   echo '
<div class="text-center">
  <br />'.ToHelp($lng, '&pg=982&mnuid=937&tconfig=0#z2').$admin[824].' <b>'.$namedroits.'</b><br />'.$admin[825].'
</div>
<hr />
<form  name="droitsUser" action="admin.php?lng='.$lng.'&amp;pg=attribdroits" method="post">
  <input type="hidden" name="namedroits" value="'.$namedroits.'" />
  <input type="hidden" name="etape" value="3" />';
    if (is_file(MSGREP.$namedroits.DBEXT)) {
        $userdb = ReadDBFields(MSGREP.$namedroits.DBEXT);
        $drtuser[38] = $userdb[0][0];
    }
    echo '
  <p class="text-center">
    <b>'.$admin[827].' : </b> '.$namedroits.'
    <br />
    <input type ="hidden" name="drtuser38" value="'.$drtuser[38].'" size="15" />
    <b> '.$admin[1612].' : </b>
    <select class="form-control mx-auto" style="max-width: 200px;" name="drtuser42" onchange="this.form.submit()">
      <option value="">'.$admin[1312].'</option>
      <option value="redac1"'.Selected($drtuser[42] == "redac1").'>'.$admin[715].'1</option>
      <option value="redac2"'.Selected($drtuser[42] == "redac2").'>'.$admin[715].'2</option>
      <option value="modo"'.Selected($drtuser[42] == "modo").'>'.$admin[712].'</option>
      <option value="webm"'.Selected($drtuser[42] == "webm").'>'.$admin[718].'</option>
      <option value="admin"'.Selected($drtuser[42] == "admin").'>'.$admin[1261].'</option>
    </select>&nbsp;
    <noscript>
	  <span style="padding:4px;">'.SubmitButton($admin[519]).'</span>
    </noscript>
  </p>
  <p class="text-center">
    <b>'.$admin[1440].'</b>&nbsp;
    <input type="text" class="mx-auto form-control texte" style="max-width: 180px;" name="drtuser30" size="20" value="'.$drtuser[30].'" />
  </p>
  <hr />';
  
	$i = 0;
    echo 
GetBeginFieldset('droitsGeneral', $admin[1355], 
	$drtuser[48] && ($drtuser[9] || $serviz[176] != $namedroits) && $drtuser[11]).
GetDiv($i++, 48, $admin[904], 'update', in_array($drtuser[42], $adroits2) ? true : false).
GetDiv($i++, 9, $admin[1554], 'protect', $serviz[42] == 'on' && in_array($drtuser[42], $adroits3) && $serviz[176] == $namedroits ? true : false).
GetDiv($i++, 11, $admin[817], 'maintenance', in_array($drtuser[42], $adroits2) ? true : false).
GetEndFieldset();

	echo 
GetBeginFieldset('droitsConfig', $admin[1356],
    $drtuser[0] && $drtuser[3] && $drtuser[4] && ($drtuser[13] || $serviz[99] != '') && 
	$drtuser[49] && ($drtuser[24] || $serviz[13] != '') && $drtuser[1] && $drtuser[5] && $drtuser[2] && $drtuser[43]).
GetDiv($i++,0, $admin[7], 'config1', in_array($drtuser[42], $adroits2) ? true : false).
GetDiv($i++, 4, $admin[169], 'config5', in_array($drtuser[42], $adroits2) ? true : false).
GetDiv($i++, 3, $admin[166], 'config4', in_array($drtuser[42], $adroits2) ? true : false).
GetDiv($i++, 13, $admin[1183], 'contact', $serviz[99] == 'on' && in_array($drtuser[42], $adroits2) ? true : false).
GetDiv($i++, 49, $admin[1264], 'search', in_array($drtuser[42], $adroits2) ? true : false).
GetDiv($i++, 1, $admin[888], 'config2', in_array($drtuser[42], $adroits2) ? true : false).
GetDiv($i++, 5, $admin[346], 'config6', in_array($drtuser[42], $adroits4) && $namedroits != 'redac1' ? true : false).
GetDiv($i++, 2, $admin[469], 'config3',in_array($drtuser[42], $adroits2) ? true : false).
GetDiv($i++, 43, $admin[719], 'css', in_array($drtuser[42], $adroits2) ? true : false).
GetEndFieldset();
if (!isset($drtuser[25])) $drtuser[25] = '';
echo 
	GetBeginFieldset('droitsAdmGen', $admin[881],
		$drtuser[10] && ($drtuser[12] || $serviz[42] != '') && $drtuser[8] && $drtuser[6] && $drtuser[25] &&
		($drtuser[52] || $drtuser[53] || $serviz[13] != '') && $drtuser[46] && $drtuser[47] && $drtuser[44] && $drtuser[36] && 
		$drtuser[31] && $drtuser[32] && $drtuser[35]).
	GetDiv($i++, 10, $admin[816], 'members1', in_array($drtuser[42], $adroits2) ? true : false).
	GetDiv($i++, 12, $admin[818], 'droits1', $serviz[42] == 'on' && in_array($drtuser[42], $adroits3)  && $serviz[176] == $namedroits || $serviz[31] == $namedroits ? true : false).
	GetDiv($i++, 8, $admin[278], 'dbcheck', in_array($drtuser[42], $adroits2) ? true : false).
	GetDiv($i++, 6, $admin[170], 'maintain', in_array($drtuser[42], $adroits2) ? true : false).
	GetDiv($i++, 52, $admin[577], 'archive', $serviz[13] == 'on' && in_array($drtuser[42], $adroits2) ? true : false).
	GetDiv($i++, 53, $admin[1844], 'archive', $serviz[13] == 'on' && in_array($drtuser[42], $adroits2) ? true : false).
	GetDiv($i++, 46, $admin[1126], 'install', in_array($drtuser[42], $adroits3)  && $serviz[176] == $namedroits || $serviz[31] == $namedroits ? true : false).
	GetDiv($i++, 47, $admin[1245], 'save', in_array($drtuser[42], $adroits2) ? true : false).
	GetDiv($i++, 44, $admin[1093], 'import', in_array($drtuser[42], $adroits5) && $namedroits != 'redac1' ? true : false).
	GetDiv($i++, 36, $admin[171], 'upload', in_array($drtuser[42], $adroits2) ? true : false).
	GetDiv($i++, 31, $admin[17], 'counter1', in_array($drtuser[42], $adroits2) ? true : false).
	GetDiv($i++, 32, $admin[212], 'stats3', in_array($drtuser[42], $adroits2) ? true : false).
	GetDiv($i++, 35, $admin[553], 'eye1', in_array($drtuser[42], $adroits2) ? true : false).
	GetDiv($i++, 25, $admin[957], 'article', in_array($drtuser[42], $adroits2) ? true : false).
	GetEndFieldset();

	echo 
GetBeginFieldset('droitsStructure', $admin[1357],
	$drtuser[26] && ($drtuser[34] || $serviz[27] != '') && ($drtuser[27] || $serviz[19] != '') &&
	($drtuser[28] || $serviz[0] != '') && $drtuser[29] && $drtuser[17] && $drtuser[45] && $drtuser[50] && $drtuser[51] &&
    ($drtuser[18] || $serviz[9] != '')).
GetDiv($i++, 26, $admin[270], 'freebox', in_array($drtuser[42], $adroits4) ? true : false).
GetDiv($i++, 34, $admin[602], 'newsrss', $serviz[27] == 'on' && in_array($drtuser[42], $adroits2) ? true : false).
GetDiv($i++, 27, $admin[263], 'banner', $serviz[19] == 'on' && in_array($drtuser[42], $adroits2) ? true : false).
GetDiv($i++, 28, $admin[6], 'think', $serviz[0] == 'on' && in_array($drtuser[42], $adroits4) ? true : false).
GetDiv($i++, 29, $admin[876], 'menuico', in_array($drtuser[42], $adroits2) ? true : false).
GetDiv($i++, 17, $admin[1216], 'menu1', in_array($drtuser[42], $adroits4) ? true : false).
GetDiv($i++, 50, $admin[336], 'socnet2', in_array($drtuser[42], $adroits2) ? true : false).
GetDiv($i++, 51, $admin[1776], 'socnet1', in_array($drtuser[42], $adroits2) ? true : false).
GetDiv($i++, 45, $admin[1811], 'eye2', in_array($drtuser[45], $adroits2) ? true : false).
GetDiv($i++, 45, $admin[1807], 'eye2', in_array($drtuser[45], $adroits2) ? true : false).
GetDiv($i++, 18, $admin[322], 'photo1', $serviz[9] == 'on' && in_array($drtuser[42], $adroits4) ? true : false).
GetEndFieldset();

	echo 
GetBeginFieldset('droitsDatas', $admin[882],
    ($drtuser[14] || $serviz[8] != '') && $drtuser[15] && ($drtuser[16] || $serviz[29] != '2') && 
	($drtuser[19] || $serviz[10] != '') && ($drtuser[20] || $serviz[11] != '') && ($drtuser[21] || $serviz[14] != '') && 
	($drtuser[22] || $serviz[12] != '') && ($drtuser[37] || $serviz[47] != '') && ($drtuser[23] || $serviz[13] != '') && 
	($drtuser[39] || $serviz[53] != '') && ($drtuser[40] || $serviz[53] != '') && ($drtuser[41] || $serviz[53] != '') && 
	($drtuser[33] || $serviz[36] != '')).
GetDiv($i++, 14, $admin[13], 'news', $serviz[8] == 'on' && in_array($drtuser[42], $adroits6) ? true : false).
GetDiv($i++, 15, $admin[5], 'article', in_array($drtuser[42], $adroits5) ? true : false).
GetDiv($i++, 16, $admin[476], 'react', $serviz[29] != '2' && in_array($drtuser[42], $adroits1) ? true : false).
GetDiv($i++, 19, $admin[18], 'download', $serviz[10] == 'on' && in_array($drtuser[42], $adroits2) ? true : false).
GetDiv($i++, 20, $admin[10], 'links', $serviz[11] == 'on' && in_array($drtuser[42], $adroits2) ? true : false).
GetDiv($i++, 21, $admin[206], 'faq', $serviz[14] == 'on' && in_array($drtuser[42], $adroits4) ? true : false).
GetDiv($i++, 22, $admin[11], 'guestbook', $serviz[12] == 'on' && in_array($drtuser[42], $adroits1) ? true : false).
GetDiv($i++, 37, $admin[624], 'agenda', $serviz[47] == 'on' && in_array($drtuser[42], $adroits4) ? true : false).
GetDiv($i++, 24, $admin[253], 'config71', $serviz[13] == 'on' && in_array($drtuser[42], $adroits3) ? true : false).
GetDiv($i++, 23, $admin[9], 'forum', $serviz[13] == 'on' && in_array($drtuser[42], $adroits1) ? true : false).
GetDiv($i++, 39, $admin[770], 'blog', $serviz[53] == 'on' && in_array($drtuser[42], $adroits6) ? true : false).
GetDiv($i++, 40, $admin[776], 'reblog', $serviz[53] == 'on' && in_array($drtuser[42], $adroits1) ? true : false).
GetDiv($i++, 41, $admin[791], 'blogrss', $serviz[53] == 'on' && in_array($drtuser[42], $adroits2) ? true : false).
GetDiv($i++, 33, $admin[546], 'newsletter3', $serviz[36] != '' && in_array($drtuser[42], $adroits2) ? true : false).
GetEndFieldset();

    if (!empty($pluginlist) && in_array($drtuser[42], $adroits4)) {
		$cntdiv = 0;
        echo 
GetBeginFieldset('droitsPlugin', $admin[883], false);
        $i = 0;
        $dtrAllPlugs = TRUE;
        foreach ($pluginlist as $plugin) {
            include 'plugins/'.$plugin.'/plugin.inc';
            echo GetDivPlugin($i++, $plugin_admin_name, $plugin_admin_icon, 
              FileDBExist(REDACREP.$namedroits.INCEXT) && (isset($drtuserplg[$plugin]) ? $drtuserplg[$plugin] == 'on' : false));
            $dtrAllPlugs = $dtrAllPlugs && (isset($drtuserplg[$plugin]) ? $drtuserplg[$plugin] == 'on' : false);
        }

        if ($dtrAllPlugs) {
        	echo BeginJavascript().'
document.forms["droitsUser"].elements["droitsPlugin"].checked = true;'.EndJavascript();
        }
    }
    echo GetEndFieldset().'
  <input type="hidden" name="listplug" value="'.implode('::', $pluginlist).'" />';
	echo '
  <fieldset class="w-100 mx-0 mt-2 p-1">'.legend($admin[229]).'
    <div class="table-responsive-md">
    <table class="table">
	'.LineCB($admin[1447], 'drtusersv0', @$drtusersv[0]).'
	'.LineCB($admin[473],  'drtusersv1', @$drtusersv[1]).'
	'.LineCB($admin[235],  'drtusersv2', @$drtusersv[2]).'
	'.LineCB($admin[786],  'drtusersv3', @$drtusersv[3]).'
	'.LineCB($admin[1424], 'drtusersv4', @$drtusersv[4]).'
	'.LineCB($admin[236],  'drtusersv5', @$drtusersv[5]).'
	'.LineCB($admin[237],  'drtusersv6', @$drtusersv[6]).'    
    </table>
    </div>
  </fieldset>';
    echo '
  <div class="row quest p-0 mt-2 w-100 m-auto">
    <div class="col-md-4">
      '.SubmitButton($admin[820], 'self.location.href=\'admin.php?lng='.$lng.'&pg=attribdroits&etape=1\'', $imggauche, '', 'button').'
    </div>
    <div class="col-md-4">
      '.SubmitButton($admin[821], 'self.location.href=\'admin.php?lng='.$lng.'&pg=gestredac\'', $imggauche, '', 'button').'
    </div>
    <div class="col-md-4"><div class="float-right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div></div>
  </div>
</form>
<br />';
}
################################################################################
  
btable();
include CHEMIN.'admin/bpage.inc';
?>
