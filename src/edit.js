/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import { ServerSideRender } from '@wordpress/server-side-render';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

import metadata from './block.json';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit(props) {
	const { attributes: { textFileUrl }, setAttributes } = props;

	return (
		<p { ...useBlockProps() } >
			<div>
				Text file URL: {textFileUrl}
			</div>
			<ServerSideRender block="stellarwp/text-file-embed" attributes={attributes} />
			<InspectorControls>
				<TextControl
					label="Text File URL"
					value={textFileUrl}
					onChange={(newUrl) => setAttributes({ textFileUrl: newUrl })}
					help="Enter the URL of the .txt file you want to display."
				/>
			</InspectorControls>
		</p>
	);
}
