document.addEventListener("DOMContentLoaded", () => {
    const navLinks = document.querySelectorAll("ul li a");
    const currentPage = window.location.pathname.split("/").pop(); 

    navLinks.forEach(link => {
        if (link.getAttribute("href") === currentPage) {
            link.classList.add("active");
        } else {
            link.classList.remove("active");
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const heading = document.querySelector(".header-left h1");

    heading.classList.add("active"); 

    heading.addEventListener("click", function () {
        this.classList.add("active"); 
    });
});


document.addEventListener("DOMContentLoaded", function () {
    const nav = document.querySelector("nav");

    setTimeout(() => {
        nav.classList.add("show");
    }, 100);
});

document.addEventListener("DOMContentLoaded", function () {
    const navLinks = document.querySelectorAll(".nav-link");
    const indicator = document.querySelector(".nav-indicator");

    function moveIndicator(link) {
        const linkRect = link.getBoundingClientRect();
        const navRect = link.closest("ul").getBoundingClientRect();
        
        indicator.style.top = `${link.offsetTop}px`;
        indicator.style.left = `${link.offsetLeft}px`;
        indicator.style.width = `${link.offsetWidth}px`;
    }

    navLinks.forEach(link => {
        link.addEventListener("click", function (event) {
            event.preventDefault(); 
            navLinks.forEach(l => l.classList.remove("active"));
            this.classList.add("active");
            moveIndicator(this);
        });
    });

    const activeLink = document.querySelector(".nav-link.active");
    if (activeLink) {
        moveIndicator(activeLink);
    }
});


document.getElementById("filterSelect").addEventListener("change", function () {
    var selectedValue = this.value;
    var tagabiliTable = document.getElementById("tagabiliTable");
    var storeOwnerTable = document.getElementById("storeOwnerTable");

    tagabiliTable.style.display = "none";
    storeOwnerTable.style.display = "none";

    if (selectedValue === "partnership") {
        tagabiliTable.style.display = "flex";
    } else if (selectedValue === "standard") {
        storeOwnerTable.style.display = "flex";
    }
});

    // Logout
    document.getElementById("logoutButton").addEventListener("click", function () {
        if (confirm("Are you sure you want to log out?")) {
            sessionStorage.clear();
            localStorage.clear();
            window.location.href = "login.html";
        }
    });
