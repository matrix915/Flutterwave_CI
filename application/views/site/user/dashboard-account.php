<?php 
$this->load->view('site/templates/header');
?>
<!---DASHBOARD-->
<div class="dashboard  yourlisting bgcolor account accountid1" id="acctpg1">

  <div class="top-listing-head">
 <div class="main">  
 
         <!--   <ul id="nav">
                <li><a href="<?php echo base_url();?>dashboard"><?php if($this->lang->line('Dashboard') != '') { echo stripslashes($this->lang->line('Dashboard')); } else echo "Dashboard";?></a></li>
                <li><a href="<?php echo base_url();?>inbox"><?php if($this->lang->line('Inbox') != '') { echo stripslashes($this->lang->line('Inbox')); } else echo "Inbox";?></a></li>
                <li><a href="<?php echo base_url();?>listing/all"><?php if($this->lang->line('YourListing') != '') { echo stripslashes($this->lang->line('YourListing')); } else echo "Your Listing";?></a></li>
                <li><a href="<?php echo base_url();?>trips/upcoming"><?php if($this->lang->line('YourTrips') != '') { echo stripslashes($this->lang->line('YourTrips')); } else echo "Your Trips";?></a></li>
                <li><a href="<?php echo base_url();?>settings"><?php if($this->lang->line('Profile') != '') { echo stripslashes($this->lang->line('Profile')); } else echo "Profile";?></a></li>
                <li class="active"><a href="<?php echo base_url();?>account"><?php if($this->lang->line('Account') != '') { echo stripslashes($this->lang->line('Account')); } else echo "Account";?></a></li>
				<li><a href="<?php echo base_url();?>invite"><?php if($this->lang->line('Invite') != '') { echo stripslashes($this->lang->line('Invite')); } else echo "Invite";?></a></li>
                <li><a href="<?php echo base_url();?>plan"><?php if($this->lang->line('Plan') != '') { echo stripslashes($this->lang->line('Plan')); } else echo "Plan";?></a></li>
            </ul> -->
			
			<!--main nav header -->
            <?php 
             $this->load->view('site/user/main_nav_header');  
            ?>
			
			
			
			
			
			</div></div>
	<div class="dash_brd">
    	<div id="command_center">
    
             <ul id="nav">
                <li><a href="<?php echo base_url();?>dashboard"><?php if($this->lang->line('Dashboard') != '') { echo stripslashes($this->lang->line('Dashboard')); } else echo "Dashboard";?></a></li>
                <li><a href="<?php echo base_url();?>inbox"><?php if($this->lang->line('Inbox') != '') { echo stripslashes($this->lang->line('Inbox')); } else echo "Inbox";?></a></li>
                <li><a href="<?php echo base_url();?>listing/all"><?php if($this->lang->line('YourListing') != '') { echo stripslashes($this->lang->line('YourListing')); } else echo "Your Listing";?></a></li>
                <li><a href="<?php echo base_url();?>trips/upcoming"><?php if($this->lang->line('YourTrips') != '') { echo stripslashes($this->lang->line('YourTrips')); } else echo "Your Trips";?></a></li>
                <li><a href="<?php echo base_url();?>settings"><?php if($this->lang->line('Profile') != '') { echo stripslashes($this->lang->line('Profile')); } else echo "Profile";?></a></li>
                <li class="active"><a href="<?php echo base_url();?>account"><?php if($this->lang->line('Account') != '') { echo stripslashes($this->lang->line('Account')); } else echo "Account";?></a></li>
				<li><a href="<?php echo base_url();?>invite"><?php if($this->lang->line('Invite') != '') { echo stripslashes($this->lang->line('Invite')); } else echo "Invite";?></a></li>
                <li><a href="<?php echo base_url();?>plan"><?php if($this->lang->line('Plan') != '') { echo stripslashes($this->lang->line('Plan')); } else echo "Plan";?></a></li>
            </ul> 
<div class="lispg_top">
            <!--Side nav header -->
            <?php 
             $this->load->view('site/user/sub_nav_header');  
            ?>


            
 	<div id="account" class="listiong-areas">
    <div class="box">
      <div class="middle">
			

         <!-- <h1>Mobile Notifications / Text Messages</h1>-->
         <!-- <div class="section notification_section">

        
             <form name="mobile_settings" method="post" action="site/user/update_notifications_mobile">
            <div class="notification_area">
              <div class="notification_header">
               <?php 
                $noty = explode(',', $userDetails->row()->notifications);
                if (is_array($noty)){
                	$notifications = $noty;
                }
                ?>
                <h3>Notify me when:</h3>
                <h4>Applies to both text messages &amp; push notifications.</h4>
              </div>
             
              <div class="notification_action">
                <ul class="unstyled">
                  <li>
                  <input type="checkbox" name="message_another_person" id="message_another_person" <?php if (in_array('message_another_person', $notifications)){echo 'checked="checked"';}?>> 
                  
                  </li>
                  <li>
                  <input type="checkbox" name="guest_reservation" id="guest_reservation" <?php if (in_array('guest_reservation', $notifications)){echo 'checked="checked"';}?>> 
                  <label for="guest_reservation">My outstanding reservation request is accepted or declined.</label></li>
                  <li>
                  <input type="checkbox" name="reservation_request" id="reservation_request" <?php if (in_array('reservation_request', $notifications)){echo 'checked="checked"';}?>> 
                  <label for="reservation_request">I receive a reservation request.</label>
                  </li>
                </ul>
              </div>
           
            </div>

            <div class="buttons">
              <input type="hidden" name="user_id" value="<?php $Details->row()->id;?>" />
              <input type="submit" value="Save Mobile Settings" name="commit" class="btn green">
            </div>
          </div>-->
				   </form>
  				<h1><?php if($this->lang->line('MobileNotification') != '') { echo stripslashes($this->lang->line('MobileNotification')); } else echo "Mobile Notification / Text Messages";?></h1>
				
				
         <!--<form name="email_settings" method="post" action="site/user/update_notifications">-->
        <form name="email_settings" method="post" action="site/user/update_mobile_notifications">
				
				
				
                  				<div class="section notification_section">
				
					
  	        <div class="notification_area">
  	         
              <div class="notification_action">

                <div class="left-sided sideg">

                <h5 style="margin: 0px; line-height: 17px;"><?php if($this->lang->line('MobilePhone') != '') { echo stripslashes($this->lang->line('MobilePhone')); } else echo "Mobile Phone";?></h5>
<p><?php if($this->lang->line('Receivemobile') != '') { echo stripslashes($this->lang->line('Receivemobile')); } else echo "Receive mobile updates by regular SMS text message.";?></p>
<h5><?php if($this->lang->line('Notifymewhen') != '') { echo stripslashes($this->lang->line('Notifymewhen')); } else echo "Notify me when";?>:</h5>
<p><?php if($this->lang->line('Appliesto') != '') { echo stripslashes($this->lang->line('Appliesto')); } else echo "Applies to both text messages & push notifications.";?></p>
                </div>
             <div class="right-acnt">
      <p><?php if($this->lang->line('Youcanaddand') != '') { echo stripslashes($this->lang->line('Youcanaddand')); } else echo "You can add and verify phone numbers on your account from the edit profile section.";?></a></p>

	  
      <label><input type="checkbox" name="mobile_notification"  value="yes" <?php if ($userDetails->row()->receive_text_msg=='yes') { echo 'checked="checked"';}  ?> ><span><?php if($this->lang->line('Ireceivea') != '') { echo stripslashes($this->lang->line('Ireceivea')); } else echo "I receive a reservation request";?></span></label>
	  
	  

             </div>
  	          </div>
  	          </div>
			  
			  
			   <input type="submit" value="<?php if($this->lang->line('save_mobile_settings') != '') { echo stripslashes($this->lang->line('save_mobile_settings')); } else echo "Save Mobile Settings";?>" name="commit" class="btn green">


  </div>

 </div>



  <div class="middle">
      

         <!-- <h1>Mobile Notifications / Text Messages</h1>-->
         <!-- <div class="section notification_section">

        
             <form name="mobile_settings" method="post" action="site/user/update_notifications_mobile">
            <div class="notification_area">
              <div class="notification_header">
               <?php 
                $noty = explode(',', $userDetails->row()->notifications);
                if (is_array($noty)){
                  $notifications = $noty;
                }
                ?>
                <h3>Notify me when:</h3>
                <h4>Applies to both text messages &amp; push notifications.</h4>
              </div>
             
              <div class="notification_action">
                <ul class="unstyled">
                  <li>
                  <input type="checkbox" name="message_another_person" id="message_another_person" <?php if (in_array('message_another_person', $notifications)){echo 'checked="checked"';}?>> 
                  
                  </li>
                  <li>
                  <input type="checkbox" name="guest_reservation" id="guest_reservation" <?php if (in_array('guest_reservation', $notifications)){echo 'checked="checked"';}?>> 
                  <label for="guest_reservation">My outstanding reservation request is accepted or declined.</label></li>
                  <li>
                  <input type="checkbox" name="reservation_request" id="reservation_request" <?php if (in_array('reservation_request', $notifications)){echo 'checked="checked"';}?>> 
                  <label for="reservation_request">I receive a reservation request.</label>
                  </li>
                </ul>
              </div>
           
            </div>

            <div class="buttons">
              <input type="hidden" name="user_id" value="<?php $Details->row()->id;?>" />
              <input type="submit" value="Save Mobile Settings" name="commit" class="btn green">
            </div>
          </div>-->
           </form>
		   
		   
		   
          <h1><?php if($this->lang->line('EmailSettings') != '') { echo stripslashes($this->lang->line('EmailSettings')); } else echo "Email Settings";?></h1>
                <form name="email_settings" method="post" action="site/user/update_notifications">
                          <div class="section notification_section">
           <?php 
                $emailNoty = explode(',', $userDetails->row()->email_notifications);
                if (is_array($emailNoty)){
                  $emailNotifications = $emailNoty;
                }
                ?>
           <!-- <div class="notification_area">
              <div class="notification_header">
                <h3>Send me emails when:</h3>
                <h4>We promise not to spam, and you can disable these at any time.</h4>
              </div>
              <div class="notification_action">
                <ul class="unstyled">
                  <li>
                  <input type="checkbox" name="cool_destination" id="cool_destination"<?php if (in_array('cool_destination', $emailNotifications)){echo 'checked="checked"';}?>> 
                 
                  </li>
                  <li>
                  <input type="checkbox" name="fun_news" id="fun_news" <?php if (in_array('fun_news', $emailNotifications)){echo 'checked="checked"';}?>> 
                 
                  </li>
                </ul>
              </div>
            </div>-->
          
            <div class="notification_area">
             
              <div class="notification_action">

                <div class="left-sided">

                  <h3><?php if($this->lang->line('Iwantto') != '') { echo stripslashes($this->lang->line('Iwantto')); } else echo "I want to receive";?>:</h3>
                  <span><?php if($this->lang->line('Youcandisable') != '') { echo stripslashes($this->lang->line('Youcandisable')); } else echo "You can disable at these at any time";?></span>
                </div>
              <ul class="unstyled">
                <li>
                <input type="checkbox" name="upcoming_reservation" id="upcoming_reservation" <?php if (in_array('upcoming_reservation', $emailNotifications)){echo 'checked="checked"';}?>> 
                <label for="upcoming_reservation"><?php if($this->lang->line('Ihavean') != '') { echo stripslashes($this->lang->line('Ihavean')); } else echo "I have an upcoming reservation.";?></label>
                </li>
                <li>
                <input type="checkbox" name="new_review" id="new_review" <?php if (in_array('new_review', $emailNotifications)){echo 'checked="checked"';}?> > 
                <label for="new_review"><?php if($this->lang->line('Ihavenew') != '') { echo stripslashes($this->lang->line('Ihavenew')); } else echo "I have received a new review.";?></label>
                </li>
                <li>
                <input type="checkbox" name="new_reference" id="new_reference" <?php if (in_array('new_reference', $emailNotifications)){echo 'checked="checked"';}?>> 
                <label for="new_reference"><?php if($this->lang->line('Ihavereceived') != '') { echo stripslashes($this->lang->line('Ihavereceived')); } else echo "I have received a new reference.";?></label>
                </li>
                <li>
                <input type="checkbox" name="reference_request" id="reference_request" <?php if (in_array('reference_request', $emailNotifications)){echo 'checked="checked"';}?>>
                <label for="reference_request"><?php if($this->lang->line('Ihave') != '') { echo stripslashes($this->lang->line('Ihave')); } else echo "I have received a new reference request.";?></label>
                </li>
              <li>
                <input type="checkbox" name="review_reminder" id="review_reminder" <?php if (in_array('review_reminder', $emailNotifications)){echo 'checked="checked"';}?>> 
                <label for="review_reminder"><?php if($this->lang->line('Ineedto') != '') { echo stripslashes($this->lang->line('Ineedto')); } else echo "I need to leave a review for one of my guests.";?></label>
                </li>
                <li>
                <input type="checkbox" value="1" name="calendar_reminder" id="calendar_reminder" <?php if (in_array('calendar_reminder', $emailNotifications)){echo 'checked="checked"';}?>> 
                <label for="calendar_reminder"><?php if($this->lang->line('Icanimprove') != '') { echo stripslashes($this->lang->line('Icanimprove')); } else echo "I can improve my ranking in the search results by updating my calendar.";?></label>
                </li>
              </ul>
              </div>
              </div>
			  <input type="hidden" name="user_id" value="<?php $Details->row()->id;?>" />
              <input type="submit" value="<?php if($this->lang->line('Save') != '') { echo stripslashes($this->lang->line('Save')); } else echo "Save";?>" name="commit" class="btn green">
			  </form>



















  </div>

 </div>
</div>



        </div>          

      </div>
    </div>
  </div>
         
  </div>
    </div>
</div>
<!---DASHBOARD-->
<!---FOOTER-->
<?php 
$this->load->view('site/templates/footer');
?>