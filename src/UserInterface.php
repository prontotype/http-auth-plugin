<?php namespace Prontotype\Plugins\HttpAuth;

interface UserInterface
{
    public function isValid($name, $password, $realm);

    public function parse();

}
