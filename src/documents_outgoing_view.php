<?php
  include "models/document.php";
  include "models/division.php";
  include "models/stakeholder.php";
  include "session.php"; 
  include "require_login.php"; 
  include "require_role.php"; 

  require_role($_SESSION['id'], ['super_admin', 'administrator', 'employee'], 'outgoing document management');

  if(array_key_exists("id", $_GET)) {
    $document = view_document($_GET['id']);
    $document_id = $document['id'];
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
                <?php if(isset($_SESSION['flash_message'])) { ?>
                  <?php include "layouts/_messages.php"; ?>
                <?php } ?>
                <div class="row m-2">
                  <div class="col-sm-6">
                    <h1><?= $document['title'] ?></h1>
                  </div>
                  <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                      <li class="breadcrumb-item"><a href="/documents/outgoing">Outgoing</a></li>
                      <li class="breadcrumb-item active"><?= $document['title'] ?></li>
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
                    <div class="card">
                        <div class="card-header">
                        <h4 class="card-title">Document Information</h3>
                        <div class="card-tools float-right">
                            <a href="/documents/outgoing/edit/<?= $document['id'] ?>" class="btn bg-gradient-primary btn-sm"><i class="fa-solid fa-pencil"></i></a>
                            <a href="#" class="btn bg-gradient-danger btn-sm" data-id="<?= $document['id'] ?>"><i class="fa-solid fa-trash"></i></a>
                        </div>
                      </div>
                      <!-- /.card-header -->
                      <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-head-fixed text-nowrap">
                              <tbody>
                                <tr>
                                  <th class="table-light">Title</th>
                                  <td colspan="2"><?= htmlspecialchars($document['title'], ENT_QUOTES) ?></td>
                                  <th class="table-light">Document Type</th>
                                  <td colspan="2"><?= htmlspecialchars($document['document_type_name'], ENT_QUOTES) ?></td>
                                  <th class="table-light">Document Number</th>
                                  <td><?= htmlspecialchars($document['document_number'], ENT_QUOTES) ?></td>
                                </tr>
                                <tr>
                                  <th class="table-light">Document Date</th>
                                  <td><?= date('M d, Y', strtotime($document['document_date'])) ?></td>
                                  <th class="table-light">Date Received</th>
                                  <td><?= !empty($document['date_received']) ? date('M d, Y', strtotime($document['date_received'])) : '-' ?></td>
                                  <th class="table-light">Concerned Division</th>
                                    <?php
                                      $divisions = get_division_names_by_ids(
                                        json_decode($document['concerned_division'], true)
                                      );
                                      $names = array_column($divisions, 'name');
                                      $heads = array_column($divisions, 'head');
                                    ?>
                                    <td>
                                    <?= htmlspecialchars(implode(', ', $names)) ?>
                                    </td>
                                  </td>
                                  <th class="table-light">Head Division</th>
                                  <td><?= htmlspecialchars(implode(', ', $heads)) ?></td>
                                </tr>
                                <tr>
                                  <th class="table-light">Stakeholder Names</th>
                                    <?php $stakeholders = get_stakeholders(
                                      json_decode($document['names_stakeholders'], true)
                                    ); ?>
                                  <td><?= htmlspecialchars(implode(', ',   $stakeholders)) ?></td>
                                  <th class="table-light">Receiving Office</th>
                                  <td><?= htmlspecialchars($document['receiving_office_name'], ENT_QUOTES) ?></td>
                                  <th class="table-light">Signatories</th>
                                    <?php $signatories = get_signatories(
                                      json_decode($document['signatories'], true)
                                    ); ?>
                                    <td colspan="3"><?= htmlspecialchars(implode(', ', $signatories)) ?></td>
                                </tr>
                                <tr>
                                  <th class="table-light">Encoded By</th>
                                  <td colspan="2"><?= htmlspecialchars($document['emp_name'], ENT_QUOTES) ?></td>
                                  <th class="table-light">Location of Filing</th>
                                  <td colspan="2"><?= !empty($document['filing_location_name']) ? htmlspecialchars($document['filing_location_name'], ENT_QUOTES) : '-' ?></td>
                                  <th class="table-light">Status</th>
                                  <td colspan="2"><?= !empty($document['status']) ? ucfirst(strtolower(htmlspecialchars($document['status'], ENT_QUOTES))) : '-' ?></td>
                                </tr>
                              </tbody>
                            </table>
                        </div>
                        <div id="document-viewer" class="mt-4">
                          <?php include "shared/_file-viewer.php"; ?>
                        </div>
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
<?php include 'layouts/_footer.php'; ?>


