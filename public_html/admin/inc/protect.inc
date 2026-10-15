<?php
/*******************************************************************************
 *   Admin Protect admin
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2021 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.17 (January 23, 2024) : correction password
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'],  'protect.inc')) {
	header('location:../index.php');
	die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[9] == '' && $userprefs[1] != $serviz[176]) {
        $nomzone = $admin[12];
        include 'access.inc';
        exit();
    }
}
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d');
$nomficlog = CHEMIN.'data/log/debugprotect'.'_'.$dateheureminiutesecl.'.log';
if (isset($_GET['regit']))
// file_put_contents($nomficlog, ' Avant import Get protect regit: '.$_GET['regit']."\n", FILE_APPEND | LOCK_EX);
if (isset($_POST['regit']))
// file_put_contents($nomficlog, ' Avant import Get protect regit: '.$_POST['regit']."\n", FILE_APPEND | LOCK_EX);
$regit   = import('regit');
$upseudo = import('upseudo');
$uuid    = import('uuid');
$uuid2   = import('uuid2');
$unorm   = import('unorm');
$ufree   = import('ufree');
$uspec   = import('uspec');
$htspec  = import('htspec');

// file_put_contents($nomficlog, ' Apres import protect regit: '.$regit."\n", FILE_APPEND | LOCK_EX);

$erreur     = '';
$err_pseudo = empty($upseudo) && ($regit == 1 || $regit == 3 || $regit == 4) ? true: false;
$err_uid    = (empty($uuid) || !ctrlPasswordOK($uuid)) && ($regit == 1) ? true: false;
$err_uid2   = $uuid != $uuid2 && ($regit == 1) ? true: false;
$err_method = $unorm.$ufree.$uspec == '' && $regit == 1 ? true : false;
$erreur    .= $err_pseudo ? '<li>'.$admin[1588].'regit: '.$regit.'</li>': '';
$erreur    .= $err_uid ? '<li>'.$admin[1610].'</li>': '';
$erreur    .= $err_uid2 ? '<li>'.$admin[1611].'</li>': '';
$erreur    .= $err_method ? '<li>'.$admin[1736].'</li>': '';
$proot = dirname(__FILE__);
$proot = substr($proot, 0, strrpos($proot, '/admin/admin.php'));
@include $proot.'/data/config/testprotect.inc';
$upath = (@$testprotect == 'Success' ? true : false);
$path  = $upath ? $proot.'/data/config/.htpasswd' : '';
$mode  = '__FILE__';
if (empty($path)) {
    $proot = $_SERVER['SCRIPT_FILENAME'];
    $proot = substr($proot, 0, strrpos($proot, '/admin/admin.php'));
    @include $proot.'/data/config/testprotect.inc';
    $upath = $testprotect == 'Success' ? true : false;
    $path  = $upath ? $proot.'/data/config/.htpasswd' : '';
    $mode  = 'SCRIPT_FILENAME';
}
if (empty($path)) {
    $proot = $_SERVER['DOCUMENT_ROOT'];
    @include $proot.'/data/config/testprotect.inc';
    $upath = $testprotect == 'Success' ? true : false;
    $path  = $upath ? $proot.'/data/config/.htpasswd' : '';
    $mode  = 'DOCUMENT_ROOT';
}
if (empty($path)) {
    $proot = realpath(CHEMIN.'data/config/testprotect.inc');
    @include $proot;
    $upath = $testprotect == 'Success' ? true : false;
    $path  = realpath(CHEMIN.'data/config/.htpasswd');
    $mode  = 'realpath()';
}
if (empty($path)) $uspec == 'on';
$dateheureminiutesecl = $datjourl->format('Y_m_d');
$nomficlog = CHEMIN.'data/log/debugprotect'.'_'.$dateheureminiutesecl.'.log';
/* file_put_contents($nomficlog, ' protect regit: '.$regit."\n", FILE_APPEND | LOCK_EX);
file_put_contents($nomficlog, ' protect erreur: '.$erreur."\n", FILE_APPEND | LOCK_EX);
file_put_contents($nomficlog, ' protect path: '.$path."\n", FILE_APPEND | LOCK_EX);
*/
if ($regit == 1 && !empty($path) && empty($erreur)) { // création et ajout 1er admin
	$apachepassword  = Md5Crypt::apache ($uuid, $salt ='');	
    $htpass = '#:'.($unorm == 'on' ? '1-0-0' : ($ufree == 'on' ? '0-1-0' : '0-0-1')).chr(10).$upseudo.':'.($ufree == 'on' ? $uuid : $apachepassword);
    WriteFullDB(CHEMIN.'data/config/.htpasswd', $htpass);
    
    $htnormal = '
AuthName "'.$admin[1762].$site[0].'"
AuthType Basic
AuthUserFile '.$path.'
require valid-user
#method = '.$mode.'
    ';
    
    $htfree = '
AuthName "'.$admin[1762].$site[0].'"
PerlSetVar AuthFile /data/config/.htpasswd
AuthType Basic
require valid-user
#method = '.$mode.'
    ';
    
    $htaccess = $ufree == 'on' ? $htfree : ($uspec == 'on' ? $htspec : $htnormal);
    WriteFullDB(CHEMIN.'admin/.htaccess', $htaccess);
//    file_put_contents($nomficlog, ' protect write htaccess: '.$htaccess."\n", FILE_APPEND | LOCK_EX);
    $regit = 5;
//	file_put_contents($nomficlog, ' protect write htaccess renvoi vers: '.$regit."\n", FILE_APPEND | LOCK_EX);
}

if ($regit == 2 && empty($erreur)) { // suppression de la protection
    $htaccess = 'Options -Indexes';
    WriteFullDB(CHEMIN.'admin/.htaccess', $htaccess);
    @unlink(CHEMIN.'data/config/.htpasswd');
    $location = 'admin.php?lng='.$lng.'&pg=protect';
    header('location: '.$location);
    exit();
}

if ($regit == 3 && empty($erreur)) { // ajoute un collaborateur ou admin principal voir linuxmr
    $addht   = true;
    $ahtpass = @file(CHEMIN.'data/config/.htpasswd');
	if (count($ahtpass))
	{
		$ahts    = array();
		for ($i = 2; $i < count($ahtpass); $i++) {
			if ($ahtpass[$i] != '')
			{
				$aht = explode(':', $ahtpass[$i]);
				if ($upseudo != $aht[0] && !empty($ahtpass[$i])) $ahts[] = $ahtpass[$i];
			}
		}
		$apachepassword  = Md5Crypt::apache ($uuid, $salt ='');	
		$aht = explode(':', $ahtpass[1]);
		if ($aht[0] != $upseudo) $ahts[] = chr(10).$upseudo.':'.($ufree == 'on' ? $uuid : $apachepassword);
		$htpass = $ahtpass[0].$ahtpass[1];
		foreach ($ahts as $ht) $htpass .= $ht;
		WriteFullDB(CHEMIN.'data/config/.htpasswd', $htpass);
	}
}

if ($regit == 4 && empty($erreur)) { // suppression collaborateur
    DeleteMemberProtect($upseudo);
}
//file_put_contents($nomficlog, ' protect regit avant 5 regit: '.$regit."\n", FILE_APPEND | LOCK_EX);
//file_put_contents($nomficlog, ' protect erreur: '.$erreur."\n", FILE_APPEND | LOCK_EX);
if ($regit == 5 && empty($erreur)) { // ajout ensemble collaborateurs voir linuxmr
//file_put_contents($nomficlog, ' protect apres if 5 regit: '.$regit."\n", FILE_APPEND | LOCK_EX);
    $ahts     = array();
	$ahtpass  = array();
	if (is_file(CHEMIN.'data/config/.htpasswd'))
		$ahtpass  = @file(CHEMIN.'data/config/.htpasswd');
    $dbadmins = ReadDBFields(ADMINS);
    foreach ($dbadmins as $row) {
        if (is_file(REDACREP.$row[0].INCEXT)) {
            $addht = true;
            for ($i = 2; $i < count($ahtpass); $i++) {
                $aht = explode(':', $ahtpass[$i]);
                if ($row[0] == $aht[0]) {
                    $addht = false;
                    break;
                }
            }
			if ($addht) $ahts[] = $row[0];
        }
    }
	if (isset($ahtpass[0]))
		$ufree = substr($ahtpass[0], 0, 7) == '#:0-1-0' ? 'on' : '';
    foreach ($ahts as $ht) {
        include_once REDACREP.$ht.INCEXT;
        $userdb    = ReadDBFields(MSGREP.$ht.DBEXT);
		$motdepassclair = gy_decrypt(CRYPT_KEY, $userdb[0][0]);
		$apachepassword  = Md5Crypt::apache ($motdepassclair, $salt ='');
		if (!isset($drtuser[38])) $drtuser[38] = $apachepassword;
		$drtuser[38] = $apachepassword;
        $htpass    = $ht.':'.(isset($userdb[0][1]) ? ($ufree == 'on' ? gy_decrypt(CRYPT_KEY, $userdb[0][0]) : $apachepassword) : $drtuser[38]);
        $htpasswd  = ReadFullDB(CHEMIN.'data/config/.htpasswd');
/*
		file_put_contents($nomficlog, ' protect user nom: '.$ht.' drtuser[38]: '.$drtuser[38]."\n", FILE_APPEND | LOCK_EX);
		file_put_contents($nomficlog, ' protect userdb[0][1]: '.$apachepassword."\n", FILE_APPEND | LOCK_EX);
		file_put_contents($nomficlog, ' protect userdb[0][0]: '.$userdb[0][0]."\n", FILE_APPEND | LOCK_EX);
		file_put_contents($nomficlog, ' protect key: '.CRYPT_KEY."\n", FILE_APPEND | LOCK_EX);
*/
        $htpasswd .= chr(10).$htpass;
        WriteFullDB(CHEMIN.'data/config/.htpasswd', $htpasswd);
		$userdb[0][1] = $apachepassword; //ecriture user avec le mot de pass
		WriteDBFields(MSGREP.$ht.DBEXT, $userdb);
		REDACREP.$ht.INCEXT;
//		file_put_contents($nomficlog, ' ecr userdb: '.$ht."\n", FILE_APPEND | LOCK_EX);

    }

    $ahts    = array();
	$ahtpass  = array();
	if (is_file(CHEMIN.'data/config/.htpasswd'))
		$ahtpass  = @file(CHEMIN.'data/config/.htpasswd');
    foreach ($ahtpass as $ht) if (!empty($ht)) $ahts[] = $ht;
    $htpass = '';
    foreach ($ahts as $ht) $htpass .= $ht;
    WriteFullDB(CHEMIN.'data/config/.htpasswd', $htpass);

}

$headinc .= BeginJavascript().'
function VerifyForm() {
    var sto = "";
    var erreur = false;
    var pseudo = document.protect.upseudo.value;
	var uid = document.protect.uuid.value;
	var uid2 = document.protect.uuid2.value;
    regexp = /^[-a-zA-Z0-9_]{2,'.$serviz[148].'}$/;
    if (pseudo == "" || !regexp.test(document.protect.upseudo.value)) {
        sto += \'  - '.addslashes($admin[1588]).'\n\';
		document.protect.upseudo.className = "errorInputText";
        erreur = true;
    }
    if (uid != "") {
        regexp = /^\\S{8,40}$/;
        if (!regexp.test(uid)) {
            sto += \'  - '.addslashes($admin[1610]).'\n\';
			document.protect.uuid.className = "errorInputText";
            erreur = true;
        }
    }
	if (uid != uid2) {
		sto += \'  - '.addslashes($admin[1611]).' !\n\';
		document.protect.uuid.className = "errorInputText";
		document.protect.uuid2.className = "errorInputText";
		erreur = true;
	}
    if (erreur == true) {
        sto = "'.addslashes($admin[1636]).'\n\n" + sto;
        alert(sto);
		return false;
    }
}
function displayht0(form) {
    if (form.unorm.checked) {
        form.uspec.checked = "";
        form.ufree.checked = "";
    }
}
function displayht1(form) {
    if (form.ufree.checked) {
        form.unorm.checked = "";
        form.uspec.checked = "";
    }
}
function displayht2(form) {
    if (form.uspec.checked) {
        form.unorm.checked = "";
        form.ufree.checked = "";
        montre(\'trht\');
    } else {        
        cache(\'trht\');
        form.unorm.checked = true;
    }
}
'.EndJavascript();

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[1554]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[1554], '100%');

$htpasswd = @file(CHEMIN.'data/config/.htpasswd');
echo '
<p style="text-align:center;">'.ToHelp($lng, 'protect').$admin[1555].'</p>
<hr />';
if (!empty($erreur)) echo displayErrorMsg($admin[1715], $erreur);
echo '
<style type="text/css">
.td-input-group div.input-group-append { margin-left: -5px; }
input.texte { font-size: 13px; }
</style>
<script text="javascript">
  $(document).ready(function(){
    $("#testuuid").on("keyup", function(e) {
      var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\\\W).*$", "ig");
      var mediumRegex = new RegExp("^(?=.{8,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
      var okRegex = new RegExp("(?=.{8,}).*", "g");
      if (okRegex.test($(this).val()) === false) {
        $("#uuid-strength").removeClass().addClass("alert alert-error").html("'.$help[21].'");
      } else if (strongRegex.test($(this).val())) {
        $("#uuid-strength").removeClass().addClass("alert alert-success").html("'.$help[22].'");
      } else if (mediumRegex.test($(this).val())) {
        $("#uuid-strength").removeClass().addClass("alert alert-info").html("'.$help[23].'");
      } else {
        $("#uuid-strength").removeClass().addClass("alert alert-error").html("'.$help[24].'");
      }
      return true;
    });
  });
</script>
<div class="w-100 m-auto" style="max-width:580px;">
  <form name="protect" action="admin.php?lng='.$lng.'&amp;pg=protect" method="post" onsubmit="return VerifyForm(); return false;">';

$method = '';
if (!empty($htpasswd)) {
    $method = trim(substr($htpasswd[0], 2));
    switch ($method) {
    case '1-0-0' : 
        echo '
    <input type="hidden" name="unorm" value="on" />';
        break;
    case '0-1-0' : 
        echo '
    <input type="hidden" name="ufree" value="on" />';
        break;
    case '0-0-1' : 
        echo '
    <input type="hidden" name="uspec" value="on" />';
        break;
    }
}
if (empty($htpasswd))
{
	echo '
		<div class="alert alert-primary" role="alert">'
		.$admin[1838].'
		</div>';
     $upseudo = $userprefs[1];
echo '
    <div class="row quest">
      <div class="col-md-6 p-0 pl-1">
         '.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'
      </div>';
//	file_put_contents($nomficlog, ' 1- htppasvd pas vide  ou inexistant: '.$regit."\n", FILE_APPEND | LOCK_EX);
}
else
echo '
    <div class="row quest">
      <div class="col-md-6 p-0 pl-1">
		'.SubmitButton($admin[1411], 'self.location.href=\'admin.php?lng='.$lng.'&pg=protect&regit=2\'', $imggauche, '', 'button').'
      </div>';
if (file_exists(CHEMIN.'data/config/.htpasswd'))
{
	if ($method != '0-0-1') 
	{
//		file_put_contents($nomficlog, ' methode : '.$method.'regit : '.$regit."\n", FILE_APPEND | LOCK_EX);	
		echo '
		  <div class="col-md-6 p-0 pr-1">
			<div class="float-right">
			'.SubmitButton($admin[1749], 'self.location.href=\'admin.php?lng='.$lng.'&pg=protect&regit=5\'', '', $imgdroite, 'button').'
			</div>
		  </div>';
	}
}
echo '
      <div class="clearfix"></div>
	</div>
    <fieldset>
      <div class="w-90 m-auto">';
if (empty($htpasswd))
    echo '
        <div class="row">
          <div class="col-md-6 p-0 pt-3"><b>'.$admin[1013].'</b></div>
          <div class="col-md-6 p-0">
            <input type="text" class="'.($err_pseudo ? 'errorInputText' : 'texte').' form-control" name="upseudo" value="'.$upseudo.'" readonly="readonly" />
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 p-1" id="uuid-strength"></div>
        </div>
        <div class="row">
          <div class="col-md-6 p-0 pt-3"><b>'.$admin[1634].'</b></div>
          <div class="col-md-6 p-0 td-input-group">
            <input data-toggle="password" id="uuid" class="texte form-control" type="password" name="uuid" value="" data-message="" />
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 p-0 pt-3"><b>'.$admin[1645].'</b></div>
          <div class="col-md-6 p-0 td-input-group">
            <input data-toggle="password" id="uuid2" class="texte form-control" type="password" name="uuid2" value="" data-message="" />
          </div>
        </div>
        <div class="row"></div>
        <div class="row">
          <div class="col-md-6 px-0 py-1"><label for="unorm"><b>'.$admin[1735].'</b></label></div>
          <div class="col-md-6 px-0 py-1">
            <input type="checkbox" class="btn-check" id="unorm" name="unorm" checked onchange="displayht0(this.form); cache(\'trht\');" />
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 px-0 py-1"><label for="ufree"><b>'.$admin[1568].'</b></label></div>
          <div class="col-md-6 px-0 py-1">
            <input type="checkbox" id="ufree" name="ufree" onchange="displayht1(this.form); cache(\'trht\');" />
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 px-0 py-1"><label for="uspec"><b>'.$admin[1734].'</b></label></div>
          <div class="col-md-6 px-0 py-1">
            <input type="checkbox" id="uspec" name="uspec" onchange="displayht2(this.form);" />
          </div>
        </div>';
echo '
      </div>
      <div id="trht" class="w-98 m-auto" style="display:none;">
        <textarea name="htspec" rows="4" class="w-100"></textarea>
      </div>
    </fieldset>
    <div class="row quest">
	  <div class="col-md-6 p-0 pl-1">
        '.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'
	  </div>';
if (empty($htpasswd) || !(file_exists(CHEMIN.'data/config/.htpasswd')))
{
	echo '
      <div class="col-md-6 p-0 pr-1">
        <div class="float-right">
		<input type="hidden" name="regit" id="regit" value="1">
          '.SubmitButton($admin[885], 'self.location.href=\'admin.php?lng='.$lng.'&pg=protect\'', '', $imgdroite).'
        </div>
      </div>';
//	file_put_contents($nomficlog, ' 2- htpasswd vide ou inexistant: '.$regit."\n", FILE_APPEND | LOCK_EX);
}
echo '
	  <div class="clear:both"></div>
	</div>
  </form>';
if (!empty($htpasswd)) {
    echo '
    <fieldset style="margin-top: 24px; padding: 6px 24px; width: 60%; margin: auto;">';
    $countht = count($htpasswd);
    for ($i = 1; $i < $countht; $i++) {
        $aht = explode(':', $htpasswd[$i]);
        echo '
      <div style="line-height: 20px;">
        <b>'.$aht[0].'</b>';
        if($i > 1) echo '
        <span style="float:right;">
          <img src="'.CHEMIN.'admin/inc/img/files/sup.gif" alt="X" title="'.$admin[186].'"  style="cursor: pointer;" 
            onclick="self.location.href=\'admin.php?lng='.$lng.'&pg=protect&regit=4&upseudo='.$aht[0].'\'" />
        </span>';
        echo '
      </div><div style="clear: both;"></div>';
    }
    echo '
    </fieldset>';
//	file_put_contents($nomficlog, ' htppasvd pas ou existant: '.$regit."\n", FILE_APPEND | LOCK_EX);
}
    echo '
</div>
'.BeginJavascript().'document.newpwd.uuid.focus()'.EndJavascript();

btable();
include CHEMIN.'admin/bpage.inc' ;
?>