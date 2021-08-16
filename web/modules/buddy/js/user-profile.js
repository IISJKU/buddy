function initCheckboxes(){
  //Todo later if icons are needed
}



jQuery(document).ready(function (){

  jQuery('main').on('DOMSubtreeModified', function(){
    initCheckboxes();
  });
});



(function($) {
  // Argument passed from InvokeCommand.
  $.fn.myAjaxCallback = function(argument) {
    console.log('myAjaxCallback is called.');
    // Set textfield's value to the passed arguments.
    $(".page-title").text(argument);

    $(window).scrollTop(0);
  };
})(jQuery);
