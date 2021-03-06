<?php
$this->load->view('admin/templates/header.php');
?>
<script src="js/jquery.validate.js"></script>
<script>$(document).ready(function(){$("#addbanner_form").validate(); });</script>
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
						$attributes = array('class' => 'form_container left_label', 'id' => 'addsitemap_form', 'method' => 'post',  'enctype' => 'multipart/form-data');
						echo form_open_multipart('admin/sitemap/insertsitemap',$attributes) 
					?>
                    
					<ul>
						<li>
							<div class="form_grid_12 form_chsfile">
								<label class="field_title" for="banner_image">Sitemap File<span class="req">*</span></label>
								<div class="form_input">
									<input name="sitemap_file" id="sitemap_file" type="file" tabindex="2" class="large tipTop required" title="Please upload Sitemap" accept="xml"/>
									<span class="input_instruction red mrgn_top">To create a sitemap for below link and upload the sitemap.xml file.<br><a href="https://xmlsitemapgenerator.org/" target="_blank">https://xmlsitemapgenerator.org/</a></span>
								</div>
							</div>
						</li>
								
						<li>
							<div class="form_grid_12">
								<div class="form_input">
									<button type="submit" class="btn_small btn_blue" tabindex="5"><span>Submit</span></button>
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
$('#addsitemap_form').validate();
</script>
<?php 
$this->load->view('admin/templates/footer.php');
?>