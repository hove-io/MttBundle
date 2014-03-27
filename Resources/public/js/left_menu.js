define(['jquery', 'bootstrap'], function($) {
    return {
        init:function($wrapper){
            var _toggle_link = function($link) {
                $link.parent().toggleClass('active');
                $link.find('.glyphicon-chevron-right, .glyphicon-chevron-down').toggle();
            };
            $wrapper.find('.mode-wrapper > a').click(function(){
                $(this).find('.glyphicon-chevron-right, .glyphicon-chevron-down').toggle();
            });
            $wrapper.find('a.line-link-toggle').click(function(){
                // console.log('link: ' + $(this).attr('href'));
                _toggle_link($(this));
                $(this).parent().siblings('.active').each(function(){
                    _toggle_link($(this).find('a.line-link-toggle'));
                    $(this).find('ul').collapse('toggle');
                });
            });
            //toggle button
            $wrapper.parents('#left-menu').find('.toggle-button').click(function(){
                var new_pos = $(this).parents('#left-menu').css('left') == '-300px' ? '0' : '-300px';
                $(this).parents('#left-menu').animate({
                    'left': new_pos
                });
            });
            var $current_item = $wrapper.find('.line-menu-wrapper li.active');
            // shall we open the menu and do we have an active item?
            if ($wrapper.parents('#left-menu').hasClass('toggable-left-menu') == false && $current_item.length == 1) {
                $current_item.parents('.line-menu-wrapper').find('a').click();
                $current_item.parents('.mode-wrapper').find('> a').click();
            }
            
        }
        
    }
});