$(document).ready(function () {
    $(".team_" + global_dict["user_id"]).addClass("label label-info");
});

$('.row .collapse-btn').on('click', function(e) {
    e.preventDefault();
    var $this = $(this);
    var $collapse = $this.closest('.collapse-group').find('.collapse');
    $collapse.collapse('toggle');
});
