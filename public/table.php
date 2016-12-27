
<?php 
    $pageTitle = "Billiard Tournament Monitor";
    $bodyCSS = "../css/table_reg_body.css";
    include_once "../html/header.php";
?>

<br>
<script type="text/javascript">

    function loadDoc() {
        var xhttp = new XMLHttpRequest();
        xhttp.open("GET", "ajax_info.txt", false);
        xhttp.send();
        document.getElementById("demo").innerHTML = xhttp.responseText;
    }

</script>

<div class="container" style="margin: auto; width: 350px;">
    <form class="form-signin">
        <h2 class="form-signin-heading text-muted" style="text-align: center;">Table Registration</h2>
        <br>
        <input type="text" class="form-control" placeholder="Enter tournament key..." required="" autofocus="">
        <br>
        <input type="password" class="form-control" placeholder="Enter table key..." required="">
        <br>
        <button class="btn btn-md btn-success btn-block" type="submit" style="margin: auto; width: 200px;">
            <span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>
            &nbsp; Waiting for other tables
        </button>
        <br>
        <div class="alert alert-danger">
            Error message... Try again.
        </div>
    </form>
</div>
<br>

