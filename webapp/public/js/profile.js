var Profile = {
  upTime: {
    load: function(target, timeline, uptime, accuracy) {
      new Chart($(target), {
          type: 'line',
          data: {
              labels: timeline,
              datasets: [
                {
                    label: 'Uptime, %',
                    data: uptime,
                    borderWidth: 1,
                    borderColor: 'rgba(40, 167, 69, 0.5)',
                    backgroundColor: 'rgba(40, 167, 69, 0.2)'
                },
                {
                    label: 'Accuracy, %',
                    data: accuracy,
                    borderWidth: 1,
                    borderColor: 'rgba(0, 123, 255, 0.5)',
                    backgroundColor: 'rgba(0, 0, 0, 0)'
                },
              ]
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              tooltips: {
                  mode: 'index'
              },
              scales: {
                  yAxes: [{
                      ticks: {
                          suggestedMin: 0,
                      }
                  }]
              }
          }
      })
    }
  },
  following: {
    page: 0,
    total: 0,
    lock: false,
    load: function(peerId, data, load, loading, refresh) {
      if ((refresh || Profile.following.total == 0) && Profile.following.lock == false) {
        $.ajax({
          url:  'api/profile/following',
          type: 'POST',
          data: {
            peerId: peerId,
            page: Profile.following.page,
          },
          beforeSend: function() {
            Profile.following.lock = true;
            $(load).addClass('disabled');
          },
          success: function(response) {
            if (response.success) {
              $(loading).addClass('d-none');
              Profile.following.page++;
              if (response.following.length) {
                $(response.following).each(function() {
                  Profile.following.total++;
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
              if (Profile.following.total < response.total) {
                $(load).removeClass('d-none');
                $(load).removeClass('disabled');
              } else {
                $(load).addClass('d-none');
                $(load).addClass('disabled');
              }
              Profile.following.lock = false;
            }
          }
        });
      }
    }
  },
  followers: {
    page: 0,
    total: 0,
    lock: false,
    load: function(peerId, data, load, loading, refresh) {
      if ((refresh || Profile.followers.total == 0) && Profile.followers.lock == false) {
        $.ajax({
          url:  'api/profile/followers',
          type: 'POST',
          data: {
            peerId: peerId,
            page: Profile.followers.page,
          },
          beforeSend: function() {
            Profile.followers.lock = true;
            $(load).addClass('disabled');
          },
          success: function(response) {
            if (response.success) {
              $(loading).addClass('d-none');
              Profile.followers.page++;
              if (response.followers.length) {
                $(response.followers).each(function() {
                  Profile.followers.total++;
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
              if (Profile.followers.total < response.total) {
                $(load).removeClass('d-none');
                $(load).removeClass('disabled');
              } else {
                $(load).addClass('d-none');
                $(load).addClass('disabled');
              }
              Profile.followers.lock = false;
            }
          }
        });
      }
    }
  },
  ratings: {
    page: 0,
    total: 0,
    lock: false,
    load: function(peerId, data, load, loading, refresh) {
      if ((refresh || Profile.ratings.total == 0) && Profile.ratings.lock == false) {
        $.ajax({
          url:  'api/profile/ratings',
          type: 'POST',
          data: {
            peerId: peerId,
            page: Profile.ratings.page,
          },
          beforeSend: function() {
            Profile.ratings.lock = true;
            $(load).addClass('disabled');
          },
          success: function(response) {
            if (response.success) {
              $(loading).addClass('d-none');
              Profile.ratings.page++;
              if (response.ratings.length) {
                $(response.ratings).each(function() {
                  Profile.ratings.total++;
                  $(data).append(
                    $('<div/>', {
                      'class': 'result mt-2 mb-2 pt-2 pb-2 pl-3 pr-3',
                    }).append(
                      $('<div/>', {
                        'class': 'row',
                      }).append(
                        $('<div/>', {
                          'class': 'col-lg-3 col-xs-12',
                        }).append(
                          $('<div/>', {
                            'class': 'row ml-0 mr-0 mb-sm-3 mb-lg-0',
                          }).append(
                            $('<div/>', {
                              'class': 'col-3 text-center rounded-left rating-left ' + (this.status == 'high' ? 'bg-success' : (this.status == 'medium' ? 'bg-warning' : 'bg-danger')),
                            }).append(this.average)
                          ).append(
                            $('<div/>', {
                              'class': 'col-9 bg-light border rounded-right ' + (this.status == 'high' ? 'bg-success' : (this.status == 'medium' ? 'bg-warning' : 'bg-danger')),
                            }).append(
                              $('<div/>').append($('<small/>', {'class': 'text-nowrap ' + (this.customerService.status == 'high' ? 'text-success' : (this.customerService.status == 'medium' ? 'text-warning' : 'text-danger'))}).append(this.customerService.text))
                            ).append(
                              $('<div/>').append($('<small/>', {'class': 'text-nowrap ' + (this.deliverySpeed.status == 'high' ? 'text-success' : (this.deliverySpeed.status == 'medium' ? 'text-warning' : 'text-danger'))}).append(this.deliverySpeed.text))
                            ).append(
                              $('<div/>').append($('<small/>', {'class': 'text-nowrap ' + (this.description.status == 'high' ? 'text-success' : (this.description.status == 'medium' ? 'text-warning' : 'text-danger'))}).append(this.description.text))
                            ).append(
                              $('<div/>').append($('<small/>', {'class': 'text-nowrap ' + (this.overall.status == 'high' ? 'text-success' : (this.overall.status == 'medium' ? 'text-warning' : 'text-danger'))}).append(this.overall.text))
                            ).append(
                              $('<div/>').append($('<small/>', {'class': 'text-nowrap ' + (this.quality.status == 'high' ? 'text-success' : (this.quality.status == 'medium' ? 'text-warning' : 'text-danger'))}).append(this.quality.text))
                            )
                          )
                        )
                      ).append(
                        $('<div/>', {
                          'class': 'col-lg-9 col-xs-12',
                        }).append(
                          $('<div/>', {
                            'class': 'description'
                          }).append(this.review)
                        ).append(
                          $('<small/>', {
                            'class': 'text-secondary'
                          }).append(this.time)
                        ).append(
                          $('<a/>', {
                            'href': this.href.profile
                          }).append(
                            $('<i/>', {
                              'class': 'material-icons online ml-1 mr-1 ' + (this.online.status == 'online' ? 'text-success' : this.online.status == 'active' ? 'text-warning' : 'text-danger'),
                              'title': this.online.text
                            }).append('lens')
                          ).append(
                            $('<small/>', {
                              'class': 'mr-1 d-lg-none d-xl-none ' + (this.online.status == 'online' ? 'text-success' : this.online.status == 'active' ? 'text-warning' : 'text-danger')
                            }).append(this.online.text)
                          ).append(this.name ? this.name : $('<code/>', {'class':'text-primary'}).append(this.peerId))
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
              if (Profile.ratings.total < response.total) {
                $(load).removeClass('d-none');
                $(load).removeClass('disabled');
              } else {
                $(load).addClass('d-none');
                $(load).addClass('disabled');
              }
              Profile.ratings.lock = false;
            }
          }
        });
      }
    }
  },
  contacts: {
    refresh: true,
    load: function(peerId, data, loading) {
      if (Profile.contacts.refresh) {
        $.ajax({
          url:  'api/profile/contacts',
          type: 'POST',
          data: {
            peerId: peerId,
          },
          success: function(response) {
            Profile.contacts.refresh = false;
            if (response.success) {
              $(loading).addClass('d-none');
              if (response.total > 0) {
                var contacts = $('<table/>', {
                  'class': 'table mt-3'
                });
                $(response.contacts).each(function() {
                  if (this.website) {
                    $(contacts).append(
                      $('<tr/>').append(
                        $('<td/>').append(this.website.text)
                      ).append(
                        $('<td/>').append(urlify(this.website.link))
                      )
                    );
                  }
                  if (this.email) {
                    $(contacts).append(
                      $('<tr/>').append(
                        $('<td/>').append(this.email.text)
                      ).append(
                        $('<td/>').append(
                          $('<a/>', {
                            'href': 'mailto:' + this.email.address
                          }).append(this.email.address)
                        )
                      )
                    );
                  }
                  if (this.telephone) {
                    $(contacts).append(
                      $('<tr/>').append(
                        $('<td/>').append(this.telephone.text)
                      ).append(
                        $('<td/>').append(this.telephone.number)
                      )
                    );
                  }
                  if (this.social) {
                    $(this.social).each(function() {
                      $(contacts).append(
                        $('<tr/>').append(
                          $('<td/>').append(this.type)
                        ).append(
                          $('<td/>').append(urlify(this.username))
                        )
                      );
                    });
                  }
                });
                $(data).append(contacts);
              } else {
                $(data).html(
                  $('<div/>', {
                    'class': 'mt-5 mb-5 text-center',
                  }).append(response.message)
                );
              }
            }
          }
        });
      }
    }
  },
  connections: {
    page: 0,
    total: 0,
    lock: false,
    load: function(peerId, data, load, loading, refresh) {
      if ((refresh || Profile.connections.total == 0) && Profile.connections.lock == false) {
        $.ajax({
          url:  'api/profile/connections',
          type: 'POST',
          data: {
            peerId: peerId,
            page: Profile.connections.page,
          },
          beforeSend: function() {
            Profile.connections.lock = true;
            $(load).addClass('disabled');
          },
          success: function(response) {
            if (response.success) {
              $(loading).addClass('d-none');
              Profile.connections.page++;
              if (response.connections.length) {
                $(response.connections).each(function() {
                  Profile.connections.total++;
                  var location  = $('<div/>',{'class':'location'});
                  if (this.location.flag) {
                    location.append(
                      $('<img/>', {
                        'class': 'flag mr-1',
                        'src': this.location.flag,
                      })
                    );
                  }
                  location.append(this.location.name);
                  $(data).append(
                    $('<tr/>').append(
                      $('<td/>', {'class':'pt-2 pb-2'}).append(this.ip)
                    ).append(
                      $('<td/>', {'class':'pt-2 pb-2'}).append(location)
                    ).append(
                      $('<td/>', {'class':'pt-2 pb-2'}).append(this.frequency)
                    ).append(
                      $('<td/>', {'class':'pt-2 pb-2'}).append(this.time)
                    )
                  );
                });
              } else {
                $(data).html(
                  $('<tr/>').append(
                    $('<td/>', {'class':'pt-5 pb-5 text-center', 'colspan': '4'}).append(response.message)
                  )
                );
              }
              if (Profile.connections.total < response.total) {
                $(load).removeClass('d-none');
                $(load).removeClass('disabled');
              } else {
                $(load).addClass('d-none');
                $(load).addClass('disabled');
              }
              Profile.connections.lock = false;
            }
          }
        });
      }
    }
  },
  listings: {
    page: 0,
    total: 0,
    lock: false,
    load: function(peerId, data, load, loading, refresh) {
      if ((refresh || Profile.listings.total == 0) && Profile.listings.lock == false) {
        $.ajax({
          url:  'api/profile/listings',
          type: 'POST',
          data: {
            peerId: peerId,
            page: Profile.listings.page,
          },
          beforeSend: function() {
            Profile.listings.lock = true;
            $(load).addClass('disabled');
          },
          success: function(response) {
            if (response.success) {
              $(loading).addClass('d-none');
              Profile.listings.page++;
              if (response.listings.length) {
                $(response.listings).each(function() {
                  Profile.listings.total++;
                  var image;
                  if (this.image) {
                    image = $('<div/>', {
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
                    );
                  }
                  $(data).append(
                    $('<div/>', {
                      'class': 'result mt-2 mb-2 pt-2 pb-2 pl-3 pr-3',
                    }).append(
                      $('<div/>', {
                        'class': 'row',
                      }).append(image).append(
                        $('<div/>', {
                          'class': 'col-8',
                        }).append(
                          $('<a/>', {
                            'href': 'listing/' + this.hash,
                          }).append(
                            $('<h6/>').append(this.title)
                          )
                        ).append(
                          $('<div/>', {
                            'class': 'description ' + (!this.available ? 'text-secondary' : '')
                          }).append(this.description)
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
              if (Profile.listings.total < response.total) {
                $(load).removeClass('d-none');
                $(load).removeClass('disabled');
              } else {
                $(load).addClass('d-none');
                $(load).addClass('disabled');
              }
              Profile.listings.lock = false;
            }
          }
        });
      }
    }
  },
};

$(document).ready(function () {
  Profile.upTime.load('#upTime', $('#upTime').data('timeline').split('|'), $('#upTime').data('uptime').split('|'), $('#upTime').data('accuracy').split('|'));
  Profile.listings.load($('#profileListingsContainer').data('peer-id'), '#profileListingsData', '#profileListingsLoad', '#profileListingsLoading', false)
});
