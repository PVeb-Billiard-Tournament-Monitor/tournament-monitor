use billiard_db;
-- round 1
insert into new_match
(match_id, tournament_date, billiard_club_id, tournament_type, id_player1, id_player2,
    score1, score2, round, finished, at_table, winner_id) values
(DEFAULT, '2017-01-15', 1, 'Drzavni', 1, 2, 3, 1, 1, DEFAULT, DEFAULT, DEFAULT);

insert into new_match
(match_id, tournament_date, billiard_club_id, tournament_type, id_player1, id_player2,
    score1, score2, round, finished, at_table, winner_id) values
(DEFAULT, '2017-01-15', 1, 'Drzavni', 3, 4, 2, 3, 1, DEFAULT, DEFAULT, DEFAULT);

insert into new_match
(match_id, tournament_date, billiard_club_id, tournament_type, id_player1, id_player2,
    score1, score2, round, finished, at_table, winner_id) values
(DEFAULT, '2017-01-15', 1, 'Drzavni', 5, 6, 0, 3, 1, DEFAULT, DEFAULT, DEFAULT);

insert into new_match
(match_id, tournament_date, billiard_club_id, tournament_type, id_player1, id_player2,
    score1, score2, round, finished, at_table, winner_id) values
(DEFAULT, '2017-01-15', 1, 'Drzavni', 7, 8, 3, 0, 1, DEFAULT, DEFAULT, DEFAULT);

-- round 2
insert into new_match
(match_id, tournament_date, billiard_club_id, tournament_type, id_player1, id_player2,
    score1, score2, round, finished, at_table, winner_id) values
(DEFAULT, '2017-01-15', 1, 'Drzavni', 6, 7, 5, 4, 2, DEFAULT, DEFAULT, DEFAULT);

insert into new_match
(match_id, tournament_date, billiard_club_id, tournament_type, id_player1, id_player2,
    score1, score2, round, finished, at_table, winner_id) values
(DEFAULT, '2017-01-15', 1, 'Drzavni', 1, 4, 15, 7, 2, DEFAULT, DEFAULT, DEFAULT);

-- round 3
insert into new_match
(match_id, tournament_date, billiard_club_id, tournament_type, id_player1, id_player2,
    score1, score2, round, finished, at_table, winner_id) values
(DEFAULT, '2017-01-15', 1, 'Drzavni', 6, 1, 10, 9, 3, DEFAULT, DEFAULT, DEFAULT);
