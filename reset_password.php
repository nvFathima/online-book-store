<?php
    session_start();
    include "db_connect.php";

    if (!isset($_GET['email']) || empty($_GET['email'])) {
        if (!isset($_SESSION['email'])){
            echo '<script>
                        alert("Invalid or missing email.");
                        window.location.href = "reset_password.php";
                    </script>';
            exit;
        }
        else{
            $email = $_SESSION['email'];
        }
    }
    else{
        $email = $_GET['email'];
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $verification_code = $_POST['verification_code'];
        $new_password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Check if passwords match
        if ($new_password !== $confirm_password) {
            echo "Passwords do not match.";
            exit;
        }

        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Verify the code and expiration
        $sql = "SELECT * FROM password_reset WHERE email = ? AND verification_code = ? AND expire_at > NOW() LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $verification_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Code is valid, update the password
            $updatePassword = "UPDATE user SET password = ? WHERE email = ?";
            $stmt = $conn->prepare($updatePassword);
            $stmt->bind_param("ss", $hashed_password, $email);
            $stmt->execute();

            // Delete the reset entry after successful reset
            $deleteCode = "DELETE FROM password_reset WHERE email = ?";
            $stmt = $conn->prepare($deleteCode);
            $stmt->bind_param("s", $email);
            $stmt->execute();

            // Show success message and close the window
            echo '<script>
                    alert("Your password has been successfully reset.");
                    window.opener.location.reload(); // Reload the parent window (login page)
                    window.close(); // Close the current reset password window
                </script>';
        } else {
            echo "Invalid verification code or the code has expired.";
        }
    }

    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 0.5rem;
            color: #555;
        }
        input {
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 0.75rem;
            background-color: #d33b33;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #c32b2b;
        }

        /* Password validation message styles */
        #message {
        display: none;
        background: #f1f1f1;
        color: #000;
        position: relative;
        padding: 20px;
        margin-top: 10px;
        border-radius: 5px;
        box-shadow: 0px 8px 20px 0px rgba(0, 0, 0, 0.15); /* Match your card shadow */
        font-family: "Poppins", "Arial", "Helvetica Neue", sans-serif;
        }

        #message h3 {
        margin-top: 0;
        font-size: 18px;
        color: #525252;
        }

        #message p {
        padding: 10px 0 10px 35px; /* Adjusted padding for better alignment */
        font-size: 14px;
        position: relative;
        }

        /* Green checkmark for valid input */
        .valid {
        color: green;
        }

        .valid:before {
        position: absolute;
        left: 0;
        content: "✔"; /* Check mark */
        font-size: 18px;
        line-height: 18px;
        }

        /* Red cross mark for invalid input */
        .invalid {
        color: red;
        }

        .invalid:before {
        position: absolute;
        left: 0;
        content: "✖"; /* Cross mark */
        font-size: 18px;
        line-height: 18px;
        }
    </style>
    <script src="js/register-validate.js"></script>
</head>
<body>
    <div class="container">
        <h1>Reset Your Password</h1>
            <form action="reset_password.php?email=<?php echo $email; ?>" method="POST">
                <!--<input type="hidden" name="email" value="<?php //echo htmlspecialchars($email); ?>">-->
                
                <label for="verification_code">Verification Code:</label>
                <input type="text" id="verification_code" name="verification_code" required>
                
                <label for="new_password">New Password:</label>
                <input type="password" id="password" name="password" required>
                
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" onkeyup="passwordMatch()" required>
                <p style='color:red;font-size:12px' id='pass-confirm'></p>

                <div id="message">
                            <h3>Password must contain the following:</h3>
                            <p id="letter" class="invalid">A <b>lowercase</b> letter</p>
                            <p id="capital" class="invalid">A <b>capital (uppercase)</b> letter</p>
                            <p id="number" class="invalid">A <b>number</b></p>
                            <p id="special" class="invalid">A <b>special character</b> (e.g., !@#$%^&*)</p>
                            <p id="length" class="invalid">Minimum <b>8 characters</b></p>
                </div>
                <button type="submit">Reset Password</button>
            </form>
    </div>

    <!-- Password validation script -->
    <script>
        var myInput = document.getElementById("password");
        var letter = document.getElementById("letter");
        var capital = document.getElementById("capital");
        var number = document.getElementById("number");
        var length = document.getElementById("length");
        var special = document.getElementById("special"); // New Variable

        myInput.onfocus = function() {
            document.getElementById("message").style.display = "block";
        }

        myInput.onblur = function() {
            document.getElementById("message").style.display = "none";
        }

        myInput.onkeyup = function() {
            // Validate lowercase letters
            var lowerCaseLetters = /[a-z]/g;
            if(myInput.value.match(lowerCaseLetters)) {  
                letter.classList.remove("invalid");
                letter.classList.add("valid");
            } else {
                letter.classList.remove("valid");
                letter.classList.add("invalid");
            }
            
            // Validate capital letters
            var upperCaseLetters = /[A-Z]/g;
            if(myInput.value.match(upperCaseLetters)) {  
                capital.classList.remove("invalid");
                capital.classList.add("valid");
            } else {
                capital.classList.remove("valid");
                capital.classList.add("invalid");
            }

            // Validate numbers
            var numbers = /[0-9]/g;
            if(myInput.value.match(numbers)) {  
                number.classList.remove("invalid");
                number.classList.add("valid");
            } else {
                number.classList.remove("valid");
                number.classList.add("invalid");
            }
            
            // Validate length
            if(myInput.value.length >= 8) {
                length.classList.remove("invalid");
                length.classList.add("valid");
            } else {
                length.classList.remove("valid");
                length.classList.add("invalid");
            }
            
            // Validate special characters
            var specialCharacters = /[!@#$%^&*(),.?":{}|<>]/g; // New Regex
            if(myInput.value.match(specialCharacters)) {  
                special.classList.remove("invalid");
                special.classList.add("valid");
            } else {
                special.classList.remove("valid");
                special.classList.add("invalid");
            }
        }
    </script>
</body>
</html>