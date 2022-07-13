<?php
/**
 * busca-ativa-escolar-api
 * CityListFile.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 27/12/2016, 13:09
 */

namespace BuscaAtivaEscolar\IBGE\Importing;


use Curl;
use Log;
use Maatwebsite\Excel\Files\ExcelFile;

class CityListXls extends ExcelFile {

	const FIELD_UF_CODE = 'nm_uf_sigla';
	const FIELD_NAME = 'nm_mun_2015';
	const FIELD_IBGE_CITY_ID = 'cd_gcmun';
	const FIELD_IBGE_UF_ID = 'cd_gcuf';

	const REMOTE_FILE_PATH = 'ftp://geoftp.ibge.gov.br//organizacao_do_territorio/estrutura_territorial/areas_territoriais/2015/AR_BR_RG_UF_MUN_2015.xls';
	const LOCAL_FILE_PATH = 'app/ibge_cities.xls';

	public function getFile() {
		$localPath = storage_path(self::LOCAL_FILE_PATH);

		if(file_exists($localPath)) {
			return $localPath;
		}

		return $this->fetchFile($localPath);

	}

	public function fetchFile($localPath) {
		Log::info("Downloading file: " . self::REMOTE_FILE_PATH);

		Curl::to(self::REMOTE_FILE_PATH)->download($localPath);

		Log::info("File downloaded to {$localPath}, size=" . filesize($localPath));

		return $localPath;
	}


}