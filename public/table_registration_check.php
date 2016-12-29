<?php

	session_start();

	//-------------------------------------------------------------------------
	// initialize [or destroy] 'registered_tables' array
	//-------------------------------------------------------------------------
	if (empty($_SESSION['registered_tables']))
	{
		$_SESSION['registered_tables'] = array();
	}
	else if (isset($_REQUEST['restart']))	// for testing only [just add to the query &restart to destroy the current session]
	{
		$_SESSION['registered_tables'] = array();
		session_destroy();
		return;
	}

	//-------------------------------------------------------------------------
	// data from previous pages [for testing only]
	//-------------------------------------------------------------------------
	$_SESSION['billiard_club_id'] = 1;
	$_SESSION['tournament_key'] = 'proba123';

	//-------------------------------------------------------------------------
	// get query data
	//-------------------------------------------------------------------------
	$tournament_key = $_REQUEST["tournament_key"];
	$current_table_number = intval($_REQUEST["table_number"]);

	//-------------------------------------------------------------------------
	// fetching total number of tables
	//-------------------------------------------------------------------------
	require_once '../db/connecting.php';

	$query = $db->prepare("SELECT num_of_tables FROM billiard_club WHERE id = ?");
	$query->bindParam(1, $_SESSION['billiard_club_id']);
	$query->execute();

	while ($row = $query->fetch(PDO::FETCH_ASSOC))
	{
		$_SESSION['number_of_tables'] = $row['num_of_tables'];
	}

	//-------------------------------------------------------------------------
	// performing table registration check
	//-------------------------------------------------------------------------
	if ($_SESSION['tournament_key'] == $tournament_key)
	{
		if ($current_table_number > $_SESSION['number_of_tables'])
		{
			echo "Invalid table number. Try again!";
			return;
		}

		foreach ($_SESSION['registered_tables'] as $value)
		{
			if ($current_table_number == $value)
			{
				echo "This table is already registered. Try again!";
				return;
			}
		}

		$_SESSION['registered_tables'][count($_SESSION['registered_tables'])] = $current_table_number;

		echo "Successful registration!";
	}
	else
	{
		echo "Wrong tournament key. Try again!";
		return;
	}
?>
