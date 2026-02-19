<?php
    include "session.php";

    if (array_key_exists("logout", $_GET)) {
        session_destroy();
        header("Location: login");
    }
?>