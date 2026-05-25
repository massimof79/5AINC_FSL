# PCTOConnect — 5AINC\_FSL

> A full-stack web platform for managing PCTO internship programs, developed collaboratively by class **5AINC** as a school project.

---

## Table of Contents

1. [What is PCTO?](#what-is-pcto)
2. [Project Overview](#project-overview)
3. [Features](#features)
4. [Tech Stack](#tech-stack)
5. [Architecture](#architecture)
6. [Directory Structure](#directory-structure)
7. [Database Schema](#database-schema)
8. [Modules](#modules)
   - [Gruppo 1 — Companies](#gruppo-1--companies-aziende)
   - [Gruppo 2 — Availability](#gruppo-2--availability-disponibilità)
   - [Gruppo 3 — Experiences](#gruppo-3--experiences-esperienze)
   - [Gruppo 4 — Personnel](#gruppo-4--personnel-personale)
9. [Shared Infrastructure](#shared-infrastructure)
10. [Theme System](#theme-system)
11. [Authentication](#authentication)
12. [API Reference](#api-reference)
13. [Installation](#installation)
14. [Configuration](#configuration)
15. [Usage](#usage)
16. [Team](#team)

---

## What is PCTO?

**PCTO** (*Percorsi per le Competenze Trasversali e l'Orientamento*) is the Italian mandatory work-study alternance program for high school students. Every student must complete a minimum number of hours of practical work experience at external companies during their final school years.

Managing PCTO involves coordinating:
- **Companies** that offer internship positions
- **Availability slots** (when and how many students a company can host)
- **Experiences** (the actual PCTO sessions that took place)
- **School tutors** (teachers who oversee student progress)
- **Company tutors** (employees who mentor students on-site)
- **Students** and their assigned experiences

PCTOConnect centralizes all of this into one web application.

---

## Project Overview

PCTOConnect is a **browser-based management system** with a PHP backend, a MySQL database, and a vanilla JavaScript + CSS frontend. There is no external JavaScript framework — everything is built from scratch with modern web standards.

The project was developed in **four groups**, each responsible for a different domain of the application. All groups share a common visual design system, authentication layer, and database connection module.

The application requires a login to access any page. Once authenticated, users can navigate between modules using the sidebar, create and edit records through modal dialogs, and search or paginate through data in tables.

---

## Features

- **Secure authentication** — session-based login with bcrypt password hashing
- **Company management** — full CRUD for companies (Aziende), with VAT validation
- **Availability management** — manage internship slots offered by companies
- **Experience management** — track completed PCTO sessions with hours and linked tutors
- **School tutor management** — manage the teachers who supervise PCTO activities
- **Company tutor management** — manage the company-side mentors
- **Student management** — full student registry with linked experiences and applications
- **Live search and pagination** — every table is searchable and paginated
- **Three visual themes** — switchable at runtime, persisted via localStorage
- **Responsive layout** — sidebar collapses to a hamburger menu on screens ≤ 900 px
- **Toast notifications** — non-blocking feedback for every user action
- **WCAG AA contrast** — all themes pass accessibility contrast requirements
- **No page reloads** — all CRUD operations use the Fetch API with JSON

---

## Tech Stack

| Layer | Technology |
|---|---|
| Server language | PHP 8.1+ (strict types, PDO) |
| Database | MySQL / MariaDB 10.4+ |
| Frontend markup | HTML5 (semantic elements) |
| Frontend style | CSS3 (custom properties, Grid, Flexbox) |
| Frontend logic | JavaScript ES2020+ (Fetch API, async/await) |
| Fonts | Google Fonts — Inter |
| Icons | Custom inline SVG library (`icons/icons.js`) |
| Authentication | PHP sessions + bcrypt (`password_hash`) |
| Database access | PDO with prepared statements |

No npm, no build step, no framework. Open a PHP-capable web server and it runs.

---

## Architecture

```
Browser
  │
  ├── Loads HTML page (PHP renders initial shell)
  │     └── <link id="theme-link"> — active CSS file
  │     └── theme.js — reads localStorage, swaps CSS before first paint (FOUC prevention)
  │
  ├── JavaScript (per-module .js file) calls REST endpoints via Fetch API
  │     └── Returns JSON {success, message, data}
  │
  └── PHP REST endpoints
        ├── requireLoginApi() — rejects unauthenticated requests with HTTP 401
        ├── PDO prepared statement — query database
        └── echo json_encode(...) — respond to browser
```

Every page follows the same pattern:
1. PHP checks session on page load; redirects to `login.php` if not authenticated.
2. The page skeleton (sidebar, topbar, empty table) renders immediately.
3. The module's JavaScript file fires on `DOMContentLoaded`, calls the API, and fills the table.
4. User interactions (create, edit, delete) open a modal, submit via `fetch()`, and update the table without reloading.

---

## Directory Structure

```
5AINC_FSL/
│
├── index.php                   # Dashboard — entry point after login
├── login.php                   # Login form
├── logout.php                  # Session termination
├── register.php                # User registration
│
├── config.php                  # PDO database connection (Gruppo 1)
├── auth.php                    # Session helper functions (shared)
├── api.php                     # Unified REST API router (shared)
├── api_session.php             # Session state endpoint
│
├── theme.js                    # 3-way theme switcher (FOUC-safe)
├── global.css                  # Theme 1: Classic (navy/green)
├── global2.css                 # Theme 2: Aurora (petrol/teal)
├── global3.css                 # Theme 3: Brutale (Olivetti cream/vermillion)
│
├── 5ainc_fsl.sql               # Full database schema with sample data
│
├── icons/
│   ├── icons.js                # SVG icon constants (status, nav, actions)
│   └── *.svg                   # Individual icon files
│
├── Gruppo1/
│   ├── index_aziende.php       # Companies page
│   ├── aziende.js              # Companies frontend logic
│   └── aziende_middleware.php  # Companies REST API
│
├── Gruppo 2/
│   ├── disponibilità.php       # Availability page (HTML + inline JS)
│   └── middlewere.php          # Availability REST API
│
├── Gruppo 3/
│   ├── esperienze.php          # Experiences page
│   ├── esperienze.js           # Experiences frontend logic
│   └── api_esperienze.php      # Experiences REST API
│
└── Gruppo4/
    ├── tutor_scolastici.html   # School tutors page
    ├── tutor_scolastici.js     # School tutors frontend logic
    ├── tutor_aziendali.html    # Company tutors page
    ├── tutor_aziendali.js      # Company tutors frontend logic
    ├── studenti.html           # Students page
    └── studenti.js             # Students frontend logic
```

---

## Database Schema

The database is named `5AINC_FSL`. Import `5ainc_fsl.sql` to create all tables, views, and sample data.

### Entity-Relationship Overview

```
AZIENDA ──< DISPONIBILITA ──< ESPERIENZA >── TUTOR_SCOLASTICO
                                    │
                              TUTOR_AZIENDALE

STUDENTE >── ESPERIENZA
STUDENTE >── CANDIDATURA
```

### Tables

#### `AZIENDA` — Companies
| Column | Type | Notes |
|---|---|---|
| `codice_azienda` | INT | Primary key, auto-increment |
| `ragione_sociale` | VARCHAR | Company name |
| `partita_iva` | VARCHAR(11) | VAT number, unique, 11 digits |
| `sede_legale` | VARCHAR | Registered address |
| `sede_operativa` | VARCHAR | Operational address |

#### `DISPONIBILITA` — Availability Slots
| Column | Type | Notes |
|---|---|---|
| `codice_disponibilita` | INT | Primary key |
| `periodo_previsto` | DATE | When the slot is offered |
| `numero_studenti` | INT | Max students (> 0) |
| `descrizione` | TEXT | Description of the activity |
| `competenze` | TEXT | Required skills |
| `indirizzo_consigliato` | VARCHAR | Recommended school program |
| `codice_azienda` | INT | FK → AZIENDA (CASCADE DELETE) |

#### `ESPERIENZA` — PCTO Experiences
| Column | Type | Notes |
|---|---|---|
| `codice_esperienza` | INT | Primary key |
| `periodo_effettivo` | VARCHAR | Actual period (free text) |
| `numero_ore_previste` | INT | Planned hours (≥ 0) |
| `numero_ore_svolte` | INT | Completed hours (≥ 0) |
| `numero_studenti` | INT | Students involved (> 0) |
| `codice_docente` | INT | FK → TUTOR_SCOLASTICO (UPDATE CASCADE) |
| `codice_disponibilita` | INT | FK → DISPONIBILITA (UPDATE CASCADE) |
| `codice_tutor` | INT | FK → TUTOR_AZIENDALE (UPDATE CASCADE) |

#### `TUTOR_SCOLASTICO` — School Tutors
| Column | Type | Notes |
|---|---|---|
| `codice_docente` | INT | Primary key |
| `nome` | VARCHAR | First name |
| `cognome` | VARCHAR | Last name |
| `tipo` | ENUM | `'dipartimento'` or `'area disciplinare'` |
| `numero_studenti` | INT | Number of students supervised |

#### `TUTOR_AZIENDALE` — Company Tutors
| Column | Type | Notes |
|---|---|---|
| `codice_tutor` | INT | Primary key |
| `nome` | VARCHAR | First name |
| `cognome` | VARCHAR | Last name |
| `ruolo` | VARCHAR | Job title (e.g. "Software Engineer") |
| `email` | VARCHAR | Unique email address |

#### `STUDENTE` — Students
| Column | Type | Notes |
|---|---|---|
| `codice_studente` | INT | Primary key |
| `nome` | VARCHAR | First name |
| `cognome` | VARCHAR | Last name |
| `data_di_nascita` | DATE | Date of birth |
| `luogo_di_nascita` | VARCHAR | Place of birth |
| `indirizzo` | VARCHAR | Home address |
| `email` | VARCHAR | Unique email address |
| `classe` | VARCHAR | School class (e.g. `4AI`) |
| `indirizzo_di_studi` | VARCHAR | School program (e.g. `Informatica`) |
| `codice_esperienza` | INT | FK → ESPERIENZA (SET NULL on delete) |
| `codice_candidatura` | INT | FK → CANDIDATURA (SET NULL on delete) |

#### `CANDIDATURA` — Applications
| Column | Type | Notes |
|---|---|---|
| `codice_candidatura` | INT | Primary key |
| `data_candidatura` | DATE | Application date |
| `lettera_motivazionale` | TEXT | Cover letter |
| `stato_candidatura` | ENUM | `inserita`, `in valutazione`, `accettata`, `rifiutata`, `ritirata` |

#### `UTENTI` — User Accounts
| Column | Type | Notes |
|---|---|---|
| `ID` | INT | Primary key |
| `username` | VARCHAR | Unique username |
| `password` | VARCHAR | bcrypt hash |

### Views

**`v_studenti_anagrafica`** — A read-only view exposing a safe subset of student fields (excludes sensitive internal codes). Used for display purposes.

---

## Modules

### Gruppo 1 — Companies (`Aziende`)

**Page:** `Gruppo1/index_aziende.php`  
**API:** `Gruppo1/aziende_middleware.php`  
**Logic:** `Gruppo1/aziende.js`

Manages the companies that participate in PCTO programs.

**What you can do:**
- View the full list of companies in a paginated, searchable table
- Add a new company (name, VAT number, legal address, operational address)
- Edit any company's details
- Delete a company (this also deletes all its availability slots via CASCADE)

**Validation:**
- VAT number (`Partita IVA`) must be exactly 11 numeric digits
- All fields are required

**Table columns:** `#`, Company Name, VAT, Legal Address, Operational Address, Actions

---

### Gruppo 2 — Availability (`Disponibilità`)

**Page:** `Gruppo 2/disponibilità.php`  
**API:** `Gruppo 2/middlewere.php`

Manages the internship slots that companies offer. A "disponibilità" (availability) is a company's declaration that it can host a certain number of students during a specific period.

**What you can do:**
- View all availability slots with search by period or description
- Create a new slot (period, max students, description, required skills, recommended school program)
- Edit an existing slot
- Delete a slot

**Table columns:** `#`, Period, Description, Actions

**Note:** Each availability is linked to a company. When a company is deleted, all its availability slots are deleted automatically.

---

### Gruppo 3 — Experiences (`Esperienze`)

**Page:** `Gruppo 3/esperienze.php`  
**API:** `Gruppo 3/api_esperienze.php`  
**Logic:** `Gruppo 3/esperienze.js`

This is the **core domain module**. An "esperienza" (experience) represents a PCTO session that actually took place — it links a company's availability slot to the school tutor and company tutor who supervised it.

**What you can do:**
- View all experiences with joined data (tutor names, availability period) displayed inline
- Create a new experience with seven fields
- Edit an existing experience
- Delete an experience

**Form fields:**
| Field | Description |
|---|---|
| Periodo effettivo | Free-text description of when it happened (e.g. `Mar–Apr 2026`) |
| Ore previste | Planned hours |
| Ore svolte | Hours actually completed |
| Numero studenti | How many students participated |
| Tutor scolastico | School tutor — dropdown populated from the database |
| Tutor aziendale | Company tutor — dropdown populated from the database |
| Disponibilità | Which availability slot this experience fulfilled — dropdown from DB |

**Table columns:** `#`, Period, Planned Hours, Completed Hours, Students, School Tutor, Company Tutor, Availability, Actions

**Gruppo 3 also maintains `global.css`** — the shared design system used by all pages in the application.

---

### Gruppo 4 — Personnel (`Personale`)

Gruppo 4 manages three separate entities, each with its own page.

---

#### School Tutors (`Tutor Scolastici`)

**Page:** `Gruppo4/tutor_scolastici.html`  
**Logic:** `Gruppo4/tutor_scolastici.js`

Manages the teachers who act as school-side supervisors for PCTO experiences.

**Form fields:** First name, Last name, Type (`dipartimento` or `area disciplinare`), Number of students supervised

**Table columns:** `#`, First Name, Last Name, Type, Number of Students, Actions

---

#### Company Tutors (`Tutor Aziendali`)

**Page:** `Gruppo4/tutor_aziendali.html`  
**Logic:** `Gruppo4/tutor_aziendali.js`

Manages the company employees who mentor students on-site.

**Form fields:** First name, Last name, Role, Email

**Table columns:** `#`, First Name, Last Name, Role, Email, Actions

---

#### Students (`Studenti`)

**Page:** `Gruppo4/studenti.html`  
**Logic:** `Gruppo4/studenti.js`

The full student registry. Each student can be linked to a PCTO experience and an application.

**Form fields:** First name, Last name, Date of birth, Place of birth, Address, Email, Class, School program, PCTO Experience (optional, dropdown), Application (optional, dropdown)

**Table columns:** `#`, Student, Date of birth, Place, Address, Email, Class, Program, Experience, Application, Status, Actions

---

## Shared Infrastructure

### Database Connection — `config.php`

Establishes a PDO connection to the MySQL database. Used by every API file via `require_once`.

- Host: `127.0.0.1`, Port: `3307` (configurable)
- Database: `5AINC_FSL`
- Error mode: `PDO::ERRMODE_EXCEPTION` — errors throw exceptions, never expose details to the browser
- Prepared statement emulation: disabled — real prepared statements only
- Charset: `utf8mb4`

### Authentication — `auth.php`

Provides session management functions used across all pages and APIs:

| Function | Description |
|---|---|
| `isLoggedIn()` | Returns true if the user has an active session |
| `requireLoginPage()` | Redirects to `login.php` if not logged in (used in page files) |
| `requireLoginApi()` | Returns HTTP 401 JSON if not logged in (used in API files) |
| `loginUser(array)` | Regenerates session ID and sets `$_SESSION['user_id']` and `username` |
| `logoutUser()` | Clears session data, destroys the session, expires cookies |
| `verifyUserPassword(input, hash)` | Verifies with `password_verify()`; falls back to hash-equals for legacy plain-text accounts |

### Unified API — `api.php`

A single entry point that routes requests to the correct module based on the `?entity=` query parameter. All CRUD operations are protected: every request passes through `requireLoginApi()` before reaching any database code.

**Supported entities:**
- `?entity=aziende`
- `?entity=disponibilita`
- `?entity=esperienze`
- `?entity=tutor_scolastici`
- `?entity=tutor_aziendali`

All API responses follow this JSON structure:

```json
{
  "success": true,
  "message": "Operazione completata",
  "data": [ ... ]
}
```

Errors return `"success": false` with an appropriate message and HTTP status code (400, 401, 404, 500).

### Icon Library — `icons/icons.js`

Exports SVG string constants used for status indicators and navigation icons:

```javascript
ICONS.dbOk        // green dot — database connected
ICONS.dbLoading   // spinning indicator — checking connection
ICONS.dbErr       // red dot — connection failed
ICONS.chevronLeft  // pagination arrow
ICONS.chevronRight // pagination arrow
```

---

## Theme System

PCTOConnect includes three complete visual themes. The active theme is stored in `localStorage` and applied before the first browser paint — no flash of unstyled content.

### Themes

| Name | File | Palette | Character |
|---|---|---|---|
| **Classic** | `global.css` | Navy sidebar, green accent | Clean, professional |
| **Aurora** | `global2.css` | Deep petrol sidebar, teal accent, pearl background | Modern, clarity-focused |
| **Brutale** | `global3.css` | Cream/sand sidebar, vermillion accent, near-zero radius | Bold, Olivetti Valentine-inspired |

### How to Switch

In the sidebar, under the **Tema** section, three buttons are always visible: **Classic**, **Aurora**, **Brutale**. Clicking one:
1. Saves the choice to `localStorage` (key: `fsl-theme`)
2. Swaps the `<link id="theme-link">` `href` attribute to the new CSS file
3. Updates the active state on the theme buttons

The choice is remembered across sessions and page navigations. The switch happens in under one frame — no reload required.

### FOUC Prevention

`theme.js` is loaded synchronously in `<head>`, immediately after the `<link>` element. It reads `localStorage` and corrects the CSS href before the browser renders anything. This eliminates the "white flash" that would occur if theme switching happened after page load.

---

## Authentication

All pages require a valid login session. The flow is:

1. User visits any page → PHP calls `requireLoginPage()` → redirects to `login.php` if not authenticated.
2. User submits username and password on `login.php`.
3. PHP queries the `UTENTI` table, verifies the password with `password_verify()`.
4. On success: `session_regenerate_id(true)` (prevents session fixation), session variables set, redirect to `index.php`.
5. `logout.php` clears all session data, destroys the session file, and expires the session cookie.

**Password storage:** bcrypt via `password_hash($password, PASSWORD_DEFAULT)`. The cost factor is PHP's current default (10+). Legacy accounts with plain-text passwords are detected and verified via `hash_equals()` — a migration to bcrypt is recommended for production use.

---

## API Reference

### Companies (`Aziende`)

| Method | URL | Action |
|---|---|---|
| `GET` | `/Gruppo1/aziende_middleware.php` | List all companies |
| `GET` | `/Gruppo1/aziende_middleware.php?id=N` | Get one company |
| `POST` | `/Gruppo1/aziende_middleware.php` | Create company |
| `PUT` | `/Gruppo1/aziende_middleware.php?id=N` | Update company |
| `DELETE` | `/Gruppo1/aziende_middleware.php?id=N` | Delete company |

### Availability (`Disponibilità`)

| Method | URL | Action |
|---|---|---|
| `GET` | `/Gruppo 2/middlewere.php` | List all slots |
| `GET` | `/Gruppo 2/middlewere.php?id=N` | Get one slot |
| `POST` | `/Gruppo 2/middlewere.php` | Create slot |
| `PUT` | `/Gruppo 2/middlewere.php?id=N` | Update slot |
| `DELETE` | `/Gruppo 2/middlewere.php?id=N` | Delete slot |

### Experiences (`Esperienze`)

| Method | URL | Action |
|---|---|---|
| `GET` | `/Gruppo 3/api_esperienze.php` | List all experiences |
| `GET` | `/Gruppo 3/api_esperienze.php?id=N` | Get one experience |
| `POST` | `/Gruppo 3/api_esperienze.php` | Create experience |
| `PUT` | `/Gruppo 3/api_esperienze.php?id=N` | Update experience |
| `DELETE` | `/Gruppo 3/api_esperienze.php?id=N` | Delete experience |
| `GET` | `/Gruppo 3/api_esperienze.php?resource=tutor_scolastico` | List school tutors (for dropdown) |
| `GET` | `/Gruppo 3/api_esperienze.php?resource=tutor_aziendale` | List company tutors (for dropdown) |
| `GET` | `/Gruppo 3/api_esperienze.php?resource=disponibilita` | List availability slots (for dropdown) |

### Personnel — Unified via `api.php`

| Method | URL | Action |
|---|---|---|
| `GET` | `/api.php?entity=tutor_scolastici` | List school tutors |
| `POST` | `/api.php?entity=tutor_scolastici` | Create school tutor |
| `PUT` | `/api.php?entity=tutor_scolastici&id=N` | Update school tutor |
| `DELETE` | `/api.php?entity=tutor_scolastici&id=N` | Delete school tutor |
| `GET` | `/api.php?entity=tutor_aziendali` | List company tutors |
| `POST` | `/api.php?entity=tutor_aziendali` | Create company tutor |
| `PUT` | `/api.php?entity=tutor_aziendali&id=N` | Update company tutor |
| `DELETE` | `/api.php?entity=tutor_aziendali&id=N` | Delete company tutor |

---

## Installation

### Prerequisites

| Requirement | Version |
|---|---|
| PHP | 8.1 or higher |
| MySQL / MariaDB | 10.4 or higher |
| PDO extension | Enabled in `php.ini` |
| PDO MySQL driver | `pdo_mysql` enabled |
| Web server | Apache, Nginx, or PHP built-in server |

### Step-by-Step Setup

**1. Clone or download the repository**

```bash
git clone https://github.com/massimof79/5AINC_FSL.git
cd 5AINC_FSL
```

**2. Create the database**

Open your MySQL client (phpMyAdmin, TablePlus, DBeaver, or the command line) and run:

```sql
CREATE DATABASE `5AINC_FSL` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `5AINC_FSL`;
SOURCE 5ainc_fsl.sql;
```

Or from the terminal:

```bash
mysql -u root -p < 5ainc_fsl.sql
```

This creates all 8 tables, the view, and loads sample data (including two test user accounts).

**3. Configure the database connection**

Open `config.php` and update the constants to match your environment:

```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');       // change if your MySQL runs on a different port
define('DB_NAME', '5AINC_FSL');
define('DB_USER', 'root');       // your MySQL username
define('DB_PASS', '');           // your MySQL password
```

> **Important:** Do not commit `config.php` with real credentials to a public repository.

**4. Start a web server**

Option A — PHP built-in server (development only):

```bash
php -S localhost:8000
```

Then open [http://localhost:8000](http://localhost:8000) in your browser.

Option B — Apache / XAMPP / WAMP / MAMP:

Place the project folder inside `htdocs` (XAMPP) or `www` (WAMP/MAMP) and navigate to `http://localhost/5AINC_FSL/`.

Option C — Nginx:

Configure a server block pointing to the project directory with PHP-FPM.

**5. Log in**

Navigate to `http://localhost:8000/login.php` (or equivalent).

Default accounts (from sample data):

| Username | Password |
|---|---|
| `miccia` | *(ask team for credentials)* |
| `michele` | *(ask team for credentials)* |

You will be redirected to the dashboard (`index.php`) on successful login.

---

## Configuration

### `config.php` — Database

```php
define('DB_HOST', '127.0.0.1');  // Database host
define('DB_PORT', '3306');        // Port (default MySQL: 3306)
define('DB_NAME', '5AINC_FSL');  // Database name
define('DB_USER', 'root');        // MySQL user
define('DB_PASS', '');            // MySQL password
```

### PHP session settings (recommended for `php.ini`)

```ini
session.use_cookies = 1
session.use_only_cookies = 1
session.cookie_httponly = 1
session.cookie_samesite = Lax
```

These settings prevent session hijacking via JavaScript and cross-site request forgery.

### Theme default (`theme.js`)

```javascript
var DEFAULT = 'global2.css';   // Aurora theme is the default
```

Change this to `'global.css'` (Classic) or `'global3.css'` (Brutale) to make a different theme the application default.

---

## Usage

### Navigating the Application

After login, the **dashboard** (`index.php`) shows six clickable cards — one for each module. The sidebar on the left provides persistent navigation to all sections.

**Sidebar sections:**
- **Gestione** — Companies, Availability, Experiences
- **Personale** — School Tutors, Company Tutors, Students
- **Tema** — Switch between Classic, Aurora, and Brutale themes
- **Sistema** — Logout

### Working with Records

Every module page follows the same interaction pattern:

1. **View** — Records load automatically into a table when the page opens.
2. **Search** — Type in the search box to filter rows in real time (no page reload).
3. **Paginate** — Use the ← → buttons to navigate between pages of results.
4. **Create** — Click **+ Nuovo [record type]** to open a form modal. Fill in the fields and submit.
5. **Edit** — Click the edit (pencil) button on any row. The same modal opens pre-filled with existing data.
6. **Delete** — Click the delete (trash) button. A confirmation dialog appears before anything is deleted.

All operations show a **toast notification** in the top-right corner confirming success or reporting an error.

### Responsive Use

On screens narrower than 900 px (tablets and phones), the sidebar hides automatically. A hamburger menu button (☰) appears in the top-left corner to toggle it. The rest of the layout adapts to single-column.

---

## Team

| Group | Responsibility | Members |
|---|---|---|
| **Gruppo 1** | Companies module, database connection (`config.php`) | — |
| **Gruppo 2** | Availability module, unified API (`api.php`) | — |
| **Gruppo 3** | Experiences module, global design system (`global.css`) | — |
| **Gruppo 4** | Personnel module (tutors, students), session management | — |

**Class:** 5AINC  
**School year:** 2025/2026  
**Design system:** Claude (Anthropic) — [claude.ai/code](https://claude.ai/code)

---

*Progetto scolastico — Classe 5AINC — Anno scolastico 2025/2026*
