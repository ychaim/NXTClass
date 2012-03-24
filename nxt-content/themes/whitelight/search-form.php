<?php
/**
 * Search Form Template
 *
 * This template is a customised search form.
 *
 * @package lokFramework
 * @subpackage Template
 */
?>
<div class="search_main fix">
    <form method="get" class="searchform" action="<?php echo home_url( '/' ); ?>" >
        <input type="text" class="field s" name="s" value="<?php esc_attr_e( 'Search...', 'lokthemes' ); ?>" onfocus="if ( this.value == '<?php esc_attr_e( 'Search...', 'lokthemes' ); ?>' ) { this.value = ''; }" onblur="if ( this.value == '' ) { this.value = '<?php esc_attr_e( 'Search...', 'lokthemes' ); ?>'; }" />
        <input type="image" src="<?php echo get_template_directory_uri(); ?>/images/ico-search.png" class="search-submit" name="submit" alt="Submit" />
    </form>    
</div><!--/.search_main-->