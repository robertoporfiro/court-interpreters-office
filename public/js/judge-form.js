/** public/js/judge-form.js */

/* global  $, fail, displayValidationErrors */

$(document).ready(function(){
    // this whole thing needs cleaning up
    $("#add-location").on("show.bs.modal",function(){

        // event.relatedTarget is the button they clicked, FYI
        $("#add-location > div > div > div.modal-body").load(
            "/admin/locations/add?form_context=judges form",
            function(){
                $(".modal-header .modal-title").text("add a courthouse/courtroom");
                // use the modal's buttons instead
                $("#add-location input[name=\"submit\"]").remove();
            });
    });
    var courthouseSelect = $("#courthouse");
    var courtroomSelect = $("#courtroom");
    // if there is no courthouse selected, disable the courtroom control
    if (! courthouseSelect.val()) {
        courtroomSelect.val("").attr({disabled : "disabled"});
    }
    courthouseSelect.on("change",function(){
        var parent_id = courthouseSelect.val();
        if (! parent_id) {
            return courtroomSelect.val("").attr({disabled : "disabled"});
        }
        $.getJSON("/admin/locations/courtrooms/"+parent_id, null,
            function(data){
                var options = [], i = -1;
                $.each(data,function(){
                    options[++i] = $("<option/>").attr({label:this.name, value: this.id }).text(this.name);
                });
                if (courtroomSelect.children().length > 1) {
                    // might want to cache these later
                    // var discard =
                    courtroomSelect.children().slice(1).remove();
                }
                courtroomSelect.append(options);
            }
        );
    });
    var defaultLocation = $("#defaultLocation");
    $("#courthouse, #courtroom").on("change",function(event){
        var selectElement = $(event.target);
        // if the courtroom changed
        if ("courtroom" === selectElement.attr("id")) {
            defaultLocation.val(selectElement.val());
        } else {
            // the courthouse is what changed. if there is no courtroom,
            // the courthouse value becomes the default-location value
            if (! courtroomSelect.val()) {
                defaultLocation.val(selectElement.val());
            }
            if (courthouseSelect.val()) {
                courtroomSelect.prop("disabled",false);
            }
        }
        console.debug("set defaultLocation value to: "+defaultLocation.val());
    });
    // initialize this...
    defaultLocation.val(courtroomSelect.val() || courthouseSelect.val());

    $("#add-location").on("click","#btn-add-location-submit", function(){
        $.post("/admin/locations/add?form_context=judges",$("#add-location form").serialize(),
            function(response){
                if (! response.valid) {
                    return displayValidationErrors(response.validationErrors);
                }
                $("#add-location").modal("hide");
                $("#defaultLocation").val(response.entity.id);
                $("#courthouse").prop("disabled",false);
                var entity = response.entity;
                var targetElement = $("#"+entity.type);
                $("<option>")
                    .attr({label: entity.name, value: entity.id})
                    .text(entity.name)
                    .appendTo(targetElement);
                targetElement.val(response.entity.id);
                if (entity.parent_location_id) {
                    $("#courthouse").val(entity.parent_location_id);
                }
            });
    });

    var form = $("#judge-form");
    $("#btn-delete").on("click",function(event){
        event.preventDefault();
        if (! window.confirm("Are you sure you want to delete this judge?")) {
            return;
        }
        var name = form.data().judge_name;
        var id = $("input[name='judge[id]'").val();
        var url = `/admin/judges/delete/${id}`;
        $.post(url,{name})
            .done(()=>
                window.document.location = `${window.basePath||""}/admin/judges`)
            .fail(fail);
    });
});
