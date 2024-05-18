<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Controller;
use App\Models\CombinedOrder;
use App\Utility\NotificationUtility;
use Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Database\QueryException;

use DB;


class PaymobController extends Controller
{ 
  
        /**
         * Summary of checkingOut
         * @param mixed $payment_method
         * @param mixed $integration_id
         * @param mixed $order_id
         * @param mixed $iframe_id_or_wallet_number
         * @return \Illuminate\Http\RedirectResponse
         */
        public function checkingOut($payment_method, $integration_id, $order_id, $iframe_id_or_wallet_number): RedirectResponse
        {
          
                $response = Http::withHeaders([
                'content-type' => 'application/json'])->post('https://accept.paymobsolutions.com/api/auth/tokens', [
                "api_key"=>env('PAYMOB_API_KEY')]);    

                $json=$response->json();
                $order = CombinedOrder::query()->findOrFail($order_id);
                $grand_total = $order->grand_total;
               
                $response_final=Http::withHeaders([
                    'content-type'=>'application/json'])->post( 'https://accept.paymobsolutions.com/api/ecommerce/orders', [
                    "auth_token"=>$json['token'],
                    "delivery_needed"=>"false",
                    "amount_cents" =>$grand_total*100,
                    "merchant_order_id" => $order->id
                ]);


                $json_final=$response_final->json();
                $user = Auth::user();

                
                $data = DB::table('addresses')
    ->select('countries.name as country', 'states.name as state', 'cities.name as city', 'postal_code', 'phone', 'address')
    ->join('countries', 'addresses.country_id', '=', 'countries.id')
    ->join('cities', 'addresses.city_id', '=', 'cities.id')
    ->join('states', 'addresses.state_id', '=', 'states.id')
    ->where('user_id', $user->id)
    ->first();

                $name = $user->name;
                if ((count (explode (" ", $name)) == 1)) {
                    $first_name = $name; 
                    $last_name=$name;
                } else 
                {
                    $first_name = explode( " ", $name)[0];
                    $last_name = explode(" ", $name) [1];
                }
                $user_token = $json['token'];
                $response_final_final=Http::withHeaders([
                    'content-type'=>'application/json'
                    ])->post('https://accept.paymobsolutions.com/api/acceptance/payment_keys', [
                    "auth_token"=>$user_token,
                    "expiration"=>36000,
                    "amount_cents"=>$json_final['amount_cents'],
                    "order_id"=>$json_final['id'],
                    "billing_data"=> [
                        "first_name"   =>$first_name,
                        "last_name"    =>$last_name,
                        "phone_number" => $data->phone ?: "NA",
                        "email"        => $user->email,
                        "apartment"    => "NA",
                        "floor"        => "NA",
                        "street"       => $data->address,
                        "building" => "NA",
                        "shipping_method" => "NA",
                        "postal_code" =>$data->postal_code ?: "NA",
                        "city" => $data->city,
                        "state" =>$data->state ?: "NA",
                        "country" => $data->country,
                    ],
                    "currency"=>"EGP",
                    "integration_id"=>$integration_id
                    ]);
                    
                    $response_final_final_json=$response_final_final->json();
                    
                    if ($payment_method == 'paymob_mobile_wallet_payment' && $iframe_id_or_wallet_number != 'null') {
                      
                        $response_iframe =Http::withHeaders(['content-type' =>'application/json'])->post('https://accept.paymob.com/api/acceptance/payments/pay',["source"=> ["identifier"=> $iframe_id_or_wallet_number,"subtype"=> "WALLET"],
                        "payment_token"=>$response_final_final_json['token'],
                        ]);
                        return redirect ($response_iframe->json()['redirect_url']);
                    }  elseif($payment_method == 'paymob_mobile_wallet_payment' && $iframe_id_or_wallet_number == 'null'){
                                    flash(translate('Payment Failed'))->error();
                                    return redirect()->route('home');
                        
                    }else {

                        return redirect ('https://accept.paymobsolutions.com/api/acceptance/iframes/'. $iframe_id_or_wallet_number.'?payment_token='. $response_final_final_json['token']);
                        }
        }


        public function callback(Request $request): RedirectResponse
        {
            $payment_details = json_encode($request->all());
            // $request->merge(['success' => true]);
            if ($request->success =="true")
            {
                $combined_order = CombinedOrder::query()->findorFail($request->merchant_order_id);
                // 
                try {
                        Order::where('combined_order_id', $request->merchant_order_id)
                            ->whereIn('payment_type', ['paymob_card_payment', 'paymob_mobile_wallet_payment'])
                            ->update(['payment_status' => 'paid']);
                            
                            $updatedOrder = Order::where('combined_order_id', $request->merchant_order_id)->first();

                    } catch (QueryException $e) { dd($e);}
// 
                
                return (new CheckoutController)->checkout_done($request->merchant_order_id, $payment_details);
            } else {
                flash(translate('Payment Failed'))->error();
                return redirect()->route('home');
            }
        }
}