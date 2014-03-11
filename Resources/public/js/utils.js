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
    
    return utils;
});