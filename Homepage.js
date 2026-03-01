document.addEventListener("DOMContentLoaded", function() {
    // ==== Hamburger Menu Toggle ====
    var hamburger = document.getElementById("hamburgerBtn");
    var mainNav = document.getElementById("mainNav");
    var rightActions = document.getElementById("rightActions");
    var menu = document.querySelector(".menu");

    if (hamburger) {
        // Toggle function shared by click and touch
        function toggleMenu(e) {
            if (e) {
                e.stopPropagation();
                e.preventDefault();
            }
            hamburger.classList.toggle("active");
            if (mainNav) mainNav.classList.toggle("show");
            if (rightActions) rightActions.classList.toggle("show");
            if (menu) menu.classList.toggle("menu-open");
        }

        hamburger.addEventListener("click", toggleMenu);

        // Fix for older Android WebView where click doesn't fire on tap
        hamburger.addEventListener("touchend", function(e) {
            e.stopPropagation();
            e.preventDefault();
            toggleMenu();
        });

        // Close on outside click/tap
        function closeMenu(e) {
            if (menu && !menu.contains(e.target)) {
                hamburger.classList.remove("active");
                if (mainNav) mainNav.classList.remove("show");
                if (rightActions) rightActions.classList.remove("show");
                if (menu) menu.classList.remove("menu-open");
            }
        }
        document.addEventListener("click", closeMenu);
        document.addEventListener("touchstart", closeMenu);

        if (mainNav) {
            var links = mainNav.querySelectorAll(".nav-link");
            for (var i = 0; i < links.length; i++) {
                links[i].addEventListener("click", function() {
                    hamburger.classList.remove("active");
                    mainNav.classList.remove("show");
                    if (rightActions) rightActions.classList.remove("show");
                    if (menu) menu.classList.remove("menu-open");
                });
            }
        }
    }

    // ==== Scroll-Spy: Track Active Section ====
    var allNavLinks = document.querySelectorAll(".center-nav .nav-link");
    var homeLink = null;
    var sectionMap = []; // { el, link }

    for (var i = 0; i < allNavLinks.length; i++) {
        var href = allNavLinks[i].getAttribute("href");
        if (href === "index.php") {
            homeLink = allNavLinks[i];
        } else if (href && href.charAt(0) === "#" && href.length > 1) {
            var el = document.getElementById(href.substring(1));
            if (el) {
                sectionMap.push({ el: el, link: allNavLinks[i] });
            }
        }
    }

    function setActive(link) {
        for (var i = 0; i < allNavLinks.length; i++) {
            allNavLinks[i].classList.remove("active");
        }
        if (link) link.classList.add("active");
    }

    function updateScrollSpy() {
        var scrollTop = window.pageYOffset || document.documentElement.scrollTop || 0;
        var windowH = window.innerHeight || document.documentElement.clientHeight;
        var docH = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);

        // Near bottom → activate last section
        if (scrollTop + windowH >= docH - 80) {
            if (sectionMap.length > 0) {
                setActive(sectionMap[sectionMap.length - 1].link);
            }
            return;
        }

        // Find last section whose top has scrolled past the navbar
        var navH = 100;
        var activeLink = null;

        for (var i = 0; i < sectionMap.length; i++) {
            var top = sectionMap[i].el.getBoundingClientRect().top;
            if (top <= navH) {
                activeLink = sectionMap[i].link;
            }
        }

        // If no section has passed the navbar → we're at the top → Home
        if (!activeLink) {
            activeLink = homeLink;
        }

        setActive(activeLink);
    }

    // Run on scroll
    var ticking = false;
    window.addEventListener("scroll", function() {
        if (!ticking) {
            ticking = true;
            if (window.requestAnimationFrame) {
                window.requestAnimationFrame(function() {
                    updateScrollSpy();
                    ticking = false;
                });
            } else {
                setTimeout(function() {
                    updateScrollSpy();
                    ticking = false;
                }, 16);
            }
        }
    });

    // Click → set active immediately
    for (var i = 0; i < allNavLinks.length; i++) {
        (function(link) {
            link.addEventListener("click", function() {
                setActive(link);
            });
        })(allNavLinks[i]);
    }

    // Initial state
    updateScrollSpy();

    // ==== Navbar Shadow on Scroll ====
    window.addEventListener("scroll", function() {
        var scrollTop = window.pageYOffset || document.documentElement.scrollTop || 0;
        if (menu) {
            if (scrollTop > 20) {
                menu.classList.add("scrolled");
            } else {
                menu.classList.remove("scrolled");
            }
        }
    });

    // ==== Scroll-Reveal Animation ====
    var revealEls = document.querySelectorAll(".feature-card, .contact-card, .about-container, .section-header");

    if ("IntersectionObserver" in window) {
        var observer = new IntersectionObserver(function(entries) {
            for (var i = 0; i < entries.length; i++) {
                if (entries[i].isIntersecting) {
                    entries[i].target.classList.add("revealed");
                    observer.unobserve(entries[i].target);
                }
            }
        }, { threshold: 0.12, rootMargin: "0px 0px -30px 0px" });

        for (var i = 0; i < revealEls.length; i++) {
            revealEls[i].classList.add("reveal-on-scroll");
            observer.observe(revealEls[i]);
        }
    } else {
        // Fallback for old browsers
        for (var i = 0; i < revealEls.length; i++) {
            revealEls[i].classList.add("revealed");
        }
    }
});