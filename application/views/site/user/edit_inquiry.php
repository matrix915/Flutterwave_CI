<?php
$this->load->view('site/templates/header');
?>
<script src="js/site/menu.js" type="text/javascript"></script>
<script src="js/site/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript">
	jQuery(document).ready( function () {
		$(".datepicker").datepicker({ minDate:0, dateFormat: 'yy-mm-dd'});
	});
</script>
                    <div class="main">
						<div class="dashboard_full">
    					<div class="page_title W99"><?php if($this->lang->line('Edit Inquiry Details') != '') { echo stripslashes($this->lang->line('Edit Inquiry Details')); } else echo "Edit Inquiry Details";?></div>
                        <div class="dashboard_full_tex">
                         
                        <div class="right_dashboard_content">
                        <?php 
						$attributes = array('class' => 'form_container left_label');
						echo form_open('site/rentals/contact_booking',$attributes) 
					?>
                     <table width="100%" border="0" cellspacing="0" cellpadding="0" class="property_table">
 
<?php foreach($InquirieDisplay->result() as $InquiryRow){ ?>
  <tr>
 		<td width="10%"><?php if($this->lang->line('Rental Name') != '') { echo stripslashes($this->lang->line('Rental Name')); } else echo "Rental Name";?></td>
 			
 					<td width="20%"><?php echo $InquiryRow->product_title; ?></td> 
  </tr>
  
  
  <tr>
  	    <td width="20%"><?php if($this->lang->line('signup_full_name') != '') { echo stripslashes($this->lang->line('signup_full_name')); } else echo "First Name";?></td>
  		<td width="60%"><?php echo $InquiryRow->firstname; ?></td>
  </tr>
  <tr>
  		<td width="20%"><?php if($this->lang->line('signup_user_name') != '') { echo stripslashes($this->lang->line('signup_user_name')); } else echo "Last Name";?></td>
    	<td width="60%"><?php echo $InquiryRow->lastname; ?></td>
  </tr>
  <tr>
  		<td width="20%"><?php if($this->lang->line('Email') != '') { echo stripslashes($this->lang->line('Email')); } else echo "Email";?></td>
    	<td width="60%"><?php echo $InquiryRow->email; ?></td>
  </tr>
  <tr>
  		<td width="20%"><?php if($this->lang->line('Phone') != '') { echo stripslashes($this->lang->line('Phone')); } else echo "Phone";?>
</td>
    	<td width="60%"><?php echo $InquiryRow->phone_no; ?></td>
  </tr>
  <tr>
  		<td width="20%"><?php if($this->lang->line('Arrival Date') != '') { echo stripslashes($this->lang->line('Arrival Date')); } else echo "Arrival Date";?></td>
        <td width="60%"><input type="hidden" name="arrival_date" value="<?php echo $InquiryRow->checkin; ?>" class="datepicker" /><?php echo $InquiryRow->checkin; ?></td>
  </tr>
  <tr>
  		<td width="20%"><?php if($this->lang->line('Depature_Date') != '') { echo stripslashes($this->lang->line('Depature_Date')); } else echo "Depature Date";?>
</td>
     	<td width="60%"><input type="hidden" name="depature_date" value="<?php echo $InquiryRow->checkout; ?>" class="datepicker" /><?php echo $InquiryRow->checkout; ?></td>
  </tr>
  <tr>
  		<td width="20%"><?php if($this->lang->line('guest') != '') { echo stripslashes($this->lang->line('guest')); } else echo "Guests";?> </td>
    	<td width="60%"><?php echo $InquiryRow->NoofGuest; ?></td>
  </tr>
 <!-- <tr>
  		<td width="20%">Children</td>
    	<td width="60%"><?php //echo $InquiryRow->children; ?></td>
  </tr>-->
  <tr>	
  		<td width="20%"><?php if($this->lang->line('Comments') != '') { echo stripslashes($this->lang->line('Comments')); } else echo "Comments";?></td>
        <td width="60%"><?php echo $InquiryRow->Enquiry; ?></td>
  </tr>
  
  <tr>
  		
  		<td width="20%"> <input type="hidden" name="cntId" value="<?php echo $InquiryRow->id; ?>" />
        		<input type="hidden" name="rental_id" value="<?php echo $InquiryRow->prd_id;?>" />
                <input type="hidden" name="renter_id" value="<?php echo $InquiryRow->renter_id;?>" />
                <input type="hidden" name="email_address" value="<?php echo $InquiryRow->email; ?>" />
        <input style="float:left; margin-top:10px;" type="submit" name="submit" value="Confirm Booking" class="cancel_btn" /></td></td>
    	
        <td>	
       
  </tr>
 <?php } ?>	
</table>
</form>
 </div>
          </div>
            </div>
              </div>
<script type="text/javascript">
$(function() {
			$("#inquiry_reply").submit(function(){
				// var email = $('#vendor_email').val();
				//alert('');
				$("#reply_warn").html('');
				if(jQuery.trim($("#reply").val()) == '')
					{
						
						$("#reply_warn").html('<?php if($this->lang->line('Please_enter_some_message_to_send') != '') { echo stripslashes($this->lang->line('Please_enter_some_message_to_send')); } else echo "Please enter some message to send";?>');
						$("#reply").focus();
						return false;
						
					}
					else
					{	
					      	$("#inquiry_reply").submit();
					}
					
					return false;	
				});
		});
</script>
<?php
$this->load->view('site/templates/footer');
?>
    