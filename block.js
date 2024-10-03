(function (blocks, editor, element, components) {
	const { registerBlockType } = blocks;
	const { InspectorControls } = editor;
	const { TextControl } = components;
	const { Fragment } = element;
	const { ServerSideRender } = editor;

	registerBlockType('stellarwp/text-file-embed', {
		title: 'Text File Embed',
		icon: 'media-text',
		category: 'common',
		attributes: {
			textFileUrl: {
				type: 'string',
				default: ''
			}
		},
		edit: function (props) {
			const { attributes: { textFileUrl }, setAttributes } = props;

			return (
				<Fragment>
					<InspectorControls>
						<TextControl
							label="Text File URL"
							value={textFileUrl}
							onChange={(newUrl) => setAttributes({ textFileUrl: newUrl })}
							help="Enter the URL of the .txt file you want to display."
						/>
					</InspectorControls>
					<ServerSideRender
						block="stellarwp/text-file-embed"
						attributes={props.attributes}
					/>
				</Fragment>
			);
		},
		save: function () {
			// Server-rendered block, so save function is not needed
			return null;
		},
	});
})(
	window.wp.blocks,
	window.wp.editor,
	window.wp.element,
	window.wp.components
);
