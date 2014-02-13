define(['jquery'], function($) {
    var layout = {};
    
    var $icon_tpl = $('<span class="glyphicon"></span>');
    
    layout.init = function($wrapper, blockTypes, routeExternalId, externalCoverageId, stop_point)
    {
        // needed properties
        layout.blockLevel = stop_point == false ? 'route' : 'stop_point';
        layout.blockTypes = blockTypes;
        // to prevent modal content to be cached by bootstrap
        $('#base-modal').on('hidden.bs.modal', function () {
            $(this).removeData('bs.modal');
        });
        var $blocks = _get_blocks();
        // bind click listener
        $blocks.click(function(){
            var url = Routing.generate(
                'canal_tp_meth_block_edit', 
                { 
                    'block_type'        : $(this).data('block-type'), 
                    'routeExternalId'   : routeExternalId, 
                    'externalCoverageId': externalCoverageId, 
                    'dom_id'            : $(this).attr('id'),
                    'stop_point'        : stop_point, 
                }
            );
            $('#base-modal').modal({remote: url});
        });
    };
    
    var _get_blocks = function()
    {
        var $blocks = $('.block').each(function(){
            var icon_class = 'glyphicon-';
            $elem = $(this);
            var data = $elem.data();
            if (data.blockLevel == layout.blockLevel && layout.blockTypes[data.blockType])
            {
                icon_class += layout.blockTypes[data.blockType].icon;
            }
            else
            {
                icon_class += 'ban-circle';
                $elem.addClass('disabled-block');
            }
            $elem.append($icon_tpl.clone().addClass(icon_class));
        });
        // return editable blocks only
        return $blocks.filter(function() { 
            return $(this).data("block-level") == layout.blockLevel;
        });
    };
    
    return layout;
});