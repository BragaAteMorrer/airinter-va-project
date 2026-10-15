<?php
	if($redir == '')
		$redir = SITE_URL;
		
?><div align="center">
<p>Vous serez redirig&eacute; dans quelques secondes, ou cliquez ci-dessous pour &ecirc;tre redirig&eacute;.</p>
<p><a href="<?php echo $redir;?>">Cliquez ici pour &ecirc;tre redirig&eacute;</a></p>
</div>

<script type="text/javascript"> 
    window.location = "<?php echo $redir;?>";
</script>