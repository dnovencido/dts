<?php
    require "db/db.php";

    function validate_receiving_office($division = []) {
        $validation_errors = [];

        if (!empty($division)) {
            $name = trim($division['name'] ?? '');
            
            // Name
            if (empty($name)) {
                $validation_errors[] = "Name is required.";
            } 

        } else {
            $validation_errors[] = "No data submitted.";
        }
    
        return $validation_errors;
    }

    function save_receiving_office($fields = [], $id = null) {
        global $conn;
        $flag = false;
    
        // Allowed columns in DB
        $allowed = [
            'name', 'last_updated', 'date_created'
         ];
    
        // Filter only allowed keys
        $data = array_intersect_key($fields, array_flip($allowed));
        
        if ($id === null) {
            // Insert
            $data['date_created'] = date("Y-m-d H:i:s");
    
            $columns = array_keys($data);
            $placeholders = implode(",", array_fill(0, count($columns), "?"));
            $sql = "INSERT INTO receiving_offices (`" . implode("`,`", $columns) . "`) VALUES ($placeholders)"; 

            $stmt = $conn->prepare($sql);
            $types = str_repeat("s", count($columns));
            $values = array_values($data);
    
            $stmt->bind_param($types, ...$values);
        } else {
            // Update
            $data['last_updated'] = date("Y-m-d H:i:s");
    
            $set = implode(", ", array_map(fn($col) => "`$col` = ?", array_keys($data)));

            $sql = "UPDATE receiving_offices SET $set WHERE id = ?";
    
            $stmt = $conn->prepare($sql);
    
            $types = str_repeat("s", count($data)) . "i"; // last one is for id
            $values = array_merge(array_values($data), [$id]);
            
            $stmt->bind_param($types, ...$values);
        }

        if($stmt->execute())
            $flag = true;
        
        return $flag;
    }    

    function get_all_receiving_offices($filter = [], $pagination = []) {
        global $conn;

        $documents = ['total' => 0, 'result' => []];

        $query = "SELECT 
                    r.*
                FROM receiving_offices r";

        $conditions = [];
        $params = [];
        $types = "";

        /* =========================
        Build Conditions
        ========================= */
        foreach ($filter as $key => $value) {

            /* =====================
            SEARCH
            ===================== */
            if ($key === 'search' && is_array($value) && count($value) >= 2) {

                $cols  = $value[0];
                $input = trim($value[1]);

                if ($input !== '') {

                    $searchParts = [];

                    foreach ($cols as $col) {

                        $searchParts[] = "$col LIKE ?";
                        $params[] = "%$input%";
                        $types .= "s";
                    }

                    if ($searchParts) {
                        $conditions[] = "(" . implode(" OR ", $searchParts) . ")";
                    }
                }

                continue;
            }

            /* =====================
            DATE RANGE
            ===================== */
            if ($key === 'date_range' && is_array($value) && count($value) >= 3) {

                $column = $value[0][0];
                $from   = trim($value[1]);
                $to     = trim($value[2]);

                if ($from !== '' && $to !== '') {

                    $conditions[] = "r.$column BETWEEN ? AND ?";
                    $params[] = $from;
                    $params[] = $to;
                    $types .= "ss";

                } elseif ($from !== '') {

                    $conditions[] = "r.$column >= ?";
                    $params[] = $from;
                    $types .= "s";

                } elseif ($to !== '') {

                    $conditions[] = "r.$column <= ?";
                    $params[] = $to;
                    $types .= "s";
                }

                continue;
            }

            /* =====================
            DYNAMIC ITEM FILTERS (AND)
            ===================== */
            if ($key === 'item' && is_array($value)) {

                foreach ($value as $item) {

                    if (
                        !isset($item['column'], $item['value']) ||
                        $item['value'] === ''
                    ) {
                        continue;
                    }

                    $column = $item['column'];
                    $input  = $item['value'];

                    /* Multiple values → IN() */
                    if (is_array($input)) {

                        $input = array_map('intval', $input);
                        $input = array_filter($input);

                        if (!empty($input)) {

                            $placeholders = implode(',', array_fill(0, count($input), '?'));

                            $conditions[] = "r.$column IN ($placeholders)";

                            foreach ($input as $id) {
                                $params[] = $id;
                                $types .= "i";
                            }
                        }

                    }
                    /* Single value → = */
                    else {

                        $conditions[] = "d.$column = ?";
                        $params[] = $input;

                        $types .= is_numeric($input) ? "i" : "s";
                    }
                }

                continue;
            }
        }

        /* =========================
        Apply WHERE
        ========================= */
        if (!empty($conditions)) {

            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        /* =========================
        Count Query
        ========================= */
        $count_query = "SELECT COUNT(*) AS total FROM (" . $query . ") AS total_records";

        $stmt = $conn->prepare($count_query);

        if ($stmt === false) {
            throw new Exception("Prepare failed (COUNT): " . $conn->error);
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        $count_result = $stmt->get_result()->fetch_assoc();

        $documents['total'] = $count_result['total'] ?? 0;

        $stmt->close();

        /* =========================
        Main Query
        ========================= */

        $query .= " ORDER BY r.id DESC";

        if (!empty($pagination)) {

            $query .= " LIMIT ?, ?";

            $params[] = (int)$pagination['offset'];
            $params[] = (int)$pagination['total_records_per_page'];

            $types .= "ii";
        }

        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            throw new Exception("Prepare failed (MAIN): " . $conn->error);
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        $result = $stmt->get_result();

        $documents['result'] = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $documents;
    }

    function view_receiving_office($id) {
        global $conn;
        $document = [];

        $query = "SELECT r.* FROM receiving_offices r WHERE r.id = ?";

        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
          
            if ($result) {
                $document = $result->fetch_array(MYSQLI_ASSOC);
            }
            $stmt->close();
        }

        return $document;
    }

    function delete_receiving_office($id) {
        global $conn;
        $flag = false;

        $stmt = $conn->prepare("SELECT id FROM `receiving_offices` WHERE id = ?");

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $stmt = $conn->prepare("DELETE FROM `receiving_offices` WHERE id = ?");
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $flag = true;
            }
        }

        return $flag;
    }
?>