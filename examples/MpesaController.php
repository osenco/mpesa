<?php

namespace App\Http\Controllers;

use App\User;
use App\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

use Osen\Mpesa\STK;

class MpesaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        STK::init(
            array(
                'env'               => 'sandbox',
                'type'              => 4,
                'shortcode'         => '173527',
                'honumber'          => '173527',
                'key'               => 'Your Consumer Key',
                'secret'            => 'Your Consumer Secret',
                'username'          => '',
                'passkey'           => 'Your Online Passkey',
                'validation_url'    => url('mpesa/validate'),
                'confirmation_url'  => url('mpesa/confirm'),
                'callback_url'      => url('mpesa/reconcile'),
                'timeout_url'       => url('mpesa/timeout'),
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function pay(Request $request)
    {
        $data = $request->all();

        try {
            $res = STK($request->phone, $request->amount, $request->reference);
            if($res){
                $data['ref']            = $res['transactionID'];
                $data['paid_status']    = 'Pending';
                $data['session']        = 1;
                $payment                = Payment::create($data);
        
                if($payment){
                    toast(ucwords(__('details saved successfully')), 'success', 'bottom-right');
                    
                    try {
                        $AT       = new AfricasTalking(Setting::sms('api_username', 'schooliq'), Setting::sms('api_key', '97ca15305d52f5113374ea80ae5e4718ebca840099c9d6b7b5dc63b3d0fc1634'));
                        $sms      = $AT->sms();
                        $sms->send([
                            'to'      => $payment->user->phone,
                            'message' => 'Your payment of '.Setting::general('currency', 'KEN').' '.$payment->amount.'has been received'
                        ]);
                    } catch (\Throwable $th) {
                        toast(ucwords(__('failed to send sms')), 'error', 'bottom-right');
                    }
                } else {
                    toast(ucwords(__('failed to create record')), 'error', 'bottom-right');
                }

                return Redirect::back();
            }
        } catch (\Throwable $th) {
            toast(ucwords(__('failed to create record')), 'error', 'bottom-right');
            return Redirect::back();
        }
    }

    public function reconcile(Request $request, $method = 'mpesa')
    {
        if ($method == 'mpesa') {
            $response = STK::reconcile($request->all());

            $transaction = $response['transID'];

            $payment = Payment::whereRef($transaction)->first();

            $payment->mpesa = $response['reference'];

            if ($payment->update()) {
                return array('status' => 0);
            }
        }
    }

    public function validation()
    {
        return STK::validate();
    }

    public function confirmation()
    {
        return STK::confirm();
    }
}
