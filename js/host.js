var host = {
    currState: undefined,
    hostData: undefined,
    states: {
        notLoggedIn: {
            error_handler: function(msg) {
                console.log("im here");
                $("#error_box").addClass("alert alert-danger");
                $("#error_box").html(msg);
                $("#refresh").removeClass("glyphicon glyphicon-refresh glyphicon-refresh-animate");
                $("#login_button_text").html("Log in");
            },
            init: function(host) {
                this.host = host;
            },
            enter: function() {
                var ref = this;
                $.ajax({
                    url: "/tournament-monitor/html/host_login_form.html",
                    dataType: "html",
                    success: function(response) {
                        $("div.container").html(response);
                    },
                    error: function(response) {
                        ref.error_handler(response.responseText);
                    }
                });
            },
            exit: function() {
                $("div.container").html('');
            },
            update: function() {
                var username = $("input[type='text']").val();
                var password = $("input[type='password']").val();
                if(!username || !password) {
                    this.error_handler("Both fields are required!");
                    return;
                }

                $("#refresh").addClass("glyphicon glyphicon-refresh glyphicon-refresh-animate");
                $("#login_button_text").html("logging...");

                var ref = this;
                $.ajax({
                    url: "/tournament-monitor/public/login_host.php",
                    data: {
                        username: username,
                        password: password
                    },
                    async: true,
                    method: "POST",
                    success: function(response) {
                        if (response === "success") {
                            ref.host.hostData = {};
                            ref.host.hostData.username = username;
                            ref.host.changeState(host.states.loggedIn);
                        } else {
                            ref.error_handler(response);
                        }
                    },
                    error: function(response) {
                        ref.error_handler(response.responseText);
                    }
                });
            }
        },
        loggedIn: {
            error_handler: function(msg) {
                console.log("Im error logged in");
                $("#error_box").addClass("alert alert-danger");
                $("#error_box").html(msg);
            },
            init: function(host) {
                this.host = host;
            },
            enter: function() {
                var ref = this;
                $.ajax({
                    url: "/tournament-monitor/html/player_selection.php",
                    dataType: "html",
                    success: function(response) {
                        $("div.container").html(response);
                    },
                    error: function(response) {
                        ref.error_handler(response.responseText);
                    }
                });
                $("div.container").css("width", "60%");
                $("nav ul").append("<li style='position: absolute; right: 0px;'>" +
                    "<a href='/tournament-monitor/public/login_host.php?logout'>" +
                        "Logout</a></li>");
            },
            exit: function() {
                $("div.container").html('');
            },
            update: function() {
                var ref = this;

                var players = [];
                $("#bootstrap-duallistbox-selected-list_players > option").each(function(n, e) {
                    players.push($(e).val());
                });

                var data = {};
                var missing_input = false;

                $(":required").each(function(n, e) {
                    if (!$(e).val()) {
                        missing_input = true;
                    }
                    data[$(e).prop("id")] = $(e).val();
                });

                if (missing_input) {
                    ref.error_handler("Please input all the required fields!");
                    return;
                }

                if (players.length === 0) {
                    ref.error_handler("There must be at least 1 player selected!");
                    return;
                }
                data.players = players;
                data.tournament_type = $("input[name='type'][type='radio']:checked").val();

                $.ajax({
                    url: "/tournament-monitor/public/login_host.php",
                    data: { "host_data": JSON.stringify(data) },
                    method: "POST",
                    async: true,
                    dataType: "text",
                    success: function(response) {
                        if (response === "success") {
                            ref.host.t_key = data.tournament_key;
                            ref.host.changeState(host.states.waitingTournament);
                        } else {
                            ref.error_handler(response);
                        }

                    },
                    error: function(response) {
                        console.log(response.responseText);
                    }
                });

                //console.log(JSON.stringify(data));
            }
        },
        waitingTournament: {
            init: function(host) {
                this.host = host;
            },
            enter: function() {
                $("div.container").html(
                '<h2 class="text-muted" style="text-align: center;">' +
                '<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>' +
                '<br>Waiting for tournament!</h2>');
            },
            exit: function() {
                $("div.container").html('');
            },
            update: function() {
                ref = this;
                var string_data =
                {
                    "message":"is_tournament_ready",
                    "tournament_key": ref.host.t_key,
                };
                var json_data = JSON.stringify(string_data);
                var int_id = setInterval(function() {
                    $.ajax({
                        url: "/tournament-monitor/public/backend_script.php",
                        data: { "table_data": json_data },
                        method: "POST",
                        success: function(response) {
                            if (response === 'yes') {
                                clearInterval(int_id);
                                ref.host.changeState(host.states.monitoringTournament);
                            }
                        },
                        error: function(response) {
                            clearInterval(int_id);
                            $("div.container").html(response.responseText);
                        }
                    });
                }, 2000);
            }
        },
        monitoringTournament: {
            init: function(host) {
                this.host = host;
            },
            enter: function() {
                $("div.container").html(
                '<h2 class="text-muted" style="text-align: center;">' +
                '<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>' +
                '<br>Tournament in progress</h2>');
            },
            exit: function() {
                $("div.container").html('');
            },
            update: function() {

            }
        }
    },
    init: function() {
        this.states.notLoggedIn.init(this);
        this.states.loggedIn.init(this);
        this.states.waitingTournament.init(this);
        this.states.monitoringTournament.init(this);

        this.currState = this.states.notLoggedIn;
        this.currState.enter();
    },
    changeState: function(state) {
        if (state != this.currState) {
            this.currState.exit();
            this.currState = state;
            this.currState.enter();
            if (this.currState != this.states.loggedIn)
                this.currState.update();
        }
    },
    update: function() {
        this.currState.update();
    }
};

host.init();
$(document).ready(function() {
    $("div.container").on("click", "button", function() {
        host.update();
    });
});
