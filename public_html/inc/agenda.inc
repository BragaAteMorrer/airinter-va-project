<?php
/*******************************************************************************
 *   Agenda display
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2021 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.21 (December 17, 2024) : Validation Php 8.4
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'agenda.inc')) {
    header('location:../index.php');
    die();
}
#[AllowDynamicProperties]
class GY_agenda {
    public $tconfig;
    public $topmess;
    protected $monthlist;
    protected $daylist;
    protected $an;
    protected $mois;
    protected $id;
    protected $idpg;
    protected $agv;
    
    public function __construct() {
        global $web, $serviz;
        $this->an        = import('an');
        $this->mois      = import('mois');
        $this->id        = import('id');
        $this->idpg      = import('idpg');
        $this->tconfig   = $serviz[171] == 'ALL' ? import('tconfig', '', true, 0) : $serviz[171];
        $this->agv       = import('agv');
        $this->monthlist = array($web[268], $web[269], $web[270], $web[271], $web[272], $web[273],
                                 $web[274], $web[275], $web[276], $web[277], $web[278], $web[279]);
        $this->daylist   = array(0 => $web[532], $web[533], $web[534], $web[535], $web[536], $web[537], $web[538]);
    }
    
    public function AGV_ok($pseudo, $lng) {
        global $site, $urlrw, $serviz, $web, $z2, $members;
        $this->topmess = $web[436];
        if ($serviz[47] != 'on') {
            $out = '
    <div class="text-center web342">'.$web[143];
            $href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng);
            $out .= '
      <p class="text-center">
        '.SubmitButton($web[135], 'self.location.href=\''.$href1.'\'').'
      </p>
    </div>';
            return array('AGV0', $out);
        }
        elseif (!isAccessGranted($members[14])) 
		{
				$out = '
		<div class="web342 text-center">'.$web[342];
				if ($members[19] != '0' && $members[19] != '3') 
				{
					$href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[3].'-0-'.$this->tconfig.$z2 : 'connect.php?lng='.$lng.'&tconfig='.$this->tconfig.$z2);
					$href2 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[15].'-5-new-'.$this->tconfig.$z2 : $site['USR'].'.php?lng='.$lng.'&uuser=new&tconfig='.$this->tconfig.$z2);
					$out .= '
		  <p class="text-center">
			'.SubmitButton($web[630], 'self.location.href=\''.$href1.'\'').
			  (empty($pseudo) ? '&nbsp;&nbsp;'.SubmitButton($web[160], 'self.location.href=\''.$href2.'\'') : '').'
		  </p>';
				}
				$out .= '
		</div>';
				return array('AGV0', $out);
        }
        return $this->agv;
    }
    
    public function AGV_day($lng) {
        global $web, $site, $serviz, $urlrw, $prt, $z2, $pg, $lang;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, 
               $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
               $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        $pgid = explode('-', $this->idpg);
        $wag  = array();
        for ($a = 0; $a < count($pgid) - 1; $a++) {
            if (!is_file(DBBASE.TestFileId($pgid[$a]).INCEXT)) continue;
            ReadDoc($pgid[$a]);
            if ($type != TYP_AGENDA) continue;
            if (!isAccessGranted($fieldmod) || !isAuthorizedPublication($creadate)) continue;
            $wag[$a][0] = $fielda2;
            $wag[$a][1] = $fileid;
        }
        if (empty($wag)) {
            $this->topmess = $web[436];
            return array('AGV1', '<p class="text-center">'.$web[290].'</p>');
        }
        sort($wag);
        for ($jj = 0; $jj < count($wag); $jj++) {
            ReadDoc($wag[$jj][1]);
			$datcre = $creadate;
			$datmod = $moddate;
            $agtxt1 = substr($fielda1, 6, 2);
            $agtxt2 = substr($fielda1, 4, 2);
            $agtxt3 = substr($fielda1, 0, 4);
            $agtime = array();
            $agtime = explode('-', $fielda2);
			$agtime = array_pad($agtime, 2, '');
            if ($agtime[0] < '10:00') $agtime[0] = substr($agtime[0], 1, 7);
            if ($agtime[1] < '10:00') $agtime[1] = substr($agtime[1], 1, 7);
            if ($site[22] == 'H2'){
                $agtime[0] = str_replace(':', 'h', $agtime[0]);
                $agtime[1] = str_replace(':', 'h', $agtime[1]);
            }
            if ($agtime[0] != '' && $agtime[1] != '') $til = '<br />&darr;<br />';
            elseif ($agtime[0] != '' && $agtime[1] == '') $til= '<br />&darr;<br />&#133;';
            elseif ($agtime[0] == '' && $agtime[1] != '') $til = '&#133;<br />&darr;<br />';
            else $til = '';
            $mthname = $this->monthlist[$agtxt2-1];
            $dd      = date('w', mktime(0, 0, 0, (int)$agtxt2, (int)$agtxt1, (int)$agtxt3));
            $dayname = $this->daylist[$dd];
            if ($lng == $lang[0]) {
                $agtxt4 = $fieldc1;
            } else {
                $agtxt4 = empty($fieldc2) ? '<em>'.$web[442].'</em><br /><br />'.$fieldc1 : $fieldc2;
            }
            if ($jj == 0 ) {
                if ($web[284] == '0') {
                    $datecountry = $dayname.' '.$agtxt1.' '.$mthname.' '.$agtxt3;
                } elseif ($web[284] == '1') {
                    $datecountry = $mthname.' '.$agtxt1.' '.$agtxt3.', '.$dayname;
                } elseif ($web[284] == '2') {
                    $datecountry = $agtxt3.' '.$mthname.' '.$agtxt1.', '.$dayname;
                } else {
                    $datecountry = '';
                }
                if (!empty($datecountry)) {
                    $datecountry = '
                    <a href="'.$site[3].($site['URLR'] == 'on' 
                        ? $lng.'-'.$urlrw[17].'-22-'.$this->idpg.'-'.$pg.'-1-'.$this->tconfig.$z2 
                        : 'agenda.php?lng='.$lng.'&amp;idpg='.$this->idpg.'&amp;pg='.$pg.'&amp;agv=1&amp;tconfig='.$this->tconfig.$z2).'" title="'.$web[511].'">
                      '.$datecountry.'
                    </a>';
                }
                $out = '
    <br />';
            }
            $out .= '
    <div class="table-responsive-md">
    <div class="bord d-table m-auto w-98">
      <div class="d-table-row">
        <div class="quest d-table-cell text-center align-top calagvL font-weight-bold">'.$agtime[0].$til.$agtime[1].'</div>
        <div class="rep d-table-cell text-left align-top calagvR w-100">'.getNewUpdateDoc($datcre, $datmod, $serviz[145]).$agtxt4;
            if (empty($prt)) $out .= displayQuickConfig(NULL, $pgid[$jj], 'agenda&amp;form=2&amp;id='.$pgid[$jj], '', '', 37);
            $out .= '
        </div>
      </div>
    </div>
    </div>';
        }
        $out .= '
    <p class="text-center">
      <a href="'.($site['URLR'] == 'on' ? 
          $lng.'-'.$urlrw[17].'-21-'.$agtxt2.'-'.$agtxt3.'-2-'.$this->tconfig.$z2 : 
          'agenda.php?lng='.$lng.'&amp;mois='.$agtxt2.'&amp;an='.$agtxt3.'&amp;agv=2&amp;tconfig='.$this->tconfig.$z2).'">
        '.$web[286].' '.$mthname.' '.$agtxt3.'
      </a>
    </p>
    <br />';
        $this->topmess = $web[285].' '.$datecountry;
        return array('AGV1', $out);
    }
    
    public function AGV_month($lng) {
        global $web, $site, $serviz, $urlrw, $prt, $z2, $pg, $lang;
        global $type, $fileid, $status, $creadate, $moddate, $author, $email, 
               $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
               $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
        if (empty($this->id)) $this->id = 1;
        if (!$this->mois && !$this->an) {
            $this->mois = date('m');
            $this->an   = date('Y');
        }
        if ($this->mois < 1) {
            $this->mois = 12;
            $this->an--;
        }
        if ($this->mois > 12) {
            $this->mois = 1;
            $this->an++;
        }
        $mthname   = $this->monthlist[$this->mois - 1];
        $permalink = '
          <a href="'.$site[3].($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[17].'-21-'.$this->mois.'-'.$this->an.'-2-'.$this->tconfig.$z2 
              : 'agenda.php?lng='.$lng.'&amp;mois='.$this->mois.'&amp;an='.$this->an.'&amp;agv=2&amp;tconfig='.$this->tconfig.$z2).'" title="'.$web[511].'">
            '.$mthname.' '.$this->an.'
          </a>';
        $dbwork = ReadDBFields(DBAGENDA);
        @sort($dbwork);
		$dbannee = array();
		foreach($dbwork as $dbw) {
			$ann = substr($dbw[2], -4);
			if (!in_array($ann, $dbannee)) $dbannee[] = $ann;
		}
		$ann = date('Y');
		if (!in_array($this->an, $dbannee)) $dbannee[] = $ann;
		@sort($dbannee);
        $out = '
    <br />
    <div class="table-responsive-md">
    <div class="d-table m-auto">
      <div class="d-table-row">
        <div class="d-table-cell calendarg">';
		$ann_p = $this->an - ($this->mois == 1 ? 1 : 0);
		$ann_s = $this->an + ($this->mois == 12 ? 1 : 0);
		if ($dbannee[0] <= $ann_p) {
			$out .= '
          <a href="'.($site['URLR'] == 'on' ? 
              $lng.'-'.$urlrw[17].'-21-'.sprintf('%02d', $this->mois-1).'-'.$this->an.'-2-'.$this->tconfig.$z2 : 
              'agenda.php?lng='.$lng.'&amp;mois='.sprintf('%02d', $this->mois-1).'&amp;an='.$this->an.'&amp;agv=2&amp;tconfig='.$this->tconfig.$z2).'">
            <i class="fas fa-arrow-left"></i>
          </a>';
		}
		$out .= '
        </div>
        <div class="d-table-cell calendard"><b>'.$permalink.'</b></div>
        <div class="d-table-cell calendard">';
		if ($dbannee[count($dbannee)-1] >= $ann_s) {
			$out .= '
          <a href="'.($site['URLR'] == 'on' ? 
              $lng.'-'.$urlrw[17].'-21-'.sprintf('%02d', $this->mois + 1).'-'.$this->an.'-2-'.$this->tconfig.$z2 : 
              'agenda.php?lng='.$lng.'&amp;mois='.sprintf('%02d', $this->mois + 1).'&amp;an='.$this->an.'&amp;agv=2&amp;tconfig='.$this->tconfig.$z2).'">
            <i class="fas fa-arrow-right"></i>
          </a>';
		}
		$out .= '
        </div>
      </div>
    </div>
    </div>';
        $mthag = array();
        for ($i = 0; $i < count($dbwork); $i++) {
            if ($dbwork[$i][2] == $this->mois.$this->an && isAccessGranted($dbwork[$i][6]) && isAuthorizedPublication($dbwork[$i][5])) {
                $mthag[] = $dbwork[$i][4];
            }
        }
        $controle = CheckDB1Field(DBAGENDA, $this->mois.$this->an, 2);
        if ($controle) {
            $lastday    = '';
            $currentday = '';
            $k          = 0;
            if (3 == abs($prt)) {
                $this->id   = 1;
                $serviz[46] = 99999;
            }
            for ($i = $serviz[46]*($this->id - 1); $i < Min($serviz[46] * $this->id, count($mthag)); $i++) {
                $k++;
                ReadDoc($mthag[$i]);
				$datcre     = $creadate;
				$datmod     = $moddate;
                $currentday = $fielda1;
                $agtime     = array();
                $agtime     = explode('-', $fielda2);
				$agtime     = array_pad($agtime, 2, '');
                if ($agtime[0] < '10:00') $agtime[0] = substr($agtime[0], 1, 7);
                if ($agtime[1] < '10:00') $agtime[1] = substr($agtime[1], 1, 7);
                if ($site[22] == 'H2'){
                    $agtime[0] = str_replace(':', 'h', $agtime[0]);
                    $agtime[1] = str_replace(':', 'h', $agtime[1]);
                }
                if ($agtime[0] != '' && $agtime[1] != '') $til = '<br />&darr;<br />';
                elseif ($agtime[0] != '' && $agtime[1] == '') $til= '<br />&darr;<br />&#133;';
                elseif ($agtime[0] == '' && $agtime[1] != '') $til = '&#133;<br />&darr;<br />';
                else $til = '';
				$agtxt1 = substr($fielda1, 6, 2);
				$agtxt2 = substr($fielda1, 4, 2);
				$agtxt3 = substr($fielda1, 0, 4);
                $dd = date('w', mktime(0, 0, 0, (int)$agtxt2, (int)$agtxt1, (int)$agtxt3));
                $dayname = $this->daylist[$dd];
                if ($lng == $lang[0]) {
                    $agtxt4 = $fieldc1;
                } else {
                    $agtxt4 = empty($fieldc2) ? '<em>'.$web[442].'</em><br /><br />'.$fieldc1 : $fieldc2;
                }
                if ($web[284]=='0') {
                    $datecountry = $dayname.' '.$agtxt1.' '.$mthname.' '.$agtxt3;
                }
                if ($web[284]=='1') {
                    $datecountry = $mthname.' '.$agtxt1.' '.$agtxt3.', '.$dayname;
                }
                if ($web[284]=='2') {
                    $datecountry = $agtxt3.' '.$mthname.' '.$agtxt1.', '.$dayname;
                }
                if ($currentday != $lastday) {
                    $out .= '
    <div class="table-responsive-md">
    <div class="bord d-table w-98 m-auto">
      <div class="d-table-row">
        <div class="forum2 d-table-cell"><b>'.$datecountry.'</b></div>
      </div>
    </div>
    </div>';
                }
                $out .= '
    <div class="table-responsive-md">
    <div class="bord d-table w-98 m-auto">
      <div class="d-table-row">
        <div class="quest d-table-cell text-center align-top calagvL font-weight-bold">'.$agtime[0].$til.$agtime[1].'<br /></div>
        <div class="rep d-table-cell text-left align-top calagvR w-100">'.getNewUpdateDoc($datcre, $datmod, $serviz[145]).$agtxt4;
                if (empty($prt)) $out .= displayQuickConfig(NULL, $mthag[$i], 'agenda&amp;form=2&amp;id='.$mthag[$i], '', '', 37);
                $out .= '
        </div>
      </div>
    </div>
    </div>';
                $lastday = $currentday;
            }
        } else {
            $out .= '
    <p class="text-center">'.$web[290].'</p>';
        }
        $out .= GetNavBar(($site['URLR'] == 'on' ? 
          $lng.'-'.$urlrw[17].'-23-'.$this->mois.'-'.$this->an.'-2-'.$this->tconfig.'-' : 
          'agenda.php?lng='.$lng.'&amp;mois='.$this->mois.'&amp;an='.$this->an.'&amp;agv=2&amp;tconfig='.$this->tconfig.'&amp;id='), count($mthag), $this->id, $serviz[46], $z2);
		$this->topmess = $web[287].' '.$mthname.' '.$this->an;
        return array('AGV2', $out);
    }
}
?>