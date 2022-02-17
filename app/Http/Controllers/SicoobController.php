<?php

namespace App\Http\Controllers;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SicoobController extends Controller
{
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

    }

    /**
     * @return string
     */
    private function getToken(): string
    {
        return DB::table("fiscal_api_table")->get(["token"])[0]->token;
    }

    /**
     * @return JsonResponse
     */
    public function getKey()
    {
        return response()->json(["sicoob_key" => env("SICOOB_KEY")]);
    }

    public function setKey(Request $request)
    {
        if (!isset($request->sicoob_key) || empty($request->sicoob_key))
            return response()->json(["error" => true, "message" => "Chave não informada"]);

        if ($this->setEnv(["SICOOB_KEY" => $request->sicoob_key]))
            return response()->json(["error" => false, "message" => "Chave inserida com sucesso.", 'env' => app()->environmentFilePath()]);

        return response()->json(["error" => true, "message" => "Erro ao inserir chave."]);
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
