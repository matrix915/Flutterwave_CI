<?php 
$this->load->view('site/templates/header');
?>
<style>
.cart-list.chept2{
float: left;
width: 100%;
margin: 20px 0px;
}
.thanks-main{
width:70%;
margin:70px auto;
}
.booking{
background:#717171;
width:100%;
text-align:center;
font-size:24px;
color:#fff;
padding:5px 0px;
}
.booking h1{
padding:5px 0px;
margin:0px;
font-size:35px;
font-weight: 100;
}
.booking h5{
padding:0px;
padding-bottom:10px;
margin:0px;
font-size: 13px;
font-weight: 100;
}
.booking-success{
background: #E7E7E7;
float: left;
width: auto;
margin: 16px;
}
.grid-con{
width:95%;
margin:0 auto;
}
.booking-success h2 {
margin-left: 2%;
}
.grid-step1{
width:100%;
float:left;
margin: 10px 0px;
}
.grid-step1 p{
width: 45%;
float: left;
background: #a5a5a5;
color: #fff;
padding: 10px;
margin-right: 12px;
}
.grid-step1 p a{
text-decoration:none;
}
.grid-step1 p:last-child{
margin-right:0px;
margin-left: 20px;
}
.grid-step1 p a{
color:#fff;
}
.thanks-book{
border:1px solid #a5a5a5;
float: left;
width: 100%;
}
</style>

<link rel="stylesheet" type="text/css" media="all" href="css/site/cms.css">

<div class="lang-en wider no-subnav thing signed-out winOS" data-twttr-rendered="true" >
 <div id="container-wrapper">
	<div class="container ">
		
		<div class="wrapper-content right-sidebar">			
			<div class="content_text" /*style="width:890px;"*/>
<?php //echo '<pre>'; print_r($invoicedata->result_array()); die; ?>	
        		
<?php if($flash_data != '') { ?>
		<div class="errorContainer" id="<?php echo $flash_data_type;?>">
			<script>setTimeout("hideErrDiv('<?php echo $flash_data_type;?>')", 3000);</script>
			<p><span><?php echo $flash_data;?></span></p>
		</div>
		<?php } ?>
		
	<div id="content" style="padding:0px 20px 20px 20px;">
	    
            <div class="cart-list chept2">
					<div class="thanks-main">
<div class="thanks-book">

<div class="booking">
<h1><?php if($this->lang->line('Thanks for your Booking') != '') { echo stripslashes($this->lang->line('Thanks for your Booking')); } else echo "Thanks for your Booking";?></h1>
<h5><?php if($this->lang->line('We are glad that you choose our service, please review us after your journey') != '') { echo stripslashes($this->lang->line('We are glad that you choose our service, please review us after your journey')); } else echo "We are glad that you choose our service, please review us after your journey";?>.</h5>
</div>
<div class="booking-success">
<h2><?php if($this->lang->line('Booking Success') != '') { echo stripslashes($this->lang->line('Booking Success')); } else echo "Booking Success";?></h2>
<div class="grid-con">
<div class="grid-step1">
<p><a href=""><?php if($this->lang->line('Your Booking Reference Number is') != '') { echo stripslashes($this->lang->line('Your Booking Reference Number is')); } else echo "Your Booking Reference Number is";?>-</a></p>
<p><a href=""><?php echo $bookingId; ?></a></p>
</div>
<div class="grid-step1">
<p><a href=""><?php if($this->lang->line('Amount paid') != '') { echo stripslashes($this->lang->line('Amount paid')); } else echo "Amount paid";?>-</a></p>
<p><a href=""><?php echo $currencySymbol; ?><?php 
											if($this->session->userdata('currency_type') != "USD")
						                      {
						                      

						                     echo  convertCurrency("USD",$this->session->userdata('currency_type'),$price);

						                     }
											elseif($this->session->userdata('currency_type') == "USD"){
											
						                     	 echo  $price;
						                     }	

											?>
												
											<?php  echo $this->session->userdata('currency_type'); ?> </a></p>


</div></div>
</div>
</div>
</div>
<?php header('Refresh:5; url='.base_url().'experience/all');?>

</div>
	  </div>
</div>
		</div>
		<!-- / wrapper-content -->
	</div>
	<!-- / container -->
</div>

</div>	 
<?php 
     $this->load->view('site/templates/footer');
     ?>
