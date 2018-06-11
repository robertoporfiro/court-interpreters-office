/** public/js/common.js
 *
 * code that is common to most if not all pages in the application
 */


var $, jQuery;
var basePath;
/**
 * redirect to login page if, e.g., session has timed out
 */
$( document ).ajaxComplete(function(event, xhr) {
    if (xhr.getResponseHeader("X-Authentication-required")) {
        document.location = (basePath || "/") + "login";
        //var doc = $(xhr.responseText);
    }
});

/** experimental: prepend basePath if such exists */
jQuery.ajaxSetup({
    beforeSend : function(xhr,settings) {
        if (window.basePath && window.basePath.length) {
            settings.url = window.basePath + settings.url;
        }
    }
});

/**
 * displays validation errors on a form
 *
 * @param object validationErrors
 * @param object options
 * @returns void
 */
var displayValidationErrors = function(validationErrors,options) {
    $(".validation-error").hide();
    var debug = (options && options.debug) || false;
    for (var field in validationErrors) {
        for (var key in validationErrors[field]) {
            var message = validationErrors[field][key];
            var element = $("#" +field);
            if (! element.length) {
                // nothing to lose by trying harder; undo camelcase
                var id = "#" + field.replace(/([A-Z])/g,"_$1").toLowerCase();
                element = $(id);
            }
            var errorDiv = $("#error_"+field);
            if (! errorDiv.length) { errorDiv = null;}
            if (! element.length) {
                if (debug) { console.log("is there no element "+field+ " ?"); }
                // look for an existing div by id
                if ($("#error_"+field).length) {
                    $("#error_"+field).html(message).show();
                } else {
                    if (debug) {
                        console.warn("no element with id "+field + ", and nowhere to put message: "+message);
                    }
                }
            } else {
                errorDiv = errorDiv || element.next(".validation-error");
                if (! errorDiv.length) {
                    errorDiv = $("<div/>")
                        .addClass("alert alert-warning validation-error")
                        .attr({id:"error_"+field})
                        .insertAfter(element);
                }
                errorDiv.html(message).show();
            }
            break;
        }
    }
};
