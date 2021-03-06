<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * This model contains all db functions related to Cart Page
 * @author Teamtweaks
 *
 */
class Bookingpayment_model extends My_Model
{
	
	
	public function view_newbooking_details(){
	
		$this->db->select('rq.*,u.email,u.firstname,u.address,u.accname,u.Acccountry,u.swiftcode,u.phone_no,u.accno as bank_no,u.bankname as bank_name,u.postal_code,u.state,u.country,u.city,pd.product_name,pd.price,pd.id as PrdID, py.created as transaction_date, py.paypal_transaction_id as transaction_id, py.sell_id');
		$this->db->from(RENTALENQUIRY.' as rq');
		$this->db->join(USERS.' as u' , 'rq.renter_id = u.id');
		$this->db->join(PRODUCT.' as pd' , 'pd.id = rq.prd_id');
        $this->db->join(PAYMENT.' as py' , 'py.EnquiryId = rq.id');	
        		
		//$this->db->where('rq.booking_status = "'.$status.'"');	
		$this->db->where('py.status = "Paid"');	
		$this->db->order_by("rq.dateAdded", "desc"); 
		$PrdList = $this->db->get();
		//echo '<pre>'; print_r($PrdList->result()); die;
		return $PrdList;
	}


	
	public function view_newbooking_detailsexp($status){
		$this->db->select('rq.*,u.email,u.firstname,u.address,u.accname,u.Acccountry,u.swiftcode,u.phone_no,u.bank_no,u.bank_name,u.postal_code,u.state,u.country,u.city,pd.product_name,pd.price,pd.id as PrdID');
		$this->db->from(RENTALENQUIRY.' as rq');
		$this->db->join(USERS.' as u' , 'rq.user_id = u.id');
		$this->db->join(PRODUCT.' as pd' , 'pd.id = rq.prd_id');		
		$this->db->where('rq.booking_status = "'.$status.'"');				
		$today = date('Y-m-d'); 
		$this->db->where("rq.dateAdded <=",$today); 
		$PrdList = $this->db->get();
		
		//echo '<pre>'; print_r($PrdList->result()); die;
		return $PrdList;
	}
	
	public function get_all_commission_tracking($rep_code=''){

		$Query = "select c.* , re.prd_id,re.currencyPerUnitSeller,re.currencycode from ".COMMISSION_TRACKING." c JOIN ".RENTALENQUIRY." re on re.Bookingno=c.booking_no ";
		if($rep_code!=''){
			$Query.=" JOIN ".USERS." u on u.email=c.host_email and u.rep_code='$rep_code' ";
		}
		return $this->ExecuteQuery($Query);
	}

	public function get_all_commission_tracking_by_user($rep_code='',$host){
		//$host = 'kumarkailash075@gmail.com';
				
		$this->db->select('c.*, rq.prd_id,rq.currencyPerUnitSeller,rq.currencycode');
		$this->db->from(COMMISSION_TRACKING.' as c');
		$this->db->join(RENTALENQUIRY.' as rq', 'rq.Bookingno=c.booking_no');
		if($rep_code!=''){
			$this->db->join(USERS. ' as u', 'u.rep_code="'.$rep_code.'"');
		} else {
			//$this->db->join(USERS.' as u');
		}
		$this->db->where('c.host_email = "'.$host.'"');	
		$UserList = $this->db->get();	
		return $UserList;

	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}

?>