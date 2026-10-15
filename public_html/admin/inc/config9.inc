<?php
/*******************************************************************************
 *   Admin Configuration
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
 * v6.00.19 (August 26, 2024) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'config9.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[1] == '') {
        $nomzone = $admin[888];
        include 'access.inc';
        exit();
    }
}

$regit    = import('regit');
$page9    = import('page9');
$page14   = import('page14');
$page23   = import('page23');
$daterota = import('daterota');
$skinrota = import('skinrota');

if ($regit == 1) {
    $messreg = ' OK';
    $meskin  = CHEMIN.'skins/'.$selskin.'/';
    if (is_file ($meskin.'confskin.inc')){
        include $meskin.'confskin.inc';
    } else {
        include CHEMIN.'skins/no_skin/confskin.inc';
    }
    $page[9] = $page9; // Thème icônes O/N
    include 'inc/confskin.inc';
    include CONFIG;
    $page[14] = $page14; // Thème skins
    $page[23] = $page23; // Thème avatars
    include 'inc/confupdt.inc';
    if ($site[7] == 'on' || PHP700) {
        @ob_flush();
        sleep(PHP700 ? 3 : $site[8]);
        echo BeginJavascript().'window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=config9";'.EndJavascript();
    }
}

if ($regit == 2) {
    $addrota   = true;
    $rotaskins = @file(CHEMIN.'data/config/rotaskins.dtb');
    if (!empty($rotaskins)) {
        foreach ($rotaskins as $rotaskin) {
            $rota = explode(CONNECTOR, $rotaskin);
            if ($rota[0] == substr($daterota.'0000', 0, 12)) {
                $addrota = false;
                break;
            }
        }
    }
    if ($addrota) {
        $rotas  = ReadFullDB(CHEMIN.'data/config/rotaskins.dtb');
        $rotas .= (empty($rotas) || $rotas[strlen($rotas)-1] == chr(10) ? '' : chr(10)).substr($daterota.'0000', 0, 12).CONNECTOR.$skinrota;
        WriteFullDB(CHEMIN.'data/config/rotaskins.dtb', $rotas);
    } else {
        echo BeginJavascript().'alert("'.$admin[1746].'");'.EndJavascript();
    }
}

if ($regit == 3) {
    $rotaskins = @file(CHEMIN.'data/config/rotaskins.dtb');
    $rotas     = '';
    foreach ($rotaskins as $rotaskin) {
        $rota = explode(CONNECTOR, $rotaskin);
        if ($rota[0] != $daterota) $rotas .= $rotaskin;
    }
    WriteFullDB(CHEMIN.'data/config/rotaskins.dtb', $rotas);
}

include CONFIG;
$selskin = is_file(CHEMIN.'skins/'.$selskin.'/confskin.inc') ? $selskin : 'no_skin';
$meskin = CHEMIN.'skins/'.$selskin.'/';
if (is_file ($meskin.'confskin.inc')) {
    include $meskin.'confskin.inc';
} else {
    die('Skin not found : '.$meskin);
}

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[67]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';

$skintheme   = ExploreDir('skins/');
$avatartheme = ExploreImg('inc/img/avatars/');

htable($admin[67], '100%');

function GetSelector($inputname = 'idselector', $default = '') {
    return '<input size="8" class="texte" type="text" id="'.$inputname.'" name="'.$inputname.'" value="'.$default.'" />'.
           '<input id="'.$inputname.'btn" name="'.$inputename.'btn" type="button" value="    " onclick="opencolorselector(\''.$inputename.'\', event)" style="background:'.$default.'" />';
}

if (isset($selskin)) $skn = '&amp;selskin='.$selskin;
echo '
<div style="text-align:center;"><p>'.ToHelp($lng, '&pg=964&mnuid=937&tconfig=0#z2').'<br />'.$admin[889].'<br /><br /></p>
  <hr />
</div>
<div class="w-100 m-auto" style="max-width:514px">
  <form name="conf9" action="admin.php?lng='.$lng.'&amp;pg=config9" method="post">
	<input type="hidden" name="regit" value="1" />
	<fieldset>'.legend($admin[344]).'
      <div class="table-responsive-md">
	  <table class="table w-100">
		<tr class="text-left">
		  <td colspan="2"><p class="forum2" style="text-align:center;"> --------- '.$admin[891].' ----------</p><br /></td>
		</tr>
		<tr class="text-left">
		  <td class="align-middle">&bull; '.$admin[550].'</td>
		  <td class="align-middle">
		    <select class="form-control" name="page14">';
for ($i = 0; $i < count($skintheme); $i++) {
	echo '
			  <option value="'.$skintheme[$i].'"'.Selected($page[14] == $skintheme[$i]).'>'.$skintheme[$i].'</option>';
}
echo '
			</select>
		  </td>
		</tr>
		<tr style="text-align:left;">
		  <td class="align-middle">&bull; '.$admin[809].'</td>
		  <td class="align-middle w-40 pt-4">
            <div class="d-inline-block">
		      <select class="form-control mt-2" name="page23" onchange="submit();">
			    <option value="none"'.Selected($page[23] == 'none').'>'.$admin[1681].'</option>';
for ($i = 0; $i < count($avatartheme); $i++) {
    $avatar = substr($avatartheme[$i], 0, -4);
	echo '
			    <option value="'.$avatar.'"'.Selected($page[23] == $avatar).'>'.$avatar.'</option>';
}
echo '
			  </select>&nbsp;
            </div>
            <span class="float-right pt-3 pr-5">
              <button type="button" data-target="#user-ava" data-toggle="modal"><i class="far fa-eye"></i></button>
            </span>
            <div class="modal fade" id="user-ava" role="dialog">
              <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                  <div class="modal-header">
                    <h2 class="modal-title text-center">'.$page[23].'</h2>
                    <button class="close" data-dismiss="modal" type="button"><i class="fas fa-times"></i></button>
                  </div>
                  <div class="modal-body">
                    <div class="table-responsive-md divuserava">
                      <table class="table table-borderless m-auto tbluseravaW">
                        <tr>';
@include CHEMIN.'inc/img/avatars/'.$page[23].'.inc';
$nbrimgavatar = 0; $j = $imgavatars;
for ($i = 1; $i < $imgavatars; $i++) {
    $ava  = substr('0'.(string)$i, -2);
    echo '
                          <td class="text-center">
                            <i class="ava-'.$ava.'" title="'.$page[23].'-'.$ava.'"></i>
                          </td>';
    $nbrimgavatar++; $j--;
    if ($nbrimgavatar == 5 && $j != 0) {
        $nbrimgavatar = 0;
        echo '
                        </tr>
                        <tr>';
    }
}
$vid = 5 - $imgavatars%5 ;
if ($vid != 5) {
    for ($i = 0; $i < $vid; $i++)
        echo '
                          <td class="text-center">&nbsp;</td>';
}
echo '
                        </tr>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
		  </td>
		</tr>
		<tr>
		  <td class="text-left align-middle pb-3">&bull; <label for="page9">'.$admin[306].'</label></td>
          <td class="text-left align-middle pb-3">
            <input type="checkbox" id="page9" name="page9"'.Checked($page[9]).' />
          </td>
		  </td>
		</tr>
	  </table>
      </div>
	</fieldset>
	<input type="hidden" name="selskin" value="'.$selskin.'" />
    <div class="row quest p-0 mt-2 w-98 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
    </div>
  </form>
  <a id="rotaskin"></a>
  <form name="conf91" action="admin.php?lng='.$lng.'&amp;pg=config9#rotaskin" method="post">
	<input type="hidden" name="regit" value="2" />
	<fieldset>'.legend($admin[1742]).'
      <div class="table-responsive-md">
	  <table class="table w-100">
		<tr style="text-align:left;vertical-align:middle;">
		  <td class="align-middle"><p>&bull; '.$admin[1743].'</p></td>
		  <td class="align-middle">
		    <select class="form-control" name="skinrota">';
for ($i = 0; $i < count($skintheme); $i++) {
	echo '
			  <option value="'.$skintheme[$i].'"'.Selected($page[14] == $skintheme[$i]).'>'.$skintheme[$i].'</option>';
}
echo '
			</select>
          </td>
		</tr>
		<tr style="text-align:left;vertical-align:middle;">
		  <td class="align-middle">
            <p>&bull; '.$admin[1744].'</p>
          </td>
		  <td class="align-middle">
            <div class="d-inline-block">
              <input class="form-control" type="text" name="daterota" size="16" />
            </div>
            <div class="d-inline-block">            
              <script>
                $(document).ready(function(){
                  $(\'.popuserpwd\').popover({
                    title:"'.$admin[1744].'",
                    content:"'.$help[25].'",
                    trigger:"hover",
                    placement:"auto",
                    html:true
                  });
                });
              </script>
              <span class="overpop float-left">&nbsp;
                <a class="popuserpwd" title="">
                  <i class="far fa-comment-dots"></i>
                </a>
              </span>
		    </div>
          </td>
		</tr>
	  </table>
      </div>
      <div class="row quest p-0 mt-2 w-100 m-auto">
	    <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
         <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[1745], '', '', $imgdroite).'</div></div>
      </div>';
if (is_file(CHEMIN.'data/config/rotaskins.dtb')) {
    $rotaskins = @file(CHEMIN.'data/config/rotaskins.dtb');
    if (!empty($rotaskins)) {
        echo '
      <fieldset>';
        sort($rotaskins);
        foreach($rotaskins as $rotaskin) {
            $rota = explode(CONNECTOR, $rotaskin);
            echo '
        <div style="line-height: 20px;">
          '.FormatDate($rota[0]).'  ==>  '.$rota[1].'
          <span style="float:right;">
            <i class="fadm-sup align-middle" title="'.$admin[186].'" 
              onclick="self.location.href=\'admin.php?lng='.$lng.'&pg=config9&regit=3&daterota='.$rota[0].'#rotaskin\'">
            </i>
          </span>
        </div>';
        }
        echo '
      </fieldset>';
    }
}
echo '
    </fieldset>
  </form>
</div>
<div style="text-align:center;">';
if (isset($selskin)) $skn = '&amp;selskin='.$selskin;
echo '
</div>';

btable();
include CHEMIN.'admin/bpage.inc';
?>