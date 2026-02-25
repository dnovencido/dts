<?php
    include "session.php"; 
    include "models/document.php";
    include "lib/pagination.php";
    include "require_login.php"; 
    include "require_role.php"; 
    require "dompdf/autoload.inc.php";

    require_role($_SESSION['id'], ['super_admin', 'administrator', 'employee'], 'reports document management');

    use Dompdf\Dompdf;
    use Dompdf\Options;

    $document_types = get_document_types(["incoming", "outgoing"]);
    $date_range_text = "ALL Documents";

    // Generate report if filter is used
    if (!empty($_GET['category']) || !empty($_GET['document_type']) || !empty($_GET['date_received'])) {

        $filter = [];

       if (!empty($_GET['category'])) {
            $filter['item'][] = [
            'column' => 'category',
            'value'  => $_GET['category']
            ];
        }


        if (!empty($_GET['document_type'])) {
            $filter['item'][] = [
            'column' => 'document_type',
            'value'  => $_GET['document_type']
            ];
        }

        // Date Received
        if (!empty($_GET['date_received'])) {
            $range = $_GET['date_received'];
            $dates = explode(' - ', $range);

            if (count($dates) === 2) {
            $from = DateTime::createFromFormat('m/d/Y', trim($dates[0]));
            $to   = DateTime::createFromFormat('m/d/Y', trim($dates[1]));
                if ($from && $to) {
                    $dateFrom = $from->format('Y-m-d');
                    $dateTo   = $to->format('Y-m-d');
                    $filter['date_range'] = [
                        ['date_received'],
                        $dateFrom,
                        $dateTo
                    ];
                }
            }

            if ($dateFrom === $dateTo) {
                $date_range_text = "On " . date('F d, Y', strtotime($dateFrom));
            } else {
                $date_range_text = "From " . 
                    date('F d, Y', strtotime($dateFrom)) . 
                    " to " . 
                    date('F d, Y', strtotime($dateTo));
            }
            
        }

        // Get documents
        $document_data = get_all_documents($filter, []);
        $documents = $document_data['result'] ?? [];

        $logoLeft  = __DIR__ . '/assets/images/coa.jpeg';
        $logoRight = __DIR__ . '/assets/images/dpwh.jpeg';

        $logoLeftData  = base64_encode(file_get_contents($logoLeft));
        $logoRightData = base64_encode(file_get_contents($logoRight));

        // Build report HTML
        $html = '
        <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            width: 100%;
            text-align: center;
            position: relative;
        }
        
        .report-title {
            margin-top: 35px;
        }

        .logo-left {
            position: absolute;
            left: 0;
            top: 0;
        }

        .logo-right {
            position: absolute;
            right: 0;
            top: 0;
        }

        .title {
            text-align: center;
            margin-top: 10px;
        }

        .upper {
            text-transform: uppercase;
            line-height: 0.5;
            font-weight: normal;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid black;
            padding: 6px;
        }

        th {
            background: #f2f2f2;
        }
        </style>

        <div class="header">
            <img src="data:image/png;base64,'.$logoLeftData.'" width="80" class="logo-left" alt="Logo Left">
            <img src="data:image/png;base64,'.$logoRightData.'" width="80" class="logo-right" alt="Logo Right">
            <div class="title">
                <div class="upper">
                    <h3>Republic of the Philippines</h3>
                    <h1>COMMISSION ON AUDIT</h1>
                    <h3>Regional Office 1</h3>
                    <h3>Aguila Road, Sevilla, City of San Fernando, La Union</h3>
                </div>
                <h2 class="report-title">List of Documents</h2>';
                if(!empty($date_range_text)) {
                    $html .= '<p><strong>'.$date_range_text.'</strong></p>';
                }
        $html .='</div>
        </div>
        <table>
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="5%">Type</th>
                    <th width="30%">Title</th>
                    <th width="15%">Document Type</th>
                    <th width="15%">Document Number</th>
                    <th width="15%">Date</th>
                    <th width="10%">Status</th>
                    <th width="10%">Location</th>
                    <th width="15%">Date Received</th>
                </tr>
            </thead>

            <tbody>
        ';

        if (!empty($documents)) {
            foreach ($documents as $key => $value) {
                $date = date('M d, Y', strtotime($value['document_date']));
                $date_received = date('M d, Y ', strtotime($value['date_received'] ?? ''));

                $html .= '
                <tr>
                    <td>'.++$key.'</td>
                    <td>'.ucfirst(strtolower(htmlspecialchars($value['category_name'], ENT_QUOTES))).'</td>
                    <td>'.htmlspecialchars($value['title']).'</td>
                    <td>'.htmlspecialchars($value['document_type_name'] ?? '').'</td>
                    <td>'.htmlspecialchars($value['document_number'] ?? '-').'</td>
                    <td>'.$date.'</td>
                    <td>'.ucfirst(strtolower(htmlspecialchars($value['status'], ENT_QUOTES))).'</td>
                    <td>'.htmlspecialchars($value['filing_location_name']).'</td>
                    <td>'.$date_received.'</td>
                </tr>';
            }

        } else {
            $html .= '
            <tr>
                <td colspan="9" align="center">No records found</td>
            </tr>';
        }

        $html .= '
            </tbody>
        </table>

        <br>

        Generated on: '.date("M d, Y h:i A").'
        ';


        // Generate PDF
        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('legal', 'landscape');

        $dompdf->render();


        // Convert PDF to Base64 for preview
        $pdfData = base64_encode($dompdf->output());
    }
?>
<?php include 'layouts/_header.php'; ?>
  <body class="hold-transition sidebar-collapse sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
      <div class="wrapper">
        <div class="content-wrapper">
          <?php include 'layouts/_navbar.php'; ?>
            <!-- Content Header (Page header) -->
            <section class="content-header">
              <div class="container-fluid">
                <div class="row mb-2">
                  <div class="col-sm-6">
                    <h1>Generate Report</h1>
                  </div>
                  <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                      <li class="breadcrumb-item">Reports</li>
                    </ol>
                  </div>
                </div>
              </div><!-- /.container-fluid -->
            </section>
            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <?php if(isset($_SESSION['flash_message'])) { ?>
                            <?php include "layouts/_messages.php"; ?>
                            <?php } ?>
                            <div id="search-form" class="card p-3">
                            <p class="text-muted text-uppercase fs-6 fw-bold"><i class="fa-solid fa-magnifying-glass"></i> Search Filter</hp>
                            <form method="get">
                                <div id="form-search">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="category">Category</label>
                                            <select name="category" id="category" class="form-control">
                                                <option value="">-- Type--</option>
                                                <option value="1" <?= (isset($_GET['category']) && $_GET['category'] == "1") ? 'selected' : '' ?>>Incoming</option>
                                                <option value="2" <?= (isset($_GET['category']) && $_GET['category'] == "2") ? 'selected' : '' ?>>Outgoing</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="document_type">Document Type</label>
                                            <select name="document_type" id="document_type" class="form-control">
                                            <option value="">-- Select Document Type --</option>
                                            <?php foreach($document_types as $type) { ?>
                                                <option value="<?= htmlspecialchars($type['id'], ENT_QUOTES) ?>" <?= (isset($_GET['document_type']) && $_GET['document_type'] == $type['id']) ? 'selected' : '' ?>><?= htmlspecialchars($type['name'], ENT_QUOTES) ?></option>
                                            <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Date Received/Released:</label>
                                            <input type="text" class="form-control date-range" name="date_received" value="<?= isset($_GET['date_received']) ? htmlspecialchars($_GET['date_received'], ENT_QUOTES) : '' ?>"/>
                                        </div>
                                    </div>
                                </div>
                                </div>
                                <button type="submit" class="btn bg-gradient-primary btn-md">
                                <i class="fa-solid fa-magnifying-glass"></i> Search
                                </button>
                                <a href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?>" 
                                class="btn bg-gradient-secondary btn-md">
                                <i class="fa-solid fa-rotate-left"></i> Reset
                                </a>
                            </form>  
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <?php if (isset($pdfData)) { ?>
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Report Preview</h3>
                                        <div class="card-tools">
                                            <a href="data:application/pdf;base64,<?= $pdfData ?>" target="_blank" class="btn btn-sm bg-gradient-success">
                                                <i class="fa-solid fa-file-pdf"></i> View PDF
                                            </a>
                                            <a href="data:application/pdf;base64,<?= $pdfData ?>" download="document_report.pdf" class="btn btn-sm bg-gradient-primary">
                                                <i class="fa-solid fa-download"></i> Download PDF
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <iframe src="data:application/pdf;base64,<?= $pdfData ?>" style="width:100%; height:600px;" frameborder="0"></iframe>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>  
                </div><!-- /.container-fluid -->
            </section>
            <!-- /.content -->
          </div>
        <?php include 'layouts/_sidebar.php'; ?>
        </div>
      </div>
    <?php include 'shared/_scripts.php'; ?>
    <script>
      $('#document_type').select2();
    </script>
    <!-- Custom JS -->
    <script src="/assets/js/date_range.js"></script>
  </body>
<?php include 'layouts/_footer.php'; ?>


