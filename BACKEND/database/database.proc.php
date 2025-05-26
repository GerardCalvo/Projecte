<?php
// Mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ruta base de la base de datos
$db_path = __DIR__ . '/residus.db';

// Si no existe la base, crearla y las tablas
if (!file_exists($db_path)) {
    echo "🛠 Base de dades no existeix. Creant base de dades i taules...\n";
    $db = new SQLite3($db_path);
    if (!$db) die("❌ No s'ha pogut crear la base de dades.");

    $tablas = [
        "CREATE TABLE IF NOT EXISTS comarques (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nom TEXT UNIQUE
        )",
        "CREATE TABLE IF NOT EXISTS municipis (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            codi_municipi TEXT UNIQUE,
            nom TEXT,
            comarca_id INTEGER,
            FOREIGN KEY(comarca_id) REFERENCES comarques(id)
        )",
        "CREATE TABLE IF NOT EXISTS dades_residus (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            any INTEGER,
            municipi_id INTEGER,
            comarca_id INTEGER,
            poblaci INTEGER,
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
        )",
        "CREATE TABLE IF NOT EXISTS dades_geo (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            municipi_id INTEGER,
            prov_ncia TEXT,
            utm_x REAL,
            utm_y REAL,
            longitud REAL,
            latitud REAL,
            geocoded_type TEXT,
            geocoded_coordinates TEXT,
            FOREIGN KEY(municipi_id) REFERENCES municipis(id)
        )"
    ];

    foreach ($tablas as $sql) {
        if (!$db->exec($sql)) {
            die("❌ Error creant taula: " . $db->lastErrorMsg());
        }
    }
    chmod($db_path, 0666);
    echo "✔ Base de dades i taules creades amb èxit.\n\n";
} else {
    $db = new SQLite3($db_path);
    echo "✔ Base de dades oberta: $db_path\n\n";
}

// Funció per descarregar dades amb offset
function descarregarDades($url, $nom) {
    echo "🔄 Descarregant dades de $nom...\n";
    $tots = [];
    $offset = 0;
    $limit = 1000;

    while (true) {
        $endpoint = "$url?\$limit=$limit&\$offset=$offset";
        $json = file_get_contents($endpoint);
        if (!$json) {
            echo "⚠️ No s'ha pogut descarregar des de $endpoint\n";
            break;
        }

        $dades = json_decode($json, true);
        if (!$dades || count($dades) === 0) break;

        $tots = array_merge($tots, $dades);
        echo "📦 Recuperats " . count($dades) . " registres (offset $offset)\n";
        $offset += $limit;
        usleep(100000); // pausa per no saturar l'API
    }

    echo "✔ Total $nom descarregats: " . count($tots) . " registres.\n";
    return $tots;
}

// Descarregar datasets
$rows_residus = descarregarDades("https://analisi.transparenciacatalunya.cat/resource/69zu-w48s.json", "residus");
$rows_geo = descarregarDades("https://analisi.transparenciacatalunya.cat/resource/wpyq-we8x.json", "geogràfiques");

// Preparar inserció de dades_residus
echo "\n📝 Inserint dades de residus...\n";
$stmt = $db->prepare("INSERT INTO dades_residus (
    any, municipi_id, comarca_id, poblaci,
    autocompostatge, mat_ria_org_nica, poda_i_jardineria, paper_i_cartr,
    vidre, envasos_lleugers, residus_voluminosos_fusta, raee, ferralla,
    olis_vegetals, t_xtil, runes, res_especials_en_petites, piles, medicaments,
    altres_recollides_selectives, total_recollida_selectiva, r_s_r_m_total,
    kg_hab_any_recollida_selectiva, resta_a_dip_sit, resta_a_incineraci,
    resta_a_tractament_mec_nic, resta_sense_desglossar, suma_fracci_resta,
    f_r_r_m, generaci_residus_municipal, kg_hab_dia, kg_hab_any)
    VALUES (
    :any, :municipi_id, :comarca_id, :poblacio,
    :autocompostatge, :mat_ria_org_nica, :poda_i_jardineria, :paper_i_cartr,
    :vidre, :envasos_lleugers, :residus_voluminosos_fusta, :raee, :ferralla,
    :olis_vegetals, :t_txtil, :runes, :res_especials_en_petites, :piles, :medicaments,
    :altres_recollides_selectives, :total_recollida_selectiva, :r_s_r_m_total,
    :kg_hab_any_recollida_selectiva, :resta_a_dip_sit, :resta_a_incineraci,
    :resta_a_tractament_mec_nic, :resta_sense_desglossar, :suma_fracci_resta,
    :f_r_r_m, :generaci_residus_municipal, :kg_hab_dia, :kg_hab_any)");

$clau = fn($k, $r) => isset($r[$k]) ? floatval($r[$k]) : 0;
$insertats = 0;

foreach ($rows_residus as $r) {
    $comarca = $r['comarca'] ?? '';
    $municipi = $r['municipi'] ?? '';
    $codi = $r['codi_municipi'] ?? '';
    if (!$comarca || !$municipi || !$codi) continue;

    // Inserir comarca
    $db->exec("INSERT OR IGNORE INTO comarques (nom) VALUES ('" . SQLite3::escapeString($comarca) . "')");
    $comarca_id = $db->querySingle("SELECT id FROM comarques WHERE nom = '" . SQLite3::escapeString($comarca) . "'");

    // Inserir municipi
    $db->exec("INSERT OR IGNORE INTO municipis (codi_municipi, nom, comarca_id) VALUES (
        '" . SQLite3::escapeString($codi) . "', '" . SQLite3::escapeString($municipi) . "', $comarca_id)");
    $municipi_id = $db->querySingle("SELECT id FROM municipis WHERE codi_municipi = '" . SQLite3::escapeString($codi) . "'");

    $stmt->bindValue(':any', intval($r['any'] ?? 0));
    $stmt->bindValue(':municipi_id', $municipi_id);
    $stmt->bindValue(':comarca_id', $comarca_id);
    $stmt->bindValue(':poblacio', intval($r['poblaci'] ?? 0));

    $claus = [
        'autocompostatge', 'mat_ria_org_nica', 'poda_i_jardineria', 'paper_i_cartr',
        'vidre', 'envasos_lleugers', 'residus_voluminosos_fusta', 'raee', 'ferralla',
        'olis_vegetals', 't_xtil', 'runes', 'res_especials_en_petites', 'piles',
        'medicaments', 'altres_recollides_selectives', 'total_recollida_selectiva',
        'r_s_r_m_total', 'kg_hab_any_recollida_selectiva', 'resta_a_dip_sit',
        'resta_a_incineraci', 'resta_a_tractament_mec_nic', 'resta_sense_desglossar',
        'suma_fracci_resta', 'f_r_r_m', 'generaci_residus_municipal', 'kg_hab_dia', 'kg_hab_any'
    ];

    foreach ($claus as $k) {
        $stmt->bindValue(":$k", $clau($k, $r));
    }

    $stmt->execute();
    $insertats++;
}
echo "📊 Registres residus inserits: $insertats\n";

// Inserir dades geogràfiques
echo "\n🗺 Inserint dades geogràfiques...\n";
$stmt_geo = $db->prepare("INSERT INTO dades_geo (
    municipi_id, prov_ncia, utm_x, utm_y, longitud, latitud, geocoded_type, geocoded_coordinates)
    VALUES (:municipi_id, :prov_ncia, :utm_x, :utm_y, :longitud, :latitud, :geocoded_type, :geocoded_coordinates)");

$insertats_geo = 0;
foreach ($rows_geo as $r) {
    $codi = $r['codi_municipi'] ?? '';
    if (!$codi) continue;

    $municipi_id = $db->querySingle("SELECT id FROM municipis WHERE codi_municipi = '" . SQLite3::escapeString($codi) . "'");
    if (!$municipi_id) continue;

    $geo = $r['geocoded_column'] ?? null;

    $stmt_geo->bindValue(':municipi_id', $municipi_id);
    $stmt_geo->bindValue(':prov_ncia', $r['prov_ncia'] ?? null);
    $stmt_geo->bindValue(':utm_x', isset($r['utm_x']) ? floatval($r['utm_x']) : null);
    $stmt_geo->bindValue(':utm_y', isset($r['utm_y']) ? floatval($r['utm_y']) : null);
    $stmt_geo->bindValue(':longitud', isset($r['longitud']) ? floatval($r['longitud']) : null);
    $stmt_geo->bindValue(':latitud', isset($r['latitud']) ? floatval($r['latitud']) : null);
    $stmt_geo->bindValue(':geocoded_type', is_array($geo) ? ($geo['type'] ?? null) : null);
    $stmt_geo->bindValue(':geocoded_coordinates', is_array($geo) ? json_encode($geo['coordinates'] ?? null) : null);
    $stmt_geo->execute();
    $insertats_geo++;
}
echo "✔ Dades geogràfiques inserides: $insertats_geo registres.\n";

$db->close();
echo "\n✅ Procés completat!\n";
