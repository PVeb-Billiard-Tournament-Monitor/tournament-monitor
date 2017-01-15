
<br>

<div class="tree">
    <script type="text/javascript">

        <?php
            require_once '../db/connecting.php';

            function next_pow($number) {
                if($number < 2)
                    return 1;
                for($i = 0; $number > 1; $i++) {
                    $number = $number >> 1;
                }
                return 1 << ($i + 1);
            }
            $json = json_decode($_POST['bracket_data']);

            $club_id = $json->id;
            $type = $json->type;
            $date = $json->date;

            $query = $db->prepare(  
                "SELECT COUNT(*) as max " .
                "FROM playing_tournament " .
                "WHERE billiard_club_id = :id " .
                    "AND tournament_type = :type " .
                    "AND tournament_date = :date"
            );
            $query->bindParam(":id", $club_id);
            $query->bindParam(":type", $type);
            $query->bindParam(":date", $date);
            $query->execute();
            $max_players = $query->fetch(PDO::FETCH_ASSOC)['max'];

            $max_players = next_pow($max_players);

        ?>
        var x = <?php echo $max_players; ?>;
        console.log(x);

        //tournamentData = JSON.stringify(tournamentData);

        //$(document).ready(function() {
        //    console.log(JSON.stringify(tournamentData));

        //    $('.tree').bracket({
        //        init: tournamentData,
        //        skipConsolationRound: true,
        //        teamWidth: 200,
        //        centerConnectors: true,
        //    });
        //});
    </script>
</div>
<br>

