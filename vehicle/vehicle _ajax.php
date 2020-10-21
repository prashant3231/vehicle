<?php
include_once('../../../wp-config.php');

if(isset($_POST['vehicle_type'])){
	$vehicle_type = $_POST['vehicle_type'];
	$args = array(
	'post_type' => 'vehicle',
	'tax_query' => array(
		array(
		'taxonomy' => 'categories',
		'field' => 'term_id',
		'terms' => $vehicle_type
		 )
	  )
	);
	$query = new WP_Query( $args );
	$options = '';
	if(!empty($query->posts)){
		foreach($query->posts as $vehicle){
			
			$options .= '<option value="'.$vehicle->ID.'">'.$vehicle->post_title.'</option>';
		}
	}

	print_r($options);
}else{
	$vehicle_id = $_POST['vehicle_id'];
	$price = get_post_meta($vehicle_id, 'vehicle_price');
	print_r($price[0]);
}