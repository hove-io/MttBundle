define(['jquery'], function($) {

    var layout = {};
    var url_params = {};
    var $icon_tpl = $('<span class="glyphicon"></span>');
    
    layout.init = function($wrapper, blockTypes, objectId, externalNetworkId, stop_point)
    {
        // store url params for later
        url_params.externalNetworkId  = externalNetworkId;
        url_params.timetableId        = objectId;
        url_params.stop_point         = stop_point;
        // needed properties
        layout.blockLevel = stop_point == false ? 'route' : 'stop_point';
        layout.blockTypes = blockTypes;

        $(".loading").hide();

        _bind_listeners();
        _bind_blocks_listeners();
        _bind_blocks_loader_listener();
    };
    
    var _bind_blocks_listeners = function()
    {
        var $blocks = _get_blocks();
        $blocks.each(function(){
            var $block = $(this);
            // bind click listener if there is no menu inside
            if ($block.find('*[role=menu]').length == 0) {
                $block.click(_get_remote_modal);
            } else {
                $block.find('*[role=menu]').siblings('button.btn').dropdown();
                $block.click(function(event){
                    if ($block.find('*[role=menu]').parents('.btn-group').hasClass('open') == false) {
                        $block.find('*[role=menu]').siblings('button.btn').click();
                        return false;
                    }
                });
            }
        });
    };

    var _bind_blocks_loader_listener = function()
    {
        var blockLoader = $('.targetDataCalendar');
        blockLoader.each(function(){
            block = $(this);
            if (block.data('calendar') !== undefined ) {
                block.on('click', function () {
                    var elem  = $(this);
                    var target = $('#' + elem.attr('id') + ' > .target');
                    target.hide();
                    $(".loading." + elem.data('calendar')).show();

                    var param = {
                        'externalNetworkId': elem.data('network_id'),
                        'externalLineId': elem.data('line_id'),
                        'calendarId': elem.data('block_content'),
                        'routeId': elem.data('block_route_id')
                    }

                    $.ajax({
                        type: "POST",
                        url: Routing.generate('canal_tp_mtt_timecard_load_one_calendar', param),
                        cache: false,
                        success: function(data){
                            target.html(data.content);
                            target.show();
                            $(".loading." + elem.data('calendar')).hide();
                        },
                        error: function() {
                            target.show();
                            $(".loading." + elem.data('calendar')).hide();
                        }
                    });
                    return false;

                });
            }
        });
    };

    var _get_remote_modal = function()
    {
        var params = {
            'dom_id'    : $(this).attr('id'),
            'block_type': $(this).data('block-type'),
            'objectType':  $(this).data('block-object'),
            'objectId':  $(this).data('block-object-id')
        };
        $.extend(params, url_params);
        var url = Routing.generate(
            'canal_tp_mtt_block_edit', 
            params
        );
        $('#base-modal').modal({remote: url});
    };
    
    var _get_blocks = function()
    {
        var $blocks = $('.block').each(function(){
            var icon_class = 'glyphicon-';
            $elem = $(this);
            var data = $elem.data();
            if (data.blockLevel == layout.blockLevel && layout.blockTypes[data.blockType])
            {
                if(data.blockType == 'calendar') {
                    var icon_class_loader = icon_class + layout.blockTypes[data.blockType].icon_loader;
                    $elem.prepend($icon_tpl.clone().addClass(icon_class_loader));
                }
                icon_class += layout.blockTypes[data.blockType].icon;
            }
            else
            {
                icon_class += 'ban-circle';
                $elem.addClass('disabled-block');
            }
            $elem.prepend($icon_tpl.clone().addClass(icon_class));

        });

        // return editable blocks only
        return $blocks.filter(function() { 
            return $(this).data("block-level") == layout.blockLevel;
        });
    };
    
    var _bind_listeners = function()
    {
        $('#base-modal').on('loaded.bs.modal', function () {
            var $field = $(this).find('*[data-fill-title]');
            if ($field.length == 1 && $field.data('fillTitle') == true)
            {
                var $titleField = $field.parents('form').find("input[name*='[title]']");
                if ($titleField.length == 1 && $titleField.val() == '')
                {
                    $field.change(function(){
                        $titleField.val($field.find(':selected').text());
                    });
                    // set default value
                    $field.change();
                    // unbind if user types sthg in this title field
                    $titleField.keypress(function(){
                        $field.unbind('change');
                    });
                }
            }
        });
    };
    
    return layout;
});