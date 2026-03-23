<?php

namespace App\Http\Controllers;

use App\SmsLog;
use Illuminate\View\View;
use Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    /**
     * Send SMS from the dashboard.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendSms(Request $request)
    {
        $rules = [
            'mobile' => 'required|numeric|digits_between:8,10',
            'subject' => 'required|string|max:10',
            'message' => 'required|max:1000',
        ];
        $inputs = $request->all();
        $validator = Validator::make($inputs, $rules);
        if($validator->fails()){
            return redirect()->back()->withInput()->withErrors($validator);
        } else {
            $this->sendingSms($request->subject, $request->mobile, $request->message);
            $this->smsLog([
                'user_id' => Session::get('user')['id'],
                'mobile_no' => $request->mobile,
                'sender_id' => $request->subject,
                'message' => $request->message,
                'sms_count' => ceil(strlen($request->message) / 140),
            ]);
            return redirect()->back()->with('message', "SMS Sent.");
        }
    }

    /**
     * Show Sent SMS Details.
     *
     * @return View
     */
    public function getTrackingDetails(Request $request)
    {
        $tracking = SmsLog::paginate(50);
        return view('admin.tracking', compact('tracking'));
    }
}
