<?php

include 'RedCap_API_to_Flask.php';

/*
    This the page that is loaded to the patients
    The overall layout includes pulling data from the individuals record and displaying it here.

    The study coordinator needs to customize this area to fit their REDCap data and records
    Your REDCap project should have details on how the data will be formatted.
*/

$relationships = array('Mother', 'Father', 'Grandmother', 'Grandfather', 'Uncle', 'Aunt', 'Brother', 'Sister', 'Niece', 'Nephew', 'First Cousin', 'Second Cousin', 'Third Cousin', 'Other');
$record_id = $_GET['recordID'];

// personal data retrieval and display
// The individual data will come through on this variable
$data = request_data($record_id);

// Depending on how your data is structured, custom code needs to be included here.
$person = $data['proband'];

$pedigree = get_pedigree($record_id, $data['token']);

$info_spacing = '9px';

print '<head>';
print '<link rel="stylesheet" href="/content/plugins/RedCap/css/copy_fields.css">';
print '</head>';

print '<div style="float:left; margin-top: 20px;">';
	print '<p style="font-size:25px;">Personal Info</p>';
	print '<div style="font-size:20px; background-color: #f2f2f2; min-width: 500px; width: 70%; border:3px solid; border-radius: 10px; padding:10px; margin-bottom:10px;">';
		print '<ul style="margin-bottom:10px;">' . $person['proband_name_first'] . ' ' . $person['proband_name_last'] . '</ul>';
		if($person['proband_address']!=''){
			print '<ul>' . $person['proband_address'] .'</ul>';
		}
		 if($person['proband_city']!='' or $person['proband_state']!='' or $person['proband_zipcode']!=''){
		 	print '<ul style="margin-bottom:' . $info_spacing . ';">' . $person['proband_city'] .' ' . $person['proband_state'] . ' ' .$person['proband_zipcode'] . '</ul>';
		 }
		if($person['proband_phone_cell'] != ''){
			print '<ul style="margin-bottom:' . $info_spacing . ';">' . 'Cell Phone: ' . $person['proband_phone_cell'] . '</ul>';
		}
		if($person['proband_phone_home'] != ''){
			print '<ul style="margin-bottom:' . $info_spacing . ';">' .'Home Phone: ' . $person['proband_phone_home'] . '</ul>';
		}
		if($person['proband_email'] != ''){
			print '<ul style="margin-bottom:' . $info_spacing . ';">' . 'Email: ' . $person['proband_email'] . '<ul>';
		}
		print '<br>';
		$ending = 'copyTarget' . '_prob';
		print '<script type="text/javascript" src="/content/plugins/RedCap/js/copy_button.js"></script>';
		print '<input class="copy_field" type="text" id="' . $ending . '" value="' . $person['return_code'] . '">';
		print '<button class="copy_button" id="copyButton' . $ending . '">Copy</button>';
		print '<script>copy("' . $ending . '");</script>';
		print '<br>';
		print '<a href='. $person['return_link'] .'>Edit Information</a>';
		print '<br>';
		print '<br>';
		
	print '</div>';
	print '<p style="font-size:25px;">Family Pedigree</p>';
	if(base64_decode($pedigree)['error'] != "There is no file to download for this record") {
		print '<img class="pedigree" src="data:image/png;base64,' . $pedigree . '" width=100%>';
	}
	else{
		print '<p style="font-size:20px;" width=800px>Pedigree currently not available</p>';
	}
print '</div>';

//Relative information retrieval and display

#$data = get_relative_info($record_id);
$last_rel = $data['number_of_relatives'];
$returned = floatval($data['kits_returned']);
$sent = floatval($data['kits_sent']);
$vus = floatval($data['vus']);
$dnaTesting = intval(($vus/$returned)*100.0);
$kitsReturned = intval(($returned/$sent)*100.0);

print '<div style="float:right; margin-top: 20px;">'; //border-left:3px solid; padding-left: 10px;
	print '<p style="font-size: 25px;">Relatives</p>';
	//border-bottom:3px solid; 
	print '<div style="padding:10px; margin-top:0px; margin-bottom:10px; border:3px solid; border-radius:10px; background-color: #fce5e5;">';
		print '<p style="font-size:20px;">Kits Returned: <span style="font-weight:bold;">' . $kitsReturned  . '%</span></p>';
		print '<p style="font-size:20px;">DNA Testing Completed: <span style="font-weight:bold;">' . $dnaTesting  . '%</span></p>';
	print '</div>';
	print '<div style="padding:10px; margin-top:0px; margin-bottom:0px;">';
		print '<a style="font-size: 20px;" href='.$data['add_relative_link'].'>+ Add Relative</a>';
	print '</div>';
	foreach (range(1,$last_rel) as $count){
		$person = $data[(string)$count];
		$id = 'copyTarget' . '_rel_' . $count;
		print '<div style="background-color: #f2f2f2; font-size:20px; border:3px solid; border-radius:10px; padding: 10px; margin-top:0px; margin-bottom:5px;">';
			print '<p style="padding-right:20px;">';
				if (($person['rel_relationship'] > 1) and ($person['rel_relationship'] < 14)){
				print '<span style="font-weight:bold; font-size:21px;">' . $relationships[$person['rel_relationship']-1] . '</span>';
				}
				else if ($person['rel_relationship'] == 14){
					print '<span style="font-weight:bold; font-size:21px;">' . $person['rel_relationship_oth'] . '</span>';
				}
				if ($person['rel_kit_sent']!='1'){
					print '<span style="font-size:20px; color:red; float:right; margin-left:5px;">Not Contacted By Study</span>';
				}
				else {
					print '<span style="font-size:20px; color:green; float:right;">Contacted By Study</span>';
				}
			print '</p>';
			print '<ul style="margin-bottom:' . $info_spacing . ';">' . $person['rel_first_name'] . ' ' . $person['rel_last_name'] . '</ul>';
			if($person['rel_address']!=''){
				print '<ul>' . $person['rel_address'] .'</ul>';
			}
			 if($person['rel_city']!='' or $person['rel_state']!='' or $person['rel_zipcode']!=''){
			 	print '<ul style="margin-bottom:' . $info_spacing . ';">' . $person['rel_city'] .' ' . $person['rel_state'] . ' ' .$person['rel_zipcode'] . '</ul>';
			 }
			if($person['rel_phone_cell'] != ''){
				print '<ul style="margin-bottom:' . $info_spacing . ';">' . 'Cell Phone: ' . $person['rel_phone_cell'] . '</ul>';
			}
			if($person['rel_phone_home'] != ''){
				print '<ul style="margin-bottom:' . $info_spacing . ';">' . 'Home Phone: ' . $person['rel_phone_home'] . '</ul>';
			}
			if($person['rel_email'] != ''){
				print '<ul style="margin-bottom:' . $info_spacing . ';">' . 'Email: ' . $person['rel_email'] . '</ul>';
			}
			print '<br>';
			print '<script type="text/javascript" src="/content/plugins/RedCap/js/copy_button.js"></script>';
			print '<input class="copy_field" type="text" id="' . $id . '" value=' . $person['return_code'] . '> <button class="copy_button" id="copyButton' . $id . '">Copy</button>';
			print '<script>copy("' . $id . '");</script>';
			print '<br>';
			print '<a href='.$person['return_link'].'>Edit Relative</a>';	
		print '</div>';
	}
print '</div>';

?>