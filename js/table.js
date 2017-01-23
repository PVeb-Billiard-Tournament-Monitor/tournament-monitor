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
                };

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
                $("div.container").html(
                '<h2 class="text-muted" style="text-align: center;">' +
                '<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate">' +
                '</span><br>Waiting for match!</h2>');
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
                };

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

                                var match_data =
                                {
                                    message: "score_changed",
                                    tournament_key: ref.table.t_key,
                                    table_number: ref.table.t_number,
                                    player1: {
                                        id: response.player1.id,
                                        name: response.player1.name,
                                        last_name: response.player1.last_name,
                                        image_link: response.player1.image_link,
                                        score: response.player1.score
                                    },
                                    player2: {
                                        id: response.player2.id,
                                        name: response.player2.name,
                                        last_name: response.player2.last_name,
                                        image_link: response.player2.image_link,
                                        score: response.player2.score
                                    }
                                };

                                ref.table.matchData = match_data;
                                ref.table.changeState(table.states.match);
                            } else if (response.message == "tournament_finished") {
                                clearInterval(int_id);
                                $("div.container").html(
                                    '<h2 class="text-muted" style="text-align: center;">' +
                                    'Tournament finished! </h2>'
                                );
                                setTimeout(function() {
                                    ref.table.changeState(table.states.notRegistered);
                                }, 2000);
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
                    "table_number": ref.table.t_number,
                    "tournament_key": ref.table.t_key
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
            update_score: function(e) {
                if ($(e.target).hasClass("player1")) {
                    player = "player1";
                } else {
                    player = "player2";
                }

                var cnt = $(e.target).html() == "+" ? 1 : -1;
                this.table.matchData[player].score += cnt;
                $("h1." + player).html(this.table.matchData[player].score);

            },
            finish_match: function(e) {
                this.table.matchData.message = "match_finished";
            },
            enter: function() {
                ref = this;
                $.ajax({
                    url: "/tournament-monitor/html/scoreboard.html",
                    dataType: "html",
                    success: function(response) {
                        $("div.container").html(response);
                        $("div.container").after("<div id='error_box'></div>");
                        //$("div.container").css("width", "60%");

                        $("span.player1").html(ref.table.matchData.player1.name + " " + ref.table.matchData.player1.last_name);
                        $("h1.player1").html(ref.table.matchData.player1.score);
                        $("img.player1").attr('src', ref.table.matchData.player1.image_link);

                        $("span.player2").html(ref.table.matchData.player2.name + " " + ref.table.matchData.player2.last_name);
                        $("h1.player2").html(ref.table.matchData.player2.score);
                        $("img.player2").attr('src', ref.table.matchData.player2.image_link);

                        $("button").click(function(e) {
                            ref.update(e);
                        });
                    },
                    error: function(response) {
                        $("div.container").html(response.responseText);
                    }
                });
            },
            exit: function() {
                $("div.container").html('');
            },
            update: function(e) {
                if (e === undefined)
                    return;
                var element = $(e.target);
                if (element.hasClass("btn-primary")) {
                    this.update_score(e);
                } else {
                    this.finish_match(e);
                }

                ref = this;
                $.ajax({
                    url: "/tournament-monitor/public/backend_script.php",
                    method: "POST",
                    data: {
                        "table_data": JSON.stringify(ref.table.matchData)
                    },
                    success: function(response) {
                        if (response == "wait_for_next_match") {
                            ref.table.changeState(table.states.waitingMatch);
                        } else if (response == "tournament_finished") {
                            $("div.container").html(
                                '<h2 class="text-muted" style="text-align: center;">' +
                                'Tournament finished! </h2>'
                            );
                            setTimeout(function() {
                                ref.table.changeState(table.states.notRegistered);
                            }, 2000);
                        }
                    },
                    error: function(response) {
                        $("#error_box").addClass("alert alert-danger").html(response.responseText);
                    }
                });
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
            if (this.currState != this.states.notRegistered)
                this.currState.update();
        }
    },
    update: function() {
        this.currState.update();
    }
};
