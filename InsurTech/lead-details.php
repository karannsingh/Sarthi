<?php
require('include/top.php');

// Check for authentication
if (!isset($_SESSION['USEROID'])) {
    header('Location: login.php');
    exit();
}

$current_user_id = $_SESSION['USEROID'] ?? 0;
$user_role = $_SESSION['ROLE'] ?? '';

if (!isset($_GET['LeadID']) || empty($_GET['LeadID'])) {
    die("Invalid Lead ID");
}

$lead_id = $_GET['LeadID'];

// Fetch Lead Details
$leadQuery = "SELECT l.*, s.StatusName, u.UserName as CreatedBy, l.EmployeeOID 
              FROM leads l
              LEFT JOIN master_remark_status s ON l.RemarkStatusOID = s.RemarkStatusOID
              LEFT JOIN users u ON l.EmployeeOID = u.UserOID
              WHERE l.LeadOID = ?";
$stmt = $conn->prepare($leadQuery);
$stmt->bind_param("i", $lead_id);
$stmt->execute();
$leadResult = $stmt->get_result();
$lead = $leadResult->fetch_assoc();

if (!$lead) {
    die("Lead not found");
}

// Check if user is lead creator or admin/manager/team leader
$isLeadCreator = ($current_user_id == $lead['EmployeeOID']);
$isAdmin = (in_array($user_role, ['1', '2', '3']));

// Fetch Lead Conversations
$convoQuery = "SELECT c.*, s.StatusName, U.UserName 
FROM leads L
LEFT JOIN lead_conversation c ON L.LeadOID = c.LeadOID
LEFT JOIN master_remark_status s ON c.StatusOID = s.RemarkStatusOID
LEFT JOIN users U ON c.EmployeeOID = U.UserOID
WHERE L.LeadOID = ?
ORDER BY c.ConversationDate DESC";
$stmt = $conn->prepare($convoQuery);
$stmt->bind_param("i", $lead_id);
$stmt->execute();
$convoResult = $stmt->get_result();

// Fetch AI data if it exists
$aiDataExists = false;
$aiData = null;

$aiQuery = "SELECT als.score, als.priority, afr.reminder_date, asa.sentiment, asa.score as sentiment_score,
           als.created_at, u.UserName
           FROM ai_lead_scoring als
           LEFT JOIN ai_followup_reminders afr ON als.lead_id = afr.lead_id AND als.created_at = afr.created_at
           LEFT JOIN ai_sentiment_analysis asa ON als.lead_id = asa.lead_id AND als.created_at = asa.created_at
           LEFT JOIN users u ON als.employee_id = u.UserOID
           WHERE als.lead_id = ? 
           ORDER BY als.created_at DESC
           LIMIT 1";
$stmt = $conn->prepare($aiQuery);
$stmt->bind_param("i", $lead_id);
$stmt->execute();
$aiResult = $stmt->get_result();
if ($aiResult->num_rows > 0) {
    $aiDataExists = true;
    $aiData = $aiResult->fetch_assoc();
}

require('include/side-navbar.php');
require('include/right-side-navbar.php');
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
          
          <?php if ($aiDataExists): ?>
          <!-- AI Summary Card -->
          <div class="row mb-4">
            <div class="col-lg-12">
              <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                  <h5 class="mb-0">
                    <i class="fa fa-robot me-2"></i>AI Analysis Summary
                    <span class="badge bg-light text-primary float-end">
                      Last updated: <?= date('d M Y H:i', strtotime($aiData['created_at'])) ?>
                    </span>
                  </h5>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-3 text-center mb-3">
                      <h6 class="text-muted">Lead Score</h6>
                      <div class="display-6"><?= $aiData['score'] ?>/10</div>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                      <h6 class="text-muted">Priority</h6>
                      <div>
                        <?php
                          $priorityColor = 'bg-success';
                          if (strtolower($aiData['priority']) == 'high') $priorityColor = 'bg-danger';
                          elseif (strtolower($aiData['priority']) == 'medium') $priorityColor = 'bg-warning';
                        ?>
                        <span class="badge <?= $priorityColor ?> px-3 py-2"><?= $aiData['priority'] ?></span>
                      </div>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                      <h6 class="text-muted">Suggested Follow-up</h6>
                      <div class="fw-bold"><?= date('d M Y', strtotime($aiData['reminder_date'])) ?></div>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                      <h6 class="text-muted">Customer Sentiment</h6>
                      <?php
                        $sentimentColor = 'bg-secondary';
                        if (strtolower($aiData['sentiment']) == 'positive') $sentimentColor = 'bg-success';
                        elseif (strtolower($aiData['sentiment']) == 'negative') $sentimentColor = 'bg-danger';
                      ?>
                      <span class="badge <?= $sentimentColor ?> px-3 py-2">
                        <?= $aiData['sentiment'] ?> (<?= number_format($aiData['sentiment_score'], 1) ?>)
                      </span>
                    </div>
                  </div>
                  <div class="mt-3 d-flex justify-content-end">
                    <a href="AILeadScoring-test.php?id=<?= $lead_id ?>" class="btn btn-sm btn-outline-primary">
                      <i class="fa fa-search me-1"></i>View Full AI Analysis
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php elseif ($isLeadCreator): ?>
          <!-- Show prompt to generate AI analysis -->
          <div class="row mb-4">
            <div class="col-lg-12">
              <div class="card border-info">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <i class="fa fa-lightbulb text-warning fa-2x me-3"></i>
                    <div>
                      <h5 class="mb-1">Generate AI Lead Analysis</h5>
                      <p class="mb-0">Use AI to analyze this lead, generate call scripts, and get smart follow-up suggestions.</p>
                    </div>
                    <div class="ms-auto">
                      <a href="AILeadScoring-test.php?id=<?= $lead_id ?>" class="btn btn-primary">
                        <i class="fa fa-robot me-1"></i>Generate AI Analysis
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php endif; ?>
          
          <div class="row">
            <div class="col-lg-12">
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
                            <tr><th>Created By</th><td>
                              <?= $lead['CreatedBy'] ?> 
                              <?php if ($isLeadCreator): ?>
                                <span class="badge bg-info">You</span>
                              <?php endif; ?>
                            </td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr><th>Previous IDV</th><td>₹<?= number_format($lead['PreviousIDV'], 2) ?></td></tr>
                            <tr><th>Previous Premium</th><td>₹<?= number_format($lead['PreviousPremium'], 2) ?></td></tr>
                            <tr><th>Current Status</th><td><span class="badge bg-primary"><?= $lead['StatusName'] ?></span></td></tr>
                            <tr><th>Follow-Up</th><td><?= $lead['FollowUpDate'] ?> <?= $lead['FollowUpTime'] ?></td></tr>
                            <?php if ($aiDataExists): ?>
                            <tr>
                                <th>AI Recommended Follow-Up</th>
                                <td>
                                    <?= date('Y-m-d', strtotime($aiData['reminder_date'])) ?>
                                    <?php if(strtotime($aiData['reminder_date']) <= strtotime(date('Y-m-d'))): ?>
                                        <span class="badge bg-danger">Due Today</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <!-- Add New Conversation Button for lead creators -->
                <?php if ($isLeadCreator): ?>
                <div class="row mt-3 mb-4">
                    <div class="col-12">
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addConversationModal">
                            <i class="fa fa-plus-circle me-1"></i> Add New Conversation
                        </button>
                        
                        <?php if ($aiDataExists): ?>
                        <a href="AILeadScoring-test.php?id=<?= $lead_id ?>&refresh=1" class="btn btn-primary ms-2">
                            <i class="fa fa-sync me-1"></i> Refresh AI Analysis
                        </a>
                        <?php else: ?>
                        <a href="AILeadScoring-test.php?id=<?= $lead_id ?>" class="btn btn-primary ms-2">
                            <i class="fa fa-robot me-1"></i> Generate AI Analysis
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <h4>Lead Conversations</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Status</th>
                            <th>Feedback</th>
                            <th>Employee</th>
                            <th>Date</th>
                            <?php if ($aiDataExists): ?>
                            <th>Sentiment</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($convoResult) > 0) {
                            $count = 1;
                            while ($convo = mysqli_fetch_assoc($convoResult)) { 
                                // Get sentiment for this feedback if AI exists
                                $feedbackSentiment = null;
                                if ($aiDataExists) {
                                    $sentimentQuery = "SELECT sentiment, score 
                                                    FROM ai_sentiment_analysis 
                                                    WHERE lead_id = ? AND feedback_text LIKE ?
                                                    LIMIT 1";
                                    $stmt = $conn->prepare($sentimentQuery);
                                    $feedbackPattern = "%" . substr($convo['Feedback'], 0, 50) . "%";
                                    $stmt->bind_param("is", $lead_id, $feedbackPattern);
                                    $stmt->execute();
                                    $sentimentResult = $stmt->get_result();
                                    if ($sentimentResult->num_rows > 0) {
                                        $feedbackSentiment = $sentimentResult->fetch_assoc();
                                    }
                                }
                            ?>
                                <tr>
                                    <td><?= $count ?></td>
                                    <td><span class="badge bg-info"><?= $convo['StatusName'] ?></span></td>
                                    <td><?= $convo['Feedback'] ?></td>
                                    <td><?= $convo['UserName'] ?></td>
                                    <td><?= $convo['ConversationDate'] ?></td>
                                    <?php if ($aiDataExists): ?>
                                    <td>
                                        <?php if ($feedbackSentiment): 
                                            $sentColor = "bg-secondary";
                                            if ($feedbackSentiment['sentiment'] == 'Positive') $sentColor = "bg-success";
                                            elseif ($feedbackSentiment['sentiment'] == 'Negative') $sentColor = "bg-danger";
                                        ?>
                                            <span class="badge <?= $sentColor ?>">
                                                <?= $feedbackSentiment['sentiment'] ?> (<?= number_format($feedbackSentiment['score'], 1) ?>)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not analyzed</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php $count++;
                            }
                        } else { ?>
                            <tr><td colspan="<?= $aiDataExists ? 6 : 5 ?>" class="text-center">No conversations yet.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
    <!-- Add New Conversation Modal -->
    <div class="modal fade" id="addConversationModal" tabindex="-1" role="dialog" aria-labelledby="conversationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="conversationModalLabel">Add New Conversation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="add_conversation.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="lead_id" value="<?= $lead_id ?>">
                        
                        <div class="form-group mb-3">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <?php
                                $statusQuery = "SELECT * FROM master_remark_status ORDER BY StatusName";
                                $statusResult = mysqli_query($conn, $statusQuery);
                                while($status = mysqli_fetch_assoc($statusResult)) {
                                    echo "<option value='".$status['RemarkStatusOID']."'>".$status['StatusName']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="feedback">Feedback</label>
                            <textarea class="form-control" id="feedback" name="feedback" rows="4" required placeholder="Enter conversation details, customer feedback, or next steps"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Conversation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
      
<?php
require('include/footer.php');
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add Bootstrap modal initialization for older Bootstrap versions
    // This is needed if you're using Bootstrap 4 instead of 5
    if (typeof bootstrap === 'undefined' && typeof $ !== 'undefined' && typeof $.fn.modal === 'function') {
        $('#addConversationModal').modal({
            keyboard: false,
            backdrop: 'static',
            show: false
        });
    }
    
    // Highlight today's follow-up date
    const today = new Date().toISOString().split('T')[0];
    const followUpDate = "<?= $lead['FollowUpDate'] ?>";
    
    if (followUpDate === today) {
        document.querySelectorAll('td:contains("'+followUpDate+'")').forEach(function(el) {
            el.style.color = '#dc3545';
            el.style.fontWeight = 'bold';
        });
    }
});
</script>