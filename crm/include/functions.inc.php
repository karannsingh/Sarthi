<?php

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