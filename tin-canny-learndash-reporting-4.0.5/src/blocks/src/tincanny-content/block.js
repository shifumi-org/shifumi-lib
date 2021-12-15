// Import Uncanny Owl icon
import {
	UncannyOwlIconColor
} from '../components/icons';

// Import Editor components
import {
	ContentPlaceholder,
	ContentReady,
	Header,
	Description,
	ActionButtons,
	Notice,
	LoadingContent,
	ContentExplorer,
	UnsupportedContent
} from './components/editor';

// Import Sidebar filters
import './components/sidebar';

//  Import CSS.
import './css/style.scss';
import './css/editor.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;

registerBlockType( 'tincanny/content', {
	title: __( 'Tin Canny Content' ),

	description: __( 'Upload and embed Tin Canny content such as Storyline, Captivate and iSpring files. Display the uploaded content in an iFrame, lightbox or new window.' ),

	icon: UncannyOwlIconColor,

	category: 'uncanny-learndash-reporting',

	keywords: [
		__( 'Uncanny Owl' ),
	],

	supports: {
		html: false
	},

	attributes: {
		status: {
			type:    'string',
			default: 'start'
		},
		contentId: {
			type:    'string',
			default: ''
		},
		contentTitle: {
			type:    'string',
			default: ''
		},
		contentUrl: {
			type:    'string',
			default: ''
		}
	},

	edit({ className, attributes, setAttributes }){
		// Create variable to save all the components we're going
		// to show in the editor
		let editorComponents = [];
		
		if (// Check if:
			// the user was uploading content but refreshed the page
			( attributes.status == 'uploading-content' && ! isDefined( attributes.file ) )
			// the user was loading the library but refreshed the page
			|| ( attributes.status == 'fetching-content' && ! isDefined( attributes.fetchInProgress ) )
			// the user was selecting a content but then refreshed the page
			|| ( attributes.status == 'content-library-ready' && ! isDefined( attributes.library ) )
			// the user was selecting a launcher in the unsupported content
			// but refreshed the page
			|| ( attributes.status == 'not-supported' && ! isDefined( attributes.unsupportedContent ) )
			// The user selected a file in the launcher and the AJAX request started
			// but before finishing the user refreshed the page
			|| ( attributes.status == 'not-supported-selected' && ! isDefined( attributes.unsupportedContentSelectedFile ) )
		){
			// Then start again
			attributes.status = 'start';
		}

		// Check if we have content or we have to show the placeholder
		if ( attributes.status == 'has-valid-content' ){
			// Add ContentReady component
			editorComponents.push((
				<ContentReady
					contentName={ attributes.contentTitle }
					data={ attributes }
				/>
			));
		}
		else {
			// Create array we're we are going to save all the
			// components to show on the placeholder, depending of our data
			let placeholderComponents = [];

			// Initial status, the user didn't select a file yet
			if ( attributes.status == 'start' ){
				// Check for notices
				let notices = [];
				if ( isDefined( attributes.notice ) ){
					if ( attributes.notice.type == 'error' ){
						notices.push((
							<Notice
								type="error"
								content={ attributes.notice.message }
							/>
						));
					}
				}

				// Add components
				placeholderComponents.push((
					<Fragment>
						<Header/>
						<Description
							content={ __( 'Embed Tin Canny content and display the uploaded content in an iFrame, lightbox or new window.' ) }
						/>
						<ActionButtons
							onUpload={( event ) => {
								setAttributes({
									status: 'uploading-content',
									file:   event.target.files[0]
								});
							}}
							onClickSelect={() => {
								setAttributes({
									status:          'fetching-content',
									fetchInProgress: true
								});
							}}
						/>
						{ notices }
					</Fragment>
				));
			}
			// The user used "Upload", we have to upload attributes.file
			else if ( attributes.status == 'uploading-content' ){
				placeholderComponents.push((
					<LoadingContent
						text={ __( 'Uploading content...' ) }
					/>
				));
				
				// Create Form Data and append file and data
				let formData = new FormData();

				formData.append( 'media_upload_file', attributes.file );
				formData.append( 'security',          vc_snc_data_obj.ajax_nonce_2 );
				formData.append( 'action',            'SnC_Media_Upload' );
				formData.append( 'snc-extension',     'zip' );

				// Upload file
				fetch( vc_snc_data_obj.ajaxurl, {
					method: 'POST',
					body:   formData,
				})
				.then( response => response.json() )
				.then(
					( result ) => {
						// Check if the id is a number
						// That would mean the request was successful
						if ( ! isNaN( result.id ) ){
							// Set new attributes
							setAttributes({
								status:       'has-valid-content',
								contentId:    String( result.id ),
								contentTitle: result.title
							});
						}
						else {
							if ( result.id == 'not_supported' ){
								// Not supported
								setAttributes({
									status: 'not-supported',
									unsupportedContent: {
										title:             result.title,
										structure:         result.structure,
										confirmationNonce: result.nonce
									}
								});
							}
							else {
								// Error. Show "Try again"
								setAttributes({
									status:      'start',
									notice: {
										type:    'error',
										message: __( 'Something went wrong. Please, try again' )
									}
								});
							}
						}
					},
					( error ) => {
						// Error. Show "Try again"
						setAttributes({
							status:      'start',
							notice: {
								type:    'error',
								message: __( 'Something went wrong. Please, try again' )
							}
						});
					}
				);
			}
			// The user clicked "Select from library", we have to get the list of content
			else if ( attributes.status == 'fetching-content' ){
				placeholderComponents.push((
					<LoadingContent
						text={ __( 'Loading Library...' ) }
					/>
				));

				// Get list of content
				let formData = new FormData();

				formData.append( 'security', vc_snc_data_obj.ajax_nonce );
				formData.append( 'action',   'vc_snc_data' );

				fetch( vc_snc_data_obj.ajaxurl, {
					method: 'POST',
					body:   formData,
				})
				.then( response => response.json() )
				.then(
					( result ) => {
						// Check if the result is an array
						if ( result.constructor == Array ){
							if ( result.length > 0 ){
								// Prepare library
								let library = result.map(( item, index ) => {
									return {
										id:          String( item.ID ),
										title:       item.file_name,
										titleSearch: item.file_name.toLowerCase().replace( /\s/g, '' ),
										url:         item.url
									}
								})

								// Set attributes and go to next step
								setAttributes({
									status:  'content-library-ready',
									library: library
								});
							}
							else {
								setAttributes({
									status:      'start',
									notice: {
										type:    'error',
										message: __( "We didn't find any content. Please, try uploading some" )
									}
								});
							}
						}
						else {
							// Error. Show "Try again"
							setAttributes({
								status:      'start',
								notice: {
									type:    'error',
									message: __( 'Something went wrong. Please, try again' )
								}
							});
						}
					},
					( error ) => {
						// Error. Show "Try again"
						setAttributes({
							status:      'start',
							notice: {
								type:    'error',
								message: __( 'Something went wrong. Please, try again' )
							}
						});
					}
				);
			}
			// The list is ready, show it
			else if ( attributes.status == 'content-library-ready' ){
				placeholderComponents.push((
					<Fragment>
						<Header/>
						<ContentExplorer
							searchQuery={ attributes.searchQuery }
							onSearch={( searchQuery ) => {
								setAttributes({
									searchQuery: searchQuery
								});
							}}
							files={ attributes.library }
							onClickDo={( contentItem ) => {
								// The user selected an item
								setAttributes({
									status:       'has-valid-content',
									contentId:    contentItem.id,
									contentTitle: contentItem.title,
									contentUrl:   contentItem.url
								});
							}}
							onCancelDo={() => {
								// Delete this and go to the first step
								setAttributes({
									status:       'start',
								});
							}}
						/>
					</Fragment>
				));
			}
			// The user uploaded unsupported content and has to select the a launcher
			else if ( attributes.status == 'not-supported' ){
				placeholderComponents.push((
					<Fragment>
						<Header/>
						<UnsupportedContent
							fileTree={ attributes.unsupportedContent.structure }
							onSelectDo={( file ) => {
								// Delete this and go to the first step
								setAttributes({
									status:                         'not-supported-selected',
									unsupportedContentSelectedFile: file
								});
							}}
							onCancelDo={() => {
								// Delete this and go to the first step
								setAttributes({
									status:       'start',
								});
							}}
						/>
					</Fragment>
				));
			}
			// The user selected a product, so we have to save it in the database
			else if ( attributes.status == 'not-supported-selected' ){
				placeholderComponents.push((
					<LoadingContent
						text={ __( 'Saving Tin Canny Content...' ) }
					/>
				));

				// Create form data to do the request that
				// will save the content into the database
				let formData = new FormData();

				// If the path can't start with /. Check if it has that character 
				// and remove it
				let fileDirectory = attributes.unsupportedContentSelectedFile.directory.replace( /^(\/)/, '' );

				// Get file title from the ZIP file the user uploaded
				let fileTitle = attributes.file.name.replace( /(.zip)/g, '' );

				// Append data
				formData.append( 'security', attributes.unsupportedContent.confirmationNonce );
				formData.append( 'action',   'SnC_Link_File_Path' );
				formData.append( 'filePath', fileDirectory );
				formData.append( 'title',    fileTitle );

				fetch( vc_snc_data_obj.ajaxurl, {
					method: 'POST',
					body:   formData,
				})
				.then( response => response.json() )
				.then(
					( result ) => {
						// Check if we have the required data
						if ( isDefined( result.id ) && isDefined( result.title ) ){
							// The user selected an item
							setAttributes({
								status:       'has-valid-content',
								contentId:    String( result.id ),
								contentTitle: fileTitle,
								contentUrl:   fileDirectory
							});
						}
						else {
							// Error. Show "Try again"
							setAttributes({
								status:      'start',
								notice: {
									type:    'error',
									message: __( 'Something went wrong. Please, try again' )
								}
							});
						}
					},
					( error ) => {
						// Error. Show "Try again"
						setAttributes({
							status:      'start',
							notice: {
								type:    'error',
								message: __( 'Something went wrong. Please, try again' )
							}
						});
					}
				);
			}

			// Add placeholder and his components
			editorComponents.push((
				<ContentPlaceholder>
					{ placeholderComponents }
				</ContentPlaceholder>
			));
		}

		return (
			<div className={ className }>
				{ editorComponents }
			</div>
		);
	},

	save({ className, attributes }){
		// We're going to render this block using PHP
		// Return null
		return null;
	},
});

export const isDefined = ( variable ) => {
	return variable !== undefined && variable !== null;
}