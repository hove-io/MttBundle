define(['jquery'], function($) {
    $('img.layout-preview').dblclick(function() {
      $('form').submit();
    });
});
