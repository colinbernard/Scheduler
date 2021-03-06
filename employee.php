<!DOCTYPE html>
<html>
<head>
<title>KVLiquor - Employee</title>
<link rel='stylesheet' type='text/css' href='style/kvliquor.css'/>
<?php
// CONNECT TO DATABASE
require 'vendor/autoload.php';
use App\SQLiteConnection;

$pdo = (new SQLiteConnection())->connect();

date_default_timezone_set('America/Los_Angeles'); // set default time zone to PST


// alert function
function alert($message) {
	echo "<script type='text/javascript'>alert('$message');</script>";
}

?>
</head>
<body>
<div id = "header">
<img src="images/logo.jpg" style="float:left">

<div id="links">

<table id="top"><tr>
<td><a href = "admin.php?year=<?php echo date("Y"); ?>&month=<?php echo date("m"); ?>&day=<?php echo date("d"); ?>"> Schedule a Shift </a></td>
<td><a href = "report.php"> Generate Report </a></td>
<td><a href = "email.php"> Generate Emails </a></td>
<td><a href = "admin.php?showemp=1&year=<?php echo date("Y"); ?>&month=<?php echo date("m"); ?>&day=<?php echo date("d");?>"> Employees </a></td>
<td><a href = "settings.php"> Settings </a></td></tr></table>
</div>
</div>
<div id="calendar">
<?php

/* CHECKS IF FIRST NAME AND LAST NAME ARE SET */
function validate() {
	if(isset($_GET['firstname']) && isset($_GET['lastname'])) {
		if(!empty($_GET['firstname']) && !empty($_GET['lastname'])) {
			if((preg_match('/\\d/', $_GET['firstname']) <= 0) && (preg_match('/\\d/', $_GET['lastname']) <= 0)) {
				return true;
			} else { alert("Names must not contain numbers."); }
		}
	}
	return false;
}

/* DELETING AN EMPLOYEE */
if(isset($_GET['del'])) {	
	$id = $_GET['del'];
	//$sql = "DELETE FROM Employee WHERE id = :id;";
	$sql = "UPDATE Employee SET employed = 'false' WHERE id = :id"; // set employed attr to false
	$stmt = $pdo->prepare($sql);
	$stmt->execute([':id' => $id]);
	echo "<p>Employee Deleted</p>";
} else {

	$id = null;
	if(isset($_GET['id'])) {
		/* VIEWING AND EDITING A CURRENT EMPLOYEE */

		$id = $_GET['id'];
		echo "<h2>Editing Employee ID: $id</h2>";
		// query db using id
		// prepare select statement
		$sql = "SELECT firstname, lastname, email, phone, username FROM Employee WHERE id = :id;";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([':id' => $id]);
		$employee = $stmt->fetchObject(); // only one employee should be returned
		$firstname = $employee->firstname;
		$lastname = $employee->lastname;
		$email = $employee->email;
		$username = $employee->username;
		$phone = $employee->phone;
		
		// check if save button has been pressed
		if(isset($_GET['submitted'])) {
			// update employee if firstname and lastname are not null
			if(validate()) {
				// update
				$firstname = $_GET['firstname'];
				$lastname = $_GET['lastname'];
				$email = $_GET['email'];
				$username = $_GET['username'];
				$phone = $_GET['phone'];
				
				$sql = "UPDATE Employee 
						SET firstname = :firstname, lastname = :lastname, 
							email = :email, username = :username, phone = :phone
						WHERE id = :id;";
				$stmt = $pdo->prepare($sql);
				
				// passing values to the parameters
				$stmt->bindValue(':firstname', $firstname);
				$stmt->bindValue(':lastname', $lastname);
				$stmt->bindValue(':email', $email);
				$stmt->bindValue(':username', $username);
				$stmt->bindValue(':phone', $phone);
				$stmt->bindValue(':id', $id);
				
				$stmt->execute(); // execute the statement
				$message = "Update Successful!";
				echo "<script type='text/javascript'>alert('$message');</script>";
			} else { // alert user
				alert("Invalid! Firstname and lastname must not be empty.");
			}
		}
		
	} else {
		/* ADDING A NEW EMPLOYEE */
		echo "<h2>Add an Employee</h2>";
		$firstname = $lastname = $email = $username = $phone = "";
		
		// check if save button has been pressed
		if(isset($_GET['submitted'])) {
			// insert employee if firstname and lastname are not null
			if(validate()) {
				// insert
				$firstname = $_GET['firstname'];
				$lastname = $_GET['lastname'];
				$email = $_GET['email'];
				$username = $_GET['username'];
				$phone = $_GET['phone'];
				
				$sql = 	"INSERT INTO Employee(firstname, lastname, email, username, phone, employed)" 
						. "VALUES (:firstname, :lastname, :email, :username, :phone, 'true');";
				$stmt = $pdo->prepare($sql);
				
				// passing values to the parameters
				$stmt->bindValue(':firstname', $firstname);
				$stmt->bindValue(':lastname', $lastname);
				$stmt->bindValue(':email', $email);
				$stmt->bindValue(':username', $username);
				$stmt->bindValue(':phone', $phone);
				
				$stmt->execute(); // execute the statement
				$message = "Insert Successful!";
				echo "<script type='text/javascript'>alert('$message');</script>";
				header("Location: admin.php?showemp=1&year=".date("Y"). "&month=".date("m")."&day=".date("d")); // re direct user
			} else {
				$message = "Invalid! Firstname and lastname must not be empty.";
				echo "<script type='text/javascript'>alert('$message');</script>";
			}
		}
	}

	echo "<form method=\"get\" action=\"employee.php\">
			<table>
			<tr><td align =\"left\">First Name:</td><td align =\"left\"><input type=\"text\" name=\"firstname\" size=\"30\" value=\"$firstname\"></td></td>
			<tr><td  align =\"left\">Last Name:</td><td  align =\"left\"><input type=\"text\" name=\"lastname\" size=\"30\" value=\"$lastname\"></td></td>
			<tr><td align =\"left\">Email:</td><td align =\"left\"><input type=\"text\" name=\"email\" size=\"30\" value=\"$email\"></td></td>
			<tr><td align =\"left\">Site Username:</td><td align =\"left\"><input type=\"text\" name=\"username\" size=\"30\" value=\"$username\"> (Not implemented)</td></td>
			<tr><td align =\"left\">Phone:</td><td align =\"left\"><input type=\"text\" name=\"phone\" size=\"10\" value=\"$phone\"></td></td>
			</table>";
			
	if(!empty($id)) {
		echo "<input type=\"hidden\" name=\"id\" value=\"$id\">";
	}
			
	echo "<input type=\"hidden\" name=\"submitted\" value=\"1\">
			<br><br>
			<input type=\"submit\" name=\"submit\" value=\"Save\" id=\"submit\" />
			";
			
	if(!empty($id)) {
		echo "<a href=employee.php?del=$id><button type=\"button\">Delete Employee</button></a>";
	}
	echo "</form>";
}
?>
</div>
<body>
<div id="footer">
<p>Colin Bernard 2016</p>
</div>
</html>