<?php
$conf=$ms->getConfig($_COOKIE['Valvola-id']);
$configs=$ms->getTimer($_COOKIE['Valvola-id']);
?>
<div class="mdl-grid pomp-content">
	<div class="pomp-card-wide mdl-card mdl-shadow--2dp">
		<div class="mdl-card__title">
			<h2 class="mdl-card__title-text">Log Valvola <?php echo $configs['nome'];?></h2>
			<div class="temperature"><span></span></div>
		</div>
		<div class="mdl-card__supporting-text"><?php if($_COOKIE['Valvola-id']!=0){ ?>
		<table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp table-timer">
			<thead>
				<tr>
					<th>Evento</th>
					<th>Ora</th>
				</tr>
			</thead>
			<tbody>
			<tr>
			<?php
			$confArray=$ms->getLog($_COOKIE['Valvola-id']);
			if($confArray){
				while($logArray=$confArray->fetch_array()){
					echo "<tr><td>",$logArray['msg'],"</td><td>",$logArray['data'],"</td></tr>";
				}
			}
			?>
			</tr>
			</tbody>
		</table>
	  	<?php } else {
			echo "Devi impostare almeno una valvola";
		} ?>
		</div>
	</div>
</div>
<script>
	$(function() {
  	$.ajax({
			url: "<?php echo URL_BASE; ?>includes/ajax.php",
			type: "POST",
			data: "temp=1&ip=<?php echo $conf['ip'];?>",
			beforeSend: function() {
						//$(".text_red").text("");
						//$(".text_orange").text("Connessione in corso con la valvola");
					},
			success: function(result){
				if(result!="errore"){
					if(result>=30){
						$(".temperature span").css( "color", "red" );
						$(".temperature span").text(result+"°C");
					} else if(result>=20&&result<30){
						$(".temperature span").css( "color", "green" );
						$(".temperature span").text(result+"°C");
					} else if(result<20){
						$(".temperature span").css( "color", "blue" );
						$(".temperature span").text(result+"°C");
					}
				}	
			}
		});
	});
</script>