/**
 * This is the initial screen of the block. It is the first screen that the user sees when they add the block to the editor.
 */
import { useState } from '@wordpress/element';
import {
	InspectorControls,
	BlockControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	PanelRow,
	TextControl,
	ButtonGroup,
	SelectControl,
	ToggleControl,
	ToolbarItem,
	ToolbarButton,
	DropdownMenu,
	ToolbarGroup,
	MenuItemsChoice,
} from '@wordpress/components';
import axios from 'axios';
import { __ } from '@wordpress/i18n';
import PluginScreenshots from '../../../templates/PluginScreenshots';
import CustomPresets from '../../../components/CustomPresets';
import PresetButton from '../../../components/PresetButton/PresetButton';
import Notice from '../../../components/Notice';
import LoadingScreen from '../../../components/Loading';

/**
 * InitialScreen component.
 *
 * @param {Object} props - Component props.
 * @return {Function} Component.
 */
const ScreenPluginPreview = ( props ) => {
	const { attributes, setAttributes, clientId } = props;
	const [ loading, setLoading ] = useState( false );

	const loadData = () => {
		setLoading( true );
		const restUrl = wppic.rest_url + 'wppic/v2/get_data';
		axios
			.get( restUrl + `?type=plugin&slug=${ encodeURIComponent( attributes.slug ) }`, {
				headers: {
					'X-WP-Nonce': wppic.rest_nonce,
				},
			} )
			.then( ( response ) => {
				if ( response.data.success ) {
					// Set asset data.
					setAttributes(
						{
							assetData: response.data.data[ 0 ] || {},
						},
					);
				} else {
					// todo - error messages
				}
			} ).catch( ( error ) => {
			} ).then( () => {
				setLoading( false );
			} );
	};

	const {
		assetData,
		enableScreenshots,
		enableRoundedIcon,
		iconStyle,
		enableContextMenu,
		pluginTitle,
		colorTheme,
		uniqueId,
	} = attributes;

	// Set the local inspector controls.
	const localInspectorControls = (
		<InspectorControls>
			<PanelBody
				title={ __( 'Block Customization', 'wp-plugin-info-card' ) }
				initialOpen={ true }
			>
				<ToggleControl
					label={ __( 'Rounded Icon', 'wp-plugin-info-card' ) }
					checked={ enableRoundedIcon }
					onChange={ ( value ) => {
						setAttributes( { enableRoundedIcon: value } );
					} }
					help={ __( 'Make the icon rounded.', 'wp-plugin-info-card' ) }
				/>
				<SelectControl
					label={ __( 'Icon Style', 'wp-plugin-info-card' ) }
					value={ iconStyle }
					onChange={ ( value ) => {
						setAttributes( { iconStyle: value } );
					} }
					help={ __( 'Set the style of the icon.', 'wp-plugin-info-card' ) }
					options={
						[
							{ label: __( 'None', 'wp-plugin-info-card' ), value: 'none' },
							{ label: __( 'Border', 'wp-plugin-info-card' ), value: 'border' },
							{ label: __( 'B&W With Border', 'wp-plugin-info-card' ), value: 'bw' },
						]
					}
				/>
				<ToggleControl
					label={ __( 'Enable Context Menu', 'wp-plugin-info-card' ) }
					checked={ enableContextMenu }
					onChange={ ( value ) => {
						setAttributes( { enableContextMenu: value } );
					} }
					help={ __( 'Make the icon rounded.', 'wp-plugin-info-card' ) }
				/>
				<TextControl
					label={ __( 'Override Plugin Title', 'wp-plugin-info-card' ) }
					help={ __( 'Leave blank for no override. Otherwise enter a custom title for the plugin.', 'wp-plugin-info-card' ) }
					value={ pluginTitle }
					onChange={ ( value ) => {
						setAttributes( { pluginTitle: value } );
					} }
				/>
				<PanelRow>
					<ToggleControl
						label={ __( 'Enable Screenshots', 'wp-plugin-info-card' ) }
						checked={ enableScreenshots }
						onChange={ ( value ) => {
							setAttributes( { enableScreenshots: value } );
						} }
						help={ __( 'Enable or disable screenshots.', 'wp-plugin-info-card' ) }
					/>
				</PanelRow>
				{
					enableScreenshots && (
						<>
							<PanelRow>
								<ToggleControl
									label={ __( 'Skip Animated Gifs', 'wp-plugin-info-card' ) }
									checked={ attributes.skipAnimatedGifs }
									onChange={ ( value ) => {
										setAttributes( { skipAnimatedGifs: value } );
										loadData();
									} }
									help={ __( 'Skip animated gifs in the screenshot carousel to speed up loading.', 'wp-plugin-info-card' ) }
								/>
							</PanelRow>
						</>
					)
				}
			</PanelBody>
			<PanelBody
				title={ __( 'Color Themes', 'wp-plugin-info-card' ) }
				initialOpen={ true }
				className="wppic-presets-panel"
				icon="admin-customizer"
			>
				<div className="wppic-screenshot-presets-button-group">
					<h3>{ __( 'Select a color theme', 'wp-plugin-info-card' ) }</h3>
					{
						attributes.customColors && (
							<>
								<Notice
									status="warning"
									isDismissible={ false }
									className="wppic-custom-preset-notice"
								>
									{ __( 'Custom colors are enabled, so these themes will have no effect.', 'wp-plugin-info-card' ) }
								</Notice>
							</>
						)
					}
					<ButtonGroup>
						<PresetButton
							label={ __( 'Default', 'wp-plugin-info-card' ) }
							setAttributes={ setAttributes }
							attributes={ attributes }
							uniqueId={ uniqueId }
							isPressed={ 'default' === colorTheme }
							theme="default"
						/>
						<PresetButton
							label={ __( 'Blossom', 'wp-plugin-info-card' ) }
							setAttributes={ setAttributes }
							attributes={ attributes }
							uniqueId={ uniqueId }
							isPressed={ 'blossom' === colorTheme }
							theme="blossom"
						/>
						<PresetButton
							label={ __( 'Crimson', 'wp-plugin-info-card' ) }
							setAttributes={ setAttributes }
							attributes={ attributes }
							uniqueId={ uniqueId }
							isPressed={ 'crimson' === colorTheme }
							theme="crimson"
						/>
						<PresetButton
							label={ __( 'Velvet', 'wp-plugin-info-card' ) }
							setAttributes={ setAttributes }
							attributes={ attributes }
							uniqueId={ uniqueId }
							isPressed={ 'velvet' === colorTheme }
							theme="velvet"
						/>
						<PresetButton
							label={ __( 'Dark', 'wp-plugin-info-card' ) }
							setAttributes={ setAttributes }
							attributes={ attributes }
							uniqueId={ uniqueId }
							isPressed={ 'dark' === colorTheme }
							theme="dark"
						/>
						<PresetButton
							label={ __( 'Light', 'wp-plugin-info-card' ) }
							setAttributes={ setAttributes }
							attributes={ attributes }
							uniqueId={ uniqueId }
							isPressed={ 'light' === colorTheme }
							theme="light"
						/>
						<PresetButton
							label={ __( 'Soft as Feathers', 'wp-plugin-info-card' ) }
							setAttributes={ setAttributes }
							attributes={ attributes }
							uniqueId={ uniqueId }
							isPressed={ 'feathers' === colorTheme }
							theme="feathers"
						/>
						<PresetButton
							label={ __( 'Caramel', 'wp-plugin-info-card' ) }
							setAttributes={ setAttributes }
							attributes={ attributes }
							uniqueId={ uniqueId }
							isPressed={ 'caramel' === colorTheme }
							theme="caramel"
						/>
					</ButtonGroup>
				</div>
				<CustomPresets
					clientId={ clientId }
					uniqueId={ uniqueId }
					attributes={ attributes }
					setAttributes={ setAttributes }
				/>
			</PanelBody>
		</InspectorControls>
	);

	const blockToolbar = (
		<BlockControls>
			<ToolbarGroup>
				<ToolbarButton
					icon="edit"
					title={ __(
						'Edit Plugin Slug',
						'wp-plugin-info-card',
					) }
					onClick={ () => setAttributes( { screen: 'slug-entry' } ) }
				/>
			</ToolbarGroup>
			<ToolbarGroup>
				<ToolbarItem as="button">
					{ ( toolbarItemHTMLProps ) => (
						<DropdownMenu
							toggleProps={ toolbarItemHTMLProps }
							label={ __(
								'Select a Color Theme',
								'wp-plugin-info-card',
							) }
							icon="admin-customizer"
						>
							{ ( { onClose } ) => (
								<>
									<MenuItemsChoice
										choices={
											[
												{ label: __( 'Default', 'wp-plugin-info-card' ), value: 'default' },
												{ label: __( 'Blossom', 'wp-plugin-info-card' ), value: 'blossom' },
												{ label: __( 'Crimson', 'wp-plugin-info-card' ), value: 'crimson' },
												{ label: __( 'Velvet', 'wp-plugin-info-card' ), value: 'velvet' },
												{ label: __( 'Dark', 'wp-plugin-info-card' ), value: 'dark' },
												{ label: __( 'Light', 'wp-plugin-info-card' ), value: 'light' },
												{ label: __( 'Soft as Feathers', 'wp-plugin-info-card' ), value: 'feathers' },
												{ label: __( 'Caramel', 'wp-plugin-info-card' ), value: 'caramel' },
											]
										}
										onSelect={ ( value ) => {
											setAttributes( {
												colorTheme: value,
											} );
											onClose();
										} }
										value={ colorTheme }
									/>
								</>
							) }
						</DropdownMenu>
					) }
				</ToolbarItem>
			</ToolbarGroup>
		</BlockControls>
	);

	if ( loading ) {
		return ( <LoadingScreen label={ __( 'Loading plugin data…', 'wp-plugin-info-card' ) } /> );
	}

	const block = (
		<>
			{ blockToolbar }
			<PluginScreenshots
				assetData={ assetData }
				attributes={ attributes }
			/>
		</>
	);

	return (
		<>
			{ localInspectorControls }
			{ block }
		</>
	);
};
export default ScreenPluginPreview;
