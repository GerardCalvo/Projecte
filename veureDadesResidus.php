<div class="container">
    <h3>DADES RESIDUS 2023:</h3>
    <ul class="residus" id="llistaResidus">
         <li>Carregant dades...</li>
    </ul>
 </div>

 <script>
    fetch('api/residus.php')
        .then(response => response.json())
        .then(dades => {
            const llista = document.getElementById('llistaResidus');
            llista.innerHTML = '';
            Object.entries(dades).forEach(([clau, valor]) => {
                const li = document.createElement('li');
                const nom = clau.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                li.textContent = `${nom}: ${parseFloat(valor).toLocaleString('ca-ES')} kg`;
                llista.appendChild(li);
            });
        })
        .catch(error => {
            document.getElementById('llistaResidus').innerHTML = '<li>Error en carregar les dades.</li>';
            console.error('Error:', error);
        });
</script>