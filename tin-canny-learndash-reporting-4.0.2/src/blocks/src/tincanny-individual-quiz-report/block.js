// Import Uncanny Owl icon
import {
	UncannyOwlIconColor
} from '../components/icons';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

registerBlockType( 'tincanny/user-quiz-report', {
	title: __( 'Tin Canny Individual Quiz Report' ),

	description: __( 'Embed Tin Canny report that displays quiz results for the current user.' ),

	icon: UncannyOwlIconColor,

	category: 'uncanny-learndash-reporting',

	keywords: [
		__( 'Uncanny Owl' ),
	],

	supports: {
		html: false
	},

	attributes: {},

	edit({ className, attributes, setAttributes }){
		return (
			<div className={ className }>
				{ __( 'Tin Canny Individual Quiz Report' ) }
			</div>
		);
	},

	save({ className, attributes }){
		// We're going to render this block using PHP
		// Return null
		return null;
	},
});