/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
//<ServerSideRender block="stellarwp/changelog-embed" attributes={attributes} />
export default function Edit(props) {
	const { attributes, setAttributes } = props;
	const blockProps = useBlockProps();
	let changelogUrlInfo = null;

	if ( ! attributes.changelogUrl ) {
		changelogUrlInfo = (<span style={{ color: '#999', fontSize: '1rem' }}>Set the changelog URL in the sidebar.</span>);
	} else {
		changelogUrlInfo = (<a href={attributes.changelogUrl} target="_blank">{attributes.changelogUrl}</a>);
	}

  return (
		<Fragment>
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

			<div {...blockProps}>
				<div style={{ backgroundColor: '#f0f0f0', border: '1px solid #000', padding: '1rem' }}>
					Changelog Embed <span style={{ color: '#999', fontSize: '0.8rem' }}>(this box is not visible in the frontend)</span>
					<div>{changelogUrlInfo}</div>
				</div>

				<ServerSideRender
					block="stellarwp/changelog-embed"
					attributes={attributes}
				/>
			</div>
		</Fragment>
	);
}
