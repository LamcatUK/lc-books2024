// Add your custom JS here.

document.addEventListener('DOMContentLoaded', function() {

    // HIDE NAV

    // const navbar = document.getElementById('navigation');

    // let lastScrollPosition = 0;
    // const navbarHeight = 0; // Get the height of the navbar
    // const smallerScrollThreshold = 200; // Threshold for adding the .smaller class
  
    // window.addEventListener('scroll', function() {
    //     const currentScroll = window.scrollY || document.documentElement.scrollTop;
  
    //     if (currentScroll > smallerScrollThreshold) {
    //         if (currentScroll > lastScrollPosition) {
    //             // Down scroll
    //             navbar.classList.add('hidden');
    //         } else {
    //             // Up scroll
    //             navbar.classList.remove('hidden');
    //             navbar.classList.add('scrolled');
    //         }
    //     }
  
    //     lastScrollPosition = currentScroll <= 0 ? 0 : currentScroll;
    // });

    var mainNav = document.querySelector('header');
    var lastScrollTop = 0;

    window.addEventListener('scroll', function() {
        var scrollTop = window.scrollY || document.documentElement.scrollTop;
        
        // Prevent negative scrollTop (elastic scroll) from causing the nav to hide
        if (scrollTop < 0) {
            scrollTop = 0;
        }

        if (scrollTop > lastScrollTop) {
            // Scrolling down
            mainNav.classList.add('hidden');
            mainNav.classList.remove('scrolled');
        } else {
            // Scrolling up
            mainNav.classList.remove('hidden');
            mainNav.classList.add('scrolled');
        }

        lastScrollTop = scrollTop;
    });
    
});


