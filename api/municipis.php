<?php
include '../includes/errorHandler.proc.php';
include '../includes/dbConnect.proc.php';

// Peticions GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $result = $db->query("SELECT * FROM municipis ORDER BY id");
        $municipis = [];
        while ($municipi = $result->fetchArray(SQLITE3_ASSOC)){
            $municipis[] = $municipi;
        }
        //header('Content-Type: application/json');
        echo json_encode($municipis);
}