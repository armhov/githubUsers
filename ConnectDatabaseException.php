<?php

namespace GitHub;

class ConnectDatabaseException extends \Exception
{
    protected $message = 'Connection to database was fail!';
}