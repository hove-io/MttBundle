define(['jquery', 'bootstrap'], function($) {
    var validator = {};
    var $wrapper,
        $msgWrapperTpl = $('<div class="alert alert-danger alert-dismissable"></div>');
    ;
    
    
    validator.init = function(params){
        $wrapper = params.wrapper;

        return validator;
    };
    
    validator.validate = function(){
        var errors = [];
        // check if content is bigger than block wrapper
        $wrapper.find('.block > *[data-validate-height="1"]').each(function(){
            if ($(this).children().height() > $(this).height())
            {
                $(this).parents('.block').addClass('error');
                errors.push();
            }
        });
    };
    
    return validator;
})