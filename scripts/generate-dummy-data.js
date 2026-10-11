#!/usr/bin/env node
/**
 * Cross-platform (Windows/macOS/Linux) wrapper around the dummy-data
 * generator. Node-based so it doesn't depend on bash being on PATH.
 *
 * Generates ECM dummy test data: 3 custom post types (100 posts each by
 * default), 6 custom taxonomies (20 terms each, hierarchical + flat) linked
 * to those post types plus ALMS/StoreEngine post types when active, and one
 * field group covering every ECM field type.
 *
 * Usage: node scripts/generate-dummy-data.js [--posts=N] [--terms=N] [-y|--yes]
 *
 *   --posts=N   Posts to create per post type (default: 100)
 *   --terms=N   Terms to create per taxonomy (default: 20)
 *   -y, --yes   Skip the confirmation prompt
 *
 * Safe to re-run: it deletes only the demo data a previous run of this
 * script created, then regenerates it fresh. It never touches other site
 * content.
 */

const path = require( 'path' );
const readline = require( 'readline' );
const { spawnSync } = require( 'child_process' );

const PLUGIN_DIR = path.dirname( __dirname );
const PAYLOAD = path.join( 'scripts', 'generate-dummy-data.php' );

let postsPerType = 100;
let termsPerTaxonomy = 20;
let assumeYes = false;

function usage() {
	console.log( `Generate ECM dummy test data: 3 custom post types (100 posts each by
default), 6 custom taxonomies (20 terms each, hierarchical + flat) linked to
those post types plus ALMS/StoreEngine post types when active, and one field
group covering every ECM field type.

Usage: node scripts/generate-dummy-data.js [--posts=N] [--terms=N] [-y|--yes]

  --posts=N   Posts to create per post type (default: 100)
  --terms=N   Terms to create per taxonomy (default: 20)
  -y, --yes   Skip the confirmation prompt

Safe to re-run: it deletes only the demo data a previous run of this script
created, then regenerates it fresh. It never touches other site content.` );
}

for ( const arg of process.argv.slice( 2 ) ) {
	if ( arg.startsWith( '--posts=' ) ) {
		postsPerType = arg.slice( '--posts='.length );
	} else if ( arg.startsWith( '--terms=' ) ) {
		termsPerTaxonomy = arg.slice( '--terms='.length );
	} else if ( arg === '-y' || arg === '--yes' ) {
		assumeYes = true;
	} else if ( arg === '-h' || arg === '--help' ) {
		usage();
		process.exit( 0 );
	} else {
		console.error( `❌ Unknown option: ${ arg }` );
		usage();
		process.exit( 1 );
	}
}

function run( args ) {
	return spawnSync( 'wp', args, {
		cwd: PLUGIN_DIR,
		stdio: 'inherit',
	} );
}

function commandExists() {
	const result = spawnSync( 'wp', [ '--info' ], {
		cwd: PLUGIN_DIR,
		stdio: 'ignore',
	} );
	return ! result.error && result.status === 0;
}

async function confirm( question ) {
	const rl = readline.createInterface( { input: process.stdin, output: process.stdout } );
	const answer = await new Promise( ( resolve ) => rl.question( question, resolve ) );
	rl.close();
	return /^y(es)?$/i.test( answer.trim() );
}

async function main() {
	if ( ! commandExists() ) {
		console.error( '❌ WP-CLI not found. Install it: https://wp-cli.org/#installing' );
		process.exit( 1 );
	}

	const pluginActive = run( [ 'plugin', 'is-active', 'easy-content-manager' ] );
	if ( pluginActive.status !== 0 ) {
		console.error( "❌ The easy-content-manager plugin isn't active on this site." );
		process.exit( 1 );
	}

	if ( ! assumeYes ) {
		console.log( `This will delete any demo data a previous run of this script created,
then create ${ postsPerType } posts per demo post type and ${ termsPerTaxonomy } terms per demo taxonomy.
It will not touch any other content on this site.` );
		const ok = await confirm( 'Continue? [y/N] ' );
		if ( ! ok ) {
			console.log( 'Aborted.' );
			process.exit( 1 );
		}
	}

	console.log( '🌱 Defining post types, taxonomies, and field group...' );
	let result = run( [ 'eval-file', PAYLOAD, 'define', String( postsPerType ), String( termsPerTaxonomy ) ] );
	if ( result.status !== 0 ) {
		process.exit( result.status ?? 1 );
	}

	console.log( '\n🌱 Seeding posts, terms, and field values (fresh WP bootstrap)...' );
	result = run( [ 'eval-file', PAYLOAD, 'seed', String( postsPerType ), String( termsPerTaxonomy ) ] );
	if ( result.status !== 0 ) {
		process.exit( result.status ?? 1 );
	}

	console.log( '\n✅ Done.' );
}

main();
