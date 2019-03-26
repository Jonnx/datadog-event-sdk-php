<?php

namespace Jonnx\DataDog;

use Carbon\Carbon;

class DataDogEvent {
    
    protected $client;
    
    public function __construct(DataDogEventClient $client, $data) 
    {
        $this->client = $client;
        $this->data = $data;
    }
    
    public function getDateHappened()
    {
        $date_happened = new Carbon($this->data->date_happened);
        return $date_happened;
    }
    
    public function getHandle()
    {
        return $this->data->handle;
    }
    
    public function getId()
    {
        return $this->data->id;
    }
    
    public function getPriority()
    {
        return $this->data->priority;
    }
    
    public function getTags()
    {
        return $this->data->tags;
    }
    
    public function getText()
    {
        return $this->data->text;
    }
    
    public function getTitle()
    {
        return $this->data->title;
    }
    
    public function getUrl()
    {
        return $this->data->url;
    }
    
    public function fresh() {
        return $this->client->getEvent($this->getId);
    }
    
    public function delete()
    {
        return $this->client->deleteEvent($this);
    }
    
}