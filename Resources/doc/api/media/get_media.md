# API: [GET] Media

Get media informations

## General request

|             | Values
|-------------|-------
| **Method**  | GET
| **Route**   | /media/{reference}.{_format}
| **Formats** | json, xml, csv, jpeg, jpg, png, gif
| **Secured** | jwt token

## Route Parameters description

- *reference* : The unique reference of the media
- *_format* : extension media format

## HTTP Request parameters

>  with specific images formats (png, jpeg, jpg, gif)

| Name      | Optional | Default | Requirements | Description
|-----------|----------|---------|--------------|------------
| quality   | true     | 115     | int          | the image compression quality
| rotate    | true     | 0       | \d+          | rotation transformer angle
| resize    | true     | 0       | \d+          | resize transformer value
| scale     | true     | 0       | \d+          | scale transformer value
| grayscale | true     | 0       | 0/1          | grayscale transformer off/on
| width     | true     | 0       | \d+          | output image width
| height    | true     | 0       | \d+          | output image height
| maxwidth  | true     | 0       | \d+          | rescale maxwidth value
| maxheight | true     | 0       | \d+          | rescale maxheight value
| minwidth  | true     | 0       | \d+          | rescale minwidth value
| minheight | true     | 0       | \d+          | rescale minheight value

> with all documents formats

| Name          | Optional | Description
|---------------|----------|---------------
| signingKey    | true     | jwt token permission
| maxAge        | true     | ttl maxAge value of HTTP cache headers
| sharedMaxAge  | true     | ttl sharedMaxAge of HTTP cache headers

## HTTP Response codes

| Code | HTTP Status Code
|------|------------
| 200  | Ok
| 401  | Unauthorized
| 404  | Not found (wrong id)
| 503  | Service Unavailable

## Decode Signing key

When you call a media, permission to download will be denied if the called api media was created with the connection constraint and:

- it is not activated
- or the token contains a larger start date than now
- or the token contains an expiration date already passed
- or the client id media value contained in token (media[id] value) no match with the client id media contained in metadata (metadata[idMedia] value)
- or the token has been saved with a unique download permission while the media has already been downloaded once
- or it has been saved with a list of user names allowed to download media that does not contain the registered user name inside the token
- or it has been saved with a list of roles allowed to download media that does not contain any of the roles saved in the token
- or there is not intersect between rangeip token and rangeip authorized by api media

In all these cases, the response returned will be an HTTP request with a 503 status.

## HTTP Response content examples

> json format

```curl
$ curl http://your_domain/api/media/reference.json
```

```json
[{
    id: 206,
    source: "mediatheque/file",
    ip_source: "10.255.0.2",
    reference: "4027551660-1534259017-5d67afbf38f5ca5f13b0497047599fb2-9241",
    reference_prefix: "mediatheque/file",
    extension: "png",
    provider_service_name: "gaufrette_storage_gallery_azure",
    name: "Sélection_015.png",
    description: "doc-1",
    size: 168693,
    quality: 150,
    mime_type: "image/png",
    enabled: true,
    metadata: {
        form_name: "sfynx_mediabundle_mediatype_file",
        field_form: "image",
        idMedia: "28",
        title: "doc-1",
        description: "doc-1",
        width: 1213,
        height: 756
    },
    signing: {
        connected: "1",
        roles: [
            "ROLE_USER",
            "ROLE_CUSTOMER",
            "ROLE_PROVIDER"
        ]
    },
    "createdAt": "2014-01-29T15:47:32+01:00",
    uploaded_file: null
}]
```

> xml format

```curl
$ curl http://your_domain/api/media/reference.xml
```

```xml
<response>
    <id>206</id>
    <source>mediatheque/file</source>
    <ip_source>10.255.0.2</ip_source>
    <reference>
        4027551660-1534259017-5d67afbf38f5ca5f13b0497047599fb2-9241
    </reference>
    <reference_prefix>mediatheque/file</reference_prefix>
    <extension>png</extension>
    <provider_service_name>gaufrette_storage_gallery_azure</provider_service_name>
    <name>Sélection_015.png</name>
    <description>doc-1</description>
    <size>168693</size>
    <quality>150</quality>
    <mime_type>image/png</mime_type>
    <enabled>1</enabled>
    <metadata>
        <form_name>sfynx_mediabundle_mediatype_file</form_name>
        <field_form>image</field_form>
        <idMedia>28</idMedia>
        <title>doc-1</title>
        <description>doc-1</description>
        <width>1213</width>
        <height>756</height>
    </metadata>
    <signing>
        <connected>1</connected>
        <roles>ROLE_USER</roles>
        <roles>ROLE_CUSTOMER</roles>
        <roles>ROLE_PROVIDER</roles>
    </signing>
    <createdat>2014-01-29T15:47:32+01:00</createdat>
    <uploaded_file/>
</response>
```