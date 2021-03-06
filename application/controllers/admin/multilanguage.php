<?php
class Multilanguage extends MY_Controller {

   function __construct()
    {
		parent::__construct();		
		$this->load->helper('file');
		$this->load->helper('language');
		$this->load->helper(array('cookie','date','form'));
		$this->load->library(array('encrypt','form_validation'));		
		$this->load->model('admin_model');
		$this->load->model('multilanguage_model');
		$this->load->helper('directory');
		if ($this->checkPrivileges('Language',$this->privStatus) == FALSE){
			redirect('admin');				
		}
		
    }
    
    function index()
    {        
     	$this->display_language_list();		
    }
	
	function display_language_list()
	{
	
		if ($this->checkLogin('A') == ''){
			redirect('admin');
		}else {						
				$this->data['heading'] = 'Manage Language';
				$this->data['admin_settings'] = $result = $this->admin_model->getAdminSettings();	
				$this->data['language_list'] = $result = $this->multilanguage_model->get_language_list();				
				$this->load->view('admin/multilanguage/language_list',$this->data);
		}
	}
	
	/**To Update Language Order**/
	function UpdateLangOrder(){
		$languageId = $this->input->post('catID');
		$order = $this->input->post('title');
		$updateFields = $this->input->post('chk');
		if (!empty($_POST)){
		$this->multilanguage_model->update_details(LANGUAGES,array($updateFields=>$order),array('id'=>$languageId));
			echo "succ";
		}else{
			echo "fail";
		}
	}
	
	
	
	function edit_language()
	{		
		if ($this->checkLogin('A') == ''){
			redirect('admin');
		}else {	
		
		$file_name_prefix = 'file';
		$file_number = $this->uri->segment(5);		
				
		$selectedLanguage = $this->uri->segment('4');		
		$languagDirectory = APPPATH.'language/'.$selectedLanguage;
		//echo $languagDirectory;
		
		
		
		$get_english_lang_count = directory_map(APPPATH."language/en/");
		//echo "<pre>";print_r($get_english_lang_count);die;
		$filePath = APPPATH."language/".$selectedLanguage."/".$file_name_prefix.$file_number."_lang.php";	
		if(!is_dir($languagDirectory))
		{
		
			mkdir($languagDirectory,0777); 
			
			if(!is_file($filePath))
			{	
				
				mkdir($languagDirectory,0777); 
				file_put_contents($filePath,'');
			}
		}
		//echo $filePath;die;
		// $this->lang->load('file1', $selectedLanguage);
		if(is_file($filePath))
		{		
			$this->lang->load($file_name_prefix.$file_number, $selectedLanguage);		
		}
	 	 
		//$filePath = APPPATH."language/en/".$file_name_prefix.$file_number."_lang.php";		
		$filePath = APPPATH."language/en/".$file_name_prefix.$file_number."_lang.php";		
		$fileValues = file_get_contents($filePath);		 
		//echo "<pre>";print_r($fileValues);die;
		/********************************** Key value explode start *************************************/
		$fileKeyValues_explode1 = explode("\$lang['", $fileValues);	
		$language_file_keys = array();
		foreach($fileKeyValues_explode1 as $fileKeyValues2)
		{
			$fileKeyValues_explode2 = explode("']", $fileKeyValues2);
			$language_file_keys[] = $fileKeyValues_explode2[0];
		}
		/********************************** Key value explode end *************************************/
		
		/**********************************  value explode start *************************************/
		$fileValues_explode1 = explode("']='", $fileValues);	
		$language_file_values = array();
		
		//echo "<pre>";print_r($fileValues_explode1);die;
		foreach($fileValues_explode1 as $fileValues2)
		{
			$fileValues_explode2 = explode("';", $fileValues2);		 
			$language_file_values[] = $fileValues_explode2[0]; 		
		}
		/**********************************  value explode end *************************************/	
		
	//echo count($get_english_lang_count);die;
		
				
		//echo "<pre>";print_r($language_file_keys);die;	
		$this->data['file_key_values'] = $language_file_keys;
		$this->data['file_lang_values'] = $language_file_values;
		$this->data['selectedLanguage'] = $selectedLanguage;
		$this->data['heading'] = 'Edit Language';
		$this->data['file_name_prefix'] = $file_name_prefix;
		$this->data['get_total_files'] = count($get_english_lang_count);
		$this->data['current_file_no'] = $file_number;
		$this->data['admin_settings'] = $result = $this->admin_model->getAdminSettings();	
		$this->load->view('admin/multilanguage/language_edit',$this->data);	
		
		}
	}
	
	function languageAddEditValues()
	{					
				 
			$getLanguageKeyDetails = $this->input->post('languageKeys');
			$getLanguageContentDetails = $this->input->post('language_vals');
			$selectedLanguage = $this->input->post('selectedLanguage');
			$file_name_prefix = $this->input->post('file_name_prefix');
			$current_file_no = $this->input->post('current_file_no');
			// echo "<pre>";print_r($getLanguageContentDetails);die;
			/* file write start*/
			$loopItem = 0;
			$config = '<?php';
			foreach($getLanguageKeyDetails as $key_val)
			{
				$language_file_values = addslashes($getLanguageContentDetails[$loopItem]);
				$config .= "\n\$lang['$key_val']='$language_file_values'; ";
				$loopItem = $loopItem+1;
			}
			
			$config .= ' ?>';
			
			$languagDirectory = APPPATH."language/".$selectedLanguage;
			if(!is_dir($languagDirectory))
			{
				mkdir($languagDirectory,0777); 
			}
			
			//$filePath = APPPATH."language/".$selectedLanguage."/".$selectedLanguage."_lang.php";
			$filePath = APPPATH."language/".$selectedLanguage."/".$file_name_prefix.$current_file_no."_lang.php"; 
			file_put_contents($filePath, $config);
			//redirect('admin/multilanguage/display_language_list');
			//error_reporting(-1);
			$get_folder_files = directory_map(APPPATH."language/".$selectedLanguage);
			
			
			
			/******** Merge all sub files into language single language file eveerytime update start ****************/
			
			
			$filePath = APPPATH."language/".$selectedLanguage."/".$selectedLanguage."_lang.php";
			
			if(!is_file($filePath))
			{	
				
				mkdir($languagDirectory,0777); 
				file_put_contents($filePath,'');					
			}				
		 	file_put_contents($filePath,'');	
			
			foreach($get_folder_files as $file_name_dtls)
			{
				if($file_name_dtls != $selectedLanguage."_lang.php")
				{				
					 $open_file_to_append = APPPATH."language/".$selectedLanguage."/".$file_name_dtls; 
					$handle = fopen($filePath, 'a');
					$data = file_get_contents($open_file_to_append);
					fwrite($handle, $data);
				}
			}
			/******** Merge all sub files into language single language file eveerytime update end ****************/
			
						
			/******** Merge all english sub files into english language single language file eveerytime update start ****************/
			$get_en_folder_files = directory_map(APPPATH."language/en");
			
			$filePath = APPPATH."language/en/en_lang.php";
			
			if(!is_file($filePath))
			{	
				
				mkdir($languagDirectory,0777); 
				file_put_contents($filePath,'');					
			}				
		 	file_put_contents($filePath,'');	
			//echo "<pre>";print_r($get_en_folder_files);die;
			foreach($get_en_folder_files as $file_name_dtls)
			{
				if($file_name_dtls != "en_lang.php")
				{				
					$open_file_to_append = APPPATH."language/en/".$file_name_dtls; 
					$handle = fopen($filePath, 'a');
					$data = file_get_contents($open_file_to_append);
					fwrite($handle, $data);
				}
			}
			/******** Merge all sub files into language single language file eveerytime update end ****************/
					
			redirect('admin/multilanguage/edit_language/'.$selectedLanguage."/".$current_file_no);
	}
	
	function delete_language(){
		$languageId = $this->uri->segment('4');
		$delete_language = $this->multilanguage_model->delete_language($languageId);
		$this->setErrorMessage('success'," Language deleted changed successfully");
		redirect('admin/multilanguage/display_language_list');			
	}
	
	function change_multi_language_details()
	{
		$statusMode = $this->input->post('statusMode');
		$checkbox_id = $this->input->post('checkbox_id');
		
		if($statusMode != '' && !empty($checkbox_id))
		{
			$change_language_status = $this->multilanguage_model->change_language_status($statusMode,$checkbox_id);	
			$this->setErrorMessage('success'," Language settings changed successfully");
			redirect('admin/multilanguage/display_language_list');				
		}
		else
		{
			redirect('admin');
		}
	
	}
	
	function change_language_status()
	{
		$current_status = $this->uri->segment('4');
		$languageId = $this->uri->segment('5');
		
		if($current_status != '' && $languageId != ''){
			$change_language_details = $this->multilanguage_model->change_language_details($current_status,$languageId);	
			$this->setErrorMessage('success'," Language settings changed successfully");
			redirect('admin/multilanguage/display_language_list');	
			
		}else {
			redirect('admin');
		}
	}
	
	public function add_new_lg(){
		if ($this->checkLogin('A')==''){
			show_404();
		}else {
			$this->data['heading'] = 'Add New Language';
			$this->load->view('admin/multilanguage/add_new_lg',$this->data);
		}
	}
	
	public function add_lg_process(){
		if ($this->checkLogin('A') == ''){
			show_404();
		}else {
			$lname = $this->input->post('name');
			$lcode = $this->input->post('lang_code');
			$lorder = $this->input->post('language_order');
			$duplicateName = $this->multilanguage_model->get_all_details(LANGUAGES,array('name'=>$lname));
			$duplicateLangOrder = $this->multilanguage_model->get_all_details(LANGUAGES,array('language_order'=>$lorder));
			
			if ($duplicateLangOrder->num_rows()>0){
				$this->setErrorMessage('error','Language order already exists..!');
				echo "<script>window.history.go(-1);</script>";exit();
			}else if ($duplicateName->num_rows()>0){
				$this->setErrorMessage('error','Language name already exists');
				echo "<script>window.history.go(-1);</script>";exit();
			}else {
				$duplicateCode = $this->multilanguage_model->get_all_details(LANGUAGES,array('lang_code'=>$lcode));
				if ($duplicateCode->num_rows()>0){
					$this->setErrorMessage('error','Language code already exists');
					echo "<script>window.history.go(-1);</script>";exit();
				}else {
					$this->multilanguage_model->commonInsertUpdate(LANGUAGES,'insert',array(),array());
					$this->setErrorMessage('success','Language added successfully');
					redirect('admin/multilanguage/display_language_list');
				}
			}
		}
	}
	
	
	public function default_language($id) {
	
	 $this->multilanguage_model->update_details(LANGUAGES,array('default_lang'=>""),array('status'=>'Active'));
	 $this->multilanguage_model->update_details(LANGUAGES,array('default_lang'=>'Default'),array('id'=>$id));

	 $this->setErrorMessage('success','Default Language Modified successfully');
	 redirect('admin/multilanguage/display_language_list');
	
	}
	
	public function display_user_language() {
		if ($this->checkLogin('A') == ''){
			redirect('admin');
		}else {	
			$this->data['heading'] = 'Manage User Language';
			$this->data['language_list'] = $result = $this->multilanguage_model->get_all_details(LANGUAGES_KNOWN, array());					
			$this->load->view('admin/multilanguage/display_user_language',$this->data);
		}
	}
	
	public function edit_user_language() {
		if ($this->checkLogin('A') == ''){
			redirect('admin');
		}else {			
			$selectedLanguage = $this->uri->segment('4');	
			$this->data['heading'] = 'Edit User Language';
			$this->data['language_list'] = $result = $this->multilanguage_model->get_all_details(LANGUAGES_KNOWN, array('id'=>$selectedLanguage));					
			$this->load->view('admin/multilanguage/edit_user_language',$this->data);
		}
	}
	
	public function add_user_language() {
		if ($this->checkLogin('A') == ''){
			redirect('admin');
		}else {			
			$selectedLanguage = $this->uri->segment('4');	
			$this->data['heading'] = 'Add User Language';
			$this->load->view('admin/multilanguage/add_user_language',$this->data);
		}
	}
	
	public function insertEditUserLang() {
		if ($this->checkLogin('A') == ''){
			redirect('admin');
		}else {		
			$language_id = $this->input->post('language_id');
			$excludeArr = array('language_id');
			$time = time();
			$dataArr = array('language_code'=>$time);
			if ($language_id == ''){
			    $language_check = $this->multilanguage_model->get_all_details(LANGUAGES_KNOWN,array('language_name'=>$this->input->post('language_name')));
                if($language_check->num_rows() >0){
					$this->setErrorMessage('error','This Language is Already Exists');
			        redirect(ADMIN_PATH.'/multilanguage/display_user_language');
			    }else{
					$this->multilanguage_model->commonInsertUpdate(LANGUAGES_KNOWN,'insert',$excludeArr,$dataArr,$condition);
					$this->setErrorMessage('success','Language Added successfully');
					redirect(ADMIN_PATH.'/multilanguage/display_user_language');
			    }
			}else{
				$this->multilanguage_model->commonInsertUpdate(LANGUAGES_KNOWN,'update',$excludeArr,array(),array('id'=>$language_id));
				$this->setErrorMessage('success','Language updated successfully');
				redirect(ADMIN_PATH.'/multilanguage/display_user_language');
		    }
		}
	}
	
	function delete_user_language(){
		$languageId = $this->uri->segment('4');
		$condition = array('id' => $languageId);
		$this->multilanguage_model->commonDelete(LANGUAGES_KNOWN,$condition);
		$this->setErrorMessage('success'," Language deleted successfully");
		redirect('admin/multilanguage/display_user_language');			
	}

	
} /* main controller end */

?>