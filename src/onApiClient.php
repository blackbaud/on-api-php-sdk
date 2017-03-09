<?php namespace Blackbaud\onSDK;
/**
*
*   This Class is for connecting to the WhippleHill Rest API.
*
**/
use Exception;

class onApiClient  {
    private $_apiuser;

    private $_apipass;

    private $_api;
    private $_vendor;

    private $_token;

    private $_sProfileId;

    private $_sStartDate;

    private $_sEndDate;

    private $_user_id;

    private $_bUseCache;

    private $_iCacheAge;

    private $_screen = false;

    private $_options = array(
        'debug'=>'false',
        'dev_sid'=>''
    );

    public static function testing()
    {
        return 'Just a test :)';
    }

    public function __construct($on_api_domain, $user, $pass , $vendor = false, $options = array() ) {

        $this->_apiuser = $user;

        $this->_apipass = $pass;
        $this->_vendor = $vendor;

        $this->_api = $on_api_domain. "/api/";

        $this->_options = array_merge($this->_options, $options);

        


        if($this->option('debug')){
            $this->debug_mode();
        }

        //$this->_bUseCache = false;
       
       

    }

    function option($o, $v= null){
        if(!isset($v))
            return isset($this->_options[$o]) ? $this->_options[$o] : false;

        $this->_options[$o] = $v;

    }

    function debug_mode(){
        $this->_screen = true;
    }


    function signin($username, $password){
        $data = $this->getUrl($this->_api."signin",  array('username'=>$username ,'password'=>$password )  );
        $data = json_decode($data);
        return $data;
    }

    public function reAuth(){
        $this->auth(true);
    }

    private function auth( $re_auth = false ){

        if($this->_token && $re_auth == false){
            return;
        }

         $vendor = "";
        if($this->_vendor !== false){
            $vendor = "&vendorkey=".$this->_vendor;
        }

        $data = $this->getUrl($this->_api."authentication/login?username=".$this->_apiuser."&password=".$this->_apipass."&format=json".$vendor );
        $data = json_decode($data);
        if(isset($data->Token)){
            $this->_token = $data->Token;
            $this->_user_id =  $data->UserId;
        }
    }

    /**
    *
    *   User Actions
    *
    **/

    function get_emergency_phone( $id = false ){
        if($id == false){
            $id = $this->user_id();
        }
        return $this->get_api( "user/emergencycontactphone/", array('userid' => $id) );

    }

    function update_emergency_phone( $id = false ){
         if($id == false){
            $id = $this->user_id();
        }
        $d= $this->get_emergency_phone();

        $x =  array();
        /*
        print_r($d);
            $x["UserId"] = 2852800;
            $x["ContactId"] = 0;
            $x["ContactEmailId"] = 0;
            $x["ContactPhoneId"] = 0;
            $x["UserContactId"] = 0;
            $x["FirstName"] = "my test";
            $x["LastName"] =  "my test";
            $x["Relationship"] = 'sdfg';
            $x["RelationshipUserId"] = 2852800;
            $x["RelationshipId"] = -1;
            $x["ContactCallDialerInd"] = 1;
            $x["Email"] = "";
            $x["PhoneNumber"] = "6036695979" ;                   
            $x["PhoneType"] = "Wireless";
            $x["PhoneIndexId"] = 0;
            $x["PhoneId"] = 0;
            $x["SortOrder"] = 2;
            $x["InsertDate"] = null;
            $x["LastModifyDate"] = null;
            $x["LastModifyUserId"] = null;
            $x["FieldsToNull"] = array();

        
       array_push($d,(object)$x);
      */
        return $this->put_api("user/emergencycontactphone/". $id."/", array('userid' => $id) , $d);
    }


    function get_news($id){
        $this->get_api();
    }

    function user_id(){
        return $this->_user_id;
    }

    function get_current_user(){
        return $this->get_api( "user/". $this->user_id() );
    }
    
    function get_user( $user_id ){
        return $this->get_api("user/". $user_id );
    }

    function get_current_user_extended(){
        return $this->get_api( "user/extended/". $this->user_id() );
    }

    function get_user_extended( $user_id , $params = array() ){
        return $this->get_api("user/extended/". $user_id, $params );
    }

    function get_user_address_all( $user_id , $type = null){
        $params =  array(
            "userId"=>$user_id,
           // "type"=>$type
            );

        return $this->get_api("user/address/all/", $params);
    }


    function put_api( $method , $url_params = array() , $putdata = array() ){

        return json_decode( $this->getUrl( $this->_api . $method . $this->format_options( $url_params ) , $putdata, false, 'PUT' )  );
    }

    function post_api( $method , $url_params = array() , $postdate = array() ){

        return json_decode( $this->getUrl( $this->_api . $method . $this->format_options( $url_params ) , $postdate, false, 'PUT' )  );
    }
    function get_api( $method , $params = array() ){
        return json_decode( $this->getUrl( $this->_api . $method . $this->format_options( $params ) )  );
    }


    private function format_options($params = array() ){
        $this->auth();
        $params['format'] = 'json';
        $params['t']= $this->_token;
        //for internal whipplehill use only - 
        //$params['dev_sid'] = '161';
        //$params['vendorkey'] = 'evertrue';
        $options = '';
        
        $keys = array_keys($params);
        $values = array_values($params);
        
        for($i = 0; $i < count($params); $i++){
             if (isset( $values[$i] )){
                if($options == ''){$options .= "?";}else{$options .= "&";}
               
                     $options .= $keys[$i]."=".$values[$i];
                
            }
        }
        
        return $options;
    }

    /**
    * Get data from given URL
    * Uses Curl if installed, falls back to file_get_contents if not
    * 
    * @param string $sUrl
    * @param array $aPost
    * @param array $aHeader
    * @return string Response
    */

    private function getUrl($sUrl, $aPost = array(), $aHeader = array() , $method = 'GET'){
        /*
        if($this->_screen == true){
            echo "Making call to: <br>";
            echo $sUrl;
            echo "<br><br>";
        }
        */

        if (count($aPost) > 0 && $method =='POST'){

            // build POST query

            $sPost = json_encode($aPost);   
            $sMethod = 'POST'; 

            $sPost = http_build_query($aPost);    

            $aHeader[] = 'Content-type: application/json';

            $aHeader[] = 'Content-Length: ' . strlen($sPost);

            $sContent = $aPost;

        } elseif (count($aPost) > 0 && $method =='PUT'){
            
            $sMethod = 'PUT'; 

            //$sPost = http_build_query($aPost);    
            $sPost = json_encode($aPost);
            $aHeader[] = 'Content-type: application/json';

            $aHeader[] = 'Content-Length: ' . strlen($sPost);

            $sContent = $aPost;
        }

        else {

            $sMethod = 'GET';

            $sContent = null;

        }

        

        if (function_exists('curl_init')){

            // If Curl is installed, use it!

            $rRequest = curl_init();

            curl_setopt($rRequest, CURLOPT_URL, $sUrl);

            curl_setopt($rRequest, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($rRequest, CURLOPT_SSL_VERIFYPEER, false);
            
            if ($sMethod == 'POST'){

                curl_setopt($rRequest, CURLOPT_POST, 1); 
                curl_setopt($rRequest, CURLOPT_POSTFIELDS, $sContent);

            } elseif ($sMethod == 'PUT'){
                curl_setopt($rRequest, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($rRequest, CURLOPT_POSTFIELDS, $sContent);

            }
            else {

                curl_setopt($rRequest, CURLOPT_HTTPHEADER, $aHeader);

            }

            $sOutput = curl_exec($rRequest);

            if ($sOutput === false){

                throw new Exception('Curl error (' . curl_error($rRequest) . ')');    

            }

            $aInfo = curl_getinfo($rRequest);

           // print_r($aInfo);

            if ($aInfo['http_code'] != 200){

                if ($aInfo['http_code'] == 400){

                    throw new Exception('Bad request (' . $aInfo['http_code'] . ') url: ' . $sUrl);     

                }

                if ($aInfo['http_code'] == 403){

                    throw new Exception('Access denied (' . $aInfo['http_code'] . ') url: ' . $sUrl);     

                }

                throw new Exception('Not a valid response (' . $aInfo['http_code'] . ') url: ' . $sUrl);

            }
        
            curl_close($rRequest);

        } else {

            // Curl is not installed, use file_get_contents

            // create headers and post

            $aContext = array('http' => array ( 'method' => $sMethod,

                                                'header'=> implode("\r\n", $aHeader) . "\r\n",

                                                'content' => $sContent));

            $rContext = stream_context_create($aContext);

            $sOutput = @file_get_contents($sUrl, 0, $rContext);

            if (strpos($http_response_header[0], '200') === false){

                throw new Exception('Not a valid response (' . $http_response_header[0] . ') url: ' . $sUrl);       

            }

        }

        return $sOutput;

    }   


}


