<?php
  include "session.php"; 
  include "models/document.php";
  include "lib/pagination.php";
  include "require_login.php"; 
  include "require_role.php"; 
  include "status.php";

  require_role($_SESSION['id'], ['super_admin', 'administrator', 'employee'], 'outgoing document management');

  $document_types = get_document_types(["outgoing"]);

  $filter = [];

  if (isset($_GET['query']) && $_GET['query'] !== '') {
    $filter['search'] = [
      ['title', 'document_number'],
      $_GET['query']
    ];
  }

  if (!empty($_GET['document_type'])) {
    $filter['item'][] = [
      'column' => 'document_type',
      'value'  => $_GET['document_type']
    ];
  }

  if (!empty($_GET['status'])) {
    $filter['item'][] = [
      'column' => 'status',
      'value'  => $_GET['status']
    ];
  }

  // Outgoing
  $filter['item'][] = [
      'column' => 'category',
      'value'  => 2
  ];

  /* Date Received*/
  if (!empty($_GET['date_received'])) {
    $range = $_GET['date_received'];
    $dates = explode(' - ', $range);

    if (count($dates) === 2) {
      $from = DateTime::createFromFormat('m/d/Y', trim($dates[0]));
      $to   = DateTime::createFromFormat('m/d/Y', trim($dates[1]));
      if ($from && $to) {
        $dateFrom = $from->format('Y-m-d');
        $dateTo   = $to->format('Y-m-d');
        $filter['date_range'] = [
            ['date_received'],
            $dateFrom,
            $dateTo
        ];
      }
    }
  }

  $config = $status_config[$type] ?? ['options' => []];

  if (isset($_GET['page_no'])) {
    $page_no = $_GET['page_no'];
  } else {
    $page_no = 1;
  }
    
  $offset = get_offset($page_no); // calculate the offset based on the current page number

  $document_data = get_all_documents($filter, ['offset'=> $offset, 'total_records_per_page' => TOTAL_RECORDS_PER_PAGE]);
  
  $documents = $document_data['result'] ?? [];
  $total_records = $document_data['total'] ?? 0;

  $current_page = isset($_GET['page_no']) ? (int)$_GET['page_no'] : 1;
  $pagy = pagination($total_records, $page_no); // setup pagination
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
                    <h1>Manage Outgoing Documents</h1>
                  </div>
                  <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                      <li class="breadcrumb-item">Outgoing</li>
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
                    <div id="cards" class="row">
                      <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-success">
                          <div class="inner">
                            <h3><?= get_document_count(["outgoing"], "received") ?></h3>
                            <p>All Outgoing Documents</p>
                          </div>
                          <div class="icon">
                            <i class="ion ion-paper-airplane"></i>
                          </div>
                        </div>
                      </div>
                      <!-- ./col -->
                      <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-danger">
                          <div class="inner">
                              <h3><?= get_document_count(["outgoing"], "pending") ?></h3>
                              <p>Pending Documents</p>
                          </div>
                          <div class="icon">
                           <i class="ion ion-clipboard"></i>
                          </div>
                        </div>
                      </div>
                    </div>                    
                    <div id="search-form" class="card p-3">
                      <p class="text-muted text-uppercase fs-6 fw-bold"><i class="fa-solid fa-magnifying-glass"></i> Search Filter</hp>
                      <form method="get">
                        <div id="form-search">
                          <div class="row">
                            <div class="col-md-3">
                              <div class="form-group">
                                <label for="query">Title / Document Number</label>
                                <input type="text" name="query" id="query" class="form-control" value="<?= isset($_GET['query']) ? htmlspecialchars($_GET['query'], ENT_QUOTES) : '' ?>">
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                <label for="document_type">Document Type</label>
                                <select name="document_type" id="document_type" class="form-control">
                                  <option value="">-- Select Document Type --</option>
                                  <?php foreach($document_types as $type) { ?>
                                    <option value="<?= htmlspecialchars($type['id'], ENT_QUOTES) ?>" <?= (isset($_GET['document_type']) && $_GET['document_type'] == $type['id']) ? 'selected' : '' ?>><?= htmlspecialchars($type['name'], ENT_QUOTES) ?></option>
                                  <?php } ?>
                                </select>
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="">-- Select Status --</option>
                                    <?php foreach ($config['options'] as $value => $label): ?>
                                    <option value="<?= $value ?>"
                                        <?= (($_GET['status'] ?? '') === $value) ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                <label>Date Received:</label>
                                <input type="text" class="form-control date-range" name="date_received" value="<?= isset($_GET['date_received']) ? htmlspecialchars($_GET['date_received'], ENT_QUOTES) : '' ?>"/>
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
                      <a href="/documents/outgoing/new" class="btn bg-gradient-success btn-md"><i class="fa-solid fa-plus"></i> New </a>
                    </div>
                    <div class="card">
                      <div class="card-header">
                          <h4 class="card-title">Documents</h3>
                      </div>
                      <!-- /.card-header -->
                      <div class="card-body p-0">
                        <div class="card-body table-responsive p-0">
                          <table class="table table-striped table-head-fixed text-nowrap">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Document Type</th>
                                    <th>Document Number</th>
                                    <th>Document Date</th>
                                    <th>Status</th>
                                    <th>Date Received</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                              <?php if(!empty($documents)) { ?>
                                <?php foreach($documents as $key => $value ) { ?>
                                    <tr>
                                        <?php $start = ($current_page - 1) * TOTAL_RECORDS_PER_PAGE?>
                                        <td><?= $start + ++$key ?></td>
                                        <td><?= $value['title'] ?></td>
                                        <td><?= $value['document_type_name'] ?></td>
                                        <td><?= $value['document_number'] ?></td>
                                        <td><?= date('M d, Y @ h:i a', strtotime($value['document_date'])) ?></td>
                                        <td><?= ucfirst(strtolower(htmlspecialchars($value['status'], ENT_QUOTES))) ?></td>
                                        <td><?= !empty($value['date_received']) ? date('M d, Y @ h:i a', strtotime($value['date_received'])) : '-' ?></td>
                                        <td class="action-buttons">
                                            <a href="/documents/outgoing/view/<?= $value['id'] ?>" class="btn bg-gradient-info btn-sm"><i class="fa-solid fa-eye"></i></a>
                                            <a href="/documents/outgoing/edit/<?= $value['id'] ?>" class="btn bg-gradient-primary btn-sm"><i class="fa-solid fa-pencil"></i></a>
                                            <a href="#" class="btn bg-gradient-danger btn-sm btn-delete" data-id="<?= $value['id'] ?>" data-url="/documents/delete"><i class="fa-solid fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php } ?>
                              <?php } else { ?>
                                  <td colspan="9">No document(s) to display.</td>
                              <?php } ?>                
                            </tbody>
                          </table>
                        </div>
                      </div>
                      <!-- /.card-body -->
                      </div>
                      <?php if(!empty($documents)) { ?>
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
    <?php include 'shared/_scripts.php'; ?>
    <script>
      $('#document_type').select2();
    </script>
    <script src="/assets/js/date_range.js"></script>
  </body>
<?php include 'layouts/_footer.php'; ?>


