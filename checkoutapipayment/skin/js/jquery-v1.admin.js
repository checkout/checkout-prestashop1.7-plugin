(function($){
    $(function(){
        $_selectBox = $('[name^="cardType"]');
        if($_selectBox.length) {
            $_selectBox.click(function(){
                var $_this = $(this);
                var $parent =$_this.parent();
               if($_this.is(':checked')) {

                   $parent.children().addClass('selected');
                }else {
                   $parent.children().removeClass('selected');
               }
            });
        }
    });
})(jQuery)