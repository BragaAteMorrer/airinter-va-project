<?php
/*******************************************************************************
 *   Upload réalisé par Djchouix - Licence CeCILL
 *   Web site = http://lebrikabrak.free.fr/
 *   e-mail   = lebrikabrak@free.fr
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
 * v6.00.00 (December 15, 2020) : initial release
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "style_upload.inc")) {
  header("location:../index.php");
  die();
}

?>
body {
 font-size: 16px;
 font-family: "Times New Roman" , Arial, sans-serif;
 color: <?php echo $colorTextCorp; ?>; 
 background-color:<?php echo $colorBodyUpload; ?>;
 margin:1px;
 padding-top:15px;
 padding-right:10px;
 padding-left:10px;
}

div.help {
 text-align:center;
 display:none;
 border:<?php echo $styleBordureTitre; ?>;
 background-color:<?php echo $colorFondFileUpload; ?>;
 color:<?php echo $texte[0]; ?>;
 width:auto;
 margin:15px;
 font-weight:normal;
}

div.corpsFileScroll {
 margin-right:auto;
 margin-left:auto;
 width:630px;
 height:291px;
 border:<?php echo $styleBordureCorp; ?>;
 overflow:auto;
}

div.corpsFilenoScroll {
 margin-right:auto;
 margin-left:auto;
 width:630px;
 height:291px;
 border:<?php echo $styleBordureCorp; ?>;
 color: <?php echo $colorTextCorp; ?>;
 background-color: <?php echo $colorFondFileUpload; ?>;
}

div.labelupload {
 text-align:center;
 font-weight:bold;
 vertical-align:middle;
}

div.upload {
 text-align:center;
 font-weight:bold;
 vertical-align:middle;
 margin-top:10px;  
 margin-bottom:10px;
}

input.bouton {
margin-left:10px;
font-size: 12px;
font-family: Arial, Helvetica, sans-serif;
}
 
a {
 color: <?php echo $colorFileUploadOff; ?>;
 background-color: transparent;
 text-decoration: none;
 cursor:pointer;
}

a:hover {
 color: <?php echo $colorFileUploadOn; ?>;
 text-decoration: underline;
 background-color: transparent;
 cursor:pointer;
}

/* style général du tableau */
table {
 font-size: 12px;
 font-family: Arial, Helvetica, sans-serif;
 font-weight:normal;
 color: <?php echo $colorTextCorp; ?>;
 background-color: <?php echo $colorFondFileUpload; ?>;
}

div.corpsFilenoScroll table {
 width:630px;
} 

div.corpsFileScroll table {
 width:100% !important;
 width:613px;
}

td {
 padding:5px;
 text-align:center;
}

/*  style des titres du tableau (ligne 1 du tableau)  */
tr.forum {
 background-color:<?php echo $colorFondTitre; ?>;
 color:<?php echo $colorTextTitre; ?>;
 text-align: center;
 font-weight: bold;
}
td#forumName {
 border:1px solid <?php echo $colorFondCorp; ?>;
}

td#forumSize {
 border-top:1px solid <?php echo $colorFondCorp; ?>;
 border-bottom:1px solid <?php echo $colorFondCorp; ?>;
}

td#forumAction {
 border:1px solid <?php echo $colorFondCorp; ?>;
}

/*  style des lignes et des cellules du corps du tableau   */
tr.quest {
}

tr.rep {
}

td.fileName {
 text-align:left;
 border-left:1px solid <?php echo $colorFondFileUpload; ?>;
 border-bottom:1px solid <?php echo $colorFondFileUpload; ?>;
}

td.fileSize {
 border-bottom:1px solid <?php echo $colorFondFileUpload; ?>;
}

td.fileAction {
 border-right:1px solid <?php echo $colorFondFileUpload; ?>;
 border-bottom:1px solid <?php echo $colorFondFileUpload; ?>;
}

/* style des images */
img {
 vertical-align:middle;
 margin-right:10px;
 margin-bottom:2px;
}
/* style des images dans le tableau */
img.imgName{
 vertical-align:middle;
 margin-right:10px;
 cursor:pointer;
}

a.imgName:hover{
 text-decoration:none;
}

img.imgAction {
vertical-align:middle;
cursor:pointer;
border:2px solid <?php echo $colorFondFileUpload; ?>;
margin-right:0px;
}

img.imgAction2 {
vertical-align:middle;
cursor:pointer;
margin-left:5px;
margin-right:5px;
border:2px solid <?php echo $colorFondFileUpload; ?>;
}

/*  style lors du survol de la souris sur l'élément */
tr#trsurvol {
 background-color: <?php echo $colorFondFileUploadOver; ?>;
 color: #000000;
}

tr#trsurvol img.imgAction {
 border:2px solid <?php echo $colorFondFileUploadOver; ?>;
 background-color: <?php echo $colorFondFileUploadOver; ?>;
 color: #000000;
}

tr#trsurvol img.imgAction2 {
 border:2px solid <?php echo $colorFondFileUploadOver; ?>;
 background-color: <?php echo $colorFondFileUploadOver; ?>;
 color: #000000;
}

tr#trsurvol img#imgsurvol {
 border:2px solid <?php echo $colorFileUploadOn; ?>;
 background-color: <?php echo $colorFondFileUploadOver; ?>;
 color: #000000;
}
