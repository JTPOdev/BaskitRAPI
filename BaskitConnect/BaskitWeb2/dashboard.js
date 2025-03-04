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
    const salesData = {
        today: 150.75,
        thisWeek: 1025.50,
        totalOrdersMonth: 45
    };

    document.getElementById("salesToday").textContent = `₱${salesData.today.toLocaleString("en-PH", { minimumFractionDigits: 2 })}`;
    document.getElementById("salesWeek").textContent = `₱${salesData.thisWeek.toLocaleString("en-PH", { minimumFractionDigits: 2 })}`;
    document.getElementById("ordersMonth").textContent = salesData.totalOrdersMonth;
});


const productCtx = document.getElementById('productChart').getContext('2d');
const productChart = new Chart(productCtx, {
    type: 'bar',
    data: {
        labels: ['Apple', 'Orange', 'Cabbage', 'Ampalaya', 'Pineapple'],
        datasets: [{
            label: 'Times Purchased',
            data: [150, 80, 120, 60, 30],
            backgroundColor: ['#4CAF50', '#2196F3', '#FF9800', '#F44336', '#9C27B0'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

const storeCtx = document.getElementById('storeChart').getContext('2d');
const storeChart = new Chart(storeCtx, {
    type: 'pie',
    data: {
        labels: ['Standard Store', 'Partnership Store'],
        datasets: [{
            label: 'Orders',
            data: [200, 300],
            backgroundColor: ['#FF6384', '#36A2EB']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
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
