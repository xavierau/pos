<?php

namespace App\Http\Controllers;

use App\Mail\CustomEmail;
use App\Mail\Payment_Purchase;
use App\Models\Account;
use App\Models\EmailMessage;
use App\Models\PaymentPurchase;
use App\Models\Provider;
use App\Models\Purchase;
use App\Models\Role;
use App\Models\Setting;
use App\Models\sms_gateway;
use App\Models\SMSMessage;
use App\Services\Payments\PaymentPurchaseStatusCalculator;
use App\utils\Helper;
use ArPHP\I18N\Arabic;
use GuzzleHttp\Client as Client_guzzle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Infobip\Api\SendSmsApi;
use Infobip\Configuration;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;
use Twilio\Rest\Client as Client_Twilio;

class PaymentPurchasesController extends BaseController
{

    //------------- Get All Payments Purchases --------------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Reports_payments_Purchases', PaymentPurchase::class);

        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = $request->get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $helpers = new Helper();
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        // Filter fields With Params to retriever
        $param = array(0 => 'like', 1 => '=', 2 => 'like');
        $columns = array(0 => 'ref', 1 => 'purchase_id', 2 => 'type');
        $data = array();

        // Check If User Has Permission View  All Records
        $Payments = PaymentPurchase::with('purchase.provider', 'account')
            ->whereBetween('date', array($request->from, $request->to))
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            })
            // Multiple Filter
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('provider_id'), function ($query) use ($request) {
                    return $query->whereHas('purchase.provider', function ($q) use ($request) {
                        $q->where('id', '=', $request->provider_id);
                    });
                });
            });
        $Filtred = $helpers->filter($Payments, $columns, $param, $request)
            // Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('ref', 'LIKE', "%{$request->search}%")
                        ->orWhere('date', 'LIKE', "%{$request->search}%")
                        ->orWhere('type', 'LIKE', "%{$request->search}%")
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('purchase', function ($q) use ($request) {
                                $q->where('ref', 'LIKE', "%{$request->search}%");
                            });
                        })
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('purchase.provider', function ($q) use ($request) {
                                $q->where('name', 'LIKE', "%{$request->search}%");
                            });
                        });
                });
            });

        $totalRows = $Filtred->count();
        if ($perPage == "-1") {
            $perPage = $totalRows;
        }
        $Payments = $Filtred->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        foreach ($Payments as $Payment) {

            $item['date'] = $Payment->date;
            $item['ref'] = $Payment->ref;
            $item['ref_purchase'] = $Payment['purchase']->ref;
            $item['provider_name'] = $Payment['purchase']['provider']->name;
            $item['type'] = $Payment->type;
            $item['amount'] = $Payment->amount;
            $item['account_name'] = $Payment['account'] ? $Payment['account']->account_name : '---';
            $data[] = $item;
        }

        $suppliers = Provider::where('deleted_at', '=', null)->get(['id', 'name']);
        $purchases = Purchase::get(['ref', 'id']);

        return response()->json([
            'totalRows' => $totalRows,
            'payments' => $data,
            'purchases' => $purchases,
            'suppliers' => $suppliers,
        ]);

    }

    //----------- Store New Payment Purchase --------------\\

    public function store(Request $request, PaymentPurchaseStatusCalculator $paymentPurchaseStatusCalculator)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'purchase_id' => ['required', Rule::exists('purchases', 'id')],
            'account_id' => ['nullable', Rule::exists('accounts', 'id')],
            'type' => ['required', 'string'],
            'change' => ['required', 'numeric'],
            'notes' => ['nullable'],
        ]);

        $this->authorizeForUser($request->user('api'), 'create', PaymentPurchase::class);

        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        $purchase = Purchase::findOrFail($request->get('purchase_id'));

        // Check If User Has Permission view All Records
        if (!$view_records) {
            $this->authorizeForUser($request->user('api'), 'check_record', $purchase);
        }

        if ($request['amount'] > 0) {
            DB::transaction(function () use ($request, $purchase, $paymentPurchaseStatusCalculator) {

                $total_paid = $purchase->paid_amount + $request->get('amount');

                $payment_status = $paymentPurchaseStatusCalculator->execute(
                    $purchase->grand_total - $total_paid,
                    $purchase->grand_total
                );

                PaymentPurchase::create([
                    'purchase_id' => $request['purchase_id'],
                    'account_id' => $request['account_id'] ? $request['account_id'] : NULL,
                    'type' => $request['type'],
                    'amount' => $request['amount'],
                    'change' => $request['change'],
                    'notes' => $request['notes'],
                    'ref' => PaymentPurchase::genRef(),
                    'date' => now(),
                    'user_id' => Auth::user()->id,
                ]);

                // Account exists, perform the update
                Account::where('id', $request['account_id'])
                    ->decrement('balance', $request['amount']);

                $purchase->update([
                    'paid_amount' => $total_paid,
                    'payment_status' => $payment_status,
                ]);

            }, 10);
        }

        return response()->json(['success' => true, 'message' => 'Payment Create successfully'], 200);
    }

    //------------ function show -----------\\

    public function show($id)
    {
        //

    }

    //----------- Update Payment Purchases --------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', PaymentPurchase::class);

        \DB::transaction(function () use ($id, $request) {
            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $payment = PaymentPurchase::findOrFail($id);

            // Check If User Has Permission view All Records
            if (!$view_records) {
                // Check If User->id === payment->id
                $this->authorizeForUser($request->user('api'), 'check_record', $payment);
            }

            $purchase = Purchase::whereId($request['purchase_id'])->first();
            $old_total_paid = $purchase->paid_amount - $payment->amount;
            $new_total_paid = $old_total_paid + $request['amount'];

            $due = $purchase->grand_total - $new_total_paid;

            $payment_status = $this->getPaymentStatus($due, $purchase);

            //delete old balance
            $account = Account::where('id', $payment->account_id)->exists();

            if ($account) {
                // Account exists, perform the update
                $account = Account::find($payment->account_id);
                $account->update([
                    'balance' => $account->balance + $payment->amount,
                ]);
            }

            $payment->update([
                'date' => $request['date'],
                'account_id' => $request['account_id'] ? $request['account_id'] : NULL,
                'type' => $request['type'],
                'amount' => $request['amount'],
                'change' => $request['change'],
                'notes' => $request['notes'],
            ]);

            //update new account

            $new_account = Account::where('id', $request['account_id'])->exists();

            if ($new_account) {
                // Account exists, perform the update
                $new_account = Account::find($request['account_id']);
                $new_account->update([
                    'balance' => $new_account->balance - $request['amount'],
                ]);
            }

            $purchase->paid_amount = $new_total_paid;
            $purchase->payment_status = $payment_status;
            $purchase->save();

        }, 10);

        return response()->json(['success' => true, 'message' => 'Payment Update successfully'], 200);
    }

    //----------- Delete Payment Purchase --------------\\

    public function destroy(Request $request, $id, PaymentPurchaseStatusCalculator $paymentPurchaseStatusCalculator)
    {
        $this->authorizeForUser($request->user('api'), 'delete', PaymentPurchase::class);
        $payment = PaymentPurchase::with('purchase')
            ->findOrFail($id);
        $this->checkAuthorization(
            resource: $payment,
            rolePermission: 'record_view',
            resourcePermission: 'check_record'
        );


        DB::transaction(function () use ($id, $request, $payment, $paymentPurchaseStatusCalculator) {
            // update purchase status
            $purchase = $payment->purchase;

            $total_paid = $purchase->paid_amount - $payment->amount;
            $payment_status = $paymentPurchaseStatusCalculator->execute(
                $purchase->grand_total - $total_paid,
                $purchase->grand_total
            );

            // increment bank account by revert the payment
            $payment->account?->increment('balance', $payment->amount);

            // update purchase record by revert the payment
            $purchase->update([
                'paid_amount' => $total_paid,
                'payment_status' => $payment_status,
            ]);

            $payment->delete();

        }, 3);

        return response()->json(['success' => true, 'message' => 'Payment Delete successfully'], 200);

    }


    //----------- Payment Purchase PDF --------------\\

    public function Payment_purchase_pdf(Request $request, $id)
    {
        $payment = PaymentPurchase::with('purchase', 'purchase.provider')->findOrFail($id);

        $payment_data['purchase_ref'] = $payment['purchase']->ref;
        $payment_data['supplier_name'] = $payment['purchase']['provider']->name;
        $payment_data['supplier_phone'] = $payment['purchase']['provider']->phone;
        $payment_data['supplier_adr'] = $payment['purchase']['provider']->Address;
        $payment_data['supplier_email'] = $payment['purchase']['provider']->email;
        $payment_data['amount'] = $payment->amount;
        $payment_data['ref'] = $payment->ref;
        $payment_data['date'] = $payment->date;
        $payment_data['type'] = $payment->type;

        $helpers = new Helper();
        $settings = Setting::where('deleted_at', '=', null)->first();
        $symbol = $helpers->getCurrencyCode();

        $Html = view('pdf.payments_purchase', [
            'symbol' => $symbol,
            'setting' => $settings,
            'payment' => $payment_data,
        ])->render();

        $arabic = new Arabic();
        $p = $arabic->arIdentify($Html);

        for ($i = count($p) - 1; $i >= 0; $i -= 2) {
            $utf8ar = $arabic->utf8Glyphs(substr($Html, $p[$i - 1], $p[$i] - $p[$i - 1]));
            $Html = substr_replace($Html, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
        }

        $pdf = PDF::loadHTML($Html);

        return $pdf->download('Payment_Purchase.pdf');

    }


    //------------- Send Payment purchase on Email -----------\\

    public function SendEmail(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', PaymentPurchase::class);

        //PaymentPurchase
        $payment = PaymentPurchase::with('purchase.provider')->findOrFail($request->id);

        $helpers = new Helper();
        $currency = $helpers->Get_Currency();

        //settings
        $settings = Setting::where('deleted_at', '=', null)->first();

        //the custom msg of payment_received
        $emailMessage = EmailMessage::where('name', 'payment_sent')->first();

        if ($emailMessage) {
            $message_body = $emailMessage->body;
            $message_subject = $emailMessage->subject;
        } else {
            $message_body = '';
            $message_subject = '';
        }


        $payment_number = $payment->ref;

        $total_amount = $currency . ' ' . number_format($payment->amount, 2, '.', ',');

        $contact_name = $payment['purchase']['provider']->name;
        $business_name = $settings->CompanyName;

        //receiver email
        $receiver_email = $payment['purchase']['provider']->email;

        //replace the text with tags
        $message_body = str_replace('{contact_name}', $contact_name, $message_body);
        $message_body = str_replace('{business_name}', $business_name, $message_body);
        $message_body = str_replace('{payment_number}', $payment_number, $message_body);
        $message_body = str_replace('{total_amount}', $total_amount, $message_body);

        $email['subject'] = $message_subject;
        $email['body'] = $message_body;
        $email['company_name'] = $business_name;

        $this->set_config_mail();

        $mail = Mail::to($receiver_email)->send(new CustomEmail($email));

        return $mail;
    }


    //-------------------Sms Notifications -----------------\\

    public function Send_SMS(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', PaymentPurchase::class);

        //PaymentPurchase
        $payment = PaymentPurchase::with('purchase.provider')->findOrFail($request->id);

        //settings
        $settings = Setting::where('deleted_at', '=', null)->first();

        $default_sms_gateway = sms_gateway::where('id', $settings->sms_gateway)
            ->where('deleted_at', '=', null)->first();

        $helpers = new Helper();
        $currency = $helpers->Get_Currency();

        //the custom msg of payment_sent
        $smsMessage = SMSMessage::where('name', 'payment_sent')->first();

        if ($smsMessage) {
            $message_text = $smsMessage->text;
        } else {
            $message_text = '';
        }

        $payment_number = $payment->ref;

        $total_amount = $currency . ' ' . number_format($payment->amount, 2, '.', ',');

        $contact_name = $payment['purchase']['provider']->name;
        $business_name = $settings->CompanyName;

        //receiver phone
        $receiverNumber = $payment['purchase']['provider']->phone;

        //replace the text with tags
        $message_text = str_replace('{contact_name}', $contact_name, $message_text);
        $message_text = str_replace('{business_name}', $business_name, $message_text);
        $message_text = str_replace('{payment_number}', $payment_number, $message_text);
        $message_text = str_replace('{total_amount}', $total_amount, $message_text);

        //twilio
        if ($default_sms_gateway->title == "twilio") {
            try {

                $account_sid = env("TWILIO_SID");
                $auth_token = env("TWILIO_TOKEN");
                $twilio_number = env("TWILIO_FROM");

                $client = new Client_Twilio($account_sid, $auth_token);
                $client->messages->create($receiverNumber, [
                    'from' => $twilio_number,
                    'body' => $message_text]);

            } catch (Exception $e) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
            //nexmo
        } elseif ($default_sms_gateway->title == "nexmo") {
            try {

                $basic = new \Nexmo\Client\Credentials\Basic(env("NEXMO_KEY"), env("NEXMO_SECRET"));
                $client = new \Nexmo\Client($basic);
                $nexmo_from = env("NEXMO_FROM");

                $message = $client->message()->send([
                    'to' => $receiverNumber,
                    'from' => $nexmo_from,
                    'text' => $message_text
                ]);

            } catch (Exception $e) {
                return response()->json(['message' => $e->getMessage()], 500);
            }

            //---- infobip
        } elseif ($default_sms_gateway->title == "infobip") {

            $BASE_URL = env("base_url");
            $API_KEY = env("api_key");
            $SENDER = env("sender_from");

            $configuration = (new Configuration())
                ->setHost($BASE_URL)
                ->setApiKeyPrefix('Authorization', 'App')
                ->setApiKey('Authorization', $API_KEY);

            $client = new Client_guzzle();

            $sendSmsApi = new SendSMSApi($client, $configuration);
            $destination = (new SmsDestination())->setTo($receiverNumber);
            $message = (new SmsTextualMessage())
                ->setFrom($SENDER)
                ->setText($message_text)
                ->setDestinations([$destination]);

            $request = (new SmsAdvancedTextualRequest())->setMessages([$message]);

            try {
                $smsResponse = $sendSmsApi->sendSmsMessage($request);
                echo("Response body: " . $smsResponse);
            } catch (Throwable $apiException) {
                echo("HTTP Code: " . $apiException->getCode() . "\n");
            }

        }

        return response()->json(['success' => true]);
    }


    public function getNumberOrder(Request $request)
    {
        return response()->json(['ref' => PaymentPurchase::genRef()]);

    }


}
