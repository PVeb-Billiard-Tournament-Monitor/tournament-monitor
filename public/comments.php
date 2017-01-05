<?php

	// ===== REGISTRACIJA STOLOVA =====
	// Sto salje ovoj skripti nisku sa sadrzinom "message=register_me&tournament_key=XXX&table_number=XXX"
		// Ukoliko registrovanje nije moguce, skripta salje jednu od sledecih niski:
			// "Invalid table number. Try again!"
			// "This table is already registered. Try again!"
			// "Wrong tournament key. Try again!"
		// Ukoliko je registrovanje moguce, skripta odgovara stolu slanjem niske "success".
	// Uspesno registrovani stolovi salju ovoj skripti nisku "message=is_tournament_ready&table_number=XXX".
		// Ukoliko svi stolovi nisu registrovani, skripta odgovara registrovanim stolovima slanjem niske "no".
		// Ukoliko su svi stolovi uspesno registrovani, skripta odgovara registrovanim stolovima slanjem sledece json niske "{p1_id=XXX, p2_id=XXX}"
				// i upisuje u bazu podatke vezane za trenutne meceve (player1_id, player2_id, table_id, round, ...).
	//=================================

	// ===== MEC JE U TOKU =====
	// Kada se rezultat promeni, sto salje ovoj skripti nisku "message=result_changed&table_id=XXX&player_id=XXX&player_result=XXX}" i ona to upisuje u bazu,
				// odnosno, u tabelu koja se odnosi na trenutne meceve.
	// =========================

	// ===== MEC JE ZAVRSEN =====
	// Kada se na stolu klikne FINISH, sto salje ovoj skripti nisku "message=match_finished&table_number=XXX".
		// Ukoliko postoje parovi na cekanju, ova skripta odgovara slanjem niske "{p1_id=XXX, p2_id=XXX}".
		// Ukoliko ne postoje parovi na cekanju, ova skripta odgovara slanjem niske "tournament_finished".
		// Ukoliko postoji samo jedan pobednik, a ceka se na drugog koji bi formirao par, ova skripta kao odgovor salje nisku "wait".
	// ==========================

?>
