import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { label, slotType, status, approvedText } = attributes;

	const blockProps = useBlockProps( {
		className: 'cwc-slot',
		style: { border: '1px dashed #999', padding: '12px' },
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title="Copy Slot Settings">
					<TextControl
						label="Label"
						value={ label }
						onChange={ ( v ) => setAttributes( { label: v } ) }
					/>
					<SelectControl
						label="Type"
						value={ slotType }
						options={ [
							{ label: 'Headline', value: 'headline' },
							{ label: 'Paragraph', value: 'paragraph' },
							{ label: 'CTA', value: 'cta' },
						] }
						onChange={ ( v ) => setAttributes( { slotType: v } ) }
					/>
					<SelectControl
						label="Status"
						value={ status }
						options={ [
							{ label: 'Not started', value: 'not_started' },
							{ label: 'In progress', value: 'in_progress' },
							{ label: 'Needs review', value: 'needs_review' },
							{ label: 'Changes requested', value: 'changes_requested' },
							{ label: 'Approved (client)', value: 'approved_client' },
							{ label: 'Approved (final)', value: 'approved_final' },
						] }
						onChange={ ( v ) => setAttributes( { status: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div style={ { fontWeight: 600 } }>{ label }</div>
				<div style={ { fontSize: 12, opacity: 0.8 } }>
					Type: { slotType } • Status: { status }
				</div>
				<hr />
				{ approvedText ? (
					<div>{ approvedText }</div>
				) : (
					<div style={ { opacity: 0.7 } }>[No approved copy yet]</div>
				) }
			</div>
		</>
	);
}
