<?php
/**
 * Template Name: Archives Page
 *
 * The archives page template displays a conprehensive archive of the current
 * content of your website on a single page. 
 *
 * @package lokFramework
 * @subpackage Template
 */
 
 global $lok_options; 
 get_header();
?> 
    <div id="content">
    
    	<div class="page col-full">
    	
    		<?php if ( isset( $lok_options['lok_breadcrumbs_show'] ) && $lok_options['lok_breadcrumbs_show'] == 'true' ) { ?>
				<section id="breadcrumbs">
					<?php lok_breadcrumbs(); ?>
				</section><!--/#breadcrumbs -->
			<?php } ?>  
    	
			<section id="main" class="col-left">
				
				<article <?php post_class(); ?>>
				    
				    <header>
				    	<h1><?php the_title(); ?></h1>
				    </header>
				    
				    <section class="entry fix">
			            <?php
			            	if ( have_posts() ) { the_post();
			            		the_content();
			            	}
			            ?>
					    <h3><?php _e( 'The Last 30 Posts', 'lokthemes' ); ?></h3>
																		  
					    <ul>											  
					        <?php
					        	query_posts( 'shonxtosts=30' );
					        	if ( have_posts() ) {
					        		while ( have_posts() ) { the_post();
					        ?>
					            <?php $nxt_query->is_home = false; ?>	  
					            <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> - <?php the_time( get_option( 'date_format' ) ); ?> - <?php echo $post->comment_count; ?> <?php _e( 'comments', 'lokthemes' ); ?></li>
					        <?php
					        		}
					        	}
					        	nxt_reset_query();
					        ?>					  
					    </ul>											  
						
						<div id="archive-categories" class="fl" style="width:50%">												  
						    <h3><?php _e( 'Categories', 'lokthemes' ); ?></h3>	  
						    <ul>											  
						        <?php nxt_list_categories( 'title_li=&hierarchical=0&show_count=1' ); ?>	
						    </ul>											  
						</div><!--/#archive-categories-->			     												  
	
						<div id="archive-dates" class="fr" style="width:50%">												  
						    <h3><?php _e( 'Monthly Archives', 'lokthemes' ); ?></h3>
																			  
						    <ul>											  
						        <?php nxt_get_archives( 'type=monthly&show_post_count=1' ); ?>	
						    </ul>
						</div><!--/#archive-dates-->	 												  
	
					</section><!-- /.entry -->
				    			
				</article><!-- /.post -->                 
	                
	        </section><!-- /#main -->
	
	        <?php get_sidebar(); ?>
		
		</div>
		
    </div><!-- /#content -->
		
<?php get_footer(); ?>