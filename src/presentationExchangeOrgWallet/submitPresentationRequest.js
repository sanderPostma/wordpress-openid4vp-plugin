(function($){
    $(document).ready(function() {
        $("#org-wallet-submit").on("click", function(e) {
            e.preventDefault();
            var walletUrl = $("#org-wallet-url").val();

            var data = {
                action: "presentation_exchange_ajax",
                walletUrl: walletUrl
            };

            $.post(my_ajax_obj.ajax_url, data, function(response) {
                console.log(response);
                console.log(response.presentationRequestUri);
                if (response && response.presentationRequestUri) {
                    window.location = response.presentationRequestUri;
                }
            }, "json");
        });
    });
})(jQuery);
