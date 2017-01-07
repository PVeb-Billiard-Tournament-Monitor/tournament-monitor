<?php
	$pageTitle = "Tournament settings";
	include_once "../html/header.php";
?>

<br>

<div style="width: 90%; margin: auto;">
	<form>
		<span> Set tournament key: </span>
		<br>
		<input type="text" class="form-control" placeholder="Enter tournament key..." required="" autofocus="" style="width: 220px;">
		<br>
		<span> Choose tournament type: </span>
		<br>
		<select class="selectpicker">
  		<?php
		 	require_once '../db/connecting.php';

			$result = $db->query("SELECT * FROM tournament");

			while ($row = $result->fetch(PDO::FETCH_ASSOC))
			{
				$type = $row['type'];
				echo "<option value=" . $type . ">" . $type . "</option>".PHP_EOL;
			}

		?>
		</select>
		<br><br>
		<span> Set entry fee: </span>
		<br>
		<input type="text" class="form-control" placeholder="Set entry fee" autofocus="" style="width: 220px;">
		<br>
		<span> Choose players: </span>
		<br>
		<select multiple="multiple" size="10" name="duallistbox_demo1[]">
		<?php
			$result = $db->query("SELECT * FROM player");

			while ($row = $result->fetch(PDO::FETCH_ASSOC))
			{
				$player_id = $row['id'];
				$player_name = $row['name'];
				$player_last_name = $row['last_name'];

				echo "<option value=" . $player_id . ">" . $player_name . " " . $player_last_name . "</option>".PHP_EOL;
			}
		?>
		</select>
		<br><br>
		<button id="start_tournament" class="btn btn-md btn-success btn-block" type="button" style="margin: auto; width: 200px;">
			<span id="login_button_text"> Start tournament </span>
		</button>
	</form>
</div>

<script type="text/javascript">
	var demo1 = $('select[name="duallistbox_demo1[]"]').bootstrapDualListbox();
</script>

<br>

<?php include_once "../html/footer.php" ?>
