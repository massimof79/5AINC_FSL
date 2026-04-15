# Documentazione tecnica — Gruppo 3
## Progetto PCTOConnect — 5AINC\_FSL

---

## Indice

1. [Contesto del progetto](#1-contesto-del-progetto)
2. [Architettura generale](#2-architettura-generale)
3. [Sistema di design — global.css](#3-sistema-di-design--globalcss)
4. [Interfaccia utente — esperienze.html](#4-interfaccia-utente--esperienzhtml)
5. [Logica client-side — esperienze.js](#5-logica-client-side--esperienzejs)
6. [API server-side — api_esperienze.php](#6-api-server-side--api_esperienezphp)
7. [Sicurezza](#7-sicurezza)
8. [Flusso di una operazione CRUD](#8-flusso-di-una-operazione-crud)
9. [Scelte progettuali](#9-scelte-progettuali)

---

## 1. Contesto del progetto

Il progetto PCTOConnect è un'applicazione web per la gestione amministrativa delle esperienze PCTO (Percorsi per le Competenze Trasversali e per l'Orientamento), sviluppata dalla classe 5AINC come esercitazione pratica su architetture web a tre livelli.

La classe è divisa in quattro gruppi, ognuno responsabile di un sottoinsieme delle tabelle del database. Tutti i gruppi condividono lo stesso database, la stessa sessione PHP e lo stesso foglio di stile globale. Il Gruppo 3 ha prodotto:

- il sistema di design condiviso (`global.css`)
- la pagina di gestione delle esperienze PCTO (`esperienze.html`, `esperienze.js`)
- la REST API per la tabella `ESPERIENZA` (`api_esperienze.php`)

---

## 2. Architettura generale

L'applicazione segue il pattern classico a tre livelli:

```
[Browser]
    |
    | HTTP (JSON)
    v
[PHP — api_esperienze.php]
    |
    | PDO
    v
[MySQL — tabella ESPERIENZA]
```

Ogni pagina HTML è indipendente e comunica esclusivamente con la propria API tramite Fetch API. Non esiste un framework front-end: tutto è scritto in JavaScript nativo (ES2020+). Sul lato server, PHP gestisce il routing tramite il metodo HTTP e i parametri GET.

La sessione utente è centralizzata: ogni richiesta all'API viene respinta con HTTP 401 se la sessione non è attiva, e il client reindirizza automaticamente al login.

---

## 3. Sistema di design — global.css

Il file `global.css` definisce il contratto visivo dell'intera applicazione. Tutti gli altri gruppi lo includono e possono estenderlo senza modificarlo.

### 3.1 Variabili CSS (custom properties)

Tutte le costanti visive sono dichiarate in `:root`. Questo permette a qualsiasi gruppo di usare i valori corretti senza duplicarli o memorizzarli.

**Colori brand:**

| Variabile | Valore | Uso |
|---|---|---|
| `--primary` | `#2c3e50` | Sidebar, intestazioni tabelle, titoli |
| `--accent` | `#27ae60` | Pulsanti primari, focus, active nav |
| `--danger` | `#c0392b` | Eliminazione, errori |
| `--warning` | `#f39c12` | Modifica, avvisi |
| `--info` | `#2980b9` | Link, informazioni |

**Alias semantici** (pensati per gli altri gruppi):

| Variabile | Descrizione |
|---|---|
| `--success` | Alias di `--accent`, per stati positivi |
| `--bg-light` | Sfondo della pagina |
| `--bg-card` | Sfondo di card e modal |
| `--text` | Testo principale |
| `--text-muted` | Testo secondario, placeholder |
| `--border` | Bordi di separazione |

**Layout:**

| Variabile | Valore | Uso |
|---|---|---|
| `--sidebar-w` | `260px` | Larghezza sidebar fissa |
| `--topbar-h` | `60px` | Altezza topbar fissa |
| `--radius` | `8px` | Raggio standard |
| `--radius-lg` | `14px` | Raggio per card e modal |

### 3.2 Layout principale

Il layout usa CSS Flexbox sull'elemento `.app-shell` che occupa l'intera finestra. La sidebar è `position: fixed` a sinistra, la topbar è `position: fixed` in cima, e `.main-content` ha margini che compensano esattamente le dimensioni fisse dei due elementi.

```
+------------------+-----------------------------+
|                  | topbar (fixed, 60px)        |
|  sidebar         |-----------------------------|
|  (fixed, 260px)  | main-content                |
|                  | (margin-left: 260px,        |
|                  |  margin-top: 60px)          |
+------------------+-----------------------------+
```

Su schermi sotto i 900px la sidebar si trasforma in un pannello a scomparsa: viene spostata fuori dalla viewport con `transform: translateX(-100%)` e riportata con la classe `.is-open` attivata dal pulsante hamburger in JavaScript.

### 3.3 Componenti condivisi

Il file definisce i seguenti componenti pronti all'uso per tutti i gruppi:

**Pulsanti (`.btn`):** cinque varianti (`btn-primary`, `btn-secondary`, `btn-danger`, `btn-warning`, `btn-info`) con tre dimensioni (`btn-sm`, base, `btn-lg`) e variante icona (`btn-icon`). Tutti hanno transizioni, stato `:focus-visible` con outline per accessibilità, e stato `:disabled`.

**Tabelle:** la classe `.table-wrapper` abilita lo scroll orizzontale su mobile. Le intestazioni usano `--primary` come sfondo. Le righe hanno striping alternato e highlight al passaggio del cursore. La classe `.table-empty` gestisce lo stato vuoto con testo centrato.

**Form:** `.form-group`, `.form-label`, `.form-control`, `.form-error`. I campi obbligatori mostrano un asterisco rosso tramite la classe `.required`. La classe `.is-invalid` su un controllo aggiunge bordo rosso e box-shadow rossa per il feedback di validazione. I `<select>` hanno `cursor: pointer` e le `<textarea>` sono redimensionabili verticalmente.

**Modal:** l'overlay usa `opacity` e `visibility` per la transizione, `backdrop-filter: blur(3px)` per l'effetto sfumato. Il modal stesso scivola verso l'alto con `translateY(20px) → translateY(0)`. Header e footer del modal sono `position: sticky` per restare visibili durante lo scroll del contenuto.

**Toast notifications:** il container è posizionato in alto a destra, sopra la topbar. Ogni toast entra da destra con `translateX(30px)` e sparisce nella stessa direzione. I colori del bordo sinistro identificano il tipo (success, danger, warning, info).

**Badge di stato:** la classe `.badge-status` con modificatori `.pending`, `.approved`, `.rejected`, `.active` è pensata per visualizzare stati delle candidature, ma può essere usata da qualsiasi gruppo.

**Classi utility:** il file include un set minimale di utility per margini (`mt-1/2/3`, `mb-1/2/3`), testo (`text-muted`, `text-danger`, `text-center`, `fw-bold`) e flex (`flex`, `flex-center`, `flex-wrap`).

---

## 4. Interfaccia utente — esperienze.html

### 4.1 Struttura dell'app shell

La pagina segue la struttura definita dal CSS:

```html
<div class="app-shell">
  <aside class="sidebar">...</aside>
  <header class="topbar">...</header>
  <main class="main-content">...</main>
</div>
```

La sidebar contiene tre sezioni di navigazione (Gestione, Studenti, Sistema) con le voci di tutte le pagine del progetto. Questo permette all'utente di navigare tra le sezioni degli altri gruppi senza cambiare l'impostazione grafica. La voce attiva riceve la classe `.active` che aggiunge l'indicatore verde sul bordo sinistro.

La topbar mostra il titolo della sezione corrente, un badge con iniziale e nome dell'utente in sessione, e il link di logout. Il badge utente è predisposto per essere popolato con dati di sessione PHP (commentato nell'HTML, pronto per l'attivazione).

### 4.2 Area contenuto

L'area principale include:

- Un `page-header` con titolo e descrizione della sezione
- Una `stats-grid` (nascosta di default, attivabile via JS) con tre card statistiche: totale esperienze, studenti coinvolti, ore totali svolte
- Una `card` principale con intestazione (titolo + pulsante "Nuova Esperienza") e la tabella

La tabella ha nove colonne: ID, periodo effettivo, ore previste, ore svolte, numero studenti, tutor scolastico (nome da JOIN), tutor aziendale (nome da JOIN), disponibilità (label da JOIN), azioni (modifica / elimina).

### 4.3 Modal

Il modal è definito fuori dall'app shell, a livello di `body`. Contiene un form con sei campi:

- `periodo_effettivo` — campo testo libero
- `numero_ore_previste` / `numero_ore_svolte` — due numerici affiancati con `.form-row`
- `numero_studenti` — numerico
- `codice_docente` — select popolata dinamicamente dall'API
- `codice_tutor` — select popolata dinamicamente dall'API
- `codice_disponibilita` — select popolata dinamicamente dall'API

Il tasto di submit nel footer del modal cambia testo dinamicamente tra "Crea esperienza" e "Salva modifiche" tramite un `MutationObserver` sul titolo del modal.

### 4.4 Adattamenti responsive in JavaScript

Un piccolo blocco `<script>` inline (non nel `.js` principale per separazione dei concern) gestisce:

- La visibilità del pulsante hamburger in base alla larghezza della finestra, con aggiornamento al resize
- Il toggle della classe `.is-open` sulla sidebar al clic del pulsante
- Il `MutationObserver` per aggiornare il testo del pulsante submit

---

## 5. Logica client-side — esperienze.js

Il file è scritto in JavaScript rigoroso (`'use strict'`) senza dipendenze esterne.

### 5.1 Inizializzazione

Tutto parte dall'evento `DOMContentLoaded`. In questo handler vengono:

1. cachati tutti i riferimenti DOM necessari in variabili di modulo
2. registrati tutti gli event listener (pulsanti, form submit, chiusura modal, tasto Escape, clic sfondo)
3. chiamata `loadEsperienze()` per il caricamento iniziale dei dati

Lo stato della modalità corrente (creazione vs modifica) è gestito dalla variabile `currentEditId`: `null` indica creazione, un numero intero indica la modifica di quell'ID.

### 5.2 Il wrapper apiFetch

Tutta la comunicazione con il server passa per la funzione `apiFetch(url, options)`, che:

- aggiunge automaticamente l'header `Content-Type: application/json`
- include le credenziali di sessione (`credentials: 'same-origin'`)
- serializza il body in JSON se non è già una stringa
- lancia un'eccezione di tipo `ApiError` (classe custom) se la risposta non è `ok` o `success` è `false`
- restituisce direttamente l'oggetto JSON parsato

Questo centralizza la gestione degli errori HTTP: ogni chiamata che fallisce genera un `ApiError` con il messaggio del server e il codice HTTP, catturato nei `try/catch` delle funzioni chiamanti.

### 5.3 Caricamento e rendering della tabella

`loadEsperienze()` è `async`. Mostra lo spinner, chiama l'API, poi chiama `renderTable(rows)` che costruisce il markup HTML della `<tbody>` con `Array.map().join('')`. Ogni cella testuale passa per la funzione `escHtml()` per prevenire XSS.

Le colonne dei nomi (tutor scolastico, tutor aziendale, disponibilità) usano il valore `?? '—'` per mostrare un trattino quando il JOIN non ha prodotto risultati (record orfano).

### 5.4 Gestione del modal

`apriModal(id)` è `async` e gestisce sia la creazione (`id = null`) sia la modifica (`id = numero`). Il flusso è:

1. imposta `currentEditId` e il titolo del modal
2. resetta il form e pulisce gli errori
3. chiama in parallelo (con `Promise.all`) i tre endpoint ausiliari per popolare le select
4. se è una modifica, carica i dati dell'esperienza e precompila il form con `fillForm(data)`
5. apre il modal aggiungendo la classe `.is-open`
6. porta il focus sul primo campo

`chiudiModal()` rimuove la classe `.is-open` e resetta lo stato.

### 5.5 Submit del form

`handleFormSubmit(e)` previene il submit nativo, costruisce il payload con `buildPayload()` (che legge i valori dal form e converte i campi numerici con `Number()`), disabilita il pulsante per evitare doppi invii, e chiama l'API appropriata (POST o PUT) in base a `currentEditId`.

In caso di errore HTTP 422 (validazione fallita lato server) mostra il messaggio di errore con un toast. In caso di 401 avvisa che la sessione è scaduta. Negli altri casi usa `handleError()`.

### 5.6 Eliminazione

`eliminaEsperienza(id)` chiede conferma con `confirm()` nativo prima di procedere. Dopo l'eliminazione ricarica la tabella.

### 5.7 Toast notifications

`showToast(type, title, message, duration)` crea un elemento DOM, lo appende al container, e programma la sua rimozione dopo `duration` ms. Il clic sul toast lo chiude anticipatamente. La classe `.toast-hide` attiva l'animazione di uscita; la rimozione dal DOM avviene sull'evento `animationend`.

### 5.8 escHtml — protezione XSS

La funzione `escHtml(str)` trasforma i caratteri `&`, `<`, `>`, `"`, `'` nelle rispettive entità HTML. Viene applicata a ogni stringa proveniente dal server prima di inserirla nel DOM tramite `innerHTML`. Questo garantisce che dati potenzialmente malevoli non vengano interpretati come markup.

---

## 6. API server-side — api\_esperienze.php

### 6.1 Struttura del file

Il file segue questo ordine:

1. dichiarazioni e header HTTP
2. helper `respond()` per tutte le risposte
3. verifica sessione
4. inclusione di `config.php` (che espone `$pdo`)
5. router basato su `$_SERVER['REQUEST_METHOD']` e `$_GET['id']`
6. funzioni per le risorse ausiliarie
7. funzioni CRUD
8. helper `parseRequestBody()` e `validateFields()`

### 6.2 Il router

Il router usa l'istruzione `match` di PHP 8. Se è presente il parametro `?resource=`, viene gestito prima da `handleResource()` che termina sempre con `respond()` → `exit`. Altrimenti si entra nel `match` sul metodo HTTP.

```php
match ($method) {
    'GET'    => $id ? getEsperienza($pdo, $id) : listEsperienze($pdo),
    'POST'   => createEsperienza($pdo),
    'PUT'    => $id ? updateEsperienza($pdo, $id) : respond(false, ...),
    'DELETE' => $id ? deleteEsperienza($pdo, $id) : respond(false, ...),
    default  => respond(false, null, 'Metodo non supportato.', 405),
};
```

### 6.3 Formato della risposta

Ogni risposta ha sempre la stessa struttura:

```json
{
  "success": true | false,
  "message": "testo descrittivo",
  "data": { ... } | [ ... ] | null
}
```

Il codice HTTP è coerente con lo stato: 200 per letture, 201 per creazione, 400 per richieste malformate, 401 per sessione assente, 404 per record non trovati, 405 per metodi non supportati, 422 per errori di validazione, 500 per errori interni.

### 6.4 listEsperienze — query con JOIN

La query di lista usa tre LEFT JOIN per recuperare i nomi dei tutor e la label della disponibilità in un'unica chiamata al database, evitando query multiple o logica di ricomposizione nel client.

```sql
SELECT
    e.*,
    CONCAT(ts.nome, ' ', ts.cognome) AS nome_tutor_scolastico,
    CONCAT(ta.nome, ' ', ta.cognome) AS nome_tutor_aziendale,
    CONCAT(d.data_inizio, ' → ', d.data_fine) AS label_disponibilita
FROM ESPERIENZA e
LEFT JOIN TUTOR_SCOLASTICO ts ON ts.codice_docente = e.codice_docente
LEFT JOIN TUTOR_AZIENDALE  ta ON ta.codice_tutor   = e.codice_tutor
LEFT JOIN DISPONIBILITA     d ON d.codice_disponibilita = e.codice_disponibilita
ORDER BY e.codice_esperienza DESC
```

`LEFT JOIN` (invece di `INNER JOIN`) garantisce che anche le esperienze con FK orfane vengano restituite, mostrando `null` nelle colonne derivate (il client le sostituisce con `—`).

### 6.5 Validazione lato server (validateFields)

La funzione `validateFields()` controlla che tutti i campi obbligatori siano presenti e corretti. I valori numerici vengono castati esplicitamente con `(int)`. Se ci sono errori, risponde immediatamente con HTTP 422 e un array di messaggi di errore nel campo `data.errors`.

Questa validazione è separata da quella client-side (che è solo per UX): la validazione server-side è quella che garantisce l'integrità dei dati.

### 6.6 Risorse ausiliarie

I tre endpoint `?resource=tutor_scolastico`, `?resource=tutor_aziendale`, `?resource=disponibilita` restituiscono array nella forma `[{id, label}]`, standardizzata per semplificare la funzione `populateSelect()` nel client. La lista delle risorse permesse è un array `$allowed` che viene verificato prima di eseguire qualsiasi query.

---

## 7. Sicurezza

### Autenticazione

Ogni richiesta all'API verifica `$_SESSION['user_id']`. Se assente, risponde con 401 prima di qualsiasi altra operazione. Il client intercetta il 401 e reindirizza al login dopo tre secondi.

### SQL Injection

Tutte le query che accettano input esterno usano PDO prepared statements con parametri nominali (`:id`, `:periodo`, ecc.). Nessuna query è costruita per concatenazione di stringhe.

### XSS

Sul client, ogni valore proveniente dall'API viene passato per `escHtml()` prima di essere inserito nel DOM via `innerHTML`. I valori inseriti tramite `.value` o `.textContent` sono nativamente sicuri.

### Enumerazione risorse

La funzione `handleResource()` verifica che il parametro `resource` appartenga a un insieme esplicito di valori permessi. Valori non in lista producono HTTP 400 senza eseguire query.

### Informazioni di errore

In caso di `PDOException`, viene eseguito `error_log()` ma il messaggio dell'eccezione non viene esposto al client. Il client riceve solo "Errore del database. Riprova più tardi."

---

## 8. Flusso di una operazione CRUD

### Creazione di una nuova esperienza

```
Utente clicca "+ Nuova Esperienza"
    |
    v
apriModal(null)
    |
    +-- populateSelect x3 [GET ?resource=...]
    |       Server risponde con liste {id, label}
    |       Le <select> vengono popolate
    |
    +-- modal si apre, focus sul primo campo
    |
Utente compila il form e clicca "Crea esperienza"
    |
    v
handleFormSubmit(e)
    |
    +-- buildPayload() legge i valori del form
    |
    +-- apiFetch(API_URL, {method: 'POST', body: payload})
    |       [POST /api_esperienze.php]
    |       Server: parseRequestBody() → validateFields() → INSERT
    |       Risposta: {success: true, data: {codice_esperienza: N}, ...} HTTP 201
    |
    +-- showToast('success', ...)
    +-- chiudiModal()
    +-- loadEsperienze()  ← ricarica la tabella aggiornata
```

### Modifica di un'esperienza esistente

```
Utente clicca il pulsante modifica su una riga
    |
    v
apriModal(id)
    |
    +-- populateSelect x3 [GET ?resource=...]
    |
    +-- apiFetch(API_URL?id=N) [GET singolo]
    |       Server: SELECT con JOIN → risposta JSON
    |
    +-- fillForm(data) precompila tutti i campi
    +-- modal si apre
    |
Utente modifica e salva
    |
    v
handleFormSubmit(e)
    |
    +-- apiFetch(API_URL?id=N, {method: 'PUT', body: payload})
    |       Server: verifica esistenza → validateFields() → UPDATE
    |       Risposta: HTTP 200
    |
    +-- showToast, chiudiModal, loadEsperienze
```

---

## 9. Scelte progettuali

**Nessun framework front-end.** La scelta di usare JavaScript nativo permette a tutta la classe di leggere e capire il codice senza conoscere Vue, React o simili. Il costo è una maggiore verbosità, ma il guadagno in chiarezza è significativo per un progetto didattico.

**Separazione dei file per gruppo.** Ogni gruppo ha la propria API PHP indipendente. Questo evita conflitti di merge e rende il codice di ciascun gruppo autonomamente leggibile e testabile.

**global.css come contratto condiviso.** Invece di lasciare ogni gruppo libero di scrivere stili incompatibili, il Gruppo 3 ha definito un sistema di design con variabili CSS. Tutti i componenti riutilizzabili (card, tabelle, form, modal, toast) sono centralizzati. I singoli gruppi estendono, non sovrascrivono.

**Risposta JSON strutturata uniforme.** Il formato `{success, message, data}` è uguale per tutte le API di tutti i gruppi. Questo rende più semplice scrivere funzioni client riutilizzabili e interpretare le risposte in modo uniforme.

**LEFT JOIN invece di query multiple.** La lista delle esperienze recupera i dati correlati (nomi tutor, label disponibilità) in una singola query con JOIN, invece di fare N query aggiuntive dopo aver ottenuto la lista. Questo riduce il numero di round-trip al database.

**Validazione duale.** La validazione avviene sia nel client (per UX immediata) che nel server (per integrità dei dati). Le due validazioni sono indipendenti: il client non si fida della propria validazione per decidere se inviare la richiesta, e il server non si fida del client per decidere se accettare i dati.

---

*Gruppo 3 — 5AINC — Anno scolastico 2024/2025*
