<?php
	$pageTitle = "Tournament result";
	include_once "../html/header.php";
?>

<br>

<div class="tree">

	<script type="text/javascript">

		var tournamentData = {
		  	"teams":
		  	[
		    	["Ivan Ivanovic", "Marko Markovic"],
		    	["Jovan Jovanovic", "Alekandar Aleksandrovic"],
		    	["Nikola Nikolic", "Milos Milosevic"],
		    	["Stefan Stefanovic", "Dejan Dejanovic"]
		  	],
		  	"results":
		  	[
		    	[
		    		// first match
		      		[
		        		[22, 11],
		        		[10, 4],
		        		[41, 5],
		        		[52, 6]
		        	],
		        	// second match
		        	[
		        		[11, 24],
		        		[42, 55]
		        	],
		        	// third match
		        	[
		        		[22, 53],
		        	]
		    	]
		  	]
		}

		$('.tree').bracket({
			init: tournamentData,
			skipConsolationRound: true,
			teamWidth: 200,
			centerConnectors: true
		});


	</script>

</div>

<br>

<?php include_once "../html/footer.php" ?>
