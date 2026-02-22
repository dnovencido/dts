<?php
  include "session.php"; 
  include "models/user_role.php"; 
  include "models/document.php";
  include "require_login.php"; 

  $document_count = get_document_counts();

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
                    <h1>Dashboard</h1>
                  </div>
                  <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                      <li class="breadcrumb-item"><a href="#">Account</a></li>
                      <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                  </div>
                </div>
              </div><!-- /.container-fluid -->
            </section>
            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                  <?php if(isset($_SESSION['flash_message'])) { ?>
                    <?php include "layouts/_messages.php"; ?>
                  <?php } ?>
                    <div class="row">
                      <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-info">
                          <div class="inner">
                            <h3><?= $document_count['incoming'] ?></h3>
                            <p>Incoming Documents</p>
                          </div>
                          <div class="icon">
                            <i class="ion ion-document-text"></i>
                          </div>
                          <a href="/documents/incoming" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                      </div>
                      <!-- ./col -->
                      <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-success">
                          <div class="inner">
                            <h3><?= $document_count['outgoing'] ?></h3>
                            <p>Outgoing Documents</p>
                          </div>
                          <div class="icon">
                            <i class="ion ion-paper-airplane"></i>
                          </div>
                          <a href="/documents/outgoing" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                      </div>
                      <!-- ./col -->
                      <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-warning">
                          <div class="inner">
                            <h3><?= $document_count['incoming_pending'] ?></h3>
                            <p>Pending Incoming Documents</p>
                          </div>
                          <div class="icon">
                            <i class="ion ion-clock"></i>
                          </div>
                          <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                      </div>
                      <!-- ./col -->
                      <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-danger">
                          <div class="inner">
                            <h3><?= $document_count['outgoing_pending'] ?></h3>
                            <p>Pending Outgoing Documents</p>
                          </div>
                          <div class="icon">
                            <i class="ion ion-clipboard"></i>
                          </div>
                          <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                      </div>
                      <!-- ./col -->
                    </div>
                    <div class="row">
                      <div class="col-md-7">
                        <div class="card">
                          <div class="card-header">
                            <h3 class="card-title">
                              <i class="fas fa-chart-pie mr-1"></i>
                               Total number of documents
                            </h3>
                            <div class="card-tools">
                              <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                              <label class="btn btn-outline-secondary btn-sm active flex-fill">
                                  <input type="radio" name="range" value="3" autocomplete="off" checked> 3 Months
                              </label>
                              <label class="btn btn-outline-secondary btn-sm flex-fill">
                                  <input type="radio" name="range" value="6" autocomplete="off"> 6 Months
                              </label>
                              <label class="btn btn-outline-secondary btn-sm flex-fill">
                                  <input type="radio" name="range" value="12" autocomplete="off"> 1 Year
                              </label>
                              </div>
                            </div>
                          </div><!-- /.card-header -->
                          <div class="card-body">
                            <canvas id="document_line_chart"></canvas>
                          </div><!-- /.card-body -->
                        </div>
                      </div>
                      <div class="col-md-5">
                        <div class="row">
                          <div class="col-sm-12">
                            <div class="card">
                              <div class="card-header">
                                <h3 class="card-title">
                                  <i class="fas fa-chart-pie mr-1"></i>
                                  Documents by type
                                </h3>
                                <div class="card-tools form-group">
                                  <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                                    <label class="btn btn-outline-secondary btn-sm active flex-fill">
                                        <input type="radio" name="category" value="1" autocomplete="off" checked> Incoming
                                    </label>
                                    <label class="btn btn-outline-secondary btn-sm flex-fill">
                                        <input type="radio" name="category" value="2" autocomplete="off"> Outgoing
                                    </label>
                                  </div>
                                </div>
                              </div><!-- /.card-header -->
                              <div class="card-body">
                                <canvas id="document_type_doughnut"></canvas>
                              </div><!-- /.card-body -->
                            </div>
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
    <!-- Chart JS -->
    <script src="/assets/chartjs/chartjs.min.js"></script>
    <script src="/assets/js/dashboard.js"></script>
  </body>
<?php include 'layouts/_footer.php'; ?>


