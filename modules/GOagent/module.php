<?php
namespace creamy;

require_once(CRM_MODULE_INCLUDE_DIRECTORY.'Module.php');
require_once(CRM_MODULE_INCLUDE_DIRECTORY.'CRMDefaults.php');
require_once(CRM_MODULE_INCLUDE_DIRECTORY.'LanguageHandler.php');
include(CRM_MODULE_INCLUDE_DIRECTORY.'Session.php');
require_once(CRM_MODULE_INCLUDE_DIRECTORY.'goCRMAPISettings.php');

### Check if DB variables are not set ###
$VARDB_server   = (!isset($VARDB_server)) ? "162.254.144.92" : $VARDB_server;
$VARDB_user     = (!isset($VARDB_user)) ? "justgocloud" : $VARDB_user;
$VARDB_pass     = (!isset($VARDB_pass)) ? "justgocloud1234" : $VARDB_pass;
$VARDB_database = (!isset($VARDB_database)) ? "asterisk" : $VARDB_database;

$VARDBgo_server   = (!isset($VARDBgo_server)) ? "162.254.144.92" : $VARDBgo_server;
$VARDBgo_user     = (!isset($VARDBgo_user)) ? "goautodialu" : $VARDBgo_user;
$VARDBgo_pass     = (!isset($VARDBgo_pass)) ? "pancit8888" : $VARDBgo_pass;
$VARDBgo_database = (!isset($VARDBgo_database)) ? "goautodial" : $VARDBgo_database;

$baseURL = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'] : "http://".$_SERVER['SERVER_NAME'];
$getSlashes = preg_match_all("/\//", $_SERVER['REQUEST_URI']);
$baseDIR = (!empty($_SERVER['REQUEST_URI']) && $getSlashes > 1) ? dirname($_SERVER['REQUEST_URI'])."/" : "/";
define(__NAMESPACE__ . '\GO_MODULE_DIR', $baseURL.$baseDIR.'modules'.DIRECTORY_SEPARATOR.'GOagent'.DIRECTORY_SEPARATOR);

/**
 * This module is an example of how to write a module for Creamy.
 * It will show a message of the day (message of the day).
 */
class GOagent extends Module {
	protected $userrole;
	protected $is_logged_in;
	protected $astDB;

	// module meta-data (ModuleData interface implementation).
	static function getModuleName() { return "GOautodial Agent Dialer"; }
	
	static function getModuleVersion() { return "1.0"; }
	
	static function getModuleDescription() { return "A module for GOautodial Agent Dialer integration."; }

	// lifecycle and respond to interactions.
	public function uponInit() {
		error_log("Module \"GOautodial Agent Dialer\" initializing...");
		
		// add the translation files to our language handler.
		$customLanguageFile = $this->getModuleLanguageFileForLocale($this->lh()->getLanguageHandlerLocale());
		if (!isset($customLanguageFile)) { $customLanguageFile = $this->getModuleLanguageFileForLocale(CRM_LANGUAGE_DEFAULT_LOCALE); }
		$this->lh()->addCustomTranslationsFromFile($customLanguageFile);
		
		$this->astDB = \creamy\DatabaseConnectorFactory::getInstance()->getDatabaseConnectorOfType(CRM_DB_CONNECTOR_TYPE_MYSQL, null, 'asterisk');
		$this->goDB = \creamy\DatabaseConnectorFactory::getInstance()->getDatabaseConnectorOfType(CRM_DB_CONNECTOR_TYPE_MYSQL);

		$this->userrole = \creamy\CreamyUser::currentUser()->getUserRole();

		if ($this->userrole > 1) {
			$_SESSION['is_logged_in'] = $this->checkIfLoggedOnPhone();

			echo $this->getGOagentContent();
		}
	}
		
	public function uponActivation() {
		error_log("Module \"GOautodial Agent Dialer\" activating...");
	}
		
	public function uponDeactivation() {
		error_log("Module \"GOautodial Agent Dialer\" deactivating...");
	}

	public function uponUninstall() {
		error_log("Module \"GOautodial Agent Dialer\" uninstalling...");
	}
	
	// Private functions for this module.
	private function dateIsToday($date) {
		 $current = strtotime(date("Y-m-d"));
		
		 $datediff = $date - $current;
		 $differance = floor($datediff/(60*60*24));
		 if ($differance == 0) return true;
		 return false;
	}

	private function checkIfLoggedOnPhone() {
		$this->is_logged_in = (isset($_SESSION['is_logged_in'])) ? $_SESSION['is_logged_in'] : false;
		return $this->is_logged_in;
	}
	
	// views and code generation
	/** We return true here to indicate that we want access to the database */
	public function needsDatabaseFunctionality() { return false; }

	public function mainPageViewContent($args) {
		return false;
	}

	public function mainPageViewTitle() {
		return $this->lh()->translationFor("GO_title");
	}
	
	public function mainPageViewSubtitle() {
		return $this->lh()->translationFor("GO_subtitle");
	}
	
	public function mainPageViewIcon() {
		return 'phone-square';
	}

	private function getGOagentContent() {
		$custInfoTitle = $this->lh()->translationFor("customer_information");
		$selectACampaign = $this->lh()->translationFor("select_a_campaign");
		$dispositionCall = $this->lh()->translationFor("disposition_call");
		$manualDialLead = $this->lh()->translationFor("manual_dial_lead");
		$availableCampaigns = $this->lh()->translationFor("available_campaigns");
		$groupsNotSelected = $this->lh()->translationFor("groups_not_selected");
		$selectedGroups = $this->lh()->translationFor("selected_groups");
		$blendedCalling = $this->lh()->translationFor("blended_calling");
		$outboundActivated = $this->lh()->translationFor("outbound_activated");
		$selectAll = $this->lh()->translationFor("select_all");
		$submit = $this->lh()->translationFor("submit");
		$note = $this->lh()->translationFor("note");
		$phoneNumber = $this->lh()->translationFor("phone_number");
		$dialCode = $this->lh()->translationFor("dial_code");
		$dialCodeInfo = $this->lh()->translationFor("dial_code_info");
		$digitsOnly = $this->lh()->translationFor("digits_only");
		$searchExistingLeads = $this->lh()->translationFor("search_existing_leads");
		$searchExistingLeadsInfo = $this->lh()->translationFor("search_existing_leads_info");
		$dialOverride = $this->lh()->translationFor("dial_override");
		$dialOverrideInfo = $this->lh()->translationFor("dial_override_info");
		$digitsOnlyPlease = $this->lh()->translationFor("digits_only_please");
		$dialNow = $this->lh()->translationFor("dial_now");
		$previewCall = $this->lh()->translationFor("preview_call");
		$goBack = $this->lh()->translationFor("go_back");
		$selectByDragging = preg_replace('/(\w*'. $selectAll .'\w*)/i', '<b>$1</b>', $this->lh()->translationFor("select_by_dragging"));
		$goModuleDIR = GO_MODULE_DIR;
		$userrole = $this->userrole;
		$_SESSION['module_dir'] = $goModuleDIR;
		$_SESSION['campaign_id'] = (strlen($_SESSION['campaign_id']) > 0) ? $_SESSION['campaign_id'] : '';
		
		//$webProtocol = (preg_match("/Windows/", $_SERVER['HTTP_USER_AGENT'])) ? "wss" : "ws";
		$webProtocol = "wss";
		
		$labels = $this->getLabels()->labels;
		$disable_alter_custphone = $this->getLabels()->disable_alter_custphone;
		$labelHTML = '';
		foreach ($labels as $key => $value) {
			$key = str_replace("label_", "", $key);
			if (!preg_match("/---HIDE---/i", $value)) {
				if (strlen($value) < 1) {
					$value = ucwords(str_replace("_", " ", $key));
				}
				if ($key == "comments") {
					$labelHTML .= "<tr>\n";
					$labelHTML .= "<td align='right' valign='top' width='200' nowrap style='padding-right: 10px;'>$value:<br style='display:none;'><span id='viewcommentsdisplay' style='display:none;'><input type='button' id='ViewCommentButton' onClick=\"ViewComments('ON')\" value='-History-'/></span> </td><td><textarea rows='5' cols='50' id='formMain_$key' name='$key' class='cust_form_text' value='' style='resize:none;'></textarea></td>\n";
					$labelHTML .= "</tr>\n";
				} else if ($key == "gender_list") {
					$labelHTML .= "<tr>\n";
					$labelHTML .= "<td align='right' width='200' nowrap style='padding-right: 10px;'>$value:</td><td><span id='GENDERhideFORie'><select size='1' name='$key' class='cust_form' id='formMain_$key'><option value='U'>U - Undefined</option><option value='M'>M - Male</option><option value='F'>F - Female</option></select></span></td>\n";
					$labelHTML .= "</tr>\n";
				} else if ($key == "phone_number") {
					if ( preg_match('/Y|HIDE/', $disable_alter_custphone) ) {
						$labelHTML .= "<tr>\n";
						$labelHTML .= "<td align='right' width='200' nowrap style='padding-right: 10px;'>$value:</td><td><span id='phone_numberDISP' style='line-height: 30px;'> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span>";
						$labelHTML .= "<input type='hidden' name='$key' id='formMain_$key' value='' /></td>\n";
						$labelHTML .= "</tr>\n";
					} else {
						$labelHTML .= "<tr>\n";
						$labelHTML .= "<td align='right' width='200' nowrap style='padding-right: 10px;'>$value:</td><td><input type='text' size='20' name='$key' id='formMain_$key' maxlength='16' class='cust_form' value='' /></td>\n";
						$labelHTML .= "</tr>\n";
					}
				} else {
					switch ($key) {
						case "title":
							$size = "4";
							$maxlength = "4";
							break;
						case "middle_initial":
							$size = "1";
							$maxlength = "1";
							break;
						case "email":
							$size = "45";
							$maxlength = "70";
							break;
						case "state":
							$size = "4";
							$maxlength = "2";
							break;
						case "postal_code":
							$size = "14";
							$maxlength = "10";
							break;
						case "vendor_lead_code":
							$size = "15";
							$maxlength = "20";
							break;
						case "phone_code":
							$size = "4";
							$maxlength = "10";
							break;
						case "alt_phone":
							$size = "20";
							$maxlength = "12";
							break;
						case "security_phrase":
							$size = "20";
							$maxlength = "100";
							break;
						case "address1":
							$size = "50";
							$maxlength = "100";
							break;
						case "address2":
							$size = "50";
							$maxlength = "100";
							break;
						case "address3":
							$size = "50";
							$maxlength = "100";
							break;
						case "city":
							$size = "20";
							$maxlength = "50";
							break;
						case "province":
							$size = "20";
							$maxlength = "50";
							break;
						default:
							$size = "20";
							$maxlength = "30";
					}
					
					$convert_dial_code = 0;
					if ($convert_dial_code && $key == "phone_code") {
						$labelHTML .= "<tr>\n";
						$labelHTML .= "<td align='right' width='200' nowrap style='padding-right: 10px;'>$value:</td><td><span id='converted_dial_code'></span><input type='hidden' size='$size' maxlength='$maxlength' id='formMain_$key' name='$key' class='cust_form' value='' /></td>\n";
						$labelHTML .= "</tr>\n";
					} else {
						$labelHTML .= "<tr>\n";
						$labelHTML .= "<td align='right' width='200' nowrap style='padding-right: 10px;'>$value:</td><td><input type='text' size='$size' maxlength='$maxlength' id='formMain_$key' name='$key' class='cust_form' value='' /></td>\n";
						$labelHTML .= "</tr>\n";
					}
				}
			} else {
				$additionalDISP = '';
				if ($key == "phone_number") { $additionalDISP = "<span id='phone_numberDISP' style='display:none;'></span>"; }
				if ($key == "phone_code") { $additionalDISP = "<span id='converted_dial_code' style='display:none;'></span>"; }
				$labelHTML .= "<tr style='display:none;' width='200' nowrap style='padding-right: 10px;'>\n";
				$labelHTML .= "<td align='right'>$value:</td><td>$additionalDISP<input type='hidden' id='formMain_$key' name='$key' value='' />";
				if ($key == "gender_list")
					{$labelHTML .= "<span id='GENDERhideFORie' style='display:none;'><select size='1' name='$key' class='cust_form' id='formMain_$key'><option value='U'>U - Undefined</option><option value='M'>M - Male</option><option value='F'>F - Female</option></select></span>";}
				$labelHTML .= "</td>\n";
				$labelHTML .= "</tr>\n";
			}
		}
		
		$str = <<<EOF
		<link type='text/css' rel='stylesheet' href='{$goModuleDIR}css/style.css'></link>
					<script type='text/javascript' src='{$goModuleDIR}GOagentJS.php'></script>
					
					<audio id="remoteStream" style="display: none;" autoplay controls></audio>
					<script type="text/javascript" src="{$goModuleDIR}js/jsSIP.js"></script>
					<script>
					var audioElement = document.querySelector('#remoteStream');
					var localStream;
					var remoteStream;
					
					var configuration = {
						'ws_servers': '{$webProtocol}://webrtc.goautodial.com:44344/',
						'uri': 'sip:'+phone_login+'@'+server_ip,
						'password': phone_pass,
						'session_timers': false
					};
					
					var rtcninja = JsSIP.rtcninja;
					var phone = new JsSIP.UA(configuration);
					
					phone.on('connected', function(e) {
						console.log('connected', e);
					});
					
					phone.on('disconnected', function(e) {
						console.log('disconnected', e);
					});
					
					phone.on('newRTCSession', function(e) {
						var session = e.session;
						console.log('newRTCSession: originator', e.originator, 'session', e.session, 'request', e.request);
					
						session.on('peerconnection', function (data) {
							console.log('session::peerconnection', data);
						});
					
						session.on('iceconnectionstatechange', function (data) {
							console.log('session::iceconnectionstatechange', data);
						});
					
						session.on('connecting', function (data) {
							console.log('session::connecting', data);
						});
					
						session.on('sending', function (data) {
							console.log('session::sending', data);
						});
					
						session.on('progress', function (data) {
							console.log('session::progress', data);
						});
					
						session.on('accepted', function (data) {
							console.log('session::accepted', data);
						});
					
						session.on('confirmed', function (data) {
							console.log('session::confirmed', data);
						});
					
						session.on('ended', function (data) {
							console.log('session::ended', data);
						});
					
						session.on('failed', function (data) {
							console.log('session::failed', data);
						});
					
						session.on('addstream', function (data) {
							console.log('session::addstream', data);
					
							remoteStream = data.stream;
							audioElement = document.querySelector('#remoteStream');
							audioElement.src = window.URL.createObjectURL(remoteStream);
							
							$(document).on('keydown', function(event) {
								console.log(event);
								var keys = {
									48: '0', 49: '1', 50: '2', 51: '3', 52: '4', 53: '5', 54: '6', 55: '7', 56: '8', 57: '9'
								};
								
								if (keys[event.which] === undefined) {
									return;
								}
								
								console.log('keydown: '+keys[event.which], event);
								var options = {
									'duration': 160,
									'eventHandlers': {
										'succeeded': function(originator, response) {
											console.log('DTMF succeeded', originator, response);
										},
										'failed': function(originator, response, cause) {
											console.log('DTMF failed', originator, response, cause);
										},
									}
								};
								session.sendDTMF(keys[event.which], options);
							});
						});
					
						session.on('removestream', function (data) {
							console.log('session::removestream', data);
						});
					
						session.on('newDTMF', function (data) {
							console.log('session::newDTMF', data);
						});
					
						session.on('hold', function (data) {
							console.log('session::hold', data);
						});
					
						session.on('unhold', function (data) {
							console.log('session::unhold', data);
						});
					
						session.on('muted', function (data) {
							console.log('session::muted', data);
						});
					
						session.on('unmuted', function (data) {
							console.log('session::unmuted', data);
						});
					
						session.on('reinvite', function (data) {
							console.log('session::reinvite', data);
						});
					
						session.on('update', function (data) {
							console.log('session::update', data);
						});
					
						session.on('refer', function (data) {
							console.log('session::refer', data);
						});
					
						session.on('replaces', function (data) {
							console.log('session::replaces', data);
						});
					
						session.on('sdp', function (data) {
							console.log('session::sdp', data);
						});
					
						session.answer({
							mediaConstraints: {
								audio: true,
								video: false
							},
							mediaStream: localStream
						});
					});
					
					phone.on('newMessage', function(e) {
						console.log('newMessage', e);
					});
					
					phone.on('registered', function(e) {
						var xmlhttp = new XMLHttpRequest();
						var query = "";
						
						query += "SIP_user_DiaL=" + SIP_user_Dial;
						query += "&session_id=" + session_id;
						query += "&phone_login=" + phone_login;
						query += "&phone_pass=" + phone_pass;
						query += "&VD_campaign=" + campaign;
						query += "&enable_sipsak=" + enable_sipsak;
						query += "&campaign_cid=" + campaign_cid;
						query += "&on_hook_agent=" + on_hook_agent;
						
						console.log('registered', e);
						//xmlhttp.open('GET', 'originate.php?' + query); 
						//xmlhttp.send(null); 
						//xmlhttp.onreadystatechange = function() { 
						//	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
						//		console.log('reply!');
						//	}
						//};
					});
					
					phone.on('unregistered', function(e) {
						console.log('unregistered', e);
					});
					
					phone.on('registrationFailed', function(e) {
						console.log('registrationFailed', e);
					});
					
					rtcninja.getUserMedia({
						audio: true,
						video: false
					}, function successCb(stream) {
						localStream = stream;
					
						phone.start();
					}, function failureCb(e) {
						console.error('getUserMedia failed.', e);
					});
					</script>
					<div id="dialog-custinfo" class="modal fade" tabindex="-1">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
									<h4>$custInfoTitle</h4>
								</div>
								<div class="modal-body">
									<form id="formMain" class="form-horizontal">
										<div class="list-group">
											<span name="callchannel" id="formMain_callchannel" style="display: none;"></span>
											<input type="hidden" name="callserverip" id="formMain_callserverip" value="" />
											<input type="hidden" name="uniqueid" id="formMain_uniqueid" value="" />
											<input type="hidden" name="lead_id" id="formMain_lead_id" value="" />
											<input type="hidden" name="list_id" id="formMain_list_id" value="" />
											<table width="100%" border=0>
												$labelHTML
											</table>
										</div>
									</form>
								</div>
								<div class="modal-footer">
									<button id="submitForm" class="btn btn-warning" data-dismiss="modal"><span class="fa fa-check-square-o" aria-hidden="true"></span> $submit</button>
								</div>
							</div>
						</div>
					</div>
					<div id="select-campaign" class="modal fade" tabindex="-1">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
									<h4 class="modal-title">$selectACampaign</h4>
								</div>
								<div class="modal-body">
									<div style='text-align: center;'>$availableCampaigns: &nbsp; <select id='select_camp'></select></div>
									<br />
									<div id="logSpinner" class="text-center hidden"><span style="font-size: 42px;" class="fa fa-spinner fa-pulse"></span></div>
									<div id="inboundSelection" class="clearfix hidden">
										<span style="min-width: 48%; margin: 0 5px;" class="text-center bold pull-left">$groupsNotSelected</span>
										<span style="min-width: 48%; margin: 0 5px;" class="text-center bold pull-right">$selectedGroups</span>
										<ul id="notSelectedINB" class="connectedINB pull-left"></ul>
										<ul id="selectedINB" class="connectedINB pull-right"></ul>
										<br />
									</div>
									<p class="text-center hidden" style="padding-top: 5px;"><input type='checkbox' name='closerSelectBlended' id='closerSelectBlended' value='closer' /> $blendedCalling ($outboundActivated)</p>
									<br />
									<p id="selectionNote" class="small text-center hidden" style="margin-bottom: 0px;"><b>$note</b>: $selectByDragging</p>
									<div style="text-align: center;">Use WebRTC: <input type="checkbox" name="use_webrtc" value="1" checked disabled /></div>
								</div>
								<div class="modal-footer">
									<button id="scButton" class="btn btn-link bold hidden">$selectAll</button>
									<button id="scSubmit" class="btn btn-warning disabled"><span class="fa fa-check-square-o" aria-hidden="true"></span> $submit</button>
								</div>
							</div>
						</div>
					</div>
					<div id="select-disposition" class="modal fade" tabindex="-1">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
									<h4 class="modal-title">$dispositionCall</h4>
								</div>
								<div class="modal-body">
									
								</div>
							</div>
						</div>
					</div>
					<div id="manual-dial-box" class="modal fade" tabindex="-1">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
									<h4 class="modal-title">$manualDialLead</h4>
								</div>
								<div class="modal-body">
									<table width="100%">
										<tr>
											<td align="right" style="padding-right: 10px">$dialCode:</td>
											<td align="left">
												<input type="text" size="7" maxlength="10" name="MDDiaLCodE" id="MDDiaLCodE" class="digits-only" value="1" />&nbsp; <small>($dialCodeInfo)</small>
											</td>
										</tr>
										<tr>
											<td align="right" style="padding-right: 10px">$phoneNumber:</td>
											<td align="left">
												<input type="text" size="14" maxlength="18" name="MDPhonENumbeR" id="MDPhonENumbeR" class="phonenumbers-only" value="" onkeyup="activateLinks();" onchange="activateLinks();" />&nbsp; <small>($digitsOnly)</small>
												<input type="hidden" name="MDPhonENumbeRHiddeN" id="MDPhonENumbeRHiddeN" value="" />
												<input type="hidden" name="MDLeadID" id="MDLeadID" value="" />
												<input type="hidden" name="MDType" id="MDType" value="" />
											</td>
										</tr>
										<tr>
											<td align="right" style="padding-right: 10px">$searchExistingLeads:</td>
											<td align="left">
												<input type="checkbox" name="LeadLookUP" id="LeadLookUP" size="1" value="0" disabled /><!--&nbsp; ($searchExistingLeadsInfo)-->
											</td>
										</tr>
										<tr>
											<td align="left" colspan="2" style="display:none;">
											<br /><br />
											<CENTER>
												<span id="ManuaLDiaLGrouPSelecteD"></span> &nbsp; &nbsp; <span id="ManuaLDiaLGrouP"></span>
												<br><br>
												<span id="ManuaLDiaLInGrouPSelecteD"></span> &nbsp; &nbsp; <span id="ManuaLDiaLInGrouP"></span>
												<br><br>
												<span id="NoDiaLSelecteD"></span>
											</CENTER>
											<br /><br />$dialOverrideInfo<br /> &nbsp; </td>
										</tr>
										<tr style="display:none;">
											<td align="right">$dialOverride:</td>
											<td align="left">
												<input type="text" size="24" maxlength="20" name="MDDiaLOverridE" id="MDDiaLOverridE" class="cust_form" value="" />&nbsp; ($digitsOnlyPlease)
											</td>
										</tr>
									</table>
								</div>
								<div class="modal-footer">
									<button id="manual-dial-now" class="btn btn-warning disabled">$dialNow</button>
									<button id="manual-dial-preview" class="btn btn-default disabled">$previewCall</button>
								</div>
							</div>
						</div>
					</div>
					
EOF;
		return $str;
	}
	
	// hooks
	private function getLabels($type='system_settings', $label_id=null) {
		//set variables
		$camp = (isset($_SESSION['campaign_id'])) ? $_SESSION['campaign_id'] : null;
		$url = gourl.'/goAgent/goAPI.php';
		$fields = array(
			'goAction' => 'goGetLabels',
			'goUser' => goUser,
			'goPass' => goPass,
			'responsetype' => responsetype,
			'goTableName' => $type,
			'goLabelID' => $label_id,
			'goCampaign' => $camp
		);
		
		//url-ify the data for the POST
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string, '&');
		
		//open connection
		$ch = curl_init();
		
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		
		//execute post
		$result = json_decode(curl_exec($ch));
		
		//close connection
		curl_close($ch);
		
		return $result->data;
	}
	
	// settings
	public function moduleSettings() {
		return array("GO_agent_url" => CRM_SETTING_TYPE_STRING, "GO_agent_url_info" => CRM_SETTING_TYPE_LABEL, "GO_agent_db" => CRM_SETTING_TYPE_STRING, "GO_agent_user" => CRM_SETTING_TYPE_STRING, "GO_agent_pass" => CRM_SETTING_TYPE_PASS, "GO_agent_db_info" => CRM_SETTING_TYPE_LABEL);
	}
}

?>