<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Service\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

class AuthController extends BaseController
{
    public function __construct(
        Twig $view,
        private AuthService $authService,
        private LoggerInterface $logger,
    ) {
        parent::__construct($view);
    }

    public function showRegister(Request $request, Response $response): Response
    {
        //$this->logger->info('Register page requested');

        if (isset($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/')->withStatus(302);
        }
        return $this->render($response, 'auth/register.twig', ['csrf_token' => $_SESSION['csrf_token'],]);
    }

    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        $passwordRepeat = $data['confirm_password'] ?? '';
        $csrfToken = $data['csrf_token'] ?? '';

        if (!isset($_SESSION['csrf_token']) || $csrfToken !== $_SESSION['csrf_token']) {
            return $this->render($response, 'auth/register.twig');
        }

        $errors = [];

        if (strlen($password) < 8) {
            $errors['password'] = 'Passwords msut be at least 8 charaters long.';
        }

        if (strlen($username) < 4) {
            $errors['username'] = 'Usernam msut be at least 4 charaters long.';
        }

        if ($password !== $passwordRepeat) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        if (!empty($errors)) {
            return $this->render($response, 'auth/register.twig', [
                'errors' => $errors,
                'username' => $username,
                'password' => $password,
                'csrf_token' => $_SESSION['csrf_token'],
            ]);
        }

        try {
            $this->authService->register($username, $password);
            return $response->withHeader('Location', '/login')->withStatus(302);
        } catch (\InvalidArgumentException $e) {
            $errors['username'] = 'Username in use.';
            return $this->render($response, 'auth/register.twig', [
                'errors' => $errors,
                'username' => $username,
                'password' => $password,
                'csrf_token' => $_SESSION['csrf_token'],
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Registration failed: ' . $e->getMessage());
            return $this->render($response, 'auth/register.twig', [
                'error' => 'Something went wrong. Please try again later.',
                'username' => $username,
                'password' => $password,
                'csrf_token' => $_SESSION['csrf_token'],
            ]);
        }
    }

    public function showLogin(Request $request, Response $response): Response
    {
        if (isset($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/')->withStatus(302);
        }
        return $this->render($response, 'auth/login.twig', ['csrf_token' => $_SESSION['csrf_token'],]);
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        $csrfToken = $data['csrf_token'] ?? '';

        if (!isset($_SESSION['csrf_token']) || $csrfToken !== $_SESSION['csrf_token']) {
            return $this->render($response, 'auth/login.twig');
        }

        $errors = [];

        try {
            $succes = $this->authService->attempt($username, $password);
            if ($succes) {
                $this->logger->info('success');
                return $response->withHeader('Location', '/')->withStatus(302);
            }
            $errors['loginfail'] = 'Usernam or password are worng';
            return $this->render($response, 'auth/login.twig', [
                'errors' => $errors,
                'username' => $username,
                'password' => $password,
                'csrf_token' => $_SESSION['csrf_token'],
            ]);
        } catch (\Throwable $th) {
            $errors['loginfail'] = 'Something went wrong';
            return $this->render($response, 'auth/login.twig', [
                'errors' => $errors,
                'username' => $username,
                'password' => $password,
                'csrf_token' => $_SESSION['csrf_token'],
            ]);
        }
    }

    public function logout(Request $request, Response $response): Response
    {
        session_destroy();
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}
