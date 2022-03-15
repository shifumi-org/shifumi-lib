// fuse.js
import Fuse from 'fuse.js/dist/fuse.min.js';

/**
 * Import Uncanny Owl icon
 */

import {
	UncannyOwlIconColor,
	detailModuleIdIcon,
	detailInsertAsIcon,
	detailDimensionsIcon,
	detailEffectIcon,
	detailAnchorTargetIcon,
	imageIcon,
	angleRightCircle,
	searchIcon
} from '../../components/icons';

/**
 * Define __ constant
 * from global wp.i18n
 */

const { __ } = wp.i18n;

/**
 * Get WP Components
 */

const {
	Button,
	Spinner,
	TextControl,
	FormFileUpload
} = wp.components;

const { Fragment } = wp.element;

export const ContentPlaceholder = ({ children }) => {
	return (
		<div className="uo-tclr-gutenberg-content">
			{ children }
		</div>
	);
}

export const ContentReady = ({ contentName, data = {} }) => {
	// Clone properties
    data = JSON.parse( JSON.stringify( data ) );

	// Parse all the JSON strings attributes
	for ( let key in data ){
		data[ key ] = parseJSONifPossible( data[ key ] );
	}

	/**
	 * Create Front End view
	 */

	let frontEnd = [];

	// Create iframe simulation, but only if
	// "Insert as" is "iframe"
	if ( data.insertAs == 'iframe' ){
		frontEnd.push((
			<FrontendIframe
				title={ contentName}/>
		));
	}
	// Create button, link or custom image, but only if
	// "Insert as" is "lightbox" or "page"
	else if ( [ 'lightbox', 'page' ].includes( data.insertAs ) ){
		// Check what we have to add
		if ( data.openWith == 'button' ){
			frontEnd.push((
				<FrontendSimulationButton
					text={ data.buttonSettings.text }
					size={ data.buttonSettings.size }
				/>
			));
		}
		else if ( data.openWith == 'image' ){
			frontEnd.push((
				<FrontendSimulationCustomImage
					imageUrl={ data.imageSettings.url }
				/>
			));
		}
		else if ( data.openWith == 'link' ){
			frontEnd.push((
				<FrontendSimulationLink
					text={ data.linkSettings.text }
				/>
			));
		}
	}

	/**
	 * Custom title
	 */
	
	let customTitle = [];

	if ( data.insertAs == 'lightbox' && data.lightboxSettings.title !== '' ){
		customTitle.push((
			<div className="uo-tclr-gutenberg-content-data__custom-title">
				{ data.lightboxSettings.title }
			</div>
		));
	}

	/**
	 * Details
	 */

	// Create details array
	let details = [];

	// "ID" (Fingerprint)
	let detailID = {
		icon: detailModuleIdIcon,
		label: `${ __( 'ID:' ) } ${ data.contentId }`
	}

	details.push( detailID );

	// "Insert as" (eye icon)
	let detailInsertAs = {
		icon: detailInsertAsIcon
	};

	if ( data.insertAs == 'iframe' ){
		detailInsertAs.label = __( 'Iframe' )
	}
	else if ( data.insertAs == 'lightbox' ){
		detailInsertAs.label = __( 'Lightbox' )
	}
	else if ( data.insertAs == 'page' ){
		detailInsertAs.label = __( 'Page' )
	}

	details.push( detailInsertAs );

	// "Dimensions" (fullscreen icon)
	// We're going to add this one only if "Insert as" is
	// "iframe" or "lightbox"
	if ( [ 'iframe', 'lightbox' ].includes( data.insertAs ) ){
		let detailDimensions = {
			icon: detailDimensionsIcon
		}

		let widthValue,
			widthUnit,
			heightValue,
			heightUnit;

		if ( data.insertAs == 'iframe' ){
			widthValue  = data.iframeSettings.widthValue;
			widthUnit   = data.iframeSettings.widthUnit;
			heightValue = data.iframeSettings.heightValue;
			heightUnit  = data.iframeSettings.heightUnit;
		}
		else if ( data.insertAs == 'lightbox' ){
			widthValue  = data.lightboxSettings.widthValue;
			widthUnit   = data.lightboxSettings.widthUnit;
			heightValue = data.lightboxSettings.heightValue;
			heightUnit  = data.lightboxSettings.heightUnit;
		}

		detailDimensions.label = `${ widthValue }${ widthUnit } x ${ heightValue }${ heightUnit }`;

		details.push( detailDimensions );
	}

	// "Effect" (magic wand icon)
	// Show only if "Insert as" is "lightbox"
	if ( data.insertAs == 'lightbox' ){
		let detailEffect = {
			icon: detailEffectIcon
		}

		switch ( data.lightboxSettings.effect ){
			case 'fade':
				detailEffect.label = __( 'Fade' );
				break;

			case 'fadeScale':
				detailEffect.label = __( 'Fade Scale' );
				break;

			case 'slideLeft':
				detailEffect.label = __( 'Slide Left' ) ;
				break;

			case 'slideRight':
				detailEffect.label = __( 'Slide Right' );
				break;

			case 'slideUp':
				detailEffect.label = __( 'Slide Up' );
				break;

			case 'slideDown':
				detailEffect.label = __( 'Slide Down' );
				break;

			case 'fall':
				detailEffect.label = __( 'Fall' );
				break;
		}

		details.push( detailEffect );
	}

	// "Target" (new window icon)
	// Show only if "Insert as" is "page"
	if ( data.insertAs == 'page' ){
		let detailTarget = {
			icon: detailAnchorTargetIcon
		}

		if ( data.pageSettings.target == '_self' ){
			detailTarget.label = __( 'Open in the same window' )
		}
		else if ( data.pageSettings.target == '_blank' ){
			detailTarget.label = __( 'Open in a new window' )
		}

		details.push( detailTarget );
	}

	// Render
	return (
		<div className="uo-tclr-gutenberg-content uo-tclr-gutenberg-content--ready">
			<div className="uo-tclr-gutenberg-content-frontend">
				{ frontEnd }
			</div>
			<div className="uo-tclr-gutenberg-content-data">
				{ customTitle }
				<div className="uo-tclr-gutenberg-content-data__name">
					{ contentName }
				</div>
				<div className="uo-tclr-gutenberg-content-data__details">
					{
						// Iterate details object
						details.map(function( detail, index ){
							return (
								<ContentReadyDetail icon={detail.icon} label={detail.label}/>
							);
						})
					}
				</div>
			</div>
		</div>
	);
}

export const ContentReadyDetail = ({ icon, label }) => {
	return (
		<div className="uo-tclr-gutenberg-content-ready__detail">
			<div className="uo-tclr-gutenberg-content-ready-detail__icon">
				{ icon }
			</div>
			<div className="uo-tclr-gutenberg-content-ready-detail__data">
				{ label }
			</div>
		</div>
	);
}

export const Header = () => {
	return (
		<div className="uo-tclr-gutenberg-content__header">
			<div className="uo-tclr-gutenberg-content-header__icon">
				{ UncannyOwlIconColor }
			</div>
			<div className="uo-tclr-gutenberg-content-header__title">
				{ __( 'Tin Canny Content' ) }
			</div>
		</div>
	);
}

export const Description = ({ content }) => {
	return (
		<div className="uo-tclr-gutenberg-content-description">
			{ content }
		</div>
	);
}

export const ActionButtons = ({ onUpload, onClickSelect }) => {
	return (
		<div className="uo-tclr-gutenberg-content-actions">
			<FormFileUpload
                isDefault
                accept="application/zip"
                onChange={ onUpload }
            >
                { __( 'Upload' ) }
            </FormFileUpload>

			<Button
				isDefault
				onClick={ onClickSelect }
			>
				{ __( 'Select from Library' ) }
			</Button>
		</div>
	);
}

export const Notice = ({ type, content }) => {
	let noticeClass = '';

	if ( type == 'error' ){
		noticeClass = 'error';
	}
	else if ( type == 'warning' ){
		noticeClass = 'warning';
	}

	return (
		<div className={ `uo-tclr-gutenberg-content-notice uo-tclr-gutenberg-content-notice--${ noticeClass }` }>
			{ content }
		</div>
	);
}

export const LoadingContent = ({ text }) => {
	return (
		<div className="uo-tclr-gutenberg-content-uploading">
			<div className="uo-tclr-gutenberg-content-uploading__snipper">
				<Spinner/>
			</div>
			<div className="uo-tclr-gutenberg-content-uploading__text">
				{ text }
			</div>
		</div>
	);
}

export const ContentExplorerItem = ({ fileTitle, onClickDo }) => {
	return (
		<div
			className="uo-tclr-gutenberg-content-explorer-item"
			onClick={ onClickDo }
		>
			{ fileTitle }
		</div>
	);
}

export const ContentExplorer = ({ searchQuery, onSearch, files, onClickDo, onCancelDo }) => {
	// Filter the files
	// Check if the user searched something
	if ( ! [ undefined, '' ].includes( searchQuery ) ){
		// Set up the Fuse instance
		const searchFiles = new Fuse( files, {
			keys: [ 'title' ],
			threshold: 0,
        	ignoreLocation: true
		});

		// Search
		files = searchFiles.search( searchQuery ).map( file => file.item );
	}

	return (
		<div className="uo-tclr-gutenberg-content-explorer">
			<div className="uo-tclr-gutenberg-content-explorer__title">
				{ __( 'Select content' ) }
			</div>
			<div className="uo-tclr-gutenberg-content-explorer__box">
				<div className="uo-tclr-gutenberg-content-explorer__search">
					<div className="uo-tclr-gutenberg-content-explorer__search-icon">
						{ searchIcon }
					</div>
					<TextControl
		                placeholder={ __( 'Search content' ) }
		                value={ searchQuery }
		                type="text"
		                onChange={ ( searchQuery ) => onSearch( searchQuery ) } 
		            />
				</div>
				<div className="uo-tclr-gutenberg-content-explorer__list">
					{
						files.length > 0 ?
							files.map( ( file, index ) => {
								return (
									<ContentExplorerItem
										fileTitle={ file.title }
										onClickDo={ () => onClickDo( file ) }
									/>
								);
							})
						: <div className="uo-tclr-gutenberg-content-explorer-no-results">{ __( 'No results found' ) }</div>
					}
				</div>
			</div>
			<div className="uo-tclr-gutenberg-content-explorer__actions">
				<Button
					isDefault
					onClick={ onCancelDo }
				>
					{ __( 'Cancel' ) }
				</Button>
			</div>
		</div>
	);
}

export const FrontendSimulationButton = ({ text, size = 'normal' }) => {
	let cssClasses = [
		'uo-tclr-gutenberg-content-frontend-button'
	];

	// Add size class
	cssClasses.push( `uo-tclr-gutenberg-content-frontend-button--${size}` );

	return (
		<div className={ cssClasses.join( ' ' ) }>
			<div className="uo-tclr-gutenberg-content-frontend-button__text">
				{ text }
			</div>
			<div className="uo-tclr-gutenberg-content-frontend-button__icon">
				{ angleRightCircle }
			</div>
		</div>
	);
}

export const FrontendSimulationLink = ({ text }) => {
	return (
		<div className="uo-tclr-gutenberg-content-frontend-link">
			{ text }
		</div>
	);
}

export const FrontendSimulationCustomImage = ({ imageUrl }) => {
	let image = [];

	// Check if we can add the image or we have to add placeholder
	if ( isDefined( imageUrl ) && imageUrl !== '' ){
		image.push((
			<img src={ imageUrl }/>
		));
	}
	else {
		// Placeholder
		image.push((
			<div className="uo-tclr-gutenberg-content-frontend-image__placeholder">
				{ imageIcon }
			</div>
		));
	}

	return (
		<div className="uo-tclr-gutenberg-content-frontend-image">
			{ image }
		</div>
	);
}

export const FrontendIframe = ({ title }) => {
	return (
		<div className="uo-tclr-gutenberg-content-iframe">
			{ title }
		</div>
	);
}

export const UnsupportedContent = ({ fileTree, onCancelDo, onSelectDo }) => {
	return (
		<Fragment>
			<div className="uo-tclr-gutenberg-content-unsupported">
				<div className="uo-tclr-gutenberg-content-unsupported__notice">
					<Notice
						type="warning"
						content={ __( 'Unable to read .zip file. Please re-zip your file and upload it again.' ) }
					/>
				</div>
				<div className="uo-tclr-gutenberg-content-unsupported__selection">
					<div className="uo-tclr-gutenberg-content-unsupported__description">
						<p>
							{ __( 'Please note that any xAPI/SCORM statements sent by this module:' ) }
						</p>
						<p>
							<ul>
								<li>{ __( 'will not be recorded' ) }</li>
								<li>{ __( 'may display errors because the module cannot communicate with an LMS or LRS' ) }</li>
							</ul>
						</p>
						<p>
							{ __( 'To use this module anyway, select the .html file that launches the module using the file browser below:' ) }
						</p>
					</div>
					<div className="uo-tclr-gutenberg-content-unsupported__filetree">
						<FileTree
							structure={ fileTree }
							onSelectDo={ onSelectDo }
						/>
					</div>
				</div>
			</div>
			<div className="uo-tclr-gutenberg-content-unsupported-actions">
				<Button
					isDefault
					onClick={ onCancelDo }
				>
					{ __( 'Cancel and Delete Content' ) }
				</Button>
			</div>
		</Fragment>
	);
}

/**
 * To-do: Re-do this function
 */

export const FileTree = ({ structure, onSelectDo }) => {
	// Try to parse the structure
	structure = parseJSONifPossible( structure );

	// Get structure
	// To-do: Don't use dangerouslySetInnerHTML and create a recursive function
	// to create React elememts
	let getFileStructureHTML = ( structure, directory = '' ) => {
		let html = '';

		// Iterate each structure item
		for ( let [ key, structureItem ] of Object.entries( structure ) ){
			// Check if it's a folder
			if ( isObject( structureItem ) ){
				// Get random number, we're going to need this
				let randomNumber = ( Math.random() + 1 ) * ( Math.random() + 1 ) * 1000;

				// Get folder information
				let folder = {
					title:     key,
					directory: `${ directory }/${ key }`,
					id:        `uo-tclr-${ directory }-${ key }-${ randomNumber }`
				}

				html += (
					`<div class="uo-tclr-gutenberg-content-filetree__folder">
						<input type="checkbox" id="${ folder.id }"/>
						<div class="uo-tclr-gutenberg-content-filetree__folder-name">
							<label for="${ folder.id }">
								${ folder.title }
							</label>
						</div>
						<div class="uo-tclr-gutenberg-content-filetree__folder-items">
							${ getFileStructureHTML( structureItem, folder.directory ) }
						</div>
					</div>`
				);
			}
			// Otherwise is a single file
			else {
				let file = {
					title:     structureItem,
					directory: `${ directory }/${ structureItem }`,
				}

				let fileAction = () => {
					onSelectDo( file )
				}

				html += (
					`<div
						class="uo-tclr-gutenberg-content-filetree__file"
						data-title="${ file.title }"
						data-directory="${ file.directory }"
					>
						${ file.title }
					</div>`
				);
			}
		}

		return html;
	}

	// Get structure
	let files = {
		__html: getFileStructureHTML( structure )
	}

	return (
		<div className="uo-tclr-gutenberg-content-filetree">
			<div className="uo-tclr-gutenberg-content-filetree__instructions">
				{ __( 'Select an .html file' ) }
			</div>
			<div
				className="uo-tclr-gutenberg-content-filetree__content"
				onClick={( event ) => {
					// Get element
					let selectedFile = event.target;

					// File data
					let file = Object.assign( {}, selectedFile.dataset );

					// Check if it's a file, otherwise do nothing
					if ( isDefined( file.title ) && isDefined( file.directory ) ){
						onSelectDo( file )
					}
				}}
				dangerouslySetInnerHTML={ files }
			></div>
		</div>
	);
}

/**
 * Helper Functions
 */

export const isDefined = ( variable ) => {
	return variable !== undefined && variable !== null;
}

export const isObject = ( variable ) => {
	return isDefined( variable ) && typeof variable == 'object';
}

export const isArray = ( variable ) => {
	return variable.constructor == Array;
}

export const parseJSONifPossible = ( variable ) => {
	// Try to parse it as JSON
	try {
		// Replace u0022 with quotes
		variable = variable.replace( /u0022/g, '"' );

		// Try to parse it as JSON
		variable = JSON.parse( variable );
	}
	catch ( e ){}

	return variable;
}