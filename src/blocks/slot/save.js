import { useBlockProps } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const { approvedText, label } = attributes;
	const blockProps = useBlockProps.save( { className: 'cwc-slot' } );

	return <div { ...blockProps }>{ approvedText ? approvedText : `(${ label })` }</div>;
}
