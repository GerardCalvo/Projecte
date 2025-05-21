<?php
include '../includes/errorHandler.proc.php';
include '../includes/dbConnect.proc.php';

// Peticions GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $result = $db->query("SELECT * FROM comarques ORDER BY id");
        $comarques = [];
        while ($comarca = $result->fetchArray(SQLITE3_ASSOC)){
            $comarques[] = $comarca;
        }
        //header('Content-Type: application/json');
        echo json_encode($comarques);
}