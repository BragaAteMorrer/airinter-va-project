<?php
/*******************************************************************************
 *   Carnet de bord des administrateurs et rédacteurs
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
 * v6.00.07 (February 24, 2022) : adaptation to php 8.1
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "notes.inc")) {
	header("location:../index.php");
	die();
}

if ($serviz[3] == 'on' && isAdmin($userprefs[1])) {
    $lignes = ReadDBFields(ADMNOTES);
    $lignes1 = array();
    foreach ($lignes as $key => $ligne) {
        $lignes1[] = array_pad($ligne, 4, $key);
    }
    WriteDBFields(ADMNOTES, $lignes1);
    unset($lignes1);
    $lignes = ReadDBFields(ADMNOTES);
    $editor = import('editor');
    if ($editor == 'on') {
        $nblg = count($lignes);
        $idlg = import('idlg');
        $note = trim(stripslashes(import('editnote')));
        for ($i = 0; $i < $nblg; $i++) {
            if ($lignes[$i][3] == $idlg) $lignes[$i][2] = $note;
        }
        WriteDBFields(ADMNOTES, $lignes);
        $lignes = ReadDBFields(ADMNOTES);
    }
    $suppr  = import('suppr');
    if ($suppr == 'on') {
        $idlg    = import('idlg');
        $lignes1 = array();
        foreach ($lignes as $ligne) {
            if ($ligne[3] != $idlg) $lignes1[] = $ligne;
        }
        WriteDBFields(ADMNOTES, $lignes1);
        unset($lignes1);
        $lignes = ReadDBFields(ADMNOTES);
    }
    $nblg = count($lignes);
    for ($i = 0; $i < $nblg; $i++) {
        $lignes[$i][3] = $i;
    }
    WriteDBFields(ADMNOTES, $lignes);
    $lignes = ReadDBFields(ADMNOTES);
    $note = trim(stripslashes(import('note')));
    if (!empty($note)) {
        $lignes[] = array(
            GetCurrentDateTime(),
            $userprefs[1],
            $note,
            count($lignes)
            );
        while (count($lignes) > NB_NOTES) {
            array_shift($lignes);
        }
        WriteDBFields(ADMNOTES, $lignes);
    }
    $nblignes = count($lignes);
    echo '
  <style>
    .modal {
      display: inline-block;
      position: fixed;
      z-index: 99;
      left: calc(50% - 400px);
      top: 136px;
      width: 100%;
      max-width: 800px;
      height: auto;
      background-color: #FFF;
      border-radius: 8px;
    }
    @media screen and (max-width: 760px), screen and (max-device-width: 760px) {
      .modal {
        left: 0;
        right: 0;
        top: 0;
        width: 100%;
      }
    }
  </style>
  <fieldset>'.legend($admin[937]).'
    <div class="table-responsive-md overflow-auto" style="height:300px;">
      <table class="table table-striped table-bordered w-100 bg-white">';
    $lignes = array_reverse($lignes);
    for ($i = 0; $i < $nblignes; $i++) {
        echo '
        <tr>
          <td class="p-2" style="width:140px;">'.FormatDate($lignes[$i][0]).'</td>
          <td class="p-2" style="width:160px;">'.$lignes[$i][1].'</td>
          <td class="p-2">'.htmlentities($lignes[$i][2], ENT_QUOTES, $charset).'</td>
          <td class="text-center font-weight-bold p-2" style="width:70px;font-size:1.3rem;">
            <div class="d-inline-block">
              <i class="far fa-edit align-middle c-pointer" onmouseup="montre(\'edit'.$lignes[$i][3].'\')" title="'.$admin[144].'"></i>
            </div>
            <div id="edit'.$lignes[$i][3].'" class="modal border py-2 px-4" style="display: none;">
              <div class="modal-content border-0">
                <form name="editform" action="#" method="post">
                  <input type="hidden" name="validForm" value="send" />
                  <input type="hidden" name="editor" value="on" />
                  <input type="hidden" name="idlg" value="'.$lignes[$i][3].'" />
                  <input class="form-control" type="text" name="editnote" value="'.$lignes[$i][2].'" style="max-width:100%;font-size:14px;" />
                  <div class="text-center mt-3">
                    <div class="d-inline-block">'.SubmitButton($admin[38], 'cache(\'edit'.$lignes[$i][3].'\')').'</div>
                    <div class="d-inline-block">'.SubmitButton($admin[121], 'cache(\'edit'.$lignes[$i][3].'\')', '', '', 'button').'</div>
                  </div>
                  <div class="clearfix"></div>
                </form>
              </div>
            </div>
            <div class="d-inline-block">
              <i class="far fa-times-circle align-middle text-danger c-pointer" 
                title="'.$admin[186].'" onmouseup="ConfirmSuppr'.$lignes[$i][3].'()">
              </i>
              <script>
              function ConfirmSuppr'.$lignes[$i][3].'() {
                if (confirm("'.$admin[1028].' ?'.'")) {
                  window.location="admin.php?lng='.$lng.'&suppr=on&idlg='.$lignes[$i][3].'";
                }
              }
              </script>
            </div>
          </td>
        </tr>';
    }
    echo '
      </table>
    </div>';
    echo '
    <form name="notes" method="post" action="admin.php?lng='.$lng.'">
	  <div class="text-center w-100 mx-auto mt-3">
        <div class="d-inline-block">'.$admin[938].'</div>
        <div class="d-inline-block w-100" style="max-width:800px;">
	      <input class="form-control" type="text" name="note" style="font-size:14px;"/>
        </div>
  	    <div class="d-inline-block">'.SubmitButton($admin[38], '', '', $imgdroite).'</div>
	  </div>
    </form>
  </fieldset>
<hr />';
}
?>