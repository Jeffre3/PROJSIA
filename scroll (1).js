$(document).ready(function(){
    $("button").click(function(event){
      var targetId = $(this).attr("onclick").split("'")[1]; // Extract the target id from the onclick attribute
      var target = $("#" + targetId); // Get the target element by id
      
      $('html, body').animate({
        scrollTop: target.offset().top
      }, 1000); // 1000ms for smooth scroll duration
      event.preventDefault();
    });
  });
  