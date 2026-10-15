<?php
/*******************************************************************************
 *   Admin Search
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
 * v6.00.19 (August 26, 2024) : Search config modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'search.inc')) {
	header('location:../index.php');
	die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[49] == '') {
        $nomzone = $admin[1264];
        include 'access.inc';
        exit();
    }
}

$regit = import('regit');
if (!isset($rech_article))	  $rech_article	 = 'ar';   // recherche sur article
if (!isset($rech_download))	  $rech_download = 'dn'; // recherche sur telechargements
if (!isset($rech_faq))		  $rech_faq 	 = 'fa';            // recherche sur faq
if (!isset($rech_forum ))	  $rech_forum 	 = 'fr';       // recherche sur forum
if (!isset($rech_livreor))	  $rech_livreor  = 'gb';   // recherche sur livre d'or
if (!isset($rech_liens))	  $rech_liens    = 'li';       // recherche sur liens
if (!isset($rech_news)) 	  $rech_news     = 'ne';         // recherche sur nouvelles
if (!isset($rech_blog)) 	  $rech_blog     = 'bl';         // recherche sur blog
if (!isset($rech_photo))	  $rech_photo    = 'ph';       // recherche sur photos
if (!isset($rech_agenda))     $rech_agenda   = 'ag';     // recherche sur agenda
if (!isset($rech_archive))    $rech_archive  = 'arch';   // recherche sur archives

if (is_file(CONFIGREP.'searchconfig.inc')) {
	include CONFIGREP.'searchconfig.inc';
} else {
	$pertinencemin   = 35;   // Affiche les résultats de pertinence supérieure à cette valeur
	$resultbypage    = 10;   // Nombre de résultats par page
	$barcolor        = 'vert';   // Couleur de la barre de pertinence
	$bargrey         = '3';   // Fond de la barre de pertinence
	$styleocc[0]     = '';   // Couleur de la police
	$styleocc[1]     = '';   // Italique
	$styleocc[2]     = 'bold';   // Caractères gras
	$styleocc[3]     = '';   // Souligné
	$styleocc[4]     = 'on';   // "on"/"" Surligné en couleur
	$bgcolor[0]      = '#ECBDA2';   // Couleur de l'occurence n° 1
	$bgcolor[1]      = '#B6F3A2';   // Couleur de l'occurence n° 2
	$bgcolor[2]      = '#ECF3A2';   // Couleur de l'occurence n° 3
	$bgcolor[3]      = '#D1BDF3';   // Couleur de l'occurence n° 4
	$bgcolor[4]      = '#ECD887';   // Couleur de l'occurence n° 5
	$bgcolor[5]      = '#ECBDF3';   // Couleur de l'occurence n° 6
	$bgcolor[6]      = '#D1F36C';   // Couleur de l'occurence n° 7
	$bgcolor[7]      = '#D1D8F3';   // Couleur de l'occurence n° 8
	
	$exclure0  = 'a, b, c, d, e, f, g, h, i, j, k, l, m, n, o, p, q, r, s, t, u, v, w, x, y, z, un, une, d\', de, de la, du, des, l\', le, la, les, à, au, et, et un, et une, et de, et du, et des, et le, et la, et les, et à, et au, sur, sur le, sur la, dans, dans le, dans la, en, ce, cette, mon, ma, mes, ton, ta, tes, son, sa, ses, je, tu, il, on';
	$exclure1  = 'a, b, c, d, e, f, g, h, i, j, k, l, m, n, o, p, q, r, s, t, u, v, w, x, y, z, an, the, on, in, of';
    $hideprint = '';
    $confpage  = 'ALL';
}

if ($regit == 1) { 

    $rech_article = import("rech_article"); 
    $rech_download = import("rech_download");
    $rech_faq= import("rech_faq"); 
    $rech_forum = import("rech_forum"); 
    $rech_livreor = import("rech_livreor");
    $rech_liens = import("rech_liens"); 
    $rech_news= import("rech_news"); 
    $rech_blog= import("rech_blog"); 
    $rech_photo= import("rech_photo"); 
    $rech_agenda = import("rech_agenda"); 
	$rech_archive = import("rech_archive"); 
	$pertmin       = intval(import('pertmin'));
	$pertmin       = ($pertmin < 0)   ? 0   : $pertmin;
	$pertmin       = ($pertmin > 100) ? 100 : $pertmin;
	$pertinencemin = $pertmin;
	$nbrslt        = intval(import('nbrslt'));
	$nbrslt        = ($nbrslt < 10) ? 10 : $nbrslt;
	$resultbypage  = $nbrslt;
	$barcolor      = import('barcol');
	$bargrey       = import('bargr');
	$styleocc[0]   = import('police');
	$styleocc[1]   = import('italic');
	$styleocc[2]   = import('bold');
	$styleocc[3]   = import('underline');
	$styleocc[4]   = import('hightlighted');
	$bgcolor[0]    = import('bgcolor0', 'POST', true, '#FF9999');
	$bgcolor[1]    = import('bgcolor1', 'POST', true, '#CCFF66');
	$bgcolor[2]    = import('bgcolor2', 'POST', true, '#CC99FF');
	$bgcolor[3]    = import('bgcolor3', 'POST', true, '#FFCC66');
	$bgcolor[4]    = import('bgcolor4', 'POST', true, '#CCCCFF');
	$bgcolor[5]    = import('bgcolor5', 'POST', true, '#FFCCCC');
	$bgcolor[6]    = import('bgcolor6', 'POST', true, '#FFFF99');
	$bgcolor[7]    = import('bgcolor7', 'POST', true, '#CCFFFF');
	
	$exclure0     = import('excl0');
	$exclure1     = import('excl1');
	$hideprint    = import('hideprint');
	$confpage     = import('confpage', 'POST', true, '0');
	
	$content = '<?php
/*******************************************************************************
 *   Config Search
 *******************************************************************************
 *   GuppY PHP Script - version 5.0
 *   CeCILL Copyright (C) 2004-2014 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = http://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V5 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.00 (December 15, 2019) : initial release
 ******************************************************************************/

if (stristr($_SERVER[\'SCRIPT_NAME\'], \'searchconfig.inc\')) {
	header(\'location:../index.php\');
	die();
}

$pertinencemin = '.$pertinencemin.';   // Affiche les résultats de pertinence supérieure à cette valeur
$resultbypage  = '.$resultbypage.';   // Nombre de résultats par page
$barcolor      = stripslashes("'.$barcolor.'");   // Couleur de la barre de pertinence
$bargrey       = stripslashes("'.$bargrey.'");   // Fond de la barre de pertinence
$styleocc[0]   = stripslashes("'.$styleocc[0].'");   // Couleur de la police
$styleocc[1]   = stripslashes("'.$styleocc[1].'");   // Italique
$styleocc[2]   = stripslashes("'.$styleocc[2].'");   // Caractères gras
$styleocc[3]   = stripslashes("'.$styleocc[3].'");   // Souligné
$styleocc[4]   = stripslashes("'.$styleocc[4].'");   // "on"/"" Surligné en couleur
$bgcolor[0]    = stripslashes("'.$bgcolor[0].'");   // Couleur de l\'occurence n° 1
$bgcolor[1]    = stripslashes("'.$bgcolor[1].'");   // Couleur de l\'occurence n° 2
$bgcolor[2]    = stripslashes("'.$bgcolor[2].'");   // Couleur de l\'occurence n° 3
$bgcolor[3]    = stripslashes("'.$bgcolor[3].'");   // Couleur de l\'occurence n° 4
$bgcolor[4]    = stripslashes("'.$bgcolor[4].'");   // Couleur de l\'occurence n° 5
$bgcolor[5]    = stripslashes("'.$bgcolor[5].'");   // Couleur de l\'occurence n° 6
$bgcolor[6]    = stripslashes("'.$bgcolor[6].'");   // Couleur de l\'occurence n° 7
$bgcolor[7]    = stripslashes("'.$bgcolor[7].'");   // Couleur de l\'occurence n° 8

$exclure0      = stripslashes("'.$exclure0.'");
$exclure1      = stripslashes("'.$exclure1.'");
$hideprint     = stripslashes("'.$hideprint.'");
$confpage      = stripslashes("'.$confpage.'");
$rech_article = stripslashes("'.$rech_article.'");   // recherche sur article
$rech_download = stripslashes("'.$rech_download.'"); // recherche sur telechargements
$rech_faq= stripslashes("'.$rech_faq.'");            // recherche sur faq
$rech_forum = stripslashes("'.$rech_forum.'");       // recherche sur forum
$rech_livreor = stripslashes("'.$rech_livreor.'");   // recherche sur livre d\'or
$rech_liens = stripslashes("'.$rech_liens.'");       // recherche sur liens
$rech_news = stripslashes("'.$rech_news.'");         // recherche sur nouvelles
$rech_blog = stripslashes("'.$rech_blog.'");         // recherche sur blog
$rech_photo = stripslashes("'.$rech_photo.'");       // recherche sur photos
$rech_agenda = stripslashes("'.$rech_agenda.'");     // recherche sur agenda
$rech_archive = stripslashes("'.$rech_archive.'");   // recherche sur archives
?>';

	WriteFullDB(CONFIGREP.'searchconfig.inc', $content);
    
    include CONFIG;
    $serviz[24]  = import('serviz24'); // Boîte recherche ON / OFF
    $members[23] = import('members23'); // Zone membre accès boîtes de recherche
    include 'inc/confupdt.inc';

    if ($site[7] == 'on' || PHP700) {
        @ob_flush();
        sleep(PHP700 ? 3 : $site[8]);
        echo '<script>window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=search";</script>';
    }
}

$headinc .= '
<link rel="stylesheet" media="screen" href="../inc/colorpicker/css/colorpicker.css" /> 
<script src="../inc/colorpicker/js/colorpicker.js"></script>
';

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[1275]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[1275], "100%");    

function GetSelector($inputname, $color) { // Palette de couleurs
	return '
			<div>
			  <input class="form-control" id="'.$inputname.'" name="'.$inputname.'" type="text" size="6" value="'.$color.'" 
                style="font:12px Arial;text-align:center;width:76px;border:1px solid #000;border-radius:4px 0 0 4px;float:left;" />
			  <div style="float:left;width:40px;height:30px;vertical-align:top;border:1px solid #000;border-left:none;background-color:'.$color.';padding:-4px 0 4px 0;"></div>
			  <div id="'.$inputname.'div" style="float:left;width:40px;height:30px;border:1px solid #000;border-left:none;background-color:'.$color.';border-radius:0 4px 4px 0;">&nbsp;</div>
			</div>'.
			BeginJavascript()."
			$('#".$inputname."').ColorPicker({
				onSubmit: function(hsb, hex, rgb, el) {
					$(el).val('#' + hex.toUpperCase());
					$(el).ColorPickerHide();
				},
				onChange: function (hsb, hex, rgb) {
					$('#".$inputname."div').css('backgroundColor', '#' + hex);
				},
				onBeforeShow: function () {
					$(this).ColorPickerSetColor(this.value);
				}
			})
			.bind('keyup', function(){
				$(this).ColorPickerSetColor(this.value);
			});".EndJavascript();
}

echo '
<div style="text-align:center">
  <br />'.ToHelp($lng, '&pg=962&mnuid=937&tconfig=0#z2').$admin[1276].'
  <hr />
</div>';

echo '
<br />
<div class="m-auto" style="max-width:600px;">
  <form action="'.CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg=search&amp;regit=1" method="POST" name="pres">
    <div class="row quest p-0 mb-2 w-100 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
    </div>
    <fieldset class="m-0">'.legend('<b>'.$admin[1279].'</b>').'
      <div class="table-responsive-md">
      <table class="table" style="max-width:540px;margin:auto;">
	    <colgroup><col width="75%"><col width="25%"></colgroup>
        <tr>
          <td class="pt-2 pb-1 text-left"><label for="serviz24">'.$admin[163].'</label></td>
          <td class="pt-2 text-left">
			<div class="float-left">
			  <input type="checkbox" id="serviz24" name="serviz24"'.Checked($serviz[24] == 'on').' />
			</div>
		  </td>
        </tr>
        <tr>
          <td class="text-left align-middle">'.$admin[1280].'</td>
          <td class="text-left align-middle">
			<div class="float-left">
			  <div class="d-inline-block"><input class="form-control" type="text" name="pertmin" size="1" value="'.$pertinencemin.'"></div>
              <div class="d-inline-block">%</div>
			</div>
            <script>
              $(document).ready(function(){
                $(\'.hpertmin\').popover({
                  title:"'.$admin[1280].'",
                  content:"'.$help[1].'",
                  trigger:"hover",
                  placement:"auto",
                  html:true
                });
              });
            </script>
            <span class="overpop float-left">&nbsp;
              <a class="hpertmin" title="">
                <i class="far fa-comment-dots"></i>
              </a>
            </span>
		  </td>
        </tr>
        <tr>
          <td class="text-left align-middle">'.$admin[1282].'</td>
          <td style="text-align:left;">
			<div class="float-left">
  		      <input class="form-control" type="text" name="nbrslt" size="1" value="'.$resultbypage.'" />
			</div>
            <script>
              $(document).ready(function(){
                $(\'.hnbrslt\').popover({
                  title:"'.$admin[1282].'",
                  content:"'.$help[2].'",
                  trigger:"hover",
                  placement:"auto",
                  html:true
                });
              });
            </script>
            <span class="overpop float-left">&nbsp;
              <a class="hnbrslt" title="">
                <i class="far fa-comment-dots"></i>
              </a>
            </span>
		  </td>
        </tr>
        <tr>
          <td class="text-left align-middle"><label for="hideprint">'.$admin[1531].'</label></td>
          <td class="form-check text-left">
            <input class="form-check-input" type="checkbox" id="hideprint" name="hideprint"'.Checked($hideprint == 'on').' />
		  </td>
        </tr>
        <tr>
          <td class="text-left align-middle">'.$admin[1483].'</td>
          <td style="text-align:left;">
            <select class="form-control" name="confpage">
              <option value="ALL"'.Selected($confpage == 'ALL').'>'.$admin[1363].'</option>';
        foreach ($templates as $key=>$value)
            echo '
              <option value="'.$key.'"'.Selected($confpage != 'ALL' && $confpage == $key).'>'.$key.' - '.$value.'</option>';
        echo '
            </select>
		  </td>
        </tr>
        <tr>
          <td class="text-left align-middle">'.$admin[932].'</td>
          <td style="text-align:left;">
            <select class="form-control" name="members23">';
        $dbgroups = ReadDBFields(GROUPS);
        echo '
            <option value=""> -- '.$admin[1604].' -- </option>
			<option value="all_members"'.Selected('all_members' == $members[23]).'>'.$admin[690].'</option>';
        foreach ($dbgroups as $dbgroup) {
            echo '
            <option value="'.$dbgroup[0].'"'.Selected($dbgroup[0] == $members[23]).'>'.$dbgroup[0].'</option>';
        }
        echo '
            </select>
		  </td>
        </tr>
      </table>
      </div>
	  <br />';	  //

$barlist  = array();
$greylist = array();
$dossier  = opendir(CHEMIN.'admin/inc/img/search');
while ($element = readdir($dossier)) {
	if (is_file(CHEMIN.'admin/inc/img/search/'.$element)) {
		if (strpos($element, 'bar.gif')) {
			$coloroption = substr($element, 0, strlen($element)-7);
			$barlist[$coloroption] = $coloroption;
		} elseif (strpos($element, 'gris.gif')) {
			$greylist[] = substr($element, 0, strlen($element)-8);
		}
	}
}
closedir($dossier);
asort($barlist);
asort($greylist);
$baroptions = '';
foreach ($barlist as $key => $value) {
	if ($key == $barcolor) {
		$baroptions .= '<option value="'.$key.'" selected="selected">'.$key.'</option>';
	} else {
		$baroptions .= '<option value="'.$key.'">'.$key.'</option>';
	}
}
$greyoptions = '';
foreach ($greylist as $key => $value) {
	if ($key == $bargrey) {
		$greyoptions .= '<option value="'.$key.'" selected="selected">'.$admin[1285].$value.'</option>';
	} else {
		$greyoptions .= '<option value="'.$key.'">'.$admin[1285].$value.'</option>';
	}
}
 echo '
 	  <br />
      <fieldset class="m-0">'.legend($admin[1856]).'
        <div class="table-responsive-md">
        <table class="table m-auto" style="max-width:480px;">
          <colgroup><col width="50%"><col width="50%"></colgroup>
          <tr style="height: 26px;">
            <td style="text-align:left;"><label for="rech_article">'.$admin[1857].'</label></td>
            <td class="text-left align-top form-check">
              <input class="mt-2 form-check-input" type="checkbox" id="rech_article" name="rech_article" value="'.TYP_ART.'" '.(($rech_article == TYP_ART) ? 'checked="checked"' : '').'>
            </td>
          </tr>
          <tr style="height: 26px;">
            <td style="text-align:left;"><label for="rech_download">'.$admin[1858].'</label></td>
            <td class="text-left align-top form-check">
              <input class="mt-2 form-check-input" type="checkbox" id="rech_download" name="rech_download" value="'.TYP_DNLOAD.'" '.(($rech_download == TYP_DNLOAD) ? 'checked="checked"' : '').'>
            </td>
          </tr>
          <tr style="height: 26px;">
            <td style="text-align:left;"><label for="rech_faq">'.$admin[1859].'</label></td>
            <td class="text-left align-top form-check">
              <input class="mt-2 form-check-input" type="checkbox" id="rech_faq" name="rech_faq" value="'.TYP_FAQ.'" '.(($rech_faq == TYP_FAQ) ? 'checked="checked"' : '').'>
            </td>
          </tr>
          <tr style="height: 26px;">
            <td style="text-align:left;"><label for="rech_forum">'.$admin[1860].'</label></td>
            <td class="text-left align-top form-check">
              <input class="mt-2 form-check-input" type="checkbox" id="rech_forum" name="rech_forum" value="'.TYP_FORUM.'" '.(($rech_forum == TYP_FORUM) ? 'checked="checked"' : '').'>
            </td>
          </tr>
          <tr style="height: 26px;">
            <td style="text-align:left;"><label for="rech_livreor">'.$admin[1861].'</label></td>
            <td class="text-left align-top form-check">
              <input class="mt-2 form-check-input" type="checkbox" id="rech_livreor" name="rech_livreor" value="'.TYP_GUESTBK.'" '.(($rech_livreor == TYP_GUESTBK) ? 'checked="checked"' : '').'>
            </td>
          </tr>
          <tr style="height: 26px;">
            <td style="text-align:left;"><label for="rech_liens">'.$admin[1862].'</label></td>
            <td class="text-left align-top form-check">
              <input class="mt-2 form-check-input" type="checkbox" id="rech_liens" name="rech_liens" value="'.TYP_LINKS.'" '.(($rech_liens == TYP_LINKS) ? 'checked="checked"' : '').'>
            </td>
          </tr>
          <tr style="height: 26px;">
            <td style="text-align:left;"><label for="rech_news">'.$admin[1863].'</label></td>
            <td class="text-left align-top form-check">
              <input class="mt-2 form-check-input" type="checkbox" id="rech_news" name="rech_news" value="'.TYP_NEWS.'" '.(($rech_news == TYP_NEWS) ? 'checked="checked"' : '').'>
            </td>
          </tr>
          <tr style="height: 26px;">
            <td style="text-align:left;"><label for="rech_blog">'.$admin[1864].'</label></td>
            <td class="text-left align-top form-check">
              <input class="mt-2 form-check-input" type="checkbox" id="rech_blog" name="rech_blog" value="'.TYP_BLOG.'" '.(($rech_blog == TYP_BLOG) ? 'checked="checked"' : '').'>
            </td>
          </tr>
          <tr style="height: 26px;">
            <td style="text-align:left;"><label for="rech_photo">'.$admin[1865].'</label></td>
            <td class="text-left align-top form-check">
              <input class="mt-2 form-check-input" type="checkbox" id="rech_photo" name="rech_photo" value="'.TYP_PHOTO.'" '.(($rech_photo == TYP_PHOTO) ? 'checked="checked"' : '').'>
            </td>
          </tr>
          <tr style="height: 26px;">
            <td style="text-align:left;"><label for="rech_agenda">'.$admin[1866].'</label></td>
            <td class="text-left align-top form-check">
              <input class="mt-2 form-check-input" type="checkbox" id="rech_agenda" name="rech_agenda" value="'.TYP_AGENDA.'" '.(($rech_agenda == TYP_AGENDA) ? 'checked="checked"' : '').'>
            </td>
          </tr>
          <tr style="height: 26px;">
            <td style="text-align:left;"><label for="rech_archive">'.$admin[1867].'</label></td>
            <td class="text-left align-top form-check">
              <input class="mt-2 form-check-input" type="checkbox" id="rech_archive" name="rech_archive" value="'.TYP_ARCH.'" '.(($rech_archive == TYP_ARCH) ? 'checked="checked"' : '').'>
            </td>
          </tr>
        </table>
        </div>
	  </fieldset>
	  <br />';
echo '
	  <fieldset class="m-0">'.legend($admin[1284]).'
        <div class="table-responsive-md">
        <table class="table m-auto" style="max-width:540px;">
          <colgroup><col span="3"></colgroup>
          <tr>
            <td class="w-35 text-right" style="white-space:nowrap;">
              <select class="form-control w-50 mx-auto" name="barcol" 
                  onchange="images[\'bar\'].src=\''.CHEMIN.'admin/inc/img/search/\'+pres.barcol.options[pres.barcol.selectedIndex].value+\'bar.gif\'">
                '.$baroptions.'
			  </select>
            </td>
            <td class="w-30 text-center" style="text-align:center;white-space:nowrap;">
              <img id="bar" src="'.CHEMIN.'admin/inc/img/search/'.$barcolor.'bar.gif" style="width:33px;height:10px;" />
			  <img id="grey" src="'.CHEMIN.'admin/inc/img/search/'.$bargrey.'gris.gif" style="width:33px;height:10px;" />
            </td>
            <td class="w-35 text-left" style="white-space:nowrap;">
              <select class="form-control w-50 mx-auto" name="bargr" 
                  onchange="images[\'grey\'].src=\''.CHEMIN.'admin/inc/img/search/\'+pres.bargr.options[pres.bargr.selectedIndex].value+\'gris.gif\'">
                '.$greyoptions.'
              </select>
            </td>
          </tr>
        </table>
        </div>
	  </fieldset>
	  <br />
      <fieldset class="m-0">'.legend($admin[1286]).'
        <div class="table-responsive-md">
        <table class="table m-auto" style="max-width:480px;">
          <colgroup><col width="50%"><col width="50%"></colgroup>
		  <tr style="height:48px;">
            <td style="text-align:left;"><br />'.$admin[1287].'</td>
            <td style="text-align:left;vertical-align:top;"><br/>'.GetSelector('police', "$styleocc[0]").'</td>
          </tr>
          <tr style="height: 26px;">
            <td style="text-align:left;"><label for="italic">'.$admin[1288].'</label></td>
            <td class="text-left align-top form-check">
              <input class="mt-2 form-check-input" type="checkbox" id="italic" name="italic" value="italic" '.(($styleocc[1] == "italic") ? 'checked="checked"' : '').'>
            </td>
          </tr>
          <tr style="height: 26px;">
            <td style="text-align:left;"><label for="bold">'.$admin[1289].'</label></td>
            <td class="text-left align-top form-check">
              <input class="mt-2 form-check-input" type="checkbox" id="bold" name="bold" value="bold" '.(($styleocc[2] == "bold") ? 'checked="checked"' : '').'>
            </td>
          </tr>
          <tr style="height: 26px;">
            <td style="text-align:left;"><label for="underline">'.$admin[1290].'</label></td>
            <td class="text-left align-top form-check">
              <input class="mt-2 form-check-input" type="checkbox" id="underline" name="underline" name="underline" value="underline" '.(($styleocc[3] == "underline") ? 'checked="checked"' : '').'>
            </td>
          </tr>
          <tr style="height: 26px;">
            <td style="text-align:left;"><label for="hightlighted">'.$admin[1291].'</label></td>
            <td class="text-left align-top form-check">
              <input class="mt-2 form-check-input" type="checkbox" id="hightlighted" name="hightlighted" value="on" '.(($styleocc[4] == "on") ? 'checked="checked"' : '').'>
            </td>
          </tr>
        </table>
        </div>
	  </fieldset>
	  <br />
      <fieldset class="m-0">'.legend($admin[1292]).'
        <div class="table-responsive-md">
        <table class="table m-auto" style="max-width:480px;">
          <colgroup><col width="50%"><col width="50%"></colgroup>
		  <tr style="height:32px;">
		    <td style="text-align:left;">'.$admin[1293].' 1</td>
			<td style="text-align:left;">'.GetSelector('bgcolor0', $bgcolor[0]).'</td>
		  </tr>
		  <tr style="height:32px;">
		    <td style="text-align:left;">'.$admin[1293].' 2</td>
			<td style="text-align:left;">'.GetSelector('bgcolor1', $bgcolor[1]).'</td>
		  </tr>
		  <tr style="height:32px;">
		    <td style="text-align:left;">'.$admin[1293].' 3</td>
			<td style="text-align:left;">'.GetSelector('bgcolor2', $bgcolor[2]).'</td>
		  </tr>
		  <tr style="height:32px;">
		    <td style="text-align:left;">'.$admin[1293].' 4</td>
			<td style="text-align:left;">'.GetSelector('bgcolor3', $bgcolor[3]).'</td>
		  </tr>
		  <tr style="height:32px;">
		    <td style="text-align:left;">'.$admin[1293].' 5</td>
			<td style="text-align:left;">'.GetSelector('bgcolor4', $bgcolor[4]).'</td>
		  </tr>
		  <tr style="height:32px;">
		    <td style="text-align:left;">'.$admin[1293].' 6</td>
			<td style="text-align:left;">'.GetSelector('bgcolor5', $bgcolor[5]).'</td>
		  </tr>
		  <tr style="height:32px;">
		    <td style="text-align:left;">'.$admin[1293].' 7</td>
			<td style="text-align:left;">'.GetSelector('bgcolor6', $bgcolor[6]).'</td>
		  </tr>
		  <tr style="height:32px;">
		    <td style="text-align:left;">'.$admin[1293].' 8</td>
			<td style="text-align:left;">'.GetSelector('bgcolor7', $bgcolor[7]).'</td>
		  </tr>
	    </table>
        </div>
	  </fieldset>
    </fieldset>
    <div class="row quest p-0 my-2 w-100 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
	</div>';
echo '
    <br />
    <fieldset class="m-0">'.legend('<b>'.$admin[1294].'</b>').'
      <div style="text-align:center">'.$admin[1295].'</div>
      <br />
	  <div class="text-center">
	    '.($lang[1] != '' ? ''.$flag1.'' : '').'
        <textarea class="form-control w-95 mx-auto" rows="12" name="excl0">'.stripslashes($exclure0).'</textarea>
	  </div>';
if ($lang[1] != '') {
	echo '
	  <br />
	  <div class="text-center">
	    '.$flag2.'
        <textarea class="form-control w-95 mx-auto" rows="12" name="excl1">'.stripslashes($exclure1).'</textarea>
	  </div>';
}
echo '
    </fieldset>
    <div class="row quest p-0 mt-3 w-100 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
    </div>
  </form>
</div>';

btable();
include CHEMIN.'admin/bpage.inc';
?>