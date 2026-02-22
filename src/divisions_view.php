<?php
    include "models/division.php";
    include "session.php"; 
    include "require_login.php"; 
    include "require_role.php"; 

    //require_role($_SESSION['id'], ['super_admin', 'administrator', 'employee'], 'student registration');

    if (array_key_exists("id", $_GET)) {
      $division = view_division($_GET['id']);
    }

?>

<h3 class="d-none" id="page-title">
  Division Information
</h3>
<div class="table-responsive">
  <table class="table table-head-fixed text-nowrap">
    <tbody>
      <tr>
        <th class="table-light">Name</th>
        <td><?= htmlspecialchars($division['name'], ENT_QUOTES) ?></td>
        <th class="table-light">Head</th>
        <td><?= htmlspecialchars($division['head'], ENT_QUOTES) ?></td>
      </tr>
      <tr>
        <th class="table-light">Last Updated</th>
        <td> <?= empty($division['last_updated']) ? 'N/A' : date('M d, Y g:i A', strtotime($division['last_updated'])) ?></td>
        <th class="table-light">Date Created</th>
        <td><?= date('M d, Y', strtotime($division['date_created'])) ?></td>
      </tr>
    </tbody>
  </table>
</div>

