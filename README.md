# PCTOConnect — 5AINC\_FSL

Applicazione web per la gestione delle esperienze di alternanza scuola-lavoro (PCTO).  
Progetto scolastico della classe **5AINC**, sviluppato in gruppi collaborativi su un database condiviso.

---

## Struttura del progetto

Il progetto è organizzato per gruppi: ogni gruppo è responsabile di una o più tabelle del database e dei file front-end e back-end ad esse relativi. Alcuni file sono condivisi tra tutti i gruppi e mantenuti collaborativamente.

### File condivisi

| File | Responsabile | Descrizione |
|---|---|---|
| `config.php` | **Gruppo 1** | Connessione PDO al database |
| `api.php` | **Gruppo 2** | Api generali |
| `global.css` | **Gruppo 3** | Foglio di stile globale dell'intera applicazione |
| `session.php` | **Gruppo 4** | Avvio e gestione della sessione utente |

### Struttura per gruppi

```
/
├── global.css                  # Gruppo 3 — stile globale condiviso
├── config.php                  # Connessione DB
├── session.php                 # Gestione sessione
│
├── esperienze.html             # Gruppo 3 — pagina CRUD Esperienze
├── esperienze.js               # Gruppo 3 — logica front-end Esperienze
├── api_esperienze.php          # Gruppo 3 — REST API Esperienze
│
├── [pagina_gruppo_1].html      # Gruppo 1 — (aggiungere)
├── [pagina_gruppo_1].js        # Gruppo 1 — (aggiungere)
├── [api_gruppo_1].php          # Gruppo 1 — (aggiungere)
│
├── [pagina_gruppo_2].html      # Gruppo 2 — (aggiungere)
├── [pagina_gruppo_2].js        # Gruppo 2 — (aggiungere)
├── [api_gruppo_2].php          # Gruppo 2 — (aggiungere)
│
└── [pagina_gruppo_4].html      # Gruppo 4 — (aggiungere)
    [pagina_gruppo_4].js        # Gruppo 4 — (aggiungere)
    [api_gruppo_4].php          # Gruppo 4 — (aggiungere)
```

---

## Gruppo 3 — Gestione Esperienze

Il Gruppo 3 è responsabile della tabella `ESPERIENZA` e del foglio di stile globale `global.css`.

### Tabella gestita

La tabella `ESPERIENZA` registra ogni singola esperienza PCTO, collegando disponibilità aziendali, tutor scolastici e tutor aziendali.

| Campo | Tipo | Descrizione |
|---|---|---|
| `codice_esperienza` | INT PK | Identificatore univoco |
| `periodo_effettivo` | VARCHAR | Descrizione testuale del periodo |
| `numero_ore_previste` | INT | Ore pianificate |
| `numero_ore_svolte` | INT | Ore effettivamente svolte |
| `numero_studenti` | INT | Studenti coinvolti |
| `codice_docente` | FK | Riferimento a `TUTOR_SCOLASTICO` |
| `codice_disponibilita` | FK | Riferimento a `DISPONIBILITA` |
| `codice_tutor` | FK | Riferimento a `TUTOR_AZIENDALE` |

### File prodotti

**`global.css`** — foglio di stile condiviso da tutti i gruppi. Definisce il sistema di design dell'applicazione: layout sidebar + topbar, componenti (card, tabelle, form, modal, toast, badge, spinner) e variabili CSS. Ogni gruppo può estenderlo con un proprio file `.css` per le parti specifiche.

**`esperienze.html`** — interfaccia utente per la gestione delle esperienze. Include la sidebar di navigazione comune, la topbar con badge sessione, una tabella delle esperienze con colonne calcolate via JOIN, e un modal per la creazione e modifica dei record.

**`esperienze.js`** — logica client-side. Gestisce tutte le operazioni CRUD tramite Fetch API, la popolazione dinamica delle `<select>` dal server, la validazione dei form, il sistema di toast notifications e la protezione contro XSS.

**`api_esperienze.php`** — REST API server-side. Espone cinque endpoint (GET lista, GET singolo, POST, PUT, DELETE) più tre endpoint ausiliari per popolare le select di tutor scolastici, tutor aziendali e disponibilità. Utilizza PDO con prepared statements, verifica la sessione su ogni richiesta e risponde sempre con JSON strutturato.

### Operazioni CRUD

| Operazione | Metodo HTTP | Endpoint |
|---|---|---|
| Lista tutte le esperienze | `GET` | `/api_esperienze.php` |
| Dettaglio singola | `GET` | `/api_esperienze.php?id=N` |
| Crea nuova esperienza | `POST` | `/api_esperienze.php` |
| Modifica esperienza | `PUT` | `/api_esperienze.php?id=N` |
| Elimina esperienza | `DELETE` | `/api_esperienze.php?id=N` |
| Lista tutor scolastici | `GET` | `/api_esperienze.php?resource=tutor_scolastico` |
| Lista tutor aziendali | `GET` | `/api_esperienze.php?resource=tutor_aziendale` |
| Lista disponibilità | `GET` | `/api_esperienze.php?resource=disponibilita` |

---

## Gruppo 1 — (titolo da aggiungere)

> *Documentazione a cura del Gruppo 1.*

---

## Gruppo 2 — (titolo da aggiungere)

> *Documentazione a cura del Gruppo 2.*

---

## Gruppo 4 — (titolo da aggiungere)

> *Documentazione a cura del Gruppo 4.*

---

## Prerequisiti e installazione

1. Server PHP >= 8.1 con estensione PDO abilitata
2. Database MySQL / MariaDB con le tabelle del progetto
3. Configurare `config.php` con le credenziali del database (file non versionato)
4. Posizionare tutti i file nella root del server web (o in una sottodirectory con percorsi aggiornati)
5. Accedere tramite `login.php` — la sessione è richiesta da tutte le pagine

---

## Tecnologie utilizzate

- PHP 8.1+ con PDO
- HTML5, CSS3 (custom properties, grid, flexbox)
- JavaScript ES2020+ (Fetch API, async/await, MutationObserver)
- Google Fonts — Inter
- MySQL / MariaDB

---

## Note per i gruppi

Il file `global.css` è mantenuto dal Gruppo 3 ed è incluso da tutte le pagine. Se un gruppo ha necessità di stili specifici che non appartengono al sistema globale, deve creare un proprio file `[nome_gruppo].css` e includerlo **dopo** `global.css` nella propria pagina HTML.

Le variabili CSS definite in `:root` (colori, spaziatura, tipografia, raggi, ombre) sono disponibili per tutti e devono essere preferite ai valori hardcoded.

---

*Progetto scolastico — Classe 5AINC — Anno scolastico 2025/2026*
