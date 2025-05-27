<?php
include '../includes/errorHandler.proc.php';
include '../includes/dbConnect.proc.php';
ini_set('memory_limit', '512M');

// Peticions GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    /**
     * @author Gerard Calvo, Oriol Canellas, Agustí Lopez, Anthonella Orosco
     * @since 27 / 05 / 2025
     * @return $residusAnys Retorna la mitjana del r_s_r_m_total de cada any.
     */
    if (isset($_GET['$select']) && isset($_GET['$group']) && isset($_GET['$order'])) {
    $result = $db->query("SELECT any, AVG(r_s_r_m_total) AS avg_r_s_r_m_total FROM dades_residus GROUP BY any ORDER BY any");
    $residusAnys = [];

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $formatted = [];
        foreach ($row as $key => $value) {
            $formatted[$key] = strval($value); // Convertim tot a text, sense modificar el valor
        }
        $residusAnys[] = $formatted;
    }

    header('Content-Type: application/json');
    echo json_encode($residusAnys);
    /**
     * @author Gerard Calvo, Oriol Canellas, Agustí Lopez, Anthonella Orosco
     * @since 27 / 05 / 2025
     * @return $residus Retorna tota la informació dels residus de tots els municipis.
     */
    } else {
        $result = $db->query("SELECT 
                dr.any,
                m.codi_municipi,
                m.nom AS municipi,
                c.nom AS comarca,
                dr.poblaci AS poblaci,
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
        ");
        
        $residus = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $formatted = [];
        foreach ($row as $key => $value) {
            if (is_numeric($value)) {
                // Camps que volem com enters en format string (sense decimals)
                if (in_array($key, ['any', 'codi_municipi', 'poblaci'])) {
                    $formatted[$key] = strval(intval($value));
                } else {
                    // Nombres reals en format text amb 2 decimals
                    $formatted[$key] = number_format((float)$value, 2, '.', '');
                }
            } else {
                $formatted[$key] = $value; // Per strings com "municipi", "comarca", etc.
            }
        }
        $residus[] = $formatted;
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