// TEMPORARY PLAINTEXT LOGIN FOR CLASS ONLY

document.getElementById("loginForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    // Hardcoded demo credentials
    const validUsers = [
        { username: "student", password: "1234" },
        { username: "admin", password: "adminpass" }
    ];

    const match = validUsers.find(user => 
        user.username === username && user.password === password
    );

    if (match) {
        alert("Login successful!");
        window.location.href = "dashboard.html"; // redirect
    } else {
        alert("Invalid username or password");
    }
});
