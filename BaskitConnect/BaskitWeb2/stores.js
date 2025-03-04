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


function addStore(image, name, owner, contact, status) {
    const storeContainer = document.getElementById("storeContainer");

    const storeDiv = document.createElement("div");
    storeDiv.classList.add("store-item");

    const storeImg = document.createElement("img");
    storeImg.src = image;
    storeImg.alt = name;

    const storeName = document.createElement("div");
    storeName.classList.add("store-name");
    storeName.textContent = name;

    const storeDetails = document.createElement("div");
    storeDetails.classList.add("store-details");

    const storeOwner = document.createElement("div");
    storeOwner.innerHTML = "<span>Owner:</span> " + owner;

    const storeContact = document.createElement("div");
    storeContact.innerHTML = "<span>Contact:</span> " + contact;

    const storeType = document.createElement("div");
    storeType.innerHTML = "<span>Store Type:</span> " + status;

    const deleteIcon = document.createElement("i");
    deleteIcon.classList.add("fa-solid", "fa-trash", "delete-icon");
    deleteIcon.addEventListener("click", () => {
        storeContainer.removeChild(storeDiv);
    });

    storeDetails.appendChild(deleteIcon);
    storeDetails.appendChild(storeOwner);
    storeDetails.appendChild(storeContact);
    storeDetails.appendChild(storeType);

    storeDiv.appendChild(storeImg);
    storeDiv.appendChild(storeName);
    storeDiv.appendChild(storeDetails);

    storeContainer.appendChild(storeDiv);
}

addStore("img/logo.png", "ABC Store", "ABC DEF", "09123456789", "Partnership");
addStore("img/logo.png", "XYZ Store", "LMN OPQ", "09987654321", "Standard");
addStore("img/logo.png", "QWE Store", "RTY UIO", "09234567890", "Partnership");


let stores = [
    { image: "img/logo.png", name: "ABC Store", owner: "ABC DEF", contact: "09123456789", status: "Partnership", location: "dagupan" },
    { image: "img/logo.png", name: "XYZ Store", owner: "LMN OPQ", contact: "09987654321", status: "Standard", location: "calasiao" },
    { image: "img/logo.png", name: "QWE Store", owner: "RTY UIO", contact: "09234567890", status: "Partnership", location: "dagupan" },
];


const storeProducts = {
    "ABC Store": [
        { name: "Apple", image: "img/logo.png", price: "₱10", category: "fruit" },
        { name: "Carrot", image: "img/logo.png", price: "₱5", category: "vegetable" }
    ],
    "XYZ Store": [
        { name: "Chicken", image: "img/logo.png", price: "₱20", category: "meat" },
        { name: "Salmon", image: "img/logo.png", price: "₱25", category: "fish" }
    ],
    "QWE Store": [
        { name: "Hotdog", image: "img/logo.png", price: "₱8", category: "frozen" },
        { name: "Black Pepper", image: "img/logo.png", price: "₱3", category: "spices" }
    ]
};



function displayStores(filteredStores) {
    const storeContainer = document.getElementById("storeContainer");
    storeContainer.innerHTML = "";  

    filteredStores.forEach(store => {
        const storeDiv = document.createElement("div");
        storeDiv.classList.add("store-item");

        const storeImg = document.createElement("img");
        storeImg.src = store.image;
        storeImg.alt = store.name;

        const storeName = document.createElement("div");
        storeName.classList.add("store-name");
        storeName.textContent = store.name;

        const storeDetails = document.createElement("div");
        storeDetails.classList.add("store-details");

        const storeOwner = document.createElement("div");
        storeOwner.innerHTML = "<span>Owner:</span> " + store.owner;

        const storeContact = document.createElement("div");
        storeContact.innerHTML = "<span>Contact:</span> " + store.contact;

        const storeType = document.createElement("div");
        storeType.innerHTML = "<span>Store Type:</span> " + store.status;

        const deleteIcon = document.createElement("i");
        deleteIcon.classList.add("fa-solid", "fa-trash", "delete-icon");
        deleteIcon.addEventListener("click", () => {
            storeContainer.removeChild(storeDiv);
        });

        storeDetails.appendChild(deleteIcon);
        storeDetails.appendChild(storeOwner);
        storeDetails.appendChild(storeContact);
        storeDetails.appendChild(storeType);

        storeDiv.appendChild(storeImg);
        storeDiv.appendChild(storeName);
        storeDiv.appendChild(storeDetails);

        storeDiv.addEventListener("click", function () {
            showProducts(store.name);
        });

        storeContainer.appendChild(storeDiv);
    });
}

function showProducts(storeName) {
    document.getElementById("storeContainer").style.display = "none"; 
    document.getElementById("productContainer").style.display = "block"; 

    document.getElementById("filterSelect").style.display = "none"; 
    document.getElementById("locationSelect").style.display = "none"; 

    document.getElementById("categorySelect").style.display = "inline-block";
    document.getElementById("backButton").style.display = "inline-block";

    document.querySelector(".header-left h1").textContent = storeName;

    
    document.getElementById("categorySelect").dataset.storeName = storeName;
    displayProducts(storeName, "all");
}

function displayProducts(storeName, category) {
    const productList = document.getElementById("productList");
    productList.innerHTML = ""; 

    const products = storeProducts[storeName] || [];
    const filteredProducts = category === "all" ? products : products.filter(p => p.category === category);

    if (filteredProducts.length === 0) {
        productList.innerHTML = "<p>No products available in this category.</p>";
    } else {
        filteredProducts.forEach(product => {
            const productCard = document.createElement("div");
            productCard.classList.add("product-card");

            productCard.innerHTML = `
            <img src="${product.image}" alt="${product.name}">
            <h3>${product.name} - ${product.price}</h3> 
        `;
        

            productList.appendChild(productCard);
        });
    }
}

document.getElementById("categorySelect").addEventListener("change", function () {
    const storeName = this.dataset.storeName;
    const selectedCategory = this.value;
    displayProducts(storeName, selectedCategory);
});

document.getElementById("backButton").addEventListener("click", function () {
    document.getElementById("productContainer").style.display = "none"; 
    document.getElementById("storeContainer").style.display = "flex"; 

    document.getElementById("filterSelect").style.display = "inline-block"; 
    document.getElementById("locationSelect").style.display = "inline-block";

    document.getElementById("categorySelect").style.display = "none"; 
    document.getElementById("backButton").style.display = "none"; 

    document.querySelector(".header-left h1").textContent = "Stores"; 
});




function filterStores() {
    const selectedType = document.getElementById("filterSelect").value;
    const selectedLocation = document.getElementById("locationSelect").value;

    let filteredStores = stores.filter(store => {
        const matchesType = selectedType === "all" || store.status.toLowerCase() === selectedType;
        const matchesLocation = selectedLocation === "all" || store.location.toLowerCase() === selectedLocation;
        return matchesType && matchesLocation;
    });

    displayStores(filteredStores);
}

document.getElementById("filterSelect").addEventListener("change", filterStores);
document.getElementById("locationSelect").addEventListener("change", filterStores);

displayStores(stores);

    // Logout
    document.getElementById("logoutButton").addEventListener("click", function () {
        if (confirm("Are you sure you want to log out?")) {
            sessionStorage.clear();
            localStorage.clear();
            window.location.href = "login.html";
        }
    });
