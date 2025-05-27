<?php
include '../includes/errorHandler.proc.php';
include '../includes/dbConnect.proc.php';

// Peticions GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    /**
     * @author Gerard Calvo, Oriol Canellas, Agustí Lopez, Anthonella Orosco
     * @since 27 / 05 / 2025
     * @param $_GET['municipi'] -> Passa l'id del municipi.
     * @return $residus Retorna les dades del municipi.
     */
    if (isset($_GET['municipi'])) {
        $stmt = $db->prepare("SELECT d.*, m.nom FROM dades_residus d JOIN municipis m ON d.municipi_id = m.id WHERE municipi_id = :municipi_id");
        $stmt->bindValue(':municipi_id', $_GET['municipi'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        $dades = [];
        while ($fila = $result->fetchArray(SQLITE3_ASSOC)) {
            $dades[] = $fila;
        }
        echo json_encode($dades);
    /**
     * @author Gerard Calvo, Oriol Canellas, Agustí Lopez, Anthonella Orosco
     * @since 27 / 05 / 2025
     * @param $_GET['comarca'] -> Passa l'id de la comarca.
     * @return $municipis -> Retorna els municipis de la comaraca passada.
     */        
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
    /**
     * @author Gerard Calvo, Oriol Canellas, Agustí Lopez, Anthonella Orosco
     * @since 27 / 05 / 2025
     * @return $municipis Retorna tots els municipis.
     */
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
