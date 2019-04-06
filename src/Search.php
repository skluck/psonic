<?php

namespace Psonic;


use Psonic\Channels\Channel;
use Psonic\Contracts\Client;
use Psonic\Commands\Search\QueryCommand;
use Psonic\Commands\Search\SuggestCommand;
use Psonic\Exceptions\CommandFailedException;
use Psonic\Commands\Search\StartSearchChannelCommand;

class Search extends Channel
{
    public function __construct(Client $client)
    {
        parent::__construct($client);
    }

    public function connect()
    {
        parent::connect();

        $response = $this->send(new StartSearchChannelCommand());

        if($bufferSize = $response->get('bufferSize')){
            $this->bufferSize = (int) $bufferSize;
        }

        return $response;
    }

    public function query($collection, $bucket, $terms): array
    {
        $response = $this->send(new QueryCommand($collection, $bucket, $terms));

        if(! $response->getStatus() == 'PENDING') {
            throw new CommandFailedException;
        }

        $results = $this->read();

        if(! $results->getStatus() == 'EVENT') {
            throw new CommandFailedException;
        }

        return $results->getResults();
    }

    public function suggest($collection, $bucket, $terms): array
    {
        $response = $this->send(new SuggestCommand($collection, $bucket, $terms));

        if(! $response->getStatus() == 'PENDING') {
            throw new CommandFailedException;
        }

        $results = $this->read();

        if(! $results->getStatus() == 'EVENT') {
            throw new CommandFailedException;
        }

        return $results->getResults();
    }
}