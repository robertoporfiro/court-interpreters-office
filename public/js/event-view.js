$(function(){
    var noteworthy = ["date","time","type","interpreters","location"];
    var email_flag = false;
    if ( $('span.interpreter').length && $("ins, del").length) {
        $("ins, del").each(function(){
            var field = ($(this).parent().prev("div").text().trim());
            if (noteworthy.includes(field)) {
                email_flag = true;
                return false;
            }
        });
    }
    console.log(`email flag? ${email_flag}`);

});