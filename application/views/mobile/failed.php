<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php echo $this->config->item('email_title'); ?> - Payment Failed</title>
		<link rel="stylesheet" href="<?php echo base_url(); ?>css/mobile/app-style.css" type="text/css" media="all" />
	</head>
	<body>
		<section>
			<div class="app-shipping">
				<div class="main">
					<ul class="app-shipping-level level-3">		
						<li>Shipping</li>
						<li>Payment</li>
						<li class="active">Failed</li>
					</ul>
				</div>
			</div>
			<div class="shipping_address">
					<div class="main">		
						<div class="app-content-box">
						<h1>Your Payment Failed - <?php echo urldecode($errors); ?><a class="close_btn" href="<?php echo base_url().'mobile/payment/pay-failed'; ?>"></a></h1>
						<div class="payment-success"><img src="<?php echo base_url(); ?>css/mobile/images/failed.png" alt="failed" title="failed" /></div>
					</div>			
					</div>	
			</div>
			<?php $this->output->set_header('refresh:2;url='.base_url().'mobile/payment/pay-failed'); ?>
		</section>
	</body>
</html>
