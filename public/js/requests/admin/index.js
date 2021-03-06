/**
 * public/js/requests/admin/index.js
 *
 * for /admin/requests main page
 */

var moment, schedule_request_callback;

var update_verbiage = function(count) {
    if ( "undefined" === typeof count) {
        count = $("#pending-requests tbody tr").length;
    }
    var verbiage = `${count} request`;
    if (count !== 1) {
        verbiage += "s";
    }
    $("#requests-pending").text(verbiage);
    var thead = $("#pending-requests table > thead");
    if (! count) {
        thead.hide();
    } else {
        if (! thead.is(":visible")) {
            thead.show();
        }
    }
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
        var csrf = row.parent().data("csrf");
        $.post(`/admin/requests/schedule/${id}`,{csrf})
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
                if (response.message.match(/already.*schedule|request.*cancel/i)) {
                    row.remove();
                    update_verbiage();
                }
            }
        }).fail(fail);
    // keep track of whatever dropdown is being shown
    // in case the parent <table> gets replaced
    }).on("show.bs.dropdown","td.dropdown",function(event){
            $("table").data({dropdown_id : event.relatedTarget.id});
        }
    // or not
    ).on("hide.bs.dropdown","td.dropdown",function(){
            $("table").data({dropdown_id :null});
        }
    );
    // periodically refresh interpreter-request data
    var html = $("#pending-requests tbody").html();
    var refresh = function refresh(){
        $.get(document.location.href)
        .then((response)=>{

            var this_html = $(response).find("tbody").html();
            var updated = html !== this_html;
            console.warn("updated? "+ (updated ? "yes" : "no"));
            //console.log(html); console.log('----'); console.log(this_html);
            if (updated) {
                html = this_html;
                var dropdown_id = $("table").data("dropdown_id");
                $("#pending-requests").html(response);
                // restore any previously-showing dropdown
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

    // https://getbootstrap.com/docs/4.4/components/navs/#events
    $("#scheduled-requests-tab").on("show.bs.tab",function(e){
        console.log("time to load future requests...");
        $.get('/admin/requests/scheduled').then((res)=>{
            $("#scheduled-requests").html(res);
        });

    });

    $("#pending-requests-tab").on("show.bs.tab",function(e){
        console.log("time to load PENDING requests...");
        $.get('/admin/requests').then((res)=>{
            $("#pending-requests").html(res);
        });

    });

    $("#tab-content").on("click",".pagination a",function(e){
        e.preventDefault();
        var tab = $(this).closest(".tab-pane");
        $.get(this.href).then((html)=>tab.html(html));
    });
});
