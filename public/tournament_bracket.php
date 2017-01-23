
<br>
<div class="tree">
    <script type="text/javascript">

<?php
require_once "../db/connecting.php";
$data = json_decode($_POST['bracket_data']);

$query = $db->prepare(
    "SELECT * FROM `match` ".
    "WHERE billiard_club_id = :id ".
        "AND tournament_type = :type ".
        "AND tournament_date = :date ".
        "AND round = :rnd"
);
$round = 0;
$tmp = $round + 1;
$query->bindParam(":id", $data->id);
$query->bindParam(":type", $data->type);
$query->bindParam(":date", $data->date);
$query->bindParam(":rnd", $tmp);
$query->execute();

$brackets = new stdClass();
$brackets->date = $data->date;
$brackets->type = $data->type;
$brackets->id = $data->id;
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
    $brackets->n_of_players += 2;
}

$brackets->n_of_rounds = 0;
$brackets->n_of_rounds = log($brackets->n_of_players, 2);

// Round 2 to x
for ($round = 1; $round < $brackets->n_of_rounds; $round++) {
    $tmp = $round + 1;
    $query->bindParam(":id", $data->id);
    $query->bindParam(":type", $data->type);
    $query->bindParam(":date", $data->date);
    $query->bindParam(":rnd", $tmp);
    $query->execute();

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

    for ($i = 0; $match = $query->fetch(PDO::FETCH_ASSOC); $i++) {
        $p1_id = intval($match['player_id_1']);
        $p1_score = intval($match['score_1']);
        $p2_id = intval($match['player_id_2']);
        $p2_score = intval($match['score_2']);

        $key1 = false; $key2 = false;

        for($pair = 0; $pair < count($brackets->id_map[0][$round-1]); $pair++) {
            if ($key1 === false) {
                if (array_search($p1_id, $brackets->id_map[0][$round-1][$pair]) !== false)
                    $key1 = $pair;
            }
            if ($key2 === false) {
                if (array_search($p2_id, $brackets->id_map[0][$round-1][$pair]) !== false)
                    $key2 = $pair;
            }
        }


        $index = min($key1, $key2);
        $index = floor($index / log(count($brackets->id_map[0][$round-1]), 2));
        $brackets->results[0][$round][$index] = array($p1_score, $p2_score);
        $brackets->id_map[0][$round][$index] = array($p1_score, $p2_score);
    }
}


?>

        var tournamentData = <?php echo json_encode($brackets); ?>;


        $(document).ready(function() {
            console.log(JSON.stringify(tournamentData, null, '\t'));

            $('.tree').bracket({
                init: tournamentData,
                skipConsolationRound: true,
                teamWidth: 200,
                centerConnectors: true,
            });
            window.setTimeout(function() {
                $.ajax({
                    url: "/tournament-monitor/public/tournament_bracket.php",
                    method: "POST",
                    data: {
                        "bracket_data": JSON.stringify(tournamentData)
                    },
                    success: function(response) {
                        $("div.container").html(response);
                    },
                    error: function(response) {
                        $("div.container").html(response.responseText);
                    }
                });
            }, 2000);
        });
    </script>
</div>
<br>

