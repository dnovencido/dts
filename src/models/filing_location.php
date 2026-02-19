<?php
    require "db/db.php";

    function validate_filing_location($filing_location = []) {
        $validation_errors = [];

        if (!empty($filing_location)) {
            $name = trim($filing_location['name'] ?? '');
            
            // Name
            if (empty($name)) {
                $validation_errors[] = "Name is required.";
            } 
        } else {
            $validation_errors[] = "No data submitted.";
        }
    
        return $validation_errors;
    }

    function save_filing_location($fields = [], $id = null) {
        global $conn;

        $flag = false;

        // Allowed columns
        $allowed = [
            'name', 'last_updated', 'date_created'
        ];

        // Filter only allowed keys
        $data = array_intersect_key($fields, array_flip($allowed));
        
        if ($id === null) {
            $data['date_created'] = date("Y-m-d H:i:s");
            $columns = array_keys($data);
            $placeholders = implode(",", array_fill(0, count($columns), "?"));

            $sql = "INSERT INTO filing_locations (`" .
                implode("`,`", $columns) .
                "`) VALUES ($placeholders)";

            $stmt = $conn->prepare($sql);

            $types  = str_repeat("s", count($columns));
            $values = array_values($data);

            $stmt->bind_param($types, ...$values);
        } else {
            $data['last_updated'] = date("Y-m-d H:i:s");

            $set = implode(", ", array_map(fn($col) => "`$col` = ?", array_keys($data)));

            $sql = "UPDATE filing_locations SET $set WHERE id = ?";

            $stmt = $conn->prepare($sql);

            $types  = str_repeat("s", count($data)) . "i";
            $values = array_merge(array_values($data), [$id]);

            $stmt->bind_param($types, ...$values);
        }

        if ($stmt->execute())
            $flag = true;

        $stmt->close();

        return $flag;
    }
    
    function get_all_filing_locations($filter = [], $pagination = []) {
        global $conn;

        $documents = ['total' => 0, 'result' => []];

        $query = "SELECT f.* FROM filing_locations f";

        $conditions = [];
        $params = [];
        $types = "";

        // Build Conditions
        foreach ($filter as $key => $value) {
            // Search
            if($key === 'search' && is_array($value) && count($value) >= 2) {
                $cols  = $value[0];
                $input = trim($value[1]);

                if($input !== '') {
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
        }

        // Apply Where Conditions
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        // Count query for pagination
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

        $document_types['total'] = $count_result['total'] ?? 0;

        $stmt->close();

        // Main query with order and pagination
        $query .= " ORDER BY f.id DESC";

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

        $filing_location['result'] = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $filing_location;
    }

    function view_filing_location($id) {
        global $conn;
        $filing_location = [];

        $query = "SELECT f.* FROM filing_locations f WHERE f.id = ?";

        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
          
            if ($result) {
                $filing_location = $result->fetch_array(MYSQLI_ASSOC);
            }
            $stmt->close();
        }

        return $filing_location;
    }
    
    function delete_filing_location($id) {
        global $conn;
        $flag = false;

        $stmt = $conn->prepare("SELECT id FROM `filing_locations` WHERE id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $stmt = $conn->prepare("DELETE FROM `filing_locations` WHERE id = ?");
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