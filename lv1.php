<?php

interface iRadovi {
    function create($naziv, $tekst, $link, $oib);
    function save();
    function read();
}


class DiplomskiRadovi implements iRadovi {
    private $naziv_rada;
    private $tekst_rada;
    private $link_rada;
    private $oib_tvrtke;
    
    function __construct($naziv, $tekst, $link, $oib) {
        $this->naziv_rada = $naziv;
        $this->tekst_rada = $tekst;
        $this->link_rada = $link;
        $this->oib_tvrtke = $oib;
    }

    function create($naziv, $tekst, $link, $oib) {
        //not needed
    }

    
    function save() {
        $conn = connectToDatabase();
        $naziv = $this->naziv_rada;
        $tekst = $this->tekst_rada;
        $link = $this->link_rada;
        $oib = $this->oib_tvrtke;
        $sql = "INSERT INTO `diplomski_radovi` (`naziv_rada`, `tekst_rada`, `link_rada`, `oib_tvrtke`) VALUES ('$this->naziv_rada', '$this->tekst_rada', '$this->link_rada', '$this->oib_tvrtke')";
        if($conn->query($sql) === true) {
            $this->read();
        }
        else {
            echo "Error! " . $sql . "<br>" . $conn->error;
        };
        $conn->close();
    }

    
    function read(){
        $conn = open_db();
        $radovi = $conn->query("SELECT * FROM `diplomski_radovi`");
        if($radovi->num_rows > 0){
            $rad = $radovi->fetch_assoc()
            while($rad){
                echo "Naziv rada: {$rad['naziv_rada']}";
                echo "Tekst rada: {$rad['tekst_rada']}";
                echo "Link rada: {$rad['link_rada']}";
                echo "OIB tvrtke: {$rad['oib_tvrtke']}";
            }
        }
        $conn->close();    
    } 
}

function connectToDatabase(){
    return new mysqli("localhost", "root", "", "radovi");
}


function httpGET($URL){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $URL);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

$page = get_content("https://stup.ferit.hr/index.php/zavrsni-radovi/page/2");
$new_page = $page;

$article_len = 1;
$page_len = strlen($page);

while(true){
    $pos1 = strpos($new_page, "<article id=");
    $pos2 = strpos($new_page, "</article>");
    $article_len = $pos2-$pos1;
    if($article_len>0){
        $article = substr($new_page, $pos1, $article_len);
        $oib_tvrtke = substr($article, strpos($article, ".png")-13, 13);
        $link_start = strpos($article, '<a class="fusion-rollover-link" href=')+38;
        $link_end = strpos($article, '/">')+1;
        $link_rada = substr($article, $link_start, $link_end - $link_start);
        $tema_start = strpos($article, '<div class="fusion-post-content-container"><p>')+46;
        $tema_end = strpos($article, "   Opis:");
        $naziv_rada = substr($article, $tema_start, $tema_end-$tema_start);
        $tema_page = get_content($link_rada);
        $tekst_start = strpos($tema_page, "<p><strong>${naziv_rada}")+strlen($naziv_rada)+48;
        $tekst_end = strpos($tema_page, "<p><strong>Mentor:")-17;
        $tekst_rada = substr($tema_page, $tekst_start, $tekst_end-$tekst_start);

        $rad = new DiplomskiRadovi($naziv_rada, $tekst_rada, $link_rada, $oib_tvrtke);
        $rad->save();
        
        $page_len = $page_len - $pos2; 
        $new_page = substr($new_page, $pos2, $page_len);
    }
    else{
        break;
    }
}


?>
