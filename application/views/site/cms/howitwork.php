<?php $this->load->view('site/templates/header'); ?>
<section>
  <div class="profile-page">
  <div class="container">


    	  <div class="col-sm-12">
    	  
    	  	<div class="how-it-box">
    	  	
    	  		<div class="how-top-box">
    	
    	  		<input type="text" placeholder="<?php if($this->lang->line('Start Booking Today') != '') { echo stripslashes($this->lang->line('Start Booking Today')); } else echo "Start Booking Today";?>" class="text-scroll" id="autocomplete" name="city">
    	  		
    	  	
    	  	
    	  		<button class="text-submit-btn" onclick="search_city(this)"><?php if($this->lang->line('Search') != '') { echo stripslashes($this->lang->line('Search')); } else echo "Search";?> </button>
    	  		</div>
    	  		
    	  		
    	  		
    	  		
    	  		
    	  		<div class="advantages">
    	  			 
    	  			<?php  echo stripslashes($cmshowitwork->row()->description);?>

    	  		</div>

          </div>
  

         <div class="col-sm-12">
      
      			<div class="how-links"><?php if($this->lang->line('Any questions about being a guest, check out our') != '') { echo stripslashes($this->lang->line('Any questions about being a guest, check out our')); } else echo "Any questions about being a guest, check out our";?> <a href="pages/faq"><?php if($this->lang->line('FAQs') != '') { echo stripslashes($this->lang->line('FAQs')); } else echo "FAQs";?></a> <?php if($this->lang->line('or') != '') { echo stripslashes($this->lang->line('or')); } else echo "or";?> <a href="contact-us"><?php if($this->lang->line('drop us a line') != '') { echo stripslashes($this->lang->line('drop us a line')); } else echo "drop us a line";?></a></div>
      
 
  		 </div>


</section>

<script>
$(function() {
	$( "#autocomplete,#city_landing" ).autocomplete({
	source: function( request, response ) {
	        $.ajax({
	            url: "<?php echo base_url();?>site/landing/home_search_auto",
	            dataType: "json",
	            data: {
				term : request.term,
	            tableName : "city"
				},
	            success: function(data) {
	                    response(data);
	            }
	        });
	    },
		change: function (event, ui) {
	            if (!ui.item) {
	                this.value = '';
	            }
	        },
			select: function(event,ui){
			
			var city=ui.item.value;
			city=city.replace(" ", "+");
			if($(this).attr('id')=='autocomplete')
			{
			window.location='<?php echo base_url()?>property?city='+city+'';
			}
			
	        
	    },
		min_length: 10,
	    delay: 100

	});
	});


	</script>
	
	
<script>
function change_next(evt){
	$(evt).parent().prev().find('li.active').next().find('a').trigger('click');

}
function change_previous(evt)
{
	$(evt).parent().prev().find('li.active').prev().find('a').trigger('click');
}

function search_city(evt)
{
	var sear=$(evt).prev().val();
//alert(sear);return false;
	window.location='<?php echo base_url()?>property?city='+sear+'';
}
	</script>


<?php $this->load->view('site/templates/footer');?>