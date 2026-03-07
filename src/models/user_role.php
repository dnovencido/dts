<?php
    require "db/db.php";

    function update_user_role($user_id, $role_id) {
        global $conn;
        $flag = false;

        // Remove old role assignment
        $delete_query = "DELETE FROM user_roles WHERE user_id = ?";
        if($stmt = $conn->prepare($delete_query)) {
            $stmt->bind_param("i", $user_id);
            if($stmt->execute()) {
                $stmt->close();

                // Only insert new role if role_id is not empty
                if (!empty($role_id)) {
                    $insert_query = "INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)";
                    if ($stmt = $conn->prepare($insert_query)) {
                        $stmt->bind_param("ii", $user_id, $role_id);
                        if ($stmt->execute()) {
                            $flag = true;
                        }
                        $stmt->close();
                    }
                } else {
                    // No new role assigned, but deletion succeeded
                    $flag = true;
                }
            } else {
                $stmt->close();
            }
        }

        return $flag;
    }

    function get_user_roles($user_id, $type = 'names') {
        global $conn;
        $roles = [];

        // Choose which column to select based on $type
        $column = ($type === 'ids') ? 'r.id' : 'r.role_name';
        $sql = "SELECT $column AS role_value 
                FROM roles r 
                JOIN user_roles ur ON r.id = ur.role_id 
                WHERE ur.user_id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $roles[] = $row['role_value'];
            }

            $stmt->close();
        }

        return $roles;
    }

    function user_has_role($user_id, array $required_roles) {
        // Fetch roles as names (default)
        $user_roles = get_user_roles($user_id, 'names');

        // Check if any of the required roles exist in user roles
        return count(array_intersect($required_roles, $user_roles)) > 0;
    }
?>