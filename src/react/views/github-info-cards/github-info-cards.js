import React, { useState, useEffect } from 'react';
import classnames from 'classnames';
import { useForm, Controller, useWatch } from 'react-hook-form';
import { Link } from '@tanstack/react-router';
import { __ } from '@wordpress/i18n';
import { ToggleControl, Button, Spinner, TextControl } from '@wordpress/components';
import { Cog, BookText, ExternalLink, TriangleAlert } from 'lucide-react';
import { GitHubIcon } from '../../../blocks/components/GitHubIcons';
import SendCommand from '../../utils/SendCommand';
import SnackStatus from '../../components/SnackStatus';
import Notice from '../../components/Notice';

const GitHubInfoCards = () => {
	const [ advancedOptions, setAdvancedOptions ] = useState( null );
	const [ loading, setLoading ] = useState( true );
	const [ saving, setSaving ] = useState( false );
	const [ revoking, setRevoking ] = useState( false );
	const [ apiValid, setApiValid ] = useState( false );
	const [ unauthenticatedActive, setUnauthenticatedActive ] = useState( false );
	const [ rateLimit, setRateLimit ] = useState( null );
	const [ rateRemaining, setRateRemaining ] = useState( null );
	const [ rateReset, setRateReset ] = useState( null );

	const [ snackbarOptions, setSnackbarOptions ] = useState( {
		isVisible: false,
		type: 'info',
		message: '',
		title: '',
		politeness: 'polite',
	} );

	const {
		control,
		handleSubmit,
		formState: { errors },
		getValues,
		setError,
		clearErrors,
		reset,
	} = useForm( {
		defaultValues: {
			enable_github_info_cards: false,
			github_info_cards_token: '',
			github_info_cards_unauthenticated: false,
			nonce: wppicAdminGitHubInfoCards.saveAdvancedNonce,
		},
	} );

	const formValues = useWatch( { control } );

	useEffect( () => {
		if ( advancedOptions ) {
			return;
		}

		setLoading( true );

		const fetchOptions = async () => {
			const response = await SendCommand( 'wppic_get_github_info_cards_options', {
				nonce: wppicAdminGitHubInfoCards.getNonce,
			} );
			const { success, data } = response.data;
			if ( success ) {
				const hasToken = ( data.github_info_cards_token || '' ).length > 0;
				const rateReset = data.github_info_cards_rate_reset ? String( data.github_info_cards_rate_reset ) : '';
				const unauthenticated = !! data.github_info_cards_unauthenticated && ! hasToken;
				reset( {
					enable_github_info_cards: data.enable_github_info_cards,
					github_info_cards_token: data.github_info_cards_token,
					github_info_cards_unauthenticated: unauthenticated,
				} );
				if ( data.enable_github_info_cards && hasToken && rateReset.length > 0 ) {
					setApiValid( true );
					setUnauthenticatedActive( false );
				} else if ( data.enable_github_info_cards && unauthenticated && rateReset.length > 0 ) {
					setApiValid( false );
					setUnauthenticatedActive( true );
				} else {
					setApiValid( false );
					setUnauthenticatedActive( false );
				}
				setRateLimit( data.github_info_cards_rate_limit );
				setRateRemaining( data.github_info_cards_rate_remaining );
				setRateReset( data.github_info_cards_rate_reset );
				setAdvancedOptions( data );
			} else {
				setError( 'github_info_cards_token', {
					message: data.message,
				} );
				setLoading( false );
			}
			setLoading( false );
		};
		// Fetch options.
		fetchOptions();
	}, [] );

	/**
	 * Save the advanced settings.
	 *
	 * @param {Object} formData Form data to save.
	 */
	const onSubmit = async ( formData ) => {
		setSaving( true );
		try {
			const tokenValue = ( formData.github_info_cards_token || '' ).trim();
			const unauthenticated = ! tokenValue.length && !! formData.github_info_cards_unauthenticated;
			const response = await SendCommand( 'wppic_authenticate_with_github', {
				nonce: wppicAdminGitHubInfoCards.saveNonce,
				enable_github_info_cards: formData.enable_github_info_cards,
				github_info_cards_token: tokenValue,
				github_info_cards_unauthenticated: unauthenticated,
			} );

			const { success, data } = response.data;
			if ( success ) {
				// Show success notice.
				setSnackbarOptions( {
					isVisible: true,
					type: 'success',
					message: __( 'Settings saved', 'wp-plugin-info-card' ),
					title: __( 'Success', 'wp-plugin-info-card' ),
				} );
				const hasToken = ( data.github_info_cards_token || '' ).length > 0;
				const rateReset = data.github_info_cards_rate_reset ? String( data.github_info_cards_rate_reset ) : '';
				const savedUnauthenticated = !! data.github_info_cards_unauthenticated && ! hasToken;
				reset( {
					enable_github_info_cards: data.enable_github_info_cards,
					github_info_cards_token: data.github_info_cards_token,
					github_info_cards_unauthenticated: savedUnauthenticated,
				} );
				if ( data.enable_github_info_cards && hasToken && rateReset.length > 0 ) {
					setApiValid( true );
					setUnauthenticatedActive( false );
				} else if ( data.enable_github_info_cards && savedUnauthenticated && rateReset.length > 0 ) {
					setApiValid( false );
					setUnauthenticatedActive( true );
				} else {
					setApiValid( false );
					setUnauthenticatedActive( false );
				}
				setRateLimit( data.github_info_cards_rate_limit );
				setRateRemaining( data.github_info_cards_rate_remaining );
				setRateReset( data.github_info_cards_rate_reset );
				setAdvancedOptions( data );
			} else {
				setError( 'github_info_cards_token', {
					message: data.message,
				} );
			}
		} catch ( e ) {
			setError( 'github_info_cards_token', {
				message: e.message,
			} );
			// Show error notice.
			setSaving( false );
		}
		setSaving( false );
	};

	/**
	 * Save the advanced settings.
	 *
	 * @param {Object} formData Form data to save.
	 */
	const onRevokeToken = async ( formData ) => {
		setRevoking( true );
		try {
			const response = await SendCommand( 'wppic_revoke_github_token', {
				nonce: wppicAdminGitHubInfoCards.revokeNonce,
			} );

			const { success } = response.data;
			if ( success ) {
				// Show success notice.
				setSnackbarOptions( {
					isVisible: true,
					type: 'success',
					message: unauthenticatedActive
						? __( 'Unauthenticated GitHub access disabled', 'wp-plugin-info-card' )
						: __( 'GitHub token revoked', 'wp-plugin-info-card' ),
					title: __( 'Success', 'wp-plugin-info-card' ),
				} );
				setApiValid( false );
				setUnauthenticatedActive( false );
				setRateLimit( null );
				setRateRemaining( null );
				setRateReset( null );
				reset( {
					enable_github_info_cards: false,
					github_info_cards_token: '',
					github_info_cards_unauthenticated: false,
				} );
			} else {
				setError( 'github_info_cards_token', {
					message: __( 'Could not revoke GitHub token.', 'wp-plugin-info-card' ),
				} );
			}
		} catch ( e ) {
			setError( 'github_info_cards_token', {
				message: e.message,
			} );
			// Show error notice.
			setRevoking( false );
		}
		setRevoking( false );
	};

	/**
	 * Gets an authentication or revocation button.
	 */
	const saveButton = () => {
		if ( loading ) {
			return null;
		}
		const tokenValue = ( formValues?.github_info_cards_token || '' ).trim();
		const hasTokenField = tokenValue.length > 0;
		const useWithoutToken = !! formValues?.github_info_cards_unauthenticated;
		if ( apiValid && hasTokenField ) {
			return (
				<Button
					variant="primary"
					isDestructive={ true }
					className={ classnames(
						'wppic__btn wppic__btn-secondary wppic__btn--icon-right',
					) }
					onClick={ () => {
						onRevokeToken();
					} }
					disabled={ revoking }
					icon={ () => (
						revoking ? <Spinner /> : null
					) }
				>
					{
						revoking ? __( 'Revoking…', 'wp-plugin-info-card' ) : __( 'Revoke GitHub Token', 'wp-plugin-info-card' )
					}
				</Button>
			);
		}
		if ( unauthenticatedActive && ! hasTokenField ) {
			return (
				<Button
					variant="primary"
					isDestructive={ true }
					className={ classnames(
						'wppic__btn wppic__btn-secondary wppic__btn--icon-right',
					) }
					onClick={ () => {
						onRevokeToken();
					} }
					disabled={ revoking }
					icon={ () => (
						revoking ? <Spinner /> : null
					) }
				>
					{
						revoking ? __( 'Disabling…', 'wp-plugin-info-card' ) : __( 'Disable unauthenticated access', 'wp-plugin-info-card' )
					}
				</Button>
			);
		}
		if ( getValues( 'enable_github_info_cards' ) ) {
			const savingWithoutToken = useWithoutToken && ! hasTokenField;
			return (
				<Button
					type="submit"
					variant="primary"
					className={ classnames(
						'wppic__btn wppic__btn-secondary wppic__btn--icon-right',
					) }
					disabled={ saving }
					icon={ () => (
						saving ? <Spinner /> : null
					) }
				>
					{
						saving
							? ( savingWithoutToken ? __( 'Saving…', 'wp-plugin-info-card' ) : __( 'Authenticating…', 'wp-plugin-info-card' ) )
							: ( savingWithoutToken ? __( 'Save', 'wp-plugin-info-card' ) : __( 'Authenticate with GitHub', 'wp-plugin-info-card' ) )
					}
				</Button>
			);
		}
		return null;
	};

	const tableContent = (
		<table className="form-table form-table-row-sections">
			<tbody>
				<tr>
					<th scope="row">
						{ __( 'GitHub Info Cards', 'wp-plugin-info-card' ) }
					</th>
					<td>
						<div className="wppic-admin-row">
							<Controller
								control={ control }
								name="enable_github_info_cards"
								render={ ( { field } ) => (
									<ToggleControl
										{ ...field }
										checked={ field.value }
										label={ __( 'Enable GitHub Info Cards', 'wp-plugin-info-card' ) }
										help={ __( 'Enable the GitHub Info Cards block and shortcode feature. Enter a GitHub API token, or use GitHub without a token for public repositories.', 'wp-plugin-info-card' ) }
										onChange={ ( e ) => {
											field.onChange( e );
											clearErrors( 'github_info_cards_token' );
										} }
									/>
								) }
							/>
						</div>
						{
							formValues.enable_github_info_cards && (
								<>
									<div className="wppic-admin-row">
										<Controller
											control={ control }
											name="github_info_cards_token"
											rules={ {
												required: ! formValues.github_info_cards_unauthenticated,
											} }
											render={ ( { field } ) => (
												<>
													<TextControl
														{ ...field }
														label={ __( 'GitHub Personal Access Token', 'wp-plugin-info-card' ) }
														className={
															classnames( 'wppic-admin-input',
																{
																	'is-required': ! formValues.github_info_cards_unauthenticated,
																	'has-error': errors?.github_info_cards_token,
																},
															) }
														type="text"
														onChange={ ( e ) => {
															field.onChange( e );
															clearErrors( 'github_info_cards_token' );
														} }
														help={ __( 'You will need to enter a non-expiring GitHub Personal Access Token to use this feature. You can get one from your GitHub account settings. Use a classic non-expiring token.', 'wp-plugin-info-card' ) }
														disabled={ apiValid || revoking || saving }
													/>
													<p className="wppic-admin-row-description">
														<a href="https://docs.mediaron.com/product/wp-plugin-info-card/creating-a-github-token/" target="_blank" rel="noopener noreferrer">
															{ __( 'How to Create a Non-Expiring GitHub Personal Access Token.', 'wp-plugin-info-card' ) }
														</a>
													</p>
												</>
											) }
										/>
										{
											errors?.github_info_cards_token?.required && (
												<Notice
													type="error"
													message={ __( 'Please enter a GitHub API token.', 'wp-plugin-info-card' ) }
													icon={ () => <TriangleAlert /> }
												/>
											)
										}
									</div>
									{
										! ( formValues.github_info_cards_token || '' ).trim().length && (
											<div className="wppic-admin-row">
												<Controller
													control={ control }
													name="github_info_cards_unauthenticated"
													render={ ( { field } ) => (
														<ToggleControl
															checked={ !! field.value }
															label={ __( 'Use GitHub without a token (not recommended)', 'wp-plugin-info-card' ) }
															help={ __( 'Limited to 60 requests per hour and public repositories only.', 'wp-plugin-info-card' ) }
															onChange={ ( value ) => {
																field.onChange( value );
																clearErrors( 'github_info_cards_token' );
															} }
															disabled={ saving || revoking }
														/>
													) }
												/>
											</div>
										)
									}
								</>
							)
						}
					</td>
				</tr>
			</tbody>
		</table>
	);

	/**
	 * Get the rates table.
	 *
	 * @return {React.ReactNode} The rates table.
	 */
	const getRatesTable = () => {
		return (
			<div className="wppic-admin-panel-area__section">
				<h3>
					{ __( 'GitHub API Rate Limits', 'wp-plugin-info-card' ) }
				</h3>
				<table className="form-table form-table-row-sections form-table-list">
					<thead>
						<tr>
							<th scope="col">
								{ __( 'Rate Limit', 'wp-plugin-info-card' ) }
							</th>
							<th scope="col">
								{ __( 'Rate Remaining', 'wp-plugin-info-card' ) }
							</th>
							<th scope="col">
								{ __( 'Rate Reset', 'wp-plugin-info-card' ) }
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								{ rateLimit }
							</td>
							<td>
								{ rateRemaining }
							</td>
							<td>
								{ rateReset }
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		);
	};

	return (
		<>
			<div className="wppic-admin-panel-container with-sidebar">
				<div className="wppic-admin-panel-options-wrapper">
					<form onSubmit={ handleSubmit( onSubmit ) }>
						<div className="wppic-admin-panel-area">
							<div className="wppic-admin-panel-area__section">
								<h2>
									<GitHubIcon />
									<span className="wppic-admin-panel-area__section-title">
										{ __( 'GitHub Info Cards', 'wp-plugin-info-card' ) }
									</span>
								</h2>
							</div>
							{ loading ? (
								<div className="wppic-admin-row">
									<Spinner />
								</div>
							) : (
								tableContent
							) }
							{ ( apiValid || unauthenticatedActive ) && getRatesTable() }
							<div className="wppic-admin-panel-area__section">
								{ saveButton() }
							</div>
							{ errors?.github_info_cards_token?.message && (
								<Notice
									status="error"
									message={ errors?.github_info_cards_token?.message }
									icon={ () => <TriangleAlert /> }
								/>
							) }
						</div>
					</form>
				</div>
				<div className="wppic-admin-panel-sidebar">
					<div className="wppic-admin-panel-sidebar-card">
						<h3>
							<BookText />
							{ __( 'Documentation', 'wp-plugin-info-card' ) }
						</h3>
						<p>
							{ __(
								'Learn more about the GitHub Info Cards and WP Plugin Info Card.',
								'wp-plugin-info-card',
							) }
						</p>
						<Button
							variant="secondary"
							href="https://docs.mediaron.com/product/wp-plugin-info-card/github-info-cards/"
							className="wppic-btn wppic-btn-alt has-icon-right btn-full-width"
							target="_blank"
							onClick={ ( e ) => {
								e.preventDefault();
								window.open( 'https://docs.mediaron.com/product/wp-plugin-info-card/github-info-cards/', '_blank' );
							} }
							rel="noopener noreferrer"
							icon={ () => <ExternalLink /> }
						>
							{ __( 'View Cards Documentation', 'wp-plugin-info-card' ) }
						</Button>
					</div>
				</div>
			</div>
			<SnackStatus snackbarOptions={ snackbarOptions } onTimeout={ () => {
				const newSnackbarOptions = {
					...snackbarOptions,
					isVisible: false,
				};
				setSnackbarOptions( newSnackbarOptions );
			} } />
		</>
	);
};

export default GitHubInfoCards;
