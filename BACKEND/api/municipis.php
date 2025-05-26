<?php
include '../includes/errorHandler.proc.php';
include '../includes/dbConnect.proc.php';

// Peticions GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Si s'ha passat un ID de municipi per obtenir les dades de residus
    if (isset($_GET['municipi'])) {
        $stmt = $db->prepare("SELECT d.*, m.nom FROM dades_residus d JOIN municipis m ON d.municipi_id = m.id WHERE municipi_id = :municipi_id");
        $stmt->bindValue(':municipi_id', $_GET['municipi'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        $dades = [];
        while ($fila = $result->fetchArray(SQLITE3_ASSOC)) {
            $dades[] = $fila;
        }
        echo json_encode($dades);
            
    }else if (isset($_GET['comarca'])){
    $stmt = $db->prepare("SELECT * FROM municipis WHERE comarca_id = :comarca ORDER BY id");
        $stmt->bindValue(':comarca', $_GET['comarca'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        $municipis = [];
        while ($municipi = $result->fetchArray(SQLITE3_ASSOC)){
            $municipis[] = $municipi;
        }
        //header('Content-Type: application/json');
        echo json_encode($municipis);
    }else {
        $result = $db->query("SELECT * FROM municipis ORDER BY id");
        $municipis = [];
        while ($municipi = $result->fetchArray(SQLITE3_ASSOC)){
            $municipis[] = $municipi;
        }
        //header('Content-Type: application/json');
        echo json_encode($municipis);
    }
}
