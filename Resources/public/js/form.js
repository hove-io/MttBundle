require(['jquery'], function($){
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
                        alert('error');
                    }
                },
                'error':function(xhr){
                    document.open();
                    document.write(xhr.responseText);
                    document.close();
                }
            });
            return false;
        }
    );
});