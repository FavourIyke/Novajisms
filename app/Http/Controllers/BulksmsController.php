<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Laravel\Lumen\Routing\Controller as BaseController;

class BulksmsController extends BaseController
{
    //
    var $host;
var $port;
/*
* Username that is to be used for submission
*/
var $strUserName;
/*
* password that is to be used along with username 
*/
var $strPassword;
/*
* Sender Id to be used for submitting the message
*/
var $strSender; 
/*
* Message content that is to be transmitted 
*/
var $strMessage;
/*
* Mobile No is to be transmitted. 
*/
var $strMobile;

/*
* What type of the message that is to be sent 
* <ul>
* <li>0:means plain text</li> 
* <li>1:means flash</li>
* <li>2:means Unicode (Message content should be in Hex)</li>
* <li>6:means Unicode Flash (Message content should be in Hex)</li>
* </ul>
*/
var $strMessageType;
/*
* Require DLR or not *
<ul>
* <li>0:means DLR is not Required</li>
* <li>1:means DLR is Required</li>
* </ul> 
*/
var $strDlr;
private function sms__unicode($message){ 
$hex1='';
if (function_exists('iconv')) {
$latin = @iconv('UTF-8', 'ISO-8859-1', $message);
if (strcmp($latin, $message)) {
$arr = unpack('H*hex', @iconv('UTF-8', 'UCS-2BE', 
$message));
$hex1 = strtoupper($arr['hex']);
}
if($hex1 ==''){
$hex2='';
$hex='';
for ($i=0; $i < strlen($message); $i++)
{ 
$hex = dechex(ord($message[$i]));
$len =strlen($hex);
$add = 4 - $len; 
if($len < 4)
{
for($j=0;$j<$add;$j++)
{ $hex="0".$hex;
} 
}
$hex2.=$hex;
}
return $hex2;
}
else{
return $hex1;

}
} 
else{
print 'iconv Function Not Exists !';
}
} //Constructor..
public function Sender ($host,$username,$password,$sender, 
$message,$mobile, $msgtype,$dlr){
$this->host=$host; 
//$this->port=$port;
$this->strUserName = $username;
$this->strPassword = $password; 
$this->strSender= $sender;
$this->strMessage=$message; //URL Encode The Message.. 
$this->strMobile=$mobile;
$this->strMessageType=$msgtype; 
$this->strDlr=$dlr;
}
public function send(Request $request){
  $host="api.rmlconnect.net"; 
  //$this->port=$port;
  $strUserName = $request->username;
  $strPassword = $request->password; 
  $strSender= $request->sender;
  $strMessage= $request->message; //URL Encode The Message.. 
  $strMobile= $request->mobile;
  $strMessageType="2"; 
  $strDlr="1";

if($strMessageType=="2" || $strMessageType=="6") {
//Call The Function Of String To HEX.
$strMessage = $this->sms__unicode($strMessage);
try{
//Smpp http Url to send sms.
$live_url="http://".$host."/bulksms/bulksms?username=".$strUserName."&password=".$strPassword."&type=".$strMessageType."&dlr=".$strDlr."&destination=".$strMobile."&source=".$strSender."&message=".$strMessage."";
 $parse_url=file($live_url);
//echo $parse_url[0]; 
}catch(Exception $e){
//echo 'Message:' .$e->getMessage(); 
}
} else
$strMessage=urlencode($strMessage);
try{
// http Url to send sms.
$live_url="http://".$host."/bulksms/bulksms?username=".$strUserName."&password=".$strPassword."&type=".$strMessageType."&dlr=".$strDlr."&destination=".$strMobile."&source=".$strSender."&message=".$strMessage."";
$parse_url=file($live_url);
$encode = $parse_url[0]; 

//echo $parse_url[0];

$explode = explode("|",$encode);

$arrLength = count($explode);
$first =  $explode[0];
$second = "null";
$third = "null";
$message = "null";

if($first === "1701"){
$message = "Message Submitted succssfully";
}

if($first === "1703"){
  $message = "Invalid value in username or password parameter.";
  }

  if($first === "1702"){
    $message = "Invalid URL.";
    }
    
  if($first === "1704"){
      $message = "Invalid value in type parameter.";
      }
  if($first === "1705"){
        $message = "Invalid message.";
          }
  if($first === "1706"){
          $message = "Invalid destination.";
           }
  if($first === "1707"){
           $message = "Invalid source (Sender).";
            }
  if($first === "1708"){
          $message = "Invalid value for dlr parameter. ";
            }
  if($first === "1709"){
                $message = "User validation failed.";
            }
  if($first === "1710"){
              $message = "Internal error. ";
            }
  if($first === "1025"){
              $message = "Insufficient credit.";
            }
  if($first === "1715"){
              $message = "Response timeout.";
            }
        if($first === "1032"){
              $message = "DND reject.";
            }
         if($first === "1028"){
              $message = "Spam message";
            }

if($arrLength > 1){
  $second = $explode[1];
}

if($arrLength > 2){
  $third = $explode[2];
}


echo '{"code":"'.$first.'","destination":"'.$second.'","id":"'.$third.'","message":"'.$message.'"}';
}

catch(Exception $e){
echo 'Message:' .$e->getMessage(); 
}
} 

}


