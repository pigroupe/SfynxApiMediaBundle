<?php
namespace Sfynx\ApiMediaBundle\Layers\Presentation\Coordination\Media\Query;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Generalisation\Interfaces\MediaManagerInterface;
use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\MediaNotFoundException;

/**
 * Class GetEndpointController
 * @category   Sfynx\ApiMediaBundle\Layers
 * @package    Presentation
 * @subpackage Coordination\Media\Query
 */
class GetEndpointController
{
    /** @var MediaManagerInterface */
    protected $manager;

    /**
     * GetEndpointController constructor.
     * @param MediaManagerInterface $manager
     */
    public function __construct(MediaManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Get a specific Endpoint
     *
     * @param Request $request
     * @param string $reference
     * @param string $_format
     */
    public function execute(Request $request, $reference, $_format)
    {
        try {
            $media = $this->manager->retrieveMedia($reference);

            $data = array(
                'publicEndpoint' => $this->manager->getMediaPublicUri($media)
            );

            $response = new Response();
            $response->setPublic();
            $response->setStatusCode(Response::HTTP_OK);
            // Cache for one year
            $response->setMaxAge(31536000);
            $response->setSharedMaxAge(31536000);
        } catch (MediaNotFoundException $e) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent($e->getMessage());
            $response->headers->set('Content-Type', 'text/html');
        }

        if ($_format == 'json') {
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode($data));
        } elseif ($_format == 'xml') {
            $xml = new \SimpleXMLElement('<root/>');
            $data = array_flip($data);
            array_walk_recursive($data, array($xml, 'addChild'));
            $response->headers->set('Content-Type', 'text/xml');
            $response->setContent($xml->asXML());
        }

        return $response;
    }
}
