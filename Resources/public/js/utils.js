define(['jquery'], function($){
    var utils = {};
    var tpl = {
        msgWrapperTpl: $('<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>')
    };
    
    utils.getTpl = function(tplName)
    {
        if (tpl[tplName])
            return tpl[tplName].clone();
        else
            return false
    };

    utils.disableBtn = function($btn)
    {
        $btn.addClass('disabled').find('span.glyphicon').hide();
        $btn.prepend('<span class="glyphicon glyphicon-refresh icon-refresh-animate"></span>');
        if ($btn.is('a')) {
            $btn.unbind('click');
            $btn.click(function(){
                return false;
            });
        }
    };

    return utils;
});