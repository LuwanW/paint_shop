<?php

// this file display favorites when click on favorites in the header
session_start();

function getFavorites() {
	if (isset($_SESSION['favorites'])){
		foreach($_SESSION['favorites'] as $favorite) {

			echo '<tr>';
			echo '<td><a href="single-painting.php?id='.$favorite['paintID'].'">'
				.'<img src="images/art/works/square-medium/'.$favorite['ImageFileName'].'"/></a></td>';
			echo '<td>'.$favorite['title'].'</td>';
			echo '<td><a href="remove-favorites.php?paintID='.$favorite['paintID'].'">'.'<button class="ui right labeled icon button" >Remove</button></a>';
			echo '</td>';
			echo '</tr>';
		}
		// show remove all option when count > 0
		if (count($_SESSION['favorites'])>0){
			echo '<td><a href="remove-favorites.php?paintID=-1'.'">'.'<button class="ui right labeled icon button" >Remove all</button></a>';
		}
	

	}

}
?>
<!DOCTYPE html>
<html lang=en>
<head>
    <meta charset=utf-8>
    <link href='http://fonts.googleapis.com/css?family=Merriweather' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script src="css/semantic.js"></script>
        <script src="js/misc.js"></script>
    
    <link href="css/semantic.css" rel="stylesheet" >
    <link href="css/icon.css" rel="stylesheet" >
    <link href="css/styles.css" rel="stylesheet">
    
</head>
<body>
<?php include_once('header.inc.php'); ?>

<main class="ui segment doubling stackable grid container">
<table class="ui very basic collapsing celled table">
	<thead>
		<td>Image</td>
		<td>Title</td>
		<td>Action</td>
	</thead>
	<tbody>
	<?php getFavorites(); ?>
	</tbody>
</table>

</main>

  <footer class="ui black inverted segment">
      <div class="ui container">footer for later</div>
  </footer>
</body>
</html>