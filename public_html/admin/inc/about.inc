<?php
/*******************************************************************************
 *   About window
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
 * v6.00.19 (August 26, 2024) : Tohelp modify
 ******************************************************************************/

 if (stristr($_SERVER['SCRIPT_NAME'], 'about.inc')) {
  header('location:../index.php');
  die();
}

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.$admin[277];
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[277], "100%");
echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=946&mnuid=937&tconfig=0#z2').'</p>';
echo '
<div class="w-100 m-auto" style="max-width:640px;">
  <div class="row">
    <div class="col-md-4 text-center px-5" style="padding-right:10px;">
      <i class="gen-gylogo" title="Logo"></i>
    </div>
    <div class="col-md-8 text-left" style="font-size:14px;">
      <span style="font-size:20px;"><strong>GuppY v'.MP_VERSION.'</strong></span><br />
      '.MP_DATE.'<br />CeCILL Copyright&nbsp; <span style="font-size:14px;"><strong>&copy;</strong></span>&nbsp; '.MP_COPYRIGHT.' :
      <strong>Laurent Duveau</strong><br />
      Initiated by Laurent Duveau and Nicolas Alves<br />
	  V6 developed by Lud Bienaimé<br />
      &nbsp;&nbsp;&nbsp;with the participation of the GuppY Team
    </div>
  </div>
</div>
<br />
<p style="text-align:center;">GuppY\'s Web site&nbsp;:&nbsp;&nbsp;<a href="https://www.freeguppy.org/">https://www.freeguppy.org/</a></p>
<br />
<p style="text-align:center;"><strong>===&nbsp; Credits &nbsp;===</strong></p>
<div id="credit" class="rep w-98 m-auto text-left overflow-auto" style="max-width:640px;height:300px;">
The GuppY Team is made of real friendly
international people. Thanks to all of you guys!<br />
<br />
As many people have given a hand in this project,
I just cannot write something about all of them,
so <b>let me just focus on the team</b>
and <b>look at the scrolling area below.</b><br />
<br />
<strong>aldweb</strong> (that\'s me!, from France)<br />
- GuppY\'s creator and first developper.<br />
<br />
<strong>Saxbar</strong> (the "composer and musician" of php)<br />
- Saxbar, Since August 2011 all GuppY versions rose from his keyboard, developper GuppY 5.0<br />
  and this will not surprise you as computing, php and javascript writing <br />
  and last but not least music are his passions<br />
<br />  
<strong>JeandePeyrat</strong> (The "Servers\' servant!")<br />
- JeandePeyrat,  GuppY Team administrator, president of the association freeGuppY,<br />
  forum moderator,  freeGuppY servers maintenance has no secrets for Jean.<br />
<br />
<strong>Papinou</strong> (The "The wizard of CSS")<br />
- Papinou, administrator GuppY Team, Treasurer of the association freeGuppY,<br />
  forum moderator, interested in everything concerning CSS style sheets and not bad at all at English!<br />
<br />
<strong>Djchouix</strong> (The scientist)<br />
- Djchouix, a great developper working also noiselessy.<br />
  He is the author of the new editors of GuppY<br />
<br />
<strong>Corrector</strong> (The"king of translation")<br />
- Corrector, the English language is his second mother tongue, but the corrections<br />
  of the French language are also his line. He is especially hard on Frenglish.<br />
<br />
<strong>Sabine</strong> ("fairy design")<br />
- Sabine, in a jiffy you have an excellent design for a Guppy site and is in Quebec.<br />
  Check out the tutorial skins of 5.0.0 and you will be convinced!<br />
<br />
<strong>La Vache Qui Rêve</strong> (the dreaming cow! as she has named herself)<br />
- La Vache qui rêve, (GuppYed, tutos) GuppYed administrator and tutorials specialist<br />
  in addition to keeping our stable tidy.<br />
<br />
<strong>Fred68</strong> (GuppYed, tutos)<br />
- GuppYed administrator and  tutorials specialist, an excellent tutorial for CKEditor is one of his major works.<br />
<br />
<strong>Pascal31</strong> (GuppYLand)<br />
- GuppYLand administrator, designer of the homepage presentation.<br />
<br />
<strong>jerlal</strong> (GuppYLand)<br />
-  GuppYLand administrator, alpha versions beta-tester , or fierce squeak-buster ...<br />
<br />
Thanks to <strong>JeanMi</strong> for his help, for advice, for the solutions with perfect knowledge of GuppY!.<br />
<br />
Let\'s also thank all the GuppY Team, people who provided the GuppY Team with aid, nice comments and improvement ideas.<br />
<br />
</div>';
include CHEMIN.'admin/inc/generic.inc';
echo '
<p style="text-align:center;"><strong>===&nbsp; Licence &nbsp;===</strong></p>
<div id="licence" class="rep w-98 m-auto text-left overflow-auto p-4" style="max-width:640px;height:168px;">
This software is free, you can distribute it and/or
modify it accordingly to the CeCILL License.<br />
CEA  CNRS and INRIA, great French organizations released CeCILL in July 2004.
CeCILL is the first license defining the principles
of use and dissemination of Free Software in
conformance with French law, following the
principles of the GNU GPL.<br />
<br />
This software is distributed because it might be useful, but it comes WITHOUT ANY
WARRANTY OF ANY KIND, either expressed or implied,
including, but not limited to, the implied
warranties of merchantability and fitness for a
particular purpose. Please refer to
<a href="http://www.cecill.info/index.en.html" target="blank">http://www.cecill.info/index.en.html</a>
for more detailed information.<br />
<br />
You should have received a copy of the CeCILL free
 License at the same time you received this
software ; if this were not the case, please send
 an e-mail to "info@cecill.info".<br />
</div>';
include 'infos.inc'; 

btable();
include CHEMIN.'admin/bpage.inc';
?>
