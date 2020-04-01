<?php

namespace App\Http\Controllers;

use App\Payment;
use Illuminate\Http\Request;
use Osen\Mpesa\B2C;

class MpesaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        B2C::init(
            array(
                "env"              => "sandbox",
                "shortcode"        => "173527",
                "headoffice"       => "173527",
                "key"              => "Your Consumer Key",
                "secret"           => "Your Consumer Secret",
                "username"         => "Your Org Username",
                "password"         => "Your Org Password",
                "validation_url"   => url("disburse/validate"),
                "confirmation_url" => url("disburse/confirm"),
                "callback_url"     => url("disburse/reconcile"),
                "results_url"      => url("disburse/timeout"),
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
            $request = B2C::send($request->phone, $request->amount, $request->command, $request->remarks, $request->occassion, function ($response) {
                $ConversationID           = $response["ConversationID"];
                $OriginatorConversationID = $response["OriginatorConversationID"];
                $ResponseCode             = $response["ResponseCode"];
                $ResponseDescription      = $response["ResponseDescription"];

                // TIP: Save $OriginatorConversationID in the database, and use it as a key for update
                $data["request_id"] = $OriginatorConversationID;
                $payment            = Payment::create($data);

                return true;
            });

            return back();
        } catch (\Exception $e) {
            return array("msg" => $e->getMessage);
        }
    }

    public function confirmation()
    {
        return B2C::reconcile(function ($response) {
            $Result                              = $response["Result"];
            $ResultType                          = $Result["ResultType"];
            $ResultCode                          = $Result["ResultCode"];
            $ResultDesc                          = $Result["ResultDesc"];
            $OriginatorConversationID            = $Result["OriginatorConversationID"];
            $ConversationID                      = $Result["ConversationID"];
            $TransactionID                       = $Result["TransactionID"];
            $ResultParameters                    = $Result["ResultParameters"];
            $ResultParameter                     = $Result["ResultParameters"]["ResultParameter"];
            $TransactionReceipt                  = $ResultParameter[0]["Value"];
            $TransactionAmount                   = $ResultParameter[1]["Value"];
            $B2CWorkingAccountAvailableFunds     = $ResultParameter[2]["Value"];
            $B2CUtilityAccountAvailableFunds     = $ResultParameter[3]["Value"];
            $TransactionCompletedDateTime        = $ResultParameter[4]["Value"];
            $ReceiverPartyPublicName             = $ResultParameter[5]["Value"];
            $B2CChargesPaidAccountAvailableFunds = $ResultParameter[6]["Value"];
            $B2CRecipientIsRegisteredCustomer    = $ResultParameter[7]["Value"];
            $ReferenceData                       = $Result["ReferenceData"];
            $ReferenceItem                       = $ReferenceData["ReferenceItem"];
            $QueueTimeoutURL                     = $ReferenceItem[0]["Value"];

            // Update Database record with $TransactionID as the MPESA receipt number where $OriginatorConversationID
            $payment          = Payment::where("request_id", $OriginatorConversationID)->first();
            $payment->receipt = $TransactionID;
            $payment->save();

            return true;
        });
    }

    public function results()
    {
        return B2C::results();
    }
}