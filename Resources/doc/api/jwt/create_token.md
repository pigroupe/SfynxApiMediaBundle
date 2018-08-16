# API: [POST] JWT TOKEN

Create a token from data

## General request

|             | Values                            | Optional
|-------------|-----------------------------------|-----------
| **Method**  | POST                              |
| **Route**   | /token                            |
| **Header**  | Content-Type=multipart/form-data  | false
| **Header**  | X-TENANT-ID={tenantIDValue}       | true
| **Secured** | true                              |

## HTTP Request parameters

| Name             | Optional | Default               | Requirements | Description
|------------------|----------|-----------------------|--------------|------------
| unique           | true     | false/config value    | boolean      | to create token with an unique upload authorisation
| kid              | true     | random/config value   | string       | key value used to manage an unique upload authorisation (value register in cache)
| ipRange          | true     | []/config value       | array        | to create token that allows downloading only from rangeip
| user             | true     | []                    | array        | to create token that allows downloading only from specific roles and/or a username
| media            | true     | []                    | array        | to create token that allows downloading only from a specific media
| start            | true     | 0/config value        | int          | to create token with a valid-from date (value in second)
| expire           | true     | 3600/config value     | int          | to create token with a valid-to date (value in second)
| algorithm        | true     | RS256/config value    | string       | algorithm used to code/decode jwt

> *`config value`* from Default column means that the default values can be set
from the bundle configuration as follows:

```yaml
sfynx_api_media:
    ...
    media:
        token: { start: 0, expire: 3600, unique: true, ipRange: {} }
```

> user parameter structure example

```php
$user = [
    'username' => 'abc',
    'roles' => ['ROLE_USER', 'ROLE_ADMIN'],
];
```

> media parameter structure example

```php
$media = [
    'id' => 28,
    ...
];
```

## HTTP Response codes

| Code | HTTP Status Code       | Description
|------|------------------------|------------
| 201  | Created                | if a valid media is passed
| 400  | Bad Request            | if the media is passed twice (i.e media which already exists in the database and in the filesystem)

## Example of usage

```curl 
curl -F media=@pathToTheFile http://your_domain/api/media
```
