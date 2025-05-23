<div class="container">
    <h3>MUNICIPIS:</h3>
    <ul class="comarques" id="llistaMunicipis">
        <li>Carregant municipis...</li>
    </ul>
</div>

<script>
const params = new URLSearchParams(window.location.search);
const comarcaId = params.get('comarca');

let url = 'api/municipis.php';
if (comarcaId) {
    url += '?comarca=' + encodeURIComponent(comarcaId);
}

fetch(url)
    .then(response => response.json())
    .then(municipis => {
        const llista = document.getElementById('llistaMunicipis');
        llista.innerHTML = '';
        municipis.forEach(municipi => {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = 'veureMunicipi.php?municipi=' + encodeURIComponent(municipi.id);
            a.textContent = municipi.nom;
            li.appendChild(a);
            llista.appendChild(li);
        });
    })
    .catch(error => {
        document.getElementById('llistaMunicipis').innerHTML = '<li>Error en carregar els municipis.</li>';
        console.error('Error:', error);
    });
</script>