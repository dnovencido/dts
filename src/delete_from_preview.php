<?php
    include "session.php";
    include "models/document.php";
    
    $result = [];

    if(array_key_exists("category", $_GET)) {
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'text' => 'You have successfully deleted the document.'
        ];

        if($_GET['category'] === "outgoing") {
            header("Location: /documents/outgoing");
        } else {
            header("Location: /documents/incoming");
        }
    }

?>
