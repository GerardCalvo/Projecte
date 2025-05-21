<?php
include '../includes/errorHandler.proc.php';
include '../includes/dbConnect.proc.php';

// Peticions GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
    if(isset($_GET['filtreGenere'])) {
        $stmt = $db->prepare("SELECT *, gen_nom FROM pellicules p join generes g ON p.gen_id = g.gen_id WHERE p.gen_id = :id ORDER BY p.pel_id ");
        $stmt->bindValue(':id', $_GET['filtreGenere'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        $pellicules = [];
        while ($pellicula = $result->fetchArray(SQLITE3_ASSOC)){
            $pellicules[] = $pellicula;
        }
        //header('Content-Type: application/json');
        echo json_encode($pellicules);

    } else if(isset($_GET['id'])) {
        $stmt = $db->prepare("SELECT *, gen_nom FROM pellicules p join generes g ON p.gen_id = g.gen_id WHERE pel_id = :id");
        $stmt->bindValue(':id', $_GET['id'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        $producte = [];
        if ($producte = $result->fetchArray(SQLITE3_ASSOC)){
            $producte = [
                "pel_id" => $producte['pel_id'],
                "pel_titol" => $producte['pel_titol'],
                "pel_director" => $producte['pel_director'],
                "pel_protagonista" => $producte['pel_protagonista'],
                "pel_any" => $producte['pel_any'],
                "pel_puntuacio" => $producte['pel_puntuacio'],
                "pel_conceptes" => $producte['pel_conceptes'],
                "gen_id" => $producte['gen_id'],
                "gen_nom" => $producte['gen_nom']
            ];
        }

        //header('Content-Type: application/json');
        echo json_encode($producte);
    } else if(isset($_GET['filtreAny'])) {
        $stmt = $db->prepare("SELECT *, gen_nom FROM pellicules p join generes g ON p.gen_id = g.gen_id WHERE p.pel_any = :any ORDER BY p.pel_id ");
        $stmt->bindValue(':any', $_GET['filtreAny'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        $pellicules = [];
        while ($pellicula = $result->fetchArray(SQLITE3_ASSOC)){
            $pellicules[] = $pellicula;
        }
        //header('Content-Type: application/json');
        echo json_encode($pellicules);
    } else {
        $result = $db->query("SELECT *, gen_nom FROM pellicules p join generes g ON p.gen_id = g.gen_id ORDER BY pel_id");
        $pellicules = [];
        while ($pellicula = $result->fetchArray(SQLITE3_ASSOC)){
            $pellicules[] = $pellicula;
        }
        //header('Content-Type: application/json');
        echo json_encode($pellicules);
    }
// Peticions POST
} else if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['pel_titol']) && isset($input['pel_director']) && isset($input['pel_protagonista']) && isset($input['pel_any']) && isset($input['pel_puntuacio']) && isset($input['pel_conceptes'])&& isset($input['gen_id'])) {
        $stmt = $db->prepare("INSERT INTO pellicules (pel_titol, pel_director, pel_protagonista, pel_any, pel_puntuacio, pel_conceptes, gen_id) VALUES (:titol, :dir, :prota, :any, :puntua, :concept, :genId)");
        $stmt->bindValue(':titol', $input['pel_titol'], SQLITE3_TEXT);
        $stmt->bindValue(':dir', $input['pel_director'], SQLITE3_TEXT);
        $stmt->bindValue(':prota', $input['pel_protagonista'], SQLITE3_TEXT);
        $stmt->bindValue(':any', $input['pel_any'], SQLITE3_INTEGER);
        $stmt->bindValue(':puntua', $input['pel_puntuacio'], SQLITE3_INTEGER);
        $stmt->bindValue(':concept', $input['pel_conceptes'], SQLITE3_TEXT);
        $stmt->bindValue(':genId', $input['gen_id'], SQLITE3_INTEGER);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["success" => "Producte afegit correctament"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error al inserir el producte"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Falten camps obligatoris"]);
    }
// Peticions PUT
} else if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    $id = $_GET['id'];

    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "Falta l'identificador del producte"]);
        exit;
    }

    $stmt = $db->prepare("UPDATE pellicules SET 
        pel_titol = :titol,
        pel_director = :dir,
        pel_protagonista = :prota,
        pel_any = :any,
        pel_puntuacio = :puntua,
        pel_conceptes = :concept,
        gen_id = :genId
        WHERE pel_id = :id
    ");

    $stmt->bindValue(':titol', $input['pel_titol'], SQLITE3_TEXT);
    $stmt->bindValue(':dir', $input['pel_director'], SQLITE3_TEXT);
    $stmt->bindValue(':prota', $input['pel_protagonista'], SQLITE3_TEXT);
    $stmt->bindValue(':any', $input['pel_any'], SQLITE3_TEXT);
    $stmt->bindValue(':puntua', $input['pel_puntuacio'], SQLITE3_TEXT);
    $stmt->bindValue(':concept', $input['pel_conceptes'], SQLITE3_TEXT);
    $stmt->bindValue(':genId', $input['gen_id'], SQLITE3_TEXT);
    $stmt->bindValue(':id', $id, SQLITE3_TEXT);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["success" => "Producte actualitzat correctament"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Error al actualitzar el producte"]);
    }

// Peticions DELETE
} else if($_SERVER['REQUEST_METHOD'] == 'DELETE') {

    $id = $_GET['id'] ?? null;
    if ($id === null) {
        parse_str(file_get_contents("php://input"), $params);
        $id = $params['id'] ?? null;
    }
    if ($id !== null) {
        $stmt = $db->prepare("DELETE FROM pellicules WHERE pel_id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        if ($stmt->execute()) {
            echo json_encode(["success" => "Producte eliminat correctament"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "No s'ha pogut eliminar el producte"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Falta l'identificador del producte"]);
    }
    
} else {
    http_response_code(400);
    echo json_encode(["error" => "Petició no acceptada"]);
}
?>
