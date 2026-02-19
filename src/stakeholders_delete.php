<?php
    include "session.php";
    include "models/stakeholder.php";
    
    $result = [];

    if(array_key_exists("id", $_GET)) {
        $result['deleted'] = false;
        $is_deleted = delete_stakeholder($_GET['id']);

        if($is_deleted) {
            $result['deleted'] = true;
        } 
    }

    echo json_encode($result);
?>