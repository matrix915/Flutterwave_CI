<?php //die;?>
<script src="js/site/core.js" type="text/javascript"></script>
<script type="text/javascript">

function homeView(val){
	//alert(val);
	if($('#homelist'+val).css('display')=='block'){
		$('#homelist'+val).hide('');	
	}else{
		$('#homelist'+val).show('');
	}
}


function roomView(val){
	//alert(val);
	if($('#roomlist'+val).css('display')=='block'){
		$('#roomlist'+val).hide('');	
	}else{
		$('#roomlist'+val).show('');
	}
}

function cityView(val){
	//alert(val);
	if($('#citylist'+val).css('display')=='block'){
		$('#citylist'+val).hide('');	
	}else{
		$('#citylist'+val).show('');
	}
}

function otherView(val){
	//alert(val);
	if($('#otherlist'+val).css('display')=='block'){
		$('#otherlist'+val).hide('');	
	}else{
		$('#otherlist'+val).show('');
	}
}

function accommodatesView(val){
	//alert(val);
	if($('#accommodateslist'+val).css('display')=='block'){
		$('#accommodateslist'+val).hide('');	
	}else{
		$('#accommodateslist'+val).show('');
	}
}

function calenderView(val){
	//alert(val);
	if($('#calenderlist'+val).css('display')=='block'){
		$('#calenderlist'+val).hide('');	
	}else{
		$('#calenderlist'+val).show('');
	}
}

</script>


<?php $url = $this->uri->segment(1);?>

<div class="sub_header">

	<ul class="sub_header_left">
    
    	<li><a class="view_listingarw" href="<?php echo base_url()."listing/all";?>"><img src="images/arw.png" style="width: 66px; padding: 8px 20px 0px 27px;"></a><div class="tools"><i class="arsd-ico"></i><span><?php if($this->lang->line('list_listingwillbe') != '') { echo stripslashes($this->lang->line('list_listingwillbe')); } else echo "The listing will be previewed after activating it in the admin."; ?></span>
</div></li>

<?php 
	if($listDetail->row()->room_type!=''){
		$getRoomType=$this->product_model->get_all_details(LISTSPACE_VALUES,array('id'=>$listDetail->row()->room_type));
		$theRoomType=$getRoomType->row()->list_value;
	}else{
		$theRoomType='New Property';
	}
	
	if ($listDetail->row()->id!=''){
		$getCity=$this->product_model->get_all_details(PRODUCT_ADDRESS_NEW,array('productId'=>$listDetail->row()->id));
		$TheCity=$getCity->row()->city;
	}else{
		$TheCity="This City";
	}

?>

	<li class="write_title"><?php if($listDetail->row()->product_title == '') echo $theRoomType.' in '.$TheCity; else echo $listDetail->row()->product_title;?></li>
        
       <!-- <li class="write_title"><?php //if($listDetail->row()->product_title == '') echo $listDetail->row()->room_type.' in '.$listDetail->row()->city; else echo $listDetail->row()->product_title;?></li>-->
        
       <li class="prevwli" style="display:none"><a class="pre-li" href="#"><?php if($this->lang->line('Preview') != '') { echo stripslashes($this->lang->line('Preview')); } else echo "Preview";?></a><div class="tools"><i class="arsd-ico"></i><span><?php if($this->lang->line('list_intheadmin') != '') { echo stripslashes($this->lang->line('list_intheadmin')); } else echo "The listing will be previewed after activating it in the admin."; ?></span></div></li>
    
    </ul>

</div>

<!---DASHBOARD-->


<?php 
//$listDetail =$listDetail->result_array();
 /* echo '<pre>'; print_r($listDetail);
 die; */  ?>
	<div class="main_2 listingmain_2">
    	
        <div class="manage_listing">
        
        	<div class="left_side">
            
            	<div class="left_side_top">
            
            	<h2><?php if($this->lang->line('list_Basics') != '') { echo stripslashes($this->lang->line('list_Basics')); } else echo "Basics"; ?></h2>
                
                <ul class="left_side_1">
				
                
                	<li <?php if($url == 'manage_listing') echo 'class="active"';?>><a href="<?php echo base_url()."manage_listing/".$listDetail->row()->id;?>"><i class="left_side_icon left_icon-1"></i><span><?php if($this->lang->line('list_Calendar') != '') { echo stripslashes($this->lang->line('list_Calendar')); } else echo "Calendar"; ?></span><div class="new-section-icon"><i <?php if($listDetail->row()->calendar_checked==''){ echo 'class="icon_plus"'; }else{ echo 'class="icon_plus_active"';}?>></i></div></a></li>
                    
                    <li <?php if($url == 'price_listing') echo 'class="active"';?>><a href="<?php echo base_url()."price_listing/".$listDetail->row()->id;?>"><i class="left_side_icon left_icon-2"></i><span><?php if($this->lang->line('list_Pricing') != '') { echo stripslashes($this->lang->line('list_Pricing')); } else echo "Pricing"; ?></span><div class="new-section-icon"><i <?php if($Steps_count2=='1'){ echo 'class="icon_plus"'; }else{ echo 'class="icon_plus_active"';}?>></i></div></a></li>
                
                </ul>
                
                <h2><?php if($this->lang->line('list_Description') != '') { echo stripslashes($this->lang->line('list_Description')); } else echo "Description"; ?></h2>
                
                <ul class="left_side_1">
                
                	<li <?php if($url == 'overview_listing') echo 'class="active"';?>><a href="<?php echo base_url()."overview_listing/".$listDetail->row()->id;?>"><i class="left_side_icon left_icon-3"></i><span><?php if($this->lang->line('list_Overview') != '') { echo stripslashes($this->lang->line('list_Overview')); } else echo "Overview"; ?></span><div class="new-section-icon"><i <?php if($Steps_count1=='1'){ echo 'class="icon_plus"'; }else{ echo 'class="icon_plus_active"';}?>></i></div></a></li>
					<?php //if($listDetail->row()->space !="" || $listDetail->row()->guest_access !="" || $listDetail->row()->interact_guest !="" || $listDetail->row()->neighbor_overview !="" || $listDetail->row()->neighbor_around !="" || $listDetail->row()->house_rules !="" || $url == 'detail_list'){?>
					<li <?php if($url == 'detail_list') echo 'class="active"';?>><a href="<?php echo base_url()."detail_list/".$listDetail->row()->id;?>"><i class="left_side_icon left_icon-3"></i><span><?php if($this->lang->line('list_Details') != '') { echo stripslashes($this->lang->line('list_Details')); } else echo "Details"; ?></span><div class="new-section-icon"><i <?php if($listDetail->row()->space !="" || $listDetail->row()->guest_access !="" || $listDetail->row()->interact_guest !="" || $listDetail->row()->neighbor_overview !="" || $listDetail->row()->neighbor_around !="" || $listDetail->row()->house_rules !=""){ echo 'class="icon_plus_active"'; }else{ echo 'class="icon_plus"';}?>></i></div></a></li>
                    <?php //}?>
                    <li <?php if($url == 'photos_listing') echo 'class="active"';?>><a href="<?php echo base_url()."photos_listing/".$listDetail->row()->id;?>"><i class="left_side_icon left_icon-4"></i><span><?php if($this->lang->line('list_Photos') != '') { echo stripslashes($this->lang->line('list_Photos')); } else echo "Photos"; ?></span><div class="new-section-icon"><i <?php if($Steps_count4=='1'){ echo 'class="icon_plus"'; }else{ echo 'class="icon_plus_active"';}?>></i></div></a></li>
                
                </ul>
                
                    <h2><?php if($this->lang->line('list_Settings') != '') { echo stripslashes($this->lang->line('list_Settings')); } else echo "Settings"; ?></h2>
                
                <ul class="left_side_1">
                
                	<li <?php if($url == 'amenities_listing') echo 'class="active"';?>><a href="<?php echo base_url()."amenities_listing/".$listDetail->row()->id;?>"><i class="left_side_icon left_icon-5"></i><span><?php if($this->lang->line('list_Amenities') != '') { echo stripslashes($this->lang->line('list_Amenities')); } else echo "Amenities"; ?></span><div class="new-section-icon"><i <?php if($Steps_count5=='1'){ echo 'class="icon_plus"'; }else{ echo 'class="icon_plus_active"';}?>></i></div></a></li>
					<?php //print_r($Steps_count6);die; ?>
					<li <?php if($url == 'space_listing') echo 'class="active"';?>><a href="<?php echo base_url()."space_listing/".$listDetail->row()->id; ?>"><i class="left_side_icon left_icon-7"></i><span><?php if($this->lang->line('list_Listing') != '') { echo stripslashes($this->lang->line('list_Listing')); } else echo "Listing"; ?></span><div class="new-section-icon"><i <?php if($Steps_count7=='1'){ echo 'class="icon_plus"'; }else{ echo 'class="icon_plus_active"';}?>></i></div></a></li>
                    
                    <li <?php if($url == 'address_listing') echo 'class="active"';?>><a href="<?php echo base_url()."address_listing/".$listDetail->row()->id;?>"><i class="left_side_icon left_icon-6"></i><span><?php if($this->lang->line('list_Location') != '') { echo stripslashes($this->lang->line('list_Location')); } else echo "Location"; ?></span><div class="new-section-icon"><i <?php if($Steps_count6=='1'){ echo 'class="icon_plus"'; }else{ echo 'class="icon_plus_active"';}?>></i></div></a></li>
        
                   <li <?php if($url == 'cancel_policy') echo 'class="active"';?>><a href="<?php echo base_url()."cancel_policy/".$listDetail->row()->id; ?>"><i class="left_side_icon left_icon-7"></i><span><?php if($this->lang->line('list_Cancellation') != '') { echo stripslashes($this->lang->line('list_Cancellation')); } else echo "Cancellation Policy"; ?></span><div class="new-section-icon"><i <?php if($Steps_count8=='1'){ echo 'class="icon_plus"'; }else{ echo 'class="icon_plus_active"';}?>></i></div></a></li>
			   </ul>
            
            </div>
            
            <div class="left_side_bottom"  >
            <!--<div class="left_side_bottom" id="list" value="<?php echo $listDetail->row()->id; ?>" onclick = "publish"; >-->
			
            <?php // echo "<pre>"; print_r($hosting_commission_status->row()->status);die;
			
			
			if($Steps_tot==0 || ($Steps_tot==1 && $Steps_count3 == 1)) {
				
			  if($hosting_commission_status->row()->status =='Inactive')
			  {
$payment_url=base_url().'site/product/redirect_base/completed/'.$listDetail->row()->id;
				  
						
			  }
			  else{
$payment_url=base_url().'site/product/redirect_base/payment/'.$listDetail->row()->id;
			   }
			   
			  
			?>
			

			
			<!--<a href="<?php 
			/* if($listDetail->row()->status!='Publish'){
					echo $payment_url;
					} else {
					echo 'javascript:void(0);';
					} */
					?>">-->
					
					
	<a href="<?php 
			 if($listDetail->row()->status!='Publish'){
					echo $payment_url;
					} else {
						//echo base_url().'verification'; 
					echo 'javascript:void(0);';

					} 
					?>">
					
					

			
			
			
			<?php if($listDetail->row()->status !='Publish'){
			if($this->lang->line('List Space') != '') { 
			echo stripslashes($this->lang->line('List Space')); 
			} 
			else echo "List Space";
			}
			else{
				if($this->lang->line('Listed') != '') { 
			echo stripslashes($this->lang->line('Listed')); } else echo "Listed";
			}?>
			</a>
			
			
			
			<?php } else { ?>
            	<div class="left_side_bottom_content"><!--Complete <strong><?php echo $Steps_tot;?> steps</strong> to list your space.--></div>
           <?php }?>
            </div>
            
            </div>
			

<script type="text/javascript">


function publish() {
	
	$.ajax({
			type:'POST',
			url:'<?php echo base_url()?>site/experience/publish"+id,
			data:{id :id},
			success:function(response)
			{
				
				

			}
		}); 
	} else {
        
    }  
}

	
</script>