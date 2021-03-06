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
						<table class="display display_tbl display_tbl_fnt " id="subadmin_tblListing">
						<thead>
						<tr>
							<th class="center">
								<input name="checkbox_id[]" type="checkbox" value="on" class="checkall">
							</th>
                         <!--   <th class="tip_top" title="Click to sort">
								 PaymentID
							</th> -->
							<th class="tip_top" title="Click to sort">
								 Host Email
							</th>
							
							<th class="tip_top" title="Click to sort">
								 Property title
							</th>
                            <th class="tip_top" title="Click to sort">
								 Payment Date		
							</th>
							<th>
								Total(Host)
							</th>
							<th>
								Total(Admin)
							</th>
							
							<th class="tip_top" title="Click to sort">
								 Commission
							</th>
							<th class="tip_top" title="Click to sort">
								 Payable amount(Host)		
							</th>
							<th class="tip_top" title="Click to sort">
								 Payable amount(Admin)		
							</th>
							<th class="tip_top" title="Click to sort">
								 Transaction ID
							</th>
                            <th>
                            	Payment Type
                            </th>
                            
   							<th class="tip_top" title="Click to sort">
								Status
							</th>

						<!--	<th>
								 Action
							</th> -->
						</tr>
						</thead>
						<tbody>
						<?php 
						if ($listingorderList->num_rows() > 0){
							//print_r($listingorderList->result());
							foreach ($listingorderList->result() as $row){
						?>
						<tr>
							<td class="center tr_select ">
								<input name="checkbox_id[]" type="checkbox" value="<?php echo $row->id;?>">
							</td>
                           <!-- <td class="center">
								<?php echo $row->id;?>
							</td> -->
							<td class="center">
								<?php echo $row->email;?>
							</td>
							<td class="center">
								<?php echo $row->prd_name;?>
							</td>
							
   							<td class="center">
								<?php echo date('d-m-Y',strtotime($row->created));?>
							</td>
							<td class="center">
								<?php echo $row->amount." ".$row->currency_code_host;?>
							</td>
							<td class="center">
								 <?php 

								$unitPerCurrencyHost=$row->unitPerCurrencyHost;
								//echo 'dd';
								if($admin_currency_code!=$row->currency_code_host){
									//echo convertCurrency($row->currency_code,$admin_currency_code,$row->total);
									echo $admin_currency_symbol.' '.customised_currency_conversion($unitPerCurrencyHost,$row->amount);
								}else
							 		echo $admin_currency_symbol.' '.$row->amount;
							
								 ?>
							</td>
							
							<td class="center">
								<?php echo $row->commission." (".$row->commission_type.")";?>
							</td>
							
							
							<td class="center">
							
								<?php //echo $row->hosting_price." ".$row->currency_code_host;?>
								
								<?php
								
								if($admin_currency_code!=$row->currency_code_host){
									$theHostingPrice=$row->hosting_price*$unitPerCurrencyHost;
									echo  $theHostingPrice." ".$row->currency_code_host;
								}else
							 		echo $admin_currency_symbol.' '.$row->hosting_price;													
								?>
								
							</td>
							
							
							
							<td class="center">
								<?php 
								
				echo $admin_currency_symbol." ".$row->hosting_price;
								
								?>
							</td>


							<td class="center">
							
								<?php if($row->paypal_txn_id != '')
								{
								echo $row->paypal_txn_id; 
								}
								else
								{
								echo "---";	
								}
								?>
							</td>
							
							<td class="center">
								 <?php echo $row->payment_type;?>
							</td>
							<td class="center">
							<span class="badge_style b_done"><?php echo $row->payment_status;?></span>
							</td>
						<!--	<td class="center">	                            
                           		<a href="order-review/<?php echo $row->paypal_txn_id;?>" class="tipTop" title="View Comments"><span class="action-icons c-suspend" style="cursor:pointer;"></span></a>

							</td> -->
						</tr>
                        
						<?php 
							}
						}
						?>
						</tbody>
						<tfoot>
						<tr>
							<th class="center">
								<input name="checkbox_id[]" type="checkbox" value="on" class="checkall">
							</th>
                         <!--   <th class="tip_top" title="Click to sort">
								 PaymentID
							</th> -->
							<th class="tip_top" title="Click to sort">
								 Host Email
							</th>
							
							<th class="tip_top" title="Click to sort">
								 Property title
							</th>
                            <th class="tip_top" title="Click to sort">
								 Payment Date		
							</th>
							<th>
								Total(Host)
							</th>
							<th>
								Total(Admin)
							</th>
							
							<th class="tip_top" title="Click to sort">
								 Commission
							</th>
							<th class="tip_top" title="Click to sort">
								 Payable amount(Host)		
							</th>
							<th class="tip_top" title="Click to sort">
								 Payable amount(Admin)		
							</th>
							<th class="tip_top" title="Click to sort">
								 Transaction ID
							</th>
							
                            <th>
                            	Payment Type
                            </th>
                            
   							<th class="tip_top" title="Click to sort">
								Status
							</th>

							<!-- <th>
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