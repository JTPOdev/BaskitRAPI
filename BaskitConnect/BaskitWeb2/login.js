const BASE_URL = "192.168.100.111";
const passwordField = document.getElementById('password');
const togglePassword = document.getElementById('togglePassword');
const loginForm = document.getElementById('loginForm');
const errorMessage = document.getElementById('errorMessage');

//  const adminEmail = "admin123";
// const adminPassword = "admin123";

 togglePassword.addEventListener('click', () => {
    const isPasswordVisible = passwordField.type === 'password';
    passwordField.type = isPasswordVisible ? 'text' : 'password';
    togglePassword.textContent = isPasswordVisible ? 'HIDE' : 'SHOW';
});

 loginForm.addEventListener('submit', async function(event) {
    event.preventDefault();
    const username = document.getElementById('email').value;
    const password = passwordField.value;

    try {
        const response = await fetch('http://172.20.10.2/BaskitConnect/BaskitAPI/public/admin/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                username: username,
                password: password
            })
        });

        const responseText = await response.text();
        console.log('Raw response:', responseText);

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('Failed to parse JSON:', e);  
            throw new Error('Server returned invalid JSON');
        }

        if (response.ok) {
            if (data.access_token) {
                localStorage.setItem('access_token', data.access_token);
                localStorage.setItem('isLoggedIn', 'true');
                alert('Login successful!');
                window.location.href = 'dashboard.html';
            } else {
                console.error("Login response missing token:", data);
                errorMessage.style.display = 'block';
                errorMessage.textContent = 'Login failed: Missing access token.';
            }
        } else {
            errorMessage.style.display = 'block';
            errorMessage.textContent = data.message || 'Invalid credentials';
        } 
    } catch (error) {
        console.error('Error:', error);
        errorMessage.style.display = 'block';
        errorMessage.textContent = 'An error occurred. Please try again.';
    }
});
//     if (enteredEmail === adminEmail && enteredPassword === adminPassword) {
//         localStorage.setItem("isLoggedIn", "true");  
//         alert("Login successful!");
//         window.location.href = "summary.html";  
//     } else {
//         errorMessage.style.display = "block";
//     }
// });


// document.getElementById("loginForm").addEventListener("submit", function(event) {
// event.preventDefault();

// const enteredEmail = document.getElementById("email").value;
// const enteredPassword = document.getElementById("password").value;

// const storedEmail = localStorage.getItem("adminEmail") || "admin123";
// const storedPassword = localStorage.getItem("adminPassword") || "admin123";

// if (enteredEmail === storedEmail && enteredPassword === storedPassword) {
// localStorage.setItem("isLoggedIn", "true");
// localStorage.setItem("username", enteredEmail);
// alert("Login successful!");
// window.location.href = "dashboard.html";
// } else {
// document.getElementById("errorMessage").style.display = "block";
// }
// });
