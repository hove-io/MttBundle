define(
    ['jquery', 'utils', 'mtt/form/collection', 'translations/messages', 'sf_routes'],
    function($, utils, collection){

        var frequencyForm = {};
        var error_msg_keys = [],
        error_msg = [],
        $msgWrapperTpl = utils.getTpl('msgWrapperTpl');
        var externalNetworkId = null;

        frequencyForm.init = function(network)
        {
            var $form = $('.modal-dialog .form-with-collection');
            collection.init($form.find('[data-count]'));
            bindListeners($form);
            externalNetworkId = network;
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

            $form.on('click', '.btn-check', function(event) {
                event.preventDefault();

                var loader = $('.loading-indicator').clone();
                var form = $(this).parents('.frequency-form');

                var time = form.find('[id$=time]').val();

                // time input validation
                if (time === undefined ||
                    time.length === 0 ||
                    Math.floor(time) != time ||
                    !$.isNumeric(time) ||
                    parseInt(time) <= 0
                   ) {
                    form.find('[id$=time]').parent().addClass('has-error');
                    return false;
                } else {
                    form.find('[id$=time]').parent().removeClass('has-error');
                }

                var target = form.find('.check-frequency');
                target.show().html(loader.show());

                var startHour = form.find('[id$=startTime_hour]').val();
                var startMinute = form.find('[id$=startTime_minute]').val();
                var endHour  = form.find('[id$=endTime_hour]').val();
                var endMinute = form.find('[id$=endTime_minute]').val();

                var params = {
                    'externalNetworkId': externalNetworkId,
                    'blockId': form.data('block-id'),
                    'frequency': {
                        'limits': {
                            'min': startHour + startMinute + '00',
                            'max': endHour + endMinute + '00'
                        },
                        'time': time
                    }
                };

                $.ajax({
                    type: "POST",
                    url: Routing.generate('canal_tp_mtt_frequency_check', params),
                    cache: false,
                    success: function(data) {
                        target.hide().html(data.content).show(1000);
                    },
                    error: function() {
                        loader.detach();
                    }
                });
            });
        };

        return frequencyForm;
    }
);
