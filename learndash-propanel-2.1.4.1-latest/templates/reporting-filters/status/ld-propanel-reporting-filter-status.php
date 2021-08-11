<?php
if ( ( !class_exists( 'LearnDash_ProPanel_Reporting_Filter_Status' ) ) && ( class_exists( 'LearnDash_ProPanel_Filtering' ) ) ) {
	class LearnDash_ProPanel_Reporting_Filter_Status extends LearnDash_ProPanel_Filtering {

		public function __construct() {
			$this->filter_key = 'courseStatus';
			add_filter( 'ld_propanel_filtering_register_filters', array( $this, 'filter_register' ), 30 );
		}

		public function filter_post_args( $post_args_filters = array() ) {
			
			if ( ( isset( $_GET['filters'][$this->filter_key] ) ) && ( !empty( $_GET['filters'][$this->filter_key] ) ) ) {
				if ( is_string( $_GET['filters'][$this->filter_key] ) ) {
					$post_args_filters[$this->filter_key][] = esc_attr( $_GET['filters'][$this->filter_key] );
				} else if (is_array( $_GET['filters'][$this->filter_key] ) ) {
					foreach( $_GET['filters'][$this->filter_key] as $idx => $val ) {
						$post_args_filters[$this->filter_key][$idx] = esc_attr( $val );
					}
				}
			}

			return $post_args_filters;
		}


		public function filter_display() {
			//return '<select multiple class="filter-status select2" data-allow-clear="true" data-placeholder="'. esc_html__( 'All Statuses', 'ld_propanel' ) .'"><option value="">'. __( 'All Statuses', 'ld_propanel' ) .'</option><option value="not-started">'. __( 'Not Started', 'ld_propanel' ) .'</option><option value="in-progress">'. __( 'In Progress', 'ld_propanel' ) .'</option><option value="completed">'. __( 'Completed', 'ld_propanel' ) .'</option></select>';
			
			return '<select multiple="multiple" class="filter-coursestatus select2" data-ajax--cache="true" data-allow-clear="true" data-placeholder="' . 
			// translators: Course status - All Statuses
			esc_html_x( 'All Statuses', 'Course status - All Statuses', 'ld_propanel' ) .'"></select>';
			
		}

		function filter_search() {
			
			$statuses = array(
				array(
					'id' => 'not-started',
					'text' => esc_html__( 'Not Started', 'ld_propanel' ),
				),
				array(
					'id' => 'in-progress',
					'text' => esc_html__( 'In Progress', 'ld_propanel' ),
				),
				array(
					'id' => 'completed',
					'text' => esc_html__( 'Completed', 'ld_propanel' ),
				)
			);
			
			return array( 'total' => count( $statuses ), 'items' => $statuses );
			
		}
		
		// End of functions 
	}
}

add_action( 'learndash_propanel_filtering_init', function() {
	new LearnDash_ProPanel_Reporting_Filter_Status();
});
