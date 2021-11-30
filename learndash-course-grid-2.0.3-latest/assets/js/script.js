( function() {
    function ajax_apply_filter( course_grid, filter ) {
        const data = {
            action: 'ld_cg_apply_filter',
            nonce: LearnDash_Course_Grid.nonce.load_posts,
            filter: prepare_filter( filter ),
            course_grid: {
                ...course_grid.dataset
            }
        };

        data.filter = JSON.stringify( data.filter );
        data.course_grid = JSON.stringify( data.course_grid );

        fetch( LearnDash_Course_Grid.ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams( data ),
        } )
            .then( response => {
                return response.json();
            } )
            .then( data => {
                if ( typeof data !== 'undefined' ) {
                    if ( data.status == 'success' ) {
                        const items_wrapper = course_grid.querySelector( '.items-wrapper' );

                        items_wrapper.style.visibility = 'hidden';
                        items_wrapper.innerHTML = data.html;

                        course_grid.dataset.page = data.page;

                        const pagination = course_grid.querySelector( '.pagination' );

                        if ( ! pagination ) {
                            course_grid.insertAdjacentHTML( 'beforeend', data.html_pagination );
                        }

                        if ( data.html_pagination == '' ) {
                            course_grid.querySelector( '.pagination' ).remove();
                        }

                        if ( course_grid.dataset.skin == 'grid' ) {
                            setTimeout( function() {
                                learndash_course_grid_init_grid_responsive_design();
                            }, 500 );
                        } else if ( course_grid.dataset.skin == 'masonry' ) {
                            setTimeout( function() {
                                learndash_course_grid_init_masonry( course_grid.querySelector( '.masonry' ) );
                            }, 500 );
                        } else {
                            setTimeout( function() {
                                items_wrapper.style.visibility = 'visible';
                            }, 500 );
                        }
                        
                    }
                }
            } )
            .catch( error => {
                console.log( error );
            } );
    }

    function ajax_init_infinite_scrolling( el ) {
        const wrapper = el.closest( '.learndash-course-grid' );

        if ( ! wrapper ) {
            infinite_scroll_run = false;
            return false;
        }

        const filter = document.querySelector( '.learndash-course-grid-filter[data-course_grid_id="' + wrapper.id + '"]' );
    
        const data = {
            action: 'ld_cg_load_more',
            nonce: LearnDash_Course_Grid.nonce.load_posts,
            course_grid: JSON.stringify( wrapper.dataset ),
            filter: JSON.stringify( prepare_filter( filter ) ),
        };

        fetch( LearnDash_Course_Grid.ajaxurl + '?' + new URLSearchParams( data ), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
        } )
            .then( response => {
                return response.json();
            } )
            .then( data => {
                if ( typeof data !== 'undefined' ) {
                    if ( data.status == 'success' ) {
                        wrapper.querySelector( '.items-wrapper' ).insertAdjacentHTML( 'beforeend', data.html );

                        if ( data.page !== 'complete' ) {
                            wrapper.dataset.page = data.page;
                        }
                        
                        if ( wrapper.dataset.pagination == 'infinite' ) {
                            infinite_scroll_run = false;
                        }

                        if ( data.page == 'complete' ) {
                            const pagination = wrapper.querySelector( '.pagination' );

                            pagination.remove();
                        }

                        if ( wrapper.dataset.skin == 'grid' ) {
                            setTimeout( function() {
                                learndash_course_grid_init_grid_responsive_design();
                            }, 500 );
                        } else if ( wrapper.dataset.skin == 'masonry' ) {
                            wrapper.style.visibility = 'hidden';
    
                            setTimeout( function() {
                                learndash_course_grid_init_masonry( wrapper.querySelector( '.masonry' ) );
    
                                wrapper.style.visibility = 'visible';
                            }, 500 );
                        }
                    }
                }
            } )
            .catch( error => {
                console.log( error );
            } );
    }

    function in_viewport( element ) {
        var pos = element.getBoundingClientRect();
        return ! ( pos.top > innerHeight || pos.bottom < 0 );
    }

    function prepare_filter( filter ) {
        let data = {};

        if ( ! filter ) {
            return data;
        }

        const search = filter.querySelector( 'input[name="search"]' );
        const price_min = filter.querySelector( '[name="price_min"]' );
        const price_max = filter.querySelector( '[name="price_max"]' );

        data.search = search ? search.value : null;

        let taxonomies = filter.dataset.taxonomies;
        taxonomies = taxonomies.split( ',' ).map( function( value ) {
            return value.trim();
        } );

        data.price_min = price_min ? price_min.value : null;
        data.price_max = price_max ? price_max.value : null;

        taxonomies.forEach( function( taxonomy ) {
            const inputs = filter.querySelectorAll( 'input[name="' + taxonomy + '[]"]:checked' );

            let values = [];
            inputs.forEach( function( input ) {
                values.push( input.value );
            } );

            data[ taxonomy ] = values;
        } );

        return data;
    }
    
    // Toggle filter display handler
    document.addEventListener( 'click', function( e ) {
        const el = e.target;
        if ( el.matches( '.learndash-course-grid .toggle-filter' ) ) {
            if ( el.nextElementSibling.style.display === 'none' || el.nextElementSibling.style.display === '' ) {
                el.classList.remove( 'closed' );
                el.classList.add( 'opened' );
                el.nextElementSibling.style.display = 'block';
            } else  {
                el.classList.remove( 'opened' );
                el.classList.add( 'closed' );
                el.nextElementSibling.style.display = 'none';
            }
        }
    } );

    // Apply filter handler
    const filter_submit = document.querySelectorAll( '.learndash-course-grid-filter .button.apply' );

    if ( filter_submit ) {
        filter_submit.forEach( function( el ) {
            el.addEventListener( 'click', function( e ) {
                e.preventDefault();

                const filter = this.closest( '.learndash-course-grid-filter' );

                if ( filter ) {
                    const course_grid = document.getElementById( filter.dataset.course_grid_id );
                    
                    ajax_apply_filter( course_grid, filter );
                }

                if ( filter.previousElementSibling &&  filter.previousElementSibling.classList.contains( 'toggle-filter' ) ) {
                    filter.previousElementSibling.classList.remove( 'opened' );
                    filter.previousElementSibling.classList.add( 'closed' );
                    filter.style.display = 'none';
                }

            } );
        } );
    }

    // Clear filter handler
    const filter_clear = document.querySelectorAll( '.learndash-course-grid-filter .button.clear' );

    if ( filter_clear ) {
        filter_clear.forEach( function( el ) {
            el.addEventListener( 'click', function( e ) {
                e.preventDefault();
    
                const filter = this.closest( '.learndash-course-grid-filter' );
                
                if ( filter ) {
                    const search = filter.querySelector( 'input[name="search"]' );
                    const price_min = filter.querySelector( 'input[name="price_min"]' );
                    const price_max = filter.querySelector( 'input[name="price_max"]' );
                    const price_min_range = filter.querySelector( 'input[name="price_min_range"]' );
                    const price_max_range = filter.querySelector( 'input[name="price_max_range"]' );

                    if ( search ) {
                        filter.querySelector( 'input[name="search"]' ).value = '';
                    }

                    if ( price_min ) {
                        filter.querySelector( 'input[name="price_min"]' ).value = '';
                    }
                    
                    if ( price_max ) {
                        filter.querySelector( 'input[name="price_max"]' ).value = '';
                    }

                    if ( price_min_range ) {
                        filter.querySelector( 'input[name="price_min_range"]' ).value = '';
                    }

                    if ( price_max_range ) {
                        filter.querySelector( 'input[name="price_max_range"]' ).value = '';
                    }

                    filter.dataset.taxonomies.split( ',' ).forEach( function( taxonomy ) {
                        taxonomy = taxonomy.trim();

                        if ( taxonomy != '' ) {
                            filter.querySelectorAll( 'input[name="' + taxonomy + '[]"]' ).forEach( function( input ) {
                                input.checked = false;
                            } );
                        }
                    } );

                    const course_grid = document.getElementById( filter.dataset.course_grid_id );
                    
                    ajax_apply_filter( course_grid, filter );

                    if ( filter.previousElementSibling &&  filter.previousElementSibling.classList.contains( 'toggle-filter' ) ) {
                        filter.previousElementSibling.classList.remove( 'opened' );
                        filter.previousElementSibling.classList.add( 'closed' );
                        filter.style.display = 'none';
                    }
                }
            } );
        } );
    }

    // Dynamic input value update for price filter inputs
    document.addEventListener( 'input', function( e ) {
        if ( e.target.classList.contains( 'range' ) ) {
            const name = e.target.name,
                value = e.target.value,
                price_wrapper = e.target.closest( '.filter' );
    
            switch ( name ) {
            case 'price_min_range':
                price_wrapper.querySelector( '[name="price_min"]' ).value = value;
                break;
            
            case 'price_max_range':
                price_wrapper.querySelector( '[name="price_max"]' ).value = value;
                break;
            }
        }

        if ( e.target.closest( '.number-wrapper' ) !== null && e.target.type == 'number' ) {
            const name = e.target.name,
                value = e.target.value,
                price_wrapper = e.target.closest( '.filter' );

            switch ( name ) {
            case 'price_min':
                price_wrapper.querySelector( '[name="price_min_range"]' ).value = value;
                break;
            
            case 'price_max':
                price_wrapper.querySelector( '[name="price_max_range"]' ).value = value;
                break;
            }
        }
    } );

    /**
     * Pagination
     */

    // Load more button pagination handler
    document.addEventListener( 'click', function( e ) {
        const el = e.target;

        if ( ! el.matches( '.learndash-course-grid[data-pagination="button"] .pagination .load-more' ) ) {
            return;
        }

        e.preventDefault();

        const wrapper = el.closest( '.learndash-course-grid' );
        const filter = document.querySelector( '.learndash-course-grid-filter[data-course_grid_id="' + wrapper.id + '"]' );

        const data = {
            action: 'ld_cg_load_more',
            nonce: LearnDash_Course_Grid.nonce.load_posts,
            course_grid: JSON.stringify( wrapper.dataset ),
            filter: JSON.stringify( prepare_filter( filter ) ),
        };

        // AJAX request
        fetch( LearnDash_Course_Grid.ajaxurl + '?' + new URLSearchParams( data ), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
        } )
            .then( response => {
                return response.json();
            } )
            .then( data => {
                if ( typeof data !== 'undefined' ) {
                    if ( data.status == 'success' ) {
                        const items_wrapper = wrapper.querySelector( '.items-wrapper' );
                        
                        items_wrapper.insertAdjacentHTML( 'beforeend', data.html );

                        if ( data.page !== 'complete' ) {
                            wrapper.dataset.page = data.page;
                        }

                        if ( data.page == 'complete' ) {
                            wrapper.querySelector( '.pagination' ).remove();
                        }
                        
                        if ( wrapper.dataset.skin == 'grid' && data.html != '' ) {
                            setTimeout( function() {
                                learndash_course_grid_init_grid_responsive_design();
                            }, 500 );
                        } else if ( wrapper.dataset.skin == 'masonry' && data.html != '' ) {
                            wrapper.style.visibility = 'hidden';

                            setTimeout( function() {
                                learndash_course_grid_init_masonry( wrapper.querySelector( '.masonry' ) );

                                wrapper.style.visibility = 'visible';
                            }, 500 );
                        }
                    }
                }
            } )
            .catch( error => {
                console.log( error );
            } );
    } );

    // Infinite scrolling handler
    let infinite_scroll_run = false;
    document.addEventListener( 'scroll', function() {
        const infinite_scroll_elements = document.querySelectorAll( '.learndash-course-grid[data-pagination="infinite"] .pagination' );

        if ( infinite_scroll_elements ) {
            infinite_scroll_elements.forEach( function( infinite_scroll ) {
                // Make sure the function is called only once
                if ( in_viewport( infinite_scroll ) && ! infinite_scroll_run ) {
                    infinite_scroll_run = true;
                    ajax_init_infinite_scrolling( infinite_scroll );
                }
            } );
        }
    } );
} )();