Bonjour <?php echo $pilot->firstname.' '.$pilot->lastname?>,

Vous avez &eacute;t&eacute; marqu&eacute; comme &eacute;tant &agrave; la retraite parce que vous avez &eacute;t&eacute; inactif pendant plus de <?php echo Config::Get('PILOT_INACTIVE_TIME')?> jours. Pour &ecirc;tre r&eacute;activ&eacute;, vous devez d&eacute;poser un PIREP.


Merci,
Les administrateurs d&#39;<?php echo SITE_NAME; ?>