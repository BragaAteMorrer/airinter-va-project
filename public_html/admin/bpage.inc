<?php
/*******************************************************************************
 *   Site Bottom & Right of Page
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
 * v6.00.17 (January 23, 2024) : add Opcache code
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'bpage.inc')) {
    header('location:../index.php');
    die();
}
if (function_exists('opcache_get_configuration'))
{
	// Ces Lignes ne peuvent être mises qu'à la fin du script
	// Clear du cache et restore de opcache.enable
	if ($site[7] == 'on')
	{
		if (OPCACHEDISABLE == 1) //cache enable voir constantes
		{
			if (OPCACHEINISET == 1) // Gestion des options caches enable/disable
			{	
				if (ini_get('opcache') == 1) 
				{
					ini_set('opcache.enable', '0');
//					echo ' OPcache désactivé fait<br />';
				}
				else
				 ini_restore('opcache.enable');				
			}
		}
		/*
		   Clear du cache disque géré par PHP
		*/
		clearstatcache();
		// Invalidation du fichier contenant les constantes
		if (function_exists('opcache_invalidate')) {
		  opcache_invalidate(CHEMIN.INCREP.'constantes.php', TRUE);
		}
	}
}
if ($lng == 'fr') {
	$txtspe = '
  &nbsp;
  <a class="copyright" href="https://www.freeguppy.org/" title="freeguppy.org" target="_blank">
	'.($site[35] == 'T' ? 'Propulsé par GuppY' : '<i class="gen-gypower"></i>').'
  </a>
  &nbsp; '.html_entity_decode('© '.MP_COPYRIGHT, ENT_COMPAT, $charset).' &nbsp;
  <a class="copyright" href="http://cecill.info/index.fr.html" title="En savoir plus ..." target="_blank">
	'.($site[35] == 'T' ? 'Sous Licence Libre CeCILL' : '<i class="gen-gycecill-f align-middle" title="More info ..."></i>').'
  </a>
  &nbsp;';
}
else {
	$txtspe = '
  &nbsp;
  <a class="copyright" href="https://www.freeguppy.org/" title="freeguppy.org" target="_blank">
	'.($site[35] == 'T' ? 'Powered by GuppY' : '<i class="gen-gypower align-middle" title="freeguppy.org"></i>').'
  </a>
  &nbsp; '.html_entity_decode('© '.MP_COPYRIGHT, ENT_COMPAT, $charset).' &nbsp;
  <a class="copyright" href="http://cecill.info/index.en.html" title="More info ..." target="_blank">
	'.($site[35] == 'T' ? 'Under Free License CeCILL' : '<i class="gen-gycecill-f align-middle" title="More info ..."></i>').'
  </a>
  &nbsp;';
}

echo '
    <!-- Begin of ADMIN BOTTOM_BOXES -->
    <div style="margin-bottom:20px;">';
if (!empty($admcookie)) echo '
	  <div style="text-align:center; font-size: 14px;">
	    <a href="#top">
		  <i class="fas fa-chevron-circle-up"></i>
		  <b> '.$admin[1583].' </b>
		  <i class="fas fa-chevron-circle-up"></i>
	    </a>
	  </div>
      <div class="table-responsive-md">
      <table class="bord w-98 m-auto rounded-pill fs-14">
        <tr>
          <td class="col0 w-85">'.@ExecFunctionBox(BOX_BOTTOM, '||fbox_ariane||W12').'</td>
          <td class="col3 w-15 text-center">
            <strong><a href="admin.php?lng='.$lng.'&amp;logout=1">'.$admin[19].'</a></strong>
          </td>
        </tr>
      </table>
      </div>
      ';
echo '
      <div style="text-align:center;">'.$txtspe.'</div>
    </div>
    <!-- End of ADMIN BOTTOM_BOXES -->
  </div>
	';
    if ($page[8] == 'on') {
        $timer = StopTimer();
        echo '
        <div class="timer">
          '.$web[116].' '.$timer.' '.($timer <= 1 ? $web[117] : $web[118]).'
        </div>';
    }
echo '
</div>
<!-- end page -->

</body>
</html>';
?>
