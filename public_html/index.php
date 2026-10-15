<?php
/*******************************************************************************
 *   Home page
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
 * v6.00.03 (August 10, 2021) : adaptation to php 8
******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '');
include CHEMIN.'inc/includes.inc';

$widepage = 'on' == $serviz[74];
$topmess  = '';
$pgtype   = 5;
$tconfig  = 0;
include 'inc/hpage.inc';

echo '
  <!-- Begin of EditoBoxes -->
  <section id="EditoBoxes">';

foreach ($xposbox[BOX_EDITO][BOX_LEFT] as $i => $a) {
    $item4 = @ExecFunctionBox(BOX_EDITO, $xposbox[BOX_EDITO][BOX_LSIDE][$i]);
    $item1 = @ExecFunctionBox(BOX_EDITO, $xposbox[BOX_EDITO][BOX_LEFT][$i]);
    $item2 = @ExecFunctionBox(BOX_EDITO, $xposbox[BOX_EDITO][BOX_CENTER][$i]);
    $item3 = @ExecFunctionBox(BOX_EDITO, $xposbox[BOX_EDITO][BOX_RIGHT][$i]);
    $item5 = @ExecFunctionBox(BOX_EDITO, $xposbox[BOX_EDITO][BOX_RSIDE][$i]);
    if ($item4 || $item1 || $item2 || $item3 || $item5) {
        $w3    = (int)$wposbox[BOX_EDITO][BOX_LSIDE][$i];
        $w0    = (int)$wposbox[BOX_EDITO][BOX_LEFT][$i];
        $w1    = (int)$wposbox[BOX_EDITO][BOX_CENTER][$i];
        $w2    = (int)$wposbox[BOX_EDITO][BOX_RIGHT][$i];
        $w4    = (int)$wposbox[BOX_EDITO][BOX_RSIDE][$i];
        $sfix  = BOX_EDITO.'0'.$i;
        $wcol3 = 'col-md-'.($item4 === false ?  '0' : $w3).' mx-0 px-'.($item4 === false ?  '0' : '2').' wcol3'.$sfix;
        $wcol0 = 'col-md-'.($item1 === false ?  '0' : $w0).' mx-0 px-'.($item1 === false ?  '0' : '2').' wcol0'.$sfix;
        $wcol1 = 'col-md-'.($item2 === false ?  '0' : $w1).' mx-0 px-'.($item2 === false ?  '0' : '2').' wcol1'.$sfix;
        $wcol2 = 'col-md-'.($item3 === false ?  '0' : $w2).' mx-0 px-'.($item3 === false ?  '0' : '2').' wcol2'.$sfix;
        $wcol4 = 'col-md-'.($item5 === false ?  '0' : $w4).' mx-0 px-'.($item5 === false ?  '0' : '2').' wcol4'.$sfix;
        echo '
    <div class="E'.$tconfig.'entrow'.$i.'">
      <div id="E'.$tconfig.'row'.$i.'" class="row mx-auto px-0 E'.$tconfig.'row'.$i.'">
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
  </section> <!-- End of EditoBoxes -->
  ';
include 'inc/bpage.inc';
if (empty(trim($serviz[31])) && is_file(CHEMIN.'install/install.php')) @include CHEMIN.'install/init.inc';
?>