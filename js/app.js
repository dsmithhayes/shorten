(function ($) {
    $('#copy-form').submit(function() {
        $('#url').select();
        document.execCommand('copy');

        return false;
    });
})(window.jQuery);