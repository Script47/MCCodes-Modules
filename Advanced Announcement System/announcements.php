<html>
    <head>
        <title>Advanced Announcement System</title>
        <style>
            .table {
                border-collapse: collapse;
            }
            .input {
                height: 30px;
                width: 150px;
                color: black;
                background-color: #CCCCCC;
                border: none;
                border-radius: 5px;         
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                outline: none;
                padding-left: 2px;
                overflow: hidden;
            }
            button {
                height: auto;
                width: auto;
                background-color: #CCCCCC;
                outline: none;
                border: none;
                border-radius: 5px;                
            }
            button:active {
                background-color: #A9A9A9;
            }
            select {
                height: 30px;
                background-color: #CCCCCC;
                border: none;
                border-radius: 5px;         
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                outline: none;
            }
            option {
                background-color: #CCCCCC;               
            }
            textarea {
                color: black;
                background-color: #CCCCCC;
                border: none;
                border-radius: 5px;         
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                outline: none;     
                resize: none;
                padding-left: 2px;
                overflow: auto;                
            }
            #link {
                color: blue;
            }
            #link:hover {
                color: red;
            }
            #filterBox {
                height: 50px;
                text-align: left;
            }
        </style>
    </head>
</html>

<?php
/*
 * MCCodes V2 Modification 
 * Advanced Announcement System
 * Developer: Script47
 * Price: Free
 * Contact Via MWG: http://www.makewebgames.com/member.php/69670-Script47
 * Contact Via Twitter: https://twitter.com/Script47
 */

include 'globals.php';

echo '<h4>Advanced Announcement System</h4>';

$urgencyColours = array(
    1 => "<font color='gray'>Low</font>",
    2 => "<font color='orange'>Medium</font>",
    3 => "<font color='red'>High</font>",    
);

$userID = $ir['userid'];
$newAnnouncements = $ir['new_announcements'];

if($ir['new_announcements'] > 0) {
	$readAnnouncements = $db->query("UPDATE `users` SET new_announcements=0 WHERE userid=$userID");
}

if($ir['user_level'] == 2) {
    echo '<a  id="link" href="announcements.php?newAnnouncement=true">New Announcement</a> - ';
}

if($ir['userid'] == 1) {
	echo '<a id="link" href="announcements.php?truncate=true">Truncate Announcements</a>';
}

if(isset($_GET['newAnnouncement'])) {
    if($ir['user_level'] != 2) {
        echo "<font color='red'>You don't have permission to view this.</font>";
        exit(header("Refresh:1; URL=announcements.php"));
    }
    echo '<br/>';
    echo '<br/>';    
    echo '<form method="post">
            <input class="input" type="text" name="Title" placeholder="Announcement Title" title="Announcement Title" spellcheck="true" required autofocus>
            <br/>
            <textarea rows="12" cols="45" name="Announement" title="Announcement" placeholder="Main announcement details" spellcheck="true" required></textarea>
            <br/>
            <select name="Urgency">
                <option value="1">Low</option>
                <option value="2">Medium</option>
                <option value="3">High</option>            
            </select>
            <br/>
            <button name="newAnnouncement" type="submit">Create Announcement</button>
         </form>';    
    
    echo '<a id="link" href="announcements.php?hide=true">Hide</a><br/>';
    
    if(isset($_POST['newAnnouncement'])) {
        if($ir['user_level'] != 2) {
            echo "<font color='red'>You don't have permission to view this.</font>";
            exit(header("Refresh:1; URL=announcements.php"));
        } else if(!isset($_POST['Title']) || empty($_POST['Title']) || !isset($_POST['Announement']) || empty($_POST['Announement'])) {
            echo '<font color="red">One or more of the required fields are missing.</font>';
            exit();
        } else {
            $title = htmlspecialchars(trim($db->escape($_POST['Title'])));
            $announcement = htmlspecialchars(trim($db->escape($_POST['Announement'])));
            $urgency = htmlspecialchars(trim($_POST['Urgency']));
            $announcer = $ir['username'];
            
            $insertAnnounement = $db->query("INSERT INTO `announcements` (Title, Announcement, Announcer, Urgency) VALUES ('$title', '$announcement', '$announcer', '$urgency')");
            
            if($insertAnnounement) {
                echo '<font color="green">Announcement created.</font>';
                $updateUsers = $db->query("UPDATE `users` SET new_announcements=new_announcements+1");
                exit(header("Refresh:1; URL=announcements.php"));
            } else {
                echo '<font color="red">Announcement could not be created.</font>';
                exit(header("Refresh:1; URL=announcements.php"));               
            }
        }
    }
}

if(isset($_GET['truncate'])) {
	echo '<br/>';
	if($ir['userid'] != 1){
		echo "<font color='red'>You don't have permission to view this.</font>";
        exit(header("Refresh:1; URL=announcements.php"));			
	} else {
		$truncateTable = $db->query("TRUNCATE TABLE `announcements`");
		
		if($truncateTable) {
			echo '<font color="green">Table truncated.</font>';
			$updateNewAnnouncements = $db->query("UPDATE `users` SET new_announcements=0");
            exit(header("Refresh:1; URL=announcements.php"));					
		} else {
			echo '<font color="red">Table could not be truncated.<br/></font>';
            exit(header("Refresh:1; URL=announcements.php")); 		
		}
	}
}

echo '<br/>';
echo '<br/>';

echo '<div id="filterBox"><form method="post">
        <select name="filterOptions">
            <option value="1">Low</option>
            <option value="2">Medium</option>
            <option value="3">High</option>            
        </select>
        <button name="filter" type="submit">Filter</button></form></div>';

if(isset($_POST['filter'])) {
    $filterOptions = htmlspecialchars(trim($_POST['filterOptions']));
    $selectAnnouncements = $db->query("SELECT * FROM `announcements` ORDER BY `Urgency` = $filterOptions DESC");
} else { 
    $selectAnnouncements = $db->query("SELECT * FROM `announcements` ORDER BY `Date` DESC");
}

echo '<table class="table" border="1" cellpadding="10" align="center">';

echo '<th>Announcement Number</th>';
echo '<th>Title</th>';
echo '<th>Announement</th>';
echo '<th>Announcer</th>';
echo '<th>Urgency</th>';
echo '<th>Announced On</th>';

while ($row = $db->fetch_row($selectAnnouncements)) {
	if($newAnnouncements > 0) {
		$newAnnouncements--;
		$new = "<strong>New!</strong>";
	} else {
		$new = "";
	}
    echo '<tr><td>';
    echo $new."<br/>".$row['ID'];
    echo '<td>';
    echo $row['Title'];
    echo '<td>';
    echo $row['Announcement'];
    echo '<td>'; 
    echo $row['Announcer'];
    echo '<td>';
    echo $urgencyColours[$row['Urgency']];
    echo '<td>';
    echo date('d/m/Y g:i:s A',  strtotime($row['Date']));
    echo '</td></tr>';
}
echo '</table>';

$h->endPage();
?>