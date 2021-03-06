<?php
$this->load->view('admin/templates/header.php');
?>
<div id="content">
		<div class="grid_container">
			<div class="grid_12">
				<div class="widget_wrap">
					<div class="widget_top">
						<span class="h_icon list"></span>
						<h6>Edit Location</h6>
                        <div id="widget_tab">
              				<ul>
               					 <li><a href="#tab1" class="active_tab">Content</a></li>
               					 <li><a href="#tab2">SEO</a></li>
             				 </ul>
            			</div>
					</div>
					<div class="widget_content">
					<?php 
						$attributes = array('class' => 'form_container left_label', 'id' => 'commentForm' ,'enctype' => 'multipart/form-data');
						echo form_open('admin/location/insertEditLocation',$attributes) 
					?> 		
                    	<div id="tab1">
	 						<ul>
                                <li>
								<div class="form_grid_12">
									<label class="field_title" for="location_name">Country Name <span class="req">*</span></label>
									<div class="form_input">
                                    <input name="name" style=" width:295px" id="name" value="<?php echo $location_details->row()->name;?>" type="text" tabindex="1" class="required tipTop" title="Please enter the country name"/><span id="name_valid" style="color:#f00;display:none;">Only Characters are allowed!</span>
									</div>
								</div>
								</li>
                                <li>
								<div class="form_grid_12">
									<label class="field_title" for="iso_code2">Country Code</label>
									<div class="form_input">
                                    <input name="country_code" style=" width:295px" id="country_code" value="<?php echo $location_details->row()->country_code;?>" type="text" tabindex="1" class="tipTop" title="Please enter the iso_code2"/>
									</div>
								</div>
								</li>
                                <li>
								<div class="form_grid_12">
									<label class="field_title" for="iso_code3">Country Mobile Code</label>
									<div class="form_input">
                                    <input name="country_mobile_code" style=" width:295px" id="country_mobile_code" value="<?php echo $location_details->row()->country_mobile_code;?>" type="text" tabindex="1" class=" tipTop" title="Please enter the iso_code3"/>
									</div>
								</div>
								</li>
                                <li>
								<div class="form_grid_12">
									<label class="field_title" for="language_code">Language</label>
									<div class="form_input">
                                    <select name="language_code">
									<option value="">--Select--</option>
                                    <?php
									
									 
									  
										if($LanguageList->num_rows() > 0){
											foreach($LanguageList->result() as $Row){
												$SelVal='';
												 $SelVal = ($location_details->row()->language_code==$Row->lang_code)?'selected="selected"':'';
												echo '<option value="'.$Row->lang_code.'" '.$SelVal.'>'.$Row->name.'</option>';
											}
										}
									
									
									 ?>
                                    </select>
								</div>
								</div>
								</li>
								<!--<li>
								<div class="form_grid_12">
									<label class="field_title" for="currency_symbol">Currency Symbol<span class="req">*</span></label>
									<div class="form_input">
                                    <textarea name="currency_symbol" class="" id="currency_symbol" rows="" cols=""><?php echo $location_details->row()->currency_symbol;?></textarea>
								</div>
								</div>
								</li>
                                <li>
								<div class="form_grid_12">
									<label class="field_title" for="currency_type">Currency<span class="req">*</span></label>
									<div class="form_input">
                                    <input name="currency_type" style=" width:295px" id="currency_type" type="text" tabindex="1" class="required tipTop" value="<?php echo $location_details->row()->currency_type;?>" title="Please enter the currency symbol"/>
									</div>
								</div>
								</li>
                                <li>
								<div class="form_grid_12">
									<label class="field_title" for="logo">Logo<span class="req">*</span></label>
									<div class="form_input">
                                    <input name="logo" id="logo" type="file" tabindex="5" class="large tipTop" title="Please select the logo image"/>
									</div>
								</div>
								</li>
                                
                                <li>
								<div class="form_grid_12">
									<label class="field_title">Logo Image</label>
									<div class="form_input">
                                    <?php if($location_details->row()->logo==''){ ?>
                                    <img src="images/product/dummyProductImage.jpg" width="50px" height="50px"  />
                                    <?php }else{ ?>
                                    <img src="images/location/<?php echo $location_details->row()->logo;?>" width="50px" height="50px"  />
                                    <?php } ?>
									</div>
								</div>
								</li>
                                
                                <li>
								<div class="form_grid_12">
									<label class="field_title" for="thumb">Image<span class="req">*</span></label>
									<div class="form_input">
                                   <input name="thumb" id="thumb" type="file" tabindex="5" class="large tipTop" title="Please select the thumb image"/>
									</div>
								</div>
								</li>
                                
                                <li>
								<div class="form_grid_12">
									<label class="field_title">image image</label>
									<div class="form_input">
                                     <?php if($location_details->row()->thumb==''){ ?>
                                    <img src="images/product/dummyProductImage.jpg" width="50px" height="50px"  />
                                    <?php }else{ ?>
                                    <img src="images/location/<?php echo $location_details->row()->thumb;?>" width="50px" height="50px"  />
                                    <?php } ?>
                                    
                                    
                                    
									</div>
								</div>
								</li>
                                <li>
                                  <div class="form_grid_12">
                                    <label class="field_title" for="description">Description</label>
                                    <div class="form_input">
                                      <textarea name="description" id="description" class="large tipTop mceEditor" title="Please enter the description"><?php echo $location_details->row()->description;?></textarea>
                                    </div>
                                  </div>
                                </li>-->
                                <li>
								<div class="form_grid_12">
									<label class="field_title" for="admin_name">Status <span class="req">*</span></label>
									<div class="form_input">
										<div class="active_inactive">
                                        <input type="checkbox" name="status" <?php if ($location_details->row()->status == 'Active'){echo 'checked="checked"';}?> id="active_inactive_active" class="active_inactive"/>
										</div>
									</div>
								</div>
								</li>
                                
                               <li style="display: none;">
								<div class="form_grid_12">
									<label class="field_title" for="admin_name">Default <span class="req">*</span></label>
									<div class="form_input">
									<?php if($location_details->row()->status != 'Yes'){?>
										<div class="active_inactive">
                                        
											<input type="checkbox" tabindex="7" name="currency_default" id="active_inactive_active" class="active_inactive" <?php if ($location_details->row()->currency_default == 'Yes'){echo 'checked="checked"';}?>  />
                                            
										</div>
										<?php }else{ ?>
                                        <button type="button" style="background-position: 0 -434px; cursor:default;"  class="btn_small btn_blue" tabindex="4"><span>Active</span></button>
                                         <div class="active_inactive" style="display:none;">
                                        
											<input type="checkbox" tabindex="7" name="currency_default" id="active_inactive_active" class="active_inactive" <?php if ($location_details->row()->currency_default == 'No'){echo 'checked="checked"';}?>  />
                                            
										</div>
										<?php } ?>
									</div>
								</div>
								</li>
                               
								<input type="hidden" name="location_id" value="<?php echo $location_details->row()->id;?>"/>
								<li>
								<div class="form_grid_12">
									<div class="form_input">
										<button type="submit" class="btn_small btn_blue" tabindex="4"><span>Update</span></button>
										
										<a href="<?php echo base_url();?>admin/location/display_location_list" ><button class="btn_small btn_blue" type="button">Back</button></a>
									</div>
									
								</div>
								</li>
							</ul>
                        </div>
                        <div id="tab2">
              <ul>
                <li>
                  <div class="form_grid_12">
                    <label class="field_title" for="meta_title">Meta Title</label>
                    <div class="form_input">
                      <input name="meta_title" id="meta_title" value="<?php echo $location_details->row()->meta_title;?>" type="text" tabindex="1" class="large tipTop" title="Please enter the page meta title"/>
                    </div>
                  </div>
                </li>
                <li>
                  <div class="form_grid_12">
                    <label class="field_title" for="meta_tag">Meta Keyword</label>
                    <div class="form_input">
                      <textarea name="meta_keyword" id="meta_keyword" tabindex="2" class="large tipTop" title="Please enter the page meta keyword"><?php echo $location_details->row()->meta_keyword;?></textarea>
                    </div>
                  </div>
                </li>
                <li>
                  <div class="form_grid_12">
                    <label class="field_title" for="meta_description">Meta Description</label>
                    <div class="form_input">
                      <textarea name="meta_description" id="meta_description" tabindex="3" class="large tipTop" title="Please enter the meta description"><?php echo $location_details->row()->meta_description;?></textarea>
                    </div>
                  </div>
                </li>
              </ul>
             <ul><li><div class="form_grid_12">
				<div class="form_input">
					<button type="submit" class="btn_small btn_blue" tabindex="4"><span>Submit</span></button>
					<a href="<?php echo base_url();?>admin/location/display_location_list" ><button class="btn_small btn_blue" type="button">Back</button></a>
				</div>
			</div></li></ul>
			</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<span class="clear"></span>
	</div>
</div>

<script type="text/javascript">
	$('#commentForm').validate();
	
</script>
<?php 
$this->load->view('admin/templates/footer.php');
?>

<script>
/* $("#name").on('keyup', function(e) {
    var val = $(this).val();
   if (val.match(/[^a-zA-Z.,|-\s()&/]/g)) {
	   document.getElementById("name_valid").style.display = "inline";
	   $("#name_valid").fadeOut(5000);
	   $("#name").focus();
       $(this).val(val.replace(/[^a-zA-Z.,|-\s()&/]/g, ''));
   }
}); */
</script>