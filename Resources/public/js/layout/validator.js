define(['jquery', 'utils', 'translations/default'], function($, utils) {
    var validator = {};
    var $wrapper,
        $msgWrapperTpl = utils.getTpl('msgWrapperTpl');

    validator.init = function(params){
        $wrapper = params.wrapper;

        return validator;
    };

    validator.validate = function(){
        var errors = [];
        var msg_added = false;
        // check if content is bigger than block wrapper
        $wrapper.find('*[data-validate-size="1"]').each(function(){
            if ($(this).parents('.frequency-content').length != 0 &&
                ($(this).find('span').height() > $(this).height() || $(this).find('span').width() > $(this).width())
                ) {
                $(this).addClass('error');
                errors.push(Translator.trans('calendar.error.content_higher_than_wrapper', {}, 'default'));
            }
            else if ($(this).children('table:not(.frequencies-table)').height() > $(this).height() ||
                $(this).children('table:not(.frequencies-table)').width() > $(this).width()
            ) {
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
            $('.breadcrumb').after($msgWrapperTpl);
        }
    };

    return validator;
})
