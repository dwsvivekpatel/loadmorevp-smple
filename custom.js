jQuery(document).ready(function ($) {
    var page = 1;
    var currentFilter = '';

    // Function to load posts
    function loadPosts() {
        var totalPost = $('#services_count').val();
        var serviceLoad = currentFilter;

        $.ajax({
            type: 'POST',
            url: load_more.ajaxurl,
            data: {
                action: 'load_more',
                service_load: serviceLoad,
                page: page,
            },
            beforeSend: function () {
                $("#loading").show();
            },
            complete: function () {
                setTimeout(function () {
                    $("#loading").hide();
                }, 500);
            },
            success: function (response) {
                $('#post-load').append(response.data.content);

                // Hide load more button if no more posts
                if ($('#post-load').children().length >= response.data.services_count) {
                    $('#load-more').hide();
                } else {
                    $('#load-more').show(); // Show button if more posts are available
                }
            }
        });
    }

    // Click event handler for load more button
    $('#load-more').on('click', function () {
        page++;
        loadPosts();
    });

    // Click event handler for filter buttons
    $(".service-item").on('click', function (event) {
        event.preventDefault();

        var serviceLoad = $(this).data('load');
        $(".service-item").removeClass('active');
        $(this).addClass('active');

        currentFilter = serviceLoad;
        page = 1;

        // Clear existing posts before loading new ones
        $('#post-load').empty();

        // Hide the load more button initially
        $('#load-more').hide();
        // $('#loading').hide();
        // Load posts immediately after clicking a filter
        loadPosts();
    });
});
