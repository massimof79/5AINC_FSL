<!DOCTYPE html>
<html lang="it">
    <head>
    <link rel="stylesheet" href="style.css">
    <title>Gestione Azienda CRUD</title>
    </head>
    <body>
    <div class="container">
    <h1>Anagrafica Aziende</h1>
    <form id="aziendaForm">
    <input type="hidden" id="aziendaId">
    <input type="text" id="nome" placeholder="Nome Azienda" required>
    <input type="text" id="p_iva" placeholder="Partita IVA" required>
    <input type="text" id="indirizzo" placeholder="Indirizzo">
    <button type="submit">Salva</button>
    </form>
    <table id="tabellaAziende">
    <thead>
    <tr><th>Nome</th><th>P.IVA</th><th>Azioni</th></tr>
    </thead>

    <tbody></tbody>
    </table>
    </div>
    <script src="script.js"></script>
    </body>
</html>