/**
 * Build plugin zip for distribution (e.g. WordPress.org, customers).
 *
 * What belongs in the zip
 * - Ship: readme.txt, main PHP, assets, build, dist, langs, php, src, templates, and a production lib/ (see below).
 * - Usually omit (not needed on sites): phpstan.dist.neon, phpstan.neon, .vscode/, composer.json (optional).
 *   Those are safe if included, but they bloat the package and confuse non-developers.
 *
 * lib/ and Composer (important)
 * - If lib/ was built with dev dependencies, Composer adds phpstan/bootstrap.php to autoload files, so PHPStan
 *   runs on every front-end request. Rebuild lib without dev before zipping.
 * - `grunt` and `grunt release` run `composer install --no-dev`, add ABSPATH guards, zip, then
 *   `composer install` so local PHPStan dependencies are restored.
 * - Composer `post-autoload-dump` always runs `bin/add-composer-guards.php`, so both installs
 *   put the same ABSPATH guards back. `npm run build` and `npm run start` do not rewrite lib/.
 *
 * Tasks
 * - `grunt` or `grunt release` — composer install --no-dev (strips phpstan/php-stubs from lib/ and reapplies ABSPATH guards), guard, zip, then composer install (restores dev deps and the same guards).
 * - `grunt compress` or `grunt compress-only` — zip only; does not run Composer (lib/ left as-is).
 */
'use strict';

const { execSync } = require( 'child_process' );
const path = require( 'path' );

module.exports = function( grunt ) {
	grunt.registerTask(
		'composer-install-no-dev',
		'Install Composer deps without require-dev so lib/ autoload does not load PHPStan on every request.',
		function composerInstallNoDev() {
			try {
				execSync(
					'composer install --no-dev --no-interaction --optimize-autoloader',
					{
						stdio: 'inherit',
						cwd: path.resolve( __dirname ),
					},
				);
			} catch ( err ) {
				grunt.fail.fatal(
					'composer install --no-dev failed. Install Composer and run from the plugin root, or use grunt compress only with a production lib/.',
				);
			}
		},
	);
	grunt.registerTask(
		'composer-install',
		'Restore Composer require-dev so local PHPStan still works after a production zip.',
		function composerInstall() {
			try {
				execSync(
					'composer install --no-interaction --optimize-autoloader',
					{
						stdio: 'inherit',
						cwd: path.resolve( __dirname ),
					},
				);
			} catch ( err ) {
				grunt.fail.fatal(
					'composer install failed. Install Composer and run from the plugin root, or use grunt compress only with a production lib/.',
				);
			}
		},
	);

	grunt.initConfig( {
		compress: {
			main: {
				options: {
					archive: 'wp-plugin-info-card.zip',
				},
				files: [
					{ src: [ 'readme.txt' ], dest: '/', filter: 'isFile' },
					{ src: [ 'wp-plugin-info-card.php' ], dest: '/', filter: 'isFile' },
					{ src: [ 'functions.php' ], dest: '/', filter: 'isFile' },
					{ src: [ 'uninstall.php' ], dest: '/', filter: 'isFile' },
					{ src: [ 'assets/**' ], dest: '/' },
					{ src: [ 'src/**' ], dest: '/' },
					{ src: [ 'build/**' ], dest: '/' },
					{ src: [ 'dist/**' ], dest: '/' },
					{ src: [ 'langs/**' ], dest: '/' },
					{ src: [ 'lib/**' ], dest: '/' },
					{ src: [ 'php/**' ], dest: '/' },
					{ src: [ 'templates/**' ], dest: '/' },
				],
			},
		},
	} );

	const productionZip = [ 'composer-install-no-dev', 'composer_guard', 'compress', 'composer-install' ];

	grunt.registerTask( 'composer_guard', 'Add ABSPATH guards to Composer-generated files.', function() {
		const done = this.async();
		const { exec } = require( 'child_process' );
		exec( 'php bin/add-composer-guards.php', { cwd: path.resolve( __dirname ) }, function( error, stdout, stderr ) {
			if ( error ) {
				grunt.log.error( error );
				if ( stderr ) {
					grunt.log.error( stderr );
				}
				done( false );
				return;
			}
			grunt.log.write( stdout );
			done();
		} );
	} );

	grunt.registerTask( 'default', productionZip );
	grunt.registerTask( 'release', productionZip );
	grunt.registerTask( 'compress-only', [ 'composer_guard', 'compress' ] );

	grunt.loadNpmTasks( 'grunt-contrib-compress' );
};
