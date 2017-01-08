<?php
	$pageTitle = "Billiard Tournament Monitor";
	$bodyCSS = ["/tournament-monitor/css/home_body.css"];
	include_once "../html/header.php";
?>

<div id="myCarousel" class="carousel slide" data-ride="carousel">
	<!-- Indicators -->
	<ol class="carousel-indicators">
		<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
		<li data-target="#myCarousel" data-slide-to="1"></li>
	</ol>

	<!-- Wrapper for slides -->
	<div class="carousel-inner" role="listbox">
		<div class="item active">
			<img src="https://placehold.it/1200x400?text=IMAGE" alt="Image">
			<div class="carousel-caption">
				<h3>Sell $</h3>
				<p>Money Money.</p>
			</div>
		</div>

		<div class="item">
			<img src="https://placehold.it/1200x400?text=Another Image Maybe" alt="Image">
			<div class="carousel-caption">
				<h3>More Sell $</h3>
				<p>Lorem ipsum...</p>
			</div>
		</div>
	</div>

	<!-- Left and right controls -->
	<a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
		<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
		<span class="sr-only">Previous</span>
	</a>
	<a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
		<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
		<span class="sr-only">Next</span>
	</a>
</div>

<div class="container text-center">
  <br>
  <div class="row">
    <div class="col-sm-4">
	  <button type="button" class="btn btn-primary btn-lg" style="width:100%; height:150px;" >Live scores</button>
    </div>
    <div class="col-sm-4">
	  <a href="host.php"><button type="button" class="btn btn-primary btn-lg" style="width:100%; height:150px;" >Host a tournament</button></a>
    </div>
    <div class="col-sm-4">
	  <a href="table.php"><button type="button" class="btn btn-primary btn-lg" style="width:100%; height:150px;" >Table registration</button></a>
    </div>
  </div>
</div>

<?php include_once "../html/footer.php" ?>
