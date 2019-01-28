/**
 * public/js/requests/admin/index.js
 *
 * for /admin/requests main page
 */

var moment, schedule_request_callback;

var update_verbiage = function(count) {
    if ( "undefined" === typeof count) {
        count = $("tbody tr").length;
    }
    var verbiage = `${count} request`;
    if (count !== 1) {
        verbiage += "s";
    }
    $("#requests-pending").text(verbiage);
}

/**
 * how often to reload the requests data (via xhr)
 * @type {Number}
 */
const requests_refresh_interval = 60000;

$(function(){
    // event listeners for dropdowns in each table row
    $("#tab-content").on("click","a.request-add", function(e) {
        // add request to the schedule, then remove the TR
        // from the DOM
        e.preventDefault();
        var row = $(this).closest("tr");
        var id = row.data().id;
        $.post(`/admin/requests/schedule/${id}`)
        .then((response)=>{
            console.log(response);
            if (response.status === "success") {
                schedule_request_callback(response);
                var count = row.siblings().length;
                row.slideUp(function(){
                    $(this).remove();
                    update_verbiage(count);
                });
            }
            if (response.status === "error") {
                show_error_message(response);
                if (response.message.match(/already.*schedule/i)) {
                    row.remove();
                    update_verbiage();
                }
            }
        }).fail(fail);
    // keep track of whatever dropdown is being shown
    }).on("show.bs.dropdown","td.dropdown",function(event){
            $("table").data({dropdown_id : event.relatedTarget.id});
        }
    // or not
    ).on("hide.bs.dropdown","td.dropdown",function(){
            $("table").data({dropdown_id :null});
        }
    );
    // periodically refresh interpreter-request data
    var html = $("tbody").html();
    var refresh = function refresh(){
        $.get(document.location.href)
        .then((response)=>{
            var doc = $(response);
            var this_html = doc.find("tbody").html();
            if (! this_html) { console.warn("error? no TBODY html");  }
            var updated = html !== this_html;
            console.warn("updated? "+ (updated ? "yes" : "no"));
            if (updated) {
                html = this_html;
                $("tbody").html(this_html)
                // restore any previously-showing dropdown
                var dropdown_id = $("table").data("dropdown_id");
                if (dropdown_id) {
                    console.log("we're a class act, restoring your dropdown");
                    // could not get .dropdown("show") to work \-:
                    $(`#${dropdown_id}`).trigger("click");
                }
                update_verbiage();
            }
            setTimeout(refresh,requests_refresh_interval);
        });
    };
    setTimeout(refresh,requests_refresh_interval);
});
