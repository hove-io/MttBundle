define('mtt/stop', ['jquery', 'fosjsrouting', 'jquery_ui_sortable'], function(jquery, routing, sortable) {

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
        }
    );
    $('.list-group.sortable').disableSelection();
});
