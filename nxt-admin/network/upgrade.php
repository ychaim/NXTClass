<?php
/**
 * Multisite upgrade administration panel.
 *
 * @package NXTClass
 * @subpackage Multisite
 * @since 3.0.0
 */

/** Load NXTClass Administration Bootstrap */
require_once( './admin.php' );

if ( ! is_multisite() )
	nxt_die( __( 'Multisite support is not enabled.' ) );

require_once( ABSPATH . nxtINC . '/http.php' );

$title = __( 'Update Network' );
$parent_file = 'upgrade.php';

get_current_screen()->add_help_tab( array(
	'id'      => 'overview',
	'title'   => __('Overview'),
	'content' =>
		'<p>' . __('Only use this screen once you have updated to a new version of NXTClass through Updates/Available Updates (via the Network Administration navigation menu or the Toolbar). Clicking the Update Network button will step through each site in the network, five at a time, and make sure any database updates are applied.') . '</p>' .
		'<p>' . __('If a version update to core has not happened, clicking this button won&#8217;t affect anything.') . '</p>' .
		'<p>' . __('If this process fails for any reason, users logging in to their sites will force the same update.') . '</p>'
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="http://codex.opensource.nxtclass.tk/Network_Admin_Updates_Screen" target="_blank">Documentation on Update Network</a>') . '</p>' .
	'<p>' . __('<a href="http://opensource.nxtclass.tk/support/" target="_blank">Support Forums</a>') . '</p>'
);

require_once('../admin-header.php');

if ( ! current_user_can( 'manage_network' ) )
	nxt_die( __( 'You do not have permission to access this page.' ) );

echo '<div class="wrap">';
screen_icon('tools');
echo '<h2>' . __( 'Update Network' ) . '</h2>';

$action = isset($_GET['action']) ? $_GET['action'] : 'show';

switch ( $action ) {
	case "upgrade":
		$n = ( isset($_GET['n']) ) ? intval($_GET['n']) : 0;

		if ( $n < 5 ) {
			global $nxt_db_version;
			update_site_option( 'nxtmu_upgrade_site', $nxt_db_version );
		}

		$blogs = $nxtdb->get_results( "SELECT * FROM {$nxtdb->blogs} WHERE site_id = '{$nxtdb->siteid}' AND spam = '0' AND deleted = '0' AND archived = '0' ORDER BY registered DESC LIMIT {$n}, 5", ARRAY_A );
		if ( empty( $blogs ) ) {
			echo '<p>' . __( 'All done!' ) . '</p>';
			break;
		}
		echo "<ul>";
		foreach ( (array) $blogs as $details ) {
			$siteurl = get_blog_option( $details['blog_id'], 'siteurl' );
			echo "<li>$siteurl</li>";
			$response = nxt_remote_get( trailingslashit( $siteurl ) . "nxt-admin/upgrade.php?step=upgrade_db", array( 'timeout' => 120, 'httpversion' => '1.1' ) );
			if ( is_nxt_error( $response ) )
				nxt_die( sprintf( __( 'Warning! Problem updating %1$s. Your server may not be able to connect to sites running on it. Error message: <em>%2$s</em>' ), $siteurl, $response->get_error_message() ) );
			do_action( 'after_mu_upgrade', $response );
			do_action( 'nxtmu_upgrade_site', $details[ 'blog_id' ] );
		}
		echo "</ul>";
		?><p><?php _e( 'If your browser doesn&#8217;t start loading the next page automatically, click this link:' ); ?> <a class="button" href="upgrade.php?action=upgrade&amp;n=<?php echo ($n + 5) ?>"><?php _e("Next Sites"); ?></a></p>
		<script type='text/javascript'>
		<!--
		function nextpage() {
			location.href = "upgrade.php?action=upgrade&n=<?php echo ($n + 5) ?>";
		}
		setTimeout( "nextpage()", 250 );
		//-->
		</script><?php
	break;
	case 'show':
	default:
		?><p><?php _e( 'You can update all the sites on your network through this page. It works by calling the update script of each site automatically. Hit the link below to update.' ); ?></p>
		<p><a class="button" href="upgrade.php?action=upgrade"><?php _e("Update Network"); ?></a></p><?php
		do_action( 'nxtmu_upgrade_page' );
	break;
}
?>
</div>

<?php include('../admin-footer.php'); ?>
