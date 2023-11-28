<?php

	/**
	 * busca-ativa-escolar-api
	 * SchoolsController.php
	 *
	 * Copyright (c) LQDI Digital
	 * www.lqdi.net - 2017
	 *
	 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
	 *
	 * Created at: 30/01/2017, 19:26
	 */

	namespace BuscaAtivaEscolar\Http\Controllers\Resources;

	use BuscaAtivaEscolar\CaseSteps\Pesquisa;
	use BuscaAtivaEscolar\EmailJob;
	use BuscaAtivaEscolar\EmailTypes\SchoolEducacensoEmail;
	use BuscaAtivaEscolar\EmailTypes\SchoolFrequencyEmail;
	use BuscaAtivaEscolar\Http\Controllers\BaseController;
	use BuscaAtivaEscolar\Jobs\ProcessSmsFrequencySchool;
	use BuscaAtivaEscolar\Jobs\ProcessEmailJob;
	use BuscaAtivaEscolar\School;
	use BuscaAtivaEscolar\Search\ElasticSearchQuery;
	use BuscaAtivaEscolar\Search\Search;
	use BuscaAtivaEscolar\Serializers\SimpleArraySerializer;
	use BuscaAtivaEscolar\Transformers\SchoolSearchResultsTransformer;
	use BuscaAtivaEscolar\Transformers\SchoolTransformer;
	use BuscaAtivaEscolar\Transformers\SearchResultsTransformer;
	use Exception;
	use Illuminate\Database\QueryException;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;
	use Illuminate\Validation\ValidationException;
	use League\Fractal\Pagination\IlluminatePaginatorAdapter;
	use Illuminate\Notifications\Notifiable;
	use Illuminate\Support\Facades\Log;

	use stdClass;
	use function PHPUnit\Framework\isEmpty;

	class SchoolsController extends BaseController
	{

		use Notifiable;

		public function openSearch(Search $search)
		{
			$parameters = request()->only(['id', 'uf', 'city_ibge_id', 'name']);
			$parameters['uf'] = strtolower(Str::ascii($parameters['uf']));
			$parameters['name'] = Str::ascii($parameters['name']);

			$query = ElasticSearchQuery::withParameters($parameters)
				->searchTextInColumns('name', ['name', 'id', 'city_ibge_id'])
				->filterByTerm('city_ibge_id', false, 'must')
				->filterByTerm('uf', false)
				->getQuery();

			$results = $search->search(new School(), $query, 12);

			$values = $this->includeResultsForPesquisaWash($results);

			return response()->json($values, 200);
		}

		/**
		 * @param Search $search
		 * @return mixed
		 * @throws Exception
		 */
		public function search(Search $search)
		{

			$parameters = request()->only(['id', 'uf', 'city_id', 'name']);
			$parameters['name'] = Str::ascii($parameters['name']);

			$query = ElasticSearchQuery::withParameters($parameters)
				->searchTextInColumns('name', ['name', 'id'])
				->filterByTerm('city_id', false)
				->getQuery();

			$results = $search->search(new School(), $query, 12);

			return fractal()
				->item($results)
				->transformWith(new SearchResultsTransformer(SchoolSearchResultsTransformer::class, $query))
				->serializeWith(new SimpleArraySerializer())
				->parseIncludes(request('with'))
				->respond();
		}

		private function includeResultsForPesquisaWash($result)
		{
			if (!isset($result['hits']))
				return null;
			if (!isset($result['hits']['hits']))
				return null;
			$values = $result['hits']['hits'];

			$arrayValues = [];

			foreach ($values as $value) {
				$objet = new stdClass();
				$objet->id = $value['_id'];
				$objet->name = $value['_source']['name'];
				$objet->ibge_id = $value['_source']['city_ibge_id'];
				$objet->city_name = $value['_source']['city_name'];
				$objet->id_name = $value['_id'] . " - " . $value['_source']['name'];
				array_push($arrayValues, $objet);
			}
			return $arrayValues;
		}

		/**
		 * @param Request $request
		 * @return JsonResponse
		 * @throws Exception
		 */
		public function sendNotificationsEducacensoSchool(Request $request)
		{
			$schools = $request->request;

			$user = auth()->user();
			/* @var $user User */

			foreach ($schools as $key => $school) {

				if ($school['school_email'] == null or $school['school_email'] == "") {
					$data['status'] = "error";
					$data['message'] = "Email inválido";
					return response()->json($data, 403);
				}

				$school = School::whereSchoolEmail($school['school_email'])->first();

				if ($school->token == null) {
					$school->token = str_random(40);
					$school->save();
				}

				$job = EmailJob::createFromType(SchoolEducacensoEmail::TYPE, $user, $school);
				dispatch(new ProcessEmailJob($job));
			}

			$data['status'] = "ok";
			$data['message'] = "Mensagens encaminhadas para fila de envio";

			return response()->json($data, 200);
		}

		public function sendNotificationsFrequencySchool(Request $request)
		{

			$schools = $request->request;

			$user = auth()->user();
			/* @var $user User */

			foreach ($schools as $key => $school) {

				if ($school['school_email'] == null or $school['school_email'] == "") {
					$data['status'] = "error";
					$data['message'] = "Email inválido";
					return response()->json($data, 403);
				}

				$school = School::whereSchoolEmail($school['school_email'])->first();

				if ($school->token == null) {
					$school->token = str_random(40);
					$school->save();
				}

				$job = EmailJob::createFromType(SchoolFrequencyEmail::TYPE, $user, $school);
				//Queue::pushOn('emails', new ProcessEmailJob($job));
				$job1 = new ProcessEmailJob($job);
				$job1->handle();

				if ($school->school_cell_phone != null && $school->school_cell_phone != "") {
					//Queue::pushOn('sms_school', new ProcessSmsFrequencySchool($school));
					$job2 = new ProcessSmsFrequencySchool($school);
					$job2->handle();
				}
			}

			$data['status'] = "ok";
			$data['message'] = "Mensagens encaminhadas para fila de envio";

			return response()->json($data, 200);
		}

		/**
		 * @param School $school
		 * @return JsonResponse
		 */
		public function update(School $school)
		{

			$input = request()->all();
			$school = School::findOrFail((int)$input['id']);
			$school->fill($input);

			try {
				$school->save();
			} catch (QueryException $e) {

				$data['status'] = "error";
				$data['message'] = "Email pertence a outra escola";
				$data['school'] = $school;
				return response()->json($data, 403);
			}

			return response()->json(['status' => 'ok', 'updated' => $input]);
		}

		//only for all_educacenso method

		/**
		 * @return mixed
		 */
		public function all_educacenso()
		{

			$tenant_id = $this->currentUser()->tenant->id;

			//create a stdclass to paginate
			$meta = new stdClass();
			$pagination = new stdClass();
			$pagination->count = (int)request('max', 5);
			$pagination->per_page = (int)request('max', 5);
			$pagination->current_page = (int)request('page', 1);

			//get a list ids of schools by tenant and educacenso id
			$schools_array_id = Pesquisa::query()
				->select('school_last_id')
				->whereHas('child', function ($query_child) {
					$query_child->whereYear('created_at', '=', request('year_educacenso', date("Y")));
				})
				->whereHas('childCase', function ($query_childCase) {
					$query_childCase->where('current_step_type', '=', 'BuscaAtivaEscolar\CaseSteps\Alerta');
				})
				->where([
					['tenant_id', '=', $tenant_id],
					['place_city_name', 'like', '%' . request('city_name') . '%']
				])
				->whereNotNull('school_last_id')
				->groupBy('school_last_id')
				->pluck('school_last_id')
				->toArray();

			$qtd_schools = count($schools_array_id);

			if ($qtd_schools == 0) {
				array_push($schools_array_id, 0);
			}

			$pagination->total = $qtd_schools;

			$total_pages =
				$pagination->total % $pagination->per_page > 0 ?
					(int)($pagination->total / $pagination->per_page + 1) :
					$pagination->total / $pagination->per_page;

			$pagination->total_pages = $total_pages;

			$meta->pagination = $pagination;

			$cursor = $this->getCursor($pagination->per_page, $qtd_schools, $pagination->current_page);

			$schools = DB::select(
				"select " .
				"sc.id, sc.name, sc.city_name, sc.uf, sc.school_cell_phone, sc.school_phone, sc.school_email, " .
				"count(csp.school_last_id) as count_children, " .
				"count(case when csa.place_address is not null and csa.place_neighborhood is not null then 0 end) as count_with_cep " .
				"from schools as sc " .
				"inner join case_steps_pesquisa as csp on sc.id = csp.school_last_id " .
				"inner join case_steps_alerta as csa on csp.child_id = csa.child_id " .
				"inner join children as ch on ch.id = csa.child_id " .
				"where sc.id in (" . implode(",", $schools_array_id) . ") " .
				"and year(ch.created_at) = " . request('year_educacenso', date("Y")) . " " .
				//"and csa.place_cep is null ".
				"group by sc.id " .
				"limit " . $cursor . ", " . request('max', 5) . ""
			);

			//add a array of emailjobs to each school of the last query
			array_map(function ($school) {
				$school->emailJob = EmailJob::where('school_id', '=', $school->id)->get()->toArray();
				return $school;
			}, $schools);

			return response()->json(
				[
					'data' => $schools,
					'meta' => $meta,
					'cities' => Pesquisa::where('tenant_id', $tenant_id)
						->whereYear('created_at', '=', request('year_educacenso'))
						->orderBy('place_city_name')
						->distinct()
						->pluck('place_city_name')
				]
			);
		}

		public function getCursor($limit, $interval, $point)
		{

			if ($interval == 0)
				return 0;

			$final_array = [];
			$actual_array = [];

			for ($i = 0; $i < $interval; $i++) {
				if (count($actual_array) <= $limit) {
					array_push($actual_array, $i);
				}
				if (count($actual_array) == $limit) {
					array_push($final_array, $actual_array);
					$actual_array = [];
				}
			}

			if (count($actual_array) > 0 and count($actual_array) < $limit) {
				array_push($final_array, $actual_array);
			}

			$position = $final_array[$point - 1][0];

			return $position;
		}

		public function getAll()
		{

			if ($this->currentUser()->isRestrictedToUF()) {
				$query = School::with('emailJobs')->where('uf', '=', $this->currentUser()->uf);
			} else {
				$tenant = $this->currentUser()->tenant;
				$query = School::with('emailJobs')->where('city_id', '=', $tenant->city_id);
			}

			$search = request('search', '');
			if ($search != '') {
				$query->where('id', '=', intval($search));
			}

			$max = request('max', 128);
			if ($max > 128)
				$max = 128;
			if ($max < 5)
				$max = 5;

			$paginator = $query->paginate($max);
			$collection = $paginator->getCollection();

			return fractal()
				->collection($collection)
				->transformWith(new SchoolTransformer())
				->serializeWith(new SimpleArraySerializer())
				->paginateWith(new IlluminatePaginatorAdapter($paginator))
				->parseIncludes(request('with'))
				->respond();
		}

		public function store()
		{
			try {
				$parameters = request()->validate([
					'codigo' => 'required|numeric',
					'name' => 'required|string',
					'uf' => 'required|string',
					'uf_id' => 'required|numeric',
					'city_id' => 'required|uuid',
					'city_ibge_id' => 'required|numeric',
					'city_name' => 'required|string',
					'region' => 'required|string',
					'school_email' => 'nullable|email',
				]);

				// Verificar se o código já está cadastrado
				$isCodeRegistered = School::where('id', $parameters['codigo'])->exists();
				if ($isCodeRegistered) {
					return response()->json(['status' => 'error', 'reason' => 'INEP has already been registered', 'message' => 'INEP code already exists'], 409);
				}

				// Verificar se o nome da escola já está cadastrado
				$isNameRegistered = School::where('name', $parameters['name'])->exists();
				if ($isNameRegistered) {
					return response()->json(['status' => 'error', 'reason' => 'School name has already been registered', 'message' => 'School name already exists'], 409);
				}

				// Transação para garantir consistência no banco de dados
				DB::beginTransaction();

				// Criar uma nova instância de School
				$school = new School();
				$school->id = $parameters['codigo'];
				$school->name = $parameters['name'];
				$school->uf = $parameters['uf'];
				$school->uf_id = $parameters['uf_id'];
				$school->city_id = $parameters['city_id'];
				$school->city_ibge_id = $parameters['city_ibge_id'];
				$school->city_name = $parameters['city_name'];
				$school->region = $parameters['region'];
				$school->school_email = $parameters['school_email'] ?? null;
				$school->save();

				// Commit da transação
				DB::commit();

				// Resposta de sucesso
				return $this->api_success(['message' => 'School successfully registered',
					'school' => [
						'id' => $school->id,
						'name' => $school->name,
					]]);


			} catch (ValidationException $validationException) {
				// Chamada para a função de tratamento de exceção com detalhes de validação
				return $this->api_validation_failed('validation_failed', $validationException->validator, ['message' => 'Validation failed']);
			} catch (Exception $ex) {
				return $this->api_exception($ex);
			}
		}

		private function includeResults($result)
		{
			if (!isset($result['hits']))
				return null;
			if (!isset($result['hits']['hits']))
				return null;
			$values = $result['hits']['hits'];

			$arrayValues = [];

			foreach ($values as $value) {
				$objet = new stdClass();
				$objet->id = $value['_id'];
				$objet->name = $value['_source']['name'];
				$objet->ibge_id = $value['_source']['city_ibge_id'];
				$objet->city_name = $value['_source']['city_name'];
				array_push($arrayValues, $objet);
			}
			return $arrayValues;
		}
	}
