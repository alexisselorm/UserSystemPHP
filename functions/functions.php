<?php 


/*************************Helper Functions **********************/
function clean($string){

	return htmlentities($string);
}

function redirect($location){

return header("Location: {$location}");	

}

function set_message($message){

	if(!empty($message)){

		$_SESSION['message'] = $message;
	}else {
		$message = "";
	}
}

function display_message(){

	if(isset($_SESSION['message'])){
		echo $_SESSION['message'];

		unset($_SESSION['message']);
	}
}

function token_generator(){

	$token = $_SESSION['token'] = md5(uniqid(mt_rand(),true));

	return $token;
}

function validation_errors($errormessage){


$errormessage = <<<DELIMITER

<div class="alert alert-danger" role="alert"> $errormessage </div>

DELIMITER; 

return $errormessage;


}
function email_exists($email){

	$sql = "SELECT id FROM users WHERE email ='$email'";
	$result = query($sql);
	if (row_count($result)==1) {
		return true;
	}else{
		return false;
	}
}

function username_exists($username){

	$sql = "SELECT id FROM users WHERE username ='$username'";
	$result = query($sql);
	if (row_count($result)==1) {
		return true;
	}else{
		return false;
	}
}

function send_email($email,$subject,$message,$headers){

return mail($email,$subject,$message,$headers);




}

/**************************Validation Functions*****************/

function validate_user(){

	$min = 3;
	$errors = [];
	$max = 20;


	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		
		$first_name = clean($_POST['first_name']);
		$last_name = clean($_POST['last_name']);
		$username = clean($_POST['username']);
		$email = clean($_POST['email']);
		$password = clean($_POST['password']);
		$confirm_password = clean($_POST['confirm_password']);


// Pos rrequests checker 
if ($password !== $confirm_password) {
	$errors[]= "Your passwords do not match";
}
if (strlen($first_name) < $min) {
	
	$errors[] ="Your first name cannot be less than {$min} characters";
}
if(empty($first_name)){
	$errors[]= "Your first name cannot be empty";
}
if (strlen($last_name) < $min) {
	
	$errors[] ="Your last name cannot be  less than {$min} characters";
}
if(strlen($username) < $min){
	$errors[]= "Your username cannot be less than {$min} characters";
}
if (empty($last_name)) {
	
	$errors[] ="Your last name cannot be empty";
}
if(strlen($email) < $min){
	$errors[]= "Your username cannot be less than {$min} characters";
}
if (email_exists($email)) {

	$errors[]="Email already exists";
}
if (username_exists($username)) {

	$errors[]="username already exists";
}
if(!empty($errors)){
	foreach ($errors as $error) {

	echo validation_errors($error);

			}

		}else {
			if (register_user($first_name,$last_name,$username,$email,$password)) {


				set_message("<p class='bg-success text-center'>Please check your indox or spam folder for an activation code</p>");

				redirect("index.php");
			} else{
				set_message("<p class='bg-success text-center'>User could not be registered</p>");

				redirect("index.php");
			}
		}



	}
}

/********************Register User Functions**********//////////////
function register_user($first_name,$last_name,$username,$email,$password){

	$first_name = escape($first_name);
	$last_name = escape($last_name);
	$username = escape($username);
	$email = escape($email);
	$password = escape($password);




	if(email_exists($email)){
		return false;
	}elseif (username_exists($username)) {
		return false;
	}else {
		$password = md5($password);

		$validation_code = md5($username . microtime());

		$sql = "INSERT INTO users(first_name,last_name,username,email,password,validation_code,active)";
		$sql.= " VALUES('$first_name','$last_name','$username','$email','$password','$validation_code',0)";
		$result = query($sql);
		


		$subject = "Activate Account";
		$message = "Please click the link below to activate your account

		  http://localhost/login/activate.php?email=$email&code=$validation_code";

		$header = "From: noreply@geng-geng.com";

		send_email($email,$subject,$message,$headers);

		return true;
	}
}



/***********************Activation Functions****************************/

function activate_user(){
	if($_SERVER['REQUEST_METHOD'] == "GET"){

		if (isset($_GET['email'])) {
			

			 $email = clean($_GET['email']);
			 $validation_code = clean($_GET['code']);


			 $sql = "SELECT id FROM users WHERE email = '".escape($_GET['email'])."' AND validation_code = '".clean($_GET['code'])."' ";
			 $result = query($sql);
			 

			 if (row_count($result) == 1) {
			 	$sql2 = "UPDATE users SET active = 1,validation_code= 0 WHERE email ='".escape($email)."'AND validation_code = '".escape($validation_code)."'   " ;
			 	 $result2 = query($sql2);
			 confirm($result2);
			 

			 set_message("<p class ='bg-success'>You're inside oooohhh</p>");
			 redirect("login.php");

			}
			else{
				set_message("<p class ='bg-danger'>Your accound could not be activated</p>");
			 redirect("register.php");

			}
		}

	}
}

/***************Vlaidate User Login*******/
function validate_userlogin(){

	$min    = 3;
	$errors = [];
	$max    = 20;


	if ($_SERVER['REQUEST_METHOD'] == "POST") {


	$email    = clean($_POST['email']);
	$password = clean($_POST['password']);
	$remember = isset($_POST['remember']);


	if (empty($email)) {
		$errors[] = "Email field cannot be empty";
	}
	if (empty($password)) {
		$errors[] = "Password field cannot be empty";
	}




	if(!empty($errors)){
	foreach ($errors as $error) {

	echo validation_errors($error);

			}

		}else {
				if (login_user($email,$password,$remember)) {
					redirect("admin.php");
				}else{


					echo validation_errors("Your credentials are not correct");

				}
			


		}

		
	}

}


/*****************User Login*********************/
function login_user($email,$password, $remember){

$sql = "SELECT password,id FROM users WHERE email ='".escape($email)."' AND active = 1";       
$result = query($sql);

if (row_count($result) == 1)  {
	$row = fetch_array($result);
	$hashed_password = $row['password'];

	if (md5($password) == $hashed_password) {

		if ($remember == "on") {
			setcookie('email',$email,time()+ 16000);
		


		}




	$_SESSION['email'] = $email;



		return true;
	}else{
		return false;

	}


	return true;
}else{


return false;

}

} 


/*******************************Logged In functions**********************/
function logged_in(){
	if (isset($_SESSION['email']) || isset($_COOKIE['email'])) {
		return true;


	}else{


		return false;
	}
}






/*******************Recover Password Functions******************/


function recover_password(){

	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		if (isset($_SESSION['token']) && $_POST['token'] === $_SESSION['token']) {
		
		
		$email = clean($_POST['email']);

		$validation_code = md5($email.microtime());


		setcookie('temp_access_code',$validation_code,time() +900);


		$sql = "UPDATE users SET validation_code = '".escape($validation_code)."'  WHERE email = '".escape($email). "'";
		$result = query($sql);
		



		if (email_exists($email)) {

			$subject = "Please reset your password";
			$message = "This is your password reset code {$validation_code}


			Please click the link below to reset you password
			http://localhost/code.php?email=$email&code=$validation_code";

			$headers = "From: noreply@geng-geng.com";


			if(!send_email($email,$subject,$message,$headers)){

			echo validation_errors("Email could not be sent");	

			}

			set_message("<p class='bg-success'>Please check your email or spam folder for a password reset code </p> ");
			redirect("index.php");





		}else{


			echo validation_errors("This email does not exist");



		}




		}else{
			redirect("index.php");
		}

			if (isset($_POST['cancel_submit'])) {
				redirect('login.php');
		}


	}	

	
}


function codevalidator(){
	if (isset($_COOKIE['temp_access_code'])) {
		
			if (!isset($_GET['email']) && !isset($_GET['code'])) {
		
				redirect('index.php');
		
			}elseif (empty($_GET['email']) || empty($_GET['code'])) {
		
				redirect('index.php');
		
			}else{
		
				if (isset($_POST['code'])) {
					$validation_code = clean($_POST['code']);
					$email= clean($_GET['email']);


					$sql = "SELECT id FROM users WHERE validation_code ='".escape($validation_code)."' AND email = '".escape($email)."'";
					$result = query($sql);
					


					if (row_count($result) ==1 ) {

								setcookie('temp_access_code',$validation_code,time() +900);

						redirect("reset.php?email=$email&code=$validation_code");
					}else{
						echo validation_errors("Sorry wrong validation code");
					}

				}
			}
		


	}else{
		redirect("recover.php");
		set_message("<p class='bg-danger text-center'>Sorry your validation cookie has expired</p>");

		
	}
}

/***************************Password Reset Function********************/
function password_reset(){

if (isset($_COOKIE['temp_access_code'])) {

	if (isset($_GET['email']) && isset($_GET['code'])) {

		if (isset($_SESSION['token']) && isset($_POST['token'])){

			if($_POST['token'] === $_SESSION['token']) {

				if ($_POST['password'] == $_POST['confirm_password']) {
									
				$updated_password = md5($_POST['password']);


		 		$sql = "UPDATE users SET password = '".escape($updated_password)."',validation_code = 0 WHERE email ='".escape($_GET['email'])."' ";
		 		query($sql);
		 		set_message("<p class='bg-success text-center'>Your password has been updated. Please login</p>");
		 		redirect("login.php");
					

				}else{

					echo validation_errors("Password fields don't match");

				}
			}


			}
		}
	}else{

		set_message("<p class='bg-danger'>Sorry your session has expired </p> ");
		redirect("recover.php");
	}

}
?>