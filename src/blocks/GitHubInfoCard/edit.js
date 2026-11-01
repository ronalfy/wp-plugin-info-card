/* eslint-disable react/jsx-key */
/* eslint-disable no-unused-vars */
/* eslint-disable no-undef */
/* eslint-disable camelcase */
/**
 * External dependencies
 */
import axios from 'axios';
import classnames from 'classnames';
import { escapeAttribute, escapeHTML } from '@wordpress/escape-html';
import Logo from '../Logo';
import { createBlock } from '@wordpress/blocks';
import { isURL, filterURLForDisplay } from '@wordpress/url';
import { ForkIcon, HomeIcon, GitHubIcon, HeartIcon, StarIcon, EyeIcon, CodeIcon } from '../components/GitHubIcons';
import { Fragment, useEffect, useState } from 'react';
import { dateI18n } from '@wordpress/date';
import numbro from 'numbro';
import { select, dispatch, useSelect, useDispatch } from '@wordpress/data';
import { blockStore } from '../store';
import useMediaUploader from '../hooks/useMediaUploader';

import { __ } from '@wordpress/i18n';

import {
	PanelBody,
	PanelRow,
	SelectControl,
	Spinner,
	TextControl,
	ToggleControl,
	ToolbarGroup,
	ToolbarButton,
	ToolbarItem,
	DropdownMenu,
	TabPanel,
	Button,
	MenuItemsChoice,
	BaseControl,
	ButtonGroup,
	Notice,
	/* eslint-disable-next-line */
	__experimentalTruncate as Truncate,
} from '@wordpress/components';

import {
	InspectorControls,
	BlockAlignmentToolbar,
	MediaUpload,
	BlockControls,
	useBlockProps,
} from '@wordpress/block-editor';

import { useInstanceId } from '@wordpress/compose';

const GitHubInfoCard = ( props ) => {
	const { attributes, setAttributes, context, clientId } = props;

	const blockUniqueId = context[ 'wppic/github-grid-uniqueId' ];

	const { openMediaUploader } = useMediaUploader();

	const {
		assetData,
		username,
		preview,
		repo,
		align,
		nameOverride,
		loginOverride,
		sponsorsUrlOverride,
		organizationUrlOverride,
		homepageUrlOverride,
		overrideButton,
		overrideButtonText,
		buttonType,
		overrideButtonUrl,
		avatarImageId,
		avatarImageUrl,
		versionOverride,
	} = attributes;

	const [ data, setData ] = useState( assetData );
	const [ noData, setNoData ] = useState( false );
	const [ cardLoading, setCardLoading ] = useState( false );
	const [ loading, setLoading ] = useState( attributes.loading );
	const [ mainInputField, setMainInputField ] = useState( null );
	const [ mainButton, setMainButton ] = useState( null );

	const resolveAvatarImage = () => {
		if ( avatarImageId ) {
			return avatarImageUrl;
		}
		if ( ! assetData?.avatar ) {
			return wppic.default_github_avatar;
		}
		return assetData.avatar;
	};

	const [ avatarSrc, setAvatarSrc ] = useState( resolveAvatarImage );

	useEffect( () => {
		setAvatarSrc( resolveAvatarImage() );
	}, [ assetData?.avatar, avatarImageUrl, avatarImageId ] );

	const {
		getShowTopBar,
		getShowAuthorBar,
		getShowStatsBar,
		getShowLastUpdated,
		getButtonType,
	} = useSelect( ( newSelect ) => {
		const storeName = blockStore( blockUniqueId );

		// Context uniqueId is empty on the first paste/insert frame. select(null) throws.
		if ( ! storeName ) {
			return {
				getShowTopBar: () => true,
				getShowAuthorBar: () => true,
				getShowStatsBar: () => true,
				getShowLastUpdated: () => true,
				getButtonType: () => 'github',
			};
		}

		return {
			getShowTopBar: newSelect( storeName ).getShowTopBar,
			getShowAuthorBar: newSelect( storeName ).getShowAuthorBar,
			getShowStatsBar: newSelect( storeName ).getShowStatsBar,
			getShowLastUpdated: newSelect( storeName ).getShowLastUpdated,
			getButtonType: newSelect( storeName ).getButtonType,
		};
	}, [ blockUniqueId ] );

	/**
	 * Get the styles of the parent block.
	 *
	 * @return {Array} The styles of the parent block.
	 */
	const getParentBlockStyles = () => {
		const currentBlockClientId = select( 'core/block-editor' ).getSelectedBlockClientId();
		if ( ! currentBlockClientId ) {
			return [];
		}
		const parentBlockClientId = select( 'core/block-editor' ).getBlockRootClientId( currentBlockClientId );
		if ( ! parentBlockClientId ) {
			return [];
		}
		const parentBlock = select( 'core/block-editor' ).getBlock( parentBlockClientId );
		if ( ! parentBlock ) {
			return [];
		}
		const blockType = select( 'core/blocks' ).getBlockType( parentBlock.name );
		const styles = blockType?.styles || [];

		// Get into label|value pairs.
		return styles.map( ( style ) => {
			return {
				label: style.label,
				value: 'is-style-' + style.name,
			};
		} );
	};

	/**
	 * Get the class name of the parent block.
	 *
	 * @return {string} The class name of the parent block.
	 */
	const getParentBlockClassName = () => {
		const currentBlockClientId = select( 'core/block-editor' ).getSelectedBlockClientId();
		if ( ! currentBlockClientId ) {
			return '';
		}
		const parentBlockClientId = select( 'core/block-editor' ).getBlockRootClientId( currentBlockClientId );
		if ( ! parentBlockClientId ) {
			return '';
		}
		const parentBlock = select( 'core/block-editor' ).getBlock( parentBlockClientId );
		if ( ! parentBlock ) {
			return '';
		}
		return parentBlock.attributes?.className || '';
	};

	const loadData = () => {
		setLoading( false );
		setCardLoading( true );
		setNoData( false );
		const restUrl = wppic.rest_url + 'wppic/v2/get_github_data';
		axios
			.get(
				restUrl + `?username=${ username }&repo=${ repo }&force=true`,
			)
			.then( ( response ) => {
				if ( response.data.success ) {
					// Now Set State
					setData( response.data.data );
					setAttributes( { assetData: response.data.data } );
					setCardLoading( false );
				} else {
					setNoData( true );
					setCardLoading( false );
					setLoading( true );
				}
			} );
	};
	const pluginOnClick = ( assetSlug, assetType ) => {
		loadData();
	};
	useEffect( () => {
		if ( loading ) {
			return;
		}
		if ( ! data || 0 === data.length ) {
			loadData();
		}
	}, [] );

	/**
	 * Select the search input field.
	 */
	useEffect( () => {
		if ( mainInputField ) {
			mainInputField.focus();

			// Select the text in the input field.
			mainInputField.select();
		}
	}, [ mainInputField ] );

	const getDateFromDateTime = ( dateTime ) => {
		return dateI18n( 'F j, Y', dateTime );
	};
	/**
	 * Get a humanized number.
	 *
	 * @param {number} number - The number to humanize.
	 * @return {string} The humanized number.
	 */
	const getNumberHumanized = ( number ) => {
		if ( number < 1000 ) {
			return number;
		}
		return numbro( number ).format( { mantissa: 1, average: true } );
	};
	const inspectorControls = (
		<InspectorControls>
			<PanelBody
				title={ __( 'Overrides', 'wp-plugin-info-card' ) }
				initialOpen={ true }
			>
				<TextControl
					label={ __( 'Name Override', 'wp-plugin-info-card' ) }
					value={ nameOverride }
					onChange={ ( value ) => setAttributes( { nameOverride: value } ) }
					help={ __( 'Override the name of the repo.', 'wp-plugin-info-card' ) }
				/>
				<TextControl
					label={ __( 'Login Override', 'wp-plugin-info-card' ) }
					value={ loginOverride }
					onChange={ ( value ) => setAttributes( { loginOverride: value } ) }
					help={ __( 'Override the login of the repo.', 'wp-plugin-info-card' ) }
				/>
				<TextControl
					label={ __( 'Sponsors URL Override', 'wp-plugin-info-card' ) }
					value={ sponsorsUrlOverride }
					type="url"
					onChange={ ( value ) => setAttributes( { sponsorsUrlOverride: value } ) }
					placeholder="https://"
					help={ __( 'Override the sponsors URL of the repo.', 'wp-plugin-info-card' ) }
				/>
				<TextControl
					label={ __( 'Organization URL Override', 'wp-plugin-info-card' ) }
					value={ organizationUrlOverride }
					type="url"
					placeholder="https://"
					onChange={ ( value ) => setAttributes( { organizationUrlOverride: value } ) }
					help={ __( 'Override the organization URL of the repo.', 'wp-plugin-info-card' ) }
				/>
				<TextControl
					label={ __( 'Homepage URL Override', 'wp-plugin-info-card' ) }
					value={ homepageUrlOverride }
					type="url"
					placeholder="https://"
					onChange={ ( value ) => setAttributes( { homepageUrlOverride: value } ) }
					help={ __( 'Override the homepage URL of the repo.', 'wp-plugin-info-card' ) }
				/>
				<TextControl
					label={ __( 'Version Override', 'wp-plugin-info-card' ) }
					value={ versionOverride }
					onChange={ ( value ) => setAttributes( { versionOverride: value } ) }
					help={ __( 'Override the version of the repo.', 'wp-plugin-info-card' ) }
				/>
				<div className="wppic-avatar-image-container">
					{
						( avatarImageUrl ) && (
							<>
								<Button
									variant="secondary"
									isDestructive={ true }
									icon="trash"
									label={ __( 'Remove Avatar Image', 'wp-plugin-info-card' ) }
									onClick={ () => {
										setAttributes( { avatarImageId: 0, avatarImageUrl: '' } );
									} }
								>
									{ __( 'Remove Custom Avatar', 'wp-plugin-info-card' ) }
								</Button>
							</>
						)
					}
					{
						( ! avatarImageId && ! avatarImageUrl ) && (
							<>
								<Button
									variant="secondary"
									label={ __( 'Override Avatar Image', 'wp-plugin-info-card' ) }
									help={ __( 'Upload an avatar image for the card. Recommended size is 500x500.', 'wp-plugin-info-card' ) }
									onClick={ () => {
										openMediaUploader( {
											attachmentId: avatarImageId,
											title: __( 'Avatar Image', 'wp-plugin-info-card' ),
											buttonLabel: __( 'Upload Avatar Image', 'wp-plugin-info-card' ),
											suggestedWidth: '500',
											suggestedHeight: '500',
											aspectRatio: '1:1',
										}, ( media ) => {
											setAttributes( { avatarImageId: media.id, avatarImageUrl: media.url } );
										} );
									} }
								>
									{ __( 'Upload Custom Avatar', 'wp-plugin-info-card' ) }
								</Button>
							</>
						)
					}
				</div>
			</PanelBody>
			<PanelBody
				title={ __( 'Button', 'wp-plugin-info-card' ) }
				initialOpen={ true }
			>
				<ToggleControl
					label={ __( 'Override Button', 'wp-plugin-info-card' ) }
					checked={ overrideButton }
					onChange={ ( value ) => setAttributes( { overrideButton: value } ) }
					help={ __( 'Override the button of the card. Leave disabled to use the parent settings.', 'wp-plugin-info-card' ) }
				/>
				{ overrideButton && (
					<>
						<TextControl
							label={ __( 'Button Text', 'wp-plugin-info-card' ) }
							value={ overrideButtonText }
							onChange={ ( value ) => setAttributes( { overrideButtonText: value } ) }
							help={ __( 'Override the text of the button. Leave blank to use the parent settings.', 'wp-plugin-info-card' ) }
						/>
						<SelectControl
							label={ __( 'Button Target and Type', 'wp-plugin-info-card' ) }
							value={ buttonType }
							onChange={ ( value ) => setAttributes( { buttonType: value } ) }
							options={ [
								{ label: __( 'View on GitHub', 'wp-plugin-info-card' ), value: 'github' },
								{ label: __( 'View Website', 'wp-plugin-info-card' ), value: 'website' },
								{ label: __( 'Sponsor', 'wp-plugin-info-card' ), value: 'sponsor' },
								{ label: __( 'Download', 'wp-plugin-info-card' ), value: 'download' },
								{ label: __( 'Star', 'wp-plugin-info-card' ), value: 'star' },
								{ label: __( 'Custom', 'wp-plugin-info-card' ), value: 'custom' },
							] }
							help={ __( 'Select the target of the button.', 'wp-plugin-info-card' ) }
						/>
						{
							buttonType === 'custom' && (
								<TextControl
									label={ __( 'Button URL', 'wp-plugin-info-card' ) }
									value={ overrideButtonUrl }
									onChange={ ( value ) => setAttributes( { overrideButtonUrl: value } ) }
									type="url"
									placeholder="https://"
									help={ __( 'Override the URL of the button.', 'wp-plugin-info-card' ) }
								/>
							)
						}
					</>
				) }
			</PanelBody>
		</InspectorControls>
	);

	const blockProps = useBlockProps( {
		className: classnames( `wppic-github-info-card align${ align }` ),
	} );

	const layouts = [
		{
			label: __( 'Large', 'wp-plugin-info-card' ),
			value: 'large',
		},
		{
			label: __( 'Card', 'wp-plugin-info-card' ),
			value: 'card',
		},
	];

	/**
	 * Get the layout of the parent block.
	 *
	 * @return {string} The layout of the parent block.
	 */
	const getParentBlockLayout = () => {
		const currentBlockClientId = select( 'core/block-editor' ).getSelectedBlockClientId();
		if ( ! currentBlockClientId ) {
			return '';
		}
		const parentBlockClientId = select( 'core/block-editor' ).getBlockRootClientId( currentBlockClientId );
		if ( ! parentBlockClientId ) {
			return '';
		}
		const parentBlock = select( 'core/block-editor' ).getBlock( parentBlockClientId );
		if ( ! parentBlock ) {
			return '';
		}
		return parentBlock.attributes?.layout || 'large';
	};
	// Shortcircuit early if the block is not part of a grid.
	if ( ! blockUniqueId ) {
		return null;
	}
	if ( preview ) {
		return (
			<div style={ { textAlign: 'center' } }>
				<img
					src={ wppic.github_items_preview }
					alt=""
					style={ { width: '100%', height: 'auto' } }
				/>
			</div>
		);
	}
	if ( cardLoading ) {
		return (
			<div { ...blockProps }>
				<div className="wppic-loading-placeholder">
					<div className="wppic-loading">
						<Logo size="45" />
						<br />
						<div className="wppic-spinner">
							<Spinner />
						</div>
					</div>
				</div>
			</div>
		);
	}

	/**
	 * Get the text for the button.
	 *
	 * @return {string} The text for the button.
	 */
	const getButtonText = () => {
		let parentButtonType = getButtonType();
		let buttonText = '';
		if ( overrideButton ) {
			parentButtonType = buttonType;
		}
		switch ( parentButtonType ) {
			case 'github':
			case 'custom':
				buttonText = __( 'View on GitHub', 'wp-plugin-info-card' );
				break;
			case 'website':
				buttonText = __( 'View Website', 'wp-plugin-info-card' );
				break;
			case 'sponsor':
				buttonText = __( 'Sponsor', 'wp-plugin-info-card' );
				break;
			case 'download':
				buttonText = __( 'Download', 'wp-plugin-info-card' );
				break;
			case 'star':
				buttonText = __( 'Star', 'wp-plugin-info-card' );
				break;
		}
		buttonText = overrideButton ? ( overrideButtonText || buttonText ) : buttonText;
		return buttonText;
	};
	const block = (
		<Fragment>
			{ loading && (
				<div className="wppic-query-block wppic-query-block-panel">
					<div className="wppic-block-svg">
						<Logo size="75" />
					</div>
					<div className="wp-pic-tab-panel">
						<TextControl
							label={ __(
								'GitHub Username or GitHub Repo URL',
								'wp-plugin-info-card',
							) }
							id="search-github-username"
							value={ username }
							onChange={ ( value ) => {
								// Check for URL so we don't paste in the slug AND url.
								if ( isURL( value ) ) {
									return;
								}
								setAttributes( {
									username: value,
								} );
							} }
							help={ __(
								'Enter a GitHub username or GitHub repo URL.',
								'wp-plugin-info-card',
							) }
							onPaste={ ( event ) => {
								// Get contents from clipboard.
								const clipboardData = event.clipboardData
									.getData( 'text/plain' )
									.trim();

								if ( isURL( clipboardData ) ) {
									// Extract out the slug from the URL.
									const gitHubUrlRegex = /https:\/\/github\.com\/([^/]*)\/([^/]*)/;
									if ( ! gitHubUrlRegex.test( clipboardData ) ) {
										return;
									}
									const newUsername = gitHubUrlRegex.exec(
										clipboardData,
									)[ 1 ];
									const newRepo = gitHubUrlRegex.exec(
										clipboardData,
									)[ 2 ];
									setAttributes( {
										username: newUsername,
										repo: newRepo,
									} );
									mainButton.focus();
								}
							} }
							onBlur={ () => {
								// If the username is a URL, extract out the username.
								if ( isURL( username ) ) {
									// Extract out the username from the URL.
									const gitHubUrlRegex = /https:\/\/github\.com\/([^/]*)\/([^/]*)/;
									if ( ! gitHubUrlRegex.test( clipboardData ) ) {
										return;
									}
									const newUsername = gitHubUrlRegex.exec(
										clipboardData,
									)[ 1 ];
									const newRepo = gitHubUrlRegex.exec(
										clipboardData,
									)[ 2 ];
									setAttributes( {
										username: newUsername,
										repo: newRepo,
									} );
									mainButton.focus();
								}
							} }
							ref={ setMainInputField }
						/>
						<TextControl
							label={ __(
								'GitHub Repo',
								'wp-plugin-info-card',
							) }
							id="search-github-repo"
							value={ repo }
							onChange={ ( value ) => {
								setAttributes( {
									repo: value,
								} );
							} }
						/>
						{ noData && (
							<Notice
								status="error"
								isDismissible={ false }
							>
								{ __(
									'No data found for the given slug.',
									'wp-plugin-info-card',
								) }
							</Notice>
						) }
					</div>

					<div className="wp-pic-gutenberg-button">
						<Button
							iconSize={ 20 }
							icon={ <Logo size="25" /> }
							variant="primary"
							type="submit"
							id="wppic-input-submit"
							onClick={ ( event ) => {
								event.preventDefault();
								setAttributes( { loading: false } );
								// Make sure the two fields are filled out.
								if ( ! username || ! repo ) {
									setNoData( true );
									return;
								}
								pluginOnClick( event );
							} }
							ref={ setMainButton }
						>
							{ __(
								'Preview and Configure',
								'wp-plugin-info-card',
							) }
						</Button>
					</div>
				</div>
			) }
			{ cardLoading && (
				<Fragment>
					<div className="wppic-loading-placeholder">
						<div className="wppic-loading">
							<Logo size="45" />
							<br />
							<div className="wppic-spinner">
								<Spinner />
							</div>
						</div>
					</div>
				</Fragment>
			) }
			{ ! loading && ! cardLoading && (
				<Fragment>
					{ inspectorControls }
					<BlockControls>
						<ToolbarGroup>
							<ToolbarButton
								icon="edit"
								title={ __(
									'Edit and Configure',
									'wp-plugin-info-card',
								) }
								onClick={ () => setLoading( true ) }
							/>
						</ToolbarGroup>
						<ToolbarGroup>
							<ToolbarButton
								icon="update"
								title={ __(
									'Refresh',
									'wp-plugin-info-card',
								) }
								onClick={ () => loadData() }
							/>
						</ToolbarGroup>
						<ToolbarGroup>
							<ToolbarItem as="button">
								{ ( toolbarItemHTMLProps ) => (
									<DropdownMenu
										toggleProps={ toolbarItemHTMLProps }
										label={ __(
											'Select Theme',
											'wp-plugin-info-card',
										) }
										icon="admin-customizer"
									>
										{ ( { onClose } ) => (
											<Fragment>
												<MenuItemsChoice
													choices={ getParentBlockStyles() }
													onSelect={ ( value ) => {
														const currentBlockClientId = select( 'core/block-editor' ).getSelectedBlockClientId();
														if ( ! currentBlockClientId ) {
															return;
														}
														const parentBlockClientId = select( 'core/block-editor' ).getBlockRootClientId( currentBlockClientId );
														if ( ! parentBlockClientId ) {
															return;
														}
														const parentBlock = select( 'core/block-editor' ).getBlock( parentBlockClientId );
														if ( ! parentBlock ) {
															return;
														}
														const newAttributes = { ...parentBlock.attributes, className: value };
														dispatch( 'core/block-editor' ).updateBlockAttributes( parentBlockClientId, newAttributes );
														onClose();
													} }
													value={ getParentBlockClassName() }
												/>
											</Fragment>
										) }
									</DropdownMenu>
								) }
							</ToolbarItem>
						</ToolbarGroup>
						<ToolbarGroup>
							<ToolbarItem as="button">
								{ ( toolbarItemHTMLProps ) => (
									<DropdownMenu
										toggleProps={ toolbarItemHTMLProps }
										label={ __(
											'Select a Layout',
											'wp-plugin-info-card',
										) }
										icon="layout"
									>
										{ ( { onClose } ) => (
											<Fragment>
												<MenuItemsChoice
													choices={ layouts }
													onSelect={ ( value ) => {
														const currentBlockClientId = select( 'core/block-editor' ).getSelectedBlockClientId();
														if ( ! currentBlockClientId ) {
															return;
														}
														const parentBlockClientId = select( 'core/block-editor' ).getBlockRootClientId( currentBlockClientId );
														if ( ! parentBlockClientId ) {
															return;
														}
														const parentBlock = select( 'core/block-editor' ).getBlock( parentBlockClientId );
														if ( ! parentBlock ) {
															return;
														}
														const newAttributes = { ...parentBlock.attributes, layout: value };
														dispatch( 'core/block-editor' ).updateBlockAttributes( parentBlockClientId, newAttributes );
														onClose();
													} }
													value={ getParentBlockLayout() }
												/>
											</Fragment>
										) }
									</DropdownMenu>
								) }
							</ToolbarItem>
						</ToolbarGroup>
						<ToolbarGroup>
							<ToolbarButton
								icon="admin-generic"
								title={ __(
									'GitHub Grid Settings',
									'wp-plugin-info-card',
								) }
								onClick={ () => {
									// Select our parent block.
									const parentBlockClientId = select( 'core/block-editor' ).getBlockRootClientId( clientId );
									if ( ! parentBlockClientId ) {
										return;
									}
									const parentBlock = select( 'core/block-editor' ).getBlock( parentBlockClientId );
									if ( ! parentBlock ) {
										return;
									}
									dispatch( 'core/block-editor' ).selectBlock( parentBlockClientId );
								} }
							/>
						</ToolbarGroup>
						<ToolbarGroup>
							<ToolbarItem as={ Button }
								onClick={ () => {
									// Select our parent block.
									const parentBlockClientId = select( 'core/block-editor' ).getBlockRootClientId( clientId );
									if ( ! parentBlockClientId ) {
										return;
									}
									// Get the current inner blocks.
									const parentBlock = select( 'core/block-editor' ).getBlock( parentBlockClientId );
									const innerBlocks = parentBlock.innerBlocks;
									const parentInnerBlocksCount = innerBlocks.length;
									// Create a new block.
									const newBlock = createBlock( 'wp-plugin-info-card/github-info-card' );
									// Insert the block at the end of the inner blocks.
									dispatch( 'core/block-editor' ).insertBlock( newBlock, parentInnerBlocksCount, parentBlockClientId );
									// Select the new block.
									dispatch( 'core/block-editor' ).selectBlock( newBlock.clientId );
								} }
							>
								{ __( 'Add Card', 'wp-plugin-info-card' ) }
							</ToolbarItem>
						</ToolbarGroup>
					</BlockControls>
					<div
						id={ attributes.uniqueId }
						className={ classnames(
							'is-placeholder',
							'wp-block-github-info-card',
							`align${ align }`,
						) }
					>
						<div className="wppic-github-info-card-wrapper">
							{ getShowTopBar() && (
								<>
									<div className="wppic-github-info-card-header">
										<div className="wppic-github-info-card-header-left">
											<>
												{ assetData.language && (
													<div className="wppic-github-info-card-header-language">
														{ escapeHTML( assetData.language ) }
													</div>
												) }
												{ assetData.license && (
													<div className="wppic-github-info-card-header-license">
														{ escapeHTML( assetData.license ) }
													</div>
												) }
											</>
										</div>
										<div className="wppic-github-info-card-header-right">
											<div className="wppic-github-info-card-header-icon wppic-github-info-card-icon-home">
												<HomeIcon width={ 20 } height={ 20 } />
											</div>
											<div className="wppic-github-info-card-header-icon wppic-github-info-card-icon-github">
												<GitHubIcon width={ 20 } height={ 20 } />
											</div>
											<div className="wppic-github-info-card-header-icon wppic-github-info-card-icon-heart">
												<HeartIcon width={ 20 } height={ 20 } />
											</div>
											<div className="wppic-github-info-card-header-icon wppic-github-info-card-icon-star">
												<StarIcon width={ 20 } height={ 20 } />
											</div>
										</div>
									</div>
								</>
							) }
							<div className="wppic-github-info-card-author-section">
								<div className="wppic-github-info-card-author-section-avatar">
									<img
										src={ avatarSrc }
										alt={ escapeHTML( assetData.full_name ) }
										loading="lazy"
										decoding="async"
										onError={ () => setAvatarSrc( wppic.default_github_avatar ) }
									/>
								</div>
								<div className="wppic-github-info-card-author-section-info">
									<div className="wppic-github-info-card-author-section-name">
										{ escapeHTML( nameOverride || assetData.name ) }
									</div>
									{ getShowAuthorBar() && (
										<div className="wppic-github-info-card-author-section-login">
											{ __( 'By', 'wp-plugin-info-card' ) } { escapeHTML( loginOverride || assetData.login ) }
										</div>
									) }
								</div>
							</div>
							{
								getShowStatsBar() && (
									<div className="wppic-github-info-card-meta">
										<div className="wppic-github-info-card-meta-item">
											<div className="wppic-github-info-card-meta-item-icon">
												<StarIcon width={ 20 } height={ 20 } />
											</div>
											<div className="wppic-github-info-card-meta-item-text">
												{ getNumberHumanized( parseInt( assetData.stargazers_count ) ) }
											</div>
										</div>
										<div className="wppic-github-info-card-meta-item">
											<div className="wppic-github-info-card-meta-item-icon">
												<ForkIcon width={ 20 } height={ 20 } />
											</div>
											<div className="wppic-github-info-card-meta-item-text">
												{ getNumberHumanized( parseInt( assetData.forks_count ) ) }
											</div>
										</div>
										<div className="wppic-github-info-card-meta-item">
											<div className="wppic-github-info-card-meta-item-icon">
												<EyeIcon width={ 20 } height={ 20 } />
											</div>
											<div className="wppic-github-info-card-meta-item-text">
												{ getNumberHumanized( parseInt( assetData.subscribers_count ) ) }
											</div>
										</div>
										{
											(
												versionOverride || typeof assetData.latest_release_tag_name !== 'undefined'
											) && (
												<>
													<div className="wppic-github-info-card-meta-item">
														<div className="wppic-github-info-card-meta-item-icon">
															<CodeIcon width={ 20 } height={ 20 } />
														</div>

														<div className="wppic-github-info-card-meta-item-text">
															<Truncate
																ellipsizeMode="tail"
																limit={ 10 }
															>
																{ escapeHTML( versionOverride || assetData.latest_release_tag_name ) }
															</Truncate>
														</div>

													</div>
												</>

											) }

									</div>
								)
							}
							<div className="wppic-github-info-card-description">
								{ escapeHTML( assetData.description ) }
							</div>
							<div className="wppic-github-info-card-buttons">
								<div className="wppic-github-info-card-buttons-left">
									{
										getShowLastUpdated() && (
											<div className="wppic-github-info-card-last-updated">
												<span className="wppic-github-info-card-last-updated-label">{ __( 'Last updated:', 'wp-plugin-info-card' ) }</span> { getDateFromDateTime( escapeHTML( assetData.updated_at ) ) }
											</div>
										)
									}
								</div>
								<div className="wppic-github-info-card-buttons-right">
									<div className="wppic-github-info-card-buttons-right-button">
										<Button
											className={
												classnames(
													'wppic-github-button button-reset',
													`wppic-github-button-${ getButtonType() }`,
												)
											}
											variant="link"
										>
											{
												getButtonText()
											}
										</Button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</Fragment>
			) }
		</Fragment>
	);

	return <div { ...blockProps }>{ block }</div>;
};

export default GitHubInfoCard;
