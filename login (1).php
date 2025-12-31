<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost","u123910324_qoselahali77","PASSWORD","Qoselahali77");
if ($conn->connect_error) {
    echo json_encode(["success"=>false,"message"=>"DB Error"]);
    exit;
}

$username = $_POST['username'] ?? '';
$password = hash('sha256', $_POST['password'] ?? '');
$hwid     = $_POST['hwid'] ?? '';

$stmt = $conn->prepare("
    SELECT * FROM users
    WHERE username=?
    AND password=?
    AND is_active=1
    AND (expiry_date IS NULL OR expiry_date >= CURDATE())
");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {

    // ربط HWID أول مرة
    if ($row['hwid'] == null) {
        $up = $conn->prepare("UPDATE users SET hwid=? WHERE id=?");
        $up->bind_param("si", $hwid, $row['id']);
        $up->execute();
    } 
    elseif ($row['hwid'] !== $hwid) {
        echo json_encode(["success"=>false,"message"=>"HWID Mismatch"]);
        exit;
    }

    echo json_encode(["success"=>true,"message"=>"Login Success"]);
}
else {
    echo json_encode(["success"=>false,"message"=>"Invalid or Expired Account"]);
}
