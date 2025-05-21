<?php
// 1. Crear/obrir la base de dades SQLite
$db = new SQLite3('residus.db');

// 2. Crear taules normalitzades

// Taula de comarques
$db->exec("CREATE TABLE IF NOT EXISTS comarques (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT UNIQUE
)");

// Taula de municipis
$db->exec("CREATE TABLE IF NOT EXISTS municipis (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    codi_municipi TEXT UNIQUE,
    nom TEXT,
    comarca_id INTEGER,
    FOREIGN KEY(comarca_id) REFERENCES comarques(id)
)");

// Taula de dades de residus
$db->exec("CREATE TABLE IF NOT EXISTS dades_residus (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    any INTEGER,
    municipi_id INTEGER,
    comarca_id INTEGER,
    poblacio INTEGER,
    autocompostatge REAL,
    mat_ria_org_nica REAL,
    poda_i_jardineria REAL,
    paper_i_cartr REAL,
    vidre REAL,
    envasos_lleugers REAL,
    residus_voluminosos_fusta REAL,
    raee REAL,
    ferralla REAL,
    olis_vegetals REAL,
    t_xtil REAL,
    runes REAL,
    res_especials_en_petites REAL,
    piles REAL,
    medicaments REAL,
    altres_recollides_selectives REAL,
    total_recollida_selectiva REAL,
    r_s_r_m_total REAL,
    kg_hab_any_recollida_selectiva REAL,
    resta_a_dip_sit REAL,
    resta_a_incineraci REAL,
    resta_a_tractament_mec_nic REAL,
    resta_sense_desglossar REAL,
    suma_fracci_resta REAL,
    f_r_r_m REAL,
    generaci_residus_municipal REAL,
    kg_hab_dia REAL,
    kg_hab_any REAL,
    FOREIGN KEY(municipi_id) REFERENCES municipis(id),
    FOREIGN KEY(comarca_id) REFERENCES comarques(id)
)");

// 3. Descarregar dades del JSON extret de l'API
$url = "https://analisi.transparenciacatalunya.cat/resource/69zu-w48s.json";
$data = file_get_contents($url);
if (!$data) {
    die("No s'han pogut obtenir dades de l'API.");
}
$rows = json_decode($data, true);

// 4. Preparar consulta INSERT per dades_residus
$stmt = $db->prepare("
    INSERT INTO dades_residus (
        any, municipi_id, comarca_id, poblacio,
        autocompostatge, mat_ria_org_nica, poda_i_jardineria,
        paper_i_cartr, vidre, envasos_lleugers, residus_voluminosos_fusta,
        raee, ferralla, olis_vegetals, t_xtil, runes,
        res_especials_en_petites, piles, medicaments, altres_recollides_selectives,
        total_recollida_selectiva, r_s_r_m_total, kg_hab_any_recollida_selectiva,
        resta_a_dip_sit, resta_a_incineraci, resta_a_tractament_mec_nic,
        resta_sense_desglossar, suma_fracci_resta, f_r_r_m, generaci_residus_municipal,
        kg_hab_dia, kg_hab_any
    ) VALUES (
        :any, :municipi_id, :comarca_id, :poblacio,
        :autocompostatge, :mat_ria_org_nica, :poda_i_jardineria,
        :paper_i_cartr, :vidre, :envasos_lleugers, :residus_voluminosos_fusta,
        :raee, :ferralla, :olis_vegetals, :t_xtil, :runes,
        :res_especials_en_petites, :piles, :medicaments, :altres_recollides_selectives,
        :total_recollida_selectiva, :r_s_r_m_total, :kg_hab_any_recollida_selectiva,
        :resta_a_dip_sit, :resta_a_incineraci, :resta_a_tractament_mec_nic,
        :resta_sense_desglossar, :suma_fracci_resta, :f_r_r_m, :generaci_residus_municipal,
        :kg_hab_dia, :kg_hab_any
    );
");

// 5. Inserir les dades
foreach ($rows as $r) {
    // -- COMARCA
    $comarca_nom = $r['comarca'] ?? '';
    $stmt_comarca = $db->prepare("INSERT OR IGNORE INTO comarques (nom) VALUES (:nom)");
    $stmt_comarca->bindValue(':nom', $comarca_nom);
    $stmt_comarca->execute();
    $comarca_id = $db->querySingle("SELECT id FROM comarques WHERE nom = '{$comarca_nom}'");

    // -- MUNICIPI
    $codi_municipi = $r['codi_municipi'] ?? '';
    $municipi_nom = $r['municipi'] ?? '';
    $stmt_municipi = $db->prepare("INSERT OR IGNORE INTO municipis (codi_municipi, nom, comarca_id) VALUES (:codi, :nom, :comarca_id)");
    $stmt_municipi->bindValue(':codi', $codi_municipi);
    $stmt_municipi->bindValue(':nom', $municipi_nom);
    $stmt_municipi->bindValue(':comarca_id', $comarca_id);
    $stmt_municipi->execute();
    $municipi_id = $db->querySingle("SELECT id FROM municipis WHERE codi_municipi = '{$codi_municipi}'");

    // -- DADES DE RESIDUS
    $stmt->bindValue(':any', intval($r['any'] ?? 0));
    $stmt->bindValue(':municipi_id', $municipi_id);
    $stmt->bindValue(':comarca_id', $comarca_id);
    $stmt->bindValue(':poblacio', intval($r['poblaci'] ?? 0));
    $stmt->bindValue(':autocompostatge', floatval($r['autocompostatge'] ?? 0));
    $stmt->bindValue(':mat_ria_org_nica', floatval($r['mat_ria_org_nica'] ?? 0));
    $stmt->bindValue(':poda_i_jardineria', floatval($r['poda_i_jardineria'] ?? 0));
    $stmt->bindValue(':paper_i_cartr', floatval($r['paper_i_cartr'] ?? 0));
    $stmt->bindValue(':vidre', floatval($r['vidre'] ?? 0));
    $stmt->bindValue(':envasos_lleugers', floatval($r['envasos_lleugers'] ?? 0));
    $stmt->bindValue(':residus_voluminosos_fusta', floatval($r['residus_voluminosos_fusta'] ?? 0));
    $stmt->bindValue(':raee', floatval($r['raee'] ?? 0));
    $stmt->bindValue(':ferralla', floatval($r['ferralla'] ?? 0));
    $stmt->bindValue(':olis_vegetals', floatval($r['olis_vegetals'] ?? 0));
    $stmt->bindValue(':t_xtil', floatval($r['t_xtil'] ?? 0));
    $stmt->bindValue(':runes', floatval($r['runes'] ?? 0));
    $stmt->bindValue(':res_especials_en_petites', floatval($r['res_especials_en_petites'] ?? 0));
    $stmt->bindValue(':piles', floatval($r['piles'] ?? 0));
    $stmt->bindValue(':medicaments', floatval($r['medicaments'] ?? 0));
    $stmt->bindValue(':altres_recollides_selectives', floatval($r['altres_recollides_selectives'] ?? 0));
    $stmt->bindValue(':total_recollida_selectiva', floatval($r['total_recollida_selectiva'] ?? 0));
    $stmt->bindValue(':r_s_r_m_total', floatval($r['r_s_r_m_total'] ?? 0));
    $stmt->bindValue(':kg_hab_any_recollida_selectiva', floatval($r['kg_hab_any_recollida_selectiva'] ?? 0));
    $stmt->bindValue(':resta_a_dip_sit', floatval($r['resta_a_dip_sit'] ?? 0));
    $stmt->bindValue(':resta_a_incineraci', floatval($r['resta_a_incineraci'] ?? 0));
    $stmt->bindValue(':resta_a_tractament_mec_nic', floatval($r['resta_a_tractament_mec_nic'] ?? 0));
    $stmt->bindValue(':resta_sense_desglossar', floatval($r['resta_sense_desglossar'] ?? 0));
    $stmt->bindValue(':suma_fracci_resta', floatval($r['suma_fracci_resta'] ?? 0));
    $stmt->bindValue(':f_r_r_m', floatval($r['f_r_r_m'] ?? 0));
    $stmt->bindValue(':generaci_residus_municipal', floatval($r['generaci_residus_municipal'] ?? 0));
    $stmt->bindValue(':kg_hab_dia', floatval($r['kg_hab_dia'] ?? 0));
    $stmt->bindValue(':kg_hab_any', floatval($r['kg_hab_any'] ?? 0));
    $stmt->execute();
}

$db->close();
?>
