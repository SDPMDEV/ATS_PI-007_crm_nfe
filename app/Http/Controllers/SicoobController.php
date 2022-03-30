<?php

namespace App\Http\Controllers;


use ArUtil\I18N\Date;
use Exception;
use Faker\Provider\DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenBoleto\Agente;
use OpenBoleto\Banco\Sicoob;
use CnabPHP\Remessa;
use CnabPHP\Retorno;
use PhpOffice\PhpSpreadsheet\Reader\Xls\RC4;
use ZipArchive;

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

    public function getBoleto(Request $request)
    {
        $sacado = new Agente($request->customer_name, $request->customer_cpfcnpj, $request->customer_address, $request->customer_cep ?? '', $request->customer_city, $request->customer_uf);
        $cedente = new Agente($request->issuer_name, $request->issuer_cpfcnpj, $request->issuer_address, $request->issuer_cep, $request->issuer_city, $request->issuer_uf);

        $dataVenc = date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $request->venc_dias . ' days'));

        if (isset($request->data_vencimento)) {
            $dataVenc = date('Y-m-d', strtotime($dataVenc));
        }

        $boletoArr = [
            // Parâmetros obrigatórios
            'dataVencimento' => new \DateTime($dataVenc),
            'valor' => doubleval($request->valor),
            'sacado' => $sacado,
            'cedente' => $cedente,
            'agencia' => $request->agencia, // Até 4 dígitos
            'carteira' => $request->codigo_carteira, // 1, 2 e 3
            'modalidade' => $request->modalidade, // 01, 02 e 05
            'conta' => $request->conta, // Até 10 dígitos
            'convenio' => $request->convenio, // Até 5 dígitos
            'sequencial' => $request->sequencial, // Até 10 dígitos
            'logoPath' => $request->logo_path, // Logo da sua empresa
            'contaDv' => $request->conta_dv,
            'agenciaDv' => $request->agencia_dv,
            'descricaoDemonstrativo' => $request->descDemo ?? [],
            'instrucoes' => $request->inst ?? [],
            'dataDocumento' => (isset($request->data_emissao)) ? $request->data_emissao : new \DateTime(),
            //'dataProcessamento' => new DateTime(),
            //'contraApresentacao' => true,
            //'pagamentoMinimo' => 23.00,
            'aceite' => 'N',
            'CodigoBeneficiario' => $request->codigo_beneficiario,
            'CodigoBeneficiarioDv' => $request->codigo_beneficiario_dv,
            'especieDoc' => $request->especie,
            'numeroDocumento' => $request->num_doc,
            //'usoBanco' => 'Uso banco',
            //'layout' => 'layout.phtml',
            //'logoPath' => 'http://boletophp.com.br/img/opensource-55x48-t.png',
            //'sacadorAvalista' => new Agente('Antônio da Silva', '02.123.123/0001-11'),
            'descontosAbatimentos' => doubleval($request->desconto),
            'moraMulta' => doubleval($request->mora_multa),
            'outrasDeducoes' => $request->outras_ded,
            'outrosAcrescimos' => doubleval($request->outro_acrescimo),
            'valorCobrado' => $request->valor_cob,
            'valorUnitario' => doubleval($request->valor),
            'quantidade' => $request->quantidade,
        ];

        $boleto = new Sicoob($boletoArr);

        return response()->json([
            'boleto' => $boleto->getOutput(),
            'vencimento' => $dataVenc
        ]);
    }

    public function createRemessa(Request $request)
    {
        $arquivo = new Remessa(756,'cnab400',array(

            //Informações da emrpesa recebedora
            'tipo_inscricao'  	=>	'2', // 1 para cpf, 2 cnpj
            'numero_inscricao'	=>	$request->issuer_cnpj, // seu cpf ou cnpj completo
            'agencia'       	=>	$request->agencia, // agencia sem o digito verificador
            'agencia_dv'    	=>	$request->agencia_dv, // somente o digito verificador da agencia
            'conta'         	=> 	$request->conta, // número da conta
            'conta_dv'     		=> 	$request->conta_dv, // digito da conta
            'nome_empresa' 		=>	$request->issuer_name, // seu nome de empresa
            'numero_sequencial_arquivo'	=>	$request->sequencial,

            'codigo_beneficiario'	=> $request->codigo_beneficiario, // codigo fornecido pelo banco
            'codigo_beneficiario_dv'=> $request->codigo_beneficiario_dv, // codigo fornecido pelo banco

            'situacao_arquivo' => 'P' // use T para teste e P para produção
        ));
        $lote  = $arquivo->addLote([ 'tipo_servico'=> '1' ]); // tipo_servico  = 1 para cobrança registrada, 2 para sem registro

        $lote->inserirDetalhe([
            //Registro 3P Dados do Boleto
            'nosso_numero'      => $request->sequencial, // numero sequencial de boleto
            //'nosso_numero_dv'   =>	1, // pode ser informado ou calculado pelo sistema
            'parcela' 			=>	'01',
            'modalidade'		=>	'1',
            'tipo_formulario'	=>	'4',
            'codigo_carteira'   =>	$request->codigo_carteira, // codigo da carteira
            'carteira'   		=>	$request->codigo_carteira, // codigo da carteira
            'seu_numero'        =>	$request->sequencial,// se nao informado usarei o nosso numero
            'data_vencimento'   =>	date('Y-m-d', strtotime($request->data_emissao. ' + '.$request->venc_dias.' days')), // informar a data neste formato AAAA-MM-DD
            'valor'             =>	$request->valor, // Valor do boleto como float valido em php
            'cod_emissao_boleto'=>	'2', // tipo de emissao do boleto informar 2 para emissao pelo beneficiario e 1 para emissao pelo banco
            'especie_titulo'    => 	$request->especie, // informar dm e sera convertido para codigo em qualquer laytou conferir em especie.php
            'data_emissao'      => 	$request->data_emissao, // informar a data neste formato AAAA-MM-DD
            'codigo_juros'		=>	'2', // Taxa por mês,
            'data_juros'   	  	=> 	date('Y-m-d', strtotime($request->data_emissao. ' + '.$request->venc_dias.' days')), // data dos juros, mesma do vencimento
            'vlr_juros'         => 	($request->mora_multa / 100) * $request->valor, // Valor do juros/mora informa 1% e o sistema recalcula a 0,03% por
            // Você pode inserir desconto se houver, ou deixar em branco
            //'codigo_desconto'	=>	'1',
            //'data_desconto'		=> 	'2018-04-15', // inserir data para calcular desconto
            //'vlr_desconto'		=> 	'0', // Valor do desconto
//            'vlr_IOF'			=> 	'0',
            'protestar'         => 	'1', // 1 = Protestar com (Prazo) dias, 3 = Devolver após (Prazo) dias
            'prazo_protesto'    => 	'90', // Informar o numero de dias apos o vencimento para iniciar o protesto
            'identificacao_contrato'	=>	$request->identificacao_contrato,


            // Registro 3Q [PAGADOR]
            'tipo_inscricao'    => (strlen($request->customer_cpfcnpj) > 14) ? "2" : "1", //campo fixo, escreva '1' se for pessoa fisica, 2 se for pessoa juridica
            'numero_inscricao'  => $request->customer_cpfcnpj,//cpf ou ncpj do pagador
            'nome_pagador'      => $request->customer_name, // O Pagador é o cliente, preste atenção nos campos abaixo
            'endereco_pagador'  => $request->customer_address,
            'bairro_pagador'    => $request->customer_bairro,
            'cep_pagador'       => $request->customer_cep, // com hífem
            'cidade_pagador'    => $request->customer_city,
            'uf_pagador'        => $request->customer_uf,

            // Registro 3R Multas, descontos, etc
            // Você pode inserir desconto se houver, ou deixar em branco, mas quando informar
            // deve preencher os 3 campos: codigo, data e valor
            'codigo_multa'		=>	'2', // Taxa por mês
            'data_multa'   	  	=> 	date('Y-m-d', strtotime($request->data_emissao. ' + '.$request->venc_dias.' days')), // data dos juros, mesma do vencimento
            'vlr_multa'         => 	($request->valor_venc / 100) * $request->valor, // Valor do juros de 2% ao mês

            // Registro 3S3 Mensagens a serem impressas
            'mensagem_sc_1' 	=> $request->inst1,
            'mensagem_sc_2' 	=> $request->inst2,
            'mensagem_sc_3' 	=> $request->inst3,
            'mensagem_sc_4' 	=> $request->inst4,

        ]);

        $remessa = utf8_decode(trim($arquivo->getText())); // observar a header do seu php para não gerar comflitos de codificação de caracteres;
        $arquivoNome = 'remessa_'.$request->sequencial . ".REM";

        // Grava o arquivo
        if ( file_put_contents($this->verificaPastas()->path.$arquivoNome, $remessa) ) {
            $this->verificaPastas()->close();
            return response()->json([
                'error' => false,
                'name' => 'remessa_'.$request->sequencial . ".REM"
            ]);
        }

        return response()->json([ 'error' => true ]);
    }

    /* Função que pega o nome das pastas de acordo com o número do ano
     * Caso as pastas não existam, serão criadas.
     * Os arquivos de remessa serão organizados em ano/mês
    */
    private function verificaPastas()
    {
        date_default_timezone_set('America/Sao_Paulo');
        $base_dir = dir(public_path() . '/remessas_sicoob/');

        if (!is_dir($base_dir->path.date('Y').'/'.date('m').'/')){
            mkdir ($base_dir->path.date('Y'), 0755);
            mkdir($base_dir->path.date('Y').'/'.date('m'), 0755);
        };
        $base_dir = dir($base_dir->path.date('Y').'/'.date('m').'/');
        //Retorna o caminho para guardar o arquivo
        return $base_dir;
    }

    public function getRetorno(Request $request)
    {
        $fileContent = file_get_contents(public_path() . '/retornos_sicoob/' . $request->retorno);

        $arquivo = new Retorno($fileContent);

        $registros = $arquivo->getRegistros();
        $boletoPagos = [];
        foreach($registros as $registro) {
            if($registro->R3U->codigo_movimento==6) {
                $boletoPagos[] = [
                    'nosso_numero' => $registro->nosso_numero,
                    'vlr_pago' => $registro->R3U->vlr_pago
                ];
            }
        }
        return response()->json(['error' => false, 'boletos' => $boletoPagos]);
    }
}
