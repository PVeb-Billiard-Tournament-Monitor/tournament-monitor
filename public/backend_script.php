<?php

	//-----------------------------------------------------------------
	// Only for the testing purpose.
	//-----------------------------------------------------------------
	if (isset($_GET['restart']))
	{
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


	$json_data = json_decode($_POST['table_data']);
	$table_message = $json_data->message;

	switch ($table_message)
	{
		//-----------------------------------------------------------------
		// Table registration
		//-----------------------------------------------------------------
		case 'register_me':
		{
			// Get the forwarded data.
			$received_tournament_key = $json_data->tournament_key;
			$received_table_number = intval($json_data->table_number);

			// Connect to the database.
			require_once '../db/connecting.php';

			// Check the tournament key.
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

			// Tournament key is OK.
			if ($tournament_key_passed == true)
			{
				// Get the total number of tables for the current billiard club.
				$query = $db->prepare("SELECT bc.num_of_tables FROM billiard_club bc JOIN hosting_tournament ht ON bc.id = ht.billiard_club_id WHERE ht.tournament_key = :rtk AND ht.active = true");
				$query->bindParam(':rtk', $received_tournament_key);
				$query->execute();
				$row = $query->fetch(PDO::FETCH_ASSOC);
				$number_of_tables = $row['num_of_tables'];

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
			// Get the forwarded data.
			$received_tournament_key = $json_data->tournament_key;

			// Connect to the database.
			require_once '../db/connecting.php';

			// Get the total number of tables for the current billiard club.
			$query = $db->prepare("SELECT bc.num_of_tables FROM billiard_club bc JOIN hosting_tournament ht ON bc.id = ht.billiard_club_id WHERE ht.tournament_key = :rtk AND ht.active = true");
			$query->bindParam(':rtk', $received_tournament_key);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_ASSOC);
			$total_number_of_tables = $row['num_of_tables'];

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
			// Get the forwarded data.
			$received_tournament_key = $json_data->tournament_key;
			$received_table_number = intval($json_data->table_number);

			// Connect to the database.
			require_once '../db/connecting.php';

			// Get the required data from the HOSTING_TOURNAMENT table.
			$query = $db->prepare("SELECT billiard_club_id, tournament_type, date FROM hosting_tournament WHERE tournament_key = :rtk AND active = true");
			$query->bindParam(':rtk', $received_tournament_key);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_ASSOC);
			$billiard_club_id = $row['billiard_club_id'];
			$tournament_type = $row['tournament_type'];
			$tournament_date = $row['date'];

			// Check lock.
			$query = $db->prepare("SELECT lock FROM hosting_tournament WHERE billiard_club_id = :bci AND tournament_type = :tt AND date = :td");
			$query->bindParam(':bci', $billiard_club_id);
			$query->bindParam(':tt', $tournament_type);
			$query->bindParam(':td', $tournament_date);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_ASSOC);
			if ($row['lock'] == true)
			{
				$response = new stdClass();
				$response->message = "no";

				echo json_encode($response);
				return;
			}

			// Lock this action.
			$query = $db->prepare("UPDATE hosting_tournament SET lock = true WHERE billiard_club_id = :bci AND tournament_type = :tt AND date = :td");
			$query->bindParam(':bci', $billiard_club_id);
			$query->bindParam(':tt', $tournament_type);
			$query->bindParam(':td', $tournament_date);
			$query->execute();

			// Get all records from the PLAYING_TOURNAMENT table.
			$query = $db->prepare("SELECT player_id FROM playing_tournament WHERE tournament_date = :td AND billiard_club_id = :bci AND tournament_type = :tt");
			$query->bindParam(':td', $tournament_date);
			$query->bindParam(':bci', $billiard_club_id);
			$query->bindParam(':tt', $tournament_type);
			$query->execute();
			$all_players = array();
			while ($row = $query->fetch(PDO::FETCH_ASSOC))
			{
				array_push($all_players, $row['player_id']);
			}

			// Get the next-round players.
			$query = $db->prepare("SELECT player_id, next_round FROM playing_tournament WHERE tournament_date = :td AND billiard_club_id = :bci AND tournament_type = :tt AND active = false ORDER BY next_round ASC LIMIT 2");
			$query->bindParam(':td', $tournament_date);
			$query->bindParam(':bci', $billiard_club_id);
			$query->bindParam(':tt', $tournament_type);
			$query->execute();
			$next_round_players = array();
			while ($row = $query->fetch(PDO::FETCH_ASSOC))
			{
				$player_data = new stdClass();
				$player_data->player_id = $row['player_id'];
				$player_data->next_round = $row['next_round'];
				array_push($next_round_players, $player_data);
			}

			// There is a pair for the round.
			if (count($next_round_players) == 2)
			{
				// Two players can play only if they waiting for the same round.
				if ($next_round_players[0]->next_round == $next_round_players[1]->next_round)
				{
					$query = $db->prepare("INSERT INTO `match`(player_id_1, player_id_2, round, score_1, score_2, active, table_id, tournament_date, billiard_club_id, tournament_type) VALUES(:pi1, :pi2, :r, :s1, :s2, :a, :ti, :td, :bci, :tt)");
					$query->bindParam(':pi1', $next_round_players[0]->player_id);
					$query->bindParam(':pi2', $next_round_players[1]->player_id);
					$query->bindValue(':r', 1);
					$query->bindValue(':s1', 0);
					$query->bindValue(':s2', 0);
					$query->bindValue(':a', true);
					$query->bindParam(':ti', $received_table_number);
					$query->bindParam(':td', $tournament_date);
					$query->bindParam(':bci', $billiard_club_id);
					$query->bindParam(':tt', $tournament_type);
					$query->execute();

					// Generate the JSON response.
					$response = new stdClass();
					$response->message = "yes";
					$response->player1 = new stdClass();
					$response->player2 = new stdClass();

					// Get the first player data.
					$query = $db->prepare("SELECT name, last_name, img_link FROM player WHERE id = :i");
					$query->bindParam(':i', $next_round_players[0]->player_id);
					$query->execute();
					$row = $query->fetch(PDO::FETCH_ASSOC);
					$response->player1->id = $next_round_players[0]->player_id;
					$response->player1->name = $row['name'];
					$response->player1->last_name = $row['last_name'];
					$response->player1->image_link = $row['img_link'];
					$response->player1->score = 0;

					// Get the second player data.
					$query = $db->prepare("SELECT name, last_name, img_link FROM player WHERE id = :i");
					$query->bindParam(':i', $next_round_players[1]->player_id);
					$query->execute();
					$row = $query->fetch(PDO::FETCH_ASSOC);
					$response->player2->id = $next_round_players[1]->player_id;
					$response->player2->name = $row['name'];
					$response->player2->last_name = $row['last_name'];
					$response->player2->image_link = $row['img_link'];
					$response->player2->score = 0;

					echo json_encode($response);

					// The active column indicates that player playing a match.
					$query = $db->prepare("UPDATE playing_tournament SET active = true WHERE player_id = :p1");
					$query->bindParam(':p1', $next_round_players[0]->player_id);
					$query->execute();
					$query = $db->prepare("UPDATE playing_tournament SET active = true WHERE player_id = :p2");
					$query->bindParam(':p2', $next_round_players[1]->player_id);
					$query->execute();
				}
				else
				{
					$response = new stdClass();
					$response->message = "no";

					echo json_encode($response);
				}
			}
			// There is no pair for the next round.
			else
			{
				// Tournament finished.
				if (count($all_players) == 1)
				{
					echo "tournament_finished";
				}
				// The player waiting for a opponent.
				else
				{
					$response = new stdClass();
					$response->message = "no";

					echo json_encode($response);
				}
			}

			// Unlock this action.
			$query = $db->prepare("UPDATE hosting_tournament SET lock = false WHERE billiard_club_id = :bci AND tournament_type = :tt AND date = :td");
			$query->bindParam(':bci', $billiard_club_id);
			$query->bindParam(':tt', $tournament_type);
			$query->bindParam(':td', $tournament_date);
			$query->execute();

			break;
		}
		// --------------------------------------------------------------------
		//	Score changed
		// --------------------------------------------------------------------
		case 'score_changed':
		{
			// Connect to the database.
			require_once '../db/connecting.php';

			// Get the forwarded data.
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
			$tournament_date = $row['date'];

			// Update a player score.
			$query = $db->prepare("UPDATE `match` SET score_1 = :s1, score_2 = :s2 WHERE player_id_1 = :pi1 AND player_id_2 = :pi2 AND tournament_date = :td AND billiard_club_id = :bci AND tournament_type = :tt AND table_id = :ti");
			$query->bindParam(':pi1', $received_player_1_id);
			$query->bindParam(':pi2', $received_player_2_id);
			$query->bindValue(':s1', $received_player_1_score);
			$query->bindValue(':s2', $received_player_2_score);
			$query->bindParam(':ti', $received_table_number);
			$query->bindParam(':td', $tournament_date);
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
			// Connect to the database.
			require_once "../db/connecting.php";

			// Get the forwarded data.
			$received_tournament_key = $json_data->tournament_key;
			$received_table_number = intval($json_data->table_number);
			$received_player_1_id = $json_data->player1->id;
			$received_player_2_id = $json_data->player2->id;
			$received_player_1_score = $json_data->player1->score;
			$received_player_2_score = $json_data->player2->score;

			// Determine who is who.
			if ($received_player_1_score > $received_player_2_score)
			{
				$winner = $received_player_1_id;
				$looser = $received_player_2_id;
			}
			else
			{
				$winner = $received_player_2_id;
				$looser = $received_player_1_id;
			}

			// Get the required data from the HOSTING_TOURNAMENT table.
			$query = $db->prepare("SELECT billiard_club_id, tournament_type, date FROM hosting_tournament WHERE tournament_key = :rtk AND active = true");
			$query->bindParam(':rtk', $received_tournament_key);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_ASSOC);
			$billiard_club_id = $row['billiard_club_id'];
			$tournament_type = $row['tournament_type'];
			$tournament_date = $row['date'];

			// Get the current round for the MATCH table.
			$query = $db->prepare("SELECT next_round FROM playing_tournament WHERE player_id = :pi AND tournament_date = :td AND billiard_club_id = :bci AND tournament_type = :tt");
			$query->bindParam(':pi', $looser);
			$query->bindParam(':td', $tournament_date);
			$query->bindParam(':bci', $billiard_club_id);
			$query->bindParam(':tt', $tournament_type);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_ASSOC);
			$round = $row['next_round'];

			// Delete a looser from the PLAYING_TOURNAMENT table.
			$query = $db->prepare("DELETE FROM playing_tournament WHERE player_id = :pi AND tournament_date = :td AND tournament_type = :tt AND billiard_club_id = :bci");
			$query->bindParam(':pi', $looser);
			$query->bindParam(':td', $tournament_date);
			$query->bindParam(':tt', $tournament_type);
			$query->bindParam(':bci', $billiard_club_id);
			$query->execute();

			// Increment next_round column and set active column to false for a winner.
			$query = $db->prepare("UPDATE playing_tournament SET next_round = next_round + 1, active = false WHERE player_id = :pi AND tournament_date = :td AND tournament_type = :tt AND billiard_club_id = :bci");
			$query->bindParam(':pi', $winner);
			$query->bindParam(':td', $tournament_date);
			$query->bindParam(':tt', $tournament_type);
			$query->bindParam(':bci', $billiard_club_id);
			$query->execute();

			// Update record in the MATCH table.
			$query = $db->prepare("UPDATE `match` SET active = false, round = :r WHERE player_id_1 = :pi1 AND player_id_2 = :pi2 AND tournament_date = :td AND billiard_club_id = :bci AND tournament_type = :tt AND table_id = :ti");
			$query->bindParam(':pi1', $received_player_1_id);
			$query->bindParam(':pi2', $received_player_2_id);
			$query->bindParam(':ti', $received_table_number);
			$query->bindParam(':td', $tournament_date);
			$query->bindParam(':bci', $billiard_club_id);
			$query->bindParam(':tt', $tournament_type);
			$query->bindParam(':r', $round);
			$query->execute();

			echo "wait_for_next_match";

			break;
		}
		default:
		{
			echo 'Bad request';

			break;
		}
	}

?>
