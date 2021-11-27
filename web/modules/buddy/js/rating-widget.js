(function($) {
  $(document).ready(function (){

      let submit_rating = function(user, item, rating, path='') {
      let origin = window.location.href.replace(path,'');
      let process_url = origin + '/update-rating/' + user + '/' + item + '/' +rating;
      $.get(process_url);
    };

    let update_text = function (id_selector, text) {
      $(id_selector).html(text);
    }

    // Iterate through all radio buttons and add a click
    // event listener to the labels
    let star_radios = $('.star_rating input[type=radio]');
    star_radios.each(function( index ) {
      let rating_and_ids = $(this).val().split('_');
      let rating_num = rating_and_ids[0];
      let rating_uid = rating_and_ids[1];
      let rating_item_id = rating_and_ids[2];
      let star_label = $("label[for='" + $(this).attr('id') + "']");
      star_label.click(function() {
        let rating_text = $(this).children('span').first().text();
        let output_selector = "#msg_" + rating_uid + "_" + rating_item_id;
        submit_rating(rating_uid, rating_item_id, rating_num, '/user-at-library');
        update_text(output_selector, rating_text);
      });
    });

    // Form submit
    document.querySelector('.star_rating').addEventListener('submit', function(event){
      update_text(document.querySelector('.star_rating :checked ~ label span').textContent);
      submit_rating();
      event.preventDefault();
      event.stopImmediatePropagation();
    });

  });
})(jQuery);
