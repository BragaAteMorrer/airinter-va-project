<h3>T&eacute;l&eacute;chargements</h3>
<?php
 
if(!$allcategories){
	echo 'Il n&#39;y a pas de t&eacute;l&eacute;chargement disponible!';
	return;
}
$ITFCat = array_flip(array('flottepremiereperiode', 'flottesecondeperiode','flottetroisiemeperiode','intercargoservice','aircharterinternational'));
$ITFCat = array_flip(array_change_key_case($ITFCat, CASE_LOWER));

$MyGroups = array();
$pilotid = Auth::PilotID();
$allgroups = PilotGroups::GetAllGroups();
foreach($allgroups as $group){
	if(PilotGroups::CheckUserInGroup($pilotid, $group->groupid)){
		array_push($MyGroups, trim($group->name));
	}
}
$MyGroups = array_flip(array_change_key_case(array_flip($MyGroups), CASE_LOWER));
$allcategories = DB::get_results('SELECT * FROM '.TABLE_PREFIX.'downloads WHERE pid=0 ORDER BY name');					
foreach($allcategories as $category){	
?>
  <table width="98%" align="center" padding="2">
    <tr style="background-color: #333; color: #FFF; padding: 5px;">
	  <td><?php echo substr($category->name, 3);?></td>
    </tr>
  </table>
  <!--
  <table id="tabledlist" class="tablesorter width="98%"">
    <thead>
    <tr>
	  <th>t&eacute;l&eacute;chargement</th>
	  <th>Description</th>
	  <th>Nombre de t&eacute;l&eacute;chargement</th>
    </tr>
  </thead>
  <tbody>
  </tbody>
  -->
  <ul>  
    <?php	
    # This loops through every download available in the category
    $alldownloads = DownloadData::GetDownloads($category->id);
    foreach ($MyGroups as $MyCat){
      if (!in_array($MyCat, $ITFCat)) {
        if (($MyCat == 'intercargoservice') or ($MyCat == 'aircharterinternational')){
         echo 'Vous n&#39;&ecirc;tes pas membre de cette filiale';
        } else {
          echo 'Vous n&#39;&ecirc;tes pas membre de cette p&eacute;riode';
        }
        if (!$alldownloads){
          echo 'Il n&#39;y a aucun t&eacute;l&eacute;chargement dans cette cat&eacute;gorie';
          $alldownloads = array();
        }
      } else {
     foreach($alldownloads as $download){
        ?>
        <li>
        <a href="<?php echo url('/downloads/dl/'.$download->id);?>">
        <?php echo $download->name;?></a><br />
        <?php echo $download->description;?><br />
        <em><?php echo $download->hits;?> t&eacute;l&eacute;chargement(s)</em>
        </li><br />            
        <?php  
      }
    }  
    ?>
    <br />
    <?php
  }
  ?>
  </ul>
<?php
}
?>
