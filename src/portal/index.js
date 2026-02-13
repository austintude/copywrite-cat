import { render, useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

apiFetch.use( apiFetch.createNonceMiddleware( window?.CWC_PORTAL?.nonce ) );

function PortalApp() {
	const [ slots, setSlots ] = useState( [] );
	const [ selected, setSelected ] = useState( null );
	const [ versions, setVersions ] = useState( [] );
	const [ comments, setComments ] = useState( [] );
	const [ draftText, setDraftText ] = useState( '' );
	const [ commentText, setCommentText ] = useState( '' );
	const [ error, setError ] = useState( '' );

	useEffect( () => {
		apiFetch( { path: '/copywrite-cat/v1/slots' } )
			.then( ( res ) => setSlots( res.items || [] ) )
			.catch( ( e ) => setError( e?.message || 'Failed to load slots' ) );
	}, [] );

	useEffect( () => {
		if ( ! selected ) return;
		apiFetch( { path: `/copywrite-cat/v1/slots/${ selected.id }/versions` } )
			.then( ( res ) => setVersions( res.items || [] ) )
			.catch( () => setVersions( [] ) );
		apiFetch( { path: `/copywrite-cat/v1/slots/${ selected.id }/comments` } )
			.then( ( res ) => setComments( res.items || [] ) )
			.catch( () => setComments( [] ) );
	}, [ selected?.id ] );

	const submitDraft = () => {
		if ( ! selected ) return;
		apiFetch( {
			path: `/copywrite-cat/v1/slots/${ selected.id }/versions`,
			method: 'POST',
			data: { draftText },
		} ).then( () => {
			setDraftText( '' );
			return apiFetch( { path: `/copywrite-cat/v1/slots/${ selected.id }/versions` } );
		} ).then( ( res ) => setVersions( res.items || [] ) );
	};

	const submitComment = () => {
		if ( ! selected ) return;
		apiFetch( {
			path: `/copywrite-cat/v1/slots/${ selected.id }/comments`,
			method: 'POST',
			data: { commentText },
		} ).then( () => {
			setCommentText( '' );
			return apiFetch( { path: `/copywrite-cat/v1/slots/${ selected.id }/comments` } );
		} ).then( ( res ) => setComments( res.items || [] ) );
	};

	const approve = ( versionId, level = 'client' ) => {
		if ( ! selected ) return;
		apiFetch( {
			path: `/copywrite-cat/v1/slots/${ selected.id }/approve`,
			method: 'POST',
			data: { level, versionId },
		} ).then( () => {
			// refresh slots list
			return apiFetch( { path: '/copywrite-cat/v1/slots' } );
		} ).then( ( res ) => {
			setSlots( res.items || [] );
			const updated = ( res.items || [] ).find( ( s ) => s.id === selected.id );
			if ( updated ) setSelected( updated );
		} );
	};

	return (
		<div style={ { display: 'grid', gridTemplateColumns: '320px 1fr', gap: 16 } }>
			<div style={ { borderRight: '1px solid #eee', paddingRight: 16 } }>
				<h2>Copy Slots</h2>
				{ error ? <div style={ { color: 'crimson' } }>{ error }</div> : null }
				<ul>
					{ slots.map( ( s ) => (
						<li key={ s.id }>
							<button onClick={ () => setSelected( s ) } style={ { width: '100%', textAlign: 'left' } }>
								{ s.label } <small style={ { opacity: 0.7 } }>({ s.status })</small>
							</button>
						</li>
					) ) }
				</ul>
			</div>

			<div>
				{ ! selected ? (
					<p>Select a slot to start drafting.</p>
				) : (
					<>
						<h2>{ selected.label }</h2>
						<p>
							Type: <strong>{ selected.slotType }</strong> • Status: <strong>{ selected.status }</strong>
						</p>

						<h3>New draft</h3>
						<textarea value={ draftText } onChange={ ( e ) => setDraftText( e.target.value ) } rows={ 6 } style={ { width: '100%' } } />
						<div>
							<button onClick={ submitDraft } disabled={ ! draftText.trim() }>
								Save draft
							</button>
						</div>

						<h3>Versions</h3>
						<ol>
							{ versions.map( ( v ) => (
								<li key={ v.id } style={ { marginBottom: 12 } }>
									<div style={ { whiteSpace: 'pre-wrap' } }>{ v.draftText }</div>
									<div>
										<button onClick={ () => approve( v.id, 'client' ) }>
											Approve (client)
										</button>
									</div>
								</li>
							) ) }
						</ol>

						<h3>Comments</h3>
						<textarea value={ commentText } onChange={ ( e ) => setCommentText( e.target.value ) } rows={ 3 } style={ { width: '100%' } } />
						<div>
							<button onClick={ submitComment } disabled={ ! commentText.trim() }>
								Add comment
							</button>
						</div>
						<ul>
							{ comments.map( ( c ) => (
								<li key={ c.id }>
									{ c.commentText } <small style={ { opacity: 0.7 } }>by { c.createdBy }</small>
								</li>
							) ) }
						</ul>
					</>
				) }
			</div>
		</div>
	);
}

const root = document.getElementById( 'cwc-portal-root' );
if ( root ) {
	render( <PortalApp />, root );
}
