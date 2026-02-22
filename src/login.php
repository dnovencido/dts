<?php 
    include "models/signin.php";
    include "session.php";

    $errors = []; 

    if(isset($_SESSION['id'])) {
        header("Location: /dashboard/");
    }

    if(isset($_POST['submit'])) {
        if(!$_POST['email']) {
            $errors[] = "Email is required.";
        }
        if(!$_POST['password']) {
            $errors[] = "Password is required.";
        }
        if(empty($errors)) {
            $user = login_account($_POST['email'], $_POST['password']);
            if(!empty($user)) {
                // Check if verified
                if (is_account_verified($_POST['email'])) {
                    $_SESSION['id'] = $user['id'];
                    $_SESSION['fname'] = $user['fname'];

                    header("Location: /dashboard/");
                    exit;
                } else {
                    $errors[] = "Your account is not verified. Please check your email.";
                }
            } else {    
                $errors[] = "The email that you've entered does not match any account.";
            }
        }
    } 
?>
<?php include 'layouts/_header.php'; ?>
<body class="login-page" style="min-height: 466px;">
    <div class="login-box">
    <!-- /.login-logo -->
    <?php if (!empty($errors)) { ?>
        <?php include "layouts/_errors.php" ?>
    <?php } ?>
    <?php if(isset($_SESSION['flash_message'])) { ?>
        <?php include "layouts/_messages.php"; ?>
    <?php } ?>
    <div class="card card-outline card-primary">
        <div id="logo-header" class="card-header text-center">
            <div id="logo" class="d-flex justify-content-center gap-3 mb-3">
                <img src="assets/images/logo.png" alt="">
                <img src="assets/images/dpwh.png" alt="">
            </div>
            <h1>Document Tracking System</h1>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Sign in to your account</p>
            <form method="post">
                <div class="input-group mb-3">
                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>" placeholder="Email">
                <div class="input-group-append">
                    <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                    </div>
                </div>
                </div>
                <div class="input-group mb-3">
                <input type="password" class="form-control" name="password" value="<?= htmlspecialchars($_POST['password'] ?? '', ENT_QUOTES) ?>" placeholder="Password">
                <div class="input-group-append">
                    <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                    </div>
                </div>
                </div>
                <div class="row">
                <div class="col-8">
                    <div class="icheck-primary">
                        <input type="checkbox" id="remember">
                        <label for="remember">
                            Remember Me
                        </label>
                    </div>
                </div>
                <!-- /.col -->
                <div class="col-4">
                    <button type="submit" name="submit" class="btn btn-primary btn-block">Sign In</button>
                </div>
                <!-- /.col -->
                </div>
            </form>
            <p class="mb-1">
                <a href="register" class="text-center">Register an account</a>
            </p>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
    </div> 
    <script src="/assets/jquery/jquery.min.js"></script>  
    <script src="/assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/admin-lte/js/adminlte.min.js"></script>
</body>
<?php include 'layouts/_footer.php'; ?>