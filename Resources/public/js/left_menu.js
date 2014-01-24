define(['jquery', 'bootstrap'], function($) {
    return {
        init:function($wrapper){
            //first bind click listener
            $wrapper.find('a.line-link-toggle').click(function(){
                $(this).siblings('ul').slideToggle();
                return false;
            });
            var $current_item = $wrapper.find('.line-menu-wrapper li.active');
            // shall we open the menu ie do we have an active item?
            if ($current_item.length == 1)
            {
                $current_item.parents('.line-menu-wrapper').find('a').click();
                $current_item.parents('.panel').find('h4 a').click();
            }
        }
    }
});