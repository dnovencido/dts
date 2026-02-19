<?php
  include "models/document.php";
  include "models/division.php";
  include "models/receiving_office.php";
  include "models/filing_location.php";
  include "models/stakeholder.php";
  include "status.php";
  include "session.php"; 
  include "require_login.php"; 
  include "require_role.php"; 

  //require_role($_SESSION['id'], ['super_admin', 'administrator', 'employee'], 'student registration');
  $document_types = get_document_types(["incoming"]);
  $divisions = get_all_divisions();
  $receiving_offices = get_all_receiving_offices();
  $filing_locations = get_all_filing_locations();
  $stakeholders = get_all_stakeholders();

  $config = $status_config[$type] ?? ['options' => []];

  $errors = [];
  if(isset($_POST['submit'])) {
    foreach (['document_date', 'date_received'] as $date_field) {
      if(!empty($_POST[$date_field])) {
        $timestamp = strtotime($_POST[$date_field]);
        if($timestamp) {
          $_POST[$date_field] = date('Y-m-d', $timestamp);
        }
      }
    }
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
      $file_name = $_FILES['file']['name'];
      $file_type = $_FILES['file']['type'];
      $tmp_path = $_FILES['file']['tmp_name'];
      $_POST['file_name'] = $file_name;
      $_POST['file_type'] = $file_type;
      $_POST['file'] = file_get_contents($tmp_path);
    } 

    $errors = validate_document($_POST, $_FILES['file']['tmp_name']);

    if(empty($errors)) {
      $_POST['category'] = 1;
      $save_document = save_document($_POST);
      if($save_document) {
        $_SESSION['flash_message'] = [
          'type' => 'success',
          'text' => 'You have successfully added new document.'
        ];
        header("Location: /documents/incoming");
        exit;
      } else {
        $errors[] = "Could save document. Please try again later.";
      }        
    }
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
                    <h1>New Incoming Document</h1>
                  </div>
                  <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                      <li class="breadcrumb-item"><a href="/documents/incoming">Incoming</a></li>
                      <li class="breadcrumb-item active">New Incoming Document</li>
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
                    <?php include 'shared/_form.php'; ?>
                  </div>
                </div>
              </div><!-- /.container-fluid -->
            </section>
            <!-- /.content -->
          </div>
        <?php include 'layouts/_sidebar.php'; ?>
        </div>
      </div>
    <script>
      const selectedValues = <?= json_encode($_POST['signatories'] ?? [])  ?>;
    </script>
    <?php include 'shared/_scripts.php'; ?>
    <!-- Custom JS -->
    <script src="/assets/js/document.js"></script>
  </body>
<?php include 'layouts/_footer.php'; ?>


