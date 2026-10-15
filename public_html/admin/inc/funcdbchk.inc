<?php
/*******************************************************************************
 *   DataBase Integrity Check
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2023 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.11 (March 09, 2023) : correction integrity data control
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'funcdbchk.inc')) {
    header('location:../index.php');
    die();
}

function CheckDBmP($docmin, $docmax, $quiet=0) {
    global $serviz,
        $type, $fileid, $status, $creadate, $moddate, $author, $email, 
        $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2, 
        $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod, $admin;

    $error_reporting = ini_get('error_reporting');
    error_reporting($error_reporting ^ E_NOTICE);

    $dbnewdocid = array();
    $timenow    = time();
    $docdates   = IndexDbFieldsByField(ReadDBFields(DOCDATES), 0);
    $children   = IndexDbFieldsByField(ReadDBFields(CHILDREN), 0);
    for ($i = $docmin; $i <= $docmax; $i++) {
        if (isset($docdates[$i])) unset($docdates[$i]);
        if (isset($children[$i])) unset($children[$i]);
    }
    $j = 0;
    for ($i = $docmin; $i <= $docmax; $i++) {
        if (FileDBExist(DBBASE.TestFileId($i).INCEXT)) {
            ReadDoc($i);
            if ($type == '' || $fileid == '' || $status == '') {
                DestroyDBFile(DBBASE.TestFileId($i).INCEXT);
                DestroyDBFile(DBCOUNT.TestFileId($i).DBEXT);
                DestroyDBFile(DBIPBASE.TestFileId($i).DBEXT);
                continue;
            }
            $dbnewdocid[$j][0] = $type;
            $dbnewdocid[$j][1] = $fileid;
            $dbnewdocid[$j][2] = $status;
            $docdates[$fileid] = array($fileid, $creadate, $moddate);
            switch ($type) {
            case TYP_GUESTBK :
            case TYP_REACT :
            case TYP_REBLOG :
                $children[$fileid] = array($fileid, $type, $fielda1, $fielda2);
                break;
            case TYP_FORUM :
                if (0 != $fielda2) $children[$fileid] = array($fileid, $type, $fielda2, $fielda1);
                break;
            }
            $j++;
            if (FileDBExist(DBIPBASE.TestFileId($i).DBEXT)) {
                $timefile = filemtime(DBIPBASE.TestFileId($i).DBEXT);
                if ($timenow - $timefile > HIT_TIME) {
                    DestroyDBFile(DBIPBASE.TestFileId($i).DBEXT);
                }
            }
        }
        else {
            if (FileDBExist(DBCOUNT.TestFileId($i).DBEXT)) {
                DestroyDBFile(DBCOUNT.TestFileId($i).DBEXT);
            }
            if (FileDBExist(DBIPBASE.TestFileId($i).DBEXT)) {
                DestroyDBFile(DBIPBASE.TestFileId($i).DBEXT);
            }
        }
    }
    unset($fr);
    $dbdocid   = ReadDBFields(DOCID);
    $tempdocid = array();
    $i = 0; // index $dbnewdocid
    $j = 0; // index $dbdocid
    $k = 0; // index tempodocid
    $onerror = 0;
    $reindex = array();
    if (count($dbnewdocid) == 0) {
        for ($j = 0; $j < count($dbdocid); $j++) {
            if ($dbdocid[$j][1] >= $docmin && $dbdocid[$j][1] <= $docmax) {
                $onerror++;
                if ($quiet == 0) {
                    echo $admin[282].$dbdocid[$j][1].$admin[288];
                    echo $admin[284].$dbdocid[$j][1].$admin[285].$dbdocid[$j][0].$admin[286].$dbdocid[$j][2].$admin[287];
                }
                $reindex[$dbdocid[$j][0]] = TRUE;
            }
            else {
                $tempdocid[$k][0] = $dbdocid[$j][0];
                $tempdocid[$k][1] = $dbdocid[$j][1];
                $tempdocid[$k][2] = $dbdocid[$j][2];
                $k++;
            }
        }
    } else {
        if ($docmax > $dbdocid[count($dbdocid)-1][1] && $dbnewdocid[count($dbnewdocid)-1][1] < $dbdocid[count($dbdocid)-1][1]) {
            $whiletoomuch = 1;
        }
        else {
            $whiletoomuch = 0;
        }
        $docmax = $dbnewdocid[count($dbnewdocid)-1][1];
        if ($quiet == 0) {
            echo $admin[301].$docmin.$admin[302].$docmax.$admin[303];
        }
        $whiletest = 0;
        $whileloop = Max($docmax,$dbdocid[count($dbdocid)-1][1]);
        while ($whiletest < $whileloop && ($j < count($dbdocid) || $i < count($dbnewdocid)))  {
            if ($j == count($dbdocid)) {
                $dbdocid[$j][1] = '999999999999';
            }
            $i = Min($i,count($dbnewdocid));
            if ($dbdocid[$j][1] < $docmin) {
                $tempdocid[$k][0] = $dbdocid[$j][0];
                $tempdocid[$k][1] = $dbdocid[$j][1];
                $tempdocid[$k][2] = $dbdocid[$j][2];
                $k++;
                $j++;
            } elseif ($dbdocid[$j][1] > $docmax && $whiletoomuch == 0 && $dbdocid[$j][1] != '999999999999' && $i != (count($dbnewdocid)-1)) {
                $tempdocid[$k][0] = $dbdocid[$j][0];
                $tempdocid[$k][1] = $dbdocid[$j][1];
                $tempdocid[$k][2] = $dbdocid[$j][2];
                $k++;
                $j++;
            } elseif ($dbdocid[@$j][0] == $dbnewdocid[@$i][0] && $dbdocid[@$j][1] == $dbnewdocid[@$i][1] && $dbdocid[@$j][2] == $dbnewdocid[@$i][2]) {
                $tempdocid[$k][0] = $dbdocid[$j][0];
                $tempdocid[$k][1] = $dbdocid[$j][1];
                $tempdocid[$k][2] = $dbdocid[$j][2];
                $k++;
                $j++;
                $i++;
            } else {
                $onerror++;
                if ($dbdocid[@$j][0] == $dbnewdocid[@$i][0] && $dbdocid[@$j][1] == $dbnewdocid[@$i][1]) {
                    if ($quiet == 0) {
                        echo $admin[282].$dbnewdocid[$i][1].$admin[299];
                        echo $admin[284].$dbnewdocid[$i][1].$admin[285].$dbnewdocid[$i][0].$admin[286].$dbnewdocid[$i][2].$admin[287];
                    }
                    $reindex[$dbnewdocid[$i][0]] = TRUE;
                    $tempdocid[$k][0] = $dbnewdocid[$i][0];
                    $tempdocid[$k][1] = $dbnewdocid[$i][1];
                    $tempdocid[$k][2] = $dbnewdocid[$i][2];
                    $i++;
                    $j++;
                    $k++;
                } elseif ($dbdocid[@$j][1] == $dbnewdocid[@$i][1] && $dbdocid[@$j][2] == $dbnewdocid[@$i][2]) {
                    if ($quiet == 0) {
                        echo $admin[282].$dbnewdocid[$i][1].$admin[300];
                        echo $admin[284].$dbnewdocid[$i][1].$admin[285].$dbnewdocid[$i][0].$admin[286].$dbnewdocid[$i][2].$admin[287];
                    }
                    $reindex[$dbnewdocid[$i][0]] = TRUE;
                    $reindex[$dbdocid[$i][0]] = TRUE;
                    $tempdocid[$k][0] = $dbnewdocid[$i][0];
                    $tempdocid[$k][1] = $dbnewdocid[$i][1];
                    $tempdocid[$k][2] = $dbnewdocid[$i][2];
                    $i++;
                    $j++;
                    $k++;
                } elseif ($dbdocid[@$j][1] < $dbnewdocid[@$i][1] || ($dbdocid[@$j][1] > $docmax && $whiletoomuch == 1)) {
                    if ($quiet == 0) {
                        echo $admin[282].$dbdocid[$j][1].$admin[288];
                        echo $admin[284].$dbdocid[$j][1].$admin[285].$dbdocid[$j][0].$admin[286].$dbdocid[$j][2].$admin[287];
                    }
                    $reindex[$dbdocid[$i][0]] = TRUE;
                    $j++;
                } elseif ($dbdocid[$j][1] > $dbnewdocid[$i][1]) {
                    if ($quiet == 0) {
                        echo $admin[282].$dbnewdocid[$i][1].$admin[283];
                        echo $admin[284].$dbnewdocid[$i][1].$admin[285].$dbnewdocid[$i][0].$admin[286].$dbnewdocid[$i][2].$admin[287];
                    }
                    $reindex[$dbnewdocid[$i][0]] = TRUE;
                    $tempdocid[$k][0] = $dbnewdocid[$i][0];
                    $tempdocid[$k][1] = $dbnewdocid[$i][1];
                    $tempdocid[$k][2] = $dbnewdocid[$i][2];
                    $i++;
                    $k++;
                }
            }
            $whiletest = $tempdocid[$k-1][1];
        }
    }
    ksort($docdates);
    WriteDBFields(DOCDATES, $docdates);
    ksort($children);
    WriteDBFields(CHILDREN, $children);
    if (ReadCounter(NEXTID) != $tempdocid[count($tempdocid)-1][1]) {
        WriteCounter(NEXTID, $tempdocid[count($tempdocid)-1][1]);
        if ($quiet == 0) {
            echo $admin[304].' ('.$tempdocid[count($tempdocid)-1][1].')<br />';
        }
        $onerror++;
    }
    if ($onerror > 0) {
        WriteDBFields(DOCID, $tempdocid);
        if ($quiet == 0) {
            echo $admin[289];
        }
        $reindexstr = array(
            TYP_AGENDA => $admin[913],
            TYP_ART => $admin[290],
            TYP_REACT => $admin[479],
            TYP_BLOG => $admin[911],
            TYP_REBLOG => $admin[914],
            TYP_BSS => $admin[1120],
            TYP_DNLOAD => $admin[292],
            TYP_FAQ => $admin[912],
            TYP_FREEBOX => $admin[1122],
            TYP_FORUM => $admin[294],
            TYP_LINKS => $admin[291],
            TYP_MENUICO => $admin[1360],
            TYP_NEWS => $admin[293],
            TYP_DDMENUBOX => $admin[1358],
            TYP_OPTDDMENU => $admin[1361],
            TYP_MENUBOX => $admin[1359],
            TYP_OPTMENU => $admin[1362],
            TYP_PHOTO => $admin[332],
            TYP_RSS => $admin[1121],
            TYP_SOCNET => $admin[1363]
            );
        foreach ($reindex as $reindex_type => $val) {
            switch ($reindex_type) {
            case TYP_GUESTBK :
            case TYP_BANNER :
            case TYP_THINK :
            case TYP_RULES :
            case TYP_RECO :
                // Pas d'index à régénérer pour ces documents
                break;
            default :
                ActionOnFields('reindex', array($reindex_type, 0));
                if ($quiet == 0) {
                    echo isset($reindexstr[$reindex_type]) ? $reindexstr[$reindex_type] : '... reindex for "'.$reindex_type.'" type !<br>';
                }
            }
        }
    }
	$temp = ReadDBFields(DOCID);
	$docid = array();
	foreach ($temp as $tmp) {
		if (FileDBExist(DBBASE.TestFileId($tmp[1]).INCEXT)) {
			$docid[] = $tmp;
		}
	}
	WriteDBFields(DOCID, $docid);
    
    error_reporting($error_reporting);

    return $onerror;
}

function majIndexAROM() {
    global $lng, $lang;
    global $fielda1, $fielda2, $fieldd1, $fieldd2, $fieldweb;
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
                $pos   = strpos($fielda1, '-->');
                $flda1 = substr($fielda1, ($pos !== false ? $pos + 3 : 0));
                $pos   = strpos($fielda2, '-->');
                $flda2 = substr($fielda2, ($pos !== false ? $pos + 3 : 0));
				$mnus2 = $mnus3 = '';
                if (isset($menus[0][2]))
				{
					
					$pos   = strpos($menus[0][2], '-->');
					$mnus2 = substr($menus[0][2], ($pos !== false ? $pos + 3 : 0));
					$pos   = strpos($menus[0][3], '-->');
					$mnus3 = substr($menus[0][3], ($pos !== false ? $pos + 3 : 0));
				}
				else $menus[0][2] = '';
                $dbw[] = array($pg, $om[1], $fieldweb, $mnus2, $mnus3, $flda1, $flda2);
            }
        }
    }
    WriteDBFields(DBARTMENU, $dbw);
}
?>
