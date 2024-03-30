<?php
$clothType = "";
$clothCode = "";
$price = "";

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'ims';

$db = new mysqli($db_host,$db_user,$db_pass,$db_name);
if($db->connect_error){
    die("Connection failed: " . $db->connect_error);
}

if(isset($_POST["clear"])){
    $db->query("DELETE FROM inventory");
    $db->query("ALTER TABLE inventory AUTO_INCREMENT = 1");
    header("Location: {$_SERVER['REQUEST_URI']}",true,303);
    exit();
}

if(isset($_POST["delete"]) && isset($_POST["ID"])) {
    $id = $_POST["ID"];
    $stmt = $db->prepare("DELETE FROM inventory WHERE id = ?");
    $stmt->bind_param("i", $id);
    $delete = $stmt->execute();

    if($delete){
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }
}

if(isset($_POST["submit"]) && !empty($_POST['clothType']) && !empty($_POST['clothName']) && !empty($_POST['clothPrice'])) {
    $clothType = $_POST['clothType'];
    $clothCode = $_POST['clothName'];
    $price = $_POST['clothPrice'];

    $stmt = $db->prepare("INSERT INTO inventory (clothType, clothCode, price, date) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $clothType, $clothCode, $price);
    $insert = $stmt->execute();
    
    if($insert){
         // Send WebSocket message to update inventory
         $data = array(
            'id' => $db->insert_id,
            'clothType' => $clothType,
            'clothCode' => $clothCode,
            'price' => $price,
            'date' => date('Y-m-d H:i:s')
        );
        sendMessageToWebSocketServer('addItem', $data);
        header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
        exit();
    }
}

function sendMessageToWebSocketServer($event, $data) {
    $url = 'http://localhost:3000';
    $options = array(
        'http' => array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query(array('event' => $event, 'data' => $data))
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return $result;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Angels Collection Cloth Inventory System</title>
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <header>
    <h1>Angels Collection</h1>
    <h3>Inventory Management System</h3>
    <button id="logout">logout</button>
</header>

<section>
<div class="input">
    <form method="POST" enctype="multipart/form-data">
    <select name="clothType" id="clothType" required>
        <option>Wing Sweater</option>
        <option>One piece</option>
        <option>Jeans Pant</option>
        <option>Joggers</option>
        <option>Cargo Pant</option>
        <option>Formal Pant</option>
        <option>T-shirt</option>
        <option>Blazzer</option>
        <option>Sweater</option>
        <option>Crop T-shirt</option>
        <option>Tops</option>
        <option>Jumpsuit</option>
        <option>Pant</option>
        <option>Two Piece</option>
        <option>Leggins</option>
        <option>Lelan Pant</option>
        <option>Outer</option>
        <option>Formal Shirt</option>
        <option>Dress</option>
        <option>Belly Jeans</option>
        <option>Shirt</option>
        <option>String Dress</option>
        <option>Belt</option>
        <option>Belly Pant</option>
        <option>Jacket</option>
        <option>Bodycon</option>
        <option>Quarter-Leggins</option>
    </select>

    <input type="text" placeholder="Cloth Code" id="clothName" name="clothName" required>
    <input type="text" placeholder="cloth Price" id="price" name="clothPrice" required>
    <input type="Submit" value="Add" id="add" name="submit">
    <br>
    </form>
</div>

<div class="output">
<?php
$sql = "SELECT * FROM `inventory`";

$result = $db->query($sql);
if($result->num_rows > 0){
    echo "<table>";
    echo "<tr><th>ID</th><th>Cloth Type</th><th>Cloth Code</th><th>Price</th><th>Date</th><th>Action</th>";
    while ($row = $result->fetch_assoc()){
        echo "<tr><td>". $row["id"]. "</td><td>" . $row["clothType"] . "</td><td>" . $row["clothCode"] . "</td><td>" . $row["price"] . "</td><td>" . $row["date"] . "</td>";
        echo "<td><form method='POST'><input type='hidden' name='ID' value='".$row["id"]."'><button type='submit' name='delete'><i class='fa-solid fa-trash'></i></button></form></td></tr>";
    }
    echo "</table>";    
}
else{
    echo "<h1 class='message'>No Item Found</h1>";
}

$result->free_result();
$db->close();
?>

</div>

<div class='buttons'>
<form method="POST">
    <button type="submit" id="clear" name="clear">Clear</button>
</form>

<form method="POST">
    <button type="submit" id="total" name ="total">Total</button>
</form>
</div>

</section>

<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'ims';

$db = new mysqli($db_host,$db_user,$db_pass,$db_name);
if($db->connect_error){
    die("Connection failed: " . $db->connect_error);
}

if(isset($_POST["total"])) {
    $sql = "SELECT SUM(price) AS total FROM inventory";
    $result = $db->query($sql);
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $totalPrice = $row['total'];
        echo "<script>alert('Total Price: Npr " . $totalPrice . "');</script>";
    } else {
        echo "No items found";
    }
}
?>

<script src="server.js"></script>
<script src="https://kit.fontawesome.com/4f9d824da5.js" crossorigin="anonymous"></script>
</body>
</html>
