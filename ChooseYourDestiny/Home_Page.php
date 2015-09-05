<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Choose Your Destiny</title>

    <!-- Bootstrap -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="bootstrap/js/bootstrap.min.js"></script>
    </head>
    <body style="padding-top: 70px;">
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
        <!-- Fixed navbar -->
        <nav class="navbar navbar-inverse navbar-fixed-top">
            <div class="container-fluid">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="Home_Page.php" style="color: white">Choose Your Destiny</a>
                    <form class="navbar-form navbar-left" role="search" method="POST">
                        <div class="input-group">
                            <input type="text" class="form-control" name="venue" placeholder="E.g. Outback">                        
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                        style="border-radius: 0px;">
                                  <span class="caret"></span>
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu">
                                  <li><a href="#">Food</a></li>
                                  <li><a href="#">Nightlife</a></li>
                                  <li><a href="#">Shopping</a></li>
                                  <li role="separator" class="divider"></li>
                                  <li><a href="#">General</a></li>
                                </ul>
                                <button type="button" type="submit" name="button_search_venue" class="btn btn-default">Search</button>     
                                <?php
                                    if(isset($_POST['button_search_venue']))
                                    {
                                        $_SESSION['search_venue'] = $_POST['venue'];

                                        if($_SESSION['user_mode'] == 'facebook')
                                        {
                                            $user = $_SESSION['user_id'];

                                            require_once 'Recommendation/Prepare_Categories.php';
                                            require_once 'Recommendation/Prepare_Categories_UserLiked.php';
                                            require_once 'Recommendation/Prepare_Categories_FriendLiked.php';
                                            require_once 'Recommendation/K-Means.php';
                                            require_once 'Recommendation/Nearest_Users.php';
                                            require_once 'Recommendation/Top_Users.php';
                                        }
                                        else if($_SESSION['user_mode'] == 'simple')
                                        {
                                            $user = $_SESSION['user_id'];
                                        }

                                        require_once 'Foursquare/Foursquare_Search_Recommendation.php';
                                        require_once 'Foursquare/Foursquare_Search.php';
                                    }
                                ?>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse navbar-right" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        <li><a href="Home_Page.php">Home</a></li>                   
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">My Account <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="#">Saved Places</a></li>
                                <li><a href="#">History</a></li>
                                <?php
                                    if($_SESSION['user_mode'] == 'simple')
                                    {
                                        ?>
                                        <li><a href="SimpleUser/Edit_Profile.php">Edit Profile</a></li>
                                        <?php
                                    }
                                ?>
                                <li role="separator" class="divider"></li>
                                <?php
                                    if($_SESSION['user_mode'] == 'facebook')
                                    {
                                        ?>
                                        <li>
                                        <?php
                                        require_once 'Facebook/FB_Logout.php';
                                        ?>
                                        </li>
                                        <?php
                                    }
                                    else if($_SESSION['user_mode'] == 'simple')
                                    {
                                        ?>
                                        <li><a href="Index.php">Logout</a></li>
                                        <?php
                                    }
                                ?>
                            </ul>                         
                        </li>
                        <li><a href="#">About</a></li>
                    </ul>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>
        <div class="container-fluid" style="padding-top: 100px;">
            <div class="text-center">
                <h1>What are you looking for?</h1>
            </div>
            <div class="row text-center">
                <div class="col-xs-6 col-sm-4"><a>Food</a></div>
                <div class="col-xs-6 col-sm-4"><a>Nightlife</a></div>
                <div class="col-xs-6 col-sm-4"><a>Shopping</a></div>
            </div>
        </div>               
    </body> 
</html>