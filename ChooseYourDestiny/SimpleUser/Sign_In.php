<!DOCTYPE html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Login Form</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <section class="container">
        <div class="login">
        <h1>Choose Your Destiny</h1>
        <form method="post" action="Register.php">
            <p><input type="text" name="first_name" value="" placeholder="First Name"></p>
            <p><input type="text" name="last_name" value="" placeholder="Last Name"></p> 
            <p><input type="text" name="gender" value="" placeholder="Gender"></p> 
            <p><input type="text" name="email" value="" placeholder="E-mail"></p> 
            <p><input type="text" name="username" value="" placeholder="User"></p> 
            <p><input type="password" name="password" value="" placeholder="Password"></p> 
            <p class="signup"><input type="submit" value="Sign Up"></p>
        </form>      
        <p class="cancel"><input type="submit" value="Cancel" onclick="cancel();"></p>    
        <script type="text/javascript">
            function cancel()
            {
                location.replace("../Index.php");
            }
        </script>
        </div>
    </section>
</body>
</html>
