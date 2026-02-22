<?php
    require "db/db.php";
    include_once "config/default.php";

    function check_existing_email($email) {
        global $conn;
        $flag = false;

        $sql = "SELECT id FROM users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $flag = true;
        }

        $stmt->close();
        return $flag;
    }

    function save_registration($fname, $mname, $lname, $email, $password, $employee_id, $position) {
        global $conn;
        $user = [];

        // Generate verification token
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+24 hours"));

        $date_created = date('Y-m-d H:i:s');
        $sql = "INSERT INTO users (fname, mname, lname, email, password, employee_id, position, date_created, verification_token, token_expires, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param( "ssssssssss", $fname, $mname, $lname, $email, $password, $employee_id, $position, $date_created, $token, $expires);

        if ($stmt->execute()) {
            $id = $conn->insert_id;
            $stmt->close();

            $encrypted_password = md5(md5($id . $password));
            $sql = "UPDATE users SET password = ? WHERE id = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $encrypted_password, $id);

            if ($stmt->execute()) {
                $stmt->close();

                $sql = "SELECT id, fname, email FROM users WHERE id = ? AND password = ? LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $id, $encrypted_password);
                $stmt->execute();

                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $user = [
                        'id' => $row['id'],
                        'fname' => $row['fname'],
                        'email' => $row['email'],
                        'token' => $token
                    ];
                }
                $stmt->close();
            }
        }

        return $user;
    }
?>
