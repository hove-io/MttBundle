define('navitia', ['jquery', 'mtt/utils', 'mtt/translations/messages'], function($, utils) {
    var self = {};
    var _url = null;
    var $msgWrapperTpl = utils.getTpl('msgWrapperTpl');

    var _set_url = function(params)
    {
        _url = Routing.generate(
            'canal_tp_mtt_network_list_json',
            params
        );
    }

    self.getCoverageNetworks = function(params, callback, callbackFail)
    {
        _set_url(params);
        $.get(_url, function(data){
            callback(data.networks);
        }).fail(function() {
            var msg = Translator.trans('network.error.wrong_token', {}, 'message');

            $msgWrapperTpl.append('<div>' + msg + '</div>');
            $('.modal-header').after($msgWrapperTpl);
            callbackFail();
        });
    };

    return function Navitia(){
        return self;
    }
});
