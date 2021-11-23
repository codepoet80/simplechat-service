<html><head>
<style>
body { font-family: Helvetic,Arial; }
h1 { font-size: 18px; }
img { padding: 4px; }
small { font-size: 12px; color: dimgray; }
</style>
<?php
$config = include('config.php');
$imgurclientid = $config['imgurclientid'];

if (isset($_SERVER['QUERY_STRING'])) {
    $url = urldecode($_SERVER['QUERY_STRING']);
    if (isset($_GET["album"])) {
        $url = $_GET["album"];
    }
    $maxwidth = "1024";
    if (isset($_GET["maxwidth"])) {
        $maxwidth = $_GET["maxwidth"];
    }
    $urlParts = explode("/", $url);
    $url = end($urlParts);
    $url = "https://api.imgur.com/3/album/" . $url;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FILE, $fh);
    curl_setopt($ch, CURLOPT_TIMEOUT, 720);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
    $headers = [];
    $headers[] = 'Authorization: Client-ID ' . $imgurclientid ;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo "Fetch error: " . curl_error($ch);
    }
    curl_close($ch);

    $json_a = json_decode($response);
    $title = $json_a->data->title;
    $date = $json_a->data->datetime;
    $date = date("h:i:s Y-m-d", $date);
    echo "<title>$title</title>";
    echo "<body>";
    echo "<h1>$title<br>";
    echo "<small>$date</small></h1>";
    $items = $json_a->data->images;
    foreach ($items as $item) { 
        if (isset($item->link)) {
            echo "<img src='$item->link' width='$maxwidth' style='width:" . $maxwidth ."px'><br/>";
        }
    }
    echo "</body>";
}
?>
</html>