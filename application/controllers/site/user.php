<?php if (! defined ( 'BASEPATH' ))exit ( 'No direct script access allowed' );

/**
 *
 * User related functions
 * 
 * @author Teamtweaks
 *        
 */
class User extends MY_Controller {
	function __construct() {
		// echo "<pre>";print_r($_REQUEST);echo "</pre>";// die;
		parent::__construct ();
		$this->load->helper ( array (
				'cookie',
				'date',
				'form',
				'email',
				'url' 
		) );
		// $this->load->library ( array (
		// 		'encrypt',
		// 		'form_validation',
		// 		'linkedin',
		// 		'session',
		// 		'email'
		// ) );
		$this->load->model ( array (
				'user_model',
				'product_model',
				'contact_model',
				'checkout_model',
				'order_model'				
		) );
		if ($_SESSION ['sMainCategories'] == '') {
			$sortArr1 = array (
					'field' => 'cat_position',
					'type' => 'asc' 
			);
			$sortArr = array (
					$sortArr1 
			);
			$_SESSION ['sMainCategories'] = $this->product_model->get_all_details ( CATEGORY, array (
					'rootID' => '0',
					'status' => 'Active' 
			), $sortArr );
		}
		$this->data ['mainCategories'] = $_SESSION ['sMainCategories'];
		
		if ($_SESSION ['sColorLists'] == '') {
			$_SESSION ['sColorLists'] = $this->user_model->get_all_details ( LIST_VALUES, array (
					'list_id' => '1' 
			) );
		}
		$this->data ['mainColorLists'] = $_SESSION ['sColorLists'];
		
		$this->data ['loginCheck'] = $this->checkLogin ( 'U' );
		$this->data ['likedProducts'] = array ();
		if ($this->data ['loginCheck'] != '') {
			$this->data ['WishlistUserDetails'] = $this->user_model->get_all_details ( USERS, array (
					'id' => $this->checkLogin ( 'U' ) 
			) );
			$this->data ['likedProducts'] = $this->user_model->get_all_details ( PRODUCT_LIKES, array (
					'user_id' => $this->checkLogin ( 'U' ) 
			) );
		}
	}
	
	/**
	 * Function for quick signup
	 */
	public function quickSignup() { 
		$email = $this->input->post ( 'email' );
		$returnStr ['success'] = '0';
		if (valid_email ( $email )) {
			$condition = array (
					'email' => $email 
			);
			$duplicateMail = $this->user_model->get_all_details ( USERS, $condition );
			if ($duplicateMail->num_rows () > 0) {
				$returnStr ['msg'] = 'Email id already exists';
			} else {
				$fullname = substr ( $email, 0, strpos ( $email, '@' ) );
				$checkAvail = $this->user_model->get_all_details ( USERS, array (
						'user_name' => $fullname 
				) );
				if ($checkAvail->num_rows () > 0) {
					$avail = FALSE;
				} else {
					$avail = TRUE;
					$username = $fullname;
				}
				while ( ! $avail ) {
					$username = $fullname . rand ( 1111, 999999 );
					$checkAvail = $this->user_model->get_all_details ( USERS, array (
							'user_name' => $username 
					) );
					if ($checkAvail->num_rows () > 0) {
						$avail = FALSE;
					} else {
						$avail = TRUE;
					}
				}
				if ($avail) {
					$pwd = $this->get_rand_str ( '6' );
					$this->user_model->insertUserQuick ( $fullname, $username, $email, $pwd );
					$this->session->set_userdata ( 'quick_user_email', $email );
					$returnStr ['msg'] = 'Successfully registered';
					$returnStr ['full_name'] = $fullname;
					$returnStr ['user_name'] = $username;
					$returnStr ['password'] = $pwd;
					$returnStr ['email'] = $email;
					$returnStr ['success'] = '1';
				}
			}
		} else {
			$returnStr ['msg'] = "Invalid email id";
		}
		echo json_encode ( $returnStr );
	}
	
	/**
	 * Function for quick signup update
	 */
	public function quickSignupUpdate() {
		$returnStr ['success'] = '0';
		$unameArr = $this->config->item ( 'unameArr' );
		$username = $this->input->post ( 'username' );
		if (! preg_match ( '/^\w{1,}$/', trim ( $username ) )) {
			$returnStr ['msg'] = 'User name not valid. Only alphanumeric allowed';
		} elseif (in_array ( $username, $unameArr )) {
			$returnStr ['msg'] = 'User name already exists';
		} else {
			$email = $this->input->post ( 'email' );
			$condition = array (
					'user_name' => $username,
					'email !=' => $email 
			);
			$duplicateName = $this->user_model->get_all_details ( USERS, $condition );
			if ($duplicateName->num_rows () > 0) {
				$returnStr ['msg'] = 'Username already exists';
			} else {
				$pwd = $this->input->post ( 'password' );
				$fullname = $this->input->post ( 'fullname' );
				$this->user_model->updateUserQuick ( $fullname, $username, $email, $pwd );
				$this->session->set_userdata ( 'quick_user_name', $username );
				$returnStr ['msg'] = 'Successfully registered';
				$returnStr ['success'] = '1';
			}
		}
		echo json_encode ( $returnStr );
	}
	public function send_quick_register_mail() {
		if ($this->checkLogin ( 'U' ) != '') {
			redirect ( base_url () );
		} else {
			
			$quick_user_name = $this->session->userdata ( 'quick_user_email' );
			if ($quick_user_name == '') {
				redirect ( base_url () );
			} else {
				$condition = array (
						'email' => $quick_user_name 
				);
				$userDetails = $this->user_model->get_all_details ( USERS, $condition );
				
				
				
				if ($userDetails->num_rows () == 1) {
					$this->send_confirm_mail ( $userDetails );
					if(stripslashes($this->lang->line('reg_success')) != '') {
					 $this->setErrorMessage('success',stripslashes($this->lang->line('reg_success')));
					 }else{ 
					$this->setErrorMessage('success','Registration  Successfully Completed. Please Check Your Mail to Verify Registration.');
					 }
					// $this->setErrorMessage ( 'success', 'Registration  Successfully Completed. Please Check Your Mail to Verify Registration.' );
					redirect ( base_url () );
				} else {
					if(stripslashes($this->lang->line('reg_verify')) != '') {
					 $this->setErrorMessage('success',stripslashes($this->lang->line('reg_verify')));
					 }else{ 
					$this->setErrorMessage('success','Please Check Your Mail to Verify Registration.');
					 }
					redirect ( base_url () );
				}
			}
		}
	}
	public function registerUser1() {
		$returnStr ['success'] = '0';
		$unameArr = $this->config->item ( 'unameArr' );
		$fullname = $this->input->post ( 'fullname' );
		$username = $this->input->post ( 'username' );
		$thumbnail = $this->input->post ( 'thumbnail' );
		/*
		 * if (!preg_match('/^\w{1,}$/', trim($username))){
		 * $returnStr['msg'] = 'User name not valid. Only alphanumeric allowed';
		 * }elseif (in_array($username, $unameArr)){
		 * $returnStr['msg'] = 'User name already exists';
		 * }else {
		 */
		$email = $this->input->post ( 'email' );
		$pwd = $this->input->post ( 'pwd' );
		/*
		 * $brand = $this->input->post('news_signup');
		 * if (valid_email($email)){
		 * $condition = array('user_name'=>$username);
		 * $duplicateName = $this->user_model->get_all_details(USERS,$condition);
		 * if ($duplicateName->num_rows()>0){
		 * $returnStr['msg'] = 'User name already exists';
		 * }else {
		 * $condition = array('email'=>$email);
		 * $duplicateMail = $this->user_model->get_all_details(USERS,$condition);
		 * if ($duplicateMail->num_rows()>0){
		 * $returnStr['msg'] = 'Email id already exists';
		 * }else {
		 */
		
		$this->user_model->insertUserQuick_social ( $fullname, $username, $email, $pwd, $thumbnail );
		$this->session->set_userdata ( 'quick_user_email', $email );
		$returnStr ['msg'] = 'Successfully registered';
		$returnStr ['success'] = '1';
		/*
		 * }
		 * }
		 * }else {
		 * $returnStr['msg'] = "Invalid email id";
		 * }
		 */
		// }
		echo json_encode ( $returnStr );
	}
	public function registerUser() {
	$returnStr ['success'] = '0';
		$firstname = $this->input->post ( 'firstname' );
		$lastname = $this->input->post ( 'lastname' );
		$email = $this->input->post ( 'email' );
		$pwd = md5($this->input->post ( 'pwd' ));
		$datefrom = $this->input->post ( 'datefrom' );
		$expiredate = $this->input->post ( 'expiredate' );
		$number_of_guests = $this->input->post ( 'number_of_guests' );
		
		
		//$rep_code = $this->input->post('rep_code');
		$rep_code = '';
		
		$repcode_id =1;
		
		$confirm_password = $this->input->post ( 'pwd' );

		$invite_reference = $this->input->post('invite_reference');
		//echo $rep_code;
		//die;
		$image = 'profile.png';
		$news_signup = $this->input->post ( 'news_signup' );
		//print_r(trim($rep_code));
		//die;
		if(trim($rep_code)!='')
		{
			
			
		$repcode = $this->user_model->get_rep_details(trim($rep_code)); 
		//print_r($repcode);
		//die;
			if (count($repcode)== 0) {
				$returnStr ['msgs'] = 'Does not Support Rep Code'; 
				//$this->setErrorMessage('success','Email id already exists!');
				echo json_encode ( $returnStr );
				
				exit;
			}
		}
		
		if (valid_email ( $email )) {
			$condition = array (
					'email' => $email
					
					
			);
			/* if($rep_code)
			{
			$condition_rep = array (
					
					'admin_rep_code' => $rep_code
					
			);*/
			$duplicateMail = $this->user_model->get_all_details( USERS, $condition );
			if ($duplicateMail->num_rows () > 0) {
				$returnStr ['msg'] = 'Email id already exists'; 
				//$this->setErrorMessage('success','Email id already exists!');
				echo json_encode ( $returnStr );
				
				exit;
			} 
			
			/*$repcode = $this->user_model->get_all_details( SUBADMIN, $condition_rep ); 
			if ($repcode->num_rows () > 0) {
				$returnStr ['msg'] = 'Does not Support Rep Code'; 
				//$this->setErrorMessage('success','Email id already exists!');
				echo json_encode ( $returnStr );
				
				exit;
			}*/
			
			else {
				$image = 'profile.png';
	            $expireddate = date ( 'Y-m-d', strtotime ( '+15 days' ) );
				
			
				$this->user_model->insertUserQuick ( $firstname, $lastname, $email, $pwd, $image, $rep_code, $repcode_id, $confirm_password, $news_signup, $expireddate);
				$this->session->set_userdata ( 'quick_user_name', $firstname );
				
				$this->session->set_userdata ( 'quick_user_email', $email );
				
				$usrDetails = $this->user_model->get_all_details ( USERS, $condition );
 /* Mail function */ 

                        $newsid='35';

			$template_values=$this->product_model->get_newsletter_template_details($newsid);
			if($template_values['sender_name']=='' && $template_values['sender_email']==''){
				$sender_email=$this->data['siteContactMail'];
				$sender_name=$this->data['siteTitle'];
			}else{
				$sender_name=$template_values['sender_name'];
				$sender_email=$template_values['sender_email'];
			} 
                          
                                      $username = $firstname.$lastname;	
$uid = $usrDetails->row ()->id;
		$username = $usrDetails->row ()->user_name;
		$email = $usrDetails->row ()->email;
		
		$randStr = $this->get_rand_str ( '10' );

		$cfmurl = base_url () . 'site/user/confirm_verify/' . $uid . "/" . $randStr . "/confirmation";
		$logo_mail = $this->data['logo'];
                                 
                        $email_values = array(
					'from_mail_id'=>$sender_email,
					'to_mail_id'=> $this->input->post ( 'email' ),
					'subject_message'=>$template_values ['news_subject'],
					'body_messages'=>$message
			);  
$reg= array('username' => $username, 'cfmurl'=>$cfmurl, 'email_title' => $sender_name,'logo'=>$logo_mail );
           //print_r($this->data['logo']);
            $message = $this->load->view('newsletter/RegistrationConfirmation'.$newsid.'.php',$reg,TRUE);

            
            //send mail
            $this->load->library('email');
            $this->email->from($email_values['from_mail_id'], $sender_name);
            $this->email->to($email_values['to_mail_id']);
            $this->email->subject($email_values['subject_message']);
            $this->email->set_mailtype("html");
           
                        
                        $this->email->message($message); 
                        try{
                        $this->email->send();
                        $returnStr ['msg'] = 'Successfully registered';
			$returnStr ['success'] = '1';
                        }catch(Exception $e){
                        echo $e->getMessage();
                        }                   
                        
                        /* Mail function End */          
		
                        
                      /* Admin Mail function */ 

            $newsid='42';

			$template_values=$this->product_model->get_newsletter_template_details($newsid);
			if($template_values['sender_name']=='' && $template_values['sender_email']==''){
				$sender_email=$this->data['siteContactMail'];
				$sender_name=$this->data['siteTitle'];
			}else{
				$sender_name=$template_values['sender_name'];
				$sender_email=$template_values['sender_email'];
			} 
                          
                                      $username = $firstname.$lastname;	
$uid = $usrDetails->row ()->id;
		$username = $usrDetails->row ()->user_name;
		$email = $usrDetails->row ()->email;
		$randStr = $this->get_rand_str ( '10' );

		$cfmurl = 'There is one new registration done on website. User details below.';
		$logo_mail = $this->data['logo'];
                                 
                        $email_values = array(
					'from_mail_id'=>$this->input->post('email'),
					'to_mail_id'=> $sender_email,
					'subject_message'=>$template_values ['news_subject'],
					'body_messages'=>$message
			);  
$reg= array('username' => $username, 'email'=> $email, 'cfmurl'=>$cfmurl, 'email_title' => $sender_name,'logo'=>$logo_mail );
           //print_r($this->data['logo']);
            $message = $this->load->view('newsletter/RegistrationAdminConfirmation'.$newsid.'.php',$reg,TRUE);

            
            //send mail
            $this->load->library('email');
            $this->email->from($email_values['from_mail_id'], $sender_name);
            $this->email->to($email_values['to_mail_id']);
            $this->email->subject($email_values['subject_message']);
            $this->email->set_mailtype("html");
           
                        
                        $this->email->message($message); 
                        try{
                        $this->email->send();
                        $returnStr ['msg'] = 'Successfully registered';
			$returnStr ['success'] = '1';
                        }catch(Exception $e){
                        echo $e->getMessage();
                        }                   
                        
                        /* Admin Mail function End */ 

				//$this->send_confirm_mail ( $usrDetails );
				
				//die;
			
				
				//$this->login_user();
				

			}
			$email = $this->input->post ( 'email' );
			$pwd = md5($this->input->post ( 'pwd' ));
			if (valid_email ( $email )) {
			$condition = array (
					'email' => $email
					 
			);
			
			$checkUser = $this->user_model->get_all_details(USERS, $condition);
			if ($checkUser->num_rows () == '1') {
				$userdata = array (
						'fc_session_user_id' => $checkUser->row ()->id,
						
						'session_user_email' => $checkUser->row ()->email,
                           'loginUserType' => 'normal', 
'datefrom' => $datefrom,
						'expiredate' => $expiredate,
						'number_of_guests' => $number_of_guests	
				);
				$this->session->set_userdata ( $userdata );
				$datestring = "%Y-%m-%d %h:%i:%s";
				$time = time ();
				$newdata = array (
						'last_login_date' => mdate ( $datestring, $time ),
						'last_login_ip' => $this->input->ip_address () 
				);
				$condition = array (
						'id' => $checkUser->row ()->id 
				);
				$this->user_model->update_details ( USERS, $newdata, $condition );
				if ($remember != '') {
					$userid = $this->encrypt->encode ( $checkUser->row ()->id );
					$cookie = array (
							'name' => 'admin_session',
							'value' => $userid,
							'expire' => 86400,
							'secure' => FALSE 
					);
					$this->input->set_cookie ( $cookie );
				}
				if(stripslashes($this->lang->line('login_success')) != '') {
				 $this->setErrorMessage('success',stripslashes($this->lang->line('login_success')));
				 }else{ 
				$this->setErrorMessage('success','You are successfully Registered!');
				 }
			}else{ 
			$returnStr ['msg'] = 'Successfully failed 1';
			}
			}else{
			$returnStr ['msg'] = 'Successfully failed 2';
			}	
		}
		//} 
		else {
			$returnStr ['msg'] = "Email id already exists";
			//$returnStr ['msg'] = "Does not Support Rep Code";
		}
		
		
		echo json_encode ( $returnStr );
		
	}
	
	
	
	public function registerUser_bck(){
	
		$returnStr['success'] = '0';
		$firstname = $this->input->post ( 'firstname' );
		$lastname = $this->input->post ( 'lastname' );
		$email = $this->input->post ( 'email' );
		$pwd = $this->input->post ( 'pwd' );
		//echo '<script>alert(hai)</script>'; die;
		//$news_signup = $this->input->post ( 'news_signup' );
		//$news_signup = $this->input->post('news_signup');
			if (valid_email($email)){
					$condition = array('email'=>$email);
					$duplicateMail = $this->user_model->get_all_details(USERS,$condition);
						if ($duplicateMail->num_rows()>0){
							if($this->lang->line('Email id already exists!') != '') 
							{ 
								$message = stripslashes($this->lang->line('Email id already exists!')); 
							} 
							else 
							{
								$message = "Email id already exists!";
							}
								$this->setErrorMessage('error',$message);
								redirect('sign_up');
						}else {
							
						
						$returnMail = $this->user_model->get_all_details(USERS,$condition);
						//echo "<pre>"; print_r($returnMail->result_array());		 die;
						if($returnMail->num_rows()>0){
							
							if($this->lang->line('Welcome back, Thanks for registering again') != '') 
							{ 
								$message = stripslashes($this->lang->line('Welcome back, Thanks for registering again')); 
							} 
							else 
							{
								$message = "Welcome back, Thanks for registering again";
							}

								$this->setErrorMessage('success',$message);
							
							}
							$this->user_model->insertUserQuick($firstname,$lastname,$email,$pwd);
							$this->session->set_userdata('quick_user_name',$firstname);
							$usrDetails = $this->user_model->get_all_details(USERS,$condition);
							$this->send_confirm_mail($usrDetails);
							if($this->lang->line('Successfully registered') != '') 
							{ 
								$message = stripslashes($this->lang->line('Successfully registered')); 
							} 
							else 
							{
								$message = "Successfully registered";
							}

								$this->setErrorMessage('success',$message);
							
							//$returnStr['success'] = '1';
							
							/* auto login */
							
							$returnStr['status_code'] = 0;
							$returnStr['message'] = 'welcome';
			
			
									 $email = $this->input->post('email'); 
									// print_r($email);  die;
									 
									$pwd = md5($this->input->post('pwd'));
									//$remember = $this->input->post('remember');
									
									if (valid_email($email)){
									$condition = array('email'=>$email,'password'=>$pwd,'status'=>'Active');
									$checkUser = $this->user_model->get_all_details(USERS,$condition);
									 $str = $this->db->last_query(); 
									if ($checkUser->num_rows() == '1')
										{
											$userdata = array(
														'fc_session_user_id' => $checkUser->row()->id,
														'dhdy_session_user_id' => $checkUser->row()->id,
														'session_user_email' => $checkUser->row()->email
														);
										$this->session->set_userdata($userdata);
										$datestring = "%Y-%m-%d %h:%i:%s";
										$time = time();
										$newdata = array(
													'last_login_date' => mdate($datestring,$time),
													'last_login_ip' => $this->input->ip_address(),
													'commision'=>$this->config->item('guide_commission')
													);
										$condition = array('id' => $checkUser->row()->id);
										$this->user_model->update_details(USERS,$newdata,$condition);
										if ($remember != ''){
											$userid = $this->encrypt->encode($checkUser->row()->id);
											$cookie = array(
												'name'   => 'admin_session',
												'value'  => $userid,
												'expire' => 86400,
												'secure' => FALSE
											);
											$this->input->set_cookie($cookie);
										}
										if($this->lang->line('Welcome back!') != '') 
										{ 
											$message = stripslashes($this->lang->line('Welcome back!')); 
										} 
										else 
										{
											$message = "Welcome back!";
										}
											$this->setErrorMessage('success',$message);
											$returnStr['status_code'] = 1;
											redirect(base_url());
										
									}else {
										
									if($this->lang->line('User Profile Information Updated successfully') != '') 
									{ 
										$message = stripslashes($this->lang->line('User Profile Information Updated successfully')); 
									} 
									else 
									{
										$message = "User Profile Information Updated successfully";
									}

										$this->setErrorMessage('error',$message);
									}
			}
			else {
				if($this->lang->line('Invalid email id') != '') 
				{ 
					$message = stripslashes($this->lang->line('Invalid email id')); 
				} 
				else 
				{
					$message = "Invalid email id";
				}

					$this->setErrorMessage('error',$message);
				
			}
							/* auto login End */	
						}
			}else {
				if($this->lang->line('Invalid email id') != '') 
				{ 
					$message = stripslashes($this->lang->line('Invalid email id')); 
				} 
				else 
				{
					$message = "Invalid email id";
				}
					$this->setErrorMessage('error',$message);
			}

		redirect(base_url());
	}
	
	public function resend_confirm_mail() {
		$mail = $this->input->post ( 'mail' );
		if ($mail == '') {
			echo '0';
		} else {
			$condition = array (
					'email' => $mail 
			);
			$userDetails = $this->user_model->get_all_details ( USERS, $condition );
			$this->send_confirm_mail ( $userDetails );
			echo '1';
		}
	}
	public function dashboard_resend_confirm_mail() {
		$mail = $this->data ['userDetails']->row ()->email;
		if ($mail != '') {
			
			$condition = array (
					'email' => $mail 
			);
			$userDetails = $this->user_model->get_all_details (USERS,$condition );
			$this->send_confirm_mail ( $userDetails );
			if(stripslashes($this->lang->line('reg_verify')) != '') {
			 $this->setErrorMessage('success',stripslashes($this->lang->line('reg_verify')));
			 }else{ 
			$this->setErrorMessage('success','Please Check Your Mail to Verify Registration.');
			 }
			redirect ( 'dashboard' );
		}
	}
	public function send_email_confirmation() {
		$returnStr ['status_code'] = 0;
		if ($this->checkLogin ( 'U' ) == '') {
			$returnStr ['message'] = 'Login required';
		} else {
			$this->send_confirm_mail ( $this->data ['userDetails'] );
			$returnStr ['status_code'] = 1;
		}
		echo json_encode ( $returnStr );
	}
	public function send_confirm_mail($userDetails = '') {	
	    
		$uid = $userDetails->row ()->id;
		$email = $userDetails->row ()->email;
		$name = $userDetails->row ()->firstname."    ".$userDetails->row ()->lastname;
		
		$randStr = $this->get_rand_str ('10');
		$condition = array (
				'id' => $uid 
		);
		$dataArr = array (
				'verify_code' => $randStr 
		);
		$this->user_model->update_details ( USERS, $dataArr, $condition );
		
		$newsid = '35';
		$template_values = $this->user_model->get_newsletter_template_details( $newsid );
		
		$user=$userDetails->row ()->firstname."     ".$userDetails->row ()->lastname;
		$cfmurl = base_url () . 'site/user/confirm_register/' . $uid . "/" . $randStr . "/confirmation";
		$subject = 'From: ' . $this->config->item ( 'email_title' ) . ' - ' . $template_values ['news_subject'];
		$adminnewstemplateArr = array (
				'email_title' => $this->config->item ('email_title'),
				'logo' => $this->data ['logo'],
				'username'=>$name
		);
		extract ( $adminnewstemplateArr );
		$header .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
		
		$message .= '<body>';
		include ('./newsletter/registeration' . $newsid . '.php');
		
		$message .= '</body>
			';
		
		if ($template_values ['sender_name'] == '' && $template_values ['sender_email'] == '') {
			$sender_email = $this->data ['siteContactMail'];
			$sender_name = $this->data ['siteTitle'];
		} else {
			$sender_name = $template_values ['sender_name'];
			$sender_email = $template_values ['sender_email'];
		}
		$email_values = array (
				'mail_type' => 'html',
				'from_mail_id' => $sender_email,
				'mail_name' => $sender_name,
				'to_mail_id' => $email,
				'subject_message' => $template_values ['news_subject'],
				'body_messages' => trim($message)
		);
                       
                        $this->email->set_mailtype($email_values['mail_type']);
                        $this->email->from($email_values['from_mail_id'], $sender_name);
                        $this->email->to($email_values['to_mail_id'], $email_values['mail_name']);
                        $this->email->subject($email_values['subject_message']);
                        $this->email->message($email_values['body_messages']); 
                        try{
                        $this->email->send();
						if($this->lang->line('Your registration successfully completed.') != '') 
						{ 
							$message = stripslashes($this->lang->line('Your registration successfully completed.')); 
						} 
						else 
						{
							$message = "Your registration successfully completed.";
						}

						
							$this->setErrorMessage ( 'success',$message);
						
                        }catch(Exception $e){
                        echo $e->getMessage();
                        }                
	
		//$email_send_to_common = $this->user_model->common_email_send ( $email_values );
	
	}
	
	public function send_verify_mail($userDetails = '') {
	
	   // echo "<script>alert('hi')</script>";die;
		$uid = $userDetails->row ()->id;
		$username = $userDetails->row ()->user_name;
		$email = $userDetails->row ()->email;
		
		$randStr = $this->get_rand_str ( '10' );
		$condition = array (
				'user_id' => $uid 
		);
		$dataArr = array (
				'verify_code' => $randStr 
		);
		$user_id_exist=$this->user_model->get_all_details(REQUIREMENTS,array('user_id'=>$uid));
		
		$user_id_exist1=$this->user_model->get_all_details(USERS,array('id'=>$uid));
		//echo " hgdfh".$uid.$user_id_exist->num_rows(); die;
		if($user_id_exist->num_rows() == 0)
		{
		//echo "<script>alert('inside')</script>"; die;
		$dataArr1 = array (
					'user_id' => $uid,
					'id_verified'=>'no',
					'verify_code' => $randStr
					);
					
		$condition1 = array();
		$this->user_model->commonInsertUpdate(REQUIREMENTS,'insert', $excludeArr,$dataArr1,$condition1);
		$dataArr2 = array (
					
					'is_verified'=>'No',
					'verify_code' => $randStr
					);
		
		$this->user_model->commonInsertUpdate(USERS,'insert', $excludeArr,$dataArr2,$condition1);
		}else
		{
		$this->user_model->update_details( REQUIREMENTS, $dataArr, $condition );
		$condition2 = array (
				'id' => $uid 
		);
		
		$this->user_model->update_details( USERS, $dataArr, $condition2 );
		}
		$newsid = '18';
		$template_values = $this->user_model->get_newsletter_template_details( $newsid );
		
		$user=$userDetails->row ()->firstname.' '.$userDetails->row ()->lastname;
		$cfmurl = base_url () . 'site/user/confirm_verify/' . $uid . "/" . $randStr . "/confirmation";
		$subject = 'From: ' . $this->config->item ( 'email_title' ) . ' - ' . $template_values ['news_subject'];
		$adminnewstemplateArr = array (
				'email_title' => $this->config->item ( 'email_title' ),
				'logo' => $this->data ['logo'],
				'username'=>$username,
				'confirm_url'=>$cfmurl
		);
		extract ( $adminnewstemplateArr );
		//echo $this->data ['siteContactMail'];die;
		$header .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
		
		$message .= '<!DOCTYPE HTML>
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			<meta name="viewport" content="width=device-width"/><body>';
		include ('./newsletter/registeration' . $newsid . '.php');
		
		$message .= '</body>
			</html>';
		
		if ($template_values ['sender_name'] == '' && $template_values ['sender_email'] == '') {
			$sender_email = $this->data ['siteContactMail'];
			$sender_name = $this->data ['siteTitle'];
		} else {
		//echo 'dscvdscs';die;
			$sender_name = $template_values ['sender_name'];
			$sender_email = $template_values ['sender_email'];
		}
		
		// add inbox from mail
		// $this->product_model->simple_insert(INBOX,array('sender_id'=>$sender_email,'user_id'=>$email,'mailsubject'=>$template_values['news_subject'],'description'=>stripslashes($message)));
		/* $adminDetails=$this->user_model->get_all_details(ADMIN_SETTINGS,array('id'=>1));
		$ccMail = $adminDetails->row()->site_contact_mail; */
		$email_values = array (
				'mail_type' => 'html',
				'from_mail_id' => $sender_email,
				'mail_name' => $sender_name,
				'to_mail_id' => $email,
				'cc_mail_id' => $sender_email,
				'subject_message' => $template_values ['news_subject'],
				'body_messages' => $message 
				
		);
		//echo "<pre>";print_r($email_values);die;
		 /* foreach($email_values as $emailV)
		{
		echo stripslashes($emailV);
		echo '<br>';
		}die;   */
		//print_r(stripslashes($message));die;
		
		$email_send_to_common = $this->user_model->common_email_send ( $email_values );
	}
	
	public function signup_form() {
		if ($this->checkLogin ( 'U' ) != '') {
			redirect ( base_url () );
		} else {
			$this->data ['heading'] = 'Sign up';
			$this->load->view ( 'site/user/signup.php', $this->data );
		}
	}
	
	/**
	 * Loading login page
	 */
	public function login_form() {
		if ($this->checkLogin ( 'U' ) != '') {
			redirect ( base_url () );
		} else {
			$this->data ['next'] = $this->input->get ( 'next' );
			$this->data ['heading'] = 'Sign in';
			$this->load->view ( 'site/user/login.php', $this->data );
		}
	}
	public function login_user() {
		$returnStr ['status_code'] = 0;
		$returnStr ['message'] = 'Invalid login details';
		//print_r($_POST);die;
		$email = $this->input->post ( 'email' );
		$datefrom = $this->input->post ( 'datefrom' );
		$expiredate = $this->input->post ( 'expiredate' );
		$number_of_guests = $this->input->post ( 'number_of_guests' );
		
		$pwd = md5 ( $this->input->post ( 'password' ) );
		
		$bpath = $this->input->post ('bpath');
		
		$remember = $this->input->post ( 'remember' );
		
		if (valid_email($email)) {
			$condition = array (
					'email' => $email,
					'password' => $pwd,
					'status' => 'Active'
					
			);
			$checkUser = $this->user_model->get_all_details(USERS, $condition );
			
			//echo $this->db->last_query();die;
			if ($checkUser->num_rows () == '1') {
				$userdata = array (
						'fc_session_user_id' => $checkUser->row ()->id,
						
						'session_user_email' => $checkUser->row ()->email,

						'normal_login' => normal,
					
						'datefrom' => $datefrom,
						'expiredate' => $expiredate,
						'number_of_guests' => $number_of_guests
				);
				$this->session->set_userdata ( $userdata );
				$datestring = "%Y-%m-%d %h:%i:%s";
				$time = time ();
				$newdata = array (
						'last_login_date' => mdate ( $datestring, $time ),
						'last_login_ip' => $this->input->ip_address () ,
						'login_hit' => 0
				);
				$condition = array (
						'id' => $checkUser->row ()->id 
				);
				
				$this->user_model->update_details( USERS, $newdata, $condition );
				if ($remember != '') {
					// $userid = $this->encrypt->encode( $checkUser->row ()->id );
					$userid = 2;
					$cookie = array (
							'name' => 'admin_session',
							'value' => $userid,
							'expire' => 86400,
							'secure' => FALSE 
					);
					$this->input->set_cookie( $cookie );
				}

					if($this->lang->line('You are Logged In') != '') 
					{ 
						$message = stripslashes($this->lang->line('You are Logged In')); 
					} 
					else 
					{
						$message = "You are Logged In ... !";
					}
					
					//print_r($_SESSION['pageURL']); die;
					$this->setErrorMessage ( 'success',$message);
					$returnStr ['status_code'] = 1;
					$returnStr ['message'] = $message;
					if($_SESSION['pageURL'] != '')
					$returnStr ['redirect'] = $_SESSION['pageURL'];
					else
					$returnStr ['redirect'] = '';
				
			} 
			else
			{	
					$condition = array (
						'email' => $email	
				);
				
				$checkUser = $this->user_model->get_all_details ( USERS, $condition );
				
				$status=$checkUser->row()->status;
				$ArchievedUser=$checkUser->row()->host_status;
				
				if($ArchievedUser=='1'){
					$returnStr ['message'] = "You Are Not a Regsitered User";
				}
				else if($status=='Inactive'){	
					$returnStr ['message'] = "Admin has Cancelled Your Account.. Please Contact Admin";
				}else{
						$message = "Invalid Login Details";
						$returnStr ['message'] = $message;	
				}

				/* $login_hit = 0;
				if ($checkUser->num_rows () == '1') 
				{
					$login_hit = $checkUser->row()->login_hit;
					$login_hit = $login_hit+1;
					$newdata = array (
							'login_hit' => $login_hit
					);
					$condition = array (
							'id' => $checkUser->row ()->id 
					);
					$this->user_model->update_details ( USERS, $newdata, $condition );
				}
				if($login_hit < 5)
				{

					$message = "Enter the correct User name and Password or the User may InActive";

						$returnStr ['message'] = $message;
				
				} */ 
				

			}
		} else {
			if($this->lang->line('Invalid email id') != '') 
				{ 
					$message = stripslashes($this->lang->line('Invalid email id')); 
				} 
				else 
				{
					$message = "Invalid email id";
				}

			
					$returnStr ['message'] = $message;
		}
		
		echo json_encode ( $returnStr );
	}
	
	/**
	 * ************************* added 14/05/2014 --------------------------------
	 */
	public function paypaldetail() {
		$returnStr ['status_code'] = '1';
		$bank_code = $this->input->post ( 'bank_code' );
		$paypalemail = $this->input->post ( 'paypalemail' );
		$bank_name = $this->input->post ( 'bank_name' );
		$bank_no = $this->input->post ( 'bank_no' );
		
		$condition = array (
				'id' => $this->checkLogin ( 'U' ) 
		);
		$dataArr = array (
				'bank_name' => $bank_name,
				'bank_no' => $bank_no,
				'bank_code' => $bank_code,
				'paypal_email' => $paypalemail 
		);
		$this->user_model->update_details ( USERS, $dataArr, $condition );
		$returnStr ['message'] = "success" . $bank_code . $paypalemail;
		
		echo json_encode ( $returnStr );
	}
	
	/* -------------------- Rental enquiry added 15/04/2014 ----- */
	public function rentalEnquiry() {
		$returnStr ['status_code'] = 1;
		
		$NoOfDays = $this->getDatesFromRange ( date ( 'Y-m-d', strtotime ( $_REQUEST ['checkin'] ) ), date ( 'Y-m-d', strtotime ( $_REQUEST ['checkout'] ) ) );
		$dateCheck = $this->user_model->get_all_details ( CALENDARBOOKING, array (
				'PropId' => $_REQUEST ['prd_id'] 
		) );
		// echo $this->db->last_query();
		// print_r($NoOfDays);die;
		if ($dateCheck->num_rows () > 0) {
			foreach ( $dateCheck->result () as $dateCheckStr ) {
				if (in_array ( $dateCheckStr->the_date, $NoOfDays )) {
					$returnStr ['status_code'] = '';
					if($this->lang->line('Rental date already booked') != '') 
					{ 
						$message = stripslashes($this->lang->line('Rental date already booked')); 
					} 
					else 
					{
						$message = "Rental date already booked";
					}

					$returnStr ['message'] = $message;
					$returnStr ['status_code'] = 10;
					exit ();
				}
			}
		}
		// print_r($NoOfDays); echo '<pre>';print_r($dateCheck->result()); die;
		if ($returnStr ['status_code'] != 10) {
			// echo '<pre>';print_r($NoOfDays);die;
			$dataArr = array (
					'checkin' => date ( 'Y-m-d H:i:s', strtotime ( str_replace ( '-', '/', $this->input->post ( 'checkin' ) ) ) ),
					'checkout' => date ( 'Y-m-d H:i:s', strtotime ( str_replace ( '-', '/', $this->input->post ( 'checkout' ) ) ) ),
					'Enquiry' => $this->input->post ( 'Enquiry' ),
					'numofdates' => $this->input->post ( 'numofdates' ),
					'caltophone' => $this->input->post ( 'caltophone' ),
					'enquiry_timezone' => $this->input->post ( 'enquiry_timezone' ),
					'user_id' => $this->checkLogin ( 'U' ),
					'renter_id' => $this->input->post ( 'renter_id' ),
					'NoofGuest' => $this->input->post ( 'NoofGuest' ),
					'prd_id' => $this->input->post ( 'prd_id' ) 
			);
			$booking_status = array (
					'booking_status' => 'Enquiry' 
			);
			$dataArr = array_merge ( $dataArr, $booking_status );
			$this->user_model->commonInsertUpdate ( RENTALENQUIRY, 'insert', array (), $dataArr, array (
					'user_id' => $this->checkLogin ( 'U' ) 
			) );
			$insertid = $this->db->insert_id ();
			$this->session->set_userdata ( 'EnquiryId', $insertid );
			if($this->lang->line('Contact not send.') != '') 
			{ 
				$message = stripslashes($this->lang->line('Contact not send.')); 
			} 
			else 
			{
				$message = "Contact not send.";
			}
			$returnStr ['message'] = $message;
			
			$rentalArr = $this->user_model->view_product_details_email ( $_REQUEST ['prd_id'] );
			// echo $this->db->last_query();die;
			$proImages = base_url () . PRODUCTPATH . $rentalArr->row ()->product_image;
			$rental_Details = array (
					'first_name' => $this->data ['userDetails']->row ()->firstname,
					'userphoneno' => $this->data ['userDetails']->row ()->phone_no,
					'last_name' => $this->data ['userDetails']->row ()->lastname,
					'firest_name' => $this->data ['userDetails']->row ()->firstname,
					'rental_name' => $rentalArr->row ()->product_title,
					'rental_image' => $proImages,
					'owner_email' => $rentalArr->row ()->email,
					'owner_phone' => $rentalArr->row ()->phone_no 
			);
			$dataArr = array_merge ( $dataArr, $rental_Details );
			// echo json_encode($returnStr);
			$this->contact_owner ( $dataArr );
			if($this->lang->line('Contact details sent to owner') != '') 
				{ 
					$message = stripslashes($this->lang->line('Contact details sent to owner')); 
				} 
				else 
				{
					$message = "Contact details sent to owner";
				}

				$this->setErrorMessage ( 'success',$message);
			
		}
		echo json_encode ( $returnStr );
	}
	public function rentalEnquiry_booking() {

	
		$returnStr ['status_code'] = 1;
		
		$NoOfDays = $this->getDatesFromRange(date('Y-m-d', strtotime($_REQUEST['checkin'])),date('Y-m-d',strtotime($_REQUEST['checkout'])));
		$dateCheck = $this->user_model->get_all_details ( CALENDARBOOKING, array ('PropId'=>$_REQUEST['prd_id']));
		
		if ($dateCheck->num_rows () > 0) {
			
			foreach ( $dateCheck->result () as $dateCheckStr ) {
				if (in_array ( $dateCheckStr->the_date, $NoOfDays )) {
					
					$returnStr ['status_code'] = '';
					if($this->lang->line('Rental date already booked') != '') 
					{ 
						$message = stripslashes($this->lang->line('Rental date already booked')); 
					} 
					else 
					{
						$message = "Rental date already booked";
					}

					
						$returnStr ['message'] = $message;
					
					$returnStr ['status_code'] = 10;
					break;
				}
			}
		}
		
		
		if ($returnStr ['status_code'] != 10) {

			
			
			/*if($this->input->post('use_wallet_checkbox')=='yes')
				 $walletAmount=$this->input->post('walletAmount');
			else  $walletAmount='0.00';
			*/
			
			//admin to user
			$admin=$this->user_model->get_all_details (ADMIN,array('admin_type'=>'super'));
			$data=$admin->row();
			//print_r($data=$admin->row());
			$admin_currencyCode=trim($data->admin_currencyCode);
			$seller_currencyCode=trim($this->input->post ( 'currencycode' )); //PropertiesCurrency
			$user_currencyCode=trim($this->input->post ( 'user_currencyCode' )); //SessionCurrency

			$amount=1;
			$currencyPerUnitSeller=1;
			$unitPerCurrencyUser=1;
			$unitPerCurrencySeller=1;
			//storage amount based on seller
			
			//admin to seller // for admin view
			if($admin_currencyCode!=$seller_currencyCode){
			$currencyPerUnitSeller=convertCurrency($admin_currencyCode,$seller_currencyCode,$amount);
			}
			//seller to admin // for admin view
			if($seller_currencyCode!=$admin_currencyCode){
			$unitPerCurrencySeller=convertCurrency($seller_currencyCode,$admin_currencyCode,$amount);
			}
			//seller to user // for user view
			if($user_currencyCode!=$seller_currencyCode){
			$unitPerCurrencyUser=convertCurrency($user_currencyCode,$seller_currencyCode,$amount);
			}
			
			$walletAmount = ($this->input->post('walletAmount')!='')? $this->input->post('walletAmount')  :'0.00';
			
			$dataArr = array (
					'checkin' => date ( 'Y-m-d H:i:s', strtotime ( str_replace ( '-', '/', $this->input->post ( 'checkin' ) ) ) ),
					'checkout' => date ( 'Y-m-d H:i:s', strtotime ( str_replace ( '-', '/', $this->input->post ( 'checkout' ) ) ) ),
					'Enquiry' => $this->input->post ( 'Enquiry' ),
					'numofdates' => $this->input->post ( 'numofdates' ),
					
					'caltophone' => $this->input->post ( 'caltophone' ),
					'enquiry_timezone' => $this->input->post ( 'enquiry_timezone' ),
					'user_id' => $this->checkLogin ( 'U' ),
					'renter_id' => $this->input->post ( 'renter_id' ),
					'totalAmt' => $this->input->post ( 'totalAmt' ),
					'NoofGuest' => $this->input->post ( 'NoofGuest' ),
					'prd_id' => $this->input->post ( 'prd_id' ),
					'cancel_percentage' => $this->input->post ( 'cancel_percentage' ),
					'currencycode' => $this->input->post ( 'currencycode' ),
					'secDeposit' =>$this->input->post ( 'secDeposit' ),
					'cleaningFee'=>$this->input->post('cleaningFee'),
					'user_currencyCode' =>$user_currencyCode,
					'walletAmount' => $this->input->post('walletAmount'),
					'currencyPerUnitSeller' => $currencyPerUnitSeller,
					'unitPerCurrencyUser'=>$unitPerCurrencyUser,
					'choosed_option'=>$this->input->post('choosed_option')
			);
			
			
			$booking_status = array (
					'booking_status' => 'Enquiry'
						
			);
			$dataArr1 = array_merge ( $dataArr, $booking_status );
			
			//print_r($dataArr1);
			//exit;
			
			
			//echo '<pre>'; print_r($dataArr1);die;
			
			/* user wallet update */
			//malar - 07/07/2017 - moved to payment
			/*
			if($walletAmount>0){
				$userDetail = $this->user_model->get_all_details(USERS,array(
				'id' => $this->checkLogin ('U')));
				if( $userDetail->row()->referalAmount_currency == $this->input->post ('currencycode'))
					$remindWallet = $userDetail->row()->referalAmount-$walletAmount;
				
				else{

					$newWallet = convertCurrency($this->input->post ('currencycode'),$userDetail->row()->referalAmount_currency,$walletAmount);

					$remindWallet = $userDetail->row()->referalAmount-$newWallet;
				}

				$newdata  = array('referalAmount' => $remindWallet );
				$condition = array('id' => $this->checkLogin ('U'));
				$this->user_model->update_details (USERS,$newdata,$condition);
			}
			*/
			/* user wallet update */
			//echo $this->db->last_query();
			
			$this->user_model->commonInsertUpdate (RENTALENQUIRY, 'insert', array (), $dataArr1, array (
					'user_id' => $this->checkLogin ( 'U' ) 
			) );
			
			//echo $this->db->last_query();
			
			//echo $this->db->last_query();die;
			$insertid = $this->db->insert_id ();

			$this->data['bookingno']=$this->user_model->get_all_details(RENTALENQUIRY,array('id'=>$insertid));
			//echo $this->db->last_query();die;
			if($this->data['bookingno']->row()->Bookingno=='' || $this->data['bookingno']->row()->Bookingno==NULL) {
			
			$val = 10*$insertid+8;
			$val = 1500000+$val;
			// $bval ="150000".$val;
			$bookingno ="EN".$val;

			$newdata = array (
						'Bookingno' => $bookingno
				);
				$condition = array (
						'id' => $insertid
				);
				$this->user_model->update_details (RENTALENQUIRY,$newdata,$condition);
			} 
			
			//$this->emailhostreservationreq($insertid);
			//$this->traveller_reservation($insertid);
			//echo "enq".$insertid;die;
			$this->session->set_userdata('EnquiryId',$insertid);
			//echo $this->session->userdata('EnquiryId');die;
			//echo "enq".$insertid;die;
				if($this->lang->line('Contact not send.') != '') 
				{ 
					$message = stripslashes($this->lang->line('Contact not send.')); 
				} 
				else 
				{
					$message = "Contact not send.";
				}
				$returnStr ['message'] = $message;
			
		}
		
		// print_r($returnStr);die;
		echo json_encode ( $returnStr );
	}
	
	/* Booking confirmation mail */
	
	
	public function emailhostreservationreq($id) {
	/*
		$this->data['bookingmail'] = $this->user_model->getbookeduser_detail($id);
		$price = $this->data['bookingmail']->row()->price * $this->data['bookingmail']->row()->noofdates;

		$checkindate = date('d-M-Y',strtotime($this->data['bookingmail']->row()->checkin));
		$checkoutdate = date('d-M-Y',strtotime($this->data['bookingmail']->row()->checkout));

		$this->data['hostdetail'] = $this->user_model->get_all_details(USERS,array('id'=>$this->data['bookingmail']->row()->renter_id));
		
		$hostemail = $this->data['hostdetail']->row()->email;
		$hostname = $this->data['hostdetail']->row()->user_name;			
		$to  = $this->data['bookingmail']->row()->email; 
		

		$price = $this->data['bookingmail']->row()->price * $this->data['bookingmail']->row()->noofdates;
	        
		$newsid = '16';
		$template_values = $this->user_model->get_newsletter_template_details ( $newsid );
		$adminnewstemplateArr = array (
				'email_title' => $this->config->item ( 'email_title' ),
				'logo' => $this->data ['logo'],
				'checkindate'=>	$checkindate,
				'checkoutdate'=>$checkoutdate,
				'hostname'=>$hostname,
				'travellername'=>$this->data['bookingmail']->row()->name,
				'productname'=>$this->data['bookingmail']->row()->productname,
				'prd_id'=>$this->data['bookingmail']->row()->prd_id,
				'price'=>$this->data['bookingmail']->row()->price,
				'totalprice'=>$price
		);
		extract ( $adminnewstemplateArr );
		$subject = 'From: ' . $this->config->item ( 'email_title' ) . ' - ' . $template_values ['news_subject'];
		$header .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
		
		$message .= '<body>';
		include ('./newsletter/registeration' . $newsid . '.php');
		
		$message .= '</body>
			';
		
		if ($template_values ['sender_name'] == '' && $template_values ['sender_email'] == '') {
			$sender_email = $this->config->item ( 'site_contact_mail' );
			$sender_name = $this->config->item ( 'email_title' );
		} else {
			$sender_name = $template_values ['sender_name'];
			$sender_email = $template_values ['sender_email'];
		}
		
		
		
		$email_values = array (
				'mail_type' => 'html',
				'from_mail_id' => $sender_email,
				'mail_name' => $sender_name,
				'to_mail_id' => $hostemail, 
				'subject_message' => $template_values['news_subject'],
				'body_messages' => $message 
		);
		
		//echo '<pre>'; print_r($message); die;
			
			 
	$this->contact_model->common_email_send($email_values);
	*/
	
	}
	
	
	
	public function traveller_reservation($id) {
	/*
	        $this->data['bookingmail'] = $this->user_model->getbookeduser_detail($id);
			$price = $this->data['bookingmail']->row()->price * $this->data['bookingmail']->row()->noofdates;

			$checkindate =date('d-M-Y',strtotime($this->data['bookingmail']->row()->checkin));
			$checkoutdate =date('d-M-Y',strtotime($this->data['bookingmail']->row()->checkout));
		
			$this->data['hostdetail'] = $this->user_model->get_all_details(USERS,array('id'=>$this->data['bookingmail']->row()->renter_id));
			$hostname = $this->data['hostdetail']->row->email;
			$hostemail = $this->data['hostdetail']->row->user_name;			
			$to  = $this->data['bookingmail']->row()->email; 
			
			// echo $this->data['bookingmail']->row()->noofdates;
			// echo $this->data['bookingmail']->row()->checkin;
			// echo $this->data['bookingmail']->row()->checkout;
			// echo $this->data['bookingmail']->row()->price;
			// echo $this->data['bookingmail']->row()->email;
			// echo $this->data['bookingmail']->row()->name;
			$price = $this->data['bookingmail']->row()->price * $this->data['bookingmail']->row()->noofdates;
			$prd_id =$this->data['bookingmail']->row()->prd_id;
			
		//	$this->data['productimage'] = $this->user_model->get_detail_all(PRODUCT_PHOTOS,array('product_id'=>$prd_id));
			$this->data['productimage'] = $this->user_model->getproductimage($prd_id);
			//echo $prd_id;
			//echo '<pre>'; print_r($this->data['productimage']->row()->product_image);die;
			
	        
		$newsid = '20';
		$template_values = $this->user_model->get_newsletter_template_details ($newsid);
		$adminnewstemplateArr = array (
				'email_title' => $this->config->item ( 'email_title' ),
				'logo' => $this->data ['logo'],
				'checkindate'=>	$checkindate,
				'checkoutdate'=>$checkoutdate,
				'hostname'=>$hostname,
				'travellername'=>$this->data['bookingmail']->row()->name,
				'price'=>$this->data['bookingmail']->row()->price,
				'totalprice'=>$price,
				'productname'=>$this->data['bookingmail']->row()->productname,
				'prd_id'=>$this->data['bookingmail']->row()->prd_id,
				'prd_image'=>$this->data['productimage']->row()->product_image
		);
		extract ( $adminnewstemplateArr );
		$subject = 'From: ' . $this->config->item ( 'email_title' ) . ' - ' . $template_values ['news_subject'];
		
		$header .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
		
		$message .= '<body>';
		include ('./newsletter/registeration' . $newsid . '.php');
		
		$message .= '</body>';
		
		if ($template_values ['sender_name'] == '' && $template_values ['sender_email'] == '') {
			$sender_email = $this->config->item ( 'site_contact_mail' );
			$sender_name = $this->config->item ( 'email_title' );
		} else {
			$sender_name = $template_values ['sender_name'];
			$sender_email = $template_values ['sender_email'];
		}
		
		$email_values = array (
				'mail_type' => 'html',
				'from_mail_id' => $sender_email,
				'mail_name' => $sender_name,
				'to_mail_id' => $this->data['bookingmail']->row()->email,
				'subject_message' => $template_values ['news_subject'],
				'body_messages' => $message 
		);
		//echo "<pre>";print_r($message);die; 
			$this->contact_model->common_email_send($email_values);
	*/
	
	}
	
	
	
	
	
	
	
	
	
	
	
	/* email send after enquiry */
	public function contact_owner($dataArr) {
		
		// ---------------email to user---------------------------
		if ($dataArr ['renter_id'] > 0) {
			$UserDetails = $this->user_model->get_all_details ( USERS, array (
					'id' => $this->checkLogin ( 'U' ) 
			) );
			$emailid = $UserDetails->row ()->email;
			$this->session->set_userdata ( 'ContacterEmail', $emailid );
			
			$newsid = '1';
			$template_values = $this->contact_model->get_newsletter_template_details ( $newsid );
			
			$cfmurl = base_url () . 'site/user/confirm_register/' . $uid . "/" . $randStr . "/confirmation";
			$subject = 'From: ' . $this->config->item ( 'email_title' ) . ' - ' . $template_values ['news_subject'];
			$adminnewstemplateArr = array (
					'email_title' => $this->config->item ( 'email_title' ),
					'logo' => $this->data ['logo'] 
			);
			
			extract ( $adminnewstemplateArr );
			extract ( $dataArr );
			
			// $ddd =htmlentities($template_values['news_descrip'],null,'UTF-8');
			$header .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
			
			$message .= '<!DOCTYPE HTML>
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
							<meta name="viewport" content="width=device-width"/><body>';
			include ('./newsletter/registeration' . $newsid . '.php');
			
			$message .= '</body>
							</html>';
			
			if ($template_values ['sender_name'] == '' && $template_values ['sender_email'] == '') {
				$sender_email = $this->data ['siteContactMail'];
				$sender_name = $this->data ['siteTitle'];
			} else {
				$sender_name = $template_values ['sender_name'];
				$sender_email = $template_values ['sender_email'];
			}
			
			// add inbox from mail
			$this->contact_model->simple_insert ( INBOX, array (
					'sender_id' => $owner_email,
					'user_id' => $emailid,
					'mailsubject' => $template_values ['news_subject'],
					'description' => stripslashes ( $message ) 
			) );
			
			$email_values = array (
					'mail_type' => 'html',
					'from_mail_id' => $sender_email,
					'mail_name' => $sender_name,
					'to_mail_id' => $emailid,
					'subject_message' => $template_values ['news_subject'],
					'body_messages' => $message 
			);
			
			$email_send_to_common = $this->contact_model->common_email_send ( $email_values );
			
			// $user_input_values = $this->input->post();
			
			$this->mail_owner_admin ( $dataArr );
		}
		// redirect(base_url('rental/'.$this->input->post('rental_id')));
		/* echo '<!--<script>window.history.go(-1);</script>-->'; */
		
		// }
	}
	public function mail_owner_admin($got_values) { // print_r($got_values);die;
	  
		// email to admin
		$header = '';
		$adminnewstemplateArr = array ();
		$subject = '';
		$cfmurl = '';
		$sender_email = '';
		$sender_name = '';
		$newsid = '9';
		$template_values = $this->contact_model->get_newsletter_template_details ( $newsid );
		
		$adminnewstemplateArr = array (
				'email_title' => $this->config->item ( 'email_title' ),
				'logo' => $this->data ['logo'] 
		);
		
		extract ( $adminnewstemplateArr );
		extract ( $got_values );
		
		$subject = 'From: ' . $this->config->item ( 'email_title' ) . ' - ' . $template_values ['news_subject'];
		$header .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
		
		$message .= '<!DOCTYPE HTML>
						<html>
						<head>
						<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
						<meta name="viewport" content="width=device-width"/><body>';
		include ('./newsletter/registeration' . $newsid . '.php');
		
		$message .= '</body>
						</html>';
		
		if ($template_values ['sender_name'] == '' && $template_values ['sender_email'] == '') {
			$sender_email = $this->data ['siteContactMail'];
			$sender_name = $this->data ['siteTitle'];
		} else {
			$sender_name = $template_values ['sender_name'];
			$sender_email = $template_values ['sender_email'];
		}
		
		// add inbox from mail
		$this->contact_model->simple_insert ( INBOX, array (
				'sender_id' => $this->session->userdata ( 'ContacterEmail' ),
				'user_id' => $sender_email,
				'mailsubject' => $template_values ['news_subject'],
				'description' => stripslashes ( $message ) 
		) );
		$email_values2 = array (
				'mail_type' => 'html',
				'from_mail_id' => $sender_email,
				'mail_name' => $sender_email,
				'to_mail_id' => $sender_email,
				'subject_message' => $template_values ['news_subject'],
				'body_messages' => $message 
		);
		$email_send_to_common1 = $this->contact_model->common_email_send ( $email_values2 );
		
		// Email to owner
		
		if ($got_values ['renter_id'] > 0) {
			$UserDetails = $this->user_model->get_all_details ( USERS, array (
					'id' => $got_values ['renter_id'] 
			) );
			$emailid = $UserDetails->row ()->email;
			$this->contact_model->simple_insert ( INBOX, array (
					'sender_id' => $this->session->userdata ( 'ContacterEmail' ),
					'user_id' => $emailid,
					'mailsubject' => $template_values ['news_subject'],
					'description' => stripslashes ( $message ) 
			) );
			$email_values = array (
					'mail_type' => 'html',
					'from_mail_id' => $sender_email,
					'mail_name' => $sender_name,
					'to_mail_id' => $emailid,
					'subject_message' => $template_values ['news_subject'],
					'body_messages' => $message 
			);
			// echo"admin<pre>"; print_r($email_values2);echo "<br>";
			// echo"owner"; print_r($email_values); die;
			$this->session->unset_userdata ( 'ContacterEmail' );
			$email_send_to_common = $this->contact_model->common_email_send ( $email_values );
		}
		
		// print_r($message);die;
	}
	/* email send End */
	public function login_after_signup($userDetails = '') {
		if ($userDetails->num_rows () == '1') {
			$userdata = array (
					'fc_session_user_id' => $userDetails->row ()->id,
					'session_user_name' => $userDetails->row ()->user_name,
					'session_user_email' => $userDetails->row ()->email 
			);
			$this->session->set_userdata ( $userdata );
			$datestring = "%Y-%m-%d %h:%i:%s";
			$time = time ();
			$newdata = array (
					'last_login_date' => mdate ( $datestring, $time ),
					'last_login_ip' => $this->input->ip_address () 
			);
			$condition = array (
					'id' => $userDetails->row ()->id 
			);
			$this->user_model->update_details ( USERS, $newdata, $condition );
			
			$this->user_model->updategiftcard ( GIFTCARDS_TEMP, $this->checkLogin ( 'T' ), $userDetails->row ()->id );
		} else {
			redirect ( base_url () );
		}
	}
	public function confirm_register() {
		$uid = $this->uri->segment ( 4, 0 );
		$code = $this->uri->segment ( 5, 0 );
		$mode = $this->uri->segment ( 6, 0 );
		if ($mode == 'confirmation') {
			$condition = array (
					'verify_code' => $code,
					'id' => $uid 
			);
			$checkUser = $this->user_model->get_all_details ( USERS, $condition );
			if ($checkUser->num_rows () == 1) {
				$conditionArr = array (
						'id' => $uid,
						'verify_code' => $code 
				);
				$dataArr = array (
						'id_verified' => 'Yes'
						//'status' => 'Active' 
				);
				$this->user_model->update_details ( USERS, $dataArr, $condition );
				if($this->lang->line('Great going ! Your mail ID has been verified') != '') 
				{ 
					$message = stripslashes($this->lang->line('Great going ! Your mail ID has been verified')); 
				} 
				else 
				{
					$message = "Great going ! Your mail ID has been verified";
				}
					$this->setErrorMessage ('success',$message);
				
				$this->login_after_signup ( $checkUser );
				redirect ( base_url () );
			} else {
				if($this->lang->line('Invalid confirmation link') != '') 
				{ 
					$message = stripslashes($this->lang->line('Invalid confirmation link')); 
				} 
				else 
				{
					$message = "Invalid confirmation link";
				}
					$this->setErrorMessage ( 'error',$message);
				redirect ( base_url () );
				
			}
		} else {
			
				if($this->lang->line('Invalid confirmation link') != '') 
				{ 
					$message = stripslashes($this->lang->line('Invalid confirmation link')); 
				} 
				else 
				{
					$message = "Invalid confirmation link";
				}
				$this->setErrorMessage ('error',$message);
				redirect ( base_url () );
			
		}
	}
	
	public function confirm_verify() {
		$uid = $this->uri->segment ( 4, 0 );
		$code = $this->uri->segment ( 5, 0 );
		$mode = $this->uri->segment ( 6, 0 );

		if ($mode == 'confirmation') {
			/* $condition = array (
					'verify_code' => $code,
					'user_id' => $uid 
			);
			$checkUser = $this->user_model->get_all_details ( REQUIREMENTS, $condition );*/
			$condition1 = array ( 
					'verify_code' => $code,
					'id' => $uid 
			);
			$checkUser = $this->user_model->get_all_users($uid);

			if ($checkUser) {
				/* $conditionArr = array (
						'user_id' => $uid,
						'verify_code' => $code 
				);
				$dataArr = array (
						'id_verified' => 'yes'
				);
				$this->user_model->update_user ( $code, $uid); */
				$conditionArr1 = array (
						'id' => $uid,
						'verify_code' => $code 
				);
				$dataArr1 = array (
						'is_verified' => 'yes'
				);
				$this->user_model->update_user($code, $uid);
				if($this->lang->line('Great going ! Your mail ID has been verified') != '') 
				{ 
					$message = stripslashes($this->lang->line('Great going ! Your mail ID has been verified')); 
				} 
				else 
				{
					$message = "Great going ! Your mail ID has been verified";
				}
					$this->setErrorMessage ( 'success',$message);
					redirect ( base_url () );
				
			} else {
				if($this->lang->line('Invalid confirmation link') != '') 
				{ 
					$message = stripslashes($this->lang->line('Invalid confirmation link')); 
				} 
				else 
				{
					$message = "Invalid confirmation link";
				}

				$this->setErrorMessage ( 'error',$message);
				redirect ( base_url () );
				
			}
		} else {
			if($this->lang->line('Invalid confirmation link') != '') 
				{ 
					$message = stripslashes($this->lang->line('Invalid confirmation link')); 
				} 
				else 
				{
					$message = "Invalid confirmation link";
				}
				$this->setErrorMessage ( 'error',$message);
				redirect ( base_url () );
			
		}
	}
	
	public function logout_user() {
		$datestring = "%Y-%m-%d %h:%i:%s";
		$time = time ();
		$newdata = array (
				'last_logout_date' => mdate ( $datestring, $time ) 
		);
		$condition = array (
				'id' => $this->checkLogin ( 'U' ) 
		);
		$this->user_model->update_details ( USERS, $newdata, $condition );
		$userdata = array (
				'fc_session_user_id' => '',
				'session_user_name' => '',
				'session_user_email' => '',
				'fc_session_temp_id' => '',
				'login_type' =>'',
				'normal_login' => ''
		);
		$this->session->unset_userdata ( $userdata );
		//$this->load->url('https://accounts.google.com/logout');
		@session_start ();
		unset ( $_SESSION ['token'] );
		$twitter_return_values = array (
				'tw_status' => '',
				'tw_access_token' => '' 
		);
		
		$this->session->unset_userdata ( $twitter_return_values );
			if($this->lang->line('Successfully logout from your account') != '') 
				{ 
					$message = stripslashes($this->lang->line('Successfully logout from your account')); 
				} 
				else 
				{
					$message = "Successfully logout from your account";
				}

		$this->setErrorMessage ( 'success',$message);
		
		redirect ( base_url () );
	}
	
	public function forgot_password_form() {
		$this->data ['heading'] = 'Forgot Password';
		$this->load->view ( 'site/user/forgot_password.php', $this->data );
	}
	public function forgot_password_user() {
		$returnStr ['status_code'] = 0;
		$returnStr ['message'] = '';
		$this->form_validation->set_rules ( 'email', 'Email Address', 'required' );
		if ($this->form_validation->run () === FALSE) {
			
			if($this->lang->line('Email address required') != '') 
				{ 
					$message = stripslashes($this->lang->line('Email address required')); 
				} 
				else 
				{
					$message = "Email address required";
				}
				$this->setErrorMessage ('error',$message);
				redirect ( 'forgot-password' );
			
		} else {
			$email = $this->input->post ( 'email' );
			if (valid_email ( $email )) {
				$condition = array (
						'email' => $email 
				);
				$checkUser = $this->user_model->get_all_details ( USERS, $condition );
				
				//echo '<pre>'; print_r($checkUser->result_array()); die;
				if ($checkUser->num_rows () == '1') {
					$pwd = $this->get_rand_str ( '6' );
					$newdata = array (
							'password' => md5 ( $pwd ) 
					);
					$condition = array (
							'email' => $email 
					);
					$this->user_model->update_details ( USERS, $newdata, $condition );
					$this->send_user_password ( $pwd, $checkUser );
					if($this->lang->line('New password sent to your mail') != '') 
					{ 
						$message = stripslashes($this->lang->line('New password sent to your mail')); 
					} 
					else 
					{
						$message = "New password sent to your mail";
					}

						$this->setErrorMessage ( 'success',$message);
						$returnStr ['message'] = $message;
					
					
					$returnStr ['status_code'] = 1;
					// redirect('site/landing');
				} else {
					// $this->setErrorMessage('error','Your email id not matched in our records');
					if($this->lang->line('Your email id not matched in our records') != '') 
					{ 
						$message = stripslashes($this->lang->line('Your email id not matched in our records')); 
					} 
					else 
					{
						$message = "Your email id not matched in our records";
					}

						$returnStr ['message'] = $message;
					
					// redirect('forgot-password');
				}
			} else {
				// $this->setErrorMessage('error','Email id not valid');
				if($this->lang->line('Please enter a valid email address') != '') 
				{ 
					$message = stripslashes($this->lang->line('Please enter a valid email address')); 
				} 
				else 
				{
					$message = "Please enter a valid email address";
				}
					$returnStr ['message'] = $message;
				
				// redirect('forgot-password');
			}
		}
		echo json_encode ( $returnStr );
	}
	public function send_user_password($pwd = '', $query) {
		$newsid = '5';
		$template_values = $this->user_model->get_newsletter_template_details ( $newsid );
		$adminnewstemplateArr = array (
				'email_title' => $this->config->item ( 'email_title' ),
				'logo' => $this->data ['logo'] 
		);
		extract ( $adminnewstemplateArr );
		$subject = 'From: ' . $this->config->item ( 'email_title' ) . ' - ' . $template_values ['news_subject'];
	/*	$message .= '<!DOCTYPE HTML>
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			<meta name="viewport" content="width=device-width"/>
			<title>' . $template_values ['news_subject'] . '</title>
			<body>';
		include ('./newsletter/registeration' . $newsid . '.php');
		
		$message .= '</body>
			</html>';
			*/
		$newsid='5';
		if ($template_values ['sender_name'] == '' && $template_values ['sender_email'] == '') {
			$sender_email = $this->config->item ( 'site_contact_mail' );
			$sender_name = $this->config->item ( 'email_title' );
		} else {
			$sender_name = $template_values ['sender_name'];
			$sender_email = $template_values ['sender_email'];
		}
		
		// add inbox from mail
		// $this->product_model->simple_insert(INBOX,array('sender_id'=>$sender_email,'user_id'=>$query->row()->email,'mailsubject'=>'Password Reset','description'=>stripslashes($message)));
		
		$email_values = array (
				'mail_type' => 'html',
				'from_mail_id' => $sender_email,
				'mail_name' => $sender_name,
				'to_mail_id' => $query->row ()->email,
				'subject_message' => 'Password Reset',
				'body_messages' => $message 
		);
		
		$reg = array (
				'email_title' => $this->config->item ( 'email_title' ),
'pwd' => $pwd,
				'logo' => $this->data ['logo'] 
		);
           
            $message = $this->load->view('newsletter/Forgot Password'.$newsid.'.php',$reg,TRUE);
//$message = $this->load->view('newsletter/RegistrationConfirmation'.$newsid.'.php',$reg,TRUE);
            
            //send mail
            $this->load->library('email');
            $this->email->from($email_values['from_mail_id'], $sender_name);
            $this->email->to($email_values['to_mail_id']);
            $this->email->subject($email_values['subject_message']);
            $this->email->set_mailtype("html");
           
                        
                        $this->email->message($message); 
                        try{
                        $this->email->send();
						if($this->lang->line('Successfully registered') != '') 
						{ 
							$message = stripslashes($this->lang->line('Successfully registered')); 
						} 
						else 
						{
							$message = "Successfully registered";
						}

							$returnStr ['msg'] = $message;
						
			$returnStr ['success'] = '1';
                        }catch(Exception $e){
                        echo $e->getMessage();
                        } 
						
		// print_r($message);die;
		
		//$email_send_to_common = $this->product_model->common_email_send ( $email_values );
		
		/* echo $this->email->print_debugger();die; */
	}
	/*
	 * public function emailSettings_notification()
	 * {
	 * if ($this->checkLogin('U') == '')
	 * {
	 * $returnStr['message'] = 'You must login';
	 * }
	 * else
	 * {
	 * $user_id = $this->input->post('user_id');
	 * if($this->input->post('upcoming_reservation'))
	 * $up_res = 'yes';
	 * $current_pass = md5($this->input->post('old_password'));
	 * $condition = array('email'=>$email,'password'=>$current_pass);
	 * $checkuser = $this->user_model->get_all_details(USERS,$condition);
	 * if($checkuser->num_rows() == 1)
	 * {
	 * $newPass = md5($this->input->post('new_password'));
	 * $newdata = array('password' => $newPass);
	 * $condition1 = array('email'=>$email);
	 * $this->user_model->update_details(USERS,$newdata,$condition1);
	 * $this->setErrorMessage('success','Password changed successfully');
	 * redirect(dashboard);
	 * }
	 * else
	 * {
	 * $this->setErrorMessage('error','Current password is wrong');
	 * redirect('account-settings');
	 * }
	 * }
	 * }
	 */
	public function update_notifications() {
		if ($this->checkLogin ( 'U' ) == '')
			redirect ( base_url () );
		else {
			$emailArr = $this->data ['emailArr'];
			$emailStr = '';
			foreach ( $this->input->post () as $key => $val ) {
				if (in_array ( $key, $emailArr )) {
					$emailStr .= $key . ',';
				}
			}
			$emailStr = substr ( $emailStr, 0, strlen ( $emailStr ) - 1 );
			$dataArr = array (
					'email_notifications' => $emailStr 
			);
			$condition = array (
					'id' => $this->checkLogin ( 'U' ) 
			);
			$this->user_model->update_details ( USERS, $dataArr, $condition );
			if($this->lang->line('Email notifications settings saved successfully') != '') 
				{ 
					$message = stripslashes($this->lang->line('Email notifications settings saved successfully')); 
				} 
				else 
				{
					$message = "Email notifications settings saved successfully";
				}

				$this->setErrorMessage ( 'success',$message);
			
			redirect ( account );
		}
	}
	
	
public function update_mobile_notifications(){
	if ($this->checkLogin ( 'U' ) == '')
			redirect ( base_url () );
		else {
			
			$mob_notify=$this->input->post('mobile_notification');
			if($mob_notify==''){
				$notyStr='no';
			}else{
				$notyStr='yes';
			}
			$dataArr = array (
					'receive_text_msg' => $notyStr 
			);
			
			$condition = array (
					'id' => $this->checkLogin ( 'U' ) 
			);
			$this->user_model->update_details ( USERS, $dataArr, $condition );
			
			
						if($this->lang->line('mobile_notfification') != '')
							{ 
								$message = stripslashes($this->lang->line('mobile_notfification')); 
							} 
							else
							{
								$message = "Mobile Notification Saved Successfully..!";
							}
			
			
			
			$this->setErrorMessage ('success',$message);	
			redirect ( account );

		}		
		
	}
	
	public function update_notifications_mobile() {
		if ($this->checkLogin ( 'U' ) == '')
			redirect ( base_url () );
		else {
			$notyArr = $this->data ['notyArr'];
			$notyStr = '';
			foreach ( $this->input->post () as $key => $val ) {
				if (in_array ( $key, $notyArr )) {
					$notyStr .= $key . ',';
				}
			}
			$notyStr = substr ( $notyStr, 0, strlen ( $notyStr ) - 1 );
			$dataArr = array (
					'notifications' => $notyStr 
			);
			$condition = array (
					'id' => $this->checkLogin ( 'U' ) 
			);
			$this->user_model->update_details ( USERS, $dataArr, $condition );
				if($this->lang->line('Mobile notifications settings saved successfully') != '') 
				{ 
					$message = stripslashes($this->lang->line('Mobile notifications settings saved successfully')); 
				} 
				else 
				{
					$message = "Mobile notifications settings saved successfully";
				}

				$this->setErrorMessage ('success',$messag);	
			
			
			redirect ( account );
		}
	}
	
	/**
	 * * Membership Package Payment *
	 */
	public function memberPackagePayment() {
		$this->load->library ( 'paypal_class' );
		$totalAmount = explode ( '-', $_POST ['plan'] );
		$paypalProcess = unserialize ( $paypal_ipn_settings ['settings'] );
		$loginUserId = $this->checkLogin ( 'U' );
		$excludeArr = array (
				'plan',
				'planpay' 
		);
		$MembershipIdArr = explode ( '-', $_POST ['member_pakage'] );
		if ($MembershipIdArr [0] > 0) {
			$meb_id = $MembershipIdArr [0];
		} else {
			$this->setErrorMessage ( 'error', 'Payment Details Invalid' );
			redirect ( base_url ( 'plan' ) );
		}
		$MembershipDetails = $this->user_model->get_all_details ( FANCYYBOX, array (
				'id' => $meb_id 
		) );
		$condition = array (
				'user_id' => $loginUserId 
		);
		$dataArr = array (
				'member_pakage' => $_POST ['member_pakage'] 
		);
		
		$this->product_model->commonInsertUpdate ( PRODUCT, 'update', $excludeArr, $dataArr, $condition );
		$currDAte = date ( "Y-m-d" );
		$this->product_model->commonInsertUpdate ( USERS, 'update', array (
				'user_id',
				'plan',
				'planpay' 
		), array (
				'member_pakage' => $meb_id,
				'member_purchase_date' => $currDAte 
		), array (
				'id' => $loginUserId 
		) );
		// echo $this->db->last_query();die;
		$quantity = 1;
		
		$paypal = $this->checkout_model->getPaypalDetails ();
		// print_r($paypal);
		$dataArr = array (
				'settings' => serialize ( $paypal ) 
		);
		// $result=serialize($dataArr['settings']);
		$ans = unserialize ( $paypal [0] ['settings'] );
		
		$email = $ans ['merchant_email'];
		
		$mode = $ans ['mode'];
		
		if ($mode == 'sandbox') {
			$this->paypal_class->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr'; // testing paypal url
		} else {
			$this->paypal_class->paypal_url = 'https://www.paypal.com/cgi-bin/webscr'; // paypal url
		}
		
		$this->paypal_class->add_field ( 'currency_code', 'USD' ); // USD
		
		$this->paypal_class->add_field ( 'business', $email ); // Business Email
		                                                   // $this->paypal_class->add_field('business',$email); // Business Email
		
		$this->paypal_class->add_field ( 'return', base_url () . 'order/pakagesuccess/' . $loginUserId . '/' . $lastFeatureInsertId ); // Return URL
		
		$this->paypal_class->add_field ( 'cancel_return', base_url () . 'order/failure' ); // Cancel URL
		
		$this->paypal_class->add_field ( 'notify_url', base_url () . 'order/ipnpayment' ); // Notify url
		                                                                             
		// $this->paypal_class->add_field('custom', 'Product|'.$loginUserId.'|'.$lastFeatureInsertId); // Custom Values
		
		$this->paypal_class->add_field ( 'item_name', $totalAmount [0] ); // Product Name
		
		$this->paypal_class->add_field ( 'user_id', $loginUserId );
		
		$this->paypal_class->add_field ( 'quantity', $quantity ); // Quantity
		                                                       // echo $totalAmount;die;
		$this->paypal_class->add_field ( 'amount', $totalAmount [1] ); // Price
		                                                           // $this->paypal_class->add_field('amount', 1); // Price
		                                                           
		// echo base_url().'order/success/'.$loginUserId.'/'.$lastFeatureInsertId; die;
		
		$this->paypal_class->submit_paypal_post ();
	}
	public function update_privacy() {
		if ($this->checkLogin ( 'U' ) == '')
			redirect ( base_url () );
		else {
			$privacyArr = $this->data ['privacyArr'];
			$privacyStr = '';
			foreach ( $this->input->post () as $key => $val ) {
				if (in_array ( $key, $privacyArr )) {
					$privacyStr .= $key . ',';
				}
			}
			$privacyStr = substr ( $privacyStr, 0, strlen ( $privacyStr ) - 1 );
			$dataArr = array (
					'notifications' => $privacyStr 
			);
			$condition = array (
					'id' => $this->checkLogin ( 'U' ) 
			);
			$this->user_model->update_details ( USERS, $dataArr, $condition );
			if($this->lang->line('Privacy settings saved successfully') != '') 
				{ 
					$message = stripslashes($this->lang->line('Privacy settings saved successfully')); 
				} 
				else 
				{
					$message = "Privacy settings saved successfully";
				}

				$this->setErrorMessage ('success',$message);
			
			redirect ( account - privacy );
		}
	}
	public function change_password1() {
		
		if ($this->checkLogin ( 'U' ) == '') {
			if($this->lang->line('You must login') != '') 
				{ 
					$message = stripslashes($this->lang->line('You must login')); 
				} 
				else 
				{
					$message = "You must login";
				}

				$returnStr ['message'] = $message;
			
		} else {
		
		$email = $this->input->post ( 'id' );
		 $current_pass = md5 ( $this->input->post ( 'old_password' ) );
			$condition = array (
					'email' => $email,
					'password' => $current_pass 
			);
			$checkuser = $this->user_model->get_all_details ( USERS, $condition );
			if ($checkuser->num_rows () == 1) {
				$newPass = md5 ( $this->input->post ( 'new_password' ) );
				$newdata = array (
						'password' => $newPass 
				);
				$condition1 = array (
						'email' => $email 
				);
				$this->user_model->update_details ( USERS, $newdata, $condition1 );
				if($this->lang->line('Password changed successfully') != '') 
				{ 
					$message = stripslashes($this->lang->line('Password changed successfully')); 
				} 
				else 
				{
					$message = "Password changed successfully";
				}

				$this->setErrorMessage ( 'success',$message);
				redirect ( dashboard );
				
			} else {
			
				if($this->lang->line('Current password is wrong') != '') 
				{ 
					$message = stripslashes($this->lang->line('Current password is wrong')); 
				} 
				else 
				{
					$message = "Current password is wrong";
				}

				$this->setErrorMessage ( 'error',$message);
				redirect ( 'account-security' );
			}
		}
	}
	public function cancel_account() {
		if ($this->checkLogin ( 'U' ) == '') {
				if($this->lang->line('You must login') != '') 
				{ 
					$message = stripslashes($this->lang->line('You must login')); 
				} 
				else 
				{
					$message = "You must login";
				}

				$returnStr ['message'] = $message;
			
		} else {
			$email = $this->input->post ( 'email' );
			$condition = array (
					'email' => $email 
			);
			$checkUser = $this->user_model->get_all_details ( USERS, $condition );
			if ($checkUser->num_rows () == 1) {
				$data = array (
						'user_id' => $this->input->post ( 'id' ),
						'email' => $email,
						'reason' => $this->input->post ( 'reason' ),
						'contact_again' => $this->input->post ( 'contact_ok' ),
						'detail' => $this->input->post ( 'details' ) 
				);
				$this->user_model->simple_insert ( USERS_DELETE, $data );
				$this->user_model->commonDelete ( USERS, $condition );
				$userdata = array (
						'fc_session_user_id' => '',
						'session_user_name' => '',
						'session_user_email' => '',
						'fc_session_temp_id' => '' 
				);
				$this->session->unset_userdata ( $userdata );
				
				@session_start ();
				unset ( $_SESSION ['token'] );
				$twitter_return_values = array (
						'tw_status' => '',
						'tw_access_token' => '' 
				);
				$this->session->unset_userdata ( $twitter_return_values );
				if($this->lang->line('Your account has been canceled') != '') 
				{ 
					$message = stripslashes($this->lang->line('Your account has been canceled')); 
				} 
				else 
				{
					$message = "Your account has been canceled";
				}
					$this->setErrorMessage ('error',$message);
					redirect ( base_url () );
				
			} else {
				if($this->lang->line('User details not available') != '') 
				{ 
					$message = stripslashes($this->lang->line('User details not available')); 
				} 
				else 
				{
					$message = "User details not available";
				}

					$this->setErrorMessage ('error',$message);
					redirect ( 'account-settings' );
				
			}
		}
	}
	public function add_fancy_item() {
		$returnStr ['status_code'] = 0;
		if ($this->checkLogin ( 'U' ) == '') {
			if($this->lang->line('You must login') != '') 
				{ 
					$message = stripslashes($this->lang->line('You must login')); 
				} 
				else 
				{
					$message = "You must login";
				}

				$returnStr ['message'] = $message;
			
		} else {
			$tid = $this->input->post ( 'tid' );
			$checkProductLike = $this->user_model->get_all_details ( PRODUCT_LIKES, array (
					'product_id' => $tid,
					'user_id' => $this->checkLogin ( 'U' ) 
			) );
			if ($checkProductLike->num_rows () == 0) {
				$productDetails = $this->user_model->get_all_details ( PRODUCT, array (
						'seller_product_id' => $tid 
				) );
				if ($productDetails->num_rows () == 0) {
					$productDetails = $this->user_model->get_all_details ( USER_PRODUCTS, array (
							'seller_product_id' => $tid 
					) );
					$productTable = USER_PRODUCTS;
				} else {
					$productTable = PRODUCT;
				}
				if ($productDetails->num_rows () == 1) {
					$likes = $productDetails->row ()->likes;
					$dataArr = array (
							'product_id' => $tid,
							'user_id' => $this->checkLogin ( 'U' ),
							'ip' => $this->input->ip_address () 
					);
					$this->user_model->simple_insert ( PRODUCT_LIKES, $dataArr );
					$actArr = array (
							'activity_name' => 'fancy',
							'activity_id' => $tid,
							'user_id' => $this->checkLogin ( 'U' ),
							'activity_ip' => $this->input->ip_address () 
					);
					$this->user_model->simple_insert ( USER_ACTIVITY, $actArr );
					$datestring = "%Y-%m-%d %h:%i:%s";
					$time = time ();
					$createdTime = mdate ( $datestring, $time );
					$actArr = array (
							'activity' => 'like',
							'activity_id' => $tid,
							'user_id' => $this->checkLogin ( 'U' ),
							'activity_ip' => $this->input->ip_address (),
							'created' => $createdTime 
					);
					$this->user_model->simple_insert ( NOTIFICATIONS, $actArr );
					$likes ++;
					$dataArr = array (
							'likes' => $likes 
					);
					$condition = array (
							'seller_product_id' => $tid 
					);
					$this->user_model->update_details ( $productTable, $dataArr, $condition );
					$totalUserLikes = $this->data ['userDetails']->row ()->likes;
					$totalUserLikes ++;
					$this->user_model->update_details ( USERS, array (
							'likes' => $totalUserLikes 
					), array (
							'id' => $this->checkLogin ( 'U' ) 
					) );
					/*
					 * -------------------------------------------------------
					 * Creating list automatically when user likes a product
					 * -------------------------------------------------------
					 *
					 * $listCheck = $this->user_model->get_list_details($tid,$this->checkLogin('U'));
					 * if ($listCheck->num_rows() == 0){
					 * $productCategoriesArr = explode(',', $productDetails->row()->category_id);
					 * if (count($productCategoriesArr)>0){
					 * foreach ($productCategoriesArr as $productCategoriesRow){
					 * if ($productCategoriesRow != ''){
					 * $productCategory = $this->user_model->get_all_details(CATEGORY,array('id'=>$productCategoriesRow));
					 * if ($productCategory->num_rows()==1){
					 *
					 * }
					 * }
					 * }
					 * }
					 * }
					 */
					$returnStr ['status_code'] = 1;
				} else {
					if($this->lang->line('Product not available') != '') 
					{ 
						$message = stripslashes($this->lang->line('Product not available')); 
					} 
					else 
					{
						$message = "Product not available";
					}
						$returnStr ['message'] = $message;
				}
			}
		}
		echo json_encode ( $returnStr );
	}
	public function remove_fancy_item() {
		$returnStr ['status_code'] = 0;
		if ($this->checkLogin ( 'U' ) == '') {
			if($this->lang->line('You must login') != '') 
				{ 
					$message = stripslashes($this->lang->line('You must login')); 
				} 
				else 
				{
					$message = "You must login";
				}
				$returnStr ['message'] = $message;
			
		} else {
			$tid = $this->input->post ( 'tid' );
			$checkProductLike = $this->user_model->get_all_details ( PRODUCT_LIKES, array (
					'product_id' => $tid,
					'user_id' => $this->checkLogin ( 'U' ) 
			) );
			if ($checkProductLike->num_rows () == 1) {
				$productDetails = $this->user_model->get_all_details ( PRODUCT, array (
						'seller_product_id' => $tid 
				) );
				if ($productDetails->num_rows () == 0) {
					$productDetails = $this->user_model->get_all_details ( USER_PRODUCTS, array (
							'seller_product_id' => $tid 
					) );
					$productTable = USER_PRODUCTS;
				} else {
					$productTable = PRODUCT;
				}
				if ($productDetails->num_rows () == 1) {
					$likes = $productDetails->row ()->likes;
					$conditionArr = array (
							'product_id' => $tid,
							'user_id' => $this->checkLogin ( 'U' ) 
					);
					$this->user_model->commonDelete ( PRODUCT_LIKES, $conditionArr );
					$actArr = array (
							'activity_name' => 'unfancy',
							'activity_id' => $tid,
							'user_id' => $this->checkLogin ( 'U' ),
							'activity_ip' => $this->input->ip_address () 
					);
					$this->user_model->simple_insert ( USER_ACTIVITY, $actArr );
					$likes --;
					$dataArr = array (
							'likes' => $likes 
					);
					$condition = array (
							'seller_product_id' => $tid 
					);
					$this->user_model->update_details ( $productTable, $dataArr, $condition );
					$totalUserLikes = $this->data ['userDetails']->row ()->likes;
					$totalUserLikes --;
					$this->user_model->update_details ( USERS, array (
							'likes' => $totalUserLikes 
					), array (
							'id' => $this->checkLogin ( 'U' ) 
					) );
					$returnStr ['status_code'] = 1;
				} else {
					if($this->lang->line('Product not available') != '') 
					{ 
						$message = stripslashes($this->lang->line('Product not available')); 
					} 
					else 
					{
						$message = "Product not available";
					}

						$returnStr ['message'] = $message;
					
				}
			}
		}
		echo json_encode ( $returnStr );
	}
	public function display_user_profile() {
		$username = urldecode ( $this->uri->segment ( 2, 0 ) );
		if ($username == 'administrator') {
			$this->data ['heading'] = $username;
			$this->load->view ( 'site/user/display_admin_profile' );
		} else {
			$userProfileDetails = $this->user_model->get_all_details ( USERS, array (
					'user_name' => $username,
					'status' => 'Active' 
			) );
			if ($userProfileDetails->num_rows () == 1) {
				$this->data ['heading'] = $username;
				if ($userProfileDetails->row ()->visibility == 'Only you' && $userProfileDetails->row ()->id != $this->checkLogin ( 'U' )) {
					$this->load->view ( 'site/user/display_user_profile_private', $this->data );
				} else {
					$this->data ['productLikeDetails'] = $this->user_model->get_like_details_fully ( $userProfileDetails->row ()->id );
					$this->data ['userProductLikeDetails'] = $this->user_model->get_like_details_fully_user_products ( $userProfileDetails->row ()->id );
					$this->data ['userProfileDetails'] = $userProfileDetails;
					$this->data ['recentActivityDetails'] = $this->user_model->get_activity_details ( $userProfileDetails->row ()->id );
					$this->data ['featureProductDetails'] = $this->product_model->get_featured_details ( $userProfileDetails->row ()->feature_product );
					
					$this->load->view ( 'site/user/display_user_profile', $this->data );
				}
			} else {
				if($this->lang->line('User details not available') != '') 
				{ 
					$message = stripslashes($this->lang->line('User details not available')); 
				} 
				else 
				{
					$message = "User details not available";
				}

				$this->setErrorMessage ( 'error',$message);
				redirect ( base_url () );
			}
		}
	}
	public function add_follow() {
		$returnStr ['status_code'] = 0;
		if ($this->checkLogin ( 'U' ) != '') {
			$follow_id = $this->input->post ( 'user_id' );
			$followingListArr = explode ( ',', $this->data ['userDetails']->row ()->following );
			if (! in_array ( $follow_id, $followingListArr )) {
				$followingListArr [] = $follow_id;
				$newFollowingList = implode ( ',', $followingListArr );
				$followingCount = $this->data ['userDetails']->row ()->following_count;
				$followingCount ++;
				$dataArr = array (
						'following' => $newFollowingList,
						'following_count' => $followingCount 
				);
				$condition = array (
						'id' => $this->checkLogin ( 'U' ) 
				);
				$this->user_model->update_details ( USERS, $dataArr, $condition );
				$followUserDetails = $this->user_model->get_all_details ( USERS, array (
						'id' => $follow_id 
				) );
				if ($followUserDetails->num_rows () == 1) {
					$followersListArr = explode ( ',', $followUserDetails->row ()->followers );
					if (! in_array ( $this->checkLogin ( 'U' ), $followersListArr )) {
						$followersListArr [] = $this->checkLogin ( 'U' );
						$newFollowersList = implode ( ',', $followersListArr );
						$followersCount = $followUserDetails->row ()->followers_count;
						$followersCount ++;
						$dataArr = array (
								'followers' => $newFollowersList,
								'followers_count' => $followersCount 
						);
						$condition = array (
								'id' => $follow_id 
						);
						$this->user_model->update_details ( USERS, $dataArr, $condition );
					}
				}
				$actArr = array (
						'activity_name' => 'follow',
						'activity_id' => $follow_id,
						'user_id' => $this->checkLogin ( 'U' ),
						'activity_ip' => $this->input->ip_address () 
				);
				$this->user_model->simple_insert ( USER_ACTIVITY, $actArr );
				$datestring = "%Y-%m-%d %h:%i:%s";
				$time = time ();
				$createdTime = mdate ( $datestring, $time );
				$actArr = array (
						'activity' => 'follow',
						'activity_id' => $follow_id,
						'user_id' => $this->checkLogin ( 'U' ),
						'activity_ip' => $this->input->ip_address (),
						'created' => $createdTime 
				);
				$this->user_model->simple_insert ( NOTIFICATIONS, $actArr );
				$this->send_noty_mail ( $followUserDetails->result_array () );
				$returnStr ['status_code'] = 1;
			} else {
				$returnStr ['status_code'] = 1;
			}
		}
		echo json_encode ( $returnStr );
	}
	public function add_follows() {
		$returnStr ['status_code'] = 0;
		if ($this->checkLogin ( 'U' ) != '') {
			$follow_ids = $this->input->post ( 'user_ids' );
			$follow_ids_arr = explode ( ',', $follow_ids );
			$followingListArr = explode ( ',', $this->data ['userDetails']->row ()->following );
			foreach ( $follow_ids_arr as $flwRow ) {
				if (in_array ( $flwRow, $followingListArr )) {
					if (($key = array_search ( $flwRow, $follow_ids_arr )) !== false) {
						unset ( $follow_ids_arr [$key] );
					}
				}
			}
			if (count ( $follow_ids_arr ) > 0) {
				$newfollowingListArr = array_merge ( $followingListArr, $follow_ids_arr );
				$newFollowingList = implode ( ',', $newfollowingListArr );
				$followingCount = $this->data ['userDetails']->row ()->following_count;
				$newCount = count ( $follow_ids_arr );
				$followingCount = $followingCount + $newCount;
				$dataArr = array (
						'following' => $newFollowingList,
						'following_count' => $followingCount 
				);
				$condition = array (
						'id' => $this->checkLogin ( 'U' ) 
				);
				$this->user_model->update_details ( USERS, $dataArr, $condition );
				$conditionStr = 'where id IN (' . implode ( ',', $follow_ids_arr ) . ')';
				$followUserDetailsArr = $this->user_model->get_users_details ( $conditionStr );
				if ($followUserDetailsArr->num_rows () > 0) {
					foreach ( $followUserDetailsArr->result () as $followUserDetails ) {
						$followersListArr = explode ( ',', $followUserDetails->followers );
						if (! in_array ( $this->checkLogin ( 'U' ), $followersListArr )) {
							$followersListArr [] = $this->checkLogin ( 'U' );
							$newFollowersList = implode ( ',', $followersListArr );
							$followersCount = $followUserDetails->followers_count;
							$followersCount ++;
							$dataArr = array (
									'followers' => $newFollowersList,
									'followers_count' => $followersCount 
							);
							$condition = array (
									'id' => $followUserDetails->id 
							);
							$this->user_model->update_details ( USERS, $dataArr, $condition );
							$datestring = "%Y-%m-%d %h:%i:%s";
							$time = time ();
							$createdTime = mdate ( $datestring, $time );
							$actArr = array (
									'activity' => 'follow',
									'activity_id' => $followUserDetails->id,
									'user_id' => $this->checkLogin ( 'U' ),
									'activity_ip' => $this->input->ip_address (),
									'created' => $createdTime 
							);
							$this->user_model->simple_insert ( NOTIFICATIONS, $actArr );
							$this->send_noty_mails ( $followUserDetails );
						}
					}
				}
				$returnStr ['status_code'] = 1;
			} else {
				$returnStr ['status_code'] = 1;
			}
		}
		echo json_encode ( $returnStr );
	}
	public function delete_follow() {
		$returnStr ['status_code'] = 0;
		if ($this->checkLogin ( 'U' ) != '') {
			$follow_id = $this->input->post ( 'user_id' );
			$followingListArr = explode ( ',', $this->data ['userDetails']->row ()->following );
			if (in_array ( $follow_id, $followingListArr )) {
				if (($key = array_search ( $follow_id, $followingListArr )) !== false) {
					unset ( $followingListArr [$key] );
				}
				$newFollowingList = implode ( ',', $followingListArr );
				$followingCount = $this->data ['userDetails']->row ()->following_count;
				$followingCount --;
				$dataArr = array (
						'following' => $newFollowingList,
						'following_count' => $followingCount 
				);
				$condition = array (
						'id' => $this->checkLogin ( 'U' ) 
				);
				$this->user_model->update_details ( USERS, $dataArr, $condition );
				$followUserDetails = $this->user_model->get_all_details ( USERS, array (
						'id' => $follow_id 
				) );
				if ($followUserDetails->num_rows () == 1) {
					$followersListArr = explode ( ',', $followUserDetails->row ()->followers );
					if (in_array ( $this->checkLogin ( 'U' ), $followersListArr )) {
						if (($key = array_search ( $this->checkLogin ( 'U' ), $followersListArr )) !== false) {
							unset ( $followersListArr [$key] );
						}
						$newFollowersList = implode ( ',', $followersListArr );
						$followersCount = $followUserDetails->row ()->followers_count;
						$followersCount --;
						$dataArr = array (
								'followers' => $newFollowersList,
								'followers_count' => $followersCount 
						);
						$condition = array (
								'id' => $follow_id 
						);
						$this->user_model->update_details ( USERS, $dataArr, $condition );
					}
				}
				$actArr = array (
						'activity_name' => 'unfollow',
						'activity_id' => $follow_id,
						'user_id' => $this->checkLogin ( 'U' ),
						'activity_ip' => $this->input->ip_address () 
				);
				$this->user_model->simple_insert ( USER_ACTIVITY, $actArr );
				$returnStr ['status_code'] = 1;
			} else {
				$returnStr ['status_code'] = 1;
			}
		}
		echo json_encode ( $returnStr );
	}
	public function display_user_added() {
		$username = urldecode ( $this->uri->segment ( 2, 0 ) );
		$userProfileDetails = $this->user_model->get_all_details ( USERS, array (
				'user_name' => $username 
		) );
		if ($userProfileDetails->num_rows () == 1) {
			if ($userProfileDetails->row ()->visibility == 'Only you' && $userProfileDetails->row ()->id != $this->checkLogin ( 'U' )) {
				$this->load->view ( 'site/user/display_user_profile_private', $this->data );
			} else {
				$this->data ['heading'] = $username;
				$this->data ['userProfileDetails'] = $userProfileDetails;
				$this->data ['recentActivityDetails'] = $this->user_model->get_activity_details ( $userProfileDetails->row ()->id );
				$this->data ['addedProductDetails'] = $this->product_model->view_product_details ( ' where p.user_id=' . $userProfileDetails->row ()->id . ' and p.status="Publish"' );
				$this->data ['notSellProducts'] = $this->product_model->view_notsell_product_details ( ' where p.user_id=' . $userProfileDetails->row ()->id . ' and p.status="Publish"' );
				$this->load->view ( 'site/user/display_user_added', $this->data );
			}
		} else {
			redirect ( base_url () );
		}
	}
	public function display_user_lists() {
		$username = urldecode ( $this->uri->segment ( 2, 0 ) );
		$userProfileDetails = $this->user_model->get_all_details ( USERS, array (
				'user_name' => $username 
		) );
		if ($userProfileDetails->num_rows () == 1) {
			if ($userProfileDetails->row ()->visibility == 'Only you' && $userProfileDetails->row ()->id != $this->checkLogin ( 'U' )) {
				$this->load->view ( 'site/user/display_user_profile_private', $this->data );
			} else {
				$this->data ['heading'] = $username;
				$this->data ['userProfileDetails'] = $userProfileDetails;
				$this->data ['recentActivityDetails'] = $this->user_model->get_activity_details ( $userProfileDetails->row ()->id );
				$this->data ['listDetails'] = $this->product_model->get_all_details ( LISTS_DETAILS, array (
						'user_id' => $userProfileDetails->row ()->id 
				) );
				if ($this->data ['listDetails']->num_rows () > 0) {
					foreach ( $this->data ['listDetails']->result () as $listDetailsRow ) {
						$this->data ['listImg'] [$listDetailsRow->id] = '';
						if ($listDetailsRow->product_id != '') {
							$pidArr = array_filter ( explode ( ',', $listDetailsRow->product_id ) );
							
							$productDetails = '';
							if (count ( $pidArr ) > 0) {
								foreach ( $pidArr as $pidRow ) {
									if ($pidRow != '') {
										$productDetails = $this->product_model->get_all_details ( PRODUCT, array (
												'seller_product_id' => $pidRow,
												'status' => 'Publish' 
										) );
										if ($productDetails->num_rows () == 0) {
											$productDetails = $this->product_model->get_all_details ( USER_PRODUCTS, array (
													'seller_product_id' => $pidRow,
													'status' => 'Publish' 
											) );
										}
										if ($productDetails->num_rows () == 1)
											break;
									}
								}
							}
							if ($productDetails != '' && $productDetails->num_rows () == 1) {
								$this->data ['listImg'] [$listDetailsRow->id] = $productDetails->row ()->image;
							}
						}
					}
				}
				$this->load->view ( 'site/user/display_user_lists', $this->data );
			}
		} else {
			redirect ( base_url () );
		}
	}
	public function display_user_wants() {
		$username = urldecode ( $this->uri->segment ( 2, 0 ) );
		$userProfileDetails = $this->user_model->get_all_details ( USERS, array (
				'user_name' => $username 
		) );
		if ($userProfileDetails->num_rows () == 1) {
			if ($userProfileDetails->row ()->visibility == 'Only you' && $userProfileDetails->row ()->id != $this->checkLogin ( 'U' )) {
				$this->load->view ( 'site/user/display_user_profile_private', $this->data );
			} else {
				$this->data ['heading'] = $username;
				$this->data ['userProfileDetails'] = $userProfileDetails;
				$this->data ['recentActivityDetails'] = $this->user_model->get_activity_details ( $userProfileDetails->row ()->id );
				$wantList = $this->user_model->get_all_details ( WANTS_DETAILS, array (
						'user_id' => $userProfileDetails->row ()->id 
				) );
				$this->data ['wantProductDetails'] = $this->product_model->get_wants_product ( $wantList );
				$this->data ['notSellProducts'] = $this->product_model->get_notsell_wants_product ( $wantList );
				$this->load->view ( 'site/user/display_user_wants', $this->data );
			}
		} else {
			redirect ( base_url () );
		}
	}
	public function display_user_owns() {
		$username = urldecode ( $this->uri->segment ( 2, 0 ) );
		$userProfileDetails = $this->user_model->get_all_details ( USERS, array (
				'user_name' => $username 
		) );
		if ($userProfileDetails->num_rows () == 1) {
			if ($userProfileDetails->row ()->visibility == 'Only you' && $userProfileDetails->row ()->id != $this->checkLogin ( 'U' )) {
				$this->load->view ( 'site/user/display_user_profile_private', $this->data );
			} else {
				$this->data ['heading'] = $username;
				$this->data ['userProfileDetails'] = $userProfileDetails;
				$this->data ['recentActivityDetails'] = $this->user_model->get_activity_details ( $userProfileDetails->row ()->id );
				$productIdsArr = array_filter ( explode ( ',', $userProfileDetails->row ()->own_products ) );
				$productIds = '';
				if (count ( $productIdsArr ) > 0) {
					foreach ( $productIdsArr as $pidRow ) {
						if ($pidRow != '') {
							$productIds .= $pidRow . ',';
						}
					}
					$productIds = substr ( $productIds, 0, - 1 );
				}
				if ($productIds != '') {
					$this->data ['ownsProductDetails'] = $this->product_model->view_product_details ( ' where p.seller_product_id in (' . $productIds . ') and p.status="Publish"' );
					$this->data ['notSellProducts'] = $this->product_model->view_notsell_product_details ( ' where p.seller_product_id in (' . $productIds . ') and p.status="Publish"' );
				} else {
					$this->data ['addedProductDetails'] = '';
					$this->data ['notSellProducts'] = '';
				}
				$this->load->view ( 'site/user/display_user_owns', $this->data );
			}
		} else {
			redirect ( base_url () );
		}
	}
	public function display_user_following() {
		$username = urldecode ( $this->uri->segment ( 2, 0 ) );
		$userProfileDetails = $this->user_model->get_all_details ( USERS, array (
				'user_name' => $username 
		) );
		if ($userProfileDetails->num_rows () == 1) {
			if ($userProfileDetails->row ()->visibility == 'Only you' && $userProfileDetails->row ()->id != $this->checkLogin ( 'U' )) {
				$this->load->view ( 'site/user/display_user_profile_private', $this->data );
			} else {
				$this->data ['heading'] = $username;
				$this->data ['userProfileDetails'] = $userProfileDetails;
				$this->data ['recentActivityDetails'] = $this->user_model->get_activity_details ( $userProfileDetails->row ()->id );
				$fieldsArr = array (
						'*' 
				);
				$searchName = 'id';
				$searchArr = explode ( ',', $userProfileDetails->row ()->following );
				$joinArr = array ();
				$sortArr = array ();
				$limit = '';
				$this->data ['followingUserDetails'] = $followingUserDetails = $this->product_model->get_fields_from_many ( USERS, $fieldsArr, $searchName, $searchArr, $joinArr, $sortArr, $limit );
				if ($followingUserDetails->num_rows () > 0) {
					foreach ( $followingUserDetails->result () as $followingUserRow ) {
						$this->data ['followingUserLikeDetails'] [$followingUserRow->id] = $this->user_model->get_userlike_products ( $followingUserRow->id );
					}
				}
				$this->load->view ( 'site/user/display_user_following', $this->data );
			}
		} else {
			redirect ( base_url () );
		}
	}
	public function display_user_followers() {
		$username = urldecode ( $this->uri->segment ( 2, 0 ) );
		$userProfileDetails = $this->user_model->get_all_details ( USERS, array (
				'user_name' => $username 
		) );
		if ($userProfileDetails->num_rows () == 1) {
			if ($userProfileDetails->row ()->visibility == 'Only you' && $userProfileDetails->row ()->id != $this->checkLogin ( 'U' )) {
				$this->load->view ( 'site/user/display_user_profile_private', $this->data );
			} else {
				$this->data ['heading'] = $username;
				$this->data ['userProfileDetails'] = $userProfileDetails;
				$this->data ['recentActivityDetails'] = $this->user_model->get_activity_details ( $userProfileDetails->row ()->id );
				$fieldsArr = array (
						'*' 
				);
				$searchName = 'id';
				$searchArr = explode ( ',', $userProfileDetails->row ()->followers );
				$joinArr = array ();
				$sortArr = array ();
				$limit = '';
				$this->data ['followingUserDetails'] = $followingUserDetails = $this->product_model->get_fields_from_many ( USERS, $fieldsArr, $searchName, $searchArr, $joinArr, $sortArr, $limit );
				if ($followingUserDetails->num_rows () > 0) {
					foreach ( $followingUserDetails->result () as $followingUserRow ) {
						$this->data ['followingUserLikeDetails'] [$followingUserRow->id] = $this->user_model->get_userlike_products ( $followingUserRow->id );
					}
				}
				$this->load->view ( 'site/user/display_user_followers', $this->data );
			}
		} else {
			redirect ( base_url () );
		}
	}
	public function add_list_when_fancyy() {
		$returnStr ['status_code'] = 0;
		$returnStr ['listCnt'] = '';
		$returnStr ['wanted'] = 0;
		$uniqueListNames = array ();
		if ($this->checkLogin ( 'U' ) == '') {
			
			
			if($this->lang->line('Login required') != '') 
				{ 
					$message = stripslashes($this->lang->line('Login required')); 
				} 
				else 
				{
					$message = "Login required";
				}

				$returnStr ['message'] = $message;
			
		} else {
			$tid = $this->input->post ( 'tid' );
			$firstCatName = '';
			$firstCatDetails = '';
			$count = 1;
			
			// Adding lists which was not already created from product categories
			$productDetails = $this->user_model->get_all_details ( PRODUCT, array (
					'seller_product_id' => $tid 
			) );
			if ($productDetails->num_rows () == 0) {
				$productDetails = $this->user_model->get_all_details ( USER_PRODUCTS, array (
						'seller_product_id' => $tid 
				) );
			}
			if ($productDetails->num_rows () == 1) {
				$productCatArr = explode ( ',', $productDetails->row ()->category_id );
				if (count ( $productCatArr ) > 0) {
					$productCatNameArr = array ();
					foreach ( $productCatArr as $productCatID ) {
						if ($productCatID != '') {
							$productCatDetails = $this->user_model->get_all_details ( CATEGORY, array (
									'id' => $productCatID 
							) );
							if ($productCatDetails->num_rows () == 1) {
								if ($count == 1) {
									$firstCatName = $productCatDetails->row ()->cat_name;
								}
								$listConditionArr = array (
										'name' => $productCatDetails->row ()->cat_name,
										'user_id' => $this->checkLogin ( 'U' ) 
								);
								$listCheck = $this->user_model->get_all_details ( LISTS_DETAILS, $listConditionArr );
								if ($count == 1) {
									$firstCatDetails = $listCheck;
								}
								if ($listCheck->num_rows () == 0) {
									$this->user_model->simple_insert ( LISTS_DETAILS, $listConditionArr );
									$userDetails = $this->user_model->get_all_details ( USERS, array (
											'id' => $this->checkLogin ( 'U' ) 
									) );
									$listCount = $userDetails->row ()->lists;
									if ($listCount < 0 || $listCount == '') {
										$listCount = 0;
									}
									$listCount ++;
									$this->user_model->update_details ( USERS, array (
											'lists' => $listCount 
									), array (
											'id' => $this->checkLogin ( 'U' ) 
									) );
								}
								$count ++;
							}
						}
					}
				}
			}
			
			// Check the product id in list table
			$checkListsArr = $this->user_model->get_list_details ( $tid, $this->checkLogin ( 'U' ) );
			
			if ($checkListsArr->num_rows () == 0) {
				
				// Add the product id under the first category name
				if ($firstCatName != '') {
					$listConditionArr = array (
							'name' => $firstCatName,
							'user_id' => $this->checkLogin ( 'U' ) 
					);
					if ($firstCatDetails == '' || $firstCatDetails->num_rows () == 0) {
						$dataArr = array (
								'product_id' => $tid 
						);
					} else {
						$productRowArr = explode ( ',', $firstCatDetails->row ()->product_id );
						$productRowArr [] = $tid;
						$newProductRowArr = implode ( ',', $productRowArr );
						$dataArr = array (
								'product_id' => $newProductRowArr 
						);
					}
					$this->user_model->update_details ( LISTS_DETAILS, $dataArr, $listConditionArr );
					$listCntDetails = $this->user_model->get_all_details ( LISTS_DETAILS, $listConditionArr );
					if ($listCntDetails->num_rows () == 1) {
						array_push ( $uniqueListNames, $listCntDetails->row ()->id );
						$returnStr ['listCnt'] .= '<li class="selected"><label for="' . $listCntDetails->row ()->id . '"><input type="checkbox" checked="checked" id="' . $listCntDetails->row ()->id . '" name="' . $listCntDetails->row ()->id . '">' . $listCntDetails->row ()->name . '</label></li>';
					}
				}
			} else {
				
				// Get all the lists which contain this product
				foreach ( $checkListsArr->result () as $checkListsRow ) {
					array_push ( $uniqueListNames, $checkListsRow->id );
					$returnStr ['listCnt'] .= '<li class="selected"><label for="' . $checkListsRow->id . '"><input type="checkbox" checked="checked" id="' . $checkListsRow->id . '" name="' . $checkListsRow->id . '">' . $checkListsRow->name . '</label></li>';
				}
			}
			$all_lists = $this->user_model->get_all_details ( LISTS_DETAILS, array (
					'user_id' => $this->checkLogin ( 'U' ) 
			) );
			if ($all_lists->num_rows () > 0) {
				foreach ( $all_lists->result () as $all_lists_row ) {
					if (! in_array ( $all_lists_row->id, $uniqueListNames )) {
						$returnStr ['listCnt'] .= '<li><label for="' . $all_lists_row->id . '"><input type="checkbox" id="' . $all_lists_row->id . '" name="' . $all_lists_row->id . '">' . $all_lists_row->name . '</label></li>';
					}
				}
			}
			
			// Check the product wanted status
			$wantedProducts = $this->user_model->get_all_details ( WANTS_DETAILS, array (
					'user_id' => $this->checkLogin ( 'U' ) 
			) );
			if ($wantedProducts->num_rows () == 1) {
				$wantedProductsArr = explode ( ',', $wantedProducts->row ()->product_id );
				if (in_array ( $tid, $wantedProductsArr )) {
					$returnStr ['wanted'] = 1;
				}
			}
			$returnStr ['status_code'] = 1;
		}
		echo json_encode ( $returnStr );
	}
	public function add_item_to_lists() {
		$returnStr ['status_code'] = 0;
		if ($this->checkLogin ( 'U' ) == '') {
			if($this->lang->line('You must login') != '') 
				{ 
					$message = stripslashes($this->lang->line('You must login')); 
				} 
				else 
				{
					$message = "You must login";
				}
				$returnStr ['message'] = $message;
			
		} else {
			$tid = $this->input->post ( 'tid' );
			$lid = $this->input->post ( 'list_ids' );
			$listDetails = $this->user_model->get_all_details ( LISTS_DETAILS, array (
					'id' => $lid 
			) );
			if ($listDetails->num_rows () == 1) {
				$product_ids = explode ( ',', $listDetails->row ()->product_id );
				if (! in_array ( $tid, $product_ids )) {
					array_push ( $product_ids, $tid );
				}
				$new_product_ids = implode ( ',', $product_ids );
				$this->user_model->update_details ( LISTS_DETAILS, array (
						'product_id' => $new_product_ids 
				), array (
						'id' => $lid 
				) );
				$returnStr ['status_code'] = 1;
			}
		}
		echo json_encode ( $returnStr );
	}
	public function remove_item_from_lists() {
		$returnStr ['status_code'] = 0;
		if ($this->checkLogin ( 'U' ) == '') {
			if($this->lang->line('You must login') != '') 
				{ 
					$message = stripslashes($this->lang->line('You must login')); 
				} 
				else 
				{
					$message = "You must login";
				}
				$returnStr ['message'] = $message;
			
		} else {
			$tid = $this->input->post ( 'tid' );
			$lid = $this->input->post ( 'list_ids' );
			$listDetails = $this->user_model->get_all_details ( LISTS_DETAILS, array (
					'id' => $lid 
			) );
			if ($listDetails->num_rows () == 1) {
				$product_ids = explode ( ',', $listDetails->row ()->product_id );
				if (in_array ( $tid, $product_ids )) {
					if (($key = array_search ( $tid, $product_ids )) !== false) {
						unset ( $product_ids [$key] );
					}
				}
				$new_product_ids = implode ( ',', $product_ids );
				$this->user_model->update_details ( LISTS_DETAILS, array (
						'product_id' => $new_product_ids 
				), array (
						'id' => $lid 
				) );
				$returnStr ['status_code'] = 1;
			}
		}
		echo json_encode ( $returnStr );
	}
	public function add_want_tag() {
		$returnStr ['status_code'] = 0;
		if ($this->checkLogin ( 'U' ) == '') {
				if($this->lang->line('You must login') != '') 
				{ 
					$message = stripslashes($this->lang->line('You must login')); 
				} 
				else 
				{
					$message = "You must login";
				}
				$returnStr ['message'] = $message;
			
		} else {
			$tid = $this->input->post ( 'thing_id' );
			$wantDetails = $this->user_model->get_all_details ( WANTS_DETAILS, array (
					'user_id' => $this->checkLogin ( 'U' ) 
			) );
			if ($wantDetails->num_rows () == 1) {
				$product_ids = explode ( ',', $wantDetails->row ()->product_id );
				if (! in_array ( $tid, $product_ids )) {
					array_push ( $product_ids, $tid );
				}
				$new_product_ids = implode ( ',', $product_ids );
				$this->user_model->update_details ( WANTS_DETAILS, array (
						'product_id' => $new_product_ids 
				), array (
						'user_id' => $this->checkLogin ( 'U' ) 
				) );
			} else {
				$dataArr = array (
						'user_id' => $this->checkLogin ( 'U' ),
						'product_id' => $tid 
				);
				$this->user_model->simple_insert ( WANTS_DETAILS, $dataArr );
			}
			$wantCount = $this->data ['userDetails']->row ()->want_count;
			if ($wantCount <= 0 || $wantCount == '') {
				$wantCount = 0;
			}
			$wantCount ++;
			$dataArr = array (
					'want_count' => $wantCount 
			);
			$ownProducts = explode ( ',', $this->data ['userDetails']->row ()->own_products );
			if (in_array ( $tid, $ownProducts )) {
				if (($key = array_search ( $tid, $ownProducts )) !== false) {
					unset ( $ownProducts [$key] );
				}
				$ownCount = $this->data ['userDetails']->row ()->own_count;
				$ownCount --;
				$dataArr ['own_count'] = $ownCount;
				$dataArr ['own_products'] = implode ( ',', $ownProducts );
			}
			$this->user_model->update_details ( USERS, $dataArr, array (
					'id' => $this->checkLogin ( 'U' ) 
			) );
			$returnStr ['status_code'] = 1;
		}
		echo json_encode ( $returnStr );
	}
	public function delete_want_tag() {
		$returnStr ['status_code'] = 0;
		if ($this->checkLogin ( 'U' ) == '') {
			if($this->lang->line('You must login') != '') 
				{ 
					$message = stripslashes($this->lang->line('You must login')); 
				} 
				else 
				{
					$message = "You must login";
				}
				$returnStr ['message'] = $message;
			
		} else {
			$tid = $this->input->post ( 'thing_id' );
			$wantDetails = $this->user_model->get_all_details ( WANTS_DETAILS, array (
					'user_id' => $this->checkLogin ( 'U' ) 
			) );
			if ($wantDetails->num_rows () == 1) {
				$product_ids = explode ( ',', $wantDetails->row ()->product_id );
				if (in_array ( $tid, $product_ids )) {
					if (($key = array_search ( $tid, $product_ids )) !== false) {
						unset ( $product_ids [$key] );
					}
				}
				$new_product_ids = implode ( ',', $product_ids );
				$this->user_model->update_details ( WANTS_DETAILS, array (
						'product_id' => $new_product_ids 
				), array (
						'user_id' => $this->checkLogin ( 'U' ) 
				) );
				$wantCount = $this->data ['userDetails']->row ()->want_count;
				if ($wantCount <= 0 || $wantCount == '') {
					$wantCount = 1;
				}
				$wantCount --;
				$this->user_model->update_details ( USERS, array (
						'want_count' => $wantCount 
				), array (
						'id' => $this->checkLogin ( 'U' ) 
				) );
				$returnStr ['status_code'] = 1;
			}
		}
		echo json_encode ( $returnStr );
	}
	public function create_list() {
		$returnStr ['status_code'] = 0;
		if ($this->checkLogin ( 'U' ) == '') {
			if($this->lang->line('You must login') != '') 
				{ 
					$message = stripslashes($this->lang->line('You must login')); 
				} 
				else 
				{
					$message = "You must login";
				}
				$returnStr ['message'] = $message;
			
		} else {
			$tid = $this->input->post ( 'tid' );
			$list_name = $this->input->post ( 'list_name' );
			$category_id = $this->input->post ( 'category_id' );
			$checkList = $this->user_model->get_all_details ( LISTS_DETAILS, array (
					'name' => $list_name,
					'user_id' => $this->checkLogin ( 'U' ) 
			) );
			if ($checkList->num_rows () == 0) {
				$dataArr = array (
						'user_id' => $this->checkLogin ( 'U' ),
						'name' => $list_name,
						'product_id' => $tid 
				);
				if ($category_id != '') {
					$dataArr ['category_id'] = $category_id;
				}
				$this->user_model->simple_insert ( LISTS_DETAILS, $dataArr );
				$userDetails = $this->user_model->get_all_details ( USERS, array (
						'id' => $this->checkLogin ( 'U' ) 
				) );
				$listCount = $userDetails->row ()->lists;
				if ($listCount < 0 || $listCount == '') {
					$listCount = 0;
				}
				$listCount ++;
				$this->user_model->update_details ( USERS, array (
						'lists' => $listCount 
				), array (
						'id' => $this->checkLogin ( 'U' ) 
				) );
				$returnStr ['list_id'] = $this->user_model->get_last_insert_id ();
				$returnStr ['new_list'] = 1;
			} else {
				$productArr = explode ( ',', $checkList->row ()->product_id );
				if (! in_array ( $tid, $productArr )) {
					array_push ( $productArr, $tid );
				}
				$product_id = implode ( ',', $productArr );
				$dataArr = array (
						'product_id' => $product_id 
				);
				if ($category_id != '') {
					$dataArr ['category_id'] = $category_id;
				}
				$this->user_model->update_details ( LISTS_DETAILS, $dataArr, array (
						'user_id' => $this->checkLogin ( 'U' ),
						'name' => $list_name 
				) );
				$returnStr ['list_id'] = $checkList->row ()->id;
				$returnStr ['new_list'] = 0;
			}
			$returnStr ['status_code'] = 1;
		}
		echo json_encode ( $returnStr );
	}
	public function search_users() {
		$search_key = $this->input->post ( 'term' );
		$returnStr = array ();
		if ($search_key != '') {
			$userList = $this->user_model->get_search_user_list ( $search_key, $this->checkLogin ( 'U' ) );
			if ($userList->num_rows () > 0) {
				$i = 0;
				foreach ( $userList->result () as $userRow ) {
					$userArr ['id'] = $userRow->id;
					$userArr ['fullname'] = $userRow->full_name;
					$userArr ['username'] = $userRow->user_name;
					if ($userRow->image != '') {
						$userArr ['image_url'] = 'images/users/' . $userRow->image;
					} else {
						$userArr ['image_url'] = 'images/users/user-thumb1.png';
					}
					array_push ( $returnStr, $userArr );
					$i ++;
				}
			}
		}
		echo json_encode ( $returnStr );
	}
	public function seller_signup_form() {
		if ($this->checkLogin ( 'U' ) == '') {
			redirect ( base_url () );
		} else {
			if ($this->data ['userDetails']->row ()->is_verified == 'No') {
				if($this->lang->line('Please confirm your email first') != '') 
				{ 
					$message = stripslashes($this->lang->line('Please confirm your email first')); 
				} 
				else 
				{
					$message = "Please confirm your email first";
				}
				$this->setErrorMessage ('error',$message);
				redirect ( base_url () );
			} else {
				if($this->lang->line('Seller Signup') != '') 
				{ 
					$message = stripslashes($this->lang->line('Seller Signup')); 
				} 
				else 
				{
					$message = "Seller Signup";
				}
				$this->data ['heading'] = $message;
				$this->load->view ( 'site/user/seller_register', $this->data );
			}
		}
	}
	public function create_brand_form() {
		if ($this->checkLogin ( 'U' ) == '') {
			redirect ( base_url () );
		} else {
			if($this->lang->line('Seller Signup') != '') 
				{ 
					$message = stripslashes($this->lang->line('Seller Signup')); 
				} 
				else 
				{
					$message = "Seller Signup";
				}
				$this->data ['heading'] = $message;
			$this->load->view ( 'site/user/seller_register', $this->data );
		}
	}
	public function seller_signup() {
		if ($this->checkLogin ( 'U' ) == '') {
			redirect ( base_url () );
		} else {
			if ($this->data ['userDetails']->row ()->is_verified == 'No') {
				if($this->lang->line('Please confirm your email first') != '') 
				{ 
					$message = stripslashes($this->lang->line('Please confirm your email first')); 
				} 
				else 
				{
					$message = "Please confirm your email first";
				}
				$this->setErrorMessage ( 'error',$message);
				redirect ( 'create-brand' );
				// echo "<script>window.history.go(-1)/script>";
			} else {
				$dataArr = array (
						'request_status' => 'Pending' 
				);
				$this->user_model->commonInsertUpdate ( USERS, 'update', array (), $dataArr, array (
						'id' => $this->checkLogin ( 'U' ) 
				) );
				if($this->lang->line('Welcome onboard ! Our team is evaluating your request. We will contact you shortly') != '') 
				{ 
					$message = stripslashes($this->lang->line('Welcome onboard ! Our team is evaluating your request. We will contact you shortly')); 
				} 
				else 
				{
					$message = "Welcome onboard ! Our team is evaluating your request. We will contact you shortly";
				}

				$this->setErrorMessage ( 'success', $message);
				redirect ( base_url () );
			}
		}
	}
	public function view_purchase() {
		if ($this->checkLogin ( 'U' ) == '') {
			show_404 ();
		} else {
			$uid = $this->uri->segment ( 2, 0 );
			$dealCode = $this->uri->segment ( 3, 0 );
			if ($uid != $this->checkLogin ( 'U' )) {
				show_404 ();
			} else {
				$purchaseList = $this->user_model->get_purchase_list ( $uid, $dealCode );
				$invoice = $this->get_invoice ( $purchaseList );
				echo $invoice;
			}
		}
	}
	public function view_order() {
		if ($this->checkLogin ( 'U' ) == '') {
			show_404 ();
		} else {
			$uid = $this->uri->segment ( 2, 0 );
			$dealCode = $this->uri->segment ( 3, 0 );
			if ($uid != $this->checkLogin ( 'U' )) {
				show_404 ();
			} else {
				$orderList = $this->user_model->get_order_list ( $uid, $dealCode );
				$invoice = $this->get_invoice ( $orderList );
				echo $invoice;
			}
		}
	}
	public function get_invoice($PrdList) {
		$shipAddRess = $this->user_model->get_all_details ( SHIPPING_ADDRESS, array (
				'id' => $PrdList->row ()->shippingid 
		) );
		$message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width"/></head>
<title>Product Order Confirmation</title>
<body>
<div style="width:1012px;background:#FFFFFF; margin:0 auto;">
<div style="width:100%;background:#454B56; float:left; margin:0 auto;">
    <div style="padding:20px 0 10px 15px;float:left; width:50%;"><a href="' . base_url () . '" target="_blank" id="logo"><img src="' . base_url () . 'images/logo/' . $this->data ['logo'] . '" alt="' . $this->data ['WebsiteTitle'] . '" title="' . $this->data ['WebsiteTitle'] . '"></a></div>
	
</div>			
<!--END OF LOGO-->
    
 <!--start of deal-->
    <div style="width:970px;background:#FFFFFF;float:left; padding:20px; border:1px solid #454B56; ">
    
	<div style=" float:right; width:35%; margin-bottom:20px; margin-right:7px;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:1px solid #cecece;">
			  <tr bgcolor="#f3f3f3">
                <td width="87"  style="border-right:1px solid #cecece;"><span style="font-size:13px; font-family:Arial, Helvetica, sans-serif; text-align:center; width:100%; font-weight:bold; color:#000000; line-height:38px; float:left;">Order Id</span></td>
                <td  width="100"><span style="font-size:12px; font-family:Arial, Helvetica, sans-serif; font-weight:normal; color:#000000; line-height:38px; text-align:center; width:100%; float:left;">#' . $PrdList->row ()->dealCodeNumber . '</span></td>
              </tr>
              <tr bgcolor="#f3f3f3">
                <td width="87"  style="border-right:1px solid #cecece;"><span style="font-size:13px; font-family:Arial, Helvetica, sans-serif; text-align:center; width:100%; font-weight:bold; color:#000000; line-height:38px; float:left;">Order Date</span></td>
                <td  width="100"><span style="font-size:12px; font-family:Arial, Helvetica, sans-serif; font-weight:normal; color:#000000; line-height:38px; text-align:center; width:100%; float:left;">' . date ( "F j, Y g:i a", strtotime ( $PrdList->row ()->created ) ) . '</span></td>
              </tr>
			 
              </table>
        	</div>
		
    <div style="float:left; width:100%;">
	
    <div style="width:49%; float:left; border:1px solid #cccccc; margin-right:10px;">
    	<span style=" border-bottom:1px solid #cccccc; background:#f3f3f3; width:95.8%; float:left; padding:10px; font-family:Arial, Helvetica, sans-serif; font-size:13px; font-weight:bold; color:#000305;">Shipping Address</span>
    		<div style="float:left; padding:10px; width:96%;  font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#030002; line-height:28px;">
            	<table width="100%" border="0" cellpadding="0" cellspacing="0">
                	<tr><td>Full Name</td><td>:</td><td>' . stripslashes ( $shipAddRess->row ()->full_name ) . '</td></tr>
                    <tr><td>Address</td><td>:</td><td>' . stripslashes ( $shipAddRess->row ()->address1 ) . '</td></tr>
					<tr><td>Address 2</td><td>:</td><td>' . stripslashes ( $shipAddRess->row ()->address2 ) . '</td></tr>
					<tr><td>City</td><td>:</td><td>' . stripslashes ( $shipAddRess->row ()->city ) . '</td></tr>
					<tr><td>Country</td><td>:</td><td>' . stripslashes ( $shipAddRess->row ()->country ) . '</td></tr>
					<tr><td>State</td><td>:</td><td>' . stripslashes ( $shipAddRess->row ()->state ) . '</td></tr>
					<tr><td>Zipcode</td><td>:</td><td>' . stripslashes ( $shipAddRess->row ()->postal_code ) . '</td></tr>
					<tr><td>Phone Number</td><td>:</td><td>' . stripslashes ( $shipAddRess->row ()->phone ) . '</td></tr>
            	</table>
            </div>
     </div>
    
    <div style="width:49%; float:left; border:1px solid #cccccc;">
    	<span style=" border-bottom:1px solid #cccccc; background:#f3f3f3; width:95.7%; float:left; padding:10px; font-family:Arial, Helvetica, sans-serif; font-size:13px; font-weight:bold; color:#000305;">Billing Address</span>
    		<div style="float:left; padding:10px; width:96%;  font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#030002; line-height:28px;">
            	<table width="100%" border="0" cellpadding="0" cellspacing="0">
                	<tr><td>Full Name</td><td>:</td><td>' . stripslashes ( $PrdList->row ()->full_name ) . '</td></tr>
                    <tr><td>Address</td><td>:</td><td>' . stripslashes ( $PrdList->row ()->address ) . '</td></tr>
					<tr><td>Address 2</td><td>:</td><td>' . stripslashes ( $PrdList->row ()->address2 ) . '</td></tr>
					<tr><td>City</td><td>:</td><td>' . stripslashes ( $PrdList->row ()->city ) . '</td></tr>
					<tr><td>Country</td><td>:</td><td>' . stripslashes ( $PrdList->row ()->country ) . '</td></tr>
					<tr><td>State</td><td>:</td><td>' . stripslashes ( $PrdList->row ()->state ) . '</td></tr>
					<tr><td>Zipcode</td><td>:</td><td>' . stripslashes ( $PrdList->row ()->postal_code ) . '</td></tr>
					<tr><td>Phone Number</td><td>:</td><td>' . stripslashes ( $PrdList->row ()->phone_no ) . '</td></tr>
            	</table>
            </div>
    </div>
</div> 
	   
<div style="float:left; width:100%; margin-right:3%; margin-top:10px; font-size:14px; font-weight:normal; line-height:28px;  font-family:Arial, Helvetica, sans-serif; color:#000; overflow:hidden;">   
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
    	<td colspan="3"><table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:1px solid #cecece; width:99.5%;">
        <tr bgcolor="#f3f3f3">
        	<td width="17%" style="border-right:1px solid #cecece; text-align:center;"><span style="font-size:12px; font-family:Arial, Helvetica, sans-serif; font-weight:bold; color:#000000; line-height:38px; text-align:center;">Bag Items</span></td>
            <td width="43%" style="border-right:1px solid #cecece;text-align:center;"><span style="font-size:12px; font-family:Arial, Helvetica, sans-serif; font-weight:bold; color:#000000; line-height:38px; text-align:center;">Product Name</span></td>
            <td width="12%" style="border-right:1px solid #cecece;text-align:center;"><span style="font-size:12px; font-family:Arial, Helvetica, sans-serif; font-weight:bold; color:#000000; line-height:38px; text-align:center;">Qty</span></td>
            <td width="14%" style="border-right:1px solid #cecece;text-align:center;"><span style="font-size:12px; font-family:Arial, Helvetica, sans-serif; font-weight:bold; color:#000000; line-height:38px; text-align:center;">Unit Price</span></td>
            <td width="15%" style="text-align:center;"><span style="font-size:12px; font-family:Arial, Helvetica, sans-serif; font-weight:bold; color:#000000; line-height:38px; text-align:center;">Sub Total</span></td>
         </tr>';
		
		$disTotal = 0;
		$grantTotal = 0;
		foreach ( $PrdList->result () as $cartRow ) {
			$InvImg = @explode ( ',', $cartRow->image );
			$unitPrice = ($cartRow->price * (0.01 * $cartRow->product_tax_cost)) + $cartRow->product_shipping_cost + $cartRow->price;
			$uTot = $unitPrice * $cartRow->quantity;
			if ($cartRow->attr_name != '') {
				$atr = '<br>' . $cartRow->attr_name;
			} else {
				$atr = '';
			}
			$message .= '<tr>
            <td style="border-right:1px solid #cecece; text-align:center;border-top:1px solid #cecece;"><span style="font-size:13px; font-family:Arial, Helvetica, sans-serif; font-weight:normal; color:#000000; line-height:30px;  text-align:center;"><img src="' . base_url () . PRODUCTPATH . $InvImg [0] . '" alt="' . stripslashes ( $cartRow->product_name ) . '" width="70" /></span></td>
			<td style="border-right:1px solid #cecece;text-align:center;border-top:1px solid #cecece;"><span style="font-size:13px; font-family:Arial, Helvetica, sans-serif; font-weight:normal; color:#000000; line-height:30px;  text-align:center;">' . stripslashes ( $cartRow->product_name ) . $atr . '</span></td>
            <td style="border-right:1px solid #cecece;text-align:center;border-top:1px solid #cecece;"><span style="font-size:13px; font-family:Arial, Helvetica, sans-serif; font-weight:normal; color:#000000; line-height:30px;  text-align:center;">' . strtoupper ( $cartRow->quantity ) . '</span></td>
            <td style="border-right:1px solid #cecece;text-align:center;border-top:1px solid #cecece;"><span style="font-size:13px; font-family:Arial, Helvetica, sans-serif; font-weight:normal; color:#000000; line-height:30px;  text-align:center;">' . $this->data ['currencySymbol'] . number_format ( $unitPrice, 2, '.', '' ) . '</span></td>
            <td style="text-align:center;border-top:1px solid #cecece;"><span style="font-size:13px; font-family:Arial, Helvetica, sans-serif; font-weight:normal; color:#000000; line-height:30px;  text-align:center;">' . $this->data ['currencySymbol'] . number_format ( $uTot, 2, '.', '' ) . '</span></td>
        </tr>';
			$grantTotal = $grantTotal + $uTot;
		}
		$private_total = $grantTotal - $PrdList->row ()->discountAmount;
		$private_total = $private_total + $PrdList->row ()->tax + $PrdList->row ()->shippingcost;
		
		$message .= '</table></td> </tr><tr><td colspan="3"><table border="0" cellspacing="0" cellpadding="0" style=" margin:10px 0px; width:99.5%;"><tr>
			<td width="460" valign="top" >';
		if ($PrdList->row ()->note != '') {
			$message .= '<table width="97%" border="0"  cellspacing="0" cellpadding="0"><tr>
                <td width="87" ><span style="font-size:13px; font-family:Arial, Helvetica, sans-serif; text-align:left; width:100%; font-weight:bold; color:#000000; line-height:38px; float:left;">Note:</span></td>
               
            </tr>
			<tr>
                <td width="87"  style="border:1px solid #cecece;"><span style="font-size:13px; font-family:Arial, Helvetica, sans-serif; text-align:left; width:97%; color:#000000; line-height:24px; float:left; margin:10px;">' . stripslashes ( $PrdList->row ()->note ) . '</span></td>
            </tr></table>';
		}
		
		if ($PrdList->row ()->order_gift == 1) {
			$message .= '<table width="97%" border="0"  cellspacing="0" cellpadding="0"  style="margin-top:10px;"><tr>
                <td width="87"  style="border:1px solid #cecece;"><span style="font-size:16px; font-weight:bold; font-family:Arial, Helvetica, sans-serif; text-align:center; width:97%; color:#000000; line-height:24px; float:left; margin:10px;">This Order is a gift</span></td>
            </tr></table>';
		}
		
		$message .= '</td>
            <td width="174" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:1px solid #cecece;">
            <tr bgcolor="#f3f3f3">
                <td width="87"  style="border-right:1px solid #cecece;border-bottom:1px solid #cecece;"><span style="font-size:13px; font-family:Arial, Helvetica, sans-serif; text-align:center; width:100%; font-weight:bold; color:#000000; line-height:38px; float:left;">Sub Total</span></td>
                <td  style="border-bottom:1px solid #cecece;" width="69"><span style="font-size:12px; font-family:Arial, Helvetica, sans-serif; font-weight:normal; color:#000000; line-height:38px; text-align:center; width:100%; float:left;">' . $this->data ['currencySymbol'] . number_format ( $grantTotal, '2', '.', '' ) . '</span></td>
            </tr>
			<tr>
                <td width="87"  style="border-right:1px solid #cecece;border-bottom:1px solid #cecece;"><span style="font-size:13px; font-family:Arial, Helvetica, sans-serif; text-align:center; width:100%; font-weight:bold; color:#000000; line-height:38px; float:left;">Discount Amount</span></td>
                <td  style="border-bottom:1px solid #cecece;" width="69"><span style="font-size:12px; font-family:Arial, Helvetica, sans-serif; font-weight:normal; color:#000000; line-height:38px; text-align:center; width:100%; float:left;">' . $this->data ['currencySymbol'] . number_format ( $PrdList->row ()->discountAmount, '2', '.', '' ) . '</span></td>
            </tr>
		<tr bgcolor="#f3f3f3">
            <td width="31" style="border-right:1px solid #cecece;border-bottom:1px solid #cecece;"><span style="font-size:13px; font-family:Arial, Helvetica, sans-serif; font-weight:bold; text-align:center; width:100%; color:#000000; line-height:38px; float:left;">Shipping Cost</span></td>
                <td  style="border-bottom:1px solid #cecece;" width="69"><span style="font-size:12px; font-family:Arial, Helvetica, sans-serif; font-weight:normal; color:#000000; line-height:38px; text-align:center; width:100%;  float:left;">' . $this->data ['currencySymbol'] . number_format ( $PrdList->row ()->shippingcost, 2, '.', '' ) . '</span></td>
              </tr>
			  <tr>
            <td width="31" style="border-right:1px solid #cecece;border-bottom:1px solid #cecece;"><span style="font-size:13px; font-family:Arial, Helvetica, sans-serif; font-weight:bold; text-align:center; width:100%; color:#000000; line-height:38px; float:left;">Shipping Tax</span></td>
                <td  style="border-bottom:1px solid #cecece;" width="69"><span style="font-size:12px; font-family:Arial, Helvetica, sans-serif; font-weight:normal; color:#000000; line-height:38px; text-align:center; width:100%;  float:left;">' . $this->data ['currencySymbol'] . number_format ( $PrdList->row ()->tax, 2, '.', '' ) . '</span></td>
              </tr>
			  <tr bgcolor="#f3f3f3">
                <td width="87" style="border-right:1px solid #cecece;"><span style="font-size:13px; font-family:Arial, Helvetica, sans-serif; font-weight:bold; color:#000000; line-height:38px; text-align:center; width:100%; float:left;">Grand Total</span></td>
                <td width="31"><span style="font-size:12px; font-family:Arial, Helvetica, sans-serif; font-weight:normal; color:#000000; line-height:38px; text-align:center; width:100%;  float:left;">' . $this->data ['currencySymbol'] . number_format ( $private_total, '2', '.', '' ) . '</span></td>
              </tr>
            </table></td>
            </tr>
        </table></td>
        </tr>
    </table>
        </div>
        
        <!--end of left--> 
		
            
            <div style="width:27.4%; margin-right:5px; float:right;">
            
           
            </div>
        
        <div style="clear:both"></div>
        
    </div>
    </div></body></html>';
		return $message;
	}
	public function change_order_status() {
		if ($this->checkLogin ( 'U' ) == '') {
			show_404 ();
		} else {
			$uid = $this->input->post ( 'seller' );
			if ($uid != $this->checkLogin ( 'U' )) {
				show_404 ();
			} else {
				$returnStr ['status_code'] = 0;
				$dealCode = $this->input->post ( 'dealCode' );
				$status = $this->input->post ( 'value' );
				$dataArr = array (
						'shipping_status' => $status 
				);
				$conditionArr = array (
						'dealCodeNumber' => $dealCode,
						'sell_id' => $uid 
				);
				$this->user_model->update_details ( PAYMENT, $dataArr, $conditionArr );
				$returnStr ['status_code'] = 1;
				echo json_encode ( $returnStr );
			}
		}
	}
	public function display_user_lists_home() {
		$lid = $this->uri->segment ( '4', '0' );
		$uname = $this->uri->segment ( '2', '0' );
		$this->data ['user_profile_details'] = $userProfileDetails = $this->user_model->get_all_details ( USERS, array (
				'user_name' => $uname 
		) );
		if ($userProfileDetails->row ()->visibility == 'Only you' && $userProfileDetails->row ()->id != $this->checkLogin ( 'U' )) {
			$this->load->view ( 'site/user/display_user_profile_private', $this->data );
		} else {
			
			$this->data ['list_details'] = $list_details = $this->product_model->get_all_details (LISTS_DETAILS, array (
					'id' => $lid 
			) );
			
		
			$searchArr = array_filter ( explode ( ',', $list_details->row ()->product_id ) );
			if (count ( $searchArr ) > 0) {
				foreach ( $searchArr as $searchphotoid ) {
					$wishlist_image[$searchphotoid]  = $this->product_model->get_wishlistphoto ( $searchphotoid );
				}
				
				$this->data ['product_details'] = $product_details = $this->product_model->get_product_details_wishlist_one_category ( $searchArr );
				
				
				
				$this->data ['totalProducts'] = $this->data ['product_details']->num_rows ();
			}

			/* Experience Listing */
			$this->data ['totalExperience'] = 0;
            if($this->data['experienceExistCount']>0) /* check experienece module enabled */ { 
				$experienceArr = array_filter ( explode ( ',', $list_details->row ()->experience_id ) );
				//print_r($experienceArr);exit; 
				$this->data ['experience_details'] =  $this->data['wishlist_ExpImage']  = array();
				if (count ( $experienceArr ) > 0) {
					foreach ( $experienceArr as $experiencePhotoid ) {
						$this->load->model('experience_model');
						$wishlist_ExpImage[$experiencePhotoid]  = $this->experience_model->get_wishlistphoto ( $experiencePhotoid );
					}
					$this->data ['experience_details'] = $experience_details = $this->experience_model->get_experience_details_wishlist_one_category ( $experienceArr );
					
					$this->data ['totalExperience'] = $this->data ['experience_details']->num_rows();

					$this->data['wishlist_ExpImage']=$wishlist_ExpImage;
					//print_r($this->data['experience_details']->result());exit;
				}
				//print_r(count($this->data ['experience_details']));exit;
			}
			/* Experience Listing  */	


			$this->data['wishlist_image']=$wishlist_image;
			
			$this->load->view ( 'site/user/user_list_home', $this->data );
		}
	}
	public function DeleteallWishList() {
		$lid = $this->uri->segment ( '4', '0' );
	//echo($lid);die;
			$this->data['deletewishlist'] = $this->product_model->alldeletewishlist_details($lid );
			
			$uid = $this->uri->segment ( '2', '0' );
			//echo($this->data ['userDetails']->row ()->id );die;
			
			//redirect('users/'.$uid.'/wishlists');
			redirect('users/'.$this->data ['userDetails']->row ()->id.'/wishlists');
	}
	public function display_user_lists_edit() {
		$this->load->view ( 'site/user/user_list_edit' );
	}
	public function display_user_lists_followers() {
		$lid = $this->uri->segment ( '4', '0' );
		$uname = $this->uri->segment ( '2', '0' );
		$this->data ['user_profile_details'] = $userProfileDetails = $this->user_model->get_all_details ( USERS, array (
				'user_name' => $uname 
		) );
		if ($userProfileDetails->row ()->visibility == 'Only you' && $userProfileDetails->row ()->id != $this->checkLogin ( 'U' )) {
			$this->load->view ( 'site/user/display_user_profile_private', $this->data );
		} else {
			$this->data ['list_details'] = $list_details = $this->product_model->get_all_details ( LISTS_DETAILS, array (
					'id' => $lid,
					'user_id' => $this->data ['user_profile_details']->row ()->id 
			) );
			if ($this->data ['list_details']->num_rows () == 0) {
				show_404 ();
			} else {
				$fieldsArr = '*';
				$searchArr = explode ( ',', $list_details->row ()->followers );
				$this->data ['user_details'] = $user_details = $this->product_model->get_fields_from_many ( USERS, $fieldsArr, 'id', $searchArr );
				if ($user_details->num_rows () > 0) {
					foreach ( $user_details->result () as $userRow ) {
						$fieldsArr = array (
								PRODUCT_LIKES . '.*',
								PRODUCT . '.product_name',
								PRODUCT . '.image',
								PRODUCT . '.id as PID' 
						);
						$searchArr = array (
								$userRow->id 
						);
						$joinArr1 = array (
								'table' => PRODUCT,
								'on' => PRODUCT_LIKES . '.product_id=' . PRODUCT . '.seller_product_id',
								'type' => '' 
						);
						$joinArr = array (
								$joinArr1 
						);
						$sortArr1 = array (
								'field' => PRODUCT . '.created',
								'type' => 'desc' 
						);
						$sortArr = array (
								$sortArr1 
						);
						$this->data ['product_details'] [$userRow->id] = $this->product_model->get_fields_from_many ( PRODUCT_LIKES, $fieldsArr, PRODUCT_LIKES . '.user_id', $searchArr, $joinArr, $sortArr, '5' );
					}
				}
				$fieldsArr = array (
						PRODUCT . '.*',
						USERS . '.user_name',
						USERS . '.full_name' 
				);
				$searchArr = array_filter ( explode ( ',', $list_details->row ()->product_id ) );
				if (count ( $searchArr ) > 0) {
					$this->data ['totalProducts'] = count ( $searchArr );
				} else {
					$this->data ['totalProducts'] = 0;
				}
				
				$this->load->view ( 'site/user/user_list_followers', $this->data );
			}
		}
	}
	public function follow_list() {
		$returnStr ['status_code'] = 0;
		$lid = $this->input->post ( 'lid' );
		if ($this->checkLogin ( 'U' ) != '') {
			$listDetails = $this->product_model->get_all_details ( LISTS_DETAILS, array (
					'id' => $lid 
			) );
			$followersArr = explode ( ',', $listDetails->row ()->followers );
			$followersCount = $listDetails->row ()->followers_count;
			$oldDetails = explode ( ',', $this->data ['userDetails']->row ()->following_user_lists );
			if (! in_array ( $lid, $oldDetails )) {
				array_push ( $oldDetails, $lid );
			}
			if (! in_array ( $this->checkLogin ( 'U' ), $followersArr )) {
				array_push ( $followersArr, $this->checkLogin ( 'U' ) );
				$followersCount ++;
			}
			$this->product_model->update_details ( USERS, array (
					'following_user_lists' => implode ( ',', $oldDetails ) 
			), array (
					'id' => $this->checkLogin ( 'U' ) 
			) );
			$this->product_model->update_details ( LISTS_DETAILS, array (
					'followers' => implode ( ',', $followersArr ),
					'followers_count' => $followersCount 
			), array (
					'id' => $lid 
			) );
			$returnStr ['status_code'] = 1;
		}
		echo json_encode ( $returnStr );
	}
	public function unfollow_list() {
		$returnStr ['status_code'] = 0;
		$lid = $this->input->post ( 'lid' );
		if ($this->checkLogin ( 'U' ) != '') {
			$listDetails = $this->product_model->get_all_details ( LISTS_DETAILS, array (
					'id' => $lid 
			) );
			$followersArr = explode ( ',', $listDetails->row ()->followers );
			$followersCount = $listDetails->row ()->followers_count;
			$oldDetails = explode ( ',', $this->data ['userDetails']->row ()->following_user_lists );
			if (in_array ( $lid, $oldDetails )) {
				if ($key = array_search ( $lid, $oldDetails ) !== false) {
					unset ( $oldDetails [$key] );
				}
			}
			if (in_array ( $this->checkLogin ( 'U' ), $followersArr )) {
				if ($key = array_search ( $this->checkLogin ( 'U' ), $followersArr ) !== false) {
					unset ( $followersArr [$key] );
				}
				$followersCount --;
			}
			$this->product_model->update_details ( USERS, array (
					'following_user_lists' => implode ( ',', $oldDetails ) 
			), array (
					'id' => $this->checkLogin ( 'U' ) 
			) );
			$this->product_model->update_details ( LISTS_DETAILS, array (
					'followers' => implode ( ',', $followersArr ),
					'followers_count' => $followersCount 
			), array (
					'id' => $lid 
			) );
			$returnStr ['status_code'] = 1;
		}
		echo json_encode ( $returnStr );
	}
	public function edit_user_lists() {
		if ($this->checkLogin ( 'U' ) == '') {
			redirect ( 'login' );
		} else {
			$lid = $this->uri->segment ( '4', '0' );
			$uname = $this->uri->segment ( '2', '0' );
			if ($uname != $this->data ['userDetails']->row ()->user_name) {
				show_404 ();
			} else {
				$this->data ['user_profile_details'] = $this->user_model->get_all_details ( USERS, array (
						'user_name' => $uname 
				) );
				$this->data ['list_details'] = $list_details = $this->product_model->get_all_details ( LISTS_DETAILS, array (
						'id' => $lid,
						'user_id' => $this->data ['user_profile_details']->row ()->id 
				) );
				if ($this->data ['list_details']->num_rows () == 0) {
					show_404 ();
				} else {
					$this->data ['list_category_details'] = $this->user_model->get_all_details ( CATEGORY, array (
							'id' => $this->data ['list_details']->row ()->category_id 
					) );
					if($this->lang->line('Edit List') != '') 
					{ 
						$message = stripslashes($this->lang->line('Edit List')); 
					} 
					else 
					{
						$message = "Edit List";
					}

					$this->data ['heading'] = $message;
					$this->load->view ( 'site/user/edit_user_list', $this->data );
				}
			}
		}
	}
	public function edit_user_list_details() {
		if ($this->checkLogin ( 'U' ) == '') {
			redirect ( 'login' );
		} else {
			$lid = $this->input->post ( 'lid' );
			$uid = $this->input->post ( 'uid' );
			if ($uid != $this->checkLogin ( 'U' )) {
				show_404 ();
			} else {
				$list_title = $this->input->post ( 'setting-title' );
				$catID = $this->input->post ( 'category' );
				$duplicateCheck = $this->user_model->get_all_details ( LISTS_DETAILS, array (
						'user_id' => $uid,
						'id !=' => $lid,
						'name' => $list_title 
				) );
				if ($duplicateCheck->num_rows () > 0) {
					if($this->lang->line('List title already exists') != '') 
					{ 
						$message = stripslashes($this->lang->line('List title already exists')); 
					} 
					else 
					{
						$message = "List title already exists";
					}

					
						$this->setErrorMessage ('error',$message);
					
					echo '<script>window.history.go(-1);</script>';
				} else {
					if ($catID == '') {
						$catID = 0;
					}
					$this->user_model->update_details ( LISTS_DETAILS, array (
							'name' => $list_title,
							'category_id' => $catID 
					), array (
							'id' => $lid,
							'user_id' => $uid 
					) );
					if($this->lang->line('List updated successfully') != '') 
					{ 
						$message = stripslashes($this->lang->line('List updated successfully')); 
					} 
					else 
					{
						$message = "List updated successfully";
					}
						$this->setErrorMessage ( 'success',$message);
					
					echo '<script>window.history.go(-1);</script>';
				}
			}
		}
	}
	public function delete_user_list() {
		$returnStr ['status_code'] = 0;
		if ($this->checkLogin ( 'U' ) == '') {
			$returnStr ['message'] = 'Login required';
		} else {
			$lid = $this->input->post ( 'lid' );
			$uid = $this->input->post ( 'uid' );
			if ($uid != $this->checkLogin ( 'U' )) {
				$returnStr ['message'] = 'You can\'t delete other\'s list';
			} else {
				$list_details = $this->user_model->get_all_details ( LISTS_DETAILS, array (
						'id' => $lid,
						'user_id' => $uid 
				) );
				if ($list_details->num_rows () == 1) {
					$followers_id = $list_details->row ()->followers;
					if ($followers_id != '') {
						$searchArr = array_filter ( explode ( ',', $followers_id ) );
						$fieldsArr = array (
								'following_user_lists',
								'id' 
						);
						$followersArr = $this->user_model->get_fields_from_many ( USERS, $fieldsArr, 'id', $searchArr );
						if ($followersArr->num_rows () > 0) {
							foreach ( $followersArr->result () as $followersRow ) {
								$listArr = array_filter ( explode ( ',', $followersRow->following_user_lists ) );
								if (in_array ( $lid, $listArr )) {
									if (($key = array_search ( $lid, $listArr )) != false) {
										unset ( $listArr [$key] );
										$this->user_model->update_details ( USERS, array (
												'following_user_lists' => implode ( ',', $listArr ) 
										), array (
												'id' => $followersRow->id 
										) );
									}
								}
							}
						}
					}
					$this->user_model->commonDelete ( LISTS_DETAILS, array (
							'id' => $lid,
							'user_id' => $this->checkLogin ( 'U' ) 
					) );
					$listCount = $this->data ['userDetails']->row ()->lists;
					$listCount --;
					if ($listCount == '' || $listCount < 0) {
						$listCount = 0;
					}
					$this->user_model->update_details ( USERS, array (
							'lists' => $listCount 
					), array (
							'id' => $this->checkLogin ( 'U' ) 
					) );
					$returnStr ['url'] = base_url () . 'user/' . $this->data ['userDetails']->row ()->user_name . '/lists';
					if($this->lang->line('List deleted successfully') != '') 
					{ 
						$message = stripslashes($this->lang->line('List deleted successfully')); 
					} 
					else 
					{
						$message = "List deleted successfully";
					}
						$this->setErrorMessage ('success',$message);
					
					$returnStr ['status_code'] = 1;
				} else {
					if($this->lang->line('List not available') != '') 
					{ 
						$message = stripslashes($this->lang->line('List not available')); 
					} 
					else 
					{
						$message = "List not available";
					}
	
					$returnStr ['message'] = $message;
				}
			}
		}
		echo json_encode ( $returnStr );
	}
	public function update_reservation_requirements() {
		if ($this->checkLogin ( 'U' ) == '')
			redirect ( 'login' );
		else {
			if ($this->input->post ( 'verify_id' ))
				$verify_id = 'yes';
			else
				$verify_id = 'no';
			
			if ($this->input->post ( 'verify_phone' ))
				$verify_phone = 'yes';
			else
				$verify_phone = 'no';
			
			if ($this->input->post ( 'profilePicture' ))
				$profilePicture = 'yes';
			else
				$profilePicture = 'no';
			
			if ($this->input->post ( 'tripDesc' ))
				$tripDesc = 'yes';
			else
				$tripDesc = 'no';
				// echo $verify_id;die;
			
			$this->user_model->commonDelete ( REQUIREMENTS, array (
					'user_id' => $this->input->post ( 'user_id' ) 
			) );
			
			$data = array (
					'user_id' => $this->input->post ( 'user_id' ),
					'id_verified' => $verify_id,
					'ph_verified' => $verify_phone,
					'profile_picture' => $profilePicture,
					'trip_description' => $tripDesc 
			);
			$this->user_model->simple_insert ( REQUIREMENTS, $data );
			// echo $this->db->last_query();die;
			
			/*
			 * $data = array('is_verified'			=>		$verify_id,
			 * 'ph_verified'			=>		$verify_phone,
			 * 'profile_picture'		=>		$profilePicture,
			 * 'trip_description'	=>		$tripDesc
			 * );
			 *
			 *
			 * $this->user_model->commonInsertUpdate(USERS,'update',array(),$data,array('id'=>$this->input->post('user_id')));
			 */
			if($this->lang->line('Reservation requirements updated successfully') != '') 
				{ 
					$message = stripslashes($this->lang->line('Reservation requirements updated successfully')); 
				} 
				else 
				{
					$message = "Reservation requirements updated successfully";
				}

				$this->setErrorMessage ( 'success',$message);
			
			echo "<script>window.history.go(-1);</script>";
			exit ();
		}
	}
	public function image_crop() {
		if ($this->checkLogin ( 'U' ) == '') {
			redirect ( 'login' );
		} else {
			$uid = $this->uri->segment ( 2, 0 );
			if ($uid != $this->checkLogin ( 'U' )) {
				show_404 ();
			} else {
				$this->data ['heading'] = 'Cropping Image';
				$this->load->view ( 'site/user/crop_image', $this->data );
			}
		}
	}
	public function image_crop_process() {
		if ($this->checkLogin ( 'U' ) == '') {
			redirect ( 'login' );
		} else {
			$targ_w = $targ_h = 240;
			$jpeg_quality = 90;
			
			$src = 'images/users/' . $this->data ['userDetails']->row ()->image;
			$ext = substr ( $src, strpos ( $src, '.' ) + 1 );
			if ($ext == 'png') {
				$jpgImg = imagecreatefrompng ( $src );
				imagejpeg ( $jpgImg, $src, 90 );
			}
			$img_r = imagecreatefromjpeg ( $src );
			$dst_r = ImageCreateTrueColor ( $targ_w, $targ_h );
			
			// list($width, $height) = getimagesize($src);
			
			imagecopyresampled ( $dst_r, $img_r, 0, 0, $_POST ['x1'], $_POST ['y1'], $targ_w, $targ_h, $_POST ['w'], $_POST ['h'] );
			// imagecopyresized($dst_r,$img_r,0,0,$_POST['x1'],$_POST['y1'], $targ_w,$targ_h,$_POST['w'],$_POST['h']);
			// imagecopyresized($dst_r, $img_r,0,0, $_POST['x1'],$_POST['y1'], $_POST['x2'],$_POST['y2'],1024,980);
			// header('Content-type: image/jpeg');
			imagejpeg ( $dst_r, 'images/users/' . $this->data ['userDetails']->row ()->image );
			if($this->lang->line('Profile photo changed successfully') != '') 
				{ 
					$message = stripslashes($this->lang->line('Profile photo changed successfully')); 
				} 
				else 
				{
					$message = "Profile photo changed successfully";
				}

				$this->setErrorMessage ( 'success',$message);
			
			redirect ( 'user/' . $this->data ['userDetails']->row ()->user_name );
			exit ();
		}
	}
	public function send_noty_mail($followUserDetails = array()) {
		if (count ( $followUserDetails ) > 0) {
			$emailNoty = explode ( ',', $followUserDetails [0] ['email_notifications'] );
			if (in_array ( 'following', $emailNoty )) {
				$newsid = '7';
				$template_values = $this->product_model->get_newsletter_template_details ( $newsid );
				$adminnewstemplateArr = array (
						'logo' => $this->data ['logo'],
						'meta_title' => $this->config->item ( 'meta_title' ),
						'full_name' => $followUserDetails [0] ['full_name'],
						'cfull_name' => $this->data ['userDetails']->row ()->full_name,
						'user_name' => $this->data ['userDetails']->row ()->user_name 
				);
				extract ( $adminnewstemplateArr );
				$subject = 'From: ' . $this->config->item ( 'email_title' ) . ' - ' . $template_values ['news_subject'];
				$message .= '<!DOCTYPE HTML>
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			<meta name="viewport" content="width=device-width"/>
			<title>' . $template_values ['news_subject'] . '</title><body>';
				include ('./newsletter/registeration' . $newsid . '.php');
				
				$message .= '</body>
			</html>';
				
				if ($template_values ['sender_name'] == '' && $template_values ['sender_email'] == '') {
					$sender_email = $this->data ['siteContactMail'];
					$sender_name = $this->data ['siteTitle'];
				} else {
					$sender_name = $template_values ['sender_name'];
					$sender_email = $template_values ['sender_email'];
				}
				// add inbox from mail
				$this->product_model->simple_insert ( INBOX, array (
						'sender_id' => $sender_email,
						'user_id' => $followUserDetails [0] ['email'],
						'mailsubject' => $subject,
						'description' => stripslashes ( $message ) 
				) );
				
				$email_values = array (
						'mail_type' => 'html',
						'from_mail_id' => $sender_email,
						'mail_name' => $sender_name,
						'to_mail_id' => $followUserDetails [0] ['email'],
						'subject_message' => $subject,
						'body_messages' => $message 
				);
				$email_send_to_common = $this->product_model->common_email_send ( $email_values );
			}
		}
	}
	public function send_noty_mails($followUserDetails = array()) {
		if (count ( $followUserDetails ) > 0) {
			$emailNoty = explode ( ',', $followUserDetails->email_notifications );
			if (in_array ( 'following', $emailNoty )) {
				
				$newsid = '9';
				$template_values = $this->product_model->get_newsletter_template_details ( $newsid );
				$adminnewstemplateArr = array (
						'logo' => $this->data ['logo'],
						'meta_title' => $this->config->item ( 'meta_title' ),
						'full_name' => $followUserDetails [0] ['full_name'],
						'cfull_name' => $this->data ['userDetails']->row ()->full_name,
						'user_name' => $this->data ['userDetails']->row ()->user_name 
				);
				extract ( $adminnewstemplateArr );
				$subject = 'From: ' . $this->config->item ( 'email_title' ) . ' - ' . $template_values ['news_subject'];
				$message .= '<!DOCTYPE HTML>
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			<meta name="viewport" content="width=device-width"/>
			<title>' . $template_values ['news_subject'] . '</title><body>';
				include ('./newsletter/registeration' . $newsid . '.php');
				
				$message .= '</body>
			</html>';
				
				if ($template_values ['sender_name'] == '' && $template_values ['sender_email'] == '') {
					$sender_email = $this->data ['siteContactMail'];
					$sender_name = $this->data ['siteTitle'];
				} else {
					$sender_name = $template_values ['sender_name'];
					$sender_email = $template_values ['sender_email'];
				}
				
				// add inbox from mail
				$this->product_model->simple_insert ( INBOX, array (
						'sender_id' => $sender_email,
						'user_id' => $followUserDetails->email,
						'mailsubject' => $subject,
						'description' => stripslashes ( $message ) 
				) );
				
				$email_values = array (
						'mail_type' => 'html',
						'from_mail_id' => $sender_email,
						'mail_name' => $sender_name,
						'to_mail_id' => $followUserDetails->email,
						'subject_message' => $subject,
						'body_messages' => $message 
				);
				$email_send_to_common = $this->product_model->common_email_send ( $email_values );
			}
		}
	}
	
	public function post_order_comment() {
		if ($this->checkLogin ( 'U' ) != '') {
			$this->user_model->commonInsertUpdate ( REVIEW_COMMENTS, 'insert', array (), array (), '' );
		}
	}
	public function change_received_status() {
		if ($this->checkLogin ( 'U' ) != '') {
			$status = $this->input->post ( 'status' );
			$rid = $this->input->post ( 'rid' );
			$this->user_model->update_details ( PAYMENT, array (
					'received_status' => $status 
			), array (
					'id' => $rid 
			) );
		}
	}
	public function EditSiteUserLoginDetails() {
		$excludeArr = array (
				'signin',
				'first_name',
				'last_name' 
		);
		$condition = array (
				'id' => $this->checkLogin ( 'U' ) 
		);
		$dataArr = array (
				'firstname' => $this->input->post ( 'first_name' ),
				'lastname' => $this->input->post ( 'last_name' ) 
		);
		
		/*
		 * $logoDirectory ='./images/users';
		 * if(!is_dir($logoDirectory))
		 * {
		 * mkdir($logoDirectory,0777);
		 * }
		 * $config['allowed_types'] = 'jpg|jpeg|gif|png';
		 *
		 * $config['remove_spaces'] = FALSE;
		 * $config['upload_path'] = $logoDirectory;
		 * $this->upload->initialize($config);
		 * $this->load->library('upload', $config);
		 *
		 * if ($this->upload->do_upload('user_image')){
		 * $logoDetails = $this->upload->data();
		 * $logoDetails['file_name'];
		 * $dataArr['thumbnail'] = $logoDetails['file_name'];
		 * }
		 */
		
		$filePRoductUploadData = array ();
		$setPriority = 0;
		// $imgtitle = $this->input->post('usre_image');
		
		$this->user_model->commonInsertUpdate ( USERS, 'update', $excludeArr, $dataArr, $condition );
		if($this->lang->line('User details updated successfully') != '') 
				{ 
					$message = stripslashes($this->lang->line('User details updated successfully')); 
				} 
				else 
				{
					$message = "User details updated successfully";
				}

		
			$this->setErrorMessage ('success', $message);
		
		redirect ( base_url () . 'users/edit/' . $this->checkLogin ( 'U' ) );
	}
	function getDatesFromRange($start, $end) {
		$dates = array (
				$start 
		);
		while ( end ( $dates ) < $end ) {
			$dates [] = date ( 'Y-m-d', strtotime ( end ( $dates ) . ' +1 day' ) );
		}
		
		return $dates;
	}
	
	/**
	 * ****************Invite Friends*******************
	 */
	public function invite_friends() {
		if ($this->checkLogin ( 'U' ) == '') {
			redirect ( 'login' );
		} else {
			$this->data ['heading'] = 'Invite Friends';
			$this->load->view ( 'site/user/invite_friends', $this->data );
		}
	}
	public function app_twitter() {
		$returnStr ['status_code'] = 1;
		$returnStr ['url'] = base_url () . 'twtest/get_twitter_user';
		$returnStr ['message'] = '';
		echo json_encode ( $returnStr );
	}
	public function find_friends_twitter() {
		$returnStr ['status_code'] = 1;
		$returnStr ['url'] = base_url () . 'twtest/invite_friends';
		$returnStr ['message'] = '';
		echo json_encode ( $returnStr );
	}
	public function find_friends_gmail_19() {
		$returnStr ['status_code'] = 1;
		error_reporting ( 0 );
		include_once './invite_friends/GmailOath.php';
		include_once './invite_friends/Config.php';
		$oauth = new GmailOath ( $consumer_key, $consumer_secret, $argarray, $debug, $callback );
		$getcontact = new GmailGetContacts ();
		$access_token = $getcontact->get_request_token ( $oauth, false, true, true );
		$this->session->set_userdata ( 'oauth_token', $access_token ['oauth_token'] );
		$this->session->set_userdata ( 'oauth_token_secret', $access_token ['oauth_token_secret'] );
		$returnStr ['url'] = "https://www.google.com/accounts/OAuthAuthorizeToken?oauth_token=" . $oauth->rfc3986_decode ( $access_token ['oauth_token'] );
		$returnStr ['message'] = '';
		echo json_encode ( $returnStr );
	}
	public function find_friends_gmail_callback() {
		include_once './invite_friends/GmailOath.php';
		include_once './invite_friends/Config.php';
		error_reporting ( 0 );
		$oauth = new GmailOath ( $consumer_key, $consumer_secret, $argarray, $debug, $callback );
		$getcontact_access = new GmailGetContacts ();
		
		$request_token = $oauth->rfc3986_decode ( $this->input->get ( 'oauth_token' ) );
		$request_token_secret = $oauth->rfc3986_decode ( $this->session->userdata ( 'oauth_token_secret' ) );
		$oauth_verifier = $oauth->rfc3986_decode ( $this->input->get ( 'oauth_verifier' ) );
		
		$contact_access = $getcontact_access->get_access_token ( $oauth, $request_token, $request_token_secret, $oauth_verifier, false, true, true );
		$access_token = $oauth->rfc3986_decode ( $contact_access ['oauth_token'] );
		$access_token_secret = $oauth->rfc3986_decode ( $contact_access ['oauth_token_secret'] );
		$contacts = $getcontact_access->GetContacts ( $oauth, $access_token, $access_token_secret, false, true, $emails_count );
		
		$count = 0;
		foreach ( $contacts as $k => $a ) {
			$final = end ( $contacts [$k] );
			foreach ( $final as $email ) {
				$this->send_invite_mail ( $email ["address"] );
				$count ++;
			}
		}
		if ($count > 0) {
			echo "
			<script>
				alert('Invitations sent successfully');
				window.close();
			</script>
			";
		} else {
			echo "
			<script>
				window.close();
			</script>
			";
		}
	}
	public function send_invite_mail($to = '') {
		if ($to != '') {
			$newsid = '16';
			$template_values = $this->product_model->get_newsletter_template_details ( $newsid );
			$adminnewstemplateArr = array (
					'logo' => $this->data ['logo'],
					'siteTitle' => $this->data ['siteTitle'],
					'meta_title' => $this->config->item ( 'meta_title' ),
					'full_name' => $this->data ['userDetails']->row ()->full_name,
					'user_name' => $this->data ['userDetails']->row ()->user_name 
			);
			extract ( $adminnewstemplateArr );
			$subject = $template_values ['news_subject'];
			$message .= '<!DOCTYPE HTML>
					<html>
					<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
					<meta name="viewport" content="width=device-width"/>
					<title>' . $template_values ['news_subject'] . '</title><body>';
			include ('./newsletter/registeration' . $newsid . '.php');
			
			$message .= '</body>
					</html>';
			
			if ($template_values ['sender_name'] == '' && $template_values ['sender_email'] == '') {
				$sender_email = $this->data ['siteContactMail'];
				$sender_name = $this->data ['siteTitle'];
			} else {
				$sender_name = $template_values ['sender_name'];
				$sender_email = $template_values ['sender_email'];
			}
			
			$email_values = array (
					'mail_type' => 'html',
					'from_mail_id' => $sender_email,
					'mail_name' => $sender_name,
					'to_mail_id' => $to,
					'subject_message' => $subject,
					'body_messages' => $message 
			);
			$email_send_to_common = $this->product_model->common_email_send ( $email_values );
		}
	}
	public function verification() {
	
		if ($this->checkLogin ( 'U' ) == '') {
			redirect ( base_url () . 'login' );
		}
		if ($this->uri->segment ( 2 ) == 'send-mail') {
			$this->send_confirm_mail ( $this->data ['userDetails'] );
			if($this->lang->line('Verification request sent. Renters team will in contact with you shortly.') != '') 
				{ 
					$message = stripslashes($this->lang->line('Verification request sent. Renters team will in contact with you shortly.')); 
				} 
				else 
				{
					$message = "Verification request sent. Renters team will in contact with you shortly.";
				}

				$this->setErrorMessage ( 'success',$message);
			
			redirect ( 'verification' );
		}
		if ($this->uri->segment ( 2 ) == 'verify-mail') {
			$userid =  $this->session->userdata('fc_session_user_id');
		    $id_verified ="Yes";
		         //$this->db->select('*');
				 //$this->db->from(fc_users);
				 //$this->db->where('id',$userid);
				 //$this->db->where('id_verified',$id_verified);
				 
				 $this->db->where('id',$userid);
				 $this->db->set('id_verified',$id_verified);							
				 $this->db->update('fc_users');
			$this->send_verify_mail ( $this->data ['userDetails'] );
			if($this->lang->line('Verification request sent. Renters team will in contact with you shortly.') != '') 
				{ 
					$message = stripslashes($this->lang->line('Verification request sent. Renters team will in contact with you shortly.')); 
				} 
				else 
				{
					$message = "Verification request sent. Renters team will in contact with you shortly.";
				}

				$this->setErrorMessage ( 'success',$message);
			
			redirect ( 'verification' );
		}
		
		//echo $authUrl; die;
		$userid =  $this->session->userdata('fc_session_user_id');
                 //$this->db->group_by('bookingNo');
		
		$condition = array('id'=>$userid);
		$this->data['user_Details'] = $this->user_model->get_all_details(USERS,$condition);
		
		

		$country_query = 'SELECT id,name FROM ' . LOCATIONS . ' WHERE status="Active" order by name';
		$this->data ['active_countries'] = $this->user_model->ExecuteQuery ( $country_query );
		
		
		
		
		
		
		$this->data ['heading'] = 'Trust and Verification';
		$this->data ['UserDetail'] = $this->data ['userDetails'];

		/* malar 12/07/2017  proof verification  starts */
		// Get existing proof of user
		$existCheck = "SELECT * FROM ".ID_PROOF." WHERE user_id='".$this->checkLogin('U')."'" ;
		$this->data['proofDetails'] = $this->user_model->ExecuteQuery($existCheck);
		/* malar 12/07/2017  proof verification  ends */


		//sprint_r($this->data ['UserDetail']->row());die;
		$this->load->view ( 'site/user/email_verification', $this->data );
	}
	
	
	public function requestToAdmin(){
		
		$user_id = $this->checkLogin('U');
		$status=$this->input->post('status');
		$proof_type=$this->input->post('proof_type');
		
		$UpdateStatus = "UPDATE ".ID_PROOF." SET id_proof_status= '".$status."'  WHERE user_id='".$user_id."' AND proof_type= " .$proof_type;
        $UpdateStatus_res = $this->user_model->ExecuteQuery($UpdateStatus);
		
		$OnReqStatus = $this->user_model->get_all_details(ID_PROOF,array('user_id'=>$user_id,'proof_type'=>$proof_type,'id_proof_status'=>'OnRequest'));
		
		if($OnReqStatus->num_rows()=='1'){
			echo "Onreq";
		}else{
			echo "none";
		}

		
		 /* Mail function */
		 
		/* Mail To Admin*/			

            $newsid='53';
			$template_values=$this->product_model->get_newsletter_template_details($newsid);
			if($template_values['sender_name']=='' && $template_values['sender_email']==''){
				$sender_email=$this->data['siteContactMail'];
				$sender_name=$this->data['siteTitle'];
			}else{
				$sender_name=$template_values['sender_name'];
				$sender_email=$template_values['sender_email'];
			} 
						$condition = array (
				'id' => $user_id	
				);
		$usrDetails = $this->user_model->get_all_details( USERS, $condition );
		$uid = $usrDetails->row ()->id;
		$username = $usrDetails->row ()->user_name;
		$email = $usrDetails->row ()->email;
		$randStr = $this->get_rand_str ( '10' );

		//$cfmurl = base_url () . 'site/user/confirm_verify/' . $uid . "/" . $randStr . "/confirmation";
		//$logo_mail = $this->data['logo'];
                                 
                      $email_values = array(
						'from_mail_id'=>$sender_email,
						//'from_mail_id'=>'kailashkumar.r@pofitec.com',
					
					'to_mail_id'=> $sender_email,
					//'to_mail_id'=>'preetha@pofitec.com',
					'subject_message'=>$template_values ['news_subject'],
					'body_messages'=>$message
			);  
			$reg= array('hostname' => $username);
           //print_r($this->data['logo']);
            $message = $this->load->view('newsletter/Host ID Proof Request'.$newsid.'.php',$reg,TRUE);

            
            //send mail
            $this->load->library('email',$config);
            $this->email->from($email_values['from_mail_id'], $sender_name);
            $this->email->to($email_values['to_mail_id']);
            $this->email->subject($email_values['subject_message']);
            $this->email->set_mailtype("html");
			$this->email->set_newline("\r\n");
            $this->email->message($message); 
                        
                       if ( $this->email->send()){
						   echo "success";
						   }else{
							  echo $this->email->print_debugger();

						   }
                        // try{
                        // c;
                        // $returnStr ['msg'] = 'Successfully registered';
						// $returnStr ['success'] = '1';
                        // }catch(Exception $e){
                        // echo $e->getMessage();
                        // }                   
                        
                        /* Mail function End */  
		
		/* Mail To Admin*/
		
	}
	
	
	
	


	/* User Id Proof verfication starts  */
	public function upload_id_proof()
	{
		if ($this->checkLogin ( 'U' ) == '') {
			redirect ( base_url () . 'login' );
		}
		else{
			$proof_option = $this->input->post('option');
			//$proof_1 = $proof_2 = $proof_3 = '';
			//$proof_1_status = $proof_2_status = $proof_3_status = 'ND';
			$proof_status = 'P';
			//echo $_FILES['proof_file']['name'] ." ".count($_FILES['proof_file']['name']);
			if(count($_FILES['proof_file']['name'])>0) {

				$renameArr = explode('.', $_FILES['proof_file']['name']);

				$imgTitle = strtolower($renameArr[0]);

				$imgTitle = mysql_real_escape_string($imgTitle);
				$imgTitle = trim($imgTitle);

				$imgTitle = str_replace("'","",$imgTitle);
				$imgTitle = str_replace("&","",$imgTitle);
				$imgTitle = str_replace("'","",$imgTitle);
				$imgTitle = @ereg_replace("[^A-Za-z0-9]", " ", $imgTitle);
				$imgTitle = preg_replace( "/\s+/", " ", $imgTitle);
				$imgTitle = str_replace(" ","-", $imgTitle);

				
				$_FILES['proof_file']['name'] = date('Ymdhis')."_".$imgTitle.'.'.$renameArr[1];

				//$uploadPath = "server/php/id_proof/";	//a directory inside
				$config['upload_path'] = ID_PROOF_PATH;
				$config['allowed_types'] = 'gif|jpg|png|bmp|jpeg|pdf|doc|docx';
				$config['max_size'] = "2048000";

				$this->load->library('upload', $config);
				$this->upload->initialize($config);

				if($this->upload->do_upload('proof_file')){

			        $fileData = $this->upload->data();
			        $proof = $fileData['file_name'];
			        /*
			        if($proof_option==1){
			        	$proof_1 = $fileData['file_name'];
			        	$proof_1_status = 'P';
			        }elseif($proof_option==2){
			        	$proof_2 = $fileData['file_name'];
			        	$proof_2_status = 'P';
			        }
			        elseif($proof_option==3){
			        	$proof_3 = $fileData['file_name'];
			        	$proof_3_status = 'P';
			        }
					*/
					
					
						 $existCheck = "SELECT * FROM ".ID_PROOF." WHERE user_id='".$this->checkLogin('U')."'";
					 $proofExist_res = $this->user_model->ExecuteQuery($existCheck);
					  if($proofExist_res->num_rows()>0){
						  
						  unlink(ID_PROOF_PATH . $proofExist_res->row()->proof_file);
						  
					$Del_user = "DELETE FROM ".ID_PROOF." WHERE user_id='".$this->checkLogin('U')."'";
					$proofExist_res = $this->user_model->ExecuteQuery($Del_user);
					
								$insertData  = array('user_id' => $this->checkLogin('U'),
								 'proof_file' => $proof,
								 'proof_type' => $proof_option,
								 'proof_status' => $proof_status,
								 //'id_proof_status'=>'UnVerified'
								 'id_proof_status'=>'OnRequest'
 
								);
			        	$this->user_model->simple_insert(ID_PROOF,$insertData);
						
						
		 /* Mail function */
		/* Mail To Admin*/			

            $newsid='53';
			$template_values=$this->product_model->get_newsletter_template_details($newsid);
			if($template_values['sender_name']=='' && $template_values['sender_email']==''){
				$sender_email=$this->data['siteContactMail'];
				$sender_name=$this->data['siteTitle'];
			}else{
				$sender_name=$template_values['sender_name'];
				$sender_email=$template_values['sender_email'];
			} 
						$condition = array (
				'id' => $user_id	
				);
		$usrDetails = $this->user_model->get_all_details( USERS, $condition );
		$uid = $usrDetails->row ()->id;
		$username = $usrDetails->row ()->user_name;
		$email = $usrDetails->row ()->email;
		$randStr = $this->get_rand_str ( '10' );

		//$cfmurl = base_url () . 'site/user/confirm_verify/' . $uid . "/" . $randStr . "/confirmation";
		//$logo_mail = $this->data['logo'];
                                 
                      $email_values = array(
						'from_mail_id'=>$sender_email,
						//'from_mail_id'=>'kailashkumar.r@pofitec.com',
					
					'to_mail_id'=> $sender_email,
					//'to_mail_id'=>'preetha@pofitec.com',
					'subject_message'=>$template_values ['news_subject'],
					'body_messages'=>$message
			);  
			$reg= array('hostname' => $username);
           //print_r($this->data['logo']);
            $message = $this->load->view('newsletter/Host ID Proof Request'.$newsid.'.php',$reg,TRUE);
 
            //send mail
            $this->load->library('email',$config);
            $this->email->from($email_values['from_mail_id'], $sender_name);
            $this->email->to($email_values['to_mail_id']);
            $this->email->subject($email_values['subject_message']);
            $this->email->set_mailtype("html");
			$this->email->set_newline("\r\n");
            $this->email->message($message); 
                        
							if ( $this->email->send()){
								echo "success";
						   }else{
								echo $this->email->print_debugger();

						   }
                        // try{
                        // c;
                        // $returnStr ['msg'] = 'Successfully registered';
						// $returnStr ['success'] = '1';
                        // }catch(Exception $e){
                        // echo $e->getMessage();
                        // }                   
                        
                        /* Mail function End */  
						/* Mail To Admin*/
						
						
						
						  
					  }else{
						  		$insertData  = array('user_id' => $this->checkLogin('U'),
								 'proof_file' => $proof,
								 'proof_type' => $proof_option,
								 'proof_status' => $proof_status,
								 //'id_proof_status'=>'UnVerified'
								 'id_proof_status'=>'OnRequest' 
								);
			        	$this->user_model->simple_insert(ID_PROOF,$insertData);
						
						 /* Mail function */
		/* Mail To Admin*/			

            $newsid='53';
			$template_values=$this->product_model->get_newsletter_template_details($newsid);
			if($template_values['sender_name']=='' && $template_values['sender_email']==''){
				$sender_email=$this->data['siteContactMail'];
				$sender_name=$this->data['siteTitle'];
			}else{
				$sender_name=$template_values['sender_name'];
				$sender_email=$template_values['sender_email'];
			} 
						$condition = array (
				'id' => $user_id	
				);
		$usrDetails = $this->user_model->get_all_details( USERS, $condition );
		$uid = $usrDetails->row ()->id;
		$username = $usrDetails->row ()->user_name;
		$email = $usrDetails->row ()->email;
		$randStr = $this->get_rand_str ( '10' );

		//$cfmurl = base_url () . 'site/user/confirm_verify/' . $uid . "/" . $randStr . "/confirmation";
		//$logo_mail = $this->data['logo'];
                                 
                      $email_values = array(
						'from_mail_id'=>$sender_email,
						//'from_mail_id'=>'kailashkumar.r@pofitec.com',
					
					'to_mail_id'=> $sender_email,
					//'to_mail_id'=>'preetha@pofitec.com',
					'subject_message'=>$template_values ['news_subject'],
					'body_messages'=>$message
			);  
			$reg= array('hostname' => $username);
           //print_r($this->data['logo']);
            $message = $this->load->view('newsletter/Host ID Proof Request'.$newsid.'.php',$reg,TRUE);
 
            //send mail
            $this->load->library('email',$config);
            $this->email->from($email_values['from_mail_id'], $sender_name);
            $this->email->to($email_values['to_mail_id']);
            $this->email->subject($email_values['subject_message']);
            $this->email->set_mailtype("html");
			$this->email->set_newline("\r\n");
            $this->email->message($message); 
                        
							if ( $this->email->send()){
								echo "success";
						   }else{
								echo $this->email->print_debugger();

						   }
                        // try{
                        // c;
                        // $returnStr ['msg'] = 'Successfully registered';
						// $returnStr ['success'] = '1';
                        // }catch(Exception $e){
                        // echo $e->getMessage();
                        // }                   
                        
                        /* Mail function End */  
						/* Mail To Admin*/
						
						
  
					  }
					
					
					
					
					
					
					
			        //check user already submitted proof or not
			     /******  $existCheck = "SELECT * FROM ".ID_PROOF." WHERE user_id='".$this->checkLogin('U')."'  AND proof_type='".$proof_option."'";

			        $proofExist_res = $this->user_model->ExecuteQuery($existCheck);
			        if($proofExist_res->num_rows()>0)
			        {
			        	$proof_id = $proofExist_res->row()->id;

			        	
						unlink(ID_PROOF_PATH . $proofExist_res->row()->proof_file);*****/
						

								/*
								//submitted proof is already exist
								if(($proofExist_res->row()->proof_1_status !='ND'|| $proofExist_res->row()->proof_1_status !='CL') and $proof_1!='' )
								{

								} */

								//update 
								/*
								if($proof_option==1){
									
									$update_query = "update ".ID_PROOF. " SET proof_1='".$proof_1."',proof_1_status='".$proof_1_status."' where id='".$proof_id."'";

								}elseif($proof_option==2){
									$update_query = "update ".ID_PROOF. " SET proof_2='".$proof_2."',proof_2_status='".$proof_2_status."' where id='".$proof_id."'";
								}
								elseif($proof_option==3){
									$update_query = "update ".ID_PROOF. " SET proof_3='".$proof_1."',proof_3_status='".$proof_3_status."' where id='".$proof_id."'";
								}
								*/
						
						
				     /*****   $update_query = "UPDATE ".ID_PROOF. " SET proof_file='".$proof."',proof_status='".$proof_status."',proof_comments='' WHERE user_id = '".$this->checkLogin('U')."' AND proof_type='".$proof_option."' ";
				        $this->user_model->ExecuteQuery($update_query);
			        	
			        }else{

			        	
			        	$insertData  = array('user_id' => $this->checkLogin('U'),
			        						 'proof_file' => $proof,
			        						 'proof_type' => $proof_option,
			        						 'proof_status' => $proof_status
			        						);
			        	$this->user_model->simple_insert(ID_PROOF,$insertData);

			        }******/
					
					
					
					if($this->lang->line('succ_message_proof') != '')
							{ 
								$succ_message = stripslashes($this->lang->line('succ_message_proof')); 
							} 
							else
							{
								$succ_message = "Id proof successfully submitted for verification.";
							}
					
					
					
					
			        $this->setErrorMessage ('success', $succ_message);
			    }else{
			    	$this->setErrorMessage ( 'success', $this->upload->display_errors() );
			    }    
				
			}else{
				
				if($this->lang->line('proof_not_found!') != '')
							{ 
								$proof_not_found = stripslashes($this->lang->line('proof_not_found!')); 
							} 
							else
							{
								$proof_not_found = "Proof not found!";
							}
				
				
				
	  			$this->setErrorMessage('success',$proof_not_found );
	  		}
			
			redirect ( 'verification' );
		}
	}
	/* User Id Proof verfication ends  */


	/* Check user proof existance  starts */

 	public function checkUserProof(){
 		$proof_type = $this->input->post("proof_type");
 		$user_id = $this->checkLogin('U');
 		 $existCheck = "SELECT * FROM ".ID_PROOF." WHERE user_id='".$user_id."'  AND proof_type='".$proof_type."'";

        $proofExist_res = $this->user_model->ExecuteQuery($existCheck);
        if($proofExist_res->num_rows()>0)
        {
        	echo "exist";
        }else 
        	echo "No";
 	} 
 	/* Check user proof existance  starts */

	 public function linkedin() {
		try {

			$API_CONFIG = array(
			'appKey'       => '75pu28tjuxnaxx',
			'appSecret'    => '4G0zX3XShVML9Fz5',
			'callbackUrl'  => NULL 
			);
			define('DEMO_GROUP', '4010474');
			define('DEMO_GROUP_NAME', 'Simple LI Demo');
			define('PORT_HTTP', '80');
			define('PORT_HTTP_SSL', '443');

			$_GET['lType'] = (isset($_GET['lType'])) ? $_GET['lType'] : '';
			switch($_GET['lType']) {
			case 'initiate':

			if($_SERVER['HTTPS'] == 'on') {
			$protocol = 'https';
			} else {
			$protocol = 'http';
			}


			$API_CONFIG['callbackUrl'] = $protocol . '://' . $_SERVER['SERVER_NAME'] . ((($_SERVER['SERVER_PORT'] != PORT_HTTP) || ($_SERVER['SERVER_PORT'] != PORT_HTTP_SSL)) ? ':' . $_SERVER['SERVER_PORT'] : '') . $_SERVER['PHP_SELF'] . '?' . LINKEDIN::_GET_TYPE . '=initiate&' . LINKEDIN::_GET_RESPONSE . '=1';
			$OBJ_linkedin = new LinkedIn($API_CONFIG);


			$_GET[LINKEDIN::_GET_RESPONSE] = (isset($_GET[LINKEDIN::_GET_RESPONSE])) ? $_GET[LINKEDIN::_GET_RESPONSE] : '';
			if(!$_GET[LINKEDIN::_GET_RESPONSE]) {

			$response = $OBJ_linkedin->retrieveTokenRequest();
			if($response['success'] === TRUE) {
			$_SESSION['oauth']['linkedin']['request'] = $response['linkedin'];


			header('Location: ' . LINKEDIN::_URL_AUTH . $response['linkedin']['oauth_token']);
			} else {
			echo "Request token retrieval failed:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre><br /><br />LINKEDIN OBJ:<br /><br /><pre>" . print_r($OBJ_linkedin, TRUE) . "</pre>";
			}
			} else {

			$response = $OBJ_linkedin->retrieveTokenAccess($_SESSION['oauth']['linkedin']['request']['oauth_token'], $_SESSION['oauth']['linkedin']['request']['oauth_token_secret'], $_GET['oauth_verifier']);
			if($response['success'] === TRUE) {

			$_SESSION['oauth']['linkedin']['access'] = $response['linkedin'];
			$_SESSION['oauth']['linkedin']['authorized'] = TRUE;
			header('Location: ' . $_SERVER['PHP_SELF']);

			} else {

			echo "Access token retrieval failed:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre><br /><br />LINKEDIN OBJ:<br /><br /><pre>" . print_r($OBJ_linkedin, TRUE) . "</pre>";
			}
			}
			break;

			case 'revoke':
			if(!oauth_session_exists()) {
			throw new LinkedInException('This script requires session support, which doesn\'t appear to be working correctly.');
			}

			$OBJ_linkedin = new LinkedIn($API_CONFIG);
			$OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
			$response = $OBJ_linkedin->revoke();
			if($response['success'] === TRUE) {
			session_unset();
			$_SESSION = array();
			if(session_destroy()) {
			header('Location: ' . $_SERVER['PHP_SELF']);
			} else {
			echo "Error clearing user's session";
			}
			} else {
			echo "Error revoking user's token:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre><br /><br />LINKEDIN OBJ:<br /><br /><pre>" . print_r($OBJ_linkedin, TRUE) . "</pre>";
			}
			break;
			default:

			$_SESSION['oauth']['linkedin']['authorized'] = (isset($_SESSION['oauth']['linkedin']['authorized'])) ? $_SESSION['oauth']['linkedin']['authorized'] : FALSE;
			if($_SESSION['oauth']['linkedin']['authorized'] === TRUE) {
			$OBJ_linkedin = new LinkedIn($API_CONFIG);
			$OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
			$OBJ_linkedin->setResponseFormat(LINKEDIN::_RESPONSE_XML);



			$response = $OBJ_linkedin->group(DEMO_GROUP, ':(relation-to-viewer:(membership-state))');
			if($response['success'] === TRUE) {
			$result         = new SimpleXMLElement($response['linkedin']);
			$membership     = $result->{'relation-to-viewer'}->{'membership-state'}->code;
			$in_demo_group  = (($membership == 'non-member') || ($membership == 'blocked')) ? FALSE : TRUE;



			} else {
			echo "Error retrieving group membership information: <br /><br />RESPONSE:<br /><br /><pre>" . print_r ($response, TRUE) . "</pre>";
			}

			} else {

			}

			if($_SESSION['oauth']['linkedin']['authorized'] === TRUE) {


			$response = $OBJ_linkedin->profile('~:(id,first-name,last-name,picture-url)');
			if($response['success'] === TRUE) {
			$response['linkedin'] = new SimpleXMLElement($response['linkedin']);
			echo "<pre>" . print_r($response['linkedin'], TRUE) . "</pre>";
			} else {

			echo "Error retrieving profile information:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response) . "</pre>";
			} 
			} else {

			}

			break;
			}
			} catch(LinkedInException $e) {

			echo $e->getMessage();
			}
			if($this->lang->line('Registered & Login Successfully') != '') 
				{ 
					$message = stripslashes($this->lang->line('Registered & Login Successfully')); 
				} 
				else 
				{
					$message = "Registered & Login Successfully";
				}

			
				$this->setErrorMessage('success',$message);
			
	 }
	
	public function change_profile_photo() {
		$config ['overwrite'] = FALSE;
		//$config ['remove_spaces'] = TRUE;
		$config ['allowed_types'] = 'jpg|jpeg|gif|png';
		$config ['max_size'] = 2000000;
		$config ['max_width'] = '272';
		$config ['max_height'] = '272';
		$config ['upload_path'] = './images/users';

		$this->load->library ( 'upload', $config );	

		if ($_POST) {
			
			
			if ($this->upload->do_upload ( 'upload-file' )) {

				$imgDetailsd = $this->upload->data ();
			
				 if($imgDetailsd['image_width'] =='272' && $imgDetailsd['image_height'] =='272'){
				 /* Compress */ 
		    	$source_photo = './images/users/'.$imgDetailsd['file_name'].'';
				$dest_photo = './images/users/'.$imgDetailsd['file_name'];
				$this->compress($source_photo, $dest_photo, $this->config->item('image_compress_percentage'));
				/* End Compress */ 

				$imgDetails = array (
						'image' => $imgDetailsd ['file_name'],
						'loginUserType' => 'normal'
				);

				$condition = array (
					'id' => $this->checkLogin ( 'U' ) 
				);
				$dataArrMrg = $imgDetails;
				//echo $imgDetailsd ['file_ext']; die;
				 if($imgDetailsd ['file_ext'] == '.jpg' || $imgDetailsd ['file_ext'] == '.jpeg' || $imgDetailsd ['file_ext'] == '.png') {
				  
				$this->user_model->update_details (USERS, $dataArrMrg, $condition );
				if($this->lang->line('Your Profile Picture Is Updated Successfully.') != '') 
				{ 
					$message = stripslashes($this->lang->line('Your Profile Picture Is Updated Successfully.')); 
				} 
				else 
				{
					$message = "Your Profile Picture Is Updated Successfully.";
				}

					$this->setErrorMessage ( 'success',$message);
				
				redirect ( 'photo-video' );
				  
				 }else{
				echo "<script>alert('Choose correct format');</script>";
				}
			   }
			   else{
			   		if($this->lang->line('err_user_profile_picture') != '') 
					{ 
					$err_message = stripslashes($this->lang->line('err_user_profile_picture')); 
					} 
					else 
					{
						$err_message = "Image Should be JPEG,JPG,PNG and below 272*272px";
					}
					
					
					

						$this->setErrorMessage('success',$err_message);
					
					redirect ('photo-video');
			   }
			} else {
				//$imgDetails = array ();
				if($this->lang->line('Please enter a valid photo.') != '') 
				{ 
					$message = stripslashes($this->lang->line('Please enter a valid photo.')); 
				} 
				else 
				{
					$message = "Please enter a valid photo.";
				}
				
				
				
				if($this->lang->line('err_user_profile_picture') != '') 
				{ 
				$err_message = stripslashes($this->lang->line('err_user_profile_picture')); 
				} 
				else 
				{
					$err_message = "Image Should be JPEG,JPG,PNG and below 272*272px";
				}
				
				
				

					$this->setErrorMessage('success',$err_message);
				
				redirect ('photo-video');
			}
			
			
			
			// echo $this->db->last_query();die;
			
			
		}
		//$this->data ['heading'] = 'Photo';
		$this->data ['userDetails'] = $this->data ['userDetails'];
		//print_r($this->data ['userDetails']->row());die;
		$this->load->view ( 'site/user/photo_video', $this->data );
	}
	
	/* Inbox message code added by muhammed 28-11-2014 */
	public function inbox1() {
		
		// echo '<pre>'; print_r($_POST); die;
		$guide_id = $this->input->post ( 'guide_id' );
		$uid = $this->input->post ( 'user_id' );
		
		// if($uid==$this->checkLogin('U')) {
		// $this->setErrorMessage('error','Message cannot send yourself');
		// redirect('site/users/show/'.$uid);
		// }
		
		$excludeArr = array (
				'submit',
				'hid',
				'expid',
				'uid' 
		);
		$condition = array ();
		$dataArr = array ();
		$this->user_model->commonInsertUpdate ( INBOXNEW, 'insert', $excludeArr, $dataArr, $condition );
		if($this->lang->line('Thank you for contact') != '') 
				{ 
					$message = stripslashes($this->lang->line('Thank you for contact')); 
				} 
				else 
				{
					$message = "Thank you for contact";
				}

			$this->setErrorMessage ('error',$message);
		redirect ( base_url () );
	}
	public function account_update() {
		$excludeArr = array (
				'submit',
				'hid',
				'expid',
				'uid' 
		);
		$condition = array (
				'id' => $this->input->post ( 'hid' ) 
		);
if($this->input->post ( 'accname' )!="" || $this->input->post ( 'accno' )!="" || $this->input->post ( 'bankname' )!="") {
		$dataArr = array (
				'accname' => $this->input->post ( 'accname' ),
				'accno' => $this->input->post ( 'accno' ),
				
'bankname' => $this->input->post ( 'bankname' ),
				// Update for client side new fields 
				'swift_code' => $this->input->post ( 'swift_code' ),
				'ibnb_code' => $this->input->post ( 'ibnb_code' ),
				'routing_code' => $this->input->post ( 'routing_code' ),
				'currency_code_bank' => $this->input->post ( 'currency_code_bank' ),
				'country_code_bank' => $this->input->post ( 'country_code_bank' )
				// Update for client side new fields 
		);
		$this->user_model->commonInsertUpdate ( USERS, 'update', $excludeArr, $dataArr, $condition );
		
		} 
		if($this->input->post ( 'paypal_email' )!="") {
		$dataArr = array (
				'paypal_email' => $this->input->post ( 'paypal_email' )
				
				// Update for client side new fields 
				
		);
		
		//$this->user_model->commonInsertUpdate ( USERS, 'update', $excludeArr, $dataArr, $condition );
		$this->user_model->commonInsertUpdate (USERS, 'update', $excludeArr, $dataArr, $condition );
		}
		$this->account_changes($this->input->post ( 'hid' ));
		redirect ( base_url () . 'account-payout' );
	}
	
	
	
	/* deactivate account */
	
	public function deactive_user() {
		$datestring = "%Y-%m-%d %h:%i:%s";
		$time = time ();
		$newdata = array (
				'last_logout_date' => mdate ( $datestring, $time ) 
		);
		$condition = array (
				'id' => $this->checkLogin ( 'U' ) 
		);
		$this->user_model->update_details ( USERS, $newdata, $condition );
		$userdata = array (
				'fc_session_user_id' => '',
				'session_user_name' => '',
				'session_user_email' => '',
				'fc_session_temp_id' => '' 
		);
		$this->session->unset_userdata ( $userdata );
		
		@session_start ();
		unset ( $_SESSION ['token'] );
		$twitter_return_values = array (
				'tw_status' => '',
				'tw_access_token' => '' 
		);
		
		$this->session->unset_userdata ( $twitter_return_values );
		if($this->lang->line('Your account deactivate successfully') != '') 
				{ 
					$message = stripslashes($this->lang->line('Your account deactivate successfully')); 
				} 
				else 
				{
					$message = "Your account deactivate successfully";
				}
			$this->setErrorMessage ( 'success',$message);
		redirect ( base_url () );
	}
	
	public function get_mobile_code()
{
 $country_id=$this->input->post('country_id');
 $country_mobile_code_query='SELECT country_mobile_code FROM '.LOCATIONS.' WHERE id='.$country_id;
 $country_mobile_code=$this->product_model->ExecuteQuery($country_mobile_code_query)->row_array();
 echo json_encode($country_mobile_code);
}
	
	
public function booking_confirm() {
		$this->db->reconnect();
		$bookingDetails = $this->user_model->get_all_details(RENTALENQUIRY,array('Bookingno'=>$this->input->post('Bookingno')));
		
		$message = $this->input->post('message');
		$dataArr = array('productId' => $bookingDetails->row()->prd_id, 'bookingNo' => $bookingDetails->row()->Bookingno, 'senderId' => $bookingDetails->row()->user_id, 'receiverId' => $bookingDetails->row()->renter_id, 'subject' => 'Booking Request : '.$bookingDetails->row()->Bookingno, 'message' => $message);
		
		$this->user_model->simple_insert(MED_MESSAGE, $dataArr);
		
		$this->user_model->update_details( RENTALENQUIRY, array ('booking_status' => 'Pending', 'caltophone' =>$this->input->post('phone_no')), array ('user_id' => $this->checkLogin ( 'U' ),'id' => $this->session->userdata ( 'EnquiryId' ) ) );

		/* Mail function start */
		$id = $this->session->userdata ( 'EnquiryId' );
		$user_id = $this->checkLogin ( 'U' );
		
        $this->data['bookingmail'] = $this->user_model->getbookeduser_detail($id);
		
        $currencycd = $bookingDetails->row()->currencycode;
        $user_currencycode = $bookingDetails->row()->user_currencycode;

		$this->data['hostdetail'] = $this->user_model->get_all_details(USERS,array('id'=>$this->data['bookingmail']->row()->renter_id));
		
		
		
		
		$hostemail = $this->data['hostdetail']->row()->email;
		$hostname = $this->data['hostdetail']->row()->user_name;

        
		$price = $this->data['bookingmail']->row()->totalAmt;
		$hostprice = $this->data['bookingmail']->row()->totalAmt - $this->data['bookingmail']->row()->serviceFee;
		$unitprice = $bookingDetails->row()->unitPerCurrencyUser;

		$checkindate = date('d-M-Y',strtotime($this->data['bookingmail']->row()->checkin));
		$checkoutdate = date('d-M-Y',strtotime($this->data['bookingmail']->row()->checkout));

		$newsid = '16';

		$template_values = $this->user_model->get_newsletter_template_details ( $newsid );
			
		
		if ($template_values ['sender_name'] == '' && $template_values ['sender_email'] == '') {
			$sender_email = $this->config->item ( 'site_contact_mail' );
			$sender_name = $this->config->item ( 'email_title' );
		} else {
			$sender_name = $template_values ['sender_name'];
			$sender_email = $template_values ['sender_email'];
		}
		
		$currency_details = $this->db->select('*')->from('fc_currency')->where('currency_type = ',$currencycd)->get();
		// echo $currency_details->row()->currency_symbols;
		// exit();
		//print_r($email_values); exit;
		
			if($currencycd != $this->session->userdata('currency_type')){

						                     //echo convertCurrency($row->currency,$this->session->userdata('currency_type'),$totalAmount);
											
											
											 if($user_currencycode==$this->session->userdata('currency_type')){ 
												if(!empty($unitprice))
											    $per_price = customised_currency_conversion($unitprice,$this->data['bookingmail']->row()->price); 

												$total_amount = customised_currency_conversion($unitprice,$price);
											 }else{
											 	$per_price = convertCurrency($currencycd,$this->session->userdata('currency_type'),$this->data['bookingmail']->row()->price);

												 $total_amount = convertCurrency($currencycd,$this->session->userdata('currency_type'),$price);
											 }

						                     }else{
						                     	$per_price = $this->data['bookingmail']->row()->price;
						                     	 $total_amount = $price;
						                     }

						                     
		    $User_Booking_info = array('travellername' => $this->data['bookingmail']->row()->name, 'checkindate'=>$checkindate, 'checkoutdate' => $checkoutdate, 'price' => $per_price, 'totalprice' => $total_amount, 'email_title' => $sender_name ,'currencySymbol' =>$this->session->userdata('currency_s'), 'currencycode'=>$currencycd,'logo'=>$this->data['logo']);

		    $Host_Booking_info = array('travellername' => $this->data['bookingmail']->row()->name, 'checkindate'=>$checkindate, 'checkoutdate' => $checkoutdate, 'price' => $this->data['bookingmail']->row()->price, 'totalprice' => $hostprice, 'email_title' => $sender_name ,'currencySymbol' =>$currency_details->row()->currency_symbols, 'currencycode'=>$currencycd,'logo'=>$this->data['logo']);

         /* print_r($hostemail); exit;*/

		    $message = $this->load->view('newsletter/BookInfo'.$newsid.'.php',$Host_Booking_info,TRUE);

           $email_values = array (
				'mail_type' => 'html',
				'from_mail_id' => $sender_email,
				'mail_name' => $sender_name,
				'to_mail_id' => $hostemail, 
				'subject_message' => $template_values['news_subject'],
				'body_messages' => $message 
		);
			
            //send mail
            $this->load->library('email');
            $this->email->from($email_values['from_mail_id'], $sender_name);
            $this->email->to($email_values['to_mail_id']);
            $this->email->subject($email_values['subject_message']);
            $this->email->set_mailtype("html");
            $this->email->message($message); 
            
            try{
            $this->email->send();
           
            }catch(Exception $e)
            {
            echo $e->getMessage();
            }  
			/*mail to guest*/
			$this->data['user_details'] = $this->user_model->get_all_details(USERS,array('id'=>$this->checkLogin ( 'U' )));
            $user_email = $this->data['user_details']->row()->email;

			

         /* print_r($user_email); exit;*/

		    $message = $this->load->view('newsletter/BookInffo_guest.php',$User_Booking_info,TRUE);			
			 $user_email_values = array (
				'mail_type' => 'html',
				'from_mail_id' => $sender_email,
				'mail_name' => $sender_name,
				'to_mail_id' => $user_email, 
				'subject_message' => $template_values['news_subject'],
				'body_messages' => $message 
		);
				$newsid = '64';

		$template_values = $this->user_model->get_newsletter_template_details ( $newsid );
			
		
		if ($template_values ['sender_name'] == '' && $template_values ['sender_email'] == '') {
			$sender_email = $this->config->item ( 'site_contact_mail' );
			$sender_name = $this->config->item ( 'email_title' );
		} else {
			$sender_name = $template_values ['sender_name'];
			$sender_email = $template_values ['sender_email'];
		}
		//send mail
            $this->load->library('email');
            $this->email->from($user_email_values['from_mail_id'], $sender_name);
            $this->email->to($user_email_values['to_mail_id']);
            $this->email->subject($user_email_values['subject_message']);
            $this->email->set_mailtype("html");
            $this->email->message($message); 
            
            try{
            $this->email->send();
           
            }catch(Exception $e)
            {
            echo $e->getMessage();
            }                   

		/* Mail function end */

		/* Traveller Mail Function Start */
		// $id = $this->session->userdata ( 'EnquiryId' );
  //       $this->data['bookingmail'] = $this->user_model->getbookeduser_detail($id);
		// $price = $this->data['bookingmail']->row()->price * $this->data['bookingmail']->row()->noofdates;

		// $checkindate =date('d-M-Y',strtotime($this->data['bookingmail']->row()->checkin));
		// $checkoutdate =date('d-M-Y',strtotime($this->data['bookingmail']->row()->checkout));
		
		// $this->data['hostdetail'] = $this->user_model->get_all_details(USERS,array('id'=>$this->data['bookingmail']->row()->renter_id));
		// $hostname = $this->data['hostdetail']->row()->email;
		// $hostemail = $this->data['hostdetail']->row()->user_name;			
		// $to  = $this->data['bookingmail']->row()->email; 
		// $price = $this->data['bookingmail']->row()->price * $this->data['bookingmail']->row()->noofdates;
		// $prd_id =$this->data['bookingmail']->row()->prd_id;
			
		// $this->data['productimage'] = $this->user_model->getproductimage($prd_id);
		
		// $newsid = '20';

		// $template_values = $this->user_model->get_newsletter_template_details ($newsid);
				
		// if ($template_values ['sender_name'] == '' && $template_values ['sender_email'] == '') {
		// 	$sender_email = $this->config->item ( 'site_contact_mail' );
		// 	$sender_name = $this->config->item ( 'email_title' );
		// } else {
		// 	$sender_name = $template_values ['sender_name'];
		// 	$sender_email = $template_values ['sender_email'];
		// }
		
		// $email_values = array (
		// 		'mail_type' => 'html',
		// 		'from_mail_id' => $sender_email,
		// 		'mail_name' => $sender_name,
		// 		'to_mail_id' => $this->data['bookingmail']->row()->email,
		// 		'subject_message' => $template_values ['news_subject'],
		// 		'body_messages' => $message 
		// );

		//  $traveller_info = array('prd_id' => $this->data['bookingmail']->row()->prd_id, 'travellername' => $this->data['bookingmail']->row()->name, 'productname' => $this->data['bookingmail']->row()->productname, 'prd_image'=>$this->data['productimage']->row()->product_image, 'checkindate'=>$checkindate, 'checkoutdate' => $checkoutdate, 'price' => $this->data['bookingmail']->row()->price, 'totalprice' => $price, 'email_title' => $sender_name, 'currencySymbol' =>$this->session->userdata('currency_s'));
           
  //           $message = $this->load->view('newsletter/TravellerInfo'.$newsid.'.php',$traveller_info,$dataArr,TRUE);
            
  //           $this->load->library('email');
  //           $this->email->from($email_values['from_mail_id'], $sender_name);
  //           $this->email->to($email_values['to_mail_id']);
  //           $this->email->subject($email_values['subject_message']);
  //           $this->email->set_mailtype("html");
  //           $this->email->message($message); 
  //           try{
  //           $this->email->send();
           
  //           }catch(Exception $e)
  //           {
  //           echo $e->getMessage();
  //           }                   

		/* Traveller Mail Function End */
		// $this->emailhostreservationreq($this->session->userdata ( 'EnquiryId' ));
		// $this->traveller_reservation($this->session->userdata ( 'EnquiryId' ));
		
		$user_id =$this->uri->segment(4);
		//echo $user_id; die;
		if($this->lang->line('Congratulation on your booking!! The host will reply you soon.') != '') 
				{ 
					$message = stripslashes($this->lang->line('Congratulation on your booking!! The host will reply you soon.')); 
				} 
				else 
				{
					$message = "Congratulation on your booking!! The host will reply you soon.";
				}

			$this->setErrorMessage ('success',$message);
		
		//redirect('booking/'.$user_id);
		redirect('trips/upcoming');
	
	}
public function booking_confirm_instant(){
		$bookingDetails = $this->user_model->get_all_details(RENTALENQUIRY,array('Bookingno'=>$this->input->post('Bookingno')));
		$message = $this->input->post('message');
		$dataArr = array('productId' => $bookingDetails->row()->prd_id, 'bookingNo' => $bookingDetails->row()->Bookingno, 'senderId' => $bookingDetails->row()->user_id, 'receiverId' => $bookingDetails->row()->renter_id, 'subject' => 'Booking Request : '.$bookingDetails->row()->Bookingno, 'message' => $message, 'currencycode' => $bookingDetails->row()->currencycode);
		
		$this->user_model->simple_insert(MED_MESSAGE, $dataArr);
		
		$this->user_model->update_details( RENTALENQUIRY, array ('booking_status' => 'Pending', 'caltophone' =>$this->input->post('phone_no')), array ('user_id' => $this->checkLogin ( 'U' ),'id' => $this->session->userdata ( 'EnquiryId' ) ) );

		/* Mail function start */

        $this->data['bookingmail'] = $this->user_model->getbookeduser_detail($id);
		$price = $this->data['bookingmail']->row()->totalAmt;
		$hostprice = $this->data['bookingmail']->row()->totalAmt - $this->data['bookingmail']->row()->serviceFee;
		$currencycd = $bookingDetails->row()->currencycode;
        $user_currencycode = $bookingDetails->row()->user_currencycode;
		$unitprice = $bookingDetails->row()->unitPerCurrencyUser;

		$checkindate = date('d-M-Y',strtotime($this->data['bookingmail']->row()->checkin));
		$checkoutdate = date('d-M-Y',strtotime($this->data['bookingmail']->row()->checkout));

		$newsid = '16';

		$template_values = $this->user_model->get_newsletter_template_details ( $newsid );
			
		
		if ($template_values ['sender_name'] == '' && $template_values ['sender_email'] == '') {
			$sender_email = $this->config->item ( 'site_contact_mail' );
			$sender_name = $this->config->item ( 'email_title' );
		} else {
			$sender_name = $template_values ['sender_name'];
			$sender_email = $template_values ['sender_email'];
		}
		
		$email_values = array (
				'mail_type' => 'html',
				'from_mail_id' => $sender_email,
				'mail_name' => $sender_name,
				'to_mail_id' => $hostemail, 
				'subject_message' => $template_values['news_subject'],
				'body_messages' => $message 
		);

		$currency_details = $this->db->select('*')->from('fc_currency')->where('currency_type = ',$currencycd)->get();
	
		
			if($currencycd != $this->session->userdata('currency_type')){

					if($user_currencycode==$this->session->userdata('currency_type')){ 
							if(!empty($unitprice))
									 $per_price = customised_currency_conversion($unitprice,$this->data['bookingmail']->row()->price); 

									$total_amount = customised_currency_conversion($unitprice,$price);
							}else{
									$per_price = convertCurrency($currencycd,$this->session->userdata('currency_type'),$this->data['bookingmail']->row()->price);

										$total_amount = convertCurrency($currencycd,$this->session->userdata('currency_type'),$price);
										 }

						       }else{
						           $per_price = $this->data['bookingmail']->row()->price;
						            $total_amount = $price;
						            }

		    $Host_Booking_info = array('travellername' => $this->data['bookingmail']->row()->name, 'checkindate'=>$checkindate, 'checkoutdate' => $checkoutdate, 'price' => $this->data['bookingmail']->row()->price, 'totalprice' => $hostprice, 'email_title' => $sender_name ,'currencySymbol' =>$currency_details->row()->currency_symbols);

		    // $Host_Booking_info = array('travellername' => $this->data['bookingmail']->row()->name, 'checkindate'=>$checkindate, 'checkoutdate' => $checkoutdate, 'price' => $this->data['bookingmail']->row()->price, 'totalprice' => $hostprice, 'email_title' => $sender_name ,'currencySymbol' =>$currency_details->row()->currency_symbols, 'currencycode'=>$currencycd,'logo'=>$this->data['logo']);

           
            $message = $this->load->view('newsletter/BookInfo'.$newsid.'.php',$Host_Booking_info,$dataArr,TRUE);

            //send mail
            $this->load->library('email');
            $this->email->from($email_values['from_mail_id'], $sender_name);
            $this->email->to($email_values['to_mail_id']);
            $this->email->subject($email_values['subject_message']);
            $this->email->set_mailtype("html");
            $this->email->message($message); 
			//echo $message; exit;
            try{
            $this->email->send();
           
            }catch(Exception $e)
            {
            echo $e->getMessage();
            }                   

		/* Mail function end */

		/* Traveller Mail Function Start */
		$id = $this->session->userdata ( 'EnquiryId' );
        $this->data['bookingmail'] = $this->user_model->getbookeduser_detail($id);
		$price = $this->data['bookingmail']->row()->totalAmt;

		$checkindate =date('d-M-Y',strtotime($this->data['bookingmail']->row()->checkin));
		$checkoutdate =date('d-M-Y',strtotime($this->data['bookingmail']->row()->checkout));
		
		$this->data['hostdetail'] = $this->user_model->get_all_details(USERS,array('id'=>$this->data['bookingmail']->row()->renter_id));
		$hostname = $this->data['hostdetail']->row->email;
		$hostemail = $this->data['hostdetail']->row->user_name;			
		$to  = $this->data['bookingmail']->row()->email; 
		$price = $this->data['bookingmail']->row()->totalAmt;
		$prd_id =$this->data['bookingmail']->row()->prd_id;
			
		$this->data['productimage'] = $this->user_model->getproductimage($prd_id);
		
		$newsid = '20';

		$template_values = $this->user_model->get_newsletter_template_details ($newsid);
				
		if ($template_values ['sender_name'] == '' && $template_values ['sender_email'] == '') {
			$sender_email = $this->config->item ( 'site_contact_mail' );
			$sender_name = $this->config->item ( 'email_title' );
		} else {
			$sender_name = $template_values ['sender_name'];
			$sender_email = $template_values ['sender_email'];
		}
		
		$email_values = array (
				'mail_type' => 'html',
				'from_mail_id' => $sender_email,
				'mail_name' => $sender_name,
				'to_mail_id' => $this->data['bookingmail']->row()->email,
				'subject_message' => $template_values ['news_subject'],
				'body_messages' => $message 
		);
		
		 $traveller_info = array('prd_id' => $this->data['bookingmail']->row()->prd_id, 'travellername' => $this->data['bookingmail']->row()->name, 'productname' => $this->data['bookingmail']->row()->productname, 'prd_image'=>$this->data['productimage']->row()->product_image, 'checkindate'=>$checkindate, 'checkoutdate' => $checkoutdate, 'price' => $per_price, 'totalprice' => $total_amount, 'email_title' => $sender_name, 'currencySymbol' =>$this->session->userdata('currency_s'));
           
            $message = $this->load->view('newsletter/TravellerInfo'.$newsid.'.php',$traveller_info,$dataArr,TRUE);
            //send mail
            $this->load->library('email');
            $this->email->from($email_values['from_mail_id'], $sender_name);
            $this->email->to($email_values['to_mail_id']);
            $this->email->subject($email_values['subject_message']);
            $this->email->set_mailtype("html");
            $this->email->message($message); 
            try{
            $this->email->send();
           
            }catch(Exception $e)
            {
            echo $e->getMessage();
            }                   

		/* Traveller Mail Function End */

		//$this->emailhostreservationreq($this->session->userdata ( 'EnquiryId' ));
		//$this->traveller_reservation($this->session->userdata ( 'EnquiryId' ));
		
		$dataArr = array(
			'productId' => $bookingDetails->row()->prd_id ,
			'senderId' => $bookingDetails->row()->renter_id ,
			'receiverId' => $bookingDetails->row()->user_id ,
			'bookingNo' => $bookingDetails->row()->Bookingno ,
			'subject' => 'Booking Request : '.$bookingDetails->row()->Bookingno ,
			'message' => 'Accepted',
			'point' => '1',
			'status' => 'Accept'
		);
		
		$this->db->insert(MED_MESSAGE, $dataArr);
		$this->db->where('bookingNo', $bookingDetails->row()->Bookingno);
		$this->db->update(MED_MESSAGE, array('status' => 'Accept'));
		$newdata = array('approval' => 'Accept');
		$condition = array('Bookingno' => $bookingDetails->row()->Bookingno);
		$this->user_model->update_details(RENTALENQUIRY,$newdata,$condition);
		$bookingDetails = $this->user_model->get_all_details(RENTALENQUIRY, $condition);
		$enqId = $bookingDetails->row()->id;
		redirect("site/user/confirmbooking/".$enqId);
	}

	/*
	public function booking_confirm_instant(){
		$bookingDetails = $this->user_model->get_all_details(RENTALENQUIRY,array('Bookingno'=>$this->input->post('Bookingno')));
		$message = $this->input->post('message');
		$dataArr = array('productId' => $bookingDetails->row()->prd_id, 'bookingNo' => $bookingDetails->row()->Bookingno, 'senderId' => $bookingDetails->row()->user_id, 'receiverId' => $bookingDetails->row()->renter_id, 'subject' => 'Booking Request : '.$bookingDetails->row()->Bookingno, 'message' => $message);
		
		$this->user_model->simple_insert(MED_MESSAGE, $dataArr);
		
		$this->user_model->update_details( RENTALENQUIRY, array ('booking_status' => 'Pending', 'caltophone' =>$this->input->post('phone_no')), array ('user_id' => $this->checkLogin ( 'U' ),'id' => $this->session->userdata ( 'EnquiryId' ) ) );
		$this->emailhostreservationreq($this->session->userdata ( 'EnquiryId' ));
		$this->traveller_reservation($this->session->userdata ( 'EnquiryId' ));
		
		$dataArr = array(
			'productId' => $bookingDetails->row()->prd_id ,
			'senderId' => $bookingDetails->row()->renter_id ,
			'receiverId' => $bookingDetails->row()->user_id ,
			'bookingNo' => $bookingDetails->row()->Bookingno ,
			'subject' => 'Booking Request : '.$bookingDetails->row()->Bookingno ,
			'message' => 'Accepted',
			'point' => '1',
			'status' => 'Accept'
		);
		
		$this->db->insert(MED_MESSAGE, $dataArr);
		$this->db->where('bookingNo', $bookingDetails->row()->Bookingno);
		$this->db->update(MED_MESSAGE, array('status' => 'Accept'));
		$newdata = array('approval' => 'Accept');
		$condition = array('Bookingno' => $bookingDetails->row()->Bookingno);
		$this->user_model->update_details(RENTALENQUIRY,$newdata,$condition);
		$bookingDetails = $this->user_model->get_all_details(RENTALENQUIRY, $condition);
		$enqId = $bookingDetails->row()->id;
		redirect("site/user/confirmbooking/".$enqId);
	}
*/
	
	public function confirmbooking() {
		if ($this->checkLogin ( 'U' ) == '') {
			redirect ( base_url () );
		} else {
			$id = $this->uri->segment(4); 
			
			$this->data['datavalues'] = $this->user_model->get_all_details(RENTALENQUIRY,array('id'=>$id));
			if($this->data['datavalues']->row()->booking_status == 'Booked' || $this->data['datavalues']->row()->booking_status == 'Enquiry' || $this->data['datavalues']->row()->approval != 'Accept')
			{
				redirect(base_url().'trips/upcoming');
			}
			$user = $this->data['datavalues']->row_array();

			$refno = $this->data['datavalues']->row()->Bookingno;
			$Rental_id = $this->data['datavalues']->row()->prd_id;
			
			$this->data['pay'] = $this->user_model->get_all_details(PAYMENT,array('user_id'=>$user['user_id']));
			
			$this->data['userDetails'] = $this->user_model->get_all_details (USERS, array ('id' => $user['user_id']) );
			
			$this->data ['productList'] = $this->product_model->view_product_details_booking ( ' where p.id="' . $Rental_id . '"  group by p.id order by p.created desc limit 0,1' );
	
			$this->data ['countryList'] = $this->product_model->get_country_list ();
			
			$this->data ['BookingUserDetails'] = $this->product_model->view_user_details_booking ( ' where p.id="' . $Rental_id . '" and rq.id="' . $this->session->userdata ( 'EnquiryId' ) . '" group by p.id order by p.created desc limit 0,1' );
			
			$service_tax_query='SELECT * FROM '.COMMISSION.' WHERE commission_type="Guest Booking" AND status="Active"';
			$this->data['service_tax']=$this->product_model->ExecuteQuery($service_tax_query);

			if ($this->data ['productList']->row ()->meta_title != '') {
				$this->data ['meta_title'] = $this->data ['productList']->row ()->meta_title;
			}
			
			if ($this->data ['productList']->row ()->meta_keyword != '') {
				$this->data ['meta_keyword'] = $this->data ['productList']->row ()->meta_keyword;
			}
			
			if ($this->data ['productList']->row ()->meta_description != '') {
				$this->data ['meta_description'] = $this->data ['productList']->row ()->meta_description;
			}
			
			$this->data['paypal_status'] = $this->product_model->get_all_details(PAYMENT_GATEWAY,array('gateway_name'=>'Paypal IPN'));
			
			$this->data['creditCard_status'] = $this->product_model->get_all_details(PAYMENT_GATEWAY,array('gateway_name'=>'Credit Card'));
			
			$tax_query = 'SELECT * FROM ' . COMMISSION . ' WHERE id=4';
			
			$this->data ['tax'] = $this->product_model->ExecuteQuery ( $tax_query );
			
			$uniid = time()."-".$refno;
			$this->data['RefNo'] = $uniid;
			$source ="DbQhpCuQpPM07244".$uniid."100MYR";
			$val = sha1($source);
			$rval = $this->hex2bin($val);
			$this->data['signature']=  base64_encode($rval);

			/* user wallet  */
			$userId = $this->checkLogin ('U');
			$selRefferalAmount_q = "select referalAmount,referalAmount_currency from ".USERS." where id ='".$userId."' " ;
			$this->data['userWallet'] = $this->product_model->ExecuteQuery($selRefferalAmount_q);
			/* user wallet ends */
			
			$this->load->view ( 'site/rentals/confirmpayment', $this->data );
		}
	}	
	
	function hex2bin( $str ) {
        $sbin = "";
        $len = strlen( $str );
        for ( $i = 0; $i < $len; $i += 2 ) {
            $sbin .= pack( "H*", substr( $str, $i, 2 ) );
        }

        return $sbin;
    }
	
	
	
	
	public function invoice() {
		header('Content-Type: text/html; charset="utf-8"', true); 
	$id = $this->uri->segment(4);
	
	$Invoicetmp = $this->product_model->get_all_details(RENTALENQUIRY,array('Bookingno'=>$id));
	
	$user_currencycode=$Invoicetmp->row()->user_currencycode;
	$unitprice=$Invoicetmp->row()->unitPerCurrencyUser;
	
	
	// echo '<pre>'; print_r($Invoicetmp->result_array());
	// exit();
	$eId = $Invoicetmp->row()->id;
	$eId_bookingno = $Invoicetmp->row()->Bookingno;

    $transactionid = $this->product_model->get_all_details(PAYMENT,array('EnquiryId'=>$eId));
	
	$rentalid = $transactionid->row()->sell_id;
	$userId = $this->checkLogin ('U');
	
	$admin_email = $this->product_model->get_all_details(ADMIN,array())->row()->email;
	
	$transid = $Invoicetmp->row()->Bookingno;
	
	$productvalue = $this->product_model->get_all_details(PRODUCT,array('id'=>$Invoicetmp->row()->prd_id));
	
	$host_fee = $this->product_model->get_all_details(COMMISSION_TRACKING,array('booking_no'=>$eId_bookingno));

	if($Invoicetmp->row()->secDeposit != 0)
	{
		$securityDepositeTemp = $Invoicetmp->row()->secDeposit;
	}

	if($Invoicetmp->row()->cleaningFee != 0)
	{
		$cleaningFeeTemp = $Invoicetmp->row()->cleaningFee;
	}
    //$Invoicetmp->row()->prd_id =4;
	$productaddress = $this->product_model->get_all_details(PRODUCT_ADDRESS_NEW,array('productId'=>$Invoicetmp->row()->prd_id));
	
	
	$product_id = $Invoicetmp->row()->prd_id;
	$checkindate =date('d-M-Y',strtotime($Invoicetmp->row()->checkin));
    $checkoutdate =date('d-M-Y',strtotime($Invoicetmp->row()->checkout));
	
	$TotalAmt_temp = ($Invoicetmp->row()->totalAmt) - ($Invoicetmp->row()->serviceFee);

/* 	if($productvalue->row()->currency != $this->session->userdata('currency_type'))
	 {
			$securityDeposite = convertCurrency($productvalue->row()->currency,$this->session->userdata('currency_type'),$securityDepositeTemp);
 		}
		else{
		$securityDeposite = $securityDepositeTemp;
	}
	 */		 
		
	
	if ($user_currencycode==$this->session->userdata('currency_type')){

		if(!empty($unitprice))
			$securityDeposite= customised_currency_conversion($unitprice,$securityDepositeTemp);	
	}else{
		$securityDeposite = convertCurrency($productvalue->row()->currency,$this->session->userdata('currency_type'),$securityDepositeTemp);
	}		
	
	if ($user_currencycode==$this->session->userdata('currency_type')){

		if(!empty($unitprice))
			$hostfee_amount= customised_currency_conversion($unitprice,$host_fee->row()->host_fee);	
	}else{
			$hostfee_amount = convertCurrency($productvalue->row()->currency,$this->session->userdata('currency_type'),$host_fee->row()->host_fee);
	}
			 

	if ($user_currencycode==$this->session->userdata('currency_type')){

		if(!empty($unitprice))
			$cleaningFee= customised_currency_conversion($unitprice,$cleaningFeeTemp);	
	}else{
		$cleaningFee = convertCurrency($productvalue->row()->currency,$this->session->userdata('currency_type'),$cleaningFeeTemp);
	}	
/* 	if($productvalue->row()->currency != $this->session->userdata('currency_type'))
	 {
		$TotalwithoutService = convertCurrency($productvalue->row()->currency,$this->session->userdata('currency_type'),$Invoicetmp->row()->subTotal);
 		}
		else{
				$TotalwithoutService = $Invoicetmp->row()->subTotal;
			 } */
			 
			 
			 
		

	if ($user_currencycode==$this->session->userdata('currency_type')){

		if(!empty($unitprice))
			$TotalwithoutService= customised_currency_conversion($unitprice,$Invoicetmp->row()->subTotal);	
	}else{
		
			$TotalwithoutService = convertCurrency($productvalue->row()->currency,$this->session->userdata('currency_type'),$Invoicetmp->row()->subTotal);
	}		
			 
			 
			 
			 
	//$TotalwithoutService = number_format(pastDateCurrency($product_id,$Invoicetmp->row()->dateAdded,$TotalAmt_temp),2);
	$to  = '';//$this->data['bookingmail']->row()->email; // note the comma
     
     $service = $this->user_model->get_all_details(COMMISSION,array('id'=>2, 'status'=>'Active'));
    // echo '<pre>'; print_r($service->row()->commission_percentage);
	
	
/*      if($productvalue->row()->currency != $this->session->userdata('currency_type'))
	 {
		$servicefee = convertCurrency($productvalue->row()->currency,$this->session->userdata('currency_type'),$Invoicetmp->row()->serviceFee);
 		}
		else{
				$servicefee = $Invoicetmp->row()->serviceFee;
			 } */
			 
			 
			 
	if ($user_currencycode==$this->session->userdata('currency_type')){

		if(!empty($unitprice))
			$servicefee= customised_currency_conversion($unitprice,$Invoicetmp->row()->serviceFee);	
	}else{
		
			$servicefee = convertCurrency($productvalue->row()->currency,$this->session->userdata('currency_type'),$Invoicetmp->row()->serviceFee);
	}
			 
			 
			 
			 
      	//$servicefee = number_format(pastDateCurrency($product_id,$Invoicetmp->row()->dateAdded,$Invoicetmp->row()->serviceFee),2);
	   	
			 $TotalAmt = $TotalwithoutService + $servicefee + $securityDeposite + $cleaningFee;
      	//$TotalAmt = number_format(pastDateCurrency($product_id,$Invoicetmp->row()->dateAdded,$Invoicetmp->row()->totalAmt),2);
		$gtotalAmt = number_format(pastDateCurrency($product_id,$Invoicetmp->row()->dateAdded,$productvalue->row()->price),2); 

	/*  if($Invoicetmp->row()->serviceFee == 0.00) {
	 $servicefee = $service->row()->commission_percentage;
	 $gtotalAmt = $productvalue->row()->price -$service->row()->commission_percentage;
     }
   	 else {
	 $servicefee = $Invoicetmp->row()->serviceFee;
	 $gtotalAmt = $productvalue->row()->price;
	 } */
	 if($this->lang->line('night') != '')
	{
		$night = stripslashes($this->lang->line('night'));
	}else{
		
		$night = "Night";
	} 
	if($this->lang->line('Nights') != '')
	{
		$Nights = stripslashes($this->lang->line('Nights'));
	}else{
		
		$Nights = "Nights";
	}
	if($this->lang->line('Guest') != '')
	{
		$guest = stripslashes($this->lang->line('Guest'));
	}else{
		
		$guest = "Guest";
	}
	if($this->lang->line('guest') != '')
	{
		$Guests = stripslashes($this->lang->line('guest'));
	}else{
		
		$Guests = "Guests";
	}
	 $Night = ($Invoicetmp->row()->numofdates == 1)?$night:$Nights;
	 $Guest = ($Invoicetmp->row()->NoofGuest==1)?$guest:$Guests;
	 //echo "<pre>"; print_r($productaddress->result()); die;
	 $houserule = ($productvalue->row()->house_rules!='')?$productvalue->row()->house_rules:'None';
	 
	// $couponcode ='[Coupon code Used]'.$transactionid->row()->couponCode;

	 /* malar 12/07/2017 -discount details adding */
	 //counpon exists
	 if($transactionid->row()->is_coupon_used == 'Yes')
	 {


	 	$couponDiscount = $transactionid->row()->total_amt - $transactionid->row()->discount ; 

	 	if($transactionid->row()->currency_code != $this->session->userdata('currency_type'))
		{
			$couponDiscount= convertCurrency($transactionid->row()->currency_code,$this->session->userdata('currency_type'),$couponDiscount);
	 	}
			

	 }else {
	 	$couponDiscount = '0.00';
	 }

	 //wallet used 

	 if($transactionid->row()->is_wallet_used == 'Yes')
	 {


	 	$wallet_Amount = $transactionid->row()->wallet_Amount; 

	 	if($transactionid->row()->currency_code != $this->session->userdata('currency_type'))
		{
			$wallet_Amount = convertCurrency($transactionid->row()->currency_code,$this->session->userdata('currency_type'),$wallet_Amount);
	 	}
			

	 }else {
	 	$wallet_Amount = '0.00';
	 }


	 
	if($this->lang->line('Receipt') != '')
	{
		$Receipt = stripslashes($this->lang->line('Receipt'));
	}else{
		
		$Receipt = "Receipt";
	}
	if($this->lang->line('Booking No') != '')
	{
		$Booking_No = stripslashes($this->lang->line('Booking No'));
	}else{
		
		$Booking_No = "Booking No";
	}
	if($this->lang->line('PropertyName') != '')
	{
		$Property_Name = stripslashes($this->lang->line('PropertyName'));
	}else{
		
		$Property_Name = "Property Name";
	}
	if($this->lang->line('Address') != '')
	{
		$Address = stripslashes($this->lang->line('Address'));
	}else{
		
		$Address = "Address";
	}
	if($this->lang->line('check_in') != '')
	{
		$check_in = stripslashes($this->lang->line('check_in'));
	}else{
		
		$check_in = "check_in";
	}
	if($this->lang->line('check_out') != '')
	{
		$check_out = stripslashes($this->lang->line('check_out'));
	}else{
		
		$check_out = "check_out";
	}
	if($this->lang->line('Print Page') != '')
	{
		$Print_Page = stripslashes($this->lang->line('Print Page'));
	}else{
		
		$Print_Page = "Print Page";
	}
	if($this->lang->line('Cancellation Policy') != '')
	{
		$Cancellation_Policy = stripslashes($this->lang->line('Cancellation Policy'));
	}else{
		
		$Cancellation_Policy = "Cancellation Policy";
	}
	if($this->lang->line('For More details of the cancellation policy, please refer') != '')
	{
		$Details_Cancellation_Policy = stripslashes($this->lang->line('For More details of the cancellation policy, please refer'));
	}else{
		
		$Details_Cancellation_Policy = "For More details of the cancellation policy, please refer";
	}
	if($this->lang->line('house_rules') != '')
	{
		$house_rules = stripslashes($this->lang->line('house_rules'));
	}else{
		
		$house_rules = "house_rules";
	}
	if($this->lang->line('Booked for') != '')
	{
		$Bookedfor = stripslashes($this->lang->line('Booked for'));
	}else{
		
		$Bookedfor = "Booked for";
	}
	if($this->lang->line('SecurityDeposit') != '')
	{
		$SecurityDeposit = stripslashes($this->lang->line('SecurityDeposit'));
	}else{
		
		$SecurityDeposit = "SecurityDeposit";
	}

	if($this->lang->line('cleaningfee') != '')
	{
		$cleaningfee = stripslashes($this->lang->line('cleaningfee'));
	}else{
		
		$cleaningfee = "cleaning fee";
	}
	if($this->lang->line('ServiceFee') != '')
	{
		$ServiceFee = stripslashes($this->lang->line('ServiceFee'));
	}else{
		
		$ServiceFee = "ServiceFee";
	}
	if($this->lang->line('HostFee_accecptance') != '')
	{
		$HostFee = stripslashes($this->lang->line('HostFee_accecptance'));
	}else{
		
		$HostFee = "Host Acceptance Fee";
	}
	if($this->lang->line('Total') != '')
	{
		$Total = stripslashes($this->lang->line('Total'));
	}else{
		
		$Total = "Total";
	}
	//malar 12/07/2017 - discount price display
	if($this->lang->line('Wallet') != '')
	{
		$Wallet = stripslashes($this->lang->line('Wallet'));
	}else{
		
		$Wallet = "Wallet";
	}

	if($this->lang->line('Coupon') != '')
	{
		$Coupon = stripslashes($this->lang->line('Coupon'));
	}else{
		
		$Coupon = "Coupon";
	}

	if($this->lang->line('Paid') != '')
	{
		$Paid = stripslashes($this->lang->line('Paid'));
	}else{
		
		$Paid = "Paid";
	}



	if($this->lang->line('24_hours_of_purchase') != '')
	{
		$Recuerde = stripslashes($this->lang->line('24_hours_of_purchase'));
	}else{
		
		$Recuerde = "Any trip or experience can be canceled and fully refunded within 24 hours of purchase.";
	}
	if($this->lang->line('If_you_need_help_or_have_any_questions_please_visit') != '')
	{
		$need_help = stripslashes($this->lang->line('If_you_need_help_or_have_any_questions_please_visit'));
	}else{
		
		$need_help = "If you need help or have any questions, please visit";
	}
	
	//print_r($productaddress->row());
	$host_fee_table = ''; 
	
	if($rentalid == $userId){ 
  $host_fee_table = '<tr>
<td style="border-bottom: 1px solid #bbb;">'.$HostFee.'</td>
    <td style="border-bottom: 1px solid #bbb;"></td>		
    <td style="border-bottom: 1px solid #bbb; padding: 5px 0px;">'.$this->session->userdata('currency_s') .' '.$hostfee_amount.'</td>
  </tr>'; }
  
  
$this->data['message'] = '
<!DOCTYPE html>
<head>
  <title>Renters booking</title>
</head>
	<body style="margin:0px;"><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" bgcolor="#ccc" data-bgcolor="body-bg-dark" data-module="1" class="ui-sortable-handle currentTable">  
	<tbody><tr>
	 <td>
	 <table width="600" border="0" cellspacing="0" cellpadding="0" align="center" class="devicewidth" style="background-color:#ffffff;" data-bgcolor="light-gray-bg"> 
	 <tbody><tr>
	 <td height="30" bgcolor="#752b7e" >&nbsp;</td> 
	 </tr>  
	 <tr>  
	<td align="center">          
	 <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">              
	 <tbody><tr style="padding: 10px 10px 0px 10px; float: left">          
			   
	 <td align="center" valign="top">
					<table width="650" border="0" cellpadding="5" cellspacing="1" >
							<tbody style="font-family:Open Sans, Arial, Helvetica, sans-serif; font-size:13px;">
							<tr>
							  
							  <th width="70" bgcolor="#752b7e"style="color:#fff; font-size:15px;">'.$Receipt.'</th>
							  <th width="75" ></th>
							  <th width="75"></th>
							  <th width="75"></th>
							  <th align="right" width="75" style="color:#f3402e; text-align:right"><a onClick="window.print()" TARGET="_blank" style="cursor: pointer; cursor: hand;text-decoration:underline;">'.$Print_Page.'</a></th>
							</tr>
							
						 
							
				</tbody></table>
				</td>       
	 </tr>          
	 </tr>
	 <tr><td align="left" style="color:#4c4c4c;font-size:13px;font-family:Open Sans, Arial, Helvetica, sans-serif;margin:10px; padding: 10px">'.$Booking_No.' : '.$transid.'</td></tr>
	 <tr><td align="left" style="color:#4c4c4c;font-size:13px;font-family:Open Sans, Arial, Helvetica, sans-serif;margin:10px; padding: 10px">'.$Property_Name.' : '.$productvalue->row()->product_title.'</td></tr>
	 <tr><td align="left" style="color:#4c4c4c;font-size:13px;font-family:Open Sans, Arial, Helvetica, sans-serif;margin:10px; padding: 10px">'.$Address.' : '.$productaddress->row()->address.'</td></tr>
	 <tr>
	 <td style="border-top:1px solid #808080" bgcolor="#fff">&nbsp;</td>       
	 </tr>        
	 <tr>         
	 <td>
	 <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">              
	 <tbody><tr style="padding: 10px; float: left">          
			   
	 <td align="center" valign="top">
					<table width="650" border="0" cellpadding="5" cellspacing="1" >
							<tbody style="font-family:Open Sans, Arial, Helvetica, sans-serif; font-size:13px;" ><tr>
							  <th width="75" bgcolor="#EFEFEF">'.$check_in.'</th>
							 <th width="5"></th>
							  <th width="75" bgcolor="#EFEFEF">'.$check_out.'</th>
							  <th width="75" ></th>
							  <th width="75" bgcolor="#EFEFEF">'.$Night.'</th>
							  <th width="75" bgcolor="#EFEFEF">'.$Guest.'</th>
							 
							</tr>
							<tr align="center">
								<td >'.$checkindate.'</td>
								<td ></td>
								<td >'.$checkoutdate.'</td>
								<td ></td>
								<td >'.$Invoicetmp->row()->numofdates.'</td>
								<td >'.$Invoicetmp->row()->NoofGuest.'</td>

							  </tr>
						
							
						 
							
				</tbody></table>
				</td>       
	 </tr>          
	 </tbody>
	 </table>  
	 </td>
	 </tr>      
	 
	<tr style="pointer-events:none;">
	 <td align="center" valign="top" style="color:#000; font-weight: 700; font-family:Open Sans, Arial, Helvetica, sans-serif; font-size:13px;" data-size="body-text" data-min="10" data-max="25" data-color="footer-text">
<img id="map-image" border="0" alt="'.$productaddress->row()->address.'" src="https://maps.googleapis.com/maps/api/staticmap?center='.$productaddress->row()->address.'&zoom=13&size=600x300&maptype=roadmap&sensor=false&format=png&visual_refresh=true&markers=size:mid%7Ccolor:red%7C'. $productaddress->row()->address.'">	
	 </td> 
	</tr> 
		   
	 <tr>      
	 <td>&nbsp;</td>      
	 </tr>       
	      
	 <tr>   
	  <tr>         
 <td align="center" >          
 <table width="100%" border="0" cellspacing="1" cellpadding="0" align="center" style="padding:0px 10px;">              
 <tbody><tr>          
            
 <td align="left" width="300px" valign="top" style="color:#4f595b; font-family:Open Sans, Arial, Helvetica, sans-serif; font-size:13px; line-height:20px;" data-size="body-text" data-min="10" data-max="25" data-color="footer-text">
<h4 style="float: left; width:100%;">'.$Cancellation_Policy.'  -    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.ucfirst($productvalue->row()->cancellation_policy).'</h4>'.$Details_Cancellation_Policy.' <a href="'.base_url().'pages/cancellation-policy" target="_blank">'.$Cancellation_Policy.'</a>.
 <td>
 
 </tr>      


<tr>          
      
 <td align="left" width="300px" valign="top" style="color:#4f595b; font-family:Open Sans, Arial, Helvetica, sans-serif; font-size:13px; line-height:20px;" data-size="body-text" data-min="10" data-max="25" data-color="footer-text">
<h4 style="float: left; width:100%; margin: 10px 0px;">Billing</h4>
<table style="width:100%; font-size:13px;">
  <tr>
    <td style="border-bottom: 1px solid #bbb;">'.$Bookedfor.' '.$Invoicetmp->row()->numofdates.'  &nbsp;'.$Night.'</td>
    <td style="border-bottom: 1px solid #bbb;"></td>		
    <td style="border-bottom: 1px solid #bbb; padding: 5px 0px;">'.$this->session->userdata('currency_s') .' '.$TotalwithoutService.'</td>
  </tr>

 <tr>
<td style="border-bottom: 1px solid #bbb;">'.$SecurityDeposit.'</td>
    <td style="border-bottom: 1px solid #bbb;"></td>		
    <td style="border-bottom: 1px solid #bbb; padding: 5px 0px;">'.$this->session->userdata('currency_s') .' '.$securityDeposite.'</td>
  </tr>

   <tr>
<td style="border-bottom: 1px solid #bbb;">'.$cleaningfee.'</td>
    <td style="border-bottom: 1px solid #bbb;"></td>		
    <td style="border-bottom: 1px solid #bbb; padding: 5px 0px;">'.$this->session->userdata('currency_s') .' '.$cleaningFee.'</td>
  </tr>

  <tr>
<td style="border-bottom: 1px solid #bbb;">'.$ServiceFee.'</td>
    <td style="border-bottom: 1px solid #bbb;"></td>		
    <td style="border-bottom: 1px solid #bbb; padding: 5px 0px;">'.$this->session->userdata('currency_s') .' '.$servicefee.'</td>
  </tr> 
  
  '.$host_fee_table.'
  
  <tr>
	<td style="border-bottom: 1px solid #bbb;  padding: 10px 0px;">'.$Total.'</td>
    <td style="border-bottom: 1px solid #bbb;padding: 10px 0px;"></td>		
    <td style="border-bottom: 1px solid #bbb;padding: 10px 0px;">'.$this->session->userdata('currency_s') .' '.$TotalAmt.'</td>
	
	
	
  </tr>
  
  <tr>
<td style="border-bottom: 1px solid #bbb;  padding: 10px 0px;">'.$Coupon.'</td>
    <td style="border-bottom: 1px solid #bbb;padding: 10px 0px;"></td>		
    <td style="border-bottom: 1px solid #bbb;padding: 10px 0px;">'.$this->session->userdata('currency_s') .' '.$couponDiscount.'</td>
	
	
	
  </tr>
 
  
  <tr>
<td style="border-bottom: 1px solid #bbb;  padding: 10px 0px;">'.$Wallet.'</td>
    <td style="border-bottom: 1px solid #bbb;padding: 10px 0px;"></td>		
    <td style="border-bottom: 1px solid #bbb;padding: 10px 0px;">'.$this->session->userdata('currency_s') .' '.$wallet_Amount.'</td>
	
	
	
  </tr>
 
  <tr>
<td style="border-bottom: 1px solid #bbb;  padding: 10px 0px;">'.$Paid.'</td>
    <td style="border-bottom: 1px solid #bbb;padding: 10px 0px;"></td>		
    <td style="border-bottom: 1px solid #bbb;padding: 10px 0px;">'.$this->session->userdata('currency_s') .' '.($TotalAmt-($wallet_Amount+$couponDiscount)).'</td>
	
	
	
  </tr>
  
 
</table>

<td>
 </tr> 
 
 </tbody>
 </table>      
 </td>        
 </tr> 
	 </tr>
	        
	 <tr>      
	 <td>&nbsp;</td>     
	 </tr>       
	 <tr>    
	 <td align="center" valign="middle" style="color:#444444; font-family:Open Sans, Arial, Helvetica, sans-serif; font-size:13px;"><a href="javascript:void(0);" style="color:#0094aa; text-decoration:none;" data-size="body-text" data-min="10" data-max="25" data-link-color="plain-url-color" data-link-size="plain-url-text">'.$Recuerde.'</a></td>       
	 </tr>        
	 <tr>        
	 <td>&nbsp;</td>   
	 </tr>              
	 <tr>               
	 <td align="center" valign="middle" style="color:#444444; font-family:Open Sans, Arial, Helvetica, sans-serif; font-size:13px; padding:0 20px;" data-size="body-text" data-min="10" data-max="25" data-color="body-text">'.$need_help.' <a href="mailto:'.$admin_email.'" style="color:#0094aa;" data-link-color="plain-url-color"> '.$admin_email.'</a></td>     
	 </tr>       
	 <tr>       
	 <td height="50">&nbsp;</td>      
	 </tr>         
	 <tr>       
	 <td height="30" bgcolor="#752b7e">&nbsp;</td>     
	 </tr>      
	 <tr>         
	 <td align="center" bgcolor="#fff">          
	 <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="padding:0px 10px;">              
	 <tbody>
	 
	 </tbody>
	 </table>      
	 </td>        
	 </tr>         
	 <tr>
	 <td height="30" bgcolor="#4BBEFF" >&nbsp;</td> 
	 </tr> 
	 </tbody></table> 
	 </td>      </tr>  
	 </tbody></table>
	 </body>	
</html>
';
/* <iframe width="800px" height="464px" src="https://maps.google.com/?q='. $productaddress->row()->latitude.','.$productaddress->row()->longitude.'&amp;ie=UTF8&amp;t=m&amp;z=8&amp;ll='.$productaddress->row()->latitude.','. $productaddress->row()->longitude.'&amp;spn=0.006295,0.006295&amp;iwloc=A&amp;output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
 */	
// To send HTML mail, the Content-type header must be set
/* $headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

// Additional headers
//$headers .= 'To: '.$this->data['bookingmail']->row()->name.' <'.$hostemail.'>, '.$hostname.' <kelly@example.com>' . "\r\n";
$headers .= 'From: Staynest Booking Request Confirmation' . "\r\n"; */

//echo $message; die;
// Mail it
/* mail($to, $subject, $message, $headers); */
	
	
	
	$this->load->view ( 'site/user/invoice', $this->data );
	
	
	
	}
	
	
	public function ipaysuccess() { 
	/* echo '<pre>'; print_r($_REQUEST); die;
			if($_REQUEST['Status']==1) {
			
			$Bookno =  explode("-", $_REQUEST);
			$Bookingno = $Bookno[1];
			$Amount = $_REQUEST['Amount'];
			$Authcode = $_REQUEST['AuthCode'];
			$Transid = $_REQUEST['TransId'];
			$signature =  $_REQUEST['Signature'];
			$prddetail = $this->user_model->get_all_details(RENTALENQUIRY,array('Bookingno'=>$Bookingno));
			$user_id = $prddetail->row()->user_id;			
			$prd_id =$prddetail->row()->prd_id;
			$sell_id = $prddetail->row()->renter_id;
			$Enquiryid = $prddetail->row()->id;
			$total_amt = $_REQUEST['Amount'];
			$price = $_REQUEST['Amount'];
			$status ="Paid";
			$payment_type ="ipay";

            $insertqry ="insert into   fc_payment(user_id,sell_id,product_id,price,total,paypal_transaction_id,status,EnquiryId,payment_type) value('".$user_id."','".$sell_id."','".$prd_id."','".$total_amt."','".$total_amt."','".$signature."','".$status."','".$Enquiryid."','".$payment_type."')";
			mysql_query($insertqry); 
			$this->data['amount'] =$_REQUEST['Amount'];
			$this->data['RefNo'] =$Bookno[1];
			$this->data['errdesc']="";
			$this->data['status'] ='Success';
			$this->booking_conform_mail($Bookno[1]);
			$this->booking_conform_mail_admin($Bookno[1]);
			$this->booking_conform_mail_host($Bookno[1]); */
			$this->load->view('site/user/ipaysuccess', $this->data);
	
		 /* }
		else {
		    echo '<pre>'; print_r($_REQUEST); die;
		    $Bookno =  explode("-", $_REQUEST);
		    $Bookingno = $Bookno[1];			
			$Amount = $_REQUEST['Amount'];
			$Authcode = $_REQUEST['AuthCode'];
			$Transid = $_REQUEST['TransId'];
			$signature =  $_REQUEST['Signature'];
			$prddetail = $this->user_model->get_all_details(RENTALENQUIRY,array('Bookingno'=>$Bookingno));
			$user_id = $prddetail->row()->user_id;			
			$prd_id =$prddetail->row()->prd_id;
			$sell_id = $prddetail->row()->renter_id;
			$Enquiryid = $prddetail->row()->id;
			$total_amt = $_REQUEST['Amount'];
			$price = $_REQUEST['Amount'];
			$status ="Pending";
			$payment_type ="ipay";
			$Errdesc = $_REQUEST['ErrDesc'];
            $insertqry ="insert into   fc_payment(user_id,sell_id,product_id,price,total,paypal_transaction_id,status,EnquiryId,errmsg,payment_type) value('".$user_id."','".$sell_id."','".$prd_id."','".$total_amt."','".$total_amt."','".$signature."','".$status."','".$Enquiryid."','".$Errdesc."','".$payment_type."')";
			mysql_query($insertqry);
			$this->data['amount'] =$_REQUEST['Amount'];
			$this->data['RefNo'] =$Bookno[1];
			$this->data['errdesc']="Your Transaction is failed ! Kindly Contact your bank";
			$this->data['status'] ='Failed';
			$this->load->view('site/user/ipaysuccess', $this->data);
		    
		}  */
	 
	 
	 }

	public function booking_conform_mail($paymentid){

	$PaymentSuccess = $this->order_model->get_all_details(PAYMENT,array('dealCodeNumber' => $paymentid)); 
					
    $Renter_details = $this->order_model->get_all_details(USERS,array('id'=>$PaymentSuccess->row()->sell_id));
	echo '<br>'.$this->db->last_query();
					//var_dump($Renter_details->row());die;
	
	$user_details = $this->order_model->get_all_details(USERS,array('id'=>$PaymentSuccess->row()->user_id));
					
	$Rental_details = $this->order_model->get_all_details(PRODUCT,array('id'=>$PaymentSuccess->row()->product_id));
    $Contact_details = $this->order_model->get_all_details(RENTALENQUIRY,array( 'id' => $PaymentSuccess->row()->EnquiryId));
	$RentalPhoto = $this->order_model->get_all_details(PRODUCT_PHOTOS,array('product_id'=>$PaymentSuccess->row()->product_id));
					
	//$total = $Renter_details->row()->price * $Contact_details->row()->numofdates;
	$total = $Contact_details->row()->totalAmt-$Contact_details->row()->serviceFee;
	
	//---------------email to user---------------------------
	$newsid='29';
	$template_values=$this->order_model->get_newsletter_template_details($newsid);
					
	$subject = 'From: '.$this->config->item('email_title').' - '.$template_values['news_subject'];
	$proImages=base_url().PRODUCTPATH.$RentalPhoto->row()->product_image;
	$chkIn = date('d-m-y',strtotime($Contact_details->row()->checkin));
	$chkOut = date('d-m-y',strtotime($Contact_details->row()->checkout));
	$adminnewstemplateArr=array(
				'email_title'=>$this->config->item('email_title'),
				'logo'=>$this->data['logo'],
				'first_name'=>$user_details->row()->firstname,
				'last_name'=>$user_details->row()->lastname,
				'NoofGuest'=>$Contact_details->row()->NoofGuest,
				'numofdates'=>$Contact_details->row()->numofdates,
				'booking_status'=>$Contact_details->row()->booking_status,
				'user_email'=>$user_details->row()->email,
				'ph_no'=>$Renter_details->row()->phone_no,
				'Enquiry'=>$Contact_details->row()->Enquiry,
				'checkin'=>$chkIn,
				'checkout'=>$chkOut,
				'price'=>$Renter_details->row()->price,
				'amount'=>$total,
				'netamount'=>$Contact_details->row()->totalAmt,
				'noofnights'=>$Contact_details->row()->numofdates,
				'serviceFee'=>$Contact_details->row()->serviceFee,
				'renter_id'=>$PaymentSuccess->row()->sell_id,
				'prd_id'=>$PaymentSuccess->row()->product_id,
				'renter_fname'=>$Renter_details->row()->firstname,
				'renter_lname'=>$Renter_details->row()->lastname,
				'owner_email'=>$Renter_details->row()->email,
				'owner_phone'=>$Renter_details->row()->phone_no,
				'rental_name'=>$Rental_details->row()->product_title,
				'rental_image'=>$proImages);
                    
                    
	extract($adminnewstemplateArr);

	$header .="Content-Type: text/plain; charset=ISO-8859-1\r\n";
	
	$message .= '<!DOCTYPE HTML>
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width"/><body>';
	
	
	include('./newsletter/registeration'.$newsid.'.php');	
	
	$message .= '</body>';
		
	
	if($template_values['sender_name']=='' && $template_values['sender_email']==''){
		$sender_email=$this->data['siteContactMail'];
		$sender_name=$this->data['siteTitle'];
	}else{
		$sender_name=$template_values['sender_name'];
		$sender_email=$template_values['sender_email'];
	}
	
	/*$sender_name=ucfirst($Renter_details->row()->first_name).' '.ucfirst($Renter_details->row()->last_name);
	$sender_email=$Renter_details->row()->email;*/
	
	//add inbox from mail 
	$this->order_model->simple_insert(INBOX,array('sender_id'=>$sender_email,'user_id'=>$this->data['userDetails']->row()->email,'mailsubject'=>$template_values['news_subject'],'description'=>stripslashes($message)));
	$this->session->set_userdata('ContacterEmail',$user_details->row()->email);
	
	$email_values = array('mail_type'=>'html',
						'from_mail_id'=>$sender_email,
						'mail_name'=>$sender_name,
						'to_mail_id'=>$this->data['userDetails']->row()->email,
						'subject_message'=>$template_values['news_subject'],
						'body_messages'=>$message
						);
					//print_r(stripslashes($message));die;	
					
	//echo '<pre>'; print_r($email_values);					
	$email_send_to_common = $this->order_model->common_email_send($email_values);
	
	//$this->mail_owner_admin_booking($adminnewstemplateArr);
}	
	
public function booking_conform_mail_admin($paymentid){

	$PaymentSuccess = $this->order_model->get_all_details(PAYMENT,array('dealCodeNumber' => $paymentid)); 
					
	$condition = array('id'=>$PaymentSuccess->row()->sell_id);
	$Renter_details = $this->order_model->get_all_details(USERS,$condition);

	$condition3 = array('id'=>$PaymentSuccess->row()->user_id);
	$user_details = $this->order_model->get_all_details(USERS,$condition3);
	
	$condition1 = array('id'=>$PaymentSuccess->row()->product_id);
	$Rental_details = $this->order_model->get_all_details(PRODUCT,$condition1);
	$Contact_details = $this->order_model->get_all_details(RENTALENQUIRY,array( 'id' => $PaymentSuccess->row()->EnquiryId));
	$RentalPhoto = $this->order_model->get_all_details(PRODUCT_PHOTOS,array('product_id'=>$PaymentSuccess->row()->product_id));
					
	/* $total = $Renter_details->row()->price * $Contact_details->row()->numofdates; */
	$total = $Contact_details->row()->totalAmt-$Contact_details->row()->serviceFee;
		//---------------email to user---------------------------
	$newsid='33';
	$template_values=$this->order_model->get_newsletter_template_details($newsid);
	
	
	$subject = 'From: '.$this->config->item('email_title').' - '.$template_values['news_subject'];
	$proImages=base_url().PRODUCTPATH.$RentalPhoto->row()->product_image;
	$chkIn = date('d-m-y',strtotime($Contact_details->row()->checkin));
	$chkOut = date('d-m-y',strtotime($Contact_details->row()->checkout));
	$adminnewstemplateArr=array(
			'email_title'=>$this->config->item('email_title'),
			'logo'=>$this->data['logo'],
			'first_name'=>$user_details->row()->firstname,
			'last_name'=>$user_details->row()->lastname,
			'NoofGuest'=>$Contact_details->row()->NoofGuest,
			'numofdates'=>$Contact_details->row()->numofdates,
			'booking_status'=>$Contact_details->row()->booking_status,
			'user_email'=>$user_details->row()->email,
			'ph_no'=>$user_details->row()->phone_no,
			'Enquiry'=>$Contact_details->row()->Enquiry,
			'checkin'=>$chkIn,
			'checkout'=>$chkOut,
			'price'=>$Renter_details->row()->price,
			'amount'=>$total,
			'netamount'=>$Contact_details->row()->totalAmt,
			'noofnights'=>$Contact_details->row()->numofdates,
			'serviceFee'=>$Contact_details->row()->serviceFee,
			'renter_id'=>$PaymentSuccess->row()->sell_id,
			'prd_id'=>$PaymentSuccess->row()->product_id,
			'renter_fname'=>$Renter_details->row()->firstname,
			'renter_lname'=>$Renter_details->row()->lastname,
			'owner_email'=>$Renter_details->row()->email,
			'owner_phone'=>$Renter_details->row()->phone_no,
			'rental_name'=>$Rental_details->row()->product_title,
			'rental_image'=>$proImages);
	
	
	extract($adminnewstemplateArr);

	$header .="Content-Type: text/plain; charset=ISO-8859-1\r\n";
	
	$message .= '<body>';
	
	
	include('./newsletter/registeration'.$newsid.'.php');	
	
	$message .= '</body>
		</html>';
	
	if($template_values['sender_name']=='' && $template_values['sender_email']==''){
		$sender_email=$this->data['siteContactMail'];
		$sender_name=$this->data['siteTitle'];
	}else{
		$sender_name=$template_values['sender_name'];
		$sender_email=$template_values['sender_email'];
	}
	
	/* $sender_name=ucfirst($Renter_details->row()->first_name).' '.ucfirst($Renter_details->row()->last_name);
	$sender_email=$Renter_details->row()->email; */
	
	//add inbox from mail 
	$this->order_model->simple_insert(INBOX,array('sender_id'=>$sender_email,'user_id'=>$this->data['userDetails']->row()->email,'mailsubject'=>$template_values['news_subject'],'description'=>stripslashes($message)));
	$this->session->set_userdata('ContacterEmail',$user_details->row()->email);
	
	$email_values = array('mail_type'=>'html',
						'from_mail_id'=>$sender_email,
						'mail_name'=>$sender_name,
						'to_mail_id'=>$sender_email,
						'subject_message'=>$template_values['news_subject'],
						'body_messages'=>$message
						);
	//echo '<pre>'; print_r($email_values);			
	$email_send_to_common = $this->order_model->common_email_send($email_values);

					//$this->mail_owner_admin_booking($adminnewstemplateArr);
}


public function booking_conform_mail_host($paymentid){
	$PaymentSuccess = $this->order_model->get_all_details(PAYMENT,array('dealCodeNumber' => $paymentid));
					
	$condition = array('id'=>$PaymentSuccess->row()->sell_id);
	$Renter_details = $this->order_model->get_all_details(USERS,$condition);
	$condition = array('id'=>$PaymentSuccess->row()->sell_id);
	$Renter_email = $this->order_model->get_all_details(USERS,$condition);


	$condition3 = array('id'=>$PaymentSuccess->row()->user_id);
	$user_details = $this->order_model->get_all_details(USERS,$condition3);
					
					
	$condition1 = array('id'=>$PaymentSuccess->row()->product_id);
	$Rental_details = $this->order_model->get_all_details(PRODUCT,$condition1);
	$Contact_details = $this->order_model->get_all_details(RENTALENQUIRY,array( 'id' => $PaymentSuccess->row()->EnquiryId));
	$RentalPhoto = $this->order_model->get_all_details(PRODUCT_PHOTOS,array('product_id'=>$PaymentSuccess->row()->product_id));
	
	/* $total = $Renter_details->row()->price * $Contact_details->row()->numofdates; */
	$total = $Contact_details->row()->totalAmt-$Contact_details->row()->serviceFee;
		//---------------email to user---------------------------
	$newsid='34';
	$template_values=$this->order_model->get_newsletter_template_details($newsid);
	
	
	$subject = 'From: '.$this->config->item('email_title').' - '.$template_values['news_subject'];
	$proImages=base_url().PRODUCTPATH.$RentalPhoto->row()->product_image;
	$chkIn = date('d-m-y',strtotime($Contact_details->row()->checkin));
	$chkOut = date('d-m-y',strtotime($Contact_details->row()->checkout));
	$adminnewstemplateArr=array(
			'email_title'=>$this->config->item('email_title'),
			'logo'=>$this->data['logo'],
			'first_name'=>$user_details->row()->firstname,
			'last_name'=>$user_details->row()->lastname,
			'NoofGuest'=>$Contact_details->row()->NoofGuest,
			'numofdates'=>$Contact_details->row()->numofdates,
			'booking_status'=>$Contact_details->row()->booking_status,
			'user_email'=>$user_details->row()->email,
			'ph_no'=>$user_details->row()->phone_no,
			'Enquiry'=>$Contact_details->row()->Enquiry,
			'checkin'=>$chkIn,
			'checkout'=>$chkOut,
			'price'=>$Renter_details->row()->price,
			'amount'=>$total,
			'netamount'=>$Contact_details->row()->totalAmt,
			'noofnights'=>$Contact_details->row()->numofdates,
			'serviceFee'=>$Contact_details->row()->serviceFee,
			'renter_id'=>$PaymentSuccess->row()->sell_id,
			'prd_id'=>$PaymentSuccess->row()->product_id,
			'renter_fname'=>$Renter_details->row()->firstname,
			'renter_lname'=>$Renter_details->row()->lastname,
			'owner_email'=>$Renter_details->row()->email,
			'owner_phone'=>$Renter_details->row()->phone_no,
			'rental_name'=>$Rental_details->row()->product_title,
			'rental_image'=>$proImages);
	
	
	extract($adminnewstemplateArr);

	$header .="Content-Type: text/plain; charset=ISO-8859-1\r\n";
	
	$message .= '<!DOCTYPE HTML>
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width"/><body>';
	
	
	include('./newsletter/registeration'.$newsid.'.php');	
	
	$message .= '</body>
		</html>';
	
	if($template_values['sender_name']=='' && $template_values['sender_email']==''){
		$sender_email=$this->data['siteContactMail'];
		$sender_name=$this->data['siteTitle'];
	}else{
		$sender_name=$template_values['sender_name'];
		$sender_email=$template_values['sender_email'];
	}
	
	/* $sender_name=ucfirst($Renter_details->row()->first_name).' '.ucfirst($Renter_details->row()->last_name);
	$sender_email=$Renter_details->row()->email; */
	
	//add inbox from mail 
	$this->order_model->simple_insert(INBOX,array('sender_id'=>$sender_email,'user_id'=>$this->data['userDetails']->row()->email,'mailsubject'=>$template_values['news_subject'],'description'=>stripslashes($message)));
	$this->session->set_userdata('ContacterEmail',$user_details->row()->email);
	
	$email_values = array('mail_type'=>'html',
						'from_mail_id'=>$sender_email,
						'mail_name'=>$sender_name,
						'to_mail_id'=>$Renter_email->row()->email,
						'subject_message'=>$template_values['news_subject'],
						'body_messages'=>$message
						);
	//echo '<pre>'; print_r($email_values);				
	$email_send_to_common = $this->order_model->common_email_send($email_values);
					
					//$this->mail_owner_admin_booking($adminnewstemplateArr);
}



public function account_changes($userid){
	$userDetail = $this->user_model->get_all_details(USERS,array('id'=>$userid));
    $username = $userDetail->row()->firstname." ".$userDetail->row()->lastname;
	$useremail = $userDetail->row()->email;
		
	$newsid='36';
	$template_values=$this->user_model->get_newsletter_template_details($newsid);
	
	
	$subject = 'From: '.$this->config->item('email_title').' - '.$template_values['news_subject'];
	
	$adminnewstemplateArr=array(
			'email_title'=>$this->config->item('email_title'),
			'logo'=>$this->data['logo'],
			'username'=>$username
			);
	
	
	extract($adminnewstemplateArr);

	$header .="Content-Type: text/plain; charset=ISO-8859-1\r\n";
	
	$message .= '<!DOCTYPE HTML>
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width"/><body>';
	
	
	include('./newsletter/registeration'.$newsid.'.php');	
	
	$message .= '</body>
		</html>';
	
	if($template_values['sender_name']=='' && $template_values['sender_email']==''){
		$sender_email=$this->data['siteContactMail'];
		$sender_name=$this->data['siteTitle'];
	}else{
		$sender_name=$template_values['sender_name'];
		$sender_email=$template_values['sender_email'];
	}
	
	//add inbox from mail 
	//$this->session->set_userdata('ContacterEmail',$user_details->row()->email);
	
	$email_values = array('mail_type'=>'html',
						'from_mail_id'=>$sender_email,
						'mail_name'=>$sender_name,
						'to_mail_id'=>$useremail,
						'cc_mail_id'=>$sender_email,
						'subject_message'=>$template_values['news_subject'],
						'body_messages'=>$message
						);
	//echo '<pre>'; print_r($email_values);	die;			
	$email_send_to_common = $this->order_model->common_email_send($email_values);
}




public function find_friends_gmail() {
		$returnStr ['status_code'] = 1;
		/* $clientid = '827590910248-5ag82822248qvl3j1npqmks01h4mlr5b.apps.googleusercontent.com';
		$clientsecret = 'WO9Xz4V2n5Uu7te5Vu86FuwH';
		$redirecturi = 'https://www.staynest.com/site/user/find_friends_gmail'; 
		$maxresults = 100; */
		$clientid = '1082636820487-f01uj52t92djbuhlpt51j9q7tb65ulga.apps.googleusercontent.com';
		$clientsecret = 'o6oDSo3JH4undGqR27MFmlEe';
		$redirecturi = 'https://www.staynest.com/site/user/find_friends_gmail'; 
		$maxresults = 300;
		if($_GET["code"] == '')
		{
			$returnStr ['url'] = "https://accounts.google.com/o/oauth2/auth?client_id=$clientid&redirect_uri=$redirecturi&scope=https://www.google.com/m8/feeds/&response_type=code";
		}
		else
		{
			$authcode = $_GET["code"];
			$fields=array(
			'code'=> urlencode($authcode),
			'client_id'=> urlencode($clientid),
			'client_secret'=> urlencode($clientsecret),
			'redirect_uri'=> urlencode($redirecturi),
			'grant_type'=> urlencode('authorization_code') );

			$fields_string = '';
			foreach($fields as $key=>$value){ $fields_string .= $key.'='.$value.'&'; }
			$fields_string = rtrim($fields_string,'&');

			$ch = curl_init();//open connection
			curl_setopt($ch,CURLOPT_URL,'https://accounts.google.com/o/oauth2/token');
			curl_setopt($ch,CURLOPT_POST,5);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$result = curl_exec($ch);
			curl_close($ch);

			$response = json_decode($result);
			$accesstoken = $response->access_token;
			if( $accesstoken!='')
			$_SESSION['token']= $accesstoken;


			$xmlresponse = file_get_contents('https://www.google.com/m8/feeds/contacts/default/full?max-results='.$maxresults.'&oauth_token='. $_SESSION['token']);

			$xml = new SimpleXMLElement($xmlresponse);
			$xml->registerXPathNamespace('gd', 'http://schemas.google.com/g/2005');
			$contacts = $xml->xpath('//gd:email');

			$count = 0;
	
	
		
			foreach ($contacts as $title) {
				$newContacts[] = $title->attributes()->address;
				/* $this->send_invite_mail ( $title->attributes()->address );
				$count ++; */
			}
		
			sort($newContacts);
			$this->data ['gmail_contacts'] = $newContacts;
			$this->load->view ( 'site/user/gmail_list', $this->data );
			die;
			exit;
		}
		echo json_encode ( $returnStr );
	}
	
	
	public function reloadCaptcha()
	{
		$Capta1 = substr(str_shuffle("0123456789"), 0, 4);
		$Capta2 = substr(str_shuffle("0123456789"), 0, 4);
		echo $Capta1.'-'.$Capta2;
		
	}
	
	public function send_message()
	{
		$sender_id = $this->input->post ( 'sender_id' );
		$receiver_id = $this->input->post ( 'receiver_id' );
		$booking_id = $this->input->post ( 'booking_id' );
		$product_id = $this->input->post ( 'product_id' );
		$subject = $this->input->post ( 'subject' );
		$message = $this->input->post ('message');

		$host_msgread_status = $user_msgread_status ='Yes';

		$statusQry = $this->user_model->get_all_details ( MED_MESSAGE, array ('bookingNo' => $booking_id));
		$status = $statusQry->row()->status;

		$productData = $this->user_model->get_all_details(PRODUCT,array('id' => $product_id ));

		if($productData->row()->user_id==$this->checkLogin('U')){
			$user_msgread_status='No';
			$host_msgread_status = 'Yes';
		}else{
			$host_msgread_status = 'No';
			$user_msgread_status='Yes';
		}
		
		$dataArr = array(
			'productId' => $product_id ,
			'senderId' => $sender_id ,
			'receiverId' => $receiver_id ,
			'bookingNo' => $booking_id ,
			'subject' => $subject ,
			'message' => $message,
			'point'=>'0',
			'status' => $status,
			'host_msgread_status' => $host_msgread_status,
			'user_msgread_status' => $user_msgread_status
		);
		//print_r($dataArr);die;
		$this->db->insert(MED_MESSAGE, $dataArr);
		
	}
	


/**
 * ************************************************
 */
}