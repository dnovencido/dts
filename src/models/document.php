<?php
    require "db/db.php";
    require "audit.php";

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

        $allowed = [
            'title','user_id','category','document_type','document_date',
            'document_number','date_received','concerned_division',
            'names_stakeholders','receiving_office','signatories',
            'status','filing_location','file','file_name','file_type',
            'last_updated','date_created'
        ];

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
        Normalize data
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

        $data['filing_location'] =
            isset($data['filing_location']) && $data['filing_location'] !== ''
            ? (int)$data['filing_location']
            : null;


        /* ===============================
        CREATE DOCUMENT
        =============================== */

        if ($id === null) {

            $data['date_created'] = date("Y-m-d H:i:s");

            $columns = array_keys($data);
            $placeholders = implode(",", array_fill(0, count($columns), "?"));

            $sql = "INSERT INTO documents (`" .
                    implode("`,`", $columns) .
                    "`) VALUES ($placeholders)";

            $stmt = $conn->prepare($sql);

            $types = str_repeat("s", count($columns));
            $values = array_values($data);

            $stmt->bind_param($types, ...$values);

        } 

        /* ===============================
        UPDATE DOCUMENT
        =============================== */

        else {

            $old_status = get_document_status($id);

            $data['last_updated'] = date("Y-m-d H:i:s");

            $set = implode(", ", array_map(
                fn($col) => "`$col` = ?",
                array_keys($data)
            ));

            $sql = "UPDATE documents SET $set WHERE id = ?";

            $stmt = $conn->prepare($sql);

            $types = str_repeat("s", count($data)) . "i";
            $values = array_merge(array_values($data), [$id]);

            $stmt->bind_param($types, ...$values);
        }


        /* ===============================
        Execute
        =============================== */

        if ($stmt->execute()) {

            $flag = true;

            if ($id === null) {

                $new_id = $conn->insert_id;

                log_document_action(
                    $new_id,
                    "CREATED",
                    $data['status'] ?? null
                );

                log_audit("CREATE", "documents", $new_id);

            } else {

                log_audit("UPDATE", "documents", $id);

                $new_status = $data['status'] ?? $old_status;

                /* --------------------------------
                Handle Legacy Documents
                -------------------------------- */

                ensure_initial_document_log(
                    $id,
                    $old_status
                );

                /* --------------------------------
                Log status change only if changed
                -------------------------------- */

                if ($new_status !== $old_status) {

                    log_document_action(
                        $id,
                        "STATUS_CHANGED",
                        $new_status
                    );
                }
            }
        }

        // Check for document disposal
        archive_document($id);

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

        $documents = [
            'total'  => 0,
            'result' => []
        ];

        $json_columns = [
            'concerned_division',
            'names_stakeholders',
            'signatories'
        ];

        /* =========================================
        SELECT ONLY NEEDED COLUMNS (NO d.*)
        DO NOT LOAD FILE BLOB
        ========================================= */
        $query = "
            SELECT 
                d.id,
                d.title,
                d.document_number,
                d.document_date,
                d.date_received,
                d.status,
                d.category,
                d.document_type,
                d.filing_location,
                f.name AS filing_location_name,
                d.file_type,
                c.name AS category_name, 
                dt.name AS document_type_name,
                r.name AS receiving_office_name
            FROM documents d
            LEFT JOIN categories c ON d.category = c.id
            LEFT JOIN document_types dt ON d.document_type = dt.id
            LEFT JOIN receiving_offices r ON d.receiving_office = r.id
            LEFT JOIN filing_locations f ON d.filing_location = f.id
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
                        $searchParts[] = "d.$col LIKE ?";
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
                    $conditions[] = "d.$column BETWEEN ? AND ?";
                    $params[] = $from;
                    $params[] = $to;
                    $types   .= "ss";
                } elseif ($from) {
                    $conditions[] = "d.$column >= ?";
                    $params[] = $from;
                    $types   .= "s";
                } elseif ($to) {
                    $conditions[] = "d.$column <= ?";
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

                    /* JSON COLUMN FILTER */
                    if (in_array($column, $json_columns)) {

                        if (is_array($input)) {

                            $jsonParts = [];

                            foreach ($input as $val) {
                                $jsonParts[] = "JSON_CONTAINS(d.$column, CAST(? AS JSON))";
                                $params[] = json_encode((int)$val);
                                $types   .= "s";
                            }

                            if (!empty($jsonParts)) {
                                $conditions[] = "(" . implode(" OR ", $jsonParts) . ")";
                            }

                        } else {

                            $conditions[] = "JSON_CONTAINS(d.$column, CAST(? AS JSON))";
                            $params[] = json_encode((int)$input);
                            $types   .= "s";
                        }

                        continue;
                    }

                    /* NORMAL COLUMN FILTER */
                    if (is_array($input)) {

                        $placeholders = implode(',', array_fill(0, count($input), '?'));
                        $conditions[] = "d.$column IN ($placeholders)";

                        foreach ($input as $val) {
                            $params[] = $val;
                            $types   .= is_numeric($val) ? "i" : "s";
                        }

                    } else {

                        $conditions[] = "d.$column = ?";
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

        $query .= " ORDER BY d.id DESC LIMIT ?, ?";
        $params[] = (int)$offset;
        $params[] = (int)$limit;
        $types   .= "ii";

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $documents['result'][] = $row;
        }

        $stmt->close();

        return $documents;
    }

    function view_document($id) {
        global $conn;
        $document = [];

        $query = "SELECT d.*, 
            CASE 
                /* Less than 1 month → show weeks */
                WHEN TIMESTAMPDIFF(MONTH, d.document_date, CURDATE()) = 0 
                THEN CONCAT(
                    FLOOR(DATEDIFF(CURDATE(), d.document_date) / 7), ' week',
                    IF(FLOOR(DATEDIFF(CURDATE(), d.document_date) / 7) != 1, 's', '')
                )

                /* Less than 1 year → show months */
                WHEN TIMESTAMPDIFF(YEAR, d.document_date, CURDATE()) = 0 
                THEN CONCAT(
                    TIMESTAMPDIFF(MONTH, d.document_date, CURDATE()), ' month',
                    IF(TIMESTAMPDIFF(MONTH, d.document_date, CURDATE()) > 1, 's', '')
                )

                /* Exact years */
                WHEN (TIMESTAMPDIFF(MONTH, d.document_date, CURDATE()) % 12) = 0
                THEN CONCAT(
                    TIMESTAMPDIFF(YEAR, d.document_date, CURDATE()), ' year',
                    IF(TIMESTAMPDIFF(YEAR, d.document_date, CURDATE()) > 1, 's', '')
                )

                /* Years + months */
                ELSE CONCAT(
                    TIMESTAMPDIFF(YEAR, d.document_date, CURDATE()), ' year',
                    IF(TIMESTAMPDIFF(YEAR, d.document_date, CURDATE()) > 1, 's', ''),
                    ', ',
                    TIMESTAMPDIFF(MONTH, d.document_date, CURDATE()) % 12, ' month',
                    IF(TIMESTAMPDIFF(MONTH, d.document_date, CURDATE()) % 12 > 1, 's', '')
                )
            END AS document_age,

            IFNULL(dt.retention_period, 5) AS retention_period,

            CASE 
                WHEN TIMESTAMPDIFF(YEAR, d.document_date, CURDATE()) 
                    >= IFNULL(dt.retention_period, 5)
                THEN 'For Archiving'
                ELSE 'Active'
            END AS disposal_status,

            CONCAT(
                u.fname, ' ',
                IF(u.mname IS NOT NULL AND u.mname != '', CONCAT(u.mname, ' '), ''),
                u.lname
            ) AS emp_name,
            
            f.name as filing_location_name, 
            dt.name AS document_type_name, 
            r.name AS receiving_office_name

        FROM documents as d 
        LEFT JOIN categories as c ON d.category = c.id 
        LEFT JOIN document_types as dt ON d.document_type = dt.id 
        LEFT JOIN filing_locations as f ON d.filing_location = f.id
        LEFT JOIN users as u ON d.user_id = u.id
        LEFT JOIN receiving_offices r ON d.receiving_office = r.id 
        WHERE d.id = ?";

        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
          
            if ($result) {
                $document = $result->fetch_array(MYSQLI_ASSOC);
            }
            
            log_audit("VIEW", "documents", $id);

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
                log_audit("DELETE", "documents", $id);
            }
        }

        return $flag;
    }

    function get_document_count($types = [], $status = '', $filter = []) {

        global $conn;

        if (!is_array($types)) {
            $types = [$types];
        }

        $type_ids = [];

        /* =====================
        Resolve Category Names
        ===================== */

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
        Base Query
        ===================== */

        $query = "SELECT COUNT(*) AS total FROM documents d";

        $conditions = [];
        $params = [];
        $typesStr = "";

        /* =====================
        Document Type Filter
        ===================== */

        if (!empty($type_ids)) {

            $placeholders = implode(',', array_fill(0, count($type_ids), '?'));

            $conditions[] = "d.category IN ($placeholders)";

            foreach ($type_ids as $id) {
                $params[] = $id;
                $typesStr .= "i";
            }
        }

        /* =====================
        Status Filter
        ===================== */

        if (!empty($status)) {

            $conditions[] = "d.status = ?";
            $params[] = $status;
            $typesStr .= "s";
        }

        /* =====================
        Dynamic Filters
        ===================== */

        foreach ($filter as $key => $value) {

            if ($key === 'item' && is_array($value)) {

                foreach ($value as $item) {

                    if (!isset($item['column'], $item['value'])) continue;

                    $conditions[] = "d.{$item['column']} = ?";
                    $params[] = $item['value'];

                    $typesStr .= is_numeric($item['value']) ? "i" : "s";
                }
            }

            if ($key === 'date_range' && is_array($value) && count($value) >= 3) {

                $column = $value[0][0];
                $from   = $value[1];
                $to     = $value[2];

                if ($from && $to) {

                    $conditions[] = "d.$column BETWEEN ? AND ?";
                    $params[] = $from;
                    $params[] = $to;
                    $typesStr .= "ss";
                }
            }
        }

        /* =====================
        Apply WHERE
        ===================== */

        if (!empty($conditions)) {

            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        /* =====================
        Execute
        ===================== */

        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception($conn->error);
        }

        if (!empty($params)) {
            $stmt->bind_param($typesStr, ...$params);
        }

        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        return (int)($row['total'] ?? 0);
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

    function count_documents($range = 6, $filter = []) {

        global $conn;

        $allowedRanges = [3, 6, 12];

        if (!in_array($range, $allowedRanges)) {
            $range = 6;
        }

        $startDate = date('Y-m-01', strtotime("-" . ($range - 1) . " months"));
        $endDate   = date('Y-m-t');

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
                DATE_FORMAT(d.date_received, '%Y-%m') AS month_key,
                SUM(CASE WHEN d.category = 1 THEN 1 ELSE 0 END) AS incoming,
                SUM(CASE WHEN d.category = 2 THEN 1 ELSE 0 END) AS outgoing
            FROM documents d
        ";

        $conditions = ["d.date_received BETWEEN ? AND ?"];
        $params = [$startDate, $endDate];
        $types = "ss";

        /* =====================
        Apply Filters
        ===================== */
        foreach($filter as $key => $value) {

            if ($key === 'item' && is_array($value)) {

                foreach ($value as $item) {

                    if (!isset($item['column'], $item['value'])) continue;

                    $conditions[] = "d.{$item['column']} = ?";
                    $params[] = $item['value'];

                    $types .= is_numeric($item['value']) ? "i" : "s";
                }
            }
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " GROUP BY month_key ORDER BY month_key";

        $stmt = $conn->prepare($query);

        if(!$stmt) {
            throw new Exception($conn->error);
        }

        $stmt->bind_param($types, ...$params);

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

    function count_documents_per_type($year = null, $category = null, $filter = []) {

        global $conn;

        /* =========================
        Validate Category
        ========================= */

        $allowedCategories = [1, 2];

        if (!in_array($category, $allowedCategories)) {
            $category = null;
        }

        $year = $year ?? date('Y');


        /* =========================
        Base Query
        ========================= */

        $query = "
            SELECT 
                dt.name AS label,
                COUNT(*) AS total
            FROM documents d
            INNER JOIN document_types dt
                ON dt.id = d.document_type
        ";

        $conditions = ["YEAR(d.date_received) = ?"];
        $params = [$year];
        $types = "i";


        /* =========================
        Category Filter
        ========================= */

        if ($category !== null) {

            $conditions[] = "d.category = ?";
            $params[] = $category;
            $types .= "i";
        }


        /* =========================
        Dynamic Filters
        ========================= */

        foreach ($filter as $key => $value) {

            if ($key === 'item' && is_array($value)) {

                foreach ($value as $item) {

                    if (!isset($item['column'], $item['value'])) continue;

                    $conditions[] = "d.{$item['column']} = ?";
                    $params[] = $item['value'];

                    $types .= is_numeric($item['value']) ? "i" : "s";
                }
            }

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
        }


        /* =========================
        Apply WHERE
        ========================= */

        if (!empty($conditions)) {

            $query .= " WHERE " . implode(" AND ", $conditions);
        }


        /* =========================
        Group Results
        ========================= */

        $query .= "
            GROUP BY d.document_type
            HAVING total > 0
            ORDER BY dt.name
        ";


        /* =========================
        Execute Query
        ========================= */

        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception($conn->error);
        }

        $stmt->bind_param($types, ...$params);

        $stmt->execute();

        $result = $stmt->get_result();


        /* =========================
        Format Output
        ========================= */

        $data = [
            'year' => (int)$year,
            'category' => $category,
            'labels' => [],
            'counts' => []
        ];

        while ($row = $result->fetch_assoc()) {

            $data['labels'][] = $row['label'];
            $data['counts'][] = (int)$row['total'];
        }

        $stmt->close();

        return $data;
    }

    function get_category_id($name) {
        global $conn;

        $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $result['id'] ?? null;
    }

    function log_document_action($document_id, $action, $status = null) {
        global $conn;

        $user_id = $_SESSION['id'] ?? null;

        $sql = "
            INSERT INTO document_logs
            (document_id, user_id, action, status, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception($conn->error);
        }

        $stmt->bind_param("iiss", $document_id, $user_id, $action, $status);

        $stmt->execute();

        $stmt->close();
    }

    function get_document_status($document_id){
        global $conn;

        $sql = "SELECT status FROM documents WHERE id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("i", $document_id);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        return $result['status'] ?? null;
    }   
    
    function document_has_logs($document_id) {
        global $conn;

        $sql = "SELECT id FROM document_logs WHERE document_id = ? LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $document_id);
        $stmt->execute();

        $result = $stmt->get_result();

        $has_logs = $result->num_rows > 0;

        $stmt->close();

        return $has_logs;
    }

    function ensure_initial_document_log($document_id, $status) {
        if (!$status) {
            return;
        }

        if (!document_has_logs($document_id)) {

            log_document_action(
                $document_id,
                "INITIAL_STATUS",
                $status
            );
        }
    }

    function get_document_logs($document_id) {
        global $conn;

        $sql = "
            SELECT 
                dl.action,
                dl.status,
                dl.created_at,
                u.fname,
                u.lname
            FROM document_logs dl
            LEFT JOIN users u ON u.id = dl.user_id
            WHERE dl.document_id = ?
            ORDER BY dl.created_at ASC
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("i", $document_id);
        $stmt->execute();

        $result = $stmt->get_result();

        $logs = [];

        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }

        $stmt->close();

        return $logs;
    }  

    function archive_document($id) {
        global $conn;

        $flag = false;

        /* ===============================
        Check current status
        =============================== */
        $old_status = get_document_status($id);

        if (!$old_status) {
            return false; // document not found
        }

        /* ===============================
        Archive only if eligible
        =============================== */
        $sql = "
            UPDATE documents d
            LEFT JOIN document_types dt ON d.document_type = dt.id
            SET 
                d.status = 'archived',
                d.last_updated = NOW()
            WHERE d.id = ?
            AND d.document_date IS NOT NULL
            AND TIMESTAMPDIFF(YEAR, d.document_date, CURDATE()) 
                >= IFNULL(dt.retention_period, 5)
            AND d.status != 'archived'
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        /* ===============================
        If updated → log actions
        =============================== */
        if ($stmt->affected_rows > 0) {

            $flag = true;
            $new_status = 'archived';

            /* --------------------------------
            Ensure initial log exists
            -------------------------------- */
            ensure_initial_document_log($id, $old_status);

            /* --------------------------------
            Log ARCHIVED only (avoid duplicates)
            -------------------------------- */
            if (!has_archive_log($id)) {
                log_document_action(
                    $id,
                    "ARCHIVED",
                    $new_status
                );
            }

            /* --------------------------------
            Audit trail
            -------------------------------- */
            log_audit("ARCHIVE", "documents", $id);
        }

        $stmt->close();

        return $flag;
    }
    
    function has_archive_log($document_id) {
        global $conn;

        $sql = "
            SELECT id 
            FROM document_logs 
            WHERE document_id = ? 
            AND action = 'ARCHIVED'
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $document_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;

        $stmt->close();

        return $exists;
    }
?>