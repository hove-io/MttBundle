define('mtt/line_timetable/select_stops', ['jquery', 'jquery_ui_sortable', 'translations/messages'], function($, sortable) {
    var selectionChanged = false;
    var underProgress = false;
    var errorDiv = "<div class='alert alert-danger alert-dismissable danger'><span></span><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button></div>";

    /**
     * Saving selection event
     */
    $('.save-selection').on('click', function() {
        _saveSelection();
    });

    /**
     * Selecting a route event
     */
    $('#select-route').on('change', function() {
        if (selectionChanged && confirm(Translator.trans('line_timetable.message.save_stop_selection')))
            _saveSelection();
        else
            window.location = Routing.generate('canal_tp_mtt_line_timetable_select_stops', _getSelectedRoute());
    });

    /**
     * Saving selection
     * 1/ Sending selection using ajax POST in order to update it in database
     * 2/ Redirecting or displaying an error message
     */
    var _saveSelection = function() {
        if (underProgress === true)
            return false;

        underProgress = true;

        var stopPoints = _getSelectedStopPoints();
        $.post(
            Routing.generate('canal_tp_mtt_line_timetable_select_stops', _getSelectedRoute()),
            JSON.stringify({
                'externalRouteId': $('#available-stops li:first').data('external-route-id'),
                'stopPoints': stopPoints
            })
        ).done(function(data) {
            if (data.status)
                window.location = data.location;
            else {
                if ($('#main-container').find('.alert-danger'))
                    $('#main-container .alert-danger').remove();
                $('#main-container').prepend(errorDiv).find('.alert-danger span').html(data.content);
            }
            underProgress = false;
        });

        return true;
    };

    /**
     * Getting all selected stop points in an array
     */
    var _getSelectedStopPoints = function()
    {
        var stopPoints = [];
        $('#selected-stops li.list-group-item').each(function(index) {
            stopPoints.push({
                'id': $(this).data('id'),
                'external_stop_point_id': $(this).data('external-stop-point-id'),
                'external_route_id': $(this).data('external-route-id'),
                'rank': index+1
            });
        });

        return stopPoints;
    };

    /**
     * Getting route parameters in an array
     */
    var _getSelectedRoute = function()
    {
        var route = $('#select-route :selected');

        var param = {
            'externalNetworkId': route.data('external-network-id'),
            'externalRouteId': route.data('external-route-id'),
            'seasonId': route.data('season-id'),
            'lineTimetableId': route.data('line-timetable-id')
        };

        return param;
    };

    /**
     * Selecting all available stop points event
     */
    $('#select-all').on('click', function() {
        $('#available-stops li.list-group-item').each(function() {
            if (!$(this).hasClass('active')) {
                _selectStopPoint($(this));
            }
        });
        _refreshAllButtons();

        return false;
    });

    /**
     * Selecting all available stop points event
     */
    $('#remove-all').on('click', function() {
        $('#available-stops li.list-group-item').each(function() {
            if ($(this).hasClass('active')) {
                $(this).removeClass('active');
            }
        });

        $('#selected-stops li.list-group-item').detach();
        _refreshAllButtons();

        return false;
    });

    /**
     * Selecting one stop point event
     */
    $('#available-stops .add-stop-point').on('click', function() {
        _selectStopPoint($(this).parent());
        _refreshAllButtons();

        return false;
    });

    /**
     * Adding a stop point to selection panel
     */
    var _selectStopPoint = function(stopRow) {
        var oldContainer = stopRow.parents('.list-group');
        var newContainer = oldContainer.parent().siblings('div').find('.sortable');
        var stopRowClone = stopRow.clone();

        _bindingRemoveEvent(stopRowClone.find('.remove-stop-point'));
        newContainer.append(stopRowClone);
        stopRow.addClass('active');
        newContainer.trigger('sortupdate');
    };

    /**
     * Removing a stop point from selection panel
     */
    var _bindingRemoveEvent = function(stopRemoveButton) {
        $(stopRemoveButton).on('click', function() {
            var newContainer = $(this).parents('.list-group');
            $(this).parent().detach();
            $('#available-stops li[data-external-stop-point-id="' + $(this).parent().data('external-stop-point-id') + '"]').removeClass('active').find('.add-stop-point').show();
            _refreshAllButtons();

            return false;
        });
    };

    /**
     * Binding remove event on buttons
     */
    $('#selected-stops .remove-stop-point').each(function() {
        _bindingRemoveEvent(this);
    });

    /**
     * Refreshing select-all button state
     */
    var _refreshAllButtons = function() {
        if ($('#selected-stops li').length === $('#available-stops li').length)
            $('#select-all').addClass('disabled').prop('disabled', true);
        else
            $('#select-all').removeClass('disabled').prop('disabled', false);

        if ($('#selected-stops li').length === 0)
            $('#remove-all').addClass('disabled').prop('disabled', true);
        else
            $('#remove-all').removeClass('disabled').prop('disabled', false);

        selectionChanged = true;
    };

    /**
     * Binding sortable function on right panel
     */
    $('.list-group.sortable').sortable({
        placeholder: "sortable-dropzone list-group-item",
        items: "> li",
        connectWith: "ul.list-group.sortable"
    }).on(
        'sortupdate',
        function( event, ui ){
            var $list = $(this);
            if ($list.find('.list-group-item').length > 0) {
                $list.find('> span').addClass('display-none');
                $list.removeClass('empty-list');
                if ($list.attr('id') == 'selected-stops') {
                    $('#generate-distribution-list, .save-area').removeClass('disabled');
                }
            } else {
                $list.find('> span').removeClass('display-none');
                $list.addClass('empty-list');
                if ($list.attr('id') == 'selected-stops') {
                    $('#generate-distribution-list, .save-area').addClass('disabled');
                }
            }
            selectionChanged = true;
        }
    );
    $('.list-group.sortable').disableSelection();
});
