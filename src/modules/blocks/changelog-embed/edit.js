/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { InspectorControls } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

const { serverSideRender: ServerSideRender } = wp;

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit(props) {
	const { attributes, setAttributes } = props;

  return (
		<div { ...useBlockProps() } >
			<InspectorControls>
				<div className="block-editor-block-card">
					<TextControl
						label="Changelog URL"
						value={attributes.changelogUrl}
						onChange={(newUrl) => setAttributes({ changelogUrl: newUrl })}
						help="Enter the URL of the changelog file you want to display."
					/>
				</div>
			</InspectorControls>

			<div style={{ backgroundColor: '#f0f0f0', border: '1px solid #000', padding: '1rem' }}>
				Changelog Embed
				<div>{attributes.changelogUrl}</div>
			</div>

			<ServerSideRender block="stellarwp/changelog-embed" attributes={attributes} />
		</div>
	);
}
