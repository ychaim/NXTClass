<?php
/**
 * NXTClass Dashboard Widget Administration Screen API
 *
 * @package NXTClass
 * @subpackage Administration
 */

/**
 * Registers dashboard widgets.
 *
 * Handles POST data, sets up filters.
 *
 * @since 2.5.0
 */
function nxt_dashboard_setup() {
	global $nxt_registered_widgets, $nxt_registered_widget_controls, $nxt_dashboard_control_callbacks;
	$nxt_dashboard_control_callbacks = array();
	$screen = get_current_screen();

	$update = false;
	$widget_options = get_option( 'dashboard_widget_options' );
	if ( !$widget_options || !is_array($widget_options) )
		$widget_options = array();

	/* Register Widgets and Controls */

	$response = nxt_check_browser_version();

	if ( $response && $response['upgrade'] ) {
		add_filter( 'postbox_classes_dashboard_dashboard_browser_nag', 'dashboard_browser_nag_class' );
		if ( $response['insecure'] )
			nxt_add_dashboard_widget( 'dashboard_browser_nag', __( 'You are using an insecure browser!' ), 'nxt_dashboard_browser_nag' );
		else
			nxt_add_dashboard_widget( 'dashboard_browser_nag', __( 'Your browser is out of date!' ), 'nxt_dashboard_browser_nag' );
	}

	// Right Now
	if ( is_blog_admin() && current_user_can('edit_posts') )
		nxt_add_dashboard_widget( 'dashboard_right_now', __( 'Right Now' ), 'nxt_dashboard_right_now' );

	if ( is_network_admin() )
		nxt_add_dashboard_widget( 'network_dashboard_right_now', __( 'Right Now' ), 'nxt_network_dashboard_right_now' );

	// Recent Comments Widget
	if ( is_blog_admin() && current_user_can('moderate_comments') ) {
		if ( !isset( $widget_options['dashboard_recent_comments'] ) || !isset( $widget_options['dashboard_recent_comments']['items'] ) ) {
			$update = true;
			$widget_options['dashboard_recent_comments'] = array(
				'items' => 5,
			);
		}
		$recent_comments_title = __( 'Recent Comments' );
		nxt_add_dashboard_widget( 'dashboard_recent_comments', $recent_comments_title, 'nxt_dashboard_recent_comments', 'nxt_dashboard_recent_comments_control' );
	}

	// Incoming Links Widget
	if ( is_blog_admin() && current_user_can('publish_posts') ) {
		if ( !isset( $widget_options['dashboard_incoming_links'] ) || !isset( $widget_options['dashboard_incoming_links']['home'] ) || $widget_options['dashboard_incoming_links']['home'] != get_option('home') ) {
			$update = true;
			$num_items = isset($widget_options['dashboard_incoming_links']['items']) ? $widget_options['dashboard_incoming_links']['items'] : 10;
			$widget_options['dashboard_incoming_links'] = array(
				'home' => get_option('home'),
				'link' => apply_filters( 'dashboard_incoming_links_link', 'http://blogsearch.google.com/blogsearch?scoring=d&partner=nxtclass&q=link:' . trailingslashit( get_option('home') ) ),
				'url' => isset($widget_options['dashboard_incoming_links']['url']) ? apply_filters( 'dashboard_incoming_links_feed', $widget_options['dashboard_incoming_links']['url'] ) : apply_filters( 'dashboard_incoming_links_feed', 'http://blogsearch.google.com/blogsearch_feeds?scoring=d&ie=utf-8&num=' . $num_items . '&output=rss&partner=nxtclass&q=link:' . trailingslashit( get_option('home') ) ),
				'items' => $num_items,
				'show_date' => isset($widget_options['dashboard_incoming_links']['show_date']) ? $widget_options['dashboard_incoming_links']['show_date'] : false
			);
		}
		nxt_add_dashboard_widget( 'dashboard_incoming_links', __( 'Incoming Links' ), 'nxt_dashboard_incoming_links', 'nxt_dashboard_incoming_links_control' );
	}

	// nxt Plugins Widget
	if ( ( ! is_multisite() && is_blog_admin() && current_user_can( 'install_plugins' ) ) || ( is_network_admin() && current_user_can( 'manage_network_plugins' ) && current_user_can( 'install_plugins' ) ) )
		nxt_add_dashboard_widget( 'dashboard_plugins', __( 'Plugins' ), 'nxt_dashboard_plugins' );

	// QuickPress Widget
	if ( is_blog_admin() && current_user_can('edit_posts') )
		nxt_add_dashboard_widget( 'dashboard_quick_press', __( 'QuickPress' ), 'nxt_dashboard_quick_press' );

	// Recent Drafts
	if ( is_blog_admin() && current_user_can('edit_posts') )
		nxt_add_dashboard_widget( 'dashboard_recent_drafts', __('Recent Drafts'), 'nxt_dashboard_recent_drafts' );

	// Primary feed (Dev Blog) Widget
	if ( !isset( $widget_options['dashboard_primary'] ) ) {
		$update = true;
		$widget_options['dashboard_primary'] = array(
			'link' => apply_filters( 'dashboard_primary_link',  __( 'http://nxtclass.org/news/' ) ),
			'url' => apply_filters( 'dashboard_primary_feed',  __( 'http://nxtclass.org/news/feed/' ) ),
			'title' => apply_filters( 'dashboard_primary_title', __( 'NXTClass Blog' ) ),
			'items' => 2,
			'show_summary' => 1,
			'show_author' => 0,
			'show_date' => 1,
		);
	}
	nxt_add_dashboard_widget( 'dashboard_primary', $widget_options['dashboard_primary']['title'], 'nxt_dashboard_primary', 'nxt_dashboard_primary_control' );

	// Secondary Feed (Planet) Widget
	if ( !isset( $widget_options['dashboard_secondary'] ) ) {
		$update = true;
		$widget_options['dashboard_secondary'] = array(
			'link' => apply_filters( 'dashboard_secondary_link',  __( 'http://planet.nxtclass.org/' ) ),
			'url' => apply_filters( 'dashboard_secondary_feed',  __( 'http://planet.nxtclass.org/feed/' ) ),
			'title' => apply_filters( 'dashboard_secondary_title', __( 'Other NXTClass News' ) ),
			'items' => 5,
			'show_summary' => 0,
			'show_author' => 0,
			'show_date' => 0,
		);
	}
	nxt_add_dashboard_widget( 'dashboard_secondary', $widget_options['dashboard_secondary']['title'], 'nxt_dashboard_secondary', 'nxt_dashboard_secondary_control' );

	// Hook to register new widgets
	// Filter widget order
	if ( is_network_admin() ) {
		do_action( 'nxt_network_dashboard_setup' );
		$dashboard_widgets = apply_filters( 'nxt_network_dashboard_widgets', array() );
	} elseif ( is_user_admin() ) {
		do_action( 'nxt_user_dashboard_setup' );
		$dashboard_widgets = apply_filters( 'nxt_user_dashboard_widgets', array() );
	} else {
		do_action( 'nxt_dashboard_setup' );
		$dashboard_widgets = apply_filters( 'nxt_dashboard_widgets', array() );
	}

	foreach ( $dashboard_widgets as $widget_id ) {
		$name = empty( $nxt_registered_widgets[$widget_id]['all_link'] ) ? $nxt_registered_widgets[$widget_id]['name'] : $nxt_registered_widgets[$widget_id]['name'] . " <a href='{$nxt_registered_widgets[$widget_id]['all_link']}' class='edit-box open-box'>" . __('View all') . '</a>';
		nxt_add_dashboard_widget( $widget_id, $name, $nxt_registered_widgets[$widget_id]['callback'], $nxt_registered_widget_controls[$widget_id]['callback'] );
	}

	if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['widget_id']) ) {
		ob_start(); // hack - but the same hack nxt-admin/widgets.php uses
		nxt_dashboard_trigger_widget_control( $_POST['widget_id'] );
		ob_end_clean();
		nxt_redirect( remove_query_arg( 'edit' ) );
		exit;
	}

	if ( $update )
		update_option( 'dashboard_widget_options', $widget_options );

	do_action('do_meta_boxes', $screen->id, 'normal', '');
	do_action('do_meta_boxes', $screen->id, 'side', '');
}

function nxt_add_dashboard_widget( $widget_id, $widget_name, $callback, $control_callback = null ) {
	$screen = get_current_screen();
	global $nxt_dashboard_control_callbacks;

	if ( $control_callback && current_user_can( 'edit_dashboard' ) && is_callable( $control_callback ) ) {
		$nxt_dashboard_control_callbacks[$widget_id] = $control_callback;
		if ( isset( $_GET['edit'] ) && $widget_id == $_GET['edit'] ) {
			list($url) = explode( '#', add_query_arg( 'edit', false ), 2 );
			$widget_name .= ' <span class="postbox-title-action"><a href="' . esc_url( $url ) . '">' . __( 'Cancel' ) . '</a></span>';
			$callback = '_nxt_dashboard_control_callback';
		} else {
			list($url) = explode( '#', add_query_arg( 'edit', $widget_id ), 2 );
			$widget_name .= ' <span class="postbox-title-action"><a href="' . esc_url( "$url#$widget_id" ) . '" class="edit-box open-box">' . __( 'Configure' ) . '</a></span>';
		}
	}

	if ( is_blog_admin () )
		$side_widgets = array('dashboard_quick_press', 'dashboard_recent_drafts', 'dashboard_primary', 'dashboard_secondary');
	else if (is_network_admin() )
		$side_widgets = array('dashboard_primary', 'dashboard_secondary');
	else
		$side_widgets = array();

	$location = 'normal';
	if ( in_array($widget_id, $side_widgets) )
		$location = 'side';

	$priority = 'core';
	if ( 'dashboard_browser_nag' === $widget_id )
		$priority = 'high';

	add_meta_box( $widget_id, $widget_name, $callback, $screen, $location, $priority );
}

function _nxt_dashboard_control_callback( $dashboard, $meta_box ) {
	echo '<form action="" method="post" class="dashboard-widget-control-form">';
	nxt_dashboard_trigger_widget_control( $meta_box['id'] );
	echo '<input type="hidden" name="widget_id" value="' . esc_attr($meta_box['id']) . '" />';
	submit_button( __('Submit') );
	echo '</form>';
}

/**
 * Displays the dashboard.
 *
 * @since 2.5.0
 */
function nxt_dashboard() {
	global $screen_layout_columns;

	$screen = get_current_screen();

	$hide2 = $hide3 = $hide4 = '';
	switch ( $screen_layout_columns ) {
		case 4:
			$width = 'width:25%;';
			break;
		case 3:
			$width = 'width:33.333333%;';
			$hide4 = 'display:none;';
			break;
		case 2:
			$width = 'width:50%;';
			$hide3 = $hide4 = 'display:none;';
			break;
		default:
			$width = 'width:100%;';
			$hide2 = $hide3 = $hide4 = 'display:none;';
	}
?>
<div id="dashboard-widgets" class="metabox-holder">
<?php
	echo "\t<div id='postbox-container-1' class='postbox-container' style='$width'>\n";
	do_meta_boxes( $screen->id, 'normal', '' );

	echo "\t</div><div id='postbox-container-2' class='postbox-container' style='{$hide2}$width'>\n";
	do_meta_boxes( $screen->id, 'side', '' );

	echo "\t</div><div id='postbox-container-3' class='postbox-container' style='{$hide3}$width'>\n";
	do_meta_boxes( $screen->id, 'column3', '' );

	echo "\t</div><div id='postbox-container-4' class='postbox-container' style='{$hide4}$width'>\n";
	do_meta_boxes( $screen->id, 'column4', '' );
?>
</div></div>

<form style="display:none" method="get" action="">
	<p>
<?php
	nxt_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
	nxt_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
?>
	</p>
</form>

<?php
}

/* Dashboard Widgets */

function nxt_dashboard_right_now() {
	global $nxt_registered_sidebars;

	$num_posts = nxt_count_posts( 'post' );
	$num_pages = nxt_count_posts( 'page' );

	$num_cats  = nxt_count_terms('category');

	$num_tags = nxt_count_terms('post_tag');

	$num_comm = nxt_count_comments( );

	echo "\n\t".'<div class="table table_content">';
	echo "\n\t".'<p class="sub">' . __('Content') . '</p>'."\n\t".'<table>';
	echo "\n\t".'<tr class="first">';

	// Posts
	$num = number_format_i18n( $num_posts->publish );
	$text = _n( 'Post', 'Posts', intval($num_posts->publish) );
	if ( current_user_can( 'edit_posts' ) ) {
		$num = "<a href='edit.php'>$num</a>";
		$text = "<a href='edit.php'>$text</a>";
	}
	echo '<td class="first b b-posts">' . $num . '</td>';
	echo '<td class="t posts">' . $text . '</td>';

	echo '</tr><tr>';
	/* TODO: Show status breakdown on hover
	if ( $can_edit_pages && !empty($num_pages->publish) ) { // how many pages is not exposed in feeds.  Don't show if !current_user_can
		$post_type_texts[] = '<a href="edit-pages.php">'.sprintf( _n( '%s page', '%s pages', $num_pages->publish ), number_format_i18n( $num_pages->publish ) ).'</a>';
	}
	if ( $can_edit_posts && !empty($num_posts->draft) ) {
		$post_type_texts[] = '<a href="edit.php?post_status=draft">'.sprintf( _n( '%s draft', '%s drafts', $num_posts->draft ), number_format_i18n( $num_posts->draft ) ).'</a>';
	}
	if ( $can_edit_posts && !empty($num_posts->future) ) {
		$post_type_texts[] = '<a href="edit.php?post_status=future">'.sprintf( _n( '%s scheduled post', '%s scheduled posts', $num_posts->future ), number_format_i18n( $num_posts->future ) ).'</a>';
	}
	if ( current_user_can('publish_posts') && !empty($num_posts->pending) ) {
		$pending_text = sprintf( _n( 'There is <a href="%1$s">%2$s post</a> pending your review.', 'There are <a href="%1$s">%2$s posts</a> pending your review.', $num_posts->pending ), 'edit.php?post_status=pending', number_format_i18n( $num_posts->pending ) );
	} else {
		$pending_text = '';
	}
	*/

	// Pages
	$num = number_format_i18n( $num_pages->publish );
	$text = _n( 'Page', 'Pages', $num_pages->publish );
	if ( current_user_can( 'edit_pages' ) ) {
		$num = "<a href='edit.php?post_type=page'>$num</a>";
		$text = "<a href='edit.php?post_type=page'>$text</a>";
	}
	echo '<td class="first b b_pages">' . $num . '</td>';
	echo '<td class="t pages">' . $text . '</td>';

	echo '</tr><tr>';

	// Categories
	$num = number_format_i18n( $num_cats );
	$text = _n( 'Category', 'Categories', $num_cats );
	if ( current_user_can( 'manage_categories' ) ) {
		$num = "<a href='edit-tags.php?taxonomy=category'>$num</a>";
		$text = "<a href='edit-tags.php?taxonomy=category'>$text</a>";
	}
	echo '<td class="first b b-cats">' . $num . '</td>';
	echo '<td class="t cats">' . $text . '</td>';

	echo '</tr><tr>';

	// Tags
	$num = number_format_i18n( $num_tags );
	$text = _n( 'Tag', 'Tags', $num_tags );
	if ( current_user_can( 'manage_categories' ) ) {
		$num = "<a href='edit-tags.php'>$num</a>";
		$text = "<a href='edit-tags.php'>$text</a>";
	}
	echo '<td class="first b b-tags">' . $num . '</td>';
	echo '<td class="t tags">' . $text . '</td>';

	echo "</tr>";
	do_action('right_now_content_table_end');
	echo "\n\t</table>\n\t</div>";


	echo "\n\t".'<div class="table table_discussion">';
	echo "\n\t".'<p class="sub">' . __('Discussion') . '</p>'."\n\t".'<table>';
	echo "\n\t".'<tr class="first">';

	// Total Comments
	$num = '<span class="total-count">' . number_format_i18n($num_comm->total_comments) . '</span>';
	$text = _n( 'Comment', 'Comments', $num_comm->total_comments );
	if ( current_user_can( 'moderate_comments' ) ) {
		$num = '<a href="edit-comments.php">' . $num . '</a>';
		$text = '<a href="edit-comments.php">' . $text . '</a>';
	}
	echo '<td class="b b-comments">' . $num . '</td>';
	echo '<td class="last t comments">' . $text . '</td>';

	echo '</tr><tr>';

	// Approved Comments
	$num = '<span class="approved-count">' . number_format_i18n($num_comm->approved) . '</span>';
	$text = _nx( 'Approved', 'Approved', $num_comm->approved, 'Right Now' );
	if ( current_user_can( 'moderate_comments' ) ) {
		$num = "<a href='edit-comments.php?comment_status=approved'>$num</a>";
		$text = "<a class='approved' href='edit-comments.php?comment_status=approved'>$text</a>";
	}
	echo '<td class="b b_approved">' . $num . '</td>';
	echo '<td class="last t">' . $text . '</td>';

	echo "</tr>\n\t<tr>";

	// Pending Comments
	$num = '<span class="pending-count">' . number_format_i18n($num_comm->moderated) . '</span>';
	$text = _n( 'Pending', 'Pending', $num_comm->moderated );
	if ( current_user_can( 'moderate_comments' ) ) {
		$num = "<a href='edit-comments.php?comment_status=moderated'>$num</a>";
		$text = "<a class='waiting' href='edit-comments.php?comment_status=moderated'>$text</a>";
	}
	echo '<td class="b b-waiting">' . $num . '</td>';
	echo '<td class="last t">' . $text . '</td>';

	echo "</tr>\n\t<tr>";

	// Spam Comments
	$num = number_format_i18n($num_comm->spam);
	$text = _nx( 'Spam', 'Spam', $num_comm->spam, 'comment' );
	if ( current_user_can( 'moderate_comments' ) ) {
		$num = "<a href='edit-comments.php?comment_status=spam'><span class='spam-count'>$num</span></a>";
		$text = "<a class='spam' href='edit-comments.php?comment_status=spam'>$text</a>";
	}
	echo '<td class="b b-spam">' . $num . '</td>';
	echo '<td class="last t">' . $text . '</td>';

	echo "</tr>";
	do_action('right_now_table_end');
	do_action('right_now_discussion_table_end');
	echo "\n\t</table>\n\t</div>";

	echo "\n\t".'<div class="versions">';
	$ct = current_theme_info();

	echo "\n\t<p>";

	if ( empty( $ct->stylesheet_dir ) ) {
		if ( ! is_multisite() || is_super_admin() )
			echo '<span class="error-message">' . __('ERROR: The themes directory is either empty or doesn&#8217;t exist. Please check your installation.') . '</span>';
	} elseif ( ! empty($nxt_registered_sidebars) ) {
		$sidebars_widgets = nxt_get_sidebars_widgets();
		$num_widgets = 0;
		foreach ( (array) $sidebars_widgets as $k => $v ) {
			if ( 'nxt_inactive_widgets' == $k || 'orphaned_widgets' == substr( $k, 0, 16 ) )
				continue;
			if ( is_array($v) )
				$num_widgets = $num_widgets + count($v);
		}
		$num = number_format_i18n( $num_widgets );

		$switch_themes = $ct->title;
		if ( current_user_can( 'switch_themes') )
			$switch_themes = '<a href="themes.php">' . $switch_themes . '</a>';
		if ( current_user_can( 'edit_theme_options' ) ) {
			printf(_n('Theme <span class="b">%1$s</span> with <span class="b"><a href="widgets.php">%2$s Widget</a></span>', 'Theme <span class="b">%1$s</span> with <span class="b"><a href="widgets.php">%2$s Widgets</a></span>', $num_widgets), $switch_themes, $num);
		} else {
			printf(_n('Theme <span class="b">%1$s</span> with <span class="b">%2$s Widget</span>', 'Theme <span class="b">%1$s</span> with <span class="b">%2$s Widgets</span>', $num_widgets), $switch_themes, $num);
		}
	} else {
		if ( current_user_can( 'switch_themes' ) )
			printf( __('Theme <span class="b"><a href="themes.php">%1$s</a></span>'), $ct->title );
		else
			printf( __('Theme <span class="b">%1$s</span>'), $ct->title );
	}
	echo '</p>';

	// Check if search engines are blocked.
	if ( !is_network_admin() && !is_user_admin() && current_user_can('manage_options') && '1' != get_option('blog_public') ) {
		$title = apply_filters('privacy_on_link_title', __('Your site is asking search engines not to index its content') );
		$content = apply_filters('privacy_on_link_text', __('Search Engines Blocked') );

		echo "<p><a href='options-privacy.php' title='$title'>$content</a></p>";
	}

	update_right_now_message();

	echo "\n\t".'<br class="clear" /></div>';
	do_action( 'rightnow_end' );
	do_action( 'activity_box_end' );
}

function nxt_network_dashboard_right_now() {
	$actions = array();
	if ( current_user_can('create_sites') )
		$actions['create-site'] = '<a href="' . network_admin_url('site-new.php') . '">' . __( 'Create a New Site' ) . '</a>';
	if ( current_user_can('create_users') )
		$actions['create-user'] = '<a href="' . network_admin_url('user-new.php') . '">' . __( 'Create a New User' ) . '</a>';

	$c_users = get_user_count();
	$c_blogs = get_blog_count();

	$user_text = sprintf( _n( '%s user', '%s users', $c_users ), number_format_i18n( $c_users ) );
	$blog_text = sprintf( _n( '%s site', '%s sites', $c_blogs ), number_format_i18n( $c_blogs ) );

	$sentence = sprintf( __( 'You have %1$s and %2$s.' ), $blog_text, $user_text );

	if ( $actions ) {
		echo '<ul class="subsubsub">';
		foreach ( $actions as $class => $action ) {
			 $actions[ $class ] = "\t<li class='$class'>$action";
		}
		echo implode( " |</li>\n", $actions ) . "</li>\n";
		echo '</ul>';
	}
?>
	<br class="clear" />

	<p class="youhave"><?php echo $sentence; ?></p>
	<?php do_action( 'nxtmuadminresult', '' ); ?>

	<form name="searchform" action="<?php echo network_admin_url('users.php'); ?>" method="get">
		<p>
			<input type="text" name="s" value="" size="17" />
			<?php submit_button( __( 'Search Users' ), 'button', 'submit', false, array( 'id' => 'submit_users' ) ); ?>
		</p>
	</form>

	<form name="searchform" action="<?php echo network_admin_url('sites.php'); ?>" method="get">
		<p>
			<input type="text" name="s" value="" size="17" />
			<?php submit_button( __( 'Search Sites' ), 'button', 'submit', false, array( 'id' => 'submit_sites' ) ); ?>
		</p>
	</form>
<?php
	do_action( 'mu_rightnow_end' );
	do_action( 'mu_activity_box_end' );
}

function nxt_dashboard_quick_press() {
	global $post_ID;

	$drafts = false;
	if ( 'post' === strtolower( $_SERVER['REQUEST_METHOD'] ) && isset( $_POST['action'] ) && 0 === strpos( $_POST['action'], 'post-quickpress' ) && (int) $_POST['post_ID'] ) {
		$view = get_permalink( $_POST['post_ID'] );
		$edit = esc_url( get_edit_post_link( $_POST['post_ID'] ) );
		if ( 'post-quickpress-publish' == $_POST['action'] ) {
			if ( current_user_can('publish_posts') )
				printf( '<div class="updated"><p>' . __( 'Post published. <a href="%s">View post</a> | <a href="%s">Edit post</a>' ) . '</p></div>', esc_url( $view ), $edit );
			else
				printf( '<div class="updated"><p>' . __( 'Post submitted. <a href="%s">Preview post</a> | <a href="%s">Edit post</a>' ) . '</p></div>', esc_url( add_query_arg( 'preview', 1, $view ) ), $edit );
		} else {
			printf( '<div class="updated"><p>' . __( 'Draft saved. <a href="%s">Preview post</a> | <a href="%s">Edit post</a>' ) . '</p></div>', esc_url( add_query_arg( 'preview', 1, $view ) ), $edit );
			$drafts_query = new nxt_Query( array(
				'post_type' => 'post',
				'post_status' => 'draft',
				'author' => $GLOBALS['current_user']->ID,
				'posts_per_page' => 1,
				'orderby' => 'modified',
				'order' => 'DESC'
			) );

			if ( $drafts_query->posts )
				$drafts =& $drafts_query->posts;
		}
		printf('<p class="textright">' . __('You can also try %s, easy blogging from anywhere on the Web.') . '</p>', '<a href="' . esc_url( admin_url( 'tools.php' ) ) . '">' . __('Press This') . '</a>' );
		$_REQUEST = array(); // hack for get_default_post_to_edit()
	}

	/* Check if a new auto-draft (= no new post_ID) is needed or if the old can be used */
	$last_post_id = (int) get_user_option( 'dashboard_quick_press_last_post_id' ); // Get the last post_ID
	if ( $last_post_id ) {
		$post = get_post( $last_post_id );
		if ( empty( $post ) || $post->post_status != 'auto-draft' ) { // auto-draft doesn't exists anymore
			$post = get_default_post_to_edit('post', true);
			update_user_option( (int) $GLOBALS['current_user']->ID, 'dashboard_quick_press_last_post_id', (int) $post->ID ); // Save post_ID
		} else {
			$post->post_title = ''; // Remove the auto draft title
		}
	} else {
		$post = get_default_post_to_edit('post', true);
		update_user_option( (int) $GLOBALS['current_user']->ID, 'dashboard_quick_press_last_post_id', (int) $post->ID ); // Save post_ID
	}

	$post_ID = (int) $post->ID;
?>

	<form name="post" action="<?php echo esc_url( admin_url( 'post.php' ) ); ?>" method="post" id="quick-press">
		<h4 id="quick-post-title"><label for="title"><?php _e('Title') ?></label></h4>
		<div class="input-text-wrap">
			<input type="text" name="post_title" id="title" tabindex="1" autocomplete="off" value="<?php echo esc_attr( $post->post_title ); ?>" />
		</div>

		<?php if ( current_user_can( 'upload_files' ) ) : ?>
		<div id="nxt-content-wrap" class="nxt-editor-wrap hide-if-no-js nxt-media-buttons">
			<?php do_action( 'media_buttons', 'content' ); ?>
		</div>
		<?php endif; ?>

		<h4 id="content-label"><label for="content"><?php _e('Content') ?></label></h4>
		<div class="textarea-wrap">
			<textarea name="content" id="content" class="mceEditor" rows="3" cols="15" tabindex="2"><?php echo esc_textarea( $post->post_content ); ?></textarea>
		</div>

		<script type="text/javascript">edCanvas = document.getElementById('content');edInsertContent = null;</script>

		<h4><label for="tags-input"><?php _e('Tags') ?></label></h4>
		<div class="input-text-wrap">
			<input type="text" name="tags_input" id="tags-input" tabindex="3" value="<?php echo get_tags_to_edit( $post->ID ); ?>" />
		</div>

		<p class="submit">
			<input type="hidden" name="action" id="quickpost-action" value="post-quickpress-save" />
			<input type="hidden" name="post_ID" value="<?php echo $post_ID; ?>" />
			<input type="hidden" name="post_type" value="post" />
			<?php nxt_nonce_field('add-post'); ?>
			<?php submit_button( __( 'Save Draft' ), 'button', 'save', false, array( 'id' => 'save-post', 'tabindex'=> 4 ) ); ?>
			<input type="reset" value="<?php esc_attr_e( 'Reset' ); ?>" class="button" />
			<span id="publishing-action">
				<input type="submit" name="publish" id="publish" accesskey="p" tabindex="5" class="button-primary" value="<?php current_user_can('publish_posts') ? esc_attr_e('Publish') : esc_attr_e('Submit for Review'); ?>" />
				<img class="waiting" src="<?php echo esc_url( admin_url( 'images/nxtspin_light.gif' ) ); ?>" alt="" />
			</span>
			<br class="clear" />
		</p>

	</form>

<?php
	if ( $drafts )
		nxt_dashboard_recent_drafts( $drafts );
}

function nxt_dashboard_recent_drafts( $drafts = false ) {
	if ( !$drafts ) {
		$drafts_query = new nxt_Query( array(
			'post_type' => 'post',
			'post_status' => 'draft',
			'author' => $GLOBALS['current_user']->ID,
			'posts_per_page' => 5,
			'orderby' => 'modified',
			'order' => 'DESC'
		) );
		$drafts =& $drafts_query->posts;
	}

	if ( $drafts && is_array( $drafts ) ) {
		$list = array();
		foreach ( $drafts as $draft ) {
			$url = get_edit_post_link( $draft->ID );
			$title = _draft_or_post_title( $draft->ID );
			$item = "<h4><a href='$url' title='" . sprintf( __( 'Edit &#8220;%s&#8221;' ), esc_attr( $title ) ) . "'>" . esc_html($title) . "</a> <abbr title='" . get_the_time(__('Y/m/d g:i:s A'), $draft) . "'>" . get_the_time( get_option( 'date_format' ), $draft ) . '</abbr></h4>';
			if ( $the_content = preg_split( '#\s#', strip_tags( $draft->post_content ), 11, PREG_SPLIT_NO_EMPTY ) )
				$item .= '<p>' . join( ' ', array_slice( $the_content, 0, 10 ) ) . ( 10 < count( $the_content ) ? '&hellip;' : '' ) . '</p>';
			$list[] = $item;
		}
?>
	<ul>
		<li><?php echo join( "</li>\n<li>", $list ); ?></li>
	</ul>
	<p class="textright"><a href="edit.php?post_status=draft" ><?php _e('View all'); ?></a></p>
<?php
	} else {
		_e('There are no drafts at the moment');
	}
}

/**
 * Display recent comments dashboard widget content.
 *
 * @since 2.5.0
 */
function nxt_dashboard_recent_comments() {
	global $nxtdb;

	if ( current_user_can('edit_posts') )
		$allowed_states = array('0', '1');
	else
		$allowed_states = array('1');

	// Select all comment types and filter out spam later for better query performance.
	$comments = array();
	$start = 0;

	$widgets = get_option( 'dashboard_widget_options' );
	$total_items = isset( $widgets['dashboard_recent_comments'] ) && isset( $widgets['dashboard_recent_comments']['items'] )
		? absint( $widgets['dashboard_recent_comments']['items'] ) : 5;

	while ( count( $comments ) < $total_items && $possible = $nxtdb->get_results( "SELECT * FROM $nxtdb->comments c LEFT JOIN $nxtdb->posts p ON c.comment_post_ID = p.ID WHERE p.post_status != 'trash' ORDER BY c.comment_date_gmt DESC LIMIT $start, 50" ) ) {

		foreach ( $possible as $comment ) {
			if ( count( $comments ) >= $total_items )
				break;
			if ( in_array( $comment->comment_approved, $allowed_states ) && current_user_can( 'read_post', $comment->comment_post_ID ) )
				$comments[] = $comment;
		}

		$start = $start + 50;
	}

	if ( $comments ) :
?>

		<div id="the-comment-list" class="list:comment">
<?php
		foreach ( $comments as $comment )
			_nxt_dashboard_recent_comments_row( $comment );
?>

		</div>

<?php
		if ( current_user_can('edit_posts') ) { ?>
			<?php _get_list_table('nxt_Comments_List_Table')->views(); ?>
<?php	}

		nxt_comment_reply( -1, false, 'dashboard', false );
		nxt_comment_trashnotice();

	else :
?>

	<p><?php _e( 'No comments yet.' ); ?></p>

<?php
	endif; // $comments;
}

function _nxt_dashboard_recent_comments_row( &$comment, $show_date = true ) {
	$GLOBALS['comment'] =& $comment;

	$comment_post_url = get_edit_post_link( $comment->comment_post_ID );
	$comment_post_title = strip_tags(get_the_title( $comment->comment_post_ID ));
	$comment_post_link = "<a href='$comment_post_url'>$comment_post_title</a>";
	$comment_link = '<a class="comment-link" href="' . esc_url(get_comment_link()) . '">#</a>';

	$actions_string = '';
	if ( current_user_can( 'edit_comment', $comment->comment_ID ) ) {
		// preorder it: Approve | Reply | Edit | Spam | Trash
		$actions = array(
			'approve' => '', 'unapprove' => '',
			'reply' => '',
			'edit' => '',
			'spam' => '',
			'trash' => '', 'delete' => ''
		);

		$del_nonce = esc_html( '_nxtnonce=' . nxt_create_nonce( "delete-comment_$comment->comment_ID" ) );
		$approve_nonce = esc_html( '_nxtnonce=' . nxt_create_nonce( "approve-comment_$comment->comment_ID" ) );

		$approve_url = esc_url( "comment.php?action=approvecomment&p=$comment->comment_post_ID&c=$comment->comment_ID&$approve_nonce" );
		$unapprove_url = esc_url( "comment.php?action=unapprovecomment&p=$comment->comment_post_ID&c=$comment->comment_ID&$approve_nonce" );
		$spam_url = esc_url( "comment.php?action=spamcomment&p=$comment->comment_post_ID&c=$comment->comment_ID&$del_nonce" );
		$trash_url = esc_url( "comment.php?action=trashcomment&p=$comment->comment_post_ID&c=$comment->comment_ID&$del_nonce" );
		$delete_url = esc_url( "comment.php?action=deletecomment&p=$comment->comment_post_ID&c=$comment->comment_ID&$del_nonce" );

		$actions['approve'] = "<a href='$approve_url' class='dim:the-comment-list:comment-$comment->comment_ID:unapproved:e7e7d3:e7e7d3:new=approved vim-a' title='" . esc_attr__( 'Approve this comment' ) . "'>" . __( 'Approve' ) . '</a>';
		$actions['unapprove'] = "<a href='$unapprove_url' class='dim:the-comment-list:comment-$comment->comment_ID:unapproved:e7e7d3:e7e7d3:new=unapproved vim-u' title='" . esc_attr__( 'Unapprove this comment' ) . "'>" . __( 'Unapprove' ) . '</a>';
		$actions['edit'] = "<a href='comment.php?action=editcomment&amp;c={$comment->comment_ID}' title='" . esc_attr__('Edit comment') . "'>". __('Edit') . '</a>';
		$actions['reply'] = '<a onclick="commentReply.open(\''.$comment->comment_ID.'\',\''.$comment->comment_post_ID.'\');return false;" class="vim-r hide-if-no-js" title="'.esc_attr__('Reply to this comment').'" href="#">' . __('Reply') . '</a>';
		$actions['spam'] = "<a href='$spam_url' class='delete:the-comment-list:comment-$comment->comment_ID::spam=1 vim-s vim-destructive' title='" . esc_attr__( 'Mark this comment as spam' ) . "'>" . /* translators: mark as spam link */  _x( 'Spam', 'verb' ) . '</a>';
		if ( !EMPTY_TRASH_DAYS )
			$actions['delete'] = "<a href='$delete_url' class='delete:the-comment-list:comment-$comment->comment_ID::trash=1 delete vim-d vim-destructive'>" . __('Delete Permanently') . '</a>';
		else
			$actions['trash'] = "<a href='$trash_url' class='delete:the-comment-list:comment-$comment->comment_ID::trash=1 delete vim-d vim-destructive' title='" . esc_attr__( 'Move this comment to the trash' ) . "'>" . _x('Trash', 'verb') . '</a>';

		$actions = apply_filters( 'comment_row_actions', array_filter($actions), $comment );

		$i = 0;
		foreach ( $actions as $action => $link ) {
			++$i;
			( ( ('approve' == $action || 'unapprove' == $action) && 2 === $i ) || 1 === $i ) ? $sep = '' : $sep = ' | ';

			// Reply and quickedit need a hide-if-no-js span
			if ( 'reply' == $action || 'quickedit' == $action )
				$action .= ' hide-if-no-js';

			$actions_string .= "<span class='$action'>$sep$link</span>";
		}
	}

?>

		<div id="comment-<?php echo $comment->comment_ID; ?>" <?php comment_class( array( 'comment-item', nxt_get_comment_status($comment->comment_ID) ) ); ?>>
			<?php if ( !$comment->comment_type || 'comment' == $comment->comment_type ) : ?>

			<?php echo get_avatar( $comment, 50 ); ?>

			<div class="dashboard-comment-wrap">
			<h4 class="comment-meta">
				<?php printf( /* translators: 1: comment author, 2: post link, 3: notification if the comment is pending */__( 'From %1$s on %2$s%3$s' ),
					'<cite class="comment-author">' . get_comment_author_link() . '</cite>', $comment_post_link.' '.$comment_link, ' <span class="approve">' . __( '[Pending]' ) . '</span>' ); ?>
			</h4>

			<?php
			else :
				switch ( $comment->comment_type ) :
				case 'pingback' :
					$type = __( 'Pingback' );
					break;
				case 'trackback' :
					$type = __( 'Trackback' );
					break;
				default :
					$type = ucwords( $comment->comment_type );
				endswitch;
				$type = esc_html( $type );
			?>
			<div class="dashboard-comment-wrap">
			<?php /* translators: %1$s is type of comment, %2$s is link to the post */ ?>
			<h4 class="comment-meta"><?php printf( _x( '%1$s on %2$s', 'dashboard' ), "<strong>$type</strong>", $comment_post_link." ".$comment_link ); ?></h4>
			<p class="comment-author"><?php comment_author_link(); ?></p>

			<?php endif; // comment_type ?>
			<blockquote><p><?php comment_excerpt(); ?></p></blockquote>
			<p class="row-actions"><?php echo $actions_string; ?></p>
			</div>
		</div>
<?php
}

/**
 * The recent comments dashboard widget control.
 *
 * @since 3.0.0
 */
function nxt_dashboard_recent_comments_control() {
	if ( !$widget_options = get_option( 'dashboard_widget_options' ) )
		$widget_options = array();

	if ( !isset($widget_options['dashboard_recent_comments']) )
		$widget_options['dashboard_recent_comments'] = array();

	if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['widget-recent-comments']) ) {
		$number = absint( $_POST['widget-recent-comments']['items'] );
		$widget_options['dashboard_recent_comments']['items'] = $number;
		update_option( 'dashboard_widget_options', $widget_options );
	}

	$number = isset( $widget_options['dashboard_recent_comments']['items'] ) ? (int) $widget_options['dashboard_recent_comments']['items'] : '';

	echo '<p><label for="comments-number">' . __('Number of comments to show:') . '</label>';
	echo '<input id="comments-number" name="widget-recent-comments[items]" type="text" value="' . $number . '" size="3" /></p>';
}

function nxt_dashboard_incoming_links() {
	nxt_dashboard_cached_rss_widget( 'dashboard_incoming_links', 'nxt_dashboard_incoming_links_output' );
}

/**
 * Display incoming links dashboard widget content.
 *
 * @since 2.5.0
 */
function nxt_dashboard_incoming_links_output() {
	$widgets = get_option( 'dashboard_widget_options' );
	@extract( @$widgets['dashboard_incoming_links'], EXTR_SKIP );
	$rss = fetch_feed( $url );

	if ( is_nxt_error($rss) ) {
		if ( is_admin() || current_user_can('manage_options') ) {
			echo '<p>';
			printf(__('<strong>RSS Error</strong>: %s'), $rss->get_error_message());
			echo '</p>';
		}
		return;
	}

	if ( !$rss->get_item_quantity() ) {
		echo '<p>' . __('This dashboard widget queries <a href="http://blogsearch.google.com/">Google Blog Search</a> so that when another blog links to your site it will show up here. It has found no incoming links&hellip; yet. It&#8217;s okay &#8212; there is no rush.') . "</p>\n";
		$rss->__destruct();
		unset($rss);
		return;
	}

	echo "<ul>\n";

	if ( !isset($items) )
		$items = 10;

	foreach ( $rss->get_items(0, $items) as $item ) {
		$publisher = '';
		$site_link = '';
		$link = '';
		$content = '';
		$date = '';
		$link = esc_url( strip_tags( $item->get_link() ) );

		$author = $item->get_author();
		if ( $author ) {
			$site_link = esc_url( strip_tags( $author->get_link() ) );

			if ( !$publisher = esc_html( strip_tags( $author->get_name() ) ) )
				$publisher = __( 'Somebody' );
		} else {
		  $publisher = __( 'Somebody' );
		}
		if ( $site_link )
			$publisher = "<a href='$site_link'>$publisher</a>";
		else
			$publisher = "<strong>$publisher</strong>";

		$content = $item->get_content();
		$content = nxt_html_excerpt($content, 50) . ' ...';

		if ( $link )
			/* translators: incoming links feed, %1$s is other person, %3$s is content */
			$text = __( '%1$s linked here <a href="%2$s">saying</a>, "%3$s"' );
		else
			/* translators: incoming links feed, %1$s is other person, %3$s is content */
			$text = __( '%1$s linked here saying, "%3$s"' );

		if ( !empty($show_date) ) {
			if ( !empty($show_author) || !empty($show_summary) )
				/* translators: incoming links feed, %4$s is the date */
				$text .= ' ' . __( 'on %4$s' );
			$date = esc_html( strip_tags( $item->get_date() ) );
			$date = strtotime( $date );
			$date = gmdate( get_option( 'date_format' ), $date );
		}

		echo "\t<li>" . sprintf( $text, $publisher, $link, $content, $date ) . "</li>\n";
	}

	echo "</ul>\n";
	$rss->__destruct();
	unset($rss);
}

function nxt_dashboard_incoming_links_control() {
	nxt_dashboard_rss_control( 'dashboard_incoming_links', array( 'title' => false, 'show_summary' => false, 'show_author' => false ) );
}

function nxt_dashboard_primary() {
	nxt_dashboard_cached_rss_widget( 'dashboard_primary', 'nxt_dashboard_rss_output' );
}

function nxt_dashboard_primary_control() {
	nxt_dashboard_rss_control( 'dashboard_primary' );
}

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.5.0
 *
 * @param string $widget_id
 */
function nxt_dashboard_rss_output( $widget_id ) {
	$widgets = get_option( 'dashboard_widget_options' );
	echo '<div class="rss-widget">';
	nxt_widget_rss_output( $widgets[$widget_id] );
	echo "</div>";
}

function nxt_dashboard_secondary() {
	nxt_dashboard_cached_rss_widget( 'dashboard_secondary', 'nxt_dashboard_secondary_output' );
}

function nxt_dashboard_secondary_control() {
	nxt_dashboard_rss_control( 'dashboard_secondary' );
}

/**
 * Display secondary dashboard RSS widget feed.
 *
 * @since 2.5.0
 *
 * @return unknown
 */
function nxt_dashboard_secondary_output() {
	$widgets = get_option( 'dashboard_widget_options' );
	@extract( @$widgets['dashboard_secondary'], EXTR_SKIP );
	$rss = @fetch_feed( $url );

	if ( is_nxt_error($rss) ) {
		if ( is_admin() || current_user_can('manage_options') ) {
			echo '<div class="rss-widget"><p>';
			printf(__('<strong>RSS Error</strong>: %s'), $rss->get_error_message());
			echo '</p></div>';
		}
	} elseif ( !$rss->get_item_quantity() ) {
		$rss->__destruct();
		unset($rss);
		return false;
	} else {
		echo '<div class="rss-widget">';
		nxt_widget_rss_output( $rss, $widgets['dashboard_secondary'] );
		echo '</div>';
		$rss->__destruct();
		unset($rss);
	}
}

function nxt_dashboard_plugins() {
	nxt_dashboard_cached_rss_widget( 'dashboard_plugins', 'nxt_dashboard_plugins_output', array(
		'http://nxtclass.org/extend/plugins/rss/browse/popular/',
		'http://nxtclass.org/extend/plugins/rss/browse/new/',
		'http://nxtclass.org/extend/plugins/rss/browse/updated/'
	) );
}

/**
 * Display plugins most popular, newest plugins, and recently updated widget text.
 *
 * @since 2.5.0
 */
function nxt_dashboard_plugins_output() {
	$popular = fetch_feed( 'http://nxtclass.org/extend/plugins/rss/browse/popular/' );
	$new     = fetch_feed( 'http://nxtclass.org/extend/plugins/rss/browse/new/' );
	$updated = fetch_feed( 'http://nxtclass.org/extend/plugins/rss/browse/updated/' );

	if ( false === $plugin_slugs = get_transient( 'plugin_slugs' ) ) {
		$plugin_slugs = array_keys( get_plugins() );
		set_transient( 'plugin_slugs', $plugin_slugs, 86400 );
	}

	foreach ( array( 'popular' => __('Most Popular'), 'new' => __('Newest Plugins'), 'updated' => __('Recently Updated') ) as $feed => $label ) {
		if ( is_nxt_error($$feed) || !$$feed->get_item_quantity() )
			continue;

		$items = $$feed->get_items(0, 5);

		// Pick a random, non-installed plugin
		while ( true ) {
			// Abort this foreach loop iteration if there's no plugins left of this type
			if ( 0 == count($items) )
				continue 2;

			$item_key = array_rand($items);
			$item = $items[$item_key];

			list($link, $frag) = explode( '#', $item->get_link() );

			$link = esc_url($link);
			if ( preg_match( '|/([^/]+?)/?$|', $link, $matches ) )
				$slug = $matches[1];
			else {
				unset( $items[$item_key] );
				continue;
			}

			// Is this random plugin's slug already installed? If so, try again.
			reset( $plugin_slugs );
			foreach ( $plugin_slugs as $plugin_slug ) {
				if ( $slug == substr( $plugin_slug, 0, strlen( $slug ) ) ) {
					unset( $items[$item_key] );
					continue 2;
				}
			}

			// If we get to this point, then the random plugin isn't installed and we can stop the while().
			break;
		}

		// Eliminate some common badly formed plugin descriptions
		while ( ( null !== $item_key = array_rand($items) ) && false !== strpos( $items[$item_key]->get_description(), 'Plugin Name:' ) )
			unset($items[$item_key]);

		if ( !isset($items[$item_key]) )
			continue;

		// current bbPress feed item titles are: user on "topic title"
		if ( preg_match( '/&quot;(.*)&quot;/s', $item->get_title(), $matches ) )
			$title = $matches[1];
		else // but let's make it forward compatible if things change
			$title = $item->get_title();
		$title = esc_html( $title );

		$description = esc_html( strip_tags(@html_entity_decode($item->get_description(), ENT_QUOTES, get_option('blog_charset'))) );

		$ilink = nxt_nonce_url('plugin-install.php?tab=plugin-information&plugin=' . $slug, 'install-plugin_' . $slug) .
							'&amp;TB_iframe=true&amp;width=600&amp;height=800';

		echo "<h4>$label</h4>\n";
		echo "<h5><a href='$link'>$title</a></h5>&nbsp;<span>(<a href='$ilink' class='thickbox' title='$title'>" . __( 'Install' ) . "</a>)</span>\n";
		echo "<p>$description</p>\n";

		$$feed->__destruct();
		unset($$feed);
	}
}

/**
 * Checks to see if all of the feed url in $check_urls are cached.
 *
 * If $check_urls is empty, look for the rss feed url found in the dashboard
 * widget options of $widget_id. If cached, call $callback, a function that
 * echoes out output for this widget. If not cache, echo a "Loading..." stub
 * which is later replaced by AJAX call (see top of /nxt-admin/index.php)
 *
 * @since 2.5.0
 *
 * @param string $widget_id
 * @param callback $callback
 * @param array $check_urls RSS feeds
 * @return bool False on failure. True on success.
 */
function nxt_dashboard_cached_rss_widget( $widget_id, $callback, $check_urls = array() ) {
	$loading = '<p class="widget-loading hide-if-no-js">' . __( 'Loading&#8230;' ) . '</p><p class="hide-if-js">' . __( 'This widget requires JavaScript.' ) . '</p>';
	$doing_ajax = ( defined('DOING_AJAX') && DOING_AJAX );

	if ( empty($check_urls) ) {
		$widgets = get_option( 'dashboard_widget_options' );
		if ( empty($widgets[$widget_id]['url']) && ! $doing_ajax ) {
			echo $loading;
			return false;
		}
		$check_urls = array( $widgets[$widget_id]['url'] );
	}

	$cache_key = 'dash_' . md5( $widget_id );
	if ( false !== ( $output = get_transient( $cache_key ) ) ) {
		echo $output;
		return true;
	}

	if ( ! $doing_ajax ) {
		echo $loading;
		return false;
	}

	if ( $callback && is_callable( $callback ) ) {
		$args = array_slice( func_get_args(), 2 );
		array_unshift( $args, $widget_id );
		ob_start();
		call_user_func_array( $callback, $args );
		set_transient( $cache_key, ob_get_flush(), 43200); // Default lifetime in cache of 12 hours (same as the feeds)
	}

	return true;
}

/* Dashboard Widgets Controls */

// Calls widget_control callback
/**
 * Calls widget control callback.
 *
 * @since 2.5.0
 *
 * @param int $widget_control_id Registered Widget ID.
 */
function nxt_dashboard_trigger_widget_control( $widget_control_id = false ) {
	global $nxt_dashboard_control_callbacks;

	if ( is_scalar($widget_control_id) && $widget_control_id && isset($nxt_dashboard_control_callbacks[$widget_control_id]) && is_callable($nxt_dashboard_control_callbacks[$widget_control_id]) ) {
		call_user_func( $nxt_dashboard_control_callbacks[$widget_control_id], '', array( 'id' => $widget_control_id, 'callback' => $nxt_dashboard_control_callbacks[$widget_control_id] ) );
	}
}

/**
 * The RSS dashboard widget control.
 *
 * Sets up $args to be used as input to nxt_widget_rss_form(). Handles POST data
 * from RSS-type widgets.
 *
 * @since 2.5.0
 *
 * @param string $widget_id
 * @param array $form_inputs
 */
function nxt_dashboard_rss_control( $widget_id, $form_inputs = array() ) {
	if ( !$widget_options = get_option( 'dashboard_widget_options' ) )
		$widget_options = array();

	if ( !isset($widget_options[$widget_id]) )
		$widget_options[$widget_id] = array();

	$number = 1; // Hack to use nxt_widget_rss_form()
	$widget_options[$widget_id]['number'] = $number;

	if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['widget-rss'][$number]) ) {
		$_POST['widget-rss'][$number] = stripslashes_deep( $_POST['widget-rss'][$number] );
		$widget_options[$widget_id] = nxt_widget_rss_process( $_POST['widget-rss'][$number] );
		// title is optional.  If black, fill it if possible
		if ( !$widget_options[$widget_id]['title'] && isset($_POST['widget-rss'][$number]['title']) ) {
			$rss = fetch_feed($widget_options[$widget_id]['url']);
			if ( is_nxt_error($rss) ) {
				$widget_options[$widget_id]['title'] = htmlentities(__('Unknown Feed'));
			} else {
				$widget_options[$widget_id]['title'] = htmlentities(strip_tags($rss->get_title()));
				$rss->__destruct();
				unset($rss);
			}
		}
		update_option( 'dashboard_widget_options', $widget_options );
		$cache_key = 'dash_' . md5( $widget_id );
		delete_transient( $cache_key );
	}

	nxt_widget_rss_form( $widget_options[$widget_id], $form_inputs );
}

// Display File upload quota on dashboard
function nxt_dashboard_quota() {
	if ( !is_multisite() || !current_user_can('upload_files') || get_site_option( 'upload_space_check_disabled' ) )
		return true;

	$quota = get_space_allowed();
	$used = get_dirsize( BLOGUPLOADDIR ) / 1024 / 1024;

	if ( $used > $quota )
		$percentused = '100';
	else
		$percentused = ( $used / $quota ) * 100;
	$used_color = ( $percentused >= 70 ) ? ' spam' : '';
	$used = round( $used, 2 );
	$percentused = number_format( $percentused );

	?>
	<p class="sub musub"><?php _e( 'Storage Space' ); ?></p>
	<div class="table table_content musubtable">
	<table>
		<tr class="first">
			<td class="first b b-posts"><?php printf( __( '<a href="%1$s" title="Manage Uploads" class="musublink">%2$sMB</a>' ), esc_url( admin_url( 'upload.php' ) ), $quota ); ?></td>
			<td class="t posts"><?php _e( 'Space Allowed' ); ?></td>
		</tr>
	</table>
	</div>
	<div class="table table_discussion musubtable">
	<table>
		<tr class="first">
			<td class="b b-comments"><?php printf( __( '<a href="%1$s" title="Manage Uploads" class="musublink">%2$sMB (%3$s%%)</a>' ), esc_url( admin_url( 'upload.php' ) ), $used, $percentused ); ?></td>
			<td class="last t comments<?php echo $used_color;?>"><?php _e( 'Space Used' );?></td>
		</tr>
	</table>
	</div>
	<br class="clear" />
	<?php
}
add_action( 'activity_box_end', 'nxt_dashboard_quota' );

// Display Browser Nag Meta Box
function nxt_dashboard_browser_nag() {
	$notice = '';
	$response = nxt_check_browser_version();

	if ( $response ) {
		if ( $response['insecure'] ) {
			$msg = sprintf( __( "It looks like you're using an insecure version of <a href='%s'>%s</a>. Using an outdated browser makes your computer unsafe. For the best NXTClass experience, please update your browser." ), esc_attr( $response['update_url'] ), esc_html( $response['name'] ) );
		} else {
			$msg = sprintf( __( "It looks like you're using an old version of <a href='%s'>%s</a>. For the best NXTClass experience, please update your browser." ), esc_attr( $response['update_url'] ), esc_html( $response['name'] ) );
		}

		$browser_nag_class = '';
		if ( !empty( $response['img_src'] ) ) {
			$img_src = ( is_ssl() && ! empty( $response['img_src_ssl'] ) )? $response['img_src_ssl'] : $response['img_src'];

			$notice .= '<div class="alignright browser-icon"><a href="' . esc_attr($response['update_url']) . '"><img src="' . esc_attr( $img_src ) . '" alt="" /></a></div>';
			$browser_nag_class = ' has-browser-icon';
		}
		$notice .= "<p class='browser-update-nag{$browser_nag_class}'>{$msg}</p>";
		$notice .= '<p>' . sprintf( __( '<a href="%1$s" class="update-browser-link">Update %2$s</a> or learn how to <a href="%3$s" class="browse-happy-link">browse happy</a>' ), esc_attr( $response['update_url'] ), esc_html( $response['name'] ), 'http://browsehappy.com/' ) . '</p>';
		$notice .= '<p class="hide-if-no-js"><a href="" class="dismiss">' . __( 'Dismiss' ) . '</a></p>';
		$notice .= '<div class="clear"></div>';
	}

	echo apply_filters( 'browse-happy-notice', $notice, $response );
}

function dashboard_browser_nag_class( $classes ) {
	$response = nxt_check_browser_version();

	if ( $response && $response['insecure'] )
		$classes[] = 'browser-insecure';

	return $classes;
}

/**
 * Check if the user needs a browser update
 *
 * @since 3.2.0
 *
 * @return array|bool False on failure, array of browser data on success.
 */
function nxt_check_browser_version() {
	if ( empty( $_SERVER['HTTP_USER_AGENT'] ) )
		return false;

	$key = md5( $_SERVER['HTTP_USER_AGENT'] );

	if ( false === ($response = get_site_transient('browser_' . $key) ) ) {
		global $nxt_version;

		$options = array(
			'body'			=> array( 'useragent' => $_SERVER['HTTP_USER_AGENT'] ),
			'user-agent'	=> 'NXTClass/' . $nxt_version . '; ' . get_bloginfo( 'url' )
		);

		$response = nxt_remote_post( 'http://api.nxtclass.org/core/browse-happy/1.0/', $options );

		if ( is_nxt_error( $response ) || 200 != nxt_remote_retrieve_response_code( $response ) )
			return false;

		/**
		 * Response should be an array with:
		 *  'name' - string - A user friendly browser name
		 *  'version' - string - The most recent version of the browser
		 *  'current_version' - string - The version of the browser the user is using
		 *  'upgrade' - boolean - Whether the browser needs an upgrade
		 *  'insecure' - boolean - Whether the browser is deemed insecure
		 *  'upgrade_url' - string - The url to visit to upgrade
		 *  'img_src' - string - An image representing the browser
		 *  'img_src_ssl' - string - An image (over SSL) representing the browser
		 */
		$response = unserialize( nxt_remote_retrieve_body( $response ) );

		if ( ! $response )
			return false;

		set_site_transient( 'browser_' . $key, $response, 604800 ); // cache for 1 week
	}

	return $response;
}

/**
 * Empty function usable by plugins to output empty dashboard widget (to be populated later by JS).
 */
function nxt_dashboard_empty() {}

/**
 * Displays a welcome panel to introduce users to NXTClass.
 *
 * @since 3.3
 */
function nxt_welcome_panel() {
	global $nxt_version;

	if ( ! current_user_can( 'edit_theme_options' ) )
		return;

	$classes = 'welcome-panel';

	$option = get_user_meta( get_current_user_id(), 'show_welcome_panel', true );
	// 0 = hide, 1 = toggled to show or single site creator, 2 = multisite site owner
	$hide = 0 == $option || ( 2 == $option && nxt_get_current_user()->user_email != get_option( 'admin_email' ) );
	if ( $hide )
		$classes .= ' hidden';

	list( $display_version ) = explode( '-', $nxt_version );
	?>
	<div id="welcome-panel" class="<?php echo esc_attr( $classes ); ?>">
	<?php nxt_nonce_field( 'welcome-panel-nonce', 'welcomepanelnonce', false ); ?>
	<a class="welcome-panel-close" href="<?php echo esc_url( admin_url( '?welcome=0' ) ); ?>"><?php _e('Dismiss'); ?></a>
	<div class="nxt-badge"><?php printf( __( 'Version %s' ), $display_version ); ?></div>

	<div class="welcome-panel-content">
	<h3><?php _e( 'Welcome to your new NXTClass site! ' ); ?></h3>
	<p class="about-description"><?php _e( 'If you need help getting started, check out our documentation on <a href="http://codex.nxtclass.org/First_Steps_With_NXTClass">First Steps with NXTClass</a>. If you&#8217;d rather dive right in, here are a few things most people do first when they set up a new NXTClass site. If you need help, use the Help tabs in the upper right corner to get information on how to use your current screen and where to go for more assistance.' ); ?></p>
	<div class="welcome-panel-column-container">
	<div class="welcome-panel-column">
		<h4><span class="icon16 icon-settings"></span> <?php _e( 'Basic Settings' ); ?></h4>
		<p><?php _e( 'Here are a few easy things you can do to get your feet wet. Make sure to click Save on each Settings screen.' ); ?></p>
		<ul>
		<li><?php echo sprintf(	__( '<a href="%s">Choose your privacy setting</a>' ), esc_url( admin_url('options-privacy.php') ) ); ?></li>
		<li><?php echo sprintf( __( '<a href="%s">Select your tagline and time zone</a>' ), esc_url( admin_url('options-general.php') ) ); ?></li>
		<li><?php echo sprintf( __( '<a href="%s">Turn comments on or off</a>' ), esc_url( admin_url('options-discussion.php') ) ); ?></li>
		<li><?php echo sprintf( __( '<a href="%s">Fill in your profile</a>' ), esc_url( admin_url('profile.php') ) ); ?></li>
		</ul>
	</div>
	<div class="welcome-panel-column">
		<h4><span class="icon16 icon-page"></span> <?php _e( 'Add Real Content' ); ?></h4>
		<p><?php _e( 'Check out the sample page & post editors to see how it all works, then delete the default content and write your own!' ); ?></p>
		<ul>
		<li><?php echo sprintf( __( 'View the <a href="%1$s">sample page</a> and <a href="%2$s">post</a>' ), esc_url( get_permalink( 2 ) ), esc_url( get_permalink( 1 ) ) ); ?></li>
		<li><?php echo sprintf( __( 'Delete the <a href="%1$s">sample page</a> and <a href="%2$s">post</a>' ), esc_url( admin_url('edit.php?post_type=page') ), esc_url( admin_url('edit.php') ) ); ?></li>
		<li><?php echo sprintf( __( '<a href="%s">Create an About Me page</a>' ), esc_url( admin_url('edit.php?post_type=page') ) ); ?></li>
		<li><?php echo sprintf( __( '<a href="%s">Write your first post</a>' ), esc_url( admin_url('post-new.php') ) ); ?></li>
		</ul>
	</div>
	<div class="welcome-panel-column welcome-panel-last">
		<h4><span class="icon16 icon-appearance"></span> <?php _e( 'Customize Your Site' ); ?></h4>
		<?php
		$ct = current_theme_info();
		if ( empty ( $ct->stylesheet_dir ) ) :
			echo '<p>';
			printf( __( '<a href="%s">Install a theme</a> to get started customizing your site.' ), esc_url( admin_url( 'themes.php' ) ) );
			echo '</p>';
		else:
			$customize_links = array();
			if ( 'twentyeleven' == $ct->stylesheet )
				$customize_links[] = sprintf( __( '<a href="%s">Choose light or dark</a>' ), esc_url( admin_url( 'themes.php?page=theme_options' ) ) );

			if ( current_theme_supports( 'custom-background' ) )
				$customize_links[] = sprintf( __( '<a href="%s">Set a background color</a>' ), esc_url( admin_url( 'themes.php?page=custom-background' ) ) );

			if ( current_theme_supports( 'custom-header' ) )
				$customize_links[] = sprintf( __( '<a href="%s">Select a new header image</a>' ), esc_url( admin_url( 'themes.php?page=custom-header' ) ) );

			if ( current_theme_supports( 'widgets' ) )
				$customize_links[] = sprintf( __( '<a href="%s">Add some widgets</a>' ), esc_url( admin_url( 'widgets.php' ) ) );

			if ( ! empty( $customize_links ) ) {
				echo '<p>';
				printf( __( 'Use the current theme &mdash; %1$s &mdash; or <a href="%2$s">choose a new one</a>. If you stick with %3$s, here are a few ways to make your site look unique.' ), $ct->title, esc_url( admin_url( 'themes.php' ) ), $ct->title );
				echo '</p>';
			?>
			<ul>
				<?php foreach ( $customize_links as $customize_link ) : ?>
				<li><?php echo $customize_link ?></li>
				<?php endforeach; ?>
			</ul>
			<?php
			} else {
				echo '<p>';
				printf( __( 'Use the current theme &mdash; %1$s &mdash; or <a href="%2$s">choose a new one</a>.' ), $ct->title, esc_url( admin_url( 'themes.php' ) ) );
				echo '</p>';
			}
		endif; ?>
	</div>
	</div>
	<p class="welcome-panel-dismiss"><?php printf( __( 'Already know what you&#8217;re doing? <a href="%s">Dismiss this message</a>.' ), esc_url( admin_url( '?welcome=0' ) ) ); ?></p>
	</div>
	</div>
	<?php
}

?>
