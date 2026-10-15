<h3>D&eacute;tail de l&#39;horaire</h3>
<div class="indent">
<strong>Num&eacute;ro de vol: </strong> <?php echo $schedule->code.$schedule->flightnum ?><br />
<strong>D&eacute;part: </strong><?php echo $schedule->depname ?> (<?php echo $schedule->depicao ?>) &agrave; <?php echo $schedule->deptime ?><br />
<strong>Arriv&eacute;e: </strong><?php echo $schedule->arrname ?> (<?php echo $schedule->arricao ?>) &agrave; <?php echo $schedule->arrtime ?><br />
<?php
if($schedule->route!='')
{ ?>
<strong>Route: </strong><?php echo $schedule->route ?><br />
<?php
}?>
<br />
<strong>Information M&eacute;t&eacute;o</strong>
<div id="<?php echo $schedule->depicao ?>" class="metar">Obtention des informations METAR actuels de <?php echo $schedule->depicao ?></div>
<div id="<?php echo $schedule->arricao ?>" class="metar">Obtention des informations METAR actuels de <?php echo $schedule->arricao ?></div>
<br />
<strong>Fr&eacute;quence de l&#39;horaire</strong>
<div align="center">
<?php
/*
	Added in 2.0!
*/
$chart_width = '800';
$chart_height = '170';

/* Don't need to change anything below this here */
?>
<div align="center" style="width: 100%;">
	<div align="center" id="pireps_chart"></div>
</div>

<script type="text/javascript" src="<?php echo fileurl('/lib/js/ofc/js/swfobject.js')?>"></script>
<script type="text/javascript">
swfobject.embedSWF("<?php echo fileurl('/lib/js/ofc/open-flash-chart.swf');?>", 
	"pireps_chart", "<?php echo $chart_width;?>", "<?php echo $chart_height;?>", 
	"9.0.0", "expressInstall.swf", 
	{"data-file":"<?php echo actionurl('/schedules/statsdaysdata/'.$schedule->id);?>"});
</script>
<?php
/* End added in 2.0
*/
?>
</div>

