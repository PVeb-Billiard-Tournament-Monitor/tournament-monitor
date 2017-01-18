
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
                    "AND round = 1"
            );
            $query->bindParam(":id", $data->id);
            $query->bindParam(":type", $data->type);
            $query->bindParam(":date", $data->date);
            $query->execute();

            $brackets = new stdClass();
            $brackets->ids = array();
            $i = 0;
            $results=[];
            $players = 0;

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $get_names = $db->prepare(
                    "SELECT CONCAT(name, ' ', last_name) as name ".
                    "FROM player ".
                    "WHERE id = :id"
                );
                if (is_null($row['player_id_1'])) {
                    $p1 = 'Bye';
                } else {
                    $get_names->bindParam(":id", $row['player_id_1']);
                    $get_names->execute();
                    $p1 = $get_names->fetch(PDO::FETCH_ASSOC)['name'];
                }

                if (is_null($row['player_id_2'])) {
                    $p2 = 'Bye';
                } else {
                    $get_names->bindParam(":id", $row['player_id_2']);
                    $get_names->execute();
                    $p2 = $get_names->fetch(PDO::FETCH_ASSOC)['name'];
                }


                $brackets->teams[$i] = array($p1, $p2);
                
                $results[0][0][$i] = array(intval($row['score_1']),
                                             intval($row['score_2']));

                $results_ind[0][$i] = array($row['player_id_1'], $row['player_id_2']);

                $i++;
                $players += 2;
            }

            $query = $db->prepare(
                "SELECT * FROM `match` ".
                "WHERE billiard_club_id = :id ".
                    "AND tournament_type = :type ".
                    "AND tournament_date = :date ".
                    "AND round = :rnd"
            );

            $n_rounds = log($players, 2);
            for ($round = 1; $round < $n_rounds; $round++) {
                $query->bindParam(":id", $data->id);
                $query->bindParam(":type", $data->type);
                $query->bindParam(":date", $data->date);
                $tmp = $round + 1;
                $query->bindParam(":rnd", $tmp);
                $query->execute();

                $k = 0;
                while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                    $key1 = false; $key2 = false;
                    for ($i = 0; $i < count($results_ind[$round - 1]); $i++) {
                        if ($key1 === false) {
                            if(array_search($row['player_id_1'], $results_ind[$round - 1][$i]) !== false)
                                $key1 = intval($i < ($players / 4) ? 0 : 1);
                        }
                        if ($key2 === false) {
                            if(array_search($row['player_id_2'], $results_ind[$round - 1][$i]) !== false)
                                $key2 = intval($i < ($players / 4) ? 0 : 1);
                        }
                    }

                    if ($key1 === false) {
                        $brackets->error_message="we have a problem";
                    }
                        
                    if ($key2 === false) {
                        $brackets->error_message="we have a problem";
                    }

                    //array_push($brackets->ids, "key1: $key1, key2 = $key2: inserting at results[0][$round][".min($key1, $key2)."] <- ".$row['score_1']. ", ".$row['score_2']);

                    $index = ($key1 < $key2 ? intval($key1) : intval($key2));
                    $results[0][$round][$index] =
                        array(intval($row['score_1']), intval($row['score_2']));

                    $results_ind[$round][$k] =
                        array($row['player_id_1'], $row['player_id_2']);

                    $k++;
                }
                if (isset($results[0][$round]))
                    ksort($results[0][$round]);
            }

            $brackets->results = $results;
            $brackets->all_ids = $results_ind;
            //$brackets->results = [
            //    [
            //        [
            //            [1, 0],
            //            [2, 3]
            //        ],
            //        [
            //            [4, 2]
            //        ]
            //    ]
            //];
        ?>

        var tournamentData = <?php echo json_encode($brackets); ?>;


        $(document).ready(function() {
            console.log(JSON.stringify(tournamentData));

            $('.tree').bracket({
                init: tournamentData,
                skipConsolationRound: true,
                teamWidth: 200,
                centerConnectors: true,
            });
        });
    </script>
</div>
<br>

