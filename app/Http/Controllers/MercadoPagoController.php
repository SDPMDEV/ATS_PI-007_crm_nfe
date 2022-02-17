<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

                $preference->notification_url = str_replace("/api_fiscal", "", url('/order/details/'));
                $preference->external_reference = $request->sale_id;
                $preference->payment_methods = [
                    "excluded_payment_types" => [
                        ["id" => "pec"],
                        ["id" => "digital_wallet"]
                    ]
                ];

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
     * @return JsonResponse|mixed
     */
    public function getNotification(Request $request)
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
                CURLOPT_HTTPHEADER => [ 'Authorization: Bearer ' . $this->access_token ]
            ]);

            $payment_info = json_decode(curl_exec($curl), true);
            curl_close($curl);

            return response()->json([
                'error' => false,
                'mercado_pago' => $payment_info,
            ]);
        }

        return response()->json([
             'error' => true,
             'message' => 'Identificador da venda não informado'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function setKeys(Request $request)
    {
        if(isset($request->access_token) && !empty($request->access_token) &&
            isset($request->public_key) && !empty($request->public_key)) {

            if($this->setEnv(["MP_ACCESS_TOKEN" => $request->access_token]) &&
                $this->setEnv(["MP_PUBLIC_KEY" => $request->public_key])) {
                return response()->json([
                    "error" => false,
                    "message" => "Chave inserida com sucesso."
                ]);
            }
        }

        return response()->json([
            "error" => true,
            "message" => "Informe todas as chaves à serem inseridas."
        ]);
    }

    public function getKeys()
    {
        return response()->json([
            "access_token" => env("MP_ACCESS_TOKEN"),
            "public_key" => env("MP_PUBLIC_KEY")
        ]);
    }

    public function setEnv(array $values)
    {

        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {

                $str .= "\n"; // In case the searched variable is in the last line without \n
                $keyPosition = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                // If key does not exist, add it
                if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
                    $str .= "{$envKey}={$envValue}\n";
                } else {
                    $str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
                }

            }
        }

        $str = substr($str, 0, -1);
        if (!file_put_contents($envFile, $str)) return false;
        return true;

    }
}
