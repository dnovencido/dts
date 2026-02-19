<?php
    require "db/db.php";

    function validate_document_type($document_type = []) {
        $validation_errors = [];

        if (!empty($document_type)) {
            $title = trim($document_type['name'] ?? '');
            $document_type = $document_type['type'] ?? '';
            
            // Title
            if (empty($title)) {
                $validation_errors[] = "Title is required.";
            } 

            // Document Type
            if (empty($document_type)) {
                $validation_errors[] = "Document Type is required.";
            } 
        } else {
            $validation_errors[] = "No data submitted.";
        }
    
        return $validation_errors;
    }

    function save_document_type($fields = [], $id = null) {
        global $conn;

        $flag = false;

        // Allowed columns
        $allowed = [
            'name', 'type', 'last_updated', 'date_created'
        ];

        // Filter only allowed keys
        $data = array_intersect_key($fields, array_flip($allowed));
        
        if ($id === null) {
            $data['date_created'] = date("Y-m-d H:i:s");
            $columns = array_keys($data);
            $placeholders = implode(",", array_fill(0, count($columns), "?"));

            $sql = "INSERT INTO document_types (`" .
                implode("`,`", $columns) .
                "`) VALUES ($placeholders)";

            $stmt = $conn->prepare($sql);

            $types  = str_repeat("s", count($columns));
            $values = array_values($data);

            $stmt->bind_param($types, ...$values);
        } else {
            $data['last_updated'] = date("Y-m-d H:i:s");

            $set = implode(", ", array_map(fn($col) => "`$col` = ?", array_keys($data)));

            $sql = "UPDATE document_types SET $set WHERE id = ?";

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

    function get_document_types($type = []) {
        global $conn;
        $document_types = [];

        $query = "SELECT `id`, `name` FROM `document_types`";
        
        if (!empty($type)) {
            $query .= " WHERE `type` IN ('" . implode("', '", $type) . "')";
        }
        
        $query .= " ORDER BY `id` ASC";

        $result = $conn->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $document_types[] = $row;
            }
        }

        return $document_types;
    }
    
    function get_all_document_types($filter = [], $pagination = []) {
        global $conn;

        $documents = ['total' => 0, 'result' => []];

        $query = "SELECT d.* FROM document_types d";

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

            // Date Range
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

            // Dynamic Item Filters
            if ($key === 'item' && is_array($value)) {
                foreach ($value as $item) {
                    if(!isset($item['column'], $item['value']) || $item['value'] === ''){
                        continue;
                    }

                    $column = $item['column'];
                    $input  = $item['value'];

                    // Multiple values 
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

                    } else { // Single values 
                        $conditions[] = "d.$column = ?";
                        $params[] = $input;

                        $types .= is_numeric($input) ? "i" : "s";
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

        $query .= " ORDER BY d.id DESC";

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

        $document_types['result'] = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $document_types;
    }

    function view_document_type($id) {
        global $conn;
        $document_type = [];

        $query = "SELECT d.* FROM document_types d WHERE d.id = ?";

        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
          
            if ($result) {
                $document_type = $result->fetch_array(MYSQLI_ASSOC);
            }
            $stmt->close();
        }

        return $document_type;
    }
    
    function delete_document_type($id) {
        global $conn;
        $flag = false;

        $stmt = $conn->prepare("SELECT id FROM `document_types` WHERE id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $stmt = $conn->prepare("DELETE FROM `document_types` WHERE id = ?");
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