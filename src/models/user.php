<?php
    require "db/db.php";

    function get_all_users($filter = [], $pagination = []) {
        global $conn;
    
        $users = ['total' => 0, 'result' => []];
    
        $query = "SELECT 
                    u.id, u.fname, u.mname, u.lname, u.email, u.employee_id, u.position, u.date_created
                  FROM users u";
    
        $conditions = [];
        $params = [];
        $types = "";
    
        // Build WHERE conditions
        if (!empty($filter)) {
            foreach ($filter as $column => $value) {
                if ($column === "search" && is_array($value)) {
                    $searchCols = $value[0] ?? [];
                    $searchTerm = $value[1] ?? "";
    
                    $searchParts = [];
                    foreach ($searchCols as $col) {
                        $searchParts[] = "$col LIKE ?";
                        $params[] = "%$searchTerm%";
                        $types .= "s";
                    }
                    if ($searchParts) {
                        $conditions[] = "(" . implode(" OR ", $searchParts) . ")";
                    }
                } else {
                    $conditions[] = "$column = ?";
                    $params[] = $value;
                    $types .= "s";
                }
            }
        }
    
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
    
        // Count query for pagination
        $count_query = "SELECT COUNT(*) AS total FROM (" . $query . ") AS total_records";
        $stmt = $conn->prepare($count_query);
        if ($stmt === false) {
            throw new Exception("Prepare failed (COUNT): " . $conn->error . "\nSQL: " . $count_query);
        }
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $count_result = $stmt->get_result()->fetch_assoc();
        $registrations['total'] = $count_result['total'] ?? 0;
        $stmt->close();
    
        // Main query
        $query .= " ORDER BY u.id DESC";
        if (!empty($pagination)) {
            $query .= " LIMIT ?, ?";
            $params[] = (int)$pagination['offset'];
            $params[] = (int)$pagination['total_records_per_page'];
            $types .= "ii";
        }
    
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            throw new Exception("Prepare failed (MAIN): " . $conn->error . "\nSQL: " . $query);
        }
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $registrations['result'] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    
        return $registrations;
    }

    function update_profile($user_id, $fname, $mname, $lname, $employee_id, $email, $position, $password) {
        global $conn;
        $flag = false;

        // Get current user record to retrieve the existing password
        $current_password = '';
        $query = "SELECT password FROM users WHERE id = ?";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($current_password);
            $stmt->fetch();
            $stmt->close();
        }

        // Apply custom password hashing rule only if a new password is entered
        if (!empty($password)) {
            $hashed_password = md5(md5($user_id . $password));
        } else {
            $hashed_password = $current_password;
        }

        // Update the user profile
        $update_sql = "UPDATE users 
                       SET fname = ?, mname = ?, lname = ?, employee_id = ?, email = ?, position = ?, password = ?
                       WHERE id = ?";

        if ($stmt = $conn->prepare($update_sql)) {
            $stmt->bind_param("sssssssi", $fname, $mname, $lname, $employee_id, $email, $position, $hashed_password, $user_id);
            if ($stmt->execute()) {
                $flag = true;
            }
            $stmt->close();
        }

        return $flag;
    }

    function get_user_role_name($user_id) {
        global $conn;
        $role_name = '';

        $query = "SELECT r.role_name 
                  FROM roles r
                  JOIN user_roles ur ON r.id = ur.role_id
                  WHERE ur.user_id = ? LIMIT 1";

        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($role_name);
            $stmt->fetch();
            $stmt->close();
        }

        return $role_name;
    }
?>