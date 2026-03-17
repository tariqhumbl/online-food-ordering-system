<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reset password request</title>
</head>
<body>
    <h1>{{$heading}}</h1>
    <strong>Dear {{$name}}</strong>
    <p>Here is the verification code, to reset your password <strong>{{$code}}</strong></p>
    Thank You <br>
    Regards {{env('APP_NAME')}}
</body>
</html>
