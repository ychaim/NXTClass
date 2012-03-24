<?php

/*-----------------------------------------------------------------------------------

TABLE OF CONTENTS

- Exclude categories from displaying on the "Blog" page template.
- Exclude categories from displaying on the homepage.
- Register WP Menus
- Page navigation
- Post Meta
- Portfolio Meta
- Subscribe & Connect
- Comment Form Fields
- Comment Form Settings
- Archive Description
- lokPagination markup
- CPT Portfolio
- CPT Info Boxes
- CPT Slides
- Google maps (for contact template)

-----------------------------------------------------------------------------------*/

/*-----------------------------------------------------------------------------------*/
/* Exclude categories from displaying on the "Blog" page template.
/*-----------------------------------------------------------------------------------*/

// Exclude categories on the "Blog" page template.
add_filter( 'lok_blog_template_query_args', 'lok_exclude_categories_blogtemplate' );

function lok_exclude_categories_blogtemplate ( $args ) {

	if ( ! function_exists( 'lok_prepare_category_ids_from_option' ) ) { return $args; }

	$excluded_cats = array();

	// Process the category data and convert all categories to IDs.
	$excluded_cats = lok_prepare_category_ids_from_option( 'lok_exclude_cats_blog' );

	// Homepage logic.
	if ( count( $excluded_cats ) > 0 ) {

		// Setup the categories as a string, because "category__not_in" doesn't seem to work
		// when using query_posts().

		foreach ( $excluded_cats as $k => $v ) { $excluded_cats[$k] = '-' . $v; }
		$cats = join( ',', $excluded_cats );

		$args['cat'] = $cats;
	}

	return $args;

} // End lok_exclude_categories_blogtemplate()

/*-----------------------------------------------------------------------------------*/
/* Exclude categories from displaying on the homepage.
/*-----------------------------------------------------------------------------------*/

// Exclude categories on the homepage.
add_filter( 'pre_get_posts', 'lok_exclude_categories_homepage' );

function lok_exclude_categories_homepage ( $query ) {

	if ( ! function_exists( 'lok_prepare_category_ids_from_option' ) ) { return $query; }

	$excluded_cats = array();

	// Process the category data and convert all categories to IDs.
	$excluded_cats = lok_prepare_category_ids_from_option( 'lok_exclude_cats_home' );

	// Homepage logic.
	if ( is_home() && ( count( $excluded_cats ) > 0 ) ) {
		$query->set( 'category__not_in', $excluded_cats );
	}

	$query->parse_query();

	return $query;

} // End lok_exclude_categories_homepage()

/*-----------------------------------------------------------------------------------*/
/* Register WP Menus */
/*-----------------------------------------------------------------------------------*/

if ( function_exists( 'nxt_nav_menu') ) {
	add_theme_support( 'nav-menus' );
	register_nav_menus( array( 'primary-menu' => __( 'Primary Menu', 'lokthemes' ) ) );
	register_nav_menus( array( 'top-menu' => __( 'Top Menu', 'lokthemes' ) ) );
	register_nav_menus( array( 'footer-menu' => __( 'Footer Menu', 'lokthemes' ) ) );
}


/*-----------------------------------------------------------------------------------*/
/* Page navigation */
/*-----------------------------------------------------------------------------------*/

if (!function_exists( 'lok_pagenav')) {
	function lok_pagenav() {

		global $lok_options;

		// If the user has set the option to use simple paging links, display those. By default, display the pagination.
		if ( array_key_exists( 'lok_pagination_type', $lok_options ) && $lok_options[ 'lok_pagination_type' ] == 'simple' ) {
			if ( get_next_posts_link() || get_previous_posts_link() ) {
		?>
            <nav class="nav-entries fix">
                <?php next_posts_link( '<span class="nav-prev fl">'. __( '<span class="meta-nav">&larr;</span> Older posts', 'lokthemes' ) . '</span>' ); ?>
                <?php previous_posts_link( '<span class="nav-next fr">'. __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'lokthemes' ) . '</span>' ); ?>
            </nav>
		<?php
			}
		} else {
			lok_pagination();

		} // End IF Statement

	} // End lok_pagenav()
} // End IF Statement


/*-----------------------------------------------------------------------------------*/
/* Post Meta */
/*-----------------------------------------------------------------------------------*/

if (!function_exists( 'lok_post_meta')) {
	function lok_post_meta( ) {
?>
<aside class="post-meta">
	<ul>
		<li class="post-date">
			<?php the_time( get_option( 'date_format' ) ); ?>
		</li>
		<li class="post-author">
			<?php the_author_posts_link(); ?>
		</li>
		<li class="post-comments">
			<?php comments_popup_link( __( 'Leave a comment', 'lokthemes' ), __( '1 Comment', 'lokthemes' ), __( '% Comments', 'lokthemes' ) ); ?>
		</li>
		<?php edit_post_link( __( '{ Edit }', 'lokthemes' ), '<li class="edit">', '</li>' ); ?>
	</ul>
</aside>
<?php
	}
}

/*-----------------------------------------------------------------------------------*/
/* Portfolio Meta */
/*-----------------------------------------------------------------------------------*/

if (!function_exists( 'lok_portfolio_meta')) {
	function lok_portfolio_meta( ) {
?>
<aside class="portfolio-meta">
	<ul>
		<li class="portfolio-date">
			<?php the_time( get_option( 'date_format' ) ); ?>
		</li>
		<li class="portfolio-comments">
			<?php comments_popup_link( __( 'Leave a comment', 'lokthemes' ), __( '1 Comment', 'lokthemes' ), __( '% Comments', 'lokthemes' ) ); ?>
		</li>
		<li><?php edit_post_link( __( '{ Edit }', 'lokthemes' ), '<li class="edit">', '</li>' ); ?></li>
	</ul>
</aside>
<?php
	}
}



/*-----------------------------------------------------------------------------------*/
/* Subscribe / Connect */
/*-----------------------------------------------------------------------------------*/

if (!function_exists( 'lok_subscribe_connect')) {
	function lok_subscribe_connect($widget = 'false', $title = '', $form = '', $social = '') {

		//Setup default variables, overriding them if the "Theme Options" have been saved.
		$settings = array(
						'connect' => 'false', 
						'connect_title' => __('Subscribe' , 'lokthemes'), 
						'connect_related' => 'true', 
						'connect_content' => __( 'Subscribe to our e-mail newsletter to receive updates.', 'lokthemes' ),
						'connect_newsletter_id' => '', 
						'connect_mailchimp_list_url' => '',
						'feed_url' => '',
						'connect_rss' => '',
						'connect_twitter' => '',
						'connect_facebook' => '',
						'connect_youtube' => '',
						'connect_flickr' => '',
						'connect_linkedin' => '',
						'connect_delicious' => '',
						'connect_rss' => '',
						'connect_googleplus' => ''
						);
		$settings = lok_get_dynamic_values( $settings );

		// Setup title
		if ( $widget != 'true' )
			$title = $settings[ 'connect_title' ];

		// Setup related post (not in widget)
		$related_posts = '';
		if ( $settings[ 'connect_related' ] == "true" AND $widget != "true" )
			$related_posts = do_shortcode( '[related_posts limit="5"]' );

?>
	<?php if ( $settings[ 'connect' ] == "true" OR $widget == 'true' ) : ?>
	<aside id="connect" class="fix">
		<h3><?php if ( $title ) echo apply_filters( 'widget_title', $title ); else _e('Subscribe','lokthemes'); ?></h3>

		<div <?php if ( $related_posts != '' ) echo 'class="col-left"'; ?>>
			<p><?php if ($settings[ 'connect_content' ] != '') echo stripslashes($settings[ 'connect_content' ]); ?></p>

			<?php if ( $settings[ 'connect_newsletter_id' ] != "" AND $form != 'on' ) : ?>
			<form class="newsletter-form<?php if ( $related_posts == '' ) echo ' fl'; ?>" action="http://feedburner.google.com/fb/a/mailverify" method="post" target="popupwindow" onsubmit="window.open( 'http://feedburner.google.com/fb/a/mailverify?uri=<?php echo $settings[ 'connect_newsletter_id' ]; ?>', 'popupwindow', 'scrollbars=yes,width=550,height=520' );return true">
				<input class="email" type="text" name="email" value="<?php esc_attr_e( 'E-mail', 'lokthemes' ); ?>" onfocus="if (this.value == '<?php _e( 'E-mail', 'lokthemes' ); ?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php _e( 'E-mail', 'lokthemes' ); ?>';}" />
				<input type="hidden" value="<?php echo $settings[ 'connect_newsletter_id' ]; ?>" name="uri"/>
				<input type="hidden" value="<?php bloginfo( 'name' ); ?>" name="title"/>
				<input type="hidden" name="loc" value="en_US"/>
				<input class="submit" type="submit" name="submit" value="<?php _e( 'Submit', 'lokthemes' ); ?>" />
			</form>
			<?php endif; ?>

			<?php if ( $settings['connect_mailchimp_list_url'] != "" AND $form != 'on' AND $settings['connect_newsletter_id'] == "" ) : ?>
			<!-- Begin MailChimp Signup Form -->
			<div id="mc_embed_signup">
				<form class="newsletter-form<?php if ( $related_posts == '' ) echo ' fl'; ?>" action="<?php echo $settings['connect_mailchimp_list_url']; ?>" method="post" target="popupwindow" onsubmit="window.open('<?php echo $settings['connect_mailchimp_list_url']; ?>', 'popupwindow', 'scrollbars=yes,width=650,height=520');return true">
					<input type="text" name="EMAIL" class="required email" value="<?php _e('E-mail','lokthemes'); ?>"  id="mce-EMAIL" onfocus="if (this.value == '<?php _e('E-mail','lokthemes'); ?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php _e('E-mail','lokthemes'); ?>';}">
					<input type="submit" value="<?php _e('Submit', 'lokthemes'); ?>" name="subscribe" id="mc-embedded-subscribe" class="btn submit button">
				</form>
			</div>
			<!--End mc_embed_signup-->
			<?php endif; ?>

			<?php if ( $social != 'on' ) : ?>
			<div class="social<?php if ( $related_posts == '' AND $settings['connect_newsletter_id' ] != "" ) echo ' fr'; ?>">
		   		<?php if ( $settings['connect_rss' ] == "true" ) { ?>
		   		<a href="<?php if ( $settings['feed_url'] ) { echo esc_url( $settings['feed_url'] ); } else { echo get_bloginfo_rss('rss2_url'); } ?>" class="subscribe" title="RSS"></a>

		   		<?php } if ( $settings['connect_twitter' ] != "" ) { ?>
		   		<a href="<?php echo esc_url( $settings['connect_twitter'] ); ?>" class="twitter" title="Twitter"></a>

		   		<?php } if ( $settings['connect_facebook' ] != "" ) { ?>
		   		<a href="<?php echo esc_url( $settings['connect_facebook'] ); ?>" class="facebook" title="Facebook"></a>

		   		<?php } if ( $settings['connect_youtube' ] != "" ) { ?>
		   		<a href="<?php echo esc_url( $settings['connect_youtube'] ); ?>" class="youtube" title="YouTube"></a>

		   		<?php } if ( $settings['connect_flickr' ] != "" ) { ?>
		   		<a href="<?php echo esc_url( $settings['connect_flickr'] ); ?>" class="flickr" title="Flickr"></a>

		   		<?php } if ( $settings['connect_linkedin' ] != "" ) { ?>
		   		<a href="<?php echo esc_url( $settings['connect_linkedin'] ); ?>" class="linkedin" title="LinkedIn"></a>

		   		<?php } if ( $settings['connect_delicious' ] != "" ) { ?>
		   		<a href="<?php echo esc_url( $settings['connect_delicious'] ); ?>" class="delicious" title="Delicious"></a>

		   		<?php } if ( $settings['connect_googleplus' ] != "" ) { ?>
		   		<a href="<?php echo esc_url( $settings['connect_googleplus'] ); ?>" class="googleplus" title="Google+"></a>

				<?php } ?>
			</div>
			<?php endif; ?>

		</div><!-- col-left -->

		<?php if ( $settings['connect_related' ] == "true" AND $related_posts != '' ) : ?>
		<div class="related-posts col-right">
			<h4><?php _e( 'Related Posts:', 'lokthemes' ); ?></h4>
			<?php echo $related_posts; ?>
		</div><!-- col-right -->
		<?php nxt_reset_query(); endif; ?>

	</aside>
	<?php endif; ?>
<?php
	}
}

/*-----------------------------------------------------------------------------------*/
/* Comment Form Fields */
/*-----------------------------------------------------------------------------------*/

	add_filter( 'comment_form_default_fields', 'lok_comment_form_fields' );

	if ( ! function_exists( 'lok_comment_form_fields' ) ) {
		function lok_comment_form_fields ( $fields ) {

			$commenter = nxt_get_current_commenter();

			$required_text = ' <span class="required">(' . __( 'Required', 'lokthemes' ) . ')</span>';

			$req = get_option( 'require_name_email' );
			$aria_req = ( $req ? " aria-required='true'" : '' );
			$fields =  array(
				'author' => '<p class="comment-form-author">' .
							'<input id="author" class="txt" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' />' .
							'<label for="author">' . __( 'Name' ) . ( $req ? $required_text : '' ) . '</label> ' .
							'</p>',
				'email'  => '<p class="comment-form-email">' .
				            '<input id="email" class="txt" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' />' .
				            '<label for="email">' . __( 'Email' ) . ( $req ? $required_text : '' ) . '</label> ' .
				            '</p>',
				'url'    => '<p class="comment-form-url">' .
				            '<input id="url" class="txt" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" />' .
				            '<label for="url">' . __( 'Website' ) . '</label>' .
				            '</p>',
			);

			return $fields;

		} // End lok_comment_form_fields()
	}

/*-----------------------------------------------------------------------------------*/
/* Comment Form Settings */
/*-----------------------------------------------------------------------------------*/

	add_filter( 'comment_form_defaults', 'lok_comment_form_settings' );

	if ( ! function_exists( 'lok_comment_form_settings' ) ) {
		function lok_comment_form_settings ( $settings ) {

			$settings['comment_notes_before'] = '';
			$settings['comment_notes_after'] = '';
			$settings['label_submit'] = __( 'Submit Comment', 'lokthemes' );
			$settings['cancel_reply_link'] = __( 'Click here to cancel reply.', 'lokthemes' );

			return $settings;

		} // End lok_comment_form_settings()
	}

	/*-----------------------------------------------------------------------------------*/
	/* Misc back compat */
	/*-----------------------------------------------------------------------------------*/

	// array_fill_keys doesn't exist in PHP < 5.2
	// Can remove this after PHP <  5.2 support is dropped
	if ( !function_exists( 'array_fill_keys' ) ) {
		function array_fill_keys( $keys, $value ) {
			return array_combine( $keys, array_fill( 0, count( $keys ), $value ) );
		}
	}

/*-----------------------------------------------------------------------------------*/
/**
 * lok_archive_description()
 *
 * Display a description, if available, for the archive being viewed (category, tag, other taxonomy).
 *
 * @since V1.0.0
 * @uses do_atomic(), get_queried_object(), term_description()
 * @echo string
 * @filter lok_archive_description
 */

if ( ! function_exists( 'lok_archive_description' ) ) {
	function lok_archive_description ( $echo = true ) {
		do_action( 'lok_archive_description' );
		
		// Archive Description, if one is available.
		$term_obj = get_queried_object();
		if ( isset($term_obj->term_id) ) {
			$description = term_description( $term_obj->term_id, $term_obj->taxonomy );
		} else {
			$description = '';
		}
		
		if ( $description != '' ) {
			// Allow child themes/plugins to filter here ( 1: text in DIV and paragraph, 2: term object )
			$description = apply_filters( 'lok_archive_description', '<div class="archive-description">' . $description . '</div><!--/.archive-description-->', $term_obj );
		}
		
		if ( $echo != true ) { return $description; }
		
		echo $description;
	} // End lok_archive_description()
}

/*-----------------------------------------------------------------------------------*/
/* lokPagination Markup */
/*-----------------------------------------------------------------------------------*/

add_filter( 'lok_pagination_args', 'lok_pagination_html5_markup', 2 );

function lok_pagination_html5_markup ( $args ) {
	$args['before'] = '<nav class="pagination lok-pagination">';
	$args['after'] = '</nav>';
	
	return $args;
} // End lok_pagination_html5_markup()


/*-----------------------------------------------------------------------------------*/
/* Custom Post Type - Portfolio Item (Portfolio Component) */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'lok_add_portfolio' ) ) {
	function lok_add_portfolio() {
	
		global $lok_options;
	
		// "Portfolio Item" Custom Post Type
		$labels = array(
			'name' => _x( 'Portfolio', 'post type general name', 'lokthemes' ),
			'singular_name' => _x( 'Portfolio Item', 'post type singular name', 'lokthemes' ),
			'add_new' => _x( 'Add New', 'slide', 'lokthemes' ),
			'add_new_item' => __( 'Add New Portfolio Item', 'lokthemes' ),
			'edit_item' => __( 'Edit Portfolio Item', 'lokthemes' ),
			'new_item' => __( 'New Portfolio Item', 'lokthemes' ),
			'view_item' => __( 'View Portfolio Item', 'lokthemes' ),
			'search_items' => __( 'Search Portfolio Items', 'lokthemes' ),
			'not_found' =>  __( 'No portfolio items found', 'lokthemes' ),
			'not_found_in_trash' => __( 'No portfolio items found in Trash', 'lokthemes' ), 
			'parent_item_colon' => ''
		);
		
		$portfolioitems_rewrite = get_option( 'lok_portfolioitems_rewrite' );
 		if( empty( $portfolioitems_rewrite ) ) { $portfolioitems_rewrite = 'portfolio-items'; }
		
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true, 
			'query_var' => true,
			'rewrite' => array( 'slug' => $portfolioitems_rewrite ),
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_icon' => get_template_directory_uri() .'/includes/images/portfolio.png',
			'menu_position' => null, 
			'has_archive' => true, 
			'taxonomies' => array( 'portfolio-gallery' ), 
			'supports' => array( 'title','editor','thumbnail', 'page-attributes', 'comments')
		);
		
		if ( isset( $lok_options['lok_portfolio_excludesearch'] ) && ( $lok_options['lok_portfolio_excludesearch'] == 'true' ) ) {
			$args['exclude_from_search'] = true;
		}
		
		register_post_type( 'portfolio', $args );
		
		// "Portfolio Galleries" Custom Taxonomy
		$labels = array(
			'name' => _x( 'Portfolio Galleries', 'taxonomy general name', 'lokthemes' ),
			'singular_name' => _x( 'Portfolio Gallery', 'taxonomy singular name', 'lokthemes' ),
			'search_items' =>  __( 'Search Portfolio Galleries', 'lokthemes' ),
			'all_items' => __( 'All Portfolio Galleries', 'lokthemes' ),
			'parent_item' => __( 'Parent Portfolio Gallery', 'lokthemes' ),
			'parent_item_colon' => __( 'Parent Portfolio Gallery:', 'lokthemes' ),
			'edit_item' => __( 'Edit Portfolio Gallery', 'lokthemes' ), 
			'update_item' => __( 'Update Portfolio Gallery', 'lokthemes' ),
			'add_new_item' => __( 'Add New Portfolio Gallery', 'lokthemes' ),
			'new_item_name' => __( 'New Portfolio Gallery Name', 'lokthemes' ),
			'menu_name' => __( 'Portfolio Galleries', 'lokthemes' )
		); 	
		
		$args = array(
			'hierarchical' => true,
			'labels' => $labels,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'portfolio-gallery' )
		);
		
		register_taxonomy( 'portfolio-gallery', array( 'portfolio' ), $args );
	}
	
	add_action( 'init', 'lok_add_portfolio' );
}

/*-----------------------------------------------------------------------------------*/
/* lok Portfolio Navigation */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'lok_portfolio_navigation' ) ) {
	function lok_portfolio_navigation ( $galleries, $settings = array(), $toggle_pagination = false ) {

		// Sanity check.
		if ( ! is_array( $galleries ) || ( count( $galleries ) <= 0 ) ) { return; }
		
		global $lok_options;
		
		$defaults = array(
						'id' => 'port-tags', 
						'label' => '', 
						'display_all' => true, 
						'current' => 'all'
						 );
		
		$settings = nxt_parse_args( $settings, $defaults );
					 
		$settings = apply_filters( 'lok_portfolio_navigation_args', $settings );
		
		// Prepare the anchor tags of the various gallery items.
		$gallery_anchors = '';
		foreach ( $galleries as $g ) {
			$current_class = '';

			if ( $settings['current'] == $g->term_id ) {
				$current_class = ' current';
			}
			
			$permalink = '#' . $g->slug;
			if ( $toggle_pagination == true ) {
				$permalink = get_term_link( $g, 'portfolio-gallery' );
			}
			
			$gallery_anchors .= '<a href="' . $permalink . '" rel="' . $g->slug . '" class="navigation-slug-' . $g->slug . ' navigation-id-' . $g->term_id . $current_class . '">' . $g->name . '</a>' . "\n";
		}
		
		$html = '<div id="' . $settings['id'] . '" class="port-tags">' . "\n";
			$html .= '<div class="fl">' . "\n";
				$html .= '<span class="port-cat">' . "\n";
				
				// Display label, if one is set.
				if ( $settings['label'] != '' ) { $html .= $settings['label'] . ' '; }
				
				// Display "All", if set to "true".
				if ( $settings['display_all'] == 'all' ) {
					$all_permalink = '#';
					if ( $toggle_pagination == true ) {
						$all_permalink = get_post_type_archive_link( 'portfolio' );
					}
					
					$all_current = '';
					if ( $settings['current'] == 'all' ) {
						$all_current = ' class="current"';
					}
					$html .= '<a href="' . $all_permalink . '" rel="all"' . $all_current . '>' . __( 'All','lokthemes' ) . '</a> ';
				}
				
				// Add the gallery anchors in.
				$html .= $gallery_anchors;
				
				$html .= '</span>' . "\n";
			$html .= '</div><!--/.fl-->' . "\n";
			$html .= '<div class="fix"></div>' . "\n";
		$html .= '</div><!--/#' . $settings['id'] . ' .port-tags-->' . "\n";
		
		
		$html = apply_filters( 'lok_portfolio_navigation', $html );
		
		echo $html;
	
	} // End lok_portfolio_navigation()
}

/*-----------------------------------------------------------------------------------*/
/* lok Portfolio Item Settings */
/* @uses lok_portfolio_image_dimensions() */
/*-----------------------------------------------------------------------------------*/
 
if ( !function_exists( 'lok_portfolio_item_settings' ) ) {
	function lok_portfolio_item_settings ( $id ) {
		
		global $lok_options;
		
		// Sanity check.
		if ( ! is_numeric( $id ) ) { return; }
		
		$website_layout = 'two-col-left';
		$website_width = '900px';
		
		if ( isset( $lok_options['lok_layout'] ) ) { $website_layout = $lok_options['lok_layout']; }
		if ( isset( $lok_options['lok_layout_width'] ) ) { $website_width = $lok_options['lok_layout_width']; }
		
		$dimensions = lok_portfolio_image_dimensions( $website_layout, $website_width );
		
		$width = $dimensions['width'];
		$height = $dimensions['height'];
		
		
		$settings = array(
							'large' => '', 
							'caption' => '', 
							'rel' => '', 
							'gallery' => array(), 
							'css_classes' => 'group post portfolio-img', 
							'embed' => '', 
							'testimonial' => '', 
							'testimonial_author' => '', 
							'display_url' => '', 
							'width' => $width, 
							'height' => $height
						 );
		
		$meta = get_post_custom( $id );
		
		// Check if there is a gallery in post.
		// lok_get_post_images is offset by 1 by default. Setting to offset by 0 to show all images.
		
		$large = '';
		if ( isset( $meta['portfolio-image'][0] ) )
			$large = $meta['portfolio-image'][0];
			
		$caption = '';
			    
		$rel = 'rel="lightbox['. $id .']"';

		// Check if there are more than 1 image
    	$gallery = lok_get_post_images( '0' );
    	
	    // If we only have one image, disable the gallery functionality.
	    if ( isset( $gallery ) && is_array( $gallery ) && ( count( $gallery ) <= 1 ) ) {
			$rel = 'rel="lightbox"';
	    }
	    
	    // Check for a post thumbnail, if support for it is enabled.
	    if ( isset( $lok_options['lok_post_image_support'] ) && ( $lok_options['lok_post_image_support'] == 'true' ) && current_theme_supports( 'post-thumbnails' ) ) {
	    	$image_id = get_post_thumbnail_id( $id );
	    	if ( intval( $image_id ) > 0 ) {
	    		$large_data = nxt_get_attachment_image_src( $image_id, 'large' );
	    		if ( is_array( $large_data ) ) {
	    			$large = $large_data[0];
	    		}
	    	}
	    }
	    
	    // See if lightbox-url custom field has a value
	    if ( isset( $meta['lightbox-url'] ) && ( $meta['lightbox-url'][0] != '' ) ) {
	    	$large = $meta['lightbox-url'][0];
	    }
	    		
		// Create CSS classes string.
		$css = '';
		$galleries = array();
		$terms = get_the_terms( $id, 'portfolio-gallery' );
		if ( is_array( $terms ) && ( count( $terms ) > 0 ) ) { foreach ( $terms as $t ) { $galleries[] = $t->slug; } }				
		$css = join( ' ', $galleries );
		
		// If on the single item screen, check for a video.
		if ( is_singular() ) { $settings['embed'] = lok_embed( 'width=540' ); }
		
		// Add testimonial information.
		if ( isset( $meta['testimonial'] ) && ( $meta['testimonial'][0] != '' ) ) {
			$settings['testimonial'] = $meta['testimonial'][0];
		}
		
		if ( isset( $meta['testimonial_author'] ) && ( $meta['testimonial_author'][0] != '' ) ) {
			$settings['testimonial_author'] = $meta['testimonial_author'][0];
		}
		
		// Look for a custom display URL of the portfolio item (used if it's a website, for example)
		if ( isset( $meta['url'] ) && ( $meta['url'][0] != '' ) ) {
			$settings['display_url'] = $meta['url'][0];
		}
		
		// Assign the values we have to our array.
		$settings['large'] = $large;
		$settings['caption'] = $caption;
		$settings['rel'] = $rel;
		if (isset( $gallery )) { $settings['gallery'] = $gallery; } else { $settings['gallery'] = array(); }
		$settings['css_classes'] .= ' ' . $css;
				
		// Check for a custom description.
		$description = get_post_meta( $id, 'lightbox-description', true );
		if ( $description != ''  ) { $settings['caption'] = $description; }
		
		// Allow child themes/plugins to filter these settings.
		$settings = apply_filters( 'lok_portfolio_item_settings', $settings, $id );
		
		return $settings;
	
	} // End lok_portfolio_item_settings()
}

/*-----------------------------------------------------------------------------------*/
/* lok Portfolio, show portfolio galleries in portfolio item breadcrumbs */
/* Modify lok_breadcrumbs() Arguments Specific to this Theme */
/*-----------------------------------------------------------------------------------*/

add_filter( 'lok_breadcrumbs_args', 'lok_portfolio_filter_breadcrumbs_args', 10 );

if ( !function_exists( 'lok_portfolio_filter_breadcrumbs_args' ) ) {
	function lok_portfolio_filter_breadcrumbs_args( $args ) {
	
		$args['singular_portfolio_taxonomy'] = 'portfolio-gallery';
	
		return $args;
	
	} // End lok_portfolio_filter_breadcrumbs_args()
}

/*-----------------------------------------------------------------------------------*/
/* lok Portfolio, get image dimensions based on layout and website width settings. */
/*-----------------------------------------------------------------------------------*/

if ( !function_exists( 'lok_portfolio_image_dimensions' ) ) {
	function lok_portfolio_image_dimensions ( $layout = 'one-col', $width = '960' ) {
		
		$dimensions = array( 'width' => 575, 'height' => 0, 'thumb_width' => 175, 'thumb_height' => 175 );
		
		// Allow child themes/plugins to filter these dimensions.
		$dimensinos = apply_filters( 'lok_portfolio_gallery_dimensions', $dimensions );
	
		return $dimensions;
	
	} // End lok_post_gallery_dimensions()
}

/*-----------------------------------------------------------------------------------*/
/* Get Post image attachments */
/*-----------------------------------------------------------------------------------*/
/* 
Description:

This function will get all the attached post images that have been uploaded via the 
WP post image upload and return them in an array. 

*/
if ( !function_exists( 'lok_get_post_images' ) ) {
	function lok_get_post_images( $offset = 1, $size = 'large' ) {
		
		// Arguments
		$repeat = 100; 				// Number of maximum attachments to get 
		$photo_size = 'large';		// The WP "size" to use for the large image
	
		global $post;
	
		$output = array();
	
		$id = get_the_id();
		$attachments = get_children( array(
		'post_parent' => $id,
		'numberposts' => $repeat,
		'post_type' => 'attachment',
		'post_mime_type' => 'image',
		'order' => 'ASC', 
		'orderby' => 'menu_order date' )
		);
		if ( !empty($attachments) ) :
			$output = array();
			$count = 0;
			foreach ( $attachments as $att_id => $attachment ) {
				$count++;  
				if ($count <= $offset) continue;
				$url = nxt_get_attachment_image_src($att_id, $photo_size, true);	
					$output[] = array( 'url' => $url[0], 'caption' => $attachment->post_excerpt, 'id' => $att_id );
			}  
		endif; 
		return $output;
	} // End lok_get_post_images()
}

/**
 * lok_portfolio_add_post_classes function.
 * 
 * @access public
 * @param array $classes
 * @return array $classes
 */

add_filter( 'post_class', 'lok_portfolio_add_post_classes', 10 );
 
function lok_portfolio_add_post_classes ( $classes ) {
	if ( in_array( 'portfolio', $classes ) ) {
		global $post;
		
		$terms = get_the_terms( $post->ID, 'portfolio-gallery' );

		if ( $terms && ! is_nxt_error( $terms ) ) {
			foreach ( $terms as $t ) {
				$classes[] = $t->slug;
			}
		}
		
		if ( ! is_singular() ) {
			foreach ( $classes as $k => $v ) {
				if ( in_array( $v, array( 'hentry', 'portfolio' ) ) ) {
					unset( $classes[$k] );
				}
			}
		}
	}
	return $classes;
} // End lok_portfolio_add_post_classes()


/*-----------------------------------------------------------------------------------*/
/* Custom Post Type - Info Boxes */
/*-----------------------------------------------------------------------------------*/

add_action('init', 'lok_add_features');
function lok_add_features() 
{
  $labels = array(
    'name' => _x('Features', 'post type general name', 'lokthemes'),
    'singular_name' => _x('Feature', 'post type singular name', 'lokthemes'),
    'add_new' => _x('Add New', 'features', 'lokthemes'),
    'add_new_item' => __('Add New Feature', 'lokthemes'),
    'edit_item' => __('Edit Feature', 'lokthemes'),
    'new_item' => __('New Feature', 'lokthemes'),
    'view_item' => __('View Feature', 'lokthemes'),
    'search_items' => __('Search Features', 'lokthemes'),
    'not_found' =>  __('No Features found', 'lokthemes'),
    'not_found_in_trash' => __('No Features found in Trash', 'lokthemes'), 
    'parent_item_colon' => ''
  );
  
  $features_rewrite = get_option('lok_features_rewrite');
  if(empty($features_rewrite)) $features_rewrite = 'features';
  
  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'query_var' => true,
    'rewrite' => array('slug'=> $features_rewrite),
    'capability_type' => 'post',
    'hierarchical' => false,
    'menu_icon' => get_template_directory_uri() .'/includes/images/box.png',
    'menu_position' => null,
    'has_archive' => true,
    'supports' => array('title','editor',/*'author','thumbnail','excerpt','comments'*/)
  ); 
  register_post_type('features',$args);
}

/*-----------------------------------------------------------------------------------*/
/* Custom Post Type - Slides */
/*-----------------------------------------------------------------------------------*/

add_action('init', 'lok_add_slides');
function lok_add_slides() 
{
  $labels = array(
    'name' => _x('Slides', 'post type general name', 'lokthemes', 'lokthemes'),
    'singular_name' => _x('Slide', 'post type singular name', 'lokthemes'),
    'add_new' => _x('Add New', 'slide', 'lokthemes'),
    'add_new_item' => __('Add New Slide', 'lokthemes'),
    'edit_item' => __('Edit Slide', 'lokthemes'),
    'new_item' => __('New Slide', 'lokthemes'),
    'view_item' => __('View Slide', 'lokthemes'),
    'search_items' => __('Search Slides', 'lokthemes'),
    'not_found' =>  __('No slides found', 'lokthemes'),
    'not_found_in_trash' => __('No slides found in Trash', 'lokthemes'), 
    'parent_item_colon' => ''
  );
  $args = array(
    'labels' => $labels,
    'public' => false,
    'publicly_queryable' => false,
    'show_ui' => true, 
    'query_var' => true,
    'rewrite' => true,
    'capability_type' => 'post',
    'hierarchical' => false,
    'menu_icon' => get_template_directory_uri() .'/includes/images/slides.png',
    'menu_position' => null,
    'supports' => array('title','editor','thumbnail', /*'author','thumbnail','excerpt','comments'*/)
  ); 
  register_post_type('slide',$args);
}

/*-----------------------------------------------------------------------------------*/
/* Google Maps */
/*-----------------------------------------------------------------------------------*/

function lok_maps_contact_output($args){

	$key = get_option('lok_maps_apikey');
	
	// No More API Key needed
	
	if ( !is_array($args) ) 
		parse_str( $args, $args );
		
	extract($args);	
		
	$map_height = get_option('lok_maps_single_height');
	$featured_w = get_option('lok_home_featured_w');
	$featured_h = get_option('lok_home_featured_h');
	$zoom = get_option('lok_maps_default_mapzoom');
	$marker_title = get_option('lok_contact_title');
	if ( $zoom == '' ) { $zoom = 6; }   
	$lang = get_option('lok_maps_directions_locale');
	$locale = '';
	if(!empty($lang)){
		$locale = ',locale :"'.$lang.'"';
	}
	$extra_params = ',{travelMode:G_TRAVEL_MODE_WALKING,avoidHighways:true '.$locale.'}';
	
	if(empty($map_height)) { $map_height = 250;}
	
	if(is_home() && !empty($featured_h) && !empty($featured_w)){
	?>
    <div id="single_map_canvas" style="width:<?php echo $featured_w; ?>px; height: <?php echo $featured_h; ?>px"></div>
    <?php } else { ?> 
    <div id="single_map_canvas" style="width:100%; height: <?php echo $map_height; ?>px"></div>
    <?php } ?>
    <script src="<?php bloginfo('template_url'); ?>/includes/js/markers.js" type="text/javascript"></script>
    <script type="text/javascript">
		jQuery(document).ready(function(){
			function initialize() {
				
				
			<?php if($streetview == 'on'){ ?>

				
			<?php } else { ?>
				
			  	<?php switch ($type) {
			  			case 'G_NORMAL_MAP':
			  				$type = 'ROADMAP';
			  				break;
			  			case 'G_SATELLITE_MAP':
			  				$type = 'SATELLITE';
			  				break;
			  			case 'G_HYBRID_MAP':
			  				$type = 'HYBRID';
			  				break;
			  			case 'G_PHYSICAL_MAP':
			  				$type = 'TERRAIN';
			  				break;
			  			default:
			  				$type = 'ROADMAP';
			  				break;
			  	} ?>
			  	
			  	var myLatlng = new google.maps.LatLng(<?php echo $geocoords; ?>);
				var myOptions = {
				  zoom: <?php echo $zoom; ?>,
				  center: myLatlng,
				  mapTypeId: google.maps.MapTypeId.<?php echo $type; ?>
				};
			  	var map = new google.maps.Map(document.getElementById("single_map_canvas"),  myOptions);
				<?php if(get_option('lok_maps_scroll') == 'true'){ ?>
			  	map.scrollwheel = false;
			  	<?php } ?>
			  	
				<?php if($mode == 'directions'){ ?>
			  	directionsPanel = document.getElementById("featured-route");
 				directions = new GDirections(map, directionsPanel);
  				directions.load("from: <?php echo $from; ?> to: <?php echo $to; ?>" <?php if($walking == 'on'){ echo $extra_params;} ?>);
			  	<?php
			 	} else { ?>
			 
			  		var point = new google.maps.LatLng(<?php echo $geocoords; ?>);
	  				var root = "<?php bloginfo('template_url'); ?>";
	  				var the_link = '<?php echo get_permalink(get_the_id()); ?>';
	  				<?php $title = str_replace(array('&#8220;','&#8221;'),'"', $marker_title); ?>
	  				<?php $title = str_replace('&#8211;','-',$title); ?>
	  				<?php $title = str_replace('&#8217;',"`",$title); ?>
	  				<?php $title = str_replace('&#038;','&',$title); ?>
	  				var the_title = '<?php echo html_entity_decode($title) ?>'; 
	  				
	  			<?php		 	
			 	if(is_page()){ 
			 		$custom = get_option('lok_cat_custom_marker_pages');
					if(!empty($custom)){
						$color = $custom;
					}
					else {
						$color = get_option('lok_cat_colors_pages');
						if (empty($color)) {
							$color = 'red';
						}
					}			 	
			 	?>
			 		var color = '<?php echo $color; ?>';
			 		createMarker(map,point,root,the_link,the_title,color);
			 	<?php } else { ?>
			 		var color = '<?php echo get_option('lok_cat_colors_pages'); ?>';
	  				createMarker(map,point,root,the_link,the_title,color);
				<?php 
				}
					if(isset($_POST['lok_maps_directions_search'])){ ?>
					
					directionsPanel = document.getElementById("featured-route");
 					directions = new GDirections(map, directionsPanel);
  					directions.load("from: <?php echo htmlspecialchars($_POST['lok_maps_directions_search']); ?> to: <?php echo $address; ?>" <?php if($walking == 'on'){ echo $extra_params;} ?>);
  					
  					
  					
					directionsDisplay = new google.maps.DirectionsRenderer();
					directionsDisplay.setMap(map);
    				directionsDisplay.setPanel(document.getElementById("featured-route"));
					
					<?php if($walking == 'on'){ ?>
					var travelmodesetting = google.maps.DirectionsTravelMode.WALKING;
					<?php } else { ?>
					var travelmodesetting = google.maps.DirectionsTravelMode.DRIVING;
					<?php } ?>
					var start = '<?php echo htmlspecialchars($_POST['lok_maps_directions_search']); ?>';
					var end = '<?php echo $address; ?>';
					var request = {
       					origin:start, 
        				destination:end,
        				travelMode: travelmodesetting
    				};
    				directionsService.route(request, function(response, status) {
      					if (status == google.maps.DirectionsStatus.OK) {
        					directionsDisplay.setDirections(response);
      					}
      				});	
      				
  					<?php } ?>			
				<?php } ?>
			<?php } ?>
			

			  }
			  function handleNoFlash(errorCode) {
				  if (errorCode == FLASH_UNAVAILABLE) {
					alert("Error: Flash doesn't appear to be supported by your browser");
					return;
				  }
				 }

			
		
		initialize();
			
		});
	jQuery(window).load(function(){
			
		var newHeight = jQuery('#featured-content').height();
		newHeight = newHeight - 5;
		if(newHeight > 300){
			jQuery('#single_map_canvas').height(newHeight);
		}
		
	});

	</script>

<?php
}

 
/*-----------------------------------------------------------------------------------*/
/* END */
/*-----------------------------------------------------------------------------------*/
?>