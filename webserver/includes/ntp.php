<?php
$confArray=$ms->getConfig($_COOKIE['Valvola-id']);
?>
<div class="mdl-grid pomp-content-manual">
	<div class="pomp-card-wide mdl-card mdl-shadow--2dp">
		<div class="mdl-card__title">
			<h2 class="mdl-card__title-text">Impostazione Server NTP</h2>
		</div>
		<div class="mdl-card__supporting-text"><?php if($_COOKIE['Valvola-id']!=0){ ?>
  			<div class="msg_text"><span class="text_red"><span class="text_red"></span><span class="text_green"></span>
  			</div>
			<table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp table-ntp">
				<thead>
					<tr>
						<th style="text-align: center" colspan="2">Ore Offset UTC</th>
					</tr>
				</thead>
				<tbody>
				<tr>
					<td style="text-align: center">
					<div class="mdl-textfield mdl-js-textfield"><input class="mdl-textfield__input mdl-offset" required type="number" placeholder="Offset" id="ntp" min="0" max="23"></div></td>
					<td style="text-align: center"><button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent button-ntp" disabled>Salva</button></td>
				</tr>
				</tbody>
			</table>
		  <br><br>
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
			data: "ntp=1&ip=<?php echo $confArray['ip'];?>",
			beforeSend: function() {
						$(".mdl-progress").css("display", "block");
					},
			success: function(result){
				if(result!="errore"){
					$("#ntp").val(result);
					$(".mdl-progress").css("display", "none");
				} else {
					$(".mdl-progress").css("display", "none");
					$(".text_orange").text("");
					$(".text_red").text();
					$(".text_red").text("Impossibile connettersi alla valvola!");
				}
					
			}
		});
	});
	$(".button-ntp").click(function(){
		$.ajax({
			url: "<?php echo URL_BASE; ?>includes/ajax.php",
			type: "POST",
			data: "offset="+$("#ntp").val()+"&ip=<?php echo $confArray['ip'];?>",
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
	$(".mdl-card__supporting-text").bind("keyup click", function(){	
		if(!$(".mdl-textfield").hasClass("is-invalid")){
			$(".button-ntp").prop("disabled",false);
		} else {
			$(".button-ntp").prop("disabled",true);
		}
	});
	
</script>