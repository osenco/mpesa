<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Message;
use App\Models\Payment;
use App\Notifications\SendMessage;
use Illuminate\Http\Request;
use Osen\Mpesa\C2B;
use Osen\Mpesa\STK;

class MpesaController extends Controller
{
    public function __construct()
    {
        STK::init(
            array(
                'env'              => 'sandbox',
                'type'             => 4,
                'headoffice'       => 174379,
                'shortcode'        => 174379,
                'key'              => 'l6jE7kgV4lCtNH4aveMueR9QdGkbutfR',
                'secret'           => '5slRuAafb4Gk7Ogo',
                'passkey'          => 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919',
                'validation_url'   => url('api/lipwa/validate'),
                'confirmation_url' => url('api/lipwa/confirm'),
                'callback_url'     => url('api/lipwa/reconcile'),
                'results_url'      => url('api/lipwa/results'),
                'timeout_url'      => url('api/lipwa/timeout'),
            )
        );

        C2B::init(
            array(
                'env'              => 'sandbox',
                'type'             => 4,
                'headoffice'       => 174379,
                'shortcode'        => 174379,
                'key'              => 'l6jE7kgV4lCtNH4aveMueR9QdGkbutfR',
                'secret'           => '5slRuAafb4Gk7Ogo',
                'passkey'          => 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919',
                'username'         => 'username',
                'validation_url'   => url('api/lipwa/validate'),
                'confirmation_url' => url('api/lipwa/confirm'),
                'callback_url'     => url('api/lipwa/reconcile'),
                'timeout_url'      => url('api/lipwa/timeout'),
                'response_url'     => url('api/lipwa/response'),
            )
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Payment::query();

        if (!empty($request->query())) {
            foreach ($request->query() as $key => $value) {
                if ($key == 's') {
                    $query->whereHas('customer', function ($q) use ($request) {
                        $q->where('first_name', 'like', "%{$request->query('s')}%")->orWhere('last_name', 'like', "%{$request->query('s')}%")->orWhere('phone', 'like', "%{$request->query('s')}%");
                    });
                } elseif ($key == 'page') {
                    continue;
                } else if ($key == 'date') {
                    $query->whereDate('created_at', '>=', $value);
                } else {
                    $query->where($key, $value);
                }
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $response = array(
            "trans_type"  => $request->TransactionType ?? 'C2B',
            "receipt"     => $request->TransID,
            "trans_time"  => $request->TransTime,
            "amount"      => $request->TransAmount,
            "shortcode"   => $request->BusinessShortCode,
            "reference"   => $request->BillRefNumber ?? strtoupper(random_bytes(8)),
            "invoice"     => $request->InvoiceNumber,
            "balance"     => $request->OrgAccountBalance,
            "transaction" => $request->ThirdPartyTransID,
            "phone"       => $request->MSISDN,
            "first_name"  => ucfirst(strtolower($request->FirstName)),
            "middle_name" => ucfirst(strtolower($request->MiddleName)),
            "last_name"   => ucfirst(strtolower($request->LastName)),
        );

        $data = array_merge(
            $request->all(),
            $response
        );

        try {
            $data['meta']        = array(
                "type"        => $data["trans_type"],
                "time"        => $data["trans_time"],
                "shortcode"   => $data["shortcode"],
                "invoice"     => $data["invoice"],
                "balance"     => $data["balance"],
                "transaction" => $data["transaction"],
            );

                $payment = Payment::firstOrCreate(['receipt' => $data['receipt']], $data);

                $paid             = $payment->paid += $data['amount'];
                $payment->balance = $paid - $data['amount'];
                $payment->status  = 'completed';
                $payment->save();

                if ($payment) {
                    $return = array(
                        'error'   => false,
                        'message' => 'Successfully created payment ',
                        'data'    => compact('customer', 'payment'),
                    );
                } else {
                    $return = array(
                        'error'   => true,
                        'message' => 'Failed to create payment ',
                    );
                }
        } catch (\Exception $e) {
            $return = array(
                'error'   => true,
                'message' => $e->getMessage(),
            );
        }

        return $return['error'] ? C2B::confirm(function ($response) {
            return false;
        }) : C2B::confirm();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function show($payment)
    {
        return Payment::with(['customer'])->find($payment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Payment $payment)
    {
        $data = $request->all();
        try {
            $update = $payment->update($data);
            $return = $update
                ? array(
                    'error'   => false,
                    'message' => 'Successfully updated payment ',
                    'data'    => $payment,
                )
                : array(
                    'error'   => true,
                    'message' => 'Failed to update payment ',
                );
        } catch (\Exception $e) {
            $return = array(
                'error'   => true,
                'message' => $e->getMessage(),
            );
        }

        return $return;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Payment $payment)
    {
        try {
            $delete = $payment->delete();
            $return = $delete
                ? array(
                    'error'   => false,
                    'message' => 'Successfully deleted payment ',
                )
                : array(
                    'error'   => true,
                    'message' => 'Failed to delete payment ',
                );
        } catch (\Exception $e) {
            $return = array(
                'error'   => true,
                'message' => $e->getMessage(),
            );
        }

        return $return;
    }

    public function request_payment(Request $request)
    {
        try {
            return STK::send($request->phone, $request->amount, $request->reference, 'Request Payment', 'Payment', function ($response) use ($request) {
                $shortcode = STK::$config->shortcode;
                $data      = $request->all();

                if (isset($response['MerchantRequestID'])) {
                    $data['request']   = $response['MerchantRequestID'];
                    $data['reference'] = strtoupper(random_bytes(8));

                    $data['meta'] = array(
                        "type"        => $data["trans_type"] ?? 'stk',
                        "time"        => $data["trans_time"] ?? time(),
                        "shortcode"   => $data["shortcode"] ?? $shortcode,
                        "invoice"     => $data["invoice"] ?? $request->reference,
                        "balance"     => $data["balance"] ?? 0,
                        "transaction" => $data["transaction"] ?? time(),
                    );

                    $payment = Payment::firstOrCreate(['request' => $data['request']], $data);

                    return array(
                        'request' => $response,
                        'payment' => $payment
                    );
                } else {
                    return array(
                        'errorCode' => $response['errorCode'],
                        'errorMessage' => $response['errorMessage']
                    );
                }
            });
        } catch (\Throwable $th) {
            return ['errorCode' => 1, 'errorMessage' => $th->getMessage()];
        }
    }

    public function validation(Request $request)
    {
        return C2B::validate();
    }

    public function register(Request $request)
    {
        return C2B::register();
    }

    public function reconcile(Request $request)
    {
        return STK::reconcile(function ($response) {
            $shortcode         = STK::$config->shortcode;
            $response          = $response["Body"];
            $resultCode        = $response["stkCallback"]["ResultCode"];
            $resultDesc        = $response["stkCallback"]["ResultDesc"];
            $merchantRequestID = $response["stkCallback"]["MerchantRequestID"];

            $payment = Payment::where('request', $merchantRequestID)->orderBy('created_at', 'desc')->first();

            if (isset($response["stkCallback"]["CallbackMetadata"])) {
                $CallbackMetadata = $response["stkCallback"]["CallbackMetadata"]["Item"];

                $amount             = $CallbackMetadata[0]["Value"] ?? 0;
                $mpesaReceiptNumber = $CallbackMetadata[1]["Value"] ?? '';
                $balance            = $CallbackMetadata[2]["Value"] ?? 0;
                $transactionDate    = $CallbackMetadata[3]["Value"] ?? time();
                $phone              = $CallbackMetadata[4]["Value"] ?? '';

                $payment->update(
                    [
                        'status'  => "completed",
                        'amount'  => $amount,
                        'phone'   => $phone,
                        'receipt' => $mpesaReceiptNumber,
                        'meta'    => array(
                            "type"      => 'stk',
                            "time"      => $transactionDate,
                            "shortcode" => $shortcode,
                            "invoice"   => time(),
                            "balance"   => $balance,
                        ),
                    ]
                );

                return true;
            } else {
                $payment->update([
                    'meta' => array(
                        'code' => $resultCode,
                        'error' => $resultDesc
                    )
                ]);
                return false;
            }

            return true;
        });
    }
}