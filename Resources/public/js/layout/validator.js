define(['jquery', 'mtt/translations/default'], function($) {
    var validator = {};
    var $wrapper,
        $msgWrapperTpl = $('<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>');
    ;
    
    validator.init = function(params){
        $wrapper = params.wrapper;

        return validator;
    };
    
    validator.validate = function(){
        var errors = [];
        var msg_added = false;
        // check if content is bigger than block wrapper
        $wrapper.find('.block > *[data-validate-height="1"]').each(function(){
            if ($(this).children().height() > $(this).height())
            {
                $(this).parents('.block').addClass('error');
                if (msg_added == false)
                {
                    errors.push(Translator.trans('calendar.error.content_higher_than_wrapper', {}, 'default'));
                    msg_added = true;
                }
            }
        });
        if (errors.length > 0)
        {
            for (var error in errors)
            {
                $msgWrapperTpl.append('<div>' + errors[error] + '</div>');
            }
            $('.navbar').append($msgWrapperTpl);
        }
    };
    
    return validator;
})