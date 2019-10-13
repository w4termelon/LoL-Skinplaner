<?php
// get the q parameter from URL
$q = $_REQUEST["q"]; //championname wird übergeben
$searchs = array(" ", "'",".");
$q = str_replace($searchs, "", $q);
$searchspec = array("KhaZix","Fiddlesticks","ChoGath","LeBlanc","Wukong","VelKoz",);
$replacespec = array("Khazix","FiddleSticks","Chogath","Leblanc","MonkeyKing","Velkoz",);
$q = str_replace($searchspec, $replacespec, $q);
$url = "https://global.api.pvp.net/api/lol/static-data/euw/v1.2/champion?champData=skins&api_key=RGAPI-CD7AE743-2B77-4649-BBD1-2E954EA9707A";
$news_contents = file_get_contents($url); //String des Codes der URL
$news_contents = utf8_encode($news_contents); //Zeichensatz wird auf UTF8 gesetzt	
$results = json_decode($news_contents); //JSON Datei wird als PHP Array/Object "gespeichert"
$skins_n = count($results->data->$q->skins)-1; // Zählt alle vorhanden Skins für einen champion
$search = "/default/i"; //Suchwort für den default Skin
for ($i = 0; $i <= $skins_n; $i++){
$skins = $results->data->$q->skins[$i]->name; 

if (preg_match($search, $skins) != true){ //default Skin wird hierdurhc gefiltert
echo "<option value='".$skins."'>".$skins."</option>";
}
}
?>