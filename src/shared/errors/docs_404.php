<?php
  // Default values (can be overridden)
  $title   = $title   ?? "404 - Not Found";
  $message = $message ?? "The requested resource could not be found.";
  $backUrl = $backUrl ?? "/";
?>
<?php include 'layouts/_header.php'; ?>
  <body class="hold-transition sidebar-collapse sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
      <div class="wrapper">
        <div class="content-wrapper">
          <?php include 'layouts/_navbar.php'; ?>
            <!-- Main content -->
            <section class="content">
              <div class="container-fluid">
                <div class="row d-flex align-items-center justify-content-center">
                  <div class="w-60">
                    <div class="error-page d-flex align-items-center" style="height: 80vh; w">
                        <h2 class="headline text-warning"> 404</h2>
                        <div class="error-content">
                            <h3><i class="fas fa-exclamation-triangle text-warning"></i> <?= htmlspecialchars($title) ?></h3>
                            <p class="text-muted mb-4"><?= htmlspecialchars($message) ?></p>
                            <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-primary">
                                Go Back
                            </a>
                        </div>
                        <!-- /.error-content -->
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


