<?php

if ( !defined( 'POST_EXCERPT_LENGTH' ) ) {
	define( 'POST_EXCERPT_LENGTH', 100 );
}

class testimonials
{
	function __construct() {
		
		add_action( 'init', array( $this, '_register_post_type' ) );
		add_action( 'add_meta_boxes_testimonials', [ $this, '_add_meta_boxes' ] );
		add_action( 'save_post_testimonials', [ $this, '_save' ], 10, 2 );
		
		add_action( 'manage_testimonials_posts_custom_column', [ $this, '_column_data' ], 10, 2);
		
		#add_shortcode( 'questions', array( $this, '_questions' ) );
		#add_shortcode( 'answers', array( $this, '_answers' ) );
		
		add_filter( 'manage_testimonials_posts_columns', [ $this, '_set_columns' ] );
		add_filter( 'wp_insert_post_data' , [ $this, '_modify_post_title' ], '99', 1 );
	}
	
	function _register_post_type() {
		// Create (register) the custom type
		register_post_type( 'testimonials', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
			// Add all the options for this post type
			array (
				'labels' => array (
					'name' => __( 'Testimonials', 'testimonials' ), /* This is the Title of the Group */
					'singular_name' => __( 'Testimonial', 'testimonials' ), /* This is the individual type */
					'all_items' => __( 'All Testimonials', 'testimonials' ), /* the all items menu item */
					'add_new' => __( 'Add New', 'testimonials' ), /* The add new menu item */
					'add_new_item' => __( 'Add New Testimonial', 'testimonials' ), /* Add New Display Title */
					'edit' => __( 'Edit', 'testimonials' ), /* Edit Dialog */
					'edit_item' => __( 'Edit Testimonial', 'testimonials' ), /* Edit Display Title */
					'new_item' => __( 'New Testimonial', 'testimonials' ), /* New Display Title */
					'view_item' => __( 'View Testimonial', 'testimonials' ), /* View Display Title */
					'search_items' => __( 'Search Testimonials', 'testimonials' ), /* Search Custom Type Title */ 
					'not_found' =>	__( 'No testimonials found.', 'testimonials' ), /* This displays if there are no entries yet */ 
					'not_found_in_trash' => __( 'Nothing found in Trash', 'testimonials' ), /* This displays if there is nothing in the trash */
					'parent_item_colon' => ''
				), /* end of arrays */
				'description' => __( 'Testimonials.', 'testimonials' ), /* Custom Type Description */
				'public' => true,
				'publicly_queryable' => true,
				'exclude_from_search' => false,
				'show_ui' => true,
				'query_var' => true,
				'menu_position' => 8, /* this is what order you want it to appear in on the left hand side menu */ 
				'menu_icon' => 'dashicons-editor-help', /* the icon for the custom post type menu */
				'rewrite'	=> array( 'slug' => 'testimonials', 'with_front' => false ), /* you can specify its url slug */
				'has_archive' => false,//'testimonials', /* you can rename the slug here */
				'capability_type' => 'post',
				'hierarchical' => false,
				/* the next one is important, it tells what's enabled in the post editor */
				'supports' => array( 'editor', 'thumbnail', 'excerpt', 'sticky' )
			) /* end of options */
		);
		
		/* this adds your post categories to your custom post type */
		//register_taxonomy_for_object_type( 'category', 'testimonials' );
		/* this adds your post tags to your custom post type */
		register_taxonomy_for_object_type( 'post_tag', 'testimonials' );
		
	}
	
	function _add_meta_boxes( $post ) {
		add_meta_box( 'testimonials_meta_box', __( 'Meta Information', 'testimonials' ), [ $this, 'testimonials_meta_box' ], 'testimonials', 'normal', 'high' );
	}
	
	function testimonials_meta_box( $post ) {
		wp_nonce_field( basename( __FILE__ ), 'testimonials_meta_box_nonce' );
		$name = get_post_meta( $post->ID, '_testimonials_name', true );
		$date = get_post_meta( $post->ID, '_testimonials_date', true );
		require_once( __DIR__ . '/includes/metabox.php' );
	}
	
	function _save( $post_id, $post ) {
		if ( !isset( $_POST['testimonials_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['testimonials_meta_box_nonce'], basename( __FILE__ ) ) ) {
			return;
		}
		// Check if autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}
		// Check if revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( isset( $_POST['_name'] ) ) {
			update_post_meta( $post_id, '_testimonials_name', sanitize_text_field( $_POST['_name'] ) );
		}
		if ( isset( $_POST['_name'] ) ) {
			update_post_meta( $post_id, '_testimonials_date', sanitize_text_field( $_POST['_date'] ) );
		}
	}
	
	function _questions( $atts ) {
		query_posts( 'post_type=testimonials&posts_per_page=-1&order=ASC' );
		
		$o = '';
		
		if ( have_posts() ) :
		
			$o .= "<ul class=\"testimonials\">\n";
		
			while ( have_posts() ) :
			
				the_post();
		
				$o .= '<li><a href="#faq-' . get_the_id() . '">' . get_the_title() . "</a></li>\n";
			
			endwhile;
			$o .= "</ul>\n";
		endif;
		
		wp_reset_query();
		
		return $o;
	}
	
	function _answers( $atts ) {
		
		query_posts( 'post_type=testimonials&posts_per_page=-1&order=ASC' );
		
		$o = '';
		
		if ( have_posts() ) :
		
			$o .= '<div class="testimonials">' . "\n";
		
			while ( have_posts() ) :
			
				the_post();
				
				$content = get_the_content();
				
				$o .= '<article id="faq-' . get_the_ID() . '">' . "\n";
				$o .= "\t<header><h3>" . get_the_title() . "</h3></header>\n";
				if ( str_word_count( strip_tags( $content ) ) > POST_EXCERPT_LENGTH - 5 ) $o .= "\t<p>" . get_the_excerpt() . "</p>\n";
				else $o .= "\t" . self::get_the_content() . "\n";
				$o .= "\t" . '<footer><p><a href="#top">Back to top</a></p>' . "\n";
				$o .= get_the_tags( '<p class="tags"><span class="tags-title">Tags:</span> ', ', ', '</p>' );
				$o .= "\t</footer>\n";
				$o .= "</article>\n";
					
			endwhile;
			$o .= "</div>\n";
		endif;
		
		wp_reset_query();
		
		return $o;
	}
	
	function get_the_content( $more_link_text = '(more...)', $stripteaser = 0, $more_file = '' ) {
		$content = get_the_content( $more_link_text, $stripteaser, $more_file );
		$content = apply_filters( 'the_content', $content );
		$content = str_replace( ']]>', ']]&gt;', $content );
		return $content;
	}
	
	function _set_columns( $columns ) {

		$cols = [
			'cb' => $columns['cb'],
			'name' => __( 'Testimonial', 'testimonials' ),
			'tags' => $columns['tags'],
			'date' => $columns['date'],
		];

		return $cols;
	}
	
	function _column_data( $column, $post_id ) {
		switch ( $column ) {
			case 'name':
				//echo get_post_meta( $post_id, '_testimonials_name', true );
				echo wp_trim_words( get_the_content( null, false, $post_id ), 10 ), ' ', get_post_meta( $post_id, '_testimonials_name', true );
			break;
		}
	}
	
	function _modify_post_title( $data ) {
		if( $data['post_type'] == 'testimonials' ) {
			$title = time();
			$data['post_title'] =  $title ;
		}
		return $data;
	}

}

new testimonials;

