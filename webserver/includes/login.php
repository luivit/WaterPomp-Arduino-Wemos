<div class="mdl-grid pomp-content">
	<div class="pomp-card-wide mdl-card mdl-shadow--2dp">
		<div class="mdl-card__title">
			<h2 class="mdl-card__title-text">Login</h2>
		</div><form>
		<div class="mdl-card__supporting-text">
		  <div class="mdl-textfield mdl-js-textfield">
			<input class="mdl-textfield__input" type="text" placeholder="Username" id="username" required>
		  </div>
		  <div class="mdl-textfield mdl-js-textfield">
			<input class="mdl-textfield__input" type="password" placeholder="Password" id="password" required>
		  </div><br>
		  <input type="submit" value="Login" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">
		</div></form>
	</div>
</div>
<script>
$('.mdl-button').click(function(){
				   $.ajax({
						url: "<?php echo URL_BASE; ?>includes/ajax.php",
						type: "POST",
						data: "user="+$("#username").val()+"&pass="+$("#password").val(),
						success: function(result){
							if(result=="yes"){
								location.reload();
							} else {
								alert("Dati Errati!")
							}
						}
					});
				   });
</script>