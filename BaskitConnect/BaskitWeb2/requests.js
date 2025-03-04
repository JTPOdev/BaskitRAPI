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

const base_url = "http://172.20.10.2/BaskitConnect/BaskitAPI/public";

document.addEventListener("DOMContentLoaded", async function () {
    const userInfoContainer = document.querySelector(".business-profile");
    const filterSelect = document.getElementById("filterSelect");
    
    let users = [];

    async function fetchUsers() {
        try {
            const response = await fetch(`${base_url}/store/request/all`, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                }
            });

            if (!response.ok) throw new Error("Failed to fetch users");

            users = await response.json();
            renderUsers(users);
        } catch (error) {
            console.error("Error fetching users:", error);
            userInfoContainer.innerHTML = "<p>Error loading users.</p>";
        }
    }

    function renderUsers(usersToDisplay) {
        userInfoContainer.innerHTML = "";
        
        if (usersToDisplay.length === 0) {
            userInfoContainer.innerHTML = "";
            return;
        }

        userInfoContainer.innerHTML = `
            <div class="header-row">
                <div>Username</div>
                <div>Name</div>
                <div>Mobile Number</div>
                <div>Email</div>
            </div>
        `;

        usersToDisplay.forEach(user => {
            let userRow = document.createElement("div");
            userRow.classList.add("user-info");
            userRow.dataset.userId = user.id;
            userRow.innerHTML = `
                <div>${user.username || "N/A"}</div>
                <div>${user.firstname} ${user.lastname || "N/A"}</div>
                <div>${user.mobile_number || "N/A"}</div>
                <div class="email">
                    <span>${user.user_email || "N/A"}</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            `;

            userRow.addEventListener("click", function () {
                toggleUserDetails(userRow, user);
            });

            userInfoContainer.appendChild(userRow);
        });
    }

    function toggleUserDetails(userRow, user) {
        let existingDetailsRow = document.querySelector(".details-row");

        if (existingDetailsRow) {
            if (existingDetailsRow.dataset.userId === user.id.toString()) {
                existingDetailsRow.remove();
                return;
            }
            existingDetailsRow.remove();
        }

        let detailsRow = document.createElement("div");
        detailsRow.classList.add("details-row");
        detailsRow.dataset.userId = user.id;
        detailsRow.innerHTML = `
            <div class="info-box">
                <h2>STORE INFORMATION</h2>
                <div><strong>Store Name:</strong> ${user.store_name || "N/A"}</div>
                <div><strong>Owner:</strong> ${user.owner_name || "N/A"}</div>
                <div><strong>Contact:</strong> ${user.store_phone_number || "N/A"}</div>
                <div><strong>Address:</strong> ${user.store_address || "N/A"}</div>
                <div><strong>Origin:</strong> ${user.store_origin || "N/A"}</div>
            </div>
            <div class="info-box">
                <h2>BUSINESS INFORMATION</h2>
                <div><strong>Registered Store Name:</strong> ${user.registered_store_name || "N/A"}</div>
                <div><strong>Registered Store Address:</strong> ${user.registered_store_address || "N/A"}</div>
                <div><strong>Store Type:</strong> ${user.store_status || "N/A"}</div>
                <div class="documents">
                    <div class="document">
                        <h3>Valid ID:</h3>
                        <img src="${base_url}${user.valid_id}" alt="Valid ID" class="document-image" onclick="openLightbox('${base_url}${user.valid_id}')">
                    </div>
                    <div class="document">
                        <h3>Certificate:</h3>
                        <img src="${base_url}${user.certificate_of_registration}" alt="Certificate" class="document-image" onclick="openLightbox('${base_url}${user.certificate_of_registration}')">
                    </div>
                </div>

                <div class="action-buttons">
                    <button class="decline" data-user-id="${user.id}" data-action="decline">Decline</button>
                    <button class="approve" data-user-id="${user.id}" data-action="approve">Approve</button>
                </div>
            </div>
        `;

        userRow.insertAdjacentElement("afterend", detailsRow);
    }

    function handleAction(userId, action) {
        const endpoint = action === "approve" ? "store/approve" : "store/decline";
        fetch(`${base_url}/${endpoint}/${userId}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            }
        })
        .then(response => response.json())
        .then(data => {
            alert(`User ID ${userId} has been ${action}d.`);
            fetchUsers();
        })
        .catch(error => console.error("Error updating store status:", error));
    }

    filterSelect.addEventListener("change", function () {
        let selectedType = this.value.toLowerCase();

        if (selectedType === "") {
            renderUsers(users);
            return;
        }

        let filteredUsers = users.filter(user => user.store_status.toLowerCase() === selectedType);
        renderUsers(filteredUsers);
    });

    document.addEventListener("click", function (event) {
        if (event.target.matches(".approve, .decline")) {
            let userId = parseInt(event.target.dataset.userId, 10);
            let action = event.target.dataset.action;
            handleAction(userId, action);
        }
    });

    fetchUsers();
});

function openLightbox(imageSrc) {
    document.getElementById("lightbox-img").src = imageSrc;
    document.getElementById("lightbox").style.display = "flex";
}

document.querySelector(".close-btn").addEventListener("click", function () {
    document.getElementById("lightbox").style.display = "none";
});

document.getElementById("lightbox").addEventListener("click", function (event) {
    if (event.target === this) {
        this.style.display = "none";
    }
});

// logout
document.getElementById("logoutButton").addEventListener("click", function () {
    if (confirm("Are you sure you want to log out?")) {
        sessionStorage.clear();
        localStorage.clear();
        window.location.href = "login.html";
    }
});

