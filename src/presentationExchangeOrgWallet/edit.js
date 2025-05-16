/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * Imports the InspectorControls component, which is used to wrap
 * the block's custom controls that will appear in in the Settings
 * Sidebar when the block is selected.
 *
 * Also imports the React hook that is used to mark the block wrapper
 * element. It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#inspectorcontrols
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';

/**
 * Imports the necessary components that will be used to create
 * the user interface for the block's settings.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/components/panel/#panelbody
 * @see https://developer.wordpress.org/block-editor/reference-guides/components/text-control/
 * @see https://developer.wordpress.org/block-editor/reference-guides/components/toggle-control/
 */
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';

/**
 * Imports the useEffect React Hook. This is used to set an attribute when the
 * block is loaded in the Editor.
 *
 * @see https://react.dev/reference/react/useEffect
 */
import { useEffect } from 'react';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}   props               Properties passed to the function.
 * @param {Object}   props.attributes    Available block attributes.
 * @param {Function} props.setAttributes Function that updates individual attributes.
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const { openidEndpoint, presentationDefinitionId, authenticationHeaderName, authenticationToken, successUrl } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'openid4vp-exchange' ) }>
					<TextControl
						label={ __(
							'OpenID4VP Endpoint',
							'openid4vp-exchange'
						) }
						value={ openidEndpoint }
						onChange={ ( value ) =>
							setAttributes( { openidEndpoint: value } )
						}
					/>
					<TextControl
						label="Authentication header"
						value={authenticationHeaderName}
						onChange={( value ) =>
							setAttributes( { authenticationHeaderName: value } )
						}
					/>
					<TextControl
						label="Authentication token"
						value={authenticationToken}
						onChange={( value ) =>
							setAttributes( { authenticationToken: value } )
						}
					/>
					<TextControl
						label={ __(
							'Presentation definition id',
							'openid4vp-exchange'
						) }
						value={ presentationDefinitionId }
						onChange={ ( value ) =>
							setAttributes( { presentationDefinitionId: value } )
						}
					/>
					<TextControl
						label={ __(
							'Success url',
							'openid4vp-exchange'
						) }
						value={ successUrl }
						onChange={ ( value ) =>
							setAttributes( { successUrl: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<p {...useBlockProps()}>
				<form id="org-wallet-form">
					<input type="text" id="org-wallet-url" name="walletUrl" placeholder="Enter wallet URL" />
					<button type="button" id="org-wallet-submit">Connect to wallet</button>
				</form>
			</p>
		</>
	);
}
