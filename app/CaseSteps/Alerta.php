<?php
/**
 * busca-ativa-escolar-api
 * Alerta.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 29/12/2016, 13:43
 */

namespace BuscaAtivaEscolar\CaseSteps;

use BuscaAtivaEscolar\Data\AlertCause;
use BuscaAtivaEscolar\FormBuilder\CanGenerateForms;
use BuscaAtivaEscolar\FormBuilder\FormBuilder;

/**
 * Class Alerta
 * @package BuscaAtivaEscolar\CaseSteps
 *
 * @property string $id
 * @property string $name
 * @property string $gender
 * @property string $race
 * @property string $dob
 * @property string $rg
 * @property string $cpf
 * @property string $nis
 * @property string $cns
 * @property string $alert_cause_id
 * @property string $alert_status
 * @property string $mother_name
 * @property string $mother_rg
 * @property string $mother_phone
 * @property string $father_name
 * @property string $father_rg
 * @property string $father_phone
 * @property string $place_address
 * @property string $place_cep
 * @property string $place_reference
 * @property string $place_neighborhood
 * @property string $place_city_id
 * @property string $place_city_name
 * @property string $place_uf
 * @property string $place_phone
 * @property string $place_mobile
 * @property string $place_lat
 * @property string $place_lng
 * @property string $place_map_region
 * @property string $place_map_geocoded_address
 *
 */
class Alerta extends CaseStep implements CanGenerateForms  {

    protected $table = "case_steps_alerta";

    public $stepFields = [
        'name',
        'gender',
        'race',
        'dob',
        'rg',

        'cpf',
        'nis',
        'cns',

        'alert_cause_id',
        'alert_status',

        'mother_name',
        'mother_rg',
        'mother_phone',

        'father_name',
        'father_rg',
        'father_phone',

        'place_address',
        'place_cep',
        'place_reference',
        'place_neighborhood',
        'place_city_id',
        'place_city_name',
        'place_uf',
        'place_phone',
        'place_mobile',
        'place_lat',
        'place_lng',
        'place_map_region',
        'place_map_geocoded_address',
        'observation'
    ];

    protected $casts = [
        'place_map_geocoded_address' => 'array',
    ];

    public function scopeAccepted($query) {
        return $query->where('alert_status', 'accepted');
    }

    public function scopeRejected($query) {
        return $query->where('alert_status', 'rejected');
    }

    public function scopePending($query) {
        return $query->where('alert_status', 'pending');
    }

    public function scopeNotRejected($query) {
        return $query->whereIn('alert_status', ['accepted', 'pending']);
    }

    protected function onComplete() : bool {

        if($this->gender) $this->child->gender = $this->gender;
        if($this->name) $this->child->name = $this->name;
        if($this->mother_name) $this->child->mother_name = $this->mother_name;
        if($this->father_name) $this->child->father_name = $this->father_name;

        $this->child->save();

        if($this->dob) $this->child->recalculateAgeThroughBirthday($this->dob);

        $location = $this->child->updateCoordinatesThroughGeocoding("{$this->place_address} {$this->place_neighborhood} {$this->place_city_name} {$this->place_uf} {$this->place_cep}");

        if($location){
            
            $this->update([
                'place_lat' => ($location->DisplayPosition) ? $location->DisplayPosition->Latitude : null,
                'place_lng' => ($location->DisplayPosition) ? $location->DisplayPosition->Longitude : null,
                'place_map_geocoded_address' => ($location) ? $location : null,
            ]);
        }else{

            $this->update([
                'place_lat' => null,
                'place_lng' => null,
                'place_map_geocoded_address' => null,
            ]);
        }

        return true;

    }

    public function validate($data, $isCompletingStep = false) {
        return validator($data, [
            'name' => 'required',
            'gender' => \BuscaAtivaEscolar\Data\Gender::getSlugValidationMask(),
            'race' => \BuscaAtivaEscolar\Data\Race::getSlugValidationMask(),
            'dob' => 'date',
            'rg' => 'alpha_num',

            'cpf' => 'digits:11',
            'nis' => 'alpha_num',
            'cns' => 'digits:15',
            'alert_cause_id' => 'required|' . \BuscaAtivaEscolar\Data\AlertCause::getSlugValidationMask('id'),

            'mother_name' => 'required|string',
            'mother_rg' => 'alpha_num',
            'mother_phone' => 'alpha_dash',

            'father_name' => 'string',
            'father_rg' => 'alpha_num',
            'father_phone' => 'alpha_dash',

            'place_address' => 'required|string',
            'place_cep' => 'digits:8',
            'place_reference' => 'string',
            'place_neighborhood' => 'required|string',
            'place_city_id' => 'required|string',
            'place_city_name' => 'required|string',
            'place_uf' => 'required|string|size:2',
            'place_phone' => 'alpha_dash',
            'place_mobile' => 'alpha_dash',
        ]);
    }

    public static function getFormFields(): FormBuilder {
        return (new FormBuilder())
            ->group('personal', trans('form_builder.alerta.group.personal'), function (FormBuilder $group) {
                return $group
                    ->field('name', 'string', trans('form_builder.alerta.field.name'), ['required' => true])
                    //->field('gender', 'select', trans('form_builder.alerta.field.gender'), ['options' => Gender::getAllAsArray(), 'key' => 'slug', 'label' => 'label'])
                    //->field('race', 'select', trans('form_builder.alerta.field.race'), ['options' => Race::getAllAsArray(), 'key' => 'slug', 'label' => 'label'])
                    ->field('dob', 'date', trans('form_builder.alerta.field.dob'));
                //->field('rg', 'alphanum', trans('form_builder.alerta.field.rg'))
                //->field('cpf', 'alphanum', trans('form_builder.alerta.field.cpf'), ['mask' => 'cpf', 'transform' => 'strip_punctuation', 'placeholder' => '000.000.000-00'])
                //->field('nis', 'alphanum', trans('form_builder.alerta.field.nis'));
            })


            ->group('parents', trans('form_builder.alerta.group.parents'), function (FormBuilder $group) {
                return $group
                    ->field('mother_name', 'string', trans('form_builder.alerta.field.mother_name'), ['required' => true]);
                //->field('mother_rg', 'alphanum', trans('form_builder.alerta.field.mother_rg'))
                //->field('mother_phone', 'alphanum', trans('form_builder.alerta.field.mother_phone'), ['mask' => 'phone', 'transform' => 'strip_punctuation', 'placeholder' => '(00) 00000-0000'])

                //->field('father_name', 'string', trans('form_builder.alerta.field.father_name'))
                //->field('father_rg', 'alphanum', trans('form_builder.alerta.field.father_rg'))
                //->field('father_phone', 'alphanum', trans('form_builder.alerta.field.father_phone'), ['mask' => 'phone', 'transform' => 'strip_punctuation', 'placeholder' => '(00) 00000-0000']);
            })


            ->group('place', trans('form_builder.alerta.group.place'), function (FormBuilder $group) {
                return $group
                    ->field('place_address', 'string', trans('form_builder.alerta.field.place_address'), ['required' => true])
                    //->field('place_cep', 'alphanum', trans('form_builder.alerta.field.place_cep'), ['mask' => 'cep', 'transform' => 'strip_punctuation', 'placeholder' => '00000-000'])
                    ->field('place_reference', 'string', trans('form_builder.alerta.field.place_reference'))
                    ->field('place_neighborhood', 'string', trans('form_builder.alerta.field.place_neighborhood'), ['required' => true])
                    //->field('place_uf', 'select', trans('form_builder.alerta.field.place_uf'), ['options' => UF::getAllAsArray(), 'key' => 'code', 'label' => 'name'])
                    ->field('place_city_id', 'model', trans('form_builder.alerta.field.place_city_id'), ['key_as' => 'place_city', 'search_by' => 'name', 'source' => route('api.cities.search'), 'list_key' => 'results', 'key' => 'id', 'label' => 'full_name', 'hide_if_offline' => true])
                    ->field('place_city_name', 'model_field', trans('form_builder.alerta.field.place_city_name'), ['key' => 'place_city', 'field' => 'name'])
                    ->field('place_uf', 'model_field', trans('form_builder.alerta.field.place_uf'), ['key' => 'place_city', 'field' => 'uf']);
                //->field('place_phone', 'alphanum', trans('form_builder.alerta.field.place_phone'), ['mask' => 'phone', 'transform' => 'strip_punctuation'])
                //->field('place_mobile', 'alphanum', trans('form_builder.alerta.field.place_mobile'), ['mask' => 'phone', 'transform' => 'strip_punctuation']);

            })

            ->group('cause', trans('form_builder.alerta.group.cause'), function (FormBuilder $group) {
                return $group->field('alert_cause_id', 'select', trans('form_builder.alerta.field.alert_cause_id'), ['options' => AlertCause::getAllAsArray(), 'key' => 'id', 'label' => 'label']);
            })

            ->group('observation', trans('form_builder.alerta.group.observation'), function (FormBuilder $group) {
                return $group
                    ->field('observation', 'string', trans('form_builder.alerta.field.observation'));
            });
    }

}