<?php 
/*
* Application Status Class
*/


if (!class_exists('contactApplicaitonStatus')) {
    class contactApplicaitonStatus{
    	
	   	public $plugin_name;
	    public $plugin_slug;
	    public $version;
	    public $plugin_url;
	    public $plugin_path;
	    public $table;
	    public $wpdb;
    
    
    	/**
		 * here we go
		*/
    	public function __construct() {
    	global $wpdb;
	    $this->plugin_name = 'Application Status';
	    $this->wpdb = $wpdb;
	    $this->plugin_slug = 'application-status';
	    $this->version = '1.0.0';
	    $this->plugin_url = plugins_url() . '/contact-form-7-status';
	    $this->plugin_path = plugin_dir_path( __FILE__ );
	    $this->table = $this->wpdb->prefix . 'application_status';
	    $this->init();
	    $this->createdb();
    	} 
    	
    	private function init(){
    		add_action( 'admin_menu', array($this, 'application_status_menu') );
    		add_action('admin_head', array($this, 'application_status_style') );
    		add_action('wpcf7_admin_footer', array($this, 'contactFormSevenEditPage'));
    		add_action( 'admin_enqueue_scripts', array($this, 'admin_enque_script') );
    		add_action( 'wp_enqueue_scripts', array($this, 'application_front_enque') );
    		add_action('admin_footer', array($this, 'contactForm7Status_footer_function'));

    		// Ajax Call admin
    		add_action('wp_ajax_nopriv_appliation_post_ls', array($this, 'appliation_post_ls'));
			add_action( 'wp_ajax_appliation_post_ls', array($this, 'appliation_post_ls') );

			// Ajax Call admin
    		add_action('wp_ajax_nopriv_appliation_formpost_ls', array($this, 'appliation_formpost_ls'));
			add_action( 'wp_ajax_appliation_formpost_ls', array($this, 'appliation_formpost_ls') );

			// Ajax Status Update admin status_update_app
			add_action('wp_ajax_nopriv_status_update_app', array($this, 'status_update_app'));
			add_action( 'wp_ajax_status_update_app', array($this, 'status_update_app') );

			// Shortcode for application status update
			add_shortcode( 'wpcf7-status', array($this, 'application_status_shortcode'));
    	}

    	
		function application_status_menu() {
			add_menu_page( 'Application Status', 'Application Status', 'manage_options', 'appliation-status-wpcp7', array($this, 'applicationStatusCallback'), '', 50);
		}	
	
		function applicationStatusCallback(){  //Main Page Body
			global $wpdb;
			$metatbl = $wpdb->prefix . 'postmeta';
			if(!isset($_GET['form'])){
			$results = $wpdb->get_results('SELECT `meta_value`, `post_id` FROM `'.$metatbl.'` WHERE `meta_key` = "_appliation_ls"', OBJECT);
			$table = '<table>
						<thead>
							<tr>
								<th>ID</th>
								<th>Form Name</th>
								<th>Action</th>
							</tr>
						</thead><tbody>';
			foreach($results as $k=>$sr){
				$arrData = array();
				parse_str($sr->meta_value, $arrData);
				$table .= sprintf('<tr>
					<td>%d</td>
					<td>%s</td>
					<td><a href="%s">All Application</a></td>
					</tr>', $k+1, get_the_title($sr->post_id), admin_url( $path = '/admin.php?page=appliation-status-wpcp7&form='.$sr->post_id, $scheme = 'admin' ));
			}
			$table .= '</tbody></table>';
			$output = '<div class="applicatWrap">
				
				<h1><span>Application Status</span>
				<div class="myAdd">'.$this->add().'</div>
				</h1><hr/><br/>
				<div class="contentbody">
					<div class="application_inner">
					'.$table.'
					</div>
				</div>
				</div>';
			
			echo $output;
			}//if not set form get
			elseif(isset($_GET['form']) && !isset($_GET['id'])){
				$prefix = $wpdb->prefix;
				$reg_tble = $prefix . 'application_status';
				$allapplication = $wpdb->get_results('SELECT * FROM `'.$reg_tble.'` WHERE `form_id` ='.$_GET['form'].'', OBJECT);
				/*echo '<pre>';
				print_r($allapplication);
				echo '</pre>';*/

				$formcolumns = $wpdb->get_results('SELECT `meta_value` FROM `'.$metatbl.'` WHERE `meta_key` = "_appliation_ls" AND `post_id`='.$_GET['form'].'', OBJECT);
				$arrData = array();
				parse_str($formcolumns[0]->meta_value, $arrData);

				$table = '<table>
						<thead><tr><th>ID</th>';
						$clmn = array();
						foreach($arrData['column_f'] as $c => $scolumn):
							$table .= sprintf('<th>%s</th>', $scolumn);
							array_push($clmn, $scolumn);
						endforeach;
						$table .='<th>Status</th></tr></thead>';

						$table.='<tbody>';
						foreach($allapplication as $ap=>$saff):
							
							$datas = json_decode($saff->form_value);
							$cid= (int)$ap + 1; 
							$table .= '<tr><td>'. $cid .'</td>';
								for($i=0; count($clmn) > $i; $i++){
									$colum = $clmn[$i];
									$table .= sprintf('<td>%s</td>', $datas->$colum);
								}
							$table .= '<td><a href="'.admin_url( '/admin.php?page=appliation-status-wpcp7&form='.$_GET['form'] . '&id='.$saff->id, $scheme = 'admin' ).'">'.$saff->status.'</a></td></tr>';
							
						endforeach;
						

				$table .= '</tbody></table>';

				$output = '<div class="applicatWrap">
				<h1><span>Application Status</span>
				<div class="myAdd">'.$this->add().'</div>
				</h1><hr/><br/>
				<div class="contentbody">
					<div class="application_inner">
					'.$table.'
					</div>
				</div><br/>
				<a class="button button-primary" href="'.admin_url( $path = '/admin.php?page=appliation-status-wpcp7', $scheme = 'admin' ).'">Back to Main</a>
				</div>';
				echo $output;
			}else{
				$prefix = $wpdb->prefix;
				$reg_tble = $prefix . 'application_status';
				$meta = get_post_meta( $_GET['form'], '_appliation_ls', false );
				
				$arrData = array();
				parse_str($meta[0], $arrData);
				
				$current_status = $wpdb->get_row('SELECT `status` FROM `'.$reg_tble.'` WHERE `form_id` ='.$_GET['form'].' AND `id`='.$_GET['id'].'', OBJECT);
				//update pending to follow
				if($current_status->status == 'pending'){
					$update = $wpdb->update( 
						$reg_tble, 
						array( 
							'status' => 'following' // status
						), 
						array( 'id' => $_GET['id'] ), 
						array( 
							'%s',	// value1
						), 
						array( '%d' ) 
					);
				}
				$details = $wpdb->get_results('SELECT * FROM `'.$reg_tble.'` WHERE `form_id` ='.$_GET['form'].' AND `id`='.$_GET['id'].'', OBJECT);

				$values = json_decode($details[0]->form_value);
				$table = '<table class="applicationDetails"><thead><tr><th colspan="2"><h3>Form Details</h3></th></tr></thead><tbody>';
					foreach($values as $key=>$vlue):
						$table .= sprintf('<tr><th>%s</th><td>%s</td></tr>', $key, $vlue);
					endforeach;

				$sArray = array('pending', 'following', 'in progress', 'missing information', 'completed', 'rejected');

				$table .= '<tr><th>Status</th><td>
					<select data-post_id="'.$_GET['form'].'" data-id='.$_GET['id'].' name="app_status" class="status">';
						foreach($sArray as $sop){
							$selected = ($details[0]->status == $sop )?'selected':'';
							$table .= sprintf('<option %s value="%s">%s</option>', $selected, $sop, $sop);
						}
					$table .= '</select>
				</td></tr><tbody></table>';

				$output = '<div class="applicatWrap">
				<h1>Application Details</h1>
				<div class="contentbody">
					<div class="application_inner">
					'.$table.'
					</div>
				<br/>
				<button id="applicationUpdate" class="button button-primary">Update</button>
				<br/><br/>
				<a class="button button-primary" href="'.admin_url( $path = '/admin.php?page=appliation-status-wpcp7', $scheme = 'admin' ).'">Back to Main</a>
				<a class="button button-primary" href="'.admin_url( $path = '/admin.php?page=appliation-status-wpcp7&form='.$_GET['form'], $scheme = 'admin' ).'">Back to List</a>
				</div>
				</div>';
				echo $output;
			}

		} // End downl


		function send_mail($email, $template){
			$to = $email;
			$subject = get_bloginfo() . ' Application Update';
			$body = $template;
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			$headers[] = 'From: '.get_option('blogname').' <'.get_option('admin_email').'>';
			 
			wp_mail( $to, $subject, $body, $headers );
		}
		
		function application_status_style() { // Style for admin and it hook to admin header

		echo '<style>
	    	a.menu-top.menu-icon-generic.toplevel_page_appliation-status-wpcp7 div.dashicons-admin-generic:before{
	    		content: "\f232";
	    	}
	  	</style>';
		}

		function contactFormSevenEditPage($post){
			$atts = $post->scan_form_tags();
			
			$item = '<ul class="list-inline">';
			foreach($atts as $at){
				$item .= ($at->name != '')?sprintf('<li><b>[%s]</b></li>', $at->name):'';
			}
			$item.= '</ul>';

			$getdata = get_post_meta( $post->id(), '_appliation_ls', false );
			$arrData = array();
			parse_str(@$getdata[0], $arrData);
		
			$checked = (@$arrData['activeApplication'] == 'on')?'checked':'';
			$formField = '';
			$progressemail = '';
			$completed = '';
			$rejected ='';


			if($arrData){
				foreach($arrData['column_f'] as $sc){
					$formField .= sprintf('<div class="form-group">
					 				<input type="text" value="%s" name="column_f[]" class="form-control" /><div class="delete_item"><div alt="f153" class="dashicons dashicons-dismiss"></div></div>
					 			</div>', $sc);
				}
				$progressemail .= (array_key_exists('template_progress', $arrData))?$arrData['template_progress']:'<h4>Dear Applicant,</h4>
<div style="color:gray;">We start process for your application and you got update immoderately. </div>
<br/>
regards,<br/>
Team';
				$completed 		.= (array_key_exists('template_completed', $arrData))?$arrData['template_completed']:'<h4>Dear Applicant,</h4>
<div style="color:gray;">Your Application successfully complete. </div><br/>
regards,<br/>
Team';

				$rejected 		.= (array_key_exists('template_rejected', $arrData))?$arrData['template_rejected']:'<h4>Dear Applicant,</h4>
<div style="color:gray;">We are sorry. Your application are rejected. Please try again. </div><br/>
regards,<br/>
Team';
			}

			$output = '<div class="applicationStatusCF7">';
				$output .='<div class="inner">';
					$output .='
					<form data-id="'.$post->id().'" id="application_setting_ls">
					<header>';
						$output .= ($checked != '')?'<label class="selected"':'<label';
						$output .=' for="applicationApplication"><input '.$checked.' type="checkbox" name="activeApplication" style="display:none;" id="applicationApplication" /> <b>Active Application Status</b></label>
						<div class="successMessage"></div>
					 	</header>
					 	<div class="app_wrap">
					 		<ul class="tabs list-inline">
					 			<li class="active"><a href="#general">General</a></li>
					 			<li><a href="#email_template">Email Templates</a></li>
					 			<li><a href="#info">Info</a></li>
					 		</ul>
					 		<div class="tabElement active" id="general">
						 		<div class="list_item">'.$item.'</div>
						 		<div class="fdiv">';
						 			if($formField){
						 				$output .= $formField;
						 			}else{
						 				$output .='<div class="form-group">
						 				<input type="text" name="column_f[]" class="form-control" />
						 				</div>';
						 			}

						 			$output .= '<div class="new_item">
						 				<div alt="f502" class="dashicons dashicons-plus-alt"></div>
						 			</div>
						 		</div>
					 		</div> <!-- End general -->
					 		<div class="tabElement" id="email_template">
					 			<div class="tabinner">
					 				<table style="width:100%;" class="emailTemplatetable">
					 					<tbody>
					 						<tr>
					 							<th>In Progress</th>
					 							<td><textarea rows="6" style="width:100%;" name="template_progress" id="template_progress">'.$progressemail.'</textarea></td>
					 						</tr>
					 						<tr>
					 							<th>Completed</th>
					 							<td><textarea rows="6" style="width:100%;" name="template_completed" id="template_completed">'.$completed.'</textarea></td>
					 						</tr>
					 						<tr>
					 							<th>Rejected</th>
					 							<td><textarea rows="6" style="width:100%;" name="template_rejected" id="template_rejected">'.$rejected.'</textarea></td>
					 						</tr>
					 					</tbody>
					 				</table>
					 			</div>
					 		</div> <!-- End Tab Element -->
					 		<div class="tabElement" id="info">
					 			<div class="tabinner">
					 				<h2>Application Status plugin Documentation</h2>
					 				<ul>
					 					<li><strong>Front-end Shortcode: </strong> Use <code>[wpcf7-status]</code> for status update from front view.</li>
					 					<li>Dont use -(desh) sign in form name / column name under general tab.</li>
					 					<li>Create a page and use shortcode for your user for get application status.</li>

					 				</ul>
					 			</div>
					 		</div> <!-- End Table Element -->
					 	</div>
					 	</form>
					 	';
				$output .= '</div>';
			$output .= '</div>';
			echo $output;
			//wp_editor($content,'template_progress');
		}

		function admin_enque_script(){
			 wp_register_style( 'application_form_css', $this->plugin_url . '/css/admin-style.css', false, '1.0.0' );
			 wp_enqueue_style( 'application_form_css' );
			 wp_enqueue_script( 'application_status_script', $this->plugin_url . '/js/admin-script.js', array(), '1062017', true );
		}


		/*
		* Front Enque Script
		*/
		function application_front_enque(){ 
			wp_enqueue_script( 'application_front_script', $this->plugin_url . '/js/front-script.js', array(), '1062017', true );
			wp_localize_script( 'application_front_script', 'status_ajax', admin_url( 'admin-ajax.php' ));
		}

		/*
		* Admin Ajax call from content from page
		*/
		function appliation_post_ls(){
			$f_data = $_POST['f_data'];
			$id = $_POST['id'];
			update_post_meta( $id, '_appliation_ls', $f_data );
			die();
		}

		/*
		* Form submit by user ajax call
		*/
		function appliation_formpost_ls(){
			global $wpdb;
			$prefix = $wpdb->prefix;
			$reg_tble = $prefix . 'application_status';
			$posts = $_POST['f_data'];
			$postdatas = array();
			parse_str($posts, $postdatas);
			$id = $postdatas['_wpcf7'];
			unset($postdatas['_wpcf7']);
			unset($postdatas['_wpcf7_version']);
			unset($postdatas['_wpcf7_locale']);
			unset($postdatas['_wpcf7_unit_tag']);
			unset($postdatas['_wpcf7_container_post']);
			unset($postdatas['_wpcf7_nonce']);
			$metaid = get_post_meta( $id, '_appliation_ls', false );
			$metaArray = array();
			parse_str($metaid[0], $metaArray);
			$status = $metaArray['activeApplication'];
			$email = '';
			foreach($postdatas as $k=>$vlu) if (filter_var($vlu, FILTER_VALIDATE_EMAIL)) $email .= $vlu;
			$postdatas = json_encode( $postdatas );
			if($status == 'on'){
				$query = "INSERT INTO $reg_tble (form_id, form_value, email, status) VALUES ('%d', '%s', '%s', '%s')";
	    		$insert = $wpdb->query($wpdb->prepare($query, $id, $postdatas, $email, 'pending'));
	    		if($insert){
	    			echo 'success';
	    		}else{
	    			echo 'fail';
	    		}
			}
			die();
		}

		private function createdb(){
			global $wpdb;
			$prefix = $wpdb->prefix;
			$reg_tble = $prefix . 'application_status';
			if($wpdb->get_var("SHOW TABLES LIKE '$reg_tble'") != $reg_tble) {
		     //table not in database. Create new table
		     $charset_collate = $wpdb->get_charset_collate();
		     $sql = "CREATE TABLE $reg_tble (
		          id mediumint(10) NOT NULL AUTO_INCREMENT,
		          form_id mediumint(10) NOT NULL,
		          form_value varchar(500) NOT NULL,
		          email varchar(500) NOT NULL,
		          status varchar(500) NOT NULL,
		          date timestamp NOT NULL,
		          UNIQUE KEY id (id)
		     ) $charset_collate;";
		     require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		     dbDelta( $sql );
			}
		}

		function status_update_app(){
			$status = $_POST['val'];
			$id = $_POST['id'];
			$post_id = $_POST['post_id'];
			$missing_info = $_POST['missing'];
			global $wpdb; 
			$table = $wpdb->prefix . 'application_status';

			$getdata = get_post_meta( $post_id, '_appliation_ls', false );
			$arrData = array();
			parse_str($getdata[0], $arrData);

			$email = $wpdb->get_row('SELECT `email` FROM `'.$table.'` WHERE `id`='.$id.'', OBJECT);

			$update = $wpdb->query(
					    $wpdb->prepare(
					        "UPDATE $table SET `status` = %s WHERE `id` = %d", $status, $id ) // $wpdb->prepare
					); // $wpdb->query
			if($update){
				$email = $email->email;
				switch($status){
					case 'in progress':
					$template = $arrData['template_progress'];
					$this->send_mail($email, $template);	
					break;	

					case 'missing information';
					$this->send_mail($email, $missing_info);	
					break;

					case 'completed':
					$template = $arrData['template_completed'];
					$this->send_mail($email, $template);	
					break;

					case 'rejected':
					$template = $arrData['template_rejected'];
					$this->send_mail($email, $template);	
					break;

				}
				echo 'update';
			}else{
				echo 'fail';
			}
			die();
		} // End status update backend

		/*
		* Shortcode
		*/
		function application_status_shortcode(){
			global $post;
			$table = $this->table;
			$metatbl = $this->wpdb->prefix . 'postmeta';
			$forms = $this->wpdb->get_results('SELECT `post_id` FROM `'.$metatbl.'` WHERE `meta_key` = "_appliation_ls"', OBJECT);
			$appForms = '';
			foreach($forms as $frm) $appForms .= '<option value="'.$frm->post_id.'">'.get_the_title( $frm->post_id ).'</option>';
			
			$outputr = '';
			if(isset($_POST['application_name']) && $_POST['status_email'] != '' ){
				$result = $this->wpdb->get_row("SELECT `status` FROM $this->table WHERE `email`='".$_POST['status_email']."' AND `form_id`='".$_POST['application_name']."' ORDER BY `date` ASC", OBJECT);

				$outputr .= $result->status;
			}

			$html = '<div id="wrap_applucation_status">
				<div class="wrap_inner">
					<div class="col-md-12 col-sm-12 col-xs-12">';
			if($outputr == '' ){
			$html .= '
						<form method="POST" class="status_form">
							<div class="form-group">
								<label for="application_name">Your Application Name</label>
								<select name="application_name" class="form-control" id="application_name">
									<option value="">Select Your Application</option>	
									'.$appForms.'
								</select>
							</div>
							<div class="form-group">
								<label for="status_email">Your Email</label>
								<input placeholder="your_email@example.com" type="email" name="status_email" id="status_email" value="" class="form-control" />
							</div>
							<button type="submit" class="btn btn-primary btn-applicaiton_status">Get Status</button>
						</form>
			';
			}
			else
			{
				$html .= '<div class="status_result">
					<div class="jumbotron">
						  <h2 class="display-3">Your application ('.get_the_title( $_POST['application_name'] ).') Status </h2>
						  
						  <h3>Status: '.ucfirst($outputr).'</h3>
						  <hr style="margin-top:15px;" class="my-4">
						  <p class="lead">
						    <a class="btn btn-primary btn-lg" href="'.get_page_link( $post->ID ).'" role="button">Reset</a>
						  </p>
					</div>
				</div>';
			}
			$html .= '</div></div></div>';
			echo $html;
		}

		function add(){
			$add = '
				<div class="add_app">
					<ul class="my_add_list">
						<li><a target="_blank" class="dashicons dashicons-admin-home" href="http://larasoftbd.com/"></a></li>
						<li><a target="_blank" class="dashicons dashicons-facebook-alt" href="https://www.facebook.com/LaraSoft-438450959878922/"></a></li>
						<li><a target="_blank" class="dashicons dashicons-googleplus" href="http://plus.google.com/+LaraSoftOmarFaruque"></a></li>
						<li><a target="_blank" class="dashicons dashicons-networking" href="https://github.com/OmarFaruque"></a></li>
						
					</ul>
				</div>
			';
			return $add;
		}

		function contactForm7Status_footer_function(){
			$output = '<div class="contF7Statusloading" style="display:none;">
				<div class="statusInner">
					<img src="'.$this->plugin_url.'/img/spinner-2x.gif" alt="Loading..."/>
				</div>
			</div>';
			echo $output;
		}

    } //End Class
}