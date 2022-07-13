<?php
/**
 * busca-ativa-escolar-api
 * Utils.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 04/03/2017, 18:46
 */

namespace BuscaAtivaEscolar;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

class Utils {

	public static function addURLParameter($in, $params) {

		if(strpos($in, "?") !== -1) {
			return $in . "?" . $params;
		}

		return $in . "&" . $params;

	}

	public static function renderUserAgent($userAgent) {
		if(!$userAgent) return 'Unknown';
		$ua = new Agent(null, $userAgent);

		return "{$ua->platform()} - {$ua->browser()} - {$ua->device()}";
	}

	public static function clearPhoneNumber($phone) {
		return str_replace(['(',')',' ', '.', '-',','], '', $phone);
	}

	public static function clearUserHTML($in) {
		return strip_tags($in, '<p><b><u><i><a><img><center><span><div><strong><small><big><ul><li>');
	}

	public static function clearUserStyles($in) {
		return preg_replace('/(<[^>]*) style=("[^"]+"|\'[^\']+\')([^>]*>)/i', '$1$3', $in);
	}

	public static function clearSEODescription($in) {
		$out = strip_tags($in);
		$out = str_replace(["\r","\n","\t"], " ", $out);
		$out = str_replace("\"", "\\\"", $out);
		return $out;
	}

	public static function clearSearchableField($in) {
		$out = preg_replace('/[^A-Za-z0-9\-]/', '', $in);
		return Str::ascii($out);
	}

	public static function renderHighlightableText($text) {
		$text = preg_replace('#\*{2}(.*?)\*{2}#', '<strong>$1</strong>', $text);
		$text = preg_replace('#\-{2}(.*?)\-{2}#', '<em>$1</em>', $text);

		return $text;
	}

	public static function buildPaginatorMeta(LengthAwarePaginator $paginator) {
		return ['pagination' => [
			'current_page' => $paginator->currentPage(),
			'total_pages' => $paginator->lastPage(),
			'total' => $paginator->total(),
			'count' => $paginator->count(),
			'per_page' => $paginator->perPage(),
		]];
	}

}