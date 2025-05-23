<?php
/*AILeadScoring.php*/
require 'include/config.php';
require 'ai-test/groq_api.php'; // Fixed filename (was incorrectly referenced as qroq_api.php)

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

try {
    // Step 1: Fetch lead data
    $leadData = fetchLeadAndFeedback($conn, $id);
    if (!$leadData) {
        die("<div style='color:red; padding:10px; background:#ffeeee; border:1px solid #ffaaaa;'>
             <strong>Error:</strong> Lead not found.</div>");
    }
    
    // Step 2: Get AI analysis
    $startTime = microtime(true);
    $aiResponse = getLeadScoreAndScript($leadData);
    $processingTime = round(microtime(true) - $startTime, 2);
    
    // Log the raw API response for debugging
    error_log("Groq API Response for Lead $id: " . substr($aiResponse, 0, 1000));
    
    // Step 3: Parse AI response
    list($score, $priority, $nextFollowUp, $callScript) = parseAIResponse($aiResponse);
    
    // Check if parsing was successful
    $parsingSuccess = ($score !== null && $priority !== null && $nextFollowUp !== null);
    
    // Step 4: Update database if parsing was successful
    $updateSuccess = false;
    if ($parsingSuccess) {
        $updateSuccess = updateLeadWithAI($conn, $id, $score, $priority, $nextFollowUp);
    }
    
} catch (Exception $e) {
    error_log("Exception in AILeadScoring.php: " . $e->getMessage());
    $error = "System encountered an error: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lead AI Analysis</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        .score { font-size: 24px; font-weight: bold; }
        .date { font-weight: bold; color: #555; }
        .history { max-height: 300px; overflow-y: auto; background: #f9f9f9; padding: 10px; border-radius: 5px; }
        .error { color: red; padding: 10px; background: #ffeeee; border: 1px solid #ffaaaa; border-radius: 5px; }
        .success { color: green; padding: 10px; background: #eeffee; border: 1px solid #aaffaa; border-radius: 5px; }
        .debug-info { font-size: 12px; color: #777; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php else: ?>
            <?php if ($updateSuccess): ?>
                <div class="success">Lead information successfully updated in the system.</div>
            <?php elseif ($parsingSuccess): ?>
                <div class="error">Warning: Lead information could not be updated in the database. Please try again.</div>
            <?php endif; ?>
            
            <div class="panel">
                <h2>AI-Generated Call Script</h2>
                <div class="script"><?= nl2br(htmlspecialchars($callScript)) ?></div>
                <div class="debug-info">Processing time: <?= $processingTime ?> seconds</div>
            </div>
            
            <div class="panel">
                <h2>Lead Analysis</h2>
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
                </div>
            </div>
            
            <div class="panel">
                <h2>Lead Details</h2>
                <pre><?= htmlspecialchars($leadData) ?></pre>
            </div>
        <?php endif; ?>
        
        <div class="panel">
            <a href="javascript:history.back()" style="display: inline-block; padding: 10px 15px; background: #2a7ae2; color: white; text-decoration: none; border-radius: 5px;">Back to Leads</a>
        </div>
    </div>
    
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
</body>
</html>