define('mtt/stop', ['jquery', 'jquery_ui_sortable'], function($, sortable) {
    var stop = {};
    var isChange = false;
    var underProgress = false;

    // Add All Stop points
    $('#add-all-stop-points.btn').click(function(){
        $('ul#excluded-stops > li.list-group-item').each(function(){
            if (!$(this).hasClass('active')) {
                _addStopPointInArea($(this).find('> a.toggle-stop-point-btn'));
            }
        })
        _refreshAddAllStopPointsButton();
        return (false);
    })

    $('.dropdown-menu').on('click', function(e) {
        if($(this).hasClass('checkbox-dropdown-menu')) {
            e.stopPropagation();
        }
    });

    //Add to stoppoints list
    $('ul#excluded-stops .toggle-stop-point-btn').click(function(){
        _addStopPointInArea($(this));
        _refreshAddAllStopPointsButton();
        return false;
    });

    // Remove StopPoint inf included
    $('ul#included-stops .toggle-stop-point-btn').click(function(){
        var $stopElement = $(this).parent();
        var $newContainer = $stopElement.parents('.list-group');
        $(this).parent().detach();
        $('ul#excluded-stops li[data-stop-point-id="' + $(this).parent().data('stop-point-id') + '"][data-route-id="' + $(this).parent().data('route-id') + '"]').removeClass('active').find('.add-stop-point-btn').show();
        $newContainer.trigger('sortupdate');
        _refreshAddAllStopPointsButton();

        return false;
    });

    var _refreshAddAllStopPointsButton = function() {
        var $addAllBtn = $('#add-all-stop-points');
        var $excludedStopsActive = $('#excluded-stops').find('li.active').length;
        var $excludedStops = $('#excluded-stops').find('li.list-group-item').length;
        var $excludedStopsIsEmpty = $('#excluded-stops').hasClass('empty-list');

        if ($excludedStopsIsEmpty || $excludedStopsActive >= $excludedStops) {
            $addAllBtn.addClass('disabled');
        } else {
            $addAllBtn.removeClass('disabled');
        }

        isChange = true;
    };

    var _addStopPointInArea = function(element) {
        var $stopElement = element.parent();
        var $oldContainer = $stopElement.parents('.list-group');
        var $newContainer = $oldContainer.parent().siblings('div').find('.sortable');
        var $stopElementClone = $stopElement.clone();

        //Remove from area list
        $stopElementClone.find('.toggle-stop-point-btn').click(function(){
            $(this).parent().detach();
            $('ul#excluded-stops li[data-stop-point-id="' + element.parent().data('stop-point-id') + '"][data-route-id="' + element.parent().data('route-id') + '"]').removeClass('active').find('.add-stop-point-btn').show();
            $newContainer.trigger('sortupdate');

            _refreshAddAllStopPointsButton();
            return false;
        });
        $newContainer.append($stopElementClone);
        $stopElement.addClass('active');
        $newContainer.trigger('sortupdate');

    }

    //Draggable
    $('.list-group.sortable').sortable({
        placeholder: "sortable-highlight list-group-item",
        items: "> li",
        connectWith: "ul.list-group.sortable"
    }).on(
        'sortupdate',
        function( event, ui ){
            var $list = $(this);
            if ($list.find('.list-group-item').length > 0) {
                $list.find('> span').addClass('display-none');
                $list.removeClass('empty-list');
                if ($list.attr('id') == 'included-stops') {
                    $('#generate-distribution-list, .save-area').removeClass('disabled');
                }
            } else {
                $list.find('> span').removeClass('display-none');
                $list.addClass('empty-list');
                if ($list.attr('id') == 'included-stops') {
                    $('#generate-distribution-list, .save-area').addClass('disabled');
                }
            }
            isChange = true;
        }
    );
    $('.list-group.sortable').disableSelection();

    var _getStopPointAndRouteIds = function()
    {
        var stopPointsIds = [];
        $('ul#included-stops > li.list-group-item').each(function(){
            stopPointsIds.push($(this).data('route-id') + '-' + $(this).data('stop-point-id'));
        });

        return stopPointsIds;
    };

    var _getStopPoint = function()
    {
        var stopPoints = [];
        $('ul#included-stops > li.list-group-item').each(function(){
            stopPoints.push(JSON.stringify({'stopPointId': $(this).data('stop-point-id'), 'routeId': $(this).data('route-id'), 'lineId': $(this).data('line-id')}));
        });

        return stopPoints;
    };

    var _getRoute = function()
    {
        return $('select.route>option:selected').val();
    }

    var _sendStopPointsList = function(element) {

        if (underProgress == true) {
            return false;
        }
        underProgress = true;
        element.parent().find('span.glyphicon-refresh').toggleClass('icon-refresh-animate display-none');
        element.parent().find('span.glyphicon-arrow-right').toggle();
        element.find('span.glyphicon-floppy-disk').toggle();

        var stopPoints = _getStopPoint();
        var route = _getRoute();
        if (stopPoints.length > 0) {
            $.post(
                element.data('href') || element.attr('href'),
                {"stopPoints[]" : stopPoints, 'route' : route}
            ).done(
                function() {
                    underProgress = false;
                    window.location = element.data('href') || element.attr('href');
                }
            );
        }

        return true;
    };

    $('.save-area').on('click', function() {
        _sendStopPointsList($(this))
    });

    $('.route').on('change', function(event) {
        element = $(this).find(':selected');

        if (true == isChange && confirm('Souhaitez-vous enregistrer les changements ?')) {
            _sendStopPointsList(element);
        } else {
            element.parent().find('span.glyphicon-refresh').toggleClass('icon-refresh-animate display-none');
            element.parent().find('span.glyphicon-arrow-right').toggle();
            var param = {
                'externalNetworkId': element.data('network_id'),
                'externalLineId': element.data('line_id'),
                'externalRouteId': element.data('route_id'),
                'seasonId': element.data('season_id'),
                'lineTimecardId': element.data('linetimecard_id')
            }
            window.location = Routing.generate('canal_tp_mtt_timecard_edit', param);
        }
    });

    var _init = function()
    {
        stopPointsIds = _getStopPointAndRouteIds();
        dom = $('ul#excluded-stops > li.list-group-item');
        dom.each( function(index) {
            tmp =  $(this).data('route-id') + '-' + $(this).data('stop-point-id');
            if ($.inArray(tmp, stopPointsIds) != -1) {
                $(this).addClass('active');
            }
        })
    }


    _init();

    return (stop);
});
