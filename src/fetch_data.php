<?php
    include "models/document.php";

    header('Content-Type: application/json');
    $range = $_GET['range'] ?? 6;
    $year  = $_GET['year'] ?? date('Y');
    if ($_GET['chart'] == 1)
        echo json_encode(count_documents($range));

     
    if ($_GET['chart'] == 2) {
        echo json_encode(count_documents_per_type(
            $_GET['year'] ?? null,
            $_GET['category'] ?? null
        ));
    }
?>
