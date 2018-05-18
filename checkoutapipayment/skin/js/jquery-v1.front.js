$(function(){
   if($('.cvv-what-is-this').length){

       $('.cvv-what-is-this').click(function(event) {
           event.preventDefault();
           $('.tool-tip-content').toggle();
       })
   }
});