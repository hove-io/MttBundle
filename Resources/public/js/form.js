require(['jquery'], function($){
    function displayError(responseText) {
        document.open();
        document.write(responseText);
        document.close();
    }

    $('.modal').on(
        'submit',
        'form',
        function(){
            var $form = $(this);
            var $submitBtn = $form.find('button[type=submit]');
            $submitBtn.addClass('disabled').find('span.glyphicon').hide();
            $submitBtn.prepend('<span class="glyphicon glyphicon-refresh icon-refresh-animate"></span>');
            $.ajax({
                'type': 'POST',
                'url':$(this).attr('action'),
                'data':new FormData($form[0]),
                'processData':false,
                'contentType': false,
                'success': function(data, textStatus){
                    if (data.status == false) {
                        $form.replaceWith(data.content);
                    } else if (data.status == true) {
                        window.location = data.location;
                    } else {
                        displayError(data);
                    }
                },
                'error':function(xhr) {
                    displayError(xhr.responseText)
                }
            });
            return false;
        }
    );
});