<?php
/*******************************************************************************
 *   RSS Feeds
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

header('Pragma: no-cache');
define('CHEMIN', '');
include CHEMIN.'inc/includes.inc';

$topmess = $web[617];
$tconfig = import('tconfig', '', true, 0);
include 'inc/hpage.inc';
if (function_exists('htable1'))
    htable1($topmess, 'RSS'.$tconfig, '100%');
else
    htable($topmess, '100%');

if ($prt != -1 && $prt != 1) {
	echo sprintf($web[618], stripslashes($site[($lang[0] == $lng ? 0 : 11)])).'
<div class="table-responsive-md">
<div class="bord d-table m-auto w-90">';
	if ($serviz[60] == 'on') {
		$rssurl = $site[3].sprintf(RSSART, $lng);
		echo '
	<div class="d-table-row mt-2">
	  <div class="d-table-cell align-middle w-50 text-right itemrss">'.$web[169].'</div>
	  <div class="d-table-cell align-middle w-50 text-left pl-3 itemrss">
        <a href="'.CHEMIN.'newsrss.php?lng='.$lng.'&url=arts&tconfig='.$tconfig.$z2.'">
		  <i class="fas fa-rss"></i>
		</a>
	  </div>
	</div>';
	}
	if ($serviz[54] == 'on') {
		$rssurl = $site[3].sprintf(RSSBLOG, $lng);
		echo '
	<div class="d-table-row mt-2">
	  <div class="d-table-cell align-middle w-50 text-right itemrss">'.getLabel($nom[$lng == $lang[0] ? 42 : 43]).'</div>
	  <div class="d-table-cell align-middle w-50 text-left pl-3 itemrss">
        <a href="'.CHEMIN.'newsrss.php?lng='.$lng.'&url=blog&tconfig='.$tconfig.$z2.'">
		  <i class="fas fa-rss"></i>
		</a>
	  </div>
	</div>';
	}
	if ($serviz[16] == 'on') {
		$rssurl = $site[3].sprintf(RSSNEWS, $lng);
		echo '
	<div class="d-table-row mt-2">
	  <div class="d-table-cell align-middle w-50 text-right itemrss">'.getLabel($nom[$lng == $lang[0] ? 7 : 17]).'</div>
	  <div class="d-table-cell align-middle w-50 text-left pl-3 itemrss">
        <a href="'.CHEMIN.'newsrss.php?lng='.$lng.'&url=news&tconfig='.$tconfig.$z2.'">
		  <i class="fas fa-rss"></i>
		</a>
	  </div>
	</div>';
	}
	if ($serviz[103] == 'on') {
		$rssurl = $site[3].sprintf(RSSALL, $lng);
		echo '
	<div class="d-table-row mt-2">
	  <div class="d-table-cell align-middle w-50 text-right itemrss">'.$web[619].'</div>
	  <div class="d-table-cell align-middle w-50 text-left pl-3 itemrss">
        <a href="'.CHEMIN.'newsrss.php?lng='.$lng.'&url=all&tconfig='.$tconfig.$z2.'">
		  <i class="fas fa-rss"></i>
		</a>
	  </div>
	</div>';
	}
	echo '
</div>
</div>
<br />
<hr />';
}

btable();
include 'inc/bpage.inc';
?>