var Search = {
  listing: {
    page: 0,
    total: 0,
    lock: false,
    load: function(query, data, load, loading, refresh) {

      if (refresh) {
        $(data).html('');
        $(loading).removeClass('d-none');
        $(load).addClass('d-none');
        $(load).addClass('disabled');
        Search.listing.page = 0;
      }

      if (Search.listing.lock == false) {
        $.ajax({
          url:  'api/search/listing',
          type: 'POST',
          data: {
            query: query,
            page: Search.listing.page,
          },
          beforeSend: function() {
            Search.listing.lock = true;
            $(load).addClass('disabled');
          },
          success: function(response) {
            if (response.success) {
              $(loading).addClass('d-none');
              Search.listing.page++;
              if (response.listings.length) {
                $(response.listings).each(function() {
                  Search.listing.total++;
                  $(data).append(
                    $('<div/>', {
                      'class': 'result mt-2 mb-2 pt-2 pb-2 pl-3 pr-3',
                    }).append(
                      $('<div/>', {
                        'class': 'row',
                      }).append($('<div/>', {
                        'class': 'col-2 pr-0',
                      }).append(
                        $('<a/>', {
                          'href': 'listing/' + this.hash,
                        }).append(
                          $('<img/>', {
                            'class': 'w-100 mt-1 rounded',
                            'alt': this.image,
                            'src': 'api/image?hash=' + this.image
                          })
                        )
                      )).append(
                        $('<div/>', {
                          'class': 'col-8 position-relative',
                        }).append(
                          $('<a/>', {
                            'href': this.href.listing,
                          }).append(
                            $('<h6/>').append(this.title)
                          )
                        ).append(
                          $('<div/>', {
                            'class': 'description',
                          }).append(this.description)
                        ).append(
                          $('<div/>').append(
                            $('<i/>', {
                              'class': 'material-icons online mr-1 ' + (this.profile.online.status == 'online' ? 'text-success' : this.profile.online.status == 'active' ? 'text-warning' : 'text-danger'),
                              'title': this.profile.online.text
                            }).append('lens')
                          ).append(
                            $('<small/>', {
                              'class': 'mr-1 d-lg-none d-xl-none ' + (this.profile.online.status == 'online' ? 'text-success' : this.profile.online.status == 'active' ? 'text-warning' : 'text-danger')
                            }).append(this.profile.online.text)
                          ).append(
                            $('<' + (this.profile.name ? 'small' : 'code') + '/>', {
                              'class': 'text-secondary'
                            }).append(
                              $('<a/>', {
                                'href': this.profile.href.profile
                              }).append(this.profile.name ? this.profile.name : this.profile.peerId)
                            )
                          )
                        )
                      ).append(
                        $('<div/>', {
                          'class': 'col-2 text-center pl-0 pr-0',
                        }).append(
                          $('<div/>', {
                            'class': 'text-success',
                          }).append(this.price)
                        ).append(
                          $('<div/>', {
                            'class': 'text-uppercase',
                          }).append(
                            $('<small/>').append(this.condition)
                          )
                        ).append(
                          $('<div/>', {
                            'class': 'text-muted',
                          }).append(
                            $('<small/>').append(this.contractType)
                          )
                        ).append(
                          $('<a/>', {
                            'class': 'badge mt-2 pt-1 pb-1 pl-2 pr-2 ' + (this.payment.security == 'danger' ? 'badge-danger' : (this.payment.security == 'moderated' ? 'badge-primary' : 'badge-success')),
                            'href': this.href.ob,
                            'title': this.payment.text
                          }).append(
                            this.payment.security == 'danger' ? $('<i/>',{'class': 'material-icons mr-1'}).append('warning') : (this.payment.security == 'moderated' ? $('<i/>',{'class': 'material-icons mr-1'}).append('security'):'')
                          ).append(this.payment.button)
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
              if (Search.listing.total < response.total) {
                $(load).removeClass('d-none');
                $(load).removeClass('disabled');
              } else {
                $(load).addClass('d-none');
                $(load).addClass('disabled');
              }
              Search.listing.lock = false;
            }
          }
        });
      }
    }
  },
  profile: {
    page: 0,
    total: 0,
    lock: false,
    load: function(query, data, load, loading, refresh) {

      if (refresh) {
        $(data).html('');
        $(loading).removeClass('d-none');
        $(load).addClass('d-none');
        $(load).addClass('disabled');
        Search.profile.page = 0;
      }

      if (Search.profile.lock == false) {
        $.ajax({
          url:  'api/search/profile',
          type: 'POST',
          data: {
            query: query,
            page: Search.profile.page,
          },
          beforeSend: function() {
            Search.profile.lock = true;
            $(load).addClass('disabled');
          },
          success: function(response) {
            if (response.success) {
              $(loading).addClass('d-none');
              Search.profile.page++;
              if (response.profiles.length) {
                $(response.profiles).each(function() {
                  Search.profile.total++;
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
                          'class': 'col-10',
                        }).append(
                          $('<a/>', {
                            'href': this.href.profile,
                          }).append(
                            $('<h6/>').append(this.name)
                          )
                        ).append(
                          $('<div/>', {
                            'class': 'description',
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
              if (Search.profile.total < response.total) {
                $(load).removeClass('d-none');
                $(load).removeClass('disabled');
              } else {
                $(load).addClass('d-none');
                $(load).addClass('disabled');
              }
              Search.profile.lock = false;
            }
          }
        });
      }
    }
  },
};

$(document).ready(function () {
  if ('profile' == $('select[name=t]').val()) {
    Search.profile.load($('input[name=q]').val(), '#searchProfileData', '#searchProfileLoad', '#searchProfileLoading', true);
  } else {
    Search.listing.load($('input[name=q]').val(), '#searchListingData', '#searchListingLoad', '#searchListingLoading', true);
  }
});
