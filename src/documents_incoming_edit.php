<?php
  include "models/document.php";
  include "models/division.php";
  include "models/receiving_office.php";
  include "models/stakeholder.php";
  include "models/filing_location.php";
  include "status.php";
  include "session.php"; 
  include "require_login.php"; 
  include "require_role.php"; 

  require_role($_SESSION['id'], ['super_admin', 'administrator', 'employee'], 'incoming document management');

  $errors = [];
  if (array_key_exists("id", $_GET)) {
    $document = view_document($_GET['id']);
    $document_id = $document['id'];
    $document_types = get_document_types(["incoming"]);
    $divisions = get_all_divisions();
    $receiving_offices = get_all_receiving_offices();
    $filing_locations = get_all_filing_locations();
    $stakeholders = get_all_stakeholders();

    if(isset($_POST['submit'])) {
      $file_name = (empty($_FILES['file']['tmp_name'])) ? $document['file_name'] : $_FILES['file']['name'];
      $file_type = (empty($_FILES['file']['tmp_name'])) ? $document['file_type'] : $_FILES['file']['type'];
      $tmp_path = $_FILES['file']['tmp_name'];
      $_POST['file_name'] = $file_name;
      $_POST['file_type'] = $file_type;
      $_POST['file'] = (empty($_FILES['file']['tmp_name'])) ? $document['file'] : file_get_contents($_FILES['file']['tmp_name']);
      $errors = validate_document($_POST);
      if(empty($errors)) {
        $file = (empty($_FILES['file']['tmp_name'])) ? $document['file'] : file_get_contents($_FILES['file']['tmp_name']);
        $save_document = save_document($_POST, $document_id);
        if($save_document) {
          $_SESSION['flash_message'] = [
              'type' => 'success',
              'text' => 'You have successfully updated a document.'
          ];
          header("Location: /documents/incoming/view/".$document_id);
        } else {
          $errors[] = "Could not update the document. Please try again later.";
        }
      }
      } else {
        $_POST = [
          'title' => isset($_POST['title']) ? $_POST['title'] : $document['title'],
          'document_type' => isset($_POST['document_type']) ? $_POST['document_type'] : $document['document_type'],
          'document_date' => isset($_POST['document_date']) ? $_POST['document_date'] : $document['document_date'],
          'document_number' => isset($_POST['document_number']) ? $_POST['document_number'] : $document['document_number'],
          'date_received' => isset($_POST['date_received']) ? $_POST['date_received'] : $document['date_received'],
          'concerned_division' => isset($_POST['concerned_division']) ? $_POST['concerned_division'] : json_decode($document['concerned_division'], true),
          'names_stakeholders' => isset($_POST['names_stakeholders']) ? $_POST['names_stakeholders'] : json_decode($document['names_stakeholders'], true),
          'receiving_office' => isset($_POST['receiving_office']) ? $_POST['receiving_office'] : $document['receiving_office'],
          'status' => isset($_POST['status']) ? $_POST['status'] : $document['status'],
          'signatories' => isset($_POST['signatories']) ? $_POST['signatories'] : json_decode($document['signatories']),
          'file' => isset($_POST['file']) ? $_POST['file'] : base64_encode($document['file']),
          'filing_location' => isset($_POST['filing_location']) ? $_POST['filing_location'] : $document['filing_location'],
        ];
      }
    $config = $status_config[$type] ?? ['options' => []];
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
                    <h1>Edit Document</h1>
                  </div>
                  <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                      <li class="breadcrumb-item"><a href="/documents/incoming/">Incoming</a></li>
                      <li class="breadcrumb-item active">Edit Document</li>
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
      const selectedValues = <?= json_encode($_POST['signatories']) ?>;
    </script>
    <?php include 'shared/_scripts.php'; ?>
    <!-- Custom JS -->
    <script src="/assets/js/document.js"></script>
  </body>
<?php include 'layouts/_footer.php'; ?>


