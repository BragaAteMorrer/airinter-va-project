<?php
/*******************************************************************************
 *   Upload / Delete files
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2024 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.24 (August 12, 2026) : security correction and message die
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'upload.inc')) {
    header('location:../index.php');
    die();
}
if(!file_exists(CONFIG))
{
	die("!!! Erreur - Veuillez contacter le webmaster du site. !!! Error - Please contact the site's webmaster");
}
// Liste des répertoires où l'upload est autorisé
$list_baserep = array('file', 'img', 'pages', 'photo', 'save', 'skins','redacgroupdocs');
// Liste des répertoires où l'upload n'est pas autorisé
$list_baserepbad = array('admin', 'data', 'documentation', 'inc', 'install', 'mobile');
// Extensions non autorisées
$deniedExtFileUpload = array('.php','.inc','.cmd','.sh','.ini','.dat','.bat','.txt','.css','.js', '.html', '.htm', '.xml', '.phtml');						 
if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[36] == '') {
        $nomzone = $admin[171];
        include 'access.inc';
        exit();
    }
}

$rep    = import('rep');
$newrep = import('newrep');
$del    = import('del');
$actionfichier = import('actionfichier');
for ($i=0; $i < count($list_baserepbad);$i++)
{
	$pos = strpos($rep, $list_baserepbad[$i]);
	if ($pos !== false)
         die($admin[2028]);
}
//Fonction pour contrôler et corriger le nom d'un répertoire ou d'un fichier
function cleanName($name,$up=false) {
	$nameclean = trim($name);
	$nameclean = str_replace(' ','_',$nameclean);
	$nameclean = str_replace('à','a',$nameclean);
	$nameclean = preg_replace('![éèê]!','e',$nameclean);
	$nameclean = preg_replace('![îï]!','i',$nameclean);
	$nameclean =($up)? preg_replace('![^-a-zA-Z0-9_\.]!','',$nameclean) : preg_replace('![^-a-zA-Z0-9_]!','',$nameclean);
	return $nameclean;
}
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

if (empty($rep)) {
    $rep = 'file';
}

/// DELETE FILE
if (!empty($del) && $actionfichier == 2) {
    SetChmod(CHEMIN.$rep.'/'.$del);
    @unlink(CHEMIN.$rep.'/'.$del);
}

/// CREATE DIRECTORY
if (!empty($newrep)) {
    if (stristr($newrep, 'script') !== false) {
        echo BeginJavascript().'
alert("'.$admin[987].'");
history.back();'.EndJavascript();
    } else {
        $tmp = explode('/', $rep);
        if (in_array($tmp[0], $list_baserep)) {
            $subreps = explode('/', $newrep);
            if (!in_array('..', $tmp) && !in_array('..', $subreps)) {
                $path = CHEMIN.$rep.'/';
                foreach ($subreps as $subrep) {
                    if (!is_dir($path.$subrep)) {
                        if (Makedir($path.$subrep)) {
                            SetChmod($path.$subrep);
                            @copy(CHEMIN.'admin/inc/index.php', $path.$subrep.'/index.php');
                        } else {
                            $msg = $path.$subrep.' '.$admin[20];
                            break;
                        }
                    }
                    $path .= $subrep.'/';
                }
                if (empty($msg)) {
                    $rep .= '/'.$newrep;
                }
            }
        }
    }
}

/// DELETE DIRECTORY
if (isset($_GET['delrep'])) {
	$delrep = import('delrep');
    $tmp = explode('/', $delrep);
    if (count($tmp) > 1) {
        $last = array_pop($tmp);
        if ($dh = opendir(CHEMIN.$delrep)) {
            $empty = true;
            $notdel = true;
            while ($empty && ($file = readdir($dh)) !== false) {
                switch ($file) {
                case '.' :
                case '..' :
                    break;
                case 'index.php' :
                    SetChmod(CHEMIN.$delrep.'/index.php');
                    $empty = @unlink(CHEMIN.$delrep.'/index.php');
                    break;
                default :
                    $empty = false;
                }
            }
            closedir($dh);
            if ($empty) {
                SetChmod(CHEMIN.$delrep);
                if (@rmdir(CHEMIN.$delrep)) {
                    if (!is_dir(CHEMIN.$delrep)) {
                        $delrep = implode('/', $tmp);
                        $notdel = false;
                    }
                }
            }
            if ($notdel) {
                $msg = $delrep.' : '.$admin[20].'!! &nbsp;nb '.$admin[171].' > 0';
            }
        }
    }
    $rep = $delrep;
}

/// UPLOAD FILE
$pagerror = 0;
if (isset($_FILES['ficup']) && !empty($_FILES['ficup']['name'])) {
		if((count($deniedExtFileUpload) > 0 && in_array(strrchr($_FILES['ficup']['name'],'.'),$deniedExtFileUpload))) { //AUTORISATION EXTENSION DU FICHIER
	     	$pagerror = 1; 
		 	$erreur = $admin[1104];	
		}
 
    if (is_uploaded_file($_FILES['ficup']['tmp_name']) && $pagerror != 1) {	//UPLOAD) {
        move_uploaded_file($_FILES['ficup']['tmp_name'], CHEMIN.$rep.'/'.$_FILES['ficup']['name']);
	   	SetChmod(CHEMIN.$rep.'/'.$_FILES['ficup']['name']);
    }
}

function ExtClass($extn){
    switch(strtolower($extn)) {
    case 'bmp'  :
    case 'gif'  : return 'fas fa-image text-secondary';
    case 'png'  : return 'far fa-image text-success';
    case 'jpeg' :
	case 'webp' : 
	case 'avif' :
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
	case 'mp4'  : 
    case 'mp3'  : 
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
if (version_compare(PHP_VERSION, '8.1.0') >= 0 && version_compare(PHP_VERSION, '8.5.0') < 0) {
	$imgTypes = array('bmp', 'gif', 'png', 'jpeg', 'jpg', 'webp', 'avif');
}
else
	$imgTypes = array('bmp', 'gif', 'png', 'jpeg', 'jpg', 'webp');
	
$newrep = '';
$candel = !in_array($rep, $list_baserep);
$list_rep = lister_repertoires($list_baserep);
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
$verifrep = str_replace('/','',$rep);
if (in_array($verifrep, $list_baserepbad) ) {
    die($admin[2028]);
}
$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[180]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[180], '100%');

echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=998&mnuid=937&tconfig=0#z2').$admin[181].'</p>
<hr />
<div class="w-100 m-auto" style="max-width:600px;">
  <fieldset>
  <form name="changedir" action="admin.php?lng='.$lng.'&amp;pg=upload" method="post">
    <div class="table-responsive-md">
    <table class="table">
      <tr>
        <td style="padding:5px;">
	      <p class="text-center mb-2">
            <div class="d-inline-block">
              <strong>'.$admin[182].'</strong>&nbsp;:&nbsp;<i class="fas fa-folder-open text-warning align-middle" style="font-size: 1.3rem;"></i>&nbsp;
            </div>
            <div class="d-inline-block">
              <select class="form-control font-weight-bold" name="rep" onchange="document.changedir.submit();">';
sort($list_rep);
for($i=0; $i<count($list_rep); $i++) {
    echo '
                <option value="'.$list_rep[$i].'"'.Selected($list_rep[$i] == $rep).">".$list_rep[$i]."</option>";
}
echo '
		      </select>
            </div>';
    if ($candel) {
        echo '
            <div class="d-inline-block">
		      <a class="btn btn-info bg-white ml-4" href="admin.php?lng='.$lng.'&amp;pg=upload&amp;delrep='.$rep.'" title="'.$admin[1724].' '.$rep.'"> 
		        <i class="fas fa-times text-danger align-middle" style="font-size: 1.3rem;" title="'.$admin[1724].' '.$rep.'"></i>
			  </a>
            </div>';
    }
    echo '
          </p>';
if (!empty($msg)) {
    echo '
		  <p class="text-center">'.$msg.'</p>';
}
?>
<style>
div.corpsFilenoScroll table {
 width:630px;
} 

div.corpsFileScroll table {
 width:100% !important;
 width:613px;
}
div.corpsFileScroll {
  margin-right: auto;
  margin-left: auto;
  max-width: 630px;
  height: 291px;
  border: 1px solid #C7C78F;
  overflow: auto;
}
</style>
<?php
echo '
		  <div class="text-center" style="padding-left:76px;">
		    <div class="d-inline-block mr-2">
              <i class="far fa-plus-square align-middle"></i>
              <i class="fas fa-folder-open text-warning align-middle" style="font-size: 1.3rem;" title="'.$admin[182].'"></i>
            </div>
            <div class="d-inline-block mr-1">
		      <input class="form-control" type="text" name="newrep" value="'.$newrep.'" size="25" />
            </div>
            <div class="d-inline-block">
			  '.SubmitButton($admin[1590].mb_strtolower($admin[182], $charset), '', '', $imgdroite).'
            </div>
		  </div>
        </td>
      </tr>
    </table>
    </div>
  </form>
  <hr />
  <div class="table-responsive-md '.(count($dbfiles) > 8 ? 'corpsFileScroll' : 'corpsFilenoScroll').'"> 
  <table class="table w-80 m-auto table-borderless">
    <tr>
      <td style="width:10%;text-align:center;"></td>
      <td style="width:50%"><strong>'.$admin[184].'</strong></td>
      <td style="width:20%;text-align:center;"><strong>'.$admin[185].'</strong></td>
      <td style="width:20%;text-align:center;"><strong>'.$admin[186].'</strong></td>
    </tr>';
?>
<script>
function confirmDelFile(rep,file,type) {
    if (type == 'file') message = '<?php echo $admin[1833]; ?> " '+file+' ".\n';
    if (type == 'dir') message = '<?php echo $admin[1834]; ?> " '+file+' " <?php echo $admin[1835]; ?>\n';
	message = message + " <?php echo $admin[1836]; ?>";
	x = false;
    x = window.confirm(message);
	if (x == true) {
	   window.location = "admin.php?lng=<?php echo $lng; ?>&pg=upload&rep=<?php echo $rep; ?>&actionfichier=2&del="+file;
	} else {
	      return false;
	}
}
</script>
<?php
for ($i = 0; $i < count($dbfiles); $i++) {
	if (strlen($dbfiles[$i][0]) > 34)
	 $mrnomfile = substr($dbfiles[$i][0], 0, 34).' ...';
	else 
		$mrnomfile = $dbfiles[$i][0];

    echo '
    <tr>
      <td class="text-center"><i class="'.$dbfiles[$i][1].'" style="font-size: 1.3rem;"></i></td>
	  <td>
		<div class="hover_img">
		  <a href="#" data-toggle="tooltip" data-placement="top" title="'.$dbfiles[$i][0].'">'.$mrnomfile.'
            <span'.($dbfiles[$i][3] ? '' : ' class="d-none"').'>
              <img src="'.CHEMIN.$rep.'/'.$dbfiles[$i][0].'" alt="" height="100px" />
            </span>
          </a>
		</div>
	  </td>
      <td class="text-center">'.$dbfiles[$i][2].' '.$admin[1725].'</td>
      <td class="text-center">
			  <i class="fas fa-times text-danger align-middle" 
			  onclick=\'confirmDelFile("'.$rep.'","'.$dbfiles[$i][0].'","file");\' 
			  style="cursor: pointer; font-size: 1.3rem;" title="'.$admin[187].'"></i>
	  </td>
    </tr>';
}
echo '
  </table>
  </div>
  </fieldset>
  <form name="uploadit" enctype="multipart/form-data" action="admin.php?lng='.$lng.'&amp;pg=upload" method="post">
    <input type="hidden" name="rep" value="'.$rep.'" />
    <fieldset>';
	if (isset($erreur)) 
	{
		echo '	<div class="alert alert-primary" role="alert">
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img" aria-label="Warning:">
    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
  </svg>  '.$erreur.'
			</div>';
	}
	echo '
    <p class="text-center"><i class="fas fa-file-upload text-info mr-2" style="font-size: 1.5rem;"></i><strong>'.$admin[188].'</strong></p>
    <p class="text-center px-5">';
	echo '  <div class="input-group mb-3"> 
		  <input class="form-control" type="file" name="ficup" id="inputGroupFile01" />';
	if ($lng != 'fr')
		echo '<label class="input-group-text" for="inputGroupFile01">'.$admin[793].'</label>';
	echo '
		  </div></p>
    </fieldset>
    <div class="quest" style="padding:2px 20px;">
      <div class="float-right">'.SubmitButton($admin[189], '', '', $imgdroite).'</div>
      <div class="clearfix"></div>
    </div>
  </form>
</div>';
echo '
<script>
$(document).ready(function(){
  $(\'[data-toggle="tooltip"]\').tooltip();
});
</script>'; 
btable();
include CHEMIN.'admin/bpage.inc';
?>
