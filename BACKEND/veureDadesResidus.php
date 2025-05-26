<div class="container">
    <h3>DADES RESIDUS 2023:</h3>
    <ul class="residus" id="llistaResidus">
         <li>Carregant dades...</li>
    </ul>
</div>

<script>
fetch('api/residus.php')
    .then(response => {
        if (!response.ok) throw new Error('Resposta no OK');
        return response.json();
    })
    .then(dades => {
        const llista = document.getElementById('llistaResidus');
        llista.innerHTML = '';

        if (!dades || Object.values(dades).every(v => v === null)) {
            llista.innerHTML = '<li>No hi ha dades disponibles.</li>';
            return;
        }

        Object.entries(dades).forEach(([clau, valor]) => {
            const li = document.createElement('li');
            const nom = clau.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            li.textContent = `${nom}: ${parseFloat(valor || 0).toLocaleString('ca-ES')} kg`;
            llista.appendChild(li);
        });
    })
    .catch(error => {
        document.getElementById('llistaResidus').innerHTML = '<li>Error en carregar les dades.</li>';
        console.error('Error:', error);
    });
</script>
