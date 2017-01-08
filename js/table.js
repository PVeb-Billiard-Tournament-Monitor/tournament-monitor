var table = {
    currState: undefined,
    matchData: undefined,
    states: {
        notRegistered: {
            error_handler: function(msg) {
                $("#error_box").addClass("alert alert-danger").html(msg);
            },
            init: function(table) {
                this.table = table;
            },
            enter: function() {
                $.ajax({
                    url: "/tournament-monitor/html/table_reg_form.html",
                    dataType: "html",
                    success: function(response) {
                        $("div.container").html(response);
                    },
                    error: function(response) {
                        $("div.container").html(response.responseText);
                    }
                });
            },
            exit: function() {
                $("div.container").html('');
            },
            update: function() {
                var t_key = $("input[name='tournament_key']").val();
                var t_number = $("input[name='table_number']").val();

                if (!t_key || !t_number) {
                    this.error_handler("Both fields are required!");
                    return;
                }

                var string_data =
                {
                    "message":"register_me",
                    "tournament_key": t_key,
                    "table_number": t_number
                }

                var json_data = JSON.stringify(string_data);

                ref = this;
                $.ajax({
                    url: "/tournament-monitor/public/backend_script.php",
                    method: "POST",
                    data: { "table_data": json_data },
                    success: function (response) {
                        if (response == "success") {
                            ref.table.t_number =  t_number;
                            ref.table.t_key =  t_key;
                            ref.table.changeState(table.states.waitingTournament);
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
        waitingMatch: {
            init: function(table) {
                this.table = table;
            },
            enter: function() {
                $("div.container").html('\
                <h2 class="text-muted" style="text-align: center;">\
                <span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>\
                <br>Waiting for match!</h2>');
            },
            exit: function() {
                $("div.container").html('');
            },
            update: function() {
                ref = this;
                var string_data =
                {
                    "message":"is_match_ready",
                    "table_number": ref.table.t_number,
                    "tournament_key": ref.table.t_key
                }
                var json_data = JSON.stringify(string_data);
                var int_id = setInterval(function() {
                    $.ajax({
                        url: "/tournament-monitor/public/backend_script.php",
                        data: { "table_data": json_data },
                        method: "POST",
                        dataType: "json",
                        success: function(response) {
                            if (response.message === 'yes') {
                                clearInterval(int_id);
                                ref.table.changeState(table.states.match);
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
        waitingTournament: {
            init: function(table) {
                this.table = table;
            },
            enter: function() {
                $("div.container").html('\
                <h2 class="text-muted" style="text-align: center;">\
                <span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>\
                <br>Waiting for tournament!</h2>');
            },
            exit: function() {
                $("div.container").html('');
            },
            update: function() {
                ref = this;
                var string_data =
                {
                    "message":"is_tournament_ready",
                    "table_number": ref.table.t_number,
                    "tournament_key": ref.table.t_key
                }
                var json_data = JSON.stringify(string_data);
                var int_id = setInterval(function() {
                    $.ajax({
                        url: "/tournament-monitor/public/backend_script.php",
                        data: { "table_data": json_data },
                        method: "POST",
                        success: function(response) {
                            if (response === 'yes') {
                                clearInterval(int_id);
                                ref.table.changeState(table.states.waitingMatch);
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
        match: {
            init: function(table) {
                this.table = table;
            },
            enter: function() {
                $.ajax({
                    url: "/tournament-monitor/html/scoreboard.html",
                    dataType: "html",
                    success: function(response) {
                        $("div.container").html(response);
                    },
                    error: function(response) {
                        $("div.container").html(response.responseText);
                    }
                });
            },
            exit: function() {
                $("div.container").html('');
            },
            update: function() {
                // TODO: implement the requests and ui for match score update
            }
        },
    },
    init: function() {
        this.states.notRegistered.init(this);
        this.states.waitingMatch.init(this);
        this.states.waitingTournament.init(this);
        this.states.match.init(this);

        this.currState = this.states.notRegistered;
        this.currState.enter();
    },
    changeState: function(state) {
        if (state != this.currState) {
            this.currState.exit();
            this.currState = state;
            this.currState.enter();
            this.currState.update();
        }
    },
    update: function() {
        this.currState.update();
    }
}
