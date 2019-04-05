<?php
include 'include/config.php';

/*
Defines functions to connect to the Database, retrieve the result and 
return them. 
*/

function getDB()
{
	// connect to database
	$conn= mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);

	
	if(!$conn){
		print "Error- Could not connect to mySQL";
		exit;
	} 
	
	return $conn;
}

function runQuery($db, $query) {

    // takes a reference to the DB and a query and returns the 
    // results of running the query on the database

	$result = mysqli_query($db,$query);
	if (mysqli_num_rows($result) > 0){
	   return $result;
	} else {
		echo ("0 results");
	}
}


/*
Defines functions for users to login or signup.
*/
function LogIn($username, $password){
    // Create a Database Connection
    $db = getDB();
    
    // Check if User exists in Database + Bind Parameters
    $statement = "SELECT * FROM Users WHERE UserName = ?";
    $statement = mysqli_prepare($db, $statement);
    mysqli_stmt_bind_param($statement,'s',$username);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);

    // If User exists, then password will be checked. 
    // Otherwise, Password will not be checked. 
    // Note: Password's are hashed for security reasons. 
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_array($result)){
            if (password_verify($password,$row['Password'])){
                echo "Login Successful";
                echo $row["UserID"];
                $_SESSION['userID'] = $row["UserID"];
                header("Location: dashboard.php");
            } else {
                echo "Incorrect Password";
            }
        }
    } else {
       echo "Account Does not Exist";
    }
}

function SignUp($username, $password, $email){
    # CREATE DB CONNECTION
    $db = getDB();
    
    # CHECK IF USER ALREADY EXISTS IN DATABASE
    $statement = "SELECT UserName, UserID FROM Users WHERE UserName = ?";
    $statement = mysqli_prepare($db, $statement);
    mysqli_stmt_bind_param($statement,'s',$username);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);

    
    // IF USER DOES NOT EXIST, A NEW ACCOUNT WILL BE CREATED.
    # NOTE: PASSWORDS ARE HASHED FOR SECURITY REASONS
    if (mysqli_num_rows($result) > 0) {
        echo "User already exists";
        $success = -1;
    } else {
        $password = password_hash($password,PASSWORD_DEFAULT);
        $statement = "INSERT INTO Users (UserName,Password, Email) VALUES ('$username', '$password', '$email')";
        
        if (mysqli_query($db,$statement)){
            # NEW USER CREATED 
            $success = 1;   
        } else {
            # ERROR IN CREATING NEW USER
            $success = -2;
        };
    }

    # IF USER CREATION SUCCESSFUL, GO TO DASHBOARD
    if ($success ==1){
        $statement = "SELECT UserName, UserID FROM Users WHERE UserName = ?";
        $statement = mysqli_prepare($db, $statement);
        mysqli_stmt_bind_param($statement,'s',$username);
        mysqli_stmt_execute($statement);
        $result = mysqli_stmt_get_result($statement);
        while($row = mysqli_fetch_array($result)){
            $_SESSION["userID"] = $row["UserID"];
            header("Location: dashboard.php");
        }
    } else if ($success == -1){
        # SIGN UP UNSUCCESSFUL. USER ALREADY EXISTS.
        $a = "signup.php?msg=exists";
        header ("Location: " . $a);
    } else if ($success == -2){
        # SIGN UP UNSUCCESSFUL. ERROR.
        $a = "signup.php?msg=failed";
        header ("Location: " . $a);
    }
    
}


/*
    Defines functions for user dashboard
*/
function deletePost($postArray){
    # Make Database Connection
    $db = getDB();

    # Delete checked posts
    $statement = "DELETE FROM Post WHERE PostID IN ($postArray) ";
    if (mysqli_query($db,$statement)){
        # SUCCESSFUL DELETE
        header ("Location: dashboard.php");
    } else {
        # UNSUCCESFUL DELETE
        header ("Location: dashboard.php");
    };
}

function getPosts($userID){
    # ESTABLISH DATABASE CONNECTION
    $db = getDB();

    # RETRIEVE POSTS THAT BELONG TO THE USER
    $statement = "SELECT p.PostID, p.Title, p.PostDate, c.Type, SUM(r.Number) as 'Rank' FROM Rank r 
    INNER JOIN Post p 
    INNER JOIN Users u 
    INNER JOIN Category c 
    ON u.UserID = p.UserID 
    AND p.CategoryID = c.CategoryID 
    AND p.PostID = r.PostID 
    WHERE u.UserID = ? 
    GROUP BY p.PostID ORDER BY Rank DESC";
    $statement = mysqli_prepare($db, $statement);
    mysqli_stmt_bind_param($statement,'i',$userID);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $numberofrows = mysqli_num_rows($result);

    return $result;
}

function getCategories(){
    # ESTABLISH DATABASE CONNECTION
    $db = getDB();

    # GET CATEGORIES FROM DB
    $statement = "SELECT * FROM Category";
    $statement = mysqli_prepare($db, $statement);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    
    return $result;
}

function filter($categoryID, $userID){
    # ESTABLISH DATABASE CONNECTION
    $db = getDB();

    # SELECT POSTS THAT BELONG TO THE CATEGORY
    $statement = "SELECT p.PostID, p.Title, p.PostDate, c.Type, SUM(r.Number) as 'Rank' FROM Rank r 
    INNER JOIN Post p 
    INNER JOIN Users u 
    INNER JOIN Category c 
    ON u.UserID = p.UserID 
    AND p.CategoryID = c.CategoryID 
    AND p.PostID = r.PostID 
    WHERE u.UserID = ? AND c.CategoryID = ? 
    GROUP BY p.PostID ORDER BY Rank DESC";
    $statement = mysqli_prepare($db, $statement);
    mysqli_stmt_bind_param($statement,'ii',$userID,$categoryID);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $numberofrows = mysqli_num_rows($result);

    return $result;
}


/* for index.php */

function getAll(){
    # establish database connection
    $db = getDB();

    # get SQL query + Bind Parameters
    $statement = "SELECT u.UserID, p.PostID, u.UserName, p.PostID, p.Title, DATE_FORMAT(p.PostDate, '%m/%d/%y') AS 'P', c.Type, SUM(r.Number) as 'RANK' 
    FROM Rank r 
    INNER JOIN Post p INNER JOIN Users u 
    INNER JOIN Category c ON u.UserID = p.UserID 
    AND p.CategoryID = c.CategoryID AND p.PostID = r.PostID 
    GROUP BY p.PostID ORDER BY RANK DESC";
    $statement = mysqli_prepare($db, $statement);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $numberofrows = mysqli_num_rows($result);

    return $result;
}

/* 
    These are SQL functions for filling up data for a single post page
*/

function fillPost($postID){
    # establish database connection
    $db = getDB();

    # get SQL query + Bind Parameters
    $statement = "SELECT u.UserName, p.PostID, p.Content, p.Title, DATE_FORMAT(p.PostDate, '%m/%d/%y') AS 'P', c.Type, SUM(r.Number) as 'RANK' 
    FROM Rank r 
    INNER JOIN Post p INNER JOIN Users u 
    INNER JOIN Category c ON u.UserID = p.UserID 
    AND p.CategoryID = c.CategoryID AND p.PostID = r.PostID  WHERE p.PostID = ?
    GROUP BY p.PostID";
    $statement = mysqli_prepare($db, $statement);
    mysqli_stmt_bind_param($statement,'i',$postID);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $numberofrows = mysqli_num_rows($result);
   
    return $result;
}

function getComments($postID){
    # establish database connection
    $db = getDB();

    # get SQL query + Bind Parameters
    $statement = "SELECT c.Comment, c.UserID, DATE_FORMAT(c.CommentDate, '%m/%d/%y') AS 'P', u.UserName 
    FROM Comments c INNER JOIN Post p INNER JOIN Users u 
    ON p.PostID = c.PostID AND u.UserID = c.UserID WHERE p.PostID = ? 
    ORDER BY c.CommentDate DESC";
    $statement = mysqli_prepare($db, $statement);
    mysqli_stmt_bind_param($statement,'i',$postID);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $numberofrows = mysqli_num_rows($result);

    return $result;
}

function getRank($postID){
    # establish database connection
    $db = getDB();

    # get SQL query + Bind Parameters
    $statement = "SELECT u.UserID, p.PostID, SUM(r.Number) as 'RANK' FROM Rank r 
    INNER JOIN Post p INNER JOIN Users u ON p.PostID = r.PostID AND u.UserID = p.UserID WHERE p.PostID = ? GROUP BY p.PostID";
    $statement = mysqli_prepare($db, $statement);
    mysqli_stmt_bind_param($statement,'i',$postID);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $numberofrows = mysqli_num_rows($result);

    return $result;
}

function newComment($postID,$userID,$comment){
      # establish database connection
      $db = getDB();

      # INSERT NEW COMMENT into COMMENTS Table + Bind Parameters
      $statement = "INSERT INTO Comments (PostID, UserID, Comment) VALUES ($postID,$userID,'$comment')";
  
      # IF INSERT SUCCESSFUL, GO BACK TO POST. ELSE, SIGN UP BECAUSE NOT LOGGED IN. 
      if (mysqli_query($db,$statement)){ 
            $string = "post.php?id=" .$postID; 
            header("Location: ". $string);
    } else {
            header("Location: signup.php");
    };
}

/*
    Create New Post
*/
function createNewPost($userID, $title, $categoryID, $text){
    
    # ESTABLISH DATABASE CONNECTION
    $db = getDB();

    # SQL STATEMENT FOR INSERT
    $statement = "INSERT INTO Post (UserID, Title, CategoryID, Content) VALUES ($userID, '$title', $categoryID, '$text')";

       # IF INSERT SUCCESSFUL, GO BACK TO DASHBOARD. ELSE, GO BACK TO CREATE NEW POST. 
        if (!mysqli_query($db,$statement)){
            // fail 
            echo "Create Post: FAILED";
            header ("Location: createPost.php");
        } else {
            $result = getPostID($userID, $title);
            if (mysqli_num_rows($result)>0){
                while ($row = mysqli_fetch_array($result)){
                $postID = $row['PostID'];
                echo "this is post ID: " . $postID;
                $statement = "INSERT INTO Rank (UserID, PostID, Number) VALUES ($userID,$postID,1)";
            }   
            mysqli_query($db,$statement);
            }

            echo "Create Post: SUCESS";
            header ("Location: dashboard.php");
        }


}

# THIS FUNCTION IS JSUT HELPING THE CREATE POST FUNCTION LOL
function getPostID($userID, $title){   
    # ESTABLISH DATABASE CONNECTION 
    $db = getDB();

    # SQL STATEMENT TO GET POST ID
    $statement = "SELECT * FROM Post p INNER JOIN Users u WHERE u.UserID = p.UserID AND p.Title = ?";
    $statement = mysqli_prepare($db, $statement);
    mysqli_stmt_bind_param($statement,'s',$title);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $numberofrows = mysqli_num_rows($result);

    return $result;
}



/*
    Pagination Functions
*/

function pagination($result_per_page){
    $db = getDB();

    if (isset($_GET["page"])) { $page  = $_GET["page"]; } else { $page=1; }; 
    $start_from = ($page-1) * $results_per_page;
    $statement = "SELECT p.PostID, p.Title, p.PostDate, c.Type, SUM(r.Number) as 'Rank' FROM Rank r 
    INNER JOIN Post p 
    INNER JOIN Users u 
    INNER JOIN Category c 
    ON u.UserID = p.UserID 
    AND p.CategoryID = c.CategoryID 
    AND p.PostID = r.PostID 
    WHERE u.UserID = ? 
    GROUP BY p.PostID ORDER BY Rank DESC LIMIT $start_from, $results_per_page";

    $statement = mysqli_prepare($db, $statement);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $numberofrows = mysqli_num_rows($result);

    return $result;
}



/*
    Function for Editing Posts
*/
function editPost($PostID, $title, $categoryID, $text){
    # MAKE DATABASE CONNECTION
    $db = getDB();

    $statement = "UPDATE Post SET Title = '$title',CategoryID = $categoryID, Content = '$text' WHERE PostID = $PostID";

    # IF INSERT SUCCESSFUL, GO BACK TO DASHBOARD. ELSE, GO BACK TO CREATE NEW POST. 
    if (!mysqli_query($db,$statement)){
        # EDIT POST WAS NOT SUCCESSFUL 
        $a = "editPost.php?id=".$PostID;
        header ("Location: ".$a);
    } else {
        # EDIT POST WAS SUCCESSFUL 
        $a = "editPost.php?id=".$PostID;
        header ("Location: ".$a);
    }
}


function getPostData($postID){    
    # MAKE DATABASE CONNECTION
    $db = getDB();

    # SQL STATEMENT TO GET POST
    $statement = "SELECT Title,CategoryID,Content FROM Post WHERE PostID = ?";
    $statement = mysqli_prepare($db, $statement);
    mysqli_stmt_bind_param($statement,'d',$postID);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $numberofrows = mysqli_num_rows($result);

    return $result;
}
?>




