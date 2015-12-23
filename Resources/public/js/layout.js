define(['jquery', 'sf_routes'], function($) {

    var layout = {};
    var global_params = {};
    var $icon_tpl = $('<span class="glyphicon"></span>');

    layout.init = function($wrapper, globalParameters)
    {
        global_params = globalParameters;

        _bind_listeners();
        _bind_add_block_listener();
        _bind_blocks_listeners();
        _bind_action_bar();
        _bind_auto_create();

    };

    var _bind_auto_create = function()
    {
        $(document).ready(function() {
            $(document).find(".auto-create").each(function() {
                var params = {
                    'rank'      : $(this).data('rank'),
                    'domId'     : $(this).attr('id') === undefined ? '' : $(this).attr('id'),
                    'blockType' : $(this).data('type'),
                };
                $.extend(params, global_params);

                $.ajax({
                    type: "POST",
                    url: Routing.generate('canal_tp_mtt_block_auto_create', params),
                    cache: false,
                    error: function(data) {
                        $loader.detach();
                        $(this).html('<div class="alert alert-danger" role="alert">'+data.responseJSON+'</div>').show(1000);
                    }
                });
            });
        });
    };

    var _bind_add_block_listener = function()
    {
        $(document).on("click", ".add-block", function(event) {
            event.preventDefault();
            var params = {
                'rank': $(this).data('rank')
            };
            $.extend(params, global_params);
            var url = Routing.generate(
                'canal_tp_mtt_block_add',
                params
            );

            $('#base-modal').modal({remote: url});
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
            'domId'     : $(this).attr('id') === undefined ? '' : $(this).attr('id'),
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
            $(this).prepend($icon_tpl.clone().addClass(icon_class));
        });

        return $blocks;
    };

    var _bind_listeners = function()
    {
        $('#base-modal').on('loaded.bs.modal', function () {
            var $field = $(this).find('*[data-fill-title]');
            if ($field.length == 1 && $field.data('fillTitle') == '1')
            {
                var $titleField = $field.parents('form').find("input[name*='[title]']");
                if ($titleField.length == 1 && $titleField.val().length === 0)
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

    var _bind_action_bar = function()
    {
        $('.action-bar .action-button').each(function() {
            var action = $(this);
            if (action.data('action-type') !== undefined) {
                switch (action.data('action-type')) {
                    case 'load-calendar':
                        _action_data_load(action);
                        break;
                    case 'delete-calendar':
                        _action_delete_calendar(action);
                        break;
                    default:
                        // do nothing
                }
            }
        });
    };

    var _action_data_load = function(action) {
        action.on('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            var $container = $(this).parents('.' + $(this).data('action-container'));
            var $target = $container.find('.' + $(this).data('action-target'));

            var params = {
                'blockId': $(this).data('id'),
                'columnsLimit': $(this).closest('.line').data('columns')
            };
            $.extend(params, {'externalNetworkId': global_params.externalNetworkId});
            $loader = $('.loading-indicator').clone();
            $target.hide().empty().parent().prepend($loader.show());
            $.ajax({
                type: "POST",
                url: Routing.generate('canal_tp_mtt_line_timetable_load_calendar', params),
                cache: false,
                success: function(data) {
                    $loader.detach();
                    $target.html(data.content).show(1000);
                },
                error: function(data) {
                    $loader.detach();
                    $target.html('<div class="alert alert-danger" role="alert">'+data.responseJSON+'</div>').show(1000);
                }
            });

            return false;
        });
    };

    var _action_delete_calendar = function(action) {
        action.on('click', function(event) {
            event.preventDefault();
            event.stopPropagation();

            var params = {
                'blockId': $(this).data('id')
            };

            $.extend(params, global_params);

            $.ajax({
                type: "POST",
                url: Routing.generate('canal_tp_mtt_block_delete', params),
                success: function(data) {
                    window.location = data.location;
                },
                error: function(data) {
                    console.log(data.content);
                }
            });
        });
    };

    return layout;
});
