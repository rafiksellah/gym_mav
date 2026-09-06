document.addEventListener("DOMContentLoaded", function () {

  ```
/*
 * ==========================================
 * CURRENT YEAR
 * ==========================================
 */

const currentYear = document.getElementById("currentYear");

if (currentYear) {
    currentYear.textContent = new Date().getFullYear();
}


/*
 * ==========================================
 * NAVBAR ON SCROLL
 * ==========================================
 */

const navbar = document.querySelector(".main-navbar");

function handleNavbarScroll() {

    if (!navbar) {
        return;
    }

    if (window.scrollY > 50) {
        navbar.classList.add("navbar-scrolled");
    } else {
        navbar.classList.remove("navbar-scrolled");
    }

}

handleNavbarScroll();

window.addEventListener("scroll", handleNavbarScroll);


/*
 * ==========================================
 * CLOSE MOBILE MENU AFTER CLICK
 * ==========================================
 */

const navbarLinks = document.querySelectorAll(
    ".navbar-collapse .nav-link"
);

const navbarCollapse = document.querySelector(
    ".navbar-collapse"
);

navbarLinks.forEach(function (link) {

    link.addEventListener("click", function () {

        if (
            window.innerWidth < 992 &&
            navbarCollapse.classList.contains("show")
        ) {

            const bootstrapCollapse =
                bootstrap.Collapse.getOrCreateInstance(
                    navbarCollapse
                );

            bootstrapCollapse.hide();
        }

    });

});


/*
 * ==========================================
 * SMOOTH REVEAL ANIMATION
 * ==========================================
 */

const animatedElements = document.querySelectorAll(
    ".product-card, .category-card, .service-card, .about-feature"
);

animatedElements.forEach(function (element) {
    element.classList.add("animate-on-scroll");
});


const observerOptions = {
    threshold: 0.15
};


const observer = new IntersectionObserver(
    function (entries) {

        entries.forEach(function (entry) {

            if (entry.isIntersecting) {

                entry.target.classList.add("visible");

                observer.unobserve(entry.target);

            }

        });

    },
    observerOptions
);


document
    .querySelectorAll(".animate-on-scroll")
    .forEach(function (element) {

        observer.observe(element);

    });

});
