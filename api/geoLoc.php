<?php
include '../includes/errorHandler.proc.php';
include '../includes/dbConnect.proc.php';

// Peticions GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Si s'ha passat un ID de municipi per obtenir les dades de residus
    if (isset($_GET['municipi'])) {
        $stmt = $db->prepare("SELECT m.nom, g.latitud, g.longitud, d.f_r_r_m, d.kg_hab_any, d.total_recollida_selectiva * 100.0 / d.generaci_residus_municipal, d.poblacio 
        FROM dades_residus d JOIN municipis m ON d.municipi_id = m.id JOIN dades_geo g ON g.municipi_id = m.id 
        WHERE m.id = :municipi_id AND d.any = (SELECT MAX(any) FROM dades_residus) ORDER BY m.nom ASC;");
        $stmt->bindValue(':municipi_id', $_GET['municipi'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        $dades = [];
        while ($fila = $result->fetchArray(SQLITE3_ASSOC)) {
            $dades[] = $fila;
        }
        echo json_encode($dades);
            
    }
}
