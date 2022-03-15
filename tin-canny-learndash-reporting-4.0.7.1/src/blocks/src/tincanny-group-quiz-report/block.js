// Import Uncanny Owl icon
import {
	UncannyOwlIconColor
} from '../components/icons';

import {
    TinCannyPlaceholder
} from '../components/editor';

import './sidebar.js';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

registerBlockType( 'tincanny/group-quiz-report', {
	title: __( 'Tin Canny Group Quiz Report' ),

	description: __( 'Embed Tin Canny report for group leaders that displays quiz results of group members.' ),

	icon: UncannyOwlIconColor,

	category: 'uncanny-learndash-reporting',

	keywords: [
		__( 'Uncanny Owl' ),
	],

	supports: {
		html: false
	},

	attributes: {
		'user_report_url': {
            type: 'string',
            default: ''
        },
	},

	edit({ className, attributes, setAttributes }){
		return (
			<div className={ className }>
				<TinCannyPlaceholder>
					{ __( 'Tin Canny Group Quiz Report' ) }
				</TinCannyPlaceholder>
			</div>
		);
	},

	save({ className, attributes }){
		// We're going to render this block using PHP
		// Return null
		return null;
	},
});