<?php
    function abort_404($message = null, $backUrl = "/") {

        http_response_code(404);

        $title   = "404 - Not Found";
        $message = $message ?? "The requested resource could not be found.";

        include __DIR__ . "/shared/errors/docs_404.php";
        exit;
    }
?>