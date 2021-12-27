<?php
require_once 'request.php';

try
{
    $body['order'] = 'order_id';
    $body['direction'] = 'asc';
    $location = 'http://localhost/pmweb/pmweb';
    $users = request($location, 'GET', $body, 'Basic 123');
    
    print_r($users);
}
catch (Exception $e)
{
    print $e->getMessage();
}