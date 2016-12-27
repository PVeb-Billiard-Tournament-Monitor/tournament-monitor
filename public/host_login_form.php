<?php

	echo
    '
        <br>
        <style type="text/css">

            .glyphicon-refresh-animate
            {
                -animation: spin .7s infinite linear;
                -webkit-animation: spin2 .7s infinite linear;
            }

            @-webkit-keyframes spin2
            {
                from { -webkit-transform: rotate(0deg);}
                to { -webkit-transform: rotate(360deg);}
            }
        </style>

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
                <h2 class="form-signin-heading text-muted" style="text-align: center;">Host a tournament</h2>
                <br>
                <input type="text" class="form-control" placeholder="Enter username..." required="" autofocus="">
                <br>
                <input type="password" class="form-control" placeholder="Enter password..." required="">
                <br>
                <button class="btn btn-md btn-success btn-block" type="submit" style="margin: auto; width: 200px;">
                    <span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>
                    &nbsp; Logging...
                </button>
                <br>
                <div class="alert alert-danger">
                    Error message... Try again.
                </div>
            </form>
        </div>
        <br>
	';

?>
