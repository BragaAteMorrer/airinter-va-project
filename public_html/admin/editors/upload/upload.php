<?php
/*******************************************************************************
 *   Upload réalisé par Djchouix - Licence CeCILL
 *******************************************************************************
 *   GuppY PHP Script - version 6
 *   CeCILL Copyright (C) 2004-2022 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.23 (July 7, 2026) : Vulnerability correct
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '../../../');
include CHEMIN."admin/includes.inc";
if(!file_exists(CONFIG))
{
	die("!!! Erreur - Veuillez contacter le webmaster du site. !!! Error - Please contact the site's webmaster");
}
//PROTECTION CONTRE LES PETITS CURIEUX
include CHEMIN.'admin/editors/action.php';
if ($wri == "admin") {
    $mdp    = $userprefs[1] == '' ? md5('pass') : $userprefs[7];
    $fctwri = 'admin';
} else {
    if (FileDBExist(CHEMIN.'admin/'.REDACREP.$wri.INCEXT)) {
        include(CHEMIN.'admin/'.REDACREP.$wri.INCEXT);
        $mdp    = $drtuser[38];
        $fctwri = $drtuser[42];
    } else {
        $mdp = "bad";
    }
}
//CREATION DE L'INDEX DES FICHIERS

function ExtClass($extn){
    switch(strtolower($extn)) {
    case 'bmp'  :
    case 'gif'  : return 'fas fa-image text-secondary';
    case 'png'  : return 'far fa-image text-success';
	case 'webp' : 
	case 'avif' :
    case 'jpeg' :
    case 'jpg'  : return 'far fa-file-image text-primary';
    case 'bat'  :
    case 'com'  :
    case 'exe'  : return 'fas fa-code text-muted';
    case 'inc'  :
    case 'php'  :
    case 'js'   : return 'far fa-file-code text-secondary';
    case 'css'  : return 'fab fa-css3 text-success';
    case 'htm'  :
    case 'html' : return 'fas fa-file-code text-muted';
    case 'pdf'  : return 'far fa-file-pdf text-muted';
    case 'ods'  :
    case 'xls'  : return 'far fa-file-excel text-muted';
    case 'csv'  : return 'fas fa-file-csv text-muted';
    case 'doc'  : 
    case 'docx' : 
    case 'odf'  : return 'far fa-file-word text-info';
    case 'ppt'  : return 'far fa-file-powerpoint text-info';
    case 'dtb'  :
    case 'ini'  :
    case 'txt'  : return 'far fa-file-alt text-dark';
    case 'avi'  :
    case 'mpeg' :
    case 'mpg'  :
    case 'mov'  : return 'far fa-file-video text-warning';
    case 'mp3'  : 
    case 'mp4'  : 
    case 'wav'  : return 'far fa-file-audio text-info';
    case 'ace'  :
    case 'cab'  :
    case 'gz'   :
    case 'rar'  :
    case 'tar'  :
    case 'tgz'  :
    case 'zip'  : return 'far fa-file-archive text-success';
    default     : return 'fas fa-question text-danger';
    }
}
// Liste des répertoires où l'upload n'est pas autorisé
$list_baserepbad = array('admin', 'data', 'documentation', 'inc', 'install', 'mobile');
// Fonctions d'exploration récursive des répertoires
function lister_sousrepertoires($dir){
    $r = $dir.' ';
    $h = opendir(CHEMIN.$dir);
    while ($f = readdir($h)) {
        if (is_dir(CHEMIN.$dir.'/'.$f) && ($f[0] != '.'))
            $r .= lister_sousrepertoires($dir.'/'.$f).' ';
    }
    closedir($h);
    return trim($r);
}

function lister_repertoires($list){
    $r = '';
    for($i=0; $i<count($list); $i++){
        if (is_dir(CHEMIN.$list[$i])) {
			$r .= ' '.lister_sousrepertoires($list[$i]);
        }
    }
    return explode(' ', trim($r));
}
$ctrlmdp = abs(crc32($mdp));
$okctrlmdp = false;
if (isset($_COOKIE[ADMP_COOKIE]))
{
	$admcookie = $_COOKIE[ADMP_COOKIE];
	$strlen    = substr($admcookie, 0, 2);
	$okctrlmdp = substr($admcookie, 2, intval($strlen)) == $ctrlmdp ? true : false;
}
if (!$okctrlmdp ) {
    die($admin[2028]);
} else {
//RECUPERATION ET CONTROL DES VARIABLES PASSEES EN PARAMETRE
$pathRepUploadConfig = import('pathconfig','',false,'');
if(!preg_match("!^[a-z]{2,3}$!i",$lng) || ($pathRepUploadConfig != '' && !preg_match("!^[-a-z0-9_]{1}[-a-z0-9_\/]*\/$!i",$pathRepUploadConfig))) die($admin[2029]);
// $uptype = isset($_POST['uptype']) ? $_POST['uptype'] : (isset( ['uptype']) ? $_GET['uptype'] : 'Image');
$uptype = import('uptype','',false,'Image');
if ($uptype != 'Link' && $uptype != 'Image' && $uptype != 'Flash' && $uptype != 'Media') die($admin[2030]);
$nameRepUploadConfig = import('namerepconfig','',false,'');
if (!preg_match('!^[-a-z0-9_\/]+$!i',$nameRepUploadConfig)) die($admin[2031]);
$upvalid = isset($_POST['upvalid']) ? $_POST['upvalid'] : NULL;
if ($upvalid != NULL && $upvalid != 'ok' ) die($admin[2032]);
$creatrep = import('creatrep','',false,'');		     
$fnewname = import('fnewname','',false,'');
$foldname = import('foldname','',false,'');
$del = import('del','',false,'');
//INITIALISATION DES VARIABLES DE CONFIGURATION POUR UPLOAD (A NE SURTOUT PAS MODIFIER !!)
$pathRepUpload = 'admin/editors/';
$allowedUpload = false;
$allowedCreateRep = false;
$allowedRenameRepFile = false;
$allowedDeleteRepFile = false;
$allowedExtFileUpload = array();
$accessRepUpload = array('img','photo','file','pages');
$accessRepUploadImage = array('img','photo');
$accessRepUploadLink = array('file','img','photo','pages');
if (is_dir(CHEMIN.'redacgroupdocs'))
{
	$accessRepUploadLink[] = 'redacgroupdocs';											
	$accessRepUpload[] = 'redacgroupdocs';										
	$accessRepUploadImage[] = 'redacgroupdocs';
}
$accessRepUploadMedia = array('img','photo');
if (version_compare(PHP_VERSION, '8.1.0') >= 0 && version_compare(PHP_VERSION, '8.5.0') < 0) {
	$allowedExtImage = array('.jpg','.webp','.avif','.gif','.png','.bmp','.jpeg','.JPG','.GIF','.PNG','.BMP','.JPEG');
	$imgTypes = array('bmp', 'gif', 'png', 'jpeg', 'jpg', 'webp', 'avif');
}
else
{
	$allowedExtImage = array('.jpg','.webp','.gif','.png','.bmp','.jpeg','.JPG','.GIF','.PNG','.BMP','.JPEG');
	$imgTypes = array('bmp', 'gif', 'png', 'jpeg', 'jpg', 'webp');
}

$ListdeniedExtFileUpload = array('.php','.inc','.cmd','.sh','.ini','.dat','.bat','.txt','.css','.js', '.html', '.htm', '.xml','.phtml');
$deniedExtLink = array();
$allowedExtMedia = array('.avi','.mp3','.mp4','.AVI','.MP3','.MP4');
$colorTextTitre = '#FFFFFF';  //couleur du titre
$colorFondTitre = '#7F7F7F';  //couleur de fond du titre
$styleBordureTitre = '1px solid #7F7F7F';  //bordure du titre
$colorTextCorp = '#000000';   //couleur du texte
$colorFondCorp = '#EFEFEF';   //couleur du fond de page
$styleBordureCorp = '1px solid #7F7F7F';  //bordure du corps
$colorBodyUpload = '#EFEFEF';  //couleur de fond de page (popup upload)
$colorFondFileUpload = '#EFEFEF'; //couleur du fond du tableau (popup upload)
$colorFondFileUploadOver = '#E7E1EF';  //couleur de fond des fichiers lors du survol de la souris (popup upload)
$colorFileUploadOff = '#000000';  //couleur des liens off (popup upload)
$colorFileUploadOn = '#FF0000';  //couleur des liens on (popup upload)
$pagerror = 0;
$image_size = array(0, 0);

//RECUPERATION DE LA CONFIGURATION DES VARIABLES DE L'UPLOAD SI ELLE EXISTE
if($pathRepUploadConfig !='' && file_exists(CHEMIN.$pathRepUploadConfig.$nameRepUploadConfig.'/config_upload.inc')) {  
	include(CHEMIN.$pathRepUploadConfig.$nameRepUploadConfig.'/config_upload.inc');  //CONFIGURATION UPLOAD PLUGIN
}
if (!isset($drtuser[42])) $drtuser[42] = 'admin';
$droit42wri = $drtuser[42];
if ($droitaccegroupredac == 'on')
{
	if ($drtuser[42] == "redac1" || $drtuser[42] == "redac2")
		{
		   if (FileDBExist(CHEMIN.'admin/'.REDACREP.'redacaccessdossier/accessdossier'.$wri.INCEXT)) 
		   {
				include(CHEMIN.'admin/'.REDACREP.'redacaccessdossier/accessdossier'.$wri.INCEXT);
				$accessRepUpload = explode(',',$drtaccessRepUpload);       //Nom des répertoires à la racine de Guppy accessibles pour le fichier Upload
				$accessRepUploadImage = explode(',',$drtaccessRepUploadImage);  //Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion d'Images.(accès avec variable uptype='Image')
				$accessRepUploadLink = explode(',',$drtaccessRepUploadLink);  //Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion de Liens (accès avec variable uptype='Link')
				$accessRepUploadMedia = explode(',',$drtaccessRepUploadMedia);					
		   }
		}
}
// RECUPERATION DES FICHIER DE LANGUE
if(file_exists(CHEMIN.$pathRepUpload.'upload/lang/'.$lng.'_upload.inc')) {
	include(CHEMIN.$pathRepUpload.'upload/lang/'.$lng.'_upload.inc');
} else {
	include(CHEMIN.$pathRepUpload.'upload/lang/en_upload.inc'); // FICHIER PAR DEFAUT
}
//FONCTIONS UTILISEES DANS LE SCRIPT
include(CHEMIN.$pathRepUpload.'upload/functions_upload.inc');

//CONTROLE DES REPERTOIRES ECRITS DANS LES VARIABLES $accessRepUpload $accessRepUploadImage $accessRepUploadLink et $accessRepUploadFlash
$accessRepUpload = controlNameRepUpload($accessRepUpload);
$accessRepUploadImage = controlAccessRepUpload($accessRepUploadImage,$accessRepUpload,'Image');
$accessRepUploadLink = controlAccessRepUpload($accessRepUploadLink,$accessRepUpload,'Link');
$accessRepUploadMedia = controlAccessRepUpload($accessRepUploadMedia,$accessRepUpload,'Media');

//RECUPERATION ET AFFICHAGE DU REPERTOIRE PAR DEFAUT
$admcookie = isset($_COOKIE[ADMP_COOKIE]) ? $_COOKIE[ADMP_COOKIE] : '00';
$positem   = 0;
for ($i = 0; $i < 9; $i++) {
    $posdata        = $positem + 2;
    $lendata        = intval(substr($admcookie, $positem, 2));
    $adminprefs[$i] = substr($admcookie, $posdata, $lendata);
    $positem        = $posdata + $lendata;
}
	
switch($uptype) {
	case 'Image':
		if ($wri != 'admin')
		{
			if ($droit42wri == 'redac1' || $droit42wri == 'redac2')
			{
			  if (!empty($accessRepUploadImage[0])) 
				$repdefault = $accessRepUploadImage[0];
			}
			else $repdefault = $adminprefs[1];
		}
		else		
			$repdefault = $adminprefs[1];
		break;
	case 'Link':
		if ($wri != 'admin')
		{
			if ($dtruser[42] == 'redac1' || $dtruser[42] == 'redac2')
			{
			  if (!empty($accessRepUploadLink[0])) 
				$repdefault = $accessRepUploadLink[0];
			}
				else $repdefault = $adminprefs[2];
		}
		else $repdefault = $adminprefs[2];
		break;
	case 'Media':
		if ($wri != 'admin')
		{
			if ($dtruser[42] == 'redac1' || $dtruser[42] == 'redac2')
			{
			  if (!empty($accessRepUploadMedia[0])) 
				$repdefault = $accessRepUploadMedia[0];
			}
			else $repdefault = $adminprefs[3];
		}
		else $repdefault = $adminprefs[3];
		break;
	default :
		if ($dtruser[42] != 'redac1' && $dtruser[42] != 'redac2' )
		{
			$repdefault = 'img';

		}
}
$rep = import('rep','',true,$repdefault);
for ($i=0; $i < count($list_baserepbad);$i++)
{
	$pos = strpos($rep, $list_baserepbad[$i]);
	if ($pos !== false)
         die($admin[2028]);
}
// Liste des répertoires où l'upload est autorisé
$list_rep = lister_repertoires($accessRepUploadImage);

$dbfiles = array();
$i = 0;
if (!file_exists(CHEMIN.$rep))
	  die($admin[2028]);
$dossier = opendir(CHEMIN.$rep);
while ($fichier = readdir($dossier)) {
		if (is_file(CHEMIN.$rep.'/'.$fichier) && $fichier != 'index.php' && $fichier != '.htaccess') {
        $dbfiles[$i][0] = $fichier;
        $path_parts = basename($fichier);
        $path_parts = strtolower(substr($path_parts, strrpos($path_parts, '.') + 1));
        $dbfiles[$i][1] = ExtClass($path_parts);
        $dbfiles[$i][2] = FileSizeInKb(CHEMIN.$rep.'/'.$fichier);
        $dbfiles[$i][3] = in_array($path_parts, $imgTypes);
        $i++;
    }
}
closedir($dossier);
sort($dbfiles);
}
switch($uptype) {
	case 'Image':
		$adminprefs[1] = $rep;
	break;
	case 'Link':
		$adminprefs[2] = $rep;
	break;
	case 'Media':
		$adminprefs[3] = $rep;
	break;
}
//CREATION D'UN SOUS-REPERTOIRE
$allowedCreateRep = $fctwri != 'admin' ? false : $allowedCreateRep;
if ($drtuser[42] == 'redac1' || $drtuser[42] == 'redac2' )
		$allowedUpload = $allowedCreateRep = true;
if($allowedCreateRep == true) {  //AUTORISATION
	if (isset($creatrep) && trim($creatrep) != "") {
		$creatrep = cleanName($creatrep);
    	if ($creatrep == "0" ) {$creatrep = "0_";}
	
        if (stristr($creatrep, 'script') !== false) {
            echo '
<script type="text/javascript">
    alert('.$admin[2033].');
    history.back();
</script>';
        } else {
    		if(is_dir(CHEMIN.$rep.'/'.$creatrep)){
    	    	$pagerror = 1;
    			$erreur = $lang_upload[1];
        	} else {
    			createNewRep($rep.'/'.$creatrep);
    	  	}
	  	}
	}
}
//RENOMMER UN FICHIER OU UN REPERTOIRE			
$allowedRenameRepFile = $fctwri != 'admin' ? false : $allowedRenameRepFile;
if($allowedRenameRepFile == true) { //AUTORISATION
	if (isset($fnewname) && trim($fnewname) != "") {
		$fnewname = cleanName($fnewname);
	
		$fext = strrchr($foldname,'.');
		if(is_file(CHEMIN.$rep.'/'.$foldname)) {	    
	    	if (file_exists(CHEMIN.$rep.'/'.$fnewname.$fext)){
	  	   		$pagerror = 1; 
		   		$erreur = $lang_upload[2];
	    	} else {	      
              	@rename(CHEMIN.$rep.'/'.$foldname , CHEMIN.$rep.'/'.$fnewname.$fext);
			  	SetChmod(CHEMIN.$rep.'/'.$fnewname.$fext);
	      	}
		} else if(is_dir(CHEMIN.$rep.'/'.$foldname)) {
	    	if ($fnewname == "0" ){$fnewname = "0_";}	    
	    	if (is_dir(CHEMIN.$rep.'/'.$fnewname)){
	  	   		$pagerror = 1; 
		   		$erreur = $lang_upload[3];
	    	} else {	                
	          	@rename(CHEMIN.$rep.'/'.$foldname , CHEMIN.$rep.'/'.$fnewname);
			  	SetChmod(CHEMIN.$rep.'/'.$fnewname);
	       	}	
      	}
	}
}
//SUPPRIMER UN FICHIER OU UN REPERTOIRE
$allowedDeleteRepFile = $fctwri != 'admin' ? false : $allowedDeleteRepFile;
if($allowedDeleteRepFile == true) { //AUTORISATION
	if (isset($del) && trim($del) != "") {
  		if(is_file(CHEMIN.$rep."/".$del)) {
      		@unlink(CHEMIN.$rep."/".$del);
  		}
  		if(is_dir(CHEMIN.$rep."/".$del)) {
      		DelSubRep(CHEMIN.$rep."/".$del."/");
	  		if(rmdir(CHEMIN.$rep."/".$del) === false) {
	  	 		$pagerror = 1;
		 		$erreur = $lang_upload[4]; 
	  		}
  		}      	  
	}
}
//UPLOAD DES FICHIERS
$allowedUpload = $fctwri != 'admin' ? false : $allowedUpload;
if ($drtuser[42] == 'redac1' || $drtuser[42] == 'redac2' && $droitaccegroupredac != 'on')
		$allowedUpload = $allowedCreateRep = true;	
if ($drtuser[42] == 'redac1' || $drtuser[42] == 'redac2' && $droitaccegroupredac == 'on')
{
	$allowedUpload = true;
	$allowedCreateRep = false;
}
if($allowedUpload == true) { //AUTORISATION
	if (isset($_FILES['ficup']['name']) && trim($_FILES['ficup']['name']) != "" && $upvalid == "ok") {
    	$pagerror = 0;
		$_FILES['ficup']['name'] = strip_tags($_FILES['ficup']['name']);
		$_FILES['ficup']['name'] = cleanName($_FILES['ficup']['name'],true);

		if (!preg_match('!^([-a-zA-Z0-9_]+)(\.)([a-zA-Z0-9]{2,4})$!i',$_FILES['ficup']['name'])){ //CONTROL SI NOM FICHIER VALIDE
	     	$pagerror = 1;
		 	$erreur = $lang_upload[5]; 
		}
		if (file_exists(CHEMIN.$rep.'/'.$_FILES['ficup']['name'])){ //CONTROL SI NOM DU FICHIER DEJA PRESENT
	     	$pagerror = 1; 
		 	$erreur = $lang_upload[6];
		}
		if((count($allowedExtFileUpload) > 0 && !in_array(strrchr($_FILES['ficup']['name'],'.'),$allowedExtFileUpload)) || (count($ListdeniedExtFileUpload) > 0 && in_array(strrchr($_FILES['ficup']['name'],'.'),$ListdeniedExtFileUpload))) { //AUTORISATION EXTENSION DU FICHIER
	     	$pagerror = 1; 
		 	$erreur = $admin[1104];	
		}
		if($pagerror != 1) {	//UPLOAD	
    		if (move_uploaded_file($_FILES['ficup']['tmp_name'], CHEMIN.$rep.'/'.$_FILES['ficup']['name'])) {
	   			SetChmod(CHEMIN.$rep.'/'.$_FILES['ficup']['name']);
    		}
		}
	}
}

$dbdir    = array();
$dbfiles  = array();
$i        = 0;
$dossier = opendir(CHEMIN.$rep);
while ($fichier = readdir($dossier)) {
    if($fichier != '.' && $fichier != '..' && is_dir(CHEMIN.$rep.'/'.$fichier)) {
    	$dbdir[$i][0] = $fichier;
      	$dbdir[$i][1] = 'fas fa-folder-open text-warning';
      	$dbdir[$i][2] = 'dir';
      	$dbdir[$i][3] = 'dir';
	} else if ((is_file(CHEMIN.$rep.'/'.$fichier) && $fichier != 'index.php')) {
      	$dbfiles[$i][0] = $fichier;
		$path_parts = strtolower(substr(strrchr($fichier,'.'), 1));
        $dbfiles[$i][1] = ExtClass($path_parts);
	  	$filesize = filesize(CHEMIN.$rep.'/'.$fichier);
	  	$dbfiles[$i][2] = number_format($filesize,0,',',' ');
        $dbfiles[$i][3] = in_array($path_parts, $imgTypes);
  	}
	$i++;
}
closedir($dossier);
if(count($dbdir) > 0) sort($dbdir);
if(count($dbfiles) > 0) sort($dbfiles);
$dbfiles = array_merge($dbdir, $dbfiles);

//PAGE HTML
echo '
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>'.$lang_upload[13].'</title>
<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'" />
<meta http-equiv="Content-Style-Type" content="text/css" />
';
//INSERTION STYLE CSS
if (file_exists(CHEMIN.$pathRepUploadConfig.$nameRepUploadConfig.'/style_upload.css')) {
    echo '
<link type="text/css" rel="stylesheet" href="'.CHEMIN.$pathRepUploadConfig.$nameRepUploadConfig.'/style_upload.css" />';
}
elseif (file_exists(CHEMIN.$pathRepUploadConfig.$nameRepUploadConfig.'/style_upload.inc')) {
    echo '
<style type="text/css">';
	include(CHEMIN.$pathRepUploadConfig.$nameRepUploadConfig.'/style_upload.inc');
	echo '
</style>';
} 
else {
    echo '
<link type="text/css" rel="stylesheet" href="'.CHEMIN.$pathRepUpload.'upload/style_upload.css" />';  //STYLE PAR DEFAUT
}
echo '
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" 
   />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css" 
  integrity="sha512-rt/SrQ4UNIaGfDyEXZtNcyWvQeOq0QLygHluFQcSjaGB04IxWhal71tKuzP6K8eYXYB6vJV4pHkXcmFGGQ1/0w==" crossorigin="anonymous" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" 
  integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.bundle.min.js" 
  integrity="sha512-sha512-KgffulL3mxrOsDicgQWA11O6q6oKeWcV00VxgfJw4TcM8XRQT8Df9EsrYxDf7tpVpfl3qcYD96BpyPvA4d1FDQ==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-migrate/3.5.2/jquery-migrate.min.js" 
  integrity="sha512-BzvgYEoHXuphX+g7B/laemJGYFdrq4fTKEo+B3PurSxstMZtwu28FHkPKXu6dSBCzbUWqz/rMv755nUwhjQypw==" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js" 
  integrity="sha512-0QbL0ph8Tc8g5bLhfVzSqxe9GERORsKhIn1IrpxDAgUsbBGz/V7iSav2zzW325XGd1OMLdL4UiqRJj702IeqnQ==" crossorigin="anonymous">
</script>
';
//INSERTION FONCTIONS JAVASCRIPT
?>
<style>
.hover_img a { position:relative; }
.hover_img a span { background: #FFF; position:absolute; left:80px; display:none; z-index:99; border: 1px solid #000; padding: 10px 0px 10px 10px; }
.hover_img a:hover span { display:block; }
.hover_img img { max-width: 468px; height: auto; color:#000}/*si pas d'image (noscreen) le texte du tag alt sera en noir*/
.hover_img img::after  { content: url(inc/img/files/noscreen.jpg); } /*affichage de cette image si le lien n'est pas une image*/
</style>

<script type="text/javascript">
//REDIMENSIONNEMENT POPUP UPLOAD
function popupUploadSize() {
    var wscr = 700;
<?php
	if(!$allowedUpload && !$allowedCreateRep) {
		echo '
    var hscr = 460;';
	} elseif(!$allowedUpload || !$allowedCreateRep){
		echo '
    var hscr = 490;';
	} else {
		echo '
    var hscr = 680;';
	}
	echo '
	window.resizeTo(wscr, hscr);
	window.moveTo((window.screen.width-wscr)/2, (window.screen.height-hscr)/2);
	';
?>
}
//AFFICHAGE FICHIER IMAGE DANS POPUP
function popupImgUp(chemin) {
    var hauteurScreen= window.screen.height;
    var largeurScreen= window.screen.width;		
	
    FenImg = window.open("","","directories=0, menubar=0, status=0, resizable=1, scrollbars=0,location=0");
	FenImg.document.open();	
	
	var html = '<html><head><title><?php echo $lang_upload[22]; ?><\/title>'
		html = html+'<script type="text\/javascript">';
		html = html+'function ajustsize()  {';
		html = html+'var hauteurImg = document.images[0].height; var largeurImg = document.images[0].width; var top = ('+hauteurScreen+'- hauteurImg)/2; var left = ('+largeurScreen+'- largeurImg)/2; ';
		html = html+' if (document.images[0].complete) {  window.resizeTo(largeurImg+40,hauteurImg+70); window.moveTo(left,top); window.focus();} else { setTimeout("ajustsize();",500) } }<\/script>';
        html = html+'<\/head><body onload="ajustsize();"><div style="text-align:center; vertical-align:middle;"><img src="'+chemin+'" alt="" onclick="window.close();" /><\/div><\/body><\/html>';

	FenImg.document.write(html);
	FenImg.document.close();
	}	
//AFFICHAGE FICHIER AUTRE QUE IMAGE DANS POPUP
function popupFileUp(chemin) {
    var hauteur = 500;
    var largeur = 700;
    var top=(screen.height-hauteur)/2;
    var left=(screen.width-largeur)/2;
    window.open(chemin, '', 'top='+top+', left='+left+', width='+largeur+', height='+hauteur+', resizable=yes,menubar=no,location=no,directories=no,status=no,copyhistory=no,toolbar=no,scrollbars=yes');
}
<?php
//RENOMMER UN FICHIER OU UN REPERTOIRE
if($allowedRenameRepFile == true) { //AUTORISATION
?>
function renameFile(rep,file,type) {
    if (type == 'file') {
	    message = '<?php echo $lang_upload[27]; ?> "'+file+'".\n';
		message2 = "<?php echo $lang_upload[28]; ?>";
	}
    if (type == 'dir') {
	    message = '<?php echo $lang_upload[29]; ?> "'+file+'".\n';
		message2 = '<?php echo $lang_upload[30]; ?>';
	}
	message = message + '<?php echo $lang_upload[31]; ?>';
	x = null;
    x = window.prompt(message,message2);
	//control de la variable
	erreur = false;
	if(x.search(/^[-a-zA-Z0-9_]+$/gi) == -1) erreur = true;
	if (x != null && erreur == false) {
	   window.location = "upload.php?lng=<?php echo $lng; ?>&uptype=<?php echo $uptype; ?>&namerepconfig=<?php echo $nameRepUploadConfig; ?>&pathconfig=<?php echo $pathRepUploadConfig; ?>&rep="+rep+"&foldname="+file+"&fnewname="+x;
	} else {
		  alert('<?php echo $lang_upload[31]; ?>');
	      return false;
	 }
}
<?php
}
//SUPPRIMER UN FICHIER OU UN REPERTOIRE	
if($allowedDeleteRepFile == true) { //AUTORISATION
?>
function confirmDelFile(rep,file,type) {
    if (type == 'file') message = '<?php echo $lang_upload[23]; ?> " '+file+' ".\n';
    if (type == 'dir') message = '<?php echo $lang_upload[24]; ?> " '+file+' " <?php echo $lang_upload[25]; ?>\n';
	message = message + " <?php echo $lang_upload[26]; ?>";
	x = false;
    x = window.confirm(message);
	if (x == true) {
	   window.location = "upload.php?lng=<?php echo $lng; ?>&uptype=<?php echo $uptype; ?>&namerepconfig=<?php echo $nameRepUploadConfig; ?>&pathconfig=<?php echo $pathRepUploadConfig; ?>&rep="+rep+"&del="+file;
	} else {
	      return false;
	}
}
<?php
}
?>
//RETOUR AU REPERTOIRE PARENT
function RepParent(directory) {
	  dir = directory.split("/");
	  dir.length--;
      directory = dir.join("/");
	  x = document.forms["changedir"].elements["rep"].selectedIndex;
	  document.forms["changedir"].elements["rep"].options[x].value = directory;
	  document.forms["changedir"].submit();
}
//RECUPERATION DE L'URL
<?php
if(file_exists(CHEMIN.$pathRepUploadConfig.$nameRepUploadConfig.'/jscript_upload.inc')) {
	include(CHEMIN.$pathRepUploadConfig.$nameRepUploadConfig.'/jscript_upload.inc');
} else {
	echo 'function openURL(url,type,alt,width,height) {window.close();}'."\n";
}
?>
//AFFICHER/MASQUER AIDE
function OpenCloseHelp() {
   if (document.getElementById('helpClose') == null) {
    document.getElementById('help').style.display = 'block';
	document.getElementById('helpOpen').value = "<?php echo $lang_upload[12]; ?>";	
	document.getElementById('helpOpen').id = 'helpClose';	
	} else {
    document.getElementById('help').style.display = 'none';
	document.getElementById('helpClose').value = "<?php echo $lang_upload[11]; ?>";	
	document.getElementById('helpClose').id = 'helpOpen';	
	}
}
//AFFICHER ELEMENT SURVOLE
function MouseOverFile(y,x,z) {
    if (y == 'yes') {
        document.getElementById(x).id = z;
	} else {
        document.getElementById(z).id = x;
	}
}
//VERIFICATION AVANT SOUMISSION POUR UPLOAD
function UploadValid(h,champ) {
	erreur = false;
	msg_erreur = '';
	if(h.elements[champ].value == "") {
	 	erreur = true;
	 	msg_erreur += "<?php echo $lang_upload[20]; ?>\n";
	}
	if(champ == 'ficup') {
		if(h.elements[champ].value.search(/(\\|\/)?([-a-zA-Z0-9_]+)(\.)([a-zA-Z0-9]{2,4})$/gi) == -1) {
	 		erreur = true;
	 		msg_erreur += "<?php echo $lang_upload[31]; ?>";
		}
	} else {
		if(h.elements[champ].value.search(/^[-a-zA-Z0-9_]+$/gi) == -1) {
	 		erreur = true;
	 		msg_erreur += "<?php echo $lang_upload[31]; ?>";
		}	
	}
    if (erreur == false) {
	   return true;
	} else {
	   alert(msg_erreur);
	   return false;
	}
}
</script>
</head>
<body onload="popupUploadSize();">
<?php
switch ($pagerror) {
    case "1" :
        echo '
<form name="pbupload" action="upload.php?lng='.$lng.'" method="post" style="margin:0px;">
  <input type="hidden" name="pg" value="upload" />
  <input type="hidden" name="rep" value="'.$rep.'" />
  <input type="hidden" name="uptype" value="'.$uptype.'" />
  <input type="hidden" name="pathconfig" value="'.$pathRepUploadConfig.'" />
  <input type="hidden" name="namerepconfig" value="'.$nameRepUploadConfig.'" />
  <div style="text-align:center; font-weight:bold; margin-top:15px;">'.$erreur.'</div>
  <div style="text-align:center; margin-top:15px; margin-bottom:15px;">
    <input type="submit" class="btn- btn-info bouton" id="retour" name="retour" value="'.$lang_upload[33].'" />
  </div>
</form>';
        break;
    default :
if (is_dir(CHEMIN.'redacgroupdocs'))
{
	$accessRepUploadImage[] = 'redacgroupdocs';
}	
        echo '
<div id="help" class="help">'.$lang_upload[7].$lang_upload[19].'</div>
<form name="changedir" action="upload.php?lng='.$lng.'" method="post">
  <input type="hidden" name="uptype" value="'.$uptype.'" />
  <input type="hidden" name="pathconfig" value="'.$pathRepUploadConfig.'" />
  <input type="hidden" name="namerepconfig" value="'.$nameRepUploadConfig.'" />
  <div class="upload">
    <div class="d-inline-block">
      <i class="fas fa-folder text-warning" style="font-size: 1.4rem;" title="'.$lang_upload[14].'"></i>&nbsp;'.$lang_upload[14].'&nbsp;:&nbsp;
    </div>
    <div class="d-inline-block">
      <select class="form-control" name="rep" onchange="document.changedir.submit();">';
		switch($uptype) {
			case 'Image' :
				foreach($accessRepUploadImage as $nameRepUploadImage) {
					$repselected = Selected($rep == $nameRepUploadImage);
					echo '
        <option value="'.$nameRepUploadImage.'"'.$repselected.'>'.$nameRepUploadImage.'</option>';
				}
			break;
			case 'Link' :
				foreach($accessRepUploadLink as $nameRepUploadLink) {
					$repselected = Selected($rep == $nameRepUploadLink);
					echo '
        <option value="'.$nameRepUploadLink.'"'.$repselected.'>'.$nameRepUploadLink.'</option>';
				}
			break;
			case 'Media':
				foreach($accessRepUploadMedia as $nameRepUploadMedia) {
					$repselected = Selected($rep == $nameRepUploadMedia);
					echo '
        <option value="'.$nameRepUploadMedia.'"'.$repselected.'>'.$nameRepUploadMedia.'</option>';
				}
			break;
			default :
				die("Erreur 202");
		}          
		if (!in_array($rep, $accessRepUpload)) echo  '
        <option value="" selected="selected">'.$rep.'</option>';
        echo '
      </select>
    </div>
    <div class="d-inline-block">
      <input id="helpOpen" class="btn btn-info bouton mb-1" style="font-size: 1rem;" type="button" value="'.$lang_upload[11].'" onclick="OpenCloseHelp()" />	    
    </div>
  </div>
  <div style="text-align:center;" style="z-index: 0;">
    <div class="table-responsive-md '.(count($dbfiles) > 8 ? 'corpsFileScroll' : 'corpsFilenoScroll').'"> 
      <table class="table table-borderless" style="z-index: 0;">
        <tr class="forum">
          <td id="forumName" nowrap="nowrap">'.$lang_upload[15].'</td>
          <td id="forumSize" nowrap="nowrap" width="15%">'.$lang_upload[16].'</td>
          <td id="forumAction" nowrap="nowrap" width="15%">'.$lang_upload[17].'</td>
        </tr>';
        for ($i = 0; $i < count($dbfiles); $i++) {
			$n = ($i/2);
		    if (is_integer($n)) {
				echo '
        <tr class="rep" id="tr'.$i.'" 
          onmouseover="MouseOverFile(\'yes\',\'tr'.$i.'\',\'trsurvol\');" 
          onmouseout="MouseOverFile(\'no\',\'tr'.$i.'\',\'trsurvol\');">';
			} 
            else {
			    echo '
        <tr class="quest" id="tr'.$i.'" 
          onmouseover="MouseOverFile(\'yes\',\'tr'.$i.'\',\'trsurvol\');" 
          onmouseout="MouseOverFile(\'no\',\'tr'.$i.'\',\'trsurvol\');">';
			}
            if ($dbfiles[$i][2] == 'dir') {
				echo '
          <td class="fileName">
            <a href="upload.php?lng='.$lng.'&amp;rep='.$rep.'/'.$dbfiles[$i][0].'&amp;uptype='.$uptype.'&amp;pathconfig='.$pathRepUploadConfig.
                '&amp;namerepconfig='.$nameRepUploadConfig.'" class="imgName">
              <i class="'.$dbfiles[$i][1].' mr-2" style="cursor: pointer; font-size: 1.1rem;"></i>
            </a>
            <a href="upload.php?lng='.$lng.'&amp;rep='.$rep.'/'.$dbfiles[$i][0].'&amp;uptype='.$uptype.'&amp;pathconfig='.$pathRepUploadConfig.
                '&amp;namerepconfig='.$nameRepUploadConfig.'" title="'.$lang_upload[32].'">
              <span style="font-size: 1rem;">'.$dbfiles[$i][0].'</span>
            </a>
          </td>
          <td class="fileSize"><i class="far fa-folder" style="font-size: 1.1rem;"></i></td>
          <td class="fileAction">
            <a href="upload.php?lng='.$lng.'&amp;rep='.$rep.'/'.$dbfiles[$i][0].'&amp;uptype='.$uptype.
                '&amp;pathconfig='.$pathRepUploadConfig.'&amp;namerepconfig='.$nameRepUploadConfig.'">
              <i class="fas fa-search-location align-middle ml-n2" style="cursor: pointer; font-size: 1.1rem;" title="'.$lang_upload[32].'" 
                onmouseover="MouseOverFile(\'yes\',\'on'.$i.'\',\'imgsurvol\');" 
                onmouseout="MouseOverFile(\'no\',\'on'.$i.'\',\'imgsurvol\');"></i>
            </a>';
				if($allowedRenameRepFile == true) { //AUTORISATION RENOMMER
					echo '
            <i class="fas fa-edit align-middle ml-2" style="cursor: pointer; font-size: 1rem;" title="'.$lang_upload[36].'" 
              onclick=\'renameFile("'.$rep.'","'.$dbfiles[$i][0].'","dir");\' 
              onmouseover="MouseOverFile(\'yes\',\'nom'.$i.'\',\'imgsurvol\');" 
              onmouseout="MouseOverFile(\'no\',\'nom'.$i.'\',\'imgsurvol\');"</i>';
				} //FIN AUTORISATION
				if($allowedDeleteRepFile == true) { //AUTORISATION SUPPRESSION
					echo '
            <i class="fas fa-times text-danger align-middle ml-2" style="cursor: pointer; font-size: 1rem;" title="'.$lang_upload[37].'" 
              onclick=\'confirmDelFile("'.$rep.'","'.$dbfiles[$i][0].'","dir");\' 
              onmouseover="MouseOverFile(\'yes\',\'sup'.$i.'\',\'imgsurvol\');" 
              onmouseout="MouseOverFile(\'no\',\'sup'.$i.'\',\'imgsurvol\');"></i>';
				} //FIN AUTORISATION
				echo '
          </td>';
			} else {
				$ext = strrchr($dbfiles[$i][0], '.');
				echo '
          <td class="fileName">';
 				switch ($uptype) {
					case 'Image' :
					    if (count($allowedExtImage) > 0 && in_array($ext, $allowedExtImage)) {
						    $image_size = getimagesize(CHEMIN.$rep.'/'.$dbfiles[$i][0]);
                            echo '
            <div class="hover_img">
              <a name="file'.$i.'" title="'.$lang_upload[38].'" href="#file'.$i.'" style="font-size: 1rem;"
                onclick=\'openURL("'.$site[3].$rep.'/'.$dbfiles[$i][0].'","img","'.$dbfiles[$i][0].'","'.$image_size[0].'","'.$image_size[1].'");\' >
                <i class="'.$dbfiles[$i][1].' align-middle mr-2" style="font-size: 1.2rem;" title="'.$rep.'/'.$dbfiles[$i][0].'"
                  onclick=\'openURL("'.$site[3].$rep.'/'.$dbfiles[$i][0].'","img","'.$dbfiles[$i][0].'","'.$image_size[0].'","'.$image_size[1].'");\'></i>
                '.$dbfiles[$i][0].'
                <span'.($dbfiles[$i][3] ? '' : ' class="d-none"').' style="z-index: 9999;">
                  <img src="'.$site[3].$rep.'/'.$dbfiles[$i][0].'">
                </span>
              </a>
            </div>';						
				 		} else {
							echo '
            <i class="'.$dbfiles[$i][1].' align-middle mr-2" style="font-size: 1.1rem;" title="'.$rep.'/'.$dbfiles[$i][0].'"></i>
            <span style="font-size: 1rem;">'.$dbfiles[$i][0].'</span>';
						}
					break;
					case 'Link' :
						if(count($deniedExtLink) > 0 && in_array($ext, $deniedExtLink)) {
							echo '
            <i class="fas fa-unlink text-danger align-middle mr-2" title="'.$rep.'/'.$dbfiles[$i][0].'"></i>
            <span style="font-size: 1rem;">'.$dbfiles[$i][0].'</span>';
						} else {
							echo '
            <i class="fas fa-link text-info align-middle mr-2" title="'.$rep.'/'.$dbfiles[$i][0].'" 
               onclick=\'openURL("'.$site[3].$rep.'/'.$dbfiles[$i][0].'","file","'.$dbfiles[$i][0].'","","");\'></i>
            <a style="font-size: 1rem;" name="file'.$i.'" title="'.$lang_upload[38].'" href="#file'.$i.'" 
                onclick=\'openURL("'.$site[3].$rep.'/'.$dbfiles[$i][0].'","file","'.$dbfiles[$i][0].'","","");\' >
              '.$dbfiles[$i][0].'
            </a>';
						}
					break;
					case 'Media' :
					    if (count($allowedExtMedia) > 0 && in_array($ext, $allowedExtMedia)) {
							echo '
            <i class="far fa-file-audio text-info align-middle mr-2" title="'.$rep.'/'.$dbfiles[$i][0].'"
              onclick=\'openURL("'.$site[3].$rep.'/'.$dbfiles[$i][0].'","media","'.$dbfiles[$i][0].'","","");\'></i>
            <a style="font-size: 1rem;" name="file'.$i.'" title="'.$lang_upload[38].'" href="#file'.$i.'" 
                onclick=\'openURL("'.$site[3].$rep.'/'.$dbfiles[$i][0].'","media","'.$dbfiles[$i][0].'","","");\' >
              '.$dbfiles[$i][0].'
            </a>';
						} else {
							echo '
            <i class="'.$dbfiles[$i][1].' align-middle mr-2" title="'.$rep.'/'.$dbfiles[$i][0].'"></i>
            <span style="font-size: 1rem;">'.$dbfiles[$i][0].'</span>';
						}
					break;
					default :
						echo '
            <i class="'.$dbfiles[$i][1].' align-middle mr-2" style="font-size: 1.1rem;" title="'.$rep.'/'.$dbfiles[$i][0].'"></i>
            '.$dbfiles[$i][0];						    
				}				
				echo '
          </td>
          <td class="fileSize"><span style="font-size: 1rem;">'.$dbfiles[$i][2].'</span></td>';
				if (in_array($ext, $allowedExtImage)) {
                	echo '
          <td class="fileAction">
            <i class="far fa-eye text-info align-middle ml-n2"  title="'.$lang_upload[35].'" style="cursor: pointer; font-size: 1rem;" 
              onclick=\'popupImgUp("'.CHEMIN.$rep.'/'.$dbfiles[$i][0].'");\' 
              onmouseover="MouseOverFile(\'yes\',\'on'.$i.'\',\'imgsurvol\');" 
              onmouseout="MouseOverFile(\'no\',\'on'.$i.'\',\'imgsurvol\');"></i>';
				} else{
                    echo '
          <td class="fileAction">
            <i class="far fa-eye text-info align-middle ml-n2"  title="'.$lang_upload[35].'" style="cursor: pointer; font-size: 1rem;" 
              onclick=\'popupFileUp("'.CHEMIN.$rep.'/'.$dbfiles[$i][0].'");\' 
              onmouseover="MouseOverFile(\'yes\',\'on'.$i.'\',\'imgsurvol\');" 
              onmouseout="MouseOverFile(\'no\',\'on'.$i.'\',\'imgsurvol\');"></i>';
				}
				if($allowedRenameRepFile == true) { //AUTORISATION RENOMMER
					echo '
            <i class="fas fa-edit align-middle ml-2" title="'.$lang_upload[36].'" style="cursor: pointer; font-size: 1rem;"  
              onclick=\'renameFile("'.$rep.'","'.$dbfiles[$i][0].'","file");\' 
              onmouseover="MouseOverFile(\'yes\',\'nom'.$i.'\',\'imgsurvol\');" 
              onmouseout="MouseOverFile(\'no\',\'nom'.$i.'\',\'imgsurvol\');"></i>';											
				}  //FIN AUTORISATION
				if($allowedDeleteRepFile == true) { //AUTORISATION SUPPRESSION
					echo '
            <i class="fas fa-times text-danger align-middle ml-2" title="'.$lang_upload[37].'" style="cursor: pointer; font-size: 1rem;"  
              onclick=\'confirmDelFile("'.$rep.'","'.$dbfiles[$i][0].'","file");\' 
              onmouseover="MouseOverFile(\'yes\',\'sup'.$i.'\',\'imgsurvol\');" 
              onmouseout="MouseOverFile(\'no\',\'sup'.$i.'\',\'imgsurvol\');"></i>';
				} //FIN AUTORISATION
				echo '
          </td>';
			}			
            echo '
        </tr>';
        }
        echo '
      </table>
    </div>
  </div>
</form>';
		//UPLOAD DES FICHIERS

		if($allowedUpload == true) {  //AUTORISATION		
        	echo '
<form name="uploadit" enctype="multipart/form-data" action="upload.php?lng='.$lng.'" method="post" onsubmit="return UploadValid(this,\'ficup\');" >
  <input type="hidden" name="rep" value="'.$rep.'" />
  <input type="hidden" name="upvalid" value="ok" />
  <input type="hidden" name="uptype" value="'.$uptype.'" />
  <input type="hidden" name="pathconfig" value="'.$pathRepUploadConfig.'" />
  <input type="hidden" name="namerepconfig" value="'.$nameRepUploadConfig.'" />
  <div class="labelupload mt-3">
    <i class="fas fa-file-upload text-info mr-2" style="font-size: 1.2rem;" title="'.$lang_upload[13].'"></i>
    '.$lang_upload[13].'
  </div>
  <div class="upload">
    <div class="d-inline-block"><input class="form-control" type="file" name="ficup" size="27" /></div>
    <div class="d-inline-block"><input class="btn btn-info bouton mb-1" style="font-size: 1rem;" type="submit" value="'.$lang_upload[18].'" /></div>
  </div>
</form>';
		}
		//CREATION D'UN SOUS-REPERTOIRE	
		if($allowedCreateRep == true) {  //AUTORISATION	
			echo '
<form name="createdit"  action="upload.php?lng='.$lng.'" method="post" onsubmit="return UploadValid(this,\'creatrep\');">
  <input type="hidden" name="rep" value="'.$rep.'" />
  <input type="hidden" name="uptype" value="'.$uptype.'" />
  <input type="hidden" name="pathconfig" value="'.$pathRepUploadConfig.'" />
  <input type="hidden" name="namerepconfig" value="'.$nameRepUploadConfig.'" />
  <div class="labelupload">
    <i class="fas fa-folder-open text-warning mr-2" style="font-size: 1.2rem;" title="'.$lang_upload[8].'"></i>'.$lang_upload[8].'
  </div>
  <div class="upload">
    <div class="d-inline-block">'.$lang_upload[9].'&nbsp;:&nbsp;</div>
    <div class="d-inline-block"><input class="form-control" type="text" name="creatrep" size="30" value="" /></div>
    <div class="d-inline-block"><input class="btn btn-info bouton mb-1" style="font-size: 1rem;" type="submit" value="'.$lang_upload[10].'" /></div>
  </div>
</form>';
		}
}		
echo '
</body>
</html>';