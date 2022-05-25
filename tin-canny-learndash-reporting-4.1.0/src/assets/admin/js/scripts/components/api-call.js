var uoReportingAPI = {

    reportingApiCall: function (type, data) {

        if(typeof data == 'undefined'){
            data = '';
        }else{
            data = '/'+data
        }

        return jQuery.ajax({
            url: reportingApiSetup.root + type + data,
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', reportingApiSetup.nonce);
            }
        });

    },

    reportingApiCallDataPost: function (type, data) {

        return jQuery.ajax({
            data : data,
            method: 'POST',
            url: reportingApiSetup.root + type,
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', reportingApiSetup.nonce);
            }
        });

    }
};