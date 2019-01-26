

<?php // callback.php
// กรณีต้องการตรวจสอบการแจ้ง error ให้เปิด 3 บรรทัดล่างนี้ให้ทำงาน กรณีไม่ ให้ comment ปิดไป
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//require __DIR__."/vendor/autoload.php";
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use \Statickidz\GoogleTranslate;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
use LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
use LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use LINE\LINEBot\ImagemapActionBuilder;
use LINE\LINEBot\ImagemapActionBuilder\AreaBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder ;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder;
use LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder;
use LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\DatetimePickerTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\Flex;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder;
$logger = new Logger('LineBot');
$logger->pushHandler(new StreamHandler('php://stderr', Logger::DEBUG));

define("MLAB_API_KEY", '6QxfLc4uRn3vWrlgzsWtzTXBW7CYVsQv');
define("LINE_MESSAGING_API_CHANNEL_SECRET", '82d7948950b54381bcbd0345be0d4a2c');
define("LINE_MESSAGING_API_CHANNEL_TOKEN", 'BYnvAcR40qJk4fLopvVtVozF00iUqfUjoD33tIPcnjMoXEyG3fzYSE24XRKB5lnttxPePUIHPWdylLdkROwbOESi4rQE3+oSG3njcFj7yoQuaqU27effhhF4lz6lbOfhPjD9mLvHWYZlSbeigV4ETAdB04t89/1O/w1cDnyilFU=');

$bot = new \LINE\LINEBot(
    new \LINE\LINEBot\HTTPClient\CurlHTTPClient(LINE_MESSAGING_API_CHANNEL_TOKEN),
    ['channelSecret' => LINE_MESSAGING_API_CHANNEL_SECRET]
);
$signature = $_SERVER["HTTP_".\LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
try {
	$events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);
} catch(\LINE\LINEBot\Exception\InvalidSignatureException $e) {
	error_log('parseEventRequest failed. InvalidSignatureException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownEventTypeException $e) {
	error_log('parseEventRequest failed. UnknownEventTypeException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownMessageTypeException $e) {
	error_log('parseEventRequest failed. UnknownMessageTypeException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\InvalidEventRequestException $e) {
	error_log('parseEventRequest failed. InvalidEventRequestException => '.var_export($e, true));
}

        
foreach ($events as $event) {
	$replyToken = $event->getReplyToken();
	$replyData='No Data';
                
  // Postback Event
    if (($event instanceof \LINE\LINEBot\Event\PostbackEvent)) {
		$logger->info('Postback message has come');
		continue;
	}
	// Location Event
    if  ($event instanceof LINE\LINEBot\Event\MessageEvent\LocationMessage) {
		$logger->info("location -> ".$event->getLatitude().",".$event->getLongitude());
	        $multiMessage =     new MultiMessageBuilder;
	        $textReplyMessage= "location -> ".$event->getLatitude().",".$event->getLongitude();
                $textMessage = new TextMessageBuilder($textReplyMessage);
		$multiMessage->add($textMessage);
	        $replyData = $multiMessage;
	        $response = $bot->replyMessage($replyToken,$replyData);
		continue;
	}
    if ($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage) {
       
        $text = $event->getText();
        $text = strtolower($text);
        $explodeText=explode(" ",$text);
	$textReplyMessage="";
	   
        $multiMessage =     new MultiMessageBuilder;
	    /*
	$groupId='';$roomId='';$userId=''; $userDisplayName='';// default value          
	    
	    // ส่วนตรวจสอบผู้ใช้
		$userId=$event->getUserId();
	   // if((!is_null($userId)){
		$response = $bot->getProfile($userId);
                if ($response->isSucceeded()) {// ดึงค่าโดยแปลจาก JSON String .ให้อยู่ใรูปแบบโครงสร้าง ตัวแปร array 
                   $userData = $response->getJSONDecodedBody(); // return array     
                            // $userData['userId'] // $userData['displayName'] // $userData['pictureUrl']                            // $userData['statusMessage']
                   $userDisplayName = $userData['displayName']; 
		   //$bot->replyText($replyToken, $userDisplayName); ใช้ตรวจสอบว่าผู้ถาม ชื่อ อะไร	
		}else{
		 //$bot->replyText($replyToken, $userId);  ใช้ตรวจสอบว่าผู้ถาม ID อะไร	
			$userDisplayName = $userId;
		}// end get profile
	   // }//end is_null($userId);
	     $textReplyMessage = 'ตอบคุณ '.$userDisplayName.' User id : '.$userId; 
                    $textMessage = new TextMessageBuilder($textReplyMessage);
		    $multiMessage->add($textMessage);
		// จบส่วนการตรวจสอบผู้ใช้
		*/
		      
      switch ($explodeText[0]) {
		
	case '#i':
		      
		   
		 //$picFullSize = $userData['pictureUrl';
                          // $picThumbnail = $userData['pictureUrl';
			  // $imageMessage = new ImageMessageBuilder($picFullSize,$picThumbnail);
			  // $multiMessage->add($imageMessage);
		/* ส่วนดึงข้อมูลจากฐานข้อมูล */
		if (!is_null($explodeText[1])){
		   $json = file_get_contents('https://api.mlab.com/api/1/databases/hooqline/collections/people?apiKey='.MLAB_API_KEY.'&q={"nationid":"'.$explodeText[1].'"}');
                   $data = json_decode($json);
                   $isData=sizeof($data);
		      
                 if($isData >0){
		    $count=1;
                    foreach($data as $rec){
			   $count++;
                           $textReplyMessage= "\nหมายเลข ปชช. ".$rec->nationid."\nชื่อ".$rec->name."\nที่อยู่".$rec->address."\nหมายเหตุ".$rec->note;
                           $textMessage = new TextMessageBuilder($textReplyMessage);
			   $multiMessage->add($textMessage);
			   $textReplyMessage= "https://www.hooq.info/img/$rec->nationid.png";
                           $textMessage = new TextMessageBuilder($textReplyMessage);
			   $multiMessage->add($textMessage);
			   $picFullSize = "https://www.hooq.info/img/$rec->nationid.png";
                           $picThumbnail = "https://www.hooq.info/img/$rec->nationid.png";
			   //$picFullSize = 'https://s.isanook.com/sp/0/rp/r/w700/ya0xa0m1w0/aHR0cHM6Ly9zLmlzYW5vb2suY29tL3NwLzAvdWQvMTY2LzgzNDUzOS9sb3ZlcmppbmEuanBn.jpg';
                           //$picThumbnail = 'https://s.isanook.com/sp/0/rp/r/w700/ya0xa0m1w0/aHR0cHM6Ly9zLmlzYW5vb2suY29tL3NwLzAvdWQvMTY2LzgzNDUzOS9sb3ZlcmppbmEuanBn.jpg';
                           $imageMessage = new ImageMessageBuilder($picFullSize,$picThumbnail);
			   $multiMessage->add($imageMessage);
			    //$arrayPostData['to']=$replyId;
			    //$arrayPostData['messages'][0]['type']="text";
			    //$arrayPostData['messages'][0]['text']="hello";
			    //pushMsg($arrayHeader,$arrayPostData);
                           }//end for each
	            $replyData = $multiMessage;
			 
		   }else{ //$isData <0  ไม่พบข้อมูลที่ค้นหา
		          $textReplyMessage= "ไม่พบ ".$explodeText[1]."  ในฐานข้อมูลของหน่วย"; 
			  $textMessage = new TextMessageBuilder($textReplyMessage);
			  $multiMessage->add($textMessage);
			  $ranNumber=rand(1,214);
			  $picFullSize = "https://www.hooq.info/photos/$ranNumber.jpg";
                          $picThumbnail = "https://www.hooq.info/photos/thumbnails/tn_$ranNumber.jpg";
			  $imageMessage = new ImageMessageBuilder($picFullSize,$picThumbnail);
			  $multiMessage->add($imageMessage);
			  $replyData = $multiMessage;
			 // กรณีจะตอบเฉพาะข้อความ
		      //$bot->replyText($replyToken, $textMessage);
		        } // end $isData>0
		   }else{ // no $explodeText[1]
	                $textReplyMessage= "คุณให้ข้อมูลในการสอบถามไม่ครบถ้วนค่ะ"; 
			$textMessage = new TextMessageBuilder($textReplyMessage);
			  $multiMessage->add($textMessage);
			  $ranNumber=rand(1,214);
			  $picFullSize = "https://www.hooq.info/photos/$ranNumber.jpg";
                          $picThumbnail = "https://www.hooq.info/photos/thumbnails/tn_$ranNumber.jpg";
			  $imageMessage = new ImageMessageBuilder($picFullSize,$picThumbnail);
			  $multiMessage->add($imageMessage);
			  $replyData = $multiMessage;
			 // กรณีจะตอบเฉพาะข้อความ
		      //$bot->replyText($replyToken, $textMessage);
		   }// end !is_null($explodeText[1])
		/* จบส่วนดึงข้อมูลจากฐานข้อมูล */
		      
		
		break; // break case #i
	      case '$':
		      
		      
		          $textReplyMessage= "ไม่เอาไม่พูด ,".$explodeText[1].",\n  ดูภาพแก้เซ็งดีกว่าค่ะ "; 
			$textMessage = new TextMessageBuilder($textReplyMessage);
			  $multiMessage->add($textMessage);
		          $image=rand(1,214);
			
			  $picFullSize = "https://www.hooq.info/photos/$image.jpg";
                          $picThumbnail = "https://www.hooq.info/photos/thumbnails/tn_$image.jpg";
                          $imageMessage = new ImageMessageBuilder($picFullSize,$picThumbnail);
			  $multiMessage->add($imageMessage);
		          $image2=$image+1;
		
			  $picFullSize = "https://www.hooq.info/photos/$image2.jpg";
                          $picThumbnail = "https://www.hooq.info/photos/thumbnails/tn_$image2.jpg";
                          $imageMessage = new ImageMessageBuilder($picFullSize,$picThumbnail);
			  $multiMessage->add($imageMessage);
		       $image3=$image+2;
		
			  $picFullSize = "https://www.hooq.info/photos/$image3.jpg";
                          $picThumbnail = "https://www.hooq.info/photos/thumbnails/tn_$image3.jpg";
                          $imageMessage = new ImageMessageBuilder($picFullSize,$picThumbnail);
			  $multiMessage->add($imageMessage);
		          
			  $replyData = $multiMessage;
		break; //break case $
          case 'สอนเป็ด':
           
            //Post New Data
		      $indexCount=1;$answer='';
	    foreach($explodeText as $rec){
		    $indexCount++;
		    if($indexCount>1){
		    $answer= $answer." ".$explodeText[$indexCount]; 
		    }
	    }  
            $newData = json_encode(array('question' => $explodeText[1],'answer'=> $answer) );
            $opts = array('http' => array( 'method' => "POST",
                                          'header' => "Content-type: application/json",
                                          'content' => $newData
                                           )
                                        );
            // เพิ่มเงื่อนไข ตรวจสอบว่ามีข้อมูลในฐานข้อมูลหรือยัง
            
            $url = 'https://api.mlab.com/api/1/databases/hooqline/collections/hooqbot?apiKey='.MLAB_API_KEY.'';
            $context = stream_context_create($opts);
            $returnValue = file_get_contents($url,false,$context);
            if($returnValue)$text = 'ขอบคุณที่สอนเป็ด ฮะ ';
            else $text="Cannot teach Ducky";
            $bot->replyText($replyToken, $text);
            break;
         
          default:
		 //$textMessage= $userDisplayName."คุณไม่ได้ถามตามที่กำหนดค่ะ".$replyId.$userId; 
		 //$bot->replyText($replyToken, $textMessage);
	      $url = 'https://api.mlab.com/api/1/databases/hooqline/collections/hooqbot?apiKey='.MLAB_API_KEY.'';
              $json = file_get_contents('https://api.mlab.com/api/1/databases/hooqline/collections/hooqbot?apiKey='.MLAB_API_KEY.'&q={"question":"'.$explodeText[0].'"}');
              $data = json_decode($json);
              $isData=sizeof($data);
		  $text='';
              if($isData >0){
                foreach($data as $rec){
                  $text= $text.$rec->answer."\n";
                  //-----------------------
                }//end for each
              }else{
                  $text='';
		      break;
                  //$text= $explodeText[0];
                  //$bot->replyText($reply_token, $text);
              }//end no data from mlab
		      
                    $textReplyMessage= $text; 
			$textMessage = new TextMessageBuilder($textReplyMessage);
			  $multiMessage->add($textMessage);
		          $image=rand(1,214);
			
			  $picFullSize = "https://www.hooq.info/photos/$image.jpg";
                          $picThumbnail = "https://www.hooq.info/photos/thumbnails/tn_$image.jpg";
                          $imageMessage = new ImageMessageBuilder($picFullSize,$picThumbnail);
			  $multiMessage->add($imageMessage);
			  $replyData = $multiMessage;
		break;
            }//end switch
	    
	   // ส่วนส่งกลับข้อมูลให้ LINE
           $response = $bot->replyMessage($replyToken,$replyData);
           if ($response->isSucceeded()) {
              echo 'Succeeded!';
              return;
              }
 
              // Failed ส่งข้อความไม่สำเร็จ
             $statusMessage = $response->getHTTPStatus() . ' ' . $response->getRawBody();
             echo $statusMessage;
             $bot->replyText($replyToken, $statusMessage);
         }//end if event is textMessage
}// end foreach event
	

function getSurname($surname){
	      $json = file_get_contents('https://api.mlab.com/api/1/databases/hooqline/collections/intelphonebook?apiKey='.MLAB_API_KEY.'&q={"surname":"'.$surname.'"}');
              $data = json_decode($json);
              $isData=sizeof($data);
              if($isData >0){
		   $text="";
		   $count=1;
                foreach($data as $rec){
                 $text= $text.$count.' '.$rec->name.' '.$rec->surname.' ('.$rec->nickname.' ฉายา '.$rec->nickname2.') '.$rec->jobposition.' โทร'.$rec->telephone.' '.$rec->address."\n\n";
                  $count++;
                }//end for each
	      }else{
		  $text= "ไม่พบข้อมูลนามสกุล ".$explodeText[1];
	      }
               return $text;                  
		 }

/*
function pushMsg($arrayHeader,$arrayPostData){
		 $strUrl ="https://api.line.me/v2/bot/message/push";
		 $ch=curl_init();
		 curl_setopt($ch,CURLOPT_URL,$strUrl);
		 curl_setopt($ch,CURLOPT_HEADER,false);
		 curl_setopt($ch,CURLOPT_POST,true);
		 curl_setopt($ch,CURLOPT_HTTPHEADER,$arrayHeader);
		 curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($arrayPostData));
		 curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		 curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		 $result=curl_exec($ch);
		 curl_close($ch);
		 }
		 */
?>
