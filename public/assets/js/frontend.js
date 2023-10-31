( () => {
	"use strict";

	const siteHeader = document.querySelector( '.site-header' );
	const navbarExpander = document.querySelector( '.navbar-expander' );
	const dropdownMenus = document.querySelectorAll( '.dropdown-menu' );
	const mobileNavTrigger = document.querySelector( '.mobile-nav-trigger' );
	const siteNavContainer = document.querySelector( '.site-nav-container' );
	const templatesShowMore = document.querySelector( '.templates-show-more' );
	const filterTriggers = document.querySelectorAll( 'button[data-target]' );
	const frontendLocalNav = document.querySelector( '#frontend-local-navbar' );
	const verticalNav = document.querySelector( '.navbar-vertical' );
	const textRotators = document.querySelectorAll( '.lqd-text-rotator' );
	let siteHeaderOffsetTop = siteHeader?.offsetTop || 0;
	let lastActiveTrigger = null;
	let lastOpenedAccordion = null;

	function onVerticalNavTransitionend( ev ) {
		if ( ev.target !== verticalNav ) return;
		verticalNav.style.whiteSpace = '';
		verticalNav.classList.remove( 'lqd-is-collapsing' );
	}

	function toggleVerticalNavShrink( enterOrLeave ) {
		const navbarShrinkIsActive = localStorage.getItem( 'lqd-navbar-shrinked' );
		if ( navbarShrinkIsActive == 'false' || window.innerWidth <= 991 ) return;
		document.body.classList.toggle( 'navbar-shrinked', enterOrLeave === 'leave' );
		verticalNav.style.whiteSpace = 'nowrap';
		verticalNav.classList.add( 'lqd-is-collapsing' );
		verticalNav.removeEventListener( 'transitionend', onVerticalNavTransitionend );
		verticalNav.addEventListener( 'transitionend', onVerticalNavTransitionend )
	}

	function handleStickyHeader() {
		if ( !siteHeader ) return;
		if ( window.scrollY >= siteHeaderOffsetTop ) {
			siteHeader.classList.add( 'lqd-is-sticky' );
			siteHeader.style.position = 'fixed';
			siteHeader.style.top = '0';
		} else {
			siteHeader.classList.remove( 'lqd-is-sticky' );
			siteHeader.style.position = '';
			siteHeader.style.top = '';
		}
	}

	function onWindowScroll( ev ) {
		handleStickyHeader();
	}

	function onWindowResize() {
		if ( siteHeader ) {
			siteHeader.classList.remove( 'lqd-is-sticky' );
			siteHeader.style.position = '';
			siteHeader.style.top = '';
			siteHeaderOffsetTop = siteHeader?.offsetTop || 0;
		}
		handleStickyHeader();
	}

	textRotators?.forEach( textRotator => {
		const items = textRotator.querySelectorAll( '.lqd-text-rotator-item' );

		if ( !items.length ) return;

		const timeout = 2000;
		let activeIndex = 0;

		textRotator.style.width = `${ items[ activeIndex ].querySelector( 'span' ).clientWidth }px`;

		setInterval( () => {
			// current item
			items[ activeIndex ].classList.remove( 'lqd-is-active' );

			// now next item
			activeIndex = activeIndex === items.length - 1 ? 0 : activeIndex + 1;
			textRotator.style.width = `${ items[ activeIndex ].querySelector( 'span' ).clientWidth }px`;
			items[ activeIndex ].classList.add( 'lqd-is-active' );
		}, timeout );
	} );

	dropdownMenus.forEach( dd => {
		if ( document.body.classList.contains( 'navbar-shrinked' ) ) {
			dd.classList.remove( 'show' );
		}
	} );

	// verticalNav?.addEventListener( 'mouseenter', toggleVerticalNavShrink.bind( verticalNav, 'enter' ) );
	// verticalNav?.addEventListener( 'mouseleave', toggleVerticalNavShrink.bind( verticalNav, 'leave' ) );

	navbarExpander?.addEventListener( 'click', event => {
		event.preventDefault();
		const navbarIsShrinked = document.body.classList.contains( 'navbar-shrinked' );
		document.body.classList.toggle( 'navbar-shrinked' );
		localStorage.setItem( 'lqd-navbar-shrinked', !navbarIsShrinked );
	} );

	document.addEventListener( 'click', ev => {
		const { target } = ev;
		dropdownMenus
			.forEach( dd => {
				if ( !document.body.classList.contains( 'navbar-shrinked' ) && dd.closest( '.primary-nav' ) ) return;
				const clickedOutside = !dd.parentElement.contains( target );
				if ( clickedOutside ) {
					dd.classList.remove( 'show' );
					searchResultsVisible = false;
				};
			} )
	} );

	templatesShowMore?.addEventListener( 'click', ev => {
		ev.preventDefault();
		const list = document.querySelector( '.templates-cards' );
		const overlay = document.querySelector( '.templates-cards-overlay' );
		list.style.overflow = 'visible';
		list.animate(
			[
				// keyframes
				{ maxHeight: '28rem' },
				{ maxHeight: '500rem' },
			],
			{
				// timing options
				duration: 3000,
				easing: 'ease-out',
				fill: 'forwards'
			}
		);
		overlay.animate(
			[
				{ opacity: 0 }
			],
			{
				duration: 650,
				fill: 'forwards',
				easing: 'ease-out'
			}
		);
		const btnAnima = templatesShowMore.animate(
			[
				{ opacity: 0 }
			],
			{
				duration: 650,
				fill: 'forwards',
				easing: 'ease-out'
			}
		);
		btnAnima.onfinish = () => {
			overlay.style.visibility = 'hidden';
			templatesShowMore.style.visibility = 'hidden';
		}
	} );

	filterTriggers?.forEach( trigger => {
		const targetId = trigger.getAttribute( 'data-target' );
		const targets = document.querySelectorAll( targetId );
		const triggerType = trigger.getAttribute( 'data-trigger-type' ) || 'toggle';

		if ( targets.length <= 0 ) {
			return trigger.setAttribute( 'disabled', true );
		};

		trigger.addEventListener( 'click', ev => {
			ev?.preventDefault();

			trigger.classList.add( 'lqd-is-active' );

			if ( triggerType === 'toggle' ) {
				[ ...trigger.parentElement.children ]
					.filter( c => c.getAttribute( 'data-target' ) !== targetId )
					.forEach( c => c.classList.remove( 'lqd-is-active' ) );
			} else if ( triggerType === 'accordion' ) {
				if ( lastActiveTrigger ) {
					lastActiveTrigger.classList.remove( 'lqd-is-active' );
				}
				if ( lastActiveTrigger === trigger ) {
					lastActiveTrigger = null;
				} else {
					lastActiveTrigger = trigger;
				}
			}

			targets?.forEach( t => {
				t.style.display = 'block';
				t.animate(
					[
						{ opacity: 0 },
						{ opacity: 1 },
					],
					{
						duration: 650,
						easing: 'cubic-bezier(.48,.81,.52,.99)'
					}
				);
			} );

			if ( triggerType === 'toggle' ) {
				[ ...targets[ 0 ]?.parentElement?.children ]
					?.filter( c => targetId.startsWith( '.' ) ? !c.classList.contains( targetId.replace( '.', '' ) ) : c.getAttribute( 'id' ) !== targetId.replace( '#', '' ) )
					?.forEach( c => c.style.display = 'none' );
			} else if ( triggerType === 'accordion' ) {
				if ( lastOpenedAccordion ) {
					lastOpenedAccordion.style.display = 'none';
				}
				if ( lastOpenedAccordion === targets[ 0 ] ) {
					lastOpenedAccordion = null;
				} else {
					lastOpenedAccordion = targets[ 0 ];
				}
			}
		} )
	} );

	if ( frontendLocalNav ) {
		const scrollspy = VanillaScrollspy( { menu: frontendLocalNav } )
		scrollspy.init()
	}

	mobileNavTrigger?.addEventListener( 'click', ev => {
		ev.preventDefault();
		mobileNavTrigger.classList.toggle( 'lqd-is-active' );
		siteNavContainer.classList.toggle( 'lqd-is-active' );
	} );


	// Copy to clipboard
	document.addEventListener( 'click', ev => {

		const button = ev.target.closest( '.lqd-clipboard-copy' );

		if ( !button ) return;

		const settings = JSON.parse( button.getAttribute( 'data-copy-options' ) || {} );
		let getContentFrom;

		if ( settings.contentIn ) {
			if ( settings.contentIn.startsWith( '<' ) && settings.content ) {
				const el = button.parentElement.closest( settings.contentIn.replace( '<', '' ) );
				getContentFrom = el.querySelector( settings.content );
			}
		} else {
			getContentFrom = settings.content ? document.querySelector( settings.content ) : button.parentElement
		}

		if ( getContentFrom ) {
			navigator.clipboard.writeText( getContentFrom.innerText );
			toastr.success( magicai_localize?.content_copied_to_clipboard || 'Content copied to clipboard' );
		}
	} )

	window.addEventListener( 'scroll', onWindowScroll );

	window.addEventListener( 'resize', onWindowResize );

} )();