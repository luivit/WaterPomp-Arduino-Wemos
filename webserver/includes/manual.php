<?php
$confArray=$ms->getConfig($_COOKIE['Valvola-id']);
?>
<div class="mdl-grid pomp-content-manual">
	<div class="pomp-card-wide mdl-card mdl-shadow--2dp">
		<div class="mdl-card__title">
			<h2 class="mdl-card__title-text">Controllo Manuale</h2>
		</div>
		<div class="mdl-card__supporting-text"><?php if($_COOKIE['Valvola-id']!=0){ ?>
  			<div class="msg_text">Stato valvola:<span class="text_red"></span><span class="text_green"></span><span class="text_orange"></span>
  			</div>
	  		<table class="mdl-data-table mdl-js-data-table">
			  <thead>
				<tr>
				  <th class="mdl-data-table__cell--non-numeric">Nome</th>
				  <th>IP</th>
				  <th>Porta</th>
				</tr>
			  </thead>
			  <tbody>
				<tr>
				  <td class="mdl-data-table__cell--non-numeric"><?php echo $confArray['nome'];?></td>
				  <td><?php echo $confArray['ip'];?></td>
				  <td><?php echo $confArray['porta'];?></td>
				</tr>
			  </tbody>
			</table>
			<div class="mdl-textfield mdl-js-textfield">
				<input class="mdl-textfield__input" type="number" min="1" max="60" placeholder="Minuti d'apertura (1-60)" id="durata">
			</div><br>
		  <button title="Apri" class="mdl-button mdl-js-button mdl-button--icon open" disabled><i class="material-icons">lock_open</i></button>
		  <button title="Chiudi" class="mdl-button mdl-js-button mdl-button--icon close" disabled><i class="material-icons">lock_outline</i></button>
		  <div id="p2" class="mdl-progress mdl-js-progress mdl-progress__indeterminate"></div>
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
			data: "stato=1&ip=<?php echo $confArray['ip'];?>",
			beforeSend: function() {
						$(".mdl-progress").css("display", "block");
						$(".text_red").text("");
						$(".text_orange").text("Connessione in corso con la valvola");
					},
			success: function(result){
				if(result!="errore"){
					if(result=="aperto"){
						$(".mdl-progress").css("display", "none");
						$(".text_orange").text("");
						$(".text_green").text(result);
						$(".close").prop("disabled",false);
					} else {
						$(".mdl-progress").css("display", "none");
						$(".text_orange").text("");
						$(".text_red").text(result);
						$(".open").prop("disabled",false);
					}
				} else {
					$(".mdl-progress").css("display", "none");
					$(".text_orange").text("");
					$(".text_red").text();
					$(".text_red").text("Impossibile connettersi alla valvola, controlla i dati!");
				}
					
			}
		});
	});
	$(".open").click(function(){
		if($("#durata").val()>0 && $("#durata").val()<61){
		$(".open").prop("disabled",true);
			$.ajax({
				url: "<?php echo URL_BASE; ?>includes/ajax.php",
				type: "POST",
				data: "open=1&ip=<?php echo $confArray['ip'];?>&durata="+$("#durata").val(),
				beforeSend: function() {
					$(".mdl-progress").css("display", "block");
					$(".text_red").text("");
					$(".text_orange").text("In apertura");
				},
				success: function(result){
					if(result=="yes"){
						$(".mdl-progress").css("display", "none");
						$(".text_red").text("");
						$(".text_orange").text("");
						$(".text_green").text("aperto");
						$(".close").prop("disabled",false);
						$(".open").prop("disabled",true);
					}
				}
			});
		}
	});
	$(".close").click(function(){
		$(".close").prop("disabled",true);
		$.ajax({
			url: "<?php echo URL_BASE; ?>includes/ajax.php",
			type: "POST",
			data: "close=1&ip=<?php echo $confArray['ip'];?>",
			beforeSend: function() {
				$(".mdl-progress").css("display", "block");
				$(".text_green").text("");
				$(".text_orange").text("In chiusura");
			},
			success: function(result){
				if(result=="yes"){
					$(".mdl-progress").css("display", "none");
					$(".text_green").text("");
					$(".text_orange").text("");
					$(".text_red").text("chiuso");
					$(".open").prop("disabled",false);
					$(".close").prop("disabled",true);
				} 
			}
		});
	});
	
</script>