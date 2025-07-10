<?php
$host = 'localhost';
$username = 'root';
$password = "";
$dbname = 'inventory_cdms';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//db name: inventory_cdms

//table name: inventory
//id	int(11)	NO	PRI	NULL	auto_increment	
//inv_id	varchar(20)	NO	UNI	NULL		
//item_name	varchar(255)	NO		NULL		
//category	varchar(100)	NO		NULL		
//quantity	int(11)	NO		NULL		
//unit	varchar(20)	YES		'pcs'		
//location_name	varchar(100)	NO		NULL		
//supplier	varchar(100)	NO		NULL		
//date_added	timestamp	NO		current_timestamp()		
//last_updated	timestamp	NO		current_timestamp()	on update current_timestamp()	
//is_consumable	tinyint(1)	YES		0		
//archived	tinyint(1)	NO		0

//table name: inventory_consumption_log
//id	int(11)	NO	PRI	NULL	auto_increment	
//inv_id	varchar(50)	NO		NULL		
//quantity_consumed	int(11)	NO		NULL		
//consumed_at	datetime	NO		current_timestamp()		
//consumed_by	varchar(100)	YES		NULL		

//table name: inventory_checklist
//checklist_id	int(11)	NO	PRI	NULL	auto_increment	
//inv_id	varchar(20)	NO	MUL	NULL		
//status_check_in	enum('Good','Missing','Broken','Damaged')	YES		Good		
//status_check_out	enum('Good','Missing','Broken','Damaged')	YES		Good		
//checked_at	datetime	YES		current_timestamp()		

?>