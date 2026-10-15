<?php
/*******************************************************************************
 *   Error Track
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
 * v6.00.20 (October 24, 2024) : Integration news function about counter IP
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '');
@include CHEMIN.'inc/includes.inc';
$msg0 = '';
$msg1 = '';
$msg2 = '';
$msg3 = '';
$source = '';
$addr_ip = '';
$domaine = '';
$with_mail = '';
$msg = array();
@include 'inc/lang/'.$lng.'-help.inc';
$err = import('err');
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d');
$nomficlog = CHEMIN.'data/log/error403'.'_'.$dateheureminiutesecl.'.log';

$REMOTE_ADDR = strip_tags(addslashes(getIPadress($site[6] == 'on' ? (isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']): $_SERVER['REMOTE_ADDR'])));

if (!empty($err)) {
    $error_number = array('400', '401', '403', '404', '406', '500');
    if (!in_array(abs($err), $error_number)) {
        header('HTTP/1.0 403 Forbidden');
        die('STOP ! Bad error number ('.$err.')');
    }
    $with_mail = 'false';

    if (!is_numeric($err)) {
        header('HTTP/1.0 403 Forbidden');
        die('STOP ! $err is not numeric ('.$err.')');
    }
//		file_put_contents($nomficlog, date('Y-m-d H:i:s').'Adresse IP: '.$REMOTE_ADDR."\n", FILE_APPEND | LOCK_EX);
	$isvalidepoint = strpos($REMOTE_ADDR,'.');
	if ($isvalidepoint)
	{
		$ip = explode('.', $REMOTE_ADDR);
//			file_put_contents($nomficlog, date('Y-m-d H:i:s').'IP Count Adresse IP V4: '.count($ip)."\n", FILE_APPEND | LOCK_EX);
		if (count($ip) == 4)
		{
			foreach($ip as $n) {
				if (!is_numeric($n) || $n < 0 || $n > 255) {
//					file_put_contents($nomficlog, date('Y-m-d H:i:s').'Erreur Adresse IP V4: '.$REMOTE_ADDR."\n", FILE_APPEND | LOCK_EX);
					header('HTTP/1.0 403 Forbidden');
					die('STOP ! Bad IP ('.$REMOTE_ADDR.')');

				}
			}
		}
	}
	$isvalidepoint = strpos($REMOTE_ADDR,':');
	if ($isvalidepoint)
	{
		$ip = explode(':', $REMOTE_ADDR);
//		file_put_contents($nomficlog, date('Y-m-d H:i:s').'IP Count Adresse IP V6: '.count($ip)."\n", FILE_APPEND | LOCK_EX);
		if (count($ip) < 8)
		{
			header('HTTP/1.0 403 Forbidden');
			die('STOP ! Bad IP ('.$REMOTE_ADDR.')');

//			file_put_contents($nomficlog, date('Y-m-d H:i:s').'Erreur Adresse IP V6: '.$REMOTE_ADDR."\n", FILE_APPEND | LOCK_EX);
		}
	}
    if ($err < 0) {
        $with_mail = 'true';
        $err = abs($err);
    }

    $fichiers = array();
    if ($hd = opendir(DATAREP.'error')) {
        while ($fichier = readdir($hd)) {
            if (stristr($fichier, INCEXT) == TRUE ) {
                $fichiers[] = $fichier;
            }
        }
        closedir($hd);
    }
    $max_fic = 20;
    if (isset($serviz[50]) && ($serviz[50] > 0)) $max_fic = $serviz[50];
    $min_fic = max(0, $max_fic - 10);
    if (count($fichiers) > $max_fic) {
        for ($i=0; $i < count($fichiers) - $min_fic; $i++) {
            DestroyDBFile(DATAREP.'error/'.$fichiers[$i]);
        }
    }
    $msg0 = $errormsg[(string)$err][0];
    $msg1 = addslashes($errormsg[(string)$err][1]);
    $msg2 = addslashes($errormsg[(string)$err][2]);
    $msg3 = $errormsg[(string)$err][3];
    if (empty($msg0)) {
        $msg0 = 'Unattended error';
        $msg1 = 'Unattended error';
        $msg2 = 'See the <a href="http://www.apachefrance.com/Articles/7/page2.html" alt="">errors code HTTP</a>.';
        $msg3 = 'HTTP/1.0 403 Forbidden';
    }
    $date = date("d/m/Y H:i:s");
    $dest = $_SERVER['REQUEST_URI'];
    $domaine = gethostbyaddr($REMOTE_ADDR);

    $err = addslashes($err);
    $dest = strip_tags(addslashes($dest));
	$HTTP_REFERER = '';
	$HTTP_USER_AGENT = '';	
	if (isset($_SERVER['HTTP_REFERER'])) $_SERVER['HTTP_REFERER'] = '';
    else
		$HTTP_REFERER = strip_tags(addslashes($_SERVER['HTTP_REFERER']));
	if (isset($_SERVER['HTTP_USER_AGENT'])) $_SERVER['HTTP_USER_AGENT'] = '';
	else
		$HTTP_USER_AGENT = strip_tags(addslashes($_SERVER['HTTP_USER_AGENT']));

    $mettre = "<?php
\$err = '$err';
\$msg0 = '$msg0';
\$msg1 = '$msg1';
\$msg2 = '$msg2';
\$msg3 = '$msg3';
\$date = 'Date : $date';
\$dest = 'Page requested : $dest';
\$source = 'Page source : $HTTP_REFERER';
\$browser = 'Browser : $HTTP_USER_AGENT';
\$addr_ip = 'IP address : $REMOTE_ADDR';
\$domaine = 'Domaine : $domaine';
\$with_mail = $with_mail;
";

    $errorId = date('Ymd_His_').$err;
    WriteFullDB(DATAREP."error/".$errorId.INCEXT, $mettre);
    header($msg3);
    header('location:'.$site[3].($site['URLR'] == 'on'? $lng.'-'.$urlrw[0].'-99-'.$errorId: 'error.php?lng='.$lng.'&errorId='.$errorId));
}
else {
    $errorId = preg_replace("`[^0-9_]`", "", import('errorId'));
    if (!is_file(DATAREP."error/".$errorId.INCEXT)) {
        header("HTTP/1.0 404 Not Found");
    }

    include DATAREP.'error/'.$errorId.INCEXT;

    header($msg3);
    $topmess = "Error ".$err." : ".$msg0;
    include 'inc/hpage.inc';
    htable($topmess, "100%");
    echo "<b>".stripslashes($msg1)."</b><br />".stripslashes($msg2);
    echo "<hr /><b>Context of the error</b><br />".$dest."<br />".$source."<br />".
        $browser."<br />".$addr_ip."<br />".$domaine;

    if ($with_mail) {
        echo "<br /><b>This context is recorded and communicated to the webmaster.</b>";
        eMailHtmlTo(
            stripslashes($site[($lang[0] == $lng ? 0 : 11)])." : ERROR ".$err,
            "Site : ".stripslashes($site[($lang[0] == $lng ? 0 : 11)])."<br />ERROR ".$err." : ".$msg0."<br />".$date."<br />".
            $dest."<br />".$source."\n".$browser."<br />".$addr_ip."<br />".$domaine);
    }
    btable();
    include 'inc/bpage.inc';
}
?>