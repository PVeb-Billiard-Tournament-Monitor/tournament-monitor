<?php
	$pageTitle = "Host a tournament";
    $bodyCSS = ["/tournament-monitor/css/animate_glyph.css"];
	include_once "../html/header.php";
?>

<br>
<script type="text/javascript">
	function login_user() {
		$("#refresh").addClass("glyphicon glyphicon-refresh glyphicon-refresh-animate");
		$("#login_button_text").html("logging...");
	}

	function error_handler(msg) {
		$("#error_box").addClass("alert alert-danger").html(msg);
		$("#refresh").removeClass("glyphicon glyphicon-refresh glyphicon-refresh-animate");
		$("#login_button_text").html("Log in");
	}

	$(document).ready(function() {
		$("#login_button").click(function() {
			var username = $("input[type='text']").val();
			var password = $("input[type='password']").val();
			if(!username || !password) {
				error_handler("Both fields are required!");
				return;
			}

			login_user();

			$.ajax({
				url: "/tournament-monitor/public/login_host.php",
				data: {
					username: username,
					password: password
				},
				async: true,
				method: "GET",
				success: function(msg) {
					if (msg == "success") {
						location.reload();
					} else {
						error_handler(msg);
					}
				},
				error: function(response) {
					error_handler(response.responseText);
				}
			});
		});
	});

</script>

<?php
	if (isset($_SESSION["username"])):
		include_once "player_selection.php";
	else:
?>

<div class="container" style="margin: auto; width: 350px;">
	<form class="form-signin">
		<h2 class="form-signin-heading text-muted" style="text-align: center;">Host a tournament</h2>
		<br>
		<input type="text" class="form-control" placeholder="Enter username..." required="" autofocus="">
		<br>
		<input type="password" class="form-control" placeholder="Enter password..." required="">
		<br>
		<button id="login_button" class="btn btn-md btn-success btn-block" type="button" style="margin: auto; width: 200px;">
			<span id="refresh"></span>
			&nbsp;
			<span id="login_button_text"> Log in </span>
		</button>
		<br>
		<div id="error_box">

		</div>
	</form>
</div>
<br>

<?php include_once "../html/footer.php" ?>
<?php endif; ?>
