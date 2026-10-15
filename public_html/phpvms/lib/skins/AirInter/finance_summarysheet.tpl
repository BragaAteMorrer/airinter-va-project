<?php
/*
 * DO NOT EDIT THIS TEMPLATE UNLESS:
 *   1. YOU HAVE ALOT OF TIME
 *   2. YOU DON'T MIND LOSING SOME HAIR
 *   3. YOU HAVE BIG BALLS MADE OF STEEL
 *
 *	It can cause incontinence
 *
 *	YOU HAVE BEEN WARNED!!!
 */
?><?php Template::Show('finance_header.tpl'); ?>
<h3><?php echo $title?></h3>
<?php

	$total = 0;
	
	$profit = array();
	$pilot_pay = array();
	$revenue = array();	
	$expenses = array();
	$flightexpenses = array();
	$fuelexpenses = array();
	$months=array();
	
?>
<table width="600px" class="balancesheet" cellpadding="0" cellspacing="0">

	<tr class="balancesheet_header" style="text-align: center">
		<td align="left">Mois</td>
		<td align="center">#Vols</td>
		<td align="left">Revenu</td>
		<td align="center" nowrap>Salaires Pilotes</td>
		<td align="left">D&eacute;penses</td>
		<td align="left">Carburant</td>
		<td align="center" nowrap>Co&ucirc;ts Vols</td>
		<td align="center">Total</td>
	</tr>
	
<?php 
foreach ($allfinances as $month)
{
?>
	<tr>
		<td align="right">
			<?php 
			$months[] = date('M', $month['timestamp']);
			echo date('F', $month['timestamp']);
			?>
		</td>
		<td align="center">
		<?php 
			$flights[] = $month['pirepfinance']->TotalFlights==''?0:$month['pirepfinance']->TotalFlights;
			echo ($month['pirepfinance']->TotalFlights=='') ? 0 : $month['pirepfinance']->TotalFlights;
		?>
		</td>
		<td align="right" nowrap>
			<?php 
			$revenue[] = ($month['pirepfinance']->Revenue=='')?0:$month['pirepfinance']->Revenue;
			echo FinanceData::FormatMoney($month['pirepfinance']->Revenue);
			?>
		</td>
		<td align="right" nowrap>
			<?php 
			$pilot_pay[] = $month['pirepfinance']->TotalPay;
			echo FinanceData::FormatMoney($month['pirepfinance']->TotalPay);
			?>
		</td>
		<td align="right" nowrap>
			<?php 
			$expenses[] = $month['totalexpenses']==''?0:$month['totalexpenses'];
			echo FinanceData::FormatMoney((-1)*$month['totalexpenses']);
			?>
		</td>
		<td align="right" nowrap>
			<?php 
			$fuelexpenses[] = $month['fuelcost']==''?0:$month['fuelcost'];
			echo FinanceData::FormatMoney((-1)*$month['fuelcost']);
			?>
		</td>
		<td align="right" nowrap>
			<?php 
			$flightexpenses[] = $month['flightexpenses']==''?0:$month['flightexpenses'];
			echo FinanceData::FormatMoney((-1)*$month['flightexpenses']);
			?>
		</td>
		<td align="right" nowrap>
			<?php 
			$profit[] = round($month['total'], 2);
			$total+=$month['total'];
			echo FinanceData::FormatMoney($month['total']);
			?>
		</td>
	</tr>
<?php
}
?>
<tr class="balancesheet_header" style="border-bottom: 1px dotted">
	<td align="" colspan="8" style="padding: 1px;"></td>
</tr>
	
<tr>
	<td align="right" colspan="6"><strong>Total:</strong></td>
	<td align="right" colspan="2"><strong><?php echo FinanceData::FormatMoney($total);?></strong></td>
</tr>
	
</table>

<h3>D&eacute;tail</h3>

<?php
/**
 * Show the revenue details graph
 */

$graph = new ChartGraph('pchart', 'line', 680, 400);
$graph->setFontSize(8);
$graph->AddData($profit, $months);
$graph->setTitles('Profit mensuel', 'Mois', 'Revenu ('.htmlspecialchars_decode(Config::Get('MONEY_UNIT')).')');
$graph->GenerateGraph();

?>
<br /><br />
<?php
/*
	Show the expenses details graph
*/
$graph1 = new ChartGraph('pchart', 'line', 680, 400);
$graph1->AddData($fuelexpenses, $months);
$graph1->setTitles('Co&#251;t du carburant', 'Mois', 'D&#233;penses ('.htmlspecialchars_decode(Config::Get('MONEY_UNIT')).')');
$graph1->GenerateGraph();

?>