<?php
// This file should be included in the dashboard page for managers/team leaders/admins

// Check if user has permission to view this widget
if (!in_array($_SESSION['ROLE'] ?? '', ['1', '2', '3'])) {
    return; // Exit if user doesn't have permission
}

// Get AI analyzed leads with high priority
$highPriorityQuery = "SELECT l.LeadOID, l.LeadID, l.CustomerName, l.VehicleNumber, l.RenewalDate,
                      als.score, als.priority, afr.reminder_date, asa.sentiment,
                      u.UserName as AssignedTo
                      FROM ai_lead_scoring als
                      JOIN leads l ON als.lead_id = l.LeadOID
                      LEFT JOIN ai_followup_reminders afr ON als.lead_id = afr.lead_id AND als.created_at = afr.created_at
                      LEFT JOIN ai_sentiment_analysis asa ON als.lead_id = asa.lead_id AND als.created_at = asa.created_at
                      LEFT JOIN users u ON l.EmployeeOID = u.UserOID
                      WHERE als.priority = 'High'
                      AND als.id IN (
                          SELECT MAX(id) FROM ai_lead_scoring GROUP BY lead_id
                      )
                      ORDER BY als.score DESC
                      LIMIT 5";
$highPriorityResult = mysqli_query($conn, $highPriorityQuery);

// Get leads with negative sentiment
$negativeSentimentQuery = "SELECT l.LeadOID, l.LeadID, l.CustomerName, l.VehicleNumber, l.RenewalDate,
                         asa.sentiment, asa.score as sentiment_score, asa.feedback_text,
                         u.UserName as AssignedTo
                         FROM ai_sentiment_analysis asa
                         JOIN leads l ON asa.lead_id = l.LeadOID
                         LEFT JOIN users u ON l.EmployeeOID = u.UserOID
                         WHERE asa.sentiment = 'Negative'
                         AND asa.id IN (
                             SELECT MAX(id) FROM ai_sentiment_analysis GROUP BY lead_id
                         )
                         ORDER BY asa.score ASC
                         LIMIT 5";
$negativeSentimentResult = mysqli_query($conn, $negativeSentimentQuery);

// Get AI follow-up reminders due today
$todayFollowupsQuery = "SELECT l.LeadOID, l.LeadID, l.CustomerName, l.VehicleNumber,
                       afr.reminder_date, u.UserName as AssignedTo
                       FROM ai_followup_reminders afr
                       JOIN leads l ON afr.lead_id = l.LeadOID
                       LEFT JOIN users u ON l.EmployeeOID = u.UserOID
                       WHERE DATE(afr.reminder_date) = CURDATE()
                       AND afr.id IN (
                           SELECT MAX(id) FROM ai_followup_reminders GROUP BY lead_id
                       )
                       ORDER BY afr.created_at DESC";
$todayFollowupsResult = mysqli_query($conn, $todayFollowupsQuery);
?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <i class="fa fa-robot me-2"></i>AI-Powered Lead Insights
    </div>
    <div class="card-body p-0">
        <ul class="nav nav-tabs" id="aiInsightsTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="high-priority-tab" data-toggle="tab" href="#high-priority" role="tab">
                    High Priority Leads (<?= mysqli_num_rows($highPriorityResult) ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="negative-sentiment-tab" data-toggle="tab" href="#negative-sentiment" role="tab">
                    Negative Sentiment (<?= mysqli_num_rows($negativeSentimentResult) ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="today-followups-tab" data-toggle="tab" href="#today-followups" role="tab">
                    Today's Follow-ups (<?= mysqli_num_rows($todayFollowupsResult) ?>)
                </a>
            </li>
        </ul>
        
        <div class="tab-content p-3" id="aiInsightsTabContent">
            <!-- High Priority Leads Tab -->
            <div class="tab-pane fade show active" id="high-priority" role="tabpanel">
                <?php if (mysqli_num_rows($highPriorityResult) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Lead ID</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Renewal</th>
                                <th>AI Score</th>
                                <th>Assigned To</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($lead = mysqli_fetch_assoc($highPriorityResult)): ?>
                            <tr>
                                <td><?= $lead['LeadID'] ?></td>
                                <td><?= $lead['CustomerName'] ?></td>
                                <td><?= $lead['VehicleNumber'] ?></td>
                                <td><?= date('d M Y', strtotime($lead['RenewalDate'])) ?></td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $lead['score']*10 ?>%"
                                             aria-valuenow="<?= $lead['score'] ?>" aria-valuemin="0" aria-valuemax="10">
                                            <?= $lead['score'] ?>/10
                                        </div>
                                    </div>
                                </td>
                                <td><?= $lead['AssignedTo'] ?></td>
                                <td>
                                    <a href="lead-details.php?LeadID=<?= $lead['LeadOID'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center p-4">
                    <p class="text-muted">No high priority leads found.</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Negative Sentiment Tab -->
            <div class="tab-pane fade" id="negative-sentiment" role="tabpanel">
                <?php if (mysqli_num_rows($negativeSentimentResult) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Lead ID</th>
                                <th>Customer</th>
                                <th>Sentiment</th>
                                <th>Recent Feedback</th>
                                <th>Assigned To</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($lead = mysqli_fetch_assoc($negativeSentimentResult)): ?>
                            <tr>
                                <td><?= $lead['LeadID'] ?></td>
                                <td><?= $lead['CustomerName'] ?></td>
                                <td>
                                    <span class="badge bg-danger">
                                        <?= $lead['sentiment'] ?> (<?= number_format($lead['sentiment_score'], 1) ?>)
                                    </span>
                                </td>
                                <td><?= substr($lead['feedback_text'], 0, 100) ?>...</td>
                                <td><?= $lead['AssignedTo'] ?></td>
                                <td>
                                    <a href="lead-details.php?LeadID=<?= $lead['LeadOID'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center p-4">
                    <p class="text-muted">No leads with negative sentiment found.</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Today's Follow-ups Tab -->
            <div class="tab-pane fade" id="today-followups" role="tabpanel">
                <?php if (mysqli_num_rows($todayFollowupsResult) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Lead ID</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Follow-up Date</th>
                                <th>Assigned To</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($lead = mysqli_fetch_assoc($todayFollowupsResult)): ?>
                            <tr>
                                <td><?= $lead['LeadID'] ?></td>
                                <td><?= $lead['CustomerName'] ?></td>
                                <td><?= $lead['VehicleNumber'] ?></td>
                                <td>
                                    <span class="badge bg-warning text-dark">Today</span>
                                </td>
                                <td><?= $lead['AssignedTo'] ?></td>
                                <td>
                                    <a href="lead-details.php?LeadID=<?= $lead['LeadOID'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center p-4">
                    <p class="text-muted">No follow-ups scheduled for today.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <a href="ai_analytics.php" class="btn btn-sm btn-primary">
            <i class="fa fa-chart-line me-1"></i>View All AI Analytics
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tabs for Bootstrap 4
    if (typeof bootstrap === 'undefined' && typeof $ !== 'undefined') {
        $('#aiInsightsTabs a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
    }
});
</script>