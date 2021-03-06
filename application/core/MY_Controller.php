<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
//error_reporting(-1);
error_reporting(E_ALL); ini_set('display_errors', 1);

/**
 * 
 * This controller contains the common functions
 * @author Teamtweaks 
 *
 */ 
date_default_timezone_set('Asia/Kolkata'); 
class MY_Controller extends CI_Controller {  


	public $privStatus;	
	public $data = array();
	function __construct()
    {
        parent::__construct();
		ob_start();
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url');
        $this->load->helper('text');
		$this->load->helper('currency_helper');
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');
		$this->load->library('session');
		$this->load->model(array('product_model','user_model','landing_model','admin_model'));
		
		/*
		 * Connecting Database
		 */
		 
		if($_SESSION['pageURL'] != '' && $this->uri->segment(3) != 'login_user'){
			$_SESSION['redirectCount'] += 1;
			if($_SESSION['redirectCount'] > 2 || strpos($_SESSION['pageURL'], 'logout') > 1)
			{ 
				$_SESSION['pageURL'] = '';
				$_SESSION['redirectCount'] = '';
			}
		}
		//echo $this->session->userdata('language_code');die;
		$this->load->database();
		$this->db->reconnect();
		$this->data['demoserverChk'] = $demoserverChk = strpos($this->input->server('DOCUMENT_ROOT'),'casperon/');

		/*
		 * Loading CMS Pages
		 */
		if ($_SESSION['cmsPages'] == ''){
			$cmsPages = $this->db->query('select * from '.CMS.' where `status`="Publish"');
			$_SESSION['cmsPages'] = $cmsPages->result_array();
		}
		$this->data['cmsPages'] = $_SESSION['cmsPages'];
		
		/*
		 * Loading Footer Cms
		 */
		
		if($this->session->userdata('language_code') == ''){

			/**Starts - Get Default language code**/
			$get_languages=$this->landing_model->get_all_details(LANGUAGES,array('default_lang'=>'Default','status'=>'Active'));
			$langCode=$get_languages->row()->lang_code;
			/**Ends - Get Default language code**/
			
			$this->data['cmsList'] = $this->db->query('select * from '.CMS.' where `lang_code`="'.$langCode.'" and `status`="Publish" and hidden_page="No" and section!="services"');
		$this->data['cmsListServices'] = $this->db->query('select * from '.CMS.' where `lang_code`="'.$langCode.'" and `status`="Publish" and hidden_page="No" and section="services"');
		}else{
			$this->data['cmsList'] = $this->db->query('select * from '.CMS.' where `lang_code`="'.$this->session->userdata('language_code').'" and `status`="Publish" and hidden_page="No" and section!="services"');
			$this->data['cmsListServices'] = $this->db->query('select * from '.CMS.' where `lang_code`="'.$this->session->userdata('language_code').'" and `status`="Publish" and hidden_page="No" and section="services"');
			

		}
		

		/*
		 * Loading active languages
		 */
		//if ($_SESSION['activeLgs'] == ''){
			$activeLgsList = $this->db->query('select * from '.LANGUAGES.' where `status`="Active" ORDER BY language_order ASC');
			$_SESSION['activeLgs'] = $activeLgsList->result_array();
		//}
		$this->data['activeLgs'] = $_SESSION['activeLgs'];
		
		$prefooter_query="SELECT * FROM ".PREFOOTER." WHERE status='Active'  ORDER BY id ASC LIMIT 3";
		$this->data['prefooter_results']=$this->db->query($prefooter_query);
		
		$this->data['google_map_api'] = $this->config->item ( 'google_map_api' );
		$this->appkey_col = $this->landing_model->get_social_media();
define('APPKEY',$this->appkey_col->row()->facebook_app_id);

$this->linkkey_col = $this->landing_model->get_social_media();
define('APPLinkKEY',$this->linkkey_col->row()->linkedin_app_key);

$this->googlekey_col = $this->landing_model->get_social_media();
define('GOOGLEKEY',$this->googlekey_col->row()->google_client_id);

$this->googlesecretkey_col = $this->landing_model->get_social_media();
define('GOOGLESECRETKEY',$this->googlesecretkey_col->row()->google_client_secret);

$this->watermark_col = $this->landing_model->get_social_media();
define('WATERMARKCOL',$this->watermark_col->row()->watermark);
		
		/*-Unread messages start-*/
		if($this->checkLogin('U') != '')
		{
			$this->data['unread_messages_count'] = $this->user_model->get_unread_messages_count($this->checkLogin('U'));
			$this->data['unread_messages_count_admin'] = $this->user_model->get_unread_messages_count_admin();
			$userId = $this->checkLogin('U');
		}
		/*-Unread messages end-*/
		
		//$this->data['admin_currency_symbol'] = $this->db->where(array('status'=>'Active','default_currency'=>'Yes'))->get(CURRENCY)->row()->currency_symbols;

		//$this->data['admin_currency_code'] = $this->db->where(array('status'=>'Active','default_currency'=>'Yes'))->get(CURRENCY)->row()->currency_type;

		$this->data['admin_currency_code'] = $this->db->where('id','1')->get(ADMIN)->row()->admin_currencyCode;
		//print_r($this->data['admin_currency_code']);

		$this->data['admin_currency_symbol'] = $this->db->where(array('currency_type'=>$this->data['admin_currency_code']))->get(CURRENCY)->row()->currency_symbols;


		/*
		 * Checking user language and loading user details
		 */
		if($this->checkLogin('U')!=''){
			$this->data['userDetails'] = $this->db->query('select * from '.USERS.' where `id`="'.$this->checkLogin('U').'"');
			$selectedLangCode = $this->session->userdata('language_code');
		}
		$config['SITENAME']=$this->config->item('meta_title');
		
		if (substr($uriMethod, 0,7) == 'display' || substr($uriMethod, 0,4) == 'view' || $uriMethod == '0'){
			$this->privStatus = '0';
		}else if (substr($uriMethod, 0,3) == 'add'){
			$this->privStatus = '1';
		}else if (substr($uriMethod, 0,4) == 'edit' || substr($uriMethod, 0,6) == 'insert' || substr($uriMethod, 0,6) == 'change'){
			$this->privStatus = '2';
		}else if (substr($uriMethod, 0,6) == 'delete'){
			$this->privStatus = '3';
		}else {
			$this->privStatus = '0';
		}
		$this->data['title'] = $this->config->item('meta_title');
		$this->data['heading'] = '';
		$this->data['flash_data'] = $this->session->flashdata('sErrMSG');
		$this->data['flash_data_type'] = $this->session->flashdata('sErrMSGType');
		$this->data['adminPrevArr'] = $this->config->item('adminPrev');
 		$this->data['adminEmail'] = $this->config->item('email');	
		$this->data['privileges'] = $this->session->userdata('fc_session_admin_privileges');
		$this->data['subAdminMail'] = $this->session->userdata('fc_session_admin_email');	
		$this->data['admin_rep_code'] = $this->session->userdata('fc_session_admin_rep_code');
		$this->data['loginID'] = $this->session->userdata('fc_session_user_id');				
    	$this->data['allPrev'] = '0';
    	$this->data['logo'] = $this->config->item('logo_image');
		$this->data['logo_img'] = $this->config->item('home_logo_image');
    	$this->data['fevicon'] = $this->config->item('fevicon_image');
		$this->data['watermark'] = $this->config->item('watermark');
    	$this->data['footer'] = $this->config->item('footer_content');
    	$this->data['siteContactMail'] = $this->config->item('site_contact_mail');
		$this->data['WebsiteTitle'] = $this->config->item('email_title');
    	$this->data['siteTitle'] = $this->config->item('email_title');
    	$this->data['meta_title'] = $this->config->item('meta_title');
    	$this->data['meta_keyword'] = $this->config->item('meta_keyword');
    	$this->data['meta_description'] = $this->config->item('meta_description');
    	$this->data['giftcard_status'] = $this->config->item('giftcard_status');
		$this->data['sidebar_id'] = $this->session->userdata('session_sidebar_id');
		if ($this->session->userdata('fc_session_admin_name') == $this->config->item('admin_name')){
			$this->data['allPrev'] = '1';
		}
		$this->data['paypal_ipn_settings'] = unserialize($this->config->item('payment_0'));
		$this->data['paypal_credit_card_settings'] = unserialize($this->config->item('payment_1'));
		$this->data['authorize_net_settings'] = unserialize($this->config->item('payment_2'));
		
		/* if($this->session->userdata('currency_type') == ''){
			require_once('geoplugin.class.php');
$geoplugin = new geoPlugin();
$geoplugin->locate();
$geoplugin->currencyCode; 
$geoplugin->currencySymbol; 
			
			$currency_values = $this->product_model->get_all_details(CURRENCY,array('status'=>'Active'));
			if($currency_values->num_rows() > 0){
				foreach($currency_values->result() as $currency_v){

					if($geoplugin->currencyCode==$currency_v->currency_type)
					{
						$this->session->set_userdata('currency_type',$currency_v->currency_type) ;
					$this->session->set_userdata('currency_s',$currency_v->currency_symbols) ; 
					$this->session->set_userdata('currency_r',$currency_v->currency_rate) ;
					}
					
				}
			}
			else{
				$currency_values = $this->product_model->get_all_details(CURRENCY,array('currency_type'=>'USD'));
				foreach($currency_values->result() as $currency_v){
					$this->session->set_userdata('currency_type',$currency_v->currency_type) ;
					$this->session->set_userdata('currency_s',$currency_v->currency_symbols) ; 
					$this->session->set_userdata('currency_r',$currency_v->currency_rate) ;
				}
			}
			$this->data['currencySymbol'] = $this->session->userdata('currency_s');
			$this->data['currencyType'] = $this->session->userdata('currency_type');
		}else{
		 	$this->data['currencySymbol'] = $this->session->userdata('currency_s');
		 	$this->data['currencyType'] = $this->session->userdata('currency_type');
		}
		
		$this->data['currency_setup'] = $this->product_model->get_all_details(CURRENCY,array('status'=>'Active'),''); */
		/**********Curreny Settings end*********/
		
		/***************USD Default Curreny********/
		
		if($this->session->userdata('currency_type') == ''){
			$currency_values = $this->product_model->get_all_details(CURRENCY,array('status'=>'Active','default_currency'=>'Yes'));
			if($currency_values->num_rows() == 1){
				foreach($currency_values->result() as $currency_v){
					$this->session->set_userdata('currency_type',$currency_v->currency_type) ;
					$this->session->set_userdata('currency_s',$currency_v->currency_symbols) ; 
					$this->session->set_userdata('currency_r',$currency_v->currency_rate) ;
				}
			}
			else{
				$currency_values = $this->product_model->get_all_details(CURRENCY,array('currency_type'=>'USD'));
				foreach($currency_values->result() as $currency_v){
					$this->session->set_userdata('currency_type',$currency_v->currency_type) ;
					$this->session->set_userdata('currency_s',$currency_v->currency_symbols) ; 
					$this->session->set_userdata('currency_r',$currency_v->currency_rate) ;
				}
			}
			$this->data['currencySymbol'] = $this->session->userdata('currency_s');
			$this->data['currencyType'] = $this->session->userdata('currency_type');
		}else{
		 	$this->data['currencySymbol'] = $this->session->userdata('currency_s');
		 	$this->data['currencyType'] = $this->session->userdata('currency_type');
		}
		
		$this->data['currency_setup'] = $this->product_model->get_all_details(CURRENCY,array('status'=>'Active'),'');
		
		
		
		/***************USD Default Curreny********/

		$this->data['datestring'] = "%Y-%m-%d %h:%i:%s";
		if($this->checkLogin('U')!=''){
 			$this->data['common_user_id'] = $this->checkLogin('U'); 
		}elseif($this->checkLogin('T')!=''){
 			$this->data['common_user_id'] = $this->checkLogin('T'); 
		}else{
			$temp_id = substr(number_format(time() * rand(),0,'',''),0,6);
			$this->session->set_userdata('fc_session_temp_id',$temp_id);
			$this->data['common_user_id'] = $temp_id;
		}
		$this->data['emailArr'] = $this->config->item('emailArr');
		$this->data['notyArr'] = $this->config->item('notyArr');
		
		$this->load->model('product_model');

		/*
		 * Like button texts
		 */
		define(LIKE_BUTTON, $this->config->item('like_text'));
		define(LIKED_BUTTON, $this->config->item('liked_text'));
		define(UNLIKE_BUTTON, $this->config->item('unlike_text'));
		
		if($_SESSION['authUrl'] == ''){
			//header( 'Location:base_url()');
		}
		/*Refereral Start */
		
		if($this->input->get('ref') != '')
		{
			//echo $this->input->get('ref');	
			$referenceName = $this->input->get('ref');
			$this->session->set_userdata('referenceName',$referenceName);
		}
		
		/*Refereral End */
		
		/* Multilanguage start*/
		$defaultLanguage = 'en';
		if($this->uri->segment('1') != 'admin')
		{	
			if($this->session->userdata('language_code')==''){ 
			$CountryArr = $this->product_model->get_all_details(LANGUAGES,array('status'=>'Active','default_lang'=>'Default'));
				if($CountryArr->row()->status=='Active'){
					$this->session->set_userdata('language_code',$CountryArr->row()->lang_code);
					$defaultLanguage = $CountryArr->row()->lang_code;
				}else{
					$this->session->set_userdata('language_code','en');
					$defaultLanguage = 'en';
				}
			}
			$selectedLanguage = $this->session->userdata('language_code');	
			($selectedLanguage != '')? $selectedLanguage = $selectedLanguage : $selectedLanguage = 'en';

			$filePath = APPPATH."language/".$selectedLanguage."/".$selectedLanguage."_lang.php";
			if($selectedLanguage != '')
			{
			
					if(!(is_file($filePath)))
					{	
						$this->lang->load($defaultLanguage, $defaultLanguage);
					}
					else
					{
						$this->lang->load($selectedLanguage, $selectedLanguage);
					}
				
			}
			else
			{
				$this->lang->load($defaultLanguage, $defaultLanguage);
			}
		}		
		/* Multilanguage end*/
		
		/* experience module data - check experience module enabled or not */
	    $exprienceModuleExist = $this->landing_model->checkModuleStatus('experience');
	    //print_r($exprienceModuleExist->num_rows());exit;
	    $this->data['experienceExistCount'] = $exprienceModuleExist->num_rows();

	    /* experience module ends  */
		
		/* Curreny Check  */
		
		$admin_currencyCode=$this->session->userdata('fc_session_admin_currencyCode');
		$session_admin_mode=$this->session->userdata('session_admin_mode');
		//echo $this->checkLogin('A');
			
		if(($this->checkLogin('A')!="") && ($session_admin_mode==ADMIN)) {
			if($admin_currencyCode==''){
				$this->setErrorMessage('error','Please choose currency and update');	
			}
		}			
		
		$controller = $this->router->fetch_class();
		$this->data['current_controller']=$controller;
		
		/* Curreny Check  */

		/*Privilages Check and Set*/ 
		$name = $this->session->userdata('fc_session_admin_name');
		$email = $this->session->userdata('fc_session_admin_email');
		if($name != '' && $email != ''){
		$mode = SUBADMIN;
			
			if ($name == $this->config->item('admin_name')){
				$mode = ADMIN;
			}
			$condition = array('admin_name' => $name, 'email' => $email, 'is_verified' => 'Yes', 'status' => 'Active');
			$query = $this->admin_model->get_all_details($mode,$condition);

			if ($query->num_rows() == 1)
			{
				
				$priv = unserialize($query->row()->privileges);
				if($mode==ADMIN) {
				$admindata = array(
								
								'fc_session_admin_privileges' => $priv
								
							);
				} else {
				  $admindata = array(
								
								'fc_session_admin_privileges' => $priv
							
							);  
				}
				
				$this->session->set_userdata($admindata);
			}
		}

		/*End Privilages Check and Set*/
    }
    
    /**
     * 
     * This function return the session value based on param
     * @param $type
     */
	
    public function checkLogin($type=''){
    	if ($type == 'A'){
    		return $this->session->userdata('fc_session_admin_id');
    	}else if ($type == 'N'){
    		return $this->session->userdata('fc_session_admin_name');
    	}else if ($type == 'M'){
    		return $this->session->userdata('fc_session_admin_email');
    	}else if ($type == 'P'){
    		return $this->session->userdata('fc_session_admin_privileges');
    	}else if ($type == 'U'){
    		return $this->session->userdata('fc_session_user_id');
    	}else if ($type == 'T'){
    		return $this->session->userdata('fc_session_temp_id');
			
    	}
    }
    
    /**
     * 
     * This function set the error message and type in session
     * @param string $type
     * @param string $msg
     */
    public function setErrorMessage($type='',$msg=''){
    	($type == 'success') ? $msgVal = 'message-green' : $msgVal = 'message-red';
		$this->session->set_flashdata('sErrMSGType', $msgVal);
		$this->session->set_flashdata('sErrMSG', $msg);
    }
   /**
    * 
    * This function check the admin privileges
    * @param String $name	->	Management Name
    * @param Integer $right	->	0 for view, 1 for add, 2 for edit, 3 delete
    */ 
   public function checkPrivileges($name='',$right=''){
   		$prev = '0';
		$privileges = $this->session->userdata('fc_session_admin_privileges');
		extract($privileges);
		$userName =  $this->session->userdata('fc_session_admin_name');
		$adminName = $this->config->item('admin_name');
		if ($userName == $adminName){
			$prev = '1';
		}
		if (isset(${$name}) && is_array(${$name}) && in_array($right, ${$name})){
			$prev = '1';
		}
		if ($prev == '1'){
			return TRUE;
		}else {
			return FALSE;
		}
   }
   
   /**
    * 
    * Generate random string
    * @param Integer $length
    */
   public function get_rand_str($length='6'){
		return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
   }
   
   /**
    * 
    * Unsetting array element
    * @param Array $productImage
    * @param Integer $position
    */
	public function setPictureProducts($productImage,$position){
		unset($productImage[$position]);
		return $productImage;
	}
	
	/**
	 * 
	 * Resize the image
	 * @param int target_width
	 * @param int target_height
	 * @param string image_name
	 * @param string target_path
	 */
	 public function imageResizeWithSpace($box_w,$box_h,$userImage,$savepath){
			$thumb_file = $savepath.$userImage;
			
			list($w, $h, $type, $attr) = getimagesize($thumb_file);
			
				//print_r($box_w);die;
				$size=getimagesize($thumb_file);
			    switch($size["mime"]){
			        case "image/jpeg":
            			$img = imagecreatefromjpeg($thumb_file); //jpeg file
			        break;
			        case "image/gif":
            			$img = imagecreatefromgif($thumb_file); //gif file
				      break;
			      case "image/png":
			          $img = imagecreatefrompng($thumb_file); //png file
			      break;
				
				  default:
				        $im=false;
				    break;
				}
				
			$new = imagecreatetruecolor($box_w, $box_h);
			if($new === false) {
				//creation failed -- probably not enough memory
				return null;
			}
			$whiteColorIndex = imagecolorexact($new,255,255,255);
			$whiteColor = imagecolorsforindex($new,$whiteColorIndex);
			imagecolortransparent($new,$whiteColor);
		
			$fill = imagecolorallocate($new, 064, 064, 064);
			imagefill($new, 0, 0, $fill);
		
			//compute resize ratio
			$hratio = $box_h / imagesy($img);
			$wratio = $box_w / imagesx($img);
			$ratio = min($hratio, $wratio);
		
			if($ratio > 1.0)
				$ratio = 1.0;
		
			//compute sizes
			$sy = floor(imagesy($img) * $ratio);
			$sx = floor(imagesx($img) * $ratio);
		
			$m_y = floor(($box_h - $sy) / 2);
			$m_x = floor(($box_w - $sx) / 2);
		
			if(!imagecopyresampled($new, $img,
				$m_x, $m_y, //dest x, y (margins)
				0, 0, //src x, y (0,0 means top left)
				$sx, $sy,//dest w, h (resample to this size (computed above)
				imagesx($img), imagesy($img)) //src w, h (the full size of the original)
			) {
				//copy failed
				imagedestroy($new);
				return null;
				
			}
			imagedestroy($i);
			imagejpeg($new, $thumb_file, 90);
			
	}
	public function imageResizeWithSpace1($box_w,$box_h,$userImage,$savepath){
			
			
			
			 $thumb_file = $savepath.$userImage;
			
			 $dist_file = $savepath.'/thumb/'.$userImage;
			 
			 
			 
			$config['source_image']    = $dist_file;
			$config['wm_text'] = 'Rentals';
			$config['wm_type'] = 'text';
			$config['wm_font_path'] = './GILSANUB.TTF';
			$config['wm_font_size']    = '22';
			$config['wm_font_color'] = 'e9b9b9';
			$config['wm_vrt_alignment'] = 'middle';
			$config['wm_hor_alignment'] = 'center';
			$config['wm_padding'] = '0';
			$this->image_lib->initialize($config); 
			$this->image_lib->watermark();
			 
			 
			 
			 
			 
			 
			 
			 
				
			list($w, $h, $type, $attr) = getimagesize($thumb_file);
				
				$size=getimagesize($thumb_file);
			    switch($size["mime"]){
			        case "image/jpeg":
            			$img = imagecreatefromjpeg($thumb_file); //jpeg file
			        break;
			        case "image/gif":
            			$img = imagecreatefromgif($thumb_file); //gif file
				      break;
			      case "image/png":
			          $img = imagecreatefrompng($thumb_file); //png file
			      break;
				
				  default:
				        $im=false;
				    break;
				}
				
			$new = imagecreatetruecolor($box_w, $box_h);
			if($new === false) {
				//creation failed -- probably not enough memory
				return null;
			}
		
		
			$fill = imagecolorallocate($new, 255, 255, 255);
			imagefill($new, 0, 0, $fill);
		
			//compute resize ratio
			$hratio = $box_h / imagesy($img);
			$wratio = $box_w / imagesx($img);
			$ratio = min($hratio, $wratio);
		
			if($ratio > 1.0)
				$ratio = 1.0;
		
			//compute sizes
			$sy = floor(imagesy($img) * $ratio);
			$sx = floor(imagesx($img) * $ratio);
		
			$m_y = floor(($box_h - $sy) / 2);
			$m_x = floor(($box_w - $sx) / 2);
		
			if(!imagecopyresampled($new, $img,
				$m_x, $m_y, //dest x, y (margins)
				0, 0, //src x, y (0,0 means top left)
				$sx, $sy,//dest w, h (resample to this size (computed above)
				imagesx($img), imagesy($img)) //src w, h (the full size of the original)
			) {
				//copy failed
				imagedestroy($new);
				return null;
				
			}
			imagedestroy($i);
			imagejpeg($new, $dist_file, 99);
	}
	/**
	 * 
	 * Resize the image
	 * @param int target_width
	 * @param int target_height
	 * @param string image_name
	 * @param string target_path
	 */
	public function imageResizeWithSpaceold($box_w,$box_h,$userImage,$savepath){
			
			$thumb_file = $savepath.$userImage;
				
			list($w, $h, $type, $attr) = getimagesize($thumb_file);
				
				$size=getimagesize($thumb_file);
			    switch($size["mime"]){
			        case "image/jpeg":
            			$img = imagecreatefromjpeg($thumb_file); //jpeg file
			        break;
			        case "image/gif":
            			$img = imagecreatefromgif($thumb_file); //gif file
				      break;
			      case "image/png":
			          $img = imagecreatefrompng($thumb_file); //png file
			      break;
				
				  default:
				        $im=false;
				    break;
				}
				
			$new = imagecreatetruecolor($box_w, $box_h);
			if($new === false) {
				//creation failed -- probably not enough memory
				return null;
			}
		
		
			$fill = imagecolorallocate($new, 255, 255, 255);
			imagefill($new, 0, 0, $fill);
		
			//compute resize ratio
			$hratio = $box_h / imagesy($img);
			$wratio = $box_w / imagesx($img);
			$ratio = min($hratio, $wratio);
		
			if($ratio > 1.0)
				$ratio = 1.0;
		
			//compute sizes
			$sy = floor(imagesy($img) * $ratio);
			$sx = floor(imagesx($img) * $ratio);
		
			$m_y = floor(($box_h - $sy) / 2);
			$m_x = floor(($box_w - $sx) / 2);
		
			if(!imagecopyresampled($new, $img,
				$m_x, $m_y, //dest x, y (margins)
				0, 0, //src x, y (0,0 means top left)
				$sx, $sy,//dest w, h (resample to this size (computed above)
				imagesx($img), imagesy($img)) //src w, h (the full size of the original)
			) {
				//copy failed
				imagedestroy($new);
				return null;
				
			}
			imagedestroy($i);
			imagejpeg($new, $thumb_file, 99);
			
	}
	
	public function watermarkimages($uploaddir,$image_name){
			$masterURL =$uploaddir.$image_name;
			header('content-type: image/jpeg');
			//$path = base_url().'images/logo'.$this->config->item('watermark');
			$watermark = imagecreatefrompng('images/watermark3.png');
			$watermark_width = imagesx($watermark);
			$watermark_height = imagesy($watermark);
			$image = imagecreatetruecolor($watermark_width, $watermark_height);
			$image = imagecreatefromjpeg($masterURL);
			$size = getimagesize($masterURL);
			$dest_x = $size[0] - $watermark_width - 5;
			$dest_y = $size[1] - $watermark_height - 500;
			imagecopymerge($image, $watermark, $dest_x, $dest_y,0, 0, $watermark_width, $watermark_height,20);
			imagejpeg($image, $masterURL);
	}
	
	
	/**
	 * 
	 * Resize the image
	 * @param int target_width
	 * @param int target_height
	 * @param string image_name
	 * @param string target_path
	 */
	public function imageResizeWithSpaceCity($box_w,$box_h,$userImage,$savepath){
			
			 $thumb_file = $savepath.$userImage;
			
			 $dist_file = $savepath.'/thumb/'.$userImage;
				
			list($w, $h, $type, $attr) = getimagesize($thumb_file);
				
				$size=getimagesize($thumb_file);
			    switch($size["mime"]){
			        case "image/jpeg":
            			$img = imagecreatefromjpeg($thumb_file); //jpeg file
			        break;
			        case "image/gif":
            			$img = imagecreatefromgif($thumb_file); //gif file
				      break;
			      case "image/png":
			          $img = imagecreatefrompng($thumb_file); //png file
			      break;
				
				  default:
				        $im=false;
				    break;
				}
				
			$new = imagecreatetruecolor($box_w, $box_h);
			if($new === false) {
				//creation failed -- probably not enough memory
				return null;
			}
		
		
			$fill = imagecolorallocate($new, 000, 000, 000);
			imagefill($new, 0, 0, $fill);
		
			//compute resize ratio
			$hratio = $box_h / imagesy($img);
			$wratio = $box_w / imagesx($img);
			$ratio = min($hratio, $wratio);
		
			if($ratio > 1.0)
				$ratio = 1.0;
		
			//compute sizes
			$sy = floor(imagesy($img) * $ratio);
			$sx = floor(imagesx($img) * $ratio);
		
			$m_y = floor(($box_h - $sy) / 2);
			$m_x = floor(($box_w - $sx) / 2);
		
			if(!imagecopyresampled($new, $img,
				$m_x, $m_y, //dest x, y (margins)
				0, 0, //src x, y (0,0 means top left)
				$sx, $sy,//dest w, h (resample to this size (computed above)
				imagesx($img), imagesy($img)) //src w, h (the full size of the original)
			) {
				//copy failed
				imagedestroy($new);
				return null;
				
			}
			imagedestroy($i);
			imagejpeg($new, $dist_file, 99);
			
	}
	
	public function compress($source, $destination, $quality) {

		$info = getimagesize($source);

		if ($info['mime'] == 'image/jpeg') 
			$image = imagecreatefromjpeg($source);

		elseif ($info['mime'] == 'image/gif') 
			$image = imagecreatefromgif($source);

		elseif ($info['mime'] == 'image/png') 
			$image = imagecreatefrompng($source);

		imagejpeg($image, $destination, $quality);

		return $destination;
	}

	public function ImageCompress($source_url, $destination_url, $quality=50){
		$info = getimagesize($source_url);
		$savePath = $source_url;
		
		if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($savePath);
		elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($savePath);
		elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($savePath);
		imagejpeg($image, $savePath, $quality);
	}


	

	public function getImageShape($width, $height, $target_file){
		list($w, $h) = getimagesize($target_file);
		if($w==$width && $h==$height){
			$option="exact";
		}else if($w>$h){
			$option="exact";
			//$option="landscape";
		}else if($w<$h){
			$option="exact";
			//$option="portrait";
		}else{
			$option="exact";
			//$option="crop";
		}
		return $option;
	}
	
	/*--Push Notification for IOS--*/
	
	public function push_notification($deviceId,$message){
		/* echo $deviceId;
		var_dump($message); */
	  	#$deviceId = "488f26fbf22b6af5023b0f4f7787e7ec5cd807cb69fb97f1308daafcd939d05a";
	  	#$deviceId = "e97186519ec3b3ef6733226b4401f61a8913c29db7e9ad53cc90c804dfec0f0c";
	  	#$deviceId = "6b1763dfa8393319c851800288f1cd1251793ecd8053012a0818d44c802a1961";
		
		
	  	#$deviceId = "b275d455774145f64ed3bdbf300b436e5262f8e2caa77daf9c9ff0e42fa2e179";
	  	$deviceId = "d71c5c42cf8bee5e4b56d401b342094c15d77303afd375190077ecbf091ea64a";
		
		
	   	$message = array('message'=>"Test message for Renters succeeded");
	   	$this->load->library('apns');
	   	$this->apns->send_push_message($deviceId,$message);
	}
	
	/*--Push Notification for IOS--*/

	/* override number_format function  starts */
	/*-- this for avoiding the round off calculation of currency based amount calculations --*/
	public function number_format($number, $precision = 2, $separator = '.')
	{
	    $numberParts = explode($separator, $number);
	    $response = $numberParts[0];
	    if(count($numberParts)>1){
	        $response .= $separator;
	        $response .= substr($numberParts[1], 0, $precision);
	    }
	    return $response;
	}
	/* override number_format function ends */
	
	/*nan--added*/
	public function get_review_exp($id){
		$data=$this->product_model->get_avg_review_experience($id);
		return $data;
	}

}
