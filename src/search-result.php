<?php
  include "session.php"; 
  include "models/document.php";
  include "lib/pagination.php";
  include "require_login.php"; 
  include "require_role.php"; 
  include "status.php";

  require_role($_SESSION['id'], ['super_admin', 'administrator', 'employee'], 'incoming document management');

  $filter = [];

  if(isset($_GET['query']) && $_GET['query'] !== '') {
    $filter['search'] = [
      ['title', 'document_number'],
      $_GET['query']
    ];
  }

  $config = $status_config[$type] ?? ['options' => []];

  if(isset($_GET['page_no'])) {
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
            <?php include 'layouts/_sidebar.php'; ?>
            <!-- Page Header -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                        <h4 class="fw-bold mb-0">Documents</h4>
                        <small class="text-muted">
                            <?= $total_records ?> result(s) found
                        </small>
                        </div>
                    </div>
                </div>
            </section>
            <section class="content">
                <div class="container-fluid">
                    <!-- Document Grid -->
                    <?php if(!empty($documents)) { ?>
                        <div class="row">

                            <?php 
                            $start = ($current_page - 1) * TOTAL_RECORDS_PER_PAGE;

                            foreach($documents as $key => $doc) {

                                $mime_type = $doc['file_type'] ?? '';

                                $border_color = 'secondary';
                                $file_icon = 'fa-file';

                                if (str_starts_with($mime_type, 'image/')) {
                                $border_color = 'success';
                                $file_icon = 'fa-file-image';
                                }
                                elseif ($mime_type === 'application/pdf') {
                                $border_color = 'danger';
                                $file_icon = 'fa-file-pdf';
                                }
                                elseif (in_array($mime_type, [
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel'
                                ])) {
                                $border_color = 'success';
                                $file_icon = 'fa-file-excel';
                                }
                                elseif (in_array($mime_type, [
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/msword'
                                ])) {
                                $border_color = 'primary';
                                $file_icon = 'fa-file-word';
                                }
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <a href="/documents/incoming/view/<?= $doc['id'] ?>"
                                    class="text-decoration-none text-dark">
                                    <div class="card ripple-card card-result shadow-sm document-card border-left-<?= $border_color ?>">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <!-- LEFT: ICON -->
                                                <div class="icon-wrapper me-3 text-center">
                                                    <i class="fa-solid <?= $file_icon ?> fa-2x text-muted"></i>
                                                </div>
                                                <!-- RIGHT: INFO -->
                                                <div class="flex-grow-1 ml-3">
                                                    <div class="d-flex justify-content-between">
                                                        <h6 class="fw-bold mb-1 text-truncate">
                                                            <?= htmlspecialchars($doc['title']) ?>
                                                        </h6>
                                                        <span class="badge bg-<?= $doc['status'] ?? 'secondary' ?>">
                                                            <?= ucfirst(strtolower(htmlspecialchars($doc['status']))) ?>
                                                    </div>
                                                    <p class="text-muted mb-1">
                                                        <?= date('M d, Y', strtotime($doc['document_date'])) ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php } ?>
                        </div>
                        <?php } else { ?>
                            <div class="card shadow-sm border-0">
                                <div class="card-body text-center py-5">
                                <i class="fa-solid fa-folder-open fa-2x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No documents found.</p>
                                </div>
                            </div>
                        <?php } ?>
                        <!-- Pagination -->
                        <?php if(!empty($documents)) { ?>
                        <div class="d-flex justify-content-center mt-4">
                            <ul class="pagination">

                            <?php for ($counter = 1; $counter <= $pagy['total_no_of_pages']; $counter++) { ?>
                                <li class="page-item <?= ($counter == $current_page) ? 'active' : '' ?>">
                                <a class="page-link"
                                    href="?page_no=<?= $counter ?>">
                                    <?= $counter ?>
                                </a>
                                </li>
                            <?php } ?>

                            </ul>
                        </div>
                    <?php } ?>
                </div>
            </section>
        </div>
      </div>
    <?php include 'shared/_scripts.php'; ?>
    <script>
    </script>
  </body>
<?php include 'layouts/_footer.php'; ?>


