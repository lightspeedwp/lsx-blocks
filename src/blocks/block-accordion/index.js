/**
 * BLOCK: LSX Blocks Accordion Block
 */

// Import block dependencies and components
import classnames from 'classnames';
import Inspector from './components/inspector';
import Accordion from './components/accordion';
import icons from './components/icons';
import omit from 'lodash/omit';

// Components
const { __ } = wp.i18n;

// Extend component
const { Component } = wp.element;

// Register block
const {
	registerBlockType,
	createBlock,
} = wp.blocks;

// Register editor components
const {
	RichText,
	AlignmentToolbar,
	BlockControls,
	BlockAlignmentToolbar,
	InnerBlocks,
} = wp.blockEditor;

// Register components
const {
	withFallbackStyles,
	Button,
	Dashicon,
} = wp.components;

const blockAttributes = {
	accordionTitle: {
		type: 'array',
		selector: '.lsx-accordion-title',
		source: 'children',
	},
	accordionText: {
		type: 'array',
		selector: '.lsx-accordion-text',
		source: 'children',
	},
	accordionAlignment: {
		type: 'string',
	},
	accordionFontSize: {
		type: 'number',
		default: 18
	},
	accordionOpen: {
		type: 'boolean',
		default: false
	},
};

class LSXAccordionBlock extends Component {

	render() {

		// Setup the attributes
		const { attributes: { accordionTitle, accordionText, accordionAlignment, accordionFontSize, accordionOpen }, isSelected, className, setAttributes } = this.props;

		return [
			// Show the block alignment controls on focus
			<BlockControls key="controls">
				<AlignmentToolbar
					value={ accordionAlignment }
					onChange={ ( value ) => this.props.setAttributes( { accordionAlignment: value } ) }
				/>
			</BlockControls>,
			// Show the block controls on focus
			<Inspector key={ 'lsx-accordion-inspector-' + this.props.clientId }
				{ ...this.props }
			/>,
			// Show the button markup in the editor
			<Accordion key={ 'lsx-accordion-' + this.props.clientId } { ...this.props }>
				<RichText
					tagName="p"
					placeholder={ __( 'Accordion Title', 'lsx-blocks' ) }
					value={ accordionTitle }
					className="lsx-accordion-title"
					onChange={ ( value ) => this.props.setAttributes( { accordionTitle: value } ) }
				/>

				<div className="lsx-accordion-text">
					<InnerBlocks />
				</div>
			</Accordion>
		];
	}
}

// Register the block
registerBlockType( 'lsx-blocks/lsx-accordion', {
	title: __( 'LSX Accordion', 'lsx-blocks' ),
	description: __( 'Add accordion block with a title and text.', 'lsx-blocks' ),
	icon: 'editor-ul',
	category: 'lsx-blocks',
	keywords: [
		__( 'accordion', 'lsx-blocks' ),
		__( 'list', 'lsx-blocks' ),
		__( 'lsx', 'lsx-blocks' ),
	],
	attributes: blockAttributes,

	// Render the block components
	edit: LSXAccordionBlock,

	// Save the attributes and markup
	save: function( props ) {

		// Setup the attributes
		const { accordionTitle, accordionText, accordionAlignment, accordionFontSize, accordionOpen } = props.attributes;

		// Save the block markup for the front end
		return (
			<Accordion { ...props }>
				<details open={accordionOpen}>
					<summary className="lsx-accordion-title" style={ { fontSize: accordionFontSize } } >{ accordionTitle }</summary>
					<div className="lsx-accordion-text">
						<InnerBlocks.Content />
					</div>
				</details>
			</Accordion>
		);
	},

	deprecated: [ {
		attributes: {
			accordionText: {
				type: 'array',
				selector: '.lsx-accordion-text',
				source: 'children',
			},
			...blockAttributes
		},

		migrate( attributes, innerBlocks  ) {
			return [
				omit( attributes, 'accordionText' ),
				[
					createBlock( 'core/paragraph', {
						content: attributes.accordionText,
					} ),
					...innerBlocks,
				],
			];
		},

		save( props ) {
			return (
				<Accordion { ...props }>
					<details open={ props.attributes.accordionOpen }>
						<summary className="lsx-accordion-title">
							<RichText.Content
								value={ props.attributes.accordionTitle }
							/>
						</summary>
						<RichText.Content
							className="lsx-accordion-text"
							tagName="p"
							value={ props.attributes.accordionText }
						/>
					</details>
				</Accordion>
			);
		},
	} ],
} );
