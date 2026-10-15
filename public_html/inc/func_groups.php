<?php
/*******************************************************************************
 *   Functions for groups
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

if (stristr($_SERVER['SCRIPT_NAME'], 'func_groups.php')) {
    header('location:../index.php');
    die();
}

/*******************************************************************************
 * Lecture du fichier GROUPS pour fournir un tableau associatif
 * dont l'index est le nom d'un groupe et
 * dont le contenu est la liste des membres de ce groupe
 *
 * @return array
 ******************************************************************************/
function ReadGroups() {
    if (!File_Exists(GROUPS)) return array();
    $dbf = ReadDbFields(GROUPS);
    $grps = array();
    foreach($dbf as $f) {
        $grp = $f[0];
        unset($f[0]);
        $grps[$grp] = array_values($f);
    }
    return $grps;
}

/*******************************************************************************
 * Ecriture du fichier GROUPS
 * A partir du tableau associatif fourni par ReadGroups()
 *
 * @param array $grps (une liste de groupes)
 * @return void
 ******************************************************************************/
function WriteGroups($grps) {
    ksort($grps);
    $dbf = array();
    foreach ($grps as $grp=>$mbrs) {
        sort($mbrs);
        $dbf[] = array_merge(array($grp), $mbrs);
    }
    WriteDbFields(GROUPS, $dbf);
}

/*******************************************************************************
 * Lecture du fichier MEMBERS
 * pour fournir un tableau associatif
 * dont l'index est le nom d'un membre et
 * dont le contenu est la liste des groupes contenant ce membre
 *
 * @return array
 ******************************************************************************/
function ReadMembers() {
    if (!File_Exists(MEMBERS)) return array();
    $dbf = ReadDbFields(MEMBERS);
    $mbrs = array();
    foreach($dbf as $f) {
        $mbr = $f[0];
        unset($f[0]);
        $mbrs[$mbr] = array_values($f);
    }
    return $mbrs;
}

/*******************************************************************************
 * Ecriture du fichier MEMBERS
 * A partir d'un tableau associatif fourni par ReadMembers()
 *
 * @param array $mbrs (une liste de membres)
 * @return void
 ******************************************************************************/
function WriteMembers($mbrs) {
    ksort($mbrs);
    $dbf = array();
    foreach ($mbrs as $mbr=>$grps) {
        sort($grps);
        $dbf[] = array_merge(array($mbr), $grps);
    }
    WriteDbFields(MEMBERS, $dbf);
}

/*******************************************************************************
 * Ajout d'un groupe dans le fichier GROUPS
 *
 * @param string $grp (un nom de groupe)
 * @return boolean (TRUE = réussite, FALSE = échec)
 ******************************************************************************/
function AddGroup($grp) {
    if ('all_members' == $grp) return FALSE;
	$grps = ReadGroups();
    if (array_key_exists($grp, $grps)) {
        return FALSE; // Le groupe existe déjà  !
    } else {
        $grps[$grp] = array();
        WriteGroups($grps);
        return TRUE;
    }
}

/*******************************************************************************
 * Suppression d'un groupe de membres dans le fichier GROUPS
 * avec mise à jour du fichier MEMBERS
 *
 * @param string $grp (un nom de groupe)
 * @return boolean (TRUE = réussite, FALSE = échec)
 ******************************************************************************/
function DelGroup($grp) {
    $grps = ReadGroups();
    if (!array_key_exists($grp, $grps)) {
        return FALSE; // Le groupe n'existe pas !
    } else {
        unset($grps[$grp]);
        WriteGroups($grps);
        $mbrs1 = ReadMembers();
        $mbrs2 = array();
        foreach ($mbrs1 as $mbr=>$grps) {
            $find = array_search($grp, $grps);
            if (FALSE !== $find) {
                unset($grps[$find]);
                $grps = array_values($grps);
            }
            $mbrs2[$mbr] = $grps;
        }
        WriteMembers($mbrs2);
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
function CheckGroup($grp, $mbr) {
	global $userprefs;
	if ($grp == 'all_members' && !empty($userprefs[1])) return TRUE;
    $grps = ReadGroups();
    if (!array_key_exists($grp, $grps)) {
        return FALSE; // Le groupe n'existe pas !
    } else {
        $find = array_search($mbr, $grps[$grp]);
        return FALSE !== $find;
    }
}

/*******************************************************************************
 * Ajout d'un membre dans un groupe
 * avec mise à jour des fichiers MEMBERS et GROUPS
 *
 * @param string $grp (un nom de groupe)
 * @param string $mbr (un nom de membre)
 * @return boolean (TRUE = réussite, FALSE = échec)
 ******************************************************************************/
function AddMember($grp, $mbr) {
    $grps = ReadGroups();
    if (!array_key_exists($grp, $grps)) {
        return FALSE; // Le groupe n'existe pas !
    } else {
        $mbrs = $grps[$grp];
        if (in_array($mbr, $mbrs)) {
            return FALSE; // Le membre existe déjà  !
        } else {
            $grps[$grp][] = $mbr;
            WriteGroups($grps);
            $mbrs = ReadMembers();
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
            if ($change) WriteMembers($mbrs);
            return TRUE;
        }
    }
}

/*******************************************************************************
 * Retrait d'un membre d'un groupe
 * avec mise à jour des fichiers MEMBERS et GROUPS
 *
 * @param string $grp (un nom de groupe)
 * @param string $mbr (un nom de membre)
 * @return boolean (TRUE = réussite, FALSE = échec)
 ******************************************************************************/
function DelMember($grp, $mbr) {
    $grps = ReadGroups();
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
                WriteGroups($grps);
            }
            $mbrs = ReadMembers();
            $find = array_search($grp, $mbrs[$mbr]);
            if (FALSE !== $find) {
                unset($mbrs[$mbr][$find]);
                $mbrs[$mbr] = array_values($mbrs[$mbr]);
                WriteMembers($mbrs);
            }
            return TRUE;
        }
    }
}

/*******************************************************************************
 * Suppression d'un membre 
 * avec mise à jour des fichiers MEMBERS et GROUPS
 *
 * @param string $mbr (un nom de membre)
 * @return void
 ******************************************************************************/
function DestroyMember($mbr) {
    $mbrs = ReadMembers();
    unset($mbrs[$mbr]);
    WriteMembers($mbrs);
    unset($mbrs);
    
    $grps1 = ReadGroups();
    $grps2 = array();
    foreach ($grps1 as $grp=>$mbrs) {
        $find = array_search($mbr, $mbrs);
        if (FALSE !== $find) {
            unset($mbrs[$find]);
            $mbrs = array_values($mbrs);
        }
        $grps2[$grp] = $mbrs;
    }
    WriteGroups($grps2);
}

/*******************************************************************************
 * Contrôle l'existance des membres présents dans les fichiers GROUPS et MEMBERS
 *
 * @return void
 ******************************************************************************/
function CheckMembers() {
    $mbrs = ReadMembers();
    foreach ($mbrs as $mbr=>$grps) {
        if (!file_exists(USEREP.$mbr.DBEXT)) {
            DestroyMember($mbr);
        }
    }
}

/*******************************************************************************
 * Contrôle du droit d'accès à un document
 *
 * @param string $group (un nom de groupe)
 * @return boolean (TRUE = réussite, FALSE = échec)
 ******************************************************************************/
function isAccessGranted($group, $user='') {
    global $userprefs, $serviz;
    if (empty($user)) $user = $userprefs[1];
    if ($user == $serviz[31] || $user == $serviz[176]) return TRUE;
	if ($group == 'all_members' && !empty($user)) return TRUE;
    if (empty($group)) return TRUE;
    if (empty($user)) return FALSE;
    return CheckGroup($group, $user);
}

/*******************************************************************************
 * Contrôle du droit d'accès à un document du forum
 *
 * @param string $catforum (une catégorie de forum "id,groupe")
 * @return boolean (TRUE = réussite, FALSE = échec)
 ******************************************************************************/
function isForumAccessGranted($catforum) {
    global $userprefs, $serviz;
    if ($userprefs[1] == $serviz[31] || $userprefs[1] == $serviz[176]) return TRUE;
    $catforum = getForumCatFull($catforum);
    if (FALSE !== strpos($catforum, ',')) {
        $temp  = explode(',', $catforum);
        $group = empty($temp[1]) ? '' : trim($temp[1]);
    } else {
        return TRUE;
    }
	if ($group == 'all_members' && !empty($userprefs[1])) return TRUE;
    if (empty($group)) return TRUE;
    if (empty($userprefs[1])) return FALSE;
    return CheckGroup($group, $userprefs[1]);
}

/*******************************************************************************
 * Contrôle si une catégorie de discussion du forum existe bien
 *
 * param string $cat (identifiant de la catégorie)
 * @return boolean (TRUE = réussite, FALSE = échec)
 ******************************************************************************/
function IsValidForumCat($cat) {	
	$cats = ReadForumCats();
	if (!empty($cat) && in_array($cat, $cats)) {
			return TRUE;
	}
	return FALSE;
}


/*******************************************************************************
 * Vérifie si une catégorie de discussion du forum est privée ou non
 *
 * param string $cat (identifiant de la catégorie)
 * @return boolean (TRUE = réussite, FALSE = échec)
 ******************************************************************************/
function IsPrivateForumCat($cat) {
	$cats = ReadForumCats();
    if (!empty($cat)) {
        foreach ($cats as $categ) {
            $categs = explode(',', $categ);
            if ($cat == $categs[0] && isset($categs[1])) {
                return TRUE;
            }
        }
    }
	return FALSE;
}

/*******************************************************************************
 * Lecture du fichier FORUM_CATS
 * pour fournir un tableau associatif
 * dont l'index est le nom d'un membre et
 * dont le contenu est la liste des groupes contenant ce membre
 *
 * return array
 ******************************************************************************/ 
function ReadForumCats() {
    if (!File_Exists(DBFORUMCAT)) return array();
	
    $dbwork = ReadDbFields(DBFORUMCAT);
	
	$cats = array();
	for ($i = 0; $i < count($dbwork); $i++) {
		$cats[] = $dbwork[$i][0]; //id de la catégorie
	}

    return $cats;
}
?>
