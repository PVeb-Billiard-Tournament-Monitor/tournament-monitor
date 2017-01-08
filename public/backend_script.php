<?php
	//testing
	if (isset($_GET['restart'])) {
		require_once '../db/connecting.php';
		$query = $db->prepare("DELETE FROM currently_registered_tables");
		$query->execute();

		$query = $db->prepare("DELETE FROM `match`");
		$query->execute();


		header("Location: /tournament-monitor/public/table.php");
		return;
	}
	// end testing



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
			$received_tournament_key = $json_data->tournament_key;

			require_once '../db/connecting.php';

			// get total number of tables
			$query = $db->prepare("SELECT bc.num_of_tables FROM billiard_club bc JOIN hosting_tournament ht ON bc.id = ht.billiard_club_id WHERE ht.tournament_key = :rtk AND ht.active = true");
			$query->bindParam(':rtk', $received_tournament_key);
			$query->execute();

			$total_number_of_tables = 0;
			while ($row = $query->fetch(PDO::FETCH_ASSOC))
			{
				$total_number_of_tables = $row['num_of_tables'];
			}

			// get total number of currently registered tables
			$query = $db->prepare("SELECT COUNT(table_number) AS number_of_currently_registered_tables FROM currently_registered_tables WHERE tournament_key = :tk");
			$query->bindParam(':tk', $received_tournament_key);
			$query->execute();

			$row = $query->fetch(PDO::FETCH_ASSOC);
			if ($row['number_of_currently_registered_tables'] == $total_number_of_tables)
			{
				echo "yes";
			}
			else
			{
				echo "no";
			}

			break;
		}
		// --------------------------------------------------------------------
		//	Match ready check
		// --------------------------------------------------------------------
		case 'is_match_ready':
		{
			$received_tournament_key = $json_data->tournament_key;
			$received_table_number = intval($json_data->table_number);

			require_once '../db/connecting.php';

			// get required data from HOSTING_TOURNAMENT
			$query = $db->prepare("SELECT billiard_club_id, tournament_type, date FROM hosting_tournament WHERE tournament_key = :rtk AND active = true");
			$query->bindParam(':rtk', $received_tournament_key);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_ASSOC);
			$billiard_club_id = $row['billiard_club_id'];
			$tournament_type = $row['tournament_type'];
			$date = $row['date'];

			// get already assigned number of players from MATCH to calculate next pair for a first round
			$query = $db->prepare("SELECT COUNT(*) AS number_of_already_assigned_players FROM `match` WHERE tournament_date = :td AND billiard_club_id = :bci AND tournament_type = :tt AND active = true");
			$query->bindParam(':td', $date);
			$query->bindParam(':bci', $billiard_club_id);
			$query->bindParam(':tt', $tournament_type);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_ASSOC);
			$next_pair = 2 * intval($row['number_of_already_assigned_players']);

			// check is there a pair for a round
			$query = $db->prepare("SELECT player_id FROM playing_tournament WHERE tournament_date = :td AND billiard_club_id = :bci AND tournament_type = :tt ORDER BY next_round ASC");
			$query->bindParam(':td', $date);
			$query->bindParam(':bci', $billiard_club_id);
			$query->bindParam(':tt', $tournament_type);
			$query->execute();
			$players = array();
			while ($row = $query->fetch(PDO::FETCH_ASSOC))
			{
				array_push($players, $row['player_id']);
			}

			// there is a pair for a round
			if (count($players) > 2)
			{
				$query = $db->prepare("INSERT INTO `match`(player_id_1, player_id_2, round, score_1, score_2, active, table_id, tournament_date, billiard_club_id, tournament_type) VALUES(:pi1, :pi2, :r, :s1, :s2, :a, :ti, :td, :bci, :tt)");
				$query->bindParam(':pi1', $players[0]);
				$query->bindParam(':pi2', $players[1]);
				$query->bindValue(':r', 1);
				$query->bindValue(':s1', 0);
				$query->bindValue(':s2', 0);
				$query->bindValue(':a', true);
				$query->bindParam(':ti', $received_table_number);
				$query->bindParam(':td', $date);
				$query->bindParam(':bci', $billiard_club_id);
				$query->bindParam(':tt', $tournament_type);
				$query->execute();

				// player class
				$response = new stdClass();
				$response->message = "yes";
				$response->player1 = new stdClass();
				$response->player2 = new stdClass();

				// get the first player
				$query = $db->prepare("SELECT name, last_name, img_link FROM player WHERE id = :i");
				$query->bindParam(':i', $players[$next_pair]);
				$query->execute();
				$row = $query->fetch(PDO::FETCH_ASSOC);
				$response->player1->id = $players[$next_pair];
				$response->player1->name = $row['name'];
				$response->player1->last_name = $row['last_name'];
				$response->player1->image_link = $row['img_link'];

				// get the second player
				$query = $db->prepare("SELECT name, last_name, img_link FROM player WHERE id = :i");
				$query->bindParam(':i', $players[$next_pair + 1]);
				$query->execute();
				$row = $query->fetch(PDO::FETCH_ASSOC);
				$response->player2->id = $players[$next_pair + 1];
				$response->player2->name = $row['name'];
				$response->player2->last_name = $row['last_name'];
				$response->player2->image_link = $row['img_link'];

				echo json_encode($response);
			}

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
			// Delete a looser from the PLAYING_TOURNAMENT table and update next_round to the next_round+1 for a winner

			break;
		}
		default:
		{
			echo '[ERROR] # Bad request';
			break;
		}
	}

?>
