/**
 * Find whether a variable is a string
 *
 * @param   {mixed}     variable - The variable being evaluated
 * @return  {boolean}   TRUE if the variable is an string
 */

function isString( string ){
    return typeof string === 'string';
}

/**
 * Find whether a variable is an object
 *
 * @param   {mixed}     variable - The variable being evaluated
 * @return  {boolean}   TRUE if the variable is an object
 */

function isObject( variable ){
    return variable !== null && typeof variable === 'object';
}

/**
 * Remove all spaces from a string
 *
 * @param   {string}    String
 * @return  {string}    String without spaces
 */

function removeSpaces( string ){
    return string.replace( /\s/g, '' );
}

/**
 * Check if a value exists in an array
 * If the needle is an array then it will check if all his elements exists in the haystack
 *
 * @param   {mixed}     needle - The searched value. The comparison between values is strict. If the needle is an array then it will check if all his elements exists in the haystack
 * @param   {array}     haystack - An array through which to search.
 *
 * @return  {boolean} TRUE if needle is found in the array or if needle is an array, TRUE if all the elements from the needle are in haystack
 */

function inArray( needle, haystack ){
    let response = false;

    if ( Array.isArray( needle ) ){
        // Check that all the elements from the array "value" are in the array "array"
        response = arrayDifference( needle, haystack ).length == 0;
    }
    else {
        // Check if one element is in an array
        response = $.inArray( needle, haystack ) !== -1;
    }

    return response;
}

/**
 * Determine if a variable is set and is not NULL
 *
 * @param  {mixed}      variable - The variable being evaluated
 * @return {boolean}    TRUE if the variable is defined
 */

function isDefined( variable ){
    // Returns true if the variable is undefined
    return typeof variable !== 'undefined' && variable !== null;
}

/**
 * Determine whether a variable is empty
 *
 * @param   {mixed}     variable - The variable being evaluated
 * @return  {boolean}   TRUE if the variable is empty
 */

function isEmpty( variable ){
    let response = true;

    // Check if the variable is defined, otherwise is empty
    if ( isDefined( variable ) ){
        // Check if it's array
        if ( $.isArray( variable ) ){
            response = variable.length == 0;
        }
        else if ( isObject( variable ) ){
            response = $.isEmptyObject( variable );
        }
        else {
            response = variable == '';
        }
    }

    return response;
}