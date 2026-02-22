<?php
    require "db/db.php";

    function validate_document($document = []) {
        $validation_errors = [];

        if (!empty($document)) {
            $title = trim($document['title'] ?? '');
            $document_type = $document['document_type'] ?? '';
            $document_date = $document['document_date'] ?? '';
            $file = $document['file'] ?? ''; 
            $document_number = trim($document['document_number'] ?? '');
            $date_received = $document['date_received'] ?? '';
            $concerned_division = $document['concerned_division'] ?? [];
            $head_division = trim($document['head_division'] ?? '');
            $names_stakeholders = $document['names_stakeholders'] ?? [];
            $receiving_office = trim($document['receiving_office'] ?? '');
            $signatories = $document['signatories'] ?? [];
            $status = trim($document['status'] ?? '');
            $location_of_filing = trim($document['location_of_filing'] ?? '');
            
            // Title
            if (empty($title)) {
                $validation_errors[] = "Title is required.";
            } 

            // Document Type
            if (empty($document_type)) {
                $validation_errors[] = "Document Type is required.";
            } 

            // Document Date
            if (empty($document_date)) {
                $validation_errors[] = "Document Date is required.";
            } 

            // File
            if (empty($file)) {
                $validation_errors[] = "File is required.";
            } 

            // Concerned Division
            if (empty($concerned_division)) {
                $validation_errors[] = "Concerned Division is required.";
            } 

            // Receiving Office
            if (empty($receiving_office)) {
                $validation_errors[] = "Receiving Office is required.";
            } 

            // Status
            if (empty($status)) {
                $validation_errors[] = "Status is required.";
            } 
    
        } else {
            $validation_errors[] = "No data submitted.";
        }
    
        return $validation_errors;
    }

    function save_document($fields = [], $id = null) {
        global $conn;

        $flag = false;

        // Allowed columns
        $allowed = [
            'title', 'user_id', 'category', 'document_type', 'document_date',
            'document_number', 'date_received','concerned_division', 'names_stakeholders',
            'receiving_office','signatories','status','filing_location','file', 'file_name', 'file_type', 'last_updated', 'date_created'
        ];

        // Columns that must always be JSON
        $jsonFields = [
            'concerned_division',
            'names_stakeholders',
            'signatories'
        ];

        /* ===============================
        Filter allowed fields
        =============================== */
        $data = array_intersect_key($fields, array_flip($allowed));

        /* ===============================
        Normalize data (JSON + strings)
        =============================== */
        foreach ($data as $key => $value) {
            if (in_array($key, $jsonFields)) {
                if (empty($value)) {
                    $data[$key] = json_encode([]);
                } elseif (is_array($value)) {
                    $data[$key] = json_encode(
                        $value,
                        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                    );
                } else {
                    $data[$key] = json_encode(
                        [trim($value)],
                        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                    );
                }
            } else {
                if (is_string($value)) {
                    $data[$key] = trim($value);
                }
            }
        }

        if (isset($data['document_date']) && empty($data['document_date'])) {
            $data['document_date'] = null;
        }

        if (isset($data['date_received']) && empty($data['date_received'])) {
            $data['date_received'] = null;
        }

        if ($id === null) {
            $data['date_created'] = date("Y-m-d H:i:s");
            $columns = array_keys($data);
            $placeholders = implode(",", array_fill(0, count($columns), "?"));

            $sql = "INSERT INTO documents (`" .
                implode("`,`", $columns) .
                "`) VALUES ($placeholders)";

            $stmt = $conn->prepare($sql);

            $types  = str_repeat("s", count($columns));
            $values = array_values($data);

            $stmt->bind_param($types, ...$values);
        } else {
            $data['last_updated'] = date("Y-m-d H:i:s");

            $set = implode(", ", array_map(
                fn($col) => "`$col` = ?",
                array_keys($data)
            ));

            $sql = "UPDATE documents SET $set WHERE id = ?";

            $stmt = $conn->prepare($sql);

            $types  = str_repeat("s", count($data)) . "i";
            $values = array_merge(array_values($data), [$id]);

            $stmt->bind_param($types, ...$values);
        }
 
        if($stmt->execute())
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
    
    function get_all_documents($filter = [], $pagination = []) {
        global $conn;

        $documents = ['total' => 0, 'result' => []];

        $json_columns = [
            'concerned_division',
            'names_stakeholders',
            'signatories'
        ];

        $query = "SELECT 
                d.*, 
                c.name AS category_name, 
                dt.name AS document_type_name,
                r.name AS receiving_office_names
            FROM documents d
            INNER JOIN categories c ON d.category = c.id
            INNER JOIN document_types dt ON d.document_type = dt.id
            INNER JOIN receiving_offices r ON d.receiving_office = r.id
            INNER JOIN filing_locations f ON d.filing_location = f.id";
        $conditions = [];
        $params = [];
        $types = "";

        /* =========================
        Build Conditions
        ========================= */
        foreach ($filter as $key => $value) {
            if($key === 'search' && is_array($value) && count($value) >= 2) {
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

            if($key === 'date_range' && is_array($value) && count($value) >= 3) {
                $column = $value[0][0];
                $from   = trim($value[1]);
                $to     = trim($value[2]);

                if ($from !== '' && $to !== '') {
                    $conditions[] = "d.$column BETWEEN ? AND ?";
                    $params[] = $from;
                    $params[] = $to;
                    $types .= "ss";

                } elseif ($from !== '') {

                    $conditions[] = "d.$column >= ?";
                    $params[] = $from;
                    $types .= "s";

                } elseif ($to !== '') {

                    $conditions[] = "d.$column <= ?";
                    $params[] = $to;
                    $types .= "s";
                }

                continue;
            }

            if($key === 'item' && is_array($value)) {
                foreach ($value as $item) {
                    if(!isset($item['column'], $item['value']) || $item['value'] === '') {
                        continue;
                    }

                    $column = $item['column'];
                    $input  = $item['value'];

                    if(in_array($column, $json_columns)) {
                        if (is_array($input)) {
                            $jsonParts = [];

                            foreach ($input as $val) {
                                $jsonParts[] = "JSON_CONTAINS(d.$column, CAST(? AS JSON))";
                                $params[] = json_encode((int)$val);
                                $types  .= "s";
                            }

                            if ($jsonParts) {
                                $conditions[] = "(" . implode(" OR ", $jsonParts) . ")";
                            }
                        } else {
                            $conditions[] = "JSON_CONTAINS(d.$column, CAST(? AS JSON))";

                            $params[] = json_encode((int)$input);
                            $types  .= "s";
                        }

                        continue;
                    }
                    if(is_array($input)) {
                        $input = array_map('intval', $input);
                        $input = array_filter($input);

                        if(!empty($input)) {
                            $placeholders = implode(',', array_fill(0, count($input), '?'));

                            $conditions[] = "d.$column IN ($placeholders)";

                            foreach ($input as $id) {
                                $params[] = $id;
                                $types  .= "i";
                            }
                        }
                    } else {
                        $conditions[] = "d.$column = ?";

                        $params[] = $input;

                        $types .= is_numeric($input) ? "i" : "s";
                    }
                }
                continue;
            }
        }

        if(!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $count_query = "SELECT COUNT(*) AS total FROM (" . $query . ") AS total_records";

        $stmt = $conn->prepare($count_query);

        if($stmt === false) {
            throw new Exception("Prepare failed (COUNT): " . $conn->error);
        }

        if(!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        $count_result = $stmt->get_result()->fetch_assoc();

        $documents['total'] = $count_result['total'] ?? 0;

        $stmt->close();

        $query .= " ORDER BY d.id DESC";

        if(!empty($pagination)) {
            $query .= " LIMIT ?, ?";
            $params[] = (int)$pagination['offset'];
            $params[] = (int)$pagination['total_records_per_page'];

            $types .= "ii";
        }

        $stmt = $conn->prepare($query);

        if($stmt === false) {
            throw new Exception("Prepare failed (MAIN): " . $conn->error);
        }

        if(!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        $result = $stmt->get_result();

        $documents['result'] = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $documents;
    }

    function view_document($id) {
        global $conn;
        $document = [];

        $query = "SELECT d.*, 
           CONCAT(
                    u.fname, ' ',
                    IF(u.mname IS NOT NULL AND u.mname != '', CONCAT(u.mname, ' '), ''),
                    u.lname
                ) AS emp_name,
            f.name as filing_location_name, dt.name AS `document_type_name`, r.name AS `receiving_office_name` FROM `documents` as d 
            INNER JOIN `categories` as `c` ON d.category = c.id 
            INNER JOIN `document_types` as `dt` ON d.document_type = dt.id 
            INNER JOIN `filing_locations` as `f` ON d.filing_location = f.id
            INNER JOIN `users` as `u` ON d.user_id = u.id
            INNER JOIN receiving_offices r ON d.receiving_office = r.id WHERE d.id = ?";

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
    
    function delete_document($id) {
        global $conn;
        $flag = false;

        $stmt = $conn->prepare("SELECT id FROM `documents` WHERE id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $stmt = $conn->prepare("DELETE FROM `documents` WHERE id = ?");
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

    function get_document_count($types = [], $status = '') {
        global $conn;

        if (!is_array($types)) {
            $types = [$types];
        }

        $type_ids = [];

        if (!empty($types)) {
            $placeholders = implode(',', array_fill(0, count($types), '?'));
            $type_str = str_repeat('s', count($types));

            $sql = "SELECT id FROM categories WHERE name IN ($placeholders)";
            $stmt = $conn->prepare($sql);

            $stmt->bind_param($type_str, ...$types);
            $stmt->execute();

            $res = $stmt->get_result();

            while ($row = $res->fetch_assoc()) {
                $type_ids[] = (int)$row['id'];
            }

            $stmt->close();
        }

        /* =====================
        Build Count Query
        ===================== */
        $query = "SELECT COUNT(*) AS total FROM documents";

        $conditions = [];
        $params = [];
        $typesStr = "";

        /* Document Type Filter */
        if (!empty($type_ids)) {

            $placeholders = implode(',', array_fill(0, count($type_ids), '?'));

            $conditions[] = "category IN ($placeholders)";

            foreach ($type_ids as $id) {
                $params[] = $id;
                $typesStr .= "i";
            }
        }

        /* Status Filter (pending / received) */
        if (!empty($status)) {

            $conditions[] = "status = ?";
            $params[] = $status;
            $typesStr .= "s";
        }

        /* Apply WHERE */
        if (!empty($conditions)) {

            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        /* Execute */
        $stmt = $conn->prepare($query);

        if (!empty($params)) {
            $stmt->bind_param($typesStr, ...$params);
        }

        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        return $row['total'] ?? 0;
    }

    function get_document_counts($filter = []) {
        global $conn;

        $counts = [
            'incoming' => 0,
            'outgoing' => 0,
            'total'    => 0
        ];

        $query = "SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN d.category = 1 THEN 1 ELSE 0 END) AS incoming,
                    SUM(CASE WHEN d.category = 2 THEN 1 ELSE 0 END) AS outgoing,
                    SUM(CASE WHEN d.status = 'pending' AND d.category = 1 THEN 1 ELSE 0 END) AS incoming_pending,
                    SUM(CASE WHEN d.status = 'pending' AND d.category = 2 THEN 1 ELSE 0 END) AS outgoing_pending
                FROM documents d";

        $conditions = [];
        $params = [];
        $types = "";

        foreach($filter as $key => $value) {

            if ($key === 'date_range' && is_array($value) && count($value) >= 3) {

                $column = $value[0][0];
                $from   = $value[1];
                $to     = $value[2];

                if ($from && $to) {
                    $conditions[] = "d.$column BETWEEN ? AND ?";
                    $params[] = $from;
                    $params[] = $to;
                    $types .= "ss";
                }
            }

            if ($key === 'item' && is_array($value)) {

                foreach ($value as $item) {

                    if (!isset($item['column'], $item['value'])) continue;

                    $conditions[] = "d.{$item['column']} = ?";
                    $params[] = $item['value'];
                    $types .= is_numeric($item['value']) ? "i" : "s";
                }
            }
        }

        if(!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $conn->prepare($query);

        if($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        if(!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();

        $counts['incoming'] = (int)$result['incoming'];
        $counts['outgoing'] = (int)$result['outgoing'];
        $counts['incoming_pending'] = (int)$result['incoming_pending'];
        $counts['outgoing_pending'] = (int)$result['outgoing_pending'];
        $counts['total']    = (int)$result['total'];

        $stmt->close();

        return $counts;
    }

    function count_documents($range = 6) {

        global $conn;

        // Allowed ranges
        $allowedRanges = [3, 6, 12];

        if (!in_array($range, $allowedRanges)) {
            $range = 6;
        }

        // Calculate start and end dates
        $startDate = date('Y-m-01', strtotime("-" . ($range - 1) . " months"));
        $endDate   = date('Y-m-t');

        // Initialize months
        $monthCounts = [];

        for ($i = $range - 1; $i >= 0; $i--) {

            $date = strtotime("-$i months");

            $key = date('Y-m', $date);

            $monthCounts[$key] = [
                'label' => date('M Y', $date),
                'incoming' => 0,
                'outgoing' => 0
            ];
        }

        $query = "
            SELECT 
                DATE_FORMAT(date_received, '%Y-%m') AS month_key,
                SUM(CASE WHEN category = '1' THEN 1 ELSE 0 END) AS incoming,
                SUM(CASE WHEN category = '2' THEN 1 ELSE 0 END) AS outgoing
            FROM documents
            WHERE date_received BETWEEN ? AND ?
            GROUP BY month_key
            ORDER BY month_key
        ";

        $stmt = $conn->prepare($query);

        if(!$stmt) {
            throw new Exception($conn->error);
        }

        $stmt->bind_param("ss", $startDate, $endDate);

        $stmt->execute();

        $result = $stmt->get_result();


        while ($row = $result->fetch_assoc()) {

            $key = $row['month_key'];

            if (isset($monthCounts[$key])) {

                $monthCounts[$key]['incoming'] = (int)$row['incoming'];

                $monthCounts[$key]['outgoing'] = (int)$row['outgoing'];
            }
        }

        $stmt->close();

        $data = [
            'range' => $range . " months",
            'labels' => [],
            'incoming' => [],
            'outgoing' => []
        ];

        foreach($monthCounts as $month) {
            $data['labels'][]   = $month['label'];
            $data['incoming'][] = $month['incoming'];
            $data['outgoing'][] = $month['outgoing'];
        }

        return $data;
    }

    function count_documents_per_type($year = null, $category = null) {

        global $conn;

        /* =========================
        Validate category
        ========================= */
        $allowedCategories = [1, 2];

        if (!in_array($category, $allowedCategories)) {
            $category = null;
        }

        // Use current year if not specified
        $year = $year ?? date('Y');


        /* =========================
        Step 1: Fetch document types filtered by category
        ========================= */
        $types = [];

        if ($category !== null) {

            $typeQuery = "
                SELECT DISTINCT dt.id, dt.name
                FROM document_types dt
                INNER JOIN documents d 
                    ON d.document_type = dt.id
                WHERE d.category = ?
                AND YEAR(d.date_received) = ?
                ORDER BY dt.name
            ";

            $stmt = $conn->prepare($typeQuery);

            if (!$stmt) {
                throw new Exception($conn->error);
            }

            $stmt->bind_param("ii", $category, $year);

        } else {

            $typeQuery = "
                SELECT id, name
                FROM document_types
                ORDER BY name
            ";

            $stmt = $conn->prepare($typeQuery);

            if (!$stmt) {
                throw new Exception($conn->error);
            }
        }

        $stmt->execute();

        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {

            $types[$row['id']] = [
                'label' => $row['name'],
                'count' => 0
            ];
        }

        $stmt->close();


        /* =========================
        Step 2: Count documents per type for selected year
        ========================= */
        $query = "
            SELECT 
                document_type,
                COUNT(*) AS total
            FROM documents
            WHERE YEAR(date_received) = ?
        ";

        $params = [$year];
        $paramTypes = "i";

        if ($category !== null) {

            $query .= " AND category = ?";

            $params[] = $category;

            $paramTypes .= "i";
        }

        $query .= " GROUP BY document_type ORDER BY document_type";


        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception($conn->error);
        }

        $stmt->bind_param($paramTypes, ...$params);

        $stmt->execute();

        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {

            $typeId = $row['document_type'];

            if (isset($types[$typeId])) {

                $types[$typeId]['count'] = (int)$row['total'];
            }
        }

        $stmt->close();


        /* =========================
        Format output for Chart.js
        ========================= */
        $data = [
            'year' => (int)$year,
            'category' => $category,
            'labels' => [],
            'counts' => []
        ];

        foreach ($types as $type) {

            $data['labels'][] = $type['label'];
            $data['counts'][] = $type['count'];
        }

        return $data;
    }

?>