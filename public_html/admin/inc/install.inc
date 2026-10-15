<?php
/*******************************************************************************
 *   Installer une mise à jour ou extension de GuppY
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
 * v6.00.24 (August 12, 2026) : New functions in Manifest file
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'install.inc')) {
    header('location:../index.php');
    die();
}
if (!isAdmin($userprefs[1])) {
        $nomzone = $admin[2028];
        include 'access.inc';
        exit();
}
$ctrlmdp = abs(crc32($mdp));
$admcookie = '';
$okctrlmdp = false;
if (isset($_COOKIE[ADMP_COOKIE]))
{
	$admcookie = $_COOKIE[ADMP_COOKIE];
	$strlen    = substr($admcookie, 0, 2);
	$okctrlmdp = substr($admcookie, 2, intval($strlen)) == $ctrlmdp ? true : false;
}
else
{
    $admcookie  = $ctrlmdp.'-img-file-img-3-1-1-web-web';
    $adminprefs = explode('-', $admcookie);
    $admdata    = '';
    for ($i = 0; $i < count($adminprefs); $i++) {
        $strlen   = sprintf("%02d", strlen($adminprefs[$i]));
        $admdata .= $strlen.$adminprefs[$i];
    }
    setcookie(ADMP_COOKIE, $admdata);
    echo BeginJavascript().'
window.location = "admin.php?lng='.$lng.'";'.EndJavascript();
}
if (!$okctrlmdp ) {
    die($admin[2028]);
}
function MakeDirectory($fulldir) {
    /// $fulldir est un chemin de répertoire relatif à la racine
    /// utiliser MakeDirectory("data/plugin/toto") et non CHEMIN."data/plugin/toto"
    $dirs    = explode('/', $fulldir);
    $currdir = '';
    foreach ($dirs as $dir) {
        $currdir .= $dir.'/';
        if (!is_dir(CHEMIN.$currdir)) {
            MakeDir(CHEMIN.$currdir);
			SetChmod(CHEMIN.$currdir);
        }
    }
    return is_dir($fulldir);
}

function RemoveDirectory($basedir) {
    /// $basedir est un chemin relatif au répertoire courant
    /// utiliser RemoveDirectory(CHEMIN."data/plugins/toto");
    if (!is_dir($basedir)) return FALSE;
    $items = scandir($basedir);
    foreach($items as $item) {
        switch ($item) {
        case '.' :
        case '..' :
            break;
        case '.htaccess' : /// bizarre mais nécessaire
        default :
            if (is_file($basedir.'/'.$item)) {
                @unlink($basedir.'/'.$item);
            } elseif (is_dir($basedir.'/'.$item)) {
                RemoveDirectory($basedir.'/'.$item);
            }
        }
    }
    usleep(1000); /// bizarre mais nécessaire
    return @rmdir($basedir);
}

if (!function_exists('parse_ini_string')) {
	function parse_ini_string($str) {
		if (empty($str)) return false;
		$lines          = explode("\n", $str);
		$ret            = Array();
		$inside_section = false;
		foreach ($lines as $line) {   
			$line = trim($line);
			if (!$line || $line[0] == "#" || $line[0] == ";") continue;	   
			if ($line[0] == "[" && $endIdx = strpos($line, "]")) {
				$inside_section = substr($line, 1, $endIdx-1);
				continue;
			}
			if (!strpos($line, '=')) continue;
			$tmp = explode("=", $line, 2);
			if ($inside_section) {
				$key   = rtrim($tmp[0]);
				$value = ltrim($tmp[1]);
				if (preg_match("/^\".*\"$/", $value) || preg_match("/^'.*'$/", $value)) {
					$value = mb_substr($value, 1, mb_strlen($value) - 2);
				}
				$t = preg_match("^\[(.*?)\]^", $key, $matches);
				if (!empty($matches) && isset($matches[0])) {
					$arr_name = preg_replace('#\[(.*?)\]#is', '', $key);
					if (!isset($ret[$inside_section][$arr_name]) || !is_array($ret[$inside_section][$arr_name])) {
						$ret[$inside_section][$arr_name] = array();
					}
					if (isset($matches[1]) && !empty($matches[1])) {
						$ret[$inside_section][$arr_name][$matches[1]] = $value;
					} else {
						$ret[$inside_section][$arr_name][] = $value;
					}
				} else {
					$ret[$inside_section][trim($tmp[0])] = $value;
				}           
			} else {	   
				$ret[trim($tmp[0])] = ltrim($tmp[1]);
			}
		}
		return $ret;
	}
}

$headinc = '
<style type="text/css">
.error { text-align: center; color: red; background-color: yellow; font-weight: bold; padding: 5px; }
.c { text-align: center; }
.b { font-weight: bold; }
.cb { text-align: center; font-weight: bold; }
.li { position: relative; }
.c1 { float: left; width: 100px; }
.c2 { float: left; width: 10px; }
.c3 { font-weight: bold; margin-left: 110px;}
.fl { float: left; }
.fr { float: right; }
.clear { clear: both; }
fieldset { padding: 8px; }
</style>';


$step = isset($_POST['step']) ? $_POST['step'] : 1;
if (!in_array($step, array(1, 2, 3))) $step = 1;

$intro = $admin[1127];
$content   = '';
switch ($step) {
case 1 :
	$pmax      = substr(@ini_get('post_max_size'), 0, -1);
	$umax      = substr(@ini_get('upload_max_filesize'), 0, -1);
	$maxsize   = min($pmax, $umax);
    $introStep = $admin[1128];
    $content   .= ($maxsize > 0 ? sprintf($admin[1444], $maxsize.'M') : 'No values set !').'
<br />
<form name="zipinst" method="post" action="admin.php?lng='.$lng.'&amp;pg=install" enctype="multipart/form-data">
<fieldset class="w-100 m-0 p-3">
<input type="hidden" name="step" value="2" />
<div class="c">'.$admin[1129].' 
  <input class="form-control w-70 mx-auto mt-2" type="file" name="filezip" accept="application/zip" />
</div>
</fieldset>
<div class="quest mt-2" style="padding:2px 20px;">
  <div class="fr">'.SubmitButton($admin[1100], '', '', $imgdroite).'</div>
  <div class="clear"></div>
</div>
</form>
';
    break;
    
case 2 :
    $introStep = $admin[1130];
    $zipfile   = trim(strip_tags($_FILES['filezip']['name']));
	$tmpname   = $_FILES['filezip']['tmp_name'];
	$zipname   = TEMPREP.$zipfile;
    $error     = '';
    $stop      = FALSE;
    if (!empty($zipfile)) 
	{
        $extension = mb_strtolower(substr($zipfile, -4), $charset);
        if ('.zip' != $extension) {
            // Erreur : Mauvaise extension
            $content = '
<form name="zipinst" method="post" action="admin.php?lng='.$lng.'&amp;pg=install">
<fieldset class="w-100 m-0 p-0">
<input type="hidden" name="step" value="1" />
<p class="error">'.$admin[1131].'</p>
</fieldset>
<div class="quest" style="padding:2px 20px;">
  <div class="fl">'.SubmitButton($admin[1101], '', $imggauche, '', '').'</div>
  <div class="clear"></div>
</div>
</form>';
        } 
		else 
		{
            if ($zipname != '' && is_uploaded_file($tmpname)) {
                move_uploaded_file($tmpname, $zipname);
                $zip = new ZipArchive;
                $cr  = $zip->open($zipname);
                if (FALSE === $cr) {
                    // Echec ouverture du fichier zip
                    DestroyDBFile($zipname);
                    $content = '
<form name="zipinst" method="post" action="admin.php?lng='.$lng.'&amp;pg=install">
<fieldset class="w-100 m-0 p-0">
<input type="hidden" name="step" value="1" />
<p class="error">'.$admin[1132].'</p>
</fieldset>
<div class="quest" style="padding:2px 20px;">
  <div class="fl">'.SubmitButton($admin[1101], '', $imggauche, '').'</div>
  <div class="clear"></div>
</div>
</form>';
                } 
				else 
				{
                    $comment = '
						<ul>
						  <li>'.$admin[184].' : <strong>'.$zipfile.'</strong></li>
						</ul>';									
                    $cr = $zip->locateName('Manifest');
                    $manifest = '';
                    if (FALSE !== $cr) {
                        // Il y a un fichier 'Manifest'...
                        $manifest = '
							<ul>
							  <li>'.$admin[1135].'
								<ul>';
                        $str = $zip->getFromIndex($cr);
                        $datas = parse_ini_string($str, TRUE);
                        if (isset($datas['infos'])) {
                            foreach ($datas['infos'] as $key => $val) 
							{
								$val = mb_check_encoding($val, 'UTF-8') ? $val : mb_convert_encoding($val,"UTF-8", "ISO-8859-1");
                                $manifest .= '
								  <li class="li">
									<div class="c1">'.ucfirst($key).'</div><div class="c2"> : </div><div class="c3">'.nl2br($val).'</div>
								  </li>';
                            }

                        }
                        if (isset($datas['check'])) {
                            foreach ($datas['check'] as $key => $val) 
							{
								$val = mb_check_encoding($val, 'UTF-8') ? $val : mb_convert_encoding($val,"UTF-8", "ISO-8859-1");
                                $manifest .= '
								  <li class="li">
									<div class="c1">'.ucfirst($key).'</div><div class="c2"> : </div><div class="c3">'.nl2br($val).'</div>
								  </li>';
                            }

                        }
                            $manifest .= '
    </ul>
  </li>
</ul>';
						$error = '';
						$msg = '';
                        if (isset($datas['check']) && isset($datas['check']['type'])) 
						{
							$guppy_vmin = isset($datas['check']['guppy_vmin']) ? $datas['check']['guppy_vmin'] : NULL;
                            $guppy_vmax = isset($datas['check']['guppy_vmax']) ? $datas['check']['guppy_vmax'] : NULL;
												
                            $ok_vmin = isset($guppy_vmin) ? version_compare($guppy_vmin, MP_VERSION, '<=') : TRUE;
                            $ok_vmax = isset($guppy_vmax) ? version_compare(MP_VERSION, $guppy_vmax, '<=') : TRUE;
                            $stop = !$ok_vmin;
                            $msg .= !$ok_vmin ? sprintf($admin[1394], $guppy_vmin, MP_VERSION) : '';
                            $stop |= !$ok_vmax;
                            switch ($datas['check']['type']) {
								case 'guppy-pack' :
									$msg .= !$ok_vmax ? sprintf($admin[1395], $guppy_vmax, MP_VERSION) : $msg;
								break;
								case 'guppy-cumul' :
									$msg .= !$ok_vmax ? sprintf($admin[1396], $guppy_vmax, MP_VERSION) : $msg;
									
								break;
								case 'guppy-non-cumul' :
									$msg .= !$ok_vmax ? sprintf($admin[1397], $guppy_vmin, MP_VERSION) : $msg;
								break;
								case 'plugin' :
								case 'skin' :
									$ok_vmin = isset($guppy_vmin) ? version_compare($guppy_vmin, MP_VERSION, '<=') : TRUE;
									$msg .= !$ok_vmax ? sprintf($admin[1398], $guppy_vmax, MP_VERSION) : $msg;
								break;
                            }
                        }
                        $error = '';
                        if ($stop) {
                            $error = '
								<p class="error">'.$admin[1393].'<br /><br />'.$msg.'</p>';
                        }
                    }
					else
					{   
						$stop = false;
						$msg = $admin[2014];
						$error = '
							<p class="error">'.$admin[1393].'<br /><br />'.$msg.'</p>';	
					}
                    $zip->close();
                    $content = '
						<form name="zipinst" method="post" action="admin.php?lng='.$lng.'&amp;pg=install">
						<fieldset class="w-100 m-0 p-2">
						<input type="hidden" name="step" value="3" />
						<input type="hidden" name="zipname" value="'.$zipname.'" />
						<p style="text-align:center;">'.$admin[1137].'</p>
						<fieldset class="w-100 m-0 p-0"><legend>Informations sur le fichier ZIP</legend>
						'.$comment.'
						'.$manifest.'
						'.$error.'
						</fieldset>
						<div class="quest mt-2" style="padding:2px 20px;">
						  <div class="fl">'.SubmitButton($admin[1138], 'step.value=1;', $imggauche, '').'</div>';
                    if (!$stop) {
                        $content .= '
  <div class="fr">'.SubmitButton($admin[1139], '', '', $imgdroite).'</div>';
                    }
                    $content .= '
  <div class="clear"></div>
</div>
</form>';
                }
            } else {
                // Le fichier n'est pas chargé
                $content = '
<form name="zipinst" method="post" action="admin.php?lng='.$lng.'&amp;pg=install">
<fieldset class="w-100 m-0 p-0">
<input type="hidden" name="step" value="1" />
<p class="error">'.$admin[1103].'</p>
</fieldset>
<div class="quest" style="padding:2px 20px;">
  <div class="fl">'.SubmitButton($admin[1101], '', $imggauche, '').'</div>
  <div class="clear"></div>
</div>
</form>';
            }
        }
    } else {
        // Pas de nom de fichier
        header('location:admin.php?lng='.$lng.'&pg=install');
        exit;
    }
	break;

case 3 :
    // Installation des fichiers contenus dans l'archive
    $zipname = import('zipname');
    $introStep = $admin[1141];
    $msg = $errors = '';
    $zip = new ZipArchive;
    $cr  = $zip->open($zipname);
    if (FALSE === $cr) {
        // Problème à l'ouverture de l'archive
        $error = $admin[1140];
    } else {
        $cr = $zip->locateName('Manifest');
        $manifest = array();
        $excludes = array();
        if (FALSE !== $cr) {
            // Il y a un fichier 'Manifest'...
			$str = $zip->getFromIndex($cr);
			$manifest = parse_ini_string($str, TRUE);

			// Traitement des effacements préalables
			if (isset($manifest['delete'])) {
				$dirs = isset($manifest['delete']['dirs']) ? explode(' ', $manifest['delete']['dirs']) : '';
				$files = isset($manifest['delete']['files']) ? explode(' ', $manifest['delete']['files']) : '';
				$cr = '';
				if (!empty($files)) {
					foreach($files as $file) {
						if (is_file(CHEMIN.$file)) {
							@unlink(CHEMIN.$file);
							$cr .= sprintf($admin[1400], $file).(is_file(CHEMIN.$file) ? $admin[1404] : $admin[1403]);
						}
					}
				}
				if (!empty($dirs)) {
					foreach($dirs as $dir) {
						if (is_dir(CHEMIN.$dir)) {
							RemoveDirectory(CHEMIN.$dir);
							$cr .= sprintf($admin[1401], $dir).(is_dir(CHEMIN.$dir) ? $admin[1404] : $admin[1403]);
						}
					}
				}
				if (!empty($cr)) {
					$msg .= $admin[1399].$cr;
				}
			}

			// Préparation de la liste des exclusions (à ne pas installer)
			$excludes = array('Manifest');
			if (isset($manifest['script']['include'])) {
				$include = $manifest['script']['include'];
				$cr = $zip->locateName($include);
				if (FALSE === $cr) {
					// rien à inclure => pas de script
					unset($manifest['script']);
				} else {
					$datas = $zip->getFromIndex($cr);
					file_put_contents(TEMPREP.$include, $datas);
					include_once TEMPREP.$include;
					$excludes[] = $include;
				}
			}
			// Préparation de la liste des exclusions (à ne pas installer)
			if (isset($manifest['scriptsuite']['include'])) {
				$includesuite = $manifest['scriptsuite']['include'];
				$cr = $zip->locateName($includesuite);
				if (FALSE === $cr) {
					// rien à inclure => pas de script suite
					unset($manifest['scriptsuite']);
				} else {
					$datas = $zip->getFromIndex($cr);
					file_put_contents(TEMPREP.$includesuite, $datas);
					include_once TEMPREP.$includesuite;
					$excludes[] = $includesuite;
				}
			}
		}
        // Installation de l'archive
        $cr  = $zip->open($zipname);
        $index = 0;
        do {
            $name = $zip->getNameIndex($index); 

			if (FALSE !== $name && !in_array($name, $excludes)) {
                if ('/' == substr($name, -1, 1)) {
                    // Cas d'un répertoire
                    MakeDirectory($name);
                } 
				else 
				{
                    // cas d'un fichier
                    if (!is_dir(dirname(CHEMIN.$name))) {
                        MakeDirectory(dirname($name));
                    }
                    $data = $zip->getFromIndex($index);
                    if (is_file(CHEMIN.$name)) {
                        $perms = substr(sprintf('%o', fileperms(CHEMIN.$name)), -4);
                        SetChmod(CHEMIN.$name);
                        @file_put_contents(CHEMIN.$name, $data);
                        SetChmod(CHEMIN.$name, $perms);
                    } else
                        @file_put_contents(CHEMIN.$name, $data);
                }
            }
            $index++;
        } while (FALSE != $name);
        // Traitement des CHMOD (sauf sous Windows)
        if (isset($manifest['chmod']) && !defined(PHP_WINDOWS_VERSION_MAJOR)) {
            $cr = '';
            if (!empty($manifest['chmod'])) {
                foreach($manifest['chmod'] as $file => $chmod) {
                    if (is_file(CHEMIN.$file)) {
                        $bool = @chmod(CHEMIN.$file, intval($chmod, 8));
                        $cr .= sprintf($admin[1400], $file).($bool ? $admin[1403] : $admin[1405]);
                    }
                    elseif (is_dir(CHEMIN.$file)) {
                        $bool = @chmod(CHEMIN.$file, intval($chmod, 8));
                        $cr .= sprintf($admin[1401], $file).($bool ? $admin[1403] : $admin[1405]);
                    }
                }
            }
            if (!empty($cr)) {
                $msg .= $admin[1402].$cr;
            }
        }
        // Exécution du script de post-installation 
        if (isset($manifest['script']['function'])) {
            if (function_exists($manifest['script']['function'])) {
                $msg .= $manifest['script']['function']($manifest);
            }
            @unlink(TEMPREP.$include);
        }
        if (isset($manifest['scriptsuite']['function'])) {
            if (function_exists($manifest['scriptsuite']['function'])) {
                $msg .= $manifest['scriptsuite']['function']($manifest);
            }
            @unlink(TEMPREP.$includesuite);
        }
       // Traitement des règles de bannissement id à 0
        if (isset($manifest['security']) && !defined(PHP_WINDOWS_VERSION_MAJOR)) {
			include_once CHEMIN.INCREP.'security.inc';
			include_once CHEMIN.INCREP.'functions.php';
			switch ($manifest['security']['majbansecdef']) {
			case 'yes' :
				$msg = sprintf($admin[1006], MP_VERSION);
//				$cfg = ReadSecurityConfig(BAN_CFG);
				$def = ReadSecurityDatas(BAN_DEF);
				$xdef = array('id'     => '99', 
					'status' => 'on', 
					'ip'     => '195.113.175.60',
					'host'   => 'undefined.hostname.localhost,vmi2035320.contaboserver.net,ows.lexis.it4i.cz',
					'pseudo' => '',
					'email'  => '',
					'path'   => 'hacker.com,onerror,php-cgi,allow_url,XSS,xss,xxs,XXS,noimage.png,alert(,String.fromCharCode(,ONLOAD=,url(javascript,url(&amp;#1;javascript,somevariables=maliciouscode,variables=maliciouscode,variables=maliciouscode,JaVaScRiPt,%60javascript%3Aalert,j%20a%20v%20a%20s%20c%2,href=\"&amp;#,onmouseover=\"alert(document.cookie),jav&amp;#x0A;ascript,jav&amp;#x0D;ascript,vbscript:,xss.css,console.log(),AND 1=1 OR,**/AND/**,**/OR/**,(SELECT/*,string,UPDATEXML(,SELECT/**,GROUP/**,LIKE/**,PROCEDURE/**/,AutoFocus,ContentEditable,OnFocusIn,autofocus,contenteditable,al\x65rt,%3C\u003c,ContentsEditable,callback,Editable,OnFocus',				  
					'agent'  => '',
					'mots'   => 'hacker.com,onerror,php-cgi,allow_url,XSS,xss,xxs,XXS,noimage.png,alert(,String.fromCharCode(,ONLOAD=,url(javascript,url(&amp;#1;javascript,somevariables=maliciouscode,variables=maliciouscode,variables=maliciouscode,JaVaScRiPt,%60javascript%3Aalert,j%20a%20v%20a%20s%20c%2,href=\"&amp;#,onmouseover=\"alert(document.cookie),jav&amp;#x0A;ascript,jav&amp;#x0D;ascript,vbscript:,xss.css,console.log(),AND 1=1 OR,**/AND/**,**/OR/**,(SELECT/*,string,UPDATEXML(,SELECT/**,GROUP/**,LIKE/**,PROCEDURE/**/,AutoFocus,ContentEditable,OnFocusIn,autofocus,contenteditable,al\x65rt,%3C\u003c,ContentsEditable,callback,Editable,OnFocus',
					'msg'    => '403',
					'url'    => 'error403.php');
				$def[$xdef['id']] = $xdef;
//				if ($cfg['nextid'] == $xdef['id']) {
//					$cfg['nextid']++;
//				WriteSecurityConfig(BAN_CFG, $cfg);
				WriteSecurityDatas(BAN_DEF, $def);
			break;
			}
        }
        // Nettoyage
        $zip->close();
        DestroyDBFile($zipname);
        $content = '
<form name="zipinst" method="post" action="admin.php?lng='.$lng.'&amp;pg=install">
<fieldset class="w-100 m-0 p-0">
<input type="hidden" name="step" value="1" />';
        if (!empty($msg)) {
            $content .= '
<p class="c">'.$msg.'</p>';
        }
        if (empty($error)) {
            $content .= '
<p class="cb">'.$admin[1142].'</p>';
        } else {
            $content .= '
<p class="error">'.$error.'</p>';
        }
        $content .= '
</fieldset>
<div class="quest mt-2" style="padding:2px 20px;">
  <div class="fr">'.SubmitButton($admin[1143], '', '', $imgdroite).'</div>
  <div class="clear"></div>
</div>
</form>';
    }
	break;
}

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.$admin[1126];
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[1126], '100%');
CreateDir(TEMPREP);

echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=994&mnuid=937&tconfig=0#z2').$intro.'</p>
<hr />';

if (empty(trim($serviz[31]).trim($serviz[176]))) {
    echo BeginJavascript().'
alert("'.$admin[1753].'");
window.location = "admin.php?lng='.$lng.'&pg=config5";'.EndJavascript();
} 
elseif (!class_exists('ZipArchive'))
    echo '
<div style="text-align:center;padding:24px;">'.$help[19].'</div><hr />';
else
    echo '
<div class="w-100 m-auto" style="max-width:800px;">
<p class="forum" style="text-align:left; padding:5px 20px;">'.$introStep.'</p>
'.$content.'
</div>';

btable();
include CHEMIN.'admin/bpage.inc';
?>