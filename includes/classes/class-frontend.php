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
class Frontend {

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		add_action( 'enqueue_block_editor_assets', array( $this, 'block_editor_assets' ), 5 );
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
