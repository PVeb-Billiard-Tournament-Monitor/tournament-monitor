
<?php
    $pageTitle = "Billiard Tournament Monitor";
    $bodyCSS = "../css/table_reg_body.css";
    include_once "../html/header.php";
?>

<br>
<script type="text/javascript">
    function error_handler(msg) {
        $("#error_box").addClass("alert alert-danger").html(msg instanceof XMLHttpRequest ? msg.responseText : msg);
    }
    function set_waiting() {
        $("input[name='tournament_key']").prop("disabled", true);
        $("input[name='table_number']").prop("disabled", true);
        $("#refresh").addClass("glyphicon glyphicon-refresh glyphicon-refresh-animate");
        $("#registration_button_text").text("Waiting...");
        $("#registration_button").prop("disabled", true);
    }
    $(document).ready(function () {
        $("#registration_button").click(function () {
            var t_key =$("input[name='tournament_key']");
            var t_number = $("input[name='table_number']");

            if (!t_key.val() || !t_number.val()) {
                return;
            }

            $.ajax({
                url: "table_registration_check.php",
                method: "GET",
                data: {
                    tournament_key: $("input[name='tournament_key']").val(),
                    table_number: $("input[name='table_number']").val()
                },
                success: function (response) {
                    if (response == "success") {
                        set_waiting();
                    } else {
                        error_handler(response);
                    }
                },
                error: error_handler
            });
        });
    });

</script>

<div class="container" style="margin: auto; width: 350px;">
    <form class="form-signin">
        <h2 class="form-signin-heading text-muted" style="text-align: center;">Table Registration</h2>
        <br>
        <input name="tournament_key" type="text" class="form-control" placeholder="Enter tournament key..." required="" autofocus="">
        <br>
        <input name="table_number" type="text" class="form-control" placeholder="Enter table key..." required="">
        <br>
        <button id="registration_button" class="btn btn-md btn-success btn-block" type="button" style="margin: auto; width: 200px;">
            <span id="refresh"></span>
            <span id="registration_button_text">&nbsp; Register</span>
        </button>
        <br>
        <div id="error_box">
        </div>
    </form>
</div>
<br>

<?php include_once "../html/footer.php" ?>
