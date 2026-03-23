<?php

    namespace App\Http\Controllers;

    use Hash;
    use Session;
    use App\User;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Cookie;

    class LoginController extends Controller
    {
        public function index(Request $request, $flush = false)
        {
            if($flush == true) {
                Session::forget('mobile');
                return redirect()->route('admin.login');
            }
            if (Session::has('login_status') && Session::get('login_status') === true) :
                return redirect()->route('home');
            else :
                if (!Session::has('mobile')) :
                    return view('admin.login');
                else :
                    return view('admin.otp');
                endif;
            endif;
        }

        public function backToLogin() {
            Session::forget('mobile');
            return redirect()->route('admin.login');
        }

        public function login(Request $request, $flush = false)
        {
//            dd(Session::get('mobile'));
            if (!Session::has('mobile')) :
                if(isset($request->mobile)) :
                    $mobile = $request->mobile;
                    Session::put('mobile', $mobile);
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'message' => 'OTP Sent Successfully.',
                    ]);
                else :
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => 'Mobile Number required!',
                    ]);
                endif;
            else :
                if(isset($request->code1) && isset($request->code2) && isset($request->code3) && isset($request->code2)) :
                    $code = $request->code1 . "" . $request->code2 . "" . $request->code3 . "" . $request->code4;
                    $mobile = Session::get('mobile');
                    $user = config('settings.users')[$mobile];
//                    dd($mobile, $user);
                    $loginStatus = false;
                    if ($code == $user['password']) {
                        $loginStatus = true;
                        Session::put('login_status', true);
                        Session::put('user', $user);
                    } else {
                        Session::pull('login_status', false);
                    }
                    if ($loginStatus === true)
                        return response()->json(['code' => 200, 'status' => true, 'message' => 'Login Successfully.']);
                    else
                        Session::flush();
                        return response()->json(['code' => 404, 'status' => false, 'message' => 'OTP not Valid!']);
                else :
                    return response()->json(['code' => 404, 'status' => false, 'message' => 'OTP required!']);
                endif;
            endif;
        }

        public function logout(Request $request)
        {
            Session::flush();
            return redirect()->route('admin.login');
        }
    }
