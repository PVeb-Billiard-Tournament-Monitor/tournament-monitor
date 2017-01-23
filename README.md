## Frontend

Korisnik na raspolaganju ima tri opcije:

* ***posmatranje turnira*** <br>
    Korisniku se izlistavaju aktivni turniri koje može da posmatra. Svaki turnir je opisan svojim nazivom, bilijarskim klubom u kom se turnir održava, tipom i datumom održavanja. <br>
    Korisnik bira turnir koji želi da posmatra. <br>
    Korisniku se prikazuje šema i trenutni rezultat turnira koji je izabrao. <br>

* ***kreiranje turnira*** <br>
    Korisnik vrši odabir kluba (klub se mora nalaziti u bazi podataka, ukoliko je klub registrovan). <br>
    Korisnik unosi svoj *username* i *password* koji mu je dodeljen prilikom registracije kluba. <br>
    Korisnik unosi proizvoljni naziv i šifru turnira. <br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Dodeljena šifra se kasnije upotrebljava za registraciju stolova. <br>
    Korisnik određuje tip turnira. <br>
    Korisnik vrši izbor igrača koji će se takmičiti. <br>
    Nakon odabira igrača, prelazi se u fazu registracije stolova. <br>
    Nakon registracije svih stolova, omogućuje se pokretanje turnira. <br>
    Korisniku se iscrtava šema turnira i stolovi na kojima igraju parovi. <br>
    Korisnik ima mogućnost da završi ili prekine turnir. <br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Završeni turnir se briše iz liste aktivnih turnira koje je moguće posmatrati. <br>

* ***registracija stola na kom će se protivnici takmičiti*** <br>
    Korisnik unosi identifikator stola i šifru turnira koju je postavio prilikom kreiranja turnira. <br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Ukoliko je identifikator stola u upotrebi (već registrovan), ponavlja se prethodni korak. <br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Ukoliko identifikator stola nije u upotrebi, prikazuje se obaveštenje da se čeka da server pokrene turnir. <br>
    Kada server pokrene turnir, takmičarima se prikazuju njihove slike iz baze i mogućnost podešavanja rezultata. <br>
    Korisnik ima mogućnost da završi meč. <br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Tom akcijom se server obaveštava o završetku meča i pobednik čeka novi meč. <br>

## Bekend

Baza podataka čuva podatke o trenutno registrovanim klubovima, igračima, aktivnim i odigranim turnirima. <br>
Bekend omogućuje generisanje i iscrtavanje šeme koju prikazuje korisnicima. <br>
Bekend vrši kontrolisanje i praćenje toka turnira: registraciju stolova, provera zadovoljenosti uslova za početak turnira, odabir igrača za naredni meč, provera zadovoljenosti uslova za završetak turnira. <br>
Bekend obaveštava korisnike koji meč se igra na kom stolu. <br>
Bekend čuva redosled mečeva koji su na listi čekanja. <br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Kada se oslobodi neki sto, prvi meč sa liste čekanja se postavlja za taj sto. <br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Nakon određivanja meča sledećeg kola (par protivnika), pomenuti meč se postavlja na kraj liste čekanja. <br>

## Kontakt informacije

Repozitorijum: <br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; https://github.com/PVeb-Billiard-Tournament-Monitor/tournament-monitor <br>

Učesnici: <br>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Isidora Đurđević &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 1097/2016 <br>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Đorđe Milićević &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 1091/2016 <br>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Daniel Doža &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 1098/2016 <br>
