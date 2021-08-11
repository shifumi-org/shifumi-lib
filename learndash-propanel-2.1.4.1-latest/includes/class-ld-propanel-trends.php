<?php
/**
 * LearnDash ProPanel Activity
 *
 * @package LearnDash_ProPanel_Trends
 * @since 2.0
 */

if ( !class_exists( 'LearnDash_ProPanel_Activity' ) ) {
	class LearnDash_ProPanel_Trends extends LearnDash_ProPanel_Widget {

		/**
		 * @var string
		 */
		protected $name;
	
		/**
		 * @var string
		 */
		protected $label;

		/**
		 * LearnDash_ProPanel_Trends constructor.
		 */
		public function __construct() {
	//		$this->name = 'trends';
	//		$this->label = esc_html__( 'ProPanel Trends', 'ld_propanel' );
	//
	//		parent::__construct();
	//		add_filter( 'learndash_propanel_template_ajax', array( $this, 'trends_template' ), 10, 2 );
		}

		public function trends_template( $output, $template ) {
			if ( 'trends' == $template ) {
				ob_start();
				include ld_propanel_get_template( 'ld-propanel-trends.php' );
				$output = ob_get_clean();
			}

			return $output;
		}
	}
}
