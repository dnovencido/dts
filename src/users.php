<?php
  include "models/user.php";
  include "lib/pagination.php";
  include "session.php"; 
  include "require_login.php"; 
  include "require_role.php"; 
  
  require_role($_SESSION['id'], ['super_admin', 'administrator'], 'user accounts');

  $filter = [];

  if(array_key_exists("query", $_GET)) {
    if(!empty($_GET['query'])) {
      $filter['search'] = [['employee_id', 'lname', 'fname'], $_GET['query']];
    }
  }
  
  if (isset($_GET['page_no'])) {
    $page_no = $_GET['page_no'];
  } else {
    $page_no = 1;
  }
    
  $offset = get_offset($page_no); // calculate the offset based on the current page number

  $registration_data = get_all_users($filter, ['offset'=> $offset, 'total_records_per_page' => TOTAL_RECORDS_PER_PAGE]);
  
  $users = $registration_data['result'] ?? [];
  $total_records = $registration_data['total'] ?? 0;

  $pagy = pagination($total_records, $page_no); // setup pagination
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
                    <h1>Manage Users</h1>
                  </div>
                  <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                      <li class="breadcrumb-item"><a href="#">Account</a></li>
                      <li class="breadcrumb-item active">Users</li>
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
                    <div class="card">
                      <div class="card-header">
                        <h4 class="card-title">Users Details</h3>
                      </div>
                      <!-- /.card-header -->
                      <div class="card-body p-0">
                        <div class="card-body table-responsive p-0">
                          <table class="table table-striped table-head-fixed text-nowrap">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>First Name</th>
                                    <th>Middle Name</th>
                                    <th>Last Name</th>
                                    <th>Email</th>
                                    <th>Position</th>
                                    <th>Role</th>
                                    <th>Date Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                              <?php if(!empty($users)) { ?>
                                <?php foreach($users as $key => $value ) { ?>
                                    <tr>
                                        <td><?= ++$key ?></td>
                                        <td><?= $value['fname'] ?></td>
                                        <td><?= $value['mname'] ?></td>
                                        <td><?= $value['lname'] ?></td>
                                        <td><?= $value['email'] ?></td>
                                        <td><?= $value['position'] ?></td>
                                        <td><?= get_user_role_name($value['id']) ?></td>
                                        <td><?= date('M d, Y @ h:i a', strtotime($value['date_created'])) ?></td>
                                        <td class="action-buttons">
                                          <a href="edit_user.php?id=<?= $value['id'] ?>" class="btn bg-gradient-primary btn-sm">
                                            <i class="fa-solid fa-user-pen"></i>
                                          </a>
                                        </td>
                                    </tr>
                                <?php } ?>
                              <?php } else { ?>
                                  <td colspan="6">No user(s) to display.</td>
                              <?php } ?>                
                            </tbody>
                          </table>
                        </div>
                      </div>
                      <!-- /.card-body -->
                    </div>
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


