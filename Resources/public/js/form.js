require(['jquery'], function($){
    $('.modal').on(
        'submit',
        'form',
        function(){
            var $form = $(this);
            $.ajax({
                'type': 'POST',
                'url':$(this).attr('action'),
                'data':$(this).serialize(),
                statusCode: {
                    302: function() {
                      alert( "302 found" );
                    }
                },
                'success': function(data, textStatus){
                    console.log('success');
                    // console.dir(data);
                    $form.replaceWith(data);
                },
                'complete':function(data){
                    console.log('complete');
                    // console.dir(data);
                },
                'error':function(data, textStatus){
                    console.log('error');
                    // console.dir(data);
                }
            });
            return false;
        }
    )
});