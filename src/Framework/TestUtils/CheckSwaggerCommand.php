<?php declare(strict_types=1);

namespace App\Framework\TestUtils;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class CheckSwaggerCommand extends Command
{
    private $controller;

    public function __construct(KernelInterface $kernel)
    {
        $this->controller = $kernel->getContainer()->get('nelmio_api_doc.controller.swagger_ui');
        parent::__construct('swagger:check');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ($this->controller)(new Request());

        return 0;
    }

}