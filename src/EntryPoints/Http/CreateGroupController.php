<?php declare(strict_types=1);

namespace App\EntryPoints\Http;

use App\Domain\Command\CreateGroup;
use App\Domain\Exception\DuplicateGroupNameException;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateGroupController extends AbstractFOSRestController
{
    private $command;

    public function __construct(CreateGroup $command)
    {
        $this->command = $command;
    }

    /**
     * @Rest\Post("/groups/")
     */
    public function action(Request $request)
    {
        $name = $request->request->get('name');
        try {
            $group = $this->command->execute($name);
            return View::create(['success' => true, 'data' => $group], Response::HTTP_CREATED);
        } catch (DuplicateGroupNameException $e) {
            return View::create(['success' => false, 'data' => [], 'msg' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}