<?php
$this->load->view('admin/templates/header.php');
?>
<div id="content">
		<div class="grid_container">
			<div class="grid_12">
				<div class="widget_wrap">
					<div class="widget_top">
						<span class="h_icon list"></span>
						<h6><?php echo $heading;?></h6>
                        
					</div>
					<div class="widget_content">
					<?php 
						$attributes = array('class' => 'form_container left_label', 'id' => 'addlg_form','accept-charset'=>'UTF-8');
						echo form_open_multipart('admin/multilanguage/add_lg_process',$attributes) 
					?>
                    
						<ul>
	 							
							<li>
							<div class="form_grid_12">
							<label class="field_title" for="name">Language Name <span class="req">*</span></label>
							<div class="form_input">
								<input name="name" id="name" type="text" tabindex="1" class="required large tipTop" title="Please enter the language name"/>
							</div>
							</div>
							</li>

							<li>
							<div class="form_grid_12">
							<label class="field_title" for="lang_code">Language Code <span class="req">*</span></label>
							<div class="form_input">
								<input name="lang_code" id="lang_code" type="text" tabindex="2" class="required large tipTop" title="Please enter the language code"/>
							</div>
							</div>
							</li>
						
						
							<li>
								<div class="form_grid_12">
									<label class="field_title" for="lang_order">Order <span class="req">*</span></label>
									<div class="form_input">
										<input name="language_order"  onkeypress="return check_for_num(event)" id="language_order" type="text" tabindex="2" class="required large tipTop" title="Please enter the language order"/>
									</div>
								</div>
							</li>
                        	
								<li>
								<input type="hidden" name="status" value="Inactive"/>
								<div class="form_grid_12">
									<div class="form_input">
										<button type="submit" class="btn_small btn_blue" tabindex="3"><span>Submit</span></button>
									</div>
								</div>
								</li>
							</ul>
                    
						</form>
					</div>
				</div>
			</div>
		</div>
		<span class="clear"></span>
	</div>
</div>
<script type="text/javascript">
$('#addlg_form').validate();
</script>
<script>
function check_for_num(evt)
{
	evt = (evt) ? evt : window.event;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if (charCode > 31 && (charCode < 48 || charCode > 57))
	{
		return false;
	}
	return true;
}
</script>
<?php 
$this->load->view('admin/templates/footer.php');
?>