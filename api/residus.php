<?php
include '../includes/errorHandler.proc.php';
include '../includes/dbConnect.proc.php';

// Peticions GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET[''])) {
        // Aquí podries afegir gestió per altres paràmetres GET
    } else {
        $result = $db->query("
            SELECT
                SUM(mat_ria_org_nica) AS materia_organica,
                SUM(paper_i_cartr) AS paper_cartro,
                SUM(envasos_lleugers) AS envasos_lleugers,
                SUM(vidre) AS vidre,
                SUM(runes) AS runes,
                SUM(poda_i_jardineria) AS poda_i_jardineria,
                SUM(raee) AS raee,
                SUM(altres_recollides_selectives) AS altres_recollides_select,
                SUM(t_txtil) AS textil,
                SUM(ferralla) AS ferralla,
                SUM(res_especials_en_petites) AS residus_esp_petites,
                SUM(olis_vegetals) AS olis_vegetals,
                SUM(autocompostatge) AS autocompostatge
            FROM dades_residus
            WHERE any = 2023
        ");
        $residus = [];
        if ($result) {
            $residus = $result->fetchArray(SQLITE3_ASSOC);
        }

        header('Content-Type: application/json');
        echo json_encode($residus);
    }
}
