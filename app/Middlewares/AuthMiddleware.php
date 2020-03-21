<?php

namespace Kneub\Middlewares;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use DateTimeImmutable;

class AuthMiddleware
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Check if user is connected
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        // Authorization not found
        if(empty($request->getHeader('HTTP_AUTHORIZATION')[0])){
            $dataJson = ['notification' => ['type' => 'error', 'title' => 'Authorization', 'msg' => "Vous n'avez pas les autorisations"]];
            return $response->withStatus($dataJson,403);
        }
        // Get Token from header
        $jwt = explode(" ", $request->getHeader('HTTP_AUTHORIZATION')[0])[1];
        $jwtManager = $this->container['jwtManager'];
        $jwtManager->setToken($jwt)->decode();

        // decode and check if token is valid
        if($jwtManager->verify()){

            if($jwtManager->validate()){
                // save user data in request
                $newRequest = $request->withAttribute('user_name', $jwtManager->getClaim('login'));
                $newRequest = $newRequest->withAttribute('user_role', $jwtManager->getClaim('role'));
                $newRequest = $newRequest->withAttribute('user_id', $jwtManager->getClaim('id'));

                $response = $next($newRequest, $response);
                return $response;
            }else{
                // ***** refresh token *****
                // get secret for username
                $repoUser = $this->container['entityManager']->getRepository('user');
                $userDB = $repoUser->getByLogin($jwtManager->getClaim('login'));
                if($userDB){
                    // decode refreshToken in invalidate token
                    $refresh = $this->container['jwtManager'];
                    $refresh->setToken($jwtManager->getClaim('refreshToken'))
                        ->setSecret($userDB->secret)
                        ->decode();

                    // Create new token
                    $newToken = $this->container['jwtManager'];
                    $newToken->addClaim('login', $userDB->login)
                        ->addClaim('role', $userDB->role)
                        ->addClaim('id', $userDB->id)
                        ->addClaim('institut', $userDB->institut)
                        ->addClaim('depot', $userDB->depot)
                        ->addClaim('exp', time() + $this->container['params.jwt']['expire'])
                        ->addClaim('refreshToken', $refresh->getToken())
                        ->encode();

                    // check if newToken is near to expire
                    if($refresh->verify() && ($newToken->decode()->getClaim('exp') <= $refresh->getClaim('exp'))){
                        $dataJson = ['type' => 'refreshToken', 'token' => (string)$newToken->getToken()];
                        return $response->withJson($dataJson);
                        //return $response->withStatus(401)->write();
                    }
                }
            }
        }
        // token not conform or expired
        $dataJson = ['type' => 'TOKENEXPIRE', 'title' => 'Session', 'msg' => "Une erreur est survenue avec votre session"];
        return $response->withJson($dataJson, 403);
    }
}
