<br>
<div style="width: 90%; margin: auto;">
	<form>
		<span> Set tournament name: </span>
		<br>
		<input id="tournament_name" type="text" class="form-control" placeholder="Enter tournament name..." required="" autofocus="" style="width: 220px;">
		<br>
		<span> Set tournament key: </span>
		<br>
		<input id="tournament_key" type="text" class="form-control" placeholder="Enter tournament key..." required="" autofocus="" style="width: 220px;">
		<br>
		<span> Choose tournament type: </span>
		<br>


        <?php
            require_once '../db/connecting.php';

            $result = $db->query("SELECT * FROM tournament");

            $i = 0;
            while ($row = $result->fetch(PDO::FETCH_ASSOC))
            {
                if ($i == 0)
                    $active = 'checked="checked"';
                else
                    $active = "";
                $i++;

                $type = $row['type'];
                echo '<label class="radio-inline"><input '.$active.' type="radio" value="'.$type.'" name="type">'. $type. '</label>';
            }

        ?>

		<br>
		<br>
		<span> Set entry fee: </span>
		<br>
		<input id="entry_fee" type="text" class="form-control" placeholder="Set entry fee" autofocus="" style="width: 220px;" required="">
		<br>
		<span> Choose players: </span>
		<br>
		<select multiple="multiple" size="10" name="players">
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
	var demo1 = $('select[name="players"]').bootstrapDualListbox();
</script>
<br>
<div id="error_box">
</div>

