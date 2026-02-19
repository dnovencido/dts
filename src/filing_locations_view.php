<?php
    include "models/filing_location.php";
    include "session.php"; 
    include "require_login.php"; 
    include "require_role.php"; 

    //require_role($_SESSION['id'], ['super_admin', 'administrator', 'employee'], 'student registration');

    if (array_key_exists("id", $_GET)) {
      $filing_location = view_filing_location($_GET['id']);
    }

?>

<h3 class="d-none" id="page-title">
  Filing Location Information
</h3>

<table class="table table-head-fixed text-nowrap">
  <tbody>
    <tr>
      <th class="table-light">Name</th>
      <td colspan="3"><?= htmlspecialchars($filing_location['name'], ENT_QUOTES) ?></td>
    </tr>
    <tr>
      <th class="table-light">Last Updated</th>
      <td> <?= empty($filing_location['last_updated']) ? 'N/A' : date('M d, Y g:i A', strtotime($filing_location['last_updated'])) ?></td>
      <th class="table-light">Date Created</th>
      <td><?= date('M d, Y', strtotime($filing_location['date_created'])) ?></td>
    </tr>
  </tbody>
</table>




