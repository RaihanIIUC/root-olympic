<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\item;
use App\Models\route;
use App\Models\error_message;
use App\Models\download_message;
use App\Models\route_with_number;
use App\Models\response_log;
use App\Models\voucher_details;
use Session;
use DB;
use DataTables;

class AdminController extends Controller
{
    //
	 //date_default_timezone_set("Asia/Dhaka");
       protected $cur_date;//= date("Y-m-d");
	   protected $prev_date;// = date('Y-m-d', strtotime('-7 days'));
	   
	     public function __construct()
  {
	  date_default_timezone_set("Asia/Dhaka");
    $this->cur_date = date('Y-m-d', strtotime('+1 days'));
	$this->prev_date = date('Y-m-d', strtotime('-7 days'));
  }

		
 function getDatesFromRange($start, $end){
    $dates = array($start);
    while(end($dates) < $end){
        $dates[] = date('Y-m-d', strtotime(end($dates).' +1 day'));
    }
    return $dates;
}
    public function show_report(Request $request)
    {
         $datas = DB::table('salesman')->get();
         $from_date = date("Y-m-d", strtotime($request->from_date));
         $content = array();
         $to_date = date("Y-m-d", strtotime($request->to_date."+1 days"));
         $date = $this->getDatesFromRange($from_date,$to_date);
         for($i=0;$i<sizeof($date);$i++)
         {
             foreach ($datas as $data)
             {
                  $error_msg = error_message::where('created_at','LIKE',$date[$i]."%")->where('mobile_number','88'.$data->mobile)->first();
                $download_msg = download_message::where('created_at','LIKE',$date[$i]."%")->where('mobile_number','88'.$data->mobile)->first();
                if($error_msg)
                {
                    array_push($content,['name'=>$data->name,'mobile'=>$data->mobile,'status'=>'true','date'=>$date[$i]]);
                }
                 else if($download_msg)
                {
                    array_push($content,['name'=>$data->name,'mobile'=>$data->mobile,'status'=>'true','date'=>$date[$i]]);
                }
                else
                {
                     array_push($content,['name'=>$data->name,'mobile'=>$data->mobile,'status'=>'false','date'=>$date[$i]]);
                }
             }
             
         }
        
         
        $content = json_decode(json_encode($content));
        $j=1;
      foreach($content as $d)
      {
          $d->serial = $j++;
      }
       return view('admin.report',['contents'=>$content]);
    }
    
    public function login_view()
    {
        if(auth()->check())
        {
             return redirect()->route('admin');
        }
        else
        {
            return view('login');
        }
    }
      public function login(Request  $request)
    {
        
        $credentials = array(
            'email' => $request->email,
            'password'=>$request->password
            );
            if (auth()->attempt($credentials)) {
                
                return redirect()->route('admin');

            }
            else
            {
                return redirect()->back()->with('error','Email and password credential not match');
            }
    }
    
    public function logout()
    {

        auth()->logout();
        return redirect()->to('/');

    }
	
	public function download_message2(Request $request)
	{
		if ($request->ajax()) {
            $data = download_message::whereBetween('created_at',[$this->prev_date,$this->cur_date])->orderBy('id', 'DESC')->get();
            $i=1;
                foreach($data as $datas)
                {
					$date = explode('.',$datas->created_at);
						$date = $date[0];
					$date =  date("d/m/Y g:i:s A", strtotime($date));
                   
					$datas['date'] = $date;

                   

                }

            return Datatables::of($data)
                    ->addIndexColumn()
                  

                 
                   
                    ->make(true);
        }
		
		return view('admin.download_message_report');

		
	}

   
   public function download_message()
   {
       $data = DB::table('download_message')->whereBetween('created_at',[$this->prev_date,$this->cur_date])->orderBy('id', 'DESC')->get();
       $i=1;
      foreach($data as $d)
      {
          $d->serial = $i++;
      }
       return view('admin.download_message_report',['contents'=>$data]);
   }
   public function add_item(Request $request)
   {
        item::create($request->all());
         return redirect()->route('item_list')->with('success','Item Created Successfully');
   }
   
   public function edit_item($id)
   {
       $data = item::where('id',$id)->first();
       return view('admin.edit_item',['data'=>$data]);
   }
   public function update_item(Request $request)
   {
       item::where('id',$request->id)->update(['name'=>$request->name,'item_id'=>$request->item_id]);
       return redirect()->route('item_list')->with('success','Item Updated Successfully');
   }
   
   public function delete_item($id)
   {
       item::where('id',$id)->delete();
   }
   
    public function delete_route_with_number($id)
   {
       route_with_number::where('id',$id)->delete();
   }
   
    public function delete_route($id)
   {
       route::where('id',$id)->delete();
   }
   
   public function add_route(Request $request)
   {
       route::create($request->all());
       return redirect()->route('route')->with('success','Route Created Successfully');
   }
    public function add_route_with_number(Request $request)
   {
	   $request->validate([
        'mobile_number' => 'required|min:13|max:13',
        
    ]);
       route_with_number::create($request->all());
	   
       return redirect()->route('route_with_number')->with('success','Route Created Successfully');
   }
   public function update_route(Request $request)
   {
       route::where('id',$request->id)->update(['route'=>$request->route,'union_name'=>$request->union_name,'dist'=>$request->dist,'thana'=>$request->thana,'division'=>$request->division]);
        return redirect()->route('route')->with('success','Route Updated Successfully');
   }
   
   public function update_route_with_number(Request $request)
   {
       route_with_number::where('id',$request->id)->update(['mobile_number'=>$request->mobile_number,'route_name'=>$request->route_name,'short_name'=>$request->short_name]);
        return redirect()->route('route_with_number')->with('success','Route Updated Successfully');
   }
   public function edit_route($id)
   {
        $data = route::where('id',$id)->first();
       return view('admin.edit_route',['data'=>$data]);
   }
   
    public function edit_route_with_number($id)
   {
        $data = route_with_number::where('id',$id)->first();
       return view('admin.edit_route_with_number',['data'=>$data]);
   }
   
   public function delete_error_data($id)
   { 
       //$data = DB::table('error_message')->where('id',$id)->first();
       //download_message::create(['mobile_number'=>$data->mobile_number,'message_text'=>$data->sms_text ,'msg_date'=>$data->msg_date]);
       
        DB::table('error_message')->where('id',$id)->update(['status'=>1]);
        
   }
    public function error_message()
   {
       $data = DB::table('error_message')->where('status',0)->orWhere('status','=',0)->whereBetween('created_at',[$this->prev_date,$this->cur_date])->orderBy('id', 'DESC')->get();
       $i=1;
      foreach($data as $d)
      {
          $d->serial = $i++;
      }
       return view('admin.error_message_report',['contents'=>$data]);
   }
   
    public function item_list()
   {
       $data = DB::table('item')->get();
       $i=1;
      foreach($data as $d)
      {
          $d->serial = $i++;
      }
       return view('admin.item2',['contents'=>$data]);
   }
   
    public function route()
   {
       $data = DB::table('route_list')->get();
       $i=1;
      foreach($data as $d)
      {
          $d->serial = $i++;
      }
       return view('admin.route',['contents'=>$data]);
   }
   
   public function edit_error_msg($id)
   {
       $data = DB::table('error_message')->where('id',$id)->first();
        return view('admin.edit_error_msg',['data'=>$data]);
   }
   
   public function response_log(Request $request)
   {
	   
	   if ($request->ajax()) {
            $data = response_log::whereBetween('created_at',[$this->prev_date,$this->cur_date])->orderBy('created_at','DESC')->get();
            $i=1;
                foreach($data as $datas)
                {

                    $datas['sl_no'] = $i++;
					
					$datas['date'] =  date("d-m-Y h:i:s", strtotime($datas->created_at));
                   

                }

            return Datatables::of($data)
                    ->addIndexColumn()
                   
                   

                 
                   
                    ->make(true);
        }
		 return view('admin.response_log');
   }
   
   public function route_with_number(Request $request)
   {
	     if ($request->ajax()) {
            $data = route_with_number::get();
            $i=1;
                foreach($data as $datas)
                {

                    $datas['sl_no'] = $i++;

                   

                }

            return Datatables::of($data)
                    ->addIndexColumn()
                   ->addColumn('action', function($data){

                        $button = ' <a href="edit_route_with_number/'.$data->id.'"><img width="20px" src="../assets/image/edit.png" alt="not found"></a>';
                        $button .= '&nbsp;&nbsp;';
                        $button .= '<a href="javascript:;"  onclick="delete_route_with_number_data('.$data->id.')"><img style="width:20px" src="../assets/image/delete.png" alt=""></a>';
                        return $button;
                 })
                   

                 
                   ->rawColumns(['action'])
                    ->make(true);
        }

        return view('admin.route_with_number');
   }
   
    public function voucher_details()
   {
       $data = DB::table('voucher_details')->whereBetween('created_at',[$this->prev_date,$this->cur_date])->orderBy('id', 'DESC')->get();
       $i=1;
      foreach($data as $d)
      {
          $item = DB::table('item')->where('item_id',$d->item)->first();
          if($item)
          {
              $item_name = $item->name;
          }
          else
          {
              $item_name= 'Not Found';
          }
          $d->item_name = $item_name;
          $d->serial = $i++;
      }
       return view('admin.voucher_details',['contents'=>$data]);
   }
   
    public function voucher_head()
   {
       $data = DB::table('voucher_head')->whereBetween('created_at',[$this->prev_date,$this->cur_date])->orderBy('id', 'DESC')->get();
       $i=1;
      foreach($data as $d)
      {
          $d->serial = $i++;
      }
       return view('admin.voucher_head',['contents'=>$data]);
   }
   
   
   public function resend_sms(Request $request)
   {
            $message = $request->error_msg;
           // return $message;
            $id = $request->id;
            $address = DB::table('error_message')->where('id',$id)->first()->mobile_number;
            $address = 'tel:'.$address;
            


            $curl = curl_init();
            
            curl_setopt_array($curl, array(
              CURLOPT_URL => "http://bdappsandroid.com/olympic/sms_api.php",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"msg\"\r\n\r\n".$message."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"address\"\r\n\r\n".$address."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"id\"\r\n\r\n".$id."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
              CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
                "postman-token: 611d7661-5db0-5a02-9006-3897ce2c9c66"
              ),
            ));
            
            $response = curl_exec($curl);
            $response = trim($response);
            
            if($response == "ok")
            {
                DB::table('error_message')->where('id',$id)->update(['status'=>1]);
               return redirect()->route('error-message')->with('success','Error Update Successfully');
            }
             if($response == "not_ok")
            {
                
                
                return redirect()->route('error-message')->with('error','Some error occured');
            }
           
                        
    }
	
	public function test()
	{
		//$sql = "Select *  from voucher_details
//WHERE CONVERT(VARCHAR(25), created_at, 126) LIKE '2021-09-14%'";
		//$id = DB::Select(DB::raw($sql));
		//$id = voucher_details::where('created_at','LIKE','2021-03-15'.'%')->get();
		//return $id[0];
		//return json_encode($id[0]);
		//file_put_contents('test.txt',json_encode($id));
		
		$a = [
		'EPM',
'PC',
'PGS',
'CP',
'CHC',
'SCC',
'SCL',
'RCM',
'NHS',
'CCM',
'PFC',
'CCM', 
'OWM',
'CPA',
'JTM',
'JPA',
'LS'];
dd(array_count_values($a));
	}

   
}
