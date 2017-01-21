<?php
	session_start();
	//-----------------------------------------------------------------
	// Only for the testing purpose.
	//-----------------------------------------------------------------
	if (isset($_GET['restart']))
	{
		session_destroy();
		require_once '../db/connecting.php';

		$cmd = "/opt/lampp/bin/mysql -u " . $config['username']. " -p" . $config['password'] .
			" < /opt/lampp/htdocs/tournament-monitor/db/create.sql";
		shell_exec($cmd);

		header("Location: /tournament-monitor/public/home.php");
		return;
	}


	$json_data = json_decode($_POST['table_data']);
	$table_message = $json_data->message;

	switch ($table_message)
	{
		//-----------------------------------------------------------------
		// Fill match table [init]
		//-----------------------------------------------------------------
		case 'fill_match_table':
		{
			// Get the forwarded data.
			$received_billiard_club_id = $json_data->billiard_club_id;
			$received_tournament_date = $json_data->tournament_date;
			$received_tournament_type = $json_data->tournament_type;

			// Connect to the database.
			require_once '../db/connecting.php';

			// Get the player list.
			$query = $db->prepare("SELECT player_id FROM playing_tournament WHERE tournament_date = :td AND billiard_club_id = :bci AND tournament_type = :tt AND active = false ORDER BY next_round ASC, player_id ASC");
			$query->bindParam(':td', $received_tournament_date);
			$query->bindParam(':bci', $received_billiard_club_id);
			$query->bindParam(':tt', $received_tournament_type);
			$query->execute();
			$player_list = array();
			while ($row = $query->fetch(PDO::FETCH_ASSOC))
			{
				array_push($player_list, $row['player_id']);
			}

			for ($i = 0; $i < (count($player_list) / 2); ++$i)
			{
				// Add phony pairs to the MATCH table.
				// Insert phony pairs to the MATCH table.
				$query = $db->prepare("INSERT INTO `match` (player_id_1, player_id_2, round, score_1, score_2, active, tournament_date, billiard_club_id, tournament_type, phony) VALUES(:pi1, :pi2, :r, :s1, :s2, :a, :td, :bci, :tt, true)");
				$query->bindParam(':pi1', $player_list[2 * $i]);
				$query->bindParam(':pi2', $player_list[2 * $i + 1]);
				$query->bindValue(':r', 1);
				$query->bindValue(':s1', 0);
				$query->bindValue(':s2', 0);
				$query->bindValue(':a', false);
				$query->bindParam(':td', $received_tournament_date);
				$query->bindParam(':bci', $received_billiard_club_id);
				$query->bindParam(':tt', $received_tournament_type);
				$query->execute();
			}



            $query = $db->prepare(
                "SELECT * FROM `match` ".
                "WHERE billiard_club_id = :id ".
                    "AND tournament_type = :type ".
                    "AND tournament_date = :date ".
                    "AND round = :rnd"
            );
            $round = 0;
            $tmp = $round + 1;
            $query->bindParam(":rnd", $tmp);
			$query->bindParam(':date', $received_tournament_date);
			$query->bindParam(':id', $received_billiard_club_id);
			$query->bindParam(':type', $received_tournament_type);
            $query->execute();

            $brackets = new stdClass();
            $brackets->n_of_players = 0;

            /* Round 1 */
            for ($i = 0; $match = $query->fetch(PDO::FETCH_ASSOC); $i++) {
                $get_names = $db->prepare(
                    "SELECT CONCAT(name, ' ', last_name) as name ".
                    "FROM player ".
                    "WHERE id = :id"
                );
                if (is_null($match['player_id_1'])) {
                    $p1_name = 'Bye';
                } else {
                    $get_names->bindParam(":id", $match['player_id_1']);
                    $get_names->execute();
                    $p1_name = $get_names->fetch(PDO::FETCH_ASSOC)['name'];
                }
                if (is_null($match['player_id_2'])) {
                    $p2_name = 'Bye';
                } else {
                    $get_names->bindParam(":id", $match['player_id_2']);
                    $get_names->execute();
                    $p2_name = $get_names->fetch(PDO::FETCH_ASSOC)['name'];
                }

                $brackets->teams[$i] = array($p1_name, $p2_name);
                $brackets->results[0][0][$i] =
                    array(intval($match['score_1']), intval($match['score_2']));
                $brackets->id_map[0][0][$i] =
                    array(
                        intval($match['player_id_1']),
                        intval($match['player_id_2'])
                    );
                $brackets->possible_pairs[0][$i] =
                    array(
                        intval($match['player_id_1']),
                        intval($match['player_id_2'])
                    );
                $brackets->n_of_players += 2;
            }

            $brackets->n_of_rounds = 0;
            $brackets->n_of_rounds = log($brackets->n_of_players, 2);

            for ($round = 1; $round < $brackets->n_of_rounds; $round++) {

                $brackets->results[0][$round] = array();
                $brackets->results[0][$round] = array_pad(
                    $brackets->results[0][$round],
                    count($brackets->results[0][$round - 1]) / 2,
                    array(null, null)
                );
                $brackets->id_map[0][$round] = array();
                $brackets->id_map[0][$round] = array_pad(
                    $brackets->id_map[0][$round],
                    count($brackets->id_map[0][$round - 1]) / 2,
                    array(null, null)
                );


                $brackets->possible_pairs[$round] = array();
                $brackets->possible_pairs[$round] = array_pad(
                    $brackets->possible_pairs[$round],
                    count($brackets->possible_pairs[$round - 1]) / 2,
                    array()
                );

                $tmp = log(count($brackets->possible_pairs[$round-1]), 2);
                for ($i = 0; $i < count($brackets->possible_pairs[$round-1]); $i++) {
                    if ($tmp == 1) {
                        $index = 0;
                    } else {
                        $index = floor($i / $tmp);
                    }
                    if (!isset( $brackets->possible_pairs[$round][$index] ))
                        $brackets->possible_pairs[$round][$index] = array();
                    foreach ($brackets->possible_pairs[$round-1][$i] as $id) {
                        array_push($brackets->possible_pairs[$round][$index], $id);
                    }
                }

            }

            $brackets->message = "success";
			$schema = json_encode($brackets);

            $query = $db->prepare(
                "UPDATE hosting_tournament ".
                "SET schema_JSON = :schema ".
                "WHERE tournament_type = :type ".
                "AND date = :date ".
                "AND billiard_club_id = :id"
            );
			$query->bindParam(':date', $received_tournament_date);
			$query->bindParam(':id', $received_billiard_club_id);
			$query->bindParam(':type', $received_tournament_type);
			$query->bindParam(':schema', $schema);
            $query->execute();

            echo $schema;

			break;
		}
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

				$query = $db->prepare("SELECT table_number FROM currently_registered_tables WHERE tournament_key = :rtk");
				$query->bindParam(':rtk', $received_tournament_key);
				$query->execute();

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

			// Lock this action.
			$lock_status = $db->query("SELECT GET_LOCK('my_lock', 0)")->fetchColumn();
			if ($lock_status != "1")
			{
				$response = new stdClass();
				$response->message = "no";

				echo json_encode($response);
				return;
			}

			// Get the required data from the HOSTING_TOURNAMENT table.
			$query = $db->prepare("SELECT billiard_club_id, tournament_type, date FROM hosting_tournament WHERE tournament_key = :rtk AND active = true");
			$query->bindParam(':rtk', $received_tournament_key);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_ASSOC);
			$billiard_club_id = $row['billiard_club_id'];
			$tournament_type = $row['tournament_type'];
			$tournament_date = $row['date'];

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

			// Get json file with orderings.
			$query = $db->prepare("SELECT schema_JSON FROM hosting_tournament WHERE date = :td AND billiard_club_id = :bci AND tournament_type = :tt");
			$query->bindParam(':td', $tournament_date);
			$query->bindParam(':bci', $billiard_club_id);
			$query->bindParam(':tt', $tournament_type);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_ASSOC);
			$json_orderings = json_decode($row['schema_JSON']);

			// Get all inactive players from playing_tournament table.
			$query = $db->prepare("SELECT player_id, next_round FROM playing_tournament WHERE tournament_date = :td AND billiard_club_id = :bci AND tournament_type = :tt AND active = false ORDER BY next_round ASC, player_id ASC");
			$query->bindParam(':td', $tournament_date);
			$query->bindParam(':bci', $billiard_club_id);
			$query->bindParam(':tt', $tournament_type);
			$query->execute();
			$players = array();
			while ($row = $query->fetch(PDO::FETCH_ASSOC))
			{
				$player_data = new stdClass();
				$player_data->player_id = $row['player_id'];
				$player_data->next_round = $row['next_round'];
				array_push($players, $player_data);
			}

			for ($i = 0; $i < count($players); $i++)
			{
				$first_player_round = $players[$i]->next_round;
				$first_player_id = $players[$i]->player_id;

				for ($j = 0; $j < count($players); $j++)
				{
					if ($i == $j)
						continue;

					$second_player_id = $players[$j]->player_id;
					$second_player_round = $players[$j]->next_round;

					// da li cekaju na istu rundu
					if (intval($first_player_round) == intval($second_player_round))
					{
						// dobavi niz breketa za rundu na koju ceka prvi igrac
						$bracket_array = $json_orderings->possible_pairs[$first_player_round - 1];
						// prodji kroz svaki unutrasnji niz i proveri da li su igraci zajedno u nekom od njih
						foreach ($bracket_array as $bracket)
						{
							if ((in_array($first_player_id, $bracket) == true) && (in_array($second_player_id, $bracket) == true))
							{
								// ========== bracket data <3 ==========
								if (intval($first_player_round) == 1)
								{
									// Update phony column of the MATCH table. That record is the real pair now.
									$query = $db->prepare("UPDATE `match` SET table_id = :ti, phony = false, active = true WHERE player_id_1 = :pi1 AND player_id_2 = :pi2 AND tournament_date = :td AND billiard_club_id = :bci AND tournament_type = :tt");
									$query->bindParam(':pi1', $first_player_id);
									$query->bindParam(':pi2', $second_player_id);
									$query->bindParam(':td', $tournament_date);
									$query->bindParam(':bci', $billiard_club_id);
									$query->bindParam(':tt', $tournament_type);
									$query->bindParam(':ti', $received_table_number);
									$query->execute();
								}
								// ========== end of bracket data <3 ==========
								else
								{
									// Insert the real pair to the MATCH table.
									$query = $db->prepare("INSERT INTO `match`(player_id_1, player_id_2, round, score_1, score_2, active, table_id, tournament_date, billiard_club_id, tournament_type, phony) VALUES(:pi1, :pi2, :r, :s1, :s2, :a, :ti, :td, :bci, :tt, false)");
									$query->bindParam(':pi1', $first_player_id);
									$query->bindParam(':pi2', $second_player_id);
									$query->bindParam(':r', $first_player_round);
									$query->bindValue(':s1', 0);
									$query->bindValue(':s2', 0);
									$query->bindValue(':a', true);
									$query->bindParam(':ti', $received_table_number);
									$query->bindParam(':td', $tournament_date);
									$query->bindParam(':bci', $billiard_club_id);
									$query->bindParam(':tt', $tournament_type);
									$query->execute();
								}

								// Generate the JSON response.
								$response = new stdClass();
								$response->message = "yes";
								$response->player1 = new stdClass();
								$response->player2 = new stdClass();

								// Get the first player data.
								$query = $db->prepare("SELECT name, last_name, img_link FROM player WHERE id = :i");
								$query->bindParam(':i', $first_player_id);
								$query->execute();
								$row = $query->fetch(PDO::FETCH_ASSOC);
								$response->player1->id = $first_player_id;
								$response->player1->name = $row['name'];
								$response->player1->last_name = $row['last_name'];
								$response->player1->image_link = $row['img_link'];
								$response->player1->score = 0;

								// Get the second player data.
								$query = $db->prepare("SELECT name, last_name, img_link FROM player WHERE id = :i");
								$query->bindParam(':i', $second_player_id);
								$query->execute();
								$row = $query->fetch(PDO::FETCH_ASSOC);
								$response->player2->id = $second_player_id;
								$response->player2->name = $row['name'];
								$response->player2->last_name = $row['last_name'];
								$response->player2->image_link = $row['img_link'];
								$response->player2->score = 0;

								echo json_encode($response);

								// The active column indicates that player playing a match.
								$query = $db->prepare("UPDATE playing_tournament SET active = true WHERE player_id = :p1");
								$query->bindParam(':p1', $first_player_id);
								$query->execute();
								$query = $db->prepare("UPDATE playing_tournament SET active = true WHERE player_id = :p2");
								$query->bindParam(':p2', $second_player_id);
								$query->execute();

								// Unlock this action.
								$db->query("RELEASE_LOCK ('my_lock')");

								return;
							}
						}
					}
					else
					{
						continue;
					}
				}
			}

			// ili trenutno nema para, ili je turnir zavrsen
			// Tournament finished.
			if (count($all_players) == 1)
			{
				$response = new stdClass();
				$response->message = "tournament_finished";

				echo json_encode($response);
			}
			// The player is waiting for an opponent.
			else
			{
				$response = new stdClass();
				$response->message = "no";

				echo json_encode($response);
			}

			// Unlock this action.
			$db->query("RELEASE_LOCK ('my_lock')");

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
			$query = $db->prepare("UPDATE `match` SET active = false WHERE player_id_1 = :pi1 AND player_id_2 = :pi2 AND tournament_date = :td AND billiard_club_id = :bci AND tournament_type = :tt AND table_id = :ti");
			$query->bindParam(':pi1', $received_player_1_id);
			$query->bindParam(':pi2', $received_player_2_id);
			$query->bindParam(':ti', $received_table_number);
			$query->bindParam(':td', $tournament_date);
			$query->bindParam(':bci', $billiard_club_id);
			$query->bindParam(':tt', $tournament_type);
			$query->execute();

			echo "wait_for_next_match";

			break;
		}
		// --------------------------------------------------------------------
		//	Bad request
		// --------------------------------------------------------------------
		default:
		{
			$response = new stdClass();
			$response->message = 'Bad request';
			echo json_encode($response);

			break;
		}
	}

?>
