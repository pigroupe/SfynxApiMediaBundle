Transfomers
===========

What is it
----------

Transformer is a service class wich allow you to apply some transformation to the required raw media.


How to add a new customized transformer
---------------------------------------

Declare the transformer in the service files and tag it as below :

```yml

    sfynx_api_media.transformer.default:
        class: Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\MyStuffMediaTransformer
        public: false
        tags:
            - { name: sfynx_api_media.transformer }
```

You must implements MediaTransfomerInterface or extends a class whose implements it.

Like this:

```php

abstract class AbstractStuffMediaTransformer implements MediaTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function checkFormat($format)
    {
        return in_array($format, $this->getAvailableFormats());
    }

    /**
     * {@inheritdoc}
     */
    public function transform(Filesystem $storageProvider, Media $media, array $options = array())
    {
        ...

        $responseMedia = $this
            ->process($storageProvider, $media, $options)

        ...

        return $responseMedia;
    }

    ...
}
```

Implies you declare inside these methods below :

```php

    /**
     * Get available formats
     *
     * @return array
     */
    abstract protected function getAvailableFormats();

    /**
     * Process the transformation
     *
     * @param Filesystem $storageProvider
     * @param Media $media
     * @return ResponseMedia
     */
    abstract protected function process(Filesystem $storageProvider, Media $media, array $options = array());

```

Extends it :

```php

class MyStuffMediaTransformer extends AbstractStuffMediaTransformer
{
```

define available formats :

```php

    /**
     * {@inheritdoc}
     */
    protected function getAvailableFormats()
    {
        return ['stu', 'stf', 'stuff'];
    }
```

Defines options the transformer can stand :

```php
    /**
     * {@inheritdoc}
     */
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setOptional(array(
            'stuff',
            'thing',
            'other',
            'option4',
        ));
    }
```

Implements the transform method or the abstract inherited part of it :

```php

    /**
     * {@inheritdoc}
     */
    public function process(Filesystem $storageProvider, Media $media, array $options = array())
    {
        ...

        $responseMedia
            ->setContent($myTransformedStuffContent)
            ->setContentType($myStuffMimeType)
            ->setContentLength($myStuffSize)
            ->setLastModifiedAt($myTransformedStuffCreationDate)
        ;

        return responseMedia;
    }
```

Then you can use your transformation as simple service

```php
    $media = $this->get('sfynx.apimedia.manager.media.entity')->retrieveMedia($reference);

    $responseMedia = $this->get('sfynx.apimedia.manager.media.entity')->transform(
        $media,
        array_merge(
            $request->query->all(),
            ['format' => $request->getRequestFormat()]
        )
    );
```
