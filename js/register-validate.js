function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function emailCheck() {
        var email = document.getElementById('email').value;
        if (!isValidEmail(email)) {
            document.getElementById("email-val").innerText = "Invalid email format";
            return;
        }
        else{
            var xhr = new XMLHttpRequest();
            xhr.open("POST", 'form-validate.php',true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function(){
                
                if(xhr.status == 200){
                    //console.log(xhr.responseText);
                    if(xhr.responseText.trim() === "exists"){
                        console.log(xhr.responseText);
                        document.getElementById("email-val").textContent = "Email already exists";
                    }
                    else{
                        document.getElementById("email-val").textContent = "";
                    }
                }
            }
            var params = "email=" + encodeURIComponent(email);
            xhr.send(params);
        }
}

function numCheck(){
    var num = document.getElementById('contact_number').value;
    if(num.length < 10){
        document.getElementById('phone-val').innerHTML = 'Contact Number must be ten digits long';
    }
    else if(num.length == 10){
        document.getElementById('phone-val').innerHTML = '';
    }
    else if(num.length >10){
        document.getElementById('phone-val').innerHTML = 'Invalid Contact Number';
    }
}

function passwordMatch(){
    var pass = document.getElementById('password').value;
    var confirm = document.getElementById('confirm_password').value;
    if(pass != confirm){
        document.getElementById('pass-confirm').innerHTML = 'Password doesn\'t match';
    }
    else if(pass == confirm){
        document.getElementById('pass-confirm').innerHTML = '';
    }
}

document.querySelector('form').addEventListener('submit', function(e) {
    var agreementCheckbox = document.getElementById('agreement');
    if (!agreementCheckbox.checked) {
        e.preventDefault();
        alert('You must agree to the Terms of Service and Privacy Policy to register.');
    }
});