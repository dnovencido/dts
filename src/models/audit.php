<?php
    function log_audit($action, $table, $record_id = null) {
        global $conn;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user_id = $_SESSION['id'] ?? null;
        $ip      = $_SERVER['REMOTE_ADDR'] ?? null;
        $agent   = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $created = date("Y-m-d H:i:s");

        $stmt = $conn->prepare("
            INSERT INTO audit_trails
            (user_id, action, table_name, record_id, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ississs",
            $user_id,
            $action,
            $table,
            $record_id,
            $ip,
            $agent,
            $created
        );

        $stmt->execute();
        $stmt->close();
    }

    function get_all_audit_trails($filter = [], $pagination = []) {

        global $conn;

        $audit_trails = [
            'total'  => 0,
            'result' => []
        ];
        $query = "
            SELECT at.*, u.fname, u.mname, u.lname,
            CONCAT(
                u.fname, ' ',
                IF(u.mname IS NOT NULL AND u.mname != '', 
                    CONCAT(u.mname, ' '), 
                    ''
                ),
                u.lname
            ) AS name
            FROM audit_trails at
            LEFT JOIN users u ON at.user_id = u.id
        ";

        $conditions = [];
        $params     = [];
        $types      = "";

        /* =========================================
        BUILD FILTERS
        ========================================= */
        foreach ($filter as $key => $value) {

            /* ---------- SEARCH ---------- */
            if ($key === 'search' && is_array($value) && count($value) >= 2) {

                $columns = $value[0];
                $input   = trim($value[1]);

                if ($input !== '') {

                    $searchParts = [];

                    foreach ($columns as $col) {

                        // 🔥 ADD THE PLACEHOLDER HERE
                        $searchParts[] = "$col LIKE ?";

                        $params[] = "%$input%";
                        $types   .= "s";
                    }

                    if (!empty($searchParts)) {
                        $conditions[] = "(" . implode(" OR ", $searchParts) . ")";
                    }
                }

                continue;
            }
            /* ---------- DATE RANGE ---------- */
            if ($key === 'date_range' && is_array($value) && count($value) >= 3) {

                $column = $value[0][0];
                $from   = trim($value[1]);
                $to     = trim($value[2]);

                if ($from && $to) {
                    $conditions[] = "at.$column BETWEEN ? AND ?";
                    $params[] = $from;
                    $params[] = $to;
                    $types   .= "ss";
                } elseif ($from) {
                    $conditions[] = "at.$column >= ?";
                    $params[] = $from;
                    $types   .= "s";
                } elseif ($to) {
                    $conditions[] = "at.$column <= ?";
                    $params[] = $to;
                    $types   .= "s";
                }

                continue;
            }

            /* ---------- ITEM FILTER ---------- */
            if ($key === 'item' && is_array($value)) {

                foreach ($value as $item) {

                    if (!isset($item['column'], $item['value']) || $item['value'] === '') {
                        continue;
                    }

                    $column = $item['column'];
                    $input  = $item['value'];

                    /* NORMAL COLUMN FILTER */
                    if (is_array($input)) {

                        $placeholders = implode(',', array_fill(0, count($input), '?'));
                        $conditions[] = "at.$column IN ($placeholders)";

                        foreach ($input as $val) {
                            $params[] = $val;
                            $types   .= is_numeric($val) ? "i" : "s";
                        }

                    } else {

                        $conditions[] = "at.$column = ?";
                        $params[] = $input;
                        $types   .= is_numeric($input) ? "i" : "s";
                    }
                }

                continue;
            }
        }

        /* =========================================
        APPLY WHERE
        ========================================= */
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        /* =========================================
        COUNT TOTAL (SAFE)
        ========================================= */
        $countQuery = "SELECT COUNT(*) AS total FROM ($query) AS total_records";

        $stmt = $conn->prepare($countQuery);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $countResult = $stmt->get_result()->fetch_assoc();
        $documents['total'] = $countResult['total'] ?? 0;
        $stmt->close();

        /* =========================================
        FORCE PAGINATION (ALWAYS)
        ========================================= */
        $limit  = $pagination['total_records_per_page'] ?? 20;
        $offset = $pagination['offset'] ?? 0;

        $query .= " ORDER BY at.id DESC LIMIT ?, ?";
        $params[] = (int)$offset;
        $params[] = (int)$limit;
        $types   .= "ii";

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $audit_trails['result'][] = $row;
        }

        $stmt->close();

        return $audit_trails;
    }


    function view_audit_trail($id) {
        global $conn;
        $audit_trail = [];

        $query = "
            SELECT at.*, 
            CONCAT(
                u.fname, ' ',
                IF(u.mname IS NOT NULL AND u.mname != '', 
                    CONCAT(u.mname, ' '), 
                    ''
                ),
                u.lname
            ) AS name
            FROM audit_trails at
            LEFT JOIN users u ON at.user_id = u.id WHERE at.id = ?
        ";

        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
          
            if ($result) {
                $audit_trail = $result->fetch_array(MYSQLI_ASSOC);
            }
            $stmt->close();
        }

        return $audit_trail;
    }
?>