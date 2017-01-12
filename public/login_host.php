<?php
    session_start();
    include_once "../db/connecting.php";

    sleep(1);


    if (isset($_GET["logout"])) {

        session_destroy();
        $_SESSION = [];
        header("Location: /tournament-monitor/public/home.php");

    } else if (isset($_POST["username"]) && isset($_POST["password"])) {

		$query = $db->prepare("SELECT username, password, id FROM billiard_club WHERE username = ? AND password = ?");
        $query->bindParam(1, $_POST["username"]);
        $query->bindParam(2, $_POST["password"]);
        $query->execute();

        if (!($row = $query->fetch(PDO::FETCH_ASSOC))) {
            echo "wrong username of password!";
        } else {
            $_SESSION["host_id"] = intval($row["id"]);
            $_SESSION["username"] = $_POST["username"];
            echo "success";
        }
        $_POST = [];

	} else if (isset($_SESSION["username"]) && isset($_SESSION["host_id"])) {

        if (!isset($_POST["host_data"])) {
            echo "fail";
        }

        $json = json_decode($_POST["host_data"]);

//        echo "name: $json->tournament_name<br>";
//        echo "key: $json->tournament_key<br>";
//        echo "type: $json->tournament_type<br>";
//        echo "fee: $json->entry_fee<br>";
//        foreach ($json->players as $p) {
//            echo "&nbsp;&nbsp;&nbsp;&nbsp;player_id: $p<br>";
//        }

        //$db->beginTransaction();

        $query = $db->prepare("SELECT NOW() as n");
        $query->execute();
        $date = $query->fetch(PDO::FETCH_ASSOC);

        $query = $db->prepare("INSERT INTO hosting_tournament (date, billiard_club_id, name, entry_fee, tournament_type, tournament_key, active) VALUES (:date, :id, :name, :fee, :type, :key, TRUE)");

        $query->bindParam(":date", $date["n"]);
        $query->bindParam(":id", $_SESSION["host_id"]);
        $query->bindParam(":name", $json->tournament_name);
        $query->bindParam(":fee", $json->entry_fee);
        $query->bindParam(":type", $json->tournament_type);
        $query->bindParam(":key", $json->tournament_key);
        $query->execute();


        foreach ($json->players as $player_id) {
            $query = $db->prepare("INSERT INTO playing_tournament (player_id, tournament_date, billiard_club_id, tournament_type, next_round, active) VALUES (:p_id, :date, :id, :type, DEFAULT, DEFAULT)");

            $query->bindParam(":date", $date["n"]);
            $query->bindParam(":id", $_SESSION["host_id"]);
            $query->bindParam(":type", $json->tournament_type);
            $query->bindParam(":p_id", $player_id);
            $query->execute();
        }

        //$db->commit();

        $_POST = [];
        echo "success";
    } else {
        echo "fail";
    }



?>
