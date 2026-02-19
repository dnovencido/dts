<?php
  include "db/db.php"; 
  include "models/user.php";
  include "models/roles.php";
  include "models/user_role.php";
  include "session.php"; 
  include "require_login.php";

  if (!isset($_GET['id'])) {
    die("No user selected");
  }

  $user_id = (int)$_GET['id'];

  if(isset($_POST['submit'])) {
    $role_id = (int)$_POST['role_id'];
    if(update_profile($user_id, $_POST['fname'], $_POST['mname'], $_POST['lname'], $_POST['employee_id'],  $_POST['email'], $_POST['position'], $_POST['password']) && update_user_role($user_id, $role_id)) {
      $_SESSION['flash_message'] = [
        'type' => 'success',
        'text' => 'You have successfully updated a user.'
      ];
      header("Location: users.php?success=role_updated");
      exit;
    }
  }
  // Get user info
  $user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

  // Get roles
  $roles = get_all_roles();

  // Get current role
  $current_role = get_user_roles($user_id, 'ids');
  $current_role_id = $current_role[0] ?? '';
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
                    <h1>Edit User Details</h1>
                  </div>
                  <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                      <li class="breadcrumb-item"><a href="#">Account</a></li>
                      <li class="breadcrumb-item"><a href="users.php">Users</a></li>
                      <li class="breadcrumb-item active">Edit User Details</li>
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
                          <h4 class="card-title">User Details</h3>
                      </div>
                      <!-- /.card-header -->
                      <div class="card-body p-4">
                        <form method="post">
                          <div class="row justify-content-center">
                            <div class="col-md-6">
                              <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="fname" class="form-control" value="<?= htmlspecialchars($user['fname']) ?>" required>
                              </div>
                              <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" name="mname" class="form-control" value="<?= htmlspecialchars($user['mname']) ?>">
                              </div>
                              <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="lname" class="form-control" value="<?= htmlspecialchars($user['lname']) ?>" required>
                              </div>
                              <div class="form-group">
                                <label>Employee ID</label>
                                <input type="text" name="employee_id" class="form-control" value="<?= htmlspecialchars($user['employee_id']) ?>">
                              </div>
                              <div class="form-group">
                                <label>Position</label>
                                <input type="text" name="position" class="form-control" value="<?= htmlspecialchars($user['position']) ?>">
                              </div>
                              <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                              </div>
                              <div class="form-group">
                                <label>Password (Leave blank to keep current)</label>
                                <input type="password" name="password" class="form-control">
                              </div>
                              <div class="form-group">
                                <label for="role_id">Select Role:</label>
                                <select name="role_id" id="role_id" class="form-control">
                                  <option value="">-- Select Role --</option>
                                  <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= ($r['id'] == $current_role_id) ? "selected" : "" ?>>
                                      <?= htmlspecialchars($r['role_name']) ?>
                                    </option>
                                  <?php endforeach; ?>
                                </select>
                              </div>
                              <button type="submit" name="submit" class="btn btn-primary btn-md mt-3">Save Changes</button>
                            </div>
                          </div>
                        </form>
                      </div>
                      <!-- /.card-body -->
                      </div>
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
