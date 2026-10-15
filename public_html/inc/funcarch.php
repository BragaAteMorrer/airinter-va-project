<?php
/*******************************************************************************
 *   Archives Specific Functions
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
 * v6.00.15 (November 29, 2023) : correction archive forum
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "funcarch.php")) {
    header("location:../index.php");
    die();
}

function UpdateDocCounterArch($id) {
    $DataDB = ReadDocCounter($id, ARCHDBCOUNT);
    $vote = DejaVote(ARCHDBIPBASE.$id.DBEXT, 300);
    if ($vote[0] == false) {
        $DataDB++;
        WriteDocCounter($id, $DataDB, ARCHDBCOUNT);
    }
    return $DataDB;
}

function UpdateDBdtbArch($dtb, $idxarch) {
    global $type,$fileid,$status,$creadate,$moddate,$author,$email,
        $fielda1,$fielda2,$fieldb1,$fieldb2,$fieldd1,$fieldd2,$fieldc1,$fieldc2,$fieldweb,$fieldmail,$fieldmod;
    @set_time_limit(0);
    $db  = SelectDBFields($dtb, 'a', '', DOCIDARCH);
    $dba = array();
    $j = 0;
    foreach($db as $dbs) {
        ReadDoc($dbs[1], ARCHDBBASE);
        $dba[$j]= array(
            RemoveConnector($fielda1),
            RemoveConnector($fielda2),
            RemoveConnector($fieldb1),
            RemoveConnector($fieldb2),
            $fileid,
            $creadate,
            $fieldmod,
			$type
            );
        $j++;
    }
    WriteDBFields($idxarch, $dba);
}
function UpdateDBreblogArch() {
    global $type, $fileid, $status, $creadate, $moddate, $author, $email, 
        $fielda1,$fielda2,$fieldb1,$fieldb2,$fieldd1,$fieldd2,$fieldc1,$fieldc2,$fieldweb,$fieldmail,$fieldmod;
    @set_time_limit(0);
    $dbs = SelectDBFields(TYP_REBLOG, 'a', '', DOCIDARCH);
    $dbr = array();
    $j = 0;
    foreach($dbs as $db) {
		ReadDoc($db[1], ARCHDBBASE);
//		$dbr = array_pad(array(), 7, '');
		$dbr[$j][0] = $fileid;
		$dbr[$j][1] = $fielda2;
		$dbr[$j][5] = $creadate;
		ReadDoc($fielda2, ARCHDBBASE);
		$dbr[$j][2] = $fieldb1;
		$dbr[$j][3] = $fieldb2;
		$dbr[$j][6] = $fieldmod;
        $j++;
    }
	WriteDBFields(DBREBLOGARCH, $dbr);
}

function UpdateDBforumArch() {
    global $site,$type,$fileid,$status,$creadate,$moddate,$author,$email,
        $fielda1,$fielda2,$fieldb1,$fieldb2,$fieldc1,$fieldc2,$fieldd1,$fieldd2,$fieldweb,$fieldmail,$fieldmod;
    @set_time_limit(0);
    $db = ReadDBFields(DOCIDARCH);
    @sort($db);
    $dbf = array();
    $j   = 0;
    foreach ($db as $dbw) {
        if ($dbw[2] == "a") {
            ReadDoc($dbw[1], ARCHDBBASE);
            $dbf[$j][0] = $creadate;
            $dbf[$j][1] = $fielda1;
            $dbf[$j][2] = $fielda2;
            $dbf[$j][3] = $fileid;
            $dbf[$j][4] = RemoveConnector($author);
            $dbf[$j][5] = RemoveConnector($email);
            $dbf[$j][6] = RemoveConnector($fieldb1);
            $dbf[$j][7] = $fieldd1;
            $dbf[$j][8] = $fieldb2;
            $j++;
        }
    }
    @sort($dbf, SORT_REGULAR);
    WriteDBFields(DBTHREADARCH, $dbf);
    $db  = ReadDBFields(DBTHREADARCH);
    $dbf = array();
    $j = 0;
    for ($i = 0; $i < count($db); $i++) {
        if ($db[$i][2] == "0") {
            $dbf[$j][0] = $db[$i][0];
            $dbf[$j][1] = $db[$i][1];
            $dbf[$j][2] = $db[$i][3];
            $dbf[$j][3] = $db[$i][4];
            $dbf[$j][4] = $db[$i][5];
            $dbf[$j][5] = $db[$i][6];
            $dbf[$j][6] = $db[$i][0];
            $dbf[$j][7] = 0;
            $dbf[$j][8] = "";
            $dbf[$j][9] = "";
            $dbf[$j][10] = $db[$i][7];
            $dbf[$j][11] = "";
            $dbf[$j][12] = $db[$i][8];
            for ($k = $i + 1; $k < count($db); $k++) {
                if ($db[$k][1] == $dbf[$j][1]) {
                    $dbf[$j][0] = $db[$k][0];
                    $dbf[$j][7] = $dbf[$j][7]+1;
                    $dbf[$j][8] = $db[$k][4];
                    $dbf[$j][9] = $db[$k][5];
                    $dbf[$j][11] = $db[$k][7];
                }
            }
            $j++;
        }
    }
    @rsort($dbf, SORT_REGULAR);
    WriteDBFields(DBFORUMARCH, $dbf);
}

?>
