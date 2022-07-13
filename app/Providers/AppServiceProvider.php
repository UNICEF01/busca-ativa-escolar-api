<?php
/**
 * busca-ativa-escolar-api
 * AppServiceProvider.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 */

namespace BuscaAtivaEscolar\Providers;

use BuscaAtivaEscolar\Attachment;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\Comment;
use BuscaAtivaEscolar\Listeners\ChildActivityNotificationGenerator;
use BuscaAtivaEscolar\Observers\AttachmentActivityLogObserver;
use BuscaAtivaEscolar\Observers\ChildActivityLogObserver;
use BuscaAtivaEscolar\Observers\CommentActivityLogObserver;
use BuscaAtivaEscolar\SMS\Handlers\Zenvia;
use BuscaAtivaEscolar\SMS\SmsProvider;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Log;
use Validator;

class AppServiceProvider extends ServiceProvider {

    public function boot() {

	    setlocale(LC_ALL, config('app.locale') . '.utf8');
	    Carbon::setLocale(config('app.locale'));

	    Child::observe(new ChildActivityLogObserver());
    	Comment::observe(new CommentActivityLogObserver());
    	Attachment::observe(new AttachmentActivityLogObserver());

	    Validator::extend('required_for_completion', function ($attribute, $value, $parameters, $validator) {
	    	if(!isset($validator->getData()['is_completing_step'])) return true;
	    	if(!boolval($validator->getData()['is_completing_step'])) return true;
	    	return !empty($value);
	    });

	    Validator::extend('boolean_required_for_completion', function ($attribute, $value, $parameters, $validator) {
	    	if(!isset($validator->getData()['is_completing_step'])) return true;
	    	if(!boolval($validator->getData()['is_completing_step'])) return true;
	    	return !empty($value)
			    || ($value === "0")
			    || ($value === "false")
			    || ($value === 0)
			    || ($value === false);
	    });

	    Validator::extend('required_if_different', function ($attribute, $value, $parameters, $validator) {
	    	if($validator->getData()[$parameters[0]] !== $validator->getData()[$parameters[1]]) {
	    		return !empty($value);
		    }

		    return true;
	    });

    }

    public function register() {
	    $this->app->bind(SmsProvider::class, Zenvia::class);
    }
}
