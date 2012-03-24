<?php
/**
 * NXTClass Export Administration API
 *
 * @package NXTClass
 * @subpackage Administration
 */

/**
 * Version number for the export format.
 *
 * Bump this when something changes that might affect compatibility.
 *
 * @since 2.5.0
 */
define( 'WXR_VERSION', '1.1' );

/**
 * Generates the WXR export file for download
 *
 * @since 2.1.0
 *
 * @param array $args Filters defining what should be included in the export
 */
function export_nxt( $args = array() ) {
	global $nxtdb, $post;

	$defaults = array( 'content' => 'all', 'author' => false, 'category' => false,
		'start_date' => false, 'end_date' => false, 'status' => false,
	);
	$args = nxt_parse_args( $args, $defaults );

	do_action( 'export_nxt' );

	$sitename = sanitize_key( get_bloginfo( 'name' ) );
	if ( ! empty($sitename) ) $sitename .= '.';
	$filename = $sitename . 'nxtclass.' . date( 'Y-m-d' ) . '.xml';

	header( 'Content-Description: File Transfer' );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );

	if ( 'all' != $args['content'] && post_type_exists( $args['content'] ) ) {
		$ptype = get_post_type_object( $args['content'] );
		if ( ! $ptype->can_export )
			$args['content'] = 'post';

		$where = $nxtdb->prepare( "{$nxtdb->posts}.post_type = %s", $args['content'] );
	} else {
		$post_types = get_post_types( array( 'can_export' => true ) );
		$esses = array_fill( 0, count($post_types), '%s' );
		$where = $nxtdb->prepare( "{$nxtdb->posts}.post_type IN (" . implode( ',', $esses ) . ')', $post_types );
	}

	if ( $args['status'] && ( 'post' == $args['content'] || 'page' == $args['content'] ) )
		$where .= $nxtdb->prepare( " AND {$nxtdb->posts}.post_status = %s", $args['status'] );
	else
		$where .= " AND {$nxtdb->posts}.post_status != 'auto-draft'";

	$join = '';
	if ( $args['category'] && 'post' == $args['content'] ) {
		if ( $term = term_exists( $args['category'], 'category' ) ) {
			$join = "INNER JOIN {$nxtdb->term_relationships} ON ({$nxtdb->posts}.ID = {$nxtdb->term_relationships}.object_id)";
			$where .= $nxtdb->prepare( " AND {$nxtdb->term_relationships}.term_taxonomy_id = %d", $term['term_taxonomy_id'] );
		}
	}

	if ( 'post' == $args['content'] || 'page' == $args['content'] ) {
		if ( $args['author'] )
			$where .= $nxtdb->prepare( " AND {$nxtdb->posts}.post_author = %d", $args['author'] );

		if ( $args['start_date'] )
			$where .= $nxtdb->prepare( " AND {$nxtdb->posts}.post_date >= %s", date( 'Y-m-d', strtotime($args['start_date']) ) );

		if ( $args['end_date'] )
			$where .= $nxtdb->prepare( " AND {$nxtdb->posts}.post_date < %s", date( 'Y-m-d', strtotime('+1 month', strtotime($args['end_date'])) ) );
	}

	// grab a snapshot of post IDs, just in case it changes during the export
	$post_ids = $nxtdb->get_col( "SELECT ID FROM {$nxtdb->posts} $join WHERE $where" );

	// get the requested terms ready, empty unless posts filtered by category or all content
	$cats = $tags = $terms = array();
	if ( isset( $term ) && $term ) {
		$cat = get_term( $term['term_id'], 'category' );
		$cats = array( $cat->term_id => $cat );
		unset( $term, $cat );
	} else if ( 'all' == $args['content'] ) {
		$categories = (array) get_categories( array( 'get' => 'all' ) );
		$tags = (array) get_tags( array( 'get' => 'all' ) );

		$custom_taxonomies = get_taxonomies( array( '_builtin' => false ) );
		$custom_terms = (array) get_terms( $custom_taxonomies, array( 'get' => 'all' ) );

		// put categories in order with no child going before its parent
		while ( $cat = array_shift( $categories ) ) {
			if ( $cat->parent == 0 || isset( $cats[$cat->parent] ) )
				$cats[$cat->term_id] = $cat;
			else
				$categories[] = $cat;
		}

		// put terms in order with no child going before its parent
		while ( $t = array_shift( $custom_terms ) ) {
			if ( $t->parent == 0 || isset( $terms[$t->parent] ) )
				$terms[$t->term_id] = $t;
			else
				$custom_terms[] = $t;
		}

		unset( $categories, $custom_taxonomies, $custom_terms );
	}

	/**
	 * Wrap given string in XML CDATA tag.
	 *
	 * @since 2.1.0
	 *
	 * @param string $str String to wrap in XML CDATA tag.
	 */
	function wxr_cdata( $str ) {
		if ( seems_utf8( $str ) == false )
			$str = utf8_encode( $str );

		// $str = ent2ncr(esc_html($str));
		$str = "<![CDATA[$str" . ( ( substr( $str, -1 ) == ']' ) ? ' ' : '' ) . ']]>';

		return $str;
	}

	/**
	 * Return the URL of the site
	 *
	 * @since 2.5.0
	 *
	 * @return string Site URL.
	 */
	function wxr_site_url() {
		// ms: the base url
		if ( is_multisite() )
			return network_home_url();
		// nxt: the blog url
		else
			return get_bloginfo_rss( 'url' );
	}

	/**
	 * Output a cat_name XML tag from a given category object
	 *
	 * @since 2.1.0
	 *
	 * @param object $category Category Object
	 */
	function wxr_cat_name( $category ) {
		if ( empty( $category->name ) )
			return;

		echo '<nxt:cat_name>' . wxr_cdata( $category->name ) . '</nxt:cat_name>';
	}

	/**
	 * Output a category_description XML tag from a given category object
	 *
	 * @since 2.1.0
	 *
	 * @param object $category Category Object
	 */
	function wxr_category_description( $category ) {
		if ( empty( $category->description ) )
			return;

		echo '<nxt:category_description>' . wxr_cdata( $category->description ) . '</nxt:category_description>';
	}

	/**
	 * Output a tag_name XML tag from a given tag object
	 *
	 * @since 2.3.0
	 *
	 * @param object $tag Tag Object
	 */
	function wxr_tag_name( $tag ) {
		if ( empty( $tag->name ) )
			return;

		echo '<nxt:tag_name>' . wxr_cdata( $tag->name ) . '</nxt:tag_name>';
	}

	/**
	 * Output a tag_description XML tag from a given tag object
	 *
	 * @since 2.3.0
	 *
	 * @param object $tag Tag Object
	 */
	function wxr_tag_description( $tag ) {
		if ( empty( $tag->description ) )
			return;

		echo '<nxt:tag_description>' . wxr_cdata( $tag->description ) . '</nxt:tag_description>';
	}

	/**
	 * Output a term_name XML tag from a given term object
	 *
	 * @since 2.9.0
	 *
	 * @param object $term Term Object
	 */
	function wxr_term_name( $term ) {
		if ( empty( $term->name ) )
			return;

		echo '<nxt:term_name>' . wxr_cdata( $term->name ) . '</nxt:term_name>';
	}

	/**
	 * Output a term_description XML tag from a given term object
	 *
	 * @since 2.9.0
	 *
	 * @param object $term Term Object
	 */
	function wxr_term_description( $term ) {
		if ( empty( $term->description ) )
			return;

		echo '<nxt:term_description>' . wxr_cdata( $term->description ) . '</nxt:term_description>';
	}

	/**
	 * Output list of authors with posts
	 *
	 * @since 3.1.0
	 */
	function wxr_authors_list() {
		global $nxtdb;

		$authors = array();
		$results = $nxtdb->get_results( "SELECT DISTINCT post_author FROM $nxtdb->posts" );
		foreach ( (array) $results as $result )
			$authors[] = get_userdata( $result->post_author );

		$authors = array_filter( $authors );

		foreach ( $authors as $author ) {
			echo "\t<nxt:author>";
			echo '<nxt:author_id>' . $author->ID . '</nxt:author_id>';
			echo '<nxt:author_login>' . $author->user_login . '</nxt:author_login>';
			echo '<nxt:author_email>' . $author->user_email . '</nxt:author_email>';
			echo '<nxt:author_display_name>' . wxr_cdata( $author->display_name ) . '</nxt:author_display_name>';
			echo '<nxt:author_first_name>' . wxr_cdata( $author->user_firstname ) . '</nxt:author_first_name>';
			echo '<nxt:author_last_name>' . wxr_cdata( $author->user_lastname ) . '</nxt:author_last_name>';
			echo "</nxt:author>\n";
		}
	}

	/**
	 * Ouput all navigation menu terms
	 *
	 * @since 3.1.0
	 */
	function wxr_nav_menu_terms() {
		$nav_menus = nxt_get_nav_menus();
		if ( empty( $nav_menus ) || ! is_array( $nav_menus ) )
			return;

		foreach ( $nav_menus as $menu ) {
			echo "\t<nxt:term><nxt:term_id>{$menu->term_id}</nxt:term_id><nxt:term_taxonomy>nav_menu</nxt:term_taxonomy><nxt:term_slug>{$menu->slug}</nxt:term_slug>";
			wxr_term_name( $menu );
			echo "</nxt:term>\n";
		}
	}

	/**
	 * Output list of taxonomy terms, in XML tag format, associated with a post
	 *
	 * @since 2.3.0
	 */
	function wxr_post_taxonomy() {
		global $post;

		$taxonomies = get_object_taxonomies( $post->post_type );
		if ( empty( $taxonomies ) )
			return;
		$terms = nxt_get_object_terms( $post->ID, $taxonomies );

		foreach ( (array) $terms as $term ) {
			echo "\t\t<category domain=\"{$term->taxonomy}\" nicename=\"{$term->slug}\">" . wxr_cdata( $term->name ) . "</category>\n";
		}
	}

	function wxr_filter_postmeta( $return_me, $meta_key ) {
		if ( '_edit_lock' == $meta_key )
			$return_me = true;
		return $return_me;
	}
	add_filter( 'wxr_export_skip_postmeta', 'wxr_filter_postmeta', 10, 2 );

	echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . "\" ?>\n";

	?>
<!-- This is a NXTClass eXtended RSS file generated by NXTClass as an export of your site. -->
<!-- It contains information about your site's posts, pages, comments, categories, and other content. -->
<!-- You may use this file to transfer that content from one site to another. -->
<!-- This file is not intended to serve as a complete backup of your site. -->

<!-- To import this information into a NXTClass site follow these steps: -->
<!-- 1. Log in to that site as an administrator. -->
<!-- 2. Go to Tools: Import in the NXTClass admin panel. -->
<!-- 3. Install the "NXTClass" importer from the list. -->
<!-- 4. Activate & Run Importer. -->
<!-- 5. Upload this file using the form provided on that page. -->
<!-- 6. You will first be asked to map the authors in this export file to users -->
<!--    on the site. For each author, you may choose to map to an -->
<!--    existing user on the site or to create a new user. -->
<!-- 7. NXTClass will then import each of the posts, pages, comments, categories, etc. -->
<!--    contained in this file into your site. -->

<?php the_generator( 'export' ); ?>
<rss version="2.0"
	xmlns:excerpt="http://nxtclass.org/export/<?php echo WXR_VERSION; ?>/excerpt/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:nxt="http://nxtclass.org/export/<?php echo WXR_VERSION; ?>/"
>

<channel>
	<title><?php bloginfo_rss( 'name' ); ?></title>
	<link><?php bloginfo_rss( 'url' ); ?></link>
	<description><?php bloginfo_rss( 'description' ); ?></description>
	<pubDate><?php echo date( 'D, d M Y H:i:s +0000' ); ?></pubDate>
	<language><?php echo get_option( 'rss_language' ); ?></language>
	<nxt:wxr_version><?php echo WXR_VERSION; ?></nxt:wxr_version>
	<nxt:base_site_url><?php echo wxr_site_url(); ?></nxt:base_site_url>
	<nxt:base_blog_url><?php bloginfo_rss( 'url' ); ?></nxt:base_blog_url>

<?php wxr_authors_list(); ?>

<?php foreach ( $cats as $c ) : ?>
	<nxt:category><nxt:term_id><?php echo $c->term_id ?></nxt:term_id><nxt:category_nicename><?php echo $c->slug; ?></nxt:category_nicename><nxt:category_parent><?php echo $c->parent ? $cats[$c->parent]->slug : ''; ?></nxt:category_parent><?php wxr_cat_name( $c ); ?><?php wxr_category_description( $c ); ?></nxt:category>
<?php endforeach; ?>
<?php foreach ( $tags as $t ) : ?>
	<nxt:tag><nxt:term_id><?php echo $t->term_id ?></nxt:term_id><nxt:tag_slug><?php echo $t->slug; ?></nxt:tag_slug><?php wxr_tag_name( $t ); ?><?php wxr_tag_description( $t ); ?></nxt:tag>
<?php endforeach; ?>
<?php foreach ( $terms as $t ) : ?>
	<nxt:term><nxt:term_id><?php echo $t->term_id ?></nxt:term_id><nxt:term_taxonomy><?php echo $t->taxonomy; ?></nxt:term_taxonomy><nxt:term_slug><?php echo $t->slug; ?></nxt:term_slug><nxt:term_parent><?php echo $t->parent ? $terms[$t->parent]->slug : ''; ?></nxt:term_parent><?php wxr_term_name( $t ); ?><?php wxr_term_description( $t ); ?></nxt:term>
<?php endforeach; ?>
<?php if ( 'all' == $args['content'] ) wxr_nav_menu_terms(); ?>

	<?php do_action( 'rss2_head' ); ?>

<?php if ( $post_ids ) {
	global $nxt_query;
	$nxt_query->in_the_loop = true; // Fake being in the loop.

	// fetch 20 posts at a time rather than loading the entire table into memory
	while ( $next_posts = array_splice( $post_ids, 0, 20 ) ) {
	$where = 'WHERE ID IN (' . join( ',', $next_posts ) . ')';
	$posts = $nxtdb->get_results( "SELECT * FROM {$nxtdb->posts} $where" );

	// Begin Loop
	foreach ( $posts as $post ) {
		setup_postdata( $post );
		$is_sticky = is_sticky( $post->ID ) ? 1 : 0;
?>
	<item>
		<title><?php echo apply_filters( 'the_title_rss', $post->post_title ); ?></title>
		<link><?php the_permalink_rss() ?></link>
		<pubDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true ), false ); ?></pubDate>
		<dc:creator><?php echo get_the_author_meta( 'login' ); ?></dc:creator>
		<guid isPermaLink="false"><?php esc_url( the_guid() ); ?></guid>
		<description></description>
		<content:encoded><?php echo wxr_cdata( apply_filters( 'the_content_export', $post->post_content ) ); ?></content:encoded>
		<excerpt:encoded><?php echo wxr_cdata( apply_filters( 'the_excerpt_export', $post->post_excerpt ) ); ?></excerpt:encoded>
		<nxt:post_id><?php echo $post->ID; ?></nxt:post_id>
		<nxt:post_date><?php echo $post->post_date; ?></nxt:post_date>
		<nxt:post_date_gmt><?php echo $post->post_date_gmt; ?></nxt:post_date_gmt>
		<nxt:comment_status><?php echo $post->comment_status; ?></nxt:comment_status>
		<nxt:ping_status><?php echo $post->ping_status; ?></nxt:ping_status>
		<nxt:post_name><?php echo $post->post_name; ?></nxt:post_name>
		<nxt:status><?php echo $post->post_status; ?></nxt:status>
		<nxt:post_parent><?php echo $post->post_parent; ?></nxt:post_parent>
		<nxt:menu_order><?php echo $post->menu_order; ?></nxt:menu_order>
		<nxt:post_type><?php echo $post->post_type; ?></nxt:post_type>
		<nxt:post_password><?php echo $post->post_password; ?></nxt:post_password>
		<nxt:is_sticky><?php echo $is_sticky; ?></nxt:is_sticky>
<?php	if ( $post->post_type == 'attachment' ) : ?>
		<nxt:attachment_url><?php echo nxt_get_attachment_url( $post->ID ); ?></nxt:attachment_url>
<?php 	endif; ?>
<?php 	wxr_post_taxonomy(); ?>
<?php	$postmeta = $nxtdb->get_results( $nxtdb->prepare( "SELECT * FROM $nxtdb->postmeta WHERE post_id = %d", $post->ID ) );
		foreach ( $postmeta as $meta ) :
			if ( apply_filters( 'wxr_export_skip_postmeta', false, $meta->meta_key, $meta ) )
				continue;
		?>
		<nxt:postmeta>
			<nxt:meta_key><?php echo $meta->meta_key; ?></nxt:meta_key>
			<nxt:meta_value><?php echo wxr_cdata( $meta->meta_value ); ?></nxt:meta_value>
		</nxt:postmeta>
<?php	endforeach; ?>
<?php	$comments = $nxtdb->get_results( $nxtdb->prepare( "SELECT * FROM $nxtdb->comments WHERE comment_post_ID = %d AND comment_approved <> 'spam'", $post->ID ) );
		foreach ( $comments as $c ) : ?>
		<nxt:comment>
			<nxt:comment_id><?php echo $c->comment_ID; ?></nxt:comment_id>
			<nxt:comment_author><?php echo wxr_cdata( $c->comment_author ); ?></nxt:comment_author>
			<nxt:comment_author_email><?php echo $c->comment_author_email; ?></nxt:comment_author_email>
			<nxt:comment_author_url><?php echo esc_url_raw( $c->comment_author_url ); ?></nxt:comment_author_url>
			<nxt:comment_author_IP><?php echo $c->comment_author_IP; ?></nxt:comment_author_IP>
			<nxt:comment_date><?php echo $c->comment_date; ?></nxt:comment_date>
			<nxt:comment_date_gmt><?php echo $c->comment_date_gmt; ?></nxt:comment_date_gmt>
			<nxt:comment_content><?php echo wxr_cdata( $c->comment_content ) ?></nxt:comment_content>
			<nxt:comment_approved><?php echo $c->comment_approved; ?></nxt:comment_approved>
			<nxt:comment_type><?php echo $c->comment_type; ?></nxt:comment_type>
			<nxt:comment_parent><?php echo $c->comment_parent; ?></nxt:comment_parent>
			<nxt:comment_user_id><?php echo $c->user_id; ?></nxt:comment_user_id>
<?php		$c_meta = $nxtdb->get_results( $nxtdb->prepare( "SELECT * FROM $nxtdb->commentmeta WHERE comment_id = %d", $c->comment_ID ) );
			foreach ( $c_meta as $meta ) : ?>
			<nxt:commentmeta>
				<nxt:meta_key><?php echo $meta->meta_key; ?></nxt:meta_key>
				<nxt:meta_value><?php echo wxr_cdata( $meta->meta_value ); ?></nxt:meta_value>
			</nxt:commentmeta>
<?php		endforeach; ?>
		</nxt:comment>
<?php	endforeach; ?>
	</item>
<?php
	}
	}
} ?>
</channel>
</rss>
<?php
}
