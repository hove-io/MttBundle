define(['jquery', 'bootstrap'], function($) {
    return {
        init:function($wrapper, current_route){
            //first bind click listener
            $wrapper.find('a.line-link-toggle').click(function(){
                $(this).siblings('ul').slideToggle();
                return false;
            });
            // shall we open the menu?
            if (current_route)
            {
                var $current_item = $wrapper.find('.line-menu-wrapper li.active');
                $current_item.parents('.line-menu-wrapper').find('a').click();
                $current_item.parents('.panel').find('h4 a').click();
            }
        }
    }
});