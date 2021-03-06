<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Googlelogin extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
	}
	public function index()
	{
		$this->googleLoginProcess();
	}

	/* Job seeker login, registraiton and forgot password start*/

	function googleLoginProcess()
	{

		$getFileNameArray = explode('/',$profile_image_url);

		$fileNameDetails = $getFileNameArray[7];
			
		$url = $twConnectId->profile_image_url;
		$img = 'images/users/'.$fileNameDetails ;
		file_put_contents($img, file_get_contents($url));


		$url = $profile_image_url;
		$img = 'images/users/'.$fileNameDetails ;
		file_put_contents($img, file_get_contents($url));

		/*@mysql_query("INSERT INTO google_users (api_id, full_name,email, thumbnail) VALUES ($user_id, '$user_name','$email','$fileNameDetails')");*/

		$google_login_details = array('social_login_name'=>$user_name,'social_login_unique_id'=>$user_id,'screen_name'=>$user_name,'social_image_name'=>$fileNameDetails);

		$_SESSION['social_login_name']=$user_name;
		$_SESSION['social_login_unique_id']=$user_id;
		$_SESSION['screen_name']=$user_name;
		$_SESSION['social_image_name']=$fileNameDetails;
		//redirect('signup');
		
		header( 'Location: '.$originalBasePath.'signup' );

	}

	function googleRedirect()
	{

		require_once 'google-login-mats/index.php';

		$user_name  = '';
		$email = '';
		if (isset($_GET['code']))
		{
			$gClient->authenticate($_GET['code']);
			$_SESSION['token'] = $gClient->getAccessToken();
			//header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL));
			return;
		}


		if (isset($_SESSION['token']))
		{
			$gClient->setAccessToken($_SESSION['token']);
		}


		if ($gClient->getAccessToken())
		{
			//Get user details if user is logged in
			$user 				= $google_oauthV2->userinfo->get();
			// print_r($user);
			// echo filter_var($user['name']);
			// echo $user['picture'];
			// echo "1".filter_var($user['link'], FILTER_VALIDATE_URL);
			
			// die;
			
			$user_id 				= $user['id'];
			$user_name 			= filter_var($user['name'], FILTER_SANITIZE_SPECIAL_CHARS);
			$email 				= filter_var($user['email'], FILTER_SANITIZE_EMAIL);
			$profile_url 			= filter_var($user['link'], FILTER_VALIDATE_URL);
			$profile_image_url 	= $user['picture'];
			$personMarkup 		= $email."<div><img src='".$profile_image_url."?sz=50'></div>";
			if($profile_image_url!=""){
				$image = $user_id.'.jpg';
				$profile_image = file_get_contents($profile_image_url); // sets $image to the contents of the url
				file_put_contents('images/users/'.$user_id.'.jpg', $profile_image); // places the contents in the file /path/image.gif
	        } else {
	        	$image = 'profile.png';
	        }
//print_r($email); 
        /*$this->db->cache_on();
		$this->db->select('*');
		$this->db->from(USERS);
		$this->db->where('email',$email);
		$this->db->where('loginUserType','normal');
        $result = $this->db->get();	
		if($result->num_rows () > 0)
		{
			echo "Already Exists";
			
		}
		else{
			//$this->db->insert('fc_users');
			
			//$this->user_model->update_details (USERS);
			echo "New";
			
		}*/
//exit;
				
			$_SESSION['token'] 	= $gClient->getAccessToken();
		}
		else
		{
			//get google login url
			
			$authUrl = $gClient->createAuthUrl();
			//print_r($authUrl); exit;
		}





			
		if($email != '')
		{
			$googleLoginCheck = $this->user_model->googleLoginCheck($email);

				
			if($googleLoginCheck > 0)
			{
				//echo "login";
				$getGoogleLoginDetails = $this->user_model->google_user_login_details($email);
				//echo "<pre>";print_r($getGoogleLoginDetails);die;

				$userdata = array(
							'fc_session_user_id' => $getGoogleLoginDetails['id'],
							
							'session_user_email' => $getGoogleLoginDetails['email'],

							'login_type' => 'google'
				);
				//echo "<pre>";print_r($userdata);die;
				echo $this->session->set_userdata($userdata);
				
				if($this->data['login_succ_msg'] != '')
				$lg_err_msg = $this->data['login_succ_msg'];
			    
				else
				{
					//$condition = array('id' => $this->checkLogin ( 'U' ));
					$condition = array('id' => $getGoogleLoginDetails['id']);
					//$dataArr = array('google_id'=>$user_id,'image'=>$image,'status'=>'Active','expired_date'=>$expireddate,'is_verified'=>'Yes','google'=>'Yes','created'=>date('Y-m-d H:i:s'));
					$dataArr = array('google_id'=>$user_id,'status'=>'Active','expired_date'=>$expireddate,'is_verified'=>'Yes','google'=>'Yes');
				    $this->user_model->update_details(USERS,$dataArr,$condition);
				}
				$lg_err_msg = 'You are Logged In ...';
				$this->setErrorMessage('success',$lg_err_msg);
				//print_r($authUrl); 
				if($_SESSION['currentpage_url'] !="") {
					redirect($_SESSION['currentpage_url']);
				}
				redirect(base_url());
			}
			else
			{

				$google_login_details = array('social_login_name'=>$user_name,'social_login_unique_id'=>'','screen_name'=>$user_name,'social_image_name'=>'','social_email_name'=>$email,'loginUserType'=>'google');
				//echo "<pre>";print_r($google_login_details);die;
				//echo "redirect to registration page";
				$social_login_name = $user_name;
				$this->session->set_userdata($google_login_details);
				
				
				$firstname = $user_name;
				$lastname = '';
				$orgPass = time();
				$pwd = md5($orgPass);
				$Confirmpwd = $orgPass;
				$username = $user_name;
				
				/* Referal User id insert */
				if(isset($_SESSION['inviterID']))
					$inivte_reference = $_SESSION['inviterID']; 
				else 
					$inivte_reference = '0';
				/* Referal User id insert ends */

				$condition = array ('email' => $email);
				$duplicateMail = $this->user_model->get_all_details ( USERS, $condition );
				//print_r('loginUserType'); exit;
				$expireddate = date ( 'Y-m-d', strtotime ( '+15 days' ) );
				
					$dataArr = array('google_id'=>$user_id,'firstname'=>$firstname,'lastname'=>$lastname,'user_name'=>$firstname,'group'=>'User','image'=>$image,'email'=>$email,'password'=>$pwd,'status'=>'Active','expired_date'=>$expireddate,'is_verified'=>'No','loginUserType'=>'google','google'=>'Yes','created'=>date('Y-m-d H:i:s'),'referId'=> $inivte_reference);
					$this->user_model->simple_insert(USERS,$dataArr);
				
				$lstID = $this->db->insert_id();
				//echo $this->db->last_query(); die;
				$userdata = array (
						'quick_user_name' => $firstname,
						'quick_user_email' => $email,
						'fc_session_user_id' => $lstID,
						'session_user_email' => $email,
						'login_type' => 'google'
				);
				$this->session->set_userdata ( $userdata );
				
				$this->setErrorMessage('success','Registered & Login Successfully');
				if($_SESSION['currentpage_url'] !="") {
					redirect($_SESSION['currentpage_url']);
				}
				redirect(base_url());
				
				
			}
		}
		else
		{
			
			if($_SESSION['currentpage_url'] !="") {
					redirect($_SESSION['currentpage_url']);
				}
				redirect(base_url());
		}
	}

	function facebookRedirect()
	{
		@session_start();
		//echo '<pre>'; print_r($_SESSION);
		//echo $_SESSION['email'];die;

		if($_SESSION['email'] !='')
		{
			$facebookLoginCheck = $this->user_model->googleLoginCheck($_SESSION['email']);
			//echo $this->db->last_query();
			//echo "<pre>";print_r($facebookLoginCheck);
			if($facebookLoginCheck > 0)
			{
				//echo "login";
				$getFacebookLoginDetails = $this->user_model->google_user_login_details($_SESSION['email']);
				
				$userdata = array(
							'fc_session_user_id' => $getFacebookLoginDetails['id'],
							'session_user_email' => $getFacebookLoginDetails['email'] 
				);
				//echo "<pre>";print_r($userdata);die;
				$this->session->set_userdata($userdata);
				
				$this->setErrorMessage('success','Login successfully');
				redirect(base_url());
			}
			else
			{

				$google_login_details = array('social_login_name'=>$_SESSION['first_name'],'social_login_unique_id'=>'','screen_name'=>$_SESSION['first_name'],'social_image_name'=>$_SESSION['fb_image_name'],'social_email_name'=>$_SESSION['email'],'loginUserType'=>'facebook');
					
				$social_login_name = $_SESSION['first_name'];
				$this->session->set_userdata($google_login_details);
				
				$firstname = $_SESSION['first_name'];
				$lastname = $_SESSION['last_name'];
				$email = $_SESSION['email'];
				$fb_image_name = $_SESSION['fb_image_name'];
				$orgPass = time();
				$pwd = md5($orgPass);
				$Confirmpwd = $orgPass;
				$username = stripslashes($_SESSION['first_name'].trim());
		
				/* Referal User id insert */
				if(isset($_SESSION['inviterID']))
					$inivte_reference = $_SESSION['inviterID']; 
				else 
					$inivte_reference = '0';
				/* Referal User id insert ends */
		
				$condition = array ('email' => $email);
				$duplicateMail = $this->user_model->get_all_details ( USERS, $condition );
				
				$expireddate = date ( 'Y-m-d', strtotime ( '+15 days' ) );
				
				$dataArr = array('firstname'=>$firstname,'lastname'=>$lastname,'user_name'=>$firstname,'image'=>$fb_image_name,'group'=>'User','email'=>$email,'password'=>$pwd,'status'=>'Active','expired_date'=>$expireddate,'loginUserType'=>'facebook','is_verified'=>'No','created'=>date('Y-m-d H:i:s'),'referId'=> $inivte_reference);
				
				$this->user_model->simple_insert(USERS,$dataArr);
				
				$lstID = $this->db->insert_id();
				
				$userdata = array (
						'quick_user_name' => $firstname,
						'quick_user_email' => $email,
						'fc_session_user_id' => $lstID,
						'session_user_email' => $email ,
						'facebook_type' => facebook
				);
				$this->session->set_userdata ( $userdata );
				
				$this->setErrorMessage('success','Registered & Login Successfully');
				redirect(base_url());
				
					
			}
		}
		else
		{
			//redirect('');
			$authUrl = $gClient->createAuthUrl();
		}


		//echo "<pre>";print_r($_REQUEST);die;
		//echo "hi";die;
	}




}