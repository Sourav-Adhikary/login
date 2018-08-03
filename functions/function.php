<?php
#-----------random-unique string--------------

function crypto_rand_secure($min, $max)
{
    $range = $max - $min;
    if ($range < 1) return $min; // not so random...
    $log = ceil(log($range, 3));
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd > $range);
    return $min + $rnd;
}

function getToken($length)
{
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "0123456789";
    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";

    $max = strlen($codeAlphabet); // edited

    for ($i=0; $i < $length; $i++) {
        $token .= $codeAlphabet[crypto_rand_secure(0, $max-1)];
    }

    return $token;
}
function my_random_generator(){
  $random = getToken(15).mt_rand();
  $random.=microtime();
  $random = md5($random);


  return $random;
}



#-----------helper function--------------


function clean($string){
return htmlentities( $string );
}

function redirect($location){
  return header("Location:{$location}");
}
function set_message($message){
  if(!empty($message)){
    $_SESSION['message']=$message;
  }
  else{
    $messge="";
  }
}
function display_message(){
  if(isset($_SESSION['message'])){
    echo $_SESSION['message'];
    unset($_SESSION['message']);
  }
}
function token_generator(){
  $session_token =my_random_generator();
  $_SESSION['token_recover']=$session_token ;
  return $session_token ;
}
function validation_errors($error){
  $message = <<<DELIMITER
  <div class="alert alert-danger alert-dismissible fade in">
    <a href="" class="close" data-dismiss="alert" aria-label="close">&times;</a>
    <strong>Warning!</strong> $error
  </div>
</div>


DELIMITER;
  return $message;
}
function email_exists($email){
  $sql= "SELECT id FROM users WHERE email = '$email'";
  $result = query($sql);
  if(row_count($result)==1){
    return true;
  }
  else{
    return false;
  }

}

function username_exists($username){
  $sql= "SELECT id FROM users WHERE username = '$username' ";
  $result = query($sql);
  if(row_count($result)==1){
    return true;
  }
  else{
    return false;
  }

}


function send_email($email,$subject,$message,$headers){

  return @mail($email,$subject,$message,$headers);
}



# -------------------validation function------------------------------


function validate_user_registration(){
  $errors=[];
  $min=3;
  $max=20;

  if($_SERVER['REQUEST_METHOD']== "POST"){
    $first_name= clean($_POST['first_name']);
    $last_name= clean($_POST['last_name']);
    $username= clean($_POST['username']);
    $email= clean($_POST['email']);
    $password= clean($_POST['password']);
    $confirm_password= clean($_POST['confirm_password']);

    #less than min
    if(strlen($first_name)<$min){
    $errors[] = "Your first name can not be less than {$min} characters. " ;
    }
    if(strlen($last_name)<$min){
    $errors[] = "Your last name can not be less than {$min} characters. " ;
    }
    #greater than max
    if(strlen($first_name)> $max){
    $errors[] = "Your first name can not be greater than {$max} characters. " ;
    }
    if(strlen($last_name)> $max){
    $errors[] = "Your last name can not be greater than {$max} characters. " ;
    }

    #username
    if(strlen($username)<$min){
    $errors[] = "Your username can not be less than {$min} characters. " ;
    }
    if(strlen($username)> $max){
    $errors[] = "Your username can not be greater than {$max} characters. " ;
    }
    if(username_exists( $username )){
      $errors[]= "This username already exists. ";
    }
#Email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[]= "Invalid email address. ";
    }
    if(email_exists( $email )){
      $errors[]= "This email already exist. ";
    }

    #password
    if($password !== $confirm_password ){
      $errors[]= "Your password fields do not match.";
    }
    $Pass_min=6;
    if(strlen($password)<$Pass_min){
    $errors[] = "Your password can not be less than {$Pass_min} characters. " ;
    }


#display errors
    if(!empty($errors)){
      foreach($errors as $error){
        echo validation_errors($error);

      }
    }
    else {
      if (register_user($first_name,$last_name,$username,$email,$password)) {
        set_message("<p class='bg-success text-center' >Please check your email for activation </p>");
        redirect("index.php");
      }
      else {
        set_message("<p class='bg-danger text-center' >Sorry We could not register the user . </p>");
      }
    }


  }#post method


} #function validate_user_registration

#----------------------regisiter the user-----------------------------------
function register_user($first_name,$last_name,$username,$email,$password){

$first_name=escape($first_name);
$last_name=escape($last_name);
$username=escape($username);
$email=escape($email);
$password=escape($password);


  if(email_exists( $email )){
    return false;
  }
  else if(username_exists( $username ))
  {
    return false;
  }
  else {

    $password = md5($password);
    $validation = my_random_generator().md5($email.$password);
    $sql= "INSERT INTO users(first_name,last_name,username,email,password,validation,active) VALUES('$first_name','$last_name','$username','$email','$password','$validation',0)";

  }

$result = query($sql);
confirm($result);

$subject= "Activate Account";
$message ="Please click the link below to activate your Account
http://northhall.byethost5.com/login/activate.php?email=$email&code=$validation";
$headers= "From: noreply@northhall.byethost5.com";

send_email($email,$subject,$message,$headers);

return true;
}#register_user();

#-------------------------------Activate User ------------------------------------------#
function activate_user(){
  if($_SERVER['REQUEST_METHOD']=="GET")
  {
    if (isset($_GET['email'])) {
       $email = escape(clean($_GET['email']));

       $validation_code = escape(clean($_GET['code']));

       $sql = "SELECT id FROM users WHERE email= '$email'  AND validation= '$validation_code' ";
       $result = query($sql);
       confirm($result);
       if(row_count($result)==1){
         $sql2= "UPDATE users SET active =1,validation=0 WHERE email ='$email' AND validation='$validation_code' ";
         $result2 = query($sql2);
         confirm($result2);
        set_message("<p class='bg-success text-center' >Your account has been activated.Please login.</p>");
        redirect("login.php");
      }else {
        $sql3="SELECT id FROM users WHERE email= '$email'  AND  active = 1 ";
        $result3=query($sql3);
        confirm($result3);
        if(row_count($result3)==1)
        {
          set_message("<p class='bg-success text-center' >Your account has already activated.Please login.</p>");
          redirect("login.php");
        }
        else {
          set_message("<p class='bg-danger text-center' >Sorry, Account activation has failed.Please try again </p>");
          redirect("login.php");
        }

      }
       }


  }
}#active user
#-----------------------Validate user login----------------------
function validate_user_login(){
  $errors=[];
  $min=3;
  $max=20;
  if ($_SERVER['REQUEST_METHOD']=="POST") {
    $email= clean($_POST['email']);
    $password= clean($_POST['password']);
    $remember= clean(isset($_POST['remember']));

    if(empty($email)){
      $errors[]="Email Field is empty";
    }
    if(empty($password)){
      $errors[]="Password Field is empty";
    }




    if(!empty($errors)){
      foreach($errors as $error){
        echo validation_errors($error);

      }
    }
    else {
      if(login_user($email,$password,$remember)){
        redirect("admin.php");
      }

    }
  }


}#Validate user login

#------------------------User login----------------
function login_user($email,$password,$remember){
$sql="SELECT password,id FROM users WHERE email ='".escape($email)."' AND active=1 ";
$result= query($sql);
if(row_count($result)==1){
  $row = fetch_array($result);
  $db_password = $row['password'];
  if(md5($password)===$db_password){
    if ($remember="on") {
      setcookie('email',$email,time()+86400);
    }
    $_SESSION['email']= $email;
    return true;
  }else{
    echo validation_errors("The password that you've entered is incorrect.");
    return false;
  }

}
else{
  echo validation_errors("The email address that you've entered doesn't match any account. Sign up for an account.");
  return false;
}
}#User login function

#---------------loged in function ----Session loged in ------
function logged_in(){
  if(isset($_SESSION['email'])  || isset($_COOKIE['email'])  ){
    return true ;
  }else{
    return false;
  }
}#loged in function



#-------------------------------Recover password-----------------------

function recover_password(){

  if($_SERVER['REQUEST_METHOD']=="POST"){
    if(isset($_POST['token_recover'])){
      $post_token=escape($_POST['token_recover']);
    if (isset($_SESSION['token_recover'])) {
      $session_token = escape($_SESSION['token_recover']);
      if ($post_token ===  $session_token) {



        $email = escape(clean($_POST['email']));
        if(email_exists( $email )){

          $subject="Password Reset";
          $validation_code = my_random_generator().escape(md5($email));
          setcookie('temp_access_code',$validation_code,time()+1800);
          $code = getToken(4).mt_rand(1000,9999).getToken(5);

          $sql = "UPDATE users SET validation ='$code' WHERE email='$email' ";
          $result = query($sql);
          confirm($result);



          $message= " Here is your password reset code $code . \n
          Click here to reset your password
         http://127.0.0.1/login/code.php?email=$email&&code=$validation_code";
          $headers= "From: noreply@northhall.byethost5.com";

        if (!send_email($email,$subject,$message,$headers)) {

          echo  validation_errors("Rocovering password has failed .Please try again");


        }#send_email
        else {
          // send email has failed
          set_message("<p class='bg-success text-center' >Email has sent. Please check Your email .</p>");
          redirect("index.php");
        }
        $message= " Here is your password reset code $code . \n
        Click here to reset your password \n
       http://northhall.byethost5.com/login/code.php?email=$email&&code=$validation_code";
        echo $message;// Just for checking unitil mail works;----------------//???????????????????????????????!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!------------
        }#email check
        else {
          // email does not exist;
          echo  validation_errors("This email does not exist");
        }

      }//session  token ===post token;
      else {

        redirect("index.php");
      }
    } #end if session token set check

}//isset post token;
if (isset($_POST['cancel_submit'])) {
  redirect("login.php");
}

  }#end if -post check
}#recover_password()


#-------------------code.php  validation function-------------
function validate_code(){
  if (isset($_COOKIE['temp_access_code'])) {

      if (!isset($_GET['email']) && !isset($_GET['code'])) {
        rediect("index.php");
      } #email and validation code check not-isset
      elseif (empty($_GET['email']) || empty($_GET['code'])) {
        rediect("index.php");
      }//empty check
      elseif($_GET['code']===$_COOKIE['temp_access_code']) {#every thing is set
        $email = escape(clean($_GET['email']));

        if (isset($_POST['code'])) {
            $code = escape(clean($_POST['code']));
            $sql = "SELECT id FROM users WHERE email='$email' AND validation='$code' ";
            $result = query($sql);
            if(row_count($result)==1){
              $validation_code=my_random_generator();
              setcookie('temp_access_code',$validation_code,time()+600);
              redirect("reset.php?email=$email&code=$validation_code");
            }

        }#post code  set

      } #every thing is set
      else {
        set_message("<p class='bg-danger text-center' >Sorry ,Password recover link has expired. please try again .</p>");
        redirect("recover.php");
      }


  }else {#cookie
    set_message("<p class='bg-danger text-center' >Sorry ,Password recover link has expired. please try again .</p>");
    redirect("recover.php");
  }
}


#-------------------reset.php  reset password  function-------------
function password_reset(){
    if (isset($_GET['email']) && isset($_GET['code']) && isset($_COOKIE['temp_access_code']) && $_GET['code']===$_COOKIE['temp_access_code'] ) {
      if($_SERVER['REQUEST_METHOD']=="POST"){

      if (isset($_SESSION['token_recover']) && isset($_POST['token_reset']) && $_SESSION['token_recover']===$_POST['token_reset'] ) {
        $email = escape(clean($_GET['email']));
        $code = escape(clean($_GET['code']));
        $password=escape(clean($_POST['password']));
        $confirm_password= escape(clean($_POST['confirm_password']));

        if ($password===$confirm_password) {
          $Pass_min=6;
          if(strlen($password)<$Pass_min){
          $errors= "Your password can not be less than {$Pass_min} characters. " ;
          echo validation_errors($errors);
          }
          else {
            $password=md5($password);
            $sql = "UPDATE users SET password ='$password', validation=0 WHERE email='$email' ";
            $result=query($sql);
            set_message("<p class='bg-success text-center' >Password has changed successfuly .</p>");
            if($result){

              setcookie('temp_access_code',$code,time()-600);
              redirect("login.php");

            }
          }



        }
        else {
          echo validation_errors("Your password field do not match");
        }




      }#token same check
      else {

        redirect("index.php");
      }
}
  }else {
    set_message("<p class='bg-danger text-center' >Sorry ,Password recover link has expired. please try again .</p>");
    redirect("recover.php");
  }
}
?>
