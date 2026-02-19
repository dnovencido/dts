<?php
    include "models/receiving_office.php";
    include "session.php"; 
    include "require_login.php"; 
    include "require_role.php"; 

    //require_role($_SESSION['id'], ['super_admin', 'administrator', 'employee'], 'student registration');

    if (array_key_exists("id", $_GET)) {
      $receiving_office = view_receiving_office($_GET['id']);
    }

?>

<h3 class="d-none" id="page-title">
  Division Information
</h3>

<table class="table table-head-fixed text-nowrap">
  <tbody>
    <tr>
      <th class="table-light">Name</th>
      <td colspan="3"><?= htmlspecialchars($receiving_office['name'], ENT_QUOTES) ?></td>
    </tr>
    <tr>
      <th class="table-light">Last Updated</th>
      <td> <?= empty($receiving_office['last_updated']) ? 'N/A' : date('M d, Y g:i A', strtotime($receiving_office['last_updated'])) ?></td>
      <th class="table-light">Date Created</th>
      <td><?= date('M d, Y', strtotime($receiving_office['date_created'])) ?></td>
    </tr>
  </tbody>
</table>




