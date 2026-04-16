const API = "middleware.php";

document.addEventListener("DOMContentLoaded", load);

async function load() {
    const res = await fetch(API);
    const data = await res.json();

    const list = document.getElementById("list");
    list.innerHTML = "";

    data.forEach(a => {
        list.innerHTML += `
        <tr>
            <td>${a.codice_azienda}</td>
            <td>${a.ragione_sociale}</td>
            <td>${a.partita_iva}</td>
            <td>
                <button onclick='edit(${JSON.stringify(a)})'>✏️</button>
                <button onclick='remove(${a.codice_azienda})'>🗑️</button>
            </td>
        </tr>`;
    });
}

document.getElementById("form").onsubmit = async e => {
    e.preventDefault();

    const data = {
        codice_azienda: id.value,
        ragione_sociale: ragione_sociale.value,
        partita_iva: partita_iva.value,
        sede_legale: sede_legale.value,
        sede_operativa: sede_operativa.value
    };

    await fetch(API, {
        method: data.codice_azienda ? "PUT" : "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
    });

    form.reset();
    load();
};

function edit(a) {
    id.value = a.codice_azienda;
    ragione_sociale.value = a.ragione_sociale;
    partita_iva.value = a.partita_iva;
    sede_legale.value = a.sede_legale;
    sede_operativa.value = a.sede_operativa;
}

async function remove(idA) {
    if (!confirm("Eliminare?")) return;

    await fetch(API, {
        method: "DELETE",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ codice_azienda: idA })
    });

    load();
}