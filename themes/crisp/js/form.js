$(document).ready(function(){
    var frm = $('#contact-form');
    
    frm.change(function (ev) {
        validate([$('#naam'), $('#email'), $('#bericht')]);    
    });
    
    frm.submit(function (ev) {
        // First validate
        var valid = validate([$('#naam'), $('#email'), $('#bericht')]);
        
        if(valid){
            $.ajax({
                type: frm.attr('method'),
                url: frm.attr('action'),
                data: frm.serialize(),
                success: function (data) {
                    if(data == 'success'){
                        $('.contact__success').removeClass('hide');
                    }else{
                        $('.contact__error').removeClass('hide');
                    }
                }
            });
        }

        ev.preventDefault();
    });   
});

function validate(required) {

    var valid = true;
    $(required).each(function(){
        if($(this).val().length < 1){
            valid = false;
            $(this).parent('.form-group').addClass('has-error');
        }else{
            $(this).parent('.form-group').removeClass('has-error');
        }
    });
    return valid;
        
}
