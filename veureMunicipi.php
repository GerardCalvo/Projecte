<div class="container">
    <h2 id="titolMunicipi">Municipi</h2>
    <ul class="comarques" id="dadesMunicipi">
        <li>Carregant dades del municipi...</li>
    </ul>
</div>

<script>
const params = new URLSearchParams(window.location.search);
const municipiId = params.get('municipi');

// Si no hi ha ID, mostrem missatge
if (!municipiId) {
    document.getElementById('dadesMunicipi').innerHTML = '<li>Cap municipi seleccionat.</li>';
} else {
    // 1. Obtenir el nom del municipi
    fetch('api/municipis.php')
        .then(response => response.json())
        .then(municipis => {
            const municipi = municipis.find(m => m.id == municipiId);
            if (municipi) {
                document.getElementById('titolMunicipi').textContent = municipi.nom;
            } else {
                document.getElementById('titolMunicipi').textContent = 'Municipi no trobat';
            }
        })
        .catch(error => {
            console.error('Error obtenint el nom del municipi:', error);
        });

    // 2. Obtenir les dades de residus
    fetch('api/municipis.php?municipi=' + encodeURIComponent(municipiId))
        .then(response => response.json())
        .then(dades => {
            const llista = document.getElementById('dadesMunicipi');
            llista.innerHTML = '';

            if (dades.length === 0) {
                llista.innerHTML = '<li>No hi ha dades per aquest municipi.</li>';
                return;
            }

            dades.forEach(registre => {
                const li = document.createElement('li');

                // Mostra ID separat
                const idTitle = document.createElement('strong');
                idTitle.textContent = 'DADES AMB l\'ID: ' + registre.id;
                li.appendChild(idTitle);

                const detalls = document.createElement('ul');

                for (const [clau, valor] of Object.entries(registre)) {
                    // Evita tornar a mostrar 'id' i 'nom'
                    if (clau !== 'id' && clau !== 'nom') {
                        const item = document.createElement('li');
                        item.textContent = `${clau.replace(/_/g, ' ')}: ${valor}`;
                        detalls.appendChild(item);
                    }
                }

                li.appendChild(detalls);
                llista.appendChild(li);
            });
        })
        .catch(error => {
            document.getElementById('dadesMunicipi').innerHTML = '<li>Error en carregar les dades del municipi.</li>';
            console.error('Error:', error);
        });
}
</script>
