<?php
    include "models/document_type.php";
    include "session.php"; 
    include "require_login.php"; 
    include "require_role.php"; 

    //require_role($_SESSION['id'], ['super_admin', 'administrator', 'employee'], 'student registration');

    $errors = [];
    if (array_key_exists("id", $_GET)) {
        $document_type = view_document_type($_GET['id']);
        $document_type_id = $document_type['id'];
        
        if(isset($_POST['submit'])) {
          $errors = validate_document_type($_POST);
          if(empty($errors)) {
            $save_document_type = save_document_type($_POST, $document_type_id);
            if($save_document_type) {
              $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => 'You have successfully updated a document type.'
              ];
              header("Location: /document_types/");
            } else {
              $errors[] = "Could not update the document type. Please try again later.";
            }
          }
        } else {
          $_POST = [
            'name' => isset($_POST['name']) ? $_POST['name'] : $document_type['name'],
            'type' => isset($_POST['type']) ? $_POST['type'] : $document_type['type'],
          ];
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
                    <h1>Edit Document Type</h1>
                  </div>
                  <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                      <li class="breadcrumb-item"><a href="/document_types/">Document Types</a></li>
                      <li class="breadcrumb-item active">Edit Document Type</li>
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
                    <?php include 'shared/document_types/_form.php'; ?>
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
  </body>
<?php include 'layouts/_footer.php'; ?>


