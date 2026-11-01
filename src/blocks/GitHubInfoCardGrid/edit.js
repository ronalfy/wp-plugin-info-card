/* eslint-disable react/jsx-key */
/* eslint-disable no-unused-vars */
/* eslint-disable no-undef */
/* eslint-disable camelcase */
/**
 * External dependencies
 */
import axios from 'axios';
import classnames from 'classnames';
import { Fragment, useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch, select, dispatch } from '@wordpress/data';
import { createBlock, insertBlocks } from '@wordpress/blocks';

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
	MenuItemsChoice,
	BaseControl,
	ButtonGroup,
	Button,
} from '@wordpress/components';

import {
	InspectorControls,
	BlockControls,
	useBlockProps,
	useInnerBlocksProps,
	PanelColorSettings,
} from '@wordpress/block-editor';

import { useInstanceId } from '@wordpress/compose';
import NumbersComponent from '../components/Numbers';
import { blockStore } from '../store';

const gitHubColors = [
	{ color: '#B9F6DD', name: 'Mint Green', slug: 'mint-green' },
	{ color: '#0E0911', name: 'Charcoal Black', slug: 'charcoal-black' },
	{ color: '#E8851A', name: 'Vibrant Orange', slug: 'vibrant-orange' },
	{ color: '#4C28A7', name: 'Deep Purple', slug: 'deep-purple' },
	{ color: '#F8F7FA', name: 'Soft White', slug: 'soft-white' },
	{ color: '#8A2925', name: 'Brick Red', slug: 'brick-red' },
	{ color: '#A1A8A3', name: 'Cool Gray', slug: 'cool-gray' },
	{ color: '#E078E4', name: 'Lavender Pink', slug: 'lavender-pink' },
	{ color: '#1363F1', name: 'Bright Blue', slug: 'bright-blue' },
	{ color: '#0B682A', name: 'Forest Green', slug: 'forest-green' },
	{ color: '#E6DD73', name: 'Golden Yellow', slug: 'golden-yellow' },
	{ color: '#61EB84', name: 'Lime Green', slug: 'lime-green' },
];

const GitHubInfoCardGrid = ( props ) => {
	const { attributes, setAttributes, clientId } = props;
	const instanceId = useInstanceId( GitHubInfoCardGrid, 'wp-plugin-info-card-id' );
	const [ hasCustomColors, setHasCustomColors ] = useState( false );
	const [ colors ] = useState( [
		...wppic.palette,
		...gitHubColors,
	] );

	const {
		preview,
		align,
		horizontalAlign,
		className,
		layout,
		showTopBar,
		showAuthorBar,
		showStatsBar,
		showLastUpdated,
		buttonType,
		numChildren,
		colGap,
		rowGap,
		cols,
		marginSpacing,
		marginSpacingTarget,
		backgroundColor,
		textColor,
		iconColor,
		iconColorHover,
		borderColor,
		languageBgColor,
		languageTextColor,
		sponsorsColor,
		buttonBgColor,
		buttonBgColorHover,
		buttonTextColor,
		buttonTextColorHover,
		uniqueId,
	} = attributes;

	// Prefer a saved or transform-provided uniqueId so child cards can select the store on first paint.
	const blockUniqueId = uniqueId || instanceId;

	const {
		getShowTopBar,
		getShowStatsBar,
		getShowAuthorBar,
		getShowLastUpdated,
		getButtonType,
	} = useSelect( ( select ) => {
		const storeName = blockStore( blockUniqueId );

		if ( ! storeName ) {
			return {
				getShowTopBar: () => true,
				getShowStatsBar: () => true,
				getShowAuthorBar: () => true,
				getShowLastUpdated: () => true,
				getButtonType: () => 'github',
			};
		}

		return {
			getShowTopBar: select( storeName ).getShowTopBar,
			getShowStatsBar: select( storeName ).getShowStatsBar,
			getShowAuthorBar: select( storeName ).getShowAuthorBar,
			getShowLastUpdated: select( storeName ).getShowLastUpdated,
			getButtonType: select( storeName ).getButtonType,
		};
	}, [ blockUniqueId ] );

	const {
		setShowTopBar,
		setShowStatsBar,
		setShowLastUpdated,
		setButtonType,
		setShowAuthorBar,
	} = useDispatch( blockStore( blockUniqueId ) );

	const innerBlocksCount = useSelect( ( select ) => {
		const blockOrder = select( 'core/block-editor' ).getBlockOrder( clientId );
		return blockOrder.length;
	} );

	// Set defaults.
	useEffect( () => {
		setShowTopBar( attributes.showTopBar );
		setShowStatsBar( attributes.showStatsBar );
		setShowLastUpdated( attributes.showLastUpdated );
		setButtonType( attributes.buttonType );
	}, [] );

	useEffect( () => {
		if ( numChildren !== innerBlocksCount ) {
			setAttributes( { numChildren: innerBlocksCount } );
		}
	}, [ innerBlocksCount ] );

	/**
	 * Attempt to check when block styles are changed.
	 */
	useEffect( () => {
		if ( undefined === className ) {
			return;
		}

		const styleMatch = new RegExp( /is-style-([^\s]*)/g ).exec( className );
		if ( null !== styleMatch ) {
			const match = styleMatch[ 1 ];
			if ( 'wppic-github-custom' === match ) {
				setHasCustomColors( true );
			} else {
				setHasCustomColors( false );
			}
		}
	}, [ className ] );

	const innerBlocksProps = useInnerBlocksProps( {
		className: `wppic-github-info-card-grid cols-${ cols } layout-${ layout } horizontal-align-${ horizontalAlign }`,
	}, {
		allowedBlocks: [ 'wp-plugin-info-card/github-info-card' ],
		template: [
			[ 'wp-plugin-info-card/github-info-card' ],
		],
		templateInsertUpdatesSelection: true,
		renderAppender: false,
	} );

	useEffect( () => {
		if ( ! uniqueId && blockUniqueId ) {
			setAttributes( { uniqueId: blockUniqueId } );
		}
	}, [ uniqueId, blockUniqueId ] );

	/**
	 * Retrieve colums interface for sidebar options.
	 *
	 * @return {Element} The columns interface.
	 */
	const getCols = () => {
		return (
			<BaseControl id="col-count" label={ __( 'Select How Many Columns', 'wp-plugin-info-card' ) }>
				<ButtonGroup>
					<Button
						variant={ cols === 1 ? 'primary' : 'secondary' }
						onClick={ () => {
							setAttributes( {
								cols: 1,
							} );
						} }
					>
						{ __( 'One', 'wp-plugin-info-card' ) }
					</Button>
					<Button
						variant={ cols === 2 ? 'primary' : 'secondary' }
						onClick={ () => {
							setAttributes( {
								cols: 2,
							} );
						} }
					>
						{ __( 'Two', 'wp-plugin-info-card' ) }
					</Button>
					<Button
						variant={ cols === 3 ? 'primary' : 'secondary' }
						onClick={ () => {
							setAttributes( {
								cols: 3,
							} );
						} }
					>
						{ __( 'Three', 'wp-plugin-info-card' ) }
					</Button>
				</ButtonGroup>
			</BaseControl>
		);
	};

	const marginSpacingOptions = [
		{ value: 'none', label: __( 'None', 'wp-plugin-info-card' ) },
		{ value: 'compact', label: __( 'Compact', 'wp-plugin-info-card' ) },
		{ value: 'comfortable', label: __( 'Comfortable', 'wp-plugin-info-card' ) },
		{ value: 'spacious', label: __( 'Spacious', 'wp-plugin-info-card' ) },
		{ value: 'extreme', label: __( 'Extreme', 'wp-plugin-info-card' ) },
	];

	const marginSpacingTargetOptions = [
		{ value: 'both', label: __( 'Both', 'wp-plugin-info-card' ) },
		{ value: 'top', label: __( 'Top', 'wp-plugin-info-card' ) },
		{ value: 'bottom', label: __( 'Bottom', 'wp-plugin-info-card' ) },
	];

	const inspectorControls = (
		<>
			<InspectorControls>
				{
					<PanelBody title={ __( 'Layout', 'wp-plugin-info-card' ) }>
						<SelectControl
							label={ __( 'Margin Spacing', 'wp-plugin-info-card' ) }
							options={ marginSpacingOptions }
							value={ marginSpacing }
							onChange={ ( value ) => {
								setAttributes( { marginSpacing: value } );
							} }
						/>
						<SelectControl
							label={ __( 'Margin Target', 'wp-plugin-info-card' ) }
							options={ marginSpacingTargetOptions }
							value={ marginSpacingTarget }
							onChange={ ( value ) => {
								setAttributes( { marginSpacingTarget: value } );
							} }
						/>
						{
							numChildren > 1 && (
								<>
									<PanelRow className="wppic-panel-rows-cols">
										{ getCols() }
									</PanelRow>
									<PanelRow className="wppic-panel-rows-numbers">
										<NumbersComponent
											value={ colGap }
											label={ __( 'Column Gap (in px)', 'wp-plugin-info-card' ) }
											numbers={ [ 20, 40, 60, 80 ] }
											onClick={ ( value ) => {
												setAttributes( { colGap: parseInt( value ) } );
											} }
											id="wppic-col-gap"
										/>
									</PanelRow>
									<PanelRow className="wppic-panel-rows-numbers">
										<NumbersComponent
											value={ rowGap }
											label={ __( 'Row Gap (in px)', 'wp-plugin-info-card' ) }
											numbers={ [ 20, 40, 60, 80 ] }
											onClick={ ( value ) => {
												setAttributes( { rowGap: parseInt( value ) } );
											} }
											id="wppic-row-gap"
										/>
									</PanelRow>
								</>
							)
						}
						<PanelRow>
							<BaseControl
								label={ __( 'Horizontal Alignment', 'wp-plugin-info-card' ) }
								id="wppic-horizontal-align"
							>
								<ButtonGroup>
									<Button
										variant={ horizontalAlign === 'left' ? 'primary' : 'secondary' }
										onClick={ () => {
											setAttributes( { horizontalAlign: 'left' } );
										} }
										icon="align-left"
										label={ __( 'Left', 'wp-plugin-info-card' ) }
									/>
									<Button
										variant={ horizontalAlign === 'center' ? 'primary' : 'secondary' }
										onClick={ () => {
											setAttributes( { horizontalAlign: 'center' } );
										} }
										icon="align-center"
										label={ __( 'Center', 'wp-plugin-info-card' ) }
									/>
									<Button
										variant={ horizontalAlign === 'right' ? 'primary' : 'secondary' }
										onClick={ () => {
											setAttributes( { horizontalAlign: 'right' } );
										} }
										icon="align-right"
										label={ __( 'Right', 'wp-plugin-info-card' ) }
									/>
								</ButtonGroup>
							</BaseControl>
						</PanelRow>
					</PanelBody>
				}
				<PanelBody title={ __( 'Visibility Controls', 'wp-plugin-info-card' ) }>
					<ToggleControl
						label={ __( 'Show Top Bar', 'wp-plugin-info-card' ) }
						checked={ showTopBar }
						onChange={ ( value ) => {
							setShowTopBar( value );
							setAttributes( { showTopBar: value } );
						} }
						help={ __( 'Show the top bar with the icons.', 'wp-plugin-info-card' ) }
					/>
					<ToggleControl
						label={ __( 'Show Stats Bar', 'wp-plugin-info-card' ) }
						checked={ showStatsBar }
						onChange={ ( value ) => {
							setShowStatsBar( value );
							setAttributes( { showStatsBar: value } );
						} }
						help={ __( 'Show the stats bar with the plugin stats.', 'wp-plugin-info-card' ) }
					/>
					<ToggleControl
						label={ __( 'Show Author Bar', 'wp-plugin-info-card' ) }
						checked={ showAuthorBar }
						onChange={ ( value ) => {
							setShowAuthorBar( value );
							setAttributes( { showAuthorBar: value } );
						} }
						help={ __( 'Show the author bar with the Organization Name and login.', 'wp-plugin-info-card' ) }
					/>
					<ToggleControl
						label={ __( 'Show Last Updated', 'wp-plugin-info-card' ) }
						checked={ showLastUpdated }
						onChange={ ( value ) => {
							setShowLastUpdated( value );
							setAttributes( { showLastUpdated: value } );
						} }
						help={ __( 'Show the last updated date.', 'wp-plugin-info-card' ) }
					/>
					<SelectControl
						label={ __( 'Button Target and Type', 'wp-plugin-info-card' ) }
						value={ buttonType }
						onChange={ ( value ) => {
							setButtonType( value );
							setAttributes( { buttonType: value } );
						} }
						options={ [
							{ label: __( 'View on GitHub', 'wp-plugin-info-card' ), value: 'github' },
							{ label: __( 'View Website', 'wp-plugin-info-card' ), value: 'website' },
							{ label: __( 'Sponsor', 'wp-plugin-info-card' ), value: 'sponsor' },
							{ label: __( 'Download', 'wp-plugin-info-card' ), value: 'download' },
							{ label: __( 'Star', 'wp-plugin-info-card' ), value: 'star' },
						] }
						help={ __( 'Select the type of button to display and where it will link to.', 'wp-plugin-info-card' ) }
					/>
				</PanelBody>
			</InspectorControls>
			{ hasCustomColors && (
				<InspectorControls group="styles">
					<PanelColorSettings
						__experimentalIsRenderedInSidebar
						title={ __( 'Custom Color Settings', 'wp-plugin-info-card' ) }
						colorSettings={ [
							{
								label: __( 'Background Color', 'wp-plugin-info-card' ),
								value: backgroundColor,
								onChange: ( value ) => {
									setAttributes( { backgroundColor: value } );
								},
							},
							{
								label: __( 'Text Color', 'wp-plugin-info-card' ),
								value: textColor,
								onChange: ( value ) => {
									setAttributes( { textColor: value } );
								},
							},
							{
								label: __( 'Icon Color', 'wp-plugin-info-card' ),
								value: iconColor,
								onChange: ( value ) => {
									setAttributes( { iconColor: value } );
								},
							},
							{
								label: __( 'Icon Color Hover', 'wp-plugin-info-card' ),
								value: iconColorHover,
								onChange: ( value ) => {
									setAttributes( { iconColorHover: value } );
								},
							},
							{
								label: __( 'Border Color', 'wp-plugin-info-card' ),
								value: borderColor,
								onChange: ( value ) => {
									setAttributes( { borderColor: value } );
								},
							},
							{
								label: __( 'Language Background Color', 'wp-plugin-info-card' ),
								value: languageBgColor,
								onChange: ( value ) => {
									setAttributes( { languageBgColor: value } );
								},
							},
							{
								label: __( 'Language Text Color', 'wp-plugin-info-card' ),
								value: languageTextColor,
								onChange: ( value ) => {
									setAttributes( { languageTextColor: value } );
								},
							},
							{
								label: __( 'Sponsors Color', 'wp-plugin-info-card' ),
								value: sponsorsColor,
								onChange: ( value ) => {
									setAttributes( { sponsorsColor: value } );
								},
							},
							{
								label: __( 'Button Background Color', 'wp-plugin-info-card' ),
								value: buttonBgColor,
								onChange: ( value ) => {
									setAttributes( { buttonBgColor: value } );
								},
							},
							{
								label: __( 'Button Background Color Hover', 'wp-plugin-info-card' ),
								value: buttonBgColorHover,
								onChange: ( value ) => {
									setAttributes( { buttonBgColorHover: value } );
								},
							},
							{
								label: __( 'Button Text Color', 'wp-plugin-info-card' ),
								value: buttonTextColor,
								onChange: ( value ) => {
									setAttributes( { buttonTextColor: value } );
								},
							},
							{
								label: __( 'Button Text Color Hover', 'wp-plugin-info-card' ),
								value: buttonTextColorHover,
								onChange: ( value ) => {
									setAttributes( { buttonTextColorHover: value } );
								},
							},
						] }
						colors={ colors }
					/>
				</InspectorControls>
			) }
		</>
	);

	const blockProps = useBlockProps( {
		className: classnames(
			`align${ align } layout-${ layout } cols-${ numChildren > 1 ? cols : 1 } wppic-margin-spacing-${ marginSpacing } wppic-margin-spacing-target-${ marginSpacingTarget }`,
			{
				'is-grid': numChildren > 1,
				'wppic-github-info-card': layout === 'card',
			},
		),
	} );

	if ( preview ) {
		return (
			<div style={ { textAlign: 'center' } }>
				<img
					src={ wppic.github_items_grid_preview }
					alt=""
					style={ { width: '100%', height: 'auto' } }
				/>
			</div>
		);
	}

	const themes = [
		{
			label: 'GitHub Light',
			value: 'is-style-wppic-github-light',
		},
		{
			label: 'GitHub Dark',
			value: 'is-style-wppic-github-dark',
		},

		{
			label: 'GitHub Colorful',
			value: 'is-style-wppic-github-colorful',
		},

		{
			label: 'GitHub Black & White',
			value: 'is-style-wppic-github-bw',
		},

		{
			label: 'GitHub Custom',
			value: 'is-style-wppic-github-custom',
		},
	];

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

	const getBlockControls = () => (
		<BlockControls>
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
										choices={ themes }
										onSelect={ ( value ) => {
											setAttributes( {
												className: value,
											} );
											onClose();
										} }
										value={ attributes.className }
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
											setAttributes( {
												layout: value,
											} );
											onClose();
										} }
										value={ layout }
									/>
								</Fragment>
							) }
						</DropdownMenu>
					) }
				</ToolbarItem>
			</ToolbarGroup>
			<ToolbarGroup>
				<ToolbarItem as={ Button }
					onClick={ () => {
						// Get the current inner blocks.
						const parentBlock = select( 'core/block-editor' ).getBlock( clientId );
						const innerBlocks = parentBlock.innerBlocks;
						const parentInnerBlocksCount = innerBlocks.length;
						// Create a new block.
						const newBlock = createBlock( 'wp-plugin-info-card/github-info-card' );
						// Insert the block at the end of the inner blocks.
						dispatch( 'core/block-editor' ).insertBlock( newBlock, parentInnerBlocksCount, clientId );
						// Select the new block.
						dispatch( 'core/block-editor' ).selectBlock( newBlock.clientId );
					} }
				>
					{ __( 'Add Card', 'wp-plugin-info-card' ) }
				</ToolbarItem>
			</ToolbarGroup>
		</BlockControls>
	);

	const gridStyles = `
		#${ attributes.uniqueId } .wppic-github-info-card-grid {
			display: grid;
			column-gap: ${ colGap }px;
			row-gap: ${ rowGap }px;
		}
	`;

	const colorStyles = `
		#${ attributes.uniqueId } .wppic-github-info-card {
			--wppic-github-background-color: ${ backgroundColor };
			--wppic-github-text-color: ${ textColor };
			--wppic-github-icon-color: ${ iconColor };
			--wppic-github-icon-color-hover: ${ iconColorHover };
			--wppic-github-border: ${ borderColor };
			--wppic-github-language-bg: ${ languageBgColor };
			--wppic-github-language-color: ${ languageTextColor };
			--wppic-github-sponsors-color: ${ sponsorsColor };
			--wppic-github-button-bg: ${ buttonBgColor };
			--wppic-github-button-bg-hover: ${ buttonBgColorHover };
			--wppic-github-button-text-color: ${ buttonTextColor };
			--wppic-github-button-text-color-hover: ${ buttonTextColorHover };
		}
	`;

	return (
		<div { ...blockProps } id={ attributes.uniqueId }>
			{ numChildren > 1 && (
				<style>{ gridStyles }</style>
			) }
			{ hasCustomColors && (
				<style>{ colorStyles }</style>
			) }
			{ inspectorControls }
			{ getBlockControls() }
			<div { ...innerBlocksProps } />
		</div>
	);
};

export default GitHubInfoCardGrid;
