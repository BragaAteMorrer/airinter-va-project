<?php
/*******************************************************************************
 *   Admin Forum
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2014 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = http://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V5 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.03 (August 10, 2021) : adaptation to php 8
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'frsign.inc')) {
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

$act  = import('act');
$code = import('code');

switch ($act) {
case 'add' :
    $frcode    = '';
    $frimage   = '';
    $frstatus  = 'a';
    $frorigine = 'i';
    $act       = 'edit';
    break;
case 'edit' :
    $frsigns   = ReadFrSigns();
    $frcode    = $code;
    $frimage   = $frsigns[$code]['image'];
    $frstatus  = $frsigns[$code]['status'];
    $frorigine = $frsigns[$code]['origine'];
    $act       = 'edit';
    break;
case 'change' :
	$frcode    = import('frcode');
	$oldcode   = import('oldcode');
	$frstatus  = import('frstatus');
	$frimage   = import('frimage');
    $frorigine = 'i';
    $newcode   = strtr($frcode, '[]?&%#<>', '_');
    if (empty($frcode)) {
        $msg = $admin[967];
        $act = 'edit';
    }
    elseif ($frcode != $newcode) {
        $msg = $admin[968];
        $act = 'edit';
    }
    elseif (strlen($frcode) < 5) {
        $msg = $admin[969];
        $act = 'edit';
    }
    elseif (empty($frimage)) {
        $msg = $admin[970];
        $act = 'edit';
    }
    else {
        $msg     = '';
        $frsigns = ReadFrSigns();
        if (!empty($oldcode)) unset($frsigns[$oldcode]);
        $frsigns[$frcode]['image']   = $frimage;
        $frsigns[$frcode]['status']  = 'on' == $frstatus ? 'a' : 'i';
        $frsigns[$frcode]['origine'] = $frorigine;
        WriteFrSigns($frsigns);
        $act = 'list';
    }
    break;
case 'status' :
    $frsigns = ReadFrSigns();
    $frsigns[$code]['status'] = 'a' == $frsigns[$code]['status'] ? 'i' : 'a';
    WriteFrSigns($frsigns);
    $act = 'list';
    break;
case 'delete' :
    $frsigns = ReadFrSigns();
    unset($frsigns[$code]);
    WriteFrSigns($frsigns);
    $act = 'list';
    break;
case 'list' :
default :
    $act = 'list';
}

$widepage = 'on';
$topmess  = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[954]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[954], '100%');

echo '
<div style="text-align:center;margin:auto;width:98%;">
<p style="text-align:center;">'.ToHelp($lng, 'frsign').$admin[956].'</p>
<hr />
<br />';

if ('list' == $act) {
    $frsigns = ReadFrSigns();
    echo '
<p style="text-align:center;">
  <a href="admin.php?lng='.$lng.'&amp;pg=frsign&amp;act=add">'.$admin[963].'</a>
</p>

  <fieldset>'.legend($admin[957]);
    if (0 == count($frsigns)) {
        echo '
    <p>'.$admin[962].'</p>';
    } else {
        foreach ($frsigns as $frcode => $frsign) {
            $frtxtact = $frsign['status'] != 'a' ? $admin[959] : $admin[960];
            $frimgact = $frsign['status'] != 'a' ? 'on' : 'off';
            echo '
    <div class="rep" style="float:left; width:100px; height:100px; border:1px solid; margin:1px; position:relative;">
      <div class="quest" style="text-align:center; font-weight:bold; padding:2px;margin-bottom:4px;">'.$frcode.'</div>
      <div style="text-align:center;">';
		if ($frsign['origine'] == 'i') {
			echo '
        <a href="admin.php?lng='.$lng.'&amp;pg=frsign&amp;act=edit&amp;code='.$frcode.'" title="'.$admin[958].'">
          <img src="'.CHEMIN.FRSIGNS_IMG.$frsign['image'].'" alt="" title="" style=" max-width:75px; max-height:60px;"/>
        </a>';
		} else {
			echo '
        <img src="'.$frsign['image'].'" alt="" title="" style=" max-width:75px; max-height:60px;"/>';
		}
		echo '
      </div>
      <div style="text-align:center; position:absolute; bottom:2px; width:100%;">';
		if ($frsign['origine'] == 'i') {
			echo '
        <a href="admin.php?lng='.$lng.'&amp;pg=frsign&amp;act=edit&amp;code='.$frcode.'" title="'.$admin[958].'">
		  <img src="'.CHEMIN.'admin/inc/img/files/edit.gif" alt="'.$admin[958].'" title="'.$admin[958].'" style="padding:0 3px;" />
		</a>';
		}
		echo '
        <a href="admin.php?lng='.$lng.'&amp;pg=frsign&amp;act=status&amp;code='.$frcode.'" title="'.$frtxtact.'">
		  <img src="'.CHEMIN.'admin/inc/img/files/'.$frimgact.'.gif" alt="'.$frtxtact.'" title="'.$frtxtact.'" style="padding:0 3px;" />
		</a>
        <a href="admin.php?lng='.$lng.'&amp;pg=frsign&amp;act=delete&amp;code='.$frcode.'" title="'.$admin[961].'">
		  <img src="'.CHEMIN.'admin/inc/img/files/sup.gif" alt="'.$admin[961].'" title="'.$admin[961].'" style="padding:0 3px;" />
		</a>
      </div>
    </div>';
        }
    }
    echo '
    <div style="clear:left"></div>
  </fieldset>';
}
elseif ('edit' == $act) {
    $frallimg = ExploreImg(FRSIGNS_IMG);
    echo '
<p style="text-align:center;">
  <a href="admin.php?lng='.$lng.'&amp;pg=frsign&amp;act=list">'.$admin[964].'</a> ||
  <a href="admin.php?lng='.$lng.'&amp;pg=frsign&amp;act=add">'.$admin[963].'</a>
</p>
<form name="frsignedit" action="admin.php?lng='.$lng.'&amp;pg=frsign" method="post">
  <input type="hidden" name="act" value="change" />
  <input type="hidden" name="oldcode" value="'.$frcode.'" />
  <fieldset>'.legend($admin[963]);
    if (!empty($msg)) {
        echo '
  <p class="quest" style="text-align:center;font-weight:bold;">
    <img src="'.CHEMIN.'admin/inc/img/files/off.gif" alt="" title="" style="padding-right:3px;vertical-align:middle;" />
    '.$msg.'
  </p>';
    }
    echo '
    <p>
      <span style="padding:4px 10px;">'.$admin[965].' <input type="text" name="frcode" value="'.$frcode.'" /></span>
      <span style="padding:4px 10px;">'.$admin[966].' <input type="checkbox" name="frstatus"'.Checked($frstatus == 'a').' /></span>
    </p>
    <hr />
    <p> Image</p>';
    foreach($frallimg as $frimg) {
        echo '
      <div style="float:left; width:100px; height:75px;">
        <label>
        <input type="radio" name="frimage" value="'.$frimg.'"'.Checked($frimg == $frimage).' />
        <img src="'.CHEMIN.FRSIGNS_IMG.$frimg.'" style=" vertical-align:middle; max-width:75px; max-height:60px;" alt="'.$frimg.'" title="'.$frimg.'" />
        </label>
      </div>';
    }
  
    echo '
    <div style="clear:left"></div>
  </fieldset>
  <div class="quest" style="padding:2px 20px;">
    <div style="float:right">'.SubmitButton($admin[66].' &gt;&gt;&gt;').'</div>
    <div style="clear:both"></div>
  </div>
</form>';
}

echo '
</div>';

btable();
include CHEMIN.'admin/bpage.inc' ;
?>
