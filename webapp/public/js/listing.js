var Listing = {
  shippings: {
    page: 0,
    total: 0,
    lock: false,
    load: function(hash, data, load, loading, refresh) {

      if (refresh) {
        $(data).html('');
        $(loading).removeClass('d-none');
        $(load).addClass('d-none');
        $(load).addClass('disabled');
        Listing.shippings.page = 0;
      }

      if ((refresh || Listing.shippings.total == 0) && Listing.shippings.lock == false) {
        $.ajax({
          url:  'api/listing/shippings',
          type: 'POST',
          data: {
            hash: hash,
            page: Listing.shippings.page,
          },
          beforeSend: function() {
            Listing.shippings.lock = true;
            $(load).addClass('disabled');
          },
          success: function(response) {
            if (response.success) {
              $(loading).addClass('d-none');
              Listing.shippings.page++;
              if (response.total > 0) {
                $(response.shippings).each(function() {
                  Listing.shippings.total++;
                  var shippingsTotal = 0;
                  var shippings = $('<table/>', {
                    'class': 'table table-bordered mt-3 mb-2'
                  });
                  $(this.services).each(function() {
                    shippingsTotal++;
                    $(shippings).append(
                      $('<tr/>').append(
                        $('<td/>').append(this.name)
                      ).append(
                        $('<td/>').append(this.estimatedDelivery)
                      ).append(
                        $('<td/>', {'class':'text-center'}).append(this.price)
                      ).append(
                        $('<td/>', {'class':'text-center'}).append(this.additionalItemPrice)
                      )
                    )
                  });
                  var countriesTotal = 0;
                  var countries = $('<div/>', {
                    'class': 'countries'
                  });
                  $(this.countries).each(function() {
                    countriesTotal++;
                    if (this.flag) {
                      countries.append(
                        $('<img/>', {
                          'alt': this.name,
                          'title': this.name,
                          'src': this.flag,
                          'class': 'flag mr-1'
                        })
                      );
                    } else {
                      countries.append(
                        $('<span/>', {
                          'class': 'mr-1'
                        }).append(this.name)
                      );
                    }
                  });
                  $(data).append(
                    $('<div/>', {
                      'class': 'result mt-2 mb-2 pt-2 pb-2 pl-3 pr-3'
                    }).append(
                      $('<strong/>').append(this.name)
                    ).append(
                      $('<div/>', {
                        'class': 'text-secondary'
                      }).append('<small>'+this.type+'</small>')
                    ).append(countriesTotal > 0 ? countries : '').append(shippingsTotal > 0 ? shippings : '')
                  );
                });
              } else {
                $(data).html(
                  $('<div/>', {
                    'class': 'mt-5 mb-5 text-center',
                  }).append(response.message)
                );
              }
              if (Listing.shippings.total < response.total) {
                $(load).removeClass('d-none');
                $(load).removeClass('disabled');
              } else {
                $(load).addClass('d-none');
                $(load).addClass('disabled');
              }
              Listing.shippings.lock = false;
            }
          }
        });
      }
    }
  },
  moderators: {
    page: 0,
    total: 0,
    lock: false,
    load: function(hash, data, load, loading, refresh) {

      if (refresh) {
        $(data).html('');
        $(loading).removeClass('d-none');
        $(load).addClass('d-none');
        $(load).addClass('disabled');
        Listing.moderators.page = 0;
      }

      if ((refresh || Listing.moderators.total == 0) && Listing.moderators.lock == false) {
        $.ajax({
          url:  'api/listing/moderators',
          type: 'POST',
          data: {
            hash: hash,
            page: Listing.moderators.page,
          },
          beforeSend: function() {
            Listing.moderators.lock = true;
            $(load).addClass('disabled');
          },
          success: function(response) {
            if (response.success) {
              $(loading).addClass('d-none');
              Listing.moderators.page++;
              if (response.moderators.length) {
                $(response.moderators).each(function() {
                  Listing.moderators.total++;
                  $(data).append(
                    $('<div/>', {
                      'class': 'result position-relative mt-2 mb-2 pt-2 pb-2 pl-3 pr-3',
                    }).append(
                      $('<div/>', {
                        'class': 'row',
                      }).append($('<div/>', {
                        'class': 'col-2 pr-0',
                      }).append(
                        $('<a/>', {
                          'href': 'profile/' + this.peerId,
                        }).append(
                          $('<img/>', {
                            'class': 'w-100 mt-1 rounded',
                            'alt': this.image,
                            'src': 'api/image?hash=' + this.image
                          })
                        )
                      )).append(
                        $('<div/>', {
                          'class': 'col-8',
                        }).append(
                          $('<a/>', {
                            'href': this.href.profile,
                          }).append(
                            $('<h6/>').append(this.name)
                          )
                        ).append(
                          $('<div/>', {
                            'class': 'description ' + (!this.available ? 'text-secondary' : '')
                          }).append(this.shortDescription)
                        ).append(
                          $('<div/>').append(
                            $('<i/>', {
                              'class': 'material-icons online mr-1 ' + (this.online.status == 'online' ? 'text-success' : this.online.status == 'active' ? 'text-warning' : 'text-danger'),
                              'title': this.online.text
                            }).append('lens')
                          ).append(
                            $('<small/>', {
                              'class': 'mr-1 d-lg-none d-xl-none ' + (this.online.status == 'online' ? 'text-success' : this.online.status == 'active' ? 'text-warning' : 'text-danger')
                            }).append(this.online.text)
                          ).append(
                            $('<code/>', {
                              'class': 'text-secondary'
                            }).append(this.peerId)
                          )
                        )
                      ).append(
                        $('<div/>', {
                          'class': 'col-2 pt-3 pb-3 text-center',
                        }).append(
                          $('<div/>', {
                            'class': 'text-primary',
                          }).append(this.price)
                        ).append(
                          $('<small/>', {
                            'class': 'text-secondary',
                          }).append(this.feeType)
                        )
                      )
                    )
                  );
                });
              } else {
                $(data).html(
                  $('<div/>', {
                    'class': 'mt-5 mb-5 text-center',
                  }).append(response.message)
                );
              }
              if (Listing.moderators.total < response.total) {
                $(load).removeClass('d-none');
                $(load).removeClass('disabled');
              } else {
                $(load).addClass('d-none');
                $(load).addClass('disabled');
              }
              Listing.moderators.lock = false;
            }
          }
        });
      }
    }
  },
};

$(document).ready(function () {
  Listing.shippings.load($('#listingShippingsContainer').data('hash'), '#listingShippingsData', '#listingShippingsLoad', '#listingShippingsLoading', false);
});
