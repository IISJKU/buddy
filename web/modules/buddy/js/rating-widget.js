(function ($) {

  let star_selector = $('#stars li');

  star_selector.on('mouseover', function(){
    let onStar = parseInt($(this).data('value'), 10);

    $(this).parent().children('li.star').each(function(e){
      if (e < onStar) {
        $(this).addClass('hover');
      }
      else {
        $(this).removeClass('hover');
      }
    });

  }).on('mouseout', function(){
    $(this).parent().children('li.star').each(function(e){
      $(this).removeClass('hover');
    });
  });

  star_selector.on('click', function(){
    let onStar = parseInt($(this).data('value'), 10);
    let stars = $(this).parent().children('li.star');

    for (i = 0; i < stars.length; i++) {
      $(stars[i]).removeClass('selected');
    }

    for (i = 0; i < onStar; i++) {
      $(stars[i]).addClass('selected');
    }

    let ratingValue = parseInt($('#stars li.selected').last().data('value'), 10);
    let msg = "";

    // TODO handle value

    responseMessage(msg);

  });

})(jQuery);


function responseMessage(msg) {
  jQuery('.success-box').fadeIn(200);
  jQuery('.success-box div.text-message').html("<span>" + msg + "</span>");
}
