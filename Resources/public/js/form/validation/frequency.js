define(
    ['jquery', 'utils', 'mtt/form/collection', 'translations/messages'],
    function($, utils, collection){

        var frequencyForm = {};
        var error_msg_keys = [],
        error_msg = [],
        $msgWrapperTpl = utils.getTpl('msgWrapperTpl');

        frequencyForm.init = function()
        {
            var $form = $('.modal-dialog .form-with-collection');
            collection.init($form.find('[data-count]'));
            bindListeners($form);
        };


        var bindListeners = function($form)
        {
            $form.on(
                'keyup',
                'textarea',
                function(){
                    var maxlength = $(this).attr('maxlength');
                    var value = $(this).val();
                    var old_text = $(this).next('span').text();
                    var new_text = old_text.replace(/(\d+)/, maxlength - value.length);
                    $(this).next('span').text(new_text);
                }
            );
        };

        return frequencyForm;
    }
);
