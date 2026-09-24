<?php
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src="js/profile_view.js" defer></script>
    <link href='https://fonts.googleapis.com/css?family=Turret Road' rel='stylesheet'>
    <link href="https://fonts.cdnfonts.com/css/porter-sans-block" rel="stylesheet">
    <link rel="stylesheet" href="style/profile_view.css">
</head>
<body>

<body>

    <?php if (!$isLoggedIn): ?>
        <div class="login-overlay">
            <div class="login-box">
                <h2>Welcome</h2>
                <p>You need an account to edit your profile.</p>
                <div class="login-buttons">
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <header class="top">
        <div class="selectbox selected"></div>
        <div class="selectbox"></div>
        <div class="selectbox"></div>
        <div class="selectbox"></div>
        <div class="selectbox"></div>
        <div class="selectbox"></div>
        <div class="selectbox"></div>
        <div class="selectbox"></div>
        <div class="selectbox"></div>
    </header>

    <main>
        <div id="main">
            <div id="mii">
                <img src="../media/mii.png" alt="Mii face" class="mii-img">
                <button id="confirm">Confirm</button>
            </div>
            <div id="box">
                <div class="rowb">
                    <div class="infob">
                        <p>Name:</p>
                        <input type="text">
                    </div>
                    <div class="infob">
                        <p>Email:</p>
                        <input type="email">
                    </div>
                </div>
                <div class="rowb">
                    <div class="infob">
                        <p>Gender:</p>
                        <input type="text">
                    </div>
                </div>
                <div class="rowb">
                    <div class="infob">
                        <p>Birthday:</p>
                        <input type="date">
                    </div>
                </div>
                <div class="rowb">
                    <div class="infob">
                        <p>Favourite color:</p>
                        <input type="color">
                    </div>
                </div>
                <div class="rowb">
                    <div class="infob">
                        <p>Single:</p>
                        <input type="checkbox">
                    </div>
                </div>
                <div class="rowb">
                    <div class="infob">
                        <p>Password:</p>
                        <input type="password">
                    </div>
                </div>
                <div class="rowb">
                    <div class="infob">
                        <p>Confirm Password:</p>
                        <input type="password">
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bottom">
        <button class="bottom-button home">Home</button>
        <div class="username">Username</div>
        <button class="bottom-button profile">Profile</button>
    </footer>

    <button class="menu-button">Menu</button>
</body>
</html>
