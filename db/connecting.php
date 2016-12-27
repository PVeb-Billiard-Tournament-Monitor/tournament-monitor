<?php

	$username = "root";
	$password = "abcdef";

	try
	{
		$db = new PDO('mysql:host=localhost; dbname=billiard_db; charset=utf8;', $username, $password);
	}
	catch (Exception $error)
	{
		echo $error->getMessage();
	}

?>
