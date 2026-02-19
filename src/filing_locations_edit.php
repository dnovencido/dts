<?php
    include "models/filing_location.php";
    include "session.php"; 
    include "require_login.php"; 
    include "require_role.php"; 

    //require_role($_SESSION['id'], ['super_admin', 'administrator', 'employee'], 'student registration');

    $errors = [];
    if (array_key_exists("id", $_GET)) {
        $filing_location = view_filing_location($_GET['id']);
        $filing_location_id = $filing_location['id'];
        
        if(isset($_POST['submit'])) {
          $errors = validate_filing_location($_POST);
          if(empty($errors)) {
            $save_filing_location = save_filing_location($_POST, $filing_location_id);
            if($save_filing_location) {
              $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => 'You have successfully updated a filing location.'
              ];
              header("Location: /filing_locations/");
            } else {
              $errors[] = "Could not update the filing location. Please try again later.";
            }
          }
        } else {
          $_POST = [
            'name' => isset($_POST['name']) ? $_POST['name'] : $filing_location['name'],
            'type' => isset($_POST['type']) ? $_POST['type'] : $filing_location['type'],
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
                    <h1>Edit Filing Location</h1>
                  </div>
                  <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                      <li class="breadcrumb-item"><a href="/filing_locations/">Filing Locations</a></li>
                      <li class="breadcrumb-item active">Edit Filing Location</li>
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
                    <?php include 'shared/filing_locations/_form.php'; ?>
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


