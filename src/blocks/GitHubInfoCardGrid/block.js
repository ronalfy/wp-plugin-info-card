import metadata from './block.json';
import { GitHubIcon } from '../components/GitHubIcons';
import { InnerBlocks } from '@wordpress/block-editor';
import { createBlock } from '@wordpress/blocks';

//  Import main block file.
import edit from './edit';

const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

/**
 * Build a GitHub grid + card from a repository URL.
 *
 * @param {string} url GitHub repository URL.
 * @return {Object|false} Block or false when the URL is not a repo.
 */
const createGitHubCardFromUrl = ( url ) => {
	const githubRegex = /https:\/\/github\.com\/([a-z0-9-]+)\/([a-z0-9-]+)\/?$/i;
	const match = githubRegex.exec( url );
	if ( ! match ) {
		return false;
	}

	// Persist uniqueId immediately so the child card can select the grid store on first paint.
	const uniqueId = `wp-plugin-info-card-id-${ Date.now() }`;

	return createBlock(
		'wp-plugin-info-card/github-info-card-grid',
		{ uniqueId },
		[
			createBlock( 'wp-plugin-info-card/github-info-card', {
				username: match[ 1 ],
				repo: match[ 2 ],
				loading: true,
			} ),
		],
	);
};

if ( wppic.is_github_info_cards_enabled ) {
	registerBlockType( metadata, {
		icon: (
			<GitHubIcon fill="#333" />
		),
		edit,
		save() {
			return <InnerBlocks.Content />;
		},
		transforms: {
			from: [
				{
					type: 'block',
					blocks: [ 'core/embed' ],
					isMatch: ( { url } ) => {
						const githubRegex = /https:\/\/github\.com\/([a-z0-9-]+)\/([a-z0-9-]+)\/?$/i;
						return null !== githubRegex.exec( url );
					},
					transform: ( { url } ) => {
						return createGitHubCardFromUrl( url );
					},
				},
				{
					type: 'raw',
					isMatch: ( node ) => {
						if ( node.nodeName === 'P' ) {
							const githubRegex = /https:\/\/github\.com\/([a-z0-9-]+)\/([a-z0-9-]+)\/?$/i;
							return null !== githubRegex.exec( node.textContent );
						}
						return false;
					},
					priority: 5,
					transform: ( node ) => {
						return createGitHubCardFromUrl( node.textContent );
					},
				},
			],
		},
	} );
}

