define('mtt/stop', ['jquery', 'fosjsrouting', 'jquery_ui_sortable'], function($, sortable) {
    var stop = {};
    var isChange = false;

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

    //Add to area list
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
                    $('#generate-distribution-list, #save-area').removeClass('disabled');
                }
            } else {
                $list.find('> span').removeClass('display-none');
                $list.addClass('empty-list');
                if ($list.attr('id') == 'included-stops') {
                    $('#generate-distribution-list, #save-area').addClass('disabled');
                }
            }
            isChange = true;
        }
    );
    $('.list-group.sortable').disableSelection();



    var underProgress = false;

    var _sendStopPointsList = function(doRedirection) {
        if (underProgress == true) {
            return false;
        }
        underProgress = true;
        var $link = $(this);
        $link.find('span.glyphicon-refresh').toggleClass('icon-refresh-animate display-none');
        $link.find('span.glyphicon-floppy-disk').toggle();
        var stopPoints = _getStopPoint();
        if (stopPoints.length > 0) {
            $.post(
                $link.attr('href'),
                {"stopPoints[]" : stopPoints}
            ).done(
                function(data, textStatus) {
                    underProgress = false;
                    if ($link.hasAttr('data-toggle')) {
                        window.location = $link.attr('href');
                    }
                }
            );
        }

        return true;
    };

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

    $('#save-area').click(_sendStopPointsList);

    $('.rDirection').on('change', function(event) {
        if (true == isChange) {
            if (confirm('Attention les changements ne seront pas conservés')) {
                // TODO : Enregistrement des informations et rechargement de la page
            }
        }
        return false;
    });


    var _test = function()
    {
        stopPointsIds = _getStopPointAndRouteIds();
        dom = $('ul#excluded-stops > li.list-group-item');
        dom.each( function(index) {
            //console.log(dom);
            tmp =  $(this).data('route-id') + '-' + $(this).data('stop-point-id');
            if ($.inArray(tmp, stopPointsIds) != -1) {
                $(this).addClass('active');
            }

        })
    }


    _test();

    return (stop);
});
