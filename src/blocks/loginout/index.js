
/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { login as icon } from '@wordpress/icons';
import edit from './edit';

/**
 * Internal dependencies
 */
import metadata from './block.json';
metadata.icon = icon;
metadata.edit = edit;

registerBlockType( 
	metadata.name, metadata
);