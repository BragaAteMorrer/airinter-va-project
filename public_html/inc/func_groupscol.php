<?php
/*******************************************************************************
 *   Functions for collaborators GROUPSCOL
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
 * v6.00.00 (December 15, 2020) : initial release
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'func_groupscol.php')) {
    header('location:../index.php');
    die();
}

/*******************************************************************************
 * Lecture du fichier GROUPSCOL pour fournir un tableau associatif
 * dont l'index est le nom d'un groupe et
 * dont le contenu est la liste des membres de ce groupe
 *
 * @return array
 ******************************************************************************/
function ReadGroupsCol() {
    if (!File_Exists(GROUPSCOL)) return array();
    $dbf = ReadDbFields(GROUPSCOL);
    $grps = array();
    foreach($dbf as $f) {
        $grp = $f[0];
        unset($f[0]);
        $grps[$grp] = array_values($f);
    }
    return $grps;
}

/*******************************************************************************
 * Ecriture du fichier GROUPSCOL
 * A partir du tableau associatif fourni par ReadGroupsCol()
 *
 * @param array $grps (une liste de groupes)
 * @return void
 ******************************************************************************/
function WriteGroupsCol($grps) {
    ksort($grps);
    $dbf = array();
    foreach ($grps as $grp=>$mbrs) {
        sort($mbrs);
        $dbf[] = array_merge(array($grp), $mbrs);
    }
    WriteDbFields(GROUPSCOL, $dbf);
}

/*******************************************************************************
 * Lecture du fichier MEMBERSCOL
 * pour fournir un tableau associatif
 * dont l'index est le nom d'un membre et
 * dont le contenu est la liste des groupes contenant ce membre
 *
 * @return array
 ******************************************************************************/
function ReadMembersCol() {
    if (!File_Exists(MEMBERSCOL)) return array();
    $dbf  = ReadDbFields(MEMBERSCOL);
    $mbrs = array();
    foreach($dbf as $f) {
        $mbr = $f[0];
        unset($f[0]);
        $mbrs[$mbr] = array_values($f);
    }
    return $mbrs;
}

/*******************************************************************************
 * Ecriture du fichier MEMBERSCOL
 * A partir d'un tableau associatif fourni par ReadMembersCol()
 *
 * @param array $mbrs (une liste de membres)
 * @return void
 ******************************************************************************/
function WriteMembersCol($mbrs) {
    ksort($mbrs);
    $dbf = array();
    foreach ($mbrs as $mbr=>$grps) {
        sort($grps);
        $dbf[] = array_merge(array($mbr), $grps);
    }
    WriteDbFields(MEMBERSCOL, $dbf);
}

/*******************************************************************************
 * Ajout d'un groupe dans le fichier GROUPSCOL
 *
 * @param string $grp (un nom de groupe)
 * @return boolean (TRUE = réussite, FALSE = échec)
 ******************************************************************************/
function AddGroupCol($grp) {
    if ('ALL' == $grp) return FALSE;
	$grps = ReadGroupsCol();
    if (array_key_exists($grp, $grps)) {
        return FALSE; // Le groupe existe déjà  !
    } else {
        $grps[$grp] = array();
        WriteGroupsCol($grps);
        return TRUE;
    }
}

/*******************************************************************************
 * Suppression d'un groupe de membres dans le fichier GROUPSCOL
 * avec mise à jour du fichier MEMBERSCOL
 *
 * @param string $grp (un nom de groupe)
 * @return boolean (TRUE = réussite, FALSE = échec)
 ******************************************************************************/
function DelGroupCol($grp) {
    $grps = ReadGroupsCol();
    if (!array_key_exists($grp, $grps)) {
        return FALSE; // Le groupe n'existe pas !
    } else {
        unset($grps[$grp]);
        WriteGroupsCol($grps);
        $mbrs1 = ReadMembersCol();
        $mbrs2 = array();
        foreach ($mbrs1 as $mbr=>$grps) {
            $find = array_search($grp, $grps);
            if (FALSE !== $find) {
                unset($grps[$find]);
                $grps = array_values($grps);
            }
            $mbrs2[$mbr] = $grps;
        }
        WriteMembersCol($mbrs2);
        return TRUE;
    }
}

/*******************************************************************************
 * Recheche d'un membre d'un groupe
 *
 * @param string $grp (un nom de groupe)
 * @param string $mbr (un nom de membre)
 * @return boolean (TRUE = réussite, FALSE = échec)
 ******************************************************************************/
function CheckGroupCol($grp, $mbr) {
	global $userprefs;
	if ($grp == 'ALL' && !empty($userprefs[1])) return TRUE;
    $grps = ReadGroupsCol();
    if (!array_key_exists($grp, $grps)) {
        return FALSE; // Le groupe n'existe pas !
    } else {
        $find = array_search($mbr, $grps[$grp]);
        return FALSE !== $find;
    }
}

/*******************************************************************************
 * Ajout d'un membre dans un groupe
 * avec mise à jour des fichiers MEMBERSCOL et GROUPSCOL
 *
 * @param string $grp (un nom de groupe)
 * @param string $mbr (un nom de membre)
 * @return boolean (TRUE = réussite, FALSE = échec)
 ******************************************************************************/
function AddMemberCol($grp, $mbr) {
    $grps = ReadGroupsCol();
    if (!array_key_exists($grp, $grps)) {
        return FALSE; // Le groupe n'existe pas !
    } else {
        $mbrs = $grps[$grp];
        if (in_array($mbr, $mbrs)) {
            return FALSE; // Le membre existe déjà  !
        } else {
            $grps[$grp][] = $mbr;
            WriteGroupsCol($grps);
            $mbrs = ReadMembersCol();
            $change = FALSE;
            if (!array_key_exists($mbr, $mbrs)) {
                // Le membre n'existe pas encore
                $mbrs[$mbr] = array($grp);
                $change = TRUE;
            } else {
                if (!in_array($grp, $mbrs[$mbr])) {
                    // Le groupe n'est pas encore dans la liste
                    $mbrs[$mbr][] = $grp;
                    $change = TRUE;
                }
            }
            if ($change) WriteMembersCol($mbrs);
            return TRUE;
        }
    }
}

/*******************************************************************************
 * Retrait d'un membre d'un groupe
 * avec mise à jour des fichiers MEMBERSCOL et GROUPSCOL
 *
 * @param string $grp (un nom de groupe)
 * @param string $mbr (un nom de membre)
 * @return boolean (TRUE = réussite, FALSE = échec)
 ******************************************************************************/
function DelMemberCol($grp, $mbr) {
    $grps = ReadGroupsCol();
    if (!array_key_exists($grp, $grps)) {
        return FALSE; // Le groupe n'existe pas !
    } else {
        if (!in_array($mbr, $grps[$grp])) {
            return FALSE; // Le membre n'existe pas !
        } else {
            $find = array_search($mbr, $grps[$grp]);
            if (FALSE !== $find) {
                unset($grps[$grp][$find]);
                $grps[$grp] = array_values($grps[$grp]);
                WriteGroupsCol($grps);
            }
            $mbrs = ReadMembersCol();
            $find = array_search($grp, $mbrs[$mbr]);
            if (FALSE !== $find) {
                unset($mbrs[$mbr][$find]);
                $mbrs[$mbr] = array_values($mbrs[$mbr]);
                WriteMembersCol($mbrs);
            }
            return TRUE;
        }
    }
}

/*******************************************************************************
 * Suppression d'un membre 
 * avec mise à jour des fichiers MEMBERSCOL et GROUPSCOL
 *
 * @param string $mbr (un nom de membre)
 * @return void
 ******************************************************************************/
function DestroyMemberCol($mbr) {
    $mbrs = ReadMembersCol();
    unset($mbrs[$mbr]);
    WriteMembersCol($mbrs);
    unset($mbrs);
    
    $grps1 = ReadGroupsCol();
    $grps2 = array();
    foreach ($grps1 as $grp=>$mbrs) {
        $find = array_search($mbr, $mbrs);
        if (FALSE !== $find) {
            unset($mbrs[$find]);
            $mbrs = array_values($mbrs);
        }
        $grps2[$grp] = $mbrs;
    }
    WriteGroupsCol($grps2);
}

/*******************************************************************************
 * Contrôle l'existance des membres présents dans les fichiers GROUPSCOL et MEMBERSCOL
 *
 * @return void
 ******************************************************************************/
function CheckMembersCol() {
    $mbrs = ReadMembersCol();
    foreach ($mbrs as $mbr=>$grps) {
        if (!file_exists(REDACREP.$mbr.INCEXT)) {
            DestroyMemberCol($mbr);
        }
    }
}

/*******************************************************************************
 * Contrôle du droit d'accès à un document
 *
 * @param string $group (un nom de groupe)
 * @return boolean (TRUE = réussite, FALSE = échec)
 ******************************************************************************/
function isAccessGrantedCol($group) {
    global $userprefs;
	if ($group == 'ALL' && !empty($userprefs[1])) return TRUE;
    if (empty($userprefs[1])) return FALSE;
    return CheckGroupCol($group, $userprefs[1]);
}
?>
