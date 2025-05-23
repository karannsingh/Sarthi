<?php
// Your JWT API key (keep this secure)
$apiKey     = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCIgOiAiYzMzZmNkOTktODA2My00YmFjLWE2NjYtOGMwN2QyOTQ2NDkzIiwgInJvbGUiIDogImFwaSIsICJ0eXBlIiA6ICJhcGkiLCAibmFtZSIgOiAiU2FydGhpIEVudGVycHJpc2VzIiwgImV4cCIgOiAyMDYyNjY3NTQ1LCAiaWF0IiA6IDE3NDcxMzQ3NDUsICJzdWIiIDogIjc2ZGQxNzhmLTM4M2EtNDVhOS1hMDUxLTFmMWJhYmI2ZjgxZiIsICJpc3MiIDogInBlcmlza29wZS5hcHAiLCAibWV0YWRhdGEiIDoge319.3CM_RT2itn9MoqBOR-9nEVLo1acSvzT1eFz3EouwHkk';

// Endpoint to send WhatsApp message (update if different)
$apiUrl = 'https://api.periskope.app/api/v1/message/send';

// WhatsApp chat_id format: mobile_number@c.us
$chatId = '919137420955@c.us';
$message = 'Hello World';

// Payload
$payload = [
    'chat_id' => $chatId,
    'message' => $message
];

// Initialize cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

// Execute
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Handle result
if ($error) {
    echo "❌ cURL Error: $error";
} elseif ($httpCode === 200) {
    echo "✅ Custom message sent: $response";
} else {
    echo "⚠️ Failed (HTTP $httpCode): $response";
}
?>