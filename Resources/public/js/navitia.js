define('navitia', ['jquery'], function($) {
    var self = {};
    var _url = '';
    
    var _set_url = function()
    {
        _url = self.host + '/' + self.version + '/' + self.api + '/';
    };
    
    var _send_request = function(filter, callback)
    {
        $.get(_url + filter, callback);
    };
    
    self.getCoverageNetworks = function(coverage_id, callback) 
    {
        _send_request(coverage_id + '/networks', function(data){
            callback(data.networks);
        });
    };
    
    return function Navitia(params){
        self.host = params.host;
        self.version = params.version;
        self.api = params.api;
        _set_url();
        
        return self;
    }
});