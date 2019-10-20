<?php

namespace Elliemae\ContentStack;

use Illuminate\Http\Request;

class ContentStackAPI
{
	private $csurl;
	private $csparam;
	private $query_str;
	private $client;

	public function __construct($content_type, $params=[], $query_str='')
	{
		$this->csurl = 'https://'.env('CONTENT_STACK_API_ENDPOINT');
        $this->csurl.= 'content_types/';
        $this->csurl.= $content_type;
        $this->csurl.= '/entries';
        $this->csurl.= '?';

		$this->csparam['api_key'] = env('CONTENT_STACK_API_KEY');
        $this->csparam['access_token'] = env('CONTENT_STACK_API_ACCESS_TOKEN');
        $this->csparam['environment'] = env('CONTENT_STACK_ENVIRONMENT');
        $this->csparam['include_count'] = 'true';
        $this->csparam += $params;

        $this->query_str = $query_str==''?'':'&query='.$query_str;

        $this->client = new \GuzzleHttp\Client(['http_errors'=>false]);
	}

	private function get_url()
	{
		return $this->csurl.http_build_query($this->csparam).$this->query_str;
	}

	public function fetch()
	{
		$response = $this->client->request('GET', $this->get_url());
		$result = json_decode($response->getBody());

		if($response->getReasonPhrase() != 'OK')
		{
			abort(400, 'ContentStack '.$response->getReasonPhrase().' - Error code: '.$result->error_code.', '.$result->error_message);
		}

		return $result;
	}

	public function fetchAll()
	{
		unset($this->csparam['limit']);

		$loop = 0;
		$results = [];

		do {
			$this->csparam['skip'] = $loop*100;

			$result = $this->fetch();

			$results = array_merge($results, $result->entries);

			$count = (int)ceil($result->count/100);
		} while(++$loop < $count);

		return (object)[
			'entries' => $results,
			'count' => $result->count,
		];
	}
}
