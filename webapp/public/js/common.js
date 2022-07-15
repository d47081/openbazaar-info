function urlify(string) {
    return string.replace(/(https?:\/\/[^\s]+)/g, function(url) {
        return '<a href="' + url + '">' + url + '</a>';
    })
}

$(document).ready(function() {
  $('.nav-tabs a').click(function(e){
    e.preventDefault();
    $(this).tab('show');
  });
});
