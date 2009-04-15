<?php
/**
 *  PHP-Git Example
 *
 *  PHP version 5
 *
 *  @category VersionControl
 *  @package  PHP-Git
 *  @author   C�sar D. Rodas <crodas@member.fsf.org>
 *  @license  http://www.php.net/license/3_01.txt  PHP License 3.01
 *  @link     http://cesar.la/git
 */


define("GIT_DIR", "/home/crodas/projects/playground/phpgit/.git");
#define("GIT_DIR", "/home/crodas/projects/git/.git");
#define("GIT_DIR","/home/crodas/projects/bigfs/.git/");

require "phpgit/git.php";

try {
    $git = new Git(GIT_DIR);
} catch(Exception $e) {
    die(GIT_DIR." is not a valid git directory");
}

/* commit file list */
if (isset($_GET['commit'])) {
    $commit = $_GET['commit'];
    $commit = $git->getCommit($commit); 
    $file_list = & $commit['Tree'];
} else if (isset($_GET['file'])) {
    /* it is a file */
    $object = $git->getFile($_GET['file'], $type);
    if ($type == OBJ_TREE) {
        $file_list = & $object;
    } else {
        $content = & $object;
    }
}

if (isset($_GET['tag'])) {
    $tag = $git->getTag($_GET['tag']);
    $file_list = & $tag['Tree'];
}

if (isset($_GET['history'])) {
    $history = $git->getHistory($_GET['history'],20);
}

/* it is a branch  */
if (!isset($content) && !isset($history) && !isset($file_list) && !isset($_GET['branch'])) {
    $_GET['branch'] = 'master';
}
if (isset($_GET['branch'])) {
    try {
        $history = $git->getHistory($_GET['branch']);
    } catch(Exception $e) {
        $history = $git->getHistory('master');
    }
    $file_list = $git->getCommitTree($history['tree']);
    unset($history);
}


?>
<html>
<head>
    <title>Example - a fast and ugly Git view</title>
    <script src="prettify.js" type="text/javascript"></script>
    <link rel="stylesheet" href="prettify.css" 
    type="text/css" media="screen" />
</head>
<body>
<table>
<tr>
    <th>Branches</th>
    <th>Tags</th>
</tr>
<tr>
    <td>
    <ul>
<?php 
foreach ($git->getBranches() as $branch):
?>
    <li><a href="?branch=<?php echo $branch?>"><?php echo $branch?></a> | <a href="?history=<?php echo $branch?>">history</a> </li>
<?php
endforeach;
?>
    </ul>
    </td>
    <td>
    <ul>
<?php 
foreach ($git->getTags() as $id => $tag):
?>
    <li><a href="?tag=<?php echo $id?>"><?php echo $tag?></a></li>
<?php
endforeach;
?>
    </ul>
    </td>
</tr>
</table>


<?php 
if (isset($history)) :
?>
<table>
<tr>
    <th>Author</th>
    <th>Commit ID</th>
    <th>Date</th>
</tr>
<?php
foreach($history as $commit):
?>
<tr>
    <td><?php echo $commit['author']?></td>
    <td><a href="?commit=<?php echo $commit['id']?>"><?php echo $commit['id']?></a></td>
    <td><?php echo $commit['time']?></td>
</tr>
<?php
endforeach;
?>
</table>
<?php 
endif;
?>

<?php 
if (isset($file_list)) :
?>
<table>
<tr>
    <th>Permission</th>
    <th>Filename</th>
</tr>
<?php
foreach($file_list as $file):
?>
<tr>
    <td></td>
    <td><a href="?file=<?php echo $file->id?>"><?php echo $file->name?><?php echo $file->is_dir ? "/" : "" ?></a></td>
</tr>
<?php
endforeach;
?>
</table>
<?php 
endif;
?>


<?php
if (isset($content)) :
?>
<pre class="prettyprint">
<?php echo htmlentities($content);?>
</pre>
<script>prettyPrint();</script>

<?php
endif;
?>

</body>
</html>
