<?php
/*******************************************************************************
 *   Admin Forum
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2022 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.19 (August 26, 2024) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'frconfig.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[24] == '') {
        $nomzone = $admin[253];
        include 'access.inc';
        exit();
    }
}

$regit = import('regit');

if ($regit == 1) {
    if (is_file ($meskin.'confskin.inc')) {
        include $meskin.'confskin.inc';
    } else {
        die(sprintf('DEBUG : $meskin incorrect "%s" : STOP !', $meskin));
        include CHEMIN.'confskin.inc';
    }

    $serviz[34] = import('serviz34'); // Compteur de lecture Threads Forum
    $forum[3]   = import('forum3'); // Forum pleine page
    $forum[4]   = import('forum4'); // Position des boîtes
    $forum[5]   = import('forum5', 'POST', true, ''); // Durée up en heures
    $forum[6]   = import('forum6'); // Durée au top en jours
    $forum[7]   = import('forum7'); // Echelle
    $forum[8]   = import('forum8', 'POST', true, ''); // Durée nouveau en jours
    $forum[9]   = import('forum9'); // Affichage charte du forum ON / OFF
    $forum[10]  = import('forum10'); // Affichage du lien uniquement ON / OFF
    $forum[15]  = import('forum15'); // Affichage topforum ON / OFF

    include 'inc/confupdt.inc';
    include 'inc/confskin.inc';
    if ($site[7] == 'on' || PHP700) {
        @ob_flush();
        sleep(PHP700 ? 3 : $site[8]);
        echo BeginJavascript().'window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=frconfig";'.EndJavascript();
    }
}

include $meskin.'confskin.inc';

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[952]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[952], '100%');
$serviz34 = 'on';
echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=1066&mnuid=937&tconfig=0#z2').$admin[948].'</p>
<hr />
<div class="w-100 m-auto text-center" style="max-width:580px;">
  <br />
  <div style="text-align:left;">
    <form action="admin.php?lng='.$lng.'&amp;pg=frconfig" method="post">
    <input type="hidden" name="regit" value="1" />
	<div class="quest" style="padding:2px 20px;">
      <div style="float:right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div>
      <div class="clearfix"></div>
    </div>
    <fieldset>
      <fieldset>'.legend($admin[863]).'
        <div class="table-responsive-md">
        <table class="table w-100 m-auto">
          <tr>
            <td class="py-1 align-middle">&bull; <label for="forum9">'.$admin[869].'</label></td>
            <td class="py-1 form-check">
              <input class="form-check-input" type="checkbox" id="forum9" name="forum9"'.Checked($forum[9]).' />
            </td>
          </tr>
          <tr>
            <td class="py-1 align-middle"><span style="padding-left:16px;">&bull; <label for="forum10">'.$admin[500].'</label></span></td>
            <td class="py-1 form-check">
              <input class="form-check-input" type="checkbox" id="forum10" name="forum10"'.Checked($forum[10]).' />
            </td>
          </tr>
          <tr>
            <td class="py-1 align-middle">&bull; <label for="forum3">'.$admin[768].'</label></td>
            <td class="py-1 form-check">
              <input class="form-check-input" type="checkbox" id="forum3" name="forum3"'.Checked($forum[3]).' />
            </td>
          </tr>
          <tr>
            <td class="align-middle" style="padding-left:16px;">&bull; '.$admin[1656].'</td>
            <td>
              <select class="form-control" name="forum4">
                <option value="'.BOX_LEFT.'"'.Selected($forum[4] == BOX_LEFT).'>'.$admin[1658].'</option>
                <option value="'.BOX_RIGHT.'"'.Selected($forum[4] == BOX_RIGHT).'>'.$admin[1659].'</option>
              </select>
            </td>
          </tr>
          <tr>
            <td class="py-1 align-middle">&bull; <label for="forum15">'.$admin[1774].'</label></td>
            <td class="py-1 form-check">
              <input class="form-check-input" type="checkbox" id="forum15" name="forum15"'.Checked($forum[15]).' />
            </td>
          </tr>
          <tr>
            <td class="py-1 align-middle">&bull; <label for="serviz34">'.$admin[483].'</label></td>
            <td class="py-1 form-check">
              <input class="form-check-input" type="checkbox" id="serviz34" name="serviz34"'.Checked($serviz[34]).' disabled />
            </td>
          </tr>
          <tr>
            <td class="align-middle">&bull; '.$admin[855].'</td>
            <td>
              <select class="form-control w-50" name="forum8">
                <option value=""'. Selected($forum[8] == '' ).'>&nbsp;</option>
                <option value="1"'.Selected($forum[8] == '1').'>1</option>
                <option value="2"'.Selected($forum[8] == '2').'>2</option>
                <option value="3"'.Selected($forum[8] == '3').'>3</option>
                <option value="4"'.Selected($forum[8] == '4').'>4</option>
                <option value="5"'.Selected($forum[8] == '5').'>5</option>
              </select>
            </td>
          </tr>
          <tr>
            <td class="align-middle">&bull; '.$admin[856].'</td>
            <td>
              <select class="form-control w-50" name="forum5">
                <option value=""'. Selected($forum[5] == '' ).'>&nbsp;</option>
                <option value="1"'.Selected($forum[5] == '1').'>1</option>
                <option value="2"'.Selected($forum[5] == '2').'>2</option>
                <option value="3"'.Selected($forum[5] == '3').'>3</option>
                <option value="4"'.Selected($forum[8] == '4').'>4</option>
                <option value="5"'.Selected($forum[8] == '5').'>5</option>
              </select>
            </td>
          </tr>
          <tr>
            <td class="align-middle">&bull; '.$admin[1688].'</td>
            <td>
              <div class="d-inline-block">
                <input class="form-control" type="text" name="forum6"  size="2" value="'.$forum[6].'" />
              </div>
              <div class="d-inline-block">
                <script>
                  $(document).ready(function(){
                    $(\'.hforum6\').popover({
                      title:"'.$admin[1688].'",
                      content:"'.$help[5].'",
                      trigger:"hover",
                      placement:"auto",
                      html:true
                    });
                  });
                </script>
                <span class="overpop float-left">&nbsp;
                  <a class="hforum6" title="">
                    <i class="far fa-comment-dots"></i>
                  </a>
                </span>
              </div>
		    </td>
          </tr>
        </table>
        </div>
      </fieldset>
  
      <fieldset>'.legend($admin[862]).'
        <div class="table-responsive-md">
        <table class="table m-auto" style="max-width:483px;">
          <tr style="text-align:center">
            <td class="py-1">'.$admin[858].'</td>
            <td class="py-1"><i class="frm-rank1"></i></td>
            <td class="py-1"><i class="frm-rank2"></i></td>
            <td class="py-1"><i class="frm-rank2"></i></td>
            <td class="py-1"><i class="frm-rank4"></i></td>
            <td class="py-1"><i class="frm-rank5"></i></td>
            <td class="py-1">&nbsp;</td>
          </tr>
          <tr class="quest" style="text-align:center">
            <td class="py-1">'.$admin[766].'</td>
            <td class="py-1">&nbsp;</td>
            <td class="py-1">&nbsp;</td>
            <td class="py-1">&nbsp;</td>
            <td class="py-1">&nbsp;</td>
            <td class="py-1">&nbsp;</td>
            <td class="py-1">
              <input type="radio" name="forum7" value=""'.Checked($forum[7] == '').' />
            </td>
          </tr>
          <tr class="rep" style="text-align:center">
            <td class="py-1">'.$admin[859].'</td>
            <td class="py-1">10</td>
            <td class="py-1">30</td>
            <td class="py-1">60</td>
            <td class="py-1">100</td>
            <td class="py-1">+ 100</td>
            <td class="py-1">
              <input type="radio" name="forum7" value="10-30-60-100"'.Checked($forum[7] == '10-30-60-100').' />
            </td>
          </tr>
          <tr class="quest" style="text-align:center">
            <td class="py-1">'.$admin[860].'</td>
            <td class="py-1">10</td>
            <td class="py-1">50</td>
            <td class="py-1">100</td>
            <td class="py-1">200</td>
            <td class="py-1">+ 200</td>
            <td class="py-1">
              <input type="radio" name="forum7" value="10-50-100-200"'.Checked($forum[7] == '10-50-100-200').' />
            </td>
          </tr>
          <tr class="rep" style="text-align:center">
            <td>'.$admin[861].'</td>
            <td class="py-1">20</td>
            <td class="py-1">100</td>
            <td class="py-1">200</td>
            <td class="py-1">300</td>
            <td class="py-1">+ 300</td>
            <td class="py-1">
              <input type="radio" name="forum7" value="20-100-200-300"'.Checked($forum[7] == '20-100-200-300').' />
            </td>
          </tr>
        </table>
        </div>
        <br />
      </fieldset>
    </fieldset>
    <div class="quest" style="padding:2px 20px;">
      <div style="float:right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div>
      <div style="clear:both"></div>
    </div>
  </form>
</div>
</div>';

btable();
include CHEMIN.'admin/bpage.inc';
?>
