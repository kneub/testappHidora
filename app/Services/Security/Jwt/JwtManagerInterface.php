<?php
namespace Kneub\Services\Security\Jwt;

interface JwtManagerInterface
{
    public function getToken();

    public function encode();

    public function decode();

    public function verify();

    public function validate();
}