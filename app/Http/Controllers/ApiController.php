<?php

namespace App\Http\Controllers;

use App\Cliente;
use App\ConfigNota;
use App\Cte;
use App\Mdfe;
use App\NaturezaOperacao;
use App\Services\NFService;
use App\Services\NFCeService;
use App\Venda;
use App\VendaCaixa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use NFePHP\Common\Certificate;
use App\Certificado;
use App\EscritorioContabil;
use App\Tributacao;
use App\ManifestaDfe;
use App\Services\DFeService;
use NFePHP\NFe\Common\Standardize;
use App\Categoria;
use App\Produto;
use App\Compra;
use App\Cidade;
use App\Fornecedor;
use App\ItemDfe;
use NFePHP\NFe\Make;
use NFePHP\NFe\Complements;
use App\ListaPreco;
use Exception;
use NFePHP\DA\NFe\Danfe;
use NFePHP\NFe\Tools;
use function GuzzleHttp\json_decode;
use NFePHP\DA\NFe\Daevento;
use Mail;
use ZipArchive;
use NFePHP\DA\NFe\Danfce;
use NFePHP\DA\NFe\Cupom;
use PhpParser\Node\Expr;

class ApiController extends \NFePHP\DA\NFe\Danfe
{
    private $token;

    public function __construct(Request $request)
    {
        $this->token = $this->getToken();

        if ((!isset($request->api_token) && $request->api_token != $this->token))
            return response()->json(["error" => "Unauthorized"], 401);
    }

    private function sanitizeString($str)
    {
        return preg_replace('{\W}', ' ', preg_replace('{ +}', ' ', strtr(
            utf8_decode(html_entity_decode($str)),
            utf8_decode('ÀÁÃÂÉÊÍÓÕÔÚÜÇÑàáãâéêíóõôúüçñ'),
            'AAAAEEIOOOUUCNaaaaeeiooouucn')));
    }

    private function getToken(): string
    {
        return DB::table("fiscal_api_table")->get(["token"])[0]->token ?? "";
    }

    public function returnIssuer(Request $request)
    {
        return ConfigNota::first();
    }

    public function getIssuerConfigs(Request $request)
    {
        return response()->json([
            "estados" => ConfigNota::estados(),
            "tiposPagamento" => ConfigNota::tiposPagamento(),
            "tiposFrete" => ConfigNota::tiposFrete(),
            "listaCSTCSOSN" => ConfigNota::listaCST(),
            "listaCSTPISCOFINS" => ConfigNota::listaCST_PIS_COFINS(),
            "listaCSTIPI" => ConfigNota::listaCST_IPI(),
            "naturezas" => NaturezaOperacao::all(),
            "certificado" => !empty(DB::table("fiscal_certificados")->get()->first()),
            "infoCerfificado" => $this->getInfoCertificado(Certificado::first()),
            "listaPrecos" => ListaPreco::all(),
            "estados_clientes" => Cliente::estados(),
            "unidadesDeMedida" => Produto::unidadesMedida(),
            "anps" => Produto::lista_ANP()
        ]);
    }

    public function saveIssuerConfigs(Request $request)
    {


        ConfigNota::truncate();
        $uf = $request->all()["UF"];
        if ($request->id == 0) {
            $result = ConfigNota::create([
                'razao_social' => strtoupper($this->sanitizeString($request->all()['razao_social'])),
                'nome_fantasia' => strtoupper($this->sanitizeString($request->all()['nome_fantasia'])),
                'cnpj' => $request->all()['cnpj'],
                'ie' => $request->all()['ie'],
                'logradouro' => strtoupper($this->sanitizeString($request->all()['logradouro'])),
                'numero' => strtoupper($this->sanitizeString($request->all()['numero'])),
                'bairro' => strtoupper($this->sanitizeString($request->all()['bairro'])),
                'cep' => $request->all()['cep'],
                'municipio' => strtoupper($this->sanitizeString($request->all()['municipio'])),
                'codMun' => $request->all()['codMun'],
                'codPais' => $request->all()['codPais'],
                'UF' => ConfigNota::getUF($uf),
                'pais' => strtoupper($request->all()['pais']),
                'fone' => $this->sanitizeString($request->all()['fone']),
                'CST_CSOSN_padrao' => $request->all()['CST_CSOSN_padrao'],
                'CST_COFINS_padrao' => $request->all()['CST_COFINS_padrao'],
                'CST_PIS_padrao' => $request->all()['CST_PIS_padrao'],
                'CST_IPI_padrao' => $request->all()['CST_IPI_padrao'],
                'frete_padrao' => $request->all()['frete_padrao'],
                'tipo_pagamento_padrao' => $request->all()['tipo_pagamento_padrao'],
                'nat_op_padrao' => $request->all()['nat_op_padrao'] ?? 0,
                'ambiente' => $request->all()['ambiente'],
                'cUF' => $uf,
                'ultimo_numero_nfe' => $request->all()['ultimo_numero_nfe'],
                'ultimo_numero_nfce' => $request->all()['ultimo_numero_nfce'],
                'ultimo_numero_cte' => $request->all()['ultimo_numero_cte'],
                'ultimo_numero_mdfe' => $request->all()['ultimo_numero_mdfe'],
                'numero_serie_nfe' => $request->all()['numero_serie_nfe'],
                'numero_serie_nfce' => $request->all()['numero_serie_nfce'],
                'csc' => $request->all()['csc'],
                'csc_id' => $request->all()['csc_id'],
                'certificado_a3' => $request->certificado_a3 ? true : false,
            ]);
        } else {
            $config = ConfigNota::
            first();

            $config->razao_social = strtoupper($this->sanitizeString($request->razao_social));
            $config->nome_fantasia = strtoupper($this->sanitizeString($request->nome_fantasia));
            $config->cnpj = $this->sanitizeString($request->cnpj);
            $config->ie = $this->sanitizeString($request->ie);
            $config->logradouro = strtoupper($this->sanitizeString($request->logradouro));
            $config->numero = strtoupper($this->sanitizeString($request->numero));
            $config->bairro = strtoupper($this->sanitizeString($request->bairro));
            $config->cep = $request->cep;
            $config->municipio = strtoupper($this->sanitizeString($request->municipio));
            $config->codMun = $request->codMun;
            $config->codPais = $request->codPais;
            $config->UF = ConfigNota::getUF($uf);
            $config->pais = strtoupper($request->pais);
            $config->fone = $request->fone;

            $config->CST_CSOSN_padrao = $request->CST_CSOSN_padrao;
            $config->CST_COFINS_padrao = $request->CST_COFINS_padrao;
            $config->CST_PIS_padrao = $request->CST_PIS_padrao;
            $config->CST_IPI_padrao = $request->CST_IPI_padrao;

            $config->frete_padrao = $request->frete_padrao;
            $config->tipo_pagamento_padrao = $request->tipo_pagamento_padrao;
            $config->nat_op_padrao = $request->nat_op_padrao ?? 0;
            $config->ambiente = $request->ambiente;
            $config->cUF = $uf;
            $config->ultimo_numero_nfe = $request->ultimo_numero_nfe;
            $config->ultimo_numero_nfce = $request->ultimo_numero_nfce;
            $config->ultimo_numero_cte = $request->ultimo_numero_cte;
            $config->ultimo_numero_mdfe = $request->ultimo_numero_mdfe;
            $config->numero_serie_nfe = $request->numero_serie_nfe;
            $config->numero_serie_nfce = $request->numero_serie_nfce;
            $config->csc = $request->csc;
            $config->csc_id = $request->csc_id;
            $config->certificado_a3 = $request->certificado_a3 ? true : false;

            $result = $config->save();
        }

        if ($result) {
            return response()->json(["error" => false, "message" => "Configurado com sucesso!"]);
        } else {
            return response()->json(["error" => true, "message" => "Erro ao configurar!"]);
        }
    }

    private function getInfoCertificado($certificado)
    {
        if (isset($certificado->arquivo) && isset($certificado->senha)) {
            $infoCertificado = Certificate::readPfx($certificado->arquivo, $certificado->senha);

            $publicKey = $infoCertificado->publicKey;

            $inicio = $publicKey->validFrom->format('Y-m-d H:i:s');
            $expiracao = $publicKey->validTo->format('Y-m-d H:i:s');

            return [
                'serial' => $publicKey->serialNumber,
                'inicio' => \Carbon\Carbon::parse($inicio)->format('d-m-Y H:i'),
                'expiracao' => \Carbon\Carbon::parse($expiracao)->format('d-m-Y H:i'),
                'id' => $publicKey->commonName
            ];
        }
    }

    public function saveCertificate(Request $request)
    {
        if ($request->hasFile('file') && strlen($request->senha) > 0) {
            $file = $request->file('file');
            $temp = file_get_contents($file);

            if (!openssl_pkcs12_read($temp, $x509certdata, $request->senha)) {
                $tentativas = $this->tentativasCertificado($temp);
                if ($tentativas >= 3) {
                    return response()->json([
                        "error" => true,
                        "message" => "Senha incorreta. Limite de 3 tentativas excedido",
                    ]);
                } else {
                    $tentativas_restantes = 3 - $tentativas;
                    return response()->json([
                        "error" => true,
                        "message" => "Senha incorreta. $tentativas_restantes tentativas restantes.",
                        "type" => "warning"
                    ]);
                }
            } else {
                $tentativas = $this->tentativasCertificado($temp);
                if ($tentativas >= 3) {
                    return response()->json([
                        "error" => true,
                        "message" => "Limite de 3 tentativas excedido",
                    ]);
                } else {
                    Certificado::truncate();
                    $res = Certificado::create([
                        'senha' => $request->senha,
                        'arquivo' => $temp
                    ]);

                    if ($res) {
                        return response()->json(["error" => false, "message" => "Upload de certificado realizado!"]);
                    }
                }
            }
        } else {
            return response()->json(["error" => true, "message" => "Envie o arquivo e senha por favor!"]);
        }
    }

    private function tentativasCertificado($certificado)
    {
        $log = DB::table('fiscal_log_certificados')->where('arquivo', $certificado)->first();

        if ($log == NULL) {
            DB::table('fiscal_log_certificados')->insert([
                'arquivo' => $certificado,
                'tentativas' => 1
            ]);

            return 1;
        } else {
            $all_logs = DB::select("select * from fiscal_log_certificados");
            foreach ($all_logs as $log) {
                if ($log->arquivo == $certificado) {

                    $tentativas = $log->tentativas;
                    $tentativas++;

                    DB::table('fiscal_log_certificados')->where("arquivo", $certificado)->update([
                        'tentativas' => $tentativas
                    ]);

                    return $tentativas;
                }
            }
        }
    }

    public function removeCertificate(Request $request)
    {
        if (Certificado::truncate()) {
            return response()->json(["error" => false, "message" => "Certificado removido com sucesso!"]);
        } else {
            return response()->json(["error" => true, "message" => "Erro ao remover certificado!"]);
        }
    }

    public function teste()
    {
        try {
            $config = ConfigNota::first();

            $cnpj = str_replace(".", "", $config->cnpj);
            $cnpj = str_replace("/", "", $cnpj);
            $cnpj = str_replace("-", "", $cnpj);
            $cnpj = str_replace(" ", "", $cnpj);

            $nfe_service = new NFService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$config->ambiente,
                "razaosocial" => $config->razao_social,
                "siglaUF" => $config->UF,
                "cnpj" => $cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $config->csc,
                "CSCid" => $config->csc_id
            ]);

            $uf = $config->UF;
            return response()->json($nfe_service->consultaCadastro($cnpj, $uf), 200);
        } catch (\Exception $e) {
            return response()->json([
                "error" => true,
                "message" => $e->getMessage()
            ], 500);
        }
    }

    public function getOfficeConfigs()
    {
        return response()->json(EscritorioContabil::first());
    }

    public function saveOfficeConfigs(Request $request)
    {


        if ($request->id == 0) {
            $result = EscritorioContabil::create([
                'razao_social' => strtoupper($this->sanitizeString($request->razao_social)),
                'nome_fantasia' => strtoupper($this->sanitizeString($request->nome_fantasia)),
                'cnpj' => $request->cnpj,
                'ie' => $request->ie,
                'fone' => $request->fone,
                'logradouro' => strtoupper($this->sanitizeString($request->logradouro)),
                'numero' => strtoupper($this->sanitizeString($request->numero)),
                'bairro' => strtoupper($this->sanitizeString($request->bairro)),
                'cep' => $request->cep,
                'email' => $request->email
            ]);
        } else {
            $config = EscritorioContabil::first();

            $config->razao_social = strtoupper($this->sanitizeString($request->razao_social));
            $config->nome_fantasia = strtoupper($this->sanitizeString($request->nome_fantasia));
            $config->cnpj = $request->cnpj;
            $config->ie = $request->ie;
            $config->fone = $request->fone;
            $config->logradouro = strtoupper($this->sanitizeString($request->logradouro));
            $config->numero = strtoupper($this->sanitizeString($request->numero));
            $config->bairro = strtoupper($this->sanitizeString($request->bairro));
            $config->cep = $request->cep;
            $config->email = strtoupper($request->email);
            $result = $config->save();
        }

        if ($result) {
            return response()->json(["error" => false, "message" => "Configurado com sucesso!"]);
        } else {
            return response()->json(["error" => true, "message" => "Erro ao configurar!"]);
        }
    }

    public function getCertificateStatus(Request $request)
    {


        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $temp = file_get_contents($file);

            $all_logs = DB::select("select * from fiscal_log_certificados");

            foreach ($all_logs as $log) {
                if ($log->arquivo == $temp) {
                    return response()->json([
                        "tentativas" => $log->tentativas
                    ]);
                }
            }

        } else {
            return response()->json(["error" => true, "message" => "Envie o arquivo corretamente!"]);
        }
    }

    public function getNatureConfigs()
    {
        return response()->json(NaturezaOperacao::all());
    }

    public function deleteNature(Request $request)
    {


        $id = $request->all()["id"];
        $delete = NaturezaOperacao
            ::where('id', $id)
            ->delete();
        if ($delete) {
            return response()->json(['error' => false, 'message' => 'Registro removido com sucesso']);
        } else {
            return response()->json(['error' => true, 'message' => 'Falha ao remover registro']);
        }
    }

    public function editNature(Request $request)
    {


        $natureza = new NaturezaOperacao();

        $id = $request->all()["id"];
        $resp = $natureza->where('id', $id)->first();

        $resp->natureza = $this->sanitizeString(strtoupper($request->all()['natureza']));
        $resp->CFOP_entrada_estadual = $request->all()['CFOP_entrada_estadual'];
        $resp->CFOP_entrada_inter_estadual = $request->all()['CFOP_entrada_inter_estadual'];
        $resp->CFOP_saida_estadual = $request->all()['CFOP_saida_estadual'];
        $resp->CFOP_saida_inter_estadual = $request->all()['CFOP_saida_inter_estadual'];

        $result = $resp->save();

        if ($result) {
            return response()->json(["error" => false, "message" => "Natureza de operação editada com sucesso!"]);
        } else {
            return response()->json(['error' => true, 'menssage' => 'Erro ao editar categoria!']);
        }
    }

    public function newNature(Request $request)
    {


        $natureza = new NaturezaOperacao();
        $request->merge(['natureza' => strtoupper(
            $this->sanitizeString($request->input('natureza')))]);

        $result = $natureza->create([
            'natureza' => $this->sanitizeString(strtoupper($request->all()['natureza'])),
            'CFOP_entrada_estadual' => $request->all()['CFOP_entrada_estadual'],
            'CFOP_entrada_inter_estadual' => $request->all()['CFOP_entrada_inter_estadual'],
            'CFOP_saida_estadual' => $request->all()['CFOP_saida_estadual'],
            'CFOP_saida_inter_estadual' => $request->all()['CFOP_saida_inter_estadual']
        ]);

        if ($result) {
            return response()->json(["error" => false, "message" => "Natureza de Operação cadastrada com sucesso."]);
        } else {
            return response()->json(['error' => true, 'message' => 'Erro ao cadastrar natureza de operação.']);
        }

    }

    public function getTaxation()
    {
        return response()->json(Tributacao::all());
    }

    public function saveTaxation(Request $request)
    {

        Tributacao::truncate();
        $trib = new Tributacao();

        $res = $trib->create([
            "icms" => $request->all()["icms"],
            "pis" => $request->all()["pis"],
            "cofins" => $request->all()["cofins"],
            "ncm_padrao" => $request->all()["ncm_padrao"],
            "regime" => $request->all()["regime"],
            "ipi" => $request->all()["ipi"],
        ]);

        if ($res) {
            return response()->json(["error" => false, "message" => "Tributação salva com sucesso"]);
        } else {
            return response()->json(["error" => true, "message" => "Falha ao salvar tributação"]);
        }
    }

    public function filtrarXml(Request $request)
    {
        $xml = DB::table('sma_sales')->whereBetween('date', [
            $this->parseDate($request->data_inicial),
            $this->parseDate($request->data_final, true)
        ])
            ->where('pos', '=', 1)
            ->where('estado', '=', 'APROVADO')->get();

        $public = getenv('SERVIDOR_WEB') ? 'public/' : '';

        try {
            if (count($xml) > 0) {

                $zip_file = $public . 'xml.zip';
                $zip = new \ZipArchive();
                $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

                foreach ($xml as $x) {
                    if (file_exists($public . 'xml_nfe/' . $x->chave . '.xml'))
                        $zip->addFile($public . 'xml_nfe/' . $x->chave . '.xml', $x->chave . ".xml");
                }
                $zip->close();
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => "Erro interno do servidor",
                'exception' => $e->getMessage()
            ]);
        }

//        try {
//            $xmlCte = DB::table('sma_sales')->whereBetween('date', [
//                $this->parseDate($request->data_inicial),
//                $this->parseDate($request->data_final, true)
//            ])
//            ->where('estado', '=', 'CANCELADA')
//            ->where('sequencia_cce', '<>', null)
//            ->where('sequencia_cce', '<>', 0)->get();
//
//            if(count($xmlCte) > 0){
//                $zip_file = $public.'xmlcte.zip';
//                $zip = new \ZipArchive();
//                $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
//
//                foreach($xmlCte as $x){
//                    if(file_exists($public.'xml_cte/'.$x->chave. '.xml'))
//                        $zip->addFile($public.'xml_cte/'.$x->chave. '.xml');
//                }
//                $zip->close();
//
//            }
//        } catch(\Exception $e) {
//            return response()->json([
//                'error' => true,
//                'message' => "Erro interno do servidor",
//                'exception' => $e->getMessage()
//            ]);
//        }

        try {
            $xmlNfce = DB::table('sma_sales')->whereBetween('date', [
                $this->parseDate($request->data_inicial),
                $this->parseDate($request->data_final, true)
            ])
                ->where('estado', '=', 'APROVADO')
                ->where('nfcNumero', '<>', null)
                ->where('pos', '=', 1)->get();

            if (count($xmlNfce) > 0) {

                $zip_file = $public . 'xmlnfce.zip';
                $zip = new \ZipArchive();
                $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

                foreach ($xmlNfce as $x) {
                    if (file_exists($public . 'xml_nfce/' . $x->chave . '.xml'))
                        $zip->addFile($public . 'xml_nfce/' . $x->chave . '.xml', $x->chave . '.xml');
                }
                $zip->close();
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => "Erro interno do servidor",
                'exception' => $e->getMessage()
            ]);
        }

//        $xmlMdfe = Mdfe::
//        whereBetween('updated_at', [
//            $this->parseDate($request->data_inicial),
//            $this->parseDate($request->data_final, true)])
//            ->where('estado', 'APROVADO')
//            ->get();
//
//        if(count($xmlMdfe) > 0){
//            try{
//
//                $zip_file = $public.'xmlmdfe.zip';
//                $zip = new \ZipArchive();
//                $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
//
//                foreach($xmlMdfe as $x){
//                    if(file_exists($public.'xml_mdfe/'.$x->chave. '.xml')){
//                        $zip->addFile($public.'xml_mdfe/'.$x->chave. '.xml', $x->chave. '.xml');
//                    }
//                }
//                $zip->close();
//            }catch(\Exception $e){
//                return response()->json([
//                    'error' => true,
//                    'message' => "Erro interno do servidor"
//                ]);
//            }
//
//        }

        foreach ($xmlNfce as $index => $value) {
            $xmlNfce[$index]["razao_social"] = $this->getClientName($value->customer_id);
        }

        $dataInicial = str_replace("/", "-", $request->data_inicial);
        $dataFinal = str_replace("/", "-", $request->data_final);

        return response()->json([
            'xml' => $xml,
            'xmlNfce' => $xmlNfce,
//            'xmlCte' => $xmlCte,
//            'xmlMdfe' => $xmlMdfe,
            'dataInicial' => $dataInicial,
            'dataFinal' => $dataFinal
        ]);
    }

    private function getClientName($id)
    {
        foreach (DB::table('sma_companies')->where(['id', '=', $id])->get() as $dt) {
            return $dt->name;
        }
    }

    private function parseDate($date, $plusDay = false)
    {
        if ($plusDay == false)
            return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
        else
            return date('Y-m-d', strtotime("+1 day", strtotime(str_replace("/", "-", $date))));
    }

    public function validateCertificate()
    {
        $config = ConfigNota::first();
        $certificado = Certificado::first();

        if ($config == null || $certificado == null) {
            return response()->json([
                "manifest_perm" => false
            ]);
        } else {
            return response()->json([
                "manifest_perm" => true
            ]);
        }
    }

    public function getDocsDFE()
    {
        $data_inicial = date('d/m/Y', strtotime("-90 day", strtotime(date("Y-m-d"))));
        $data_final = date('d/m/Y');

        $docs = ManifestaDfe::orderBy('id', 'desc')->get();
        $arrayDocs = [];
        foreach ($docs as $d) {
            $dIni = str_replace("/", "-", $data_inicial);
            $dFim = str_replace("/", "-", $data_final);

            $dIni = \Carbon\Carbon::parse($dIni)->format('Y-m-d');
            $dFim = \Carbon\Carbon::parse($dFim)->format('Y-m-d');
            $data_dfe = \Carbon\Carbon::parse($d->data_emissao)->format('Y-m-d');

            if (strtotime($data_dfe) >= strtotime($dIni) && strtotime($data_dfe) <= strtotime($dFim)) {
                array_push($arrayDocs, $d);
            }

            $d->data_emissao = \Carbon\Carbon::parse($d->data_emissao)->format('d/m/Y H:i:s');

            if ($d->tipo == 0) {
                $d->estado = "--";
            } else if ($d->tipo == 1) {
                $d->estado = "Ciência";
            } else if ($d->tipo == 2) {
                $d->estado = "Confirmada";
            } else if ($d->tipo == 2) {
                $d->estado = "Desconhecimento";
            } else if ($d->tipo == 2) {
                $d->estado = "Operação não realizada";
            }
        }

        return response()->json([
            'docs' => $arrayDocs,
            'dfeJS' => $arrayDocs,
            'data_final' => $data_final,
            'data_inical' => $data_inicial
        ]);
    }

    function getDocsFilter(Request $request)
    {
        $tipo = $request->tipo;
        $dataInicial = $request->data_inicial;
        $dataFinal = $request->data_final;

        $config = ConfigNota::first();

        if ($config == null) {

            session()->flash('mensagem_sucesso', 'Configure o Emitente');
            return redirect('configNF');
        }

        $certificado = Certificado::first();
        if ($certificado == null) {

            session()->flash('mensagem_erro', 'Configure o Certificado');
            return redirect('configNF');
        }


        $docs = manifestaDfe::orderBy('id', 'desc')->get();
        $arrayDocs = [];

        foreach ($docs as $d) {
            $dIni = str_replace("/", "-", $dataInicial);
            $dFim = str_replace("/", "-", $dataFinal);

            $dIni = \Carbon\Carbon::parse($dIni)->format('Y-m-d');
            $dFim = \Carbon\Carbon::parse($dFim)->format('Y-m-d');
            $data_dfe = \Carbon\Carbon::parse($d->data_emissao)->format('Y-m-d');
            if ($tipo != '--') {
                if (strtotime($data_dfe) >= strtotime($dIni) && strtotime($data_dfe) <= strtotime($dFim)) {
                    if ($d->tipo == $tipo) {
                        array_push($arrayDocs, $d);
                    }
                }
            } else {
                if (strtotime($data_dfe) >= strtotime($dIni) && strtotime($data_dfe) <= strtotime($dFim)) {
                    array_push($arrayDocs, $d);
                }
            }

            $d->data_emissao = \Carbon\Carbon::parse($d->data_emissao)->format('d/m/Y H:i:s');

            if ($d->tipo == 0) {
                $d->estado = "--";
            } else if ($d->tipo == 1) {
                $d->estado = "Ciência";
            } else if ($d->tipo == 2) {
                $d->estado = "Confirmada";
            } else if ($d->tipo == 2) {
                $d->estado = "Desconhecimento";
            } else if ($d->tipo == 2) {
                $d->estado = "Operação não realizada";
            }
        }

        return response()->json([
            'docs' => $arrayDocs,
            'dfeJS' => $arrayDocs,
            'data_final' => $dataFinal,
            'data_inicial' => $dataInicial
        ]);

    }

    private function validaNaoInserido($chave)
    {
        $m = ManifestaDfe::where('chave', $chave)->first();
        if ($m == null) return true;
        else return false;
    }

    public function getNewDocs()
    {
        try {
            $config = ConfigNota::first();

            $cnpj = str_replace(".", "", $config->cnpj);
            $cnpj = str_replace("/", "", $cnpj);
            $cnpj = str_replace("-", "", $cnpj);
            $cnpj = str_replace(" ", "", $cnpj);


            $dfe_service = new DFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => 1,
                "razaosocial" => $config->razao_social,
                "siglaUF" => $config->UF,
                "cnpj" => $cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $config->csc,
                "CSCid" => $config->csc_id
            ], 55);

            $manifesto = ManifestaDfe::orderBy('nsu', 'desc')->first();
            if ($manifesto == null) $nsu = 0;
            else $nsu = $manifesto->nsu;
            $docs = $dfe_service->novaConsulta($nsu);
            $novos = [];
            foreach ($docs as $d) {
                if ($this->validaNaoInserido($d['chave'])) {
                    if ($d['valor'] > 0 && $d['nome']) {
                        ManifestaDfe::create($d);
                        array_push($novos, $d);
                    }
                }
            }

            return response()->json($novos, 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 403);
        }
    }

    private function getFornecedorXML($xml)
    {

        $cidade = Cidade::getCidadeCod($xml->NFe->infNFe->emit->enderEmit->cMun);
        $fornecedor = [
            'cpf' => $xml->NFe->infNFe->emit->CPF ?? '',
            'cnpj' => $xml->NFe->infNFe->emit->CNPJ,
            'razaoSocial' => $xml->NFe->infNFe->emit->xNome,
            'nomeFantasia' => $xml->NFe->infNFe->emit->xFant ?? $xml->NFe->infNFe->emit->xNome,
            'logradouro' => $xml->NFe->infNFe->emit->enderEmit->xLgr,
            'numero' => $xml->NFe->infNFe->emit->enderEmit->nro,
            'bairro' => $xml->NFe->infNFe->emit->enderEmit->xBairro,
            'cep' => $xml->NFe->infNFe->emit->enderEmit->CEP,
            'fone' => $xml->NFe->infNFe->emit->enderEmit->fone,
            'ie' => $xml->NFe->infNFe->emit->IE,
            'cidade' => $xml->NFe->infNFe->emit->enderEmit->xMun,
            'pais' => $xml->NFe->infNFe->emit->enderEmit->xPais
        ];

        $fornecedorEncontrado = $this->verificaFornecedor($xml->NFe->infNFe->emit->CNPJ);

        if ($fornecedorEncontrado) {
            $fornecedor['novo_cadastrado'] = false;
        } else {
            $fornecedor['novo_cadastrado'] = true;
            $this->cadastrarFornecedor($fornecedor);
        }

        return $fornecedor;
    }

    private function cadastrarFornecedor($fornecedor)
    {

        if (!Fornecedor::where('company', $fornecedor['nomeFantasia'])->first()) {
            $result = Fornecedor::create([
                'group_id' => '4',
                'group_name' => 'supplier',
                'name' => $fornecedor['nomeFantasia'],
                'company' => $fornecedor['nomeFantasia'],
                'cep' => $fornecedor['cep'],
                'cpf_cnpj' => $fornecedor['cnpj'],
                'ie_rg' => $fornecedor['ie'],
                'phone' => $this->formataTelefone($fornecedor['fone']),
                'email' => '',
                'city' => $fornecedor['cidade'],
                'vat_no' => $fornecedor['cnpj'],
                'country' => $fornecedor['pais']
            ]);

            return $result->id;
        }

        return Fornecedor::where('company', $fornecedor['nomeFantasia'])->first()->id;
    }

    private function getItensDaNFe($xml)
    {
        $itens = [];
        foreach ($xml->NFe->infNFe->det as $item) {
            $produto = Produto::verificaCadastrado($item->prod->cEAN, $item->prod->xProd, $item->prod->cProd);

            $produtoNovo = !$produto ? true : false;

            $item = [
                'codigo' => $item->prod->cProd,
                'xProd' => $item->prod->xProd,
                'NCM' => $item->prod->NCM,
                'CFOP' => $item->prod->CFOP,
                'uCom' => $item->prod->uCom,
                'vUnCom' => $item->prod->vUnCom,
                'qCom' => $item->prod->qCom,
                'codBarras' => $item->prod->cEAN,
                'produtoNovo' => $produtoNovo,
                'id' => $produtoNovo ? null : $produto->id,
                'produtoId' => $produtoNovo ? '0' : $produto->id,
                'conversao_unitaria' => $produtoNovo ? '' : $produto->conversao_unitaria,
                'cest' => ($item->prod->CEST) ? $item->prod->CEST : 0
            ];

            array_push($itens, $item);
        }

        return $itens;
    }

    private function getInfosDaNFe($xml)
    {
        $chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
        $vFrete = number_format((double)$xml->NFe->infNFe->total->ICMSTot->vFrete,
            2, ",", ".");
        $vDesc = number_format((double)$xml->NFe->infNFe->total->ICMSTot->vDesc, 2, ",", ".");
        return [
            'chave' => $chave,
            'vProd' => $xml->NFe->infNFe->total->ICMSTot->vProd,
            'indPag' => $xml->NFe->infNFe->ide->indPag,
            'nNf' => $xml->NFe->infNFe->ide->nNF,
            'vFrete' => $vFrete,
            'vDesc' => $vDesc
        ];
    }

    private function getFaturaDaNFe($xml)
    {
        if (!empty($xml->NFe->infNFe->cobr->dup)) {
            $fatura = [];
            $cont = 1;
            foreach ($xml->NFe->infNFe->cobr->dup as $dup) {
                $titulo = $dup->nDup;
                $vencimento = $dup->dVenc;
                $vencimento = explode('-', $vencimento);
                $vencimento = $vencimento[2] . "/" . $vencimento[1] . "/" . $vencimento[0];
                $vlr_parcela = number_format((double)$dup->vDup, 2, ",", ".");

                $parcela = [
                    'numero' => $titulo,
                    'vencimento' => $vencimento,
                    'valor_parcela' => $vlr_parcela,
                    'referencia' => $xml->NFe->infNFe->ide->nNF . "/" . $cont
                ];
                array_push($fatura, $parcela);
                $cont++;
            }
            return $fatura;
        }
        return [];
    }

    private function verificaFornecedor($cnpj)
    {
        $forn = Fornecedor::verificaCadastrado($this->formataCnpj($cnpj));
        return $forn;
    }

    private function formataCnpj($cnpj)
    {
        $temp = substr($cnpj, 0, 2);
        $temp .= "." . substr($cnpj, 2, 3);
        $temp .= "." . substr($cnpj, 5, 3);
        $temp .= "/" . substr($cnpj, 8, 4);
        $temp .= "-" . substr($cnpj, 12, 2);
        return $temp;
    }

    private function formataCep($cep)
    {
        $temp = substr($cep, 0, 5);
        $temp .= "-" . substr($cep, 5, 3);
        return $temp;
    }

    private function formataTelefone($fone)
    {
        $temp = substr($fone, 0, 2);
        $temp .= " " . substr($fone, 2, 4);
        $temp .= "-" . substr($fone, 4, 4);
        return $temp;
    }

    public function getDownloadConfigs($chave)
    {
        $config = ConfigNota::first();

        $cnpj = str_replace(".", "", $config->cnpj);
        $cnpj = str_replace("/", "", $cnpj);
        $cnpj = str_replace("-", "", $cnpj);
        $cnpj = str_replace(" ", "", $cnpj);

        $dfe_service = new DFeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => 1,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ], 55);

        try {
            $public = $_SERVER['DOCUMENT_ROOT'] . '/api_fiscal/public/';
            $file = $public . 'xml_dfe/' . $chave . '.xml';

            if (!file_exists($file)) {
                $response = $dfe_service->download($chave);

                $stz = new Standardize($response);
                $std = $stz->toStd();
                if ($std->cStat != 138) {
                    return ['error' => true, 'message' => "Documento não retornado. [$std->cStat] $std->xMotivo" . ", aguarde alguns instantes e atualize a pagina!"];
                }

                $zip = $std->loteDistDFeInt->docZip;
                $xml = gzdecode(base64_decode($zip));
                $public = getenv('SERVIDOR_WEB') ? 'public/' : '';

                file_put_contents($public . 'xml_dfe/' . $chave . '.xml', $xml);
                $nfe = simplexml_load_string($xml);

                if (!$nfe) {
                    return response()->json(["error" => true, 'message' => 'erro ao ler XML.']);
                }

                $fornecedor = $this->getFornecedorXML($nfe);
                $itens = $this->getItensDaNFe($nfe);
                $infos = $this->getInfosDaNFe($nfe);
                $fatura = $this->getFaturaDaNFe($nfe);
            } else {
                $content = file_get_contents($file);
                $nfe = simplexml_load_string($content);

                if (!$nfe) {
                    return response()->json([
                        'error' => true,
                        'message' => "Ocorreu um erro ao ler o XML solicitado!"
                    ]);
                }

                $fornecedor = $this->getFornecedorXML($nfe);
                $itens = $this->getItensDaNFe($nfe);

                $infos = $this->getInfosDaNFe($nfe);
                $fatura = $this->getFaturaDaNFe($nfe);
            }

            $categorias = Categoria::all();
            $unidadesDeMedida = Produto::unidadesMedida();

            $listaCSTCSOSN = Produto::listaCSTCSOSN();
            $listaCST_PIS_COFINS = Produto::listaCST_PIS_COFINS();
            $listaCST_IPI = Produto::listaCST_IPI();
            $config = ConfigNota::first();

            $manifesto = ManifestaDfe::where('chave', $chave)->first();

            $xml = simplexml_load_file($file);
            $objXML = json_encode($xml);
            $objXML = json_decode($objXML);

            $this->criarFatura($objXML);

            if(! DB::table('sma_adjustments')->where('reference_no', $chave)->first()) {
                DB::table('sma_adjustments')->insert([
                    'date' => date('Y-m-d H:i:s', strtotime('now')),
                    'warehouse_id' => 1,
                    'created_by' => 1,
                    'reference_no' => $chave
                ]);
            }

            $latest = DB::table('sma_adjustments')->where('reference_no', $chave)->first();

            $products = $objXML->NFe->infNFe->det;
            if(! is_array($products)) {
                if($dbProduct = DB::table('sma_products')->where('name', $products->prod->xProd)->first()) {
                    $data = [
                        'latest_id' => $latest->id,
                        'product_id' => $dbProduct->id,
                        'quantity' => $products->prod->qCom,
                        'cost' => $dbProduct->cost,
                        'product_code' => $dbProduct->code,
                        'product_name' => $dbProduct->name,
                        'net_unit_cost' => $dbProduct->cost,
                        'warehouse_id' => 1,
                        'subtotal' => $dbProduct->cost,
                        'quantity_balance' => $dbProduct->quantity,
                        'date' => date('Y-m-d', strtotime('now')),
                        'status' => 'received',
                        'unit_cost' => $dbProduct->cost,
                        'real_unit_cost' => $dbProduct->cost,
                        'quantity_received' => $dbProduct->quantity ?? 0,
                        'unit_quantity' => $dbProduct->quantity ?? 0,
                        'unit' => $dbProduct->unit
                    ];
                    $this->createAdjustment($data);
                }
            } else {
                foreach($products as $product) {
                    if($dbProduct = DB::table('sma_products')->where('name', $product->prod->xProd)->first()) {
                        $data = [
                            'latest_id' => $latest->id,
                            'product_id' => $dbProduct->id,
                            'quantity' => $product->prod->qCom,
                            'cost' => $dbProduct->cost,
                            'product_code' => $dbProduct->code,
                            'product_name' => $dbProduct->name,
                            'net_unit_cost' => $dbProduct->cost,
                            'warehouse_id' => 1,
                            'subtotal' => $dbProduct->cost,
                            'quantity_balance' => $dbProduct->quantity,
                            'date' => date('Y-m-d', strtotime('now')),
                            'status' => 'received',
                            'unit_cost' => $dbProduct->cost,
                            'real_unit_cost' => $dbProduct->cost,
                            'quantity_received' => $dbProduct->quantity ?? 0,
                            'unit_quantity' => $dbProduct->quantity ?? 0,
                            'unit' => $dbProduct->unit
                        ];
                        $this->createAdjustment($data);
                    }
                }
            }

            return response()->json([
                'fornecedor' => $fornecedor,
                'itens' => $itens,
                'infos' => $infos,
                'dfeJS' => true,
                'fatura' => $fatura,
                'listaCSTCSOSN' => $listaCSTCSOSN,
                'listaCST_PIS_COFINS' => $listaCST_PIS_COFINS,
                'listaCST_IPI' => $listaCST_IPI,
                'categorias' => $categorias,
                'config' => $config,
                'fatura_salva' => $manifesto == null ? false : $manifesto->fatura_salva,
                'unidadesDeMedida' => $unidadesDeMedida,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => "Erro interno",
                'exception' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getTrace()
            ]);
        }
    }


    public function getFiscalSettings()
    {
        return response()->json([
            "listaCSTCSOSN" => ConfigNota::listaCST(),
            "listaCSTPISCOFINS" => ConfigNota::listaCST_PIS_COFINS(),
            "listaCSTIPI" => ConfigNota::listaCST_IPI(),
            "unidadesDeMedida" => Produto::unidadesMedida(),
            "anps" => Produto::lista_ANP()
        ]);
    }

    private function verificaAnterior($chave)
    {
        return ManifestaDfe::where('chave', $chave)->first();
    }

    public function manifest(Request $request)
    {

        $config = ConfigNota::first();

        $cnpj = str_replace(".", "", $config->cnpj);
        $cnpj = str_replace("/", "", $cnpj);
        $cnpj = str_replace("-", "", $cnpj);
        $cnpj = str_replace(" ", "", $cnpj);

        $dfe_service = new DFeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => 1,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ], 55);
        $evento = $request->evento;

        $manifestaAnterior = $this->verificaAnterior($request->chave);

        if ($evento == 1) {
            $res = $dfe_service->manifesta($request->chave,$manifestaAnterior != null ? ($manifestaAnterior->sequencia_evento + 1) : 1);
        } else if ($evento == 2) {
            $res = $dfe_service->confirmacao($request->chave,
                $manifestaAnterior != null ? ($manifestaAnterior->sequencia_evento + 1) : 1);
        } else if ($evento == 3) {
            $res = $dfe_service->desconhecimento($request->chave,
                $manifestaAnterior != null ? ($manifestaAnterior->sequencia_evento + 1) : 1, $request->justificativa);
        } else if ($evento == 4) {
            $res = $dfe_service->operacaoNaoRealizada($request->chave,
                $manifestaAnterior != null ? ($manifestaAnterior->sequencia_evento + 1) : 1, $request->justificativa);
        }

        if ($res['retEvento']['infEvento']['cStat'] == '135') {

            $manifesto = ManifestaDfe::where('chave', $request->chave)
                ->first();
            $manifesto->tipo = $evento;
            $manifesto->save();

            return response()->json([
                'error' => false,
                'message' => 'Manifestado com sucesso'
            ]);
        } else {
            $manifesto = ManifestaDfe::where('chave', $request->chave)
                ->first();
            $manifesto->tipo = $evento;
            $manifesto->save();

            return response()->json([
                'error' => true,
                'message' => 'Já esta manifestado a chave ' . $request->chave
            ]);
        }

    }

    public function gerarNFe(Request $request)
    {

        $config = ConfigNota::first(); // iniciando os dados do emitente NF
        $tributacao = Tributacao::first(); // iniciando tributos

        $nfe = new Make();
        $stdInNFe = new \stdClass();
        $stdInNFe->versao = '4.00';
        $stdInNFe->Id = null;
        $stdInNFe->pk_nItem = '';

        $infNFe = $nfe->taginfNFe($stdInNFe);

        $vendaLast = $request->lastId;
        $lastNumero = $vendaLast;

        $stdIde = new \stdClass();
        $stdIde->cUF = $config->cUF;
        $stdIde->cNF = rand(11111, 99999);
        // $stdIde->natOp = $venda->natureza->natureza;
        $stdIde->natOp = $request->natureza->natureza;

        // $stdIde->indPag = 1; //NÃO EXISTE MAIS NA VERSÃO 4.00 // forma de pagamento

        $stdIde->mod = 55;
        $stdIde->serie = $config->numero_serie_nfe;
        $stdIde->nNF = (int)$lastNumero + 1;
        $stdIde->dhEmi = date("Y-m-d\TH:i:sP");
        $stdIde->dhSaiEnt = date("Y-m-d\TH:i:sP");
        $stdIde->tpNF = 1;
        $stdIde->idDest = $config->UF != $request->cliente->cidade->uf ? 2 : 1;
        $stdIde->cMunFG = $config->codMun;
        $stdIde->tpImp = 1;
        $stdIde->tpEmis = 1;
        $stdIde->cDV = 0;
        $stdIde->tpAmb = $config->ambiente;
        $stdIde->finNFe = 1;
        $stdIde->indFinal = $request->cliente->consumidor_final;
        $stdIde->indPres = 1;
        $stdIde->procEmi = '0';
        $stdIde->verProc = '2.0';
        // $stdIde->dhCont = null;
        // $stdIde->xJust = null;


        //
        $tagide = $nfe->tagide($stdIde);

        $stdEmit = new \stdClass();
        $stdEmit->xNome = $config->razao_social;
        $stdEmit->xFant = $config->nome_fantasia;

        $ie = str_replace(".", "", $config->ie);
        $ie = str_replace("/", "", $ie);
        $ie = str_replace("-", "", $ie);
        $stdEmit->IE = $ie;
        $stdEmit->CRT = $tributacao->regime == 0 ? 1 : 3;

        $cnpj = str_replace(".", "", $config->cnpj);
        $cnpj = str_replace("/", "", $cnpj);
        $cnpj = str_replace("-", "", $cnpj);
        $stdEmit->CNPJ = $cnpj;
        // $stdEmit->IM = $ie;

        $emit = $nfe->tagemit($stdEmit);

        // ENDERECO EMITENTE
        $stdEnderEmit = new \stdClass();
        $stdEnderEmit->xLgr = $config->logradouro;
        $stdEnderEmit->nro = $config->numero;
        $stdEnderEmit->xCpl = "";

        $stdEnderEmit->xBairro = $config->bairro;
        $stdEnderEmit->cMun = $config->codMun;
        $stdEnderEmit->xMun = $config->municipio;
        $stdEnderEmit->UF = $config->UF;

        $cep = str_replace("-", "", $config->cep);
        $cep = str_replace(".", "", $cep);
        $stdEnderEmit->CEP = $cep;
        $stdEnderEmit->cPais = $config->codPais;
        $stdEnderEmit->xPais = $config->pais;

        $enderEmit = $nfe->tagenderEmit($stdEnderEmit);

        // DESTINATARIO
        $stdDest = new \stdClass();
        $stdDest->xNome = $request->cliente->razao_social;

        if ($request->cliente->contribuinte) {
            if ($request->cliente->ie_rg == 'ISENTO') {
                $stdDest->indIEDest = "2";
            } else {
                $stdDest->indIEDest = "1";
            }

        } else {
            $stdDest->indIEDest = "9";
        }


        $cnpj_cpf = str_replace(".", "", $request->cliente->cpf_cnpj);
        $cnpj_cpf = str_replace("/", "", $cnpj_cpf);
        $cnpj_cpf = str_replace("-", "", $cnpj_cpf);

        if (strlen($cnpj_cpf) == 14) {
            $stdDest->CNPJ = $cnpj_cpf;
            $ie = str_replace(".", "", $request->cliente->ie_rg);
            $ie = str_replace("/", "", $ie);
            $ie = str_replace("-", "", $ie);
            $stdDest->IE = $ie;
        } else {
            $stdDest->CPF = $cnpj_cpf;
        }

        $dest = $nfe->tagdest($stdDest);

        $stdEnderDest = new \stdClass();
        $stdEnderDest->xLgr = $request->cliente->rua;
        $stdEnderDest->nro = $request->cliente->numero;
        $stdEnderDest->xCpl = "";
        $stdEnderDest->xBairro = $request->cliente->bairro;
        $stdEnderDest->cMun = $request->cliente->cidade->codigo;
        $stdEnderDest->xMun = strtoupper($request->cliente->cidade->nome);
        $stdEnderDest->UF = $request->cliente->cidade->uf;

        $cep = str_replace("-", "", $request->cliente->cep);
        $cep = str_replace(".", "", $cep);
        $stdEnderDest->CEP = $cep;
        $stdEnderDest->cPais = "1058";
        $stdEnderDest->xPais = "BRASIL";

        $enderDest = $nfe->tagenderDest($stdEnderDest);

        $somaProdutos = 0;
        $somaICMS = 0;
        $somaIPI = 0;
        //PRODUTOS
        $itemCont = 0;

        $totalItens = count($request->produtos);
        $somaFrete = 0;
        $somaDesconto = 0;
        $somaISS = 0;
        $somaServico = 0;

        $VBC = 0;
        foreach ($request->produtos as $produto) {
            $itemCont++;

            $stdProd = new \stdClass();
            $stdProd->item = $itemCont;
            $stdProd->cEAN = $produto->codBarras;
            $stdProd->cEANTrib = $produto->codBarras;
            $stdProd->cProd = $produto->id;
            $stdProd->xProd = $produto->nome;
            $ncm = $produto->NCM;
            $ncm = str_replace(".", "", $ncm);
            if ($produto->CST_CSOSN == '500' || $produto->CST_CSOSN == '60') {
                $stdProd->cBenef = 'SEM CBENEF';
            }

            if ($produto->perc_iss > 0) {
                $stdProd->NCM = '00';
            } else {
                $stdProd->NCM = $ncm;
            }

            $stdProd->CFOP = $config->UF != $request->cliente->cidade->uf ?
                $produto->CFOP_saida_inter_estadual : $produto->CFOP_saida_estadual;

            $stdProd->uCom = $produto->unidade_venda;
            $stdProd->qCom = $produto->quantidade;
            $stdProd->vUnCom = $this->format($produto->valor);
            $stdProd->vProd = $this->format(($produto->quantidade * $produto->valor));
            $stdProd->uTrib = $produto->unidade_venda;
            $stdProd->qTrib = $produto->quantidade;
            $stdProd->vUnTrib = $this->format($produto->valor);
            $stdProd->indTot = $produto->perc_iss > 0 ? 0 : 1;
            $somaProdutos += $stdProd->vProd;

            $vDesc = 0;

            if ($request->desconto > 0) {
                if ($itemCont < sizeof($request->produto)) {
                    $totalVenda = $request->valor_total;

                    $media = (((($stdProd->vProd - $totalVenda) / $totalVenda)) * 100);
                    $media = 100 - ($media * -1);

                    $tempDesc = ($request->desconto * $media) / 100;
                    $somaDesconto += $tempDesc;

                    $stdProd->vDesc = $this->format($tempDesc);
                } else {
                    $stdProd->vDesc = $this->format($request->desconto - $somaDesconto);
                }
            }

            if ($request->frete) {
                if ($request->frete->valor > 0) {
                    $somaFrete += $vFt = $request->frete->valor / $totalItens;
                    $stdProd->vFrete = $this->format($vFt);
                }
            }

            $prod = $nfe->tagprod($stdProd);

            //TAG IMPOSTO

            $stdImposto = new \stdClass();
            $stdImposto->item = $itemCont;
            if ($produto->perc_iss > 0) {
                $stdImposto->vTotTrib = 0.00;
            }

            $imposto = $nfe->tagimposto($stdImposto);

            // ICMS
            if ($produto->perc_iss == 0) {
                // regime normal
                if ($tributacao->regime == 1) {

                    //$venda->produto->CST  CST

                    $stdICMS = new \stdClass();
                    $stdICMS->item = $itemCont;
                    $stdICMS->orig = 0;
                    $stdICMS->CST = $produto->CST_CSOSN;
                    $stdICMS->modBC = 0;
                    $stdICMS->vBC = $stdProd->vProd;
                    $stdICMS->pICMS = $this->format($produto->perc_icms);
                    $stdICMS->vICMS = $stdICMS->vBC * ($stdICMS->pICMS / 100);

                    if ($produto->CST_CSOSN == '500' || $produto->CST_CSOSN == '60') {
                        $stdICMS->pRedBCEfet = 0.00;
                        $stdICMS->vBCEfet = 0.00;
                        $stdICMS->pICMSEfet = 0.00;
                        $stdICMS->vICMSEfet = 0.00;
                    } else {
                        $VBC += $stdProd->vProd;
                    }

                    $somaICMS += (($produto->valor * $produto->quantidade)
                        * ($stdICMS->pICMS / 100));
                    $ICMS = $nfe->tagICMS($stdICMS);
                    // regime simples
                } else {

                    //$venda->produto->CST CSOSN

                    $stdICMS = new \stdClass();

                    $stdICMS->item = $itemCont;
                    $stdICMS->orig = 0;
                    $stdICMS->CSOSN = $produto->CST_CSOSN;

                    if ($produto->CST_CSOSN == '500') {
                        $stdICMS->vBCSTRet = 0.00;
                        $stdICMS->pST = 0.00;
                        $stdICMS->vICMSSTRet = 0.00;
                    }

                    $stdICMS->pCredSN = $this->format($produto->perc_icms);
                    $stdICMS->vCredICMSSN = $this->format($produto->perc_icms);
                    $ICMS = $nfe->tagICMSSN($stdICMS);

                    $somaICMS = 0;
                }
            } else {
                $valorIss = ($produto->valor * $produto->quantidade) - $vDesc;
                $somaServico += $valorIss;
                $valorIss = $valorIss * ($produto->perc_iss / 100);
                $somaISS += $valorIss;


                $std = new \stdClass();
                $std->item = $itemCont;
                $std->vBC = $stdProd->vProd;
                $std->vAliq = $produto->perc_iss;
                $std->vISSQN = $this->format($valorIss);
                $std->cMunFG = $config->codMun;
                $std->cListServ = $produto->cListServ;
                $std->indISS = 1;
                $std->indIncentivo = 1;

                $nfe->tagISSQN($std);
            }

            //PIS
            $stdPIS = new \stdClass();
            $stdPIS->item = $itemCont;
            $stdPIS->CST = $produto->CST_PIS;
            $stdPIS->vBC = $this->format($produto->perc_pis) > 0 ? $stdProd->vProd : 0.00;
            $stdPIS->pPIS = $this->format($produto->perc_pis);
            $stdPIS->vPIS = $this->format(($stdProd->vProd * $produto->quantidade) *
                ($produto->perc_pis / 100));
            $PIS = $nfe->tagPIS($stdPIS);

            //COFINS
            $stdCOFINS = new \stdClass();
            $stdCOFINS->item = $itemCont;
            $stdCOFINS->CST = $produto->CST_COFINS;
            $stdCOFINS->vBC = $this->format($produto->perc_cofins) > 0 ? $stdProd->vProd : 0.00;
            // $stdCOFINS->qBCProd = '0.00';
            // $stdCOFINS->vAliqProd = '0.00';

            $stdCOFINS->pCOFINS = $this->format($produto->perc_cofins);
            $stdCOFINS->vCOFINS = $this->format(($stdProd->vProd * $produto->quantidade) *
                ($produto->perc_cofins / 100));
            $COFINS = $nfe->tagCOFINS($stdCOFINS);


            //IPI

            $std = new \stdClass();
            $std->item = $itemCont;
            //999 – para tributação normal IPI
            $std->cEnq = '999';
            $std->CST = $produto->CST_IPI;
            $std->vBC = $this->format($produto->perc_ipi) > 0 ? $stdProd->vProd : 0.00;
            $std->pIPI = $this->format($produto->perc_ipi);
            $somaIPI += $std->vIPI = $stdProd->vProd * $this->format(($produto->perc_ipi / 100));

            $nfe->tagIPI($std);


            //TAG ANP
            if (strlen($produto->descricao_anp) > 5) {
                $stdComb = new \stdClass();
                $stdComb->item = $itemCont;
                $stdComb->cProdANP = $produto->codigo_anp;
                $stdComb->descANP = $produto->descricao_anp;
                $stdComb->UFCons = $request->cliente->cidade->uf;

                $nfe->tagcomb($stdComb);
            }


            $cest = $produto->CEST;
            $cest = str_replace(".", "", $cest);
            $stdProd->CEST = $cest;


            if (strlen($cest) > 0) {
                $std = new \stdClass();
                $std->item = $itemCont;
                $std->CEST = $cest;
                $nfe->tagCEST($std);
            }
        }


        $stdICMSTot = new \stdClass();
        $stdICMSTot->vProd = $this->format($somaProdutos);
        $stdICMSTot->vBC = $this->format($VBC);
        $stdICMSTot->vICMS = $this->format($somaICMS);
        $stdICMSTot->vICMSDeson = 0.00;
        $stdICMSTot->vBCST = 0.00;
        $stdICMSTot->vST = 0.00;

        if ($request->frete) $stdICMSTot->vFrete = $this->format($request->frete->valor);
        else $stdICMSTot->vFrete = 0.00;

        $stdICMSTot->vSeg = 0.00;
        $stdICMSTot->vDesc = $this->format($request->desconto);
        $stdICMSTot->vII = 0.00;
        $stdICMSTot->vIPI = 0.00;
        $stdICMSTot->vPIS = 0.00;
        $stdICMSTot->vCOFINS = 0.00;
        $stdICMSTot->vOutro = 0.00;

        if ($request->frete) {
            $stdICMSTot->vNF =
                $this->format(($somaProdutos + $request->frete->valor + $somaIPI) - $request->desconto);
        } else $stdICMSTot->vNF = $this->format($somaProdutos + $somaIPI - $request->desconto);

        $stdICMSTot->vTotTrib = 0.00;
        $ICMSTot = $nfe->tagICMSTot($stdICMSTot);

        //inicio totalizao issqn

        if ($somaISS > 0) {
            $std = new \stdClass();
            $std->vServ = $this->format($somaServico + $request->desconto);
            $std->vBC = $this->format($somaServico);
            $std->vISS = $this->format($somaISS);
            $std->dCompet = date('Y-m-d');

            $std->cRegTrib = 6;

            $nfe->tagISSQNTot($std);
        }

        //fim totalizao issqn


        $stdTransp = new \stdClass();
        $stdTransp->modFrete = $request->frete->tipo ?? '9';

        $transp = $nfe->tagtransp($stdTransp);


        if ($request->transportadora) {
            $std = new \stdClass();
            $std->xNome = $request->transportadora->razao_social;

            $std->xEnder = $request->transportadora->logradouro;
            $std->xMun = strtoupper($request->transportadora->cidade->nome);
            $std->UF = $request->transportadora->cidade->uf;


            $cnpj_cpf = $request->transportadora->cnpj_cpf;
            $cnpj_cpf = str_replace(".", "", $request->transportadora->cnpj_cpf);
            $cnpj_cpf = str_replace("/", "", $cnpj_cpf);
            $cnpj_cpf = str_replace("-", "", $cnpj_cpf);

            if (strlen($cnpj_cpf) == 14) $std->CNPJ = $cnpj_cpf;
            else $std->CPF = $cnpj_cpf;

            $nfe->tagtransporta($std);
        }


        if ($request->frete != null) {

            $std = new \stdClass();


            $placa = str_replace("-", "", $request->frete->placa);
            $std->placa = strtoupper($placa);
            $std->UF = $request->frete->uf;

            if ($config->UF == $request->cliente->cidade->uf) {
                $nfe->tagveicTransp($std);
            }


            if ($request->frete->qtdVolumes > 0 && $request->frete->peso_liquido > 0
                && $request->frete->peso_bruto > 0) {
                $stdVol = new \stdClass();
                $stdVol->item = 1;
                $stdVol->qVol = $request->frete->qtdVolumes;
                $stdVol->esp = $request->frete->especie;

                $stdVol->nVol = $request->frete->numeracaoVolumes;
                $stdVol->pesoL = $request->frete->peso_liquido;
                $stdVol->pesoB = $request->frete->peso_bruto;
                $vol = $nfe->tagvol($stdVol);
            }
        }


        $std = new \stdClass();
        $std->CNPJ = getenv('RESP_CNPJ'); //CNPJ da pessoa jurídica responsável pelo sistema utilizado na emissão do documento fiscal eletrônico
        $std->xContato = getenv('RESP_NOME'); //Nome da pessoa a ser contatada
        $std->email = getenv('RESP_EMAIL'); //E-mail da pessoa jurídica a ser contatada
        $std->fone = getenv('RESP_FONE'); //Telefone da pessoa jurídica/física a ser contatada
        $nfe->taginfRespTec($std);


        //Fatura
        if ($somaISS == 0 && $request->natureza->CFOP_saida_estadual != '5915' && $request->natureza->CFOP_saida_inter_estadual != '6915') {
            $stdFat = new \stdClass();
            $stdFat->nFat = (int)$lastNumero + 1;
            $stdFat->vOrig = $this->format($somaProdutos);
            $stdFat->vDesc = $this->format($request->desconto);
            $stdFat->vLiq = $this->format($somaProdutos - $request->desconto);

            $fatura = $nfe->tagfat($stdFat);
        }


        $stdPag = new \stdClass();
        $pag = $nfe->tagpag($stdPag);

        $stdDetPag = new \stdClass();


        $stdDetPag->tPag = $request->tipo_pagamento;
        $stdDetPag->vPag = $request->tipo_pagamento != '90' ? $this->format($somaProdutos - $request->desconto) : 0.00;

        if ($request->tipo_pagamento == '03' || $request->tipo_pagamento == '04') {
            $stdDetPag->CNPJ = '12345678901234';
            $stdDetPag->tBand = '01';
            $stdDetPag->cAut = '3333333';
            $stdDetPag->tpIntegra = 1;
        }
        $stdDetPag->indPag = $request->forma_pagamento == 'a_vista' ? 0 : 1;

        $detPag = $nfe->tagdetPag($stdDetPag);


        $stdInfoAdic = new \stdClass();
        $stdInfoAdic->infCpl = $request->observacao;

        $infoAdic = $nfe->taginfAdic($stdInfoAdic);


        $std = new \stdClass();
        $std->CNPJ = getenv('RESP_CNPJ'); //CNPJ da pessoa jurídica responsável pelo sistema utilizado na emissão do documento fiscal eletrônico
        $std->xContato = getenv('RESP_NOME'); //Nome da pessoa a ser contatada
        $std->email = getenv('RESP_EMAIL'); //E-mail da pessoa jurídica a ser contatada
        $std->fone = getenv('RESP_FONE'); //Telefone da pessoa jurídica/física a ser contatada
        $nfe->taginfRespTec($std);

        if (getenv("AUTXML")) {
            $std = new \stdClass();
            $std->CNPJ = getenv("AUTXML");
            $std->CPF = null;
            $nfe->tagautXML($std);
        }

        try {
            $nfe->montaNFe();
            $arr = [
                'chave' => $nfe->getChave(),
                'xml' => $nfe->getXML(),
                'nNf' => $stdIde->nNF
            ];
            return $arr;
        } catch (\Exception $e) {
            return [
                'erros_xml' => $nfe->getErrors()
            ];
        }
    }

    private function format($number, $dec = 2)
    {
        return number_format((float)$number, $dec, ".", "");
    }

    public function getPaymentOpt()
    {
        return ConfigNota::tiposPagamento();
    }

    private function sign($xml)
    {
        return $this->tools->signNFe($xml);
    }

    public function transmitirNfce($signXml, $chave)
    {
        try {
            $idLote = str_pad(100, 15, '0', STR_PAD_LEFT);
            $resp = $this->tools->sefazEnviaLote([$signXml], $idLote);
            sleep(3);
            $st = new Standardize();
            $std = $st->toStd($resp);

            if ($std->cStat != 103) {

                return "[$std->cStat] - $std->xMotivo";
            }
            sleep(1);
            $recibo = $std->infRec->nRec;
            $protocolo = $this->tools->sefazConsultaRecibo($recibo);
            sleep(1);

            $public = getenv('SERVIDOR_WEB') ? 'public/' : '';
            try {
                $xml = Complements::toAuthorize($signXml, $protocolo);
                header('Content-type: text/xml; charset=UTF-8');
                file_put_contents($public . 'xml_nfce/' . $chave . '.xml', $xml);
                return $recibo;
            } catch (\Exception $e) {
                return "Erro: " . $st->toJson($protocolo);
            }

        } catch (\Exception $e) {
            return "Erro: " . $e->getMessage();
        }

    }

    public function generateDanfe(Request $request)
    {

        $request->produtos = json_decode(json_encode($request->produtos));
        $request->frete = json_decode(json_encode($request->frete));
        $request->cliente = json_decode(json_encode($request->cliente));
        $request->natureza = json_decode(json_encode($request->natureza));

        $nfe = $this->gerarNFe($request);

        if (!isset($nfe['erros_xml'])) {
            $xml = $nfe['xml'];

            $public = getenv('SERVIDOR_WEB') ? 'public/' : '';
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($public . 'imgs/logo.jpg'));

            try {

                $danfe = new Danfe($xml);
                $id = $danfe->monta();
                $pdf = $danfe->render();
                header('Content-Type: application/pdf');
                return response($pdf)->header('Content-Type', 'application/pdf');
            } catch (\InvalidArgumentException $e) {
                return response()->json(['error' => true, 'message' => "Ocorreu um erro durante o processamento :" . $e->getMessage()]);
            }
        } else {
            return response()->json(['error' => true, 'xml' => $nfe['erros_xml']]);
        }
    }

    public function getInfoNfe(Request $request)
    {

        $request->produtos = json_decode(json_encode($request->produtos));
        $request->frete = json_decode(json_encode($request->frete));
        $request->cliente = json_decode(json_encode($request->cliente));
        $request->natureza = json_decode(json_encode($request->natureza));

        return $this->gerarNFe($request);
    }

    public function gerarNf(Request $request)
    {
        try {
            $config = ConfigNota::first();

            $cnpj = str_replace(".", "", $config->cnpj);
            $cnpj = str_replace("/", "", $cnpj);
            $cnpj = str_replace("-", "", $cnpj);
            $cnpj = str_replace(" ", "", $cnpj);

            $nfe_service = new NFService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$config->ambiente,
                "razaosocial" => $config->razao_social,
                "siglaUF" => $config->UF,
                "cnpj" => $cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $config->csc,
                "CSCid" => $config->csc_id
            ]);
            if ($request->estado == 'REJEITADO' || $request->estado == 'DISPONIVEL') {

                header('Content-type: text/html; charset=UTF-8');

                $request->produtos = json_decode(json_encode($request->produtos));
                $request->frete = json_decode(json_encode($request->frete));
                $request->cliente = json_decode(json_encode($request->cliente));
                $request->natureza = json_decode(json_encode($request->natureza));

                $nfe = $this->gerarNFe($request);

                if (!isset($nfe['erros_xml'])) {

                    $signed = $nfe_service->sign($nfe['xml']);
                    $resultado = $nfe_service->transmitir($signed, $nfe['chave']);

                    if (substr($resultado, 0, 4) != 'Erro') {

                        $data = [
                            'error' => false,
                            'chave' => $nfe['chave'],
                            'estado' => 'APROVADO',
                            'nfNumero' => $nfe['nNf']
                        ];
                    } else {

                        $data = [
                            'error' => true,
                            'estado' => 'REJEITADO'
                        ];
                    }

                    return response()->json([
                        'data' => $data,
                        'lastId' => $request->lastId,
                        'nf' => $resultado
                    ]);

                } else {

                    return response()->json([
                        'error' => true,
                        'xml_erros' => $nfe['erros_xml'],
                        'lastId' => $request->lastId
                    ]);
                }

            } else {
                echo json_encode("Apro");
            }
        } catch (\Exception $ex) {
            return response()->json([
               'exception' => $ex->getMessage(),
               'line' => $ex->getLine(),
               'file' => $ex->getFile()
            ]);
        }
    }

    public function consultNfe(Request $request)
    {
        if (isset($request->chave)) {
            $config = ConfigNota::first();

            $cnpj = str_replace(".", "", $config->cnpj);
            $cnpj = str_replace("/", "", $cnpj);
            $cnpj = str_replace("-", "", $cnpj);
            $cnpj = str_replace(" ", "", $cnpj);
            $nfe_service = new NFService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$config->ambiente,
                "razaosocial" => $config->razao_social,
                "siglaUF" => $config->UF,
                "cnpj" => $cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $config->csc,
                "CSCid" => $config->csc_id
            ]);

            if ($nfe_service->consultarPorChave($request->chave)) {
                $dt = $nfe_service->consultarPorChave($request->chave);
                echo json_encode($dt);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Chave: ' . $request->chave . ' inválida.'
                ]);
            }

        } else {
            return response()->json([
                'error' => true,
                'message' => 'Chave vazia ou inválida.'
            ]);
        }
    }

    public function cancelNfe(Request $request)
    {

        $config = ConfigNota::first();

        $cnpj = str_replace(".", "", $config->cnpj);
        $cnpj = str_replace("/", "", $cnpj);
        $cnpj = str_replace("-", "", $cnpj);
        $cnpj = str_replace(" ", "", $cnpj);

        $nfe_service = new NFService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ]);


        $nfe = $nfe_service->cancelarPelaChave($request->chave, $request->justificativa);

        if (!isset($nfe['erro'])) {

            return response()->json(['error' => false, 'nfe' => $nfe], 200);
        } else {

            return response()->json(['error' => false, 'nfe_data' => $nfe['data']]);
        }

    }

    public function fixNfe(Request $request)
    {

        $config = ConfigNota::first();

        $cnpj = str_replace(".", "", $config->cnpj);
        $cnpj = str_replace("/", "", $cnpj);
        $cnpj = str_replace("-", "", $cnpj);
        $cnpj = str_replace(" ", "", $cnpj);

        $nfe_service = new NFService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ]);

        $nfe = $nfe_service->cartaCorrecaoPorRequest($request);

        return response()->json($nfe);
    }

    private function getEmitente()
    {
        $config = ConfigNota::first();

        return [
            'razao' => $config->razao_social,
            'logradouro' => $config->logradouro,
            'numero' => $config->numero,
            'complemento' => '',
            'bairro' => $config->bairro,
            'CEP' => $config->cep,
            'municipio' => $config->municipio,
            'UF' => $config->UF,
            'telefone' => $config->telefone,
            'email' => ''
        ];
    }

    public function printCce(Request $request)
    {
        if ($request->sequencia_cce > 0) {

            $public = getenv('SERVIDOR_WEB') ? 'public/' : '';
            if (file_exists($public . 'xml_nfe_correcao/' . $request->chave . '.xml')) {
                $xml = file_get_contents($public . 'xml_nfe_correcao/' . $request->chave . '.xml');
                $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($public . 'imgs/logo.jpg'));

                $dadosEmitente = $this->getEmitente();

                try {
                    $daevento = new Daevento($xml, $dadosEmitente);
                    $daevento->debugMode(true);
                    $pdf = $daevento->render($logo);

                    return response($pdf)->header('Content-Type', 'application/pdf');
                } catch (\InvalidArgumentException $e) {
                    return response()->json([
                        'error' => true,
                        'message' => "Ocorreu um erro durante o processamento :" . $e->getMessage()
                    ]);
                }
            } else {
                return response()->json([
                    'error' => true,
                    'message' => "Arquivo XML não encontrado!!"
                ]);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Este documento não possui evento de correção!'
            ]);
        }
    }

    public function downloadXml(Request $request)
    {
        if ($request->chave) {
            $public = getenv('SERVIDOR_WEB') ? 'public/' : '';
            if (file_exists($public . 'xml_nfe/' . $request->chave . '.xml')) {

                return response()->download($public . 'xml_nfe/' . $request->chave . '.xml');
            } else {
                return response()->json([
                    'error' => true,
                    'message' => "Arquivo XML não encontrado!!"
                ]);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Selecione uma venda para baixar o XML'
            ]);
        }
    }

    public function printCancel(Request $request)
    {
        $request->all();

        if ($request->estado == 'CANCELADA') {
            try {
                $public = getenv('SERVIDOR_WEB') ? 'public/' : '';
                if (file_exists($public . 'xml_nfe_cancelada/' . $request->chave . '.xml')) {
                    $xml = file_get_contents($public . 'xml_nfe_cancelada/' . $request->chave . '.xml');

                    $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($public . 'imgs/logo.jpg'));

                    $dadosEmitente = $this->getEmitente();

                    $daevento = new Daevento($xml, $dadosEmitente);
                    $daevento->debugMode(true);
                    $pdf = $daevento->render($logo);

                    return response($pdf)->header('Content-Type', 'application/pdf');
                } else {
                    return response()->json([
                        'error' => true,
                        'message' => "Arquivo XML não encontrado!!",
                    ]);
                }
            } catch (\InvalidArgumentException $e) {
                return response()->json([
                    'error' => true,
                    'message' => "Ocorreu um erro durante o processamento :" . $e->getMessage(),
                ]);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => "Este documento não possui evento de cancelamento!",
            ]);
        }
    }

    public function disableNfe(Request $request)
    {
        $config = ConfigNota::first();

        $cnpj = str_replace(".", "", $config->cnpj);
        $cnpj = str_replace("/", "", $cnpj);
        $cnpj = str_replace("-", "", $cnpj);
        $cnpj = str_replace(" ", "", $cnpj);


        $nfe_service = new NFService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ]);

        $result = $nfe_service->inutilizarPelaRequest($request->nInicio, $request->nFinal, $request->justificativa);

        return json_encode($result);
    }

    public function sendNfeXml(Request $request)
    {
        if (!empty($request->sales_to_pdf)) {

            $this->deleteLastZip();

            $public = getenv('SERVIDOR_WEB') ? 'public/' : '';

            $zip = new ZipArchive();
            $filename = $public . 'pdf/zip/DANFES_E_XML_PARA_EMAIL.zip';

            if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
                exit("cannot open <$filename>\n");
            }

            foreach ($request->sales_to_pdf as $id => $sale) {
                $id++;

                if (!isset($sale['chave'])) {

                    return response()->json([
                        'error' => true,
                        'message' => 'Uma das vendas selecionadas não possui XML para ser baixado. É necessário enviar a venda para gerar o XML'
                    ]);
                } else {
                    $this->criarPdfParaEnvio($sale['chave']);

                    $zip->addFile($public . 'pdf/DANFE_' . $sale['chave'] . '.pdf', "xml_e_pdf_" . $id . "/DANFE_" . $sale['chave'] . ".pdf");
                    $zip->addFile($public . 'xml_nfe/' . $sale['chave'] . '.xml', "xml_e_pdf_" . $id . "/XML_" . $sale['chave'] . ".xml");
                }
            }

            $zip->close();

            return response()->download($public . 'pdf/zip/DANFES_E_XML_PARA_EMAIL.zip');

        } else {
            return response()->json([
                'error' => true,
                'message' => 'Nenhuma venda requisitada.'
            ]);
        }
    }

    private function criarPdfParaEnvio($chave)
    {
        $public = getenv('SERVIDOR_WEB') ? 'public/' : '';
        $xml = file_get_contents($public . 'xml_nfe/' . $chave . '.xml');
        $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($public . 'imgs/logo.jpg'));

        try {
            $danfe = new Danfe($xml);
            $id = $danfe->monta($logo);
            $pdf = $danfe->render();

            header('Content-Type: application/pdf');
            file_put_contents($public . 'pdf/DANFE_' . $chave . '.pdf', $pdf);
        } catch (\InvalidArgumentException $e) {
            echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
        }
    }

    public function downloadXmlZip(Request $request)
    {
        if (!empty($request->xmls)) {

            $this->deleteLastZip();

            $public = getenv('SERVIDOR_WEB') ? 'public/' : '';

            $zip = new ZipArchive();
            $filename = $public . 'xml_nfe/zip/XML_VENDAS.zip';

            if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
                exit("cannot open <$filename>\n");
            }

            foreach ($request->xmls as $id => $xml) {
                $zip->addFile($public . 'xml_nfe/' . $xml['chave'] . '.xml', $xml['chave'] . ".xml");
            }

            $zip->close();

            return response()->download($public . 'xml_nfe/zip/XML_VENDAS.zip');
        } else {
            die('<script>window.close()</script>');
        }
    }

    private function deletePdfEnvio($chave)
    {
        $public = getenv('SERVIDOR_WEB') ? 'public/' : '';
        unlink($public . 'pdf/DANFE_' . $chave . '.pdf');
    }

    public function factNfce(Request $request)
    {
        try {

            $config = ConfigNota::first();
            $tributacao = Tributacao::first();

            $nfe = new Make();
            $stdInNFe = new \stdClass();
            $stdInNFe->versao = '4.00'; //versão do layout
            $stdInNFe->Id = null; //se o Id de 44 digitos não for passado será gerado automaticamente
            $stdInNFe->pk_nItem = ''; //deixe essa variavel sempre como NULL

            $infNFe = $nfe->taginfNFe($stdInNFe);

            //IDE
            $stdIde = new \stdClass();
            $stdIde->cUF = $config->cUF;
            $stdIde->cNF = rand(11111111, 99999999);
            $stdIde->natOp = $config->nat_op_padrao;

            // $stdIde->indPag = 1; //NÃO EXISTE MAIS NA VERSÃO 4.00 // forma de pagamento

            $vendaLast = $request->lastId;
            $lastNumero = $vendaLast;

            $stdIde->mod = 65;
            $stdIde->serie = $config->numero_serie_nfce;
            $stdIde->nNF = (int)$lastNumero + 1;
            $stdIde->dhEmi = date("Y-m-d\TH:i:sP");
            $stdIde->dhSaiEnt = date("Y-m-d\TH:i:sP");
            $stdIde->tpNF = 1;
            $stdIde->idDest = 1;
            $stdIde->cMunFG = $config->codMun;
            $stdIde->tpImp = 4;
            $stdIde->tpEmis = 1;
            $stdIde->cDV = 0;
            $stdIde->tpAmb = $config->ambiente;
            $stdIde->finNFe = 1;
            $stdIde->indFinal = 1;
            $stdIde->indPres = 1;
            $stdIde->procEmi = '0';
            $stdIde->verProc = '2.0';
            //
            $tagide = $nfe->tagide($stdIde);

            $stdEmit = new \stdClass();
            $stdEmit->xNome = $config->razao_social;
            $stdEmit->xFant = $config->nome_fantasia;

            $ie = str_replace(".", "", $config->ie);
            $ie = str_replace("/", "", $ie);
            $ie = str_replace("-", "", $ie);
            $stdEmit->IE = $ie;
            $stdEmit->CRT = $tributacao->regime == 0 ? 1 : 3;

            $cnpj = str_replace(".", "", $config->cnpj);
            $cnpj = str_replace("/", "", $cnpj);
            $cnpj = str_replace("-", "", $cnpj);
            $stdEmit->CNPJ = $cnpj;

            $emit = $nfe->tagemit($stdEmit);

            // ENDERECO EMITENTE
            $stdEnderEmit = new \stdClass();
            $stdEnderEmit->xLgr = $config->logradouro;
            $stdEnderEmit->nro = $config->numero;
            $stdEnderEmit->xCpl = "";
            $stdEnderEmit->xBairro = $config->bairro;
            $stdEnderEmit->cMun = $config->codMun;
            $stdEnderEmit->xMun = $config->municipio;
            $stdEnderEmit->UF = $config->UF;

            $cep = str_replace("-", "", $config->cep);
            $stdEnderEmit->CEP = $cep;
            $stdEnderEmit->cPais = $config->codPais;
            $stdEnderEmit->xPais = $config->pais;

            $fone = str_replace(" ", "", $config->fone);
            $fone = str_replace("-", "", $fone);
            $stdEnderEmit->fone = $fone;

            $enderEmit = $nfe->tagenderEmit($stdEnderEmit);

            // DESTINATARIO


            if ($request->cpf != null) {
                $stdDest = new \stdClass();
                $cpf = str_replace(".", "", $request->cpf);
                $cpf = str_replace("/", "", $cpf);
                $cpf = str_replace("-", "", $cpf);
                $cpf = str_replace(" ", "", $cpf);

                if ($request->nome) $stdDest->xNome = $request->nome;
                $stdDest->indIEDest = "9";
                $stdDest->CPF = $cpf;
                $dest = $nfe->tagdest($stdDest);
            }


            $somaProdutos = 0;
            $somaICMS = 0;
            //PRODUTOS
            $itemCont = 0;
            $somaDesconto = 0;
            $totalItens = count($request->produtos);
            $somaAcrescimo = 0;
            $VBC = 0;

            foreach ($request->produtos as $produto) {
                $itemCont++;

                $stdProd = new \stdClass();
                $stdProd->item = $itemCont;
                $stdProd->cEAN = $produto->codBarras;
                $stdProd->cEANTrib = $produto->codBarras;
                $stdProd->cProd = $produto->id;
                $stdProd->xProd = $produto->nome;
                if ($produto->CST_CSOSN == '500' || $produto->CST_CSOSN == '60') {
                    $stdProd->cBenef = 'SEM CBENEF';
                }

                $ncm = $produto->NCM;
                $ncm = str_replace(".", "", $ncm);
                $stdProd->NCM = $ncm;

                $stdProd->CFOP = $produto->CFOP_saida_estadual;
                $cest = $produto->CEST;
                $cest = str_replace(".", "", $cest);
                $stdProd->CEST = $cest;
                $stdProd->uCom = $produto->unidade_venda;
                $stdProd->qCom = $produto->quantidade;
                $stdProd->vUnCom = $this->format($produto->valor);
                $stdProd->vProd = $this->format($produto->quantidade * $produto->valor);
                $stdProd->uTrib = $produto->unidade_venda;
                $stdProd->qTrib = $produto->quantidade;
                $stdProd->vUnTrib = $this->format($produto->valor);
                $stdProd->indTot = 1;

                // if($venda->desconto > 0){
                // 	$stdProd->vDesc = $this->format($venda->desconto/$totalItens);
                // }

                if ($request->desconto > 0) {
                    if ($itemCont < sizeof($request->produtos)) {
                        $totalVenda = $request->valor_total;

                        $media = (((($stdProd->vProd - $totalVenda) / $totalVenda)) * 100);
                        $media = 100 - ($media * -1);

                        $tempDesc = ($request->desconto * $media) / 100;
                        $somaDesconto += $tempDesc;

                        $stdProd->vDesc = $this->format($tempDesc);
                    } else {
                        $stdProd->vDesc = $this->format($request->desconto - $somaDesconto);
                    }
                }

                $somaProdutos += $produto->quantidade * $produto->valor;


                $prod = $nfe->tagprod($stdProd);

                $tributacao = Tributacao::first();

                $stdImposto = new \stdClass();
                $stdImposto->item = $itemCont;

                $imposto = $nfe->tagimposto($stdImposto);

                if ($tributacao->regime == 1) { // regime normal

                    $stdICMS = new \stdClass();
                    $stdICMS->item = $itemCont;
                    $stdICMS->orig = 0;
                    $stdICMS->CST = $produto->CST_CSOSN;
                    $stdICMS->modBC = 0;
                    $stdICMS->vBC = $this->format($produto->valor * $produto->quantidade);
                    $stdICMS->pICMS = $this->format($produto->perc_icms);
                    $stdICMS->vICMS = $stdICMS->vBC * ($stdICMS->pICMS / 100);

                    if ($produto->CST_CSOSN == '500' || $produto->CST_CSOSN == '60') {
                        $stdICMS->pRedBCEfet = 0.00;
                        $stdICMS->vBCEfet = 0.00;
                        $stdICMS->pICMSEfet = 0.00;
                        $stdICMS->vICMSEfet = 0.00;
                    } else {
                        $VBC += $stdProd->vProd;
                    }

                    $somaICMS += $stdICMS->vICMS;
                    $ICMS = $nfe->tagICMS($stdICMS);

                } else { // regime simples

                    $stdICMS = new \stdClass();

                    $stdICMS->item = $itemCont;
                    $stdICMS->orig = 0;
                    $stdICMS->CSOSN = $produto->CST_CSOSN;
                    $stdICMS->pCredSN = $this->format($produto->perc_icms);
                    $stdICMS->vCredICMSSN = $this->format($produto->perc_icms);
                    $ICMS = $nfe->tagICMSSN($stdICMS);

                    $somaICMS = 0;
                }


                $stdPIS = new \stdClass();
                $stdPIS->item = $itemCont;
                $stdPIS->CST = $produto->CST_PIS;
                $stdPIS->vBC = $this->format($produto->perc_pis) > 0 ? $stdProd->vProd : 0.00;
                $stdPIS->pPIS = $this->format($produto->perc_pis);
                $stdPIS->vPIS = $this->format(($stdProd->vProd * $produto->quantidade) * ($produto->perc_pis / 100));
                $PIS = $nfe->tagPIS($stdPIS);

                //COFINS
                $stdCOFINS = new \stdClass();
                $stdCOFINS->item = $itemCont;
                $stdCOFINS->CST = $produto->CST_COFINS;
                $stdCOFINS->vBC = $this->format($produto->perc_cofins) > 0 ? $stdProd->vProd : 0.00;
                $stdCOFINS->pCOFINS = $this->format($produto->perc_cofins);
                $stdCOFINS->vCOFINS = $this->format(($stdProd->vProd * $produto->quantidade) *
                    ($produto->perc_cofins / 100));
                $COFINS = $nfe->tagCOFINS($stdCOFINS);

                if (strlen($produto->descricao_anp) > 5) {
                    $stdComb = new \stdClass();
                    $stdComb->item = 1;
                    $stdComb->cProdANP = $produto->codigo_anp;
                    $stdComb->descANP = $produto->descricao_anp;
                    $stdComb->UFCons = $request->cliente->cidade->uf;

                    $nfe->tagcomb($stdComb);
                }

                $cest = $produto->CEST;
                $cest = str_replace(".", "", $cest);
                $stdProd->CEST = $cest;
                if (strlen($cest) > 0) {
                    $std = new \stdClass();
                    $std->item = $itemCont;
                    $std->CEST = $cest;
                    $nfe->tagCEST($std);
                }
            }

            //ICMS TOTAL
            $stdICMSTot = new \stdClass();
            $stdICMSTot->vBC = $this->format($VBC);
            $stdICMSTot->vICMS = $this->format($somaICMS);
            $stdICMSTot->vICMSDeson = 0.00;
            $stdICMSTot->vBCST = 0.00;
            $stdICMSTot->vST = 0.00;
            $stdICMSTot->vProd = $this->format($somaProdutos);

            $stdICMSTot->vFrete = 0.00;

            $stdICMSTot->vSeg = 0.00;
            $stdICMSTot->vDesc = $this->format($request->desconto);
            $stdICMSTot->vII = 0.00;
            $stdICMSTot->vIPI = 0.00;
            $stdICMSTot->vPIS = 0.00;
            $stdICMSTot->vCOFINS = 0.00;
            $stdICMSTot->vOutro = 0.00;
            $stdICMSTot->vNF = $this->format($request->valor_total);
            $stdICMSTot->vTotTrib = 0.00;
            $ICMSTot = $nfe->tagICMSTot($stdICMSTot);

            //TRANSPORTADORA

            $stdTransp = new \stdClass();
            $stdTransp->modFrete = 9;

            $transp = $nfe->tagtransp($stdTransp);


            $stdPag = new \stdClass();

            $stdPag->vTroco = $this->format($request->troco);

            $pag = $nfe->tagpag($stdPag);

            //Resp Tecnico
            $stdResp = new \stdClass();
            $stdResp->CNPJ = getenv('RESP_CNPJ');
            $stdResp->xContato = getenv('RESP_NOME');
            $stdResp->email = getenv('RESP_EMAIL');
            $stdResp->fone = getenv('RESP_FONE');

            $nfe->taginfRespTec($stdResp);

            //DETALHE PAGAMENTO

            $stdDetPag = new \stdClass();
            $stdDetPag->indPag = 0;

            $stdDetPag->tPag = $request->tipo_pagamento;
            $stdDetPag->vPag = $this->format($request->valor_pago); //Obs: deve ser informado o valor pago pelo cliente

            if ($request->tipo_pagamento == '03' || $request->tipo_pagamento == '04') {
                $stdDetPag->CNPJ = '16549105000215';
                $stdDetPag->tBand = $request->tipo_pagamento;
                $stdDetPag->cAut = '3333333';
                $stdDetPag->tpIntegra = 1;
            }

            $detPag = $nfe->tagdetPag($stdDetPag);

            try {
                $nfe->monta();
                $arr = [
                    'chave' => $nfe->getChave(),
                    'xml' => $nfe->getXML(),
                    'nNf' => $stdIde->nNF,
                    'modelo' => $nfe->getModelo()
                ];
                return $arr;
            } catch (\Exception $e) {
                return [
                    'erros_xml' => $nfe->getErrors()
                ];
            }
        } catch (\Exception $ex) {
            return [
                'exception' => $ex->getMessage() . ' ---> ' . $ex->getLine()
            ];
        }
    }

    public function generateNfce(Request $request)
    {
        try {
            $config = ConfigNota::first();

            $cnpj = str_replace(".", "", $config->cnpj);
            $cnpj = str_replace("/", "", $cnpj);
            $cnpj = str_replace("-", "", $cnpj);
            $cnpj = str_replace(" ", "", $cnpj);

            $nfe_service = new NFCeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$config->ambiente,
                "razaosocial" => $config->razao_social,
                "siglaUF" => $config->UF,
                "cnpj" => $cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $config->csc,
                "CSCid" => $config->csc_id
            ]);

            if ($request->estado == 'REJEITADO' || $request->estado == 'DISPONIVEL') {
                header('Content-type: text/html; charset=UTF-8');

                $request->produtos = json_decode(json_encode($request->produtos));
                $request->frete = json_decode(json_encode($request->frete));
                $request->cliente = json_decode(json_encode($request->cliente));
                $request->natureza = json_decode(json_encode($request->natureza));

                $nfce = $this->factNfce($request);

                if(isset($nfce['exception'])) {
                    return response()->json([
                        'error' => true,
                        'message' => 'erro interno',
                        'exception' => $nfce['exception']
                    ]);
                }

                if (!isset($nfce['erros_xml'])) {

                    $signed = $nfe_service->sign($nfce['xml']);
                    $resultado = $nfe_service->transmitirNfce($signed, $nfce['chave']);

                    $public = getenv('SERVIDOR_WEB') ? 'public/' : '';
                    file_put_contents($public . 'xml_nfce/' . $nfce['chave'] . '.xml', $signed);

                    if (substr($resultado, 0, 4) != 'Erro') {

                        $data = [
                            'error' => false,
                            'chave' => $nfce['chave'],
                            'estado' => 'APROVADO',
                            'nfcNum' => $nfce['nNf']
                        ];

                    } else {

                        $data = [
                            'estado' => 'REJEITADO'
                        ];
                    }

                    return response()->json([
                        'data' => $data,
                        'json' => $resultado
                    ]);

                } else {
                    return response()->json([
                        'data' => [
                            'error' => true,
                            'xml_error' => $nfce['erros_xml']
                        ]
                    ]);
                }

            } else {
                echo json_encode("Apro");
            }
        } catch(\Exception $e) {
            return [
                'error' => true,
                'exception' => $e->getMessage(),
            ];
        }
    }

    public function printNfce(Request $request)
    {
        $public = getenv('SERVIDOR_WEB') ? 'public/' : '';
        if (file_exists($public . 'xml_nfce/' . $request->chave . '.xml')) {
            try {
                $xml = file_get_contents($public . 'xml_nfce/' . $request->chave . '.xml');
                $pathLogo = $public . 'imgs/lagolimp_logo_(1)_(1).jpg';
                $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($pathLogo));


                $danfce = new Danfce($xml);
                $danfce->monta($logo);
                $pdf = $danfce->render();

                return response($pdf)->header('Content-Type', 'application/pdf');

            } catch (\Exception $e) {

                return response()->json([
                    'error' => true,
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => "Arquivo XML não encontrado!!"
            ]);
        }
    }

    public function generateCupom(Request $request)
    {
        $public = getenv('SERVIDOR_WEB') ? 'public/' : '';
        $pathLogo = $public . 'imgs/lagolimp_logo_(1)_(1).jpg';

        $request->itens = json_decode(json_encode($request->itens));

//        return $request->itens;

        $cupom = new Cupom($request, $pathLogo);

        $cupom->monta();
        $pdf = $cupom->render();

        return response($pdf)->header('Content-Type', 'application/pdf');
    }

    private function deleteLastZip()
    {
        $public = getenv('SERVIDOR_WEB') ? 'public/' : '';

        (file_exists($public . 'xml_nfe/zip/XML_VENDAS.zip')) ?? unlink($public . 'xml_nfe/zip/XML_VENDAS.zip');
        (file_exists($public . 'pdf/zip/DANFES_E_XML_PARA_EMAIL.zip')) ?? unlink($public . 'pdf/zip/DANFES_E_XML_PARA_EMAIL.zip');
    }

    public function importXML()
    {
        try {
            $public = $_SERVER['DOCUMENT_ROOT'] . '/api_fiscal/public/';

            if (file_exists($public . 'xml_dfe/' . $_FILES['xml_file']['name'])) {
                unlink($public . 'xml_dfe/' . $_FILES['xml_file']['name']);
            }

            $moveFile = move_uploaded_file($_FILES['xml_file']['tmp_name'], $public . 'xml_dfe/' . $_FILES['xml_file']['name']);

            $file = $public . 'xml_dfe/' . $_FILES['xml_file']['name'];

            if ($moveFile) {
                $config = ConfigNota::first();

                $cnpj = str_replace(".", "", $config->cnpj);
                $cnpj = str_replace("/", "", $cnpj);
                $cnpj = str_replace("-", "", $cnpj);
                $cnpj = str_replace(" ", "", $cnpj);


                $dfe_service = new DFeService([
                    "atualizacao" => date('Y-m-d h:i:s'),
                    "tpAmb" => 1,
                    "razaosocial" => $config->razao_social,
                    "siglaUF" => $config->UF,
                    "cnpj" => $cnpj,
                    "schemes" => "PL_009_V4",
                    "versao" => "4.00",
                    "tokenIBPT" => "AAAAAAA",
                    "CSC" => $config->csc,
                    "CSCid" => $config->csc_id
                ], 55);


                $xml = simplexml_load_file($file);

                if ($xml) {
                    $objXML = json_encode($xml);
                    $objXML = json_decode($objXML);
                    $chave = str_replace('.xml', '', $_FILES['xml_file']['name']);
                    if (!ManifestaDfe::where('chave', $chave)->first()) {
                        $this->criarFatura($objXML);

                        DB::table('sma_adjustments')->insert([
                            'date' => date('Y-m-d H:i:s', strtotime('now')),
                            'warehouse_id' => 1,
                            'created_by' => 1,
                            'reference_no' => $chave
                        ]);

                        $latest = DB::table('sma_adjustments')->where('reference_no', $chave)->first();

                        $products = $objXML->NFe->infNFe->det;

                        if(! is_array($products)) {
                            if(isset($products->prod)) {
                                $dbProduct = DB::table('sma_products')->where('name', $products->prod->xProd)->first();

                                if(! $dbProduct) {
                                    DB::table('sma_products')->insert([
                                        'code' => $products->prod->cProd,
                                        'name' => $products->prod->xProd,
                                        'price' => $products->prod->vProd,
                                        'category_id' => 1,
                                        'barcode_symbology' => 'code128',
                                        'type' => 'standard',
                                        'views' => 1,
                                        'hide' => 0,
                                        'hide_pos' => 0,
                                        'CFOP_saida_estadual' => $products->prod->CFOP,
                                        'NCM' => $products->prod->NCM,
                                        'quantity' => $products->prod->qCom,
                                    ]);

                                    $dbProduct = DB::table('sma_products')->where('name', $products->prod->xProd)->first();
                                }

                                $data = [
                                    'latest_id' => $latest->id,
                                    'product_id' => $dbProduct->id,
                                    'quantity' => $products->prod->qCom,
                                    'cost' => $dbProduct->cost,
                                    'product_code' => $dbProduct->code,
                                    'product_name' => $dbProduct->name,
                                    'net_unit_cost' => $dbProduct->cost,
                                    'warehouse_id' => 1,
                                    'subtotal' => $dbProduct->cost,
                                    'quantity_balance' => $dbProduct->quantity,
                                    'date' => date('Y-m-d', strtotime('now')),
                                    'status' => 'received',
                                    'unit_cost' => $dbProduct->cost,
                                    'real_unit_cost' => $dbProduct->cost,
                                    'quantity_received' => $dbProduct->quantity,
                                    'unit_quantity' => $dbProduct->quantity,
                                    'unit' => $dbProduct->unit
                                ];

                                $this->createAdjustment($data);
                            }
                        } else {
                            foreach($products as $product) {
                                if(isset($product->prod)) {
                                    $dbProduct = DB::table('sma_products')->where('name', $product->prod->xProd)->first();

                                    if(! $dbProduct) {
                                        DB::table('sma_products')->insert([
                                            'code' => $product->prod->cProd,
                                            'name' => $product->prod->xProd,
                                            'price' => $product->prod->vProd,
                                            'category_id' => 1,
                                            'barcode_symbology' => 'code128',
                                            'type' => 'standard',
                                            'views' => 1,
                                            'hide' => 0,
                                            'hide_pos' => 0,
                                            'CFOP_saida_estadual' => $product->prod->CFOP,
                                            'NCM' => $product->prod->NCM,
                                            'quantity' => $product->prod->qCom,
                                        ]);

                                        $dbProduct = DB::table('sma_products')->where('name', $product->prod->xProd)->first();
                                    }

                                    $data = [
                                        'latest_id' => $latest->id,
                                        'product_id' => $dbProduct->id,
                                        'quantity' => $product->prod->qCom,
                                        'cost' => $dbProduct->cost,
                                        'product_code' => $dbProduct->code,
                                        'product_name' => $dbProduct->name,
                                        'net_unit_cost' => $dbProduct->cost,
                                        'warehouse_id' => 1,
                                        'subtotal' => $dbProduct->cost,
                                        'quantity_balance' => $dbProduct->quantity,
                                        'date' => date('Y-m-d', strtotime('now')),
                                        'status' => 'received',
                                        'unit_cost' => $dbProduct->cost,
                                        'real_unit_cost' => $dbProduct->cost,
                                        'quantity_received' => $dbProduct->quantity,
                                        'unit_quantity' => $dbProduct->quantity,
                                        'unit' => $dbProduct->unit
                                    ];

                                    $this->createAdjustment($data);
                                }
                            }
                        }

                        $res = ManifestaDfe::create([
                            'chave' => $objXML->protNFe->infProt->chNFe,
                            'nome' => $objXML->NFe->infNFe->dest->xNome,
                            'documento' => $objXML->NFe->infNFe->dest->CNPJ ?? $objXML->NFe->infNFe->dest->CPF,
                            'valor' => $objXML->NFe->infNFe->pag->detPag->tPag,
                            'num_prot' => $objXML->protNFe->infProt->nProt,
                            'data_emissao' => $objXML->NFe->infNFe->ide->dhEmi,
                            'sequencia_evento' => 0,
                            'fatura_salva' => 0,
                            'tipo' => 0,
                            'nsu' => 0
                        ]);

                        if ($res) {
                            return [
                                'error' => false,
                                'message' => 'Arquivo importado com sucesso!'
                            ];
                        }
                    }

                    return [
                        'error' => true,
                        'message' => 'Nota solicitada já foi registrada no sistema!'
                    ];
                }
            }

            return [
                'error' => true,
                'message' => 'Erro ao importar arquivo xml.'
            ];

        } catch (Exception $ex) {
            return [
                'error' => true,
                'message' => "Erro interno",
                'exception' => $ex->getMessage()
            ];
        }
    }

    public function createAdjustment($data)
    {
        if(! DB::table('sma_adjustment_items')->where('product_id', $data['product_id'])->first()) {
            DB::table('sma_adjustment_items')->insert([
                'adjustment_id' => $data['latest_id'],
                'product_id' => $data['product_id'],
                'quantity' => $data['quantity'],
                'warehouse_id' => 1,
                'type' => 'addition'
            ]);

            $product = DB::table('sma_products')->where('id', $data['product_id'])->first();
            DB::table('sma_products')->where('id', $data['product_id'])->update([
                'quantity' => $product->quantity + $data['quantity']
            ]);

            DB::table('sma_warehouses_products')->insert([
                'product_id' => $data['product_id'],
                'warehouse_id' => 1,
                'quantity' => $data['quantity'],
                'avg_cost' => $data['cost']
            ]);

            DB::table('sma_purchase_items')->insert([
                'product_id' =>  $data['product_id'],
                'product_code' =>  $data['product_code'],
                'product_name' =>  $data['product_name'],
                'net_unit_cost' =>  $data['net_unit_cost'],
                'quantity' =>  $data['quantity'],
                'warehouse_id' =>  $data['warehouse_id'],
                'subtotal' =>  $data['subtotal'],
                'quantity_balance' =>  $data['quantity_balance'],
                'date' =>  $data['date'],
                'status' => 'received',
                'unit_cost' =>  $data['unit_cost'],
                'real_unit_cost' =>  $data['real_unit_cost'],
                'quantity_received' =>  $data['quantity_received'],
                'unit_quantity' => $data['unit_quantity'],
                'tax_rate_id' => 1,
                'tax' => 0,
                'item_tax' => 0,
                'product_unit_id' => $data['unit'],
                'product_unit_code' => $data['product_code']
            ]);
        }
    }

	public function downloadDfeXML($chave)
	{
		$public = $_SERVER['DOCUMENT_ROOT'] . '/api_fiscal/public';
		$file = "$public/xml_dfe/$chave.xml";

		return response()->download($file);
	}

	public function printDanfe($chave)
	{
		$config = ConfigNota::first();

		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);

		$dfe_service = new DFeService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => 1,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->UF,
			"cnpj" => $cnpj,
			"schemes" => "PL_009_V4",
			"versao" => "4.00",
			"tokenIBPT" => "AAAAAAA",
			"CSC" => $config->csc,
			"CSCid" => $config->csc_id
		], 55);

		try {
			$public = getenv('SERVIDOR_WEB') ? 'public/' : '';
			$xml = file_get_contents($public.'xml_dfe/'.$chave.'.xml');

			$danfe = new Danfe($xml);
			$id = $danfe->monta();
			$pdf = $danfe->render();
			header('Content-Type: application/pdf');
			echo $pdf;
		} catch (Exception $e) {
			echo "
			<div style='margin: 5rem; display: flex; align-items: center; justify-content: center;'>
				<p style='font-family: Arial; font-size: 20px;'>Erro interno: " . $e->getMessage().", aguarde alguns instantes e atualize a pagina!</p>
			<div>
			";
		}
	}

    private function criarFatura($xml)
    {
        $supplier = DB::table('sma_companies')->where('company', '=', $xml->NFe->infNFe->emit->xNome)->first();

        if(! $supplier) {
            $id = $this->cadastrarFornecedor([
                'cpf' => $xml->NFe->infNFe->emit->CPF ?? '',
                'cnpj' => $xml->NFe->infNFe->emit->CNPJ,
                'razaoSocial' => $xml->NFe->infNFe->emit->xNome,
                'nomeFantasia' => $xml->NFe->infNFe->emit->xFant ?? $xml->NFe->infNFe->emit->xNome,
                'logradouro' => $xml->NFe->infNFe->emit->enderEmit->xLgr,
                'numero' => $xml->NFe->infNFe->emit->enderEmit->nro,
                'bairro' => $xml->NFe->infNFe->emit->enderEmit->xBairro,
                'cep' => $xml->NFe->infNFe->emit->enderEmit->CEP,
                'fone' => $xml->NFe->infNFe->emit->enderEmit->fone,
                'ie' => $xml->NFe->infNFe->emit->IE,
                'cidade' => $xml->NFe->infNFe->emit->enderEmit->xMun,
                'pais' => $xml->NFe->infNFe->emit->enderEmit->xPais
            ]);

            $supplier = DB::table('sma_companies')->where('id', '=', $id)->first();
        }

        if(! DB::table('sma_purchases')->where('reference_no', '=', $xml->protNFe->infProt->chNFe)->first()) {
            if(isset($xml->NFe->infNFe->cobr->dup)) {
                $faturas = $xml->NFe->infNFe->cobr->dup;
                if(is_array($faturas)) {
                    foreach ($faturas as $fatura) {
                        DB::table('sma_purchases')->insert([
                            'reference_no' => $xml->protNFe->infProt->chNFe,
                            'date' => date('Y-m-d H:i:s', time()),
                            'supplier_id' => $supplier->id,
                            'supplier' => $supplier->company,
                            'warehouse_id' => 1,
                            'total' => $fatura->vDup,
                            'grand_total' => $fatura->vDup,
                            'paid' => 0,
                            'status' => 'pending',
                            'payment_status' => 'pending',
                            'created_by' => 1,
                            'vencimento' => $fatura->dVenc
                        ]);
                    }
                } else {
                    DB::table('sma_purchases')->insert([
                        'reference_no' => $xml->protNFe->infProt->chNFe,
                        'date' => date('Y-m-d H:i:s', time()),
                        'supplier_id' => $supplier->id,
                        'supplier' => $supplier->company,
                        'warehouse_id' => 1,
                        'total' => $faturas->vDup,
                        'grand_total' => $faturas->vDup,
                        'paid' => 0,
                        'status' => 'pending',
                        'payment_status' => 'pending',
                        'created_by' => 1,
                        'vencimento' => $faturas->dVenc
                    ]);
                }
            } else {
                DB::table('sma_purchases')->insert([
                    'reference_no' => $xml->protNFe->infProt->chNFe,
                    'date' => date('Y-m-d H:i:s', time()),
                    'supplier_id' => $supplier->id,
                    'supplier' => $supplier->company,
                    'warehouse_id' => 1,
                    'total' => $xml->NFe->infNFe->total->ICMSTot->vProd,
                    'grand_total' => $xml->NFe->infNFe->total->ICMSTot->vProd,
                    'paid' => 0,
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'created_by' => 1,
                    'vencimento' => ''
                ]);
            }
        }
    }

    public function downloadPosXml(Request $request)
    {
        if (isset($request->chaves)) {
            if(count($request->chaves) == 1) {
                $public = getenv('SERVIDOR_WEB') ? 'public/' : '';
                    if (file_exists($public . 'xml_nfce/' . $request->chaves[0] . '.xml')) {

                    return response()->download($public . 'xml_nfce/' . $request->chaves[0] . '.xml');
                } else {
                    return response()->json([
                        'error' => true,
                        'message' => "Arquivo XML não encontrado!!"
                    ]);
                }
            } else {
                $zip = new ZipArchive();

                $public = getenv('SERVIDOR_WEB') ? 'public/' : '';;
                $DelFilePath = $public . 'xml_nfce/' . 'XML_nfce.zip';

                if(file_exists($DelFilePath)) {
                    unlink ($DelFilePath);
                }

                if ($zip->open($DelFilePath, ZIPARCHIVE::CREATE) != TRUE) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Erro ao abrir arquivo zip'
                    ]);
                }

                foreach ($request->chaves as $chave) {
                    if (file_exists($public . 'xml_nfce/' . $chave . '.xml')) {
                        $zip->addFile($public . 'xml_nfce/' . $chave . '.xml', "$chave.xml");
                    } else {
                        return response()->json([
                            'error' => true,
                            'message' => "Arquivo XML não encontrado!!"
                        ]);
                    }
                }

                $zip->close();
                return response()->download($DelFilePath);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Selecione uma venda para baixar o XML'
            ]);
        }
    }
}
