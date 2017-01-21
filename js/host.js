var host = {
	currState: undefined,
	hostData: undefined,
	states: {
		notLoggedIn: {
			error_handler: function(msg) {
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
				$("#login_button_text").html("Logging...");

				var ref = this;
				$.ajax({
					url: "/tournament-monitor/public/login_host.php",
					data: {
						username: username,
						password: password
					},
					async: true,
					method: "POST",
					dataType: "json",
					success: function(response) {
						if (response.message === "success") {
							ref.host.hostData = {};
							ref.host.hostData.username = response.username;
							ref.host.hostData.num_of_tables = response.num_of_tables;
							ref.host.changeState(host.states.loggedIn);
						} else {
							ref.error_handler(response.message);
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
				$("#bootstrap-duallistbox-selected-list_players > option").
					each(function(n, e) {
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

				var player_length = players.length;
				if (player_length && (player_length & (player_length - 1)) !== 0) {
					ref.error_handler("There must be power of 2 players selected!");
					return;
				}

				data.players = players;
				data.tournament_type = $("input[name='type'][type='radio']:checked").val();


				$.ajax({
					url: "/tournament-monitor/public/login_host.php",
					data: { "host_data": JSON.stringify(data) },
					method: "POST",
					async: false,
					dataType: "json",
					success: function(response) {
						if (response.message === "success") {
							ref.host.hostData.id = response.id;
							ref.host.hostData.tournament_key =
								$("#tournament_key").val();
							ref.host.hostData.type = response.type;
							ref.host.hostData.date = response.date;
						} else {
							ref.error_handler(response.message);
						}

					},
					error: function(response) {
						console.log(response.responseText);
					}
				});

				var fill_table_data = {
					message: "fill_match_table",
					tournament_date: ref.host.hostData.date,
					billiard_club_id: ref.host.hostData.id,
					tournament_type: ref.host.hostData.type
				};

				$.ajax({
					url: "/tournament-monitor/public/backend_script.php",
					data: {
						"table_data": JSON.stringify(fill_table_data)
					},
					dataType: "json",
					async: false,
					method: "POST",
					success: function(response) {
						if (response.message === "success") {
                            console.log(JSON.stringify(response, null, '\t'));
							//ref.host.changeState(host.states.waitingTournament);
						} else {
                            console.log(response);
                        }

					},
					error: function(response) {
						ref.error_handler(response);
					}
				});
			}
		},
		waitingTournament: {
			init: function(host) {
				this.host = host;
			},
			enter: function() {
				$("div.container").html(
				'<h2 class="text-muted" style="text-align: center;">' +
				'<span class="glyphicon glyphicon-refresh ' +
				'glyphicon-refresh-animate"></span>' +
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
					"tournament_key": ref.host.hostData.tournament_key,
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
