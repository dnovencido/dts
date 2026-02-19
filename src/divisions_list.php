<?php
  include "session.php"; 
  include "models/division.php";
  include "lib/pagination.php";
  include "require_login.php"; 
  include "require_role.php"; 

  //require_role($_SESSION['id'], ['super_admin', 'administrator', 'employee'], 'student registration');
  $filter = [];

  if (isset($_GET['query']) && $_GET['query'] !== '') {
    $filter['search'] = [
      ['name','head'],
      $_GET['query']
    ];
  }

  if(isset($_GET['page_no'])) {
    $page_no = $_GET['page_no'];
  } else {
    $page_no = 1;
  }
    
  $offset = get_offset($page_no); // calculate the offset based on the current page number

  $division_data = get_all_divisions($filter, ['offset'=> $offset, 'total_records_per_page' => TOTAL_RECORDS_PER_PAGE]);
  
  $divisions = $division_data['result'] ?? [];
  $total_records = $division_data['total'] ?? 0;

  $pagy = pagination($total_records, $page_no); // setup pagination
  $modal_title = "Division Details";
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
                  <h1>Manage Divisions</h1>
                </div>
                <div class="col-sm-6">
                  <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Division</li>
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
                  <div id="search-form" class="card p-3">
                    <p class="text-muted text-uppercase fs-6 fw-bold"><i class="fa-solid fa-magnifying-glass"></i> Search Filter</hp>
                    <form method="get">
                      <div id="form-search">
                        <div class="row">
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="query">Name</label>
                              <input type="text" name="query" id="query" class="form-control" value="<?= isset($_GET['query']) ? htmlspecialchars($_GET['query'], ENT_QUOTES) : '' ?>">
                            </div>
                          </div>
                        </div>
                      </div>
                      <button type="submit" class="btn bg-gradient-primary btn-md">
                        <i class="fa-solid fa-magnifying-glass"></i> Search
                      </button>
                      <a href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?>" 
                        class="btn bg-gradient-secondary btn-md">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                      </a>
                    </form>  
                  </div>
                  <div class="menu mt-5 mb-3">
                    <a href="/divisions/new" class="btn bg-gradient-success btn-md"><i class="fa-solid fa-plus"></i> New </a>
                  </div>
                  <div class="card">
                    <div class="card-header">
                      <h3 class="card-title">Documents</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body p-0">
                      <div class="card-body table-responsive p-0">
                        <table class="table table-striped table-head-fixed text-nowrap">
                          <thead>
                              <tr>
                                  <th>#</th>
                                  <th>Name</th>
                                  <th>Head of Division</th>
                                  <th>Last Updated</th>
                                  <th>Date Created</th>
                                  <th>Actions</th>
                              </tr>
                          </thead>
                          <tbody>
                            <?php if(!empty($divisions)) { ?>
                              <?php foreach($divisions as $key => $value ) { ?>
                                  <tr>
                                      <td><?= ++$key ?></td>
                                      <td><?= $value['name'] ?></td>
                                      <td><?= $value['head'] ?></td>
                                        <td> <?= empty($division['last_updated']) ? 'N/A' : date('M d, Y g:i A', strtotime($division['last_updated'])) ?></td>
                                      <td><?= date('M d, Y @ h:i a', strtotime($value['date_created'])) ?></td>
                                      <td class="action-buttons">
                                          <button data-url="/divisions/view/<?= $value['id'] ?>" class="btn bg-gradient-info btn-sm view-modal"><i class="fa-solid fa-eye"></i></button>
                                          <a href="/divisions/edit/<?= $value['id'] ?>" class="btn bg-gradient-primary btn-sm"><i class="fa-solid fa-pencil"></i></i></a>
                                          <a href="#" class="btn bg-gradient-danger btn-sm btn-delete" data-id="<?= $value['id'] ?>" data-url="/divisions/delete"><i class="fa-solid fa-trash"></i></a>
                                      </td>
                                  </tr>
                              <?php } ?>
                            <?php } else { ?>
                                <td colspan="9">No division(s) to display.</td>
                            <?php } ?>                
                          </tbody>
                        </table>
                      </div>
                    </div>
                    <!-- /.card-body -->
                    </div>
                    <?php if(!empty($divisions)) { ?>
                      <div id="pagination">
                        <ul>
                          <li class="page-item <?= ($page_no <= 1) ? "disabled" : "" ?>"> 
                              <a href="<?= ($page_no > 1) ? '?page_no='.$pagy['previous_page'] : '' ?>" class="page-link">Previous</a>
                          </li>
                          <!-- Page numbers -->
                          <?php for ($counter = 1; $counter <= $pagy['total_no_of_pages']; $counter++) { ?>
                              <?php if ($counter == $page_no) { ?>
                                  <li class="page-item"><a class="page-link active"> <?= $counter ?> </a></li>
                              <?php } else { ?>
                                  <li class="page-item"><a href='?page_no=<?=$counter?>' class="page-link"><?= $counter ?></a></li>
                              <?php } ?>
                          <?php } ?>
                          <!-- Next and last button -->
                          <?php if($page_no < $pagy['total_no_of_pages']) { ?>
                              <li class="page-item <?= ($page_no >= $pagy['total_no_of_pages']) ? "disabled" : "" ?>">
                                  <a href="<?= ($page_no < $pagy['total_no_of_pages']) ?  "?page_no=".$pagy['next_page'] : ""?>" class="page-link"> Next  &rsaquo;&rsaquo; </a>
                              </li>
                              <li class="page-item"><a href="?page_no=<?=$pagy['total_no_of_pages']?>" class="page-link">Last</a></li>
                          <?php } ?>
                        </ul>
                      </div>
                    <?php } ?>
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
    <?php include 'shared/_modal.php'; ?>
    <?php include 'shared/_scripts.php'; ?>
  </body>
<?php include 'layouts/_footer.php'; ?>


