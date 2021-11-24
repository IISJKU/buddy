(function ($) {

  let radios = document.querySelectorAll('#star_rating input[type=radio]');
  let output = document.querySelector('#star_rating output');

  let submit_rating = function(stars) {
    // TODO: submit data
    output.textContent = stars;
  };

  // Iterate through all radio buttons and add a click
  // event listener to the labels
  Array.prototype.forEach.call(radios, function(el, i){
    let label = el.nextSibling.nextSibling;
    label.addEventListener("click", function(event){
      submit_rating(label.querySelector('span').textContent);
    });
  });

  // Form submit
  document.querySelector('#star_rating').addEventListener('submit', function(event){
    submit_rating(document.querySelector('#star_rating :checked ~ label span').textContent);
    event.preventDefault();
    event.stopImmediatePropagation();
  });

})(jQuery);
