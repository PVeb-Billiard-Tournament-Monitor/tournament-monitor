<!DOCTYPE html>
<?php session_start(); ?>
<html lang="en">

<head>
	<title><?php echo $pageTitle; ?></title>

	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

	<link rel="stylesheet" type="text/css" href="../css/header.css">

	<?php
		foreach ($css_includes as $css_include)
			echo "<link rel='stylesheet' type='text/css' href='$css_include'>";

		foreach ($js_includes as $js_include)
			echo "<script src='$js_include'></script>";
	?>
</head>

<body>
	<nav class="navbar navbar-inverse">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="home.php">Logo</a>
			</div>

			<div style="position: relative;" class="collapse navbar-collapse" id="myNavbar">
				<ul class="nav navbar-nav">
					<li class="active"><a href="home.php">Home</a></li>
					<!-- TESTING -->
					<li><a href="backend_script.php?restart">Delete currently logged tables in db [testing]</a></li>
					<!-- END TESTING -->
				</ul>
			</div>
		</div>
	</nav>
