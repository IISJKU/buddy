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

    let element       = $(".site-title");
    let elementHeight = element.height();
    let windowHeight  = $(window).height();

    let offset = Math.min(elementHeight, windowHeight) + element.offset().top;
    $('html, body').animate({ scrollTop: offset });
  };
})(jQuery);
