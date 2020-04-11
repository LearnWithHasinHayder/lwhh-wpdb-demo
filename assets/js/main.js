; (function ($) {
    $(document).ready(function () {
        $(".action-button").on('click', function () {
            let task = $(this).data('task');
            let params = { "action": "display_result", "nonce": plugindata.nonce, "task": task };
            $.post(plugindata.ajax_url, params, function (data) {
                $("#plugin-demo-result").html("<pre>" + data + "</pre>").show();
            });
        });
    });
})(jQuery);