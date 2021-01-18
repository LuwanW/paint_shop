<?php
// luwan wang 100995128
// this file create paint as a class 
define('DBHOST', '');
define('DBNAME', 'art');
define('DBUSER', 'testuser');
define('DBPASS', 'mypassword');
define('DBCONNSTRING','mysql:dbname=art;charset=utf8mb4;');

// creat a class painting that helps to create single-painting easier
class Painting {
	public $Id;
	public $title;
	public $artist;
	public $year;
	public $medium;
	public $height;
	public $width;
	public $fileName;
	public $museumLink;
	public $accessionNumber;
	public $copyright;
	public $excerpt;
	public $description;
	public $googleDesc;
	public $wikiLink;
	public $googleLink;
	public $msrp;
	public $gallery;
	public $genres = [];
	public $subjects = [];	

	public function __construct($Id) {
		//Make DB queries to fill out the above properties
		$this->Id = $Id;
		try {	
			$sql = 'SELECT * FROM Paintings WHERE PaintingID = '. $Id;
			$painting_result = Query($sql);
			if($painting_result) {
				// query painting 
				$painting = $painting_result->fetch();
				$this->title = $painting['Title'];
				// query artists 
				$sql = 'SELECT ArtistID, FirstName, LastName FROM Artists
					 WHERE ArtistID = '. $painting['ArtistID'];
				$artist_result = Query($sql);
				if(!$artist_result) die("Artist doesn't exist");
				$artist = $artist_result->fetch();
				// query gallery 
				$sql = 'SELECT GalleryName FROM Galleries
					 WHERE GalleryID = '. $painting['GalleryID'];
				$gallery_result = Query($sql);
				if(!$gallery_result) die("Gallery doesn't exist");
				$gallery = $gallery_result->fetch();
				$this->gallery = $gallery['GalleryName'];
				// query PaintingGenres
				$sql = 'SELECT * FROM (SELECT * FROM PaintingGenres WHERE PaintingID = '. $Id
					. ') t1 INNER JOIN Genres ON t1.GenreID = Genres.GenreID';
				$genres_result = Query($sql);
				if(!$genres_result) die('DBQuery error');
				while($genre = $genres_result->fetch()) {
					array_push($this->genres, $genre['GenreName']);
				}
				// query subjects
				$sql = 'SET @row = 0;
					SELECT * FROM (SELECT * FROM PaintingSubjects WHERE PaintingID = '. $Id
						. ') t1 INNER JOIN (SELECT (@row := @row + 1) as Num, SubjectName FROM
					       	Subjects) t2 ON t1.SubjectID = t2.Num;';
				$subject_result = Query($sql);
				if(!$subject_result) die('error');
				$subject_result->nextRowset();

				while($subject = $subject_result->fetch()) {
					array_push($this->subjects, $subject['SubjectName']);
				}

				// set this. parameters
				$this->artist = $artist['FirstName'].' '.$artist['LastName'];
				$this->year = $painting['YearOfWork'];
				$this->medium = $painting['Medium'];
				$this->height = $painting['Height'];
				$this->width = $painting['Width'];
				$this->fileName = $painting['ImageFileName']. '.jpg';
				$this->museumLink = $painting['MuseumLink'];
				$this->excerpt = $painting['Excerpt'];
				$this->description = $painting['Description'];
				$this->googleDesc = $painting['GoogleDescription'];
				$this->accessionNumber = $painting['AccessionNumber'];
				$this->copyright = $painting['CopyrightText'];
				$this->wikiLink = $painting['WikiLink'];
				$this->googleLink = $painting['GoogleLink'];
				$this->msrp = $painting['MSRP'];
			}
		} catch(PDOException $e) {
			die($e->getMessage());
		}
	}


    
}
?>