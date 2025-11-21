$host = "db";
$dbname = "catabris";
$user = "user";
$pass = "userpass";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die($e->getMessage());
}
