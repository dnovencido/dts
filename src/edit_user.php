<?php
include "db/db.php";
include "models/user.php";
include "models/roles.php";
include "models/user_role.php";
include "models/assigned_office.php";
include "session.php";
include "require_login.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid user selected.");
}

$user_id = (int) $_GET['id'];

/* ----------------------------
   FETCH USER DATA
-----------------------------*/
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("User not found.");
}

$current_office   = get_assigned_office($user_id);
$roles            = get_all_roles();
$current_role_ids = get_user_roles($user_id, 'ids');
$role_id  = $current_role_ids[0] ?? '';
$assigned_offices = get_all_assigned_offices();
$current_office_id = $current_office ? $current_office : '';

/* ----------------------------
   HANDLE FORM SUBMIT
-----------------------------*/
if (isset($_POST['submit'])) {
    $fname            = trim($_POST['fname']);
    $mname            = trim($_POST['mname']);
    $lname            = trim($_POST['lname']);
    $employee_id      = trim($_POST['employee_id']);
    $email            = trim($_POST['email']);
    $position         = trim($_POST['position']);
    $password         = $_POST['password'];
    $role_id          = (int) $_POST['role_id'];
    $assigned_office  = trim($_POST['assigned_office']);
    $is_verified      = isset($_POST['is_verified']) ? 1 : 0;

    $update_user   = update_profile(
        $user_id,
        $fname,
        $mname,
        $lname,
        $employee_id,
        $email,
        $position,
        $password,
        $is_verified
    );

    $update_role   = update_user_role($user_id, $role_id);
    $update_office = update_assigned_office($user_id, $assigned_office);

    if ($update_user && $update_role && $update_office) {

        $_SESSION['flash_message'] = [
            'type' => 'success',
            'text' => 'User successfully updated.'
        ];

        header("Location: users.php");
        exit;
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
                                    <option value="<?= $r['id'] ?>" <?= ($r['id'] == $role_id) ? "selected" : "" ?>>
                                      <?= htmlspecialchars($r['role_name']) ?>
                                    </option>
                                  <?php endforeach; ?>
                                </select>
                              </div>
                                <div class="form-group">
                                    <label for="assigned_office">Select Assigned Office:</label>

                                    <select id="assigned_office" 
                                            class="form-control" 
                                            name="assigned_office">

                                        <option value="">-- Select Assigned Office --</option>

                                        <?php 
                                            $selected_value = $_POST['assigned_office'] ?? $current_office_id;

                                            foreach ($assigned_offices['result'] as $office): 
                                        ?>

                                            <option value="<?= htmlspecialchars($office['id']) ?>"
                                                <?= ((string)$selected_value === (string)$office['id']) ? 'selected' : '' ?>>

                                                <?= htmlspecialchars($office['name']) ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>
                                </div>
                                <div class="form-group">
                                <label>
                                    <input type="checkbox" name="is_verified" value="1"
                                        <?= ($user['is_verified'] == 1) ? 'checked' : '' ?>>
                                    Mark as Verified
                                </label>
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
