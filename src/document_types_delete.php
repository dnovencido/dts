<?php
    include "session.php";
    include "models/document_type.php";
    
    $result = [];

    if(array_key_exists("id", $_GET)) {
        $result['deleted'] = false;
        $is_deleted = delete_document_type($_GET['id']);

        if($is_deleted) {
            $result['deleted'] = true;
        } 
    }

    echo json_encode($result);
?>