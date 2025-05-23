<?php
require 'include/top.php';
require 'ai-test/groq_api.php';

// Enhanced error handling
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error [$errno] $errstr - $errfile:$errline");
    if (in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo "<div style='color:red; padding:10px; background:#ffeeee; border:1px solid #ffaaaa;'>
              <strong>System Error:</strong> The system encountered an error processing your request. 
              Please try again or contact support.</div>";
        exit(1);
    }
});

// Get lead ID and validate
$id = $_GET['id'] ?? null;
if (!$id || !ctype_digit($id)) {
    die("<div style='color:red; padding:10px; background:#ffeeee; border:1px solid #ffaaaa;'>
         <strong>Error:</strong> Invalid Lead ID.</div>");
}

// Get current user information
$current_user_id = $_SESSION['UserOID'] ?? 0;
$user_role = $_SESSION['UserRole'] ?? '';

try {
    // Step 1: Fetch lead data
    $leadData = fetchLeadAndFeedback($conn, $id);
    if (!$leadData) {
        die("<div style='color:red; padding:10px; background:#ffeeee; border:1px solid #ffaaaa;'>
             <strong>Error:</strong> Lead not found.</div>");
    }
    
    // Get lead creator information
    $leadQuery = "SELECT EmployeeOID FROM leads WHERE LeadOID = ?";
    $stmt = $conn->prepare($leadQuery);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leadInfo = $result->fetch_assoc();
    $lead_creator_id = $leadInfo['CreatedByOID'] ?? 0;
    
    // Check if stored AI analysis exists
    $aiDataExists = checkAIDataExists($conn, $id);
    
    // Determine if new AI analysis is needed
    $needNewAIAnalysis = false;
    $isLeadCreator = ($current_user_id == $lead_creator_id);
    $isAdmin = (in_array($user_role, ['admin', 'manager', 'team_leader']));
    
    if ($isLeadCreator && !$aiDataExists) {
        // Lead creator viewing for first time - generate new AI analysis
        $needNewAIAnalysis = true;
    } elseif ($isLeadCreator && hasNewConversations($conn, $id)) {
        // Lead creator viewing with new conversations since last AI analysis
        $needNewAIAnalysis = true;
    }
    
    // Get or generate AI analysis
    $startTime = microtime(true);
    
    if ($needNewAIAnalysis) {
        // Generate new AI analysis
        $aiResponse = getLeadScoreAndScript($leadData);
        $processingTime = round(microtime(true) - $startTime, 2);
        
        // Log the raw API response for debugging
        error_log("Groq API Response for Lead $id: " . substr($aiResponse, 0, 1000));
        
        // Parse AI response
        list($score, $priority, $nextFollowUp, $callScript) = parseAIResponse($aiResponse);
        
        // Check if parsing was successful
        $parsingSuccess = ($score !== null && $priority !== null && $nextFollowUp !== null);
        
        // Sentiment analysis on latest feedback
        list($sentiment, $sentimentScore) = analyzeSentiment($conn, $id);
        
        // Update database if parsing was successful
        $updateSuccess = false;
        if ($parsingSuccess) {
            // Update lead with AI data
            $updateSuccess = updateLeadWithAI($conn, $id, $score, $priority, $nextFollowUp);
            
            // Store AI data in new tables
            storeAILeadScoring($conn, $id, $current_user_id, $score, $priority, "AI-generated lead scoring");
            storeAIFollowupReminders($conn, $id, $current_user_id, $nextFollowUp, "AI-suggested follow-up date");
            storeAISentimentAnalysis($conn, $id, $current_user_id, getLatestFeedback($conn, $id), $sentiment, $sentimentScore);
        }
    } else {
        // Retrieve stored AI analysis
        $processingTime = 0;
        $storedAIData = getStoredAIData($conn, $id);
        $score = $storedAIData['score'];
        $priority = $storedAIData['priority'];
        $nextFollowUp = $storedAIData['reminder_date'];
        $callScript = $storedAIData['call_script'];
        $sentiment = $storedAIData['sentiment'];
        $sentimentScore = $storedAIData['sentiment_score'];
        $updateSuccess = true;
        $parsingSuccess = true;
    }
    
} catch (Exception $e) {
    error_log("Exception in AILeadScoring.php: " . $e->getMessage());
    $error = "System encountered an error: " . htmlspecialchars($e->getMessage());
}

// Helper function to check if AI data exists for this lead
function checkAIDataExists($conn, $leadId) {
    $query = "SELECT COUNT(*) as count FROM ai_lead_scoring WHERE lead_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $leadId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    return ($data['count'] > 0);
}

// Helper function to check if there are new conversations since last AI analysis
function hasNewConversations($conn, $leadId) {
    $query = "SELECT MAX(lc.ConversationDate) as last_convo, MAX(als.created_at) as last_analysis
              FROM lead_conversation lc
              LEFT JOIN ai_lead_scoring als ON als.lead_id = lc.LeadOID
              WHERE lc.LeadOID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $leadId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    if (!$data['last_analysis']) return true;
    
    return strtotime($data['last_convo']) > strtotime($data['last_analysis']);
}

// Helper function to get stored AI data
function getStoredAIData($conn, $leadId) {
    $query = "SELECT 
                als.score, 
                als.priority, 
                afr.reminder_date, 
                afr.remarks as call_script,
                asa.sentiment,
                asa.score as sentiment_score
              FROM ai_lead_scoring als
              LEFT JOIN ai_followup_reminders afr ON als.lead_id = afr.lead_id
              LEFT JOIN ai_sentiment_analysis asa ON als.lead_id = asa.lead_id
              WHERE als.lead_id = ?
              ORDER BY als.created_at DESC
              LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $leadId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Helper function to store AI lead scoring
function storeAILeadScoring($conn, $leadId, $employeeId, $score, $priority, $comments) {
    $stmt = $conn->prepare("INSERT INTO ai_lead_scoring (lead_id, employee_id, score, priority, comments, created_at)
                            VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iidss", $leadId, $employeeId, $score, $priority, $comments);
    return $stmt->execute();
}

// Helper function to store AI followup reminders
function storeAIFollowupReminders($conn, $leadId, $employeeId, $reminderDate, $remarks) {
    $stmt = $conn->prepare("INSERT INTO ai_followup_reminders (lead_id, employee_id, reminder_date, remarks, created_at)
                            VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiss", $leadId, $employeeId, $reminderDate, $remarks);
    return $stmt->execute();
}

// Helper function to store AI sentiment analysis
function storeAISentimentAnalysis($conn, $leadId, $employeeId, $feedbackText, $sentiment, $score) {
    $stmt = $conn->prepare("INSERT INTO ai_sentiment_analysis (lead_id, employee_id, feedback_text, sentiment, score, created_at)
                            VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iissd", $leadId, $employeeId, $feedbackText, $sentiment, $score);
    return $stmt->execute();
}

// Helper function to get the latest feedback
function getLatestFeedback($conn, $leadId) {
    $query = "SELECT Feedback FROM lead_conversation 
              WHERE LeadOID = ? 
              ORDER BY ConversationDate DESC 
              LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $leadId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    return $data['Feedback'] ?? '';
}

// Simple sentiment analysis function
function analyzeSentiment($conn, $leadId) {
    // Get latest feedback
    $feedback = getLatestFeedback($conn, $leadId);
    
    // Simplified sentiment analysis - in production would use NLP API
    $positive_words = ['interested', 'excited', 'yes', 'good', 'great', 'call', 'purchase', 'buy', 'renew'];
    $negative_words = ['not', 'expensive', 'high', 'cancel', 'no', 'later', 'busy', 'don\'t'];
    
    $positive_count = 0;
    $negative_count = 0;
    
    foreach ($positive_words as $word) {
        if (stripos($feedback, $word) !== false) {
            $positive_count++;
        }
    }
    
    foreach ($negative_words as $word) {
        if (stripos($feedback, $word) !== false) {
            $negative_count++;
        }
    }
    
    $total = $positive_count + $negative_count;
    if ($total == 0) {
        return ['Neutral', 5.0];
    }
    
    $score = 5 + (($positive_count - $negative_count) / $total) * 5;
    $score = max(1, min(10, $score));
    
    if ($score > 6.5) {
        $sentiment = 'Positive';
    } elseif ($score < 4.5) {
        $sentiment = 'Negative';
    } else {
        $sentiment = 'Neutral';
    }
    
    return [$sentiment, $score];
}
require('include/side-navbar.php');
require('include/right-side-navbar.php');
?>
<style>
    body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; color: #333; }
    .container { max-width: 1000px; margin: 0 auto; }
    .panel { background: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
    h2 { color: #2a7ae2; margin-top: 0; }
    h3 { color: #2a7ae2; margin-top: 20px; }
    .script { background: #f5f5f5; padding: 15px; border-radius: 5px; border-left: 4px solid #2a7ae2; }
    .lead-info { display: flex; flex-wrap: wrap; }
    .lead-info div { flex: 1; min-width: 200px; margin: 10px; }
    .badge { display: inline-block; padding: 5px 10px; border-radius: 20px; font-size: 14px; font-weight: bold; color: white; }
    .badge-high { background-color: #dc3545; }
    .badge-medium { background-color: #ffc107; color: #333; }
    .badge-low { background-color: #28a745; }
    .badge-positive { background-color: #28a745; }
    .badge-neutral { background-color: #6c757d; }
    .badge-negative { background-color: #dc3545; }
    .score { font-size: 24px; font-weight: bold; }
    .date { font-weight: bold; color: #555; }
    .history { max-height: 300px; overflow-y: auto; background: #f9f9f9; padding: 10px; border-radius: 5px; }
    .error { color: red; padding: 10px; background: #ffeeee; border: 1px solid #ffaaaa; border-radius: 5px; }
    .success { color: green; padding: 10px; background: #eeffee; border: 1px solid #aaffaa; border-radius: 5px; }
    .debug-info { font-size: 12px; color: #777; margin-top: 10px; }
    .ai-badge { background-color: #6610f2; color: white; padding: 3px 8px; border-radius: 10px; font-size: 12px; margin-left: 5px; }
</style>
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
        <div class="container">
            <?php if (isset($error)): ?>
                <div class="error"><?= $error ?></div>
            <?php else: ?>
                <?php if ($needNewAIAnalysis): ?>
                    <?php if ($updateSuccess): ?>
                        <div class="success">Lead information successfully updated with fresh AI analysis.</div>
                    <?php elseif ($parsingSuccess): ?>
                        <div class="error">Warning: AI analysis generated but could not be saved to database. Please try again.</div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="panel">
                        <div class="debug-info">Displaying previously generated AI analysis</div>
                    </div>
                <?php endif; ?>
                
                <div class="panel">
                    <h2>AI-Generated Call Script <span class="ai-badge">AI</span></h2>
                    <div class="script"><?= nl2br(htmlspecialchars($callScript)) ?></div>
                    <?php if ($needNewAIAnalysis): ?>
                        <div class="debug-info">Processing time: <?= $processingTime ?> seconds</div>
                    <?php endif; ?>
                </div>
                
                <div class="panel">
                    <h2>Lead Analysis <span class="ai-badge">AI</span></h2>
                    <div class="lead-info">
                        <div>
                            <h3>Lead Score</h3>
                            <span class="score"><?= $score ?>/10</span>
                        </div>
                        <div>
                            <h3>Priority</h3>
                            <span class="badge badge-<?= strtolower($priority) ?>"><?= ucfirst($priority) ?></span>
                        </div>
                        <div>
                            <h3>Next Follow-Up</h3>
                            <span class="date"><?= date('d M Y', strtotime($nextFollowUp)) ?></span>
                        </div>
                        <div>
                            <h3>Sentiment Analysis</h3>
                            <span class="badge badge-<?= strtolower($sentiment) ?>"><?= $sentiment ?> (<?= number_format($sentimentScore, 1) ?>)</span>
                        </div>
                    </div>
                </div>
                
                <?php if ($isLeadCreator || $isAdmin): ?>
                <div class="panel">
                    <h2>AI Analysis History</h2>
                    <?php 
                    // Get AI history if available
                    $aiHistory = getAIHistory($conn, $id);
                    if (count($aiHistory) > 0): 
                    ?>
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f3f3f3;">
                                <th style="padding: 8px; text-align: left; border-bottom: 1px solid #ddd;">Date</th>
                                <th style="padding: 8px; text-align: left; border-bottom: 1px solid #ddd;">Score</th>
                                <th style="padding: 8px; text-align: left; border-bottom: 1px solid #ddd;">Priority</th>
                                <th style="padding: 8px; text-align: left; border-bottom: 1px solid #ddd;">Sentiment</th>
                                <th style="padding: 8px; text-align: left; border-bottom: 1px solid #ddd;">Employee</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($aiHistory as $history): ?>
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd;"><?= date('d M Y', strtotime($history['created_at'])) ?></td>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd;"><?= $history['score'] ?>/10</td>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd;">
                                    <span class="badge badge-<?= strtolower($history['priority']) ?>"><?= $history['priority'] ?></span>
                                </td>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd;">
                                    <span class="badge badge-<?= strtolower($history['sentiment']) ?>"><?= $history['sentiment'] ?></span>
                                </td>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd;"><?= $history['UserName'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p>No AI analysis history available.</p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="panel">
                    <h2>Lead Details</h2>
                    <pre><?= htmlspecialchars($leadData) ?></pre>
                </div>
            <?php endif; ?>
            
            <div class="panel">
                <a href="lead-details.php?LeadID=<?= $id ?>" style="display: inline-block; padding: 10px 15px; background: #2a7ae2; color: white; text-decoration: none; border-radius: 5px;">Back to Lead Details</a>
            </div>
        </div>
    </div>
</div>
<?php
require('include/footer.php');
?>
<script>
    // Simple script to highlight today's date in follow-up
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        const followUpDate = "<?= $nextFollowUp ?>";
        
        if (followUpDate === today) {
            document.querySelector('.date').style.color = '#dc3545';
            document.querySelector('.date').style.fontWeight = 'bold';
        }
    });
</script>
<?php
// Helper function to get AI history
function getAIHistory($conn, $leadId) {
    $query = "SELECT als.created_at, als.score, als.priority, asa.sentiment, u.UserName
              FROM ai_lead_scoring als
              LEFT JOIN ai_sentiment_analysis asa ON als.lead_id = asa.lead_id AND als.created_at = asa.created_at
              LEFT JOIN users u ON als.employee_id = u.UserOID
              WHERE als.lead_id = ?
              ORDER BY als.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $leadId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    
    return $history;
}
?>