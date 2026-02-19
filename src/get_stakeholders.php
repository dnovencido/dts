<?php
    include "session.php";
    include "models/division.php";
    
    $result = [];

    if(array_key_exists("division_ids", $_POST)) {
        $divisions =  get_division_heads_by_ids($_POST['division_ids']);  
        $result['divisions'] = $divisions;
        echo json_encode($result);
    }

?>