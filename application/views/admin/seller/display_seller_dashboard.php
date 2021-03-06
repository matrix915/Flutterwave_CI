<?php
$this->load->view('admin/templates/header.php');
extract($privileges);
?>
<?php 
	$active_users = $inactive_users = 0;
	if ($usersList->num_rows()>0){
		foreach ($usersList->result() as $row){
			$status = strtolower($row->status);
			if ($status == 'active'){
				$active_users++;
			}else {
				$inactive_users++;
			}
		}
	}
?>

<?php 
	$active_users1 = $archivedUser1 = $inactive_users1 = 0;
	if ($usersList->num_rows()>0){
		foreach ($usersList->result() as $row){
				$status = strtolower($row->status);
			if ($status == 'active' && $row->host_status=='0'){
				$active_users1++;
			}else if ($row->host_status=='1' ){
				$archivedUser1++;
			}else if ($status == 'inactive' &&  $row->host_status=='0' ){
				$inactive_users1++;
			}
		}
	}
	
?>


<script type="text/javascript">
/*=================
CHART 5
===================*/
$(function(){
  plot2 = jQuery.jqplot('chart5',
    [[['Active',<?php echo $active_users; ?>],['Inactive', <?php echo $inactive_users; ?>]]],
    {
      title: ' ',
      seriesDefaults: {
        shadow: false,
        renderer: jQuery.jqplot.PieRenderer,
        rendererOptions: {
          startAngle: 180,
          sliceMargin: 4,
          showDataLabels: true }
      },
		grid: {
         borderColor: '#ccc',     // CSS color spec for border around grid.
        borderWidth: 2.0,           // pixel width of border around grid.
        shadow: false               // draw a shadow for grid.
    },
      legend: { show:true, location: 'w' }
    }
  );
});

</script>
<div id="content">
		<div class="grid_container">
			
            <div class="grid_6">
				<div class="widget_wrap">
					<div class="widget_top">
						<span class="h_icon list"></span>
						<h6>Hosts Dashboard</h6>
					</div>
					<div class="widget_content">
					<?php if($usersList->num_rows() >0) { ?>
					<h4><?php echo $usersList->num_rows();?> Host registered in this site</h4>	<br>
					
					<div style="margin-left:20px">
						<small>Active Host : <?php echo $active_users1; ?></small><br>
						<small>In Active Host : <?php echo $inactive_users1; ?></small><br>
						<small>Archived Host : <?php echo $archivedUser1; ?></small>
					</div>
					
					
						<?php } else { ?>
						<h4>No host registered in this site</h4>	
					<?php }?>
					<!--<p>
							 <?php echo $usersList->num_rows();?> users registered in this site
						</p>-->
						<div id="chart5" class="chart_block">
						</div>
					</div>
				</div>
			</div>
            
            
            
            
            <!--<div class="grid_6">
				<div class="widget_wrap">
					<div class="widget_top">
						<span class="h_icon graph"></span>
						<h6><?php echo $heading;?></h6>
					</div>
					<?php 
						$active_users = $inactive_users = 0;
						if ($usersList->num_rows()>0){
							foreach ($usersList->result() as $row){
								$status = strtolower($row->status);
								if ($status == 'active'){
									$active_users++;
								}else {
									$inactive_users++;
								}
							}
						}
					?>
					<div class="widget_content">
						<div class="stat_block">
							<h4> <?php echo $usersList->num_rows();?> users registered in this site</h4>
							<table>
							<tbody>
							<tr>
								<td>
									Active Users
								</td>
								<td>
									<?php echo $active_users;?>
								</td>
							</tr>
							<tr>
								<td>
									Inactive Users
								</td>
								<td>
									<?php echo $inactive_users;?>
								</td>
							</tr>
							</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>-->
            
			<div class="grid_6">
				<div class="widget_wrap">
					<div class="widget_top">
						<span class="h_icon image_1"></span>
						<h6>Recent Host</h6>
					</div>
					<div class="widget_content">
						<table class="wtbl_list">
						<thead>
						<tr>
							<th>
								 Full Name
							</th>
							<th>
								 Status
							</th>
							<th>
								 Email-ID
							</th>
							<th>
								 Image
							</th>
<!-- 							<th>
								 Status
							</th>
 -->						</tr>
						</thead>
						<tbody>
						<?php 
						if ($usersList->num_rows() > 0){
							$result = $usersList->result_array();
							for ($i=0;$i<5;$i++){
								if (isset($result[$i]) && is_array($result[$i])){
						?>
						<tr class="tr_even">
							<td>
								 <?php echo ucfirst($result[$i]['firstname']).' '.ucfirst($result[$i]['lastname']);?>
							</td>
							<td>
								 <?php echo $result[$i]['status'];?>
							</td>
							<td>
								 <?php echo $result[$i]['email'];?>
							</td>
							<td>
								<div class="widget_thumb">
								<?php if ($result[$i]['image'] != ''){?>
								 	<img width="40px" height="40px" src="<?php echo base_url();?>images/users/<?php echo $result[$i]['image'];?>" />
								<?php }else {?>
									 <img width="40px" height="40px" src="<?php echo base_url();?>images/users/user-thumb1.png" />
								<?php }?>
								</div>
							</td>
<!-- 							<td>
							<?php if (strtolower($result[$i]['status']) == 'active'){?>
								 <span class="badge_style b_done"><?php echo $result[$i]['status'];?></span>
							<?php }else {?>
								 <span class="badge_style b_active"><?php echo $result[$i]['status'];?></span>
							<?php }?>
							</td>
 -->						</tr>
						<?php 
								}
							}
						}else {
						?>
						<tr>
							<td colspan="5" align="center">No Host Available</td>
						</tr>
						<?php }?>
						</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<span class="clear"></span>
	</div>
</div>
<?php 
$this->load->view('admin/templates/footer.php');
?>