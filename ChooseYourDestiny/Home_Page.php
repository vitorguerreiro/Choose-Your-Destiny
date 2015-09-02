<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your Destiny</title>
    <link rel="stylesheet" href="css/homepage.css">
    <script type="text/javascript" src="//code.jquery.com/jquery-1.11.3.min.js"></script>
</head>
<body>
    <script type="text/javascript">
        function logout() {
            location.replace("Index.php");
        }
        
        function edit_profile() {
            location.replace("SimpleUser/Edit_Profile.php");
        }
    </script>
    <?php
        if($_SESSION['user_mode'] == 'facebook')
        {
            
        }
        else if($_SESSION['user_mode'] == 'simple')
        {
            
        }
        else
        {
            ?>
            <script type="text/javascript">
                location.replace("Index.php");
            </script>
            <?php
        }
    ?>
    <div class="container clearfix">
        <header class="header">
            <h1>
                Choose Your Destiny
            </h1>
        </header>
        
        <section class="search-banner">
            <div id="search-content">                
                <form method="POST" action="" id="form-search">
                    <input class="form-search-box" type="text" name="venue" placeholder="E.g. Campinas">
                    <button id="search_button" type="submit" class="button" name="button_search_venue">Search</button>
                </form>
            </div> 
            <?php
                if($_SESSION['user_mode'] == 'facebook')
                {
                    require_once 'Facebook/FB_Logout.php';
                }
                else if($_SESSION['user_mode'] == 'simple')
                {
                    ?>
                        <a href="#" onclick="edit_profile();">Edit Profile</a>
                        <br>
                        <a href="#" onclick="logout();return false;">Logout</a>
                    <?php
                }
            ?>
        </section>
        
        <section class="main">
                <?php
                    if(isset($_POST['button_search_venue']))
                    {
                        $_SESSION['search_venue'] = $_POST['venue'];
                        
                        //require_once 'Foursquare/Foursquare_Search.php';
                        
                        require_once 'Recommendation/Prepare_Categories.php';
                        require_once 'Recommendation/Prepare_Categories_UserLiked.php';
                        require_once 'Recommendation/Prepare_Categories_FriendLiked.php';
                        require_once 'Recommendation/K-Means.php';
                        require_once 'Recommendation/Nearest_Users.php';
                        require_once 'Recommendation/Top_Users.php';
                        
                        require_once 'Foursquare/Foursquare_Search_Recommendation.php';
                        require_once 'Foursquare/Foursquare_Search.php';
                    }
                ?>
        </section>          
        
        <footer class="footer">
            
        </footer>
    </div>   
</body>
</html>


