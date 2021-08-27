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
  $.fn.user_profile_ajax_callback = function(argument) {


       // Set textfield's value to the passed arguments.
       $(".page-title").text(argument);

       /*
       let element       = $(".site-title");
       let elementHeight = element.height();
       let windowHeight  = $(window).height();

       let offset = Math.min(elementHeight, windowHeight) + element.offset().top;
       $('html, body').animate({ scrollTop: offset }, 500);
       */
  };

  $.fn.user_profile_update_progress_ajax_callback = function(argument) {

    let progress = Math.floor( argument*100 );+
      $('#profile-progress-bar').width(progress+'%');

    /*$('#profile-progress-bar').animate({
      width: progress+'%'
    })
    console.log(argument);

     */
  };
})(jQuery);
