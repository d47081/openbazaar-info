var Main = {
  chart: {
    load: function(target, timeline, peers, profiles, listings) {
      new Chart($(target), {
          type: 'line',
          data: {
              labels: timeline,
              datasets: [
                {
                    label: 'Peers Online',
                    data: peers,
                    borderWidth: 1,
                    borderColor: 'rgba(40, 167, 69, 0.5)',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)'
                },
                {
                    label: 'Profiles Discovered',
                    data: profiles,
                    borderWidth: 1,
                    borderColor: 'rgba(0, 123, 255, 0.5)',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)'
                },
                {
                    label: 'Listings Added',
                    data: listings,
                    borderWidth: 1,
                    borderColor: 'rgba(255, 193, 7, 0.5)',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)'
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
};

$(document).ready(function () {
  Main.chart.load('#mainChart', $('#mainChart').data('timeline').split('|'), $('#mainChart').data('peers').split('|'), $('#mainChart').data('profiles').split('|'), $('#mainChart').data('listings').split('|'));
});
