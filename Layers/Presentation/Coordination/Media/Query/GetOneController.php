<?php
namespace Sfynx\ApiMediaBundle\Layers\Presentation\Coordination\Media\Query;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\InvalidOptionsException;

use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Generalisation\Interfaces\MediaManagerInterface;
use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\MediaNotFoundException;
use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\NoMatchedTransformerException;
use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\MediaNotAuthorisationException;

/**
 * Class GetOneController
 * @category   Sfynx\ApiMediaBundle\Layers
 * @package    Presentation
 * @subpackage Coordination\Media\Query
 */
class GetOneController
{
    /** @var MediaManagerInterface */
    protected $manager;
    /** @var string */
    protected $cacheStorageProvider;

    /**
     * GetOneController constructor.
     * @param MediaManagerInterface $manager
     * @param string $cacheStorageProvider
     */
    public function __construct(MediaManagerInterface $manager, string $cacheStorageProvider)
    {
        $this->manager = $manager;
        $this->cacheStorageProvider = $cacheStorageProvider;
    }

    /**
     * Get a specific media
     *
     * @param Request $request
     * @param string $reference
     * @param string $_format
     */
    public function execute(Request $request, $reference, $_format)
    {
        $response = new Response();

        try {
            $media = $this->manager->retrieveMedia($reference);
            try {
                $responseMedia = $this->manager->transform($media,
                    \array_merge($request->query->all(), [
                            'format' => $_format,
                            'cacheStorageProvider' => $this->cacheStorageProvider
                        ]
                    )
                );
            } catch (InvalidOptionsException $e) {
                $responseMedia = $this->manager->transform($media, [
                        'format' => $_format,
                        'cacheStorageProvider' => $this->cacheStorageProvider
                    ]
                );
            }

            $response
            ->setPublic()
            ->setStatusCode(Response::HTTP_OK)
            ->setETag($responseMedia->getETag())
            ->setLastModified($responseMedia->getLastModifiedAt())
            ->setContent($responseMedia->getContent());
            
            (null !== $responseMedia->getMaxAge()) ? $response->setMaxAge($responseMedia->getMaxAge()): false;
            (null !== $responseMedia->getSharedMaxAge()) ? $response->setSharedMaxAge($responseMedia->getSharedMaxAge()): false;

            $response->headers->set('Content-Type', $responseMedia->getContentType());
            $response->headers->set('Content-Length', $responseMedia->getContentLength());
            $response->headers->set('Access-Control-Allow-Origin', '*');

            if (null !== $responseMedia->getContentDisposition()) {
                $response->headers->set('Content-Disposition', $responseMedia->getContentDisposition());
            }
        } catch (MediaNotFoundException $e) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent($e->getMessage());
            $response->headers->set('Content-Type', 'text/html');
        } catch (NoMatchedTransformerException $e) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent($e->getMessage());
            $response->headers->set('Content-Type', 'text/html');
        } catch (MediaNotAuthorisationException $e) {
            $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
            $response->setContent($e->getMessage());
            $response->headers->set('Content-Type', 'text/html');
        } catch (\Exception $e) {
            $response->setStatusCode(Response::HTTP_SERVICE_UNAVAILABLE);
            $response->setContent($e->getMessage());
            $response->headers->set('Content-Type', 'text/html');
        }

        return $response;
    }
}
