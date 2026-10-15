<h3>Recherche d&#039;horaire</h3>
<form id="form" action="<?php echo actionurl('/schedules/view');?>" method="post">

<div id="tabcontainer">
	<ul>
		<li><a href="#depapttab"><span>Par l&#39;a&eacute;roport de D&eacute;part</span></a></li>
		<li><a href="#arrapttab"><span>Par l&#39;a&eacute;roport d&#039;Arriv&eacute;e</span></a></li>
		<!--
		<li><a href="#aircrafttab"><span>Par le type d&#039;&eacute;quipement</span></a></li>
		-->
		<li><a href="#distance"><span>Par la Distance</span></a></li>
	</ul>
	<div id="depapttab">
		<p>A&eacute;roport de D&eacute;part:</p>
		<select id="depicao" name="depicao">
		<option value="">Tous</option>
		<?php
		if(!$depairports) $depairports = array();
			foreach($depairports as $airport)
			{
				echo '<option value="'.$airport->icao.'">'.$airport->icao
						.' ('.$airport->name.')</option>';
			}
		?>
			
		</select>
		<input type="submit" name="submit" value="Rechercher" />
	</div>
	<div id="arrapttab">
		<p>A&eacute;roport d&#039;arriv&eacute;e:</p>
		<select id="arricao" name="arricao">
			<option value="">Tous</option>
		<?php
		if(!$depairports) $depairports = array();
			foreach($depairports as $airport)
			{
				echo '<option value="'.$airport->icao.'">'.$airport->icao
						.' ('.$airport->name.')</option>';
			}
		?>
		</select>
		<input type="submit" name="submit" value="Rechercher" />
	</div>
	<!--	
	<div id="aircrafttab">
		<p>Choisir l&#39;&eacute;quipement:</p>
		<select id="equipment" name="equipment">
			<option value="">Selection &eacute;quipement</option>
		<?php
		
		if(!$equipment) $equipment = array();
		foreach($equipment as $equip)
		{
			echo '<option value="'.$equip->name.'">'.$equip->name.'</option>';
		}
		
		?>
		</select>
		<input type="submit" name="submit" value="Rechercher" />
	</div>
	-->
	<div id="distance">
		<p>Choisir Distance:</p>
		<select id="type" name="type">
			<option value="greater">Plus Que</option>
			<option value="less">Moins Que</option>
		</select>
		<input type="text" name="distance" value="" />
		<input type="submit" name="submit" value="Rechercher" />
	</div>
</div>

<p>
<input type="hidden" name="action" value="findflight" />
</p>
</form>
<script type="text/javascript">

</script>
<hr>