<?php

namespace App\Services;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use App\Certificado;
use NFePHP\NFe\Common\Standardize;

error_reporting(E_ALL);
ini_set('display_errors', 'On');

class DFeService{

	private $config; 
	private $tools;

	public function __construct($config, $modelo){
		$certificado = Certificado::first();
		$this->config = $config;
		$this->tools = new Tools(json_encode($config), Certificate::readPfx($certificado->arquivo, $certificado->senha));
		$this->tools->model($modelo);
		$this->tools->setEnvironment(1);
	}

	public function consulta(){
		$ultNSU = 0;
		$maxNSU = $ultNSU;
		$loopLimit = 10;
		$iCount = 0;
		//executa a busca de DFe em loop
		$last = "";
		$imprime = false;
		$arrXml = [];
		while ($ultNSU <= $maxNSU) {
			$iCount++;
			if ($iCount >= $loopLimit) {
				break;
			}
			try {

				$resp = $this->tools->sefazDistDFe($ultNSU);
				$dom = new \DOMDocument();
				$dom->loadXML($resp);

				$node = $dom->getElementsByTagName('retDistDFeInt')->item(0);
				$tpAmb = $node->getElementsByTagName('tpAmb')->item(0)->nodeValue;
				$verAplic = $node->getElementsByTagName('verAplic')->item(0)->nodeValue;
				$cStat = $node->getElementsByTagName('cStat')->item(0)->nodeValue;
				$xMotivo = $node->getElementsByTagName('xMotivo')->item(0)->nodeValue;
				$dhResp = $node->getElementsByTagName('dhResp')->item(0)->nodeValue;
				$ultNSU = $node->getElementsByTagName('ultNSU')->item(0)->nodeValue;
				$maxNSU = $node->getElementsByTagName('maxNSU')->item(0)->nodeValue;
				$lote = $node->getElementsByTagName('loteDistDFeInt')->item(0);

				if($last != $ultNSU){
					
					$last = $ultNSU;
					if (empty($lote)) {
        			//lote vazio
						continue;
					}
    				//essas tags irão conter os documentos zipados
					$docs = $lote->getElementsByTagName('docZip');

					foreach ($docs as $doc) {

						$numnsu = $doc->getAttribute('NSU');
						$schema = $doc->getAttribute('schema');

						$content = gzdecode(base64_decode($doc->nodeValue));

						if($iCount){ // TESTE
							// dd($content);
							$xml = simplexml_load_string($content);

							if(!in_array($xml->chNFe, $arrXml)){


								$xmlCompleto = $this->teste2($xml->chNFe);							

								// try {
								// 	$stz = new Standardize($xmlCompleto);
								// 	$std = $stz->toStd();
								// 	if ($std->cStat != 138) {
								// 		// echo "Documento não retornado. [$std->cStat] $std->xMotivo";  
								// 		// die;
								// 	}    
								// 	echo $xml->chNFe . "<br>";

								// 	$zip = $std->loteDistDFeInt->docZip;
								// 	$esc = gzdecode(base64_decode($zip));
								// 	print($esc);
								// 	// header('Content-type: text/xml; charset=UTF-8');
								// 	// echo $xml;

								// } catch (\Exception $e) {
								// 	echo str_replace("\n", "<br/>", $e->getMessage());
								// }
								array_push($arrXml, $xml->chNFe);
							}
							
						}

        				//identifica o tipo de documento
						$tipo = substr($schema, 0, 6);

					}
					sleep(2.5);
				}
			} catch (\Exception $e) {
				echo $e->getMessage();
			}

    		//extrair e salvar os retornos

		}
	}

	public function downloadXmlPelaChave($chave){
		try {

			$this->tools->setEnvironment(1);
			$response = $this->tools->sefazDownload($chave);

			return $response;

		} catch (\Exception $e) {
			return str_replace("\n", "<br/>", $e->getMessage());
		}
	}

	public function teste2($chave){
		try {

			$response = $this->tools->sefazConsultaChave($chave);

    //você pode padronizar os dados de retorno atraves da classe abaixo
    //de forma a facilitar a extração dos dados do XML
    //NOTA: mas lembre-se que esse XML muitas vezes será necessário, 
    //      quando houver a necessidade de protocolos
			$stdCl = new Standardize($response);
    //nesse caso $std irá conter uma representação em stdClass do XML
			$std = $stdCl->toStd();
    //nesse caso o $arr irá conter uma representação em array do XML
			$arr = $stdCl->toArray();
    //nesse caso o $json irá conter uma representação em JSON do XML
			$json = $stdCl->toJson();
			return $arr;

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

}