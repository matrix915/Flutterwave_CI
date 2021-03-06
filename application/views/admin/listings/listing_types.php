<?php
$this->load->view('admin/templates/header.php');
extract($privileges);
//var_dump($listingvalues);die;
?>
<div id="content">
		<div class="grid_container">
			<?php 
				$attributes = array('id' => 'display_form');
				echo form_open('admin/listings/change_list_types_status_global',$attributes) 
			?>
			<div class="grid_12">
				<div class="widget_wrap">
					<div class="widget_top">
						<span class="h_icon blocks_images"></span>
						<h6><?php echo $heading; ?></h6>
						<div style="float: right;line-height:40px;padding:0px 10px;height:39px;">
						<?php 
						if ($allPrev == '1' || in_array('2', $Listing)){?>
							<div class="btn_30_light" style="height: 29px;">
								<a href="javascript:void(0)" onclick="return checkBoxValidationAdmin('Active','<?php echo $subAdminMail; ?>');" class="tipTop" title="Select any checkbox and click here to active records"><span class="icon accept_co"></span><span class="btn_link">Active</span></a>
							</div>
							<div class="btn_30_light" style="height: 29px;">
								<a href="javascript:void(0)" onclick="return checkBoxValidationAdmin('Inactive','<?php echo $subAdminMail; ?>');" class="tipTop" title="Select any checkbox and click here to inactive records"><span class="icon delete_co"></span><span class="btn_link">Inactive</span></a>
							</div>
						<?php 
						}
						if ($allPrev == '1' || in_array('3', $Listing)){
						?>
							<div class="btn_30_light" style="height: 29px;">
								<a href="javascript:void(0)" onclick="return checkBoxValidationAdmin('Delete','<?php echo $subAdminMail; ?>');" class="tipTop" title="Select any checkbox and click here to delete records"><span class="icon cross_co"></span><span class="btn_link">Delete</span></a>
							</div>
						<?php }?>
						</div>
					</div>
					<div class="widget_content">
						<table class="display" id="action_tbl_view"> 
						<thead>
						<tr>
							<th class="center">
								<input name="checkbox_id[]" type="checkbox" value="on" class="checkall">
							</th>
							
							<th class="tip_top" title="Click to sort">
								  Name
							</th>

							<th class="tip_top" title="Click to sort">
								 Type
							</th>
							
							<!--<th class="tip_top" title="Click to sort">
								Label
							</th>-->

							<th class="tip_top" title="Click to sort">
								Status
							</th >
							<th class="tip_top" title="Click to sort"> Action</th>
							
							<!--<th>
								 Action
							</th>-->
						</tr>
						</thead>
						<tbody>
						<?php 
						//if ($listingvalues->num_rows() > 0){
							foreach ($listingvalues as $row){
								if (strtolower($row->name) != 'price')
								{
						?>
						<tr>
							<td class="center tr_select ">					<?php  							if($row->name != 'accommodates' && $row->name != 'can_policy'  && $row->name != 'minimum_stay' && $row->name !='guest_capacity') {?>
								<input name="checkbox_id[]" type="checkbox" value="<?php echo $row->id;?>">							<?php } ?>
							</td>
							<td class="center">
								<?php echo $row->labelname;?>
							</td>
							<td class="center">
								<?php echo $row->type;?>
							</td>
							
							<!--<td class="center">
								<?php echo $row->labelname;?>
							</td>-->

							<td class="center">
								<?php // echo $row->status;?>
								<?php 
							if ($allPrev == '1' || in_array('2', $Listing)){

								if($row->name != 'accommodates' && $row->name != 'can_policy'  && $row->name != 'minimum_stay' && $row->name !='guest_capacity') {

									$mode = ($row->status == 'Active')?'0':'1';
									if ($mode == '0'){
								?>
									<a title="Click to inactive" class="tip_top" href="javascript:confirm_status('admin/listings/change_listings_status/<?php echo $mode;?>/<?php echo $row->id;?>');"><span class="badge_style b_done"><?php echo $row->status;?></span></a>
								<?php
									}else {	
								?>
									<a title="Click to active" class="tip_top" href="javascript:confirm_status('admin/listings/change_listings_status/<?php echo $mode;?>/<?php echo $row->id;?>')"><span class="badge_style"><?php echo $row->status;?></span></a>
							<?php 
									}
								}
								else{
									?>
									<span class="badge_style b_done"><?php echo $row->status;?></span>
									<?php
								}
							}else {
							?>
							<span class="badge_style b_done"><?php echo $row->status;?></span>
							<?php }?>

							</td>
							<td class="center">
							<?php
							if($row->type!='text'){
								?>
								<?php if ($allPrev == '1' || in_array('1', $Listing)){?>
								<span><a class="action-icons c-add" href="admin/listings/add_new_child_fields/<?php echo $row->id;?>" title="Add Child">Add Child</a></span>

								<?php
								}
							}	

							?>	

							<?php //if($row->name !== accommodates && $row->name !== can_policy) {?>
							
							<?php if ($allPrev == '1' || in_array('2', $Listing)){?>
								<span><a class="action-icons c-edit" href="admin/listings/add_new_attribute/<?php echo $row->id;?>" title="Edit">Edit</a></span>
							<?php }?>
							<?php 
							if($row->name != 'accommodates' && $row->name != 'can_policy'  && $row->name != 'minimum_stay' && $row->name !='guest_capacity') {?>
							<?php if ($allPrev == '1' || in_array('3', $Listing)){?>	
								<span><a class="action-icons c-delete" href="javascript:confirm_delete('admin/listings/delete_list/<?php echo $row->id;?>')" title="Delete">Delete</a></span>
							<?php } }?>
							</td>
						</tr>
						<?php 
								}
							}
						//}
						?>
						</tbody>
						<tfoot>
						<tr>
							<th class="center">
								<input name="checkbox_id[]" type="checkbox" value="on" class="checkall">
							</th>
							
							<th>
								  Name
							</th>

							<th>
								 Type
							</th>
							
							
							<!--<th>
								Label
							</th>-->
							<th>
								Status
							</th>
							
							<th>
								 Action
							</th> 
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