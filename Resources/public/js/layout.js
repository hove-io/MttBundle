define(['jquery'], function($) {

    var layout = {};
    var $icon_tpl = $('<span class="glyphicon"></span>');
    
    layout.init = function($wrapper, blockTypes, timetableId, externalCoverageId, stop_point)
    {
        // needed properties
        layout.blockLevel = stop_point == false ? 'route' : 'stop_point';
        layout.blockTypes = blockTypes;
        _bind_listeners();
        var $blocks = _get_blocks();
        // bind click listener
        $blocks.click(function(){
            var url = Routing.generate(
                'canal_tp_meth_block_edit', 
                { 
                    'dom_id'            : $(this).attr('id'),
                    'timetableId'       : timetableId,
                    'externalCoverageId': externalCoverageId,
                    'block_type'        : $(this).data('block-type'), 
                    'stop_point'        : stop_point 
                }
            );
            $('#base-modal').modal({remote: url});
        });
        // berk
        $blocks.find('.no-ajax').click(function(){
            window.location = $(this).attr('href');
            return false;
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
    
    var _bind_listeners = function()
    {
        // to prevent modal content to be cached by bootstrap
        $('#base-modal').on('hidden.bs.modal', function () {
            $(this).removeData('bs.modal');
            $(this).find('.modal-header, .modal-body, .modal-footer').empty();
        });
        $('#base-modal').on('loaded.bs.modal', function () {
            var $field = $(this).find('*[data-fill-title]');
            if ($field.length == 1)
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