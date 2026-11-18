// @ts-nocheck
/**
 * External dependencies
 */
import axios from 'axios';
import classnames from 'classnames';
const HtmlToReactParser = require( 'html-to-react' ).Parser;

import { __ } from '@wordpress/i18n';

const { useState, useEffect, Fragment, useCallback } = wp.element;

const {
	PanelBody,
	SelectControl,
	Spinner,
	TextControl,
	Button,
	ToolbarGroup,
	ToggleControl,
	Notice,
	TabPanel,
	PanelRow,
	ToolbarItem,
	DropdownMenu,
	MenuItemsChoice,
} = wp.components;

import { debounce, useInstanceId } from '@wordpress/compose';
import { escapeAttribute } from '@wordpress/escape-html';
import { decodeEntities } from '@wordpress/html-entities';

const {
	InspectorControls,
	BlockControls,
	MediaUpload,
	AlignmentToolbar,
	BlockAlignmentToolbar,
	useBlockProps,
} = wp.blockEditor;

import PluginFlex from '../templates/PluginFlex';
import PluginCard from '../templates/PluginCard';
import PluginLarge from '../templates/PluginLarge';
import PluginWordPress from '../templates/PluginWordPress';
import ThemeFlex from '../templates/ThemeFlex';
import ThemeWordPress from '../templates/ThemeWordPress';
import ThemeLarge from '../templates/ThemeLarge';
import ThemeCard from '../templates/ThemeCard';
import PluginRatingsCard from '../templates/PluginRatingsCard';
import ThemeRatingsCard from '../templates/ThemeRatingsCard';
import Logo from '../Logo';
import { Radio } from 'lucide-react';
import NumbersComponent from '../components/Numbers';
import { set, uniqueId } from 'lodash';

/**
 * Recover the search field from the attributes that were saved with the block.
 *
 * @param {Object} attrs Block attributes.
 * @return {{searchBy: string, searchValue: string}} Saved search mode and value.
 */
const getSavedQuery = ( attrs ) => {
	if ( attrs.tag ) {
		return { searchBy: 'tag', searchValue: attrs.tag };
	}
	if ( attrs.author ) {
		return { searchBy: 'author', searchValue: attrs.author };
	}
	if ( attrs.user ) {
		return { searchBy: 'favorites', searchValue: attrs.user };
	}
	if ( attrs.browse ) {
		return { searchBy: 'category', searchValue: '' };
	}
	return { searchBy: 'general', searchValue: attrs.search || '' };
};

const WP_Plugin_Card_Query = ( props ) => {
	const { attributes, setAttributes } = props;

	const generatedUniqueId = useInstanceId( WP_Plugin_Card_Query, 'wp-plugin-info-card-query' );

	const savedQuery = getSavedQuery( attributes );
	const [ loading, setLoading ] = useState( false );
	const [ cardLoading, setCardLoading ] = useState( false );
	const [ noData, setNoData ] = useState( false );
	const [ searchBy, setSearchBy ] = useState( savedQuery.searchBy );
	const [ searchValue, setSearchValue ] = useState( savedQuery.searchValue );
	const [ currentScheme, setCurrentScheme ] = useState( attributes.scheme );
	const [ currentLayout, setCurrentLayout ] = useState( attributes.layout );
	const [ itemSlugs, setItemSlugs ] = useState( attributes.itemSlugs );

	const {
		assetData,
		type,
		slug,
		align,
		image,
		containerid,
		margin,
		clear,
		expiration,
		ajax,
		scheme,
		layout,
		width,
		search,
		tag,
		author,
		user,
		browse,
		per_page,
		preview,
		cols,
		sortby,
		sort,
		colGap,
		rowGap,
	} = attributes;

	useEffect( () => {
		// The query route stores cards in assetData and leaves html empty.
		// An empty html value is not an unconfigured block.
		const hasSavedCards = Array.isArray( assetData ) && assetData.length > 0;
		if ( hasSavedCards ) {
			setLoading( false );
		} else {
			setLoading( true );
		}

		// Apply defaults.
		if ( ! attributes.defaultsApplied && 'default' === attributes.scheme ) {
			setAttributes( {
				defaultsApplied: true,
				scheme: wppic.default_scheme,
				layout: wppic.default_layout,
			} );
		}
	}, [] );

	useEffect( () => {
		setAttributes( { uniqueId: generatedUniqueId } );
	}, [] );

	const pluginOnClick = ( event ) => {
		if ( '' !== type ) {
			setCardLoading( true );
			const rest_url = wppic.rest_url + 'wppic/v2/get_query/';
			axios
				.get( rest_url, {
					params: {
						type: attributes.type,
						slug: attributes.slug,
						align: attributes.align,
						image: attributes.image,
						containerid: attributes.containerid,
						margin: attributes.margin,
						clear: attributes.clear,
						expiration: attributes.expiration,
						ajax: attributes.ajax,
						scheme: attributes.scheme,
						layout: attributes.layout,
						search: attributes.search,
						tag: attributes.tag,
						author: attributes.author,
						user: attributes.user,
						browse: attributes.browse,
						per_page: attributes.per_page,
						cols: attributes.cols,
						sortby: attributes.sortby,
						sort: attributes.sort,
						searchBy,
					},
					headers: {
						'X-WP-Nonce': wppic.rest_nonce,
					},
				} )
				.then( ( response ) => {
					// Now Set State
					setLoading( false );
					setCardLoading( false );
					if ( response.data.success ) {
						setAttributes( {
							assetData: response.data.data.api_response,
							html: response.data.data.html,
						} );
					} else {
						setAttributes( {
							assetData: [],
							html: '',
						} );
						setNoData( true );
						setLoading( true );
					}
				} )
				.catch( () => {
					setLoading( false );
					setCardLoading( false );
					setAttributes( {
						assetData: [],
						html: '',
					} );
					setNoData( true );
					setLoading( true );
				} );
		}
	};

	const outputInfoCards = () => {
		return assetData.map( ( cardData, key ) => {
			let textValue = '';

			// Check to see if slug is in the itemSlugs array.
			if ( cardData.slug in itemSlugs ) {
				if ( '' !== itemSlugs[ cardData.slug ] && false !== itemSlugs[ cardData.slug ] ) {
					textValue = itemSlugs[ cardData.slug ];
					// Merge with card data.
					cardData = { ...cardData, name: textValue };
				} else if ( false === itemSlugs[ cardData.slug ] ) {
					return null;
				}
			}
			return (
				<Fragment key={ key }>
					{ 'flex' === layout && 'plugin' === type && (
						<PluginFlex
							scheme={ scheme }
							image={ image }
							data={ cardData }
							align={ align }
						/>
					) }
					{ 'card' === layout && 'plugin' === type && (
						<PluginCard
							scheme={ scheme }
							image={ image }
							data={ cardData }
							align={ align }
						/>
					) }
					{ 'large' === layout && 'plugin' === type && (
						<PluginLarge
							scheme={ scheme }
							image={ image }
							data={ cardData }
							align={ align }
						/>
					) }
					{ 'wordpress' === layout && 'plugin' === type && (
						<PluginWordPress
							scheme={ scheme }
							image={ image }
							data={ cardData }
							align={ align }
						/>
					) }
					{ 'ratings' === layout && 'plugin' === type && (
						<PluginRatingsCard
							scheme={ scheme }
							image={ image }
							data={ cardData }
							align={ align }
						/>
					) }
					{ 'flex' === layout && 'theme' === type && (
						<ThemeFlex
							scheme={ scheme }
							image={ image }
							data={ cardData }
							align={ align }
						/>
					) }
					{ 'wordpress' === layout && 'theme' === type && (
						<ThemeWordPress
							scheme={ scheme }
							image={ image }
							data={ cardData }
							align={ align }
						/>
					) }
					{ 'large' === layout && 'theme' === type && (
						<ThemeLarge
							scheme={ scheme }
							image={ image }
							data={ cardData }
							align={ align }
						/>
					) }
					{ 'card' === layout && 'theme' === type && (
						<ThemeCard
							scheme={ scheme }
							image={ image }
							data={ cardData }
							align={ align }
						/>
					) }
					{ 'ratings' === layout && 'theme' === type && (
						<ThemeRatingsCard
							scheme={ scheme }
							image={ image }
							data={ cardData }
							align={ align }
						/>
					) }
				</Fragment>
			);
		} );
	};

	const htmlToReactParser = new HtmlToReactParser();

	const resetSelect = [
		{
			icon: 'edit',
			title: __( 'Reset', 'wp-plugin-info-card' ),
			onClick: () => setLoading( true ),
		},
	];
	const clearOptions = [
		{ value: 'none', label: __( 'None', 'wp-plugin-info-card' ) },
		{ value: 'before', label: __( 'Before', 'wp-plugin-info-card' ) },
		{ value: 'after', label: __( 'After', 'wp-plugin-info-card' ) },
	];
	const ajaxOptions = [
		{ value: 'false', label: __( 'No', 'wp-plugin-info-card' ) },
		{ value: 'true', label: __( 'Yes', 'wp-plugin-info-card' ) },
	];
	const schemeOptions = [
		{ value: 'default', label: __( 'Default', 'wp-plugin-info-card' ) },
		{ value: 'scheme1', label: __( 'Scheme 1', 'wp-plugin-info-card' ) },
		{ value: 'scheme2', label: __( 'Scheme 2', 'wp-plugin-info-card' ) },
		{ value: 'scheme3', label: __( 'Scheme 3', 'wp-plugin-info-card' ) },
		{ value: 'scheme4', label: __( 'Scheme 4', 'wp-plugin-info-card' ) },
		{ value: 'scheme5', label: __( 'Scheme 5', 'wp-plugin-info-card' ) },
		{ value: 'scheme6', label: __( 'Scheme 6', 'wp-plugin-info-card' ) },
		{ value: 'scheme7', label: __( 'Scheme 7', 'wp-plugin-info-card' ) },
		{ value: 'scheme8', label: __( 'Scheme 8', 'wp-plugin-info-card' ) },
		{ value: 'scheme9', label: __( 'Scheme 9', 'wp-plugin-info-card' ) },
		{ value: 'scheme10', label: __( 'Scheme 10', 'wp-plugin-info-card' ) },
		{ value: 'scheme11', label: __( 'Scheme 11', 'wp-plugin-info-card' ) },
		{ value: 'scheme12', label: __( 'Scheme 12', 'wp-plugin-info-card' ) },
		{ value: 'scheme13', label: __( 'Scheme 13', 'wp-plugin-info-card' ) },
		{ value: 'scheme14', label: __( 'Scheme 14', 'wp-plugin-info-card' ) },
	];
	const layoutOptions = [
		{ value: 'card', label: __( 'Card', 'wp-plugin-info-card' ) },
		{ value: 'large', label: __( 'Large', 'wp-plugin-info-card' ) },
		{ value: 'wordpress', label: __( 'WordPress', 'wp-plugin-info-card' ) },
		{ value: 'flex', label: __( 'Flex', 'wp-plugin-info-card' ) },
		{ value: 'ratings', label: __( 'Ratings', 'wp-plugin-info-card' ) },
	];
	const customThemeOptions = [
		{ value: '', label: __( 'None', 'wp-plugin-info-card' ) },
		{ value: 'url', label: __( 'URL', 'wp-plugin-info-card' ) },
		{ value: 'name', label: __( 'Name', 'wp-plugin-info-card' ) },
		{ value: 'version', label: __( 'Version', 'wp-plugin-info-card' ) },
		{ value: 'author', label: __( 'Author', 'wp-plugin-info-card' ) },
		{
			value: 'screenshot_url',
			label: __( 'Screenshot URL', 'wp-plugin-info-card' ),
		},
		{ value: 'rating', label: __( 'Ratings', 'wp-plugin-info-card' ) },
		{
			value: 'num_ratings',
			label: __( 'Number of Ratings', 'wp-plugin-info-card' ),
		},
		{
			value: 'active_installs',
			label: __( 'Active Installs', 'wp-plugin-info-card' ),
		},
		{
			value: 'last_updated',
			label: __( 'Last Updated', 'wp-plugin-info-card' ),
		},
		{ value: 'homepage', label: __( 'Homepage', 'wp-plugin-info-card' ) },
		{
			value: 'download_link',
			label: __( 'Download Link', 'wp-plugin-info-card' ),
		},
	];
	const widthOptions = [
		{ value: 'none', label: __( 'Default', 'wp-plugin-info-card' ) },
		{ value: 'full', label: __( 'Full Width', 'wp-plugin-info-card' ) },
	];

	const triggerPluginClick = debounce( ( event ) => {
		pluginOnClick( event );
	}, 1100 );

	/**
	 * Output slug options for overriding the plugin title per plugin.
	 *
	 * @param {Array} cardDataArray The card data array.
	 * @param {Array} oldItemSlugs  The old item slugs.
	 */
	const outputSlugs = ( cardDataArray, oldItemSlugs ) => {
		return cardDataArray.map( ( cardData, key ) => {
			let textValue = '';
			// Check to see if slug is in the itemSlugs array.
			if ( cardData.slug in oldItemSlugs ) {
				textValue = oldItemSlugs[ cardData.slug ];
			}

			// Determine if plugin is displaying or not.
			let displayPlugin = true;
			if ( cardData.slug in oldItemSlugs ) {
				if ( false === oldItemSlugs[ cardData.slug ] ) {
					displayPlugin = false;
				}
			}

			const panelBodyTitle = escapeAttribute( decodeEntities( cardData.name ) ) + ' (' + ( displayPlugin ? __( 'Enabled', 'wp-plugin-info-card' ) : __( 'Disabled', 'wp-plugin-info-card' ) ) + ')';
			return (
				<PanelBody
					title={ panelBodyTitle }
					key={ key }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Display Plugin', 'wp-plugin-info-card' ) }
						checked={ displayPlugin }
						onChange={ ( value ) => {
							let newTextValue = '';
							if ( false === value ) {
								newTextValue = false;
							}
							const itemSlugsTemp = itemSlugs;
							itemSlugsTemp[ cardData.slug ] = newTextValue; // Slug can be false, or have a title override.
							setItemSlugs( { ...itemSlugsTemp } );
							setAttributes( { itemSlugs: { ...itemSlugsTemp } } );
						} }
					/>
					{ displayPlugin && (
						<>
							<TextControl
								label={ __( 'Override Title', 'wp-plugin-info-card' ) }
								value={ itemSlugs[ cardData.slug ] || textValue }
								onChange={ ( value ) => {
									const itemSlugsTemp = itemSlugs;
									itemSlugsTemp[ cardData.slug ] = value;
									setItemSlugs( { ...itemSlugsTemp } );
									setAttributes( { itemSlugs: { ...itemSlugsTemp } } );
								} }
							/>
						</>
					) }
				</PanelBody>
			);
		} );
	};

	const inspectorControls = (
		<InspectorControls>
			<PanelBody
				title={ __( 'Query Options', 'wp-plugin-info-card' ) }
			>
				<TextControl
					type="number"
					label={ __(
						'Per Page',
						'wp-plugin-info-card',
					) }
					value={ per_page }
					onChange={ ( value ) => {
						setAttributes( {
							per_page: value,
						} );
						attributes.per_page = value;

						triggerPluginClick();
					} }
					help={ __( 'Set how many cards to return.', 'wp-plugin-info-card' ) }
				/>
				<SelectControl
					label={ __(
						'Sort results by:',
						'wp-plugin-info-card',
					) }
					options={ [
						{
							label: __(
								'None',
								'wp-plugin-info-card',
							),
							value: 'none',
						},
						{
							label: __(
								'Active Installs (Plugins only)',
								'wp-plugin-info-card',
							),
							value: 'active_installs',
						},
						{
							label: __(
								'Downloads',
								'wp-plugin-info-card',
							),
							value: 'downloaded',
						},
						{
							label: __(
								'Last Updated',
								'wp-plugin-info-card',
							),
							value: 'last_updated',
						},
						{
							label: __(
								'Rating',
								'wp-plugin-info-card',
							),
							value: 'rating',
						},
					] }
					value={ sortby }
					onChange={ ( value ) => {
						setAttributes( {
							sortby: value,
						} );
						attributes.sortby = value;
						triggerPluginClick();
					} }
				/>
				<SelectControl
					label={ __(
						'Sort Order:',
						'wp-plugin-info-card',
					) }
					options={ [
						{
							label: __(
								'ASC',
								'wp-plugin-info-card',
							),
							value: 'ASC',
						},
						{
							label: __(
								'DESC',
								'wp-plugin-info-card',
							),
							value: 'DESC',
						},
					] }
					value={ sort }
					onChange={ ( value ) => {
						setAttributes( {
							sort: value,
						} );
						attributes.sort = value;
						triggerPluginClick();
					} }
				/>
				<>
					<SelectControl
						label={ __(
							'Columns',
							'wp-plugin-info-card',
						) }
						options={ [
							{
								label: __(
									'1',
									'wp-plugin-info-card',
								),
								value: '1',
							},
							{
								label: __(
									'2',
									'wp-plugin-info-card',
								),
								value: '2',
							},
							{
								label: __(
									'3',
									'wp-plugin-info-card',
								),
								value: '3',
							},
						] }
						value={ cols }
						onChange={ ( value ) => {
							setAttributes( {
								cols: value,
							} );
						} }
					/>
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
			</PanelBody>
			<PanelBody
				title={ __( 'WP Plugin Info Card', 'wp-plugin-info-card' ) }
			>
				<SelectControl
					label={ __( 'Scheme', 'wp-plugin-info-card' ) }
					options={ schemeOptions }
					value={ scheme }
					onChange={ ( value ) => {
						setAttributes( { scheme: value } );
					} }
				/>
				<SelectControl
					label={ __( 'Layout', 'wp-plugin-info-card' ) }
					options={ layoutOptions }
					value={ layout }
					onChange={ ( value ) => {
						setAttributes( { layout: value } );
					} }
				/>
				<MediaUpload
					onSelect={ ( imageObject ) => {
						setAttributes( {
							image: imageObject.url,
						} );
						attributes.image = imageObject.url;
						pluginOnClick();
					} }
					type="image"
					value={ image }
					render={ ( { open } ) => (
						<>
							<button
								className="components-button is-button"
								onClick={ open }
							>
								{ __( 'Upload Image!', 'wp-plugin-info-card' ) }
							</button>
							{ image && (
								<>
									<div>
										<img
											src={ image }
											alt={ __(
												'Plugin Card Image',
												'wp-plugin-info-card',
											) }
											width="250"
											height="250"
										/>
									</div>
									<div>
										<button
											className="components-button is-button"
											onClick={ ( event ) => {
												setAttributes( {
													image: '',
												} );
												attributes.image = '';
												pluginOnClick();
											} }
										>
											{ __(
												'Reset Image',
												'wp-plugin-info-card',
											) }
										</button>
									</div>
								</>
							) }
						</>
					) }
				/>
				<TextControl
					label={ __( 'Container ID', 'wp-plugin-info-card' ) }
					type="text"
					value={ containerid }
					onChange={ ( value ) => {
						setAttributes( { containerid: value } );
						attributes.containerId = value;
						setTimeout( function() {
							pluginOnClick();
						}, 5000 );
					} }
				/>
				<TextControl
					label={ __( 'Margin', 'wp-plugin-info-card' ) }
					type="text"
					value={ margin }
					onChange={ ( value ) => {
						setAttributes( { margin: value } );
						attributes.margin = value;
						setTimeout( function() {
							pluginOnClick();
						}, 5000 );
					} }
				/>
				<SelectControl
					label={ __( 'Clear', 'wp-plugin-info-card' ) }
					options={ clearOptions }
					value={ clear }
					onChange={ ( value ) => {
						setAttributes( { clear: value } );
					} }
				/>
				<TextControl
					label={ __(
						'Expiration in minutes',
						'wp-plugin-info-card',
					) }
					type="number"
					value={ expiration }
					onChange={ ( value ) => {
						setAttributes( { expiration: value } );
					} }
				/>
				<SelectControl
					label={ __( 'Load card via Ajax?', 'wp-plugin-info-card' ) }
					options={ ajaxOptions }
					value={ ajax }
					onChange={ ( value ) => {
						setAttributes( { ajax: value } );
					} }
				/>
			</PanelBody>
			{ outputSlugs( assetData, itemSlugs ) }
		</InspectorControls>
	);

	const categories = [
		{
			value: 'featured',
			label: __( 'Featured', 'wp-plugin-info-card' ),
		},
		{
			value: 'popular',
			label: __( 'Popular', 'wp-plugin-info-card' ),
		},
		{
			value: 'updated',
			label: __( 'Updated', 'wp-plugin-info-card' ),
		},
		{
			value: 'favorites',
			label: __( 'Favorites', 'wp-plugin-info-card' ),
		},
	];

	const block = (
		<>
			<>
				{ loading && (
					<div className="wppic-query-block wppic-query-block-panel">
						<div className="wppic-block-svg">
							<Logo size="75" />
						</div>
						<div className="wp-pic-tab-panel">
							{ noData && (
								<div className="wppic-no-data">
									<Notice
										status="error"
										isDismissible={ false }
									>
										{ __(
											'No data found. Please check your query.',
											'wp-plugin-info-card',
										) }
									</Notice>
								</div>
							) }
							<TabPanel
								activeClass="active-tab"
								initialTabName={ type }
								tabs={ [
									{
										title: __( 'Plugin', 'wp-plugin-info-card' ),
										name: 'plugin',
										className: 'wppic-tab-plugin wppic-tab-slug',
									},
									{
										title: __(
											'Theme',
											'wp-plugin-info-card',
										),
										name: 'theme',
										className: 'wppic-tab-theme',
									},
								] }
								onSelect={ ( tabName ) => {
									setAttributes( {
										type: tabName,
									} );
								} }
							>
								{ ( tab ) => {
									let tabContent;
									if ( 'plugin' === tab.name || 'theme' === tab.name ) {
										tabContent = (
											<>
												<SelectControl
													label={ __( 'Search by:', 'wp-plugin-info-card' ) }
													value={ searchBy }
													options={ [
														{
															label: __( 'General', 'wp-plugin-info-card' ),
															value: 'general',
														},
														{
															label: __( 'Plugin or Theme Author', 'wp-plugin-info-card' ),
															value: 'author',
														},
														{
															label: __( 'An Author\'s Favorites', 'wp-plugin-info-card' ),
															value: 'favorites',
														},
														{
															label: __( 'By Category', 'wp-plugin-info-card' ),
															value: 'category',
														},
														{
															label: __( 'By Tag', 'wp-plugin-info-card' ),
															value: 'tag',
														},
													] }
													onChange={ ( value ) => {
														setSearchBy( value );
													} }
												/>
											</>
										);
										return tabContent;
									}
								} }
							</TabPanel>
							{ 'category' !== searchBy && (
								<TextControl
									label={ __(
										'Search',
										'wp-plugin-info-card',
									) }
									value={ searchValue }
									onChange={ ( value ) => {
										setSearchValue( value );
									} }
									placeholder={ __( 'Enter your search…', 'wp-plugin-info-card' ) }
								/>
							) }
							{
								'category' === searchBy && (
									<>
										<SelectControl
											label={ __(
												'Browse',
												'wp-plugin-info-card',
											) }
											options={ [
												{
													label: __(
														'None',
														'wp-plugin-info-card',
													),
													value: '',
												},
												{
													label: __(
														'Featured',
														'wp-plugin-info-card',
													),
													value: 'featured',
												},
												{
													label: __(
														'Updated',
														'wp-plugin-info-card',
													),
													value: 'updated',
												},
												{
													label: __(
														'Favorites',
														'wp-plugin-info-card',
													),
													value: 'favorites',
												},
												{
													label: __(
														'Popular',
														'wp-plugin-info-card',
													),
													value: 'popular',
												},
											] }
											value={ browse }
											onChange={ ( value ) => {
												setAttributes( {
													browse: value,
												} );
											} }
										/>
									</>
								)
							}
						</div>
						<div className="wp-pic-gutenberg-button">
							<Button
								iconSize={ 20 }
								icon={ <Logo size="25" /> }
								isSecondary
								id="wppic-input-submit"
								onClick={ ( event ) => {
									event.preventDefault();
									setLoading( false );
									setNoData( false );

									// Clear attributes.
									setAttributes( {
										browse: '',
										tag: '',
										author: '',
										user: '',
										search: '',
									} );

									// Set attributes on search.
									switch ( searchBy ) {
										case 'category':
											setAttributes( {
												browse,
											} );
											attributes.browse = browse;
											break;
										case 'tag':
											setAttributes( {
												tag: searchValue,
											} );
											attributes.tag = searchValue;
											break;
										case 'author':
											setAttributes( {
												author: searchValue,
											} );
											attributes.author = searchValue;
											break;
										case 'favorites':
											setAttributes( {
												user: searchValue,
											} );
											attributes.user = searchValue;
											break;
										case 'general':
										default:
											setAttributes( {
												search: searchValue,
											} );
											attributes.search = searchValue;
											break;
									}
									pluginOnClick( event );
								} }
							>
								{ __(
									'Query and Configure',
									'wp-plugin-info-card',
								) }
							</Button>
						</div>
					</div>
				) }
				{ cardLoading && (
					<>
						<div className="wppic-loading-placeholder">
							<div className="wppic-loading">
								<Logo size="45" />
								<br />
								<div className="wppic-spinner">
									<Spinner />
								</div>
							</div>
						</div>
					</>
				) }
				{ ! loading && ! cardLoading && (
					<>
						{ inspectorControls }
						<BlockControls>
							<ToolbarGroup controls={ resetSelect } />
							{ 'flex' === layout && (
								<BlockAlignmentToolbar
									value={ align }
									onChange={ ( value ) => {
										setAttributes( {
											align: value,
										} );
										pluginOnClick( value );
									} }
								></BlockAlignmentToolbar>
							) }
							{ 'card' === layout && (
								<AlignmentToolbar
									value={ align }
									onChange={ ( value ) => {
										setAttributes( {
											align: value,
										} );
										pluginOnClick( value );
									} }
								></AlignmentToolbar>
							) }
							<ToolbarGroup>
								<ToolbarItem as="button">
									{ ( toolbarItemHTMLProps ) => (
										<DropdownMenu
											toggleProps={ toolbarItemHTMLProps }
											label={ __(
												'Select Color Scheme',
												'wp-plugin-info-card',
											) }
											icon="admin-customizer"
										>
											{ ( { onClose } ) => (
												<Fragment>
													<MenuItemsChoice
														choices={ schemeOptions }
														onSelect={ ( value ) => {
															setAttributes( {
																scheme: value,
															} );
															setCurrentScheme( value );
															onClose();
														} }
														value={ currentScheme }
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
														choices={ layoutOptions }
														onSelect={ ( value ) => {
															setAttributes( {
																layout: value,
															} );
															setCurrentLayout( value );
															onClose();
														} }
														value={ currentLayout }
													/>
												</Fragment>
											) }
										</DropdownMenu>
									) }
								</ToolbarItem>
							</ToolbarGroup>
						</BlockControls>
						<div
							className={
								'' !== width ? 'wp-pic-full-width' : ''
							}
						>
							<div
								className={
									classnames(
										'has-grid-layout',
									) }
							>
								<div className={ `wp-query-plugin-info-card cols-${ cols }` }>
									{ outputInfoCards() }
								</div>
							</div>
						</div>
					</>
				) }
			</>
		</>
	);

	const blockProps = useBlockProps( {
		className: classnames( `wp-plugin-info-card-query align${ align }` ),
	} );
	if ( preview ) {
		return (
			<>
				<img
					src={ wppic.query_preview }
					style={ { width: '100%', height: 'auto' } }
				/>
			</>
		);
	}

	const styles = `
		#${ attributes.uniqueId } .wp-query-plugin-info-card {
			display: grid;
			column-gap: ${ colGap }px;
			row-gap: ${ rowGap }px;
		}
	`;
	return (
		<div { ...blockProps } id={ attributes.uniqueId }>
			<style>{ styles }</style>
			{ block }
		</div>
	);
};

export default WP_Plugin_Card_Query;
