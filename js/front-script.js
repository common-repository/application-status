jQuery(document).ready(function($){
	//Submit contact Form
	$(".wpcf7").on('submit.wpcf', function(e){
		$id = $(this).find('input[name="_wpcf7"]').val();
		$formdata = $(this).find('form').serialize();
		$.ajax({
			type:'POST', 
            //dataType: "json",
            url: status_ajax,
            data:
            {
                'action'    : 'appliation_formpost_ls',
                'f_data'    : $formdata
                //'id' 		: $id
            },success:function(data){
            		//console.log($.trim(data));
            }
		});
	});


	/*
	* 
	*/

}); //End document ready