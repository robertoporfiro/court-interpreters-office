$(function(){
    $("#motd, #motw").resizable({
        //handles : 'all',
        stop: function (event,ui) {
            var type = $(event.target).attr("id");
            var settings = {[type]: {
                size: {
                    width: `${ui.size.width}px`,
                    height: `${ui.size.height}px`
                }
            }};
            console.warn(settings);
            $.post("/admin/notes/update-settings",settings);
        }
    });
    $("#motd, #motw").draggable({
        stop: function (event,ui) {
            //var settings = {};
            var settings = {[event.target.id] : {
                position: {
                    top: `${ui.position.top}px`,
                    left: `${ui.position.left}px`
                }
            }};
            $.post("/admin/notes/update-settings",settings);
        }
    });

    $("#btn-motd, #btn-motw").on("click",function(e){
        e.preventDefault();
        var type = e.target.id.indexOf('motd') > -1 ? 'motd':'motw';
        var div = $(`#${type}`);
        div.toggle();
        var visible = div.is(":visible");
        $(`#btn-${type}`).text(`${visible ? "hide":"show"} ${type.toUpperCase()}`);
        $.post("/admin/notes/update-settings",{[type]: {visible: visible ? 1 : 0}})
    });
    var motd_visible = $("#motd").is(":visible");
    $("#btn-motd").text(`${motd_visible ? "hide":"show"} MOTD`);
    var motw_visible = $("#motw").is(":visible");
    $("#btn-motw").text(`${motw_visible ? "hide":"show"} MOTW`);


});