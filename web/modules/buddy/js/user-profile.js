function initCheckboxes(){

  console.log("gogo");
}



jQuery(document).ready(function (){

  jQuery('main').on('DOMSubtreeModified', function(){
    initCheckboxes();
  });
});



