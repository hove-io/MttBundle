require(
    ['jquery', 'mtt/utils', 'mtt/form/collection', 'mtt/translations/messages'], 
    function($, utils, collection){
    
        var $form = $('.modal-dialog .form-with-collection');
        collection.init($form.find('[data-count]'));
        var error_msg_keys = [],
        error_msg = [];
        var $msgWrapperTpl = utils.getTpl('msgWrapperTpl');
        
        $form.find('textarea').keyup(function(){
            var maxlength = $(this).attr('maxlength');
            var value = $(this).val();
            var old_text = $(this).next('span').text();
            var new_text = old_text.replace(/(\d+)/, maxlength - value.length);
            $(this).next('span').text(new_text);
        });
        
        $form.submit(function(){
            error_msg_keys = [];
            error_msg = [];
            var no_error = true;
            var $form = $(this);
            $form.find('.form-group').removeClass('has-error');
            var frequencies = $form.serializeObject().block_frequencies_coll.frequencies;
            for(var i = 0;i < frequencies.length;i++) {
                parse_frequency_hours(frequencies[i]);
                if (parseInt(frequencies[i].startTime.hour) >= parseInt(frequencies[i].endTime.hour)) {
                    $form.find("#block_frequencies_wrapper .row")
                        .eq(i)
                        .find('.form-group div.bootstrap-time')
                        .parents('.form-group')
                        .addClass('has-error');
                    add_error_msg('error.start_end_time_incoherent');
                }
                for(var j = i + 1;j < frequencies.length;j++) {
                    parse_frequency_hours(frequencies[j]);
                    if (
                        // startTime included in other range?
                        (frequencies[i].startTime.hour >= frequencies[j].startTime.hour && 
                        frequencies[i].startTime.hour <= frequencies[j].endTime.hour) ||
                        // endTime included in other range?
                        (frequencies[i].endTime.hour <= frequencies[j].endTime.hour && 
                        frequencies[i].endTime.hour >= frequencies[j].startTime.hour)
                    ) {
                        // console.log('error between ' + i + ' and ' + j);
                        $form.find("#block_frequencies_wrapper .row").eq(i).find('.form-group').addClass('has-error');
                        $form.find("#block_frequencies_wrapper .row").eq(j).find('.form-group').addClass('has-error');
                        add_error_msg('error.frequencies_conflict');
                    }
                }
            }
            display_errors();
            return error_msg.length == 0;
        });
        
        var parse_frequency_hours = function(frequency)
        {
            frequency.startTime.hour = parseInt(frequency.startTime.hour);
            frequency.endTime.hour = parseInt(frequency.endTime.hour);
        };
        
        // mutualize with layout/validator.js
        var display_errors = function()
        {
            $msgWrapperTpl.find(':not(button)').remove();
            if (error_msg.length > 0)
            {
                for (var error in error_msg)
                {
                    $msgWrapperTpl.append('<div>' + error_msg[error] + '</div>');
                }
                $('.modal-body').prepend($msgWrapperTpl);
            }
        };
        
        var add_error_msg = function(msg_key)
        {
            // console.dir(msg_key);
            // console.log($.inArray(msg_key, error_msg_keys));
            if ($.inArray(msg_key, error_msg_keys) == -1) {
                error_msg_keys.push(msg_key);
                error_msg.push(Translator.trans(msg_key, {}, 'messages'));
            } 
        };
    }
);