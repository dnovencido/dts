<?php
    include "models/document_type.php";
    include "session.php"; 
    include "require_login.php"; 
    include "require_role.php"; 

    //require_role($_SESSION['id'], ['super_admin', 'administrator', 'employee'], 'student registration');

    if (array_key_exists("id", $_GET)) {
      $document_type = view_document_type($_GET['id']);
    }

?>

<h3 class="d-none" id="page-title">
  Document Type Information
</h3>
<div class="table-responsive">
  <table class="table table-head-fixed text-nowrap">
    <tbody>
      <tr>
        <th class="table-light">Name</th>
        <td><?= htmlspecialchars($document_type['name'], ENT_QUOTES) ?></td>
        <th class="table-light">Type</th>
        <td><?= htmlspecialchars($document_type['type'], ENT_QUOTES) ?></td>
      </tr>
      <tr>
        <th class="table-light">Last Updated</th>
        <td> <?= empty($document_type['last_updated']) ? 'N/A' : date('M d, Y g:i A', strtotime($document_type['last_updated'])) ?></td>
        <th class="table-light">Date Created</th>
        <td><?= date('M d, Y', strtotime($document_type['date_created'])) ?></td>
      </tr>
    </tbody>
  </table>
</div>



