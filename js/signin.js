function validateSignInForm() {
     const email = document.getElementsByName("email_id")[0].value.trim();
    const password = document.getElementsByName("password")[0].value.trim();

    if (!email || !password) {
        alert("Please fill in all fields.");
        return false;
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        alert("Please enter a valid email address.");
        return false;
    }

    if (password.length < 6) {
        alert("Password must be at least 6 characters long.");
        return false;
    }

    return true;
}