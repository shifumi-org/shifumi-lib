( function() {
    document.addEventListener( 'click', function( e ) {
        const el = e.target;

        if ( el.closest( '.learndash-block-inner > .learndash-course-grid' ) ||  el.closest( '.learndash-block-inner > .learndash-course-grid-filter' ) ) {
            e.preventDefault();
        }
    } );

    setInterval( function() {
        learndash_course_grid_init_grid_responsive_design();

        const items_wrappers = document.querySelectorAll( '.learndash-course-grid .items-wrapper.masonry' );
    }, 500 );

    ( function() {
        setInterval( function() {
            const temp_css = document.querySelectorAll( '.learndash-course-grid-temp-css' );

            if ( temp_css ) {
                const css_wrapper = document.getElementById( 'learndash-course-grid-custom-css' );

                let style = '';

                temp_css.forEach( function( element ) {
                    style += element.innerText;
                } )

                css_wrapper.innerHTML = style;
            }

            // Masonry
            const wrappers = document.querySelectorAll( '.learndash-course-grid .masonry' );

            wrappers.forEach( function( wrapper ) {
                learndash_course_grid_init_masonry( wrapper );
            } );
        }, 500 );
    } )();
} )();