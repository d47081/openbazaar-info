var API = {
  showGetResponse: function(iframe, element) {

    var input    = $(element).find('input');
    var button   = $(element).find('.btn');
    var response = $(element).find('.response');
    var loading  = $(element).find('.loading');

    $.ajax({
      url: input.val(),
      type: 'GET',
      beforeSend: function() {

        $(button).addClass('disabled');

        $(loading).remove();
        $(response).remove();

        $(element).append(
          $('<div/>', {
            'class': 'loading p-2 text-center'
          }).append(
            $('<div/>', {
              'class': 'spinner-grow text-primary'
            }).append(
              $('<span/>', {
                'class': 'sr-only'
              }).text('Loading...')
            )
          )
        );
      },
      success: function(json) {
        $(button).removeClass('disabled');
        $(element).find('.loading').remove();

        if (true === iframe) {
          $(element).append(
            $('<iframe/>', {
              'class': 'response border-0 p-0 m-0',
              'src': input.val(),
            })
          );
        } else {
          $(element).append(
            $('<pre/>', {
              'class': 'response p-2 border rounded',
            }).append(hljs.highlight('json', JSON.stringify(json, null, '  ')).value)
          );
        }
      },
      error: function (e) {
        if (e.status) {
          $(button).removeClass('disabled');
          $(element).find('.loading').remove();

          $(element).append(
            $('<pre/>', {
              'class': 'response p-2 border rounded',
            }).append(e.status)
          );
        }
      }
    });
  },
};

$(document).ready(function () {
  $('.api').find('div.btn').each(function() {
    $(this).click();
  });
});
