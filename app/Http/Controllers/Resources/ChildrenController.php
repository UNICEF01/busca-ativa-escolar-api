<?php

/**
 * busca-ativa-escolar-api
 * ChildrenController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 30/12/2016, 16:16
 */

namespace BuscaAtivaEscolar\Http\Controllers\Resources;

use Auth;
use BuscaAtivaEscolar\City;
use BuscaAtivaEscolar\ActivityLog;
use BuscaAtivaEscolar\Attachment;
use BuscaAtivaEscolar\CaseSteps\Alerta;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\Comment;
use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\Jobs\ProcessExportChildrenJob;
use BuscaAtivaEscolar\Search\ElasticSearchQuery;
use BuscaAtivaEscolar\Search\Search;
use BuscaAtivaEscolar\Serializers\SimpleArraySerializer;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\Transformers\AttachmentTransformer;
use BuscaAtivaEscolar\Transformers\ChildExportResultsTransformer;
use BuscaAtivaEscolar\Transformers\ChildSearchResultsTransformer;
use BuscaAtivaEscolar\Transformers\ChildTransformer;
use BuscaAtivaEscolar\Transformers\CommentTransformer;
use BuscaAtivaEscolar\Transformers\LogEntryTransformer;
use BuscaAtivaEscolar\Transformers\SearchResultsTransformer;
use BuscaAtivaEscolar\Transformers\StepTransformer;
use BuscaAtivaEscolar\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Maatwebsite\Excel\Excel as ExcelB;
use BuscaAtivaEscolar\Exports\ChildrenExport;

class ChildrenController extends BaseController
{
	private $excel;
	public function __construct(ExcelB $excel)
	{
		$this->excel = $excel;
	}
	protected function prepareSearchQuery(): ElasticSearchQuery
	{

		$params = $this->filterAsciiFields(request()->all(), ['name', 'cause_name', 'assigned_user_name', 'location_full', 'step_name', 'city_name', 'group_id']);

		$params['alert_status'] = "accepted";

		// Scope the query within the tenant
		if (Auth::user()->isRestrictedToTenant())
			$params['tenant_id'] = Auth::user()->tenant_id;

		// Scope the query to state agents
		if (Auth::user()->isRestrictedToUF())
			$params['assigned_uf'] = Auth::user()->uf;

		if (isset($params['uf']))
			$params['uf'] = Str::lower($params['uf']);
		if (isset($params['assigned_uf']))
			$params['assigned_uf'] = Str::lower($params['assigned_uf']);

		$query = ElasticSearchQuery::withParameters($params)
			->filterByTerm('tenant_id', false)
			->filterByTerm('uf', false)
			->filterByTerm('alert_status', false)
			->filterByTerm('assigned_uf', false)
			->addTextFields(['name', 'cause_name', 'step_name', 'assigned_user_name', 'city_name'], 'match')
			->searchTextInColumns('location_full', ['place_address^3', 'place_cep^2', 'place_city^2', 'place_uf', 'place_neighborhood', 'place_reference'])
			->searchTextInColumns('city_name_full', ['place_uf', 'place_city_name'])
			->filterByTerms('case_status', false)
			->filterByTerms('risk_level', $params['risk_level_null'] ?? false)
			->filterByTerm('current_step_type', false)
			->filterByTerm('step_slug', false)
			->filterByTerms('gender', $params['gender_null'] ?? false)
			->filterByTerms('place_kind', $params['place_kind_null'] ?? false)
			->filterByRange('age', $params['age_null'] ?? false);

		// Verifica se o parâmetro 'deadline_status' não está vazio (ou seja, não é um array vazio).
		if (!empty($params['deadline_status'])) {
			$query->filterByTerms('deadline_status', true);
		}

		// Scope query within user, when relevant
		if (Auth::user()->type === User::TYPE_TECNICO_VERIFICADOR) {
			$query->filterByOneOf(['assigned_user_id' => ['type' => 'term', 'search' => Auth::user()->id]]);
		}

		if (array_key_exists("case_not_info", $params) == 1) {
			if ($params["case_not_info"][0] == 'yes')
				$query->getNonInformedCases(1, array());
			else {
				if (array_key_exists("case_cause_ids", $params)) {
					$query->getNonInformedCases(0, $params);
				}
			}
		}

		// Verifica se o tipo de usuário atual não contém a palavra 'estadual'.
		if (!str_contains(Auth::user()->type, 'estadual')) {
			// Se o tipo de usuário não contiver 'estadual', então...
			// Chama o método getGroups no objeto $query, passando $params como argumento.
			// Essa função adiciona uma cláusula de busca à query Elasticsearch para filtrar por grupos. 
			// Ela recebe um array de parâmetros e verifica se foi passado 'tree' e 'group_id'.
			$query->getGroups($params);
		}

		return $query;
	}

	public function search(Search $search)
	{
		$from = request()->input('from');
		$size = request()->input('size');

		$query = $this->prepareSearchQuery();
		$attempted = $query->getAttemptedQuery();
		$query = $query->getQuery();

		$results = $search->search(new Child(), $query, $size, $from - 1); //need to use -1 (value of front is always 1 or more and eastic needs to start at 0)

		return fractal()
			->item($results)
			->transformWith(new SearchResultsTransformer(new ChildSearchResultsTransformer(), $query, $attempted))
			->serializeWith(new SimpleArraySerializer())
			->parseIncludes(request('with'))
			->respond();
	}

	public function export(Search $search)
	{

		$query = $this->prepareSearchQuery();

		$attempted = $query->getAttemptedQuery();
		$query = $query->getQuery();

		$results = $search->search(new Child(), $query, 2000);


		$data = fractal()
			->item($results)
			->transformWith(new SearchResultsTransformer(new ChildExportResultsTransformer(), $query, $attempted))
			->serializeWith(new SimpleArraySerializer())
			->parseIncludes(request('with'))
			->toArray();

		$tenantID = auth()->user()->tenant_id ?? 'global';

		$this->excel->store(new ChildrenExport($data), 'attachment/children_reports/' . auth()->user()->id . '/' . auth()->user()->id . '.xls');

		$token = \JWTAuth::fromUser(auth()->user());

		return $this->api_success([
			'export_file' => auth()->user()->id . '.xls',
			'download_url' => route('api.children.download_exported', ['filename' => auth()->user()->id . '.xls', 'token' => $token])
		]);
	}

	public function download_exported($filename)
	{

		$tenantID = auth()->user()->tenant_id ?? 'global';
		$token = request('token');

		\JWTAuth::invalidate($token);

		return response()->download(storage_path('app/attachment/children_reports/' . auth()->user()->id . '/' . basename($filename)));
	}

	protected function filterAsciiFields($input, $fields)
	{
		$output = [];

		foreach ($input as $key => $value) {
			if (in_array($key, $fields))
				$value = Str::ascii($value);
			$output[$key] = $value;
		}

		return $output;
	}

	protected function list()
	{
		$paginator = Child::with('cases')->paginate(64);
		$collection = $paginator->getCollection();

		return fractal()
			->collection($collection)
			->transformWith(new ChildTransformer)
			->paginateWith(new IlluminatePaginatorAdapter($paginator))
			->excludeCases()
			->respond();
	}

	public function show(Child $child)
	{

		return fractal()
			->item($child)
			->transformWith(new ChildTransformer)
			->serializeWith(new SimpleArraySerializer())
			->parseIncludes(request('with'))
			->respond();
	}

	public function getAlert(Child $child)
	{
		$alert = $child->alert;

		return fractal()
			->item($alert)
			->transformWith(new StepTransformer())
			->serializeWith(new SimpleArraySerializer())
			->parseIncludes(['fields', 'case'])
			->respond();
	}

	public function comments(Child $child)
	{
		return fractal()
			->collection($child->comments)
			->transformWith(new CommentTransformer())
			->serializeWith(new SimpleArraySerializer())
			->parseIncludes(request('with'))
			->respond();
	}

	public function removeComment(Child $child, Comment $comment)
	{

		if ($child == null or $comment == null)
			return $this->api_failure("A anotação não pode ser removida");

		if ($this->currentUser()->id != $comment->author_id) {
			return $this->api_failure("Você não tem permissão para remover a anotação selecionada");
		} else {
			$comment->delete();
			return $this->api_success();
		}
	}

	public function getComment(Child $child, Comment $comment)
	{
		if ($child == null or $comment == null)
			return $this->api_failure("A anotação não foi encontrada");

		if ($this->currentUser()->id != $comment->author_id) {
			return $this->api_failure("Você não tem permissão para visualizar a anotação selecionada");
		} else {
			return response()->json($comment);
		}
	}

	public function attachments(Child $child)
	{
		return fractal()
			->collection($child->attachments)
			->transformWith(new AttachmentTransformer())
			->serializeWith(new SimpleArraySerializer())
			->parseIncludes(request('with'))
			->respond();
	}

	public function activityLog(Child $child)
	{
		return fractal()
			->collection(ActivityLog::fetchEntries($child, 64, true))
			->transformWith(new LogEntryTransformer())
			->serializeWith(new SimpleArraySerializer())
			->parseIncludes(request('with'))
			->respond();
	}

	public function addComment(Child $child)
	{
		try {

			$message = request('message', '');
			$comment = Comment::post($child, Auth::user(), $message);

			return response()->json(['status' => 'ok', 'comment_id' => $comment->id]);
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

	public function updateComment()
	{

		try {

			$message = request('message', '');
			$id_message = request('id_message', null);

			if ($message == null or $id_message == null)
				return $this->api_failure("A anotação não pode ser editada");

			$comment = Comment::updateComment(Auth::user(), $id_message, $message);

			return response()->json(['status' => 'ok', 'comment_id' => $comment->id]);
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

	public function removeAttachment(Child $child, Attachment $attachment)
	{
		try {

			if ($attachment->content_id !== $child->id) {
				return $this->api_failure('not_allowed');
			}

			$attachment->delete();

			return $this->api_success();
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

	public function addAttachment(Child $child)
	{
		try {

			$file = request()->file('file');

			if (!$file || !$file->isValid()) {
				return $this->api_failure('file_not_uploaded', ['file' => $file]);
			}

			$description = request('description', '');
			$attachment = Attachment::createFromUpload($file, $child, Auth::user(), $description);

			return response()->json(['status' => 'ok', 'attachment_id' => $attachment->id]);
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

	public function store()
	{

		try {
			$user = Auth::user();
			$tenant = $user->isRestrictedToTenant() ? $user->tenant : Tenant::findOrFail(request('tenant_id'));

			$data = request()->toArray();
			$validation = (new Alerta())->validate($data);

			if ($validation->fails())
				return $this->api_validation_failed('validation_failed', $validation);

			$child = Child::spawnFromAlertData($tenant, $user->id, $data);

			return response()->json([
				'status' => 'ok',
				'tenant_id' => $tenant->id,
				'child_id' => $child->id,
			]);
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

	public function getMap()
	{

		$city_id = request('city_id');

		$mapCenter = ['lat' => '-10.5013846', 'lng' => '-50.901559', 'zoom' => 10];

		if ($city_id == null) {
			$coordinates = Child::query()
				->whereIn('child_status', ['out_of_school', 'in_observation'])
				->whereNotNull('lat')
				->whereNotNull('lng')
				->get(['id', 'lat', 'lng'])
				->map(function ($child) {
					return ['id' => $child->id, 'latitude' => $child->lat, 'longitude' => $child->lng];
				});
		} else {
			$city_ibge = City::where('ibge_city_id', '=', intval($city_id))->first();
			$coordinates = Child::query()
				->where('city_id', '=', $city_ibge->id)
				->whereIn('child_status', ['out_of_school', 'in_observation'])
				->whereNotNull('lat')
				->whereNotNull('lng')
				->get(['id', 'lat', 'lng'])
				->map(function ($child) {
					return ['id' => $child->id, 'latitude' => $child->lat, 'longitude' => $child->lng];
				});
		}

		return response()->json([
			'center' => [
				'latitude' => $mapCenter['lat'],
				'longitude' => $mapCenter['lng'],
				'zoom' => $mapCenter['zoom'],
			],
			'coordinates' => $coordinates
		]);
	}

	public function list_files_exported()
	{
		$reports = \Storage::allFiles('attachments/children_reports/' . Auth::user()->id . "/");
		$finalReports = array_map(function ($file) {
			return [
				'file' => str_replace('attachments/children_reports/' . Auth::user()->id, "", $file),
				'size' => \Storage::size($file),
				'last_modification' => \Storage::lastModified($file)
			];
		}, $reports);
		return response()->json(['status' => 'ok', 'data' => $finalReports]);
	}

	public function get_file_exported()
	{
		$nameFile = request('file');
		if (!isset($nameFile)) {
			return response()->json(['error' => 'Not authorized.'], 403);
		}
		$exists = \Storage::exists("attachments/children_reports/" . Auth::user()->id . "/" . $nameFile);
		if ($exists) {
			return response()->download(storage_path("app/attachments/children_reports/" . Auth::user()->id . "/" . $nameFile));
		} else {
			return response()->json(['error' => 'Arquivo inexistente.'], 403);
		}
	}

	public function create_report_child(Search $search)
	{
		$query = $this->prepareSearchQuery();
		$query = $query->getQuery();
		$job = new ProcessExportChildrenJob(Auth::user(), $query);
		$job->handle($search);
		return response()->json(
			[
				'msg' => 'Arquivo criado',
				'date' => Carbon::now()->timestamp
			],
			200
		);
	}
}
