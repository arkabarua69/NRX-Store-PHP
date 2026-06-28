<script type="text/javascript">
    // Get input elements
    const usernameInput = document.getElementById("username");
    const emailInput = document.getElementById("email");
    const phoneInput = document.getElementById("phone");
    const passwordInput = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("confirm_password");


    // Get alert divs
    const usernameAlert = document.getElementById("username-alert");
    const emailAlert = document.getElementById("email-alert");
    const phoneAlert = document.getElementById("phone-alert");
    const passwordAlert = document.getElementById("password-alert");
    const confirmPasswordAlert = document.getElementById("confirm_password-alert");

    // Add event listeners to input elements
    usernameInput.addEventListener("input", validateUsername);
    emailInput.addEventListener("input", validateEmail);
    phoneInput.addEventListener("input", validatephone);
    passwordInput.addEventListener("input", validatePassword);
    confirmPasswordInput.addEventListener("input", validateConfirmPassword);

    function validateUsername() {
        // Check if username is more than 5 characters
        if (usernameInput.value.length < 5) {
            usernameAlert.innerText = "Full Name should be more than 3 characters.";
            usernameAlert.classList.add("alert", "alert-white");
            usernameAlert.classList.remove("alert-success");
        } else {
            // Clear alert if username is valid
            usernameAlert.innerText = "";
            usernameAlert.classList.remove("alert", "alert-white");
            usernameAlert.classList.add("alert-success");
        }
    }

    function validateEmail() {
        // Check if email is valid
        if (!/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(emailInput.value)) {
            emailAlert.innerText = "Please enter a valid email address.";
            emailAlert.classList.add("alert", "alert-white");
            emailAlert.classList.remove("alert-success");
        } else {
            // Clear alert if email is valid
            emailAlert.innerText = "";
            emailAlert.classList.remove("alert", "alert-white");
            emailAlert.classList.add("alert-success");
        }
    }

    function validatephone() {
        // Check if phone number is valid
        if (!/^\d{11}$/.test(phoneInput.value)) {
            phoneAlert.innerText = "Please enter a 11 digit phone number.";
            phoneAlert.classList.add("alert", "alert-white");
            phoneAlert.classList.remove("alert-success");
        } else {
            // Clear alert if phone number is valid
            phoneAlert.innerText = "";
            phoneAlert.classList.remove("alert", "alert-white");
            phoneAlert.classList.add("alert-success");
        }
    }

    function validatePassword() {
        // Check if password is more than 8 characters
        if (passwordInput.value.length < 8) {
            passwordAlert.innerText = "Password should be more than 8 characters.";
            passwordAlert.classList.add("alert", "alert-white");
            passwordAlert.classList.remove("alert-success");
        } else if (passwordInput.value === usernameInput.value) {
            // Check if password doesn't match username
            passwordAlert.innerText = "Password should not match username.";
            passwordAlert.classList.add("alert", "alert-white");
            passwordAlert.classList.remove("alert-success");
        } else {
            // Clear alert if password is valid
            passwordAlert.innerText = "";
            passwordAlert.classList.remove("alert", "alert-white");
            passwordAlert.classList.add("alert-success");
        }
    }

    function validateConfirmPassword() {
        // Check if confirm password matches password
        if (confirmPasswordInput.value !== passwordInput.value) {
            confirmPasswordAlert.innerText = "Confirm password does not match password.";
            confirmPasswordAlert.classList.add("alert", "alert-white");
            confirmPasswordAlert.classList.remove("alert-success");
        } else {
            // Clear alert if confirm password matches password
            confirmPasswordAlert.innerText = "";
            confirmPasswordAlert.classList.remove("alert", "alert-white");
            confirmPasswordAlert.classList.add("alert-success");
        }
    }
</script>
