<?php
/*******************************************************************************
 *   Galeries photos
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
 * v6.00.22 (December 19, 2025) : Modify manage orientation picture
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'photorama.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[18] == '') {
        $nomzone = $admin[322];
        include 'access.inc';
        exit();
    }
}

include CONFIG;
include CHEMIN.INCREP.'lang/'.$lng.'-ugadmin'.INCEXT;
$catgal = '';
$nomgal = '';
$regit  = import('regit');
$act    = import('act');
$sel    = import('sel');
$id     = import('id');
$idpg   = import('idpg');
$numgal = import('numgal');
$messageinfurgent = import('messageinfurgent');
$messageinfo = import('messageinfo');
$typevideo = array('youtube','vimeo','wistia','html5video');
$gal    = SelectDBFieldsByField(ReadDBFields(DBPHOTO), 4, $numgal);
if (@$gal[0]!= '' ) $catgal = @$gal[0][$lng == $lang[0] ? 0 : 1];
if ($catgal!= '' ) $pos    = mb_strpos($catgal, '-->');
if ($catgal!= '' ) $catgal = mb_substr($catgal, ($pos !== false ? $pos + 3 : 0));
if ($catgal!= '' ) $catgal = empty($catgal) ? '' : $catgal.' => ';
if (@$gal[0]!= '' )  $nomgal = @$gal[0][$lng == $lang[0] ? 2 : 3];
if ($nomgal!= '' ) $pos    = mb_strpos($nomgal, '-->');
if ($nomgal!= '' ) $nomgal = mb_substr($nomgal, ($pos !== false ? $pos + 3 : 0));

$dirgal = 'gal_'.$numgal;
$dirmin = $dirgal.'/vgnt';
$def    = import('def');
$param1 = ''; // Nombre maximum de miniatures par galerie
$param2 = ''; // Largeur maximum d'une miniature
$param3 = ''; // Hauteur maximum d'une miniature
$param4 = ''; // Choix effet pour le phototrama
$param5 = ''; // Intervalle entre diapos en secondes
$param6 = ''; // Nombre maximum de miniatures par ligne
$param7 = ''; // Espace en pixels entre les miniatures
$param8 = ''; // Position des miniatures L / C / R
$param9 = ''; // Inclure dans le slishow on/off
$param10 = ''; // Galerie indépendante on/off
$param11 = ''; // Galerie indépendante on/off
$param12 = ''; // Galerie indépendante on/off
$baseurl  = 'admin.php?lng='.$lng.'&amp;pg=photorama';
$galleryvideos = array('ugdt','ugct','uggt','ugsl','ugvg');
$allowed_extensions = array("webm", "mp3", "mp4", "ogv");
$video_types = array("video/webm", "video/mp3", "video/mp4", "video/ogv");
	
$mfu      = @ini_get('max_file_uploads');

/*
max_file_uploads=20
$mfu      = @ini_get('max_file_uploads');

upload_max_filesize=256M
$maxsizefiles = @ini_get('upload_max_filesize');

post_max_size=256M
$maxpostsize = @ini_get('post_max_size');
*/
$maxsizefiles = @ini_get('upload_max_filesize');
$maxpostsizes = @ini_get('post_max_size');

function return_bytes($val) {
    $val = trim($val);
	$last = strtolower($val[strlen($val)-1]);
	$val = substr($val, 0, -1);
    switch($last) {
        // Le modifieur 'G' est disponible
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

include_once CHEMIN.'inc/func_groupscol.php';
$grpcols = ReadMembersCol();
if (!empty($userprefs[1])) {
	$myname  = $userprefs[1];
	$myemail = $userprefs[2];
}
else {
	$myname  = $user[0];
	$myemail = $user[1];
}
$grpcol  = empty($grpcol) ? (isset($grpcols[$myname]) ? (isset($grpcols[$myname][0]) ? $grpcols[$myname][0] : '') : '') : $grpcol;

function info_gd() {
	if (function_exists('gd_info')) {
		$gdinfo = gd_info();
		return '<p style="text-align:center;">GD Version : <b>'.$gdinfo['GD Version'].'</b></p>';
	}
	else return '<p style="text-align:center;color:#FF0000;">ALERTE - WARNING : Librairie GD2 absente - No library GD2</p>';
}
if ($act == '')
{	
	$act = 3;

}
if (!isset($act)) 
{
	$act = 2;

}

// actions rapides
if ($act == 'fast') { 
    $ids   = import('ids');
    $dbids = explode(' ', trim($ids));
    $data  = array();
    for ($ij = 0; $ij < count($dbids); $ij++) {
        $ii = $dbids[$ij];
        if (isset($_POST['a_'.$ii]) && $_POST['a_'.$ii] == 'on') {
            $data[] = array($ii, 'a');
        }
        elseif (isset($_POST['i_'.$ii]) && $_POST['i_'.$ii] == 'on') {
            $data[] = array($ii, 'i');
        }
        elseif (isset($_POST['d_'.$ii]) && $_POST['d_'.$ii] == 'on') {
            $data[] = array($ii, 'd');
        }
    }
    unset($dbids);
	
	if (!empty($data)) {
        $dbphoto = ReadDBFields(PHOTOREP.$dirgal.'/'.$dirgal.DBEXT);
		for ($i = 0; $i < count($dbphoto); $i++) {
			for ($j = 0; $j < count($data); $j++) {
				if ($data[$j][0] == $dbphoto[$i][0]) {
					$dbphoto[$i][1] = $data[$j][1];
					break;
				}
			}
		}
		WriteDBFields(PHOTOREP.$dirgal.'/'.$dirgal.DBEXT, $dbphoto);
		unset($dbphoto);
	}
	unset($data);
	$messageinfo = $admin[1824];
    $act = 3;
}

if ($act == 'fast2') { // suppression ou restauration apres suppression par fast
    $ids2  = import('ids2');
    $dbids = explode(' ', trim($ids2));
    $data  = array();
	for ($ij=0; $ij < count($dbids); $ij++) {
		$ii = $dbids[$ij];
        if (isset($_POST['i_'.$ii]) && $_POST['i_'.$ii] == 'on') {
			$data[] = array($ii, 'i');
		}
		elseif (isset($_POST['s_'.$ii]) && $_POST['s_'.$ii] == 'on') {
			$data[] = array($ii, 's');
		}
	}
	unset($dbids);
	
	if (!empty($data)) {
        $dbphoto = ReadDBFields(PHOTOREP.$dirgal.'/'.$dirgal.DBEXT);
		for ($i = 0; $i < count($dbphoto); $i++) {
			for ($j = 0; $j < count($data); $j++) {
				if ($data[$j][0] == $dbphoto[$i][0]) {
					$dbphoto[$i][1] = $data[$j][1];
					break;
				}
			}
		}
        $dbw = array();
        for ($i = 0; $i < count($dbphoto); $i++) 
            if ($dbphoto[$i][1] == 's') {
                if (is_file(PHOTOREP.$dirgal.'/'.$dbphoto[$i][2])) unlink(PHOTOREP.$dirgal.'/'.$dbphoto[$i][2]);
				if (is_file(PHOTOREP.$dirgal.'/'.$dbphoto[$i][11])) unlink(PHOTOREP.$dirgal.'/'.$dbphoto[$i][11]);
                if (is_file(PHOTOREP.$dirmin.'/'.$dbphoto[$i][2])) unlink(PHOTOREP.$dirmin.'/'.$dbphoto[$i][2]);
            } else
                $dbw[] = $dbphoto[$i];
		WriteDBFields(PHOTOREP.$dirgal.'/'.$dirgal.DBEXT, $dbw);
		unset($dbphoto, $dbw);
	}
	unset($data, $dbphoto);
	$messageinfo = $admin[1825];
    $act = 3;
}

/////////////////////////////////////////////////////////////////////////////////////////

$textalign = array(''=>'-- '.$admin[77].' --', 'left'=>$admin[494], 'center'=>$admin[391], 'right'=>$admin[495]);
$vertalign = array(''=>'-- '.$admin[77].' --', 'top'=>$admin[1583], 'center'=>$admin[391], 'bottom'=>$admin[1667]);
$textdir   = array(''=>'-- '.$admin[77].' --', 'left'=>$admin[494], 'right'=>$admin[495]);
$effects   = array(''=>'-- '.$admin[77].' --', 'bw'=>$ugadmin[91], 'blur'=>$ugadmin[92], 'sepia'=>$ugadmin[93]);
$appears   = array(''=>'-- '.$admin[77].' --', 'slide'=>$ugadmin[94], 'fade'=>$ugadmin[95]);
$panelpos  = array(''=>'-- '.$admin[77].' --', 'inside_bottom'=>$ugadmin[96], 'inside_top'=>$ugadmin[97], 'inside_center'=>$ugadmin[98], 'top'=>$admin[1583], 'bottom'=>$admin[1667]);
$lightbox  = array(''=>'-- '.$admin[77].' --', 'wide'=>$ugadmin[99], 'compact'=>$ugadmin[100]);
$arrowspos = array(''=>'-- '.$admin[77].' --', 'sides'=>$ugadmin[101], 'inside'=>$ugadmin[102]);
$navtype   = array(''=>'-- '.$admin[77].' --', 'bullets'=>$ugadmin[103], 'arrows'=>$ugadmin[104]);
$bullcolor = array(''=>'-- '.$admin[77].' --', 'gray'=>$ugadmin[105], 'blue'=>$ugadmin[106], 'brown'=>$ugadmin[107], 'green'=>$ugadmin[108], 'red'=>$ugadmin[109]);
$vertpos   = array(''=>'-- '.$admin[77].' --', 'top'=>$admin[1583], 'bottom'=>$admin[1667]);
$vertpos2  = array(''=>'-- '.$admin[77].' --', 'top'=>$admin[1583], 'middle'=>$admin[1666], 'bottom'=>$admin[1667]);
$transit   = array(''=>'-- '.$admin[77].' --', 'fit'=>$ugadmin[110],'down'=>$ugadmin[111],'fill'=>$ugadmin[112]);
$blorwh    = array(''=>'-- '.$admin[77].' --', 'black'=>$ugadmin[113], 'white'=>$ugadmin[114]);
$progress  = array(''=>'-- '.$admin[77].' --', 'pie'=>$ugadmin[115], 'pie2'=>$ugadmin[116], 'bar'=>$ugadmin[117]);
$thumbrole = array(''=>'-- '.$admin[77].' --', 'scroll_strip'=>$ugadmin[118], 'advance_item'=>$ugadmin[119]);
$panalign  = array(''=>'-- '.$admin[77].' --', 'top'=>$admin[1583], 'middle'=>$admin[1666], 'bottom'=>$admin[1667], 'left'=>$admin[494], 'center'=>$admin[391], 'right'=>$admin[495]);
$panelpos2 = array(''=>'-- '.$admin[77].' --', 'top'=>$admin[1583], 'bottom'=>$admin[1667], 'left'=>$admin[494], 'right'=>$admin[495]);
$gridvert  = array(''=>'-- '.$admin[77].' --', 'borders'=>$ugadmin[120], 'grid'=>$ugadmin[121], 'middle'=>$admin[1666]);
$gridhoriz = array(''=>'-- '.$admin[77].' --', 'borders'=>$ugadmin[120], 'grid'=>$ugadmin[121], 'center'=>$admin[391]);
$griddir   = array(''=>'-- '.$admin[77].' --', 'left'=>$admin[494], 'bottom'=>$admin[1667]);
$themeskin = array(''=>'-- '.$admin[77].' --', 'right-thumb'=>$ugadmin[122], 'right-title-only'=>$ugadmin[123], 'right-no-thumb'=>$ugadmin[124]);
$typvideo  = array(''=>'-- '.$admin[77].' --', 'youtube'=>'Youtube Video', 'vimeo'=>'Vimeo Video', 'html5video'=>'Html5 Video', 'wistia'=>'Wistia Video');

$strvalues = array('left','center','right','top','bottom','bw','blur','sepia','slide','fade','inside_top','inside_bottom','inside_center','wide','compact','sides',
                   'inside','bullets','arrows','gray','blue','brown','green','red','fit','down','fill','black','white','pie','pie2','bar','scroll_strip','advance_item',
                   'middle','borders','grid','right-thumb','right-title-only','right-no-thumb');

/////////////////////////////////////////////////////////////////////////////////////////

function constructTC($lg = '') {
    global $lng, $lang, $dirgal, $numgal, $catgal, $nomgal,$strvalues;
    if ($lg == '') $lg = $lng;
    
    $def_tcgo   = array(1=>'100%','150','');
    $lbl_tcgo   = array(1=>'gallery_width','gallery_min_width','gallery_background_color');
    $def_tcto   = array(1=>'250','center','3','false','3','true','2','0');
    $lbl_tcto   = array(1=>'tiles_col_width','tiles_align','tiles_space_between_cols','tiles_exact_width','tiles_space_between_cols_mobile','tiles_include_padding','tiles_min_columns','tiles_max_columns');
    $def_tctdo  = array(1=>'false','3','#F0F0F0','0','false','#8B8B8B','true','#8B8B8B','true','0.4','#000000','true','false','true','26','false','bw','false');
    $lbl_tctdo  = array(1=>'tile_enable_border','tile_border_width','tile_border_color','tile_border_radius','tile_enable_outline','tile_outline_color','tile_enable_shadow','tile_shadow_color','tile_enable_overlay','tile_overlay_opacity','tile_overlay_color','tile_enable_icons','tile_show_link_icon','tile_link_newpage','tile_space_between_icons','tile_enable_image_effect','tile_image_effect_type','tile_image_effect_reverse');
    $def_tcttpo = array(1=>'false','false','slide','inside_bottom','0.4','#000000');
    $lbl_tcttpo = array(1=>'tile_enable_textpanel','tile_textpanel_always_on','tile_textpanel_appear_type','tile_textpanel_position','tile_textpanel_background_opacity','tile_textpanel_background_color');
    $def_tclo   = array(1=>'wide','sides','false','true','10','#','0');
    $lbl_tclo   = array(1=>'lightbox_type','lightbox_arrows_position','lightbox_arrows_inside_alwayson','lightbox_slider_image_border','lightbox_slider_image_border_width','lightbox_slider_image_border_color','lightbox_slider_image_border_radius');
        
    $construct = '
<script src="inc/unitegallery/themes/tiles/ug-theme-tiles.js"></script>
<script>
  jQuery(document).ready(function(){
    jQuery("#'.$dirgal.'").unitegallery({
      gallery_theme: "tiles",';
    if (file_exists(PHOTOREP.$dirgal.'/ugtc_'.$numgal.INCEXT)) include PHOTOREP.$dirgal.'/ugtc_'.$numgal.INCEXT;
    foreach ($def_tcgo as $key => $value) 
        if (!empty($ugtc_go[$key]) && $ugtc_go[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tcgo[$key].': '.(!in_array($ugtc_go[$key], $strvalues) && substr($ugtc_go[$key], -1) != '%' && $ugtc_go[$key][0] != '#' ? $ugtc_go[$key] : '"'.$ugtc_go[$key].'"').',';
    foreach ($def_tcto as $key => $value) 
        if (!empty($ugtc_to[$key]) && $ugtc_to[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tcto[$key].': '.(!in_array($ugtc_to[$key], $strvalues) && $ugtc_to[$key][0] != '#' ? $ugtc_to[$key] : '"'.$ugtc_to[$key].'"').',';
    foreach ($def_tctdo as $key => $value) 
        if (!empty($ugtc_tdo[$key]) && $ugtc_tdo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tctdo[$key].': '.(!in_array($ugtc_tdo[$key], $strvalues) && $ugtc_tdo[$key][0] != '#' ? $ugtc_tdo[$key] : '"'.$ugtc_tdo[$key].'"').',';
    foreach ($def_tcttpo as $key => $value) 
        if (!empty($ugtc_ttpo[$key]) && $ugtc_ttpo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tcttpo[$key].': '.(!in_array($ugtc_ttpo[$key], $strvalues) && $ugtc_ttpo[$key][0] != '#' ? $ugtc_ttpo[$key] : '"'.$ugtc_ttpo[$key].'"').',';
    foreach ($def_tclo as $key => $value) 
        if (!empty($ugtc_lo[$key]) && $ugtc_lo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tclo[$key].': '.(!in_array($ugtc_lo[$key], $strvalues) && $ugtc_lo[$key][0] != '#' ? $ugtc_lo[$key] : '"'.$ugtc_lo[$key].'"').',';
    $construct = substr($construct, 0, -1);
    $construct .= '
      });
    });
</script>
<style> .css'.$numgal.' { display: none; margin: auto; padding: 6px 0; } </style>
<div id="'.$dirgal.'" class="css'.$numgal.'">';
    if (file_exists(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT)) {
        $dbphoto = SelectDBFieldsByField(ReadDBFields(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT), 1, 'a');
        if (!empty($dbphoto)) {
            foreach($dbphoto as $photo) $construct .= '
  <a href="'.(!empty($photo[9]) ? $photo[9] : '#z2').'">
    <img alt="'.$photo[$lg == $lang[0] ? 5 : 6].'"
      src="photo/'.$dirgal.'/vgnt/'.$photo[2].'"
      data-image="photo/'.$dirgal.'/'.$photo[2].'"
      data-description="'.$photo[$lg == $lang[0] ? 5 : 6].'"
      style="display:none;" />
  </a>';
        }
    }
    $construct .= '
</div>';

    return $construct;
}

function saveTC() {
    global $lang, $dirgal, $numgal, $def;
	$mettre = "<?php
if (stristr(\$_SERVER[\"SCRIPT_NAME\"], \"ugtc_".$numgal.".inc\")) {
    header(\"location:../index.php\");
    die();
}

\$ugtc_go[1] = stripslashes(\"".($def == 'on' ? '100%' : import('tcgo1'))."\");
\$ugtc_go[2] = stripslashes(\"".($def == 'on' ? '150' : import('tcgo2'))."\");
\$ugtc_go[3] = stripslashes(\"".($def == 'on' ? '' : import('tcgo3'))."\");

\$ugtc_to[1] = stripslashes(\"".($def == 'on' ? '250' : import('tcto1'))."\");
\$ugtc_to[2] = stripslashes(\"".($def == 'on' ? 'center' : import('tcto2'))."\");
\$ugtc_to[3] = stripslashes(\"".($def == 'on' ? '3' : import('tcto3'))."\");
\$ugtc_to[4] = stripslashes(\"".($def == 'on' ? 'false' : (import('tcto4') == 'on' ? 'true' : 'false'))."\");
\$ugtc_to[5] = stripslashes(\"".($def == 'on' ? '3' : import('tcto5'))."\");
\$ugtc_to[6] = stripslashes(\"".($def == 'on' ? 'true' : (import('tcto6') == 'on' ? 'true' : 'false'))."\");
\$ugtc_to[7] = stripslashes(\"".($def == 'on' ? '2' : import('tcto7'))."\");
\$ugtc_to[8] = stripslashes(\"".($def == 'on' ? '0' : import('tcto8'))."\");
    
\$ugtc_tdo[1] = stripslashes(\"".($def == 'on' ? 'false' : (import('tctdo1') == 'on' ? 'true' : 'false'))."\");
\$ugtc_tdo[2] = stripslashes(\"".($def == 'on' ? '3' : import('tctdo2'))."\");
\$ugtc_tdo[3] = stripslashes(\"".($def == 'on' ? '#F0F0F0' : import('tctdo3'))."\");
\$ugtc_tdo[4] = stripslashes(\"".($def == 'on' ? '0' : import('tctdo4'))."\");
\$ugtc_tdo[5] = stripslashes(\"".($def == 'on' ? 'false' : (import('tctdo5') == 'on' ? 'true' : 'false'))."\");
\$ugtc_tdo[6] = stripslashes(\"".($def == 'on' ? '#8B8B8B' : import('tctdo6'))."\");
\$ugtc_tdo[7] = stripslashes(\"".($def == 'on' ? 'true' : (import('tctdo7') == 'on' ? 'true' : 'false'))."\");
\$ugtc_tdo[8] = stripslashes(\"".($def == 'on' ? '#8B8B8B' : import('tctdo8'))."\");
\$ugtc_tdo[9] = stripslashes(\"".($def == 'on' ? 'true' : (import('tctdo9') == 'on' ? 'true' : 'false'))."\");
\$ugtc_tdo[10] = stripslashes(\"".($def == 'on' ? '0.4' : import('tctdo10'))."\");
\$ugtc_tdo[11] = stripslashes(\"".($def == 'on' ? '#000000' : import('tctdo11'))."\");
\$ugtc_tdo[12] = stripslashes(\"".($def == 'on' ? 'true' : (import('tctdo12') == 'on' ? 'true' : 'false'))."\");
\$ugtc_tdo[13] = stripslashes(\"".($def == 'on' ? 'false' : (import('tctdo13') == 'on' ? 'true' : 'false'))."\");
\$ugtc_tdo[14] = stripslashes(\"".($def == 'on' ? 'true' : (import('tctdo14') == 'on' ? 'true' : 'false'))."\");
\$ugtc_tdo[15] = stripslashes(\"".($def == 'on' ? '26' : import('tctdo15'))."\");
\$ugtc_tdo[16] = stripslashes(\"".($def == 'on' ? 'false' : (import('tctdo16') == 'on' ? 'true' : 'false'))."\");
\$ugtc_tdo[17] = stripslashes(\"".($def == 'on' ? 'bw' : import('tctdo17'))."\");
\$ugtc_tdo[18] = stripslashes(\"".($def == 'on' ? 'false' : (import('tctdo18') == 'on' ? 'true' : 'false'))."\");
    
\$ugtc_ttpo[1] = stripslashes(\"".($def == 'on' ? 'false' : (import('tcttpo1') == 'on' ? 'true' : 'false'))."\");
\$ugtc_ttpo[2] = stripslashes(\"".($def == 'on' ? 'false' : (import('tcttpo2') == 'on' ? 'true' : 'false'))."\");
\$ugtc_ttpo[3] = stripslashes(\"".($def == 'on' ? 'slide' : import('tcttpo3'))."\");
\$ugtc_ttpo[4] = stripslashes(\"".($def == 'on' ? 'inside_bottom' : import('tcttpo4'))."\");
\$ugtc_ttpo[5] = stripslashes(\"".($def == 'on' ? '0.4' : import('tcttpo5'))."\");
\$ugtc_ttpo[6] = stripslashes(\"".($def == 'on' ? '#000000' : import('tcttpo6'))."\");

\$ugtc_lo[1] = stripslashes(\"".($def == 'on' ? 'wide' : import('tclo1'))."\");
\$ugtc_lo[2] = stripslashes(\"".($def == 'on' ? 'sides' : import('tclo2'))."\");
\$ugtc_lo[3] = stripslashes(\"".($def == 'on' ? 'false' : (import('tclo3') == 'on' ? 'true' : 'false'))."\");
\$ugtc_lo[4] = stripslashes(\"".($def == 'on' ? 'true' : (import('tclo4') == 'on' ? 'true' : 'false'))."\");
\$ugtc_lo[5] = stripslashes(\"".($def == 'on' ? '10' : import('tclo5'))."\");
\$ugtc_lo[6] = stripslashes(\"".($def == 'on' ? '#FFFFFF' : import('tclo6'))."\");
\$ugtc_lo[7] = stripslashes(\"".($def == 'on' ? '0' : import('tclo7'))."\");
?>";
    WriteFullDB(PHOTOREP.$dirgal.'/ugtc_'.$numgal.INCEXT, $mettre);
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[0].'-thugtc_'.$numgal.INCEXT, constructTC($lang[0]));
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[1].'-thugtc_'.$numgal.INCEXT, constructTC($lang[1]));
}

/////////////////////////////////////////////////////////////////////////////////////////

function constructTJ($lg = '') {
    global $lng, $lang, $dirgal, $numgal, $catgal, $nomgal, $strvalues;
    if ($lg == '') $lg = $lng;
    
    $def_tjgo   = array(1=>'100%','150','');
    $lbl_tjgo   = array(1=>'gallery_width','gallery_min_width','gallery_background_color');
    $def_tjto   = array(1=>'150','3');
    $lbl_tjto   = array(1=>'tiles_justified_row_height','tiles_justified_space_between');
    $def_tjtdo  = array(1=>'false','3','#F0F0F0','0','false','#8B8B8B','true','#8B8B8B','true','0.4','#000000','true','false','true','26','false','bw','false');
    $lbl_tjtdo  = array(1=>'tile_enable_border','tile_border_width','tile_border_color','tile_border_radius','tile_enable_outline','tile_outline_color','tile_enable_shadow','tile_shadow_color','tile_enable_overlay','tile_overlay_opacity','tile_overlay_color','tile_enable_icons','tile_show_link_icon','tile_link_newpage','tile_space_between_icons','tile_enable_image_effect','tile_image_effect_type','tile_image_effect_reverse');
    $def_tjttpo = array(1=>'false','false','slide','inside_bottom','0.4','#000000');
    $lbl_tjttpo = array(1=>'tile_enable_textpanel','tile_textpanel_always_on','tile_textpanel_appear_type','tile_textpanel_position','tile_textpanel_background_opacity','tile_textpanel_background_color');
    $def_tjlo   = array(1=>'wide','sides','false','true','10','#FFFFFF','0');
    $lbl_tjlo   = array(1=>'lightbox_type','lightbox_arrows_position','lightbox_arrows_inside_alwayson','lightbox_slider_image_border','lightbox_slider_image_border_width','lightbox_slider_image_border_color','lightbox_slider_image_border_radius');
    
    $construct = '
<script src="inc/unitegallery/themes/tiles/ug-theme-tiles.js"></script>
<script>
  jQuery(document).ready(function(){
    jQuery("#'.$dirgal.'").unitegallery({
      gallery_theme: "tiles",
      tiles_type: "justified",';
    if (file_exists(PHOTOREP.$dirgal.'/ugtj_'.$numgal.INCEXT)) include PHOTOREP.$dirgal.'/ugtj_'.$numgal.INCEXT;
    foreach ($def_tjgo as $key => $value) 
        if (!empty($ugtj_go[$key]) && $ugtj_go[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tjgo[$key].': '.(!in_array($ugtj_go[$key], $strvalues) && substr($ugtj_go[$key], -1) != '%' && $ugtj_go[$key][0] != '#' ? $ugtj_go[$key] : '"'.$ugtj_go[$key].'"').',';
    foreach ($def_tjto as $key => $value) 
        if (!empty($ugtj_to[$key]) && $ugtj_to[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tjto[$key].': '.(!in_array($ugtj_to[$key], $strvalues) && $ugtj_to[$key][0] != '#' ? $ugtj_to[$key] : '"'.$ugtj_to[$key].'"').',';
    foreach ($def_tjtdo as $key => $value) 
        if (!empty($ugtj_tdo[$key]) && $ugtj_tdo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tjtdo[$key].': '.(!in_array($ugtj_tdo[$key], $strvalues) && $ugtj_tdo[$key][0] != '#' ? $ugtj_tdo[$key] : '"'.$ugtj_tdo[$key].'"').',';
    foreach ($def_tjttpo as $key => $value) 
        if (!empty($ugtj_ttpo[$key]) && $ugtj_ttpo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tjttpo[$key].': '.(!in_array($ugtj_ttpo[$key], $strvalues) && $ugtj_ttpo[$key][0] != '#' ? $ugtj_ttpo[$key] : '"'.$ugtj_ttpo[$key].'"').',';
    foreach ($def_tjlo as $key => $value) 
        if (!empty($ugtj_lo[$key]) && $ugtj_lo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tjlo[$key].': '.(!in_array($ugtj_lo[$key], $strvalues) && $ugtj_lo[$key][0] != '#' ? $ugtj_lo[$key] : '"'.$ugtj_lo[$key].'"').',';
    $construct = substr($construct, 0, -1);
    $construct .= '
      });
    });
</script>
<style> .css'.$numgal.' { display: none; margin: auto; padding: 6px 0; } </style>
<div id="'.$dirgal.'" class="css'.$numgal.'">';
    if (file_exists(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT)) {
        $dbphoto = SelectDBFieldsByField(ReadDBFields(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT), 1, 'a');
        if (!empty($dbphoto)) {
            foreach($dbphoto as $photo) $construct .= '
  <a href="'.(!empty($photo[9]) ? $photo[9] : '#z2').'">
    <img alt="'.$photo[$lg == $lang[0] ? 5 : 6].'"
      src="photo/'.$dirgal.'/vgnt/'.$photo[2].'"
      data-image="photo/'.$dirgal.'/'.$photo[2].'"
      data-description="'.$photo[$lg == $lang[0] ? 5 : 6].'"
      style="display:none;" />
  </a>';
        }
    }
    $construct .= '
</div>';

    return $construct;
}

function saveTJ() {
    global $lang, $dirgal, $numgal, $def;
	$mettre = "<?php
if (stristr(\$_SERVER[\"SCRIPT_NAME\"], \"ugtj_".$numgal.".inc\")) {
    header(\"location:../index.php\");
    die();
}

\$ugtj_go[1] = stripslashes(\"".($def == 'on' ? '100%' : import('tjgo1'))."\");
\$ugtj_go[2] = stripslashes(\"".($def == 'on' ? '150' : import('tjgo2'))."\");
\$ugtj_go[3] = stripslashes(\"".($def == 'on' ? '' : import('tjgo3'))."\");
    
\$ugtj_to[1] = stripslashes(\"".($def == 'on' ? '150' : import('tjto1'))."\");
\$ugtj_to[2] = stripslashes(\"".($def == 'on' ? '3' : import('tjto2'))."\");

\$ugtj_tdo[1] = stripslashes(\"".($def == 'on' ? 'false' : (import('tjtdo1') == 'on' ? 'true' : 'false'))."\");
\$ugtj_tdo[2] = stripslashes(\"".($def == 'on' ? '3' : import('tjtdo2'))."\");
\$ugtj_tdo[3] = stripslashes(\"".($def == 'on' ? '#F0F0F0' : import('tjtdo3'))."\");
\$ugtj_tdo[4] = stripslashes(\"".($def == 'on' ? '0' : import('tjtdo4'))."\");
\$ugtj_tdo[5] = stripslashes(\"".($def == 'on' ? 'false' : (import('tjtdo5') == 'on' ? 'true' : 'false'))."\");
\$ugtj_tdo[6] = stripslashes(\"".($def == 'on' ? '#8B8B8B' : import('tjtdo6'))."\");
\$ugtj_tdo[7] = stripslashes(\"".($def == 'on' ? 'true' : (import('tjtdo7') == 'on' ? 'true' : 'false'))."\");
\$ugtj_tdo[8] = stripslashes(\"".($def == 'on' ? '#8B8B8B' : import('tjtdo8'))."\");
\$ugtj_tdo[9] = stripslashes(\"".($def == 'on' ? 'true' : (import('tjtdo9') == 'on' ? 'true' : 'false'))."\");
\$ugtj_tdo[10] = stripslashes(\"".($def == 'on' ? '0.4' : import('tjtdo10'))."\");
\$ugtj_tdo[11] = stripslashes(\"".($def == 'on' ? '#000000' : import('tjtdo11'))."\");
\$ugtj_tdo[12] = stripslashes(\"".($def == 'on' ? 'true' : (import('tjtdo12') == 'on' ? 'true' : 'false'))."\");
\$ugtj_tdo[13] = stripslashes(\"".($def == 'on' ? 'false' : (import('tjtdo13') == 'on' ? 'true' : 'false'))."\");
\$ugtj_tdo[14] = stripslashes(\"".($def == 'on' ? 'true' : (import('tjtdo14') == 'on' ? 'true' : 'false'))."\");
\$ugtj_tdo[15] = stripslashes(\"".($def == 'on' ? '26' : import('tjtdo15'))."\");
\$ugtj_tdo[16] = stripslashes(\"".($def == 'on' ? 'false' : (import('tjtdo16') == 'on' ? 'true' : 'false'))."\");
\$ugtj_tdo[17] = stripslashes(\"".($def == 'on' ? 'bw' : import('tjtdo17'))."\");
\$ugtj_tdo[18] = stripslashes(\"".($def == 'on' ? 'false' : (import('tjtdo18') == 'on' ? 'true' : 'false'))."\");
    
\$ugtj_ttpo[1] = stripslashes(\"".($def == 'on' ? 'false' : (import('tjttpo1') == 'on' ? 'true' : 'false'))."\");
\$ugtj_ttpo[2] = stripslashes(\"".($def == 'on' ? 'false' : (import('tjttpo2') == 'on' ? 'true' : 'false'))."\");
\$ugtj_ttpo[3] = stripslashes(\"".($def == 'on' ? 'slide' : import('tjttpo3'))."\");
\$ugtj_ttpo[4] = stripslashes(\"".($def == 'on' ? 'inside_bottom' : import('tjttpo4'))."\");
\$ugtj_ttpo[5] = stripslashes(\"".($def == 'on' ? '0.4' : import('tjttpo5'))."\");
\$ugtj_ttpo[6] = stripslashes(\"".($def == 'on' ? '#000000' : import('tjttpo6'))."\");

\$ugtj_lo[1] = stripslashes(\"".($def == 'on' ? 'wide' : import('tjlo1'))."\");
\$ugtj_lo[2] = stripslashes(\"".($def == 'on' ? 'sides' : import('tjlo2'))."\");
\$ugtj_lo[3] = stripslashes(\"".($def == 'on' ? 'false' : (import('tjlo3') == 'on' ? 'true' : 'false'))."\");
\$ugtj_lo[4] = stripslashes(\"".($def == 'on' ? 'true' : (import('tjlo4') == 'on' ? 'true' : 'false'))."\");
\$ugtj_lo[5] = stripslashes(\"".($def == 'on' ? '10' : import('tjlo5'))."\");
\$ugtj_lo[6] = stripslashes(\"".($def == 'on' ? '#FFFFFF' : import('tjlo6'))."\");
\$ugtj_lo[7] = stripslashes(\"".($def == 'on' ? '0' : import('tjlo7'))."\");
?>";
    WriteFullDB(PHOTOREP.$dirgal.'/ugtj_'.$numgal.INCEXT, $mettre);
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[0].'-thugtj_'.$numgal.INCEXT, constructTJ($lang[0]));
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[1].'-thugtj_'.$numgal.INCEXT, constructTJ($lang[1]));
}

/////////////////////////////////////////////////////////////////////////////////////////

function constructTN($lg = '') {
    global $lng, $lang, $dirgal, $numgal, $catgal, $nomgal, $strvalues;
    if ($lg == '') $lg = $lng;
    
    $def_tngo   = array(1=>'100%','150','');
    $lbl_tngo   = array(1=>'gallery_width','gallery_min_width','gallery_background_color');
    $def_tnto   = array(1=>'true','3','3','250','2');
    $lbl_tnto   = array(1=>'tiles_enable_transition','tiles_space_between_cols','tiles_space_between_cols_mobile','tiles_nested_optimal_tile_width','tiles_min_columns');
    $def_tntdo  = array(1=>'false','3','#F0F0F0','0','false','#8B8B8B','true','#8B8B8B','true','0.4','#000000','true','false','true','26','false','bw','false');
    $lbl_tntdo  = array(1=>'tile_enable_border','tile_border_width','tile_border_color','tile_border_radius','tile_enable_outline','tile_outline_color','tile_enable_shadow','tile_shadow_color','tile_enable_overlay','tile_overlay_opacity','tile_overlay_color','tile_enable_icons','tile_show_link_icon','tile_link_newpage','tile_space_between_icons','tile_enable_image_effect','tile_image_effect_type','tile_image_effect_reverse');
    $def_tnttpo = array(1=>'false','false','slide','inside_bottom','0.4','#000000');
    $lbl_tnttpo = array(1=>'tile_enable_textpanel','tile_textpanel_always_on','tile_textpanel_appear_type','tile_textpanel_position','tile_textpanel_background_opacity','tile_textpanel_background_color');
    $def_tnlo   = array(1=>'wide','sides','false','true','10','#FFFFFF','0');
    $lbl_tnlo   = array(1=>'lightbox_type','lightbox_arrows_position','lightbox_arrows_inside_alwayson','lightbox_slider_image_border','lightbox_slider_image_border_width','lightbox_slider_image_border_color','lightbox_slider_image_border_radius');
        
    $construct = '
<script src="inc/unitegallery/themes/tiles/ug-theme-tiles.js"></script>
<script>
  jQuery(document).ready(function(){
    jQuery("#'.$dirgal.'").unitegallery({
      gallery_theme: "tiles",
      tiles_type: "nested",';
    if (file_exists(PHOTOREP.$dirgal.'/ugtn_'.$numgal.INCEXT)) include PHOTOREP.$dirgal.'/ugtn_'.$numgal.INCEXT;
    foreach ($def_tngo as $key => $value) 
        if (!empty($ugtn_go[$key]) && $ugtn_go[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tngo[$key].': '.(!in_array($ugtn_go[$key], $strvalues) && substr($ugtn_go[$key], -1) != '%' && $ugtn_go[$key][0] != '#' ? $ugtn_go[$key] : '"'.$ugtn_go[$key].'"').',';
    foreach ($def_tnto as $key => $value) 
        if (!empty($ugtn_to[$key]) && $ugtn_to[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tnto[$key].': '.(!in_array($ugtn_to[$key], $strvalues) && $ugtn_to[$key][0] != '#' ? $ugtn_to[$key] : '"'.$ugtn_to[$key].'"').',';
    foreach ($def_tntdo as $key => $value) 
        if (!empty($ugtn_tdo[$key]) && $ugtn_tdo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tntdo[$key].': '.(!in_array($ugtn_tdo[$key], $strvalues) && $ugtn_tdo[$key][0] != '#' ? $ugtn_tdo[$key] : '"'.$ugtn_tdo[$key].'"').',';
    foreach ($def_tnttpo as $key => $value) 
        if (!empty($ugtn_ttpo[$key]) && $ugtn_ttpo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tnttpo[$key].': '.(!in_array($ugtn_ttpo[$key], $strvalues) && $ugtn_ttpo[$key][0] != '#' ? $ugtn_ttpo[$key] : '"'.$ugtn_ttpo[$key].'"').',';
    foreach ($def_tnlo as $key => $value) 
        if (!empty($ugtn_lo[$key]) && $ugtn_lo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tnlo[$key].': '.(!in_array($ugtn_lo[$key], $strvalues) && $ugtn_lo[$key][0] != '#' ? $ugtn_lo[$key] : '"'.$ugtn_lo[$key].'"').',';
    $construct = substr($construct, 0, -1);
    $construct .= '
      });
    });
</script>
<style> .css'.$numgal.' { display: none; margin: auto; padding: 6px 0; } </style>
<div id="'.$dirgal.'" class="css'.$numgal.'">';
    if (file_exists(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT)) {
        $dbphoto = SelectDBFieldsByField(ReadDBFields(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT), 1, 'a');
        if (!empty($dbphoto)) {
            foreach($dbphoto as $photo) $construct .= '
  <a href="'.(!empty($photo[9]) ? $photo[9] : '#z2').'">
    <img alt="'.$photo[$lg == $lang[0] ? 5 : 6].'"
      src="photo/'.$dirgal.'/vgnt/'.$photo[2].'"
      data-image="photo/'.$dirgal.'/'.$photo[2].'"
      data-description="'.$photo[$lg == $lang[0] ? 5 : 6].'"
      style="display:none;" />
  </a>';
        }
    }
    $construct .= '
</div>';

    return $construct;
}

function saveTN() {
    global $lang, $dirgal, $numgal, $def;
	$mettre = "<?php
if (stristr(\$_SERVER[\"SCRIPT_NAME\"], \"ugtn_".$numgal.".inc\")) {
    header(\"location:../index.php\");
    die();
}

\$ugtn_go[1] = stripslashes(\"".($def == 'on' ? '100%' : import('tngo1'))."\");
\$ugtn_go[2] = stripslashes(\"".($def == 'on' ? '150' : import('tngo2'))."\");
\$ugtn_go[3] = stripslashes(\"".($def == 'on' ? '' : import('tngo3'))."\");

\$ugtn_to[1] = stripslashes(\"".($def == 'on' ? 'true' : (import('tnto1') == 'on' ? 'true' : 'false'))."\");
\$ugtn_to[2] = stripslashes(\"".($def == 'on' ? '3' : import('tnto2'))."\");
\$ugtn_to[3] = stripslashes(\"".($def == 'on' ? '3' : import('tnto3'))."\");
\$ugtn_to[4] = stripslashes(\"".($def == 'on' ? '250' : import('tnto4'))."\");
\$ugtn_to[5] = stripslashes(\"".($def == 'on' ? '2' : import('tnto5'))."\");
    
\$ugtn_tdo[1] = stripslashes(\"".($def == 'on' ? 'false' : (import('tntdo1') == 'on' ? 'true' : 'false'))."\");
\$ugtn_tdo[2] = stripslashes(\"".($def == 'on' ? '3' : import('tntdo2'))."\");
\$ugtn_tdo[3] = stripslashes(\"".($def == 'on' ? '#F0F0F0' : import('tntdo3'))."\");
\$ugtn_tdo[4] = stripslashes(\"".($def == 'on' ? '0' : import('tntdo4'))."\");
\$ugtn_tdo[5] = stripslashes(\"".($def == 'on' ? 'false' : (import('tntdo5') == 'on' ? 'true' : 'false'))."\");
\$ugtn_tdo[6] = stripslashes(\"".($def == 'on' ? '#8B8B8B' : import('tntdo6'))."\");
\$ugtn_tdo[7] = stripslashes(\"".($def == 'on' ? 'true' : (import('tntdo7') == 'on' ? 'true' : 'false'))."\");
\$ugtn_tdo[8] = stripslashes(\"".($def == 'on' ? '#8B8B8B' : import('tntdo8'))."\");
\$ugtn_tdo[9] = stripslashes(\"".($def == 'on' ? 'true' : (import('tntdo9') == 'on' ? 'true' : 'false'))."\");
\$ugtn_tdo[10] = stripslashes(\"".($def == 'on' ? '0.4' : import('tntdo10'))."\");
\$ugtn_tdo[11] = stripslashes(\"".($def == 'on' ? '#000000' : import('tntdo11'))."\");
\$ugtn_tdo[12] = stripslashes(\"".($def == 'on' ? 'true' : (import('tntdo12') == 'on' ? 'true' : 'false'))."\");
\$ugtn_tdo[13] = stripslashes(\"".($def == 'on' ? 'false' : (import('tntdo13') == 'on' ? 'true' : 'false'))."\");
\$ugtn_tdo[14] = stripslashes(\"".($def == 'on' ? 'true' : (import('tntdo14') == 'on' ? 'true' : 'false'))."\");
\$ugtn_tdo[15] = stripslashes(\"".($def == 'on' ? '26' : import('tntdo15'))."\");
\$ugtn_tdo[16] = stripslashes(\"".($def == 'on' ? 'false' : (import('tntdo16') == 'on' ? 'true' : 'false'))."\");
\$ugtn_tdo[17] = stripslashes(\"".($def == 'on' ? 'bw' : import('tntdo17'))."\");
\$ugtn_tdo[18] = stripslashes(\"".($def == 'on' ? 'false' : (import('tntdo18') == 'on' ? 'true' : 'false'))."\");
    
\$ugtn_ttpo[1] = stripslashes(\"".($def == 'on' ? 'false' : (import('tnttpo1') == 'on' ? 'true' : 'false'))."\");
\$ugtn_ttpo[2] = stripslashes(\"".($def == 'on' ? 'false' : (import('tnttpo2') == 'on' ? 'true' : 'false'))."\");
\$ugtn_ttpo[3] = stripslashes(\"".($def == 'on' ? 'slide' : import('tnttpo3'))."\");
\$ugtn_ttpo[4] = stripslashes(\"".($def == 'on' ? 'inside_bottom' : import('tnttpo4'))."\");
\$ugtn_ttpo[5] = stripslashes(\"".($def == 'on' ? '0.4' : import('tnttpo5'))."\");
\$ugtn_ttpo[6] = stripslashes(\"".($def == 'on' ? '#000000' : import('tnttpo6'))."\");

\$ugtn_lo[1] = stripslashes(\"".($def == 'on' ? 'wide' : import('tnlo1'))."\");
\$ugtn_lo[2] = stripslashes(\"".($def == 'on' ? 'sides' : import('tnlo2'))."\");
\$ugtn_lo[3] = stripslashes(\"".($def == 'on' ? 'false' : (import('tnlo3') == 'on' ? 'true' : 'false'))."\");
\$ugtn_lo[4] = stripslashes(\"".($def == 'on' ? 'true' : (import('tnlo4') == 'on' ? 'true' : 'false'))."\");
\$ugtn_lo[5] = stripslashes(\"".($def == 'on' ? '10' : import('tnlo5'))."\");
\$ugtn_lo[6] = stripslashes(\"".($def == 'on' ? '#FFFFFF' : import('tnlo6'))."\");
\$ugtn_lo[7] = stripslashes(\"".($def == 'on' ? '0' : import('tnlo7'))."\");
?>";
    WriteFullDB(PHOTOREP.$dirgal.'/ugtn_'.$numgal.INCEXT, $mettre);
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[0].'-thugtn_'.$numgal.INCEXT, constructTN($lang[0]));
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[1].'-thugtn_'.$numgal.INCEXT, constructTN($lang[1]));
}

/////////////////////////////////////////////////////////////////////////////////////////

function constructTG($lg = '') {
    global $lng, $lang, $dirgal, $numgal, $catgal, $nomgal, $strvalues;
    if ($lg == '') $lg = $lng;
    
    $def_tgmo   = array(1=>'180','150','10','20','20');
    $lbl_tgmo   = array(1=>'tile_width','tile_height','grid_padding','grid_space_between_cols','grid_space_between_rows');
    $def_tggo   = array(1=>'100%','150','');
    $lbl_tggo   = array(1=>'gallery_width','gallery_min_width','gallery_background_color');
    $def_tgno   = array(1=>'3','bullets','40','gray','12','20','5');
    $lbl_tgno   = array(1=>'grid_num_rows','theme_navigation_type','theme_bullets_margin_top','theme_bullets_color','bullets_space_between','theme_arrows_margin_top','theme_space_between_arrows');
    $def_tgtdo  = array(1=>'false','3','#F0F0F0','0','false','#8B8B8B','true','#8B8B8B','true','0.4','#000000','true','false','true','26','false','bw','false');
    $lbl_tgtdo  = array(1=>'tile_enable_border','tile_border_width','tile_border_color','tile_border_radius','tile_enable_outline','tile_outline_color','tile_enable_shadow','tile_shadow_color','tile_enable_overlay','tile_overlay_opacity','tile_overlay_color','tile_enable_icons','tile_show_link_icon','tile_link_newpage','tile_space_between_icons','tile_enable_image_effect','tile_image_effect_type','tile_image_effect_reverse');
    $def_tgttpo = array(1=>'false','false','slide','inside_bottom','0.4','#000000');
    $lbl_tgttpo = array(1=>'tile_enable_textpanel','tile_textpanel_always_on','tile_textpanel_appear_type','tile_textpanel_position','tile_textpanel_background_opacity','tile_textpanel_background_color');
    $def_tglo   = array(1=>'wide','sides','false','true','10','#FFFFFF','0');
    $lbl_tglo   = array(1=>'lightbox_type','lightbox_arrows_position','lightbox_arrows_inside_alwayson','lightbox_slider_image_border','lightbox_slider_image_border_width','lightbox_slider_image_border_color','lightbox_slider_image_border_radius');
        
    $construct = '
<script src="inc/unitegallery/themes/tilesgrid/ug-theme-tilesgrid.js"></script>
<script>
  jQuery(document).ready(function(){
    jQuery("#'.$dirgal.'").unitegallery({
      gallery_theme: "tilesgrid",';
    if (file_exists(PHOTOREP.$dirgal.'/ugtg_'.$numgal.INCEXT)) include PHOTOREP.$dirgal.'/ugtg_'.$numgal.INCEXT;
    foreach ($def_tgmo as $key => $value) 
        if (!empty($ugtg_mo[$key]) && $ugtg_mo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tgmo[$key].': '.(!in_array($ugtg_mo[$key], $strvalues) && $ugtg_mo[$key][0] != '#' ? $ugtg_mo[$key] : '"'.$ugtg_mo[$key].'"').',';
    foreach ($def_tggo as $key => $value) 
        if (!empty($ugtg_go[$key]) && $ugtg_go[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tggo[$key].': '.(!in_array($ugtg_go[$key], $strvalues) && substr($ugtg_go[$key], -1) != '%' && $ugtg_go[$key][0] != '#' ? $ugtg_go[$key] : '"'.$ugtg_go[$key].'"').',';
    foreach ($def_tgno as $key => $value) 
        if (!empty($ugtg_no[$key]) && $ugtg_no[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tgno[$key].': '.(!in_array($ugtg_no[$key], $strvalues) && $ugtg_no[$key][0] != '#' ? $ugtg_no[$key] : '"'.$ugtg_no[$key].'"').',';
    foreach ($def_tgtdo as $key => $value) 
        if (!empty($ugtg_tdo[$key]) && $ugtg_tdo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tgtdo[$key].': '.(!in_array($ugtg_tdo[$key], $strvalues) && $ugtg_tdo[$key][0] != '#' ? $ugtg_tdo[$key] : '"'.$ugtg_tdo[$key].'"').',';
    foreach ($def_tgttpo as $key => $value) 
        if (!empty($ugtg_ttpo[$key]) && $ugtg_ttpo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tgttpo[$key].': '.(!in_array($ugtg_ttpo[$key], $strvalues) && $ugtg_ttpo[$key][0] != '#' ? $ugtg_ttpo[$key] : '"'.$ugtg_ttpo[$key].'"').',';
    foreach ($def_tglo as $key => $value) 
        if (!empty($ugtg_lo[$key]) && $ugtg_lo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_tglo[$key].': '.(!in_array($ugtg_lo[$key], $strvalues) && $ugtg_lo[$key][0] != '#' ? $ugtg_lo[$key] : '"'.$ugtg_lo[$key].'"').',';
    $construct = substr($construct, 0, -1);
    $construct .= '
      });
    });
</script>
<style> .css'.$numgal.' { display: none; margin: auto; padding: 6px 0; } </style>
<div id="'.$dirgal.'" class="css'.$numgal.'">';
    if (file_exists(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT)) {
        $dbphoto = SelectDBFieldsByField(ReadDBFields(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT), 1, 'a');
        if (!empty($dbphoto)) {
            foreach($dbphoto as $photo) $construct .= '
  <a href="'.(!empty($photo[9]) ? $photo[9] : '#z2').'">
    <img alt="'.$photo[$lg == $lang[0] ? 5 : 6].'"
      src="photo/'.$dirgal.'/vgnt/'.$photo[2].'"
      data-image="photo/'.$dirgal.'/'.$photo[2].'"
      data-description="'.$photo[$lg == $lang[0] ? 5 : 6].'"
      style="display:none;" />
  </a>';
        }
    }
    $construct .= '
</div>';

    return $construct;
}

/////////////////////////////////////////////////////////////////////////////////////////

function saveTG() {
    global $lang, $dirgal, $numgal, $def;
	$mettre = "<?php
if (stristr(\$_SERVER[\"SCRIPT_NAME\"], \"ugtg_".$numgal.".inc\")) {
    header(\"location:../index.php\");
    die();
}

\$ugtg_mo[1] = stripslashes(\"".($def == 'on' ? '180' : import('tgmo1'))."\");
\$ugtg_mo[2] = stripslashes(\"".($def == 'on' ? '150' : import('tgmo2'))."\");
\$ugtg_mo[3] = stripslashes(\"".($def == 'on' ? '10' : import('tgmo3'))."\");
\$ugtg_mo[4] = stripslashes(\"".($def == 'on' ? '20' : import('tgmo4'))."\");
\$ugtg_mo[5] = stripslashes(\"".($def == 'on' ? '20' : import('tgmo5'))."\");

\$ugtg_go[1] = stripslashes(\"".($def == 'on' ? '100%' : import('tggo1'))."\");
\$ugtg_go[2] = stripslashes(\"".($def == 'on' ? '150' : import('tggo2'))."\");
\$ugtg_go[3] = stripslashes(\"".($def == 'on' ? '' : import('tggo3'))."\");

\$ugtg_no[1] = stripslashes(\"".($def == 'on' ? '3' : import('tgno1'))."\");
\$ugtg_no[2] = stripslashes(\"".($def == 'on' ? 'bullets' : import('tgno2'))."\");
\$ugtg_no[3] = stripslashes(\"".($def == 'on' ? '40' : import('tgno3'))."\");
\$ugtg_no[4] = stripslashes(\"".($def == 'on' ? 'gray' : import('tgno4'))."\");
\$ugtg_no[5] = stripslashes(\"".($def == 'on' ? '12' : import('tgno5'))."\");
\$ugtg_no[6] = stripslashes(\"".($def == 'on' ? '20' : import('tgno6'))."\");
\$ugtg_no[7] = stripslashes(\"".($def == 'on' ? '5' : import('tgno7'))."\");

\$ugtg_tdo[1] = stripslashes(\"".($def == 'on' ? 'false' : (import('tgtdo1') == 'on' ? 'true' : 'false'))."\");
\$ugtg_tdo[2] = stripslashes(\"".($def == 'on' ? '3' : import('tgtdo2'))."\");
\$ugtg_tdo[3] = stripslashes(\"".($def == 'on' ? '#F0F0F0' : import('tgtdo3'))."\");
\$ugtg_tdo[4] = stripslashes(\"".($def == 'on' ? '0' : import('tgtdo4'))."\");
\$ugtg_tdo[5] = stripslashes(\"".($def == 'on' ? 'false' : (import('tgtdo5') == 'on' ? 'true' : 'false'))."\");
\$ugtg_tdo[6] = stripslashes(\"".($def == 'on' ? '#8B8B8B' : import('tgtdo6'))."\");
\$ugtg_tdo[7] = stripslashes(\"".($def == 'on' ? 'true' : (import('tgtdo7') == 'on' ? 'true' : 'false'))."\");
\$ugtg_tdo[8] = stripslashes(\"".($def == 'on' ? '#8B8B8B' : import('tgtdo8'))."\");
\$ugtg_tdo[9] = stripslashes(\"".($def == 'on' ? 'true' : (import('tgtdo9') == 'on' ? 'true' : 'false'))."\");
\$ugtg_tdo[10] = stripslashes(\"".($def == 'on' ? '0.4' : import('tgtdo10'))."\");
\$ugtg_tdo[11] = stripslashes(\"".($def == 'on' ? '#000000' : import('tgtdo11'))."\");
\$ugtg_tdo[12] = stripslashes(\"".($def == 'on' ? 'true' : (import('tgtdo12') == 'on' ? 'true' : 'false'))."\");
\$ugtg_tdo[13] = stripslashes(\"".($def == 'on' ? 'false' : (import('tgtdo13') == 'on' ? 'true' : 'false'))."\");
\$ugtg_tdo[14] = stripslashes(\"".($def == 'on' ? 'true' : (import('tgtdo14') == 'on' ? 'true' : 'false'))."\");
\$ugtg_tdo[15] = stripslashes(\"".($def == 'on' ? '26' : import('tgtdo15'))."\");
\$ugtg_tdo[16] = stripslashes(\"".($def == 'on' ? 'false' : (import('tgtdo16') == 'on' ? 'true' : 'false'))."\");
\$ugtg_tdo[17] = stripslashes(\"".($def == 'on' ? 'bw' : import('tgtdo17'))."\");
\$ugtg_tdo[18] = stripslashes(\"".($def == 'on' ? 'false' : (import('tgtdo18') == 'on' ? 'true' : 'false'))."\");
    
\$ugtg_ttpo[1] = stripslashes(\"".($def == 'on' ? 'false' : (import('tgttpo1') == 'on' ? 'true' : 'false'))."\");
\$ugtg_ttpo[2] = stripslashes(\"".($def == 'on' ? 'false' : (import('tgttpo2') == 'on' ? 'true' : 'false'))."\");
\$ugtg_ttpo[3] = stripslashes(\"".($def == 'on' ? 'slide' : import('tgttpo3'))."\");
\$ugtg_ttpo[4] = stripslashes(\"".($def == 'on' ? 'inside_bottom' : import('tgttpo4'))."\");
\$ugtg_ttpo[5] = stripslashes(\"".($def == 'on' ? '0.4' : import('tgttpo5'))."\");
\$ugtg_ttpo[6] = stripslashes(\"".($def == 'on' ? '#000000' : import('tgttpo6'))."\");

\$ugtg_lo[1] = stripslashes(\"".($def == 'on' ? 'wide' : import('tglo1'))."\");
\$ugtg_lo[2] = stripslashes(\"".($def == 'on' ? 'sides' : import('tglo2'))."\");
\$ugtg_lo[3] = stripslashes(\"".($def == 'on' ? 'false' : (import('tglo3') == 'on' ? 'true' : 'false'))."\");
\$ugtg_lo[4] = stripslashes(\"".($def == 'on' ? 'true' : (import('tglo4') == 'on' ? 'true' : 'false'))."\");
\$ugtg_lo[5] = stripslashes(\"".($def == 'on' ? '10' : import('tglo5'))."\");
\$ugtg_lo[6] = stripslashes(\"".($def == 'on' ? '#FFFFFF' : import('tglo6'))."\");
\$ugtg_lo[7] = stripslashes(\"".($def == 'on' ? '0' : import('tglo7'))."\");
?>";
    WriteFullDB(PHOTOREP.$dirgal.'/ugtg_'.$numgal.INCEXT, $mettre);
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[0].'-thugtg_'.$numgal.INCEXT, constructTG($lang[0]));
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[1].'-thugtg_'.$numgal.INCEXT, constructTG($lang[1]));
}

/////////////////////////////////////////////////////////////////////////////////////////

function constructCA($lg = '') {
    global $lng, $lang, $dirgal, $numgal, $catgal, $nomgal, $strvalues;
    if ($lg == '') $lg = $lng;
    
    $def_camo   = array(1=>'160','160','center');
    $lbl_camo   = array(1=>'tile_width','tile_height','theme carousel align');
    $def_cago   = array(1=>'100%','150','');
    $lbl_cago   = array(1=>'gallery_width','gallery_min_width','gallery_background_color');
    $def_caco   = array(1=>'20','3','true','3000','right');
    $lbl_caco   = array(1=>'carousel_space_between_tiles','carousel_navigation_numtiles','carousel_autoplay','carousel_autoplay_timeout','carousel_autoplay_direction');
    $def_cano   = array(1=>'true','bottom','20');
    $lbl_cano   = array(1=>'theme_enable_navigation','theme_navigation_position','theme_navigation_margin');
    $def_catdo  = array(1=>'false','3','#F0F0F0','0','false','#8B8B8B','true','#8B8B8B','true','0.4','#000000','true','false','true','26','false','bw','false');
    $lbl_catdo  = array(1=>'tile_enable_border','tile_border_width','tile_border_color','tile_border_radius','tile_enable_outline','tile_outline_color','tile_enable_shadow','tile_shadow_color','tile_enable_overlay','tile_overlay_opacity','tile_overlay_color','tile_enable_icons','tile_show_link_icon','tile_link_newpage','tile_space_between_icons','tile_enable_image_effect','tile_image_effect_type','tile_image_effect_reverse');
    $def_cattpo = array(1=>'false','false','slide','inside_bottom','0.4','#000000');
    $lbl_cattpo = array(1=>'tile_enable_textpanel','tile_textpanel_always_on','tile_textpanel_appear_type','tile_textpanel_position','tile_textpanel_background_opacity','tile_textpanel_background_color');
    $def_calo   = array(1=>'wide','sides','false','true','10','#FFFFFF','0');
    $lbl_calo   = array(1=>'lightbox_type','lightbox_arrows_position','lightbox_arrows_inside_alwayson','lightbox_slider_image_border','lightbox_slider_image_border_width','lightbox_slider_image_border_color','lightbox_slider_image_border_radius');
        
    $construct = '
<script src="inc/unitegallery/themes/carousel/ug-theme-carousel.js"></script>
<script>
  jQuery(document).ready(function(){
    jQuery("#'.$dirgal.'").unitegallery({
      gallery_theme: "carousel",';
    if (file_exists(PHOTOREP.$dirgal.'/ugca_'.$numgal.INCEXT)) include PHOTOREP.$dirgal.'/ugca_'.$numgal.INCEXT;
    foreach ($def_camo as $key => $value) 
        if (!empty($ugca_mo[$key]) && $ugca_mo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_camo[$key].': '.(!in_array($ugca_mo[$key], $strvalues) && $ugca_mo[$key][0] != '#' ? $ugca_mo[$key] : '"'.$ugca_mo[$key].'"').',';
    foreach ($def_cago as $key => $value) 
        if (!empty($ugca_go[$key]) && $ugca_go[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_cago[$key].': '.(!in_array($ugca_go[$key], $strvalues) && substr($ugca_go[$key], -1) != '%' && $ugca_go[$key][0] != '#' ? $ugca_go[$key] : '"'.$ugca_go[$key].'"').',';
    foreach ($def_caco as $key => $value) 
        if (!empty($ugca_co[$key]) && $ugca_co[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_caco[$key].': '.(!in_array($ugca_co[$key], $strvalues) && $ugca_co[$key][0] != '#' ? $ugca_co[$key] : '"'.$ugca_co[$key].'"').',';
    foreach ($def_cano as $key => $value) 
        if (!empty($ugca_no[$key]) && $ugca_no[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_cano[$key].': '.(!in_array($ugca_no[$key], $strvalues) && $ugca_no[$key][0] != '#' ? $ugca_no[$key] : '"'.$ugca_no[$key].'"').',';
    foreach ($def_catdo as $key => $value) 
        if (!empty($ugca_tdo[$key]) && $ugca_tdo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_catdo[$key].': '.(!in_array($ugca_tdo[$key], $strvalues) && $ugca_tdo[$key][0] != '#' ? $ugca_tdo[$key] : '"'.$ugca_tdo[$key].'"').',';
    foreach ($def_cattpo as $key => $value) 
        if (!empty($ugca_ttpo[$key]) && $ugca_ttpo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_cattpo[$key].': '.(!in_array($ugca_ttpo[$key], $strvalues) && $ugca_ttpo[$key][0] != '#' ? $ugca_ttpo[$key] : '"'.$ugca_ttpo[$key].'"').',';
    foreach ($def_calo as $key => $value) 
        if (!empty($ugca_lo[$key]) && $ugca_lo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_calo[$key].': '.(!in_array($ugca_lo[$key], $strvalues) && $ugca_lo[$key][0] != '#' ? $ugca_lo[$key] : '"'.$ugca_lo[$key].'"').',';
    $construct = substr($construct, 0, -1);
    $construct .= '
      });
    });
</script>
<style> .css'.$numgal.' { display: none; margin: auto; padding: 6px 0; } </style>
<div id="'.$dirgal.'" class="css'.$numgal.'">';
    if (file_exists(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT)) {
        $dbphoto = SelectDBFieldsByField(ReadDBFields(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT), 1, 'a');
        if (!empty($dbphoto)) {
            foreach($dbphoto as $photo) $construct .= '
  <a href="'.(!empty($photo[9]) ? $photo[9] : '#z2').'">
    <img alt="'.$photo[$lg == $lang[0] ? 5 : 6].'"
      src="photo/'.$dirgal.'/vgnt/'.$photo[2].'"
      data-image="photo/'.$dirgal.'/'.$photo[2].'"
      data-description="'.$photo[$lg == $lang[0] ? 5 : 6].'"
      style="display:none;" />
  </a>';
        }
    }
    $construct .= '
</div>';

    return $construct;
}

function saveCA() {
    global $lang, $dirgal, $numgal, $def;
	$mettre = "<?php
if (stristr(\$_SERVER[\"SCRIPT_NAME\"], \"ugca_".$numgal.".inc\")) {
    header(\"location:../index.php\");
    die();
}

\$ugca_mo[1] = stripslashes(\"".($def == 'on' ? '160' : import('camo1'))."\");
\$ugca_mo[2] = stripslashes(\"".($def == 'on' ? '160' : import('camo2'))."\");
\$ugca_mo[3] = stripslashes(\"".($def == 'on' ? 'center' : import('camo3'))."\");

\$ugca_go[1] = stripslashes(\"".($def == 'on' ? '100%' : import('cago1'))."\");
\$ugca_go[2] = stripslashes(\"".($def == 'on' ? '150' : import('cago2'))."\");
\$ugca_go[3] = stripslashes(\"".($def == 'on' ? '' : import('cago3'))."\");

\$ugca_co[1] = stripslashes(\"".($def == 'on' ? '20' : import('caco1'))."\");
\$ugca_co[2] = stripslashes(\"".($def == 'on' ? '3' : import('caco2'))."\");
\$ugca_co[3] = stripslashes(\"".($def == 'on' ? 'true' : (import('caco3') == 'on' ? 'true' : 'false'))."\");
\$ugca_co[4] = stripslashes(\"".($def == 'on' ? '3000' : import('caco4'))."\");
\$ugca_co[5] = stripslashes(\"".($def == 'on' ? 'right' : import('caco5'))."\");

\$ugca_no[1] = stripslashes(\"".($def == 'on' ? 'true' : (import('cano1') == 'on' ? 'true' : 'false'))."\");
\$ugca_no[2] = stripslashes(\"".($def == 'on' ? 'bottom' : import('cano2'))."\");
\$ugca_no[3] = stripslashes(\"".($def == 'on' ? '20' : import('cano3'))."\");

\$ugca_tdo[1] = stripslashes(\"".($def == 'on' ? 'false' : (import('catdo1') == 'on' ? 'true' : 'false'))."\");
\$ugca_tdo[2] = stripslashes(\"".($def == 'on' ? '3' : import('catdo2'))."\");
\$ugca_tdo[3] = stripslashes(\"".($def == 'on' ? '#F0F0F0' : import('catdo3'))."\");
\$ugca_tdo[4] = stripslashes(\"".($def == 'on' ? '0' : import('catdo4'))."\");
\$ugca_tdo[5] = stripslashes(\"".($def == 'on' ? 'false' : (import('catdo5') == 'on' ? 'true' : 'false'))."\");
\$ugca_tdo[6] = stripslashes(\"".($def == 'on' ? '#8B8B8B' : import('catdo6'))."\");
\$ugca_tdo[7] = stripslashes(\"".($def == 'on' ? 'true' : (import('catdo7') == 'on' ? 'true' : 'false'))."\");
\$ugca_tdo[8] = stripslashes(\"".($def == 'on' ? '#8B8B8B' : import('catdo8'))."\");
\$ugca_tdo[9] = stripslashes(\"".($def == 'on' ? 'true' : (import('catdo9') == 'on' ? 'true' : 'false'))."\");
\$ugca_tdo[10] = stripslashes(\"".($def == 'on' ? '0.4' : import('catdo10'))."\");
\$ugca_tdo[11] = stripslashes(\"".($def == 'on' ? '#000000' : import('catdo11'))."\");
\$ugca_tdo[12] = stripslashes(\"".($def == 'on' ? 'true' : (import('catdo12') == 'on' ? 'true' : 'false'))."\");
\$ugca_tdo[13] = stripslashes(\"".($def == 'on' ? 'false' : (import('catdo13') == 'on' ? 'true' : 'false'))."\");
\$ugca_tdo[14] = stripslashes(\"".($def == 'on' ? 'true' : (import('catdo14') == 'on' ? 'true' : 'false'))."\");
\$ugca_tdo[15] = stripslashes(\"".($def == 'on' ? '26' : import('catdo15'))."\");
\$ugca_tdo[16] = stripslashes(\"".($def == 'on' ? 'false' : (import('catdo16') == 'on' ? 'true' : 'false'))."\");
\$ugca_tdo[17] = stripslashes(\"".($def == 'on' ? 'bw' : import('catdo17'))."\");
\$ugca_tdo[18] = stripslashes(\"".($def == 'on' ? 'false' : (import('catdo18') == 'on' ? 'true' : 'false'))."\");
    
\$ugca_ttpo[1] = stripslashes(\"".($def == 'on' ? 'false' : (import('cattpo1') == 'on' ? 'true' : 'false'))."\");
\$ugca_ttpo[2] = stripslashes(\"".($def == 'on' ? 'false' : (import('cattpo2') == 'on' ? 'true' : 'false'))."\");
\$ugca_ttpo[3] = stripslashes(\"".($def == 'on' ? 'slide' : import('cattpo3'))."\");
\$ugca_ttpo[4] = stripslashes(\"".($def == 'on' ? 'inside_bottom' : import('cattpo4'))."\");
\$ugca_ttpo[5] = stripslashes(\"".($def == 'on' ? '0.4' : import('cattpo5'))."\");
\$ugca_ttpo[6] = stripslashes(\"".($def == 'on' ? '#000000' : import('cattpo6'))."\");

\$ugca_lo[1] = stripslashes(\"".($def == 'on' ? 'wide' : import('calo1'))."\");
\$ugca_lo[2] = stripslashes(\"".($def == 'on' ? 'sides' : import('calo2'))."\");
\$ugca_lo[3] = stripslashes(\"".($def == 'on' ? 'false' : (import('calo3') == 'on' ? 'true' : 'false'))."\");
\$ugca_lo[4] = stripslashes(\"".($def == 'on' ? 'true' : (import('calo4') == 'on' ? 'true' : 'false'))."\");
\$ugca_lo[5] = stripslashes(\"".($def == 'on' ? '10' : import('calo5'))."\");
\$ugca_lo[6] = stripslashes(\"".($def == 'on' ? '#FFFFFF' : import('calo6'))."\");
\$ugca_lo[7] = stripslashes(\"".($def == 'on' ? '0' : import('calo7'))."\");
?>";
    WriteFullDB(PHOTOREP.$dirgal.'/ugca_'.$numgal.INCEXT, $mettre);
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[0].'-thugca_'.$numgal.INCEXT, constructCA($lang[0]));
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[1].'-thugca_'.$numgal.INCEXT, constructCA($lang[1]));
}

/////////////////////////////////////////////////////////////////////////////////////////

function constructDT($lg = '') {
    global $lng, $lang, $dirgal, $numgal, $catgal, $nomgal, $strvalues, $typevideo;
    if ($lg == '') $lg = $lng;
    
    $def_dttmo  = array(1=>'true','true','true','true','left','480');
    $lbl_dttmo  = array(1=>'theme_enable_fullscreen_button','theme_enable_play_button','theme_enable_hidepanel_button','theme_enable_text_panel','theme_text_align','theme_hide_panel_under_width');
    $def_dtgo   = array(1=>'900','500','400','300','false','3000','');
    $lbl_dtgo   = array(1=>'gallery_width','gallery_height','gallery_min_width','gallery_min_height','gallery_autoplay','gallery_play_interval','gallery_background_color');
    $def_dtslo  = array(1=>'fill','slide','300','true','true','pie','right','top','#FFFFFF','0.6','true','true','true','true','true','false','middle','#000000','0.4');
    $lbl_dtslo  = array(1=>'slider_scale_mode','slider_transition','slider_transition_speed','slider_enable_arrows','slider_enable_progress_indicator','slider_progress_indicator_type','slider_progress_indicator_align_hor','slider_progress_indicator_align_vert','slider_progressbar_color','slider_progressbar_opacity','slider_enable_play_button','slider_enable_fullscreen_button','slider_enable_zoom_panel','slider_controls_always_on','slider_controls_appear_ontap','slider_textpanel_enable_title','slider_textpanel_text_valign','slider_textpanel_bg_color','slider_textpanel_bg_opacity');
    $def_dttho  = array(1=>'88','50','true','true','0','#000000','0','#D9D9D9','1','#D9D9D9','0','true','#000000','0.4');
    $lbl_dttho  = array(1=>'thumb_width','thumb_height','thumb_fixed_size','thumb_border_effect','thumb_border_width','thumb_border_color','thumb_over_border_width','thumb_over_border_color','thumb_selected_border_width','thumb_selected_border_color','thumb_round_corners_radius','thumb_color_overlay_effect','thumb_overlay_color','thumb_overlay_opacity');
    $def_dtstpo = array(1=>'false','2','scroll_strip','top','','left');
    $lbl_dtstpo = array(1=>'strippanel_enable_buttons','strippanel_padding_buttons','strippanel_buttons_role','strippanel_handle_align','strippanel_background_color','strip_thumbs_align');
    
    $construct = '
<script src="inc/unitegallery/themes/default/ug-theme-default.js"></script>
<link href="inc/unitegallery/themes/default/ug-theme-default.css" rel="stylesheet" type="text/css" />
<script>
  jQuery(document).ready(function(){
    jQuery("#'.$dirgal.'").unitegallery({';
    $st = '';
    if (file_exists(PHOTOREP.$dirgal.'/ugdt_'.$numgal.INCEXT)) include PHOTOREP.$dirgal.'/ugdt_'.$numgal.INCEXT;
    foreach ($def_dttmo as $key => $value) 
        if (!empty($ugdt_tmo[$key]) && $ugdt_tmo[$key] != $value) 
            $st .= chr(10).'      '.$lbl_dttmo[$key].': '.(!in_array($ugdt_tmo[$key], $strvalues) && $ugdt_tmo[$key][0] != '#' ? $ugdt_tmo[$key] : '"'.$ugdt_tmo[$key].'"').',';
    foreach ($def_dtgo as $key => $value) 
        if (!empty($ugdt_go[$key]) && $ugdt_go[$key] != $value) 
            $st .= chr(10).'      '.$lbl_dtgo[$key].': '.(!in_array($ugdt_go[$key], $strvalues) && substr($ugdt_go[$key], -1) != '%' && $ugdt_go[$key][0] != '#' ? $ugdt_go[$key] : '"'.$ugdt_go[$key].'"').',';
    foreach ($def_dtslo as $key => $value) 
        if (!empty($ugdt_slo[$key]) && $ugdt_slo[$key] != $value) 
            $st .= chr(10).'      '.$lbl_dtslo[$key].': '.(!in_array($ugdt_slo[$key], $strvalues) && $ugdt_slo[$key][0] != '#' ? $ugdt_slo[$key] : '"'.$ugdt_slo[$key].'"').',';
    foreach ($def_dttho as $key => $value) 
        if (!empty($ugdt_tho[$key]) && $ugdt_tho[$key] != $value) 
            $st .= chr(10).'      '.$lbl_dttho[$key].': '.(!in_array($ugdt_tho[$key], $strvalues) && $ugdt_tho[$key][0] != '#' ? $ugdt_tho[$key] : '"'.$ugdt_tho[$key].'"').',';
    foreach ($def_dtstpo as $key => $value) 
        if (!empty($ugdt_stpo[$key]) && $ugdt_stpo[$key] != $value) 
            $st .= chr(10).'      '.$lbl_dtstpo[$key].': '.(!in_array($ugdt_stpo[$key], $strvalues) && $ugdt_stpo[$key][0] != '#' ? $ugdt_stpo[$key] : '"'.$ugdt_stpo[$key].'"').',';
    $construct .= empty($st) ? '' : substr($st, 0, -1);
    $construct .= '
      });
    });
</script>
<style> .css'.$numgal.' { display: none; margin: auto; padding: 6px 0; } </style>
<div id="'.$dirgal.'" class="css'.$numgal.'">';
    if (file_exists(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT)) {
        $dbphoto = SelectDBFieldsByField(ReadDBFields(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT), 1, 'a');
        if (!empty($dbphoto)) {
            foreach($dbphoto as $photo) {
                $construct .= '
  <img alt="'.$nomgal.'"';
                if (in_array($photo[10], array('youtube','vimeo','html5video','wistia'))) $construct .= '
    data-type="'.$photo[10].'"';
                if ($photo[10] != 'youtube') $construct .= '
    src="photo/'.$dirgal.'/vgnt/'.$photo[2].'"
    data-image="photo/'.$dirgal.'/'.$photo[2].'"';
                if (in_array($photo[10], $typevideo)) $construct .= '
    data-videoid="'.$photo[11].'"';
                if ($photo[10] == 'html5video') {
                    $infos      = pathinfo($photo[11]);
//                    $photoname  = $infos['dirname'].'/'.$infos['filename'];
					$photoname  = $infos['filename'];
                    $construct .= '
    data-videoogv="photo/'.$dirgal.'/'.$photoname.'.ogv"
    data-videowebm="photo/'.$dirgal.'/'.$photoname.'.webm"
    data-videomp4="photo/'.$dirgal.'/'.$photoname.'.mp4"';
                }
                $construct .= '
    data-description="'.$photo[$lg == $lang[0] ? 5 : 6].'" />';
            }
        }
    }
    $construct .= '
</div>';

    return $construct;
}

function saveDT() {
    global $lang, $dirgal, $numgal, $def;
	$mettre = "<?php
if (stristr(\$_SERVER[\"SCRIPT_NAME\"], \"ugdt_".$numgal.".inc\")) {
    header(\"location:../index.php\");
    die();
}

\$ugdt_tmo[1] = stripslashes(\"".($def == 'on' ? 'true' : (import('dttmo1') == 'on' ? 'true' : 'false'))."\");
\$ugdt_tmo[2] = stripslashes(\"".($def == 'on' ? 'true' : (import('dttmo2') == 'on' ? 'true' : 'false'))."\");
\$ugdt_tmo[3] = stripslashes(\"".($def == 'on' ? 'true' : (import('dttmo3') == 'on' ? 'true' : 'false'))."\");
\$ugdt_tmo[4] = stripslashes(\"".($def == 'on' ? 'true' : (import('dttmo4') == 'on' ? 'true' : 'false'))."\");
\$ugdt_tmo[5] = stripslashes(\"".($def == 'on' ? 'left' : import('dttmo5'))."\");
\$ugdt_tmo[6] = stripslashes(\"".($def == 'on' ? '480' : import('dttmo6'))."\");

\$ugdt_go[1] = stripslashes(\"".($def == 'on' ? '900' : import('dtgo1'))."\");
\$ugdt_go[2] = stripslashes(\"".($def == 'on' ? '500' : import('dtgo2'))."\");
\$ugdt_go[3] = stripslashes(\"".($def == 'on' ? '400' : import('dtgo3'))."\");
\$ugdt_go[4] = stripslashes(\"".($def == 'on' ? '300' : import('dtgo4'))."\");
\$ugdt_go[5] = stripslashes(\"".($def == 'on' ? 'false' : (import('dtgo5') == 'on' ? 'true' : 'false'))."\");
\$ugdt_go[6] = stripslashes(\"".($def == 'on' ? '3000' : import('dtgo6'))."\");
\$ugdt_go[7] = stripslashes(\"".($def == 'on' ? '' : import('dtgo7'))."\");

\$ugdt_slo[1] = stripslashes(\"".($def == 'on' ? 'fill' : import('dtslo1'))."\");
\$ugdt_slo[2] = stripslashes(\"".($def == 'on' ? 'slide' : import('dtslo2'))."\");
\$ugdt_slo[3] = stripslashes(\"".($def == 'on' ? '300' : import('dtslo3'))."\");
\$ugdt_slo[4] = stripslashes(\"".($def == 'on' ? 'true' : (import('dtslo4') == 'on' ? 'true' : 'false'))."\");
\$ugdt_slo[5] = stripslashes(\"".($def == 'on' ? 'true' : (import('dtslo5') == 'on' ? 'true' : 'false'))."\");
\$ugdt_slo[6] = stripslashes(\"".($def == 'on' ? 'pie' : import('dtslo6'))."\");
\$ugdt_slo[7] = stripslashes(\"".($def == 'on' ? 'right' : import('dtslo7'))."\");
\$ugdt_slo[8] = stripslashes(\"".($def == 'on' ? 'top' : import('dtslo8'))."\");
\$ugdt_slo[9] = stripslashes(\"".($def == 'on' ? '#FFFFFF' : import('dtslo9'))."\");
\$ugdt_slo[10] = stripslashes(\"".($def == 'on' ? '0.6' : import('dtslo10'))."\");
\$ugdt_slo[11] = stripslashes(\"".($def == 'on' ? 'true' : (import('dtslo11') == 'on' ? 'true' : 'false'))."\");
\$ugdt_slo[12] = stripslashes(\"".($def == 'on' ? 'true' : (import('dtslo12') == 'on' ? 'true' : 'false'))."\");
\$ugdt_slo[13] = stripslashes(\"".($def == 'on' ? 'true' : (import('dtslo13') == 'on' ? 'true' : 'false'))."\");
\$ugdt_slo[14] = stripslashes(\"".($def == 'on' ? 'true' : (import('dtslo14') == 'on' ? 'true' : 'false'))."\");
\$ugdt_slo[15] = stripslashes(\"".($def == 'on' ? 'true' : (import('dtslo15') == 'on' ? 'true' : 'false'))."\");
\$ugdt_slo[16] = stripslashes(\"".($def == 'on' ? 'false' : (import('dtslo16') == 'on' ? 'true' : 'false'))."\");
\$ugdt_slo[17] = stripslashes(\"".($def == 'on' ? 'middle' : import('dtslo17'))."\");
\$ugdt_slo[18] = stripslashes(\"".($def == 'on' ? '#000000' : import('dtslo18'))."\");
\$ugdt_slo[19] = stripslashes(\"".($def == 'on' ? '0.4' : import('dtslo19'))."\");

\$ugdt_tho[1] = stripslashes(\"".($def == 'on' ? '88' : import('dttho1'))."\");
\$ugdt_tho[2] = stripslashes(\"".($def == 'on' ? '50' : import('dttho2'))."\");
\$ugdt_tho[3] = stripslashes(\"".($def == 'on' ? 'true' : (import('dttho3') == 'on' ? 'true' : 'false'))."\");
\$ugdt_tho[4] = stripslashes(\"".($def == 'on' ? 'true' : (import('dttho4') == 'on' ? 'true' : 'false'))."\");
\$ugdt_tho[5] = stripslashes(\"".($def == 'on' ? '0' : import('dttho5'))."\");
\$ugdt_tho[6] = stripslashes(\"".($def == 'on' ? '#000000' : import('dttho6'))."\");
\$ugdt_tho[7] = stripslashes(\"".($def == 'on' ? '0' : import('dttho7'))."\");
\$ugdt_tho[8] = stripslashes(\"".($def == 'on' ? '#D9D9D9' : import('dttho8'))."\");
\$ugdt_tho[9] = stripslashes(\"".($def == 'on' ? '1' : import('dttho9'))."\");
\$ugdt_tho[10] = stripslashes(\"".($def == 'on' ? '#D9D9D9' : import('dttho10'))."\");
\$ugdt_tho[11] = stripslashes(\"".($def == 'on' ? '0' : import('dttho11'))."\");
\$ugdt_tho[12] = stripslashes(\"".($def == 'on' ? 'true' : (import('dttho12') == 'on' ? 'true' : 'false'))."\");
\$ugdt_tho[13] = stripslashes(\"".($def == 'on' ? '#000000' : import('dttho13'))."\");
\$ugdt_tho[14] = stripslashes(\"".($def == 'on' ? '0.4' : import('dttho14'))."\");

\$ugdt_stpo[1] = stripslashes(\"".($def == 'on' ? 'false' : (import('dtstpo1') == 'on' ? 'true' : 'false'))."\");
\$ugdt_stpo[2] = stripslashes(\"".($def == 'on' ? '2' : import('dtstpo2'))."\");
\$ugdt_stpo[3] = stripslashes(\"".($def == 'on' ? 'scroll_strip' : import('dtstpo3'))."\");
\$ugdt_stpo[4] = stripslashes(\"".($def == 'on' ? 'top' : import('dtstpo4'))."\");
\$ugdt_stpo[5] = stripslashes(\"".($def == 'on' ? '' : import('dtstpo5'))."\");
\$ugdt_stpo[6] = stripslashes(\"".($def == 'on' ? 'left' : import('dtstpo6'))."\");
?>";
    WriteFullDB(PHOTOREP.$dirgal.'/ugdt_'.$numgal.INCEXT, $mettre);
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[0].'-thugdt_'.$numgal.INCEXT, constructDT($lang[0]));
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[1].'-thugdt_'.$numgal.INCEXT, constructDT($lang[1]));
}

/////////////////////////////////////////////////////////////////////////////////////////

function constructCT($lg = '') {
    global $lng, $lang, $dirgal, $numgal, $catgal, $nomgal, $strvalues, $typevideo;
    if ($lg == '') $lg = $lng;
    
    $def_cttmo  = array(1=>'bottom','480');
    $lbl_cttmo  = array(1=>'theme_panel_position','theme_hide_panel_under_width');
    $def_ctgo   = array(1=>'900','500','400','300','false','3000','');
    $lbl_ctgo   = array(1=>'gallery_width','gallery_height','gallery_min_width','gallery_min_height','gallery_autoplay','gallery_play_interval','gallery_background_color');
    $def_ctslo  = array(1=>'fill','slide','300','true','true','pie','right','top','#FFFFFF','0.6','true','true','true','true','true','false','middle','#000000','0.4');
    $lbl_ctslo  = array(1=>'slider_scale_mode','slider_transition','slider_transition_speed','slider_enable_arrows','slider_enable_progress_indicator','slider_progress_indicator_type','slider_progress_indicator_align_hor','slider_progress_indicator_align_vert','slider_progressbar_color','slider_progressbar_opacity','slider_enable_play_button','slider_enable_fullscreen_button','slider_enable_zoom_panel','slider_controls_always_on','slider_controls_appear_ontap','slider_enable_text_panel','slider_textpanel_always_on','slider_textpanel_text_valign','slider_textpanel_bg_color','slider_textpanel_bg_opacity');
    $def_cttho  = array(1=>'88','50','true','true','0','#000000','0','#D9D9D9','1','#D9D9D9','0','true','#000000','0.4');
    $lbl_cttho  = array(1=>'thumb_width','thumb_height','thumb_fixed_size','thumb_border_effect','thumb_border_width','thumb_border_color','thumb_over_border_width','thumb_over_border_color','thumb_selected_border_width','thumb_selected_border_color','thumb_round_corners_radius','thumb_color_overlay_effect','thumb_overlay_color','thumb_overlay_opacity');
    $def_ctstpo = array(1=>'true','2','scroll_strip','top','','left');
    $lbl_ctstpo = array(1=>'strippanel_enable_handle','strippanel_padding_buttons','strippanel_buttons_role','strippanel_handle_align','strippanel_background_color','strip_thumbs_align');
        
    $construct = '
<script src="inc/unitegallery/themes/compact/ug-theme-compact.js"></script>
<script>
  jQuery(document).ready(function(){
    jQuery("#'.$dirgal.'").unitegallery({
      gallery_theme: "compact",';
    if (file_exists(PHOTOREP.$dirgal.'/ugct_'.$numgal.INCEXT)) include PHOTOREP.$dirgal.'/ugct_'.$numgal.INCEXT;
    foreach ($def_cttmo as $key => $value) 
        if (!empty($ugct_tmo[$key]) && $ugct_tmo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_cttmo[$key].': '.(!in_array($ugct_tmo[$key], $strvalues) && $ugct_tmo[$key][0] != '#' ? $ugct_tmo[$key] : '"'.$ugct_tmo[$key].'"').',';
    foreach ($def_ctgo as $key => $value) 
        if (!empty($ugct_go[$key]) && $ugct_go[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_ctgo[$key].': '.(!in_array($ugct_go[$key], $strvalues) && substr($ugct_go[$key], -1) != '%' && $ugct_go[$key][0] != '#' ? $ugct_go[$key] : '"'.$ugct_go[$key].'"').',';
    foreach ($def_ctslo as $key => $value) 
        if (!empty($ugct_slo[$key]) && $ugct_slo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_ctslo[$key].': '.(!in_array($ugct_slo[$key], $strvalues) && $ugct_slo[$key][0] != '#' ? $ugct_slo[$key] : '"'.$ugct_slo[$key].'"').',';
    foreach ($def_cttho as $key => $value) 
        if (!empty($ugct_tho[$key]) && $ugct_tho[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_cttho[$key].': '.(!in_array($ugct_tho[$key], $strvalues) && $ugct_tho[$key][0] != '#' ? $ugct_tho[$key] : '"'.$ugct_tho[$key].'"').',';
    foreach ($def_ctstpo as $key => $value) 
        if (!empty($ugct_stpo[$key]) && $ugct_stpo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_ctstpo[$key].': '.(!in_array($ugct_stpo[$key], $strvalues) && $ugct_stpo[$key][0] != '#' ? $ugct_stpo[$key] : '"'.$ugct_stpo[$key].'"').',';
    $construct  = substr($construct, 0, -1);
    $construct .= '
      });
    });
</script>
<style> .css'.$numgal.' { display: none; margin: auto; padding: 6px 0; } </style>
<div id="'.$dirgal.'" class="css'.$numgal.'">';
    if (file_exists(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT)) {
        $dbphoto = SelectDBFieldsByField(ReadDBFields(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT), 1, 'a');
        if (!empty($dbphoto)) {
            foreach($dbphoto as $photo) {
                $construct .= '
  <img alt="'.$nomgal.'"';
                if (in_array($photo[10], array('youtube','vimeo','html5video','wistia'))) $construct .= '
    data-type="'.$photo[10].'"';
                if ($photo[10] != 'youtube') $construct .= '
    src="photo/'.$dirgal.'/vgnt/'.$photo[2].'"
    data-image="photo/'.$dirgal.'/'.$photo[2].'"';
                if (in_array($photo[10], $typevideo)) $construct .= '
    data-videoid="'.$photo[11].'"';
                if ($photo[10] == 'html5video') {
                    $infos      = pathinfo($photo[11]);
//                    $photoname  = $infos['dirname'].'/'.$infos['filename'];
					$photoname  = $infos['filename'];
                    $construct .= '
    data-videoogv="photo/'.$dirgal.'/'.$photoname.'.ogv"
    data-videowebm="photo/'.$dirgal.'/'.$photoname.'.webm"
    data-videomp4="photo/'.$dirgal.'/'.$photoname.'.mp4"';
                }
                $construct .= '
    data-description="'.$photo[$lg == $lang[0] ? 5 : 6].'" />';
            }
        }
    }
    $construct .= '
</div>';

    return $construct;
}

function saveCT() {
    global $lang, $dirgal, $numgal, $def;
	$mettre = "<?php
if (stristr(\$_SERVER[\"SCRIPT_NAME\"], \"ugct_".$numgal.".inc\")) {
    header(\"location:../index.php\");
    die();
}

\$ugct_tmo[1] = stripslashes(\"".($def == 'on' ? 'bottom' : import('cttmo1'))."\");
\$ugct_tmo[2] = stripslashes(\"".($def == 'on' ? '480' : import('cttmo2'))."\");

\$ugct_go[1] = stripslashes(\"".($def == 'on' ? '900' : import('ctgo1'))."\");
\$ugct_go[2] = stripslashes(\"".($def == 'on' ? '500' : import('ctgo2'))."\");
\$ugct_go[3] = stripslashes(\"".($def == 'on' ? '400' : import('ctgo3'))."\");
\$ugct_go[4] = stripslashes(\"".($def == 'on' ? '300' : import('ctgo4'))."\");
\$ugct_go[5] = stripslashes(\"".($def == 'on' ? 'false' : (import('ctgo5') == 'on' ? 'true' : 'false'))."\");
\$ugct_go[6] = stripslashes(\"".($def == 'on' ? '3000' : import('ctgo6'))."\");
\$ugct_go[7] = stripslashes(\"".($def == 'on' ? '' : import('ctgo7'))."\");

\$ugct_slo[1] = stripslashes(\"".($def == 'on' ? 'fill' : import('ctslo1'))."\");
\$ugct_slo[2] = stripslashes(\"".($def == 'on' ? 'slide' : import('ctslo2'))."\");
\$ugct_slo[3] = stripslashes(\"".($def == 'on' ? '300' : import('ctslo3'))."\");
\$ugct_slo[4] = stripslashes(\"".($def == 'on' ? 'true' : (import('ctslo4') == 'on' ? 'true' : 'false'))."\");
\$ugct_slo[5] = stripslashes(\"".($def == 'on' ? 'true' : (import('ctslo5') == 'on' ? 'true' : 'false'))."\");
\$ugct_slo[6] = stripslashes(\"".($def == 'on' ? 'pie' : import('ctslo6'))."\");
\$ugct_slo[7] = stripslashes(\"".($def == 'on' ? 'right' : import('ctslo7'))."\");
\$ugct_slo[8] = stripslashes(\"".($def == 'on' ? 'top' : import('ctslo8'))."\");
\$ugct_slo[9] = stripslashes(\"".($def == 'on' ? '#FFFFFF' : import('ctslo9'))."\");
\$ugct_slo[10] = stripslashes(\"".($def == 'on' ? '0.6' : import('ctslo10'))."\");
\$ugct_slo[11] = stripslashes(\"".($def == 'on' ? 'true' : (import('ctslo11') == 'on' ? 'true' : 'false'))."\");
\$ugct_slo[12] = stripslashes(\"".($def == 'on' ? 'true' : (import('ctslo12') == 'on' ? 'true' : 'false'))."\");
\$ugct_slo[13] = stripslashes(\"".($def == 'on' ? 'true' : (import('ctslo13') == 'on' ? 'true' : 'false'))."\");
\$ugct_slo[14] = stripslashes(\"".($def == 'on' ? 'true' : (import('ctslo14') == 'on' ? 'true' : 'false'))."\");
\$ugct_slo[15] = stripslashes(\"".($def == 'on' ? 'true' : (import('ctslo15') == 'on' ? 'true' : 'false'))."\");
\$ugct_slo[16] = stripslashes(\"".($def == 'on' ? 'false' : (import('ctslo16') == 'on' ? 'true' : 'false'))."\");
\$ugct_slo[17] = stripslashes(\"".($def == 'on' ? 'middle' : import('ctslo17'))."\");
\$ugct_slo[18] = stripslashes(\"".($def == 'on' ? '#000000' : import('ctslo18'))."\");
\$ugct_slo[19] = stripslashes(\"".($def == 'on' ? '0.4' : import('ctslo19'))."\");

\$ugct_tho[1] = stripslashes(\"".($def == 'on' ? '88' : import('cttho1'))."\");
\$ugct_tho[2] = stripslashes(\"".($def == 'on' ? '50' : import('cttho2'))."\");
\$ugct_tho[3] = stripslashes(\"".($def == 'on' ? 'true' : (import('cttho3') == 'on' ? 'true' : 'false'))."\");
\$ugct_tho[4] = stripslashes(\"".($def == 'on' ? 'true' : (import('cttho4') == 'on' ? 'true' : 'false'))."\");
\$ugct_tho[5] = stripslashes(\"".($def == 'on' ? '0' : import('cttho5'))."\");
\$ugct_tho[6] = stripslashes(\"".($def == 'on' ? '#000000' : import('cttho6'))."\");
\$ugct_tho[7] = stripslashes(\"".($def == 'on' ? '0' : import('cttho7'))."\");
\$ugct_tho[8] = stripslashes(\"".($def == 'on' ? '#D9D9D9' : import('cttho8'))."\");
\$ugct_tho[9] = stripslashes(\"".($def == 'on' ? '1' : import('cttho9'))."\");
\$ugct_tho[10] = stripslashes(\"".($def == 'on' ? '#D9D9D9' : import('cttho10'))."\");
\$ugct_tho[11] = stripslashes(\"".($def == 'on' ? '0' : import('cttho11'))."\");
\$ugct_tho[12] = stripslashes(\"".($def == 'on' ? 'true' : (import('cttho12') == 'on' ? 'true' : 'false'))."\");
\$ugct_tho[13] = stripslashes(\"".($def == 'on' ? '#000000' : import('cttho13'))."\");
\$ugct_tho[14] = stripslashes(\"".($def == 'on' ? '0.4' : import('cttho14'))."\");

\$ugct_stpo[1] = stripslashes(\"".($def == 'on' ? 'true' : (import('ctstpo1') == 'on' ? 'true' : 'false'))."\");
\$ugct_stpo[2] = stripslashes(\"".($def == 'on' ? '2' : import('ctstpo2'))."\");
\$ugct_stpo[3] = stripslashes(\"".($def == 'on' ? 'scroll_strip' : import('ctstpo3'))."\");
\$ugct_stpo[4] = stripslashes(\"".($def == 'on' ? 'top' : import('ctstpo4'))."\");
\$ugct_stpo[5] = stripslashes(\"".($def == 'on' ? '' : import('ctstpo5'))."\");
\$ugct_stpo[6] = stripslashes(\"".($def == 'on' ? 'left' : import('ctstpo6'))."\");
?>";
    WriteFullDB(PHOTOREP.$dirgal.'/ugct_'.$numgal.INCEXT, $mettre);
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[0].'-thugct_'.$numgal.INCEXT, constructCT($lang[0]));
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[1].'-thugct_'.$numgal.INCEXT, constructCT($lang[1]));
}

/////////////////////////////////////////////////////////////////////////////////////////

function constructGT($lg = '') {
    global $lng, $lang, $dirgal, $numgal, $catgal, $nomgal, $strvalues, $typevideo;
    if ($lg == '') $lg = $lng;
    
    $def_gttmo  = array(1=>'right','480');
    $lbl_gttmo  = array(1=>'theme_panel_position','theme_hide_panel_under_width');
    $def_gtgo   = array(1=>'900','500','400','300','false','3000','');
    $lbl_gtgo   = array(1=>'gallery_width','gallery_height','gallery_min_width','gallery_min_height','gallery_autoplay','gallery_play_interval','gallery_background_color');
    $def_gtslo  = array(1=>'fill','slide','300','true','true','pie','right','top','#FFFFFF','0.6','true','true','true','true','true','true','true','middle','#000000','0.4');
    $lbl_gtslo  = array(1=>'slider_scale_mode','slider_transition','slider_transition_speed','slider_enable_arrows','slider_enable_progress_indicator','slider_progress_indicator_type','slider_progress_indicator_align_hor','slider_progress_indicator_align_vert','slider_progressbar_color','slider_progressbar_opacity','slider_enable_play_button','slider_enable_fullscreen_button','slider_enable_zoom_panel','slider_controls_always_on','slider_controls_appear_ontap','slider_enable_text_panel','slider_textpanel_enable_title','slider_textpanel_text_valign','slider_textpanel_bg_color','slider_textpanel_bg_opacity');
    $def_gttho  = array(1=>'88','50', 'true','true','0','#000000','1','#D9D9D9','0','true','#000000','0.4');
    $lbl_gttho  = array(1=>'thumb_width','thumb_height','thumb_fixed_size','thumb_border_effect','thumb_border_width','thumb_border_color','thumb_selected_border_width','thumb_selected_border_color','thumb_round_corners_radius','thumb_color_overlay_effect','thumb_overlay_color','thumb_overlay_opacity');
    $def_gtgpo  = array(1=>'true','middle','middle','center','left','2','10','10');
    $lbl_gtgpo  = array(1=>'gridpanel_vertical_scroll','gridpanel_grid_align','gridpanel_arrows_align_vert','gridpanel_arrows_align_hor','grid_panes_direction','grid_num_cols','grid_space_between_cols','grid_space_between_rows');
        
    $construct = '
<script src="inc/unitegallery/themes/grid/ug-theme-grid.js"></script>
<script>
  jQuery(document).ready(function(){
    jQuery("#'.$dirgal.'").unitegallery({
      gallery_theme: "grid",';
    if (file_exists(PHOTOREP.$dirgal.'/uggt_'.$numgal.INCEXT)) include PHOTOREP.$dirgal.'/uggt_'.$numgal.INCEXT;
    foreach ($def_gttmo as $key => $value) 
        if (!empty($uggt_tmo[$key]) && $uggt_tmo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_gttmo[$key].': '.(!in_array($uggt_tmo[$key], $strvalues) ? $uggt_tmo[$key] : '"'.$uggt_tmo[$key].'"').',';
    foreach ($def_gtgo as $key => $value) 
        if (!empty($uggt_go[$key]) && $uggt_go[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_gtgo[$key].': '.(!in_array($uggt_go[$key], $strvalues) && substr($uggt_go[$key], -1) != '%' && $uggt_go[$key][0] != '#' ? $uggt_go[$key] : '"'.$uggt_go[$key].'"').',';
    foreach ($def_gtslo as $key => $value) 
        if (!empty($uggt_slo[$key]) && $uggt_slo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_gtslo[$key].': '.(!in_array($uggt_slo[$key], $strvalues) && $uggt_slo[$key][0] != '#' ? $uggt_slo[$key] : '"'.$uggt_slo[$key].'"').',';
    foreach ($def_gttho as $key => $value) 
        if (!empty($uggt_tho[$key]) && $uggt_tho[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_gttho[$key].': '.(!in_array($uggt_tho[$key], $strvalues) && $uggt_tho[$key][0] != '#' ? $uggt_tho[$key] : '"'.$uggt_tho[$key].'"').',';
    foreach ($def_gtgpo as $key => $value) 
        if (!empty($uggt_gpo[$key]) && $uggt_gpo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_gtgpo[$key].': '.(!in_array($uggt_gpo[$key], $strvalues) && $uggt_gpo[$key][0] != '#' ? $uggt_gpo[$key] : '"'.$uggt_gpo[$key].'"').',';
    $construct = substr($construct, 0, -1);
    $construct .= '
      });
    });
</script>
<style> .css'.$numgal.' { display: none; margin: auto; padding: 6px 0; } </style>
<div id="'.$dirgal.'" class="css'.$numgal.'">';
    if (file_exists(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT)) {
        $dbphoto = SelectDBFieldsByField(ReadDBFields(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT), 1, 'a');
        if (!empty($dbphoto)) {
            foreach($dbphoto as $photo) {
                $construct .= '
  <img alt="'.$nomgal.'"';
				if (!isset($photo[10])) $photo[10] = '';
				if (!isset($photo[11])) $photo[11] = '';
                if (in_array($photo[10], array('youtube','vimeo','html5video','wistia'))) $construct .= '
    data-type="'.$photo[10].'"';
                if ($photo[10] != 'youtube') $construct .= '
    src="photo/'.$dirgal.'/vgnt/'.$photo[2].'"
    data-image="photo/'.$dirgal.'/'.$photo[2].'"';
                if (in_array($photo[10], $typevideo)) $construct .= '
    data-videoid="'.$photo[11].'"';
                if ($photo[10] == 'html5video') {
                    $infos      = pathinfo($photo[11]);
//                    $photoname  = $infos['dirname'].'/'.$infos['filename'];
					$photoname  = $infos['filename'];
                    $construct .= '
    data-videoogv="photo/'.$dirgal.'/'.$photoname.'.ogv"
    data-videowebm="photo/'.$dirgal.'/'.$photoname.'.webm"
    data-videomp4="photo/'.$dirgal.'/'.$photoname.'.mp4"';
                }
                $construct .= '
    data-description="'.$photo[$lg == $lang[0] ? 5 : 6].'" />';
            }
        }
    }
    $construct .= '
</div>';

    return $construct;
}

function saveGT() {
    global $lang, $dirgal, $numgal, $def;
	$mettre = "<?php
if (stristr(\$_SERVER[\"SCRIPT_NAME\"], \"uggt_".$numgal.".inc\")) {
    header(\"location:../index.php\");
    die();
}

\$uggt_tmo[1] = stripslashes(\"".($def == 'on' ? 'right' : import('gttmo1'))."\");
\$uggt_tmo[2] = stripslashes(\"".($def == 'on' ? '480' : import('gttmo2'))."\");

\$uggt_go[1] = stripslashes(\"".($def == 'on' ? '900' : import('gtgo1'))."\");
\$uggt_go[2] = stripslashes(\"".($def == 'on' ? '500' : import('gtgo2'))."\");
\$uggt_go[3] = stripslashes(\"".($def == 'on' ? '400' : import('gtgo3'))."\");
\$uggt_go[4] = stripslashes(\"".($def == 'on' ? '300' : import('gtgo4'))."\");
\$uggt_go[5] = stripslashes(\"".($def == 'on' ? 'false' : (import('gtgo5') == 'on' ? 'true' : 'false'))."\");
\$uggt_go[6] = stripslashes(\"".($def == 'on' ? '3000' : import('gtgo6'))."\");
\$uggt_go[7] = stripslashes(\"".($def == 'on' ? '' : import('gtgo7'))."\");

\$uggt_slo[1] = stripslashes(\"".($def == 'on' ? 'fill' : import('gtslo1'))."\");
\$uggt_slo[2] = stripslashes(\"".($def == 'on' ? 'slide' : import('gtslo2'))."\");
\$uggt_slo[3] = stripslashes(\"".($def == 'on' ? '300' : import('gtslo3'))."\");
\$uggt_slo[4] = stripslashes(\"".($def == 'on' ? 'true' : (import('gtslo4') == 'on' ? 'true' : 'false'))."\");
\$uggt_slo[5] = stripslashes(\"".($def == 'on' ? 'true' : (import('gtslo5') == 'on' ? 'true' : 'false'))."\");
\$uggt_slo[6] = stripslashes(\"".($def == 'on' ? 'pie' : import('gtslo6'))."\");
\$uggt_slo[7] = stripslashes(\"".($def == 'on' ? 'right' : import('gtslo7'))."\");
\$uggt_slo[8] = stripslashes(\"".($def == 'on' ? 'top' : import('gtslo8'))."\");
\$uggt_slo[9] = stripslashes(\"".($def == 'on' ? '#FFFFFF' : import('gtslo9'))."\");
\$uggt_slo[10] = stripslashes(\"".($def == 'on' ? '0.6' : import('gtslo10'))."\");
\$uggt_slo[11] = stripslashes(\"".($def == 'on' ? 'true' : (import('gtslo11') == 'on' ? 'true' : 'false'))."\");
\$uggt_slo[12] = stripslashes(\"".($def == 'on' ? 'true' : (import('gtslo12') == 'on' ? 'true' : 'false'))."\");
\$uggt_slo[13] = stripslashes(\"".($def == 'on' ? 'true' : (import('gtslo13') == 'on' ? 'true' : 'false'))."\");
\$uggt_slo[14] = stripslashes(\"".($def == 'on' ? 'true' : (import('gtslo14') == 'on' ? 'true' : 'false'))."\");
\$uggt_slo[15] = stripslashes(\"".($def == 'on' ? 'true' : (import('gtslo15') == 'on' ? 'true' : 'false'))."\");
\$uggt_slo[16] = stripslashes(\"".($def == 'on' ? 'true' : (import('gtslo16') == 'on' ? 'true' : 'false'))."\");
\$uggt_slo[17] = stripslashes(\"".($def == 'on' ? 'true' : (import('gtslo17') == 'on' ? 'true' : 'false'))."\");
\$uggt_slo[18] = stripslashes(\"".($def == 'on' ? 'middle' : import('gtslo18'))."\");
\$uggt_slo[19] = stripslashes(\"".($def == 'on' ? '#000000' : import('gtslo19'))."\");
\$uggt_slo[20] = stripslashes(\"".($def == 'on' ? '0.4' : import('gtslo20'))."\");

\$uggt_tho[1] = stripslashes(\"".($def == 'on' ? '88' : import('gttho1'))."\");
\$uggt_tho[2] = stripslashes(\"".($def == 'on' ? '50' : import('gttho2'))."\");
\$uggt_tho[3] = stripslashes(\"".($def == 'on' ? 'true' : (import('gttho3') == 'on' ? 'true' : 'false'))."\");
\$uggt_tho[4] = stripslashes(\"".($def == 'on' ? 'true' : (import('gttho4') == 'on' ? 'true' : 'false'))."\");
\$uggt_tho[5] = stripslashes(\"".($def == 'on' ? '0' : import('gttho5'))."\");
\$uggt_tho[6] = stripslashes(\"".($def == 'on' ? '#000000' : import('gttho6'))."\");
\$uggt_tho[7] = stripslashes(\"".($def == 'on' ? '1' : import('gttho7'))."\");
\$uggt_tho[8] = stripslashes(\"".($def == 'on' ? '#D9D9D9' : import('gttho8'))."\");
\$uggt_tho[9] = stripslashes(\"".($def == 'on' ? '0' : import('gttho9'))."\");
\$uggt_tho[10] = stripslashes(\"".($def == 'on' ? 'true' : (import('gttho10') == 'on' ? 'true' : 'false'))."\");
\$uggt_tho[11] = stripslashes(\"".($def == 'on' ? '#000000' : import('gttho11'))."\");
\$uggt_tho[12] = stripslashes(\"".($def == 'on' ? '0.4' : import('gttho12'))."\");

\$uggt_gpo[1] = stripslashes(\"".($def == 'on' ? 'true' : (import('gtgpo1') == 'on' ? 'true' : 'false'))."\");
\$uggt_gpo[2] = stripslashes(\"".($def == 'on' ? 'middle' : import('gtgpo2'))."\");
\$uggt_gpo[3] = stripslashes(\"".($def == 'on' ? 'middle' : import('gtgpo3'))."\");
\$uggt_gpo[4] = stripslashes(\"".($def == 'on' ? 'center' : import('gtgpo4'))."\");
\$uggt_gpo[5] = stripslashes(\"".($def == 'on' ? 'left' : import('gtgpo5'))."\");
\$uggt_gpo[6] = stripslashes(\"".($def == 'on' ? '2' : import('gtgpo6'))."\");
\$uggt_gpo[7] = stripslashes(\"".($def == 'on' ? '10' : import('gtgpo7'))."\");
\$uggt_gpo[8] = stripslashes(\"".($def == 'on' ? '10' : import('gtgpo8'))."\");
?>";
    WriteFullDB(PHOTOREP.$dirgal.'/uggt_'.$numgal.INCEXT, $mettre);
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[0].'-thuggt_'.$numgal.INCEXT, constructGT($lang[0]));
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[1].'-thuggt_'.$numgal.INCEXT, constructGT($lang[1]));
}

/////////////////////////////////////////////////////////////////////////////////////////

function constructSL($lg = '') {
    global $lng, $lang, $dirgal, $numgal, $catgal, $nomgal, $strvalues, $typevideo;
    if ($lg == '') $lg = $lng;
    
    $def_slgo   = array(1=>'900','500','400','300','false','3000','');
    $lbl_slgo   = array(1=>'gallery_width','gallery_height','gallery_min_width','gallery_min_height','gallery_autoplay','gallery_play_interval','gallery_background_color');
    $def_slslo  = array(1=>'fill','slide','300','true','true','pie','right','top','#FFFFFF','0.6','false','false','false','true','true','false','true','middle','#000000','0.4','true','0');
    $lbl_slslo  = array(1=>'slider_scale_mode','slider_transition','slider_transition_speed','slider_enable_arrows','slider_enable_progress_indicator','slider_progress_indicator_type','slider_progress_indicator_align_hor','slider_progress_indicator_align_vert','slider_progressbar_color','slider_progressbar_opacity','slider_enable_play_button','slider_enable_fullscreen_button','slider_enable_zoom_panel','slider_controls_always_on','slider_controls_appear_ontap','slider_enable_text_panel','slider_textpanel_enable_title','slider_textpanel_text_valign','slider_textpanel_bg_color','slider_textpanel_bg_opacity','slider_enable_bullets','slider_bullets_offset_vert');
        
    $construct = '
<script src="inc/unitegallery/themes/slider/ug-theme-slider.js"></script>
<script>
  jQuery(document).ready(function(){
    jQuery("#'.$dirgal.'").unitegallery({
      gallery_theme: "slider",';
    if (file_exists(PHOTOREP.$dirgal.'/ugsl_'.$numgal.INCEXT)) include PHOTOREP.$dirgal.'/ugsl_'.$numgal.INCEXT;
    foreach ($def_slgo as $key => $value) 
        if (!empty($ugsl_go[$key]) && $ugsl_go[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_slgo[$key].': '.(!in_array($ugsl_go[$key], $strvalues) && substr($ugsl_go[$key], -1) != '%' && $ugsl_go[$key][0] != '#' ? $ugsl_go[$key] : '"'.$ugsl_go[$key].'"').',';
    foreach ($def_slslo as $key => $value) 
        if (!empty($ugsl_slo[$key]) && $ugsl_slo[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_slslo[$key].': '.(!in_array($ugsl_slo[$key], $strvalues) && $ugsl_slo[$key][0] != '#' ? $ugsl_slo[$key] : '"'.$ugsl_slo[$key].'"').',';
    $construct = substr($construct, 0, -1);
    $construct .= '
      });
    });
</script>
<style> .css'.$numgal.' { display: none; margin: auto; padding: 6px 0; } </style>
<div id="'.$dirgal.'" class="css'.$numgal.'">';
    if (file_exists(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT)) {
        $dbphoto = SelectDBFieldsByField(ReadDBFields(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT), 1, 'a');
        if (!empty($dbphoto)) {
            foreach($dbphoto as $photo) {
                $construct .= '
  <img alt="'.$nomgal.'"';
                if (in_array($photo[10], array('youtube','vimeo','html5video','wistia'))) $construct .= '
    data-type="'.$photo[10].'"';
                if ($photo[10] != 'youtube') $construct .= '
    src="photo/'.$dirgal.'/vgnt/'.$photo[2].'"
    data-image="photo/'.$dirgal.'/'.$photo[2].'"';
                if (in_array($photo[10], $typevideo)) $construct .= '
    data-videoid="'.$photo[11].'"';
                if ($photo[10] == 'html5video') {
                    $infos      = pathinfo($photo[11]);
//                    $photoname  = $infos['dirname'].'/'.$infos['filename'];
					$photoname  = $infos['filename'];
                    $construct .= '
    data-videoogv="photo/'.$dirgal.'/'.$photoname.'.ogv"
    data-videowebm="photo/'.$dirgal.'/'.$photoname.'.webm"
    data-videomp4="photo/'.$dirgal.'/'.$photoname.'.mp4"';
                }
                $construct .= '
    data-description="'.$photo[$lg == $lang[0] ? 5 : 6].'" />';
            }
        }
    }
    $construct .= '
</div>';

    return $construct;
}

function saveSL() {
    global $lang, $dirgal, $numgal, $def;
	$mettre = "<?php
if (stristr(\$_SERVER[\"SCRIPT_NAME\"], \"ugsl_".$numgal.".inc\")) {
    header(\"location:../index.php\");
    die();
}

\$ugsl_go[1] = stripslashes(\"".($def == 'on' ? '900' : import('slgo1'))."\");
\$ugsl_go[2] = stripslashes(\"".($def == 'on' ? '500' : import('slgo2'))."\");
\$ugsl_go[3] = stripslashes(\"".($def == 'on' ? '400' : import('slgo3'))."\");
\$ugsl_go[4] = stripslashes(\"".($def == 'on' ? '300' : import('slgo4'))."\");
\$ugsl_go[5] = stripslashes(\"".($def == 'on' ? 'false' : (import('slgo5') == 'on' ? 'true' : 'false'))."\");
\$ugsl_go[6] = stripslashes(\"".($def == 'on' ? '3000' : import('slgo6'))."\");
\$ugsl_go[7] = stripslashes(\"".($def == 'on' ? '' : import('slgo7'))."\");

\$ugsl_slo[1] = stripslashes(\"".($def == 'on' ? 'fill' : import('slslo1'))."\");
\$ugsl_slo[2] = stripslashes(\"".($def == 'on' ? 'slide' : import('slslo2'))."\");
\$ugsl_slo[3] = stripslashes(\"".($def == 'on' ? '300' : import('slslo3'))."\");
\$ugsl_slo[4] = stripslashes(\"".($def == 'on' ? 'true' : (import('slslo4') == 'on' ? 'true' : 'false'))."\");
\$ugsl_slo[5] = stripslashes(\"".($def == 'on' ? 'true' : (import('slslo5') == 'on' ? 'true' : 'false'))."\");
\$ugsl_slo[6] = stripslashes(\"".($def == 'on' ? 'pie' : import('slslo6'))."\");
\$ugsl_slo[7] = stripslashes(\"".($def == 'on' ? 'right' : import('slslo7'))."\");
\$ugsl_slo[8] = stripslashes(\"".($def == 'on' ? 'top' : import('slslo8'))."\");
\$ugsl_slo[9] = stripslashes(\"".($def == 'on' ? '#FFFFFF' : import('slslo9'))."\");
\$ugsl_slo[10] = stripslashes(\"".($def == 'on' ? '0.6' : import('slslo10'))."\");
\$ugsl_slo[11] = stripslashes(\"".($def == 'on' ? 'false' : (import('slslo11') == 'on' ? 'true' : 'false'))."\");
\$ugsl_slo[12] = stripslashes(\"".($def == 'on' ? 'false' : (import('slslo12') == 'on' ? 'true' : 'false'))."\");
\$ugsl_slo[13] = stripslashes(\"".($def == 'on' ? 'false' : (import('slslo13') == 'on' ? 'true' : 'false'))."\");
\$ugsl_slo[14] = stripslashes(\"".($def == 'on' ? 'true' : (import('slslo14') == 'on' ? 'true' : 'false'))."\");
\$ugsl_slo[15] = stripslashes(\"".($def == 'on' ? 'true' : (import('slslo15') == 'on' ? 'true' : 'false'))."\");
\$ugsl_slo[16] = stripslashes(\"".($def == 'on' ? 'false' : (import('slslo16') == 'on' ? 'true' : 'false'))."\");
\$ugsl_slo[17] = stripslashes(\"".($def == 'on' ? 'true' : (import('slslo17') == 'on' ? 'true' : 'false'))."\");
\$ugsl_slo[18] = stripslashes(\"".($def == 'on' ? 'middle' : import('slslo18'))."\");
\$ugsl_slo[19] = stripslashes(\"".($def == 'on' ? '#000000' : import('slslo19'))."\");
\$ugsl_slo[20] = stripslashes(\"".($def == 'on' ? '0.4' : import('slslo20'))."\");
\$ugsl_slo[21] = stripslashes(\"".($def == 'on' ? 'true' : (import('slslo21') == 'on' ? 'true' : 'false'))."\");
\$ugsl_slo[22] = stripslashes(\"".($def == 'on' ? '0' : import('slslo22'))."\");
?>";
    WriteFullDB(PHOTOREP.$dirgal.'/ugsl_'.$numgal.INCEXT, $mettre);
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[0].'-thugsl_'.$numgal.INCEXT, constructSL($lang[0]));
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[1].'-thugsl_'.$numgal.INCEXT, constructSL($lang[1]));
}

/////////////////////////////////////////////////////////////////////////////////////////

function constructVG($lg = '') { //pour lng-thugvg_nogal.inc
    global $lng, $lang, $dirgal, $numgal, $catgal, $nomgal, $strvalues, $typevideo;
    if ($lg == '') $lg = $lng;
    
    $def_vgto   = array(1=>'right-thumb','false','2500');
    $lbl_vgto   = array(1=>'theme_skin','theme_autoplay','theme_disable_panel_timeout');
        
    $construct = '
<script src="inc/unitegallery/themes/video/ug-theme-video.js"></script>
<link  href="inc/unitegallery/themes/video/skin-right-no-thumb.css" rel="stylesheet" type="text/css" />
<link  href="inc/unitegallery/themes/video/skin-right-thumb.css" rel="stylesheet" type="text/css" />
<link  href="inc/unitegallery/themes/video/skin-right-title-only.css" rel="stylesheet" type="text/css" />
<script>
  jQuery(document).ready(function(){
    jQuery("#'.$dirgal.'").unitegallery({
      gallery_theme: "video",';
    if (file_exists(PHOTOREP.$dirgal.'/ugvg_'.$numgal.INCEXT)) include PHOTOREP.$dirgal.'/ugvg_'.$numgal.INCEXT;
    foreach ($def_vgto as $key => $value) 
        if (!empty($ugvg_to[$key]) && $ugvg_to[$key] != $value) 
            $construct .= chr(10).'      '.$lbl_vgto[$key].': '.(!in_array($ugvg_to[$key], $strvalues) ? $ugvg_to[$key] : '"'.$ugvg_to[$key].'"').',';
    $construct = substr($construct, 0, -1);
    $construct .= '
      });
    });
</script>
<style> .css'.$numgal.' { display: none; margin: auto; padding: 6px 0; } </style>
<div id="'.$dirgal.'" class="css'.$numgal.'">';
    if (file_exists(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT)) {
        $dbphoto = SelectDBFieldsByField(ReadDBFields(PHOTOREP.$dirgal.'/gal_'.$numgal.DBEXT), 1, 'a');
        if (!empty($dbphoto)) {
            foreach($dbphoto as $photo) {
                $construct .= '
<div data-type="'.$photo[10].'"
     data-title="'.$nomgal.'"
     data-thumb="photo/'.$dirgal.'/vgnt/'.$photo[2].'"
     data-image="photo/'.$dirgal.'/'.$photo[2].'"';
                if (in_array($photo[10], $typevideo)) $construct .= '
     data-videoid="'.$photo[11].'"';
                if ($photo[10] == 'html5video') {
                    $infos      = pathinfo($photo[11]);
//                    $photoname  = $infos['dirname'].'/'.$infos['filename'];
					$photoname  = $infos['filename'];
                    $construct .= '
     data-videoogv="photo/'.$dirgal.'/'.$photoname.'.ogv"
     data-videowebm="photo/'.$dirgal.'/'.$photoname.'.webm"
     data-videomp4="photo/'.$dirgal.'/'.$photoname.'.mp4"';
                }
                $construct .= '
     data-description="'.$photo[$lg == $lang[0] ? 5 : 6].'">
</div>';
            }
        }
    }
    $construct .= '
</div>';

    return $construct;
}

function saveVG() {
    global $lang, $dirgal, $numgal, $def;
	$mettre = "<?php
if (stristr(\$_SERVER[\"SCRIPT_NAME\"], \"ugvg_".$numgal.".inc\")) {
    header(\"location:../index.php\");
    die();
}

\$ugvg_to[1] = stripslashes(\"".($def == 'on' ? 'right-thumb' : import('vgto1'))."\");
\$ugvg_to[2] = stripslashes(\"".($def == 'on' ? 'false' : (import('vgto2') == 'on' ? 'true' : 'false'))."\");
\$ugvg_to[3] = stripslashes(\"".($def == 'on' ? '2500' : import('vgto3'))."\");
?>";
    WriteFullDB(PHOTOREP.$dirgal.'/ugvg_'.$numgal.INCEXT, $mettre);
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[0].'-thugvg_'.$numgal.INCEXT, constructVG($lang[0]));
    WriteFullDB(PHOTOREP.$dirgal.'/'.$lang[1].'-thugvg_'.$numgal.INCEXT, constructVG($lang[1]));
}

/////////////////////////////////////////////////////////////////////////////////////////

function constructUG($theme) {
    global $lng;
    switch ($theme) {
    case 'ugtc' : $construct = constructTC(); break;
    case 'ugtj' : $construct = constructTJ(); break;
    case 'ugtn' : $construct = constructTN(); break;
    case 'ugtg' : $construct = constructTG(); break;
    case 'ugca' : $construct = constructCA(); break;
    case 'ugdt' : $construct = constructDT(); break;
    case 'ugct' : $construct = constructCT(); break;
    case 'uggt' : $construct = constructGT(); break;
    case 'ugsl' : $construct = constructSL(); break;
    case 'ugvg' : $construct = constructVG(); break;
    default : $construct = '';
    }
    return $construct;
}

/////////////////////////////////////////////////////////////////////////////////////////

if ($regit == 1) { // config galerie
    $param1 = import('param1'); // Nombre maximum de miniatures par galerie
	if (empty($param1)) $param1 =1;
    $param2 = import('param2'); // Largeur maximum d'une miniature

    $param3 = import('param3'); // Hauteur maximum d'une miniature

    $param4 = import('param4'); // Choix effet pour le photorama
    $param5 = import('param5'); // Intervalle entre diapos en secondes
	if (empty($param5)) $param5 =1;
	$param6 = import('param6'); // Nombre maximum de miniatures par ligne
	$param7 = import('param7'); // Espace en pixels entre les miniatures
	if (empty($param7)) $param7 =1;
	$param8 = import('param8'); // Position des miniatures L / C / R
	$param9 = import('param9'); // Inclure dans le slishow on/off
	$param10 = import('param10'); // Galerie indépendante on/off
	$param11 = import('param11'); // Cacher le compteur des photos on/off
	$param12 = import('param12'); // Cacher le compteur des visites on/off
	$param13 = import('param13'); // Thème UniteGallery
    
    if ($param4 == 'unitegallery') {
        $param9  = '';
        $param10 = 'on';
    }
	
	$mettre = "<?php
if (stristr(\$_SERVER[\"SCRIPT_NAME\"], \"params_".$numgal.".inc\")) {
    header(\"location:../index.php\");
    die();
}

\$param1 = stripslashes(\"$param1\"); // Nombre maximum de miniatures par galerie
\$param2 = stripslashes(\"$param2\"); // Largeur maximum d'une miniature
\$param3 = stripslashes(\"$param3\"); // Hauteur maximum d'une miniature
\$param4 = stripslashes(\"$param4\"); // Choix effet pour le phototrama
\$param5 = stripslashes(\"$param5\"); // Intervalle entre diapos en secondes
\$param6 = stripslashes(\"$param6\"); // Nombre maximum de miniatures par ligne
\$param7 = stripslashes(\"$param7\"); // Espace en pixels entre les miniatures
\$param8 = stripslashes(\"$param8\"); // Position des miniatures L / C / R
\$param9 = stripslashes(\"$param9\"); // Inclure dans le slishow on/off
\$param10 = stripslashes(\"$param10\"); // Galerie indépendante on/off
\$param11 = stripslashes(\"$param11\"); // Cacher le compteur des photos on/off
\$param12 = stripslashes(\"$param12\"); // Cacher le compteur des visites on/off
\$param13 = stripslashes(\"$param13\"); // Thème UniteGallery

?>";
    WriteFullDB(PHOTOREP.$dirgal.'/params_'.$numgal.INCEXT, $mettre);

    if ($param4 == 'unitegallery') {
        switch ($param13) {
        case 'ugtc' : saveTC(); break;
        case 'ugtj' : saveTJ(); break;
        case 'ugtn' : saveTN(); break;
        case 'ugtg' : saveTG(); break;
        case 'ugca' : saveCA(); break;
        case 'ugdt' : saveDT(); break;
        case 'ugct' : saveCT(); break;
        case 'uggt' : saveGT(); break;
        case 'ugsl' : saveSL(); break;
        case 'ugvg' : saveVG(); break;
        }
    }
    
	$act = 1;
	$messageinfo = $admin[1822];
}

if ($regit == 2) { // gérer les photos
    $wmax    = @intval($param2);
    $hmax    = @intval($param3);
	$wmax    = @$vmax > 0 ? @$vmax : 100;
	$hmax    = $hmax > 0 ? $hmax : 75;
    $nextid  = 1;
/*
max_file_uploads=20
$mfu      = @ini_get('max_file_uploads');

upload_max_filesize=256M
$maxsizefiles = @ini_get('upload_max_filesize');

post_max_size=256M
$maxpostsize = @ini_get('post_max_size');
*/
    $filecom = PHOTOREP.$dirgal.'/'.$dirgal.DBEXT;
    if (is_file($filecom)) {
        $dbphoto = ReadDBFields($filecom);
        if (!empty($dbphoto)) {
            foreach ($dbphoto as $photo) $nextid = max($nextid, $photo[0]);
            $nextid++;
        }
    }
   if (file_exists(PHOTOREP.$dirgal.'/params_'.$numgal.INCEXT))
		include PHOTOREP.$dirgal.'/params_'.$numgal.INCEXT;	
	$is_video = in_array($param13, $galleryvideos);
	$nbf = min($mfu, count(@$_FILES['new_photo']['name']));
	$totalfiles_size =0;
	// limite de la taille des fichiers en upload des fichiers maxsizefiles
	// limite de la taille des zones saisies y compris les fichiers maxpostsizes
	for ($i = 0; $i < $nbf; $i++) 
	{
		$filename = @$_FILES['new_photo']['name'][$i];
		$tmpname  = @$_FILES['new_photo']['tmp_name'][$i];
		$file_type = @$_FILES['new_photo']['type'][$i];
		$file_size = @$_FILES['new_photo']['size'][$i];
		$totalfiles_size = $totalfiles_size + $file_size;
		if ($filename != "" && is_uploaded_file($tmpname)) {
			$filename = trim($filename);
			$filename = str_replace(' ','_', $filename);
			$filename = str_replace('à','a', $filename);
			$filename = preg_replace('![éèê]!','e', $filename);
			$filename = preg_replace('![îï]!','i', $filename);
			$filename = preg_replace('![^-a-zA-Z0-9_\.]!', '', $filename);
			$uperror = false;

			if (!preg_match("`^([-a-z0-9_]+)(\.)([a-z0-9]{2,4})$`i", $filename)) { // controle nom de fichier valide
				$messageinfurgent = $admin[1829];
				$uperror = true;
			}
			if (is_file(PHOTOREP.$dirgal.'/'.$filename)) { // controle présence fichier
				$messageinfurgent = $admin[1828];
				$uperror = true; 
			}
// si galerie est unite gallery, videos et photos sont possibles
			$file_name_temp = explode(".", $filename);
			$extension = end($file_name_temp);		
			if (in_array($file_type, $video_types) 
					   && in_array($extension, $allowed_extensions))
			{
				if ($is_video)
				{
				   if (in_array($file_type, $video_types) 
					   && in_array($extension, $allowed_extensions))
					{
						if (!$uperror)
						{
							if (move_uploaded_file($tmpname, PHOTOREP.$dirgal.'/'.$filename))
							{
								SetChmod(PHOTOREP.$dirgal.'/'.$filename);
							}
						}
					}
				}
			}
		   else
			{
				if (!IsImage(pathinfo($filename, PATHINFO_EXTENSION))) {
					$messageinfurgent = $admin[1830];
					$uperror = true; 
				}
				$typimg = getimagesize($tmpname);
				if(!$uperror) { // UPLOAD			
					list($wimgtmp, $himgtmp, $typimg) = getimagesize($tmpname);
					$headers = exif_read_data($tmpname, 0, true);
					$wimg = $wimgtmp;
					$himg = $himgtmp;
					$orientationphoto = '';
					if (isset($headers['IFD0']['Orientation']))
					{
							$orientationphoto = $headers['IFD0']['Orientation'];
							if ($headers['IFD0']['Orientation'] == '6')
							{
								$wimg = $himgtmp;
								$himg = $wimgtmp;
							}
							if ($headers['IFD0']['Orientation'] == '8')
							{
								$wimg = $himgtmp;
								$himg = $wimgtmp;
							}
					}
					if ($wimg > $himg) {
						$wmin = $wmax;
						$hmin = ceil($himg * $wmax / $wimg);
						if ($hmin > $hmax) {
							$wmin = ceil($wmin * $hmax / $hmin);
							$hmin = $hmax;
						}
					} else 
					{
						if (version_compare(PHP_VERSION, '8.1.0') >= 0 && version_compare(PHP_VERSION, '8.2.0') < 0) {
						  $wimg = 630;
						  $himg = 420;						  
						}						
						$hmin = $hmax;        
						$wmin = ceil($wimg * $hmax / $himg);
						if ($wmin > $wmax) {
							$hmin = ceil($hmin * $wmax / $wmin);
							$wmin = $wmax;
						}
					}
					if (@$param4 == 'unitegallery') {
						$wmin = intval(floor($wimg * 0.30));
						$hmin = intval(floor($himg * 0.30));
					}
					if (move_uploaded_file($tmpname, PHOTOREP.$dirgal.'/'.$filename)) {
						$headers = exif_read_data(PHOTOREP.$dirgal.'/'.$filename, 0, true);	
						$rotationimage = 0;
						$orientionimage = 1;
						if (isset($headers['IFD0']))
						{
							if (isset($headers['IFD0']['Orientation']))
							{
								if ($headers['IFD0']['Orientation'] == '1') // l'orientation correcte, aucun ajustement n'est nécessaire
								{
									$rotationimage = 0; 
								}
								if ($headers['IFD0']['Orientation'] == '2') // en miroir: l'image a été retournée vers l'avant.
								{
									$rotationimage = 0;
								}
								
								if ($headers['IFD0']['Orientation'] == '3') // 180° l'image est à l'envers.
								{
									$rotationimage = 180;
								}
								if ($headers['IFD0']['Orientation'] == '4') // 180° en miroir: l'image a été retournée vers l'avant et est à l'envers.
								{
									$rotationimage = 270;
								}
								if ($headers['IFD0']['Orientation'] == '5') // 90° l'image a été retournée de retour et est sur son côté.
								{
									$rotationimage = 270;
								}
								if ($headers['IFD0']['Orientation'] == '6') // en miroir: l'image est sur son côté
								{
									$rotationimage = 270;
								}
								if ($headers['IFD0']['Orientation'] == '7') // 270° l'image a été retournée de retour à l'avant et est sur sa face cachée
								{
									$rotationimage = 270;
								}
								if ($headers['IFD0']['Orientation'] == '8') // 270 ° en miroir: l'image est sur son côté.
								{
									$rotationimage = 90;
								}
							}
						}						
						SetChmod(PHOTOREP.$dirgal.'/'.$filename);
						// création des miniatures
						switch($typimg) {

						case 1 :
							$hfic = ImageCreateFromGif(PHOTOREP.$dirgal.'/'.$filename);
							$hvig = @ImageCreateTrueColor($wmin, $hmin);
							$cr   = @ImageCopyResampled($hvig, $hfic, 0, 0, 0, 0, $wmin, $hmin, $wimg, $himg);
							$rotate = imagerotate($hfic, $rotationimage, 0);
							@ImageGif($rotate, PHOTOREP.$dirmin.'/'.$filename);
							SetChmod(PHOTOREP.$dirmin.'/'.$filename);
							break;

						case 2 : // JPG
							$hfic = ImageCreateFromJpeg(PHOTOREP.$dirgal.'/'.$filename);
							$hvig = @ImageCreateTrueColor($wmin, $hmin);
							$cr   = @ImageCopyResampled($hvig, $hfic, 0, 0, 0, 0, $wmin, $hmin, $wimg, $himg);
							$rotate = imagerotate($hfic, $rotationimage, 0);						
							ImageJpeg($rotate, PHOTOREP.$dirmin.'/'.$filename, 75);
							SetChmod(PHOTOREP.$dirmin.'/'.$filename);
							break;
						case 3 : // PNG
							$hfic = ImageCreateFromPng(PHOTOREP.$dirgal.'/'.$filename);
							$hvig = @ImageCreateTrueColor($wmin, $hmin);
							$cr = @ImageCopyResampled($hvig, $hfic, 0, 0, 0, 0, $wmin, $hmin, $wimg, $himg);
							$rotate = imagerotate($hfic, $rotationimage, 0);	
							@ImagePng($rotate, PHOTOREP.$dirmin.'/'.$filename);
							SetChmod(PHOTOREP.$dirmin.'/'.$filename);
							break;
						case 18 : // webp*

							$hfic = imagecreatefromwebp(PHOTOREP.$dirgal.'/'.$filename);
							$hvig = @ImageCreateTrueColor($wmin, $hmin);
							$cr = @ImageCopyResampled($hvig, $hfic, 0, 0, 0, 0, $wmin, $hmin, $wimg, $himg);
							$rotate = imagerotate($hfic, $rotationimage, 0);	
							@imagewebp($rotate, PHOTOREP.$dirmin.'/'.$filename);
							SetChmod(PHOTOREP.$dirmin.'/'.$filename);

							break;
						case 19 : // avif
						    if (version_compare(PHP_VERSION, '8.1.0') >= 0 && version_compare(PHP_VERSION, '8.2.0') < 0) {
								$hfic = imagecreatefromavif(PHOTOREP.$dirgal.'/'.$filename);
								$hvig = @ImageCreateTrueColor($wmin, $hmin);
								$cr = @ImageCopyResampled($hvig, $hfic, 0, 0, 0, 0, $wmin, $hmin, $wimg, $himg);
							$rotate = imagerotate($hfic, $rotationimage, 0);	
							@imageavif($rotate, PHOTOREP.$dirmin.'/'.$filename);
							SetChmod(PHOTOREP.$dirmin.'/'.$filename);
							}
							break;
						default ;
							echo 'erreur: <br/>';
							break;
						}
						if (isset($hfic)) @imagedestroy($hfic);
						if (isset($hvig)) @imagedestroy($hvig);
						unset($hfic, $hvig);
						// ajout dans le fichier commentaires
						$datas   = array($nextid, 'a', $filename, $wmin, $hmin, '', '', $wimg, $himg, '', '', '', $orientationphoto, $rotationimage);
						AppendDBFields($filecom, $datas);
						$nextid++;
						$messageinfo = $admin[1827];
						$messageinfurgent = '';
					} // fin de Upload
				} // Fin de control erreur
			} // fin du else et traitement image
		} // fichier non vide
	} // fin de la boucle des fichiers
} // fin de regit 2
if ($regit == 3) { // ajout de commentaires
    $dbids   = explode(' ', trim(import('ids3')));
    $dbphoto = ReadDBFields(PHOTOREP.$dirgal.'/'.$dirgal.DBEXT);
    for ($i = 0; $i < count($dbids); $i++) {
		$ii = $dbids[$i];
        $jj = 0;
        for ($j = 0; $j < count($dbphoto); $j++) if ($ii == $dbphoto[$j][0]) { $jj = $j; break; }
		if (isset($_POST['comment1_'.$ii]) && $_POST['comment1_'.$ii] != '') $dbphoto[$jj][5] = $_POST['comment1_'.$ii];
        if (isset($_POST['comment2_'.$ii]) && $_POST['comment2_'.$ii] != '') $dbphoto[$jj][6] = $_POST['comment2_'.$ii];
//       if (isset($_POST['imglink_'.$ii]) && $_POST['imglink_'.$ii] != '') $dbphoto[$jj][9] = $_POST['imglink_'.$ii];
		if (isset($_POST['nordre_'.$ii]) && $_POST['nordre_'.$ii] != '') $dbphoto[$jj][9] = $_POST['nordre_'.$ii];
        if (isset($_POST['typevideo_'.$ii]) && $_POST['typevideo_'.$ii] != '') $dbphoto[$jj][10] = $_POST['typevideo_'.$ii];
        if (isset($_POST['videolink_'.$ii]) && $_POST['videolink_'.$ii] != '') $dbphoto[$jj][11] = $_POST['videolink_'.$ii];

    }
    WriteDBFields(PHOTOREP.$dirgal.'/'.$dirgal.DBEXT, $dbphoto);
	unset($dbids, $dbphoto);
}

if ($regit == 4) { // reconstruire les miniatures
	if (file_exists(PHOTOREP.$dirgal.'/params_'.$numgal.INCEXT))
		include PHOTOREP.$dirgal.'/params_'.$numgal.INCEXT;
    $wmax    = intval($param2);
    $hmax    = intval($param3);
	$wmax    = $wmax > 0 ? $wmax : 100;
	$hmax    = $hmax > 0 ? $hmax : 75;
	$dbwork  = $dbw = array();
	$bad     = array('.', '..', 'vgnt', 'index.php', $dirgal.DBEXT, 'params_'.$numgal.INCEXT, 
      'ugtc_'.$numgal.INCEXT, 'ugtj_'.$numgal.INCEXT, 'ugtn_'.$numgal.INCEXT, 'ugtg_'.$numgal.INCEXT, 'ugca_'.$numgal.INCEXT, 
      'ugdt_'.$numgal.INCEXT, 'ugct_'.$numgal.INCEXT, 'uggt_'.$numgal.INCEXT, 'ugsl_'.$numgal.INCEXT, 'ugvg_'.$numgal.INCEXT, 
      'thugtc_'.$numgal.INCEXT, 'thugtj_'.$numgal.INCEXT, 'thugtn_'.$numgal.INCEXT, 'thugtg_'.$numgal.INCEXT, 'thugca_'.$numgal.INCEXT, 
      'thugdt_'.$numgal.INCEXT, 'thugct_'.$numgal.INCEXT, 'thuggt_'.$numgal.INCEXT, 'thugsl_'.$numgal.INCEXT, 'thugvg_'.$numgal.INCEXT
    );
	$dbphoto = scandir(PHOTOREP.$dirgal);
	foreach ($dbphoto as $photo) {
		if (in_array($photo, $bad)) continue;
		if (IsImage(pathinfo($photo, PATHINFO_EXTENSION))) $dbwork[] = $photo;
	}
	$dbvgnts = ReadDBFields(PHOTOREP.$dirgal.'/'.$dirgal.DBEXT);
    $n       = 0;
	foreach ($dbvgnts as $photo) {
		$dbw[] = $photo[2];
		$n     = max($n, $photo[0]);
	}
	$dbphoto = array_diff($dbwork, $dbw);
	foreach ($dbphoto as $photo) $dbvgnts[] = array(++$n, 'a', $photo, $wmax, $hmax, '', '', '', '', '', '', '0');
	$n = 0;
	foreach ($dbvgnts as $photo) {
		$filename = $photo[2];
	/*
		$headers = exif_read_data(PHOTOREP.$dirgal.'/'.$filename, 0, true);
		echo '<br />';
		var_dump($headers);
		echo '<br />';
	*/
		list($wimgtmp, $himgtmp, $typimg) = getimagesize(PHOTOREP.$dirgal.'/'.$filename);
		$wimg = $wimgtmp;
		$himg = $himgtmp;
		$orientationphoto = 1;
		$rotationimage = 0;
		if (!isset($photo[12]))
		{
			$photo[12] = $rotationimage;
		}
		if (!isset($photo[13]))
		{
			$photo[13] = $photo[12];
		}
		if ($photo[12] == '' ) $photo[12] = $orientationphoto;
		if ($photo[13] == '') $photo[13] = $rotationimage;
		if ($photo[13] != $rotationimage)
		{
			$wimg = $himgtmp;
			$himg = $wimgtmp;
			if ($photo[13] == 90)
				$photo[12] = 8;
			if ($photo[13] == 180)
				$photo[12] = 6;
		}

		$orientationphoto = $photo[12];
		$rotationimage = $photo[13];
		// si type image est avif (19), wimg et himg = 0 pour une version Php < 8.2.0
		if ($typimg == 19 && (version_compare(PHP_VERSION, '8.0.0') >= 0 && version_compare(PHP_VERSION, '8.2.0') < 0)) {
		  $wimg = 630;
		  $himg = 420;						  
		}	
		if (intval($wimg) <= 0) $wimg = $wmax;
		if (intval($himg) <= 0) $himg = $hmax;
		if ($wimg > $himg) {
			$wmin = $wmax;
			$hmin = ceil($himg * $wmax / $wimg);
			if ($hmin > $hmax) {
				$wmin = ceil($wmin * $hmax / $hmin);
				$hmin = $hmax;
			}
		} else {
			if (version_compare(PHP_VERSION, '8.1.0') >= 0 && version_compare(PHP_VERSION, '8.2.0') < 0) {
			  $wimg = 630;
			  $himg = 420;						  
			}			
			$hmin = $hmax;        
			$wmin = ceil($wimg * $hmax / $himg);
			if ($wmin > $wmax) {
				$hmin = ceil($hmin * $wmax / $wmin);
				$wmin = $wmax;
			}
		}
		if ($param4 == 'unitegallery') {
			$wmin = intval(floor($wimg * 0.30));
			$hmin = intval(floor($himg * 0.30));
		}
		if (file_exists(PHOTOREP.$dirmin.'/'.$filename)) unlink(PHOTOREP.$dirmin.'/'.$filename);

		// création des miniatures
		switch($typimg) {

		case 1 :
			$hfic = ImageCreateFromGif(PHOTOREP.$dirgal.'/'.$filename);
			$hvig = @ImageCreateTrueColor($wmin, $hmin);
			$cr   = @ImageCopyResampled($hvig, $hfic, 0, 0, 0, 0, $wmin, $hmin, $wimg, $himg);
			$rotate = imagerotate($hfic, $rotationimage, 0);
			@ImageGif($rotate, PHOTOREP.$dirmin.'/'.$filename);
			SetChmod(PHOTOREP.$dirmin.'/'.$filename);
			break;

		case 2 : // JPG
			$hfic = ImageCreateFromJpeg(PHOTOREP.$dirgal.'/'.$filename);
			$hvig = @ImageCreateTrueColor($wmin, $hmin);
			$cr   = @ImageCopyResampled($hvig, $hfic, 0, 0, 0, 0, $wmin, $hmin, $wimg, $himg);
			$rotate = imagerotate($hfic, $rotationimage, 0);						
			ImageJpeg($rotate, PHOTOREP.$dirmin.'/'.$filename, 75);
			SetChmod(PHOTOREP.$dirmin.'/'.$filename);
			break;
		case 3 : // PNG
			$hfic = ImageCreateFromPng(PHOTOREP.$dirgal.'/'.$filename);
			$hvig = @ImageCreateTrueColor($wmin, $hmin);
			$cr = @ImageCopyResampled($hvig, $hfic, 0, 0, 0, 0, $wmin, $hmin, $wimg, $himg);
			$rotate = imagerotate($hfic, $rotationimage, 0);	
			@ImagePng($rotate, PHOTOREP.$dirmin.'/'.$filename);
			SetChmod(PHOTOREP.$dirmin.'/'.$filename);
			break;
		case 18 : // webp*

			$hfic = imagecreatefromwebp(PHOTOREP.$dirgal.'/'.$filename);
			$hvig = @ImageCreateTrueColor($wmin, $hmin);
			$cr = @ImageCopyResampled($hvig, $hfic, 0, 0, 0, 0, $wmin, $hmin, $wimg, $himg);
			$rotate = imagerotate($hfic, $rotationimage, 0);	
			@imagewebp($rotate, PHOTOREP.$dirmin.'/'.$filename);
			SetChmod(PHOTOREP.$dirmin.'/'.$filename);

			break;
		case 19 : // avif
			if (version_compare(PHP_VERSION, '8.1.0') >= 0 && version_compare(PHP_VERSION, '8.2.0') < 0) {
				$hfic = imagecreatefromavif(PHOTOREP.$dirgal.'/'.$filename);
				$hvig = @ImageCreateTrueColor($wmin, $hmin);
				$cr = @ImageCopyResampled($hvig, $hfic, 0, 0, 0, 0, $wmin, $hmin, $wimg, $himg);
			$rotate = imagerotate($hfic, $rotationimage, 0);	
			@imageavif($rotate, PHOTOREP.$dirmin.'/'.$filename);
			SetChmod(PHOTOREP.$dirmin.'/'.$filename);
			}
			break;
		default ;
			echo 'erreur: <br/>';
			break;
		}
		if (isset($hfic)) @imagedestroy($hfic);
		if (isset($hvig)) @imagedestroy($hvig);
		unset($hfic, $hvig);
		$photo[3]    = $wmin;
		$photo[4]    = $hmin;
		$photo[7]    = $wimg;
		$photo[8]    = $himg;
		$photo[12]   = $orientationphoto;
		$photo[13]   = $rotationimage;
		$dbvgnts[$n] = $photo;
		$n++;
	}
	WriteDBFields(PHOTOREP.$dirgal.'/'.$dirgal.DBEXT, $dbvgnts);
	$messageinfo = $admin[1823];
}

################################################################################
function initGallery($dirgal) {
    global $numgal, $dirmin;
    if (!is_dir(PHOTOREP.$dirgal)) {
        if (MakeDir(PHOTOREP.$dirgal)) {
            @copy(CHEMIN.'data/index.php', PHOTOREP.$dirgal.'/index.php');
            SetChmod(PHOTOREP.$dirgal.'/index.php');
            WriteFullDB(PHOTOREP.$dirgal.'/'.$dirgal.DBEXT, '');
            SetChmod(PHOTOREP.$dirgal.'/'.$dirgal.DBEXT);
            $mettre = "<?php
if (stristr(\$_SERVER[\"SCRIPT_NAME\"], \"params_".$numgal.".inc\")) {
    header(\"location:../index.php\");
    die();
}

\$param1 = stripslashes(\"10\"); // Nombre maximum de miniatures par galerie
\$param2 = stripslashes(\"100\"); // Largeur maximum d'une miniature
\$param3 = stripslashes(\"75\"); // Hauteur maximum d'une miniature
\$param4 = stripslashes(\"lytebox\"); // Choix effet pour le photorama
\$param5 = stripslashes(\"4\"); // Intervalle entre diapos en secondes
\$param6 = stripslashes(\"5\"); // Nombre maximum de miniatures par ligne
\$param7 = stripslashes(\"20\"); // Espace en pixels entre les miniatures
\$param8 = stripslashes(\"L\"); // Position des miniatures L / C / R
\$param9 = stripslashes(\"\"); // Inclure dans le slideshow on/off
\$param10 = stripslashes(\"\"); // Galerie indépendante on/off
\$param11 = stripslashes(\"\"); // Cacher le compteur des photos on/off
\$param12 = stripslashes(\"\"); // Cacher le compteur des visites on/off
\$param13 = stripslashes(\"\"); // Thème UniteGallery

?>";
	
            WriteFullDB(PHOTOREP.$dirgal.'/params_'.$numgal.INCEXT, $mettre);
            SetChmod(PHOTOREP.$dirgal.'/params_'.$numgal.INCEXT);
        }
    }
    if (!is_dir(PHOTOREP.$dirmin)) {
        if (MakeDir(PHOTOREP.$dirmin)) {
            @copy(CHEMIN.'data/index.php', PHOTOREP.$dirmin.'/index.php');
            SetChmod(PHOTOREP.$dirmin.'/index.php');
        }
    }
}

function GetSelector($padding, $label, $inputname, $color) { // Palette de couleurs
    return '
        <tr style="text-align:left;">
          <td class="align-middle" style="padding:4px 0 2px '.$padding.'px;width:55%;">&bull; '.$label.'</td>
		  <td class="align-middle" style="padding:4px 0 2px 8px;">
			<div>
			  <input class="form-control" id="'.$inputname.'" name="'.$inputname.'" type="text" size="6" value="'.$color.'" 
                style="font:14px Arial;text-align:center;width:92px;height:29px;border:1px solid #000;border-radius:4px 0 0 4px;float:left;" />
			  <div style="float:left;width:40px;height:29px;vertical-align:top;border:1px solid #000;border-left:none;background-color:'.$color.';padding:-4px 0 4px 0;"></div>
			  <div id="'.$inputname.'div" style="float:left;width:40px;height:29px;border:1px solid #000;border-left:none;background-color:'.$color.';border-radius:0 4px 4px 0;">&nbsp;</div>
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
			});".EndJavascript().'
          </td>
        </tr>';
}

function SelectText($padding, $label, $name, $value, &$textarray) {
    $out = '
        <tr style="text-align:left;">
          <td class="align-middle" style="padding-left:'.$padding.'px;width:65%;">&bull; '.$label.'</td>
          <td class="align-middle text-left" style="padding:0 0 0 8px;">
            <select class="form-control w-95" name="'.$name.'">';
    foreach($textarray as $key => $text) {
        $out .= '
              <option value="'.$key.'"'.Selected($value == $key).'>'.$text.'</option>';
    }
    $out .= '
            </select>
          </td>
        </tr>';
    return $out;
}

function InputTexte($padding, $label, $name, $value, $size) {
    return '
        <tr style="text-align:left;">
          <td class="align-middle" style="padding-left:'.$padding.'px;width:65%;">&bull; '.$label.'</td>
          <td class="align-middle text-left" style="padding:0 0 0 4px;">
            <input name="'.$name.'" class="form-control w-95 texte" type="text" size="'.$size.'" value="'.$value.'" />
          </td>
        </tr>';
}

function InputNumber($padding, $label, $name, $min, $max, $value, $step=1) {
    return '
        <tr style="text-align:left;">
          <td class="align-middle" style="padding:0 0 0 '.$padding.'px;width:65%;">&bull; '.$label.'</td>
          <td class="align-middle text-left" style="padding:0 0 0 4px;">
            <input name="'.$name.'" class="form-control w-95 texte" type="number" min="'.$min.'" max="'.$max.'" step="'.$step.'" value="'.$value.'" />
          </td>
        </tr>';
}

function InputCheckbox($padding, $label, $name, $value) {
    return '
        <tr style="text-align:left;">
          <td class="align-middle" style="padding-left:'.$padding.'px;width:65%;">
            <label for="'.$name.'">&bull; '.$label.'</label>
          </td>
          <td class="align-middle" style="padding:2px 0 3px 14px;">
            <input id="'.$name.'" type="checkbox" name="'.$name.'" '.Checked($value == 'on').' />
          </td>
        </tr>';
}

################################################################################
// config galeries
if($act == 1) {
    $headinc .= '
<script>
  function cacheallug() {
    cache(\'ugtc\'); cache(\'ugtj\'); cache(\'ugtn\'); cache(\'ugtg\'); cache(\'ugca\');
    cache(\'ugdt\'); cache(\'ugct\'); cache(\'uggt\'); cache(\'ugsl\'); cache(\'ugvg\');
    cache(\'ugconstruct\');
  }
</script>

<link rel="stylesheet" media="screen" type="text/css" href="../inc/colorpicker/css/colorpicker.css" /> 
<script src="../inc/colorpicker/js/colorpicker.js"></script>
';
    
	$topmess  = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[638]);
    include CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($admin[638], '100%');
    initGallery($dirgal);
	if (file_exists(PHOTOREP.$dirgal.'/params_'.$numgal.INCEXT)) {
		include PHOTOREP.$dirgal.'/params_'.$numgal.INCEXT;
        if (file_exists(PHOTOREP.$dirgal.'/'.$param13.'_'.$numgal.INCEXT))
            include PHOTOREP.$dirgal.'/'.$param13.'_'.$numgal.INCEXT;
    }
// Sélection galerie faite
    echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=1042&mnuid=937&tconfig=0#z2').$admin[331].'</p>
<hr />'.info_gd().'<hr />
<div style="max-width:680px;margin:auto auto 12px auto;">
  <fieldset>'.legend($admin[132]).'
    <form name="galsel1" action="'.$baseurl.'&amp;act='.$act.'&amp;sel='.$sel.'" method="POST">
      <div class="table-responsive-md">
      <table class="table table-bordered w-80 m-auto">
        <tr>
		  <td style="text-align:center;">
            <select class="form-control" name="numgal" style="width:300px;" onchange="submit();">';
    $lstgal = ReadAuthorizedIndex(DBPHOTO, $myname, $grpcol, $fctwri);
    sort($lstgal);
    foreach ($lstgal as $gal) {
		$cat  = $gal[$lng == $lang[0] ? 0 : 1];
		$pos  = strpos($cat, '-->');
		$cat  = substr($cat, ($pos !== false ? $pos + 3 : 0));
		$cat  = empty($cat) ? '' : $cat.' => ';
		$name = $gal[$lng == $lang[0] ? 2 : 3];
		$pos  = strpos($name, '-->');
		$name = substr($name, ($pos !== false ? $pos + 3 : 0));
		echo '
              <option value="'.$gal[4].'"'.Selected($numgal == $gal[4]).'>'.$gal[4].' : '.$cat.$name.'</option>';
    }
    echo '
            </select>
          </td>
		</tr>
		<noscript>
        <tr>
		  <td style="text-align:center;">'.SubmitButton($admin[519]).'</td>
		</tr>
		</noscript>
      </table>
      </div>
    </form>
  </fieldset>
</div>
<div style="max-width:680px;text-align:center;margin:auto;">
  <fieldset>
    <form name="galvgnt" action="'.$baseurl.'" method="POST">
      <input type="hidden" name="act" value="3" />
	  <input type="hidden" name="messageinfo" value="'.$messageinfo.'" /> 
	  <input type="hidden" name="messageinfurgent" value="'.$messageinfurgent.'" />
	  <input type="hidden" name="numgal" value="'.$numgal.'" />
      <div class="quest" style="padding:2px 20px;">
        <div style="float:right">'.SubmitButton(str_replace('Admin - ', '', $admin[133]), '', '', $imgdroite).'</div>
        <div style="clear:both"></div>
      </div>
    </form>
  </fieldset>
</div>
<div style="max-width:680px;margin: 0 auto;">';
if ($messageinfurgent !='')
{
	echo '
	<div class="alert alert-dark" role="alert">
	</div>';
}
if ($messageinfo !='')
{
	echo '
	<div class="alert alert-primary" role="alert">
	  '.$messageinfo.'
	</div>';
}
echo '
  <fieldset class="m-0">'.legend($numgal.' : '.$catgal.$nomgal).'
    <form name="uggalleries" action="'.$baseurl.'&amp;regit=1&amp;numgal='.$numgal.($param4 == 'unitegallery' ? '#constructpanel' : '').'" method="POST">
      <fieldset class="m-0"><legend> '.$admin[134].' </legend>
        <div style="margin:6px 0 16px 26px;text-ailgn:center;">
          <label style="margin-right:12px;cursor:pointer;background-color:#DCD9E0;padding:8px;border:1px solid #000;border-radius:6px;">
            <input type="radio" name="param4" 
              onclick="cacheallug(); cache(\'bydef\'); cache(\''.$param13.'\'); cache(\'inter\'); cache(\'unitegallery\'); montre(\'commun\');" 
              value="lightbox"'.Checked($param4 == 'lightbox').' />
            '.$admin[1034].'
          </label>
          <label style="margin-right:12px;cursor:pointer;background-color:#DCD9E0;padding:8px;border:1px solid #000;border-radius:6px;">
            <input type="radio" name="param4" 
              onclick="cacheallug(); cache(\'bydef\'); cache(\''.$param13.'\'); cache(\'unitegallery\'); montre(\'inter\'); montre(\'commun\');" 
              value="lytebox"'.Checked($param4 == 'lytebox').' />
            '.$admin[1035].'
          </label>
          <label style="cursor:pointer;background-color:#DCD9E0;padding:8px;border:1px solid #000;border-radius:6px;">
            <input type="radio" name="param4" 
              onclick="montre(\'bydef\'); cache(\'inter\'); cache(\'commun\'); montre(\'unitegallery\'); montre(\''.$param13.'\'); montre(\'ugconstruct\');" 
              value="unitegallery"'.Checked($param4 == 'unitegallery').' />
            '.$admin[1740].'
          </label>
        </div>
        <div id="commun" style="display:'.(in_array($param4, array('lytebox','lightbox')) ? 'block' : 'none').';"><hr />
          <div class="table-responsive-md">
          <table class="table table-bordered w-100 m-auto">
            <tr id="inter" style="display:'.($param4 == 'lytebox' ? 'block' : 'none').';">
              <td style="margin-left:40px;">&bull; '.$admin[1313].'</td>
              <td><input class="form-control texte" type="text" name="param5" size="2" value="'.$param5.'" /></td>
          </tr>
          <tr>
            <td style="margin-left:40px;">&bull; '.$admin[1452].'</td>
            <td>
              <select class="form-control" name="param8">
                <option value="L"'.Selected('L' == $param8).'>'.$admin[390].'</option>
                <option value="C"'.Selected('C' == $param8).'>'.$admin[391].'</option>
                <option value="R"'.Selected('R' == $param8).'>'.$admin[392].'</option>
              </select>
            </td>
          </tr>
          <tr>
            <td style="margin-left:40px;">&bull; '.$admin[129].'</td>
            <td><input class="form-control texte" type="text" name="param1" size="2" value="'.$param1.'" /></td>
          </tr>
          <tr>
            <td style="margin-left:40px;">&bull; '.$admin[130].'</td>
            <td><input class="form-control texte" type="text" name="param2" size="4" value="'.$param2.'" /></td>
          </tr>
          <tr>
            <td style="margin-left:40px;">&bull; '.$admin[131].'</td>
            <td><input class="form-control texte" type="text" name="param3" size="4" value="'.$param3.'" /></td>
          </tr>
          <tr>
            <td style="margin-left:40px;">&bull; '.$admin[1450].'</td>
            <td><input class="form-control texte" type="text" name="param6" size="2" value="'.$param6.'" /></td>
          </tr>
          <tr>
            <td style="margin-left:40px;">&bull; '.$admin[1451].'</td>
            <td><input class="form-control texte" type="text" name="param7" size="3" value="'.$param7.'" /></td>
          </tr>
          '.InputCheckbox(10, $admin[1457], 'param9', $param9).'
          '.InputCheckbox(10, $admin[1458], 'param10', $param10).'
          '.InputCheckbox(10, $admin[1557], 'param11', $param11).'
          '.InputCheckbox(10, $admin[1558], 'param12', $param12).'
          </table>
          </div>
        </div>';
    $ugs   = array('ugtc','ugtj','ugtn','ugtg','ugca','ugdt','ugct','uggt','ugsl','ugvg');
    $ugsts = array('ugtc'=>$ugadmin[1],'ugtj'=>$ugadmin[2],'ugtn'=>$ugadmin[3],'ugtg'=>$ugadmin[4],'ugca'=>$ugadmin[5],
                   'ugdt'=>$ugadmin[6],'ugct'=>$ugadmin[7],'uggt'=>$ugadmin[8],'ugsl'=>$ugadmin[9],'ugvg'=>$ugadmin[10]);
    echo '
        <div id="unitegallery" style="display:'.($param4 == 'unitegallery' ? 'block' : 'none').';"><hr />
          <div style="margin:16px 0 10px 16px;text-align:center;">
            <label style="margin-right:12px;cursor:pointer;background-color:#DCD9E0;padding:8px;border:1px solid #000;border-radius:6px;">
              <input type="radio" name="param13" onclick="cacheallug(); montre(\''.$ugs[0].'\');"  checked value="'.$ugs[0].'"'.Checked($param13 == $ugs[0]).' />
              '.$ugsts[$ugs[0]].'
            </label>
            <label style="margin-right:12px;cursor:pointer;background-color:#DCD9E0;padding:8px;border:1px solid #000;border-radius:6px;">
              <input type="radio" name="param13" onclick="cacheallug(); montre(\''.$ugs[1].'\');" value="'.$ugs[1].'"'.Checked($param13 == $ugs[1]).' />
              '.$ugsts[$ugs[1]].'
            </label>
            <label style="margin-right:12px;cursor:pointer;background-color:#DCD9E0;padding:8px;border:1px solid #000;border-radius:6px;">
              <input type="radio" name="param13" onclick="cacheallug(); montre(\''.$ugs[2].'\');" value="'.$ugs[2].'"'.Checked($param13 == $ugs[2]).' />
              '.$ugsts[$ugs[2]].'
            </label>
            <label style="margin-right:12px;cursor:pointer;background-color:#DCD9E0;padding:8px;border:1px solid #000;border-radius:6px;">
              <input type="radio" name="param13" onclick="cacheallug(); montre(\''.$ugs[3].'\');" value="'.$ugs[3].'"'.Checked($param13 == $ugs[3]).' />
              '.$ugsts[$ugs[3]].'
            </label>
          </div>
          <div style="margin:20px 0 10px 16px;text-align:center;">
            <label style="margin-right:12px;cursor:pointer;background-color:#DCD9E0;padding:8px;border:1px solid #000;border-radius:6px;">
              <input type="radio" name="param13" onclick="cacheallug(); montre(\''.$ugs[4].'\');" value="'.$ugs[4].'"'.Checked($param13 == $ugs[4]).' />
              '.$ugsts[$ugs[4]].'
            </label>
            <label style="margin-right:12px;cursor:pointer;background-color:#DCD9E0;padding:8px;border:1px solid #000;border-radius:6px;">
              <input type="radio" name="param13" onclick="cacheallug(); montre(\''.$ugs[5].'\');" value="'.$ugs[5].'"'.Checked($param13 == $ugs[5]).' />
              '.$ugsts[$ugs[5]].'
            </label>
            <label style="margin-right:12px;cursor:pointer;background-color:#DCD9E0;padding:8px;border:1px solid #000;border-radius:6px;">
              <input type="radio" name="param13" onclick="cacheallug(); montre(\''.$ugs[6].'\');" value="'.$ugs[6].'"'.Checked($param13 == $ugs[6]).' />
              '.$ugsts[$ugs[6]].'
            </label>
            <label style="margin-right:12px;cursor:pointer;background-color:#DCD9E0;padding:8px;border:1px solid #000;border-radius:6px;">
              <input type="radio" name="param13" onclick="cacheallug(); montre(\''.$ugs[7].'\');" value="'.$ugs[7].'"'.Checked($param13 == $ugs[7]).' />
              '.$ugsts[$ugs[7]].'
            </label>
          </div>
          <div style="margin:20px 0 20px 26px;text-align:center;">
            <label style="margin-right:12px;cursor:pointer;background-color:#DCD9E0;padding:8px;border:1px solid #000;border-radius:6px;">
              <input type="radio" name="param13" onclick="cacheallug(); montre(\''.$ugs[8].'\');" value="'.$ugs[8].'"'.Checked($param13 == $ugs[8]).' />
              '.$ugsts[$ugs[8]].'
            </label>
            <label style="margin-right:12px;cursor:pointer;background-color:#DCD9E0;padding:8px;border:1px solid #000;border-radius:6px;">
              <input type="radio" name="param13" onclick="cacheallug(); montre(\''.$ugs[9].'\');" value="'.$ugs[9].'"'.Checked($param13 == $ugs[9]).' />
              '.$ugsts[$ugs[9]].'
            </label>
          </div>
        </div>
        <div id="ugtc" class="w-100 m-auto" style="display:'.($param4 == 'unitegallery' && $param13 == 'ugtc' ? 'block' : 'none').';"><hr />
          <fieldset style="margin:8px 0;padding:0 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[11].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100 m-auto">';
    echo '
              '.InputTexte(32, $ugadmin[25].' (100%)', 'tcgo1', $ugtc_go[1], 4).'
              '.InputTexte(32, $ugadmin[27].' (150)', 'tcgo2', $ugtc_go[2], 4).'
              '.GetSelector(32, $ugadmin[31], 'tcgo3', $ugtc_go[3]).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0; padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[12].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputTexte(32, $ugadmin[32].' (250)', 'tcto1', $ugtc_to[1], 4).'
              '.SelectText(32, $ugadmin[33], 'tcto2', $ugtc_to[2], $textalign).'
              '.InputTexte(32, $ugadmin[34].' (3)', 'tcto3', $ugtc_to[3], 4).'
              '.InputCheckbox(32, $ugadmin[36].' ('.$admin[1604].')', 'tcto4', ($ugtc_to[4] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[35].' (3)', 'tcto5', $ugtc_to[5], 4).'
              '.InputCheckbox(32, $ugadmin[37].' ('.$admin[1605].')', 'tcto6', ($ugtc_to[6] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[38].' (2)', 'tcto7', $ugtc_to[7], 4).'
              '.InputTexte(32, $ugadmin[40].' ('.$ugadmin[139].')', 'tcto8', $ugtc_to[8], 4).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0; padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[13].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputCheckbox(32, $ugadmin[41].' ('.$admin[1604].')', 'tctdo1', ($ugtc_tdo[1] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[42].' (3)', 'tctdo2', $ugtc_tdo[2], 4).'
              '.GetSelector(32, $ugadmin[43].' (#F0F0F0)', 'tctdo3', $ugtc_tdo[3]).'
              '.InputTexte(32, $ugadmin[44].' (0)', 'tctdo4', $ugtc_tdo[4], 4).'
              '.InputCheckbox(32, $ugadmin[46].' ('.$admin[1604].')', 'tctdo5', ($ugtc_tdo[5] == 'true' ? true : false)).'
              '.GetSelector(32, $ugadmin[47].' (#8B8B8B)', 'tctdo6', $ugtc_tdo[6]).'
              '.InputCheckbox(32, $ugadmin[48].' ('.$admin[1605].')', 'tctdo7', ($ugtc_tdo[7] == 'true' ? true : false)).'
              '.GetSelector(32, $ugadmin[49].' (#8B8B8B)', 'tctdo8', $ugtc_tdo[8]).'
              '.InputCheckbox(32, $ugadmin[53].' ('.$admin[1605].')', 'tctdo9', ($ugtc_tdo[9] == 'true' ? true : false)).'
              '.InputNumber(32, $ugadmin[54].' (0.4)', 'tctdo10', 0, 1, $ugtc_tdo[10], 0.1).'
              '.GetSelector(32, $ugadmin[55].' (#000000)', 'tctdo11', $ugtc_tdo[11]).'
              '.InputCheckbox(32, $ugadmin[57].' ('.$admin[1605].')', 'tctdo12', ($ugtc_tdo[12] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[51].' ('.$admin[1604].')', 'tctdo13', ($ugtc_tdo[13] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[52].' ('.$admin[1604].')', 'tctdo14', ($ugtc_tdo[14] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[58].' (26)', 'tctdo15', $ugtc_tdo[15], 4).'
              '.InputCheckbox(32, $ugadmin[59].' ('.$admin[1604].')', 'tctdo16', ($ugtc_tdo[16] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[60], 'tctdo17', $ugtc_tdo[17], $effects).'
              '.InputCheckbox(32, $ugadmin[61].' ('.$admin[1604].')', 'tctdo18', ($ugtc_tdo[18] == 'true' ? true : false)).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0; padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[14].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputCheckbox(32, $ugadmin[62].' ('.$admin[1604].')', 'tcttpo1', ($ugtc_ttpo[1] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[63].' ('.$admin[1604].')', 'tcttpo2', ($ugtc_ttpo[2] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[64], 'tcttpo3', $ugtc_ttpo[3], $appears).'
              '.SelectText(32, $ugadmin[65], 'tcttpo4', $ugtc_ttpo[4], $panelpos).'
              '.InputNumber(32, $ugadmin[66].' (0.4)', 'tcttpo5', 0, 1, $ugtc_ttpo[5], 0.1).'
              '.GetSelector(32, $ugadmin[67].' (#000000)', 'tcttpo6', $ugtc_ttpo[6]).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0; padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[15].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.SelectText(32, $ugadmin[68], 'tclo1', $ugtc_lo[1], $lightbox).'
              '.SelectText(32, $ugadmin[69], 'tclo2', $ugtc_lo[2], $arrowspos).'
              '.InputCheckbox(32, $ugadmin[70].' ('.$admin[1604].')', 'tclo3', ($ugtc_lo[3] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[71].' ('.$admin[1605].')', 'tclo4', ($ugtc_lo[4] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[72].' (10)', 'tclo5', $ugtc_lo[5], 4).'
              '.GetSelector(32, $ugadmin[73].' (#FFFFFF)', 'tclo6', $ugtc_lo[6]).'
              '.InputTexte(32, $ugadmin[74].' (0)', 'tclo7', $ugtc_lo[7], 4).'
            </table>
            </div>
          </fieldset>
        </div>
        <div id="ugtj" class="w-100 m-auto" style="display:'.($param4 == 'unitegallery' && $param13 == 'ugtj' ? 'block' : 'none').';"><hr />
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[11].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputTexte(32, $ugadmin[25].' (100%)', 'tjgo1', $ugtj_go[1], 4).'
              '.InputTexte(32, $ugadmin[27].' (150)', 'tjgo2', $ugtj_go[2], 4).'
              '.GetSelector(32, $ugadmin[31], 'tjgo3', $ugtj_go[3]).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:-12px 0 0; padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[12].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputTexte(32, $ugadmin[75].' (150)', 'tjto1', $ugtj_to[1], 4).'
              '.InputTexte(32, $ugadmin[76].' (3)', 'tjto2', $ugtj_to[2], 4).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0; padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[13].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputCheckbox(32, $ugadmin[41].' ('.$admin[1604].')', 'tjtdo1', ($ugtj_tdo[1] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[42].' (3)', 'tjtdo2', $ugtj_tdo[2], 4).'
              '.GetSelector(32, $ugadmin[43].' (#F0F0F0)', 'tjtdo3', $ugtj_tdo[3]).'
              '.InputTexte(32, $ugadmin[44].' (0)', 'tjtdo4', $ugtj_tdo[4], 4).'
              '.InputCheckbox(32, $ugadmin[46].' ('.$admin[1604].')', 'tjtdo5', ($ugtj_tdo[5] == 'true' ? true : false)).'
              '.GetSelector(32, $ugadmin[47].' (#8B8B8B)', 'tjtdo6', $ugtj_tdo[6]).'
              '.InputCheckbox(32, $ugadmin[48].' ('.$admin[1605].')', 'tjtdo7', ($ugtj_tdo[7] == 'true' ? true : false)).'
              '.GetSelector(32, $ugadmin[49].' (#8B8B8B)', 'tjtdo8', $ugtj_tdo[8]).'
              '.InputCheckbox(32, $ugadmin[53].' ('.$admin[1605].')', 'tjtdo9', ($ugtj_tdo[9] == 'true' ? true : false)).'
              '.InputNumber(32, $ugadmin[54].' (0.4)', 'tjtdo10', 0, 1, $ugtj_tdo[10], 0.1).'
              '.GetSelector(32, $ugadmin[55].' (#000000)', 'tjtdo11', $ugtj_tdo[11]).'
              '.InputCheckbox(32, $ugadmin[57].' ('.$admin[1605].')', 'tjtdo12', ($ugtj_tdo[12] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[51].' ('.$admin[1604].')', 'tjtdo13', ($ugtj_tdo[13] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[52].' ('.$admin[1604].')', 'tjtdo14', ($ugtj_tdo[14] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[58].' (26)', 'tjtdo15', $ugtj_tdo[15], 4).'
              '.InputCheckbox(32, $ugadmin[59].' ('.$admin[1604].')', 'tjtdo16', ($ugtj_tdo[16] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[60], 'tjtdo17', $ugtj_tdo[17], $effects).'
              '.InputCheckbox(32, $ugadmin[61].' ('.$admin[1604].')', 'tjtdo18', ($ugtj_tdo[18] == 'true' ? true : false)).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0; padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[14].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputCheckbox(32, $ugadmin[62].' ('.$admin[1604].')', 'tjttpo1', ($ugtj_ttpo[1] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[63].' ('.$admin[1604].')', 'tjttpo2', ($ugtj_ttpo[2] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[64], 'tjttpo3', $ugtj_ttpo[3], $appears).'
              '.SelectText(32, $ugadmin[65], 'tjttpo4', $ugtj_ttpo[4], $panelpos).'
              '.InputNumber(32, $ugadmin[66].' (0.4)', 'tjttpo5', 0, 1, $ugtj_ttpo[5], 0.1).'
              '.GetSelector(32, $ugadmin[67].' (#000000)', 'tjttpo6', $ugtj_ttpo[6]).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0; padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[15].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.SelectText(32, $ugadmin[68], 'tjlo1', $ugtj_lo[1], $lightbox).'
              '.SelectText(32, $ugadmin[69], 'tjlo2', $ugtj_lo[2], $arrowspos).'
              '.InputCheckbox(32, $ugadmin[70].' ('.$admin[1604].')', 'tjlo3', ($ugtj_lo[3] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[71].' ('.$admin[1605].')', 'tjlo4', ($ugtj_lo[4] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[72].' (10)', 'tjlo5', $ugtj_lo[5], 4).'
              '.GetSelector(32, $ugadmin[73].' (#FFFFFF)', 'tjlo6', $ugtj_lo[6]).'
              '.InputTexte(32, $ugadmin[74].' (0)', 'tjlo7', $ugtj_lo[7], 4).'
            </table>
            </div>
          </fieldset>
        </div>
        <div id="ugtn" class="w-100 m-auto" style="display:'.($param4 == 'unitegallery' && $param13 == 'ugtn' ? 'block' : 'none').';"><hr />
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[11].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputTexte(32, $ugadmin[25].' (100%)', 'tngo1', $ugtn_go[1], 4).'
              '.InputTexte(32, $ugadmin[27].' (150)', 'tngo2', $ugtn_go[2], 4).'
              '.GetSelector(32, $ugadmin[31], 'tngo3', $ugtn_go[3]).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[12].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputCheckbox(32, $ugadmin[77].' ('.$admin[1605].')', 'tnto1', ($ugtn_to[1] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[34].' (3)', 'tnto2', $ugtn_to[2], 4).'
              '.InputTexte(32, $ugadmin[35].' (3)', 'tnto3', $ugtn_to[3], 4).'
              '.InputTexte(32, $ugadmin[39].' (250)', 'tnto4', $ugtn_to[4], 4).'
              '.InputTexte(32, $ugadmin[38].' (2)', 'tnto5', $ugtn_to[5], 4).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[13].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputCheckbox(32, $ugadmin[41].' ('.$admin[1604].')', 'tntdo1', ($ugtn_tdo[1] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[42].' (3)', 'tntdo2', $ugtn_tdo[2], 4).'
              '.GetSelector(32, $ugadmin[43].' (#F0F0F0)', 'tntdo3', $ugtn_tdo[3]).'
              '.InputTexte(32, $ugadmin[44].' (0)', 'tntdo4', $ugtn_tdo[4], 4).'
              '.InputCheckbox(32, $ugadmin[46].' ('.$admin[1604].')', 'tntdo5', ($ugtn_tdo[5] == 'true' ? true : false)).'
              '.GetSelector(32, $ugadmin[47].' (#8B8B8B)', 'tntdo6', $ugtn_tdo[6]).'
              '.InputCheckbox(32, $ugadmin[48].' ('.$admin[1605].')', 'tntdo7', ($ugtn_tdo[7] == 'true' ? true : false)).'
              '.GetSelector(32, $ugadmin[49].' (#8B8B8B)', 'tntdo8', $ugtn_tdo[8]).'
              '.InputCheckbox(32, $ugadmin[53].' ('.$admin[1605].')', 'tntdo9', ($ugtn_tdo[9] == 'true' ? true : false)).'
              '.InputNumber(32, $ugadmin[54].' (0.4)', 'tntdo10', 0, 1, $ugtn_tdo[10], 0.1).'
              '.GetSelector(32, $ugadmin[55].' (#000000)', 'tntdo11', $ugtn_tdo[11]).'
              '.InputCheckbox(32, $ugadmin[57].' ('.$admin[1605].')', 'tntdo12', ($ugtn_tdo[12] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[51].' ('.$admin[1604].')', 'tntdo13', ($ugtn_tdo[13] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[52].' ('.$admin[1604].')', 'tntdo14', ($ugtn_tdo[14] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[58].' (26)', 'tntdo15', $ugtn_tdo[15], 4).'
              '.InputCheckbox(32, $ugadmin[59].' ('.$admin[1604].')', 'tntdo16', ($ugtn_tdo[16] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[60], 'tntdo17', $ugtn_tdo[17], $effects).'
              '.InputCheckbox(32, $ugadmin[61].' ('.$admin[1604].')', 'tntdo18', ($ugtn_tdo[18] == 'true' ? true : false)).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[14].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputCheckbox(32, $ugadmin[62].' ('.$admin[1604].')', 'tnttpo1', ($ugtn_ttpo[1] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[63].' ('.$admin[1604].')', 'tnttpo2', ($ugtn_ttpo[2] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[64], 'tnttpo3', $ugtn_ttpo[3], $appears).'
              '.SelectText(32, $ugadmin[65], 'tnttpo4', $ugtn_ttpo[4], $panelpos).'
              '.InputNumber(32, $ugadmin[66].' (0.4)', 'tnttpo5', 0, 1, $ugtn_ttpo[5], 0.1).'
              '.GetSelector(32, $ugadmin[67].' (#000000)', 'tnttpo6', $ugtn_ttpo[6]).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[15].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.SelectText(32, $ugadmin[68], 'tnlo1', $ugtn_lo[1], $lightbox).'
              '.SelectText(32, $ugadmin[69], 'tnlo2', $ugtn_lo[2], $arrowspos).'
              '.InputCheckbox(32, $ugadmin[70].' ('.$admin[1604].')', 'tnlo3', ($ugtn_lo[3] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[71].' ('.$admin[1605].')', 'tnlo4', ($ugtn_lo[4] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[72].' (10)', 'tnlo5', $ugtn_lo[5], 4).'
              '.GetSelector(32, $ugadmin[73].' (#FFFFFF)', 'tnlo6', $ugtn_lo[6]).'
              '.InputTexte(32, $ugadmin[74].' (0)', 'tnlo7', $ugtn_lo[7], 4).'
            </table>
            </div>
          </fieldset>
        </div>
        <div id="ugtg" class="w-100 m-auto" style="display:'.($param4 == 'unitegallery' && $param13 == 'ugtg' ? 'block' : 'none').';"><hr />
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[17].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputTexte(32, $ugadmin[78].' (180)', 'tgmo1', $ugtg_mo[1], 4).'
              '.InputTexte(32, $ugadmin[79].' (150)', 'tgmo2', $ugtg_mo[2], 4).'
              '.InputTexte(32, $ugadmin[80].' (10)', 'tgmo3', $ugtg_mo[3], 4).'
              '.InputTexte(32, $ugadmin[81].' (20)', 'tgmo4', $ugtg_mo[4], 4).'
              '.InputTexte(32, $ugadmin[82].' (20)', 'tgmo5', $ugtg_mo[5], 4).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[11].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputTexte(32, $ugadmin[25].' (100%)', 'tggo1', $ugtg_go[1], 4).'
              '.InputTexte(32, $ugadmin[27].' (150)', 'tggo2', $ugtg_go[2], 4).'
              '.GetSelector(32, $ugadmin[31], 'tggo3', $ugtg_go[3]).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:0 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[18].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputTexte(32, $ugadmin[83].' (3)', 'tgno1', $ugtg_no[1], 4).'
              '.SelectText(32, $ugadmin[84], 'tgno2', $ugtg_no[2], $navtype).'
              '.InputTexte(32, $ugadmin[85].' (40)', 'tgno3', $ugtg_no[3], 4).'
              '.SelectText(32, $ugadmin[86], 'tgno4', $ugtg_no[4], $bullcolor).'
              '.InputTexte(32, $ugadmin[87].' (12)', 'tgno5', $ugtg_no[5], 4).'
              '.InputTexte(32, $ugadmin[88].' (20)', 'tgno6', $ugtg_no[6], 4).'
              '.InputTexte(32, $ugadmin[89].' (5)', 'tgno7', $ugtg_no[7], 4).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[13].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputCheckbox(32, $ugadmin[41].' ('.$admin[1604].')', 'tgtdo1', ($ugtg_tdo[1] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[42].' (3)', 'tgtdo2', $ugtg_tdo[2], 4).'
              '.GetSelector(32, $ugadmin[43].' (#F0F0F0)', 'tgtdo3', $ugtg_tdo[3]).'
              '.InputTexte(32, $ugadmin[44].' (0)', 'tgtdo4', $ugtg_tdo[4], 4).'
              '.InputCheckbox(32, $ugadmin[46].' ('.$admin[1604].')', 'tgtdo5', ($ugtg_tdo[5] == 'true' ? true : false)).'
              '.GetSelector(32, $ugadmin[47].' (#8B8B8B)', 'tgtdo6', $ugtg_tdo[6]).'
              '.InputCheckbox(32, $ugadmin[48].' ('.$admin[1605].')', 'tgtdo7', ($ugtg_tdo[7] == 'true' ? true : false)).'
              '.GetSelector(32, $ugadmin[49].' (#8B8B8B)', 'tgtdo8', $ugtg_tdo[8]).'
              '.InputCheckbox(32, $ugadmin[53].' ('.$admin[1605].')', 'tgtdo9', ($ugtg_tdo[9] == 'true' ? true : false)).'
              '.InputNumber(32, $ugadmin[54].' (0.4)', 'tgtdo10', 0, 1, $ugtg_tdo[10], 0.1).'
              '.GetSelector(32, $ugadmin[55].' (#000000)', 'tgtdo11', $ugtg_tdo[11]).'
              '.InputCheckbox(32, $ugadmin[57].' ('.$admin[1605].')', 'tgtdo12', ($ugtg_tdo[12] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[51].' ('.$admin[1604].')', 'tgtdo13', ($ugtg_tdo[13] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[52].' ('.$admin[1605].')', 'tgtdo14', ($ugtg_tdo[14] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[58].' (26)', 'tgtdo15', $ugtg_tdo[15], 4).'
              '.InputCheckbox(32, $ugadmin[59].' ('.$admin[1604].')', 'tgtdo16', ($ugtg_tdo[16] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[60], 'tgtdo17', $ugtg_tdo[17], $effects).'
              '.InputCheckbox(32, $ugadmin[61].' ('.$admin[1604].')', 'tgtdo18', ($ugtg_tdo[18] == 'true' ? true : false)).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[14].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputCheckbox(32, $ugadmin[62].' ('.$admin[1604].')', 'tgttpo1', ($ugtg_ttpo[1] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[63].' ('.$admin[1604].')', 'tgttpo2', ($ugtg_ttpo[2] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[64], 'tgttpo3', $ugtg_ttpo[3], $appears).'
              '.SelectText(32, $ugadmin[65], 'tgttpo4', $ugtg_ttpo[4], $panelpos).'
              '.InputNumber(32, $ugadmin[66].' (0.4)', 'tgttpo5', 0, 1, $ugtg_ttpo[5], 0.1).'
              '.GetSelector(32, $ugadmin[67].' (#000000)', 'tgttpo6', $ugtg_ttpo[6]).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[15].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.SelectText(32, $ugadmin[68], 'tglo1', $ugtg_lo[1], $lightbox).'
              '.SelectText(32, $ugadmin[69], 'tglo2', $ugtg_lo[2], $arrowspos).'
              '.InputCheckbox(32, $ugadmin[70].' ('.$admin[1604].')', 'tglo3', ($ugtg_lo[3] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[71].' ('.$admin[1605].')', 'tglo4', ($ugtg_lo[4] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[72].' (10)', 'tglo5', $ugtg_lo[5], 4).'
              '.GetSelector(32, $ugadmin[73].' (#FFFFFF)', 'tglo6', $ugtg_lo[6]).'
              '.InputTexte(32, $ugadmin[74].' (0)', 'tglo7', $ugtg_lo[7], 4).'
            </table>
            </div>
          </fieldset>
        </div>
        <div id="ugca" class="w-100 m-auto" style="display:'.($param4 == 'unitegallery' && $param13 == 'ugca' ? 'block' : 'none').';"><hr />
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[17].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputTexte(32, $ugadmin[78].' (160)', 'camo1', $ugca_mo[1], 4).'
              '.InputTexte(32, $ugadmin[79].' (160)', 'camo2', $ugca_mo[2], 4).'
              '.SelectText(32, $ugadmin[90], 'camo3', $ugca_mo[3], $textalign).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[11].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputTexte(32, $ugadmin[25].' (100%)', 'cago1', $ugca_go[1], 4).'
              '.InputTexte(32, $ugadmin[27].' (150)', 'cago2', $ugca_go[2], 4).'
              '.GetSelector(32, $ugadmin[31], 'cago3', $ugca_go[3]).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[19].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputTexte(32, $ugadmin[125].' (20)', 'caco1', $ugca_co[1], 4).'
              '.InputTexte(32, $ugadmin[126].' (3)', 'caco2', $ugca_co[2], 4).'
              '.InputCheckbox(32, $ugadmin[127].' ('.$admin[1605].')', 'caco3', ($ugca_co[3] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[128].' (3000)', 'caco4', $ugca_co[4], 4).'
              '.SelectText(32, $ugadmin[129], 'caco5', $ugca_co[5], $textdir).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[18].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputCheckbox(32, $ugadmin[130].' ('.$admin[1605].')', 'cano1', ($ugca_no[1] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[131], 'cano2', $ugca_no[2], $vertpos).'
              '.InputTexte(32, $ugadmin[132].' (20)', 'cano3', $ugca_no[3], 4).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[13].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputCheckbox(32, $ugadmin[41].' ('.$admin[1604].')', 'catdo1', ($ugca_tdo[1] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[42].' (3)', 'catdo2', $ugca_tdo[2], 4).'
              '.GetSelector(32, $ugadmin[43].' (#F0F0F0)', 'catdo3', $ugca_tdo[3]).'
              '.InputTexte(32, $ugadmin[44].' (0)', 'catdo4', $ugca_tdo[4], 4).'
              '.InputCheckbox(32, $ugadmin[46].' ('.$admin[1604].')', 'catdo5', ($ugca_tdo[5] == 'true' ? true : false)).'
              '.GetSelector(32, $ugadmin[47].' (#8B8B8B)', 'catdo6', $ugca_tdo[6]).'
              '.InputCheckbox(32, $ugadmin[48].' ('.$admin[1605].')', 'catdo7', ($ugca_tdo[7] == 'true' ? true : false)).'
              '.GetSelector(32, $ugadmin[49].' (#8B8B8B)', 'catdo8', $ugca_tdo[8]).'
              '.InputCheckbox(32, $ugadmin[53].' ('.$admin[1605].')', 'catdo9', ($ugca_tdo[9] == 'true' ? true : false)).'
              '.InputNumber(32, $ugadmin[54].' (0.4)', 'catdo10', 0, 1, $ugca_tdo[10], 0.1).'
              '.GetSelector(32, $ugadmin[55].' (#000000)', 'catdo11', $ugca_tdo[11]).'
              '.InputCheckbox(32, $ugadmin[57].' ('.$admin[1605].')', 'catdo12', ($ugca_tdo[12] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[51].' ('.$admin[1604].')', 'catdo13', ($ugca_tdo[13] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[52].' ('.$admin[1605].')', 'catdo14', ($ugca_tdo[14] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[58].' (26)', 'catdo15', $ugca_tdo[15], 4).'
              '.InputCheckbox(32, $ugadmin[59].' ('.$admin[1604].')', 'catdo16', ($ugca_tdo[16] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[60], 'catdo17', $ugca_tdo[17], $effects).'
              '.InputCheckbox(32, $ugadmin[61].' ('.$admin[1604].')', 'catdo18', ($ugca_tdo[18] == 'true' ? true : false)).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[14].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputCheckbox(32, $ugadmin[62].' ('.$admin[1604].')', 'cattpo1', ($ugca_ttpo[1] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[63].' ('.$admin[1604].')', 'cattpo2', ($ugca_ttpo[2] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[64], 'cattpo3', $ugca_ttpo[3], $appears).'
              '.SelectText(32, $ugadmin[65], 'cattpo4', $ugca_ttpo[4], $panelpos).'
              '.InputNumber(32, $ugadmin[66].' (0.4)', 'cattpo5', 0, 1, $ugca_ttpo[5], 0.1).'
              '.GetSelector(32, $ugadmin[67].' (#000000)', 'cattpo6', $ugca_ttpo[6]).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[15].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.SelectText(32, $ugadmin[68], 'calo1', $ugca_lo[1], $lightbox).'
              '.SelectText(32, $ugadmin[69], 'calo2', $ugca_lo[2], $arrowspos).'
              '.InputCheckbox(32, $ugadmin[70].' ('.$admin[1604].')', 'calo3', ($ugca_lo[3] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[71].' ('.$admin[1605].')', 'calo4', ($ugca_lo[4] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[72].' (10)', 'calo5', $ugca_lo[5], 4).'
              '.GetSelector(32, $ugadmin[73].' (#FFFFFF)', 'calo6', $ugca_lo[6]).'
              '.InputTexte(32, $ugadmin[74].' (0)', 'calo7', $ugca_lo[7], 4).'
            </table>
            </div>
          </fieldset>
        </div>
        <div id="ugdt" class="w-100 m-auto" style="display:'.($param4 == 'unitegallery' && $param13 == 'ugdt' ? 'block' : 'none').';"><hr />
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[20].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputCheckbox(32, $ugadmin[133].' ('.$admin[1605].')', 'dttmo1', ($ugdt_tmo[1] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[134].' ('.$admin[1605].')', 'dttmo2', ($ugdt_tmo[2] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[135].' ('.$admin[1605].')', 'dttmo3', ($ugdt_tmo[3] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[136].' ('.$admin[1605].')', 'dttmo4', ($ugdt_tmo[4] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[137], 'dttmo5', $ugdt_tmo[5], $textalign).'
              '.InputTexte(32, $ugadmin[138].' (480)', 'dttmo6', $ugdt_tmo[6], 4).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[11].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputTexte(32, $ugadmin[25].' (900)', 'dtgo1', $ugdt_go[1], 4).'
              '.InputTexte(32, $ugadmin[26].' (500)', 'dtgo2', $ugdt_go[2], 4).'
              '.InputTexte(32, $ugadmin[27].' (400)', 'dtgo3', $ugdt_go[3], 4).'
              '.InputTexte(32, $ugadmin[28].' (300)', 'dtgo4', $ugdt_go[4], 4).'
              '.InputCheckbox(32, $ugadmin[29].' ('.$admin[1604].')', 'dtgo5', ($ugdt_go[5] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[30].' (3000)', 'dtgo6', $ugdt_go[6], 4).'
              '.GetSelector(32, $ugadmin[31], 'dtgo7', $ugdt_go[7]).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[21].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.SelectText(32, $ugadmin[140], 'dtslo1', $ugdt_slo[1], $transit).'
              '.SelectText(32, $ugadmin[141], 'dtslo2', $ugdt_slo[2], $appears).'
              '.InputTexte(32, $ugadmin[142].' (300)', 'dtslo3', $ugdt_slo[3], 4).'
              '.InputCheckbox(32, $ugadmin[145].' ('.$admin[1605].')', 'dtslo4', ($ugdt_slo[4] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[146].' ('.$admin[1605].')', 'dtslo5', ($ugdt_slo[5] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[147], 'dtslo6', $ugdt_slo[6], $progress).'
              '.SelectText(32, $ugadmin[148], 'dtslo7', $ugdt_slo[7], $textalign).'
              '.SelectText(32, $ugadmin[149], 'dtslo8', $ugdt_slo[8], $vertalign).'
              '.GetSelector(32, $ugadmin[150].' (#FFFFFF)', 'dtslo9', $ugdt_slo[9]).'
              '.InputNumber(32, $ugadmin[151].' (0.6)', 'dtslo10', 0, 1, $ugdt_slo[10], 0.1).'
              '.InputCheckbox(32, $ugadmin[134].' ('.$admin[1605].')', 'dtslo11', ($ugdt_slo[11] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[133].' ('.$admin[1605].')', 'dtslo12', ($ugdt_slo[12] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[152].' ('.$admin[1605].')', 'dtslo13', ($ugdt_slo[13] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[153].' ('.$admin[1605].')', 'dtslo14', ($ugdt_slo[14] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[154].' ('.$admin[1605].')', 'dtslo15', ($ugdt_slo[15] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[156].' ('.$admin[1604].')', 'dtslo16', ($ugdt_slo[16] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[157], 'dtslo17', $ugdt_slo[17], $vertpos2).'
              '.GetSelector(32, $ugadmin[67].' (#000000)', 'dtslo18', $ugdt_slo[18]).'
              '.InputNumber(32, $ugadmin[66].' (0.4)', 'dtslo19', 0, 1, $ugdt_slo[19], 0.1).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[22].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputTexte(32, $ugadmin[159].' (88)', 'dttho1', $ugdt_tho[1], 4).'
              '.InputTexte(32, $ugadmin[160].' (50)', 'dttho2', $ugdt_tho[2], 4).'
              '.InputCheckbox(32, $ugadmin[161].' ('.$admin[1605].')', 'dttho3', ($ugdt_tho[3] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[162].' ('.$admin[1605].')', 'dttho4', ($ugdt_tho[4] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[163].' (0)', 'dttho5', $ugdt_tho[5], 4).'
              '.GetSelector(32, $ugadmin[164].' (#000000)', 'dttho6', $ugdt_tho[6]).'
              '.InputTexte(32, $ugadmin[165].' (0)', 'dttho7', $ugdt_tho[7], 4).'
              '.GetSelector(32, $ugadmin[166].' (#D9D9D9)', 'dttho8', $ugdt_tho[8]).'
              '.InputTexte(32, $ugadmin[167].' (1)', 'dttho9', $ugdt_tho[9], 4).'
              '.GetSelector(32, $ugadmin[168].' (#D9D9D9)', 'dttho10', $ugdt_tho[10]).'
              '.InputTexte(32, $ugadmin[169].' (0)', 'dttho11', $ugdt_tho[11], 4).'
              '.InputCheckbox(32, $ugadmin[170].' ('.$admin[1605].')', 'dttho12', ($ugdt_tho[12] == 'true' ? true : false)).'
              '.GetSelector(32, $ugadmin[171].' (#000000)', 'dttho13', $ugdt_tho[13]).'
              '.InputNumber(32, $ugadmin[172].' (0.4)', 'dttho14', 0, 1, $ugdt_tho[14], 0.1).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[23].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputCheckbox(32, $ugadmin[173].' ('.$admin[1604].')', 'dtstpo1', ($ugdt_stpo[1] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[174].' (2)', 'dtstpo2', $ugdt_stpo[2], 4).'
              '.SelectText(32, $ugadmin[175], 'dtstpo3', $ugdt_stpo[3], $thumbrole).'
              '.SelectText(32, $ugadmin[176], 'dtstpo4', $ugdt_stpo[4], $panalign).'
              '.GetSelector(32, $ugadmin[177], 'dtstpo5', $ugdt_stpo[5]).'
              '.SelectText(32, $ugadmin[178], 'dtstpo6', $ugdt_stpo[6], $panalign).'
            </table>
            </div>
          </fieldset>
        </div>
        <div id="ugct" class="w-100 m-auto" style="display:'.($param4 == 'unitegallery' && $param13 == 'ugct' ? 'block' : 'none').';"><hr />
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[20].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.SelectText(32, $ugadmin[179], 'cttmo1', $ugct_tmo[1], $panelpos2).'
              '.InputTexte(32, $ugadmin[180].' (480)', 'cttmo2', $ugct_tmo[2], 4).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[11].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputTexte(32, $ugadmin[25].' (900)', 'ctgo1', $ugct_go[1], 4).'
              '.InputTexte(32, $ugadmin[26].' (500)', 'ctgo2', $ugct_go[2], 4).'
              '.InputTexte(32, $ugadmin[27].' (400)', 'ctgo3', $ugct_go[3], 4).'
              '.InputTexte(32, $ugadmin[28].' (300)', 'ctgo4', $ugct_go[4], 4).'
              '.InputCheckbox(32, $ugadmin[29].' ('.$admin[1604].')', 'ctgo5', ($ugct_go[5] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[30].' (3000)', 'ctgo6', $ugct_go[6], 4).'
              '.GetSelector(32, $ugadmin[31], 'ctgo7', $ugct_go[7]).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[21].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.SelectText(32, $ugadmin[140], 'ctslo1', $ugct_slo[1], $transit).'
              '.SelectText(32, $ugadmin[141], 'ctslo2', $ugct_slo[2], $appears).'
              '.InputTexte(32, $ugadmin[142].' (300)', 'ctslo3', $ugct_slo[3], 4).'
              '.InputCheckbox(32, $ugadmin[145].' ('.$admin[1605].')', 'ctslo4', ($ugct_slo[4] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[146].' ('.$admin[1605].')', 'ctslo5', ($ugct_slo[5] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[147], 'ctslo6', $ugct_slo[6], $progress).'
              '.SelectText(32, $ugadmin[148], 'ctslo7', $ugct_slo[7], $textalign).'
              '.SelectText(32, $ugadmin[149], 'ctslo8', $ugct_slo[8], $vertalign).'
              '.GetSelector(32, $ugadmin[150].' (#FFFFFF)', 'ctslo9', $ugct_slo[9]).'
              '.InputNumber(32, $ugadmin[151].' (0.6)', 'ctslo10', 0, 1, $ugct_slo[10], 0.1).'
              '.InputCheckbox(32, $ugadmin[134].' ('.$admin[1605].')', 'ctslo11', ($ugct_slo[11] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[133].' ('.$admin[1605].')', 'ctslo12', ($ugct_slo[12] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[152].' ('.$admin[1605].')', 'ctslo13', ($ugct_slo[13] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[153].' ('.$admin[1605].')', 'ctslo14', ($ugct_slo[14] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[154].' ('.$admin[1605].')', 'ctslo15', ($ugct_slo[15] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[156].' ('.$admin[1604].')', 'ctslo16', ($ugct_slo[16] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[157], 'ctslo17', $ugct_slo[17], $vertpos2).'
              '.GetSelector(32, $ugadmin[67].' (#000000)', 'ctslo18', $ugct_slo[18]).'
              '.InputNumber(32, $ugadmin[66].' (0.4)', 'ctslo19', 0, 1, $ugct_slo[19], 0.1).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[22].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputTexte(32, $ugadmin[159].' (88)', 'cttho1', $ugct_tho[1], 4).'
              '.InputTexte(32, $ugadmin[160].' (50)', 'cttho2', $ugct_tho[2], 4).'
              '.InputCheckbox(32, $ugadmin[161].' ('.$admin[1605].')', 'cttho3', ($ugct_tho[3] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[162].' ('.$admin[1605].')', 'cttho4', ($ugct_tho[4] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[163].' (0)', 'cttho5', $ugct_tho[5], 4).'
              '.GetSelector(32, $ugadmin[164].' (#000000)', 'cttho6', $ugct_tho[6]).'
              '.InputTexte(32, $ugadmin[165].' (1)', 'cttho7', $ugct_tho[7], 4).'
              '.GetSelector(32, $ugadmin[166].' (#D9D9D9)', 'cttho8', $ugct_tho[8]).'
              '.InputTexte(32, $ugadmin[167].' (1)', 'cttho9', $ugct_tho[9], 4).'
              '.GetSelector(32, $ugadmin[168].' (#D9D9D9)', 'cttho10', $ugct_tho[10]).'
              '.InputTexte(32, $ugadmin[169].' (0)', 'cttho11', $ugct_tho[11], 4).'
              '.InputCheckbox(32, $ugadmin[170].' ('.$admin[1605].')', 'cttho12', ($ugct_tho[12] == 'true' ? true : false)).'
              '.GetSelector(32, $ugadmin[171].' (#000000)', 'cttho13', $ugct_tho[13]).'
              '.InputNumber(32, $ugadmin[172].' (0.4)', 'cttho14', 0, 1, $ugct_tho[14], 0.1).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[23].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputCheckbox(32, $ugadmin[173].' ('.$admin[1604].')', 'ctstpo1', ($ugct_stpo[1] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[174].' (2)', 'ctstpo2', $ugct_stpo[2], 4).'
              '.SelectText(32, $ugadmin[175], 'ctstpo3', $ugct_stpo[3], $thumbrole).'
              '.SelectText(32, $ugadmin[176], 'ctstpo4', $ugct_stpo[4], $panalign).'
              '.GetSelector(32, $ugadmin[177], 'ctstpo5', $ugct_stpo[5]).'
              '.SelectText(32, $ugadmin[178], 'ctstpo6', $ugct_stpo[6], $panalign).'
            </table>
            </div>
          </fieldset>
        </div>
        <div id="uggt" class="w-100 m-auto" style="display:'.($param4 == 'unitegallery' && $param13 == 'uggt' ? 'block' : 'none').';"><hr />
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[20].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.SelectText(32, $ugadmin[179], 'gttmo1', $uggt_tmo[1], $panelpos2).'
              '.InputTexte(32, $ugadmin[180].' (480)', 'gttmo2', $uggt_tmo[2], 4).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[11].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputTexte(32, $ugadmin[25].' (900)', 'gtgo1', $uggt_go[1], 4).'
              '.InputTexte(32, $ugadmin[26].' (500)', 'gtgo2', $uggt_go[2], 4).'
              '.InputTexte(32, $ugadmin[27].' (400)', 'gtgo3', $uggt_go[3], 4).'
              '.InputTexte(32, $ugadmin[28].' (300)', 'gtgo4', $uggt_go[4], 4).'
              '.InputCheckbox(32, $ugadmin[29].' ('.$admin[1604].')', 'gtgo5', ($uggt_go[5] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[30].' (3000)', 'gtgo6', $uggt_go[6], 4).'
              '.GetSelector(32, $ugadmin[31], 'gtgo7', $uggt_go[7]).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[21].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.SelectText(32, $ugadmin[140], 'gtslo1', $uggt_slo[1], $transit).'
              '.SelectText(32, $ugadmin[141], 'gtslo2', $uggt_slo[2], $appears).'
              '.InputTexte(32, $ugadmin[142].' (300)', 'gtslo3', $uggt_slo[3], 4).'
              '.InputCheckbox(32, $ugadmin[145].' ('.$admin[1605].')', 'gtslo4', ($uggt_slo[4] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[146].' ('.$admin[1605].')', 'gtslo5', ($uggt_slo[5] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[147], 'gtslo6', $uggt_slo[6], $progress).'
              '.SelectText(32, $ugadmin[148], 'gtslo7', $uggt_slo[7], $textalign).'
              '.SelectText(32, $ugadmin[149], 'gtslo8', $uggt_slo[8], $vertalign).'
              '.GetSelector(32, $ugadmin[150].' (#FFFFFF)', 'gtslo9', $uggt_slo[9]).'
              '.InputNumber(32, $ugadmin[151].' (0.6)', 'gtslo10', 0, 1, $uggt_slo[10], 0.1).'
              '.InputCheckbox(32, $ugadmin[134].' ('.$admin[1605].')', 'gtslo11', ($uggt_slo[11] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[133].' ('.$admin[1605].')', 'gtslo12', ($uggt_slo[12] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[152].' ('.$admin[1605].')', 'gtslo13', ($uggt_slo[13] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[153].' ('.$admin[1605].')', 'gtslo14', ($uggt_slo[14] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[154].' ('.$admin[1605].')', 'gtslo15', ($uggt_slo[15] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[155].' ('.$admin[1605].')', 'gtslo16', ($uggt_slo[16] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[156].' ('.$admin[1604].')', 'gtslo17', ($uggt_slo[17] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[157], 'gtslo18', $uggt_slo[18], $vertpos2).'
              '.GetSelector(32, $ugadmin[67].' (#000000)', 'gtslo19', $uggt_slo[19]).'
              '.InputNumber(32, $ugadmin[66].' (0.4)', 'gtslo20', 0, 1, $uggt_slo[20], 0.1).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[22].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputTexte(32, $ugadmin[159].' (88)', 'gttho1', $uggt_tho[1], 4).'
              '.InputTexte(32, $ugadmin[160].' (50)', 'gttho2', $uggt_tho[2], 4).'
              '.InputCheckbox(32, $ugadmin[161].' ('.$admin[1605].')', 'gttho3', ($uggt_tho[3] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[162].' ('.$admin[1605].')', 'gttho4', ($uggt_tho[4] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[163].' (0)', 'gttho5', $uggt_tho[5], 4).'
              '.GetSelector(32, $ugadmin[164].' (#000000)', 'gttho6', $uggt_tho[6]).'
              '.InputTexte(32, $ugadmin[167].' (1)', 'gttho7', $uggt_tho[7], 4).'
              '.GetSelector(32, $ugadmin[168].' (#D9D9D9)', 'gttho8', $uggt_tho[8]).'
              '.InputTexte(32, $ugadmin[169].' (0)', 'gttho9', $uggt_tho[9], 4).'
              '.InputCheckbox(32, $ugadmin[170].' ('.$admin[1605].')', 'gttho10', ($uggt_tho[10] == 'true' ? true : false)).'
              '.GetSelector(32, $ugadmin[171].' (#000000)', 'gttho11', $uggt_tho[11]).'
              '.InputNumber(32, $ugadmin[172].' (0.4)', 'gttho12', 0, 1, $uggt_tho[12], 0.1).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[24].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputCheckbox(32, $ugadmin[181].' ('.$admin[1605].')', 'gtgpo1', ($uggt_gpo[1] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[179], 'gtgpo2', $uggt_gpo[2], $panalign).'
              '.SelectText(32, $ugadmin[182], 'gtgpo3', $uggt_gpo[3], $gridvert).'
              '.SelectText(32, $ugadmin[183], 'gtgpo4', $uggt_gpo[4], $gridhoriz).'
              '.SelectText(32, $ugadmin[184], 'gtgpo5', $uggt_gpo[5], $griddir).'
              '.InputTexte(32, $ugadmin[185].' (2)', 'gtgpo6', $uggt_gpo[6], 4).'
              '.InputTexte(32, $ugadmin[186].' (10)', 'gtgpo7', $uggt_gpo[7], 4).'
              '.InputTexte(32, $ugadmin[187].' (10)', 'gtgpo8', $uggt_gpo[8], 4).'
            </table>
            </div>
          </fieldset>
        </div>
        <div id="ugsl" class="w-100 m-auto" style="display:'.($param4 == 'unitegallery' && $param13 == 'ugsl' ? 'block' : 'none').';"><hr />
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[11].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.InputTexte(32, $ugadmin[25].' (900)', 'slgo1', $ugsl_go[1], 4).'
              '.InputTexte(32, $ugadmin[26].' (500)', 'slgo2', $ugsl_go[2], 4).'
              '.InputTexte(32, $ugadmin[27].' (400)', 'slgo3', $ugsl_go[3], 4).'
              '.InputTexte(32, $ugadmin[28].' (300)', 'slgo4', $ugsl_go[4], 4).'
              '.InputCheckbox(32, $ugadmin[29].' ('.$admin[1604].')', 'slgo5', ($ugsl_go[5] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[30].' (3000)', 'slgo6', $ugsl_go[6], 4).'
              '.GetSelector(32, $ugadmin[31], 'slgo7', $ugsl_go[7]).'
            </table>
            </div>
          </fieldset>
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[21].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.SelectText(32, $ugadmin[140], 'slslo1', $ugsl_slo[1], $transit).'
              '.SelectText(32, $ugadmin[141], 'slslo2', $ugsl_slo[2], $appears).'
              '.InputTexte(32, $ugadmin[142].' (300)', 'slslo3', $ugsl_slo[3], 4).'
              '.InputCheckbox(32, $ugadmin[145].' ('.$admin[1605].')', 'slslo4', ($ugsl_slo[4] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[146].' ('.$admin[1605].')', 'slslo5', ($ugsl_slo[5] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[147], 'slslo6', $ugsl_slo[6], $progress).'
              '.SelectText(32, $ugadmin[148], 'slslo7', $ugsl_slo[7], $textalign).'
              '.SelectText(32, $ugadmin[149], 'slslo8', $ugsl_slo[8], $vertalign).'
              '.GetSelector(32, $ugadmin[150].' (#FFFFFF)', 'slslo9', $ugsl_slo[9]).'
              '.InputNumber(32, $ugadmin[151].' (0.6)', 'slslo10', 0, 1, $ugsl_slo[10], 0.1).'
              '.InputCheckbox(32, $ugadmin[134].' ('.$admin[1604].')', 'slslo11', ($ugsl_slo[11] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[133].' ('.$admin[1604].')', 'slslo12', ($ugsl_slo[12] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[152].' ('.$admin[1604].')', 'slslo13', ($ugsl_slo[13] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[153].' ('.$admin[1605].')', 'slslo14', ($ugsl_slo[14] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[154].' ('.$admin[1605].')', 'slslo15', ($ugsl_slo[15] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[155].' ('.$admin[1604].')', 'slslo16', ($ugsl_slo[16] == 'true' ? true : false)).'
              '.InputCheckbox(32, $ugadmin[156].' ('.$admin[1605].')', 'slslo17', ($ugsl_slo[17] == 'true' ? true : false)).'
              '.SelectText(32, $ugadmin[157], 'slslo18', $ugsl_slo[18], $vertpos2).'
              '.GetSelector(32, $ugadmin[67].' (#000000)', 'slslo19', $ugsl_slo[19]).'
              '.InputNumber(32, $ugadmin[66].' (0.4)', 'slslo20', 0, 1, $ugsl_slo[20], 0.1).'
              '.InputCheckbox(32, $ugadmin[190].' ('.$admin[1605].')', 'slslo21', ($ugsl_slo[21] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[191].' (0)', 'slslo22', $ugsl_slo[22], 4).'
            </table>
            </div>
          </fieldset>
        </div>
        <div id="ugvg" class="w-100 m-auto" style="display:'.($param4 == 'unitegallery' && $param13 == 'ugvg' ? 'block' : 'none').';"><hr />
          <fieldset style="margin:8px 0;padding:6px 10px 10px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[20].'</h4></legend>
            <div class="table-responsive-md">
            <table class="table table-bordered w-100">';
    echo '
              '.SelectText(32, $ugadmin[188], 'vgto1', $ugvg_to[1], $themeskin).'
              '.InputCheckbox(32, $ugadmin[29].' ('.$admin[1604].')', 'vgto2', ($ugvg_to[2] == 'true' ? true : false)).'
              '.InputTexte(32, $ugadmin[189].' timeout (2000)', 'vgto3', $ugvg_to[3], 4).'
            </table>
            </div>
          </fieldset>
        </div>
      </fieldset>
      <div class="quest" style="padding:2px 20px;">
        <div style="float:right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div>
        <div id="bydef" style="float:right;margin-right:24px;padding-top:8px;display:'.($param4 == 'unitegallery' ? 'block' : 'none').';">
          <label for="def">'.$ugadmin[195].'</label><input type="checkbox" id="def" name="def" style="vertical-align:middle;" />
        </div>
        <div style="clear:both"></div>
      </div>
    </form>
    <a id="constructpanel"></a>
    <div id="ugconstruct" style="display:'.($param4 == 'unitegallery' ? 'block' : 'none').';"><hr />
      <fieldset style="margin:8px 0;padding:6px;"><legend style="margin-left:24px;"><h4>'.$ugadmin[16].'</h4></legend>
        <textarea style="width:100%;height:320px;margin:-12px 0 6px;" onfocus="if(window.CopyToClipboard(this)) alert(\''.$admin[910].'\');">'.constructUG($param13).'</textarea>
      </fieldset>
      <form name="themetest" action="'.CHEMIN.'photorama2.php?lng='.$lng.'&amp;pg='.$numgal.'&amp;tconfig=0#z2" target="_blank" method="POST">
        <div class="quest" style="padding:2px 20px;">
          <div style="float:right">'.SubmitButton($ugadmin[45], '', '', $imgdroite).'</div>
          <div style="clear:both"></div>
        </div>
      </form>
    </div>
  </fieldset>
</div>
<div style="max-width:680px;text-align:center;margin:auto;">
  <fieldset>
    <div class="quest" style="padding:2px 20px;">
      <div style="float:right">
        '.SubmitButton($admin[1268], 'self.location.href=\''.$baseurl.'&regit=4&act=3&numgal='.$numgal.'\'', '', $imgdroite).'
      </div>
      <div style="float:left">
        '.SubmitButton($admin[368], 'self.location.href=\''.$baseurl.'&act=4&numgal='.$numgal.'\'', $imggauche, '').'
      </div>
      <div style="clear:both"></div>
    </div>
  </fieldset>
</div>';
}
################################################################################
// sélectionner une galerie
elseif ($act == 2) {
	$topmess  = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[133]);
    include_once CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($admin[133], '100%');
    $lstgal = ReadAuthorizedIndex(DBPHOTO, $myname, $grpcol, $fctwri);
    sort($lstgal);
// sélection galerie en debut de gerer les photos
    echo '
<p class="mb-5">'.ToHelp($lng, '&pg=1042&mnuid=937&tconfig=0#z2').'</p>
<form name="galsel" action="'.$baseurl.'&amp;act='.$sel.'&amp;sel='.$sel.'" method="POST">
  <div class="w-100 mx-auto" style="max-width:380px;">
    <fieldset><legend style="margin-left:24px;">'.$admin[132].'</legend>
      <div class="table-responsive-md">
      <table class="table table-bordered" style="width:80%;margin:auto;">
        <tr>
		  <td style="text-align:center;">
            <select class="form-control w-100 mx-auto" name="numgal" style="max-width:300px;" onchange="submit();">
              <option value="" select="selected">'.$admin[1241].'</option>';
    foreach ($lstgal as $gal) {
		$cat  = $gal[$lng == $lang[0] ? 0 : 1];
		$pos  = strpos($cat, '-->');
		$cat  = substr($cat, ($pos !== false ? $pos + 3 : 0));
		$cat  = empty($cat) ? '' : $cat.' => ';
		$name = $gal[$lng == $lang[0] ? 2 : 3];
		$pos  = strpos($name, '-->');
		$name = substr($name, ($pos !== false ? $pos + 3 : 0));
		echo '
              <option value="'.$gal[4].'">'.$gal[4].' : '.$cat.$name.'</option>';
    }
    echo '
            </select>
          </td>
		</tr>
		<noscript>
        <tr>
		  <td style="text-align:center;">'.SubmitButton($admin[519]).'</td>
		</tr>
		</noscript>
      </table>
      </div>
    </fieldset>
  </div>
  <br />
</form>';
}
################################################################################
// gérer les photos
elseif ($act == 3) {

	$headinc .= BeginJavascript().'
function VerifyForm() {
    var sto    = "";
    var erreur = false;
	var mfu    = '.$mfu.';
	var files  = document.getElementById("new_photo").files;
	if (files.length > mfu) {
		erreur = true;
	}
    if (erreur == true) {
        sto = "'.addslashes(sprintf($admin[330], $mfu)).'" + sto;
        alert(sto);
        return false;
    }
}'.EndJavascript();

	$topmess  = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[133]);
    include_once CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';

    htable($admin[133], '100%');
	
    $nbresult = 10;
    echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=1040&mnuid=937&tconfig=0#z2').$admin[326].'<b>'.$numgal.' : '.$catgal.$nomgal.'</b>'.$admin[223].'</p>'.info_gd();
    if ($dirgal != '') {
        initGallery($dirgal);
        echo '
<hr />';
        echo '
<div style="max-width:640px;margin:auto auto 12px;">
  <fieldset>'.legend($admin[132]).'
    <form name="galsel1" action="'.$baseurl.'&amp;act='.$sel.'&amp;sel='.$sel.'" method="POST">
      <div class="table-responsive-md">
      <table class="table table-bordered w-80 m-auto">
        <tr>
		  <td style="text-align:center;">
            <select class="form-control" name="numgal" style="width:300px;" onchange="submit();">';
    $lstgal = ReadAuthorizedIndex(DBPHOTO, $myname, $grpcol, $fctwri);
    sort($lstgal);
    foreach ($lstgal as $gal) {
		$cat  = $gal[$lng == $lang[0] ? 0 : 1];
		$pos  = strpos($cat, '-->');
		$cat  = substr($cat, ($pos !== false ? $pos + 3 : 0));
		$cat  = empty($cat) ? '' : $cat.' => ';
		$name = $gal[$lng == $lang[0] ? 2 : 3];
		$pos  = strpos($name, '-->');
		$name = substr($name, ($pos !== false ? $pos + 3 : 0));
		echo '
              <option value="'.$gal[4].'"'.Selected($numgal == $gal[4]).'>'.$gal[4].' : '.$cat.$name.'</option>';
    }
    echo '
            </select>
          </td>
		</tr>
		<noscript>
        <tr>
		  <td style="text-align:center;">'.SubmitButton($admin[519]).'</td>
		</tr>
		</noscript>
      </table>
      </div>
    </form>
  </fieldset>
</div>
<div style="max-width:640px;text-align:center;margin:auto;">';
/*
max_file_uploads=20
$mfu      = @ini_get('max_file_uploads');

upload_max_filesize=256M
$maxsizefiles = @ini_get('upload_max_filesize');

post_max_size=256M
$maxpostsize = @ini_get('post_max_size');
*/
$messageinfo .= $admin[1831] . number_format(return_bytes($maxpostsizes),0,',',' ').'<br />';
$messageinfo .= $admin[1832] . number_format(return_bytes($maxsizefiles),0,',',' ').'<br />';
if ($messageinfurgent !='')
{
	echo '
<div class="alert alert-dark" role="alert">
  '.$messageinfurgent.'
</div>';
}
if ($messageinfo !='')
{
echo '
<div class="alert alert-primary" role="alert">
  '.$messageinfo.'
</div>';
}

echo '
  <fieldset>
    <form name="galvgnt" action="'.$baseurl.'&amp;act=1&amp;sel='.$sel.'&amp;numgal='.$numgal.'" method="POST">
      <div class="quest" style="padding:2px 20px;">
        <div style="float:right">'.SubmitButton(str_replace('Admin - ', '', $admin[631]), '', '', $imgdroite).'</div>
        <div style="clear:both"></div>
      </div>
    </form>
  </fieldset>
</div>';
// partie affichage ajouter photos

if (file_exists(PHOTOREP.$dirgal.'/params_'.$numgal.INCEXT))
	include PHOTOREP.$dirgal.'/params_'.$numgal.INCEXT;
	$messageinfo = '';
	$messageinfurgent = '';
echo '
<form name="galadd" action="'.$baseurl.'" method="POST" enctype="multipart/form-data" onsubmit="return VerifyForm(); return false;" >
  <input type="hidden" name="regit" value="2" />
  <input type="hidden" name="act" value="3" />
  <input type="hidden" name="messageinfo" value="'.$messageinfo.'" />
  <input type="hidden" name="messageinfurgent" value="'.$messageinfurgent.'" />
  <input type="hidden" name="sel" value="'.$sel.'" />
  <input type="hidden" name="numgal" value="'.$numgal.'" />
  <div style="max-width:640px;text-align:center;margin:auto;">
    <fieldset><legend style="margin-left:24px;">'.$admin[325].' ('.$dirgal.')</legend><br />
      <div class="table-responsive-md">
      <table class="table table-bordered w-100 m-auto">
        <tr>
	      <td class="pl-3 align-middle">&bull; '.sprintf($admin[330], $mfu).'</td>
          <td>';
// si galerie est unite gallery, videos et photos sont possibles
	$is_video = in_array($param13, $galleryvideos);

if ($is_video)
{
	if (version_compare(PHP_VERSION, '8.1.0') >= 0 && version_compare(PHP_VERSION, '8.2.0') < 0) {
		echo '  <div class="input-group mb-3"> 
				<input name="new_photo[]" id="new_photo" class="form-control" type="file" size="64" 
				alt="'.$admin[793].'" title="'.$admin[793].'" 
				  accept="image/jpg,image/jpeg,image/png,image/webp, image/avif, video/*"  multiple="multiple" />';
	}
	else
	{
		echo '  <div class="input-group mb-3"> 
		<input name="new_photo[]" id="new_photo" class="form-control" type="file" size="64" 
		alt="'.$admin[793].'" title="'.$admin[793].'" 
		  accept="image/jpg,image/jpeg,image/png,image/webp, video/*"  multiple="multiple" />';
	}
	if ($lng != 'fr')
		echo '
			<label class="input-group-text" for="new_photo">'.$admin[793].'</label>';
	echo '
		 </div>';
}
else
{
	if (version_compare(PHP_VERSION, '8.1.0') >= 0 && version_compare(PHP_VERSION, '8.2.0') < 0) {
		echo '  <div class="input-group mb-3"> 
				<input name="new_photo[]" id="new_photo" class="form-control" type="file" size="64" 
				alt="'.$admin[793].'" title="'.$admin[793].'" 
					 accept="image/jpg,image/jpeg,image/png,image/webp, image/avif" multiple="multiple" />';
	}
	else
		echo '  <div class="input-group mb-3"> 
						<input name="new_photo[]" id="new_photo" class="form-control" type="file" size="64" 
						alt="'.$admin[793].'" title="'.$admin[793].'" 
							 accept="image/jpg,image/jpeg,image/png,image/webp" multiple="multiple" />';		
	if ($lng != 'fr')
		echo '
			<label class="input-group-text" for="new_photo">'.$admin[793].'</label>';
	echo '
			 </div>';
}
echo '
          </td>
	    </tr>
      </table>
      </div>
      <br />
    <div class="quest" style="padding:2px 20px;">
      <div style="float:right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div>
      <div style="clear:both"></div>
    </div>
    </fieldset>
  </div>
</form>
<div style="max-width:640px;text-align:center;margin:auto;">
  <fieldset>
    <form name="galvgnt" action="'.$baseurl.'" method="POST">
      <input type="hidden" name="regit" value="4" />
      <input type="hidden" name="act" value="3" />
      <input type="hidden" name="numgal" value="'.$numgal.'" />
      <div class="quest" style="padding:2px 20px;">
        <div style="float:right">'.SubmitButton($admin[1268], '', '', $imgdroite).'</div>
        <div style="clear:both"></div>
      </div>
    </form>
  </fieldset>
</div>';
        if (file_exists(PHOTOREP.$dirgal.'/params_'.$numgal.INCEXT))
            include PHOTOREP.$dirgal.'/params_'.$numgal.INCEXT;
        $dbphoto = ReadDBFields(PHOTOREP.$dirgal.'/'.$dirgal.DBEXT);
        $tmpi    = 0;
        $tmp[0]  = array();
        $tmp[1]  = array();
        $tmp[2]  = array();
        $tmp[3]  = array();
        $tmp[4]  = array();
        $tmp[5]  = array();
        $tmp[7]  = array();
        $tmp[8]  = array();
        $tmp[9]  = array(); // adresse du lien image
        $tmp[10] = array(); // type de vidéo
        $tmp[11] = array(); // code ou adresse de la vidéo
		$tmp[12] = array(); // No ordre affichage
        for ($i = 0; $i < count($dbphoto); $i++) {
            if ($dbphoto[$i][1] != 'd') {
                $tmp[0][$tmpi] = $dbphoto[$i][0];
                $tmp[1][$tmpi] = $dbphoto[$i][1];
                $tmp[2][$tmpi] = $dbphoto[$i][2];
                $tmp[3][$tmpi] = $dbphoto[$i][3];
                $tmp[4][$tmpi] = $dbphoto[$i][4];
                $tmp[5][$tmpi] = '<tr><td style="vertical-align:bottom;">'.$flag1.' '.$dbphoto[$i][5].'</td></tr>';
                if ($lang[1] != '')
                    $tmp[5][$tmpi] .= '<tr><td style="">'.$flag2.' '.$dbphoto[$i][6].'</td></tr>';
                $tmp[7][$tmpi]  = $dbphoto[$i][7];
                $tmp[8][$tmpi]  = $dbphoto[$i][8];
                $tmp[9][$tmpi]  = @$dbphoto[$i][9];
                $tmp[10][$tmpi] = @$dbphoto[$i][10];
                $tmp[11][$tmpi] = @$dbphoto[$i][11];
				$tmp[12][$tmpi] = @$dbphoto[$i][12]; // No ordre affichage
                $tmpi++;
            }
        }
        if (empty($idpg)) {
          $idpg = 1;
        }
        else {
          while ($tmpi <= ($idpg - 1)* $nbresult) $idpg--;
        }
        if ($tmpi > 0) {
            $navbar = GetNavBar($baseurl.'&amp;act=3&amp;numgal='.$numgal.'&amp;idpg=', $tmpi, $idpg, $nbresult);
            echo '
<div style="text-align:center;max-width:640px;margin: auto;">
  <fieldset class="m-0"><legend style="margin-left:24px;">'.$admin[633].' ('.$dirgal.')</legend>'.$navbar.'
    <form name="fast_actions" action="'.$baseurl.'&amp;idpg='.$idpg.'&amp;numgal='.$numgal.'&amp;act=fast" method="post">
      <div class="table-responsive-md">
      <table class="table table-bordered" w-100">
        <tr>
          <td colspan="3">
            <div style="text-align:right; padding-right:24px;">'.SubmitButton($admin[1602]).'
              <i class="fas fa-level-down-alt"></i>
            </div>
          </td>
        </tr>
        <tr class="forum">
          <td style="padding:4px;">'.substr($admin[834], 0, -1).'</td><td style="padding:4px;">'.$admin[679].'</td><td>'.$admin[680].'</td>
	    </tr>';
            $ids   = '';
            $nbdel = 0;
            for ($i = $nbresult*($idpg-1); $i < Min($nbresult*$idpg, $tmpi); $i++) {			
                $fileid = $tmp[0][$i];
                $statut = $tmp[1][$i];
                echo '
        <tr class="'.($i %2 == 0 ? 'rep' : 'quest').'">';
				$wmin = $tmp[3][$i] >= $tmp[4][$i] ? 128 : 128 * $tmp[3][$i] / $tmp[4][$i];
                echo '
          <td  style="padding:4px;width:128px; text-align:center;">
            <a href="'.$baseurl.'&amp;act=4&amp;numgal='.$numgal.'&amp;idpg='.$idpg.'">
              <img src="'.PHOTOREP.$dirmin.'/'.$tmp[2][$i].'" style="width:'.$wmin.'px;vertical-align:bottom;" alt="'.$admin[368].'" title="'.$admin[368].'" />
            </a>
          </td>
		  <td style="padding:4px;">
            <div class="table-responsive-md">
            <table class="table table-bordered">
              <tr>
                <td style="padding:2px;">
                  &bull; '.$tmp[2][$i].' ('.$tmp[7][$i].'x'.$tmp[8][$i].')'.($param4 == 'unitegallery' && !empty($tmp[10][$i]) ? ' => <b>'.$typvideo[$tmp[10][$i]].'</b>' : '').'
                </td>
              </tr>
			  <tr><td>'.$admin[1843].' : '.$tmp[9][$i].'</td></tr>
			  <tr><td>'.$tmp[5][$i].'</td></tr>
            </table>
            </div>
		  </td>';
                echo '
          <td  style="padding:4px;width:60px; white-space:nowrap;text-align:center;">';
                if ($statut == 'a') {
                    MakeRadioGroup(array( array('a', 'i', $fileid), array('a', 'd', $fileid) ));
                }
                elseif ($statut == 'i') {
                    MakeRadioGroup(array( array('i', 'a', $fileid), array('i', 'd', $fileid) ));
                }
                elseif ($status == 'd') {
                    MakeRadioGroup(array( array('d', 's', $fileid), array('d', 'i', $fileid) ));
                }
                echo '
          </td>
	    </tr>';
                $ids .= $fileid.' ';
            }
            echo '
        <tr>
          <td colspan="3">
            <div style="text-align:right; padding-right:24px;">'.SubmitButton($admin[1602]).'
              <i class="fas fa-level-up-alt"></i>
            </div>
          </td>
        </tr>
      </table>
      </div>
      <input type="hidden" name="ids" value="'.$ids.'" />
    </form>'.$navbar.'
  </fieldset>
</div>';
        } else {
            echo '
  <p style="text-align:center"><strong>'.$admin[854].'</strong><br />';
        }
        $tmpi2    = 0;
        $tmp2[0]  = array();
        $tmp2[1]  = array();
        $tmp2[2]  = array();
        $tmp2[3]  = array();
        $tmp2[4]  = array();
        $tmp2[5]  = array();
        $tmp2[7]  = array();
        $tmp2[8]  = array();
        $tmp2[9]  = array();
        $tmp2[10] = array();
        $tmp2[11] = array();
        for ($i = 0; $i < count($dbphoto); $i++) {
            if ($dbphoto[$i][1] == 'd') {
                $tmp2[0][$tmpi2] = $dbphoto[$i][0];
                $tmp2[1][$tmpi2] = $dbphoto[$i][1];
                $tmp2[2][$tmpi2] = $dbphoto[$i][2];
                $tmp2[3][$tmpi2] = $dbphoto[$i][3];
                $tmp2[4][$tmpi2] = $dbphoto[$i][4];
                $tmp2[5][$tmpi2] = '<tr><td style="vertical-align:top;width:20px;">'.$flag1.'</td><td>'.$dbphoto[$i][5].'</td></tr>';
                if ($lang[1] != '')
                    $tmp2[5][$tmpi2] .= '<tr><td style="vertical-align:top;width:20px;">'.$flag2.'</td><td>'.$dbphoto[$i][6].'</td></tr>';
                $tmp2[7][$tmpi]  = $dbphoto[$i][7];
                $tmp2[8][$tmpi]  = $dbphoto[$i][8];
                $tmp2[9][$tmpi]  = $dbphoto[$i][9];
                $tmp2[10][$tmpi] = $dbphoto[$i][10];
                $tmp2[11][$tmpi] = $dbphoto[$i][11];
                $tmpi2++;
            }
        }
        if ($tmpi2 > 0) {
            $cr = array_multisort($tmp2[2], SORT_ASC, $tmp2[0], $tmp2[1], $tmp2[3], $tmp2[4], $tmp2[5]);
            echo '
<div style="text-align:center;width: 100%; max-width: 640px; margin: auto;"><br />
  <fieldset class="m-0"><legend style="margin-left:24px;">'.$admin[170].'</legend>
    <form name="fast_actions2" action="'.$baseurl.'&amp;idpg='.$idpg.'&amp;numgal='.$numgal.'&amp;act=fast2" method="post">
      <div class="table-responsive-md">
      <table class="table table-bordered w-100">
        <tr>
          <td colspan="3">
            <div style="text-align:right; padding-right:24px;">'.SubmitButton($admin[1602]).'
              <i class="fas fa-level-down-alt"></i>
            </div>
          </td>
        </tr>
        <tr class="forum">
          <td class="align-middle">'.substr($admin[834], 0, -1).'</td>
          <td class="align-middle">'.$admin[679].'</td>
          <td class="align-middle">'.$admin[680].'</td>
		</tr>';
            $ids2 = '';
            for ($i = 0; $i < $tmpi2; $i++) {
                $fileid = $tmp2[0][$i];
                echo '
        <tr class="'.($i %2 == 0 ? 'rep' : 'quest').'">';
				$wmin = $tmp2[3][$i] >= $tmp2[4][$i] ? 128 : 128 * $tmp2[3][$i] / $tmp2[4][$i];
                echo '
          <td  style="padding:4px;width:128px; text-align:center;">
            <img src="'.PHOTOREP.$dirmin.'/'.$tmp2[2][$i].'" style="width:'.$wmin.'px;vertical-align:bottom;" alt="'.$admin[368].'" title="'.$admin[368].'" />
          </td>
		  <td style="padding:4px;">
            <div class="table-responsive-md">
            <table cellpadding="0" cellspacing="2" width="100%" summary="">
              <tr><td colspan="2">&bull; '.$tmp2[2][$i].' ('.$tmp2[3][$i].'x'.$tmp2[4][$i].')</td></tr>'.$tmp2[5][$i].'
            </table>
            </div>';
                echo '
          <td  style="padding:4px;width:60px; white-space:nowrap;text-align:center;">';
                MakeRadioGroup(array( array('d', 'i', $fileid), array('d', 's', $fileid)));
                echo '
          </td>
		</tr>';
                $ids2 .= $fileid.' ';
            }
            echo '
        <tr>
          <td colspan="3">
            <div style="text-align:right; padding-right:24px;">'.SubmitButton($admin[1602]).'
              <i class="fas fa-level-up-alt"></i>
            </div>
          </td>
        </tr>
      </table>
      </div>
      <input type="hidden" name="ids2" value="'.$ids2.'" />
    </form>
  </fieldset>
</div>';
        }
    }
}
################################################################################
// ajouter des commentaires
elseif ($act == 4) {
	$topmess  = '<a href="admin.php?lng='.$lng.'">Admin</a> > <a href="'.$baseurl.'&amp;idpg='.$idpg.'&amp;numgal='.$numgal.'&amp;act=3">'.str_replace('Admin - ', '', $admin[1036]).'</a> > '.str_replace('Admin - ', '', $admin[368]);
    include_once CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($admin[133], '100%');
	
	if (file_exists(PHOTOREP.$dirgal.'/params_'.$numgal.INCEXT))
		include PHOTOREP.$dirgal.'/params_'.$numgal.INCEXT;
	$is_video = in_array($param13, $galleryvideos);
    $nbresult = 10;
    echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=1040&mnuid=937&tconfig=0#z2').$admin[326].'<b>'.$numgal.' : '.$catgal.$nomgal.'</b>'.$admin[223].'</p>'.info_gd();
    $dbphoto = ReadDBFields(PHOTOREP.$dirgal.'/'.$dirgal.DBEXT);
    $tmpi3    = 0;
    $tmp3[0]  = array();
    $tmp3[1]  = array();
    $tmp3[2]  = array();
    $tmp3[3]  = array();
    $tmp3[4]  = array();
    $tmp3[5]  = array();
    $tmp3[6]  = array();
    $tmp3[7]  = array();
    $tmp3[8]  = array();
    $tmp3[9]  = array(); // adresse du lien image
    $tmp3[10] = array(); // type de vidéo
    $tmp3[11] = array(); // code ou adresse de la vidéo
	$tmp3[12] = array(); // no ordre affichage
    for ($i = 0; $i < count($dbphoto); $i++) {
        $tmp3[0][$tmpi3]  = $dbphoto[$i][0];
        $tmp3[1][$tmpi3]  = $dbphoto[$i][1];
        $tmp3[2][$tmpi3]  = $dbphoto[$i][2];
        $tmp3[3][$tmpi3]  = $dbphoto[$i][3];
        $tmp3[4][$tmpi3]  = $dbphoto[$i][4];
        $tmp3[5][$tmpi3]  = $dbphoto[$i][5];
        $tmp3[6][$tmpi3]  = $dbphoto[$i][6];
        $tmp3[7][$tmpi3]  = $dbphoto[$i][7];
        $tmp3[8][$tmpi3]  = $dbphoto[$i][8];
        $tmp3[9][$tmpi3]  = $dbphoto[$i][9];
        $tmp3[10][$tmpi3] = $dbphoto[$i][10];
        $tmp3[11][$tmpi3] = $dbphoto[$i][11];
		if (!isset($dbphoto[$i][12])) $dbphoto[$i][12] = 9999; //ordre affichage; //ordre affichage
		$tmp3[12][$tmpi3] = $dbphoto[$i][12]; //ordre affichage
        $tmpi3++;
    }
    if (empty($idpg)) {
      $idpg = 1;
    }
    else {
      while ($tmpi3 <= ($idpg - 1)* $nbresult) $idpg--;
    }
    echo '
<div style="max-width:740px;text-align:center;margin:auto;">
  <fieldset>
    <div class="quest" style="padding:2px 20px;">
      <div style="float:right">
        '.SubmitButton(str_replace('Admin - ', '', $admin[631]), 'self.location.href=\''.$baseurl.'&act=1&sel='.$sel.'&numgal='.$numgal.'\'', '', $imgdroite).'
      </div>
      <div style="float:left">
        '.SubmitButton($admin[1036], 'self.location.href=\''.$baseurl.'&idpg='.$idpg.'&act=3&sel='.$sel.'&numgal='.$numgal.'\'', $imggauche, '').'
      </div>
      <div style="clear:both"></div>
    </div>
  </fieldset>
</div>';
    if ($tmpi3 > 0) {
        $navbar = GetNavBar($baseurl.'&amp;act=4&amp;numgal='.$numgal.'&amp;idpg=', $tmpi3, $idpg, $nbresult);
        echo '
<div style="text-align:center;max-width:740px;margin:auto;">';
if ($messageinfo !='')
{
echo '
<div class="alert alert-primary" role="alert">
  '.$messageinfo.'
</div>';
}
echo '
  <fieldset class="m-0"><legend style="margin-left:24px;">'.$admin[368].' ('.$dirgal.')</legend>'.$navbar.'
    <form name="fast_comments" action="'.$baseurl.'&amp;idpg='.$idpg.'&amp;numgal='.$numgal.'&amp;act=4&amp;sel='.$sel.'" method="post">
      <input type="hidden" name="regit" value="3" />
	  <input type="hidden" name="messageinfo" value="'.$admin[1826].'" />
	  <input type="hidden" name="messageinfurgent" value="'.$messageinfurgent.'" />
      <div class="quest" style="padding:2px 20px;">
        <div style="float:right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div>
        <div style="clear:both"></div>
      </div><br />
      <div class="table-responsive-md">
      <table class="table table-bordered w-100">
        <tr class="forum">
          <td style="padding:4px;">'.substr($admin[834], 0, -1).'</td><td style="padding:4px;">'.$admin[679].'</td>
	    </tr>';
        $ids3  = '';
        $nbdel = 0;
        for ($i = $nbresult*($idpg-1); $i < Min($nbresult*$idpg, $tmpi3); $i++) {
            $fileid = $tmp3[0][$i];
            if ($tmp3[1][$i] != 'd') {
                echo '
        <tr class="'.($i %2 == 0 ? 'rep' : 'quest').'">';
                $wmin = $tmp3[3][$i] >= $tmp3[4][$i] ? 128 : 128 * $tmp3[3][$i] / $tmp3[4][$i];
                $wmax = max($tmp3[7][$i], $tmp3[8][$i]);
				if ($wmax == '') $wmax=10;
                echo '
          <td  style="padding:4px;width:128px; text-align:center;">
            <a data-target="#myModal'.$i.'" data-toggle="modal">
              <img src="'.PHOTOREP.$dirmin.'/'.$tmp3[2][$i].'" style="width:'.$wmin.'px;vertical-align:bottom;" alt="'.$admin[368].'" title="'.$admin[368].'" />
            </a>
            <div class="modal fade" id="myModal'.$i.'" role="dialog">
              <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content" style="width:initial;background:rgba(0,0,0,0.5);">
                  <button style="text-align:right;margin:2px 8px 0 -8px;color:#fff;" class="close" data-dismiss="modal" type="button">X</button>
                  <div class="modal-body">
                    <img src="'.PHOTOREP.$dirmin.'/'.$tmp3[2][$i].'" style="height:auto;width:'.round($wmin).'px;vertical-align:bottom;" alt="'.$admin[368].'" title="'.$admin[368].'" />
                  </div>
                </div>
              </div>
            </div>
          </td>
          <td  style="padding:0 8px;">
            '.$tmp3[2][$i].' ('.$tmp3[7][$i].'x'.$tmp3[8][$i].') &nbsp;&nbsp;&nbsp;&nbsp;';
			if ($tmp3[9][$i] == '') $tmp3[9][$i] = '9999';
			   echo '
					<br />			 
					<div class="d-inline-block">'.$admin[1843].'</div>
					<div class="d-inline-block "><input class="form-control texte" type="text" name="nordre_'.$fileid.'" value="'.$tmp3[9][$i].'" size="5" /></div><br/>';

			if ($param4 == 'unitegallery') {
                if ($is_video) {
					$mrnomfile = 'videolink_'.$fileid;
                    echo $ugadmin[192].'
            <select class="form-control" name="typevideo_'.$fileid.'">';
                    foreach($typvideo as $key => $lblvideo) {
                        echo '
              <option value="'.$key.'"'.Selected($tmp3[10][$i] == $key).'>'.$lblvideo.'</option>';
                    }
                    echo '
            </select><br />
            '.$ugadmin[194].'		
			<div class="d-inline-block">
				<input id="videolink_'.$fileid.'" name="videolink_'.$fileid.'"  class="form-control texte" type="text" size="95" value="'.$tmp3[11][$i].'" />
			</div>
			<div class="d-inline-block">
				<input type="button" class="btn btn-info bouton" name="browserver3" id="browserver3" 
				  onclick="PopupWindow(\''.CHEMIN.'admin/editors/upload/upload.php?lng='.$lng.
					'&uptype=Media&namerepconfig=guppy_config&pathconfig=admin/editors/\',\''.$mrnomfile.'\',\'700\',\'515\', \'yes\');" 
				  value="'.$admin[793].'" />
			</div>';
                } 
/*
				else {
                    echo '<br />'.$ugadmin[193].'<input name="imglink_'.$fileid.'" class="form-control texte" type="text" size="72" value="'.$tmp3[9][$i].'" />';
                }
*/
            }
		echo '
			<br />			 
              <div class="d-inline-block">'.$flag1.'</div>
              <div class="d-inline-block w-90"><input class="form-control texte" type="text" name="comment1_'.$fileid.'" value="'.$tmp3[5][$i].'" /></div>
            </div>';
            if ($lang[1] != "") {
                echo '
            <div>
              <div class="d-inline-block">'.$flag2.'</div>
              <div class="d-inline-block w-90"><input class="form-control texte" type="text" name="comment2_'.$fileid.'" value="'.$tmp3[6][$i].'" /></div>
            </div>';
            }
                echo '
		  </td>
	    </tr>';
            $ids3 .= $fileid.' ';
            }
        }
        echo '
      </table>
      </div>
      <br />
      <input type="hidden" name="ids3" value="'.$ids3.'" />
      <div class="quest" style="padding:2px 20px;">
        <div style="float:right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div>
        <div style="clear:both"></div>
      </div>
    </form>'.$navbar.'
  </fieldset>
</div>';
    }
    echo '
<div style="max-width:740px;text-align:center;margin:auto;">
  <fieldset>
    <div class="quest" style="padding:2px 20px;">
      <div style="float:right">
        '.SubmitButton(str_replace('Admin - ', '', $admin[631]), 'self.location.href=\''.$baseurl.'&act=1&sel='.$sel.'&numgal='.$numgal.'\'', '', $imgdroite).'
      </div>
      <div style="float:left">
        '.SubmitButton($admin[1036], 'self.location.href=\''.$baseurl.'&idpg='.$idpg.'&act=3&sel='.$sel.'&numgal='.$numgal.'\'', $imggauche, '').'
      </div>
      <div style="clear:both"></div>
    </div>
  </fieldset>
</div>
</div>';
$messageinfo = $admin[1826];
}
btable();
include CHEMIN.'admin/bpage.inc';
?>