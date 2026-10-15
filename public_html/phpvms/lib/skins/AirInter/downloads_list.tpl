<h3>T&eacute;l&eacute;chargements</h3>
<?php
if(!$allcategories){
	echo 'Il n&#39;y a pas de t&eacute;l&eacute;chargement disponible!';
	return;
}
$ITFCat = array_flip(array('flottepremiereperiode', 'flottesecondeperiode','flottetroisiemeperiode','intercargoservice','aircharterinternational'));
$ITFCat = array_flip(array_change_key_case($ITFCat, CASE_LOWER));

$MyGroups = array();
$MyPilotid = Auth::PilotID();
$allgroups = PilotGroups::GetAllGroups();
foreach($allgroups as $group){
	if(PilotGroups::CheckUserInGroup($MyPilotid, $group->groupid)){
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
	
	<table id="tabledlist" class="tablesorter" style="float: left;" width="98%">
  <thead>
	<tr>
    <th>Nom</th>
    <th>Description</th>
    <th>Popularit&eacute;</th>
	</tr>
  </thead>
  <tbody>
  <?php
  $CanContinue = false;
  # This loops through every download available in the category
  $alldownloads = DB::get_results('SELECT * FROM '.TABLE_PREFIX.'downloads WHERE pid='.$category->id. " ORDER BY name");
  switch ($category->name) {
    case "1 - Flotte Periode 1":
    if (!in_array($ITFCat[0], $MyGroups)) { 
      ?>
      <tr>
        <td><strong><?php echo 'Vous n&#39;&ecirc;tes pas membre de cette p&eacute;riode';?></strong></td>           
      </tr> 
      <?php
    } else {
      $CanContinue = true;
    }
    break;
    case "2 - Flotte Periode 2":
    if (!in_array($ITFCat[1], $MyGroups)) {
      ?>
      <tr>
        <td><strong><?php echo 'Vous n&#39;&ecirc;tes pas membre de cette p&eacute;riode';?></strong></td>             
      </tr>
      <?php 
    } else {
      $CanContinue = true;
    }
    break;
    case "3 - Flotte Periode 3":
    if (!in_array($ITFCat[2], $MyGroups)) {
      ?>
      <tr>
        <td><strong><?php echo 'Vous n&#39;&ecirc;tes pas membre de cette p&eacute;riode';?></strong></td>              
      </tr> 
      <?php
    } else {
      $CanContinue = true;
    }
    break;
    case "4 - Inter Cargo Service":
    if (!in_array($ITFCat[3], $MyGroups)) {
      ?>
      <tr>
        <td><strong><?php echo 'Vous n&#39;&ecirc;tes pas membre de cette filiale';?></strong></td>            
      </tr> 
      <?php
    } else {
      $CanContinue = true;
    }
    break;
    case "5 - Air Charter International ":
    if (!in_array($ITFCat[4], $MyGroups)) {
      ?>
      <tr>
        <td><strong><?php echo 'Vous n&#39;&ecirc;tes pas membre de cette filiale';?></strong></td>              
      </tr> 
      <?php
    } else {
      $CanContinue = true;
    }    
    break;
    Default:
    $CanContinue = true;
    break;
  }
  //sort($alldownloads, SORT_STRING);
  if ($CanContinue) {
     foreach($alldownloads as $download){
        ?>
        <tr>
          <td> <a href="<?php echo url('/downloads/dl/'.$download->id);?>"><?php echo substr($download->name, 4);?></a></td>
          <td> <?php echo $download->description;?></td>      
          <td> <?php echo $download->hits;?></td>	      
	      </tr>        
     <?php
     }
  }
  ?>
  </tbody>
  </table> <?php 
  $CanContinue = false;
}
?>
