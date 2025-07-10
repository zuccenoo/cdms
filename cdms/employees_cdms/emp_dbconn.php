<?php
$host = 'localhost';
$username = 'root';
$password = "";
$dbname = 'employees_cdms';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//db name: inventory_cdms
//main table name: inventory
//table fields: id, inv_id, item_name, category, quantity, location_name, supplier, bg_balance, balance, date_added, last_updated
?>