define(['jquery'], function($) {
    var layout = {};
    
    var $icon_tpl = $('<span class="glyphicon"></span>');
    
    layout.init = function($wrapper, blockTypes, line_id)
    {
        // console.dir(blockTypes);
        $('#base-modal').on('hidden.bs.modal', function () {
            console.log('destroy')
            $(this).removeData('bs.modal');
        });
        $('.block').each(function(){
           $elem = $(this);
           var data = $elem.data();
           var icon_class = 'glyphicon-';
            if (data.blockLevel == "line" && data.blockType == "text")
            {
                icon_class += blockTypes.text.icon;
                // listener
                $elem.click(function(){
                    var url = Routing.generate(
                        'canal_tp_meth_block_edit', 
                        { 
                            'block_type': data.blockType, 
                            'line_id': line_id, 
                            'dom_id' : $(this).attr('id')
                        }
                    );
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