<?php
/**
 * Template Name: Sitemap
 *
 * The sitemap page template displays a user-friendly overview
 * of the content of your website.
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
		        	
		        	<section class="entry">
			            <?php
			            	if ( have_posts() ) { the_post();
			            		the_content();
			            	}
			            ?>  
	
						<div id="sitemap-pages" class="fl" style="width:50%">												  
			            	<h3><?php _e( 'Pages', 'lokthemes' ); ?></h3>
			            	<ul>
			           	    	<?php nxt_list_pages( 'depth=0&sort_column=menu_order&title_li=' ); ?>		
			            	</ul>
		            	</div><!--/#sitemap-pages-->			
		    
						<div id="sitemap-categories" class="fl" style="width:50%">												  	    
				            <h3><?php _e( 'Categories', 'lokthemes' ); ?></h3>
				            <ul>
			    	            <?php nxt_list_categories( 'title_li=&hierarchical=0&show_count=1' ); ?>	
			        	    </ul>
		        	    </div><!--/#sitemap-categories-->
		        	    
		        	    <div class="fix"></div>
				        
				        <h3><?php _e( 'Posts per category', 'lokthemes' ); ?></h3>
				        <?php
				    
				            $cats = get_categories();
				            foreach ( $cats as $cat ) {
				    
				            query_posts( 'cat=' . $cat->cat_ID );
				
				        ?>
		        
		        			<h4><?php echo $cat->cat_name; ?></h4>
				        	<ul>	
		    	        	    <?php while ( have_posts() ) { the_post(); ?>
		        	    	    <li style="font-weight:normal !important;"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> - <?php _e( 'Comments', 'lokthemes' ); ?> (<?php echo $post->comment_count; ?>)</li>
		            		    <?php } ?>
				        	</ul>
		    
		    		    <?php
		    		    	}
		    		    	nxt_reset_query();
		    		    ?>
		    		
		    		</section><!-- /.entry -->
		    						
		        </article><!-- /.post -->                    
		                
	        </section><!-- /#main -->
	
	        <?php get_sidebar(); ?>
        
        </div>

    </div><!-- /#content -->
		
<?php get_footer(); ?>