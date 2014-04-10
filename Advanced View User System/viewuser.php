<?php
/*
 * MCCodes V2 Modification
 * View User Advanced System
 * This file still contains MCCodes original code, however it was cleaned up and new features have been added.
 * Cleaned By: Script47, Fernando Marquardt (Shaddy at MWG)
 * New Features By: Script47
 * Optimized By: KyleMassacre, Fernando Marquardt (Shaddy at MWG)
 * Price: Free
 * Day Cron: $db->query("UPDATE `user` SET daily_rating=1");
 * Contact Via MWG: http://www.makewebgames.com/member.php/69670-Script47
 * Contact Via Twitter: https://twitter.com/Script47
 */

/********** CONFIGURATION **********/
$vu_config = array(
	'date.format' => 'F j, Y g:i:s a'
);
/******** CONFIGURATION END ********/

/************ FUNCTIONS ************/

/**
 * Calculate the time difference between the given date and time and now and returns an human friendly description of the time lapsed.
 * 
 * @param string $datetime The date and time to calculate the time lapsed.
 * @return string Returns an human friendly description of the time lapsed.
 */
function time_lapsed($datetime) {
    $time_diff = time() - $datetime;
    
    $tokens = array(
    	86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );
    
    foreach ($tokens as $unit => $description) {
        if ($time_diff < $unit) {
            continue;
        }
        
        $result = floor($time_diff / $unit);
        
        return $result . ' ' . $description . (($result > 1) ? 's' : '');
    }
    
    return '0 second';
}

/********** FUNCTIONS END **********/

include 'globals.php';

$_GET['u'] = isset($_GET['u']) ? abs((int) $_GET['u']) : null;

if(!$_GET['u'] > 0) {
    echo '<p>Invalid use of file</p>';
    $h->endpage();
    die();
}

echo <<<CSS
<style>
.comment-form {
    display: block;
    margin: 20px 0;
}

.comment-form fieldset {
    border: 0;
    margin: 0 0 10px 0;
    padding: 0 8px 0 0;
}

.comment-field {
    height: auto;
    width: 100%;
    border: 1px solid black;
    border-radius: 5px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    box-shadow: inset 0 5px 5px rgba(0,0,0,.2);
    -webkit-box-shadow: 0 0 1px #000;
    -moz-box-shadow: 0 0 1px #000;
    padding: 3px;
    outline: 0;
}
        
.profile-table {
    line-height: 1.5em;
}

.donator-name {
    color: #ca3c3c;
    font-weight: bold;
}

.user-status-online, .user-status-offline {
    border-radius: 4px;
    color: #fff;
    font-weight: bold;
    padding: 2px 5px;
    text-shadow: 1px 1px 1px rgba(0,0,0,.2);
}
        
.user-status-online {
    background: #1cb841;
}

.user-status-offline {
    background: #ca3c3c;
}
</style>
CSS;

$sql = <<<SQL
SELECT
    u.*,us.*,c.*,h.*,g.*,f.*
FROM
    users u
    LEFT JOIN userstats us ON u.userid=us.userid
    LEFT JOIN cities c ON u.location=c.cityid
    LEFT JOIN houses h ON u.maxwill=h.hWILL
    LEFT JOIN gangs g ON g.gangID=u.gang
    LEFT JOIN fedjail f ON f.fed_userid=u.userid
WHERE
    u.userid={$_GET['u']}
SQL;

$q = $db->query($sql);

if ($db->num_rows($q) == 0) {
    echo '<p>Sorry, we could not find a user with that ID, check your source.</p>';
    $h->endpage();
    die();
}

$r = $db->fetch_row($q);

switch ($r['user_level']) {
	case 1:
	    $user_level = 'Member'; break;
	case 2:
	    $user_level = 'Admin'; break;
	case 3:
	    $user_level = 'Secretary'; break;
	case 4:
	    $user_level = 'NPC'; break;
	default:
	    $user_level = 'Assistant';
}

$signup_date = date($vu_config['date.format'], $r['signedup']);

if ($r['laston'] > 0) {
    $last_action = time_lapsed($r['laston']) . ' ago';
} else {
    $last_action = 'Never';
}

if($r['last_login'] > 0) {
    $last_login = time_lapsed($r['last_login']) . ' ago';
} else {
    $last_login = '--';
}

if ($r['donatordays']) {
    $username_display = "<span class=\"donator-name\">{$r['username']}</span>";
    $donator_title = "Donator: {$r['donatordays']} Days Left";
    $donator_sign = "<img src=\"donator.gif\" alt=\"{$donator_title}\" title=\"{$donator_title}\" />";
} else {
    $username_display = $r['username'];
    $donator_sign = '';
}

if($r['laston'] >= (time() - 15 * 60)) {
    $user_status = '<span class="user-status-online">Online</span>';
} else {
    $user_status = '<span class="user-status-offline">Offline</span>';
}

echo "<h3>Profile for {$r['username']}</h3>

<table width=\"100%\" cellspacing=\"1\" class=\"table profile-table\">
    <tr><th>General Info</th><th>Financial Info</th><th>Display Pic</th></tr>
    <tr><td>
    <b>Name:</b> {$username_display} [{$r['userid']}] {$donator_sign}<br />
    <b>User Level:</b> {$user_level}<br />
    <b>Duties:</b> {$r['duties']}<br />
    <b>Gender:</b> {$r['gender']}<br />
    <b>Signed Up:</b> {$signup_date}<br />
    <b>Last Action:</b> {$last_action}<br />
    <b>Last Login:</b> {$last_login}<br />
    <b>Online:</b> {$user_status}<br />
    <b>Days Old:</b> {$r['daysold']}<br />
    <b>Rating:</b> ";

if($r['rating'] == 0) {
	echo '<font color="black">'.$r['rating'].'</font>';
} else if($r['rating'] > 0) {
	echo '<font color="green">'.$r['rating'].'</font>';
} else {
	echo '<font color="red">'.$r['rating'].'</font>';
}

$rateUp = htmlspecialchars(trim(isset($_GET['rateUp'])));
$rateDown = htmlspecialchars(trim(isset($_GET['rateDown'])));

echo " <a href='viewuser.php?u={$_GET['u']}&rateUp=true'><img src='http://www.famfamfam.com/lab/icons/silk/icons/arrow_up.png' alt='Rate Up' title='Rate Up'></a>";
echo "<a href='viewuser.php?u={$_GET['u']}&rateDown=true'><img src='http://www.famfamfam.com/lab/icons/silk/icons/arrow_down.png' alt='Rate Down' title='Rate Down'></a>";

if($rateUp) {
    if($ir['daily_rating'] <= 0) {
        echo '<font color="red">You have already used your rating for today.</font>';
        exit(header("Refresh:2; URL=viewuser.php?u=".$_GET["u"]));
    } else if($_GET["u"] == $userid) {
        echo "<font color='red'>You can't up rate yourself.</font>";
        exit(header("Refresh:2; URL=viewuser.php?u=".$_GET["u"]));
    } else {
        event_add($id, "<font color='green'><a href='viewuser.php?u={$ir['userid']}'><font color='blue'>[{$ir['userid']}]{$ir['username']}</font></a> rated you up!</font>");
        $db->query("UPDATE `users` SET rating=rating+1 WHERE userid=".$_GET["u"]);
        $updateUsersDailyRating = $db->query("UPDATE `users` SET daily_rating=0 WHERE userid=$userid");
        exit(header("Location: viewuser.php?u=".$_GET["u"]));
    }
} else if($rateDown) {
    if($ir['daily_rating'] <= 0) {
        echo '<font color="red">You have already used your rating for today.</font>';
        exit(header("Refresh:2; URL=viewuser.php?u=".$_GET["u"]));
    } else if($id == $userid) {
        echo "<font color='red'>You can't down rate yourself.</font>";
        exit(header("Refresh:2; URL=viewuser.php?u=".$_GET["u"]));
    } else {
        event_add($_GET["u"], "<font color='red'><a href='viewuser.php?u={$ir['userid']}'><font color='blue'>[{$ir['userid']}]{$ir['username']}</font></a> down rated you!</font>");
        $db->query("UPDATE `users` SET rating=rating-1 WHERE userid=$".$_GET["u"]);
        $updateUsersDailyRating = $db->query("UPDATE `users` SET daily_rating=0 WHERE userid=$userid");
        exit(header("Location: viewuser.php?u=".$_GET["u"]));
    }
}

echo "<br/>
<b>Location:</b> {$r['cityname']}</td><td>
Money: ".money_formatter($r['money'])."<br />
Crystals: {$r['crystals']}<br />
Property: {$r['hNAME']}<br />
Referals: ";
$rr = $db->query("SELECT refID FROM referals WHERE refREFER={$r['userid']}");
echo $db->num_rows($rr);
echo "<br />
Friends: {$r['friend_count']}<br />
Enemies: {$r['enemy_count']}
</td> <td>";
if($r['display_pic']) {
    echo "<img src='{$r['display_pic']}' width='150' height='150' alt='User Display Pic' title='User Display Pic' />";
} else {
    echo "This user has no display pic!";
}
$sh = ($ir['user_level'] > 1) ? "Staff Info" : "&nbsp;";
echo "</td></tr>
<tr style='background:gray'><th>Physical Info</th><th>Links</th><th>$sh</th></tr>
<tr><td>Level: {$r['level']}<br />
Health: {$r['hp']}/{$r['maxhp']}<br />
Gang: ";
if($r['gang']) {
    echo "<a href='gangs.php?action=view&ID={$r['gang']}'>{$r['gangNAME']}</a>";
} else {
    echo "N/A";
}
if($r['fedjail']) {
    echo "<br /><b><font color=red>In federal jail for {$r['fed_days']} day(s).<br />
    {$r['fed_reason']}</font>";
}
if($r['hospital']) {
    echo "<br /><b><font color=red>In hospital for {$r['hospital']} minutes.<br />{$r['hospreason']}</font></b>";
}
if($r['jail']) {
    echo "<br /><b><font color=red>In jail for {$r['jail']} minutes.<br />{$r['jail_reason']}</font></b>";
}

echo "</td><td>[<a href='mailbox.php?action=compose&ID={$r['userid']}'>Send Mail</a>]<br /><br /> [<a href='sendcash.php?ID={$r['userid']}'>Send Cash</a>]<br /><br />";

if($set['sendcrys_on']) {
    echo "[<a href='sendcrys.php?ID={$r['userid']}'>Send Crystals</a>]<br /><br />";
}
if($set['sendbank_on']) {
    if($ir['bankmoney'] >= 0 && $r['bankmoney'] >= 0) {
        echo "[<a href='sendbank.php?ID={$r['userid']}'>Bank Xfer</a>]<br /><br />";
}
    if($ir['cybermoney'] >= 0 && $r['cybermoney'] >= 0) {
        echo "[<a href='sendcyber.php?ID={$r['userid']}'>CyberBank Xfer</a>]<br /><br />";
    }
}
echo "[<a href='attack.php?ID={$r['userid']}'>Attack</a>]<br /><br /> [<a href='contactlist.php?action=add&ID={$r['userid']}'>Add Contact</a>]";

if($ir['user_level'] == 2 || $ir['user_level'] == 3 || $ir['user_level'] == 5) {
    echo "<br /><br />
    [<a href='jailuser.php?userid={$r['userid']}'>Jail</a>]<br /><br />
    [<a href='mailban.php?userid={$r['userid']}'>MailBan</a>]";
}
if($ir['donatordays'] > 0) {
    echo "<br /><br />
    [<a href='friendslist.php?action=add&ID={$r['userid']}'>Add Friends</a>]<br /><br />
    [<a href='blacklist.php?action=add&ID={$r['userid']}'>Add Enemies</a>]<br />";
}
echo "</td><td>";
if($ir['user_level'] == 2 || $ir['user_level'] == 3 || $ir['user_level'] == 5) {
    $r['lastiph']=@gethostbyaddr($r['lastip']);
    $r['lastiph']=checkblank($r['lastiph']);
    $r['lastip_loginh']=@gethostbyaddr($r['lastip_login']);
    $r['lastip_loginh']=checkblank($r['lastip_loginh']);
    $r['lastip_signuph']=@gethostbyaddr($r['lastip_signup']);
    $r['lastip_signuph']=checkblank($r['lastip_signuph']);
    echo "<h3>Internet Info</h3><table width='100%' border='0' cellspacing='1' class='table'>
    <tr><td></td><td class='h'>IP</td><td class='h'>Hostname</td></tr>
    <tr><td class='h'>Last Hit</td><td>$r[lastip]</td><td>$r[lastiph]</td></tr>
    <tr><td class='h'>Last Login</td><td>$r[lastip_login]</td><td>$r[lastip_loginh]</td></tr>
    <tr><td class='h'>Signup</td><td>$r[lastip_signup]</td><td>$r[lastip_signuph]</td></tr></table>";
    echo "<form action='staffnotes.php' method='post'>
    Staff Notes: <br />
    <textarea rows=7 cols=40 name='staffnotes'>{$r['staffnotes']}</textarea>
    <br /><input type='hidden' name='ID' value='{$_GET['u']}' />
    <input type='submit' value='Change' /></form>";
} else {
    echo "&nbsp;";
}
    echo "</tr></table>";

echo <<<COMMENT_FORM
<form method="post" class="comment-form">
    <fieldset>
        <input class="comment-field" name="comment" placeholder="Your comment" title="Your Comment" spellcheck="true" required />
    </fieldset>
    <input type="submit" name="postComment" value="Comment" />
</form>
COMMENT_FORM;

if(isset($_POST['postComment'])) {
    if(!isset($_POST['comment']) || empty($_POST['comment'])) {
        echo '<font color="red">Required field is empty!</font>';
        exit();
    } else {
        $comment = htmlspecialchars(trim($db->escape($_POST['comment'])));
        $username = $ir['username'];

        $insertComment = $db->query("INSERT INTO `comments` (Comment, SendTo, SentFrom) VALUES ('".$comment."', ".$_GET["u"].", '.".$username."')");

        if($insertComment) {
            event_add($_GET["u"], "<a href='viewuser.php?u=".$ir['userid']."'><font color='blue'>[".$ir['userid']."]".$ir['username']."</font></a> commented on your profile! Click <a href='viewuser.php?u=".$ir['userid']."#comments'><font color='blue'>here</font></a> to check it.");
            exit(header("Location: viewuser.php?u=".$_GET["u"]));
        } else {
            echo '<font color="red">Could not create comment.</font>';
            exit(header("Refresh:2; URL=viewuser.php?u=".$_GET["u"]));
        }
    }
}

echo '<table id="comments" class="table" cellpadding="10" align="center">';

echo '<th>Comment</th>';
echo '<th>Sent From</th>';
echo '<th>Sent On</th>';

if($_GET["u"] == $ir['userid']) {
    echo '<th>Actions</th>';
}

$selectComments = $db->query("SELECT * FROM `comments` WHERE `SendTo` = ".$_GET["u"]." ORDER BY `SentOn` DESC");

while($getComments = $db->fetch_row($selectComments)) {
    echo '<tr><td>';
    echo $getComments['Comment'];
    echo '<td>';
    echo $getComments['SentFrom'];
    echo '<td>';
    echo date($vu_config['date.format'],  strtotime($getComments['SentOn']));
    if($_GET["u"] == $ir['userid']) {
        echo '<td>';
        echo "<a href='viewuser.php?u=".$_GET["u"]."&commentID=".$getComments['ID']."&delete=true'><img src='http://www.famfamfam.com/lab/icons/silk/icons/delete.png' alt='Delete Comment' title='Delete Comment'></a>";
    }
    echo '</td></tr>';
}
echo '</table>';

if(isset($_GET['delete'])) {
    $commentID = htmlspecialchars(trim($_GET['commentID']));
    $db->query("DELETE FROM `comments` WHERE ID=".$commentID);
    exit(header("Location: viewuser.php?u=".$_GET["u"]));
}

function checkblank($in) {
  if(!$in) {
    return "N/A";
  }
  return $in;
}

$h->endpage();
?>