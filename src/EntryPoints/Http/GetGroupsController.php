<?php declare(strict_types=1);

namespace App\EntryPoints\Http;

use App\Domain\Entity\Group;
use App\Domain\Query\GetGroups;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class GetGroupsController extends AbstractFOSRestController
{
    private $query;

    public function __construct(GetGroups $query)
    {
        $this->query = $query;
    }

    /**
     * @Rest\Get("/groups/")
     *
     * @SWG\Get(
     *     summary="Get list of groups",
     *     tags={"Groups"},
     *     produces={"application/json"},
     *
     *     @SWG\Response(
     *          response="200",
     *          description="Success",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean", example=true),
     *              @SWG\Property(property="data", type="array", @SWG\Items(ref=@Model(type=Group::class)))
     *          )
     *     )
     * )
     */
    public function action()
    {
        $groups = $this->query->execute();
        return View::create(['success' => true, 'data' => $groups]);
    }
}