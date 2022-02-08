<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use MercadoPago\SDK;
use MercadoPago\Preference;
use MercadoPago\Item;

class MercadoPagoController extends Controller
{
    /**
     * @var string|array|false
     */
    private string $access_token;

    /**
     * @var string
     */
    private string $token;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->token = $this->getToken();

        if((!isset($request->api_token) && $request->api_token != $this->token))
            http_response_code(401);

        $this->access_token = getenv("MP_ACCESS_TOKEN");
    }

    /**
     * @return string
     */
    private function getToken(): string
    {
        return DB::table("fiscal_api_table")->get(["token"])[0]->token;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function makePayment(Request $request) : JsonResponse
    {
        try {
            if(!empty($request->all()) && !empty($request->title)) {
                SDK::setAccessToken($this->access_token);

                $preference = new Preference();
                $item = new item();

                $item->title = $request->title;
                $item->quantity = $request->quantity;
                $item->unit_price = (double)$request->unit_price;

                $preference->items = array($item);
                $preference->back_urls = [
                    'success' => $request->success_url,
                    'failure' => $request->failure_url,
                    'pending' => $request->pending_url
                ];

                $preference->notification_url = url('/api/mercado_pago/get_notification');
                $preference->external_reference = $request->sale_id;

                if ($preference->save()) {

                    return response()->json([
                        'error' => false,
                        'link' => $preference->init_point
                    ]);
                } else {
                    return response()->json([
                        'error' => true,
                        'message' => $preference->error,
                        'causes' => $preference->error->causes
                    ]);
                }
            }

            return response()->json([
                'error' => true,
                'message' => 'Dados incompletos'
            ]);
        } catch(\Exception $e) {

            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @param Request $request
     * @param string $sale_id
     * @return Application|Redirector|RedirectResponse|void
     */
    public function getNotification(Request $request, string $sale_id)
    {
        if($request->collection_id) {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.mercadopago.com/v1/payments/' . $request->collection_id,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => [ 'Authorization: Bearer' . $this->access_token ]
            ]);

            $payment_info = json_decode(curl_exec($curl), true);
            curl_close($curl);

            return redirect('/cart/checkout/?' . http_build_query($payment_info));
        }

        return redirect('/cart/checkout/?' . 'error=1');
    }
}
