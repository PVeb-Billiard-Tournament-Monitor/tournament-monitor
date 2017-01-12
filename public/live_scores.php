<?php
	$pageTitle = "Live scores";
	$bodyCSS = ["/tournament-monitor/css/home_body.css"];
	include_once "../html/header.php";
?>

<br>
<br>
<div class="container" style="margin: auto; width: 80%;">
	<table class="table table-hover table-bordered">
		<thead>
	      	<tr>
	        	<th>Tournament name</th>
	        	<th>Billiard club</th>
	        	<th>Tournament type</th>
	        	<th>Tournament date</th>
	      	</tr>
    	</thead>
    	<tbody>
		<?php
			// Connect to the database.
			require_once '../db/connecting.php';

			echo '<tr>';
			// Get all active tournaments.
			$query = $db->query("SELECT * FROM hosting_tournament WHERE active = true");
			while ($row = $query->fetch(PDO::FETCH_ASSOC))
			{
				// Set tournament name.
				echo '<td>' . $row['name'] . '</td>';

				// Set billiard club name.
				$query_temp = $db->prepare("SELECT name FROM billiard_club WHERE id = :id");
				$query_temp->bindParam(':id', $row['billiard_club_id']);
				$query_temp->execute();
				$row_temp = $query_temp->fetch(PDO::FETCH_ASSOC);
				echo '<td>' . $row_temp['name'] . '</td>';

				// Set tournament type name.
				echo '<td>' . $row['tournament_type'] . '</td>';

				// Set tournament date.
				echo '<td>' . $row['date'] . '</td>';
			}

			echo '</tr>';
		?>
		</tbody>
	</table>
</div>
</br>

<?php include_once "../html/footer.php" ?>
