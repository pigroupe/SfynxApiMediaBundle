<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 * @license: GPL
 *
 */

namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Mapper;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Mapper\Rule\RuleInterface;

interface StorageMapperInterface
{
    /**
     * Check the rules.
     *
     * @param string $mediaPath
     */
    public function checkRules($mediaPath);

    /**
     * Add a rule to the provider.
     *
     * @param RuleInterface $rule
     */
    public function addRule(RuleInterface $rule);

    /**
     * Get Storage provider service name
     *
     * @return Gaufrette\Filesystem
     */
    public function getStorageProviderServiceName();

    /**
     * Get Storage provider
     *
     * @return Gaufrette\Filesystem
     */
    public function getStorageProvider();
}
