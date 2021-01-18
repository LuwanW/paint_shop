<?php
// luwan wang 100995128
// this file deletes favorate paints from the session list
header("Location: /view-favorites.php");
session_start();

if(isset($_GET['paintID']) && isset($_SESSION['favorites'])) {
	foreach($_SESSION['favorites'] as $index => $fav) {
        // find the favorite and remove from the list 
		if(($fav['paintID'] == $_GET['paintID']) || $_GET['paintID']==-1 ) {
			unset($_SESSION['favorites'][$index]);

		}
	}
}

