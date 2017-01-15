
<br>

<div class="tree">
    <script type="text/javascript">

        <?php
            require_once "../db/connecting.php";

            $data = json_decode($_POST['bracket_data']);


            $query = $db->prepare(  
                "SELECT * FROM new_match " .
                "WHERE billiard_club_id = :id " .
                    "AND tournament_type = :type " .
                    "AND tournament_date = :date"
            );
            $query->bindParam(":id", $data->id);
            $query->bindParam(":type", $data->type);
            $query->bindParam(":date", $data->date);
            $query->execute();

            $brackets = new stdClass();
            $i = 0;
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $get_names = $db->prepare(
                    "SELECT CONCAT(name, ' ', last_name) as name ".
                    "FROM player ".
                    "WHERE id = :id"
                );
                $get_names->bindParam(":id", $row['id_player1']);
                $get_names->execute();
                $p1 = $get_names->fetch(PDO::FETCH_ASSOC)['name']; 
                $get_names->bindParam(":id", $row['id_player2']);
                $get_names->execute();
                $p2 = $get_names->fetch(PDO::FETCH_ASSOC)['name']; 
                $brackets->teams[$i] = array($p1, $p2); 
                $i++;
            }
            $brackets->results = [
                [
                    [
                        [1, 0],
                        [2, 3]
                    ],
                    [
                        [4, 2]
                    ]
                ] 
            ];
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

