<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="WaterPomp Manager">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <title>WaterPomp Manager</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.cyan-light_blue.min.css">
    <link rel="stylesheet" href="style/styles.css">
    <link rel="icon" type="image/png" href="style/icon.png" />
    <meta name="theme-color" content="#37474F" />
    <style>
    #view-source {
      position: fixed;
      display: block;
      right: 0;
      bottom: 0;
      margin-right: 40px;
      margin-bottom: 40px;
      z-index: 900;
    }
    </style>
    <script src="style/jquery-3.2.1.min.js"></script>
  </head>
  <body><?php include("includes/function.php"); include("config.php"); include("includes/class/sql.php");?>
    <div class="pomp-layout mdl-layout mdl-js-layout mdl-layout--fixed-drawer mdl-layout--fixed-header">
      <header class="pomp-header mdl-layout__header mdl-color--grey-100 mdl-color-text--grey-600">
        <div class="mdl-layout__header-row">
          <span class="mdl-layout-title">WaterPomp Manager</span>
          <div class="mdl-layout-spacer"></div>
        </div>
      </header>
      <div class="pomp-drawer mdl-layout__drawer mdl-color--blue-grey-900 mdl-color-text--blue-grey-50">
        <?php if (isLogged()){
		  	$ms=new Sql($vldb);
			$ms->setIdCookie();
		  ?><header class="pomp-drawer-header">
          <i class="mdl-color-text--blue-grey-400 material-icons pomp-avatar">verified_user</i>
          <div class="pomp-avatar-dropdown">
            <span>Ciao <?php echo $_COOKIE['Valvola']; ?></span>
            <div class="mdl-layout-spacer"></div>
            <button id="accbtn" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
              <i class="material-icons" role="presentation">arrow_drop_down</i>
              <span class="visuallyhidden">Accounts</span>
            </button>
            <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="accbtn">
              <li class="mdl-menu__item logout">Logout</li>
            </ul>
          </div>
        </header>
        <script>
			$(".logout").click(function(){
				$.ajax({
						url: "<?php echo URL_BASE; ?>includes/ajax.php",
						type: "POST",
						data: "logout=1",
						success: function(result){
							if(result == "yes"){
								location.reload();
							}
						}
					});
			});
		</script>
        <nav class="pomp-navigation mdl-navigation mdl-color--blue-grey-800">
         <a class="mdl-navigation__link" href="index.php"><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">home</i>Home</a>
          <a class="mdl-navigation__link" href="index.php?manual=1"><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">repeat_one</i>Controllo Manuale</a>
          <a class="mdl-navigation__link" href="index.php?timer=1"><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">repeat</i>Impostazione timer</a>
          <a class="mdl-navigation__link" href="index.php?settings=1"><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">settings_remote</i>Impostazione Connessione Valvola</a>
          <a class="mdl-navigation__link" href="index.php?ntp=1"><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">av_timer</i>Impostazione NTP Server</a>
        </nav><?php }?>
      </div>
      <main class="mdl-layout__content mdl-color--grey-100">
       <?php if (isLogged()){
			if(isset($_GET['manual'])){
				include("includes/manual.php");
			} elseif(isset($_GET['timer'])){
				include("includes/timer.php");
			} elseif(isset($_GET['settings'])){
				include("includes/settings.php");
			}elseif(isset($_GET['ntp'])){
				include("includes/ntp.php");
			} else {
       			include("includes/home.php");
			}
       	} else { 
			include("includes/login.php");
		} ?>
      </main>
    </div>
    <script src="https://code.getmdl.io/1.3.0/material.min.js"></script>
  </body>
</html>
