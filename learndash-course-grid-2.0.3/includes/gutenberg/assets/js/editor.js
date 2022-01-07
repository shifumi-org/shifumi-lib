( function() {
    function init_masonry() {
        // Masonry
        const wrappers = document.querySelectorAll( '.learndash-course-grid[data-skin="masonry"]' );
        wrappers.forEach( function( wrapper ) {
            const items_wrapper = wrapper.querySelector( '.items-wrapper.masonry' );

            if ( items_wrapper ) {
                const first_item = items_wrapper.querySelector( '.item' );
                if ( ! first_item ) {
                    return;
                }
            }

            learndash_course_grid_init_masonry( items_wrapper );
        } );
    }
    
    document.addEventListener( 'click', function( e ) {
        const el = e.target;

        if ( el.closest( '.learndash-block-inner > .learndash-course-grid' ) ||  el.closest( '.learndash-block-inner > .learndash-course-grid-filter' ) ) {
            e.preventDefault();
        }
    } );
    
    setInterval( function() {
        learndash_course_grid_init_grid_responsive_design();

        const temp_css = document.querySelectorAll( '.learndash-course-grid-temp-css' );

        if ( temp_css ) {
            const css_wrapper = document.getElementById( 'learndash-course-grid-custom-css' );

            if ( css_wrapper ) {
                let style = '';

                temp_css.forEach( function( element ) {
                    style += element.innerText;
                } )

                css_wrapper.innerHTML = style;
            }
        }
    }, 500 );

    setInterval( function() {
        init_masonry();
    }, 2000 );
} )();