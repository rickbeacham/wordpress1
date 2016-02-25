<?php
/**
 * Plugin Name: Refferral - Refferring users
 * Plugin URI: http://busa-solutions.com/
 * Description: Plugin for reffering new users and details from the existing users
 * Version: 1.0
 * Author: Raj
 * License: GPLv2+
 * Text Domain: busa-solutions
 */
class Refferal {

  	// Constructor
	function __construct() {

	    //add_action( 'admin_menu', array( $this, 'refferal_add_menu' ));
	    register_activation_hook( __FILE__, array( $this, 'refferal_install' ) );
	    register_deactivation_hook( __FILE__, array( $this, 'refferal_uninstall' ) );
	}

	/*
     * Actions perform on activation of plugin
     */
    function refferal_install() {
		$post_id = $this->create_refferal_shortcode();

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'refferal';

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		  id int(9) NOT NULL AUTO_INCREMENT,
		  first_name varchar(20)  NULL,
		  last_name varchar(20) NULL,
		  email_id varchar(40) NOT NULL,
		  member_id int(9) NOT NULL,
		  UNIQUE KEY id (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		if( -1 == $post_id || -2 == $post_id ) {
		   return "The post wasn't created or the page already exists";
		}
    }

    /*
     * Actions perform on de-activation of plugin
     */
    function refferal_uninstall() {
//get_page_by_title()


    }

	/**
	 * A function used to create a post in WordPress. The slug, author ID, and title
	 * are defined within the context of the function.
	 *
	 * @returns 
	 */
	function create_refferal_shortcode() {

		$title = 'Refferal';

		// If the page doesn't already exist, then create it
		if( null == get_page_by_title( $title ) ) {

			// Set the post ID so that we know the post was created successfully
			$post_id = wp_insert_post(
				array(
					'comment_status'=>	'closed',
					'ping_status'	=>	'closed',
					'post_author'	=>	1,
					'post_name'		=>	'refferal',
					'post_content'  =>  '[bs_refferal]',
					'post_title'	=>	'Refferal',
					'post_status'	=>	'publish',
					'post_type'		=>	'page'
				)
			);
		}
	}

	/**
	 * HTML code for the refferal purpose
	 */
    public function html_form_code() {
    	wp_enqueue_style( 'bb_referal_stylesheet', plugins_url( 'css/refferal-style.css', __FILE__ ) );
	    echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
		echo '<div class="refferal-box">';
		$j =1;
		for($i=1; $i <=5; $i++) {
			echo '<div class="reffer"><div class="reffer-half">';
			echo '<label for="first name">First name</label>';
			echo '<input type="text" tabindex="'.$j++.'" value="" placeholder="First name" name="first_name_'.$i.'" size="20">';
			echo '</div><div class="reffer-half"><label for="last name">Last name</label>';
			echo '<input type="text" tabindex="'.$j++.'" value="" placeholder="Last name" name="last_name_'.$i.'" size="20">';
			echo '</div><div class="reffer-full"><label for="email ID">Email Address</label>';
			echo '<input type="email" tabindex="'.$j++.'" value="" placeholder="Email Address" name="email_id_'.$i.'" size="40">';
			echo '</div></div>';
		}
		echo '</div><input type="hidden" name="member_id" value="'.$_GET['id'].'"/>';
	    echo '<p><input type="submit" name="cf-submitted" value="Send"/></p>';
	    echo '</form>';
	}

	/**
	 * Refferal short code
	 */
	public function refferal_shortcode() {
		ob_start();
		$this->add_refferal_details();
		$this->html_form_code();

		return ob_get_clean();
	}

	public function add_refferal_details()
	{
		global $wpdb;

		$table_name = $wpdb->prefix . 'refferal';
		if($_POST)
		{
			for($i=1; $i <=5; $i++) {
				if($_POST['email_id_'.$i]) {
					$reffers = array(
						'email_id' => $_POST['email_id_'.$i],
						'first_name' => $_POST['first_name_'.$i],
						'last_name' => $_POST['last_name_'.$i],
						'member_id' => $_POST['member_id'],
					);
					$wpdb->insert( $table_name, $reffers );
				}
			}
		}
	}
}

$refferal = new Refferal();
add_shortcode('bs_refferal', array($refferal, 'refferal_shortcode'));
add_action( 'admin_menu', 'refferal_admin_menu' );

function refferal_admin_menu() {
	add_menu_page( 'Reffered users', 'Reffered users', 'manage_options', 'refferal/refferal.php', 'refferal_admin_page', 'dashicons-admin-users', 6  );
}

function refferal_admin_page(){

	global $wpdb;
	$table = $wpdb->prefix.'refferal';
	$results = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC LIMIT 100", OBJECT );
	?>
	<div class="wrap">
		<h2>Welcome To My Plugin</h2>
		<table class="wp-list-table widefat fixed users">
    <thead>
    <tr>
        <th style="" class="manage-column column-username sortable desc" id="username" scope="col"><span>First name</span></th>
        <th style="" class="manage-column column-name sortable desc" id="name" scope="col"><span>Last name</span></th>
        <th style="" class="manage-column column-email sortable desc" id="email" scope="col"><span>E-mail</span></th>
    </tr>
    </thead>

    <tbody data-wp-lists="list:user" id="the-list">
        <?php foreach ($results as $value) { ?>
	    <tr class="alternate" id="user-1">
		    <td class="username column-username">
		    <img height="32" width="32" class="avatar avatar-32 photo" src="http://0.gravatar.com/avatar/af571df299349abee1f5356fd42a43d4?s=32&amp;d=http%3A%2F%2F0.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D32&amp;r=G" alt="">
		    <strong><a href="#"><?php echo $value->first_name;?></a></strong><br></td>
		    <td class="email column-email"><?php echo $value->last_name;?></td><td class="role column-role"><a title="E-mail:<?php echo $value->email_id;?>" href="mailto:<?php echo $value->email_id;?>"><?php echo $value->email_id;?></a></td>
	    </tr>
    	<?php } ?>
    </tbody>
</table>
	</div>
	<?php
}
