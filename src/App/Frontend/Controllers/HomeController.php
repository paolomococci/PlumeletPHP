<?php

declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Controllers;

use DateTime;
use Psr\Http\Message\ResponseInterface;

/**
 * HomeController
 *
 * A class demonstrating the implementation of the inversion of control principle!
 */
class HomeController extends Controller
{
    public function __construct(
        private DateTime $datetime
    ) {}

    public function index(): ResponseInterface
    {
        return $this->render(
            'Home/index',
            [
                'datetime' => $this->datetime->format('l'),
                'name'     => 'John Doe',
                'job'      => 'Full Stack Developer',
                'email'    => 'john.doe@example.local',
            ]
        );
    }
}
