<?php
/*******************************************************************************''
 *   Admin Droits
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
 * v6.00.24 (August 12, 2026) : Modify script
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'gestredac.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[12] == '') {
        $nomzone = $admin[821];
        include 'access.inc';
        exit();
    }
}

$del    = import('del');
$act    = import('act');
$regit  = import('regit');
$group  = import('group');
$member = import('member');
if (!isset($droitaccegroupredac)) $droitaccegroupredac = '';
include_once CHEMIN.'inc/func_groupscol.php';

if (empty($act)) {
    $topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > <a href="admin.php?lng='.$lng.'&amp;pg=attribdroits">'.str_replace('Admin - ', '', $admin[820]).'</a> > '.str_replace('Admin - ', '', $admin[819]);
    include CHEMIN.'admin/hpage.inc';
    if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
    if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($admin[819], '100%');
    if (!empty($del)) {
		if (FileDBExist(REDACREP.$del.INCEXT)) 
		{
			include REDACREP.$del.INCEXT;
			if ($droitaccegroupredac == 'on')
			{					
				if ($drtuser[42] == "redac1" || $drtuser[42] == "redac2")
				{					
					if (FileDBExist(REDACREP.'redacaccessdossier/accessdossier'.$del.INCEXT)) 
					{
						include REDACREP.'redacaccessdossier/accessdossier'.$del.INCEXT;
						$accessRepUpload = explode(',',$drtaccessRepUpload);       //Nom des répertoires à la racine de Guppy accessibles pour le fichier Upload
						$accessRepUploadImage = explode(',',$drtaccessRepUploadImage);  //Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion d'Images.(accès avec variable uptype='Image')
						$accessRepUploadLink = explode(',',$drtaccessRepUploadLink);  //Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion de Liens (accès avec variable uptype='Link')
						$accessRepUploadMedia = explode(',',$drtaccessRepUploadMedia);
						for ($i =0; $i < count($accessRepUpload); $i++)
						{
							if (strpos($accessRepUpload[$i], $del))
							{
								$drtaccessRepUpload = $accessRepUpload[$i];
							}
								
						}
						$drtaccessRepUploadImage = '';
						for ($i =0; $i < count($accessRepUploadImage)-1; $i++)
						{
							if (strpos($accessRepUploadImage[$i], $del))
								$drtaccessRepUploadImage .= $accessRepUploadImage[$i];
						}
						$drtaccessRepUploadLink = '';
						for ($i =0; $i < count($accessRepUploadLink)-1; $i++)
						{
							if (strpos($accessRepUploadLink[$i], $del))
								$drtaccessRepUploadLink .= $accessRepUploadLink[$i];
						}
						$drtaccessRepUploadMedia = '';
						for ($i =0; $i < count($accessRepUploadMedia)-1; $i++)
						{
							if (strpos($accessRepUploadMedia[$i], $del))
								$drtaccessRepUploadMedia .= $accessRepUploadMedia[$i];
						}
// Copie des dossier conetant  les Images, les videos etc ...
						@recursiveCopy(CHEMIN.$drtaccessRepUploadMedia, CHEMIN.'redacgroupdocs/'.$drtaccessRepUploadMedia);
// Suppression du dossier du Redacteur
						@RecursiveDeleteDirComplet(CHEMIN.$drtaccessRepUploadMedia);					
// Suppression des dossiers					
						DestroyDBFile(REDACREP.'redacaccessdossier/accessdossier'.$del.INCEXT);
						$dbw    = SelectDBFieldsByNotStatus(ReadDBFields(DOCID),'d');
						for ($i = 0; $i < count($dbw); $i++) 
						{					
							$modif = 0;
							ReadDoc($dbw[$i][1]);	
							$temp   = explode('|', $author);
							$authorcontrol = $temp[0];
;							$id = $dbw[$i][1];
							if ($authorcontrol == $del) 
							{					
								if (strpos($fieldc1, $del)) {
									$contenu = stripslashes($fieldc1);
									$contenu = str_replace('redac_'.$del.'img', 'redacgroupdocs/redac_'.$del.'img', $contenu);
									$fieldc1 = $contenu;
									$modif = 1;	
								}	
								if (strpos($fieldc2, $del)) {
									$contenu = stripslashes($fieldc2);
									$contenu = str_replace('redac_'.$del.'img', 'redacgroupdocs/redac_'.$del.'img', $contenu);
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
        DestroyDBFile(REDACREP.$del.INCEXT);
        $dbadm = ReadDBFields(ADMINS);
        for ($k = 0; $k < count($dbadm); $k++) {
            if ($dbadm[$k][0] == $del) {
                unset($dbadm);
                DeleteDBFieldById(ADMINS, $id=$k);
                break;
            }
        }
        DestroyMemberCol($del);

    }
    echo '
<p class="text-center">'.ToHelp($lng, '&pg=984&mnuid=937&tconfig=0#z2').$admin[702].'</p>
<div class="m-auto text-center" style="max-width:480px;">
  <div class="row quest p-0 w-100 m-auto">
    <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
    <div class="col-md-6">
      <div class="float-right">
        '.SubmitButton($admin[820], 'self.location.href=\'admin.php?lng='.$lng.'&pg=attribdroits&etape=1\'', '', $imgdroite, 'button').'
      </div>
    </div>
  </div>
  <div class="mt-2 table-responsive-md">
  <table class="table table-striped table-bordered w-100 m-auto">
    <tr class="forum2">
      <td class="w-45"><b>'.$admin[827].'</b></td>
      <td class="w-35"><b>'.$admin[1612].'</b></td>
      <td class="w-20 text-center"><b>'.$admin[680].'</b></td>
    </tr>';
    $dbdrtredac = array();
    $file_drtredac = opendir(REDACREP);
    while ($nomfichier = readdir($file_drtredac)) {
        if( substr($nomfichier,-3) == 'inc' ) {
            $dbdrtredac[] = str_replace(substr($nomfichier,-4),"",$nomfichier);
        }
    }
    closedir($file_drtredac);
    @usort($dbdrtredac,'AsciiCompare');
    $fonctions = array('' => '???', 'redac1' => $admin[715].'1', 'redac2' => $admin[715].'2', 'modo' => $admin[712], 'webm' => $admin[718], 'admin' => $admin[1261]);
    for ($i = 0; $i < count($dbdrtredac); $i++) {
        if ($dbdrtredac[$i] != $userprefs[1]) {
            include REDACREP.$dbdrtredac[$i].INCEXT;
		if ($drtuser[42] == 'redac') $drtuser[42] = 'redac1';
            echo '
    <tr>
      <td class="align-middle text-left py-3">'.$dbdrtredac[$i].'</td>
      <td class="align-middle text-left py-3">'.$fonctions[$drtuser[42]].'</td>
      <td class="align-middle text-center py-3">
        <a href="admin.php?lng='.$lng.'&amp;pg=attribdroits&amp;etape=2&amp;namedroits='.$dbdrtredac[$i].'">
          <i class="far fa-edit" style="font-size: 1.2rem;" title="'.$admin[144].'"></i>
        </a>&nbsp;&nbsp;
        <a href="admin.php?lng='.$lng.'&amp;pg=gestredac&amp;del='.$dbdrtredac[$i].'">
          <i class="fas fa-times text-danger" style="font-size: 1.4rem;" title="'.$admin[186].'"></i>
        </a>
      </td>
    </tr>';
      }
    }
    echo '
  </table>
  </div>  
</div>';
    btable();
} 
################################################################################
// gestion des groupes de collaborateurs
elseif ($act == 2) {
    $mess = '';
    if ($regit == 1 && $group != '') { //ajouter groupe
        $group = preg_replace('! !', '_', $group);
        $mess = '<br />'.AddGroupCol($group) ? ucfirst($admin[928]).' '.$group.$admin[929].' !<br />' : ucfirst($admin[928]).' '.$group.$admin[931].' !<br />';
    } elseif ($regit == 2) { // supprimer groupe
        $mess = '<br />'.DelGroupCol($group) ? ucfirst($admin[928]).' '.$group.$admin[930].' !<br />' : ucfirst($admin[928]).' '.$group.' '.mb_strtolower($admin[1604], $charset).$admin[930].' !<br />';
    }
    $grps    = ReadGroupsCol();
	$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[1476]);
    include CHEMIN.'admin/hpage.inc';
    if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
    if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($admin[1476], '100%');
    echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=986&mnuid=937&tconfig=0#z2').$admin[1477].'</p>
<hr />'.$mess.'
<div class="w-100 m-auto" style="max-width:400px;">
  <form name="membgrp" action="admin.php?lng='.$lng.'&amp;pg=gestredac"  method="post">
    <fieldset style="padding-top:12px;">
      <div class="table-responsive-md">
	  <table class="table bord" style="text-align:center;width:300px;margin:auto;">
	    <tr class="forum">
		  <td style="width:66%;text-align:left;"><b>'.ucfirst($admin[928]).'</b></td>
		  <td colspan="2" style="text-align:center;"><b>'.$admin[680].'</b></td>
		</tr>';
    foreach ($grps as $grp=>$mbrs) {
    echo '
		<tr class="rep">
		  <td style="width:33%;text-align:left;padding:4px 0;">'.$grp.'</td>
		  <td style="text-align:center;padding:4px 0;">
		    <a href="admin.php?lng='.$lng.'&amp;pg=gestredac&amp;act=3&amp;group='.$grp.'" title="'.$admin[144].' '.$grp.'">
            <i class="far fa-edit" style="font-size: 1.2rem;" title="'.$admin[144].'"></i>
		  </td>
		  <td style="text-align:center;padding:4px 0;">
		    <a href="admin.php?lng='.$lng.'&amp;pg=gestredac&amp;act=2&amp;regit=2&amp;group='.$grp.'" title="'.$admin[186].' '.$grp.'">
			<i class="fas fa-times text-danger" style="font-size: 1.4rem;" title="'.$admin[186].'"></i>
		  </td>
		</tr>';
    }
    unset($grps);
    echo '
	  </table>
      </div>
      <br />';
    echo '
      <input type="hidden" name="regit" value="1" />
	  <input type="hidden" name="act" value="2" />
	  <p style="text-align:center">'.$admin[1590].' '.$admin[928].' : <input class="form-control mx-auto texte" type="text" name="group" value="" style="width:196px;" /></p>
	</fieldset>
    <div class="row quest p-0 mt-2 w-98 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div></div>
    </div>
  </form>
</div>';
    btable();
}
################################################################################
// gestion des collaborateurs du groupe
elseif ($act == 3) { 
	$aMembers = (array)import('aMembers', 'POST', FALSE);
    $mess = '';
    if ($regit == 'add' && $group != '' && count($aMembers) > 0) { 
		foreach ($aMembers as $member) {  // ajout membre
            if (!empty($member))
			{
                $mess .= AddMemberCol($group, $member) ? ucfirst($admin[928]).' '.$group.' : '.$admin[1478].' '.$member.$admin[929].' !<br />' : ucfirst($admin[1478]).' '.$member.$admin[931].' !<br />';
				if ($droitaccegroupredac == 'on')
				{
					if (FileDBExist(REDACREP.$member.INCEXT)) 
					{
						include REDACREP.$member.INCEXT;
						if ($drtuser[42] == "redac1" || $drtuser[42] == "redac2")
						{					
							if (FileDBExist(REDACREP.'redacaccessdossier/accessdossier'.$member.INCEXT)) 
							{
								include REDACREP.'redacaccessdossier/accessdossier'.$member.INCEXT;
								$accessRepUpload = explode(',',$drtaccessRepUpload);       //Nom des répertoires à la racine de Guppy accessibles pour le fichier Upload
								$accessRepUploadImage = explode(',',$drtaccessRepUploadImage);  //Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion d'Images.(accès avec variable uptype='Image')
								$accessRepUploadLink = explode(',',$drtaccessRepUploadLink);  //Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion de Liens (accès avec variable uptype='Link')
								$accessRepUploadMedia = explode(',',$drtaccessRepUploadMedia);

								$drtaccessRepUpload .= ',groupe'.$group.'img';

								$drtaccessRepUploadImage .= ',groupe'.$group.'img';

								$drtaccessRepUploadLink .= ',groupe'.$group.'img';

								$drtaccessRepUploadMedia .= ',groupe'.$group.'img';
								   
								if (!is_dir(CHEMIN.'groupe'.$group.'img')) // Creation dossier image acces en Upload Image, AUdio,Link et média
								{
									@mkdir(CHEMIN.'groupe'.$group.'img');
									SetChmod(CHEMIN.'groupe'.$group.'img');
									@copy(CHEMIN.'img/index.php', CHEMIN.'groupe'.$group.'img/index.php');	
									SetChmod(CHEMIN.'img/index.php', CHEMIN.'groupe'.$group.'img/index.php');
								}
		
$mettre    = "<?php
if (stristr(\$_SERVER['SCRIPT_NAME'], 'accessdossier".$member.".inc')) {
    header('location:../index.php');
    die();
}

\$drtaccessRepUpload = \"".$drtaccessRepUpload ."\"; //Nom des répertoires à la racine de Guppy accessibles pour le fichier Upload
\$drtaccessRepUploadImage = \"".$drtaccessRepUploadImage ."\"; //Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion d'Images.(accès avec variable uptype='Image')
\$drtaccessRepUploadLink = \"".$drtaccessRepUploadLink ."\"; //Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion de Liens (accès avec variable uptype='Link')
\$drtaccessRepUploadMedia = \"".$drtaccessRepUploadMedia."\"; //Accès aux répertoires à la racine de Guppy pour les fichiers Audio et Video (accès avec variable uptype='Media')";                                                        //Attention: vous devez choisir les répertoires uniquement parmi la liste des répertoires contenus dans la variable $accessRepUpload ci-dessus
$mettre .='
?>';
								if (!is_dir(REDACREP.'redacaccessdossier')) // Generation des access utilisés en upload.php
								{
									@mkdir(REDACREP.'redacaccessdossier');
									SetChmod(REDACREP.'redacaccessdossier');
									@copy(REDACREP.'index.php', REDACREP.'redacaccessdossier/index.php');
									SetChmod(REDACREP.'redacaccessdossier/index.php');
								}
								if (!is_dir(CHEMIN.'redac_'.$member.'img')) // Creation dossier acces en Upload Image, AUdio,Link et média
								{
									@mkdir(CHEMIN.'redac_'.$member.'img');
									SetChmod(CHEMIN.'redac_'.$member.'img');
									@copy(CHEMIN.'img/index.php', CHEMIN.'redac_'.$member.'img/index.php');	
									SetChmod(CHEMIN.'redac_'.$member.'img/index.php');
								}
								if (!is_dir(CHEMIN.'redacgroupdocs')) // Creation dossier Redacgroupdocs pour tous les rédacteurs
								{
									@mkdir(CHEMIN.'redacgroupdocs');
									SetChmod(CHEMIN.'redacgroupdocs');
									@copy(CHEMIN.'img/index.php', CHEMIN.'redacgroupdocs/index.php');	
									SetChmod(CHEMIN.'redacgroupdocs/index.php');
								}
								$filedroitdossierredac = REDACREP.'redacaccessdossier/accessdossier'.$member.INCEXT;
								WriteFullDB($filedroitdossierredac, $mettre);
							} // fin acces dossiers pour redac			
			
						}
					} // fin d'existence du dossier droits acces membre
				}
			}
		} // fin ajout
									
    } 
	elseif ($regit == 'del' && $group != '' && $member != '') 
	{ // suppression membre du groupe
        $mess = DelMemberCol($group, $member) ? ucfirst($admin[928]).' '.$group.' : '.$admin[1693].' '.$member.$admin[930].' !<br />' : ucfirst($admin[1478]).' '.$member.' '.mb_strtolower($admin[1604], $charset).$admin[930].' !<br />';
		if (FileDBExist(REDACREP.$member.INCEXT)) 
		{
			include REDACREP.$member.INCEXT;
			if ($droitaccegroupredac == 'on')
			{					
				if ($drtuser[42] == "redac1" || $drtuser[42] == "redac2")
				{					
					if (FileDBExist(REDACREP.'redacaccessdossier/accessdossier'.$member.INCEXT)) 
					{
						include REDACREP.'redacaccessdossier/accessdossier'.$member.INCEXT;
						$accessRepUpload = explode(',',$drtaccessRepUpload);       //Nom des répertoires à la racine de Guppy accessibles pour le fichier Upload
						$accessRepUploadImage = explode(',',$drtaccessRepUploadImage);  //Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion d'Images.(accès avec variable uptype='Image')
						$accessRepUploadLink = explode(',',$drtaccessRepUploadLink);  //Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion de Liens (accès avec variable uptype='Link')
						$accessRepUploadMedia = explode(',',$drtaccessRepUploadMedia);
						$drtaccessRepUpload = '';
						for ($i =0; $i < count($accessRepUpload); $i++)
						{
							if (trim($accessRepUpload[$i]) != trim('groupe'.$group.'img'))
							{
								if ($i > 0) $drtaccessRepUpload.= ',';
								$drtaccessRepUpload.= $accessRepUpload[$i];
							}
								
						}
						$drtaccessRepUploadImage = '';
						for ($i =0; $i < count($accessRepUploadImage)-1; $i++)
						{
							if (trim($accessRepUploadImage[$i]) != trim('groupe'.$group.'img'))
								if ($i > 0) $drtaccessRepUploadImage.= ',';
								$drtaccessRepUploadImage .= $accessRepUploadImage[$i];
						}
						$drtaccessRepUploadLink = '';
						for ($i =0; $i < count($accessRepUploadLink)-1; $i++)
						{
							if (trim($accessRepUploadLink[$i]) != trim('groupe'.$group.'img'))
								if ($i > 0) $drtaccessRepUploadLink.= ',';
								$drtaccessRepUploadLink .= $accessRepUploadLink[$i];
						}
						$drtaccessRepUploadMedia = '';
						for ($i =0; $i < count($accessRepUploadMedia)-1; $i++)
						{
							if (trim($accessRepUploadMedia[$i]) != trim('groupe'.$group.'img'))
								if ($i > 0) $drtaccessRepUploadMedia.= ',';
								$drtaccessRepUploadMedia .= $accessRepUploadMedia[$i];
						}							
		
$mettre    = "<?php
if (stristr(\$_SERVER['SCRIPT_NAME'], 'accessdossier".$member.".inc')) {
    header('location:../index.php');
    die();
}

\$drtaccessRepUpload = \"".$drtaccessRepUpload ."\"; //Nom des répertoires à la racine de Guppy accessibles pour le fichier Upload
\$drtaccessRepUploadImage = \"".$drtaccessRepUploadImage ."\"; //Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion d'Images.(accès avec variable uptype='Image')
\$drtaccessRepUploadLink = \"".$drtaccessRepUploadLink ."\"; //Nom des répertoires à la racine de Guppy accessibles via le menu pour l'insertion de Liens (accès avec variable uptype='Link')
\$drtaccessRepUploadMedia = \"".$drtaccessRepUploadMedia."\"; //Accès aux répertoires à la racine de Guppy pour les fichiers Audio et Video (accès avec variable uptype='Media')";                                                        //Attention: vous devez choisir les répertoires uniquement parmi la liste des répertoires contenus dans la variable $accessRepUpload ci-dessus
$mettre .='
?>';
						if (!is_dir(CHEMIN.'admin/redac/redacaccessdossier')) // Generation des access utilisés en upload.php
						{
							@mkdir(CHEMIN.'admin/redac/redacaccessdossier');
							SetChmod(CHEMIN.'admin/redac/redacaccessdossier');
							@copy(CHEMIN.'admin/redac/index.php', CHEMIN.'admin/redac/redacaccessdossier/index.php');
							SetChmod(CHEMIN.'admin/redac/redacaccessdossier/index.php');
						}
						if (!is_dir(CHEMIN.'redac_'.$member.'img')) // Creation dossier acces en Upload Image, AUdio,Link et média
						{
							@mkdir(CHEMIN.'redac_'.$member.'img');
							SetChmod(CHEMIN.'redac_'.$member.'img');
							@copy(CHEMIN.'img/index.php', CHEMIN.'redac_'.$member.'img/index.php');	
							SetChmod(CHEMIN.'redac_'.$member.'img/index.php');
						}
						if (!is_dir(CHEMIN.'redacgroupdocs')) // Creation dossier Redacgrupdocs pour tous les rédacteurs
						{
							@mkdir(CHEMIN.'redacgroupdocs');
							SetChmod(CHEMIN.'redacgroupdocs');
							@copy(CHEMIN.'img/index.php', CHEMIN.'redacgroupdocs/index.php');	
							SetChmod(CHEMIN.'redacgroupdocs/index.php');
						}
						$filedroitdossierredac = REDACREP.'redacaccessdossier/accessdossier'.$member.INCEXT;
						WriteFullDB($filedroitdossierredac, $mettre);
					} // fin acces dossiers pour redac			
		
				}
			} // fin d'existence du dossier droits acces membre   
		}
	} // fin de suppression du membre
	$mess    = $mess == '' ? '' : '<br />'.$mess;
    $grps    = ReadGroupsCol();
	$lst     = $grps[$group];
	$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > <a href="admin.php?lng='.$lng.'&amp;pg=gestredac&amp;act=2">'.$admin[1476].'</a> > '.ucfirst($admin[928]).' '.$group;
    include CHEMIN.'admin/hpage.inc';
    if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
    if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable(ucfirst($admin[928]).' '.$group, '100%');
    echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=986&mnuid=937&tconfig=0#z2').$admin[1479].'</p>
<hr />'.$mess;
    if (FileDBExist(GROUPSCOL)) {
        echo '
<div class="w-100 m-auto" style="max-width:400px;">
  <div class="row quest p-0 mt-2 w-100 m-auto">
    <div class="col-md-12">'.SubmitButton($admin[1476], 'self.location.href=\'admin.php?lng='.$lng.'&pg=gestredac&act=2\'', $imggauche, '', 'button').'</div>
  </div>
  <p class="forum" style="text-align:left; padding:5px 20px;">'.$group.'</p>
  <form name="memberscol" action="admin.php?lng='.$lng.'&amp;pg=gestredac"  method="post">
    <fieldset style="padding-top:12px;">
      <div class="table-responsive-md">
	  <table class="table bord" style="text-align:center;margin:auto;width:300px;">
		<tr class="forum">
		  <td style="width:66%;text-align:left;"><b>'.ucfirst($admin[1692]).'</b></td>
		  <td style="text-align:center;"><b>'.$admin[186].'</b></td>
		</tr>';
        foreach ($lst as $mbr) {
			if (!empty($mbr))  // voir Rosier
				echo '
		<tr class="rep">
		  <td style="width:33%;text-align:left;padding:4px 0;">'.$mbr.'</td>
		  <td style="text-align:center;padding:4px 0;">
		    <a href="admin.php?lng='.$lng.'&amp;pg=gestredac&amp;act=3&amp;regit=del&amp;group='.$group.'&amp;member='.$mbr.'" title="'.$admin[186].' '.$mbr.' de '.$group.'">
              <i class="fas fa-times text-danger" style="font-size: 1.4rem;" title="'.$admin[186].'"></i>
            </a>
		  </td>
		</tr>
            ';
        }
        unset($grps);
        echo '
	  </table>
      </div>
      <br />';
        echo '
	  <input type="hidden" name="regit" value="add" />
	  <input type="hidden" name="act" value="3" />
	  <input type="hidden" name="group" value="'.$group.'" />
	  <p style="text-align:center">'.$admin[1590].' '.$admin[1478].' :</p>
	  <p style="text-align:center">';
        $dbuserspref = array();
        $file_users=opendir(REDACREP);
        while ($nomfichier=readdir($file_users)) {
			if ( substr($nomfichier,-3) == "inc") {
				$nomuser = str_replace(substr($nomfichier,-4),"",$nomfichier);
				if (!in_array($nomuser, $lst)) $dbuserspref[] = $nomuser;
			}
        }
        closedir($file_users);
        @usort($dbuserspref,'AsciiCompare');
		$size = min(20, count($dbuserspref) + 2);
        echo '
		<select class="form-control mx-auto" name="aMembers[]" id="aMembers" size="'.$size.'" multiple="multiple" style="width:160px;">';
        for ($i = 0; $i < count($dbuserspref); $i++) {
			echo '
		  <option value="'.$dbuserspref[$i].'">'.$dbuserspref[$i].'</option>';
        }
        echo '
		</select><br />
	  </p>
	</fieldset>
    <div class="row quest p-0 mt-2 w-98 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div></div>
    </div>
  </form>
</div>';
    }
    btable();
}

include CHEMIN.'admin/bpage.inc';
?>