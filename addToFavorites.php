<?php	
// this file add a new favorite to the session list 
// luwan wang 100995128
header('Location: /view-favorites.php');
session_start();
if($_SERVER['REQUEST_METHOD'] == 'GET') {
	if(isset($_GET['paintID']) && isset($_GET['ImageFileName']) && isset($_GET['title'])) {
		if(!isset($_SESSION['favorites'])) {
			$_SESSION['favorites'] = array();
		}
		// find the painting to be deleted 
		$paintID = $_GET['paintID'];
        $liked = false;
        // if this paint already in the list , set liked = true
		foreach($_SESSION['favorites'] as $fav) {
            if($paintID == $fav['paintID']){
                $liked = true;
            }
        }
        // if paint never in the list, add to the list, increment the counter
		if(!$liked) {
			$_SESSION['favorites'][] = array('paintID' => $_GET['paintID'], 'ImageFileName' => $_GET['ImageFileName'].'.jpg', 'title' => $_GET['title']);
		}

	} 
} 
?>