define(['jquery', 'bootstrap'], function($) {
    var layout = {};
    
    layout.init = function($wrapper)
    {
        $('.block').each(function(){
            var data = $(this).data();
            console.dir(data);
        });
    };
    
    return layout;
});