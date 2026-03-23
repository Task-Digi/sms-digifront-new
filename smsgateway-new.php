<?php
$user     = 'multico';
$password = 'tr5331';
$simulate = 0;
$dlrurl   = 'smsgateway.php';

// DB connection
$conn = new mysqli('localhost', 'portu_sms', 'Duq&_P,$nm?=', 'portu_sms');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate API key
$key = $conn->real_escape_string($_GET['key'] ?? '');
$resultclid = $conn->query("SELECT id FROM client WHERE apikey = '$key'");
if ($resultclid->num_rows === 0) {
    echo "Client not found";
    exit;
}
$client_id = $resultclid->fetch_assoc()['id'];

// Get client IP
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))       return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    return $_SERVER['REMOTE_ADDR'];
}

$originator = $_GET['avs']  ?? '';
$message    = $_GET['msg']  ?? '';
$dest       = $_GET['dest'] ?? '';
$client_ip  = getClientIP();
$smsCount   = ceil(strlen($message) / 140);

$mobile_numbers = array_filter(array_map('trim', explode(',', $dest)));

if (empty($mobile_numbers)) {
    echo "No recipients provided";
    exit;
}

// Insert all SMS records first and collect IDs
$insertedIds = [];
foreach ($mobile_numbers as $mobile_nr) {
    $m  = $conn->real_escape_string($mobile_nr);
    $mg = $conn->real_escape_string($message);
    $conn->query("INSERT INTO sms (client_id, mobile_nr, msg, sms_count, ip_address)
                  VALUES ('$client_id', '$m', '$mg', '$smsCount', '$client_ip')");
    $insertedIds[$mobile_nr] = $conn->insert_id;
}

// Build single messages array for eurobate
$messages = [];
foreach ($mobile_numbers as $mobile_nr) {
    $messages[] = [
        'originator' => $originator,
        'msisdn'     => (int) $mobile_nr,
        'message'    => $message,
        'dlrurl'     => $dlrurl,
    ];
}

// Single API call to eurobate
$payload = json_encode([
    'user'     => $user,
    'password' => $password,
    'simulate' => $simulate,
    'messages' => $messages,
]);

$curl = curl_init('https://api.eurobate.com/json_api.php');
curl_setopt($curl, CURLOPT_POST,           1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($curl, CURLOPT_POSTFIELDS,     $payload);
$buffer = curl_exec($curl);
curl_close($curl);

if (!$buffer) {
    $buffer = json_encode(['STATUS' => 'ERROR', 'REASON' => 'Empty buffer']);
}

$response = json_decode($buffer, true);

// Process each result and update DB records
if (is_array($response) && isset($response['messages'])) {
    foreach ($response['messages'] as $i => $msg) {
        $mobile_nr     = array_keys($insertedIds)[$i] ?? null;
        $last_id       = $insertedIds[$mobile_nr] ?? null;
        if (!$last_id) continue;

        $transactionid = $msg['transactionid'] ?? 1;
        $error         = $conn->real_escape_string($msg['error'] ?? '');
        $info          = $conn->real_escape_string($msg['info']  ?? '');

        $conn->query("INSERT INTO sms_errors (sms_id, error_code, error_info)
                      VALUES ('$last_id', '$error', '$info')");
        $conn->query("UPDATE sms SET transaction_id = '$transactionid' WHERE id = '$last_id'");
    }
} elseif (isset($response['STATUS']) && $response['STATUS'] === 'ERROR') {
    $error = $conn->real_escape_string($response['error']  ?? '');
    $info  = $conn->real_escape_string($response['REASON'] ?? '');
    foreach ($insertedIds as $last_id) {
        $conn->query("INSERT INTO sms_errors (sms_id, error_code, error_info)
                      VALUES ('$last_id', '$error', '$info')");
    }
}

echo $buffer;
?>
