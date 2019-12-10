$(function(){
  $('.icon i').on('click', function() {
    var $this = $(this);

    $.ajax({
      type: 'POST',
      url: '_answer.php',
      data: {
        token: $('#token').val()
      },
    }).done(function(res) {
      $('.count').text(res.likes_count);
      $this.toggleClass('like');
      $('.comment').fadeToggle(500);
    }).fail(function() {
      console.log('Ajax Error');
    });
  });
});
