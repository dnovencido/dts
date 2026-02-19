<?php
    include "models/division.php";
    include "session.php"; 
    include "require_login.php"; 
    include "require_role.php"; 

    //require_role($_SESSION['id'], ['super_admin', 'administrator', 'employee'], 'student registration');

    $errors = [];
    if (array_key_exists("id", $_GET)) {
        $division = view_division($_GET['id']);
        $document_id = $division['id'];
        
        if(isset($_POST['submit'])) {
          $errors = validate_division($_POST);
          if(empty($errors)) {
            $save_division = save_division($_POST, $document_id);
            if($save_division) {
              $_SESSION['flash_message'] = [
                  'type' => 'success',
                  'text' => 'You have successfully updated a division.'
              ];
              header("Location: /divisions/");
            } else {
              $errors[] = "Could not update the division. Please try again later.";
            }
          }
        } else {
          $_POST = [
            'name' => isset($_POST['name']) ? $_POST['name'] : $division['name'],
            'head' => isset($_POST['head']) ? $_POST['head'] : $division['head'],
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
                    <h1>Edit Division</h1>
                  </div>
                  <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                      <li class="breadcrumb-item"><a href="/divisions/">Divisions</a></li>
                      <li class="breadcrumb-item active">Edit Division</li>
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
                    <?php include 'shared//division/_form.php'; ?>
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


