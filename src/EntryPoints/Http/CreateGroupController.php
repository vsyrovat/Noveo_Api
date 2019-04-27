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
    private $createGroup;

    public function __construct(CreateGroup $createGroup)
    {
        $this->createGroup = $createGroup;
    }

    /**
     * @Rest\Post("/groups/")
     */
    public function action(Request $request)
    {
        $name = $request->request->get('name');
        try {
            $group = $this->createGroup->execute($name);
            return View::create(['success' => true, 'data' => ['id' => $group->getId()], 'msg' => ''], Response::HTTP_CREATED);
        } catch (DuplicateGroupNameException $e) {
            return View::create(['success' => false, 'data' => [], 'msg' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}