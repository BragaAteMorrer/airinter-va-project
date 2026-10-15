<?php
/*******************************************************************************
 *   Admin Technicals Information
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
 * v6.00.03 (August 10, 2021) : adaptation to php 8
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'infos.inc')) {
    header('location:../index.php');
    die();
}

$itact = import('itact');

// Show phpinfo() output
if ($itact == 'phpinfo') {
	phpinfo();
	exit();
}
else {
	// Check for upgrade : code inspired from punbb and pwg
	$version           = array();
	$version['actual'] = trim(MP_VERSION);
	$version['url']    = 'https://www.guppyland.org/update.txt';
	$load_ok           = FALSE;
	$lines             = array();
	if (ini_get('allow_url_fopen')) {
		$lines = @file($version['url']);
		$load_ok = TRUE;
	}
	elseif ($curl = @curl_init($version['url'])){
		$timeout = 5; // set to zero for no timeout
		curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
		$version_content = curl_exec($curl);
		curl_close($curl);
		if ($version_content != FALSE) {
			$lines   = explode("\n", $version_content);
			$load_ok = TRUE;
		}
	}
	else {
		$version['errors'] = $admin[898];
	}
    if ($load_ok) {
        $version['latest']   = @trim($lines[0]);
        $version['previous'] = @trim($lines[2]);
        $version['note']     = $lng == $lang[0] ? @trim($lines[6]) : @trim($lines[7]);
        $version['cur'] = str_replace('-', '.0', $version['actual']);
        $version['lat'] = str_replace('-', '.0', $version['latest']);
        $version['pre'] = str_replace('-', '.0', $version['previous']);
        $acur = explode('.', $version['cur']);
        $alat = explode('.', $version['lat']);
        $apre = explode('.', $version['pre']);
        $an   = max(count($acur), count($alat), count($apre));
        if ($an > count($alat)) $version['lat'] .= '.00';
        if ($an > count($acur)) $version['cur'] .= '.00';
        if ($an > count($apre)) $version['pre'] .= '.00';
        $ncur = intval(str_replace('.', '', $version['cur']));
        $npre = intval(str_replace('.', '', $version['pre']));
        $nlat = intval(str_replace('.', '', $version['lat']));
        $rpre = intval(substr(str_replace('.', '', $version['pre']), 0, 3));
        $rlat = intval(substr(str_replace('.', '', $version['lat']), 0, 3));
        $cmp  = version_compare($version['cur'], $version['lat']);
        $cmp  = $ncur == $nlat ? 0 : $cmp;
        switch ($version['latest']){
        case '':
            $version['errors'] = $admin[899];
            break;
        default:
            switch ($cmp) {
            case -1 :
                $reco1 = '';
                $reco2 = '';
                $reco3 = '';
                if ($acur[0] < $alat[0]) {
                    $reco3 = $admin[1273];
                } else {
                    if ((($ncur - $npre) == 0 || ($rlat - $rpre) == 1) && ($npre - $ncur) < 100) $reco1 = $admin[1273]; else $reco2 = $admin[1273];
                }
                $version['ok'] = '
                  <span style="color:green;">'.
                    $admin[900].'<br />'.
                    $admin[901].' '.$version['latest'].' '.$admin[902].' '.trim($lines[1]).'
                  </span><br /><br />'.
                  $admin[1271].'
                  <ul>
                    <li><a href="'.trim($lines[4]).'" target="_blank" title="'.$admin[1269].'">'.$admin[1269].'</a> '.$reco1.'</li>
                    <li><a href="'.trim($lines[5]).'" target="_blank" title="'.$admin[1270].'">'.$admin[1270].'</a> '.$reco2.'</li>
                    <li><a href="'.trim($lines[3]).'" target="_blank" title="'.$admin[1272].$version['latest'].'">'.$admin[1272].$version['latest'].'</a> '.$reco3.'</li>
                  </ul>';
                break;
            case 0 :
                $version['ok'] = $admin[903];
                break;
            case 1 :
                $version['ok'] = $admin[1274];
                break;
            }
        }
    }
	$php_os  = PHP_OS;
	$php_rel = @php_uname('r');
	echo '
  <hr />
  <p style="text-align:center;"><strong>'.$admin[895].'</strong></p>
  <hr />
  <br />
  <div class="rep w-98 m-auto text-start overflow-auto p-4" style="max-width:632px;height:220px;">
    <fieldset>'.legend($admin[904]).'
	  <ul>
	    <li>GuppY '.$version['actual'].'</li>
	    <li>';
	if (!empty($version['ok'])) {
		echo '
		  <span style="color:green;">'.$version['ok'];
	}
	else {
		echo '
		  <span style="color:red;">'.$version['errors'];
	}
    $apache = function_exists('apache_get_version') ? @apache_get_version().'/' : '';
	$met    = 'MET='.@ini_get('max_execution_time');
	$ml     = 'ML='.@ini_get('memory_limit');
	$umf    = 'UMF='.@ini_get('upload_max_filesize');
	$pms    = 'PMS='.@ini_get('post_max_size');
	$mfu    = 'MFU='.@ini_get('max_file_uploads');
    echo '</span>
	    </li>
	  </ul>
    </fieldset>
    <fieldset>'.legend($admin[905]).'
	  <ul>
        <li>'.$admin[906].'&nbsp;:&nbsp;'.$apache.$php_os.' '.$php_rel.'</li>
        <li>PHP&nbsp;:&nbsp;'.phpversion().' ( <a href="admin.php?lng='.$lng.'&amp;pg=infos&amp;itact=phpinfo" target="_blank" title="'.$admin[907].'"><span style="color:#FF0000;"><strong>'.$admin[907].'</strong></span></a> )</li>
		<li>'.$met.' - '.$ml.' - '.$umf.' - '.$pms.' - '.$mfu.'</li>
	    <li>'.$admin[53].$site[3].'</li>
		<li>UA: '.$_SERVER["HTTP_USER_AGENT"].'</li>
	  </ul>
    </fieldset>';
	$pluginlist = ExploreDir('plugins/');
	if (!empty($pluginlist)) {
        echo '
    <fieldset>'.legend($admin[908]).'
	  <ul>';
        $listplugin = 'Plugins : ';
        for ($i = 0; $i < count($pluginlist); $i++) {
            include("plugins/".$pluginlist[$i]."/plugin.inc");
            echo '
        <li><a href="admin.php?lng='.$lng.'&amp;pg=plugin&amp;plug='.$plugin_admin_url.'">'.strip_tags($plugin_admin_name).'</a></li>';
            $listplugin .= ($i != 0) ? ', ' : '';
            $listplugin .= strip_tags($plugin_admin_name);
        }
        echo '
	  </ul>
    </fieldset>';
	}
    echo '
    <br />
  </div>
  <br />
  <div class="w-98 m-auto text-start overflow-auto" style="max-width:640px;">
  <hr />
  <p class="text-center"><strong>'.$admin[909].' :</strong></p>
  <hr />'.BeginJavascript().'
window.CopyToClipboard = function (rSource) {
    rSource.select();
    if(window.clipboardData){
        var r=clipboardData.setData("Text",rSource.value);
        rSource.blur();
        return 1;
    } else
        return 0;
}'.EndJavascript().'

    <textarea id="infos" rows="10" class="w-100 text-start p-2" style="max-width:620px;" 
      onfocus="if(window.CopyToClipboard(this)) alert(\''.$admin[910].'\');">
GuppY '.$version['actual'].'
'.$admin[906].'&nbsp;:&nbsp;'.$apache.$php_os.' '.$php_rel.'
PHP&nbsp;:&nbsp;'.phpversion().' / '.$met.' - '.$ml.' - '.$umf.' - '.$pms.' - '.$mfu.'
'.$admin[53].$site[3];
	if (!empty($pluginlist)) {
        echo "\n".$listplugin;
    }
    echo '
UA: '.$_SERVER["HTTP_USER_AGENT"].'
    </textarea>
  </div>';
}
?>