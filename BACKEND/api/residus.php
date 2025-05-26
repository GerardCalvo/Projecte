<?php
include '../includes/errorHandler.proc.php';
include '../includes/dbConnect.proc.php';

// Peticions GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (
        isset($_GET['yearFrom']) &&
        isset($_GET['yearTo']) &&
        isset($_GET['minPop']) &&
        isset($_GET['maxPop'])
    ) {
        $yearFrom = intval($_GET['yearFrom']);
        $yearTo = intval($_GET['yearTo']);
        $minPop = intval($_GET['minPop']);
        $maxPop = intval($_GET['maxPop']);

        $stmt = $db->prepare(
            "SELECT any, poblacio, 
                    kg_hab_any AS `Kg / hab / any`, 
                    kg_hab_any_rec_selectiva AS `Kg/hab/any recollida selectiva`
             FROM dades_residus
             WHERE any BETWEEN :yearFrom AND :yearTo
             AND poblacio BETWEEN :minPop AND :maxPop"
        );
        $stmt->bindValue(':yearFrom', $yearFrom, SQLITE3_INTEGER);
        $stmt->bindValue(':yearTo', $yearTo, SQLITE3_INTEGER);
        $stmt->bindValue(':minPop', $minPop, SQLITE3_INTEGER);
        $stmt->bindValue(':maxPop', $maxPop, SQLITE3_INTEGER);

        $result = $stmt->execute();

        $residus = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $residus[] = $row;
        }

        header('Content-Type: application/json');
        echo json_encode($residus);
    } else {
        $result = $db->query("SELECt * FROM dades_residus JOIN ");
        $residus = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $residus[] = $row;
        }

        header('Content-Type: application/json');
        echo json_encode($residus);
        /*$result = $db->query("
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
    */}
}
?>