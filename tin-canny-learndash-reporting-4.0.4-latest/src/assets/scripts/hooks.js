/**
 * @package Uncanny TinCan API
 * @author Uncanny Owl
 * @version 1.0.0
 */

// Hooks
var tinCanny = {
	hooks: { action: {}, filter: {} },
	addAction: function( action, callable, priority, tag ) {
		tinCanny.addHook( 'action', action, callable, priority, tag );
	},
	addFilter: function( action, callable, priority, tag ) {
		tinCanny.addHook( 'filter', action, callable, priority, tag );
	},
	doAction: function( action ) {
		tinCanny.doHook( 'action', action, arguments );
	},
	applyFilters: function( action ) {
		return tinCanny.doHook( 'filter', action, arguments );
	},
	removeAction: function( action, tag ) {
		tinCanny.removeHook( 'action', action, tag );
	},
	removeFilter: function( action, priority, tag ) {
		tinCanny.removeHook( 'filter', action, priority, tag );
	},
	addHook: function( hookType, action, callable, priority, tag ) {
		if ( undefined === tinCanny.hooks[hookType][action] ) {
			tinCanny.hooks[hookType][action] = [];
		}
		var hooks = tinCanny.hooks[hookType][action];
		if ( undefined === tag ) {
			tag = action + '_' + hooks.length;
		}
        if( priority === undefined ){
            priority = 10;
        }

        tinCanny.hooks[hookType][action].push( { tag:tag, callable:callable, priority:priority } );
	},
	doHook: function( hookType, action, args ) {

        // splice args from object into array and remove first index which is the hook name
        args = Array.prototype.slice.call(args, 1);

		if ( undefined !== tinCanny.hooks[hookType][action] ) {
			var hooks = tinCanny.hooks[hookType][action], hook;
			//sort by priority
			hooks.sort(function(a,b) {
				return a.priority - b.priority;
			});

			if( !window )
				return;

			for( var i=0; i<hooks.length; i++) {
                hook = hooks[i].callable;

                if(typeof hook != 'function' && window && hook in window )
                    hook = window[hook];
				if ( 'action' == hookType ) {
                    hook.apply(null, args);
				} else {
                    args[0] = hook.apply(null, args);
				}
			}
		}
		if ( 'filter' == hookType ) {
			return args[0];
		}
	},
	removeHook: function( hookType, action, priority, tag ) {
		if ( undefined !== tinCanny.hooks[hookType][action] ) {
			var hooks = tinCanny.hooks[hookType][action];
			for( var i=hooks.length-1; i>=0; i--) {
				if ((undefined === tag||tag==hooks[i].tag) && (undefined===priority||priority==hooks[i].priority)){
					hooks.splice(i,1);
				}
			}
		}
	}
};
