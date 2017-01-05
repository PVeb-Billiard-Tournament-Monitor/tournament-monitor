<?php

	$json_data = json_decode($_POST['table_data']);

	$table_message = $json_data->message;

	switch ($table_message)
	{
		//-----------------------------------------------------------------
		// Table registration
		//-----------------------------------------------------------------
		case 'register_me':
		{
			$received_tournament_key = $json_data->tournament_key;
			$received_table_number = intval($json_data->table_number);

			require_once '../db/connecting.php';

			$query = $db->query("SELECT tournament_key FROM hosting_tournament ht WHERE active = true");
			$tournament_key_passed = false;
			while ($row = $query->fetch(PDO::FETCH_ASSOC))
			{
				if ($row['tournament_key'] == $received_tournament_key)
				{
					$tournament_key_passed = true;
					break;
				}
			}

			if ($tournament_key_passed == true)
			{
				$query = $db->prepare("SELECT bc.num_of_tables FROM billiard_club bc JOIN hosting_tournament ht ON bc.id = ht.billiard_club_id WHERE ht.tournament_key = :rtk AND ht.active = true");
				$query->bindParam(':rtk', $received_tournament_key);
				$query->execute();

				$number_of_tables = 0;
				while ($row = $query->fetch(PDO::FETCH_ASSOC))
				{
					$number_of_tables = $row['num_of_tables'];
				}

				if ($received_table_number > $number_of_tables)
				{
					echo "Invalid table number. Try again!";
					return;
				}

				$query = $db->query("SELECT table_number FROM currently_registered_tables");
				while ($row = $query->fetch(PDO::FETCH_ASSOC))
				{
					if ($received_table_number == $row['table_number'])
					{
						echo "Table already registered. Try again!";
						return;
					}
				}

				$query = $db->prepare("INSERT INTO currently_registered_tables(tournament_key, table_number) VALUES(:tk, :tn)");
				$query->bindParam(':tk', $received_tournament_key);
				$query->bindParam(':tn', $received_table_number);
				$query->execute();

				echo "success";
			}
			else
			{
				echo "Wrong tournament key. Try again!";
				return;
			}

			break;
		}
		// --------------------------------------------------------------------
		//	Tournament ready check
		// --------------------------------------------------------------------
		case 'is_tournament_ready':
		{
			break;
		}
		// --------------------------------------------------------------------
		//	Result changed
		// --------------------------------------------------------------------
		case 'result_changed':
		{
			break;
		}
		// --------------------------------------------------------------------
		//	Match finished
		// --------------------------------------------------------------------
		case 'match_finished':
		{
			break;
		}
		default:
		{
			echo '[ERROR] # Bad request';
			break;
		}
	}

?>
