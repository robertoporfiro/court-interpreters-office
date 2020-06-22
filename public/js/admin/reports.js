/* global displayValidationErrors, moment, $ */


var dp_defaults = {
    // dateFormat:"yy-mm-dd",
    showOtherMonths : true,
    selectOtherMonths : true,
    changeMonth : true,
    changeYear : true
};

$(function () {
    var btn = $("#btn-run");
    var form = btn.closest("form");
    var today = moment();
    var date_range = $("#date-range");
    date_range.on("change",function(){
        var to, from;
        var from_el = $("#date-from");
        var to_el = $("#date-to");
        switch (date_range.val()) {
        case "YTD":
            to = today;
            from = moment([today.year()]);           
            break;
        case "QTD":
            switch (today.quarter()) {
            case 1:
                from = moment([today.year()]); 
                break;
            case 2:
                from = moment([today.year(),3]); 
                break;
            case 3:
                from = moment([today.year(),6]);
                break;
            case 4: 
                from = moment([today.year(),9]);                
            }            
            to = today;
            break;
        case "PY":
            var YYYY = today.year() - 1;
            from = moment([YYYY]);
            to = moment(from).add(1,"year").subtract(1,"day");
            break;
        case "PQ":
            YYYY = today.year();
            switch (today.quarter()) {
            case 1:
                from = moment([YYYY - 1, 9]);
                break;
            case 2:
                from = moment([YYYY]);
                break;
            case 3:
                from = moment([YYYY,3]);
                break;
            case 4:
                from = moment([YYYY,6]);
                break;                    
            }
            to = moment(from).add(3,"month").subtract(1, "day");
            break;
        case "FYTD":
            if (today.month() >= 9) {
                from = moment([today.year(),9]);                
            } else {
                from = moment([today.year()-1,9]);
            }
            to = today;           
            break;
        case "PFY":
            if (today.month() >= 9) {
                from = moment([today.year()-1,9]);
            } else {
                from = moment([today.year()-2,9]);
            }
            to = moment(from).add(1, "year").subtract(1,"day");
            break;
        case "CUSTOM":
            to_el.val("").removeAttr("readonly");
            from_el.val("").removeAttr("readonly")[0].focus();            
            break;
        default:
            break;
        }
        if (from && to) {
            from_el.val(from.format("MM/DD/YYYY")).attr({readonly:true});
            to_el.val(to.format("MM/DD/YYYY")).attr({readonly:true});
        }
    });
    $("input.date").datepicker(dp_defaults);
    btn.on("click", function (e) {
        e.preventDefault();        
        var params = form.serialize();
        $.get(form.attr("action"), params)
            .then(res => {
                if (res.validation_errors) {
                    return displayValidationErrors(res.validation_errors);
                }
                console.log(res);
            });
    });
});