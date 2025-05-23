<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');

if (!isset($_GET['LeadID']) || empty($_GET['LeadID'])) {
    die("Invalid Lead ID");
}

$lead_id = $_GET['LeadID'];

// Fetch Lead Details
$leadQuery = "SELECT l.*, s.StatusName FROM leads l
              LEFT JOIN master_remark_status s ON l.RemarkStatusOID = s.RemarkStatusOID
              WHERE l.LeadOID = '$lead_id'";
$leadResult = mysqli_query($conn, $leadQuery);
$lead = mysqli_fetch_assoc($leadResult);

// Fetch Lead Conversations
$convoQuery = "SELECT c.*, s.StatusName, U.UserName 
FROM leads L
LEFT JOIN lead_conversation c ON L.LeadOID = c.LeadOID
LEFT JOIN master_remark_status s ON c.StatusOID = s.RemarkStatusOID
LEFT JOIN users U ON c.EmployeeOID = U.UserOID
WHERE L.LeadOID = '$lead_id'
ORDER BY c.ConversationDate DESC";
$convoResult = mysqli_query($conn, $convoQuery);

?>
    <div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
      <?php
        require('include/header.php');
      ?>
      <div class="body flex-grow-1 px-3">
        <div class="container-lg">
          <div class="fs-2 fw-semibold">Lead Details</div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-4">
              <li class="breadcrumb-item">
                <!-- if breadcrumb is single--><span>Lead</span>
              </li>
              <li class="breadcrumb-item active"><span>Lead Details: <strong><?= $lead['LeadID'] ?></strong></span></li>
            </ol>
          </nav>
          <div class="row">
            <div class="col-lg-12 text-center">
              <div class="card mb-4">
                <div class="card-body p-4">
                  <!-- <div class="card-title fs-4 fw-semibold">Lead Entry Form</div> -->
                  <div class="row">
        <div class="col-md-6">
            <table class="table table-bordered">
                <tr><th>Customer Name</th><td><?= $lead['CustomerName'] ?></td></tr>
                <tr><th>Vehicle Number</th><td><?= $lead['VehicleNumber'] ?></td></tr>
                <tr><th>Reg Year</th><td><?= $lead['RegYear'] ?></td></tr>
                <tr><th>Renewal Date</th><td><?= $lead['RenewalDate'] ?></td></tr>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-bordered">
                <tr><th>Previous IDV</th><td>₹<?= number_format($lead['PreviousIDV'], 2) ?></td></tr>
                <tr><th>Previous Premium</th><td>₹<?= number_format($lead['PreviousPremium'], 2) ?></td></tr>
                <tr><th>Current Status</th><td><span class="badge bg-primary"><?= $lead['StatusName'] ?></span></td></tr>
                <tr><th>Follow-Up</th><td><?= $lead['FollowUpDate'] ?> <?= $lead['FollowUpTime'] ?></td></tr>
            </table>
        </div>
    </div>

    <h4>Lead Conversations</h4>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Status</th>
                <th>Feedback</th>
                <th>Employee</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($convoResult) > 0) {
                $count = 1;
                while ($convo = mysqli_fetch_assoc($convoResult)) { ?>
                    <tr>
                        <td><?= $count ?></td>
                        <td><span class="badge bg-info"><?= $convo['StatusName'] ?></span></td>
                        <td><?= $convo['Feedback'] ?></td>
                        <td><?= $convo['UserName'] ?></td>
                        <td><?= $convo['ConversationDate'] ?></td>
                    </tr>
                    <?php $count++;
                }
            } else { ?>
                <tr><td colspan="5" class="text-center">No conversations yet.</td></tr>
            <?php } ?>
        </tbody>
    </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
    <?php
require('include/footer.php');
?>