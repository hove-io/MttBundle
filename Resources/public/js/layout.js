define(['jquery'], function($) {
    var layout = {};
    
    var $icon_tpl = $('<span class="glyphicon"></span>');
    
    layout.init = function($wrapper)
    {
        $('.block').each(function(){
           $elem = $(this);
           var data = $elem.data();
           var icon_class = 'glyphicon-';
            if (data.blockLevel == "line" && data.blockType == "text")
            {
                icon_class += 'edit';
                // listener
                $elem.click(function(){
                    var url = Routing.generate('canal_tp_meth_block_get_form', { 'block_type': data.blockType, 'dom_id' : $elem.attr('id')});
                    $('#base-modal').modal({
                        keyboard:true,
                        remote: url
                    });
                });
            }
            else
            {
                icon_class += 'ban-circle';
                $elem.addClass('disabled-block');
            }
            $elem.append($icon_tpl.clone().addClass(icon_class));
        });
    };
    
    return layout;
});