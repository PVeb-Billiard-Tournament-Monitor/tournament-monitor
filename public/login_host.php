<?php
    session_start();
    include_once "../db/connecting.php";

    sleep(1);

    if (isset($_GET["restart"])) {
        session_destroy();
        header("Location: home.php");
    }

	if (isset($_GET["username"]) && isset($_GET["password"])) {
		$query = $db->prepare("SELECT username, password FROM billiard_club WHERE username = ? AND password = ?");
        $query->bindParam(1, $_GET["username"]);
        $query->bindParam(2, $_GET["password"]);
        $query->execute();

        if (!($row = $query->fetch(PDO::FETCH_ASSOC))) {
            echo "wrong username of password!";
        } else {
            $_SESSION["host_id"] = intval($row["id"]);
            $_SESSION["username"] = $_GET["username"];
            echo "success";
        }
	} else {
		echo "something went wrong.";
	}
?>
