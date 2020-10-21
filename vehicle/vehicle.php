<?php
/**
* Plugin Name: Vehicle
* Description: This is the test plugin.
* Version: 1.0
**/


/*Custom Post type start*/
function cw_post_type_vehicle() {
    $supports = array(
        'title', // post title
        'editor', // post content
        'author', // post author
        'thumbnail', // featured images
        'excerpt', // post excerpt
        'custom-fields', // custom fields
        'comments', // post comments
        'revisions', // post revisions
        'post-formats', // post formats
    );
    
    $labels = array(
        'name' => _x('vehicle', 'plural'),
        'singular_name' => _x('vehicle', 'singular'),
        'menu_name' => _x('Vehicle', 'admin menu'),
        'name_admin_bar' => _x('Vehicle', 'admin bar'),
        'add_new' => _x('Add New', 'add new'),
        'add_new_item' => __('Add New vehicle'),
        'new_item' => __('New vehicle'),
        'edit_item' => __('Edit vehicle'),
        'view_item' => __('View vehicle'),
        'all_items' => __('All vehicle'),
        'search_items' => __('Search vehicle'),
        'not_found' => __('No vehicle found.'),
    );
    
    $args = array(
        'supports' => $supports,
		'labels' => $labels, 
        'public' => true, 
        'publicly_queryable' => true, 
        'show_ui' => true, 
        'query_var' => true, 
        'rewrite' => array( 'slug' => 'vehicle', 'with_front'=> false ), 
        'capability_type' => 'post', 
        'hierarchical' => true,
        'has_archive' => true,  
        'menu_position' => null,
    );
    register_post_type('vehicle', $args);
	
	
	register_taxonomy( 'categories', array('vehicle'), array(
        'hierarchical' => true, 
        'label' => 'Categories', 
        'singular_label' => 'Category', 
        'rewrite' => array( 'slug' => 'categories', 'with_front'=> false )
        )
    );

	register_taxonomy_for_object_type( 'categories', 'vehicle' );
	
	add_action('admin_menu', 'my_admin_menu'); 
	function my_admin_menu() { 
		add_submenu_page('edit.php?post_type=vehicle', //parent slug
			'Settings', //page title
			'Settings', //menu title
			'manage_options', //capability
			'setting', //menu slug
			'setting'); //function 
			
		add_submenu_page('edit.php?post_type=vehicle', //parent slug
			'Bookings', //page title
			'Bookings', //menu title
			'manage_options', //capability
			'bookings', //menu slug
			'bookings'); //function
		
		add_submenu_page('edit.php?post_type=vehicle', //parent slug
			'Edit Bookings', //page title
			'Edit Bookings', //menu title
			'manage_options', //capability
			'edit_booking', //menu slug
			'edit_booking'); //function
	}
	?>

	<?php
	
}
add_action('init', 'cw_post_type_vehicle');




/*Custom Post type end*/


/**
* 
* Meta box
* 
**/
function vehicle_add_meta_boxes( $post ){
	add_meta_box( 'vehicle_meta_box', __( 'Vehicle Price(per day)', 'vehicle_plugin' ), 'vehicle_build_meta_box', 'vehicle', 'side', 'low' );
}
add_action( 'add_meta_boxes_vehicle', 'vehicle_add_meta_boxes' );


function vehicle_build_meta_box( $post ){
	$vehicle_price = get_post_meta( $post->ID, 'vehicle_price', true );
	?>
	<div class='inside'>
		<p>
			<input type="text" name="vehicle_price" value="<?php echo $vehicle_price; ?>" /> 
		</p>
	</div>
	<?php
}

/** Store custom field meta box data **/
function area_save_meta_box_data( $post_id ){
	if ( isset( $_REQUEST['vehicle_price'] ) ) {
		update_post_meta( $post_id, 'vehicle_price', sanitize_text_field( $_POST['vehicle_price'] ) );
	}
}
add_action( 'save_post_vehicle', 'area_save_meta_box_data' );

/***************** Setting Page ***************/
function setting(){ ?>
	<div>
		<h3>Form Shortcode</h3>
		<p style="font-size:18px;">[vehicle-booking-form]</p>
	</div>
<?php }

add_shortcode( 'vehicle-booking-form', 'vehicle_booking_form_shortcode' );
function vehicle_booking_form_shortcode( $atts, $content = "" ) { ?>
	<?php 
		$terms = get_terms( array(
			'taxonomy' => 'categories',
			'hide_empty' => false,
		) );
		$book_message = '';
		
		if(isset($_POST['book'])){
			$first_name = $_POST['first_name'];
			$last_name  = $_POST['last_name'];
			$user_email = $_POST['user_email'];
			$user_phone = $_POST['user_phone'];
			$vehicle_type = $_POST['vehicle_type'];
			$vehicle_name = $_POST['vehicle_name'];
			$vehicle_price = $_POST['vehicle_price'];
			$message = $_POST['message'];
			
			$book_no = mt_rand(1000,9999);
			
			$new_post = array(
			'post_title' => 'Booking '.$book_no,
			'post_content' => '',
			'post_status' => 'publish',
			'post_date' => date('Y-m-d H:i:s'),
			'post_author' => '',
			'post_type' => 'vehicle_booking',
			);
			$post_id = wp_insert_post($new_post);
			update_post_meta($post_id, 'first_name', $first_name);
			update_post_meta($post_id, 'last_name', $last_name);
			update_post_meta($post_id, 'user_email', $user_email);
			update_post_meta($post_id, 'user_phone', $user_phone);
			update_post_meta($post_id, 'vehicle_type', $vehicle_type);
			update_post_meta($post_id, 'vehicle_name', $vehicle_name);
			update_post_meta($post_id, 'vehicle_price', $vehicle_price);
			update_post_meta($post_id, 'message', $message);
			update_post_meta($book_id, 'order_status', 'pending');
			
							
			$to = get_option('admin_email');
			$subject = "New Booking";
			$message = 'New booking status is pending';
			$headers = 'From: admin' .$to. "\r\n";  
			$sent = wp_mail($user_email, $subject, $message, $header);
			

			$headers = 'From:' .$user_email. "\r\n"; 
			$message = 'New booking received';			
			$sent = wp_mail($to, $subject, $message, $header);
			$book_message = 'Booking Successfull';
						
		}
		
	
	?>
    <!DOCTYPE html>
	<html lang="en">
	<head>
	  <meta charset="utf-8">
	  <meta name="viewport" content="width=device-width, initial-scale=1">
	  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
	  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
	</head>
	<body>

	<div class="container">
	<?php if($book_message != ''){ ?> 
		<div class="alert alert-success" role="alert"><?php echo $book_message; ?></div>
	<?php } ?>
	  <h2>Vehicle booking</h2>
	  <form action="" method="POST">
		<div class="form-group">
		  <label for="first_name">First Name:</label>
		  <input type="text" class="form-control" id="first_name" placeholder="Enter First Name" name="first_name" required>
		</div>
		
		<div class="form-group">
		  <label for="last_name">Last Name:</label>
		  <input type="text" class="form-control" id="last_name" placeholder="Enter Last Name" name="last_name" required>
		</div>
		
		<div class="form-group">
		  <label for="email">Email:</label>
		  <input type="email" class="form-control" id="user_email" placeholder="Enter email" name="user_email" required>
		</div>
		
		<div class="form-group">
		  <label for="phone">Phone:</label>
		  <input type="text" class="form-control" id="user_phone" placeholder="Enter phone" name="user_phone" required>
		</div>
		
		<div class="form-group">
		  <label for="vehicle_type">Vehicle Type:</label>
		  <select name="vehicle_type" id="vehicle_type" class="form-control">
			<option value="">Select Vehicle Type</option>
			<?php foreach($terms as $term){ ?>
				<option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
			<?php } ?>
		  </select>
		</div>
		
		<div class="form-group">
		  <label for="vehicle_type">Vehicle:</label>
		  <select name="vehicle_name" id="vehicle" class="form-control">
			<option value="">Select Vehicle</option>
		  </select>
		</div>
		<div class="form-group">
		  <label for="vehicle_type">Price:</label>
		  <p id="vehicle_price_text">N/A</p>
		  <input type="hidden" name="vehicle_price" id="vehicle_price" value=""/>
		</div>
		
		<div class="form-group">
		  <label for="message">Message:</label>
		  <textarea name="message"></textarea>
		</div>
		
		
		<button type="submit" class="btn btn-default" name="book">Submit</button>
	  </form>
	</div>

	</body>
	</html>
	
	<script>
		jQuery('#vehicle_type').on('change', function(){
			var vehicle_type = jQuery(this).val();
			jQuery.ajax({
			  url: "<?php echo get_site_url(); ?>/wp-content/plugins/vehicle/vehicle _ajax.php",
			  type: 'post',
			  data: {vehicle_type: vehicle_type},
			  success: function(response){
				console.log(response);
				jQuery('#vehicle').html(response);
			  }
			});
		});
		
		jQuery('#vehicle').on('change', function(){
			var vehicle_id = jQuery(this).val();
			jQuery.ajax({
			  url: "<?php echo get_site_url(); ?>/wp-content/plugins/vehicle/vehicle _ajax.php",
			  type: 'post',
			  data: {vehicle_id: vehicle_id},
			  success: function(response){
				jQuery('#vehicle_price_text').text('$'+response);
				jQuery('#vehicle_price').val(response);
			  }
			});
			
		});
	</script>
<?php }


function bookings(){ 
	$bookings = get_posts([
	  'post_type' => 'vehicle_booking',
	  'post_status' => 'publish',
	  'numberposts' => -1
	  // 'order'    => 'ASC'
	]);
	?>
	 <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
	  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
	<?php
	echo '<br><br><table class="table table-condensed">';
	echo '<thead><tr><th>Booking ID</th><th>Name</th><th>Vechicle</th><th>Action</th></tr></thead><tbody>';
	foreach($bookings as $booking){
		$first_name = get_post_meta($booking->ID, 'first_name');
		$last_name = get_post_meta($booking->ID, 'last_name');
		$vehicle_name = get_post_meta($booking->ID, 'vehicle_name');


		$first_name = $first_name[0];
		$last_name =  $last_name[0];
		$vehicle_name =  $vehicle_name[0];

		$vehicle_name = get_post($vehicle_name);
		?>
		<tr>
			<td><?php echo $booking->post_title; ?></td>
			<td><?php echo $first_name.' '.$last_name; ?></td>
			<td><?php echo $vehicle_name->post_title; ?></td>
			<td><a href="<?php echo get_site_url(); ?>/wp-admin/edit.php?post_type=vehicle&page=edit_booking&id=<?php echo $booking->ID; ?>">edit</a></td>
			
		</tr>
		<?php
	}
	echo '</tbody></table>';
}

function edit_booking(){
	$book_id = $_GET['id'];
	$content_booking = get_post($book_id);
	
	$order_status = get_post_meta($book_id, 'order_status');
	$old_order_status =  $order_status[0];

	
		$terms = get_terms( array(
			'taxonomy' => 'categories',
			'hide_empty' => false,
		) );
		
		if(isset($_POST['book'])){
			$first_name = $_POST['first_name'];
			$last_name  = $_POST['last_name'];
			$user_email = $_POST['user_email'];
			$user_phone = $_POST['user_phone'];
			$vehicle_type = $_POST['vehicle_type'];
			$vehicle_name = $_POST['vehicle_name'];
			$vehicle_price = $_POST['vehicle_price'];
			$message = $_POST['message'];
			$order_status = $_POST['order_status'];
			
			update_post_meta($book_id, 'first_name', $first_name);
			update_post_meta($book_id, 'last_name', $last_name);
			update_post_meta($book_id, 'user_email', $user_email);
			update_post_meta($book_id, 'user_phone', $user_phone);
			update_post_meta($book_id, 'vehicle_type', $vehicle_type);
			update_post_meta($book_id, 'vehicle_name', $vehicle_name);
			update_post_meta($book_id, 'vehicle_price', $vehicle_price);
			update_post_meta($book_id, 'message', $message);
			update_post_meta($book_id, 'order_status', $order_status);
			
			if($old_order_status != $order_status){				
				$to = get_option('admin_email');
				$subject = ($order_status == "Completed")?'Booking Completed':"Booking Status Changed";
				$message = ($order_status == "Completed")?'Thankyou for booking.':'New booking status is: '.$order_status;
				$headers = 'From: admin' .$to. "\r\n";  
				
				$sent = wp_mail($user_email, $subject, $message, $header);
			}

						
		}
		
		$first_name = get_post_meta($book_id, 'first_name');
		$last_name = get_post_meta($book_id, 'last_name');
		$user_email = get_post_meta($book_id, 'user_email');
		$user_phone = get_post_meta($book_id, 'user_phone');
		$vehicle_type = get_post_meta($book_id, 'vehicle_type');
		$vehicle_name = get_post_meta($book_id, 'vehicle_name');
		$vehicle_price = get_post_meta($book_id, 'vehicle_price');
		$message = get_post_meta($book_id, 'message');
		$order_status = get_post_meta($book_id, 'order_status');

		$first_name = $first_name[0];
		$last_name =  $last_name[0];
		$user_email =  $user_email[0];
		$user_phone =  $user_phone[0];
		$vehicle_type =  $vehicle_type[0];
		$vehicle_name =  $vehicle_name[0];
		$vehicle_price =  $vehicle_price[0];
		$message =  $message[0];
		$order_status =  $order_status[0];
		
		$vehicle_name = get_post($vehicle_name);
		
	
	?>
    <!DOCTYPE html>
	<html lang="en">
	<head>
	  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
	  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
	</head>
	<body>

	<div class="container">
	  <h2><?php $content_booking->post_title; ?></h2>
	  <form action="" method="POST">
		<div>
			<label for="order_status">Order Status:</label>
			<select name="order_status" class="form-group">
				<option value="Pending">Pending</option>
				<option value="Approved">Approved</option>
				<option value="Reject">Reject</option>
				<option value="On the way">On the way</option>
				<option value="Complete">Complete</option>
			</select>
		</div>
		
		<div class="form-group">
		  <label for="first_name">First Name:</label>
		  <input type="text" class="form-control" id="first_name" placeholder="Enter First Name" name="first_name" value="<?php echo $first_name; ?>">
		</div>
		
		<div class="form-group">
		  <label for="last_name">Last Name:</label>
		  <input type="text" class="form-control" id="last_name" placeholder="Enter Last Name" name="last_name" value="<?php echo $last_name; ?>">
		</div>
		
		<div class="form-group">
		  <label for="email">Email:</label>
		  <input type="email" class="form-control" id="user_email" placeholder="Enter email" name="user_email" value="<?php echo $user_email; ?>">
		</div>
		
		<div class="form-group">
		  <label for="phone">Phone:</label>
		  <input type="text" class="form-control" id="user_phone" placeholder="Enter phone" name="user_phone" value="<?php echo $user_phone; ?>">
		</div>
		
		<div class="form-group">
		  <label for="vehicle_type">Vehicle Type:</label>
		  <select name="vehicle_type" id="vehicle_type" class="form-control">
			<option value="">Select Vehicle Type</option>
			<?php foreach($terms as $term){ ?>
				<option value="<?php echo $term->term_id; ?>" <?php if($vehicle_type == $term->term_id){ echo "selected"; } ?>><?php echo $term->name; ?></option>
			<?php } ?>
		  </select>
		</div>
		
		<div class="form-group">
		  <label for="vehicle_type">Vehicle:</label>
		  <select name="vehicle_name" id="vehicle" class="form-control">
			<option value="<?php echo $vehicle_name->ID; ?>"><?php echo $vehicle_name->post_title; ?></option>
		  </select>
		</div>
		<div class="form-group">
		  <label for="vehicle_type">Price:</label>
		  <p id="vehicle_price_text"><?php echo $vehicle_price; ?></p>
		  <input type="hidden" name="vehicle_price" id="vehicle_price" value=""/>
		</div>
		
		<div class="form-group">
		  <label for="message">Message:</label>
		  <textarea name="message"><?php echo $message; ?></textarea>
		</div>
		
		
		<button type="submit" class="btn btn-default" name="book">Save</button>
	  </form>
	</div>

	</body>
	</html>
	<script>
		jQuery('#vehicle_type').on('change', function(){
			var vehicle_type = jQuery(this).val();
			jQuery.ajax({
			  url: "<?php echo get_site_url(); ?>/wp-content/plugins/vehicle/vehicle _ajax.php",
			  type: 'post',
			  data: {vehicle_type: vehicle_type},
			  success: function(response){
				console.log(response);
				jQuery('#vehicle').html(response);
			  }
			});
		});
		
		jQuery('#vehicle').on('change', function(){
			var vehicle_id = jQuery(this).val();
			jQuery.ajax({
			  url: "<?php echo get_site_url(); ?>/wp-content/plugins/vehicle/vehicle _ajax.php",
			  type: 'post',
			  data: {vehicle_id: vehicle_id},
			  success: function(response){
				jQuery('#vehicle_price_text').text('$'+response);
				jQuery('#vehicle_price').val(response);
			  }
			});
			
		});
	</script>
	<?php 
}

add_action('admin_head', 'my_custom_fonts'); 

function my_custom_fonts() {
  echo '<style>
    #menu-posts-vehicle ul li:last-child{
        display:none;   
    }
  </style>';
}

