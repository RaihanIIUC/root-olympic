<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\UssdReceiver;
use App\Classes\UssdSender;
use App\Classes\UssdException;
use App\Classes\Logger;
use App\Classes\Subscription;
use App\Classes\SubscriptionException;

use App\Classes\SMSSender;
use App\Classes\SMSReceiver;
use App\Classes\SMSServiceException;

use App\Classes\SubscriptionReceiver;

use App\Models\item;
use App\Models\route;
use App\Models\error_message;
use App\Models\download_message;
use App\Models\voucher_details;
use App\Models\voucher_head;
use Session;
use App\Models\route_with_number;
use App\Models\response_log;
use DB;

class BdappsController extends Controller
{
    //
	       protected $cur_date;//= date("Y-m-d");
	   protected $prev_date;// = date('Y-m-d', strtotime('-7 days'));
	   protected $time;
	   
	     public function __construct()
  {
	  date_default_timezone_set("Asia/Dhaka");
    $this->cur_date = date('Ymd');
	$this->time = date('h:i:s');
	$this->prev_date = date('Y-m-d', strtotime('-7 days'));
  }
    
	public function resend_sms()
	{
			 $server = 'https://developer.bdapps.com/sms/send';
        $appid = "APP_036385";
        $apppassword = "00febb6e06c0c8a30c268f18d69de401";
        $logger = new Logger();
		$sender = new SMSSender($server, $appid, $apppassword);
		$datas = response_log::where('statusCode','!=','S1000')->where('timeStamp','LIKE',$this->cur_date."%")->take(10)->get();
		$myfile = fopen("tmp_file.txt", "a+") or die("Unable to open file!");
		//fwrite($myfile,json_encode($datas)." ".$this->cur_date."\n");
		foreach($datas as $data)
		{
			 $response = $sender->sms('Thanks for your response', $data->address);
			 fwrite($myfile,$response->timeStamp." ".$this->time." ".$response->address."\n");
			 response_log::where('id',$data->id)->update(['timeStamp'=>$response->timeStamp,'address'=>$response->address,'messageId'=>$response->messageId,'statusDetail'=>$response->statusDetail,'statusCode'=>$response->statusCode]);
			 usleep(100000);

		}
		fclose($myfile);
		// $response = $sender->sms('Thanks for your response', $address);
			
	}

    public function split_msg($string)
    {	
		$string = strtoupper ($string);
        $strings = explode(".", $string);

        $splited_string = explode(".", $string);

        //print_r($strings);
		if(sizeof($splited_string)==4)
		{
        $strings = str_split($strings[3]);

        //print("<br>");
        $ch = "c";
        $i = 0;
        $prod = "";
        $val = "";
        $arr;
        foreach ($strings as $char)
        {
            if (!is_numeric($char))
            {
                if ($ch == "c") $prod .= $char;
                else
                {
                    $arr[$i++] = $prod;
                    $prod = $char;
                    $ch = "c";
                }
            }
            if (is_numeric($char))
            {
                if ($ch == "n") $prod .= $char;
                else
                {
                    $arr[$i++] = $prod;
                    $prod = $char;
                    $ch = "n";
                }
            }
            $arr[$i] = $prod;
        }
        $final = array(
            $arr,
            $splited_string
        );
		 return $final;
		}
		else{
			$final = "error";
			return $final;
		}
		
       
    }
	public function edit_download_msg()
	{
		$download_message = download_message::all();
		
		$myfile = fopen("test_file.txt", "a+") or die("Unable to open file!");
		foreach($download_message as $msg)
		{
			 $error_msg = "";
			$message = $msg->message_text;
			
			$sl =$msg->id;
			
			
			
			$current_date = date('Y-m-d H:i:s');
                $text = $this->split_msg($message);
				
                $arr = $text[0];
                $string = $text[1];
				$final_address = $msg->mobile_number;
                $type = $string[0];
                $date2 = $string[1];
                $order = $date2;
                $order_date = $order[0] . $order[1];
                $order_month = $order[2] . $order[3];
                $order_year = '2021';
                $date = $order_year . "/" . $order_month . "/" . $order_date;
                $date = strtotime($date);
                $date = date('Y-m-d', $date);
                $route = $string['2'];
               
                $items = array();
                    for ($i = 0;$i < (sizeof($arr) / 2) - 1;$i++)
                    {
                        $item = $arr[2 * $i];
						array_push($items,trim($item));
                      
						
					}
					$item_count = array_count_values($items);
					
					foreach($item_count as $key=>$value)
					{
						if($value>1)
						{
							$error_msg .= $key . " contains " .$value. ' times ,';
						}
					}
               

                if ($error_msg)
                {
					//download_message::where('id',$sl)->delete();
			//fwrite($myfile,$sl."\n");
                    // file_put_contents('test2.txt',$error_msg);
                   // $error = error_message::create(['msg_date' => $current_date, 'mobile_number' => $final_address, 'sms_text' => $message, 'error_report' => $error_msg, 'status' => 0]);
                 
                   // error_message::where('id', $error->id)
                        //->update(['sl' => $error->id]);
						//voucher_details::where('sl',$sl)->delete();
						//voucher_head::where('sl',$sl)->delete();
                }
                

              
                
            }
         
			
		
	}
    public function edit_sms()
    {
        $server = 'https://developer.bdapps.com/sms/send';
        $appid = "APP_036385";
        $apppassword = "00febb6e06c0c8a30c268f18d69de401";
        $logger = new Logger();

        $error_msg = error_message::where('error_report', 'Done')
            ->get();
        foreach ($error_msg as $msg)
        {
            $message = $msg->sms_text;

            // return $message;
            $id = $msg->id;
            $address = DB::table('error_message')->where('id', $id)->first()->mobile_number;
            $address = 'tel:' . $address;

            try
            {

              //  $myfile = fopen("report.txt", "a+") or die("Unable to open file!");
              //  $unregFile = fopen("unreg.txt", "a+") or die("Unable to open file!");

                $error_msg = "";

                try
                {
                    date_default_timezone_set("Asia/Dhaka");
                    $current_date = date('Y-m-d H:i:s');
                    $text = $this->split_msg($message);
					$arr = $text[0];
					 $final_address = trim($address, "tel:");
					if($text == 'error' || sizeof($arr)<=1)
					{
						$error_msg.='Message text not correectly formatted';
					}
					
					else
					{
                    
					//file_put_contents('test.txt',json_encode($arr).' '.sizeof($arr));
                    $string = $text[1];
                   
                    $type = $string[0];
                    $date2 = $string[1];
                   
                    $route = $string[2];
                    $sql = route::where('route', 'LIKE', $route . '%')->first();
                    if (!$sql)
                    {
                        $error_msg .= $route . " Not found" . ',';
                    }
				
                    $second_last_index = sizeof($arr) - 2;
                    $last_index = sizeof($arr) - 1;
					
                    $total_value_text = $arr[$second_last_index];
					
					
                    // file_put_contents('test.txt',$second_last_index.' '. $total_value_text);
                    if ($total_value_text != 'TV')
                    {
                        $error_msg .= 'TV not found' . ',';
                    }
					$items = array();
                    for ($i = 0;$i < (sizeof($arr) / 2) - 1;$i++)
                    {
                        $item = $arr[2 * $i];
						array_push($items,trim($item));
                        $qty = $arr[2 * $i + 1];
                        $sql = item::where('item_id', $item)->first();
                        if (!$sql)
                        {
                            $error_msg .= $item . " Not found" . ',';
                        }
						

                    }
					$item_count = array_count_values($items);
					
					foreach($item_count as $key=>$value)
					{
						if($value>1)
						{
							$error_msg .= $key . " contains " .$value. ' times ,';
						}
					}
                    if (strlen($date2) != 4)
                    {
                        $error_msg .= $date2 . " Date format should be 4 digit" . ',';
                    }
                    else
                    {
					  $order = $date2;
                    $order_date = $order[0] . $order[1];
                    $order_month = $order[2] . $order[3];
                    $order_year = '2021';
                    $date = $order_year . "/" . $order_month . "/" . $order_date;
                    $date = strtotime($date);
                    $date = date('Y-m-d', $date);
                    }
					
					$route_with_number = route_with_number::where('mobile_number',$final_address)->where('short_name', 'LIKE', $route . '%')->first();
				if(!$route_with_number)
				{
					 $error_msg .= $final_address . " is not associated with " .$route. ',';
				}
				}
					


                    if ($error_msg)
                    {

                        $error = error_message::where('id', $id)->update(['sl' => $id, 'error_report' => $error_msg, 'sms_text' => $message]);
                       

                     /*   return redirect()
                            ->route('error-message')
                            ->with('error', 'Some error occured');
							*/

                    }
                    else
                    {
                        error_message::where('id', $id)->delete();

                        $download_msg = download_message::create(['mobile_number' => $final_address, 'message_text' => $message, 'msg_date' => $current_date]);

                        $last_id = $download_msg->id;
                        download_message::where('id', $last_id)->update(['sl' => $last_id]);
                        for ($i = 0;$i < (sizeof($arr) / 2) - 1;$i++)
                        {
                            $item = $arr[2 * $i];
                            $qty = $arr[2 * $i + 1];
                            voucher_details::create(['sl' => $last_id, 'type' => $type, 'item' => $item, 'qty' => $qty]);

                        }

                        voucher_head::create(['sl' => $last_id, 'type' => $type, 'msg_date' => $current_date, 'od_date' => $date, 'mobile_number' => $final_address, 'route' => $string[2], 'amount' => $arr[$last_index]]);
	
							/*return redirect()->route('error-message')
                            ->with('success', 'Error Update Successfully');
							*/

                    }

                }
                catch(Exception $e)
                {

                }

            }

            catch(SMSServiceException $e)
            {
                $logger->WriteLog($e->getErrorCode() . " " . $e->getErrorMessage() . "\n");
            }

        }
		return redirect()->back()->with('success','Data Updated Successfully');
    }

    public function sms(Request $request)
    {
		
		file_put_contents('test.txt','hello');
        $server = 'https://developer.bdapps.com/sms/send';
        $appid = "APP_036385";
        $apppassword = "00febb6e06c0c8a30c268f18d69de401";
        $logger = new Logger();

        try
        {

            //$myfile = fopen("report.txt", "a+") or die("Unable to open file!");
            //$unregFile = fopen("unreg.txt", "a+") or die("Unable to open file!");
            // Creating a receiver and intialze it with the incomming data
            $receiver = new SMSReceiver(file_get_contents('php://input'));

            //Creating a sender
            $sender = new SMSSender($server, $appid, $apppassword);

            $message = $receiver->getMessage(); // Get the message sent to the app
            $address = $receiver->getAddress(); // Get the phone no from which the message was sent
            $appid = $receiver->getApplicationId(); // Get the phone no from which the message was sent
            $error_msg = "";

            $a = explode(" ", $message);
            $b = " ";
            for ($i = 1;$i < sizeof($a);$i++)
            {
                $b = $b . ' ' . $a[$i];
            }
            $message = $b;
            $message = trim($message);

            try
            {
                date_default_timezone_set("Asia/Dhaka");
                $current_date = date('Y-m-d H:i:s');
                $text = $this->split_msg($message);
				
				 $final_address = trim($address, "tel:");
				
				if($text == "error")
					{
						$error_msg.='Message text not correectly formatted';
						// file_put_contents('address.txt',$final_address." ".$text);
					}
					else{
                $arr = $text[0];
                $string = $text[1];
               
                $type = $string[0];
				$date2 = $string[1];
                $route = $string[2];
                $sql = route::where('route', 'LIKE', $route . '%')->first();
                if (!$sql)
                {
                    $error_msg .= $route . " Not found" . ',';
                }

                $second_last_index = sizeof($arr) - 2;

                $last_index = sizeof($arr) - 1;
                $total_value_text = $arr[$second_last_index];
                // file_put_contents('test.txt',$second_last_index.' '. $total_value_text);
                if ($total_value_text != 'TV')
                {
                    $error_msg .= 'TV not found' . ',';
                }

                $items = array();
                    for ($i = 0;$i < (sizeof($arr) / 2) - 1;$i++)
                    {
                        $item = $arr[2 * $i];
						array_push($items,trim($item));
                        $qty = $arr[2 * $i + 1];
                        $sql = item::where('item_id', $item)->first();
                        if (!$sql)
                        {
                            $error_msg .= $item . " Not found" . ',';
                        }
						

                    }
					$item_count = array_count_values($items);
					foreach($item_count as $key=>$value)
					{
						if($value>1)
						{
							$error_msg .= $key . " contains " .$value. ' times ,';
						}
					}
					//file_put_contents('test5.txt',json_encode($item_count));
					/*
					for($j=0;$j<sizeof($items);$j++)
					{
						$count = $item_count[$items[$j]];
						if($count>1)
						{
							$error_msg .= $items[$j] . "contains" .$count. 'times ,';
						}
						
					}
					*/
                if (strlen($date2) != 4)
                {
                    $error_msg .= $date2 . " Date format should be 4 digit" . ',';
                }
                else
                {
				
                $order = $date2;
                $order_date = $order[0] . $order[1];
                $order_month = $order[2] . $order[3];
                $order_year = '2021';
                $date = $order_year . "/" . $order_month . "/" . $order_date;
                $date = strtotime($date);
                $date = date('Y-m-d', $date);
                }
				$route_with_number = route_with_number::where('mobile_number',$final_address)->where('short_name', 'LIKE', $route . '%')->first();
				if(!$route_with_number)
				{
					 $error_msg .= $final_address . " is not associated with " .$route. ',';
				}
				
					}
                if ($error_msg)
                {

                    // file_put_contents('test2.txt',$error_msg);
                    $error = error_message::create(['msg_date' => $current_date, 'mobile_number' => $final_address, 'sms_text' => $message, 'error_report' => $error_msg, 'status' => 0]);
                 
                    error_message::where('id', $error->id)
                        ->update(['sl' => $error->id]);
                }
                else
                {

                    $download_msg = download_message::create(['mobile_number' => $final_address, 'message_text' => $message, 'msg_date' => $current_date]);
                    
                    $last_id = $download_msg->id;
                    download_message::where('id', $last_id)->update(['sl' => $last_id]);
                    for ($i = 0;$i < (sizeof($arr) / 2) - 1;$i++)
                    {
                        $item = $arr[2 * $i];
                        $qty = $arr[2 * $i + 1];
                        voucher_details::create(['sl' => $last_id, 'type' => $type, 'item' => $item, 'qty' => $qty]);
                       
                        

                        
                    }

                    voucher_head::create(['sl' => $last_id, 'type' => $type, 'msg_date' => $current_date, 'od_date' => $date, 'mobile_number' => $final_address, 'route' => $string[2], 'amount' => $arr[$last_index]]);
                  
                }

              
                
            }
            catch(Exception $e)
            {

            }

            //	Send a SMS to a particular user
            $response = $sender->sms('Thanks for your response', $address);
			response_log::create(['timeStamp'=>$response->timeStamp,'address'=>$response->address,'messageId'=>$response->messageId,'statusDetail'=>$response->statusDetail,'statusCode'=>$response->statusCode]);
			//file_put_contents('test.txt',$response[0]->statusCode);
			

        }

        catch(SMSServiceException $e)
        {
            $logger->WriteLog($e->getErrorCode() . " " . $e->getErrorMessage() . "\n");
        }

    }

}

