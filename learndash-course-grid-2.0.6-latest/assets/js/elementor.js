/**
 * Elementor compatibility script
 */

( function() {
    let gridFound, masonryFound;
    const gridObserver = new MutationObserver( function( mutations ) {
        mutations.forEach( function( mutationRecord ) {
            const grid = mutationRecord.target.querySelector( '.learndash-course-grid' ),
                skin = grid.dataset.skin,
                display = mutationRecord.target.style.display;

            if ( 'none' !== display ) {
                if ( 'grid' === skin ) {
                    gridFound = true;
                } else if ( 'masonry' === skin ) {
                    masonryFound = true;
                }
            }
        } );

        if ( gridFound && 'function' === typeof learndash_course_grid_init_grid_responsive_design ) {
            learndash_course_grid_init_grid_responsive_design();
        }

        if ( masonryFound && 'function' === typeof learndash_course_grid_init_masonry_responsive_design ) {
            learndash_course_grid_init_masonry_responsive_design();
        }
    } );
    
    const grids = document.querySelectorAll( '.learndash-course-grid' );
    grids.forEach( function( grid ) {
        const target = grid.closest( '.elementor-tab-content' );
        if ( target ) {
            gridObserver.observe( target, { attributes : true, attributeFilter : [ 'style' ] } );
        }
    } );
} )();