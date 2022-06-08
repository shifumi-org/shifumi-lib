<?php

defined('ABSPATH') or die('Access denied.');

/**
 * Class wdtFilterWidget is used to create filtering widget for wpDataTables
 *
 * @author Alexander Gilmanov
 *
 * @since March 2014
 */
 
 class wdtFilterWidget extends WP_Widget {
 	
 	public function __construct(){
 		parent::__construct(false, 'wpDataTables filtering widget');
 	}
 	
	function widget( $args, $instance ) {
		// Widget output
		if ( !isset($instance['title']) ) {
			$title = esc_html__( 'Filter', 'wpdatatables' );
		} else {
            $title = $instance['title'];
        }
		$title = apply_filters( 'widget_title', $title );

		echo $args['before_widget'];

        /** @noinspection PhpUnusedLocalVariableInspection */
        $title = $args['before_title'] . $title . $args['after_title'];

        ob_start();
        include(WDT_TEMPLATE_PATH . 'frontend/filter_widget.inc.php');
        $filterWidgetHtml = ob_get_contents();
        ob_end_clean();

        echo$filterWidgetHtml;
		echo $args['after_widget'];
	}

	function form( $instance ) {
		// Output admin widget options form
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = esc_html__( 'New title', 'text_domain' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	} 	
	
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}	
 	
 }

?>