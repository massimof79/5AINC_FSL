# PCTOConnect — 5AINC\_FSL

> Piattaforma web completa per la gestione delle esperienze PCTO, sviluppata collaborativamente dalla classe **5AINC** come progetto scolastico.

---

## Indice

1. [Cos'è il PCTO?](#cosè-il-pcto)
2. [Panoramica del progetto](#panoramica-del-progetto)
3. [Funzionalità](#funzionalità)
4. [Tecnologie utilizzate](#tecnologie-utilizzate)
5. [Architettura](#architettura)
6. [Struttura dei file](#struttura-dei-file)
7. [Schema del database](#schema-del-database)
8. [Moduli](#moduli)
   - [Gruppo 1 — Aziende](#gruppo-1--aziende)
   - [Gruppo 2 — Disponibilità](#gruppo-2--disponibilità)
   - [Gruppo 3 — Esperienze](#gruppo-3--esperienze)
   - [Gruppo 4 — Personale](#gruppo-4--personale)
9. [Infrastruttura condivisa](#infrastruttura-condivisa)
10. [Sistema dei temi](#sistema-dei-temi)
11. [Autenticazione](#autenticazione)
12. [Riferimento API](#riferimento-api)
13. [Installazione](#installazione)
14. [Configurazione](#configurazione)
15. [Utilizzo](#utilizzo)
16. [Team](#team)

---

## Cos'è il PCTO?

Il **PCTO** (*Percorsi per le Competenze Trasversali e l'Orientamento*) è il programma obbligatorio di alternanza scuola-lavoro per gli studenti delle scuole superiori italiane. Ogni studente deve completare un numero minimo di ore di esperienza pratica presso aziende esterne durante gli ultimi anni scolastici.

Gestire il PCTO significa coordinare:
- **Aziende** che offrono posizioni di tirocinio
- **Disponibilità** (quando e quanti studenti un'azienda può accogliere)
- **Esperienze** (le sessioni PCTO effettivamente svolte)
- **Tutor scolastici** (insegnanti che supervisionano il percorso degli studenti)
- **Tutor aziendali** (dipendenti che affiancano gli studenti in sede)
- **Studenti** e le esperienze loro assegnate

PCTOConnect centralizza tutto questo in un'unica applicazione web.

---

## Panoramica del progetto

PCTOConnect è un **sistema di gestione basato su browser** con un backend PHP, un database MySQL e un frontend in JavaScript puro e CSS. Non viene utilizzato alcun framework JavaScript esterno — tutto è costruito da zero con gli standard web moderni.

Il progetto è stato sviluppato in **quattro gruppi**, ognuno responsabile di un dominio diverso dell'applicazione. Tutti i gruppi condividono un sistema di design comune, un livello di autenticazione e un modulo di connessione al database.

L'applicazione richiede un login per accedere a qualsiasi pagina. Una volta autenticati, gli utenti possono navigare tra i moduli tramite la barra laterale, creare e modificare record attraverso finestre modali, e cercare o sfogliare i dati nelle tabelle.

---

## Funzionalità

- **Autenticazione sicura** — login basato su sessione con hashing bcrypt delle password
- **Gestione aziende** — CRUD completo per le aziende, con validazione della Partita IVA
- **Gestione disponibilità** — gestione degli slot di tirocinio offerti dalle aziende
- **Gestione esperienze** — tracciamento delle sessioni PCTO completate con ore e tutor collegati
- **Gestione tutor scolastici** — gestione degli insegnanti che supervisionano le attività PCTO
- **Gestione tutor aziendali** — gestione dei mentori lato azienda
- **Gestione studenti** — anagrafica completa con esperienze e candidature collegate
- **Ricerca live e paginazione** — ogni tabella è ricercabile e paginata
- **Tre temi visivi** — cambiabili a runtime, persistiti tramite localStorage
- **Layout responsivo** — la barra laterale si riduce a menu hamburger su schermi ≤ 900 px
- **Notifiche toast** — feedback non bloccante per ogni azione utente
- **Contrasto WCAG AA** — tutti i temi superano i requisiti di accessibilità
- **Nessun ricaricamento di pagina** — tutte le operazioni CRUD usano la Fetch API con JSON

---

## Tecnologie utilizzate

| Livello | Tecnologia |
|---|---|
| Linguaggio server | PHP 8.1+ (strict types, PDO) |
| Database | MySQL / MariaDB 10.4+ |
| Markup frontend | HTML5 (elementi semantici) |
| Stile frontend | CSS3 (custom properties, Grid, Flexbox) |
| Logica frontend | JavaScript ES2020+ (Fetch API, async/await) |
| Font | Google Fonts — Inter |
| Icone | Libreria SVG inline personalizzata (`icons/icons.js`) |
| Autenticazione | Sessioni PHP + bcrypt (`password_hash`) |
| Accesso al database | PDO con prepared statements |

Nessun npm, nessuna build, nessun framework. Basta un server web con supporto PHP per farlo funzionare.

---

## Architettura

```
Browser
  │
  ├── Carica la pagina HTML (PHP genera lo scheletro iniziale)
  │     └── <link id="theme-link"> — file CSS attivo
  │     └── theme.js — legge localStorage, sostituisce il CSS prima del primo render (prevenzione FOUC)
  │
  ├── JavaScript (file .js per modulo) chiama gli endpoint REST tramite Fetch API
  │     └── Restituisce JSON {success, message, data}
  │
  └── Endpoint REST PHP
        ├── requireLoginApi() — rifiuta le richieste non autenticate con HTTP 401
        ├── PDO prepared statement — interroga il database
        └── echo json_encode(...) — risponde al browser
```

Ogni pagina segue lo stesso schema:
1. PHP verifica la sessione al caricamento della pagina; reindirizza a `login.php` se non autenticato.
2. Lo scheletro della pagina (barra laterale, topbar, tabella vuota) viene renderizzato immediatamente.
3. Il file JavaScript del modulo si attiva su `DOMContentLoaded`, chiama l'API e riempie la tabella.
4. Le interazioni utente (crea, modifica, elimina) aprono una modale, inviano via `fetch()` e aggiornano la tabella senza ricaricare.

---

## Struttura dei file

```
5AINC_FSL/
│
├── index.php                   # Dashboard — punto di ingresso dopo il login
├── login.php                   # Form di login
├── logout.php                  # Terminazione della sessione
├── register.php                # Registrazione utente
│
├── config.php                  # Connessione PDO al database (Gruppo 1)
├── auth.php                    # Funzioni di gestione sessione (condiviso)
├── api.php                     # Router REST API unificato (condiviso)
├── api_session.php             # Endpoint stato sessione
│
├── theme.js                    # Switcher a 3 temi (FOUC-safe)
├── global.css                  # Tema 1: Classic (blu navy / verde)
├── global2.css                 # Tema 2: Aurora (petrolio / teal)
├── global3.css                 # Tema 3: Brutale (crema Olivetti / vermillion)
│
├── 5ainc_fsl.sql               # Schema completo del database con dati di esempio
│
├── icons/
│   ├── icons.js                # Costanti SVG (stato, navigazione, azioni)
│   └── *.svg                   # File icone singoli
│
├── Gruppo1/
│   ├── index_aziende.php       # Pagina aziende
│   ├── aziende.js              # Logica frontend aziende
│   └── aziende_middleware.php  # API REST aziende
│
├── Gruppo 2/
│   ├── disponibilità.php       # Pagina disponibilità (HTML + JS inline)
│   └── middlewere.php          # API REST disponibilità
│
├── Gruppo 3/
│   ├── esperienze.php          # Pagina esperienze
│   ├── esperienze.js           # Logica frontend esperienze
│   └── api_esperienze.php      # API REST esperienze
│
└── Gruppo4/
    ├── tutor_scolastici.html   # Pagina tutor scolastici
    ├── tutor_scolastici.js     # Logica frontend tutor scolastici
    ├── tutor_aziendali.html    # Pagina tutor aziendali
    ├── tutor_aziendali.js      # Logica frontend tutor aziendali
    ├── studenti.html           # Pagina studenti
    └── studenti.js             # Logica frontend studenti
```

---

## Schema del database

Il database si chiama `5AINC_FSL`. Importare `5ainc_fsl.sql` per creare tutte le tabelle, le viste e i dati di esempio.

### Panoramica delle relazioni

```
AZIENDA ──< DISPONIBILITA ──< ESPERIENZA >── TUTOR_SCOLASTICO
                                    │
                              TUTOR_AZIENDALE

STUDENTE >── ESPERIENZA
STUDENTE >── CANDIDATURA
```

### Tabelle

#### `AZIENDA` — Aziende
| Colonna | Tipo | Note |
|---|---|---|
| `codice_azienda` | INT | Chiave primaria, auto-increment |
| `ragione_sociale` | VARCHAR | Nome dell'azienda |
| `partita_iva` | VARCHAR(11) | P.IVA, univoca, 11 cifre |
| `sede_legale` | VARCHAR | Indirizzo della sede legale |
| `sede_operativa` | VARCHAR | Indirizzo della sede operativa |

#### `DISPONIBILITA` — Disponibilità
| Colonna | Tipo | Note |
|---|---|---|
| `codice_disponibilita` | INT | Chiave primaria |
| `periodo_previsto` | DATE | Periodo dello slot offerto |
| `numero_studenti` | INT | Studenti massimi (> 0) |
| `descrizione` | TEXT | Descrizione dell'attività |
| `competenze` | TEXT | Competenze richieste |
| `indirizzo_consigliato` | VARCHAR | Indirizzo di studi consigliato |
| `codice_azienda` | INT | FK → AZIENDA (CASCADE DELETE) |

#### `ESPERIENZA` — Esperienze PCTO
| Colonna | Tipo | Note |
|---|---|---|
| `codice_esperienza` | INT | Chiave primaria |
| `periodo_effettivo` | VARCHAR | Periodo reale (testo libero) |
| `numero_ore_previste` | INT | Ore pianificate (≥ 0) |
| `numero_ore_svolte` | INT | Ore effettivamente svolte (≥ 0) |
| `numero_studenti` | INT | Studenti coinvolti (> 0) |
| `codice_docente` | INT | FK → TUTOR_SCOLASTICO (UPDATE CASCADE) |
| `codice_disponibilita` | INT | FK → DISPONIBILITA (UPDATE CASCADE) |
| `codice_tutor` | INT | FK → TUTOR_AZIENDALE (UPDATE CASCADE) |

#### `TUTOR_SCOLASTICO` — Tutor Scolastici
| Colonna | Tipo | Note |
|---|---|---|
| `codice_docente` | INT | Chiave primaria |
| `nome` | VARCHAR | Nome |
| `cognome` | VARCHAR | Cognome |
| `tipo` | ENUM | `'dipartimento'` oppure `'area disciplinare'` |
| `numero_studenti` | INT | Numero di studenti supervisionati |

#### `TUTOR_AZIENDALE` — Tutor Aziendali
| Colonna | Tipo | Note |
|---|---|---|
| `codice_tutor` | INT | Chiave primaria |
| `nome` | VARCHAR | Nome |
| `cognome` | VARCHAR | Cognome |
| `ruolo` | VARCHAR | Ruolo in azienda (es. "Software Engineer") |
| `email` | VARCHAR | Indirizzo email univoco |

#### `STUDENTE` — Studenti
| Colonna | Tipo | Note |
|---|---|---|
| `codice_studente` | INT | Chiave primaria |
| `nome` | VARCHAR | Nome |
| `cognome` | VARCHAR | Cognome |
| `data_di_nascita` | DATE | Data di nascita |
| `luogo_di_nascita` | VARCHAR | Luogo di nascita |
| `indirizzo` | VARCHAR | Indirizzo di residenza |
| `email` | VARCHAR | Email univoca |
| `classe` | VARCHAR | Classe scolastica (es. `4AI`) |
| `indirizzo_di_studi` | VARCHAR | Indirizzo di studi (es. `Informatica`) |
| `codice_esperienza` | INT | FK → ESPERIENZA (SET NULL all'eliminazione) |
| `codice_candidatura` | INT | FK → CANDIDATURA (SET NULL all'eliminazione) |

#### `CANDIDATURA` — Candidature
| Colonna | Tipo | Note |
|---|---|---|
| `codice_candidatura` | INT | Chiave primaria |
| `data_candidatura` | DATE | Data della candidatura |
| `lettera_motivazionale` | TEXT | Lettera motivazionale |
| `stato_candidatura` | ENUM | `inserita`, `in valutazione`, `accettata`, `rifiutata`, `ritirata` |

#### `UTENTI` — Account Utente
| Colonna | Tipo | Note |
|---|---|---|
| `ID` | INT | Chiave primaria |
| `username` | VARCHAR | Username univoco |
| `password` | VARCHAR | Hash bcrypt |

### Viste

**`v_studenti_anagrafica`** — Vista in sola lettura che espone un sottoinsieme sicuro dei campi studente (esclude i codici interni sensibili). Utilizzata per la visualizzazione.

---

## Moduli

### Gruppo 1 — Aziende

**Pagina:** `Gruppo1/index_aziende.php`  
**API:** `Gruppo1/aziende_middleware.php`  
**Logica:** `Gruppo1/aziende.js`

Gestisce le aziende che partecipano ai programmi PCTO.

**Cosa si può fare:**
- Visualizzare l'elenco completo delle aziende in una tabella paginata e ricercabile
- Aggiungere una nuova azienda (nome, Partita IVA, sede legale, sede operativa)
- Modificare i dati di qualsiasi azienda
- Eliminare un'azienda (elimina automaticamente anche tutte le sue disponibilità tramite CASCADE)

**Validazione:**
- La Partita IVA deve essere composta esattamente da 11 cifre numeriche
- Tutti i campi sono obbligatori

**Colonne della tabella:** `#`, Ragione Sociale, P.IVA, Sede Legale, Sede Operativa, Azioni

---

### Gruppo 2 — Disponibilità

**Pagina:** `Gruppo 2/disponibilità.php`  
**API:** `Gruppo 2/middlewere.php`

Gestisce gli slot di tirocinio offerti dalle aziende. Una "disponibilità" è la dichiarazione di un'azienda di poter accogliere un certo numero di studenti in un determinato periodo.

**Cosa si può fare:**
- Visualizzare tutti gli slot con ricerca per periodo o descrizione
- Creare un nuovo slot (periodo, studenti massimi, descrizione, competenze richieste, indirizzo consigliato)
- Modificare uno slot esistente
- Eliminare uno slot

**Colonne della tabella:** `#`, Periodo, Descrizione, Azioni

**Nota:** Ogni disponibilità è collegata a un'azienda. Quando un'azienda viene eliminata, tutti i suoi slot vengono eliminati automaticamente.

---

### Gruppo 3 — Esperienze

**Pagina:** `Gruppo 3/esperienze.php`  
**API:** `Gruppo 3/api_esperienze.php`  
**Logica:** `Gruppo 3/esperienze.js`

Questo è il **modulo centrale del dominio**. Un'esperienza rappresenta una sessione PCTO effettivamente svolta — collega uno slot di disponibilità aziendale al tutor scolastico e al tutor aziendale che l'hanno supervisionata.

**Cosa si può fare:**
- Visualizzare tutte le esperienze con dati unificati tramite JOIN (nomi dei tutor, periodo della disponibilità)
- Creare una nuova esperienza con sette campi
- Modificare un'esperienza esistente
- Eliminare un'esperienza

**Campi del form:**
| Campo | Descrizione |
|---|---|
| Periodo effettivo | Descrizione testuale del periodo reale (es. `Mar–Apr 2026`) |
| Ore previste | Ore pianificate |
| Ore svolte | Ore effettivamente completate |
| Numero studenti | Studenti partecipanti |
| Tutor scolastico | Menu a tendina popolato dal database |
| Tutor aziendale | Menu a tendina popolato dal database |
| Disponibilità | Slot di disponibilità collegato — menu a tendina dal database |

**Colonne della tabella:** `#`, Periodo, Ore Previste, Ore Svolte, Studenti, Tutor Scolastico, Tutor Aziendale, Disponibilità, Azioni

**Il Gruppo 3 mantiene anche `global.css`** — il sistema di design condiviso da tutte le pagine dell'applicazione.

---

### Gruppo 4 — Personale

Il Gruppo 4 gestisce tre entità distinte, ognuna con la propria pagina.

---

#### Tutor Scolastici

**Pagina:** `Gruppo4/tutor_scolastici.html`  
**Logica:** `Gruppo4/tutor_scolastici.js`

Gestisce gli insegnanti che svolgono il ruolo di supervisori scolastici per le esperienze PCTO.

**Campi del form:** Nome, Cognome, Tipo (`dipartimento` o `area disciplinare`), Numero di studenti supervisionati

**Colonne della tabella:** `#`, Nome, Cognome, Tipo, Numero Studenti, Azioni

---

#### Tutor Aziendali

**Pagina:** `Gruppo4/tutor_aziendali.html`  
**Logica:** `Gruppo4/tutor_aziendali.js`

Gestisce i dipendenti aziendali che affiancano gli studenti durante il tirocinio.

**Campi del form:** Nome, Cognome, Ruolo, Email

**Colonne della tabella:** `#`, Nome, Cognome, Ruolo, Email, Azioni

---

#### Studenti

**Pagina:** `Gruppo4/studenti.html`  
**Logica:** `Gruppo4/studenti.js`

L'anagrafica completa degli studenti. Ogni studente può essere collegato a un'esperienza PCTO e a una candidatura.

**Campi del form:** Nome, Cognome, Data di nascita, Luogo di nascita, Indirizzo, Email, Classe, Indirizzo di studi, Esperienza PCTO (opzionale, menu a tendina), Candidatura (opzionale, menu a tendina)

**Colonne della tabella:** `#`, Studente, Data di nascita, Luogo, Indirizzo, Email, Classe, Indirizzo Studi, Esperienza, Candidatura, Stato, Azioni

---

## Infrastruttura condivisa

### Connessione al database — `config.php`

Stabilisce una connessione PDO al database MySQL. Utilizzato da ogni file API tramite `require_once`.

- Host: `127.0.0.1`, Porta: `3307` (configurabile)
- Database: `5AINC_FSL`
- Modalità errori: `PDO::ERRMODE_EXCEPTION` — gli errori lanciano eccezioni, non vengono mai esposti al browser
- Emulazione prepared statements: disabilitata — solo prepared statements reali
- Charset: `utf8mb4`

### Autenticazione — `auth.php`

Fornisce le funzioni di gestione sessione usate in tutte le pagine e API:

| Funzione | Descrizione |
|---|---|
| `isLoggedIn()` | Restituisce true se l'utente ha una sessione attiva |
| `requireLoginPage()` | Reindirizza a `login.php` se non autenticato (usato nei file pagina) |
| `requireLoginApi()` | Restituisce HTTP 401 JSON se non autenticato (usato nelle API) |
| `loginUser(array)` | Rigenera l'ID di sessione e imposta `$_SESSION['user_id']` e `username` |
| `logoutUser()` | Cancella i dati di sessione, distrugge la sessione, scade i cookie |
| `verifyUserPassword(input, hash)` | Verifica con `password_verify()`; fallback su hash-equals per account legacy |

### API unificata — `api.php`

Un unico punto di ingresso che instrada le richieste al modulo corretto in base al parametro `?entity=`. Tutte le operazioni CRUD sono protette: ogni richiesta passa per `requireLoginApi()` prima di raggiungere qualsiasi codice di database.

**Entità supportate:**
- `?entity=aziende`
- `?entity=disponibilita`
- `?entity=esperienze`
- `?entity=tutor_scolastici`
- `?entity=tutor_aziendali`

Tutte le risposte API seguono questa struttura JSON:

```json
{
  "success": true,
  "message": "Operazione completata",
  "data": [ ... ]
}
```

Gli errori restituiscono `"success": false` con un messaggio appropriato e il codice HTTP corrispondente (400, 401, 404, 500).

### Libreria icone — `icons/icons.js`

Esporta costanti stringa SVG usate per gli indicatori di stato e le icone di navigazione:

```javascript
ICONS.dbOk        // pallino verde — database connesso
ICONS.dbLoading   // indicatore rotante — connessione in corso
ICONS.dbErr       // pallino rosso — connessione fallita
ICONS.chevronLeft  // freccia paginazione
ICONS.chevronRight // freccia paginazione
```

---

## Sistema dei temi

PCTOConnect include tre temi visivi completi. Il tema attivo è salvato in `localStorage` e applicato prima del primo render del browser — nessun "flash" di contenuto non stilizzato.

### Temi disponibili

| Nome | File | Palette | Carattere |
|---|---|---|---|
| **Classic** | `global.css` | Barra laterale blu navy, accento verde | Pulito, professionale |
| **Aurora** | `global2.css` | Barra laterale petrolio scuro, accento teal, sfondo perla | Moderno, orientato alla chiarezza |
| **Brutale** | `global3.css` | Barra laterale crema/sabbia, accento vermillion, bordi netti | Deciso, ispirato alla Olivetti Valentine |

### Come cambiare tema

Nella barra laterale, sotto la sezione **Tema**, sono sempre visibili tre pulsanti: **Classic**, **Aurora**, **Brutale**. Cliccandone uno:
1. La scelta viene salvata in `localStorage` (chiave: `fsl-theme`)
2. L'attributo `href` del tag `<link id="theme-link">` viene sostituito con il nuovo file CSS
3. Lo stato attivo sui pulsanti del tema viene aggiornato

La scelta viene ricordata tra sessioni e navigazioni tra pagine. Il cambio avviene in meno di un frame — nessun ricaricamento necessario.

### Prevenzione del FOUC

`theme.js` viene caricato in modo sincrono nel `<head>`, immediatamente dopo il tag `<link>`. Legge `localStorage` e corregge l'`href` del CSS prima che il browser renderizzi qualsiasi cosa. Questo elimina il "flash bianco" che si verificherebbe se il cambio tema avvenisse dopo il caricamento della pagina.

---

## Autenticazione

Tutte le pagine richiedono una sessione di login valida. Il flusso è:

1. L'utente visita qualsiasi pagina → PHP chiama `requireLoginPage()` → reindirizza a `login.php` se non autenticato.
2. L'utente invia username e password su `login.php`.
3. PHP interroga la tabella `UTENTI` e verifica la password con `password_verify()`.
4. In caso di successo: `session_regenerate_id(true)` (previene il session fixation), variabili di sessione impostate, redirect a `index.php`.
5. `logout.php` cancella tutti i dati di sessione, distrugge il file di sessione e scade il cookie di sessione.

**Conservazione delle password:** bcrypt tramite `password_hash($password, PASSWORD_DEFAULT)`. Il cost factor è il default corrente di PHP (10+). Gli account legacy con password in chiaro vengono rilevati e verificati tramite `hash_equals()` — è consigliata una migrazione a bcrypt per uso in produzione.

---

## Riferimento API

### Aziende

| Metodo | URL | Azione |
|---|---|---|
| `GET` | `/Gruppo1/aziende_middleware.php` | Elenco tutte le aziende |
| `GET` | `/Gruppo1/aziende_middleware.php?id=N` | Dettaglio singola azienda |
| `POST` | `/Gruppo1/aziende_middleware.php` | Crea azienda |
| `PUT` | `/Gruppo1/aziende_middleware.php?id=N` | Modifica azienda |
| `DELETE` | `/Gruppo1/aziende_middleware.php?id=N` | Elimina azienda |

### Disponibilità

| Metodo | URL | Azione |
|---|---|---|
| `GET` | `/Gruppo 2/middlewere.php` | Elenco tutti gli slot |
| `GET` | `/Gruppo 2/middlewere.php?id=N` | Dettaglio singolo slot |
| `POST` | `/Gruppo 2/middlewere.php` | Crea slot |
| `PUT` | `/Gruppo 2/middlewere.php?id=N` | Modifica slot |
| `DELETE` | `/Gruppo 2/middlewere.php?id=N` | Elimina slot |

### Esperienze

| Metodo | URL | Azione |
|---|---|---|
| `GET` | `/Gruppo 3/api_esperienze.php` | Elenco tutte le esperienze |
| `GET` | `/Gruppo 3/api_esperienze.php?id=N` | Dettaglio singola esperienza |
| `POST` | `/Gruppo 3/api_esperienze.php` | Crea esperienza |
| `PUT` | `/Gruppo 3/api_esperienze.php?id=N` | Modifica esperienza |
| `DELETE` | `/Gruppo 3/api_esperienze.php?id=N` | Elimina esperienza |
| `GET` | `/Gruppo 3/api_esperienze.php?resource=tutor_scolastico` | Elenco tutor scolastici (per menu a tendina) |
| `GET` | `/Gruppo 3/api_esperienze.php?resource=tutor_aziendale` | Elenco tutor aziendali (per menu a tendina) |
| `GET` | `/Gruppo 3/api_esperienze.php?resource=disponibilita` | Elenco disponibilità (per menu a tendina) |

### Personale — tramite `api.php` unificato

| Metodo | URL | Azione |
|---|---|---|
| `GET` | `/api.php?entity=tutor_scolastici` | Elenco tutor scolastici |
| `POST` | `/api.php?entity=tutor_scolastici` | Crea tutor scolastico |
| `PUT` | `/api.php?entity=tutor_scolastici&id=N` | Modifica tutor scolastico |
| `DELETE` | `/api.php?entity=tutor_scolastici&id=N` | Elimina tutor scolastico |
| `GET` | `/api.php?entity=tutor_aziendali` | Elenco tutor aziendali |
| `POST` | `/api.php?entity=tutor_aziendali` | Crea tutor aziendale |
| `PUT` | `/api.php?entity=tutor_aziendali&id=N` | Modifica tutor aziendale |
| `DELETE` | `/api.php?entity=tutor_aziendali&id=N` | Elimina tutor aziendale |

---

## Installazione

### Prerequisiti

| Requisito | Versione |
|---|---|
| PHP | 8.1 o superiore |
| MySQL / MariaDB | 10.4 o superiore |
| Estensione PDO | Abilitata in `php.ini` |
| Driver PDO MySQL | `pdo_mysql` abilitato |
| Server web | Apache, Nginx, o server built-in di PHP |

### Configurazione passo per passo

**1. Clona o scarica il repository**

```bash
git clone https://github.com/massimof79/5AINC_FSL.git
cd 5AINC_FSL
```

**2. Crea il database**

Apri il tuo client MySQL (phpMyAdmin, TablePlus, DBeaver o la riga di comando) ed esegui:

```sql
CREATE DATABASE `5AINC_FSL` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `5AINC_FSL`;
SOURCE 5ainc_fsl.sql;
```

Oppure da terminale:

```bash
mysql -u root -p < 5ainc_fsl.sql
```

Questo crea tutte le 8 tabelle, la vista e carica i dati di esempio (inclusi due account utente di test).

**3. Configura la connessione al database**

Apri `config.php` e aggiorna le costanti in base al tuo ambiente:

```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');       // modifica se MySQL usa una porta diversa
define('DB_NAME', '5AINC_FSL');
define('DB_USER', 'root');       // il tuo username MySQL
define('DB_PASS', '');           // la tua password MySQL
```

> **Importante:** Non committare `config.php` con credenziali reali in un repository pubblico.

**4. Avvia un server web**

Opzione A — Server built-in di PHP (solo sviluppo):

```bash
php -S localhost:8000
```

Poi apri [http://localhost:8000](http://localhost:8000) nel browser.

Opzione B — Apache / XAMPP / WAMP / MAMP:

Posiziona la cartella del progetto dentro `htdocs` (XAMPP) o `www` (WAMP/MAMP) e naviga verso `http://localhost/5AINC_FSL/`.

Opzione C — Nginx:

Configura un server block che punta alla directory del progetto con PHP-FPM.

**5. Accedi all'applicazione**

Naviga su `http://localhost:8000/login.php` (o l'equivalente nel tuo setup).

Account predefiniti (dai dati di esempio):

| Username | Password |
|---|---|
| `miccia` | *(chiedere al team)* |
| `michele` | *(chiedere al team)* |

Al login riuscito verrai reindirizzato alla dashboard (`index.php`).

---

## Configurazione

### `config.php` — Database

```php
define('DB_HOST', '127.0.0.1');  // Host del database
define('DB_PORT', '3306');        // Porta (MySQL default: 3306)
define('DB_NAME', '5AINC_FSL');  // Nome del database
define('DB_USER', 'root');        // Utente MySQL
define('DB_PASS', '');            // Password MySQL
```

### Impostazioni sessione PHP (consigliate in `php.ini`)

```ini
session.use_cookies = 1
session.use_only_cookies = 1
session.cookie_httponly = 1
session.cookie_samesite = Lax
```

Queste impostazioni prevengono il session hijacking tramite JavaScript e il cross-site request forgery.

### Tema predefinito (`theme.js`)

```javascript
var DEFAULT = 'global2.css';   // Il tema Aurora è il predefinito
```

Cambia in `'global.css'` (Classic) o `'global3.css'` (Brutale) per impostare un tema diverso come predefinito dell'applicazione.

---

## Utilizzo

### Navigazione nell'applicazione

Dopo il login, la **dashboard** (`index.php`) mostra sei card cliccabili — una per ogni modulo. La barra laterale a sinistra fornisce navigazione persistente tra tutte le sezioni.

**Sezioni della barra laterale:**
- **Gestione** — Aziende, Disponibilità, Esperienze
- **Personale** — Tutor Scolastici, Tutor Aziendali, Studenti
- **Tema** — Cambia tra Classic, Aurora e Brutale
- **Sistema** — Logout

### Lavorare con i record

Ogni pagina modulo segue lo stesso schema di interazione:

1. **Visualizza** — I record si caricano automaticamente nella tabella all'apertura della pagina.
2. **Cerca** — Digita nella casella di ricerca per filtrare le righe in tempo reale (nessun ricaricamento).
3. **Pagina** — Usa i pulsanti ← → per navigare tra le pagine dei risultati.
4. **Crea** — Clicca **+ Nuovo [tipo record]** per aprire una modale con il form. Compila i campi e invia.
5. **Modifica** — Clicca il pulsante modifica (matita) su qualsiasi riga. La stessa modale si apre pre-compilata con i dati esistenti.
6. **Elimina** — Clicca il pulsante elimina (cestino). Appare una finestra di conferma prima che venga eliminato qualsiasi dato.

Tutte le operazioni mostrano una **notifica toast** nell'angolo in alto a destra che conferma il successo o segnala un errore.

### Utilizzo su dispositivi mobili

Su schermi più stretti di 900 px (tablet e smartphone), la barra laterale si nasconde automaticamente. Un pulsante a menu hamburger appare nell'angolo in alto a sinistra per mostrarla o nasconderla. Il resto del layout si adatta a colonna singola.

---

## Team

| Gruppo | Responsabilità | Componenti |
|---|---|---|
| **Gruppo 1** | Modulo aziende, connessione database (`config.php`) | — |
| **Gruppo 2** | Modulo disponibilità, API unificata (`api.php`) | — |
| **Gruppo 3** | Modulo esperienze, sistema di design globale (`global.css`) | — |
| **Gruppo 4** | Modulo personale (tutor, studenti), gestione sessione | — |

**Classe:** 5AINC  
**Anno scolastico:** 2025/2026  
**Sistema di design:** Claude (Anthropic) — [claude.ai/code](https://claude.ai/code)

---

*Progetto scolastico — Classe 5AINC — Anno scolastico 2025/2026*
