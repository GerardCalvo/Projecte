<?php
include '../includes/errorHandler.proc.php';
include '../includes/dbConnect.proc.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['municipi'])) {
        $stmt = $db->prepare("SELECT m.nom, g.latitud, g.longitud, d.f_r_r_m, d.kg_hab_any, 
                                     d.total_recollida_selectiva * 100.0 / d.generaci_residus_municipal AS percentatge_selectiva,
                                     d.poblacio
                              FROM dades_residus d 
                              JOIN municipis m ON d.municipi_id = m.id 
                              JOIN dades_geo g ON g.municipi_id = m.id 
                              WHERE m.id = :municipi_id 
                              AND d.any = (SELECT MAX(any) FROM dades_residus)
                              LIMIT 1;");
        $stmt->bindValue(':municipi_id', $_GET['municipi'], SQLITE3_INTEGER);
        $result = $stmt->execute();

        $fila = $result->fetchArray(SQLITE3_ASSOC);
        if ($fila) {
            // Converteix tots els valors a text
            foreach ($fila as $k => $v) {
                $fila[$k] = strval($v);
            }
            echo json_encode($fila, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            echo json_encode([]);
        }
    } else {
            $result = $db->query("SELECT 
                                    m.nom AS municipi, 
                                    g.latitud, 
                                    g.longitud, 
                                    g.geocoded_type, 
                                    g.geocoded_coordinates,
                                    d.f_r_r_m, 
                                    d.kg_hab_any, 
                                    d.total_recollida_selectiva * 100.0 / d.generaci_residus_municipal AS percentatge_selectiva,
                                    d.poblaci AS poblacio
                                FROM dades_residus d 
                                JOIN municipis m ON d.municipi_id = m.id 
                                LEFT JOIN dades_geo g ON g.municipi_id = m.id 
                                WHERE d.any = (SELECT MAX(any) FROM dades_residus);");

    $municipis = [];

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $formatted = [];

        foreach ($row as $k => $v) {
            if (in_array($k, ['geocoded_coordinates', 'geocoded_type'])) {
                continue; // Els gestionarem més avall com a JSON estructurat
            }
            $formatted[$k] = strval($v);
        }

        // Crea geocoded_column si hi ha dades disponibles
        if (!empty($row['geocoded_coordinates']) && !empty($row['geocoded_type'])) {
            $coordinates = json_decode($row['geocoded_coordinates'], true);
            if (is_array($coordinates) && isset($coordinates[0]) && isset($coordinates[1])) {
                $formatted['geocoded_column'] = [
                    'type' => strval($row['geocoded_type']),
                    'coordinates' => [$coordinates[0], $coordinates[1]]
                ];
            }
        }

        $municipis[] = $formatted;
    }

    header('Content-Type: application/json');
    echo json_encode($municipis);
    }
}
