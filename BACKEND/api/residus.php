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
        $result = $db->query("SELECT 
                dr.any,
                m.codi_municipi,
                m.nom AS municipi,
                c.nom AS comarca,
                dr.poblaci AS poblacio,
                dr.autocompostatge AS autocompostatge,
                dr.mat_ria_org_nica AS mat_ria_org_nica,
                dr.poda_i_jardineria AS poda_i_jardineria,
                dr.paper_i_cartr AS paper_i_cartr,
                dr.vidre AS vidre,
                dr.envasos_lleugers AS envasos_lleugers,
                dr.residus_voluminosos_fusta AS residus_voluminosos_fusta,
                dr.raee AS raee,
                dr.ferralla AS ferralla,
                dr.olis_vegetals AS olis_vegetals,
                dr.t_xtil AS t_xtil,
                dr.runes AS runes,
                dr.res_especials_en_petites AS res_especials_en_petites,
                dr.piles AS piles,
                dr.medicaments AS medicaments,
                dr.altres_recollides_selectives AS altres_recollides_selectives,
                dr.total_recollida_selectiva AS total_recollida_selectiva,
                dr.r_s_r_m_total AS r_s_r_m_total,
                dr.kg_hab_any_recollida_selectiva AS kg_hab_any_recollida_selectiva,
                dr.resta_a_dip_sit AS resta_a_dip_sit,
                dr.resta_a_incineraci AS resta_a_incineraci,
                dr.resta_a_tractament_mec_nic AS resta_a_tractament_mec_nic,
                dr.resta_sense_desglossar AS resta_sense_desglossar,
                dr.suma_fracci_resta AS suma_fracci_resta,
                dr.f_r_r_m AS f_r_r_m,
                dr.generaci_residus_municipal AS generaci_residus_municipal,
                dr.kg_hab_dia AS kg_hab_dia,
                dr.kg_hab_any AS kg_hab_any
            FROM dades_residus dr
            JOIN municipis m ON dr.municipi_id = m.id
            JOIN comarques c ON dr.comarca_id = c.id
            ORDER BY dr.any, c.nom, m.nom
        ");
        
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