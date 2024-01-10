<?php
namespace LSX\Blocks\Classes;

/**
 * @package   LSX\Blocks\Classes
 * @author    LightSpeed
 * @license   GPL-3.0+
 * @link
 * @copyright 2023 LightSpeed
 */

/**
 * Setup plugin class.
 *
 * @package LSX\Blocks\Classes
 * @author  LightSpeed
 */
class Block_Setup {

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
	}

	public function init() {
		add_action( 'init', array( $this, 'register_block_pattern_category' ) );
		add_filter( 'block_categories_all', array( $this, 'register_block_category' ), 10, 2 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'block_editor_assets' ), 5 );
	}

	/**
	 * Registers the LSX Blocks pattern category
	 *
	 * @return void
	 */
	public function register_block_pattern_category() {
		register_block_pattern_category(
			'lsx-blocks',
			array( 'label' => __( 'LSX Blocks', 'lsx-blocks' ) )
		);
	} 

	/**
	 * Registers the LSX Blocks category
	 *
	 * @return array
	 */
	public function register_block_category( $block_categories, $block_editor_context ) {
		$block_categories[] = array(
			'slug'  => 'lsx-blocks',
			'title' => __( 'LSX Blocks', 'lsx-blocks' ),			
		);
		return $block_categories;
	}

	/**
	 * Enqueue assets for backend editor
	 *
	 * @since 1.0.0
	 */
	public function block_editor_assets() {
		// Load the compiled blocks into the editor.
		wp_enqueue_script(
			'lsx-blocks-block-js',
			LSX_BLOCKS_URL . 'dist/blocks.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor', 'wp-edit-post', 'wp-plugins' ),
			LSX_BLOCKS_VER
		);
	}
}
