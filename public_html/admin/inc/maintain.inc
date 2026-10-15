<?php
/*******************************************************************************
 *   Maintenance Routine
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
 * v6.00.24 (August 12, 2026) : Control DBMENU exist and global lng and lang
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'],'maintain.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[6] == '') {
        $nomzone = $admin[170];
        include 'access.inc';
        exit();
    }
}

$act    = import('act');
$see    = import('see');
$mod    = import('mod');
$modify = import('modify');

function CreateIdxArOm() {
global $lng, $lang;

	$aom = SelectDBFieldsByNotStatus(SelectDBFieldsByType(ReadDBFields(DOCID), TYP_OPTMENU), 'd');
	$dbw = array();
	foreach($aom as $om) {
		$fid = TestFileID($om[1]);
		if (FileDBExist(DBBASE.$fid.INCEXT)) {
			include DBBASE.$fid.INCEXT;
			$flds = explode(CONNECTOR, $lng == $lang[0] ? $fieldd1 : $fieldd2);
			$fld  = $flds[0];
			if (strpos($fld, 'articles.php') !== false) {
				$pg    = substr($fld, strpos($fld, 'pg=') + 3);
				$pos   = strpos($pg, '&');
				$pg    = $pos === false ? $pg : substr($pg, 0, $pos);
				$menus = SelectDBFieldsByField(ReadDBFields(DBMENUBOX), 4, $fieldweb);					
				if (count($menus) > 0)
				{
					$pos   = strpos($fielda1, '-->');
					$flda1 = substr($fielda1, ($pos !== false ? $pos + 3 : 0));
					$pos   = strpos($fielda2, '-->');
					$flda2 = substr($fielda2, ($pos !== false ? $pos + 3 : 0));
					$pos   = strpos($menus[0][2], '-->');
					$mnus2 = substr($menus[0][2], ($pos !== false ? $pos + 3 : 0));
					$pos   = strpos($menus[0][3], '-->');
					$mnus3 = substr($menus[0][3], ($pos !== false ? $pos + 3 : 0));
					$dbw[] = array($pg, $om[1], $fieldweb, $mnus2, $mnus3, $flda1, $flda2);
				}
			}
		}
	}
	WriteDBFields(DBARTMENU, $dbw);
}

if ($act == 'fast') {
    $dbids   = explode(' ', trim(import('ids')));
    $datas   = array();
    $dbdocid = IndexDbFieldsByField(ReadDBFields(DOCID), 1);
    $types   = array();
	$idxarom = false;
    foreach ($dbids as $id) {
        $type = @$dbdocid[$id][0];
        if (import('i_'.$id) == 'on') {
            $types[$type] = $type;
            ActionOnFields('act', array($type, $id, 'i'), FALSE);
			$idxarom = in_array($type, array(TYP_ART, TYP_MENUBOX, TYP_OPTMENU));
        }
        elseif (import('s_'.$id) == 'on') {
            $types[$type] = $type;
            ActionOnFields('sup', array($type, $id), FALSE);
			if ($type == TYP_PHOTO) {
				RecursiveRemoteDir(PHOTOREP.'gal_'.$id);
			}
            if ($type == TYP_MENUBOX) {
                $types[TYP_OPTMENU] = TYP_OPTMENU;
                $opts = SelectDBFieldsByField(ReadDBFields(DBOPTMENU), 5, $id);
                foreach ($opts as $opt) ActionOnFields('sup', array(TYP_OPTMENU, $opt[4]), FALSE);
            }
            if ($type == TYP_DDMENUBOX) {
                $types[TYP_OPTDDMENU] = TYP_OPTDDMENU;
                $opts = SelectDBFieldsByField(ReadDBFields(DBOPTDDMENU), 5, $id);
                foreach ($opts as $opt) ActionOnFields('sup', array(TYP_OPTDDMENU, $opt[4]), FALSE);
            }
        }
    }
    unset($dbids);
    foreach($types as $type) {
        ActionOnFields('reindex', array($type, 0));
    }
	if ($idxarom) CreateIdxArOm();
}
elseif (!empty($modify)) {
    $mcreadate = import('mcreadate');
    $mmoddate  = import('mmoddate');
    $mauthor   = import('mauthor');
    $memail    = import('memail');
    ReadDoc($modify);
    $data     = array_fill(0, 17, '');
    $data[0]  = $type;
    $data[1]  = $fileid;
    $data[2]  = $status;
    $data[3]  = $mcreadate;
    $data[4]  = $mmoddate;
    $data[5]  = $mauthor;
    $data[6]  = $memail;
    $data[7]  = $fielda1;
    $data[8]  = $fielda2;
    $data[9]  = $fieldb1;
    $data[10] = $fieldb2;
    $data[11] = $fieldc1;
    $data[12] = $fieldc2;
    $data[13] = $fieldd1;
    $data[14] = $fieldd2;
    $data[15] = $fieldweb;
    $data[16] = $fieldmail;
    $data[17] = $fieldmod;
    ActionOnFields('mod', $data);
}

$headinc .= JavascriptFile(CHEMIN.'admin/inc/maintain.js');
$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.$admin[192];
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[192], '100%');

if (empty($see) && empty($mod)) {
    $dbwork = SelectDBFieldsByStatus(ReadDBFields(DOCID),'d');
    @rsort($dbwork);
	echo '
<p>'.ToHelp($lng, '&pg=990&mnuid=937&tconfig=0#z2').'</p>
<div class="text-center w-100 m-auto" style="max-width:700px;">'.$admin[221].'</div>
<hr />
<div class="w-100 m-auto text-center" style="max-width:600px;">';
    if (count($dbwork) > 0) {
        echo '
  <form name="fast_actions" action="admin.php?lng='.$lng.'&amp;pg=maintain" method="post">
    <div class="table-responsive-md">
    <table class="table table-bordered w-100 border-1">
      <tr>
        <td>
          <div style="float:right;padding-right:25px">
            '.SubmitButton($admin[1602]).' <i class="fas fa-level-down-alt align-middle" style="font-size: 1.4rem;"></i>
          </div>
        </td>
      </tr>
    </table>
    </div>
    <input type="hidden" id="lang_maintain_js"  value="'.$admin[60].': '.mb_strtolower($admin[893], $charset).'" />
    <input type="hidden" name="act" value="fast" />
    <div class="table-responsive-md">
    <table id="tableDB" class="table table-bordered w-100 border-1">
      <tr class="forum">
        <td class="align-middle p-2">Id/Type</td>
        <td class="align-middle p-2">'.$admin[679].'</td>
        <td class="p-2 text-center">'.$admin[680].'</td>
      </tr>';
        $ids = '';
        $flng = ($lng == $lang[0]);
        $format_nextid = '%'.ceil(log10(ReadCounter(NEXTID))).'u';
        $format_fr = '%'.ceil(log10(ReadCounter(DBFORUMCOUNTER))).'u';
        for ($i = 0; $i < count($dbwork); $i++) {
            ReadDoc($dbwork[$i][1]);

            $fielda1 = strip_tags(str_replace('<!--', '', str_replace('-->', ' ', $fielda1)));
            $fielda2 = strip_tags(str_replace('<!--', '', str_replace('-->', ' ', $fielda2)));
            $fieldb1 = strip_tags(str_replace('<!--', '', str_replace('-->', ' ', $fieldb1)));
            $fieldb2 = strip_tags(str_replace('<!--', '', str_replace('-->', ' ', $fieldb2)));
            $fieldc1 = strip_tags(str_replace('<!--', '', str_replace('-->', ' ', $fieldc1)));
            $fieldc2 = strip_tags(str_replace('<!--', '', str_replace('-->', ' ', $fieldc2)));
            $champs0 = '';
            $champs1 = '';
            $champs2 = '';
            switch ($type) {
            case TYP_GUESTBK :
                $champs1 = $admin[175].$fielda1.' - ';
                $champs2 = $author;
                break;
            case TYP_BANNER :
                $champs1 = $fieldb1.' - ';
                $champs2 = $fielda1;
                break;
            case TYP_REACT :
                $champs0 = $admin[1599].Formatage($format_nextid, $fielda2).' - ';
                $champs1 = $admin[175].Formatage('%\' 2u', $fielda1).' - ';
                $champs2 = $author;
                break;
            case TYP_REBLOG :
                $champs0 = $admin[1117].Formatage($format_nextid, $fielda2).' - ';
                $champs1 = $admin[1600].Formatage('%\' 2u', $fielda1).' - ';
                $champs2 = $author;
                break;
            case TYP_NEWS :
                $champs1 = ($flng ? $fieldb1 : $fieldb2).' - ';
                $champs2 = $author;
                break;
            case TYP_MENUBOX :
            case TYP_DDMENUBOX :
            case TYP_MENUICO :
            case TYP_NWL :
            case TYP_FREEBOX :
            case TYP_SOCNET :
                $champs1 = ($flng ? $fieldb1 : $fieldb2);
                break;
            case TYP_FORUM :
                if ($fielda2 == '0') {
                    $champs1 = $admin[208].Formatage($format_fr, $fielda1).' + ';
                    $champs2 = '<b>'.$fieldb1.'</b>';
                }
                else {
                    $champs1 = $admin[208].Formatage($format_fr, $fielda1).' - ';
                    $champs2 = $admin[209].Formatage('%\' 2u', $fielda2);
                }
                break;
            case TYP_ART :
				if (substr($fieldd1, 0, 3) == 'MNU') {
					$mnu_id  = substr($fieldd1, 4);
					$fieldd1 = 'MNU';
					$boxname = '';
					$mbs     = ReadDbFields(DBMENUBOX);
					foreach ($mbs as $mb) {
						if ($mb[4] == $mnu_id) {
							$boxname = $lng == $lang[0] ? $mb[2] : $mb[3];
							break;
						}
					}
				}
				switch($fieldd1) {
				case 'MNU' :
					$champs0 = $boxname.' : ';
					break;
				case 'none' :
				default :
					$champs0 = $admin[681].' : ';
				}
                $champs1 = ($flng ? $fielda1 : $fielda2).' - ';
                $champs2 = ($flng ? $fieldb1 : $fieldb2);
                break;
            case TYP_BLOG :
                if ($serviz[54] == 'on' && $serviz[55] == 'on') {
                    if ($fieldd1 == 'right') {
                        $champs0 = $admin[353].' ';
                    }
                    else {
                        $champs0 = $admin[352].' ';
                    }
                    if (empty($fielda1)) {
                        $champs0 = $admin[681].' ';
                    }
                }
                $champs1 = ($flng ? $fielda1 : $fielda2).' - ';
                $champs2 = ($flng ? $fieldb1 : $fieldb2);
                break;
            case TYP_BRUB :
            case TYP_OPTMENU :
            case TYP_OPTDDMENU :
            case TYP_DNLOAD :
            case TYP_FAQ :
            case TYP_LINKS :
            case TYP_PHOTO :
            case TYP_RSS :
            case TYP_BSS :
                $champs1 = ($flng ? $fielda1 : $fielda2).' - ';
                $champs2 = ($flng ? $fieldb1 : $fieldb2);
                break;
            case TYP_THINK :
                $champs1 = ($flng ? $fieldb1 : $fieldb2).' - ';
                $champs2 = ($flng ? $fieldc1 : $fieldc2);
                break;
            case TYP_RECO :
                $champs1 = $fieldb1.' - ';
                $champs2 = ($flng ? $fieldc1 : $fieldc2);
                break;
            case TYP_AGENDA :
                $champs1 = $fielda1.' - ';
                $champs2 = ($flng ? $fieldc1 : $fieldc2);
                break;
            }

            echo '
      <tr '.($i %2 == 0 ? 'class="quest"' : 'class="rep"').'>
        <td style="padding:6px 4px;white-space: nowrap;"><b>'.$fileid.' - '.$type.'</b></td>
        <td style="padding:6px 4px;width:100%;text-align:left;">
          <a href="admin.php?lng='.$lng.'&amp;pg=maintain&amp;see='.$fileid.'" title="'.$admin[190].'">
            <i class="far fa-eye px-3" title="'.$admin[190].'"></i>
          </a>
          <a href="admin.php?lng='.$lng.'&amp;pg=maintain&amp;mod='.$fileid.'" title="'.$admin[33].'">
            <i class="far fa-edit px-3" title="'.$admin[33].'"></i>
          </a>
          '.$champs0.'<b>'.$champs1.'</b>'.$champs2.'
        </td>
        <td class="text-center" style="padding:6px 4px;">';
        MakeRadioGroup(array( array('d', 'i', $fileid), array('d', 's', $fileid)));
            echo '
        </td>
      </tr>';
            $ids .= $fileid.' ';
        }
        echo '
      <tr>
        <td colspan="3">
          <div style="float:right;padding-right:25px">
            '.SubmitButton($admin[1602]).' <i class="fas fa-level-up-alt align-middle" style="font-size: 1.4rem;"></i>
          </div>
        </td>
      </tr>
    </table>
    </div>
    <input type="hidden" name="ids" value="'.$ids.'" />
  </form>';
    }
    else {
        echo '
  <p style="text-align: center;">'.$admin[854].'</p>';
    }
    echo '
</div>';
}
################################################################################
elseif (!empty($see)) {
    ReadDoc($see);
    $datecrea= FormatDate($creadate); $datemod= FormatDate($moddate);
    echo '<p>'.ToHelp($lng, '&pg=990&mnuid=937&tconfig=0#z2').'
<div class="w-100 m-auto" style="max-width:800px;">
  <div class="table-responsive-md">
  <table class="table">
    <tr>
        <td style="white-space:nowrap;">&nbsp;&nbsp;</td><td style="white-space:nowrap;"><b>'.$admin[193].'</b></td><td>'.$fileid.'</td>
    </tr>
    <tr>
        <td style="white-space:nowrap;">&nbsp;</td><td style="white-space:nowrap;"><b>'.$admin[194].'</b></td><td>'.$type.'</td>
    </tr>
    <tr>
        <td style="white-space:nowrap;">&nbsp;</td><td style="white-space:nowrap;"><b>'.$admin[195].'</b></td><td>'.$status.'</td>
    </tr>
    <tr>
        <td style="white-space:nowrap;">&nbsp;</td><td style="white-space:nowrap;"><b>'.$admin[196].'</b></td><td>'.$datecrea.'</td>
    </tr>
    <tr>
        <td style="white-space:nowrap;">&nbsp;</td><td style="white-space:nowrap;"><b>'.$admin[197].'</b></td><td>'.$datemod.'</td>
    </tr>
    <tr>
        <td style="white-space:nowrap;">&nbsp;</td><td style="white-space:nowrap;"><b>'.$admin[198].'</b></td><td>'.$author.'</td>
    </tr>
    <tr>
        <td style="white-space:nowrap;">&nbsp;</td><td style="white-space:nowrap;"><b>'.$admin[199].'</b></td><td>'.$email.'</td>
    </tr>
  </table>
  </div>
  <hr />
  <br />';
    if (!empty($fielda1)) {
        echo '<fieldset><legend>FIELD A1</legend>'.$fielda1.'</fieldset>';
    }
    if (!empty($fielda2)) {
        echo '<fieldset><legend>FIELD A2</legend>'.$fielda2.'</fieldset>';
    }
    if (!empty($fieldb1)) {
        echo '<fieldset><legend>FIELD B1</legend>'.$fieldb1.'</fieldset>';
    }
    if (!empty($fieldb2)) {
        echo '<fieldset><legend>FIELD B2</legend>'.$fieldb2.'</fieldset>';
    }
    if (!empty($fieldc1)) {
        echo '<fieldset><legend>FIELD C1</legend>'.$fieldc1.'</fieldset>';
    }
    if (!empty($fieldc2)) {
        echo '<fieldset><legend>FIELD C2</legend>'.$fieldc2.'</fieldset>';
    }
    if (!empty($fieldd1)) {
        echo '<fieldset><legend>FIELD D1</legend>'.$fieldd1.'</fieldset>';
    }
    if (!empty($fieldd2)) {
        echo '<fieldset><legend>FIELD D2</legend>'.$fieldd2.'</fieldset>';
    }
    echo '
  <div class="row quest p-0 mt-2 w-100 m-auto" style="font-size:1.1rem;">
    <div class="col-md-6">'.SubmitButton($admin[191], 'self.location.href=\'admin.php?lng='.$lng.'&pg=maintain\'', $imggauche, '', 'button').'</div>
    <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div></div>
  </div>
</div>';
}
################################################################################
elseif (!empty($mod)) {
    ReadDoc($mod);
    echo '
<p>'.ToHelp($lng, '&pg=990&mnuid=937&tconfig=0#z2').'</p>
<div class="w-100 m-auto mb-4" style="max-width:600px;font-size:1.1rem;">
<form name="hardmod" action="admin.php?lng='.$lng.'&amp;pg=maintain" method="post">
  <input type="hidden" name="modify" value="'.$mod.'" />
  <fieldset>
  <div class="table-responsive-md">
  <table class="table">
    <tr>
      <td class="align-middle"><b>'.$admin[193].'</b></td>
      <td>'.$fileid.'</td>
    </tr>
    <tr>
      <td class="align-middle"><b>'.$admin[194].'</b></td>
      <td>'.$type.'</td>
    </tr>
    <tr>
      <td class="align-middle"><b>'.$admin[195].'</b>
      </td><td>'.$status.'</td>
    </tr>
    <tr>
      <td class="align-middle"><b>'.$admin[196].'</b></td>
      <td><input class="form-control texte" type="text" name="mcreadate" size="20" value="'.$creadate.'" /></td>
    </tr>
    <tr>
      <td class="align-middle"><b>'.$admin[197].'</b></td>
      <td><input class="form-control texte" type="text" name="mmoddate" size="20" value="'.$moddate.'" /></td>
    </tr>
    <tr>
      <td class="align-middle"><b>'.$admin[198].'</b></td>
      <td><input class="form-control texte" type="text" name="mauthor" size="30" value="'.$author.'" /></td>
    </tr>
    <tr>
      <td class="align-middle"><b>'.$admin[199].'</b></td>
      <td><input class="form-control texte" type="text" name="memail" size="40" value="'.$email.'" /></td>
    </tr>
  </table>
  </div>
  <br />
  </fieldset>
  <div class="row quest p-0 mt-2 w-100 m-auto" style="font-size:1.1rem;">
    <div class="col-md-6">'.SubmitButton($admin[191], 'self.location.href=\'admin.php?lng='.$lng.'&pg=maintain\'', $imggauche, '', 'button').'</div>
    <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div></div>
  </div>
</form>
</div>';
}

btable();
include CHEMIN.'admin/bpage.inc';
?>