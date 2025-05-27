<?php
include '../includes/errorHandler.proc.php';
include '../includes/dbConnect.proc.php';

// Peticions GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    /**
     * @author Gerard Calvo, Oriol Canellas, Agustí Lopez, Anthonella Orosco
     * @since 27 / 05 / 2025
     * @return $comarques Retorna totes les comarques.
     */
        $result = $db->query("SELECT * FROM comarques ORDER BY id");
        $comarques = [];
        while ($comarca = $result->fetchArray(SQLITE3_ASSOC)){
            $comarques[] = $comarca;
        }
        //header('Content-Type: application/json');
        echo json_encode($comarques);
    
}