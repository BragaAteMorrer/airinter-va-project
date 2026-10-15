<?php
/*******************************************************************************
 *   Read Admin Sec_ban_logguppy
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
 * v6.00.24 (August 12, 2026) : Correction display fields in view table
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'sec_ban_logguppy.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[35] == '') {
        $nomzone = $admin[553];
        include 'access.inc';
        exit();
    }
}
	include CHEMIN.INCREP.'security.inc';
function SelectDBFieldsByIDSpam($Fields,$id) {
    $DataDB = array();
    foreach ($Fields as $row) {
        if ($row[6] == $id) {
            $DataDB[] = $row;
        }
    }
    return $DataDB;
}
function DeleteDBFieldsByIDSpam($amsglog,$id) {
	$datjourl = new DateTime('now');
	$dateheureminiutesecl = $datjourl->format('Y_m_d');
	$nomficlog = CHEMIN.'data/log/DeleteDBFieldsByIDSpam'.'_'.$dateheureminiutesecl.'.log';
	$DataDB = array();
	foreach ($amsglog as $row) {
        if ($row[6] != $id) {
            $DataDB[] = $row;
        }
    }
	return $DataDB;
}
function comparenomhost3asc($a, $b) {
   if ($a[3] == $b[3]) {
    return 0;
   }
   return ($a[3] < $b[3]) ? -1 : 1;
}
function CutField_log($field, $max = 15, $more = '...') {
	global $charset;
    $len = strlen($field);
    if ($len > $max) {
        return '<acronym title="'.htmlentities($field, ENT_COMPAT, $charset).'" class="cursorHelp">'.substr($field, 0, $max).$more.'</acronym>';
    } else {
        return $field;
    }
}
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d');
$nomficlog = CHEMIN.'data/log/sec_ban_logoguppy'.'_'.$dateheureminiutesecl.'.log';

// file_put_contents($nomficlog, date('Y-m-d H:i:s'). "\Debut sec_ban\n", FILE_APPEND | LOCK_EX);

$messageinfo = '';
$action = import('action');
$act = import('act');
if ($act != 1) $act = 0;
else
	$act = import('act');

$widepage = 'on';
$topmess  = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.$admin[1872];
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[1872], '100%');
echo '
<p style="text-align:center;">
  '.ToHelp($lng, '&pg=1018&mnuid=937&tconfig=0#z2').'<div class="mt-5 pt-2">'.$admin[557].' '.getIPadress($site[6] == 'on' ? (isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']): $_SERVER['REMOTE_ADDR']).'</div>
</p>';


if ($act == 1)
{
	if (isset($_POST["inputnoligne"]))
		$inputnoligne     = import('inputnoligne', 'POST');
	$urlpardefaut = 'error403.php';
	$errpardefaut = '403';
	$cfg = ReadSecurityConfig(BAN_CFG);
	if ('on' == $cfg['status'])
	{
		$def = ReadSecurityDatas(BAN_DEF);
		$xdef = array('id'     => $cfg['nextid'], 
				  'status' => 'on', 
				  'ip'     => '',
				  'pseudo' => '',
				  'email'  => '',				  
				  'host'   => '',
				  'path'   => '',
				  'agent'  => '',
				  'mots'   => '',
				  'msg'    => '',
				  'url'    => '');
		$listhost = array();
		$nomhostsupprime = '';
		$amsglog = ReadDBFields(DBLOGBOOKSPAM);
		$datjourl = new DateTime('now');
		$dateheureminiutesecl = $datjourl->format('Y_m_d');
		$nomficlog = CHEMIN.'data/log/Secbanlogguppy'.'_'.$dateheureminiutesecl.'.log';
		foreach($inputnoligne as $key => $value) 
		{
			$idlogip = $value;
			$rechdefselip = ''; // IP retour '' ou nom
			$rechdefselhost = ''; // Hote retour '' ou nom
			$rechdefselpath = ''; // $_SERVER['SCRIPT_NAME'] retour '' ou nom
			$rechdefselagent = ''; // $_SERVER['HTTP_USER_AGENT'] retour '' ou nom
			$rechdefselmot = '';  // $_SERVER['QUERY_STRING'] retour '' ou nom
			$selectamsglogbook = SelectDBFieldsByField($amsglog,9,$idlogip);
			if ($selectamsglogbook[0][1] != '')
				$rechdefselip = SelectBANReadSecurityDatas($selectamsglogbook[0][1], ''); // IP retour '' ou nom
			if ($rechdefselip == '' && $selectamsglogbook[0][3] != '') 
					$rechdefselhost = Select5BANReadSecurityDatas($selectamsglogbook[0][3]); // Hote retour '' ou nom
			if ($rechdefselhost == '' && $selectamsglogbook[0][7] != '')
				$rechdefselpath = Select6BANReadSecurityDatas($selectamsglogbook[0][7]); // $_SERVER['SCRIPT_NAME'] retour '' ou nom
			if ($rechdefselpath == '' && $selectamsglogbook[0][6] != '')
				$rechdefselagent = Select8BANReadSecurityDatas($selectamsglogbook[0][6]); // $_SERVER['HTTP_USER_AGENT'] retour '' ou nom
			if ($rechdefselmot == '' && $selectamsglogbook[0][8] != '')
				$rechdefselmot = Select7BANReadSecurityDatas($selectamsglogbook[0][8]);  // $_SERVER['QUERY_STRING'] retour '' ou nom
			
			if ($rechdefselip == '' )
			{
				if ($xdef['url'] == '')  $xdef['url'] = $urlpardefaut;
				if ($xdef['msg'] == '')  $xdef['msg'] = $errpardefaut;
				$host = mb_strtolower(@gethostbyaddr(getIPadress($_SERVER[$site[6] == 'on' ? 'HTTP_X_FORWARDED_FOR' : 'REMOTE_ADDR'])), $charset);
				$values = explode(",", $xdef['host']);
				$xdef['id'] = $cfg['nextid'];
				$xdef['ip'] = $selectamsglogbook[0][1];
				if ($selectamsglogbook[0][1] != $selectamsglogbook[0][3])
					$xdef['host'] = $selectamsglogbook[0][3];
				else
					$xdef['host'] = '';
				$xdef['path'] = '';
				$xdef['agent'] = '';
				$xdef['mots'] = '';
				$def[$xdef['id']] = $xdef;
				if ($cfg['nextid'] == $xdef['id']) {
					$cfg['nextid']++;
				}
			
				WriteSecurityConfig(BAN_CFG, $cfg);
				WriteSecurityDatas(BAN_DEF, $def);
				$amsglogbook = ReadDBFields(DBLOGBOOKSPAM);
				$amsglogbook = DeleteDBFieldsByIDSpam($amsglogbook, $idlogip);
				
				WriteDBFields(DBLOGBOOKSPAM, $amsglogbook);
			
				$act = 2;
				$messageinfo = $admin[1882];
				
				$nomhostsupprime = $selectamsglogbook[0][3];
				$amsglogbook = ReadDBFields(DBLOGBOOKSPAM);
				
			}
			else
			{
				$amsglogbook = ReadDBFields(DBLOGBOOKSPAM);
				$amsglogbook = DeleteDBFieldsByIDSpam($amsglogbook, $idlogip);
				$messageinfo = $admin[1881];
			}
		}
	}
	else
	{
		echo '
		<p style="text-align:center;"><i class="far fa-meh-rolling-eyes"></i><b> '.$admin[1880].'</b></p>';
	}


}
if ($act == 0)
{
	$amsglogbook = ReadDBFields(DBLOGBOOK);
	usort($amsglogbook, "comparenomhost3asc");
	if (empty($amsglogbook)) {
		$act = 4;
	}
	if ($act == 0)
	{
        SetChmod(DBLOGBOOKSPAM);		
		@unlink(DBLOGBOOKSPAM);
		SetChmod(DBCOUNTERLOGBK);		
		@unlink(DBCOUNTERLOGBK);
		$amsglog = array();
		$indligne = 0;
		$indenreng = 1;
		if (!file_exists(DBCOUNTERLOGBK))
		{
		   $compteur = 1;
		}
		else
		{
			$compteur = ReadCounter(DBCOUNTERLOGBK);
		}
		foreach($amsglogbook as $lignelogbook) 
		{
			if ($lignelogbook[5] != '')
			{
				if (!isset($lignelogbook[9])) $lignelogbook[9] = '';
				$lignelogbook[9] = $compteur;
				if (isset($lignelogbook[3]))
				{
					$lignelogbook[3] = htmlspecialchars($lignelogbook[3], ENT_QUOTES, $charset);
					$lignelogbook[1] = htmlspecialchars($lignelogbook[1], ENT_QUOTES, $charset);
				}
				$amsglog[] = $lignelogbook;
				$compteur++;
			}
			
// suppression des XSS dans logbook.dtb
// dans la partie no 5 de ce logbook
			$motsrecherches = array('alert');
			$values = array('alert');
//           $values = explode(',', $motsrecherches)
			$rejet = false;			
			for ($ijrech = 0; $ijrech < count($values); $ijrech++)
			{
				if (!$rejet)
				{	
					$rejet1 = substr_count($lignelogbook[5], $values[$ijrech]);	
					if ($rejet1 != 0) 
					{
						$rejet = true;
					}
				}
			}
			if (!$rejet) {
				$arraylogbook[]  = $lignelogbook;

		
			}
		}
		WriteDBFields(DBLOGBOOKSPAM, $amsglog);
		WriteCounter(DBCOUNTERLOGBK,$compteur);
		//	Ecriture du fichier pour revoir les blocages à faire
		sort($arraylogbook);
		WriteDBFields(DBLOGBOOK, $arraylogbook);
		$compteurlogspams = CountDBFields(DBLOGBOOKSPAM);
		 $act = 2;
		  echo '
<p style="text-align:center;"><i class="far fa-meh-rolling-eyes"></i><b>'.$admin[1868].'</b></p>';
	}
}
if ($act == 4) 
{
    echo '
<p style="text-align:center;"><i class="far fa-meh-rolling-eyes"></i><b>'.$admin[558].'</b></p>';
}

else {
	$amsglog = ReadDBFields(DBLOGBOOKSPAM);
	usort($amsglog, "comparenomhost3asc");
	$ijaffichage = 0;
	$nomhost = '';
	$iphostadresse = '';
	$nbrehostidentique = 0;
	$arraynbrevaluehost = array();
	$arraylogbook = array();
	$ij = 0;
	for ($ijaffichage = 0; $ijaffichage < count($amsglog); $ijaffichage++)
	{
		if (isset($amsglog[$ijaffichage][3]))
		{

			if ($amsglog[$ijaffichage][3] != '')
			{
				if ($iphostadresse == '')
				{	
					$iphostadresse = $amsglog[$ijaffichage][1];
					$nomhost       = $amsglog[$ijaffichage][3];
					
				}
				$rechdefip = '';
				$rechdefnamehost = '';
				$rechdefselpath = '';
				$rechdefselmots = '';
				$datjourl = new DateTime('now');
				$dateheureminiutesecl = $datjourl->format('Y_m_d');
				$nomficlog = CHEMIN.'data/log/Select6BANReadSecurityDatas'.'_'.$dateheureminiutesecl.'.log';				
				if ($amsglog[$ijaffichage][1] != '')
				{
					$rechdefip = SelectBANReadSecurityDatas($amsglog[$ijaffichage][1], '');	
				}
				
				if ($amsglog[$ijaffichage][3] != '')
				{
					$rechdefnamehost = Select5BANReadSecurityDatas($amsglog[$ijaffichage][3]); //	
				}
				if ($amsglog[$ijaffichage][7] != '')
				{
					$amsglogrechpath = rawurldecode(htmlspecialchars($amsglog[$ijaffichage][7]));
//					file_put_contents($nomficlog, 'Ip: '.$amsglog[$ijaffichage][1].' info 3: '.$amsglog[$ijaffichage][3].' rechdefselpath avant rech Phrase ou mot: '.$amsglogrechpath."\n", FILE_APPEND | LOCK_EX);
					$rechdefselpath = Select6BANReadSecurityDatas($amsglogrechpath); // rech path	
//					file_put_contents($nomficlog, 'Ip: '.$amsglog[$ijaffichage][1].' info 3: '.$amsglog[$ijaffichage][3].' rechdefselpath: '.$rechdefselpath. "\n", FILE_APPEND | LOCK_EX);
				}
				if ($amsglog[$ijaffichage][8] != '')
				{
					$amsglogrechmot = rawurldecode(htmlspecialchars($amsglog[$ijaffichage][8]));
					file_put_contents($nomficlog, 'info 3: '.$amsglog[$ijaffichage][3].' rechdefselmots avant rech mot: '.$amsglogrechmot."\n", FILE_APPEND | LOCK_EX);
					$rechdefselmots = Select7BANReadSecurityDatas($amsglogrechmot); // rech mots
					file_put_contents($nomficlog, 'info 3: '.$amsglog[$ijaffichage][3].' rechdefselmots: '.$rechdefselmots. "\n", FILE_APPEND | LOCK_EX);
					
				}
				if ($nomhost == $amsglog[$ijaffichage][3])
				{
					$nbrehostidentique++;
					$arraynbrevaluehost[$ij][0]  = $amsglog[$ijaffichage][0]; // Date
					$arraynbrevaluehost[$ij][1]	 = $amsglog[$ijaffichage][1]; // IP
					$arraynbrevaluehost[$ij][2]  = $amsglog[$ijaffichage][2]; // 
					$arraynbrevaluehost[$ij][3]  = $amsglog[$ijaffichage][3]; // Host
					$arraynbrevaluehost[$ij][4]  = $amsglog[$ijaffichage][4]; // Browser
					$arraynbrevaluehost[$ij][5]  = rawurldecode(htmlspecialchars($amsglog[$ijaffichage][5])); // Path
					$arraynbrevaluehost[$ij][6]  = $amsglog[$ijaffichage][6]; // Log2
					$arraynbrevaluehost[$ij][7]  = rawurldecode(htmlspecialchars($amsglog[$ijaffichage][7])); // $_SERVER['SCRIPT_NAME'];
					$arraynbrevaluehost[$ij][8]  = rawurldecode(htmlspecialchars($amsglog[$ijaffichage][8])); // $_SERVER['QUERY_STRING'];
					$arraynbrevaluehost[$ij][9]  = $amsglog[$ijaffichage][9]; // compteur
					$arraynbrevaluehost[$ij][10]  = $nbrehostidentique;   // nbre de Hosts
					$arraynbrevaluehost[$ij][11] = $rechdefnamehost;     
					$arraynbrevaluehost[$ij][12] = $rechdefselpath;
					$arraynbrevaluehost[$ij][13] = $rechdefselmots;
					$arraynbrevaluehost[$ij][14] = $rechdefip;
				}
				else
				{
					$nbrehostidentique = 1 ;
					$ij++;
					$arraynbrevaluehost[$ij][0]  = $amsglog[$ijaffichage][0];
					$arraynbrevaluehost[$ij][1]  = $amsglog[$ijaffichage][1];
					$arraynbrevaluehost[$ij][2]  = $amsglog[$ijaffichage][2];
					$arraynbrevaluehost[$ij][3]  = $amsglog[$ijaffichage][3];
					$arraynbrevaluehost[$ij][4]  = $amsglog[$ijaffichage][4];
					$arraynbrevaluehost[$ij][5]  = rawurldecode(htmlspecialchars($amsglog[$ijaffichage][5])); // Path
					$arraynbrevaluehost[$ij][6]  = $amsglog[$ijaffichage][6];
					$arraynbrevaluehost[$ij][7]  = rawurldecode(htmlspecialchars($amsglog[$ijaffichage][7])); // $_SERVER['SCRIPT_NAME'];
					$arraynbrevaluehost[$ij][8]  = rawurldecode(htmlspecialchars($amsglog[$ijaffichage][8])); // $_SERVER['QUERY_STRING'];
					if (!isset($amsglog[$ijaffichage][9])) $amsglog[$ijaffichage][9] = 1;
					$arraynbrevaluehost[$ij][9]  = $amsglog[$ijaffichage][9]; // compteur
					$arraynbrevaluehost[$ij][10] = $nbrehostidentique;   // nbre de Hosts
					$arraynbrevaluehost[$ij][11] = $rechdefnamehost;     
					$arraynbrevaluehost[$ij][12] = $rechdefselpath;
					$arraynbrevaluehost[$ij][13] = $rechdefselmots;
					$arraynbrevaluehost[$ij][14] = $rechdefip;
					$iphostadresse = $amsglog[$ijaffichage][1];
					$nomhost       = $amsglog[$ijaffichage][3];
				}
			}
		}
	}
//	Ecriture du fichier DBLOGBOOKSPAM pour revoir les blocages à faire
	sort($arraynbrevaluehost);
	WriteDBFields(DBLOGBOOKSPAM, $arraynbrevaluehost);

	$adressseipconnecte = getIPadress($site[6] == 'on' ? (isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']): $_SERVER['REMOTE_ADDR']);

if ($messageinfo != '') 
		echo '<p style="text-align:center;"><i class="far fa-meh-rolling-eyes"></i><b> '.$messageinfo.'</b></p>';
 echo '
   <form name="security" action="admin.php?lng='.$lng.'&amp;pg=sec_ban_logguppy" method="post">
       <input type="hidden" name="act" value="1" />
<fieldset >'.legend(sprintf($admin[1380], min($configlogNB, count($arraynbrevaluehost)))).'
  <div class="table-responsive">
  <table class="table table-hover table-bordered w-100">
    <tr lass="bg-primary" style="text-align:center;height:2.5em;">
	  <td class="align-middle font-weight-bold py-1 px-1 4px ">'.$admin[1877].'</td>
      <td class="align-middle font-weight-bold py-1 px-1 4px ">'.$admin[521].' </td>
      <td class="align-middle font-weight-bold py-1 px-1 4px ">'.$admin[518].' </td>
	  <td class="align-middle font-weight-bold py-1 px-1 4px">'.$admin[1879].'</td>
      <td class="align-middle font-weight-bold py-1 px-1 4px ">'.$admin[986].' </td>
	  <td class="align-middle font-weight-bold py-1 px-1 4px ">'.$admin[1883].'</td>
	  <td class="align-middle font-weight-bold py-1 px-1 4px ">'.$admin[1888].'</td>
	  <td class="align-middle font-weight-bold py-1 px-1 4px ">'.$admin[1884].'</td>
      <td class="align-middle font-weight-bold py-1 px-1 4px ">'.$admin[561].' </td>
	  <td class="align-middle font-weight-bold py-1 px-1 4px ">'.$admin[1885].'</td>
      <td class="align-middle font-weight-bold py-1 px-1 4px ">'.substr($admin[562], 0, 4).'...</td>
      <td class="align-middle font-weight-bold py-1 px-1 4px ">'.$admin[1886].'</td>
    </tr>';
    $nnav = array('netscape', 'msie', 'mozilla', 'da', 'opera', 'icab',
        'konqueror', 'robot', 'unknown', 'wget', 'avant browser', 'crazy',
        'maxthon', 'lynx', 'safari', 'seamonkey', 'psp', 'ps3', 'wii',
        'firefox', 'flock', 'iceweasel', 'chrome', 'camino', 'dillo',
        'element', 'epiphany', 'galeon', 'k-meleon', 'kazehakase', 'minifield'
        );
    $serviz[85] .= @$serviz[85][@strlen($serviz[85])] != '/' ? '/' : '';
    for ($i = count($arraynbrevaluehost)-1; $i >= 0; $i--) {
		if (isset($arraynbrevaluehost[$i][3])) {
			$imgflag = substr($arraynbrevaluehost[$i][3], 1 + strrpos($arraynbrevaluehost[$i][3], '.'));
			if (!CheckDB1Field(CHEMIN.'inc/lang/flags.inc', $imgflag, 0)) {
				$imgflag = 'unknown';
			}
		}
        else
            $imgflag = 'unknown';
        $xurl = str_replace('&', '&amp;', $arraynbrevaluehost[$i][2]);
        $xurl = empty($xurl) ? '&nbsp;' : '&nbsp;<a style="font-weight:normal;" href="'.$xurl.'" target="_blank" title="'.$xurl.'">'.CutField_log($xurl, 55).'</a>';
        $IP   = substr($arraynbrevaluehost[$i][1], 0, 18).(mb_strlen($arraynbrevaluehost[$i][1]) > 15 ? ' ...' : '');
		$IPsansubstr  = $arraynbrevaluehost[$i][1];
        echo '
    <tr '.( $i % 2 ? 'class="rep"' : 'class="quest"').'">
	  <td class="align-middle p-2" style="font-size:0.9em">';
	  	if (!isset($arraynbrevaluehost[$i][11]))
			$arraynbrevaluehost[$i][11] =  '';
		if (!isset($arraynbrevaluehost[$i][12]))
			$arraynbrevaluehost[$i][12] =  '';
		if (!isset($arraynbrevaluehost[$i][13]))
			$arraynbrevaluehost[$i][13] = '';
		if (!isset($arraynbrevaluehost[$i][14]))
			$arraynbrevaluehost[$i][14] = '';
		$cherchcaracteerror = substr_count($arraynbrevaluehost[$i][7], 'error');
		$cherchbotgoogle = substr_count($arraynbrevaluehost[$i][3], 'google-');
		$cherchbotgoogle2 = substr_count($arraynbrevaluehost[$i][3],'crawl-');
		$cherchbotgoogle = $cherchbotgoogle + $cherchbotgoogle2;
		if ($arraynbrevaluehost[$i][11] == '' && $arraynbrevaluehost[$i][12] == '' && $arraynbrevaluehost[$i][13] == '' && $arraynbrevaluehost[$i][14] == '' 
		&& $cherchbotgoogle == 0 && $cherchcaracteerror == 0 && $adressseipconnecte != $IPsansubstr)
		{	
			echo '<div class="inline-block">
				<input type="checkbox" class="btn-check" name="inputnoligne[]" value="'.$arraynbrevaluehost[$i][9].'" >'.' '.$arraynbrevaluehost[$i][9].'
			</div>';
				
		}
		else
		{
			echo '<div class="inline-block"> 
				<INPUT type="checkbox" class="btn-check" name="inputnoligne[]" disabled value="'.$arraynbrevaluehost[$i][9].'" >'.' '.$arraynbrevaluehost[$i][9].'
			</div>';
		}
		
		echo '
			</td>';
	echo '
			<script>
			$(document).ready(function(){
				$(\'[data-toggle="tooltip"]\').tooltip();
			});
			</script>';
				$cutchamps7 = (isset($arraynbrevaluehost[$i][7]) ? CutField_log($arraynbrevaluehost[$i][7], 10) : '');
				$champs7 = (isset($arraynbrevaluehost[$i][7]) ? $arraynbrevaluehost[$i][7] : '');
				$cutchamps8 = (isset($arraynbrevaluehost[$i][8]) ? CutField_log($arraynbrevaluehost[$i][8], 10) : '');
				$champs8 = (isset($arraynbrevaluehost[$i][8]) ? $arraynbrevaluehost[$i][8] : '');
				$cutchamps3 = (isset($arraynbrevaluehost[$i][3]) ? CutField_log($arraynbrevaluehost[$i][3], 10) : '');
				$champs3 = (isset($arraynbrevaluehost[$i][3]) ? $arraynbrevaluehost[$i][3] : '');
	echo '
			<td class="align-middle p-2 text-center" style="font-size:0.9em;">'.FormatDateSec($arraynbrevaluehost[$i][0]).'</td>
			<td class="align-middle p-2 text-center" style="font-size:0.9em;">
			<a href="'.$serviz[85].$arraynbrevaluehost[$i][1].'" title="'.$arraynbrevaluehost[$i][1].'" target="_blank">'.$IP.'</a>
			</td>
			<td class="bg-warning align-middle" style="font-size:0.9em;padding:0 2px;text-align:center;">';
				echo $arraynbrevaluehost[$i][14];
			echo '
			</td>
			<td class="align-middle p-2" style="font-size:0.9em;"><button type="button" class="btn" data-toggle="tooltip" 
			data-placement="left" data-toggle="tooltip" title="'.$champs7.'">'.$cutchamps7.'</button></td>';
		echo '<td class="bg-warning align-middle" style="font-size:0.9em;padding:0 2px;text-align:center;">';
				echo $arraynbrevaluehost[$i][12];
		echo '
			</td>	
			<td class="align-middle p-2" style="font-size:0.9em;"><button type="button" class="btn" data-toggle="tooltip" 
			data-placement="left" data-toggle="tooltip" title="'.$champs8.'">'.$cutchamps8.'</button></td>
			<td class="bg-warning align-middle" style="font-size:0.9em;padding:0 2px;text-align:center;">';
				echo $arraynbrevaluehost[$i][13];
		echo '
			</td>
			<td class="align-middle p-2" style="font-size:0.9em;"><button type="button" class="btn" data-toggle="tooltip" 
			data-placement="left" data-toggle="tooltip" title="'.$champs3.'">'.$cutchamps3.'</button></td>
			<td class="bg-warning align-middle" style="font-size:0.9em;padding:0 2px;text-align:center;">';
				echo $arraynbrevaluehost[$i][11];
		echo '
			</td>
			<td class="align-middle p-2 text-center" style="font-size:0.9em;">
				<i class="flg-'.$imgflag.'" title="'.$imgflag.'"></i>
			</td>
			<td class="bg-warning align-middle" style="font-size:0.9em;padding:0 2px;text-align:center;">';
			if (isset($arraynbrevaluehost[$i][10]))
				echo $arraynbrevaluehost[$i][10];
			else
				echo '';
			echo '
		  </td>
		</tr>';
    }
    echo '
  </table>
  </div>
</fieldset>
    <div class="form-group quest" style="padding:2px 20px;">
      <div style="float:right">'.SubmitButton($admin[1871], '', '', $imgdroite).'</div>
      <div style="clear:both"></div>
    </div>
</form>
<hr />';
}

btable();
include CHEMIN.'admin/bpage.inc';
?>
