<?php

namespace Jonnx\DataDog;

use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;

class DataDogEventClient {
    
    const REQUEST_STATUS_OK = 'ok';
    const ALERT_TYPE_INFO = 'info';
    const ALERT_TYPE_SUCCESS = 'success';
    const ALERT_TYPE_WARNING = 'warning';
    const ALERT_TYPE_ERROR = 'error';
    
    /**
     * @var string the datadog api key to be used when accessing the events api
     */
    protected $datadog_api_key;
    
    /**
     * @var string the datadog application key to be used when accessing the events api
     */
    protected $datadog_app_key;
    
    /**
     * @var \GuzzleHttp\Client guzzle client instance used to make requests
     */
    protected $httpClient;
    
    public function __construct($api_key, $app_key = null) 
    {
        $this->setKeys($api_key, $app_key);
    }
    
    public function setKeys($api_key, $app_key) {
        
        // set keys
        $this->datadog_api_key = $api_key;
        $this->datadog_app_key = $app_key;
        
        // configure client
        $this->httpClient = new GuzzleClient([
            'base_uri' => 'https://api.datadoghq.com/api/v1/', 
            'query' => [
                'api_key' => $this->datadog_api_key,
                'application_key' => $this->datadog_app_key,
            ],
        ]);
        return $this;
    }
    
    public function getApiKey()
    {
        return $this->datadog_api_key;
    }
    
    public function getAppKey()
    {
        return $this->datadog_app_key;
    }
    
    public function getHttpClient()
    {
        return $this->httpClient;
    }
    
    public function getEvent($event_id, $data = null) 
    {
        if(is_null($data)) {
            // fetch event data
            $response = $this->httpClient->get("events/{$event_id}");
            $data = json_decode($response->getBody())->event;
        }
        
        // @todo handle null data
        return new DataDogEvent($this, $data);
    }
    
    public function addEvent($alert_type, $title, $text, Carbon $date_happened = null, $priority = 'normal', $host = null, $tags = [], $aggregation_key = null, $source_type_name = null)
    {
        $payload = [
            'title' => $title,
            'text' => $text,
            'date_happened' => is_null($date_happened) ? null : $date_happened->timestamp,
            'priority' => $priority,
            'host' => $host,
            'tags' => $tags,
            'alert_type' => $alert_type,
            'aggregation_key' => $aggregation_key,
            'source_type_name' => $source_type_name,
        ];
    
        $request = $this->httpClient->post('events', ['json' => $payload]);
        $response = json_decode($request->getBody());
        if($response->status !== self::REQUEST_STATUS_OK) {
            return null;
        }
        
        return new DataDogEvent($this, $response->event);
    }
    
    public function deleteEvent($event) 
    {
        if($event instanceOf DataDogEvent) {
            $event = $event->getId();
        }
        
        $request = $this->httpClient->delete("events/{$event}");
        return true;
    }
    
    public function queryEventStream(Carbon $start, Carbon $end, $priority = null, $sources = [], $tags = [], $unaggregated = false)
    {
        $payload = [
            'start' => $start->timestamp,
            'end' => $end->timestamp,
            'priority' => $priority,
            'sources' => implode(',', $sources),
            'sources' => implode(',', $tags),
            'unaggregated' => $unaggregated,
            'api_key' => $this->datadog_api_key,
            'application_key' => $this->datadog_app_key,
        ];
        
        $request = $this->httpClient->get('events', ['query' => $payload]);
        $response = json_decode($request->getBody());
        
        $events = [];
        foreach($response->events as $event) {
            $events[] = new DataDogEvent($this, $eventData);
        }
        
        return $events;
    }
    
    public function info($title, $text, Carbon $date_happened = null, $priority = 'normal', $host = null, $tags = [], $aggregation_key = null, $source_type_name = null)
    {
        return $this->addEvent(self::ALERT_TYPE_INFO, $title, $text, $date_happened, $priority, $host, $tags, $aggregation_key);
    }
    
    public function success($title, $text, Carbon $date_happened = null, $priority = 'normal', $host = null, $tags = [], $aggregation_key = null, $source_type_name = null)
    {
        return $this->addEvent(self::ALERT_TYPE_SUCCESS, $title, $text, $date_happened, $priority, $host, $tags, $aggregation_key);
    }
    
    public function warning($title, $text, Carbon $date_happened = null, $priority = 'normal', $host = null, $tags = [], $aggregation_key = null, $source_type_name = null)
    {
        return $this->addEvent(self::ALERT_TYPE_WARNING, $title, $text, $date_happened, $priority, $host, $tags, $aggregation_key);
    }
    
    public function error($title, $text, Carbon $date_happened = null, $priority = 'normal', $host = null, $tags = [], $aggregation_key = null, $source_type_name = null)
    {
        return $this->addEvent(self::ALERT_TYPE_WARNING, $title, $text, $date_happened, $priority, $host, $tags, $aggregation_key);
    }
    
}