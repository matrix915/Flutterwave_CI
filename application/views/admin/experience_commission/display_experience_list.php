<?php
$this->load->view('admin/templates/header.php');
extract($privileges);
?>

<div id="content">
		<div class="grid_container">
			<?php 
				$attributes = array('id' => 'display_form');
				echo form_open('admin/order/change_order_status_global',$attributes) 
			?>
			<div class="grid_12">
				<div class="widget_wrap">
					<div class="widget_top">
						<span class="h_icon blocks_images"></span>
						<h6><?php echo $heading?></h6>
						
					</div>
					<div class="widget_content">
						<table class="display display_tbl" id="subadmin_tbl">
						<thead>
						<tr>
							<th class="center">
								<input name="checkbox_id[]" type="checkbox" value="on" class="checkall">
							</th>
                            <th class="tip_top" title="Click to sort">
								<span style="padding:10px"> S No</span>
							</th>
							<th class="tip_top" title="Click to sort">
								<span style="padding:5px 10px 5px 5px">Booking No</span>
							</th>
							<th class="tip_top" title="Click to sort">
                            	Guest Email ID
                            </th>
							<th class="tip_top" title="Click to sort">
                            	Product Title
                            </th>
                            <th class="tip_top" title="Click to sort">
								 Date Added		
							</th>
							
							<th>
                            	Total Discount
                            </th>
							<th>
                            	Guest Service Amt
                            </th>
							<th>
                            	Cancellation Amt
                            </th>
							<th>
                            	Actual Profit
                            </th>
							<th>
                            	Used Wallet Amt
                            </th>
							<th>
                            	paid
                            </th>
							<th>
                            	Balance
                            </th>
							<th>
								<span style="padding:10px">Product Title</span>
							</th>
							<th>
                            	Booking Status
                            </th>
                            
                           

							<!-- <th>
								 Action
							</th> -->
						</tr>
						</thead>
						<tbody>
						<?php 
						
						if (count($product) > 0){
						$i=1;
							foreach ($product as $value){
								
								foreach($value as $pro)
								{
								$totlessDays = $this->config->item ('cancel_hide_days_experience');
					$minus_checkin =  strtotime("-".$totlessDays."days",strtotime($pro->checkin));
							$checkinBeforeDay = date('Y-m-d',$minus_checkin);
							$current_date = date('Y-m-d');

				  
								$currencyPerUnitSeller=$pro->currencyPerUnitSeller;
								if($checkinBeforeDay <= $current_date){	
						?>
						<tr>
							<td class="center tr_select ">
								<input name="checkbox_id[]" type="checkbox" value="<?php echo $pro->id;?>">
							</td>
                            <td class="center">
								<?php echo $i;?>
							</td>
							<td class="center">
								<?php echo $pro->Bookingno;?>
							</td>
							<td class="center">
								<?php echo $pro->email;?>
							</td>
							<td class="center">
								<?php echo $pro->experience_title;?>
							</td>
							<td class="center">
								<?php echo date('d-m-Y',strtotime($pro->dateAdded));?>
							</td>
							<td class="center">
							
								<?php 
								
								
								if($pro->currencycode != $admin_currency_code){
									$totAmount=customised_currency_conversion($currencyPerUnitSeller,$pro->total_amount);
								}else{
									$totAmount=$pro->total_amount; 
								}
								echo $admin_currency_symbol.' '.$totAmount;
								
								//echo $admin_currency_symbol.' '.$pro->total_amount; ?>
							</td>
							
							<td class="center">
								<?php

								
								
								
								if($pro->currencycode != $admin_currency_code){
									$GuestFee=customised_currency_conversion($currencyPerUnitSeller,$pro->guest_fee);
								}else{
									$GuestFee=$pro->guest_fee; 
								}
								
								echo $admin_currency_symbol.' '.$GuestFee;
								
								//echo $admin_currency_symbol.' '.$pro->guest_fee;
								
								
								?>
							</td>
							
							
							<td class="center">
							<?php $cancel_amount = $pro->subTotal/100 * $pro->exp_cancel_percentage; 
							
					
							if ($pro->cancelled=='Yes'){
								if($pro->currencycode != $admin_currency_code){
									$canAmount=customised_currency_conversion($currencyPerUnitSeller,$cancel_amount);
								}else{
									$canAmount=$cancel_amount; 
								}
								
							}else{
								$canAmount='0.00'; 
							}
								
								echo $admin_currency_symbol.' '.$canAmount;
							
							
								 /*   $cancel_amount = $pro->subTotal/100 * $pro->cancel_percentage; 
								   echo $admin_currency_symbol.' '.number_format($cancel_amount); */


								 ?>
							</td>
							
							
							<td class="center">
							<?php 
							
							 $act_pro = ($pro->guest_fee + $pro->host_fee);
							 
								if($pro->currencycode != $admin_currency_code){
									$profit=customised_currency_conversion($currencyPerUnitSeller,$act_pro);
								}else{
									$profit=$act_pro; 
								}
								
								echo $admin_currency_symbol.' '.$profit;
							
							
							
							/*  $act_pro = ($pro->guest_fee + $pro->host_fee) - $pro->coupon_discount;
							 echo $admin_currency_symbol.' '.number_format($act_pro); */
							
							?>
							</td>
							<td class="center">
							
								<?php
								if($pro->booking_walletUse != '')
								{	

							
										echo  $admin_currency_symbol.' '.number_format($pro->booking_walletUse);
										
										
								}else
								{
									echo  $admin_currency_symbol.''."0.00";
								}
								?>
							</td>
							<td class="center">
							
								<?php if($pro->paid_status == 'yes')
								{
									
								$cancel_amount = $pro->subTotal/100 * $pro->exp_cancel_percentage; 	
		
								if($pro->cancelled=='Yes'){
									
									$paid_is=$pro->payable_amount-$cancel_amount;
									
								}else{
									$paid_is=$pro->payable_amount;
								}
			
								
								if($pro->currencycode != $admin_currency_code){
									$ActPaid=customised_currency_conversion($currencyPerUnitSeller,$paid_is);
								}else{
									$ActPaid=$paid_is; 
								}
								
									
									
								echo $admin_currency_symbol.''.$ActPaid;
								//echo $admin_currency_symbol.''.$pro->payable_amount;
								
								}else
								{
									echo $admin_currency_symbol.''."0.00";
								}?>
							</td>
							<td class="center">
								<?php if($pro->paid_status == 'no')
								{
									
								/* $Balence=$pro->payable_amount;
									
								if($pro->currencycode != $admin_currency_code){
									$BalAmount=customised_currency_conversion($currencyPerUnitSeller,$Balence);
								}else{
									$BalAmount=$Balence; 
								}
								
								echo $admin_currency_symbol.' '.$BalAmount; */
						
								$cancel_amount = $pro->subTotal/100 * $pro->exp_cancel_percentage; 

								if($pro->cancelled=='Yes'){
									
									$Balene=$pro->payable_amount-$cancel_amount;
									
								}else{
									$Balene=$pro->payable_amount;
								}
			
								if($pro->currencycode != $admin_currency_code){
									$ActBalence=customised_currency_conversion($currencyPerUnitSeller,$Balene);
								}else{
									$ActBalence=$Balene; 
								}
								
								
								
								
								
									
								echo $admin_currency_symbol.''.$ActBalence;
								//echo $admin_currency_symbol.''.$pro->payable_amount;
								
						
								}else
								{
									echo $admin_currency_symbol.''."0.00";
								}
								?>
							</td>
							<td class="center">
								<?php echo $pro->experience_title;?>
							</td>
							<td class="center">
								<?php echo $pro->booking_status;?>
							</td>
   							
							
						</tr>
                        
						<?php 
						
								}	
							 }
							$i++;
						}
						}
						?>
						</tbody>
						<tfoot>
						<tr>
							<th class="center">
								<input name="checkbox_id[]" type="checkbox" value="on" class="checkall">
							</th>
                             <th class="tip_top" title="Click to sort">
								 S No
							</th>
							<th class="tip_top" title="Click to sort">
								Booking No
							</th>
							<th class="tip_top" title="Click to sort">
                            	Guest Email ID
                            </th>
							<th>
                            	Product Title
                            </th>
							<th class="tip_top" title="Click to sort">
								 Date Added
							</th>
							<th>
                            	Total Amount
                            </th>
							
							<th>
                            	Guest Service Amt
                            </th>
							<th>
                            	Cancellation Amt
                            </th>
							<th>
                            	Actual Profit
                            </th>
							<th>
                            	Used Wallet Amt
                            </th>
                            <th>
                            	paid
                            </th>
							<th>
                            	Balance
                            </th>
							
							<th>
								Product Title
							</th>
    
						
   							<th class="tip_top" title="Click to sort">
								Booking Status
							</th>

						<!--	<th>
								 Action
							</th> -->
						</tr>
						</tfoot>
						</table>
					</div>
				</div>
			</div>
			<input type="hidden" name="statusMode" id="statusMode"/>
			<input type="hidden" name="SubAdminEmail" id="SubAdminEmail"/>
		</form>	
			
		</div>
		<span class="clear"></span>
	</div>
</div>
<?php 
$this->load->view('admin/templates/footer.php');
?>