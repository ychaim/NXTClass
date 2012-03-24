<?php
/**
 * Add Site Administration Screen
 *
 * @package NXTClass
 * @subpackage Multisite
 * @since 3.1.0
 */

/** Load NXTClass Administration Bootstrap */
require_once( './admin.php' );

if ( ! is_multisite() )
	nxt_die( __( 'Multisite support is not enabled.' ) );

if ( ! current_user_can( 'manage_sites' ) )
	nxt_die( __( 'You do not have sufficient permissions to add sites to this network.' ) );

	get_current_screen()->add_help_tab( array(
		'id'      => 'overview',
		'title'   => __('Overview'),
		'content' =>
			'<p>' . __('This screen is for Super Admins to add new sites to the network. This is not affected by the registration settings.') . '</p>' .
			'<p>' . __('If the admin email for the new site does not exist in the database, a new user will also be created.') . '</p>'
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="http://codex.nxtclass.org/Network_Admin_Sites_Screens" target="_blank">Documentation on Site Management</a>') . '</p>' .
	'<p>' . __('<a href="http://nxtclass.org/support/forum/multisite/" target="_blank">Support Forums</a>') . '</p>'
);

if ( isset($_REQUEST['action']) && 'add-site' == $_REQUEST['action'] ) {
	check_admin_referer( 'add-blog', '_nxtnonce_add-blog' );

	if ( ! current_user_can( 'manage_sites' ) )
		nxt_die( __( 'You do not have permission to access this page.' ) );

	if ( ! is_array( $_POST['blog'] ) )
		nxt_die( __( 'Can&#8217;t create an empty site.' ) );
	$blog = $_POST['blog'];
	$domain = '';
	if ( preg_match( '|^([a-zA-Z0-9-])+$|', $blog['domain'] ) )
		$domain = strtolower( $blog['domain'] );

	// If not a subdomain install, make sure the domain isn't a reserved word
	if ( ! is_subdomain_install() ) {
		$subdirectory_reserved_names = apply_filters( 'subdirectory_reserved_names', array( 'page', 'comments', 'blog', 'files', 'feed' ) );
		if ( in_array( $domain, $subdirectory_reserved_names ) )
			nxt_die( sprintf( __('The following words are reserved for use by NXTClass functions and cannot be used as blog names: <code>%s</code>' ), implode( '</code>, <code>', $subdirectory_reserved_names ) ) );
	}

	$email = sanitize_email( $blog['email'] );
	$title = $blog['title'];

	if ( empty( $domain ) )
		nxt_die( __( 'Missing or invalid site address.' ) );
	if ( empty( $email ) )
		nxt_die( __( 'Missing email address.' ) );
	if ( !is_email( $email ) )
		nxt_die( __( 'Invalid email address.' ) );

	if ( is_subdomain_install() ) {
		$newdomain = $domain . '.' . preg_replace( '|^www\.|', '', $current_site->domain );
		$path = $base;
	} else {
		$newdomain = $current_site->domain;
		$path = $base . $domain . '/';
	}

	$password = 'N/A';
	$user_id = email_exists($email);
	if ( !$user_id ) { // Create a new user with a random password
		$password = nxt_generate_password( 12, false );
		$user_id = nxtmu_create_user( $domain, $password, $email );
		if ( false == $user_id )
			nxt_die( __( 'There was an error creating the user.' ) );
		else
			nxt_new_user_notification( $user_id, $password );
	}

	$nxtdb->hide_errors();
	$id = nxtmu_create_blog( $newdomain, $path, $title, $user_id , array( 'public' => 1 ), $current_site->id );
	$nxtdb->show_errors();
	if ( !is_nxt_error( $id ) ) {
		if ( !is_super_admin( $user_id ) && !get_user_option( 'primary_blog', $user_id ) )
			update_user_option( $user_id, 'primary_blog', $id, true );
		$content_mail = sprintf( __( "New site created by %1s\n\nAddress: %2s\nName: %3s"), $current_user->user_login , get_site_url( $id ), stripslashes( $title ) );
		nxt_mail( get_site_option('admin_email'), sprintf( __( '[%s] New Site Created' ), $current_site->site_name ), $content_mail, 'From: "Site Admin" <' . get_site_option( 'admin_email' ) . '>' );
		nxtmu_welcome_notification( $id, $user_id, $password, $title, array( 'public' => 1 ) );
		nxt_redirect( add_query_arg( array( 'update' => 'added', 'id' => $id ), 'site-new.php' ) );
		exit;
	} else {
		nxt_die( $id->get_error_message() );
	}
}

if ( isset($_GET['update']) ) {
	$messages = array();
	if ( 'added' == $_GET['update'] )
		$messages[] = sprintf( __( 'Site added. <a href="%1$s">Visit Dashboard</a> or <a href="%2$s">Edit Site</a>' ), esc_url( get_admin_url( absint( $_GET['id'] ) ) ), network_admin_url( 'site-info.php?id=' . absint( $_GET['id'] ) ) );
}

$title = __('Add New Site');
$parent_file = 'sites.php';

require('../admin-header.php');

?>

<div class="wrap">
<?php screen_icon('ms-admin'); ?>
<h2 id="add-new-site"><?php _e('Add New Site') ?></h2>
<?php
if ( ! empty( $messages ) ) {
	foreach ( $messages as $msg )
		echo '<div id="message" class="updated"><p>' . $msg . '</p></div>';
} ?>
<form method="post" action="<?php echo network_admin_url('site-new.php?action=add-site'); ?>">
<?php nxt_nonce_field( 'add-blog', '_nxtnonce_add-blog' ) ?>
	<table class="form-table">
		<tr class="form-field form-required">
			<th scope="row"><?php _e( 'Site Address' ) ?></th>
			<td>
			<?php if ( is_subdomain_install() ) { ?>
				<input name="blog[domain]" type="text" class="regular-text" title="<?php esc_attr_e( 'Domain' ) ?>"/>.<?php echo preg_replace( '|^www\.|', '', $current_site->domain );?>
			<?php } else {
				echo $current_site->domain . $current_site->path ?><input name="blog[domain]" class="regular-text" type="text" title="<?php esc_attr_e( 'Domain' ) ?>"/>
			<?php }
			echo '<p>' . __( 'Only the characters a-z and 0-9 recommended.' ) . '</p>';
			?>
			</td>
		</tr>
		<tr class="form-field form-required">
			<th scope="row"><?php _e( 'Site Title' ) ?></th>
			<td><input name="blog[title]" type="text" class="regular-text" title="<?php esc_attr_e( 'Title' ) ?>"/></td>
		</tr>
		<tr class="form-field form-required">
			<th scope="row"><?php _e( 'Admin Email' ) ?></th>
			<td><input name="blog[email]" type="text" class="regular-text" title="<?php esc_attr_e( 'Email' ) ?>"/></td>
		</tr>
		<tr class="form-field">
			<td colspan="2"><?php _e( 'A new user will be created if the above email address is not in the database.' ) ?><br /><?php _e( 'The username and password will be mailed to this email address.' ) ?></td>
		</tr>
	</table>
	<?php submit_button( __('Add Site'), 'primary', 'add-site' ); ?>
	</form>
</div>
<?php
require('../admin-footer.php');
?>
