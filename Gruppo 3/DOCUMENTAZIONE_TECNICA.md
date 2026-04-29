# Documentazione tecnica — Gruppo 3
## Progetto PCTOConnect — 5AINC\_FSL

---

## Indice

1. [Contesto del progetto](#1-contesto-del-progetto)
2. [Architettura generale](#2-architettura-generale)
3. [Sistema di design — global.css](#3-sistema-di-design--globalcss)
4. [Autenticazione — auth.php, login.php, register.php, logout.php](#4-autenticazione--authphp-loginphp-registerphp-logoutphp)
5. [Interfaccia utente — esperienze.php](#5-interfaccia-utente--esperienzephp)
6. [Logica client-side — esperienze.js](#6-logica-client-side--esperienzejs)
7. [API server-side — api\_esperienze.php](#7-api-server-side--api_esperienezphp)
8. [Sicurezza](#8-sicurezza)
9. [Flusso di una operazione CRUD](#9-flusso-di-una-operazione-crud)
10. [Scelte progettuali](#10-scelte-progettuali)

---

## 1. Contesto del progetto

Il progetto PCTOConnect è un'applicazione web per la gestione amministrativa delle esperienze FSL (Formazione Scuola Lavoro) ex PCTO, sviluppata dalla classe 5AINC come esercitazione pratica su architetture web a tre livelli.

La classe è divisa in quattro gruppi, ognuno responsabile di un sottoinsieme delle tabelle del database. Tutti i gruppi condividono lo stesso database, la stessa sessione PHP e lo stesso foglio di stile globale. Il Gruppo 3 ha prodotto:

- il sistema di design condiviso (`global.css`)
- il modulo di autenticazione centralizzato (`auth.php`, `login.php`, `register.php`, `logout.php`)
- la pagina PHP di gestione delle esperienze FSL (`esperienze.php`, `esperienze.js`)
- la REST API per la tabella `ESPERIENZA` (`api_esperienze.php`)

---

## 2. Architettura generale

L'applicazione segue il pattern classico a tre livelli, con un layer di autenticazione sessione che precede ogni accesso sia alle pagine che all'API:

```
[Browser]
    |
    | HTTP (GET/POST — pagine PHP)
    v
[auth.php] ←── sessione PHP ($_SESSION)
    |
    | se sessione valida
    v
[esperienze.php]  ←→  [esperienze.js]
                              |
                              | Fetch API (JSON)
                              v
                    [api_esperienze.php]
                              |
                              | PDO
                              v
                    [MySQL — tabella ESPERIENZA]
```

Il flusso di autenticazione è separato dal flusso dati: `auth.php` espone funzioni pure usate da tutte le pagine. Le pagine PHP chiamano `requireLoginPage()` e l'API chiama `requireLoginApi()` — stessa logica, risposta diversa (redirect HTML vs JSON 401).

---

## 3. Sistema di design — global.css

Il file `global.css` definisce il contratto visivo dell'intera applicazione. Tutti i gruppi lo includono con path relativo `../global.css` e possono estenderlo con stili locali senza modificarlo.

### 3.1 Variabili CSS (custom properties)

Tutte le costanti visive sono dichiarate in `:root`.

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

**UI Tokens aggiuntivi:**

| Variabile | Valore |
|---|---|
| `--shadow` | `0 4px 6px rgba(0,0,0,0.08)` |
| `--shadow-lg` | `0 10px 25px rgba(0,0,0,0.12)` |
| `--transition` | `0.2s ease` |
| `--radius` / `--radius-lg` | `8px` / `14px` |

**Tipografia:**

| Variabile | Valore |
|---|---|
| `--font-sans` | `'Inter', system-ui, sans-serif` |
| `--fs-sm` / `--fs-base` / `--fs-lg` / `--fs-xl` | `13px` / `15px` / `18px` / `24px` |

### 3.2 Layout principale

Il layout usa CSS Flexbox sull'elemento `.app-shell`. La sidebar è `position: fixed` a sinistra, la topbar `position: fixed` in cima, `.main-content` compensa con margini esatti.

```
+------------------+-----------------------------+
|                  | topbar (fixed, 60px)        |
|  sidebar         |-----------------------------|
|  (fixed, 260px)  | main-content                |
|                  | (margin-left: 260px,        |
|                  |  margin-top: 60px)          |
+------------------+-----------------------------+
```

Su schermi sotto i 900px la sidebar si trasforma in un pannello a scomparsa: viene spostata fuori dalla viewport con `transform: translateX(-100%)` e riportata con la classe `.is-open` attivata dal pulsante hamburger.

### 3.3 Componenti condivisi

**Pulsanti (`.btn`):** cinque varianti (`btn-primary`, `btn-secondary`, `btn-danger`, `btn-warning`, `btn-info`) con tre dimensioni (`btn-sm`, base, `btn-lg`) e variante icona (`btn-icon`). Tutti hanno transizioni, stato `:focus-visible` con outline e stato `:disabled`.

**Tabelle:** `.table-wrapper` abilita scroll orizzontale su mobile. Intestazioni con sfondo `--primary`, righe con striping alternato e highlight al hover. `.table-empty` per stato vuoto.

**Form:** `.form-group`, `.form-label`, `.form-control`, `.form-error`. Classe `.required` per asterisco obbligatorio. Classe `.is-invalid` per feedback errore (bordo rosso + box-shadow).

**Modal:** overlay con `opacity` + `visibility` per transizione, `backdrop-filter: blur(3px)`. Il modal scivola verso l'alto con `translateY(20px) → translateY(0)`. Header e footer `position: sticky`.

**Toast notifications:** container in alto a destra. Ogni toast entra da destra con `translateX(30px)` e sparisce nella stessa direzione. Bordo sinistro colorato per tipo (success, danger, warning, info).

**Badge di stato:** `.badge-status` con modificatori `.pending`, `.approved`, `.rejected`, `.active`.

**Utility:** margini (`mt-1/2/3`, `mb-1/2/3`), testo (`text-muted`, `text-danger`, `text-center`, `fw-bold`), flex (`flex`, `flex-center`, `flex-wrap`).

---

## 4. Autenticazione — auth.php, login.php, register.php, logout.php

### 4.1 auth.php — modulo centralizzato (Gruppo 4)

`auth.php` è il contratto condiviso tra tutti i gruppi per la gestione della sessione. Viene incluso con `require_once __DIR__ . '/auth.php'` all'inizio di ogni pagina e dell'API. Avvia la sessione (se non già attiva) e definisce le seguenti funzioni:

| Funzione | Firma | Descrizione |
|---|---|---|
| `isLoggedIn()` | `(): bool` | Verifica che `$_SESSION['user_id']` sia non vuoto |
| `requireLoginPage()` | `(): void` | Redirect a `login.php` se non autenticato |
| `requireLoginApi()` | `(): void` | Risponde con JSON 401 se non autenticato |
| `loginUser()` | `(array $user): void` | Rigenera session ID, scrive `user_id` e `username` in sessione |
| `logoutUser()` | `(): void` | Svuota `$_SESSION`, cancella il cookie, distrugge la sessione |
| `verifyUserPassword()` | `(string, string): bool` | `password_verify()` con fallback plaintext per dati legacy |

```php
// Uso tipo in una pagina PHP
require_once __DIR__ . '/auth.php';
requireLoginPage();   // redirect automatico se non loggato

// Uso tipo in un'API
require_once __DIR__ . '/auth.php';
requireLoginApi();    // risponde {success:false, HTTP 401} se non loggato
```

La sessione scrive due valori: `$_SESSION['user_id']` (intero) e `$_SESSION['username']` (stringa). Il primo viene usato per verificare l'autenticazione, il secondo per visualizzare il nome utente nella topbar.

### 4.2 login.php

Pagina di accesso. Se la sessione è già attiva, reindirizza immediatamente a `esperienze.php`.

**Flusso POST:**
1. Legge `username` e `password` dal body del form
2. Recupera l'utente dalla tabella `utenti` con prepared statement
3. Chiama `verifyUserPassword($password, $user['password'])`
4. Se valido: chiama `loginUser($user)` → redirect a `esperienze.php`
5. Se non valido: mostra messaggio di errore nella pagina

```php
$stmt = $pdo->prepare(
    'SELECT ID, username, password FROM utenti WHERE username = :username LIMIT 1'
);
$stmt->execute([':username' => $username]);
$user = $stmt->fetch();

if ($user && verifyUserPassword($password, (string) $user['password'])) {
    loginUser($user);
    header('Location: esperienze.php');
    exit;
}
```

La pagina gestisce anche i parametri GET `?logout=1` e `?registered=1` per mostrare messaggi di conferma dopo logout o registrazione completata.

### 4.3 register.php

Pagina di registrazione. Reindirizza a `esperienze.php` se già autenticato.

**Validazione:**
- Tutti i campi obbligatori (username, password, conferma password)
- Username: minimo 3 caratteri
- Password: minimo 6 caratteri
- Le due password devono coincidere
- Username non già in uso (query di controllo preventiva)

**Creazione account:**

```php
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare(
    'INSERT INTO utenti (username, password) VALUES (:username, :password)'
);
$stmt->execute([':username' => $username, ':password' => $hash]);
header('Location: login.php?registered=1');
```

### 4.4 logout.php

Chiama `logoutUser()` e reindirizza a `login.php?logout=1`. Non produce output HTML.

```php
require_once __DIR__ . '/auth.php';
logoutUser();
header('Location: login.php?logout=1');
exit;
```

---

## 5. Interfaccia utente — esperienze.php

La pagina è ora un file PHP (non più HTML statico) per integrare la verifica di sessione e mostrare dati dell'utente autenticato nella topbar.

### 5.1 Verifica sessione e dati utente

Il file inizia con il blocco PHP che verifica la sessione prima di emettere qualsiasi output HTML:

```php
<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
requireLoginPage();
?>
```

La topbar mostra l'iniziale e il nome dell'utente prelevati direttamente da `$_SESSION`:

```php
<div class="avatar">
  <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?>
</div>
<span><?= htmlspecialchars($_SESSION['username'] ?? 'Utente', ENT_QUOTES, 'UTF-8') ?></span>
```

### 5.2 Struttura dell'app shell

```html
<div class="app-shell">
  <aside class="sidebar" id="sidebar">...</aside>
  <header class="topbar">...</header>
  <main class="main-content">...</main>
</div>
```

La sidebar contiene sezioni di navigazione (Gestione, Studenti, Sistema) con le voci di tutte le pagine del progetto. La voce attiva riceve la classe `.active`.

### 5.3 Area contenuto

L'area principale include:
- Un `page-header` con titolo e descrizione della sezione
- Una `card` con intestazione (titolo + pulsante "＋ Nuova Esperienza") e la tabella
- Uno spinner (`#table-spinner`) visibile durante il caricamento dati

La tabella ha nove colonne: `#`, periodo effettivo, ore previste, ore svolte, studenti, tutor scolastico (JOIN), tutor aziendale (JOIN), data disponibilità (JOIN), azioni.

### 5.4 Modal — struttura del form

Il modal è definito fuori dall'app shell, a livello di `body`. Il `<form>` **avvolge sia `modal-body` che `modal-footer`**, garantendo che il pulsante submit sia sempre fisicamente dentro il form:

```html
<div class="modal-overlay" id="modal-overlay" role="dialog" aria-modal="true">
  <div class="modal">
    <div class="modal-header">...</div>

    <form id="form-esperienza" novalidate>
      <div class="modal-body">
        <!-- tutti i campi input -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="btn-annulla">Annulla</button>
        <button type="submit" id="btn-submit" class="btn btn-primary">
          Crea esperienza
        </button>
      </div>
    </form>

  </div>
</div>
```

> **Nota:** In una versione precedente il `<form>` era solo nel `modal-body` e il pulsante submit nel `modal-footer` usava l'attributo `form="form-esperienza"` per l'associazione cross-element HTML5. Questa struttura causava il mancato firing dell'evento `submit` in alcuni browser/ambienti. La struttura attuale elimina questa dipendenza mettendo il pulsante fisicamente dentro il form.

Il form contiene sei campi:
- `periodo_effettivo` — testo libero
- `numero_ore_previste` / `numero_ore_svolte` — due numerici (`min="0"`) affiancati con `.form-row`
- `numero_studenti` — numerico (`min="1"`)
- `codice_docente` — `<select>` popolata dinamicamente da `?resource=tutor_scolastico`
- `codice_tutor` — `<select>` popolata dinamicamente da `?resource=tutor_aziendale`
- `codice_disponibilita` — `<select>` popolata dinamicamente da `?resource=disponibilita`

### 5.5 Script inline — responsive

Un piccolo `<script>` inline (separato da `esperienze.js`) gestisce solo la sidebar mobile:

```js
function checkMobile() {
    btnToggle.style.display = window.innerWidth <= 900 ? 'inline-flex' : 'none';
    if (window.innerWidth > 900) sidebar.classList.remove('is-open');
}
btnToggle.addEventListener('click', () => sidebar.classList.toggle('is-open'));
window.addEventListener('resize', checkMobile);
```

---

## 6. Logica client-side — esperienze.js

Il file è scritto in JavaScript rigoroso (`'use strict'`) senza dipendenze esterne.

### 6.1 Inizializzazione

Tutto parte dall'evento `DOMContentLoaded`. In questo handler vengono:
1. cachati tutti i riferimenti DOM in variabili di modulo
2. registrati tutti gli event listener (pulsanti, form submit, chiusura modal, Escape, clic sfondo)
3. chiamata `loadEsperienze()` per il caricamento iniziale

Lo stato è gestito da `currentEditId`: `null` = creazione, numero intero = modifica di quell'ID.

### 6.2 Il wrapper apiFetch

```js
async function apiFetch(url, options = {}) {
    const defaults = {
        headers: { 'Content-Type': 'application/json; charset=utf-8' },
        credentials: 'same-origin',
    };
    const merged = { ...defaults, ...options };
    if (merged.body && typeof merged.body !== 'string') {
        merged.body = JSON.stringify(merged.body);
    }
    const response = await fetch(url, merged);
    const json = await response.json();
    if (!response.ok || !json.success) {
        throw new ApiError(json.message || `HTTP ${response.status}`, response.status);
    }
    return json;
}
```

Centralizza headers, credenziali sessione, serializzazione JSON e gestione errori. Ogni fallimento genera un `ApiError` (classe custom con `status`) catturato nei `try/catch` chiamanti.

### 6.3 Caricamento e rendering della tabella

`loadEsperienze()` mostra lo spinner, chiama `GET /api_esperienze.php`, poi `renderTable(rows)` costruisce la `<tbody>` con `Array.map().join('')`. Ogni stringa passa per `escHtml()`. I campi JOIN con valore `null` mostrano `—` via operatore `??`.

### 6.4 Gestione del modal

`apriModal(id)` è `async` e gestisce sia creazione che modifica:

1. imposta `currentEditId` e titolo modal
2. reset form + pulizia errori
3. `Promise.all` sui tre endpoint `?resource=` per popolare le select **in parallelo**
4. se modifica: `GET ?id=N` → `fillForm(data)` precompila i campi con `setField(name, value)`
5. apre il modal con `.is-open`, focus sul primo input

Le select vengono popolate prima di `fillForm()` in modo che i valori selezionati corrispondano alle opzioni già caricate.

### 6.5 Submit del form

```js
async function handleFormSubmit(e) {
    e.preventDefault();
    const editingId = currentEditId;  // snapshot prima di operazioni async
    const payload = buildPayload();

    // disabilita pulsante per evitare doppi invii
    btnSubmit.disabled = true;

    try {
        const result = editingId
            ? await apiFetch(`${API_URL}?id=${editingId}`, { method: 'PUT', body: payload })
            : await apiFetch(API_URL, { method: 'POST', body: payload });

        showToast('success', 'Operazione completata', result.message);
        chiudiModal();
        loadEsperienze();
    } catch (err) {
        if (err.status === 422) showToast('danger', 'Dati non validi', err.message, 6000);
        else if (err.status === 401) { /* redirect login */ }
        else handleError(err, 'Salvataggio');
    } finally {
        btnSubmit.disabled = false;
    }
}
```

`buildPayload()` usa `FormData` per leggere i valori del form. I campi numerici vengono convertiti con `Number()`.

### 6.6 Eliminazione

`eliminaEsperienza(id)` chiede conferma con `confirm()` nativo prima di eseguire `DELETE /api_esperienze.php?id=N`. Ricarica la tabella in caso di successo.

### 6.7 Toast notifications

`showToast(type, title, message, duration)` crea un elemento DOM, lo appende al container `#toast-container`, e programma la rimozione dopo `duration` ms. Il clic anticipa la chiusura. `.toast-hide` attiva l'animazione di uscita; la rimozione dal DOM avviene su `animationend`.

### 6.8 escHtml — protezione XSS

```js
function escHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
```

Applicata a ogni stringa proveniente dal server prima di inserirla via `innerHTML`.

---

## 7. API server-side — api\_esperienze.php

### 7.1 Struttura del file

```
1. require auth.php + config.php
2. header JSON
3. helper respond()
4. requireLoginApi()
5. router ($method, $id, $resource)
6. handleResource() — risorse ausiliarie
7. listEsperienze(), getEsperienza()
8. createEsperienza(), updateEsperienza(), deleteEsperienza()
9. parseRequestBody(), validateFields()
```

### 7.2 Il router

```php
if ($resource !== null) {
    handleResource($pdo, $resource);  // termina sempre con exit
}

match ($method) {
    'GET'    => $id ? getEsperienza($pdo, $id) : listEsperienze($pdo),
    'POST'   => createEsperienza($pdo),
    'PUT'    => $id ? updateEsperienza($pdo, $id) : respond(false, null, 'ID mancante.', 400),
    'DELETE' => $id ? deleteEsperienza($pdo, $id) : respond(false, null, 'ID mancante.', 400),
    default  => respond(false, null, 'Metodo non supportato.', 405),
};
```

### 7.3 Formato della risposta

```json
{
  "success": true | false,
  "message": "testo descrittivo",
  "data": { ... } | [ ... ] | null
}
```

Codici HTTP coerenti: 200 letture, 201 creazione, 400 richiesta malformata, 401 sessione assente, 404 record non trovato, 405 metodo non supportato, 422 validazione fallita, 500 errore interno.

### 7.4 listEsperienze — query con JOIN

```sql
SELECT
    e.codice_esperienza,
    e.periodo_effettivo,
    e.numero_ore_previste,
    e.numero_ore_svolte,
    e.numero_studenti,
    e.codice_docente,
    e.codice_disponibilita,
    e.codice_tutor,
    CONCAT(ts.nome, ' ', ts.cognome) AS nome_tutor_scolastico,
    CONCAT(ta.nome, ' ', ta.cognome) AS nome_tutor_aziendale,
    d.periodo_previsto AS data_disponibilita
FROM ESPERIENZA e
LEFT JOIN TUTOR_SCOLASTICO ts ON ts.codice_docente      = e.codice_docente
LEFT JOIN TUTOR_AZIENDALE  ta ON ta.codice_tutor        = e.codice_tutor
LEFT JOIN DISPONIBILITA     d ON d.codice_disponibilita = e.codice_disponibilita
ORDER BY e.codice_esperienza ASC
```

`LEFT JOIN` garantisce che esperienze con FK orfane vengano comunque restituite (il client mostra `—`). `d.periodo_previsto` è la colonna corretta della tabella `DISPONIBILITA` (non `data_inizio`/`data_fine`).

La funzione include un check preventivo `SHOW TABLES LIKE 'ESPERIENZA'` e gestisce selettivamente i messaggi di errore per `PDOException` (`Base table or view not found`, `Unknown column`) senza esporre il messaggio interno al client.

### 7.5 Risorse ausiliarie — handleResource

I tre endpoint `?resource=tutor_scolastico|tutor_aziendale|disponibilita` restituiscono array `[{id, label}]` per popolare le select del form. La lista `$allowed` viene verificata prima di eseguire qualsiasi query.

```php
'disponibilita' => $pdo->query(
    'SELECT codice_disponibilita AS id,
            CONCAT("Periodo: ", periodo_previsto, " - ", descrizione) AS label
     FROM DISPONIBILITA ORDER BY periodo_previsto DESC'
)->fetchAll(PDO::FETCH_ASSOC),
```

### 7.6 Validazione lato server — validateFields

```php
if ($periodo === '')                              $errors[] = 'periodo_effettivo è obbligatorio.';
if ($orePreviste === null || $orePreviste < 0)   $errors[] = 'numero_ore_previste non valido.';
if ($oreSvolte   === null || $oreSvolte   < 0)   $errors[] = 'numero_ore_svolte non valido.';
if ($studenti    === null || $studenti    < 1)   $errors[] = 'numero_studenti non valido.';
if (!$docente)                                   $errors[] = 'codice_docente è obbligatorio.';
if (!$disponibilita)                             $errors[] = 'codice_disponibilita è obbligatorio.';
if (!$tutor)                                     $errors[] = 'codice_tutor è obbligatorio.';
```

Il check `$studenti < 1` (non `< 0`) rispecchia il vincolo `CHECK (numero_studenti > 0)` del database. In caso di errori, risponde con HTTP 422 e array `data.errors`.

---

## 8. Sicurezza

### Autenticazione e sessione

Ogni richiesta a pagine PHP chiama `requireLoginPage()` (redirect) o `requireLoginApi()` (JSON 401) prima di qualsiasi operazione. Il client intercetta il 401 e reindirizza al login dopo tre secondi.

`loginUser()` chiama `session_regenerate_id(true)` per prevenire session fixation. `logoutUser()` svuota `$_SESSION`, cancella il cookie di sessione e chiama `session_destroy()`.

### Password

Le password sono memorizzate con `password_hash($password, PASSWORD_DEFAULT)` (bcrypt). La verifica usa `password_verify()`. È presente un fallback `hash_equals()` per dati legacy in plaintext, da migrare.

### SQL Injection

Tutte le query che accettano input esterno usano PDO prepared statements con parametri nominali. Nessuna query è costruita per concatenazione di stringhe.

### XSS

Sul client, ogni valore dal server passa per `escHtml()` prima di `innerHTML`. I valori assegnati a `.value` o `.textContent` sono nativamente sicuri. Lato server, i valori PHP in output HTML usano `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.

### Enumerazione risorse

`handleResource()` verifica che il parametro `resource` appartenga all'array `$allowed`. Valori non in lista producono HTTP 400 senza query.

### Informazioni di errore

In caso di `PDOException`, viene eseguito `error_log()` ma il messaggio interno non viene esposto al client. Il client riceve solo messaggi generici.

---

## 9. Flusso di una operazione CRUD

### Login

```
Utente accede a login.php
    |
    +-- se sessione attiva → redirect esperienze.php
    |
Utente compila form e invia POST
    |
    +-- SELECT utente per username (prepared statement)
    +-- verifyUserPassword()
    |       se valido → loginUser() → session_regenerate_id
    |                              → $_SESSION['user_id'], ['username']
    |                              → redirect esperienze.php
    |       se non valido → errore nella pagina
```

### Creazione di una nuova esperienza

```
Utente clicca "＋ Nuova Esperienza"
    |
    v
apriModal(null)
    |
    +-- Promise.all: populateSelect x3 [GET ?resource=...]
    |       Server risponde con array {id, label}
    |       <select> popolate
    |
    +-- modal aperto, focus su primo input
    |
Utente compila il form e clicca "Crea esperienza"
    |
    v
handleFormSubmit(e) → e.preventDefault()
    |
    +-- buildPayload() legge FormData
    +-- apiFetch(API_URL, { method: 'POST', body: payload })
    |       requireLoginApi() → sessione OK
    |       parseRequestBody() → json_decode(php://input)
    |       validateFields() → tutti i campi validi
    |       INSERT INTO ESPERIENZA ...
    |       Risposta: { success: true, data: { codice_esperienza: N } } HTTP 201
    |
    +-- showToast('success', ...)
    +-- chiudiModal()
    +-- loadEsperienze()  ← ricarica tabella aggiornata
```

### Modifica di un'esperienza esistente

```
Utente clicca pulsante modifica su una riga
    |
    v
apriModal(id)
    |
    +-- Promise.all: populateSelect x3
    +-- apiFetch(API_URL?id=N) [GET singolo]
    |       SELECT ... FROM ESPERIENZA WHERE codice_esperienza = :id
    |
    +-- fillForm(data) → setField() per ogni campo
    +-- modal aperto
    |
Utente modifica e clicca "Salva modifiche"
    |
    v
handleFormSubmit(e)
    |
    +-- apiFetch(API_URL?id=N, { method: 'PUT', body: payload })
    |       verifica esistenza record → validateFields() → UPDATE
    |       Risposta: HTTP 200
    |
    +-- showToast, chiudiModal, loadEsperienze
```

---

## 10. Scelte progettuali

**auth.php come contratto condiviso.** Centralizzare tutta la logica di sessione in un unico file evita duplicazione e garantisce che tutti i gruppi usino gli stessi meccanismi (session_regenerate_id, struttura di $_SESSION, verifica password). Modificare il comportamento della sessione richiede un solo punto di intervento.

**Nessun framework front-end.** JavaScript nativo (ES2020+) rende il codice leggibile a tutta la classe senza conoscenza di framework. Il costo è maggiore verbosità, il guadagno è trasparenza per un progetto didattico.

**Form avvolge modal-body e modal-footer.** Il pulsante submit è fisicamente dentro il `<form>`. L'alternativa (attributo HTML5 `form="id"` su un pulsante esterno al form) è valida ma causa comportamenti inattesi in certi browser quando il form non è nel DOM attivo — la struttura avvolgente elimina la dipendenza.

**Promise.all per le select.** Le tre select del modal vengono popolate in parallelo, non in sequenza. Tre richieste seriali aggiungerebbero latenza visibile all'apertura del modal senza nessun beneficio.

**Snapshot di currentEditId nel submit handler.** `handleFormSubmit` cattura `currentEditId` in una variabile locale prima di operazioni async. `chiudiModal()` resetta `currentEditId` a `null` — usare il valore originale nel blocco `finally` (per il testo del pulsante) richiederebbe lo snapshot, altrimenti il testo mostrerebbe sempre "Crea esperienza" anche dopo una modifica.

**global.css come contratto visivo.** Invece di lasciare ogni gruppo libero di scrivere stili incompatibili, il Gruppo 3 definisce il sistema di design con variabili CSS. I componenti riutilizzabili (card, tabelle, form, modal, toast) sono centralizzati. I gruppi estendono, non sovrascrivono.

**LEFT JOIN invece di query multiple.** La lista esperienze recupera nomi tutor e label disponibilità in un'unica query, riducendo i round-trip al database.

**Validazione duale.** Client per UX immediata, server per integrità dei dati. Le due validazioni sono indipendenti e il server non si fida del client.

---

*Gruppo 3 — 5AINC — Anno scolastico 2025/2026*
