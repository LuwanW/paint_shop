<?php
// luwan wang 100995128

// to test memchache use:  
//alter table art.paintings rename to art.tmp
// alter table art.tmp rename to art.paintings
/*open console to test memcache, log will show that if the data is from database or memcache */

// note that memcache expires in 2 mins, expire is a global variable 
$expire=120;

define('DBHOST', '');
define('DBNAME', 'art');
define('DBUSER', 'testuser');
define('DBPASS', 'mypassword');
define('DBCONNSTRING','mysql:dbname=art;charset=utf8mb4;');

// query
function Query($sql) {
	try {
		$pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$result = $pdo->query($sql);
		$pdo = null;
		return $result;
	} catch(PDOException $e) {
		die( $e->getMessage());
	}
}
// filter museum 
function listMuseums() {
	$memcache = new Memcache;
	$memcache->addServer("localhost", 11211) or die("Connection Error");
	$museums = $memcache->get("museums");
	if($museums) {
		return $museums;
	} else {
		$sql = 'SELECT GalleryID, GalleryName FROM Galleries';
		$result = Query($sql);
		try {
			while($row = $result->fetch()) {
				$museums[$row['GalleryID']] = $row['GalleryName'];
			}
			$memcache->set("museums", $museums,false, $GLOBALS['expire']);
			return $museums;
		} catch (PDOException $e) {
			die($e->getMessage());
		}
	}
}

// filter artist 
function listArtists() {
	$memcache = new Memcache;
	$memcache->addServer("localhost", 11211) or die("Connection Error");
	$artists = $memcache->get("artists");
	if($artists) {
		return $artists;
	} else {
		$sql = 'SELECT ArtistID, FirstName, LastName FROM Artists';
		$result = Query($sql);
		try {
			while($row = $result->fetch()) {
				$artists[$row['ArtistID']] = $row['FirstName'].' '.$row['LastName'];
			}
			$memcache->set("artists", $artists,false,  $GLOBALS['expire']);
			return $artists;
		} catch (PDOException $e) {
			die($e->getMessage());
		}
	}
	
}

// filter shape
function listShapes() {
	$memcache = new Memcache;
	$memcache->addServer("localhost", 11211) or die("Connection Error");
	$shapes = $memcache->get("shapes");
	if($shapes) {
		return $shapes;
	} else {
		$sql = 'SELECT ShapeName FROM Shapes';
		$result = Query($sql);
		try {
			$rowNumber = 1;
			while($row = $result->fetch()) {
				$shapes[$rowNumber] = $row['ShapeName'];
				$rowNumber++;
			}
			$memcache->set("shapes", $shapes,false,  $GLOBALS['expire']);
			return $shapes;
		} catch (PDOException $e) {
			die($e->getMessage());
		}
	}
	
}

// debug function 
function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

// display each image
function display_image($row){
	
	echo '<li class="item">';
	echo '<a class="ui small image" href="single-painting.php?id='.$row['PaintingID'].'">';
	echo '<img src="images/art/works/square-medium/'.$row['ImageFileName'].'.jpg"></a>';
	echo '<div class="content">';
	echo '<a class="header" href="single-painting.php?id='.$row['PaintingID'].'">'
		.$row['Title'].'</a>';
	echo '<div class="meta"><span class="cinema">'.$row['FirstName'].' '.$row['LastName'].'</span></div>';
	echo '<div class="desciption"><p>'.$row['Excerpt'].'</p></div>';
	echo '<div class="meta">';
	echo '<strong>$'.$row['MSRP'].'</strong></div>';
	echo '<div class="extra">';
	echo '<a class="ui icon orange button"><i class="add to cart icon"></i></a>';
	echo '<a class="ui icon button" href="addToFavorites.php?
	paintID='.$row['PaintingID']
		.'&ImageFileName='.$row['ImageFileName']
		.'&title='.$row['Title']
		.'"><i class="heart icon"></i></a>';
	echo '</div></div></li>';
}

// display query painting 
function display_not_cached($result){
	$result_array=array();
	try {
		$result->nextRowset();
		while($row = $result->fetch()) {
			# push to array
			array_push($result_array,$row);
			display_image($row);
		}
	} catch(PDOException $e) {
		die($e->getMessage());
	}
	return $result_array;
}

// display cached painting 
function display_cached($result){
	try {
		foreach($result as $row){
			display_image($row);
		}
	} catch(PDOException $e) {
		die($e->getMessage());
	}
}

// get top 20 or filter
function getTop20() {
	$memcache = new Memcache;
	$memcache->addServer("localhost", 11211) or die("Connection Error");

	// if fillter is applied
	if(isset($_GET['artist'])||isset($_GET['gallery'])||isset($_GET['shape'])) {
		$artist=1;
		$museum=1;
		$shape=1;
		if($_GET['artist'] != 0){
			$artist='ArtistID = '.$_GET['artist'];
		}
		if($_GET['gallery'] != 0){
			$artist='GalleryID = '.$_GET['gallery'];
		}
		if($_GET['shape'] != 0){
			$artist='ShapeID = '.$_GET['shape'];
		}
		$sql = 'SET @row := 0;
			SELECT * FROM ((SELECT * FROM Paintings WHERE '.$artist.' AND '.$museum.' AND '. $shape.') 
			PT INNER JOIN (SELECT (@row := @row + 1) AS num, ShapeName FROM Shapes) ST ON PT.ShapeID = ST.num) 
			INNER JOIN Artists ON Artists.ArtistID = PT.ArtistID LIMIT 20';

		// set key for memcache as artistid + galeryid+ shapeid
		$result = Query($sql);
		$key = "result".$_GET['artist']."_".$_GET['gallery']."_".$_GET['shape'];
	} 
	// if no filter, set key to result
	else {
		$sql = 'SET @row := 0;
			SELECT * FROM ((SELECT * FROM Paintings ) 
			PT INNER JOIN (SELECT (@row := @row + 1) 
			AS num, ShapeName FROM Shapes) ST ON PT.ShapeID = ST.num) 
			INNER JOIN Artists ON Artists.ArtistID = PT.ArtistID LIMIT 20';

			// set memcahe key
			$result = Query($sql);
			$key="result";
	}
	// set to memcache if not exists		
	debug_to_console($key);
	$memcresult = $memcache->get($key);
	// if cached
	if($memcresult ){
		$result=$memcresult;
		display_cached($result);
		debug_to_console("display cached");
	}else{
	// if not cached, query and cache it
		$result = Query($sql);
		$memcresult = display_not_cached($result);
		$memcache->set($key,$memcresult,false,  $GLOBALS['expire']);
		debug_to_console("display query");
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
<body >
<?php include_once('header.inc.php'); ?>
    
<main class="ui segment doubling stackable grid container">

    <section class="five wide column">
        <form method="GET" class="ui form">
          <h4 class="ui dividing header">Filters</h4>

          <div class="field">
            <label>Artist</label>
            <select name="artist" class="ui fluid dropdown">
		<?php
		$artists = listArtists();
		echo '<option value="0">Select Artist</option>';
		foreach($artists as $artistID => $artist) {
			echo '<option value="'.$artistID.'">'.$artist.'</option>';
		}
		?>
            </select>
          </div>  
          <div class="field">
            <label>Museum</label>
            <select name="gallery" class="ui fluid dropdown">
		<?php
		$museums = listMuseums();
		echo '<option value="0">Select Museum</option>';
		foreach($museums as $galleryID =>  $museum) {
			echo '<option value="'.$galleryID.'">'.$museum.'</option>';
		}
		?>
            </select>
          </div>   
          <div class="field">
            <label>Shape</label>
            <select name="shape" class="ui fluid dropdown">
		<?php
		$shapes = listShapes();
		echo '<option value="0">Select Shape</option>';
		foreach($shapes as $shapeID => $shape) {
			echo '<option value="'. $shapeID.'">'.$shape.'</option>';
		}
		?>
            </select>
          </div>   

            <button class="small ui orange button" type="submit">
              <i class="filter icon"></i> Filter 
            </button>    

        </form>
    </section>
    

    <section class="eleven wide column">
        <h1 class="ui header">Paintings</h1>
        <ul class="ui divided items" id="paintingsList">
	<?php getTop20();?>

        </ul>        
    </section>  
    
</main>    
    
  <footer class="ui black inverted segment">
      <div class="ui container">footer for later</div>
  </footer>
</body>
</html>