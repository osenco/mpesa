<?php

namespace App\Http\Controllers;

use App\Payment;
use Illuminate\Http\Request;
use Osen\Mpesa\STK;
use Osen\Mpesa\C2B;

class MpesaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        C2B::init(
            array(
                "env"              => "sandbox",
                "type"             => 4,
                "shortcode"        => "174379",
                "key"              => "Your Consumer Key",
                "secret"           => "Your Consumer Secret",
                "validation_url"   => url("lipwa/validate"),
                "confirmation_url" => url("lipwa/confirm"),
                "timeout_url"      => url("lipwa/timeout"),
            )
        );

        STK::init(
            array(
                "env"            => "sandbox",
                "type"           => 4,
                "shortcode"      => "173527",
                "headoffice"     => "173527",
                "key"            => "Your Consumer Key",
                "secret"         => "Your Consumer Secret",
                "passkey"        => "Your Online Passkey",
                "validation_url" => url("lipwa/validate"),
                "callback_url"   => url("lipwa/reconcile"),
                "timeout_url"    => url("lipwa/timeout"),
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

            if (!isset($res["errorCode"])) {
                $data["ref"] = $res->MerchantRequestID;
                $payment     = Payment::create($data);

                if ($payment) {
                    return array("msg" => "saved");
                } else {
                    return array("msg" => "failed");
                }

                return back();
            }
        } catch (\Exception $e) {
            return array("msg" => $e->getMessage());
            return back();
        }
    }

    public function reconcile(Request $request, $method = "mpesa")
    {
        if ($method == "mpesa") {
            $response = STK::reconcile(function ($data) {
                $payment         = Payment::where("mpesa", $data["MerchantRequestID"])->first();
                $payment->status = "Paid";

                return $payment->save();
            });
        }
    }

    public function validation()
    {
        return STK::validate();
    }

    public function confirmation()
    {
        return C2B::confirm(function ($response) {
            // Process $response
            $TransactionType   = $response["TransactionType"];
            $TransID           = $response["TransID"];
            $TransTime         = $response["TransTime"];
            $TransAmount       = $response["TransAmount"];
            $BusinessShortCode = $response["BusinessShortCode"];
            $BillRefNumber     = $response["BillRefNumber"];
            $InvoiceNumber     = $response["InvoiceNumber"];
            $OrgAccountBalance = $response["OrgAccountBalance"];
            $ThirdPartyTransID = $response["ThirdPartyTransID"];
            $MSISDN            = $response["MSISDN"];
            $FirstName         = $response["FirstName"];
            $MiddleName        = $response["MiddleName"];
            $LastName          = $response["LastName"];

            return true;
        });
    }

    public function results()
    {
        return STK::results();
    }

    public function timeout()
    {
        return STK::timeout();
    }
}