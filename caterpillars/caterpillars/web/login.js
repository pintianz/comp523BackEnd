var baseUrl = "https://secure28.webhostinghub.com/~pocket14/forsyth.im/caterpillars/";
$(document).ready(function () {
    $.ajax(baseUrl + "login.php", {
        type: "GET",
        success: function (data, status, xhr) {
            window.location.replace("index.html");
        }
    });

    var $submitButton = $("#login");
    $submitButton.click(function (e) {
        e.preventDefault();
        var $pw = $('#password');
        var $email = $('#email');

        if (!$email.val() || !$pw.val()) {
            $(".error").remove();
            $pw.css("border", "1px solid red");
            $email.css("border", "1px solid red");
            $pw.after("<p class = 'error'>Please fill in both email and password before submitting!</p>");
            return;
        }

        var json_obj = {email: $email.val(), password: $pw.val()};
        $.ajax(baseUrl + "login.php",
                {type: "POST",
                    dataType: "json",
                    data: JSON.stringify(json_obj),
                    success: function (data, status, xhr) {
                        if (data.validPw == 0) {
                            $pw.val("");
                            $(".error").remove();
                            $pw.css("border", "1px solid red");
                            $pw.after("<p class = 'error'> Password not correct!</p>");
                            return;
                        }
                        if (data.active == 0) {
                            $pw.val("");
                            $email.val("");
                            $(".error").remove();
                            $email.css("border", "1px solid red");
                            $email.after("<p class = 'error'>User not activated!</p>");
                            return;
                        }
                        if (data.validUser == 0) {
                            $pw.val("");
                            $email.val("");
                            $(".error").remove();
                            $email.css("border", "1px solid red");
                            $email.after("<p class = 'error'>User has been marked invalid. Please contact an administrator.</p>");
                            return;
                        }
                        
                        //Login Success
                        window.location.replace("index.html");
                    },
                    error: function (xhr, status) {
                        if (xhr.status == 404) {
                            $pw.val("");
                            $email.val("");
                            $(".error").remove();
                            $email.css("border", "1px solid red");
                            $email.after("<p class = 'error'>User not found!</p>")
                        }
                    }
                });
    });
    
    //Add registration form
    $("#register").click(function(e){
        e.preventDefault();
        //Add registration form
        $("#box").html("<h2>Caterpillars Count Registration</h2>");
        $form = $("<form class='register'></form>");
        $form.append("<input type='text' name='email' placeholder= 'Email Address'><br>");
        $form.append("<input type='password' name='password' placeholder= 'Password'><br>");
        $form.append("<input type='text' name='firstName'placeholder= 'First name'><br>");
        $form.append("<input type='text' name='lastName'placeholder= 'Last name'><br>");
        $form.append("<button class='submit'>Submit</button>");
        $("#box").append($form);    
    });
    
    //Submit registration form
    $("#box").on("click", "button.submit", function(e){
       e.preventDefault();
       var $form = $("#box").find("form");
       var $email = $form.find("input[name=email]");
       var $password = $form.find("input[name=password]");
       var $firstName = $form.find("input[name=firstName]");
       var $lastName = $form.find("input[name=lastName]");
       
       if (!$email.val()) {
            $(".error").remove();
            $form.find("input").css("border", "");
            $email.css("border", "1px solid red");
            $form.prepend("<p class = 'error'>Please enter an email.</p>");
            return;
        }
        
        if (!$password.val()) {
            $(".error").remove();
            $form.find("input").css("border", "");
            $password.css("border", "1px solid red");
            $form.prepend("<p class = 'error'>Please enter an password.</p>");
            return;
        }
        
        if (!$firstName.val()) {
            $(".error").remove();
            $form.find("input").css("border", "");
            $firstName.css("border", "1px solid red");
            $form.prepend("<p class = 'error'>Please enter your first name.</p>");
            return;
        }
        
        if (!$lastName.val()) {
            $(".error").remove();
            $form.find("input").css("border", "");
            $lastName.css("border", "1px solid red");
            $form.prepend("<p class = 'error'>Please enter your last name.</p>");
            return;
        }
        
        var inputData = {
            email: $email.val(),
            password: $password.val(),
            name: $firstName.val() + " " + $lastName.val()
        };
        
        $.ajax({
           url: baseUrl + "users.php",
           type: "POST",
           dataType: "json",
           data: JSON.stringify(inputData),
           success: function(data, xhr, status){
               $("#box").html("<h4>You have successfully registered an account! Please verify \n\
the following email address by going to your email and click on the link we have just sent to you in email.</h4>");
               $("#box").append("<h4>" + data.email + "</h4>");
           },
           error: function(xhr,status){
                if (xhr.status == 409) {
                    $(".error").remove();
                    $form.find("input").css("border", "");
                    $email.css("border", "1px solid red");
                    $form.prepend("<p class = 'error'>The email address has been registered with another account. \n\
 Please log in or use a different email.</p>");
                }
           }
        });
    });
});