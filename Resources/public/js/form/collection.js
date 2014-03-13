define(['jquery'], function($) {
    var regexp = /(.*?)\]\[(\d+)\]\[(.*?)/;
    
    var collection = {};
    
    collection.init = function($wrapper)
    {
        collection.tpl = $wrapper.find('div:first').clone(true, true);
        $wrapper.parents('.form-with-collection').find('.add-item-collection-btn').click(
            function(){
                var actual_count = $wrapper.data('count');
                var $currentTpl = collection.tpl.clone();
                _reset_and_inc_tpl($currentTpl, actual_count);
                $wrapper.append($currentTpl);
                $currentTpl.find('textarea').keyup();
                $wrapper.append('<hr/>');
                actual_count++;
                $wrapper.data('count', actual_count);
                if (actual_count == 4)
                    $(this).hide();
            }
        );
        $wrapper.parents('.form-with-collection').on(
            'click',
            '.delete-item-collection-btn',
            function(){
                var elem_wrapper = $(this).parents('.row');
                var actual_count = $wrapper.data('count');
                actual_count--;
                $wrapper.data('count', actual_count);
                // show add button if remaining less than 4 elements
                if (actual_count < 4) {
                    elem_wrapper.parents('.form-with-collection').find('.add-item-collection-btn').show();
                }
                elem_wrapper.next('hr').remove();
                elem_wrapper.remove();
            }
        );
    };
    
    var _reset_and_inc_tpl = function($tpl, count)
    {
        $tpl.find('select, input, textarea').each(function(){
            $(this).val('');
            var old_name = $(this).attr('name');
            var new_name = old_name.replace(regexp, '$1][' + count + '][$3');
            $(this).attr('name', new_name);
        });
        $tpl.find('span.count-label').text(count + 1);
        return $tpl;
    };
    
    return collection;
});