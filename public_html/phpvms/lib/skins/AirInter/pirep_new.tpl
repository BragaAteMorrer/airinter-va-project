<h3>Introduire un PIREP</h3>
<?php
if(isset($message))
	echo '<div id="error">'.$message.'</div>';
?>
<form action="<?php echo url('/pireps/mine');?>" method="post">
<dl>
	<dt>Pilote:</dt>
	<dd><strong><?php echo Auth::$userinfo->firstname . ' ' . Auth::$userinfo->lastname;?></strong></dd>
	
	<dt>Compagnie:</dt>
	<dd>
		<select name="code" id="code">
			<option value="">S&eacute;lectionner la Compagnie</option>
		<?php
		foreach($allairlines as $airline)
		{
			$sel = ($_POST['code'] == $airline->code || $bid->code == $airline->code)?'selected':'';
				
			echo '<option value="'.$airline->code.'" '.$sel.'>'.$airline->code.' - '.$airline->name.'</option>';
		}
		?>
		</select>
	</dd>
	
	<dt>Entrer le num&eacute;ro de Vol:</dt>
	<dd><input type="text" name="flightnum" value="<?php if(isset($bid->flightnum)) { echo $bid->flightnum; }?><?php if(isset($_POST['flightnum'])) { echo $_POST['flightnum'];} ?>" /></dd>
	
	<dt>A&eacute;roport de d&eacute;part:</dt>
	<dd>
		<div id="depairport">
		<select id="depicao" name="depicao">
			<option value="">S&eacute;lectionner l&#39;a&eacute;roport de d&eacute;part</option>
			<?php
			foreach($allairports as $airport)
			{
				$sel = ($_POST['depicao'] == $airport->icao || $bid->depicao == $airport->icao)?'selected':'';
				
				echo '<option value="'.$airport->icao.'" '.$sel.'>'.$airport->icao . ' - '.$airport->name .'</option>';
			}
			?>
		</select>
		</div>
	</dd>
	
	<dt>A&eacute;roport d&#39;arriv&eacute;e:</dt>
	<dd>
		<div id="arrairport">
		<select id="arricao" name="arricao">
			<option value="">S&eacute;lectionner l&#39;a&eacute;roport d&#39;arriv&eacute;e</option>
			<?php
			foreach($allairports as $airport)
			{
				$sel = ($_POST['arricao'] == $airport->icao || $bid->arricao == $airport->icao)?'selected':'';
				
				echo '<option value="'.$airport->icao.'" '.$sel.'>'.$airport->icao . ' - '.$airport->name .'</option>';
			}
			?>
		</select>
		</div>
	</dd>
	
	<dt>S&eacute;lectionner l&#39;&eacute;quipement:</dt>
	<dd>
		<select name="aircraft" id="aircraft">
			<option value="">S&eacute;lectionner l&#39;&eacute;quipement pour ce vol</option>
		<?php
		
		foreach($allaircraft as $aircraft)
		{
			$sel = ($_POST['aircraft'] == $aircraft->name || $bid->registration == $aircraft->registration)?'selected':'';
			
			echo '<option value="'.$aircraft->id.'" '.$sel.'>'.$aircraft->name.' - '.$aircraft->registration.'</option>';
		}
		?>
		</select>
	</dd>

	<?php
	// List all of the custom PIREP fields
	if(!$pirepfields) $pirepfields = array();
	foreach($pirepfields as $field)
	{
	?>
		<dt><?php echo $field->title ?></dt>
		<dd>
		<?php
		
		// Determine field by the type
		
		if($field->type == '' || $field->type == 'text')
		{
		?>
			<input type="text" name="<?php echo $field->name ?>" value="<?php echo $_POST[$field->name] ?>" />
		<?php
		} 
		elseif($field->type == 'textarea')
		{
			echo '<textarea name="'.$field->name.'">'.$field->values.'</textarea>';
		}
		elseif($field->type == 'dropdown')
		{
			$values = explode(',', $field->options);
			
			echo '<select name="'.$field->name.'">';
			foreach($values as $value)
			{
				$value = trim($value);
				echo '<option value="'.$value.'">'.$value.'</option>';
			}
			echo '</select>';		
		}
		?>
		
		</dd>
	<?php
	}
	?>
	
	<dt>Carburant Consomm&eacute;</dt>
	<dd><input type="text" name="fuelused" value="<?php echo $_POST['fuelused']; ?>" />
	<!--
		<p>Carburant consomm&eacute; durant ce vol en <?php echo Config::Get('LIQUID_UNIT_NAMES', Config::Get('LiquidUnit'))?></p></dd>
	-->
	<p>Carburant consomm&eacute; durant ce vol en <?php echo Config::Get('LIQUID_UNIT_NAMES', Config::Get('LiquidUnit'))?></p></dd>
	<dt>Temps de Vol (bloc/bloc)</dt>
	<dd><input type="text" name="flighttime" value="<?php echo $_POST['flighttime'] ?>" />
		<p>Entrer en heures - "5.3" est 5 heures et 30 minutes, "0.5" est 50 minutes</p></dd>
		
	<dt>Commentaire</dt>
	<dd><textarea name="comment" style="width: 100%"><?php echo $_POST['comment'] ?></textarea></dd>
	
	<dt></dt>
	<dd><?php $bidid = ( isset($bid) )? $bid->bidid:$_POST['bid']; ?>
		<input type="hidden" name="bid" value="<?php echo $bidid ?>" />
		<input type="submit" name="submit_pirep" value="Envoyer le PIREP" /></dd>
</dl>

</form>