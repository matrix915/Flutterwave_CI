<?php 
//echo '<pre>';print_r($rentalDetail->result());die;
$this->load->view('site/templates/header');
?>
<!---DASHBOARD-->
<div class="dashboard yourlisting yourlistinghome dasblistgnwthm">

<div class="top-listing-head">
 <div class="main">

			 <!--main nav header -->
            <?php 
             $this->load->view('site/user/main_nav_header');  
            ?>

			</div></div>
	<div class="dash_brd">
    	<div id="command_center">
		<div class="lispg_top">
      <div class="dashboard-sidemenu">
            <ul id="nav">
                <li><a href="<?php echo base_url();?>dashboard"><?php if($this->lang->line('Dashboard') != '') { echo stripslashes($this->lang->line('Dashboard')); } else echo "Dashboard";?></a></li>
                <li><a href="<?php echo base_url();?>inbox"><?php if($this->lang->line('Inbox') != '') { echo stripslashes($this->lang->line('Inbox')); } else echo "Inbox";?></a></li>
                <li class="active"><a href="<?php echo base_url();?>listing/all"><?php if($this->lang->line('YourListing') != '') { echo stripslashes($this->lang->line('YourListing')); } else echo "Your Listing";?></a></li>
                <li><a href="<?php echo base_url();?>trips/upcoming"><?php if($this->lang->line('YourTrips') != '') { echo stripslashes($this->lang->line('YourTrips')); } else echo "Your Trips";?></a></li>
                <li><a href="<?php echo base_url();?>settings"><?php if($this->lang->line('Profile') != '') { echo stripslashes($this->lang->line('Profile')); } else echo "Profile";?></a></li>
                <li><a href="<?php echo base_url();?>account"><?php if($this->lang->line('Account') != '') { echo stripslashes($this->lang->line('Account')); } else echo "Account";?></a></li>
				<li><a href="<?php echo base_url();?>invite"><?php if($this->lang->line('Invite') != '') { echo stripslashes($this->lang->line('Invite')); } else echo "Invite";?></a></li>
                <li><a href="<?php echo base_url();?>plan"><?php if($this->lang->line('Plan') != '') { echo stripslashes($this->lang->line('Plan')); } else echo "Plan";?></a></li>
            </ul> 
            <ul class="subnav">
                <li class="active"><a href="<?php echo base_url();?>listing/all"><?php if($this->lang->line('ManageListings') != '') { echo stripslashes($this->lang->line('ManageListings')); } else echo "Manage Listings";?></a></li>
                <li><a href="<?php echo base_url();?>listing-reservation"><?php if($this->lang->line('YourReservations') != '') { echo stripslashes($this->lang->line('YourReservations')); } else echo "Your Reservations";?>  </a></li>
                <!--<li><a href="<?php echo base_url();?>listing-requirement"><?php if($this->lang->line('ReservationRequirements') != '') { echo stripslashes($this->lang->line('ReservationRequirements')); } else echo "Reservation Requirements";?></a></li>-->
                <!--<li><a href="<?php echo base_url();?>listing-booking">Booking</a></li>
                <li><a href="<?php echo base_url();?>listing_enquiry">Enquiry</a></li>-->
            </ul>

          </div>
            

            <div class="listiong-areas">
            <div class="box">
      <div class="middle">
	  <?php if(count($rentalDetail->result())>0){ ?>
        <div class="sort-header clearfix">
          <span id="listings-filter" class="btn-group btn-dropdown all">
          	<a href="javascript:void(0);">
            <button class="btn gray mini dropdown-toggle display-filter" id="gray_bt">
              <span class="none always"><?php if($this->lang->line('Show') != '') { echo stripslashes($this->lang->line('Show')); } else echo "Show";?>:</span>
              <span class="none">
               <?php if($this->lang->line('alllistings') != '') { echo stripslashes($this->lang->line('alllistings')); } else echo "all listings";?> 
               
              </span>
              <span class="active">
                <span class="dot dot-green"></span>
                <?php if($this->lang->line('activelistings') != '') { echo stripslashes($this->lang->line('activelistings')); } else echo "active listings";?>
              </span>
              <span class="inactive">
                <span class="dot dot-red"></span>
               <?php if($this->lang->line('hiddenlistings') != '') { echo stripslashes($this->lang->line('hiddenlistings')); } else echo "hidden listings";?> 
              </span>
              <i class="fa fa-caret-down" aria-hidden="true"></i>
            </button></a>
            <ul class="dropdown-menu toggle-filter showlist4">
              <li>
                <a href="<?php echo base_url();?>listing/all">
                 <?php if($this->lang->line('Showalllistings') != '') { echo stripslashes($this->lang->line('Showalllistings')); } else echo "Show all listings";?> 
</a>              </li>
              <li>
                <a href="<?php echo base_url();?>listing/Publish">
                  <span class="dot dot-green"></span>
                 <?php if($this->lang->line('Showactive') != '') { echo stripslashes($this->lang->line('Showactive')); } else echo "Show active";?> 
</a>              </li>
              <li>
                <a href="<?php echo base_url();?>listing/UnPublish">
                  <span class="dot dot-red"></span>
                  <?php if($this->lang->line('Showhidden') != '') { echo stripslashes($this->lang->line('Showhidden')); } else echo "Show hidden";?>
</a>              </li>
            </ul>
          </span>
        </div>
        <?php foreach($rentalDetail->result() as $row){ 
				
		?>
        <div id="listings-container">
          <ul class="listings unstyled">
            <li data-hosting-id="1898630" class="listing clearfix">
              <div class="image"><a href="<?php echo base_url()."rental/".$row->id;?>"><img src=<?php if($row->product_image == ''){?>"<?php echo base_url(). PRODUCTPATH.'dummyProductImage.jpg'; ?>"<?php } else {?>"<?php echo PRODUCTPATH; ?><?php echo $row->product_image;}?>" alt="Room_no_photos"></a></div>
                  <div class="listing-criteria-header activation-notification">
  <div class="listing-criteria-header-message">
<?php $total_steps=8;
if($row->product_title !="")
{
$total_steps--;
}
if($row->price !="0.00")
{
$total_steps--;
}
if($row->calendar_checked !="")
{
$total_steps--;
}
if($row->product_image !="")
{
$total_steps--;
}
if($row->list_name !="")
{
$total_steps--;
}
if($row->latitude !="")
{
$total_steps--;
}
if($row->listings !="")
{
$total_steps--;
}
if($row->cancellation_policy !="")
{
$total_steps--;
}
	if($total_steps != 0 ){ ?>
	<a class="btn blue" href="<?php echo base_url()."manage_listing/".$row->id;?>"><?php echo $total_steps;?> <?php if($this->lang->line('stepstolist') != '') { echo stripslashes($this->lang->line('stepstolist')); } else echo "steps to list";?></a>
	
	
	
	<?php } else { ?> 
	
	<?php if($row->status == 'Publish' && $total_steps == 0){?>
		<a class="btn blue" href="javascript:void(0);"><?php if($this->lang->line('Listed') != '') { echo stripslashes($this->lang->line('Listed')); } else echo "Listed";?></a>
		<?php } elseif($row->status == 'UnPublish' && $total_steps == 0 && $hosting_commission_status->row()->status == 'Inactive'){?>
		<a class="btn blue" href="javascript:void(0);"><?php if($this->lang->line('Pending_Approvel') != '') { echo stripslashes($this->lang->line('Pending_Approvel')); } else echo "Pending Approvel";?></a>
		<?php } elseif($row->status == 'UnPublish' && $total_steps == 0 && $hosting_commission_status->row()->status == 'Active' && $row->payment_status == 'paid'){?>
		<a class="btn blue" href="javascript:void(0);"><?php if($this->lang->line('Pending') != '') { echo stripslashes($this->lang->line('Pending')); } else echo "Pending";?></a>
		<?php } elseif($row->status == 'UnPublish' && $total_steps == 0 && $hosting_commission_status->row()->status == 'Active') { ?>
		<a class="btn blue" href="site/product/redirect_base/payment/<?php echo $row->id;?>"><?php if($this->lang->line('Pay') != '') { echo stripslashes($this->lang->line('Pay')); } else echo "Pay";?></a>
		<?php } ?>

		<?php }?>
		
		
		
  </div>
</div>

 
                <div class="listing-info">
               



            <div class="manage-center-detail">
              <span class="managing-list"><a href="<?php echo base_url()."manage_listing/".$row->id;?>"><?php echo $row->product_title;?></a></span>
              <span class="views-list"><a href="<?php echo base_url()."manage_listing/".$row->id;?>"><?php if($this->lang->line('ManageListingand') != '') { echo stripslashes($this->lang->line('ManageListingand')); } else echo "Manage Listing and Calendar";?> </a></span>
			  
			  </br>
			  <!----Publish Property ---->
			  <?php 
		if($row->status == 'UnPublish' && $total_steps == 0){	?>
		
			 <span class="views-list"><a href="<?php echo base_url()."manage_listing/".$row->id;?>"> <h6>
			 <?php if($this->lang->line('editlisting_listspace_to_publish') != '') { echo stripslashes($this->lang->line('editlisting_listspace_to_publish')); } else echo "Edit Your Listing And Click List Space To Publish Your Property.";?>
			 </h6> </a></span>
		<?php } ?>
			  
			  <!----not to host whether email is verified or not---->
		<?php 
		//echo '<h4><small>Please <u><a href="'.base_url().'verification">verify  your email id </a></u> and allow guest to book your property.</small></h4>';
		if($row->id_verified=="No"){	
			echo '<h4><small>Please <u><a href="'.base_url().'verification">verify  your email id </a></u> and allow guest to book your property.</small></h4>';
		}
		?>
		
		
		
		
            </div>



                  <div class="actions">
                    <a class="btn gray mini" href="<?php echo base_url()."manage_listing/".$row->id;?>">

                      <i class="icon-pencil"></i>
                      <?php if($this->lang->line('Managelisting') != '') { echo stripslashes($this->lang->line('Managelisting')); } else echo "Manage listing";?>
</a>
                    <a class="btn gray mini" href="<?php echo base_url()."rental/".$row->id;?>">
                      <i class="icon icon-eye-open"></i>
                      <?php if($this->lang->line('Viewlisting') != '') { echo stripslashes($this->lang->line('Viewlisting')); } else echo "View listing";?>
</a>
                    <!--<a class="btn gray mini" href="<?php echo base_url()."manage_listing/".$row->id;?>"><a name="calendar" style="cursor:pointer;" class="btn gray mini ajax cboxElement" href="site/product/edit_calendar/<?php echo $row->id; ?>?pid=<?php echo $row->id; ?>&price=<?php echo $row->price; ?>">-->
                    <a name="calendar" style="cursor:pointer;" class="btn gray mini example16" data-pid="<?php echo $row->id; ?>" data-price="<?php echo $row->price; ?>" href="javascript:void(0);">
                      <i class="icon icon-calendar"></i>
                      <?php if($this->lang->line('Viewcalendar') != '') { echo stripslashes($this->lang->line('Viewcalendar')); } else echo "View calendar";?>
</a>   


                                
                  </div>
                   
                </div>
                
              </li>
          </ul>
        </div>
        <?php } ?>
        
        <?php }   else {?>

        <div class="no-listing">
		<h2><?php if($this->lang->line('Youdonthave') != '') { echo stripslashes($this->lang->line('Youdonthave')); } else echo "You don't have any listings!";?></h2>
    <p>		Listing your space or property on Occupyproperties is an easy way to rent out any properties you have.</p>
		<p> <?php if($this->lang->line('Youllalso') != '') { echo stripslashes($this->lang->line('Youllalso')); } else echo "You will also get to meet interesting people from around Nigeria!";?></p>
		<a class="list_spaceds" href="list_space"><?php if($this->lang->line('Postanew') != '') { echo stripslashes($this->lang->line('Postanew')); } else echo "Post a new listing";?></a>
		<?php }?>
      <div id="footer_pagination"><?php echo $paginationLink; ?></div>
      </div></div>
    </div> </div>
    </div>       
  </div>
    </div>
</div>
<!---DASHBOARD-->
<!---FOOTER-->

<script type="text/javascript">
$('.example16').click(function(){

	$('#inline_example11 .popup_page').html('<div class="cnt_load"><img src="images/ajax-loader.gif"/></div>');
	
	var pid = $(this).data('pid');
	var price = $(this).data('price');
	var pname = $(this).text();
	var purl = baseURL+$(this).attr('href');
	$.ajax({
		type:'get',
//		url:baseURL+'site/product/viewMemberCalendar/'+pid,
		url:baseURL+'site/product/edit_calendar/'+pid,

		data:{'pid':pid,'price':price},
		dataType:'html',
		success:function(data){ 
		alert(data);
//			window.history.pushState({"html":data,"pageTitle":pname},"", purl);
			$('#inline_example11 .popup_page').html(data);
		}
	});
});
</script>


<!-----------popup for listing------>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
<script>
jQuery.noConflict();
jQuery(function() {
//alert('<?php echo $Steps_count6;?>');
<?php if($this->session->userdata('enable_complete_popup') == 'yes') { ?>
jQuery( "#dialog-message" ).dialog({
modal: true,
buttons: {

click: function() {
//text: "Finish My Listing",
jQuery( this ).dialog( "close" );
}
}
});
<?php }else {?>
//alert();
jQuery('#dialog-message').css('display','none');
<?php }?>
});

jQuery(function()
{
jQuery('.ui-button-text').text('Ok');
});
</script>

<div id="dialog-message" title="<?php if($this->lang->line('Download_complete') != '') { echo stripslashes($this->lang->line('Download_complete')); } else echo "Download complete";?>">
<?php if($this->session->userdata('stringdialogpublish') == 'no') { ?>
<p class="listing-down-creat"><?php if($this->lang->line('Thankyoufor_complete') != '') { echo stripslashes($this->lang->line('Thankyoufor_complete')); } else echo "Thank you for listing your property with us. Listed Property can filter and search near by.";?></p>
</p>
<?php } else { ?>
<p class="listing-down-creat"><?php if($this->lang->line('Thankyoufor') != '') { echo stripslashes($this->lang->line('Thankyoufor')); } else echo "Thank you for listing your property with us. Listing will go live upon approval from us within two working days.";?></p>
</p>
<?php } ?>

</div>
<!-----------End popup for listing------>
<?php if($this->session->userdata('enable_complete_popup'))
{
$this->session->unset_userdata('enable_complete_popup');
}
$this->load->view('site/product/front_calendar',$this->data);
$this->load->view('site/templates/footer');
?>