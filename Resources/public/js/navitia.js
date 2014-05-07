define('navitia', ['jquery'], function($) {
    var self = {};
    var _url = null;

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
            callbackFail();
        });
    };

    return function Navitia(){
        return self;
    }
});
