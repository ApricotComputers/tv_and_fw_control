<?php

// Simple PHP interface to trigger SNMP enable/disable of an interface on a Router/Switch
// Author: Henry Potgieter - www.techtutoring.ca

// This program is free software: you can redistribute it and/or modify */
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <https://www.gnu.org/licenses/>.


//////////////////////////////////
// Vars

// SWITCH
$target_switch_host = "<HOST OR IP HERE>";
$snmp_community = "<SNMP COMMUNITY>";
$snmp_mib = "<IF ADMIN STATUS INDEX MIB>";

// PFSENSE
$apikey = "<API KEY>";
$secret = "<API SECRET>";
$bypass_rule_prefix = "<BYPASS PREFIX>";
$master_rule_prefix = "<MASTER PREFIX>";
$target_pfsense_host = "<PFSENSE IP>";


//////////////////////////////////
// Main logic check
if (isset($_POST['disable'])) {
	snmp2_set($target_switch_host, $snmp_community, $snmp_mib, "i", "2");
} elseif (isset($_POST['enable'])) {
	snmp2_set($target_switch_host, $snmp_community, $snmp_mib, "i", "1");
} elseif (isset($_POST['disable_fw_bypass'])) {
	firewall_action(1, $bypass_rule_prefix);
} elseif (isset($_POST['enable_fw_bypass'])) {
	firewall_action(0, $bypass_rule_prefix);
} elseif (isset($_POST['disable_fw_master'])) {
	firewall_action(1, $master_rule_prefix);
} elseif (isset($_POST['enable_fw_master'])) {
	firewall_action(0, $master_rule_prefix);
}

//////////////////////////////////
// Create standard HTML
?>

<!DOCTYPE html>
<html>
<head>
    <title>ACCESS CONTROL</title>
    <meta charset="utf-8" />
    <style>
    	body {
		background-color: black;
		color: white;
    		font-family: Arial, Helvetica, sans-serif;
    		line-height: 1.4;
		    font-size: 2em;
	}	


	.container {
		width: 80%;
		padding: 0 1rem;
		margin: auto;
	}

	.btn-off {
		    display: inline-block;
		    border: none;
		    background: #F00;
		    color: #000;
		    padding: 7px 20px;
		    cursor: pointer;
		width: 100%;
			font-size: 2em;
	}
	.btn-on {
		    display: inline-block;
		    border: none;
		    background: #0F0;
		    color: #000;
		    padding: 7px 20px;
		    cursor: pointer;
		width: 100%;
			font-size: 2em;
	}
	.status-down {
		background-color: #F00; 
		text-align: center;
		    color: #000;
	}
	.status-up {
		background-color: #0F0;
		text-align: center;
		    color: #000;
	}

    </style>
</head>
<body>
<Section id="main">
	<div class="container">

<?php

// Call page render function
render_page();

// Finish off the HTML
?>
</body>
</html>

<?php

//////////////////////////////////
// Functions

function render_page() {
	
	// Import globals
	global $bypass_rule_prefix, $master_rule_prefix, $target_switch_host, $snmp_community, $snmp_mib;

	// Get port status
	$snmp_output = snmp2_get($target_switch_host, $snmp_community, $snmp_mib);
	
	// Check if the port is up or down, output appropriate status
	if (strpos($snmp_output, 'up') !== false) {
		echo '
		<div class="status-up">
			TV Interface Enabled
		</div>
		';
	} elseif (strpos($snmp_output, 'down') !== false) {
		echo '
		<div class="status-down">
			TV Interface Disabled
		</div>
		';
	}
	$firewall_status_bypass = check_fw_status($bypass_rule_prefix);
	if ($firewall_status_bypass > 0) {
		echo '
		<div class="status-down">
			Firewall Bypass Disabled 
		</div>
		';
	} else {
		echo '
		<div class="status-up">
			Firewall Bypass Enabled
		</div>
	';
	}
	$firewall_status = check_fw_status($master_rule_prefix);
	if ($firewall_status > 0) {
		echo '
		<div class="status-down">
			Firewall Master Disabled 
		</div>
		';
	} else {
		echo '
		<div class="status-up">
			Firewall Master Enabled
		</div>
	';
	}
	
	// Generate main form
	echo '
		<BR><BR>
		<div class="form">
			<FORM method="post" class="form">
				<button type="submit" class="btn-off" name="disable">TV DISABLE</button>
				<BR>
				<button type="submit" class="btn-on" name="enable">TV ENABLE</button>
				<BR>
				<button type="submit" class="btn-off" name="disable_fw_bypass">FW BYPASS OFF</button>
				<BR>
				<button type="submit" class="btn-on" name="enable_fw_bypass">FW BYPASS ON</button>
				<BR>
				<button type="submit" class="btn-off" name="disable_fw_master">FW MASTER OFF</button>
				<BR>
				<button type="submit" class="btn-on" name="enable_fw_master">FW MASTER ON</button>
			</FORM>
		</div>
	</div>
</section>
';
}

function check_fw_status ($rule_prefix) {
	global $target_pfsense_host;

	$current_config = api_request($target_pfsense_host, "POST", "config_get");
	$filters = $current_config['data']['config']['filter'];
	$i = 0;
	$return_status = 0;
        foreach ($filters['rule'] as $filter) {
		    if (strpos($filter['descr'], $rule_prefix) !== false) {
			    if (isset($current_config['data']['config']['filter']['rule'][$i]['disabled'])) {
				    $return_status++;
			    }
		    }
        $i++;
	    }
	return $return_status;
}


function firewall_action($fw_change_action, $rule_prefix) {
	global $target_pfsense_host;

    // Get the existing Configuration
    $current_config = api_request($target_pfsense_host, "POST", "config_get");

    // Prepare filters only for processing
    $filters = $current_config['data']['config']['filter'];

    $i = 0;
    foreach ($filters['rule'] as $filter) {
        if (strpos($filter['descr'], $rule_prefix) !== false) {
            if ($fw_change_action === 0) {
                unset($current_config['data']['config']['filter']['rule'][$i]['disabled']);
            } else {
                $current_config['data']['config']['filter']['rule'][$i]['disabled'] = "";
            }
       }
       $i++;
    }

	// Apply changed configuration back to firewall
	$change_result = api_request($target_pfsense_host, "POST", "config_set", "", $current_config['data']['config']);
}

function api_request($target_pfsense_host, $method, $action, $params="", $data="") {
	global $apikey, $secret;
    $path = "/?action=" . $action;
    $url = 'https://' . $target_pfsense_host . '/fauxapi/v1' . $path;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['fauxapi-auth: . ' . auth_gen($apikey, $secret)]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $json = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($json,true);
    return $result;
}

// Generate the fauxapi auth string
function auth_gen($apikey, $secret) {
    $nonce = makeNonce();
    $timestamp = makeTimestamp();
    $hash = hash('sha256', $secret . $timestamp . $nonce);
    $return = $apikey . ":" . $timestamp . ":" . $nonce . ":" . $hash;
    return $return;
}

// Generate random string for creating a nonce
function makeRandomString($bits = 256) {
    $bytes = ceil($bits / 8);
    $return = '';
    for ($i = 0; $i < $bytes; $i++) {
        $return .= chr(mt_rand(0, 255));
    }
    return $return;
}

// Make a timestamp
function makeTimestamp() {
    $stamp = gmdate("Ymd") . "Z" . gmdate("His");
    return $stamp;
}

// Make a nonce
function makeNonce() {
    $nonce=hash('sha512', makeRandomString());
    return substr($nonce,0,8);
}
?>

