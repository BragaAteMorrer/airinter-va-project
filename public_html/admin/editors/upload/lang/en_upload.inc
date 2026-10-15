<?php
/*******************************************************************************
 *   MinieditorTextarea réalisé par Djchouix - Licence CeCILL
 *   Web site = http://lebrikabrak.free.fr/
 *   e-mail   = lebrikabrak@free.fr
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2020 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = http://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V5 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.00 (December 15, 2020) : initial release
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "en_upload.inc")) {
  header("location:../index.php");
  die();
}

$lang_upload[1] = "Error in create because a directory with the same name already exists.<br /><br />Please change the directory's name.";
$lang_upload[2] = "Error because a file with the same name already exists.<br /><br />Please change the file's name.";
$lang_upload[3] = "Error because a directory with the same name already exists.<br /><br />Please change the directory's name.";
$lang_upload[4] = "Error in directory 's delete because the servor doesn't autorize this.<br /><br />Please delete the directory by FTP.";
$lang_upload[5] = "Error in upload because it \'s not a valid file (filename.ext)";
$lang_upload[6] = "Error in upload because a file with the same name already exists.<br /><br />Please change the file's name for uploading.";
$lang_upload[7] = "From here you manage the files and the directories on your site.<br /><i class='fas fa-search-location' style='font-size: 1.1rem;' title='Open'></i> : permit to open the directory<br /><i class='far fa-eye text-info align-middle' title='See'></i> : permit to see the file.<br /><i class='fas fa-edit align-middle' title='Rename'></i> : permit to rename the file or the directory.<br /><i class='fas fa-times text-danger align-middle' title='Delete'></i> : permit to delete the file or the directory.<br />";
$lang_upload[8] = "Create directory";
$lang_upload[9] = "Directory's name";
$lang_upload[10] = "Create";
$lang_upload[11] = "Click here to show help";  
$lang_upload[12] = "Click here to mask help";
$lang_upload[13] = "Upload a file";
$lang_upload[14] = "Directory";
$lang_upload[15] = "File";
$lang_upload[16] = "Size (octet)";
$lang_upload[17] = "Action";
$lang_upload[18] = "Upload on the site";
$lang_upload[19] = "To recuperate the URL, please click over his name.";
$lang_upload[20] = 'You must fill the field';
$lang_upload[21] = "You don't have permission to upload this type of file. Please contact website's administrator.";
$lang_upload[22] = "Preview";
$lang_upload[23] = "You are deleting the file";
$lang_upload[24] = "You are deleting the directory";
$lang_upload[25] = "width all files and subdirectories)";
$lang_upload[26] = 'Be carefull, deleting is irreversible.\n Please confirm this deleting if you really wan\'t to delete.';
$lang_upload[27] = "Please, write a new file\'s name";
$lang_upload[28] = "Write here the new file\'s name widthout the extension";
$lang_upload[29] = "Please, write a new name for this directory";
$lang_upload[30] = "Write here the new directory\'s name";
$lang_upload[31] = "Be carefull, you must use only letters and/or numbers and/or caracters (-_) without space in the name.";
$lang_upload[32] = "Open";
$lang_upload[33] = "Return";
$lang_upload[34] = "Parent Directory";
$lang_upload[35] = "See";
$lang_upload[36] = "Rename";
$lang_upload[37] = "Delete";
$lang_upload[38] = "Select";
?>
