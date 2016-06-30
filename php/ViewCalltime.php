<?php

	/** Calltimes API - View calltime */
	/**
	 * Generates action circle buttons for different pages/module
	 * @param goUser 
	 * @param goPass 
	 * @param goAction 
	 * @param responsetype
	 * @param call_time_id
	 */
        require_once('goCRMAPISettings.php');
        
<<<<<<< HEAD
        $url = gourl."/goCalltimes/goAPI.php"; #URL to GoAutoDial API. (required)
=======
        $url = gourl."/goAPI/goCalltimes/goAPI.php"; #URL to GoAutoDial API. (required)
>>>>>>> a5452525bbc1a7d8e694b130debcb946dca6d2d2
        
        $postfields["goUser"] = goUser; #Username goes here. (required)
        $postfields["goPass"] = goPass; #Password goes here. (required)
        $postfields["goAction"] = "getCalltimesInfo"; #action performed by the [[API:Functions]]. (required)
        $postfields["responsetype"] = responsetype; #json. (required)
        $postfields["call_time_id"] = $_POST['call_time_id']; #Desired uniqueid. (required)
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        $data = curl_exec($ch);
        curl_close($ch);
        
        echo $data;
?>
