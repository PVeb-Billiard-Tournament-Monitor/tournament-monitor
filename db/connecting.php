<?php

	$config = parse_ini_file("../private/config.ini");

	try
	{
		$db = new PDO('mysql:host=' . $config['servername'] . '; dbname=' . $config['dbname'] . '; charset=utf8;', $config['username'], $config['password']);
	}
	catch (Exception $error)
	{
		echo $error->getMessage();
	}

?>
