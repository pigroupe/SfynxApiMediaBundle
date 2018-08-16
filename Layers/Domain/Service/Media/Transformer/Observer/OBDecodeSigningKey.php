<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer;

use Exception;
use stdClass;

use Sfynx\CoreBundle\Layers\Domain\Workflow\Observer\Generalisation\Command\AbstractObserver;
use Sfynx\CoreBundle\Layers\Domain\Service\Request\Generalisation\RequestInterface;
use Sfynx\CoreBundle\Layers\Infrastructure\Exception\WorkflowException;
use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer\OBSetParameters;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Command\MediaCommand;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification\SpecIsGetOriginalContent;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification\SpecIsCacheFromStorage;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification\SpecIsCacheLocale;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification\SpecIsCacheStorage;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Token\TokenService;
use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\MediaNotAuthorisationException;

/**
 * Class OBDecodeSigningKey
 *
 * @category Sfynx\ApiMediaBundle\Layers
 * @package Domain
 * @subpackage Service\Media\Transformer\Observer
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2016 PI-GROUPE
 */
class OBDecodeSigningKey extends AbstractObserver
{
    /** @var MediaCommand */
    protected $wfCommand;
    /** @var Media  */
    protected $media;
    /** @var TokenService */
    protected $tokenService;
    /** @var RequestInterface */
    protected $request;

    /**
     * OBSetParameters constructor.
     * @param Media $media
     * @param TokenService $tokenService
     * @param RequestInterface $request
     */
    public function __construct(Media $media, TokenService $tokenService, RequestInterface $request)
    {
        $this->media = $media;
        $this->tokenService = $tokenService;
        $this->request = $request;
    }

    /**
     * Set Parameters and specifications used by observer
     *
     * @return AbstractObserver
     * @throws WorkflowException
     */
    protected function execute(): AbstractObserver
    {
        // TEST
//        $signingFromMediaRegistration = [
//            'connected' => true,
//            'roles' => [
//                0 => 'ROLE_ADMIN',
//                1 => 'ROLE_USER',
//                2 => 'ROLE_USER_ERROR',
//            ],
//            'usernames' => [
//                'admin@alterway.fr'
//            ],
//            'rangeip' => [
//                '10.255.0.2.2'
//            ]
//        ];

        // if media is not enabled
        $this->assertMediaNoEnabled($this->media);

        // we get signing informations (connected, username, roles)
        $signingFromMediaRegistration = $this->media->getSigning();

        // we get command values
        $options = array_filter($this->wfCommand->toArray());

        // if the media has been saved with no permission and no rangeip constraints
        if ($this->assertMediaSaveWithNoConstraintNoPermission($signingFromMediaRegistration, $options)) {
            return $this;
        }

        // we get playload information of th jwt token.
        // Return 503 Response if the JWT has been expired
        $playload = $this->tokenService->decode($options['signingKey']);

        // Return 503 response if the token media id value no match with the id media
        $this->assertTokenWithIdMedia($this->media, $playload);

        // Return 503 response if there is not intersect between rangeip token and rangeip authorized by media
        $this->assertTokenWithoutRangeIp($signingFromMediaRegistration, $playload);

        // Return 503 response if the start date is not yet effective.
        $this->assertTokenWithNoStartDateYet($playload);

        $cacheKids = [];
        $cacheKids = ['9b2a68ff2573ca44abcf45ee52cec02f7df29c8136c04f34ae71f49f7063f31a'];
        // if the media has been saved with an unique upload authorisation
        $this->assertTokenWithUniqueUpload($cacheKids, $playload);

        // Return 503 response if no User permissions
        if ($this->assertMediaPermissions($signingFromMediaRegistration, $playload)) {
            return $this;
        }
    }

    /**
     * Return 503 response if the media is not enabled
     *
     * @param Media $media
     * @return void
     * @throws MediaNotAuthorisationException
     */
    protected function assertMediaNoEnabled(Media $media): void
    {
        if (!$media->getEnabled()) {
            throw new MediaNotAuthorisationException();
        }
    }

    /**
     * Return true if the media has been saved with no permission and no rangeip constraints
     * or if the media has been applied with parameters that exclude the permission request.
     *
     * @param array $signingFromMediaRegistration
     * @param array $options
     * @return true if we have to return class
     * @throws MediaNotAuthorisationException
     */
    protected function assertMediaSaveWithNoConstraintNoPermission(array $signingFromMediaRegistration, array $options): bool
    {
        if (empty($signingFromMediaRegistration['rangeip'])) {
            if (null === $signingFromMediaRegistration
                || empty($signingFromMediaRegistration['connected'])
                || false == $signingFromMediaRegistration['connected']
            ) {
                return true;
            } elseif (empty($options['signingKey'])) {
                foreach (OBSetParameters::excludeList as $excludePattern) {
                    if (!empty($options[$excludePattern])) {
                        unset($options[$excludePattern]);
                    }
                }

                $paramGivingPermission = $this->tokenService->getParamGivingPermission();
                foreach ($paramGivingPermission as $item) {
                    if ($item == $options) {
                        return true;
                    }
                }

                // if the media has been saved with permission and there is no jwt token
                throw new MediaNotAuthorisationException();
            }
        }
        return false;
    }

    /**
     * Return 503 response if there is not intersect between rangeip token and rangeip authorized by media
     *
     * @param array $signingFromMediaRegistration
     * @param array $playload
     * @return void
     * @throws MediaNotAuthorisationException
     */
    protected function assertTokenWithoutRangeIp(array $signingFromMediaRegistration, array $playload): void
    {
        if (!empty($signingFromMediaRegistration['rangeip'])
            && empty(\array_intersect($playload['context']['rangeip'], $signingFromMediaRegistration['rangeip']))
        ) {
            throw new MediaNotAuthorisationException();
        }
    }

    /**
     * Return 503 response if the id media value in token no match with the media identifier
     *
     * @param array $signingFromMediaRegistration
     * @param array $playload
     * @return void
     * @throws MediaNotAuthorisationException
     */
    protected function assertTokenWithIdMedia(Media $media, array $playload): void
    {
        if (!empty($playload['context']['media']['id'])
            && (int)$media->getMetadata()['idMedia'] !== (int)$playload['context']['media']['id']
        ) {
            throw new MediaNotAuthorisationException();
        }
    }

    /**
     * Return 503 response if the start date is not yet effective.
     *
     * @param array $playload
     * @return void
     * @throws MediaNotAuthorisationException
     */
    protected function assertTokenWithNoStartDateYet(array $playload): void
    {
        if (!empty($playload['context']['date']['start'])) {
            $startTime = $playload['context']['date']['start'];
            $now = \time(true);
            $diff = $startTime - $now;

            if ($diff > 0) {
                throw new MediaNotAuthorisationException();
            }
        }
    }

    /**
     * Return 503 response if the token has been created with an unique upload authorisation
     *
     * @param array $cacheKids
     * @param array $playload
     * @throws MediaNotAuthorisationException
     */
    protected function assertTokenWithUniqueUpload(array $cacheKids, array $playload): void
    {
        if (!empty($playload['kid'])
            && \in_array($playload['kid'], $cacheKids)
            && !empty($playload['context']['unique']) && $playload['context']['unique']
        ) {
            throw new MediaNotAuthorisationException();
        }
    }

    /**
     * Return 503 response if no User permissions
     *
     * @param array $signingFromMediaRegistration
     * @param array $playload
     * @return true if we have to return class
     * @throws MediaNotAuthorisationException
     */
    protected function assertMediaPermissions(array $signingFromMediaRegistration, array $playload): bool
    {
        if (null !== $signingFromMediaRegistration && !(
                (
                    // if the media has been saved with the connection constraint only
                    !empty($signingFromMediaRegistration['connected']) && true == $signingFromMediaRegistration['connected']
                    && empty($signingFromMediaRegistration['roles']) && empty($signingFromMediaRegistration['usernames'])
                ) ||
                (   // if the media has been saved with the connection constraint and user roles accepted for authorization.
                    !empty($signingFromMediaRegistration['connected']) && true == $signingFromMediaRegistration['connected']
                    && !empty($signingFromMediaRegistration['roles']) && empty($signingFromMediaRegistration['usernames'])
                    && !empty(\array_intersect($playload['context']['user']['roles'], $signingFromMediaRegistration['roles']))
                ) ||
                (   // if the media has been saved with the connection constraint and username inside the token is not accepted for authorization
                    !empty($signingFromMediaRegistration['connected']) && true == $signingFromMediaRegistration['connected']
                    && empty($signingFromMediaRegistration['roles']) && !empty($signingFromMediaRegistration['usernames'])
                    && \in_array($playload['context']['user']['username'], $signingFromMediaRegistration['usernames'])
                ) ||
                (   // if the media has been saved with the connection constraint and roles and username inside the token are not accepted for authorization
                    !empty($signingFromMediaRegistration['connected']) && true == $signingFromMediaRegistration['connected']
                    && !empty($signingFromMediaRegistration['roles']) && !empty($signingFromMediaRegistration['usernames'])
                    && \in_array($playload['context']['user']['username'], $signingFromMediaRegistration['usernames'])
                    && !empty(\array_intersect($playload['context']['user']['roles'], $signingFromMediaRegistration['roles']))
                )
            )
        ) {
            throw new MediaNotAuthorisationException();
        }

        return true;
    }
}
