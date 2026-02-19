<?php
    include "models/receiving_office.php";
    include "session.php"; 
    include "require_login.php"; 
    include "require_role.php"; 

    //require_role($_SESSION['id'], ['super_admin', 'administrator', 'employee'], 'student registration');

    $errors = [];
    if(isset($_POST['submit'])) {
      $errors = validate_receiving_office($_POST);
      if(empty($errors)) {
        $_POST['category'] = 1;
        $save_receiving_office = save_receiving_office($_POST);
        if($save_receiving_office) {
          $_SESSION['flash_message'] = [
            'type' => 'success',
            'text' => 'You have successfully added new receiving office.'
          ];
          header("Location: /receiving_offices");
          exit;
        } else {
          $errors[] = "Could save receiving office. Please try again later.";
        }        
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
                    <h1>New Receiving Office</h1>
                  </div>
                  <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                      <li class="breadcrumb-item"><a href="/receiving-offices">Receiving Offices</a></li>
                      <li class="breadcrumb-item active">New Receiving Office</li>
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


