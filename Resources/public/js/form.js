require(['jquery'], function($){
    $('.modal').on(
        'submit',
        'form',
        function(){
            var $form = $(this);
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