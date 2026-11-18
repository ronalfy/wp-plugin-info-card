'use strict';
/**
 * Lazy load and queue the GitHub info card.
 *
 * @since 1.2.0
 */
import apiFetch from '@wordpress/api-fetch';
import { safeDecodeURIComponent } from '@wordpress/url';
import { __ } from '@wordpress/i18n';
import lozad from 'lozad';

( () => {
	const DEFAULTS = {
		selector: '[data-is-github-card-loading="true"]',
		endpoint: wppicGithubInfoCardLazyLoad.restUrl,
	};
	const ready = ( fn ) => {
		if ( document.readyState === 'loading' ) {
			document.addEventListener( 'DOMContentLoaded', fn, { once: true } );
		} else {
			fn();
		}
	};

	const bindAvatarFallbacks = ( root = document ) => {
		root.querySelectorAll( 'img[data-wppic-avatar-fallback]' ).forEach( ( img ) => {
			if ( img.dataset.wppicAvatarBound ) {
				return;
			}
			img.dataset.wppicAvatarBound = 'true';
			img.addEventListener(
				'error',
				() => {
					const fallback = img.dataset.wppicAvatarFallback;
					if ( fallback && img.src !== fallback ) {
						img.src = fallback;
					}
				},
				{ once: true },
			);
		} );
	};

	// Global state.
	let cards = [];
	let initialized = false;
	let observer = null;

	const showCardError = ( element ) => {
		const errorElement = document.createElement( 'div' );
		errorElement.textContent = __( 'GitHub Card data could not be loaded.', 'wp-plugin-info-card' );
		element.replaceWith( errorElement );
	};

	const fetchCard = async ( element ) => {
		const attributesKey = element.dataset.username + '_' + element.dataset.repo;
		const data = {
			username: safeDecodeURIComponent( element.dataset.username ),
			repo: safeDecodeURIComponent( element.dataset.repo ),
			token: safeDecodeURIComponent( element.dataset.token || '' ),
			cardAttributes: {},
		};
		if ( wppicGithubInfoCardLazyLoad.cardAttributes[ attributesKey ] ) {
			data.cardAttributes = wppicGithubInfoCardLazyLoad.cardAttributes[ attributesKey ];
		}
		try {
			const response = await apiFetch( {
				url: DEFAULTS.endpoint,
				parse: true,
				headers: {
					'X-WP-Nonce': wppicGithubInfoCardLazyLoad.restNonce,
					'Content-Type': 'application/json',
				},
				method: 'POST',
				body: JSON.stringify( data ),
			} );
			if ( ! response.html ) {
				showCardError( element );
				return;
			}
			element.outerHTML = response.html;
			bindAvatarFallbacks();
			return response.html;
		} catch ( error ) {
			showCardError( element );
		}
	};

	const init = () => {
		if ( initialized ) {
			return;
		}
		bindAvatarFallbacks();
		const maybeCards = document.querySelectorAll( DEFAULTS.selector );
		if ( maybeCards.length === 0 ) {
			return;
		}
		cards = Array.from( maybeCards );
		if ( cards.length > 0 && ! initialized ) {
			initialized = true;
		}
		initialized = true;
		observer = lozad( maybeCards, {
			load: ( element ) => {
				fetchCard( element );
			},
		} );
		observer.observe();
	};

	ready( init );
} )();
