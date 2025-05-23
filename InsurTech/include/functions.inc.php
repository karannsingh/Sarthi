<?php
/**
 * Functions Library
 * Common utility functions used across the application
 */

/**
 * Log application activity
 * @param string $activity Description of the activity
 * @param int $user_id ID of the user performing the activity (optional)
 * @return bool Success status
 */
function logActivity($activity, $user_id = null) {
    global $conn;
    
    if ($user_id === null && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity, ip_address) VALUES (?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $stmt->bind_param("iss", $user_id, $activity, $ip);
        return $stmt->execute();
    } catch (Exception $e) {
        // Silently fail but log to server error log
        error_log("Activity logging error: " . $e->getMessage());
        return false;
    }
}

/**
 * Log application errors
 * @param string $error Error message
 * @param string $context Context in which the error occurred
 * @return bool Success status
 */
function logError($error, $context = 'application') {
    global $conn;
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    try {
        $stmt = $conn->prepare(
            "INSERT INTO error_logs (user_id, error_message, context, ip_address) VALUES (?, ?, ?, ?)"
        );
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $stmt->bind_param("isss", $user_id, $error, $context, $ip);
        return $stmt->execute();
    } catch (Exception $e) {
        // Fallback to PHP error log
        error_log("Error logging failed: " . $e->getMessage() . ". Original error: " . $error);
        return false;
    }
}

/**
 * Set a flash message to display after redirect
 * @param string $message Message content
 * @param string $type Message type (success, danger, warning, info)
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

/**
 * Get and clear flash message
 * @return array|null Message data or null if no message
 */
function getFlashMessage() {
    if (isset($_SESSION['message'])) {
        $message = [
            'text' => $_SESSION['message'],
            'type' => $_SESSION['message_type'] ?? 'info'
        ];
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        
        return $message;
    }
    
    return null;
}

/**
 * Sanitize input data
 * @param mixed $data Data to sanitize
 * @return mixed Sanitized data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitizeInput($value);
        }
        return $data;
    }
    
    // If string, sanitize with htmlspecialchars
    if (is_string($data)) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    // Return as is for other data types
    return $data;
}

/**
 * Generate a random token
 * @param int $length Length of the token
 * @return string Random token
 */
function generateToken($length = 16) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Format date for display
 * @param string $date Date string in any valid format
 * @param string $format Output format (default: 'M d, Y')
 * @return string Formatted date
 */
function formatDate($date, $format = 'M d, Y') {
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Format datetime for display
 * @param string $datetime Datetime string in any valid format
 * @param string $format Output format (default: 'M d, Y, g:i a')
 * @return string Formatted datetime
 */
function formatDateTime($datetime, $format = 'M d, Y, g:i a') {
    $timestamp = strtotime($datetime);
    return date($format, $timestamp);
}

/**
 * Check if user has specified permission
 * @param string $permission Permission code to check
 * @return bool True if user has permission
 */
function hasPermission($permission) {
    // Simplified example - in production, you'd check against stored user permissions
    if (!isset($_SESSION['user_permissions'])) {
        return false;
    }
    
    return in_array($permission, $_SESSION['user_permissions']);
}

/**
 * Get signature block based on signature type
 * @param int $signature_type Signature type ID
 * @return string HTML signature block
 */
function getEmailSignature($signature_type) {
    $signatures = [
        1 => "<br><br>Best Regards,<br>Sarthi Enterprises<br><a href='https://sarthii.co.in'>Visit our website</a>",
        2 => "<br><br>Thanks & Regards,<br>Team Support<br>Contact: 1234567890",
        3 => "<br><br>Warm Regards,<br>Sales Team<br>Sarthi Enterprises"
    ];
    
    return $signatures[$signature_type] ?? $signatures[1];
}

function getCategory($conn, $post_id){
	$sql="SELECT * from post_category WHERE id=$post_id AND status=1";
	$run=mysqli_query($conn,$sql);
	$result = mysqli_fetch_assoc($run);
	if(!empty($result)){
		return $result['category_name'];
	}else{
		return null;
	}	
}

function getUserDetails($conn, $user_id){
	$sql="SELECT * from users WHERE UserOID=$user_id";
	$run=mysqli_query($conn,$sql);
	$result = mysqli_fetch_assoc($run);
	if(!empty($result)){
		return $result['UserName'];
	}else{
		return null;
	}
}

function getUserID($conn, $post_id){
	$sql="SELECT * from posts WHERE id=$post_id";
	$run=mysqli_query($conn,$sql);
	$result = mysqli_fetch_assoc($run);
	if(!empty($result)){
		return $result['user_id'];
	}else{
		return null;
	}
}

function getAllCategory($conn){
	$sql="SELECT * from users WHERE status=1";
	$run=mysqli_query($conn,$sql);
	$data=array();
	while($row=mysqli_fetch_assoc($run)){
		$data[]=$row;
	}
	return $data;
}

function getImages($conn, $post_id){
	$sql="SELECT * from post_images WHERE post_id=$post_id";
	$run=mysqli_query($conn, $sql);
	$data=array();
	while($row=mysqli_fetch_assoc($run)){
		$data[]=$row;
	}
	return $data;
}

function get_menu($conn){
	$sql="SELECT * from menu_category WHERE status=1";
	$run=mysqli_query($conn,$sql);
	$data=array();
	while($row=mysqli_fetch_assoc($run)){
		$data[]=$row;
	}
	return $data;
}

function getComments($conn, $post_id){
	$sql="SELECT * from post_comments WHERE post_id=$post_id and status=1 order by id desc";
	$run=mysqli_query($conn, $sql);
	$data=array();
	while($row=mysqli_fetch_assoc($run)){
		$data[]=$row;
	}
	return $data;
}

function getSubComments($conn, $post_id, $comment_id){
	$sql="SELECT * from post_sub_comments WHERE post_id=$post_id and $comment_id=$comment_id and status=1 order by id desc";
	$run=mysqli_query($conn, $sql);
	$data=array();
	while($row=mysqli_fetch_assoc($run)){
		$data[]=$row;
	}
	return $data;
}

function getPostTitle($conn, $post_id){
	$sql="SELECT * from posts WHERE id=$post_id";
	$run=mysqli_query($conn, $sql);
	$result = mysqli_fetch_assoc($run);
	if(!empty($result)){
		return $result['title'];
	}else{
		return null;
	}
}

function getAdminInfo($conn, $email){
	$sql="SELECT * from admin_users WHERE email='$email'";
	$run=mysqli_query($conn, $sql);
	$data=mysqli_fetch_assoc($run);
	return $data;
}

function pr($arr){
	echo '<pre>';
	print_r($arr);
}

function prx($arr){
	echo '<pre>';
	print_r($arr);
	die();
}

function get_safe_value($conn, $str){
	if($str!=''){
		$str = trim($str);
		return mysqli_real_escape_string($conn, $str);
	}
}

function get_product($conn,$limit='',$cat_id='',$product_id=''){
	$sql="select product.*,categories.categories from product,categories where product.status=1 ";
	if($cat_id!=''){
		$sql.=" and product.categories_id=$cat_id ";
	}
	if($product_id!=''){
		$sql.=" and product.id=$product_id ";
	}
	$sql.=" and product.categories_id=categories.id ";
	$sql.=" order by product.id desc";
	if($limit!=''){
		$sql.=" limit $limit";
	}
	
	$res=mysqli_query($con,$sql);
	$data=array();
	while($row=mysqli_fetch_assoc($res)){
		$data[]=$row;
	}
	return $data;
}

?>