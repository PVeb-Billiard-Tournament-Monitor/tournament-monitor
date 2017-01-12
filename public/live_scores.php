<?php
	$pageTitle = "Live scores";
	$css_includes = ["/tournament-monitor/css/home_body.css", "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css", "https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css"];
	$js_includes = ["//code.jquery.com/jquery-1.12.4.js", "https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js", "https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js"];
	include_once "../html/header.php";
?>

<br>
<br>
<div class="container" style="margin: auto; width: 80%;">
	<table id="tournament_table" class="table table-hover table-bordered">
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

			// Get all active tournaments.
			$query = $db->query("SELECT * FROM hosting_tournament WHERE active = true");
			while ($row = $query->fetch(PDO::FETCH_ASSOC))
			{
				echo '<tr class="clickable_row" style="cursor: pointer;">';

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

				echo '</tr>';
			}
		?>
		</tbody>
	</table>
</div>
</br>

<script type="text/javascript">

	$(document).ready(function(){
		$('#tournament_table').DataTable();

		$('.clickable_row').click(function(){
			window.location = "tournament_bracket.php";
		});
	});

</script>

<?php include_once "../html/footer.php" ?>
