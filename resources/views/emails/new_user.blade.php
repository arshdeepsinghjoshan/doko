<!DOCTYPE html>
<html>

<head>
    <title>New User Registered</title>
</head>

<body>
    <h1>New User Registration</h1>
    <p>A new user has registered with the following details:</p>
    <p>Name: {{ $model->name }}</p>
    <p>Email Otp : {{ $model->otp_email }}</p>
</body>
</html>