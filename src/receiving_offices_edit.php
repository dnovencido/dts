<?php
    include "models/receiving_office.php";
    include "session.php"; 
    include "require_login.php"; 
    include "require_role.php"; 

    //require_role($_SESSION['id'], ['super_admin', 'administrator', 'employee'], 'student registration');

    $errors = [];
    if (array_key_exists("id", $_GET)) {
        $receiving_office = view_receiving_office($_GET['id']);
        $document_id = $receiving_office['id'];
        
        if(isset($_POST['submit'])) {
          $errors = validate_receiving_office($_POST);
          if(empty($errors)) {
            $save_receiving_office = save_receiving_office($_POST, $document_id);
            if($save_receiving_office) {
              $_SESSION['flash_message'] = [
                  'type' => 'success',
                  'text' => 'You have successfully updated a receiving office.'
              ];
              header("Location: /receiving_offices/");
            } else {
              $errors[] = "Could not update the receiving office. Please try again later.";
            }
          }
        } else {
          $_POST = [
            'name' => isset($_POST['name']) ? $_POST['name'] : $receiving_office['name'],
            'head' => isset($_POST['head']) ? $_POST['head'] : $receiving_office['head'],
          ];
        }
    }
?>
<?php include 'layouts/_header.php'; ?>
  <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
      <div class="wrapper">
        <div class="content-wrapper">
          <?php include 'layouts/_navbar.php'; ?>
            <!-- Content Header (Page header) -->
            <section class="content-header">
              <div class="container-fluid">
                <div class="row mb-2">
                  <div class="col-sm-6">
                    <h1>Edit Receiving Office</h1>
                  </div>
                  <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                      <li class="breadcrumb-item"><a href="/receiving_offices/">Receiving Offices</a></li>
                      <li class="breadcrumb-item active">Edit Receiving Office</li>
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
                    <?php include 'shared/receiving_office/_form.php'; ?>
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


