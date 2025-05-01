<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';
    echo "Received username: $username<br>";
    echo "Received password: $password<br>";
} else {
    echo "Form was not submitted.";
}
?>
