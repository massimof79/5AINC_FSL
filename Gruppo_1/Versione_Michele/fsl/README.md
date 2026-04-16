# FSL Panel — Modulo CRUD Aziende

## Struttura del progetto

```
/
├── index.html               ← Frontend HTML (questo modulo)
├── aziende.css              ← Stile dashboard dark industrial
├── aziende.js               ← Logica CRUD lato client (fetch, validazione, render)
├── aziende_middleware.php   ← Middleware: validazione input, auth check, routing
├── api.php                  ← Classe AziendeApi: PDO + CRUD + sicurezza DB
│
│   ── FILE DA FORNIRE (non generati) ──
├── db_conn.php              ← Connessione PDO (vedi sotto)
├── sessioni.php             ← Gestione sessione/autenticazione (vedi sotto)
└── style.css                ← CSS globale del progetto esistente
```

---

## File da integrare

### `db_conn.php`
Deve esporre la funzione:

```php
function getDbConnection(): PDO {
    $dsn = 'mysql:host=localhost;dbname=4AIQ_FSL;charset=utf8mb4';
    $pdo = new PDO($dsn, 'utente', 'password', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    ]);
    return $pdo;
}
```

### `sessioni.php`
Deve:
- Avviare la sessione (`session_start()`)
- Verificare che l'utente sia autenticato
- Se non autenticato: fare `header('Location: login.php')` + `exit`
- Esporre `$_SESSION['user_id']`

---

## Architettura delle chiamate

```
Browser (aziende.js)
    │
    │  fetch() AJAX + X-Requested-With
    ▼
aziende_middleware.php
    │  • Verifica sessione via sessioni.php
    │  • Controlla metodo HTTP
    │  • Sanitizza e valida input
    │  • Instanzia AziendeApi
    ▼
api.php (classe AziendeApi)
    │  • Prepared statements PDO
    │  • Gestione errori DB
    ▼
db_conn.php → MySQL (tabella AZIENDA)
```

---

## Endpoint disponibili (via middleware)

| Azione   | Metodo | URL                                      | Body JSON             |
|----------|--------|------------------------------------------|-----------------------|
| list     | GET    | `aziende_middleware.php?action=list`     | —                     |
| get      | GET    | `aziende_middleware.php?action=get&id=N` | —                     |
| create   | POST   | `aziende_middleware.php?action=create`   | `{ragione_sociale, partita_iva, sede_legale, sede_operativa}` |
| update   | PUT    | `aziende_middleware.php?action=update`   | `{codice_azienda, ...stessi campi}` |
| delete   | DELETE | `aziende_middleware.php?action=delete&id=N` | —                  |

---

## Sicurezza implementata

- **PDO native prepared statements** — nessuna SQL injection possibile
- **Validazione doppia**: JS lato client + PHP lato server
- **Sanitizzazione input**: `strip_tags()`, `trim()`, regex per P.IVA
- **Autenticazione**: ogni richiesta verifica la sessione via `sessioni.php`
- **Solo AJAX**: il middleware rifiuta richieste senza `X-Requested-With`
- **Security headers**: X-Frame-Options, X-Content-Type-Options, ecc.
- **Errori DB non esposti**: logging interno, messaggio generico al client
- **XSS prevention lato JS**: `esc()` usa `createTextNode` per il rendering
- **Gestione unicità P.IVA**: intercettata a DB e restituita come errore 409
