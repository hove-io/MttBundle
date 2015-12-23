define(['jquery'], function($) {

    var layout = {};
    var global_params = {};
    var $icon_tpl = $('<span class="glyphicon"></span>');

    layout.init = function($wrapper, lineTimetableId, externalNetworkId)
    {
        // store global parameters
        global_params.externalNetworkId = externalNetworkId;
        global_params.timetableId       = lineTimetableId;
        global_params.type              = 'line';

        // needed properties
        $(document).ready(function() {
            $('.action-button').tooltip({
                placement: 'right',
                animation: true,
                delay: { "show": 500, "hide": 100 }
            });
            $('button.edit-note').button({
                icons: {
                    primary: "ui-icon-gear"
                }
            });
        });

        _bind_listeners();
        _bind_add_block_listener();
        _bind_blocks_listeners();
    };

    var _bind_listeners = function()
    {
        $('#base-modal').on('loaded.bs.modal', function () {
            var $field = $(this).find('*[data-fill-title]');
            if ($field.length == 1 && $field.data('fill-title') === 1)
            {
                var $titleField = $field.parents('form').find("input[name*='[title]']");
                if ($titleField.length == 1 && $titleField.val() === '')
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


    var _bind_blocks_listeners = function()
    {
        var $blocks = _get_blocks();
        $blocks.each(function() {
            var $block = $(this);
            // bind click listener if there is no menu inside
            if ($block.find('*[role=menu]').length === 0) {
                $block.click(_get_remote_modal);
            } else {
                $block.find('*[role=menu]').siblings('button.btn').dropdown();
                $block.click(function(event) {
                    if ($block.find('*[role=menu]').parents('.btn-group').hasClass('open') === false) {
                        $block.find('*[role=menu]').siblings('button.btn').click();
                        return false;
                    }
                });
            }
        });
    };

    var _get_remote_modal = function()
    {
        var params = {
            'rank'      : $(this).data('rank'),
            'domId'     : $(this).id('domId'),
            'blockType' : $(this).data('type'),
            'blockId'   : $(this).data('id')
        };
        $.extend(params, global_params);
        var url = Routing.generate(
            'canal_tp_mtt_block_edit',
            params
        );
        $('#base-modal').modal({remote: url});
    };

    var _get_blocks = function()
    {
        var $blocks = $('.block').each(function() {
            var icon_class = 'glyphicon-'+$(this).data('icon');
            var data = $(this).data();
            $(this).prepend($icon_tpl.clone().addClass(icon_class));
        });

        // return editable blocks only
        return $blocks.filter(function() {
            return $(this).data("block-level") == layout.blockLevel;
        });
    };

    return layout;
});
