<?php declare(strict_types=1);

namespace App\EntryPoints\Http\CreateGroup;

use App\Domain\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

class Controller extends AbstractFOSRestController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Rest\Post("/groups/")
     */
    public function action(Request $request)
    {
        $name = $request->request->get('name');
        $group = new Group($name);
        $this->em->persist($group);
        $this->em->flush();

        return View::create(['id' => $group->getId()], 201);
    }
}