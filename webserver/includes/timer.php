<?php
$configs=$ms->getTimer($_COOKIE['Valvola-id']);
?>
<div class="mdl-grid pomp-content">
	<div class="pomp-card-wide mdl-card mdl-shadow--2dp">
		<div class="mdl-card__title">
			<h2 class="mdl-card__title-text">Impostazioni Timer <?php echo $configs['nome'];?></h2>
		</div>
		<div class="mdl-card__supporting-text"><?php if($_COOKIE['Valvola-id']!=0){ ?>
			<span class="text_red"></span>
		<table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp table-timer">
			<thead>
				<tr>
					<th>Ricorrenza</th>
					<th>HH</th>
					<th>MM</th>
					<th>Durata</th>
					<th>On/Off</th>
				</tr>
			</thead>
			<tbody>
			<tr>
					<td>
						<select id="ricorrenza">
							<option value="0"<?php if($configs['frequenza']==0) echo 'selected'; ?>>Una sola volta</option>
							<option value="12"<?php if($configs['frequenza']==12) echo 'selected'; ?>>Ogni 12 ore</option>
							<option value="24"<?php if($configs['frequenza']==24) echo 'selected'; ?>>Ogni giorno</option>
							<option value="48"<?php if($configs['frequenza']==48) echo 'selected'; ?>>Ogni 48 ore</option>
							<option value="72"<?php if($configs['frequenza']==72) echo 'selected'; ?>>Ogni 72 ore</option>
							<option value="168"<?php if($configs['frequenza']==168) echo 'selected'; ?>>Ogni settimana</option>
						</select>
					</td>
					<td>
						<div class="mdl-textfield mdl-js-textfield">
							<input class="mdl-textfield__input mdl-hh" required value="<?php echo $configs['ora'];?>" type="number" min="0" max="23" placeholder="HH" id="ora">
						</div>
					</td>
					<td>
						<div class="mdl-textfield mdl-js-textfield">
							<input class="mdl-textfield__input mdl-mm" required value="<?php echo $configs['minuti'];?>" type="number" min="0" max="59" placeholder="MM" id="minuti">
						</div>
					</td>
					<td>
						<div class="mdl-textfield mdl-js-textfield">
							<input class="mdl-textfield__input mdl-durata" required value="<?php echo $configs['durata'];?>" type="number" min="0" max="60" maxlength="2" placeholder="Durata apertura in minuti" id="durata">
						</div>
					</td>
					<td>
						<label for="active" class="mdl-switch mdl-js-switch mdl-js-ripple-effect">
						  <input type="checkbox" id="active" value="0" class="mdl-switch__input" <?php if($configs['active']) echo 'checked'; ?>>
						</label>
					</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" id="id_timer" value="<?php echo $configs['id'];?>">
		<input type="hidden" id="id_conf" value="<?php echo $_COOKIE['Valvola-id'];?>">
  		<button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent button-timer" disabled>Salva</button>
  		<div id="p2" class="mdl-progress mdl-js-progress mdl-progress__indeterminate"></div>
	  	<?php } else {
			echo "Devi impostare almeno una valvola";
		} ?>
		</div>
	</div>
</div>
<script>
	$(".mdl-card__supporting-text").bind("keyup click", function(){	
		if(!$(".mdl-textfield").hasClass("is-invalid")){
			if(($(".mdl-hh").val()>=0 && $(".mdl-hh").val()<24) && ($(".mdl-mm").val()>=0 && $(".mdl-mm").val()<60) && ($(".mdl-durata").val()>0 && $(".mdl-durata").val()<=60)){
				$(".button-timer").prop("disabled",false);
			} else {
				$(".button-timer").prop("disabled",true);
			}
		} else {
			$(".button-timer").prop("disabled",true);
		}
	});
	$(".button-timer").click(function(){
		var onff="0";
		if($("#active").prop('checked')){
			onff="1";
		}
		var textAjax="id="+$("#id_timer").val()+"&id_conf="+$("#id_conf").val()+"&ric="+$("#ricorrenza").val()+"&hh="+$("#ora").val()+"&mm="+$("#minuti").val()+"&durata="+$("#durata").val()+"&active="+onff;
		$.ajax({
			url: "<?php echo URL_BASE; ?>includes/ajax.php",
			type: "POST",
			data: textAjax,
			beforeSend: function() {
						$(".mdl-progress").css("display", "block");
					},
			success: function(result){
				if(result=="yes"){
					location.reload();
				} else {
					$(".text_red").text("Errore, impossibile connettersi alla valvola!");
					$(".mdl-progress").css("display", "none");
				}
			}
		});
	});
</script>