<?php
/*******************************************************************************
 *   Help Language Strings
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

if (stristr($_SERVER['SCRIPT_NAME'], 'en-help.inc')) {
    header('location:../index.php');
    die();
}

$help[1] = 'The results of lower relevance are hidden. The value 0% normally displays all the results.';
$help[2] = 'Default : 10 minimum.';
$help[3] = '<b>IMPORTANT!</b> Do not put anything if you do not want to change your password. Otherwise, you must also enter the password control.';
$help[4] = 'Enter the value "0" to disable the control.';
$help[5] = 'Enter a value between \"1\" and \"30\", \"0\" for a permanent maintenance.';
$help[6] = 'To include an image in the forum signature, just type the code of this image in the desired location in the text of the signature.<br />Accepted formats : .gif .jpg .png 468x60 maxi.<br /><br />The code for the external image is prefixed with your user name "<b>fr_</b>". Example : <b>fr_MyUserName</b>.';
$help[7] = 'WARNING! Every action generates an email notification to the applicant.';
$help[8] = 'To use your avatar from another site, indicate its address.<br />Accepted formats : .gif .jpg .png 60x60 max.';
$help[9] = 'To synchronise email addresses in your account and newsletter.';
$help[10] = 'The ID is a unique identifier consisting of 2 to 8 characters without accents, no spaces.';
$help[11] = 'Indicate the number of options from the beginning, followed by the number of the option in the submenu, separated by dashes.<br />(i.e: 2-4-1 meaning comes from the second option from the main menu, the fourth sub-menu that is open and it is option 1 of the second sub-menu.)';
$help[12] = 'To affect any link to the menu, enter a <b>?</ b>.';
$help[13] = 'You can create subcategories by separating them with the | symbol.<br />Ex: Category|Level 1|Level 2';
$help[14] = 'Dates must be YYYYMMDDhhmn format (year month day hour minutes).<br />Blank if you do not want to use the programmed publication.<br />Can specify only a start date or only an end date.<br />If the start date is omitted, it will be the recording time of the event that will be taken.';
$help[15] = '(empty field to save)';
$help[16] = '<br /><br /><strong>The category is only used for the construction of the menu item, if necessary.</strong>';
$help[17] = 'WARNING! Check only if you use the member\'s area.<br />This option affects the search, so use if necessary.';
$help[18] = '<b>WARNING!</b> The size of the site may be very important. In this case back up into several pieces.';
$help[19] = 'Class <b>ZipArchive</b> required to run this script is not enabled.<br />Please perform the steps manually using FTP.';
$help[20] = 'GuppY is protected by the CeCILL free license.<br />Remove or modify the copyright notice, or hidden by whatever methods whatsoever, is strictly prohibited.';
$help[21] = 'Password must be 8 characters long.';
$help[22] = 'Good Password!';
$help[23] = 'Make your password stronger with more capital letters, more numbers and special characters!';
$help[24] = 'Weak Password, try using numbers, capital letters and special characters.';
$help[25] = 'So that a rotation repeats itself every year, YYYY can be replaced by ---- (4 hyphens), this will be replaced during treatment by the current year.<br />The hour and minutes on 2 digits each are optional, it will be replaced by 0000.';
$help[26] = 'By checking this box, a bcc with the webmaster\'s email address is added to the link to the recipient\'s email address for reply.<br /><br />It is useful to check when there is a choice between several recipients and you want the webmaster to always receive the answers.';
$help[27] = 'Registration of members up to 20 at a time. The required fields are username and email. If the password is not specified, it will be generated. <br />The credentials will be sent to the member and admin.';

$errormsg['400'] = array(
	'Bad Request',
	'The HTTP request could not be understood by the server due to malformed syntax.',
	'The web browser may be too recent, or the HTTP server may be too old.',
	'HTTP/1.0 400 Bad Request' );
$errormsg['401'] = array(
	'Unauthorized',
	'The request requires user authentication',
	'',
	'HTTP/1.0 401 Unauthorized' );
$errormsg['403'] = array(
	'Forbidden',
	'The HTTP server understood the request, but it is refusing to fulfill it.',
	'This status code is commonly used when the server does not wish to reveal exactly why the request has been refused, or when no other response is applicable (for example the server is an Intranet and only LAN machines are authorized to connect).',
	'HTTP/1.0 403 Forbidden' );
$errormsg['404'] = array(
	'Not found',
	'The server has not found anything matching the request address (URI).',
	'This means the URL you have typed or clicked on is wrong or obsolete and does not match any document existing on the server (you may try to gradualy remove the URL components from de right to the left to eventualy retrieve an existing path).',
	'HTTP/1.0 404 Not Found' );
$errormsg['500'] = array(
	'Internal server error',
	'The HTTP server encountered an unexpected condition which prevented it from fulfilling the request.',
	'For example this error can be caused by a server misconfiguration, or a resource exhausted or denied to the server on the host machine.',
	'HTTP/1.0 500 Internal server error' );

?>
