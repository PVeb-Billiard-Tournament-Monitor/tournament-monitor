<?php
	//testing
	if (isset($_GET['restart'])) {
		require_once '../db/connecting.php';
		$query = $db->prepare("DELETE FROM currently_registered_tables");
		$query->execute();

		$query = $db->prepare("DELETE FROM `match`");
		$query->execute();

		$query = $db->prepare("DELETE FROM playing_tournament");
		$query->execute();

		$query = $db->prepare("INSERT INTO `billiard_db`.`playing_tournament` (`player_id`, `tournament_date`, `billiard_club_id`, `tournament_type`, `next_round`, `active`) VALUES (1, 'NOW()', 1, 'Drzavni', DEFAULT, DEFAULT)");
		$query->execute();

		$query = $db->prepare("INSERT INTO `billiard_db`.`playing_tournament` (`player_id`, `tournament_date`, `billiard_club_id`, `tournament_type`, `next_round`, `active`) VALUES (2, 'NOW()', 1, 'Drzavni', DEFAULT, DEFAULT)");
		$query->execute();


		$query = $db->prepare("INSERT INTO `billiard_db`.`playing_tournament` (`player_id`, `tournament_date`, `billiard_club_id`, `tournament_type`, `next_round`, `active`) VALUES (3, 'NOW()', 1, 'Drzavni', DEFAULT, DEFAULT)");
		$query->execute();

		$query = $db->prepare("INSERT INTO `billiard_db`.`playing_tournament` (`player_id`, `tournament_date`, `billiard_club_id`, `tournament_type`, `next_round`, `active`) VALUES (4, 'NOW()', 1, 'Drzavni', DEFAULT, DEFAULT)");
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

			// Get the total number of tables.
			$query = $db->prepare("SELECT bc.num_of_tables FROM billiard_club bc JOIN hosting_tournament ht ON bc.id = ht.billiard_club_id WHERE ht.tournament_key = :rtk AND ht.active = true");
			$query->bindParam(':rtk', $received_tournament_key);
			$query->execute();

			$total_number_of_tables = 0;
			while ($row = $query->fetch(PDO::FETCH_ASSOC))
			{
				$total_number_of_tables = $row['num_of_tables'];
			}

			// Get the total number of the currently registered tables.
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

			// Get the required data from the HOSTING_TOURNAMENT table.
			$query = $db->prepare("SELECT billiard_club_id, tournament_type, date FROM hosting_tournament WHERE tournament_key = :rtk AND active = true");
			$query->bindParam(':rtk', $received_tournament_key);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_ASSOC);
			$billiard_club_id = $row['billiard_club_id'];
			$tournament_type = $row['tournament_type'];
			$date = $row['date'];

			// Check is there a pair for the round.
			$query = $db->prepare("SELECT player_id FROM playing_tournament WHERE tournament_date = :td AND billiard_club_id = :bci AND tournament_type = :tt AND active = false ORDER BY next_round ASC LIMIT 2");
			$query->bindParam(':td', $date);
			$query->bindParam(':bci', $billiard_club_id);
			$query->bindParam(':tt', $tournament_type);
			$query->execute();
			$players = array();
			while ($row = $query->fetch(PDO::FETCH_ASSOC))
			{
				array_push($players, $row['player_id']);
			}

			// There is a pair for the round.
			if (count($players) > 1)
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

				// Generate json response.
				$response = new stdClass();
				$response->message = "yes";
				$response->player1 = new stdClass();
				$response->player2 = new stdClass();

				// Get the first player data.
				$query = $db->prepare("SELECT name, last_name, img_link FROM player WHERE id = :i");
				$query->bindParam(':i', $players[0]);
				$query->execute();
				$row = $query->fetch(PDO::FETCH_ASSOC);
				$response->player1->id = $players[0];
				$response->player1->name = $row['name'];
				$response->player1->last_name = $row['last_name'];
				$response->player1->image_link = $row['img_link'];
				$response->player1->score = 0;

				// Get the second player data.
				$query = $db->prepare("SELECT name, last_name, img_link FROM player WHERE id = :i");
				$query->bindParam(':i', $players[1]);
				$query->execute();
				$row = $query->fetch(PDO::FETCH_ASSOC);
				$response->player2->id = $players[1];
				$response->player2->name = $row['name'];
				$response->player2->last_name = $row['last_name'];
				$response->player2->image_link = $row['img_link'];
				$response->player2->score = 0;

				echo json_encode($response);

				$query = $db->prepare("UPDATE playing_tournament SET active = true WHERE player_id = :p1");
				$query->bindParam(':p1', $players[0]);
				$query->execute();
				$query = $db->prepare("UPDATE playing_tournament SET active = true WHERE player_id = :p2");
				$query->bindParam(':p2', $players[1]);
				$query->execute();
			}
			else
			{
				$response = new stdClass();
				$response->message = "no";

				echo json_encode($response);
			}

			break;
		}
		// --------------------------------------------------------------------
		//	Score changed
		// --------------------------------------------------------------------
		case 'score_changed':
		{
            require_once '../db/connecting.php';

			$received_tournament_key = $json_data->tournament_key;
			$received_table_number = intval($json_data->table_number);
			$received_player_1_id = $json_data->player1->id;
			$received_player_2_id = $json_data->player2->id;
			$received_player_1_score = $json_data->player1->score;
			$received_player_2_score = $json_data->player2->score;


			// Get the required data from the HOSTING_TOURNAMENT table.
			$query = $db->prepare("SELECT billiard_club_id, tournament_type, date FROM hosting_tournament WHERE tournament_key = :rtk AND active = true");
			$query->bindParam(':rtk', $received_tournament_key);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_ASSOC);
			$billiard_club_id = $row['billiard_club_id'];
			$tournament_type = $row['tournament_type'];
			$date = $row['date'];

			// Update player score.
			$query = $db->prepare("UPDATE `match` SET score_1 = :s1, score_2 = :s2 WHERE player_id_1 = :pi1 AND player_id_2 = :pi2 AND tournament_date = :td AND billiard_club_id = :bci AND tournament_type = :tt AND table_id = :ti");
			$query->bindParam(':pi1', $received_player_1_id);
			$query->bindParam(':pi2', $received_player_2_id);
			$query->bindValue(':s1', $received_player_1_score);
			$query->bindValue(':s2', $received_player_2_score);
			$query->bindParam(':ti', $received_table_number);
			$query->bindParam(':td', $date);
			$query->bindParam(':bci', $billiard_club_id);
			$query->bindParam(':tt', $tournament_type);
			$query->execute();

			break;
		}
		// --------------------------------------------------------------------
		//	Match finished
		// --------------------------------------------------------------------
		case 'match_finished':
		{
			$received_tournament_key = $json_data->tournament_key;
			$received_table_number = intval($json_data->table_number);
			$received_player_1_id = $json_data->player1->id;
			$received_player_2_id = $json_data->player2->id;
			$received_player_1_score = $json_data->player1->score;
			$received_player_2_score = $json_data->player2->score;

			if (received_player_1_score > received_player_2_score)
			{
				$winner = received_player_1_id;
				$looser = received_player_2_id;
			}
			else
			{
				$winner = received_player_2_id;
				$looser = received_player_1_id;
			}

			// Get the required data from the HOSTING_TOURNAMENT table.
			$query = $db->prepare("SELECT billiard_club_id, tournament_type, date FROM hosting_tournament WHERE tournament_key = :rtk AND active = true");
			$query->bindParam(':rtk', $received_tournament_key);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_ASSOC);
			$billiard_club_id = $row['billiard_club_id'];
			$tournament_type = $row['tournament_type'];
			$date = $row['date'];

			// Delete a looser.
			$query = $db->prepare("DELETE FROM playing_tournament WHERE player_id = :pi AND tournament_date = :td AND tournament_type = :tt AND billiard_club_id = :bci");
			$query->bindParam(':pi', $looser);
			$query->bindParam(':td', $tournament_date);
			$query->bindParam(':tt', $tournament_type);
			$query->bindParam(':bci', $billiard_club_id);
			$query->execute();

			// Increase next_round and set active to false for a winner.
			$query = $db->prepare("UPDATE playing_tournament SET next_round = next_round + 1, active = false WHERE player_id = :pi AND tournament_date = :td AND tournament_type = :tt AND billiard_club_id = :bci");
			$query->bindParam(':pi', $winner);
			$query->bindParam(':td', $tournament_date);
			$query->bindParam(':tt', $tournament_type);
			$query->bindParam(':bci', $billiard_club_id);
			$query->execute();

			// Update record in the MATCH table.
			$query = $db->prepare("UPDATE `match` SET active = false WHERE player_id_1 = :pi1 AND player_id_2 = :pi2 AND tournament_date = :td AND billiard_club_id = :bci AND tournament_type = :tt");
			$query->bindParam(':pi1', $received_player_2_id);
			$query->bindParam(':pi2', $received_player_2_id);
			$query->bindParam(':ti', $received_table_number);
			$query->bindParam(':td', $date);
			$query->bindParam(':bci', $billiard_club_id);
			$query->bindParam(':tt', $tournament_type);
			$query->execute();

			break;
		}
		default:
		{
			echo '[ERROR] # Bad request';

			break;
		}
	}

?>
