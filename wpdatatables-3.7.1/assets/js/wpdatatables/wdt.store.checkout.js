(function () {
    document.addEventListener('click', function (event) {

        // If the clicked element doesn't have the right selector, bail
        if (!event.target.matches('.tms-store-checkout')) return;

        // Don't submit button
        event.preventDefault();

        // Render checkout dialog
        wdtRenderStoreDialog(event.target.id)

    }, false);

    let eventMethod = window.addEventListener ? 'addEventListener' : 'attachEvent';

    let eventer = window[eventMethod];

    let messageEvent = eventMethod === "attachEvent" ? 'onmessage' : 'message';

    // Listen to message from child IFrame window
    eventer(messageEvent, function (e) {
        if (e.data === 'tmsStoreCloseIFrame') {
            document.getElementById('tms-store-iframe').remove()
        }
    }, false);
})();

function wdtRenderStoreDialog(elementId) {
    let iframe = document.createElement('iframe');
    iframe.id = 'tms-store-iframe';
    iframe.setAttribute(
        'style',
        'z-index: 9999999999; display: block; background-color: transparent; border: 0px none transparent; overflow-x: hidden; overflow-y: auto; visibility: visible; margin: 0px; padding: 0px; -webkit-tap-highlight-color: transparent; position: fixed; left: 0px; top: 0px; width: 100%; height: 100%;')

    iframe.src = tmsStore.url + 'static/pages/' + elementId + '.html';
    document.body.appendChild(iframe)
}

