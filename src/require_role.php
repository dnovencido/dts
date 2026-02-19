<?php
    include "models/user_role.php";

    function require_role($user_id, $roles, $module, $redirect = "dashboard") {
        if (!user_has_role($user_id, $roles)) {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'You do not have access to the <strong>' . ucfirst($module) . ' module</strong>. Please contact the system administrator.'
            ];
            header("Location: " . $redirect);
            exit;
        }
    }
?>