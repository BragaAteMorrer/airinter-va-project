<?php
/*******************************************************************************
 *   Skin functions
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

function htable($tblti, $largeur) {
    echo '
<!-- Begin of Main Article -->
<article>
<h1 class="titre">'.$tblti.'</h1>
<div class="tbl" onmouseover="this.className = \'tbl tblover\'" onmouseout="this.className = \'tbl\'">';
}

function htable1($tblti, $class, $largeur) {
    $tclass = empty($class) ? '' : ' Tr'.trim($class);
    echo '
<!-- Begin of Main Article -->
<article>
<h1 class="titre'.$tclass.'">'.$tblti.'</h1>
<div class="tbl" onmouseout="this.className = \'tbl\'" onmouseover="this.className = \'tbl tblover\'">';
}

function btable() {
    echo '
</div>
</article> <!-- End of Main Article -->';
}

function boxwide($pos, $title, $content, $class='', $tag='div') {
    global $boxid, $tconfig;
	$boxid++;
    if (empty($title)) {
        return '
<'.$tag.' class="w-100 px-2 tbl'.$class.'" id="'.$pos.$tconfig.'tbl'.$boxid.'" 
  onmouseover="this.className = \'w-100 px-2 tbl'.$class.' tblover\'" onmouseout="this.className = \'w-100 px-2 tbl'.$class.'\'">
  '.$content.'
</'.$tag.'>
';
    } else {
		$tclass = empty($class) ? '' : ' Tr'.trim($class);
        return '
<'.$tag.'>
  <h1 class="w-100 px-2 titre'.$tclass.'" id="'.$pos.$tconfig.'titre'.$boxid.'">'.$title.'</h1>
  <div class="w-100 px-2 tbl'.$class.'" id="'.$pos.$tconfig.'tbl'.$boxid.'" 
    onmouseover="this.className = \'w-100 px-2 tbl'.$class.' tblover\'" onmouseout="this.className = \'w-100 px-2 tbl'.$class.'\'">
    '.$content.'
  </div>
</'.$tag.'>
';
    }
}

function boxthin($pos, $title, $content, $class='', $dynbox='off', $tag='div') {
    global $boxid, $page, $tconfig;
	$boxid++;
    if (empty($title)) {
        return '
<'.$tag.' class="w-100 px-2 tblbox'.$class.'" id="'.$pos.$tconfig.'tbl'.$boxid.'" 
  onmouseover="this.className=\'w-100 px-2 tblbox'.$class.' tblboxover\'" onmouseout="this.className=\'w-100 px-2 tblbox'.$class.'\'">
  '.$content.'
</'.$tag.'>
';
    } else {
		$tclass = empty($class) ? '' : ' Tr'.trim($class);
		if ($dynbox == 'off' || $dynbox == '')
			$out = '
<style>
  .curtitre { cursor: inherit; }
</style>
<h1 class="w-100 px-2 titrebox'.$tclass.' curtitre" id="'.$pos.$tconfig.'titrebox'.$boxid.'">'.$title.'</h1>';
		else
			$out = '
<h1 class="titrebox'.$tclass.'" id="'.$pos.$tconfig.'titrebox'.$boxid.'" 
  onclick="var div=getElementById(\''.$pos.$tconfig.'tblbox'.$boxid.'\'); 
  if (div.style.display == \'none\') { montre(\''.$pos.$tconfig.'tblbox'.$boxid.'\'); getElementById(\'Ar'.trim($class).'\').innerHTML = \'&#xe808;\'; } 
  else { cache(\''.$pos.$tconfig.'tblbox'.$boxid.'\'); getElementById(\'Ar'.trim($class).'\').innerHTML = \'&#xe807;\'; }">'.$title.'
</h1>';
        $out .= '
<div class="w-100 px-2 tblbox'.$class.'" id="'.$pos.$tconfig.'tblbox'.$boxid.'" 
  onmouseover="this.className=\'w-100 px-2 tblbox'.$class.' tblboxover\'" onmouseout="this.className=\'w-100 px-2 tblbox'.$class.'\'">
  '.$content;
		$out .= '
</div>';
		if (!empty($title) && ($dynbox == 'open' || $dynbox == 'close')) {
			if ($dynbox == 'open')
				$out .= '<script>montre(\''.$pos.$tconfig.'tblbox'.$boxid.'\');</script>';
			else
				$out .= '<script>cache(\''.$pos.$tconfig.'tblbox'.$boxid.'\');</script>';
		}
		return '
    <'.$tag.'>
      '.$out.'
    </'.$tag.'>
        ';
    }
}
?>