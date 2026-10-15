<?php
/*******************************************************************************
 *   clos_postforum Bannishment
 *******************************************************************************
 *   GuppY PHP Script - version 5.0
 *   CeCILL Copyright (C) 2004-2014 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = http://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V5 developed by Lud Bienaimé
 *      with the participation of Jean-Michel Misrachi and the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.21 (December 21, 2024) : Closing forum posts Initial Release 
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "clos_postforum.inc")) {
    header("location:../index.php");
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[45] == '') {
        $nomzone = $admin[1910];
        include 'access.inc';
        exit();
    }
}
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d_H');
$nomficlog = CHEMIN.'data/log/clos_postforum'.'_'.$dateheureminiutesecl.'.log';


 $listaction = $admin[1912];
 $action = import('action');
 $out = '';
if ($action == 'list')
{
   $out = ClosepostForum($actionvalide = 'non');
   $listaction = $admin[1910];
} // fin de list avant validation de cloture des posts
if ($action == 'Oui')
{
   $out = ClosepostForum($actionvalide = 'oui');
   $listaction = $admin[1912];
} 
$widepage = 'on';
$topmess  = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.$admin[1908];
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[1908], '100%');

echo '
<div class="w-100 m-auto text-align-center" >
<p>'.ToHelp($lng, '&pg=1177&mnuid=937&tconfig=0#z2').'</p>
<p style="text-align:center;">'.$admin[557].' '.getIPadress($_SERVER[$site[6] == 'on' ? 'HTTP_X_FORWARDED_FOR' : 'REMOTE_ADDR']).'</p>
<hr />';
if (empty($out)) {
    echo '
<p style="text-align:center;"><b>'.$admin[1913].'</b></p>';
}
else {
    if ($action == 'list') {
        echo '
<div class="w-100 m-auto text-align-center" >
  <form name="clos_postforum" action="admin.php?lng='.$lng.'&amp;pg=clos_postforum" method="post">
	<fieldset style="margin:10px auto;">'.legend($admin[1914]).'
      <div class="d-inline-block" style="text-align:center;">
		  <div style="text-align:center;margin:auto;">'.$admin[1910].'?&nbsp;&nbsp;
			<select name="action">';
				echo '
				  <option value="'.$admin[1604].'">'.$admin[1604].'</option>';
				echo '
				  <option value="'.$admin[1605].'">'.$admin[1605].'</option>';
			echo '
			</select>
		  </div>
	  </div>
	</fieldset>
    <div class="quest" style="padding:2px 20px;">
      <div style="float:right">'.SubmitButton($admin[1908], '', '', $imgdroite).'</div>
      <div style="clear:both"></div>
    </div>
  </form>
</div>';
    }
    if (!empty($out)) 
	{
			echo '<fieldset class="w-100 m-0 p-2">'.legend($admin[1912]);
			echo '
			<div class="table-responsive-md">'.
			$out.'
			</fieldset>
			<hr />';


    }
}
echo '</div>';
btable();
include CHEMIN.'admin/bpage.inc';
?>