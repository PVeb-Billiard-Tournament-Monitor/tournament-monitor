<?php
	$pageTitle = "Tournament settings";
	include_once "../html/header.php";
?>

<br>

<div style="width: 90%; margin: auto;">
	<form>
		<select multiple="multiple" size="10" name="duallistbox_demo1[]">
		<?php
			require_once '../db/connecting.php';

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
	</form>
</div>

<script type="text/javascript">
	var demo1 = $('select[name="duallistbox_demo1[]"]').bootstrapDualListbox();
</script>

<br>

<?php include_once "../html/footer.php" ?>
