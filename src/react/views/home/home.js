import React, { useState, useEffect, Suspense, useCallback, useRef } from 'react';
import { useForm, Controller, useWatch, useFormState } from 'react-hook-form';
import classNames from 'classnames';
import { useAsyncResource } from 'use-async-resource';
import { __ } from '@wordpress/i18n';
import { useDrag, useDrop } from 'react-dnd';
import { isURL, cleanForSlug } from '@wordpress/url';
import BeatLoader from 'react-spinners/BeatLoader';
import SaveResetButtons from '../../components/SaveResetButtons';
import { Fancybox } from '@fancyapps/ui';

import {
	TextControl,
	Button,
	ToggleControl,
	SelectControl,
	BaseControl,
} from '@wordpress/components';
import {
	AlertCircle,
	PlusCircle,
	ExternalLink,
	DatabaseZap,
	Cog,
	BookText,
	XCircle,
	Loader2,
	Database,
	ClipboardCheck,
	Plug2,
	Paintbrush2,
	Globe,
	Star,
} from 'lucide-react';
import SendCommand from '../../utils/SendCommand';
import Notice from '../../components/Notice';
import SnackPop from '../../components/SnackPop';
import usePluginPreview from '../../hooks/usePluginPreview';
import CacheButton from '../../components/CacheButton';

const OrgAsset = ( { type, slug, index, moveCallback, removeCallback } ) => {
	const ref = useRef( null );
	const [ spec, dropRef ] = useDrop( {
		accept: type,
		drop: ( item, monitor ) => {
			if ( ! monitor.didDrop() ) {
				if ( item.index !== index ) {
					moveCallback( item.index, index );
				}
			}
		},
		collect: ( monitor ) => ( {
			canDrop: monitor.canDrop(),
			isOver: monitor.isOver(),
			handlerId: monitor.getHandlerId(),
		} ),
		hover( item, monitor ) {
			if ( ! ref.current ) {
				return;
			}
			const dragIndex = item.index;
			const hoverIndex = index;
			// Don't replace items with themselves
			if ( dragIndex === hoverIndex ) {
				return;
			}
			// Determine rectangle on screen
			const hoverBoundingRect = ref.current?.getBoundingClientRect();
			// Get vertical middle
			const hoverMiddleY =
				( hoverBoundingRect.bottom - hoverBoundingRect.top ) / 2;
			// Determine mouse position
			const clientOffset = monitor.getClientOffset();
			// Get pixels to the top
			const hoverClientY = clientOffset.y - hoverBoundingRect.top;
			// Only perform the move when the mouse has crossed half of the items height
			// When dragging downwards, only move when the cursor is below 50%
			// When dragging upwards, only move when the cursor is above 50%
			// Dragging downwards
			if ( dragIndex < hoverIndex && hoverClientY < hoverMiddleY ) {
				return;
			}
			// Dragging upwards
			if ( dragIndex > hoverIndex && hoverClientY > hoverMiddleY ) {
				return;
			}
			// Time to actually perform the action
			moveCallback( dragIndex, hoverIndex );
			// Note: we're mutating the monitor item here!
			// Generally it's better to avoid mutations,
			// but it's good here for the sake of performance
			// to avoid expensive index searches.
			item.index = hoverIndex;
		},
	} );
	const [ { opacity, isDragging }, dragRef ] = useDrag( {
		type,
		item: () => {
			return { index };
		},
		collect: ( monitor ) => ( {
			opacity: monitor.isDragging() ? 1 : 1,
			isDragging: monitor.isDragging(),
		} ),
	} );
	const dragDropRef = dragRef( dropRef( ref ) );
	const classes = classNames( 'wppic-plugin-drag', {
		'is-dragging': isDragging,
		'can-drop': spec.canDrop,
		'is-over': spec.isOver,
	} );

	/**
	 * Get the label for the remove button.
	 *
	 * @return {string} The label for the remove button.
	 */
	const getRemoveLabel = () => {
		switch ( type ) {
			case 'plugin':
				return __( 'Remove Plugin', 'wp-plugin-info-card' );
			case 'theme':
				return __( 'Remove Theme', 'wp-plugin-info-card' );
		}
		return '';
	};
	return (
		<div
			className={ classes }
			ref={ dragDropRef }
			style={ { opacity } }
			data-handler-id={ spec.handlerId }
		>
			<div className="wppic-org-asset-icon">
				{ 'plugin' === type && (
					<>
						<Plug2 />
					</>
				) }
				{ 'theme' === type && (
					<>
						<Paintbrush2 />
					</>
				) }
			</div>
			<div className="wppic-org-asset-label">{ slug }</div>
			<Button
				className="button-reset wppic-close-btn"
				variant="secondary"
				label={ getRemoveLabel() }
				onClick={ () => {
					removeCallback( slug );
				} }
				icon={ () => <XCircle /> }
			/>
		</div>
	);
};

const AddPlugin = ( props ) => {
	const [ value, setValue ] = useState( '' );
	const [ isChecking, setIsChecking ] = useState( false );
	const [ isError, setIsError ] = useState( false );
	const [ pluginInput, setPluginInput ] = useState( null );
	const [ errorMessage, setErrorMessage ] = useState( '' );

	const checkPlugin = async ( slug ) => {
		setIsChecking( true );
		const checkPluginPromise = SendCommand( 'wppic_check_plugin', { slug, nonce: wppicAdminHome.checkPluginNonce } );
		checkPluginPromise.catch( () => {
			setErrorMessage( __( 'There has been an error communicating with the server. Please try again.', 'wp-plugin-info-card' ) );
			setIsError( true );
			setIsChecking( false );
			pluginInput.focus();
		} );
		const response = await checkPluginPromise;

		setIsChecking( false );
		const { success } = response.data;
		if ( success ) {
			setValue( '' );
			pluginInput.focus();
			props.onChange( slug );
		} else {
			setErrorMessage( __( 'Could not find plugin. Please try again with a different slug.', 'wp-plugin-info-card' ) );
			setIsError( true );
			pluginInput.focus();
		}
	};

	const getPluginLabel = () => {
		if ( isChecking ) {
			return __( 'Checking plugin…', 'wp-plugin-info-card' );
		}
		return __( 'Add Plugin', 'wp-plugin-info-card' );
	};
	return (
		<div className="wppic-add-plugin-wrapper">
			<TextControl
				label={ __( 'Plugin Slug', 'wp-plugin-info-card' ) }
				value={ value }
				onChange={ ( newValue ) => {
					setIsError( false );
					if ( isURL( newValue ) ) {
						return;
					}
					setValue( newValue );
				} }
				onPaste={ ( event ) => {
					// Get contents from clipboard.
					const clipboardData = event.clipboardData
						.getData( 'text/plain' )
						.trim();

					if ( isURL( clipboardData ) ) {
						// Extract out the slug from the URL.
						const urlRegex = /([^/]*)\/$/;
						const newSlug = urlRegex.exec(
							clipboardData,
						)[ 1 ];
						setValue( newSlug );
					}
				} }
				help={ __(
					'Enter the plugin slug. Example: "wp-plugin-info-card".',
					'wp-plugin-info-card',
				) }
				ref={ setPluginInput }
			/>
			<Button
				className="wppic-btn has-icon-right "
				variant="primary"
				label={ getPluginLabel() }
				disabled={ isChecking || value === '' }
				onClick={ () => {
					const sanitizedSlug = cleanForSlug( value );
					if ( '' === sanitizedSlug ) {
						return;
					}
					// Check for duplicates.
					const { list } = props.formValues;
					if ( typeof list !== 'undefined' && list.includes( sanitizedSlug ) ) {
						setErrorMessage( __( 'This plugin is already listed.', 'wp-plugin-info-card' ) );
						setIsError( true );
						return;
					}
					checkPlugin( sanitizedSlug );
				} }
				icon={ () => <PlusCircle /> }
			>
				{ getPluginLabel() }
			</Button>
			{ isError && (
				<Notice
					message={ errorMessage }
					status="error"
					politeness="assertive"
					inline={ false }
					icon={ () => <AlertCircle /> }
				/>
			) }
		</div>
	);
};

const AddTheme = ( props ) => {
	const [ value, setValue ] = useState( '' );
	const [ isChecking, setIsChecking ] = useState( false );
	const [ isError, setIsError ] = useState( false );
	const [ themeInput, setThemeInput ] = useState( null );
	const [ errorMessage, setErrorMessage ] = useState( '' );

	const checkPlugin = async ( slug ) => {
		setIsChecking( true );
		const checkThemePromise = SendCommand( 'wppic_check_theme', { slug, nonce: wppicAdminHome.checkThemeNonce } );
		checkThemePromise.catch( () => {
			setErrorMessage( __( 'There has been an error communicating with the server. Please try again.', 'wp-plugin-info-card' ) );
			setIsError( true );
			setIsChecking( false );
			themeInput.focus();
		} );
		const response = await checkThemePromise;

		setIsChecking( false );
		const { success } = response.data;
		if ( success ) {
			setValue( '' );
			themeInput.focus();
			props.onChange( slug );
		} else {
			setErrorMessage( __( 'Could not find this theme. Please try again with a different slug.', 'wp-plugin-info-card' ) );
			setIsError( true );
			themeInput.focus();
		}
	};

	const getThemeLabel = () => {
		if ( isChecking ) {
			return __( 'Checking theme…', 'wp-plugin-info-card' );
		}
		return __( 'Add Theme', 'wp-plugin-info-card' );
	};
	return (
		<div className="wppic-add-plugin-wrapper">
			<TextControl
				label={ __( 'Theme Slug', 'wp-plugin-info-card' ) }
				value={ value }
				onChange={ ( newValue ) => {
					setIsError( false );
					if ( isURL( newValue ) ) {
						return;
					}
					setValue( newValue );
				} }
				onPaste={ ( event ) => {
					// Get contents from clipboard.
					const clipboardData = event.clipboardData
						.getData( 'text/plain' )
						.trim();

					if ( isURL( clipboardData ) ) {
						// Extract out the slug from the URL.
						const urlRegex = /([^/]*)\/$/;
						const newSlug = urlRegex.exec(
							clipboardData,
						)[ 1 ];
						setValue( newSlug );
					}
				} }
				help={ __(
					'Enter the theme slug. Example: "twentytwentyfour".',
					'wp-plugin-info-card',
				) }
				ref={ setThemeInput }
			/>
			<Button
				className="wppic-btn has-icon-right "
				variant="primary"
				label={ getThemeLabel() }
				disabled={ isChecking || value === '' }
				onClick={ () => {
					const sanitizedSlug = cleanForSlug( value );
					if ( '' === sanitizedSlug ) {
						return;
					}
					// Check for duplicates.
					const themeList = props.formValues[ 'theme-list' ];
					if ( typeof themeList !== 'undefined' && themeList.includes( sanitizedSlug ) ) {
						setErrorMessage( __( 'This theme is already listed.', 'wp-plugin-info-card' ) );
						setIsError( true );
						return;
					}
					checkPlugin( sanitizedSlug );
				} }
				icon={ () => <PlusCircle /> }
			>
				{ getThemeLabel() }
			</Button>
			{ isError && (
				<Notice
					message={ errorMessage }
					status="error"
					politeness="assertive"
					inline={ false }
					icon={ () => <AlertCircle /> }
				/>
			) }
		</div>
	);
};

const HomeScreen = ( props ) => {
	const [ homeOptions, setHomeOptions ] = useState( null );

	useEffect( () => {
		if ( homeOptions ) {
			return;
		}

		const cachedOptions = localStorage.getItem( 'wppic_home_options' );
		const cachedTimestamp = localStorage.getItem( 'wppic_home_options_timestamp' );

		if ( cachedOptions && cachedTimestamp ) {
			// Do version check.
			const currentVersion = window?.wppicAdmin?.pluginVersion;
			const cachedJson = JSON.parse( cachedOptions );
			const cachedVersion = cachedJson.version;
			if ( currentVersion !== cachedVersion ) {
				localStorage.removeItem( 'wppic_home_options' );
				localStorage.removeItem( 'wppic_home_options_timestamp' );
			}
			const currentTime = new Date().getTime();
			const cacheExpiration = parseInt( cachedTimestamp ) + 3600000;
			if ( currentTime < cacheExpiration ) {
				setHomeOptions( cachedJson );
				return;
			}
		}

		const fetchOptions = async () => {
			const response = await SendCommand( 'wppic_get_home_options', {
				nonce: wppicAdminHome.getNonce,
			} );
			const { success, data } = response.data;
			if ( success ) {
				// Save to local storage.
				localStorage.setItem( 'wppic_home_options', JSON.stringify( data ) );
				localStorage.setItem( 'wppic_home_options_timestamp', new Date().getTime().toString() );

				setHomeOptions( data );
			}
		};
		// Fetch options.
		fetchOptions();
	}, [] );

	if ( ! homeOptions ) {
		return (
			<div className="wppic-admin-panel-loading">
				<h2>{ __( 'Loading Options', 'wp-plugin-info-card' ) }</h2>
				<BeatLoader color={ '#333' } loading={ true } cssOverride={ true } size={ 25 } speedMultiplier={ 0.65 } />
			</div>
		);
	}

	return (
		<Interface data={ homeOptions } { ...props } />
	);
};

const Interface = ( props ) => {
	const { data } = props;
	const {
		control,
		handleSubmit,
		getValues,
		reset,
		setValue,
		setError,
		trigger,
	} = useForm( {
		defaultValues: {
			default_layout: data.default_layout,
			colorscheme: data.colorscheme,
			widget: data.widget,
			ajax: data.ajax,
			enqueue: data.enqueue,
			credit: data.credit,
			cache_expiration: data.cache_expiration,
			list: data.list ?? [],
			'theme-list': data[ 'theme-list' ] ?? [],
			enable_rest_api: data.enable_rest_api ?? false,
			restrict_rest_api: data.restrict_rest_api ?? false,
			saveNonce: wppicAdminHome.saveNonce,
			resetNonce: wppicAdminHome.resetNonce,
			restEndpointEnabled: wppicAdminHome?.restEndpointEnabled ?? false,
		},
	} );
	const [ reviewNagDismissed, setReviewNagDismissed ] = useState( Boolean( wppicAdminHome.reviewNagDismissed ) );
	const formValues = useWatch( { control } );
	const { errors, isDirty, dirtyFields } = useFormState( {
		control,
	} );

	const { getPreview, previewReady } = usePluginPreview();

	/**
	 * Move a plugin in the list.
	 *
	 * @param {number} dragIndex  The index of the plugin being dragged.
	 * @param {number} hoverIndex The index of the plugin being hovered.
	 */
	const movePlugin = useCallback( ( dragIndex, hoverIndex ) => {
		const prevPlugins = getValues( 'list' );
		const dragItem = prevPlugins[ dragIndex ];
		const hoverItem = prevPlugins[ hoverIndex ];
		const newPlugins = [];
		prevPlugins.forEach( ( plugin, index ) => {
			if ( index !== dragIndex && index !== hoverIndex ) {
				newPlugins.push( plugin );
			} else {
				if ( index === hoverIndex && dragIndex < hoverIndex ) {
					newPlugins.push( hoverItem );
					newPlugins.push( dragItem );
				}
				if ( index === hoverIndex && dragIndex > hoverIndex ) {
					newPlugins.push( dragItem );
					newPlugins.push( hoverItem );
				}
			}
		} );
		setValue( 'list', newPlugins );
	}, [] );

	/**
	 * Remove a plugin from the list.
	 *
	 * @param {string} slug The slug of the plugin to remove.
	 */
	const removePlugin = useCallback( ( slug ) => {
		const prevPlugins = getValues( 'list' );
		const newPlugins = prevPlugins.filter( ( plugin ) => {
			return plugin !== slug;
		} );
		setValue( 'list', newPlugins );
	}, [] );

	/**
	 * Move a theme in the list.
	 *
	 * @param {number} dragIndex  The index of the theme being dragged.
	 * @param {number} hoverIndex The index of the theme being hovered.
	 */
	const moveTheme = useCallback( ( dragIndex, hoverIndex ) => {
		const prevThemes = getValues( 'theme-list' );
		const dragItem = prevThemes[ dragIndex ];
		const hoverItem = prevThemes[ hoverIndex ];
		const newThemes = [];
		prevThemes.forEach( ( theme, index ) => {
			if ( index !== dragIndex && index !== hoverIndex ) {
				newThemes.push( theme );
			} else {
				if ( index === hoverIndex && dragIndex < hoverIndex ) {
					newThemes.push( hoverItem );
					newThemes.push( dragItem );
				}
				if ( index === hoverIndex && dragIndex > hoverIndex ) {
					newThemes.push( dragItem );
					newThemes.push( hoverItem );
				}
			}
		} );
		setValue( 'theme-list', newThemes );
	}, [] );

	/**
	 * Remove a plugin from the list.
	 *
	 * @param {string} slug The slug of the plugin to remove.
	 */
	const removeTheme = useCallback( ( slug ) => {
		const themePlugins = getValues( 'theme-list' );
		const newThemes = themePlugins.filter( ( plugin ) => {
			return plugin !== slug;
		} );
		setValue( 'theme-list', newThemes );
	}, [] );

	/**
	 * Handle form submission.
	 */
	const onSubmit = () => {
		// Update local storage by clearing it.
		localStorage.removeItem( 'wppic_home_options' );
		localStorage.removeItem( 'wppic_home_options_timestamp' );
	};

	/**
	 * Get the plugins.
	 */
	const getPlugins = () => {
		const { list } = formValues;
		if ( typeof list !== 'undefined' && list.length > 0 ) {
			return (
				<BaseControl
					label={ __( 'Plugins to Track', 'wp-plugin-info-card' ) }
					className="wppic-asset-list"
					id="wppic-asset-list-plugins"
					help={ __( 'Drag and drop to reorder.', 'wp-plugin-info-card' ) }
				>
					{ list.map( ( item, index ) => {
						return (
							<div className="wppic-org-asset-row" key={ index }>
								<Controller
									name={ `list[${ index }]` }
									key={ index }
									control={ control }
									rules={ { required: true } }
									render={ ( { field: { onChange, value } } ) => (
										<TextControl
											value={ value }
											onChange={ onChange }
											type="hidden"
										/>
									) }
								/>
								<div className="wppic-org-asset-row__actions">
									<OrgAsset type="plugin" slug={ item } index={ index } removeCallback={ removePlugin } moveCallback={ movePlugin } />
								</div>
							</div>
						);
					} ) }
				</BaseControl>
			);
		}
	};

	/**
	 * Get the themes.
	 */
	const getThemes = () => {
		const themeList = getValues( 'theme-list' );
		if ( typeof themeList !== 'undefined' && themeList.length > 0 ) {
			return (
				<BaseControl
					label={ __( 'Themes to Track', 'wp-plugin-info-card' ) }
					className="wppic-asset-list"
					id="wppic-asset-list-themes"
					help={ __( 'Drag and drop to reorder.', 'wp-plugin-info-card' ) }
				>
					<>
						{ themeList.map( ( item, index ) => {
							return (
								<div className="wppic-org-asset-row" key={ index }>
									<Controller
										name={ `theme-list[${ index }]` }
										key={ index }
										control={ control }
										rules={ { required: true } }
										render={ ( { field: { onChange, value } } ) => (
											<TextControl
												value={ value }
												onChange={ onChange }
												type="hidden"
											/>
										) }
									/>
									<div className="wppic-org-asset-row__actions">
										<OrgAsset type="theme" slug={ item } index={ index } removeCallback={ removeTheme } moveCallback={ moveTheme } />
									</div>
								</div>
							);
						} ) }
					</>
				</BaseControl>
			);
		}
	};

	return (
		<>
			<div className="wppic-admin-panel-container with-sidebar">
				<div className="wppic-admin-panel-options-wrapper">
					<div className="wppic-admin-panel-area">
						<div className="wppic-admin-panel-area__section">
							<h2>
								<Cog />
								{ __( 'Card Settings', 'wp-plugin-info-card' ) }
							</h2>
							<p className="description">
								{ __(
									'Set the defaults for WP Plugin Info Card.',
									'wp-plugin-info-card',
								) }
							</p>
							<form onSubmit={ handleSubmit( onSubmit ) }>
								<table className="form-table form-table-row-sections">
									<tbody>
										<tr>
											<th scope="row">
												{ __( 'Layout and Colors', 'wp-plugin-info-card' ) }
											</th>
											<td>
												<div className="wppic-admin-row">
													<Controller
														name="default_layout"
														control={ control }
														render={ ( { field: { onChange, value } } ) => (
															<SelectControl
																label={ __(
																	'Default Layout',
																	'wp-plugin-info-card',
																) }
																value={ value }
																options={ [
																	{
																		label: __( 'Card', 'wp-plugin-info-card' ),
																		value: 'default',
																	},
																	{
																		label: __(
																			'WordPress Appearance',
																			'wp-plugin-info-card',
																		),
																		value: 'wordpress',
																	},
																	{
																		label: __(
																			'Large Card Layout',
																			'wp-plugin-info-card',
																		),
																		value: 'large',
																	},
																	{
																		label: __(
																			'Flex Layout',
																			'wp-plugin-info-card',
																		),
																		value: 'flex',
																	},
																	{
																		label: __(
																			'Ratings',
																			'wp-plugin-info-card',
																		),
																		value: 'ratings',
																	},
																] }
																onChange={ onChange }
															/>
														) }
													/>
												</div>
												<div className="wppic-admin-row">
													<Controller
														name="colorscheme"
														control={ control }
														render={ ( { field: { onChange, value } } ) => (
															<SelectControl
																label={ __(
																	'Color Scheme',
																	'wp-plugin-info-card',
																) }
																value={ value }
																options={ [
																	/* Color Schemes 1-14 */
																	{
																		label: __( 'Default', 'wp-plugin-info-card' ),
																		value: 'default',
																	},
																	{
																		label: __(
																			'Color Scheme 1',
																			'wp-plugin-info-card',
																		),
																		value: 'scheme1',
																	},
																	{
																		label: __(
																			'Color Scheme 2',
																			'wp-plugin-info-card',
																		),
																		value: 'scheme2',
																	},
																	{
																		label: __(
																			'Color Scheme 3',
																			'wp-plugin-info-card',
																		),
																		value: 'scheme3',
																	},
																	{
																		label: __(
																			'Color Scheme 4',
																			'wp-plugin-info-card',
																		),
																		value: 'scheme4',
																	},
																	{
																		label: __(
																			'Color Scheme 5',
																			'wp-plugin-info-card',
																		),
																		value: 'scheme5',
																	},
																	{
																		label: __(
																			'Color Scheme 6',
																			'wp-plugin-info-card',
																		),
																		value: 'scheme6',
																	},
																	{
																		label: __(
																			'Color Scheme 7',
																			'wp-plugin-info-card',
																		),
																		value: 'scheme7',
																	},
																	{
																		label: __(
																			'Color Scheme 8',
																			'wp-plugin-info-card',
																		),
																		value: 'scheme8',
																	},
																	{
																		label: __(
																			'Color Scheme 9',
																			'wp-plugin-info-card',
																		),
																		value: 'scheme9',
																	},
																	{
																		label: __(
																			'Color Scheme 10',
																			'wp-plugin-info-card',
																		),
																		value: 'scheme10',
																	},
																	{
																		label: __(
																			'Color Scheme 11',
																			'wp-plugin-info-card',
																		),
																		value: 'scheme11',
																	},
																	{
																		label: __(
																			'Color Scheme 12',
																			'wp-plugin-info-card',
																		),
																		value: 'scheme12',
																	},
																	{
																		label: __(
																			'Color Scheme 13',
																			'wp-plugin-info-card',
																		),
																		value: 'scheme13',
																	},
																	{
																		label: __(
																			'Color Scheme 14',
																			'wp-plugin-info-card',
																		),
																		value: 'scheme14',
																	},
																] }
																onChange={ onChange }
															/>
														) }
													/>
												</div>
												{
													previewReady && (
														<div className="wppic-admin__control-row">
															<Button
																label={ __( 'Preview Plugin Card', 'wp-plugin-info-card' ) }
																className="wppic-btn is-secondary"
																data-src="#wppic-preview"
																data-fancybox
																onClick={ ( e ) => {
																	e.preventDefault();
																	Fancybox.show(
																		[
																			{
																				src: '#wppic-preview',
																				type: 'clone',
																				autoStart: true,
																			},
																		],
																	);
																} }
															>
																{ __( 'Preview', 'wp-ajaxify-comments' ) }
															</Button>
														</div>
													)
												}
												<div aria-hidden="true" style={ { display: 'none', width: '80%', height: 'auto' } } id="wppic-preview">
													<>
														{ getPreview( getValues( 'default_layout' ), getValues( 'colorscheme' ) ) }
													</>

												</div>
											</td>
										</tr>
										<tr>
											<th scope="row">
												{ __( 'Scripts and Styles', 'wp-plugin-info-card' ) }
											</th>
											<td>
												<div className="wppic-admin-row">
													<Controller
														name="enqueue"
														control={ control }
														render={ ( { field: { onChange, value } } ) => (
															<ToggleControl
																label={ __(
																	'Force Enqueue Scripts and Styles',
																	'wp-plugin-info-card',
																) }
																checked={ value }
																onChange={ onChange }
																help={ __(
																	'By default the plugin enqueues scripts (JS & CSS) only for pages containing the shortcode. If you wish to force scripts enqueuing, check this box.',
																	'wp-plugin-info-card',
																) }
															/>
														) }
													/>
												</div>
											</td>
										</tr>
										<tr>
											<th scope="row">{ __( 'Cache', 'wp-plugin-info-card' ) }</th>
											<td>
												<div className="wppic-admin-row">
													<Controller
														name="cache_expiration"
														control={ control }
														rules={ { required: true } }
														render={ ( { field: { onChange, value } } ) => (
															<TextControl
																label={ __(
																	'Cache Expiration',
																	'wp-plugin-info-card',
																) }
																value={ value }
																onChange={ onChange }
																help={ __(
																	'Set the cache expiration in seconds. Default is 3600 seconds (1 hour).',
																	'wp-plugin-info-card',
																) }
															/>
														) }
													/>
													{ 'required' === errors.cache_expiration?.type && (
														<Notice
															message={ __(
																'This is a required field.',
																'wp-plugin-info-card',
															) }
															status="error"
															politeness="assertive"
															inline={ false }
															icon={ () => <AlertCircle /> }
														/>
													) }
												</div>
											</td>
										</tr>
										<tr>
											<th scope="row">
												{ __( 'Dashboard Widget', 'wp-plugin-info-card' ) }
											</th>
											<td>
												<div className="wppic-admin-row">
													<Controller
														name="widget"
														control={ control }
														render={ ( { field: { onChange, value } } ) => (
															<ToggleControl
																label={ __(
																	'Enable Dashboard Widget',
																	'wp-plugin-info-card',
																) }
																checked={ value }
																onChange={ onChange }
																help={ __(
																	'Check this box to enable the Dashboard Widget. Be sure to show the widget in the WordPress dashboard settings by enabling it in screen options.',
																	'wp-plugin-info-card',
																) }
															/>
														) }
													/>
												</div>
												<div className="wppic-admin-row">
													<Controller
														name="ajax"
														control={ control }
														render={ ( { field: { onChange, value } } ) => (
															<ToggleControl
																label={ __(
																	'Enable Ajax Loading',
																	'wp-plugin-info-card',
																) }
																checked={ value }
																onChange={ onChange }
																help={ __(
																	'Check this box to enable the Ajax loading of the Dashboard Widget.',
																	'wp-plugin-info-card',
																) }
															/>
														) }
													/>
												</div>
											</td>
										</tr>
										<tr>
											<th scope="row">
												{ __( 'Plugins', 'wp-plugin-info-card' ) }
											</th>
											<td>
												<div className="wppic-admin-row">
													<p className="wppic-admin-description">{ __( 'Add or remove plugins that will display in the WordPress Dashboard.', 'wp-plugin-info-card' ) }</p>
												</div>
												<div className="wppic-admin-row">
													<AddPlugin
														onChange={ ( value ) => {
															const oldPlugins = getValues( 'list' );
															const newPlugins = [ ...oldPlugins, value ];
															setValue( 'list', newPlugins );
														} }
														formValues={ formValues }
													/>
												</div>
												<div className="wppic-admin-row">{ getPlugins() }</div>
											</td>
										</tr>
										<tr>
											<th scope="row">
												{ __( 'Themes', 'wp-plugin-info-card' ) }
											</th>
											<td>
												<div className="wppic-admin-row">
													<AddTheme
														onChange={ ( value ) => {
															const oldThemes = getValues( 'theme-list' );
															const newThemes = [ ...oldThemes, value ];
															setValue( 'theme-list', newThemes );
														} }
														formValues={ formValues }
													/>
												</div>
												<div className="wppic-admin-row">{ getThemes() }</div>
											</td>
										</tr>
									</tbody>
								</table>
								<SaveResetButtons
									formValues={ formValues }
									setError={ setError }
									reset={ reset }
									errors={ errors }
									isDirty={ isDirty }
									dirtyFields={ dirtyFields }
									trigger={ trigger }
									onSave={ () => {
										onSubmit();
									} }
								/>
							</form>
						</div>
					</div>
				</div>
				<div className="wppic-admin-panel-sidebar">
					<div className="wppic-admin-panel-sidebar-card">
						<h3>
							<DatabaseZap />
							{ __( 'Cache Options', 'wp-plugin-info-card' ) }
						</h3>
						<p>
							{ __(
								'WP Plugin Info Card uses a cache system to improve performance. You can clear the cache manually by clicking the button below.',
								'wp-plugin-info-card',
							) }
						</p>
						<CacheButton nonce={ wppicAdminHome.clearCacheNonce } />
					</div>
					<div className="wppic-admin-panel-sidebar-card">
						<h3>
							<BookText />
							{ __( 'Documentation', 'wp-plugin-info-card' ) }
						</h3>
						<p>
							{ __(
								'Find out how to use WP Plugin Info Card, its blocks, and its shortcodes.',
								'wp-plugin-info-card',
							) }
						</p>
						<Button
							variant="primary"
							href="https://docs.mediaron.com/product/wp-plugin-info-card/"
							className="wppic-btn wppic-btn-alt has-icon-right btn-full-width"
							icon={ () => <ExternalLink /> }
							iconPosition="right"
							target="_blank"
							rel="noopener noreferrer"
						>
							{ __( 'English Documentation', 'wp-plugin-info-card' ) }
						</Button>
						<Button
							variant="primary"
							href="https://www.b-website.com/wp-plugin-info-card-plugin-base-plugin-api-wordpress-org"
							className="wppic-btn wppic-btn-alt has-icon-right btn-full-width"
							icon={ () => <ExternalLink /> }
							iconPosition="right"
							target="_blank"
							rel="noopener noreferrer"
						>
							{ __( 'French Documentation', 'wp-plugin-info-card' ) }
						</Button>
					</div>
					{ ! reviewNagDismissed && (
						<div className="wppic-admin-panel-sidebar-card wppic-admin-review-nag">
							<h3>
								<Star size={ 24 } />
								{ __( 'Enjoying the plugin?', 'wp-plugin-info-card' ) }
							</h3>
							<p>
								{ __(
									'If this plugin has been useful, please consider leaving a 5-star review on WordPress.org. It helps others find the plugin and means a lot.',
									'wp-plugin-info-card',
								) }
							</p>
							<Button
								variant="primary"
								href={ wppicAdminHome.reviewUrl }
								className="wppic-btn wppic-btn-alt has-icon-right btn-full-width"
								icon={ () => <ExternalLink /> }
								iconPosition="right"
								target="_blank"
								rel="noopener noreferrer"
							>
								{ __( 'Leave a Review', 'wp-plugin-info-card' ) }
							</Button>
							<Button
								variant="tertiary"
								className="wppic-admin-review-nag-dismiss"
								onClick={ () => {
									setReviewNagDismissed( true );
									SendCommand( 'wppic_dismiss_review_nag', {
										nonce: wppicAdminHome.dismissReviewNagNonce,
									} );
								} }
							>
								{ __( 'Dismiss', 'wp-plugin-info-card' ) }
							</Button>
						</div>
					) }
				</div>
			</div>
		</>
	);
};

export default HomeScreen;
