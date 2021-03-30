'use strict';

document.addEventListener('DOMContentLoaded', function() {

    setInterval( 
        function() {
            let linkAnimation = Array.from( document.querySelectorAll('.link-animation') );
            linkAnimation.map( ( link ) => {
                let animationName = link.getAttribute('data-animation-name');
                link.classList.toggle(animationName);
            } );
        },
        2000
    );

});