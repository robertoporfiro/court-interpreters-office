/** public/js/interpreters-index.js */
$(function(){
    $('[data-toggle="tooltip"]').tooltip();
    var languageSelect = $('#language_id');
    var languageButton = $('#btn-search-language');
    languageButton.on("click",function(event){
        event.preventDefault();
        var language_id = languageSelect.val() || "0";
        var url = languageButton.attr('href');
        url += '/language/' + language_id;
        url += '/active/'+$('#active').val();
        var security = $('#security_clearance_expiration').val();
        url += '/security/'+security;
        document.location = url;
    });
    var nameElement = $('#name');
    nameElement.autocomplete({
        source : /*window.basePath+*/'/admin/interpreters',
        minLength : 2,
        select : function( event, ui ) {
           nameElement.data({ interpreterName : ui.item.label, interpreterId: ui.item.id });
           //console.log("select. shit is real");
           $('#btn-search-name').trigger("click");
        }
    });

    if (document.referrer.indexOf('interpreters/language')!== -1) {
        // for their convenience, point back to index page in its previous state
        $("h2 a:contains(interpreters)").attr({href:document.referrer })
    }

    $('#btn-search-name').on("click",function(event){
        event.preventDefault();
        var name = nameElement.val().trim();
        if (! name) {
            return;
        }

        var url = `${window.basePath}/admin/interpreters`;
        var selected = nameElement.data();
        // if we have an interpreter id, use it in the url
        if (name === selected.interpreterName) {
            url  += "/" + selected.interpreterId;
        } else {
            //'route' => '/name/:lastname[/:firstname]',
            var pos = name.lastIndexOf(',');
            if (-1 === pos) {
                url += "/name/"+name.trim();
            } else {
                 var lastname = encodeURIComponent(name.substring(0,pos).trim());
                 var firstname = encodeURIComponent(name.substr(pos+1).trim());
                 url += "/name/"+ lastname + "/" + firstname;
            }
        }
        document.location = url;


    });

    /**
     * require re-authentication to decrypt and display ssn and dob
     */

    $('#auth-submit').on("click",function(){
    var input = {
        identity : $('#form-login input.thing1').val(),
        password : $('#form-login input.thing2').val(),
        login_csrf : $('input[name="login_csrf"').val()
    };
    var url = /*window.basePath +*/ '/login';
    $.post(url, input, function(response)
        {
            if (response.validation_errors) {
                //refresh the CSRF token
                $('input[name="login_csrf"').val(response.login_csrf);
                // since we hacked the names, translate them back
                var errors = {};
                errors[$('.thing1').attr("id")] = response.validation_errors.identity;
                errors[$('.thing2').attr("id")] = response.validation_errors.password;
                return displayValidationErrors(errors);
            }
            if (response.authenticated) {
                $.post('/vault/decrypt',{
                    dob  : $('#encrypted_dob').val(),
                    ssn  : $('#encrypted_ssn').val(),
                    csrf : response.csrf
                },function(data){
                    /** @todo handle errors! */
                    $('#dob').text(data.dob);
                    $('#ssn').text(data.ssn);
                    $('#login-modal').modal('hide');
                });
            } else {
                return $('#div-auth-error').text(response.error).show();
            }
        });
    });
});
