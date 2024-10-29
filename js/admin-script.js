jQuery(document).ready(function($){
	if($('input#applicationApplication').is(':checked')){
		$('.applicationStatusCF7 .app_wrap').show();
	}else{
		$('.applicationStatusCF7 .app_wrap').hide();
	}

	$('input#applicationApplication').change(function(){ // action deactive application
		if($(this).is(':checked')){
			$(this).closest('label').addClass('selected');
			$('.applicationStatusCF7 .app_wrap').show();
		}else{
			$(this).closest('label').removeClass('selected');
			$('.applicationStatusCF7 .app_wrap').hide();
		}
	});

	$(document.body).on('click', '.fdiv .new_item', function(){ // Create new item
		$html = '<div class="form-group"><input type="text" name="column_f[]" class="form-control" /><div class="delete_item"><div alt="f153" class="dashicons dashicons-dismiss"></div></div></div>';
		$($html).insertBefore($(this));
	});

	$(document.body).on('click', '.fdiv .delete_item', function(){
		$(this).closest('div.form-group').remove();
	});


	// post application settings
	$(document.body).on('click', 'input[name="wpcf7-save"]', function(e){
		//e.preventDefault();
		$formdata = $('#application_setting_ls').serialize();
		$id = $('form#application_setting_ls').data('id');
		$.ajax({
			type:'POST', 
            //dataType: "json",
            url: ajaxurl,
            data:
            {
                'action'    : 'appliation_post_ls',
                'f_data'    : $formdata, 
                'id' 		: $id
            },success:function(data){
            		//$('.successMessage').html('<p>Update Successfully.</p>');
            }
		});
		
	});


	/*
	* Admin Tab
	*/
	$(document.body).on('click', '.app_wrap ul.tabs li a', function(){
		$('.app_wrap ul.tabs li').removeClass('active');
		$('.tabElement').removeClass('active');
		$(this).closest('li').addClass('active');
		$thisid = $(this).attr('href');
		$($thisid).addClass('active');

	});


	/*
	* Application Update
	*/
	$(document.body).on('click', 'button#applicationUpdate', function(){
		$('.contF7Statusloading').css('display', 'table');
		$var = $('select[name="app_status"]').val();
		$missing = ($var == 'missing information')?$('textarea#missing_info').val():'';

		$id = $('select[name="app_status"]').data('id');
		$post_id = $('select[name="app_status"]').data('post_id');
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: 
			{
				'action'	: 'status_update_app',
				'val' 		: $var,
				'id' 		: $id, 
				'post_id' 	: $post_id,
				'missing' 	: $missing
			},success:function(data){
				console.log($.trim(data));
				if($.trim(data) == 'update'){
					$('.contF7Statusloading').css('display', 'none');
			        location.reload(); //page reload

				}
			}
		});
	});

	/*
	* On change select status missing male row
	*/
	$(document.body).on('change', 'select[name="app_status"]', function(){
		$val = $(this).val();
		$inlineMail = '<tr class="missing_info"><th>Missing Information</th><td><textarea rows="6" style="max-width:100%;" cols="120" name="missing_info" class="form-control" id="missing_info"></textarea></td></tr>';
		if($val == 'missing information'){
			$('table.applicationDetails > tbody tr:last').after($inlineMail);
		}else{
			$('tr.missing_info').remove();
		}
	}); //End Select change function 


}); // End document ready