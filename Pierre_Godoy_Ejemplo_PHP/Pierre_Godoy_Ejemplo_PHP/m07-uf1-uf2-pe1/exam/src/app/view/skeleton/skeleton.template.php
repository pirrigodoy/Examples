<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="/css/style.css">

    <title> <?=$title?> </title>
</head>

<body>
    <img src="/img/logo.jpg" alt="Logo image">

    <h1> <?=$title?> </h1>

    <p>
    <?php
        $login_link  = '<a href="/login">  (Login)  </a>';
        $logout_link = '<a href="/logout"> (Logout) </a>';
        $link        = ($user_name == 'guest') ? $login_link : $logout_link;
        $welcome_msg = "Hello, $user_name. $link";
        echo $welcome_msg;
    ?>
    </p>

    <?=$body?>
</body>

</html>
