document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('aziendaForm');
    const tbody = document.querySelector('#tabellaAziende tbody');
    // Carica Dati
    const fetchAziende = async () => {
    const res = await fetch('api.php');
    const data = await res.json();
    tbody.innerHTML = data.map(a => `
    <tr>
    <td>${a.nome}</td>
    <td>${a.p_iva}</td>
    <td>
    <button onclick="elimina(${a.id})">Elimina</button>
    
    </tr>
    `).join('');
    };
    // Crea / Aggiorna
    form.onsubmit = async (e) => {
    e.preventDefault();
    const payload = {
    nome: document.getElementById('nome').value,
    p_iva: document.getElementById('p_iva').value,
    indirizzo: document.getElementById('indirizzo').value
    };
    await fetch('api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
    });
    form.reset();
    fetchAziende();
    };
    window.elimina = async (id) => {
    if(confirm('Sei sicuro?')) {
    await fetch(`api.php?id=${id}`, { method: 'DELETE' });
    fetchAziende();
    }
    };
    fetchAziende();
});
    