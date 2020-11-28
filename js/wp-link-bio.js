(function( $ ) {
    $(function() {
        // Add Color Picker to all inputs that have 'color-field' class
        $( '.wplb-color-picker' ).wpColorPicker();

        // The "Upload" button
        $('.wplb-upload-image').click(function() {
            var send_attachment_bkp = wp.media.editor.send.attachment;
            var button = $(this);
            wp.media.editor.send.attachment = function(props, attachment) {
                $(button).parent().prev().attr('src', attachment.url);
                $(button).prev().val(attachment.url);
                wp.media.editor.send.attachment = send_attachment_bkp;
            }
            wp.media.editor.open(button);
            return false;
        });

        // The "Remove" button (remove the value from input type='hidden')
        $('.wplb-remove-image').click(function() {
            var answer = confirm('Are you sure?');
            if (answer == true) {
                var src = $(this).parent().prev().attr('data-src');
                $(this).parent().prev().attr('src', src);
                $(this).prev().prev().val('');
            }
            return false;
        });

        $('.wplb-copy-link').click(function() {
            const link = $('.wplb-link');
            const selected = document.getSelection().rangeCount > 0 ? document.getSelection().getRangeAt(0) : false;

            link.select();
            document.execCommand('copy');
            
            if( selected ) {
                document.getSelection().removeAllRanges();
                document.getSelection().addRange(selected);
            }

            alert('The URL has been copied to your clipboard!');

            return false;
        });
    });
})( jQuery );