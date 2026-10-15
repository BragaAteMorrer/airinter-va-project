<?php
/*******************************************************************************
 *   Functions for groups newsletters
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

if (stristr($_SERVER['SCRIPT_NAME'], 'func_groupsnwl.php')) {
  header('location:../index.php');
  die();
}

/*******************************************************************************
 * Lecture du fichier GROUPSNWL pour fournir un tableau associatif
 * dont l'index est le nom d'un groupe et
 * dont le contenu est la liste des abonnés de ce groupe
 *
 * @return array
 ******************************************************************************/
function ReadGroupsNewsletter() {
    if (!File_Exists(GROUPSNWL)) return array();
    $dbf  = ReadDbFields(GROUPSNWL);
    $grps = array();
    foreach($dbf as $f) {
        $grp = $f[0];
        unset($f[0]);
        $grps[$grp] = array_values($f);
    }
    return $grps;
}
/*******************************************************************************
 * Lecture du fichier GROUPSNWL pour fournir un tableau associatif
 * dont la liste des groupe 
 * dont la liste des membres 
 *
 * @return array
 ******************************************************************************/
function ReadAbonnesGroupsNewsletter() {
    if (!File_Exists(GROUPSNWL)) return array();
    $dbf  = ReadDbFields(GROUPSNWL);
    $grps = array();
	$k    = 0;
    foreach($dbf as $f) {
        $grp = $f[0];
        unset($f[0]);
		$grps["group"][$k]  = $grp;
        $grps["abonne"][$k] = array_values($f);
		$k++;
    }
    return $grps;
}

/*******************************************************************************
 * Ecriture du fichier GROUPSNWL
 * A partir du tableau associatif fourni par ReadGroupsNewsletter()
 *
 * @param array $grps (une liste de groupes)
 * @return void
 ******************************************************************************/
function WriteGroupsNewsletter($grps) {
    ksort($grps);
    $dbf = array();
    foreach ($grps as $grp=>$abonnes) {
        sort($abonnes);
        $dbf[] = array_merge(array($grp), $abonnes);
    }
    WriteDbFields(GROUPSNWL, $dbf);
}

/*******************************************************************************
 * Lecture du fichier MEMBERSNWL
 * pour fournir un tableau associatif
 * dont l'index est le nom d'un abonne et
 * dont le contenu est la liste des groupes contenant cet abonné
 *
 * @return array
 ******************************************************************************/
function ReadAbonnes() {
    if (!File_Exists(MEMBERSNWL)) return array();
    $dbf = ReadDbFields(MEMBERSNWL);
    $abonnes = array();
    foreach($dbf as $f) {
        $abonne = $f[0];
        unset($f[0]);
        $abonnes[$abonne] = array_values($f);
    }
    return $abonnes;
}

/*******************************************************************************
 * Ecriture du fichier MEMBERSNWL
 * A partir d'un tableau associatif fourni par ReadAbonnes()
 *
 * @param array $abonnes (une liste d'abonnés)
 * @return void
 ******************************************************************************/
function WriteAbonnes($abonnes) {
    ksort($abonnes);
    $dbf = array();
    foreach ($abonnes as $abonne=>$grps) {
        sort($grps);
        $dbf[] = array_merge(array($abonne), $grps);
    }
	$mrnombreabonnes = count($dbf);
	$dbfabonnes = array();
	for ($ij = 0; $ij < count($dbf); $ij++) { 
		if (isset($dbf[$ij][1]) && $dbf[$ij][1] == '')
            unset ($dbf[$ij]);
		else
            $dbfabonnes[] = $dbf[$ij];
	}
    WriteDbFields(MEMBERSNWL, $dbfabonnes);
}

/*******************************************************************************
 * Ajout d'un groupe dans le fichier GROUPSNWL
 *
 * @param string $grp (un nom de groupe)
 * @return boolean (TRUE = réussite, FALSE = échec)
 ******************************************************************************/
function AddGroupNewsletter($grp) {
	if ('all_subscribers' == $grp) return FALSE;
    $grps = ReadGroupsNewsletter();
    if (array_key_exists($grp, $grps)) {
        return FALSE; // Le groupe existe déjà  !
    } else {
        $grps[$grp] = array();
        WriteGroupsNewsletter($grps);
        return TRUE;
    }
}

/*******************************************************************************
 * Suppression d'un groupe d'abonnes dans le fichier GROUPSNWL
 * avec mise à jour du fichier MEMBERSNWL
 *
 * @param string $grp (un nom de groupe)
 * @return boolean (TRUE = réussite, FALSE = échec)
 ******************************************************************************/
function DelGroupNewsletter($grp) {
    $grps = ReadGroupsNewsletter();
    if (!array_key_exists($grp, $grps)) {
        return FALSE; // Le groupe n'existe pas !
    } else {
        unset($grps[$grp]);
        WriteGroupsNewsletter($grps);
        $abonnes1 = ReadAbonnes();
        $abonnes2 = array();
        foreach ($abonnes1 as $abonne=>$grps) {
            $find = array_search($grp, $grps);
            if (FALSE !== $find) {
                unset($grps[$find]);
                $grps = array_values($grps);
            }
            $abonnes2[$abonne] = $grps;
        }
        WriteAbonnes($abonnes2);
        return TRUE;
    }
}

/*******************************************************************************
 * Recherche d'un abonné d'un groupe
 *
 * @param string $grp (un nom de groupe)
 * @param string $abonne (un nom d'abonné)
 * @return boolean (TRUE = réussite, FALSE = échec)
 ******************************************************************************/
function CheckGroupNewsletter($grp, $abonne) {
    $grps = ReadGroupsNewsletter();
    if (!array_key_exists($grp, $grps)) {
        return FALSE; // Le groupe n'existe pas !
    } else {
        $find = array_search($abonne, $grps[$grp]);
        return FALSE !== $find;
    }
}

/*******************************************************************************
 * Ajout d'un abonné dans un groupe
 * avec mise à jour des fichiers MEMBERSNWL et GROUPSNWL
 *
 * @param string $grp (un nom de groupe)
 * @param string $abonne (un nom d'abonné)
 * @return boolean (TRUE = réussite, FALSE = échec)
 ******************************************************************************/
function AddAbonne($grp, $abonne) {
    $grps = ReadGroupsNewsletter();
    if (!array_key_exists($grp, $grps)) {
        return FALSE; // Le groupe n'existe pas !
    } else {
        $abonnes = $grps[$grp];
        if (in_array($abonne, $abonnes)) {
            return FALSE; // L'abonné existe déjà !
        } else {
            $grps[$grp][] = $abonne;
            WriteGroupsNewsletter($grps);
            $abonnes = ReadAbonnes();
            $change  = FALSE;
            if (!array_key_exists($abonne, $abonnes)) {
                // L' abonné n'existe pas encore
                $abonnes[$abonne] = array($grp);
                $change = TRUE;
            } else {
                if (!in_array($grp, $abonnes[$abonne])) {
                    // Le groupe n'est pas encore dans la liste
                    $abonnes[$abonne][] = $grp;
                    $change = TRUE;
                }
            }
            if ($change) WriteAbonnes($abonnes);
            return TRUE;
        }
    }
}

/*******************************************************************************
 * Retrait d'un abonné d'un groupe
 * avec mise à jour des fichiers MEMBERSNWL et GROUPSNWL
 *
 * @param string $grp (un nom de groupe)
 * @param string $abonne (un nom d'abonné)
 * @return boolean (TRUE = réussite, FALSE = échec)
 ******************************************************************************/
function DelAbonne($grp, $abonne) {
    $grps = ReadGroupsNewsletter();
    if (!array_key_exists($grp, $grps)) {
        return FALSE; // Le groupe n'existe pas !
    } else {
        if (!in_array($abonne, $grps[$grp])) {
            return FALSE; // L' abonne n'existe pas !
        } else {
            $find = array_search($abonne, $grps[$grp]);
            if (FALSE !== $find) {
                unset($grps[$grp][$find]);
                $grps[$grp] = array_values($grps[$grp]);
                WriteGroupsNewsletter($grps);
            }
            $abonnes = ReadAbonnes();
            $find = array_search($grp, $abonnes[$abonne]);
            if (FALSE !== $find) {
                unset($abonnes[$abonne][$find]);
                $abonnes[$abonne] = array_values($abonnes[$abonne]);
                WriteAbonnes($abonnes);
            }
            return TRUE;
        }
    }
}

/*******************************************************************************
 * Suppression d'un abonne 
 * avec mise à jour des fichiers MEMBERSNWL et GROUPSNWL
 *
 * @param string $abonne (un nom d'abonné)
 * @return void
 ******************************************************************************/
function DestroyAbonne($abonne) {
    $abonnes = ReadAbonnes();
    unset($abonnes[$abonne]);
    WriteAbonnes($abonnes);
    unset($abonnes);
    
    $grps1 = ReadGroupsNewsletter();
    $grps2 = array();
    foreach ($grps1 as $grp=>$abonnes) {
        $find = array_search($abonne, $abonnes);
        if (FALSE !== $find) {
            unset($abonnes[$find]);
            $abonnes = array_values($abonnes);
        }
        $grps2[$grp] = $abonnes;
    }
    WriteGroupsNewsletter($grps2);
}
?>
