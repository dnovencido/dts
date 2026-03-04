<?php
    require "db/db.php";
    function get_assigned_office($user_id) {
        global $conn;

        $stmt = $conn->prepare("SELECT receiving_office FROM assigned_office WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $row['receiving_office'];
        }

        return '';
    }

    function update_assigned_office($user_id, $receiving_office) {
        global $conn;

        // Check if record exists
        $check = $conn->prepare("SELECT user_id FROM assigned_office WHERE user_id = ?");
        $check->bind_param("i", $user_id);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE assigned_office SET receiving_office = ? WHERE user_id = ?");
            $stmt->bind_param("si", $receiving_office, $user_id);
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO assigned_office (user_id, receiving_office) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $receiving_office);
        }

        return $stmt->execute();
    }

    function get_all_assigned_offices() {
        global $conn;

        $stmt = $conn->prepare("
            SELECT 
                r.*
            FROM receiving_offices r
            ORDER BY r.name ASC
        ");

        $stmt->execute();
        $result = $stmt->get_result();

        return [
            'total'  => $result->num_rows,
            'result' => $result->fetch_all(MYSQLI_ASSOC)
        ];
    }
?>