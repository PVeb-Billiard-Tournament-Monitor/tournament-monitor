
<?php
	$pageTitle = "Billiard Tournament Monitor";
	$bodyCSS = "../css/table_reg_body.css";
	include_once "../html/header.php";
?>

<br>
<script type="text/javascript" src="/tournament-monitor/js/table.js"></script>

<script>
    $(document).ready(function() {
        table.init();
        $("div.container").on("click", "#registration_button", function() {table.update();});
    });
</script>

<div class="container" style="margin: auto; width: 350px;">
</div>
<br>

<?php include_once "../html/footer.php" ?>
