<div class="container">
    <h3>COMARQUES:</h3>
    <ul class="comarques" id="llistaComarques">
        <li>Carregant comarques...</li>
    </ul>
</div>

<script>
fetch('api/comarques.php')
    .then(response => response.json())
    .then(comarques => {
        const llista = document.getElementById('llistaComarques');
        llista.innerHTML = '';
        comarques.forEach(comarca => {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = 'veureMunicipis.php?comarca=' + encodeURIComponent(comarca.id);
            a.textContent = comarca.nom;
            li.appendChild(a);
            llista.appendChild(li);
        });
    })
    .catch(error => {
        document.getElementById('llistaComarques').innerHTML = '<li>Error en carregar les comarques.</li>';
        console.error('Error:', error);
    });
</script>