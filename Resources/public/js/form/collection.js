define(['jquery'], function($) {
    var regexp = /(.*?)\]\[(\d+)\]\[(.*?)/;
    
    var _init = function()
    {
        $('.modal-dialog').on(
            'click',
            '.form-with-collection .add-item-collection-btn',
            function(){
                var $wrapper = $(this).parents('form.form-with-collection').find('[data-count]');
                // var $tpl = $($wrapper.data('prototype'));
                var $tpl = $wrapper.find('div:first').clone();
                var actual_count = $wrapper.data('count');
                $tpl = _reset_and_inc_tpl($tpl, actual_count);
                $wrapper.append($tpl);
                $wrapper.append('<hr/>');
                actual_count++
                $wrapper.data('count', actual_count);
                if (actual_count == 4)
                    $(this).hide();
            }
        );
        $('.modal-dialog').on(
            'click',
            '.form-with-collection .delete-item-collection-btn',
            function(){
                $(this).parents('.row').remove();
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
    
    _init();
});