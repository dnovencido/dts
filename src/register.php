<?php
    include "models/signup.php";
    include "session.php";

    $errors = [];

    if(isset($_SESSION['id'])) {
        header("Location: /documents/incoming");
    }

    if(isset($_POST['submit'])) {
        if(!$_POST['fname']) {
            $errors[] = "First Name is required.";
        }
        if(!$_POST['lname']) {
            $errors[] = "Last Name is required.";
        }
        if(!$_POST['email']) {
            $errors[] = "Email is required.";
        }
        if(!$_POST['password']) {
            $errors[] = "Password is required.";
        }
        if($_POST['password'] != $_POST['confirm_password']) {
            $errors[] = "You must confirm your password.";
        }
        if(!$_POST['employee_id']) {
            $errors[] = "Employee ID is required.";
        }
        if(!$_POST['position']) {
            $errors[] = "Position is required.";
        }

        if(empty($errors)) {
            if(!check_existing_email($_POST['email'])) {
                $user = save_registration($_POST['fname'],$_POST['mname'], $_POST['lname'], $_POST['email'], $_POST['password'], $_POST['employee_id'], $_POST['position']);
                if(!empty($user)) {
                    // Load mailer
                    require "lib/mailer.php";
                    $verify_link = "http://localhost:9001/verify.php?token=" . $user['token'];

                    $email_body = "
                        Hi {$user['fname']},<br><br>
                        Thank you for registering.<br><br>
                        Please verify your account by clicking the link below:<br><br>
                        <a href='{$verify_link}'>Verify Account</a><br><br>
                        This link will expire in 24 hours.<br><br>
                        Thank you.
                    ";

                    $mail_result = send_mail(
                        $user['email'],
                        $user['fname'],
                        "Verify Your Account",
                        $email_body
                    );

                    if ($mail_result === true) {
                        $_SESSION['flash_message'] = [
                            'type' => 'success',
                            'text' => 'Registration successful! Please check your email to verify your account.'
                        ];
                        header("Location: /login");
                        exit;
                    } else {
                        $errors[] = "Registration successful but email could not be sent.";
                    }

                    // $_SESSION['id'] = $user['id'];
                    // $_SESSION['fname'] = $user['fname'];
                    // $_SESSION['flash_message'] = [
                    //     'type' => 'success',
                    //     'text' => 'You have successfully created an account.'
                    // ];
                    // header("Location: /dashboard/");
                    // exit;
                } else {
                    $errors[] = "There was an error logging in your account.";
                }
            } else {
                $errors[] = "Email address already exist.";
            }
        }
    }
?>
<?php include 'layouts/_header.php'; ?>
<body class="register-page" style="min-height: 570.8px;">
    <div class="register-box">
        <div class="register-logo">
            <div id="logo-header">
                <div id="logo" class="d-flex justify-content-center gap-3 mb-3">
                    <img src="assets/images/logo.png" alt="">
                    <img src="assets/images/dpwh.jpeg" alt="">
                </div>
                <h1 class="logo-label">Document Tracking System</h1>
            </div>
        </div>
        <?php if (!empty($errors)) { ?>
            <?php include "layouts/_errors.php" ?>
        <?php } ?>
        <div class="card">
            <div class="card-body register-card-body">
            <p class="login-box-msg">Register Employee Account</p>
            <form method="post">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="fname" value="<?= htmlspecialchars($_POST['fname'] ?? '', ENT_QUOTES) ?>" placeholder="First Name">
                    <div class="input-group-append">
                        <div class="input-group-text">
                        <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="mname" value="<?= htmlspecialchars($_POST['mname'] ?? '', ENT_QUOTES) ?>" placeholder="Middle Name">
                    <div class="input-group-append">
                        <div class="input-group-text">
                        <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="lname" value="<?= htmlspecialchars($_POST['lname'] ?? '', ENT_QUOTES) ?>" placeholder="Last Name">
                    <div class="input-group-append">
                        <div class="input-group-text">
                        <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>" placeholder="Email">
                    <div class="input-group-append">
                        <div class="input-group-text">
                        <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                 <div class="input-group mb-3">
                    <input type="text" class="form-control" name="employee_id" value="<?= htmlspecialchars($_POST['employee_id'] ?? '', ENT_QUOTES) ?>" placeholder="Employee ID">
                    <div class="input-group-append">
                        <div class="input-group-text">
                        <span class="fas fa-id-card"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="position" value="<?= htmlspecialchars($_POST['position'] ?? '', ENT_QUOTES) ?>" placeholder="Position">
                    <div class="input-group-append">
                        <div class="input-group-text">
                        <span class="fas fa-briefcase"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" name="password" value="<?= htmlspecialchars($_POST['password'] ?? '', ENT_QUOTES) ?>" placeholder="Password" >
                    <div class="input-group-append">
                        <div class="input-group-text">
                        <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" name="confirm_password" placeholder="Retype password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                        <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <button type="submit" name="submit" class="btn btn-primary btn-block">Register</button>
                    </div>
                </div>
            </form>
            <a href="login" class="text-center">I already have an account</a>
            </div>
            <!-- /.form-box -->
        </div><!-- /.card -->
    </div>
    <?php include 'shared/_scripts.php'; ?>
</body>
<?php include 'layouts/_footer.php'; ?>