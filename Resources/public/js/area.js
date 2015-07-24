define('mtt/area', ['mtt_left_menu', 'jquery', 'fosjsrouting', 'jquery_ui_sortable'], function(menu, jquery, routing, sortable) {
    var area = {};

    menu.init($('#lines-accordion'));

    var _refreshAddAllStopPointsButton = function() {
        var $addAllBtn = $('#add-all-stop-points');
        var $excludedStopsActive = $('#excluded-stops').find('li.active').length;
        var $excludedStops = $('#excluded-stops').find('li.list-group-item').length;
        var $excludedStopsIsEmpty = $('#excluded-stops').hasClass('empty-list');

        if ($excludedStopsIsEmpty || $excludedStopsActive >= $excludedStops) {
            $addAllBtn.addClass('hide');
        } else {
            $addAllBtn.removeClass('hide');
        }
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

    $('li.area-route a').each(function(index, route) {
        $(route).click(function(event) {
            var li = $(this).parent();
            var a = $(this);

            $('.area-route.active').removeClass('active');
            li.addClass('active');

            var lineId = li.data('ext-line-id');
            var netId = li.data('ext-network-id');
            var routeId = li.data('ext-route-id');

            var url = Routing.generate('canal_tp_mtt_stop_point_list_json', {'externalNetworkId': netId, 'lineId': lineId, 'externalRouteId': routeId});

            var sens = $.trim(a.text());
            var lineCode = li.parents('.line-menu-wrapper').find('a.line-link-toggle > span.line-code');

            $.getJSON(url, function(json){
                $('ul#excluded-stops li.list-group-item').remove();
                var inArea = _getStopPointAndRouteIds();

                for (index in json.stops) {
                    var newStop = $('<li class="list-group-item" data-stop-point-id="' + index + '" data-route-id="' + routeId + '" data-line-id= "' + lineId + '"></li>');
                    newStop.append('<span class="glyphicon glyphicon-resize-vertical"></span>');
                    newStop.append('<span class="stop_name">' + json.stops[index].name + '</span>');
                    newStop.append('<a class="pull-right minus-btn toggle-stop-point-btn remove-stop-point-btn" href="#"></a>');
                    newStop.append('<a class="pull-right plus-btn toggle-stop-point-btn add-stop-point-btn" href="#"></a>' );
                    var lineCodeClone = lineCode.clone();
                    var lineRoute = $('<div></div>')
                    lineRoute.append(lineCodeClone.show());
                    lineRoute.append(' - '+ sens);
                    newStop.append(lineRoute);

                    if ($.inArray(routeId + '-' + index, inArea) != -1) {
                        newStop.addClass('active');
                    }


                    $('#excluded-stops').append(newStop);
                    $('#excluded-stops').removeClass('empty-list');
                    $('#excluded-stops').find('> span').addClass('display-none');
                }

                //Add to area list
                $('ul#excluded-stops .toggle-stop-point-btn').click(function(){
                    _addStopPointInArea($(this));


                    _refreshAddAllStopPointsButton();
                    return false;
                });

                _refreshAddAllStopPointsButton();
            });
        });
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
                    $('#generate-distribution-list, #save-area').removeClass('disabled');
                }
            } else {
                $list.find('> span').removeClass('display-none');
                $list.addClass('empty-list');
                if ($list.attr('id') == 'included-stops') {
                    $('#generate-distribution-list, #save-area').addClass('disabled');
                }
            }
        }
    );
    $('.list-group.sortable').disableSelection();


    var underProgress = false;
    var _sendStopPointsList = function(e) {
        if (underProgress == true) {
            return false;
        }
        underProgress = true;
        var $link = $(this);
        $link.find('span.glyphicon-refresh').toggle();
        $link.find('span.glyphicon-floppy-disk').toggle();

        $('#included-stops li').each(function() {
            $(this).toggleClass('active');
        });

        var stopPoints = _getStopPoint();
        if (stopPoints.length > 0) {
            $.ajax({
                url: $link.attr('href'),
                data: {"stopPoints[]" : stopPoints},
                error: function() {
                    underProgress = false;
                    $('div.alert-danger').show();

                    $link.find('span.glyphicon-floppy-disk').toggle();
                    $link.find('span.glyphicon-refresh').toggle();

                    $('#included-stops li').each(function() {
                        $(this).toggleClass('active');
                    });
                },
                type: 'POST',
                success: function(data, textStatus){
                    underProgress = false;
                    if (data.location) {
                        window.location = data.location;
                    }
                }
            });
        }
        e.preventDefault();

        return true;
    };

    $('#save-area').click(_sendStopPointsList);
    $('#manage-area').click(_sendStopPointsList);

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

    _refreshAddAllStopPointsButton();

    return (area);
});
