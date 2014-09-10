define(['jquery', 'bootstrap'], function($) {
    return {
        init:function($wrapper){
            var $menu = $wrapper.parents('#left-menu');

            var resizeMenu = function() {
                var maxHeight = $(window).height() - $menu.offset().top - 38;
                if ($menu.find('.submenu-wrapper').length != 0)
                    $menu.find('.submenu-wrapper').css('max-height',  maxHeight);
                else
                    $menu.css('max-height',  maxHeight);
            };
            $('.line-menu-wrapper .line-link-toggle').click(function(){
                $(this).parent().addClass('active');
            });
            $wrapper.find('.line-menu-wrapper > ul').on('shown.bs.collapse', function () {
                $(this).parent().siblings('.line-menu-wrapper.active').removeClass('active').find('ul').collapse('hide');
                $(this).parent().addClass('active');
            });
            //toggle button
            $menu.find('.toggle-button').click(function(){
                var new_pos = $(this).parents('#left-menu').css('left') == '-300px' ? '0' : '-300px';
                $(this).parents('#left-menu').animate({
                    'left': new_pos
                });
            });

            // if ($menu.hasClass('toggable-left-menu') == false) {
                resizeMenu();
                $(window).resize(resizeMenu);
            // }

            var $current_item = $wrapper.find('.line-menu-wrapper li.active');
            // shall we open the menu and do we have an active item?
            if ($menu.hasClass('toggable-left-menu') == false && $current_item.length == 1) {
                var $line_menu_wrapper = $current_item.parents('.line-menu-wrapper');

                $line_menu_wrapper.addClass('active').siblings().find('> ul').removeClass('in');
                $current_item.parents('.mode-wrapper').siblings().find('ul').removeClass('in');

                current_item_position = $line_menu_wrapper.position().top;
                if (($line_menu_wrapper.position().top + $line_menu_wrapper.outerHeight()) > $menu.height())
                    $menu.scrollTop($line_menu_wrapper.position().top);
            } else {
                $menu.find('ul').removeClass('in');
            }
        }
    }
});
