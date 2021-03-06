<?php
    include("../header.php");

    if (!isset($_GET['post_id']))
        exit('Illegal call to this page.');

    $post_id = $_GET['post_id'];
    $post_id = addslashes($post_id);
    $user_id = $_SESSION['user_id'];

    $query = "SELECT * FROM post WHERE post_id = '$post_id'";
    $result = $con->query($query) or die($query . '<br/>' . $con->error);

    if ($result = $result->fetch_array(MYSQLI_BOTH))
    {
        $board_id = $result['board_id'];
        $post_name = $result['post_name'];
        $post_name = htmlspecialchars($post_name);
        $content = $result['content'];
        $content = htmlspecialchars($content);
        $img = $result['img'];
        $img = htmlspecialchars($img);
        $create_time = $result['create_time'];
        $post_user_id = $result['user_id'];
    }
    else
        exit("No board found for Pid=$post_id.");

    $permission = getPermission($user_id, $board_id);

    function printReply($author_id, $create_time, $content, $img, $user_id, $permission, $reply_id = 0)
    {
        global $post_user_id;
        static $count = 0;
        
        $author_name = getUsername($author_id);
        $author_name = htmlspecialchars($author_name);
        $content = htmlspecialchars($content);
        $img = htmlspecialchars($img);
        if($img == NULL)
            $img = "";
        if ($count == 0)
            $id = "Host &nbsp;";
        else if ($count == 1)
            $id = "$count<sup>st</sup> floor &nbsp;";
        else if ($count == 2)
            $id = "$count<sup>nd</sup> floor &nbsp;";
        else if ($count == 3)
            $id = "$count<sup>rd</sup> floor &nbsp;";
        else
            $id = "$count<sup>th</sup> floor &nbsp;";

        if ($count and ($author_id == $post_user_id))
            $prefix = "[Host] ";
        else $prefix = "";
        if ($reply_id and (($permission >= MODERATOR) or ($user_id == $author_id)) )
            $control = "<button style=\"float:right\" class=\"btn btn-outline-light btn-sm\" onClick=\"confirmDelete($reply_id)\">Delete</button>";
        else $control = "";
        echo <<< EOT
        <div class="lead col-lg-12">
            $id $prefix$author_name &nbsp;&nbsp;&nbsp;&nbsp; $create_time
            $control
        </div>
        <div class="col-lg-12">
            $content
        </div>
        <div class="col-lg-12">
            <img src="$img" alt="$img" height=300px width=300px style="display:block; margin:auto;" />
            <br>
        </div>
EOT;
        $count++;
    }

    function showReply($post_id, $user_id, $permission)
    {
        global $con; // very important, it will cause a fatal error without this line.

        $query = "SELECT * FROM post_reply WHERE post_id = '$post_id' ORDER BY create_time";
        $result = $con->query($query) or die($query . '<br/>' . $con->error);
        while ($row = $result->fetch_array(MYSQLI_BOTH))
        {
            $content = htmlspecialchars($row['content']);
            echo("<h2></h2>\n");
            printReply($row['user_id'], $row['create_time'], $content, $row['img'], $user_id, $permission, $row['reply_id']);
        }
    }

    function showReplyInput($post_id, $permission)
    {
        if ($permission >= USER)
        echo <<< EOT
        <br><br>
        <div>
            <h2>Reply</h2>
        </div>
        <form method="post" action="add_reply.php" onSubmit="return inputCheck()">
            <input type="hidden" name="post_id" value=$post_id />
            <div>
                <textarea class="form-control input-block" id="content" name="content" rows=6></textarea>
            </div><br>
            <div>
                <textarea class="form-control input-block" id="img" name="img" rows=1></textarea>
            </div><br>
            <div>
                <input class="btn  btn-outline-light btn-sm" type="submit" name="submit" value="Post!">
            </div>
        </form>
EOT;
    }
?>

<!--<!DOCTYPE html>
<html>
	<head>
		<title>NTUST-ptt - <?php echo($post_name); ?></title>
		<link href="/bootstrap-4.1.3-dist/css/bootstrap.min.css" />
		<link href="/css/style.css" rel="stylesheet" />
        <script src="/bootstrap-4.1.3-dist/js/bootstrap.min.js"></script>
	</head>
	<body>
		<header class="masthead">
			<div class="container">
				<div class="masthead-logo">
					NTUST-ptt
				</div>
				<nav class="masthead-nav">
					<a href="/ptt/home.php">Home</a>
					<?php showUserManagement($_SESSION['default_permission']); ?>
					<a href="../user/user_info.php"><?php showUser(); ?></a>
					<a href="../logout.php">Log out</a>
				</nav>
			</div>
		</header>-->
		
		<div class="container markdown-body">
            <div class="row">
                <div class="col-lg-12">
    			    <h1 class="page-title"><?php echo($post_name); ?></h1>
                </div>
                <div class="col-lg-12">
    			    <?php printReply($post_user_id, $create_time, $content, $img, $user_id, $permission); ?>
                </div>
                <div class="col-lg-12">
    			    <?php showReply($post_id, $user_id, $permission); ?>
                </div>
                <div class="col-lg-12">
                    <br>
                    <?php goBack();?>
                </div>
                <div class="col-lg-12">
                    <?php showReplyInput($post_id, $permission); ?>
                </div>
            </div>
		</div>
	</body>
</html>

<script>
    function confirmDelete(reply_id)
    {
        if (confirm("Do you really want to delete this reply?"))
            window.location.href = "del_reply.php?reply_id=" + reply_id;
    }

    function inputCheck()
    {
        content = document.getElementById("content");
        if (!content.value)
        {
            alert("Content should not be empty.");
            content.focus();
            return false;
        }
        return true;
    }
</script>