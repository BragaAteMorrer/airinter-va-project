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
 * v6.00.18 (February 11, 2024) : add Opcache code
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
		if (OPCACHEDISABLE == 1)
		{
			if (OPCACHEINISET == 1) // Gestion des options caches enable/disable
			{	
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
    $imgspe = $site[24] == 'N' ? '' : '
          <a href="https://www.freeguppy.org/" target="_blank" title="GuppY site">
            <i class="gen-gy'.$site[24].'logo"></i>
          </a>';
  
    $txtspeC = '
          <span>'.html_entity_decode('© '.MP_COPYRIGHT, ENT_COMPAT, $charset).' &nbsp;</span>';

    if ($lng == 'fr') {
        $txtspeL = '
          <a class="copyright" href="https://www.freeguppy.org/" title="freeguppy.org" target="_blank">
            '.($site[35] == 'T' ? 'Propulsé par GuppY' : '<i class="gen-gypower"></i>').'
          </a>';
        $txtspeR = '
          <a class="copyright" href="http://cecill.info/index.fr.html" title="En savoir plus ..." target="_blank">
            '.($site[35] == 'T' ? 'Sous Licence Libre CeCILL' : '<i class="gen-gycecill-f"></i>').'
          </a>
          &nbsp;';
    }
    else {
        $txtspeL = '
          <a class="copyright" href="https://www.freeguppy.org/" title="freeguppy.org" target="_blank">
            '.($site[35] == 'T' ? 'Powered by GuppY' : '<i class="gen-gypower"></i>').'
          </a>';
        $txtspeR = '
          <a class="copyright" href="http://cecill.info/index.en.html" title="More info ..." target="_blank">
            '.($site[35] == 'T' ? 'Under Free License CeCILL' : '<i class="gen-gycecill-e"></i>').'
          </a>
          &nbsp;';
    }

    $under_empty = array_reduce($xposbox[$tconfig][BOX_UNDER], 'EstNonVide', true);
    if (!$under_empty) {
        echo '
            <!-- Begin of UNDER BOXES -->
            <div id="UnderBoxes" class="row mx-0 px-0 '.BOX_UNDER.$tconfig.'">
              <div id="BoxesUnder" class="col-12 mx-0 px-0">';
        foreach ($xposbox[$tconfig][BOX_UNDER] as $item) {
            $item = ExecFunctionBox(BOX_UNDER, $item);
            if (FALSE !== $item) echo '
                <div class="row mx-0 px-0 '.BOX_UNDER.'row'.$tconfig.'">
                  <div class="col-12 mx-0 px-0">
                    '.$item.'
                  </div>
                </div>';
        }
        echo '
              </div>
            </div> <!-- End of UNDER BOXES -->
        ';
    }

    echo '
            </div>
          </div> <!-- fin des boîtes seules -->
        </section> <!-- End of CENTER BOXES -->
        ';

    $userprefs3  = (isForumPage() ? ($forum[3] == '' ? $forum[4] : BOX_LEFT.BOX_RIGHT) : $userprefs3);

    if ($serviz[74] == '' || strip_tags($_SERVER['SCRIPT_NAME']) != '/index.php') {
        if (is_file($meskin.'rightside.inc')) {
            include($meskin.'rightside.inc');
        } else {
            if ($widepage == '' && $userprefs3 != BOX_LEFT && !$right_empty) {
                echo '       
        <!-- Begin of RIGHT BOXES -->
        <aside id="'.(isBlogPage() ? 'Blog' : '').'RightBoxes" class="col-md-3 mx-0 pl-4 pr-0 a-col">
          <div id="'.(isBlogPage() ? 'Blog' : '').'BoxesRight"> <!-- hauteur des boîtes seules -->';

                foreach ($xposbox[$colconf][BOX_RIGHT] as $ii => $aa) {
                    if ($userprefs3 == BOX_RIGHT && $xposbox[$colconf][BOX_LEFT][$ii] != '') {
                        $item = ExecFunctionBox(BOX_RIGHT, $xposbox[$colconf][BOX_LEFT][$ii]);
                        if (FALSE !== $item) echo $item;
                    }
                    if ($userprefs3 != BOX_LEFT && $xposbox[$colconf][BOX_RIGHT][$ii] != '') {
                        $item =  ExecFunctionBox(BOX_RIGHT, $xposbox[$colconf][BOX_RIGHT][$ii]);
                        if (FALSE !== $item) echo $item;
                    }
                }
                
                echo '
          </div>
        </aside> <!-- End of RIGHT BOXES -->
        ';
            }
        }
    }
    if (empty($prt)) 
        echo '
        </div>
      </div>
    </div> <!-- .slide_inside -->	 
  </main> <!-- #slide2 -->  <!-- End of MAIN CONTENT -->';

    if (empty($prt)) {
        echo '
  <a id="z3"></a>
  <!-- Begin of TOTAL FOOTER -->
  <footer id="slide3">
    <div class="slide_inside">
      <!-- Begin of BOTTOM BOXES -->
      <div id="BottomBoxes" class="container-fluid mx-auto px-1">';

        foreach ($xposbox[$tconfig][BOX_BOTTOM][BOX_LEFT] as $i => $aa) {
            $item4 = @ExecFunctionBox(BOX_BOTTOM, $xposbox[$tconfig][BOX_BOTTOM][BOX_LSIDE][$i]);
            $item1 = @ExecFunctionBox(BOX_BOTTOM, $xposbox[$tconfig][BOX_BOTTOM][BOX_LEFT][$i]);
            $item2 = @ExecFunctionBox(BOX_BOTTOM, $xposbox[$tconfig][BOX_BOTTOM][BOX_CENTER][$i]);
            $item3 = @ExecFunctionBox(BOX_BOTTOM, $xposbox[$tconfig][BOX_BOTTOM][BOX_RIGHT][$i]);
            $item5 = @ExecFunctionBox(BOX_BOTTOM, $xposbox[$tconfig][BOX_BOTTOM][BOX_RSIDE][$i]);
            if ($item4 || $item1 || $item2 || $item3 || $item5) {
                $w3    = (int)$wposbox[$tconfig][BOX_BOTTOM][BOX_LSIDE][$i];
                $w0    = (int)$wposbox[$tconfig][BOX_BOTTOM][BOX_LEFT][$i];
                $w1    = (int)$wposbox[$tconfig][BOX_BOTTOM][BOX_CENTER][$i];
                $w2    = (int)$wposbox[$tconfig][BOX_BOTTOM][BOX_RIGHT][$i];
                $w4    = (int)$wposbox[$tconfig][BOX_BOTTOM][BOX_RSIDE][$i];
                $sfix  = BOX_BOTTOM.$tconfig.$i;
                $wcol3 = 'col-md-'.($item4 === false ?  '0' : $w3).' mx-0 px-'.($item4 === false ?  '0' : '2').' wcol3'.$sfix;
                $wcol0 = 'col-md-'.($item1 === false ?  '0' : $w0).' mx-0 px-'.($item1 === false ?  '0' : '2').' wcol0'.$sfix;
                $wcol1 = 'col-md-'.($item2 === false ?  '0' : $w1).' mx-0 px-'.($item2 === false ?  '0' : '2').' wcol1'.$sfix;
                $wcol2 = 'col-md-'.($item3 === false ?  '0' : $w2).' mx-0 px-'.($item3 === false ?  '0' : '2').' wcol2'.$sfix;
                $wcol4 = 'col-md-'.($item5 === false ?  '0' : $w4).' mx-0 px-'.($item5 === false ?  '0' : '2').' wcol4'.$sfix;
                echo '
            <style>#B'.$tconfig.'row'.$i.' { max-width: '.$titre[80].'; } </style>
            <div class="B'.$tconfig.'entrow'.$i.'">
              <div id="B'.$tconfig.'row'.$i.'" class="row mx-auto B'.$tconfig.'row'.$i.'">
                <div class="'.$wcol3.'">'.(FALSE !== $item4 ? $item4 : '').'</div>
                <div class="'.$wcol0.'">'.(FALSE !== $item1 ? $item1 : '').'</div>
                <div class="'.$wcol1.'">'.(FALSE !== $item2 ? $item2 : '').'</div>
                <div class="'.$wcol2.'">'.(FALSE !== $item3 ? $item3 : '').'</div>
                <div class="'.$wcol4.'">'.(FALSE !== $item5 ? $item5 : '').'</div>
              </div>
            </div>';
            }
        }

        echo '
        <a id="bottom"></a>
      </div> <!-- End of BOTTOM BOXES -->';
        echo '
    </div> <!--.slide_inside-->
  </footer> <!--#slide3--> <!-- End TOTAL FOOTER -->';
    }
	
    echo '
  <section id="footer" class="mx-auto"> <!-- begin of copyright/footer -->
    <div class="row mx-auto px-1">
      <div class="col-md-12 mx-auto px-0">';

    // php or html scripts located in directory inc/incfoot are automatically include at the bottom of the page
    if (is_dir(CHEMIN.'inc/incfoot')) { 
        $hd = opendir(CHEMIN.'inc/incfoot');
        while ($file = readdir($hd)) { 
            if (1 == preg_match('!^[^_].*(\.inc|\.inc\.php)$!', $file)) {
                include(CHEMIN.'inc/incfoot/'.$file);
            }
        }
    }
  
    // Haut
    if ($body[7] != 'on') echo '
      <div class="divtop">
        <a class="top font-weight-bold" href="#top">
          <b><span>&#xe808;</span> '.$web[136].' <span>&#xe808;</span></b>
        </a>
      </div>';

///////////////////////////////////////////////////////////////////////////////////
//                                                                               //
//                GuppY is protected by the CeCILL FREE LICENSE.                 //
//  REMOVING OR MODIFYING THE FOLLOWING COPYRIGHT NOTICE IS STRICTLY FORBIDDEN.  //
//                                                                               //
///////////////////////////////////////////////////////////////////////////////////

    echo '
    <div class="txtspeG">';
    if ($site[25] == 'T') {
        echo 
        $imgspe.($site[24] == 'N' ? '' : '<br />').$txtspeL.$txtspeC.$txtspeR;
    }
    elseif ($site[25] == 'L') {
        echo 
        $imgspe.$txtspeL.$txtspeC.$txtspeR;
    }
    elseif ($site[25] == 'R') {
        echo 
        $txtspeL.$txtspeC.$txtspeR.$imgspe;
    }
    else {
        echo 
        $imgspe.$txtspeL.$txtspeC.$txtspeR.$imgspe;
    }
    echo '
    </div>';
    if (!empty($skinCopyright)) {
        echo '
    <div id="skinCopyright">
      '.html_entity_decode($skinCopyright, ENT_QUOTES, $charset).'
    </div>';
    }

///////////////////////////    END of COPYRIGHT NOTICE    /////////////////////////

    if ($page[8] == 'on') {
        $timer = StopTimer();
        echo '
        <div class="timer">
          '.$web[116].' '.$timer.' '.($timer <= 1 ? $web[117] : $web[118]).'
        </div>';
    }
    echo '
      </div>
    </div>
  </section> <!-- end copyright/footer -->';

    echo '
</body>
</html>';
    if ($obgo) { ob_end_flush(); }
?>