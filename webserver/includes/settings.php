<?php
//include("class/sql.php");
$configs=$ms->getConfig($_COOKIE['Valvola-id']);
?>
<div class="mdl-grid pomp-content">
	<div class="pomp-card-wide mdl-card mdl-shadow--2dp">
		<div class="mdl-card__title">
			<h2 class="mdl-card__title-text">Impostazioni Valvola </h2>
		</div>
		<div class="mdl-card__supporting-text">
  		<table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp table-timer">
			<thead>
				<tr>
					<th>Indirizzo IP</th>
					<th>Nome</th>
					<th>Porta</th>
				</tr>
			</thead>
  			<tbody>
  				<tr>
  					<td>
						<div class="mdl-textfield mdl-js-textfield">
							<input type="hidden" value="<?php echo $configs['id'];?>" id="id">
							<input class="mdl-textfield__input mdl-ip" value="<?php echo $configs['ip'];?>" type="text" pattern="192.168.[0-9]{1,3}\.[0-9]{1,3}" placeholder="Indirizzo Ip" id="ip" required>
						</div>  						
  					</td>
  					<td>
  						<div class="mdl-textfield mdl-js-textfield mdl-nome">
							<input class="mdl-textfield__input" type="text" value="<?php echo $configs['nome'];?>" placeholder="Nome" id="nome" required>
						</div>
  					</td>
  					<td>
  						<div class="mdl-textfield mdl-js-textfield mdl-porta">
							<input class="mdl-textfield__input" type="number" value="<?php echo $configs['porta'];?>" placeholder="Porta" id="porta" required>
						</div>
  					</td>
  				</tr>
  			</tbody>
		</table>
			<br>
			<button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent button-conf" disabled>Salva</button>
		</div>
	</div>
</div>
<script>
	$(".mdl-card__supporting-text").bind("keyup click", function(){	
		if(!$(".mdl-textfield").hasClass("is-invalid")){
			$(".button-conf").prop("disabled",false);
		} else {
			$(".button-conf").prop("disabled",true);
		}
	});
	$(".button-conf").click(function(){
		$.ajax({
			url: "<?php echo URL_BASE; ?>includes/ajax.php",
			type: "POST",
			data: "id="+$("#id").val()+"&ip="+$("#ip").val()+"&porta="+$("#porta").val()+"&nome="+$("#nome").val(),
			success: function(result){
				if(result=="yes"){
					location.reload();
				} 
			}
		});
	});
</script>