/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

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
		changelogUrlInfo = (<span style={{ color: '#999', fontSize: '1rem' }}>{__("Configure in the sidebar.", "stellar-changelog-embed")}</span>);
	} else {
		changelogUrlInfo = (<a href={attributes.changelogUrl} target="_blank">{attributes.changelogUrl}</a>);
	}

  // TODO: Improve the styling of the inspector controls.
  return (
		<Fragment>
			<InspectorControls>
				<div className="block-editor-block-card">
					<TextControl
						label={__("Owner", "stellar-changelog-embed")}
						value={attributes.owner}
						onChange={(newOwner) => setAttributes({ owner: newOwner })}
					/>

					<TextControl
						label={__("Repo", "stellar-changelog-embed")}
						value={attributes.repo}
						onChange={(newRepo) => setAttributes({ repo: newRepo })}
					/>

					<TextControl
						label={__("Path", "stellar-changelog-embed")}
						value={attributes.path}
						onChange={(newPath) => setAttributes({ path: newPath })}
					/>

					<TextControl
						label={__("Branch", "stellar-changelog-embed")}
						value={attributes.branch}
						onChange={(newBranch) => setAttributes({ branch: newBranch })}
					/>

					<TextControl
						label={__("Max Versions", "stellar-changelog-embed")}
						value={attributes.max_versions}
						onChange={(newMaxVersions) => setAttributes({ max_versions: newMaxVersions })}
					/>
				</div>
			</InspectorControls>

			<div {...blockProps}>
				<div style={{ backgroundColor: '#f0f0f0', border: '1px solid #000', padding: '1rem' }}>
					{__("Changelog Embed", "stellar-changelog-embed")} <span style={{ color: '#999', fontSize: '0.8rem' }}>({__("this box is not visible in the frontend", "stellar-changelog-embed")})</span>
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
