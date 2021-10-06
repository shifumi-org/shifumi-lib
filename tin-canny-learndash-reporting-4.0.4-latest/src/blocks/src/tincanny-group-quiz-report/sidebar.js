const { __ } = wp.i18n;

const {
	assign
} = lodash;

const {
	addFilter
} = wp.hooks;

const {
	PanelBody,
	TextControl,
    SelectControl
} = wp.components;

const {
	Fragment
} = wp.element;

const {
	createHigherOrderComponent
} = wp.compose;

const {
    InspectorControls
} = wp.blockEditor;

export const addTincannyGroupQuizReportSettings = createHigherOrderComponent( ( BlockEdit ) => {
    return ( props ) => {
        // Check if we have to do something
        if ( props.name == 'tincanny/group-quiz-report' && props.isSelected ){
            return (
                <Fragment>
                    <BlockEdit { ...props } />
                    <InspectorControls>

                        <PanelBody title={ __( 'Group Quiz Report Settings', 'uncanny-learndash-codes' ) }>
                            <TextControl label={ __( 'User Quiz Report URL' ) } value={ props.attributes.user_report_url } type='text' onChange={ ( value ) => { props.setAttributes({ user_report_url: value }); }} />
				        </PanelBody>

                    </InspectorControls>
                </Fragment>
            );
        }



        return <BlockEdit { ...props } />;
    };
}, 'addTincannyGroupQuizReportSettings' );

addFilter( 'editor.BlockEdit', 'tincanny/group-quiz-report', addTincannyGroupQuizReportSettings );
