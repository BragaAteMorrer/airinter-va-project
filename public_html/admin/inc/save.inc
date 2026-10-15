<?php
/*******************************************************************************
 *   Save
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
 * v6.00.19 (August 26, 2024) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'save.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[9] == '') {
        $nomzone = $admin[12];
        include 'access.inc';
        exit();
    }
}

if (!class_exists('ZipArchive')) {
	$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[1246]);
    include CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($admin[1246], '100%');
    echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=996&mnuid=937&tconfig=0#z2').$admin[1258].'</p><hr />
<div style="text-align:center;padding:24px;">'.$help[19].'</div><hr />';
    btable();
    include CHEMIN.'admin/bpage.inc' ;
    exit();
}

$action    = import('action', 'POST');
$mode      = import('mode', 'POST');
$otherdirs = mb_strtolower(import('otherdirs', 'POST'), $charset);
$dirnames  = array('data', 'file', 'img', 'pages', 'photo', 'skins');
$vdirs     = array();
$seldirs   = '';

if ($mode == 'SEL') {
	for ($i = 0; $i < count($dirnames); $i++) {
		$vdirs[]  = import($dirnames[$i], 'POST');
		$seldirs .= $vdirs[$i] == 'on' ? $dirnames[$i].'/ ' : '';
	}
	if (!empty($otherdirs)) {	
		$dbw = explode(';', $otherdirs);
        $bad = array('.', '..');
        $dbw = array_diff($dbw, $bad);
        foreach ($dbw as $dir) {
            while (substr($dir, -1) == '/') $dir = substr($dir, 0, -1);
            if (is_dir(CHEMIN.$dir)) {
                $vdirs[]    = 'on';
                $seldirs   .= $dir.'/ ';
                $dirnames[] = $dir;
            }
        }
	}
}
if ($action == 'zip') {
    set_time_limit(0);
    if ($seldirs == '') {
        echo BeginJavascript().'
alert("'.addslashes($admin[1248]).'");
window.location="admin.php?lng='.$lng.'&pg=save";
        '.EndJavascript();
    } else {
        $errfile = '';
        $errzip  = '';
        $dirs    = array();
        for ($i = 0; $i < count($dirnames); $i++) {
            if ($vdirs[$i] == 'on' && is_dir(CHEMIN.$dirnames[$i])) {
                $dir    = $dirnames[$i];  
                $dirs[] = $dir;
            }
        }
        $zipname = $userprefs[1].'-'.date('Y-m-d-H-i-s').'.zip';
        $zip     = new ZipArchive();
        if ($zip->open(SAVEREP.$zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            CreateDir(TEMPREP);
			StartTimer();
			$nf     = 0;
			$volume = 0;
			while (count($dirs)) {
				if (mb_strtolower($dir, $charset) == 'save') { // on exclue le répertoire des sauvegardes
					array_shift($dirs);
					continue;
				}
				$dir = $dirs[0] == '/' ? '' : $dirs[0];
				$zip->addEmptyDir($dir);
				if (false === ($files = scandir(CHEMIN.$dir))) {
					$errfile .= sprintf($admin[1257], $nf);
					break;
				} else {
					$bad   = array('.', '..');
					$files = array_diff($files, $bad); 
					foreach ($files as $file) {
						if (is_file(CHEMIN.$dir.'/'.$file)) {
							if (!$zip->addFile(CHEMIN.$dir.DIRECTORY_SEPARATOR.$file, $dir.DIRECTORY_SEPARATOR.$file)) {
								$errfile .= $dir.'/'.$file.$admin[1249]."\n ";
							} else {
								$volume += filesize(CHEMIN.$dir.'/'.$file);
								$nf++;
							}
						} elseif (is_dir(CHEMIN.$dir.'/'.$file) && mb_strtolower($file, $charset) != 'save') {
							if (!strpos(TEMPREP, $dir.'/'.$file)) $dirs[] = $dir.'/'.$file;
						}
					}
				}
				unset($files);
				array_shift($dirs);
			}
			$timer = StopTimer();
			if ($volume < 1024)
				$volume .= ' B';
			elseif ($volume < 1048576)
				$volume = round($volume / 1024, 2).' Kb';
			else
				$volume = round($volume / 1048576, 2) . ' Mb';
			$cr = "\n".$nf.$admin[1252].' ('.$volume.')'."\n".$seldirs."\n".$admin[1716].' '.$timer.' '.($timer <= 1 ? $admin[1717] : $admin[1718]);
			if (!$errfile) {
				if (false === $zip->setArchiveComment($admin[1245].' '.stripslashes($site[($lang[0] == $lng ? 0 : 11)]).' '.$admin[1601].' '.$userprefs[1]."\n".$nf.$admin[1252].' ('.$volume.')'."\n".$seldirs)) {
					$errzip .= $admin[1250].$zipname."\n\n";
				}
			}
			$zip->close();
        } else {
            $errzip .= $admin[1250].$zipname."\n\n";
        }
        unset($dirs, $zip);
        $erreur = $errzip.$errfile;
        if ($erreur) { 
            echo BeginJavascript().'
alert("'.addslashes($erreur).'");
window.location="admin.php?lng='.$lng.'&pg=save";
            '.EndJavascript();
			$action = '';
        } elseif ($nf > 0) {
            $action = 'save';
        }
    }
}

if ($action != 'save') {
	$headinc .= BeginJavascript().'
function setAllRep() {
	var lbl1="'.$admin[1719].'";
	var lbl2="'.$admin[1256].'";
	if (document.saverep.setbutton.value == lbl1) {
		var lbl  = lbl2;
		var bool = true;
	}
	else {
		var lbl  = lbl1;
		var bool = false;
	}
	document.saverep.setbutton.value = lbl;
	document.saverep.data.checked    = bool;
	document.saverep.file.checked    = bool;
	document.saverep.img.checked     = bool;
	document.saverep.pages.checked   = bool;
	document.saverep.photo.checked   = bool;
	document.saverep.skins.checked   = bool;
}
	'.EndJavascript();

	$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[1246]);
    include CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($admin[1246], '100%');
    echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=996&mnuid=937&tconfig=0#z2').$admin[1258].'</p>
<hr />
<div class="w-100 m-auto text-center" style="max-width:480px;">
  <p class="forum" style="text-align:left; padding:5px 20px;">'.$admin[1247].'</p>
  <div style="text-align:center;">
  <form name="saverep" action="admin.php?lng='.$lng.'&amp;pg=save" method="post">
    <input type="hidden" name="action" value="zip" />
    <input type="hidden" name="mode" value="SEL" />
    <fieldset>
	  <div id="selmode0" style="display:block;">'
        .$boutonleft.'<input name="setbutton" class="btn btn-info bouton" type="button" title="'.$admin[1719].'" onclick="setAllRep();" value="'.$admin[1719].'">'.$boutonright.'<br />';
    foreach ($dirnames as $dir) {
        echo '
        <div style="text-align:right; margin: 8px 200px 0 0">
          <label for="'.$dir.'">'.$dir.'/ <input type="checkbox" id="'.$dir.'" name="'.$dir.'" value="on" /></label>
        </div>';
    }
    echo '
	  </div>
	  <div id="selmode1" style="display:none; text-align:justify; color:#FF0000;">'.$help[18].'</div>
      <div id="selmode2" style="display:block;">
        <br />'.$admin[1255].'<br /><input type="text" class="form-control w-90 mx-auto texte" name="otherdirs" size="60" />
	  </div>
    </fieldset>
    <div class="row quest p-0 mt-2 w-100 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[1245], '', '', $imgdroite).'</div></div>
    </div>
  </form>
  </div>
</div>';
}

if ($action == 'save') {
	$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[1246]);
    include CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($admin[1246], '100%');
    echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=996&mnuid=937&tconfig=0#z2').$admin[1258].'</p>
<hr />
<div class="w-100 m-auto text-center" style="max-width:500px;">
  <p class="forum" style="text-align:left; padding:5px 20px;">'.$admin[1251].'</p>
  <fieldset>
    <br />
	<p style="text-align:center;"><b>'.$zipname.'</b></p>
	<p style="text-align:center;">'.str_replace("\n", '<br />', $cr).'</p>
	<br />
	<p style="text-align:center;">'.sprintf($admin[1253], 'admin.php?lng='.$lng.'&pg=upload').'</p>
	<br />
  </fieldset>
  <div class="quest" style="padding:2px 20px;">
    <div style="float:left">'.SubmitButton($admin[857], 'self.location.href=\'admin.php?lng='.$lng.'&pg=save\'', $imggauche, '').'</div>
    <div style="float:right">'.SubmitButton($admin[1720], 'self.location.href=\''.SAVEREP.$zipname.'\'', '', $imgdroite).'</div>
    <div style="clear:both"></div>
  </div>
</div>';
}

btable();
include CHEMIN.'admin/bpage.inc' ;
?>