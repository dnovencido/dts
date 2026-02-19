<?php
require "db/db.php";

function login_account($email, $password) {
    global $conn;
    $user = [];

    $sql = "SELECT id, fname, email, password FROM users WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!empty($row)) {
        $hashed_password = md5(md5($row['id'] . $password));
        if ($hashed_password === $row['password']) {
            $user = [
                'id'   => $row['id'],
                'fname' => $row['fname']
            ];
        }
    }

    return $user;
}
?>
