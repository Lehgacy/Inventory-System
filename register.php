 <link rel="stylesheet" href="SweetAlert/sweetalert2.min.css">
  <script src="SweetAlert/sweetalert2.all.min.js"></script>

<?php
include 'connection.php';
session_start();

if (isset($_POST['signUp'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Check if password and confirm password match
    if ($password !== $confirmPassword) {
        ?>"<script>
            Swal.fire('Error!', 'Password does not match!', 'error')
            .then(function() {
                window.location = 'index.php';
            });
        </script>";
        <?php
    } else {
        // Check if email already exists
        $checkEmail = "SELECT * FROM registrationtable WHERE email='$email'";
        $result = $conn->query($checkEmail);

        if ($result->num_rows > 0) {
            ?>"<script>
                Swal.fire('Error!', 'Email Address Already Exists!', 'warning')
                .then(function() {
                    window.location = 'index.php';
                });
            </script>";
            <?php

        } else {
            // Insert new user
            $insertQuery = "INSERT INTO registrationtable (username, email, password)
                            VALUES ('$username', '$email', '$password')";
            if ($conn->query($insertQuery) === TRUE) {
                 ?>"<script>
                    Swal.fire('Success!', 'Registration Successful!', 'success')
                    .then(function() {
                        window.location = 'index.php';
                    });
                </script>";
                <?php

            } else {
                $errorMsg = addslashes($conn->error);
                ?>
                "<script>
                    Swal.fire('Error!', 'Something went wrong: $errorMsg', 'error')
                    .then(function() {
                        window.location = 'index.php';
                    });
                </script>";
                <?php
            }
        }
    }
}

if (isset($_POST['signIn'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
   
    $sql = "SELECT * FROM registrationtable WHERE email='$email' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['email'] = $row['email'];

         ?>"<script>
            Swal.fire('Success!', 'Login Successful!', 'success')
            .then(function() {
                window.location = 'homepage.php';
            });
        </script>";
            exit();             
        <?php
    } 
    else {
        ?> "<script>
            Swal.fire('Error!', 'Incorrect email or password!', 'warning')
            .then(function() {
                window.location = 'index.php';
            });
        </script>";
        <?php
    }
}
?>
