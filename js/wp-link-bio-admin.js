(function( $ ) {
    $(function() {

        // Add Color Picker to all inputs that have 'color-field' class
        $( '.wplb-color-picker' ).wpColorPicker({
            palettes: false,
            change: function(event, ui) {
                updatePhoneEmulator(event);
            }
        });

        // The "Upload" button
        $('.wplb-upload-image').click(function() {
            var send_attachment_bkp = wp.media.editor.send.attachment;
            var button = $(this);
            wp.media.editor.send.attachment = function(props, attachment) {
                $(button).parent().prev().attr('src', attachment.url);
                $(button).prev().val(attachment.url);
                wp.media.editor.send.attachment = send_attachment_bkp;
                updateScreenHeaderLogo(attachment.url);
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
                updateScreenHeaderLogo('');
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

        const template = document.querySelector('#wplb_template');
        if (template && template.value == 'link') {
            const numberOfPosts = document.querySelector('.wplb-num-posts');
            numberOfPosts.style.display = 'none';
        }

        initPhoneEmulator();

        function initPhoneEmulator() {

            const template = document.querySelector('#wplb_template');
            if (template) {
                template.addEventListener('change', changeTemplate);
            }

            const bgColor = getBgColor();
            const phoneScreen = document.querySelector('.phone-screen');
            phoneScreen.style.backgroundColor = bgColor;

            const linkColor = getLinkColor();
            const links = document.querySelectorAll('.links');
            links.forEach((link) => {
                link.style.backgroundColor = linkColor;
                link.style.borderColor = linkColor;
                link.style.color = bgColor;
                link.addEventListener('mouseover', linkFocus);
                link.addEventListener('mouseout', linkBlur);
            });

            const poweredByCheckbox = document.querySelector('#wplb_show_credits_cb');
            if (poweredByCheckbox) {
                poweredByCheckbox.addEventListener('click', togglePoweredBy);
            }

            const socialIcons = Array.from( document.querySelectorAll('.social-media__links__item') );
            socialIcons.map( icon => {
                icon.style.backgroundColor = linkColor;
            } );

            const displayLocation = document.querySelector('#wplb_sn_display_location');
            if (displayLocation) {
                displayLocation.addEventListener('change', changeDisplayLocation);
                changeDisplayLocation(displayLocation);
            }

        }

        function updateScreenHeaderLogo(src) {
            const screenHeader = document.querySelector('.screen-header');
            if (src != '') {
                screenHeader.innerHTML = `<img src="${src}" height="30" class="logo">`;
            } else {
                screenHeader.innerHTML = '<div class="logo-placeholder">Logo</div>';
            }
        }

        function changeTemplate(event) {

            const template = event.currentTarget.value;
            const phoneScreenContent = document.querySelector('.screen-content');
            const numberOfPosts = document.querySelector('.wplb-num-posts');
            const pluginUrl = wplb.plugin_url;
            let htmlTemplate = '';
            
            if (template == 'link') {
                htmlTemplate = `<ul>
                    <li class="links">Link 1</li>
                    <li class="links">Link 2</li>
                    <li class="links">Link 3</li>
                    <li class="links">Link 4</li>
                    <li class="links">Link 5</li>
                </ul>`;
                numberOfPosts.style.display = 'none';
            } else if (template == 'post') {
                htmlTemplate = `<div class="posts">
                    <div class="post-item">
                        <img src="${pluginUrl}/images/thumb-01.jpg" />
                    </div>
                    <div class="post-item">
                        <img src="${pluginUrl}/images/thumb-02.jpg" />
                    </div>
                    <div class="post-item">
                        <img src="${pluginUrl}/images/thumb-03.jpg" />
                    </div>
                    <div class="post-item">
                        <img src="${pluginUrl}/images/thumb-04.jpg" />
                    </div>
                </div>`;
                numberOfPosts.style.display = 'table-row';
            } else {
                htmlTemplate = `<div class="posts">
                    <div class="post-item">
                        <img src="${pluginUrl}/images/product-01.jpg" />
                    </div>
                    <div class="post-item">
                        <img src="${pluginUrl}/images/product-02.jpg" />
                    </div>
                    <div class="post-item">
                        <img src="${pluginUrl}/images/product-03.jpg" />
                    </div>
                    <div class="post-item">
                        <img src="${pluginUrl}/images/product-04.jpg" />
                    </div>
                </div>`;
                numberOfPosts.style.display = 'table-row';
            }

            phoneScreenContent.innerHTML = htmlTemplate;

            updateLinkColor();
            
        }

        function togglePoweredBy(event) {
            const isChecked = event.currentTarget.checked;
            const screenFooter = document.querySelector('.screen-footer');
            if ( screenFooter != 'undefined' ) {
                screenFooter.style.display = ( isChecked ) ? 'block' : 'none';
            }
        }

        function changeDisplayLocation(displayLocation) {

            const location = displayLocation.currentTarget != undefined ? displayLocation.currentTarget : displayLocation;
            const beforeContent = document.querySelector('.display-location-before-content');
            const afterContent = document.querySelector('.display-location-after-content');
            
            if ( location.value == 'before' ) {
                beforeContent.style.display = 'block';
                afterContent.style.display = 'none';
            } else {
                beforeContent.style.display = 'none';
                afterContent.style.display = 'block';
            }
        }

        function updatePhoneEmulator(event) {

            const elementId = event.target.id;
            let bgColor = getBgColor();
            //let linkColor = getLinkColor();

            if(elementId == 'wplb_body_bg_color') {
                const phoneScreen = document.querySelector('.phone-screen');
                bgColor = event.target.value;
                phoneScreen.style.backgroundColor = bgColor;
            }
            
            updateLinkColor();

            const socialIcons = Array.from( document.querySelectorAll('.social-media__links__item') );
            socialIcons.map( icon => {
                icon.style.backgroundColor = getLinkColor();
                icon.style.borderColor = getLinkColor();
                icon.style.color = getBgColor();
            } );

        }

        function updateLinkColor() {
            const links = document.querySelectorAll('.links');
            links.forEach((link) => {
                link.style.backgroundColor = getLinkColor();
                link.style.borderColor = getLinkColor();
                link.style.color = getBgColor();
                link.addEventListener('mouseover', linkFocus);
                link.addEventListener('mouseout', linkBlur);
            });
        }

        function linkFocus(event) {
            const link = event.currentTarget;
            let linkColor = getLinkColor();
            link.style.backgroundColor = 'transparent';
            link.style.color = linkColor;
        }

        function linkBlur(event) {
            const link = event.currentTarget;
            let bgColor = getBgColor();
            let linkColor = getLinkColor();
            link.style.backgroundColor = linkColor;
            link.style.color = bgColor;
        }

        function getBgColor() {
            return document.querySelector('#wplb_body_bg_color').value;
        }

        function getLinkColor() {
            return document.querySelector('#wplb_body_link_color').value;
        }

    });

})( jQuery );